<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use App\Config\Constants;
use PDO;

class DoctorController
{
    private $auth;
    private $view;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Require doctor authentication (or admin in View As mode)
        $this->auth->requireRole(['doctor', 'admin']);
    }

    public function dashboard()
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get today's statistics
        $today = date('Y-m-d');
        $stats = $this->getTodayStats($doctorId, $today);
        
        // Get recent timeline events
        $recentEvents = $this->getRecentTimelineEvents($doctorId);
        
        // Get upcoming appointments
        $upcomingAppointments = $this->getUpcomingAppointments($doctorId);
        
        $content = $this->view->render('doctor/dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'upcomingAppointments' => $upcomingAppointments
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Doctor Dashboard',
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Welcome back, ' . $user['name'],
            'content' => $content
        ]);
    }

    public function calendar()
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get available dates for this doctor
        $availableDates = $this->getAvailableDates($doctorId);
        
        // Get patient_id from query string if provided
        $patientId = $_GET['patient_id'] ?? null;
        $patientInfo = null;
        
        if ($patientId) {
            $patientInfo = $this->getPatientInfo($patientId);
        }
        
        $content = $this->view->render('doctor/calendar', [
            'doctorId' => $doctorId,
            'availableDates' => $availableDates,
            'preselectedPatient' => $patientInfo
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Calendar - Doctor Dashboard',
            'pageTitle' => 'Calendar',
            'pageSubtitle' => 'Manage your appointments',
            'content' => $content
        ]);
    }
    
    public function patients()
    {
        $user = $this->auth->user();
        $patients = $this->getAllPatients();
        $doctors = $this->getAllDoctors();
        
        $content = $this->view->render('doctor/patients', [
            'patients' => $patients,
            'doctors' => $doctors
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Patients - Doctor Dashboard',
            'pageTitle' => 'Patients',
            'pageSubtitle' => 'Manage patient records',
            'content' => $content
        ]);
    }

    // ✅ FIXED: إضافة method showPatient المطلوب
    public function showPatient($id)
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get patient details
        $patient = $this->getPatient($id);
        if (!$patient) {
            http_response_code(404);
            echo "<h1>Patient not found</h1><p>The requested patient could not be found.</p>";
            return;
        }
        
        // Get patient timeline
        $timeline = $this->getPatientTimeline($id);
        
        // Get medical history
        $medicalHistory = $this->getMedicalHistory($id);
        
        // Get recent appointments
        $recentAppointments = $this->getPatientAppointments($id, $doctorId);
        
        // Get patient files
        $patientAttachments = $this->getPatientFiles($id);
        
        // Get patient notes
        $patientNotes = $this->getPatientNotes($id);
        
        // Get patient glasses prescriptions
        $glassesPrescriptions = $this->getPatientGlassesPrescriptions($id);
        
        // Get treating doctor info (the doctor who created the patient profile)
        $treatingDoctor = null;
        if (!empty($patient['created_by_doctor_name'])) {
            $treatingDoctor = [
                'name' => $patient['created_by_name'],
                'display_name' => $patient['created_by_doctor_name']
            ];
        } else {
            // Fallback to current doctor if no creator info available
            $treatingDoctor = $this->getCurrentDoctorInfo($user['id']);
        }
        
        $content = $this->view->render('doctor/patient', [
            'patient' => $patient,
            'timeline' => $timeline,
            'medicalHistory' => $medicalHistory,
            'recentAppointments' => $recentAppointments,
            'patientAttachments' => $patientAttachments,
            'patientNotes' => $patientNotes,
            'glassesPrescriptions' => $glassesPrescriptions,
            'doctorId' => $doctorId,
            'currentDoctor' => $treatingDoctor
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Patient Profile - Doctor Dashboard',
            'pageTitle' => 'Patient Profile',
            'pageSubtitle' => $patient['first_name'] . ' ' . $patient['last_name'],
            'content' => $content
        ]);
    }

    public function viewAppointment($id)
    {
        $user = $this->auth->user();
        
        // Get appointment details - available to all doctors
        $appointment = $this->getAppointmentForAllDoctors($id);
        if (!$appointment) {
            http_response_code(404);
            echo "<h1>Appointment not found</h1><p>The requested appointment could not be found.</p>";
            return;
        }
        
        // Get patient details
        $patient = $this->getPatient($appointment['patient_id']);
        
        // Get consultation notes if exists
        $consultationNotes = $this->getConsultationNotes($id);
        
        // Get prescriptions
        $medications = $this->getMedicationPrescriptions($id);
        $glasses = $this->getGlassesPrescriptions($id);
        
        // Get lab tests
        $labTests = $this->getLabTests($id);
        
        // Get attachments
        $attachments = $this->getAttachments($id);
        
        $content = $this->view->render('doctor/appointment', [
            'appointment' => $appointment,
            'patient' => $patient,
            'consultationNotes' => $consultationNotes,
            'medications' => $medications,
            'glasses' => $glasses,
            'labTests' => $labTests,
            'attachments' => $attachments,
            'doctorId' => $user['id']
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Appointment Details - Doctor Dashboard',
            'pageTitle' => 'Appointment Details',
            'pageSubtitle' => 'Manage patient consultation',
            'content' => $content
        ]);
    }

    public function profile()
    {
        $user = $this->auth->user();
        
        // Get doctor-specific information
        $stmt = $this->pdo->prepare("
            SELECT u.*, d.display_name as doctor_name, d.specialty 
            FROM users u 
            LEFT JOIN doctors d ON u.id = d.user_id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user['id']]);
        $userWithDoctorInfo = $stmt->fetch();
        
        $content = $this->view->render('doctor/profile', [
            'user' => $userWithDoctorInfo ?: $user
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Profile - Doctor Dashboard',
            'pageTitle' => 'My Profile',
            'pageSubtitle' => 'Manage your account settings',
            'content' => $content
        ]);
    }

    public function editConsultation($id)
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get appointment details - available to all doctors
        $appointment = $this->getAppointmentForAllDoctors($id);
        if (!$appointment) {
            http_response_code(404);
            echo "<h1>Appointment not found</h1><p>The requested appointment could not be found.</p>";
            return;
        }
        
        // Get patient details
        $patient = $this->getPatient($appointment['patient_id']);
        
        // Get consultation notes if exists
        $consultationNotes = $this->getConsultationNotes($id);
        
        // Check if editing specific note
        $noteId = $_GET['note_id'] ?? null;
        if ($noteId && !empty($consultationNotes)) {
            // Find the specific note and move it to the front
            foreach ($consultationNotes as $index => $note) {
                if ($note['id'] == $noteId) {
                    // Move this note to the front
                    $selectedNote = $consultationNotes[$index];
                    unset($consultationNotes[$index]);
                    array_unshift($consultationNotes, $selectedNote);
                    break;
                }
            }
        }
        
        // Generate CSRF token
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $content = $this->view->render('doctor/edit_consultation', [
            'appointment' => $appointment,
            'patient' => $patient,
            'consultationNotes' => $consultationNotes,
            'doctorId' => $doctorId
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Edit Consultation - Doctor Dashboard',
            'pageTitle' => 'Edit Consultation',
            'pageSubtitle' => 'Update consultation notes',
            'content' => $content
        ]);
    }

    public function newConsultation($id)
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get appointment details - available to all doctors
        $appointment = $this->getAppointmentForAllDoctors($id);
        if (!$appointment) {
            http_response_code(404);
            echo "<h1>Appointment not found</h1><p>The requested appointment could not be found.</p>";
            return;
        }
        
        // Get patient details
        $patient = $this->getPatient($appointment['patient_id']);
        
        // Force empty consultation notes for new note
        $consultationNotes = [];
        
        // Generate CSRF token
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $content = $this->view->render('doctor/edit_consultation', [
            'appointment' => $appointment,
            'patient' => $patient,
            'consultationNotes' => $consultationNotes,
            'doctorId' => $doctorId
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'New Consultation - Doctor Dashboard',
            'pageTitle' => 'New Consultation',
            'pageSubtitle' => 'Add new consultation notes',
            'content' => $content
        ]);
    }

    public function updateConsultation($id)
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get appointment details - available to all doctors
        $appointment = $this->getAppointmentForAllDoctors($id);
        if (!$appointment) {
            http_response_code(404);
            echo "<h1>Appointment not found</h1><p>The requested appointment could not be found.</p>";
            return;
        }
        
        // CSRF Protection
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            http_response_code(403);
            header('Location: /doctor/appointments/' . $id . '/edit?error=' . urlencode('Invalid CSRF token'));
            exit;
        }
        
        try {
            // Check if we're updating existing note or creating new one
            $noteId = $_POST['note_id'] ?? null;
            
            if ($noteId) {
                // Update existing consultation note
            $stmt = $this->pdo->prepare("
                UPDATE consultation_notes SET 
                chief_complaint = ?, hx_present_illness = ?, visual_acuity_right = ?, visual_acuity_left = ?,
                refraction_right = ?, refraction_left = ?, IOP_right = ?, IOP_left = ?, 
                slit_lamp_right = ?, slit_lamp_left = ?, fundus_right = ?, fundus_left = ?,
                external_appearance_right = ?, external_appearance_left = ?, eyelid_right = ?, eyelid_left = ?,
                diagnosis = ?, diagnosis_code = ?, systemic_disease = ?, medication = ?, 
                plan = ?, followup_days = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND appointment_id = ?
            ");
            } else {
                // Create new consultation note
                $stmt = $this->pdo->prepare("
                    INSERT INTO consultation_notes (appointment_id, chief_complaint, hx_present_illness, 
                    visual_acuity_right, visual_acuity_left, refraction_right, refraction_left, 
                    IOP_right, IOP_left, slit_lamp_right, slit_lamp_left, fundus_right, fundus_left,
                    external_appearance_right, external_appearance_left, eyelid_right, eyelid_left,
                    diagnosis, diagnosis_code, systemic_disease, medication, plan, followup_days, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
            }
            
            // Process and validate input data
            // Allow IOP values with + and - signs, convert to float if numeric
            $iopRight = null;
            if (!empty($_POST['IOP_right'])) {
                $iopValue = trim($_POST['IOP_right']);
                if (is_numeric($iopValue)) {
                    $iopRight = (float)$iopValue;
                } else {
                    // Store as text if not purely numeric (e.g., contains + or -)
                    $iopRight = $iopValue;
                }
            }
            
            $iopLeft = null;
            if (!empty($_POST['IOP_left'])) {
                $iopValue = trim($_POST['IOP_left']);
                if (is_numeric($iopValue)) {
                    $iopLeft = (float)$iopValue;
                } else {
                    // Store as text if not purely numeric (e.g., contains + or -)
                    $iopLeft = $iopValue;
                }
            }
            
            $followupDays = (!empty($_POST['followup_days']) && is_numeric($_POST['followup_days'])) ? (int)$_POST['followup_days'] : null;
            
            // Helper function to handle empty strings
            $processTextField = function($value) {
                return !empty(trim($value ?? '')) ? trim($value) : null;
            };
            
            if ($noteId) {
                // Execute UPDATE query
                $updateData = [
                    $processTextField($_POST['chief_complaint']),
                    $processTextField($_POST['hx_present_illness']),
                    $processTextField($_POST['visual_acuity_right']),
                    $processTextField($_POST['visual_acuity_left']),
                    $processTextField($_POST['refraction_right']),
                    $processTextField($_POST['refraction_left']),
                    $iopRight,
                    $iopLeft,
                    $processTextField($_POST['slit_lamp_right']),
                    $processTextField($_POST['slit_lamp_left']),
                    $processTextField($_POST['fundus_right']),
                    $processTextField($_POST['fundus_left']),
                    $processTextField($_POST['external_appearance_right']),
                    $processTextField($_POST['external_appearance_left']),
                    $processTextField($_POST['eyelid_right']),
                    $processTextField($_POST['eyelid_left']),
                    $processTextField($_POST['diagnosis']),
                    $processTextField($_POST['diagnosis_code']),
                    $processTextField($_POST['systemic_disease']),
                    $processTextField($_POST['medication']),
                    $processTextField($_POST['plan']),
                    $followupDays,
                    $noteId,
                    $id
                ];
                $result = $stmt->execute($updateData);
            } else {
                // Execute INSERT query
                $insertData = [
                    $id,
                    $processTextField($_POST['chief_complaint']),
                    $processTextField($_POST['hx_present_illness']),
                    $processTextField($_POST['visual_acuity_right']),
                    $processTextField($_POST['visual_acuity_left']),
                    $processTextField($_POST['refraction_right']),
                    $processTextField($_POST['refraction_left']),
                    $iopRight,
                    $iopLeft,
                    $processTextField($_POST['slit_lamp_right']),
                    $processTextField($_POST['slit_lamp_left']),
                    $processTextField($_POST['fundus_right']),
                    $processTextField($_POST['fundus_left']),
                    $processTextField($_POST['external_appearance_right']),
                    $processTextField($_POST['external_appearance_left']),
                    $processTextField($_POST['eyelid_right']),
                    $processTextField($_POST['eyelid_left']),
                    $processTextField($_POST['diagnosis']),
                    $processTextField($_POST['diagnosis_code']),
                    $processTextField($_POST['systemic_disease']),
                    $processTextField($_POST['medication']),
                    $processTextField($_POST['plan']),
                    $followupDays,
                    $user['id']
                ];
                $result = $stmt->execute($insertData);
                if ($result) {
                    $newNoteId = $this->pdo->lastInsertId();
                }
            }
            
            // Redirect back to appointment view
            header('Location: /doctor/appointments/' . $id . '?success=1');
            exit;
            
        } catch (\Exception $e) {
            header('Location: /doctor/appointments/' . $id . '/edit?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function changePassword()
    {
        $user = $this->auth->user();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }
        
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($newPassword) || empty($confirmPassword)) {
            header('Location: /doctor/profile?error=All fields are required');
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            header('Location: /doctor/profile?error=New passwords do not match');
            exit;
        }
        
        if (strlen($newPassword) < 8) {
            header('Location: /doctor/profile?error=Password must be at least 8 characters');
            exit;
        }
        
        // Password complexity validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $newPassword)) {
            header('Location: /doctor/profile?error=Password must contain uppercase, lowercase, and numbers');
            exit;
        }
        
        try {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            header('Location: /doctor/profile?success=Password updated successfully');
            exit;
            
        } catch (\Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            header('Location: /doctor/profile?error=Failed to update password');
            exit;
        }
    }

    public function updateProfile()
    {
        $user = $this->auth->user();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            header('Location: /doctor/profile?error=Invalid security token');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $doctorName = trim($_POST['doctor_name'] ?? '');
        $specialty = trim($_POST['specialty'] ?? 'Ophthalmology');
        
        // Validate input
        if (empty($name)) {
            header('Location: /doctor/profile?error=Full name is required');
            exit;
        }
        
        if (empty($email)) {
            header('Location: /doctor/profile?error=Email is required');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /doctor/profile?error=Please enter a valid email address');
            exit;
        }
        
        try {
            // Check if email is already taken by another user
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                header('Location: /doctor/profile?error=Email is already taken by another user');
                exit;
            }
            
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Update user table
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $user['id']]);
            
            // Update doctor table if doctor-specific fields are provided
            if (!empty($doctorName) || !empty($specialty)) {
                $doctorId = $this->getDoctorId($user['id']);
                if ($doctorId) {
                    $stmt = $this->pdo->prepare("
                        UPDATE doctors 
                        SET display_name = ?, specialty = ?, updated_at = NOW() 
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$doctorName ?: $name, $specialty, $user['id']]);
                }
            }
            
            // Commit transaction
            $this->pdo->commit();
            
            // Update session data with all new information
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            
            // Add doctor-specific data to session if available
            if (!empty($doctorName)) {
                $_SESSION['user']['doctor_name'] = $doctorName;
            }
            if (!empty($specialty)) {
                $_SESSION['user']['specialty'] = $specialty;
            }
            
            header('Location: /doctor/profile?success=Profile updated successfully&updated=1');
            exit;
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->pdo->rollBack();
            error_log("Error updating profile for user {$user['id']}: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // More specific error message for debugging
            $errorMsg = 'Failed to update profile';
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $errorMsg = 'Database column error - please contact support';
            } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errorMsg = 'Email already exists';
            }
            
            header('Location: /doctor/profile?error=' . urlencode($errorMsg));
            exit;
        }
    }

    public function updateField()
    {
        $user = $this->auth->user();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            return;
        }
        
        $field = $input['field'] ?? '';
        $value = trim($input['value'] ?? '');
        $csrfToken = $input['csrf_token'] ?? '';
        
        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            return;
        }
        
        // Validate field name
        $allowedFields = ['name', 'email', 'phone', 'doctor_name', 'specialty'];
        if (!in_array($field, $allowedFields)) {
            echo json_encode(['success' => false, 'message' => 'Invalid field name']);
            return;
        }
        
        // Validate required fields
        if (in_array($field, ['name', 'email']) && empty($value)) {
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' cannot be empty']);
            return;
        }
        
        // Validate email format
        if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            return;
        }
        
        try {
            // Check if email is already taken by another user
            if ($field === 'email') {
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$value, $user['id']]);
                if ($stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Email is already taken by another user']);
                    return;
                }
            }
            
            // Start transaction
            $this->pdo->beginTransaction();
            
            if (in_array($field, ['name', 'email', 'phone'])) {
                // Update users table
                $stmt = $this->pdo->prepare("
                    UPDATE users 
                    SET {$field} = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$value, $user['id']]);
                
                // Update session data
                $_SESSION['user'][$field] = $value;
                
            } elseif (in_array($field, ['doctor_name', 'specialty'])) {
                // Update doctors table
                $doctorId = $this->getDoctorId($user['id']);
                if ($doctorId) {
                    $stmt = $this->pdo->prepare("
                        UPDATE doctors 
                        SET {$field} = ?, updated_at = NOW() 
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$value, $user['id']]);
                    
                    // Update session data
                    $_SESSION['user'][$field] = $value;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Doctor profile not found']);
                    return;
                }
            }
            
            // Commit transaction
            $this->pdo->commit();
            
            echo json_encode(['success' => true, 'message' => ucfirst($field) . ' updated successfully']);
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->pdo->rollBack();
            error_log("Error updating field {$field} for user {$user['id']}: " . $e->getMessage());
            
            echo json_encode(['success' => false, 'message' => 'Failed to update ' . $field]);
        }
    }

    // Private helper methods
    private function validateCsrfToken()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    private function getDoctorId($userId)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    private function getTodayStats($doctorId, $date)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Booked' THEN 1 ELSE 0 END) as booked,
                SUM(CASE WHEN status = 'CheckedIn' THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN status = 'InProgress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM appointments 
            WHERE doctor_id = ? AND date = ?
        ");
        $stmt->execute([$doctorId, $date]);
        return $stmt->fetch();
    }

    private function getRecentTimelineEvents($doctorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT te.*, p.first_name, p.last_name, p.phone
            FROM timeline_events te
            JOIN patients p ON te.patient_id = p.id
            JOIN appointments a ON te.appointment_id = a.id
            WHERE a.doctor_id = ?
            ORDER BY te.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    private function getUpcomingAppointments($doctorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = ? AND a.date >= CURDATE() AND a.status IN ('Booked', 'CheckedIn')
            ORDER BY a.date ASC, a.start_time ASC
            LIMIT 5
        ");
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    private function getAvailableDates($doctorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT weekday, work_start, work_end
            FROM doctor_schedule
            WHERE doctor_id = ? AND is_working = 1
            ORDER BY weekday
        ");
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    private function getPatient($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, mh.allergies, mh.medications, mh.systemic_history, mh.ocular_history,
                   (SELECT u.name 
                    FROM timeline_events te 
                    LEFT JOIN users u ON te.actor_user_id = u.id
                    WHERE te.patient_id = p.id 
                    AND te.event_type = 'Booking' 
                    AND te.event_summary LIKE '%New patient registered%' 
                    ORDER BY te.created_at ASC 
                    LIMIT 1) as created_by_name,
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

    private function getMedicalHistory($patientId)
    {
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
                'ocular_history' => ($entry['category'] === 'general' && strpos(strtolower($entry['condition_name']), 'eye') !== false) ? $entry['notes'] : null,
                'prior_surgeries' => ($entry['category'] === 'surgery') ? $entry['notes'] : null,
                'family_history' => ($entry['category'] === 'family_history') ? $entry['notes'] : null,
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
        
        return $allEntries;
    }

    private function getPatientAppointments($patientId, $doctorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, cn.diagnosis, cn.plan, d.name as doctor_name
            FROM appointments a
            LEFT JOIN consultation_notes cn ON a.id = cn.appointment_id
            LEFT JOIN users d ON a.doctor_id = d.id
            WHERE a.patient_id = ?
            ORDER BY a.date DESC, a.start_time DESC
            LIMIT 10
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    private function getAppointment($id, $doctorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.first_name, p.last_name, p.phone, p.dob, p.gender,
                   YEAR(CURDATE()) - YEAR(p.dob) as patient_age
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.id = ? AND a.doctor_id = ?
        ");
        $stmt->execute([$id, $doctorId]);
        return $stmt->fetch();
    }

    private function getAppointmentForAllDoctors($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.first_name, p.last_name, p.phone, p.dob, p.gender,
                   YEAR(CURDATE()) - YEAR(p.dob) as patient_age,
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

    private function getConsultationNotes($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM consultation_notes WHERE appointment_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetchAll();
    }

    private function getMedicationPrescriptions($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM prescriptions WHERE appointment_id = ?
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetchAll();
    }

    private function getGlassesPrescriptions($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM glasses_prescriptions WHERE appointment_id = ?
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetchAll();
    }

    private function getLabTests($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM lab_tests 
            WHERE appointment_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetchAll();
    }

    private function getPatientGlassesPrescriptions($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT g.*, 
                   a.id as appointment_id,
                   a.date as appointment_date,
                   a.start_time as appointment_time,
                   CONCAT(d.display_name) as doctor_name
            FROM glasses_prescriptions g
            JOIN appointments a ON g.appointment_id = a.id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    private function getAttachments($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM patient_attachments 
            WHERE appointment_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetchAll();
    }
    
    private function getAllPatients()
    {
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
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getAllDoctors()
    {
        $stmt = $this->pdo->prepare("
            SELECT d.id, d.display_name, d.specialty
            FROM doctors d
            WHERE d.user_id IN (
                SELECT id FROM users WHERE role = 'doctor' AND is_active = 1
            )
            ORDER BY d.display_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getPatientInfo($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, first_name, last_name, phone, dob, gender,
                   YEAR(CURDATE()) - YEAR(dob) as age
            FROM patients 
            WHERE id = ?
        ");
        $stmt->execute([$patientId]);
        $patient = $stmt->fetch();
        
        if ($patient) {
            $patient['full_name'] = $patient['first_name'] . ' ' . $patient['last_name'];
        }
        
        return $patient;
    }
    
    public function saveConsultation($id)
    {
        // This is an alias for updateConsultation to maintain compatibility with different route configurations
        return $this->updateConsultation($id);
    }
    
    private function getPatientFiles($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT pf.*, u.name as uploaded_by_name
            FROM patient_files pf
            LEFT JOIN users u ON pf.uploaded_by = u.id
            WHERE pf.patient_id = ?
            ORDER BY pf.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }
    
    private function getPatientNotes($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT pn.*, u.name as doctor_name
            FROM patient_notes pn
            LEFT JOIN users u ON pn.doctor_id = u.id
            WHERE pn.patient_id = ?
            ORDER BY pn.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }
    
    private function getCurrentDoctorInfo($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.name, d.display_name, d.specialty
            FROM users u
            LEFT JOIN doctors d ON u.id = d.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function editPatient($id)
    {
        $user = $this->auth->user();
        
        // Get patient details
        $patient = $this->getPatient($id);
        if (!$patient) {
            http_response_code(404);
            echo "<h1>Patient not found</h1><p>The requested patient could not be found.</p>";
            return;
        }
        
        $content = $this->view->render('doctor/edit_patient', [
            'patient' => $patient
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Edit Patient - Doctor Dashboard',
            'pageTitle' => 'Edit Patient',
            'pageSubtitle' => $patient['first_name'] . ' ' . $patient['last_name'],
            'content' => $content
        ]);
    }
    
    public function updatePatient($id)
    {
        try {
            $user = $this->auth->user();
            
            // Validate input
            $requiredFields = ['first_name', 'last_name', 'phone'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => "Field {$field} is required"]);
                    return;
                }
            }
            
            // Prepare update query
            $stmt = $this->pdo->prepare("
                UPDATE patients SET 
                    first_name = ?, 
                    last_name = ?, 
                    phone = ?,
                    alt_phone = ?,
                    address = ?,
                    national_id = ?,
                    dob = ?,
                    emergency_contact = ?,
                    emergency_phone = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['phone'],
                $_POST['alt_phone'] ?? null,
                $_POST['address'] ?? null,
                $_POST['national_id'] ?? null,
                $_POST['dob'] ?? null,
                $_POST['emergency_contact'] ?? null,
                $_POST['emergency_phone'] ?? null,
                $id
            ]);
            
            if ($result) {
                // Redirect back to patient profile
                header("Location: /doctor/patients/{$id}");
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update patient']);
            }
            
        } catch (Exception $e) {
            error_log("Error updating patient: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }

    public function reports()
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get report parameters
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $reportType = $_GET['type'] ?? 'appointments';
        
        // Generate report data specific to this doctor
        $reportData = $this->generateDoctorReport($doctorId, $reportType, $startDate, $endDate);
        
        $content = $this->view->render('doctor/reports', [
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType,
            'doctorId' => $doctorId
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Reports - Doctor Dashboard',
            'pageTitle' => 'Medical Reports',
            'pageSubtitle' => 'View your practice reports',
            'content' => $content
        ]);
    }

    public function drugs()
    {
        $user = $this->auth->user();
        
        $content = $this->view->render('doctor/drugs', [
            'user' => $user
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Drug Search - Doctor Dashboard',
            'pageTitle' => 'Drug Search',
            'pageSubtitle' => 'Search and browse medications',
            'content' => $content
        ]);
    }

    public function settings()
    {
        $user = $this->auth->user();
        
        try {
            // Get system settings
            $settings = $this->getSystemSettings();
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$this->validateCsrfToken()) {
                    throw new Exception('Invalid CSRF token');
                }
                
                // Handle file uploads
                $this->handleLogoUploads();
                
                $this->updateSystemSettings($_POST);
                $_SESSION['success_message'] = 'Settings updated successfully';
                header('Location: /doctor/settings');
                exit;
            }

            // Generate CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            $content = $this->view->render('doctor/settings', [
                'settings' => $settings,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            
            echo $this->view->render('layouts/main', [
                'title' => 'Settings - Doctor Dashboard',
                'pageTitle' => 'Settings',
                'pageSubtitle' => 'Manage system configuration',
                'content' => $content
            ]);
        } catch (Exception $e) {
            error_log("Settings error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Failed to load settings: ' . $e->getMessage();
            header('Location: /doctor/dashboard');
            exit;
        }
    }

    private function getSystemSettings()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_key, setting_value, setting_type FROM settings");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = [];
            foreach ($settings as $setting) {
                $key = $setting['setting_key'];
                $value = $setting['setting_value'];
                $type = $setting['setting_type'];
                
                // Convert value based on type
                switch ($type) {
                    case 'integer':
                        $result[$key] = (int) $value;
                        break;
                    case 'boolean':
                        $result[$key] = (bool) $value;
                        break;
                    case 'json':
                        $result[$key] = json_decode($value, true);
                        break;
                    default:
                        $result[$key] = $value;
                }
            }
            
            // Set defaults for missing settings
            $defaults = [
                'clinic_name' => 'Roaya Clinic',
                'clinic_name_arabic' => 'رؤية لطب وجراحة العيون',
                'clinic_email' => 'info@roayaclinic.com',
                'clinic_phone' => '+20 123 456 7890',
                'clinic_address' => 'Cairo, Egypt',
                'clinic_logo' => '/assets/images/Light.png',
                'clinic_logo_print' => '/assets/images/Light.png',
                'clinic_logo_watermark' => '/assets/images/Light.png',
                'new_visit_cost' => '100',
                'repeated_visit_cost' => '50',
                'timezone' => 'Africa/Cairo',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'items_per_page' => 10,
                'backup_frequency' => 'daily',
                'email_notifications' => true,
                'sms_notifications' => false,
                'maintenance_mode' => false
            ];
            
            return array_merge($defaults, $result);
        } catch (Exception $e) {
            error_log("Error getting settings: " . $e->getMessage());
            return [
                'clinic_name' => 'Roaya Clinic',
                'clinic_email' => 'info@roayaclinic.com',
                'clinic_phone' => '+20 123 456 7890',
                'clinic_address' => 'Cairo, Egypt',
                'timezone' => 'Africa/Cairo',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'items_per_page' => 10,
                'backup_frequency' => 'daily',
                'email_notifications' => true,
                'sms_notifications' => false,
                'maintenance_mode' => false
            ];
        }
    }

    private function updateSystemSettings($data)
    {
        $allowedSettings = [
            'clinic_name', 'clinic_email', 'clinic_phone', 'clinic_address',
            'timezone', 'date_format', 'time_format', 'items_per_page',
            'backup_frequency', 'email_notifications', 'sms_notifications', 'maintenance_mode'
        ];

        try {
            $this->pdo->beginTransaction();
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedSettings)) {
                    // Validate and sanitize the value
                    if ($key === 'clinic_email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email address');
                    }
                    if ($key === 'items_per_page' && (!is_numeric($value) || $value < 1 || $value > 100)) {
                        throw new Exception('Items per page must be between 1 and 100');
                    }
                    
                    // Determine setting type and convert value
                    $settingType = 'string';
                    if (in_array($key, ['email_notifications', 'sms_notifications', 'maintenance_mode'])) {
                        $value = (bool) $value;
                        $settingType = 'boolean';
                    } elseif ($key === 'items_per_page') {
                        $value = (int) $value;
                        $settingType = 'integer';
                    }
                    
                    // Convert boolean to string for database storage
                    if ($settingType === 'boolean') {
                        $dbValue = $value ? '1' : '0';
                    } else {
                        $dbValue = (string) $value;
                    }
                    
                    // Insert or update setting
                    $stmt = $this->pdo->prepare("
                        INSERT INTO settings (setting_key, setting_value, setting_type) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value), 
                        setting_type = VALUES(setting_type),
                        updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$key, $dbValue, $settingType]);
                }
            }
            
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function exportDoctorReport()
    {
        try {
            $user = $this->auth->user();
            $doctorId = $this->getDoctorId($user['id']);
            
            $reportType = $_GET['type'] ?? 'appointments';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $format = $_GET['format'] ?? 'csv';
            
            // Generate report data
            $reportData = $this->generateDoctorReport($doctorId, $reportType, $startDate, $endDate);
            
            // Export based on format
            if ($format === 'csv') {
                $this->exportToCsv($reportData, $reportType, $startDate, $endDate);
            } else {
                throw new \Exception('Unsupported export format');
            }
            
        } catch (\Exception $e) {
            header('Location: /doctor/reports?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    private function generateDoctorReport($doctorId, $type, $startDate, $endDate)
    {
        switch ($type) {
            case 'appointments':
                return $this->generateDoctorAppointmentsReport($doctorId, $startDate, $endDate);
            case 'patients':
                return $this->generateDoctorPatientsReport($doctorId, $startDate, $endDate);
            case 'revenue':
                return $this->generateDoctorRevenueReport($doctorId, $startDate, $endDate);
            default:
                return $this->generateDoctorAppointmentsReport($doctorId, $startDate, $endDate);
        }
    }

    private function generateDoctorAppointmentsReport($doctorId, $startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(a.date) as date,
                COUNT(*) as total_appointments,
                SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN a.status = 'NoShow' THEN 1 ELSE 0 END) as no_show
            FROM appointments a
            WHERE a.doctor_id = ? AND DATE(a.date) BETWEEN ? AND ?
            GROUP BY DATE(a.date)
            ORDER BY date
        ");
        $stmt->execute([$doctorId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateDoctorPatientsReport($doctorId, $startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(p.created_at) as date,
                COUNT(DISTINCT p.id) as new_patients,
                SUM(CASE WHEN p.gender = 'Male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN p.gender = 'Female' THEN 1 ELSE 0 END) as female
            FROM patients p
            JOIN appointments a ON p.id = a.patient_id
            WHERE a.doctor_id = ? AND DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE(p.created_at)
            ORDER BY date
        ");
        $stmt->execute([$doctorId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateDoctorRevenueReport($doctorId, $startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(p.created_at) as date,
                SUM(p.amount) as daily_revenue,
                COUNT(*) as transactions,
                SUM(p.discount_amount) as discounts
            FROM payments p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE a.doctor_id = ? AND DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE(p.created_at)
            ORDER BY date
        ");
        $stmt->execute([$doctorId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function exportToCsv($data, $type, $startDate, $endDate)
    {
        $filename = "doctor_{$type}_report_{$startDate}_to_{$endDate}.csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }

    private function handleLogoUploads()
    {
        $uploadDir = '/var/www/html/clinic/public/uploads/logos/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $logoFields = ['clinic_logo_print', 'clinic_logo_watermark']; // clinic_logo disabled
        
        foreach ($logoFields as $field) {
            // Handle file upload
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$field];
                
                // Validate file type
                if (!in_array($file['type'], $allowedTypes)) {
                    throw new \Exception("Invalid file type for {$field}. Only JPEG, PNG, GIF, and SVG are allowed.");
                }
                
                // Validate file size
                if ($file['size'] > $maxSize) {
                    throw new \Exception("File too large for {$field}. Maximum size is 5MB.");
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $field . '_' . time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Update setting with new file path
                    $this->updateSetting($field, '/uploads/logos/' . $filename);
                } else {
                    throw new \Exception("Failed to upload {$field}");
                }
            }
            // Handle text path input
            elseif (isset($_POST[$field . '_path']) && !empty($_POST[$field . '_path'])) {
                $path = $_POST[$field . '_path'];
                // Validate that it's a valid path
                if (filter_var($path, FILTER_VALIDATE_URL) || (strpos($path, '/') === 0 && file_exists('/var/www/html/clinic/public' . $path))) {
                    $this->updateSetting($field, $path);
                }
            }
        }
    }

    private function updateSetting($key, $value)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, updated_at) 
                VALUES (?, ?, 'string', NOW())
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value), 
                updated_at = NOW()
            ");
            $stmt->execute([$key, $value]);
        } catch (\Exception $e) {
            error_log("Error updating setting {$key}: " . $e->getMessage());
            throw $e;
        }
    }
}
