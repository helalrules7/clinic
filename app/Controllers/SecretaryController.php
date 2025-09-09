<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use App\Config\Constants;

class SecretaryController
{
    private $auth;
    private $view;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Require secretary authentication (or admin in View As mode)
        $this->auth->requireRole(['secretary', 'admin']);
    }

    public function dashboard()
    {
        $user = $this->auth->user();
        
        // Get today's statistics
        $today = date('Y-m-d');
        $stats = $this->getTodayStats($today);
        
        // Get today's appointments
        $todayAppointments = $this->getTodayAppointments($today);
        
        // Get recent payments
        $recentPayments = $this->getRecentPayments();
        
        $content = $this->view->render('secretary/dashboard', [
            'stats' => $stats,
            'todayAppointments' => $todayAppointments,
            'recentPayments' => $recentPayments
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - لوحة تحكم السكرتارية',
            'pageTitle' => 'لوحة التحكم',
            'pageSubtitle' => 'مرحباً بعودتك، ' . $user['name'],
            'content' => $content
        ]);
    }

    public function bookings()
    {
        $user = $this->auth->user();
        
        // Get all doctors for booking form
        $doctors = $this->getAllDoctors();
        
        // Get today's bookings
        $todayBookings = $this->getTodayBookings();
        
        $content = $this->view->render('secretary/bookings', [
            'doctors' => $doctors,
            'todayBookings' => $todayBookings
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - إدارة الحجوزات',
            'pageTitle' => 'إدارة الحجوزات',
            'pageSubtitle' => 'إنشاء وإدارة المواعيد',
            'content' => $content
        ]);
    }

    public function payments()
    {
        $user = $this->auth->user();
        
        // Get recent payments
        $recentPayments = $this->getRecentPayments(50);
        
        // Get payment statistics
        $paymentStats = $this->getPaymentStats();
        
        $content = $this->view->render('secretary/payments', [
            'recentPayments' => $recentPayments,
            'paymentStats' => $paymentStats
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - إدارة المدفوعات',
            'pageTitle' => 'إدارة المدفوعات',
            'pageSubtitle' => 'تتبع وإدارة المدفوعات',
            'content' => $content
        ]);
    }

    public function patients()
    {
        $user = $this->auth->user();
        
        // Get patients with pagination
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $patients = $this->getPatients($page, $search);
        
        $content = $this->view->render('secretary/patients', [
            'patients' => $patients,
            'currentPage' => $page,
            'search' => $search
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - إدارة المرضى',
            'pageTitle' => 'إدارة المرضى',
            'pageSubtitle' => 'عرض وإدارة سجلات المرضى',
            'content' => $content
        ]);
    }

    public function newPatient()
    {
        $user = $this->auth->user();
        
        $content = $this->view->render('secretary/new-patient', []);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - مريض جديد',
            'pageTitle' => 'إضافة مريض جديد',
            'pageSubtitle' => 'تسجيل مريض جديد',
            'content' => $content
        ]);
    }

    public function createPatient()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new \Exception('Invalid CSRF token');
            }
            
            // Validate input
            $rules = [
                'first_name' => 'required|max:50',
                'last_name' => 'required|max:50',
                'phone' => 'required|phone',
                'gender' => 'in:Male,Female,Other'
            ];
            
            $data = $_POST;
            if (!$this->view->validator->validate($data, $rules)) {
                throw new \Exception('Validation failed');
            }
            
            // Create patient
            $patientId = $this->createPatientRecord($data);
            
            if ($patientId) {
                // Create timeline event
                $this->createTimelineEvent($patientId, null, 'Booking', 'New patient registered');
                
                header('Location: /secretary/patients?success=Patient created successfully');
                exit;
            } else {
                throw new \Exception('Failed to create patient');
            }
            
        } catch (\Exception $e) {
            header('Location: /secretary/patients/new?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function viewPatient($id)
    {
        $user = $this->auth->user();
        
        // Get patient details
        $patient = $this->getPatient($id);
        if (!$patient) {
            http_response_code(404);
            echo "Patient not found";
            return;
        }
        
        // Get patient timeline
        $timeline = $this->getPatientTimeline($id);
        
        // Get patient appointments
        $appointments = $this->getPatientAppointments($id);
        
        // Get patient payments
        $payments = $this->getPatientPayments($id);
        
        $content = $this->view->render('secretary/patient', [
            'patient' => $patient,
            'timeline' => $timeline,
            'appointments' => $appointments,
            'payments' => $payments
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - ملف المريض',
            'pageTitle' => 'ملف المريض',
            'pageSubtitle' => $patient['first_name'] . ' ' . $patient['last_name'],
            'content' => $content
        ]);
    }

    public function viewInvoice($id)
    {
        $user = $this->auth->user();
        
        // Get invoice details
        $invoice = $this->getInvoice($id);
        if (!$invoice) {
            http_response_code(404);
            echo "Invoice not found";
            return;
        }
        
        // Get invoice items
        $items = $this->getInvoiceItems($id);
        
        $content = $this->view->render('secretary/invoice', [
            'invoice' => $invoice,
            'items' => $items
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - الفاتورة',
            'pageTitle' => 'تفاصيل الفاتورة',
            'pageSubtitle' => 'فاتورة رقم #' . $invoice['invoice_no'],
            'content' => $content
        ]);
    }

    // Helper methods
    private function getTodayStats($date)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'Booked' THEN 1 ELSE 0 END) as booked,
                SUM(CASE WHEN status = 'CheckedIn' THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
            FROM appointments 
            WHERE date = ?
        ");
        $stmt->execute([$date]);
        return $stmt->fetch();
    }

    private function getTodayAppointments($date)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone, d.display_name as doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.date = ?
            ORDER BY a.start_time
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    private function getRecentPayments($limit = 10)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, pt.first_name, pt.last_name, u.name as received_by_name
            FROM payments p
            JOIN patients pt ON p.patient_id = pt.id
            JOIN users u ON p.received_by = u.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    private function getAllDoctors()
    {
        $stmt = $this->pdo->prepare("
            SELECT d.*, u.name as user_name
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE u.is_active = 1
            ORDER BY d.display_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getTodayBookings()
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone, d.display_name as doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.date = CURDATE()
            ORDER BY a.start_time
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getPaymentStats()
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_payments,
                SUM(amount) as total_amount,
                SUM(discount_amount) as total_discounts,
                SUM(CASE WHEN is_exempt = 1 THEN 1 ELSE 0 END) as total_exemptions
            FROM payments 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        return $stmt->fetch();
    }

    private function getPatients($page = 1, $search = '')
    {
        $limit = Constants::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            // Check if search looks like a phone number
            if ($this->isPhoneNumberSearch($search)) {
                $whereClause = "WHERE " . $this->buildPhoneSearchWhereClause($search);
                $params = $this->buildPhoneSearchParams($search);
            } else {
                $whereClause = "WHERE first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm, $searchTerm];
            }
        }
        
        $sql = "
            SELECT p.*, 
                   COUNT(DISTINCT a.id) as total_appointments,
                   COUNT(DISTINCT py.id) as total_payments
            FROM patients p
            LEFT JOIN appointments a ON p.id = a.patient_id
            LEFT JOIN payments py ON p.id = py.patient_id
            {$whereClause}
            GROUP BY p.id
            ORDER BY p.last_name, p.first_name
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function getPatient($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, mh.allergies, mh.medications, mh.systemic_history, mh.ocular_history
            FROM patients p
            LEFT JOIN medical_history mh ON p.id = mh.patient_id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getPatientTimeline($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT te.*, u.name as actor_name
            FROM timeline_events te
            LEFT JOIN users u ON te.actor_user_id = u.id
            WHERE te.patient_id = ?
            ORDER BY te.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    private function getPatientAppointments($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, d.display_name as doctor_name
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = ?
            ORDER BY a.date DESC, a.start_time DESC
            LIMIT 20
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    private function getPatientPayments($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.name as received_by_name
            FROM payments p
            JOIN users u ON p.received_by = u.id
            WHERE p.patient_id = ?
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    private function getInvoice($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*, p.first_name, p.last_name, p.phone, d.display_name as doctor_name
            FROM invoices i
            JOIN patients p ON i.patient_id = p.id
            LEFT JOIN doctors d ON i.doctor_id = d.id
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Check if the search query looks like a phone number
     * This method detects Egyptian mobile numbers in various formats:
     * - 01234567890 (with 0 prefix)
     * - +201234567890 (with +20 prefix)
     * - 1234567890 (clean number)
     */
    private function isPhoneNumberSearch($query)
    {
        // Remove common phone prefixes and check if it's mostly digits
        $cleanQuery = preg_replace('/^(\+20|0)/', '', $query);
        $cleanQuery = preg_replace('/[^0-9]/', '', $cleanQuery);
        
        // If it's 9-11 digits, it's likely a phone number
        // Also check if it starts with 1 (Egyptian mobile numbers)
        return strlen($cleanQuery) >= 9 && strlen($cleanQuery) <= 11 && substr($cleanQuery, 0, 1) === '1';
    }

    /**
     * Build WHERE clause for phone search
     * This creates a comprehensive search that covers:
     * - Primary phone numbers
     * - Alternative phone numbers
     * - Names and national IDs (for fallback results)
     */
    private function buildPhoneSearchWhereClause($query)
    {
        $cleanQuery = $this->normalizePhoneNumber($query);
        $searchPatterns = $this->generatePhoneSearchPatterns($cleanQuery);
        
        $conditions = [];
        foreach ($searchPatterns as $pattern) {
            $conditions[] = "p.phone LIKE ? OR p.alt_phone LIKE ?";
        }
        
        // Also search in names and national ID for comprehensive results
        // This ensures we don't miss patients if phone search fails
        $conditions[] = "p.first_name LIKE ? OR p.last_name LIKE ? OR p.national_id LIKE ?";
        
        return implode(' OR ', $conditions);
    }

    /**
     * Build parameters for phone search
     * This method ensures all search patterns are properly mapped to SQL parameters
     */
    private function buildPhoneSearchParams($query)
    {
        $cleanQuery = $this->normalizePhoneNumber($query);
        $searchPatterns = $this->generatePhoneSearchPatterns($cleanQuery);
        
        $params = [];
        
        // Add phone search parameters (each pattern needs 2 parameters for phone and alt_phone)
        foreach ($searchPatterns as $pattern) {
            $params[] = $pattern; // for p.phone
            $params[] = $pattern; // for p.alt_phone
        }
        
        // Add name and national ID search parameters
        // These provide fallback search capabilities
        $nameSearchTerm = "%{$query}%";
        $params[] = $nameSearchTerm; // for first_name
        $params[] = $nameSearchTerm; // for last_name
        $params[] = $nameSearchTerm; // for national_id
        
        return $params;
    }

    /**
     * Normalize phone number by removing common prefixes and formatting
     * This method handles various phone number formats:
     * - +201234567890 -> 1234567890
     * - 01234567890 -> 1234567890
     * - 201234567890 -> 1234567890
     */
    private function normalizePhoneNumber($phone)
    {
        // Remove +20, 0, spaces, dashes, etc.
        $phone = preg_replace('/^(\+20|0)/', '', $phone);
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return $phone;
    }

    /**
     * Generate multiple search patterns for phone number search
     * This creates patterns for different phone number formats:
     * - 01234567890 (with 0 prefix)
     * - +201234567890 (with +20 prefix)
     * - 201234567890 (with 20 prefix)
     * - 1234567890 (clean number)
     */
    private function generatePhoneSearchPatterns($cleanQuery)
    {
        $patterns = [];
        
        // Add the clean query as is
        $patterns[] = "%{$cleanQuery}%";
        
        // Add with +20 prefix
        $patterns[] = "%+20{$cleanQuery}%";
        
        // Add with 0 prefix
        $patterns[] = "%0{$cleanQuery}%";
        
        // Add with 20 prefix (without +)
        $patterns[] = "%20{$cleanQuery}%";
        
        // If query starts with 1, also search for it without the 1
        // This allows searching with '01' to find '+201234567890'
        if (substr($cleanQuery, 0, 1) === '1' && strlen($cleanQuery) > 9) {
            $patterns[] = "%" . substr($cleanQuery, 1) . "%";
            $patterns[] = "%+20" . substr($cleanQuery, 1) . "%";
            $patterns[] = "%0" . substr($cleanQuery, 1) . "%";
            $patterns[] = "%20" . substr($cleanQuery, 1) . "%";
        }
        
        return $patterns;
    }

    private function getInvoiceItems($invoiceId)
    {
        // For now, we'll return payments as invoice items
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.name as received_by_name
            FROM payments p
            JOIN users u ON p.received_by = u.id
            WHERE p.invoice_id = ?
            ORDER BY p.created_at
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    private function createPatientRecord($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO patients (first_name, last_name, dob, gender, phone, alt_phone, address, national_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['dob'] ?? null,
            $data['gender'] ?? null,
            $data['phone'],
            $data['alt_phone'] ?? null,
            $data['address'] ?? null,
            $data['national_id'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function createTimelineEvent($patientId, $appointmentId, $eventType, $summary)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO timeline_events (patient_id, appointment_id, actor_user_id, event_type, event_summary)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $patientId,
            $appointmentId,
            $this->auth->user()['id'],
            $eventType,
            $summary
        ]);
    }

    private function validateCsrfToken()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}
