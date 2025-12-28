<?php

namespace App\Models;

use App\Database;
use PDO;

class Post {
    public function create(array $data): int {
        $pdo = Database::getConnection();
        
        try {
            $pdo->beginTransaction();

            // 1. Insert Item
            $stmt = $pdo->prepare("INSERT INTO items (parent_id, type, icon) VALUES (:parent_id, :type, :icon)");
            $stmt->execute([
                ':parent_id' => $data['parent_id'] ?: null,
                ':type' => $data['type'],
                ':icon' => $data['icon'] ?? null
            ]);
            $itemId = $pdo->lastInsertId();

            // 2. Insert English Translation
            $this->saveTranslation($itemId, 'en', $data['title_en'], $data['content_en']);

            // 3. Insert Arabic Translation
            $this->saveTranslation($itemId, 'ar', $data['title_ar'], $data['content_ar']);

            $pdo->commit();
            return $itemId;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function saveTranslation($itemId, $locale, $title, $content) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO translations (item_id, locale, title, content) VALUES (:item_id, :locale, :title, :content)");
        $stmt->execute([
            ':item_id' => $itemId,
            ':locale' => $locale,
            ':title' => $title,
            ':content' => $content
        ]);
    }

    public function getAll($locale = 'en'): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT i.id, i.parent_id, i.type, i.sort_order, i.icon, t.title, t.content 
            FROM items i 
            LEFT JOIN translations t ON t.item_id = i.id AND t.locale = :locale
            ORDER BY i.sort_order ASC, i.id ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':locale' => $locale]);
        return $stmt->fetchAll();
    }
    
    public function getTree($locale = 'en'): array {
        $items = $this->getAll($locale);
        $roots = [];
        $allItems = [];

        // First pass: organize all items by ID
        foreach ($items as $item) {
            $item['children'] = [];
            $allItems[$item['id']] = $item;
            
            if ($item['type'] === 'root') {
                $roots[$item['id']] = &$allItems[$item['id']];
            }
        }

        // Second pass: build tree structure (supports nested subitems)
        foreach ($allItems as $item) {
            if ($item['type'] !== 'root' && isset($item['parent_id']) && isset($allItems[$item['parent_id']])) {
                $allItems[$item['parent_id']]['children'][] = &$allItems[$item['id']];
            }
        }

        return $roots;
    }

    public function getById(int $id, string $locale = 'en'): ?array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT i.id, i.parent_id, i.type, i.sort_order, i.icon, t.title, t.content 
            FROM items i 
            LEFT JOIN translations t ON t.item_id = i.id AND t.locale = :locale
            WHERE i.id = :id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':locale' => $locale]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getWithTranslations(int $id): ?array {
        $pdo = Database::getConnection();
        
        // Fetch Item
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $item = $stmt->fetch();
        
        if (!$item) return null;

        // Fetch Translations
        $stmt = $pdo->prepare("SELECT * FROM translations WHERE item_id = :id");
        $stmt->execute([':id' => $id]);
        $translations = $stmt->fetchAll();

        foreach ($translations as $t) {
            $item['translations'][$t['locale']] = $t;
        }

        return $item;
    }

    public function update(int $id, array $data): void {
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            // Update Item info
            $stmt = $pdo->prepare("UPDATE items SET parent_id = :parent_id, type = :type, icon = :icon WHERE id = :id");
            $stmt->execute([
                ':parent_id' => $data['parent_id'] ?: null,
                ':type' => $data['type'],
                ':icon' => $data['icon'] ?? null,
                ':id' => $id
            ]);

            // Upsert Translations
            $this->upsertTranslation($id, 'en', $data['title_en'], $data['content_en']);
            $this->upsertTranslation($id, 'ar', $data['title_ar'], $data['content_ar']);

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function upsertTranslation($itemId, $locale, $title, $content) {
        $pdo = Database::getConnection();
        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM translations WHERE item_id = :itemId AND locale = :locale");
        $stmt->execute([':itemId' => $itemId, ':locale' => $locale]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE translations SET title = :title, content = :content WHERE item_id = :itemId AND locale = :locale");
        } else {
            $stmt = $pdo->prepare("INSERT INTO translations (item_id, locale, title, content) VALUES (:itemId, :locale, :title, :content)");
        }
        
        $stmt->execute([
            ':itemId' => $itemId,
            ':locale' => $locale,
            ':title' => $title,
            ':content' => $content
        ]);
    }

    public function search(string $query, string $locale): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT t.item_id, t.title, SUBSTR(t.content, 1, 150) as snippet 
            FROM translations t
            WHERE t.locale = :locale 
            AND (t.title LIKE :query OR t.content LIKE :query)
            LIMIT 10
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':locale' => $locale,
            ':query' => "%$query%"
        ]);
        return $stmt->fetchAll();
    }

    public function delete(int $id): void {
        $pdo = Database::getConnection();
        // Since we enabled ON DELETE CASCADE in Database.php, this will automatically
        // delete translations and children if they are set up correctly.
        // However, self-referential cascade (children) might need explicit handling or DB toggle.
        // In our schema: FOREIGN KEY(parent_id) REFERENCES items(id) ON DELETE CASCADE
        // So hitting the parent should kill the children.
        
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
