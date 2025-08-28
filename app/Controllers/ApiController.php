<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Validator;
use App\Config\Database;
use App\Config\Constants;
use App\Lib\Helpers;

class ApiController
{
    private $auth;
    private $validator;
    private $pdo;

    public function __construct()
    {
        try {
            $this->auth = new Auth();
            $this->validator = new Validator();
            $this->pdo = Database::getInstance()->getConnection();
            
            // Suppress PHP errors for API responses
            ini_set('display_errors', 0);
            error_reporting(E_ERROR | E_PARSE);
        } catch (\Exception $e) {
            error_log("Exception in ApiController __construct: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCalendar()
    {
        try {
            // Enable debug logging to custom file
            ini_set('error_log', '/tmp/clinic_debug.log');
            error_log("=== DEBUG CALENDAR START === " . date('Y-m-d H:i:s'));
            
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

            // Get appointments for the date
            $appointments = $this->getAppointmentsForDate($doctorId, $date);
            
            // Get available time slots
            $availableSlots = $this->getAvailableTimeSlots($doctorId, $date);
            
            // Get unavailable slots with doctor info
            $unavailableSlots = $this->getUnavailableSlots($doctorId, $date);

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
                    'details' => $this->validator->getErrors()
                ], 400);
            }

            // Check if time slot is available
            if (!Helpers::isTimeSlotAvailable(
                $data['doctor_id'], 
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
        try {
            if (!$this->auth->check()) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $user = $this->auth->user();
            $appointment = $this->getAppointmentDetails($id);
            
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }

            // Check permissions
            if ($user['role'] === 'secretary' && in_array($appointment['status'], ['Completed', 'Cancelled'])) {
                return $this->jsonResponse(['error' => 'Cannot modify completed or cancelled appointments'], 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['status'])) {
                $result = $this->updateAppointmentStatus($id, $data['status'], $data['reason'] ?? null);
                
                if ($result) {
                    $this->createTimelineEvent(
                        $appointment['patient_id'], 
                        $id, 
                        'StatusChange', 
                        "Status changed to {$data['status']}"
                    );
                    
                    return $this->jsonResponse([
                        'ok' => true,
                        'message' => 'Appointment updated successfully'
                    ]);
                }
            }

            return $this->jsonResponse(['error' => 'No valid updates provided'], 400);

        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
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
                    'details' => $this->validator->getErrors()
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
                'gender' => 'in:Male,Female,Other'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getErrors()
                ], 400);
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
            error_log("DEBUG: updateEmergencyContact called with ID: " . $id);
            
            if (!$this->auth->check()) {
                error_log("DEBUG: Auth check failed");
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }

            if (!$id) {
                error_log("DEBUG: No patient ID provided");
                return $this->jsonResponse(['error' => 'Patient ID is required'], 400);
            }

            // Verify patient exists
            $stmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                error_log("DEBUG: Patient not found with ID: " . $id);
                return $this->jsonResponse(['error' => 'Patient not found'], 404);
            }

            // Get JSON input
            $rawInput = file_get_contents('php://input');
            error_log("DEBUG: Raw input: " . $rawInput);
            
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                error_log("DEBUG: Failed to decode JSON input");
                return $this->jsonResponse(['error' => 'Invalid JSON input'], 400);
            }

            error_log("DEBUG: Parsed input: " . json_encode($input));

            // Validate input
            $rules = [
                'emergency_contact' => 'required|max:100',
                'emergency_phone' => 'required|phone'
            ];

            if (!$this->validator->validate($input, $rules)) {
                error_log("DEBUG: Validation failed: " . json_encode($this->validator->getErrors()));
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getErrors()
                ], 400);
            }

            // Update emergency contact
            $stmt = $this->pdo->prepare("
                UPDATE patients 
                SET emergency_contact = ?, emergency_phone = ?
                WHERE id = ?
            ");
            
            error_log("DEBUG: Executing update with values: " . json_encode([
                $input['emergency_contact'],
                $input['emergency_phone'],
                $id
            ]));
            
            $success = $stmt->execute([
                $input['emergency_contact'],
                $input['emergency_phone'],
                $id
            ]);

            error_log("DEBUG: Update success: " . ($success ? 'true' : 'false') . ", Rows affected: " . $stmt->rowCount());

            if ($success) {
                // Create timeline event
                try {
                    $this->createTimelineEvent(
                        $id, 
                        null,
                        'Update', 
                        'Emergency contact information updated'
                    );
                    error_log("DEBUG: Timeline event created successfully");
                } catch (\Exception $e) {
                    error_log("DEBUG: Timeline event failed: " . $e->getMessage());
                    // Continue even if timeline fails
                }
                
                return $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Emergency contact updated successfully'
                ]);
            } else {
                error_log("DEBUG: Update failed");
                return $this->jsonResponse(['error' => 'Failed to update emergency contact'], 500);
            }

        } catch (\Exception $e) {
            error_log("Emergency contact update error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
                    'details' => $this->validator->getErrors()
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
                'dose' => 'required|max:60',
                'frequency' => 'required|max:60',
                'duration' => 'required|max:60'
            ];

            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                return $this->jsonResponse([
                    'error' => 'Validation failed',
                    'details' => $this->validator->getErrors()
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
                    'details' => $this->validator->getErrors()
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
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    private function getAppointmentsForDate($doctorId, $date)
    {
        // Set debug log file
        ini_set('error_log', '/tmp/clinic_debug.log');
        
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
        
        error_log("Debug getAppointmentsForDate - Doctor: $doctorId, Date: $date");
        error_log("Debug - Found " . count($appointments) . " appointments");
        foreach ($appointments as $apt) {
            error_log("Debug - Appointment: ID={$apt['id']}, Time={$apt['start_time']} (formatted), Patient={$apt['patient_name']}, Status={$apt['status']}");
        }
        
        return $appointments;
    }

    private function getAvailableTimeSlots($doctorId, $date)
    {
        // Set debug log file
        ini_set('error_log', '/tmp/clinic_debug.log');
        
        // Get working hours for the doctor on this day
        $weekday = (new \DateTime($date))->format('w');
        $stmt = $this->pdo->prepare("
            SELECT work_start, work_end FROM doctor_schedule 
            WHERE doctor_id = ? AND weekday = ? AND is_working = 1
        ");
        $stmt->execute([$doctorId, $weekday]);
        $schedule = $stmt->fetch();
        
        error_log("Debug getAvailableTimeSlots - Doctor: $doctorId, Date: $date, Weekday: $weekday");
        error_log("Debug - Schedule found: " . ($schedule ? "YES (Start: {$schedule['work_start']}, End: {$schedule['work_end']})" : "NO"));
        
        if (!$schedule) {
            error_log("Debug - No schedule found, returning empty array");
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
            error_log("Debug - Checking slot $timeStr: " . ($isAvailable ? "AVAILABLE" : "NOT AVAILABLE"));
            
            if ($isAvailable) {
                $slots[] = $timeStr;
            }
            
            $current->add($interval);
        }
        
        error_log("Debug - Generated " . count($slots) . " available slots: " . implode(', ', $slots));
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
        
        error_log("Debug isTimeSlotAvailable - Doctor: $doctorId, Date: $date, Time: $startTime, Appointments: $count");
        
        return $count == 0;
    }

    private function getUnavailableSlots($doctorId, $date)
    {
        // Set debug log file
        ini_set('error_log', '/tmp/clinic_debug.log');
        
        // Get all time slots that are unavailable for this doctor
        $allSlots = $this->getAllTimeSlots($date);
        $availableSlots = $this->getAvailableTimeSlots($doctorId, $date);
        $unavailableSlots = [];
        
        // Debug logging
        error_log("Debug getUnavailableSlots - Doctor: $doctorId, Date: $date");
        error_log("Debug - All slots count: " . count($allSlots));
        error_log("Debug - Available slots count: " . count($availableSlots));
        
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
                error_log("Debug - Time: $time, Appointment found: " . ($appointment ? 'YES' : 'NO'));
                if ($appointment) {
                    error_log("Debug - Appointment doctor_id: {$appointment['doctor_id']}, current doctor: $doctorId");
                }
                
                if ($appointment) {
                    // If it's the current doctor's appointment, it will show in appointments section
                    // If it's another doctor's appointment, show as reserved
                    if ($appointment['doctor_id'] != $doctorId) {
                        $doctorDisplayName = $appointment['user_name'] ?? $appointment['doctor_name'];
                        $patientName = $appointment['first_name'] . ' ' . $appointment['last_name'];
                        $visitType = $appointment['visit_type'];
                        $status = $appointment['status'];
                        
                        error_log("Debug - Adding reserved slot for: $doctorDisplayName - $patientName");
                        
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
                    error_log("Debug - Time: $time, Outside working hours: " . ($isOutside ? 'YES' : 'NO'));
                    
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
                        
                        error_log("Debug - Mystery unavailable slot details: $debugInfo");
                        
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
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function calculateEndTime($startTime)
    {
        $start = new \DateTime($startTime);
        $start->add(new \DateInterval('PT15M'));
        return $start->format('H:i:s');
    }

    private function createAppointmentRecord($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO appointments (patient_id, doctor_id, booked_by, source, date, start_time, end_time, visit_type, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $endTime = $this->calculateEndTime($data['start_time']);
        
        $stmt->execute([
            $data['patient_id'],
            $data['doctor_id'],
            $this->auth->user()['id'],
            $data['source'],
            $data['date'],
            $data['start_time'],
            $endTime,
            $data['visit_type'],
            $data['notes'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function updateAppointmentStatus($id, $status, $reason = null)
    {
        $stmt = $this->pdo->prepare("
            UPDATE appointments SET status = ?, cancellation_reason = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$status, $reason, $id]);
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

    private function searchPatientsByQuery($query)
    {
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
                                         IOP_right, IOP_left, slit_lamp, fundus, diagnosis, diagnosis_code,
                                         plan, followup_days, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $data['slit_lamp'] ?? null,
            $data['fundus'] ?? null,
            $data['diagnosis'],
            $data['diagnosis_code'] ?? null,
            $data['plan'],
            $data['followup_days'] ?? null,
            $userId
        ]);
        
        return $this->pdo->lastInsertId();
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
            $data['dose'],
            $data['frequency'],
            $data['duration'],
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
            error_log("Upload attachment error: " . $e->getMessage());
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
            error_log("View attachment error: " . $e->getMessage());
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
            error_log("Download attachment error: " . $e->getMessage());
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
            error_log("Delete attachment error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Server error']);
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
            error_log("Delete medication error: " . $e->getMessage());
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
                'dose' => 'required|max:60',
                'frequency' => 'required|max:60',
                'duration' => 'required|max:60',
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
            
            error_log("Parsed data for validation: " . json_encode($data));
            
            if (!$this->validator->validate($data, $rules)) {
                $errors = $this->validator->getErrors();
                error_log("Validation errors: " . json_encode($errors));
                error_log("POST data: " . json_encode($data));
                
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
                $data['dose'],
                $data['frequency'],
                $data['duration'],
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
            error_log("Update medication error: " . $e->getMessage());
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
                    'details' => $this->validator->getErrors()
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
            $appointmentId = $this->request->getPost('appointment_id');
            $testType = $this->request->getPost('test_type');
            $testCategory = $this->request->getPost('test_category');
            $testName = $this->request->getPost('test_name');
            $priority = $this->request->getPost('priority') ?? 'normal';
            $status = $this->request->getPost('status') ?? 'ordered';
            $orderedDate = $this->request->getPost('ordered_date');
            $expectedDate = $this->request->getPost('expected_date');
            $notes = $this->request->getPost('notes');
            $results = $this->request->getPost('results');

            // Validation
            if (!$appointmentId || !$testType || !$testCategory || !$testName) {
                return $this->jsonResponse(['error' => 'Missing required fields'], 400);
            }

            // Get appointment details for patient_id
            $appointment = $this->db->table('appointments')->where('id', $appointmentId)->get()->getRow();
            if (!$appointment) {
                return $this->jsonResponse(['error' => 'Appointment not found'], 404);
            }

            $data = [
                'appointment_id' => $appointmentId,
                'patient_id' => $appointment->patient_id,
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

            $this->db->table('lab_tests')->insert($data);
            $labTestId = $this->db->insertID();

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
            $testType = $this->request->getPost('test_type');
            $testCategory = $this->request->getPost('test_category');
            $testName = $this->request->getPost('test_name');
            $priority = $this->request->getPost('priority');
            $status = $this->request->getPost('status');
            $orderedDate = $this->request->getPost('ordered_date');
            $expectedDate = $this->request->getPost('expected_date');
            $notes = $this->request->getPost('notes');
            $results = $this->request->getPost('results');

            // Check if lab test exists
            $labTest = $this->db->table('lab_tests')->where('id', $testId)->get()->getRow();
            if (!$labTest) {
                return $this->jsonResponse(['error' => 'Lab test not found'], 404);
            }

            $data = [
                'test_type' => $testType,
                'test_category' => $testCategory,
                'test_name' => $testName,
                'priority' => $priority,
                'status' => $status,
                'ordered_date' => $orderedDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
                'results' => $results,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Remove empty values
            $data = array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });

            $this->db->table('lab_tests')->where('id', $testId)->update($data);

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
            $labTest = $this->db->table('lab_tests')->where('id', $testId)->get()->getRow();
            if (!$labTest) {
                return $this->jsonResponse(['error' => 'Lab test not found'], 404);
            }

            $this->db->table('lab_tests')->where('id', $testId)->delete();

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
            $labTests = $this->db->table('lab_tests')
                ->where('appointment_id', $appointmentId)
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();

            return $this->jsonResponse([
                'success' => true,
                'lab_tests' => $labTests
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
