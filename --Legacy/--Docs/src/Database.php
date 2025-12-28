<?php

namespace App;

use PDO;

class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $dbPath = __DIR__ . '/../database.sqlite';
            self::$pdo = new PDO("sqlite:$dbPath");
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Enable foreign keys
            self::$pdo->exec("PRAGMA foreign_keys = ON;");
            
            self::initializeSchema();
        }
        return self::$pdo;
    }

    private static function initializeSchema(): void {
        $sql = "
        CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_id INTEGER NULL,
            type TEXT NOT NULL DEFAULT 'root', -- 'root' or 'sub'
            sort_order INTEGER DEFAULT 0,
            icon TEXT DEFAULT NULL,
            FOREIGN KEY(parent_id) REFERENCES items(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS translations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item_id INTEGER NOT NULL,
            locale TEXT NOT NULL, -- 'en' or 'ar'
            title TEXT NOT NULL,
            content TEXT,
            UNIQUE(item_id, locale),
            FOREIGN KEY(item_id) REFERENCES items(id) ON DELETE CASCADE
        );
        ";

        self::$pdo->exec($sql);

        // Seed "Getting Started" if not exists
        $stmt = self::$pdo->query("SELECT COUNT(*) FROM items WHERE id = 1");
        if ($stmt->fetchColumn() == 0) {
            // Force ID 1
            self::$pdo->exec("INSERT INTO items (id, type, sort_order) VALUES (1, 'root', 0)");
            self::$pdo->exec("INSERT INTO translations (item_id, locale, title, content) VALUES 
                (1, 'en', 'Getting Started', '<h1>Welcome</h1><p>This is the start page. Edit me in the dashboard!</p>'),
                (1, 'ar', 'البداية', '<h1>مرحباً</h1><p>هذه صفحة البداية. قم بتحريرها من لوحة التحكم!</p>')
            ");
        }
    }
}
