#!/bin/bash

echo "🔥 الحل الشامل لجميع مشاكل roaya.ahmedhelal.dev"
echo "================================================"

# تحديد المسار الصحيح للسيرفر
TARGET_PATH="/home/AhmedHelal/web/roaya.ahmedhelal.dev/public_html"

echo "1️⃣ إصلاح index.php (Router::dispatch)"
echo "================================="

cat > "$TARGET_PATH/index.php" << 'INDEX_PHP_END'
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

// Load environment variables (مع fallback للقيم الافتراضية)
if (file_exists(__DIR__ . '/.env')) {
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

// Set default environment values if not loaded
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'localhost';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'AhmedHelal_roaya';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'AhmedHelal_roaya';
$_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? 'Carmen@1230';
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'production';
$_ENV['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? 'false';

// Set error reporting based on environment
if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING);
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
    
    // ✅ FIXED: استخدم dispatch() بدلاً من handle()
    $router->dispatch();
    
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    
    if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
        echo "<h1>Application Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>خطأ في النظام</h1>";
        echo "<p>يرجى المحاولة مرة أخرى أو الاتصال بالدعم الفني.</p>";
    }
}
INDEX_PHP_END

echo "✅ تم إصلاح index.php"

echo ""
echo "2️⃣ إنشاء ملف .env محسن"
echo "====================="

cat > "$TARGET_PATH/.env" << 'ENV_END'
APP_ENV=production
APP_DEBUG=false

# Database Configuration
DB_HOST=localhost
DB_NAME=AhmedHelal_roaya
DB_USER=AhmedHelal_roaya
DB_PASS=Carmen@1230

# Security Keys
SESSION_SECRET=roaya-session-secret-key-2024-32-chars
CSRF_SECRET=roaya-csrf-secret-key-2024-32-chars
APP_KEY=roaya-clinic-system-2024-secret-key-32

# Application Settings
TIMEZONE=Africa/Cairo
LOG_LEVEL=info
LOG_FILE=storage/logs/app.log

# Feature Flags
ENABLE_DEBUG_LOGGING=false
MAINTENANCE_MODE=false
ENV_END

echo "✅ تم إنشاء ملف .env"

echo ""
echo "3️⃣ إنشاء .htaccess محسن"
echo "===================="

cat > "$TARGET_PATH/.htaccess" << 'HTACCESS_END'
RewriteEngine On

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# Remove www if present
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

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
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# Prevent access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.*">
    Order allow,deny
    Deny from all
</Files>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>
HTACCESS_END

echo "✅ تم إنشاء .htaccess"

echo ""
echo "4️⃣ ضبط الصلاحيات والمجلدات"
echo "========================"

# ضبط صلاحيات الملفات الأساسية
chmod 755 "$TARGET_PATH"
chmod 644 "$TARGET_PATH/index.php"
chmod 644 "$TARGET_PATH/.htaccess"
chmod 600 "$TARGET_PATH/.env"

# إنشاء مجلدات storage مع الصلاحيات الصحيحة
mkdir -p "$TARGET_PATH/storage/logs"
mkdir -p "$TARGET_PATH/storage/uploads"
mkdir -p "$TARGET_PATH/storage/exports"
mkdir -p "$TARGET_PATH/storage/cache"
mkdir -p "$TARGET_PATH/storage/sessions"

chmod -R 755 "$TARGET_PATH/storage"
chmod -R 644 "$TARGET_PATH/storage/logs"

# إنشاء ملف log أولي
touch "$TARGET_PATH/storage/logs/app.log"
chmod 644 "$TARGET_PATH/storage/logs/app.log"

# ضبط صلاحيات المجلدات المهمة
if [ -d "$TARGET_PATH/app" ]; then
    chmod -R 755 "$TARGET_PATH/app"
fi

if [ -d "$TARGET_PATH/public" ]; then
    chmod -R 755 "$TARGET_PATH/public"
fi

if [ -d "$TARGET_PATH/vendor" ]; then
    chmod -R 755 "$TARGET_PATH/vendor"
fi

echo "✅ تم ضبط الصلاحيات"

echo ""
echo "5️⃣ اختبار الاتصال بقاعدة البيانات"
echo "==============================="

cat > "$TARGET_PATH/test-db.php" << 'TEST_DB_END'
<?php
// Test database connection
require_once __DIR__ . '/vendor/autoload.php';

// Load .env
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'AhmedHelal_roaya';
$username = $_ENV['DB_USER'] ?? 'AhmedHelal_roaya';
$password = $_ENV['DB_PASS'] ?? 'Carmen@1230';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password);
    echo "✅ Database connection successful!<br>";
    echo "📊 Connected to: {$dbname}@{$host}<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$dbname}'");
    $result = $stmt->fetch();
    echo "📋 Tables found: " . $result['table_count'] . "<br>";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "🔧 Check your database credentials in .env file<br>";
}
TEST_DB_END

echo "✅ تم إنشاء ملف اختبار قاعدة البيانات"

echo ""
echo "🎉 الحل الشامل مكتمل!"
echo "===================="
echo ""
echo "✅ التغييرات المطبقة:"
echo "- إصلاح Router::dispatch()"
echo "- ملف .env محسن مع fallback values"
echo "- .htaccess محسن مع security headers"
echo "- مجلدات storage مضبوطة"
echo "- صلاحيات مضبوطة"
echo "- ملف اختبار قاعدة البيانات"
echo ""
echo "�� اختبر الموقع الآن:"
echo "- https://roaya.ahmedhelal.dev/"
echo "- https://roaya.ahmedhelal.dev/login"
echo "- https://roaya.ahmedhelal.dev/test-db.php (لاختبار قاعدة البيانات)"
echo ""
echo "🗑️ احذف ملف الاختبار بعد التأكد:"
echo "rm $TARGET_PATH/test-db.php"
echo ""
