<?php

namespace App\Models;

use App\Config\Database;

class AlertModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new alert
     */
    public function create($data)
    {
        $sql = "INSERT INTO alerts (doctor_id, patient_id, appointment_id, message, alert_date, alert_time, repeat_count, repeat_interval, is_active) 
                VALUES (:doctor_id, :patient_id, :appointment_id, :message, :alert_date, :alert_time, :repeat_count, :repeat_interval, 1)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':doctor_id' => $data['doctor_id'],
            ':patient_id' => $data['patient_id'] ?? null,
            ':appointment_id' => $data['appointment_id'] ?? null,
            ':message' => $data['message'],
            ':alert_date' => $data['alert_date'],
            ':alert_time' => $data['alert_time'],
            ':repeat_count' => $data['repeat_count'] ?? 1,
            ':repeat_interval' => $data['repeat_interval'] ?? 0
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Get alert by ID
     */
    public function getById($id, $doctorId = null)
    {
        try {
            $sql = "SELECT a.*, 
                           p.first_name as patient_first_name, 
                           p.last_name as patient_last_name,
                           p.phone as patient_phone,
                           apt.date as appointment_date,
                           apt.start_time as appointment_time
                    FROM alerts a
                    LEFT JOIN patients p ON a.patient_id = p.id
                    LEFT JOIN appointments apt ON a.appointment_id = apt.id
                    WHERE a.id = :id";
            
            if ($doctorId) {
                $sql .= " AND a.doctor_id = :doctor_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $params = [':id' => $id];
            if ($doctorId) {
                $params[':doctor_id'] = $doctorId;
            }
            $stmt->execute($params);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all alerts for a doctor
     */
    public function getByDoctor($doctorId, $filters = [])
    {
        $sql = "SELECT a.*, 
                       p.first_name as patient_first_name, 
                       p.last_name as patient_last_name,
                       p.phone as patient_phone,
                       apt.date as appointment_date,
                       apt.start_time as appointment_time
                FROM alerts a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN appointments apt ON a.appointment_id = apt.id
                WHERE 1=1";
        
        if ($doctorId) {
            $sql .= " AND a.doctor_id = :doctor_id";
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND a.is_active = :is_active";
        }
        
        if (isset($filters['date'])) {
            $sql .= " AND a.alert_date = :date";
        }
        
        if (isset($filters['is_dismissed'])) {
            $sql .= " AND a.is_dismissed = :is_dismissed";
        }
        
        $sql .= " ORDER BY a.alert_date ASC, a.alert_time ASC";
        
        $stmt = $this->db->prepare($sql);
        $params = [];
        if ($doctorId) {
            $params[':doctor_id'] = $doctorId;
        }
        
        if (isset($filters['is_active'])) {
            $params[':is_active'] = $filters['is_active'];
        }
        
        if (isset($filters['date'])) {
            $params[':date'] = $filters['date'];
        }
        
        if (isset($filters['is_dismissed'])) {
            $params[':is_dismissed'] = $filters['is_dismissed'];
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all alerts for a specific patient
     */
    public function getByPatient($doctorId, $patientId, $filters = [])
    {
        $sql = "SELECT a.*, 
                       p.first_name as patient_first_name, 
                       p.last_name as patient_last_name,
                       p.phone as patient_phone,
                       apt.date as appointment_date,
                       apt.start_time as appointment_time
                FROM alerts a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN appointments apt ON a.appointment_id = apt.id
                WHERE a.doctor_id = :doctor_id AND a.patient_id = :patient_id";
        
        if (isset($filters['is_active'])) {
            $sql .= " AND a.is_active = :is_active";
        }
        
        if (isset($filters['is_dismissed'])) {
            $sql .= " AND a.is_dismissed = :is_dismissed";
        }
        
        $sql .= " ORDER BY a.alert_date ASC, a.alert_time ASC";
        
        $stmt = $this->db->prepare($sql);
        $params = [
            ':doctor_id' => $doctorId,
            ':patient_id' => $patientId
        ];
        
        if (isset($filters['is_active'])) {
            $params[':is_active'] = $filters['is_active'];
        }
        
        if (isset($filters['is_dismissed'])) {
            $params[':is_dismissed'] = $filters['is_dismissed'];
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get today's active alerts for a doctor
     */
    public function getTodayAlerts($doctorId)
    {
        $today = date('Y-m-d');
        return $this->getByDoctor($doctorId, [
            'date' => $today,
            'is_active' => 1,
            'is_dismissed' => 0
        ]);
    }

    /**
     * Get alerts that should be shown now
     */
    public function getActiveAlertsForTime($doctorId, $date = null, $time = null)
    {
        try {
        if (!$date) {
            $date = date('Y-m-d');
        }
        if (!$time) {
            $time = date('H:i:s');
        }
        
            // Normalize time format - ensure both are in HH:mm:ss format
            $normalizedTime = $time;
            if (strlen($time) === 5) {
                // If time is HH:mm, add :00 for seconds
                $normalizedTime = $time . ':00';
            }
            
            // Extract HH:mm from normalized time for comparison (avoid PDO parameter binding issues)
            $timeParts = explode(':', $normalizedTime);
            if (count($timeParts) < 2) {
                error_log("getActiveAlertsForTime - Invalid time format: $normalizedTime");
                return [];
            }
            $timeHHmm = $timeParts[0] . ':' . $timeParts[1]; // Get HH:mm part
            if (empty($timeHHmm)) {
                error_log("getActiveAlertsForTime - timeHHmm is empty!");
                return [];
            }
            
            // Log query parameters for debugging
            error_log("getActiveAlertsForTime - doctorId: $doctorId, date: $date, time: $normalizedTime, timeHHmm: $timeHHmm");
            
            // Use TIME() function for proper time comparison
            // Handle both HH:mm and HH:mm:ss formats in database
            // Show alerts where alert_time <= current_time (time has passed or is now)
            // Compare by extracting hours and minutes only (ignore seconds) for more lenient matching
            // Fix: Use direct string comparison for HH:mm to avoid PDO parameter binding issues with CAST/TIME
        $sql = "SELECT a.*, 
                       p.first_name as patient_first_name, 
                       p.last_name as patient_last_name,
                       p.phone as patient_phone,
                       p.id as patient_id,
                       apt.date as appointment_date,
                       apt.start_time as appointment_time
                FROM alerts a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN appointments apt ON a.appointment_id = apt.id
                WHERE a.doctor_id = :doctor_id
                AND a.is_active = 1
                AND a.is_dismissed = 0
                AND a.alert_date = :date
                    AND (
                        -- Compare time directly - alert_time is TIME type, compare HH:mm part
                        -- Use TIME_FORMAT to extract HH:mm from alert_time and compare with current time HH:mm
                        TIME_FORMAT(a.alert_time, '%H:%i') <= :time_hhmm
                    )
                AND (a.current_repeat < a.repeat_count OR a.repeat_count = 0)
                ORDER BY a.alert_time ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':doctor_id' => $doctorId,
            ':date' => $date,
                ':time_hhmm' => $timeHHmm
            ]);
            
            $results = $stmt->fetchAll();
            error_log("getActiveAlertsForTime - Query returned " . count($results) . " alerts");
            
            // Debug: Check if there are alerts for this doctor but different date/time
            if (count($results) == 0) {
                // Use direct string interpolation for debug query to avoid PDO parameter binding issues
                $debugSql = "SELECT a.id, a.alert_time, a.alert_date, a.is_active, a.is_dismissed, a.doctor_id,
                                    TIME_FORMAT(a.alert_time, '%H:%i') as alert_time_formatted,
                                    '" . $this->db->quote($timeHHmm) . "' as current_time_formatted,
                                    TIME_FORMAT(a.alert_time, '%H:%i') <= '" . $this->db->quote($timeHHmm) . "' as time_comparison_result
                             FROM alerts a
                             WHERE a.doctor_id = :doctor_id
                             AND a.alert_date = :date
                             AND a.is_active = 1
                             AND a.is_dismissed = 0";
                $debugStmt = $this->db->prepare($debugSql);
                $debugStmt->execute([
                    ':doctor_id' => $doctorId,
                    ':date' => $date
                ]);
                $debugResults = $debugStmt->fetchAll(\PDO::FETCH_ASSOC);
                error_log("getActiveAlertsForTime - Debug Results: " . json_encode($debugResults, JSON_UNESCAPED_UNICODE));
                
                // Also get all alerts for this doctor to see what's available
                $allAlertsSql = "SELECT a.id, a.alert_time, a.alert_date, a.is_active, a.is_dismissed, a.doctor_id
                                 FROM alerts a
                                 WHERE a.doctor_id = :doctor_id
                                 ORDER BY a.alert_date DESC, a.alert_time DESC
                                 LIMIT 10";
                $allAlertsStmt = $this->db->prepare($allAlertsSql);
                $allAlertsStmt->execute([':doctor_id' => $doctorId]);
                $allAlerts = $allAlertsStmt->fetchAll(\PDO::FETCH_ASSOC);
                error_log("getActiveAlertsForTime - All alerts for doctor: " . json_encode($allAlerts, JSON_UNESCAPED_UNICODE));
            }
            
            return $results;
        } catch (\Exception $e) {
            // Log error with full details and return empty array
            error_log('Error in getActiveAlertsForTime: ' . $e->getMessage());
            error_log('Error details - doctorId: ' . ($doctorId ?? 'NULL') . ', date: ' . ($date ?? 'NULL') . ', time: ' . ($time ?? 'NULL') . ', timeHHmm: ' . (isset($timeHHmm) ? $timeHHmm : 'NOT SET'));
            error_log('Error trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Update alert
     */
    public function update($id, $data, $doctorId = null)
    {
        $sql = "UPDATE alerts SET 
                message = :message,
                alert_date = :alert_date,
                alert_time = :alert_time,
                repeat_count = :repeat_count,
                repeat_interval = :repeat_interval,
                is_active = :is_active,
                is_dismissed = :is_dismissed";
        
        // Only update dismissed_at if alert is being reactivated
        if (isset($data['is_dismissed']) && $data['is_dismissed'] == 0) {
            $sql .= ", dismissed_at = NULL";
        }
        
        $sql .= " WHERE id = :id";
        
        if ($doctorId) {
            $sql .= " AND doctor_id = :doctor_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [
            ':id' => $id,
            ':message' => $data['message'],
            ':alert_date' => $data['alert_date'],
            ':alert_time' => $data['alert_time'],
            ':repeat_count' => $data['repeat_count'] ?? 1,
            ':repeat_interval' => $data['repeat_interval'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':is_dismissed' => $data['is_dismissed'] ?? 0
        ];
        
        if ($doctorId) {
            $params[':doctor_id'] = $doctorId;
        }
        
        return $stmt->execute($params);
    }

    /**
     * Delete alert
     */
    public function delete($id, $doctorId = null)
    {
        $sql = "DELETE FROM alerts WHERE id = :id";
        if ($doctorId) {
            $sql .= " AND doctor_id = :doctor_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [':id' => $id];
        if ($doctorId) {
            $params[':doctor_id'] = $doctorId;
        }
        
        return $stmt->execute($params);
    }

    /**
     * Dismiss alert (snooze)
     */
    public function dismiss($id, $doctorId = null)
    {
        $sql = "UPDATE alerts SET 
                is_dismissed = 1,
                dismissed_at = NOW()
                WHERE id = :id";
        
        if ($doctorId) {
            $sql .= " AND doctor_id = :doctor_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [':id' => $id];
        if ($doctorId) {
            $params[':doctor_id'] = $doctorId;
        }
        
        return $stmt->execute($params);
    }

    /**
     * Increment repeat counter
     */
    public function incrementRepeat($id)
    {
        $sql = "UPDATE alerts SET current_repeat = current_repeat + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Reset dismissed alerts for next occurrence
     */
    public function resetDismissedForNextRepeat($id)
    {
        $sql = "UPDATE alerts SET 
                is_dismissed = 0,
                dismissed_at = NULL
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get alert by message (for notes)
     * Normalizes both messages for comparison (trim whitespace and normalize HTML)
     */
    public function getByMessage($doctorId, $message)
    {
        // Normalize message (trim whitespace for comparison)
        $normalizedMessage = trim($message);
        
        // Try exact match first
        $sql = "SELECT * FROM alerts 
                WHERE doctor_id = :doctor_id 
                AND TRIM(message) = :message 
                AND patient_id IS NULL 
                AND appointment_id IS NULL
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':doctor_id' => $doctorId,
            ':message' => $normalizedMessage
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result;
            }
            
        // If no exact match, try to find by similar content (strip HTML tags and compare)
        if (!empty($normalizedMessage)) {
            // Strip HTML tags and normalize whitespace for comparison
            $textOnly = strip_tags($normalizedMessage);
            $textOnly = preg_replace('/\s+/', ' ', trim($textOnly));
            
            if (!empty($textOnly)) {
                $sql = "SELECT * FROM alerts 
                        WHERE doctor_id = :doctor_id 
                        AND patient_id IS NULL 
                        AND appointment_id IS NULL
                        ORDER BY created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':doctor_id' => $doctorId]);
                $allAlerts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Compare text content (without HTML tags)
                foreach ($allAlerts as $alert) {
                    $alertTextOnly = strip_tags($alert['message']);
                    $alertTextOnly = preg_replace('/\s+/', ' ', trim($alertTextOnly));
                    
                    if ($alertTextOnly === $textOnly) {
                        return $alert;
                }
            }
        }
    }
        
        return null;
    }
}

