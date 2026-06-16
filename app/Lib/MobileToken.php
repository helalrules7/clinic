<?php

namespace App\Lib;

use App\Config\Database;

/**
 * Mobile API opaque bearer-token store (access + rotating refresh).
 *
 * Modeled on prescription_share_tokens: a random 256-bit secret per token,
 * only its sha256(raw) hex is persisted, tokens expire, can be revoked, and
 * refresh tokens rotate with family-based reuse detection.
 *
 * Raw tokens are 32-byte random, hex-encoded (64 lowercase hex chars). They
 * are returned to the client ONCE and never stored or logged server-side.
 *
 * This class is intentionally self-contained (no session, no cookies) so it
 * can authenticate a stateless mobile client without touching the existing
 * web Session/Cookie login.
 */
class MobileToken
{
    /** Access token lifetime (seconds). Short — refreshed silently by the app. */
    const ACCESS_TTL = 900;          // 15 minutes

    /** Refresh token lifetime (seconds). */
    const REFRESH_TTL = 2592000;     // 30 days

    /** Web-login handoff ticket lifetime (seconds). Single-use, very short. */
    const WEB_TICKET_TTL = 60;

    // DB-backed login rate limiting (mobile login only).
    const LOGIN_WINDOW       = 900;  // 15 minutes
    const LOGIN_MAX_PER_IP   = 15;   // failed attempts / window / IP
    const LOGIN_MAX_PER_USER = 6;    // failed attempts / window / username

    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::getInstance()->getConnection();
    }

    // ---------------------------------------------------------------
    // Token minting / verification
    // ---------------------------------------------------------------

    /** Issue a fresh access + refresh pair for a new login. */
    public function issuePair($userId, array $ctx = [])
    {
        $family = $this->uuid4();
        [$access]  = $this->insert($userId, 'access',  $family, null, self::ACCESS_TTL,  $ctx);
        [$refresh] = $this->insert($userId, 'refresh', $family, null, self::REFRESH_TTL, $ctx);

        return [
            'access_token'  => $access,
            'refresh_token' => $refresh,
            'expires_in'    => self::ACCESS_TTL,
            'family'        => $family,
        ];
    }

    /**
     * Verify an access token. Returns the token row (incl. user_id) or null.
     * Touches last_used_at on success.
     */
    public function verifyAccess($raw)
    {
        if (!$this->validShape($raw)) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT * FROM mobile_auth_tokens
             WHERE token_hash = ? AND type = 'access' AND revoked = 0
               AND (expires_at IS NULL OR expires_at > NOW())
             LIMIT 1"
        );
        $stmt->execute([$this->hash($raw)]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $upd = $this->pdo->prepare("UPDATE mobile_auth_tokens SET last_used_at = NOW() WHERE id = ?");
        $upd->execute([$row['id']]);

        return $row;
    }

    /**
     * Mint a single-use, ~60s "web ticket" — handed to /mobile/web-login so the
     * real access token never travels in a URL. Returns ['ticket','expires_in'].
     */
    public function issueWebTicket($userId, array $ctx = [])
    {
        [$raw] = $this->insert($userId, 'web_ticket', $this->uuid4(), null, self::WEB_TICKET_TTL, $ctx);
        return ['ticket' => $raw, 'expires_in' => self::WEB_TICKET_TTL];
    }

    /**
     * Atomically consume a web ticket. Returns the user_id on success (and burns
     * the ticket so it can never be replayed), or null if missing / expired /
     * already used. The revoked 0→1 UPDATE with a rowCount() guard makes the
     * single-use check race-safe against a double-tap / double-redirect.
     */
    public function consumeWebTicket($raw)
    {
        if (!$this->validShape($raw)) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT id, user_id FROM mobile_auth_tokens
             WHERE token_hash = ? AND type = 'web_ticket' AND revoked = 0
               AND (expires_at IS NULL OR expires_at > NOW())
             LIMIT 1"
        );
        $stmt->execute([$this->hash($raw)]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $burn = $this->pdo->prepare(
            "UPDATE mobile_auth_tokens
             SET revoked = 1, revoked_reason = 'used', last_used_at = NOW()
             WHERE id = ? AND revoked = 0"
        );
        $burn->execute([$row['id']]);
        if ($burn->rowCount() !== 1) {
            return null; // lost the race — another request already consumed it
        }

        return (int) $row['user_id'];
    }

    /**
     * Rotate a refresh token. Returns a brand-new pair, or throws \RuntimeException:
     *   'invalid' — bad shape / unknown / expired
     *   'reuse'   — the presented refresh was already rotated/revoked; the whole
     *               family is killed (a stolen-and-replayed token scenario).
     */
    public function rotate($raw, array $ctx = [])
    {
        if (!$this->validShape($raw)) {
            throw new \RuntimeException('invalid');
        }

        $stmt = $this->pdo->prepare(
            "SELECT * FROM mobile_auth_tokens WHERE token_hash = ? AND type = 'refresh' LIMIT 1"
        );
        $stmt->execute([$this->hash($raw)]);
        $tok = $stmt->fetch();

        if (!$tok) {
            throw new \RuntimeException('invalid');
        }
        if ((int) $tok['revoked'] === 1) {
            // A revoked refresh token is being presented again -> reuse/theft.
            $this->revokeFamily($tok['family_id'], 'reuse_detected');
            throw new \RuntimeException('reuse');
        }
        if (!empty($tok['expires_at']) && strtotime($tok['expires_at']) < time()) {
            throw new \RuntimeException('invalid');
        }

        // Burn the presented refresh + any live access tokens in this family,
        // then mint a new pair in the SAME family (chained via parent_id).
        $burn = $this->pdo->prepare(
            "UPDATE mobile_auth_tokens SET revoked = 1, revoked_reason = 'rotated' WHERE id = ?"
        );
        $burn->execute([$tok['id']]);

        $burnAccess = $this->pdo->prepare(
            "UPDATE mobile_auth_tokens SET revoked = 1, revoked_reason = 'rotated'
             WHERE family_id = ? AND type = 'access' AND revoked = 0"
        );
        $burnAccess->execute([$tok['family_id']]);

        if (!isset($ctx['clinic_id'])) {
            $ctx['clinic_id'] = $tok['clinic_id'];
        }

        [$access]  = $this->insert($tok['user_id'], 'access',  $tok['family_id'], $tok['id'], self::ACCESS_TTL,  $ctx);
        [$refresh] = $this->insert($tok['user_id'], 'refresh', $tok['family_id'], $tok['id'], self::REFRESH_TTL, $ctx);

        return [
            'user_id'       => (int) $tok['user_id'],
            'access_token'  => $access,
            'refresh_token' => $refresh,
            'expires_in'    => self::ACCESS_TTL,
        ];
    }

    // ---------------------------------------------------------------
    // Revocation
    // ---------------------------------------------------------------

    public function revokeFamily($familyId, $reason = 'logout')
    {
        $stmt = $this->pdo->prepare(
            "UPDATE mobile_auth_tokens SET revoked = 1, revoked_reason = ?
             WHERE family_id = ? AND revoked = 0"
        );
        $stmt->execute([$reason, $familyId]);
    }

    /** Revoke the family a given (access or refresh) token belongs to. */
    public function revokeByToken($raw, $reason = 'logout')
    {
        if (!$this->validShape($raw)) {
            return false;
        }
        $stmt = $this->pdo->prepare("SELECT family_id FROM mobile_auth_tokens WHERE token_hash = ? LIMIT 1");
        $stmt->execute([$this->hash($raw)]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        $this->revokeFamily($row['family_id'], $reason);
        return true;
    }

    /** Kill every live mobile token for a user (e.g. on password change). */
    public function revokeAllForUser($userId, $reason = 'password_change')
    {
        $stmt = $this->pdo->prepare(
            "UPDATE mobile_auth_tokens SET revoked = 1, revoked_reason = ?
             WHERE user_id = ? AND revoked = 0"
        );
        $stmt->execute([$reason, $userId]);
    }

    // ---------------------------------------------------------------
    // DB-backed login rate limiting (mobile login only)
    // ---------------------------------------------------------------

    public function isLoginThrottled($ip, $username)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COALESCE(SUM(ip_address <=> ?), 0) AS by_ip,
                COALESCE(SUM(username   <=> ?), 0) AS by_user
             FROM mobile_login_attempts
             WHERE outcome = 'fail'
               AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$ip, $username, self::LOGIN_WINDOW]);
        $r = $stmt->fetch() ?: ['by_ip' => 0, 'by_user' => 0];

        return ((int) $r['by_ip'] >= self::LOGIN_MAX_PER_IP)
            || ((int) $r['by_user'] >= self::LOGIN_MAX_PER_USER);
    }

    public function recordLogin($ip, $username, $outcome)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO mobile_login_attempts (ip_address, username, outcome) VALUES (?, ?, ?)"
        );
        $stmt->execute([$ip, $username, $outcome === 'success' ? 'success' : 'fail']);
    }

    // ---------------------------------------------------------------
    // Internals
    // ---------------------------------------------------------------

    private function insert($userId, $type, $familyId, $parentId, $ttl, array $ctx)
    {
        $raw = $this->newSecret();
        $stmt = $this->pdo->prepare(
            "INSERT INTO mobile_auth_tokens
                (user_id, token_hash, type, family_id, parent_id, clinic_id,
                 platform, device_id, device_name, user_agent, ip_address, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))"
        );
        $stmt->execute([
            $userId,
            $this->hash($raw),
            $type,
            $familyId,
            $parentId,
            $ctx['clinic_id'] ?? null,
            $ctx['platform'] ?? null,
            $ctx['device_id'] ?? null,
            $ctx['device_name'] ?? null,
            $ctx['user_agent'] ?? null,
            $ctx['ip'] ?? null,
            (int) $ttl,
        ]);

        return [$raw, (int) $this->pdo->lastInsertId()];
    }

    private function newSecret()
    {
        return bin2hex(random_bytes(32)); // 64 lowercase hex chars (256-bit)
    }

    private function hash($raw)
    {
        return hash('sha256', (string) $raw);
    }

    private function validShape($raw)
    {
        return is_string($raw) && preg_match('/^[a-f0-9]{64}$/', $raw) === 1;
    }

    private function uuid4()
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        $hex = bin2hex($b);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
             . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
    }
}
