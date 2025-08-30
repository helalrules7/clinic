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
