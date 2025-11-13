<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use PDO;

class MediaController
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
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Require doctor or admin role
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }
    }

    public function index()
    {
        $content = $this->view->render('media/index', []);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Media Gallery - Roaya Clinic',
            'pageTitle' => 'Media Gallery',
            'pageSubtitle' => 'View all patient images and attachments',
            'content' => $content
        ]);
    }

    public function getMedia()
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
            $perPage = 12;
            $offset = ($page - 1) * $perPage;

            // Build WHERE clause
            $whereClause = "WHERE pa.mime_type LIKE 'image/%'";
            if ($patientId) {
                $whereClause .= " AND pa.patient_id = :patient_id";
            }

            // Get all image attachments grouped by patient
            $stmt = $this->pdo->prepare("
                SELECT 
                    pa.patient_id,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    COUNT(*) as image_count,
                    MIN(pa.id) as first_image_id,
                    MIN(pa.file_path) as first_image_path,
                    MIN(pa.original_filename) as first_image_filename,
                    MIN(pa.created_at) as first_image_date
                FROM patient_attachments pa
                INNER JOIN patients p ON pa.patient_id = p.id
                $whereClause
                GROUP BY pa.patient_id, p.first_name, p.last_name
                ORDER BY first_image_date DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            if ($patientId) {
                $stmt->bindValue(':patient_id', $patientId, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countQuery = "
                SELECT COUNT(DISTINCT patient_id) as total
                FROM patient_attachments pa
                $whereClause
            ";
            $countStmt = $this->pdo->prepare($countQuery);
            if ($patientId) {
                $countStmt->bindValue(':patient_id', $patientId, PDO::PARAM_INT);
            }
            $countStmt->execute();
            $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
            $total = $totalResult ? (int)$totalResult['total'] : 0;

            // Process images to get proper URLs
            foreach ($patients as &$patient) {
                $patient['thumbnail_url'] = $this->getImageUrl($patient['first_image_path']);
                $patient['view_url'] = $this->getImageUrl($patient['first_image_path']);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $patients,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage),
                    'has_more' => ($offset + $perPage) < $total
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Media gallery error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error loading media: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPatientImages()
    {
        try {
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

            if (!$patientId) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Patient ID required'
                ], 400);
            }

            // Get all images for this patient
            $stmt = $this->pdo->prepare("
                SELECT 
                    pa.id,
                    pa.patient_id,
                    pa.appointment_id,
                    pa.original_filename,
                    pa.file_path,
                    pa.created_at,
                    a.id as appointment_id_exists,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name
                FROM patient_attachments pa
                INNER JOIN patients p ON pa.patient_id = p.id
                LEFT JOIN appointments a ON pa.appointment_id = a.id
                WHERE pa.patient_id = ? AND pa.mime_type LIKE 'image/%'
                ORDER BY pa.created_at DESC
            ");
            
            $stmt->execute([$patientId]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process images to get proper URLs and links
            foreach ($images as &$image) {
                $image['view_url'] = $this->getImageUrl($image['file_path']);
                $image['download_url'] = '/api/attachments/download/' . $image['id'];
                
                // Determine source link
                if ($image['appointment_id'] && $image['appointment_id_exists']) {
                    $image['source_type'] = 'appointment';
                    $image['source_link'] = '/doctor/appointments/' . $image['appointment_id'];
                    $image['source_label'] = 'View Appointment';
                } else {
                    $image['source_type'] = 'patient';
                    $image['source_link'] = '/doctor/patients/' . $image['patient_id'];
                    $image['source_label'] = 'View Patient';
                }
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $images
            ]);

        } catch (\Exception $e) {
            error_log("Get patient images error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error loading images: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getImageUrl($filePath)
    {
        // Handle different path formats
        if (strpos($filePath, 'storage/') === 0) {
            return '/' . $filePath;
        } elseif (strpos($filePath, 'uploads/') === 0) {
            return '/' . $filePath;
        } elseif (strpos($filePath, '/') === 0) {
            return $filePath;
        } else {
            return '/storage/uploads/attachments/' . basename($filePath);
        }
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

