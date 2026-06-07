<?php
namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use PDO;

class NoteTemplateController
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /* ---------------------------------------------------------------- helpers */

    private function jsonHeader(): void
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
    }

    private function requireUser(): ?array
    {
        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return null;
        }
        return $user;
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function fail(int $code, string $message, array $extra = []): void
    {
        http_response_code($code);
        echo json_encode(array_merge(['success' => false, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function ok(array $payload): void
    {
        echo json_encode(array_merge(['success' => true], $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Fetch a single template scoped to user. Returns null if not found.
     */
    private function fetchOwned(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM note_templates WHERE id = :id AND user_id = :uid LIMIT 1');
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Normalise / validate input. Returns [data, error|null].
     * $partial=true allows missing fields (for PATCH).
     */
    private function validate(array $in, bool $partial): array
    {
        $data = [];
        $errors = [];

        if (!$partial || array_key_exists('title', $in)) {
            $title = isset($in['title']) ? trim((string)$in['title']) : '';
            if ($title === '') {
                $errors['title'] = 'Title is required.';
            } elseif (mb_strlen($title) > 120) {
                $errors['title'] = 'Title must be at most 120 characters.';
            } else {
                $data['title'] = $title;
            }
        }

        if (!$partial || array_key_exists('body', $in)) {
            // 2026-06-07: body is OPTIONAL. The "Add template" button creates a
            // blank DRAFT that the user fills in via auto-save (PATCH on blur),
            // so an empty body must be accepted on BOTH create and update —
            // otherwise the first save (with no body yet) 422s. Whitespace is
            // preserved; only the length ceiling is enforced.
            $body = isset($in['body']) ? (string)$in['body'] : '';
            if (mb_strlen($body) > 50000) {
                $errors['body'] = 'Body must be at most 50000 characters.';
            } else {
                $data['body'] = $body;
            }
        }

        if (array_key_exists('category', $in)) {
            $category = $in['category'];
            if ($category === null || $category === '') {
                $data['category'] = null;
            } else {
                $category = trim((string)$category);
                if (mb_strlen($category) > 40) {
                    $errors['category'] = 'Category must be at most 40 characters.';
                } else {
                    $data['category'] = $category;
                }
            }
        } elseif (!$partial) {
            $data['category'] = null;
        }

        if (array_key_exists('sort_order', $in)) {
            $so = $in['sort_order'];
            if ($so === null || $so === '') {
                $data['sort_order'] = 0;
            } elseif (is_numeric($so)) {
                $data['sort_order'] = (int)$so;
            } else {
                $errors['sort_order'] = 'sort_order must be an integer.';
            }
        }

        return [$data, $errors ?: null];
    }

    /* --------------------------------------------------------------- endpoints */

    public function index()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $sql = "SELECT id, user_id, title, body, category, sort_order, use_count, last_used_at, created_at, updated_at
                FROM note_templates
                WHERE user_id = :uid
                ORDER BY sort_order ASC,
                         CASE WHEN last_used_at IS NULL THEN 1 ELSE 0 END ASC,
                         last_used_at DESC,
                         title ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $user['id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Normalise numeric / null fields and build the grouped representation.
        $flat = [];
        $grouped = [];
        foreach ($rows as $r) {
            $r['id']         = (int)$r['id'];
            $r['user_id']    = (int)$r['user_id'];
            $r['sort_order'] = (int)$r['sort_order'];
            $r['use_count']  = (int)$r['use_count'];
            $flat[] = $r;

            $cat = ($r['category'] === null || $r['category'] === '') ? 'Uncategorized' : $r['category'];
            if (!isset($grouped[$cat])) $grouped[$cat] = [];
            $grouped[$cat][] = $r;
        }

        // Return grouped as a list of {category, items} so JS can iterate deterministically.
        $groupedList = [];
        foreach ($grouped as $cat => $items) {
            $groupedList[] = ['category' => $cat, 'items' => $items];
        }

        $this->ok([
            'data'    => $flat,
            'grouped' => $groupedList,
            'count'   => count($flat),
        ]);
    }

    public function show($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) { $this->fail(404, 'Template not found'); return; }

        $row = $this->fetchOwned($id, (int)$user['id']);
        if (!$row) { $this->fail(404, 'Template not found'); return; }

        $row['id']         = (int)$row['id'];
        $row['user_id']    = (int)$row['user_id'];
        $row['sort_order'] = (int)$row['sort_order'];
        $row['use_count']  = (int)$row['use_count'];

        $this->ok(['data' => $row]);
    }

    public function create()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $in = $this->readJsonBody();
        [$data, $errors] = $this->validate($in, false);
        if ($errors) { $this->fail(422, 'Validation failed', ['errors' => $errors]); return; }

        // Default sort_order to (max + 1) when not provided so new items append.
        if (!array_key_exists('sort_order', $data)) {
            $maxStmt = $this->pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) FROM note_templates WHERE user_id = :uid');
            $maxStmt->execute([':uid' => $user['id']]);
            $data['sort_order'] = ((int)$maxStmt->fetchColumn()) + 1;
        }

        $sql = "INSERT INTO note_templates (user_id, title, body, category, sort_order, use_count, last_used_at, created_at, updated_at)
                VALUES (:uid, :title, :body, :category, :sort_order, 0, NULL, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':uid'        => $user['id'],
            ':title'      => $data['title'],
            ':body'       => $data['body'],
            ':category'   => $data['category'] ?? null,
            ':sort_order' => $data['sort_order'],
        ]);

        $newId = (int)$this->pdo->lastInsertId();
        $row   = $this->fetchOwned($newId, (int)$user['id']);
        if ($row) {
            $row['id']         = (int)$row['id'];
            $row['user_id']    = (int)$row['user_id'];
            $row['sort_order'] = (int)$row['sort_order'];
            $row['use_count']  = (int)$row['use_count'];
        }

        http_response_code(200);
        $this->ok(['data' => $row]);
    }

    public function update($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) { $this->fail(404, 'Template not found'); return; }

        $existing = $this->fetchOwned($id, (int)$user['id']);
        if (!$existing) { $this->fail(404, 'Template not found'); return; }

        $in = $this->readJsonBody();
        [$data, $errors] = $this->validate($in, true);
        if ($errors) { $this->fail(422, 'Validation failed', ['errors' => $errors]); return; }

        if (empty($data)) {
            // Nothing to update — return existing.
            $existing['id']         = (int)$existing['id'];
            $existing['user_id']    = (int)$existing['user_id'];
            $existing['sort_order'] = (int)$existing['sort_order'];
            $existing['use_count']  = (int)$existing['use_count'];
            $this->ok(['data' => $existing]);
            return;
        }

        $sets   = [];
        $params = [':id' => $id, ':uid' => $user['id']];
        foreach ($data as $col => $val) {
            $sets[]                = "$col = :$col";
            $params[":$col"]       = $val;
        }
        $sets[] = 'updated_at = NOW()';
        $sql = 'UPDATE note_templates SET ' . implode(', ', $sets) . ' WHERE id = :id AND user_id = :uid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $this->fetchOwned($id, (int)$user['id']);
        if ($row) {
            $row['id']         = (int)$row['id'];
            $row['user_id']    = (int)$row['user_id'];
            $row['sort_order'] = (int)$row['sort_order'];
            $row['use_count']  = (int)$row['use_count'];
        }

        $this->ok(['data' => $row]);
    }

    public function delete($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) { $this->fail(404, 'Template not found'); return; }

        $existing = $this->fetchOwned($id, (int)$user['id']);
        if (!$existing) { $this->fail(404, 'Template not found'); return; }

        $stmt = $this->pdo->prepare('DELETE FROM note_templates WHERE id = :id AND user_id = :uid');
        $stmt->execute([':id' => $id, ':uid' => $user['id']]);

        $this->ok(['deleted' => true, 'id' => $id]);
    }

    public function reorder()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $in  = $this->readJsonBody();
        $ids = $in['ids'] ?? null;
        if (!is_array($ids)) { $this->fail(422, 'ids must be an array'); return; }

        // Sanitize to positive ints, preserve order, drop dupes.
        $clean = [];
        $seen  = [];
        foreach ($ids as $raw) {
            if (!is_numeric($raw)) continue;
            $i = (int)$raw;
            if ($i <= 0 || isset($seen[$i])) continue;
            $seen[$i] = true;
            $clean[]  = $i;
        }

        if (empty($clean)) { $this->ok(['updated' => 0]); return; }

        // Make sure every id belongs to this user; ignore foreign/missing ones silently.
        $place = implode(',', array_fill(0, count($clean), '?'));
        $check = $this->pdo->prepare("SELECT id FROM note_templates WHERE user_id = ? AND id IN ($place)");
        $check->execute(array_merge([$user['id']], $clean));
        $owned = [];
        foreach ($check->fetchAll(PDO::FETCH_COLUMN) as $oid) { $owned[(int)$oid] = true; }

        $this->pdo->beginTransaction();
        try {
            $upd = $this->pdo->prepare('UPDATE note_templates SET sort_order = :so, updated_at = NOW() WHERE id = :id AND user_id = :uid');
            $count = 0;
            foreach ($clean as $idx => $tid) {
                if (!isset($owned[$tid])) continue;
                $upd->execute([':so' => $idx, ':id' => $tid, ':uid' => $user['id']]);
                $count++;
            }
            $this->pdo->commit();
            $this->ok(['updated' => $count]);
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $this->fail(500, 'Failed to reorder templates');
        }
    }

    public function markUsed($id)
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $id = (int)$id;
        if ($id <= 0) { $this->fail(404, 'Template not found'); return; }

        $existing = $this->fetchOwned($id, (int)$user['id']);
        if (!$existing) { $this->fail(404, 'Template not found'); return; }

        $stmt = $this->pdo->prepare('UPDATE note_templates
                                     SET use_count = use_count + 1,
                                         last_used_at = NOW(),
                                         updated_at = NOW()
                                     WHERE id = :id AND user_id = :uid');
        $stmt->execute([':id' => $id, ':uid' => $user['id']]);

        $row = $this->fetchOwned($id, (int)$user['id']);
        if ($row) {
            $row['id']         = (int)$row['id'];
            $row['user_id']    = (int)$row['user_id'];
            $row['sort_order'] = (int)$row['sort_order'];
            $row['use_count']  = (int)$row['use_count'];
        }

        $this->ok(['data' => $row]);
    }

    public function seedDefaults()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM note_templates WHERE user_id = :uid');
        $countStmt->execute([':uid' => $user['id']]);
        $existing = (int)$countStmt->fetchColumn();

        if ($existing > 0) {
            $this->ok(['seeded' => false, 'count' => $existing]);
            return;
        }

        $defaults = [
            ['Normal exam',     'Patient appears in good general health. No acute distress. Vital signs stable.',                                                                     'Examination', 0],
            ['Follow-up plan',  'Schedule follow-up in 2 weeks for re-evaluation. Patient to return immediately if symptoms worsen.',                                                  'Plan',        1],
            ['Counselling',     'Discussed condition, treatment options, and expected course with the patient. Patient verbalizes understanding and consents to plan.',               'Discussion',  2],
        ];

        $ins = $this->pdo->prepare('INSERT INTO note_templates (user_id, title, body, category, sort_order, use_count, last_used_at, created_at, updated_at)
                                    VALUES (:uid, :title, :body, :category, :sort_order, 0, NULL, NOW(), NOW())');

        $this->pdo->beginTransaction();
        try {
            $inserted = 0;
            foreach ($defaults as [$title, $body, $category, $sortOrder]) {
                $ins->execute([
                    ':uid'        => $user['id'],
                    ':title'      => $title,
                    ':body'       => $body,
                    ':category'   => $category,
                    ':sort_order' => $sortOrder,
                ]);
                $inserted++;
            }
            $this->pdo->commit();
            $this->ok(['seeded' => true, 'count' => $inserted]);
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $this->fail(500, 'Failed to seed default templates');
        }
    }
}
