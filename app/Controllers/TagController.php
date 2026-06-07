<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Config\Database;

/**
 * Tags v2 API — patient tag reports, appointment tags, session labels, drug links.
 */
class TagController
{
    private Auth $auth;
    private \PDO $pdo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->auth = new Auth();
        $this->pdo = Database::getInstance()->getConnection();
        ini_set('display_errors', 0);
        error_reporting(E_ERROR | E_PARSE);
    }

    /* ── Patient tag reports ───────────────────────────────────── */

    /** GET /api/patient-tags/reports */
    public function getPatientTagReports(): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);

            $stmt = $this->pdo->prepare("
                SELECT pt.id, pt.name, pt.color, pt.icon, pt.doctor_id, pt.sort_order,
                       COUNT(DISTINCT pta.patient_id) AS patient_count
                FROM patient_tags pt
                LEFT JOIN patient_tag_assignments pta ON pta.tag_id = pt.id
                WHERE pt.doctor_id IS NULL OR pt.doctor_id = ?
                GROUP BY pt.id
                ORDER BY pt.sort_order ASC, pt.name ASC
            ");
            $stmt->execute([$doctorId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->json(['ok' => true, 'reports' => $rows]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** GET /api/patient-tags/{id}/patients */
    public function getPatientsByTag($id): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $tagId = (int)$id;
            if ($tagId <= 0) {
                $this->json(['error' => 'Invalid tag ID'], 400);
                return;
            }

            $stmt = $this->pdo->prepare("
                SELECT p.id, p.first_name, p.last_name, pta.assigned_at
                FROM patient_tag_assignments pta
                INNER JOIN patients p ON p.id = pta.patient_id
                WHERE pta.tag_id = ?
                ORDER BY pta.assigned_at DESC, p.last_name ASC, p.first_name ASC
            ");
            $stmt->execute([$tagId]);
            $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->json(['ok' => true, 'patients' => $patients]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ── Appointment tags CRUD ─────────────────────────────────── */

    /** GET /api/appointment-tags */
    public function getAppointmentTags(): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);

            $stmt = $this->pdo->prepare("
                SELECT id, name, color, icon, doctor_id, sort_order, created_at, updated_at
                FROM appointment_tags
                WHERE doctor_id IS NULL OR doctor_id = ?
                ORDER BY sort_order ASC, name ASC
            ");
            $stmt->execute([$doctorId]);
            $this->json(['ok' => true, 'tags' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** POST /api/appointment-tags */
    public function createAppointmentTag(): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];

            $name = trim($data['name'] ?? '');
            $color = trim($data['color'] ?? '#6366f1');
            $icon = trim($data['icon'] ?? 'bi-tag');
            $sortOrder = (int)($data['sort_order'] ?? 0);

            if ($name === '') {
                $this->json(['error' => 'Tag name is required'], 400);
                return;
            }
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                $this->json(['error' => 'Invalid color code'], 400);
                return;
            }

            $finalDoctorId = $this->resolveTagDoctorId($data, $doctorId);
            if ($this->tagNameExists('appointment_tags', $name, $finalDoctorId)) {
                $this->json(['error' => 'Tag with this name already exists'], 400);
                return;
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO appointment_tags (name, color, icon, doctor_id, sort_order)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $color, $icon, $finalDoctorId, $sortOrder]);
            $tag = $this->fetchAppointmentTag((int)$this->pdo->lastInsertId());

            $this->json(['ok' => true, 'message' => 'Tag created', 'tag' => $tag]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** PUT /api/appointment-tags/{id} */
    public function updateAppointmentTag($id): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);
            $tagId = (int)$id;
            $tag = $this->fetchAppointmentTagRow($tagId);
            if (!$tag) {
                $this->json(['error' => 'Tag not found'], 404);
                return;
            }
            if (!$this->canManageTag($tag['doctor_id'], $doctorId)) {
                $this->json(['error' => 'Unauthorized'], 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $updates = [];
            $params = [];

            if (!empty(trim($data['name'] ?? ''))) {
                $name = trim($data['name']);
                if ($this->tagNameExists('appointment_tags', $name, $tag['doctor_id'], $tagId)) {
                    $this->json(['error' => 'Tag name already exists'], 400);
                    return;
                }
                $updates[] = 'name = ?';
                $params[] = $name;
            }
            if (!empty($data['color'] ?? '')) {
                $color = trim($data['color']);
                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                    $this->json(['error' => 'Invalid color'], 400);
                    return;
                }
                $updates[] = 'color = ?';
                $params[] = $color;
            }
            if (isset($data['icon'])) {
                $updates[] = 'icon = ?';
                $params[] = trim($data['icon']);
            }
            if (isset($data['sort_order'])) {
                $updates[] = 'sort_order = ?';
                $params[] = (int)$data['sort_order'];
            }

            if (empty($updates)) {
                $this->json(['error' => 'No fields to update'], 400);
                return;
            }

            $params[] = $tagId;
            $this->pdo->prepare('UPDATE appointment_tags SET ' . implode(', ', $updates) . ' WHERE id = ?')->execute($params);

            $this->json(['ok' => true, 'tag' => $this->fetchAppointmentTag($tagId)]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** DELETE /api/appointment-tags/{id} */
    public function deleteAppointmentTag($id): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);
            $tagId = (int)$id;
            $tag = $this->fetchAppointmentTagRow($tagId);
            if (!$tag) {
                $this->json(['error' => 'Tag not found'], 404);
                return;
            }
            if (!$this->canManageTag($tag['doctor_id'], $doctorId)) {
                $this->json(['error' => 'Unauthorized'], 403);
                return;
            }

            $this->pdo->prepare('DELETE FROM appointment_tags WHERE id = ?')->execute([$tagId]);
            $this->json(['ok' => true, 'message' => 'Tag deleted']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ── Appointment tag assignments ───────────────────────────── */

    /** GET /api/appointments/{id}/tags */
    public function getAppointmentAssignedTags($appointmentId): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $appointmentId = (int)$appointmentId;
            if (!$this->appointmentExists($appointmentId)) {
                $this->json(['error' => 'Appointment not found'], 404);
                return;
            }

            $stmt = $this->pdo->prepare("
                SELECT at.id, at.name, at.color, at.icon, at.doctor_id, ata.assigned_at
                FROM appointment_tag_assignments ata
                INNER JOIN appointment_tags at ON at.id = ata.tag_id
                WHERE ata.appointment_id = ?
                ORDER BY at.sort_order ASC, at.name ASC
            ");
            $stmt->execute([$appointmentId]);
            $this->json(['ok' => true, 'tags' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** POST /api/appointments/{appointment_id}/tags/{tag_id} */
    public function assignAppointmentTag($appointmentId, $tagId): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $appointmentId = (int)$appointmentId;
            $tagId = (int)$tagId;
            if (!$this->appointmentExists($appointmentId)) {
                $this->json(['error' => 'Appointment not found'], 404);
                return;
            }
            if (!$this->fetchAppointmentTagRow($tagId)) {
                $this->json(['error' => 'Tag not found'], 404);
                return;
            }

            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO appointment_tag_assignments (appointment_id, tag_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$appointmentId, $tagId]);

            $this->json(['ok' => true, 'message' => 'Tag assigned']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** DELETE /api/appointments/{appointment_id}/tags/{tag_id} */
    public function removeAppointmentTag($appointmentId, $tagId): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $this->pdo->prepare("
                DELETE FROM appointment_tag_assignments WHERE appointment_id = ? AND tag_id = ?
            ")->execute([(int)$appointmentId, (int)$tagId]);

            $this->json(['ok' => true, 'message' => 'Tag removed']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ── Session labels ────────────────────────────────────────── */

    /** GET /api/appointments/{id}/session-labels */
    public function getSessionLabels($appointmentId): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $appointmentId = (int)$appointmentId;

            $stmt = $this->pdo->prepare("
                SELECT id, label_text, color, created_at
                FROM appointment_session_labels
                WHERE appointment_id = ?
                ORDER BY id ASC
            ");
            $stmt->execute([$appointmentId]);
            $this->json(['ok' => true, 'labels' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** PUT /api/appointments/{id}/session-labels — replace all labels */
    public function setSessionLabels($appointmentId): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $appointmentId = (int)$appointmentId;
            if (!$this->appointmentExists($appointmentId)) {
                $this->json(['error' => 'Appointment not found'], 404);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $labels = $data['labels'] ?? [];
            if (!is_array($labels)) {
                $this->json(['error' => 'labels must be an array'], 400);
                return;
            }

            $this->pdo->beginTransaction();
            $this->pdo->prepare('DELETE FROM appointment_session_labels WHERE appointment_id = ?')->execute([$appointmentId]);

            $insert = $this->pdo->prepare("
                INSERT INTO appointment_session_labels (appointment_id, label_text, color)
                VALUES (?, ?, ?)
            ");
            foreach ($labels as $label) {
                $text = trim($label['label_text'] ?? $label['text'] ?? '');
                if ($text === '') {
                    continue;
                }
                $color = trim($label['color'] ?? '#f59e0b');
                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                    $color = '#f59e0b';
                }
                $insert->execute([$appointmentId, mb_substr($text, 0, 80), $color]);
            }
            $this->pdo->commit();

            $this->getSessionLabels($appointmentId);
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ── Drug → patient tag links ──────────────────────────────── */

    /** GET /api/drug-tag-links */
    public function getDrugTagLinks(): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);

            $stmt = $this->pdo->prepare("
                SELECT dtl.id, dtl.drug_name, dtl.patient_tag_id, dtl.doctor_id, dtl.created_at,
                       pt.name AS tag_name, pt.color AS tag_color, pt.icon AS tag_icon
                FROM drug_patient_tag_links dtl
                INNER JOIN patient_tags pt ON pt.id = dtl.patient_tag_id
                WHERE dtl.doctor_id IS NULL OR dtl.doctor_id = ?
                ORDER BY dtl.drug_name ASC, pt.name ASC
            ");
            $stmt->execute([$doctorId]);
            $this->json(['ok' => true, 'links' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** POST /api/drug-tag-links */
    public function createDrugTagLink(): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];

            $drugName = trim($data['drug_name'] ?? '');
            $patientTagId = (int)($data['patient_tag_id'] ?? 0);

            if ($drugName === '' || $patientTagId <= 0) {
                $this->json(['error' => 'drug_name and patient_tag_id are required'], 400);
                return;
            }

            $tagStmt = $this->pdo->prepare('SELECT id FROM patient_tags WHERE id = ?');
            $tagStmt->execute([$patientTagId]);
            if (!$tagStmt->fetch()) {
                $this->json(['error' => 'Patient tag not found'], 404);
                return;
            }

            $finalDoctorId = $this->resolveTagDoctorId($data, $doctorId);

            $stmt = $this->pdo->prepare("
                INSERT INTO drug_patient_tag_links (drug_name, patient_tag_id, doctor_id)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE id = id
            ");
            $stmt->execute([$drugName, $patientTagId, $finalDoctorId]);

            $this->json(['ok' => true, 'message' => 'Link saved']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** DELETE /api/drug-tag-links/{id} */
    public function deleteDrugTagLink($id): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);
            $linkId = (int)$id;

            $stmt = $this->pdo->prepare('SELECT id, doctor_id FROM drug_patient_tag_links WHERE id = ?');
            $stmt->execute([$linkId]);
            $link = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$link) {
                $this->json(['error' => 'Link not found'], 404);
                return;
            }
            if ($link['doctor_id'] !== null && (int)$link['doctor_id'] !== $doctorId) {
                $this->json(['error' => 'Unauthorized'], 403);
                return;
            }

            $this->pdo->prepare('DELETE FROM drug_patient_tag_links WHERE id = ?')->execute([$linkId]);
            $this->json(['ok' => true, 'message' => 'Link deleted']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** GET /api/drug-tag-links/suggestions?drug_name= */
    public function getDrugTagSuggestions(): void
    {
        try {
            if (!$this->auth->check()) {
                $this->json(['error' => 'Unauthorized'], 401);
                return;
            }
            $doctorId = $this->getDoctorId($this->auth->user()['id']);
            $drugName = trim($_GET['drug_name'] ?? '');

            if ($drugName === '') {
                $this->json(['ok' => true, 'tags' => []]);
                return;
            }

            $stmt = $this->pdo->prepare("
                SELECT DISTINCT pt.id, pt.name, pt.color, pt.icon, pt.doctor_id
                FROM drug_patient_tag_links dtl
                INNER JOIN patient_tags pt ON pt.id = dtl.patient_tag_id
                WHERE (dtl.doctor_id IS NULL OR dtl.doctor_id = ?)
                  AND LOWER(dtl.drug_name) = LOWER(?)
                ORDER BY pt.sort_order ASC, pt.name ASC
            ");
            $stmt->execute([$doctorId, $drugName]);
            $this->json(['ok' => true, 'tags' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ── Helpers ───────────────────────────────────────────────── */

    private function getDoctorId($userId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM doctors WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    private function resolveTagDoctorId(array $data, ?int $doctorId): ?int
    {
        if (array_key_exists('doctor_id', $data) && $data['doctor_id'] === null) {
            return null;
        }
        if (isset($data['doctor_id']) && $data['doctor_id'] !== null) {
            return (int)$data['doctor_id'];
        }
        return $doctorId;
    }

    private function canManageTag($tagDoctorId, ?int $currentDoctorId): bool
    {
        if ($tagDoctorId === null) {
            return true;
        }
        return $currentDoctorId !== null && (int)$tagDoctorId === $currentDoctorId;
    }

    private function tagNameExists(string $table, string $name, $doctorId, ?int $excludeId = null): bool
    {
        $sql = $doctorId === null
            ? "SELECT id FROM {$table} WHERE name = ? AND doctor_id IS NULL"
            : "SELECT id FROM {$table} WHERE name = ? AND doctor_id = ?";
        if ($excludeId) {
            $sql .= ' AND id != ?';
        }
        $stmt = $this->pdo->prepare($sql);
        $params = $doctorId === null ? [$name] : [$name, $doctorId];
        if ($excludeId) {
            $params[] = $excludeId;
        }
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    private function appointmentExists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM appointments WHERE id = ?');
        $stmt->execute([$id]);
        return (bool)$stmt->fetch();
    }

    private function fetchAppointmentTagRow(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM appointment_tags WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function fetchAppointmentTag(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, color, icon, doctor_id, sort_order, created_at, updated_at
            FROM appointment_tags WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
