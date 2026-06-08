#!/usr/bin/env php
<?php
/**
 * Orphan Chat Attachment Purge (run daily).
 *
 * A chat attachment is uploaded "staged" (chat_message_id = NULL) and only linked
 * to a message when the message is sent. If the user uploads then never sends, the
 * row + file linger forever. This deletes staged rows older than a grace period
 * plus their files on disk.
 *
 * Mirrors the bootstrap of todo-reminders.php: ROOT = the `site` dir; app files
 * live under ROOT/app, and chat_attachments.file_path is stored relative to
 * ROOT/app (e.g. "storage/uploads/chat/image_xxx.png").
 */

define('ROOT', realpath(__DIR__ . '/../..'));

require_once ROOT . '/app/Config/Database.php';

if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

const GRACE_HOURS = 6;   // allow upload → compose → send before a row is eligible
const BATCH_LIMIT = 1000;

$startedAt    = date('Y-m-d H:i');
$deletedRows  = 0;
$deletedFiles = 0;
$missingFiles = 0;
$errors       = [];

try {
    $pdo = \App\Config\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    fwrite(STDERR, "[purge-orphan-attachments $startedAt] DB connection failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

try {
    $rows = $pdo->query(
        "SELECT id, file_path
           FROM chat_attachments
          WHERE chat_message_id IS NULL
            AND created_at < DATE_SUB(NOW(), INTERVAL " . GRACE_HOURS . " HOUR)
          LIMIT " . BATCH_LIMIT
    )->fetchAll(\PDO::FETCH_ASSOC);

    $del = $pdo->prepare("DELETE FROM chat_attachments WHERE id = ?");

    foreach ($rows as $r) {
        $id   = (int) $r['id'];
        $path = (string) ($r['file_path'] ?? '');
        if ($path !== '') {
            // file_path is relative to ROOT/app (same recipe as viewAttachment).
            // Containment guard: resolve with realpath and require the result to sit
            // INSIDE the chat upload dir, so a DB-injected "../" can never escape it
            // and delete an arbitrary file (defence-in-depth vs a DB compromise).
            $base = realpath(ROOT . '/app/storage/uploads/chat');
            $full = realpath(ROOT . '/app/' . ltrim($path, '/'));
            if ($base !== false && $full !== false && strpos($full, $base . DIRECTORY_SEPARATOR) === 0 && is_file($full)) {
                if (@unlink($full)) { $deletedFiles++; }
                else { $errors[] = "unlink failed: $path"; }
            } else {
                $missingFiles++;
            }
        }
        $del->execute([$id]);
        $deletedRows++;
    }

    fwrite(STDOUT, "[purge-orphan-attachments $startedAt] rows=$deletedRows files=$deletedFiles missing=$missingFiles" . PHP_EOL);
    foreach ($errors as $e) { fwrite(STDERR, "[purge-orphan-attachments $startedAt] WARN: $e" . PHP_EOL); }
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, "[purge-orphan-attachments $startedAt] ERROR: " . $e->getMessage() . " (rows=$deletedRows before failure)" . PHP_EOL);
    exit(1);
}
