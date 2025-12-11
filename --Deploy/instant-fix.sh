#!/bin/bash

echo "🚨 إصلاح فوري لمشاكل roaya.ahmedhelal.dev"
echo "====================================="

TARGET_PATH="/home/AhmedHelal/web/roaya.ahmedhelal.dev/public_html"

echo "1️⃣ إصلاح صلاحيات ملف .env"
echo "========================"

# إنشاء ملف .env بالصلاحيات الصحيحة
cat > "$TARGET_PATH/.env" << 'ENV_EOF'
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_NAME=AhmedHelal_roaya
DB_USER=AhmedHelal_roaya
DB_PASS=Carmen@1230

SESSION_SECRET=roaya-session-secret-key-2024-32-chars
CSRF_SECRET=roaya-csrf-secret-key-2024-32-chars
APP_KEY=roaya-clinic-system-2024-secret-key-32

TIMEZONE=Africa/Cairo
LOG_LEVEL=info
LOG_FILE=storage/logs/app.log
ENV_EOF

# ضبط صلاحيات ملف .env
chmod 644 "$TARGET_PATH/.env"
chown $(stat -c "%U:%G" "$TARGET_PATH") "$TARGET_PATH/.env"

echo "✅ تم إصلاح ملف .env"

echo ""
echo "2️⃣ إصلاح index.php بالكامل"
echo "========================="

cat > "$TARGET_PATH/index.php" << 'INDEX_EOF'
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
    
    // Routes
    $router->get('/', 'AuthController@showLogin');
    $router->get('/login', 'AuthController@showLogin');
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');
    
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
    
    $router->get('/doctor/dashboard', 'DoctorController@dashboard');
    $router->get('/doctor/calendar', 'DoctorController@calendar');
    $router->get('/doctor/patients', 'DoctorController@patients');
    $router->get('/doctor/patients/{id}', 'DoctorController@showPatient');
    $router->get('/doctor/appointments/{id}', 'DoctorController@viewAppointment');
    $router->get('/doctor/appointments/{id}/edit', 'DoctorController@editConsultation');
    $router->post('/doctor/appointments/{id}/consultation', 'DoctorController@saveConsultation');
    $router->get('/doctor/profile', 'DoctorController@profile');
    $router->post('/doctor/profile', 'DoctorController@updateProfile');
    
    $router->get('/secretary/dashboard', 'SecretaryController@dashboard');
    $router->get('/secretary/bookings', 'SecretaryController@bookings');
    $router->post('/secretary/bookings', 'SecretaryController@createBooking');
    $router->put('/secretary/bookings/{id}', 'SecretaryController@updateBooking');
    $router->delete('/secretary/bookings/{id}', 'SecretaryController@deleteBooking');
    
    // API routes
    $router->get('/api/calendar', 'ApiController@getCalendar');
    $router->post('/api/appointments', 'ApiController@createAppointment');
    $router->put('/api/appointments/{id}', 'ApiController@updateAppointment');
    $router->delete('/api/appointments/{id}', 'ApiController@deleteAppointment');
    $router->get('/api/patients/search', 'ApiController@searchPatients');
    $router->post('/api/patients', 'ApiController@createPatient');
    $router->get('/api/patients/{id}/timeline', 'ApiController@getPatientTimeline');
    $router->put('/api/patients/{id}/emergency-contact', 'ApiController@updateEmergencyContact');
    $router->post('/api/consultations', 'ApiController@createConsultation');
    $router->post('/api/prescriptions/meds', 'ApiController@createMedicationPrescription');
    $router->put('/api/prescriptions/meds/{id}', 'ApiController@updateMedication');
    $router->delete('/api/prescriptions/meds/{id}', 'ApiController@deleteMedication');
    $router->post('/api/prescriptions/glasses', 'ApiController@createGlassesPrescription');
    $router->put('/api/prescriptions/glasses/{id}', 'ApiController@updateGlassesPrescription');
    $router->delete('/api/prescriptions/glasses/{id}', 'ApiController@deleteGlassesPrescription');
    
    $router->post('/api/lab-tests', 'ApiController@createLabTest');
    $router->put('/api/lab-tests/{id}', 'ApiController@updateLabTest');
    $router->delete('/api/lab-tests/{id}', 'ApiController@deleteLabTest');
    $router->get('/api/lab-tests/appointment/{id}', 'ApiController@getLabTests');
    
    $router->post('/api/daily-closure/lock', 'ApiController@lockDailyClosure');
    $router->post('/api/users/change-password', 'ApiController@changePassword');
    
    $router->post('/api/attachments/upload', 'ApiController@uploadAttachment');
    $router->get('/api/attachments/view/{id}', 'ApiController@viewAttachment');
    $router->get('/api/attachments/download/{id}', 'ApiController@downloadAttachment');
    $router->delete('/api/attachments/{id}', 'ApiController@deleteAttachment');
    
    $router->get('/print/prescription/{id}', 'PrintController@medicationPrescription');
    $router->get('/print/glasses/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/lab-test/{id}', 'PrintController@singleLabTest');
    $router->get('/print/lab-tests/{id}', 'PrintController@labTests');
    $router->get('/print/invoice/{id}', 'PrintController@invoice');
    $router->get('/print/appointment/{id}', 'PrintController@appointmentReport');
    
    // ✅ FIXED: استخدام dispatch() بدلاً من handle()
    $router->dispatch();
    
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>خطأ في النظام</h1><p>يرجى المحاولة مرة أخرى.</p>";
}
INDEX_EOF

chmod 644 "$TARGET_PATH/index.php"

echo "✅ تم إصلاح index.php"

echo ""
echo "3️⃣ إنشاء .htaccess"
echo "================="

cat > "$TARGET_PATH/.htaccess" << 'HTACCESS_EOF'
RewriteEngine On

RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [L,QSA]

<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

<Files ".env">
    Order allow,deny
    Deny from all
</Files>
HTACCESS_EOF

chmod 644 "$TARGET_PATH/.htaccess"

echo "✅ تم إنشاء .htaccess"

echo ""
echo "4️⃣ إنشاء المجلدات الضرورية"
echo "========================"

mkdir -p "$TARGET_PATH/storage/logs"
mkdir -p "$TARGET_PATH/storage/uploads"
mkdir -p "$TARGET_PATH/storage/cache"
chmod -R 755 "$TARGET_PATH/storage"

echo "✅ تم إنشاء المجلدات"

echo ""
echo "🎉 الإصلاح الفوري مكتمل!"
echo "======================"
echo ""
echo "🔗 اختبر الموقع:"
echo "https://roaya.ahmedhelal.dev/"
echo "https://roaya.ahmedhelal.dev/login"
echo ""
