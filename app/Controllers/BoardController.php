<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Config\Database;
use PDO;

/**
 * BoardController — Trello-style patient board.
 *
 * Endpoints
 *   GET    /doctor/board                         — board page (HTML)
 *   GET    /api/board/columns                    — list columns
 *   POST   /api/board/columns                    — create a column
 *   PUT    /api/board/columns/{id}               — rename / recolor / re-sort
 *   DELETE /api/board/columns/{id}               — delete column (non-system only)
 *   GET    /api/board/cards                      — patient cards grouped by column
 *                                                  ?q= search, ?clinic_id= filter
 *   POST   /api/board/move                       — move a patient between columns
 *                                                  body: {patient_id, to_column_id,
 *                                                         sort_order, if_moved_at?}
 *   GET    /api/board/auto-place/{patient_id}    — server-side default placement
 *
 * Concurrency: /api/board/move accepts an optional `if_moved_at` (ISO-8601
 * read by the client). If the server's stored `moved_at` for that patient
 * doesn't match, the server returns 409 — the client must refetch.
 *
 * Permissions: doctor + admin can read & move. Only doctor + admin can
 * create / rename / delete columns. Secretary role is read-only on the
 * board to keep the workflow column structure under clinician control.
 */
class BoardController
{
    private $auth;
    private $view;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->pdo = Database::getInstance()->getConnection();
    }

    // ---------- Page render -------------------------------------------------

    public function index()
    {
        $this->auth->requireRole(['doctor', 'admin', 'secretary']);
        $user = $this->auth->user();

        $content = $this->view->render('doctor/board', [
            'user' => $user,
        ]);

        echo $this->view->render('layouts/main', [
            'title'        => 'Roaya Clinic | Patient Boards',
            'pageTitle'    => 'Patient Boards',
            'pageSubtitle' => 'Track your patients across stages of care',
            'content'      => $content,
        ]);
    }

    // ---------- JSON helpers ------------------------------------------------

    private function json($payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function requireApi(array $roles = ['doctor', 'admin', 'secretary']): array
    {
        if (!$this->auth->check()) {
            $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }
        $user = $this->auth->user();
        if (!in_array($user['role'], $roles, true)) {
            $this->json(['ok' => false, 'error' => 'Permission denied'], 403);
        }
        return $user;
    }

    private function readBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '', true);
        return is_array($data) ? $data : ($_POST ?: []);
    }

    // ---------- Columns -----------------------------------------------------

    public function listColumns()
    {
        $this->requireApi();
        $stmt = $this->pdo->query("
            SELECT id, clinic_id, doctor_id, name, color, icon, sort_order, is_system
            FROM patient_board_columns
            ORDER BY sort_order ASC, id ASC
        ");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $this->json(['ok' => true, 'data' => $cols]);
    }

    /**
     * Lightweight per-column card counts for the dashboard "Board Snapshot" widget.
     * One query: columns LEFT JOIN assignments, grouped.
     */
    public function snapshot()
    {
        $this->requireApi();
        $stmt = $this->pdo->query("
            SELECT c.id, c.name, c.color, c.sort_order,
                   COUNT(a.patient_id) AS card_count
            FROM patient_board_columns c
            LEFT JOIN patient_board_assignments a ON a.column_id = c.id
            GROUP BY c.id, c.name, c.color, c.sort_order
            ORDER BY c.sort_order ASC, c.id ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $cols = array_map(function ($r) {
            return [
                'id'    => (int) $r['id'],
                'name'  => $r['name'],
                'color' => $r['color'] ?: '#64748b',
                'count' => (int) $r['card_count'],
            ];
        }, $rows);
        $this->json(['ok' => true, 'data' => $cols]);
    }

    public function createColumn()
    {
        $this->requireApi(['doctor', 'admin']);
        $b = $this->readBody();
        $name  = trim((string) ($b['name'] ?? ''));
        $color = trim((string) ($b['color'] ?? '#64748b'));
        if ($name === '' || mb_strlen($name) > 80) {
            $this->json(['ok' => false, 'error' => 'name is required (1–80 chars)'], 400);
        }
        if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
            $color = '#64748b';
        }

        $stmt = $this->pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 10 AS next FROM patient_board_columns");
        $next = (int) $stmt->fetchColumn();

        $ins = $this->pdo->prepare("
            INSERT INTO patient_board_columns (name, color, sort_order, is_system)
            VALUES (?, ?, ?, 0)
        ");
        $ins->execute([$name, $color, $next]);
        $this->json(['ok' => true, 'data' => ['id' => (int) $this->pdo->lastInsertId()]]);
    }

    public function updateColumn($id)
    {
        $this->requireApi(['doctor', 'admin']);
        $id = (int) $id;
        $b = $this->readBody();
        $fields = [];
        $params = [];
        if (isset($b['name'])) {
            $name = trim((string) $b['name']);
            if ($name === '' || mb_strlen($name) > 80) {
                $this->json(['ok' => false, 'error' => 'name invalid'], 400);
            }
            $fields[] = 'name = ?';
            $params[] = $name;
        }
        if (isset($b['color'])) {
            $color = trim((string) $b['color']);
            if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
                $this->json(['ok' => false, 'error' => 'color invalid'], 400);
            }
            $fields[] = 'color = ?';
            $params[] = $color;
        }
        if (isset($b['sort_order'])) {
            $fields[] = 'sort_order = ?';
            $params[] = (int) $b['sort_order'];
        }
        if (!$fields) {
            $this->json(['ok' => false, 'error' => 'no fields to update'], 400);
        }
        $params[] = $id;
        $sql = 'UPDATE patient_board_columns SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $this->pdo->prepare($sql)->execute($params);
        $this->json(['ok' => true]);
    }

    public function deleteColumn($id)
    {
        $this->requireApi(['doctor', 'admin']);
        $id = (int) $id;

        // System columns can be renamed / recolored but not deleted — they're
        // the workflow scaffold the seed migration created.
        $row = $this->pdo->prepare("SELECT is_system FROM patient_board_columns WHERE id = ?");
        $row->execute([$id]);
        $r = $row->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            $this->json(['ok' => false, 'error' => 'column not found'], 404);
        }
        if ((int) $r['is_system'] === 1) {
            $this->json(['ok' => false, 'error' => 'system column cannot be deleted'], 422);
        }

        // Any assignments on this column become orphaned — the FK has
        // ON DELETE CASCADE so they're cleared automatically.
        $this->pdo->prepare("DELETE FROM patient_board_columns WHERE id = ?")->execute([$id]);
        $this->json(['ok' => true]);
    }

    // ---------- Cards (patients grouped by column) --------------------------

    public function listCards()
    {
        $this->requireApi();
        $q        = trim((string) ($_GET['q'] ?? ''));
        $clinicId = isset($_GET['clinic_id']) && $_GET['clinic_id'] !== '' ? (int) $_GET['clinic_id'] : null;

        // Patients with their assignment + last-clinic info. Patients that
        // have no assignment row at all default to the lowest-sort_order
        // is_system=1 column ("New Consultation" in the seed).
        $sql = "
            SELECT p.id AS patient_id,
                   p.first_name, p.last_name, p.dob, p.gender, p.phone,
                   pba.column_id,
                   pba.sort_order,
                   pba.moved_at,
                   last_appt.clinic_id AS last_clinic_id
            FROM patients p
            LEFT JOIN patient_board_assignments pba ON pba.patient_id = p.id
            LEFT JOIN (
                SELECT a.patient_id, a.clinic_id,
                       ROW_NUMBER() OVER (
                           PARTITION BY a.patient_id
                           ORDER BY a.date DESC, a.start_time DESC, a.id DESC
                       ) AS rn
                FROM appointments a
                WHERE a.clinic_id IS NOT NULL
            ) last_appt ON last_appt.patient_id = p.id AND last_appt.rn = 1
        ";
        $where = [];
        $args = [];
        if ($q !== '') {
            $where[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR p.phone LIKE ?)";
            $like = '%' . $q . '%';
            $args = array_merge($args, [$like, $like, $like]);
        }
        if ($clinicId !== null) {
            $where[] = "last_appt.clinic_id = ?";
            $args[] = $clinicId;
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY pba.column_id IS NULL, pba.column_id, pba.sort_order, p.id DESC LIMIT 800';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Default-column lookup (lowest sort_order is_system=1 row).
        $defaultCol = $this->pdo->query("
            SELECT id FROM patient_board_columns
            WHERE is_system = 1
            ORDER BY sort_order ASC LIMIT 1
        ")->fetchColumn();
        $defaultCol = $defaultCol ? (int) $defaultCol : null;

        $byColumn = [];
        foreach ($rows as $r) {
            $col = $r['column_id'] !== null ? (int) $r['column_id'] : $defaultCol;
            if ($col === null) continue;  // no columns configured at all
            $byColumn[$col][] = [
                'patient_id'    => (int) $r['patient_id'],
                'name'          => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                'phone'         => $r['phone'],
                'gender'        => $r['gender'],
                'dob'           => $r['dob'],
                'profile'       => null,
                'last_clinic_id' => $r['last_clinic_id'] !== null ? (int) $r['last_clinic_id'] : null,
                'moved_at'      => $r['moved_at'],
                'sort_order'    => $r['column_id'] !== null ? (int) $r['sort_order'] : 0,
            ];
        }

        $this->json(['ok' => true, 'data' => ['by_column' => $byColumn, 'default_column' => $defaultCol]]);
    }

    // ---------- Move --------------------------------------------------------

    public function move()
    {
        $user = $this->requireApi();
        $b = $this->readBody();
        $patientId    = isset($b['patient_id']) ? (int) $b['patient_id'] : 0;
        $toColumnId   = isset($b['to_column_id']) ? (int) $b['to_column_id'] : 0;
        $sortOrder    = isset($b['sort_order']) ? (int) $b['sort_order'] : 0;
        $ifMovedAt    = isset($b['if_moved_at']) ? trim((string) $b['if_moved_at']) : '';

        if ($patientId <= 0 || $toColumnId <= 0) {
            $this->json(['ok' => false, 'error' => 'patient_id and to_column_id required'], 400);
        }

        // Verify both ends exist.
        $colStmt = $this->pdo->prepare("SELECT id FROM patient_board_columns WHERE id = ?");
        $colStmt->execute([$toColumnId]);
        if (!$colStmt->fetch()) {
            $this->json(['ok' => false, 'error' => 'column not found'], 404);
        }
        $patStmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
        $patStmt->execute([$patientId]);
        if (!$patStmt->fetch()) {
            $this->json(['ok' => false, 'error' => 'patient not found'], 404);
        }

        // Optimistic-concurrency check.
        if ($ifMovedAt !== '') {
            $exist = $this->pdo->prepare("SELECT moved_at FROM patient_board_assignments WHERE patient_id = ?");
            $exist->execute([$patientId]);
            $cur = $exist->fetchColumn();
            if ($cur !== false && $cur !== null && $cur !== $ifMovedAt) {
                $this->json([
                    'ok' => false,
                    'error' => 'stale',
                    'current_moved_at' => $cur,
                ], 409);
            }
        }

        // Upsert.
        $up = $this->pdo->prepare("
            INSERT INTO patient_board_assignments
                (patient_id, column_id, sort_order, moved_at, moved_by)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)
            ON DUPLICATE KEY UPDATE
                column_id = VALUES(column_id),
                sort_order = VALUES(sort_order),
                moved_at = CURRENT_TIMESTAMP,
                moved_by = VALUES(moved_by)
        ");
        $up->execute([$patientId, $toColumnId, $sortOrder, $user['id']]);

        // Best-effort audit row if the timeline-events table exists.
        try {
            $this->pdo->prepare("
                INSERT INTO timeline_events (event_type, actor_user_id, patient_id, payload, created_at)
                VALUES ('board_move', ?, ?, ?, CURRENT_TIMESTAMP)
            ")->execute([
                $user['id'],
                $patientId,
                json_encode(['to_column_id' => $toColumnId, 'sort_order' => $sortOrder]),
            ]);
        } catch (\Exception $e) {
            // timeline_events optional — swallow.
        }

        // Return the fresh moved_at so the client can pin its `if_moved_at`
        // for the next move.
        $fresh = $this->pdo->prepare("SELECT moved_at FROM patient_board_assignments WHERE patient_id = ?");
        $fresh->execute([$patientId]);
        $this->json(['ok' => true, 'data' => ['moved_at' => $fresh->fetchColumn()]]);
    }

    /**
     * One-click auto-place: scores every column for the patient based on
     *  (a) tag ↔ column-name keyword overlap,
     *  (b) most recent appointment (visit_type / status / recency),
     *  (c) total non-cancelled visit count,
     *  (d) per-column heuristics on common seed-column keywords,
     * and assigns the patient to the highest-scoring column (replacing any
     * existing assignment). Returns the chosen column + a human reason list.
     */
    public function autoPlace($patientId)
    {
        $user = $this->requireApi();
        $patientId = (int) $patientId;
        if ($patientId <= 0) {
            $this->json(['ok' => false, 'error' => 'bad patient_id'], 400);
        }

        // Patient must exist.
        $patStmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
        $patStmt->execute([$patientId]);
        if (!$patStmt->fetch()) {
            $this->json(['ok' => false, 'error' => 'patient not found'], 404);
        }

        // Load all columns (caller's view: every clinic column is a candidate).
        $cols = $this->pdo->query("
            SELECT id, name, sort_order, is_system
            FROM patient_board_columns
            ORDER BY sort_order ASC, id ASC
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$cols) {
            $this->json(['ok' => false, 'error' => 'no columns configured'], 422);
        }

        // ---- Patient signals --------------------------------------------
        // Tags (defensive: tag system may not be installed everywhere).
        $patientTags = [];
        try {
            $tagStmt = $this->pdo->prepare("
                SELECT LOWER(t.name) AS name
                FROM patient_tag_assignments pta
                JOIN patient_tags t ON t.id = pta.tag_id
                WHERE pta.patient_id = ?
            ");
            $tagStmt->execute([$patientId]);
            foreach ($tagStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
                $patientTags[] = trim((string) $r['name']);
            }
        } catch (\Throwable $e) { /* tag table absent → no tag signal */ }

        // Last non-cancelled appointment.
        $lastAppt = null;
        try {
            $lastStmt = $this->pdo->prepare("
                SELECT visit_type, status, date
                FROM appointments
                WHERE patient_id = ?
                  AND status NOT IN ('Cancelled','NoShow')
                ORDER BY date DESC, start_time DESC
                LIMIT 1
            ");
            $lastStmt->execute([$patientId]);
            $lastAppt = $lastStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) { /* missing column / table → no recency signal */ }

        // Visit count + future booked flag.
        $visitCount = 0; $hasFuture = false;
        try {
            $cs = $this->pdo->prepare("
                SELECT
                    SUM(CASE WHEN status NOT IN ('Cancelled','NoShow') THEN 1 ELSE 0 END) AS visit_count,
                    SUM(CASE WHEN date >= CURRENT_DATE AND status NOT IN ('Cancelled','NoShow') THEN 1 ELSE 0 END) AS future
                FROM appointments WHERE patient_id = ?
            ");
            $cs->execute([$patientId]);
            $row = $cs->fetch(PDO::FETCH_ASSOC) ?: [];
            $visitCount = (int) ($row['visit_count'] ?? 0);
            $hasFuture  = ((int) ($row['future'] ?? 0)) > 0;
        } catch (\Throwable $e) { /* appointment shape differs → ignore */ }

        // ---- Score each column ------------------------------------------
        $tok = function (string $s): array {
            return array_values(array_filter(
                preg_split('/[\s\-_\/&·,.()]+/u', mb_strtolower($s)),
                fn($t) => $t !== ''
            ));
        };
        $best = ['col' => null, 'score' => -1, 'reasons' => []];

        foreach ($cols as $col) {
            $score = 0; $reasons = [];
            $nameTokens = $tok($col['name']);

            // (a) tag overlap with column name
            foreach ($patientTags as $tagName) {
                if (in_array($tagName, ['priority', 'vip', 'starred'], true)) continue;
                $tagTokens = $tok($tagName);
                $overlap = array_intersect($tagTokens, $nameTokens);
                if (!empty($overlap)) {
                    $score += 50;
                    $reasons[] = 'tag "' . $tagName . '" matches column';
                }
            }

            // (b) recent activity heuristics
            if ($lastAppt) {
                $vt = strtolower((string) ($lastAppt['visit_type'] ?? ''));
                $st = strtolower((string) ($lastAppt['status'] ?? ''));
                if (in_array('follow', $nameTokens, true) || in_array('followup', $nameTokens, true)) {
                    if (str_contains($vt, 'follow')) { $score += 40; $reasons[] = 'last visit was a follow-up'; }
                }
                if (in_array('post', $nameTokens, true) || in_array('postop', $nameTokens, true)) {
                    if (str_contains($vt, 'procedure') || str_contains($vt, 'surgery')) {
                        $score += 35; $reasons[] = 'recent procedure/surgery';
                    }
                }
                if (in_array('pre', $nameTokens, true) || in_array('preop', $nameTokens, true) || in_array('surgical', $nameTokens, true)) {
                    foreach ($patientTags as $t) {
                        if (str_contains($t, 'surg')) { $score += 35; $reasons[] = 'surgical tag → pre-op column'; break; }
                    }
                }
                if (in_array('imaging', $nameTokens, true) || in_array('investigations', $nameTokens, true) || in_array('investigation', $nameTokens, true)) {
                    foreach ($patientTags as $t) {
                        if (str_contains($t, 'imag') || str_contains($t, 'scan') || str_contains($t, 'mri') || str_contains($t, 'ct')) {
                            $score += 30; $reasons[] = 'imaging tag → imaging column'; break;
                        }
                    }
                }
                if (in_array('discharge', $nameTokens, true) || in_array('completed', $nameTokens, true) || in_array('long-term', $nameTokens, true)) {
                    if ($st === 'completed' && !$hasFuture) {
                        $score += 25; $reasons[] = 'no future appointments → long-term/discharge column';
                    }
                }
            }

            // (c) visit count
            if ($visitCount === 0 && (in_array('new', $nameTokens, true) || in_array('consultation', $nameTokens, true))) {
                $score += 25; $reasons[] = 'first-time patient → new consultation column';
            }
            if ($visitCount >= 4 && (in_array('follow', $nameTokens, true) || in_array('long-term', $nameTokens, true) || in_array('long', $nameTokens, true))) {
                $score += 15; $reasons[] = 'returning patient (' . $visitCount . ' visits)';
            }

            // (d) ultra-weak fallback marker for the default system column
            if ($score === 0 && (int) ($col['is_system'] ?? 0) === 1 && $best['score'] <= 0) {
                $score = 1;
            }

            if ($score > $best['score']) {
                $best = ['col' => $col, 'score' => $score, 'reasons' => $reasons];
            }
        }

        if (!$best['col']) {
            // Absolute fallback: lowest sort_order.
            $best['col'] = $cols[0];
            $best['reasons'] = ['no signal — placed in default column'];
        }
        $targetCol = (int) $best['col']['id'];
        $confidence = max(0.05, min(1.0, $best['score'] / 100));

        // ---- Upsert (replaces existing assignment) ----------------------
        $up = $this->pdo->prepare("
            INSERT INTO patient_board_assignments
                (patient_id, column_id, sort_order, moved_at, moved_by)
            VALUES (?, ?, 0, CURRENT_TIMESTAMP, ?)
            ON DUPLICATE KEY UPDATE
                column_id  = VALUES(column_id),
                sort_order = 0,
                moved_at   = CURRENT_TIMESTAMP,
                moved_by   = VALUES(moved_by)
        ");
        $up->execute([$patientId, $targetCol, $user['id'] ?? null]);

        try {
            $this->pdo->prepare("
                INSERT INTO timeline_events (event_type, actor_user_id, patient_id, payload, created_at)
                VALUES ('board_auto_place', ?, ?, ?, CURRENT_TIMESTAMP)
            ")->execute([
                $user['id'] ?? null, $patientId,
                json_encode(['to_column_id' => $targetCol, 'score' => $best['score'], 'reasons' => $best['reasons']]),
            ]);
        } catch (\Throwable $e) { /* timeline_events optional */ }

        $this->json(['ok' => true, 'data' => [
            'column_id'   => $targetCol,
            'column_name' => $best['col']['name'],
            'confidence'  => round($confidence, 2),
            'reasons'     => $best['reasons'],
        ]]);
    }

    // =====================================================================
    //  Two-level board API (overview → detail). The redesigned UI treats
    //  each `patient_board_columns` row as a "board" the doctor opens to
    //  see the patients inside it.
    // =====================================================================

    /** Default fallback board: lowest sort_order, system rows preferred. */
    private function defaultBoardId(): ?int
    {
        $id = $this->pdo->query("
            SELECT id FROM patient_board_columns
            ORDER BY is_system DESC, sort_order ASC, id ASC
            LIMIT 1
        ")->fetchColumn();
        return $id ? (int) $id : null;
    }

    private function boardRow(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, description, color, icon, sort_order, is_system
            FROM patient_board_columns WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** GET /api/board/boards — overview list with patient counts. */
    public function listBoards()
    {
        $this->requireApi();
        $rows = $this->pdo->query("
            SELECT col.id, col.name, col.description, col.color, col.icon,
                   col.sort_order, col.is_system,
                   COUNT(pba.patient_id) AS patient_count
            FROM patient_board_columns col
            LEFT JOIN patient_board_assignments pba ON pba.column_id = col.id
            GROUP BY col.id, col.name, col.description, col.color, col.icon,
                     col.sort_order, col.is_system
            ORDER BY col.sort_order ASC, col.id ASC
        ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $default = $this->defaultBoardId();

        // Top-3 patient previews per column (ROW_NUMBER over sort_order/moved_at)
        $previews = [];
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM (
                    SELECT
                        pba.column_id,
                        p.id AS patient_id,
                        p.first_name,
                        p.last_name,
                        (SELECT COUNT(*) FROM comments c
                            WHERE c.commentable_type = 'board_card'
                              AND c.commentable_id = p.id
                              AND c.deleted_at IS NULL) AS comments_count,
                        (SELECT COUNT(*) FROM comment_attachments ca
                            JOIN comments c2 ON c2.id = ca.comment_id
                            WHERE c2.commentable_type = 'board_card'
                              AND c2.commentable_id = p.id
                              AND c2.deleted_at IS NULL) AS attachments_count,
                        ROW_NUMBER() OVER (
                            PARTITION BY pba.column_id
                            ORDER BY pba.sort_order ASC, pba.moved_at DESC, p.id DESC
                        ) AS rn
                    FROM patient_board_assignments pba
                    JOIN patients p ON p.id = pba.patient_id
                ) t
                WHERE t.rn <= 3
                ORDER BY t.column_id, t.rn
            ");
            $previewRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($previewRows as $pr) {
                $cid = (int) $pr['column_id'];
                $first = trim((string) ($pr['first_name'] ?? ''));
                $last  = trim((string) ($pr['last_name'] ?? ''));
                $initials = strtoupper(
                    mb_substr($first !== '' ? $first : 'P', 0, 1) .
                    ($last !== '' ? mb_substr($last, 0, 1) : '')
                );
                $previews[$cid][] = [
                    'patient_id'        => (int) $pr['patient_id'],
                    'name'              => trim($first . ' ' . $last),
                    'initials'          => $initials,
                    'comments_count'    => (int) $pr['comments_count'],
                    'attachments_count' => (int) $pr['attachments_count'],
                ];
            }
        } catch (\Throwable $e) {
            // ROW_NUMBER requires MariaDB 10.2+ / MySQL 8 — should be present, but degrade
            // gracefully: skip previews and still return per-column counts.
            $previews = [];
        }

        foreach ($rows as &$r) {
            $r['id']            = (int) $r['id'];
            $r['sort_order']    = (int) $r['sort_order'];
            $r['is_system']     = (int) $r['is_system'];
            $r['patient_count'] = (int) $r['patient_count'];
            $r['is_default']    = ($r['id'] === $default);
            $r['patients']      = $previews[$r['id']] ?? [];
        }
        $this->json(['ok' => true, 'data' => $rows]);
    }

    /** GET /api/board/boards/{id}/cards?q=&sort= — patients inside one board. */
    public function boardCards($id)
    {
        $this->requireApi();
        $id = (int) $id;
        $board = $this->boardRow($id);
        if (!$board) {
            $this->json(['ok' => false, 'error' => 'Board not found'], 404);
        }

        $q    = trim((string) ($_GET['q'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'moved');

        $sortSql = [
            'name'   => 'p.first_name ASC, p.last_name ASC',
            'recent' => 'last_visit IS NULL, last_visit DESC',
            'visits' => 'visit_count DESC',
            'moved'  => 'pba.moved_at DESC',
        ][$sort] ?? 'pba.moved_at DESC';

        $where = ['pba.column_id = ?'];
        $args  = [$id];
        if ($q !== '') {
            $where[] = '(p.first_name LIKE ? OR p.last_name LIKE ? OR p.phone LIKE ?)';
            $like = '%' . $q . '%';
            array_push($args, $like, $like, $like);
        }

        $sql = "
            SELECT p.id AS patient_id, p.first_name, p.last_name, p.dob,
                   p.gender, p.phone, p.alt_phone,
                   pba.sort_order, pba.moved_at,
                   COUNT(DISTINCT CASE WHEN a.status NOT IN ('Cancelled','NoShow') THEN a.id END) AS visit_count,
                   MAX(CASE WHEN a.status NOT IN ('Cancelled','NoShow') THEN a.date END) AS last_visit,
                   (SELECT COUNT(*) FROM comments c
                      WHERE c.commentable_type = 'board_card'
                        AND c.commentable_id = p.id
                        AND c.deleted_at IS NULL) AS notes_count
            FROM patient_board_assignments pba
            JOIN patients p ON p.id = pba.patient_id
            LEFT JOIN appointments a ON a.patient_id = p.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY p.id, p.first_name, p.last_name, p.dob, p.gender,
                     p.phone, p.alt_phone, pba.sort_order, pba.moved_at
            ORDER BY {$sortSql}
            LIMIT 500
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $cards = array_map(function ($r) {
            return [
                'patient_id'  => (int) $r['patient_id'],
                'first_name'  => $r['first_name'],
                'last_name'   => $r['last_name'],
                'name'        => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                'dob'         => $r['dob'],
                'gender'      => $r['gender'],
                'phone'       => $r['phone'],
                'alt_phone'   => $r['alt_phone'],
                'visit_count' => (int) $r['visit_count'],
                'last_visit'  => $r['last_visit'],
                'notes_count' => (int) $r['notes_count'],
                'moved_at'    => $r['moved_at'],
            ];
        }, $rows);

        $board['id']        = (int) $board['id'];
        $board['is_system'] = (int) $board['is_system'];
        $this->json(['ok' => true, 'data' => ['board' => $board, 'cards' => $cards]]);
    }

    private function validateBoardInput(array $b): array
    {
        $name  = trim((string) ($b['name'] ?? ''));
        $desc  = trim((string) ($b['description'] ?? ''));
        $color = trim((string) ($b['color'] ?? '#0ea5e9'));
        $icon  = trim((string) ($b['icon']  ?? 'bi-kanban'));
        if ($name === '' || mb_strlen($name) > 80) {
            $this->json(['ok' => false, 'error' => 'Board name is required (1–80 characters)'], 400);
        }
        if (mb_strlen($desc) > 255) {
            $this->json(['ok' => false, 'error' => 'Description is too long (255 characters max)'], 400);
        }
        if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
            $color = '#0ea5e9';
        }
        // Icon: must be a Bootstrap Icons class name (lowercase + hyphens + digits)
        // prefixed `bi-`. Length-capped to match the DB column.
        if (!preg_match('/^bi-[a-z0-9][a-z0-9-]{0,36}$/', $icon)) {
            $icon = 'bi-kanban';
        }
        return [
            'name'        => $name,
            'description' => ($desc === '' ? null : $desc),
            'color'       => $color,
            'icon'        => $icon,
        ];
    }

    /** POST /api/board/boards */
    public function createBoard()
    {
        $this->requireApi(['doctor', 'admin']);
        $d = $this->validateBoardInput($this->readBody());
        $next = (int) $this->pdo->query("SELECT COALESCE(MAX(sort_order),0)+10 FROM patient_board_columns")->fetchColumn();
        $ins = $this->pdo->prepare("
            INSERT INTO patient_board_columns (name, description, color, icon, sort_order, is_system)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        $ins->execute([$d['name'], $d['description'], $d['color'], $d['icon'], $next]);
        $this->json(['ok' => true, 'data' => ['id' => (int) $this->pdo->lastInsertId()]]);
    }

    /** PUT /api/board/boards/{id} */
    public function updateBoard($id)
    {
        $this->requireApi(['doctor', 'admin']);
        $id = (int) $id;
        if (!$this->boardRow($id)) {
            $this->json(['ok' => false, 'error' => 'Board not found'], 404);
        }
        $d = $this->validateBoardInput($this->readBody());
        $this->pdo->prepare("
            UPDATE patient_board_columns
            SET name = ?, description = ?, color = ?, icon = ?
            WHERE id = ?
        ")->execute([$d['name'], $d['description'], $d['color'], $d['icon'], $id]);
        $this->json(['ok' => true]);
    }

    /** DELETE /api/board/boards/{id} — patients fall back to the default board. */
    public function deleteBoard($id)
    {
        $this->requireApi(['doctor', 'admin']);
        $id = (int) $id;
        if (!$this->boardRow($id)) {
            $this->json(['ok' => false, 'error' => 'Board not found'], 404);
        }

        $total = (int) $this->pdo->query("SELECT COUNT(*) FROM patient_board_columns")->fetchColumn();
        if ($total <= 1) {
            $this->json(['ok' => false, 'error' => 'Cannot delete the last remaining board'], 422);
        }
        if ($id === $this->defaultBoardId()) {
            $this->json(['ok' => false, 'error' => 'Cannot delete the default board where new patients land'], 422);
        }

        // Reassign patients in this board to the default board (no orphans).
        $fallback = $this->defaultBoardId();
        if ($fallback) {
            $this->pdo->prepare("
                UPDATE patient_board_assignments
                SET column_id = ?, sort_order = 0, moved_at = CURRENT_TIMESTAMP
                WHERE column_id = ?
            ")->execute([$fallback, $id]);
        } else {
            $this->pdo->prepare("DELETE FROM patient_board_assignments WHERE column_id = ?")->execute([$id]);
        }

        $this->pdo->prepare("DELETE FROM patient_board_columns WHERE id = ?")->execute([$id]);
        $this->json(['ok' => true]);
    }

    /** POST /api/board/boards/{id}/patients  body:{patient_id} — assign/move in. */
    public function addPatient($id)
    {
        $user = $this->requireApi(['doctor', 'admin']);
        $id = (int) $id;
        if (!$this->boardRow($id)) {
            $this->json(['ok' => false, 'error' => 'Board not found'], 404);
        }
        $b = $this->readBody();
        $patientId = (int) ($b['patient_id'] ?? 0);
        if ($patientId <= 0) {
            $this->json(['ok' => false, 'error' => 'Patient ID is required'], 400);
        }
        $pat = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
        $pat->execute([$patientId]);
        if (!$pat->fetch()) {
            $this->json(['ok' => false, 'error' => 'Patient not found'], 404);
        }
        $up = $this->pdo->prepare("
            INSERT INTO patient_board_assignments (patient_id, column_id, sort_order, moved_at, moved_by)
            VALUES (?, ?, 0, CURRENT_TIMESTAMP, ?)
            ON DUPLICATE KEY UPDATE
                column_id = VALUES(column_id),
                sort_order = 0,
                moved_at = CURRENT_TIMESTAMP,
                moved_by = VALUES(moved_by)
        ");
        $up->execute([$patientId, $id, $user['id'] ?? null]);
        $this->json(['ok' => true]);
    }

    /** DELETE /api/board/boards/{id}/patients/{patientId} — remove from board. */
    public function removePatient($id, $patientId)
    {
        $this->requireApi(['doctor', 'admin']);
        $id = (int) $id;
        $patientId = (int) $patientId;
        $del = $this->pdo->prepare("
            DELETE FROM patient_board_assignments
            WHERE patient_id = ? AND column_id = ?
        ");
        $del->execute([$patientId, $id]);
        $this->json(['ok' => true]);
    }

    /** PUT /api/board/patients/{patientId} — safe partial demographic edit. */
    public function quickEditPatient($patientId)
    {
        $this->requireApi(['doctor', 'admin']);
        $patientId = (int) $patientId;
        $cur = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
        $cur->execute([$patientId]);
        if (!$cur->fetch()) {
            $this->json(['ok' => false, 'error' => 'Patient not found'], 404);
        }

        $b = $this->readBody();
        $allowed = [];
        $params  = [];

        if (array_key_exists('first_name', $b)) {
            $v = trim((string) $b['first_name']);
            if ($v === '' || mb_strlen($v) > 50) {
                $this->json(['ok' => false, 'error' => 'First name is required (up to 50 characters)'], 400);
            }
            $allowed[] = 'first_name = ?'; $params[] = $v;
        }
        if (array_key_exists('last_name', $b)) {
            $v = trim((string) $b['last_name']);
            if ($v === '' || mb_strlen($v) > 50) {
                $this->json(['ok' => false, 'error' => 'Last name is required (up to 50 characters)'], 400);
            }
            $allowed[] = 'last_name = ?'; $params[] = $v;
        }
        if (array_key_exists('phone', $b)) {
            $v = trim((string) $b['phone']);
            if ($v === '' || mb_strlen($v) > 20) {
                $this->json(['ok' => false, 'error' => 'Phone is required (up to 20 characters)'], 400);
            }
            $allowed[] = 'phone = ?'; $params[] = $v;
        }
        if (array_key_exists('alt_phone', $b)) {
            $v = trim((string) $b['alt_phone']);
            $allowed[] = 'alt_phone = ?'; $params[] = ($v === '' ? null : $v);
        }
        if (array_key_exists('gender', $b)) {
            $v = trim((string) $b['gender']);
            if (!in_array($v, ['Male', 'Female', 'Other'], true)) {
                $this->json(['ok' => false, 'error' => 'Invalid gender'], 400);
            }
            $allowed[] = 'gender = ?'; $params[] = $v;
        }
        if (array_key_exists('dob', $b)) {
            $v = trim((string) $b['dob']);
            if ($v !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $this->json(['ok' => false, 'error' => 'Invalid date of birth'], 400);
            }
            $allowed[] = 'dob = ?'; $params[] = ($v === '' ? null : $v);
        }

        if (!$allowed) {
            $this->json(['ok' => false, 'error' => 'No fields to update'], 400);
        }
        $params[] = $patientId;
        $this->pdo->prepare(
            'UPDATE patients SET ' . implode(', ', $allowed) . ', updated_at = NOW() WHERE id = ?'
        )->execute($params);
        $this->json(['ok' => true]);
    }
}
