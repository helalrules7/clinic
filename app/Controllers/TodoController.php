<?php

namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use App\Models\TodoModel;

/**
 * TodoController
 *
 * Handles the Doctor To-Do page (host view) and the JSON CRUD API
 * backing the front-end Todo experience. All queries are scoped by
 * user_id and use prepared statements.
 *
 * Routes (registered externally in public/index.php + app/index.php):
 *   GET    /doctor/todos                   -> page
 *   GET    /api/todos                      -> index
 *   GET    /api/todos/counts               -> counts
 *   POST   /api/todos/reorder              -> reorder
 *   GET    /api/todos/:id                  -> show
 *   POST   /api/todos                      -> create
 *   PATCH  /api/todos/:id                  -> update
 *   DELETE /api/todos/:id                  -> delete
 *   POST   /api/todos/:id/done             -> markDone
 *   POST   /api/todos/:id/reopen           -> reopen
 */
class TodoController
{
    /** Allowed reminder lead-time offsets in minutes. */
    const ALLOWED_REMIND = [15, 60, 240, 1440];

    /** Allowed task status values. */
    const ALLOWED_STATUS = ['open', 'done'];

    /** Allowed priority values. */
    const ALLOWED_PRIORITY = ['low', 'med', 'high'];

    /** Allowed sort modes for index(). */
    const ALLOWED_SORT = ['due_asc', 'due_desc', 'created_desc', 'priority_desc', 'manual'];

    /** @var \PDO */
    private $pdo;

    /** @var Auth */
    private $auth;

    /** @var TodoModel */
    private $model;

    public function __construct()
    {
        $this->pdo   = Database::getInstance()->getConnection();
        $this->auth  = new Auth();
        $this->model = new TodoModel();
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Boilerplate at the top of every JSON endpoint: clear buffers, set
     * the JSON content-type header, and resolve the current user.
     *
     * Returns the user array on success; on failure, emits a 401 JSON
     * response and returns null. Callers should check for null and bail.
     *
     * @return array|null
     */
    private function bootJson()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(
                ['success' => false, 'message' => 'Unauthorized'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            return null;
        }
        return $user;
    }

    /**
     * Echo a JSON envelope with the requested HTTP status code.
     */
    private function respond($payload, $code = 200)
    {
        if ($code !== 200) {
            http_response_code($code);
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Read & decode the JSON request body. Falls back to $_POST when the
     * caller submitted a form-encoded payload.
     *
     * @return array
     */
    private function readJsonBody()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST ?: [];
        }
        return $data;
    }

    /**
     * Normalize and validate a due_at value. Accepts:
     *   - null / empty string         -> returns null
     *   - 'YYYY-MM-DD HH:MM(:SS)?'    -> normalized to 'YYYY-MM-DD HH:MM:SS'
     *   - ISO 8601                    -> normalized via strtotime()
     *
     * Returns the normalized string, or false on parse error.
     *
     * @param mixed $value
     * @return string|null|false
     */
    private function parseDueAt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value)) {
            return false;
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return false;
        }
        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * Lazy-create the user's default todo list. We require_once the
     * TodoListController and delegate so list-creation policy lives in
     * exactly one place.
     *
     * @param int $userId
     * @return int|null  list id, or null on failure
     */
    private function ensureDefaultListId($userId)
    {
        try {
            $controllerPath = __DIR__ . '/TodoListController.php';
            if (file_exists($controllerPath)) {
                require_once $controllerPath;
                if (class_exists('App\\Controllers\\TodoListController')) {
                    $listController = new TodoListController();
                    if (method_exists($listController, 'ensureDefaultList')) {
                        $listId = $listController->ensureDefaultList($userId);
                        if ($listId) {
                            return (int)$listId;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // fall through to direct lookup
        }

        // Fallback: pick the user's flagged default list, or oldest list.
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id FROM todo_lists
                 WHERE user_id = :uid AND archived_at IS NULL
                 ORDER BY is_default DESC, id ASC LIMIT 1"
            );
            $stmt->execute([':uid' => $userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return (int)$row['id'];
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return null;
    }

    /**
     * Confirm a list_id belongs to the user (or is null).
     */
    private function listBelongsToUser($listId, $userId)
    {
        if ($listId === null) {
            return true;
        }
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM todo_lists WHERE id = :id AND user_id = :uid LIMIT 1"
            );
            $stmt->execute([':id' => (int)$listId, ':uid' => (int)$userId]);
            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ---------------------------------------------------------------------
    // Page
    // ---------------------------------------------------------------------

    /**
     * Render the host page at /doctor/todos.
     *
     * Matches AlertController::index() in spirit but uses the simpler
     * include pattern documented in the project context. We pre-fetch
     * lightweight data the view may want at first paint; the view falls
     * back to the API for everything else.
     */
    public function page()
    {
        $user = $this->auth->user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $userId = (int)$user['id'];

        // Best-effort prefetch for first paint; view should still tolerate
        // empty arrays here.
        $lists = [];
        $todos = [];
        try {
            $lists = []; // hydrated via /api/todo-lists by the drawer JS
            $todos = $this->model->query($userId, [
                'status' => 'open',
                'sort'   => 'manual',
            ]);
        } catch (\Throwable $e) {
            // swallow — view renders an empty state and the API hydrates.
        }

        $vars = [
            'user'  => $user,
            'lists' => $lists,
            'todos' => $todos,
            'title' => 'To-Do',
            'pageTitle' => 'To-Do',
        ];

        $viewPath = __DIR__ . '/../Views/doctor/todos.php';
        if (file_exists($viewPath)) {
            extract($vars);
            include $viewPath;
            return;
        }

        // Graceful fallback if the view hasn't shipped yet.
        http_response_code(404);
        echo 'To-Do view not found.';
    }

    // ---------------------------------------------------------------------
    // JSON API
    // ---------------------------------------------------------------------

    /**
     * GET /api/todos
     *
     * Query params:
     *   list_id      int  (optional)
     *   status       'open'|'done'|'all'  (default 'open')
     *   patient_id   int  (optional)
     *   q            string (LIKE on title/description)
     *   sort         due_asc|due_desc|created_desc|priority_desc|manual  (default manual)
     */
    public function index()
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];

        $listId    = isset($_GET['list_id']) && $_GET['list_id'] !== '' ? (int)$_GET['list_id'] : null;
        $patientId = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $q         = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $status    = isset($_GET['status']) ? strtolower(trim((string)$_GET['status'])) : 'open';
        if ($status !== 'all' && !in_array($status, self::ALLOWED_STATUS, true)) {
            $status = 'open';
        }
        $sort = isset($_GET['sort']) ? strtolower(trim((string)$_GET['sort'])) : 'manual';
        if (!in_array($sort, self::ALLOWED_SORT, true)) {
            $sort = 'manual';
        }

        if ($listId !== null && !$this->listBelongsToUser($listId, $userId)) {
            $this->respond(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        try {
            $rows = $this->model->query($userId, [
                'list_id'    => $listId,
                'patient_id' => $patientId,
                'status'     => $status,
                'q'          => $q,
                'sort'       => $sort,
            ]);
            $this->respond(['success' => true, 'data' => $rows]);
        } catch (\PDOException $e) {
            error_log('TodoController::index ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * GET /api/todos/:id
     */
    public function show($id)
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];
        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(['success' => false, 'message' => 'Invalid id'], 422);
            return;
        }

        try {
            $row = $this->model->findById($id, $userId);
            if (!$row) {
                $this->respond(['success' => false, 'message' => 'Not found'], 404);
                return;
            }
            $this->respond(['success' => true, 'data' => $row]);
        } catch (\PDOException $e) {
            error_log('TodoController::show ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * POST /api/todos
     *
     * Body: { list_id?, title (required 1-200), description?, patient_id?,
     *         appointment_id?, due_at?, remind_before_minutes?, priority? }
     */
    public function create()
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];
        $data = $this->readJsonBody();

        // ---- title ----
        $title = isset($data['title']) ? trim((string)$data['title']) : '';
        $len = function_exists('mb_strlen') ? mb_strlen($title) : strlen($title);
        if ($title === '' || $len > 200) {
            $this->respond([
                'success' => false,
                'message' => 'Title is required (1-200 chars)',
            ], 422);
            return;
        }

        // ---- list_id (resolve or lazy-create default) ----
        $listId = isset($data['list_id']) && $data['list_id'] !== '' ? (int)$data['list_id'] : null;
        if ($listId !== null) {
            if (!$this->listBelongsToUser($listId, $userId)) {
                $this->respond(['success' => false, 'message' => 'Invalid list'], 422);
                return;
            }
        } else {
            $listId = $this->ensureDefaultListId($userId);
            if (!$listId) {
                $this->respond(['success' => false, 'message' => 'Unable to resolve default list'], 500);
                return;
            }
        }

        // ---- priority ----
        $priority = isset($data['priority']) ? strtolower(trim((string)$data['priority'])) : 'med';
        if (!in_array($priority, self::ALLOWED_PRIORITY, true)) {
            $this->respond(['success' => false, 'message' => 'Invalid priority'], 422);
            return;
        }

        // ---- remind_before_minutes ----
        $remind = null;
        if (array_key_exists('remind_before_minutes', $data) && $data['remind_before_minutes'] !== null && $data['remind_before_minutes'] !== '') {
            $remind = (int)$data['remind_before_minutes'];
            if (!in_array($remind, self::ALLOWED_REMIND, true)) {
                $this->respond([
                    'success' => false,
                    'message' => 'remind_before_minutes must be one of 15, 60, 240, 1440',
                ], 422);
                return;
            }
        }

        // ---- due_at ----
        $dueAt = null;
        if (array_key_exists('due_at', $data)) {
            $dueAt = $this->parseDueAt($data['due_at']);
            if ($dueAt === false) {
                $this->respond(['success' => false, 'message' => 'Invalid due_at'], 422);
                return;
            }
        }

        $payload = [
            'user_id'                => $userId,
            'list_id'                => $listId,
            'title'                  => $title,
            'description'            => isset($data['description']) ? (string)$data['description'] : null,
            'patient_id'             => isset($data['patient_id']) && $data['patient_id'] !== '' ? (int)$data['patient_id'] : null,
            'appointment_id'         => isset($data['appointment_id']) && $data['appointment_id'] !== '' ? (int)$data['appointment_id'] : null,
            'due_at'                 => $dueAt,
            'remind_before_minutes'  => $remind,
            'priority'               => $priority,
            'status'                 => 'open',
        ];

        try {
            $newId = $this->model->create($userId, $payload);
            if (!$newId) {
                $this->respond(['success' => false, 'message' => 'Failed to create todo'], 500);
                return;
            }
            $row = $this->model->findById($newId, $userId);
            $this->respond(['success' => true, 'data' => $row], 200);
        } catch (\PDOException $e) {
            error_log('TodoController::create ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * PATCH /api/todos/:id
     *
     * Partial update. Any of the create fields may be supplied, plus
     * `status` for explicit transitions (use markDone/reopen for
     * idempotent shortcuts).
     */
    public function update($id)
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];
        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(['success' => false, 'message' => 'Invalid id'], 422);
            return;
        }

        $existing = $this->model->findById($id, $userId);
        if (!$existing) {
            $this->respond(['success' => false, 'message' => 'Not found'], 404);
            return;
        }

        $data = $this->readJsonBody();
        $patch = [];

        if (array_key_exists('title', $data)) {
            $title = trim((string)$data['title']);
            $len = function_exists('mb_strlen') ? mb_strlen($title) : strlen($title);
            if ($title === '' || $len > 200) {
                $this->respond(['success' => false, 'message' => 'Title must be 1-200 chars'], 422);
                return;
            }
            $patch['title'] = $title;
        }

        if (array_key_exists('description', $data)) {
            $patch['description'] = $data['description'] === null ? null : (string)$data['description'];
        }

        if (array_key_exists('list_id', $data)) {
            $newListId = $data['list_id'] === null || $data['list_id'] === '' ? null : (int)$data['list_id'];
            if ($newListId !== null && !$this->listBelongsToUser($newListId, $userId)) {
                $this->respond(['success' => false, 'message' => 'Invalid list'], 422);
                return;
            }
            $patch['list_id'] = $newListId;
        }

        if (array_key_exists('patient_id', $data)) {
            $patch['patient_id'] = $data['patient_id'] === null || $data['patient_id'] === '' ? null : (int)$data['patient_id'];
        }

        if (array_key_exists('appointment_id', $data)) {
            $patch['appointment_id'] = $data['appointment_id'] === null || $data['appointment_id'] === '' ? null : (int)$data['appointment_id'];
        }

        if (array_key_exists('due_at', $data)) {
            $dueAt = $this->parseDueAt($data['due_at']);
            if ($dueAt === false) {
                $this->respond(['success' => false, 'message' => 'Invalid due_at'], 422);
                return;
            }
            $patch['due_at'] = $dueAt;
        }

        if (array_key_exists('remind_before_minutes', $data)) {
            if ($data['remind_before_minutes'] === null || $data['remind_before_minutes'] === '') {
                $patch['remind_before_minutes'] = null;
            } else {
                $remind = (int)$data['remind_before_minutes'];
                if (!in_array($remind, self::ALLOWED_REMIND, true)) {
                    $this->respond([
                        'success' => false,
                        'message' => 'remind_before_minutes must be one of 15, 60, 240, 1440',
                    ], 422);
                    return;
                }
                $patch['remind_before_minutes'] = $remind;
            }
        }

        if (array_key_exists('priority', $data)) {
            $priority = strtolower(trim((string)$data['priority']));
            if (!in_array($priority, self::ALLOWED_PRIORITY, true)) {
                $this->respond(['success' => false, 'message' => 'Invalid priority'], 422);
                return;
            }
            $patch['priority'] = $priority;
        }

        if (array_key_exists('status', $data)) {
            $status = strtolower(trim((string)$data['status']));
            if (!in_array($status, self::ALLOWED_STATUS, true)) {
                $this->respond(['success' => false, 'message' => 'Invalid status'], 422);
                return;
            }
            $patch['status'] = $status;
            // Keep completed_at consistent with explicit status changes.
            if ($status === 'done' && empty($existing['completed_at'])) {
                $patch['completed_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'open') {
                $patch['completed_at'] = null;
            }
        }

        if (array_key_exists('sort_order', $data)) {
            $patch['sort_order'] = (int)$data['sort_order'];
        }

        if (empty($patch)) {
            $this->respond(['success' => true, 'data' => $existing]);
            return;
        }

        try {
            $ok = $this->model->update($id, $userId, $patch);
            if (!$ok) {
                $this->respond(['success' => false, 'message' => 'Failed to update'], 500);
                return;
            }
            $row = $this->model->findById($id, $userId);
            $this->respond(['success' => true, 'data' => $row]);
        } catch (\PDOException $e) {
            error_log('TodoController::update ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * DELETE /api/todos/:id
     */
    public function delete($id)
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];
        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(['success' => false, 'message' => 'Invalid id'], 422);
            return;
        }

        try {
            $existing = $this->model->findById($id, $userId);
            if (!$existing) {
                $this->respond(['success' => false, 'message' => 'Not found'], 404);
                return;
            }
            $ok = $this->model->delete($id, $userId);
            if (!$ok) {
                $this->respond(['success' => false, 'message' => 'Failed to delete'], 500);
                return;
            }
            $this->respond(['success' => true]);
        } catch (\PDOException $e) {
            error_log('TodoController::delete ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * POST /api/todos/:id/done
     *
     * Idempotent: if already done, returns success without touching
     * completed_at again.
     */
    public function markDone($id)
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];
        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(['success' => false, 'message' => 'Invalid id'], 422);
            return;
        }

        try {
            $existing = $this->model->findById($id, $userId);
            if (!$existing) {
                $this->respond(['success' => false, 'message' => 'Not found'], 404);
                return;
            }
            if (($existing['status'] ?? '') === 'done') {
                $this->respond(['success' => true, 'data' => $existing]);
                return;
            }
            $this->model->update($id, $userId, [
                'status'       => 'done',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
            $row = $this->model->findById($id, $userId);
            $this->respond(['success' => true, 'data' => $row]);
        } catch (\PDOException $e) {
            error_log('TodoController::markDone ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * POST /api/todos/:id/reopen
     */
    public function reopen($id)
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];
        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(['success' => false, 'message' => 'Invalid id'], 422);
            return;
        }

        try {
            $existing = $this->model->findById($id, $userId);
            if (!$existing) {
                $this->respond(['success' => false, 'message' => 'Not found'], 404);
                return;
            }
            if (($existing['status'] ?? '') === 'open') {
                $this->respond(['success' => true, 'data' => $existing]);
                return;
            }
            $this->model->update($id, $userId, [
                'status'       => 'open',
                'completed_at' => null,
            ]);
            $row = $this->model->findById($id, $userId);
            $this->respond(['success' => true, 'data' => $row]);
        } catch (\PDOException $e) {
            error_log('TodoController::reopen ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * POST /api/todos/reorder
     *
     * Body: { list_id: int, ids: [int, int, ...] }
     *
     * Re-numbers sort_order in a single transaction. The supplied ids
     * are filtered to the rows owned by the current user and matching
     * list_id before update — anything else is silently dropped to
     * avoid leaking foreign rows into the response.
     */
    public function reorder()
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];

        $data = $this->readJsonBody();
        $listId = isset($data['list_id']) && $data['list_id'] !== '' ? (int)$data['list_id'] : null;
        $ids    = isset($data['ids']) && is_array($data['ids']) ? $data['ids'] : null;

        if (!$listId || $ids === null) {
            $this->respond(['success' => false, 'message' => 'list_id and ids are required'], 422);
            return;
        }
        if (!$this->listBelongsToUser($listId, $userId)) {
            $this->respond(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        // Sanitize to positive integers, preserving order.
        $cleanIds = [];
        foreach ($ids as $v) {
            $iv = (int)$v;
            if ($iv > 0) {
                $cleanIds[] = $iv;
            }
        }
        if (empty($cleanIds)) {
            $this->respond(['success' => true, 'updated' => 0]);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "UPDATE todos
                    SET sort_order = :sort_order, updated_at = NOW()
                  WHERE id = :id AND user_id = :uid AND list_id = :lid"
            );
            $updated = 0;
            foreach ($cleanIds as $pos => $todoId) {
                $stmt->execute([
                    ':sort_order' => $pos,
                    ':id'         => $todoId,
                    ':uid'        => $userId,
                    ':lid'        => $listId,
                ]);
                $updated += $stmt->rowCount();
            }
            $this->pdo->commit();
            $this->respond(['success' => true, 'updated' => $updated]);
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('TodoController::reorder ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /**
     * GET /api/todos/counts
     *
     * Returns:
     *   {
     *     success: true,
     *     open:        int,    // total open across all lists
     *     due_today:   int,    // open + due today (local server date)
     *     overdue:     int,    // open + due before now
     *     by_list: {
     *        "<list_id>": { open: int, total: int },
     *        ...
     *     }
     *   }
     */
    public function counts()
    {
        $user = $this->bootJson();
        if (!$user) {
            return;
        }
        $userId = (int)$user['id'];

        try {
            $open = 0;
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM todos WHERE user_id = :uid AND status = 'open'"
            );
            $stmt->execute([':uid' => $userId]);
            $open = (int)$stmt->fetchColumn();

            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM todos
                  WHERE user_id = :uid AND status = 'open'
                    AND due_at IS NOT NULL
                    AND DATE(due_at) = CURDATE()"
            );
            $stmt->execute([':uid' => $userId]);
            $dueToday = (int)$stmt->fetchColumn();

            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM todos
                  WHERE user_id = :uid AND status = 'open'
                    AND due_at IS NOT NULL
                    AND due_at < NOW()"
            );
            $stmt->execute([':uid' => $userId]);
            $overdue = (int)$stmt->fetchColumn();

            // by_list aggregation — LEFT JOIN so lists with no todos still appear.
            $stmt = $this->pdo->prepare(
                "SELECT l.id AS list_id,
                        SUM(CASE WHEN t.status = 'open' THEN 1 ELSE 0 END) AS open_count,
                        COUNT(t.id) AS total_count
                   FROM todo_lists l
                   LEFT JOIN todos t ON t.list_id = l.id AND t.user_id = l.user_id
                  WHERE l.user_id = :uid
                  GROUP BY l.id"
            );
            $stmt->execute([':uid' => $userId]);
            $byList = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $byList[(string)$row['list_id']] = [
                    'open'  => (int)$row['open_count'],
                    'total' => (int)$row['total_count'],
                ];
            }

            $this->respond([
                'success'   => true,
                'open'      => $open,
                'due_today' => $dueToday,
                'overdue'   => $overdue,
                'by_list'   => $byList,
            ]);
        } catch (\PDOException $e) {
            error_log('TodoController::counts ' . $e->getMessage());
            $this->respond(['success' => false, 'message' => 'Database error'], 500);
        }
    }
}
