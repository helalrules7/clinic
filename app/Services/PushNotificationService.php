<?php

namespace App\Services;

use App\Config\Database;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    private $pdo;
    private $vapidPublicKey;
    private $vapidPrivateKey;
    
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        
        // Load VAPID keys from environment
        $this->vapidPublicKey = $_ENV['VAPID_PUBLIC_KEY'] ?? getenv('VAPID_PUBLIC_KEY');
        $this->vapidPrivateKey = $_ENV['VAPID_PRIVATE_KEY'] ?? getenv('VAPID_PRIVATE_KEY');
        
        // If not in env, try to load from .env file
        if (!$this->vapidPublicKey || !$this->vapidPrivateKey) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                        list($key, $value) = explode('=', $line, 2);
                        if ($key === 'VAPID_PUBLIC_KEY') {
                            $this->vapidPublicKey = trim($value);
                        } elseif ($key === 'VAPID_PRIVATE_KEY') {
                            $this->vapidPrivateKey = trim($value);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Get all push subscriptions for a doctor
     */
    public function getDoctorSubscriptions($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_value 
                FROM doctor_settings 
                WHERE user_id = ? AND setting_key = 'push_subscription'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result && $result['setting_value']) {
                $subscriptions = json_decode($result['setting_value'], true);
                // Handle backward compatibility: if it's a single subscription, convert to array
                if (!is_array($subscriptions) && isset($subscriptions['endpoint'])) {
                    return [$subscriptions];
                }
                return is_array($subscriptions) ? $subscriptions : [];
            }
            
            return [];
        } catch (\Exception $e) {
            error_log("Error getting doctor subscriptions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send push notification to all doctor's subscriptions
     */
    public function sendPushNotification($userId, $title, $body, $data = [])
    {
        if (!$this->vapidPublicKey || !$this->vapidPrivateKey) {
            error_log("VAPID keys not configured");
            return false;
        }
        
        $subscriptions = $this->getDoctorSubscriptions($userId);
        
        if (empty($subscriptions)) {
            return false;
        }
        
        $auth = [
            'VAPID' => [
                'subject' => $_SERVER['HTTP_HOST'] ?? 'https://roaya.hclinic.clinic',
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ],
        ];
        
        $webPush = new WebPush($auth);
        
        $successCount = 0;
        $failedSubscriptions = [];
        
        foreach ($subscriptions as $subscriptionData) {
            try {
                // Convert subscription data to Subscription object
                $subscription = Subscription::create([
                    'endpoint' => $subscriptionData['endpoint'] ?? '',
                    'keys' => [
                        'p256dh' => $subscriptionData['keys']['p256dh'] ?? '',
                        'auth' => $subscriptionData['keys']['auth'] ?? ''
                    ]
                ]);
                
                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'icon' => '/assets/images/Light.png',
                    'badge' => '/assets/images/Light.png',
                    'data' => $data
                ]);
                
                $result = $webPush->sendOneNotification($subscription, $payload);
                
                if ($result->isSuccess()) {
                    $successCount++;
                } else {
                    // Subscription might be invalid, mark for removal
                    $failedSubscriptions[] = $subscriptionData;
                }
            } catch (\Exception $e) {
                error_log("Error sending push notification: " . $e->getMessage());
                $failedSubscriptions[] = $subscriptionData;
            }
        }
        
        // Remove failed subscriptions
        if (!empty($failedSubscriptions)) {
            $this->removeFailedSubscriptions($userId, $failedSubscriptions);
        }
        
        return $successCount > 0;
    }
    
    /**
     * Remove failed/invalid subscriptions
     */
    private function removeFailedSubscriptions($userId, $failedSubscriptions)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_value 
                FROM doctor_settings 
                WHERE user_id = ? AND setting_key = 'push_subscription'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result && $result['setting_value']) {
                $subscriptions = json_decode($result['setting_value'], true);
                
                // Handle backward compatibility
                if (!is_array($subscriptions) && isset($subscriptions['endpoint'])) {
                    $subscriptions = [$subscriptions];
                }
                
                if (is_array($subscriptions)) {
                    // Remove failed subscriptions
                    $failedEndpoints = array_map(function($sub) {
                        return $sub['endpoint'] ?? '';
                    }, $failedSubscriptions);
                    
                    $validSubscriptions = array_filter($subscriptions, function($sub) use ($failedEndpoints) {
                        $endpoint = is_array($sub) ? ($sub['endpoint'] ?? '') : '';
                        return !in_array($endpoint, $failedEndpoints);
                    });
                    
                    // Update subscriptions
                    $stmt = $this->pdo->prepare("
                        UPDATE doctor_settings 
                        SET setting_value = ? 
                        WHERE user_id = ? AND setting_key = 'push_subscription'
                    ");
                    $stmt->execute([json_encode(array_values($validSubscriptions)), $userId]);
                }
            }
        } catch (\Exception $e) {
            error_log("Error removing failed subscriptions: " . $e->getMessage());
        }
    }
}

