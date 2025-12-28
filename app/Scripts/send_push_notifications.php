<?php
/**
 * Cron Job Script: Send Push Notifications for Active Alerts
 * 
 * This script should be run via cron:
 * 
 * Every minute:
 *   Add to crontab: * * * * * /usr/bin/php /path/to/app/Scripts/send_push_notifications.php
 * 
 * Or every 5 minutes:
 *   Add to crontab: 0,5,10,15,20,25,30,35,40,45,50,55 * * * * /usr/bin/php /path/to/app/Scripts/send_push_notifications.php
 * 
 * Or every 1 minute (recommended):
 *   Add to crontab: * * * * * /usr/bin/php /path/to/app/Scripts/send_push_notifications.php
 * 
 * Note: Cron format is: minute hour day month weekday (5 fields)
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
}

// Load required classes
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/AlertModel.php';
require_once __DIR__ . '/../Services/PushNotificationService.php';

use App\Config\Database;
use App\Models\AlertModel;
use App\Services\PushNotificationService;

try {
    $db = Database::getInstance()->getConnection();
    $alertModel = new AlertModel();
    $pushService = new PushNotificationService();
    
    // Get current date and time
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');
    
    // Get all doctors
    $stmt = $db->query("SELECT DISTINCT doctor_id FROM alerts WHERE is_active = 1");
    $doctors = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    
    $sentCount = 0;
    $errorCount = 0;
    
    foreach ($doctors as $doctorId) {
        try {
            // Get active alerts for this doctor
            $alerts = $alertModel->getActiveAlertsForTime($doctorId, $currentDate, $currentTime);
            
            if (!empty($alerts)) {
                // Get user_id for this doctor
                $userStmt = $db->prepare("SELECT user_id FROM doctors WHERE id = ? LIMIT 1");
                $userStmt->execute([$doctorId]);
                $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($user && isset($user['user_id'])) {
                    $userId = $user['user_id'];
                    
                    // Check which alerts were already sent (using a simple file-based tracking)
                    $sentFile = __DIR__ . '/../../storage/push_sent_' . $userId . '.json';
                    $sentAlerts = [];
                    if (file_exists($sentFile)) {
                        $sentAlerts = json_decode(file_get_contents($sentFile), true) ?: [];
                    }
                    
                    foreach ($alerts as $alert) {
                        $alertKey = $alert['id'] . '_' . $alert['alert_date'] . '_' . $alert['alert_time'];
                        
                        // Check if already sent
                        if (!isset($sentAlerts[$alertKey])) {
                            // Send push notification
                            $patientName = '';
                            if (!empty($alert['patient_first_name']) && !empty($alert['patient_last_name'])) {
                                $patientName = $alert['patient_first_name'] . ' ' . $alert['patient_last_name'];
                            }
                            
                            $title = 'New Alert';
                            $body = $alert['message'] ?? 'You have a new alert';
                            if ($patientName) {
                                $body .= ' - ' . $patientName;
                            }
                            
                            $data = [
                                'alert_id' => $alert['id'],
                                'patient_id' => $alert['patient_id'] ?? null,
                                'url' => !empty($alert['patient_id']) 
                                    ? '/doctor/patients/' . $alert['patient_id'] 
                                    : '/doctor/alerts'
                            ];
                            
                            $result = $pushService->sendPushNotification($userId, $title, $body, $data);
                            
                            if ($result) {
                                $sentAlerts[$alertKey] = time();
                                $sentCount++;
                            } else {
                                $errorCount++;
                            }
                        }
                    }
                    
                    // Save sent alerts
                    if (!is_dir(__DIR__ . '/../../storage')) {
                        mkdir(__DIR__ . '/../../storage', 0755, true);
                    }
                    file_put_contents($sentFile, json_encode($sentAlerts));
                    
                    // Clean old entries (older than 24 hours)
                    $oneDayAgo = time() - (24 * 60 * 60);
                    $sentAlerts = array_filter($sentAlerts, function($timestamp) use ($oneDayAgo) {
                        return $timestamp > $oneDayAgo;
                    });
                    file_put_contents($sentFile, json_encode($sentAlerts));
                }
            }
        } catch (\Exception $e) {
            error_log("Error processing doctor $doctorId: " . $e->getMessage());
            $errorCount++;
        }
    }
    
    // Log results (optional)
    if ($sentCount > 0 || $errorCount > 0) {
        error_log("Push notifications sent: $sentCount, errors: $errorCount");
    }
    
} catch (\Exception $e) {
    error_log("Fatal error in send_push_notifications.php: " . $e->getMessage());
    exit(1);
}
