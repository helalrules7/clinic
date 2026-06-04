<?php

namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;

/**
 * ActivityController
 *
 * Powers the team activity feed (Activity tab in the notification center).
 *
 * Endpoint:
 *   GET /api/activity?limit=50
 *
 * Response shape:
 *   { success: true, events: [
 *       { id, type, actor_name, actor_id, action, target_label, target_link, ts, time_ago }
 *   ] }
 *
 * --------------------------------------------------------------------------
 * ASSUMED COLUMNS (integrator: please verify against actual migrations):
 * --------------------------------------------------------------------------
 *   consultation_notes : id, doctor_id, patient_id, created_at
 *                        (gracefully degrades — falls back if doctor_id is
 *                         missing; patient_id is required for a useful event)
 *   appointments       : id, doctor_id, patient_id, status, updated_at
 *                        (uses status_changed_at if present, otherwise
 *                         updated_at, otherwise date)
 *   alerts             : id, doctor_id, patient_id, message, created_at
 *   todos              : id, user_id, title, patient_id, created_at
 *   users              : id, name, username, clinic_id (clinic_id OPTIONAL)
 *   patients           : id, first_name, last_name
 * --------------------------------------------------------------------------
 */
class ActivityController
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /**
     * GET /api/activity?limit=50
     *
     * UNIONs four sources (consultation_notes, appointments, alerts, todos)
     * into a single chronological feed, sorted by ts DESC, capped at $limit.
     */
    public function feed()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        if ($limit < 1)   $limit = 1;
        if ($limit > 200) $limit = 200;

        // ------------------------------------------------------------------
        // Resolve clinic scope. If users.clinic_id exists AND the current
        // user has a clinic_id, restrict actor pool to same clinic. Otherwise
        // fall back to "all users in the system".
        // ------------------------------------------------------------------
        $allowedActorIds = null; // null => no filter
        try {
            $hasClinicCol = $this->columnExists('users', 'clinic_id');
            if ($hasClinicCol) {
                // Look up the current user's clinic
                $clinicId = null;
                if (array_key_exists('clinic_id', $user) && $user['clinic_id'] !== null) {
                    $clinicId = (int)$user['clinic_id'];
                } else {
                    $stmt = $this->pdo->prepare("SELECT clinic_id FROM users WHERE id = ? LIMIT 1");
                    $stmt->execute([$user['id']]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($row && $row['clinic_id'] !== null) {
                        $clinicId = (int)$row['clinic_id'];
                    }
                }

                if ($clinicId !== null) {
                    $stmt = $this->pdo->prepare("SELECT id FROM users WHERE clinic_id = ?");
                    $stmt->execute([$clinicId]);
                    $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                    if (is_array($ids) && count($ids) > 0) {
                        $allowedActorIds = array_map('intval', $ids);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Schema check / clinic lookup failed — fall back to no filter
            error_log("ActivityController::feed clinic-scope fallback: " . $e->getMessage());
            $allowedActorIds = null;
        }

        $events = [];

        // -- Source 1: consultation_notes ----------------------------------
        try {
            $events = array_merge($events, $this->fetchConsultationNoteEvents($allowedActorIds, $limit));
        } catch (\Throwable $e) {
            error_log("ActivityController::feed consultation_notes skipped: " . $e->getMessage());
        }

        // -- Source 2: appointments (status changes) -----------------------
        try {
            $events = array_merge($events, $this->fetchAppointmentEvents($allowedActorIds, $limit));
        } catch (\Throwable $e) {
            error_log("ActivityController::feed appointments skipped: " . $e->getMessage());
        }

        // -- Source 3: alerts ----------------------------------------------
        try {
            $events = array_merge($events, $this->fetchAlertEvents($allowedActorIds, $limit));
        } catch (\Throwable $e) {
            error_log("ActivityController::feed alerts skipped: " . $e->getMessage());
        }

        // -- Source 4: todos (current user only) ---------------------------
        try {
            $events = array_merge($events, $this->fetchTodoEvents((int)$user['id'], $limit));
        } catch (\Throwable $e) {
            error_log("ActivityController::feed todos skipped: " . $e->getMessage());
        }

        // -- Sort & trim ---------------------------------------------------
        usort($events, function ($a, $b) {
            // Sort by raw ts DESC. Empty/null timestamps sink to the bottom.
            $ta = isset($a['ts']) ? strtotime($a['ts']) : 0;
            $tb = isset($b['ts']) ? strtotime($b['ts']) : 0;
            if ($ta === $tb) return 0;
            return ($ta < $tb) ? 1 : -1;
        });

        if (count($events) > $limit) {
            $events = array_slice($events, 0, $limit);
        }

        // Decorate with time_ago + iso ts (after sort, so it's only done $limit times)
        foreach ($events as &$ev) {
            $ev['ts']       = $this->toIso($ev['ts'] ?? null);
            $ev['time_ago'] = $this->timeAgo($ev['ts']);
        }
        unset($ev);

        echo json_encode([
            'success' => true,
            'events'  => $events
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ======================================================================
    //  Source fetchers
    // ======================================================================

    /**
     * consultation_notes — "Dr. X added a consultation note for {patient}"
     */
    private function fetchConsultationNoteEvents($allowedActorIds, $limit)
    {
        if (!$this->tableExists('consultation_notes')) {
            return [];
        }

        $hasDoctorId = $this->columnExists('consultation_notes', 'doctor_id');
        $hasPatientId = $this->columnExists('consultation_notes', 'patient_id');
        $hasCreatedAt = $this->columnExists('consultation_notes', 'created_at');

        if (!$hasCreatedAt) {
            return [];
        }

        $actorCol  = $hasDoctorId ? 'cn.doctor_id' : 'NULL';
        $patientCol = $hasPatientId ? 'cn.patient_id' : 'NULL';

        $sql = "SELECT cn.id AS row_id,
                       {$actorCol}   AS actor_id,
                       {$patientCol} AS patient_id,
                       cn.created_at AS ts,
                       u.name        AS actor_name,
                       u.username    AS actor_username,
                       p.first_name  AS p_first,
                       p.last_name   AS p_last
                FROM consultation_notes cn
                LEFT JOIN users u    ON u.id = {$actorCol}
                LEFT JOIN patients p ON p.id = {$patientCol}
                WHERE 1=1";

        $params = [];
        if ($hasDoctorId && is_array($allowedActorIds) && count($allowedActorIds) > 0) {
            $ph  = implode(',', array_fill(0, count($allowedActorIds), '?'));
            $sql .= " AND cn.doctor_id IN ($ph)";
            $params = array_merge($params, $allowedActorIds);
        }
        $sql .= " ORDER BY cn.created_at DESC LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $patientLabel = trim(($r['p_first'] ?? '') . ' ' . ($r['p_last'] ?? ''));
            $out[] = [
                'id'           => 'note-' . $r['row_id'],
                'type'         => 'consultation_note',
                'actor_id'     => $r['actor_id'] !== null ? (int)$r['actor_id'] : null,
                'actor_name'   => $r['actor_name'] ?: ($r['actor_username'] ?: 'Unknown'),
                'action'       => 'added a consultation note',
                'target_label' => $patientLabel !== '' ? $patientLabel : ($r['patient_id'] ? ('Patient #' . $r['patient_id']) : ''),
                'target_link'  => $r['patient_id'] ? ('/patient/' . (int)$r['patient_id']) : null,
                'ts'           => $r['ts'],
            ];
        }
        return $out;
    }

    /**
     * appointments — recently status-changed
     */
    private function fetchAppointmentEvents($allowedActorIds, $limit)
    {
        if (!$this->tableExists('appointments')) {
            return [];
        }

        $hasStatusChangedAt = $this->columnExists('appointments', 'status_changed_at');
        $hasUpdatedAt       = $this->columnExists('appointments', 'updated_at');
        $hasDate            = $this->columnExists('appointments', 'date');
        $hasStatus          = $this->columnExists('appointments', 'status');
        $hasDoctorId        = $this->columnExists('appointments', 'doctor_id');
        $hasPatientId       = $this->columnExists('appointments', 'patient_id');

        if ($hasStatusChangedAt) {
            $tsCol = 'a.status_changed_at';
        } elseif ($hasUpdatedAt) {
            $tsCol = 'a.updated_at';
        } elseif ($hasDate) {
            $tsCol = 'a.date';
        } else {
            return [];
        }

        $actorCol  = $hasDoctorId  ? 'a.doctor_id'  : 'NULL';
        $patientCol = $hasPatientId ? 'a.patient_id' : 'NULL';
        $statusCol = $hasStatus    ? 'a.status'    : "''";

        $sql = "SELECT a.id        AS row_id,
                       {$actorCol}   AS actor_id,
                       {$patientCol} AS patient_id,
                       {$statusCol}  AS status,
                       {$tsCol}      AS ts,
                       u.name        AS actor_name,
                       u.username    AS actor_username,
                       p.first_name  AS p_first,
                       p.last_name   AS p_last
                FROM appointments a
                LEFT JOIN users u    ON u.id = {$actorCol}
                LEFT JOIN patients p ON p.id = {$patientCol}
                WHERE {$tsCol} IS NOT NULL";

        $params = [];
        if ($hasDoctorId && is_array($allowedActorIds) && count($allowedActorIds) > 0) {
            $ph  = implode(',', array_fill(0, count($allowedActorIds), '?'));
            $sql .= " AND a.doctor_id IN ($ph)";
            $params = array_merge($params, $allowedActorIds);
        }
        $sql .= " ORDER BY {$tsCol} DESC LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $patientLabel = trim(($r['p_first'] ?? '') . ' ' . ($r['p_last'] ?? ''));
            $status = (string)($r['status'] ?? '');
            $action = $status !== ''
                ? "updated appointment status to \"{$status}\""
                : 'updated an appointment';
            $out[] = [
                'id'           => 'appt-' . $r['row_id'],
                'type'         => 'appointment_status',
                'actor_id'     => $r['actor_id'] !== null ? (int)$r['actor_id'] : null,
                'actor_name'   => $r['actor_name'] ?: ($r['actor_username'] ?: 'Unknown'),
                'action'       => $action,
                'target_label' => $patientLabel !== '' ? $patientLabel : ($r['patient_id'] ? ('Patient #' . $r['patient_id']) : ('Appointment #' . $r['row_id'])),
                'target_link'  => '/appointment/' . (int)$r['row_id'],
                'ts'           => $r['ts'],
            ];
        }
        return $out;
    }

    /**
     * alerts — "Dr. X created an alert for {patient}"
     */
    private function fetchAlertEvents($allowedActorIds, $limit)
    {
        if (!$this->tableExists('alerts')) {
            return [];
        }

        $hasCreatedAt = $this->columnExists('alerts', 'created_at');
        if (!$hasCreatedAt) {
            return [];
        }

        $hasDoctorId  = $this->columnExists('alerts', 'doctor_id');
        $hasPatientId = $this->columnExists('alerts', 'patient_id');
        $hasMessage   = $this->columnExists('alerts', 'message');

        $actorCol   = $hasDoctorId  ? 'al.doctor_id'  : 'NULL';
        $patientCol = $hasPatientId ? 'al.patient_id' : 'NULL';
        $msgCol     = $hasMessage   ? 'al.message'    : "''";

        $sql = "SELECT al.id      AS row_id,
                       {$actorCol}   AS actor_id,
                       {$patientCol} AS patient_id,
                       {$msgCol}     AS message,
                       al.created_at AS ts,
                       u.name        AS actor_name,
                       u.username    AS actor_username,
                       p.first_name  AS p_first,
                       p.last_name   AS p_last
                FROM alerts al
                LEFT JOIN users u    ON u.id = {$actorCol}
                LEFT JOIN patients p ON p.id = {$patientCol}
                WHERE 1=1";

        $params = [];
        if ($hasDoctorId && is_array($allowedActorIds) && count($allowedActorIds) > 0) {
            $ph  = implode(',', array_fill(0, count($allowedActorIds), '?'));
            $sql .= " AND al.doctor_id IN ($ph)";
            $params = array_merge($params, $allowedActorIds);
        }
        $sql .= " ORDER BY al.created_at DESC LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $patientLabel = trim(($r['p_first'] ?? '') . ' ' . ($r['p_last'] ?? ''));
            $out[] = [
                'id'           => 'alert-' . $r['row_id'],
                'type'         => 'alert',
                'actor_id'     => $r['actor_id'] !== null ? (int)$r['actor_id'] : null,
                'actor_name'   => $r['actor_name'] ?: ($r['actor_username'] ?: 'Unknown'),
                'action'       => 'created an alert',
                'target_label' => $patientLabel !== '' ? $patientLabel : ($r['patient_id'] ? ('Patient #' . $r['patient_id']) : (string)($r['message'] ?? '')),
                'target_link'  => $r['patient_id'] ? ('/patient/' . (int)$r['patient_id']) : null,
                'ts'           => $r['ts'],
            ];
        }
        return $out;
    }

    /**
     * todos — current user only
     */
    private function fetchTodoEvents($userId, $limit)
    {
        if (!$this->tableExists('todos')) {
            return [];
        }

        $hasCreatedAt = $this->columnExists('todos', 'created_at');
        $hasUserId    = $this->columnExists('todos', 'user_id');
        if (!$hasCreatedAt || !$hasUserId) {
            return [];
        }

        $hasTitle     = $this->columnExists('todos', 'title');
        $hasPatientId = $this->columnExists('todos', 'patient_id');

        $titleCol   = $hasTitle     ? 't.title'      : "''";
        $patientCol = $hasPatientId ? 't.patient_id' : 'NULL';

        $sql = "SELECT t.id          AS row_id,
                       t.user_id     AS actor_id,
                       {$titleCol}   AS title,
                       {$patientCol} AS patient_id,
                       t.created_at  AS ts,
                       u.name        AS actor_name,
                       u.username    AS actor_username,
                       p.first_name  AS p_first,
                       p.last_name   AS p_last
                FROM todos t
                LEFT JOIN users u    ON u.id = t.user_id
                LEFT JOIN patients p ON p.id = {$patientCol}
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC
                LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $patientLabel = trim(($r['p_first'] ?? '') . ' ' . ($r['p_last'] ?? ''));
            $title = (string)($r['title'] ?? '');
            $label = $title !== '' ? $title : ($patientLabel !== '' ? $patientLabel : ('Task #' . $r['row_id']));
            $out[] = [
                'id'           => 'todo-' . $r['row_id'],
                'type'         => 'todo',
                'actor_id'     => (int)$r['actor_id'],
                'actor_name'   => $r['actor_name'] ?: ($r['actor_username'] ?: 'You'),
                'action'       => 'created a task',
                'target_label' => $label,
                'target_link'  => '/todos#todo-' . (int)$r['row_id'],
                'ts'           => $r['ts'],
            ];
        }
        return $out;
    }

    // ======================================================================
    //  Helpers
    // ======================================================================

    /** Cache for schema lookups within one request. */
    private $schemaCache = [];

    private function tableExists($table)
    {
        $key = "t:$table";
        if (isset($this->schemaCache[$key])) {
            return $this->schemaCache[$key];
        }
        try {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            $exists = (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            $exists = false;
        }
        return $this->schemaCache[$key] = $exists;
    }

    private function columnExists($table, $column)
    {
        $key = "c:$table.$column";
        if (isset($this->schemaCache[$key])) {
            return $this->schemaCache[$key];
        }
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
            $stmt->execute([$column]);
            $exists = (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            $exists = false;
        }
        return $this->schemaCache[$key] = $exists;
    }

    private function toIso($ts)
    {
        if (!$ts) return null;
        $t = is_numeric($ts) ? (int)$ts : strtotime((string)$ts);
        if (!$t) return null;
        return date('c', $t);
    }

    private function timeAgo($iso)
    {
        if (!$iso) return '';
        $then = strtotime($iso);
        if (!$then) return '';
        $now  = time();
        $diff = $now - $then;
        if ($diff < 0) $diff = 0;

        if ($diff < 60)        return $diff . 's ago';
        if ($diff < 3600)      return floor($diff / 60) . 'm ago';
        if ($diff < 86400)     return floor($diff / 3600) . 'h ago';
        if ($diff < 604800)    return floor($diff / 86400) . 'd ago';
        if ($diff < 2592000)   return floor($diff / 604800) . 'w ago';
        if ($diff < 31536000)  return floor($diff / 2592000) . 'mo ago';
        return floor($diff / 31536000) . 'y ago';
    }
}
