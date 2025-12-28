<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use PDO;

class GlassesController
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
        $content = $this->view->render('glasses/index', []);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Glasses Prescriptions Gallery - Roaya Clinic',
            'pageTitle' => 'Glasses Prescriptions',
            'pageSubtitle' => 'View all glasses prescriptions grouped by patient',
            'content' => $content
        ]);
    }

    public function getGlassesPrescriptions()
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 12;
            $offset = ($page - 1) * $perPage;
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;

            // Build query to get glasses prescriptions grouped by patient
            $whereClause = '';
            $params = [];
            
            if ($patientId) {
                $whereClause = 'WHERE a.patient_id = ?';
                $params[] = $patientId;
            }

            $sql = "
                SELECT 
                    a.patient_id,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    COUNT(DISTINCT gp.id) as prescription_count,
                    MAX(gp.created_at) as last_prescription_date
                FROM glasses_prescriptions gp
                JOIN appointments a ON gp.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                $whereClause
                GROUP BY a.patient_id, p.first_name, p.last_name
                ORDER BY MAX(gp.created_at) DESC
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
                FROM glasses_prescriptions gp
                JOIN appointments a ON gp.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                $whereClause
            ";
            
            $countParams = $patientId ? [$patientId] : [];
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute($countParams);
            $totalResult = $countStmt->fetch(\PDO::FETCH_ASSOC);
            $total = $totalResult['total'] ?? 0;
            $totalPages = ceil($total / $perPage);

            // Generate thumbnail/preview data for each patient
            foreach ($patients as &$patient) {
                // Get the latest prescription details for thumbnail
                $prescriptionStmt = $this->pdo->prepare("
                    SELECT * FROM glasses_prescriptions 
                    WHERE appointment_id IN (
                        SELECT id FROM appointments WHERE patient_id = ?
                    )
                    ORDER BY created_at DESC 
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
                'message' => 'Error loading glasses prescriptions'
            ], 500);
        }
    }

    public function getPatientGlassesPrescriptions()
    {
        try {
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
            
            if (!$patientId) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Patient ID is required'
                ], 400);
            }

            $stmt = $this->pdo->prepare("
                SELECT 
                    gp.*,
                    a.date as appointment_date,
                    a.start_time as appointment_time,
                    a.id as appointment_id,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    p.id as patient_id
                FROM glasses_prescriptions gp
                JOIN appointments a ON gp.appointment_id = a.id
                JOIN patients p ON a.patient_id = p.id
                WHERE a.patient_id = ?
                ORDER BY gp.created_at DESC
            ");
            
            $stmt->execute([$patientId]);
            $prescriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'data' => $prescriptions
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error loading patient glasses prescriptions'
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

