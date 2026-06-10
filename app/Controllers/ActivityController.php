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
        // Resolve the viewer's clinic scope (shared with page()).
        list($clinicId, $allowedActorIds) = $this->resolveClinicScope($user);

        $events = [];

        // -- Source 1: consultation_notes (clinic-scoped via parent appointment) --
        try {
            $events = array_merge($events, $this->fetchConsultationNoteEvents($clinicId, $limit));
        } catch (\Throwable $e) {
            error_log("ActivityController::feed consultation_notes skipped: " . $e->getMessage());
        }

        // -- Source 2: appointment actions from activity_log (REAL actor) ---
        try {
            $events = array_merge($events, $this->fetchActivityLogEvents($clinicId, $limit));
        } catch (\Throwable $e) {
            error_log("ActivityController::feed activity_log skipped: " . $e->getMessage());
        }

        // -- Source 3: alerts (actor-pool scoped — alerts.doctor_id = users.id) --
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

        // Decorate with time_ago + iso ts (after sort, so it's only done $limit times).
        // actor_is_self lets the client render the actor as a bold "You" when the
        // event was performed by the currently-logged-in user.
        $meId = (int)($user['id'] ?? 0);
        foreach ($events as &$ev) {
            $ev['ts']       = $this->toIso($ev['ts'] ?? null);
            $ev['time_ago'] = $this->timeAgo($ev['ts']);
            $ev['actor_is_self'] = ($meId > 0 && isset($ev['actor_id']) && (int)$ev['actor_id'] === $meId);
        }
        unset($ev);

        echo json_encode([
            'success' => true,
            'events'  => $events
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * GET /api/activity/page?page=1&per_page=20&type=all&from=YYYY-MM-DD&to=YYYY-MM-DD&q=...
     *
     * Paginated + filtered variant of feed() for the dedicated Activities page.
     * Same clinic scope, same source fetchers, plus:
     *   - type   : all | appointment | consultation_note | alert | todo
     *   - from/to: inclusive date range (applied per-source in SQL)
     *   - q      : optional case-insensitive substring match on actor/patient/action
     * Pagination is windowed across the merged 4-source feed: each selected source
     * is fetched up to (page*per_page) rows, merged, sorted DESC, then sliced.
     *
     * Response: { success, events:[...], page, per_page, has_more }
     */
    public function page()
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        if ($perPage < 1)  $perPage = 1;
        if ($perPage > 50) $perPage = 50;

        $type = isset($_GET['type']) ? strtolower(trim((string)$_GET['type'])) : 'all';
        $validTypes = ['all', 'appointment', 'consultation_note', 'alert', 'todo'];
        if (!in_array($type, $validTypes, true)) $type = 'all';

        // Date filters — accept YYYY-MM-DD only (defensive).
        $from = (isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) ? $_GET['from'] : null;
        $to   = (isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']))   ? $_GET['to']   : null;
        $q    = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

        // Window: fetch enough from each source to cover up to the requested page,
        // capped so a deep page can't run away. +1 row lets us detect has_more.
        $window = min($page * $perPage + 1, 600);

        list($clinicId, $allowedActorIds) = $this->resolveClinicScope($user);

        $events = [];
        $want = function ($t) use ($type) { return $type === 'all' || $type === $t; };
        try {
            if ($want('consultation_note')) $events = array_merge($events, $this->fetchConsultationNoteEvents($clinicId, $window, $from, $to));
            if ($want('appointment'))       $events = array_merge($events, $this->fetchActivityLogEvents($clinicId, $window, $from, $to));
            if ($want('alert'))             $events = array_merge($events, $this->fetchAlertEvents($allowedActorIds, $window, $from, $to));
            if ($want('todo'))              $events = array_merge($events, $this->fetchTodoEvents((int)$user['id'], $window, $from, $to));
        } catch (\Throwable $e) {
            error_log('ActivityController::page source error: ' . $e->getMessage());
        }

        // Optional text search (post-merge — the sources don't share a searchable column).
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $events = array_values(array_filter($events, function ($ev) use ($needle) {
                $hay = mb_strtolower(($ev['actor_name'] ?? '') . ' ' . ($ev['patient_name'] ?? '') . ' ' . ($ev['action'] ?? '') . ' ' . ($ev['target_label'] ?? ''));
                return mb_strpos($hay, $needle) !== false;
            }));
        }

        usort($events, function ($a, $b) {
            $ta = isset($a['ts']) ? strtotime($a['ts']) : 0;
            $tb = isset($b['ts']) ? strtotime($b['ts']) : 0;
            if ($ta === $tb) return 0;
            return ($ta < $tb) ? 1 : -1;
        });

        $hasMore = count($events) > $page * $perPage;
        $slice   = array_slice($events, ($page - 1) * $perPage, $perPage);

        $meId = (int)($user['id'] ?? 0);
        foreach ($slice as &$ev) {
            $ev['ts']            = $this->toIso($ev['ts'] ?? null);
            $ev['time_ago']      = $this->timeAgo($ev['ts']);
            $ev['actor_is_self'] = ($meId > 0 && isset($ev['actor_id']) && (int)$ev['actor_id'] === $meId);
        }
        unset($ev);

        echo json_encode([
            'success'  => true,
            'events'   => $slice,
            'page'     => $page,
            'per_page' => $perPage,
            'has_more' => $hasMore
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Resolve the viewer's clinic scope. Returns [clinicId, allowedActorIds]:
     *  - clinicId       → scopes APPOINTMENTS/activity_log/notes by clinic_id
     *                     (doctors aren't pinned to a clinic, so an actor-pool
     *                     filter would miss every doctor-owned row).
     *  - allowedActorIds → the clinic's user ids, used to scope ALERTS
     *                     (alerts.doctor_id stores a users.id).
     * A viewer with no clinic (most doctors) → both null → sees everything.
     */
    private function resolveClinicScope($user)
    {
        $clinicId = null;
        $allowedActorIds = null;
        try {
            if ($this->columnExists('users', 'clinic_id')) {
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
            error_log("ActivityController::resolveClinicScope fallback: " . $e->getMessage());
            $clinicId = null;
            $allowedActorIds = null;
        }
        return [$clinicId, $allowedActorIds];
    }

    // ======================================================================
    //  Source fetchers
    // ======================================================================

    /**
     * consultation_notes — "Dr. X added a consultation note for {patient}"
     */
    private function fetchConsultationNoteEvents($clinicId, $limit, $from = null, $to = null)
    {
        if (!$this->tableExists('consultation_notes')) {
            return [];
        }

        $hasCreatedAt = $this->columnExists('consultation_notes', 'created_at');

        if (!$hasCreatedAt) {
            return [];
        }

        // consultation_notes has neither doctor_id nor patient_id — both live on
        // the parent appointment. Derive actor (appointment.doctor_id → doctors.id
        // → doctors.user_id) and patient (appointment.patient_id) by joining it.
        $sql = "SELECT cn.id AS row_id,
                       d.user_id    AS actor_id,
                       a.patient_id AS patient_id,
                       cn.created_at AS ts,
                       u.name        AS actor_name,
                       u.username    AS actor_username,
                       p.first_name  AS p_first,
                       p.last_name   AS p_last,
                       p.gender      AS p_gender
                FROM consultation_notes cn
                LEFT JOIN appointments a ON a.id = cn.appointment_id
                LEFT JOIN doctors d      ON d.id = a.doctor_id
                LEFT JOIN users u        ON u.id = d.user_id
                LEFT JOIN patients p     ON p.id = a.patient_id
                WHERE 1=1";

        $params = [];
        if ($clinicId !== null && $this->columnExists('appointments', 'clinic_id')) {
            $sql .= " AND a.clinic_id = ?";
            $params[] = (int)$clinicId;
        }
        if ($from !== null) { $sql .= " AND cn.created_at >= ?"; $params[] = $from . ' 00:00:00'; }
        if ($to   !== null) { $sql .= " AND cn.created_at <= ?"; $params[] = $to   . ' 23:59:59'; }
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
                'action_code'  => 'note_added',
                'detail'       => null,
                'patient_id'   => $r['patient_id'] ? (int)$r['patient_id'] : null,
                'patient_name' => $patientLabel,
                'patient_gender' => $r['p_gender'] ?? null,
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
    /**
     * Appointment actions from activity_log — the REAL actor (so a secretary's
     * own cancel shows as "You"), and a row that survives a hard delete. Replaces
     * the old derive-from-appointments source (which attributed everything to the
     * assigned doctor and showed noisy "status Booked" rows for every appointment).
     */
    private function fetchActivityLogEvents($clinicId, $limit, $from = null, $to = null)
    {
        if (!$this->tableExists('activity_log')) {
            return [];
        }

        $sql = "SELECT al.id          AS row_id,
                       al.actor_user_id AS actor_id,
                       al.action        AS action_code,
                       al.detail        AS detail,
                       al.entity_id     AS entity_id,
                       al.patient_id    AS patient_id,
                       al.created_at    AS ts,
                       u.name           AS actor_name,
                       u.username       AS actor_username,
                       p.first_name     AS p_first,
                       p.last_name      AS p_last,
                       p.gender         AS p_gender
                FROM activity_log al
                LEFT JOIN users u    ON u.id = al.actor_user_id
                LEFT JOIN patients p ON p.id = al.patient_id
                WHERE al.entity_type = 'appointment'";
        $params = [];
        if ($clinicId !== null) {
            $sql .= " AND al.clinic_id = ?";
            $params[] = (int)$clinicId;
        }
        if ($from !== null) { $sql .= " AND al.created_at >= ?"; $params[] = $from . ' 00:00:00'; }
        if ($to   !== null) { $sql .= " AND al.created_at <= ?"; $params[] = $to   . ' 23:59:59'; }
        $sql .= " ORDER BY al.created_at DESC LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $patientLabel = trim(($r['p_first'] ?? '') . ' ' . ($r['p_last'] ?? ''));
            $out[] = [
                'id'           => 'act-' . $r['row_id'],
                'type'         => 'appointment',
                'actor_id'     => $r['actor_id'] !== null ? (int)$r['actor_id'] : null,
                'actor_name'   => $r['actor_name'] ?: ($r['actor_username'] ?: 'Someone'),
                'action'       => $this->describeActivityAction($r['action_code'], $r['detail']),
                'action_code'  => $r['action_code'],
                'detail'       => $r['detail'],
                'patient_name' => $patientLabel,
                'patient_gender' => $r['p_gender'] ?? null,
                'patient_id'     => $r['patient_id'] ? (int)$r['patient_id'] : null,
                'appointment_id' => $r['entity_id'] ? (int)$r['entity_id'] : null,
                'target_label' => $patientLabel !== '' ? $patientLabel : ($r['patient_id'] ? ('Patient #' . $r['patient_id']) : ''),
                'target_link'  => $r['entity_id'] ? ('/appointment/' . (int)$r['entity_id']) : '',
                'ts'           => $r['ts'],
            ];
        }
        return $out;
    }

    private function describeActivityAction($action, $detail)
    {
        switch ($action) {
            case 'booked':         return 'booked an appointment';
            case 'status_changed': return 'updated appointment status to "' . ($detail ?? '') . '"';
            case 'deleted':        return 'deleted an appointment';
            case 'rescheduled':    return 'rescheduled an appointment' . ($detail ? ' ' . $detail : '');
            case 'edited':         return 'edited an appointment' . ($detail ? ' ' . $detail : '');
            case 'checked_in':     return 'checked in the patient';
            default:               return (string)$action;
        }
    }

    private function fetchAppointmentEvents($clinicId, $limit)
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

        // appointments.doctor_id is a doctors.id (NOT a users.id) — resolve the
        // actor user through doctors.user_id, and clinic-scope on that user id.
        // (Joining users directly on a.doctor_id silently matched nothing and
        // emptied the whole feed for clinic-scoped users.)
        $actorCol  = $hasDoctorId  ? 'd.user_id'   : 'NULL';
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
                LEFT JOIN doctors d  ON d.id = a.doctor_id
                LEFT JOIN users u    ON u.id = d.user_id
                LEFT JOIN patients p ON p.id = {$patientCol}
                WHERE {$tsCol} IS NOT NULL";

        $params = [];
        // Clinic scope: appointments are clinic-tagged, so filter on the row's
        // own clinic_id (works even though doctors aren't pinned to a clinic).
        if ($clinicId !== null && $this->columnExists('appointments', 'clinic_id')) {
            $sql .= " AND a.clinic_id = ?";
            $params[] = (int)$clinicId;
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
    private function fetchAlertEvents($allowedActorIds, $limit, $from = null, $to = null)
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
                       p.last_name   AS p_last,
                       p.gender      AS p_gender
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
        if ($from !== null) { $sql .= " AND al.created_at >= ?"; $params[] = $from . ' 00:00:00'; }
        if ($to   !== null) { $sql .= " AND al.created_at <= ?"; $params[] = $to   . ' 23:59:59'; }
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
                'action_code'  => 'alert_created',
                'detail'       => null,
                'patient_id'   => $r['patient_id'] ? (int)$r['patient_id'] : null,
                'patient_name' => $patientLabel,
                'patient_gender' => $r['p_gender'] ?? null,
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
    private function fetchTodoEvents($userId, $limit, $from = null, $to = null)
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
                WHERE t.user_id = ?";
        $params = [(int)$userId];
        if ($from !== null) { $sql .= " AND t.created_at >= ?"; $params[] = $from . ' 00:00:00'; }
        if ($to   !== null) { $sql .= " AND t.created_at <= ?"; $params[] = $to   . ' 23:59:59'; }
        $sql .= " ORDER BY t.created_at DESC LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
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
                'action_code'  => 'todo_created',
                'detail'       => $title,
                'patient_id'   => $r['patient_id'] ? (int)$r['patient_id'] : null,
                'patient_name' => $patientLabel,
                'patient_gender' => null,
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
            // information_schema instead of `SHOW TABLES LIKE ?` (rejected by the
            // prepared-statement protocol under native prepares — see columnExists).
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = ?"
            );
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
            // information_schema (NOT `SHOW COLUMNS ... LIKE ?`): SHOW statements
            // are rejected by MariaDB's prepared-statement protocol when PDO native
            // prepares are on, which made every columnExists() throw → return false
            // → the whole activity feed silently came back empty.
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?"
            );
            $stmt->execute([$table, $column]);
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
