<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use PDO;

class NotesController
{
    private $auth;
    private $view;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Require doctor authentication (same as AlertController)
        $this->auth->requireRole(['doctor', 'admin']);
    }

    public function index()
    {
        $user = $this->auth->user();
        
        $content = $this->view->render('doctor/notes/index', [
            'user' => $user
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Notes - Roaya Clinic',
            'pageTitle' => 'Notes',
            'pageSubtitle' => 'Manage your personal notes',
            'content' => $content
        ]);
    }

    public function getNotes()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM notes 
                WHERE user_id = ? 
                ORDER BY z_index DESC, created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ensure notes is an array
            if (!is_array($notes)) {
                $notes = [];
            }
            
            echo json_encode([
                'success' => true,
                'notes' => $notes
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Error in getNotes: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading notes'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function getNote($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM notes 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$note) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Note not found'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'note' => $note
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Error in getNote: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function createNote()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $title = $data['title'] ?? null;
            $content = $data['content'] ?? '';
            $backgroundColor = $data['background_color'] ?? '#fbbf24'; // Default: warning yellow
            $positionX = $data['position_x'] ?? 0;
            $positionY = $data['position_y'] ?? 0;
            $width = $data['width'] ?? 300;
            $height = $data['height'] ?? 200;
            $zIndex = $data['z_index'] ?? 1;
            
            $stmt = $this->pdo->prepare("
                INSERT INTO notes (user_id, title, content, background_color, position_x, position_y, width, height, z_index)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user['id'],
                $title,
                $content,
                $backgroundColor,
                $positionX,
                $positionY,
                $width,
                $height,
                $zIndex
            ]);
            
            $noteId = $this->pdo->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'note_id' => $noteId,
                'message' => 'Note created successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Error in createNote: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while creating the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function updateNote($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // Verify note belongs to user
            $stmt = $this->pdo->prepare("SELECT user_id FROM notes WHERE id = ?");
            $stmt->execute([$id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$note) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Note not found'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            if ($note['user_id'] != $user['id']) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $updates = [];
            $params = [];
            
            if (isset($data['title'])) {
                $updates[] = "title = ?";
                $params[] = $data['title'];
            }
            if (isset($data['content'])) {
                $updates[] = "content = ?";
                $params[] = $data['content'];
            }
            if (isset($data['background_color'])) {
                $updates[] = "background_color = ?";
                $params[] = $data['background_color'];
            }
            if (isset($data['position_x'])) {
                $updates[] = "position_x = ?";
                $params[] = $data['position_x'];
            }
            if (isset($data['position_y'])) {
                $updates[] = "position_y = ?";
                $params[] = $data['position_y'];
            }
            if (isset($data['width'])) {
                $updates[] = "width = ?";
                $params[] = $data['width'];
            }
            if (isset($data['height'])) {
                $updates[] = "height = ?";
                $params[] = $data['height'];
            }
            if (isset($data['z_index'])) {
                $updates[] = "z_index = ?";
                $params[] = $data['z_index'];
            }
            
            if (empty($updates)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'No changes to update'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $params[] = $id;
            
            $sql = "UPDATE notes SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                'success' => true,
                'message' => 'Note updated successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            error_log("Error in updateNote: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while updating the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function deleteNote($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        
        try {
            // Verify note belongs to user
            $stmt = $this->pdo->prepare("SELECT user_id FROM notes WHERE id = ?");
            $stmt->execute([$id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$note) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Note not found'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            if ($note['user_id'] != $user['id']) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM notes WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Note deleted successfully'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete note'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            exit;
        } catch (\Exception $e) {
            error_log("Error deleting note: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
}

