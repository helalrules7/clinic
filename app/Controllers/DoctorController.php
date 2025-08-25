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
        
        $content = $this->view->render('doctor/calendar', [
            'doctorId' => $doctorId,
            'availableDates' => $availableDates
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Calendar - Doctor Dashboard',
            'pageTitle' => 'Calendar',
            'pageSubtitle' => 'Manage your appointments',
            'content' => $content
        ]);
    }

    public function viewPatient($id)
    {
        $user = $this->auth->user();
        $doctorId = $this->getDoctorId($user['id']);
        
        // Get patient details
        $patient = $this->getPatient($id);
        if (!$patient) {
            http_response_code(404);
            echo "Patient not found";
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
            echo "Appointment not found";
            return;
        }
        
        // Get patient details
        $patient = $this->getPatient($appointment['patient_id']);
        
        // Get consultation notes if exists
        $consultationNotes = $this->getConsultationNotes($id);
        
        // Get prescriptions
        $medications = $this->getMedicationPrescriptions($id);
        $glasses = $this->getGlassesPrescriptions($id);
        
        $content = $this->view->render('doctor/appointment', [
            'appointment' => $appointment,
            'patient' => $patient,
            'consultationNotes' => $consultationNotes,
            'medications' => $medications,
            'glasses' => $glasses,
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

    public function changePassword()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            $user = $this->auth->user();
            
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new \Exception('All fields are required');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new \Exception('New passwords do not match');
            }
            
            // Change password
            $this->auth->changePassword($user['id'], $currentPassword, $newPassword);
            
            // Redirect to login with success message
            header('Location: /login?message=Password changed successfully. Please login again.');
            exit;
            
        } catch (\Exception $e) {
            // Redirect back with error
            header('Location: /doctor/profile?error=' . urlencode($e->getMessage()));
            exit;
        }
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
            SELECT * FROM medical_history WHERE patient_id = ?
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetch();
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
            SELECT a.*, p.first_name, p.last_name, p.phone, p.dob, p.gender
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
            SELECT * FROM consultation_notes WHERE appointment_id = ?
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetch();
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

    private function validateCsrfToken()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}
