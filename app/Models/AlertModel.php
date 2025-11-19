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
            error_log("AlertModel::getById error: " . $e->getMessage());
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
                WHERE a.doctor_id = :doctor_id";
        
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
        $params = [':doctor_id' => $doctorId];
        
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
        if (!$date) {
            $date = date('Y-m-d');
        }
        if (!$time) {
            $time = date('H:i:s');
        }
        
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
                AND a.alert_time <= :time
                AND (a.current_repeat < a.repeat_count OR a.repeat_count = 0)
                ORDER BY a.alert_time ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':doctor_id' => $doctorId,
            ':date' => $date,
            ':time' => $time
        ]);
        
        return $stmt->fetchAll();
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
        $logFile = '/home/hclinic/web/roaya.hclinic.clinic/logs/roaya.hclinic.clinic.error.log';
        
        // Normalize message (trim whitespace for comparison)
        $normalizedMessage = trim($message);
        
        error_log("getByMessage called - Doctor ID: {$doctorId}, Message Length: " . strlen($normalizedMessage) . "\n", 3, $logFile);
        
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
            error_log("✓ Exact match found - Alert ID: {$result['id']}\n", 3, $logFile);
            return $result;
        }
        
        error_log("✗ No exact match, trying text-only comparison...\n", 3, $logFile);
        
        // If no exact match, try to find by similar content (strip HTML tags and compare)
        if (!empty($normalizedMessage)) {
            // Strip HTML tags and normalize whitespace for comparison
            $textOnly = strip_tags($normalizedMessage);
            $textOnly = preg_replace('/\s+/', ' ', trim($textOnly));
            
            error_log("Text-only (no HTML): " . substr($textOnly, 0, 200) . "...\n", 3, $logFile);
            
            if (!empty($textOnly)) {
                $sql = "SELECT * FROM alerts 
                        WHERE doctor_id = :doctor_id 
                        AND patient_id IS NULL 
                        AND appointment_id IS NULL
                        ORDER BY created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':doctor_id' => $doctorId]);
                $allAlerts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                error_log("Found " . count($allAlerts) . " alerts for doctor (no patient/appointment)\n", 3, $logFile);
                
                // Compare text content (without HTML tags)
                foreach ($allAlerts as $alert) {
                    $alertTextOnly = strip_tags($alert['message']);
                    $alertTextOnly = preg_replace('/\s+/', ' ', trim($alertTextOnly));
                    
                    error_log("Comparing - Alert ID: {$alert['id']}, Text: " . substr($alertTextOnly, 0, 100) . "...\n", 3, $logFile);
                    
                    if ($alertTextOnly === $textOnly) {
                        error_log("✓ Text-only match found - Alert ID: {$alert['id']}\n", 3, $logFile);
                        return $alert;
                    }
                }
            }
        }
        
        error_log("✗ No match found\n", 3, $logFile);
        return null;
    }
}

