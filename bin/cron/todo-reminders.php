#!/usr/bin/env php
<?php
/**
 * Todo Reminders Cron
 *
 * Runs every 5 minutes. Dispatches:
 *   1. Lead-time reminders (remind_before_minutes ahead of due_at)
 *   2. At-due notifications (when due_at <= NOW())
 *
 * Both write into the `notifications` table and mark the todo
 * row so the same reminder is never dispatched twice.
 */

define('ROOT', realpath(__DIR__ . '/../..'));

require_once ROOT . '/app/Config/Database.php';
// NotificationController::create() is the single source of truth for the
// notifications schema (message + related_type/related_id; no body/link/icon).
require_once ROOT . '/app/Controllers/NotificationController.php';

// Only start a session when this is run via the web (defensive — this
// script is intended for the CLI, but ROOT/index bootstrap may have
// side effects on shared hosts).
if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Render a "X" string from a minute count.
 *
 * Examples:
 *   15   -> "15 min"
 *   60   -> "1 hour"
 *   240  -> "4 hours"
 *   1440 -> "1 day"
 *   2880 -> "2 days"
 */
function humanizeMinutes($minutes) {
    $m = (int)$minutes;
    if ($m <= 0) {
        return '0 min';
    }
    if ($m < 60) {
        return $m . ' min';
    }
    if ($m < 1440) {
        $h = (int) floor($m / 60);
        return $h . ' ' . ($h === 1 ? 'hour' : 'hours');
    }
    $d = (int) floor($m / 1440);
    return $d . ' ' . ($d === 1 ? 'day' : 'days');
}

$startedAt = date('Y-m-d H:i');
$leadCount = 0;
$dueCount  = 0;

try {
    $pdo = \App\Config\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    fwrite(STDERR, "[todo-cron $startedAt] DB connection failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

try {
    // ------------------------------------------------------------------
    // 1) LEAD-TIME reminders
    // ------------------------------------------------------------------
    $leadSql = "
        SELECT id, user_id, title, patient_id, due_at, remind_before_minutes
        FROM todos
        WHERE status = 'open'
          AND remind_before_minutes IS NOT NULL
          AND todo_reminded_at IS NULL
          AND DATE_SUB(due_at, INTERVAL remind_before_minutes MINUTE) <= NOW()
          AND due_at > NOW()
        LIMIT 500
    ";
    $leadRows = $pdo->query($leadSql)->fetchAll(\PDO::FETCH_ASSOC);

    $markRemindedSql = "UPDATE todos SET todo_reminded_at = NOW() WHERE id = :id";
    $markReminded    = $pdo->prepare($markRemindedSql);

    foreach ($leadRows as $row) {
        $todoId    = (int) $row['id'];
        $userId    = (int) $row['user_id'];
        $title     = (string) ($row['title'] ?? '');
        $patientId = $row['patient_id'] !== null ? (int) $row['patient_id'] : null;
        $window    = humanizeMinutes($row['remind_before_minutes']);

        \App\Controllers\NotificationController::create(
            $userId, 'todo_reminder', 'Upcoming: ' . $title, 'Due in ' . $window,
            'todo', $todoId, $patientId
        );

        $markReminded->execute([':id' => $todoId]);
        $leadCount++;
    }

    // ------------------------------------------------------------------
    // 2) AT-DUE notifications
    // ------------------------------------------------------------------
    $dueSql = "
        SELECT id, user_id, title, patient_id, due_at
        FROM todos
        WHERE status = 'open'
          AND due_at <= NOW()
          AND todo_notified_at IS NULL
        LIMIT 500
    ";
    $dueRows = $pdo->query($dueSql)->fetchAll(\PDO::FETCH_ASSOC);

    $markNotifiedSql = "UPDATE todos SET todo_notified_at = NOW() WHERE id = :id";
    $markNotified    = $pdo->prepare($markNotifiedSql);

    foreach ($dueRows as $row) {
        $todoId    = (int) $row['id'];
        $userId    = (int) $row['user_id'];
        $title     = (string) ($row['title'] ?? '');
        $patientId = $row['patient_id'] !== null ? (int) $row['patient_id'] : null;

        \App\Controllers\NotificationController::create(
            $userId, 'todo_due', 'Due now: ' . $title, 'Task is due now',
            'todo', $todoId, $patientId
        );

        $markNotified->execute([':id' => $todoId]);
        $dueCount++;
    }

    fwrite(
        STDOUT,
        "[todo-cron $startedAt] $leadCount lead reminders + $dueCount at-due dispatched" . PHP_EOL
    );
    exit(0);
} catch (\Throwable $e) {
    fwrite(
        STDERR,
        "[todo-cron $startedAt] ERROR: " . $e->getMessage()
        . " (lead=$leadCount, due=$dueCount before failure)" . PHP_EOL
    );
    exit(1);
}
