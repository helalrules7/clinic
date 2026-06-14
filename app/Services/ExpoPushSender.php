<?php

namespace App\Services;

use App\Config\Database;

/**
 * Sends push notifications to a user's registered mobile devices via the Expo
 * Push API (https://exp.host/--/api/v2/push/send).
 *
 * Tokens are stored in mobile_device_tokens (see migration 2026_06_14_mobile_api).
 * Best-effort + non-blocking: any failure is swallowed so it never breaks the
 * request that triggered it. NEVER put PHI (patient names, medical content) in
 * the title/body — callers pass a generic, privacy-safe message.
 */
class ExpoPushSender
{
    const EXPO_ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::getInstance()->getConnection();
    }

    /**
     * Push to every active device of a user.
     *
     * @param int    $userId
     * @param string $title  generic title (no PHI)
     * @param string $body   generic body (no PHI)
     * @param array  $data   small payload, e.g. ['route' => '/(app)/notifications']
     */
    public function sendToUser($userId, $title, $body, array $data = [])
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT expo_token FROM mobile_device_tokens WHERE user_id = ? AND revoked = 0"
            );
            $stmt->execute([$userId]);
            $tokens = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return; // table missing / DB error — push is optional
        }

        if (empty($tokens)) {
            return;
        }

        $messages = [];
        foreach ($tokens as $token) {
            // Only Expo tokens go to the Expo endpoint.
            if (strpos($token, 'ExponentPushToken') !== 0 && strpos($token, 'ExpoPushToken') !== 0) {
                continue;
            }
            $messages[] = [
                'to'        => $token,
                'title'     => $title,
                'body'      => $body,
                'data'      => $data,
                'sound'     => 'default',
                'channelId' => 'default',
                'priority'  => 'high',
            ];
        }

        if (empty($messages)) {
            return;
        }

        $this->post(self::EXPO_ENDPOINT, $messages);
    }

    private function post($url, array $payload)
    {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Accept-Encoding: gzip, deflate',
                ],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // best-effort; ignore
        }
    }
}
