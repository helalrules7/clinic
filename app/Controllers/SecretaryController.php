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

    // Helper method to pass view instance to layout
    public function getView()
    {
        return $this->view;
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
            'content' => $content,
            'viewHelper' => $this->view
        ]);
    }

    public function bookings()
    {
        $user = $this->auth->user();
        
        // Get all doctors for booking form
        $doctors = $this->getAllDoctors();
        
        // Get system settings for payment limits
        $settings = $this->getSystemSettings();
        
        $content = $this->view->render('secretary/bookings', [
            'doctors' => $doctors,
            'settings' => $settings
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - إدارة الحجوزات',
            'pageTitle' => 'إدارة الحجوزات',
            'pageSubtitle' => 'إنشاء وإدارة المواعيد',
            'content' => $content,
            'viewHelper' => $this->view
        ]);
    }

    public function payments()
    {
        $user = $this->auth->user();
        
        // Get patient filter
        $patientId = $_GET['patient_id'] ?? null;
        
        // Get daily balance information
        $dailyBalance = $this->getDailyBalance();
        
        // Get payment types summary
        $paymentTypes = $this->getPaymentTypesSummary();
        
        // Get payments (filtered by patient if specified)
        $payments = $patientId ? $this->getPaymentsByPatient($patientId) : $this->getTodayPayments();
        
        // Get patient info if filtering by patient
        $patient = null;
        if ($patientId) {
            $patient = $this->getPatient($patientId);
        }
        
        $content = $this->view->render('secretary/payments', [
            'dailyBalance' => $dailyBalance,
            'paymentTypes' => $paymentTypes,
            'payments' => $payments,
            'patient' => $patient,
            'patientId' => $patientId,
            'userRole' => $user['role']
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - إدارة المدفوعات والرصيد اليومي',
            'pageTitle' => $patientId ? 'المعاملات المالية - ' . ($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') : 'إدارة المدفوعات والرصيد اليومي',
            'pageSubtitle' => $patientId ? 'عرض المعاملات المالية للمريض المحدد' : 'تتبع وإدارة المدفوعات والرصيد اليومي',
            'content' => $content,
            'viewHelper' => $this->view
        ]);
    }

    public function createBooking()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $requiredFields = ['patient_id', 'doctor_id', 'date', 'start_time', 'visit_type'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    return $this->jsonResponse(['error' => "Field {$field} is required"], 400);
                }
            }

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['date'])) {
                return $this->jsonResponse(['error' => 'Invalid date format'], 400);
            }

            // Check if date is not in the past
            if ($input['date'] < date('Y-m-d')) {
                return $this->jsonResponse(['error' => 'Cannot book appointments in the past'], 400);
            }

            // Get visit cost based on type from settings
            $settings = $this->getSystemSettings();
            $visitCosts = [
                'New' => $input['visit_cost'] ?? $settings['new_visit_cost'] ?? 150,
                'FollowUp' => $input['visit_cost'] ?? $settings['repeated_visit_cost'] ?? 100,
                'Consultation' => $input['visit_cost'] ?? $settings['consultation_cost'] ?? 200
            ];
            
            $visitCost = $visitCosts[$input['visit_type']] ?? 150;

            // Calculate end time (15 minutes duration)
            $startTime = $input['start_time'];
            $endTime = $this->calculateEndTime($startTime);

            // Check if time slot is available
            if (!$this->isTimeSlotAvailable($input['doctor_id'], $input['date'], $startTime)) {
                return $this->jsonResponse(['error' => 'Time slot is not available'], 400);
            }

            // Create appointment
            $appointmentData = [
                'patient_id' => $input['patient_id'],
                'doctor_id' => $input['doctor_id'],
                'date' => $input['date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'visit_type' => $input['visit_type'],
                'source' => $input['source'] ?? 'Walk-in',
                'notes' => $input['notes'] ?? '',
                'status' => 'Booked',
                'booked_by' => $this->auth->user()['id']
            ];

            $appointmentId = $this->createAppointmentRecord($appointmentData);

            // Create payment record if payment amount is provided
            if (!empty($input['payment_amount']) && $input['payment_amount'] > 0) {
                $paymentData = [
                    'appointment_id' => $appointmentId,
                    'patient_id' => $input['patient_id'],
                    'amount' => $input['payment_amount'],
                    'method' => 'Cash',
                    'type' => 'Booking',
                    'received_by' => $this->auth->user()['id']
                ];

                $this->createPaymentRecord($paymentData, $this->auth->user()['id'], false);
            }

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Booking created successfully',
                'appointment_id' => $appointmentId
            ]);

        } catch (Exception $e) {
            error_log("Error creating booking: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Failed to create booking'], 500);
        }
    }

    public function getBookingsCalendar()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $date = $_GET['date'] ?? date('Y-m-d');

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->jsonResponse(['error' => 'Invalid date format'], 400);
            }

            // Get appointments for all doctors on the selected date
            $appointments = $this->getAllAppointmentsForDate($date);
            
            // Get available time slots (same for all doctors)
            $availableSlots = $this->getAvailableTimeSlotsForAllDoctors($date);
            
            // Get unavailable slots
            $unavailableSlots = $this->getUnavailableSlotsForAllDoctors($date);

            // Check if it's Friday (official holiday)
            $dateObj = new \DateTime($date);
            $isFriday = $dateObj->format('N') == 5; // 5 = Friday

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'date' => $date,
                    'appointments' => $appointments,
                    'available_slots' => $availableSlots,
                    'unavailable_slots' => $unavailableSlots,
                    'is_friday' => $isFriday
                ]
            ]);

        } catch (Exception $e) {
            error_log("Error getting bookings calendar: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Failed to load calendar'], 500);
        }
    }

    public function deleteBooking($id)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Get appointment details
            $appointment = $this->getAppointmentDetails($id);
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }

            // Delete associated payments first
            $stmt = $this->pdo->prepare("DELETE FROM payments WHERE appointment_id = ?");
            $stmt->execute([$id]);

            // Delete appointment
            $stmt = $this->pdo->prepare("DELETE FROM appointments WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Booking deleted successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to delete booking'], 500);
            }

        } catch (Exception $e) {
            error_log("Error deleting booking: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Failed to delete booking'], 500);
        }
    }

    public function confirmAttendance($id)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'غير مصرح بالوصول'], 401);
            }

            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);
            $remainingAmount = $input['remaining_amount'] ?? 0;
            $receivedAmount = $input['received_amount'] ?? 0;
            $paymentMethod = $input['payment_method'] ?? 'cash';
            $paymentNotes = $input['payment_notes'] ?? '';

            // Get appointment details
            $stmt = $this->pdo->prepare("
                SELECT a.*, 
                       a.patient_id,
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       d.display_name as doctor_name,
                       COALESCE(SUM(pay.amount), 0) as total_paid
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                LEFT JOIN payments pay ON a.id = pay.appointment_id
                WHERE a.id = ?
                GROUP BY a.id
            ");
            $stmt->execute([$id]);
            $appointment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$appointment) {
                return $this->jsonResponse(['error' => 'الموعد غير موجود'], 404);
            }

            // Get visit cost based on visit type
            $settings = $this->getSystemSettings();
            $visitCost = 0;
            switch ($appointment['visit_type']) {
                case 'New':
                    $visitCost = $settings['new_visit_cost'] ?? 150;
                    break;
                case 'FollowUp':
                    $visitCost = $settings['repeated_visit_cost'] ?? 100;
                    break;
                case 'Consultation':
                    $visitCost = $settings['consultation_cost'] ?? 200;
                    break;
                default:
                    $visitCost = 150;
            }

            $totalPaid = $appointment['total_paid'] ?? 0;
            $remainingAmount = $visitCost - $totalPaid;

            // Validate received amount is not negative
            if ($receivedAmount < 0) {
                return $this->jsonResponse([
                    'error' => 'المبلغ المستلم لا يمكن أن يكون سالباً'
                ], 400);
            }

            // Validate payment if there's remaining amount
            if ($remainingAmount > 0) {
                if ($receivedAmount < $remainingAmount) {
                    return $this->jsonResponse([
                        'error' => 'المبلغ المستلم (' . $receivedAmount . ' جنيه) أقل من المبلغ المتبقي (' . $remainingAmount . ' جنيه)'
                    ], 400);
                }
            }

            // Start transaction
            $this->pdo->beginTransaction();

            try {
                // Add payment if there's remaining amount and received amount
                if ($remainingAmount > 0 && $receivedAmount > 0) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO payments (appointment_id, patient_id, amount, method, received_by, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([$id, $appointment['patient_id'], $receivedAmount, $paymentMethod, $this->auth->user()['id']]);
                }

                // Update appointment status to CheckedIn
                $stmt = $this->pdo->prepare("
                    UPDATE appointments 
                    SET status = 'CheckedIn', updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    $this->pdo->commit();
                    return $this->jsonResponse([
                        'ok' => true,
                        'message' => 'تم تأكيد الحضور بنجاح'
                    ]);
                } else {
                    $this->pdo->rollBack();
                    return $this->jsonResponse(['error' => 'فشل في تحديث حالة الموعد'], 500);
                }

            } catch (Exception $e) {
                $this->pdo->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Error confirming attendance: " . $e->getMessage());
            return $this->jsonResponse([
                'error' => 'خطأ في تأكيد الحضور: ' . $e->getMessage()
            ], 500);
        }
    }

    public function patients()
    {
        $user = $this->auth->user();
        
        // Get filter parameters
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $gender = $_GET['gender'] ?? '';
        $ageRange = $_GET['age_range'] ?? '';
        $lastVisit = $_GET['last_visit'] ?? '';
        
        // Get patients with filters
        $patients = $this->getPatientsWithFilters($page, $search, $gender, $ageRange, $lastVisit);
        
        // Get patient statistics
        $stats = $this->getPatientStats();
        
        // Get gender options for filter
        $genderOptions = [
            'Male' => 'ذكر',
            'Female' => 'أنثى'
        ];
        
        // Get age range options
        $ageRangeOptions = [
            '0-18' => '0-18 سنة',
            '19-30' => '19-30 سنة',
            '31-50' => '31-50 سنة',
            '51-65' => '51-65 سنة',
            '65+' => '65+ سنة'
        ];
        
        // Get last visit options
        $lastVisitOptions = [
            'today' => 'اليوم',
            'week' => 'هذا الأسبوع',
            'month' => 'هذا الشهر',
            '3months' => 'آخر 3 أشهر',
            '6months' => 'آخر 6 أشهر',
            'year' => 'آخر سنة',
            'never' => 'لم يزر أبداً'
        ];
        
        $content = $this->view->render('secretary/patients', [
            'patients' => $patients,
            'stats' => $stats,
            'currentPage' => $page,
            'search' => $search,
            'gender' => $gender,
            'ageRange' => $ageRange,
            'lastVisit' => $lastVisit,
            'genderOptions' => $genderOptions,
            'ageRangeOptions' => $ageRangeOptions,
            'lastVisitOptions' => $lastVisitOptions,
            'viewHelper' => $this->view
        ]);
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'عيادة رؤية - إدارة المرضى',
            'pageTitle' => 'إدارة المرضى',
            'pageSubtitle' => 'عرض وإدارة سجلات المرضى',
            'content' => $content,
            'viewHelper' => $this->view
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
            'content' => $content,
            'viewHelper' => $this->view
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
        try {
            $user = $this->auth->user();
            
            // Get patient details
            $patient = $this->getPatientDetails($id);
            if (!$patient) {
                http_response_code(404);
                echo "Patient not found";
                return;
            }
            
            // Get patient appointments
            $appointments = $this->getPatientAppointments($id);
            
            // Get patient payments
            $payments = $this->getPatientPayments($id);
            
            $content = $this->view->render('secretary/patient_details', [
                'patient' => $patient,
                'appointments' => $appointments,
                'payments' => $payments,
                'viewHelper' => $this->view
            ]);
            
            echo $this->view->render('layouts/secretary_main', [
                'title' => 'عيادة رؤية - تفاصيل المريض',
                'pageTitle' => 'تفاصيل المريض - ' . $patient['first_name'] . ' ' . $patient['last_name'],
                'pageSubtitle' => 'عرض تفاصيل المريض والتاريخ الطبي',
                'content' => $content,
                'viewHelper' => $this->view
            ]);
            
        } catch (Exception $e) {
            error_log("Error viewing patient: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading patient details";
        }
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
            'content' => $content,
            'viewHelper' => $this->view
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
            SELECT d.*, u.name as user_name,
                   SUBSTRING_INDEX(u.name, ' ', 1) as first_name,
                   SUBSTRING_INDEX(u.name, ' ', -1) as last_name
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

    private function getPatientsWithFilters($page = 1, $search = '', $gender = '', $ageRange = '', $lastVisit = '')
    {
        $limit = \App\Config\Constants::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $whereConditions = [];
        $params = [];
        
        // Search filter
        if (!empty($search)) {
            if ($this->isPhoneNumberSearch($search)) {
                $whereConditions[] = $this->buildPhoneSearchWhereClause($search);
                $params = array_merge($params, $this->buildPhoneSearchParams($search));
            } else {
                $whereConditions[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR p.phone LIKE ? OR p.national_id LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
        }
        
        // Gender filter
        if (!empty($gender)) {
            $whereConditions[] = "p.gender = ?";
            $params[] = $gender;
        }
        
        // Age range filter
        if (!empty($ageRange)) {
            $currentYear = date('Y');
            switch ($ageRange) {
                case '0-18':
                    $whereConditions[] = "YEAR(p.dob) >= ?";
                    $params[] = $currentYear - 18;
                    break;
                case '19-30':
                    $whereConditions[] = "YEAR(p.dob) BETWEEN ? AND ?";
                    $params[] = $currentYear - 30;
                    $params[] = $currentYear - 19;
                    break;
                case '31-50':
                    $whereConditions[] = "YEAR(p.dob) BETWEEN ? AND ?";
                    $params[] = $currentYear - 50;
                    $params[] = $currentYear - 31;
                    break;
                case '51-65':
                    $whereConditions[] = "YEAR(p.dob) BETWEEN ? AND ?";
                    $params[] = $currentYear - 65;
                    $params[] = $currentYear - 51;
                    break;
                case '65+':
                    $whereConditions[] = "YEAR(p.dob) <= ?";
                    $params[] = $currentYear - 65;
                    break;
            }
        }
        
        // Last visit filter
        if (!empty($lastVisit)) {
            switch ($lastVisit) {
                case 'today':
                    $whereConditions[] = "DATE(a.date) = CURDATE()";
                    break;
                case 'week':
                    $whereConditions[] = "DATE(a.date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $whereConditions[] = "DATE(a.date) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                    break;
                case '3months':
                    $whereConditions[] = "DATE(a.date) >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                    break;
                case '6months':
                    $whereConditions[] = "DATE(a.date) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
                    break;
                case 'year':
                    $whereConditions[] = "DATE(a.date) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                    break;
                case 'never':
                    $whereConditions[] = "a.id IS NULL";
                    break;
            }
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "
            SELECT p.*, 
                   COUNT(DISTINCT a.id) as total_appointments,
                   COUNT(DISTINCT py.id) as total_payments,
                   MAX(a.date) as last_visit,
                   COALESCE(SUM(py.amount), 0) as total_paid
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

    private function getPatientStats()
    {
        $stats = [];
        
        // Total patients
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM patients");
        $stats['total'] = $stmt->fetch()['total'];
        
        // Patients by gender
        $stmt = $this->pdo->query("
            SELECT gender, COUNT(*) as count 
            FROM patients 
            GROUP BY gender
        ");
        $genderStats = $stmt->fetchAll();
        $stats['gender'] = [];
        foreach ($genderStats as $stat) {
            $stats['gender'][$stat['gender']] = $stat['count'];
        }
        
        // Recent patients (last 30 days)
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM patients 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stats['recent'] = $stmt->fetch()['count'];
        
        // Active patients (with appointments in last 30 days)
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT p.id) as count 
            FROM patients p
            JOIN appointments a ON p.id = a.patient_id
            WHERE a.date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stats['active'] = $stmt->fetch()['count'];
        
        return $stats;
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

    private function getSystemSettings()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_key, setting_value 
                FROM settings 
                WHERE setting_key IN ('new_visit_cost', 'repeated_visit_cost', 'consultation_cost')
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            // Set defaults
            $defaults = [
                'new_visit_cost' => '150',
                'repeated_visit_cost' => '100',
                'consultation_cost' => '200'
            ];
            
            return array_merge($defaults, $result);
        } catch (Exception $e) {
            error_log("Error getting system settings: " . $e->getMessage());
            return [
                'new_visit_cost' => '150',
                'repeated_visit_cost' => '100',
                'consultation_cost' => '200'
            ];
        }
    }

    private function getAppointmentsForDate($doctorId, $date)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.phone,
                       p.dob,
                       d.display_name as doctor_display_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.doctor_id = ? AND a.date = ?
                ORDER BY a.start_time
            ");
            $stmt->execute([$doctorId, $date]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting appointments for date: " . $e->getMessage());
            return [];
        }
    }

    private function getAllAppointmentsForDate($date)
    {
        try {
            // Get visit costs from settings
            $settings = $this->getSystemSettings();
            
            $stmt = $this->pdo->prepare("
                SELECT a.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.phone,
                       p.dob,
                       d.display_name as doctor_display_name,
                       d.id as doctor_id,
                       COALESCE(SUM(pay.amount), 0) as total_paid
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                LEFT JOIN payments pay ON a.id = pay.appointment_id
                WHERE a.date = ?
                GROUP BY a.id
                ORDER BY a.start_time
            ");
            $stmt->execute([$date]);
            $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Add visit cost based on visit type
            foreach ($appointments as &$appointment) {
                switch ($appointment['visit_type']) {
                    case 'New':
                        $appointment['visit_cost'] = $settings['new_visit_cost'] ?? 150;
                        break;
                    case 'FollowUp':
                        $appointment['visit_cost'] = $settings['repeated_visit_cost'] ?? 100;
                        break;
                    case 'Consultation':
                        $appointment['visit_cost'] = $settings['consultation_cost'] ?? 200;
                        break;
                    default:
                        $appointment['visit_cost'] = 150;
                }
            }
            
            return $appointments;
        } catch (Exception $e) {
            error_log("Error getting all appointments for date: " . $e->getMessage());
            return [];
        }
    }

    private function getAvailableTimeSlots($doctorId, $date)
    {
        try {
            // Get all time slots for the day
            $allSlots = $this->getAllTimeSlots($date);
            
            // Get booked slots
            $stmt = $this->pdo->prepare("
                SELECT start_time 
                FROM appointments 
                WHERE doctor_id = ? AND date = ? AND status NOT IN ('Cancelled', 'NoShow')
            ");
            $stmt->execute([$doctorId, $date]);
            $bookedSlots = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'start_time');
            
            // Filter out booked slots
            return array_diff($allSlots, $bookedSlots);
        } catch (Exception $e) {
            error_log("Error getting available time slots: " . $e->getMessage());
            return [];
        }
    }

    private function getAvailableTimeSlotsForAllDoctors($date)
    {
        try {
            // Get all time slots for the day
            $allSlots = $this->getAllTimeSlots($date);
            
            // Get all booked slots for all doctors
            $stmt = $this->pdo->prepare("
                SELECT start_time 
                FROM appointments 
                WHERE date = ? AND status NOT IN ('Cancelled', 'NoShow')
            ");
            $stmt->execute([$date]);
            $bookedSlots = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'start_time');
            
            // Filter out booked slots
            return array_diff($allSlots, $bookedSlots);
        } catch (Exception $e) {
            error_log("Error getting available time slots for all doctors: " . $e->getMessage());
            return [];
        }
    }

    private function getUnavailableSlots($doctorId, $date)
    {
        try {
            $unavailableSlots = [];
            $allSlots = $this->getAllTimeSlots($date);
            
            foreach ($allSlots as $time) {
                if ($this->isOutsideWorkingHours($doctorId, $date, $time)) {
                    $unavailableSlots[] = [
                        'time' => $time,
                        'reason' => 'Outside working hours'
                    ];
                }
            }
            
            return $unavailableSlots;
        } catch (Exception $e) {
            error_log("Error getting unavailable slots: " . $e->getMessage());
            return [];
        }
    }

    private function getUnavailableSlotsForAllDoctors($date)
    {
        try {
            $unavailableSlots = [];
            $allSlots = $this->getAllTimeSlots($date);
            
            foreach ($allSlots as $time) {
                if ($this->isOutsideWorkingHoursForAllDoctors($date, $time)) {
                    $unavailableSlots[] = [
                        'time' => $time,
                        'reason' => 'Outside working hours'
                    ];
                }
            }
            
            return $unavailableSlots;
        } catch (Exception $e) {
            error_log("Error getting unavailable slots for all doctors: " . $e->getMessage());
            return [];
        }
    }

    private function getAllTimeSlots($date)
    {
        $slots = [];
        $start = new \DateTime($date . ' ' . \App\Config\Constants::CLINIC_START_TIME);
        $end = new \DateTime($date . ' ' . \App\Config\Constants::CLINIC_END_TIME);
        
        $current = clone $start;
        
        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->add(new \DateInterval('PT' . \App\Config\Constants::SLOT_DURATION_MINUTES . 'M'));
        }
        
        return $slots;
    }

    private function isOutsideWorkingHours($doctorId, $date, $time)
    {
        $datetime = new \DateTime($date . ' ' . $time);
        $hour = (int) $datetime->format('H');
        $minute = (int) $datetime->format('i');
        $timeInMinutes = $hour * 60 + $minute;
        
        $startTime = 14 * 60; // 2:00 PM
        $endTime = 23 * 60;   // 11:00 PM
        
        return $timeInMinutes < $startTime || $timeInMinutes >= $endTime;
    }

    private function isOutsideWorkingHoursForAllDoctors($date, $time)
    {
        $datetime = new \DateTime($date . ' ' . $time);
        $hour = (int) $datetime->format('H');
        $minute = (int) $datetime->format('i');
        $timeInMinutes = $hour * 60 + $minute;
        
        $startTime = 14 * 60; // 2:00 PM
        $endTime = 23 * 60;   // 11:00 PM
        
        return $timeInMinutes < $startTime || $timeInMinutes >= $endTime;
    }

    private function isTimeSlotAvailable($doctorId, $date, $startTime)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM appointments 
                WHERE doctor_id = ? AND date = ? AND start_time = ? 
                AND status NOT IN ('Cancelled', 'NoShow')
            ");
            $stmt->execute([$doctorId, $date, $startTime]);
            return $stmt->fetchColumn() == 0;
        } catch (Exception $e) {
            error_log("Error checking time slot availability: " . $e->getMessage());
            return false;
        }
    }


    private function calculateEndTime($startTime)
    {
        $start = new \DateTime('2000-01-01 ' . $startTime);
        $start->add(new \DateInterval('PT' . \App\Config\Constants::SLOT_DURATION_MINUTES . 'M'));
        return $start->format('H:i');
    }

    private function getAppointmentDetails($id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.phone as patient_phone,
                       p.dob,
                       d.display_name as doctor_display_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting appointment details: " . $e->getMessage());
            return null;
        }
    }

    private function createAppointmentRecord($data)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO appointments (patient_id, doctor_id, date, start_time, end_time, 
                                        visit_type, source, notes, status, booked_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['patient_id'],
                $data['doctor_id'],
                $data['date'],
                $data['start_time'],
                $data['end_time'],
                $data['visit_type'],
                $data['source'],
                $data['notes'],
                $data['status'],
                $data['booked_by']
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating appointment record: " . $e->getMessage());
            throw $e;
        }
    }

    private function createPaymentRecord($data, $userId, $requiresApproval = false)
    {
        try {
            // Get patient_id from appointment if not provided
            if (empty($data['patient_id'])) {
                $stmt = $this->pdo->prepare("SELECT patient_id FROM appointments WHERE id = ?");
                $stmt->execute([$data['appointment_id']]);
                $appointment = $stmt->fetch(\PDO::FETCH_ASSOC);
                $data['patient_id'] = $appointment['patient_id'] ?? null;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO payments (appointment_id, patient_id, amount, method, 
                                    type, received_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['appointment_id'],
                $data['patient_id'],
                $data['amount'],
                $data['method'],
                $data['type'],
                $userId
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating payment record: " . $e->getMessage());
            throw $e;
        }
    }

    public function getBookingDetails($id)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("
                SELECT a.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.phone as patient_phone,
                       p.dob,
                       d.display_name as doctor_display_name,
                       d.id as doctor_id,
                       COALESCE(SUM(pay.amount), 0) as total_paid
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                LEFT JOIN payments pay ON a.id = pay.appointment_id
                WHERE a.id = ?
                GROUP BY a.id
            ");
            $stmt->execute([$id]);
            $appointment = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }
            
            // Add visit cost based on visit type
            $settings = $this->getSystemSettings();
            switch ($appointment['visit_type']) {
                case 'New':
                    $appointment['visit_cost'] = $settings['new_visit_cost'] ?? 150;
                    break;
                case 'FollowUp':
                    $appointment['visit_cost'] = $settings['repeated_visit_cost'] ?? 100;
                    break;
                case 'Consultation':
                    $appointment['visit_cost'] = $settings['consultation_cost'] ?? 200;
                    break;
                default:
                    $appointment['visit_cost'] = 150;
            }
            
            return $this->jsonResponse([
                'ok' => true,
                'booking' => $appointment
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting booking details: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Error getting booking details: ' . $e->getMessage()], 500);
        }
    }
    
    public function updateBooking($id)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $requiredFields = ['patient_id', 'doctor_id', 'date', 'start_time', 'visit_type'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    return $this->jsonResponse(['error' => "Field {$field} is required"], 400);
                }
            }
            
            // Get visit cost
            $settings = $this->getSystemSettings();
            $visitCost = 0;
            switch ($input['visit_type']) {
                case 'New':
                    $visitCost = $settings['new_visit_cost'] ?? 150;
                    break;
                case 'FollowUp':
                    $visitCost = $settings['repeated_visit_cost'] ?? 100;
                    break;
                case 'Consultation':
                    $visitCost = $settings['consultation_cost'] ?? 200;
                    break;
            }
            
            // Validate additional payment amount
            if (isset($input['additional_payment']) && $input['additional_payment'] < 0) {
                return $this->jsonResponse([
                    'error' => 'المبلغ الإضافي لا يمكن أن يكون سالباً'
                ], 400);
            }
            
            // Calculate end time
            $endTime = $this->calculateEndTime($input['start_time']);
            
            // Start transaction
            $this->pdo->beginTransaction();
            
            try {
                // Update appointment
                $stmt = $this->pdo->prepare("
                    UPDATE appointments 
                    SET patient_id = ?, doctor_id = ?, date = ?, start_time = ?, end_time = ?, 
                        visit_type = ?, notes = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['patient_id'],
                    $input['doctor_id'],
                    $input['date'],
                    $input['start_time'],
                    $endTime,
                    $input['visit_type'],
                    $input['notes'] ?? '',
                    $id
                ]);
                
                // Add additional payment if provided
                if (!empty($input['additional_payment']) && $input['additional_payment'] > 0) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO payments (appointment_id, amount, method, created_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$id, $input['additional_payment'], $input['payment_method'] ?? 'cash']);
                }
                
                $this->pdo->commit();
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Booking updated successfully'
                ]);
                
            } catch (Exception $e) {
                $this->pdo->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error updating booking: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Error updating booking: ' . $e->getMessage()], 500);
        }
    }
    
    

    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get daily balance information
     */
    private function getDailyBalance()
    {
        try {
            $today = date('Y-m-d');
            
            // Get opening balance for today
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as opening_balance
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'opening'
            ");
            $stmt->execute([$today]);
            $openingBalance = $stmt->fetch(\PDO::FETCH_ASSOC)['opening_balance'] ?? 0;
            
            // Get additional balance (positive amounts)
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as additional_balance
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'additional'
            ");
            $stmt->execute([$today]);
            $additionalBalance = $stmt->fetch(\PDO::FETCH_ASSOC)['additional_balance'] ?? 0;
            
            // Get total withdrawals (negative amounts)
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_withdrawals
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'withdrawal'
            ");
            $stmt->execute([$today]);
            $totalWithdrawals = $stmt->fetch(\PDO::FETCH_ASSOC)['total_withdrawals'] ?? 0;
            
            // Get total received today
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_received
                FROM payments 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$today]);
            $totalReceived = $stmt->fetch(\PDO::FETCH_ASSOC)['total_received'] ?? 0;
            
            // Get total expenses today
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_expenses
                FROM expenses 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$today]);
            $totalExpenses = $stmt->fetch(\PDO::FETCH_ASSOC)['total_expenses'] ?? 0;
            
            // Get transactions count
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as transactions_count
                FROM (
                    SELECT id FROM payments WHERE DATE(created_at) = ?
                    UNION ALL
                    SELECT id FROM expenses WHERE DATE(created_at) = ?
                    UNION ALL
                    SELECT id FROM daily_balances WHERE DATE(created_at) = ?
                ) as all_transactions
            ");
            $stmt->execute([$today, $today, $today]);
            $transactionsCount = $stmt->fetch(\PDO::FETCH_ASSOC)['transactions_count'] ?? 0;
            
            // Calculate current balance: opening + additional + payments - withdrawals - expenses
            $currentBalance = $openingBalance + $additionalBalance + $totalReceived - $totalWithdrawals - $totalExpenses;
            
            return [
                'opening_balance' => $openingBalance,
                'total_received' => $totalReceived,
                'total_expenses' => $totalExpenses,
                'current_balance' => $currentBalance,
                'transactions_count' => $transactionsCount
            ];
        } catch (Exception $e) {
            error_log("Error getting daily balance: " . $e->getMessage());
            return [
                'opening_balance' => 0,
                'total_received' => 0,
                'current_balance' => 0,
                'transactions_count' => 0
            ];
        }
    }

    /**
     * Get payment types summary
     */
    private function getPaymentTypesSummary()
    {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    CASE 
                        WHEN type = 'Booking' THEN 'new_booking'
                        WHEN type = 'FollowUp' THEN 'followup'
                        WHEN type = 'Consultation' THEN 'consultation'
                        ELSE 'other'
                    END as payment_type,
                    COALESCE(SUM(amount), 0) as total_amount
                FROM payments 
                WHERE DATE(created_at) = ?
                GROUP BY payment_type
            ");
            $stmt->execute([$today]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $summary = [
                'new_booking' => 0,
                'followup' => 0,
                'consultation' => 0,
                'procedure' => 0,
                'other' => 0
            ];
            
            foreach ($results as $result) {
                $summary[$result['payment_type']] = $result['total_amount'];
            }
            
            return $summary;
        } catch (Exception $e) {
            error_log("Error getting payment types summary: " . $e->getMessage());
            return [
                'new_booking' => 0,
                'followup' => 0,
                'consultation' => 0,
                'procedure' => 0,
                'other' => 0
            ];
        }
    }

    /**
     * Get today's payments with patient information
     */
    private function getTodayPayments()
    {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.amount,
                    p.method,
                    p.type,
                    p.description,
                    p.created_at,
                    CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                    pat.phone as patient_phone
                FROM payments p
                LEFT JOIN patients pat ON p.patient_id = pat.id
                WHERE DATE(p.created_at) = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$today]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting today's payments: " . $e->getMessage());
            return [];
        }
    }

    private function getPaymentsByPatient($patientId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.amount,
                    p.method,
                    p.type,
                    p.description,
                    p.created_at,
                    CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                    pat.phone as patient_phone
                FROM payments p
                LEFT JOIN patients pat ON p.patient_id = pat.id
                WHERE p.patient_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$patientId]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting payments by patient: " . $e->getMessage());
            return [];
        }
    }


    private function getPatientDetails($patientId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    id,
                    first_name,
                    last_name,
                    dob,
                    gender,
                    phone,
                    alt_phone,
                    national_id,
                    emergency_contact,
                    emergency_phone,
                    address,
                    created_at
                FROM patients 
                WHERE id = ?
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting patient details: " . $e->getMessage());
            return null;
        }
    }

    private function getPatientPayments($patientId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id,
                    p.amount,
                    p.method,
                    p.type,
                    p.description,
                    p.created_at,
                    u.name as received_by_name
                FROM payments p
                LEFT JOIN users u ON p.received_by = u.id
                WHERE p.patient_id = ?
                ORDER BY p.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting patient payments: " . $e->getMessage());
            return [];
        }
    }

    private function getPatientAppointments($patientId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    a.id,
                    a.date,
                    a.start_time,
                    a.end_time,
                    a.status,
                    a.visit_type,
                    a.notes,
                    a.created_at,
                    d.name as doctor_name,
                    d.specialization
                FROM appointments a
                LEFT JOIN users d ON a.doctor_id = d.id
                WHERE a.patient_id = ?
                ORDER BY a.date DESC, a.start_time DESC
                LIMIT 20
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting patient appointments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * View payment details
     */
    public function viewPayment($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get payment details
            $payment = $this->getPaymentDetails($id);
            if (!$payment) {
                http_response_code(404);
                echo "Payment not found";
                return;
            }
            
            // Get patient details
            $patient = $this->getPatientDetails($payment['patient_id']);
            if (!$patient) {
                http_response_code(404);
                echo "Patient not found";
                return;
            }
            
            // Get appointment details if exists
            $appointment = null;
            if ($payment['appointment_id']) {
                $appointment = $this->getAppointmentDetails($payment['appointment_id']);
            }
            
            // Get related payments for this patient
            $relatedPayments = $this->getPatientRelatedPayments($payment['patient_id'], $id);
            
            $content = $this->view->render('secretary/payment_details', [
                'payment' => $payment,
                'patient' => $patient,
                'appointment' => $appointment,
                'relatedPayments' => $relatedPayments
            ]);
            
            echo $this->view->render('layouts/secretary_main', [
                'title' => 'عيادة رؤية - تفاصيل الدفعة',
                'pageTitle' => 'تفاصيل الدفعة',
                'pageSubtitle' => 'عرض تفاصيل الدفعة رقم ' . $id,
                'content' => $content,
                'viewHelper' => $this->view
            ]);
            
        } catch (Exception $e) {
            error_log("Error viewing payment: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading payment details";
        }
    }

    public function viewExpense($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get expense details
            $expense = $this->getExpenseDetails($id);
            if (!$expense) {
                http_response_code(404);
                echo "Expense not found";
                return;
            }
            
            // Get creator details
            $creator = $this->getUserDetails($expense['created_by']);
            
            // Get related expenses for the same day
            $relatedExpenses = $this->getRelatedExpenses($expense['created_at']);
            
            $content = $this->view->render('secretary/expense_details', [
                'expense' => $expense,
                'creator' => $creator,
                'relatedExpenses' => $relatedExpenses
            ]);
            
            echo $this->view->render('layouts/secretary_main', [
                'title' => 'عيادة رؤية - تفاصيل المصروف',
                'pageTitle' => 'تفاصيل المصروف',
                'pageSubtitle' => 'عرض تفاصيل المصروف رقم ' . $id,
                'content' => $content,
                'viewHelper' => $this->view
            ]);
            
        } catch (Exception $e) {
            error_log("Error viewing expense: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading expense details";
        }
    }

    public function viewBooking($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get booking details directly from database
            $stmt = $this->pdo->prepare("
                SELECT a.*, 
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.phone as patient_phone,
                       p.dob,
                       d.name as doctor_display_name,
                       d.id as doctor_id,
                       COALESCE(SUM(pay.amount), 0) as total_paid
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN users d ON a.doctor_id = d.id
                LEFT JOIN payments pay ON a.id = pay.appointment_id
                WHERE a.id = ?
                GROUP BY a.id
            ");
            $stmt->execute([$id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$booking) {
                http_response_code(404);
                echo "Booking not found";
                return;
            }
            
            // Add visit cost based on visit type
            $settings = $this->getSystemSettings();
            switch ($booking['visit_type']) {
                case 'New':
                    $booking['visit_cost'] = $settings['new_visit_cost'] ?? 150;
                    break;
                case 'FollowUp':
                    $booking['visit_cost'] = $settings['repeated_visit_cost'] ?? 100;
                    break;
                case 'Consultation':
                    $booking['visit_cost'] = $settings['consultation_cost'] ?? 200;
                    break;
                default:
                    $booking['visit_cost'] = 150;
            }
            
            // Get patient details
            $patient = $this->getPatientDetails($booking['patient_id']);
            if (!$patient) {
                http_response_code(404);
                echo "Patient not found";
                return;
            }
            
            // Get doctor details
            $doctor = $this->getUserDetails($booking['doctor_id']);
            
            // Get payment details if exists
            $payments = $this->getBookingPayments($id);
            
            // Get related bookings for this patient
            $relatedBookings = $this->getPatientRelatedBookings($booking['patient_id'], $id);
            
            $content = $this->view->render('secretary/booking_details', [
                'booking' => $booking,
                'patient' => $patient,
                'doctor' => $doctor,
                'payments' => $payments,
                'relatedBookings' => $relatedBookings
            ]);
            
            echo $this->view->render('layouts/secretary_main', [
                'title' => 'عيادة رؤية - تفاصيل الحجز',
                'pageTitle' => 'تفاصيل الحجز',
                'pageSubtitle' => 'عرض تفاصيل الحجز رقم ' . $id,
                'content' => $content,
                'viewHelper' => $this->view
            ]);
            
        } catch (Exception $e) {
            error_log("Error viewing booking: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading booking details";
        }
    }

    private function getPaymentDetails($paymentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, 
                   CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                   pat.phone as patient_phone,
                   pat.address as patient_address,
                   pat.national_id as patient_national_id,
                   u.name as received_by_name
            FROM payments p
            LEFT JOIN patients pat ON p.patient_id = pat.id
            LEFT JOIN users u ON p.received_by = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$paymentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }



    private function getPatientRelatedPayments($patientId, $excludePaymentId = null)
    {
        $sql = "
            SELECT p.*, 
                   CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                   u.name as received_by_name
            FROM payments p
            LEFT JOIN patients pat ON p.patient_id = pat.id
            LEFT JOIN users u ON p.received_by = u.id
            WHERE p.patient_id = ?
        ";
        
        $params = [$patientId];
        
        if ($excludePaymentId) {
            $sql .= " AND p.id != ?";
            $params[] = $excludePaymentId;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT 10";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getExpenseDetails($expenseId)
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*, u.name as created_by_name
            FROM expenses e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$expenseId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getUserDetails($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, email, role FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getRelatedExpenses($createdAt)
    {
        $date = date('Y-m-d', strtotime($createdAt));
        $stmt = $this->pdo->prepare("
            SELECT e.*, u.name as created_by_name
            FROM expenses e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE DATE(e.created_at) = ?
            ORDER BY e.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    private function getBookingPayments($bookingId)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, 
                   CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                   u.name as received_by_name
            FROM payments p
            LEFT JOIN patients pat ON p.patient_id = pat.id
            LEFT JOIN users u ON p.received_by = u.id
            WHERE p.appointment_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getPatientRelatedBookings($patientId, $excludeBookingId = null)
    {
        $sql = "
            SELECT b.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   d.display_name as doctor_name
            FROM appointments b
            LEFT JOIN patients p ON b.patient_id = p.id
            LEFT JOIN doctors d ON b.doctor_id = d.id
            WHERE b.patient_id = ?
        ";
        
        $params = [$patientId];
        
        if ($excludeBookingId) {
            $sql .= " AND b.id != ?";
            $params[] = $excludeBookingId;
        }
        
        $sql .= " ORDER BY b.date DESC, b.start_time DESC LIMIT 10";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Show secretary profile page
     */
    public function profile()
    {
        $user = $this->auth->user();
        
        echo $this->view->render('layouts/secretary_main', [
            'title' => 'Profile - Secretary',
            'page' => 'profile',
            'user' => $user,
            'content' => $this->view->render('secretary/profile', [
                'user' => $user
            ])
        ]);
    }

    /**
     * Update secretary profile
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /secretary/profile');
            exit;
        }

        // CSRF protection
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            header('Location: /secretary/profile?error=' . urlencode('Invalid request'));
            exit;
        }

        $user = $this->auth->user();
        $userId = $user['id'];

        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $secretaryName = trim($_POST['secretary_name'] ?? '');
            $department = trim($_POST['department'] ?? '');

            // Validation
            if (empty($name)) {
                throw new \Exception('Name is required');
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Valid email is required');
            }

            // Check if email is already taken by another user
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new \Exception('Email is already taken');
            }

            // Update user profile
            $sql = "UPDATE users SET 
                    name = ?, 
                    email = ?, 
                    phone = ?, 
                    secretary_name = ?, 
                    department = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                $name, 
                $email, 
                $phone, 
                $secretaryName, 
                $department,
                $userId
            ]);

            if ($success) {
                // Update session data
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['secretary_name'] = $secretaryName;
                $_SESSION['user']['department'] = $department;

                header('Location: /secretary/profile?updated=1&success=' . urlencode('Profile updated successfully'));
                exit;
            } else {
                throw new \Exception('Failed to update profile');
            }

        } catch (\Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            header('Location: /secretary/profile?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Change secretary password
     */
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /secretary/profile');
            exit;
        }

        // CSRF protection
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            header('Location: /secretary/profile?error=' . urlencode('Invalid request'));
            exit;
        }

        $user = $this->auth->user();
        $userId = $user['id'];

        try {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($newPassword)) {
                throw new \Exception('New password is required');
            }

            if (strlen($newPassword) < 8) {
                throw new \Exception('Password must be at least 8 characters long');
            }

            if (!preg_match('/[A-Z]/', $newPassword)) {
                throw new \Exception('Password must contain at least one uppercase letter');
            }

            if (!preg_match('/[a-z]/', $newPassword)) {
                throw new \Exception('Password must contain at least one lowercase letter');
            }

            if (!preg_match('/\d/', $newPassword)) {
                throw new \Exception('Password must contain at least one number');
            }

            if ($newPassword !== $confirmPassword) {
                throw new \Exception('Passwords do not match');
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $success = $stmt->execute([$hashedPassword, $userId]);

            if ($success) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Update session
                $_SESSION['user']['password_changed'] = true;

                header('Location: /secretary/profile?success=' . urlencode('Password changed successfully. Please log in again.'));
                exit;
            } else {
                throw new \Exception('Failed to change password');
            }

        } catch (\Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            header('Location: /secretary/profile?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
