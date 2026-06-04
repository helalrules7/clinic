<?php
namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use PDO;
use PDOException;
use Throwable;

require_once __DIR__ . '/../Models/TodoListModel.php';

class TodoListController
{
    private $pdo;
    private $auth;

    private const ALLOWED_COLORS = ['indigo', 'emerald', 'rose', 'slate', 'amber', 'ocean'];
    private const DEFAULT_COLOR  = 'indigo';
    private const DEFAULT_ICON   = 'list-task';

    public function __construct()
    {
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    private function jsonHeader(): void
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
    }

    private function respond(int $status, array $payload): void
    {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function requireUser(): ?array
    {
        $user = $this->auth->user();
        if (!$user || empty($user['id'])) {
            $this->respond(401, ['success' => false, 'message' => 'Unauthorized']);
            return null;
        }
        return $user;
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function fetchList(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, name, color, icon, sort_order, is_default, archived_at, created_at, updated_at
               FROM todo_lists
              WHERE id = :id AND user_id = :uid
              LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function shapeList(array $row): array
    {
        return [
            'id'           => (int)$row['id'],
            'name'         => (string)$row['name'],
            'color'        => (string)($row['color'] ?? self::DEFAULT_COLOR),
            'icon'         => (string)($row['icon'] ?? self::DEFAULT_ICON),
            'sort_order'   => isset($row['sort_order']) ? (int)$row['sort_order'] : 0,
            'is_default'   => !empty($row['is_default']) ? 1 : 0,
            'archived_at'  => $row['archived_at'] ?? null,
            'open_count'   => isset($row['open_count']) ? (int)$row['open_count'] : 0,
            'total_count'  => isset($row['total_count']) ? (int)$row['total_count'] : 0,
        ];
    }

    private function validColor($value): bool
    {
        return is_string($value) && in_array($value, self::ALLOWED_COLORS, true);
    }

    /* ------------------------------------------------------------------ */
    /*  GET /api/todo-lists                                                */
    /* ------------------------------------------------------------------ */
    public function index()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $userId = (int)$user['id'];

        $includeArchived = isset($_GET['include_archived'])
            && in_array((string)$_GET['include_archived'], ['1', 'true', 'yes'], true);

        // Make sure the user always has at least one list.
        $this->ensureDefaultList($userId);

        $sql = "SELECT l.id, l.name, l.color, l.icon, l.sort_order, l.is_default, l.archived_at,
                       COALESCE(SUM(CASE WHEN t.status = 'open' THEN 1 ELSE 0 END), 0) AS open_count,
                       COALESCE(COUNT(t.id), 0) AS total_count
                  FROM todo_lists l
                  LEFT JOIN todos t
                         ON t.list_id = l.id
                        AND t.user_id = l.user_id
                 WHERE l.user_id = :uid";
        if (!$includeArchived) {
            $sql .= " AND l.archived_at IS NULL";
        }
        $sql .= " GROUP BY l.id, l.name, l.color, l.icon, l.sort_order, l.is_default, l.archived_at
                  ORDER BY l.is_default DESC, l.sort_order ASC, l.id ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to load lists']);
            return;
        }

        $lists = array_map([$this, 'shapeList'], $rows);
        $this->respond(200, ['success' => true, 'lists' => $lists]);
    }

    /* ------------------------------------------------------------------ */
    /*  GET /api/todo-lists/:id                                            */
    /* ------------------------------------------------------------------ */
    public function show($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $listId = (int)$id;
        if ($listId <= 0) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        $userId = (int)$user['id'];

        try {
            $stmt = $this->pdo->prepare(
                "SELECT l.id, l.name, l.color, l.icon, l.sort_order, l.is_default, l.archived_at,
                        COALESCE(SUM(CASE WHEN t.status = 'open' THEN 1 ELSE 0 END), 0) AS open_count,
                        COALESCE(COUNT(t.id), 0) AS total_count
                   FROM todo_lists l
                   LEFT JOIN todos t
                          ON t.list_id = l.id
                         AND t.user_id = l.user_id
                  WHERE l.id = :id AND l.user_id = :uid
                  GROUP BY l.id, l.name, l.color, l.icon, l.sort_order, l.is_default, l.archived_at
                  LIMIT 1"
            );
            $stmt->execute([':id' => $listId, ':uid' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to load list']);
            return;
        }

        if (!$row) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        $this->respond(200, ['success' => true, 'list' => $this->shapeList($row)]);
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/todo-lists                                               */
    /* ------------------------------------------------------------------ */
    public function create()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $userId = (int)$user['id'];
        $body   = $this->readJsonBody();

        $name = isset($body['name']) ? trim((string)$body['name']) : '';
        if ($name === '' || mb_strlen($name) > 80) {
            $this->respond(422, ['success' => false, 'message' => 'Name must be 1-80 characters']);
            return;
        }

        $color = isset($body['color']) ? (string)$body['color'] : self::DEFAULT_COLOR;
        if (!$this->validColor($color)) {
            $this->respond(422, ['success' => false, 'message' => 'Invalid color']);
            return;
        }

        $icon = isset($body['icon']) ? trim((string)$body['icon']) : self::DEFAULT_ICON;
        if ($icon === '') { $icon = self::DEFAULT_ICON; }
        if (mb_strlen($icon) > 40) {
            $this->respond(422, ['success' => false, 'message' => 'Icon must be 1-40 characters']);
            return;
        }

        $sortOrder = null;
        if (array_key_exists('sort_order', $body) && $body['sort_order'] !== null && $body['sort_order'] !== '') {
            if (!is_numeric($body['sort_order'])) {
                $this->respond(422, ['success' => false, 'message' => 'sort_order must be numeric']);
                return;
            }
            $sortOrder = (int)$body['sort_order'];
        }

        try {
            if ($sortOrder === null) {
                $stmt = $this->pdo->prepare(
                    'SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order
                       FROM todo_lists WHERE user_id = :uid'
                );
                $stmt->execute([':uid' => $userId]);
                $sortOrder = (int)$stmt->fetchColumn();
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO todo_lists (user_id, name, color, icon, sort_order, is_default, created_at, updated_at)
                 VALUES (:uid, :name, :color, :icon, :so, 0, NOW(), NOW())'
            );
            $stmt->execute([
                ':uid'   => $userId,
                ':name'  => $name,
                ':color' => $color,
                ':icon'  => $icon,
                ':so'    => $sortOrder,
            ]);
            $newId = (int)$this->pdo->lastInsertId();
            $row   = $this->fetchList($newId, $userId);
        } catch (PDOException $e) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to create list']);
            return;
        }

        if (!$row) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to load created list']);
            return;
        }

        $row['open_count']  = 0;
        $row['total_count'] = 0;
        $this->respond(201, ['success' => true, 'list' => $this->shapeList($row)]);
    }

    /* ------------------------------------------------------------------ */
    /*  PATCH /api/todo-lists/:id                                          */
    /* ------------------------------------------------------------------ */
    public function update($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $listId = (int)$id;
        if ($listId <= 0) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }
        $userId = (int)$user['id'];

        $existing = $this->fetchList($listId, $userId);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        $body = $this->readJsonBody();
        $sets = [];
        $params = [':id' => $listId, ':uid' => $userId];

        if (array_key_exists('name', $body)) {
            $name = trim((string)$body['name']);
            if ($name === '' || mb_strlen($name) > 80) {
                $this->respond(422, ['success' => false, 'message' => 'Name must be 1-80 characters']);
                return;
            }
            $sets[] = 'name = :name';
            $params[':name'] = $name;
        }

        if (array_key_exists('color', $body)) {
            $color = (string)$body['color'];
            if (!$this->validColor($color)) {
                $this->respond(422, ['success' => false, 'message' => 'Invalid color']);
                return;
            }
            $sets[] = 'color = :color';
            $params[':color'] = $color;
        }

        if (array_key_exists('icon', $body)) {
            $icon = trim((string)$body['icon']);
            if ($icon === '' || mb_strlen($icon) > 40) {
                $this->respond(422, ['success' => false, 'message' => 'Icon must be 1-40 characters']);
                return;
            }
            $sets[] = 'icon = :icon';
            $params[':icon'] = $icon;
        }

        if (array_key_exists('sort_order', $body)) {
            if (!is_numeric($body['sort_order'])) {
                $this->respond(422, ['success' => false, 'message' => 'sort_order must be numeric']);
                return;
            }
            $sets[] = 'sort_order = :so';
            $params[':so'] = (int)$body['sort_order'];
        }

        if (empty($sets)) {
            $row = $this->fetchList($listId, $userId);
            $this->respond(200, ['success' => true, 'list' => $this->shapeList($row)]);
            return;
        }

        $sets[] = 'updated_at = NOW()';
        $sql = 'UPDATE todo_lists SET ' . implode(', ', $sets)
             . ' WHERE id = :id AND user_id = :uid';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row = $this->fetchList($listId, $userId);
        } catch (PDOException $e) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to update list']);
            return;
        }

        if (!$row) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        $this->respond(200, ['success' => true, 'list' => $this->shapeList($row)]);
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/todo-lists/:id/archive                                   */
    /* ------------------------------------------------------------------ */
    public function archive($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $listId = (int)$id;
        $userId = (int)$user['id'];
        if ($listId <= 0) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        $existing = $this->fetchList($listId, $userId);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        if (!empty($existing['is_default'])) {
            $this->respond(409, ['success' => false, 'message' => 'Default list cannot be archived']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE todo_lists
                    SET archived_at = NOW(), updated_at = NOW()
                  WHERE id = :id AND user_id = :uid'
            );
            $stmt->execute([':id' => $listId, ':uid' => $userId]);
            $row = $this->fetchList($listId, $userId);
        } catch (PDOException $e) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to archive list']);
            return;
        }

        $this->respond(200, ['success' => true, 'list' => $this->shapeList($row)]);
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/todo-lists/:id/restore                                   */
    /* ------------------------------------------------------------------ */
    public function restore($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $listId = (int)$id;
        $userId = (int)$user['id'];
        if ($listId <= 0) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        $existing = $this->fetchList($listId, $userId);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE todo_lists
                    SET archived_at = NULL, updated_at = NOW()
                  WHERE id = :id AND user_id = :uid'
            );
            $stmt->execute([':id' => $listId, ':uid' => $userId]);
            $row = $this->fetchList($listId, $userId);
        } catch (PDOException $e) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to restore list']);
            return;
        }

        $this->respond(200, ['success' => true, 'list' => $this->shapeList($row)]);
    }

    /* ------------------------------------------------------------------ */
    /*  DELETE /api/todo-lists/:id                                         */
    /* ------------------------------------------------------------------ */
    public function delete($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $listId = (int)$id;
        $userId = (int)$user['id'];
        if ($listId <= 0) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        $existing = $this->fetchList($listId, $userId);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'List not found']);
            return;
        }

        if (!empty($existing['is_default'])) {
            $this->respond(409, ['success' => false, 'message' => 'Default list cannot be deleted']);
            return;
        }

        if (empty($existing['archived_at'])) {
            $this->respond(409, ['success' => false, 'message' => 'List must be archived before deletion']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM todos WHERE list_id = :id AND user_id = :uid'
            );
            $stmt->execute([':id' => $listId, ':uid' => $userId]);
            $taskCount = (int)$stmt->fetchColumn();

            if ($taskCount > 0) {
                $this->respond(409, [
                    'success' => false,
                    'message' => 'List still has tasks; move or delete them first',
                    'task_count' => $taskCount,
                ]);
                return;
            }

            $stmt = $this->pdo->prepare(
                'DELETE FROM todo_lists WHERE id = :id AND user_id = :uid'
            );
            $stmt->execute([':id' => $listId, ':uid' => $userId]);
        } catch (PDOException $e) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to delete list']);
            return;
        }

        $this->respond(200, ['success' => true]);
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/todo-lists/reorder                                       */
    /* ------------------------------------------------------------------ */
    public function reorder()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $userId = (int)$user['id'];
        $body   = $this->readJsonBody();
        $ids    = isset($body['ids']) && is_array($body['ids']) ? $body['ids'] : null;

        if ($ids === null) {
            $this->respond(422, ['success' => false, 'message' => 'ids array required']);
            return;
        }

        $clean = [];
        foreach ($ids as $rawId) {
            if (!is_numeric($rawId)) continue;
            $intId = (int)$rawId;
            if ($intId > 0 && !in_array($intId, $clean, true)) {
                $clean[] = $intId;
            }
        }

        if (empty($clean)) {
            $this->respond(422, ['success' => false, 'message' => 'No valid ids provided']);
            return;
        }

        try {
            // Confirm every id belongs to this user.
            $placeholders = implode(',', array_fill(0, count($clean), '?'));
            $params = $clean;
            $params[] = $userId;
            $check = $this->pdo->prepare(
                "SELECT COUNT(*) FROM todo_lists WHERE id IN ($placeholders) AND user_id = ?"
            );
            $check->execute($params);
            $found = (int)$check->fetchColumn();
            if ($found !== count($clean)) {
                $this->respond(404, ['success' => false, 'message' => 'One or more lists not found']);
                return;
            }

            $this->pdo->beginTransaction();
            $upd = $this->pdo->prepare(
                'UPDATE todo_lists
                    SET sort_order = :so, updated_at = NOW()
                  WHERE id = :id AND user_id = :uid'
            );
            foreach ($clean as $index => $listId) {
                $upd->execute([
                    ':so'  => $index + 1,
                    ':id'  => $listId,
                    ':uid' => $userId,
                ]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->respond(500, ['success' => false, 'message' => 'Failed to reorder lists']);
            return;
        }

        $this->respond(200, ['success' => true]);
    }

    /* ------------------------------------------------------------------ */
    /*  ensureDefaultList — used by other controllers                       */
    /* ------------------------------------------------------------------ */
    public function ensureDefaultList($userId): int
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return 0;
        }

        try {
            // Prefer an existing default.
            $stmt = $this->pdo->prepare(
                'SELECT id FROM todo_lists
                  WHERE user_id = :uid AND is_default = 1
                  ORDER BY id ASC LIMIT 1'
            );
            $stmt->execute([':uid' => $userId]);
            $existingDefault = $stmt->fetchColumn();
            if ($existingDefault) {
                return (int)$existingDefault;
            }

            // Fall back to any existing list (promote oldest non-archived to default).
            $stmt = $this->pdo->prepare(
                'SELECT id FROM todo_lists
                  WHERE user_id = :uid
                  ORDER BY (archived_at IS NULL) DESC, sort_order ASC, id ASC
                  LIMIT 1'
            );
            $stmt->execute([':uid' => $userId]);
            $anyId = $stmt->fetchColumn();
            if ($anyId) {
                $promote = $this->pdo->prepare(
                    'UPDATE todo_lists SET is_default = 1, updated_at = NOW()
                      WHERE id = :id AND user_id = :uid'
                );
                $promote->execute([':id' => (int)$anyId, ':uid' => $userId]);
                return (int)$anyId;
            }

            // No lists at all — create the default.
            $insert = $this->pdo->prepare(
                'INSERT INTO todo_lists (user_id, name, color, icon, sort_order, is_default, created_at, updated_at)
                 VALUES (:uid, :name, :color, :icon, 1, 1, NOW(), NOW())'
            );
            $insert->execute([
                ':uid'   => $userId,
                ':name'  => 'My Tasks',
                ':color' => self::DEFAULT_COLOR,
                ':icon'  => self::DEFAULT_ICON,
            ]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
