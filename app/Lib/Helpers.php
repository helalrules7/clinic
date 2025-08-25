<?php

namespace App\Lib;

class Helpers
{
    /**
     * Format money amount
     */
    public static function money($amount, $currency = 'EGP')
    {
        return number_format($amount, 2) . ' ' . $currency;
    }

    /**
     * Format date
     */
    public static function formatDate($date, $format = 'd/m/Y')
    {
        if (!$date) return '';
        
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        return $date->format($format);
    }

    /**
     * Format time
     */
    public static function formatTime($time, $format = 'H:i')
    {
        if (!$time) return '';
        
        if (is_string($time)) {
            $time = new \DateTime($time);
        }
        
        return $time->format($format);
    }

    /**
     * Get status badge class
     */
    public static function getStatusBadgeClass($status)
    {
        $classes = [
            'Booked' => 'badge bg-primary',
            'CheckedIn' => 'badge bg-info',
            'InProgress' => 'badge bg-warning',
            'Completed' => 'badge bg-success',
            'Cancelled' => 'badge bg-danger',
            'NoShow' => 'badge bg-secondary',
            'Rescheduled' => 'badge bg-info'
        ];
        
        return $classes[$status] ?? 'badge bg-secondary';
    }

    /**
     * Get visit type badge class
     */
    public static function getVisitTypeBadgeClass($type)
    {
        $classes = [
            'New' => 'badge bg-primary',
            'FollowUp' => 'badge bg-success',
            'Procedure' => 'badge bg-warning'
        ];
        
        return $classes[$type] ?? 'badge bg-secondary';
    }

    /**
     * Get payment method icon
     */
    public static function getPaymentMethodIcon($method)
    {
        $icons = [
            'Cash' => 'bi-cash-coin',
            'Card' => 'bi-credit-card',
            'Wallet' => 'bi-wallet2',
            'Transfer' => 'bi-bank'
        ];
        
        return $icons[$method] ?? 'bi-question-circle';
    }

    /**
     * Generate time slots
     */
    public static function generateTimeSlots($startTime = '14:00', $endTime = '23:00', $interval = 15)
    {
        $slots = [];
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $interval = new \DateInterval("PT{$interval}M");
        
        $current = clone $start;
        
        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->add($interval);
        }
        
        return $slots;
    }

    /**
     * Check if date is Friday
     */
    public static function isFriday($date = null)
    {
        if (!$date) {
            $date = new \DateTime();
        } elseif (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        return $date->format('N') == 5; // 5 = Friday
    }

    /**
     * Get working days for doctor
     */
    public static function getWorkingDays($doctorId)
    {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT weekday, work_start, work_end 
            FROM doctor_schedule 
            WHERE doctor_id = ? AND is_working = 1
            ORDER BY weekday
        ");
        $stmt->execute([$doctorId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Check if time slot is available
     */
    public static function isTimeSlotAvailable($doctorId, $date, $startTime, $endTime, $excludeAppointmentId = null)
    {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        // Check if it's Friday
        if (self::isFriday($date)) {
            return false;
        }
        
        // Check if doctor works on this day
        $weekday = (new \DateTime($date))->format('w');
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM doctor_schedule 
            WHERE doctor_id = ? AND weekday = ? AND is_working = 1
        ");
        $stmt->execute([$doctorId, $weekday]);
        
        if ($stmt->fetchColumn() == 0) {
            return false;
        }
        
        // Check for conflicts
        $sql = "
            SELECT COUNT(*) FROM appointments 
            WHERE doctor_id = ? AND date = ? 
            AND status NOT IN ('Cancelled', 'NoShow')
            AND (
                (start_time < ? AND end_time > ?) OR
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND end_time <= ?)
            )
        ";
        
        $params = [$doctorId, $date, $endTime, $startTime, $endTime, $startTime, $startTime, $endTime];
        
        if ($excludeAppointmentId) {
            $sql .= " AND id != ?";
            $params[] = $excludeAppointmentId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM invoices 
            WHERE YEAR(date) = ? AND MONTH(date) = ?
        ");
        $stmt->execute([$year, $month]);
        
        $count = $stmt->fetchColumn() + 1;
        
        return sprintf('%s-%s-%s-%03d', $prefix, $year, $month, $count);
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename)
    {
        // Remove any path components
        $filename = basename($filename);
        
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        
        // Remove any non-alphanumeric characters except dots and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Ensure it's not too long
        if (strlen($filename) > 255) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 255 - strlen($extension) - 1) . '.' . $extension;
        }
        
        return $filename;
    }

    /**
     * Get file extension from mime type
     */
    public static function getExtensionFromMimeType($mimeType)
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt'
        ];
        
        return $extensions[$mimeType] ?? 'bin';
    }

    /**
     * Format file size
     */
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Validate email
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (Egyptian format)
     */
    public static function validatePhone($phone)
    {
        return preg_match('/^(\+20|0)?1[0-9]{9}$/', $phone);
    }

    /**
     * Validate national ID (Egyptian format)
     */
    public static function validateNationalId($id)
    {
        return preg_match('/^[0-9]{14}$/', $id);
    }

    /**
     * Generate random string
     */
    public static function randomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Escape HTML
     */
    public static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get current timestamp
     */
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get current date
     */
    public static function today()
    {
        return date('Y-m-d');
    }

    /**
     * Calculate age from date of birth
     */
    public static function calculateAge($dob)
    {
        if (!$dob) return null;
        
        $dob = new \DateTime($dob);
        $now = new \DateTime();
        $interval = $now->diff($dob);
        
        return $interval->y;
    }
}
