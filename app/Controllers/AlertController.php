<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use App\Models\AlertModel;
use App\Services\PushNotificationService;

class AlertController
{
    private $auth;
    private $view;
    private $alertModel;
    private $pushService;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->alertModel = new AlertModel();
        $this->pushService = new PushNotificationService();
        
        // Require doctor authentication
        $this->auth->requireRole(['doctor', 'admin']);
    }

    /**
     * Show alerts management page
     */
    public function index()
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $alerts = $this->alertModel->getByDoctor($doctorId, ['is_dismissed' => 0]);
        
        $content = $this->view->render('doctor/alerts/index', [
            'alerts' => $alerts
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Alerts Management',
            'pageTitle' => 'Alerts Management',
            'pageSubtitle' => 'Manage your notifications and reminders',
            'content' => $content
        ]);
    }
    
    /**
     * Get all alerts for doctor (API endpoint) with pagination
     */
    public function getAllAlerts()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 10;
        
        // Get all alerts
        $allAlerts = $this->alertModel->getByDoctor($doctorId, []);
        $totalItems = count($allAlerts);

        // At-a-glance status breakdown (computed across ALL alerts, not just
        // the current page) for the overview stat cards.
        $now = new \DateTime();
        $stats = ['total' => $totalItems, 'active' => 0, 'past_due' => 0, 'dismissed' => 0, 'inactive' => 0];
        foreach ($allAlerts as $a) {
            if ((int)($a['is_dismissed'] ?? 0) === 1) {
                $stats['dismissed']++;
                continue;
            }
            if ((int)($a['is_active'] ?? 0) === 1) {
                $dt = null;
                try {
                    $dt = new \DateTime(trim(($a['alert_date'] ?? '') . ' ' . ($a['alert_time'] ?? '00:00:00')));
                } catch (\Exception $e) {
                    $dt = null;
                }
                if ($dt && $dt < $now) {
                    $stats['past_due']++;
                } else {
                    $stats['active']++;
                }
            } else {
                $stats['inactive']++;
            }
        }

        // Calculate pagination
        $totalPages = $perPage > 0 ? ceil($totalItems / $perPage) : 1;
        $offset = ($page - 1) * $perPage;
        
        // Paginate alerts
        if ($perPage > 0 && $perPage < $totalItems) {
            $alerts = array_slice($allAlerts, $offset, $perPage);
        } else {
            $alerts = $allAlerts;
            $totalPages = 1;
        }
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'per_page' => $perPage
            ]
        ]);
    }

    /**
     * Get alerts for a specific patient (API endpoint)
     */
    public function getPatientAlerts($patientId)
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        if (!$patientId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Patient ID is required'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        
        $alerts = $this->alertModel->getByPatient($doctorId, $patientId, []);
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Get single alert by ID (API endpoint)
     */
    public function get($alertId)
    {
        header('Content-Type: application/json');
        
        try {
            $user = $this->auth->user();
            if (!$user) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                return;
            }
            
            $doctorId = $this->getDoctorId($user['id']);
            
            if (!$alertId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Alert ID is required'
                ]);
                return;
            }
            
            $alert = $this->alertModel->getById($alertId, $doctorId);
            
            if ($alert) {
                echo json_encode([
                    'success' => true,
                    'alert' => $alert
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Alert not found'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ]);
        }
    }

    /**
     * Create new alert (API endpoint)
     */
    public function create()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }
        
        // Validate required fields
        if (empty($data['message']) || empty($data['alert_date']) || empty($data['alert_time'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Message, date, and time are required'
            ]);
            return;
        }
        
        // Check if alert already exists for this note (by message, no patient/appointment)
        $existingAlert = null;
        if (empty($data['patient_id']) && empty($data['appointment_id'])) {
            // Normalize message for comparison (trim whitespace)
            $normalizedMessage = trim($data['message']);
            $existingAlert = $this->alertModel->getByMessage($doctorId, $normalizedMessage);
        }
        
        if ($existingAlert) {
            // Update existing alert
            $updateData = [
                'message' => $data['message'],
                'alert_date' => $data['alert_date'],
                'alert_time' => $data['alert_time'],
                'repeat_count' => intval($data['repeat_count'] ?? 1),
                'repeat_interval' => intval($data['repeat_interval'] ?? 0),
                'is_active' => 1,
                'is_dismissed' => 0
            ];
            
            $result = $this->alertModel->update($existingAlert['id'], $updateData, $doctorId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Alert updated successfully',
                    'alert_id' => $existingAlert['id'],
                    'updated' => true
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update alert'
                ]);
            }
        } else {
            // Create new alert
            $alertData = [
                'doctor_id' => $doctorId,
                'patient_id' => $data['patient_id'] ?? null,
                'appointment_id' => $data['appointment_id'] ?? null,
                'message' => $data['message'],
                'alert_date' => $data['alert_date'],
                'alert_time' => $data['alert_time'],
                'repeat_count' => intval($data['repeat_count'] ?? 1),
                'repeat_interval' => intval($data['repeat_interval'] ?? 0)
            ];
            
            $alertId = $this->alertModel->create($alertData);
            
            if ($alertId) {
                // Create notification for alert
                try {
                    $patientName = '';
                    if (!empty($data['patient_id'])) {
                        $pdo = \App\Config\Database::getInstance()->getConnection();
                        $patientStmt = $pdo->prepare("SELECT first_name, last_name FROM patients WHERE id = ?");
                        $patientStmt->execute([$data['patient_id']]);
                        $patient = $patientStmt->fetch(\PDO::FETCH_ASSOC);
                        if ($patient) {
                            $patientName = trim($patient['first_name'] . ' ' . $patient['last_name']);
                        }
                    }
                    
                    $notificationTitle = 'New Alert Created';
                    $notificationMessage = $data['message'];
                    if ($patientName) {
                        $notificationMessage .= " - Patient: {$patientName}";
                    }
                    
                    \App\Controllers\NotificationController::create(
                        $user['id'],
                        'alert',
                        $notificationTitle,
                        $notificationMessage,
                        'alert',
                        $alertId,
                        $data['patient_id'] ?? null
                    );
                } catch (\Exception $e) {
                    // Continue even if notification creation fails
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Alert created successfully',
                    'alert_id' => $alertId,
                    'updated' => false
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create alert'
                ]);
            }
        }
    }

    /**
     * Get alerts for today (API endpoint)
     */
    public function getTodayAlerts()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        
        $alerts = $this->alertModel->getTodayAlerts(null);
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts
        ]);
    }

    /**
     * Get active alerts for current time (API endpoint)
     */
    public function getActiveAlerts()
    {
        header('Content-Type: application/json');
        
        try {
        $user = $this->auth->user();
            if (!$user) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                return;
            }
            
        $doctorId = $this->getDoctorId($user['id']);
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $time = $_GET['time'] ?? date('H:i:s');
        
        $alerts = $this->alertModel->getActiveAlertsForTime($doctorId, $date, $time);
        
        // Send push notifications for new alerts
        if (!empty($alerts)) {
            foreach ($alerts as $alert) {
                // Check if this alert was already sent (to avoid duplicates)
                $alertKey = 'push_sent_' . $alert['id'] . '_' . $alert['alert_date'] . '_' . $alert['alert_time'];
                if (!isset($_SESSION[$alertKey])) {
                        try {
                    $this->sendPushNotificationForAlert($user['id'], $alert);
                    $_SESSION[$alertKey] = true;
                        } catch (\Exception $e) {
                            // Continue even if push notification fails
                        }
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts
        ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'System error. Please try again.',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send push notification for an alert
     */
    private function sendPushNotificationForAlert($userId, $alert)
    {
        try {
            $patientName = '';
            if (!empty($alert['patient_first_name']) && !empty($alert['patient_last_name'])) {
                $patientName = $alert['patient_first_name'] . ' ' . $alert['patient_last_name'];
            }
            
            $title = 'New Alert';
            $body = $alert['message'] ?? 'You have a new alert';
            if ($patientName) {
                $body .= ' - ' . $patientName;
            }
            
            $data = [
                'alert_id' => $alert['id'],
                'patient_id' => $alert['patient_id'] ?? null,
                'url' => !empty($alert['patient_id']) 
                    ? '/doctor/patients/' . $alert['patient_id'] 
                    : '/doctor/alerts'
            ];
            
            $this->pushService->sendPushNotification($userId, $title, $body, $data);
        } catch (\Exception $e) {
            // Silent fail for push notifications
        }
    }

    /**
     * Dismiss alert (snooze)
     */
    public function dismiss()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        $alertId = $data['alert_id'] ?? null;
        
        if (!$alertId) {
            echo json_encode([
                'success' => false,
                'message' => 'Alert ID is required'
            ]);
            return;
        }
        
        $result = $this->alertModel->dismiss($alertId, $doctorId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Alert dismissed'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to dismiss alert'
            ]);
        }
    }

    /**
     * Toggle alert active status (activate/deactivate)
     */
    public function toggleStatus($id)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $alertId = $id ?? null;
        
        if (!$alertId) {
            echo json_encode([
                'success' => false,
                'message' => 'Alert ID is required'
            ]);
            return;
        }
        
        // Get current alert status
        $alert = $this->alertModel->getById($alertId, $doctorId);
        
        if (!$alert) {
            echo json_encode([
                'success' => false,
                'message' => 'Alert not found'
            ]);
            return;
        }
        
        // Toggle is_active status. AlertModel::update does a full-row UPDATE (message/date/time
        // are NOT NULL), so carry the existing values over instead of sending a partial array.
        $newStatus = $alert['is_active'] == 1 ? 0 : 1;

        $updateData = [
            'message' => $alert['message'],
            'alert_date' => $alert['alert_date'],
            'alert_time' => $alert['alert_time'],
            'repeat_count' => $alert['repeat_count'] ?? 1,
            'repeat_interval' => $alert['repeat_interval'] ?? 0,
            'is_active' => $newStatus,
            'is_dismissed' => $alert['is_dismissed'] ?? 0
        ];

        $result = $this->alertModel->update($alertId, $updateData, $doctorId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => $newStatus == 1 ? 'Alert activated' : 'Alert deactivated',
                'is_active' => $newStatus
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update alert status'
            ]);
        }
    }

    /**
     * Delete alert
     */
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $alertId = $id ?? null;
        
        if (!$alertId) {
            echo json_encode([
                'success' => false,
                'message' => 'Alert ID is required'
            ]);
            return;
        }
        
        $result = $this->alertModel->delete($alertId, $doctorId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Alert deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete alert'
            ]);
        }
    }

    /**
     * Disable all alerts for doctor
     */
    public function disableAllAlerts()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        if (!$doctorId) {
            echo json_encode([
                'success' => false,
                'message' => 'Doctor not found'
            ]);
            return;
        }
        
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        try {
            $stmt = $pdo->prepare("UPDATE alerts SET is_active = 0 WHERE doctor_id = ?");
            $result = $stmt->execute([$doctorId]);
            $affectedRows = $stmt->rowCount();
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => "All alerts have been disabled successfully",
                    'affected_rows' => $affectedRows
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to disable all alerts'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete all alerts for doctor
     */
    public function deleteAllAlerts()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        if (!$doctorId) {
            echo json_encode([
                'success' => false,
                'message' => 'Doctor not found'
            ]);
            return;
        }
        
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        try {
            $stmt = $pdo->prepare("DELETE FROM alerts WHERE doctor_id = ?");
            $result = $stmt->execute([$doctorId]);
            $affectedRows = $stmt->rowCount();
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => "All alerts have been deleted successfully",
                    'affected_rows' => $affectedRows
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete all alerts'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update alert
     */
    public function update($id)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        $alertId = $id ?? null;
        
        if (!$alertId || empty($data['message']) || empty($data['alert_date']) || empty($data['alert_time'])) {
            echo json_encode([
                'success' => false,
                'message' => 'All fields are required'
            ]);
            return;
        }
        
        $updateData = [
            'message' => $data['message'],
            'alert_date' => $data['alert_date'],
            'alert_time' => $data['alert_time'],
            'repeat_count' => intval($data['repeat_count'] ?? 1),
            'repeat_interval' => intval($data['repeat_interval'] ?? 0),
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
            'is_dismissed' => isset($data['is_dismissed']) ? intval($data['is_dismissed']) : 0
        ];
        
        $result = $this->alertModel->update($alertId, $updateData, $doctorId);
        
        if ($result) {
            // Create notification for alert update
            try {
                $patientName = '';
                if (!empty($data['patient_id'])) {
                    $pdo = \App\Config\Database::getInstance()->getConnection();
                    $patientStmt = $pdo->prepare("SELECT first_name, last_name FROM patients WHERE id = ?");
                    $patientStmt->execute([$data['patient_id']]);
                    $patient = $patientStmt->fetch(\PDO::FETCH_ASSOC);
                    if ($patient) {
                        $patientName = trim($patient['first_name'] . ' ' . $patient['last_name']);
                    }
                }
                
                $notificationTitle = 'Alert Updated';
                $notificationMessage = $data['message'];
                if ($patientName) {
                    $notificationMessage .= " - Patient: {$patientName}";
                }
                
                \App\Controllers\NotificationController::create(
                    $user['id'],
                    'alert',
                    $notificationTitle,
                    $notificationMessage,
                    'alert',
                    $alertId,
                    $data['patient_id'] ?? null
                );
            } catch (\Exception $e) {
                // Continue even if notification creation fails
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Alert updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update alert'
            ]);
        }
    }

    /**
     * Get doctor ID from user ID
     */
    private function getDoctorId($userId)
    {
        // In this system, doctor_id is the same as user_id for doctors
        // If there's a separate doctors table, query it here
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("SELECT id FROM doctors WHERE user_id = :user_id LIMIT 1");
            $stmt->execute([':user_id' => $userId]);
            $doctor = $stmt->fetch();
            $doctorId = $doctor ? $doctor['id'] : $userId;
            return $doctorId;
        } catch (\Exception $e) {
            // If doctors table doesn't exist or query fails, use user_id directly
            return $userId;
        }
    }
}

