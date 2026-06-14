<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\MobileToken;
use App\Config\Database;
use App\Config\Constants;

/**
 * MobileController — the native mobile-app API surface.
 *
 * Runs IN PARALLEL to the existing web Session/Cookie login, which is left
 * untouched. All responses use ONE canonical envelope:
 *      success: { "ok": true,  ...payload }
 *      failure: { "ok": false, "error": { "code": "...", "message": "..." } }
 *
 * Auth model: opaque Bearer tokens (see App\Lib\MobileToken). The handshake,
 * login and refresh endpoints are public; everything else requires a valid
 * access token (Auth::check() transparently resolves the Bearer token).
 */
class MobileController
{
    /** Bumped when the mobile <-> backend contract changes in a breaking way. */
    const MOBILE_API_VERSION = '1.0';

    private $auth;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->pdo  = Database::getInstance()->getConnection();
    }

    // =================================================================
    // PUBLIC endpoints (no auth)
    // =================================================================

    /**
     * GET /api/mobile/handshake
     * First-launch compatibility check. The app refuses to continue unless
     * this returns ok:true with a matching mobile_api_version.
     */
    public function handshake()
    {
        $settings   = $this->getSettings();
        $clinicName = $settings['clinic_name_arabic']
            ?? $settings['clinic_name']
            ?? Constants::APP_NAME;

        // Clinic logo — relative path; the mobile app prefixes the clinic base URL.
        // A dark-mode variant is derived (roaya ships Light.png / Dark.png).
        $clinicLogo     = $settings['clinic_logo'] ?? '/assets/images/Light.png';
        $clinicLogoDark = $settings['clinic_logo_dark'] ?? preg_replace('/light/i', 'Dark', $clinicLogo);

        $this->respond([
            'ok'                 => true,
            'app'                => 'clinic-system',
            'clinic_name'        => $clinicName,
            'clinic_logo'        => $clinicLogo,
            'clinic_logo_dark'   => $clinicLogoDark,
            'version'            => Constants::APP_VERSION,
            'mobile_api_version' => self::MOBILE_API_VERSION,
            'auth_type'          => 'mobile_token',
            'features'           => [
                'patients'       => true,
                'visits'         => true,
                'prescriptions'  => true,
                'attachments'    => true,
                'camera_upload'  => true,
                'file_upload'    => true,
                'printing'       => true,
                'notifications'  => true,
                'biometric_lock' => true,
                'location'       => true,
            ],
        ]);
    }

    /**
     * POST /api/mobile/login   { username, password, platform?, device_id?, device_name? }
     */
    public function login()
    {
        $body     = $this->body();
        $username = trim((string) ($body['username'] ?? ''));
        $password = (string) ($body['password'] ?? '');
        $ip       = $this->clientIp();

        if ($username === '' || $password === '') {
            $this->fail('invalid_request', 'Username and password are required.', 400);
        }

        $mt = new MobileToken($this->pdo);

        if ($mt->isLoginThrottled($ip, $username)) {
            $this->fail('rate_limited', 'Too many attempts. Please try again in a few minutes.', 429);
        }

        // Verify credentials WITHOUT creating a web session. password_verify is
        // algo-agnostic — current hashes are bcrypt, never assume argon2id.
        $stmt = $this->pdo->prepare(
            "SELECT u.*, d.display_name AS doctor_name, d.specialty
             FROM users u
             LEFT JOIN doctors d ON u.id = d.user_id
             WHERE u.username = ? AND u.is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $mt->recordLogin($ip, $username, 'fail');
            $this->fail('invalid_credentials', 'Invalid username or password.', 401);
        }

        $mt->recordLogin($ip, $username, 'success');

        $pair = $mt->issuePair((int) $user['id'], $this->deviceContext($body, $user, $ip));

        $this->respond([
            'ok'            => true,
            'access_token'  => $pair['access_token'],
            'refresh_token' => $pair['refresh_token'],
            'token_type'    => 'Bearer',
            'expires_in'    => $pair['expires_in'],
            'user'          => $this->publicUser($user),
            'clinic'        => $this->clinicInfo($user['clinic_id'] ?? null),
        ]);
    }

    /**
     * POST /api/mobile/refresh   { refresh_token }
     * Rotates the pair. A replayed/revoked refresh token kills the session.
     */
    public function refresh()
    {
        $body = $this->body();
        $raw  = (string) ($body['refresh_token'] ?? '');
        $mt   = new MobileToken($this->pdo);

        try {
            $res = $mt->rotate($raw, [
                'platform'   => $this->str($body['platform'] ?? null, 20),
                'device_id'  => $this->str($body['device_id'] ?? null, 128),
                'user_agent' => $this->str($_SERVER['HTTP_USER_AGENT'] ?? null, 255),
                'ip'         => $this->clientIp(),
            ]);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'reuse') {
                $this->fail('token_reuse', 'Your session was invalidated for security. Please sign in again.', 401);
            }
            $this->fail('invalid_token', 'Refresh token is invalid or expired. Please sign in again.', 401);
        }

        // Re-load the user so role/clinic/is_active are always current.
        $user = $this->getUserById($res['user_id']);
        if (!$user) {
            $this->fail('invalid_token', 'This account is no longer active.', 401);
        }

        $this->respond([
            'ok'            => true,
            'access_token'  => $res['access_token'],
            'refresh_token' => $res['refresh_token'],
            'token_type'    => 'Bearer',
            'expires_in'    => $res['expires_in'],
            'user'          => $this->publicUser($user),
            'clinic'        => $this->clinicInfo($user['clinic_id'] ?? null),
        ]);
    }

    // =================================================================
    // AUTHENTICATED endpoints (Bearer access token)
    // =================================================================

    /** POST /api/mobile/logout — revoke the presented token's whole family. */
    public function logout()
    {
        $raw = $this->bearerToken();
        if ($raw) {
            (new MobileToken($this->pdo))->revokeByToken($raw, 'logout');
        }
        // Idempotent: always succeeds so the client can clear local state.
        $this->respond(['ok' => true]);
    }

    /** GET /api/mobile/me — current user + clinic. */
    public function me()
    {
        if (!$this->auth->check()) {
            $this->fail('unauthorized', 'Authentication required.', 401);
        }
        $user = $this->auth->user();
        $this->respond([
            'ok'     => true,
            'user'   => $this->publicUser($user),
            'clinic' => $this->clinicInfo($user['clinic_id'] ?? null),
        ]);
    }

    /** POST /api/mobile/device-token — register/refresh this device's push token. */
    public function registerDevice()
    {
        if (!$this->auth->check()) {
            $this->fail('unauthorized', 'Authentication required.', 401);
        }
        $user  = $this->auth->user();
        $body  = $this->body();
        $token = trim((string) ($body['token'] ?? $body['expo_token'] ?? ''));

        if (!preg_match('/^ExponentPushToken\[[^\]]+\]$/', $token)
            && !preg_match('/^[A-Za-z0-9_\-:.]{20,255}$/', $token)) {
            $this->fail('invalid_request', 'A valid push token is required.', 400);
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO mobile_device_tokens
                (user_id, clinic_id, expo_token, platform, device_id, last_seen_at, revoked)
             VALUES (?, ?, ?, ?, ?, NOW(), 0)
             ON DUPLICATE KEY UPDATE
                clinic_id   = VALUES(clinic_id),
                platform    = VALUES(platform),
                device_id   = VALUES(device_id),
                revoked     = 0,
                last_seen_at = NOW()"
        );
        $stmt->execute([
            $user['id'],
            $user['clinic_id'] ?? null,
            $token,
            $this->str($body['platform'] ?? null, 20),
            $this->str($body['device_id'] ?? null, 128),
        ]);

        $this->respond(['ok' => true]);
    }

    /** DELETE /api/mobile/device-token — stop pushing to this device. */
    public function unregisterDevice()
    {
        if (!$this->auth->check()) {
            $this->fail('unauthorized', 'Authentication required.', 401);
        }
        $user  = $this->auth->user();
        $body  = $this->body();
        $token = trim((string) ($body['token'] ?? $body['expo_token'] ?? ''));

        if ($token === '') {
            $this->fail('invalid_request', 'A push token is required.', 400);
        }

        $stmt = $this->pdo->prepare(
            "UPDATE mobile_device_tokens SET revoked = 1 WHERE user_id = ? AND expo_token = ?"
        );
        $stmt->execute([$user['id'], $token]);

        $this->respond(['ok' => true]);
    }

    // =================================================================
    // Helpers
    // =================================================================

    private function deviceContext(array $body, array $user, $ip)
    {
        return [
            'clinic_id'   => $user['clinic_id'] ?? null,
            'platform'    => $this->str($body['platform'] ?? null, 20),
            'device_id'   => $this->str($body['device_id'] ?? null, 128),
            'device_name' => $this->str($body['device_name'] ?? null, 128),
            'user_agent'  => $this->str($_SERVER['HTTP_USER_AGENT'] ?? null, 255),
            'ip'          => $ip,
        ];
    }

    /** Whitelisted, non-sensitive view of a user row (never leak password_hash). */
    private function publicUser(array $user)
    {
        $name = $user['doctor_name']
            ?? $user['full_name']
            ?? $user['name']
            ?? $user['display_name']
            ?? $user['username']
            ?? null;

        return [
            'id'        => (int) $user['id'],
            'username'  => $user['username'] ?? null,
            'name'      => $name,
            'role'      => $user['role'] ?? null,
            'clinic_id' => isset($user['clinic_id']) && $user['clinic_id'] !== null
                ? (int) $user['clinic_id'] : null,
            'specialty' => $user['specialty'] ?? null,
        ];
    }

    private function clinicInfo($clinicId)
    {
        $host = $_SERVER['HTTP_HOST'] ?? null;

        if ($clinicId === null || $clinicId === '') {
            // Doctors/admins span all clinics — app shows a branch switcher.
            return ['id' => null, 'name' => null, 'code' => null, 'domain' => $host, 'all_clinics' => true];
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM clinics WHERE id = ? LIMIT 1");
            $stmt->execute([$clinicId]);
            $c = $stmt->fetch();
        } catch (\Throwable $e) {
            $c = null;
        }

        if (!$c) {
            return ['id' => (int) $clinicId, 'name' => null, 'code' => null, 'domain' => $host, 'all_clinics' => false];
        }

        return [
            'id'          => (int) $c['id'],
            'name'        => $c['name_ar'] ?? $c['name_en'] ?? $c['code'] ?? null,
            'name_en'     => $c['name_en'] ?? null,
            'code'        => $c['code'] ?? null,
            'domain'      => $host,
            'all_clinics' => false,
        ];
    }

    private function getUserById($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.*, d.display_name AS doctor_name, d.specialty
             FROM users u
             LEFT JOIN doctors d ON u.id = d.user_id
             WHERE u.id = ? AND u.is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function getSettings()
    {
        try {
            $rows = $this->pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $out[$r['setting_key']] = $r['setting_value'];
        }
        return $out;
    }

    private function body()
    {
        $raw = file_get_contents('php://input');
        if ($raw !== '' && $raw !== false) {
            $j = json_decode($raw, true);
            if (is_array($j)) {
                return $j;
            }
        }
        return is_array($_POST) ? $_POST : [];
    }

    private function bearerToken()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if ($header === '' && function_exists('getallheaders')) {
            foreach (getallheaders() as $k => $v) {
                if (strtolower($k) === 'authorization') {
                    $header = $v;
                    break;
                }
            }
        }
        if (preg_match('/^Bearer\s+([a-f0-9]{64})$/i', trim((string) $header), $m)) {
            return strtolower($m[1]);
        }
        return null;
    }

    /** Real client IP — prefer Cloudflare's header in prod (REMOTE_ADDR is CF's edge). */
    private function clientIp()
    {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        return substr((string) $ip, 0, 64);
    }

    private function str($v, $max)
    {
        if ($v === null) {
            return null;
        }
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        return substr($v, 0, $max);
    }

    private function respond(array $payload, $status = 200)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function fail($code, $message, $status)
    {
        $this->respond(['ok' => false, 'error' => ['code' => $code, 'message' => $message]], $status);
    }
}
