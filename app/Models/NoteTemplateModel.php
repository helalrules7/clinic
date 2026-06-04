<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class NoteTemplateModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * List all templates for a user, optionally filtered by category or search query.
     * Ordered by category, sort_order, then title.
     *
     * @param int $userId
     * @param array $filters optional ['category' => string, 'q' => string]
     * @return array
     */
    public function list($userId, array $filters = [])
    {
        $sql = "SELECT id, user_id, title, body, category, sort_order, use_count, last_used_at, created_at, updated_at
                FROM note_templates
                WHERE user_id = :uid";
        $params = [':uid' => (int)$userId];

        if (!empty($filters['category'])) {
            $sql .= " AND category = :cat";
            $params[':cat'] = (string)$filters['category'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (title LIKE :q OR body LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $sql .= " ORDER BY
                    CASE WHEN category IS NULL OR category = '' THEN 1 ELSE 0 END,
                    category ASC,
                    sort_order ASC,
                    title ASC,
                    id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Return templates grouped by category.
     * Shape: [ { category: string, items: [ ...templates ] }, ... ]
     *
     * @param int $userId
     * @return array
     */
    public function listGrouped($userId)
    {
        $rows = $this->list($userId);
        $groups = [];
        $order  = [];

        foreach ($rows as $row) {
            $cat = (isset($row['category']) && $row['category'] !== '' && $row['category'] !== null)
                ? $row['category']
                : 'General';
            if (!isset($groups[$cat])) {
                $groups[$cat] = [];
                $order[]      = $cat;
            }
            $groups[$cat][] = $row;
        }

        $out = [];
        foreach ($order as $cat) {
            $out[] = [
                'category' => $cat,
                'items'    => $groups[$cat],
            ];
        }
        return $out;
    }

    /**
     * Find a single template by id, scoped to the user.
     *
     * @param int $id
     * @param int $userId
     * @return array|null
     */
    public function findById($id, $userId)
    {
        $sql = "SELECT id, user_id, title, body, category, sort_order, use_count, last_used_at, created_at, updated_at
                FROM note_templates
                WHERE id = :id AND user_id = :uid
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'  => (int)$id,
            ':uid' => (int)$userId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create a new template for a user.
     *
     * @param int $userId
     * @param array $data ['title', 'body', 'category', 'sort_order']
     * @return int|false inserted id or false
     */
    public function create($userId, array $data)
    {
        $title    = isset($data['title']) ? trim((string)$data['title']) : '';
        $body     = isset($data['body']) ? (string)$data['body'] : '';
        $category = isset($data['category']) && $data['category'] !== ''
            ? (string)$data['category']
            : null;

        if ($title === '') {
            return false;
        }

        // Determine sort_order: respect provided value, else append to end of (user, category) bucket.
        if (isset($data['sort_order']) && $data['sort_order'] !== '' && $data['sort_order'] !== null) {
            $sortOrder = (int)$data['sort_order'];
        } else {
            $sortStmt = $this->pdo->prepare(
                "SELECT COALESCE(MAX(sort_order), 0) + 1
                 FROM note_templates
                 WHERE user_id = :uid AND (category <=> :cat)"
            );
            // Use null-safe operator <=> for portability; fall back if driver complains.
            try {
                $sortStmt->execute([
                    ':uid' => (int)$userId,
                    ':cat' => $category,
                ]);
                $sortOrder = (int)$sortStmt->fetchColumn();
            } catch (\Throwable $e) {
                // Fallback: simple max for the user
                $alt = $this->pdo->prepare(
                    "SELECT COALESCE(MAX(sort_order), 0) + 1 FROM note_templates WHERE user_id = :uid"
                );
                $alt->execute([':uid' => (int)$userId]);
                $sortOrder = (int)$alt->fetchColumn();
            }
        }

        $sql = "INSERT INTO note_templates
                    (user_id, title, body, category, sort_order, use_count, created_at, updated_at)
                VALUES
                    (:uid, :title, :body, :cat, :sort, 0, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([
            ':uid'   => (int)$userId,
            ':title' => $title,
            ':body'  => $body,
            ':cat'   => $category,
            ':sort'  => $sortOrder,
        ]);
        if (!$ok) {
            return false;
        }
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update a template (scoped to user). Only provided fields are updated.
     *
     * @param int $id
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function update($id, $userId, array $data)
    {
        $allowed = ['title', 'body', 'category', 'sort_order'];
        $sets    = [];
        $params  = [
            ':id'  => (int)$id,
            ':uid' => (int)$userId,
        ];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $value = $data[$field];
            switch ($field) {
                case 'title':
                    $value = trim((string)$value);
                    if ($value === '') {
                        // Refuse to wipe required title.
                        return false;
                    }
                    break;
                case 'body':
                    $value = (string)$value;
                    break;
                case 'category':
                    $value = ($value === '' || $value === null) ? null : (string)$value;
                    break;
                case 'sort_order':
                    $value = (int)$value;
                    break;
            }
            $sets[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        if (empty($sets)) {
            return true; // nothing to update
        }

        $sets[] = "updated_at = NOW()";
        $sql = "UPDATE note_templates SET " . implode(', ', $sets)
             . " WHERE id = :id AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a template (scoped to user).
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function delete($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM note_templates WHERE id = :id AND user_id = :uid"
        );
        return $stmt->execute([
            ':id'  => (int)$id,
            ':uid' => (int)$userId,
        ]);
    }

    /**
     * Reorder templates by setting sort_order to match the position in $ids.
     * Only ids belonging to the user are touched.
     *
     * @param int $userId
     * @param int[] $ids
     * @return bool
     */
    public function reorder($userId, array $ids)
    {
        if (empty($ids)) {
            return true;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "UPDATE note_templates
                 SET sort_order = :sort, updated_at = NOW()
                 WHERE id = :id AND user_id = :uid"
            );
            $position = 1;
            foreach ($ids as $id) {
                $stmt->execute([
                    ':sort' => $position,
                    ':id'   => (int)$id,
                    ':uid'  => (int)$userId,
                ]);
                $position++;
            }
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    /**
     * Mark a template as used: increment use_count and stamp last_used_at.
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function markUsed($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE note_templates
             SET use_count = use_count + 1,
                 last_used_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id AND user_id = :uid"
        );
        return $stmt->execute([
            ':id'  => (int)$id,
            ':uid' => (int)$userId,
        ]);
    }

    /**
     * Seed a starter pack of templates for a user if they have none.
     * Returns true if defaults were inserted, false if user already has templates.
     *
     * @param int $userId
     * @return bool seeded
     */
    public function seedDefaults($userId)
    {
        $userId = (int)$userId;

        $check = $this->pdo->prepare(
            "SELECT COUNT(*) FROM note_templates WHERE user_id = :uid"
        );
        $check->execute([':uid' => $userId]);
        if ((int)$check->fetchColumn() > 0) {
            return false;
        }

        $defaults = [
            // Consultation
            [
                'title'    => 'Initial Consultation',
                'category' => 'Consultation',
                'body'     => "Chief complaint:\n\nHistory of present illness:\n\nPast medical history:\n\nMedications:\n\nAllergies:\n\nExamination:\n\nAssessment:\n\nPlan:",
            ],
            [
                'title'    => 'Follow-up Visit',
                'category' => 'Consultation',
                'body'     => "Interval history:\n\nCurrent symptoms:\n\nResponse to treatment:\n\nExamination:\n\nAssessment:\n\nPlan:",
            ],
            // Examination
            [
                'title'    => 'General Examination',
                'category' => 'Examination',
                'body'     => "Vitals: BP __ / __ , HR __ , Temp __ , SpO2 __\nGeneral appearance:\nHEENT:\nCardiovascular:\nRespiratory:\nAbdomen:\nNeurological:\nMusculoskeletal:",
            ],
            // Prescription
            [
                'title'    => 'Prescription Note',
                'category' => 'Prescription',
                'body'     => "Rx:\n1. \n2. \n3. \n\nInstructions:\n\nDuration:\n\nFollow-up:",
            ],
            // Plan
            [
                'title'    => 'Treatment Plan',
                'category' => 'Plan',
                'body'     => "Diagnosis:\n\nGoals:\n\nInterventions:\n\nMedications:\n\nLifestyle advice:\n\nNext review:",
            ],
            // Referral
            [
                'title'    => 'Referral Letter',
                'category' => 'Referral',
                'body'     => "Dear Colleague,\n\nI am referring this patient for your kind opinion and management.\n\nClinical summary:\n\nInvestigations done:\n\nCurrent medications:\n\nReason for referral:\n\nThank you.",
            ],
            // Procedure
            [
                'title'    => 'Procedure Note',
                'category' => 'Procedure',
                'body'     => "Procedure:\nIndication:\nConsent: obtained\nAnaesthesia/Analgesia:\nFindings:\nSteps:\nComplications: none\nPost-procedure plan:",
            ],
        ];

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(
                "INSERT INTO note_templates
                    (user_id, title, body, category, sort_order, use_count, created_at, updated_at)
                 VALUES
                    (:uid, :title, :body, :cat, :sort, 0, NOW(), NOW())"
            );

            // Track per-category sort order so each category starts at 1.
            $perCat = [];
            foreach ($defaults as $tpl) {
                $cat = $tpl['category'];
                if (!isset($perCat[$cat])) {
                    $perCat[$cat] = 1;
                } else {
                    $perCat[$cat]++;
                }
                $stmt->execute([
                    ':uid'   => $userId,
                    ':title' => $tpl['title'],
                    ':body'  => $tpl['body'],
                    ':cat'   => $cat,
                    ':sort'  => $perCat[$cat],
                ]);
            }
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }
}
