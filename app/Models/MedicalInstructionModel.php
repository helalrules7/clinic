<?php
namespace App\Models;

use PDO;

class MedicalInstructionModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ── Templates ─────────────────────────────────────────────── */

    public function listTemplatesForUser(int $userId): array
    {
        $stmt = $this->pdo->query("
            SELECT *
            FROM medical_instruction_templates
            WHERE is_active = 1
              AND user_id IS NULL
            ORDER BY sort_order ASC, title ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findTemplate(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM medical_instruction_templates
            WHERE id = :id AND is_active = 1
              AND user_id IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findClinicTemplate(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM medical_instruction_templates
            WHERE id = :id AND user_id IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createTemplate(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO medical_instruction_templates
                (user_id, title, body_ar, body_en, category, diagnosis_keywords,
                 icd_code, sort_order, use_count, is_active, created_at, updated_at)
            VALUES
                (:user_id, :title, :body_ar, :body_en, :category, :diagnosis_keywords,
                 :icd_code, :sort_order, 0, 1, NOW(), NOW())
        ");
        $stmt->execute([
            ':user_id'             => $data['user_id'],
            ':title'               => $data['title'],
            ':body_ar'             => $data['body_ar'],
            ':body_en'             => $data['body_en'],
            ':category'            => $data['category'],
            ':diagnosis_keywords'  => $data['diagnosis_keywords'],
            ':icd_code'            => $data['icd_code'],
            ':sort_order'          => $data['sort_order'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateTemplate(int $id, array $data): bool
    {
        $sets = [];
        $params = [':id' => $id];
        foreach (['title', 'body_ar', 'body_en', 'category', 'diagnosis_keywords', 'icd_code', 'sort_order'] as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }
        if (empty($sets)) {
            return false;
        }
        $sql = 'UPDATE medical_instruction_templates SET ' . implode(', ', $sets)
             . ', updated_at = NOW() WHERE id = :id AND user_id IS NULL';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function deleteTemplate(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE medical_instruction_templates
            SET is_active = 0, updated_at = NOW()
            WHERE id = :id AND user_id IS NULL
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function incrementTemplateUse(int $id): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE medical_instruction_templates
            SET use_count = use_count + 1, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    /* ── Appointment instructions ──────────────────────────────── */

    public function listForAppointment(int $appointmentId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM appointment_medical_instructions
            WHERE appointment_id = :aid
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([':aid' => $appointmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findAppointmentInstruction(int $id, int $appointmentId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM appointment_medical_instructions
            WHERE id = :id AND appointment_id = :aid
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':aid' => $appointmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createAppointmentInstruction(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO appointment_medical_instructions
                (appointment_id, template_id, title, body_ar, body_en, source,
                 sort_order, created_by, created_at, updated_at)
            VALUES
                (:appointment_id, :template_id, :title, :body_ar, :body_en, :source,
                 :sort_order, :created_by, NOW(), NOW())
        ");
        $stmt->execute([
            ':appointment_id' => $data['appointment_id'],
            ':template_id'    => $data['template_id'],
            ':title'          => $data['title'],
            ':body_ar'        => $data['body_ar'],
            ':body_en'        => $data['body_en'],
            ':source'         => $data['source'],
            ':sort_order'     => $data['sort_order'],
            ':created_by'     => $data['created_by'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateAppointmentInstruction(int $id, int $appointmentId, array $data): bool
    {
        $sets = [];
        $params = [':id' => $id, ':aid' => $appointmentId];
        foreach (['title', 'body_ar', 'body_en', 'sort_order'] as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }
        if (empty($sets)) {
            return false;
        }
        $sql = 'UPDATE appointment_medical_instructions SET ' . implode(', ', $sets)
             . ', updated_at = NOW() WHERE id = :id AND appointment_id = :aid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function deleteAppointmentInstruction(int $id, int $appointmentId): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM appointment_medical_instructions
            WHERE id = :id AND appointment_id = :aid
        ");
        $stmt->execute([':id' => $id, ':aid' => $appointmentId]);
        return $stmt->rowCount() > 0;
    }

    public function nextSortOrder(int $appointmentId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(MAX(sort_order), -1) + 1 AS n
            FROM appointment_medical_instructions
            WHERE appointment_id = :aid
        ");
        $stmt->execute([':aid' => $appointmentId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    /* ── Diagnosis matching ────────────────────────────────────── */

    public static function normalizeText(string $text): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $text)));
    }

    public static function diagnosisMatchesTemplate(array $template, string $diagnosis): bool
    {
        $diagnosis = trim($diagnosis);
        if ($diagnosis === '') {
            return false;
        }

        $normDx = self::normalizeText($diagnosis);

        if (!empty($template['icd_code'])) {
            $icd = self::normalizeText((string) $template['icd_code']);
            if ($icd !== '' && (mb_strpos($normDx, $icd) !== false || $normDx === $icd)) {
                return true;
            }
        }

        $keywords = trim((string) ($template['diagnosis_keywords'] ?? ''));
        if ($keywords !== '') {
            foreach (preg_split('/[,،;]+/u', $keywords) as $kw) {
                $kw = self::normalizeText($kw);
                if ($kw === '') {
                    continue;
                }
                if (mb_strpos($normDx, $kw) !== false) {
                    return true;
                }
                similar_text($normDx, $kw, $pct);
                if ($pct >= 85) {
                    return true;
                }
            }
        }

        $title = self::normalizeText((string) ($template['title'] ?? ''));
        if ($title !== '') {
            similar_text($normDx, $title, $pctTitle);
            if ($pctTitle >= 85) {
                return true;
            }
        }

        return false;
    }

    public function suggestTemplates(int $userId, array $diagnoses): array
    {
        $templates = $this->listTemplatesForUser($userId);
        $seen = [];
        $out = [];

        foreach ($diagnoses as $diagInfo) {
            $dx = trim((string) ($diagInfo['diagnosis'] ?? ''));
            if ($dx === '') {
                continue;
            }
            $source = $diagInfo['source'] ?? 'diagnosis';
            foreach ($templates as $tpl) {
                $tid = (int) $tpl['id'];
                if (isset($seen[$tid])) {
                    continue;
                }
                if (self::diagnosisMatchesTemplate($tpl, $dx)) {
                    $seen[$tid] = true;
                    $out[] = array_merge($tpl, [
                        'match_source' => $source,
                        'match_diagnosis' => $dx,
                    ]);
                }
            }
        }

        usort($out, static function ($a, $b) {
            $aCurrent = ($a['match_source'] ?? '') === 'current' ? 0 : 1;
            $bCurrent = ($b['match_source'] ?? '') === 'current' ? 0 : 1;
            if ($aCurrent !== $bCurrent) {
                return $aCurrent - $bCurrent;
            }
            return ((int) ($b['use_count'] ?? 0)) - ((int) ($a['use_count'] ?? 0));
        });

        return $out;
    }
}
