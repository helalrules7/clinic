<?php
namespace App\Services;

use PDO;

class MentionParserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Parse @mentions from a body of text and insert mention notifications
     * for each matched user (excluding the author).
     *
     * @param string   $body          The text potentially containing @mentions.
     * @param int      $authorId      The id of the user authoring the text (excluded from notifications).
     * @param string   $contextLabel  Human-readable label of where the mention happened (e.g. "Comment on patient X").
     * @param string   $contextLink   URL/link the notification should point to.
     * @param int|null $patientId     Optional patient id to attach to the notification.
     *
     * @return array{mentioned_user_ids: int[], count: int}
     */
    public function parseAndNotify(
        string $body,
        int $authorId,
        string $contextLabel,
        string $contextLink,
        ?int $patientId = null
    ): array {
        $empty = ['mentioned_user_ids' => [], 'count' => 0];

        $trimmed = trim($body);
        if ($trimmed === '') {
            return $empty;
        }

        // 1) Extract @mention tokens.
        $matchCount = preg_match_all('/@([a-zA-Z][\w.-]{1,40})/u', $body, $matches);
        if (!$matchCount || empty($matches[1])) {
            return $empty;
        }

        // 2) Unique, lowercased usernames.
        $usernames = [];
        foreach ($matches[1] as $u) {
            $u = strtolower($u);
            if ($u !== '' && !in_array($u, $usernames, true)) {
                $usernames[] = $u;
            }
        }
        if (empty($usernames)) {
            return $empty;
        }

        // 3) Look up matching users (excluding the author).
        $placeholders = implode(',', array_fill(0, count($usernames), '?'));
        $sql = "SELECT id, name, username
                FROM users
                WHERE LOWER(username) IN ($placeholders)
                  AND id != ?";
        $params = $usernames;
        $params[] = $authorId;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return $empty;
        }

        if (empty($users)) {
            return $empty;
        }

        // Fetch author name for nicer notification titles (best-effort).
        $authorName = null;
        try {
            $aStmt = $this->pdo->prepare("SELECT name, username FROM users WHERE id = ? LIMIT 1");
            $aStmt->execute([$authorId]);
            $authorRow = $aStmt->fetch(PDO::FETCH_ASSOC);
            if ($authorRow) {
                $authorName = !empty($authorRow['name'])
                    ? (string)$authorRow['name']
                    : (string)($authorRow['username'] ?? '');
            }
        } catch (\Throwable $e) {
            // ignore — we'll fall back to a generic title.
        }

        // 4) Build notification fields.
        $title = $authorName !== null && $authorName !== ''
            ? ($authorName . ' mentioned you')
            : 'You were mentioned';

        if ($contextLabel !== '') {
            $title .= ' in ' . $contextLabel;
        }

        // First 120 chars of the body (multibyte-safe).
        $previewSource = trim(preg_replace('/\s+/u', ' ', $body) ?? $body);
        if (function_exists('mb_substr')) {
            $preview = mb_substr($previewSource, 0, 120, 'UTF-8');
            if (mb_strlen($previewSource, 'UTF-8') > 120) {
                $preview .= '…';
            }
        } else {
            $preview = substr($previewSource, 0, 120);
            if (strlen($previewSource) > 120) {
                $preview .= '...';
            }
        }

        $groupKey = sha1('mention|' . ($patientId ?? '') . '|' . date('Y-m-d'));

        $insertSql = "INSERT INTO notifications
                        (user_id, type, title, body, link, icon, patient_id, created_at, group_key)
                      VALUES
                        (:uid, 'mention', :title, :body, :link, 'at', :pid, NOW(), :gk)";
        $insertStmt = $this->pdo->prepare($insertSql);

        $mentionedIds = [];
        foreach ($users as $u) {
            $uid = (int)($u['id'] ?? 0);
            if ($uid <= 0 || $uid === $authorId) {
                continue;
            }
            try {
                $insertStmt->execute([
                    ':uid'   => $uid,
                    ':title' => $title,
                    ':body'  => $preview,
                    ':link'  => $contextLink,
                    ':pid'   => $patientId,
                    ':gk'    => $groupKey,
                ]);
                $mentionedIds[] = $uid;
            } catch (\Throwable $e) {
                // Skip on per-user failure; keep processing the rest.
                continue;
            }
        }

        return [
            'mentioned_user_ids' => $mentionedIds,
            'count'              => count($mentionedIds),
        ];
    }
}
