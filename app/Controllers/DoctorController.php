<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use App\Config\Constants;

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
        
        // Require doctor authentication
        $this->auth->requireRole('doctor');
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
        
        $content = $this->view->render('doctor/patients', [
            'patients' => $patients
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
        
        $content = $this->view->render('doctor/patient', [
            'patient' => $patient,
            'timeline' => $timeline,
            'medicalHistory' => $medicalHistory,
            'recentAppointments' => $recentAppointments,
            'doctorId' => $doctorId
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
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get appointment details
        $appointment = $this->getAppointment($id, $doctorId);
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
            'doctorId' => $doctorId
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
        
        $content = $this->view->render('doctor/profile', [
            'user' => $user
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
        
        // Get appointment details
        $appointment = $this->getAppointment($id, $doctorId);
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
        
        // Get appointment details
        $appointment = $this->getAppointment($id, $doctorId);
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
        
        // Verify appointment belongs to this doctor
        $appointment = $this->getAppointment($id, $doctorId);
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
                    refraction_right = ?, refraction_left = ?, IOP_right = ?, IOP_left = ?, slit_lamp = ?,
                    fundus = ?, diagnosis = ?, diagnosis_code = ?, plan = ?, followup_days = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND appointment_id = ?
                ");
            } else {
                // Create new consultation note
                $stmt = $this->pdo->prepare("
                    INSERT INTO consultation_notes (appointment_id, chief_complaint, hx_present_illness, 
                    visual_acuity_right, visual_acuity_left, refraction_right, refraction_left, 
                    IOP_right, IOP_left, slit_lamp, fundus, diagnosis, diagnosis_code, plan, 
                    followup_days, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
            }
            
            // Process and validate input data
            $iopRight = (!empty($_POST['IOP_right']) && is_numeric($_POST['IOP_right'])) ? (float)$_POST['IOP_right'] : null;
            $iopLeft = (!empty($_POST['IOP_left']) && is_numeric($_POST['IOP_left'])) ? (float)$_POST['IOP_left'] : null;
            $followupDays = (!empty($_POST['followup_days']) && is_numeric($_POST['followup_days'])) ? (int)$_POST['followup_days'] : null;
            
            // Helper function to handle empty strings
            $processTextField = function($value) {
                return !empty(trim($value ?? '')) ? trim($value) : null;
            };
            
            if ($noteId) {
                // Execute UPDATE query
                $stmt->execute([
                    $processTextField($_POST['chief_complaint']),
                    $processTextField($_POST['hx_present_illness']),
                    $processTextField($_POST['visual_acuity_right']),
                    $processTextField($_POST['visual_acuity_left']),
                    $processTextField($_POST['refraction_right']),
                    $processTextField($_POST['refraction_left']),
                    $iopRight,
                    $iopLeft,
                    $processTextField($_POST['slit_lamp']),
                    $processTextField($_POST['fundus']),
                    $processTextField($_POST['diagnosis']),
                    $processTextField($_POST['diagnosis_code']),
                    $processTextField($_POST['plan']),
                    $followupDays,
                    $noteId,
                    $id
                ]);
            } else {
                // Execute INSERT query
                $stmt->execute([
                    $id,
                    $processTextField($_POST['chief_complaint']),
                    $processTextField($_POST['hx_present_illness']),
                    $processTextField($_POST['visual_acuity_right']),
                    $processTextField($_POST['visual_acuity_left']),
                    $processTextField($_POST['refraction_right']),
                    $processTextField($_POST['refraction_left']),
                    $iopRight,
                    $iopLeft,
                    $processTextField($_POST['slit_lamp']),
                    $processTextField($_POST['fundus']),
                    $processTextField($_POST['diagnosis']),
                    $processTextField($_POST['diagnosis_code']),
                    $processTextField($_POST['plan']),
                    $followupDays,
                    $user['id']
                ]);
            }
            
            // Redirect back to appointment view
            header('Location: /doctor/appointments/' . $id . '?success=1');
            exit;
            
        } catch (\Exception $e) {
            error_log("Error updating consultation for appointment $id: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
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
        
        try {
            // Verify current password
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch();
            
            if (!$userData || !password_verify($currentPassword, $userData['password'])) {
                header('Location: /doctor/profile?error=Current password is incorrect');
                exit;
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            header('Location: /doctor/profile?success=Password updated successfully');
            exit;
            
        } catch (\Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            header('Location: /doctor/profile?error=Failed to update password');
            exit;
        }
    }

    // Private helper methods
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

    private function getMedicalHistory($patientId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM medical_history WHERE patient_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    private function getPatientAppointments($patientId, $doctorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, cn.diagnosis, cn.plan
            FROM appointments a
            LEFT JOIN consultation_notes cn ON a.id = cn.appointment_id
            WHERE a.patient_id = ? AND a.doctor_id = ?
            ORDER BY a.date DESC, a.start_time DESC
            LIMIT 10
        ");
        $stmt->execute([$patientId, $doctorId]);
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
                   MAX(a.date) as last_visit
            FROM patients p
            LEFT JOIN appointments a ON p.id = a.patient_id
            GROUP BY p.id
            ORDER BY p.created_at DESC
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
}
