<?php
/**
 * Roaya Clinic Management System
 * Main entry point
 */

// Clear output buffers FIRST to prevent HTML output before API calls
while (ob_get_level()) {
    ob_end_clean();
}

// Set timezone
date_default_timezone_set('Africa/Cairo');

// Start session
session_start();

// Load Composer autoloader (if exists)
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// v11.0.0 — Global procedural helpers (base_url, asset_url) used by view partials.
require_once __DIR__ . '/app/Lib/global_helpers.php';

// Load Controllers
require_once __DIR__ . '/app/Controllers/SecretaryController.php';
require_once __DIR__ . '/app/Controllers/AlertController.php';
require_once __DIR__ . '/app/Controllers/BoardController.php';
require_once __DIR__ . '/app/Controllers/CommentsController.php';
// require_once __DIR__ . '/app/Controllers/NotesController.php';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
    $router->get('/secretary/patients/new', 'SecretaryController@newPatient');
    $router->post('/secretary/patients', 'SecretaryController@createPatient');
    $router->get('/secretary/patients/{id}', 'SecretaryController@viewPatient');
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
    $router->get('/doctor/board', 'BoardController@index');
    $router->get('/doctor/calendar', 'DoctorController@calendar');
    $router->get('/doctor/organizer', 'DoctorController@organizer');
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
    $router->get('/doctor/instruction-templates', 'MedicalInstructionController@page');
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

    // v11.0.0 — Notification center extensions
    $router->get('/api/notifications/grouped',        'NotificationControllerV11@grouped');
    $router->post('/api/notifications/{id}/snooze',   'NotificationControllerV11@snooze');
    $router->post('/api/notifications/{id}/unsnooze', 'NotificationControllerV11@unsnooze');
    $router->post('/api/notifications/{id}/pin',      'NotificationControllerV11@pin');
    $router->post('/api/notifications/{id}/unpin',    'NotificationControllerV11@unpin');

    // v11.0.0 — To-Do (multi-list)
    $router->get('/doctor/todos',           'TodoController@page');
    $router->get('/api/todos',              'TodoController@index');
    $router->get('/api/todos/counts',       'TodoController@counts');
    $router->get('/api/todos/due-check',    'TodoController@dueCheck');
    $router->post('/api/todos/reorder',     'TodoController@reorder');
    $router->get('/api/todos/{id}',         'TodoController@show');
    $router->post('/api/todos',             'TodoController@create');
    $router->patch('/api/todos/{id}',       'TodoController@update');
    $router->delete('/api/todos/{id}',      'TodoController@delete');
    $router->post('/api/todos/{id}/done',   'TodoController@markDone');
    $router->post('/api/todos/{id}/reopen', 'TodoController@reopen');
    $router->post('/api/todos/{id}/snooze', 'TodoController@snooze');

    // v11.0.0 — To-Do lists
    $router->get('/api/todo-lists',                  'TodoListController@index');
    $router->post('/api/todo-lists/reorder',         'TodoListController@reorder');
    $router->get('/api/todo-lists/{id}',             'TodoListController@show');
    $router->post('/api/todo-lists',                 'TodoListController@create');
    $router->patch('/api/todo-lists/{id}',           'TodoListController@update');
    $router->post('/api/todo-lists/{id}/archive',    'TodoListController@archive');
    $router->post('/api/todo-lists/{id}/restore',    'TodoListController@restore');
    $router->delete('/api/todo-lists/{id}',          'TodoListController@delete');

    // v11.0.0 — Quick notes
    $router->get('/api/quick-notes',              'QuickNoteController@index');
    $router->post('/api/quick-notes',             'QuickNoteController@create');
    $router->get('/api/quick-notes/{id}',         'QuickNoteController@show');
    $router->patch('/api/quick-notes/{id}',       'QuickNoteController@update');
    $router->delete('/api/quick-notes/{id}',      'QuickNoteController@delete');
    $router->post('/api/quick-notes/{id}/pin',    'QuickNoteController@pin');
    $router->post('/api/quick-notes/{id}/unpin',  'QuickNoteController@unpin');

    // v11.0.0 — Note templates
    $router->get('/api/note-templates',                    'NoteTemplateController@index');
    $router->post('/api/note-templates',                   'NoteTemplateController@create');
    $router->post('/api/note-templates/reorder',           'NoteTemplateController@reorder');
    $router->post('/api/note-templates/seed-defaults',     'NoteTemplateController@seedDefaults');
    $router->get('/api/note-templates/{id}',               'NoteTemplateController@show');
    $router->patch('/api/note-templates/{id}',             'NoteTemplateController@update');
    $router->delete('/api/note-templates/{id}',            'NoteTemplateController@delete');
    $router->post('/api/note-templates/{id}/used',         'NoteTemplateController@markUsed');

    // v11 — Medical instruction templates + per-appointment instructions
    $router->get('/api/instruction-templates/suggestions',                    'MedicalInstructionController@suggestions');
    $router->get('/api/instruction-templates',                                'MedicalInstructionController@indexTemplates');
    $router->post('/api/instruction-templates',                               'MedicalInstructionController@createTemplate');
    $router->get('/api/instruction-templates/{id}',                           'MedicalInstructionController@showTemplate');
    $router->patch('/api/instruction-templates/{id}',                         'MedicalInstructionController@updateTemplate');
    $router->delete('/api/instruction-templates/{id}',                          'MedicalInstructionController@deleteTemplate');
    $router->get('/api/appointments/{id}/medical-instructions',               'MedicalInstructionController@indexAppointment');
    $router->post('/api/appointments/{id}/medical-instructions',              'MedicalInstructionController@createAppointment');
    $router->patch('/api/appointments/{id}/medical-instructions/{instId}',    'MedicalInstructionController@updateAppointment');
    $router->delete('/api/appointments/{id}/medical-instructions/{instId}',   'MedicalInstructionController@deleteAppointment');

    // v11.0.0 — Activity, Cmd+K, hover-card, settings extensions
    $router->get('/api/activity',                       'ActivityController@feed');
    $router->get('/api/search/palette',                 'SearchController@palette');
    $router->get('/api/patients/{id}/summary',          'PatientSummaryController@summary');
    $router->get('/api/settings/appearance',            'SettingsControllerV11@getAppearance');
    $router->post('/api/settings/theme-palette',        'SettingsControllerV11@setThemePalette');
    $router->post('/api/settings/theme-auto-schedule',  'SettingsControllerV11@setThemeAutoSchedule');
    
    // Doctor Forum routes
    
    
    
    
    
    
    
    
    
    // Medical History routes
    $router->get('/api/patients/{id}/medical-history', 'ApiController@getPatientMedicalHistory');
    $router->post('/api/patients/{id}/medical-history', 'ApiController@createMedicalHistory');
    $router->get('/api/patients/{id}/medical-history/{historyId}', 'ApiController@getMedicalHistoryEntry');
    $router->put('/api/patients/{id}/medical-history/{historyId}', 'ApiController@updateMedicalHistory');
    $router->delete('/api/patients/{id}/medical-history/{historyId}', 'ApiController@deleteMedicalHistory');
    
    // Ophthalmology News routes
    $router->get('/api/ophthalmology-news', 'ApiController@getOphthalmologyNews');

    // AI Chat API routes
    $router->post('/api/ai/chat', 'ApiController@chatWithAI');
    $router->get('/api/ai/chat/history', 'ApiController@getChatHistory');
    $router->delete('/api/ai/chat/history', 'ApiController@clearChatHistory');

    // Weather API route
    $router->get('/api/weather', 'ApiController@getWeather');
    $router->get('/api/weather-forecast', 'ApiController@getWeatherForecast');
    $router->get('/api/weather-ar', 'ApiController@getWeatherArabic');
    $router->get('/api/weather-forecast-ar', 'ApiController@getWeatherForecastArabic');

    // General routes
    $router->get('/about', 'GeneralController@about');
    $router->get('/whats-new', 'GeneralController@whatsNew');
    
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
    $router->get('/api/clinics', 'ApiController@getClinics');
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
    $router->post('/api/appointments/{id}/edit', 'ApiController@editAppointmentFields');
    $router->post('/api/appointments/{id}/correct-visit-type', 'ApiController@correctVisitType');
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
    $router->get('/api/test-export', 'ApiController@testExport');
    $router->get('/api/simple-export', 'ApiController@simpleExport');
    $router->get('/api/dashboard-summary', 'ApiController@getDashboardSummary');

    // Patient Board — two-level (overview → detail) + legacy single-Kanban. See BoardController.
    $router->get('/api/board/boards',                          'BoardController@listBoards');
    $router->post('/api/board/boards',                         'BoardController@createBoard');
    $router->put('/api/board/boards/{id}',                     'BoardController@updateBoard');
    $router->delete('/api/board/boards/{id}',                  'BoardController@deleteBoard');
    $router->get('/api/board/boards/{id}/cards',               'BoardController@boardCards');
    $router->post('/api/board/boards/{id}/patients',           'BoardController@addPatient');
    $router->delete('/api/board/boards/{id}/patients/{pid}',   'BoardController@removePatient');
    $router->put('/api/board/patients/{pid}',                  'BoardController@quickEditPatient');
    $router->get('/api/board/snapshot',                 'BoardController@snapshot');
    $router->get('/api/board/columns',                  'BoardController@listColumns');
    $router->post('/api/board/columns',                 'BoardController@createColumn');
    $router->put('/api/board/columns/{id}',             'BoardController@updateColumn');
    $router->delete('/api/board/columns/{id}',          'BoardController@deleteColumn');
    $router->get('/api/board/cards',                    'BoardController@listCards');
    $router->post('/api/board/move',                    'BoardController@move');
    $router->get('/api/board/auto-place/{patient_id}',  'BoardController@autoPlace');

    // Generic comments + @-mentions + attachments — see CommentsController.
    // Specific routes first so /api/comments/attachments/{id} isn't captured
    // by the generic /api/comments/{type}/{id} pattern.
    $router->get('/api/users/search',                   'CommentsController@searchUsers');
    $router->post('/api/comments/attachments',          'CommentsController@uploadAttachment');
    $router->get('/api/comments/attachments/{id}',      'CommentsController@viewAttachment');
    $router->get('/api/comments/{type}/{id}',           'CommentsController@listFor');
    $router->post('/api/comments/{type}/{id}',          'CommentsController@create');
    $router->patch('/api/comments/{id}',                'CommentsController@patch');
    $router->delete('/api/comments/{id}',               'CommentsController@delete');

    $router->get('/api/upcoming-appointments', 'ApiController@getUpcomingAppointments');
    $router->get('/api/missed-appointments', 'ApiController@getMissedAppointments');
    $router->get('/api/recent-activity', 'ApiController@getRecentActivity');
    $router->get('/api/dashboard-charts', 'ApiController@getDashboardCharts');
    $router->get('/api/patients/search', 'ApiController@searchPatients');
    $router->get('/api/patients', 'ApiController@getAllPatients');
    // Patient folders routes
    // More specific routes first to avoid conflicts
    // Bulk operations must come BEFORE routes with {id} parameter
    $router->delete("/api/patient-folders/bulk", "ApiController@bulkDeletePatientFolders");
    $router->get("/api/patient-folders/{parentId}/sub-folders/{parentType}", "ApiController@getSubFolders");
    $router->post("/api/patient-folders/{systemFolderId}/quick-sort/{sortType}", "ApiController@quickSortSystemFolder");
    $router->get("/api/patient-folders/{id}/patients", "ApiController@getFolderPatients");
    $router->post("/api/patient-folders/{id}/patients", "ApiController@addPatientToFolder");
    $router->delete("/api/patient-folders/{id}/patients/{patient_id}", "ApiController@removePatientFromFolder");
    // General folder routes (must come after specific routes)
    $router->get("/api/patient-folders", "ApiController@getPatientFolders");
    $router->post("/api/patient-folders", "ApiController@createPatientFolder");
    $router->put("/api/patient-folders/{id}", "ApiController@updatePatientFolder");
    $router->delete("/api/patient-folders/{id}", "ApiController@deletePatientFolder");
    
    // Patient color markers routes
    $router->post('/api/patient-color-markers/batch', 'ApiController@getBatchPatientColorMarkers');
    $router->get('/api/patient-color-markers/{patient_id}', 'ApiController@getPatientColorMarker');
    $router->put('/api/patient-color-markers/{patient_id}', 'ApiController@updatePatientColorMarker');
    $router->delete('/api/patient-color-markers/{patient_id}', 'ApiController@deletePatientColorMarker');

    // Patient tags routes
    $router->get('/api/patient-tags', 'ApiController@getPatientTags');
    $router->post('/api/patient-tags', 'ApiController@createPatientTag');
    $router->put('/api/patient-tags/{id}', 'ApiController@updatePatientTag');
    $router->delete('/api/patient-tags/{id}', 'ApiController@deletePatientTag');

    // Patient tag assignments routes (batch route must come before parameterized routes)
    $router->post('/api/patients/tags/batch', 'ApiController@getBatchPatientTags');
    $router->get('/api/patients/{patient_id}/tags', 'ApiController@getPatientAssignedTags');
    $router->post('/api/patients/{patient_id}/tags/{tag_id}', 'ApiController@assignTagToPatient');
    $router->delete('/api/patients/{patient_id}/tags/{tag_id}', 'ApiController@removeTagFromPatient');
    
    // More specific routes first - images must come before {id}
    $router->get('/api/patients/images/{id}', 'ApiController@viewPatientImageForCards');
    $router->get('/api/patients/{id}/files', 'ApiController@getPatientFiles');
    $router->get('/api/patients/{id}/timeline', 'ApiController@getPatientTimeline');
    $router->get('/api/patients/{id}/appointments/check-active', 'ApiController@checkPatientActiveAppointments');
    $router->get('/api/patients/{id}/medical-record', 'ApiController@getPatientMedicalRecord');
    $router->head('/api/patients/{id}/medical-record', 'ApiController@checkMedicalRecordAccess');
    $router->get('/api/patients/{id}/export', 'ApiController@exportPatientData');
    $router->head('/api/patients/{id}/export', 'ApiController@checkExportAccess');
    $router->get('/api/patients/{id}', 'ApiController@getPatient');
    $router->post('/api/patients', 'ApiController@createPatient');
    $router->delete('/api/patients/{id}', 'ApiController@deletePatient');
    $router->put('/api/patients/{id}/emergency-contact', 'ApiController@updateEmergencyContact');
    $router->post('/api/consultations', 'ApiController@createConsultation');
    // Consultation autocomplete + common-cases modal: the methods existed
    // but the routes were never registered, so edit_consultation.js's
    // autocomplete and "Common Cases" modal 404'd in production.
    $router->get('/api/consultation/suggestions', 'ApiController@getConsultationSuggestions');
    $router->get('/api/consultation/common-complaints', 'ApiController@getCommonComplaints');
    // Smart-consultation AI assists (Phase 1).
    $router->get('/api/consultation/prior-summary', 'ApiController@getPriorVisitSummary');
    $router->post('/api/consultation/icd10-suggest', 'ApiController@suggestICD10Codes');
    $router->get('/api/prescriptions/suggestions', 'ApiController@getPrescriptionSuggestions');
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
    // Per-doctor drug defaults (saved route + instructions per drug)
    $router->get('/api/drug-defaults', 'ApiController@getDrugDefault');
    $router->post('/api/drug-defaults', 'ApiController@saveDrugDefault');
    $router->delete('/api/drug-defaults', 'ApiController@deleteDrugDefault');
    
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
    $router->post('/api/attachments/replace/{id}', 'ApiController@replaceAttachment');
    $router->post('/api/attachments/bulk-delete', 'ApiController@bulkDeleteAttachments');
    $router->get('/api/attachments/view/{id}', 'ApiController@viewAttachment');
    $router->get('/api/attachments/download/{id}', 'ApiController@downloadAttachment');
    $router->delete('/api/attachments/{id}', 'ApiController@deleteAttachment');
    
    // Patient Files API routes
    $router->post('/api/patients/files/upload', 'ApiController@uploadPatientFile');
    $router->post('/api/patients/files/replace/{id}', 'ApiController@replacePatientFile');
    $router->post('/api/patients/files/bulk-delete', 'ApiController@bulkDeletePatientFiles');
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
        
    // Print routes
    $router->get('/print/prescription/{id}', 'PrintController@medicationPrescription');
    $router->get('/print/glasses/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/glasses-prescription/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/lab-test/{id}', 'PrintController@singleLabTest');
    $router->get('/print/lab-tests/{id}', 'PrintController@labTests');
    $router->get('/print/invoice/{id}', 'PrintController@invoice');
    $router->get('/print/appointment/{id}', 'PrintController@appointmentReport');
    
    // Payment and Expense API routes
    $router->get('/api/payments/{id}', 'ApiController@getPayment');
    $router->get('/api/expenses/{id}', 'ApiController@getExpense');
    
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

