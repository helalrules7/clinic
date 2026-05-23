<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Config\Database;
use PDO;

/**
 * CommentsController — generic in-context comments thread.
 *
 * Endpoints
 *   GET    /api/comments/{type}/{id}      — list, newest-first under pinned
 *   POST   /api/comments/{type}/{id}      — create  body: {body, parent_id?}
 *   PATCH  /api/comments/{id}             — edit body or toggle pinned
 *   DELETE /api/comments/{id}             — soft delete
 *
 * type must be 'patient' or 'appointment'. The thread is keyed on
 * (commentable_type, commentable_id) so the same UI partial drives both
 * surfaces (appointment.php + patient.php).
 *
 * @-mentions: any `@username` in the body is auto-resolved against the
 * users table; a comment_mentions row is created for each match, and a
 * notification row is written so the mentioned user sees it via the
 * existing notifications dropdown.
 *
 * Threading: single-level — `parent_id` may point at a top-level
 * comment, but not at another reply. The UI hides the Reply button on
 * comments that themselves have parent_id set.
 *
 * Permissions: any logged-in user can read. Only the author or an admin
 * can edit/delete a comment. Anyone with auth can pin (per the plan;
 * adjust here if you want stricter pinning).
 */
class CommentsController
{
    private $auth;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->pdo = Database::getInstance()->getConnection();
    }

    private function json($payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function requireAuth(): array
    {
        if (!$this->auth->check()) {
            $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }
        return $this->auth->user();
    }

    private function readBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '', true);
        return is_array($data) ? $data : ($_POST ?: []);
    }

    private function normalizeType(string $type): ?string
    {
        $t = strtolower(trim($type));
        return in_array($t, ['patient', 'appointment', 'board_card'], true) ? $t : null;
    }

    // ---------- List --------------------------------------------------------

    public function listFor($type, $id)
    {
        $user = $this->requireAuth();
        $type = $this->normalizeType((string) $type);
        $id   = (int) $id;
        if ($type === null || $id <= 0) {
            $this->json(['ok' => false, 'error' => 'bad type/id'], 400);
        }

        // Pinned (non-deleted) first, then chronological.
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.commentable_type, c.commentable_id, c.parent_id,
                   c.user_id, c.body, c.pinned, c.edited_at, c.deleted_at,
                   c.created_at,
                   u.name AS author_name,
                   u.profile_image AS author_image
              FROM comments c
              LEFT JOIN users u ON u.id = c.user_id
             WHERE c.commentable_type = ? AND c.commentable_id = ?
             ORDER BY c.pinned DESC, c.created_at ASC, c.id ASC
        ");
        $stmt->execute([$type, $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Bulk-load attachments + mentions for these comments so the UI can
        // render image/audio media and @-mention avatar badges in one pass.
        $ids = array_map(static fn($r) => (int) $r['id'], $rows);
        $attByComment     = $this->attachmentsForComments($ids);
        $mentionByComment = $this->mentionsForComments($ids);

        // Mark which comments the caller can edit / delete.
        foreach ($rows as &$r) {
            $cid = (int) $r['id'];
            $r['can_edit'] = ($r['user_id'] == $user['id'] || ($user['role'] ?? '') === 'admin');
            $r['attachments'] = $attByComment[$cid] ?? [];
            $r['mentions']    = $mentionByComment[$cid] ?? [];
            if ($r['deleted_at']) {
                // Don't ship the body for soft-deleted rows — the UI shows a
                // tombstone placeholder. Keep the row so threads still render.
                $r['body'] = '';
                $r['attachments'] = [];
            }
        }

        $this->json(['ok' => true, 'data' => $rows]);
    }

    // ---------- Create ------------------------------------------------------

    public function create($type, $id)
    {
        $user = $this->requireAuth();
        $type = $this->normalizeType((string) $type);
        $id   = (int) $id;
        if ($type === null || $id <= 0) {
            $this->json(['ok' => false, 'error' => 'bad type/id'], 400);
        }

        $b = $this->readBody();
        $body = trim((string) ($b['body'] ?? ''));

        // Staged attachment ids (uploaded before the comment text existed).
        // A comment may be media-only, so an empty body is allowed when at
        // least one valid, still-unlinked attachment of this user is present.
        $attachmentIds = [];
        if (!empty($b['attachment_ids']) && is_array($b['attachment_ids'])) {
            foreach ($b['attachment_ids'] as $aid) {
                $aid = (int) $aid;
                if ($aid > 0) $attachmentIds[] = $aid;
            }
            $attachmentIds = array_values(array_unique($attachmentIds));
        }

        if ($body === '' && !$attachmentIds) {
            $this->json(['ok' => false, 'error' => 'body or attachment required'], 400);
        }
        if (mb_strlen($body) > 4000) {
            $this->json(['ok' => false, 'error' => 'body too long (max 4000)'], 400);
        }

        $parentId = null;
        if (!empty($b['parent_id'])) {
            $p = (int) $b['parent_id'];
            $check = $this->pdo->prepare("
                SELECT parent_id, commentable_type, commentable_id
                  FROM comments WHERE id = ?
            ");
            $check->execute([$p]);
            $parent = $check->fetch(PDO::FETCH_ASSOC);
            if (!$parent) {
                $this->json(['ok' => false, 'error' => 'parent not found'], 404);
            }
            if ($parent['parent_id'] !== null) {
                $this->json(['ok' => false, 'error' => 'replies are single-level only'], 422);
            }
            if ($parent['commentable_type'] !== $type || (int) $parent['commentable_id'] !== $id) {
                $this->json(['ok' => false, 'error' => 'parent belongs to a different thread'], 422);
            }
            $parentId = $p;
        }

        $ins = $this->pdo->prepare("
            INSERT INTO comments
                (commentable_type, commentable_id, parent_id, user_id, body, created_at)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $ins->execute([$type, $id, $parentId, $user['id'], $body]);
        $commentId = (int) $this->pdo->lastInsertId();

        // Link any staged attachments this user uploaded (comment_id still
        // NULL) to the freshly-created comment. Scoped to the author so one
        // user can't steal another's staged uploads.
        if ($attachmentIds) {
            $ph = implode(',', array_fill(0, count($attachmentIds), '?'));
            $link = $this->pdo->prepare("
                UPDATE comment_attachments
                   SET comment_id = ?
                 WHERE id IN ($ph) AND user_id = ? AND comment_id IS NULL
            ");
            $link->execute(array_merge([$commentId], $attachmentIds, [(int) $user['id']]));
        }

        // Resolve @-mentions and notify, throttled to one notification per
        // mentioning-comment-and-mentioned-user (the throttle is enforced
        // by the unique (mentioned_user_id, comment_id) shape — duplicate
        // INSERTs will hit the unique index. We don't have such an index
        // by default, so dedup in PHP first.
        $this->processMentions($commentId, $body, $user['id'], $type, $id);

        $this->json(['ok' => true, 'data' => ['id' => $commentId]]);
    }

    // ---------- Patch (edit body or toggle pinned) --------------------------

    public function patch($id)
    {
        $user = $this->requireAuth();
        $id = (int) $id;
        $row = $this->pdo->prepare("SELECT * FROM comments WHERE id = ?");
        $row->execute([$id]);
        $c = $row->fetch(PDO::FETCH_ASSOC);
        if (!$c) {
            $this->json(['ok' => false, 'error' => 'not found'], 404);
        }

        $isAuthor = ((int) $c['user_id'] === (int) $user['id']);
        $isAdmin  = (($user['role'] ?? '') === 'admin');

        $b = $this->readBody();
        $set = [];
        $params = [];

        if (array_key_exists('body', $b)) {
            if (!$isAuthor && !$isAdmin) {
                $this->json(['ok' => false, 'error' => 'forbidden'], 403);
            }
            $body = trim((string) $b['body']);
            if ($body === '' || mb_strlen($body) > 4000) {
                $this->json(['ok' => false, 'error' => 'body required (1–4000 chars)'], 400);
            }
            $set[] = 'body = ?';
            $params[] = $body;
            $set[] = 'edited_at = CURRENT_TIMESTAMP';
        }

        if (array_key_exists('pinned', $b)) {
            $set[] = 'pinned = ?';
            $params[] = $b['pinned'] ? 1 : 0;
        }

        if (!$set) {
            $this->json(['ok' => false, 'error' => 'no fields to update'], 400);
        }

        $params[] = $id;
        $this->pdo->prepare('UPDATE comments SET ' . implode(', ', $set) . ' WHERE id = ?')
                  ->execute($params);
        $this->json(['ok' => true]);
    }

    // ---------- Soft delete ------------------------------------------------

    public function delete($id)
    {
        $user = $this->requireAuth();
        $id = (int) $id;
        $row = $this->pdo->prepare("SELECT user_id, deleted_at FROM comments WHERE id = ?");
        $row->execute([$id]);
        $c = $row->fetch(PDO::FETCH_ASSOC);
        if (!$c) {
            $this->json(['ok' => false, 'error' => 'not found'], 404);
        }
        if ($c['deleted_at']) {
            // Already soft-deleted — idempotent.
            $this->json(['ok' => true]);
        }
        $isAuthor = ((int) $c['user_id'] === (int) $user['id']);
        $isAdmin  = (($user['role'] ?? '') === 'admin');
        if (!$isAuthor && !$isAdmin) {
            $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        $this->pdo->prepare("UPDATE comments SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?")
                  ->execute([$id]);
        $this->json(['ok' => true]);
    }

    // ---------- Mentions ---------------------------------------------------

    private function processMentions(int $commentId, string $body, int $authorId, string $type, int $targetId): void
    {
        $users = [];

        // Preferred form: structured token `@[Display Name](u:ID)` emitted by
        // the autocomplete. The id is authoritative — no name guessing.
        if (preg_match_all('/@\[[^\]\n]+\]\(u:(\d+)\)/u', $body, $mu)) {
            $idTokens = array_values(array_unique(array_map('intval', $mu[1])));
            if ($idTokens) {
                $ph = implode(',', array_fill(0, count($idTokens), '?'));
                $stmt = $this->pdo->prepare("SELECT id, name FROM users WHERE id IN ($ph)");
                $stmt->execute($idTokens);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
        }

        // Legacy/loose form: bare `@name` typed without the picker. Strip the
        // structured tokens first so their inner text isn't double-matched.
        $bare = preg_replace('/@\[[^\]\n]+\]\(u:\d+\)/u', ' ', $body);
        if (preg_match_all('/@([A-Za-z0-9_.\-]{2,40})/u', (string) $bare, $m)) {
            $tokens = array_unique($m[1]);
            if ($tokens) {
                $placeholders = implode(',', array_fill(0, count($tokens), '?'));
                $sql = "SELECT id, name FROM users WHERE name IN ($placeholders) OR LOWER(name) IN ($placeholders)";
                $args = array_merge($tokens, array_map('strtolower', $tokens));
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($args);
                $users = array_merge($users, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
            }
        }

        if (!$users) return;

        // Readable snippet for the notification body: collapse the structured
        // tokens `@[Name](u:ID)` back to a plain `@Name`.
        $snippet = preg_replace('/@\[([^\]\n]+)\]\(u:\d+\)/u', '@$1', $body);
        $snippet = mb_substr(trim((string) $snippet), 0, 240);
        // Link target: appointments route off related_type; patient/board_card
        // both route off patient_id (board_card id IS the patient id).
        $relatedType = ($type === 'appointment') ? 'appointment' : 'comment';
        $patientId   = ($type === 'patient' || $type === 'board_card') ? $targetId : null;

        $seen = [];
        foreach ($users as $u) {
            $uid = (int) $u['id'];
            if ($uid === $authorId || isset($seen[$uid])) continue;
            $seen[$uid] = true;

            $this->pdo->prepare("
                INSERT INTO comment_mentions (comment_id, mentioned_user_id, created_at)
                VALUES (?, ?, CURRENT_TIMESTAMP)
            ")->execute([$commentId, $uid]);

            // Best-effort notification via the shared helper (matches the live
            // notifications schema + the dropdown's link rules). Swallow errors
            // so a notification hiccup never blocks the comment write.
            try {
                \App\Controllers\NotificationController::create(
                    $uid, 'comment_mention', 'You were mentioned in a comment',
                    $snippet, $relatedType, $targetId, $patientId
                );
            } catch (\Throwable $e) {
                // notifications optional — swallow.
            }
        }
    }

    // ---------- Attachments + mentions bulk loaders ------------------------

    private function attachmentsForComments(array $ids): array
    {
        if (!$ids) return [];
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("
            SELECT id, comment_id, kind, mime_type, original_name, file_size, duration_ms
              FROM comment_attachments
             WHERE comment_id IN ($ph)
             ORDER BY id ASC
        ");
        $stmt->execute($ids);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $a) {
            $cid = (int) $a['comment_id'];
            $out[$cid][] = [
                'id'          => (int) $a['id'],
                'kind'        => $a['kind'],
                'mime'        => $a['mime_type'],
                'name'        => $a['original_name'],
                'size'        => (int) $a['file_size'],
                'duration_ms' => $a['duration_ms'] !== null ? (int) $a['duration_ms'] : null,
                'url'         => '/api/comments/attachments/' . (int) $a['id'],
            ];
        }
        return $out;
    }

    private function mentionsForComments(array $ids): array
    {
        if (!$ids) return [];
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("
            SELECT cm.comment_id, cm.mentioned_user_id AS user_id,
                   u.name, u.profile_image
              FROM comment_mentions cm
              LEFT JOIN users u ON u.id = cm.mentioned_user_id
             WHERE cm.comment_id IN ($ph)
        ");
        $stmt->execute($ids);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $cid = (int) $r['comment_id'];
            $out[$cid][] = [
                'user_id'       => (int) $r['user_id'],
                'name'          => $r['name'],
                'profile_image' => $r['profile_image'],
            ];
        }
        return $out;
    }

    // ---------- Attachment upload + streaming ------------------------------

    /** POST /api/comments/attachments  (multipart, field "file") */
    public function uploadAttachment()
    {
        $user = $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->json(['ok' => false, 'error' => 'method not allowed'], 405);
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['ok' => false, 'error' => 'no file uploaded'], 400);
        }
        $file = $_FILES['file'];

        // 12 MB cap — a phone photo or ~1 min of compressed audio.
        if ($file['size'] > 12 * 1024 * 1024) {
            $this->json(['ok' => false, 'error' => 'file too large (max 12MB)'], 400);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp',
                       'image/heic', 'image/heif'];                 // iPhone photos
        // MediaRecorder labels its audio with the CONTAINER mime, which finfo
        // reports as video/* for webm and mp4 — Safari voice notes come through
        // as video/mp4. Accept those container types as audio too.
        $audioMimes = ['audio/webm', 'video/webm', 'audio/ogg', 'audio/mpeg', 'audio/mp3',
                       'audio/mp4', 'video/mp4', 'audio/x-m4a', 'audio/aac', 'audio/wav', 'audio/x-wav'];

        if (in_array($mime, $imageMimes, true)) {
            $kind = 'image';
        } elseif (in_array($mime, $audioMimes, true)) {
            $kind = 'audio';
        } else {
            $this->json(['ok' => false, 'error' => 'unsupported file type: ' . $mime], 400);
        }

        $ext = $this->extForMime($mime, (string) ($file['name'] ?? ''));
        $dir = __DIR__ . '/../../storage/uploads/comments/';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $fname = $kind . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest  = $dir . $fname;
        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            $this->json(['ok' => false, 'error' => 'failed to store file'], 500);
        }

        $duration = isset($_POST['duration_ms']) ? (int) $_POST['duration_ms'] : null;
        $orig = mb_substr((string) ($file['name'] ?? ''), 0, 255);

        $ins = $this->pdo->prepare("
            INSERT INTO comment_attachments
                (comment_id, user_id, kind, file_path, original_name, mime_type, file_size, duration_ms, created_at)
            VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $ins->execute([
            (int) $user['id'], $kind,
            'storage/uploads/comments/' . $fname,
            $orig, $mime, (int) $file['size'], $duration,
        ]);
        $aid = (int) $this->pdo->lastInsertId();

        $this->json(['ok' => true, 'attachment' => [
            'id'          => $aid,
            'kind'        => $kind,
            'mime'        => $mime,
            'name'        => $orig,
            'size'        => (int) $file['size'],
            'duration_ms' => $duration,
            'url'         => '/api/comments/attachments/' . $aid,
        ]]);
    }

    private function extForMime(string $mime, string $origName): string
    {
        $map = [
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
            'audio/webm' => 'webm', 'video/webm' => 'webm', 'audio/ogg' => 'ogg',
            'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3', 'audio/mp4' => 'm4a',
            'audio/x-m4a' => 'm4a', 'audio/aac' => 'aac', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav',
        ];
        if (isset($map[$mime])) return $map[$mime];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        return preg_match('/^[a-z0-9]{1,5}$/', $ext) ? $ext : 'bin';
    }

    /** GET /api/comments/attachments/{id} — stream with inline content-type */
    public function viewAttachment($id)
    {
        $this->requireAuth();
        $id = (int) $id;
        $stmt = $this->pdo->prepare("SELECT file_path, mime_type, original_name FROM comment_attachments WHERE id = ?");
        $stmt->execute([$id]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$a) { $this->json(['ok' => false, 'error' => 'not found'], 404); }

        $path = __DIR__ . '/../../' . ltrim($a['file_path'], '/');
        if (!is_file($path)) { $this->json(['ok' => false, 'error' => 'file missing'], 404); }

        if (ob_get_level()) { @ob_end_clean(); }
        $mime = $a['mime_type'] ?: 'application/octet-stream';
        $size = filesize($path);

        header('Content-Type: ' . $mime);
        header('Cache-Control: private, max-age=86400');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline; filename="' . rawurlencode($a['original_name'] ?: ('attachment-' . $id)) . '"');

        // HTTP Range support — Safari's <audio>/<video> elements request byte
        // ranges and expect a 206 Partial Content reply; serving a plain 200
        // for a ranged request makes WebKit reject playback (NotSupportedError)
        // and breaks seeking. Honour the Range header here.
        $start = 0;
        $end   = $size - 1;
        $range = $_SERVER['HTTP_RANGE'] ?? '';
        if ($range !== '' && preg_match('/bytes=(\d*)-(\d*)/', $range, $m)) {
            if ($m[1] !== '') { $start = (int) $m[1]; }
            if ($m[2] !== '') { $end   = (int) $m[2]; }
            if ($start > $end || $start >= $size) {
                header('HTTP/1.1 416 Range Not Satisfiable');
                header('Content-Range: bytes */' . $size);
                exit;
            }
            $end = min($end, $size - 1);
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
        }

        $length = $end - $start + 1;
        header('Content-Length: ' . $length);

        $fp = fopen($path, 'rb');
        if ($fp === false) { exit; }
        fseek($fp, $start);
        $remaining = $length;
        while ($remaining > 0 && !feof($fp)) {
            $chunk = fread($fp, (int) min(8192, $remaining));
            if ($chunk === false) { break; }
            echo $chunk;
            $remaining -= strlen($chunk);
            @flush();
        }
        fclose($fp);
        exit;
    }

    // ---------- Mentionable users (autocomplete) ---------------------------

    /** GET /api/users/search?q= — colleagues for @-mention suggestions */
    public function searchUsers()
    {
        $user = $this->requireAuth();
        $q = trim((string) ($_GET['q'] ?? ''));

        $sql  = "SELECT id, name, profile_image, role FROM users WHERE id <> ?";
        $args = [(int) $user['id']];
        if ($q !== '') {
            $sql .= " AND name LIKE ?";
            $args[] = '%' . $q . '%';
        }
        $sql .= " ORDER BY name ASC LIMIT 8";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        $this->json(['ok' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []]);
    }

    private function commentLink(string $type, int $targetId, int $commentId): string
    {
        if ($type === 'patient')     return "/doctor/patients/$targetId#comment-$commentId";
        if ($type === 'appointment') return "/doctor/appointments/$targetId#comment-$commentId";
        if ($type === 'board_card')  return "/doctor/board#patient-$targetId";
        return '#';
    }
}
