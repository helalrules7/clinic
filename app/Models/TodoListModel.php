<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class TodoListModel
{
    private $pdo;

    /** @var array Columns allowed to be set on create/update */
    private $allowedColumns = [
        'name',
        'color',
        'icon',
        'sort_order',
        'is_default',
    ];

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * List all todo lists for a user, with task counters.
     *
     * @param int  $userId
     * @param bool $includeArchived
     * @return array
     */
    public function listForUser($userId, $includeArchived = false): array
    {
        $sql = "SELECT l.id, l.user_id, l.name, l.color, l.icon, l.sort_order,
                       l.is_default, l.archived_at, l.created_at, l.updated_at,
                       COALESCE(SUM(CASE WHEN t.status = 'open' THEN 1 ELSE 0 END), 0) AS open_count,
                       COALESCE(COUNT(t.id), 0) AS total_count
                FROM todo_lists l
                LEFT JOIN todos t ON t.list_id = l.id
                WHERE l.user_id = :uid";

        if (!$includeArchived) {
            $sql .= " AND l.archived_at IS NULL";
        }

        $sql .= " GROUP BY l.id, l.user_id, l.name, l.color, l.icon, l.sort_order,
                          l.is_default, l.archived_at, l.created_at, l.updated_at
                  ORDER BY l.sort_order ASC, l.name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$r) {
            $r['open_count']  = (int)$r['open_count'];
            $r['total_count'] = (int)$r['total_count'];
            $r['sort_order']  = (int)$r['sort_order'];
            $r['is_default']  = (int)$r['is_default'];
        }
        unset($r);

        return $rows;
    }

    /**
     * Find a single list scoped to a user.
     */
    public function findById($id, $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM todo_lists WHERE id = :id AND user_id = :uid LIMIT 1"
        );
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create a new list. Returns inserted id.
     */
    public function create($userId, array $data): int
    {
        $fields = ['user_id'];
        $place  = [':user_id'];
        $params = [':user_id' => (int)$userId];

        foreach ($this->allowedColumns as $col) {
            if (array_key_exists($col, $data)) {
                $fields[]            = $col;
                $place[]             = ':' . $col;
                $params[':' . $col]  = $data[$col];
            }
        }

        // Defaults
        if (!in_array('name', $fields, true)) {
            $fields[]           = 'name';
            $place[]            = ':name';
            $params[':name']    = 'List';
        }
        if (!in_array('sort_order', $fields, true)) {
            $fields[]                  = 'sort_order';
            $place[]                   = ':sort_order';
            $params[':sort_order']     = 0;
        }

        $sql = "INSERT INTO todo_lists (" . implode(', ', $fields) . ", created_at, updated_at)
                VALUES (" . implode(', ', $place) . ", NOW(), NOW())";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            if (is_int($v) || $k === ':user_id' || $k === ':sort_order' || $k === ':is_default') {
                $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
            } elseif ($v === null) {
                $stmt->bindValue($k, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }
        $stmt->execute();

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update a list. Only whitelisted columns are touched.
     */
    public function update($id, $userId, array $patch): bool
    {
        $sets   = [];
        $params = [
            ':id'  => (int)$id,
            ':uid' => (int)$userId,
        ];

        foreach ($this->allowedColumns as $col) {
            if (array_key_exists($col, $patch)) {
                $sets[]              = "{$col} = :{$col}";
                $params[':' . $col]  = $patch[$col];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sets[] = "updated_at = NOW()";

        $sql = "UPDATE todo_lists SET " . implode(', ', $sets) . "
                WHERE id = :id AND user_id = :uid";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            if ($k === ':id' || $k === ':uid' || $k === ':sort_order' || $k === ':is_default') {
                $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
            } elseif ($v === null) {
                $stmt->bindValue($k, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Soft-archive a list.
     */
    public function archive($id, $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE todo_lists
                SET archived_at = NOW(), updated_at = NOW()
              WHERE id = :id AND user_id = :uid AND archived_at IS NULL"
        );
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Restore a previously archived list.
     */
    public function restore($id, $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE todo_lists
                SET archived_at = NULL, updated_at = NOW()
              WHERE id = :id AND user_id = :uid AND archived_at IS NOT NULL"
        );
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Hard-delete a list (caller is responsible for handling tasks beforehand).
     */
    public function delete($id, $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM todo_lists WHERE id = :id AND user_id = :uid"
        );
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Reorder a user's lists. $ids is the desired ordering.
     */
    public function reorder($userId, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE todo_lists
                    SET sort_order = :ord, updated_at = NOW()
                  WHERE id = :id AND user_id = :uid"
            );
            $order = 0;
            foreach ($ids as $id) {
                $stmt->bindValue(':ord', $order, PDO::PARAM_INT);
                $stmt->bindValue(':id',  (int)$id, PDO::PARAM_INT);
                $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
                $stmt->execute();
                $order++;
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
     * Count tasks belonging to a list (scoped to user).
     */
    public function countTasksInList($id, $userId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS c
               FROM todos t
               JOIN todo_lists l ON l.id = t.list_id
              WHERE t.list_id = :id AND l.user_id = :uid"
        );
        $stmt->bindValue(':id',  (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    }

    /**
     * Ensure a default list exists for the user; return its id.
     * If no lists at all, creates one. Otherwise returns existing default,
     * or the first list by sort_order.
     */
    public function ensureDefault($userId): int
    {
        $userId = (int)$userId;

        // Try transactional path with row-locking.
        $useTx = !$this->pdo->inTransaction();
        if ($useTx) {
            try {
                $this->pdo->beginTransaction();
            } catch (\Throwable $e) {
                $useTx = false;
            }
        }

        try {
            // Look for an existing default list first.
            $sql = "SELECT id FROM todo_lists
                     WHERE user_id = :uid AND archived_at IS NULL
                     ORDER BY is_default DESC, sort_order ASC, id ASC
                     LIMIT 1";
            $sql .= $useTx ? " FOR UPDATE" : "";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['id'])) {
                if ($useTx && $this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }
                return (int)$row['id'];
            }

            // None exists — create one.
            $ins = $this->pdo->prepare(
                "INSERT INTO todo_lists
                    (user_id, name, color, icon, sort_order, is_default, created_at, updated_at)
                 VALUES
                    (:uid, 'My Tasks', '#6366f1', 'list', 0, 1, NOW(), NOW())"
            );
            $ins->bindValue(':uid', $userId, PDO::PARAM_INT);
            $ins->execute();
            $newId = (int)$this->pdo->lastInsertId();

            if ($useTx && $this->pdo->inTransaction()) {
                $this->pdo->commit();
            }

            return $newId;
        } catch (\Throwable $e) {
            if ($useTx && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
