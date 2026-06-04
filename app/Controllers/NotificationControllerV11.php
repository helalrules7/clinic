<?php

namespace App\Controllers;

require_once __DIR__ . '/NotificationController.php';

use App\Config\Database;
use App\Lib\Auth;

/**
 * NotificationControllerV11
 *
 * Extends NotificationController with v11 features:
 *   - snooze / unsnooze
 *   - pin / unpin
 *   - grouped feed (time-bucketed, with alerts UNION, stack collapsing)
 *
 * NOTE: The parent class declares $pdo / $auth as `private`, so we cannot
 * read them from a child scope. We override the constructor (which still
 * invokes the parent constructor — preserving any side-effects) and keep
 * our own local references with the exact same shape/usage.
 */
class NotificationControllerV11 extends NotificationController
{
    /** @var \PDO */
    protected $pdo;
    /** @var Auth */
    protected $auth;

    public function __construct()
    {
        parent::__construct();
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Snooze / Unsnooze
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /api/notifications/:id/snooze
     * Body: { until: 'YYYY-MM-DDTHH:mm:ss' | 'YYYY-MM-DD HH:mm:ss' }
     */
    public function snooze($id)
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $data  = json_decode(file_get_contents('php://input'), true) ?: [];
        $until = isset($data['until']) ? trim((string)$data['until']) : '';

        if ($until === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Missing "until" field'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // Accept either ISO 'T' separator or a space separator.
        $normalized = str_replace('T', ' ', $until);
        // Strip a trailing timezone marker (e.g. 'Z') and fractional seconds for parsing.
        $normalized = preg_replace('/\.\d+/', '', $normalized);
        $normalized = rtrim($normalized, 'Zz');

        $ts = strtotime($normalized);
        if ($ts === false) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Invalid date format. Expected YYYY-MM-DDTHH:mm:ss or YYYY-MM-DD HH:mm:ss'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        if ($ts <= time()) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Snooze time must be in the future'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $snoozedUntil = date('Y-m-d H:i:s', $ts);

        try {
            $stmt = $this->pdo->prepare("UPDATE notifications SET snoozed_until = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$snoozedUntil, (int)$id, (int)$user['id']]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Notification not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return;
            }
        } catch (\PDOException $e) {
            error_log('NotificationControllerV11::snooze SQL Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        echo json_encode([
            'success'       => true,
            'message'       => 'Notification snoozed',
            'snoozed_until' => $snoozedUntil,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST /api/notifications/:id/unsnooze
     */
    public function unsnooze($id)
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE notifications SET snoozed_until = NULL WHERE id = ? AND user_id = ?");
            $stmt->execute([(int)$id, (int)$user['id']]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Notification not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return;
            }
        } catch (\PDOException $e) {
            error_log('NotificationControllerV11::unsnooze SQL Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Notification unsnoozed',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Pin / Unpin
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /api/notifications/:id/pin
     */
    public function pin($id)
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE notifications SET pinned_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([(int)$id, (int)$user['id']]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Notification not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return;
            }
        } catch (\PDOException $e) {
            error_log('NotificationControllerV11::pin SQL Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        echo json_encode([
            'success'   => true,
            'message'   => 'Notification pinned',
            'pinned_at' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST /api/notifications/:id/unpin
     */
    public function unpin($id)
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE notifications SET pinned_at = NULL WHERE id = ? AND user_id = ?");
            $stmt->execute([(int)$id, (int)$user['id']]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Notification not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return;
            }
        } catch (\PDOException $e) {
            error_log('NotificationControllerV11::unpin SQL Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Notification unpinned',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Grouped feed
    // ─────────────────────────────────────────────────────────────────────

    /**
     * GET /api/notifications/grouped
     *
     * Returns:
     *   {
     *     success: true,
     *     buckets: { today: [...], yesterday: [...], thisWeek: [...], older: [...] },
     *     unread_count: int
     *   }
     */
    public function grouped()
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }
        $uid = (int)$user['id'];

        // ── Fetch unified feed: notifications (excluding still-snoozed) ⊎ active alerts.
        //
        // Schema note: the actual `notifications` table uses `message` (not `body`)
        // and does NOT have `link` / `icon` columns — instead it has
        // `related_type` + `related_id` from which we derive a link, and the
        // icon is mapped server-side from `type` (mapping table further below).
        $sql = "
            SELECT
                id,
                user_id,
                type,
                title,
                message       AS body,
                related_type,
                related_id,
                is_read,
                snoozed_until,
                pinned_at,
                group_key,
                patient_id,
                created_at
            FROM notifications
            WHERE user_id = ?
              AND (snoozed_until IS NULL OR snoozed_until <= NOW())

            UNION ALL

            SELECT
                id,
                doctor_id AS user_id,
                'alert'   AS type,
                message   AS title,
                NULL      AS body,
                'alert'   AS related_type,
                id        AS related_id,
                0         AS is_read,
                NULL      AS snoozed_until,
                NULL      AS pinned_at,
                SHA1(CONCAT('alert|', IFNULL(patient_id,''), '|', DATE(created_at))) AS group_key,
                patient_id,
                created_at
            FROM alerts
            WHERE doctor_id = ?
              AND is_active = 1
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$uid, $uid]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('NotificationControllerV11::grouped SQL Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // ── Hydrate patient names in one round-trip
        $patientIds = [];
        foreach ($rows as $r) {
            if (!empty($r['patient_id'])) {
                $patientIds[(int)$r['patient_id']] = true;
            }
        }
        $patientMap = [];
        if (!empty($patientIds)) {
            $ids  = array_keys($patientIds);
            $ph   = implode(',', array_fill(0, count($ids), '?'));
            try {
                $pStmt = $this->pdo->prepare("SELECT id, first_name, last_name FROM patients WHERE id IN ($ph)");
                $pStmt->execute($ids);
                foreach ($pStmt->fetchAll(\PDO::FETCH_ASSOC) as $p) {
                    $patientMap[(int)$p['id']] = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                }
            } catch (\PDOException $e) {
                error_log('NotificationControllerV11::grouped patient hydration error: ' . $e->getMessage());
                // Non-fatal — continue without names.
            }
        }

        // ── Type → icon mapping (Bootstrap Icon names without the bi- prefix)
        $iconForType = [
            'alert'           => 'bell-fill',
            'mention'         => 'at',
            'todo_due'        => 'exclamation-circle-fill',
            'todo_reminder'   => 'alarm',
            'appointment'     => 'calendar2-event-fill',
            'payment'         => 'cash-coin',
            'system'          => 'gear-fill',
            'broadcast'       => 'megaphone-fill',
            'message'         => 'envelope-fill',
        ];

        // ── Normalize items
        $items = [];
        foreach ($rows as $r) {
            $pid  = !empty($r['patient_id']) ? (int)$r['patient_id'] : null;
            $type = (string)($r['type'] ?? 'system');

            // Derive a deep-link from related_type/related_id, falling back to type.
            $link = null;
            $rt = $r['related_type'] ?? null;
            $ri = $r['related_id']   ?? null;
            if ($rt === 'alert' && $ri) {
                $link = '/doctor/alerts#' . (int)$ri;
            } elseif ($rt === 'appointment' && $ri) {
                $link = '/doctor/appointments/' . (int)$ri;
            } elseif ($rt === 'patient' && $ri) {
                $link = '/doctor/patients/' . (int)$ri;
            } elseif ($rt === 'todo' && $ri) {
                $link = '/doctor/todos?focus=' . (int)$ri;
            } elseif ($rt === 'payment' && $ri) {
                $link = '/doctor/payments?focus=' . (int)$ri;
            } elseif ($pid) {
                $link = '/doctor/patients/' . $pid;
            }

            $items[] = [
                'id'            => (int)$r['id'],
                'type'          => $type,
                'title'         => $r['title'],
                'body'          => $r['body'],
                'icon'          => $iconForType[$type] ?? 'bell-fill',
                'link'          => $link,
                'patient_id'    => $pid,
                'patient_name'  => ($pid !== null && isset($patientMap[$pid])) ? $patientMap[$pid] : null,
                'is_read'       => (int)($r['is_read'] ?? 0),
                'snoozed_until' => $r['snoozed_until'] ?? null,
                'pinned_at'     => $r['pinned_at'] ?? null,
                'group_key'     => $r['group_key'] ?? null,
                'stack_size'    => 1,
                'children_ids'  => [],
                'created_at'    => $r['created_at'],
                'time_ago'      => $this->timeAgo($r['created_at']),
            ];
        }

        // ── Sort: pinned first (pinned_at DESC NULLS LAST), then created_at DESC
        usort($items, function ($a, $b) {
            $aPinned = !empty($a['pinned_at']);
            $bPinned = !empty($b['pinned_at']);
            if ($aPinned !== $bPinned) {
                return $aPinned ? -1 : 1; // pinned first
            }
            if ($aPinned && $bPinned) {
                $cmp = strcmp((string)$b['pinned_at'], (string)$a['pinned_at']);
                if ($cmp !== 0) return $cmp;
            }
            return strcmp((string)$b['created_at'], (string)$a['created_at']);
        });

        // ── Bucket by time
        $now           = time();
        $todayStart    = strtotime(date('Y-m-d', $now) . ' 00:00:00');
        $yesterdayStart = $todayStart - 86400;
        // "This week" = last 7 days, excluding today/yesterday
        $weekStart     = $todayStart - (6 * 86400);

        $buckets = [
            'today'     => [],
            'yesterday' => [],
            'thisWeek'  => [],
            'older'     => [],
        ];

        foreach ($items as $item) {
            $ts = strtotime((string)$item['created_at']);
            if ($ts === false) {
                $buckets['older'][] = $item;
                continue;
            }
            if ($ts >= $todayStart) {
                $buckets['today'][] = $item;
            } elseif ($ts >= $yesterdayStart) {
                $buckets['yesterday'][] = $item;
            } elseif ($ts >= $weekStart) {
                $buckets['thisWeek'][] = $item;
            } else {
                $buckets['older'][] = $item;
            }
        }

        // ── Collapse same group_key (3+ items) per bucket into a single stack item.
        //    Pinned items are never collapsed (so users can see all pins).
        foreach ($buckets as $name => $list) {
            $buckets[$name] = $this->collapseStacks($list);
        }

        // ── Unread count (notifications only; alerts have no read state)
        try {
            $cStmt = $this->pdo->prepare("
                SELECT COUNT(*) AS c
                FROM notifications
                WHERE user_id = ?
                  AND is_read = 0
                  AND (snoozed_until IS NULL OR snoozed_until <= NOW())
            ");
            $cStmt->execute([$uid]);
            $unread = (int)($cStmt->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
        } catch (\PDOException $e) {
            error_log('NotificationControllerV11::grouped unread count error: ' . $e->getMessage());
            $unread = 0;
        }

        echo json_encode([
            'success'      => true,
            'buckets'      => $buckets,
            'unread_count' => $unread,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Collapse items sharing the same non-empty group_key when 3+ siblings exist.
     * Preserves original ordering — the first (highest-ranked) item becomes the
     * representative, augmented with stack_size + children_ids of the rest.
     */
    protected function collapseStacks(array $list)
    {
        if (empty($list)) return [];

        // Tally group_key occurrences (only count non-pinned items toward a stack)
        $tally = [];
        foreach ($list as $idx => $item) {
            $gk = $item['group_key'] ?? null;
            if (!$gk) continue;
            if (!empty($item['pinned_at'])) continue;
            if (!isset($tally[$gk])) $tally[$gk] = 0;
            $tally[$gk]++;
        }

        $out         = [];
        $stackSeen   = []; // group_key => index in $out
        foreach ($list as $item) {
            $gk = $item['group_key'] ?? null;
            $eligible = $gk && empty($item['pinned_at']) && isset($tally[$gk]) && $tally[$gk] >= 3;

            if (!$eligible) {
                $out[] = $item;
                continue;
            }

            if (!isset($stackSeen[$gk])) {
                // First sibling: keep as representative, initialize stack fields.
                $item['stack_size']   = $tally[$gk];
                $item['children_ids'] = [];
                $out[] = $item;
                $stackSeen[$gk] = count($out) - 1;
            } else {
                // Subsequent sibling: append its id to the representative's children_ids
                $out[$stackSeen[$gk]]['children_ids'][] = (int)$item['id'];
            }
        }

        return $out;
    }

    /**
     * Human-friendly relative time.
     *  - <60s            → "just now"
     *  - <60m            → "N min ago"
     *  - same day        → "N hr ago"
     *  - yesterday       → "Yesterday"
     *  - older           → "Mar 5"   (or "Mar 5, 2024" if a different year)
     */
    protected function timeAgo($datetime)
    {
        if (empty($datetime)) return '';
        $ts = is_numeric($datetime) ? (int)$datetime : strtotime((string)$datetime);
        if ($ts === false) return '';

        $now  = time();
        $diff = $now - $ts;

        if ($diff < 0)   return 'just now'; // clock skew safety
        if ($diff < 60)  return 'just now';
        if ($diff < 3600) {
            $m = (int)floor($diff / 60);
            return $m . ' min ago';
        }

        $todayStart     = strtotime(date('Y-m-d', $now) . ' 00:00:00');
        $yesterdayStart = $todayStart - 86400;

        if ($ts >= $todayStart) {
            $h = (int)floor($diff / 3600);
            return $h . ' hr ago';
        }
        if ($ts >= $yesterdayStart) {
            return 'Yesterday';
        }

        if (date('Y', $ts) === date('Y', $now)) {
            return date('M j', $ts);
        }
        return date('M j, Y', $ts);
    }
}
