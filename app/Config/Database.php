<?php

namespace App\Config;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $this->loadEnvironment();
        
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'hclinic_roaya';
        $username = $_ENV['DB_USER'] ?? 'hclinic_roaya';
        $password = $_ENV['DB_PASS'] ?? 'Carmen@1230';
        
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        
        try {
            // Pin the DB session to whatever offset PHP is currently using so
            // DATE(created_at) / CURDATE() / NOW() all agree with the application
            // timezone. We compute the offset dynamically (rather than hardcoding
            // +02:00) so Egypt's DST switch — and any future timezone change in
            // index.php / .env — propagates without code edits. Without this,
            // MariaDB falls back to SYSTEM (UTC in container) and "today's" stats
            // silently exclude rows created late at night.
            $tzOffset = $this->computeSessionOffset();
            $this->pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '{$tzOffset}'"
            ]);
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Build a "+HH:MM" / "-HH:MM" offset string from the active PHP timezone.
     * Recomputed at connect time so DST transitions are picked up automatically.
     */
    private function computeSessionOffset()
    {
        try {
            $tz = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
            $secs = $tz->getOffset(new \DateTime('now', $tz));
            $sign = $secs >= 0 ? '+' : '-';
            $abs  = abs($secs);
            $h    = intval($abs / 3600);
            $m    = intval(($abs % 3600) / 60);
            return sprintf('%s%02d:%02d', $sign, $h, $m);
        } catch (\Throwable $e) {
            return '+00:00';
        }
    }

    private function loadEnvironment()
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }
}

