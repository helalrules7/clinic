<?php
/**
 * Test Script: Send Push Notification to All Subscribers
 * 
 * Usage: php app/Scripts/test_push_notifications.php
 * 
 * This script sends a test push notification to all users who have enabled push notifications
 */

// Set timezone
date_default_timezone_set('Africa/Cairo');

// Load environment variables
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Load Composer autoloader
$vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    die("❌ Error: vendor/autoload.php not found. Please run: composer install\n");
}

// Load required classes
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Services/PushNotificationService.php';

use App\Config\Database;
use App\Services\PushNotificationService;

echo "🧪 Test Push Notifications Script\n";
echo "================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $pushService = new PushNotificationService();
    
    // Get all users with push notifications enabled
    $stmt = $db->query("
        SELECT DISTINCT user_id 
        FROM doctor_settings 
        WHERE setting_key = 'push_notifications_enabled' 
        AND setting_value IN ('1', 'true', 'True', 'TRUE')
    ");
    $users = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    
    if (empty($users)) {
        echo "⚠️  No users with push notifications enabled found.\n";
        echo "   Please enable push notifications in the browser first.\n";
        exit(0);
    }
    
    echo "📊 Found " . count($users) . " user(s) with push notifications enabled.\n\n";
    
    $totalSent = 0;
    $totalFailed = 0;
    
    foreach ($users as $userId) {
        echo "👤 Processing user ID: $userId\n";
        
        // Get subscriptions for this user
        $subscriptions = $pushService->getDoctorSubscriptions($userId);
        
        if (empty($subscriptions)) {
            echo "   ⚠️  No subscriptions found for this user.\n\n";
            continue;
        }
        
        echo "   📱 Found " . count($subscriptions) . " subscription(s)\n";
        
        // Send test notification
        $title = "🧪 Test Notification";
        $body = "This is a test push notification sent at " . date('Y-m-d H:i:s');
        $data = [
            'test' => true,
            'timestamp' => time(),
            'url' => '/doctor/alerts'
        ];
        
        echo "   📤 Sending test notification...\n";
        
        $result = $pushService->sendPushNotification($userId, $title, $body, $data);
        
        if ($result) {
            echo "   ✅ Notification sent successfully!\n";
            $totalSent++;
        } else {
            echo "   ❌ Failed to send notification\n";
            $totalFailed++;
        }
        
        echo "\n";
    }
    
    echo "================================\n";
    echo "📊 Summary:\n";
    echo "   ✅ Successfully sent: $totalSent\n";
    echo "   ❌ Failed: $totalFailed\n";
    echo "   👥 Total users: " . count($users) . "\n";
    echo "\n";
    echo "🎉 Test completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

