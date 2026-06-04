<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class TodoModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * List todos for a user with optional filters.
     *
     * Supported filters:
     *  - list_id    int|null
     *  - status     string|'all'  (default: open == status != 'done')
     *  - patient_id int|null
     *  - q          string  (LIKE on title)
     *  - sort       string  (created_desc|created_asc|due_asc|due_desc|priority_desc|sort_order)
     *  - limit      int
     *  - offset     int
     */
    public function query($userId, array $filters): array
    {
        $where  = ['t.user_id = :uid'];
        $params = [':uid' => (int)$userId];

        if (array_key_exists('list_id', $filters) && $filters['list_id'] !== null && $filters['list_id'] !== '') {
            $where[] = 't.list_id = :list_id';
            $params[':list_id'] = (int)$filters['list_id'];
        }

        $status = $filters['status'] ?? 'open';
        if ($status !== 'all' && $status !== null && $status !== '') {
            if ($status === 'open') {
                $where[] = "t.status <> 'done'";
            } else {
                $where[] = 't.status = :status';
                $params[':status'] = (string)$status;
            }
        }

        if (!empty($filters['patient_id'])) {
            $where[] = 't.patient_id = :patient_id';
            $params[':patient_id'] = (int)$filters['patient_id'];
        }

        if (!empty($filters['q'])) {
            $where[] = 't.title LIKE :q';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $sort = $filters['sort'] ?? 'created_desc';
        switch ($sort) {
            case 'created_asc':
                $orderBy = 't.created_at ASC';
                break;
            case 'due_asc':
                $orderBy = 't.due_at IS NULL, t.due_at ASC, t.created_at DESC';
                break;
            case 'due_desc':
                $orderBy = 't.due_at IS NULL, t.due_at DESC, t.created_at DESC';
                break;
            case 'priority_desc':
                $orderBy = "FIELD(t.priority,'high','med','low') ASC, t.created_at DESC";
                break;
            case 'sort_order':
                $orderBy = 't.sort_order ASC, t.created_at DESC';
                break;
            case 'created_desc':
            default:
                $orderBy = 't.created_at DESC';
                break;
        }

        $limit  = isset($filters['limit'])  ? max(1, (int)$filters['limit'])  : 100;
        $offset = isset($filters['offset']) ? max(0, (int)$filters['offset']) : 0;

        $sql = "SELECT t.*,
                       CASE WHEN p.id IS NOT NULL
                            THEN CONCAT(p.first_name, ' ', p.last_name)
                            ELSE NULL
                       END AS patient_name
                FROM todos t
                LEFT JOIN patients p ON p.id = t.patient_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById($id, $userId): ?array
    {
        $sql = "SELECT t.*,
                       CASE WHEN p.id IS NOT NULL
                            THEN CONCAT(p.first_name, ' ', p.last_name)
                            ELSE NULL
                       END AS patient_name
                FROM todos t
                LEFT JOIN patients p ON p.id = t.patient_id
                WHERE t.id = :id AND t.user_id = :uid
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create($userId, array $data): int
    {
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            return 0;
        }

        $fields = [
            'user_id'                => (int)$userId,
            'list_id'                => isset($data['list_id']) && $data['list_id'] !== '' ? (int)$data['list_id'] : null,
            'title'                  => $title,
            'description'            => isset($data['description']) ? (string)$data['description'] : null,
            'patient_id'             => isset($data['patient_id']) && $data['patient_id'] !== '' ? (int)$data['patient_id'] : null,
            'appointment_id'         => isset($data['appointment_id']) && $data['appointment_id'] !== '' ? (int)$data['appointment_id'] : null,
            'due_at'                 => !empty($data['due_at']) ? (string)$data['due_at'] : null,
            'remind_before_minutes'  => isset($data['remind_before_minutes']) && $data['remind_before_minutes'] !== '' ? (int)$data['remind_before_minutes'] : null,
            'status'                 => isset($data['status']) && $data['status'] !== '' ? (string)$data['status'] : 'open',
            'priority'               => isset($data['priority']) && $data['priority'] !== '' ? (string)$data['priority'] : 'med',
            'sort_order'             => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
        ];

        $cols   = array_keys($fields);
        $place  = array_map(fn($c) => ':' . $c, $cols);

        $sql = "INSERT INTO todos (" . implode(',', $cols) . ", created_at, updated_at)
                VALUES (" . implode(',', $place) . ", NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);

        foreach ($fields as $k => $v) {
            if ($v === null) {
                $stmt->bindValue(':' . $k, null, PDO::PARAM_NULL);
            } elseif (is_int($v)) {
                $stmt->bindValue(':' . $k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $k, $v, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function update($id, $userId, array $patch): bool
    {
        $allowed = [
            'list_id', 'title', 'description', 'patient_id', 'appointment_id',
            'due_at', 'remind_before_minutes', 'status', 'priority', 'sort_order',
            'todo_notified_at', 'todo_reminded_at', 'completed_at',
        ];

        $sets = [];
        $params = [':id' => (int)$id, ':uid' => (int)$userId];

        foreach ($allowed as $col) {
            if (!array_key_exists($col, $patch)) {
                continue;
            }
            $val = $patch[$col];

            // Normalize empty string to null for nullable scalars
            if ($val === '') {
                $val = null;
            }

            // Cast integers where appropriate
            if (in_array($col, ['list_id', 'patient_id', 'appointment_id', 'remind_before_minutes', 'sort_order'], true)) {
                $val = ($val === null) ? null : (int)$val;
            }

            $sets[] = "$col = :$col";
            $params[":$col"] = $val;
        }

        if (empty($sets)) {
            return false;
        }

        $sets[] = 'updated_at = NOW()';

        $sql = "UPDATE todos SET " . implode(', ', $sets) . "
                WHERE id = :id AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $k => $v) {
            if ($v === null) {
                $stmt->bindValue($k, null, PDO::PARAM_NULL);
            } elseif (is_int($v)) {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function delete($id, $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM todos WHERE id = :id AND user_id = :uid");
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function markDone($id, $userId): bool
    {
        $sql = "UPDATE todos
                SET status = 'done',
                    completed_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function reopen($id, $userId): bool
    {
        $sql = "UPDATE todos
                SET status = 'open',
                    completed_at = NULL,
                    updated_at = NOW()
                WHERE id = :id AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Re-order todos within a list (or unfiled list when $listId is null).
     * Assigns sort_order = position in the array (0-based).
     */
    public function reorder($userId, $listId, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            if ($listId === null || $listId === '' || $listId === 0) {
                $sql = "UPDATE todos
                        SET sort_order = :pos, updated_at = NOW()
                        WHERE id = :id AND user_id = :uid AND list_id IS NULL";
            } else {
                $sql = "UPDATE todos
                        SET sort_order = :pos, updated_at = NOW()
                        WHERE id = :id AND user_id = :uid AND list_id = :list_id";
            }
            $stmt = $this->pdo->prepare($sql);

            $pos = 0;
            foreach ($ids as $id) {
                $stmt->bindValue(':pos', $pos,          PDO::PARAM_INT);
                $stmt->bindValue(':id',  (int)$id,      PDO::PARAM_INT);
                $stmt->bindValue(':uid', (int)$userId,  PDO::PARAM_INT);
                if (!($listId === null || $listId === '' || $listId === 0)) {
                    $stmt->bindValue(':list_id', (int)$listId, PDO::PARAM_INT);
                }
                $stmt->execute();
                $pos++;
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Per-user todo counts.
     *  - open       : todos with status != 'done'
     *  - due_today  : open todos whose due_at falls on today's date
     *  - overdue    : open todos with due_at < NOW()
     *  - by_list    : [ { list_id, name, open_count } ]
     */
    public function counts($userId): array
    {
        $uid = (int)$userId;

        // open
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = :uid AND status <> 'done'");
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $open = (int)$stmt->fetchColumn();

        // due_today
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM todos
             WHERE user_id = :uid
               AND status <> 'done'
               AND due_at IS NOT NULL
               AND DATE(due_at) = CURDATE()"
        );
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $dueToday = (int)$stmt->fetchColumn();

        // overdue
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM todos
             WHERE user_id = :uid
               AND status <> 'done'
               AND due_at IS NOT NULL
               AND due_at < NOW()"
        );
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $overdue = (int)$stmt->fetchColumn();

        // by_list — include un-filed bucket (list_id IS NULL)
        $stmt = $this->pdo->prepare(
            "SELECT t.list_id AS list_id,
                    COALESCE(l.name, '(Unfiled)') AS name,
                    COUNT(*) AS open_count
             FROM todos t
             LEFT JOIN todo_lists l ON l.id = t.list_id
             WHERE t.user_id = :uid AND t.status <> 'done'
             GROUP BY t.list_id, l.name
             ORDER BY (t.list_id IS NULL) ASC, l.sort_order ASC, l.name ASC"
        );
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $byList = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Normalize types
        $byList = array_map(function ($r) {
            return [
                'list_id'    => $r['list_id'] !== null ? (int)$r['list_id'] : null,
                'name'       => $r['name'],
                'open_count' => (int)$r['open_count'],
            ];
        }, $byList);

        return [
            'open'      => $open,
            'due_today' => $dueToday,
            'overdue'   => $overdue,
            'by_list'   => $byList,
        ];
    }

    /**
     * Cron helper: open todos whose effective reminder time falls within the
     * next $minutesAhead minutes (from now) and that haven't been reminded yet.
     *
     * Effective reminder time = due_at - remind_before_minutes (if set),
     * otherwise the due_at itself.
     *
     * NOT scoped to a user — used by the system reminder cron.
     */
    public function dueSoon($minutesAhead): array
    {
        $minutes = max(0, (int)$minutesAhead);

        $sql = "SELECT t.*,
                       CASE WHEN p.id IS NOT NULL
                            THEN CONCAT(p.first_name, ' ', p.last_name)
                            ELSE NULL
                       END AS patient_name
                FROM todos t
                LEFT JOIN patients p ON p.id = t.patient_id
                WHERE t.status <> 'done'
                  AND t.due_at IS NOT NULL
                  AND t.todo_reminded_at IS NULL
                  AND (
                        CASE
                            WHEN t.remind_before_minutes IS NOT NULL AND t.remind_before_minutes > 0
                            THEN DATE_SUB(t.due_at, INTERVAL t.remind_before_minutes MINUTE)
                            ELSE t.due_at
                        END
                      ) BETWEEN DATE_SUB(NOW(), INTERVAL :win MINUTE) AND DATE_ADD(NOW(), INTERVAL :win MINUTE)
                ORDER BY t.due_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':win', $minutes, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
