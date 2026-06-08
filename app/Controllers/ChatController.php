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

    /** True iff the user is an ACTIVE admin of a GROUP conversation. */
    private function isGroupAdmin(int $conversationId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM chat_participants p JOIN chat_conversations c ON c.id = p.conversation_id
              WHERE p.conversation_id = ? AND p.user_id = ? AND p.left_at IS NULL
                AND p.role = 'admin' AND c.type = 'group' LIMIT 1"
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

        // Role-based reach (user decision 2026-06-08), NOT clinic/appointment-scoped:
        //   doctor    → ANY doctor + ANY secretary (all clinics)
        //   secretary → ALL doctors only (no other secretaries)
        //   admin/other → not a chat participant
        if ($role === 'doctor') {
            $ids = $this->pdo->query(
                "SELECT id FROM users WHERE role IN ('doctor','secretary') AND is_active = 1 AND id <> " . $uid
            )->fetchAll(PDO::FETCH_COLUMN);
        } elseif ($role === 'secretary') {
            $ids = $this->pdo->query(
                "SELECT id FROM users WHERE role = 'doctor' AND is_active = 1 AND id <> " . $uid
            )->fetchAll(PDO::FETCH_COLUMN);
        } else {
            return []; // admins / others: no chat
        }
        return array_values(array_unique(array_map('intval', $ids)));
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

        // reply previews — sender + snippet of each referenced (quoted) message,
        // so a reply bubble can show the quote even when the target isn't loaded.
        $replyTargets = [];
        foreach ($rows as $r) { if ($r['reply_to_id'] !== null) { $replyTargets[(int) $r['reply_to_id']] = true; } }
        $replies = [];
        if ($replyTargets) {
            $rp = implode(',', array_map('intval', array_keys($replyTargets)));
            foreach ($this->pdo->query(
                "SELECT m.id, m.body, m.deleted_at, u.name AS sender_name,
                        (SELECT COUNT(*) FROM chat_attachments a WHERE a.chat_message_id = m.id) AS att
                   FROM chat_messages m JOIN users u ON u.id = m.sender_id WHERE m.id IN ($rp)"
            )->fetchAll(PDO::FETCH_ASSOC) as $q) {
                $qid = (int) $q['id'];
                $qDeleted = $q['deleted_at'] !== null;
                $replies[$qid] = [
                    'id' => $qid,
                    // silent-delete: a deleted quote shows "message removed" with no author
                    'sender_name' => $qDeleted ? '' : ($q['sender_name'] ?? ''),
                    'snippet' => $qDeleted ? null
                        : ($q['body'] !== null ? mb_substr($q['body'], 0, 120) : ((int) $q['att'] > 0 ? '📎' : '')),
                    'deleted' => $qDeleted,
                ];
            }
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
                'reply_preview' => (!$deleted && $r['reply_to_id'] !== null && isset($replies[(int) $r['reply_to_id']]))
                    ? $replies[(int) $r['reply_to_id']] : null,
                'rev'         => (int) $r['rev'],
                'edited'      => $r['edited_at'] !== null && !$deleted,
                'deleted'     => $deleted,
                'pinned'      => !$deleted && ($r['pinned_at'] ?? null) !== null,
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
        // rev spans ALL my conversations (so the list stays live even for muted
        // ones); unread_total EXCLUDES muted conversations (no badge / no desktop
        // notif for muted), via the CASE guard.
        $r = $this->pdo->query(
            "SELECT COALESCE(MAX(c.rev_counter),0) AS rev,
                    COALESCE(SUM(CASE WHEN p.muted = 0 THEN (
                       SELECT COUNT(*) FROM chat_messages m
                        WHERE m.conversation_id = c.id
                          AND m.sender_id <> $uid AND m.deleted_at IS NULL
                          AND m.id > COALESCE(p.last_read_message_id, 0)
                    ) ELSE 0 END),0) AS unread
               FROM chat_participants p
               JOIN chat_conversations c ON c.id = p.conversation_id
              WHERE p.user_id = $uid AND p.left_at IS NULL"
        )->fetch(PDO::FETCH_ASSOC) ?: ['rev' => 0, 'unread' => 0];
        // newest reaction OTHERS placed on MY messages — the client compares it to its
        // last-seen value to glow the chat button (reactions don't bump unread_total).
        $reactCursor = (int) ($this->pdo->query(
            "SELECT COALESCE(MAX(rx.id),0) FROM chat_reactions rx
               JOIN chat_messages m ON m.id = rx.message_id
              WHERE m.sender_id = $uid AND rx.user_id <> $uid AND m.deleted_at IS NULL"
        )->fetchColumn() ?: 0);
        $this->json(['ok' => true, 'me' => $uid, 'conversations_rev' => (int) $r['rev'], 'unread_total' => (int) $r['unread'], 'react_cursor' => $reactCursor]);
    }

    /** GET /api/chat/conversations — my conversations with preview + unread. */
    public function conversations()
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id']; // safe int → inlined (native PDO prepares forbid reusing a named param)
        $rows = $this->pdo->query(
            "SELECT c.id, c.type, c.title, c.last_activity_at, c.rev_counter, c.created_by,
                    p.muted, p.role AS my_role,
                    (SELECT COUNT(*) FROM chat_messages m
                       WHERE m.conversation_id = c.id AND m.sender_id <> $uid
                         AND m.deleted_at IS NULL AND m.id > COALESCE(p.last_read_message_id,0)
                         AND p.muted = 0) AS unread,
                    lm.body AS last_body, lm.sender_id AS last_sender, lm.created_at AS last_at, lm.deleted_at AS last_deleted,
                    (SELECT kind FROM chat_attachments WHERE chat_message_id = lm.id ORDER BY id LIMIT 1) AS last_kind
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
                "SELECT u.id, u.name, u.username, u.role, u.profile_image, pp.role AS group_role
                   FROM chat_participants pp JOIN users u ON u.id = pp.user_id
                  WHERE pp.conversation_id = ? AND pp.left_at IS NULL"
            );
            $parts->execute([$cid]);
            $members = array_map(fn($u) => [
                'id' => (int) $u['id'], 'name' => $u['name'] ?: $u['username'],
                'role' => $u['role'], 'avatar' => $u['profile_image'] ?: null,
                'group_role' => $u['group_role'],
            ], $parts->fetchAll(PDO::FETCH_ASSOC));
            $other = null;
            if ($c['type'] === 'dm') {
                foreach ($members as $m) { if ($m['id'] !== $uid) { $other = $m; break; } }
            }
            // The list preview reflects the latest ACTIVITY — a message (text /
            // image / audio / file) OR a reaction that is newer than the last
            // message (reactions don't change last_message_id). The client
            // localizes from {type, kind, emoji, mine, on_mine}.
            $lastKind = $c['last_deleted'] !== null ? null : ($c['last_kind'] ?? null);
            $last = [
                'type'      => $lastKind ?: 'text',
                'body'      => $c['last_deleted'] !== null ? null : $c['last_body'],
                'kind'      => $lastKind,
                'emoji'     => null,
                'mine'      => ($c['last_sender'] !== null && (int) $c['last_sender'] === $uid),
                'on_mine'   => false,
                'sender_id' => $c['last_sender'] !== null ? (int) $c['last_sender'] : null,
                'at'        => $c['last_at'],
            ];
            $rxStmt = $this->pdo->prepare(
                "SELECT rx.emoji, rx.user_id AS reactor, rx.created_at AS rat, m.sender_id AS author
                   FROM chat_reactions rx JOIN chat_messages m ON m.id = rx.message_id
                  WHERE m.conversation_id = ? AND m.deleted_at IS NULL ORDER BY rx.id DESC LIMIT 1"
            );
            $rxStmt->execute([$cid]);
            $rx = $rxStmt->fetch(PDO::FETCH_ASSOC);
            if ($rx && (string) $rx['rat'] >= (string) ($c['last_at'] ?? '')) {
                $last = [
                    'type' => 'reaction', 'emoji' => $rx['emoji'], 'body' => null, 'kind' => null,
                    'mine' => ((int) $rx['reactor'] === $uid), 'on_mine' => ((int) $rx['author'] === $uid),
                    'sender_id' => (int) $rx['reactor'], 'at' => $rx['rat'],
                ];
            }
            $convos[] = [
                'id' => $cid, 'type' => $c['type'],
                'title' => $c['type'] === 'group' ? $c['title'] : ($other['name'] ?? 'Chat'),
                'avatar' => $c['type'] === 'dm' ? ($other['avatar'] ?? null) : null,
                'other' => $other,
                'members' => $members,
                'muted' => ((int) $c['muted']) === 1,
                'my_role' => $c['my_role'],
                'is_admin' => $c['type'] === 'group' && $c['my_role'] === 'admin',
                'created_by' => (int) $c['created_by'],
                'unread' => (int) $c['unread'],
                'rev' => (int) $c['rev_counter'],
                'last' => $last,
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

        // read receipts: the lowest "last read" among the OTHER participants, so a
        // sender's own message shows ✓✓ only once everyone else has seen it. Computed
        // fresh each poll (markRead doesn't bump rev), so ✓✓ updates within one tick.
        $readUpTo = (int) ($this->pdo->query(
            "SELECT COALESCE(MIN(COALESCE(last_read_message_id,0)),0) FROM chat_participants
              WHERE conversation_id = $cid AND user_id <> $uid AND left_at IS NULL"
        )->fetchColumn() ?: 0);
        // MY last-read position (before this open marks it read) → drives the
        // "unread from here" divider client-side.
        $myLastRead = (int) ($this->pdo->query(
            "SELECT COALESCE(last_read_message_id,0) FROM chat_participants WHERE conversation_id = $cid AND user_id = $uid"
        )->fetchColumn() ?: 0);

        // active typing (≤6s), excluding me
        $typeStmt = $this->pdo->prepare(
            "SELECT t.user_id, t.state, u.name FROM chat_typing t JOIN users u ON u.id = t.user_id
              WHERE t.conversation_id = ? AND t.user_id <> ? AND t.updated_at >= (NOW() - INTERVAL 6 SECOND)"
        );
        $typeStmt->execute([$cid, $uid]);
        $typing = array_map(fn($t) => ['user_id' => (int) $t['user_id'], 'name' => $t['name'], 'state' => $t['state']], $typeStmt->fetchAll(PDO::FETCH_ASSOC));

        $this->json(['ok' => true, 'messages' => $this->hydrateMessages($rows), 'cursor' => $cursor, 'typing' => $typing, 'read_up_to' => $readUpTo, 'my_last_read' => $myLastRead]);
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
        $attachKind = '';
        if ($attIds) {
            $ph = implode(',', $attIds);
            $this->pdo->prepare(
                "UPDATE chat_attachments SET chat_message_id = ? WHERE id IN ($ph) AND user_id = ? AND chat_message_id IS NULL"
            )->execute([$mid, $uid]);
            $attachKind = (string) ($this->pdo->query("SELECT kind FROM chat_attachments WHERE chat_message_id = " . $mid . " ORDER BY id LIMIT 1")->fetchColumn() ?: 'file');
        }

        $this->pdo->prepare("UPDATE chat_conversations SET last_message_id = ? WHERE id = ?")->execute([$mid, $cid]);
        // sending IS the end of typing/recording/uploading — drop my typing row now so
        // the recipient's next poll never shows a stale "typing…/sending…" AFTER the message.
        $this->pdo->prepare("DELETE FROM chat_typing WHERE conversation_id = ? AND user_id = ?")->execute([$cid, $uid]);

        $this->notifyParticipants($cid, $uid, $me, $body, $attachKind);

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
        if (!$this->isParticipant((int) $m['conversation_id'], $uid)) { $this->json(['ok' => false, 'error' => 'not a participant'], 403); }
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
        $uid = (int) $me['id'];
        $mid = (int) $id;
        $m = $this->ownEditableMessage($mid, $me);
        if (!$this->isParticipant((int) $m['conversation_id'], $uid)) { $this->json(['ok' => false, 'error' => 'not a participant'], 403); }
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
        // Store ONLY a clean native emoji glyph — never markup/tokens (the animated <img>
        // lives client-side). A real emoji has no '<>' and no ASCII alphanumerics, so this
        // keeps notification text + grouping stable on a FIXED native char no matter what.
        $emoji = str_replace(['<', '>'], '', $emoji);
        if ($emoji === '' || preg_match('/[A-Za-z0-9]/', $emoji)) {
            $this->json(['ok' => false, 'error' => 'invalid emoji'], 422);
        }
        $m = $this->pdo->prepare("SELECT m.conversation_id, m.sender_id, m.body, u.role AS author_role
                                    FROM chat_messages m JOIN users u ON u.id = m.sender_id
                                   WHERE m.id = ? AND m.deleted_at IS NULL");
        $m->execute([$mid]);
        $row  = $m->fetch(PDO::FETCH_ASSOC);
        $conv = (int) ($row['conversation_id'] ?? 0);
        if (!$conv || !$this->isParticipant($conv, $uid)) { $this->json(['ok' => false, 'error' => 'forbidden'], 403); }

        $exists = $this->pdo->prepare("SELECT id FROM chat_reactions WHERE message_id=? AND user_id=? AND emoji=?");
        $exists->execute([$mid, $uid, $emoji]);
        if ($rid = $exists->fetchColumn()) {
            $this->pdo->prepare("DELETE FROM chat_reactions WHERE id = ?")->execute([$rid]);
        } else {
            $this->pdo->prepare("INSERT INTO chat_reactions (message_id, user_id, emoji) VALUES (?,?,?)")->execute([$mid, $uid, $emoji]);
            // notify the message AUTHOR that their message got a reaction (only on add,
            // never on un-react, never to yourself) — drives the bell + the FAB glow.
            $author = (int) ($row['sender_id'] ?? 0);
            if ($author && $author !== $uid) {
                try {
                    $ar   = (($row['author_role'] ?? '') === 'secretary'); // notify in the AUTHOR's language
                    $snip = trim($this->stripChatTokens((string) ($row['body'] ?? '')));
                    $snip = $snip !== '' ? mb_substr($snip, 0, 60) : '';
                    $verb = $ar ? 'تفاعل مع رسالتك' : 'reacted to your message';
                    $msg  = $emoji . ' ' . $verb . ($snip !== '' ? ' «' . $snip . '»' : '');
                    // SAME group key as messages → ALL activity from this person in this
                    // conversation collapses to one bell entry (the latest), message or reaction.
                    $this->pushChatNotification(
                        $author, 'chat_reaction', trim($me['name'] ?? $me['username'] ?? 'User'),
                        $msg, $conv, 'chat:' . $conv . ':' . $uid
                    );
                } catch (\Throwable $e) { /* non-fatal */ }
            }
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

    // ---------- Phase 3: group management + mute ----------------------------

    /** POST /api/chat/{id}/group {title} — rename a group (admin only). */
    public function updateGroup($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isGroupAdmin($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a group admin'], 403); }
        $title = mb_substr(trim((string) ($this->readBody()['title'] ?? '')), 0, 120);
        if ($title === '') { $this->json(['ok' => false, 'error' => 'empty title'], 422); }
        $this->pdo->prepare("UPDATE chat_conversations SET title = ? WHERE id = ?")->execute([$title, $cid]);
        $this->bumpRev($cid);
        $this->json(['ok' => true, 'title' => $title]);
    }

    /** POST /api/chat/{id}/add-member {user_id} — add a member (admin only, roster-checked). */
    public function addMember($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isGroupAdmin($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a group admin'], 403); }
        $newUid = (int) ($this->readBody()['user_id'] ?? 0);
        if ($newUid <= 0 || !in_array($newUid, $this->rosterUserIds($me), true)) {
            $this->json(['ok' => false, 'error' => 'user not allowed'], 403);
        }
        // re-activate a prior leaver, or insert fresh (idempotent on the unique key)
        $this->pdo->prepare(
            "INSERT INTO chat_participants (conversation_id, user_id, role, joined_at, left_at)
             VALUES (?, ?, 'member', NOW(), NULL)
             ON DUPLICATE KEY UPDATE left_at = NULL"
        )->execute([$cid, $newUid]);
        $this->bumpRev($cid);
        $this->json(['ok' => true]);
    }

    /** POST /api/chat/{id}/remove-member {user_id} — remove a member (admin only). */
    public function removeMember($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isGroupAdmin($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a group admin'], 403); }
        $target = (int) ($this->readBody()['user_id'] ?? 0);
        if ($target <= 0 || $target === $uid) { $this->json(['ok' => false, 'error' => 'invalid target (use leave for yourself)'], 422); }
        $this->pdo->prepare(
            "UPDATE chat_participants SET left_at = NOW() WHERE conversation_id = ? AND user_id = ? AND left_at IS NULL"
        )->execute([$cid, $target]);
        $this->ensureGroupHasAdmin($cid);
        $this->bumpRev($cid);
        $this->json(['ok' => true]);
    }

    /** POST /api/chat/{id}/leave — leave a group (self). */
    public function leaveGroup($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a participant'], 403); }
        $type = (string) ($this->pdo->query("SELECT type FROM chat_conversations WHERE id = " . $cid)->fetchColumn() ?: '');
        if ($type !== 'group') { $this->json(['ok' => false, 'error' => 'cannot leave a direct chat'], 422); }
        $this->pdo->prepare("UPDATE chat_participants SET left_at = NOW() WHERE conversation_id = ? AND user_id = ?")->execute([$cid, $uid]);
        $this->ensureGroupHasAdmin($cid);
        $this->bumpRev($cid);
        $this->json(['ok' => true]);
    }

    /** If a group lost its last admin, promote the earliest-joined active member. */
    private function ensureGroupHasAdmin(int $cid): void
    {
        $hasAdmin = (bool) $this->pdo->query(
            "SELECT 1 FROM chat_participants WHERE conversation_id = " . $cid . " AND role = 'admin' AND left_at IS NULL LIMIT 1"
        )->fetchColumn();
        if ($hasAdmin) { return; }
        $next = (int) ($this->pdo->query(
            "SELECT user_id FROM chat_participants WHERE conversation_id = " . $cid . " AND left_at IS NULL ORDER BY joined_at ASC, id ASC LIMIT 1"
        )->fetchColumn() ?: 0);
        if ($next) {
            $this->pdo->prepare("UPDATE chat_participants SET role = 'admin' WHERE conversation_id = ? AND user_id = ?")->execute([$cid, $next]);
        }
    }

    /** PUT /api/chat/{id}/mute {muted?} — toggle (or set) mute for me. */
    public function toggleMute($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'not a participant'], 403); }
        $body = $this->readBody();
        if (is_array($body) && array_key_exists('muted', $body)) {
            $val = $body['muted'] ? 1 : 0;
            $this->pdo->prepare("UPDATE chat_participants SET muted = ? WHERE conversation_id = ? AND user_id = ?")->execute([$val, $cid, $uid]);
        } else {
            $this->pdo->prepare("UPDATE chat_participants SET muted = 1 - muted WHERE conversation_id = ? AND user_id = ?")->execute([$cid, $uid]);
            $val = (int) $this->pdo->query("SELECT muted FROM chat_participants WHERE conversation_id = " . $cid . " AND user_id = " . $uid)->fetchColumn();
        }
        $this->json(['ok' => true, 'muted' => ((int) $val) === 1]);
    }

    // ---------- Phase 3b: pin · search · forward ----------------------------

    /** POST /api/chat/messages/{id}/pin — toggle pin (any participant). */
    public function pinMessage($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $mid = (int) $id;
        $stmt = $this->pdo->prepare("SELECT conversation_id, pinned_at FROM chat_messages WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$mid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) { $this->json(['ok' => false, 'error' => 'not found'], 404); }
        $cid = (int) $row['conversation_id'];
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'forbidden'], 403); }
        // Atomic toggle — the pin/unpin decision is evaluated INSIDE the UPDATE (no
        // SELECT-then-UPDATE TOCTOU race). pinned_by is assigned BEFORE pinned_at so
        // both CASEs read the OLD pinned_at (MySQL assigns left-to-right).
        $rev = $this->bumpRev($cid);
        $this->pdo->prepare(
            "UPDATE chat_messages
                SET pinned_by = CASE WHEN pinned_at IS NULL THEN ? ELSE NULL END,
                    pinned_at = CASE WHEN pinned_at IS NULL THEN NOW() ELSE NULL END,
                    rev = ?
              WHERE id = ?"
        )->execute([$uid, $rev, $mid]);
        $pinned = (bool) $this->pdo->query("SELECT pinned_at IS NOT NULL FROM chat_messages WHERE id = " . $mid)->fetchColumn();
        $this->json(['ok' => true, 'pinned' => $pinned, 'cursor' => $rev]);
    }

    /** GET /api/chat/{id}/pins — pinned messages of a conversation. */
    public function pins($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'forbidden'], 403); }
        $stmt = $this->pdo->prepare(
            "SELECT m.*, u.name AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_id
              WHERE m.conversation_id = ? AND m.pinned_at IS NOT NULL AND m.deleted_at IS NULL
              ORDER BY m.pinned_at DESC LIMIT 50"
        );
        $stmt->execute([$cid]);
        $this->json(['ok' => true, 'pins' => $this->hydrateMessages($stmt->fetchAll(PDO::FETCH_ASSOC))]);
    }

    /** GET /api/chat/{id}/search?q= — text search within a conversation. */
    public function searchMessages($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $cid = (int) $id;
        if (!$this->isParticipant($cid, $uid)) { $this->json(['ok' => false, 'error' => 'forbidden'], 403); }
        $q = trim((string) ($_GET['q'] ?? ''));
        if (mb_strlen($q) < 2) { $this->json(['ok' => true, 'results' => []]); }
        // body LIKE ? with a bound param is fine in a prepared stmt (only SHOW…LIKE? fails)
        $stmt = $this->pdo->prepare(
            "SELECT m.*, u.name AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_id
              WHERE m.conversation_id = ? AND m.deleted_at IS NULL AND m.body LIKE ? ORDER BY m.id DESC LIMIT 40"
        );
        $stmt->execute([$cid, '%' . $q . '%']);
        $this->json(['ok' => true, 'results' => $this->hydrateMessages($stmt->fetchAll(PDO::FETCH_ASSOC))]);
    }

    /** POST /api/chat/{id}/forward {message_id} — forward a message into conversation {id}. */
    public function forwardMessage($id)
    {
        $me  = $this->requireAuth();
        $uid = (int) $me['id'];
        $targetCid = (int) $id;
        if (!$this->isParticipant($targetCid, $uid)) { $this->json(['ok' => false, 'error' => 'not a participant of the target'], 403); }
        $srcMid = (int) ($this->readBody()['message_id'] ?? 0);
        $src = $this->pdo->prepare("SELECT * FROM chat_messages WHERE id = ? AND deleted_at IS NULL");
        $src->execute([$srcMid]);
        $sm = $src->fetch(PDO::FETCH_ASSOC);
        if (!$sm) { $this->json(['ok' => false, 'error' => 'source not found'], 404); }
        // you can only forward what you can see
        if (!$this->isParticipant((int) $sm['conversation_id'], $uid)) { $this->json(['ok' => false, 'error' => 'forbidden source'], 403); }

        $rev = $this->bumpRev($targetCid);
        $this->pdo->prepare(
            "INSERT INTO chat_messages (conversation_id, sender_id, body, rev, created_at) VALUES (?, ?, ?, ?, NOW())"
        )->execute([$targetCid, $uid, $sm['body'], $rev]);
        $newMid = (int) $this->pdo->lastInsertId();

        // duplicate attachment rows (point at the SAME file_path, now linked to the copy)
        $atts = $this->pdo->prepare("SELECT user_id, kind, file_path, original_name, mime_type, file_size, duration_ms FROM chat_attachments WHERE chat_message_id = ?");
        $atts->execute([$srcMid]);
        $attRows = $atts->fetchAll(PDO::FETCH_ASSOC);
        if ($attRows) {
            $insAtt = $this->pdo->prepare(
                "INSERT INTO chat_attachments (chat_message_id, user_id, kind, file_path, original_name, mime_type, file_size, duration_ms)
                 VALUES (?,?,?,?,?,?,?,?)"
            );
            foreach ($attRows as $a) {
                $insAtt->execute([$newMid, $uid, $a['kind'], $a['file_path'], $a['original_name'], $a['mime_type'],
                    $a['file_size'] !== null ? (int) $a['file_size'] : null,
                    $a['duration_ms'] !== null ? (int) $a['duration_ms'] : null]);
            }
        }
        $this->pdo->prepare("UPDATE chat_conversations SET last_message_id = ? WHERE id = ?")->execute([$newMid, $targetCid]);
        $this->notifyParticipants($targetCid, $uid, $me, (string) $sm['body'], !empty($attRows) ? ((string) ($attRows[0]['kind'] ?? 'file')) : '');
        $this->json(['ok' => true, 'conversation_id' => $targetCid, 'cursor' => $rev]);
    }

    /**
     * Render the human form of an inline chat token for notifications/previews:
     * `@[Ahmed](p:8)` → `@Ahmed`, `#[Appt 12](appt:12)` → `#Appt 12`. Mirrors the
     * client-side stripTokens() so bell snippets never show the raw `](p:8)` markup.
     */
    private function stripChatTokens(string $body): string
    {
        return (string) preg_replace_callback(
            '/([@#])\[([^\]]{1,80})\]\((?:p|appt|date):[^)]{1,40}\)/u',
            fn($m) => $m[1] . $m[2],
            $body
        );
    }

    /**
     * Bell-notify the other participants. The message is localized PER RECIPIENT
     * role (doctor → English, secretary → Arabic) and clearly labels an
     * attachment-only message ("sent you an image/voice/file").
     */
    private function notifyParticipants(int $cid, int $senderId, array $sender, string $body, string $attachKind = ''): void
    {
        try {
            $others = $this->pdo->prepare(
                "SELECT cp.user_id, u.role FROM chat_participants cp JOIN users u ON u.id = cp.user_id
                  WHERE cp.conversation_id = ? AND cp.user_id <> ? AND cp.left_at IS NULL AND cp.muted = 0"
            );
            $others->execute([$cid, $senderId]);
            $name  = trim($sender['name'] ?? $sender['username'] ?? 'User');
            $clean = trim($this->stripChatTokens($body));
            foreach ($others->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $ar = (($row['role'] ?? '') === 'secretary');
                if ($clean !== '')        { $message = mb_substr($clean, 0, 120); }
                elseif ($attachKind !== '') { $message = $this->attachLabel($ar, $attachKind); }
                else                       { $message = ''; }
                $this->pushChatNotification((int) $row['user_id'], 'chat_message', $name, $message, $cid, 'chat:' . $cid . ':' . $senderId);
            }
        } catch (\Throwable $e) { /* non-fatal */ }
    }

    /**
     * Create a chat notification that COLLAPSES with prior ones from the same
     * sender in the same conversation: the recipient's earlier UNREAD notification
     * of this group is removed first, so the bell only ever shows the LATEST.
     */
    private function pushChatNotification(int $userId, string $type, string $title, string $message, int $cid, string $groupKey): void
    {
        // Remove this recipient's prior notification(s) of the same group (any read
        // state, except a pinned one) so the bell shows exactly ONE entry per
        // sender·conversation — the latest activity, message OR reaction.
        $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND group_key = ? AND pinned_at IS NULL")
             ->execute([$userId, $groupKey]);
        \App\Controllers\NotificationController::create($userId, $type, $title, $message, 'chat', $cid, null, $groupKey);
    }

    /** Localized "sent you an image / voice message / file" label. */
    private function attachLabel(bool $ar, string $kind): string
    {
        if ($kind === 'image') { return $ar ? 'أرسل لك صورة 📷' : 'sent you an image 📷'; }
        if ($kind === 'audio') { return $ar ? 'أرسل لك تسجيلاً صوتياً 🎤' : 'sent you a voice message 🎤'; }
        return $ar ? 'أرسل لك ملفاً 📎' : 'sent you a file 📎';
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

    // ---------- link preview (SSRF-safe unfurl + cache) --------------------

    /** GET /api/chat/link-preview?url=...  — cached OG/title unfurl for chat links. */
    public function linkPreview()
    {
        $me   = $this->requireAuth();
        $role = $me['role'] ?? '';
        if ($role !== 'doctor' && $role !== 'secretary') {   // chat users only
            $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        $url = trim((string) ($_GET['url'] ?? ''));
        if ($url === '' || strlen($url) > 2048) {
            $this->json(['ok' => false, 'error' => 'bad url'], 422);
        }
        $norm = $this->lpNormalizeUrl($url);
        if ($norm === null) {
            $this->json(['ok' => false, 'error' => 'unsupported url'], 422);
        }
        $hash = hash('sha256', $norm);

        // cache: success good for 7d, negative cached 30m (avoid re-hammering bad URLs)
        $st = $this->pdo->prepare(
            "SELECT url, title, description, image, site_name, status, UNIX_TIMESTAMP(fetched_at) AS ts
               FROM chat_link_previews WHERE url_hash = ? LIMIT 1"
        );
        $st->execute([$hash]);
        if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $age = time() - (int) $row['ts'];
            $ttl = $row['status'] === 'ok' ? 604800 : 1800;
            if ($age < $ttl) {
                if ($row['status'] !== 'ok') { $this->json(['ok' => false, 'error' => 'no preview']); }
                $this->json(['ok' => true, 'preview' => $this->lpRow($row)]);
            }
        }

        $data   = $this->lpFetch($norm);             // null on any failure
        $status = $data ? 'ok' : 'error';
        $this->pdo->prepare(
            "INSERT INTO chat_link_previews (url_hash, url, title, description, image, site_name, status, fetched_at)
             VALUES (?,?,?,?,?,?,?,NOW())
             ON DUPLICATE KEY UPDATE url=VALUES(url), title=VALUES(title), description=VALUES(description),
                image=VALUES(image), site_name=VALUES(site_name), status=VALUES(status), fetched_at=NOW()"
        )->execute([
            $hash, $norm, $data['title'] ?? null, $data['description'] ?? null,
            $data['image'] ?? null, $data['site_name'] ?? null, $status,
        ]);
        if (!$data) { $this->json(['ok' => false, 'error' => 'no preview']); }
        $this->json(['ok' => true, 'preview' => $this->lpRow(array_merge($data, ['url' => $norm]))]);
    }

    private function lpRow(array $r): array
    {
        return [
            'url'         => $r['url'] ?? '',
            'title'       => $r['title'] ?? null,
            'description' => $r['description'] ?? null,
            'image'       => $r['image'] ?? null,
            'site_name'   => $r['site_name'] ?? null,
        ];
    }

    /** Validate + canonicalize a user URL → http(s) only, no userinfo, port 80/443. */
    private function lpNormalizeUrl(string $url): ?string
    {
        $p = parse_url($url);
        if (!is_array($p)) return null;
        $scheme = strtolower($p['scheme'] ?? '');
        if ($scheme !== 'http' && $scheme !== 'https') return null;
        if (isset($p['user']) || isset($p['pass'])) return null;
        $host = $p['host'] ?? '';
        if ($host === '') return null;
        $port = isset($p['port']) ? (int) $p['port'] : ($scheme === 'https' ? 443 : 80);
        if ($port !== 80 && $port !== 443) return null;
        $path     = $p['path'] ?? '/';
        $query    = isset($p['query']) ? '?' . $p['query'] : '';
        $defaultP = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80);
        $portPart = $defaultP ? '' : ':' . $port;
        return $scheme . '://' . $host . $portPart . $path . $query;     // fragment dropped
    }

    /** True iff $ip is a public, routable address (blocks SSRF targets). */
    private function lpIpAllowed(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;   // 10/8, 172.16/12, 192.168/16, 127/8, 169.254/16, ::1, fe80::, fc00::, 0.0.0.0, multicast…
        }
        if (in_array($ip, ['169.254.169.254', '0.0.0.0', '::', '::1'], true)) return false; // cloud metadata + edge
        if (stripos($ip, '::ffff:') === 0) return false;                 // IPv4-mapped IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $l = ip2long($ip);                                           // CGNAT 100.64.0.0/10
            if ($l !== false && ($l & 0xFFC00000) === (ip2long('100.64.0.0') & 0xFFC00000)) return false;
        }
        return true;
    }

    /** Resolve $host to IPs; reject if ANY is non-public. Returns one validated IP or null. */
    private function lpResolveSafe(string $host): ?string
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->lpIpAllowed($host) ? $host : null;            // bare-IP host
        }
        $ips = @gethostbynamel($host) ?: [];
        foreach ((@dns_get_record($host, DNS_AAAA) ?: []) as $rec) {
            if (!empty($rec['ipv6'])) $ips[] = $rec['ipv6'];
        }
        if (!$ips) return null;
        $pick = null;
        foreach ($ips as $ip) {
            if (!$this->lpIpAllowed($ip)) return null;                  // one bad IP → reject the host
            if ($pick === null) $pick = $ip;
        }
        return $pick;
    }

    /** SSRF-safe fetch of a URL's <head>; manual redirect loop (≤3 hops, re-validated). */
    private function lpFetch(string $url): ?array
    {
        for ($hop = 0; $hop <= 3; $hop++) {
            $norm = $this->lpNormalizeUrl($url);
            if ($norm === null) return null;
            $p    = parse_url($norm);
            $host = $p['host'];
            $sch  = strtolower($p['scheme']);
            $port = isset($p['port']) ? (int) $p['port'] : ($sch === 'https' ? 443 : 80);
            $ip   = $this->lpResolveSafe($host);
            if ($ip === null) return null;
            $resolveAddr = (strpos($ip, ':') !== false) ? "[$ip]" : $ip; // bracket IPv6

            $body = '';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL             => $norm,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_HEADER          => false,
                CURLOPT_FOLLOWLOCATION  => false,
                CURLOPT_CONNECTTIMEOUT  => 4,
                CURLOPT_TIMEOUT         => 6,
                CURLOPT_SSL_VERIFYPEER  => true,
                CURLOPT_SSL_VERIFYHOST  => 2,
                CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_USERAGENT       => 'RoayaClinicBot/1.0 (+link-preview)',
                CURLOPT_ACCEPT_ENCODING => '',
                CURLOPT_RESOLVE         => ["$host:$port:$resolveAddr"], // pin vetted IP (anti DNS-rebind)
                CURLOPT_WRITEFUNCTION   => function ($c, $chunk) use (&$body) {
                    $body .= $chunk;
                    if (strlen($body) > 524288 || stripos($body, '</head>') !== false) return -1; // abort early
                    return strlen($chunk);
                },
            ]);
            curl_exec($ch);
            $code  = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $ctype = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $loc   = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
            curl_close($ch);

            if ($code >= 300 && $code < 400) {
                if ($loc === '') return null;
                $next = $this->lpAbsUrl($loc, $norm);
                if ($next === null) return null;
                $url = $next;
                continue;                                               // re-validate next hop
            }
            if ($code < 200 || $code >= 300) return null;
            if ($ctype !== '' && stripos($ctype, 'text/html') === false && stripos($ctype, 'xhtml') === false) return null;
            if ($body === '') return null;
            $res = $this->lpParseHead($body, $norm);
            return $res ?: null;
        }
        return null;
    }

    /** Resolve a possibly-relative URL against a base. */
    private function lpAbsUrl(string $href, string $base): ?string
    {
        $href = trim($href);
        if ($href === '') return null;
        if (preg_match('#^https?://#i', $href)) return $href;
        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $href)) return null;   // some other scheme → refuse
        $b = parse_url($base);
        if (!isset($b['scheme'], $b['host'])) return null;
        $root = $b['scheme'] . '://' . $b['host'] . (isset($b['port']) ? ':' . $b['port'] : '');
        if ($href[0] === '/') return $root . $href;
        $path = isset($b['path']) ? preg_replace('#/[^/]*$#', '/', $b['path']) : '/';
        return $root . $path . $href;
    }

    /** Pull OG / twitter / <title> / site-name from a fetched <head> blob. */
    private function lpParseHead(string $html, string $finalUrl): array
    {
        $head = $html;
        if (($pos = stripos($html, '</head>')) !== false) $head = substr($html, 0, $pos);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $head);             // force UTF-8 (Arabic OG)
        libxml_clear_errors();

        $metas = [];
        foreach ($dom->getElementsByTagName('meta') as $m) {
            $key = strtolower($m->getAttribute('property') ?: $m->getAttribute('name'));
            $val = $m->getAttribute('content');
            if ($key !== '' && $val !== '' && !isset($metas[$key])) $metas[$key] = $val;
        }
        $titleTag = '';
        $tl = $dom->getElementsByTagName('title');
        if ($tl->length) $titleTag = trim($tl->item(0)->textContent);

        $pick = function (array $keys) use ($metas) {
            foreach ($keys as $k) { if (!empty($metas[$k])) return $metas[$k]; }
            return null;
        };
        $title = $pick(['og:title', 'twitter:title']) ?: ($titleTag ?: null);
        $desc  = $pick(['og:description', 'twitter:description', 'description']);
        $img   = $pick(['og:image', 'og:image:url', 'og:image:secure_url', 'twitter:image', 'twitter:image:src']);
        $site  = $pick(['og:site_name', 'application-name']);

        if ($img !== null) {                                            // absolutize + scheme-check image
            $abs = $this->lpAbsUrl($img, $finalUrl);
            $img = ($abs !== null && preg_match('#^https?://#i', $abs)) ? $abs : null;
        }
        if ($title === null && $desc === null && $img === null) return [];
        return [
            'title'       => $title !== null ? mb_substr(trim($title), 0, 500) : null,
            'description' => $desc  !== null ? mb_substr(trim($desc), 0, 1000) : null,
            'image'       => $img   !== null ? mb_substr($img, 0, 2040) : null,
            'site_name'   => $site  !== null ? mb_substr(trim($site), 0, 250) : null,
        ];
    }
}
