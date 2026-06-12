<?php

namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use PDO;
use PDOException;
use Throwable;

/**
 * v12.0.0 — Appearance settings (theme palette + auto-schedule).
 *
 * IMPORTANT: doctor_settings is a KEY-VALUE table. Schema:
 *   id, user_id, setting_key, setting_value, setting_type, created_at, updated_at
 *   UNIQUE KEY (user_id, setting_key)
 *
 * Every preference is one ROW. We use INSERT ... ON DUPLICATE KEY UPDATE so
 * the controller can be called multiple times safely and so the standard
 * settings.js / updatePersonalPreference flow keeps working alongside.
 *
 * Keys this controller reads/writes:
 *   theme_palette         (string)  one of indigo|emerald|rose|slate|amber|ocean
 *   theme_auto_schedule   (bool)    "1" or "0"
 *   theme_dark_from       (string)  "HH:MM"
 *   theme_light_from      (string)  "HH:MM"
 */
class SettingsControllerV11
{
    private $pdo;
    private $auth;

    private const ALLOWED_PALETTES = ['indigo', 'emerald', 'rose', 'slate', 'amber', 'ocean'];

    public function __construct()
    {
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /* ---------------- helpers ---------------- */

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
        if ($raw === false || $raw === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function settingsTableForRole(?string $role): string
    {
        return ($role === 'secretary') ? 'secretary_settings' : 'doctor_settings';
    }

    /**
     * UPSERT a single key on doctor_settings / secretary_settings via the key-value pattern.
     */
    private function setKey(int $userId, string $key, string $value, string $type = 'string', ?string $role = null): bool
    {
        $table = $this->settingsTableForRole($role);
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$table} (user_id, setting_key, setting_value, setting_type)
                 VALUES (:uid, :k, :v, :t)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value),
                                         setting_type  = VALUES(setting_type),
                                         updated_at    = CURRENT_TIMESTAMP"
            );
            return $stmt->execute([
                ':uid' => $userId,
                ':k'   => $key,
                ':v'   => $value,
                ':t'   => $type,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    private function getKeys(int $userId, array $keys, ?string $role = null): array
    {
        if (!$keys) return [];
        $table = $this->settingsTableForRole($role);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT setting_key, setting_value
               FROM {$table}
              WHERE user_id = ? AND setting_key IN ($placeholders)"
        );
        $stmt->execute(array_merge([$userId], $keys));
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    }

    private function validTime(string $s): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $s);
    }

    /* ---------------- endpoints ---------------- */

    /** POST /api/settings/theme-palette */
    public function setThemePalette()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $body = $this->readJsonBody();
        $palette = isset($body['palette']) ? (string)$body['palette'] : '';
        if (!in_array($palette, self::ALLOWED_PALETTES, true)) {
            $this->respond(422, ['success' => false, 'message' => 'Invalid palette']);
            return;
        }

        $ok = $this->setKey((int)$user['id'], 'theme_palette', $palette, 'string', $user['role'] ?? null);
        if (!$ok) {
            $this->respond(500, ['success' => false, 'message' => 'Failed to save palette']);
            return;
        }
        $this->respond(200, ['success' => true, 'palette' => $palette]);
    }

    /** POST /api/settings/theme-auto-schedule */
    public function setThemeAutoSchedule()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $body = $this->readJsonBody();
        $enabled   = !empty($body['enabled']);
        $darkFrom  = isset($body['dark_from'])  ? (string)$body['dark_from']  : '19:00';
        $lightFrom = isset($body['light_from']) ? (string)$body['light_from'] : '07:00';

        if (!$this->validTime($darkFrom) || !$this->validTime($lightFrom)) {
            $this->respond(422, ['success' => false, 'message' => 'Times must be HH:MM']);
            return;
        }

        $uid = (int)$user['id'];
        $role = $user['role'] ?? null;
        try {
            $this->pdo->beginTransaction();
            $this->setKey($uid, 'theme_auto_schedule', $enabled ? '1' : '0', 'bool', $role);
            $this->setKey($uid, 'theme_dark_from',  $darkFrom,  'string', $role);
            $this->setKey($uid, 'theme_light_from', $lightFrom, 'string', $role);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $this->respond(500, ['success' => false, 'message' => 'Failed to save schedule']);
            return;
        }

        $this->respond(200, [
            'success' => true,
            'enabled' => $enabled,
            'dark_from'  => $darkFrom,
            'light_from' => $lightFrom,
        ]);
    }

    /** GET /api/settings/appearance */
    public function getAppearance()
    {
        $this->jsonHeader();
        $user = $this->requireUser();
        if (!$user) return;

        $uid = (int)$user['id'];
        $role = $user['role'] ?? null;
        try {
            $vals = $this->getKeys($uid, [
                'theme_palette',
                'theme_auto_schedule',
                'theme_dark_from',
                'theme_light_from',
            ], $role);
        } catch (Throwable $e) {
            $vals = [];
        }

        $palette = $vals['theme_palette'] ?? 'indigo';
        if (!in_array($palette, self::ALLOWED_PALETTES, true)) $palette = 'indigo';

        $this->respond(200, [
            'success' => true,
            'palette' => $palette,
            'theme_auto_schedule' => isset($vals['theme_auto_schedule'])
                ? ((string)$vals['theme_auto_schedule'] === '1' || strtolower((string)$vals['theme_auto_schedule']) === 'true')
                : false,
            'theme_dark_from'  => $vals['theme_dark_from']  ?? '19:00',
            'theme_light_from' => $vals['theme_light_from'] ?? '07:00',
        ]);
    }
}
