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
            
            // Get doctor ID for alerts
            $doctorStmt = $this->pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
            $doctorStmt->execute([$user['id']]);
            $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);
            
            // Get alerts for each note
            if ($doctor) {
                $alertModel = new \App\Models\AlertModel();
                
                foreach ($notes as &$note) {
                    // Use HTML content (same as what we send when creating alert)
                    // Normalize content for comparison (trim whitespace)
                    $noteContent = trim($note['content'] ?? '');
                    if (!empty($noteContent)) {
                        $alert = $alertModel->getByMessage($doctor['id'], $noteContent);
                        
                        $note['alert'] = $alert ? [
                            'id' => $alert['id'],
                            'alert_date' => $alert['alert_date'],
                            'alert_time' => $alert['alert_time']
                        ] : null;
                    } else {
                        $note['alert'] = null;
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'notes' => $notes
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
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
            
            // Get old content before update to find associated alert
            $oldStmt = $this->pdo->prepare("SELECT content FROM notes WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldNote = $oldStmt->fetch(PDO::FETCH_ASSOC);
            $oldContent = trim($oldNote['content'] ?? '');
            
            $sql = "UPDATE notes SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Update or delete associated alert if content changed
            if (isset($data['content']) && !empty($oldContent)) {
                // Get doctor ID
                $doctorStmt = $this->pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
                $doctorStmt->execute([$user['id']]);
                $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($doctor) {
                    $alertModel = new \App\Models\AlertModel();
                    $oldAlert = $alertModel->getByMessage($doctor['id'], $oldContent);
                    
                    if ($oldAlert) {
                        $newContent = trim($data['content'] ?? '');
                        if (empty($newContent)) {
                            // Delete alert if note content is now empty
                            $alertModel->delete($oldAlert['id'], $doctor['id']);
                        } else {
                            // Update alert message with new content
                            $alertModel->update($oldAlert['id'], [
                                'message' => $newContent,
                                'alert_date' => $oldAlert['alert_date'],
                                'alert_time' => $oldAlert['alert_time'],
                                'repeat_count' => $oldAlert['repeat_count'],
                                'repeat_interval' => $oldAlert['repeat_interval'],
                                'is_active' => $oldAlert['is_active'],
                                'is_dismissed' => $oldAlert['is_dismissed']
                            ], $doctor['id']);
                        }
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Note updated successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
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
            
            // Get note content before deletion to find associated alert
            $contentStmt = $this->pdo->prepare("SELECT content FROM notes WHERE id = ?");
            $contentStmt->execute([$id]);
            $noteData = $contentStmt->fetch(PDO::FETCH_ASSOC);
            $noteContent = trim($noteData['content'] ?? '');
            
            // Delete the note
            $stmt = $this->pdo->prepare("DELETE FROM notes WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                // Delete associated alert if exists
                if (!empty($noteContent)) {
                    // Get doctor ID
                    $doctorStmt = $this->pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
                    $doctorStmt->execute([$user['id']]);
                    $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($doctor) {
                        $alertModel = new \App\Models\AlertModel();
                        $alert = $alertModel->getByMessage($doctor['id'], $noteContent);
                        
                        if ($alert) {
                            $alertModel->delete($alert['id'], $doctor['id']);
                        }
                    }
                }
                
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
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Delete all notes for the current user
     */
    public function deleteAllNotes()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        
        try {
            // Delete all notes for this user
            $stmt = $this->pdo->prepare("DELETE FROM notes WHERE user_id = ?");
            $result = $stmt->execute([$user['id']]);
            $affectedRows = $stmt->rowCount();
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => "All notes have been deleted successfully",
                    'affected_rows' => $affectedRows
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete all notes'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting all notes: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
}

