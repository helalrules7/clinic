<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Validator;
use App\Config\Database;
use App\Config\Constants;
use App\Lib\Helpers;
use App\Models\AlertModel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class ApiController
{
    private $auth;
    private $validator;
    private $pdo;
    private $tempImagesToCleanup = [];

    public function __construct()
    {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $this->auth = new Auth();
            $this->validator = new Validator();
            $this->pdo = Database::getInstance()->getConnection();
            $this->alertModel = new AlertModel();
            
            // Suppress PHP errors for API responses
            ini_set('display_errors', 0);
            error_reporting(E_ERROR | E_PARSE);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getCalendar()
    {
        try {
            // Enable debug logging to custom file
            
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $doctorId = $_GET['doctor_id'] ?? null;
            $date = $_GET['date'] ?? date('Y-m-d');

            if (!$doctorId) {
                return $this->jsonResponse(['error' => 'Doctor ID is required'], 400);
            }

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->jsonResponse(['error' => 'Invalid date format'], 400);
            }

            // Check if it's Friday (closed)
            if (Helpers::isFriday($date)) {
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => [
                        'date' => $date,
                        'is_friday' => true,
                        'appointments' => [],
                        'available_slots' => [],
                        'unavailable_slots' => []
                    ]
                ]);
            }

            // Get ALL appointments for the date (any doctor)
            $appointments = $this->getAllAppointmentsForDate($date);
            
            // Get available time slots (based on working hours only)
            $availableSlots = $this->getAvailableTimeSlotsGlobal($date);
            
            // Get unavailable slots (outside working hours only)
            $unavailableSlots = $this->getUnavailableSlotsGlobal($date);

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'date' => $date,
                    'is_friday' => false,
                    'appointments' => $appointments,
                    'available_slots' => $availableSlots,
                    'unavailable_slots' => $unavailableSlots
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteAppointment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            if (!$id) {
                return $this->jsonResponse(['error' => 'Appointment ID is required'], 400);
            }

            // Check if user is doctor or admin (security check)
            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Insufficient permissions'], 403);
            }

            // Start transaction
            $this->pdo->beginTransaction();

            try {
                // Get appointment details before deletion for logging
                $stmt = $this->pdo->prepare("
                    SELECT a.*, p.first_name, p.last_name 
                    FROM appointments a 
                    LEFT JOIN patients p ON a.patient_id = p.id 
                    WHERE a.id = ?
                ");
                $stmt->execute([$id]);
                $appointment = $stmt->fetch();

                if (!$appointment) {
                    $this->pdo->rollback();
                    return $this->jsonResponse(['error' => 'Appointment not found'], 404);
                }

                // Delete related data first
                // 1. Delete prescriptions
                $stmt = $this->pdo->prepare("DELETE FROM prescriptions WHERE appointment_id = ?");
                $stmt->execute([$id]);

                // 2. Delete glasses prescriptions
                $stmt = $this->pdo->prepare("DELETE FROM glasses_prescriptions WHERE appointment_id = ?");
                $stmt->execute([$id]);

                // 3. Delete lab tests
                $stmt = $this->pdo->prepare("DELETE FROM lab_tests WHERE appointment_id = ?");
                $stmt->execute([$id]);

                // 4. Delete radiology tests (if table exists)
                try {
                    $stmt = $this->pdo->prepare("DELETE FROM radiology_tests WHERE appointment_id = ?");
                    $stmt->execute([$id]);
                } catch (\PDOException $e) {
                    // Ignore if table doesn't exist
                }

                // 5. Delete consultation notes
                $stmt = $this->pdo->prepare("DELETE FROM consultation_notes WHERE appointment_id = ?");
                $stmt->execute([$id]);

                // 6. Delete payments
                $stmt = $this->pdo->prepare("DELETE FROM payments WHERE appointment_id = ?");
                $stmt->execute([$id]);

                // 7. Delete timeline events
                $stmt = $this->pdo->prepare("DELETE FROM timeline_events WHERE appointment_id = ?");
                $stmt->execute([$id]);

                // 8. Finally, delete the appointment
                $stmt = $this->pdo->prepare("DELETE FROM appointments WHERE id = ?");
                $stmt->execute([$id]);

                // Commit transaction
                $this->pdo->commit();

                // Create notification for appointment deletion (check user preference first)
                try {
                    // Check if user has disabled notifications for appointments
                    $dontCreateNotification = $this->shouldSkipNotification($user['id']);
                    
                    if (!$dontCreateNotification) {
                        $patientName = trim($appointment['first_name'] . ' ' . $appointment['last_name']);
                        \App\Controllers\NotificationController::create(
                            $user['id'],
                            'appointment',
                            'Appointment Deleted',
                            "Appointment for {$patientName} on {$appointment['date']} at {$appointment['start_time']} has been deleted",
                            'appointment',
                            $id,
                            $appointment['patient_id']
                        );
                    }
                } catch (\Exception $e) {
                    // Continue even if notification creation fails
                }

                // Log the deletion

                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Appointment deleted successfully',
                    'data' => [
                        'deleted_appointment' => [
                            'id' => $id,
                            'patient_name' => $appointment['first_name'] . ' ' . $appointment['last_name'],
                            'date' => $appointment['date'],
                            'time' => $appointment['start_time']
                        ]
                    ]
                ]);

            } catch (\Exception $e) {
                $this->pdo->rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getAppointment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $appointment = $this->getAppointmentDetails($id);
            
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }

            return $this->jsonResponse([
                'ok' => true,
                'data' => $appointment
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getFollowupAppointment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $followup = $this->getFollowupAppointmentData($id);
            
            if (!$followup) {
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => null,
                    'message' => 'No follow-up appointment found'
                ]);
            }

            return $this->jsonResponse([
                'ok' => true,
                'data' => $followup
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getOriginalAppointment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $original = $this->getOriginalAppointmentData($id);
            
            if (!$original) {
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => null,
                    'message' => 'No original appointment found'
                ]);
            }

            return $this->jsonResponse([
                'ok' => true,
                'data' => $original
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function createAppointment()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Validate input
            $rules = [
                'patient_id' => 'required|integer',
                'doctor_id' => 'required|integer',
                'date' => 'required|date',
                'start_time' => 'required',
                'visit_type' => 'required|in:New,FollowUp,Procedure',
                'source' => 'required|in:Walk-in,Phone'
            ];

            // Get JSON input
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                return $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
            }
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Check if time slot is available globally (any doctor can book any available slot)
            if (!Helpers::isTimeSlotAvailableGlobal(
                $data['date'], 
                $data['start_time'], 
                $this->calculateEndTime($data['start_time'])
            )) {
                return $this->jsonResponse(['error' => 'Time slot is not available'], 400);
            }

            // Create appointment
            $appointmentId = $this->createAppointmentRecord($data);
            
            if ($appointmentId) {
                // Create timeline event
                $this->createTimelineEvent($data['patient_id'], $appointmentId, 'Booking', 'Appointment booked');
                
                // Create notification (check user preference first)
                try {
                    $user = $this->auth->user();
                    if ($user) {
                        // Check if user has disabled notifications for appointments
                        $dontCreateNotification = $this->shouldSkipNotification($user['id']);
                        
                        if (!$dontCreateNotification) {
                            $patientStmt = $this->pdo->prepare("SELECT first_name, last_name FROM patients WHERE id = ?");
                            $patientStmt->execute([$data['patient_id']]);
                            $patient = $patientStmt->fetch(\PDO::FETCH_ASSOC);
                            
                            if ($patient) {
                                $patientName = trim($patient['first_name'] . ' ' . $patient['last_name']);
                                \App\Controllers\NotificationController::create(
                                    $user['id'],
                                    'appointment',
                                    'New Appointment Created',
                                    "Appointment scheduled for {$patientName} on {$data['date']} at {$data['start_time']}",
                                    'appointment',
                                    $appointmentId,
                                    $data['patient_id']
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue even if notification creation fails
                }
                
                // Create alert for appointment (check user preference first)
                try {
                    // Get user settings to check if alerts should be created
                    $user = $this->auth->user();
                    $dontCreateAlert = false;
                    
                    if ($user) {
                        $settingsStmt = $this->pdo->prepare("
                            SELECT setting_value 
                            FROM doctor_settings 
                            WHERE user_id = ? AND setting_key = 'dont_create_alert_for_appointments'
                        ");
                        $settingsStmt->execute([$user['id']]);
                        $setting = $settingsStmt->fetch(\PDO::FETCH_ASSOC);
                        
                        if ($setting && $setting['setting_value'] == '1') {
                            $dontCreateAlert = true;
                        }
                    }
                    
                    // Only create alert if user hasn't disabled it
                    if (!$dontCreateAlert) {
                        // Get patient name
                        $patientStmt = $this->pdo->prepare("SELECT first_name, last_name FROM patients WHERE id = ?");
                        $patientStmt->execute([$data['patient_id']]);
                        $patient = $patientStmt->fetch(\PDO::FETCH_ASSOC);
                        
                        if ($patient) {
                            $patientName = trim($patient['first_name'] . ' ' . $patient['last_name']);
                            $alertMessage = "Appointment for patient ({$patientName})";
                            
                            // Get doctor_id from appointment data
                            $doctorId = $data['doctor_id'];
                            
                            // Set alert date/time to be 1 hour before appointment
                            $alertDateTime = new \DateTime($data['date'] . ' ' . $data['start_time']);
                            $alertDateTime->sub(new \DateInterval('PT1H'));
                            $alertDate = $alertDateTime->format('Y-m-d');
                            $alertTime = $alertDateTime->format('H:i:s');
                            
                            $alertData = [
                                'doctor_id' => $doctorId,
                                'patient_id' => $data['patient_id'],
                                'appointment_id' => $appointmentId,
                                'message' => $alertMessage,
                                'alert_date' => $alertDate,
                                'alert_time' => $alertTime,
                                'repeat_count' => 1,
                                'repeat_interval' => 0
                            ];
                            
                            $this->alertModel->create($alertData);
                        }
                    }
                } catch (\Exception $e) {
                    // Continue even if alert creation fails
                }
                
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => ['id' => $appointmentId],
                    'message' => 'Appointment created successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to create appointment'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function updateAppointment($id)
    {
        // Clear any previous output immediately
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            if (!$user) {
                return $this->jsonResponse(['error' => 'User not found'], 401);
            }
            
            $appointment = $this->getAppointmentDetails($id);
            
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }

            // Check permissions
            if ($user['role'] === 'secretary' && in_array($appointment['status'], ['Completed', 'Cancelled'])) {
                return $this->jsonResponse(['error' => 'Cannot modify completed or cancelled appointments'], 403);
            }

            // Handle both JSON and form data
            $input = file_get_contents('php://input');
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $data = [];
            
            // Remove charset if present (e.g., "application/json; charset=utf-8")
            $contentType = trim(explode(';', $contentType)[0]);
            
            if ($contentType === 'application/json') {
                $data = json_decode($input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $this->jsonResponse(['error' => 'Invalid JSON data: ' . json_last_error_msg()], 400);
                }
            } else {
                // Handle form data (application/x-www-form-urlencoded)
                if (!empty($input)) {
                    parse_str($input, $data);
                }
                // Also check $_POST for form data (fallback)
                if (empty($data) && !empty($_POST)) {
                    $data = $_POST;
                }
            }
            
            // Validate data is not empty
            if (empty($data)) {
                return $this->jsonResponse(['error' => 'No data provided'], 400);
            }
            
            // Log received data for debugging (remove in production)
            
            
            if (isset($data['status'])) {
                $newStatus = $data['status'];
                $reason = $data['reason'] ?? null;
                
                // Validate status
                $validStatuses = ['Booked', 'CheckedIn', 'InProgress', 'Completed', 'Cancelled', 'NoShow', 'Rescheduled', 'Closed'];
                if (!in_array($newStatus, $validStatuses)) {
                    return $this->jsonResponse(['error' => 'Invalid appointment status'], 400);
                }
                
                $result = $this->updateAppointmentStatus($id, $newStatus, $reason);

        if ($result) {
                    $this->createTimelineEvent(
                        $appointment['patient_id'], 
                        $id, 
                        'StatusChange', 
                        "Status changed from {$appointment['status']} to {$newStatus}" . ($reason ? " - Reason: {$reason}" : '')
                    );
                    
                    // Create notification for appointment status update (check user preference first)
                    try {
                        // Check if user has disabled notifications for appointments
                        $dontCreateNotification = $this->shouldSkipNotification($user['id']);
                        
                        if (!$dontCreateNotification) {
                            $patientName = trim($appointment['first_name'] . ' ' . $appointment['last_name']);
                            $statusMessages = [
                                'Booked' => 'Appointment booked',
                                'CheckedIn' => 'Patient checked in',
                                'InProgress' => 'Appointment in progress',
                                'Completed' => 'Appointment completed',
                                'Cancelled' => 'Appointment cancelled',
                                'NoShow' => 'Patient did not show up',
                                'Rescheduled' => 'Appointment rescheduled',
                                'Closed' => 'Appointment closed'
                            ];
                            $statusMessage = $statusMessages[$newStatus] ?? "Status changed to {$newStatus}";
                            
                            \App\Controllers\NotificationController::create(
                                $user['id'],
                                'appointment',
                                'Appointment Status Updated',
                                "{$statusMessage} for {$patientName} on {$appointment['date']} at {$appointment['start_time']}" . ($reason ? " - {$reason}" : ''),
                                'appointment',
                                $id,
                                $appointment['patient_id']
                            );
                        }
                    } catch (\Exception $e) {
                        // Continue even if notification creation fails
                    }
                    
                    return $this->jsonResponse([
                        'ok' => true,
                'success' => true,
                        'message' => 'Appointment status updated successfully',
                        'status' => $newStatus
            ]);
        } else {
                    return $this->jsonResponse(['error' => 'Failed to update appointment status'], 500);
                }
            }

            return $this->jsonResponse(['error' => 'No valid updates provided'], 400);

        } catch (\PDOException $e) {
            return $this->jsonResponse(['error' => 'Database error occurred'], 500);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function createPayment()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            // Validate input
            $rules = [
                'patient_id' => 'required|integer',
                'type' => 'required|in:Booking,Consultation,FollowUp,Procedure,Other',
                'method' => 'required|in:Cash,Card,Wallet,Transfer',
                'amount' => 'required|decimal'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Check if discount/exemption requires approval
            $requiresApproval = false;
            if (isset($data['discount_amount']) && $data['discount_amount'] > 0) {
                $requiresApproval = true;
            }
            if (isset($data['is_exempt']) && $data['is_exempt']) {
                $requiresApproval = true;
            }

            // Create payment
            $paymentId = $this->createPaymentRecord($data, $user['id'], $requiresApproval);
            
            if ($paymentId) {
                $this->createTimelineEvent(
                    $data['patient_id'], 
                    $data['appointment_id'] ?? null, 
                    'Payment', 
                    "Payment received: {$data['amount']} EGP"
                );
                
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => ['id' => $paymentId],
                    'message' => 'Payment created successfully',
                    'requires_approval' => $requiresApproval
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to create payment'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function searchPatients()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $query = $_GET['q'] ?? '';
            if (strlen($query) < 2) {
                return $this->jsonResponse(['error' => 'Search query must be at least 2 characters'], 400);
            }

            $patients = $this->searchPatientsByQuery($query);
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => $patients
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Search appointments for autocomplete (used in notes)
     * Searches by appointment ID or patient name
     */
    public function searchAppointments()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $query = $_GET['q'] ?? '';
            $limit = min((int)($_GET['limit'] ?? 10), 20);
            
            if (strlen($query) < 1) {
                return $this->jsonResponse(['ok' => true, 'data' => []]);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            
            // If user is admin, allow searching all appointments; otherwise filter by doctor_id
            $isAdmin = in_array($user['role'], ['admin']);
            
            // Search by appointment ID (if query is numeric) or patient name
            if (is_numeric($query)) {
                // Search by appointment ID
                if ($isAdmin || $doctorId) {
                    if ($isAdmin) {
                        // Admin can see all appointments
                        $stmt = $this->pdo->prepare("
                            SELECT 
                                a.id,
                                a.date,
                                a.start_time,
                                a.end_time,
                                a.status,
                                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                                p.id as patient_id
                            FROM appointments a
                            JOIN patients p ON a.patient_id = p.id
                            WHERE a.id = ?
                            ORDER BY a.date DESC, a.start_time DESC
                            LIMIT ?
                        ");
                        $stmt->execute([$query, $limit]);
        } else {
                        // Doctor can only see their own appointments
                        $stmt = $this->pdo->prepare("
                            SELECT 
                                a.id,
                                a.date,
                                a.start_time,
                                a.end_time,
                                a.status,
                                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                                p.id as patient_id
                            FROM appointments a
                            JOIN patients p ON a.patient_id = p.id
                            WHERE a.id = ? AND a.doctor_id = ?
                            ORDER BY a.date DESC, a.start_time DESC
                            LIMIT ?
                        ");
                        $stmt->execute([$query, $doctorId, $limit]);
                    }
                } else {
                    // User is not a doctor and not admin - return empty
                    return $this->jsonResponse(['ok' => true, 'data' => []]);
                }
            } else {
                // Search by patient name
                $searchTerm = '%' . $query . '%';
                if ($isAdmin || $doctorId) {
                    if ($isAdmin) {
                        // Admin can see all appointments
                        $stmt = $this->pdo->prepare("
                            SELECT 
                                a.id,
                                a.date,
                                a.start_time,
                                a.end_time,
                                a.status,
                                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                                p.id as patient_id
                            FROM appointments a
                            JOIN patients p ON a.patient_id = p.id
                            WHERE (p.first_name LIKE ? OR p.last_name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?)
                            ORDER BY a.date DESC, a.start_time DESC
                            LIMIT ?
                        ");
                        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
                    } else {
                        // Doctor can only see their own appointments
                        $stmt = $this->pdo->prepare("
                            SELECT 
                                a.id,
                                a.date,
                                a.start_time,
                                a.end_time,
                                a.status,
                                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                                p.id as patient_id
                            FROM appointments a
                            JOIN patients p ON a.patient_id = p.id
                            WHERE a.doctor_id = ? 
                            AND (p.first_name LIKE ? OR p.last_name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?)
                            ORDER BY a.date DESC, a.start_time DESC
                            LIMIT ?
                        ");
                        $stmt->execute([$doctorId, $searchTerm, $searchTerm, $searchTerm, $limit]);
                    }
                } else {
                    // User is not a doctor and not admin - return empty
                    return $this->jsonResponse(['ok' => true, 'data' => []]);
                }
            }
            
            $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to search appointments: ' . $e->getMessage()], 500);
        }
    }
    
    public function getAllPatients()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Get sort parameters
            $sortBy = $_GET['sort_by'] ?? 'created_at';
            $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC');
            
            // Validate sort parameters
            $allowedSortFields = ['total_appointments', 'last_visit', 'created_at', 'first_name', 'last_name'];
            $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';
            $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'DESC';
            
            // Build ORDER BY clause
            $orderBy = '';
            if ($sortBy === 'total_appointments') {
                $orderBy = "ORDER BY total_appointments $sortOrder";
            } elseif ($sortBy === 'last_visit') {
                $orderBy = "ORDER BY last_visit $sortOrder";
            } elseif ($sortBy === 'created_at') {
                $orderBy = "ORDER BY p.created_at $sortOrder";
            } elseif ($sortBy === 'first_name') {
                $orderBy = "ORDER BY p.first_name $sortOrder";
            } elseif ($sortBy === 'last_name') {
                $orderBy = "ORDER BY p.last_name $sortOrder";
            } else {
                $orderBy = "ORDER BY p.created_at DESC";
            }
            
            $stmt = $this->pdo->prepare("
                SELECT p.*, 
                       COUNT(DISTINCT a.id) as total_appointments,
                       MAX(a.date) as last_visit,
                       (SELECT te.actor_user_id 
                        FROM timeline_events te 
                        WHERE te.patient_id = p.id 
                        AND te.event_type = 'Booking' 
                        AND te.event_summary LIKE '%New patient registered%' 
                        ORDER BY te.created_at ASC 
                        LIMIT 1) as created_by_user_id,
                       (SELECT u.name 
                        FROM timeline_events te 
                        LEFT JOIN users u ON te.actor_user_id = u.id
                        WHERE te.patient_id = p.id 
                        AND te.event_type = 'Booking' 
                        AND te.event_summary LIKE '%New patient registered%' 
                        ORDER BY te.created_at ASC 
                        LIMIT 1) as created_by_name,
                       (SELECT d.id 
                        FROM timeline_events te 
                        LEFT JOIN users u ON te.actor_user_id = u.id
                        LEFT JOIN doctors d ON u.id = d.user_id
                        WHERE te.patient_id = p.id 
                        AND te.event_type = 'Booking' 
                        AND te.event_summary LIKE '%New patient registered%' 
                        ORDER BY te.created_at ASC 
                        LIMIT 1) as created_by_doctor_id,
                       (SELECT d.display_name 
                        FROM timeline_events te 
                        LEFT JOIN users u ON te.actor_user_id = u.id
                        LEFT JOIN doctors d ON u.id = d.user_id
                        WHERE te.patient_id = p.id 
                        AND te.event_type = 'Booking' 
                        AND te.event_summary LIKE '%New patient registered%' 
                        ORDER BY te.created_at ASC 
                        LIMIT 1) as created_by_doctor_name
                FROM patients p
                LEFT JOIN appointments a ON p.id = a.patient_id
                GROUP BY p.id
                $orderBy
            ");
            $stmt->execute();
            $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get all doctors for filter
            $doctorsStmt = $this->pdo->prepare("
                SELECT d.id, d.display_name, d.specialty, u.profile_image
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                WHERE u.role = 'doctor' AND u.is_active = 1
                ORDER BY d.display_name
            ");
            $doctorsStmt->execute();
            $doctors = $doctorsStmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'ok' => true,
                'patients' => $patients,
                'doctors' => $doctors
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getPatient($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

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
            $stmt->execute([$id]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$patient) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Calculate age
            if ($patient['dob']) {
                $today = new \DateTime();
                $birthDate = new \DateTime($patient['dob']);
                $age = $today->diff($birthDate)->y;
                $patient['age'] = $age;
            } else {
                $patient['age'] = null;
            }

            return $this->jsonResponse([
                'ok' => true,
                'patient' => $patient
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function createPatient()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Validate input
            $rules = [
                'first_name' => 'required|max:50',
                'last_name' => 'required|max:50',
                'phone' => 'required|phone',
                'gender' => 'required|in:Male,Female',
                'dob' => 'date',
                'age' => 'integer|min_value:0|max_value:150',
                'alt_phone' => 'max:20',
                'address' => 'max:500',
                'national_id' => 'max:20',
                'emergency_contact' => 'max:100',
                'emergency_phone' => 'max:20'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Ensure gender is properly set
            if (empty($data['gender']) || !in_array($data['gender'], ['Male', 'Female'])) {
                return $this->jsonResponse([
                    'error' => 'Gender is required and must be either Male or Female'
                ], 400);
            }
            
            // Process age and date of birth
            if (!empty($data['age']) && is_numeric($data['age'])) {
                // Convert age to date of birth
                $age = intval($data['age']);
                if ($age > 0 && $age <= 150) {
                    $birthYear = date('Y') - $age;
                    $data['dob'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), $birthYear));
                }
            }
            
            // Process date of birth - use today's date if still empty
            if (empty($data['dob']) || $data['dob'] === '') {
                $data['dob'] = date('Y-m-d'); // Use today's date as default
            }
            
            // Create patient
            $patientId = $this->createPatientRecord($data);
            
            if ($patientId) {
                $this->createTimelineEvent($patientId, null, 'Booking', 'New patient registered');
                
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => ['id' => $patientId],
                    'message' => 'Patient created successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to create patient'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function deletePatient($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            if (!$id) {
                return $this->jsonResponse(['error' => 'Patient ID is required'], 400);
            }

            // Check if user is doctor or admin (security check)
            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Insufficient permissions'], 403);
            }

            // Verify patient exists
            $stmt = $this->pdo->prepare("SELECT id, first_name, last_name FROM patients WHERE id = ?");
            $stmt->execute([$id]);
            $patient = $stmt->fetch();
            
            if (!$patient) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Begin transaction for complete deletion
            $this->pdo->beginTransaction();

            try {
                // Delete all patient-related data in the correct order (respecting foreign key constraints)
                
                // 1. Delete timeline events
                $stmt = $this->pdo->prepare("DELETE FROM timeline_events WHERE patient_id = ?");
                $stmt->execute([$id]);
                
                // 2. Delete patient attachments (and their files)
                $stmt = $this->pdo->prepare("SELECT file_path FROM patient_attachments WHERE patient_id = ?");
                $stmt->execute([$id]);
                $attachments = $stmt->fetchAll();
                
                foreach ($attachments as $attachment) {
                    $filePath = __DIR__ . '/../../storage/uploads/' . $attachment['file_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                $stmt = $this->pdo->prepare("DELETE FROM patient_attachments WHERE patient_id = ?");
                $stmt->execute([$id]);
                
                // 3. Delete medication prescriptions
                $stmt = $this->pdo->prepare("DELETE FROM prescriptions WHERE appointment_id IN (SELECT id FROM appointments WHERE patient_id = ?)");
                $stmt->execute([$id]);
                
                // 4. Delete glasses prescriptions
                $stmt = $this->pdo->prepare("DELETE FROM glasses_prescriptions WHERE appointment_id IN (SELECT id FROM appointments WHERE patient_id = ?)");
                $stmt->execute([$id]);
                
                // 5. Delete lab tests
                $stmt = $this->pdo->prepare("DELETE FROM lab_tests WHERE appointment_id IN (SELECT id FROM appointments WHERE patient_id = ?)");
                $stmt->execute([$id]);
                
                // 6. Delete radiology tests (if table exists)
                try {
                    $stmt = $this->pdo->prepare("DELETE FROM radiology_tests WHERE appointment_id IN (SELECT id FROM appointments WHERE patient_id = ?)");
                    $stmt->execute([$id]);
                } catch (\PDOException $e) {
                    // Ignore if table doesn't exist
                }
                
                // 7. Delete consultation notes
                $stmt = $this->pdo->prepare("DELETE FROM consultation_notes WHERE appointment_id IN (SELECT id FROM appointments WHERE patient_id = ?)");
                $stmt->execute([$id]);
                
                // 8. Delete payments
                $stmt = $this->pdo->prepare("DELETE FROM payments WHERE appointment_id IN (SELECT id FROM appointments WHERE patient_id = ?)");
                $stmt->execute([$id]);
                
                // 9. Delete patient files (and their physical files)
                $stmt = $this->pdo->prepare("SELECT file_path FROM patient_files WHERE patient_id = ?");
                $stmt->execute([$id]);
                $patientFiles = $stmt->fetchAll();
                
                foreach ($patientFiles as $file) {
                    $filePath = __DIR__ . '/../../storage/uploads/' . $file['file_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                $stmt = $this->pdo->prepare("DELETE FROM patient_files WHERE patient_id = ?");
                $stmt->execute([$id]);
                
                // 10. Delete patient notes
                $stmt = $this->pdo->prepare("DELETE FROM patient_notes WHERE patient_id = ?");
                $stmt->execute([$id]);
                
                // 11. Delete medical history
                $stmt = $this->pdo->prepare("DELETE FROM medical_history_entries WHERE patient_id = ?");
                $stmt->execute([$id]);
                
                // 12. Delete appointments
                $stmt = $this->pdo->prepare("DELETE FROM appointments WHERE patient_id = ?");
                $stmt->execute([$id]);
                
                // 13. Finally, delete the patient record
                $stmt = $this->pdo->prepare("DELETE FROM patients WHERE id = ?");
                $stmt->execute([$id]);
                
                // Commit transaction
                $this->pdo->commit();
                
                // Log the deletion with details
                $deletionSummary = [
                    'patient_id' => $id,
                    'patient_name' => "{$patient['first_name']} {$patient['last_name']}",
                    'deleted_by' => "{$user['name']} (ID: {$user['id']})",
                    'attachments_deleted' => count($attachments),
                    'patient_files_deleted' => count($patientFiles),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
                
                return $this->jsonResponse([
                'ok' => true,
                    'message' => 'Patient and all related data deleted successfully',
                    'data' => [
                        'patient_name' => "{$patient['first_name']} {$patient['last_name']}",
                        'attachments_deleted' => count($attachments),
                        'files_deleted' => count($patientFiles)
                    ]
                ]);
            
        } catch (\Exception $e) {
                // Rollback transaction on error
                $this->pdo->rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to delete patient: ' . $e->getMessage()], 500);
        }
    }

    public function getPatientTimeline($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $timeline = $this->getPatientTimelineEvents($patientId);
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => $timeline
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function updateEmergencyContact($id)
    {
        try {
            
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            if (!$id) {
                return $this->jsonResponse(['error' => 'Patient ID is required'], 400);
            }

            // Verify patient exists
            $stmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Get JSON input
            $rawInput = file_get_contents('php://input');
            
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                return $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
            }


            // Validate input
            $rules = [
                'emergency_contact' => 'required|max:100',
                'emergency_phone' => 'required|phone'
            ];

            if (!$this->validator->validate($input, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Update emergency contact
            $stmt = $this->pdo->prepare("
                UPDATE patients 
                SET emergency_contact = ?, emergency_phone = ?
                WHERE id = ?
            ");
            
            $success = $stmt->execute([
                $input['emergency_contact'],
                $input['emergency_phone'],
                $id
            ]);


            if ($success) {
                // Create timeline event
                try {
                    $this->createTimelineEvent(
                        $id, 
                        null,
                        'Update', 
                        'Emergency contact information updated'
                    );
                } catch (\Exception $e) {
                    // Continue even if timeline fails
                }
                
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Emergency contact updated successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update emergency contact'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function createConsultation()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor') {
                return $this->jsonResponse(['error' => 'Only doctors can create consultations'], 403);
            }

            // Validate input
            $rules = [
                'appointment_id' => 'required|integer',
                'diagnosis' => 'required',
                'plan' => 'required'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Create consultation
            $consultationId = $this->createConsultationRecord($data, $user['id']);
            
            if ($consultationId) {
                $appointment = $this->getAppointmentDetails($data['appointment_id']);
                $this->createTimelineEvent(
                    $appointment['patient_id'], 
                    $data['appointment_id'], 
                    'Consultation', 
                    'Consultation completed'
                );
                
                // Automatically create medical history entry from consultation
                if (!empty($data['diagnosis']) && !empty($appointment['patient_id'])) {
                    $this->createMedicalHistoryFromConsultation($appointment['patient_id'], $data, $appointment, $user['id']);
                }
                
                return $this->jsonResponse([
                'ok' => true,
                    'data' => ['id' => $consultationId],
                    'message' => 'Consultation created successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to create consultation'], 500);
            }
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function createMedicationPrescription()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor') {
                return $this->jsonResponse(['error' => 'Only doctors can create prescriptions'], 403);
            }

            // Validate input
            $rules = [
                'appointment_id' => 'required|integer',
                'drug_name' => 'required|max:120',
                'dose' => 'max:60',
                'frequency' => 'max:60',
                'duration' => 'max:60',
                'route' => 'max:60',
                'notes' => 'max:500'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Create prescription
            $prescriptionId = $this->createMedicationPrescriptionRecord($data);
            
            if ($prescriptionId) {
                $appointment = $this->getAppointmentDetails($data['appointment_id']);
                $this->createTimelineEvent(
                    $appointment['patient_id'], 
                    $data['appointment_id'], 
                    'Rx', 
                    'Medication prescription issued'
                );
                
                return $this->jsonResponse([
                    'success' => true,
                    'data' => ['id' => $prescriptionId],
                    'message' => 'Prescription created successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to create prescription'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function createGlassesPrescription()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor') {
                return $this->jsonResponse(['error' => 'Only doctors can create glasses prescriptions'], 403);
            }

            // Validate input - Same pattern as medications
            $rules = [
                'appointment_id' => 'required|integer',
                'lens_type' => 'required|in:Single Vision,Bifocal,Progressive,Reading'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Create glasses prescription - Same pattern as medications
            $prescriptionId = $this->createGlassesPrescriptionRecord($data);
            
            if ($prescriptionId) {
                $appointment = $this->getAppointmentDetails($data['appointment_id']);
                $this->createTimelineEvent(
                    $appointment['patient_id'], 
                    $data['appointment_id'], 
                    'GlassesRx', 
                    'Glasses prescription issued'
                );
                
                return $this->jsonResponse([
                'success' => true,
                    'data' => ['id' => $prescriptionId],
                    'message' => 'Glasses prescription created successfully'
                ]);
            } else {
                            return $this->jsonResponse(['error' => 'Failed to create glasses prescription'], 500);
        }
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function lockDailyClosure()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor') {
                return $this->jsonResponse(['error' => 'Only doctors can lock daily closure'], 403);
            }

            $date = $_POST['date'] ?? date('Y-m-d');
            
            // Check if already closed
            if ($this->isDateClosed($date)) {
                return $this->jsonResponse(['error' => 'Date is already closed'], 400);
            }

            // Create daily closure
            $closureId = $this->createDailyClosure($date, $user['id']);
            
            if ($closureId) {
                $this->createTimelineEvent(null, null, 'DailyClosure', 'Daily closure locked');
                
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => ['id' => $closureId],
                    'message' => 'Daily closure locked successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to lock daily closure'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function changePassword()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                return $this->jsonResponse(['error' => 'Invalid JSON data'], 400);
            }

            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return $this->jsonResponse(['error' => 'All fields are required'], 400);
            }

            if ($newPassword !== $confirmPassword) {
                return $this->jsonResponse(['error' => 'New passwords do not match'], 400);
            }

            // Change password
            $this->auth->changePassword($user['id'], $currentPassword, $newPassword);
            
            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // Helper methods
    private function jsonResponse($data, $statusCode = 200)
    {
        // Clear any previous output
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        
        // Encode to JSON
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $data = ['error' => 'JSON encoding failed: ' . json_last_error_msg()];
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        // Output JSON and exit
        echo $json;
        exit;
    }
    
    private function getRouterParam($name, $default = null)
    {
        $router = \App\Lib\Router::getInstance();
        if ($router && method_exists($router, 'getParam')) {
            return $router->getParam($name, $default);
        }
        // Fallback: try to get from global router params
        if (isset($GLOBALS['router_params'][$name])) {
            return $GLOBALS['router_params'][$name];
        }
        return $default;
    }

    private function getAppointmentsForDate($doctorId, $date)
    {
        // Set debug log file
        
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone, p.dob, p.gender,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   DATE_FORMAT(a.start_time, '%H:%i') as start_time_formatted,
                   DATE_FORMAT(a.end_time, '%H:%i') as end_time_formatted
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = ? AND a.date = ? AND a.status NOT IN ('Cancelled', 'NoShow')
            ORDER BY a.start_time
        ");
        $stmt->execute([$doctorId, $date]);
        $appointments = $stmt->fetchAll();
        
        // Format the time fields to match frontend expectations
        foreach ($appointments as &$appointment) {
            $appointment['start_time'] = $appointment['start_time_formatted'];
            $appointment['end_time'] = $appointment['end_time_formatted'];
        }
        
        foreach ($appointments as $apt) {
        }
        
        return $appointments;
    }

    private function getAllAppointmentsForDate($date)
    {
        // Set debug log file
        
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone, p.dob, p.gender,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   DATE_FORMAT(a.start_time, '%H:%i') as start_time_formatted,
                   DATE_FORMAT(a.end_time, '%H:%i') as end_time_formatted,
                   d.display_name as doctor_name, u.name as user_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            WHERE a.date = ? AND a.status NOT IN ('Cancelled', 'NoShow')
            ORDER BY a.start_time
        ");
        $stmt->execute([$date]);
        $appointments = $stmt->fetchAll();
        
        // Check if rescheduled_from column exists
        $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
        $hasRescheduledFrom = $columnStmt->fetch(\PDO::FETCH_ASSOC);
        
        // Format the time fields to match frontend expectations and add followup/rescheduled info
        foreach ($appointments as &$appointment) {
            $appointment['start_time'] = $appointment['start_time_formatted'];
            $appointment['end_time'] = $appointment['end_time_formatted'];
            $appointment['doctor_display_name'] = $appointment['user_name'] ?? $appointment['doctor_name'];
            
            // Check if appointment has a follow-up
            $appointment['has_followup'] = false;
            $appointment['followup_id'] = null;
            if ($hasRescheduledFrom) {
                $followupStmt = $this->pdo->prepare("
                    SELECT id FROM appointments 
                    WHERE rescheduled_from = ? AND visit_type = 'FollowUp'
                    LIMIT 1
                ");
                $followupStmt->execute([$appointment['id']]);
                $followup = $followupStmt->fetch(\PDO::FETCH_ASSOC);
                if ($followup) {
                    $appointment['has_followup'] = true;
                    $appointment['followup_id'] = $followup['id'];
                }
            }
            
            // Check if appointment is a follow-up (has original appointment)
            $appointment['is_followup'] = false;
            $appointment['original_appointment_id'] = null;
            if ($hasRescheduledFrom && $appointment['visit_type'] === 'FollowUp' && !empty($appointment['rescheduled_from'])) {
                $appointment['is_followup'] = true;
                $appointment['original_appointment_id'] = $appointment['rescheduled_from'];
            }
        }
        
        foreach ($appointments as $apt) {
        }
        
        return $appointments;
    }

    private function getAvailableTimeSlots($doctorId, $date)
    {
        // Set debug log file
        
        // Get working hours for the doctor on this day
        $weekday = (new \DateTime($date))->format('w');
        $stmt = $this->pdo->prepare("
            SELECT work_start, work_end FROM doctor_schedule 
            WHERE doctor_id = ? AND weekday = ? AND is_working = 1
        ");
        $stmt->execute([$doctorId, $weekday]);
        $schedule = $stmt->fetch();
        
        
        if (!$schedule) {
            return [];
        }

        // Generate time slots
        $slots = [];
        $start = new \DateTime($schedule['work_start']);
        $end = new \DateTime($schedule['work_end']);
        $interval = new \DateInterval('PT15M');
        
        $current = clone $start;
        while ($current < $end) {
            $timeStr = $current->format('H:i');
            
            // Check if slot is available
            $isAvailable = $this->isTimeSlotAvailable($doctorId, $date, $timeStr);
            
            if ($isAvailable) {
                $slots[] = $timeStr;
            }
            
            $current->add($interval);
        }
        
        return $slots;
    }

    private function getAvailableTimeSlotsGlobal($date)
    {
        // Set debug log file
        
        // Use default working hours (2 PM to 11 PM) for all doctors
        $slots = [];
        $start = new \DateTime('14:00');
        $end = new \DateTime('23:00');
        $interval = new \DateInterval('PT15M');
        
        $current = clone $start;
        while ($current < $end) {
            $timeStr = $current->format('H:i');
            
            // Check if slot is available (no appointments at this time)
            $isAvailable = $this->isTimeSlotAvailableGlobal($date, $timeStr);
            
            if ($isAvailable) {
                $slots[] = $timeStr;
            }
            
            $current->add($interval);
        }
        
        return $slots;
    }

    private function isTimeSlotAvailable($doctorId, $date, $startTime)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE doctor_id = ? AND date = ? AND start_time = ? 
            AND status NOT IN ('Cancelled', 'NoShow')
        ");
        $stmt->execute([$doctorId, $date, $startTime]);
        $count = $stmt->fetchColumn();
        
        
        return $count == 0;
    }

    private function isTimeSlotAvailableGlobal($date, $startTime)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE date = ? AND start_time = ? 
            AND status NOT IN ('Cancelled', 'NoShow')
        ");
        $stmt->execute([$date, $startTime]);
        $count = $stmt->fetchColumn();
        
        
        return $count == 0;
    }

    private function getUnavailableSlots($doctorId, $date)
    {
        // Set debug log file
        
        // Get all time slots that are unavailable for this doctor
        $allSlots = $this->getAllTimeSlots($date);
        $availableSlots = $this->getAvailableTimeSlots($doctorId, $date);
        $unavailableSlots = [];
        
        // Debug logging
        
        foreach ($allSlots as $time) {
            if (!in_array($time, $availableSlots)) {
                // Check if there's ANY appointment at this time (any doctor)
                $stmt = $this->pdo->prepare("
                    SELECT a.start_time, a.doctor_id, d.display_name as doctor_name, u.name as user_name,
                           p.first_name, p.last_name, a.visit_type, a.status
                    FROM appointments a
                    JOIN doctors d ON a.doctor_id = d.id
                    JOIN users u ON d.user_id = u.id
                    JOIN patients p ON a.patient_id = p.id
                    WHERE a.date = ? AND a.start_time = ? 
                    AND a.status NOT IN ('Cancelled', 'NoShow')
                ");
                $stmt->execute([$date, $time]);
                $appointment = $stmt->fetch();
                
                // Debug logging for each slot
                if ($appointment) {
                }
                
                if ($appointment) {
                    // If it's the current doctor's appointment, it will show in appointments section
                    // If it's another doctor's appointment, show as reserved
                    if ($appointment['doctor_id'] != $doctorId) {
                        $doctorDisplayName = $appointment['user_name'] ?? $appointment['doctor_name'];
                        $patientName = $appointment['first_name'] . ' ' . $appointment['last_name'];
                        $visitType = $appointment['visit_type'];
                        $status = $appointment['status'];
                        
                        
                        $unavailableSlots[] = [
                            'time' => $time,
                            'doctor_name' => $doctorDisplayName,
                            'patient_name' => $patientName,
                            'visit_type' => $visitType,
                            'status' => $status,
                            'reason' => 'Reserved for ' . $doctorDisplayName . ' - Patient: ' . $patientName . ' - Type: (' . $visitType . ')'
                        ];
                    }
                } else {
                    // Check if it's outside working hours for this doctor
                    $isOutside = $this->isOutsideWorkingHours($doctorId, $date, $time);
                    
                    if ($isOutside) {
                        $unavailableSlots[] = [
                            'time' => $time,
                            'doctor_name' => null,
                            'reason' => 'Outside working hours'
                        ];
                    } else {
                        // This shouldn't happen - slot is unavailable but no appointment and not outside hours
                        // Let's investigate why this slot is considered unavailable
                        
                        // Check doctor schedule
                        $weekday = (new \DateTime($date))->format('w');
                        $scheduleStmt = $this->pdo->prepare("
                            SELECT work_start, work_end, is_working 
                            FROM doctor_schedule 
                            WHERE doctor_id = ? AND weekday = ?
                        ");
                        $scheduleStmt->execute([$doctorId, $weekday]);
                        $schedule = $scheduleStmt->fetch();
                        
                        // Check if there are any appointments for this doctor at this time
                        $ownAppointmentStmt = $this->pdo->prepare("
                            SELECT COUNT(*) as count
                            FROM appointments 
                            WHERE doctor_id = ? AND date = ? AND start_time = ? 
                            AND status NOT IN ('Cancelled', 'NoShow')
                        ");
                        $ownAppointmentStmt->execute([$doctorId, $date, $time]);
                        $ownAppointmentCount = $ownAppointmentStmt->fetchColumn();
                        
                        $debugInfo = "Time: $time | ";
                        $debugInfo .= "Doctor Schedule: " . ($schedule ? "Start: {$schedule['work_start']}, End: {$schedule['work_end']}, Working: {$schedule['is_working']}" : "No schedule found") . " | ";
                        $debugInfo .= "Own appointments: $ownAppointmentCount | ";
                        $debugInfo .= "Weekday: $weekday";
                        
                        
                        $unavailableSlots[] = [
                            'time' => $time,
                            'doctor_name' => null,
                            'debug_info' => $debugInfo,
                            'reason' => 'Investigation needed: ' . $debugInfo
                        ];
                    }
                }
            }
        }
        
        return $unavailableSlots;
    }

    private function getUnavailableSlotsGlobal($date)
    {
        // Set debug log file
        
        // Get all time slots that are unavailable globally
        $allSlots = $this->getAllTimeSlots($date);
        $availableSlots = $this->getAvailableTimeSlotsGlobal($date);
        $unavailableSlots = [];
        
        // Debug logging
        
        foreach ($allSlots as $time) {
            if (!in_array($time, $availableSlots)) {
                // Check if there's ANY appointment at this time
                $stmt = $this->pdo->prepare("
                    SELECT a.start_time, a.doctor_id, d.display_name as doctor_name, u.name as user_name,
                           p.first_name, p.last_name, a.visit_type, a.status
                    FROM appointments a
                    JOIN doctors d ON a.doctor_id = d.id
                    JOIN users u ON d.user_id = u.id
                    JOIN patients p ON a.patient_id = p.id
                    WHERE a.date = ? AND a.start_time = ? 
                    AND a.status NOT IN ('Cancelled', 'NoShow')
                ");
                $stmt->execute([$date, $time]);
                $appointment = $stmt->fetch();
                
                if ($appointment) {
                    $doctorDisplayName = $appointment['user_name'] ?? $appointment['doctor_name'];
                    $patientName = $appointment['first_name'] . ' ' . $appointment['last_name'];
                    $visitType = $appointment['visit_type'];
                    $status = $appointment['status'];
                    
                    
                    $unavailableSlots[] = [
                        'time' => $time,
                        'doctor_name' => $doctorDisplayName,
                        'patient_name' => $patientName,
                        'visit_type' => $visitType,
                        'status' => $status,
                        'reason' => 'Reserved for ' . $doctorDisplayName . ' - Patient: ' . $patientName . ' - Type: (' . $visitType . ')'
                    ];
            } else {
                    // Check if it's outside working hours (before 2 PM or after 11 PM)
                    $timeObj = new \DateTime($time);
                    $workStart = new \DateTime('14:00');
                    $workEnd = new \DateTime('23:00');
                    
                    $isOutside = $timeObj < $workStart || $timeObj >= $workEnd;
                    
                    if ($isOutside) {
                        $unavailableSlots[] = [
                            'time' => $time,
                            'doctor_name' => null,
                            'reason' => 'Outside working hours'
                        ];
                    }
                }
            }
        }
        
        return $unavailableSlots;
    }

    private function getAllTimeSlots($date)
    {
        // Generate all possible time slots for the day (2 PM to 11 PM)
        $slots = [];
        $start = new \DateTime('14:00');
        $end = new \DateTime('23:00');
        $interval = new \DateInterval('PT15M');
        
        $current = clone $start;
        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->add($interval);
        }
        
        return $slots;
    }

    private function isOutsideWorkingHours($doctorId, $date, $time)
    {
        // Get working hours for the doctor on this day
        $weekday = (new \DateTime($date))->format('w');
        $stmt = $this->pdo->prepare("
            SELECT work_start, work_end FROM doctor_schedule 
            WHERE doctor_id = ? AND weekday = ? AND is_working = 1
        ");
        $stmt->execute([$doctorId, $weekday]);
        $schedule = $stmt->fetch();
        
        if (!$schedule) {
            return true; // No working schedule = outside working hours
        }

        $timeObj = new \DateTime($time);
        $workStart = new \DateTime($schedule['work_start']);
        $workEnd = new \DateTime($schedule['work_end']);
        
        return $timeObj < $workStart || $timeObj >= $workEnd;
    }

    private function getAppointmentDetails($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone, p.dob, p.gender,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   CONCAT(u.name) as doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getFollowupAppointmentData($appointmentId)
    {
        try {
            // Check if rescheduled_from column exists
            $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
            $hasRescheduledFrom = $columnStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($hasRescheduledFrom) {
                // Check if there's a follow-up appointment with rescheduled_from pointing to this appointment
                $stmt = $this->pdo->prepare("
                    SELECT id, date, start_time, visit_type, status
                    FROM appointments 
                    WHERE rescheduled_from = ? 
                    AND visit_type = 'FollowUp'
                    ORDER BY date DESC, start_time DESC
                    LIMIT 1
                ");
                $stmt->execute([$appointmentId]);
                $followup = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($followup) {
                    return $followup;
                }
            }
            
            // Fallback: Check for follow-up appointments with same patient_id created after this appointment
            $stmt = $this->pdo->prepare("
                SELECT id, date, start_time, visit_type, status
                FROM appointments 
                WHERE patient_id = (
                    SELECT patient_id FROM appointments WHERE id = ?
                )
                AND visit_type = 'FollowUp'
                AND (date > (SELECT date FROM appointments WHERE id = ?) 
                     OR (date = (SELECT date FROM appointments WHERE id = ?) 
                         AND start_time > (SELECT start_time FROM appointments WHERE id = ?)))
                ORDER BY date ASC, start_time ASC
                LIMIT 1
            ");
            $stmt->execute([$appointmentId, $appointmentId, $appointmentId, $appointmentId]);
            $followup = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $followup ?: null;
                } catch (\Exception $e) {
            // Return null on error
            return null;
        }
    }

    private function getOriginalAppointmentData($followupAppointmentId)
    {
        try {
            // Check if rescheduled_from column exists
            $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
            $hasRescheduledFrom = $columnStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($hasRescheduledFrom) {
                // Get the rescheduled_from value for this follow-up appointment
                $stmt = $this->pdo->prepare("
                    SELECT rescheduled_from 
                    FROM appointments 
                    WHERE id = ?
                ");
                $stmt->execute([$followupAppointmentId]);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($result && $result['rescheduled_from']) {
                    // Get the original appointment details
                    $originalStmt = $this->pdo->prepare("
                        SELECT id, date, start_time, visit_type, status
                        FROM appointments 
                        WHERE id = ?
                    ");
                    $originalStmt->execute([$result['rescheduled_from']]);
                    $original = $originalStmt->fetch(\PDO::FETCH_ASSOC);
                    
                    if ($original) {
                        return $original;
                    }
                }
            }
            
            // Fallback: Find the most recent appointment before this follow-up for the same patient
            $stmt = $this->pdo->prepare("
                SELECT id, date, start_time, visit_type, status
                FROM appointments 
                WHERE patient_id = (
                    SELECT patient_id FROM appointments WHERE id = ?
                )
                AND id != ?
                AND (date < (SELECT date FROM appointments WHERE id = ?) 
                     OR (date = (SELECT date FROM appointments WHERE id = ?) 
                         AND start_time < (SELECT start_time FROM appointments WHERE id = ?)))
                ORDER BY date DESC, start_time DESC
                LIMIT 1
            ");
            $stmt->execute([$followupAppointmentId, $followupAppointmentId, $followupAppointmentId, $followupAppointmentId, $followupAppointmentId]);
            $original = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $original ?: null;
                } catch (\Exception $e) {
            // Return null on error
            return null;
        }
    }

    private function calculateEndTime($startTime)
    {
        $start = new \DateTime($startTime);
        $start->add(new \DateInterval('PT15M'));
        return $start->format('H:i:s');
    }

    private function createAppointmentRecord($data)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO appointments (patient_id, doctor_id, booked_by, source, date, start_time, end_time, visit_type, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $endTime = $this->calculateEndTime($data['start_time']);
            
            // Use booked_by from data if provided, otherwise use current user
            $bookedBy = $data['booked_by'] ?? ($this->auth->user()['id'] ?? null);
            
            if (!$bookedBy) {
                throw new \Exception('booked_by is required');
            }
            
            $result = $stmt->execute([
                $data['patient_id'],
                $data['doctor_id'],
                $bookedBy,
                $data['source'],
                $data['date'],
                $data['start_time'],
                $endTime,
                $data['visit_type'],
                $data['notes'] ?? null
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception('Failed to create appointment: ' . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            $appointmentId = $this->pdo->lastInsertId();
            
            if (!$appointmentId) {
                throw new \Exception('Failed to get appointment ID after insert');
            }
            
            return $appointmentId;
            
        } catch (\PDOException $e) {
            throw new \Exception('Database error creating appointment: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function updateAppointmentStatus($id, $status, $reason = null)
    {
        // Validate status
        $validStatuses = ['Booked', 'CheckedIn', 'InProgress', 'Completed', 'Cancelled', 'NoShow', 'Rescheduled', 'Closed'];
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid appointment status');
        }
        
        // Ensure reason is never null (use empty string if null)
        $reasonText = $reason ?? '';
        
        $stmt = $this->pdo->prepare("
            UPDATE appointments SET status = ?, cancellation_reason = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$status, $reasonText, $id]);
    }

    public function reschedule($id)
    {
        // Clear any previous output immediately
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if (!$user) {
                return $this->jsonResponse(['error' => 'User not found'], 401);
            }
            
            $appointment = $this->getAppointmentDetails($id);
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }
            
            // Check if appointment is completed
            if ($appointment['status'] === 'Completed') {
                return $this->jsonResponse(['error' => 'Cannot reschedule a completed appointment'], 400);
            }
            
            // Get JSON or form data
            $input = file_get_contents('php://input');
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $contentType = trim(explode(';', $contentType)[0]);
            
            if ($contentType === 'application/json') {
                $data = json_decode($input, true);
            } else {
                if (!empty($input)) {
                    parse_str($input, $data);
                } else {
                    $data = $_POST;
                }
            }
            
            if (empty($data) || !isset($data['new_date']) || !isset($data['new_time'])) {
                return $this->jsonResponse(['error' => 'new_date and new_time are required'], 400);
            }
            
            $newDate = trim($data['new_date']);
            $newTime = trim($data['new_time']);
            
            // Validate date format (YYYY-MM-DD)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
                return $this->jsonResponse(['error' => 'Invalid date format. Expected YYYY-MM-DD'], 400);
            }
            
            // Validate time format (HH:MM or HH:MM:SS)
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $newTime)) {
                return $this->jsonResponse(['error' => 'Invalid time format. Expected HH:MM'], 400);
            }
            
            // Normalize time to HH:MM format (remove seconds if present)
            if (strlen($newTime) > 5) {
                $newTime = substr($newTime, 0, 5);
            }
            
            // Validate that new date/time is in the future
            try {
                $currentDateTime = new \DateTime();
                $appointmentDateTime = new \DateTime($appointment['date'] . ' ' . $appointment['start_time']);
                $newDateTime = new \DateTime($newDate . ' ' . $newTime);
            } catch (\Exception $e) {
                return $this->jsonResponse(['error' => 'Invalid date or time format'], 400);
            }
            
            if ($newDateTime <= $currentDateTime) {
                return $this->jsonResponse(['error' => 'New appointment date and time must be in the future'], 400);
            }
            
            if ($newDateTime <= $appointmentDateTime) {
                return $this->jsonResponse(['error' => 'New appointment date and time must be later than the current appointment'], 400);
            }
            
            // Check if time slot is available (use private method instead of Helpers)
            $endTime = $this->calculateEndTime($newTime);
            if (!$this->isTimeSlotAvailableGlobal($newDate, $newTime)) {
                return $this->jsonResponse(['error' => 'Time slot is not available'], 400);
            }
            
            // 1. Create new appointment
            $newAppointmentData = [
                'patient_id' => $appointment['patient_id'],
                'doctor_id' => $appointment['doctor_id'],
                'booked_by' => $user['id'],
                'source' => $appointment['source'] ?? 'Walk-in',
                'date' => $newDate,
                'start_time' => $newTime,
                'visit_type' => $appointment['visit_type'] ?? 'FollowUp',
                'notes' => $appointment['notes'] ?? null
            ];
            
            $newAppointmentId = $this->createAppointmentRecord($newAppointmentData);
            
            if (!$newAppointmentId) {
                return $this->jsonResponse(['error' => 'Failed to create new appointment'], 500);
            }
            
            // 2. Link new appointment to old one (if column exists)
            try {
                $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
                if ($columnStmt->fetch(\PDO::FETCH_ASSOC)) {
                    $this->pdo->prepare("UPDATE appointments SET rescheduled_from = ? WHERE id = ?")
                        ->execute([$id, $newAppointmentId]);
                }
            } catch (\Exception $e) {
                // Column doesn't exist, ignore
            }
            
            // 3. Update old appointment status to 'Rescheduled' instead of deleting
            $stmt = $this->pdo->prepare("UPDATE appointments SET status = 'Rescheduled', cancellation_reason = ?, updated_at = NOW() WHERE id = ?");
            $reason = "Rescheduled to {$newDate} {$newTime}";
            $result = $stmt->execute([$reason, $id]);
            
            if (!$result) {
                throw new \Exception('Failed to update old appointment status');
            }
            
            // 4. Create timeline event for the NEW appointment (same as createAppointment)
            try {
                $this->createTimelineEvent(
                    $appointment['patient_id'], 
                    $newAppointmentId, 
                    'Booking', 
                    "Appointment rescheduled from {$appointment['date']} {$appointment['start_time']} to {$newDate} {$newTime}"
                );
            } catch (\Exception $e) {
                // Continue even if timeline event fails
            }
            
            // Create notification for reschedule
            try {
                $patientName = trim($appointment['first_name'] . ' ' . $appointment['last_name']);
                \App\Controllers\NotificationController::create(
                    $user['id'],
                    'appointment',
                    'Appointment Rescheduled',
                    "Appointment for {$patientName} rescheduled to {$newDate} at {$newTime}",
                    'appointment',
                    $newAppointmentId,
                    $appointment['patient_id']
                );
            } catch (\Exception $e) {
                // Continue even if notification creation fails
            }
            
            // Format date/time for display
            $formattedDate = date('M j, Y', strtotime($newDate));
            $formattedTime = date('g:i A', strtotime($newTime));
            
            return $this->jsonResponse([
                'ok' => true,
                'success' => true,
                'message' => 'Appointment rescheduled successfully',
                'data' => [
                    'new_appointment_id' => $newAppointmentId,
                    'date' => $newDate,
                    'start_time' => $newTime,
                    'formatted_date' => $formattedDate,
                    'formatted_time' => $formattedTime,
                    'old_appointment_id' => $id
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Error rescheduling appointment: ' . $e->getMessage()], 500);
        }
    }

    public function rescheduleFollowup($id)
    {
        // Clear any previous output immediately
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if (!$user) {
                return $this->jsonResponse(['error' => 'User not found'], 401);
            }
            
            $appointment = $this->getAppointmentDetails($id);
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }
            
            // Note: rescheduleFollowup can be done even for completed appointments
            
            // Get JSON or form data
            $input = file_get_contents('php://input');
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $contentType = trim(explode(';', $contentType)[0]);
            
            if ($contentType === 'application/json') {
                $data = json_decode($input, true);
            } else {
                if (!empty($input)) {
                    parse_str($input, $data);
                } else {
                    $data = $_POST;
                }
            }
            
            if (empty($data) || !isset($data['new_date']) || !isset($data['new_time'])) {
                return $this->jsonResponse(['error' => 'new_date and new_time are required'], 400);
            }
            
            $newDate = trim($data['new_date']);
            $newTime = trim($data['new_time']);
            
            // Validate date format (YYYY-MM-DD)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
                return $this->jsonResponse(['error' => 'Invalid date format. Expected YYYY-MM-DD'], 400);
            }
            
            // Validate time format (HH:MM or HH:MM:SS)
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $newTime)) {
                return $this->jsonResponse(['error' => 'Invalid time format. Expected HH:MM'], 400);
            }
            
            // Normalize time to HH:MM format (remove seconds if present)
            if (strlen($newTime) > 5) {
                $newTime = substr($newTime, 0, 5);
            }
            
            // Validate that new date/time is in the future
            try {
                $currentDateTime = new \DateTime();
                $newDateTime = new \DateTime($newDate . ' ' . $newTime);
            } catch (\Exception $e) {
                return $this->jsonResponse(['error' => 'Invalid date or time format'], 400);
            }
            
            if ($newDateTime <= $currentDateTime) {
                return $this->jsonResponse(['error' => 'New appointment date and time must be in the future'], 400);
            }
            
            // Check if time slot is available (use private method instead of Helpers)
            $endTime = $this->calculateEndTime($newTime);
            if (!$this->isTimeSlotAvailableGlobal($newDate, $newTime)) {
                return $this->jsonResponse(['error' => 'Time slot is not available'], 400);
            }
            
            // Create new appointment with visit_type = 'FollowUp'
            $newAppointmentData = [
                'patient_id' => $appointment['patient_id'],
                'doctor_id' => $appointment['doctor_id'],
                'booked_by' => $user['id'],
                'source' => $appointment['source'] ?? 'Walk-in',
                'date' => $newDate,
                'start_time' => $newTime,
                'visit_type' => 'FollowUp',
                'notes' => $appointment['notes'] ?? null
            ];
            
            $newAppointmentId = $this->createAppointmentRecord($newAppointmentData);
            
            if (!$newAppointmentId) {
                return $this->jsonResponse(['error' => 'Failed to create new appointment'], 500);
            }
            
            // Link new appointment to old one (if column exists)
            try {
                $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
                if ($columnStmt->fetch(\PDO::FETCH_ASSOC)) {
                    $this->pdo->prepare("UPDATE appointments SET rescheduled_from = ? WHERE id = ?")
                        ->execute([$id, $newAppointmentId]);
                }
            } catch (\Exception $e) {
                // Column doesn't exist, ignore
            }
            
            // Create timeline event for the NEW appointment (same as createAppointment)
            try {
                $this->createTimelineEvent(
                    $appointment['patient_id'], 
                    $newAppointmentId, 
                    'Booking', 
                    "Follow-up appointment scheduled for {$newDate} {$newTime}"
                );
            } catch (\Exception $e) {
                // Continue even if timeline event fails
            }
            
            // Create alert for follow-up appointment (always create alert regardless of old appointment status or date)
            try {
                $patientName = trim($appointment['first_name'] . ' ' . $appointment['last_name']);
                $alertMessage = "Follow-up appointment for patient ({$patientName})";
                
                // Get doctor_id from appointment
                $doctorId = $appointment['doctor_id'];
                
                // Set alert date/time to be 1 hour before appointment
                $alertDateTime = new \DateTime($newDate . ' ' . $newTime);
                $alertDateTime->sub(new \DateInterval('PT1H'));
                $alertDate = $alertDateTime->format('Y-m-d');
                $alertTime = $alertDateTime->format('H:i:s');
                
                $alertData = [
                    'doctor_id' => $doctorId,
                    'patient_id' => $appointment['patient_id'],
                    'appointment_id' => $newAppointmentId,
                    'message' => $alertMessage,
                    'alert_date' => $alertDate,
                    'alert_time' => $alertTime,
                    'repeat_count' => 1,
                    'repeat_interval' => 0
                ];
                
                $this->alertModel->create($alertData);
            } catch (\Exception $e) {
                // Continue even if alert creation fails
            }
            
            // Create notification for follow-up reschedule
            try {
                $patientName = trim($appointment['first_name'] . ' ' . $appointment['last_name']);
                \App\Controllers\NotificationController::create(
                    $user['id'],
                    'appointment',
                    'Follow-up Appointment Rescheduled',
                    "Follow-up appointment for {$patientName} rescheduled to {$newDate} at {$newTime}",
                    'appointment',
                    $newAppointmentId,
                    $appointment['patient_id']
                );
            } catch (\Exception $e) {
                // Continue even if notification creation fails
            }
            
            return $this->jsonResponse([
                    'ok' => true,
                    'success' => true,
                'message' => 'Follow-up appointment scheduled successfully',
                    'data' => [
                    'new_appointment_id' => $newAppointmentId,
                    'date' => $newDate,
                    'start_time' => $newTime,
                    'visit_type' => 'FollowUp'
                ]
            ]);
                
            } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Error scheduling follow-up appointment: ' . $e->getMessage()], 500);
        }
    }

    private function createPaymentRecord($data, $userId, $requiresApproval)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO payments (appointment_id, patient_id, received_by, type, method, amount, 
                                discount_amount, discount_reason, is_exempt, exempt_reason)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['appointment_id'] ?? null,
            $data['patient_id'],
            $userId,
            $data['type'],
            $data['method'],
            $data['amount'],
            $data['discount_amount'] ?? 0,
            $data['discount_reason'] ?? null,
            $data['is_exempt'] ?? false,
            $data['exempt_reason'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }

    /**
     * Main search function that intelligently routes to appropriate search method
     * - If query looks like a phone number, uses enhanced phone search
     * - Otherwise, uses regular text search for names and other fields
     */
    private function searchPatientsByQuery($query)
    {
        // Check if query looks like a phone number
        $isPhoneSearch = $this->isPhoneNumberSearch($query);
        
        if ($isPhoneSearch) {
            // Use enhanced phone search for better phone number matching
            return $this->searchPatientsByPhone($query);
        } else {
            // Use regular search for names and other fields
            $stmt = $this->pdo->prepare("
                SELECT p.id, p.first_name, p.last_name, p.phone, p.alt_phone, p.dob, p.gender, p.national_id,
                       CONCAT(p.first_name, ' ', p.last_name) as full_name,
                       COUNT(a.id) as total_appointments,
                       MAX(a.date) as last_visit
                FROM patients p
                LEFT JOIN appointments a ON p.id = a.patient_id AND a.status NOT IN ('Cancelled', 'NoShow')
                WHERE p.first_name LIKE ? OR p.last_name LIKE ? OR p.phone LIKE ? 
                   OR p.alt_phone LIKE ? OR p.national_id LIKE ?
                GROUP BY p.id
                ORDER BY p.last_name, p.first_name
                LIMIT 20
            ");
            
            $searchTerm = "%{$query}%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll();
        }
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
     * Enhanced phone number search that handles different formats
     * This allows users to search with '01' instead of '+201234567890'
     */
    private function searchPatientsByPhone($query)
    {
        // Clean the search query (remove +20, 0, etc.)
        $cleanQuery = $this->normalizePhoneNumber($query);
        
        // Create multiple search patterns for different phone formats
        $searchPatterns = $this->generatePhoneSearchPatterns($cleanQuery);
        
        // Build the complete parameter array for execution
        $executionParams = $this->buildExecutionParams($searchPatterns, $query);
        
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.first_name, p.last_name, p.phone, p.alt_phone, p.dob, p.gender, p.national_id,
                   CONCAT(p.first_name, ' ', p.last_name) as full_name,
                   COUNT(a.id) as total_appointments,
                   MAX(a.date) as last_visit
            FROM patients p
            LEFT JOIN appointments a ON p.id = a.patient_id AND a.status NOT IN ('Cancelled', 'NoShow')
            WHERE " . $this->buildPhoneSearchWhereClause($searchPatterns) . "
            GROUP BY p.id
            ORDER BY p.last_name, p.first_name
            LIMIT 20
        ");
        
        $stmt->execute($executionParams);
        return $stmt->fetchAll();
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

    /**
     * Build WHERE clause for phone search with multiple patterns
     * This creates a comprehensive search that covers:
     * - Primary phone numbers
     * - Alternative phone numbers
     * - Names and national IDs (for fallback results)
     */
    private function buildPhoneSearchWhereClause($searchPatterns)
    {
        $conditions = [];
        
        foreach ($searchPatterns as $index => $pattern) {
            $conditions[] = "p.phone LIKE ? OR p.alt_phone LIKE ?";
        }
        
        // Also search in names and national ID for comprehensive results
        // This ensures we don't miss patients if phone search fails
        $conditions[] = "p.first_name LIKE ? OR p.last_name LIKE ? OR p.national_id LIKE ?";
        
        return implode(' OR ', $conditions);
    }

    /**
     * Build the complete parameter array for SQL execution
     * This method ensures all search patterns are properly mapped to SQL parameters
     */
    private function buildExecutionParams($searchPatterns, $originalQuery)
    {
        $params = [];
        
        // Add phone search parameters (each pattern needs 2 parameters for phone and alt_phone)
        foreach ($searchPatterns as $pattern) {
            $params[] = $pattern; // for p.phone
            $params[] = $pattern; // for p.alt_phone
        }
        
        // Add name and national ID search parameters
        // These provide fallback search capabilities
        $nameSearchTerm = "%{$originalQuery}%";
        $params[] = $nameSearchTerm; // for first_name
        $params[] = $nameSearchTerm; // for last_name
        $params[] = $nameSearchTerm; // for national_id
        
        return $params;
    }

    private function createPatientRecord($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO patients (first_name, last_name, dob, gender, phone, alt_phone, address, national_id, emergency_contact, emergency_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['dob'], // Always has a valid date (today's date if originally empty)
            $data['gender'], // Always has a valid value (Male or Female)
            $data['phone'],
            $data['alt_phone'] ?? null,
            $data['address'] ?? null,
            $data['national_id'] ?? null,
            $data['emergency_contact'] ?? null,
            $data['emergency_phone'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function getPatientTimelineEvents($patientId)
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

    private function createConsultationRecord($data, $userId)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO consultation_notes (appointment_id, chief_complaint, hx_present_illness,
                                         visual_acuity_right, visual_acuity_left, refraction_right, refraction_left,
                                         IOP_right, IOP_left, slit_lamp_right, slit_lamp_left, fundus_right, fundus_left,
                                         external_appearance_right, external_appearance_left, eyelid_right, eyelid_left,
                                         diagnosis, diagnosis_code, systemic_disease, medication, plan, followup_days, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['appointment_id'],
            $data['chief_complaint'] ?? null,
            $data['hx_present_illness'] ?? null,
            $data['visual_acuity_right'] ?? null,
            $data['visual_acuity_left'] ?? null,
            $data['refraction_right'] ?? null,
            $data['refraction_left'] ?? null,
            $data['IOP_right'] ?? null,
            $data['IOP_left'] ?? null,
            $data['slit_lamp_right'] ?? null,
            $data['slit_lamp_left'] ?? null,
            $data['fundus_right'] ?? null,
            $data['fundus_left'] ?? null,
            $data['external_appearance_right'] ?? null,
            $data['external_appearance_left'] ?? null,
            $data['eyelid_right'] ?? null,
            $data['eyelid_left'] ?? null,
            $data['diagnosis'],
            $data['diagnosis_code'] ?? null,
            $data['systemic_disease'] ?? null,
            $data['medication'] ?? null,
            $data['plan'],
            $data['followup_days'] ?? null,
            $userId
        ]);
        
        return $this->pdo->lastInsertId();
    }

    /**
     * Automatically create medical history entry from consultation data
     */
    private function createMedicalHistoryFromConsultation($patientId, $consultationData, $appointmentData, $userId)
    {
        try {
            // Only create if diagnosis is provided
            if (empty($consultationData['diagnosis'])) {
                return false;
            }

            // Build notes from consultation data
            $notesParts = [];
            
            if (!empty($consultationData['chief_complaint'])) {
                $notesParts[] = "Chief Complaint: " . $consultationData['chief_complaint'];
            }
            
            if (!empty($consultationData['hx_present_illness'])) {
                $notesParts[] = "History of Present Illness: " . $consultationData['hx_present_illness'];
            }
            
            if (!empty($consultationData['plan'])) {
                $notesParts[] = "Plan: " . $consultationData['plan'];
            }
            
            if (!empty($consultationData['systemic_disease'])) {
                $notesParts[] = "Systemic Disease: " . $consultationData['systemic_disease'];
            }
            
            if (!empty($consultationData['medication'])) {
                $notesParts[] = "Medication: " . $consultationData['medication'];
            }

            $notes = implode("\n\n", $notesParts);
            
            // Use appointment date as diagnosis date
            $diagnosisDate = $appointmentData['date'] ?? date('Y-m-d');
            
            // Determine category based on diagnosis content
            $category = 'general';
            $diagnosisLower = strtolower($consultationData['diagnosis']);
            if (stripos($diagnosisLower, 'allergy') !== false || stripos($diagnosisLower, 'allergic') !== false) {
                $category = 'allergy';
            } elseif (stripos($diagnosisLower, 'surgery') !== false || stripos($diagnosisLower, 'surgical') !== false) {
                $category = 'surgery';
            } elseif (stripos($diagnosisLower, 'medication') !== false || stripos($diagnosisLower, 'drug') !== false) {
                $category = 'medication';
            }

            // Check if a similar medical history entry already exists for this appointment
            // to avoid duplicates
            $stmt = $this->pdo->prepare("
                SELECT id FROM medical_history_entries 
                WHERE patient_id = ? 
                AND condition_name = ? 
                AND diagnosis_date = ?
                AND notes LIKE ?
                LIMIT 1
            ");
            $stmt->execute([
                $patientId,
                $consultationData['diagnosis'],
                $diagnosisDate,
                '%' . substr($notes, 0, 50) . '%'
            ]);
            
            if ($stmt->fetch()) {
                // Entry already exists, skip creation
                return false;
            }

            // Insert medical history entry
            $stmt = $this->pdo->prepare("
                INSERT INTO medical_history_entries 
                (patient_id, condition_name, diagnosis_date, status, notes, category, created_by, created_at) 
                VALUES (?, ?, ?, 'active', ?, ?, ?, NOW())
            ");

            $result = $stmt->execute([
                $patientId,
                $consultationData['diagnosis'],
                $diagnosisDate,
                !empty($notes) ? $notes : null,
                $category,
                $userId
            ]);

            return $result;
        } catch (\Exception $e) {
            // Log error but don't fail the consultation creation
            error_log("Failed to create medical history from consultation: " . $e->getMessage());
            return false;
        }
    }

    private function createMedicationPrescriptionRecord($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO prescriptions (appointment_id, drug_name, dose, frequency, duration, route, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['appointment_id'],
            $data['drug_name'],
            $data['dose'] ?? '',
            $data['frequency'] ?? '',
            $data['duration'] ?? '',
            $data['route'] ?? 'Topical',
            $data['notes'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function createGlassesPrescriptionRecord($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO glasses_prescriptions (appointment_id, distance_sphere_r, distance_cylinder_r, distance_axis_r,
                                             distance_sphere_l, distance_cylinder_l, distance_axis_l,
                                             near_sphere_r, near_cylinder_r, near_axis_r,
                                             near_sphere_l, near_cylinder_l, near_axis_l,
                                             PD_NEAR, PD_DISTANCE, lens_type, comments)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['appointment_id'],
            (!empty($data['distance_sphere_r']) ? $data['distance_sphere_r'] : null),
            (!empty($data['distance_cylinder_r']) ? $data['distance_cylinder_r'] : null),
            (!empty($data['distance_axis_r']) ? $data['distance_axis_r'] : null),
            (!empty($data['distance_sphere_l']) ? $data['distance_sphere_l'] : null),
            (!empty($data['distance_cylinder_l']) ? $data['distance_cylinder_l'] : null),
            (!empty($data['distance_axis_l']) ? $data['distance_axis_l'] : null),
            (!empty($data['near_sphere_r']) ? $data['near_sphere_r'] : null),
            (!empty($data['near_cylinder_r']) ? $data['near_cylinder_r'] : null),
            (!empty($data['near_axis_r']) ? $data['near_axis_r'] : null),
            (!empty($data['near_sphere_l']) ? $data['near_sphere_l'] : null),
            (!empty($data['near_cylinder_l']) ? $data['near_cylinder_l'] : null),
            (!empty($data['near_axis_l']) ? $data['near_axis_l'] : null),
            (!empty($data['PD_NEAR']) ? $data['PD_NEAR'] : null),
            (!empty($data['PD_DISTANCE']) ? $data['PD_DISTANCE'] : null),
            $data['lens_type'],
            (!empty($data['comments']) ? $data['comments'] : null)
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function isDateClosed($date)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM daily_closures WHERE date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchColumn() > 0;
    }

    private function createDailyClosure($date, $userId)
    {
        // Calculate totals for the date
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
            FROM appointments WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$date]);
        $appointmentStats = $stmt->fetch();
        
        $stmt = $this->pdo->prepare("
            SELECT SUM(amount) as total_payments FROM payments WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$date]);
        $paymentStats = $stmt->fetch();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO daily_closures (date, closed_by, total_appointments, completed_appointments, total_payments, note)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $date,
            $userId,
            $appointmentStats['total'] ?? 0,
            $appointmentStats['completed'] ?? 0,
            $paymentStats['total_payments'] ?? 0,
            'Daily closure locked'
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function createTimelineEvent($patientId, $appointmentId, $eventType, $summary)
    {
        try {
            $user = $this->auth->user();
            if (!$user || !isset($user['id'])) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO timeline_events (patient_id, appointment_id, actor_user_id, event_type, event_summary)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $patientId,
                $appointmentId,
                $user['id'],
                $eventType,
                $summary
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Attachment Management Methods
    public function uploadAttachment()
    {
        // Clean output buffer to prevent any previous output from corrupting JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            // Validate required fields
            $appointmentId = $_POST['appointment_id'] ?? null;
            $patientId = $_POST['patient_id'] ?? null;
            $attachmentType = $_POST['attachment_type'] ?? null;
            $description = $_POST['description'] ?? '';

            if (!$appointmentId || !$patientId || !$attachmentType) {
                return $this->jsonResponse(['success' => false, 'message' => 'Missing required fields']);
            }

            // Check if file was uploaded
            if (!isset($_FILES['attachment_file']) || $_FILES['attachment_file']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse(['success' => false, 'message' => 'No file uploaded or upload error']);
            }

            $file = $_FILES['attachment_file'];
            
            // Validate file size (2MB limit)
            if ($file['size'] > 2 * 1024 * 1024) {
                return $this->jsonResponse(['success' => false, 'message' => 'File size exceeds 2MB limit']);
            }

            // Validate file type
            $allowedMimes = [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimes)) {
                return $this->jsonResponse(['success' => false, 'message' => 'File type not allowed']);
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../storage/uploads/attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('att_') . '.' . $extension;
            $filePath = $uploadDir . $filename;

            // Move uploaded file
            if (!@move_uploaded_file($file['tmp_name'], $filePath)) {
                // Check if directory exists and is writable
                if (!is_dir($uploadDir)) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Upload directory does not exist']);
                }
                if (!is_writable($uploadDir)) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Upload directory is not writable']);
                }
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to save file. Please check server permissions.']);
            }

            // Save to database
            $stmt = $this->pdo->prepare("
                INSERT INTO patient_attachments (patient_id, appointment_id, filename, original_filename, file_path, file_size, mime_type, uploaded_by, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $patientId,
                $appointmentId,
                $filename,
                $file['name'],
                'storage/uploads/attachments/' . $filename,
                $file['size'],
                $mimeType,
                $this->auth->user()['id'],
                $description
            ]);

            if ($result) {
                // Create timeline event
                $this->createTimelineEvent($patientId, $appointmentId, 'Attachment', 'Uploaded: ' . $file['name']);
                
                return $this->jsonResponse(['success' => true, 'message' => 'File uploaded successfully']);
            } else {
                // Delete file if database insert failed
                unlink($filePath);
                return $this->jsonResponse(['success' => false, 'message' => 'Database error']);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function viewAttachment($id)
    {
        try {
            if (!$this->auth->check()) {
                http_response_code(401);
                return;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_attachments WHERE id = ?");
            $stmt->execute([$id]);
            $attachment = $stmt->fetch();

            if (!$attachment) {
                http_response_code(404);
                return;
            }

            $filePath = __DIR__ . '/../../' . $attachment['file_path'];
            
            if (!file_exists($filePath)) {
                http_response_code(404);
                return;
            }

            // Set appropriate headers
            header('Content-Type: ' . $attachment['mime_type']);
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: inline; filename="' . $attachment['original_filename'] . '"');

            // Output file
            readfile($filePath);

        } catch (Exception $e) {
            http_response_code(500);
        }
    }

    public function downloadAttachment($id)
    {
        try {
            if (!$this->auth->check()) {
                http_response_code(401);
                return;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_attachments WHERE id = ?");
            $stmt->execute([$id]);
            $attachment = $stmt->fetch();

            if (!$attachment) {
                http_response_code(404);
                return;
            }

            $filePath = __DIR__ . '/../../' . $attachment['file_path'];
            
            if (!file_exists($filePath)) {
                http_response_code(404);
                return;
            }

            // Set download headers
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: attachment; filename="' . $attachment['original_filename'] . '"');

            // Output file
            readfile($filePath);

        } catch (Exception $e) {
            http_response_code(500);
        }
    }

    public function deleteAttachment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                return $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_attachments WHERE id = ?");
            $stmt->execute([$id]);
            $attachment = $stmt->fetch();

            if (!$attachment) {
                return $this->jsonResponse(['success' => false, 'message' => 'Attachment not found']);
            }

            // Delete from database
            $stmt = $this->pdo->prepare("DELETE FROM patient_attachments WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                // Delete physical file
                $filePath = __DIR__ . '/../../' . $attachment['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Create timeline event
                $this->createTimelineEvent(
                    $attachment['patient_id'], 
                    $attachment['appointment_id'], 
                    'Attachment', 
                    'Deleted: ' . $attachment['original_filename']
                );

                return $this->jsonResponse(['success' => true, 'message' => 'Attachment deleted successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Database error']);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error']);
        }
    }

    public function getAppointmentAttachments($appointmentId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("
                SELECT * FROM patient_attachments 
                WHERE appointment_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $attachments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'attachments' => $attachments
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getAppointmentMedications($appointmentId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("
                SELECT * FROM prescriptions 
                WHERE appointment_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $medications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'medications' => $medications
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getAppointmentGlasses($appointmentId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("
                SELECT * FROM glasses_prescriptions 
                WHERE appointment_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $glasses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'glasses' => $glasses
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function deleteMedication($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                return $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            // Get medication details before deletion for timeline
            $stmt = $this->pdo->prepare("SELECT p.*, a.patient_id FROM prescriptions p 
                                       JOIN appointments a ON p.appointment_id = a.id 
                                       WHERE p.id = ?");
            $stmt->execute([$id]);
            $medication = $stmt->fetch();

            if (!$medication) {
                return $this->jsonResponse(['success' => false, 'message' => 'Medication not found']);
            }

            // Check if user has permission (doctor or admin)
            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            }

            // Delete medication
            $stmt = $this->pdo->prepare("DELETE FROM prescriptions WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                // Create timeline event
                $this->createTimelineEvent(
                    $medication['patient_id'], 
                    $medication['appointment_id'], 
                    'Rx', 
                    'Deleted medication: ' . $medication['drug_name']
                );

                return $this->jsonResponse(['success' => true, 'message' => 'Medication deleted successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Database error']);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function updateMedication($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                return $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            // Get medication details before update
            $stmt = $this->pdo->prepare("SELECT p.*, a.patient_id FROM prescriptions p 
                                       JOIN appointments a ON p.appointment_id = a.id 
                                       WHERE p.id = ?");
            $stmt->execute([$id]);
            $medication = $stmt->fetch();

            if (!$medication) {
                return $this->jsonResponse(['success' => false, 'message' => 'Medication not found']);
            }

            // Check if user has permission (doctor or admin)
            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['success' => false, 'message' => 'Permission denied']);
            }

            // Validate input
            $rules = [
                'drug_name' => 'required|max:120',
                'dose' => 'max:60',
                'frequency' => 'max:60',
                'duration' => 'max:60',
                'route' => 'max:60',
                'notes' => 'max:500'
            ];

            // Parse PUT data
            $data = [];
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                parse_str(file_get_contents('php://input'), $data);
                // Also check for multipart/form-data
                if (empty($data) && !empty($_POST)) {
                    $data = $_POST;
                }
                    } else {
                $data = $_POST;
            }
            
            
            if (!$this->validator->validate($data, $rules)) {
                $errors = $this->validator->getAllErrors();
                
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed: ' . $this->validator->getFirstError(),
                    'details' => $errors
                ], 400);
            }

            // Update medication
            $stmt = $this->pdo->prepare("
                UPDATE prescriptions 
                SET drug_name = ?, dose = ?, frequency = ?, duration = ?, route = ?, notes = ?
                WHERE id = ?
            ");

            $result = $stmt->execute([
                $data['drug_name'],
                $data['dose'] ?? '',
                $data['frequency'] ?? '',
                $data['duration'] ?? '',
                $data['route'] ?? 'Topical',
                $data['notes'] ?? null,
                $id
            ]);

            if ($result) {
                // Create timeline event
                $this->createTimelineEvent(
                    $medication['patient_id'], 
                    $medication['appointment_id'], 
                    'Rx', 
                    'Updated medication: ' . $data['drug_name']
                );

                return $this->jsonResponse(['success' => true, 'message' => 'Medication updated successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Database error']);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function updateGlassesPrescription($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                return $this->jsonResponse(['error' => 'Method not allowed'], 405);
            }

            // Get glasses details before update
            $stmt = $this->pdo->prepare("SELECT g.*, a.patient_id FROM glasses_prescriptions g 
                                       JOIN appointments a ON g.appointment_id = a.id 
                                       WHERE g.id = ?");
            $stmt->execute([$id]);
            $glasses = $stmt->fetch();

            if (!$glasses) {
                return $this->jsonResponse(['error' => 'Glasses prescription not found'], 404);
            }

            // Check if user has permission (doctor or admin)
            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Permission denied'], 403);
            }

            // Parse PUT data
            $data = [];
            parse_str(file_get_contents('php://input'), $data);
            if (empty($data) && !empty($_POST)) {
                $data = $_POST;
            }

            // Validate input
            $rules = [
                'lens_type' => 'required|in:Single Vision,Bifocal,Progressive,Reading'
            ];

            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Update glasses prescription
            $stmt = $this->pdo->prepare("
                UPDATE glasses_prescriptions 
                SET distance_sphere_r = ?, distance_cylinder_r = ?, distance_axis_r = ?,
                    distance_sphere_l = ?, distance_cylinder_l = ?, distance_axis_l = ?,
                    near_sphere_r = ?, near_cylinder_r = ?, near_axis_r = ?,
                    near_sphere_l = ?, near_cylinder_l = ?, near_axis_l = ?,
                    PD_NEAR = ?, PD_DISTANCE = ?, lens_type = ?, comments = ?
                WHERE id = ?
            ");

            $result = $stmt->execute([
                (!empty($data['distance_sphere_r']) ? $data['distance_sphere_r'] : null),
                (!empty($data['distance_cylinder_r']) ? $data['distance_cylinder_r'] : null),
                (!empty($data['distance_axis_r']) ? $data['distance_axis_r'] : null),
                (!empty($data['distance_sphere_l']) ? $data['distance_sphere_l'] : null),
                (!empty($data['distance_cylinder_l']) ? $data['distance_cylinder_l'] : null),
                (!empty($data['distance_axis_l']) ? $data['distance_axis_l'] : null),
                (!empty($data['near_sphere_r']) ? $data['near_sphere_r'] : null),
                (!empty($data['near_cylinder_r']) ? $data['near_cylinder_r'] : null),
                (!empty($data['near_axis_r']) ? $data['near_axis_r'] : null),
                (!empty($data['near_sphere_l']) ? $data['near_sphere_l'] : null),
                (!empty($data['near_cylinder_l']) ? $data['near_cylinder_l'] : null),
                (!empty($data['near_axis_l']) ? $data['near_axis_l'] : null),
                (!empty($data['PD_NEAR']) ? $data['PD_NEAR'] : null),
                (!empty($data['PD_DISTANCE']) ? $data['PD_DISTANCE'] : null),
                $data['lens_type'],
                (!empty($data['comments']) ? $data['comments'] : null),
                $id
            ]);

            if ($result) {
                // Create timeline event
                $this->createTimelineEvent(
                    $glasses['patient_id'], 
                    $glasses['appointment_id'], 
                    'GlassesRx', 
                    'Updated glasses prescription: ' . $data['lens_type']
                );

                return $this->jsonResponse(['success' => true, 'message' => 'Glasses prescription updated successfully']);
            } else {
                return $this->jsonResponse(['error' => 'Database error'], 500);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteGlassesPrescription($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                return $this->jsonResponse(['error' => 'Method not allowed'], 405);
            }

            // Get glasses details before deletion for timeline
            $stmt = $this->pdo->prepare("SELECT g.*, a.patient_id FROM glasses_prescriptions g 
                                       JOIN appointments a ON g.appointment_id = a.id 
                                       WHERE g.id = ?");
            $stmt->execute([$id]);
            $glasses = $stmt->fetch();

            if (!$glasses) {
                return $this->jsonResponse(['error' => 'Glasses prescription not found'], 404);
            }

            // Check if user has permission (doctor or admin)
            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Permission denied'], 403);
            }

            // Delete glasses prescription
            $stmt = $this->pdo->prepare("DELETE FROM glasses_prescriptions WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                // Create timeline event
                $this->createTimelineEvent(
                    $glasses['patient_id'], 
                    $glasses['appointment_id'], 
                    'GlassesRx', 
                    'Deleted glasses prescription: ' . $glasses['lens_type']
                );

                return $this->jsonResponse(['success' => true, 'message' => 'Glasses prescription deleted successfully']);
            } else {
                return $this->jsonResponse(['error' => 'Database error'], 500);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // Lab Tests & Radiology Management
    public function createLabTest()
    {
        try {
            // Get data from request - support both JSON and form data
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $input = [];
            
            if (strpos($contentType, 'application/json') !== false) {
                // JSON request
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $input = [];
                }
            } else {
                // Form data or URL-encoded
                $input = $_POST;
            }
            
            $appointmentId = $input['appointment_id'] ?? null;
            $testType = $input['test_type'] ?? null;
            $testCategory = $input['test_category'] ?? null;
            $testName = $input['test_name'] ?? null;
            $priority = $input['priority'] ?? 'normal';
            $status = $input['status'] ?? 'ordered';
            $orderedDate = $input['ordered_date'] ?? null;
            $expectedDate = $input['expected_date'] ?? null;
            $notes = $input['notes'] ?? null;
            $results = $input['results'] ?? null;

            // Validation
            if (!$appointmentId || !$testType || !$testCategory || !$testName) {
                return $this->jsonResponse(['error' => 'Missing required fields'], 400);
            }

            // Get appointment details for patient_id
            $stmt = $this->pdo->prepare("SELECT * FROM appointments WHERE id = ?");
            $stmt->execute([$appointmentId]);
            $appointment = $stmt->fetch();
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }

            $data = [
                'appointment_id' => $appointmentId,
                'patient_id' => $appointment['patient_id'],
                'test_type' => $testType,
                'test_category' => $testCategory,
                'test_name' => $testName,
                'priority' => $priority,
                'status' => $status,
                'ordered_date' => $orderedDate ?: date('Y-m-d'),
                'expected_date' => $expectedDate,
                'notes' => $notes,
                'results' => $results,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $stmt = $this->pdo->prepare("
                INSERT INTO lab_tests (appointment_id, patient_id, test_type, test_category, test_name, 
                                     priority, status, ordered_date, expected_date, notes, results, 
                                     created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['appointment_id'], $data['patient_id'], $data['test_type'], 
                $data['test_category'], $data['test_name'], $data['priority'], 
                $data['status'], $data['ordered_date'], $data['expected_date'], 
                $data['notes'], $data['results'], $data['created_at'], $data['updated_at']
            ]);
            $labTestId = $this->pdo->lastInsertId();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lab test added successfully',
                'lab_test_id' => $labTestId
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function updateLabTest($testId)
    {
        try {
            // Get JSON data from request body
            $input = json_decode(file_get_contents('php://input'), true);
            
            $testType = $input['test_type'] ?? $_POST['test_type'] ?? null;
            $testCategory = $input['test_category'] ?? $_POST['test_category'] ?? null;
            $testName = $input['test_name'] ?? $_POST['test_name'] ?? null;
            $priority = $input['priority'] ?? $_POST['priority'] ?? null;
            $status = $input['status'] ?? $_POST['status'] ?? null;
            $orderedDate = $input['ordered_date'] ?? $_POST['ordered_date'] ?? null;
            $expectedDate = $input['expected_date'] ?? $_POST['expected_date'] ?? null;
            $notes = $input['notes'] ?? $_POST['notes'] ?? null;
            $results = $input['results'] ?? $_POST['results'] ?? null;

            // Check if lab test exists
            $stmt = $this->pdo->prepare("SELECT * FROM lab_tests WHERE id = ?");
            $stmt->execute([$testId]);
            $labTest = $stmt->fetch();
            if (!$labTest) {
                return $this->jsonResponse(['error' => 'Lab test not found'], 404);
            }

            // Build update query dynamically for non-null values
            $updateFields = [];
            $updateValues = [];
            
            if ($testType !== null) {
                $updateFields[] = "test_type = ?";
                $updateValues[] = $testType;
            }
            if ($testCategory !== null) {
                $updateFields[] = "test_category = ?";
                $updateValues[] = $testCategory;
            }
            if ($testName !== null) {
                $updateFields[] = "test_name = ?";
                $updateValues[] = $testName;
            }
            if ($priority !== null) {
                $updateFields[] = "priority = ?";
                $updateValues[] = $priority;
            }
            if ($status !== null) {
                $updateFields[] = "status = ?";
                $updateValues[] = $status;
            }
            if ($orderedDate !== null) {
                $updateFields[] = "ordered_date = ?";
                $updateValues[] = $orderedDate;
            }
            if ($expectedDate !== null) {
                $updateFields[] = "expected_date = ?";
                $updateValues[] = $expectedDate;
            }
            if ($notes !== null) {
                $updateFields[] = "notes = ?";
                $updateValues[] = $notes;
            }
            if ($results !== null) {
                $updateFields[] = "results = ?";
                $updateValues[] = $results;
            }
            
            // Always update updated_at
            $updateFields[] = "updated_at = ?";
            $updateValues[] = date('Y-m-d H:i:s');
            $updateValues[] = $testId; // for WHERE clause
            
            if (!empty($updateFields)) {
                $sql = "UPDATE lab_tests SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($updateValues);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lab test updated successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteLabTest($testId)
    {
        try {
            // Check if lab test exists
            $stmt = $this->pdo->prepare("SELECT * FROM lab_tests WHERE id = ?");
            $stmt->execute([$testId]);
            $labTest = $stmt->fetch();
            if (!$labTest) {
                return $this->jsonResponse(['error' => 'Lab test not found'], 404);
            }

            $stmt = $this->pdo->prepare("DELETE FROM lab_tests WHERE id = ?");
            $stmt->execute([$testId]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Lab test deleted successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getLabTests($appointmentId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM lab_tests 
                WHERE appointment_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $labTests = $stmt->fetchAll();

            return $this->jsonResponse([
                'success' => true,
                'lab_tests' => $labTests
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // Patient Files Methods
    public function uploadPatientFile()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = $_POST['patient_id'] ?? null;
            $fileType = $_POST['file_type'] ?? null;
            $description = $_POST['description'] ?? '';

            if (!$patientId || !$fileType) {
                return $this->jsonResponse(['error' => 'Patient ID and file type are required'], 400);
            }

            // Check if file was uploaded
            if (!isset($_FILES['patient_file']) || $_FILES['patient_file']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse(['error' => 'No file uploaded or upload error'], 400);
            }

            $file = $_FILES['patient_file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                return $this->jsonResponse(['error' => 'File type not allowed'], 400);
            }

            // Check file size (5MB max)
            if ($file['size'] > 5 * 1024 * 1024) {
                return $this->jsonResponse(['error' => 'File size too large (max 5MB)'], 400);
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../uploads/patients/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'patient_' . $patientId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filePath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return $this->jsonResponse(['error' => 'Failed to save file'], 500);
            }

            // Save file info to database
            $stmt = $this->pdo->prepare("
                INSERT INTO patient_files (patient_id, original_filename, file_path, file_type, file_size, description, uploaded_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $patientId,
                $file['name'],
                'uploads/patients/' . $filename,
                $fileType,
                $file['size'],
                $description,
                $this->auth->user()['id']
            ]);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file_id' => $this->pdo->lastInsertId()
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function viewPatientFile($fileId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_files WHERE id = ?");
            $stmt->execute([$fileId]);
            $file = $stmt->fetch();

            if (!$file) {
                return $this->jsonResponse(['error' => 'File not found'], 404);
            }

            $filePath = __DIR__ . '/../../' . $file['file_path'];
            
            if (!file_exists($filePath)) {
                return $this->jsonResponse(['error' => 'File not found on disk'], 404);
            }

            // Set appropriate headers
            $mimeType = mime_content_type($filePath);
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: private, max-age=3600');
            
            // Output file
            readfile($filePath);
                exit;

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadPatientFile($fileId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_files WHERE id = ?");
            $stmt->execute([$fileId]);
            $file = $stmt->fetch();

            if (!$file) {
                return $this->jsonResponse(['error' => 'File not found'], 404);
            }

            $filePath = __DIR__ . '/../../' . $file['file_path'];
            
            if (!file_exists($filePath)) {
                return $this->jsonResponse(['error' => 'File not found on disk'], 404);
            }

            // Set download headers
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: private');
            
            // Output file
            readfile($filePath);
                exit;

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function deletePatientFile($fileId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_files WHERE id = ?");
            $stmt->execute([$fileId]);
            $file = $stmt->fetch();

            if (!$file) {
                return $this->jsonResponse(['error' => 'File not found'], 404);
            }

            // Delete file from disk
            $filePath = __DIR__ . '/../../' . $file['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete from database
            $stmt = $this->pdo->prepare("DELETE FROM patient_files WHERE id = ?");
            $stmt->execute([$fileId]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getPatientFiles($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("
                SELECT * FROM patient_files 
                WHERE patient_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$patientId]);
            $files = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'files' => $files
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    // Patient Notes Methods
    public function createPatientNote()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = $_POST['patient_id'] ?? null;
            $title = $_POST['title'] ?? null;
            $content = $_POST['content'] ?? null;

            if (!$patientId || !$title || !$content) {
                return $this->jsonResponse(['error' => 'Patient ID, title, and content are required'], 400);
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO patient_notes (patient_id, title, content, doctor_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $patientId,
                $title,
                $content,
                $this->auth->user()['id']
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Note created successfully',
                'note_id' => $this->pdo->lastInsertId()
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePatientNote($noteId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Handle PUT request data
            $input = [];
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                // Parse PUT data
                $putData = file_get_contents('php://input');
                parse_str($putData, $input);
            } else {
                $input = $_POST;
            }

            $title = $input['title'] ?? null;
            $content = $input['content'] ?? null;

            if (!$title || !$content) {
                return $this->jsonResponse([
                    'error' => 'Title and content are required',
                    'debug' => [
                        'received_title' => $title,
                        'received_content' => $content,
                        'input_data' => $input
                    ]
                ], 400);
            }

            // Check if note exists
            $stmt = $this->pdo->prepare("SELECT * FROM patient_notes WHERE id = ?");
            $stmt->execute([$noteId]);
            $note = $stmt->fetch();

            if (!$note) {
                return $this->jsonResponse(['error' => 'Note not found'], 404);
            }

            $stmt = $this->pdo->prepare("
                UPDATE patient_notes 
                SET title = ?, content = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            $stmt->execute([$title, $content, $noteId]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Note updated successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function deletePatientNote($noteId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Check if note exists
            $stmt = $this->pdo->prepare("SELECT * FROM patient_notes WHERE id = ?");
            $stmt->execute([$noteId]);
            $note = $stmt->fetch();

            if (!$note) {
                return $this->jsonResponse(['error' => 'Note not found'], 404);
            }

            $stmt = $this->pdo->prepare("DELETE FROM patient_notes WHERE id = ?");
            $stmt->execute([$noteId]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Note deleted successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function createMedicalHistory($patientId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Validate patient exists
            $stmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $stmt->execute([$patientId]);
            if (!$stmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);
            
            $condition = trim($input['condition'] ?? '');
            $diagnosis_date = trim($input['diagnosis_date'] ?? '');
            $status = trim($input['status'] ?? 'active');
            $notes = trim($input['notes'] ?? '');
            $category = trim($input['category'] ?? 'general');

            // Validate required fields
            if (empty($condition)) {
                return $this->jsonResponse(['error' => 'Medical condition is required'], 400);
            }

            // Validate date format if provided
            if (!empty($diagnosis_date) && !$this->validateDate($diagnosis_date)) {
                return $this->jsonResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
            }

            // Validate status
            $validStatuses = ['active', 'resolved', 'chronic', 'inactive'];
            if (!in_array($status, $validStatuses)) {
                return $this->jsonResponse(['error' => 'Invalid status. Must be: active, resolved, chronic, or inactive'], 400);
            }

            // Validate category
            $validCategories = ['general', 'allergy', 'medication', 'surgery', 'family_history', 'social_history'];
            if (!in_array($category, $validCategories)) {
                return $this->jsonResponse(['error' => 'Invalid category'], 400);
            }

            // Insert medical history
            $stmt = $this->pdo->prepare("
                INSERT INTO medical_history_entries (patient_id, condition_name, diagnosis_date, status, notes, category, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $result = $stmt->execute([
                $patientId,
                $condition,
                !empty($diagnosis_date) ? $diagnosis_date : null,
                $status,
                !empty($notes) ? $notes : null,
                $category,
                $this->auth->user()['id']
            ]);

            if ($result) {
                $historyId = $this->pdo->lastInsertId();
                
                // Get the created record
                $stmt = $this->pdo->prepare("
                    SELECT mh.*, u.name as created_by_name 
                    FROM medical_history_entries mh 
                    LEFT JOIN users u ON mh.created_by = u.id 
                    WHERE mh.id = ?
                ");
                $stmt->execute([$historyId]);
                $history = $stmt->fetch();

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Medical history created successfully',
                    'data' => $history
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to create medical history'], 500);
            }

            } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function updateMedicalHistory($patientId, $historyId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Validate patient and history exist
            $stmt = $this->pdo->prepare("
                SELECT mh.* FROM medical_history_entries mh 
                WHERE mh.id = ? AND mh.patient_id = ?
            ");
            $stmt->execute([$historyId, $patientId]);
            $existingHistory = $stmt->fetch();

            if (!$existingHistory) {
                return $this->jsonResponse(['error' => 'Medical history record not found'], 404);
            }

            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);
            
            $condition = trim($input['condition'] ?? '');
            $diagnosis_date = trim($input['diagnosis_date'] ?? '');
            $status = trim($input['status'] ?? 'active');
            $notes = trim($input['notes'] ?? '');
            $category = trim($input['category'] ?? 'general');

            // Validate required fields
            if (empty($condition)) {
                return $this->jsonResponse(['error' => 'Medical condition is required'], 400);
            }

            // Validate date format if provided
            if (!empty($diagnosis_date) && !$this->validateDate($diagnosis_date)) {
                return $this->jsonResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
            }

            // Validate status
            $validStatuses = ['active', 'resolved', 'chronic', 'inactive'];
            if (!in_array($status, $validStatuses)) {
                return $this->jsonResponse(['error' => 'Invalid status. Must be: active, resolved, chronic, or inactive'], 400);
            }

            // Validate category
            $validCategories = ['general', 'allergy', 'medication', 'surgery', 'family_history', 'social_history'];
            if (!in_array($category, $validCategories)) {
                return $this->jsonResponse(['error' => 'Invalid category'], 400);
            }

            // Update medical history
            $stmt = $this->pdo->prepare("
                UPDATE medical_history_entries 
                SET condition_name = ?, diagnosis_date = ?, status = ?, notes = ?, category = ?, updated_at = NOW()
                WHERE id = ? AND patient_id = ?
            ");

            $result = $stmt->execute([
                $condition,
                !empty($diagnosis_date) ? $diagnosis_date : null,
                $status,
                !empty($notes) ? $notes : null,
                $category,
                $historyId,
                $patientId
            ]);

            if ($result) {
                // Get the updated record
                $stmt = $this->pdo->prepare("
                    SELECT mh.*, u.name as created_by_name 
                    FROM medical_history_entries mh 
                    LEFT JOIN users u ON mh.created_by = u.id 
                    WHERE mh.id = ?
                ");
                $stmt->execute([$historyId]);
                $history = $stmt->fetch();

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Medical history updated successfully',
                    'data' => $history
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update medical history'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteMedicalHistory($patientId, $historyId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Validate patient and history exist
            $stmt = $this->pdo->prepare("
                SELECT mh.* FROM medical_history_entries mh 
                WHERE mh.id = ? AND mh.patient_id = ?
            ");
            $stmt->execute([$historyId, $patientId]);
            $existingHistory = $stmt->fetch();

            if (!$existingHistory) {
                return $this->jsonResponse(['error' => 'Medical history record not found'], 404);
            }

            // Delete medical history
            $stmt = $this->pdo->prepare("DELETE FROM medical_history_entries WHERE id = ? AND patient_id = ?");
            $result = $stmt->execute([$historyId, $patientId]);

            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Medical history deleted successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to delete medical history'], 500);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function getMedicalHistoryEntry($patientId, $historyId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Validate patient exists
            $stmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $stmt->execute([$patientId]);
            if (!$stmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Get medical history entry
            $stmt = $this->pdo->prepare("
                SELECT mhe.*, u.name as created_by_name 
                FROM medical_history_entries mhe 
                LEFT JOIN users u ON mhe.created_by = u.id 
                WHERE mhe.id = ? AND mhe.patient_id = ?
            ");
            $stmt->execute([$historyId, $patientId]);
            $entry = $stmt->fetch();

            if (!$entry) {
                return $this->jsonResponse(['error' => 'Medical history entry not found'], 404);
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $entry
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function getPatientMedicalHistory($patientId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Validate patient exists
            $stmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $stmt->execute([$patientId]);
            if (!$stmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Get medical history from the main table (old format)
            $stmt = $this->pdo->prepare("
                SELECT *, 'old_format' as entry_type FROM medical_history 
                WHERE patient_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$patientId]);
            $oldHistory = $stmt->fetchAll();
            
            // Get medical history entries from the new table
            $stmt = $this->pdo->prepare("
                SELECT mhe.*, u.name as doctor_name, 'new_format' as entry_type
                FROM medical_history_entries mhe 
                LEFT JOIN users u ON mhe.created_by = u.id 
                WHERE mhe.patient_id = ? 
                ORDER BY mhe.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $newEntries = $stmt->fetchAll();
            
            // Convert new format entries to match old format structure
            $convertedEntries = [];
            foreach ($newEntries as $entry) {
                $converted = [
                    'id' => $entry['id'],
                    'patient_id' => $entry['patient_id'],
                    'allergies' => ($entry['category'] === 'allergy') ? $entry['notes'] : null,
                    'medications' => ($entry['category'] === 'medication') ? $entry['notes'] : null,
                    'systemic_history' => ($entry['category'] === 'general') ? $entry['notes'] : null,
                    'ocular_history' => ($entry['category'] === 'general' && strpos(strtolower($entry['condition_name'] ?? ''), 'eye') !== false) ? $entry['notes'] : null,
                    'prior_surgeries' => ($entry['category'] === 'surgery') ? $entry['notes'] : null,
                    'family_history' => ($entry['category'] === 'family_history') ? $entry['notes'] : null,
                    'notes' => $entry['notes'] ?? null,
                    'created_at' => $entry['created_at'],
                    'updated_at' => $entry['updated_at'],
                    'doctor_name' => $entry['doctor_name'],
                    'condition_name' => $entry['condition_name'],
                    'diagnosis_date' => $entry['diagnosis_date'],
                    'status' => $entry['status'],
                    'category' => $entry['category'],
                    'entry_type' => 'new_format'
                ];
                $convertedEntries[] = $converted;
            }
            
            // Merge all entries
            $allEntries = array_merge($oldHistory, $convertedEntries);
            
            // Sort by created_at descending
            usort($allEntries, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return $this->jsonResponse([
                'success' => true,
                'data' => $allEntries
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function getOphthalmologyNews()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $cacheFile = __DIR__ . '/../../storage/ophthalmology_news_cache.json';
            $cacheDuration = 20 * 60; // 20 minutes cache
            
            // Check cache
            if (file_exists($cacheFile)) {
                $cacheData = json_decode(file_get_contents($cacheFile), true);
                if ($cacheData && isset($cacheData['timestamp']) && 
                    (time() - $cacheData['timestamp']) < $cacheDuration) {
                    return $this->jsonResponse([
                        'success' => true,
                        'articles' => $cacheData['articles'],
                        'cached' => true
                    ]);
                }
            }

            // RSS Feeds - Ophthalmology News Sources
            $feeds = [
                [
                    'url' => 'https://bjo.bmj.com/rss/current.xml',
                    'icon' => '📄',
                    'source' => 'BJO',
                    'category' => 'research'
                ],
                [
                    'url' => 'https://www.nature.com/eye.rss',
                    'icon' => '👁️',
                    'source' => 'Nature Eye',
                    'category' => 'research'
                ],
                [
                    'url' => 'https://www.medpagetoday.com/rss/ophthalmology.xml',
                    'icon' => '📖',
                    'source' => 'MedPage Today',
                    'category' => 'clinical'
                ],
                [
                    'url' => 'https://www.retina-specialist.com/rss',
                    'icon' => '🔍',
                    'source' => 'Retina Specialist',
                    'category' => 'clinical'
                ],
                [
                    'url' => 'https://retinatoday.com/rss',
                    'icon' => '👁️',
                    'source' => 'Retina Today',
                    'category' => 'clinical'
                ],
                [
                    'url' => 'https://feeds.feedburner.com/MedicalNewsToday-Ophthalmology',
                    'icon' => '📰',
                    'source' => 'Medical News Today',
                    'category' => 'news'
                ],
                [
                    'url' => 'https://www.healio.com/rss/ophthalmology',
                    'icon' => '📋',
                    'source' => 'Healio',
                    'category' => 'news'
                ]
            ];

            $articles = [];
            $filter = $_GET['filter'] ?? 'all'; // all, research, clinical, news

            foreach ($feeds as $feed) {
                // Skip if filter doesn't match
                if ($filter !== 'all' && $feed['category'] !== $filter) {
                    continue;
                }

                try {
                    $xmlContent = $this->fetchRSSFeed($feed['url']);
                    if (!$xmlContent) {
                        error_log('Failed to fetch RSS feed: ' . $feed['url']);
                        continue;
                    }

                    // Suppress XML errors but log them
                    libxml_use_internal_errors(true);
                    $xml = simplexml_load_string($xmlContent);
                    $xmlErrors = libxml_get_errors();
                    libxml_clear_errors();
                    
                    if (!$xml) {
                        $errorMsg = !empty($xmlErrors) ? $xmlErrors[0]->message : 'Unknown XML error';
                        error_log('Failed to parse XML for ' . $feed['url'] . ': ' . $errorMsg);
                        continue;
                    }

                    // Handle different RSS formats (RSS 1.0, RSS 2.0, Atom)
                    $items = [];
                    
                    // Register common namespaces for xpath queries
                    $namespaces = $xml->getNamespaces(true);
                    // Register RSS 1.0 namespace explicitly
                    $xml->registerXPathNamespace('rss', 'http://purl.org/rss/1.0/');
                    $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
                    $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
                    // Register other namespaces
                    foreach ($namespaces as $prefix => $ns) {
                        if (!in_array($prefix, ['rss', 'rdf', 'dc'])) {
                            $xml->registerXPathNamespace($prefix, $ns);
                        }
                    }
                    
                    // Try RSS 2.0 format first (direct access)
                    if (isset($xml->channel->item)) {
                        $items = $xml->channel->item;
                    }
                    // Try Atom format (direct access)
                    elseif (isset($xml->entry)) {
                        $items = $xml->entry;
                    }
                    // Try RSS 1.0 (RDF) format using xpath (most reliable)
                    else {
                        // Try RSS 1.0 with namespace first
                        $items = $xml->xpath('//rss:item | //item | //entry');
                        if (empty($items)) {
                            // Try direct access for RSS 1.0 (may work in some cases)
                            if (isset($xml->item)) {
                                $items = $xml->item;
                            } else {
                                // Try with other namespaces
                                foreach ($namespaces as $prefix => $ns) {
                                    $items = $xml->xpath("//{$prefix}:item | //item");
                                    if (!empty($items)) break;
                                }
                            }
                        }
                    }
                    
                    if (empty($items)) {
                        error_log('No items found in RSS feed: ' . $feed['url'] . ' (XML root: ' . $xml->getName() . ')');
                        continue;
                    }

                    // Convert SimpleXMLElement to array if needed (for RSS 2.0 direct access)
                    if ($items instanceof \SimpleXMLElement && !is_array($items)) {
                        $itemsArray = [];
                        foreach ($items as $item) {
                            $itemsArray[] = $item;
                        }
                        $items = $itemsArray;
                    }

                    // Get more items (up to 10 per feed) to ensure we have enough
                    foreach (array_slice($items, 0, 10) as $item) {
                        // Handle RSS 2.0 and RSS 1.0
                        $title = '';
                        $link = '';
                        $description = '';
                        $pubDate = '';
                        
                        // Get title - handle RSS 1.0 RDF
                        if (isset($item->title)) {
                            $title = trim((string)$item->title);
                        } else {
                            // Try xpath for RSS 1.0
                            $titleNodes = $item->xpath('.//title | .//dc:title');
                            if (!empty($titleNodes)) {
                                $title = trim((string)$titleNodes[0]);
                            }
                        }
                        
                        // Get link - handle RSS 1.0 RDF (rdf:about attribute)
                        if (isset($item->link)) {
                            // RSS 2.0: link is text, Atom: link can be href attribute
                            $link = trim((string)$item->link);
                            if (isset($item->link['href'])) {
                                $link = trim((string)$item->link['href']);
                            }
                        } else {
                            // Try rdf:about attribute (RSS 1.0)
                            // Get attributes from RDF namespace
                            $rdfAttrs = $item->attributes('http://www.w3.org/1999/02/22-rdf-syntax-ns#');
                            if (!empty($rdfAttrs) && isset($rdfAttrs['about'])) {
                                $link = trim((string)$rdfAttrs['about']);
                            } else {
                                // Try regular attributes
                                $attributes = $item->attributes();
                                if (isset($attributes['rdf:about'])) {
                                    $link = trim((string)$attributes['rdf:about']);
                                } elseif (isset($attributes['about'])) {
                                    $link = trim((string)$attributes['about']);
                                } else {
                                    // Try xpath
                                    $linkNodes = $item->xpath('.//link | .//dc:identifier');
                                    if (!empty($linkNodes)) {
                                        $link = trim((string)$linkNodes[0]);
                                    }
                                }
                            }
                        }
                        
                        // Get description
                        if (isset($item->description)) {
                            $description = (string)$item->description;
                        } elseif (isset($item->summary)) {
                            $description = (string)$item->summary;
                        } elseif (isset($item->content)) {
                            $description = (string)$item->content;
                        } else {
                            // Try xpath for RSS 1.0
                            $descNodes = $item->xpath('.//description | .//dc:description | .//content:encoded');
                            if (!empty($descNodes)) {
                                $description = (string)$descNodes[0];
                            }
                        }
                        
                        if (isset($item->pubDate)) {
                            $pubDate = (string)$item->pubDate;
                        } elseif (isset($item->date)) {
                            $pubDate = (string)$item->date;
                        } elseif (isset($item->published)) {
                            $pubDate = (string)$item->published;
                        } elseif (isset($item->{'dc:date'})) {
                            $pubDate = (string)$item->{'dc:date'};
                        } else {
                            // Try to get date from namespaces (RSS 1.0 RDF)
                            $namespaces = $item->getNamespaces(true);
                            foreach ($namespaces as $prefix => $ns) {
                                $dcDate = $item->xpath(".//{$prefix}:date");
                                if (!empty($dcDate)) {
                                    $pubDate = (string)$dcDate[0];
                                    break;
                                }
                            }
                            // If still empty, use current time
                            if (empty($pubDate)) {
                                $pubDate = date('r');
                            }
                        }
                        
                        // Skip if no title or link
                        if (empty($title) || empty($link)) {
                            error_log('Skipping item: missing title or link. Title: ' . substr($title, 0, 50) . ', Link: ' . substr($link, 0, 50));
                            continue;
                        }
                        
                        // Check for breaking news keywords
                        $isBreaking = $this->isBreakingNews($title, $description);
                        
                        $articles[] = [
                            'title' => mb_convert_encoding($title, 'UTF-8', 'UTF-8'),
                            'link' => $link,
                            'description' => mb_convert_encoding(strip_tags($description), 'UTF-8', 'UTF-8'),
                            'pubDate' => $pubDate,
                            'source' => $feed['source'],
                            'source_icon' => $feed['icon'],
                            'category' => $feed['category'],
                            'is_breaking' => $isBreaking
                        ];
                    }
                } catch (\Exception $e) {
                    // Silently skip failed feeds
                    error_log('RSS Feed Error for ' . $feed['url'] . ': ' . $e->getMessage());
                    continue;
                }
            }

            // Filter articles from last 3 months (90 days)
            $threeMonthsAgo = time() - (90 * 24 * 60 * 60);
            $filteredArticles = [];
            $allArticles = [];
            
            foreach ($articles as $article) {
                $allArticles[] = $article;
                
                // Try to parse date
                $articleTime = 0;
                if (!empty($article['pubDate'])) {
                    $articleTime = strtotime($article['pubDate']);
                }
                
                // Include if date is within last 3 months OR if date parsing failed (include anyway)
                if ($articleTime === false || $articleTime === 0 || $articleTime >= $threeMonthsAgo) {
                    $filteredArticles[] = $article;
                }
            }
            
            // If no recent articles, use all articles (no time filter)
            if (empty($filteredArticles) && !empty($allArticles)) {
                $filteredArticles = $allArticles;
            }
            
            // Sort by breaking news first, then by date (newest first)
            usort($filteredArticles, function($a, $b) {
                if ($a['is_breaking'] && !$b['is_breaking']) return -1;
                if (!$a['is_breaking'] && $b['is_breaking']) return 1;
                
                $timeA = 0;
                $timeB = 0;
                
                if (!empty($a['pubDate'])) {
                    $timeA = strtotime($a['pubDate']);
                    if ($timeA === false) $timeA = 0;
                }
                
                if (!empty($b['pubDate'])) {
                    $timeB = strtotime($b['pubDate']);
                    if ($timeB === false) $timeB = 0;
                }
                
                return $timeB - $timeA;
            });

            // Limit to 15 articles
            $articles = array_slice($filteredArticles, 0, 15);

            // If still no articles, try to return cached data even if expired
            if (empty($articles)) {
                if (file_exists($cacheFile)) {
                    $oldCache = json_decode(file_get_contents($cacheFile), true);
                    if ($oldCache && isset($oldCache['articles']) && !empty($oldCache['articles'])) {
                        error_log('Using expired cache as fallback');
                        $articles = array_slice($oldCache['articles'], 0, 15);
                    }
                }
                
                if (empty($articles)) {
                    error_log('No articles found from any RSS feed');
                    return $this->jsonResponse([
                        'success' => false,
                        'error' => 'No articles available',
                        'articles' => [],
                        'debug' => [
                            'feeds_attempted' => count($feeds),
                            'cache_exists' => file_exists($cacheFile)
                        ]
                    ]);
                }
            }

            // Save to cache
            $cacheData = [
                'timestamp' => time(),
                'articles' => $articles
            ];
            @file_put_contents($cacheFile, json_encode($cacheData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return $this->jsonResponse([
                'success' => true,
                'articles' => $articles,
                'cached' => false,
                'count' => count($articles)
            ]);

        } catch (\Exception $e) {
            error_log('Ophthalmology News API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function fetchRSSFeed($url)
    {
        // Use cURL with timeout
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Accept all encodings
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/rss+xml, application/xml, text/xml, */*',
            'Accept-Language: en-US,en;q=0.9'
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log('cURL error for ' . $url . ': ' . $curlError);
        }
        
        if ($httpCode === 200 && $content && strlen($content) > 100) {
            return $content;
        }
        
        error_log('Failed to fetch RSS feed: ' . $url . ' (HTTP: ' . $httpCode . ', Size: ' . strlen($content ?? '') . ')');
        return false;
    }

    private function isBreakingNews($title, $description)
    {
        $breakingKeywords = [
            'breaking', 'urgent', 'alert', 'critical', 'emergency',
            'important', 'warning', 'recall', 'withdrawal', 'adverse'
        ];
        
        $text = strtolower($title . ' ' . $description);
        
        foreach ($breakingKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function getPatientAppointments($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Permission denied'], 403);
            }

            $stmt = $this->pdo->prepare("
                SELECT a.id, a.date, a.start_time, a.end_time, a.visit_type, a.status
                FROM appointments a
                WHERE a.patient_id = ?
                ORDER BY a.date DESC, a.start_time DESC
                LIMIT 20
            ");
            $stmt->execute([$patientId]);
            $appointments = $stmt->fetchAll();

            return $this->jsonResponse([
                'success' => true,
                'data' => $appointments
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function getPatientAppointmentsHistory($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Permission denied'], 403);
            }

            // Check if rescheduled_from column exists
            $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
            $hasRescheduledFrom = $columnStmt->rowCount() > 0;
            
            // Build query with optional exclusion
            $sql = "
                SELECT a.*, 
                       CONCAT(u.name) as doctor_name,
                       d.display_name as doctor_display_name
                FROM appointments a
                LEFT JOIN doctors d ON a.doctor_id = d.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE a.patient_id = ?
            ";
            
            $params = [$patientId];
            
            // Get excludeAppointmentId from query string
            $excludeAppointmentId = $_GET['exclude'] ?? null;
            if ($excludeAppointmentId) {
                $sql .= " AND a.id != ?";
                $params[] = $excludeAppointmentId;
            }
            
            $sql .= " ORDER BY a.date DESC, a.start_time DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $appointments = $stmt->fetchAll();
            
            // For each appointment, get prescriptions, glasses, consultation notes, and follow-up info
            foreach ($appointments as &$appointment) {
                // Determine if this is a follow-up appointment
                $appointment['is_followup'] = false;
                $appointment['original_appointment_id'] = null;
                
                if ($hasRescheduledFrom && $appointment['visit_type'] === 'FollowUp' && !empty($appointment['rescheduled_from'])) {
                    $appointment['is_followup'] = true;
                    $appointment['original_appointment_id'] = $appointment['rescheduled_from'];
                }
                
                // Get medication prescriptions
                $medStmt = $this->pdo->prepare("SELECT * FROM prescriptions WHERE appointment_id = ?");
                $medStmt->execute([$appointment['id']]);
                $appointment['medications'] = $medStmt->fetchAll();
                
                // Get glasses prescriptions
                $glassesStmt = $this->pdo->prepare("SELECT * FROM glasses_prescriptions WHERE appointment_id = ?");
                $glassesStmt->execute([$appointment['id']]);
                $appointment['glasses'] = $glassesStmt->fetchAll();
                
                // Get consultation notes
                $notesStmt = $this->pdo->prepare("SELECT * FROM consultation_notes WHERE appointment_id = ? ORDER BY created_at DESC LIMIT 1");
                $notesStmt->execute([$appointment['id']]);
                $appointment['consultation_note'] = $notesStmt->fetch();
                
                // Get attachments
                $attachmentsStmt = $this->pdo->prepare("SELECT id, filename, original_filename, file_path, mime_type, description FROM patient_attachments WHERE appointment_id = ? ORDER BY created_at DESC");
                $attachmentsStmt->execute([$appointment['id']]);
                $appointment['attachments'] = $attachmentsStmt->fetchAll();
                $appointment['attachments_count'] = count($appointment['attachments']);
            }

            return $this->jsonResponse([
                'ok' => true,
                'success' => true,
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function checkExportAccess($patientId)
    {
        try {
            if (!$this->auth->check()) {
                http_response_code(401);
                exit;
            }
            
            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                http_response_code(403);
                exit;
            }
            
            // Check if patient exists
            $stmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $stmt->execute([$patientId]);
            $patient = $stmt->fetch();
            
            if (!$patient) {
                http_response_code(404);
                exit;
            }
            
            // If we reach here, access is allowed
            http_response_code(200);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(500);
            exit;
        }
    }
    
    public function exportPatientData($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Permission denied'], 403);
            }

            // Get patient data
            $patientData = $this->getPatientDataForExport($patientId);
            
            if (!$patientData) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Generate Word document
            $filename = $this->generatePatientWordDocument($patientData);
            
            // Set headers for file download
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="Patient_' . $patientData['patient']['id'] . '_' . date('Y-m-d') . '.docx"');
            header('Content-Length: ' . filesize($filename));
            
            // Output the file
            readfile($filename);
            
            // Clean up temporary files
            unlink($filename);
            $this->cleanupTempImages();
            
                exit;

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function getGlassesPrescription($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['error' => 'Permission denied'], 403);
            }

            $stmt = $this->pdo->prepare("
                SELECT g.*, a.patient_id, a.date as appointment_date
                FROM glasses_prescriptions g
                JOIN appointments a ON g.appointment_id = a.id
                WHERE g.id = ?
            ");
            $stmt->execute([$id]);
            $prescription = $stmt->fetch();

            if (!$prescription) {
                return $this->jsonResponse(['error' => 'Glasses prescription not found'], 404);
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $prescription
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    private function getPatientDataForExport($patientId)
    {
        try {
            // Get patient basic information
            $stmt = $this->pdo->prepare("SELECT * FROM patients WHERE id = ?");
            $stmt->execute([$patientId]);
            $patient = $stmt->fetch();
            
            if (!$patient) {
                return null;
            }

            // Get medical history
            $stmt = $this->pdo->prepare("
                SELECT mhe.*, u.name as doctor_name
                FROM medical_history_entries mhe
                LEFT JOIN users u ON mhe.created_by = u.id
                WHERE mhe.patient_id = ?
                ORDER BY mhe.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $medicalHistory = $stmt->fetchAll();

            // Get old format medical history if exists
            $stmt = $this->pdo->prepare("SELECT * FROM medical_history WHERE patient_id = ? ORDER BY created_at DESC");
            $stmt->execute([$patientId]);
            $oldMedicalHistory = $stmt->fetchAll();

            // Get recent appointments
            $stmt = $this->pdo->prepare("
                SELECT a.*, u.name as doctor_name
                FROM appointments a
                LEFT JOIN users u ON a.doctor_id = u.id
                WHERE a.patient_id = ?
                ORDER BY a.date DESC, a.start_time DESC
                LIMIT 10
            ");
            $stmt->execute([$patientId]);
            $appointments = $stmt->fetchAll();

            // Get patient notes
            $stmt = $this->pdo->prepare("
                SELECT pn.*, u.name as doctor_name
                FROM patient_notes pn
                LEFT JOIN users u ON pn.doctor_id = u.id
                WHERE pn.patient_id = ?
                ORDER BY pn.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $notes = $stmt->fetchAll();

            // Get glasses prescriptions
            $stmt = $this->pdo->prepare("
                SELECT gp.*, a.date as appointment_date, u.name as doctor_name
                FROM glasses_prescriptions gp
                JOIN appointments a ON gp.appointment_id = a.id
                LEFT JOIN users u ON a.doctor_id = u.id
                WHERE a.patient_id = ?
                ORDER BY gp.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $glassesPrescriptions = $stmt->fetchAll();

            // Get patient attachments
            $stmt = $this->pdo->prepare("
                SELECT * FROM patient_attachments
                WHERE patient_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$patientId]);
            $attachments = $stmt->fetchAll();

            return [
                'patient' => $patient,
                'medical_history' => $medicalHistory,
                'old_medical_history' => $oldMedicalHistory,
                'appointments' => $appointments,
                'notes' => $notes,
                'glasses_prescriptions' => $glassesPrescriptions,
                'attachments' => $attachments
            ];
            
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generatePatientWordDocument($data)
    {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        // Create document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Roaya Clinic Management System');
        $properties->setCompany('Roaya Clinic');
        $properties->setTitle('Patient Data Export - ' . $data['patient']['first_name'] . ' ' . $data['patient']['last_name']);
        $properties->setDescription('Complete patient data export including medical history, notes, and files');

        // Add a section
        $section = $phpWord->addSection([
            'marginLeft' => 720,   // 0.5 inch
            'marginRight' => 720,
            'marginTop' => 720,
            'marginBottom' => 720
        ]);

        // Header styles
        $headerStyle = ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '2E74B5'];
        $subHeaderStyle = ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '1F497D'];
        $normalStyle = ['name' => 'Arial', 'size' => 11];
        $tableHeaderStyle = ['bold' => true, 'color' => '000000'];

        // Title
        $section->addText('PATIENT DATA EXPORT', $headerStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Roaya Clinic Management System', $normalStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Export Date: ' . date('F j, Y'), $normalStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(2);

        // Patient Information
        $section->addText('PATIENT INFORMATION', $subHeaderStyle);
        $section->addTextBreak();

        $patientTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '1F497D',
            'cellMargin' => 80
        ]);

        $this->addTableRow($patientTable, 'Patient ID', '#' . $data['patient']['id'], $tableHeaderStyle);
        $fullName = trim(($data['patient']['first_name'] ?? '') . ' ' . ($data['patient']['last_name'] ?? ''));
        $this->addTableRow($patientTable, 'Full Name', $fullName, $tableHeaderStyle);
        $this->addTableRow($patientTable, 'Date of Birth', $data['patient']['dob'] ? date('F j, Y', strtotime($data['patient']['dob'])) : 'Not specified', $tableHeaderStyle);
        
        if ($data['patient']['dob']) {
            $age = date_diff(date_create($data['patient']['dob']), date_create('now'))->y;
            $this->addTableRow($patientTable, 'Age', $age . ' years old', $tableHeaderStyle);
        }
        
        $this->addTableRow($patientTable, 'Gender', ucfirst($data['patient']['gender'] ?? 'Not specified'), $tableHeaderStyle);
        $this->addTableRow($patientTable, 'Phone', $data['patient']['phone'] ?? 'Not specified', $tableHeaderStyle);
        
        if ($data['patient']['alt_phone']) {
            $this->addTableRow($patientTable, 'Alternative Phone', $data['patient']['alt_phone'], $tableHeaderStyle);
        }
        
        if ($data['patient']['address']) {
            $this->addTableRow($patientTable, 'Address', $data['patient']['address'], $tableHeaderStyle);
        }
        
        if ($data['patient']['national_id']) {
            $this->addTableRow($patientTable, 'National ID', $data['patient']['national_id'], $tableHeaderStyle);
        }
        
        if ($data['patient']['emergency_contact']) {
            $this->addTableRow($patientTable, 'Emergency Contact', $data['patient']['emergency_contact'], $tableHeaderStyle);
        }
        
        if ($data['patient']['emergency_phone']) {
            $this->addTableRow($patientTable, 'Emergency Phone', $data['patient']['emergency_phone'], $tableHeaderStyle);
        }

        $section->addTextBreak(2);

        // Medical History
        if (!empty($data['medical_history']) || !empty($data['old_medical_history'])) {
            $section->addText('MEDICAL HISTORY', $subHeaderStyle);
            $section->addTextBreak();

            // New format medical history
            if (!empty($data['medical_history'])) {
                foreach ($data['medical_history'] as $history) {
                    $historyTable = $section->addTable([
                        'borderSize' => 6,
                        'borderColor' => '28A745',
                        'cellMargin' => 80
                    ]);

                    $this->addTableRow($historyTable, 'Condition', $history['condition_name'] ?? 'Not specified', $tableHeaderStyle);
                    $this->addTableRow($historyTable, 'Category', ucfirst(str_replace('_', ' ', $history['category'] ?? 'general')), $tableHeaderStyle);
                    $this->addTableRow($historyTable, 'Status', ucfirst($history['status'] ?? 'active'), $tableHeaderStyle);
                    
                    if ($history['diagnosis_date']) {
                        $this->addTableRow($historyTable, 'Diagnosis Date', date('F j, Y', strtotime($history['diagnosis_date'])), $tableHeaderStyle);
                    }
                    
                    if ($history['notes']) {
                        $this->addTableRow($historyTable, 'Notes', $history['notes'], $tableHeaderStyle);
                    }
                    
                    if ($history['doctor_name']) {
                        $this->addTableRow($historyTable, 'Added By', 'Dr. ' . $history['doctor_name'], $tableHeaderStyle);
                    }
                    
                    $this->addTableRow($historyTable, 'Date Added', date('F j, Y', strtotime($history['created_at'])), $tableHeaderStyle);
                    
                    $section->addTextBreak();
                }
            }

            // Old format medical history
            if (!empty($data['old_medical_history'])) {
                foreach ($data['old_medical_history'] as $history) {
                    $historyTable = $section->addTable([
                        'borderSize' => 6,
                        'borderColor' => 'FFC107',
                        'cellMargin' => 80
                    ]);

                    if ($history['allergies']) {
                        $this->addTableRow($historyTable, 'Allergies', $history['allergies'], $tableHeaderStyle);
                    }
                    if ($history['medications']) {
                        $this->addTableRow($historyTable, 'Medications', $history['medications'], $tableHeaderStyle);
                    }
                    if ($history['systemic_history']) {
                        $this->addTableRow($historyTable, 'Systemic History', $history['systemic_history'], $tableHeaderStyle);
                    }
                    if ($history['ocular_history']) {
                        $this->addTableRow($historyTable, 'Ocular History', $history['ocular_history'], $tableHeaderStyle);
                    }
                    if ($history['prior_surgeries']) {
                        $this->addTableRow($historyTable, 'Prior Surgeries', $history['prior_surgeries'], $tableHeaderStyle);
                    }
                    if ($history['family_history']) {
                        $this->addTableRow($historyTable, 'Family History', $history['family_history'], $tableHeaderStyle);
                    }
                    
                    $this->addTableRow($historyTable, 'Date Added', date('F j, Y', strtotime($history['created_at'])), $tableHeaderStyle);
                    
                    $section->addTextBreak();
                }
            }
        }

        // Recent Appointments
        if (!empty($data['appointments'])) {
            $section->addText('RECENT APPOINTMENTS', $subHeaderStyle);
            $section->addTextBreak();

            foreach ($data['appointments'] as $appointment) {
                $appointmentTable = $section->addTable([
                    'borderSize' => 6,
                    'borderColor' => '17A2B8',
                    'cellMargin' => 80
                ]);

                $this->addTableRow($appointmentTable, 'Date', date('F j, Y', strtotime($appointment['date'])), $tableHeaderStyle);
                $this->addTableRow($appointmentTable, 'Time', date('g:i A', strtotime($appointment['start_time'])) . ' - ' . date('g:i A', strtotime($appointment['end_time'])), $tableHeaderStyle);
                $this->addTableRow($appointmentTable, 'Visit Type', $appointment['visit_type'] ?? 'Not specified', $tableHeaderStyle);
                $this->addTableRow($appointmentTable, 'Status', ucfirst($appointment['status'] ?? 'unknown'), $tableHeaderStyle);
                
                if ($appointment['doctor_name']) {
                    $this->addTableRow($appointmentTable, 'Doctor', 'Dr. ' . $appointment['doctor_name'], $tableHeaderStyle);
                }
                
                $section->addTextBreak();
            }
        }

        // Patient Notes
        if (!empty($data['notes'])) {
            $section->addText('MEDICAL NOTES', $subHeaderStyle);
            $section->addTextBreak();

            foreach ($data['notes'] as $note) {
                $noteTable = $section->addTable([
                    'borderSize' => 6,
                    'borderColor' => '6C757D',
                    'cellMargin' => 80
                ]);

                $this->addTableRow($noteTable, 'Title', $note['title'], $tableHeaderStyle);
                $this->addTableRow($noteTable, 'Content', $note['content'], $tableHeaderStyle);
                
                if ($note['doctor_name']) {
                    $this->addTableRow($noteTable, 'Added By', 'Dr. ' . $note['doctor_name'], $tableHeaderStyle);
                }
                
                $this->addTableRow($noteTable, 'Date Added', date('F j, Y g:i A', strtotime($note['created_at'])), $tableHeaderStyle);
                
                $section->addTextBreak();
            }
        }

        // Glasses Prescriptions
        if (!empty($data['glasses_prescriptions'])) {
            $section->addText('GLASSES PRESCRIPTIONS', $subHeaderStyle);
            $section->addTextBreak();

            foreach ($data['glasses_prescriptions'] as $prescription) {
                $prescriptionTable = $section->addTable([
                    'borderSize' => 6,
                    'borderColor' => 'DC3545',
                    'cellMargin' => 80
                ]);

                $this->addTableRow($prescriptionTable, 'Date', date('F j, Y', strtotime($prescription['created_at'])), $tableHeaderStyle);
                $this->addTableRow($prescriptionTable, 'Appointment Date', date('F j, Y', strtotime($prescription['appointment_date'])), $tableHeaderStyle);
                $this->addTableRow($prescriptionTable, 'Lens Type', $prescription['lens_type'], $tableHeaderStyle);

                // Distance Vision
                if ($prescription['distance_sphere_r'] !== null || $prescription['distance_sphere_l'] !== null) {
                    $distanceR = sprintf('%+.2f', $prescription['distance_sphere_r'] ?? 0);
                    if ($prescription['distance_cylinder_r']) {
                        $distanceR .= sprintf(' %+.2f', $prescription['distance_cylinder_r']);
                    }
                    if ($prescription['distance_axis_r']) {
                        $distanceR .= ' x ' . $prescription['distance_axis_r'];
                    }

                    $distanceL = sprintf('%+.2f', $prescription['distance_sphere_l'] ?? 0);
                    if ($prescription['distance_cylinder_l']) {
                        $distanceL .= sprintf(' %+.2f', $prescription['distance_cylinder_l']);
                    }
                    if ($prescription['distance_axis_l']) {
                        $distanceL .= ' x ' . $prescription['distance_axis_l'];
                    }

                    $this->addTableRow($prescriptionTable, 'Distance Vision (R)', $distanceR, $tableHeaderStyle);
                    $this->addTableRow($prescriptionTable, 'Distance Vision (L)', $distanceL, $tableHeaderStyle);
                }

                // Near Vision
                if ($prescription['near_sphere_r'] !== null || $prescription['near_sphere_l'] !== null) {
                    $nearR = sprintf('%+.2f', $prescription['near_sphere_r'] ?? 0);
                    if ($prescription['near_cylinder_r']) {
                        $nearR .= sprintf(' %+.2f', $prescription['near_cylinder_r']);
                    }
                    if ($prescription['near_axis_r']) {
                        $nearR .= ' x ' . $prescription['near_axis_r'];
                    }

                    $nearL = sprintf('%+.2f', $prescription['near_sphere_l'] ?? 0);
                    if ($prescription['near_cylinder_l']) {
                        $nearL .= sprintf(' %+.2f', $prescription['near_cylinder_l']);
                    }
                    if ($prescription['near_axis_l']) {
                        $nearL .= ' x ' . $prescription['near_axis_l'];
                    }

                    $this->addTableRow($prescriptionTable, 'Near Vision (R)', $nearR, $tableHeaderStyle);
                    $this->addTableRow($prescriptionTable, 'Near Vision (L)', $nearL, $tableHeaderStyle);
                }

                // PD
                if ($prescription['PD_DISTANCE'] || $prescription['PD_NEAR']) {
                    if ($prescription['PD_DISTANCE']) {
                        $this->addTableRow($prescriptionTable, 'PD Distance', $prescription['PD_DISTANCE'] . 'mm', $tableHeaderStyle);
                    }
                    if ($prescription['PD_NEAR']) {
                        $this->addTableRow($prescriptionTable, 'PD Near', $prescription['PD_NEAR'] . 'mm', $tableHeaderStyle);
                    }
                }

                if ($prescription['comments']) {
                    $this->addTableRow($prescriptionTable, 'Comments', $prescription['comments'], $tableHeaderStyle);
                }
                
                if ($prescription['doctor_name']) {
                    $this->addTableRow($prescriptionTable, 'Prescribed By', 'Dr. ' . $prescription['doctor_name'], $tableHeaderStyle);
                }
                
                $section->addTextBreak();
            }
        }

        // Patient Files/Attachments
        if (!empty($data['attachments'])) {
            $section->addText('PATIENT FILES AND ATTACHMENTS', $subHeaderStyle);
            $section->addTextBreak();

            foreach ($data['attachments'] as $attachment) {
                $attachmentTable = $section->addTable([
                    'borderSize' => 6,
                    'borderColor' => 'FD7E14',
                    'cellMargin' => 80
                ]);

                $this->addTableRow($attachmentTable, 'File Name', $attachment['original_filename'], $tableHeaderStyle);
                $this->addTableRow($attachmentTable, 'File Type', ucfirst(str_replace('_', ' ', $attachment['file_type'] ?? 'document')), $tableHeaderStyle);
                $this->addTableRow($attachmentTable, 'File Size', number_format($attachment['file_size'] / 1024, 1) . ' KB', $tableHeaderStyle);
                
                if ($attachment['description']) {
                    $this->addTableRow($attachmentTable, 'Description', $attachment['description'], $tableHeaderStyle);
                }
                
                $this->addTableRow($attachmentTable, 'Upload Date', date('F j, Y g:i A', strtotime($attachment['created_at'])), $tableHeaderStyle);

                // Add image if it's an image file and not too large
                $fileExt = strtolower(pathinfo($attachment['original_filename'], PATHINFO_EXTENSION));
                $isImageFile = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp']) || 
                              (isset($attachment['mime_type']) && strpos($attachment['mime_type'], 'image/') === 0);
                
                if ($isImageFile && $attachment['file_size'] < 5000000) { // Less than 5MB
                    // Build correct path to the image file
                    $imagePath = __DIR__ . '/../../' . $attachment['file_path'];
                    if (file_exists($imagePath) && is_readable($imagePath)) {
                        try {
                            
                            // Get image info
                            $imageInfo = getimagesize($imagePath);
                            if (!$imageInfo) {
                                throw new \Exception("Cannot get image information");
                            }
                            
                            $originalWidth = $imageInfo[0];
                            $originalHeight = $imageInfo[1];
                            $mimeType = $imageInfo['mime'];
                            
                            
                            // Create a copy in temp directory with proper permissions
                            $tempImagePath = sys_get_temp_dir() . '/export_image_' . time() . '_' . mt_rand(1000, 9999) . '.jpg';
                            
                            // Always convert to JPEG for maximum Word compatibility
                            $this->convertImageToJpeg($imagePath, $tempImagePath, 400, 400);
                            
                            if (file_exists($tempImagePath)) {
                                
                                $section->addTextBreak();
                                
                                // Add image label
                                $section->addText('Image Preview:', ['bold' => true, 'size' => 11]);
                                $section->addTextBreak();
                                
                                // Calculate display size while maintaining aspect ratio
                                $ratio = min(200 / $originalWidth, 200 / $originalHeight);
                                $displayWidth = intval($originalWidth * $ratio);
                                $displayHeight = intval($originalHeight * $ratio);
                                
                                // Add the image using the most basic method
                                $section->addImage($tempImagePath, [
                                    'width' => $displayWidth,
                                    'height' => $displayHeight
                                ]);
                                
                                $section->addTextBreak();
                                
                                // Don't delete the temp image yet - PHPWord may need it during save
                                // We'll clean it up after the document is generated
                                $this->tempImagesToCleanup[] = $tempImagePath;
                                
                            } else {
                                throw new \Exception("Failed to create temporary image file");
                            }
                        } catch (\Exception $e) {
                            // Add note that image couldn't be loaded
                            $section->addTextBreak();
                            $section->addText('Note: Image could not be embedded in document. (' . $attachment['original_filename'] . ')', ['italic' => true, 'color' => '666666']);
                            $section->addTextBreak();
                        }
                    } else {
                    }
                }
                
                $section->addTextBreak();
            }
        }

        // Footer
        $section->addTextBreak(2);
        $section->addText('Generated by Roaya Clinic Management System on ' . date('F j, Y \a\t g:i A'), $normalStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // Save to temporary file
        $tempPath = sys_get_temp_dir() . '/patient_export_' . $data['patient']['id'] . '_' . time() . '.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempPath);

        return $tempPath;
    }

    private function addTableRow($table, $label, $value, $headerStyle)
    {
        $row = $table->addRow();
        $row->addCell(3000)->addText($label, $headerStyle, ['bgColor' => '1F497D']);
        $row->addCell(6000)->addText($value, ['name' => 'Arial', 'size' => 11]);
    }

    private function resizeImage($sourcePath, $maxWidth, $maxHeight)
    {
        if (!extension_loaded('gd')) {
            return $sourcePath; // Return original if GD not available
        }

        if (!file_exists($sourcePath)) {
            return false;
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return $sourcePath;
        }

        list($originalWidth, $originalHeight, $imageType) = $imageInfo;

        // Check if resize is needed
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return $sourcePath;
        }

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);

        // Create source image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return $sourcePath;
        }

        if (!$sourceImage) {
            return $sourcePath;
        }

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefill($newImage, 0, 0, $transparent);
        }

        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Save to temporary file - always save as JPEG for consistency in Word
        $tempPath = sys_get_temp_dir() . '/resized_' . basename($sourcePath, '.' . pathinfo($sourcePath, PATHINFO_EXTENSION)) . '_' . time() . '.jpg';
        
        // Always save as JPEG for better Word compatibility
        $saved = imagejpeg($newImage, $tempPath, 85);

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        if ($saved && file_exists($tempPath)) {
            return $tempPath;
        } else {
            return $sourcePath;
        }
    }

    private function convertImageToJpeg($sourcePath, $outputPath, $maxWidth, $maxHeight)
    {
        if (!extension_loaded('gd')) {
            return copy($sourcePath, $outputPath);
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return copy($sourcePath, $outputPath);
        }

        list($originalWidth, $originalHeight, $imageType) = $imageInfo;

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);

        // Create source image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return copy($sourcePath, $outputPath);
        }

        if (!$sourceImage) {
            return copy($sourcePath, $outputPath);
        }

        // Create new image with white background (important for Word)
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $white);

        // Resize and copy
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Save as JPEG with high quality
        $saved = imagejpeg($newImage, $outputPath, 90);

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        if ($saved && file_exists($outputPath)) {
            return true;
        } else {
            return false;
        }
    }
    
    private function cleanupTempImages()
    {
        foreach ($this->tempImagesToCleanup as $tempPath) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
        $this->tempImagesToCleanup = [];
    }

    private function validateDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    // Delete Consultation Note
    public function deleteConsultationNote($noteId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Check if consultation note exists
            $stmt = $this->pdo->prepare("SELECT * FROM consultation_notes WHERE id = ?");
            $stmt->execute([$noteId]);
            $note = $stmt->fetch();

            if (!$note) {
                return $this->jsonResponse(['error' => 'Consultation note not found'], 404);
            }

            // Delete the consultation note
            $stmt = $this->pdo->prepare("DELETE FROM consultation_notes WHERE id = ?");
            $stmt->execute([$noteId]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Consultation note deleted successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to delete consultation note'], 500);
        }
    }

    public function searchDrugs()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }
            
            $searchTerm = $_GET['q'] ?? '';
            $limit = min((int)($_GET['limit'] ?? 20), 50); // Max 50 results
            $category = $_GET['category'] ?? '';
            $company = $_GET['company'] ?? '';
            $route = $_GET['route'] ?? '';
            
            // If no search term and no filters, return empty
            if (strlen($searchTerm) < 2 && empty($category) && empty($company) && empty($route)) {
                return $this->jsonResponse(['drugs' => []]);
            }
            
            // Connect to drugs database
            $drugsPdo = $this->getDrugsDatabaseConnection();
            
            // Build WHERE clause with filters
            $whereConditions = [];
            $params = [];
            
            // Add search conditions only if search term exists
            if (strlen($searchTerm) >= 2) {
                $searchTerm = '%' . $searchTerm . '%';
                $whereConditions = [
                    '(',
                    'FirstName LIKE ? OR',
                    'LastName LIKE ? OR',
                    'Company LIKE ? OR',
                    'Pharmacology LIKE ? OR',
                    'SRDE LIKE ?',
                    ')'
                ];
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
            } else {
                // If no search term, just get all records (will be filtered by category/company/route)
                $whereConditions = ['1=1'];
            }
            
            if (!empty($category)) {
                $whereConditions[] = 'AND Pharmacology = ?';
                $params[] = $category;
            }
            
            if (!empty($company)) {
                $whereConditions[] = 'AND Company = ?';
                $params[] = $company;
            }
            
            if (!empty($route)) {
                $whereConditions[] = 'AND Route = ?';
                $params[] = $route;
            }
            
            $whereClause = implode(' ', $whereConditions);
            
            // Debug logging
            
            // Also log the full SQL query for debugging
            $fullQuery = "SELECT ID, FirstName as drug_name, LastName as active_ingredient, price, Company, Pharmacology as category, Route as administration_route, SRDE, GI FROM drugs WHERE {$whereClause} {$orderBy} LIMIT ?";
            
            // Build ORDER BY clause
            $orderBy = '';
            if (strlen($searchTerm) >= 2) {
                $exactMatch = '%' . trim($_GET['q'] ?? '') . '%';
                $orderBy = "
                    ORDER BY 
                        CASE 
                            WHEN FirstName LIKE ? THEN 1
                            WHEN LastName LIKE ? THEN 2
                            WHEN Company LIKE ? THEN 3
                            ELSE 4
                        END,
                        FirstName
                ";
                $params[] = $exactMatch;
                $params[] = $exactMatch;
                $params[] = $exactMatch;
            } else {
                $orderBy = "ORDER BY FirstName";
            }
            
            $stmt = $drugsPdo->prepare("
                SELECT 
                    ID,
                    FirstName as drug_name,
                    LastName as active_ingredient,
                    price,
                    Company,
                    Pharmacology as category,
                    Route as administration_route,
                    SRDE,
                    GI
                FROM drugs 
                WHERE {$whereClause}
                {$orderBy}
                LIMIT ?
            ");
            
            $params[] = $limit;
            
            $stmt->execute($params);
            
            $drugs = $stmt->fetchAll();
            
            if (count($drugs) > 0) {
            }
            
            return $this->jsonResponse(['drugs' => $drugs]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to search drugs'], 500);
        }
    }

    public function getDrugDetails()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }
            
            $drugId = $_GET['id'] ?? null;
            
            if (!$drugId) {
                return $this->jsonResponse(['error' => 'Drug ID is required'], 400);
            }

            // Connect to drugs database
            $drugsPdo = $this->getDrugsDatabaseConnection();
            
            $stmt = $drugsPdo->prepare("
                SELECT 
                    ID,
                    FirstName as drug_name,
                    LastName as active_ingredient,
                    price,
                    priceold,
                    Company,
                    Pharmacology as category,
                    Route as administration_route,
                    SRDE,
                    GI,
                    imageid
                FROM drugs 
                WHERE ID = ?
            ");
            
            $stmt->execute([$drugId]);
            $drug = $stmt->fetch();
            
            if (!$drug) {
                return $this->jsonResponse(['error' => 'Drug not found'], 404);
            }
            
            return $this->jsonResponse(['drug' => $drug]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to get drug details'], 500);
        }
    }

    public function getFilterOptions()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Connect to drugs database
            $drugsPdo = $this->getDrugsDatabaseConnection();
            
            // Get unique categories
            $stmt = $drugsPdo->prepare("SELECT DISTINCT Pharmacology as category FROM drugs WHERE Pharmacology IS NOT NULL AND Pharmacology != '' ORDER BY Pharmacology");
            $stmt->execute();
            $categories = $stmt->fetchAll();
            
            // Get unique companies
            $stmt = $drugsPdo->prepare("SELECT DISTINCT Company FROM drugs WHERE Company IS NOT NULL AND Company != '' ORDER BY Company");
            $stmt->execute();
            $companies = $stmt->fetchAll();
            
            // Get unique routes
            $stmt = $drugsPdo->prepare("SELECT DISTINCT Route as route FROM drugs WHERE Route IS NOT NULL AND Route != '' ORDER BY Route");
            $stmt->execute();
            $routes = $stmt->fetchAll();
            
            return $this->jsonResponse([
                'categories' => array_column($categories, 'category'),
                'companies' => array_column($companies, 'Company'),
                'routes' => array_column($routes, 'route')
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to get filter options'], 500);
        }
    }

    private function getDrugsDatabaseConnection()
    {
        // Connect to egyptian_drugs database with specific user
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $username = 'hclinic_drugs';  // Use the correct user for drugs database
        $password = 'Carmen@1230';  // Use the correct password for drugs database
        
        $dsn = "mysql:host={$host};dbname=hclinic_drugs;charset=utf8mb4";
        
        return new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]);
    }

    public function getMostUsedDrugs()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $limit = min((int)($_GET['limit'] ?? 10), 20); // Max 20 results, default 10

            // Check if prescriptions table exists and has data
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM prescriptions WHERE drug_name IS NOT NULL AND drug_name != ''");
            $checkStmt->execute();
            $count = $checkStmt->fetch()['count'];
            
            if ($count == 0) {
                return $this->jsonResponse(['drugs' => []]);
            }

            // Get most used drugs from prescriptions table
            $stmt = $this->pdo->prepare("
                SELECT 
                    drug_name,
                    COUNT(*) as usage_count,
                    GROUP_CONCAT(DISTINCT frequency ORDER BY frequency SEPARATOR ', ') as common_frequencies,
                    GROUP_CONCAT(DISTINCT dose ORDER BY dose SEPARATOR ', ') as common_doses
                FROM prescriptions 
                WHERE drug_name IS NOT NULL 
                AND drug_name != ''
                GROUP BY drug_name 
                ORDER BY usage_count DESC 
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            $drugs = $stmt->fetchAll();

            // Format the response
            $formattedDrugs = array_map(function($drug) {
                return [
                    'drug_name' => $drug['drug_name'],
                    'usage_count' => (int)$drug['usage_count'],
                    'common_frequencies' => $drug['common_frequencies'] ?: 'N/A',
                    'common_doses' => $drug['common_doses'] ?: 'N/A'
                ];
            }, $drugs);

            return $this->jsonResponse(['drugs' => $formattedDrugs]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to get most used drugs: ' . $e->getMessage()], 500);
        }
    }

    public function searchDrugsAutocomplete()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $searchTerm = $_GET['q'] ?? '';
            $limit = min((int)($_GET['limit'] ?? 10), 20); // Max 20 results, default 10
            
            if (strlen($searchTerm) < 2) {
                return $this->jsonResponse(['drugs' => []]);
            }

            // Connect to drugs database
            $drugsPdo = $this->getDrugsDatabaseConnection();
            
            $searchTerm = '%' . $searchTerm . '%';
            
            $stmt = $drugsPdo->prepare("
                SELECT 
                    ID,
                    FirstName as drug_name,
                    LastName as active_ingredient,
                    Company,
                    Pharmacology as category,
                    Route as administration_route
                FROM drugs 
                WHERE FirstName LIKE ? 
                ORDER BY 
                    CASE 
                        WHEN FirstName LIKE ? THEN 1
                        WHEN LastName LIKE ? THEN 2
                        WHEN Company LIKE ? THEN 3
                        ELSE 4
                    END,
                    FirstName
                LIMIT ?
            ");
            
            $exactMatch = '%' . trim($_GET['q'] ?? '') . '%';
            $stmt->execute([$searchTerm, $exactMatch, $exactMatch, $exactMatch, $limit]);
            
            $drugs = $stmt->fetchAll();
            
            return $this->jsonResponse(['drugs' => $drugs]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to search drugs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create daily balance entry
     */
    public function createDailyBalance()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            // Validate input
            $rules = [
                'amount' => 'required|decimal',
                'balance_type' => 'required|in:opening,additional,withdrawal',
                'description' => 'string',
                'balance_date' => 'datetime'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Create daily balance record
            $stmt = $this->pdo->prepare("
                INSERT INTO daily_balances (amount, balance_type, description, created_by, created_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $createdAt = !empty($data['balance_date']) ? $data['balance_date'] : date('Y-m-d H:i:s');
            
            $stmt->execute([
                $data['amount'],
                $data['balance_type'],
                $data['description'] ?? null,
                $user['id'],
                $createdAt
            ]);
            
            $balanceId = $this->pdo->lastInsertId();
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => ['id' => $balanceId],
                'message' => 'تم تسجيل الرصيد بنجاح'
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'فشل في تسجيل الرصيد'], 500);
        }
    }

    /**
     * Create daily closure (Doctor only) - API endpoint
     */
    public function createDailyClosureApi()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            // Check if user is doctor
            if ($user['role'] !== 'doctor') {
                return $this->jsonResponse(['error' => 'غير مصرح بالوصول - الطبيب فقط'], 403);
            }
            
            $today = date('Y-m-d');
            
            // Check if today is already closed
            if ($this->isDateClosed($today)) {
                return $this->jsonResponse(['error' => 'تم إغلاق اليوم مسبقاً'], 400);
            }
            
            // Create closure using existing method
            $closureId = $this->createDailyClosure($today, $user['id']);
            
            return $this->jsonResponse([
                'ok' => true,
                'message' => 'تم إغلاق اليوم بنجاح',
                'data' => ['id' => $closureId]
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'فشل في إغلاق اليوم'], 500);
        }
    }
    
    /**
     * Create expense entry
     */
    public function createExpense()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            // Validate input
            $rules = [
                'amount' => 'required|decimal',
                'expense_name' => 'required|string',
                'category' => 'required|in:utilities,medical,maintenance,office,salary,other',
                'notes' => 'string',
                'expense_date' => 'datetime'
            ];

            // Parse JSON data from request
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Create expense record
            $stmt = $this->pdo->prepare("
                INSERT INTO expenses (amount, expense_name, category, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $createdAt = !empty($data['expense_date']) ? $data['expense_date'] : date('Y-m-d H:i:s');
            
            $stmt->execute([
                $data['amount'],
                $data['expense_name'],
                $data['category'],
                $data['notes'] ?? null,
                $user['id'],
                $createdAt
            ]);
            
            $expenseId = $this->pdo->lastInsertId();
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => ['id' => $expenseId],
                'message' => 'تم تسجيل المصروف بنجاح'
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'فشل في تسجيل المصروف'], 500);
        }
    }

    /**
     * Update expense entry
     */
    public function updateExpense($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            // Validate input
            $rules = [
                'amount' => 'required|decimal',
                'expense_name' => 'required|string',
                'category' => 'required|in:utilities,medical,maintenance,office,salary,other',
                'notes' => 'string',
                'expense_date' => 'datetime'
            ];

            // For PUT requests, we need to parse the input differently
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $input = file_get_contents("php://input");
                $data = json_decode($input, true);
            } else {
                $data = $_POST;
            }
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Check if expense exists
            $stmt = $this->pdo->prepare("SELECT * FROM expenses WHERE id = ?");
            $stmt->execute([$id]);
            $expense = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$expense) {
                return $this->jsonResponse(['error' => 'Expense not found'], 404);
            }

            // Update expense record
            $stmt = $this->pdo->prepare("
                UPDATE expenses 
                SET amount = ?, expense_name = ?, category = ?, notes = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['amount'],
                $data['expense_name'],
                $data['category'],
                $data['notes'] ?? null,
                $id
            ]);
            
            if ($result) {
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Expense updated successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update expense'], 500);
            }
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to update expense'], 500);
        }
    }
    
    /**
     * Delete expense entry
     */
    public function deleteExpense($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Check if expense exists
            $stmt = $this->pdo->prepare("SELECT * FROM expenses WHERE id = ?");
            $stmt->execute([$id]);
            $expense = $stmt->fetch();
            
            if (!$expense) {
                return $this->jsonResponse(['error' => 'Expense not found'], 404);
            }

            // Delete expense record
            $stmt = $this->pdo->prepare("DELETE FROM expenses WHERE id = ?");
            $stmt->execute([$id]);
            
            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Expense deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to delete expense'], 500);
        }
    }

    /**
     * Update payment entry
     */
    public function updatePayment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            // Validate input
            $rules = [
                'amount' => 'required|decimal',
                'type' => 'required|in:Booking,FollowUp,Consultation,Procedure,Other',
                'method' => 'required|in:Cash,Card,Transfer,Wallet',
                'description' => 'string'
            ];

            // For PUT requests, we need to parse the input differently
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $input = file_get_contents("php://input");
                $data = json_decode($input, true);
            } else {
                $data = $_POST;
            }
            
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Check if payment exists
            $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->execute([$id]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$payment) {
                return $this->jsonResponse(['error' => 'Payment not found'], 404);
            }

            // Update payment record
            $stmt = $this->pdo->prepare("
                UPDATE payments 
                SET amount = ?, type = ?, method = ?, description = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['amount'],
                $data['type'],
                $data['method'],
                $data['description'] ?? null,
                $id
            ]);
            
            if ($result) {
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Payment updated successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update payment'], 500);
            }
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to update payment'], 500);
        }
    }

    /**
     * Delete payment entry
     */
    public function deletePayment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Check if payment exists
            $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->execute([$id]);
            $payment = $stmt->fetch();
            
            if (!$payment) {
                return $this->jsonResponse(['error' => 'Payment not found'], 404);
            }

            // Delete payment record
            $stmt = $this->pdo->prepare("DELETE FROM payments WHERE id = ?");
            $stmt->execute([$id]);
            
            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Payment deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to delete payment'], 500);
        }
    }

    /**
     * Get single payment details for editing
     */
    public function getPayment($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("
                SELECT p.*, 
                       CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                       pat.phone as patient_phone
                FROM payments p
                LEFT JOIN patients pat ON p.patient_id = pat.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$payment) {
                return $this->jsonResponse(['error' => 'Payment not found'], 404);
            }
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => $payment
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to get payment'], 500);
        }
    }

    /**
     * Get single expense details for editing
     */
    public function getExpense($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $stmt = $this->pdo->prepare("
                SELECT e.*, 
                       u.name as created_by_name
                FROM expenses e
                LEFT JOIN users u ON e.created_by = u.id
                WHERE e.id = ?
            ");
            $stmt->execute([$id]);
            $expense = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$expense) {
                return $this->jsonResponse(['error' => 'Expense not found'], 404);
            }
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => $expense
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Failed to get expense'], 500);
        }
    }

    /**
     * Get financial transactions with pagination and filters
     */
    public function getFinancialTransactions()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $date = $_GET['date'] ?? null;
            $type = $_GET['type'] ?? 'all';
            
            $offset = ($page - 1) * $limit;
            
            // Build WHERE conditions
            $whereConditions = [];
            $params = [];
            
            if ($date) {
                $whereConditions[] = "DATE(created_at) = ?";
                $params[] = $date;
            }
            
            // Get transactions from different sources
            $transactions = [];
            
            // Get payments
            if ($type === 'all' || $type === 'payment') {
                $paymentQuery = "
                    SELECT 
                        'payment' as type,
                        p.id,
                        p.amount,
                        p.created_at,
                        CONCAT('دفعة - ', pat.first_name, ' ', pat.last_name) as description,
                        u.name as created_by_name
                    FROM payments p
                    LEFT JOIN patients pat ON p.patient_id = pat.id
                    LEFT JOIN users u ON p.received_by = u.id
                ";
                
                if (!empty($whereConditions)) {
                    $paymentQuery .= " WHERE " . implode(' AND ', $whereConditions);
                }
                
                $paymentQuery .= " ORDER BY p.created_at DESC";
                
                $stmt = $this->pdo->prepare($paymentQuery);
                $stmt->execute($params);
                $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($payments as $payment) {
                    $transactions[] = $payment;
                }
            }
            
            // Get expenses
            if ($type === 'all' || $type === 'expense') {
                $expenseQuery = "
                    SELECT 
                        'expense' as type,
                        e.id,
                        e.amount,
                        e.created_at,
                        CONCAT('مصروف - ', e.expense_name) as description,
                        u.name as created_by_name
                    FROM expenses e
                    LEFT JOIN users u ON e.created_by = u.id
                ";
                
                if (!empty($whereConditions)) {
                    $expenseQuery .= " WHERE " . implode(' AND ', $whereConditions);
                }
                
                $expenseQuery .= " ORDER BY e.created_at DESC";
                
                $stmt = $this->pdo->prepare($expenseQuery);
                $stmt->execute($params);
                $expenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($expenses as $expense) {
                    $transactions[] = $expense;
                }
            }
            
            // Get daily balances
            if ($type === 'all' || $type === 'balance') {
                $balanceQuery = "
                    SELECT 
                        'balance' as type,
                        db.id,
                        db.amount,
                        db.created_at,
                        CONCAT('رصيد - ', db.balance_type) as description,
                        u.name as created_by_name
                    FROM daily_balances db
                    LEFT JOIN users u ON db.created_by = u.id
                ";
                
                if (!empty($whereConditions)) {
                    $balanceQuery .= " WHERE " . implode(' AND ', $whereConditions);
                }
                
                $balanceQuery .= " ORDER BY db.created_at DESC";
                
                $stmt = $this->pdo->prepare($balanceQuery);
                $stmt->execute($params);
                $balances = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($balances as $balance) {
                    $transactions[] = $balance;
                }
            }
            
            // Sort all transactions by date
            usort($transactions, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Calculate running balance
            $runningBalance = 0;
            foreach ($transactions as &$transaction) {
                if ($transaction['type'] === 'expense') {
                    $runningBalance -= $transaction['amount'];
                } else {
                    $runningBalance += $transaction['amount'];
                }
                $transaction['balance'] = $runningBalance;
            }
            
            // Apply pagination
            $total = count($transactions);
            $paginatedTransactions = array_slice($transactions, $offset, $limit);
            
            $pagination = [
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
                'per_page' => $limit,
                'total' => $total,
                'from' => $offset + 1,
                'to' => min($offset + $limit, $total)
            ];
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'transactions' => $paginatedTransactions,
                    'pagination' => $pagination
                ]
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'فشل في تحميل المعاملات المالية'], 500);
        }
    }

    public function exportFinancialTransactions()
    {
        try {
            // Clear any output
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set memory and time limits
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 300);
            
            
            if (!$this->auth->check()) {
                http_response_code(401);
                echo "Unauthorized - Please login first";
                exit;
            }
            
            // Get parameters
            $date = $_GET['date'] ?? '';
            $type = $_GET['type'] ?? 'all';
            
            
            // Get all transactions
            $transactions = $this->getAllFinancialTransactions($date, $type);
            
            
            // Try Excel first, fallback to CSV
            if ($this->tryExcelExport($transactions)) {
                return; // Excel export successful
            }
            
            // Fallback to CSV
            $this->exportAsCSV($transactions);
            
        } catch (Exception $e) {
            
            // Clear any previous output
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Return error as CSV
            $this->exportAsCSV([]);
        }
    }
    
    private function tryExcelExport($transactions)
    {
        try {
            // Check if PhpSpreadsheet is available
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                return false;
            }
            
            
            // Create spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set RTL for Arabic
            $sheet->setRightToLeft(true);
            
            // Headers with numbering
            $headers = ['م', 'التاريخ', 'النوع', 'الوصف', 'المبلغ', 'الرصيد'];
            $column = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($column . '1', $header);
                $column++;
            }
            
            // Data with numbering and translation
            $row = 2;
            if (empty($transactions)) {
                $sheet->setCellValue('A' . $row, '1');
                $sheet->setCellValue('B' . $row, 'لا توجد معاملات للتصدير');
            } else {
                $counter = 1;
                foreach ($transactions as $transaction) {
                    // ترقيم
                    $sheet->setCellValue('A' . $row, $counter);
                    
                    // التاريخ
                    $sheet->setCellValue('B' . $row, $transaction['created_at']);
                    
                    // ترجمة النوع
                    $translatedType = $this->translateTransactionType($transaction['type']);
                    $sheet->setCellValue('C' . $row, $translatedType);
                    
                    // الوصف
                    $sheet->setCellValue('D' . $row, $transaction['description']);
                    
                    // المبلغ
                    $sheet->setCellValue('E' . $row, $transaction['amount']);
                    
                    // الرصيد
                    $sheet->setCellValue('F' . $row, $transaction['balance']);
                    
                    // تطبيق الألوان حسب نوع العملية
                    $this->applyTransactionColors($sheet, $row, $transaction['type']);
                    
                    $row++;
                    $counter++;
                }
            }
            
            // Auto-size columns
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Style headers
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2E86AB'], // أزرق غامق
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
            
            // تطبيق تنسيق الصفوف المتناوبة
            $this->applyAlternatingRowColors($sheet, $row);
            
            // تعيين ارتفاع الصفوف
            $sheet->getRowDimension(1)->setRowHeight(25); // صف العناوين
            for ($i = 2; $i < $row; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(20); // صفوف البيانات
            }
            
            // إضافة معلومات إضافية في عمود منفصل بعد البيانات
            $infoColumn = 'H'; // العمود H بعد آخر عمود بيانات (F)
            $infoRow = 1; // بداية من الصف الأول
            
            $sheet->setCellValue($infoColumn . $infoRow, 'معلومات التصدير:');
            $sheet->setCellValue($infoColumn . ($infoRow + 1), 'تاريخ التصدير:');
            $sheet->setCellValue($infoColumn . ($infoRow + 2), date('Y/m/d H:i:s'));
            $sheet->setCellValue($infoColumn . ($infoRow + 3), 'إجمالي المعاملات:');
            $sheet->setCellValue($infoColumn . ($infoRow + 4), count($transactions));
            $sheet->setCellValue($infoColumn . ($infoRow + 5), 'نوع التصدير:');
            $sheet->setCellValue($infoColumn . ($infoRow + 6), 'Excel مع تنسيق');
            
            // تنسيق معلومات التصدير
            $infoStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => 'FF2E86AB'], // أزرق
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE8F4FD'], // أزرق فاتح
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => ['argb' => 'FF2E86AB'],
                    ],
                ],
            ];
            
            // تطبيق التنسيق على العنوان
            $sheet->getStyle($infoColumn . $infoRow)->applyFromArray($infoStyle);
            
            // تنسيق البيانات
            $dataStyle = [
                'font' => [
                    'size' => 11,
                    'color' => ['argb' => 'FF000000'], // أسود
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFFFF'], // أبيض
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF2E86AB'],
                    ],
                ],
            ];
            
            // تطبيق التنسيق على البيانات
            for ($i = 1; $i <= 6; $i++) {
                $sheet->getStyle($infoColumn . ($infoRow + $i))->applyFromArray($dataStyle);
            }
            
            // تعيين عرض عمود المعلومات
            $sheet->getColumnDimension($infoColumn)->setWidth(20);
            
            // Save file
            $filename = 'المعاملات_المالية_' . date('Y-m-d') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
                exit;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function translateTransactionType($type)
    {
        $translations = [
            'opening_balance' => 'رصيد افتتاحي',
            'additional_balance' => 'رصيد إضافي',
            'payment' => 'دفعة من مريض',
            'expense' => 'مصروف',
            'withdrawal' => 'سحب',
            'booking' => 'حجز',
            'refund' => 'استرداد',
            'balance' => 'رصيد'
        ];
        
        return $translations[$type] ?? $type;
    }
    
    private function applyTransactionColors($sheet, $row, $type)
    {
        $baseStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // أبيض
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        
        switch ($type) {
            case 'opening_balance':
            case 'additional_balance':
            case 'balance':
                // أزرق للرصيد
                $style = array_merge($baseStyle, [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2E86AB'], // أزرق
                    ],
                ]);
                break;
                
            case 'withdrawal':
            case 'expense':
                // أحمر للسحب والمصروفات
                $style = array_merge($baseStyle, [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFDC3545'], // أحمر
                    ],
                ]);
                break;
                
            case 'payment':
            case 'booking':
            case 'refund':
                // أخضر للإيرادات والدفعات
                $style = array_merge($baseStyle, [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF28A745'], // أخضر
                    ],
                ]);
                break;
                
            default:
                // رمادي للأنواع الأخرى
                $style = array_merge($baseStyle, [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF6C757D'], // رمادي
                    ],
                ]);
                break;
        }
        
        // تطبيق التنسيق على عمود النوع فقط
        $sheet->getStyle('C' . $row)->applyFromArray($style);
    }
    
    private function applyAlternatingRowColors($sheet, $lastRow)
    {
        for ($row = 2; $row < $lastRow; $row++) {
            $isEvenRow = ($row % 2 == 0);
            
            $alternatingStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => $isEvenRow ? 'FFE8F4FD' : 'FFFFFFFF'], // أزرق فاتح جداً أو أبيض
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            
            // تطبيق التنسيق على جميع الأعمدة عدا عمود النوع (C) الذي له ألوان خاصة
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($alternatingStyle);
            $sheet->getStyle('D' . $row . ':F' . $row)->applyFromArray($alternatingStyle);
        }
    }
    
    private function exportAsCSV($transactions)
    {
        // Set CSV headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="المعاملات_المالية_' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Add UTF-8 BOM
        echo "\xEF\xBB\xBF";
        
        // CSV content
        echo "التاريخ,النوع,الوصف,المبلغ,الرصيد\n";
        
        if (empty($transactions)) {
            echo '"لا توجد معاملات للتصدير","","","",""' . "\n";
            } else {
            foreach ($transactions as $transaction) {
                echo '"' . $transaction['created_at'] . '",' .
                     '"' . $transaction['type'] . '",' .
                     '"' . $transaction['description'] . '",' .
                     '"' . $transaction['amount'] . '",' .
                     '"' . $transaction['balance'] . '"' . "\n";
            }
        }
        
                        exit;
                    }

    private function getAllFinancialTransactions($date = null, $type = 'all')
    {
        try {
            
            $whereConditions = [];
            $params = [];
            
            // Date filter
            if ($date) {
                $whereConditions[] = "DATE(created_at) = ?";
                $params[] = $date;
            }
            
            // Type filter
            if ($type !== 'all') {
                if ($type === 'payment') {
                    $whereConditions[] = "type = 'payment'";
                } elseif ($type === 'expense') {
                    $whereConditions[] = "type = 'expense'";
                } elseif ($type === 'balance') {
                    $whereConditions[] = "type = 'balance'";
                }
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Prepare parameters for each subquery
            $paymentParams = [];
            $expenseParams = [];
            $balanceParams = [];
            
            if ($date) {
                $paymentParams[] = $date;
                $expenseParams[] = $date;
                $balanceParams[] = $date;
            }
            
            
            // Get all transactions with simplified query
            $allTransactions = [];
            
            // Get payments
            try {
                $paymentQuery = "
                    SELECT 
                        p.created_at,
                        'payment' as type,
                        CONCAT('دفعة من ', COALESCE(CONCAT(pat.first_name, ' ', pat.last_name), 'غير محدد')) as description,
                        p.amount,
                        0 as balance
                    FROM payments p
                    LEFT JOIN patients pat ON p.patient_id = pat.id
                    " . ($date ? "WHERE DATE(p.created_at) = ?" : "");
                
                
                $stmt = $this->pdo->prepare($paymentQuery);
                $stmt->execute($paymentParams);
                $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $allTransactions = array_merge($allTransactions, $payments);
            } catch (Exception $e) {
            }
            
            // Get expenses
            try {
                $expenseQuery = "
                    SELECT 
                        e.created_at,
                        'expense' as type,
                        CONCAT('مصروف: ', e.expense_name) as description,
                        e.amount,
                        0 as balance
                    FROM expenses e
                    " . ($date ? "WHERE DATE(e.created_at) = ?" : "");
                
                
                $stmt = $this->pdo->prepare($expenseQuery);
                $stmt->execute($expenseParams);
                $expenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $allTransactions = array_merge($allTransactions, $expenses);
            } catch (Exception $e) {
            }
            
            // Get daily balances
            try {
                $balanceQuery = "
                    SELECT 
                        db.created_at,
                        'balance' as type,
                        CONCAT('رصيد: ', 
                            CASE 
                                WHEN db.balance_type = 'opening' THEN 'رصيد افتتاحي'
                                WHEN db.balance_type = 'additional' THEN 'إضافة رصيد'
                                WHEN db.balance_type = 'withdrawal' THEN 'سحب من الرصيد'
                                ELSE 'رصيد'
                            END,
                            CASE WHEN db.description IS NOT NULL THEN CONCAT(' - ', db.description) ELSE '' END
                        ) as description,
                        db.amount,
                        0 as balance
                    FROM daily_balances db
                    " . ($date ? "WHERE DATE(db.created_at) = ?" : "");
                
                
                $stmt = $this->pdo->prepare($balanceQuery);
                $stmt->execute($balanceParams);
                $balances = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $allTransactions = array_merge($allTransactions, $balances);
            } catch (Exception $e) {
            }
            
            // Sort by created_at
            usort($allTransactions, function($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
            
            // Filter by type if needed
            if ($type !== 'all') {
                $allTransactions = array_filter($allTransactions, function($transaction) use ($type) {
                    return $transaction['type'] === $type;
                });
            }
            
            $results = $allTransactions;
            
            // Log for debugging
            
            return $results;
            
        } catch (Exception $e) {
        return [];
        }
    }

    private function generateExcelContent($transactions)
    {
        // Create Excel content with proper UTF-8 BOM for Arabic support
        $excel = "\xEF\xBB\xBF"; // UTF-8 BOM for Arabic support
        
        // Add headers with Arabic text
        $excel .= "التاريخ,النوع,الوصف,المبلغ,الرصيد\n";
        
        foreach ($transactions as $transaction) {
            // Format date in Arabic format
            $formattedDate = $this->getArabicDate($transaction['created_at']);
            
            // Format type in Arabic
            $typeText = $this->getTransactionTypeText($transaction['type']);
            
            // Clean description for CSV - handle Arabic text properly
            $description = $this->cleanForCSV($transaction['description']);
            
            // Format amounts with proper Arabic number formatting
            $amount = $this->formatArabicNumber($transaction['amount']);
            $balance = $this->formatArabicNumber($transaction['balance']);
            
            $excel .= sprintf(
                "%s,%s,\"%s\",%s,%s\n",
                $formattedDate,
                $typeText,
                $description,
                $amount,
                $balance
            );
        }
        
        return $excel;
    }

    private function generateFormattedExcelContent($transactions)
    {
        try {
            
            require_once 'vendor/autoload.php';
            
            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('المعاملات المالية');
        
        // Set headers
        $headers = ['التاريخ', 'النوع', 'الوصف', 'المبلغ', 'الرصيد'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        
        // Set row height for headers
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        // Add data rows
        $row = 2;
        if (empty($transactions)) {
            // Add empty row with message
            $sheet->setCellValue('A' . $row, 'لا توجد معاملات للتصدير');
            $sheet->setCellValue('B' . $row, 'لا توجد معاملات للتصدير');
            $sheet->setCellValue('C' . $row, 'لا توجد معاملات للتصدير');
            $sheet->setCellValue('D' . $row, '0.00');
            $sheet->setCellValue('E' . $row, '0.00');
            
            // Style empty row
            $emptyStyle = [
                'font' => [
                    'size' => 14,
                    'color' => ['rgb' => '666666']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ];
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($emptyStyle);
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        } else {
            foreach ($transactions as $transaction) {
            $date = $this->getArabicDate($transaction['created_at']);
            $type = $transaction['type'];
            $description = $transaction['description'];
            $amount = $this->formatArabicNumber($transaction['amount']);
            $balance = $this->formatArabicNumber($transaction['balance']);
            
            // Set cell values
            $sheet->setCellValue('A' . $row, $date);
            $sheet->setCellValue('B' . $row, $type);
            $sheet->setCellValue('C' . $row, $description);
            $sheet->setCellValue('D' . $row, $amount);
            $sheet->setCellValue('E' . $row, $balance);
            
            // Determine row style based on transaction type
            $rowStyle = [
                'font' => [
                    'size' => 14
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            
            if ($type === 'payment') {
                $rowStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8']
                ];
                $rowStyle['font']['color'] = ['rgb' => '2E7D32'];
            } elseif ($type === 'expense') {
                $rowStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFEBEE']
                ];
                $rowStyle['font']['color'] = ['rgb' => 'C62828'];
            } elseif ($type === 'balance') {
                if (strpos($description, 'سحب') !== false) {
                    // Withdrawal - Red
                    $rowStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFEBEE']
                    ];
                    $rowStyle['font']['color'] = ['rgb' => 'C62828'];
                } elseif (strpos($description, 'إضافة') !== false) {
                    // Additional balance - Green
                    $rowStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8F5E8']
                    ];
                    $rowStyle['font']['color'] = ['rgb' => '2E7D32'];
                } elseif (strpos($description, 'افتتاحي') !== false) {
                    // Opening balance - Blue
                    $rowStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E3F2FD']
                    ];
                    $rowStyle['font']['color'] = ['rgb' => '1565C0'];
                }
            }
            
            // Apply row style
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($rowStyle);
            
            // Set row height
            $sheet->getRowDimension($row)->setRowHeight(20);
            
            $row++;
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create writer and save to string
        $writer = new Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_contents();
        ob_end_clean();
        
        return $excelContent;
        
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    private function cleanForCSV($text)
    {
        // Clean text for CSV format while preserving Arabic characters
        $text = str_replace([',', '"', "\n", "\r"], ['،', '""', ' ', ' '], $text);
        return trim($text);
    }
    
    private function formatArabicNumber($number)
    {
        // Format number with Arabic locale
        return number_format($number, 2, '.', ',');
    }
    
    private function getArabicDate($date)
    {
        // Convert date to Arabic format
        $arabicMonths = [
            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
            '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
            '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر'
        ];
        
        $dateObj = new \DateTime($date);
        $month = $dateObj->format('m');
        $day = $dateObj->format('d');
        $year = $dateObj->format('Y');
        $time = $dateObj->format('H:i');
        
        return $day . ' ' . $arabicMonths[$month] . ' ' . $year . ' ' . $time;
    }
    
    private function getTransactionTypeText($type)
    {
        $types = [
            'payment' => 'دفعة',
            'expense' => 'مصروف',
            'balance' => 'رصيد'
        ];
        
        return $types[$type] ?? 'غير محدد';
    }

    /**
     * Get dashboard summary for updating cards
     */
    public function getDashboardSummary()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            
            // Get daily balance
            $dailyBalance = $this->getDailyBalance();
            
            // Get payment types summary
            $paymentTypes = $this->getPaymentTypesSummary();
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'dailyBalance' => $dailyBalance,
                    'paymentTypes' => $paymentTypes
                ]
            ]);
            
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'فشل في تحميل ملخص لوحة التحكم'], 500);
        }
    }

    private function getDailyBalance()
    {
        try {
            $today = date('Y-m-d');
            
            // Get opening balance
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as opening_balance
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'opening'
            ");
            $stmt->execute([$today]);
            $openingBalance = $stmt->fetchColumn();
            
            // Get additional balance (positive amounts)
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as additional_balance
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'additional'
            ");
            $stmt->execute([$today]);
            $additionalBalance = $stmt->fetchColumn();
            
            // Get total withdrawals (negative amounts)
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_withdrawals
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'withdrawal'
            ");
            $stmt->execute([$today]);
            $totalWithdrawals = $stmt->fetchColumn();
            
            // Get total received today
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_received
                FROM payments 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$today]);
            $totalReceived = $stmt->fetchColumn();
            
            // Get total expenses today
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_expenses
                FROM expenses 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$today]);
            $totalExpenses = $stmt->fetchColumn();
            
            // Calculate current balance: opening + additional + payments - withdrawals - expenses
            $currentBalance = $openingBalance + $additionalBalance + $totalReceived - $totalWithdrawals - $totalExpenses;
            
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
            $transactionsCount = $stmt->fetchColumn();
            
            return [
                'opening_balance' => $openingBalance,
                'total_received' => $totalReceived,
                'total_expenses' => $totalExpenses,
                'current_balance' => $currentBalance,
                'transactions_count' => $transactionsCount
            ];
            
        } catch (Exception $e) {
            return [
                'opening_balance' => 0,
                'total_received' => 0,
                'total_expenses' => 0,
                'current_balance' => 0,
                'transactions_count' => 0
            ];
        }
    }

    public function getRecentActivity()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            // Get pagination parameters
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 10;
            $offset = ($page - 1) * $perPage;
            
            // Get search parameter
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            // Build WHERE clause with search
            $whereClause = "WHERE 1=1";
            $params = [];
            $countParams = [];
            
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $whereClause .= " AND (
                    te.event_summary LIKE ? OR
                    p.first_name LIKE ? OR
                    p.last_name LIKE ? OR
                    CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR
                    p.phone LIKE ?
                )";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            // Get total count
            $countStmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM timeline_events te
                JOIN patients p ON te.patient_id = p.id
                JOIN appointments a ON te.appointment_id = a.id
                $whereClause
            ");
            $countStmt->execute($countParams);
            $total = $countStmt->fetchColumn();

            // Get paginated events
            $stmt = $this->pdo->prepare("
                SELECT te.*, p.first_name, p.last_name, p.phone, p.id as patient_id, a.id as appointment_id
                FROM timeline_events te
                JOIN patients p ON te.patient_id = p.id
                JOIN appointments a ON te.appointment_id = a.id
                $whereClause
                ORDER BY te.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $params[] = $perPage;
            $params[] = $offset;
            $stmt->execute($params);
            $events = $stmt->fetchAll();

            $totalPages = ceil($total / $perPage);

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'items' => $events,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => $totalPages,
                        'has_previous' => $page > 1,
                        'has_next' => $page < $totalPages
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['ok' => false, 'error' => 'Failed to load recent activity'], 500);
        }
    }

    public function getDashboardCharts()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            // Get last 30 days data for trend chart (excluding today)
            $today = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('-1 day')); // Yesterday
            $startDate = date('Y-m-d', strtotime('-30 days'));

            // Get trend data (daily appointments) - Last 30 days only (excluding today)
            $trendStmt = $this->pdo->prepare("
                SELECT 
                    DATE(a.date) as date,
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN a.status != 'Completed' THEN 1 ELSE 0 END) as missed
                FROM appointments a
                WHERE DATE(a.date) BETWEEN ? AND ?
                AND DATE(a.date) < CURDATE()
                GROUP BY DATE(a.date)
                ORDER BY date ASC
            ");
            $trendStmt->execute([$startDate, $endDate]);
            $trendData = $trendStmt->fetchAll();

            // Get status summary (total counts) - Last 30 days excluding today
            $statusStmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN a.status != 'Completed' AND a.status != 'Cancelled' THEN 1 ELSE 0 END) as missed
                FROM appointments a
                WHERE DATE(a.date) BETWEEN ? AND ?
                AND DATE(a.date) < CURDATE()
            ");
            $statusStmt->execute([$startDate, $endDate]);
            $statusData = $statusStmt->fetch();
            
            // Calculate completion ratio
            $total = (int)($statusData['total_appointments'] ?? 0);
            $completed = (int)($statusData['completed'] ?? 0);
            $missed = (int)($statusData['missed'] ?? 0);
            $completionRatio = $total > 0 ? round(($completed / $total) * 100, 2) : 0;
            $missedRatio = $total > 0 ? round(($missed / $total) * 100, 2) : 0;

            // Get new patients data for last 30 days with male/female distribution
            $patientsStmt = $this->pdo->prepare("
                SELECT 
                    DATE(p.created_at) as date,
                    COUNT(DISTINCT p.id) as new_patients,
                    SUM(CASE WHEN p.gender = 'Male' THEN 1 ELSE 0 END) as male,
                    SUM(CASE WHEN p.gender = 'Female' THEN 1 ELSE 0 END) as female
                FROM patients p
                WHERE DATE(p.created_at) BETWEEN ? AND ?
                AND DATE(p.created_at) < CURDATE()
                GROUP BY DATE(p.created_at)
                ORDER BY date ASC
            ");
            $patientsStmt->execute([$startDate, $endDate]);
            $patientsData = $patientsStmt->fetchAll();

            // Get total prescriptions (medical + glasses) for last 30 days
            // MySQL doesn't support FULL OUTER JOIN, so use UNION ALL approach
            $prescriptionsStmt = $this->pdo->prepare("
                SELECT 
                    date,
                    SUM(count) as total_prescriptions
                FROM (
                    SELECT 
                        DATE(a.date) as date,
                        COUNT(DISTINCT p.id) as count
                    FROM appointments a
                    INNER JOIN prescriptions p ON a.id = p.appointment_id
                    WHERE DATE(a.date) BETWEEN ? AND ?
                    AND DATE(a.date) < CURDATE()
                    GROUP BY DATE(a.date)
                    UNION ALL
                    SELECT 
                        DATE(a.date) as date,
                        COUNT(DISTINCT gp.id) as count
                    FROM appointments a
                    INNER JOIN glasses_prescriptions gp ON a.id = gp.appointment_id
                    WHERE DATE(a.date) BETWEEN ? AND ?
                    AND DATE(a.date) < CURDATE()
                    GROUP BY DATE(a.date)
                ) combined
                GROUP BY date
                ORDER BY date ASC
            ");
            $prescriptionsStmt->execute([$startDate, $endDate, $startDate, $endDate]);
            $prescriptionsData = $prescriptionsStmt->fetchAll();

            // Get gender statistics (all time)
            $genderStmt = $this->pdo->prepare("
                SELECT 
                    SUM(CASE WHEN p.gender = 'Male' THEN 1 ELSE 0 END) as total_male,
                    SUM(CASE WHEN p.gender = 'Female' THEN 1 ELSE 0 END) as total_female
                FROM patients p
            ");
            $genderStmt->execute([]);
            $genderData = $genderStmt->fetch();

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'trend' => $trendData,
                    'status' => [
                        'total_appointments' => $total,
                        'completed' => $completed,
                        'missed' => $missed,
                        'completion_ratio' => $completionRatio,
                        'missed_ratio' => $missedRatio
                    ],
                    'patients' => $patientsData,
                    'prescriptions' => $prescriptionsData,
                    'gender' => [
                        'total_male' => (int)($genderData['total_male'] ?? 0),
                        'total_female' => (int)($genderData['total_female'] ?? 0)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['ok' => false, 'error' => 'Failed to load charts data'], 500);
        }
    }

    public function getUpcomingAppointments()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            
            if (!$doctorId) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Doctor not found'], 404);
            }

            // Get pagination parameters
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 10;
            $offset = ($page - 1) * $perPage;

            $today = date('Y-m-d');

            // Get total count - upcoming appointments (today and future dates)
            $countStmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.date >= ?
                AND a.status IN ('Booked', 'CheckedIn')
            ");
            $countStmt->execute([$today]);
            $total = $countStmt->fetchColumn();

            // Get paginated appointments
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.first_name, p.last_name, p.phone
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.date >= ?
                AND a.status IN ('Booked', 'CheckedIn')
                ORDER BY a.date ASC, a.start_time ASC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$today, $perPage, $offset]);
            $appointments = $stmt->fetchAll();

            $totalPages = ceil($total / $perPage);

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'items' => $appointments,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => $totalPages
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['ok' => false, 'error' => 'Failed to load upcoming appointments'], 500);
        }
    }

    public function getMissedAppointments()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            
            if (!$doctorId) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Doctor not found'], 404);
            }

            // Get pagination parameters
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 10;
            $offset = ($page - 1) * $perPage;

            $today = date('Y-m-d');

            // Get total count - appointments from previous days that are not Completed
            $countStmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.date < ?
                AND a.status != 'Completed'
                AND a.status != 'Cancelled'
            ");
            $countStmt->execute([$today]);
            $total = $countStmt->fetchColumn();

            // Get paginated appointments
            $stmt = $this->pdo->prepare("
                SELECT a.*, p.first_name, p.last_name, p.phone
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.date < ?
                AND a.status != 'Completed'
                AND a.status != 'Cancelled'
                ORDER BY a.date DESC, a.start_time DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$today, $perPage, $offset]);
            $appointments = $stmt->fetchAll();

            $totalPages = ceil($total / $perPage);

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'items' => $appointments,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => $totalPages
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->jsonResponse(['ok' => false, 'error' => 'Failed to load missed appointments'], 500);
        }
    }

    private function getDoctorId($userId)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }
    
    /**
     * Check if notifications should be skipped for appointments based on user settings
     * @param int $userId
     * @return bool
     */
    private function shouldSkipNotification($userId)
    {
        try {
            $settingsStmt = $this->pdo->prepare("
                SELECT setting_value 
                FROM doctor_settings 
                WHERE user_id = ? AND setting_key = 'dont_create_notification_for_appointments'
            ");
            $settingsStmt->execute([$userId]);
            $setting = $settingsStmt->fetch(\PDO::FETCH_ASSOC);
            
            return $setting && $setting['setting_value'] == '1';
        } catch (\Exception $e) {
            // If there's an error, default to creating notifications
            return false;
        }
    }

    public function getOrganizerMonth()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            $year = (int)($_GET['year'] ?? date('Y'));
            $month = (int)($_GET['month'] ?? date('m'));
            
            // Validate month and year
            if ($month < 1 || $month > 12) {
                return $this->jsonResponse(['error' => 'Invalid month'], 400);
            }
            
            if ($year < 2020 || $year > 2100) {
                return $this->jsonResponse(['error' => 'Invalid year'], 400);
            }

            // Get first and last day of the month
            $firstDay = sprintf('%04d-%02d-01', $year, $month);
            $lastDay = date('Y-m-t', strtotime($firstDay));
            
            // Get appointments for the month
            $appointmentsStmt = $this->pdo->prepare("
                SELECT 
                    a.id,
                    a.date,
                    a.start_time,
                    a.end_time,
                    a.visit_type,
                    a.status,
                    a.notes as appointment_notes,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    p.id as patient_id
                FROM appointments a
                LEFT JOIN patients p ON a.patient_id = p.id
                WHERE a.date >= ? AND a.date <= ?
                AND a.status NOT IN ('Cancelled', 'NoShow')
                ORDER BY a.date, a.start_time
            ");
            $appointmentsStmt->execute([$firstDay, $lastDay]);
            $appointments = $appointmentsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get notes for the month (from notes table)
            $notesStmt = $this->pdo->prepare("
                SELECT 
                    id,
                    content,
                    created_at,
                    DATE(created_at) as note_date
                FROM notes
                WHERE user_id = ? AND DATE(created_at) >= ? AND DATE(created_at) <= ?
                ORDER BY created_at DESC
            ");
            $notesStmt->execute([$user['id'], $firstDay, $lastDay]);
            $notes = $notesStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get alerts for the month
            $alertsStmt = $this->pdo->prepare("
                SELECT 
                    id,
                    alert_date,
                    alert_time,
                    message,
                    patient_id,
                    appointment_id,
                    is_dismissed
                FROM alerts
                WHERE alert_date >= ? AND alert_date <= ?
                ORDER BY alert_date, alert_time
            ");
            $alertsStmt->execute([$firstDay, $lastDay]);
            $alerts = $alertsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Organize data by date
            $dataByDate = [];
            
            // Process appointments
            foreach ($appointments as $appointment) {
                $date = $appointment['date'];
                if (!isset($dataByDate[$date])) {
                    $dataByDate[$date] = [
                        'appointments' => [],
                        'notes' => [],
                        'alerts' => []
                    ];
                }
                $dataByDate[$date]['appointments'][] = $appointment;
            }
            
            // Process notes
            foreach ($notes as $note) {
                $date = $note['note_date'];
                if (!isset($dataByDate[$date])) {
                    $dataByDate[$date] = [
                        'appointments' => [],
                        'notes' => [],
                        'alerts' => []
                    ];
                }
                $dataByDate[$date]['notes'][] = $note;
            }
            
            // Process alerts
            foreach ($alerts as $alert) {
                $date = $alert['alert_date'];
                if (!isset($dataByDate[$date])) {
                    $dataByDate[$date] = [
                        'appointments' => [],
                        'notes' => [],
                        'alerts' => []
                    ];
                }
                $dataByDate[$date]['alerts'][] = $alert;
            }
            
            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'year' => $year,
                    'month' => $month,
                    'firstDay' => $firstDay,
                    'lastDay' => $lastDay,
                    'dataByDate' => $dataByDate
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function getPaymentTypesSummary()
    {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    type,
                    COUNT(*) as count,
                    SUM(amount) as total
                FROM payments 
                WHERE DATE(created_at) = ?
                GROUP BY type
            ");
            $stmt->execute([$today]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $summary = [];
            foreach ($results as $result) {
                $summary[$result['type']] = [
                    'count' => $result['count'],
                    'total' => $result['total']
                ];
            }
            
            return $summary;
            
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * POST /api/drugs/update-database
     * Update drugs database from drugeye.pharorg.com
     */
    public function updateDrugsDatabase()
    {
        ob_start();
        try {
            header('Content-Type: application/json; charset=utf-8');
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($this->auth)) {
                $this->auth = new \App\Lib\Auth();
            }
            
            if (!$this->auth->check()) {
                ob_clean();
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $excelUrl = 'http://www.drugeye.pharorg.com/drugeyeapp/inner-update-files/drugs.xlsx';
            $tempFile = sys_get_temp_dir() . '/drugs_' . time() . '_' . uniqid() . '.db';
            
            // Download file (SQLite database)
            $ch = curl_init($excelUrl);
            $fp = fopen($tempFile, 'wb');
            
            if (!$fp) {
                ob_clean();
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to create temporary file',
                    'message' => 'Cannot write to temp directory: ' . sys_get_temp_dir()
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $curlResult = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            fclose($fp);
            
            // Check if download was successful
            if ($curlResult === false || !empty($curlError)) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            ob_clean();
                http_response_code(500);
            echo json_encode([
                    'success' => false,
                    'error' => 'Failed to download file',
                    'message' => 'cURL Error: ' . $curlError
            ], JSON_UNESCAPED_UNICODE);
            exit;
            }
            
            if ($httpCode !== 200) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                    'error' => 'Failed to download file',
                    'message' => 'HTTP Code: ' . $httpCode
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
            
            // Verify file exists and has content
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                ob_clean();
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Downloaded file is empty or missing',
                    'message' => 'File size: ' . (file_exists($tempFile) ? filesize($tempFile) : 0)
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Try to read as SQLite database using sqlite3 command line tool
            $rows = [];
            
            // Check if sqlite3 command line tool is available
            $sqlite3Cmd = '/usr/bin/sqlite3'; // Use direct path since which is disabled
            if (!file_exists($sqlite3Cmd) || !is_executable($sqlite3Cmd)) {
                // Try to find sqlite3 in common locations
                $possiblePaths = ['/usr/bin/sqlite3', '/bin/sqlite3', '/usr/local/bin/sqlite3'];
                $sqlite3Cmd = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && is_executable($path)) {
                        $sqlite3Cmd = $path;
                        break;
                    }
                }
                
                if (empty($sqlite3Cmd)) {
                    if (file_exists($tempFile)) {
                        unlink($tempFile);
                    }
                ob_clean();
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => 'SQLite3 command line tool is not available',
                        'message' => 'Please install sqlite3: sudo apt-get install sqlite3'
                    ], JSON_UNESCAPED_UNICODE);
                exit;
                }
            }
            
            try {
                // Get table name using sqlite3 command with exec
                $tableQuery = escapeshellarg("SELECT name FROM sqlite_master WHERE type='table' LIMIT 1;");
                $tableOutput = [];
                $returnVar = 0;
                exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " {$tableQuery} 2>&1", $tableOutput, $returnVar);
                $tableName = trim(implode("\n", $tableOutput));
                
                if (empty($tableName) || $returnVar !== 0) {
                    throw new \Exception('No tables found in SQLite database. Error: ' . implode("\n", $tableOutput));
                }
                
                // Get column names using sqlite3 command with exec
                // PRAGMA table_info returns: cid, name, type, notnull, dflt_value, pk
                // We need to extract only the 'name' column (second column in output)
                $columnQuery = escapeshellarg("PRAGMA table_info(`{$tableName}`);");
                $columnOutput = [];
                exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " {$columnQuery} 2>&1", $columnOutput, $returnVar);
                
                $columnNames = [];
                if ($returnVar === 0 && !empty($columnOutput)) {
                    // PRAGMA table_info returns: cid|name|type|notnull|dflt_value|pk
                    // We need the second column (name)
                    foreach ($columnOutput as $line) {
                        $parts = explode('|', $line);
                        if (isset($parts[1])) {
                            $columnNames[] = trim($parts[1]);
                        }
                    }
                }
                
                // Fallback: try to get all columns using SELECT * LIMIT 1 with header
                if (empty($columnNames)) {
                    $testQuery = escapeshellarg("SELECT * FROM `{$tableName}` LIMIT 1;");
                    $testOutput = [];
                    exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " -header -csv {$testQuery} 2>&1", $testOutput, $testReturnVar);
                    
                    if ($testReturnVar === 0 && !empty($testOutput)) {
                        $testLine = $testOutput[0];
                        $columnNames = str_getcsv($testLine);
                    } else {
                        throw new \Exception('Failed to read column information from SQLite database. Error: ' . implode("\n", $columnOutput));
                    }
                }
                
                // Map SQLite columns to database columns
                $columnMap = [];
                foreach ($columnNames as $index => $colName) {
                    $colNameUpper = strtoupper(trim($colName));
                    if ($colNameUpper === 'ID' || $colNameUpper === 'CID') {
                        $columnMap['ID'] = $colName;
                    } elseif ($colNameUpper === 'FIRSTNAME' || $colNameUpper === 'NAME') {
                        $columnMap['FirstName'] = $colName;
                    } elseif ($colNameUpper === 'LASTNAME') {
                        $columnMap['LastName'] = $colName;
                    } elseif ($colNameUpper === 'PRICE') {
                        $columnMap['price'] = $colName;
                    } elseif ($colNameUpper === 'PRICEOLD') {
                        $columnMap['priceold'] = $colName;
                    } elseif ($colNameUpper === 'IMAGEID') {
                        $columnMap['imageid'] = $colName;
                    } elseif ($colNameUpper === 'COMPANY') {
                        $columnMap['Company'] = $colName;
                    } elseif ($colNameUpper === 'PHARMACOLOGY') {
                        $columnMap['Pharmacology'] = $colName;
                    } elseif ($colNameUpper === 'SRDE') {
                        $columnMap['SRDE'] = $colName;
                    } elseif ($colNameUpper === 'GI') {
                        $columnMap['GI'] = $colName;
                    } elseif ($colNameUpper === 'ROUTE') {
                        $columnMap['Route'] = $colName;
                    }
                }
                
                // Build SELECT query with column mapping
                $selectColumns = [];
                $selectColumns[] = isset($columnMap['ID']) ? $columnMap['ID'] . ' as ID' : 'NULL as ID';
                $selectColumns[] = isset($columnMap['FirstName']) ? $columnMap['FirstName'] . ' as FirstName' : 'NULL as FirstName';
                $selectColumns[] = isset($columnMap['LastName']) ? $columnMap['LastName'] . ' as LastName' : 'NULL as LastName';
                $selectColumns[] = isset($columnMap['price']) ? $columnMap['price'] . ' as price' : 'NULL as price';
                $selectColumns[] = isset($columnMap['priceold']) ? $columnMap['priceold'] . ' as priceold' : 'NULL as priceold';
                $selectColumns[] = isset($columnMap['imageid']) ? $columnMap['imageid'] . ' as imageid' : 'NULL as imageid';
                $selectColumns[] = isset($columnMap['Company']) ? $columnMap['Company'] . ' as Company' : 'NULL as Company';
                $selectColumns[] = isset($columnMap['Pharmacology']) ? $columnMap['Pharmacology'] . ' as Pharmacology' : 'NULL as Pharmacology';
                $selectColumns[] = isset($columnMap['SRDE']) ? $columnMap['SRDE'] . ' as SRDE' : 'NULL as SRDE';
                $selectColumns[] = isset($columnMap['GI']) ? $columnMap['GI'] . ' as GI' : 'NULL as GI';
                $selectColumns[] = isset($columnMap['Route']) ? $columnMap['Route'] . ' as Route' : 'NULL as Route';
                
                $selectQuery = "SELECT " . implode(', ', $selectColumns) . " FROM `{$tableName}`;";
                
                // Export data to CSV using sqlite3 command with exec
                $csvFile = sys_get_temp_dir() . '/drugs_export_' . time() . '_' . uniqid() . '.csv';
                $exportQuery = escapeshellarg($selectQuery);
                $exportOutput = [];
                $returnVar = 0;
                
                // Use exec to get output directly, then write to file
                exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " -header -csv {$exportQuery} 2>&1", $exportOutput, $returnVar);
                
                if ($returnVar !== 0 || empty($exportOutput)) {
                    $errorMsg = !empty($exportOutput) ? implode("\n", $exportOutput) : 'Unknown error';
                    throw new \Exception('Failed to export data from SQLite database. Return code: ' . $returnVar . '. Error: ' . $errorMsg);
                }
                
                // Write output to CSV file
                $csvContent = implode("\n", $exportOutput);
                if (file_put_contents($csvFile, $csvContent) === false) {
                    throw new \Exception('Failed to write CSV file');
                }
                
                if (!file_exists($csvFile) || filesize($csvFile) === 0) {
                    throw new \Exception('CSV file is empty or missing after export');
                }
                
                // Read CSV file
                $csvHandle = fopen($csvFile, 'r');
                if (!$csvHandle) {
                    throw new \Exception('Failed to open exported CSV file');
                }
                
                // Read header row
                $header = fgetcsv($csvHandle);
                if ($header === false) {
                    fclose($csvHandle);
                    unlink($csvFile);
                    throw new \Exception('Failed to read CSV header');
                }
                
                // Map header columns to indices
                $headerMap = [];
                foreach ($header as $index => $colName) {
                    $headerMap[trim($colName)] = $index;
                }
                
                // Read data rows
                $mappedRows = [];
                while (($csvRow = fgetcsv($csvHandle)) !== false) {
                    $mappedRow = [
                        isset($headerMap['ID']) && isset($csvRow[$headerMap['ID']]) ? trim($csvRow[$headerMap['ID']]) : null,
                        isset($headerMap['FirstName']) && isset($csvRow[$headerMap['FirstName']]) ? trim($csvRow[$headerMap['FirstName']]) : null,
                        isset($headerMap['LastName']) && isset($csvRow[$headerMap['LastName']]) ? trim($csvRow[$headerMap['LastName']]) : null,
                        isset($headerMap['price']) && isset($csvRow[$headerMap['price']]) ? trim($csvRow[$headerMap['price']]) : null,
                        isset($headerMap['priceold']) && isset($csvRow[$headerMap['priceold']]) ? trim($csvRow[$headerMap['priceold']]) : null,
                        isset($headerMap['imageid']) && isset($csvRow[$headerMap['imageid']]) ? trim($csvRow[$headerMap['imageid']]) : null,
                        isset($headerMap['Company']) && isset($csvRow[$headerMap['Company']]) ? trim($csvRow[$headerMap['Company']]) : null,
                        isset($headerMap['Pharmacology']) && isset($csvRow[$headerMap['Pharmacology']]) ? trim($csvRow[$headerMap['Pharmacology']]) : null,
                        isset($headerMap['SRDE']) && isset($csvRow[$headerMap['SRDE']]) ? trim($csvRow[$headerMap['SRDE']]) : null,
                        isset($headerMap['GI']) && isset($csvRow[$headerMap['GI']]) ? trim($csvRow[$headerMap['GI']]) : null,
                        isset($headerMap['Route']) && isset($csvRow[$headerMap['Route']]) ? trim($csvRow[$headerMap['Route']]) : null
                    ];
                    $mappedRows[] = $mappedRow;
                }
                
                fclose($csvHandle);
                
                // Clean up CSV file
                if (file_exists($csvFile)) {
                    unlink($csvFile);
                }
                
                $rows = $mappedRows;
                
            } catch (\Exception $e) {
                $fileSize = file_exists($tempFile) ? filesize($tempFile) : 0;
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                if (isset($csvFile) && file_exists($csvFile)) {
                    unlink($csvFile);
                }
                ob_clean();
                
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to read SQLite database',
                    'message' => $e->getMessage() . ' (File size: ' . $fileSize . ' bytes)'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Remove header row if exists
            if (!empty($rows) && isset($rows[0])) {
                $firstRow = $rows[0];
                if (is_array($firstRow) && (
                    (isset($firstRow[0]) && strtoupper(trim((string)$firstRow[0])) === 'ID') ||
                    (isset($firstRow[1]) && strtoupper(trim((string)$firstRow[1])) === 'FIRSTNAME')
                )) {
                    array_shift($rows);
                }
            }
            
            // Connect to drug database using existing method
            $pdo = $this->getDrugsDatabaseConnection();
            
            // Start transaction
            $pdo->beginTransaction();
            
            $inserted = 0;
            $updated = 0;
            $total = count($rows);
            
            try {
                // Clear existing data
                $pdo->exec("DELETE FROM drugs");
                
                // Prepare insert statement
                $stmt = $pdo->prepare("
                    INSERT INTO drugs 
                    (ID, FirstName, LastName, price, priceold, imageid, Company, Pharmacology, SRDE, GI, Route)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // Process each row
                foreach ($rows as $rowIndex => $row) {
                    // Skip empty rows
                    if (empty($row) || (!isset($row[0]) && !isset($row[1]) && !isset($row[2]))) {
                        continue;
                    }
                    
                    // Map CSV columns to database columns
                    $id = isset($row[0]) ? trim((string)$row[0]) : null;
                    $firstName = isset($row[1]) ? trim((string)$row[1]) : '';
                    $lastName = isset($row[2]) ? trim((string)$row[2]) : '';
                    $price = isset($row[3]) ? trim((string)$row[3]) : '';
                    $priceold = isset($row[4]) ? trim((string)$row[4]) : '';
                    $imageid = isset($row[5]) ? trim((string)$row[5]) : '';
                    $company = isset($row[6]) ? trim((string)$row[6]) : '';
                    $pharmacology = isset($row[7]) ? trim((string)$row[7]) : '';
                    $srde = isset($row[8]) ? trim((string)$row[8]) : '';
                    $gi = isset($row[9]) ? trim((string)$row[9]) : '';
                    $route = isset($row[10]) ? trim((string)$row[10]) : '';
                    
                    // Skip if no ID or drug name
                    if (empty($id) && empty($firstName) && empty($lastName)) {
                        continue;
                    }
                    
                    // Convert ID to integer if possible, otherwise skip
                    if (!is_numeric($id) || empty($id)) {
                        continue;
                    }
                    $id = (int)$id;
                    
                    // Limit string lengths to match database schema
                    $firstName = mb_substr($firstName, 0, 86);
                    $lastName = mb_substr($lastName, 0, 100);
                    $price = mb_substr($price, 0, 100);
                    $priceold = mb_substr($priceold, 0, 100);
                    $imageid = mb_substr($imageid, 0, 30);
                    $company = mb_substr($company, 0, 54);
                    $pharmacology = mb_substr($pharmacology, 0, 96);
                    $srde = mb_substr($srde, 0, 60);
                    $gi = mb_substr($gi, 0, 1000);
                    $route = mb_substr($route, 0, 100);
                    
                    try {
                        $stmt->execute([
                            $id,
                            $firstName ?: null,
                            $lastName ?: null,
                            $price ?: null,
                            $priceold ?: null,
                            $imageid ?: null,
                            $company ?: null,
                            $pharmacology ?: null,
                            $srde ?: null,
                            $gi ?: null,
                            $route ?: null
                        ]);
                        $inserted++;
                    } catch (\PDOException $e) {
                        // Continue with next row
                    }
                }
                
                // Commit transaction
                $pdo->commit();
            } catch (\Exception $e) {
                // Rollback transaction if started
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
            
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Database updated successfully',
                'statistics' => [
                    'total' => $total,
                    'inserted' => $inserted,
                    'updated' => $updated
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (\Exception $e) {
            // Rollback transaction if started
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Clean up temp file
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to update database',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * Get weather data for the dashboard
     * Uses OpenWeatherMap API with caching
     */
    public function getWeather()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $lat = $_GET['lat'] ?? null;
            $lon = $_GET['lon'] ?? null;

            // Cache file path
            $cacheDir = __DIR__ . '/../../storage/cache';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            $cacheKey = $lat && $lon ? md5("weather_{$lat}_{$lon}") : 'weather_default';
            $cacheFile = "{$cacheDir}/{$cacheKey}.json";
            $cacheExpiry = 15 * 60; // 15 minutes

            // Check cache
            if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
                $cachedData = json_decode(file_get_contents($cacheFile), true);
                if ($cachedData) {
                    return $this->jsonResponse([
                        'success' => true,
                        'weather' => $cachedData,
                        'cached' => true
                    ]);
                }
            }

            // Default to Kafr El Sheikh, Egypt if no coordinates provided
            if (!$lat || !$lon) {
                $lat = 31.1117; // Kafr El Sheikh latitude
                $lon = 30.9397; // Kafr El Sheikh longitude
            }

            // OpenWeatherMap API key - use environment variable or default
            $apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? '4d8fb5b93d4af21d66a2948710284366';

            // Use OpenWeatherMap ONLY (for testing, no fallbacks)
            $weatherData = $this->fetchWeatherFromOpenWeatherMap($lat, $lon, $apiKey);
            
            // If OpenWeatherMap fails, return error (no fallback)
            if (!$weatherData) {
                error_log("OpenWeatherMap API failed for coordinates: {$lat}, {$lon}");
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to fetch weather data from OpenWeatherMap API'
                ], 500);
            }

            // Save to cache
            file_put_contents($cacheFile, json_encode($weatherData));

            return $this->jsonResponse([
                'success' => true,
                'weather' => $weatherData
            ]);

        } catch (\Exception $e) {
            error_log("Weather API error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Weather API exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch weather from Open-Meteo API (free, no API key)
     */
    private function fetchWeatherFromOpenMeteo($lat, $lon)
    {
        try {
            // Open-Meteo API endpoint - no API key required
            $weatherUrl = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,uv_index,is_day&timezone=auto";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $weatherUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; WeatherApp)'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode !== 200 || !$response || $curlError) {
                error_log("Open-Meteo API error: HTTP {$httpCode}, cURL: {$curlError}");
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['current'])) {
                error_log("Open-Meteo API: Invalid response structure");
                return null;
            }
            
            $current = $data['current'];
            
            // Map weather code to condition description
            $weatherCode = $current['weather_code'] ?? 0;
            $condition = $this->mapWeatherCodeToCondition($weatherCode);
            
            // Get location name using reverse geocoding
            $locationName = $this->getLocationNameFromCoordinates($lat, $lon);
            
            // Open-Meteo returns wind_speed_10m in km/h already (check units in response)
            // But based on API docs, it's in km/h, so no conversion needed
            $windSpeed = round($current['wind_speed_10m'] ?? 0);
            
            $weatherData = [
                'temperature' => round($current['temperature_2m'] ?? 20),
                'humidity' => round($current['relative_humidity_2m'] ?? 50),
                'condition' => $condition,
                'icon' => $this->getWeatherIconFromCode($weatherCode, $current['is_day'] ?? 1),
                'windSpeed' => $windSpeed, // Already in km/h from Open-Meteo
                'location' => $locationName,
                'country' => '',
                'uvIndex' => round($current['uv_index'] ?? 5),
                'feelsLike' => round($current['temperature_2m'] ?? 20),
                'pressure' => 1013, // Open-Meteo doesn't provide pressure in free tier
                'visibility' => 10,
                'clouds' => $this->estimateCloudsFromWeatherCode($weatherCode),
                'timestamp' => time()
            ];
            
            error_log("Open-Meteo API: Successfully fetched weather data for {$lat}, {$lon}");
            return $weatherData;
            
        } catch (\Exception $e) {
            error_log("Open-Meteo API exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Fetch weather from OpenWeatherMap API (fallback)
     */
    private function fetchWeatherFromOpenWeatherMap($lat, $lon, $apiKey)
    {
        try {
            $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $weatherUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || !$response) {
                error_log("OpenWeatherMap API error: HTTP {$httpCode}");
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['main'])) {
                error_log("OpenWeatherMap API: Invalid response structure");
                return null;
            }
            
            $weatherData = [
                'temperature' => round($data['main']['temp'] ?? 20),
                'humidity' => $data['main']['humidity'] ?? 50,
                'condition' => ucfirst($data['weather'][0]['description'] ?? 'Clear'),
                'icon' => $data['weather'][0]['icon'] ?? '01d',
                'windSpeed' => round(($data['wind']['speed'] ?? 0) * 3.6), // Convert m/s to km/h
                'location' => $data['name'] ?? 'Unknown',
                'country' => $data['sys']['country'] ?? '',
                'uvIndex' => $this->estimateUVIndex($data),
                'feelsLike' => round($data['main']['feels_like'] ?? $data['main']['temp']),
                'pressure' => $data['main']['pressure'] ?? 1013,
                'visibility' => round(($data['visibility'] ?? 10000) / 1000), // Convert to km
                'clouds' => $data['clouds']['all'] ?? 0,
                'timestamp' => time()
            ];
            
            error_log("OpenWeatherMap API: Successfully fetched weather data");
            return $weatherData;
            
        } catch (\Exception $e) {
            error_log("OpenWeatherMap API exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Map WMO weather code to condition description
     */
    private function mapWeatherCodeToCondition($code)
    {
        // WMO Weather interpretation codes (WW)
        $codes = [
            0 => 'Clear',
            1 => 'Mainly Clear',
            2 => 'Partly Cloudy',
            3 => 'Overcast',
            45 => 'Foggy',
            48 => 'Depositing Rime Fog',
            51 => 'Light Drizzle',
            53 => 'Moderate Drizzle',
            55 => 'Dense Drizzle',
            56 => 'Light Freezing Drizzle',
            57 => 'Dense Freezing Drizzle',
            61 => 'Slight Rain',
            63 => 'Moderate Rain',
            65 => 'Heavy Rain',
            66 => 'Light Freezing Rain',
            67 => 'Heavy Freezing Rain',
            71 => 'Slight Snow',
            73 => 'Moderate Snow',
            75 => 'Heavy Snow',
            77 => 'Snow Grains',
            80 => 'Slight Rain Showers',
            81 => 'Moderate Rain Showers',
            82 => 'Violent Rain Showers',
            85 => 'Slight Snow Showers',
            86 => 'Heavy Snow Showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with Hail',
            99 => 'Thunderstorm with Heavy Hail'
        ];
        
        return $codes[$code] ?? 'Clear';
    }
    
    /**
     * Get weather icon from WMO code
     */
    private function getWeatherIconFromCode($code, $isDay = 1)
    {
        // Map to OpenWeatherMap icon format for compatibility
        if ($code == 0) return $isDay ? '01d' : '01n';
        if ($code <= 2) return $isDay ? '02d' : '02n';
        if ($code == 3) return '04d';
        if ($code >= 45 && $code <= 48) return '50d';
        if ($code >= 51 && $code <= 67) return '09d';
        if ($code >= 71 && $code <= 77) return '13d';
        if ($code >= 80 && $code <= 82) return '09d';
        if ($code >= 85 && $code <= 86) return '13d';
        if ($code >= 95) return '11d';
        return '01d';
    }
    
    /**
     * Estimate cloud cover from weather code
     */
    private function estimateCloudsFromWeatherCode($code)
    {
        if ($code == 0) return 0;
        if ($code <= 2) return 25;
        if ($code == 3) return 100;
        if ($code >= 45 && $code <= 48) return 50;
        if ($code >= 51 && $code <= 67) return 80;
        if ($code >= 71 && $code <= 77) return 90;
        if ($code >= 80 && $code <= 82) return 85;
        if ($code >= 85 && $code <= 86) return 95;
        if ($code >= 95) return 100;
        return 50;
    }
    
    /**
     * Get location name from coordinates using reverse geocoding
     */
    private function getLocationNameFromCoordinates($lat, $lon)
    {
        try {
            // Use Nominatim (OpenStreetMap) for reverse geocoding - more reliable and supports English
            $nominatimUrl = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=10&addressdetails=1&accept-language=en";
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $nominatimUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; WeatherApp)'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['address'])) {
                    $address = $data['address'];
                    $locationParts = [];
                    
                    // Try to get city/town/village name
                    if (isset($address['city'])) {
                        $locationParts[] = $address['city'];
                    } elseif (isset($address['town'])) {
                        $locationParts[] = $address['town'];
                    } elseif (isset($address['village'])) {
                        $locationParts[] = $address['village'];
                    } elseif (isset($address['municipality'])) {
                        $locationParts[] = $address['municipality'];
                    }
                    
                    // Add country if available
                    if (isset($address['country'])) {
                        $locationParts[] = $address['country'];
                    }
                    
                    if (!empty($locationParts)) {
                        return implode(', ', $locationParts);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Reverse geocoding error: " . $e->getMessage());
        }
        
        // No fallback - return coordinates only if geocoding fails
        error_log("Reverse geocoding failed for coordinates: {$lat}, {$lon}");
        return sprintf('Location (%.2f, %.2f)', $lat, $lon);
    }
    
    /**
     * Estimate UV index based on weather conditions
     */
    private function estimateUVIndex($weatherData)
    {
        $clouds = $weatherData['clouds']['all'] ?? 0;
        $hour = (int)date('H');

        // Base UV index (midday, clear sky)
        $baseUV = 8;

        // Adjust for time of day
        if ($hour < 7 || $hour > 18) {
            return 0; // Night
        } elseif ($hour < 10 || $hour > 16) {
            $baseUV *= 0.5;
        } elseif ($hour < 11 || $hour > 15) {
            $baseUV *= 0.8;
        }

        // Adjust for cloud cover
        $cloudFactor = 1 - ($clouds / 100) * 0.7;

        return round($baseUV * $cloudFactor);
    }

    /**
     * Get fallback weather data when API fails
     */
    private function getFallbackWeatherData()
    {
        return [
            'temperature' => 25,
            'humidity' => 50,
            'condition' => 'Partly Cloudy',
            'icon' => '02d',
            'windSpeed' => 12,
            'location' => 'Kafr El Sheikh, Egypt',
            'country' => 'EG',
            'uvIndex' => 5,
            'feelsLike' => 26,
            'pressure' => 1013,
            'visibility' => 10,
            'clouds' => 30,
            'timestamp' => time()
        ];
    }
}


//===============================================
// Server Connection//
//===============================================

/* private function getDrugsDatabaseConnection()
{
    // Connect to egyptian_drugs database with specific user
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $username = 'AhmedHelal_egyptian_drugs';  // Use the correct user for drugs database
    $password = 'Carmen@1230';  // Use the correct password for drugs database
    
    $dsn = "mysql:host={$host};dbname=AhmedHelal_egyptian_drugs;charset=utf8mb4";
    
    return new \PDO($dsn, $username, $password, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
} */