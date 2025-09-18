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
    private $tempImagesToCleanup = [];

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
                    error_log("Radiology tests table not found: " . $e->getMessage());
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

                // Log the deletion
                error_log("Appointment deleted: ID {$id}, Patient: {$appointment['first_name']} {$appointment['last_name']}, Date: {$appointment['date']}, Time: {$appointment['start_time']}");

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
                    'details' => $this->validator->getErrors()
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
                    error_log("Radiology tests table not found: " . $e->getMessage());
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
                
                error_log("Patient deletion completed: " . json_encode($deletionSummary));
                
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
            error_log("Error deleting patient: " . $e->getMessage());
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
        header('Content-Type: application/json');
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

    private function getAllAppointmentsForDate($date)
    {
        // Set debug log file
        ini_set('error_log', '/tmp/clinic_debug.log');
        
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
        
        // Format the time fields to match frontend expectations
        foreach ($appointments as &$appointment) {
            $appointment['start_time'] = $appointment['start_time_formatted'];
            $appointment['end_time'] = $appointment['end_time_formatted'];
            $appointment['doctor_display_name'] = $appointment['user_name'] ?? $appointment['doctor_name'];
        }
        
        error_log("Debug getAllAppointmentsForDate - Date: $date");
        error_log("Debug - Found " . count($appointments) . " appointments");
        foreach ($appointments as $apt) {
            error_log("Debug - Appointment: ID={$apt['id']}, Time={$apt['start_time']}, Patient={$apt['patient_name']}, Doctor={$apt['doctor_display_name']}, Status={$apt['status']}");
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

    private function getAvailableTimeSlotsGlobal($date)
    {
        // Set debug log file
        ini_set('error_log', '/tmp/clinic_debug.log');
        
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
            error_log("Debug - Checking global slot $timeStr: " . ($isAvailable ? "AVAILABLE" : "NOT AVAILABLE"));
            
            if ($isAvailable) {
                $slots[] = $timeStr;
            }
            
            $current->add($interval);
        }
        
        error_log("Debug - Generated " . count($slots) . " global available slots: " . implode(', ', $slots));
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

    private function isTimeSlotAvailableGlobal($date, $startTime)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE date = ? AND start_time = ? 
            AND status NOT IN ('Cancelled', 'NoShow')
        ");
        $stmt->execute([$date, $startTime]);
        $count = $stmt->fetchColumn();
        
        error_log("Debug isTimeSlotAvailableGlobal - Date: $date, Time: $startTime, Appointments: $count");
        
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

    private function getUnavailableSlotsGlobal($date)
    {
        // Set debug log file
        ini_set('error_log', '/tmp/clinic_debug.log');
        
        // Get all time slots that are unavailable globally
        $allSlots = $this->getAllTimeSlots($date);
        $availableSlots = $this->getAvailableTimeSlotsGlobal($date);
        $unavailableSlots = [];
        
        // Debug logging
        error_log("Debug getUnavailableSlotsGlobal - Date: $date");
        error_log("Debug - All slots count: " . count($allSlots));
        error_log("Debug - Available slots count: " . count($availableSlots));
        
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
                    
                    error_log("Debug - Adding reserved slot for: $doctorDisplayName - $patientName");
                    
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
                    error_log("Debug - Time: $time, Outside working hours: " . ($isOutside ? 'YES' : 'NO'));
                    
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

    private function createMedicationPrescriptionRecord($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO prescriptions (appointment_id, drug_name, dose, frequency, duration, route, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['appointment_id'],
            $data['drug_name'],
            $data['dose'] ?? null,
            $data['frequency'] ?? null,
            $data['duration'] ?? null,
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
                $data['dose'] ?? null,
                $data['frequency'] ?? null,
                $data['duration'] ?? null,
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
            // Get JSON data from request body
            $input = json_decode(file_get_contents('php://input'), true);
            
            $appointmentId = $input['appointment_id'] ?? $_POST['appointment_id'] ?? null;
            $testType = $input['test_type'] ?? $_POST['test_type'] ?? null;
            $testCategory = $input['test_category'] ?? $_POST['test_category'] ?? null;
            $testName = $input['test_name'] ?? $_POST['test_name'] ?? null;
            $priority = $input['priority'] ?? $_POST['priority'] ?? 'normal';
            $status = $input['status'] ?? $_POST['status'] ?? 'ordered';
            $orderedDate = $input['ordered_date'] ?? $_POST['ordered_date'] ?? null;
            $expectedDate = $input['expected_date'] ?? $_POST['expected_date'] ?? null;
            $notes = $input['notes'] ?? $_POST['notes'] ?? null;
            $results = $input['results'] ?? $_POST['results'] ?? null;

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

            // Debug logging
            error_log("DEBUG updatePatientNote: " . json_encode([
                'noteId' => $noteId,
                'method' => $_SERVER['REQUEST_METHOD'],
                'input' => $input,
                'title' => $title,
                'content' => $content
            ]));

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
            error_log("Error creating medical history: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
            error_log("Error updating medical history: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
            error_log("Error deleting medical history: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
            error_log("Error fetching medical history entry: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
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
            error_log("Error fetching patient appointments: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
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
            error_log("Error checking export access: " . $e->getMessage());
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
            error_log("Error exporting patient data: " . $e->getMessage());
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
            error_log("Error fetching glasses prescription: " . $e->getMessage());
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
            error_log("Error getting patient data: " . $e->getMessage());
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
                            error_log("Processing image: $imagePath");
                            
                            // Get image info
                            $imageInfo = getimagesize($imagePath);
                            if (!$imageInfo) {
                                throw new \Exception("Cannot get image information");
                            }
                            
                            $originalWidth = $imageInfo[0];
                            $originalHeight = $imageInfo[1];
                            $mimeType = $imageInfo['mime'];
                            
                            error_log("Original image: {$originalWidth}x{$originalHeight}, MIME: $mimeType");
                            
                            // Create a copy in temp directory with proper permissions
                            $tempImagePath = sys_get_temp_dir() . '/export_image_' . time() . '_' . mt_rand(1000, 9999) . '.jpg';
                            
                            // Always convert to JPEG for maximum Word compatibility
                            $this->convertImageToJpeg($imagePath, $tempImagePath, 400, 400);
                            
                            if (file_exists($tempImagePath)) {
                                error_log("Created temp JPEG image: $tempImagePath");
                                
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
                                error_log("Image successfully added to document: {$displayWidth}x{$displayHeight}");
                                
                                // Don't delete the temp image yet - PHPWord may need it during save
                                // We'll clean it up after the document is generated
                                $this->tempImagesToCleanup[] = $tempImagePath;
                                
                            } else {
                                throw new \Exception("Failed to create temporary image file");
                            }
                        } catch (\Exception $e) {
                            error_log("Error adding image to document: " . $e->getMessage());
                            error_log("Image path was: $imagePath");
                            // Add note that image couldn't be loaded
                            $section->addTextBreak();
                            $section->addText('Note: Image could not be embedded in document. (' . $attachment['original_filename'] . ')', ['italic' => true, 'color' => '666666']);
                            $section->addTextBreak();
                        }
                    } else {
                        error_log("Image file not accessible: $imagePath");
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
            error_log("GD extension not loaded, returning original image path");
            return $sourcePath; // Return original if GD not available
        }

        if (!file_exists($sourcePath)) {
            error_log("Image file not found: " . $sourcePath);
            return false;
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            error_log("Cannot get image info for: " . $sourcePath);
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
            error_log("Successfully created resized image: $tempPath");
            return $tempPath;
        } else {
            error_log("Failed to create resized image, returning original: $sourcePath");
            return $sourcePath;
        }
    }

    private function convertImageToJpeg($sourcePath, $outputPath, $maxWidth, $maxHeight)
    {
        if (!extension_loaded('gd')) {
            error_log("GD extension not loaded");
            return copy($sourcePath, $outputPath);
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            error_log("Cannot get image info for: $sourcePath");
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
                error_log("Unsupported image type: $imageType");
                return copy($sourcePath, $outputPath);
        }

        if (!$sourceImage) {
            error_log("Failed to create source image from: $sourcePath");
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
            error_log("Successfully converted image to JPEG: $outputPath ({$newWidth}x{$newHeight})");
            return true;
        } else {
            error_log("Failed to save JPEG image: $outputPath");
            return false;
        }
    }
    
    private function cleanupTempImages()
    {
        foreach ($this->tempImagesToCleanup as $tempPath) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
                error_log("Cleaned up temp image: $tempPath");
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
            error_log("Error deleting consultation note: " . $e->getMessage());
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
            error_log("Search API Debug - Search Term: " . $searchTerm);
            error_log("Search API Debug - Category: " . $category);
            error_log("Search API Debug - Company: " . $company);
            error_log("Search API Debug - Route: " . $route);
            error_log("Search API Debug - WHERE Clause: " . $whereClause);
            error_log("Search API Debug - Params: " . json_encode($params));
            
            // Also log the full SQL query for debugging
            $fullQuery = "SELECT ID, FirstName as drug_name, LastName as active_ingredient, price, Company, Pharmacology as category, Route as administration_route, SRDE, GI FROM drugs WHERE {$whereClause} {$orderBy} LIMIT ?";
            error_log("Search API Debug - Full Query: " . $fullQuery);
            
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
            
            error_log("Search API Debug - Results Count: " . count($drugs));
            if (count($drugs) > 0) {
                error_log("Search API Debug - First Result: " . json_encode($drugs[0]));
            }
            
            return $this->jsonResponse(['drugs' => $drugs]);

        } catch (Exception $e) {
            error_log("Error searching drugs: " . $e->getMessage());
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
            error_log("Error getting drug details: " . $e->getMessage());
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
            error_log("Error getting filter options: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Failed to get filter options'], 500);
        }
    }

    private function getDrugsDatabaseConnection()
    {
        // Connect to egyptian_drugs database with specific user
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $username = 'drug_user';  // Use the correct user for drugs database
        $password = 'DrugPassword123!';  // Use the correct password for drugs database
        
        $dsn = "mysql:host={$host};dbname=egyptian_drugs;charset=utf8mb4";
        
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
            error_log("Error getting most used drugs: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
            error_log("Error searching drugs autocomplete: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->jsonResponse(['error' => 'Failed to search drugs: ' . $e->getMessage()], 500);
        }
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