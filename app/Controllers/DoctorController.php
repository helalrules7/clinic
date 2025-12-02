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
        
        // Don't require role in constructor - let each method handle it
        // This allows API methods to handle authentication differently
    }
    
    /**
     * Helper method to require doctor/admin role
     * Use this in non-API methods
     */
    private function requireDoctorRole()
    {
        $this->auth->requireRole(['doctor', 'admin']);
    }

    /**
     * Helper method to check authentication for API methods
     * Returns true if authenticated, false otherwise
     */
    private function checkApiAuth()
    {
        if (!$this->auth->check()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        
        return true;
    }

    public function dashboard()
    {
        $this->requireDoctorRole();
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
    
    public function organizer()
    {
        $this->requireDoctorRole();
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        $content = $this->view->render('doctor/organizer', [
            'doctorId' => $doctorId
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Organizer - Doctor Dashboard',
            'pageTitle' => 'Organizer',
            'pageSubtitle' => 'Monthly calendar with appointments, notes, and alerts',
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
        
        // Get all patient appointments with prescriptions for history
        $allAppointments = $this->getAllPatientAppointmentsWithDetails($id);
        
        // Get all medications prescriptions for patient
        $allMedications = $this->getAllPatientMedications($id);
        
        // Get all glasses prescriptions for patient
        $allGlasses = $this->getAllPatientGlasses($id);
        
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
            // Get full doctor info including profile_image
            $stmt = $this->pdo->prepare("
                SELECT u.name, u.profile_image, d.display_name, d.specialty
                FROM timeline_events te
                LEFT JOIN users u ON te.actor_user_id = u.id
                LEFT JOIN doctors d ON u.id = d.user_id
                WHERE te.patient_id = ? 
                AND te.event_type = 'Booking' 
                AND te.event_summary LIKE '%New patient registered%' 
                ORDER BY te.created_at ASC 
                    LIMIT 1
                ");
            $stmt->execute([$id]);
            $treatingDoctor = $stmt->fetch();
            if (!$treatingDoctor) {
                $treatingDoctor = [
                    'name' => $patient['created_by_name'],
                    'display_name' => $patient['created_by_doctor_name'],
                    'profile_image' => null
                ];
            }
                    } else {
            // Fallback to current doctor if no creator info available
            $treatingDoctor = $this->getCurrentDoctorInfo($user['id']);
        }
        
        $content = $this->view->render('doctor/patient', [
            'allAppointments' => $allAppointments,
            'allMedications' => $allMedications,
            'allGlasses' => $allGlasses,
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
        
        // Check if there's a follow-up appointment created for this appointment
        $followupAppointment = $this->getFollowupAppointment($id);
        
        // Check if this appointment is a follow-up (has original appointment)
        $originalAppointment = null;
        if ($appointment['visit_type'] === 'FollowUp') {
            $originalAppointment = $this->getOriginalAppointment($id);
        }
        
        // Get medical history for the patient
        $medicalHistory = $this->getMedicalHistory($appointment['patient_id']);
        
        $content = $this->view->render('doctor/appointment', [
            'appointment' => $appointment,
            'patient' => $patient,
            'consultationNotes' => $consultationNotes,
            'medications' => $medications,
            'glasses' => $glasses,
            'labTests' => $labTests,
            'attachments' => $attachments,
            'doctorId' => $user['id'],
            'followupAppointment' => $followupAppointment,
            'originalAppointment' => $originalAppointment,
            'medicalHistory' => $medicalHistory
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
        
        // Get doctor-specific information including profile_image
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
            
            // Automatically create medical history entry from consultation (for both insert and update)
            if (!empty($_POST['diagnosis']) && !empty($appointment['patient_id'])) {
                $this->createMedicalHistoryFromConsultation($appointment['patient_id'], $_POST, $appointment, $user['id']);
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
        $profileImage = null;
        
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/users/';
            
            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                @chmod($uploadDir, 0777);
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            $file = $_FILES['profile_image'];
            
            // Log upload attempt
            
            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            
            if (!in_array($mimeType, $allowedTypes)) {
                header('Location: /doctor/profile?error=Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
                exit;
            }
            
            // Validate file size
            if ($file['size'] > $maxSize) {
                header('Location: /doctor/profile?error=File size exceeds 5MB limit.');
                exit;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $user['id'] . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            
            // Delete old profile image if exists
            $stmt = $this->pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $oldImage = $stmt->fetchColumn();
            if ($oldImage && file_exists(__DIR__ . '/../../public' . $oldImage)) {
                @unlink(__DIR__ . '/../../public' . $oldImage);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $profileImage = '/uploads/users/' . $filename;
            } else {
                $errorMsg = "Failed to upload image. Upload dir: $uploadDir, Writable: " . (is_writable($uploadDir) ? 'yes' : 'no');
                header('Location: /doctor/profile?error=Failed to upload image. Please check server permissions.');
                exit;
            }
        }
        
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
            
            // Update user table with or without profile_image
            if ($profileImage) {
                $stmt = $this->pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, profile_image = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone, $profileImage, $user['id']]);
            } else {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $user['id']]);
            }
            
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
            if ($profileImage) {
                $_SESSION['user']['profile_image'] = $profileImage;
            }
            
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
        $today = date('Y-m-d');
        
        // Get today's stats
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Booked' THEN 1 ELSE 0 END) as booked,
                SUM(CASE WHEN status = 'CheckedIn' THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN status NOT IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM appointments 
            WHERE doctor_id = ? AND date = ?
        ");
        $stmt->execute([$doctorId, $date]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$stats) {
            $stats = [
                'total' => 0,
                'booked' => 0,
                'checked_in' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }
        
        // Get missed appointments count (previous days, not completed)
        $missedStmt = $this->pdo->prepare("
            SELECT COUNT(*) as missed_appointments
            FROM appointments 
            WHERE doctor_id = ? 
            AND date < ?
            AND status != 'Completed'
            AND status != 'Cancelled'
        ");
        $missedStmt->execute([$doctorId, $today]);
        $missedCount = $missedStmt->fetchColumn();
        $stats['missed_appointments'] = (int)$missedCount;
        
        return $stats;
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
                   CONCAT(u.name) as doctor_name,
                   u.profile_image as doctor_profile_image,
                   d.display_name as doctor_display_name
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

    private function getAllPatientMedications($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, 
                   a.id as appointment_id,
                   a.date as appointment_date,
                   a.start_time as appointment_time,
                   a.status as appointment_status,
                   CONCAT(u.name) as doctor_name,
                   d.display_name as doctor_display_name
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE a.patient_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    private function getAllPatientGlasses($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT g.*, 
                   a.id as appointment_id,
                   a.date as appointment_date,
                   a.start_time as appointment_time,
                   a.status as appointment_status,
                   CONCAT(u.name) as doctor_name,
                   d.display_name as doctor_display_name
            FROM glasses_prescriptions g
            JOIN appointments a ON g.appointment_id = a.id
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE a.patient_id = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }
    
    private function getAllPatientAppointmentsWithDetails($patientId)
    {
        // Check if rescheduled_from column exists
        $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
        $hasRescheduledFrom = $columnStmt->rowCount() > 0;
        
        // Get all appointments for the patient
        $stmt = $this->pdo->prepare("
            SELECT a.*, 
                   CONCAT(u.name) as doctor_name,
                   d.display_name as doctor_display_name
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE a.patient_id = ?
            ORDER BY a.date DESC, a.start_time DESC
        ");
        $stmt->execute([$patientId]);
        $appointments = $stmt->fetchAll();
        
        // For each appointment, get prescriptions, glasses, attachments, and follow-up info
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
            $attachmentsStmt = $this->pdo->prepare("SELECT * FROM patient_attachments WHERE appointment_id = ? ORDER BY created_at DESC");
            $attachmentsStmt->execute([$appointment['id']]);
            $appointment['attachments'] = $attachmentsStmt->fetchAll();
        }
        
        return $appointments;
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

    private function getFollowupAppointment($appointmentId)
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

    private function getOriginalAppointment($followupAppointmentId)
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
            SELECT d.id, d.display_name, d.specialty, u.profile_image
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE u.role = 'doctor' AND u.is_active = 1
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

    /**
     * Automatically create medical history entry from consultation data
     */
    private function createMedicalHistoryFromConsultation($patientId, $consultationData, $appointmentData, $userId)
    {
        try {
            // Only create if diagnosis is provided
            $diagnosis = is_array($consultationData) ? ($consultationData['diagnosis'] ?? '') : ($consultationData['diagnosis'] ?? '');
            if (empty($diagnosis)) {
                return false;
            }

            // Build notes from consultation data
            $notesParts = [];
            
            $chiefComplaint = is_array($consultationData) ? ($consultationData['chief_complaint'] ?? '') : ($consultationData['chief_complaint'] ?? '');
            if (!empty($chiefComplaint)) {
                $notesParts[] = "Chief Complaint: " . $chiefComplaint;
            }
            
            $hxPresentIllness = is_array($consultationData) ? ($consultationData['hx_present_illness'] ?? '') : ($consultationData['hx_present_illness'] ?? '');
            if (!empty($hxPresentIllness)) {
                $notesParts[] = "History of Present Illness: " . $hxPresentIllness;
            }
            
            $plan = is_array($consultationData) ? ($consultationData['plan'] ?? '') : ($consultationData['plan'] ?? '');
            if (!empty($plan)) {
                $notesParts[] = "Plan: " . $plan;
            }
            
            $systemicDisease = is_array($consultationData) ? ($consultationData['systemic_disease'] ?? '') : ($consultationData['systemic_disease'] ?? '');
            if (!empty($systemicDisease)) {
                $notesParts[] = "Systemic Disease: " . $systemicDisease;
            }
            
            $medication = is_array($consultationData) ? ($consultationData['medication'] ?? '') : ($consultationData['medication'] ?? '');
            if (!empty($medication)) {
                $notesParts[] = "Medication: " . $medication;
            }

            $notes = implode("\n\n", $notesParts);
            
            // Use appointment date as diagnosis date
            $diagnosisDate = is_array($appointmentData) ? ($appointmentData['date'] ?? date('Y-m-d')) : date('Y-m-d');
            
            // Determine category based on diagnosis content
            $category = 'general';
            $diagnosisLower = strtolower($diagnosis);
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
                AND (notes LIKE ? OR notes IS NULL)
                LIMIT 1
            ");
            $searchPattern = !empty($notes) ? '%' . substr($notes, 0, 50) . '%' : '%';
            $stmt->execute([
                $patientId,
                $diagnosis,
                $diagnosisDate,
                $searchPattern
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
                $diagnosis,
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
            SELECT u.name, u.profile_image, d.display_name, d.specialty
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
        
        // Get additional data for medical and glasses prescriptions
        $topMedications = [];
        $glassesLensTypeStats = [];
        
        if ($reportType === 'medical_prescriptions') {
            $topMedications = $this->getTopMedications($doctorId, $startDate, $endDate, 10);
        } elseif ($reportType === 'glasses_prescriptions') {
            $glassesLensTypeStats = $this->getGlassesLensTypeStats($doctorId, $startDate, $endDate);
        }
        
        // Get clinic settings and doctor info for PDF export
        $settings = $this->getSystemSettings();
        $doctorInfo = $this->getDoctorInfo($doctorId);
        
        $content = $this->view->render('doctor/reports', [
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType,
            'doctorId' => $doctorId,
            'clinicName' => $settings['clinic_name'] ?? 'Clinic',
            'doctorName' => $doctorInfo['name'] ?? $user['name'] ?? 'Doctor',
            'topMedications' => $topMedications,
            'glassesLensTypeStats' => $glassesLensTypeStats
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Reports - Doctor Dashboard',
            'pageTitle' => 'Medical Reports',
            'pageSubtitle' => 'View your practice reports',
            'content' => $content
        ]);
    }

    private function getDoctorInfo($doctorId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.display_name, u.name
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$doctorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'name' => $result['display_name'] ?? $result['name'] ?? 'Doctor',
                'display_name' => $result['display_name'] ?? ''
            ];
        } catch (Exception $e) {
            return ['name' => 'Doctor', 'display_name' => ''];
        }
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
                'consultation_cost' => '200',
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
            'clinic_name_arabic', 'clinic_website', 'clinic_logo', 'clinic_logo_print', 'clinic_logo_watermark',
            'new_visit_cost', 'repeated_visit_cost', 'consultation_cost',
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
                    if (in_array($key, ['new_visit_cost', 'repeated_visit_cost', 'consultation_cost']) && (!is_numeric($value) || $value < 0)) {
                        throw new Exception(ucfirst(str_replace('_', ' ', $key)) . ' must be a positive number');
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
            case 'medical_prescriptions':
                return $this->generateDoctorMedicalPrescriptionsReport($doctorId, $startDate, $endDate);
            case 'glasses_prescriptions':
                return $this->generateDoctorGlassesPrescriptionsReport($doctorId, $startDate, $endDate);
            default:
                return $this->generateDoctorAppointmentsReport($doctorId, $startDate, $endDate);
        }
    }

    private function generateDoctorAppointmentsReport($doctorId, $startDate, $endDate)
    {
        // Use the same logic as dashboard.php getDashboardCharts - exclude today
        // Removed doctor_id filter to show clinic-wide data
        $stmt = $this->pdo->prepare("
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
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateDoctorPatientsReport($doctorId, $startDate, $endDate)
    {
        // Removed doctor_id filter to show clinic-wide data
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(p.created_at) as date,
                COUNT(DISTINCT p.id) as new_patients,
                SUM(CASE WHEN p.gender = 'Male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN p.gender = 'Female' THEN 1 ELSE 0 END) as female
            FROM patients p
            JOIN appointments a ON p.id = a.patient_id
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE(p.created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateDoctorRevenueReport($doctorId, $startDate, $endDate)
    {
        // Removed doctor_id filter to show clinic-wide data
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(p.created_at) as date,
                SUM(p.amount) as daily_revenue,
                COUNT(*) as transactions,
                SUM(p.discount_amount) as discounts
            FROM payments p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE(p.created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateDoctorMedicalPrescriptionsReport($doctorId, $startDate, $endDate)
    {
        // Removed doctor_id filter to show clinic-wide data
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(a.date) as date,
                COUNT(DISTINCT p.id) as total_prescriptions,
                COUNT(DISTINCT a.id) as appointments_with_prescriptions,
                COUNT(DISTINCT a.patient_id) as patients_count,
                GROUP_CONCAT(DISTINCT p.drug_name SEPARATOR ', ') as drugs_list
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE DATE(a.date) BETWEEN ? AND ?
            GROUP BY DATE(a.date)
            ORDER BY date ASC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateDoctorGlassesPrescriptionsReport($doctorId, $startDate, $endDate)
    {
        // Removed doctor_id filter to show clinic-wide data
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(a.date) as date,
                COUNT(DISTINCT gp.id) as total_prescriptions,
                COUNT(DISTINCT a.id) as appointments_with_prescriptions,
                COUNT(DISTINCT a.patient_id) as patients_count,
                COUNT(DISTINCT CASE WHEN gp.lens_type IS NOT NULL AND gp.lens_type != '' THEN gp.id END) as with_lens_type
            FROM glasses_prescriptions gp
            JOIN appointments a ON gp.appointment_id = a.id
            WHERE DATE(a.date) BETWEEN ? AND ?
            GROUP BY DATE(a.date)
            ORDER BY date ASC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function getTopMedications($doctorId, $startDate, $endDate, $limit = 10)
    {
        // Removed doctor_id filter to show clinic-wide data
        $stmt = $this->pdo->prepare("
            SELECT 
                p.drug_name,
                COUNT(*) as usage_count,
                COUNT(DISTINCT p.appointment_id) as prescription_count,
                COUNT(DISTINCT a.patient_id) as patient_count
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE DATE(a.date) BETWEEN ? AND ?
            AND p.drug_name IS NOT NULL 
            AND p.drug_name != ''
            GROUP BY p.drug_name
            ORDER BY usage_count DESC
            LIMIT ?
        ");
        $stmt->execute([$startDate, $endDate, $limit]);
        return $stmt->fetchAll();
    }

    private function getGlassesLensTypeStats($doctorId, $startDate, $endDate)
    {
        // Removed doctor_id filter to show clinic-wide data
        $stmt = $this->pdo->prepare("
            SELECT 
                CASE 
                    WHEN gp.lens_type IS NULL OR gp.lens_type = '' THEN 'Not Specified'
                    ELSE gp.lens_type
                END as lens_type,
                COUNT(*) as count,
                COUNT(DISTINCT gp.appointment_id) as prescription_count,
                COUNT(DISTINCT a.patient_id) as patient_count
            FROM glasses_prescriptions gp
            JOIN appointments a ON gp.appointment_id = a.id
            WHERE DATE(a.date) BETWEEN ? AND ?
            GROUP BY lens_type
            ORDER BY count DESC
        ");
        $stmt->execute([$startDate, $endDate]);
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
            throw $e;
        }
    }

    /**
     * Financial Management Page
     */
    public function payments()
    {
        $user = $this->auth->user();
        
        // Get daily balance
        $dailyBalance = $this->getDailyBalance();
        
        // Get payment types summary
        $paymentTypes = $this->getPaymentTypesSummary();
        
        // Get today's payments
        $payments = $this->getTodayPayments();
        
        // Get today's expenses
        $expenses = $this->getTodayExpenses();
        
        $content = $this->view->render('doctor/payments', [
            'dailyBalance' => $dailyBalance,
            'paymentTypes' => $paymentTypes,
            'payments' => $payments,
            'expenses' => $expenses,
            'userRole' => $user['role'],
            'viewHelper' => $this->view
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Roaya Clinic - Financial Management',
            'pageTitle' => 'Financial Management',
            'pageSubtitle' => 'Manage payments, expenses, and daily operations',
            'content' => $content,
            'viewHelper' => $this->view
        ]);
    }

    /**
     * Daily Closure Page
     */
    public function dailyClosure()
    {
        $user = $this->auth->user();
        
        // Get today's date
        $today = date('Y-m-d');
        
        // Check if today is already closed
        $isClosed = $this->isDateClosed($today);
        
        // Get daily summary
        $dailySummary = $this->getDailySummary($today);
        
        $content = $this->view->render('doctor/daily_closure', [
            'today' => $today,
            'isClosed' => $isClosed,
            'dailySummary' => $dailySummary,
            'viewHelper' => $this->view
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Roaya Clinic - Daily Closure',
            'pageTitle' => 'Daily Closure',
            'pageSubtitle' => 'Review and close daily operations',
            'content' => $content,
            'viewHelper' => $this->view
        ]);
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
                'additional_balance' => $additionalBalance,
                'total_received' => $totalReceived,
                'total_expenses' => $totalExpenses,
                'total_withdrawals' => $totalWithdrawals,
                'current_balance' => $currentBalance,
                'transactions_count' => $transactionsCount,
                'withdrawals_count' => count($this->getTodayWithdrawals())
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

    private function getTodayPayments()
    {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.*,
                    CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                    pat.phone,
                    u.name as received_by_name
                FROM payments p
                LEFT JOIN patients pat ON p.patient_id = pat.id
                LEFT JOIN users u ON p.received_by = u.id
                WHERE DATE(p.created_at) = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$today]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    private function getTodayExpenses()
    {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    e.*,
                    u.name as created_by_name
                FROM expenses e
                LEFT JOIN users u ON e.created_by = u.id
                WHERE DATE(e.created_at) = ?
                ORDER BY e.created_at DESC
            ");
            $stmt->execute([$today]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    private function getTodayWithdrawals()
    {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    db.*,
                    u.name as created_by_name
                FROM daily_balances db
                LEFT JOIN users u ON db.created_by = u.id
                WHERE DATE(db.created_at) = ? AND db.balance_type = 'withdrawal'
                ORDER BY db.created_at DESC
            ");
            $stmt->execute([$today]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    private function isDateClosed($date)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM daily_closures WHERE date = ?
            ");
            $stmt->execute([$date]);
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }

    private function getDailySummary($date)
    {
        try {
            // Get opening balance
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as opening_balance
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'opening'
            ");
            $stmt->execute([$date]);
            $openingBalance = $stmt->fetchColumn();
            
            // Get additional balance
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as additional_balance
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'additional'
            ");
            $stmt->execute([$date]);
            $additionalBalance = $stmt->fetchColumn();
            
            // Get total withdrawals
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_withdrawals
                FROM daily_balances 
                WHERE DATE(created_at) = ? AND balance_type = 'withdrawal'
            ");
            $stmt->execute([$date]);
            $totalWithdrawals = $stmt->fetchColumn();
            
            // Get all payments
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.*,
                    CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                    u.name as received_by_name
                FROM payments p
                LEFT JOIN patients pat ON p.patient_id = pat.id
                LEFT JOIN users u ON p.received_by = u.id
                WHERE DATE(p.created_at) = ?
                ORDER BY p.created_at ASC
            ");
            $stmt->execute([$date]);
            $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get all expenses
            $stmt = $this->pdo->prepare("
                SELECT 
                    e.*,
                    u.name as created_by_name
                FROM expenses e
                LEFT JOIN users u ON e.created_by = u.id
                WHERE DATE(e.created_at) = ?
                ORDER BY e.created_at ASC
            ");
            $stmt->execute([$date]);
            $expenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calculate totals
            $totalPayments = array_sum(array_column($payments, 'amount'));
            $totalExpenses = array_sum(array_column($expenses, 'amount'));
            $netAmount = $openingBalance + $additionalBalance + $totalPayments - $totalWithdrawals - $totalExpenses;
            
            // Get all withdrawals
            $stmt = $this->pdo->prepare("
                SELECT 
                    db.*,
                    u.name as created_by_name
                FROM daily_balances db
                LEFT JOIN users u ON db.created_by = u.id
                WHERE DATE(db.created_at) = ? AND db.balance_type = 'withdrawal'
                ORDER BY db.created_at ASC
            ");
            $stmt->execute([$date]);
            $withdrawals = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'opening_balance' => $openingBalance,
                'additional_balance' => $additionalBalance,
                'payments' => $payments,
                'expenses' => $expenses,
                'withdrawals' => $withdrawals,
                'total_payments' => $totalPayments,
                'total_expenses' => $totalExpenses,
                'total_withdrawals' => $totalWithdrawals,
                'net_amount' => $netAmount
            ];
            
        } catch (Exception $e) {
            return [
                'opening_balance' => 0,
                'payments' => [],
                'expenses' => [],
                'total_payments' => 0,
                'total_expenses' => 0,
                'net_amount' => 0
            ];
        }
    }

    /**
     * Notes Management - Personal notes for doctors
     */
    public function notes()
    {
        $user = $this->auth->user();
        
        $content = $this->view->render('doctor/notes/index', [
            'user' => $user
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Notes - Roaya Clinic',
            'pageTitle' => 'Notes',
            'pageSubtitle' => 'Manage your personal notes',
            'content' => $content
        ]);
    }

    /**
     * Get all notes for current doctor (API endpoint)
     */
    public function getNotes()
    {
        // Start output buffering IMMEDIATELY - before anything else
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        // Log to error log file
        $debugLog = function($message) {
        };
        
        $debugLog("=== getNotes() START ===");
        $debugLog("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
        $debugLog("REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));
        $debugLog("HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'N/A'));
        $debugLog("HTTP_ACCEPT: " . ($_SERVER['HTTP_ACCEPT'] ?? 'N/A'));
        
        try {
            // Set headers first
            header('Content-Type: application/json; charset=utf-8');
            
            $debugLog("Headers set: Content-Type: application/json");
            
            // Check authentication for API (this may exit if auth fails)
            $debugLog("Checking API authentication...");
            if (!$this->auth->check()) {
                ob_clean();
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            $user = $this->auth->user();
            if (!in_array($user['role'], ['doctor', 'admin'])) {
                ob_clean();
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Access denied'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            $debugLog("Auth check passed. User ID: " . ($user['id'] ?? 'N/A'));
            
            // Check for any output before this point
            $outputBefore = ob_get_contents();
            if (!empty($outputBefore)) {
                $debugLog("WARNING: Output detected before query: " . substr($outputBefore, 0, 200));
                ob_clean();
            }
            
            $debugLog("Preparing SQL query...");
            $stmt = $this->pdo->prepare("
                SELECT * FROM notes 
                WHERE user_id = ? 
                ORDER BY z_index DESC, created_at DESC
            ");
            
            $debugLog("Executing query with user_id: " . $user['id']);
            $stmt->execute([$user['id']]);
            
            $debugLog("Fetching results...");
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $debugLog("Found " . count($notes) . " notes");
            
            // Check for any output before JSON
            $outputBeforeJson = ob_get_contents();
            if (!empty($outputBeforeJson)) {
                $debugLog("ERROR: Output detected before JSON encoding: " . substr($outputBeforeJson, 0, 500));
                ob_clean();
            }
            
            $response = [
                'success' => true,
                'notes' => $notes ?: []
            ];
            
            $debugLog("Encoding JSON response...");
            $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($jsonResponse === false) {
                $jsonError = json_last_error_msg();
                $debugLog("ERROR: JSON encoding failed: $jsonError");
                throw new \Exception("JSON encoding failed: $jsonError");
            }
            
            $debugLog("JSON response length: " . strlen($jsonResponse));
            
            // Clear any output before sending JSON
            ob_clean();
            
            echo $jsonResponse;
            
            $debugLog("Response sent successfully");
            $debugLog("=== getNotes() END ===");
            
            // End output buffering
            ob_end_flush();
            exit;
            
        } catch (\Exception $e) {
            // Clear any output
            ob_clean();
            
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();
            
            $debugLog("EXCEPTION in getNotes: $errorMessage");
            $debugLog("Stack trace: $errorTrace");
            
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            
            $errorResponse = [
                'success' => false,
                'message' => 'An error occurred while loading notes',
                'error' => $errorMessage
            ];
            
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            $debugLog("Error response sent");
            $debugLog("=== getNotes() END (ERROR) ===");
            
            ob_end_flush();
            exit;
        }
    }

    /**
     * Get single note by ID (API endpoint)
     */
    public function getNote($id)
    {
        // Start output buffering IMMEDIATELY
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        header('Content-Type: application/json; charset=utf-8');
        
        // Check authentication for API
        if (!$this->auth->check()) {
            ob_clean();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            ob_clean();
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        try {
            if (!$id) {
                ob_clean();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Note ID is required'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT * FROM notes 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            ob_clean();
            if ($note) {
                echo json_encode([
                    'success' => true,
                    'note' => $note
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Note not found'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
    }

    /**
     * Create new note (API endpoint)
     */
    public function createNote()
    {
        // Start output buffering IMMEDIATELY
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        header('Content-Type: application/json; charset=utf-8');
        
        // Check authentication for API
        if (!$this->auth->check()) {
            ob_clean();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            ob_clean();
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }
        
        // Validate required fields
        if (!isset($data['content'])) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Content is required'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        try {
            $title = $data['title'] ?? null;
            $content = $data['content'] ?? '';
            $backgroundColor = $data['background_color'] ?? '#fbbf24'; // Default: warning yellow
            $positionX = intval($data['position_x'] ?? 0);
            $positionY = intval($data['position_y'] ?? 0);
            $width = intval($data['width'] ?? 300);
            $height = intval($data['height'] ?? 200);
            $zIndex = intval($data['z_index'] ?? 1);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO notes (user_id, title, content, background_color, position_x, position_y, width, height, z_index)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user['id'],
                $title,
                $content,
                $backgroundColor,
                $positionX,
                $positionY,
                $width,
                $height,
                $zIndex
            ]);
            
            $noteId = $this->pdo->lastInsertId();
            
            ob_clean();
                echo json_encode([
                    'success' => true, 
                'message' => 'Note created successfully',
                'note_id' => $noteId
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while creating the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
    }
    
    /**
     * Update note (API endpoint)
     */
    public function updateNote($id)
    {
        // Start output buffering IMMEDIATELY
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        header('Content-Type: application/json; charset=utf-8');
        
        // Check authentication for API
        if (!$this->auth->check()) {
            ob_clean();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            ob_clean();
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        $noteId = $id ?? null;
        
        if (!$noteId) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Note ID is required'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        try {
            // Verify note belongs to user
            $stmt = $this->pdo->prepare("SELECT user_id FROM notes WHERE id = ?");
            $stmt->execute([$noteId]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$note) {
                ob_clean();
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Note not found'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            if ($note['user_id'] != $user['id']) {
                ob_clean();
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            $updates = [];
            $params = [];
            
            if (isset($data['title'])) {
                $updates[] = "title = ?";
                $params[] = $data['title'];
            }
            if (isset($data['content'])) {
                $updates[] = "content = ?";
                $params[] = $data['content'];
            }
            if (isset($data['background_color'])) {
                $updates[] = "background_color = ?";
                $params[] = $data['background_color'];
            }
            if (isset($data['position_x'])) {
                $updates[] = "position_x = ?";
                $params[] = intval($data['position_x']);
            }
            if (isset($data['position_y'])) {
                $updates[] = "position_y = ?";
                $params[] = intval($data['position_y']);
            }
            if (isset($data['width'])) {
                $updates[] = "width = ?";
                $params[] = intval($data['width']);
            }
            if (isset($data['height'])) {
                $updates[] = "height = ?";
                $params[] = intval($data['height']);
            }
            if (isset($data['z_index'])) {
                $updates[] = "z_index = ?";
                $params[] = intval($data['z_index']);
            }
            
            if (empty($updates)) {
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'message' => 'No changes to update'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            $params[] = $noteId;
            
            $sql = "UPDATE notes SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?";
            $params[] = $user['id'];
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Note updated successfully'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            ob_clean();
            http_response_code(500);
                echo json_encode([
                    'success' => false, 
                'message' => 'An error occurred while updating the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
    }

    /**
     * Delete note (API endpoint)
     */
    public function deleteNote($id)
    {
        // Start output buffering IMMEDIATELY
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        header('Content-Type: application/json; charset=utf-8');
        
        // Check authentication for API
        if (!$this->auth->check()) {
            ob_clean();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            ob_clean();
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $noteId = $id ?? null;
        
        if (!$noteId) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Note ID is required'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        try {
            // Verify note belongs to user
            $stmt = $this->pdo->prepare("SELECT user_id FROM notes WHERE id = ?");
            $stmt->execute([$noteId]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$note) {
                ob_clean();
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Note not found'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            if ($note['user_id'] != $user['id']) {
                ob_clean();
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ob_end_flush();
                exit;
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$noteId, $user['id']]);
            
            ob_clean();
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Note deleted successfully'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete note'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting the note'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
    }
    
    /**
     * Get doctor settings (API endpoint)
     */
    public function getDoctorSettings()
    {
        // Start output buffering IMMEDIATELY
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        header('Content-Type: application/json; charset=utf-8');
        
        // Check authentication for API
        if (!$this->auth->check()) {
            ob_clean();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            ob_clean();
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_key, setting_value, setting_type 
                FROM doctor_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$user['id']]);
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
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'settings' => $result
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while loading settings'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
    }

    /**
     * Update doctor settings (API endpoint)
     */
    public function updateDoctorSettings()
    {
        // Start output buffering IMMEDIATELY
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        
        header('Content-Type: application/json; charset=utf-8');
        
        // Check authentication for API
        if (!$this->auth->check()) {
            ob_clean();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        $user = $this->auth->user();
        if (!in_array($user['role'], ['doctor', 'admin'])) {
            ob_clean();
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        // Get JSON input
        $rawInput = file_get_contents('php://input');
        
        $input = json_decode($rawInput, true);
        
        if (!$input || !is_array($input)) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid input data',
                'debug' => ['raw_input' => $rawInput, 'decoded' => $input]
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
        
        
        // Allowed settings keys
        $allowedSettings = [
            'notes_dashboard_height',
            'notes_dashboard_width',
            'dashboard_cards_order',
            'dock_minimized',
            'dock_autohide',
            'theme',
            'push_notifications_enabled',
            'push_subscription',
            'dont_ask_push_notifications_browsers',
            'push_notification_remind_later',
            'dont_create_alert_for_appointments',
            'dont_create_notification_for_appointments',
            'back_to_top_display',
            'desktop_dock_enabled',
            'mobile_dock_enabled',
            'dashboard_rearrange_mobile',
            'sidebar_items_enabled'
        ];
        
        try {
            $this->pdo->beginTransaction();
            
            $savedCount = 0;
            foreach ($input as $key => $value) {
                if (!in_array($key, $allowedSettings)) {
                    continue;
                }
                
                // Determine setting type
                $settingType = 'string';
                if ($key === 'push_notification_remind_later') {
                    // Always treat as integer (timestamp)
                    $settingType = 'integer';
                    $value = is_numeric($value) ? (int) $value : (int) time();
                } elseif (is_int($value) || (is_string($value) && is_numeric($value) && strpos($value, '.') === false)) {
                    $settingType = 'integer';
                    $value = (int) $value;
                } elseif (is_bool($value)) {
                    $settingType = 'boolean';
                } elseif (is_array($value) || $key === 'push_subscription' || $key === 'dont_ask_push_notifications_browsers') {
                    // For push_subscription and dont_ask_push_notifications_browsers, always encode as JSON
                    if ($key === 'push_subscription' || $key === 'dont_ask_push_notifications_browsers') {
                        // If it's already a JSON string, validate it first
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                // It's valid JSON, re-encode to ensure proper format
                                $value = json_encode($decoded);
                            } else {
                                // Invalid JSON, treat as new array
                                $value = json_encode([$value]);
                            }
                        } else {
                            // It's an array or object, encode it
                            $value = json_encode($value);
                        }
                        $settingType = 'json';
                    } else {
                        // Other arrays
                        $value = json_encode($value);
                        $settingType = 'json';
                    }
                } elseif ($key === 'dashboard_cards_order' && is_string($value)) {
                    // dashboard_cards_order is already a JSON string, keep it as string
                    $settingType = 'string';
                }
                
                // Convert boolean to string for database storage
                if ($settingType === 'boolean') {
                    $dbValue = $value ? '1' : '0';
                } elseif ($settingType === 'integer') {
                    // For integer type (like push_notification_remind_later), store as string representation
                    $dbValue = (string) $value;
                } else {
                    // For dashboard_cards_order, it's already a JSON string, keep it as is
                    if ($key === 'dashboard_cards_order' && is_string($value)) {
                        $dbValue = $value; // Keep the JSON string as is
                    } elseif ($key === 'push_subscription' || $key === 'dont_ask_push_notifications_browsers' || $key === 'sidebar_items_enabled') {
                        // push_subscription, dont_ask_push_notifications_browsers, and sidebar_items_enabled are already JSON strings
                        $dbValue = $value;
                    } else {
                        $dbValue = (string) $value;
                    }
                }
                
                
                // Insert or update setting
                $stmt = $this->pdo->prepare("
                    INSERT INTO doctor_settings (user_id, setting_key, setting_value, setting_type) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    setting_type = VALUES(setting_type),
                    updated_at = CURRENT_TIMESTAMP
                ");
                $result = $stmt->execute([$user['id'], $key, $dbValue, $settingType]);
                
                if ($result) {
                    $savedCount++;
                } else {
                }
            }
            
            $this->pdo->commit();
            
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Settings updated successfully',
                'saved_count' => $savedCount
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while updating settings',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            ob_end_flush();
            exit;
        }
    }
}
