<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Validator;
use App\Config\Database;
use App\Config\Constants;
use App\Lib\Helpers;
use App\Models\AlertModel;
use App\Services\IOLCalculatorService;
use App\Services\IOPTrendAnalyzerService;
use App\Services\PediatricIOLUndercorrectionService;
use App\Services\CornealAstigmatismService;
use App\Services\TargetIOPCalculatorService;
use App\Services\RefractionConsistencyService;
use App\Services\VisualAcuityProgressService;
use App\Services\OSDICalculatorService;
use App\Services\PachymetryAdjustedIOPCalculatorService;
use App\Services\DiabeticRetinopathyRiskEstimatorService;
use App\Services\MacularThicknessTrendAnalyzerService;
use App\Services\CataractSurgeryReadinessService;
use App\Services\PostOperativeOutcomeAnalyzerService;
use App\Services\ClinicalDataParserService;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

class ApiController
{
    private $auth;
    private $validator;
    private $pdo;
    private $alertModel;
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

    public function getClinics()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            // Secretaries are scoped to their own clinic; doctors/admins see all active clinics.
            if (($user['role'] ?? null) === 'secretary' && !empty($user['clinic_id'])) {
                $stmt = $this->pdo->prepare("
                    SELECT id, code, name_ar, name_en
                    FROM clinics
                    WHERE is_active = 1 AND id = ?
                    ORDER BY sort_order ASC, id ASC
                ");
                $stmt->execute([(int)$user['clinic_id']]);
            } else {
                $stmt = $this->pdo->query("
                    SELECT id, code, name_ar, name_en
                    FROM clinics
                    WHERE is_active = 1
                    ORDER BY sort_order ASC, id ASC
                ");
            }
            $clinics = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'ok' => true,
                'data' => $clinics
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
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
                'clinic_id' => 'required|integer',
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

            // Secretaries are clinic-scoped: ignore client-sent clinic_id and pin to their own.
            $currentUser = $this->auth->user();
            if (($currentUser['role'] ?? null) === 'secretary') {
                if (empty($currentUser['clinic_id'])) {
                    return $this->jsonResponse(['error' => 'Your account has no clinic assigned'], 403);
                }
                $data['clinic_id'] = (int)$currentUser['clinic_id'];
            }

            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getAllErrors()
                ], 400);
            }

            // Ensure clinic_id is valid
            $clinicCheck = $this->pdo->prepare("SELECT id FROM clinics WHERE id = ? AND is_active = 1");
            $clinicCheck->execute([(int)$data['clinic_id']]);
            if (!$clinicCheck->fetch()) {
                return $this->jsonResponse(['error' => 'Invalid or inactive clinic'], 400);
            }

            // Check if patient already has an active appointment for today
            $today = date('Y-m-d');
            if ($data['date'] === $today) {
                $checkStmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM appointments 
                    WHERE patient_id = ? 
                    AND date = CURDATE()
                    AND status NOT IN ('Completed', 'Cancelled')
                ");
                $checkStmt->execute([$data['patient_id']]);
                $checkResult = $checkStmt->fetch(\PDO::FETCH_ASSOC);

                if ($checkResult['count'] > 0) {
                    return $this->jsonResponse([
                        'error' => 'Patient already has an appointment scheduled for today',
                        'message' => 'This patient already has an appointment scheduled for today. Please complete or cancel the existing appointment first.'
                    ], 400);
                }
            }

            // Check if time slot is available globally (any doctor can book any available slot)
            if (
                !Helpers::isTimeSlotAvailableGlobal(
                    $data['date'],
                    $data['start_time'],
                    $this->calculateEndTime($data['start_time'])
                )
            ) {
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

    /**
     * Resolve the clinic_id to use when writing a finance row.
     *
     *   - Secretary: ALWAYS pinned to their own users.clinic_id. Anything the
     *     client sent is ignored (defence-in-depth, same pattern as appointments).
     *   - Doctor / admin: uses $input['clinic_id'] if provided and valid;
     *     otherwise inherits from a linked appointment (if any), otherwise
     *     falls back to clinic 1 (Riyadh, historical default).
     *
     * Throws a controlled \RuntimeException with a JSON-friendly message when
     * the resolved clinic cannot be used (no clinic on user, invalid id, etc.).
     */
    private function resolveClinicIdForRequest(array $input = [], ?int $inheritFromAppointmentId = null): int
    {
        $user = $this->auth->user();
        $role = $user['role'] ?? null;

        if ($role === 'secretary') {
            if (empty($user['clinic_id'])) {
                throw new \RuntimeException('Your account has no clinic assigned');
            }
            return (int)$user['clinic_id'];
        }

        // Doctor / admin path
        if (!empty($input['clinic_id'])) {
            $cid = (int)$input['clinic_id'];
            $stmt = $this->pdo->prepare("SELECT id FROM clinics WHERE id = ? AND is_active = 1");
            $stmt->execute([$cid]);
            if ($stmt->fetch()) {
                return $cid;
            }
            throw new \RuntimeException('Invalid or inactive clinic');
        }

        if ($inheritFromAppointmentId) {
            $stmt = $this->pdo->prepare("SELECT clinic_id FROM appointments WHERE id = ?");
            $stmt->execute([$inheritFromAppointmentId]);
            $inherited = $stmt->fetchColumn();
            if ($inherited) {
                return (int)$inherited;
            }
        }

        // Final fallback — keeps doctor/admin UIs working until Phase 3 adds
        // an explicit clinic picker on every financial form.
        return 1;
    }

    /**
     * For update/delete on a financial row: secretaries may only touch rows
     * inside their own clinic. Returns a JSON 403 response to be returned by
     * the caller if denied; null when allowed.
     */
    private function assertFinanceRowInScope(?array $row): ?array
    {
        $user = $this->auth->user();
        if (($user['role'] ?? null) !== 'secretary' || empty($user['clinic_id'])) {
            return null; // doctors/admins can touch any clinic
        }
        $rowClinic = isset($row['clinic_id']) ? (int)$row['clinic_id'] : 0;
        if ($rowClinic !== (int)$user['clinic_id']) {
            return $this->jsonResponse(['error' => 'هذا السجل يخص عيادة أخرى ولا يمكنك التعديل عليه.'], 403);
        }
        return null;
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

            // Resolve clinic_id: secretary → own, doctor/admin → input or inherit
            // from the linked appointment, falling back to clinic 1.
            try {
                $data['clinic_id'] = $this->resolveClinicIdForRequest(
                    $data,
                    isset($data['appointment_id']) ? (int)$data['appointment_id'] : null
                );
            } catch (\RuntimeException $e) {
                return $this->jsonResponse(['error' => $e->getMessage()], 400);
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

            $query = trim($_GET['q'] ?? '');
            $limit = min((int) ($_GET['limit'] ?? 10), 20);

            // If query is empty, return recent appointments instead of empty array - show all appointments
            if (strlen($query) < 1) {
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
                            ORDER BY a.date DESC, a.start_time DESC
                            LIMIT ?
                        ");
                $stmt->execute([$limit]);

                $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => $appointments
                ]);
            }

            // Removed doctor_id filter - show all results regardless of doctor

            // Check if query is a date (DD-MM-YYYY, YYYY-MM-DD, DD/MM/YYYY, YYYY/MM/DD)
            $isDate = false;
            $dateValue = null;

            // Try to parse date in various formats
            $dateFormats = [
                'd-m-Y' => 'DD-MM-YYYY',      // DD-MM-YYYY
                'Y-m-d' => 'YYYY-MM-DD',      // YYYY-MM-DD
                'd/m/Y' => 'DD/MM/YYYY',      // DD/MM/YYYY
                'Y/m/d' => 'YYYY/MM/DD',      // YYYY/MM/DD
                'd-m-y' => 'DD-MM-YY',        // DD-MM-YY
                'y-m-d' => 'YY-MM-DD',        // YY-MM-DD
            ];

            foreach ($dateFormats as $format => $formatName) {
                $date = \DateTime::createFromFormat($format, $query);
                if ($date !== false) {
                    // Check if the formatted date matches the original query
                    $formatted = $date->format($format);

                    if ($formatted === $query) {
                        $isDate = true;
                        $dateValue = $date->format('Y-m-d'); // Convert to database format
                        break;
                    }
                }
            }

            // If no format matched, try a more flexible approach
            if (!$isDate) {
                // Try to detect date pattern manually
                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{2,4})$/', $query, $matches)) {
                    $day = (int) $matches[1];
                    $month = (int) $matches[2];
                    $year = (int) $matches[3];

                    // Normalize year (if 2 digits, assume 20xx)
                    if ($year < 100) {
                        $year += 2000;
                    }

                    // Validate date
                    if (checkdate($month, $day, $year)) {
                        $isDate = true;
                        $dateValue = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
            }

            // Search priority: 1. Date, 2. Appointment ID (if numeric), 3. Patient name
            if ($isDate && $dateValue) {
                // Search by date - show all appointments on this date regardless of doctor
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
                    WHERE a.date = ?
                    ORDER BY a.start_time ASC
                            LIMIT ?
                        ");
                $stmt->execute([$dateValue, $limit]);

                $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else if (is_numeric($query)) {
                // Search by appointment ID - show all appointments regardless of doctor
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
                $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                // Search by patient name - show all appointments regardless of doctor
                $searchTerm = '%' . $query . '%';
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
                $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

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
            $allowedSortFields = ['total_appointments', 'last_visit', 'created_at', 'first_name', 'last_name', 'age', 'gender', 'created_by_doctor_name'];
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
                $orderBy = "ORDER BY p.first_name $sortOrder, p.last_name $sortOrder";
            } elseif ($sortBy === 'last_name') {
                $orderBy = "ORDER BY p.last_name $sortOrder, p.first_name $sortOrder";
            } elseif ($sortBy === 'age') {
                // Sort by age (calculated from dob) - older patients first for DESC, younger first for ASC
                if ($sortOrder === 'DESC') {
                    $orderBy = "ORDER BY p.dob ASC"; // Older = earlier dob
                } else {
                    $orderBy = "ORDER BY p.dob DESC"; // Younger = later dob
                }
            } elseif ($sortBy === 'gender') {
                $orderBy = "ORDER BY p.gender $sortOrder";
            } elseif ($sortBy === 'created_by_doctor_name') {
                $orderBy = "ORDER BY created_by_doctor_name $sortOrder";
            } else {
                $orderBy = "ORDER BY p.created_at DESC";
            }

            $stmt = $this->pdo->prepare("
                SELECT p.*, 
                       COUNT(DISTINCT a.id) as total_appointments,
                       MAX(a.date) as last_visit,
                       MAX(CONCAT(a.date, ' ', a.start_time)) as last_appointment_datetime,
                       COUNT(DISTINCT pr.id) as prescriptions_count,
                       COUNT(DISTINCT gp.id) as glasses_count,
                       (SELECT pa.id 
                        FROM patient_attachments pa 
                        LEFT JOIN appointments a ON pa.appointment_id = a.id
                        WHERE pa.patient_id = p.id 
                        AND pa.mime_type LIKE 'image/%'
                        ORDER BY 
                            CASE 
                                WHEN a.id IS NOT NULL 
                                THEN 0
                                ELSE 1
                            END ASC,
                            CASE 
                                WHEN a.id IS NOT NULL 
                                THEN CONCAT(a.date, ' ', COALESCE(a.start_time, '00:00:00'))
                                ELSE '0000-00-00 00:00:00'
                            END DESC,
                            pa.created_at DESC 
                        LIMIT 1) as latest_attachment_id,
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
                LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
                LEFT JOIN glasses_prescriptions gp ON a.id = gp.appointment_id
                GROUP BY p.id
                $orderBy
            ");
            $stmt->execute();
            $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Normalize latest_attachment_id - ensure NULL values are preserved properly
            foreach ($patients as &$patient) {
                // Ensure latest_attachment_id is explicitly set (even if NULL)
                if (!isset($patient['latest_attachment_id'])) {
                    $patient['latest_attachment_id'] = null;
                }
                // Convert empty string to null for consistency
                if ($patient['latest_attachment_id'] === '') {
                    $patient['latest_attachment_id'] = null;
                }
            }
            unset($patient); // Break reference

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
                'clinic_id' => 'required|integer',
                'emergency_contact' => 'max:100',
                'emergency_phone' => 'max:20'
            ];

            $data = $_POST;

            // Secretaries are clinic-scoped: ignore any client-sent clinic_id and pin to their own.
            $currentUser = $this->auth->user();
            if (($currentUser['role'] ?? null) === 'secretary') {
                if (empty($currentUser['clinic_id'])) {
                    return $this->jsonResponse(['error' => 'Your account has no clinic assigned'], 403);
                }
                $data['clinic_id'] = (int)$currentUser['clinic_id'];
            }

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

            // Ensure clinic_id is valid
            $clinicCheck = $this->pdo->prepare("SELECT id FROM clinics WHERE id = ? AND is_active = 1");
            $clinicCheck->execute([(int)$data['clinic_id']]);
            if (!$clinicCheck->fetch()) {
                return $this->jsonResponse(['error' => 'Invalid or inactive clinic'], 400);
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

        // Encode to JSON - use JSON_FORCE_OBJECT for arrays to preserve null values
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
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
                   c.name_en as clinic_name_en, c.name_ar as clinic_name_ar, c.code as clinic_code,
                   DATE_FORMAT(a.start_time, '%H:%i') as start_time_formatted,
                   DATE_FORMAT(a.end_time, '%H:%i') as end_time_formatted
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            LEFT JOIN clinics c ON a.clinic_id = c.id
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
                   c.name_en as clinic_name_en, c.name_ar as clinic_name_ar, c.code as clinic_code,
                   DATE_FORMAT(a.start_time, '%H:%i') as start_time_formatted,
                   DATE_FORMAT(a.end_time, '%H:%i') as end_time_formatted,
                   d.display_name as doctor_name, u.name as user_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            LEFT JOIN clinics c ON a.clinic_id = c.id
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
                   CONCAT(u.name) as doctor_name,
                   c.name_en as clinic_name_en, c.name_ar as clinic_name_ar, c.code as clinic_code
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            LEFT JOIN clinics c ON a.clinic_id = c.id
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
                INSERT INTO appointments (patient_id, doctor_id, clinic_id, booked_by, source, date, start_time, end_time, visit_type, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $endTime = $this->calculateEndTime($data['start_time']);

            // Use booked_by from data if provided, otherwise use current user
            $bookedBy = $data['booked_by'] ?? ($this->auth->user()['id'] ?? null);

            if (!$bookedBy) {
                throw new \Exception('booked_by is required');
            }

            $clinicId = !empty($data['clinic_id']) ? (int)$data['clinic_id'] : null;

            // If patient was created without a clinic, adopt the appointment's clinic as their default
            if ($clinicId) {
                $this->pdo->prepare("UPDATE patients SET clinic_id = ? WHERE id = ? AND clinic_id IS NULL")
                    ->execute([$clinicId, $data['patient_id']]);
            }

            $result = $stmt->execute([
                $data['patient_id'],
                $data['doctor_id'],
                $clinicId,
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
        // created_at = NOW() (explicit) so it reflects the DB session timezone
        // (pinned in Database.php). clinic_id is expected to be already
        // resolved by createPayment() via resolveClinicIdForRequest().
        $stmt = $this->pdo->prepare("
            INSERT INTO payments (appointment_id, patient_id, clinic_id, received_by, type, method, amount,
                                discount_amount, discount_reason, is_exempt, exempt_reason, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['appointment_id'] ?? null,
            $data['patient_id'],
            isset($data['clinic_id']) ? (int)$data['clinic_id'] : null,
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
            INSERT INTO patients (first_name, last_name, dob, gender, phone, alt_phone, address, national_id, clinic_id, emergency_contact, emergency_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            !empty($data['clinic_id']) ? (int)$data['clinic_id'] : null,
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
            // Don't fail the consultation creation
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

    /**
     * Is the day already closed for this clinic? After migration 028 the
     * UNIQUE on daily_closures is (clinic_id, doctor_id, date) so closures
     * are per-clinic. A NULL $clinicId argument means "any clinic" (used in
     * legacy callers that haven't been updated yet).
     */
    private function isDateClosed($date, ?int $clinicId = null)
    {
        if ($clinicId === null) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM daily_closures WHERE date = ?");
            $stmt->execute([$date]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM daily_closures WHERE date = ? AND clinic_id = ?"
            );
            $stmt->execute([$date, $clinicId]);
        }
        return $stmt->fetchColumn() > 0;
    }

    private function createDailyClosure($date, $userId, ?int $clinicId = null)
    {
        // Aggregates are scoped to the same clinic as the closure when given,
        // so per-clinic totals stay accurate.
        $appointmentsSql = "SELECT COUNT(*) as total,
                                  SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
                             FROM appointments WHERE DATE(created_at) = ?";
        $paymentsSql     = "SELECT SUM(CASE WHEN is_exempt = 1 THEN 0
                                            ELSE (amount - COALESCE(discount_amount, 0))
                                       END) as total_payments
                             FROM payments WHERE DATE(created_at) = ?";
        $params = [$date];
        if ($clinicId !== null) {
            $appointmentsSql .= " AND clinic_id = ?";
            $paymentsSql     .= " AND clinic_id = ?";
            $params[] = $clinicId;
        }

        $stmt = $this->pdo->prepare($appointmentsSql);
        $stmt->execute($params);
        $appointmentStats = $stmt->fetch();

        $stmt = $this->pdo->prepare($paymentsSql);
        $stmt->execute($params);
        $paymentStats = $stmt->fetch();

        $stmt = $this->pdo->prepare("
            INSERT INTO daily_closures (date, clinic_id, closed_by, total_appointments, completed_appointments, total_payments, note)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $date,
            $clinicId,
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
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
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
                $attachmentId = $this->pdo->lastInsertId();

                // Create timeline event
                $this->createTimelineEvent($patientId, $appointmentId, 'Attachment', 'Uploaded: ' . $file['name']);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'attachment_id' => (int)$attachmentId,
                ]);
            } else {
                // Delete file if database insert failed
                unlink($filePath);
                return $this->jsonResponse(['success' => false, 'message' => 'Database error']);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    /**
     * Replace the binary content of an existing attachment in place.
     * Keeps the same DB row + same filename so the appointment's attachment list
     * doesn't accumulate duplicates from auto-save (e.g. consultation drawings).
     */
    public function replaceAttachment($id)
    {
        if (ob_get_level()) {
            ob_clean();
        }

        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $id = (int)$id;
            if ($id <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid attachment id'], 400);
            }

            // Fetch existing
            $stmt = $this->pdo->prepare("SELECT * FROM patient_attachments WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$existing) {
                return $this->jsonResponse(['success' => false, 'message' => 'Attachment not found'], 404);
            }

            $user = $this->auth->user();
            // Only the original uploader (or a doctor/admin) may replace the content.
            if ((int)$existing['uploaded_by'] !== (int)$user['id']
                && !in_array($user['role'] ?? '', ['doctor', 'admin'], true)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
            }

            if (!isset($_FILES['attachment_file']) || $_FILES['attachment_file']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse(['success' => false, 'message' => 'No file uploaded or upload error']);
            }

            $file = $_FILES['attachment_file'];

            if ($file['size'] > 5 * 1024 * 1024) {
                return $this->jsonResponse(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            }

            $allowedMimes = [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
            ];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimes, true)) {
                return $this->jsonResponse(['success' => false, 'message' => 'File type not allowed']);
            }

            $uploadDir = __DIR__ . '/../../storage/uploads/attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Keep the existing filename if its extension still matches; otherwise create a new
            // filename and delete the old file. This keeps URLs stable for in-session reloads.
            $existingFilename = $existing['filename'];
            $existingExt = strtolower(pathinfo($existingFilename, PATHINFO_EXTENSION));
            $newExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($existingExt === $newExt) {
                $filename = $existingFilename;
                $filePath = $uploadDir . $filename;
                // Overwrite in place
                if (!@move_uploaded_file($file['tmp_name'], $filePath)) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Failed to overwrite file']);
                }
            } else {
                $filename = uniqid('att_') . '.' . $newExt;
                $filePath = $uploadDir . $filename;
                if (!@move_uploaded_file($file['tmp_name'], $filePath)) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Failed to save new file']);
                }
                // Delete the old file once the new one is in place
                $oldPath = $uploadDir . $existingFilename;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $updateStmt = $this->pdo->prepare("
                UPDATE patient_attachments
                SET filename = ?, original_filename = ?, file_path = ?, file_size = ?, mime_type = ?
                WHERE id = ?
            ");
            $updateStmt->execute([
                $filename,
                $file['name'],
                'storage/uploads/attachments/' . $filename,
                $file['size'],
                $mimeType,
                $id,
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Attachment replaced',
                'attachment_id' => $id,
            ]);
        } catch (\Exception $e) {
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

    /**
     * View patient image for Cards and Folders views
     * Handles file path resolution correctly for patient card images
     */
    public function viewPatientImageForCards($id)
    {
        try {
            if (!$this->auth->check()) {
                http_response_code(401);
                return;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_attachments WHERE id = ?");
            $stmt->execute([$id]);
            $attachment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$attachment) {
                http_response_code(404);
                return;
            }

            // Use same path resolution as viewAttachment
            // file_path is stored as 'storage/uploads/attachments/filename'
            $dbPath = $attachment['file_path'];
            
            // Remove leading slash if present
            $dbPath = ltrim($dbPath, '/');
            
            // Build path exactly like viewAttachment does
            $filePath = __DIR__ . '/../../' . $dbPath;
            
            // Normalize path
            $filePath = realpath($filePath);
            
            if (!$filePath || !file_exists($filePath)) {
                // Try alternative path
                $altPath = realpath(__DIR__ . '/../../storage/uploads/attachments/' . basename($dbPath));
                if ($altPath && file_exists($altPath)) {
                    $filePath = $altPath;
                } else {
                    http_response_code(404);
                    return;
                }
            }

            // Verify it's an image
            if (strpos($attachment['mime_type'], 'image/') !== 0) {
                http_response_code(400);
                return;
            }

            // Clear any previous output
            if (ob_get_level()) {
                ob_clean();
            }

            // Set appropriate headers
            header('Content-Type: ' . $attachment['mime_type']);
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: inline; filename="' . basename($attachment['original_filename']) . '"');
            header('Cache-Control: public, max-age=3600');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

            // Output file
            readfile($filePath);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            error_log("viewPatientImageForCards: Exception for ID {$id}: " . $e->getMessage());
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

    /**
     * Bulk-delete a set of attachment ids that all belong to the same appointment.
     * Body: { appointment_id: int, ids: int[] }
     * Returns counts so the UI can confirm the operation.
     */
    public function bulkDeleteAttachments()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $appointmentId = isset($input['appointment_id']) ? (int)$input['appointment_id'] : 0;
            $ids = isset($input['ids']) && is_array($input['ids']) ? $input['ids'] : [];
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($n) => $n > 0)));

            if ($appointmentId <= 0 || empty($ids)) {
                return $this->jsonResponse(['success' => false, 'message' => 'appointment_id and ids are required'], 400);
            }

            // Only allow deleting rows that actually belong to this appointment (defence-in-depth).
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$appointmentId], $ids);
            $stmt = $this->pdo->prepare("SELECT * FROM patient_attachments WHERE appointment_id = ? AND id IN ($placeholders)");
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return $this->jsonResponse(['success' => true, 'deleted' => 0, 'message' => 'Nothing to delete']);
            }

            $deleted = 0;
            $uploadDir = __DIR__ . '/../../';
            $this->pdo->beginTransaction();
            try {
                $del = $this->pdo->prepare("DELETE FROM patient_attachments WHERE id = ?");
                foreach ($rows as $row) {
                    if ($del->execute([$row['id']])) {
                        $deleted++;
                        $path = $uploadDir . $row['file_path'];
                        if (is_file($path)) @unlink($path);
                        $this->createTimelineEvent(
                            $row['patient_id'],
                            $row['appointment_id'],
                            'Attachment',
                            'Deleted: ' . $row['original_filename']
                        );
                    }
                }
                $this->pdo->commit();
            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }

            return $this->jsonResponse(['success' => true, 'deleted' => $deleted]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function getAppointmentAttachments($appointmentId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Pagination — `perPage = 0` means "no pagination", return everything.
            $page    = max(1, (int)($_GET['page']    ?? 1));
            $perPage = max(0, (int)($_GET['perPage'] ?? 0));

            $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM patient_attachments WHERE appointment_id = ?");
            $countStmt->execute([$appointmentId]);
            $total = (int)$countStmt->fetchColumn();

            if ($perPage > 0) {
                $totalPages = max(1, (int)ceil($total / $perPage));
                if ($page > $totalPages) $page = $totalPages;
                $offset = ($page - 1) * $perPage;
                $stmt = $this->pdo->prepare("
                    SELECT * FROM patient_attachments
                    WHERE appointment_id = ?
                    ORDER BY created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->bindValue(1, (int)$appointmentId, \PDO::PARAM_INT);
                $stmt->bindValue(2, (int)$perPage,       \PDO::PARAM_INT);
                $stmt->bindValue(3, (int)$offset,        \PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $totalPages = 1;
                $stmt = $this->pdo->prepare("
                    SELECT * FROM patient_attachments
                    WHERE appointment_id = ?
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$appointmentId]);
            }

            $attachments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'attachments' => $attachments,
                'pagination' => [
                    'page'        => $page,
                    'perPage'     => $perPage,
                    'total'       => $total,
                    'totalPages'  => $totalPages,
                ],
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

            // Connect to drugs database to get prices
            $drugsPdo = $this->getDrugsDatabaseConnection();

            // Add price to each medication
            $medicationsWithPrices = array_map(function ($medication) use ($drugsPdo) {
                $price = null;
                if (!empty($medication['drug_name'])) {
                    try {
                        // Try to find drug by name in drugs database
                        $priceStmt = $drugsPdo->prepare("
                            SELECT price 
                            FROM drugs 
                            WHERE FirstName = ? 
                            LIMIT 1
                        ");
                        $priceStmt->execute([$medication['drug_name']]);
                        $priceResult = $priceStmt->fetch();
                        if ($priceResult && isset($priceResult['price'])) {
                            $price = $priceResult['price'];
                        }
                    } catch (\Exception $e) {
                        // If error, price remains null
                    }
                }
                $medication['drug_price'] = $price;
                return $medication;
            }, $medications);

            return $this->jsonResponse([
                'success' => true,
                'medications' => $medicationsWithPrices
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
                $data['appointment_id'],
                $data['patient_id'],
                $data['test_type'],
                $data['test_category'],
                $data['test_name'],
                $data['priority'],
                $data['status'],
                $data['ordered_date'],
                $data['expected_date'],
                $data['notes'],
                $data['results'],
                $data['created_at'],
                $data['updated_at']
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

    /**
     * Bulk-delete a set of patient_files ids that all belong to the same patient.
     * Body: { patient_id: int, ids: int[] }
     */
    public function bulkDeletePatientFiles()
    {
        if (ob_get_level()) {
            ob_clean();
        }

        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $patientId = isset($input['patient_id']) ? (int)$input['patient_id'] : 0;
            $ids = isset($input['ids']) && is_array($input['ids']) ? $input['ids'] : [];
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($n) => $n > 0)));

            if ($patientId <= 0 || empty($ids)) {
                return $this->jsonResponse(['success' => false, 'message' => 'patient_id and ids are required'], 400);
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$patientId], $ids);
            $stmt = $this->pdo->prepare("SELECT * FROM patient_files WHERE patient_id = ? AND id IN ($placeholders)");
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return $this->jsonResponse(['success' => true, 'deleted' => 0, 'message' => 'Nothing to delete']);
            }

            $deleted = 0;
            $uploadDir = __DIR__ . '/../../';
            $this->pdo->beginTransaction();
            try {
                $del = $this->pdo->prepare("DELETE FROM patient_files WHERE id = ?");
                foreach ($rows as $row) {
                    if ($del->execute([$row['id']])) {
                        $deleted++;
                        $path = $uploadDir . $row['file_path'];
                        if (is_file($path)) @unlink($path);
                    }
                }
                $this->pdo->commit();
            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }

            return $this->jsonResponse(['success' => true, 'deleted' => $deleted]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Replace the binary content of an existing patient_files row in place.
     * Mirrors replaceAttachment but targets the separate patient files endpoint
     * used by the patient profile page.
     */
    public function replacePatientFile($id)
    {
        if (ob_get_level()) {
            ob_clean();
        }

        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $id = (int)$id;
            if ($id <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid file id'], 400);
            }

            $stmt = $this->pdo->prepare("SELECT * FROM patient_files WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$existing) {
                return $this->jsonResponse(['success' => false, 'message' => 'File not found'], 404);
            }

            $user = $this->auth->user();
            if ((int)$existing['uploaded_by'] !== (int)$user['id']
                && !in_array($user['role'] ?? '', ['doctor', 'admin'], true)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
            }

            if (!isset($_FILES['patient_file']) || $_FILES['patient_file']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse(['success' => false, 'message' => 'No file uploaded or upload error']);
            }

            $file = $_FILES['patient_file'];

            if ($file['size'] > 5 * 1024 * 1024) {
                return $this->jsonResponse(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            }

            $allowedMimes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
            ];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimes, true)) {
                return $this->jsonResponse(['success' => false, 'message' => 'File type not allowed']);
            }

            $uploadDir = __DIR__ . '/../../uploads/patients/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Keep the same on-disk filename when the extension matches so the URL stays stable.
            $existingPath = $existing['file_path']; // e.g. "uploads/patients/patient_5_xxx.png"
            $existingBasename = basename($existingPath);
            $existingExt = strtolower(pathinfo($existingBasename, PATHINFO_EXTENSION));
            $newExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($existingExt === $newExt) {
                $newBasename = $existingBasename;
                $newRelativePath = $existingPath;
                $newAbsPath = $uploadDir . $newBasename;
                if (!@move_uploaded_file($file['tmp_name'], $newAbsPath)) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Failed to overwrite file']);
                }
            } else {
                $newBasename = 'patient_' . (int)$existing['patient_id'] . '_' . time() . '_' . uniqid() . '.' . $newExt;
                $newRelativePath = 'uploads/patients/' . $newBasename;
                $newAbsPath = $uploadDir . $newBasename;
                if (!@move_uploaded_file($file['tmp_name'], $newAbsPath)) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Failed to save new file']);
                }
                $oldAbsPath = __DIR__ . '/../../' . $existingPath;
                if (is_file($oldAbsPath)) {
                    @unlink($oldAbsPath);
                }
            }

            $updateStmt = $this->pdo->prepare("
                UPDATE patient_files
                SET original_filename = ?, file_path = ?, file_size = ?
                WHERE id = ?
            ");
            $updateStmt->execute([
                $file['name'],
                $newRelativePath,
                $file['size'],
                $id,
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'File replaced',
                'file_id' => $id,
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function getPatientFiles($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $page    = max(1, (int)($_GET['page']    ?? 1));
            $perPage = max(0, (int)($_GET['perPage'] ?? 0));

            $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM patient_files WHERE patient_id = ?");
            $countStmt->execute([$patientId]);
            $total = (int)$countStmt->fetchColumn();

            if ($perPage > 0) {
                $totalPages = max(1, (int)ceil($total / $perPage));
                if ($page > $totalPages) $page = $totalPages;
                $offset = ($page - 1) * $perPage;
                $stmt = $this->pdo->prepare("
                    SELECT * FROM patient_files
                    WHERE patient_id = ?
                    ORDER BY created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->bindValue(1, (int)$patientId, \PDO::PARAM_INT);
                $stmt->bindValue(2, (int)$perPage,   \PDO::PARAM_INT);
                $stmt->bindValue(3, (int)$offset,    \PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $totalPages = 1;
                $stmt = $this->pdo->prepare("
                    SELECT * FROM patient_files
                    WHERE patient_id = ?
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$patientId]);
            }

            $files = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'files' => $files,
                'pagination' => [
                    'page'       => $page,
                    'perPage'    => $perPage,
                    'total'      => $total,
                    'totalPages' => $totalPages,
                ],
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
            usort($allEntries, function ($a, $b) {
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
                if (
                    $cacheData && isset($cacheData['timestamp']) &&
                    (time() - $cacheData['timestamp']) < $cacheDuration
                ) {
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
                        continue;
                    }

                    // Suppress XML errors
                    libxml_use_internal_errors(true);
                    $xml = simplexml_load_string($xmlContent);
                    libxml_clear_errors();

                    if (!$xml) {
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
                                    if (!empty($items))
                                        break;
                                }
                            }
                        }
                    }

                    if (empty($items)) {
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
                            $title = trim((string) $item->title);
                        } else {
                            // Try xpath for RSS 1.0
                            $titleNodes = $item->xpath('.//title | .//dc:title');
                            if (!empty($titleNodes)) {
                                $title = trim((string) $titleNodes[0]);
                            }
                        }

                        // Get link - handle RSS 1.0 RDF (rdf:about attribute)
                        if (isset($item->link)) {
                            // RSS 2.0: link is text, Atom: link can be href attribute
                            $link = trim((string) $item->link);
                            if (isset($item->link['href'])) {
                                $link = trim((string) $item->link['href']);
                            }
                        } else {
                            // Try rdf:about attribute (RSS 1.0)
                            // Get attributes from RDF namespace
                            $rdfAttrs = $item->attributes('http://www.w3.org/1999/02/22-rdf-syntax-ns#');
                            if (!empty($rdfAttrs) && isset($rdfAttrs['about'])) {
                                $link = trim((string) $rdfAttrs['about']);
                            } else {
                                // Try regular attributes
                                $attributes = $item->attributes();
                                if (isset($attributes['rdf:about'])) {
                                    $link = trim((string) $attributes['rdf:about']);
                                } elseif (isset($attributes['about'])) {
                                    $link = trim((string) $attributes['about']);
                                } else {
                                    // Try xpath
                                    $linkNodes = $item->xpath('.//link | .//dc:identifier');
                                    if (!empty($linkNodes)) {
                                        $link = trim((string) $linkNodes[0]);
                                    }
                                }
                            }
                        }

                        // Get description
                        if (isset($item->description)) {
                            $description = (string) $item->description;
                        } elseif (isset($item->summary)) {
                            $description = (string) $item->summary;
                        } elseif (isset($item->content)) {
                            $description = (string) $item->content;
                        } else {
                            // Try xpath for RSS 1.0
                            $descNodes = $item->xpath('.//description | .//dc:description | .//content:encoded');
                            if (!empty($descNodes)) {
                                $description = (string) $descNodes[0];
                            }
                        }

                        if (isset($item->pubDate)) {
                            $pubDate = (string) $item->pubDate;
                        } elseif (isset($item->date)) {
                            $pubDate = (string) $item->date;
                        } elseif (isset($item->published)) {
                            $pubDate = (string) $item->published;
                        } elseif (isset($item->{'dc:date'})) {
                            $pubDate = (string) $item->{'dc:date'};
                        } else {
                            // Try to get date from namespaces (RSS 1.0 RDF)
                            $namespaces = $item->getNamespaces(true);
                            foreach ($namespaces as $prefix => $ns) {
                                $dcDate = $item->xpath(".//{$prefix}:date");
                                if (!empty($dcDate)) {
                                    $pubDate = (string) $dcDate[0];
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
            usort($filteredArticles, function ($a, $b) {
                if ($a['is_breaking'] && !$b['is_breaking'])
                    return -1;
                if (!$a['is_breaking'] && $b['is_breaking'])
                    return 1;

                $timeA = 0;
                $timeB = 0;

                if (!empty($a['pubDate'])) {
                    $timeA = strtotime($a['pubDate']);
                    if ($timeA === false)
                        $timeA = 0;
                }

                if (!empty($b['pubDate'])) {
                    $timeB = strtotime($b['pubDate']);
                    if ($timeB === false)
                        $timeB = 0;
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
                        $articles = array_slice($oldCache['articles'], 0, 15);
                    }
                }

                if (empty($articles)) {
                    return $this->jsonResponse([
                        'success' => false,
                        'error' => 'No articles available',
                        'articles' => []
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

        if ($httpCode === 200 && $content && strlen($content) > 100) {
            return $content;
        }

        return false;
    }

    private function isBreakingNews($title, $description)
    {
        $breakingKeywords = [
            'breaking',
            'urgent',
            'alert',
            'critical',
            'emergency',
            'important',
            'warning',
            'recall',
            'withdrawal',
            'adverse'
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

    public function checkPatientActiveAppointments($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            // Check if patient has active appointments for today only
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM appointments 
                WHERE patient_id = ? 
                AND date = CURDATE()
                AND status NOT IN ('Completed', 'Cancelled')
            ");
            $stmt->execute([$patientId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $count = (int) $result['count'];

            return $this->jsonResponse([
                'has_active' => $count > 0,
                'count' => $count
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
            $limit = min((int) ($_GET['limit'] ?? 20), 50); // Max 50 results
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

            // Also log the full SQL query for debugging
            $fullQuery = "SELECT ID, FirstName as drug_name, LastName as active_ingredient, price, Company, Pharmacology as category, Route as administration_route, SRDE, GI FROM drugs WHERE {$whereClause} {$orderBy} LIMIT ?";

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

            // Get exact alternatives (same SRDE)
            $exactAlternatives = [];
            if (!empty($drug['SRDE']) && trim($drug['SRDE']) !== '') {
                $stmt = $drugsPdo->prepare("
                    SELECT
                        ID,
                        FirstName as drug_name,
                        LastName as active_ingredient,
                        price,
                        Company,
                        Route as administration_route
                    FROM drugs
                    WHERE SRDE = ?
                    AND Route = ?
                    AND ID != ?
                    ORDER BY FirstName
                    LIMIT 20
                ");
                $stmt->execute([$drug['SRDE'], $drug['administration_route'], $drugId]);
                $exactAlternatives = $stmt->fetchAll();
            }

            // Get similar products (same active ingredient, exclude exact alternatives)
            $similarProducts = [];
            if (!empty($drug['active_ingredient']) && trim($drug['active_ingredient']) !== '') {
                $stmt = $drugsPdo->prepare("
                    SELECT
                        ID,
                        FirstName as drug_name,
                        LastName as active_ingredient,
                        price,
                        Company,
                        Route as administration_route
                    FROM drugs
                    WHERE LastName = ?
                    AND Route = ?
                    AND ID != ?
                    AND (SRDE != ? OR SRDE IS NULL OR SRDE = '')
                    ORDER BY FirstName
                    LIMIT 20
                ");
                $stmt->execute([
                    $drug['active_ingredient'],
                    $drug['administration_route'],
                    $drugId,
                    $drug['SRDE'] ?? ''
                ]);
                $similarProducts = $stmt->fetchAll();
            }

            return $this->jsonResponse([
                'drug' => $drug,
                'exact_alternatives' => $exactAlternatives,
                'similar_products' => $similarProducts
            ]);

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
        // Connect to hclinic_drugs database with specific user
        $host = $_ENV['DB_HOST'] ?? 'db';
        $username = $_ENV['DRUGS_DB_USER'] ?? 'hclinic_drugs';
        $password = $_ENV['DRUGS_DB_PASS'] ?? 'Carmen@1230';
        $dbname = $_ENV['DRUGS_DB_NAME'] ?? 'hclinic_drugs';

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

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

            $limit = min((int) ($_GET['limit'] ?? 10), 20); // Max 20 results, default 10

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

            // Connect to drugs database to get prices
            $drugsPdo = $this->getDrugsDatabaseConnection();

            // Format the response and fetch prices
            $formattedDrugs = array_map(function ($drug) use ($drugsPdo) {
                $price = null;
                try {
                    // Try to find drug by name in drugs database
                    $priceStmt = $drugsPdo->prepare("
                        SELECT price 
                        FROM drugs 
                        WHERE FirstName = ? 
                        LIMIT 1
                    ");
                    $priceStmt->execute([$drug['drug_name']]);
                    $priceResult = $priceStmt->fetch();
                    if ($priceResult && isset($priceResult['price'])) {
                        $price = $priceResult['price'];
                    }
                } catch (\Exception $e) {
                    // If error, price remains null
                }

                return [
                    'drug_name' => $drug['drug_name'],
                    'usage_count' => (int) $drug['usage_count'],
                    'common_frequencies' => $drug['common_frequencies'] ?: 'N/A',
                    'common_doses' => $drug['common_doses'] ?: 'N/A',
                    'price' => $price
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
            $limit = min((int) ($_GET['limit'] ?? 10), 20); // Max 20 results, default 10

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
                    price,
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
     * Comprehensive search across all entities
     * Searches: appointments, drugs, patients, media, prescriptions, glass prescriptions, medical history, notes, alerts, forum, consultation_notes
     * Supports refinement using & operator: "query & refinement"
     * - If refinement is numeric: filter by patient_id or appointment_id
     * - If refinement is text: filter by patient name
     */
    public function comprehensiveSearch()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $query = trim($_GET['q'] ?? '');
            $limit = min((int) ($_GET['limit'] ?? 10), 20); // Max 20 results per category

            if (strlen($query) < 2) {
                return $this->jsonResponse([
                    'results' => [],
                    'total' => 0
                ]);
            }

            // Parse query for refinement using & and #
            // Format: "query & refinement" or "query # date"
            $baseQuery = $query;
            $refinement = null;
            $refineByDate = null;

            // Check for & refinement (patient ID/name)
            if (strpos($query, '&') !== false) {
                $parts = explode('&', $query, 2);
                $baseQuery = trim($parts[0]);
                $refinement = isset($parts[1]) ? trim($parts[1]) : null;
            }

            // Check for # date refinement (overrides & if both present)
            if (strpos($baseQuery, '#') !== false) {
                $dateParts = explode('#', $baseQuery, 2);
                $baseQuery = trim($dateParts[0]);
                $refineByDate = isset($dateParts[1]) ? trim($dateParts[1]) : null;
            } elseif ($refinement && strpos($refinement, '#') !== false) {
                // Date refinement in the & part
                $dateParts = explode('#', $refinement, 2);
                $refinement = trim($dateParts[0]);
                $refineByDate = isset($dateParts[1]) ? trim($dateParts[1]) : null;
            }

            // Determine refinement type
            $refineByPatientId = null;
            $refineByAppointmentId = null;
            $refineByPatientName = null;

            if ($refinement) {
                if (is_numeric($refinement)) {
                    $refineByPatientId = (int) $refinement;
                    $refineByAppointmentId = (int) $refinement;
                } else {
                    $refineByPatientName = '%' . $refinement . '%';
                }
            }

            // Parse date refinement (supports YYYY-MM-DD, YYYY-MM, YYYY formats)
            $refineByDateStart = null;
            $refineByDateEnd = null;
            if ($refineByDate) {
                // Try to parse date in various formats
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $refineByDate)) {
                    // Full date: YYYY-MM-DD
                    $refineByDateStart = $refineByDate . ' 00:00:00';
                    $refineByDateEnd = $refineByDate . ' 23:59:59';
                } elseif (preg_match('/^\d{4}-\d{2}$/', $refineByDate)) {
                    // Month: YYYY-MM
                    $refineByDateStart = $refineByDate . '-01 00:00:00';
                    $lastDay = date('t', strtotime($refineByDate . '-01'));
                    $refineByDateEnd = $refineByDate . '-' . $lastDay . ' 23:59:59';
                } elseif (preg_match('/^\d{4}$/', $refineByDate)) {
                    // Year: YYYY
                    $refineByDateStart = $refineByDate . '-01-01 00:00:00';
                    $refineByDateEnd = $refineByDate . '-12-31 23:59:59';
                }
            }

            $user = $this->auth->user();
            $results = [];
            $searchTerm = '%' . $baseQuery . '%';

            // 1. Search Patients
            try {
                $stmt = $this->pdo->prepare("
                    SELECT id, first_name, last_name, phone, dob, gender, 'patient' as type
                    FROM patients 
                    WHERE first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?
                    ORDER BY 
                        CASE 
                            WHEN first_name LIKE ? THEN 1
                            WHEN last_name LIKE ? THEN 2
                            ELSE 3
                        END,
                        first_name, last_name
                    LIMIT ?
                ");
                $exactMatch = '%' . $query . '%';
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $exactMatch, $exactMatch, $limit]);
                $patients = $stmt->fetchAll();
                foreach ($patients as $patient) {
                    $results[] = [
                        'id' => $patient['id'],
                        'title' => $patient['first_name'] . ' ' . $patient['last_name'],
                        'subtitle' => $patient['phone'] . ($patient['dob'] ? ' • ' . date('Y-m-d', strtotime($patient['dob'])) : ''),
                        'type' => 'patient',
                        'icon' => 'bi-people',
                        'url' => '/doctor/patients/' . $patient['id']
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 2. Search Appointments
            try {
                $stmt = $this->pdo->prepare("
                    SELECT a.id, a.date, a.start_time, p.first_name, p.last_name, p.id as patient_id
                    FROM appointments a
                    LEFT JOIN patients p ON a.patient_id = p.id
                    WHERE a.id LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?
                    ORDER BY a.date DESC, a.start_time DESC
                    LIMIT ?
                ");
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
                $appointments = $stmt->fetchAll();
                foreach ($appointments as $appointment) {
                    $patientName = ($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? '');
                    $results[] = [
                        'id' => $appointment['id'],
                        'title' => 'Appointment #' . $appointment['id'] . ($patientName ? ' - ' . trim($patientName) : ''),
                        'subtitle' => ($appointment['date'] ? date('M d, Y', strtotime($appointment['date'])) : '') .
                            ($appointment['start_time'] ? ' at ' . date('H:i', strtotime($appointment['start_time'])) : ''),
                        'type' => 'appointment',
                        'icon' => 'bi-calendar3',
                        'url' => '/doctor/appointments/' . $appointment['id']
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 3. Search Drugs (from remote drugs database)
            try {
                $drugsPdo = $this->getDrugsDatabaseConnection();
                $stmt = $drugsPdo->prepare("
                    SELECT ID, FirstName as drug_name, LastName as active_ingredient, Company
                    FROM drugs 
                    WHERE FirstName LIKE ? OR LastName LIKE ? OR Company LIKE ?
                    ORDER BY 
                        CASE 
                            WHEN FirstName LIKE ? THEN 1
                            WHEN LastName LIKE ? THEN 2
                            ELSE 3
                        END,
                        FirstName
                    LIMIT ?
                ");
                $exactMatch = '%' . $query . '%';
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $exactMatch, $exactMatch, $limit]);
                $drugs = $stmt->fetchAll();
                foreach ($drugs as $drug) {
                    $results[] = [
                        'id' => $drug['ID'],
                        'title' => $drug['drug_name'],
                        'subtitle' => ($drug['active_ingredient'] ? $drug['active_ingredient'] : '') .
                            ($drug['Company'] ? ' • ' . $drug['Company'] : ''),
                        'type' => 'drug',
                        'icon' => 'bi-capsule',
                        'url' => '/doctor/drugs?search=' . urlencode($drug['drug_name']) . '&drugId=' . $drug['ID']
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 4. Search Media
            try {
                $stmt = $this->pdo->prepare("
                    SELECT m.id, m.file_name, m.file_path, p.first_name, p.last_name, p.id as patient_id
                    FROM media m
                    LEFT JOIN patients p ON m.patient_id = p.id
                    WHERE m.file_name LIKE ? OR m.description LIKE ?
                    ORDER BY m.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$searchTerm, $searchTerm, $limit]);
                $media = $stmt->fetchAll();
                foreach ($media as $item) {
                    $patientName = ($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '');
                    $results[] = [
                        'id' => $item['id'],
                        'title' => $item['file_name'],
                        'subtitle' => $patientName ? 'Patient: ' . trim($patientName) : 'Media',
                        'type' => 'media',
                        'icon' => 'bi-images',
                        'url' => '/doctor/media?search=' . urlencode($item['file_name'])
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 5. Search Prescriptions (Medications)
            try {
                $stmt = $this->pdo->prepare("
                    SELECT pr.id, pr.drug_name, pr.dosage, a.id as appointment_id, p.first_name, p.last_name, p.id as patient_id
                    FROM prescriptions pr
                    LEFT JOIN appointments a ON pr.appointment_id = a.id
                    LEFT JOIN patients p ON a.patient_id = p.id
                    WHERE pr.drug_name LIKE ? OR pr.dosage LIKE ?
                    ORDER BY pr.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$searchTerm, $searchTerm, $limit]);
                $prescriptions = $stmt->fetchAll();
                foreach ($prescriptions as $prescription) {
                    $patientName = ($prescription['first_name'] ?? '') . ' ' . ($prescription['last_name'] ?? '');
                    $results[] = [
                        'id' => $prescription['id'],
                        'title' => $prescription['drug_name'],
                        'subtitle' => ($prescription['dosage'] ? $prescription['dosage'] . ' • ' : '') .
                            ($patientName ? trim($patientName) : ''),
                        'type' => 'prescription',
                        'icon' => 'bi-capsule',
                        'url' => $prescription['appointment_id'] ? '/doctor/appointments/' . $prescription['appointment_id'] : '/doctor/medications'
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 6. Search Glass Prescriptions
            try {
                $stmt = $this->pdo->prepare("
                    SELECT gp.id, gp.right_sphere, gp.left_sphere, a.id as appointment_id, p.first_name, p.last_name, p.id as patient_id
                    FROM glasses_prescriptions gp
                    LEFT JOIN appointments a ON gp.appointment_id = a.id
                    LEFT JOIN patients p ON a.patient_id = p.id
                    WHERE gp.right_sphere LIKE ? OR gp.left_sphere LIKE ? OR gp.right_cylinder LIKE ? OR gp.left_cylinder LIKE ?
                    ORDER BY gp.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
                $glasses = $stmt->fetchAll();
                foreach ($glasses as $glass) {
                    $patientName = ($glass['first_name'] ?? '') . ' ' . ($glass['last_name'] ?? '');
                    $results[] = [
                        'id' => $glass['id'],
                        'title' => 'Glasses Prescription',
                        'subtitle' => ($glass['right_sphere'] || $glass['left_sphere'] ?
                            'R: ' . ($glass['right_sphere'] ?? 'N/A') . ' L: ' . ($glass['left_sphere'] ?? 'N/A') . ' • ' : '') .
                            ($patientName ? trim($patientName) : ''),
                        'type' => 'glasses',
                        'icon' => 'bi-eyeglasses',
                        'url' => $glass['appointment_id'] ? '/doctor/appointments/' . $glass['appointment_id'] : '/doctor/glasses'
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 7. Search Notes
            try {
                $stmt = $this->pdo->prepare("
                    SELECT n.id, n.title, n.content, n.category, p.first_name, p.last_name, p.id as patient_id
                    FROM notes n
                    LEFT JOIN patients p ON n.patient_id = p.id
                    WHERE n.title LIKE ? OR n.content LIKE ?
                    ORDER BY n.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$searchTerm, $searchTerm, $limit]);
                $notes = $stmt->fetchAll();
                foreach ($notes as $note) {
                    $patientName = ($note['first_name'] ?? '') . ' ' . ($note['last_name'] ?? '');
                    $contentPreview = mb_substr(strip_tags($note['content'] ?? ''), 0, 50);
                    $results[] = [
                        'id' => $note['id'],
                        'title' => $note['title'] ?: 'Untitled Note',
                        'subtitle' => ($note['category'] ? ucfirst($note['category']) . ' • ' : '') .
                            ($contentPreview ? $contentPreview . '...' : '') .
                            ($patientName ? ' • ' . trim($patientName) : ''),
                        'type' => 'note',
                        'icon' => 'bi-sticky',
                        'url' => '/doctor/notes?search=' . urlencode($query)
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 8. Search Alerts
            try {
                $stmt = $this->pdo->prepare("
                    SELECT a.id, a.title, a.message, a.priority, p.first_name, p.last_name, p.id as patient_id
                    FROM alerts a
                    LEFT JOIN patients p ON a.patient_id = p.id
                    WHERE a.title LIKE ? OR a.message LIKE ?
                    ORDER BY a.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$searchTerm, $searchTerm, $limit]);
                $alerts = $stmt->fetchAll();
                foreach ($alerts as $alert) {
                    $patientName = ($alert['first_name'] ?? '') . ' ' . ($alert['last_name'] ?? '');
                    $results[] = [
                        'id' => $alert['id'],
                        'title' => $alert['title'],
                        'subtitle' => ($alert['priority'] ? ucfirst($alert['priority']) . ' • ' : '') .
                            mb_substr(strip_tags($alert['message'] ?? ''), 0, 50) .
                            ($patientName ? ' • ' . trim($patientName) : ''),
                        'type' => 'alert',
                        'icon' => 'bi-bell',
                        'url' => '/doctor/alerts?search=' . urlencode($query)
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 9. Search Forum Topics
            try {
                $stmt = $this->pdo->prepare("
                    SELECT t.id, t.title, t.content, t.category, u.name as author_name
                    FROM doctor_forum_topics t
                    LEFT JOIN users u ON t.created_by = u.id
                    WHERE t.title LIKE ? OR t.content LIKE ?
                    ORDER BY t.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$searchTerm, $searchTerm, $limit]);
                $topics = $stmt->fetchAll();
                foreach ($topics as $topic) {
                    $contentPreview = mb_substr(strip_tags($topic['content'] ?? ''), 0, 50);
                    $results[] = [
                        'id' => $topic['id'],
                        'title' => $topic['title'],
                        'subtitle' => ($topic['category'] ? ucfirst($topic['category']) . ' • ' : '') .
                            ($contentPreview ? $contentPreview . '...' : '') .
                            ($topic['author_name'] ? ' • by ' . $topic['author_name'] : ''),
                        'type' => 'forum',
                        'icon' => 'bi-chat-dots',
                        'url' => '/doctor/forum/topic/' . $topic['id']
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 10. Search Medical History (Fixed)
            try {
                $whereConditions = [];
                $params = [];

                // Base search conditions
                $whereConditions[] = "(mh.diagnosis LIKE ? OR mh.medications LIKE ? OR mh.allergies LIKE ? OR mh.notes LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;

                // Add refinement if specified
                if ($refineByPatientId !== null) {
                    $whereConditions[] = "AND (p.id = ? OR mh.patient_id = ?)";
                    $params[] = $refineByPatientId;
                    $params[] = $refineByPatientId;
                } elseif ($refineByPatientName !== null) {
                    $whereConditions[] = "AND (p.first_name LIKE ? OR p.last_name LIKE ?)";
                    $params[] = $refineByPatientName;
                    $params[] = $refineByPatientName;
                }

                $params[] = $limit;

                $stmt = $this->pdo->prepare("
                    SELECT mh.id, mh.diagnosis, mh.medications, mh.allergies, mh.notes, 
                           p.first_name, p.last_name, p.id as patient_id
                    FROM medical_history mh
                    LEFT JOIN patients p ON mh.patient_id = p.id
                    WHERE " . implode(' ', $whereConditions) . "
                    ORDER BY mh.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute($params);
                $history = $stmt->fetchAll();
                foreach ($history as $item) {
                    $patientName = ($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '');
                    $results[] = [
                        'id' => $item['id'],
                        'title' => $item['diagnosis'] ?: 'Medical History',
                        'subtitle' => ($item['medications'] ? 'Medications: ' . mb_substr($item['medications'], 0, 30) . '... • ' : '') .
                            ($patientName ? trim($patientName) : ''),
                        'type' => 'medical_history',
                        'icon' => 'bi-file-medical',
                        'url' => $item['patient_id'] ? '/doctor/patients/' . $item['patient_id'] : '/doctor/patients'
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            // 11. Search Consultation Notes (New)
            try {
                $whereConditions = [];
                $params = [];

                // Base search conditions in consultation_notes fields (including slit_lamp fields)
                $whereConditions[] = "(cn.chief_complaint LIKE ? OR cn.hx_present_illness LIKE ? OR cn.diagnosis LIKE ? 
                                      OR cn.medication LIKE ? OR cn.plan LIKE ? OR cn.systemic_disease LIKE ?
                                      OR cn.slit_lamp_right LIKE ? OR cn.slit_lamp_left LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;

                // Add refinement if specified
                if ($refineByPatientId !== null) {
                    $whereConditions[] = "AND (p.id = ? OR a.id = ? OR cn.appointment_id = ?)";
                    $params[] = $refineByPatientId;
                    $params[] = $refineByAppointmentId;
                    $params[] = $refineByAppointmentId;
                } elseif ($refineByPatientName !== null) {
                    $whereConditions[] = "AND (p.first_name LIKE ? OR p.last_name LIKE ?)";
                    $params[] = $refineByPatientName;
                    $params[] = $refineByPatientName;
                }

                // Add date refinement if specified (search in created_at or updated_at)
                if ($refineByDateStart !== null && $refineByDateEnd !== null) {
                    $whereConditions[] = "AND ((cn.created_at >= ? AND cn.created_at <= ?) OR (cn.updated_at >= ? AND cn.updated_at <= ?))";
                    $params[] = $refineByDateStart;
                    $params[] = $refineByDateEnd;
                    $params[] = $refineByDateStart;
                    $params[] = $refineByDateEnd;
                }

                $params[] = $limit;

                $stmt = $this->pdo->prepare("
                    SELECT cn.id, cn.appointment_id, cn.diagnosis, cn.chief_complaint, 
                           cn.hx_present_illness, cn.medication, cn.plan, cn.slit_lamp_right, cn.slit_lamp_left,
                           cn.created_at, cn.updated_at,
                           p.first_name, p.last_name, p.id as patient_id, a.id as appointment_id_full
                    FROM consultation_notes cn
                    LEFT JOIN appointments a ON cn.appointment_id = a.id
                    LEFT JOIN patients p ON a.patient_id = p.id
                    WHERE " . implode(' ', $whereConditions) . "
                    ORDER BY cn.created_at DESC
                    LIMIT ?
                ");
                $stmt->execute($params);
                $consultations = $stmt->fetchAll();
                foreach ($consultations as $consultation) {
                    $patientName = ($consultation['first_name'] ?? '') . ' ' . ($consultation['last_name'] ?? '');
                    $preview = '';
                    if ($consultation['chief_complaint']) {
                        $preview = mb_substr($consultation['chief_complaint'], 0, 40) . '...';
                    } elseif ($consultation['diagnosis']) {
                        $preview = mb_substr($consultation['diagnosis'], 0, 40) . '...';
                    } elseif ($consultation['hx_present_illness']) {
                        $preview = mb_substr($consultation['hx_present_illness'], 0, 40) . '...';
                    }
                    $results[] = [
                        'id' => $consultation['id'],
                        'title' => $consultation['diagnosis'] ?: 'Consultation Note',
                        'subtitle' => ($preview ? $preview . ' • ' : '') .
                            ($patientName ? trim($patientName) : '') .
                            ($consultation['appointment_id'] ? ' • Appt #' . $consultation['appointment_id'] : '') .
                            ($consultation['created_at'] ? ' • ' . date('M d, Y', strtotime($consultation['created_at'])) : ''),
                        'type' => 'consultation',
                        'icon' => 'bi-file-earmark-medical',
                        'url' => $consultation['appointment_id'] ? '/doctor/appointments/' . $consultation['appointment_id'] : '/doctor/appointments'
                    ];
                }
            } catch (\Exception $e) {
                // Continue if error
            }

            return $this->jsonResponse([
                'results' => $results,
                'total' => count($results),
                'query' => $query
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => 'Search failed: ' . $e->getMessage()], 500);
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

            // Resolve clinic_id (secretary pinned; doctor/admin from input or fallback)
            try {
                $clinicId = $this->resolveClinicIdForRequest($data);
            } catch (\RuntimeException $e) {
                return $this->jsonResponse(['error' => $e->getMessage()], 400);
            }

            // Create daily balance record
            $stmt = $this->pdo->prepare("
                INSERT INTO daily_balances (amount, balance_type, clinic_id, description, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $createdAt = !empty($data['balance_date']) ? $data['balance_date'] : date('Y-m-d H:i:s');

            $stmt->execute([
                $data['amount'],
                $data['balance_type'],
                $clinicId,
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

            // The doctor's UI doesn't have a clinic picker yet (Phase 3); accept
            // an explicit `clinic_id` from POST when sent, otherwise pass NULL =
            // close every clinic for the day in one shot.
            $input = $_POST;
            $clinicId = null;
            if (!empty($input['clinic_id'])) {
                $clinicId = (int)$input['clinic_id'];
            }

            // Check if today is already closed (for this clinic if specified)
            if ($this->isDateClosed($today, $clinicId)) {
                return $this->jsonResponse(['error' => 'تم إغلاق اليوم مسبقاً'], 400);
            }

            // Create closure using existing method
            $closureId = $this->createDailyClosure($today, $user['id'], $clinicId);

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

            // Resolve clinic_id (secretary pinned; doctor/admin from input or fallback)
            try {
                $clinicId = $this->resolveClinicIdForRequest($data ?: []);
            } catch (\RuntimeException $e) {
                return $this->jsonResponse(['error' => $e->getMessage()], 400);
            }

            // Create expense record
            $stmt = $this->pdo->prepare("
                INSERT INTO expenses (amount, expense_name, category, clinic_id, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $createdAt = !empty($data['expense_date']) ? $data['expense_date'] : date('Y-m-d H:i:s');

            $stmt->execute([
                $data['amount'],
                $data['expense_name'],
                $data['category'],
                $clinicId,
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

            if ($denied = $this->assertFinanceRowInScope($expense)) return $denied;

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

            if ($denied = $this->assertFinanceRowInScope($expense)) return $denied;

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

            if ($denied = $this->assertFinanceRowInScope($payment)) return $denied;

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

            if ($denied = $this->assertFinanceRowInScope($payment)) return $denied;

            // Delete payment record. Removing the row is enough to "deduct"
            // the amount from the secretary's daily balance because every
            // aggregate query (getDailyBalance, getPaymentTypesSummary,
            // getFinancialTransactions) recomputes from current rows.
            $stmt = $this->pdo->prepare("DELETE FROM payments WHERE id = ?");
            $stmt->execute([$id]);

            // Audit trail on the patient's timeline so the secretary can see
            // the cancellation reflected in their balance.
            $actor = $this->auth->user();
            $actorName = $actor['name'] ?? ($actor['username'] ?? 'system');
            $this->createTimelineEvent(
                $payment['patient_id'],
                $payment['appointment_id'] ?? null,
                'Payment',
                "Payment cancelled by {$actorName}: {$payment['amount']} EGP"
            );

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

            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 10);
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

            // Secretary scope: pin to their clinic.
            $user = $this->auth->user();
            if (($user['role'] ?? null) === 'secretary' && !empty($user['clinic_id'])) {
                $whereConditions[] = "clinic_id = ?";
                $params[] = (int)$user['clinic_id'];
            }

            // Get transactions from different sources
            $transactions = [];

            // Get payments — `amount` here is the NET (gross minus discount,
            // exempt rows = 0) so summing the rows reconciles with the
            // "Total Received" card on the dashboard.
            if ($type === 'all' || $type === 'payment') {
                $paymentQuery = "
                    SELECT
                        'payment' as type,
                        p.id,
                        CASE WHEN p.is_exempt = 1 THEN 0
                             ELSE (p.amount - COALESCE(p.discount_amount, 0))
                        END as amount,
                        p.amount as gross_amount,
                        COALESCE(p.discount_amount, 0) as discount_amount,
                        p.is_exempt,
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
            usort($transactions, function ($a, $b) {
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
            usort($allTransactions, function ($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });

            // Filter by type if needed
            if ($type !== 'all') {
                $allTransactions = array_filter($allTransactions, function ($transaction) use ($type) {
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
            '01' => 'يناير',
            '02' => 'فبراير',
            '03' => 'مارس',
            '04' => 'أبريل',
            '05' => 'مايو',
            '06' => 'يونيو',
            '07' => 'يوليو',
            '08' => 'أغسطس',
            '09' => 'سبتمبر',
            '10' => 'أكتوبر',
            '11' => 'نوفمبر',
            '12' => 'ديسمبر'
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

            $user = $this->auth->user();
            $secClinicId = (($user['role'] ?? null) === 'secretary' && !empty($user['clinic_id']))
                ? (int)$user['clinic_id'] : null;
            $clinicFilter = $secClinicId ? ' AND clinic_id = ? ' : '';
            $extraParam   = $secClinicId ? [$secClinicId] : [];

            // Opening balance
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as opening_balance
                FROM daily_balances
                WHERE DATE(created_at) = ? AND balance_type = 'opening' {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $extraParam));
            $openingBalance = $stmt->fetchColumn();

            // Additional balance
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as additional_balance
                FROM daily_balances
                WHERE DATE(created_at) = ? AND balance_type = 'additional' {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $extraParam));
            $additionalBalance = $stmt->fetchColumn();

            // Withdrawals
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_withdrawals
                FROM daily_balances
                WHERE DATE(created_at) = ? AND balance_type = 'withdrawal' {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $extraParam));
            $totalWithdrawals = $stmt->fetchColumn();

            // Total received today — NET of discount, exempt rows count as 0.
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(
                    CASE WHEN is_exempt = 1 THEN 0
                         ELSE (amount - COALESCE(discount_amount, 0))
                    END
                ), 0) as total_received
                FROM payments
                WHERE DATE(created_at) = ? {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $extraParam));
            $totalReceived = $stmt->fetchColumn();

            // Total expenses today
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_expenses
                FROM expenses
                WHERE DATE(created_at) = ? {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $extraParam));
            $totalExpenses = $stmt->fetchColumn();

            // Calculate current balance: opening + additional + payments - withdrawals - expenses
            $currentBalance = $openingBalance + $additionalBalance + $totalReceived - $totalWithdrawals - $totalExpenses;

            // Transactions count (also clinic-scoped for secretary)
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as transactions_count
                FROM (
                    SELECT id FROM payments       WHERE DATE(created_at) = ? {$clinicFilter}
                    UNION ALL
                    SELECT id FROM expenses       WHERE DATE(created_at) = ? {$clinicFilter}
                    UNION ALL
                    SELECT id FROM daily_balances WHERE DATE(created_at) = ? {$clinicFilter}
                ) as all_transactions
            ");
            $params = $secClinicId
                ? [$today, $secClinicId, $today, $secClinicId, $today, $secClinicId]
                : [$today, $today, $today];
            $stmt->execute($params);
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
            $total = (int) ($statusData['total_appointments'] ?? 0);
            $completed = (int) ($statusData['completed'] ?? 0);
            $missed = (int) ($statusData['missed'] ?? 0);
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
                        'total_male' => (int) ($genderData['total_male'] ?? 0),
                        'total_female' => (int) ($genderData['total_female'] ?? 0)
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

            $year = (int) ($_GET['year'] ?? date('Y'));
            $month = (int) ($_GET['month'] ?? date('m'));

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

    /**
     * Today's payment totals grouped by normalized type, with NET amounts
     * (gross minus discount, exempt rows count as 0). Secretary sees only
     * their clinic; doctor/admin sees all clinics. Shape MUST match
     * SecretaryController::getPaymentTypesSummary().
     */
    private function getPaymentTypesSummary()
    {
        try {
            $today = date('Y-m-d');
            $params = [$today];
            $clinicFilter = '';
            $user = $this->auth->user();
            if (($user['role'] ?? null) === 'secretary' && !empty($user['clinic_id'])) {
                $clinicFilter = ' AND clinic_id = ? ';
                $params[] = (int)$user['clinic_id'];
            }

            $stmt = $this->pdo->prepare("
                SELECT
                    CASE
                        WHEN type = 'Booking'      THEN 'new_booking'
                        WHEN type = 'FollowUp'     THEN 'followup'
                        WHEN type = 'Consultation' THEN 'consultation'
                        WHEN type = 'Procedure'    THEN 'procedure'
                        ELSE 'other'
                    END as payment_type,
                    COALESCE(SUM(
                        CASE WHEN is_exempt = 1 THEN 0
                             ELSE (amount - COALESCE(discount_amount, 0))
                        END
                    ), 0) as total_amount
                FROM payments
                WHERE DATE(created_at) = ? {$clinicFilter}
                GROUP BY payment_type
            ");
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $summary = [
                'new_booking'  => 0,
                'followup'     => 0,
                'consultation' => 0,
                'procedure'    => 0,
                'other'        => 0,
            ];
            foreach ($results as $row) {
                $summary[$row['payment_type']] = $row['total_amount'];
            }

            return $summary;
        } catch (\Exception $e) {
            return [
                'new_booking'  => 0,
                'followup'     => 0,
                'consultation' => 0,
                'procedure'    => 0,
                'other'        => 0,
            ];
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
                if (
                    is_array($firstRow) && (
                        (isset($firstRow[0]) && strtoupper(trim((string) $firstRow[0])) === 'ID') ||
                        (isset($firstRow[1]) && strtoupper(trim((string) $firstRow[1])) === 'FIRSTNAME')
                    )
                ) {
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
                    $id = isset($row[0]) ? trim((string) $row[0]) : null;
                    $firstName = isset($row[1]) ? trim((string) $row[1]) : '';
                    $lastName = isset($row[2]) ? trim((string) $row[2]) : '';
                    $price = isset($row[3]) ? trim((string) $row[3]) : '';
                    $priceold = isset($row[4]) ? trim((string) $row[4]) : '';
                    $imageid = isset($row[5]) ? trim((string) $row[5]) : '';
                    $company = isset($row[6]) ? trim((string) $row[6]) : '';
                    $pharmacology = isset($row[7]) ? trim((string) $row[7]) : '';
                    $srde = isset($row[8]) ? trim((string) $row[8]) : '';
                    $gi = isset($row[9]) ? trim((string) $row[9]) : '';
                    $route = isset($row[10]) ? trim((string) $row[10]) : '';

                    // Skip if no ID or drug name
                    if (empty($id) && empty($firstName) && empty($lastName)) {
                        continue;
                    }

                    // Convert ID to integer if possible, otherwise skip
                    if (!is_numeric($id) || empty($id)) {
                        continue;
                    }
                    $id = (int) $id;

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
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Weather API exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get 5-day weather forecast
     */
    public function getWeatherForecast()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $lat = $_GET['lat'] ?? null;
            $lon = $_GET['lon'] ?? null;

            // Default to Kafr El Sheikh, Egypt if no coordinates provided
            if (!$lat || !$lon) {
                $lat = 31.1117;
                $lon = 30.9397;
            }

            // OpenWeatherMap API key
            $apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? '4d8fb5b93d4af21d66a2948710284366';

            // Fetch 5-day forecast from OpenWeatherMap
            $forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&units=metric&cnt=40&appid={$apiKey}";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $forecastUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to fetch weather forecast'
                ], 500);
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['list'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Invalid forecast data'
                ], 500);
            }

            // Group forecasts by day and get daily averages/max/min
            $dailyForecasts = [];
            $currentDate = null;
            $dayData = [];

            foreach ($data['list'] as $item) {
                $date = date('Y-m-d', $item['dt']);

                if ($currentDate !== $date) {
                    if ($currentDate !== null && !empty($dayData)) {
                        // Calculate daily averages
                        $dailyForecasts[] = [
                            'date' => $currentDate,
                            'temperature' => round(array_sum(array_column($dayData, 'temp')) / count($dayData)),
                            'tempMax' => round(max(array_column($dayData, 'temp'))),
                            'tempMin' => round(min(array_column($dayData, 'temp'))),
                            'humidity' => round(array_sum(array_column($dayData, 'humidity')) / count($dayData)),
                            'windSpeed' => round(array_sum(array_column($dayData, 'windSpeed')) / count($dayData)),
                            'condition' => ucfirst($dayData[0]['condition']),
                            'icon' => $dayData[0]['icon'],
                            'uvIndex' => round(array_sum(array_column($dayData, 'uvIndex')) / count($dayData)),
                            'clouds' => round(array_sum(array_column($dayData, 'clouds')) / count($dayData))
                        ];
                    }
                    $currentDate = $date;
                    $dayData = [];
                }

                $dayData[] = [
                    'temp' => $item['main']['temp'],
                    'humidity' => $item['main']['humidity'],
                    'windSpeed' => ($item['wind']['speed'] ?? 0) * 3.6, // Convert m/s to km/h
                    'condition' => $item['weather'][0]['description'] ?? 'clear',
                    'icon' => $item['weather'][0]['icon'] ?? '01d',
                    'uvIndex' => $this->estimateUVIndex($item),
                    'clouds' => $item['clouds']['all'] ?? 0
                ];
            }

            // Add last day
            if ($currentDate !== null && !empty($dayData)) {
                $dailyForecasts[] = [
                    'date' => $currentDate,
                    'temperature' => round(array_sum(array_column($dayData, 'temp')) / count($dayData)),
                    'tempMax' => round(max(array_column($dayData, 'temp'))),
                    'tempMin' => round(min(array_column($dayData, 'temp'))),
                    'humidity' => round(array_sum(array_column($dayData, 'humidity')) / count($dayData)),
                    'windSpeed' => round(array_sum(array_column($dayData, 'windSpeed')) / count($dayData)),
                    'condition' => ucfirst($dayData[0]['condition']),
                    'icon' => $dayData[0]['icon'],
                    'uvIndex' => round(array_sum(array_column($dayData, 'uvIndex')) / count($dayData)),
                    'clouds' => round(array_sum(array_column($dayData, 'clouds')) / count($dayData))
                ];
            }

            // Limit to 4 days
            $dailyForecasts = array_slice($dailyForecasts, 0, 4);

            return $this->jsonResponse([
                'success' => true,
                'forecast' => $dailyForecasts
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Weather forecast error'
            ], 500);
        }
    }

    /**
     * Get weather data in Arabic for the secretary dashboard
     * Uses OpenWeatherMap API with Arabic language support and caching
     */
    public function getWeatherArabic()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'غير مصرح'], 401);
            }

            $lat = $_GET['lat'] ?? null;
            $lon = $_GET['lon'] ?? null;

            // Cache file path
            $cacheDir = __DIR__ . '/../../storage/cache';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            $cacheKey = $lat && $lon ? md5("weather_ar_{$lat}_{$lon}") : 'weather_ar_default';
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

            // OpenWeatherMap API key
            $apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? '4d8fb5b93d4af21d66a2948710284366';

            // Fetch weather with Arabic language
            $weatherData = $this->fetchWeatherFromOpenWeatherMapArabic($lat, $lon, $apiKey);

            if (!$weatherData) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'فشل في جلب بيانات الطقس'
                ], 500);
            }

            // Save to cache
            file_put_contents($cacheFile, json_encode($weatherData));

            return $this->jsonResponse([
                'success' => true,
                'weather' => $weatherData
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'خطأ في واجهة الطقس'
            ], 500);
        }
    }

    /**
     * Get 5-day weather forecast in Arabic
     */
    public function getWeatherForecastArabic()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'غير مصرح'], 401);
            }

            $lat = $_GET['lat'] ?? null;
            $lon = $_GET['lon'] ?? null;

            // Default to Kafr El Sheikh, Egypt if no coordinates provided
            if (!$lat || !$lon) {
                $lat = 31.1117;
                $lon = 30.9397;
            }

            // OpenWeatherMap API key
            $apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? '4d8fb5b93d4af21d66a2948710284366';

            // Fetch 5-day forecast from OpenWeatherMap with Arabic language
            $forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&units=metric&cnt=40&lang=ar&appid={$apiKey}";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $forecastUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'فشل في جلب توقعات الطقس'
                ], 500);
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['list'])) {
                error_log("OpenWeatherMap Arabic Forecast API: Invalid response structure");
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'بيانات التوقعات غير صالحة'
                ], 500);
            }

            // Group forecasts by day and get daily averages/max/min
            $dailyForecasts = [];
            $currentDate = null;
            $dayData = [];

            foreach ($data['list'] as $item) {
                $date = date('Y-m-d', $item['dt']);

                if ($currentDate !== $date) {
                    if ($currentDate !== null && !empty($dayData)) {
                        // Calculate daily averages
                        $dailyForecasts[] = [
                            'date' => $currentDate,
                            'temperature' => round(array_sum(array_column($dayData, 'temp')) / count($dayData)),
                            'tempMax' => round(max(array_column($dayData, 'temp'))),
                            'tempMin' => round(min(array_column($dayData, 'temp'))),
                            'humidity' => round(array_sum(array_column($dayData, 'humidity')) / count($dayData)),
                            'windSpeed' => round(array_sum(array_column($dayData, 'windSpeed')) / count($dayData)),
                            'condition' => $dayData[0]['condition'], // Already in Arabic
                            'icon' => $dayData[0]['icon'],
                            'uvIndex' => round(array_sum(array_column($dayData, 'uvIndex')) / count($dayData)),
                            'clouds' => round(array_sum(array_column($dayData, 'clouds')) / count($dayData))
                        ];
                    }
                    $currentDate = $date;
                    $dayData = [];
                }

                $dayData[] = [
                    'temp' => $item['main']['temp'],
                    'humidity' => $item['main']['humidity'],
                    'windSpeed' => ($item['wind']['speed'] ?? 0) * 3.6, // Convert m/s to km/h
                    'condition' => $item['weather'][0]['description'] ?? 'صافي',
                    'icon' => $item['weather'][0]['icon'] ?? '01d',
                    'uvIndex' => $this->estimateUVIndex($item),
                    'clouds' => $item['clouds']['all'] ?? 0
                ];
            }

            // Add last day
            if ($currentDate !== null && !empty($dayData)) {
                $dailyForecasts[] = [
                    'date' => $currentDate,
                    'temperature' => round(array_sum(array_column($dayData, 'temp')) / count($dayData)),
                    'tempMax' => round(max(array_column($dayData, 'temp'))),
                    'tempMin' => round(min(array_column($dayData, 'temp'))),
                    'humidity' => round(array_sum(array_column($dayData, 'humidity')) / count($dayData)),
                    'windSpeed' => round(array_sum(array_column($dayData, 'windSpeed')) / count($dayData)),
                    'condition' => $dayData[0]['condition'],
                    'icon' => $dayData[0]['icon'],
                    'uvIndex' => round(array_sum(array_column($dayData, 'uvIndex')) / count($dayData)),
                    'clouds' => round(array_sum(array_column($dayData, 'clouds')) / count($dayData))
                ];
            }

            // Limit to 4 days
            $dailyForecasts = array_slice($dailyForecasts, 0, 4);

            return $this->jsonResponse([
                'success' => true,
                'forecast' => $dailyForecasts
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'خطأ في توقعات الطقس'
            ], 500);
        }
    }

    /**
     * Fetch weather from OpenWeatherMap API with Arabic language
     */
    private function fetchWeatherFromOpenWeatherMapArabic($lat, $lon, $apiKey)
    {
        try {
            // Add lang=ar for Arabic language support
            $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=ar&appid={$apiKey}";

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
                return null;
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['main'])) {
                return null;
            }

            // Map location name to Arabic if available
            $locationName = $data['name'] ?? 'غير معروف';

            // Arabic location names for common Egyptian cities
            $arabicLocations = [
                'Kafr el-Sheikh' => 'كفر الشيخ',
                'Kafr El Sheikh' => 'كفر الشيخ',
                'Cairo' => 'القاهرة',
                'Alexandria' => 'الإسكندرية',
                'Giza' => 'الجيزة',
                'Tanta' => 'طنطا',
                'Mansoura' => 'المنصورة',
                'Damietta' => 'دمياط',
                'Port Said' => 'بورسعيد',
                'Suez' => 'السويس',
                'Ismailia' => 'الإسماعيلية',
                'Aswan' => 'أسوان',
                'Luxor' => 'الأقصر',
                'Assiut' => 'أسيوط',
                'Fayoum' => 'الفيوم',
                'Zagazig' => 'الزقازيق',
                'Shibin El Kom' => 'شبين الكوم',
                'Banha' => 'بنها',
                'Damanhur' => 'دمنهور',
                'Mahalla' => 'المحلة الكبرى'
            ];

            if (isset($arabicLocations[$locationName])) {
                $locationName = $arabicLocations[$locationName];
            }

            $weatherData = [
                'temperature' => round($data['main']['temp'] ?? 20),
                'humidity' => $data['main']['humidity'] ?? 50,
                'condition' => $data['weather'][0]['description'] ?? 'صافي', // Already in Arabic from API
                'icon' => $data['weather'][0]['icon'] ?? '01d',
                'windSpeed' => round(($data['wind']['speed'] ?? 0) * 3.6), // Convert m/s to km/h
                'location' => $locationName,
                'country' => $data['sys']['country'] ?? '',
                'uvIndex' => $this->estimateUVIndex($data),
                'feelsLike' => round($data['main']['feels_like'] ?? $data['main']['temp']),
                'pressure' => $data['main']['pressure'] ?? 1013,
                'visibility' => round(($data['visibility'] ?? 10000) / 1000), // Convert to km
                'clouds' => $data['clouds']['all'] ?? 0,
                'timestamp' => time()
            ];

            return $weatherData;

        } catch (\Exception $e) {
            return null;
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
                return null;
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['current'])) {
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

            return $weatherData;

        } catch (\Exception $e) {
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
                return null;
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['main'])) {
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

            return $weatherData;

        } catch (\Exception $e) {
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
        if ($code == 0)
            return $isDay ? '01d' : '01n';
        if ($code <= 2)
            return $isDay ? '02d' : '02n';
        if ($code == 3)
            return '04d';
        if ($code >= 45 && $code <= 48)
            return '50d';
        if ($code >= 51 && $code <= 67)
            return '09d';
        if ($code >= 71 && $code <= 77)
            return '13d';
        if ($code >= 80 && $code <= 82)
            return '09d';
        if ($code >= 85 && $code <= 86)
            return '13d';
        if ($code >= 95)
            return '11d';
        return '01d';
    }

    /**
     * Estimate cloud cover from weather code
     */
    private function estimateCloudsFromWeatherCode($code)
    {
        if ($code == 0)
            return 0;
        if ($code <= 2)
            return 25;
        if ($code == 3)
            return 100;
        if ($code >= 45 && $code <= 48)
            return 50;
        if ($code >= 51 && $code <= 67)
            return 80;
        if ($code >= 71 && $code <= 77)
            return 90;
        if ($code >= 80 && $code <= 82)
            return 85;
        if ($code >= 85 && $code <= 86)
            return 95;
        if ($code >= 95)
            return 100;
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
        }

        // No fallback - return coordinates only if geocoding fails
        return sprintf('Location (%.2f, %.2f)', $lat, $lon);
    }

    /**
     * Estimate UV index based on weather conditions
     */
    private function estimateUVIndex($weatherData)
    {
        $clouds = $weatherData['clouds']['all'] ?? 0;
        $hour = (int) date('H');

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

    /**
     * Calculate IOL Power using all three formulas
     * 
     * POST /api/iol/calculate
     * 
     * Required parameters:
     * - axial_length: Axial length in mm (15.0-35.0)
     * - k1: K1 keratometry in diopters (35.0-50.0)
     * - k2: K2 keratometry in diopters (35.0-50.0)
     * - a_constant: A-constant (110.0-130.0)
     * 
     * Optional parameters:
     * - target_refraction: Target refraction in diopters (-5.0 to +5.0)
     * - acd: Anterior Chamber Depth in mm (2.0-5.0)
     */
    public function calculateIOL()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get and sanitize input data
            $data = [
                'axial_length' => $_POST['axial_length'] ?? $_GET['axial_length'] ?? null,
                'k1' => $_POST['k1'] ?? $_GET['k1'] ?? null,
                'k2' => $_POST['k2'] ?? $_GET['k2'] ?? null,
                'a_constant' => $_POST['a_constant'] ?? $_GET['a_constant'] ?? null,
                'target_refraction' => $_POST['target_refraction'] ?? $_GET['target_refraction'] ?? 0.0,
                'acd' => $_POST['acd'] ?? $_GET['acd'] ?? null
            ];

            // Initialize calculator service
            $calculatorService = new IOLCalculatorService();

            // Calculate using all formulas
            $results = $calculatorService->calculateAll($data);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("IOL Calculator Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze IOP Trend for a patient
     * 
     * GET /api/iop/analyze?patient_id={id}
     * 
     * Retrieves all IOP readings for a patient and performs trend analysis
     */
    public function analyzeIOPTrend()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get patient_id from query parameters
            $patientId = $_GET['patient_id'] ?? $_POST['patient_id'] ?? null;

            if (!$patientId || !is_numeric($patientId)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Patient ID is required'
                ], 400);
            }

            // Retrieve all IOP readings for the patient
            $stmt = $this->pdo->prepare("
                SELECT 
                    cn.IOP_right, 
                    cn.IOP_left, 
                    a.date as measurement_date,
                    a.id as appointment_id,
                    cn.medication
                FROM consultation_notes cn
                JOIN appointments a ON cn.appointment_id = a.id
                WHERE a.patient_id = ?
                    AND (cn.IOP_right IS NOT NULL OR cn.IOP_left IS NOT NULL)
                ORDER BY a.date ASC
            ");
            $stmt->execute([$patientId]);
            $consultations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($consultations)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'No IOP readings found for this patient'
                ], 404);
            }

            // Prepare readings array for analyzer
            $readings = [];
            foreach ($consultations as $consultation) {
                // Process OD (right eye)
                if (!empty($consultation['IOP_right'])) {
                    $iopRight = $this->parseIOPValue($consultation['IOP_right']);
                    if ($iopRight !== null && $iopRight >= 5.0 && $iopRight <= 60.0) {
                        $readings[] = [
                            'eye' => 'OD',
                            'iop' => $iopRight,
                            'date' => $consultation['measurement_date'],
                            'appointment_id' => $consultation['appointment_id'],
                            'medication' => $consultation['medication'] ?? null
                        ];
                    }
                }

                // Process OS (left eye)
                if (!empty($consultation['IOP_left'])) {
                    $iopLeft = $this->parseIOPValue($consultation['IOP_left']);
                    if ($iopLeft !== null && $iopLeft >= 5.0 && $iopLeft <= 60.0) {
                        $readings[] = [
                            'eye' => 'OS',
                            'iop' => $iopLeft,
                            'date' => $consultation['measurement_date'],
                            'appointment_id' => $consultation['appointment_id'],
                            'medication' => $consultation['medication'] ?? null
                        ];
                    }
                }
            }

            if (empty($readings)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'No valid IOP readings found (values must be numeric and between 5-60 mmHg)'
                ], 404);
            }

            // Initialize analyzer service
            $analyzerService = new IOPTrendAnalyzerService();

            // Perform analysis
            $results = $analyzerService->analyze($readings);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("IOP Trend Analyzer Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse IOP value from string (handles numeric values and +/- notation)
     * 
     * @param mixed $value IOP value (can be string like "15.0", "+2", "-1" or numeric)
     * @return float|null Parsed IOP value or null if invalid
     */
    private function parseIOPValue($value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
            // Handle +/- notation (relative values)
            // For trend analysis, we need absolute values, so skip relative values
            if (preg_match('/^[+-]\d+\.?\d*$/', $value)) {
                // Relative value - cannot use for trend analysis
                return null;
            }

            // Try to parse as numeric
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    /**
     * Calculate Pediatric IOL Undercorrection
     * 
     * POST/GET /api/pediatric-iol/calculate
     * 
     * Calculates appropriate IOL power undercorrection for pediatric patients
     */
    public function calculatePediatricIOL()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get input data
            $ageValue = $_POST['age_value'] ?? $_GET['age_value'] ?? null;
            $ageUnit = $_POST['age_unit'] ?? $_GET['age_unit'] ?? null;
            $calculatedIOLPower = $_POST['calculated_iol_power'] ?? $_GET['calculated_iol_power'] ?? null;

            // Convert empty strings to null
            $ageValue = ($ageValue === '' || $ageValue === null) ? null : $ageValue;
            $ageUnit = ($ageUnit === '' || $ageUnit === null) ? null : $ageUnit;
            $calculatedIOLPower = ($calculatedIOLPower === '' || $calculatedIOLPower === null) ? null : $calculatedIOLPower;

            $data = [
                'age_value' => $ageValue,
                'age_unit' => $ageUnit,
                'calculated_iol_power' => $calculatedIOLPower
            ];

            // Initialize calculator service
            $calculatorService = new PediatricIOLUndercorrectionService();

            // Perform calculation
            $results = $calculatorService->calculate($data);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Pediatric IOL Calculator Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate Corneal Astigmatism
     * 
     * POST/GET /api/astigmatism/calculate
     * 
     * Calculates corneal astigmatism using vector analysis and provides surgical recommendations
     */
    public function calculateCornealAstigmatism()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get input data
            $k1 = $_POST['k1'] ?? $_GET['k1'] ?? null;
            $k1Axis = $_POST['k1_axis'] ?? $_GET['k1_axis'] ?? null;
            $k2 = $_POST['k2'] ?? $_GET['k2'] ?? null;
            $k2Axis = $_POST['k2_axis'] ?? $_GET['k2_axis'] ?? null;
            $sia = $_POST['sia'] ?? $_GET['sia'] ?? null;
            $siaAxis = $_POST['sia_axis'] ?? $_GET['sia_axis'] ?? null;

            // Convert empty strings to null
            $k1 = ($k1 === '' || $k1 === null) ? null : $k1;
            $k1Axis = ($k1Axis === '' || $k1Axis === null) ? null : $k1Axis;
            $k2 = ($k2 === '' || $k2 === null) ? null : $k2;
            $k2Axis = ($k2Axis === '' || $k2Axis === null) ? null : $k2Axis;
            $sia = ($sia === '' || $sia === null) ? null : $sia;
            $siaAxis = ($siaAxis === '' || $siaAxis === null) ? null : $siaAxis;

            $data = [
                'k1' => $k1,
                'k1_axis' => $k1Axis,
                'k2' => $k2,
                'k2_axis' => $k2Axis,
                'sia' => $sia,
                'sia_axis' => $siaAxis
            ];

            // Initialize calculator service
            $calculatorService = new CornealAstigmatismService();

            // Perform calculation
            $results = $calculatorService->calculate($data);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Corneal Astigmatism Calculator Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate Target IOP
     * 
     * POST/GET /api/target-iop/calculate
     * 
     * Input:
     *   - baseline_iop (float): Baseline IOP in mmHg (5-60)
     *   - glaucoma_stage (string): "Early", "Moderate", or "Advanced"
     *   - high_life_expectancy (bool|string): Optional, whether patient has high life expectancy
     *   - risk_factors (array|string): Optional, array of risk factors or comma-separated string
     * 
     * @return \Psr\Http\Message\ResponseInterface JSON response with calculation results
     */
    public function calculateTargetIOP()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get input data
            $baselineIOP = $_POST['baseline_iop'] ?? $_GET['baseline_iop'] ?? null;
            $glaucomaStage = $_POST['glaucoma_stage'] ?? $_GET['glaucoma_stage'] ?? null;
            $highLifeExpectancy = $_POST['high_life_expectancy'] ?? $_GET['high_life_expectancy'] ?? null;
            $riskFactors = $_POST['risk_factors'] ?? $_GET['risk_factors'] ?? null;

            // Convert empty strings to null
            $baselineIOP = ($baselineIOP === '' || $baselineIOP === null) ? null : $baselineIOP;
            $glaucomaStage = ($glaucomaStage === '' || $glaucomaStage === null) ? null : $glaucomaStage;
            $highLifeExpectancy = ($highLifeExpectancy === '' || $highLifeExpectancy === null) ? null : $highLifeExpectancy;
            $riskFactors = ($riskFactors === '' || $riskFactors === null) ? null : $riskFactors;

            // Handle risk_factors array from POST (when sent as array)
            if (isset($_POST['risk_factors']) && is_array($_POST['risk_factors'])) {
                $riskFactors = $_POST['risk_factors'];
            }

            $data = [
                'baseline_iop' => $baselineIOP,
                'glaucoma_stage' => $glaucomaStage,
                'high_life_expectancy' => $highLifeExpectancy,
                'risk_factors' => $riskFactors
            ];

            // Initialize calculator service
            $calculatorService = new TargetIOPCalculatorService();

            // Perform calculation
            $results = $calculatorService->calculate($data);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Target IOP Calculator Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate Refraction Consistency
     * 
     * POST/GET /api/refraction/consistency
     * 
     * Compares auto-refraction with subjective refraction to determine clinical consistency
     */
    public function calculateRefractionConsistency()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get input data
            $autoSphere = $_POST['auto_sphere'] ?? $_GET['auto_sphere'] ?? null;
            $autoCylinder = $_POST['auto_cylinder'] ?? $_GET['auto_cylinder'] ?? null;
            $autoAxis = $_POST['auto_axis'] ?? $_GET['auto_axis'] ?? null;
            $subjectiveSphere = $_POST['subjective_sphere'] ?? $_GET['subjective_sphere'] ?? null;
            $subjectiveCylinder = $_POST['subjective_cylinder'] ?? $_GET['subjective_cylinder'] ?? null;
            $subjectiveAxis = $_POST['subjective_axis'] ?? $_GET['subjective_axis'] ?? null;

            // Convert empty strings to null
            $autoSphere = ($autoSphere === '' || $autoSphere === null) ? null : $autoSphere;
            $autoCylinder = ($autoCylinder === '' || $autoCylinder === null) ? null : $autoCylinder;
            $autoAxis = ($autoAxis === '' || $autoAxis === null) ? null : $autoAxis;
            $subjectiveSphere = ($subjectiveSphere === '' || $subjectiveSphere === null) ? null : $subjectiveSphere;
            $subjectiveCylinder = ($subjectiveCylinder === '' || $subjectiveCylinder === null) ? null : $subjectiveCylinder;
            $subjectiveAxis = ($subjectiveAxis === '' || $subjectiveAxis === null) ? null : $subjectiveAxis;

            $data = [
                'auto_sphere' => $autoSphere,
                'auto_cylinder' => $autoCylinder,
                'auto_axis' => $autoAxis,
                'subjective_sphere' => $subjectiveSphere,
                'subjective_cylinder' => $subjectiveCylinder,
                'subjective_axis' => $subjectiveAxis
            ];

            // Initialize calculator service
            $calculatorService = new RefractionConsistencyService();

            // Perform calculation
            $results = $calculatorService->calculate($data);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Refraction Consistency Checker Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate Visual Acuity Progress
     * 
     * POST/GET /api/visual-acuity/progress
     * 
     * Calculates visual acuity progress over time from multiple visits
     */
    public function calculateVisualAcuityProgress()
    {
        // Clear any previous output
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get visits data
            $visits = null;

            // Handle POST data (can be JSON or form data)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                // Check if Content-Type contains 'application/json'
                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    if ($json) {
                        $postData = json_decode($json, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($postData['visits'])) {
                            $visits = $postData['visits'];
                        }
                    }
                } else {
                    // Form data - handle array format visits[0][eye], visits[0][va_format], etc.
                    if (isset($_POST['visits']) && is_array($_POST['visits'])) {
                        // Normalize array structure
                        $visits = [];
                        foreach ($_POST['visits'] as $visit) {
                            if (is_array($visit) && isset($visit['eye']) && isset($visit['va_format']) && isset($visit['va_value']) && isset($visit['date'])) {
                                $visits[] = [
                                    'eye' => trim($visit['eye']),
                                    'va_format' => trim($visit['va_format']),
                                    'va_value' => trim($visit['va_value']),
                                    'date' => trim($visit['date'])
                                ];
                            }
                        }
                    } elseif (isset($_POST['visits']) && is_string($_POST['visits'])) {
                        // JSON string
                        $decoded = json_decode($_POST['visits'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $visits = $decoded;
                        }
                    }
                }
            } else {
                // GET request - visits as JSON string
                if (isset($_GET['visits'])) {
                    $decoded = json_decode($_GET['visits'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $visits = $decoded;
                    }
                }
            }

            // Validate visits data
            if ($visits === null || !is_array($visits) || empty($visits)) {
                $debugInfo = [
                    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'not set',
                    'visits_type' => gettype($visits),
                    'visits_value' => $visits,
                    'raw_post' => $_POST,
                    'raw_input' => file_get_contents('php://input')
                ];
                error_log("Visual Acuity Progress - Invalid visits data: " . json_encode($debugInfo));
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Visits data is required and must be a non-empty array',
                    'debug' => $debugInfo
                ], 400);
            }

            // Validate and normalize visits array
            $normalizedVisits = [];
            foreach ($visits as $index => $visit) {
                if (!is_array($visit)) {
                    error_log("Visual Acuity Progress - Visit at index $index is not an array: " . print_r($visit, true));
                    continue;
                }

                // Check required fields
                $eye = isset($visit['eye']) ? trim($visit['eye']) : '';
                $vaFormat = isset($visit['va_format']) ? trim($visit['va_format']) : '';
                $vaValue = isset($visit['va_value']) ? trim($visit['va_value']) : '';
                $date = isset($visit['date']) ? trim($visit['date']) : '';

                if (empty($eye) || empty($vaFormat) || empty($vaValue) || empty($date)) {
                    error_log("Visual Acuity Progress - Visit at index $index is missing required fields. Eye: '$eye', Format: '$vaFormat', Value: '$vaValue', Date: '$date'");
                    continue;
                }

                // Validate eye value
                $eyeUpper = strtoupper($eye);
                if (!in_array($eyeUpper, ['OD', 'OS'])) {
                    error_log("Visual Acuity Progress - Visit at index $index has invalid eye value: '$eye'");
                    continue;
                }

                // Validate format
                $vaFormatLower = strtolower($vaFormat);
                if (!in_array($vaFormatLower, ['snellen', 'logmar'])) {
                    error_log("Visual Acuity Progress - Visit at index $index has invalid format: '$vaFormat'");
                    continue;
                }

                // Validate date format
                $dateTimestamp = strtotime($date);
                if ($dateTimestamp === false) {
                    error_log("Visual Acuity Progress - Visit at index $index has invalid date format: '$date'");
                    continue;
                }

                $normalizedVisits[] = [
                    'eye' => $eyeUpper,
                    'va_format' => $vaFormatLower,
                    'va_value' => $vaValue,
                    'date' => date('Y-m-d', $dateTimestamp)
                ];
            }

            if (empty($normalizedVisits)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'No valid visits found. Each visit must have eye (OD/OS), va_format (snellen/logmar), va_value, and date (Y-m-d format) fields.'
                ], 400);
            }

            if (count($normalizedVisits) < 2) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'At least 2 valid visits are required for progress calculation'
                ], 400);
            }

            $data = [
                'visits' => $normalizedVisits
            ];

            // Initialize calculator service
            try {
                $calculatorService = new VisualAcuityProgressService();

                // Perform calculation
                $results = $calculatorService->calculate($data);

                // Ensure result is an array
                if (!is_array($results)) {
                    throw new \Exception('Service returned invalid result type: ' . gettype($results));
                }

                return $this->jsonResponse($results);
            } catch (\Exception $serviceException) {
                error_log("Visual Acuity Progress Service Error: " . $serviceException->getMessage());
                error_log("Service Stack trace: " . $serviceException->getTraceAsString());
                throw $serviceException; // Re-throw to be caught by outer catch
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();
            error_log("Visual Acuity Progress Calculator Error: " . $errorMessage);
            error_log("Stack trace: " . $errorTrace);

            // Log request data for debugging
            error_log("Request data: " . json_encode([
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                'post_data' => $_POST,
                'input_data' => file_get_contents('php://input')
            ]));

            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $errorMessage,
                'error_type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Calculate OSDI (Ocular Surface Disease Index) score
     * POST/GET /api/osdi/calculate
     */
    public function calculateOSDI()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            // Ensure OSDI table exists
            $this->ensureOSDITableExists();

            // Get input data
            $questions = [];
            $measurementDate = $_POST['measurement_date'] ?? $_GET['measurement_date'] ?? date('Y-m-d');
            $patientId = $_POST['patient_id'] ?? $_GET['patient_id'] ?? null;
            $appointmentId = $_POST['appointment_id'] ?? $_GET['appointment_id'] ?? null;

            // Handle POST data (can be JSON or form data)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    if ($json) {
                        $postData = json_decode($json, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $questions = $postData['questions'] ?? [];
                            $measurementDate = $postData['measurement_date'] ?? $measurementDate;
                            $patientId = $postData['patient_id'] ?? $patientId;
                            $appointmentId = $postData['appointment_id'] ?? $appointmentId;
                        }
                    }
                } else {
                    // Form data
                    for ($i = 1; $i <= 12; $i++) {
                        $key = "question_{$i}";
                        if (isset($_POST[$key]) && $_POST[$key] !== '') {
                            $questions[$i] = (int) $_POST[$key];
                        }
                    }
                }
            } else {
                // GET request
                for ($i = 1; $i <= 12; $i++) {
                    $key = "question_{$i}";
                    if (isset($_GET[$key]) && $_GET[$key] !== '') {
                        $questions[$i] = (int) $_GET[$key];
                    }
                }
            }

            // Get previous score for comparison if patient_id provided
            $previousScore = null;
            $previousDate = null;
            if ($patientId) {
                $prevStmt = $this->pdo->prepare("
                    SELECT osdi_score, measurement_date 
                    FROM osdi_results 
                    WHERE patient_id = ? 
                    ORDER BY measurement_date DESC, created_at DESC 
                    LIMIT 1
                ");
                $prevStmt->execute([$patientId]);
                $previous = $prevStmt->fetch();
                if ($previous) {
                    $previousScore = (float) $previous['osdi_score'];
                    $previousDate = $previous['measurement_date'];
                }
            }

            $data = [
                'questions' => $questions,
                'measurement_date' => $measurementDate,
                'previous_score' => $previousScore,
                'previous_date' => $previousDate
            ];

            // Initialize calculator service
            $calculatorService = new OSDICalculatorService();
            $results = $calculatorService->calculate($data);

            if ($results['success'] && $patientId) {
                // Save to database
                $stmt = $this->pdo->prepare("
                    INSERT INTO osdi_results (
                        patient_id, appointment_id, measurement_date, osdi_score, severity,
                        question_1, question_2, question_3, question_4, question_5, question_6,
                        question_7, question_8, question_9, question_10, question_11, question_12,
                        created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $patientId,
                    $appointmentId ?: null,
                    $measurementDate,
                    $results['osdi_score'],
                    $results['severity'],
                    $questions[1] ?? null,
                    $questions[2] ?? null,
                    $questions[3] ?? null,
                    $questions[4] ?? null,
                    $questions[5] ?? null,
                    $questions[6] ?? null,
                    $questions[7] ?? null,
                    $questions[8] ?? null,
                    $questions[9] ?? null,
                    $questions[10] ?? null,
                    $questions[11] ?? null,
                    $questions[12] ?? null,
                    $user['id']
                ]);
            }

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("OSDI Calculator Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient OSDI history for follow-up comparison
     * GET /api/patients/{patientId}/osdi/history
     */
    public function getPatientOSDIHistory($patientId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['success' => false, 'error' => 'Permission denied'], 403);
            }

            // Ensure OSDI table exists
            $this->ensureOSDITableExists();

            $stmt = $this->pdo->prepare("
                SELECT id, measurement_date, osdi_score, severity, created_at
                FROM osdi_results 
                WHERE patient_id = ? 
                ORDER BY measurement_date DESC, created_at DESC
            ");
            $stmt->execute([$patientId]);
            $history = $stmt->fetchAll();

            return $this->jsonResponse([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            error_log("Get OSDI History Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to retrieve OSDI history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate Pachymetry-Adjusted IOP
     * POST/GET /api/pachymetry-adjusted-iop/calculate
     */
    public function calculatePachymetryAdjustedIOP()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get input data
            $measuredIOP = $_POST['measured_iop'] ?? $_GET['measured_iop'] ?? null;
            $cct = $_POST['cct'] ?? $_GET['cct'] ?? null;

            // Handle POST data (can be JSON or form data)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    if ($json) {
                        $postData = json_decode($json, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $measuredIOP = $postData['measured_iop'] ?? $measuredIOP;
                            $cct = $postData['cct'] ?? $cct;
                        }
                    }
                }
            }

            // Convert empty strings to null
            $measuredIOP = ($measuredIOP === '' || $measuredIOP === null) ? null : $measuredIOP;
            $cct = ($cct === '' || $cct === null) ? null : $cct;

            $data = [
                'measured_iop' => $measuredIOP,
                'cct' => $cct
            ];

            // Initialize calculator service
            $calculatorService = new PachymetryAdjustedIOPCalculatorService();
            $results = $calculatorService->calculate($data);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Pachymetry-Adjusted IOP Calculator Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure OSDI results table exists
     * Creates the table if it doesn't exist
     */
    private function ensureOSDITableExists()
    {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'osdi_results'");
            if ($stmt->rowCount() > 0) {
                return; // Table exists
            }

            // Create table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS osdi_results (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    patient_id INT NOT NULL,
                    appointment_id INT NULL,
                    measurement_date DATE NOT NULL,
                    osdi_score DECIMAL(5,2) NOT NULL,
                    severity VARCHAR(20) NOT NULL,
                    question_1 INT NULL,
                    question_2 INT NULL,
                    question_3 INT NULL,
                    question_4 INT NULL,
                    question_5 INT NULL,
                    question_6 INT NULL,
                    question_7 INT NULL,
                    question_8 INT NULL,
                    question_9 INT NULL,
                    question_10 INT NULL,
                    question_11 INT NULL,
                    question_12 INT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_patient_id (patient_id),
                    INDEX idx_appointment_id (appointment_id),
                    INDEX idx_measurement_date (measurement_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            error_log("Error creating OSDI table: " . $e->getMessage());
            // Don't throw - allow calculation to proceed even if table creation fails
        }
    }

    /**
     * Estimate Diabetic Retinopathy Risk
     * POST/GET /api/diabetic-retinopathy/risk-estimate
     */
    public function estimateDiabeticRetinopathyRisk()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Get input data
            $durationYears = $_POST['duration_years'] ?? $_GET['duration_years'] ?? null;
            $hba1c = $_POST['hba1c'] ?? $_GET['hba1c'] ?? null;
            $systolicBP = $_POST['systolic_bp'] ?? $_GET['systolic_bp'] ?? null;
            $diastolicBP = $_POST['diastolic_bp'] ?? $_GET['diastolic_bp'] ?? null;
            $fundusGrade = $_POST['fundus_grade'] ?? $_GET['fundus_grade'] ?? null;

            // Handle POST data (can be JSON or form data)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    if ($json) {
                        $postData = json_decode($json, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $durationYears = $postData['duration_years'] ?? $durationYears;
                            $hba1c = $postData['hba1c'] ?? $hba1c;
                            $systolicBP = $postData['systolic_bp'] ?? $systolicBP;
                            $diastolicBP = $postData['diastolic_bp'] ?? $diastolicBP;
                            $fundusGrade = $postData['fundus_grade'] ?? $fundusGrade;
                        }
                    }
                }
            }

            // Convert empty strings to null
            $durationYears = ($durationYears === '' || $durationYears === null) ? null : $durationYears;
            $hba1c = ($hba1c === '' || $hba1c === null) ? null : $hba1c;
            $systolicBP = ($systolicBP === '' || $systolicBP === null) ? null : $systolicBP;
            $diastolicBP = ($diastolicBP === '' || $diastolicBP === null) ? null : $diastolicBP;
            $fundusGrade = ($fundusGrade === '' || $fundusGrade === null) ? null : $fundusGrade;

            $data = [
                'duration_years' => $durationYears,
                'hba1c' => $hba1c,
                'systolic_bp' => $systolicBP,
                'diastolic_bp' => $diastolicBP,
                'fundus_grade' => $fundusGrade
            ];

            // Initialize analyzer service
            $analyzerService = new DiabeticRetinopathyRiskEstimatorService();
            $results = $analyzerService->analyze($data);

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Diabetic Retinopathy Risk Estimator Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze Macular Thickness Trend
     * POST/GET /api/macular-thickness/trend
     */
    public function analyzeMacularThicknessTrend()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            // Ensure macular thickness table exists
            $this->ensureMacularThicknessTableExists();

            // Get visits data
            $visits = null;
            $patientId = $_POST['patient_id'] ?? $_GET['patient_id'] ?? null;
            $appointmentId = $_POST['appointment_id'] ?? $_GET['appointment_id'] ?? null;

            // Handle POST data (can be JSON or form data)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    if ($json) {
                        $postData = json_decode($json, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($postData['visits'])) {
                            $visits = $postData['visits'];
                            $patientId = $postData['patient_id'] ?? $patientId;
                            $appointmentId = $postData['appointment_id'] ?? $appointmentId;
                        }
                    }
                } else {
                    // Form data - handle array format
                    if (isset($_POST['visits']) && is_array($_POST['visits'])) {
                        $visits = $_POST['visits'];
                    }
                }
            } else {
                // GET request
                if (isset($_GET['visits']) && is_array($_GET['visits'])) {
                    $visits = $_GET['visits'];
                }
            }

            if (!$visits || !is_array($visits)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Visits data is required and must be an array'
                ], 400);
            }

            // Normalize visits data
            $normalizedVisits = [];
            foreach ($visits as $index => $visit) {
                if (!is_array($visit)) {
                    continue;
                }

                $eye = isset($visit['eye']) ? trim($visit['eye']) : '';
                $thickness = isset($visit['central_macular_thickness']) ? trim($visit['central_macular_thickness']) : '';
                $date = isset($visit['date']) ? trim($visit['date']) : '';

                if (empty($eye) || empty($thickness) || empty($date)) {
                    continue;
                }

                $eyeUpper = strtoupper($eye);
                if (!in_array($eyeUpper, ['OD', 'OS'])) {
                    continue;
                }

                $dateTimestamp = strtotime($date);
                if ($dateTimestamp === false) {
                    continue;
                }

                $normalizedVisits[] = [
                    'eye' => $eyeUpper,
                    'central_macular_thickness' => $thickness,
                    'date' => date('Y-m-d', $dateTimestamp)
                ];
            }

            if (empty($normalizedVisits)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'No valid visits found. Each visit must have eye (OD/OS), central_macular_thickness, and date (Y-m-d format) fields.'
                ], 400);
            }

            if (count($normalizedVisits) < 2) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'At least 2 valid visits are required for trend analysis'
                ], 400);
            }

            $data = [
                'visits' => $normalizedVisits
            ];

            // Initialize analyzer service
            $analyzerService = new MacularThicknessTrendAnalyzerService();
            $results = $analyzerService->analyze($data);

            // Save to database if patientId provided
            if ($results['success'] && $patientId) {
                foreach ($normalizedVisits as $visit) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO macular_thickness_results (
                            patient_id, appointment_id, measurement_date, 
                            central_macular_thickness, eye, created_by
                        ) VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            central_macular_thickness = VALUES(central_macular_thickness),
                            updated_at = CURRENT_TIMESTAMP
                    ");

                    $stmt->execute([
                        $patientId,
                        $appointmentId ?: null,
                        $visit['date'],
                        $visit['central_macular_thickness'],
                        $visit['eye'],
                        $user['id']
                    ]);
                }
            }

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Macular Thickness Trend Analyzer Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient macular thickness history
     * GET /api/patients/{patientId}/macular-thickness/history
     */
    public function getPatientMacularThicknessHistory($patientId)
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['success' => false, 'error' => 'Permission denied'], 403);
            }

            // Ensure macular thickness table exists
            $this->ensureMacularThicknessTableExists();

            $stmt = $this->pdo->prepare("
                SELECT id, measurement_date, central_macular_thickness, eye, created_at
                FROM macular_thickness_results 
                WHERE patient_id = ? 
                ORDER BY measurement_date DESC, created_at DESC
            ");
            $stmt->execute([$patientId]);
            $history = $stmt->fetchAll();

            return $this->jsonResponse([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            error_log("Get Macular Thickness History Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to retrieve macular thickness history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure macular thickness results table exists
     * Creates the table if it doesn't exist
     */
    private function ensureMacularThicknessTableExists()
    {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'macular_thickness_results'");
            if ($stmt->rowCount() > 0) {
                return; // Table exists
            }

            // Create table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS macular_thickness_results (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    patient_id INT NOT NULL,
                    appointment_id INT NULL,
                    measurement_date DATE NOT NULL,
                    central_macular_thickness DECIMAL(6,2) NOT NULL,
                    eye VARCHAR(2) NOT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_patient_id (patient_id),
                    INDEX idx_appointment_id (appointment_id),
                    INDEX idx_measurement_date (measurement_date),
                    INDEX idx_eye (eye),
                    UNIQUE KEY unique_measurement (patient_id, appointment_id, measurement_date, eye)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            error_log("Error creating macular thickness table: " . $e->getMessage());
            // Don't throw - allow analysis to proceed even if table creation fails
        }
    }

    /**
     * Calculate Cataract Surgery Readiness Score
     * POST/GET /api/cataract-surgery/readiness
     */
    public function calculateCataractSurgeryReadiness()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            // Ensure readiness table exists
            $this->ensureCataractSurgeryReadinessTableExists();

            // Get input data
            $bcvaOd = $_POST['bcva_od'] ?? $_GET['bcva_od'] ?? null;
            $bcvaOs = $_POST['bcva_os'] ?? $_GET['bcva_os'] ?? null;
            $visualComplaintsScore = $_POST['visual_complaints_score'] ?? $_GET['visual_complaints_score'] ?? null;
            $lensOpacityGrade = $_POST['lens_opacity_grade'] ?? $_GET['lens_opacity_grade'] ?? null;
            $complications = $_POST['complications'] ?? $_GET['complications'] ?? [];
            $patientId = $_POST['patient_id'] ?? $_GET['patient_id'] ?? null;
            $appointmentId = $_POST['appointment_id'] ?? $_GET['appointment_id'] ?? null;

            // Handle POST data (can be JSON or form data)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    if ($json) {
                        $postData = json_decode($json, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $bcvaOd = $postData['bcva_od'] ?? $bcvaOd;
                            $bcvaOs = $postData['bcva_os'] ?? $bcvaOs;
                            $visualComplaintsScore = $postData['visual_complaints_score'] ?? $visualComplaintsScore;
                            $lensOpacityGrade = $postData['lens_opacity_grade'] ?? $lensOpacityGrade;
                            $complications = $postData['complications'] ?? $complications;
                            $patientId = $postData['patient_id'] ?? $patientId;
                            $appointmentId = $postData['appointment_id'] ?? $appointmentId;
                        }
                    }
                } else {
                    // Form data - handle complications array
                    if (isset($_POST['complications']) && is_string($_POST['complications'])) {
                        $complications = json_decode($_POST['complications'], true);
                        if (!is_array($complications)) {
                            $complications = [];
                        }
                    }
                }
            }

            // Convert empty strings to null
            $bcvaOd = ($bcvaOd === '' || $bcvaOd === null) ? null : trim($bcvaOd);
            $bcvaOs = ($bcvaOs === '' || $bcvaOs === null) ? null : trim($bcvaOs);
            $visualComplaintsScore = ($visualComplaintsScore === '' || $visualComplaintsScore === null) ? null : $visualComplaintsScore;
            $lensOpacityGrade = ($lensOpacityGrade === '' || $lensOpacityGrade === null) ? null : trim($lensOpacityGrade);
            if (!is_array($complications)) {
                $complications = [];
            }

            $data = [
                'bcva_od' => $bcvaOd,
                'bcva_os' => $bcvaOs,
                'visual_complaints_score' => $visualComplaintsScore,
                'lens_opacity_grade' => $lensOpacityGrade,
                'complications' => $complications
            ];

            // Initialize service
            $service = new CataractSurgeryReadinessService();
            $results = $service->analyze($data);

            // Save to database if patientId provided
            if ($results['success'] && $patientId) {
                // Convert BCVA to decimal for storage
                $bcvaOdDecimal = $bcvaOd ? $this->convertBcvaToDecimal($bcvaOd) : null;
                $bcvaOsDecimal = $bcvaOs ? $this->convertBcvaToDecimal($bcvaOs) : null;

                $stmt = $this->pdo->prepare("
                    INSERT INTO cataract_surgery_readiness_results (
                        patient_id, appointment_id, bcva_od, bcva_os,
                        visual_complaints_score, lens_opacity_grade, complications,
                        readiness_score, readiness_classification, clinical_summary, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $patientId,
                    $appointmentId ?: null,
                    $bcvaOdDecimal,
                    $bcvaOsDecimal,
                    $visualComplaintsScore,
                    $lensOpacityGrade,
                    json_encode($complications),
                    $results['total_score'],
                    $results['readiness_classification'],
                    $results['clinical_summary'],
                    $user['id']
                ]);
            }

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Cataract Surgery Readiness Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze Post-Operative Outcome
     * POST/GET /api/cataract-surgery/postop-outcome
     */
    public function analyzePostOperativeOutcome()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();

            // Ensure outcomes table exists
            $this->ensurePostOperativeOutcomesTableExists();

            // Get input data
            $eye = $_POST['eye'] ?? $_GET['eye'] ?? null;
            $preopBcva = $_POST['preop_bcva'] ?? $_GET['preop_bcva'] ?? null;
            $preopTargetSphere = $_POST['preop_target_sphere'] ?? $_GET['preop_target_sphere'] ?? null;
            $preopTargetCylinder = $_POST['preop_target_cylinder'] ?? $_GET['preop_target_cylinder'] ?? null;
            $postopSphere = $_POST['postop_sphere'] ?? $_GET['postop_sphere'] ?? null;
            $postopCylinder = $_POST['postop_cylinder'] ?? $_GET['postop_cylinder'] ?? null;
            $postopBcva = $_POST['postop_bcva'] ?? $_GET['postop_bcva'] ?? null;
            $surgeryDate = $_POST['surgery_date'] ?? $_GET['surgery_date'] ?? null;
            $patientId = $_POST['patient_id'] ?? $_GET['patient_id'] ?? null;
            $appointmentId = $_POST['appointment_id'] ?? $_GET['appointment_id'] ?? null;
            $surgeonId = $_POST['surgeon_id'] ?? $_GET['surgeon_id'] ?? $user['id'];

            // Handle POST data (can be JSON or form data)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    if ($json) {
                        $postData = json_decode($json, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $eye = $postData['eye'] ?? $eye;
                            $preopBcva = $postData['preop_bcva'] ?? $preopBcva;
                            $preopTargetSphere = $postData['preop_target_sphere'] ?? $preopTargetSphere;
                            $preopTargetCylinder = $postData['preop_target_cylinder'] ?? $preopTargetCylinder;
                            $postopSphere = $postData['postop_sphere'] ?? $postopSphere;
                            $postopCylinder = $postData['postop_cylinder'] ?? $postopCylinder;
                            $postopBcva = $postData['postop_bcva'] ?? $postopBcva;
                            $surgeryDate = $postData['surgery_date'] ?? $surgeryDate;
                            $patientId = $postData['patient_id'] ?? $patientId;
                            $appointmentId = $postData['appointment_id'] ?? $appointmentId;
                            $surgeonId = $postData['surgeon_id'] ?? $surgeonId;
                        }
                    }
                }
            }

            // Convert empty strings to null
            $preopBcva = ($preopBcva === '' || $preopBcva === null) ? null : trim($preopBcva);
            $preopTargetSphere = ($preopTargetSphere === '' || $preopTargetSphere === null) ? null : $preopTargetSphere;
            $preopTargetCylinder = ($preopTargetCylinder === '' || $preopTargetCylinder === null) ? null : $preopTargetCylinder;
            $eye = ($eye === '' || $eye === null) ? null : trim($eye);
            $postopSphere = ($postopSphere === '' || $postopSphere === null) ? null : $postopSphere;
            $postopCylinder = ($postopCylinder === '' || $postopCylinder === null) ? null : $postopCylinder;
            $postopBcva = ($postopBcva === '' || $postopBcva === null) ? null : trim($postopBcva);
            $surgeryDate = ($surgeryDate === '' || $surgeryDate === null) ? null : trim($surgeryDate);

            $data = [
                'eye' => $eye,
                'preop_bcva' => $preopBcva,
                'preop_target_sphere' => $preopTargetSphere,
                'preop_target_cylinder' => $preopTargetCylinder,
                'postop_sphere' => $postopSphere,
                'postop_cylinder' => $postopCylinder,
                'postop_bcva' => $postopBcva,
                'surgery_date' => $surgeryDate
            ];

            // Initialize service
            $service = new PostOperativeOutcomeAnalyzerService();
            $results = $service->analyze($data);

            // Save to database if patientId provided
            if ($results['success'] && $patientId) {
                // Convert BCVA to decimal for storage
                $preopBcvaDecimal = $preopBcva ? $this->convertBcvaToDecimal($preopBcva) : null;
                $postopBcvaDecimal = $postopBcva ? $this->convertBcvaToDecimal($postopBcva) : null;

                $stmt = $this->pdo->prepare("
                    INSERT INTO post_operative_outcomes (
                        patient_id, appointment_id, surgery_date, eye,
                        preop_bcva, preop_target_sphere, preop_target_cylinder,
                        postop_sphere, postop_cylinder, postop_bcva,
                        refractive_error_sphere, refractive_error_cylinder,
                        refractive_outcome, visual_outcome,
                        outcome_summary, surgical_summary, surgeon_id, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $patientId,
                    $appointmentId ?: null,
                    $surgeryDate,
                    $eye,
                    $preopBcvaDecimal,
                    $preopTargetSphere,
                    $preopTargetCylinder,
                    $postopSphere,
                    $postopCylinder,
                    $postopBcvaDecimal,
                    $results['refractive_error_sphere'],
                    $results['refractive_error_cylinder'],
                    $results['refractive_outcome'],
                    $results['visual_outcome'] ?: 'Not assessed',
                    $results['outcome_summary'],
                    $results['surgical_summary'],
                    $surgeonId,
                    $user['id']
                ]);
            }

            return $this->jsonResponse($results);

        } catch (\Exception $e) {
            error_log("Post-Operative Outcome Analyzer Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Surgical Outcomes Audit
     * GET /api/cataract-surgery/audit
     */
    public function getSurgicalOutcomesAudit()
    {
        try {
            // Check authentication
            if (!$this->auth->check()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['success' => false, 'error' => 'Permission denied'], 403);
            }

            // Ensure outcomes table exists
            $this->ensurePostOperativeOutcomesTableExists();

            // Get filter parameters
            $surgeonId = $_GET['surgeon_id'] ?? null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            // Build query
            $whereConditions = [];
            $params = [];

            if ($surgeonId) {
                $whereConditions[] = "surgeon_id = ?";
                $params[] = $surgeonId;
            }

            if ($startDate) {
                $whereConditions[] = "surgery_date >= ?";
                $params[] = $startDate;
            }

            if ($endDate) {
                $whereConditions[] = "surgery_date <= ?";
                $params[] = $endDate;
            }

            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

            // Get total cases
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM post_operative_outcomes $whereClause");
            $stmt->execute($params);
            $totalCases = $stmt->fetch()['total'];

            // Get refractive outcome distribution
            $stmt = $this->pdo->prepare("
                SELECT refractive_outcome, COUNT(*) as count
                FROM post_operative_outcomes
                $whereClause
                GROUP BY refractive_outcome
            ");
            $stmt->execute($params);
            $refractiveDistribution = $stmt->fetchAll();

            // Get visual outcome distribution
            $stmt = $this->pdo->prepare("
                SELECT visual_outcome, COUNT(*) as count
                FROM post_operative_outcomes
                $whereClause
                GROUP BY visual_outcome
            ");
            $stmt->execute($params);
            $visualDistribution = $stmt->fetchAll();

            // Get average refractive errors
            $stmt = $this->pdo->prepare("
                SELECT 
                    AVG(refractive_error_sphere) as avg_sphere_error,
                    AVG(refractive_error_cylinder) as avg_cylinder_error,
                    AVG(ABS(refractive_error_sphere)) as avg_abs_sphere_error,
                    AVG(ABS(refractive_error_cylinder)) as avg_abs_cylinder_error
                FROM post_operative_outcomes
                $whereClause
            ");
            $stmt->execute($params);
            $avgErrors = $stmt->fetch();

            return $this->jsonResponse([
                'success' => true,
                'total_cases' => (int) $totalCases,
                'refractive_outcome_distribution' => $refractiveDistribution,
                'visual_outcome_distribution' => $visualDistribution,
                'average_refractive_errors' => [
                    'avg_sphere_error' => $avgErrors['avg_sphere_error'] ? round((float) $avgErrors['avg_sphere_error'], 2) : null,
                    'avg_cylinder_error' => $avgErrors['avg_cylinder_error'] ? round((float) $avgErrors['avg_cylinder_error'], 2) : null,
                    'avg_abs_sphere_error' => $avgErrors['avg_abs_sphere_error'] ? round((float) $avgErrors['avg_abs_sphere_error'], 2) : null,
                    'avg_abs_cylinder_error' => $avgErrors['avg_abs_cylinder_error'] ? round((float) $avgErrors['avg_abs_cylinder_error'], 2) : null
                ],
                'filters' => [
                    'surgeon_id' => $surgeonId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Surgical Outcomes Audit Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Audit retrieval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert BCVA to decimal for storage
     * 
     * @param string $bcva BCVA value (Snellen or LogMAR)
     * @return float|null Decimal value (LogMAR) or null if invalid
     */
    private function convertBcvaToDecimal(string $bcva): ?float
    {
        $bcva = trim($bcva);

        // If already LogMAR format (numeric)
        if (is_numeric($bcva)) {
            $logmar = (float) $bcva;
            if ($logmar >= -0.3 && $logmar <= 3.0) {
                return $logmar;
            }
        }

        // Try Snellen format
        $bcva = str_replace(' ', '', $bcva);

        if (preg_match('/^(\d+(?:\.\d+)?)[\/\-](\d+(?:\.\d+)?)$/i', $bcva, $matches)) {
            $numerator = (float) $matches[1];
            $denominator = (float) $matches[2];

            if ($denominator > 0) {
                $snellenDecimal = $numerator / $denominator;
                if ($snellenDecimal > 0) {
                    return -log10($snellenDecimal);
                }
            }
        }

        return null;
    }

    /**
     * Ensure cataract surgery readiness results table exists
     */
    private function ensureCataractSurgeryReadinessTableExists()
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'cataract_surgery_readiness_results'");
            if ($stmt->rowCount() > 0) {
                return;
            }

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS cataract_surgery_readiness_results (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    patient_id INT NOT NULL,
                    appointment_id INT NULL,
                    bcva_od DECIMAL(4,2) NULL,
                    bcva_os DECIMAL(4,2) NULL,
                    visual_complaints_score INT NOT NULL,
                    lens_opacity_grade VARCHAR(50) NOT NULL,
                    complications TEXT NULL,
                    readiness_score DECIMAL(5,2) NOT NULL,
                    readiness_classification VARCHAR(50) NOT NULL,
                    clinical_summary TEXT NOT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_patient_id (patient_id),
                    INDEX idx_appointment_id (appointment_id),
                    INDEX idx_readiness_classification (readiness_classification)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            error_log("Error creating cataract surgery readiness table: " . $e->getMessage());
        }
    }

    /**
     * Ensure post-operative outcomes table exists
     */
    private function ensurePostOperativeOutcomesTableExists()
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'post_operative_outcomes'");
            if ($stmt->rowCount() > 0) {
                return;
            }

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS post_operative_outcomes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    patient_id INT NOT NULL,
                    appointment_id INT NULL,
                    surgery_date DATE NOT NULL,
                    eye VARCHAR(2) NOT NULL,
                    preop_bcva DECIMAL(4,2) NULL,
                    preop_target_sphere DECIMAL(6,2) NULL,
                    preop_target_cylinder DECIMAL(6,2) NULL,
                    postop_sphere DECIMAL(6,2) NOT NULL,
                    postop_cylinder DECIMAL(6,2) NOT NULL,
                    postop_bcva DECIMAL(4,2) NOT NULL,
                    refractive_error_sphere DECIMAL(6,2) NOT NULL,
                    refractive_error_cylinder DECIMAL(6,2) NOT NULL,
                    refractive_outcome VARCHAR(50) NOT NULL,
                    visual_outcome VARCHAR(50) NOT NULL,
                    outcome_summary TEXT NOT NULL,
                    surgical_summary TEXT NOT NULL,
                    surgeon_id INT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_patient_id (patient_id),
                    INDEX idx_appointment_id (appointment_id),
                    INDEX idx_surgery_date (surgery_date),
                    INDEX idx_surgeon_id (surgeon_id),
                    INDEX idx_refractive_outcome (refractive_outcome),
                    INDEX idx_visual_outcome (visual_outcome)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            error_log("Error creating post-operative outcomes table: " . $e->getMessage());
        }
    }

    /**
     * Get unified clinical dashboard snapshot for a patient
     * 
     * @return \Psr\Http\Message\ResponseInterface JSON response
     */
    public function getClinicalDashboardSnapshot()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['ok' => false, 'error' => 'Permission denied'], 403);
            }

            // Get patient_id from query parameter
            $patientId = $_GET['patient_id'] ?? null;

            if (!$patientId || !is_numeric($patientId)) {
                return $this->jsonResponse([
                    'ok' => false,
                    'error' => 'Patient ID is required',
                    'data' => [
                        'snapshot' => null,
                        'alerts' => [],
                        'summary' => 'Please select a patient to view clinical dashboard.'
                    ]
                ]);
            }

            $patientId = (int) $patientId;

            // Verify patient exists and get patient name
            $stmt = $this->pdo->prepare("SELECT id, first_name, last_name FROM patients WHERE id = ?");
            $stmt->execute([$patientId]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$patient) {
                return $this->jsonResponse([
                    'ok' => false,
                    'error' => 'Patient not found',
                    'data' => [
                        'snapshot' => null,
                        'alerts' => [],
                        'summary' => 'Patient not found.'
                    ]
                ]);
            }

            // Get clinical snapshot using parser service
            $parserService = new ClinicalDataParserService();
            $snapshot = $parserService->getClinicalSnapshot($patientId);

            // Generate clinical summary
            $summary = $parserService->generateClinicalSummary($snapshot);

            // Build patient full name
            $patientName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'snapshot' => $snapshot,
                    'alerts' => $snapshot['alerts'],
                    'summary' => $summary,
                    'patient_id' => $patientId,
                    'patient_name' => $patientName
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Error getting clinical dashboard snapshot: " . $e->getMessage());
            return $this->jsonResponse([
                'ok' => false,
                'error' => 'Failed to load clinical dashboard data',
                'data' => [
                    'snapshot' => null,
                    'alerts' => [],
                    'summary' => 'Error loading clinical data.'
                ]
            ], 500);
        }
    }

    public function getCommonComplaints()
    {
        try {
            $seedFile = __DIR__ . '/../Data/common_complaints.json';
            $cachedFile = __DIR__ . '/../storage/common_complaints.json';

            $seedData = [];
            $cachedData = [];

            // Load seed data (initial/common complaints)
            if (file_exists($seedFile)) {
                $seedContent = file_get_contents($seedFile);
                $seedData = json_decode($seedContent, true);
                if ($seedData === null || !is_array($seedData)) {
                    $seedData = [];
                }
            }

            // Load cached data from database (generated by update script)
            if (file_exists($cachedFile)) {
                $cachedContent = file_get_contents($cachedFile);
                $cachedData = json_decode($cachedContent, true);
                if ($cachedData === null || !is_array($cachedData)) {
                    $cachedData = [];
                }
            }

            // Merge seed data with cached data
            $mergedComplaints = [];
            $seenComplaints = []; // Track normalized complaints to avoid duplicates

            // Helper function to normalize text for comparison
            $normalizeText = function ($text) {
                return mb_strtolower(trim(preg_replace('/\s+/', ' ', $text)));
            };

            // Helper function to calculate similarity
            $calculateSimilarity = function ($text1, $text2) use ($normalizeText) {
                $norm1 = $normalizeText($text1);
                $norm2 = $normalizeText($text2);
                if ($norm1 === $norm2)
                    return 100;
                similar_text($norm1, $norm2, $percent);
                return $percent;
            };

            // Add cached complaints first (they have count data)
            if (isset($cachedData['complaints']) && is_array($cachedData['complaints'])) {
                foreach ($cachedData['complaints'] as $complaint) {
                    $normalized = $normalizeText($complaint['complaint'] ?? '');
                    if (!empty($normalized) && !isset($seenComplaints[$normalized])) {
                        $mergedComplaints[] = [
                            'complaint' => $complaint['complaint'] ?? '',
                            'diagnosis' => $complaint['diagnosis'] ?? '',
                            'plan' => $complaint['plan'] ?? '',
                            'count' => isset($complaint['count']) ? (int) $complaint['count'] : 1
                        ];
                        $seenComplaints[$normalized] = true;
                    }
                }
            }

            // Add seed data, checking for duplicates
            foreach ($seedData as $seedComplaint) {
                if (!isset($seedComplaint['complaint']))
                    continue;

                $normalized = $normalizeText($seedComplaint['complaint']);
                $isDuplicate = false;

                // Check if similar complaint already exists (>90% similarity)
                foreach ($mergedComplaints as $existing) {
                    $similarity = $calculateSimilarity($seedComplaint['complaint'], $existing['complaint']);
                    if ($similarity > 90) {
                        $isDuplicate = true;
                        // Update existing if seed has more complete data
                        if (!empty($seedComplaint['diagnosis']) && empty($existing['diagnosis'])) {
                            $existing['diagnosis'] = $seedComplaint['diagnosis'];
                        }
                        if (!empty($seedComplaint['plan']) && empty($existing['plan'])) {
                            $existing['plan'] = $seedComplaint['plan'];
                        }
                        break;
                    }
                }

                if (!$isDuplicate && !isset($seenComplaints[$normalized])) {
                    $mergedComplaints[] = [
                        'complaint' => $seedComplaint['complaint'],
                        'diagnosis' => $seedComplaint['diagnosis'] ?? '',
                        'plan' => $seedComplaint['plan'] ?? '',
                        'count' => isset($seedComplaint['count']) ? (int) $seedComplaint['count'] : 0
                    ];
                    $seenComplaints[$normalized] = true;
                }
            }

            // Sort by count descending, then by complaint text
            usort($mergedComplaints, function ($a, $b) {
                if ($b['count'] === $a['count']) {
                    return strcmp($a['complaint'], $b['complaint']);
                }
                return $b['count'] - $a['count'];
            });

            // Limit to top 30
            $mergedComplaints = array_slice($mergedComplaints, 0, 30);

            // Determine last updated time
            $lastUpdated = null;
            if (isset($cachedData['last_updated'])) {
                $lastUpdated = $cachedData['last_updated'];
            }

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'last_updated' => $lastUpdated,
                    'total_count' => count($mergedComplaints),
                    'complaints' => $mergedComplaints
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Error getting common complaints: " . $e->getMessage());
            return $this->jsonResponse([
                'ok' => false,
                'error' => 'Failed to load common complaints',
                'data' => [
                    'last_updated' => null,
                    'total_count' => 0,
                    'complaints' => []
                ]
            ], 500);
        }
    }

    /**
     * Get consultation suggestions based on field and query
     * Route: GET /api/consultation/suggestions?field={field}&query={query}
     */
    public function getConsultationSuggestions()
    {
        try {
            $field = $_GET['field'] ?? '';
            $query = trim($_GET['query'] ?? '');

            // Validate field
            $allowedFields = ['chief_complaint', 'diagnosis', 'plan'];
            if (!in_array($field, $allowedFields)) {
                return $this->jsonResponse([
                    'ok' => false,
                    'error' => 'Invalid field. Allowed fields: ' . implode(', ', $allowedFields)
                ], 400);
            }

            // Validate query length (minimum 3 characters)
            if (mb_strlen($query) < 3) {
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => []
                ]);
            }

            // Search in consultation_notes
            $searchQuery = '%' . $query . '%';
            $stmt = $this->pdo->prepare("
                SELECT
                    {$field} as suggestion,
                    COUNT(*) as count
                FROM consultation_notes
                WHERE {$field} IS NOT NULL
                  AND {$field} != ''
                  AND {$field} LIKE ?
                GROUP BY {$field}
                ORDER BY COUNT(*) DESC, {$field} ASC
                LIMIT 15
            ");

            $stmt->execute([$searchQuery]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Format results
            $suggestions = array_map(function ($row) {
                return [
                    'text' => $row['suggestion'],
                    'count' => (int) $row['count']
                ];
            }, $results);

            return $this->jsonResponse([
                'ok' => true,
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            error_log("Error getting consultation suggestions: " . $e->getMessage());
            return $this->jsonResponse([
                'ok' => false,
                'error' => 'Failed to load suggestions',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get prescription suggestions based on diagnosis
     * Route: GET /api/prescriptions/suggestions?diagnosis={diagnosis}&complaint={complaint}
     */
    public function getPrescriptionSuggestions()
    {
        try {
            $diagnosis = trim($_GET['diagnosis'] ?? '');
            $complaint = trim($_GET['complaint'] ?? '');

            // Validate diagnosis is provided
            if (empty($diagnosis)) {
                return $this->jsonResponse([
                    'ok' => false,
                    'error' => 'Diagnosis parameter is required'
                ], 400);
            }

            // Normalize text for comparison
            $normalizeText = function ($text) {
                return mb_strtolower(trim(preg_replace('/\s+/', ' ', $text)));
            };

            // Calculate similarity between two texts
            $calculateSimilarity = function ($text1, $text2) use ($normalizeText) {
                $norm1 = $normalizeText($text1);
                $norm2 = $normalizeText($text2);
                if ($norm1 === $norm2)
                    return 100;
                similar_text($norm1, $norm2, $percent);
                return $percent;
            };

            // Get all consultation notes with diagnoses
            $stmt = $this->pdo->query("
                SELECT DISTINCT cn.appointment_id, cn.diagnosis, cn.chief_complaint
                FROM consultation_notes cn
                WHERE cn.diagnosis IS NOT NULL
                  AND cn.diagnosis != ''
                  AND TRIM(cn.diagnosis) != ''
            ");
            $allConsultations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Find appointments with similar diagnoses (>90% similarity)
            $matchingAppointmentIds = [];
            foreach ($allConsultations as $consultation) {
                $similarity = $calculateSimilarity($diagnosis, $consultation['diagnosis']);
                if ($similarity > 90) {
                    $matchingAppointmentIds[] = $consultation['appointment_id'];
                }
            }

            if (empty($matchingAppointmentIds)) {
                return $this->jsonResponse([
                    'ok' => true,
                    'data' => [],
                    'message' => 'No similar diagnoses found'
                ]);
            }

            // Get prescriptions from matching appointments
            $placeholders = implode(',', array_fill(0, count($matchingAppointmentIds), '?'));
            $stmt = $this->pdo->prepare("
                SELECT
                    p.drug_name,
                    p.notes,
                    p.route,
                    COUNT(*) as count
                FROM prescriptions p
                WHERE p.appointment_id IN ($placeholders)
                  AND p.drug_name IS NOT NULL
                  AND p.drug_name != ''
                  AND TRIM(p.drug_name) != ''
                GROUP BY p.drug_name, p.notes, p.route
                ORDER BY COUNT(*) DESC
                LIMIT 15
            ");

            $stmt->execute($matchingAppointmentIds);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Format results
            $suggestions = array_map(function ($row) {
                return [
                    'drug_name' => $row['drug_name'],
                    'notes' => $row['notes'] ?? '',
                    'route' => $row['route'] ?? 'Topical',
                    'count' => (int) $row['count']
                ];
            }, $results);

            return $this->jsonResponse([
                'ok' => true,
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            error_log("Error getting prescription suggestions: " . $e->getMessage());
            return $this->jsonResponse([
                'ok' => false,
                'error' => 'Failed to load prescription suggestions',
                'data' => []
            ], 500);
        }
    }

    /**
     * Chat with AI using Groq API
     *
     * @return \Psr\Http\Message\ResponseInterface JSON response
     */
    public function chatWithAI()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['ok' => false, 'error' => 'Permission denied'], 403);
            }

            $userId = $user['id'];
            $input = json_decode(file_get_contents('php://input'), true);

            $message = trim($input['message'] ?? '');
            $patientId = isset($input['patient_id']) && $input['patient_id'] ? (int) $input['patient_id'] : null;
            $appointmentId = isset($input['appointment_id']) && $input['appointment_id'] ? (int) $input['appointment_id'] : null;
            $contextType = $input['context_type'] ?? 'general';

            error_log("=== chatWithAI Request Debug ===");
            error_log("Raw input patient_id: " . ($input['patient_id'] ?? 'not set'));
            error_log("Parsed patientId: " . ($patientId ?? 'null'));
            error_log("Raw input appointment_id: " . ($input['appointment_id'] ?? 'not set'));
            error_log("Parsed appointmentId: " . ($appointmentId ?? 'null'));
            error_log("Context Type: " . $contextType);
            error_log("Message: " . substr($message, 0, 100));

            if (empty($message)) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Message is required'], 400);
            }

            // Validate patient/appointment access
            if ($patientId) {
                $stmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
                $stmt->execute([$patientId]);
                if (!$stmt->fetch()) {
                    return $this->jsonResponse(['ok' => false, 'error' => 'Patient not found'], 404);
                }
            }

            if ($appointmentId) {
                $stmt = $this->pdo->prepare("SELECT id FROM appointments WHERE id = ?");
                $stmt->execute([$appointmentId]);
                if (!$stmt->fetch()) {
                    return $this->jsonResponse(['ok' => false, 'error' => 'Appointment not found'], 404);
                }
            }

            // Ensure table exists (don't fail if table creation has issues)
            try {
                $this->ensureAIChatHistoryTableExists();
            } catch (\Exception $tableError) {
                error_log("Warning: Could not ensure AI chat history table exists: " . $tableError->getMessage());
                // Continue anyway - table might already exist or will be created later
            }

            // Build context based on context_type
            // IMPORTANT: For follow-up messages, check chat history to determine if we should include context
            $context = '';
            $systemPrompt = 'You are a helpful medical AI assistant for healthcare professionals. You are assisting a licensed medical doctor who is reviewing patient information. Provide evidence-based medical guidance, clinical insights, and suggestions based on the provided medical information. Your responses should be professional, accurate, and clinically relevant. Always remind users that your responses are for guidance and clinical decision support only and should not replace professional medical judgment.';

            // Check chat history to see if previous messages had patient context
            $chatHistory = $this->getChatHistoryMessages($userId, $patientId, null);
            $hasPatientHistoryContext = false;
            $hasConsultationContext = false;

            if (!empty($chatHistory)) {
                // Check if any previous message had patient_history or consultation_summary context
                foreach ($chatHistory as $msg) {
                    if (isset($msg['context_type'])) {
                        if ($msg['context_type'] === 'patient_history') {
                            $hasPatientHistoryContext = true;
                        } elseif ($msg['context_type'] === 'consultation_summary') {
                            $hasConsultationContext = true;
                        }
                    }
                }
            }

            // Build context based on current request or previous context
            if ($contextType === 'patient_history' && $patientId) {
                // Build complete patient history context based on patient_id only
                // This retrieves ALL data from database for the patient, ignoring appointment_id
                $context = $this->buildPatientHistoryContext($patientId);

                // Ensure context is not empty
                if (empty($context) || trim($context) === '') {
                    error_log("WARNING: buildPatientHistoryContext returned empty context for patient ID: " . $patientId);
                    $context = "PATIENT INFORMATION:\nPatient ID: {$patientId}\n\nNOTE: No patient data was found in the database for this patient ID. Please verify the patient ID is correct.";
                }

                $systemPrompt = 'You are a helpful medical AI assistant for healthcare professionals. You are assisting a licensed medical doctor who is reviewing a patient\'s complete medical history. CRITICAL: You MUST base your analysis ONLY on the patient data provided below in the "PATIENT MEDICAL HISTORY DATA" section. Do NOT invent, assume, or add any information that is not explicitly stated in the provided data. If information is missing, state that it is not available rather than making assumptions. Provide evidence-based clinical insights, identify patterns, and suggest considerations based STRICTLY on the actual data provided. Your responses should be professional, clinically relevant, and appropriate for a medical professional. Always remind users that your responses are for guidance and clinical decision support only and should not replace professional medical judgment.';
            } elseif ($contextType === 'consultation_summary' && $appointmentId) {
                $context = $this->buildConsultationSummaryContext($appointmentId, $patientId);
                $systemPrompt = 'You are a helpful medical AI assistant for healthcare professionals. You are assisting a licensed medical doctor who is reviewing a consultation. CRITICAL: You MUST base your summary ONLY on the consultation and patient data provided below. Do NOT invent, assume, or add any information that is not explicitly stated in the provided data. If information is missing, state that it is not available rather than making assumptions. Provide a clear, clinically relevant summary and context based STRICTLY on the actual data provided. Your responses should be professional and appropriate for a medical professional. Always remind users that your responses are for guidance and clinical decision support only and should not replace professional medical judgment.';
            } elseif ($hasPatientHistoryContext && $patientId) {
                // Follow-up message but previous messages had patient history context
                // Rebuild the context so the AI remembers the patient data
                $context = $this->buildPatientHistoryContext($patientId);

                if (empty($context) || trim($context) === '') {
                    error_log("WARNING: buildPatientHistoryContext returned empty context for patient ID: " . $patientId);
                    $context = "PATIENT INFORMATION:\nPatient ID: {$patientId}\n\nNOTE: No patient data was found in the database for this patient ID. Please verify the patient ID is correct.";
                }

                $systemPrompt = 'You are a helpful medical AI assistant for healthcare professionals. You are assisting a licensed medical doctor who is reviewing a patient\'s complete medical history. The patient data has been provided in previous messages and is included again below for reference. CRITICAL: You MUST base your responses ONLY on the patient data provided below in the "PATIENT MEDICAL HISTORY DATA" section. Do NOT invent, assume, or add any information that is not explicitly stated in the provided data. Provide evidence-based clinical insights and suggestions appropriate for a medical professional. Always remind users that your responses are for guidance and clinical decision support only and should not replace professional medical judgment.';
            } elseif ($hasConsultationContext && $appointmentId) {
                // Follow-up message but previous messages had consultation context
                // Rebuild the context so the AI remembers the consultation data
                $context = $this->buildConsultationSummaryContext($appointmentId, $patientId);
                $systemPrompt = 'You are a helpful medical AI assistant for healthcare professionals. You are assisting a licensed medical doctor who is reviewing a consultation. The consultation data has been provided in previous messages and is included again below for reference. CRITICAL: You MUST base your responses ONLY on the consultation and patient data provided below. Do NOT invent, assume, or add any information that is not explicitly stated in the provided data. Provide clinically relevant insights appropriate for a medical professional. Always remind users that your responses are for guidance and clinical decision support only and should not replace professional medical judgment.';
            } elseif ($patientId && !empty($chatHistory)) {
                // Follow-up message with patient ID - include patient context for continuity
                // This ensures the AI always has access to patient data in follow-up conversations
                $context = $this->buildPatientHistoryContext($patientId);

                if (empty($context) || trim($context) === '') {
                    error_log("WARNING: buildPatientHistoryContext returned empty context for patient ID: " . $patientId);
                    $context = "PATIENT INFORMATION:\nPatient ID: {$patientId}\n\nNOTE: No patient data was found in the database for this patient ID. Please verify the patient ID is correct.";
                }

                $systemPrompt = 'You are a helpful medical AI assistant for healthcare professionals. You are assisting a licensed medical doctor who is reviewing patient information. The patient data is provided below for reference and has been discussed in previous messages. CRITICAL: You MUST base your responses ONLY on the patient data provided below in the "PATIENT MEDICAL HISTORY DATA" section. Do NOT invent, assume, or add any information that is not explicitly stated in the provided data. Provide evidence-based clinical insights and suggestions appropriate for a medical professional. Always remind users that your responses are for guidance and clinical decision support only and should not replace professional medical judgment.';
            } elseif ($patientId && $contextType === 'general') {
                // General message but we have a patient ID - include patient context for continuity
                $context = $this->buildPatientHistoryContext($patientId);

                if (empty($context) || trim($context) === '') {
                    error_log("WARNING: buildPatientHistoryContext returned empty context for patient ID: " . $patientId);
                    $context = "PATIENT INFORMATION:\nPatient ID: {$patientId}\n\nNOTE: No patient data was found in the database for this patient ID. Please verify the patient ID is correct.";
                }

                $systemPrompt = 'You are a helpful medical AI assistant for healthcare professionals. You are assisting a licensed medical doctor who is reviewing patient information. The patient data is provided below for reference. CRITICAL: You MUST base your responses ONLY on the patient data provided below in the "PATIENT MEDICAL HISTORY DATA" section. Do NOT invent, assume, or add any information that is not explicitly stated in the provided data. Provide evidence-based clinical insights and suggestions appropriate for a medical professional. Always remind users that your responses are for guidance and clinical decision support only and should not replace professional medical judgment.';
            }

            // Note: chatHistory was already retrieved above for context type detection
            // Build messages array for Groq API
            $messages = [];

            // Add system message with context
            // Always include context if it exists, even if empty (for debugging)
            $systemMessageContent = $systemPrompt;
            if ($context && !empty(trim($context))) {
                $systemMessageContent .= "\n\n=== PATIENT MEDICAL HISTORY DATA ===\n\n" . $context . "\n\n=== END PATIENT MEDICAL HISTORY DATA ===";
            } else {
                error_log("WARNING: No context to include in system message!");
                $systemMessageContent .= "\n\n=== PATIENT MEDICAL HISTORY DATA ===\n\nNo patient data was found or provided.\n\n=== END PATIENT MEDICAL HISTORY DATA ===";
            }

            $messages[] = [
                'role' => 'system',
                'content' => $systemMessageContent
            ];

            error_log("System message length: " . strlen($systemMessageContent) . " characters");

            // Add chat history (last 10 messages for context)
            $recentHistory = array_slice($chatHistory, -10);
            foreach ($recentHistory as $historyMsg) {
                $messages[] = [
                    'role' => $historyMsg['role'],
                    'content' => $historyMsg['message']
                ];
            }

            // Add current user message
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];

            // Log the context being sent (for debugging - remove in production or make conditional)
            error_log("=== AI Chat Request Debug ===");
            error_log("Context Type: " . $contextType);
            error_log("Patient ID: " . ($patientId ?? 'null'));
            error_log("Appointment ID: " . ($appointmentId ?? 'null'));
            error_log("Context exists: " . ($context ? 'YES' : 'NO'));
            if ($context) {
                error_log("Context Length: " . strlen($context) . " characters");
                error_log("Context Preview (first 1000 chars): " . substr($context, 0, 1000));
                error_log("Context Preview (last 500 chars): " . substr($context, -500));
            } else {
                error_log("WARNING: Context is empty!");
            }
            error_log("=== End AI Chat Request Debug ===");

            // Save user message to database
            $this->saveChatMessage($userId, $patientId, $appointmentId, 'user', $message, $contextType);

            // Call Groq API
            $groqApiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');
            if (!$groqApiKey || empty(trim($groqApiKey))) {
                error_log("GROQ_API_KEY not found in environment. Available keys: " . implode(', ', array_keys($_ENV)));
                return $this->jsonResponse(['ok' => false, 'error' => 'AI service not configured. Please check GROQ_API_KEY in .env file.'], 500);
            }

            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $groqApiKey
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 4000
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                error_log("Groq API cURL error: " . $curlError);
                return $this->jsonResponse(['ok' => false, 'error' => 'AI service connection error: ' . $curlError], 500);
            }

            if ($httpCode !== 200) {
                error_log("Groq API HTTP error: " . $httpCode . " - " . substr($response, 0, 500));
                $errorMsg = 'AI service error';
                if ($httpCode === 401) {
                    $errorMsg = 'AI service authentication failed. Please check API key.';
                } elseif ($httpCode === 429) {
                    $errorMsg = 'AI service rate limit exceeded. Please try again later.';
                }
                return $this->jsonResponse(['ok' => false, 'error' => $errorMsg], 500);
            }

            $aiResponse = json_decode($response, true);

            if (!isset($aiResponse['choices'][0]['message']['content'])) {
                error_log("Groq API unexpected response: " . substr($response, 0, 500));
                return $this->jsonResponse(['ok' => false, 'error' => 'AI service returned invalid response'], 500);
            }

            $aiMessage = $aiResponse['choices'][0]['message']['content'];

            // Save AI response to database
            $this->saveChatMessage($userId, $patientId, $appointmentId, 'assistant', $aiMessage, $contextType);

            return $this->jsonResponse([
                'ok' => true,
                'data' => [
                    'message' => $aiMessage,
                    'role' => 'assistant'
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Error in chatWithAI: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->jsonResponse([
                'ok' => false,
                'error' => 'Failed to process chat request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chat history
     *
     * @return \Psr\Http\Message\ResponseInterface JSON response
     */
    public function getChatHistory()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['ok' => false, 'error' => 'Permission denied'], 403);
            }

            $userId = $user['id'];
            $patientId = isset($_GET['patient_id']) && $_GET['patient_id'] ? (int) $_GET['patient_id'] : null;
            $appointmentId = isset($_GET['appointment_id']) && $_GET['appointment_id'] ? (int) $_GET['appointment_id'] : null;

            // Ensure table exists (don't fail if table creation has issues)
            try {
                $this->ensureAIChatHistoryTableExists();
            } catch (\Exception $tableError) {
                error_log("Warning: Could not ensure AI chat history table exists: " . $tableError->getMessage());
                // Continue anyway - return empty array if table doesn't exist
            }

            // Chat history is shared by patient_id only (appointment_id is ignored)
            $messages = $this->getChatHistoryMessages($userId, $patientId, null);

            return $this->jsonResponse([
                'ok' => true,
                'data' => $messages
            ]);

        } catch (\Exception $e) {
            error_log("Error getting chat history: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->jsonResponse([
                'ok' => false,
                'error' => 'Failed to load chat history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear chat history
     *
     * @return \Psr\Http\Message\ResponseInterface JSON response
     */
    public function clearChatHistory()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['ok' => false, 'error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
                return $this->jsonResponse(['ok' => false, 'error' => 'Permission denied'], 403);
            }

            $userId = $user['id'];
            $input = json_decode(file_get_contents('php://input'), true);
            $patientId = isset($input['patient_id']) && $input['patient_id'] ? (int) $input['patient_id'] : null;
            $appointmentId = isset($input['appointment_id']) && $input['appointment_id'] ? (int) $input['appointment_id'] : null;

            // Ensure table exists (don't fail if table creation has issues)
            try {
                $this->ensureAIChatHistoryTableExists();
            } catch (\Exception $tableError) {
                error_log("Warning: Could not ensure AI chat history table exists: " . $tableError->getMessage());
                // Continue anyway - table might already exist
            }

            // Build query - clear all chat history for patient (appointment_id is ignored)
            $query = "DELETE FROM ai_chat_history WHERE user_id = ?";
            $params = [$userId];

            if ($patientId) {
                $query .= " AND patient_id = ?";
                $params[] = $patientId;
            } else {
                $query .= " AND patient_id IS NULL";
            }

            // Note: appointment_id is NOT used for clearing history
            // This ensures all chat history for the patient is cleared, regardless of appointment

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Chat history cleared successfully'
            ]);

        } catch (\Exception $e) {
            error_log("Error clearing chat history: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->jsonResponse([
                'ok' => false,
                'error' => 'Failed to clear chat history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure AI chat history table exists
     */
    private function ensureAIChatHistoryTableExists()
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'ai_chat_history'");
            if ($stmt->rowCount() > 0) {
                return;
            }

            // Try to create table with foreign keys first
            try {
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS ai_chat_history (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        patient_id INT NULL,
                        appointment_id INT NULL,
                        role ENUM('user', 'assistant', 'system') NOT NULL DEFAULT 'user',
                        message TEXT NOT NULL,
                        context_type VARCHAR(50) NULL DEFAULT 'general',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_user_id (user_id),
                        INDEX idx_patient_id (patient_id),
                        INDEX idx_appointment_id (appointment_id),
                        INDEX idx_created_at (created_at),
                        INDEX idx_user_patient (user_id, patient_id),
                        INDEX idx_user_appointment (user_id, appointment_id),
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                        FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            } catch (\Exception $fkError) {
                // If foreign keys fail, create without them
                error_log("Foreign key constraint failed, creating table without foreign keys: " . $fkError->getMessage());
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS ai_chat_history (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        patient_id INT NULL,
                        appointment_id INT NULL,
                        role ENUM('user', 'assistant', 'system') NOT NULL DEFAULT 'user',
                        message TEXT NOT NULL,
                        context_type VARCHAR(50) NULL DEFAULT 'general',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_user_id (user_id),
                        INDEX idx_patient_id (patient_id),
                        INDEX idx_appointment_id (appointment_id),
                        INDEX idx_created_at (created_at),
                        INDEX idx_user_patient (user_id, patient_id),
                        INDEX idx_user_appointment (user_id, appointment_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch (\Exception $e) {
            error_log("Error creating AI chat history table: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to be caught by calling method
        }
    }

    /**
     * Get chat history messages
     * Chat history is shared by patient_id only (appointment_id parameter is ignored)
     */
    private function getChatHistoryMessages($userId, $patientId = null, $appointmentId = null)
    {
        try {
            $query = "SELECT role, message, context_type, created_at
                      FROM ai_chat_history
                      WHERE user_id = ?";
            $params = [$userId];

            // Chat history is shared by patient_id only, not by appointment_id
            if ($patientId) {
                $query .= " AND patient_id = ?";
                $params[] = $patientId;
            } else {
                $query .= " AND patient_id IS NULL";
            }

            // Note: appointment_id is NOT used for filtering chat history
            // This ensures chat history is shared across all appointments for the same patient

            $query .= " ORDER BY created_at ASC";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getChatHistoryMessages: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    /**
     * Save chat message to database
     */
    private function saveChatMessage($userId, $patientId, $appointmentId, $role, $message, $contextType = 'general')
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ai_chat_history (user_id, patient_id, appointment_id, role, message, context_type)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $patientId,
                $appointmentId,
                $role,
                $message,
                $contextType
            ]);
        } catch (\Exception $e) {
            error_log("Error saving chat message: " . $e->getMessage());
        }
    }

    /**
     * Build complete patient history context for AI
     *
     * This function builds the complete medical history for a patient based on ALL database records
     * associated with the patient_id. It ignores appointment_id completely and retrieves:
     * - All appointments and consultation notes
     * - All medication prescriptions
     * - All glasses prescriptions
     * - All lab tests and radiology
     * - All medical history entries
     * - All patient notes
     *
     * @param int $patientId The patient ID (appointment_id is ignored)
     * @return string Complete patient history context string
     */
    private function buildPatientHistoryContext($patientId)
    {
        try {
            error_log("buildPatientHistoryContext called with patientId: " . $patientId);

            // Get patient basic info from database
            $stmt = $this->pdo->prepare("
                SELECT id, first_name, last_name, dob, gender, phone, address, national_id
                FROM patients WHERE id = ?
            ");
            $stmt->execute([$patientId]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$patient) {
                error_log("buildPatientHistoryContext: Patient not found for ID: " . $patientId);
                error_log("buildPatientHistoryContext: Query executed - checking if patient exists...");
                // Try to see if patient exists with different query
                $checkStmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM patients WHERE id = ?");
                $checkStmt->execute([$patientId]);
                $checkResult = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                error_log("buildPatientHistoryContext: Patient count check: " . $checkResult['count']);
                return '';
            }

            error_log("buildPatientHistoryContext: Patient found - ID: " . $patient['id'] . ", Name: " . $patient['first_name'] . " " . $patient['last_name']);

            $context = "PATIENT INFORMATION:\n";
            $context .= "Name: {$patient['first_name']} {$patient['last_name']}\n";
            if ($patient['dob']) {
                $age = date_diff(date_create($patient['dob']), date_create('now'))->y;
                $context .= "Age: {$age} years\n";
                $context .= "Date of Birth: {$patient['dob']}\n";
            }
            if ($patient['gender']) {
                $context .= "Gender: {$patient['gender']}\n";
            }
            if ($patient['phone']) {
                $context .= "Phone: {$patient['phone']}\n";
            }
            $context .= "\n";

            // Get ALL appointments with consultation notes for this patient (appointment_id is ignored)
            // Query includes ALL fields from consultation_notes table based on database structure
            // Note: consultation_notes table does NOT have a 'notes' column
            $stmt = $this->pdo->prepare("
                SELECT a.id, a.date, a.start_time, a.status, a.visit_type,
                       cn.chief_complaint, cn.diagnosis, cn.diagnosis_code, cn.plan, cn.hx_present_illness,
                       cn.visual_acuity_right, cn.visual_acuity_left, cn.refraction_right, cn.refraction_left,
                       cn.IOP_right, cn.IOP_left, cn.slit_lamp_right, cn.slit_lamp_left,
                       cn.fundus_right, cn.fundus_left, cn.external_appearance_right, cn.external_appearance_left,
                       cn.eyelid_right, cn.eyelid_left, cn.systemic_disease, cn.medication, cn.followup_days
                FROM appointments a
                LEFT JOIN consultation_notes cn ON a.id = cn.appointment_id
                WHERE a.patient_id = ?
                ORDER BY a.date DESC, a.start_time DESC
            ");
            $stmt->execute([$patientId]);
            $appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("buildPatientHistoryContext: Found " . count($appointments) . " appointments for patient ID: " . $patientId);

            if ($appointments) {
                $context .= "CONSULTATION NOTES & APPOINTMENT HISTORY:\n";
                foreach ($appointments as $apt) {
                    $context .= "\n=== Appointment #{$apt['id']} - {$apt['date']} {$apt['start_time']} ===\n";
                    $context .= "Status: {$apt['status']}, Type: {$apt['visit_type']}\n";
                    if ($apt['chief_complaint']) {
                        $context .= "Chief Complaint: {$apt['chief_complaint']}\n";
                    }
                    if ($apt['hx_present_illness']) {
                        $context .= "History of Present Illness: {$apt['hx_present_illness']}\n";
                    }
                    if ($apt['visual_acuity_right'] || $apt['visual_acuity_left']) {
                        $context .= "Visual Acuity: OD {$apt['visual_acuity_right']} OS {$apt['visual_acuity_left']}\n";
                    }
                    if ($apt['refraction_right'] || $apt['refraction_left']) {
                        $context .= "Refraction: OD {$apt['refraction_right']} OS {$apt['refraction_left']}\n";
                    }
                    if ($apt['IOP_right'] || $apt['IOP_left']) {
                        $context .= "IOP: OD {$apt['IOP_right']} OS {$apt['IOP_left']}\n";
                    }
                    if ($apt['slit_lamp_right'] || $apt['slit_lamp_left']) {
                        $context .= "Slit Lamp: OD {$apt['slit_lamp_right']} OS {$apt['slit_lamp_left']}\n";
                    }
                    if ($apt['fundus_right'] || $apt['fundus_left']) {
                        $context .= "Fundus: OD {$apt['fundus_right']} OS {$apt['fundus_left']}\n";
                    }
                    if ($apt['external_appearance_right'] || $apt['external_appearance_left']) {
                        $context .= "External Appearance: OD {$apt['external_appearance_right']} OS {$apt['external_appearance_left']}\n";
                    }
                    if ($apt['eyelid_right'] || $apt['eyelid_left']) {
                        $context .= "Eyelid: OD {$apt['eyelid_right']} OS {$apt['eyelid_left']}\n";
                    }
                    if ($apt['diagnosis']) {
                        $context .= "Diagnosis: {$apt['diagnosis']}\n";
                    }
                    if ($apt['diagnosis_code']) {
                        $context .= "Diagnosis Code: {$apt['diagnosis_code']}\n";
                    }
                    if ($apt['systemic_disease']) {
                        $context .= "Systemic Disease: {$apt['systemic_disease']}\n";
                    }
                    if ($apt['medication']) {
                        $context .= "Current Medications: {$apt['medication']}\n";
                    }
                    if ($apt['plan']) {
                        $context .= "Treatment Plan: {$apt['plan']}\n";
                    }
                    if ($apt['followup_days']) {
                        $context .= "Follow-up Days: {$apt['followup_days']}\n";
                    }
                }
                $context .= "\n";
            }

            // Get ALL prescriptions (medications) for this patient (appointment_id is ignored)
            // Query includes ALL fields from prescriptions table: drug_name, dose, frequency, duration, route, notes
            $stmt = $this->pdo->prepare("
                SELECT p.id, p.drug_name, p.dose, p.frequency, p.duration, p.route, p.notes, p.created_at, a.date as appointment_date
                FROM prescriptions p
                JOIN appointments a ON p.appointment_id = a.id
                WHERE a.patient_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $medications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("buildPatientHistoryContext: Found " . count($medications) . " medications for patient ID: " . $patientId);

            if ($medications) {
                $context .= "MEDICATION PRESCRIPTIONS:\n";
                foreach ($medications as $med) {
                    $context .= "\n- {$med['drug_name']}";
                    if ($med['dose']) {
                        $context .= " - Dose: {$med['dose']}";
                    }
                    if ($med['frequency']) {
                        $context .= " - Frequency: {$med['frequency']}";
                    }
                    if ($med['duration']) {
                        $context .= " - Duration: {$med['duration']}";
                    }
                    if ($med['route']) {
                        $context .= " - Route: {$med['route']}";
                    }
                    if ($med['notes']) {
                        $context .= " - Notes: {$med['notes']}";
                    }
                    $context .= " - Prescribed Date: {$med['appointment_date']}\n";
                }
                $context .= "\n";
            }

            // Get ALL glasses prescriptions for this patient (appointment_id is ignored)
            // Query includes ALL fields from glasses_prescriptions table
            $stmt = $this->pdo->prepare("
                SELECT gp.id, gp.lens_type,
                       gp.distance_sphere_r, gp.distance_cylinder_r, gp.distance_axis_r,
                       gp.distance_sphere_l, gp.distance_cylinder_l, gp.distance_axis_l,
                       gp.near_sphere_r, gp.near_cylinder_r, gp.near_axis_r,
                       gp.near_sphere_l, gp.near_cylinder_l, gp.near_axis_l,
                       gp.PD_DISTANCE, gp.PD_NEAR, gp.comments, gp.created_at, a.date as appointment_date
                FROM glasses_prescriptions gp
                JOIN appointments a ON gp.appointment_id = a.id
                WHERE a.patient_id = ?
                ORDER BY gp.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $glasses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("buildPatientHistoryContext: Found " . count($glasses) . " glasses prescriptions for patient ID: " . $patientId);

            if ($glasses) {
                $context .= "GLASSES PRESCRIPTIONS:\n";
                foreach ($glasses as $glass) {
                    $context .= "\nLens Type: {$glass['lens_type']} - Date: {$glass['appointment_date']}\n";
                    if ($glass['distance_sphere_r'] || $glass['distance_sphere_l'] || $glass['distance_cylinder_r'] || $glass['distance_cylinder_l']) {
                        $context .= "Distance Vision:\n";
                        $context .= "  OD: SPH {$glass['distance_sphere_r']} CYL {$glass['distance_cylinder_r']} AXIS {$glass['distance_axis_r']}\n";
                        $context .= "  OS: SPH {$glass['distance_sphere_l']} CYL {$glass['distance_cylinder_l']} AXIS {$glass['distance_axis_l']}\n";
                    }
                    if ($glass['near_sphere_r'] || $glass['near_sphere_l'] || $glass['near_cylinder_r'] || $glass['near_cylinder_l']) {
                        $context .= "Near Vision:\n";
                        $context .= "  OD: SPH {$glass['near_sphere_r']} CYL {$glass['near_cylinder_r']} AXIS {$glass['near_axis_r']}\n";
                        $context .= "  OS: SPH {$glass['near_sphere_l']} CYL {$glass['near_cylinder_l']} AXIS {$glass['near_axis_l']}\n";
                    }
                    if ($glass['PD_DISTANCE']) {
                        $context .= "PD Distance: {$glass['PD_DISTANCE']} mm\n";
                    }
                    if ($glass['PD_NEAR']) {
                        $context .= "PD Near: {$glass['PD_NEAR']} mm\n";
                    }
                    if ($glass['comments']) {
                        $context .= "Comments: {$glass['comments']}\n";
                    }
                }
                $context .= "\n";
            }

            // Get ALL lab tests for this patient (appointment_id is ignored)
            // Query includes ALL fields from lab_tests table: test_type, test_category, test_name, priority, status, ordered_date, expected_date, notes, results
            $stmt = $this->pdo->prepare("
                SELECT lt.id, lt.test_type, lt.test_category, lt.test_name, lt.priority, lt.status,
                       lt.ordered_date, lt.expected_date, lt.notes, lt.results, lt.created_at, a.date as appointment_date
                FROM lab_tests lt
                JOIN appointments a ON lt.appointment_id = a.id
                WHERE a.patient_id = ?
                ORDER BY lt.created_at DESC
            ");
            $stmt->execute([$patientId]);
            $labTests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($labTests) {
                $context .= "LAB TESTS & RADIOLOGY:\n";
                foreach ($labTests as $test) {
                    $context .= "\n{$test['test_name']} ({$test['test_type']} - {$test['test_category']})\n";
                    if ($test['priority']) {
                        $context .= "Priority: {$test['priority']}\n";
                    }
                    $context .= "Status: {$test['status']}";
                    if ($test['ordered_date']) {
                        $context .= ", Ordered Date: {$test['ordered_date']}";
                    }
                    if ($test['expected_date']) {
                        $context .= ", Expected Date: {$test['expected_date']}";
                    }
                    $context .= "\n";
                    if ($test['notes']) {
                        $context .= "Notes: {$test['notes']}\n";
                    }
                    if ($test['results']) {
                        $context .= "Results: {$test['results']}\n";
                    }
                }
                $context .= "\n";
            }

            // Get ALL medical history entries for this patient (ignoring appointment_id)
            // This retrieves complete medical history from database
            $stmt = $this->pdo->prepare("
                SELECT condition_name, diagnosis_date, category, status, notes, created_at
                FROM medical_history_entries
                WHERE patient_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$patientId]);
            $medicalHistory = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($medicalHistory) {
                $context .= "MEDICAL HISTORY ENTRIES:\n";
                foreach ($medicalHistory as $history) {
                    $context .= "\n- {$history['condition_name']}";
                    if ($history['category']) {
                        $context .= " (Category: {$history['category']})";
                    }
                    $context .= "\n";
                    $context .= "Status: {$history['status']}";
                    if ($history['diagnosis_date']) {
                        $context .= ", Diagnosis Date: {$history['diagnosis_date']}";
                    }
                    if ($history['notes']) {
                        $context .= "\nNotes: {$history['notes']}";
                    }
                    $context .= "\n";
                }
                $context .= "\n";
            }

            // Get ALL patient notes for this patient (ignoring appointment_id)
            // This retrieves complete patient notes history from database
            $stmt = $this->pdo->prepare("
                SELECT title, content, created_at
                FROM patient_notes
                WHERE patient_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$patientId]);
            $notes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($notes) {
                $context .= "PATIENT NOTES:\n";
                foreach ($notes as $note) {
                    $context .= "\n{$note['title']} ({$note['created_at']})\n";
                    $context .= "{$note['content']}\n";
                }
            }

            error_log("buildPatientHistoryContext: Final context length: " . strlen($context) . " characters");
            error_log("buildPatientHistoryContext: Context preview: " . substr($context, 0, 1000));

            return $context;

        } catch (\Exception $e) {
            error_log("Error building patient history context: " . $e->getMessage());
            error_log("Error stack trace: " . $e->getTraceAsString());
            return '';
        }
    }

    /**
     * Build consultation summary context for AI
     */
    private function buildConsultationSummaryContext($appointmentId, $patientId = null)
    {
        try {
            error_log("buildConsultationSummaryContext called with appointmentId: " . $appointmentId . ", patientId: " . ($patientId ?? 'null'));

            // Get current appointment details with ALL consultation note fields
            // Note: consultation_notes table does NOT have a 'notes' column
            $stmt = $this->pdo->prepare("
                SELECT a.id, a.date, a.start_time, a.status, a.visit_type, a.patient_id,
                       p.first_name, p.last_name, p.dob, p.gender,
                       cn.chief_complaint, cn.diagnosis, cn.diagnosis_code, cn.plan, cn.hx_present_illness,
                       cn.visual_acuity_right, cn.visual_acuity_left, cn.refraction_right, cn.refraction_left,
                       cn.IOP_right, cn.IOP_left, cn.slit_lamp_right, cn.slit_lamp_left,
                       cn.fundus_right, cn.fundus_left, cn.external_appearance_right, cn.external_appearance_left,
                       cn.eyelid_right, cn.eyelid_left, cn.systemic_disease, cn.medication, cn.followup_days
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                LEFT JOIN consultation_notes cn ON a.id = cn.appointment_id
                WHERE a.id = ?
            ");
            $stmt->execute([$appointmentId]);
            $appointment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$appointment) {
                error_log("buildConsultationSummaryContext: Appointment not found for ID: " . $appointmentId);
                return '';
            }

            $patientId = $patientId ?: ($appointment['patient_id'] ?? null);
            error_log("buildConsultationSummaryContext: Appointment found - Patient: " . $appointment['first_name'] . " " . $appointment['last_name'] . ", Patient ID: " . $patientId);

            $context = "CURRENT CONSULTATION:\n";
            $context .= "Appointment ID: {$appointment['id']}\n";
            $context .= "Patient: {$appointment['first_name']} {$appointment['last_name']}\n";
            if ($appointment['dob']) {
                $age = date_diff(date_create($appointment['dob']), date_create('now'))->y;
                $context .= "Age: {$age} years\n";
            }
            $context .= "Appointment Date: {$appointment['date']} {$appointment['start_time']}\n";
            $context .= "Visit Type: {$appointment['visit_type']}\n";
            $context .= "Status: {$appointment['status']}\n\n";

            if ($appointment['chief_complaint']) {
                $context .= "Chief Complaint: {$appointment['chief_complaint']}\n";
            }
            if ($appointment['hx_present_illness']) {
                $context .= "History of Present Illness: {$appointment['hx_present_illness']}\n";
            }
            if ($appointment['visual_acuity_right'] || $appointment['visual_acuity_left']) {
                $context .= "Visual Acuity: OD {$appointment['visual_acuity_right']} OS {$appointment['visual_acuity_left']}\n";
            }
            if ($appointment['refraction_right'] || $appointment['refraction_left']) {
                $context .= "Refraction: OD {$appointment['refraction_right']} OS {$appointment['refraction_left']}\n";
            }
            if ($appointment['IOP_right'] || $appointment['IOP_left']) {
                $context .= "IOP: OD {$appointment['IOP_right']} OS {$appointment['IOP_left']}\n";
            }
            if ($appointment['slit_lamp_right'] || $appointment['slit_lamp_left']) {
                $context .= "Slit Lamp: OD {$appointment['slit_lamp_right']} OS {$appointment['slit_lamp_left']}\n";
            }
            if ($appointment['fundus_right'] || $appointment['fundus_left']) {
                $context .= "Fundus: OD {$appointment['fundus_right']} OS {$appointment['fundus_left']}\n";
            }
            if ($appointment['external_appearance_right'] || $appointment['external_appearance_left']) {
                $context .= "External Appearance: OD {$appointment['external_appearance_right']} OS {$appointment['external_appearance_left']}\n";
            }
            if ($appointment['eyelid_right'] || $appointment['eyelid_left']) {
                $context .= "Eyelid: OD {$appointment['eyelid_right']} OS {$appointment['eyelid_left']}\n";
            }
            if ($appointment['diagnosis']) {
                $context .= "Diagnosis: {$appointment['diagnosis']}\n";
            }
            if ($appointment['diagnosis_code']) {
                $context .= "Diagnosis Code: {$appointment['diagnosis_code']}\n";
            }
            if ($appointment['systemic_disease']) {
                $context .= "Systemic Disease: {$appointment['systemic_disease']}\n";
            }
            if ($appointment['medication']) {
                $context .= "Current Medications: {$appointment['medication']}\n";
            }
            if ($appointment['plan']) {
                $context .= "Treatment Plan: {$appointment['plan']}\n";
            }
            if ($appointment['followup_days']) {
                $context .= "Follow-up Days: {$appointment['followup_days']}\n";
            }
            $context .= "\n";

            // Get prescriptions for THIS appointment
            $stmt = $this->pdo->prepare("
                SELECT p.id, p.drug_name, p.dose, p.frequency, p.duration, p.route, p.notes, p.created_at
                FROM prescriptions p
                WHERE p.appointment_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $prescriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("buildConsultationSummaryContext: Found " . count($prescriptions) . " prescriptions for appointment ID: " . $appointmentId);

            if ($prescriptions) {
                $context .= "PRESCRIPTIONS FOR THIS APPOINTMENT:\n";
                foreach ($prescriptions as $med) {
                    $context .= "\n- {$med['drug_name']}";
                    if ($med['dose']) {
                        $context .= " - Dose: {$med['dose']}";
                    }
                    if ($med['frequency']) {
                        $context .= " - Frequency: {$med['frequency']}";
                    }
                    if ($med['duration']) {
                        $context .= " - Duration: {$med['duration']}";
                    }
                    if ($med['route']) {
                        $context .= " - Route: {$med['route']}";
                    }
                    if ($med['notes']) {
                        $context .= " - Notes: {$med['notes']}";
                    }
                    $context .= "\n";
                }
                $context .= "\n";
            }

            // Get lab tests for THIS appointment
            $stmt = $this->pdo->prepare("
                SELECT lt.id, lt.test_type, lt.test_category, lt.test_name, lt.priority, lt.status,
                       lt.ordered_date, lt.expected_date, lt.notes, lt.results, lt.created_at
                FROM lab_tests lt
                WHERE lt.appointment_id = ?
                ORDER BY lt.created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $labTests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("buildConsultationSummaryContext: Found " . count($labTests) . " lab tests for appointment ID: " . $appointmentId);

            if ($labTests) {
                $context .= "LAB TESTS & RADIOLOGY FOR THIS APPOINTMENT:\n";
                foreach ($labTests as $test) {
                    $context .= "\n{$test['test_name']} ({$test['test_type']} - {$test['test_category']})\n";
                    if ($test['priority']) {
                        $context .= "Priority: {$test['priority']}\n";
                    }
                    $context .= "Status: {$test['status']}";
                    if ($test['ordered_date']) {
                        $context .= ", Ordered Date: {$test['ordered_date']}";
                    }
                    if ($test['expected_date']) {
                        $context .= ", Expected Date: {$test['expected_date']}";
                    }
                    $context .= "\n";
                    if ($test['notes']) {
                        $context .= "Notes: {$test['notes']}\n";
                    }
                    if ($test['results']) {
                        $context .= "Results: {$test['results']}\n";
                    }
                }
                $context .= "\n";
            }

            // Get glasses prescriptions for THIS appointment
            $stmt = $this->pdo->prepare("
                SELECT gp.id, gp.lens_type,
                       gp.distance_sphere_r, gp.distance_cylinder_r, gp.distance_axis_r,
                       gp.distance_sphere_l, gp.distance_cylinder_l, gp.distance_axis_l,
                       gp.near_sphere_r, gp.near_cylinder_r, gp.near_axis_r,
                       gp.near_sphere_l, gp.near_cylinder_l, gp.near_axis_l,
                       gp.PD_DISTANCE, gp.PD_NEAR, gp.comments, gp.created_at
                FROM glasses_prescriptions gp
                WHERE gp.appointment_id = ?
                ORDER BY gp.created_at DESC
            ");
            $stmt->execute([$appointmentId]);
            $glasses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("buildConsultationSummaryContext: Found " . count($glasses) . " glasses prescriptions for appointment ID: " . $appointmentId);

            if ($glasses) {
                $context .= "GLASSES PRESCRIPTIONS FOR THIS APPOINTMENT:\n";
                foreach ($glasses as $glass) {
                    $context .= "\nLens Type: {$glass['lens_type']}\n";
                    if ($glass['distance_sphere_r'] || $glass['distance_sphere_l'] || $glass['distance_cylinder_r'] || $glass['distance_cylinder_l']) {
                        $context .= "Distance Vision:\n";
                        $context .= "  OD: SPH {$glass['distance_sphere_r']} CYL {$glass['distance_cylinder_r']} AXIS {$glass['distance_axis_r']}\n";
                        $context .= "  OS: SPH {$glass['distance_sphere_l']} CYL {$glass['distance_cylinder_l']} AXIS {$glass['distance_axis_l']}\n";
                    }
                    if ($glass['near_sphere_r'] || $glass['near_sphere_l'] || $glass['near_cylinder_r'] || $glass['near_cylinder_l']) {
                        $context .= "Near Vision:\n";
                        $context .= "  OD: SPH {$glass['near_sphere_r']} CYL {$glass['near_cylinder_r']} AXIS {$glass['near_axis_r']}\n";
                        $context .= "  OS: SPH {$glass['near_sphere_l']} CYL {$glass['near_cylinder_l']} AXIS {$glass['near_axis_l']}\n";
                    }
                    if ($glass['PD_DISTANCE']) {
                        $context .= "PD Distance: {$glass['PD_DISTANCE']} mm\n";
                    }
                    if ($glass['PD_NEAR']) {
                        $context .= "PD Near: {$glass['PD_NEAR']} mm\n";
                    }
                    if ($glass['comments']) {
                        $context .= "Comments: {$glass['comments']}\n";
                    }
                }
                $context .= "\n";
            }

            if ($patientId) {
                // Get recent appointments (last 6 months) for context
                $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));
                $stmt = $this->pdo->prepare("
                    SELECT a.id, a.date, a.start_time, a.status,
                           cn.chief_complaint, cn.diagnosis
                    FROM appointments a
                    LEFT JOIN consultation_notes cn ON a.id = cn.appointment_id
                    WHERE a.patient_id = ? AND a.id != ? AND a.date >= ?
                    ORDER BY a.date DESC
                    LIMIT 5
                ");
                $stmt->execute([$patientId, $appointmentId, $sixMonthsAgo]);
                $recentAppointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if ($recentAppointments) {
                    $context .= "RECENT APPOINTMENTS (Last 6 months) - For Context:\n";
                    foreach ($recentAppointments as $apt) {
                        $context .= "\n{$apt['date']} - ";
                        if ($apt['chief_complaint']) {
                            $context .= "Complaint: {$apt['chief_complaint']}";
                        }
                        if ($apt['diagnosis']) {
                            $context .= ", Diagnosis: {$apt['diagnosis']}";
                        }
                        $context .= "\n";
                    }
                    $context .= "\n";
                }
            }

            error_log("buildConsultationSummaryContext: Final context length: " . strlen($context) . " characters");
            error_log("buildConsultationSummaryContext: Context preview: " . substr($context, 0, 1000));

            return $context;

        } catch (\Exception $e) {
            error_log("Error building consultation summary context: " . $e->getMessage());
            return '';
        }
    }

    // ============================================
    // Patient Folders API Endpoints
    // ============================================

    /**
     * GET /api/patient-folders
     * Get all folders (custom + system folders by doctor)
     */
    public function getPatientFolders()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);

            // Get custom folders (only top-level, no sub-folders)
            $customFoldersStmt = $this->pdo->prepare("
                SELECT pf.*, 
                       COUNT(DISTINCT pfp.patient_id) as patient_count
                FROM patient_folders pf
                LEFT JOIN patient_folder_patients pfp ON pf.id = pfp.folder_id
                WHERE (pf.doctor_id IS NULL OR pf.doctor_id = ?)
                AND pf.parent_id IS NULL
                GROUP BY pf.id
                ORDER BY pf.created_at DESC
            ");
            $customFoldersStmt->execute([$doctorId]);
            $customFolders = $customFoldersStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get system folders (grouped by treating doctor)
            // System folders are computed dynamically
            $systemFoldersStmt = $this->pdo->prepare("
                SELECT d.id as doctor_id,
                       d.display_name as doctor_name,
                       u.profile_image,
                       COUNT(DISTINCT p.id) as patient_count
                FROM doctors d
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN timeline_events te ON te.actor_user_id = d.user_id 
                    AND te.event_type = 'Booking' 
                    AND te.event_summary LIKE '%New patient registered%'
                LEFT JOIN patients p ON te.patient_id = p.id
                WHERE d.id IS NOT NULL
                GROUP BY d.id, d.display_name, u.profile_image
                HAVING patient_count > 0
                ORDER BY d.display_name
            ");
            $systemFoldersStmt->execute();
            $systemFoldersData = $systemFoldersStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Format system folders
            $systemFolders = [];
            foreach ($systemFoldersData as $sf) {
                // Format profile image path
                $profileImage = null;
                if (!empty($sf['profile_image'])) {
                    $profileImage = strpos($sf['profile_image'], '/public/') === 0 
                        ? $sf['profile_image'] 
                        : '/public' . $sf['profile_image'];
                }
                
                $systemFolders[] = [
                    'id' => 'system_' . $sf['doctor_id'],
                    'doctor_id' => $sf['doctor_id'],
                    'name' => $sf['doctor_name'] . ' Patients',
                    'type' => 'system',
                    'patient_count' => (int)$sf['patient_count'],
                    'profile_image' => $profileImage,
                    'icon' => 'bi-folder-fill',
                    'gradient_color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'created_at' => null,
                    'updated_at' => null
                ];
            }

            // Format custom folders (only top-level, no sub-folders)
            $formattedCustomFolders = [];
            foreach ($customFolders as $cf) {
                // Only include folders without parent (top-level folders)
                if (empty($cf['parent_id'])) {
                    // Get sub-folders count for this folder
                    $subFoldersStmt = $this->pdo->prepare("
                        SELECT COUNT(*) as sub_count 
                        FROM patient_folders 
                        WHERE parent_id = ? AND parent_type = 'custom'
                    ");
                    $subFoldersStmt->execute([$cf['id']]);
                    $subFoldersCount = $subFoldersStmt->fetchColumn();
                    
                    $formattedCustomFolders[] = [
                        'id' => $cf['id'],
                        'doctor_id' => $cf['doctor_id'],
                        'name' => $cf['name'],
                        'type' => 'custom',
                        'patient_count' => (int)$cf['patient_count'],
                        'sub_folders_count' => (int)$subFoldersCount,
                        'icon' => $cf['icon'] ?? 'bi-folder',
                        'gradient_color' => $cf['gradient_color'] ?? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        'created_at' => $cf['created_at'],
                        'updated_at' => $cf['updated_at']
                    ];
                }
            }

            // Get sub-folders count for system folders
            foreach ($systemFolders as &$sf) {
                // Sub-folders for system folders are stored with parent_type = 'system' and parent_id = NULL
                // We match by doctor_id instead
                $subFoldersStmt = $this->pdo->prepare("
                    SELECT COUNT(*) as sub_count
                    FROM patient_folders
                    WHERE parent_type = 'system'
                    AND parent_id IS NULL
                    AND doctor_id = ?
                ");
                $subFoldersStmt->execute([$sf['doctor_id']]);
                $subFoldersCount = $subFoldersStmt->fetchColumn();
                $sf['sub_folders_count'] = (int)$subFoldersCount;
            }
            unset($sf);

            /* Clinic-based system folders — append one folder per active clinic
               grouping patients by the clinic of their MOST RECENT appointment.
               This is what the user means by "sort/group by clinic". A patient
               is in exactly one clinic folder (the one of their last visit),
               so the counts don't double-count. */
            $clinicFoldersStmt = $this->pdo->prepare("
                SELECT c.id, c.code, c.name_ar, c.name_en,
                       COUNT(DISTINCT lc.patient_id) AS patient_count
                FROM clinics c
                LEFT JOIN (
                    SELECT patient_id, clinic_id,
                           ROW_NUMBER() OVER (
                               PARTITION BY patient_id
                               ORDER BY date DESC, start_time DESC, id DESC
                           ) AS rn
                    FROM appointments
                    WHERE clinic_id IS NOT NULL
                ) lc ON lc.clinic_id = c.id AND lc.rn = 1
                WHERE c.is_active = 1
                GROUP BY c.id, c.code, c.name_ar, c.name_en
                ORDER BY c.sort_order ASC, c.id ASC
            ");
            $clinicFoldersStmt->execute();
            $clinicFoldersRaw = $clinicFoldersStmt->fetchAll(\PDO::FETCH_ASSOC);

            $clinicThemes = [
                'RIYADH' => 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
                'KFS'    => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
            ];

            foreach ($clinicFoldersRaw as $cf) {
                $label = $cf['name_ar'] ?: $cf['name_en'] ?: ('Clinic #' . $cf['id']);
                /* id is pre-prefixed with `clinic_` (mirrors how doctor system
                   folders use `system_X`). The JS lookup tables compare by id
                   exactly, so the id stored here must match the id used when
                   the user clicks on the folder. */
                $systemFolders[] = [
                    'id' => 'clinic_' . (int)$cf['id'],
                    'doctor_id' => null,
                    'clinic_id' => (int)$cf['id'],
                    'clinic_code' => $cf['code'],
                    'name' => $label,
                    'type' => 'system',
                    'group' => 'clinic',
                    'patient_count' => (int)$cf['patient_count'],
                    'profile_image' => null,
                    'icon' => 'bi-building-fill',
                    'gradient_color' => $clinicThemes[$cf['code']] ?? 'linear-gradient(135deg, #64748b 0%, #475569 100%)',
                    'sub_folders_count' => 0,
                    'created_at' => null,
                    'updated_at' => null,
                ];
            }

            return $this->jsonResponse([
                'ok' => true,
                'system_folders' => $systemFolders,
                'custom_folders' => $formattedCustomFolders
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/patient-folders
     * Create a new custom folder
     */
    public function createPatientFolder()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            $data = json_decode(file_get_contents('php://input'), true);

            $name = trim($data['name'] ?? '');
            $folderDoctorId = isset($data['doctor_id']) ? (int)$data['doctor_id'] : null;
            $parentId = isset($data['parent_id']) ? $data['parent_id'] : null;
            $parentType = trim($data['parent_type'] ?? '');

            if (empty($name)) {
                return $this->jsonResponse(['error' => 'Folder name is required'], 400);
            }

            // If doctor_id is provided, ensure it matches current doctor (for private folders)
            // Allow creating folders for any doctor (no permission check)

            // If creating a sub-folder, validate parent
            $dbParentId = null;
            $dbParentType = null;
            
            if ($parentId !== null) {
                if (empty($parentType) || !in_array($parentType, ['system', 'custom'])) {
                    return $this->jsonResponse(['error' => 'Invalid parent type'], 400);
                }

                if ($parentType === 'system') {
                    // For system folders, parent_id is like "system_1", extract doctor_id
                    $systemDoctorId = str_replace('system_', '', $parentId);
                    if (!is_numeric($systemDoctorId)) {
                        return $this->jsonResponse(['error' => 'Invalid system folder ID'], 400);
                    }
                    // System folders don't exist in DB, so we use NULL for parent_id to avoid FK constraint
                    // We'll store the doctor_id in the name or use a special format, but actually
                    // we can query by parent_type='system' and doctor_id match
                    // Better: store doctor_id in parent_id as NULL and use a separate approach
                    // Actually, we need to store doctor_id somewhere. Let's use a workaround:
                    // Store doctor_id in parent_id as NULL, and use parent_type + doctor_id matching
                    $dbParentId = null; // NULL to avoid FK constraint
                    $dbParentType = 'system';
                    // Store system doctor_id in doctor_id field of the sub-folder
                    $folderDoctorId = (int)$systemDoctorId;
                } else {
                // For custom folders, verify parent exists
                $parentCheckStmt = $this->pdo->prepare("
                    SELECT id FROM patient_folders WHERE id = ?
                ");
                $parentCheckStmt->execute([$parentId]);
                $parent = $parentCheckStmt->fetch();

                if (!$parent) {
                    return $this->jsonResponse(['error' => 'Parent folder not found'], 404);
                }
                    
                    $dbParentId = (int)$parentId;
                    $dbParentType = 'custom';
                }
            }

            // For system folders, store the system doctor_id in doctor_id field
            // and use NULL for parent_id to avoid FK constraint
            if ($parentType === 'system' && isset($systemDoctorId)) {
                $folderDoctorId = (int)$systemDoctorId;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO patient_folders (doctor_id, name, created_by_user_id, parent_id, parent_type)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$folderDoctorId, $name, $user['id'], $dbParentId, $dbParentType]);
            $folderId = $this->pdo->lastInsertId();

            return $this->jsonResponse([
                'ok' => true,
                'folder' => [
                    'id' => $folderId,
                    'doctor_id' => $folderDoctorId,
                    'name' => $name,
                    'type' => 'custom',
                    'parent_id' => $parentId,
                    'parent_type' => $dbParentType,
                    'patient_count' => 0,
                    'sub_folders_count' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => null
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/patient-folders/{id}/sub-folders
     * Get sub-folders for a parent folder
     */
    public function getSubFolders($parentId, $parentType)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);

            if ($parentType === 'system') {
                // Clinic system folders are leaf — they never have sub-folders.
                if (strpos((string)$parentId, 'clinic_') === 0) {
                    return $this->jsonResponse(['ok' => true, 'sub_folders' => []]);
                }
                // Extract doctor_id from system folder ID (system_1 -> 1)
                $systemDoctorId = str_replace('system_', '', $parentId);
                if (!is_numeric($systemDoctorId)) {
                    return $this->jsonResponse(['error' => 'Invalid system folder ID'], 400);
                }

                // Get sub-folders for this system folder
                // parent_id is NULL for system folders, so we match by parent_type='system' and doctor_id
                $stmt = $this->pdo->prepare("
                    SELECT pf.*,
                           COUNT(DISTINCT pfp.patient_id) as patient_count
                    FROM patient_folders pf
                    LEFT JOIN patient_folder_patients pfp ON pf.id = pfp.folder_id
                    WHERE pf.parent_type = 'system' 
                    AND pf.parent_id IS NULL
                    AND pf.doctor_id = ?
                    GROUP BY pf.id
                    ORDER BY pf.name
                ");
                $stmt->execute([(int)$systemDoctorId]);
            } else {
                // For custom folders, verify parent exists
                $parentCheckStmt = $this->pdo->prepare("
                    SELECT id FROM patient_folders WHERE id = ?
                ");
                $parentCheckStmt->execute([$parentId]);
                $parent = $parentCheckStmt->fetch();

                if (!$parent) {
                    return $this->jsonResponse(['error' => 'Parent folder not found'], 404);
                }

                // Get sub-folders
                $stmt = $this->pdo->prepare("
                    SELECT pf.*,
                           COUNT(DISTINCT pfp.patient_id) as patient_count
                    FROM patient_folders pf
                    LEFT JOIN patient_folder_patients pfp ON pf.id = pfp.folder_id
                    WHERE pf.parent_id = ? AND pf.parent_type = 'custom'
                    GROUP BY pf.id
                    ORDER BY pf.name
                ");
                $stmt->execute([$parentId]);
            }

            $subFolders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $formattedSubFolders = [];
            foreach ($subFolders as $sf) {
                $formattedSubFolders[] = [
                    'id' => $sf['id'],
                    'name' => $sf['name'],
                    'type' => 'custom',
                    'patient_count' => (int)$sf['patient_count'],
                    'icon' => $sf['icon'] ?? 'bi-folder',
                    'gradient_color' => $sf['gradient_color'] ?? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                    'created_at' => $sf['created_at'],
                    'updated_at' => $sf['updated_at'],
                    'parent_id' => $sf['parent_id'] ?? $id, // Current folder is the parent
                    'parent_type' => $sf['parent_type'] ?? ($folder['parent_type'] ?? 'custom')
                ];
            }

            return $this->jsonResponse([
                'ok' => true,
                'sub_folders' => $formattedSubFolders
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/patient-folders/{id}/quick-sort
     * Quick sort system folder patients into sub-folders
     */
    public function quickSortSystemFolder($systemFolderId, $sortType)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);

            // Extract doctor_id from system folder ID
            $systemDoctorId = str_replace('system_', '', $systemFolderId);
            if (!is_numeric($systemDoctorId)) {
                return $this->jsonResponse(['error' => 'Invalid system folder ID'], 400);
            }

            if (!in_array($sortType, ['by_date_created', 'by_visits'])) {
                return $this->jsonResponse(['error' => 'Invalid sort type'], 400);
            }

            // Get all patients for this system folder
            $patientsStmt = $this->pdo->prepare("
                SELECT DISTINCT p.*
                FROM patients p
                INNER JOIN timeline_events te ON te.patient_id = p.id
                INNER JOIN users u ON te.actor_user_id = u.id
                INNER JOIN doctors d ON u.id = d.user_id
                WHERE d.id = ?
                AND te.event_type = 'Booking'
                AND te.event_summary LIKE '%New patient registered%'
                ORDER BY p.first_name, p.last_name
            ");
            $patientsStmt->execute([$systemDoctorId]);
            $patients = $patientsStmt->fetchAll(\PDO::FETCH_ASSOC);

            $subFoldersCreated = [];
            $patientsDistributed = 0;

            if ($sortType === 'by_date_created') {
                // Group by Year - Month
                $groupedPatients = [];
                foreach ($patients as $patient) {
                    $createdDate = new \DateTime($patient['created_at']);
                    $year = $createdDate->format('Y');
                    $month = $createdDate->format('F'); // Full month name
                    $key = $year . ' - ' . $month;
                    
                    if (!isset($groupedPatients[$key])) {
                        $groupedPatients[$key] = [];
                    }
                    $groupedPatients[$key][] = $patient;
                }

                // Create sub-folders and distribute patients
                foreach ($groupedPatients as $folderName => $folderPatients) {
                    // Check if sub-folder already exists
                    $checkStmt = $this->pdo->prepare("
                        SELECT id FROM patient_folders 
                        WHERE parent_type = 'system' 
                        AND parent_id IS NULL
                        AND doctor_id = ?
                        AND name = ?
                    ");
                    $checkStmt->execute([$systemDoctorId, $folderName]);
                    $existingFolder = $checkStmt->fetch();

                    if ($existingFolder) {
                        $subFolderId = $existingFolder['id'];
                    } else {
                        // Create new sub-folder
                        $createStmt = $this->pdo->prepare("
                            INSERT INTO patient_folders (name, doctor_id, created_by_user_id, parent_id, parent_type)
                            VALUES (?, ?, ?, NULL, 'system')
                        ");
                        $createStmt->execute([$folderName, $systemDoctorId, $user['id']]);
                        $subFolderId = $this->pdo->lastInsertId();
                    }

                    // Add patients to sub-folder
                    foreach ($folderPatients as $patient) {
                        // Check if patient already in folder
                        $checkPatientStmt = $this->pdo->prepare("
                            SELECT folder_id FROM patient_folder_patients 
                            WHERE folder_id = ? AND patient_id = ?
                        ");
                        $checkPatientStmt->execute([$subFolderId, $patient['id']]);
                        if (!$checkPatientStmt->fetch()) {
                            $insertStmt = $this->pdo->prepare("
                                INSERT INTO patient_folder_patients (folder_id, patient_id)
                                VALUES (?, ?)
                            ");
                            $insertStmt->execute([$subFolderId, $patient['id']]);
                            $patientsDistributed++;
                        }
                    }

                    $subFoldersCreated[] = [
                        'name' => $folderName,
                        'id' => $subFolderId,
                        'patient_count' => count($folderPatients)
                    ];
                }

            } elseif ($sortType === 'by_visits') {
                // Group by With Visits / Without Visits
                $withVisits = [];
                $withoutVisits = [];

                foreach ($patients as $patient) {
                    // Check if patient has appointments
                    $visitsStmt = $this->pdo->prepare("
                        SELECT COUNT(*) FROM appointments WHERE patient_id = ?
                    ");
                    $visitsStmt->execute([$patient['id']]);
                    $visitCount = $visitsStmt->fetchColumn();

                    if ($visitCount > 0) {
                        $withVisits[] = $patient;
                    } else {
                        $withoutVisits[] = $patient;
                    }
                }

                // Create "With Visits" sub-folder
                if (!empty($withVisits)) {
                    $folderName = 'With Visits';
                    $checkStmt = $this->pdo->prepare("
                        SELECT id FROM patient_folders 
                        WHERE parent_type = 'system' 
                        AND parent_id IS NULL
                        AND doctor_id = ?
                        AND name = ?
                    ");
                    $checkStmt->execute([$systemDoctorId, $folderName]);
                    $existingFolder = $checkStmt->fetch();

                    if ($existingFolder) {
                        $subFolderId = $existingFolder['id'];
                    } else {
                        $createStmt = $this->pdo->prepare("
                            INSERT INTO patient_folders (name, doctor_id, created_by_user_id, parent_id, parent_type)
                            VALUES (?, ?, ?, NULL, 'system')
                        ");
                        $createStmt->execute([$folderName, $systemDoctorId, $user['id']]);
                        $subFolderId = $this->pdo->lastInsertId();
                    }

                    foreach ($withVisits as $patient) {
                        $checkPatientStmt = $this->pdo->prepare("
                            SELECT folder_id FROM patient_folder_patients 
                            WHERE folder_id = ? AND patient_id = ?
                        ");
                        $checkPatientStmt->execute([$subFolderId, $patient['id']]);
                        if (!$checkPatientStmt->fetch()) {
                            $insertStmt = $this->pdo->prepare("
                                INSERT INTO patient_folder_patients (folder_id, patient_id)
                                VALUES (?, ?)
                            ");
                            $insertStmt->execute([$subFolderId, $patient['id']]);
                            $patientsDistributed++;
                        }
                    }

                    $subFoldersCreated[] = [
                        'name' => $folderName,
                        'id' => $subFolderId,
                        'patient_count' => count($withVisits)
                    ];
                }

                // Create "Without Visits" sub-folder
                if (!empty($withoutVisits)) {
                    $folderName = 'Without Visits';
                    $checkStmt = $this->pdo->prepare("
                        SELECT id FROM patient_folders 
                        WHERE parent_type = 'system' 
                        AND parent_id IS NULL
                        AND doctor_id = ?
                        AND name = ?
                    ");
                    $checkStmt->execute([$systemDoctorId, $folderName]);
                    $existingFolder = $checkStmt->fetch();

                    if ($existingFolder) {
                        $subFolderId = $existingFolder['id'];
                    } else {
                        $createStmt = $this->pdo->prepare("
                            INSERT INTO patient_folders (name, doctor_id, created_by_user_id, parent_id, parent_type)
                            VALUES (?, ?, ?, NULL, 'system')
                        ");
                        $createStmt->execute([$folderName, $systemDoctorId, $user['id']]);
                        $subFolderId = $this->pdo->lastInsertId();
                    }

                    foreach ($withoutVisits as $patient) {
                        $checkPatientStmt = $this->pdo->prepare("
                            SELECT folder_id FROM patient_folder_patients 
                            WHERE folder_id = ? AND patient_id = ?
                        ");
                        $checkPatientStmt->execute([$subFolderId, $patient['id']]);
                        if (!$checkPatientStmt->fetch()) {
                            $insertStmt = $this->pdo->prepare("
                                INSERT INTO patient_folder_patients (folder_id, patient_id)
                                VALUES (?, ?)
                            ");
                            $insertStmt->execute([$subFolderId, $patient['id']]);
                            $patientsDistributed++;
                        }
                    }

                    $subFoldersCreated[] = [
                        'name' => $folderName,
                        'id' => $subFolderId,
                        'patient_count' => count($withoutVisits)
                    ];
                }
            }

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Patients sorted successfully',
                'sub_folders_created' => $subFoldersCreated,
                'patients_distributed' => $patientsDistributed
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/patient-folders/{id}
     * Rename a custom folder
     */
    public function updatePatientFolder($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            $data = json_decode(file_get_contents('php://input'), true);

            $name = trim($data['name'] ?? '');
            $icon = trim($data['icon'] ?? '');
            $gradientColor = trim($data['gradient_color'] ?? '');

            // Handle system folders first (they don't exist in database)
            if (strpos($id, 'system_') === 0) {
                // System folders customization should be stored client-side (localStorage)
                // For now, we'll return success and let the client handle storage
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'System folder customization saved (stored client-side)',
                    'icon' => $icon,
                    'gradient_color' => $gradientColor
                ]);
            }

            // Check if folder exists
            $checkStmt = $this->pdo->prepare("
                SELECT id FROM patient_folders WHERE id = ?
            ");
            $checkStmt->execute([$id]);
            $folder = $checkStmt->fetch();

            if (!$folder) {
                return $this->jsonResponse(['error' => 'Folder not found'], 404);
            }

            // Build update query dynamically based on provided fields
            $updateFields = [];
            $updateValues = [];
            
            if (!empty($name)) {
                $updateFields[] = "name = ?";
                $updateValues[] = $name;
            }
            
            if (!empty($icon)) {
                $updateFields[] = "icon = ?";
                $updateValues[] = $icon;
            }
            
            if (!empty($gradientColor)) {
                $updateFields[] = "gradient_color = ?";
                $updateValues[] = $gradientColor;
            }
            
            if (empty($updateFields)) {
                return $this->jsonResponse(['error' => 'No fields to update'], 400);
            }
            
            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            $updateValues[] = $id;
            
            $stmt = $this->pdo->prepare("
                UPDATE patient_folders 
                SET " . implode(', ', $updateFields) . "
                WHERE id = ?
            ");
            $stmt->execute($updateValues);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Folder updated successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/patient-folders/{id}
     * Delete a custom folder
     */
    public function deletePatientFolder($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);

            // Check if folder exists and user has permission
            // For subfolders, we need to check parent folder permissions too
            $checkStmt = $this->pdo->prepare("
                SELECT id, doctor_id, parent_id, parent_type FROM patient_folders WHERE id = ?
            ");
            $checkStmt->execute([$id]);
            $folder = $checkStmt->fetch();

            if (!$folder) {
                return $this->jsonResponse(['error' => 'Folder not found'], 404);
            }

            // No permission check - all doctors can access all folders

            // Delete folder (CASCADE will delete patient mappings)
            $stmt = $this->pdo->prepare("DELETE FROM patient_folders WHERE id = ?");
            $stmt->execute([$id]);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Folder deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete patient folders
     * DELETE /api/patient-folders/bulk
     */
    public function bulkDeletePatientFolders()
    {
        error_log("bulkDeletePatientFolders: Function called");
        try {
            if (!$this->auth->check()) {
                error_log("bulkDeletePatientFolders: Unauthorized");
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $folderIds = $input['folder_ids'] ?? [];
            
            error_log("bulkDeletePatientFolders: Received folder_ids: " . json_encode($folderIds));

            if (empty($folderIds) || !is_array($folderIds)) {
                return $this->jsonResponse(['error' => 'No folder IDs provided'], 400);
            }

            // Validate all folder IDs exist
            $placeholders = implode(',', array_fill(0, count($folderIds), '?'));
            $checkStmt = $this->pdo->prepare("SELECT id FROM patient_folders WHERE id IN ($placeholders)");
            $checkStmt->execute($folderIds);
            $existingFolders = $checkStmt->fetchAll(\PDO::FETCH_COLUMN);

            if (count($existingFolders) !== count($folderIds)) {
                return $this->jsonResponse(['error' => 'Some folders not found'], 404);
            }

            // Delete all folders (CASCADE will handle patient_folder_patients)
            $deleteStmt = $this->pdo->prepare("DELETE FROM patient_folders WHERE id IN ($placeholders)");
            $deleteStmt->execute($folderIds);

            return $this->jsonResponse([
                'ok' => true,
                'message' => count($folderIds) . ' folder(s) deleted successfully',
                'deleted_count' => count($folderIds)
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/patient-folders/{id}/patients
     * Add a patient to a folder
     */
    public function addPatientToFolder($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            $data = json_decode(file_get_contents('php://input'), true);

            $patientId = (int)($data['patient_id'] ?? 0);

            if ($patientId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID'], 400);
            }

            // Check if folder exists and user has permission
            // For subfolders, we need to check parent folder permissions too
            $checkStmt = $this->pdo->prepare("
                SELECT id, doctor_id, parent_id, parent_type FROM patient_folders WHERE id = ?
            ");
            $checkStmt->execute([$id]);
            $folder = $checkStmt->fetch();

            if (!$folder) {
                return $this->jsonResponse(['error' => 'Folder not found'], 404);
            }

            // No permission check - all doctors can access all folders

            // Check if patient exists
            $patientStmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $patientStmt->execute([$patientId]);
            if (!$patientStmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Check if this is a "move" operation (remove from all other folders first)
            $isMove = $data['move'] ?? false;
            
            if ($isMove) {
                // Remove patient from all folders first
                $removeStmt = $this->pdo->prepare("
                    DELETE FROM patient_folder_patients 
                    WHERE patient_id = ?
                ");
                $removeStmt->execute([$patientId]);
            } else {
                // Check if already in folder
                $existsStmt = $this->pdo->prepare("
                    SELECT folder_id FROM patient_folder_patients 
                    WHERE folder_id = ? AND patient_id = ?
                ");
                $existsStmt->execute([$id, $patientId]);
                if ($existsStmt->fetch()) {
                    return $this->jsonResponse([
                        'ok' => true,
                        'message' => 'Patient already in folder'
                    ]);
                }
            }

            // Add patient to folder
            $stmt = $this->pdo->prepare("
                INSERT INTO patient_folder_patients (folder_id, patient_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$id, $patientId]);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Patient added to folder successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/patient-folders/{id}/patients/{patient_id}
     * Remove a patient from a folder
     */
    public function removePatientFromFolder($id, $patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            $patientId = (int)$patientId;

            if ($patientId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID'], 400);
            }

            // Check if folder exists and user has permission
            // For subfolders, we need to check parent folder permissions too
            $checkStmt = $this->pdo->prepare("
                SELECT id, doctor_id, parent_id, parent_type FROM patient_folders WHERE id = ?
            ");
            $checkStmt->execute([$id]);
            $folder = $checkStmt->fetch();

            if (!$folder) {
                return $this->jsonResponse(['error' => 'Folder not found'], 404);
            }

            // No permission check - all doctors can access all folders

            // Remove patient from folder
            $stmt = $this->pdo->prepare("
                DELETE FROM patient_folder_patients 
                WHERE folder_id = ? AND patient_id = ?
            ");
            $stmt->execute([$id, $patientId]);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Patient removed from folder successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/patient-folders/{id}/patients
     * Get patients in a folder
     */
    public function getFolderPatients($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);

            // Handle clinic system folders (clinic_X) — patients whose MOST
            // RECENT appointment is in this clinic.
            if (strpos($id, 'clinic_') === 0) {
                $clinicFolderId = (int)str_replace('clinic_', '', $id);
                if ($clinicFolderId <= 0) {
                    return $this->jsonResponse(['error' => 'Invalid clinic folder ID'], 400);
                }

                // Get clinic name for breadcrumb
                $cStmt = $this->pdo->prepare("SELECT name_ar, name_en FROM clinics WHERE id = ?");
                $cStmt->execute([$clinicFolderId]);
                $cRow = $cStmt->fetch(\PDO::FETCH_ASSOC);
                $clinicName = $cRow ? ($cRow['name_ar'] ?: $cRow['name_en']) : ('Clinic #' . $clinicFolderId);

                $stmt = $this->pdo->prepare("
                    SELECT p.*,
                           COUNT(DISTINCT a.id) as total_appointments,
                           MAX(a.date) as last_visit,
                           MAX(CONCAT(a.date, ' ', a.start_time)) as last_appointment_datetime,
                           COUNT(DISTINCT pr.id) as prescriptions_count,
                           COUNT(DISTINCT gp.id) as glasses_count,
                           (SELECT pa.id
                            FROM patient_attachments pa
                            LEFT JOIN appointments aa ON pa.appointment_id = aa.id
                            WHERE pa.patient_id = p.id AND pa.mime_type LIKE 'image/%'
                            ORDER BY CASE WHEN aa.id IS NOT NULL THEN 0 ELSE 1 END ASC,
                                     CASE WHEN aa.id IS NOT NULL
                                          THEN CONCAT(aa.date, ' ', COALESCE(aa.start_time, '00:00:00'))
                                          ELSE '0000-00-00 00:00:00' END DESC,
                                     pa.created_at DESC
                            LIMIT 1) as latest_attachment_id,
                           (SELECT d.display_name
                            FROM timeline_events te2
                            LEFT JOIN users u2 ON te2.actor_user_id = u2.id
                            LEFT JOIN doctors d ON u2.id = d.user_id
                            WHERE te2.patient_id = p.id
                            AND te2.event_type = 'Booking'
                            AND te2.event_summary LIKE '%New patient registered%'
                            ORDER BY te2.created_at ASC
                            LIMIT 1) as created_by_doctor_name,
                           last_clinic.id   as last_clinic_id,
                           last_clinic.code as last_clinic_code,
                           last_clinic.name_ar as last_clinic_name_ar,
                           last_clinic.name_en as last_clinic_name_en
                    FROM patients p
                    INNER JOIN (
                        SELECT patient_id, clinic_id,
                               ROW_NUMBER() OVER (
                                   PARTITION BY patient_id
                                   ORDER BY date DESC, start_time DESC, id DESC
                               ) AS rn
                        FROM appointments
                        WHERE clinic_id IS NOT NULL
                    ) latest_appt ON latest_appt.patient_id = p.id AND latest_appt.rn = 1 AND latest_appt.clinic_id = ?
                    LEFT JOIN clinics last_clinic ON last_clinic.id = latest_appt.clinic_id
                    LEFT JOIN appointments a ON p.id = a.patient_id
                    LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
                    LEFT JOIN glasses_prescriptions gp ON a.id = gp.appointment_id
                    GROUP BY p.id
                    ORDER BY p.first_name, p.last_name
                ");
                $stmt->execute([$clinicFolderId]);
                $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($patients as &$patient) {
                    if (!isset($patient['latest_attachment_id'])) {
                        $patient['latest_attachment_id'] = null;
                    }
                    if ($patient['latest_attachment_id'] === '') {
                        $patient['latest_attachment_id'] = null;
                    }
                }
                unset($patient);

                return $this->jsonResponse([
                    'ok' => true,
                    'folder' => [
                        'id' => $id,
                        'name' => $clinicName,
                        'type' => 'system',
                        'group' => 'clinic',
                    ],
                    'patients' => $patients,
                ]);
            }

            // Handle system folders first (before checking database)
            if (strpos($id, 'system_') === 0) {
                $systemDoctorId = (int)str_replace('system_', '', $id);
                
                if ($systemDoctorId <= 0) {
                    return $this->jsonResponse(['error' => 'Invalid system folder ID'], 400);
                }
                
                // Get doctor name for breadcrumb
                $doctorStmt = $this->pdo->prepare("SELECT display_name FROM doctors WHERE id = ?");
                $doctorStmt->execute([$systemDoctorId]);
                $doctor = $doctorStmt->fetch();
                $doctorName = $doctor ? $doctor['display_name'] : "Doctor #{$systemDoctorId}";
                
                // Get patients for this doctor (system folder) with latest_attachment_id and created_by_doctor_name
                $stmt = $this->pdo->prepare("
                    SELECT DISTINCT p.*,
                           COUNT(DISTINCT a.id) as total_appointments,
                           MAX(a.date) as last_visit,
                           MAX(CONCAT(a.date, ' ', a.start_time)) as last_appointment_datetime,
                           COUNT(DISTINCT pr.id) as prescriptions_count,
                           COUNT(DISTINCT gp.id) as glasses_count,
                           (SELECT pa.id 
                            FROM patient_attachments pa 
                            LEFT JOIN appointments a ON pa.appointment_id = a.id
                            WHERE pa.patient_id = p.id 
                            AND pa.mime_type LIKE 'image/%'
                            ORDER BY 
                                CASE 
                                    WHEN a.id IS NOT NULL 
                                    THEN 0
                                    ELSE 1
                                END ASC,
                                CASE 
                                    WHEN a.id IS NOT NULL 
                                    THEN CONCAT(a.date, ' ', COALESCE(a.start_time, '00:00:00'))
                                    ELSE '0000-00-00 00:00:00'
                                END DESC,
                                pa.created_at DESC 
                            LIMIT 1) as latest_attachment_id,
                           (SELECT d.display_name
                            FROM timeline_events te2
                            LEFT JOIN users u2 ON te2.actor_user_id = u2.id
                            LEFT JOIN doctors d ON u2.id = d.user_id
                            WHERE te2.patient_id = p.id
                            AND te2.event_type = 'Booking'
                            AND te2.event_summary LIKE '%New patient registered%'
                            ORDER BY te2.created_at ASC
                            LIMIT 1) as created_by_doctor_name,
                           last_clinic.id   as last_clinic_id,
                           last_clinic.code as last_clinic_code,
                           last_clinic.name_ar as last_clinic_name_ar,
                           last_clinic.name_en as last_clinic_name_en
                    FROM patients p
                    INNER JOIN timeline_events te ON te.patient_id = p.id
                    INNER JOIN users u ON te.actor_user_id = u.id
                    INNER JOIN doctors d ON u.id = d.user_id
                    LEFT JOIN appointments a ON p.id = a.patient_id
                    LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
                    LEFT JOIN glasses_prescriptions gp ON a.id = gp.appointment_id
                    LEFT JOIN (
                        SELECT patient_id, clinic_id,
                               ROW_NUMBER() OVER (
                                   PARTITION BY patient_id
                                   ORDER BY date DESC, start_time DESC, id DESC
                               ) AS rn
                        FROM appointments
                        WHERE clinic_id IS NOT NULL
                    ) latest_appt ON latest_appt.patient_id = p.id AND latest_appt.rn = 1
                    LEFT JOIN clinics last_clinic ON last_clinic.id = latest_appt.clinic_id
                    WHERE d.id = ?
                    AND te.event_type = 'Booking'
                    AND te.event_summary LIKE '%New patient registered%'
                    GROUP BY p.id
                    ORDER BY p.first_name, p.last_name
                ");
                $stmt->execute([$systemDoctorId]);
                $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Normalize latest_attachment_id
                foreach ($patients as &$patient) {
                    if (!isset($patient['latest_attachment_id'])) {
                        $patient['latest_attachment_id'] = null;
                    }
                    if ($patient['latest_attachment_id'] === '') {
                        $patient['latest_attachment_id'] = null;
                    }
                }
                unset($patient);

                // Get sub-folders for system folder
                $subFoldersStmt = $this->pdo->prepare("
                    SELECT pf.*,
                           COUNT(DISTINCT pfp.patient_id) as patient_count
                    FROM patient_folders pf
                    LEFT JOIN patient_folder_patients pfp ON pf.id = pfp.folder_id
                    WHERE pf.parent_type = 'system' 
                    AND pf.parent_id IS NULL
                    AND pf.doctor_id = ?
                    GROUP BY pf.id
                    ORDER BY pf.name
                ");
                $subFoldersStmt->execute([(int)$systemDoctorId]);
                $subFolders = $subFoldersStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                $formattedSubFolders = [];
                foreach ($subFolders as $sf) {
                    $formattedSubFolders[] = [
                        'id' => $sf['id'],
                        'name' => $sf['name'],
                        'type' => 'custom',
                        'patient_count' => (int)$sf['patient_count'],
                        'icon' => $sf['icon'] ?? 'bi-folder',
                        'gradient_color' => $sf['gradient_color'] ?? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        'created_at' => $sf['created_at'],
                        'updated_at' => $sf['updated_at'],
                        'parent_id' => $sf['parent_id'] ?? null,
                        'parent_type' => $sf['parent_type'] ?? null
                    ];
                }

                // Always show patients NOT in any subfolder (Without Folder)
                // Even if there are no subfolders, show patients without any folder
                $stmt = $this->pdo->prepare("
                    SELECT p.*, 
                           COUNT(DISTINCT a.id) as total_appointments,
                           MAX(a.date) as last_visit,
                           MAX(CONCAT(a.date, ' ', a.start_time)) as last_appointment_datetime,
                           COUNT(DISTINCT pr.id) as prescriptions_count,
                           COUNT(DISTINCT gp.id) as glasses_count,
                           (SELECT pa.id 
                            FROM patient_attachments pa 
                            LEFT JOIN appointments a ON pa.appointment_id = a.id
                            WHERE pa.patient_id = p.id 
                            AND pa.mime_type LIKE 'image/%'
                            ORDER BY 
                                CASE 
                                    WHEN a.id IS NOT NULL 
                                    THEN 0
                                    ELSE 1
                                END ASC,
                                CASE 
                                    WHEN a.id IS NOT NULL 
                                    THEN CONCAT(a.date, ' ', COALESCE(a.start_time, '00:00:00'))
                                    ELSE '0000-00-00 00:00:00'
                                END DESC,
                                pa.created_at DESC 
                            LIMIT 1) as latest_attachment_id,
                           (SELECT d.display_name 
                            FROM timeline_events te2 
                            LEFT JOIN users u2 ON te2.actor_user_id = u2.id
                            LEFT JOIN doctors d ON u2.id = d.user_id
                            WHERE te2.patient_id = p.id 
                            AND te2.event_type = 'Booking' 
                            AND te2.event_summary LIKE '%New patient registered%' 
                            ORDER BY te2.created_at ASC 
                            LIMIT 1) as created_by_doctor_name
                    FROM patients p
                    INNER JOIN timeline_events te ON te.patient_id = p.id
                    INNER JOIN users u ON te.actor_user_id = u.id
                    INNER JOIN doctors d ON u.id = d.user_id
                    LEFT JOIN appointments a ON p.id = a.patient_id
                    LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
                    LEFT JOIN glasses_prescriptions gp ON a.id = gp.appointment_id
                    WHERE d.id = ?
                    AND te.event_type = 'Booking'
                    AND te.event_summary LIKE '%New patient registered%'
                    AND NOT EXISTS (
                        SELECT 1 FROM patient_folder_patients pfp
                        INNER JOIN patient_folders pf ON pfp.folder_id = pf.id
                        WHERE pfp.patient_id = p.id
                        AND pf.parent_type = 'system'
                        AND pf.parent_id IS NULL
                        AND pf.doctor_id = ?
                    )
                    GROUP BY p.id
                    ORDER BY p.first_name, p.last_name
                ");
                $stmt->execute([$systemDoctorId, $systemDoctorId]);
                $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Normalize latest_attachment_id
                foreach ($patients as &$patient) {
                    if (!isset($patient['latest_attachment_id'])) {
                        $patient['latest_attachment_id'] = null;
                    }
                    if ($patient['latest_attachment_id'] === '') {
                        $patient['latest_attachment_id'] = null;
                    }
                }
                unset($patient);

                // Build breadcrumb for system folder
                $breadcrumb = [
                    [
                        'id' => $id,
                        'name' => $doctorName,
                        'type' => 'system'
                    ]
                ];

                return $this->jsonResponse([
                    'ok' => true,
                    'folders' => $formattedSubFolders,
                    'patients' => $patients,
                    'breadcrumb' => $breadcrumb
                ]);
            }

            // Check if custom folder exists and get parent info for breadcrumb
            $checkStmt = $this->pdo->prepare("
                SELECT pf.id, pf.doctor_id, pf.name, pf.parent_id, pf.parent_type,
                       parent.name as parent_name, parent.id as parent_folder_id
                FROM patient_folders pf
                LEFT JOIN patient_folders parent ON pf.parent_id = parent.id
                WHERE pf.id = ?
            ");
            $checkStmt->execute([$id]);
            $folder = $checkStmt->fetch();

            if (!$folder) {
                return $this->jsonResponse(['error' => 'Folder not found'], 404);
            }

            // No permission check - all doctors can access all folders

            // Get patients in folder with latest_attachment_id and created_by_doctor_name
            // For subfolders, only get patients from this specific folder
            $stmt = $this->pdo->prepare("
                SELECT p.*,
                       COUNT(DISTINCT a.id) as total_appointments,
                       MAX(a.date) as last_visit,
                       MAX(CONCAT(a.date, ' ', a.start_time)) as last_appointment_datetime,
                       COUNT(DISTINCT pr.id) as prescriptions_count,
                       COUNT(DISTINCT gp.id) as glasses_count,
                       (SELECT pa.id 
                        FROM patient_attachments pa 
                        LEFT JOIN appointments a ON pa.appointment_id = a.id
                        WHERE pa.patient_id = p.id 
                        AND pa.mime_type LIKE 'image/%'
                        ORDER BY 
                            CASE 
                                WHEN a.id IS NOT NULL 
                                THEN 0
                                ELSE 1
                            END ASC,
                            CASE 
                                WHEN a.id IS NOT NULL 
                                THEN CONCAT(a.date, ' ', COALESCE(a.start_time, '00:00:00'))
                                ELSE '0000-00-00 00:00:00'
                            END DESC,
                            pa.created_at DESC 
                        LIMIT 1) as latest_attachment_id,
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
                INNER JOIN patient_folder_patients pfp ON p.id = pfp.patient_id
                LEFT JOIN appointments a ON p.id = a.patient_id
                LEFT JOIN prescriptions pr ON a.id = pr.appointment_id
                LEFT JOIN glasses_prescriptions gp ON a.id = gp.appointment_id
                WHERE pfp.folder_id = ?
                GROUP BY p.id
                ORDER BY p.first_name, p.last_name
            ");
            $stmt->execute([$id]);
            $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Normalize latest_attachment_id
            foreach ($patients as &$patient) {
                if (!isset($patient['latest_attachment_id'])) {
                    $patient['latest_attachment_id'] = null;
                }
                if ($patient['latest_attachment_id'] === '') {
                    $patient['latest_attachment_id'] = null;
                }
            }
            unset($patient);

            // Get sub-folders for custom folder
            // Only get sub-folders if user has permission to access parent folder
            $subFoldersStmt = $this->pdo->prepare("
                SELECT pf.*,
                       COUNT(DISTINCT pfp.patient_id) as patient_count
                FROM patient_folders pf
                LEFT JOIN patient_folder_patients pfp ON pf.id = pfp.folder_id
                WHERE pf.parent_id = ? AND pf.parent_type = 'custom'
                AND (pf.doctor_id IS NULL OR pf.doctor_id = ? OR ? IS NULL)
                GROUP BY pf.id
                ORDER BY pf.name
            ");
            $subFoldersStmt->execute([$id, $doctorId, $doctorId]);
            $subFolders = $subFoldersStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $formattedSubFolders = [];
            foreach ($subFolders as $sf) {
                $formattedSubFolders[] = [
                    'id' => $sf['id'],
                    'name' => $sf['name'],
                    'type' => 'custom',
                    'patient_count' => (int)$sf['patient_count'],
                    'icon' => $sf['icon'] ?? 'bi-folder',
                    'gradient_color' => $sf['gradient_color'] ?? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                    'created_at' => $sf['created_at'],
                    'updated_at' => $sf['updated_at']
                ];
            }

            // Build breadcrumb path recursively
            $breadcrumb = [];
            $currentFolder = $folder;
            $maxDepth = 10; // Prevent infinite loops
            $depth = 0;
            
            // Build breadcrumb by traversing up the folder hierarchy
            while ($currentFolder && $depth < $maxDepth) {
                $depth++;
                
                // Add current folder to breadcrumb
                array_unshift($breadcrumb, [
                    'id' => $currentFolder['id'],
                    'name' => $currentFolder['name'],
                    'type' => 'custom'
                ]);
                
                // Check for parent
                $parentId = $currentFolder['parent_id'] ?? null;
                $parentType = $currentFolder['parent_type'] ?? null;
                $doctorId = $currentFolder['doctor_id'] ?? null;
                
                // Special case: If parent_type is 'system' but parent_id is NULL, 
                // this means the folder is a subfolder of a system folder
                // Use doctor_id to get the system folder parent
                if ($parentType === 'system' && (empty($parentId) || $parentId === null) && $doctorId) {
                    // Parent is a system folder - get doctor name
                    $parentDoctorStmt = $this->pdo->prepare("SELECT display_name FROM doctors WHERE id = ?");
                    $parentDoctorStmt->execute([$doctorId]);
                    $parentDoctor = $parentDoctorStmt->fetch();
                    $parentDoctorName = $parentDoctor ? $parentDoctor['display_name'] : "Doctor #{$doctorId}";
                    
                    // Add system folder to breadcrumb
                    array_unshift($breadcrumb, [
                        'id' => 'system_' . $doctorId,
                        'name' => $parentDoctorName,
                        'type' => 'system'
                    ]);
                    break; // System folders are top-level
                }
                // Regular case: parent_id exists
                else if ($parentId && $parentId !== '0' && $parentId !== 0) {
                    if ($parentType === 'system') {
                        // Parent is a system folder - get doctor name
                        $parentDoctorStmt = $this->pdo->prepare("SELECT display_name FROM doctors WHERE id = ?");
                        $parentDoctorStmt->execute([$currentFolder['doctor_id']]);
                        $parentDoctor = $parentDoctorStmt->fetch();
                        $parentDoctorName = $parentDoctor ? $parentDoctor['display_name'] : "Doctor #{$currentFolder['doctor_id']}";
                        
                        // Add system folder to breadcrumb
                        array_unshift($breadcrumb, [
                            'id' => 'system_' . $currentFolder['doctor_id'],
                            'name' => $parentDoctorName,
                            'type' => 'system'
                        ]);
                        break; // System folders are top-level
                    } else {
                        // Parent is a custom folder - get parent info directly
                        $parentStmt = $this->pdo->prepare("
                            SELECT pf.id, pf.doctor_id, pf.name, pf.parent_id, pf.parent_type
                            FROM patient_folders pf
                            WHERE pf.id = ?
                        ");
                        $parentStmt->execute([$parentId]);
                        $parentFolder = $parentStmt->fetch(\PDO::FETCH_ASSOC);
                        
                        if ($parentFolder) {
                            $currentFolder = $parentFolder;
                        } else {
                            // Parent not found - stop here
                            break;
                        }
                    }
                } else {
                    // No parent - we've reached the top
                    break;
                }
            }
            
            // If breadcrumb is empty (shouldn't happen), add current folder
            if (empty($breadcrumb)) {
                $breadcrumb[] = [
                    'id' => $folder['id'],
                    'name' => $folder['name'],
                    'type' => 'custom'
                ];
            }

            return $this->jsonResponse([
                'ok' => true,
                'folders' => $formattedSubFolders,
                'patients' => $patients,
                'breadcrumb' => $breadcrumb
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // Patient Color Markers API Endpoints
    // ============================================

    /**
     * GET /api/patient-color-markers/{patient_id}
     * Get color marker for a patient
     */
    public function getPatientColorMarker($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = (int)$patientId;
            if ($patientId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID'], 400);
            }

            $stmt = $this->pdo->prepare("
                SELECT color_code 
                FROM patient_color_markers 
                WHERE patient_id = ?
            ");
            $stmt->execute([$patientId]);
            $marker = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($marker) {
                return $this->jsonResponse([
                    'ok' => true,
                    'color_code' => $marker['color_code']
                ]);
            } else {
                return $this->jsonResponse([
                    'ok' => true,
                    'color_code' => null
                ]);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/patient-color-markers/{patient_id}
     * Update or create color marker for a patient
     */
    public function updatePatientColorMarker($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = (int)$patientId;
            if ($patientId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID'], 400);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $colorCode = trim($data['color_code'] ?? '');

            // Validate color code (hex format)
            if (empty($colorCode) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $colorCode)) {
                return $this->jsonResponse(['error' => 'Invalid color code. Must be a valid hex color (e.g., #ef4444)'], 400);
            }

            // Check if patient exists
            $patientStmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $patientStmt->execute([$patientId]);
            if (!$patientStmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Check if marker exists
            $checkStmt = $this->pdo->prepare("SELECT patient_id FROM patient_color_markers WHERE patient_id = ?");
            $checkStmt->execute([$patientId]);
            $exists = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($exists) {
                // Update existing marker
                $stmt = $this->pdo->prepare("
                    UPDATE patient_color_markers 
                    SET color_code = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE patient_id = ?
                ");
                $stmt->execute([$colorCode, $patientId]);
            } else {
                // Create new marker
                $stmt = $this->pdo->prepare("
                    INSERT INTO patient_color_markers (patient_id, color_code) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$patientId, $colorCode]);
            }

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Color marker updated successfully',
                'color_code' => $colorCode
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/patient-color-markers/{patient_id}
     * Delete color marker for a patient
     */
    public function deletePatientColorMarker($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = (int)$patientId;
            if ($patientId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID'], 400);
            }

            $stmt = $this->pdo->prepare("DELETE FROM patient_color_markers WHERE patient_id = ?");
            $stmt->execute([$patientId]);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Color marker deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/patient-color-markers/batch
     * Get color markers for multiple patients in a single request
     * Reduces N API calls to 1 for better performance
     */
    public function getBatchPatientColorMarkers()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $patientIds = $data['patient_ids'] ?? [];

            if (empty($patientIds) || !is_array($patientIds)) {
                return $this->jsonResponse(['error' => 'patient_ids array is required'], 400);
            }

            // Sanitize patient IDs
            $patientIds = array_map('intval', $patientIds);
            $patientIds = array_filter($patientIds, function($id) { return $id > 0; });

            if (empty($patientIds)) {
                return $this->jsonResponse(['ok' => true, 'markers' => []]);
            }

            // Build placeholders for IN clause
            $placeholders = implode(',', array_fill(0, count($patientIds), '?'));

            $stmt = $this->pdo->prepare("
                SELECT patient_id, color_code
                FROM patient_color_markers
                WHERE patient_id IN ($placeholders)
            ");
            $stmt->execute($patientIds);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Convert to associative array keyed by patient_id
            $markers = [];
            foreach ($results as $row) {
                $markers[$row['patient_id']] = $row['color_code'];
            }

            return $this->jsonResponse([
                'ok' => true,
                'markers' => $markers
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/patients/tags/batch
     * Get tags for multiple patients in a single request
     * Reduces N API calls to 1 for better performance
     */
    public function getBatchPatientTags()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $patientIds = $data['patient_ids'] ?? [];

            if (empty($patientIds) || !is_array($patientIds)) {
                return $this->jsonResponse(['error' => 'patient_ids array is required'], 400);
            }

            // Sanitize patient IDs
            $patientIds = array_map('intval', $patientIds);
            $patientIds = array_filter($patientIds, function($id) { return $id > 0; });

            if (empty($patientIds)) {
                return $this->jsonResponse(['ok' => true, 'tags' => []]);
            }

            // Build placeholders for IN clause
            $placeholders = implode(',', array_fill(0, count($patientIds), '?'));

            $stmt = $this->pdo->prepare("
                SELECT pta.patient_id, pt.id, pt.name, pt.color, pt.icon
                FROM patient_tag_assignments pta
                INNER JOIN patient_tags pt ON pta.tag_id = pt.id
                WHERE pta.patient_id IN ($placeholders)
                ORDER BY pt.sort_order ASC, pt.name ASC
            ");
            $stmt->execute($patientIds);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group tags by patient_id
            $tagsByPatient = [];
            foreach ($patientIds as $id) {
                $tagsByPatient[$id] = [];
            }
            foreach ($results as $row) {
                $tagsByPatient[$row['patient_id']][] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'color' => $row['color'],
                    'icon' => $row['icon']
                ];
            }

            return $this->jsonResponse([
                'ok' => true,
                'tags' => $tagsByPatient
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // ============================================
    // Patient Tags API Endpoints
    // ============================================

    /**
     * GET /api/patient-tags
     * Get all tags (global + doctor-specific)
     */
    public function getPatientTags()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);

            // Get global tags (doctor_id IS NULL) and doctor-specific tags
            $stmt = $this->pdo->prepare("
                SELECT id, name, color, icon, doctor_id, sort_order, created_at, updated_at
                FROM patient_tags
                WHERE doctor_id IS NULL OR doctor_id = ?
                ORDER BY sort_order ASC, name ASC
            ");
            $stmt->execute([$doctorId]);
            $tags = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'ok' => true,
                'tags' => $tags
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/patient-tags
     * Create a new tag
     */
    public function createPatientTag()
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            $data = json_decode(file_get_contents('php://input'), true);

            $name = trim($data['name'] ?? '');
            $color = trim($data['color'] ?? '#6366f1');
            $icon = trim($data['icon'] ?? 'bi-tag');
            $tagDoctorId = isset($data['doctor_id']) ? (int)$data['doctor_id'] : null;
            $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;

            if (empty($name)) {
                return $this->jsonResponse(['error' => 'Tag name is required'], 400);
            }

            // Validate color code
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                return $this->jsonResponse(['error' => 'Invalid color code. Must be a valid hex color'], 400);
            }

            // Determine final doctor_id:
            // - If doctor_id is explicitly set to null in request, it's a global tag
            // - If doctor_id is not provided, use current doctor's ID (private tag)
            // - If doctor_id is provided with a value, use that value
            if (isset($data['doctor_id']) && $data['doctor_id'] === null) {
                // Explicitly set to null - global tag
                $finalDoctorId = null;
            } else if ($tagDoctorId !== null) {
                // Provided with a value - use it
                $finalDoctorId = $tagDoctorId;
            } else {
                // Not provided - use current doctor's ID (private tag)
                $finalDoctorId = $doctorId;
            }

            // Check if tag with same name already exists for this doctor
            if ($finalDoctorId === null) {
                // Checking for global tag
                $checkStmt = $this->pdo->prepare("
                    SELECT id FROM patient_tags 
                    WHERE name = ? AND doctor_id IS NULL
                ");
                $checkStmt->execute([$name]);
            } else {
                // Checking for doctor-specific tag
                $checkStmt = $this->pdo->prepare("
                    SELECT id FROM patient_tags 
                    WHERE name = ? AND doctor_id = ?
                ");
                $checkStmt->execute([$name, $finalDoctorId]);
            }
            if ($checkStmt->fetch()) {
                return $this->jsonResponse(['error' => 'Tag with this name already exists'], 400);
            }

            // Create tag
            $stmt = $this->pdo->prepare("
                INSERT INTO patient_tags (name, color, icon, doctor_id, sort_order) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $color, $icon, $finalDoctorId, $sortOrder]);
            $tagId = $this->pdo->lastInsertId();

            // Fetch created tag
            $fetchStmt = $this->pdo->prepare("
                SELECT id, name, color, icon, doctor_id, sort_order, created_at, updated_at
                FROM patient_tags
                WHERE id = ?
            ");
            $fetchStmt->execute([$tagId]);
            $tag = $fetchStmt->fetch(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Tag created successfully',
                'tag' => $tag
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/patient-tags/{id}
     * Update a tag
     */
    public function updatePatientTag($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $id = (int)$id;
            if ($id <= 0) {
                return $this->jsonResponse(['error' => 'Invalid tag ID'], 400);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            $data = json_decode(file_get_contents('php://input'), true);

            // Check if tag exists
            $checkStmt = $this->pdo->prepare("
                SELECT id, doctor_id FROM patient_tags WHERE id = ?
            ");
            $checkStmt->execute([$id]);
            $tag = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$tag) {
                return $this->jsonResponse(['error' => 'Tag not found'], 404);
            }

            // Check permission: can only update global tags or own doctor tags
            if ($tag['doctor_id'] !== null && $tag['doctor_id'] != $doctorId) {
                return $this->jsonResponse(['error' => 'Unauthorized to update this tag'], 403);
            }

            $name = trim($data['name'] ?? '');
            $color = trim($data['color'] ?? '');
            $icon = trim($data['icon'] ?? '');
            $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : null;

            // Build update query dynamically
            $updates = [];
            $params = [];

            if (!empty($name)) {
                // Check if name already exists
                if ($tag['doctor_id'] === null) {
                    // Checking for global tag
                    $nameCheckStmt = $this->pdo->prepare("
                        SELECT id FROM patient_tags 
                        WHERE name = ? AND id != ? AND doctor_id IS NULL
                    ");
                    $nameCheckStmt->execute([$name, $id]);
                } else {
                    // Checking for doctor-specific tag
                    $nameCheckStmt = $this->pdo->prepare("
                        SELECT id FROM patient_tags 
                        WHERE name = ? AND id != ? AND doctor_id = ?
                    ");
                    $nameCheckStmt->execute([$name, $id, $tag['doctor_id']]);
                }
                if ($nameCheckStmt->fetch()) {
                    return $this->jsonResponse(['error' => 'Tag with this name already exists'], 400);
                }
                $updates[] = "name = ?";
                $params[] = $name;
            }

            if (!empty($color)) {
                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                    return $this->jsonResponse(['error' => 'Invalid color code'], 400);
                }
                $updates[] = "color = ?";
                $params[] = $color;
            }

            if (!empty($icon)) {
                $updates[] = "icon = ?";
                $params[] = $icon;
            }

            if ($sortOrder !== null) {
                $updates[] = "sort_order = ?";
                $params[] = $sortOrder;
            }

            if (empty($updates)) {
                return $this->jsonResponse(['error' => 'No fields to update'], 400);
            }

            $updates[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $id;

            $stmt = $this->pdo->prepare("
                UPDATE patient_tags 
                SET " . implode(', ', $updates) . "
                WHERE id = ?
            ");
            $stmt->execute($params);

            // Fetch updated tag
            $fetchStmt = $this->pdo->prepare("
                SELECT id, name, color, icon, doctor_id, sort_order, created_at, updated_at
                FROM patient_tags
                WHERE id = ?
            ");
            $fetchStmt->execute([$id]);
            $updatedTag = $fetchStmt->fetch(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Tag updated successfully',
                'tag' => $updatedTag
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/patient-tags/{id}
     * Delete a tag
     */
    public function deletePatientTag($id)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $id = (int)$id;
            if ($id <= 0) {
                return $this->jsonResponse(['error' => 'Invalid tag ID'], 400);
            }

            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);

            // Check if tag exists and permission
            $checkStmt = $this->pdo->prepare("
                SELECT id, doctor_id FROM patient_tags WHERE id = ?
            ");
            $checkStmt->execute([$id]);
            $tag = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$tag) {
                return $this->jsonResponse(['error' => 'Tag not found'], 404);
            }

            // Check permission: can only delete global tags or own doctor tags
            if ($tag['doctor_id'] !== null && $tag['doctor_id'] != $doctorId) {
                return $this->jsonResponse(['error' => 'Unauthorized to delete this tag'], 403);
            }

            // Delete tag (CASCADE will delete all assignments)
            $stmt = $this->pdo->prepare("DELETE FROM patient_tags WHERE id = ?");
            $stmt->execute([$id]);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Tag deleted successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/patients/{patient_id}/tags
     * Get tags assigned to a patient
     */
    public function getPatientAssignedTags($patientId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = (int)$patientId;
            if ($patientId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID'], 400);
            }

            // Check if patient exists
            $patientStmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $patientStmt->execute([$patientId]);
            if (!$patientStmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Get assigned tags
            $stmt = $this->pdo->prepare("
                SELECT pt.id, pt.name, pt.color, pt.icon, pt.doctor_id, pt.sort_order,
                       pta.assigned_at
                FROM patient_tag_assignments pta
                INNER JOIN patient_tags pt ON pta.tag_id = pt.id
                WHERE pta.patient_id = ?
                ORDER BY pt.sort_order ASC, pt.name ASC
            ");
            $stmt->execute([$patientId]);
            $tags = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'ok' => true,
                'tags' => $tags
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/patients/{patient_id}/tags/{tag_id}
     * Assign a tag to a patient
     */
    public function assignTagToPatient($patientId, $tagId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = (int)$patientId;
            $tagId = (int)$tagId;

            if ($patientId <= 0 || $tagId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID or tag ID'], 400);
            }

            // Check if patient exists
            $patientStmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $patientStmt->execute([$patientId]);
            if (!$patientStmt->fetch()) {
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Check if tag exists
            $tagStmt = $this->pdo->prepare("SELECT id FROM patient_tags WHERE id = ?");
            $tagStmt->execute([$tagId]);
            if (!$tagStmt->fetch()) {
                return $this->jsonResponse(['error' => 'Tag not found'], 404);
            }

            // Check if already assigned
            $checkStmt = $this->pdo->prepare("
                SELECT patient_id FROM patient_tag_assignments 
                WHERE patient_id = ? AND tag_id = ?
            ");
            $checkStmt->execute([$patientId, $tagId]);
            if ($checkStmt->fetch()) {
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Tag already assigned to patient'
                ]);
            }

            // Assign tag
            $stmt = $this->pdo->prepare("
                INSERT INTO patient_tag_assignments (patient_id, tag_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$patientId, $tagId]);

            return $this->jsonResponse([
                'ok' => true,
                'message' => 'Tag assigned to patient successfully'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/patients/{patient_id}/tags/{tag_id}
     * Remove a tag from a patient
     */
    public function removeTagFromPatient($patientId, $tagId)
    {
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $patientId = (int)$patientId;
            $tagId = (int)$tagId;

            if ($patientId <= 0 || $tagId <= 0) {
                return $this->jsonResponse(['error' => 'Invalid patient ID or tag ID'], 400);
            }

            $stmt = $this->pdo->prepare("
                DELETE FROM patient_tag_assignments 
                WHERE patient_id = ? AND tag_id = ?
            ");
            $stmt->execute([$patientId, $tagId]);

            if ($stmt->rowCount() > 0) {
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Tag removed from patient successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Tag was not assigned to patient'
                ]);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}