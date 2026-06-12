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
        
        // Get today's statistics (for all doctors)
        $today = date('Y-m-d');
        $stats = $this->getTodayStats($today);
        
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
            'title' => 'HClinic / Roaya | Dashboard',
            'pageTitle' => 'Dashboard',
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
            'title' => 'HClinic / Roaya | Calendar',
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
            'title' => 'HClinic / Roaya | Organizer',
            'pageTitle' => 'Organizer',
            'pageSubtitle' => 'Monthly calendar with appointments, notes, and alerts',
            'content' => $content
        ]);
    }
    
    public function patients()
    {
        $user = $this->auth->user();
        // v12_perf: the full patient set is no longer inlined (it was a 1.6 MB
        // JSON blob). The list is now fetched page-by-page from
        // /api/patients/paginated. We only need the cheap aggregate stats for
        // the above-the-fold stat cards + the (small) doctors list for filters.
        $patientStats = $this->getPatientStats();
        $doctors = $this->getAllDoctors();

        $content = $this->view->render('doctor/patients', [
            'patients' => [],            // client loads the first page via the API
            'patientStats' => $patientStats,
            'doctors' => $doctors
        ]);

        echo $this->view->render('layouts/main', [
            'title' => 'HClinic / Roaya | Patients',
            'pageTitle' => 'Patients',
            'pageSubtitle' => 'Manage patient records',
            'content' => $content
        ]);
    }

    /**
     * v12_perf: cheap aggregate stats for the patients-page stat cards.
     * Replaces the per-row PHP array_filter() loops over the full patient set
     * (which only existed because the whole set used to be loaded for the inline
     * PATIENTS_CONFIG blob). Date thresholds are computed in PHP (not MySQL
     * CURDATE) so the values exactly match the previous behaviour.
     */
    private function getPatientStats(): array
    {
        $d7  = date('Y-m-d', strtotime('-7 days'));
        $d30 = date('Y-m-d', strtotime('-30 days'));
        $d90 = date('Y-m-d', strtotime('-90 days'));

        $pStmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS total_patients,
                SUM(CASE WHEN LOWER(gender) = 'male'   THEN 1 ELSE 0 END) AS male_count,
                SUM(CASE WHEN LOWER(gender) = 'female' THEN 1 ELSE 0 END) AS female_count,
                SUM(CASE WHEN DATE(created_at) >= ? THEN 1 ELSE 0 END) AS new_this_week,
                SUM(CASE WHEN DATE(created_at) >= ? THEN 1 ELSE 0 END) AS new_this_month
            FROM patients
        ");
        $pStmt->execute([$d7, $d30]);
        $p = $pStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        $vStmt = $this->pdo->prepare("
            SELECT
                COALESCE(SUM(cnt), 0)                                         AS total_visits,
                COALESCE(SUM(CASE WHEN last_visit >= ? THEN 1 ELSE 0 END), 0) AS recent_visits,
                COALESCE(SUM(CASE WHEN last_visit >= ? THEN 1 ELSE 0 END), 0) AS active_patients
            FROM (
                SELECT patient_id, COUNT(*) AS cnt, MAX(date) AS last_visit
                FROM appointments
                WHERE patient_id IS NOT NULL
                GROUP BY patient_id
            ) lv
        ");
        $vStmt->execute([$d7, $d90]);
        $v = $vStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        return [
            'total_patients'  => (int)($p['total_patients']  ?? 0),
            'male_count'      => (int)($p['male_count']      ?? 0),
            'female_count'    => (int)($p['female_count']    ?? 0),
            'new_this_week'   => (int)($p['new_this_week']   ?? 0),
            'new_this_month'  => (int)($p['new_this_month']  ?? 0),
            'total_visits'    => (int)($v['total_visits']    ?? 0),
            'recent_visits'   => (int)($v['recent_visits']   ?? 0),
            'active_patients' => (int)($v['active_patients'] ?? 0),
        ];
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
            'title' => 'HClinic / Roaya | Patient Profile',
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

        $medicalInstructions = $this->getAppointmentMedicalInstructions($id);
        $latestNote = !empty($consultationNotes) ? $consultationNotes[0] : null;
        $latestDiagnosis = $latestNote['diagnosis'] ?? '';
        $latestDiagnosisCode = trim((string) ($latestNote['diagnosis_code'] ?? ''));
        
        $content = $this->view->render('doctor/appointment', [
            'appointment' => $appointment,
            'patient' => $patient,
            'consultationNotes' => $consultationNotes,
            'medications' => $medications,
            'medicalInstructions' => $medicalInstructions,
            'latestDiagnosis' => $latestDiagnosis,
            'latestDiagnosisCode' => $latestDiagnosisCode,
            'glasses' => $glasses,
            'labTests' => $labTests,
            'attachments' => $attachments,
            'doctorId' => $user['id'],
            'followupAppointment' => $followupAppointment,
            'originalAppointment' => $originalAppointment,
            'medicalHistory' => $medicalHistory,
            'autocompletePrefs' => $this->getDoctorAutocompletePrefs($user['id'])
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'HClinic / Roaya | Appointment Details',
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
            SELECT u.*, d.display_name as doctor_name, d.display_name_ar as doctor_name_ar, d.specialty 
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
            'title' => 'HClinic / Roaya | Profile',
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
            'doctorId' => $doctorId,
            'autocompletePrefs' => $this->getDoctorAutocompletePrefs($user['id'])
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'HClinic / Roaya | Edit Consultation',
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
            'doctorId' => $doctorId,
            'autocompletePrefs' => $this->getDoctorAutocompletePrefs($user['id'])
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'HClinic / Roaya | New Consultation',
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
        $doctorNameAr = trim($_POST['doctor_name_ar'] ?? '');
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
            if (!empty($doctorName) || !empty($specialty) || isset($_POST['doctor_name_ar'])) {
                $doctorId = $this->getDoctorId($user['id']);
                if ($doctorId) {
                    $stmt = $this->pdo->prepare("
                        UPDATE doctors 
                        SET display_name = ?, display_name_ar = ?, specialty = ?, updated_at = NOW() 
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$doctorName ?: $name, $doctorNameAr ?: null, $specialty, $user['id']]);
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

    private function getTodayStats($date)
    {
        // Get today's stats (for all doctors - no doctor_id restriction)
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Booked' THEN 1 ELSE 0 END) as booked,
                SUM(CASE WHEN status = 'CheckedIn' THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN status NOT IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM appointments 
            WHERE date = ?
        ");
        $stmt->execute([$date]);
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
        
        // Get completed appointments count for last 30 days (excluding today, for all doctors)
        $endDate = date('Y-m-d', strtotime('-1 day')); // Yesterday
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $completed30DaysStmt = $this->pdo->prepare("
            SELECT COUNT(*) as completed_30_days
            FROM appointments 
            WHERE date BETWEEN ? AND ?
            AND date < CURDATE()
            AND status = 'Completed'
        ");
        $completed30DaysStmt->execute([$startDate, $endDate]);
        $completed30DaysCount = $completed30DaysStmt->fetchColumn();
        $stats['completed'] = (int)$completed30DaysCount; // Override with last 30 days value
        
        // Get missed appointments count for last 30 days (excluding today, for all doctors)
        $missedStmt = $this->pdo->prepare("
            SELECT COUNT(*) as missed_appointments
            FROM appointments 
            WHERE date BETWEEN ? AND ?
            AND date < CURDATE()
            AND status != 'Completed'
            AND status != 'Cancelled'
        ");
        $missedStmt->execute([$startDate, $endDate]);
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
                   c.name_en as clinic_name_en, c.name_ar as clinic_name_ar, c.code as clinic_code,
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
                    LIMIT 1) as created_by_doctor_name,
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
                    LIMIT 1) as latest_attachment_id
            FROM patients p
            LEFT JOIN medical_history mh ON p.id = mh.patient_id
            LEFT JOIN clinics c ON p.clinic_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $patient = $stmt->fetch();

        // Normalize latest_attachment_id
        if ($patient) {
            if (!isset($patient['latest_attachment_id'])) {
                $patient['latest_attachment_id'] = null;
            }
            if ($patient['latest_attachment_id'] === '') {
                $patient['latest_attachment_id'] = null;
            }
        }
        
        return $patient;
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
                'notes' => $entry['notes'] ?? null, // Add notes field directly
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
                   d.display_name as doctor_display_name,
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

    private function getAppointmentMedicalInstructions($appointmentId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM appointment_medical_instructions
                WHERE appointment_id = ?
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute([$appointmentId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
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
                    LIMIT 1) as created_by_doctor_name,
                   /* Clinic of the patient's most recent appointment. NULL when
                      the patient has no appointments yet or older rows that
                      were created before clinic_id was added. */
                   last_clinic.id   as last_clinic_id,
                   last_clinic.code as last_clinic_code,
                   last_clinic.name_ar as last_clinic_name_ar,
                   last_clinic.name_en as last_clinic_name_en
            FROM patients p
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
            GROUP BY p.id
            ORDER BY p.created_at DESC
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
        
        return $patients;
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
            // Don't fail the consultation creation
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
            'title' => 'HClinic / Roaya | Edit Patient',
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
            
            $patientFields = \App\Lib\DigitNormalizer::normalizePatientNumericFields($_POST);
            $result = $stmt->execute([
                $patientFields['first_name'],
                $patientFields['last_name'],
                $patientFields['phone'],
                $patientFields['alt_phone'] ?? null,
                $patientFields['address'] ?? null,
                $patientFields['national_id'] ?? null,
                $patientFields['dob'] ?? null,
                $patientFields['emergency_contact'] ?? null,
                $patientFields['emergency_phone'] ?? null,
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
        $drugCompanyStats = [];
        $drugTrend = [];
        $drugRegimenBreakdown = [];
        $drugFilterOptions = ['companies' => [], 'categories' => [], 'routes' => []];
        $drugFilters = $this->getDrugReportFilters();
        
        if ($reportType === 'medical_prescriptions') {
            $topMedications = $this->getTopMedications($doctorId, $startDate, $endDate, 10);
        } elseif ($reportType === 'glasses_prescriptions') {
            $glassesLensTypeStats = $this->getGlassesLensTypeStats($doctorId, $startDate, $endDate);
        } elseif ($reportType === 'drugs') {
            $drugReport = $this->buildFullDrugReport($doctorId, $startDate, $endDate, $drugFilters);
            $reportData = $drugReport['reportData'];
            $drugCompanyStats = $drugReport['drugCompanyStats'];
            $drugTrend = $drugReport['drugTrend'];
            $drugRegimenBreakdown = $drugReport['drugRegimenBreakdown'];
            $drugFilterOptions = $drugReport['filterOptions'];
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
            'glassesLensTypeStats' => $glassesLensTypeStats,
            'drugCompanyStats' => $drugCompanyStats,
            'drugTrend' => $drugTrend,
            'drugRegimenBreakdown' => $drugRegimenBreakdown,
            'drugFilterOptions' => $drugFilterOptions,
            'drugFilters' => $drugFilters
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'HClinic / Roaya | Reports',
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
            'title' => 'HClinic / Roaya | Drug Search',
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
                'title' => 'HClinic / Roaya | Settings',
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
                'maintenance_mode' => false,
                'whatsapp_enabled' => false,
                'whatsapp_advanced_features' => false
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
                'maintenance_mode' => false,
                'whatsapp_enabled' => false,
                'whatsapp_advanced_features' => false
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
            'backup_frequency', 'email_notifications', 'sms_notifications', 'maintenance_mode',
            'whatsapp_enabled', 'whatsapp_advanced_features',
            'whatsapp_mod_appointments', 'whatsapp_mod_visits', 'whatsapp_mod_report', 'whatsapp_mod_patientlog'
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
                    if (in_array($key, ['email_notifications', 'sms_notifications', 'maintenance_mode', 'whatsapp_enabled', 'whatsapp_advanced_features', 'whatsapp_mod_appointments', 'whatsapp_mod_visits', 'whatsapp_mod_report', 'whatsapp_mod_patientlog'])) {
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
            
            // Generate report data (drugs report applies filters + enrichment)
            if ($reportType === 'drugs') {
                $drugFilters = $this->getDrugReportFilters();
                $reportData = $this->buildFullDrugReport($doctorId, $startDate, $endDate, $drugFilters)['reportData'];
            } else {
                $reportData = $this->generateDoctorReport($doctorId, $reportType, $startDate, $endDate);
            }
            
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
            case 'drugs':
                $df = $this->getDrugReportFilters();
                return $this->generateDoctorDrugsReport(
                    $doctorId,
                    $startDate,
                    $endDate,
                    $df['continuation_window'],
                    $df['route']
                );
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

    /**
     * Drug Reports — per-drug prescription analytics.
     *
     * Returns one row per drug with:
     *   - total_count        : how many times the drug was written (prescription events)
     *   - patient_count      : distinct patients who received it
     *   - new_count          : "new starts" (first time for that patient, or no recent prior)
     *   - continuation_count : "continuations / refills" (same patient + drug within window)
     *   - continuation_rate  : continuation_count / total_count (%)
     *
     * The new-vs-continuation split answers the real-world nuance: a doctor may
     * re-prescribe a drug at a follow-up before the patient finished the first
     * supply. Counting every write as a separate purchase over-states demand, so
     * we surface both the raw write count AND how many of those were repeats of a
     * recent prescription (within $continuationWindow days, default 90).
     */
    /** Read drug-report filter params from the query string. */
    private function getDrugReportFilters(): array
    {
        $window = (int)($_GET['continuation_window'] ?? 90);
        if (!in_array($window, [30, 60, 90, 120, 180], true)) {
            $window = 90;
        }
        return [
            'company' => trim($_GET['drug_company'] ?? ''),
            'category' => trim($_GET['drug_category'] ?? ''),
            'route' => trim($_GET['drug_route'] ?? ''),
            'continuation_window' => $window,
        ];
    }

    /**
     * Orchestrate the full drug report: aggregate → enrich → filter → trend.
     */
    private function buildFullDrugReport($doctorId, $startDate, $endDate, array $filters): array
    {
        $rows = $this->generateDoctorDrugsReport(
            $doctorId,
            $startDate,
            $endDate,
            $filters['continuation_window'],
            $filters['route']
        );
        list($rows, $companyStats) = $this->enrichDrugsWithCompany($rows);

        if ($filters['company'] !== '') {
            $rows = array_values(array_filter(
                $rows,
                fn($r) => ($r['company'] ?? 'Unmapped') === $filters['company']
            ));
            $companyStats = $this->rollupDrugCompanyStats($rows);
        }
        if ($filters['category'] !== '') {
            $rows = array_values(array_filter(
                $rows,
                fn($r) => ($r['category'] ?? '') === $filters['category']
            ));
            $companyStats = $this->rollupDrugCompanyStats($rows);
        }

        $topNames = array_slice(array_column($rows, 'drug_name'), 0, 5);
        $trend = $this->generateDrugTrendData(
            $startDate,
            $endDate,
            $topNames,
            $filters['route']
        );
        $regimenBreakdown = $this->getDrugRegimenBreakdown(
            $startDate,
            $endDate,
            $filters['route']
        );

        return [
            'reportData' => $rows,
            'drugCompanyStats' => $companyStats,
            'drugTrend' => $trend,
            'drugRegimenBreakdown' => $regimenBreakdown,
            'filterOptions' => $this->getDrugReportFilterOptions($startDate, $endDate),
            'filters' => $filters,
        ];
    }

    private function rollupDrugCompanyStats(array $drugRows): array
    {
        $totals = [];
        foreach ($drugRows as $r) {
            $company = (!empty($r['company'])) ? $r['company'] : 'Unmapped';
            if (!isset($totals[$company])) {
                $totals[$company] = ['company' => $company, 'total_count' => 0, 'drug_count' => 0];
            }
            $totals[$company]['total_count'] += (int)($r['total_count'] ?? 0);
            $totals[$company]['drug_count'] += 1;
        }
        $stats = array_values($totals);
        usort($stats, fn($a, $b) => $b['total_count'] <=> $a['total_count']);
        return $stats;
    }

    /** Distinct companies / categories (drugs DB) + routes (prescriptions) for filter dropdowns. */
    private function getDrugReportFilterOptions($startDate, $endDate): array
    {
        $options = ['companies' => [], 'categories' => [], 'routes' => []];
        try {
            $drugsPdo = $this->getDrugsDatabaseConnection();
            $stmt = $drugsPdo->query(
                "SELECT DISTINCT Company AS v FROM drugs
                 WHERE Company IS NOT NULL AND Company <> '' ORDER BY Company"
            );
            $options['companies'] = array_column($stmt->fetchAll(), 'v');
            $stmt = $drugsPdo->query(
                "SELECT DISTINCT Pharmacology AS v FROM drugs
                 WHERE Pharmacology IS NOT NULL AND Pharmacology <> '' ORDER BY Pharmacology"
            );
            $options['categories'] = array_column($stmt->fetchAll(), 'v');
        } catch (\Throwable $e) {
            // Degrade gracefully.
        }
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT p.route AS v
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE DATE(a.date) BETWEEN ? AND ?
              AND p.route IS NOT NULL AND p.route <> ''
            ORDER BY p.route
        ");
        $stmt->execute([$startDate, $endDate]);
        $options['routes'] = array_column($stmt->fetchAll(), 'v');
        return $options;
    }

    /**
     * Monthly prescription-write counts for the top-N drugs (line-chart data).
     */
    private function generateDrugTrendData($startDate, $endDate, array $drugNames, $routeFilter = ''): array
    {
        if (empty($drugNames)) {
            return ['labels' => [], 'datasets' => []];
        }
        $placeholders = implode(',', array_fill(0, count($drugNames), '?'));
        $params = array_merge([$startDate, $endDate], $drugNames);
        $routeSql = '';
        if ($routeFilter !== '') {
            $routeSql = ' AND p.route = ?';
            $params[] = $routeFilter;
        }
        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(a.date, '%Y-%m') AS month_key,
                   p.drug_name,
                   COUNT(*) AS write_count
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE DATE(a.date) BETWEEN ? AND ?
              AND p.drug_name IN ($placeholders)
              $routeSql
            GROUP BY month_key, p.drug_name
            ORDER BY month_key ASC
        ");
        $stmt->execute($params);
        $raw = $stmt->fetchAll();

        $labels = [];
        $cursor = new \DateTime($startDate);
        $cursor->modify('first day of this month');
        $end = new \DateTime($endDate);
        $end->modify('first day of this month');
        while ($cursor <= $end) {
            $labels[] = $cursor->format('Y-m');
            $cursor->modify('+1 month');
        }

        $byDrug = [];
        foreach ($drugNames as $name) {
            $byDrug[$name] = array_fill_keys($labels, 0);
        }
        foreach ($raw as $row) {
            $name = $row['drug_name'];
            if (isset($byDrug[$name][$row['month_key']])) {
                $byDrug[$name][$row['month_key']] = (int)$row['write_count'];
            }
        }

        $datasets = [];
        foreach ($drugNames as $name) {
            $datasets[] = [
                'drug_name' => $name,
                'data' => array_values($byDrug[$name]),
            ];
        }
        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /** Normalize Arabic-Indic digits and common OCR typos in free-text regimen fields. */
    private function normalizeRxText(?string $text): string
    {
        if ($text === null) {
            return '';
        }
        $t = trim($text);
        $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        foreach ($arabicDigits as $i => $digit) {
            $t = str_replace($digit, (string)$i, $t);
        }
        $t = str_replace(['×', '＊', '✕'], '*', $t);
        return $t;
    }

    /** Guess whether a free-text field is dose / frequency / duration (fields are often swapped). */
    private function classifyRxField(?string $text): string
    {
        $t = mb_strtolower($this->normalizeRxText($text));
        if ($t === '') {
            return 'empty';
        }
        if (preg_match('/(?:forever|for ever|مستمر|دائم)/u', $t)) {
            return 'duration';
        }
        if (preg_match('/(?:times?|daily|bid|tid|tds|qid|ttd|\bod\b|once|hour|ساعات|مرتين|مرات|يوميا|باليوم)/u', $t)) {
            return 'frequency';
        }
        if (preg_match('/(?:week|month|day|أيام|ايام|اسبوع|أسبوع|شهر|moth)/u', $t)) {
            return 'duration';
        }
        if (preg_match('/(?:tablet|tab|capsule|حباية|حبة|قرص|قرص)/u', $t)) {
            return 'dose';
        }
        if (preg_match('/^\d+(?:\.\d+)?(?:\s*\*\s*\d+)?$/', $t)) {
            return 'dose';
        }
        return 'unknown';
    }

    /**
     * Re-assign dose/frequency/duration when doctors typed values in the wrong column
     * (common in the live data: "3 times" in dose, "2 weeks" in frequency, etc.).
     */
    private function smartAssignRegimenFields(?string $dose, ?string $frequency, ?string $duration): array
    {
        $assigned = ['dose' => '', 'frequency' => '', 'duration' => ''];
        $pool = [
            ['role' => $this->classifyRxField($dose), 'val' => $this->normalizeRxText($dose)],
            ['role' => $this->classifyRxField($frequency), 'val' => $this->normalizeRxText($frequency)],
            ['role' => $this->classifyRxField($duration), 'val' => $this->normalizeRxText($duration)],
        ];
        foreach ($pool as $item) {
            if ($item['val'] === '') {
                continue;
            }
            $slot = $item['role'];
            if ($slot === 'empty' || $slot === 'unknown') {
                continue;
            }
            if ($assigned[$slot] === '') {
                $assigned[$slot] = $item['val'];
            }
        }
        // Whatever wasn't classified — fill remaining slots in original order.
        $original = [
            $this->normalizeRxText($dose),
            $this->normalizeRxText($frequency),
            $this->normalizeRxText($duration),
        ];
        $used = array_filter(array_values($assigned));
        foreach ($original as $val) {
            if ($val === '' || in_array($val, $used, true)) {
                continue;
            }
            foreach (['dose', 'frequency', 'duration'] as $slot) {
                if ($assigned[$slot] === '') {
                    $assigned[$slot] = $val;
                    $used[] = $val;
                    break;
                }
            }
        }
        return $assigned;
    }

    /**
     * Estimate dispensed units from dose × frequency/day × duration (days).
     * Uses smart field assignment; returns null when any component can't parse.
     */
    private function estimatePrescriptionUnits(?string $dose, ?string $frequency, ?string $duration): ?float
    {
        $assigned = $this->smartAssignRegimenFields($dose, $frequency, $duration);
        $d = $this->parseDoseUnits($assigned['dose']);
        $f = $this->parseFrequencyPerDay($assigned['frequency']);
        $days = $this->parseDurationDays($assigned['duration']);
        if ($d === null || $f === null || $days === null) {
            return null;
        }
        return round($d * $f * $days, 1);
    }

    /**
     * Resolve regimen for unit estimation: prescription fields first, then per-doctor
     * drug_defaults template filling any gaps.
     *
     * @return array{units: ?float, source: string} source = rx | template | none
     */
    private function resolveRegimenEstimate(
        ?string $rxDose,
        ?string $rxFreq,
        ?string $rxDur,
        ?array $template
    ): array {
        $hasRx = trim((string)$rxDose) !== '' || trim((string)$rxFreq) !== '' || trim((string)$rxDur) !== '';
        $units = $this->estimatePrescriptionUnits($rxDose, $rxFreq, $rxDur);
        if ($units !== null) {
            return ['units' => $units, 'source' => 'rx'];
        }
        if ($template) {
            $mergedDose = trim((string)$rxDose) !== '' ? $rxDose : ($template['dose'] ?? '');
            $mergedFreq = trim((string)$rxFreq) !== '' ? $rxFreq : ($template['frequency'] ?? '');
            $mergedDur = trim((string)$rxDur) !== '' ? $rxDur : ($template['duration'] ?? '');
            $units = $this->estimatePrescriptionUnits($mergedDose, $mergedFreq, $mergedDur);
            if ($units !== null) {
                return ['units' => $units, 'source' => 'template'];
            }
        }
        if ($hasRx) {
            return ['units' => null, 'source' => 'unparsed'];
        }
        return ['units' => null, 'source' => 'none'];
    }

    private function parseDoseUnits(?string $dose): ?float
    {
        $dose = $this->normalizeRxText($dose);
        if ($dose === '') {
            return null;
        }
        $d = mb_strtolower($dose);
        if (preg_match('/(?:حباية|حبة|tablet|tab|capsule|قرص)/u', $d)) {
            return 1.0;
        }
        // "1*3" or "1×3" in dose column often means 1 unit taken 3×/day — freq handled separately.
        if (preg_match('/^(\d+(?:\.\d+)?)\s*[\*\/]\s*(\d+)/', $d, $m)) {
            return (float)$m[1];
        }
        if (preg_match('/(\d+(?:\.\d+)?)/', $d, $m)) {
            return (float)$m[1];
        }
        return null;
    }

    private function parseFrequencyPerDay(?string $frequency): ?float
    {
        $frequency = $this->normalizeRxText($frequency);
        if ($frequency === '') {
            return null;
        }
        $f = mb_strtolower(trim($frequency));
        $map = [
            'once daily' => 1, 'once' => 1, 'od' => 1, 'qd' => 1, 'daily' => 1, 'once a day' => 1,
            'twice daily' => 2, 'bid' => 2, 'twice a day' => 2, '2x daily' => 2,
            'three times daily' => 3, 'tid' => 3, 'tds' => 3, 'ttd' => 3, '3x daily' => 3,
            'four times daily' => 4, 'qid' => 4, '4x daily' => 4,
            'five times' => 5, '5 times' => 5,
            'every other day' => 0.5, 'alternate day' => 0.5,
            'weekly' => 1 / 7, 'once weekly' => 1 / 7,
        ];
        foreach ($map as $pattern => $val) {
            if (strpos($f, $pattern) !== false) {
                return $val;
            }
        }
        if (preg_match('/كل\s*(\d+)\s*ساعات/u', $f, $m) && (int)$m[1] > 0) {
            return round(24 / (int)$m[1], 2);
        }
        if (preg_match('/every\s*(\d+)\s*h(?:ours?)?/i', $f, $m) && (int)$m[1] > 0) {
            return round(24 / (int)$m[1], 2);
        }
        if (preg_match('/(\d+)\s*(?:x|\*|times?)(?:\s*(?:daily|day|per day))?/i', $f, $m)) {
            return (float)$m[1];
        }
        if (preg_match('/^(\d+)\s*times?$/i', $f, $m)) {
            return (float)$m[1];
        }
        if (preg_match('/مرتين/u', $f)) {
            return 2.0;
        }
        if (preg_match('/ثلاث/u', $f)) {
            return 3.0;
        }
        if (preg_match('/(\d+)\s*(?:مرات?|مرة)\s*(?:يوم|اليوم|فى اليوم|باليوم)/u', $f, $m)) {
            return (float)$m[1];
        }
        if (preg_match('/مرة\s*(?:واحدة|باليوم|يوميا)/u', $f)) {
            return 1.0;
        }
        return null;
    }

    private function parseDurationDays(?string $duration): ?int
    {
        $duration = $this->normalizeRxText($duration);
        if ($duration === '') {
            return null;
        }
        $d = mb_strtolower(trim($duration));
        if (preg_match('/(?:forever|for ever|مستمر|دائم)/u', $d)) {
            return 90;
        }
        if (preg_match('/^weeks?$/i', $d)) {
            return 7;
        }
        if (preg_match('/(\d+)\s*(?:days?|d\b)/i', $d, $m)) {
            return (int)$m[1];
        }
        if (preg_match('/(\d+)\s*(?:weeks?|wk|w\b)/i', $d, $m)) {
            return (int)$m[1] * 7;
        }
        if (preg_match('/(\d+)\s*(?:months?|moths?|mo)/i', $d, $m)) {
            return (int)$m[1] * 30;
        }
        if (preg_match('/(\d+)\s*(?:أيام?|ايام?|يوم)/u', $d, $m)) {
            return (int)$m[1];
        }
        if (preg_match('/(?:لمدة\s*)?(?:اسبوعين|أسبوعين)/u', $d)) {
            return 14;
        }
        if (preg_match('/(\d+)\s*(?:أسابيع?|اسبوع|أسبوع)/u', $d, $m)) {
            return (int)$m[1] * 7;
        }
        if (preg_match('/(\d+)\s*(?:شهور?|شهر)/u', $d, $m)) {
            return (int)$m[1] * 30;
        }
        if (preg_match('/^(\d+)$/', $d, $m)) {
            $n = (int)$m[1];
            return $n <= 14 ? $n : $n;
        }
        return null;
    }

    /**
     * Load drug_defaults for template fallback.
     * Returns [perDoctorIndex, perDrugIndex] — perDrug is clinic-wide (any doctor).
     */
    private function loadDrugDefaultsIndex(): array
    {
        $perDoctor = [];
        $perDrug = [];
        try {
            $stmt = $this->pdo->query(
                "SELECT doctor_id, drug_name, dose, frequency, duration
                 FROM drug_defaults
                 WHERE drug_name IS NOT NULL AND drug_name <> ''"
            );
            foreach ($stmt->fetchAll() as $row) {
                $drugKey = mb_strtolower(trim($row['drug_name']));
                $perDoctor[(int)$row['doctor_id'] . '|' . $drugKey] = $row;
                if (!isset($perDrug[$drugKey])) {
                    $perDrug[$drugKey] = $row;
                }
            }
        } catch (\Throwable $e) {
            // Table may not exist on older installs.
        }
        return [$perDoctor, $perDrug];
    }

    /**
     * Top dose/frequency/duration combinations actually written in the period
     * (after smart field assignment), with estimated units per combo.
     */
    private function getDrugRegimenBreakdown($startDate, $endDate, $routeFilter = ''): array
    {
        $params = [$startDate, $endDate];
        $routeSql = '';
        if ($routeFilter !== '') {
            $routeSql = ' AND p.route = ?';
            $params[] = $routeFilter;
        }
        $stmt = $this->pdo->prepare("
            SELECT p.drug_name, p.dose, p.frequency, p.duration, COUNT(*) AS write_count
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE DATE(a.date) BETWEEN ? AND ?
              AND p.drug_name IS NOT NULL AND p.drug_name <> ''
              AND (
                  TRIM(p.dose) <> '' OR TRIM(p.frequency) <> '' OR TRIM(p.duration) <> ''
              )
              $routeSql
            GROUP BY p.drug_name, p.dose, p.frequency, p.duration
            ORDER BY write_count DESC
            LIMIT 40
        ");
        $stmt->execute($params);
        $rows = [];
        foreach ($stmt->fetchAll() as $r) {
            $assigned = $this->smartAssignRegimenFields($r['dose'], $r['frequency'], $r['duration']);
            $units = $this->estimatePrescriptionUnits($assigned['dose'], $assigned['frequency'], $assigned['duration']);
            $rows[] = [
                'drug_name' => $r['drug_name'],
                'dose' => $assigned['dose'] ?: ($r['dose'] ?: '—'),
                'frequency' => $assigned['frequency'] ?: ($r['frequency'] ?: '—'),
                'duration' => $assigned['duration'] ?: ($r['duration'] ?: '—'),
                'write_count' => (int)$r['write_count'],
                'estimated_units' => $units,
            ];
        }
        return $rows;
    }

    private function generateDoctorDrugsReport(
        $doctorId,
        $startDate,
        $endDate,
        $continuationWindow = 90,
        $routeFilter = ''
    ) {
        $params = [$startDate, (int)$continuationWindow, $endDate];
        $routeSql = '';
        if ($routeFilter !== '') {
            $routeSql = ' AND p.route = ?';
            $params[] = $routeFilter;
        }

        $stmt = $this->pdo->prepare("
            SELECT p.drug_name, a.patient_id, a.doctor_id, DATE(a.date) AS d,
                   p.dose, p.frequency, p.duration, p.route
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE p.drug_name IS NOT NULL AND p.drug_name <> ''
              AND DATE(a.date) BETWEEN DATE_SUB(?, INTERVAL ? DAY) AND ?
              $routeSql
            ORDER BY p.drug_name, a.patient_id, a.date ASC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        [$defaultsByDoctor, $defaultsByDrug] = $this->loadDrugDefaultsIndex();

        $drugs = [];
        $lastSeen = [];

        foreach ($rows as $r) {
            $name = trim($r['drug_name']);
            $nameKey = mb_strtolower($name);
            $pairKey = $nameKey . '|' . $r['patient_id'];
            $d = $r['d'];

            $prev = $lastSeen[$pairKey] ?? null;
            $lastSeen[$pairKey] = $d;

            if ($d < $startDate || $d > $endDate) {
                continue;
            }

            if (!isset($drugs[$nameKey])) {
                $drugs[$nameKey] = [
                    'drug_name' => $name,
                    'total_count' => 0,
                    'new_count' => 0,
                    'continuation_count' => 0,
                    'estimated_units' => 0.0,
                    'estimated_units_count' => 0,
                    'estimated_units_rx_count' => 0,
                    'estimated_units_template_count' => 0,
                    'patients' => [],
                    'routes' => [],
                ];
            }
            $drugs[$nameKey]['total_count']++;
            $drugs[$nameKey]['patients'][$r['patient_id']] = true;
            if (!empty($r['route'])) {
                $drugs[$nameKey]['routes'][$r['route']] = true;
            }

            $tplKey = (int)$r['doctor_id'] . '|' . $nameKey;
            $template = $defaultsByDoctor[$tplKey] ?? $defaultsByDrug[$nameKey] ?? null;
            $estimate = $this->resolveRegimenEstimate(
                $r['dose'],
                $r['frequency'],
                $r['duration'],
                $template
            );
            if ($estimate['units'] !== null) {
                $drugs[$nameKey]['estimated_units'] += $estimate['units'];
                $drugs[$nameKey]['estimated_units_count']++;
                if ($estimate['source'] === 'rx') {
                    $drugs[$nameKey]['estimated_units_rx_count']++;
                } elseif ($estimate['source'] === 'template') {
                    $drugs[$nameKey]['estimated_units_template_count']++;
                }
            }

            $isContinuation = $prev !== null
                && (strtotime($d) - strtotime($prev)) / 86400 <= $continuationWindow;
            if ($isContinuation) {
                $drugs[$nameKey]['continuation_count']++;
            } else {
                $drugs[$nameKey]['new_count']++;
            }
        }

        $result = [];
        foreach ($drugs as $row) {
            $row['patient_count'] = count($row['patients']);
            unset($row['patients']);
            $row['routes'] = array_keys($row['routes']);
            $row['continuation_rate'] = $row['total_count'] > 0
                ? round($row['continuation_count'] * 100 / $row['total_count'], 1)
                : 0;
            $parsed = (int)$row['estimated_units_count'];
            $row['estimated_units_parse_rate'] = $row['total_count'] > 0
                ? round($parsed * 100 / $row['total_count'], 0)
                : 0;
            $row['estimated_units'] = $parsed > 0
                ? round($row['estimated_units'], 1)
                : null;
            unset($row['estimated_units_count']);
            $result[] = $row;
        }
        usort($result, fn($a, $b) => $b['total_count'] <=> $a['total_count']);
        return $result;
    }

    /**
     * Enrich per-drug rows with trade metadata from the separate hclinic_drugs DB
     * (Company / active ingredient / category / price) and roll the counts up by
     * manufacturer. Matching is by drug name (prescriptions.drug_name ≈
     * drugs.FirstName, case-insensitive). Degrades gracefully if the drugs DB is
     * unreachable. Returns [$drugRows (enriched), $companyStats].
     */
    private function enrichDrugsWithCompany(array $drugRows)
    {
        if (empty($drugRows)) {
            return [$drugRows, []];
        }

        $meta = [];
        try {
            $drugsPdo = $this->getDrugsDatabaseConnection();
            $names = array_values(array_unique(array_map(fn($r) => $r['drug_name'], $drugRows)));
            $placeholders = implode(',', array_fill(0, count($names), '?'));
            $stmt = $drugsPdo->prepare(
                "SELECT FirstName AS drug_name, Company, LastName AS active_ingredient,
                        price, Pharmacology AS category
                 FROM drugs
                 WHERE FirstName IN ($placeholders)"
            );
            $stmt->execute($names);
            foreach ($stmt->fetchAll() as $m) {
                $meta[mb_strtolower(trim($m['drug_name']))] = $m;
            }
        } catch (\Throwable $e) {
            // No drug metadata available — return rows unenriched, no company stats.
            return [$drugRows, []];
        }

        $companyTotals = [];
        foreach ($drugRows as &$r) {
            $info = $meta[mb_strtolower(trim($r['drug_name']))] ?? null;
            $r['company'] = $info['Company'] ?? null;
            $r['active_ingredient'] = $info['active_ingredient'] ?? null;
            $r['category'] = $info['category'] ?? null;
            $r['price'] = $info['price'] ?? null;

            $company = (!empty($info['Company'])) ? $info['Company'] : 'Unmapped';
            if (!isset($companyTotals[$company])) {
                $companyTotals[$company] = ['company' => $company, 'total_count' => 0, 'drug_count' => 0];
            }
            $companyTotals[$company]['total_count'] += (int)$r['total_count'];
            $companyTotals[$company]['drug_count'] += 1;
        }
        unset($r);

        $companyStats = array_values($companyTotals);
        usort($companyStats, fn($a, $b) => $b['total_count'] <=> $a['total_count']);
        return [$drugRows, $companyStats];
    }

    /**
     * Dedicated connection to the hclinic_drugs database (drug catalogue lives in
     * its own DB, separate from the clinical data). Mirrors ApiController.
     */
    private function getDrugsDatabaseConnection()
    {
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
        // Portable docroot — the hardcoded /var/www/html/clinic/public broke on prod
        // (open_basedir) and never matched the current local docroot either.
        $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . '/uploads/logos/';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
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
                if (filter_var($path, FILTER_VALIDATE_URL) || (strpos($path, '/') === 0 && @file_exists(rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . $path))) {
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

        // Doctor can filter the whole financial page by clinic via ?clinic_id=
        // (empty / missing = "all clinics", matching the cross-clinic visibility
        // doctors retain by default).
        $clinicFilter = !empty($_GET['clinic_id']) ? (int)$_GET['clinic_id'] : null;

        // Get the active clinics list for the toolbar dropdown
        $clinics = $this->pdo->query("
            SELECT id, code, name_ar, name_en
            FROM clinics
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $dailyBalance = $this->getDailyBalance($clinicFilter);
        $paymentTypes = $this->getPaymentTypesSummary($clinicFilter);
        $payments     = $this->getTodayPayments($clinicFilter);
        $expenses     = $this->getTodayExpenses($clinicFilter);
        // 7-day trend series for the KPI stat-card sparklines.
        $financialTrend = $this->getFinancialTrend($clinicFilter);

        $content = $this->view->render('doctor/payments', [
            'dailyBalance' => $dailyBalance,
            'paymentTypes' => $paymentTypes,
            'payments' => $payments,
            'expenses' => $expenses,
            'financialTrend' => $financialTrend,
            'userRole' => $user['role'],
            'clinics' => $clinics,
            'selectedClinicId' => $clinicFilter,
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

    /**
     * 7-day financial trend series (opening / received / expenses / current)
     * used to draw the KPI stat-card sparklines on the financial page.
     */
    private function getFinancialTrend(?int $clinicId = null, int $days = 7)
    {
        try {
            $dates = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $dates[] = date('Y-m-d', strtotime("-{$i} day"));
            }
            $start = $dates[0];
            $cf = $clinicId ? ' AND clinic_id = ? ' : '';

            // Run one grouped query per series, map results by date.
            $byDate = function (string $sql) use ($clinicId, $start) {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($clinicId ? [$start, $clinicId] : [$start]);
                $map = [];
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $map[$r['d']] = (float) $r['v'];
                }
                return $map;
            };

            $opening    = $byDate("SELECT DATE(created_at) d, COALESCE(SUM(amount),0) v FROM daily_balances WHERE DATE(created_at) >= ? AND balance_type='opening' {$cf} GROUP BY DATE(created_at)");
            $additional = $byDate("SELECT DATE(created_at) d, COALESCE(SUM(amount),0) v FROM daily_balances WHERE DATE(created_at) >= ? AND balance_type='additional' {$cf} GROUP BY DATE(created_at)");
            $withdrawal = $byDate("SELECT DATE(created_at) d, COALESCE(SUM(amount),0) v FROM daily_balances WHERE DATE(created_at) >= ? AND balance_type='withdrawal' {$cf} GROUP BY DATE(created_at)");
            $received   = $byDate("SELECT DATE(created_at) d, COALESCE(SUM(CASE WHEN is_exempt=1 THEN 0 ELSE (amount - COALESCE(discount_amount,0)) END),0) v FROM payments WHERE DATE(created_at) >= ? {$cf} GROUP BY DATE(created_at)");
            $expenses   = $byDate("SELECT DATE(created_at) d, COALESCE(SUM(amount),0) v FROM expenses WHERE DATE(created_at) >= ? {$cf} GROUP BY DATE(created_at)");

            $tOpening = $tReceived = $tExpenses = $tCurrent = [];
            foreach ($dates as $d) {
                $o = $opening[$d]    ?? 0;
                $a = $additional[$d] ?? 0;
                $w = $withdrawal[$d] ?? 0;
                $r = $received[$d]   ?? 0;
                $e = $expenses[$d]   ?? 0;
                $tOpening[]  = round($o, 2);
                $tReceived[] = round($r, 2);
                $tExpenses[] = round($e, 2);
                $tCurrent[]  = round($o + $a + $r - $w - $e, 2);
            }

            return [
                'opening'  => $tOpening,
                'received' => $tReceived,
                'expenses' => $tExpenses,
                'current'  => $tCurrent,
            ];
        } catch (\Exception $e) {
            $z = array_fill(0, $days, 0);
            return ['opening' => $z, 'received' => $z, 'expenses' => $z, 'current' => $z];
        }
    }

    private function getDailyBalance(?int $clinicId = null)
    {
        try {
            $today = date('Y-m-d');
            $clinicFilter = $clinicId ? ' AND clinic_id = ? ' : '';
            $clinicParam  = $clinicId ? [$clinicId] : [];

            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as opening_balance
                FROM daily_balances
                WHERE DATE(created_at) = ? AND balance_type = 'opening' {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $clinicParam));
            $openingBalance = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as additional_balance
                FROM daily_balances
                WHERE DATE(created_at) = ? AND balance_type = 'additional' {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $clinicParam));
            $additionalBalance = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_withdrawals
                FROM daily_balances
                WHERE DATE(created_at) = ? AND balance_type = 'withdrawal' {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $clinicParam));
            $totalWithdrawals = $stmt->fetchColumn();

            // NET total received (matches Phase 0 fix across other controllers).
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(
                    CASE WHEN is_exempt = 1 THEN 0
                         ELSE (amount - COALESCE(discount_amount, 0))
                    END
                ), 0) as total_received
                FROM payments
                WHERE DATE(created_at) = ? {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $clinicParam));
            $totalReceived = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_expenses
                FROM expenses
                WHERE DATE(created_at) = ? {$clinicFilter}
            ");
            $stmt->execute(array_merge([$today], $clinicParam));
            $totalExpenses = $stmt->fetchColumn();

            $currentBalance = $openingBalance + $additionalBalance + $totalReceived - $totalWithdrawals - $totalExpenses;

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
            $params = $clinicId
                ? [$today, $clinicId, $today, $clinicId, $today, $clinicId]
                : [$today, $today, $today];
            $stmt->execute($params);
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

    private function getPaymentTypesSummary(?int $clinicId = null)
    {
        try {
            $today = date('Y-m-d');
            $clinicFilter = $clinicId ? ' AND clinic_id = ? ' : '';
            $params = $clinicId ? [$today, $clinicId] : [$today];

            $stmt = $this->pdo->prepare("
                SELECT
                    CASE
                        WHEN type = 'Booking'      THEN 'new_booking'
                        WHEN type = 'FollowUp'     THEN 'followup'
                        WHEN type = 'Consultation' THEN 'consultation'
                        WHEN type = 'Procedure'    THEN 'procedure'
                        ELSE 'other'
                    END as payment_type,
                    COUNT(*) as count,
                    COALESCE(SUM(
                        CASE WHEN is_exempt = 1 THEN 0
                             ELSE (amount - COALESCE(discount_amount, 0))
                        END
                    ), 0) as total
                FROM payments
                WHERE DATE(created_at) = ? {$clinicFilter}
                GROUP BY payment_type
            ");
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $summary = [];
            foreach ($results as $row) {
                $summary[$row['payment_type']] = [
                    'count' => $row['count'],
                    'total' => $row['total'],
                ];
            }
            return $summary;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTodayPayments(?int $clinicId = null)
    {
        try {
            $today = date('Y-m-d');
            $clinicFilter = $clinicId ? ' AND p.clinic_id = ? ' : '';
            $params = $clinicId ? [$today, $clinicId] : [$today];

            $stmt = $this->pdo->prepare("
                SELECT
                    p.*,
                    CONCAT(pat.first_name, ' ', pat.last_name) as patient_name,
                    pat.phone,
                    u.name as received_by_name,
                    c.name_ar as clinic_name_ar, c.name_en as clinic_name_en, c.code as clinic_code
                FROM payments p
                LEFT JOIN patients pat ON p.patient_id = pat.id
                LEFT JOIN users u ON p.received_by = u.id
                LEFT JOIN clinics c ON p.clinic_id = c.id
                WHERE DATE(p.created_at) = ? {$clinicFilter}
                ORDER BY p.created_at DESC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTodayExpenses(?int $clinicId = null)
    {
        try {
            $today = date('Y-m-d');
            $clinicFilter = $clinicId ? ' AND e.clinic_id = ? ' : '';
            $params = $clinicId ? [$today, $clinicId] : [$today];

            $stmt = $this->pdo->prepare("
                SELECT
                    e.*,
                    u.name as created_by_name,
                    c.name_ar as clinic_name_ar, c.name_en as clinic_name_en, c.code as clinic_code
                FROM expenses e
                LEFT JOIN users u ON e.created_by = u.id
                LEFT JOIN clinics c ON e.clinic_id = c.id
                WHERE DATE(e.created_at) = ? {$clinicFilter}
                ORDER BY e.created_at DESC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
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
     * Activities page — dedicated, filterable feed (reads /api/activity/page).
     */
    public function activities()
    {
        $user = $this->auth->user();
        $content = $this->view->render('doctor/activities', [
            'user' => $user,
            'activitiesLang' => 'en',
        ]);
        echo $this->view->render('layouts/main', [
            'title' => 'Activity Log - Roaya Clinic',
            'pageTitle' => 'Activity Log',
            'pageSubtitle' => 'All clinic activity in one place',
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
    /**
     * Load a doctor's Auto Complete preferences for server-side injection
     * into the Edit Consultation + Appointment views, so the autocomplete
     * never flashes on before an async settings fetch can disable it.
     * Each defaults to TRUE (autocomplete enabled) when no row exists.
     *
     * @param int $userId
     * @return array{consultation:bool, icd10:bool, medications:bool}
     */
    private function getDoctorAutocompletePrefs($userId)
    {
        $prefs = [
            'consultation' => true,
            'icd10'        => true,
            'medications'  => true,
        ];
        $map = [
            'autocomplete_consultation' => 'consultation',
            'autocomplete_icd10'        => 'icd10',
            'autocomplete_medications'  => 'medications',
        ];
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_key, setting_value
                FROM doctor_settings
                WHERE user_id = ? AND setting_key IN ('autocomplete_consultation','autocomplete_icd10','autocomplete_medications')
            ");
            $stmt->execute([$userId]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $k = $map[$row['setting_key']] ?? null;
                if ($k !== null) {
                    // Stored as '1'/'0' (boolean setting_type).
                    $prefs[$k] = !($row['setting_value'] === '0' || $row['setting_value'] === 0 || $row['setting_value'] === false);
                }
            }
        } catch (\Exception $e) {
            // On any error keep the safe default (all enabled).
        }
        return $prefs;
    }

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
                    case 'string':
                        // For dashboard_cards_order, parse JSON string if it's valid JSON
                        if ($key === 'dashboard_cards_order' && is_string($value)) {
                            $decoded = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $result[$key] = $decoded;
                            } else {
                                $result[$key] = $value;
                            }
                        } else {
                            $result[$key] = $value;
                        }
                        break;
                    default:
                        // For dashboard_cards_order stored as string, try to parse as JSON
                        if ($key === 'dashboard_cards_order' && is_string($value)) {
                            $decoded = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $result[$key] = $decoded;
                            } else {
                                $result[$key] = $value;
                            }
                        } else {
                            $result[$key] = $value;
                        }
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
            'sidebar_items_enabled',
            // Auto Complete preferences (Edit Consultation + medication entry).
            // Doctor-scoped switches; default ON when no row exists.
            'autocomplete_consultation',
            'autocomplete_icd10',
            'autocomplete_medications'
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
