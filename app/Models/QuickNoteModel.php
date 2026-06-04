<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class QuickNoteModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * List quick notes for a user.
     * Pinned notes first, then most recently updated.
     *
     * @param int        $userId
     * @param array      $opts  Optional filters: ['search' => string, 'pinned' => bool, 'limit' => int, 'offset' => int]
     * @return array
     */
    public function list($userId, array $opts = [])
    {
        $userId = (int)$userId;
        $where  = ['user_id = :user_id'];
        $params = [':user_id' => $userId];

        if (!empty($opts['search'])) {
            $where[] = '(title LIKE :search OR body LIKE :search)';
            $params[':search'] = '%' . $opts['search'] . '%';
        }

        if (array_key_exists('pinned', $opts) && $opts['pinned'] !== null) {
            $where[] = 'pinned = :pinned';
            $params[':pinned'] = !empty($opts['pinned']) ? 1 : 0;
        }

        $sql = 'SELECT id, user_id, title, body, pinned, created_at, updated_at
                FROM quick_notes
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY pinned DESC, COALESCE(updated_at, created_at) DESC, id DESC';

        $limit  = isset($opts['limit'])  ? (int)$opts['limit']  : 0;
        $offset = isset($opts['offset']) ? (int)$opts['offset'] : 0;
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
            if ($offset > 0) {
                $sql .= ' OFFSET ' . $offset;
            }
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            if ($k === ':user_id' || $k === ':pinned') {
                $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $row['id']     = (int)$row['id'];
            $row['user_id'] = (int)$row['user_id'];
            $row['pinned'] = (int)$row['pinned'] ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    /**
     * Find a single quick note by id, scoped to user.
     *
     * @param int $id
     * @param int $userId
     * @return array|null
     */
    public function findById($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, title, body, pinned, created_at, updated_at
             FROM quick_notes
             WHERE id = :id AND user_id = :user_id
             LIMIT 1'
        );
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['id']      = (int)$row['id'];
        $row['user_id'] = (int)$row['user_id'];
        $row['pinned']  = (int)$row['pinned'] ? 1 : 0;
        return $row;
    }

    /**
     * Create a new quick note for the user.
     * Returns the created row or null on failure.
     *
     * @param int   $userId
     * @param array $data  Keys: title, body, pinned
     * @return array|null
     */
    public function create($userId, array $data)
    {
        $title  = isset($data['title']) ? trim((string)$data['title']) : '';
        $body   = isset($data['body'])  ? (string)$data['body']        : '';
        $pinned = !empty($data['pinned']) ? 1 : 0;

        // Allow empty title if body is present (acts like a sticky note).
        if ($title === '' && trim($body) === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO quick_notes (user_id, title, body, pinned, created_at, updated_at)
             VALUES (:user_id, :title, :body, :pinned, NOW(), NOW())'
        );
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':title',   $title,       PDO::PARAM_STR);
        $stmt->bindValue(':body',    $body,        PDO::PARAM_STR);
        $stmt->bindValue(':pinned',  $pinned,      PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return null;
        }

        $id = (int)$this->pdo->lastInsertId();
        return $this->findById($id, $userId);
    }

    /**
     * Update an existing quick note. Only the fields present in $data are touched.
     * Returns the updated row, or null if not found / no change applied.
     *
     * @param int   $id
     * @param int   $userId
     * @param array $data  Allowed keys: title, body, pinned
     * @return array|null
     */
    public function update($id, $userId, array $data)
    {
        $existing = $this->findById($id, $userId);
        if (!$existing) {
            return null;
        }

        $sets   = [];
        $params = [
            ':id'      => (int)$id,
            ':user_id' => (int)$userId,
        ];

        if (array_key_exists('title', $data)) {
            $sets[] = 'title = :title';
            $params[':title'] = trim((string)$data['title']);
        }
        if (array_key_exists('body', $data)) {
            $sets[] = 'body = :body';
            $params[':body'] = (string)$data['body'];
        }
        if (array_key_exists('pinned', $data)) {
            $sets[] = 'pinned = :pinned';
            $params[':pinned'] = !empty($data['pinned']) ? 1 : 0;
        }

        if (empty($sets)) {
            return $existing;
        }

        $sets[] = 'updated_at = NOW()';

        $sql  = 'UPDATE quick_notes SET ' . implode(', ', $sets) .
                ' WHERE id = :id AND user_id = :user_id';
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $k => $v) {
            if (in_array($k, [':id', ':user_id', ':pinned'], true)) {
                $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }

        if (!$stmt->execute()) {
            return null;
        }

        return $this->findById($id, $userId);
    }

    /**
     * Delete a quick note. Returns true on success, false if not found / no rows.
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function delete($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM quick_notes WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->bindValue(':id',      (int)$id,     PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Set or unset the pinned flag on a quick note.
     * Returns the updated row, or null if note not found.
     *
     * @param int  $id
     * @param int  $userId
     * @param bool $pinned
     * @return array|null
     */
    public function setPinned($id, $userId, $pinned)
    {
        $existing = $this->findById($id, $userId);
        if (!$existing) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE quick_notes
             SET pinned = :pinned, updated_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->bindValue(':pinned',  !empty($pinned) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id',      (int)$id,                PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int)$userId,            PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return null;
        }

        return $this->findById($id, $userId);
    }
}
