<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use PDO;

class ForumController
{
    private $auth;
    private $view;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Require doctor authentication
        $this->auth->requireRole(['doctor', 'admin']);
    }

    /**
     * Main forum page
     */
    public function index()
    {
        $user = $this->auth->user();
        
        $content = $this->view->render('doctor/forum/index', [
            'user' => $user
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Doctor Forum - Roaya Clinic',
            'pageTitle' => 'Doctor Forum',
            'pageSubtitle' => 'Discuss cases and share knowledge',
            'content' => $content
        ]);
    }

    /**
     * Single topic view page
     */
    public function topic($id)
    {
        $user = $this->auth->user();
        
        $content = $this->view->render('doctor/forum/topic', [
            'user' => $user,
            'topicId' => $id
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Forum Topic - Roaya Clinic',
            'pageTitle' => 'Forum Topic',
            'pageSubtitle' => 'View and discuss',
            'content' => $content
        ]);
    }

    /**
     * Get all topics
     */
    public function getTopics()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
        $appointmentId = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : null;
        $category = isset($_GET['category']) ? trim($_GET['category']) : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $pinnedOnly = isset($_GET['pinned_only']) ? (bool)$_GET['pinned_only'] : false;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        try {
            $where = [];
            $params = [];
            
            if ($patientId) {
                $where[] = "t.patient_id = ?";
                $params[] = $patientId;
            }
            
            if ($appointmentId) {
                $where[] = "t.appointment_id = ?";
                $params[] = $appointmentId;
            }
            
            if ($category) {
                $where[] = "t.category = ?";
                $params[] = $category;
            }
            
            if ($pinnedOnly) {
                $where[] = "t.is_pinned = 1";
            }
            
            if ($search) {
                $where[] = "(t.title LIKE ? OR t.content LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $stmt = $this->pdo->prepare("
                SELECT t.*,
                       u.name as creator_name,
                       u.profile_image as creator_image,
                       u2.name as last_reply_name,
                       p.first_name as patient_first_name,
                       p.last_name as patient_last_name,
                       a.id as appointment_id
                FROM doctor_forum_topics t
                LEFT JOIN users u ON t.created_by = u.id
                LEFT JOIN users u2 ON t.last_reply_by = u2.id
                LEFT JOIN patients p ON t.patient_id = p.id
                LEFT JOIN appointments a ON t.appointment_id = a.id
                $whereClause
                ORDER BY t.is_pinned DESC, t.last_reply_at DESC, t.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $paramIndex = 1;
            foreach ($params as $param) {
                if (is_int($param)) {
                    $stmt->bindValue($paramIndex++, $param, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($paramIndex++, $param, PDO::PARAM_STR);
                }
            }
            $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get tags and user like for each topic
            $userId = $user['id'] ?? null;
            foreach ($topics as &$topic) {
                $topic['tags'] = $this->getTopicTags($topic['id']);
                if ($userId) {
                    $topic['user_like'] = $this->getUserTopicLike($topic['id'], $userId);
                } else {
                    $topic['user_like'] = null;
                }
            }
            
            echo json_encode([
                'success' => true,
                'topics' => $topics
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum getTopics error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading topics'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Get single topic
     */
    public function getTopic($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            // Increment views
            $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET views_count = views_count + 1 WHERE id = ?");
            $stmt->execute([$id]);
            
            // Get topic
            $stmt = $this->pdo->prepare("
                SELECT t.*,
                       u.name as creator_name,
                       u.profile_image as creator_image,
                       u2.name as last_reply_name,
                       p.first_name as patient_first_name,
                       p.last_name as patient_last_name,
                       a.id as appointment_id
                FROM doctor_forum_topics t
                LEFT JOIN users u ON t.created_by = u.id
                LEFT JOIN users u2 ON t.last_reply_by = u2.id
                LEFT JOIN patients p ON t.patient_id = p.id
                LEFT JOIN appointments a ON t.appointment_id = a.id
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$topic) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Topic not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $topic['tags'] = $this->getTopicTags($id);
            $topic['user_like'] = $this->getUserTopicLike($id, $user['id']);
            
            echo json_encode([
                'success' => true,
                'topic' => $topic
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum getTopic error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading topic'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Create new topic
     */
    public function createTopic()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['title']) || !isset($data['content'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Title and content are required'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO doctor_forum_topics (title, content, category, created_by, patient_id, appointment_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['title'],
                $data['content'],
                $data['category'] ?? 'General Discussion',
                $user['id'],
                $data['patient_id'] ?? null,
                $data['appointment_id'] ?? null
            ]);
            
            $topicId = $this->pdo->lastInsertId();
            
            // Add tags if provided
            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->addTagsToTopic($topicId, $data['tags']);
            }
            
            // Handle attachments if provided
            if (isset($data['attachment_ids']) && is_array($data['attachment_ids'])) {
                foreach ($data['attachment_ids'] as $attachmentId) {
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_topic_attachments SET topic_id = ? WHERE id = ?");
                    $stmt->execute([$topicId, $attachmentId]);
                }
            }
            
            $this->pdo->commit();
            
            // Create notification for all doctors
            $this->createTopicNotification($topicId, $data['title'], $user['id']);
            
            echo json_encode([
                'success' => true,
                'topic_id' => $topicId,
                'message' => 'Topic created successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum createTopic error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while creating topic'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Update topic
     */
    public function updateTopic($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Check if user owns the topic or is admin
            $stmt = $this->pdo->prepare("SELECT created_by FROM doctor_forum_topics WHERE id = ?");
            $stmt->execute([$id]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$topic) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Topic not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            if ($topic['created_by'] != $user['id'] && $user['role'] != 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $updateFields = [];
            $params = [];
            
            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $params[] = $data['title'];
            }
            
            if (isset($data['content'])) {
                $updateFields[] = "content = ?";
                $params[] = $data['content'];
            }
            
            if (isset($data['is_pinned']) && $user['role'] != 'secretary') {
                $updateFields[] = "is_pinned = ?";
                $params[] = $data['is_pinned'] ? 1 : 0;
            }
            
            if (isset($data['is_locked']) && $user['role'] != 'secretary') {
                $updateFields[] = "is_locked = ?";
                $params[] = $data['is_locked'] ? 1 : 0;
            }
            
            if (empty($updateFields)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No fields to update'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $params[] = $id;
            $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET " . implode(", ", $updateFields) . " WHERE id = ?");
            $stmt->execute($params);
            
            echo json_encode([
                'success' => true,
                'message' => 'Topic updated successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum updateTopic error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while updating topic'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Delete topic
     */
    public function deleteTopic($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            // Check if user owns the topic or is admin
            $stmt = $this->pdo->prepare("SELECT created_by FROM doctor_forum_topics WHERE id = ?");
            $stmt->execute([$id]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$topic) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Topic not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            if ($topic['created_by'] != $user['id'] && $user['role'] != 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_topics WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Topic deleted successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum deleteTopic error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting topic'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Get topics for patient
     */
    public function getPatientTopics($patientId)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*,
                       u.name as creator_name,
                       u2.name as last_reply_name
                FROM doctor_forum_topics t
                LEFT JOIN users u ON t.created_by = u.id
                LEFT JOIN users u2 ON t.last_reply_by = u2.id
                WHERE t.patient_id = ?
                ORDER BY t.is_pinned DESC, t.last_reply_at DESC, t.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $userId = $user['id'] ?? null;
            foreach ($topics as &$topic) {
                $topic['tags'] = $this->getTopicTags($topic['id']);
                if ($userId) {
                    $topic['user_like'] = $this->getUserTopicLike($topic['id'], $userId);
                } else {
                    $topic['user_like'] = null;
                }
            }
            
            echo json_encode([
                'success' => true,
                'topics' => $topics
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum getPatientTopics error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading topics'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Get topics for appointment
     */
    public function getAppointmentTopics($appointmentId)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*,
                       u.name as creator_name,
                       u2.name as last_reply_name
                FROM doctor_forum_topics t
                LEFT JOIN users u ON t.created_by = u.id
                LEFT JOIN users u2 ON t.last_reply_by = u2.id
                WHERE t.appointment_id = ?
                ORDER BY t.is_pinned DESC, t.last_reply_at DESC, t.created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $userId = $user['id'] ?? null;
            foreach ($topics as &$topic) {
                $topic['tags'] = $this->getTopicTags($topic['id']);
                if ($userId) {
                    $topic['user_like'] = $this->getUserTopicLike($topic['id'], $userId);
                } else {
                    $topic['user_like'] = null;
                }
            }
            
            echo json_encode([
                'success' => true,
                'topics' => $topics
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum getAppointmentTopics error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading topics'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Get posts for topic (tree structure)
     */
    public function getTopicPosts($topicId)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*,
                       u.name as creator_name,
                       u.id as creator_id,
                       u.profile_image as creator_image
                FROM doctor_forum_posts p
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.topic_id = ?
                ORDER BY p.created_at ASC
            ");
            $stmt->execute([$topicId]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get images and likes for each post
            foreach ($posts as &$post) {
                $post['images'] = $this->getPostImages($post['id']);
                $post['user_like'] = $this->getUserLike($post['id'], $user['id']);
            }
            
            // Build tree structure
            $tree = $this->buildPostTree($posts);
            
            echo json_encode([
                'success' => true,
                'posts' => $tree
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum getTopicPosts error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading posts'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Create new post
     */
    public function createPost()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['topic_id']) || !isset($data['content'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Topic ID and content are required'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO doctor_forum_posts (topic_id, parent_post_id, content, created_by)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['topic_id'],
                $data['parent_post_id'] ?? null,
                $data['content'],
                $user['id']
            ]);
            
            $postId = $this->pdo->lastInsertId();
            
            // Update topic replies count and last reply
            $stmt = $this->pdo->prepare("
                UPDATE doctor_forum_topics 
                SET replies_count = replies_count + 1,
                    last_reply_at = NOW(),
                    last_reply_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$user['id'], $data['topic_id']]);
            
            $this->pdo->commit();
            
            // Create notification
            $this->createPostNotification($data['topic_id'], $user['id']);
            
            echo json_encode([
                'success' => true,
                'post_id' => $postId,
                'message' => 'Post created successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum createPost error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while creating post'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Update post
     */
    public function updatePost($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Check if user owns the post
            $stmt = $this->pdo->prepare("SELECT created_by FROM doctor_forum_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$post) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Post not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            if ($post['created_by'] != $user['id'] && $user['role'] != 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET content = ? WHERE id = ?");
            $stmt->execute([$data['content'], $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Post updated successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum updatePost error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while updating post'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Delete post
     */
    public function deletePost($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            // Check if user owns the post
            $stmt = $this->pdo->prepare("SELECT created_by, topic_id FROM doctor_forum_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$post) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Post not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            if ($post['created_by'] != $user['id'] && $user['role'] != 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $this->pdo->beginTransaction();
            
            // Delete post (cascade will handle children and images)
            $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_posts WHERE id = ?");
            $stmt->execute([$id]);
            
            // Update topic replies count
            $stmt = $this->pdo->prepare("
                UPDATE doctor_forum_topics 
                SET replies_count = GREATEST(0, replies_count - 1)
                WHERE id = ?
            ");
            $stmt->execute([$post['topic_id']]);
            
            $this->pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Post deleted successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum deletePost error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting post'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Like a post
     */
    public function likePost($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            
            // Check if already liked/disliked
            $stmt = $this->pdo->prepare("SELECT id, is_like FROM doctor_forum_post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['is_like']) {
                    // Already liked, remove like
                    $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_post_likes WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET likes_count = GREATEST(0, likes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                } else {
                    // Was disliked, change to like
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_post_likes SET is_like = 1 WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET likes_count = likes_count + 1, dislikes_count = GREATEST(0, dislikes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                }
            } else {
                // New like
                $stmt = $this->pdo->prepare("INSERT INTO doctor_forum_post_likes (post_id, user_id, is_like) VALUES (?, ?, 1)");
                $stmt->execute([$id, $user['id']]);
                $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET likes_count = likes_count + 1 WHERE id = ?");
                $stmt->execute([$id]);
            }
            
            $this->pdo->commit();
            
            // Get updated counts
            $stmt = $this->pdo->prepare("SELECT likes_count, dislikes_count FROM doctor_forum_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'likes_count' => $post['likes_count'],
                'dislikes_count' => $post['dislikes_count'],
                'user_like' => $this->getUserLike($id, $user['id'])
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum likePost error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while liking post'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Dislike a post
     */
    public function dislikePost($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            
            // Check if already liked/disliked
            $stmt = $this->pdo->prepare("SELECT id, is_like FROM doctor_forum_post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if (!$existing['is_like']) {
                    // Already disliked, remove dislike
                    $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_post_likes WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET dislikes_count = GREATEST(0, dislikes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                } else {
                    // Was liked, change to dislike
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_post_likes SET is_like = 0 WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET dislikes_count = dislikes_count + 1, likes_count = GREATEST(0, likes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                }
            } else {
                // New dislike
                $stmt = $this->pdo->prepare("INSERT INTO doctor_forum_post_likes (post_id, user_id, is_like) VALUES (?, ?, 0)");
                $stmt->execute([$id, $user['id']]);
                $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET dislikes_count = dislikes_count + 1 WHERE id = ?");
                $stmt->execute([$id]);
            }
            
            $this->pdo->commit();
            
            // Get updated counts
            $stmt = $this->pdo->prepare("SELECT likes_count, dislikes_count FROM doctor_forum_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'likes_count' => $post['likes_count'],
                'dislikes_count' => $post['dislikes_count'],
                'user_like' => $this->getUserLike($id, $user['id'])
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum dislikePost error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while disliking post'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Remove like/dislike
     */
    public function removeLike($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("SELECT id, is_like FROM doctor_forum_post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_post_likes WHERE id = ?");
                $stmt->execute([$existing['id']]);
                
                if ($existing['is_like']) {
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET likes_count = GREATEST(0, likes_count - 1) WHERE id = ?");
                } else {
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_posts SET dislikes_count = GREATEST(0, dislikes_count - 1) WHERE id = ?");
                }
                $stmt->execute([$id]);
            }
            
            $this->pdo->commit();
            
            // Get updated counts
            $stmt = $this->pdo->prepare("SELECT likes_count, dislikes_count FROM doctor_forum_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'likes_count' => $post['likes_count'],
                'dislikes_count' => $post['dislikes_count'],
                'user_like' => null
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum removeLike error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while removing like'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Upload image to post
     */
    public function uploadImage($postId)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No image uploaded'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $file = $_FILES['image'];
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            // Create upload directory
            $uploadDir = __DIR__ . '/../../storage/uploads/forum/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'forum_' . $postId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filePath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save file'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }

            // Save to database
            $stmt = $this->pdo->prepare("
                INSERT INTO doctor_forum_post_images (post_id, image_path, original_filename, file_size, mime_type)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $postId,
                '/storage/uploads/forum/' . $filename,
                $file['name'],
                $file['size'],
                $mimeType
            ]);

            $imageId = $this->pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'image_id' => $imageId,
                'image_path' => '/storage/uploads/forum/' . $filename,
                'original_filename' => $file['name']
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum uploadImage error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while uploading image'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Delete image
     */
    public function deleteImage($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            // Get image info
            $stmt = $this->pdo->prepare("
                SELECT i.*, p.created_by 
                FROM doctor_forum_post_images i
                JOIN doctor_forum_posts p ON i.post_id = p.id
                WHERE i.id = ?
            ");
            $stmt->execute([$id]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$image) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Image not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            if ($image['created_by'] != $user['id'] && $user['role'] != 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // Delete file
            $filePath = __DIR__ . '/../..' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete from database
            $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_post_images WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Image deleted successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum deleteImage error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting image'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Add tags to topic
     */
    public function addTags($topicId)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['tags']) || !is_array($data['tags'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tags array is required'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            $this->addTagsToTopic($topicId, $data['tags']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Tags added successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum addTags error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while adding tags'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Remove tag
     */
    public function removeTag($topicId, $tagId)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_tags WHERE id = ? AND topic_id = ?");
            $stmt->execute([$tagId, $topicId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Tag removed successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum removeTag error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while removing tag'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    // Helper methods

    private function getTopicTags($topicId)
    {
        try {
            // First, get all tags
            $stmt = $this->pdo->prepare("
                SELECT t.*
                FROM doctor_forum_tags t
                WHERE t.topic_id = ?
            ");
            $stmt->execute([$topicId]);
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enrich tags with names
            foreach ($tags as &$tag) {
                if ($tag['tag_type'] === 'patient') {
                    $stmt = $this->pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM patients WHERE id = ?");
                    $stmt->execute([$tag['tag_id']]);
                    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                    $tag['tag_name'] = $patient ? $patient['name'] : 'Unknown Patient';
                } elseif ($tag['tag_type'] === 'appointment') {
                    $tag['tag_name'] = '#' . $tag['tag_id'];
                } elseif ($tag['tag_type'] === 'drug') {
                    // Get drug name from hclinic_drugs database
                    try {
                        $drugsPdo = $this->getDrugsDatabaseConnection();
                        $drugStmt = $drugsPdo->prepare("
                            SELECT FirstName as drug_name
                            FROM drugs 
                            WHERE ID = ?
                        ");
                        $drugStmt->execute([$tag['tag_id']]);
                        $drug = $drugStmt->fetch(PDO::FETCH_ASSOC);
                        $tag['tag_name'] = $drug ? $drug['drug_name'] : 'Drug #' . $tag['tag_id'];
                    } catch (\Exception $e) {
                        error_log("Forum getTopicTags - Error fetching drug: " . $e->getMessage());
                        $tag['tag_name'] = 'Drug #' . $tag['tag_id'];
                    }
                }
            }
            
            return $tags;
        } catch (\Exception $e) {
            error_log("Forum getTopicTags error: " . $e->getMessage());
            // Return empty array on error
            return [];
        }
    }

    /**
     * Get connection to drugs database (hclinic_drugs)
     */
    private function getDrugsDatabaseConnection()
    {
        // Connect to hclinic_drugs database with specific user
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $username = 'hclinic_drugs';  // Use the correct user for drugs database
        $password = 'Carmen@1230';  // Use the correct password for drugs database
        
        $dsn = "mysql:host={$host};dbname=hclinic_drugs;charset=utf8mb4";
        
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]);
    }

    private function addTagsToTopic($topicId, $tags)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO doctor_forum_tags (topic_id, tag_type, tag_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE id = id
        ");
        
        foreach ($tags as $tag) {
            if (isset($tag['type']) && isset($tag['id'])) {
                $stmt->execute([$topicId, $tag['type'], $tag['id']]);
            }
        }
    }

    private function getPostImages($postId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM doctor_forum_post_images WHERE post_id = ? ORDER BY created_at ASC");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserLike($postId, $userId)
    {
        $stmt = $this->pdo->prepare("SELECT is_like FROM doctor_forum_post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        $like = $stmt->fetch(PDO::FETCH_ASSOC);
        return $like ? ($like['is_like'] ? 'like' : 'dislike') : null;
    }

    private function getUserTopicLike($topicId, $userId)
    {
        $stmt = $this->pdo->prepare("SELECT is_like FROM doctor_forum_topic_likes WHERE topic_id = ? AND user_id = ?");
        $stmt->execute([$topicId, $userId]);
        $like = $stmt->fetch(PDO::FETCH_ASSOC);
        return $like ? ($like['is_like'] ? 'like' : 'dislike') : null;
    }

    /**
     * Like a topic
     */
    public function likeTopic($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            
            // Check if already liked/disliked
            $stmt = $this->pdo->prepare("SELECT id, is_like FROM doctor_forum_topic_likes WHERE topic_id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['is_like']) {
                    // Already liked, remove like
                    $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_topic_likes WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET likes_count = GREATEST(0, likes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                } else {
                    // Was disliked, change to like
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_topic_likes SET is_like = 1 WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET likes_count = likes_count + 1, dislikes_count = GREATEST(0, dislikes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                }
            } else {
                // New like
                $stmt = $this->pdo->prepare("INSERT INTO doctor_forum_topic_likes (topic_id, user_id, is_like) VALUES (?, ?, 1)");
                $stmt->execute([$id, $user['id']]);
                $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET likes_count = likes_count + 1 WHERE id = ?");
                $stmt->execute([$id]);
            }
            
            $this->pdo->commit();
            
            // Get updated counts
            $stmt = $this->pdo->prepare("SELECT likes_count, dislikes_count FROM doctor_forum_topics WHERE id = ?");
            $stmt->execute([$id]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'likes_count' => $topic['likes_count'],
                'dislikes_count' => $topic['dislikes_count'],
                'user_like' => $this->getUserTopicLike($id, $user['id'])
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum likeTopic error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while liking topic'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Dislike a topic
     */
    public function dislikeTopic($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            
            // Check if already liked/disliked
            $stmt = $this->pdo->prepare("SELECT id, is_like FROM doctor_forum_topic_likes WHERE topic_id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if (!$existing['is_like']) {
                    // Already disliked, remove dislike
                    $stmt = $this->pdo->prepare("DELETE FROM doctor_forum_topic_likes WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET dislikes_count = GREATEST(0, dislikes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                } else {
                    // Was liked, change to dislike
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_topic_likes SET is_like = 0 WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET dislikes_count = dislikes_count + 1, likes_count = GREATEST(0, likes_count - 1) WHERE id = ?");
                    $stmt->execute([$id]);
                }
            } else {
                // New dislike
                $stmt = $this->pdo->prepare("INSERT INTO doctor_forum_topic_likes (topic_id, user_id, is_like) VALUES (?, ?, 0)");
                $stmt->execute([$id, $user['id']]);
                $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET dislikes_count = dislikes_count + 1 WHERE id = ?");
                $stmt->execute([$id]);
            }
            
            $this->pdo->commit();
            
            // Get updated counts
            $stmt = $this->pdo->prepare("SELECT likes_count, dislikes_count FROM doctor_forum_topics WHERE id = ?");
            $stmt->execute([$id]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'likes_count' => $topic['likes_count'],
                'dislikes_count' => $topic['dislikes_count'],
                'user_like' => $this->getUserTopicLike($id, $user['id'])
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Forum dislikeTopic error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while disliking topic'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    private function buildPostTree($posts)
    {
        $tree = [];
        $indexed = [];
        
        // Index all posts
        foreach ($posts as $post) {
            $post['children'] = [];
            $indexed[$post['id']] = $post;
        }
        
        // Build tree
        foreach ($indexed as $post) {
            if ($post['parent_post_id'] === null) {
                $tree[] = $post;
            } else {
                if (isset($indexed[$post['parent_post_id']])) {
                    $indexed[$post['parent_post_id']]['children'][] = $post;
                }
            }
        }
        
        return $tree;
    }

    private function createTopicNotification($topicId, $title, $createdBy)
    {
        try {
            // Get all users except the creator
            $stmt = $this->pdo->prepare("
                SELECT id 
                FROM users
                WHERE id != ?
            ");
            $stmt->execute([$createdBy]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                \App\Controllers\NotificationController::create(
                    $user['id'],
                    'forum_topic',
                    'New Forum Topic',
                    "New topic: " . $title,
                    'forum_topic',
                    $topicId,
                    null
                );
            }
        } catch (\Exception $e) {
            error_log("Forum createTopicNotification error: " . $e->getMessage());
        }
    }

    private function createPostNotification($topicId, $createdBy)
    {
        try {
            // Get topic info
            $stmt = $this->pdo->prepare("
                SELECT t.title, t.created_by, t.patient_id
                FROM doctor_forum_topics t
                WHERE t.id = ?
            ");
            $stmt->execute([$topicId]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$topic) return;
            
            // Get all users except the creator
            $stmt = $this->pdo->prepare("
                SELECT id 
                FROM users
                WHERE id != ?
            ");
            $stmt->execute([$createdBy]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                \App\Controllers\NotificationController::create(
                    $user['id'],
                    'forum_post',
                    'New Forum Reply',
                    "New reply in: " . $topic['title'],
                    'forum_topic',
                    $topicId,
                    $topic['patient_id']
                );
            }
        } catch (\Exception $e) {
            error_log("Forum createPostNotification error: " . $e->getMessage());
        }
    }

    /**
     * Get category statistics
     */
    public function getCategoryStats()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT category, COUNT(*) as count
                FROM doctor_forum_topics
                GROUP BY category
                ORDER BY count DESC
            ");
            $stmt->execute();
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum getCategoryStats error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading statistics'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Get top meta tags
     */
    public function getTopMetaTags()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

        try {
            $stmt = $this->pdo->prepare("
                SELECT tag_type, tag_id, COUNT(*) as count
                FROM doctor_forum_tags
                GROUP BY tag_type, tag_id
                ORDER BY count DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get tag names
            foreach ($tags as &$tag) {
                if ($tag['tag_type'] === 'patient') {
                    $stmt = $this->pdo->prepare("SELECT first_name, last_name FROM patients WHERE id = ?");
                    $stmt->execute([$tag['tag_id']]);
                    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                    $tag['tag_name'] = $patient ? ($patient['first_name'] . ' ' . $patient['last_name']) : 'Patient #' . $tag['tag_id'];
                } elseif ($tag['tag_type'] === 'appointment') {
                    $tag['tag_name'] = 'Appointment #' . $tag['tag_id'];
                } elseif ($tag['tag_type'] === 'drug') {
                    try {
                        $drugsPdo = $this->getDrugsDatabaseConnection();
                        $drugStmt = $drugsPdo->prepare("SELECT FirstName as drug_name FROM drugs WHERE ID = ?");
                        $drugStmt->execute([$tag['tag_id']]);
                        $drug = $drugStmt->fetch(PDO::FETCH_ASSOC);
                        $tag['tag_name'] = $drug ? $drug['drug_name'] : 'Drug #' . $tag['tag_id'];
                    } catch (\Exception $e) {
                        $tag['tag_name'] = 'Drug #' . $tag['tag_id'];
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'tags' => $tags
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum getTopMetaTags error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading top tags'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Toggle resolved status
     */
    public function toggleResolved($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            // Check if user owns the topic or is admin
            $stmt = $this->pdo->prepare("SELECT created_by, category FROM doctor_forum_topics WHERE id = ?");
            $stmt->execute([$id]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$topic) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Topic not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // Only allow resolving if not General Discussion
            if ($topic['category'] === 'General Discussion') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot mark General Discussion topics as resolved'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // All doctors can mark topics as resolved
            
            // Toggle resolved status
            $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET is_resolved = NOT is_resolved WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Resolved status updated'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum toggleResolved error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while updating resolved status'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Toggle pin status
     */
    public function togglePin($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // All doctors can pin/unpin

        try {
            $stmt = $this->pdo->prepare("UPDATE doctor_forum_topics SET is_pinned = NOT is_pinned WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Pin status updated'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum togglePin error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while updating pin status'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Upload attachment for topic or post
     */
    public function uploadAttachment()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No file uploaded'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $file = $_FILES['file'];
        $topicId = $_POST['topic_id'] ?? null;
        $postId = $_POST['post_id'] ?? null;
        
        if (!$topicId && !$postId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Either topic_id or post_id is required'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // Validate file type
        $allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            $uploadDir = __DIR__ . '/../../storage/uploads/forum/attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'forum_attach_' . ($topicId ? 'topic_' . $topicId : 'post_' . $postId) . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filePath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save file'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }

            // Save to database
            if ($topicId) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO doctor_forum_topic_attachments (topic_id, file_path, original_filename, file_size, mime_type, uploaded_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $topicId,
                    '/storage/uploads/forum/attachments/' . $filename,
                    $file['name'],
                    $file['size'],
                    $mimeType,
                    $user['id']
                ]);
            } else {
                $stmt = $this->pdo->prepare("
                    INSERT INTO doctor_forum_post_attachments (post_id, file_path, original_filename, file_size, mime_type, uploaded_by)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $postId,
                    '/storage/uploads/forum/attachments/' . $filename,
                    $file['name'],
                    $file['size'],
                    $mimeType,
                    $user['id']
                ]);
            }

            $attachmentId = $this->pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'attachment_id' => $attachmentId,
                'file_path' => '/storage/uploads/forum/attachments/' . $filename,
                'original_filename' => $file['name'],
                'file_size' => $file['size'],
                'mime_type' => $mimeType
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum uploadAttachment error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while uploading attachment'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * View/download attachment
     */
    public function viewAttachment($id)
    {
        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            header('Content-Type: text/plain');
            echo 'Unauthorized';
            exit;
        }

        try {
            // Try topic attachment first
            $stmt = $this->pdo->prepare("
                SELECT * FROM doctor_forum_topic_attachments WHERE id = ?
            ");
            $stmt->execute([$id]);
            $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$attachment) {
                // Try post attachment
                $stmt = $this->pdo->prepare("
                    SELECT * FROM doctor_forum_post_attachments WHERE id = ?
                ");
                $stmt->execute([$id]);
                $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$attachment) {
                http_response_code(404);
                header('Content-Type: text/plain');
                echo 'Attachment not found';
                exit;
            }

            $filePath = __DIR__ . '/../..' . $attachment['file_path'];
            if (!file_exists($filePath)) {
                http_response_code(404);
                header('Content-Type: text/plain');
                echo 'File not found';
                exit;
            }

            // Set headers for download
            header('Content-Type: ' . $attachment['mime_type']);
            header('Content-Disposition: inline; filename="' . $attachment['original_filename'] . '"');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
            exit;
        } catch (\Exception $e) {
            error_log("Forum viewAttachment error: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: text/plain');
            echo 'Error viewing attachment';
            exit;
        }
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            // Try topic attachment first
            $stmt = $this->pdo->prepare("
                SELECT * FROM doctor_forum_topic_attachments WHERE id = ?
            ");
            $stmt->execute([$id]);
            $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
            $tableName = 'doctor_forum_topic_attachments';
            
            if (!$attachment) {
                // Try post attachment
                $stmt = $this->pdo->prepare("
                    SELECT * FROM doctor_forum_post_attachments WHERE id = ?
                ");
                $stmt->execute([$id]);
                $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
                $tableName = 'doctor_forum_post_attachments';
            }
            
            if (!$attachment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Attachment not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // Check permissions (author or admin)
            if ($attachment['uploaded_by'] != $user['id'] && $user['role'] != 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Permission denied'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // Delete file
            $filePath = __DIR__ . '/../..' . $attachment['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete from database
            $stmt = $this->pdo->prepare("DELETE FROM {$tableName} WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Attachment deleted successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Forum deleteAttachment error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting attachment'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
}

