<?php

namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;

class NotificationController
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /**
     * Get all notifications for current user
     */
    public function getAll()
    {
        // Clear output buffers first
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        try {
            $stmt = $this->pdo->prepare("
                SELECT n.*, 
                       p.first_name as patient_first_name, 
                       p.last_name as patient_last_name
                FROM notifications n
                LEFT JOIN patients p ON n.patient_id = p.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bindValue(1, $user['id'], \PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("NotificationController::getAll SQL Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // Get unread count
        try {
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
            $countStmt->bindValue(1, $user['id'], \PDO::PARAM_INT);
            $countStmt->execute();
            $result = $countStmt->fetch(\PDO::FETCH_ASSOC);
            $unreadCount = $result ? (int)$result['count'] : 0;
        } catch (\PDOException $e) {
            error_log("NotificationController::getAll Count Error: " . $e->getMessage());
            $unreadCount = 0;
        }

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => (int)$unreadCount
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        // Clear output buffers first
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'unread_count' => (int)$result['count']
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as read'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
            
            $deleted = $stmt->rowCount();
            
            if ($deleted > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification deleted'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Notification not found or already deleted'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        } catch (\PDOException $e) {
            error_log("NotificationController::delete SQL Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            
            $deleted = $stmt->rowCount();
            
            echo json_encode([
                'success' => true,
                'message' => 'All notifications cleared',
                'deleted_count' => $deleted
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\PDOException $e) {
            error_log("NotificationController::clearAll SQL Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Create notification (internal method, can be called from other controllers)
     */
    public static function create($userId, $type, $title, $message, $relatedType = null, $relatedId = null, $patientId = null, $groupKey = null)
    {
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, related_type, related_id, patient_id, is_read, group_key)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)
        ");

        $stmt->execute([$userId, $type, $title, $message, $relatedType, $relatedId, $patientId, $groupKey]);

        return $pdo->lastInsertId();
    }

    /**
     * @return int[] user ids of all secretaries in a clinic (inverse of
     * resolveDoctorUserIdsForClinic — secretaries DO carry users.clinic_id).
     */
    public static function resolveSecretaryUserIdsForClinic($clinicId)
    {
        if (!$clinicId) {
            return [];
        }
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare(
                "SELECT id FROM users WHERE clinic_id = ? AND role = 'secretary'"
            );
            $stmt->execute([(int)$clinicId]);
            return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Cross-party appointment notification. Any book / status-change / delete /
     * reschedule / edit on an appointment notifies the OTHER party — the
     * appointment's assigned doctor AND every secretary of its clinic — minus the
     * actor who performed it (never self-notify). Collapsed in the bell by
     * group_key = "appt:{id}" so a busy day on one appointment is one stack.
     *
     * Also writes the action to activity_log with the REAL actor (drives the
     * activity feed: correct "You" for whoever acted, and an audit row that
     * survives a hard delete).
     *
     * @param array    $appointment  needs at least: id, doctor_id, clinic_id, patient_id
     * @param int      $actorUserId  the user who performed the action (excluded from notifs)
     * @param string   $action       activity verb: booked|status_changed|deleted|rescheduled|edited|checked_in
     * @param string   $detail       optional activity detail (e.g. new status, "to <date> <time>")
     * @return int[]   created notification ids
     */
    public static function notifyAppointmentCounterparties(array $appointment, $actorUserId, $action, $title, $message, $detail = null)
    {
        $apptId   = (int)($appointment['id'] ?? 0);
        $doctorId = $appointment['doctor_id'] ?? null;
        $clinicId = $appointment['clinic_id'] ?? null;
        $patientId = isset($appointment['patient_id']) ? (int)$appointment['patient_id'] : null;
        $groupKey = $apptId ? ('appt:' . $apptId) : null;

        // Audit log first (real actor) — drives the activity feed.
        \App\Lib\Helpers::logActivity($actorUserId, $action, $apptId ?: null, $patientId, $doctorId, $clinicId, $detail);

        // Language per recipient: the secretary UI is Arabic, the doctor UI English.
        // Secretaries → Arabic message (built below); the assigned doctor → the
        // English $title/$message passed in.
        $langByUid = [];
        if ($doctorId) {
            $du = self::resolveUserIdForDoctorId($doctorId);
            if ($du) {
                $langByUid[(int)$du] = 'en';
            }
        }
        foreach (self::resolveSecretaryUserIdsForClinic($clinicId) as $sid) {
            $langByUid[(int)$sid] = 'ar'; // secretary wins if somehow both
        }

        $actorUserId = (int)$actorUserId;
        $arTitle = null;
        $arMessage = null;
        $created = [];
        foreach ($langByUid as $uid => $lang) {
            $uid = (int)$uid;
            if ($uid === $actorUserId) {
                continue; // never notify the actor about their own action
            }
            // Honour the per-doctor appointment-notification opt-out (doctors only).
            if (self::userSkipsNotification($uid, 'dont_create_notification_for_appointments')) {
                continue;
            }
            if ($lang === 'ar') {
                if ($arTitle === null) {
                    [$arTitle, $arMessage] = self::buildArabicAppointmentNotif($appointment, $actorUserId, $action, $detail);
                }
                $t = $arTitle;
                $m = $arMessage;
            } else {
                $t = $title;
                $m = $message;
            }
            $nid = self::create($uid, 'appointment', $t, $m, 'appointment', $apptId ?: null, $patientId, $groupKey);
            if ($nid) {
                $created[] = (int)$nid;
            }
        }
        return $created;
    }

    /** Arabic (gender-aware) title + message for an appointment notification. */
    private static function buildArabicAppointmentNotif(array $appointment, $actorUserId, $action, $detail)
    {
        $pdo = Database::getInstance()->getConnection();
        $patientName = '';
        $gender = null;
        try {
            $stmt = $pdo->prepare('SELECT first_name, last_name, gender FROM patients WHERE id = ? LIMIT 1');
            $stmt->execute([(int)($appointment['patient_id'] ?? 0)]);
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $patientName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                $gender = $row['gender'] ?? null;
            }
        } catch (\Throwable $e) { /* best-effort */ }

        $actor = 'مستخدم';
        try {
            $stmt = $pdo->prepare('SELECT name, username FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([(int)$actorUserId]);
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $actor = trim($row['name'] ?? '') ?: ($row['username'] ?? 'مستخدم');
            }
        } catch (\Throwable $e) { /* best-effort */ }

        $forP = $patientName !== ''
            ? (($gender === 'Female' ? 'للمريضة' : ($gender === 'Male' ? 'للمريض' : 'للمريض/ة')) . ' ' . $patientName)
            : '';
        $bareP = $patientName !== ''
            ? (($gender === 'Female' ? 'المريضة' : ($gender === 'Male' ? 'المريض' : 'المريض/ة')) . ' ' . $patientName)
            : 'المريض';
        $date = $appointment['date'] ?? '';
        $time = isset($appointment['start_time']) ? substr((string)$appointment['start_time'], 0, 5) : '';
        $when = ($date ? (' يوم ' . $date) : '') . ($time ? (' الساعة ' . $time) : '');

        $statusAr = [
            'Booked' => 'محجوز', 'CheckedIn' => 'تم الحضور', 'InProgress' => 'جارٍ',
            'Completed' => 'مكتمل', 'Cancelled' => 'ملغي', 'NoShow' => 'لم يحضر',
            'Rescheduled' => 'معاد جدولته', 'Closed' => 'مغلق',
        ];

        switch ($action) {
            case 'booked':
                return ['موعد جديد', "قام {$actor} بحجز موعد {$forP}{$when}"];
            case 'status_changed':
                $s = $statusAr[$detail] ?? (string)$detail;
                return ['تحديث حالة موعد', "قام {$actor} بتغيير حالة الموعد إلى «{$s}» {$forP}"];
            case 'deleted':
                return ['حذف موعد', "قام {$actor} بحذف موعد {$forP}{$when}"];
            case 'rescheduled':
                $d = $detail ? str_replace('to ', 'إلى ', (string)$detail) : '';
                return ['إعادة جدولة موعد', trim("قام {$actor} بإعادة جدولة موعد {$forP} {$d}")];
            case 'edited':
                return ['تعديل موعد', "قام {$actor} بتعديل موعد {$forP}"];
            case 'checked_in':
                return ['تسجيل حضور', "تم تسجيل حضور {$bareP}{$when}"];
            default:
                return ['إشعار موعد', "تحديث على موعد {$forP}"];
        }
    }

    /**
     * Map doctors.id → users.id for bell notifications.
     */
    public static function resolveUserIdForDoctorId($doctorId)
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare('SELECT user_id FROM doctors WHERE id = ? LIMIT 1');
            $stmt->execute([(int)$doctorId]);
            $uid = $stmt->fetchColumn();
            if ($uid) {
                return (int)$uid;
            }
        } catch (\Throwable $e) {
            // fall through
        }
        return (int)$doctorId;
    }

    /**
     * @return int[]
     */
    public static function resolveDoctorUserIdsForClinic($clinicId)
    {
        if (!$clinicId) {
            return [];
        }
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare(
                "SELECT id FROM users WHERE clinic_id = ? AND role = 'doctor'"
            );
            $stmt->execute([(int)$clinicId]);
            return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function userSkipsNotification($userId, $settingKey)
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare(
                'SELECT setting_value FROM doctor_settings WHERE user_id = ? AND setting_key = ? LIMIT 1'
            );
            $stmt->execute([(int)$userId, $settingKey]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row && (string)$row['setting_value'] === '1';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Secretary booked an appointment → immediate bell row for the assigned doctor.
     * Picked up by notification-center.js background poll (30s) / panel poll (60s).
     */
    public static function notifyDoctorAppointmentBookedBySecretary(
        array $secretaryUser,
        $doctorId,
        $appointmentId,
        $patientId,
        $patientName,
        $date,
        $startTime
    ) {
        $doctorUserId = self::resolveUserIdForDoctorId($doctorId);
        if (self::userSkipsNotification($doctorUserId, 'dont_create_notification_for_appointments')) {
            return null;
        }

        $secLabel = trim($secretaryUser['name'] ?? $secretaryUser['username'] ?? 'Secretary');
        $timeLabel = substr((string)$startTime, 0, 5);

        return self::create(
            $doctorUserId,
            'appointment',
            'New Appointment (Secretary)',
            "Secretary ({$secLabel}) booked {$patientName} on {$date} at {$timeLabel}",
            'appointment',
            (int)$appointmentId,
            (int)$patientId
        );
    }

    /**
     * Secretary recorded a patient payment → immediate bell row for the doctor.
     */
    public static function notifyDoctorsPaymentReceivedBySecretary(
        array $secretaryUser,
        $paymentId,
        $patientId,
        $patientName,
        $amount,
        $method,
        $appointmentDoctorId = null,
        $clinicId = null
    ) {
        $recipientIds = [];
        if ($appointmentDoctorId) {
            $recipientIds[] = self::resolveUserIdForDoctorId($appointmentDoctorId);
        } elseif ($clinicId) {
            $recipientIds = self::resolveDoctorUserIdsForClinic($clinicId);
        }
        $recipientIds = array_values(array_unique(array_filter($recipientIds)));
        if (!$recipientIds) {
            return [];
        }

        $secLabel = trim($secretaryUser['name'] ?? $secretaryUser['username'] ?? 'Secretary');
        $amt = number_format((float)$amount, 2);
        $methodLabel = $method ? (string)$method : 'Cash';
        $created = [];

        foreach ($recipientIds as $doctorUserId) {
            $nid = self::create(
                $doctorUserId,
                'payment',
                'Payment Received (Secretary)',
                "Secretary ({$secLabel}) recorded {$amt} EGP ({$methodLabel}) for {$patientName}",
                'payment',
                (int)$paymentId,
                (int)$patientId
            );
            if ($nid) {
                $created[] = (int)$nid;
            }
        }

        return $created;
    }

    /**
     * Create notification for admin (system notification)
     */
    public function createSystemNotification()
    {
        header('Content-Type: application/json');
        
        $user = $this->auth->user();
        if (!$user || $user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['title']) || empty($data['message'])) {
            echo json_encode(['success' => false, 'message' => 'Title and message are required'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // Get all users or specific users
        $userIds = [];
        if (isset($data['user_ids']) && is_array($data['user_ids']) && !empty($data['user_ids'])) {
            $userIds = $data['user_ids'];
        } else {
            // Send to all users
            $stmt = $this->pdo->query("SELECT id FROM users");
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $userIds = array_column($users, 'id');
        }

        $created = 0;
        foreach ($userIds as $userId) {
            self::create(
                $userId,
                $data['type'] ?? 'system',
                $data['title'],
                $data['message'],
                $data['related_type'] ?? null,
                $data['related_id'] ?? null,
                $data['patient_id'] ?? null
            );
            $created++;
        }

        echo json_encode([
            'success' => true,
            'message' => "Notification sent to {$created} user(s)",
            'created' => $created
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

