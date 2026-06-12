<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Config\Database;
use PDO;

/**
 * BackupController — doctor/admin backup hub (v12).
 *
 * Three backup types, each generated SERVER-SIDE into the backups/ folder and
 * listed for download (no restore):
 *   - database          : main DB + drugs DB (scope: main | drugs | both)
 *   - database_uploads  : both DBs + all uploaded files / attachments
 *   - system            : both DBs + full public_html + cron snapshot (compressed)
 *
 * The heavy work runs in bin/backup-run.php (background CLI) so big archives
 * never time out the web request. This controller spawns it, exposes progress
 * (status), the list, secure downloads and delete.
 */
class BackupController
{
    private $auth;
    private $pdo;
    private $dir;        // backups dir
    private $statusDir;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->pdo  = Database::getInstance()->getConnection();
        // Backups live OUTSIDE the web docroot: the panel's `private/` dir is in
        // open_basedir, writable by the web user, and NOT served by nginx (which
        // ignores .htaccess). Falls back to <app>/backups for local dev.
        $appRoot = dirname(__DIR__, 2);
        $private = dirname($appRoot) . '/private';
        $this->dir = (is_dir($private) && is_writable($private)) ? $private . '/backups' : $appRoot . '/backups';
        $this->statusDir = $this->dir . '/.status';
    }

    /* ── helpers ─────────────────────────────────────────────── */

    private function json($data, $code = 200)
    {
        if (ob_get_level() > 0) { ob_end_clean(); }
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function guard($needCsrf = false)
    {
        if (!$this->auth->check()) { $this->json(['success' => false, 'message' => 'Unauthorized'], 401); }
        $user = $this->auth->user();
        if (!in_array($user['role'] ?? '', ['doctor', 'admin'], true)) {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        if ($needCsrf) {
            $token = $_POST['csrf_token']
                ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 419);
            }
        }
        return $user;
    }

    /** Resolve + validate a backup filename to a safe absolute path inside the dir. */
    private function safePath($name)
    {
        $name = basename((string)$name);
        if (!preg_match('/^[A-Za-z0-9._\-]+\.(tar\.gz|sql\.gz|zip|sql)$/', $name)) return null;
        $path = $this->dir . '/' . $name;
        $real = realpath($path);
        if ($real === false || strpos($real, realpath($this->dir) . DIRECTORY_SEPARATOR) !== 0) return null;
        return $real;
    }

    private function typeOf($file)
    {
        if (strpos($file, 'database_uploads_') === 0) return 'database_uploads';
        if (strpos($file, 'system_') === 0) return 'system';
        if (strpos($file, 'database_') === 0) return 'database';
        return 'other';
    }

    /* ── endpoints ───────────────────────────────────────────── */

    /** POST /api/backup/create  body: {type, scope} */
    public function create()
    {
        $user = $this->guard(true);
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $type  = $input['type'] ?? '';
        $scope = $input['scope'] ?? 'both';
        if (!in_array($type, ['database', 'database_uploads', 'system'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid backup type'], 400);
        }
        if (!in_array($scope, ['main', 'drugs', 'both'], true)) { $scope = 'both'; }

        @mkdir($this->dir, 0775, true);
        @mkdir($this->statusDir, 0775, true);

        $jobId = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
        // Seed an initial status so the UI can poll immediately.
        @file_put_contents("$this->statusDir/$jobId.json", json_encode([
            'job' => $jobId, 'type' => $type, 'scope' => $scope,
            'status' => 'running', 'phase' => 'queued', 'pct' => 0,
        ]));

        $runner = dirname(__DIR__, 2) . '/bin/backup-run.php';
        // NB: shell_exec/proc_open/popen are disabled on prod (hardening); only exec()
        // is allowed — use it for the php-path probe and the background spawn.
        $probe = []; @exec('command -v php 2>/dev/null', $probe);
        $php = trim($probe[0] ?? '');
        if ($php === '' || !is_file($php)) { $php = '/usr/bin/php'; }
        $cmd = sprintf(
            'nohup %s %s %s %s %s %d > /dev/null 2>&1 &',
            escapeshellarg($php),
            escapeshellarg($runner),
            escapeshellarg($jobId),
            escapeshellarg($type),
            escapeshellarg($scope),
            (int)($user['id'] ?? 0)
        );
        @exec($cmd);

        $this->json(['success' => true, 'job' => $jobId]);
    }

    /** GET /api/backup/status?job=... */
    public function status()
    {
        $this->guard();
        $job = preg_replace('/[^A-Za-z0-9_\-]/', '', $_GET['job'] ?? '');
        $f = "$this->statusDir/$job.json";
        if ($job === '' || !is_file($f)) { $this->json(['success' => false, 'message' => 'Unknown job'], 404); }
        $st = json_decode((string)file_get_contents($f), true) ?: [];
        $this->json(['success' => true, 'status' => $st]);
    }

    /** GET /api/backup/list */
    public function index()
    {
        $this->guard();
        $items = [];
        foreach (glob($this->dir . '/*.{tar.gz,sql.gz,zip,sql}', GLOB_BRACE) ?: [] as $path) {
            $name = basename($path);
            if (strpos($name, '.part') !== false) continue;
            $items[] = [
                'file'  => $name,
                'type'  => $this->typeOf($name),
                'size'  => filesize($path),
                'mtime' => filemtime($path),
            ];
        }
        usort($items, function ($a, $b) { return $b['mtime'] <=> $a['mtime']; });

        // running jobs (status files that are still running)
        $running = [];
        foreach (glob($this->statusDir . '/*.json') ?: [] as $sf) {
            $st = json_decode((string)file_get_contents($sf), true);
            if ($st && ($st['status'] ?? '') === 'running') { $running[] = $st; }
        }
        $this->json(['success' => true, 'backups' => $items, 'running' => $running]);
    }

    /** GET /api/backup/download?file=... */
    public function download()
    {
        $this->guard();
        $path = $this->safePath($_GET['file'] ?? '');
        if (!$path) { http_response_code(404); echo 'Not found'; exit; }
        if (ob_get_level() > 0) { ob_end_clean(); }
        $name = basename($path);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        $fp = fopen($path, 'rb');
        while (!feof($fp)) { echo fread($fp, 1 << 20); @ob_flush(); flush(); }
        fclose($fp);
        exit;
    }

    /** POST /api/backup/delete  body/query: file */
    public function delete()
    {
        $this->guard(true);
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $path = $this->safePath($input['file'] ?? ($_GET['file'] ?? ''));
        if (!$path) { $this->json(['success' => false, 'message' => 'Invalid file'], 400); }
        @unlink($path);
        $this->json(['success' => true]);
    }
}
