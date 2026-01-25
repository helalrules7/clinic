<?php
/**
 * Roaya Clinic Management System
 */

// Clear output buffers FIRST to prevent HTML output before API calls
while (ob_get_level()) {
    ob_end_clean();
}

date_default_timezone_set('Africa/Cairo');
session_start();

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load Controllers
require_once __DIR__ . '/app/Controllers/AlertController.php';
// require_once __DIR__ . '/app/Controllers/NotesController.php';

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
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'db';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'roaya';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'roaya_user';
$_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? 'roaya_password';
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'local';
$_ENV['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? 'true';

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
    
    $router->get('/', 'GeneralController@home');
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');
    $router->get('/api/auth/session-time', 'AuthController@getSessionTime');
    
    // Admin routes
    $router->get('/admin/dashboard', 'AdminController@dashboard');
    $router->get('/admin/users', 'AdminController@users');
    $router->post('/admin/users', 'AdminController@createUser');
    $router->put('/admin/users/{id}', 'AdminController@updateUser');
    $router->delete('/admin/users/{id}', 'AdminController@deleteUser');
    $router->get('/admin/settings', 'AdminController@settings');
    $router->post('/admin/settings', 'AdminController@settings');
    $router->get('/admin/notifications', 'AdminController@notifications');
    $router->get('/admin/media', 'AdminController@media');
    $router->get('/admin/backup', 'AdminController@backup');
    $router->post('/api/admin/backup/database', 'AdminController@apiBackupDatabase');
    $router->post('/api/admin/backup/full', 'AdminController@apiBackupFull');
    $router->post('/api/admin/backup/website', 'AdminController@apiBackupWebsite');
    $router->get('/api/admin/backup/list', 'AdminController@apiBackupList');
    $router->post('/api/admin/backup/restore', 'AdminController@apiBackupRestore');
    $router->post('/api/admin/backup/restore-upload', 'AdminController@apiBackupRestoreUpload');
    $router->get('/api/admin/backup/download/{type}/{name}', 'AdminController@apiBackupDownload');
    $router->get('/api/admin/media/list', 'AdminController@apiMediaList');
    $router->post('/api/admin/media/delete', 'AdminController@apiMediaDelete');
    $router->post('/api/admin/media/delete-all', 'AdminController@apiMediaDeleteAll');
    $router->post('/api/admin/media/backup', 'AdminController@apiMediaBackup');
    $router->get('/api/admin/media/backups', 'AdminController@apiMediaBackups');
    $router->post('/api/admin/media/restore', 'AdminController@apiMediaRestore');
    $router->post('/api/admin/media/restore-upload', 'AdminController@apiMediaRestoreUpload');
    $router->get('/api/admin/media/backup-download/{name}', 'AdminController@apiMediaBackupDownload');
    $router->post('/api/admin/backup/database', 'AdminController@apiBackupDatabase');
    $router->post('/api/admin/backup/full', 'AdminController@apiBackupFull');
    $router->post('/api/admin/backup/website', 'AdminController@apiBackupWebsite');
    $router->get('/api/admin/backup/list', 'AdminController@apiBackupList');
    $router->post('/api/admin/backup/restore', 'AdminController@apiBackupRestore');
    $router->post('/api/admin/backup/restore-upload', 'AdminController@apiBackupRestoreUpload');
    $router->get('/api/admin/backup/download/{type}/{name}', 'AdminController@apiBackupDownload');
    $router->post('/admin/users/update/{id}', 'AdminController@updateUser');
    $router->post('/admin/users/delete/{id}', 'AdminController@deleteUser');
    
    // View As routes (Admin only)
    $router->get('/admin/view-as', 'AdminController@viewAs');
    $router->get('/admin/stop-view-as', 'AdminController@stopViewAs');

    
    // Secretary routes
    $router->get('/secretary/dashboard', 'SecretaryController@dashboard');
    $router->get('/api/secretary/dashboard', 'SecretaryController@getDashboardData');
    $router->get('/secretary/bookings', 'SecretaryController@bookings');
    $router->get('/secretary/bookings/calendar', 'SecretaryController@getBookingsCalendar');
    $router->post('/secretary/bookings', 'SecretaryController@createBooking');
    $router->delete('/secretary/bookings/{id}', 'SecretaryController@deleteBooking');
    $router->post('/secretary/bookings/{id}/confirm', 'SecretaryController@confirmAttendance');
    $router->get('/secretary/bookings/{id}/details', 'SecretaryController@getBookingDetails');
    $router->post('/secretary/bookings/{id}/update', 'SecretaryController@updateBooking');
    $router->get('/secretary/bookings/{id}', 'SecretaryController@viewBooking');
    $router->get('/secretary/payments', 'SecretaryController@payments');
    $router->get('/secretary/patients', 'SecretaryController@patients');
    $router->get('/api/secretary/patients', 'SecretaryController@getPatientsData');
    $router->get('/secretary/patients/{id}', 'SecretaryController@viewPatient');
    $router->get('/secretary/patients/new', 'SecretaryController@newPatient');
    $router->post('/secretary/patients', 'SecretaryController@createPatient');
    $router->get('/secretary/invoices/{id}', 'SecretaryController@viewInvoice');
    $router->get('/secretary/payments/{id}', 'SecretaryController@viewPayment');
    $router->get('/secretary/payments/{id}/receipt', 'PrintController@paymentReceipt');
    $router->get('/secretary/expenses/{id}', 'SecretaryController@viewExpense');
    $router->get('/secretary/bookings/{id}', 'SecretaryController@viewBooking');
    $router->get('/secretary/bookings/{id}/print', 'PrintController@bookingDetails');
    $router->get('/secretary/patients/{id}/invoice', 'PrintController@patientInvoice');
    $router->get('/secretary/profile', 'SecretaryController@profile');
    $router->post('/secretary/profile/update', 'SecretaryController@updateProfile');
    $router->post('/secretary/profile/change-password', 'SecretaryController@changePassword');
    // Doctor routes
    $router->get('/doctor/dashboard', 'DoctorController@dashboard');
    $router->get('/doctor/calendar', 'DoctorController@calendar');
    $router->get('/doctor/organizer', 'DoctorController@organizer');
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
    $router->post('/doctor/profile/update', 'DoctorController@updateProfile');
    $router->post('/doctor/profile/change-password', 'DoctorController@changePassword');
    $router->post('/doctor/profile/update-field', 'DoctorController@updateField');
    $router->get('/doctor/drugs', 'DoctorController@drugs');
    $router->get('/doctor/payments', 'DoctorController@payments');
    $router->get('/doctor/daily-closure', 'DoctorController@dailyClosure');
    $router->get('/doctor/reports', 'DoctorController@reports');
    $router->get('/doctor/reports/export', 'DoctorController@exportDoctorReport');
    $router->get('/doctor/settings', 'DoctorController@settings');
    $router->post('/doctor/settings', 'DoctorController@settings');
    
    // Alerts routes - specific routes first to avoid conflicts
    $router->get('/doctor/alerts', 'AlertController@index');
    $router->get('/api/alerts/today', 'AlertController@getTodayAlerts');
    $router->get('/api/alerts/active', 'AlertController@getActiveAlerts');
    $router->post('/api/alerts/dismiss', 'AlertController@dismiss');
    $router->post('/api/alerts/{id}/toggle-status', 'AlertController@toggleStatus');
    $router->get('/api/alerts/patient/{patientId}', 'AlertController@getPatientAlerts');
    $router->post('/api/alerts/disable-all', 'AlertController@disableAllAlerts');
    $router->delete('/api/alerts/delete-all', 'AlertController@deleteAllAlerts');
    $router->get('/api/alerts', 'AlertController@getAllAlerts');
    $router->get('/api/alerts/{id}', 'AlertController@get');
    $router->post('/api/alerts', 'AlertController@create');
    $router->put('/api/alerts/{id}', 'AlertController@update');
    $router->delete('/api/alerts/{id}', 'AlertController@delete');
    
    // Notes routes - moved to DoctorController
    $router->get('/doctor/notes', 'DoctorController@notes');
    $router->get('/api/notes', 'NotesController@getNotes');
    $router->post('/api/notes', 'NotesController@createNote');
    $router->delete('/api/notes/delete-all', 'NotesController@deleteAllNotes'); // Must be before {id} route
    $router->get('/api/notes/{id}', 'NotesController@getNote');
    $router->put('/api/notes/{id}', 'NotesController@updateNote');
    $router->delete('/api/notes/{id}', 'NotesController@deleteNote');
    
    // Doctor settings routes
    $router->get('/api/doctor/settings', 'DoctorController@getDoctorSettings');
    $router->put('/api/doctor/settings', 'DoctorController@updateDoctorSettings');
    
    // Notifications API routes
    $router->get('/api/notifications', 'NotificationController@getAll');
    $router->get('/api/notifications/unread-count', 'NotificationController@getUnreadCount');
    $router->put('/api/notifications/{id}/read', 'NotificationController@markAsRead');
    $router->put('/api/notifications/read-all', 'NotificationController@markAllAsRead');
    $router->delete('/api/notifications/clear-all', 'NotificationController@clearAll');
    $router->delete('/api/notifications/{id}', 'NotificationController@delete');
    $router->post('/api/notifications/system', 'NotificationController@createSystemNotification');

    // Doctor Forum routes
    $router->get('/doctor/forum', 'ForumController@index');
    $router->get('/doctor/forum/topic/{id}', 'ForumController@topic');
    
    // Forum API routes - Topics
    $router->get('/api/forum/topics', 'ForumController@getTopics');
    $router->get('/api/forum/topics/{id}', 'ForumController@getTopic');
    $router->post('/api/forum/topics', 'ForumController@createTopic');
    $router->put('/api/forum/topics/{id}', 'ForumController@updateTopic');
    $router->delete('/api/forum/topics/{id}', 'ForumController@deleteTopic');
    $router->get('/api/forum/topics/patient/{patientId}', 'ForumController@getPatientTopics');
    $router->get('/api/forum/topics/appointment/{appointmentId}', 'ForumController@getAppointmentTopics');
    
    // Forum API routes - Posts
    $router->get('/api/forum/posts/topic/{topicId}', 'ForumController@getTopicPosts');
    $router->get('/api/forum/posts/{id}', 'ForumController@getPost');
    $router->post('/api/forum/posts', 'ForumController@createPost');
    $router->put('/api/forum/posts/{id}', 'ForumController@updatePost');
    $router->delete('/api/forum/posts/{id}', 'ForumController@deletePost');
    
    // Forum API routes - Likes (Posts)
    $router->post('/api/forum/posts/{id}/like', 'ForumController@likePost');
    $router->post('/api/forum/posts/{id}/dislike', 'ForumController@dislikePost');
    $router->delete('/api/forum/posts/{id}/like', 'ForumController@removeLike');
    
    // Forum API routes - Likes (Topics)
    $router->post('/api/forum/topics/{id}/like', 'ForumController@likeTopic');
    $router->post('/api/forum/topics/{id}/dislike', 'ForumController@dislikeTopic');
    
    // Forum API routes - Images
    $router->post('/api/forum/posts/{id}/images', 'ForumController@uploadImage');
    $router->delete('/api/forum/images/{id}', 'ForumController@deleteImage');
    
    // Forum API routes - Tags
    $router->post('/api/forum/topics/{id}/tags', 'ForumController@addTags');
    $router->delete('/api/forum/topics/{id}/tags/{tagId}', 'ForumController@removeTag');
    
    // Forum API routes - Attachments
    $router->post('/api/forum/attachments/upload', 'ForumController@uploadAttachment');
    $router->get('/api/forum/attachments/view/{id}', 'ForumController@viewAttachment');
    $router->delete('/api/forum/attachments/{id}', 'ForumController@deleteAttachment');
    
    // Forum API routes - Statistics and Actions
    $router->get('/api/forum/stats/categories', 'ForumController@getCategoryStats');
    $router->get('/api/forum/stats/top-meta', 'ForumController@getTopMetaTags');
    $router->post('/api/forum/topics/{id}/toggle-resolved', 'ForumController@toggleResolved');
    $router->post('/api/forum/topics/{id}/toggle-pin', 'ForumController@togglePin');

        // Medical History routes
    $router->get('/api/patients/{id}/medical-history', 'ApiController@getPatientMedicalHistory');
    $router->post('/api/patients/{id}/medical-history', 'ApiController@createMedicalHistory');
    $router->get('/api/patients/{id}/medical-history/{historyId}', 'ApiController@getMedicalHistoryEntry');
    $router->put('/api/patients/{id}/medical-history/{historyId}', 'ApiController@updateMedicalHistory');
    $router->delete('/api/patients/{id}/medical-history/{historyId}', 'ApiController@deleteMedicalHistory');
        
    // Ophthalmology News routes
    $router->get('/api/ophthalmology-news', 'ApiController@getOphthalmologyNews');  
    
    // Weather API route
    $router->get('/api/weather', 'ApiController@getWeather');
    $router->get('/api/weather-forecast', 'ApiController@getWeatherForecast');
    $router->get('/api/weather-ar', 'ApiController@getWeatherArabic');
    $router->get('/api/weather-forecast-ar', 'ApiController@getWeatherForecastArabic');

    // General routes
    $router->get('/about', 'GeneralController@about');
    $router->get('/whats-new', 'GeneralController@whatsNew');
    $router->get('/whats-new/older-versions', 'GeneralController@olderVersions');
    $router->get('/whats-new/full-features', 'GeneralController@fullfeatures');

    // Media routes
    $router->get('/doctor/media', 'MediaController@index');
    $router->get('/api/media', 'MediaController@getMedia');
    $router->get('/api/media/patient', 'MediaController@getPatientImages');
    
    // Glasses Prescriptions routes
    $router->get('/doctor/glasses', 'GlassesController@index');
    $router->get('/api/glasses/prescriptions', 'GlassesController@getGlassesPrescriptions');
    $router->get('/api/glasses/prescriptions/patient', 'GlassesController@getPatientGlassesPrescriptions');
    
    // Medications Prescriptions routes
    $router->get('/doctor/medications', 'MedicationsController@index');
    $router->get('/api/medications/prescriptions', 'MedicationsController@getMedicationsPrescriptions');
    $router->get('/api/medications/prescriptions/patient', 'MedicationsController@getPatientMedicationsPrescriptions');
    
    // API routes
    $router->get('/api/calendar', 'ApiController@getCalendar');
    $router->get('/api/organizer/month', 'ApiController@getOrganizerMonth');
    // More specific routes first
    // More specific routes first - search must come before {id}
    $router->get('/api/appointments/search', 'ApiController@searchAppointments');
    $router->get('/api/appointments/{id}/attachments', 'ApiController@getAppointmentAttachments');
    $router->get('/api/appointments/{id}/medications', 'ApiController@getAppointmentMedications');
    $router->get('/api/appointments/{id}/glasses', 'ApiController@getAppointmentGlasses');
    $router->get('/api/appointments/{id}/followup', 'ApiController@getFollowupAppointment');
    $router->get('/api/appointments/{id}/original', 'ApiController@getOriginalAppointment');
    $router->get('/api/appointments/{id}', 'ApiController@getAppointment');
    $router->post('/api/appointments', 'ApiController@createAppointment');
    $router->put('/api/appointments/{id}', 'ApiController@updateAppointment');
    $router->delete('/api/appointments/{id}', 'ApiController@deleteAppointment');
    $router->post('/api/appointments/{id}/reschedule', 'ApiController@reschedule');
    $router->post('/api/appointments/{id}/reschedule-followup', 'ApiController@rescheduleFollowup');
    $router->post('/api/payments', 'ApiController@createPayment');
    $router->post('/api/daily-balance', 'ApiController@createDailyBalance');
    $router->post('/api/daily-closure', 'ApiController@createDailyClosureApi');
    $router->post('/api/expenses', 'ApiController@createExpense');
    $router->put('/api/expenses/{id}', 'ApiController@updateExpense');
    $router->delete('/api/expenses/{id}', 'ApiController@deleteExpense');
    $router->put('/api/payments/{id}', 'ApiController@updatePayment');
    $router->delete('/api/payments/{id}', 'ApiController@deletePayment');
    $router->get('/api/financial-transactions', 'ApiController@getFinancialTransactions');
    $router->get('/api/financial-transactions/export', 'ApiController@exportFinancialTransactions');
    $router->get('/api/dashboard-summary', 'ApiController@getDashboardSummary');
    $router->get('/api/recent-activity', 'ApiController@getRecentActivity');
    $router->get('/api/dashboard-charts', 'ApiController@getDashboardCharts');
    $router->get('/api/missed-appointments', 'ApiController@getMissedAppointments');
    $router->get('/api/upcoming-appointments', 'ApiController@getUpcomingAppointments');
    $router->get('/api/patients/search', 'ApiController@searchPatients');
    $router->get('/api/patients', 'ApiController@getAllPatients');
    // Patient folders routes
    // More specific routes first to avoid conflicts
    // Bulk operations must come BEFORE routes with {id} parameter
    $router->delete('/api/patient-folders/bulk', 'ApiController@bulkDeletePatientFolders');
    $router->get('/api/patient-folders/{parentId}/sub-folders/{parentType}', 'ApiController@getSubFolders');
    $router->post('/api/patient-folders/{systemFolderId}/quick-sort/{sortType}', 'ApiController@quickSortSystemFolder');
    $router->get('/api/patient-folders/{id}/patients', 'ApiController@getFolderPatients');
    $router->post('/api/patient-folders/{id}/patients', 'ApiController@addPatientToFolder');
    $router->delete('/api/patient-folders/{id}/patients/{patient_id}', 'ApiController@removePatientFromFolder');
    // General folder routes (must come after specific routes)
    $router->get('/api/patient-folders', 'ApiController@getPatientFolders');
    $router->post('/api/patient-folders', 'ApiController@createPatientFolder');
    $router->put('/api/patient-folders/{id}', 'ApiController@updatePatientFolder');
    $router->delete('/api/patient-folders/{id}', 'ApiController@deletePatientFolder');
    // More specific routes first - images must come before {id}
    $router->get('/api/patients/images/{id}', 'ApiController@viewPatientImageForCards');
    $router->get('/api/patients/{id}/files', 'ApiController@getPatientFiles');
    $router->get('/api/patients/{id}/timeline', 'ApiController@getPatientTimeline');
    $router->get('/api/patients/{id}/appointments/check-active', 'ApiController@checkPatientActiveAppointments');
    $router->get('/api/patients/{id}/export', 'ApiController@exportPatientData');
    $router->head('/api/patients/{id}/export', 'ApiController@checkExportAccess');
    $router->get('/api/patients/{id}', 'ApiController@getPatient');
    $router->post('/api/patients', 'ApiController@createPatient');
    $router->delete('/api/patients/{id}', 'ApiController@deletePatient');
    $router->put('/api/patients/{id}/emergency-contact', 'ApiController@updateEmergencyContact');
    $router->post('/api/consultations', 'ApiController@createConsultation');
    $router->delete('/api/consultation-notes/{id}', 'ApiController@deleteConsultationNote');
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
    $router->get('/api/getMostUsedDrugs', 'ApiController@getMostUsedDrugs');
    $router->get('/api/searchDrugsAutocomplete', 'ApiController@searchDrugsAutocomplete');
    $router->post('/api/drugs/update-database', 'ApiController@updateDrugsDatabase');
    
    // Comprehensive Search API route
    $router->get('/api/search/comprehensive', 'ApiController@comprehensiveSearch');

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
    $router->get('/api/patients/{id}/appointments/history', 'ApiController@getPatientAppointmentsHistory');
    
    // Individual Glasses Prescription API routes
    $router->get('/api/prescriptions/glasses/{id}', 'ApiController@getGlassesPrescription');
    
    // Payment and Expense API routes
    $router->get('/api/payments/{id}', 'ApiController@getPayment');
    $router->get('/api/expenses/{id}', 'ApiController@getExpense');
    
    // Print routes
    $router->get('/print/prescription/{id}', 'PrintController@medicationPrescription');
    $router->get('/print/glasses/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/glasses-prescription/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/lab-test/{id}', 'PrintController@singleLabTest');
    $router->get('/print/lab-tests/{id}', 'PrintController@labTests');
    $router->get('/print/invoice/{id}', 'PrintController@invoice');
    $router->get('/print/appointment/{id}', 'PrintController@appointmentReport');
    
    // IOL Power Calculator API route
    $router->post('/api/iol/calculate', 'ApiController@calculateIOL');
    
    // IOP Trend Analyzer API route
    $router->get('/api/iop/analyze', 'ApiController@analyzeIOPTrend');
    
    // Pediatric IOL Undercorrection Calculator API routes
    $router->post('/api/pediatric-iol/calculate', 'ApiController@calculatePediatricIOL');
    $router->get('/api/pediatric-iol/calculate', 'ApiController@calculatePediatricIOL');
    
    // Corneal Astigmatism Calculator API routes
    $router->post('/api/astigmatism/calculate', 'ApiController@calculateCornealAstigmatism');
    $router->get('/api/astigmatism/calculate', 'ApiController@calculateCornealAstigmatism');
    
    // Target IOP Calculator API routes
    $router->post('/api/target-iop/calculate', 'ApiController@calculateTargetIOP');
    $router->get('/api/target-iop/calculate', 'ApiController@calculateTargetIOP');
    
    // Refraction Consistency Checker API routes
    $router->post('/api/refraction/consistency', 'ApiController@calculateRefractionConsistency');
    $router->get('/api/refraction/consistency', 'ApiController@calculateRefractionConsistency');
    
    // Visual Acuity Progress Calculator API routes
    $router->post('/api/visual-acuity/progress', 'ApiController@calculateVisualAcuityProgress');
    $router->get('/api/visual-acuity/progress', 'ApiController@calculateVisualAcuityProgress');
    
    // OSDI Calculator API routes
    $router->post('/api/osdi/calculate', 'ApiController@calculateOSDI');
    $router->get('/api/osdi/calculate', 'ApiController@calculateOSDI');
    $router->get('/api/patients/:patientId/osdi/history', 'ApiController@getPatientOSDIHistory');
    
    // Pachymetry-Adjusted IOP Calculator API routes
    $router->post('/api/pachymetry-adjusted-iop/calculate', 'ApiController@calculatePachymetryAdjustedIOP');
    $router->get('/api/pachymetry-adjusted-iop/calculate', 'ApiController@calculatePachymetryAdjustedIOP');
    
    // Diabetic Retinopathy Risk Estimator API routes
    $router->post('/api/diabetic-retinopathy/risk-estimate', 'ApiController@estimateDiabeticRetinopathyRisk');
    $router->get('/api/diabetic-retinopathy/risk-estimate', 'ApiController@estimateDiabeticRetinopathyRisk');
    
    // Macular Thickness Trend Analyzer API routes
    $router->post('/api/macular-thickness/trend', 'ApiController@analyzeMacularThicknessTrend');
    $router->get('/api/macular-thickness/trend', 'ApiController@analyzeMacularThicknessTrend');
    $router->get('/api/patients/:patientId/macular-thickness/history', 'ApiController@getPatientMacularThicknessHistory');
    
    // Cataract Surgery Tools API routes
    $router->post('/api/cataract-surgery/readiness', 'ApiController@calculateCataractSurgeryReadiness');
    $router->get('/api/cataract-surgery/readiness', 'ApiController@calculateCataractSurgeryReadiness');
    $router->post('/api/cataract-surgery/postop-outcome', 'ApiController@analyzePostOperativeOutcome');
    $router->get('/api/cataract-surgery/postop-outcome', 'ApiController@analyzePostOperativeOutcome');
    $router->get('/api/cataract-surgery/audit', 'ApiController@getSurgicalOutcomesAudit');
    
    // Unified Clinical Dashboard API routes
    $router->get('/api/clinical-dashboard/snapshot', 'ApiController@getClinicalDashboardSnapshot');
    
    // ✅ FIXED: استخدام dispatch() بدلاً من handle()
    $router->dispatch();
    
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>خطأ في النظام</h1><p>يرجى المحاولة مرة أخرى.</p>";
}
