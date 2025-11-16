<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use App\Models\AlertModel;

class AlertController
{
    private $auth;
    private $view;
    private $alertModel;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->alertModel = new AlertModel();
        
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
     * Get all alerts for doctor (API endpoint)
     */
    public function getAllAlerts()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $alerts = $this->alertModel->getByDoctor($doctorId, []);
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts
        ]);
    }

    /**
     * Get alerts for a specific patient (API endpoint)
     */
    public function getPatientAlerts($patientId)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        if (!$patientId) {
            echo json_encode([
                'success' => false,
                'message' => 'Patient ID is required'
            ]);
            return;
        }
        
        $alerts = $this->alertModel->getByPatient($doctorId, $patientId, []);
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts
        ]);
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
            error_log("AlertController::get error: " . $e->getMessage());
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
            echo json_encode([
                'success' => true,
                'message' => 'Alert created successfully',
                'alert_id' => $alertId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create alert'
            ]);
        }
    }

    /**
     * Get alerts for today (API endpoint)
     */
    public function getTodayAlerts()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $alerts = $this->alertModel->getTodayAlerts($doctorId);
        
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
        
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $time = $_GET['time'] ?? date('H:i:s');
        
        $alerts = $this->alertModel->getActiveAlertsForTime($doctorId, $date, $time);
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts
        ]);
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
            return $doctor ? $doctor['id'] : $userId;
        } catch (\Exception $e) {
            // If doctors table doesn't exist or query fails, use user_id directly
            return $userId;
        }
    }
}

