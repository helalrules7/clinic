<?php
/**
 * Roaya Clinic Management System
 * Main entry point
 */

// Set timezone
date_default_timezone_set('Africa/Cairo');

// Start session
session_start();

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Set error reporting based on environment
if (($_ENV['APP_ENV'] ?? 'local') === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

try {
    // Initialize router
    $router = new \App\Lib\Router();
    
    // Define routes
    $router->get('/', 'AuthController@showLogin');
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');
    
    // Admin routes
    $router->get('/admin/dashboard', 'AdminController@dashboard');
    $router->get('/admin/users', 'AdminController@users');
    $router->post('/admin/users', 'AdminController@createUser');
    $router->put('/admin/users/{id}', 'AdminController@updateUser');
    $router->delete('/admin/users/{id}', 'AdminController@deleteUser');
    $router->get('/admin/reports', 'AdminController@reports');
    $router->get('/admin/reports/export', 'AdminController@exportReport');
    $router->get('/admin/settings', 'AdminController@settings');
    $router->post('/admin/settings', 'AdminController@settings');
    $router->post('/admin/users/update/{id}', 'AdminController@updateUser');
    $router->post('/admin/users/delete/{id}', 'AdminController@deleteUser');
    
    // View As routes (Admin only)
    $router->get('/admin/view-as', 'AdminController@viewAs');
    $router->get('/admin/stop-view-as', 'AdminController@stopViewAs');
    
    // Secretary routes
    $router->get('/secretary/dashboard', 'SecretaryController@dashboard');
    $router->get('/secretary/bookings', 'SecretaryController@bookings');
    $router->get('/secretary/payments', 'SecretaryController@payments');
    $router->get('/secretary/patients', 'SecretaryController@patients');
    $router->get('/secretary/patients/new', 'SecretaryController@newPatient');
    $router->post('/secretary/patients', 'SecretaryController@createPatient');
    $router->get('/secretary/patients/{id}', 'SecretaryController@viewPatient');
    $router->get('/secretary/invoices/{id}', 'SecretaryController@viewInvoice');
    
    // Doctor routes
    $router->get('/doctor/dashboard', 'DoctorController@dashboard');
    $router->get('/doctor/calendar', 'DoctorController@calendar');
    $router->get('/doctor/patients', 'DoctorController@patients');
    $router->get('/doctor/patients/{id}', 'DoctorController@showPatient');
    $router->get('/doctor/patients/{id}/edit', 'DoctorController@editPatient');
    $router->put('/doctor/patients/{id}', 'DoctorController@updatePatient');
    $router->get('/doctor/appointments/{id}', 'DoctorController@viewAppointment');
    $router->get('/doctor/appointments/{id}/edit', 'DoctorController@editConsultation');
    $router->get('/doctor/appointments/{id}/edit/new', 'DoctorController@newConsultation');
    $router->post('/doctor/appointments/{id}/edit', 'DoctorController@updateConsultation');
    $router->post('/doctor/appointments/{id}/consultation', 'DoctorController@saveConsultation');
    $router->get('/doctor/profile', 'DoctorController@profile');
    $router->post('/doctor/profile/change-password', 'DoctorController@changePassword');
    $router->post('/doctor/profile/update', 'DoctorController@updateProfile');
    $router->get('/doctor/drugs', 'DoctorController@drugs');
    $router->get('/doctor/reports', 'DoctorController@reports');
    $router->get('/doctor/reports/export', 'DoctorController@exportDoctorReport');
    
    // Medical History routes
    $router->post('/api/patients/{id}/medical-history', 'ApiController@createMedicalHistory');
    $router->get('/api/patients/{id}/medical-history/{historyId}', 'ApiController@getMedicalHistoryEntry');
    $router->put('/api/patients/{id}/medical-history/{historyId}', 'ApiController@updateMedicalHistory');
    $router->delete('/api/patients/{id}/medical-history/{historyId}', 'ApiController@deleteMedicalHistory');
    
    // General routes
    $router->get('/about', 'GeneralController@about');
    
    // API routes
    $router->get('/api/calendar', 'ApiController@getCalendar');
    $router->get('/api/appointments/{id}', 'ApiController@getAppointment');
    $router->post('/api/appointments', 'ApiController@createAppointment');
    $router->put('/api/appointments/{id}', 'ApiController@updateAppointment');
    $router->post('/api/payments', 'ApiController@createPayment');
    $router->get('/api/patients/search', 'ApiController@searchPatients');
    $router->post('/api/patients', 'ApiController@createPatient');
    $router->delete('/api/patients/{id}', 'ApiController@deletePatient');
    $router->get('/api/patients/{id}/timeline', 'ApiController@getPatientTimeline');
    $router->put('/api/patients/{id}/emergency-contact', 'ApiController@updateEmergencyContact');
    $router->post('/api/consultations', 'ApiController@createConsultation');
    $router->post('/api/prescriptions/meds', 'ApiController@createMedicationPrescription');
    $router->put('/api/prescriptions/meds/{id}', 'ApiController@updateMedication');
    $router->delete('/api/prescriptions/meds/{id}', 'ApiController@deleteMedication');
    $router->post('/api/prescriptions/glasses', 'ApiController@createGlassesPrescription');
    $router->put('/api/prescriptions/glasses/{id}', 'ApiController@updateGlassesPrescription');
    $router->delete('/api/prescriptions/glasses/{id}', 'ApiController@deleteGlassesPrescription');
    // Drug Search API routes
    $router->get('/api/searchDrugs', 'ApiController@searchDrugs');
    $router->get('/api/getDrugDetails', 'ApiController@getDrugDetails');
    $router->get('/api/getFilterOptions', 'ApiController@getFilterOptions');
    
    // Lab Tests & Radiology API routes
    $router->post('/api/lab-tests', 'ApiController@createLabTest');
    $router->put('/api/lab-tests/{id}', 'ApiController@updateLabTest');
    $router->delete('/api/lab-tests/{id}', 'ApiController@deleteLabTest');
    $router->get('/api/lab-tests/appointment/{id}', 'ApiController@getLabTests');
    
    $router->post('/api/daily-closure/lock', 'ApiController@lockDailyClosure');
    $router->post('/api/users/change-password', 'ApiController@changePassword');
    
    // Attachment API routes
    $router->post('/api/attachments/upload', 'ApiController@uploadAttachment');
    $router->get('/api/attachments/view/{id}', 'ApiController@viewAttachment');
    $router->get('/api/attachments/download/{id}', 'ApiController@downloadAttachment');
    $router->delete('/api/attachments/{id}', 'ApiController@deleteAttachment');
    
    // Patient Files API routes
    $router->post('/api/patients/files/upload', 'ApiController@uploadPatientFile');
    $router->get('/api/patients/files/view/{id}', 'ApiController@viewPatientFile');
    $router->get('/api/patients/files/download/{id}', 'ApiController@downloadPatientFile');
    $router->delete('/api/patients/files/{id}', 'ApiController@deletePatientFile');
    
    // Patient Notes API routes
    $router->post('/api/patients/notes', 'ApiController@createPatientNote');
    $router->put('/api/patients/notes/{id}', 'ApiController@updatePatientNote');
    $router->delete('/api/patients/notes/{id}', 'ApiController@deletePatientNote');
    
    // Patient Appointments API routes
    $router->get('/api/patients/{id}/appointments', 'ApiController@getPatientAppointments');
    
    // Patient Export API routes
    $router->get('/api/patients/{id}/export', 'ApiController@exportPatientData');
    $router->head('/api/patients/{id}/export', 'ApiController@checkExportAccess');
    
    // Individual Glasses Prescription API routes
    $router->get('/api/prescriptions/glasses/{id}', 'ApiController@getGlassesPrescription');
        
    // Print routes
    $router->get('/print/prescription/{id}', 'PrintController@medicationPrescription');
    $router->get('/print/glasses/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/glasses-prescription/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/lab-test/{id}', 'PrintController@singleLabTest');
    $router->get('/print/lab-tests/{id}', 'PrintController@labTests');
    $router->get('/print/invoice/{id}', 'PrintController@invoice');
    $router->get('/print/appointment/{id}', 'PrintController@appointmentReport');
        
    // Handle the request
    $router->dispatch();
    
} catch (Exception $e) {
    // Log error
    error_log("Fatal error: " . $e->getMessage());
    
    // Show error page
    if (($_ENV['APP_ENV'] ?? 'local') === 'local') {
        echo "<h1>Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
}
