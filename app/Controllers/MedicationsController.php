<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use PDO;

class MedicationsController
{
    private $auth;
    private $view;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Check authentication but don't redirect for API calls
        if (!$this->auth->check()) {
            // For API calls, return JSON
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            // For regular pages, redirect to login
            header('Location: /login');
            exit;
        }
        
        // Require doctor or admin role
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                exit;
            }
            http_response_code(403);
            echo "Access denied";
            exit;
        }
    }

    public function index()
    {
        $content = $this->view->render('medications/index', []);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Medications Prescriptions Gallery - Roaya Clinic',
            'pageTitle' => 'Medications Prescriptions',
            'pageSubtitle' => 'View all medication prescriptions grouped by patient',
            'content' => $content
        ]);
    }

    public function getMedicationsPrescriptions()
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? max(1, min(120, (int)$_GET['per_page'])) : 12;
            $offset = ($page - 1) * $perPage;
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;

            // Build query to get medication prescriptions grouped by patient
            // Count distinct appointments (not prescriptions) per patient
            $whereClause = '';
            $params = [];
            
            if ($patientId) {
                $whereClause = 'WHERE a.patient_id = ?';
                $params[] = $patientId;
            }

            $sql = "
                SELECT 
                    a.patient_id,
                    CONCAT(pt.first_name, ' ', pt.last_name) as patient_name,
                    COUNT(DISTINCT p.appointment_id) as appointment_count,
                    MAX(p.created_at) as last_prescription_date
                FROM prescriptions p
                JOIN appointments a ON p.appointment_id = a.id
                JOIN patients pt ON a.patient_id = pt.id
                $whereClause
                GROUP BY a.patient_id, pt.first_name, pt.last_name
                ORDER BY MAX(p.created_at) DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get total count
            $countSql = "
                SELECT COUNT(DISTINCT a.patient_id) as total
                FROM prescriptions p
                JOIN appointments a ON p.appointment_id = a.id
                JOIN patients pt ON a.patient_id = pt.id
                $whereClause
            ";
            
            $countParams = $patientId ? [$patientId] : [];
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute($countParams);
            $totalResult = $countStmt->fetch(\PDO::FETCH_ASSOC);
            $total = $totalResult['total'] ?? 0;
            $totalPages = ceil($total / $perPage);

            // Get latest prescription details for each patient for thumbnail
            foreach ($patients as &$patient) {
                // Get the latest appointment with prescriptions for this patient
                $prescriptionStmt = $this->pdo->prepare("
                    SELECT p.*, a.date as appointment_date, a.start_time as appointment_time, a.id as appointment_id
                    FROM prescriptions p
                    JOIN appointments a ON p.appointment_id = a.id
                    WHERE a.patient_id = ?
                    ORDER BY p.created_at DESC 
                    LIMIT 1
                ");
                $prescriptionStmt->execute([$patient['patient_id']]);
                $latestPrescription = $prescriptionStmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($latestPrescription) {
                    $patient['prescription_data'] = $latestPrescription;
                    $patient['prescription_id'] = $latestPrescription['id'];
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $patients,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total' => $total,
                    'per_page' => $perPage,
                    'has_more' => $page < $totalPages
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error loading medications prescriptions'
            ], 500);
        }
    }

    public function getPatientMedicationsPrescriptions()
    {
        try {
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
            
            if (!$patientId) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Patient ID is required'
                ], 400);
            }

            // Get appointments with prescriptions for this patient
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT a.id as appointment_id, a.date, a.start_time
                FROM appointments a
                JOIN prescriptions p ON a.id = p.appointment_id
                WHERE a.patient_id = ?
                ORDER BY a.date DESC, a.start_time DESC
            ");
            
            $stmt->execute([$patientId]);
            $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $appointmentIds = array_column($appointments, 'appointment_id');
            
            if (empty($appointmentIds)) {
                return $this->jsonResponse([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Get full appointment and prescription data
            $result = [];
            foreach ($appointmentIds as $appointmentId) {
                // Get appointment details
                $appointmentStmt = $this->pdo->prepare("
                    SELECT id, date as appointment_date, start_time as appointment_time, visit_type
                    FROM appointments
                    WHERE id = ?
                ");
                $appointmentStmt->execute([$appointmentId]);
                $appointment = $appointmentStmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$appointment) continue;
                
                // Get prescriptions for this appointment
                $prescriptionStmt = $this->pdo->prepare("
                    SELECT id, drug_name, dose, frequency, duration, route, notes, created_at
                    FROM prescriptions
                    WHERE appointment_id = ?
                    ORDER BY created_at DESC
                ");
                $prescriptionStmt->execute([$appointmentId]);
                $prescriptions = $prescriptionStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                if (empty($prescriptions)) continue;
                
                $result[] = [
                    'appointment_id' => $appointment['id'],
                    'appointment_date' => $appointment['appointment_date'],
                    'appointment_time' => $appointment['appointment_time'],
                    'visit_type' => $appointment['visit_type'],
                    'prescription_count' => count($prescriptions),
                    'last_prescription_date' => $prescriptions[0]['created_at'],
                    'prescriptions' => $prescriptions
                ];
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error loading patient medications prescriptions'
            ], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        
        $json = json_encode($data);
        if ($json === false) {
            $data = ['error' => 'JSON encoding failed'];
            $json = json_encode($data);
        }
        
        echo $json;
        exit;
    }
}

