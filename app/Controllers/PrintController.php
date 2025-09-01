<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use App\Config\Constants;

class PrintController
{
    private $auth;
    private $view;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Check authentication for print pages
        if (!$this->auth->check()) {
            http_response_code(401);
            echo "<!DOCTYPE html><html><head><title>Unauthorized</title></head><body><h1>401 - Unauthorized</h1><p>Please log in to access this page.</p><script>window.close();</script></body></html>";
            exit;
        }
    }

    public function medicationPrescription($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get appointment details first
            $appointment = $this->getAppointment($id);
            if (!$appointment) {
                http_response_code(404);
                echo "Appointment not found";
                return;
            }
            
            // Get medication prescriptions for this appointment
            $prescriptions = $this->getMedicationPrescriptions($id);
            if (empty($prescriptions)) {
                http_response_code(404);
                echo "No medication prescriptions found for this appointment";
                return;
            }
            
            // Get patient and doctor details
            $patient = $this->getPatient($appointment['patient_id']);
            $doctor = $this->getDoctor($appointment['doctor_id']);
            
            // Set print-specific headers
            header('Content-Type: text/html; charset=utf-8');
            
            echo $this->view->render('print/medication-prescription', [
                'prescriptions' => $prescriptions,
                'patient' => $patient,
                'appointment' => $appointment,
                'doctor' => $doctor,
                'clinic' => $this->getClinicInfo()
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }

    public function glassesPrescription($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get glasses prescription by its ID (not appointment ID)
            $prescription = $this->getGlassesPrescription($id);
            if (!$prescription) {
                http_response_code(404);
                echo "No glasses prescription found with ID: " . $id;
                return;
            }
            
            // Get appointment details using the appointment_id from prescription
            $appointment = $this->getAppointment($prescription['appointment_id']);
            if (!$appointment) {
                http_response_code(404);
                echo "Associated appointment not found";
                return;
            }
            
            // Get patient and doctor details
            $patient = $this->getPatient($appointment['patient_id']);
            $doctor = $this->getDoctor($appointment['doctor_id']);
            
            // Set print-specific headers
            header('Content-Type: text/html; charset=utf-8');
            
            echo $this->view->render('print/glasses-prescription', [
                'prescription' => $prescription,
                'patient' => $patient,
                'appointment' => $appointment,
                'doctor' => $doctor,
                'clinic' => $this->getClinicInfo()
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }

    public function singleLabTest($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get single lab test details
            $stmt = $this->pdo->prepare("SELECT * FROM lab_tests WHERE id = ?");
            $stmt->execute([$id]);
            $labTest = $stmt->fetch();
            
            if (!$labTest) {
                http_response_code(404);
                echo "Lab test not found";
                return;
            }
            
            // Get patient and appointment details
            $patient = $this->getPatient($labTest['patient_id']);
            $appointment = $this->getAppointment($labTest['appointment_id']);
            $doctor = $this->getDoctor($appointment['doctor_id']);
            
            // Set print-specific headers
            header('Content-Type: text/html; charset=utf-8');
            
            echo $this->view->render('print/single-lab-test', [
                'labTest' => $labTest,
                'patient' => $patient,
                'appointment' => $appointment,
                'doctor' => $doctor,
                'clinic' => $this->getClinicInfo()
            ]);
            
        } catch (Exception $e) {
            error_log("Error in singleLabTest: " . $e->getMessage());
            http_response_code(500);
            echo "Error generating lab test print";
        }
    }

    public function labTests($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get all lab tests for this appointment
            $stmt = $this->pdo->prepare("SELECT * FROM lab_tests WHERE appointment_id = ? ORDER BY created_at ASC");
            $stmt->execute([$id]);
            $labTests = $stmt->fetchAll();
            
            if (empty($labTests)) {
                http_response_code(404);
                echo "No lab tests found for this appointment";
                return;
            }
            
            // Get patient and appointment details from first test
            $patient = $this->getPatient($labTests[0]['patient_id']);
            $appointment = $this->getAppointment($id);
            $doctor = $this->getDoctor($appointment['doctor_id']);
            
            // Set print-specific headers
            header('Content-Type: text/html; charset=utf-8');
            
            echo $this->view->render('print/lab-tests-new', [
                'labTests' => $labTests,
                'patient' => $patient,
                'appointment' => $appointment,
                'doctor' => $doctor,
                'clinic' => $this->getClinicInfo()
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }

    public function invoice($id)
    {
        try {
            $user = $this->auth->user();
            
            // Get invoice details
            $invoice = $this->getInvoice($id);
            if (!$invoice) {
                http_response_code(404);
                echo "Invoice not found";
                return;
            }
            
            // Get patient and invoice items
            $patient = $this->getPatient($invoice['patient_id']);
            $items = $this->getInvoiceItems($id);
            
            // Set print-specific headers
            header('Content-Type: text/html; charset=utf-8');
            
            echo $this->view->render('print/invoice', [
                'invoice' => $invoice,
                'patient' => $patient,
                'items' => $items,
                'clinic' => $this->getClinicInfo()
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }

    // Helper methods
    private function getMedicationPrescription($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, a.patient_id, a.date as appointment_date
            FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getGlassesPrescription($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT gp.*, a.patient_id, a.date as appointment_date, a.start_time, a.visit_type
            FROM glasses_prescriptions gp
            JOIN appointments a ON gp.appointment_id = a.id
            WHERE gp.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getConsultationNotes($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT cn.*, a.patient_id, a.date as appointment_date
            FROM consultation_notes cn
            JOIN appointments a ON cn.appointment_id = a.id
            WHERE cn.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
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
        $patient = $stmt->fetch();
        
        // Calculate age if date of birth is available
        if ($patient && $patient['dob']) {
            $dob = new \DateTime($patient['dob']);
            $now = new \DateTime();
            $age = $dob->diff($now)->y;
            $patient['age_computed'] = $age;
        } else {
            $patient['age_computed'] = null;
        }
        
        return $patient;
    }

    private function getAppointment($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, p.first_name, p.last_name, p.phone
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getDoctor($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT d.*, u.name as user_name
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getInvoice($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*, p.first_name, p.last_name, p.phone
            FROM invoices i
            JOIN patients p ON i.patient_id = p.id
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getInvoiceItems($invoiceId)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.name as received_by_name
            FROM payments p
            JOIN users u ON p.received_by = u.id
            WHERE p.invoice_id = ?
            ORDER BY p.created_at
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    private function getClinicInfo()
    {
        return [
            'name' => Constants::APP_NAME,
            'address' => '123 Medical Center St., Cairo, Egypt',
            'phone' => '+20 2 1234 5678',
            'email' => 'info@ophthalmology-clinic.com',
            'website' => 'www.ophthalmology-clinic.com',
            'license' => 'Medical License #12345',
            'tax_id' => 'Tax ID: 123-456-789'
        ];
    }

    public function appointmentReport($id)
    {
        $appointment = $this->getAppointmentDetails($id);
        if (!$appointment) {
            http_response_code(404);
            echo "Appointment not found";
            return;
        }

        $consultationNotes = $this->getConsultationNotesByAppointmentId($id);
        $medications = $this->getMedicationPrescriptions($id);
        $glasses = $this->getGlassesPrescriptions($id);
        $clinic = $this->getClinicInfo();

        $content = $this->view->render('print/appointment_report', [
            'appointment' => $appointment,
            'consultationNotes' => $consultationNotes,
            'medications' => $medications,
            'glasses' => $glasses,
            'clinic' => $clinic
        ], false);

        echo $content;
    }

    private function getAppointmentDetails($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.dob, p.phone, p.national_id,
                d.display_name as doctor_name,
                u.name as booked_by_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON a.booked_by = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getConsultationNotesByAppointmentId($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT cn.*, u.name as created_by_name
            FROM consultation_notes cn
            LEFT JOIN users u ON cn.created_by = u.id
            WHERE cn.appointment_id = ?
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
        return $stmt->fetch();
    }
}
