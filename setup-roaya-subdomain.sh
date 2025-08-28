#!/bin/bash

echo "🚀 إعداد roaya.ahmedhelal.dev مع قاعدة البيانات الصحيحة"
echo "================================================="

# تحديد المسار الصحيح للسيرفر
TARGET_PATH="/home/AhmedHelal/web/roaya.ahmedhelal.dev/public_html"

echo "📁 إنشاء ملف .env بإعدادات قاعدة البيانات الصحيحة..."

cat > "$TARGET_PATH/.env" << 'ENV_END'
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
ENV_END

echo "✅ تم إنشاء ملف .env"

echo ""
echo "📁 إنشاء index.php في الجذر..."

cat > "$TARGET_PATH/index.php" << 'INDEX_END'
<?php
/**
 * Roaya Clinic Management System
 * Main entry point for subdomain
 */

// Set timezone
date_default_timezone_set('Africa/Cairo');

// Start session
session_start();

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

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
    
    // Doctor routes
    $router->get('/doctor/dashboard', 'DoctorController@dashboard');
    $router->get('/doctor/calendar', 'DoctorController@calendar');
    $router->get('/doctor/patients', 'DoctorController@patients');
    $router->get('/doctor/patients/{id}', 'DoctorController@showPatient');
    $router->get('/doctor/appointments/{id}', 'DoctorController@viewAppointment');
    $router->get('/doctor/appointments/{id}/edit', 'DoctorController@editConsultation');
    $router->post('/doctor/appointments/{id}/consultation', 'DoctorController@saveConsultation');
    $router->get('/doctor/profile', 'DoctorController@profile');
    $router->post('/doctor/profile', 'DoctorController@updateProfile');
    
    // Secretary routes
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
    
    // Print routes
    $router->get('/print/prescription/{id}', 'PrintController@medicationPrescription');
    $router->get('/print/glasses/{id}', 'PrintController@glassesPrescription');
    $router->get('/print/lab-test/{id}', 'PrintController@singleLabTest');
    $router->get('/print/lab-tests/{id}', 'PrintController@labTests');
    $router->get('/print/invoice/{id}', 'PrintController@invoice');
    $router->get('/print/appointment/{id}', 'PrintController@appointmentReport');
    
    // Handle the request
    $router->handle();
    
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    
    if (($_ENV['APP_ENV'] ?? 'local') === 'local') {
        echo "<h1>Application Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Something went wrong</h1>";
        echo "<p>Please try again later.</p>";
    }
}
INDEX_END

echo "✅ تم إنشاء index.php"

echo ""
echo "📁 إنشاء .htaccess..."

cat > "$TARGET_PATH/.htaccess" << 'HTACCESS_END'
RewriteEngine On

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# Send ALL requests to index.php
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [L,QSA]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Prevent access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>
HTACCESS_END

echo "✅ تم إنشاء .htaccess"

echo ""
echo "🔒 ضبط الصلاحيات..."

chmod 755 "$TARGET_PATH"
chmod 644 "$TARGET_PATH/index.php"
chmod 644 "$TARGET_PATH/.htaccess"
chmod 600 "$TARGET_PATH/.env"

# إنشاء مجلدات storage إذا لم تكن موجودة
mkdir -p "$TARGET_PATH/storage/logs"
mkdir -p "$TARGET_PATH/storage/uploads"
mkdir -p "$TARGET_PATH/storage/exports"
chmod -R 755 "$TARGET_PATH/storage"

echo "✅ تم ضبط الصلاحيات"

echo ""
echo "🎉 تم الانتهاء من الإعداد!"
echo "========================="
echo ""
echo "📋 ملخص الإعدادات:"
echo "- المسار: $TARGET_PATH"
echo "- قاعدة البيانات: AhmedHelal_roaya"
echo "- المستخدم: AhmedHelal_roaya"
echo "- البيئة: production"
echo ""
echo "🔗 اختبر الموقع:"
echo "- https://roaya.ahmedhelal.dev/"
echo "- https://roaya.ahmedhelal.dev/login"
echo ""
echo "📊 إذا لم يعمل، تحقق من:"
echo "- tail -f /home/AhmedHelal/web/roaya.ahmedhelal.dev/logs/roaya.ahmedhelal.dev.error.log"
echo "- وجود جميع ملفات المشروع (app/, vendor/, sql/)"
echo "- إعدادات قاعدة البيانات"
echo ""
