<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Config\Database;
use PDO;

/**
 * ChatController — real-time-ish doctor↔secretary chat over adaptive polling.
 *
 * The cursor is `chat_messages.rev`: every send/edit/delete/reaction bumps the
 * conversation's `rev_counter` (atomically via LAST_INSERT_ID) and stamps the
 * affected message's `rev`, so a single `WHERE rev > :after_rev` poll carries new
 * messages AND edits/deletes/reactions. See CHAT_FEATURE_PLAN.md.
 *
 * Scope (admins excluded entirely):
 *   doctor    → all other doctors + secretaries of clinics where he has appointments
 *   secretary → doctors with appointments at her clinic + sibling secretaries
 * Edit/delete: a DOCTOR may edit/delete only his own messages (silent; deletes vanish).
 */
class ChatController
{
    private $auth;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->pdo  = Database::getInstance()->getConnection();
    }

    // ---------- helpers -----------------------------------------------------

    private function json($payload, int $status = 200): void
    {
        if (ob_get_level()) { @ob_end_clean(); }
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
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw ?: '', true);
        return is_array($data) ? $data : ($_POST ?: []);
    }

    /** Atomic per-conversation monotonic counter; returns the new rev. */
    private function bumpRev(int $conversationId): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE chat_conversations
                SET rev_counter = LAST_INSERT_ID(rev_counter + 1), last_activity_at = NOW()
              WHERE id = ?"
        );
        $stmt->execute([$conversationId]);
        return (int) $this->pdo->lastInsertId();
    }

    private function isParticipant(int $conversationId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM chat_participants WHERE conversation_id = ? AND user_id = ? AND left_at IS NULL LIMIT 1"
        );
        $stmt->execute([$conversationId, $userId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * User ids the current user is allowed to start a chat with (admins excluded).
     * @return int[]
     */
    private function rosterUserIds(array $me): array
    {
        $uid  = (int) $me['id'];
        $role = $me['role'] ?? '';

        if ($role === 'doctor') {
            $did = (int) ($this->pdo->query("SELECT id FROM doctors WHERE user_id = " . $uid . " LIMIT 1")->fetchColumn() ?: 0);
            $clinics = [];
            if ($did) {
                $clinics = $this->pdo->query(
                    "SELECT DISTINCT clinic_id FROM appointments WHERE doctor_id = " . $did . " AND clinic_id IS NOT NULL"
                )->fetchAll(PDO::FETCH_COLUMN);
            }
            $ids = $this->pdo->query("SELECT id FROM users WHERE role = 'doctor' AND id <> " . $uid)->fetchAll(PDO::FETCH_COLUMN);
            if ($clinics) {
                $ph = implode(',', array_map('intval', $clinics));
                $secs = $this->pdo->query("SELECT id FROM users WHERE role = 'secretary' AND clinic_id IN ($ph)")->fetchAll(PDO::FETCH_COLUMN);
                $ids = array_merge($ids, $secs);
            }
            return array_values(array_unique(array_map('intval', $ids)));
        }

        if ($role === 'secretary') {
            $clinic = (int) ($me['clinic_id'] ?? 0);
            if (!$clinic) { return []; }
            $docs = $this->pdo->query(
                "SELECT DISTINCT d.user_id
                   FROM appointments a JOIN doctors d ON d.id = a.doctor_id
                  WHERE a.clinic_id = " . $clinic . " AND d.user_id IS NOT NULL"
            )->fetchAll(PDO::FETCH_COLUMN);
            $secs = $this->pdo->query(
                "SELECT id FROM users WHERE role = 'secretary' AND clinic_id = " . $clinic . " AND id <> " . $uid
            )->fetchAll(PDO::FETCH_COLUMN);
            return array_values(array_unique(array_map('intval', array_merge($docs, $secs))));
        }

        return []; // admins / others: no chat
    }

    private function dmKey(int $a, int $b): string
    {
        return min($a, $b) . ':' . max($a, $b);
    }

    /** Serialize one message row (+ attachments + reactions) for the client. */
    private function hydrateMessages(array $rows): array
    {
        if (!$rows) { return []; }
        $ids = array_map(fn($r) => (int) $r['id'], $rows);
        $ph  = implode(',', $ids);

        // attachments
        $att = [];
        foreach ($this->pdo->query(
            "SELECT id, chat_message_id, kind, mime_type, original_name, file_size, duration_ms
               FROM chat_attachments WHERE chat_message_id IN ($ph)"
        )->fetchAll(PDO::FETCH_ASSOC) as $a) {
            $att[(int) $a['chat_message_id']][] = [
                'id' => (int) $a['id'], 'kind' => $a['kind'], 'mime' => $a['mime_type'],
                'name' => $a['original_name'], 'size' => (int) $a['file_size'],
                'duration_ms' => $a['duration_ms'] !== null ? (int) $a['duration_ms'] : null,
                'url' => '/api/chat/attachments/' . (int) $a['id'],
            ];
        }
        // reactions grouped by message + emoji
        $rx = [];
        foreach ($this->pdo->query(
            "SELECT message_id, emoji, user_id FROM chat_reactions WHERE message_id IN ($ph)"
        )->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $mid = (int) $r['message_id'];
            $rx[$mid][$r['emoji']]['emoji'] = $r['emoji'];
            $rx[$mid][$r['emoji']]['count'] = ($rx[$mid][$r['emoji']]['count'] ?? 0) + 1;
            $rx[$mid][$r['emoji']]['users'][] = (int) $r['user_id'];
        }

        $out = [];
        foreach ($rows as $r) {
            $mid = (int) $r['id'];
            $deleted = $r['deleted_at'] !== null;
            $out[] = [
                'id'          => $mid,
                'conversation_id' => (int) $r['conversation_id'],
                'sender_id'   => (int) $r['sender_id'],
                'sender_name' => $r['sender_name'] ?? '',
                'body'        => $deleted ? null : $r['body'],
                'reply_to_id' => $r['reply_to_id'] !== null ? (int) $r['reply_to_id'] : null,
                'rev'         => (int) $r['rev'],
                'edited'      => $r['edited_at'] !== null && !$deleted,
                'deleted'     => $deleted,
                'created_at'  => $r['created_at'],
                'attachments' => $deleted ? [] : array_values($att[$mid] ?? []),
                'reactions'   => $deleted ? [] : array_values($rx[$mid] ?? []),
            ];
        }
        return $out;
    }

    // ---------- endpoints ---------------------------------------------------

    /** GET /api/chat/roster — users I can start a conversation with. */
    public function roster()
    {
        $me  = $this->requireAuth();
        $ids = $this->rosterUserIds($me);
        if (!$ids) { $this->json(['ok' => true, 'users' => []]); }
        $ph = implode(',', $ids);
        $rows = $this->pdo->query(
            "SELECT id, name, username, role, profile_image FROM users WHERE id IN ($ph) ORDER BY role, name"
        )->fetchAll(PDO::FETCH_ASSOC);
        $users = array_map(fn($u) => [
            'id' => (int) $u['id'], 'name' => $u['name'] ?: $u['username'],
            'role' => $u['role'], 'avatar' => $u['profile_image'] ?: null,
        ], $rows);
        $this->json(['ok' => true, 'users' => $users]);
    }

    /** GET /api/chat/version — cheap badge poll. */
    public function version()
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id']; // safe int → inlined (native PDO prepares forbid reusing a named param)
        $r = $this->pdo->query(
            "SELECT COALESCE(MAX(c.rev_counter),0) AS rev,
                    COALESCE(SUM((
                       SELECT COUNT(*) FROM chat_messages m
                        WHERE m.conversation_id = c.id
                          AND m.sender_id <> $uid AND m.deleted_at IS NULL
                          AND m.id > COALESCE(p.last_read_message_id, 0)
                    )),0) AS unread
               FROM chat_participants p
               JOIN chat_conversations c ON c.id = p.conversation_id
              WHERE p.user_id = $uid AND p.left_at IS NULL"
        )->fetch(PDO::FETCH_ASSOC) ?: ['rev' => 0, 'unread' => 0];
        $this->json(['ok' => true, 'me' => $uid, 'conversations_rev' => (int) $r['rev'], 'unread_total' => (int) $r['unread']]);
    }

    /** GET /api/chat/conversations — my conversations with preview + unread. */
    public function conversations()
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id']; // safe int → inlined (native PDO prepares forbid reusing a named param)
        $rows = $this->pdo->query(
            "SELECT c.id, c.type, c.title, c.last_activity_at, c.rev_counter,
                    (SELECT COUNT(*) FROM chat_messages m
                       WHERE m.conversation_id = c.id AND m.sender_id <> $uid
                         AND m.deleted_at IS NULL AND m.id > COALESCE(p.last_read_message_id,0)) AS unread,
                    lm.body AS last_body, lm.sender_id AS last_sender, lm.created_at AS last_at, lm.deleted_at AS last_deleted
               FROM chat_participants p
               JOIN chat_conversations c ON c.id = p.conversation_id
          LEFT JOIN chat_messages lm ON lm.id = c.last_message_id
              WHERE p.user_id = $uid AND p.left_at IS NULL
           ORDER BY c.last_activity_at DESC
              LIMIT 200"
        );
        $convos = [];
        foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cid = (int) $c['id'];
            // other participants (for dm title/avatar + group member list)
            $parts = $this->pdo->prepare(
                "SELECT u.id, u.name, u.username, u.role, u.profile_image
                   FROM chat_participants pp JOIN users u ON u.id = pp.user_id
                  WHERE pp.conversation_id = ? AND pp.left_at IS NULL"
            );
            $parts->execute([$cid]);
            $members = array_map(fn($u) => [
                'id' => (int) $u['id'], 'name' => $u['name'] ?: $u['username'],
                'role' => $u['role'], 'avatar' => $u['profile_image'] ?: null,
            ], $parts->fetchAll(PDO::FETCH_ASSOC));
            $other = null;
            if ($c['type'] === 'dm') {
                foreach ($members as $m) { if ($m['id'] !== $uid) { $other = $m; break; } }
            }
            $convos[] = [
                'id' => $cid, 'type' => $c['type'],
                'title' => $c['type'] === 'group' ? $c['title'] : ($other['name'] ?? 'Chat'),
                'avatar' => $c['type'] === 'dm' ? ($other['avatar'] ?? null) : null,
                'other' => $other,
                'members' => $members,
                'unread' => (int) $c['unread'],
                'rev' => (int) $c['rev_counter'],
                'last' => [
                    'body' => $c['last_deleted'] !== null ? null : $c['last_body'],
                    'sender_id' => $c['last_sender'] !== null ? (int) $c['last_sender'] : null,
                    'at' => $c['last_at'],
                ],
                'last_activity_at' => $c['last_activity_at'],
            ];
        }
        $this->json(['ok' => true, 'conversations' => $convos]);
    }

    /** POST /api/chat/conversations — start a dm {user_id} or group {type:'group',title,member_ids[]}. */
    public function startConversation()
    {
        $me   = $this->requireAuth();
        $uid  = (int) $me['id'];
        $data = $this->readBody();
        $allowed = $this->rosterUserIds($me);

        if (($data['type'] ?? 'dm') === 'group') {
            $title   = trim((string) ($data['title'] ?? ''));
            $members = array_values(array_unique(array_map('intval', $data['member_ids'] ?? [])));
            $members = array_values(array_intersect($members, $allowed)); // only roster-allowed
            if ($title === '' || count($members) < 1) {
                $this->json(['ok' => false, 'error' => 'group needs a title and at least one member'], 422);
            }
            $this->pdo->prepare("INSERT INTO chat_conversations (type, title, created_by, last_activity_at) VALUES ('group', ?, ?, NOW())")
                 ->execute([mb_substr($title, 0, 120), $uid]);
            $cid = (int) $this->pdo->lastInsertId();
            $ins = $this->pdo->prepare("INSERT INTO chat_participants (conversation_id, user_id, role) VALUES (?, ?, ?)");
            $ins->execute([$cid, $uid, 'admin']);
            foreach ($members as $mid) { if ($mid !== $uid) { $ins->execute([$cid, $mid, 'member']); } }
            $this->json(['ok' => true, 'conversation_id' => $cid]);
        }

        // dm
        $other = (int) ($data['user_id'] ?? 0);
        if ($other <= 0 || !in_array($other, $allowed, true)) {
            $this->json(['ok' => false, 'error' => 'you cannot start a chat with this user'], 403);
        }
        // Atomic get-or-create on the unique dm_key (avoids a TOCTOU race where two
        // concurrent starts both pass a SELECT then collide on the UNIQUE key).
        $key = $this->dmKey($uid, $other);
        $this->pdo->prepare(
            "INSERT INTO chat_conversations (type, created_by, dm_key, last_activity_at)
             VALUES ('dm', ?, ?, NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
        )->execute([$uid, $key]);
        $cid = (int) $this->pdo->lastInsertId();
        // ensure both participants (idempotent — re-opening a DM reuses the row)
        $ins = $this->pdo->prepare("INSERT IGNORE INTO chat_participants (conversation_id, user_id, role) VALUES (?, ?, 'member')");
        $ins->execute([$cid, $uid]);
        $ins->execute([$cid, $other]);
        $this->json(['ok' => true, 'conversation_id' => $cid]);
    }

    /** GET /api/chat/{id}/messages?after_rev=&limit=&before_id= */
    public function messages($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a participant'], 403); }

        $afterRev = isset($_GET['after_rev']) ? (int) $_GET['after_rev'] : 0;
        $beforeId = isset($_GET['before_id']) ? (int) $_GET['before_id'] : 0;
        $limit    = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 50;

        if ($afterRev > 0) {
            // poll for changes since cursor (new + edited + deleted + reacted)
            $stmt = $this->pdo->prepare(
                "SELECT m.*, u.name AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_id
                  WHERE m.conversation_id = ? AND m.rev > ? ORDER BY m.rev ASC LIMIT 200"
            );
            $stmt->execute([$cid, $afterRev]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($beforeId > 0) {
            // history scroll-back
            $stmt = $this->pdo->prepare(
                "SELECT m.*, u.name AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_id
                  WHERE m.conversation_id = ? AND m.id < ? ORDER BY m.id DESC LIMIT ?"
            );
            $stmt->bindValue(1, $cid, PDO::PARAM_INT);
            $stmt->bindValue(2, $beforeId, PDO::PARAM_INT);
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // initial load: latest N
            $stmt = $this->pdo->prepare(
                "SELECT m.*, u.name AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_id
                  WHERE m.conversation_id = ? ORDER BY m.id DESC LIMIT ?"
            );
            $stmt->bindValue(1, $cid, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
        }

        $cursor = (int) ($this->pdo->query("SELECT rev_counter FROM chat_conversations WHERE id = " . $cid)->fetchColumn() ?: 0);

        // active typing (≤6s), excluding me
        $typeStmt = $this->pdo->prepare(
            "SELECT t.user_id, t.state, u.name FROM chat_typing t JOIN users u ON u.id = t.user_id
              WHERE t.conversation_id = ? AND t.user_id <> ? AND t.updated_at >= (NOW() - INTERVAL 6 SECOND)"
        );
        $typeStmt->execute([$cid, $uid]);
        $typing = array_map(fn($t) => ['user_id' => (int) $t['user_id'], 'name' => $t['name'], 'state' => $t['state']], $typeStmt->fetchAll(PDO::FETCH_ASSOC));

        $this->json(['ok' => true, 'messages' => $this->hydrateMessages($rows), 'cursor' => $cursor, 'typing' => $typing]);
    }

    /** POST /api/chat/{id}/messages {body, reply_to_id?, attachment_ids[]} */
    public function send($id)
    {
        $me   = $this->requireAuth();
        $uid  = (int) $me['id'];
        $cid  = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a participant'], 403); }

        $data = $this->readBody();
        $body = isset($data['body']) ? trim((string) $data['body']) : '';
        $body = mb_substr($body, 0, 8000);
        $attIds = array_values(array_unique(array_map('intval', $data['attachment_ids'] ?? [])));
        $replyTo = isset($data['reply_to_id']) ? (int) $data['reply_to_id'] : null;
        if ($body === '' && !$attIds) { $this->json(['ok' => false, 'error' => 'empty message'], 422); }

        // reply target must belong to THIS conversation (else drop the link — prevents
        // a cross-conversation reply that a future reply-preview could leak).
        if ($replyTo) {
            $chk = $this->pdo->prepare("SELECT 1 FROM chat_messages WHERE id = ? AND conversation_id = ? LIMIT 1");
            $chk->execute([$replyTo, $cid]);
            if (!$chk->fetchColumn()) { $replyTo = null; }
        }

        // Assign rev up-front and INSERT with it set, so no poller ever sees the row
        // with rev=0 in the window between insert and a separate rev-update.
        $rev = $this->bumpRev($cid);
        $this->pdo->prepare(
            "INSERT INTO chat_messages (conversation_id, sender_id, body, reply_to_id, rev, created_at) VALUES (?, ?, ?, ?, ?, NOW())"
        )->execute([$cid, $uid, $body !== '' ? $body : null, $replyTo ?: null, $rev]);
        $mid = (int) $this->pdo->lastInsertId();

        // link staged attachments owned by me
        if ($attIds) {
            $ph = implode(',', $attIds);
            $this->pdo->prepare(
                "UPDATE chat_attachments SET chat_message_id = ? WHERE id IN ($ph) AND user_id = ? AND chat_message_id IS NULL"
            )->execute([$mid, $uid]);
        }

        $this->pdo->prepare("UPDATE chat_conversations SET last_message_id = ? WHERE id = ?")->execute([$mid, $cid]);

        $this->notifyParticipants($cid, $uid, $me, $body, $attIds ? true : false);

        $stmt = $this->pdo->prepare("SELECT m.*, u.name AS sender_name FROM chat_messages m JOIN users u ON u.id=m.sender_id WHERE m.id = ?");
        $stmt->execute([$mid]);
        $msg = $this->hydrateMessages([$stmt->fetch(PDO::FETCH_ASSOC)]);
        $this->json(['ok' => true, 'message' => $msg[0] ?? null, 'cursor' => $rev]);
    }

    /** PATCH /api/chat/messages/{id} {body} — DOCTOR edits his OWN message only. */
    public function editMessage($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $mid = (int) $id;
        $m = $this->ownEditableMessage($mid, $me);
        $data = $this->readBody();
        $body = mb_substr(trim((string) ($data['body'] ?? '')), 0, 8000);
        if ($body === '') { $this->json(['ok' => false, 'error' => 'empty'], 422); }
        $rev = $this->bumpRev((int) $m['conversation_id']);
        $this->pdo->prepare("UPDATE chat_messages SET body = ?, edited_at = NOW(), rev = ? WHERE id = ?")->execute([$body, $rev, $mid]);
        $this->json(['ok' => true, 'cursor' => $rev]);
    }

    /** DELETE /api/chat/messages/{id} — DOCTOR soft-deletes his OWN message (vanishes for all). */
    public function deleteMessage($id)
    {
        $me  = $this->requireAuth();
        $mid = (int) $id;
        $m = $this->ownEditableMessage($mid, $me);
        $rev = $this->bumpRev((int) $m['conversation_id']);
        $this->pdo->prepare("UPDATE chat_messages SET deleted_at = NOW(), body = NULL, rev = ? WHERE id = ?")->execute([$rev, $mid]);
        $this->json(['ok' => true, 'cursor' => $rev]);
    }

    private function ownEditableMessage(int $mid, array $me): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chat_messages WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$mid]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$m) { $this->json(['ok' => false, 'error' => 'not found'], 404); }
        if (($me['role'] ?? '') !== 'doctor' || (int) $m['sender_id'] !== (int) $me['id']) {
            $this->json(['ok' => false, 'error' => 'only the author (doctor) may edit/delete'], 403);
        }
        return $m;
    }

    /** POST /api/chat/messages/{id}/reactions {emoji} — toggle. */
    public function react($id)
    {
        $me   = $this->requireAuth();
        $uid  = (int) $me['id'];
        $mid  = (int) $id;
        $emoji = mb_substr(trim((string) ($this->readBody()['emoji'] ?? '')), 0, 16);
        if ($emoji === '') { $this->json(['ok' => false, 'error' => 'no emoji'], 422); }
        $m = $this->pdo->prepare("SELECT conversation_id FROM chat_messages WHERE id = ? AND deleted_at IS NULL");
        $m->execute([$mid]);
        $conv = (int) ($m->fetchColumn() ?: 0);
        if (!$conv || !$this->isParticipant($conv, $uid)) { $this->json(['ok' => false, 'error' => 'forbidden'], 403); }

        $exists = $this->pdo->prepare("SELECT id FROM chat_reactions WHERE message_id=? AND user_id=? AND emoji=?");
        $exists->execute([$mid, $uid, $emoji]);
        if ($rid = $exists->fetchColumn()) {
            $this->pdo->prepare("DELETE FROM chat_reactions WHERE id = ?")->execute([$rid]);
        } else {
            $this->pdo->prepare("INSERT INTO chat_reactions (message_id, user_id, emoji) VALUES (?,?,?)")->execute([$mid, $uid, $emoji]);
        }
        $rev = $this->bumpRev($conv);
        $this->pdo->prepare("UPDATE chat_messages SET rev = ? WHERE id = ?")->execute([$rev, $mid]);
        $this->json(['ok' => true, 'cursor' => $rev]);
    }

    /** PUT /api/chat/{id}/read {up_to_message_id} */
    public function markRead($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a participant'], 403); }
        $upTo = (int) ($this->readBody()['up_to_message_id'] ?? 0);
        $this->pdo->prepare(
            "UPDATE chat_participants SET last_read_message_id = GREATEST(COALESCE(last_read_message_id,0), ?)
              WHERE conversation_id = ? AND user_id = ?"
        )->execute([$upTo, $cid, $uid]);
        $this->json(['ok' => true]);
    }

    /** POST /api/chat/{id}/typing {state} */
    public function typing($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'forbidden'], 403); }
        $state = (string) ($this->readBody()['state'] ?? 'typing');
        if (!in_array($state, ['typing', 'voice', 'image', 'file'], true)) { $state = 'typing'; }
        $this->pdo->prepare(
            "INSERT INTO chat_typing (conversation_id, user_id, state, updated_at) VALUES (?,?,?,NOW())
             ON DUPLICATE KEY UPDATE state = VALUES(state), updated_at = NOW()"
        )->execute([$cid, $uid, $state]);
        $this->json(['ok' => true]);
    }

    private function notifyParticipants(int $cid, int $senderId, array $sender, string $body, bool $hasAttachment): void
    {
        try {
            $others = $this->pdo->prepare(
                "SELECT user_id FROM chat_participants WHERE conversation_id = ? AND user_id <> ? AND left_at IS NULL AND muted = 0"
            );
            $others->execute([$cid, $senderId]);
            $name = trim($sender['name'] ?? $sender['username'] ?? 'User');
            $snippet = $body !== '' ? mb_substr($body, 0, 120) : ($hasAttachment ? '📎' : '');
            foreach ($others->fetchAll(PDO::FETCH_COLUMN) as $rid) {
                \App\Controllers\NotificationController::create(
                    (int) $rid, 'chat_message', $name, $snippet, 'chat', $cid, null
                );
            }
        } catch (\Throwable $e) { /* non-fatal */ }
    }

    // ---------- attachments (clone of CommentsController) -------------------

    /** POST /api/chat/attachments — staged upload (chat_message_id NULL). */
    public function uploadAttachment()
    {
        $user = $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { $this->json(['ok' => false, 'error' => 'method not allowed'], 405); }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) { $this->json(['ok' => false, 'error' => 'no file uploaded'], 400); }
        $file = $_FILES['file'];
        if ($file['size'] > 25 * 1024 * 1024) { $this->json(['ok' => false, 'error' => 'file too large (max 25MB)'], 400); }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $imageMimes = ['image/jpeg','image/png','image/gif','image/webp','image/heic','image/heif'];
        $audioMimes = ['audio/webm','video/webm','audio/ogg','audio/mpeg','audio/mp3','audio/mp4','video/mp4','audio/x-m4a','audio/aac','audio/wav','audio/x-wav'];
        $documentMimes = [
            'application/pdf','application/msword','application/vnd.ms-excel','application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-office','application/CDFV2','text/plain',
        ];
        $ooxmlExts = ['docx','xlsx','pptx'];
        $extLower  = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (in_array($mime, $imageMimes, true))        { $kind = 'image'; }
        elseif (in_array($mime, $audioMimes, true))    { $kind = 'audio'; }
        elseif (in_array($mime, $documentMimes, true)) { $kind = 'file'; }
        elseif (in_array($mime, ['application/zip','application/octet-stream'], true) && in_array($extLower, $ooxmlExts, true)) { $kind = 'file'; }
        else { $this->json(['ok' => false, 'error' => 'unsupported file type: ' . $mime], 400); }

        $ext = $this->extForMime($mime, (string) ($file['name'] ?? ''));
        $dir = __DIR__ . '/../../storage/uploads/chat/';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $fname = $kind . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!@move_uploaded_file($file['tmp_name'], $dir . $fname)) { $this->json(['ok' => false, 'error' => 'failed to store file'], 500); }

        $duration = isset($_POST['duration_ms']) ? (int) $_POST['duration_ms'] : null;
        $orig = mb_substr((string) ($file['name'] ?? ''), 0, 255);
        $this->pdo->prepare(
            "INSERT INTO chat_attachments (chat_message_id, user_id, kind, file_path, original_name, mime_type, file_size, duration_ms, created_at)
             VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"
        )->execute([(int) $user['id'], $kind, 'storage/uploads/chat/' . $fname, $orig, $mime, (int) $file['size'], $duration]);
        $aid = (int) $this->pdo->lastInsertId();

        $this->json(['ok' => true, 'attachment' => [
            'id' => $aid, 'kind' => $kind, 'mime' => $mime, 'name' => $orig,
            'size' => (int) $file['size'], 'duration_ms' => $duration, 'url' => '/api/chat/attachments/' . $aid,
        ]]);
    }

    private function extForMime(string $mime, string $origName): string
    {
        $map = [
            'image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp',
            'audio/webm'=>'webm','video/webm'=>'webm','audio/ogg'=>'ogg','audio/mpeg'=>'mp3','audio/mp3'=>'mp3',
            'audio/mp4'=>'m4a','audio/x-m4a'=>'m4a','audio/aac'=>'aac','audio/wav'=>'wav','audio/x-wav'=>'wav',
            'application/pdf'=>'pdf','application/msword'=>'doc','application/vnd.ms-excel'=>'xls','application/vnd.ms-powerpoint'=>'ppt',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'pptx','text/plain'=>'txt',
        ];
        if (isset($map[$mime])) { return $map[$mime]; }
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        return preg_match('/^[a-z0-9]{1,5}$/', $ext) ? $ext : 'bin';
    }

    /** GET /api/chat/attachments/{id} — stream (only a conversation participant, or the staged uploader). */
    public function viewAttachment($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $aid = (int) $id;
        $stmt = $this->pdo->prepare(
            "SELECT a.file_path, a.mime_type, a.original_name, a.user_id, m.conversation_id
               FROM chat_attachments a LEFT JOIN chat_messages m ON m.id = a.chat_message_id WHERE a.id = ?"
        );
        $stmt->execute([$aid]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$a) { $this->json(['ok' => false, 'error' => 'not found'], 404); }
        // authz: staged (no message yet) → only uploader; sent → must be a participant
        if ($a['conversation_id'] === null) {
            if ((int) $a['user_id'] !== $uid) { $this->json(['ok' => false, 'error' => 'forbidden'], 403); }
        } elseif (!$this->isParticipant((int) $a['conversation_id'], $uid)) {
            $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        $path = __DIR__ . '/../../' . ltrim($a['file_path'], '/');
        if (!is_file($path)) { $this->json(['ok' => false, 'error' => 'file missing'], 404); }
        if (ob_get_level()) { @ob_end_clean(); }
        $mime = $a['mime_type'] ?: 'application/octet-stream';
        $size = filesize($path);
        header('Content-Type: ' . $mime);
        header('Cache-Control: private, max-age=86400');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline; filename="' . rawurlencode($a['original_name'] ?: ('attachment-' . $aid)) . '"');
        $start = 0; $end = $size - 1;
        $range = $_SERVER['HTTP_RANGE'] ?? '';
        if ($range !== '' && preg_match('/bytes=(\d*)-(\d*)/', $range, $mm)) {
            if ($mm[1] !== '') { $start = (int) $mm[1]; }
            if ($mm[2] !== '') { $end = (int) $mm[2]; }
            if ($start > $end || $start >= $size) { header('HTTP/1.1 416 Range Not Satisfiable'); header('Content-Range: bytes */' . $size); exit; }
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
            echo $chunk; $remaining -= strlen($chunk); @flush();
        }
        fclose($fp);
        exit;
    }

    // ---------- contacts (doctor's settings roster) ------------------------

    /** GET /api/chat/contacts — my curated contacts. */
    public function contacts()
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $rows = $this->pdo->prepare(
            "SELECT c.contact_user_id AS id, u.name, u.username, u.role, u.profile_image, c.hidden
               FROM chat_contacts c JOIN users u ON u.id = c.contact_user_id WHERE c.owner_user_id = ?"
        );
        $rows->execute([$uid]);
        $this->json(['ok' => true, 'contacts' => array_map(fn($u) => [
            'id' => (int) $u['id'], 'name' => $u['name'] ?: $u['username'], 'role' => $u['role'],
            'avatar' => $u['profile_image'] ?: null, 'hidden' => (int) $u['hidden'],
        ], $rows->fetchAll(PDO::FETCH_ASSOC))]);
    }

    /** POST /api/chat/contacts {contact_user_id} */
    public function addContact()
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $contact = (int) ($this->readBody()['contact_user_id'] ?? 0);
        if (!in_array($contact, $this->rosterUserIds($me), true)) { $this->json(['ok' => false, 'error' => 'not allowed'], 403); }
        $this->pdo->prepare("INSERT IGNORE INTO chat_contacts (owner_user_id, contact_user_id) VALUES (?, ?)")->execute([$uid, $contact]);
        $this->json(['ok' => true]);
    }

    /** DELETE /api/chat/contacts/{id} */
    public function removeContact($id)
    {
        $me  = $this->requireAuth();
        $this->pdo->prepare("DELETE FROM chat_contacts WHERE owner_user_id = ? AND contact_user_id = ?")->execute([(int) $me['id'], (int) $id]);
        $this->json(['ok' => true]);
    }
}
