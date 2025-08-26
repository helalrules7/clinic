<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Lib\Validator;
use App\Config\Database;
use App\Config\Constants;
use App\Lib\Helpers;

class ApiController
{
    private $auth;
    private $view;
    private $validator;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->validator = new Validator();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Set JSON response header
        header('Content-Type: application/json');
    }

    public function getCalendar()
    {
        try {
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
                        'available_slots' => []
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

            $data = $_POST;
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
                    'ok' => true,
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

            // Validate input
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

            // Create glasses prescription
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
                    'ok' => true,
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
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone, p.dob, p.gender,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = ? AND a.date = ? AND a.status NOT IN ('Cancelled', 'NoShow')
            ORDER BY a.start_time
        ");
        $stmt->execute([$doctorId, $date]);
        return $stmt->fetchAll();
    }

    private function getAvailableTimeSlots($doctorId, $date)
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
            if ($this->isTimeSlotAvailable($doctorId, $date, $timeStr)) {
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
        return $stmt->fetchColumn() == 0;
    }

    private function getUnavailableSlots($doctorId, $date)
    {
        // Get all time slots that are unavailable for this doctor
        $allSlots = $this->getAllTimeSlots($date);
        $availableSlots = $this->getAvailableTimeSlots($doctorId, $date);
        $unavailableSlots = [];
        
        foreach ($allSlots as $time) {
            if (!in_array($time, $availableSlots)) {
                // Check if there's ANY appointment at this time (any doctor)
                $stmt = $this->pdo->prepare("
                    SELECT a.start_time, a.doctor_id, d.display_name as doctor_name, u.name as user_name
                    FROM appointments a
                    JOIN doctors d ON a.doctor_id = d.id
                    JOIN users u ON d.user_id = u.id
                    WHERE a.date = ? AND a.start_time = ? 
                    AND a.status NOT IN ('Cancelled', 'NoShow')
                ");
                $stmt->execute([$date, $time]);
                $appointment = $stmt->fetch();
                
                if ($appointment) {
                    // If it's the current doctor's appointment, it will show in appointments section
                    // If it's another doctor's appointment, show as reserved
                    if ($appointment['doctor_id'] != $doctorId) {
                        $doctorDisplayName = $appointment['user_name'] ?? $appointment['doctor_name'];
                        $unavailableSlots[] = [
                            'time' => $time,
                            'doctor_name' => $doctorDisplayName,
                            'reason' => 'Reserved for ' . $doctorDisplayName
                        ];
                    }
                } else {
                    // Check if it's outside working hours for this doctor
                    if ($this->isOutsideWorkingHours($doctorId, $date, $time)) {
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
                                             distance_sphere_l, distance_cylinder_l, distance_axis_l, add_power_r, add_power_l,
                                             PD_NEAR, PD_DISTANCE, lens_type, comments)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['appointment_id'],
            $data['distance_sphere_r'] ?? null,
            $data['distance_cylinder_r'] ?? null,
            $data['distance_axis_r'] ?? null,
            $data['distance_sphere_l'] ?? null,
            $data['distance_cylinder_l'] ?? null,
            $data['distance_axis_l'] ?? null,
            $data['add_power_r'] ?? null,
            $data['add_power_l'] ?? null,
            $data['PD_NEAR'] ?? null,
            $data['PD_DISTANCE'] ?? null,
            $data['lens_type'],
            $data['comments'] ?? null
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
}
