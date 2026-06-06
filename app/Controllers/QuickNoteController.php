<?php
namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use App\Models\QuickNoteModel;

class QuickNoteController
{
    private $pdo;
    private $auth;
    private $model;

    public function __construct()
    {
        $this->pdo   = Database::getInstance()->getConnection();
        $this->auth  = new Auth();
        $this->model = new QuickNoteModel();
    }

    /* ---------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------- */

    private function bootstrap()
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
    }

    private function requireUser()
    {
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

    private function jsonBody()
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function respond($code, array $payload)
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function toBool($val)
    {
        if (is_bool($val))   return $val;
        if (is_int($val))    return $val === 1;
        if (is_string($val)) {
            $v = strtolower(trim($val));
            return in_array($v, ['1', 'true', 'yes', 'on'], true);
        }
        return false;
    }

    /* ---------------------------------------------------------------------
     * Endpoints
     * ------------------------------------------------------------------- */

    // GET /api/quick-notes
    public function index()
    {
        $this->bootstrap();
        $user = $this->requireUser();
        if (!$user) return;

        try {
            $rows = $this->model->list((int)$user['id'], ['limit' => 100]);
            $this->respond(200, ['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            error_log('[QuickNoteController::index] ' . $e->getMessage());
            $this->respond(500, ['success' => false, 'message' => 'Failed to load quick notes']);
        }
    }

    // GET /api/quick-notes/:id
    public function show($id)
    {
        $this->bootstrap();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(422, ['success' => false, 'message' => 'Invalid id']);
            return;
        }

        try {
            $note = $this->model->findById($id, (int)$user['id']);
            if (!$note) {
                $this->respond(404, ['success' => false, 'message' => 'Quick note not found']);
                return;
            }
            $this->respond(200, ['success' => true, 'data' => $note]);
        } catch (\Throwable $e) {
            error_log('[QuickNoteController::show] ' . $e->getMessage());
            $this->respond(500, ['success' => false, 'message' => 'Failed to load quick note']);
        }
    }

    // POST /api/quick-notes
    public function create()
    {
        $this->bootstrap();
        $user = $this->requireUser();
        if (!$user) return;

        $data = $this->jsonBody();

        $title  = isset($data['title']) ? trim((string)$data['title']) : '';
        $body   = isset($data['body'])  ? (string)$data['body']        : '';
        $pinned = isset($data['pinned']) ? $this->toBool($data['pinned']) : false;
        $bg     = isset($data['background_color']) ? trim((string)$data['background_color']) : '';

        // Validation
        if (mb_strlen($title) > 200) {
            $this->respond(422, ['success' => false, 'message' => 'Title must be 200 characters or fewer']);
            return;
        }
        $bodyTrimmed = trim($body);
        if ($bodyTrimmed === '') {
            $this->respond(422, ['success' => false, 'message' => 'Body is required']);
            return;
        }
        if (mb_strlen($body) > 50000) {
            $this->respond(422, ['success' => false, 'message' => 'Body must be 50000 characters or fewer']);
            return;
        }

        try {
            $note = $this->model->create((int)$user['id'], [
                'title'            => $title === '' ? null : $title,
                'body'             => $body,
                'background_color' => $bg === '' ? null : $bg,
                'pinned'           => $pinned ? 1 : 0,
            ]);

            // QuickNoteModel::create() returns the freshly-inserted row (or null).
            if (is_array($note) && isset($note['id'])) {
                $note = $this->model->findById((int)$note['id'], (int)$user['id']);
            }
            $this->respond(200, ['success' => true, 'data' => $note]);
        } catch (\Throwable $e) {
            error_log('[QuickNoteController::create] ' . $e->getMessage());
            $this->respond(500, ['success' => false, 'message' => 'Failed to create quick note']);
        }
    }

    // PATCH /api/quick-notes/:id
    public function update($id)
    {
        $this->bootstrap();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(422, ['success' => false, 'message' => 'Invalid id']);
            return;
        }

        $existing = $this->model->findById($id, (int)$user['id']);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'Quick note not found']);
            return;
        }

        $data = $this->jsonBody();
        $fields = [];

        if (array_key_exists('title', $data)) {
            $title = $data['title'] === null ? '' : trim((string)$data['title']);
            if (mb_strlen($title) > 200) {
                $this->respond(422, ['success' => false, 'message' => 'Title must be 200 characters or fewer']);
                return;
            }
            $fields['title'] = $title === '' ? null : $title;
        }

        if (array_key_exists('body', $data)) {
            $body = (string)$data['body'];
            if (trim($body) === '') {
                $this->respond(422, ['success' => false, 'message' => 'Body is required']);
                return;
            }
            if (mb_strlen($body) > 50000) {
                $this->respond(422, ['success' => false, 'message' => 'Body must be 50000 characters or fewer']);
                return;
            }
            $fields['body'] = $body;
        }

        if (array_key_exists('pinned', $data)) {
            $fields['pinned'] = $this->toBool($data['pinned']) ? 1 : 0;
        }

        if (array_key_exists('background_color', $data)) {
            $bg = $data['background_color'] === null ? null : trim((string)$data['background_color']);
            $fields['background_color'] = ($bg === '' ? null : $bg);
        }

        if (empty($fields)) {
            $this->respond(422, ['success' => false, 'message' => 'No updatable fields supplied']);
            return;
        }

        try {
            $this->model->update($id, (int)$user['id'], $fields);
            $note = $this->model->findById($id, (int)$user['id']);
            $this->respond(200, ['success' => true, 'data' => $note]);
        } catch (\Throwable $e) {
            error_log('[QuickNoteController::update] ' . $e->getMessage());
            $this->respond(500, ['success' => false, 'message' => 'Failed to update quick note']);
        }
    }

    // DELETE /api/quick-notes/:id
    public function delete($id)
    {
        $this->bootstrap();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(422, ['success' => false, 'message' => 'Invalid id']);
            return;
        }

        $existing = $this->model->findById($id, (int)$user['id']);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'Quick note not found']);
            return;
        }

        try {
            $this->model->delete($id, (int)$user['id']);
            $this->respond(200, ['success' => true, 'message' => 'Quick note deleted']);
        } catch (\Throwable $e) {
            error_log('[QuickNoteController::delete] ' . $e->getMessage());
            $this->respond(500, ['success' => false, 'message' => 'Failed to delete quick note']);
        }
    }

    // POST /api/quick-notes/:id/pin
    public function pin($id)
    {
        $this->bootstrap();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(422, ['success' => false, 'message' => 'Invalid id']);
            return;
        }

        $existing = $this->model->findById($id, (int)$user['id']);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'Quick note not found']);
            return;
        }

        try {
            $this->model->setPinned($id, (int)$user['id'], true);
            $note = $this->model->findById($id, (int)$user['id']);
            $this->respond(200, ['success' => true, 'data' => $note]);
        } catch (\Throwable $e) {
            error_log('[QuickNoteController::pin] ' . $e->getMessage());
            $this->respond(500, ['success' => false, 'message' => 'Failed to pin quick note']);
        }
    }

    // POST /api/quick-notes/:id/unpin
    public function unpin($id)
    {
        $this->bootstrap();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) {
            $this->respond(422, ['success' => false, 'message' => 'Invalid id']);
            return;
        }

        $existing = $this->model->findById($id, (int)$user['id']);
        if (!$existing) {
            $this->respond(404, ['success' => false, 'message' => 'Quick note not found']);
            return;
        }

        try {
            $this->model->setPinned($id, (int)$user['id'], false);
            $note = $this->model->findById($id, (int)$user['id']);
            $this->respond(200, ['success' => true, 'data' => $note]);
        } catch (\Throwable $e) {
            error_log('[QuickNoteController::unpin] ' . $e->getMessage());
            $this->respond(500, ['success' => false, 'message' => 'Failed to unpin quick note']);
        }
    }
}
