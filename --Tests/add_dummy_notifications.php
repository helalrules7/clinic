<?php
require_once __DIR__ . '/app/Config/Database.php';

use App\Config\Database;

$pdo = Database::getInstance()->getConnection();

// Create table if not exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        related_type VARCHAR(50) NULL,
        related_id INT NULL,
        patient_id INT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_is_read (is_read),
        INDEX idx_created_at (created_at),
        INDEX idx_patient_id (patient_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Add dummy notifications for user_id = 2
$notifications = [
    [
        'user_id' => 2,
        'type' => 'appointment',
        'title' => 'موعد جديد',
        'message' => 'تم إنشاء موعد جديد للمريض أحمد محمد',
        'related_type' => 'appointment',
        'related_id' => 1,
        'patient_id' => 1,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ],
    [
        'user_id' => 2,
        'type' => 'appointment',
        'title' => 'تذكير بالموعد',
        'message' => 'موعد قادم خلال ساعة واحدة',
        'related_type' => 'appointment',
        'related_id' => 2,
        'patient_id' => 2,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
    ],
    [
        'user_id' => 2,
        'type' => 'alert',
        'title' => 'تنبيه مهم',
        'message' => 'يوجد تنبيه يحتاج إلى انتباهك',
        'related_type' => 'alert',
        'related_id' => 1,
        'patient_id' => null,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
    ],
    [
        'user_id' => 2,
        'type' => 'system',
        'title' => 'تحديث النظام',
        'message' => 'تم تحديث النظام بنجاح',
        'related_type' => null,
        'related_id' => null,
        'patient_id' => null,
        'is_read' => 1,
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ],
    [
        'user_id' => 2,
        'type' => 'appointment',
        'title' => 'موعد ملغي',
        'message' => 'تم إلغاء موعد للمريض سارة أحمد',
        'related_type' => 'appointment',
        'related_id' => 3,
        'patient_id' => 3,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
    ],
    [
        'user_id' => 2,
        'type' => 'appointment',
        'title' => 'موعد محدث',
        'message' => 'تم تحديث موعد للمريض محمد علي',
        'related_type' => 'appointment',
        'related_id' => 4,
        'patient_id' => 4,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
    ]
];

$stmt = $pdo->prepare("
    INSERT INTO notifications (user_id, type, title, message, related_type, related_id, patient_id, is_read, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$added = 0;
foreach ($notifications as $notif) {
    try {
        $stmt->execute([
            $notif['user_id'],
            $notif['type'],
            $notif['title'],
            $notif['message'],
            $notif['related_type'],
            $notif['related_id'],
            $notif['patient_id'],
            $notif['is_read'],
            $notif['created_at']
        ]);
        $added++;
    } catch (PDOException $e) {
        echo "Error adding notification: " . $e->getMessage() . "\n";
    }
}

echo "تم إضافة $added إشعار بنجاح!\n";

// Show current notifications count
$countStmt = $pdo->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = 2");
$count = $countStmt->fetch(PDO::FETCH_ASSOC);
echo "إجمالي الإشعارات للمستخدم: " . $count['count'] . "\n";

$unreadStmt = $pdo->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = 2 AND is_read = 0");
$unread = $unreadStmt->fetch(PDO::FETCH_ASSOC);
echo "الإشعارات غير المقروءة: " . $unread['count'] . "\n";

