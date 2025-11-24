<?php

namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;

class NotificationController
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /**
     * Get all notifications for current user
     */
    public function getAll()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        try {
            $stmt = $this->pdo->prepare("
                SELECT n.*, 
                       p.first_name as patient_first_name, 
                       p.last_name as patient_last_name
                FROM notifications n
                LEFT JOIN patients p ON n.patient_id = p.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bindValue(1, $user['id'], \PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("NotificationController::getAll SQL Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // Get unread count
        try {
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
            $countStmt->bindValue(1, $user['id'], \PDO::PARAM_INT);
            $countStmt->execute();
            $result = $countStmt->fetch(\PDO::FETCH_ASSOC);
            $unreadCount = $result ? (int)$result['count'] : 0;
        } catch (\PDOException $e) {
            error_log("NotificationController::getAll Count Error: " . $e->getMessage());
            $unreadCount = 0;
        }

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => (int)$unreadCount
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'unread_count' => (int)$result['count']
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as read'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
            
            $deleted = $stmt->rowCount();
            
            if ($deleted > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification deleted'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Notification not found or already deleted'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        } catch (\PDOException $e) {
            error_log("NotificationController::delete SQL Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            
            $deleted = $stmt->rowCount();
            
            echo json_encode([
                'success' => true,
                'message' => 'All notifications cleared',
                'deleted_count' => $deleted
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\PDOException $e) {
            error_log("NotificationController::clearAll SQL Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Create notification (internal method, can be called from other controllers)
     */
    public static function create($userId, $type, $title, $message, $relatedType = null, $relatedId = null, $patientId = null)
    {
        $pdo = Database::getInstance()->getConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, related_type, related_id, patient_id, is_read)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0)
        ");
        
        $stmt->execute([$userId, $type, $title, $message, $relatedType, $relatedId, $patientId]);
        
        return $pdo->lastInsertId();
    }

    /**
     * Create notification for admin (system notification)
     */
    public function createSystemNotification()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user || $user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['title']) || empty($data['message'])) {
            echo json_encode(['success' => false, 'message' => 'Title and message are required'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // Get all users or specific users
        $userIds = [];
        if (isset($data['user_ids']) && is_array($data['user_ids']) && !empty($data['user_ids'])) {
            $userIds = $data['user_ids'];
        } else {
            // Send to all users
            $stmt = $this->pdo->query("SELECT id FROM users");
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $userIds = array_column($users, 'id');
        }

        $created = 0;
        foreach ($userIds as $userId) {
            self::create(
                $userId,
                $data['type'] ?? 'system',
                $data['title'],
                $data['message'],
                $data['related_type'] ?? null,
                $data['related_id'] ?? null,
                $data['patient_id'] ?? null
            );
            $created++;
        }

        echo json_encode([
            'success' => true,
            'message' => "Notification sent to {$created} user(s)",
            'created' => $created
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

