<?php
/**
 * Roaya Clinic Management System
 */

date_default_timezone_set('Africa/Cairo');
session_start();

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables with error handling
if (file_exists(__DIR__ . '/.env') && is_readable(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
}

// Set default values if .env not loaded
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'localhost';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'AhmedHelal_roaya';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'AhmedHelal_roaya';
$_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? 'Carmen@1230';
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'production';
$_ENV['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? 'false';

// Error reporting
if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR);
    ini_set('display_errors', 0);
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

try {
    $router = new \App\Lib\Router();
    
    // Define routes
    $router->get('/', 'AuthController@showLogin');
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');
    
    // Admin routes
    $router->get('/admin/dashboard', 'AdminController@dashboard');
    $router->get('/admin/users', 'AdminController@users');
    $router->get('/admin/reports', 'AdminController@reports');
    $router->get('/admin/settings', 'AdminController@settings');
    $router->post('/admin/settings', 'AdminController@updateSettings');
    $router->get('/admin/users/create', 'AdminController@createUser');
    $router->post('/admin/users', 'AdminController@storeUser');
    $router->get('/admin/users/{id}/edit', 'AdminController@editUser');
    $router->put('/admin/users/{id}', 'AdminController@updateUser');
    $router->delete('/admin/users/{id}', 'AdminController@deleteUser');
    
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
    $router->get('/doctor/appointments/{id}', 'DoctorController@viewAppointment');
    $router->get('/doctor/appointments/{id}/edit', 'DoctorController@editConsultation');
    $router->get('/doctor/appointments/{id}/edit/new', 'DoctorController@newConsultation');
    $router->get('/doctor/patients/{id}/edit', 'DoctorController@editPatient');
    $router->put('/doctor/patients/{id}', 'DoctorController@updatePatient');
    $router->post('/doctor/appointments/{id}/edit', 'DoctorController@updateConsultation');
    $router->post('/doctor/appointments/{id}/consultation', 'DoctorController@saveConsultation');
    $router->get('/doctor/profile', 'DoctorController@profile');
    $router->post('/doctor/profile/change-password', 'DoctorController@changePassword');
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
    
    // Patient Export API routes
    $router->get('/api/patients/{id}/export', 'ApiController@exportPatientData');
    $router->head('/api/patients/{id}/export', 'ApiController@checkExportAccess');
        

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
    
    // ✅ FIXED: استخدام dispatch() بدلاً من handle()
    $router->dispatch();
    
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>خطأ في النظام</h1><p>يرجى المحاولة مرة أخرى.</p>";
}
