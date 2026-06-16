<?php

namespace App\Services;

use App\Config\Constants;
use App\Config\Database;
use PDO;

/**
 * Aggregates a patient's full clinical record for PDF export (§2.17).
 */
class PatientMedicalRecordService
{
    private PDO $pdo;
    private IOPTrendAnalyzerService $iopAnalyzer;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
        $this->iopAnalyzer = new IOPTrendAnalyzerService();
    }

    public function aggregate(int $patientId, array $exportedBy = []): ?array
    {
        $patient = $this->getPatientRow($patientId);
        if (!$patient) {
            return null;
        }

        $appointments = $this->getAppointmentsWithDetails($patientId);
        $medicalHistory = $this->getMedicalHistory($patientId);
        $notes = $this->getPatientNotes($patientId);
        $files = array_map(fn (array $f) => $this->enrichPatientFileMeta($f), $this->getAllPatientFiles($patientId));
        $attachments = array_map(fn (array $a) => $this->enrichAttachmentMeta($a), $this->getAllPatientAttachments($patientId));
        $alerts = $this->getAlerts($patientId);
        $boardComments = $this->getBoardComments($patientId);
        $tags = $this->getTags($patientId);
        $colorMarker = $this->getColorMarker($patientId);
        $glassesHistory = $this->getGlassesHistory($patientId);
        $medicationRegister = $this->buildMedicationRegister($appointments);
        $iopData = $this->buildIOPData($patientId);
        $osdiHistory = $this->getOsdiHistory($patientId);
        $macularHistory = $this->getMacularHistory($patientId);
        $statistics = $this->buildStatistics($appointments, $medicationRegister, $medicalHistory);

        return [
            'meta' => [
                'exported_at' => date('c'),
                'exported_by' => $exportedBy,
                'patient_id' => $patientId,
                'version' => 'v11_medical_record_1',
            ],
            'clinic' => $this->getClinicInfo(),
            'patient' => $patient,
            'tags' => $tags,
            'color_marker' => $colorMarker,
            'medical_history' => $medicalHistory,
            'alerts' => $alerts,
            'statistics' => $statistics,
            'iop' => $iopData,
            'osdi_history' => $osdiHistory,
            'macular_history' => $macularHistory,
            'appointments' => $appointments,
            'medication_register' => $medicationRegister,
            'glasses_history' => $glassesHistory,
            'patient_notes' => $notes,
            'board_comments' => $boardComments,
            'files' => $files,
            'attachments' => $attachments,
            // Profile-only appendix (visit attachments stay under each appointment)
            'images' => $this->buildProfileImageManifest($files),
            'profile_documents' => $this->buildProfileDocumentManifest($files),
        ];
    }

    private function getPatientRow(int $patientId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*,
                   c.name_en AS clinic_name_en,
                   c.name_ar AS clinic_name_ar,
                   c.code AS clinic_code
            FROM patients p
            LEFT JOIN clinics c ON p.clinic_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['full_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $row['age'] = $this->calcAge($row['dob'] ?? null);
        return $row;
    }

    private function calcAge(?string $dob): ?int
    {
        if (!$dob) {
            return null;
        }
        try {
            $birth = new \DateTime($dob);
            $now = new \DateTime();
            return (int) $birth->diff($now)->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getClinicInfo(): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_key, setting_value FROM settings");
            $stmt->execute();
            $settings = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
                $settings[$s['setting_key']] = $s['setting_value'];
            }
            return [
                'name' => $settings['clinic_name'] ?? Constants::APP_NAME,
                'name_arabic' => $settings['clinic_name_arabic'] ?? '',
                'address' => $settings['clinic_address'] ?? '',
                'phone' => $settings['clinic_phone'] ?? '',
                'email' => $settings['clinic_email'] ?? '',
                'website' => $settings['clinic_website'] ?? '',
                'logo' => $settings['clinic_logo'] ?? '/assets/images/Light.png',
                'logo_print' => $settings['clinic_logo_print'] ?? '/assets/images/Light.png',
                'logo_watermark' => $settings['clinic_logo_watermark'] ?? '/assets/images/Light.png',
            ];
        } catch (\Exception $e) {
            return [
                'name' => Constants::APP_NAME,
                'name_arabic' => '',
                'address' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
                'logo' => '/assets/images/Light.png',
                'logo_print' => '/assets/images/Light.png',
                'logo_watermark' => '/assets/images/Light.png',
            ];
        }
    }

    private function getAppointmentsWithDetails(int $patientId): array
    {
        $columnStmt = $this->pdo->query("SHOW COLUMNS FROM appointments LIKE 'rescheduled_from'");
        $hasRescheduledFrom = $columnStmt && $columnStmt->rowCount() > 0;

        $stmt = $this->pdo->prepare("
            SELECT a.*,
                   COALESCE(d.display_name, u.name) AS doctor_name
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE a.patient_id = ?
            ORDER BY a.date DESC, a.start_time DESC
        ");
        $stmt->execute([$patientId]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($appointments as &$appt) {
            $id = (int) $appt['id'];
            $appt['is_followup'] = $hasRescheduledFrom
                && ($appt['visit_type'] ?? '') === 'FollowUp'
                && !empty($appt['rescheduled_from']);

            $medStmt = $this->pdo->prepare("SELECT * FROM prescriptions WHERE appointment_id = ? ORDER BY id ASC");
            $medStmt->execute([$id]);
            $appt['medications'] = $medStmt->fetchAll(PDO::FETCH_ASSOC);

            $gStmt = $this->pdo->prepare("SELECT * FROM glasses_prescriptions WHERE appointment_id = ? ORDER BY id ASC");
            $gStmt->execute([$id]);
            $appt['glasses'] = $gStmt->fetchAll(PDO::FETCH_ASSOC);

            $cnStmt = $this->pdo->prepare("SELECT * FROM consultation_notes WHERE appointment_id = ? ORDER BY created_at DESC LIMIT 1");
            $cnStmt->execute([$id]);
            $appt['consultation_note'] = $cnStmt->fetch(PDO::FETCH_ASSOC) ?: null;

            $attStmt = $this->pdo->prepare("
                SELECT id, filename, original_filename, file_path, mime_type, file_size, description, created_at
                FROM patient_attachments WHERE appointment_id = ? ORDER BY created_at DESC
            ");
            $attStmt->execute([$id]);
            $appt['attachments'] = array_map(
                fn (array $att) => $this->enrichAttachmentMeta($att),
                $attStmt->fetchAll(PDO::FETCH_ASSOC)
            );

            $labStmt = $this->pdo->prepare("SELECT * FROM lab_tests WHERE appointment_id = ? ORDER BY ordered_date DESC");
            $labStmt->execute([$id]);
            $appt['lab_tests'] = $labStmt->fetchAll(PDO::FETCH_ASSOC);

            $miStmt = $this->pdo->prepare("SELECT * FROM appointment_medical_instructions WHERE appointment_id = ? ORDER BY sort_order ASC, id ASC");
            $miStmt->execute([$id]);
            $appt['medical_instructions'] = $miStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($appt);

        return $appointments;
    }

    private function getMedicalHistory(int $patientId): array
    {
        $stmt = $this->pdo->prepare("SELECT *, 'legacy' AS entry_format FROM medical_history WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$patientId]);
        $legacy = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("
            SELECT mhe.*, u.name AS doctor_name, 'structured' AS entry_format
            FROM medical_history_entries mhe
            LEFT JOIN users u ON mhe.created_by = u.id
            WHERE mhe.patient_id = ?
            ORDER BY COALESCE(mhe.diagnosis_date, mhe.created_at) DESC
        ");
        $stmt->execute([$patientId]);
        $structured = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $merged = array_merge($structured, $legacy);
        usort($merged, static function ($a, $b) {
            $da = $a['diagnosis_date'] ?? $a['created_at'] ?? '';
            $db = $b['diagnosis_date'] ?? $b['created_at'] ?? '';
            return strtotime($db) <=> strtotime($da);
        });

        return $merged;
    }

    private function getPatientNotes(int $patientId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT pn.*, u.name AS doctor_name
            FROM patient_notes pn
            LEFT JOIN users u ON pn.doctor_id = u.id
            WHERE pn.patient_id = ?
            ORDER BY pn.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllPatientFiles(int $patientId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patient_files WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllPatientAttachments(int $patientId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT pa.*, a.date AS appointment_date
            FROM patient_attachments pa
            LEFT JOIN appointments a ON pa.appointment_id = a.id
            WHERE pa.patient_id = ?
            ORDER BY pa.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAlerts(int $patientId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM alerts WHERE patient_id = ? ORDER BY is_active DESC, alert_date DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getBoardComments(int $patientId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.body, c.pinned, c.created_at, u.name AS author_name
            FROM comments c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.commentable_type = 'board_card' AND c.commentable_id = ? AND c.deleted_at IS NULL
            ORDER BY c.pinned DESC, c.created_at ASC
        ");
        $stmt->execute([$patientId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$comments) {
            return [];
        }

        $attachmentsByComment = $this->getCommentAttachmentsForIds(array_column($comments, 'id'));
        foreach ($comments as &$comment) {
            $comment['attachments'] = $attachmentsByComment[(int) $comment['id']] ?? [];
        }
        unset($comment);

        return $comments;
    }

    private function getCommentAttachmentsForIds(array $commentIds): array
    {
        $commentIds = array_values(array_filter(array_map('intval', $commentIds)));
        if (!$commentIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT id, comment_id, kind, mime_type, original_name, file_size, duration_ms
            FROM comment_attachments
            WHERE comment_id IN ($placeholders)
            ORDER BY id ASC
        ");
        $stmt->execute($commentIds);

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $cid = (int) $row['comment_id'];
            $mime = $row['mime_type'] ?? '';
            $kind = $row['kind'] ?? '';
            $out[$cid][] = [
                'id' => (int) $row['id'],
                'kind' => $kind,
                'mime_type' => $mime,
                'name' => $row['original_name'] ?? 'Attachment',
                'file_size' => (int) ($row['file_size'] ?? 0),
                'duration_ms' => $row['duration_ms'] !== null ? (int) $row['duration_ms'] : null,
                'view_url' => '/api/comments/attachments/' . (int) $row['id'],
                'is_image' => $kind === 'image' || ($mime && stripos($mime, 'image/') === 0),
            ];
        }

        return $out;
    }

    private function getTags(int $patientId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT pt.name, pt.color, pt.icon
                FROM patient_tag_assignments pta
                INNER JOIN patient_tags pt ON pta.tag_id = pt.id
                WHERE pta.patient_id = ?
                ORDER BY pt.sort_order ASC, pt.name ASC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getColorMarker(int $patientId): ?string
    {
        try {
            $stmt = $this->pdo->prepare("SELECT color_code FROM patient_color_markers WHERE patient_id = ?");
            $stmt->execute([$patientId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['color_code'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getGlassesHistory(int $patientId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT g.*, a.date AS appointment_date, a.id AS appointment_id
            FROM glasses_prescriptions g
            JOIN appointments a ON g.appointment_id = a.id
            WHERE a.patient_id = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildMedicationRegister(array $appointments): array
    {
        $register = [];
        foreach ($appointments as $appt) {
            foreach ($appt['medications'] ?? [] as $med) {
                $register[] = array_merge($med, [
                    'appointment_date' => $appt['date'] ?? null,
                    'appointment_id' => $appt['id'] ?? null,
                    'doctor_name' => $appt['doctor_name'] ?? null,
                ]);
            }
        }
        return $register;
    }

    private function buildIOPData(int $patientId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT cn.IOP_right, cn.IOP_left, a.date AS measurement_date, a.id AS appointment_id
            FROM consultation_notes cn
            JOIN appointments a ON cn.appointment_id = a.id
            WHERE a.patient_id = ?
              AND (cn.IOP_right IS NOT NULL OR cn.IOP_left IS NOT NULL)
            ORDER BY a.date ASC
        ");
        $stmt->execute([$patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $readings = [];
        $series = ['OD' => [], 'OS' => []];

        foreach ($rows as $row) {
            $date = $row['measurement_date'];
            if (!empty($row['IOP_right']) && is_numeric($row['IOP_right'])) {
                $v = (float) $row['IOP_right'];
                $readings[] = ['eye' => 'OD', 'iop' => $v, 'date' => $date];
                $series['OD'][] = ['date' => $date, 'value' => $v];
            }
            if (!empty($row['IOP_left']) && is_numeric($row['IOP_left'])) {
                $v = (float) $row['IOP_left'];
                $readings[] = ['eye' => 'OS', 'iop' => $v, 'date' => $date];
                $series['OS'][] = ['date' => $date, 'value' => $v];
            }
        }

        $analysis = !empty($readings) ? $this->iopAnalyzer->analyze($readings) : ['success' => false];

        return [
            'readings' => $readings,
            'series' => $series,
            'analysis' => $analysis,
        ];
    }

    private function getOsdiHistory(int $patientId): array
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'osdi_results'");
            if (!$stmt || $stmt->rowCount() === 0) {
                return [];
            }
            $stmt = $this->pdo->prepare("
                SELECT measurement_date, osdi_score, severity, created_at
                FROM osdi_results WHERE patient_id = ?
                ORDER BY measurement_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMacularHistory(int $patientId): array
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'macular_thickness_results'");
            if (!$stmt || $stmt->rowCount() === 0) {
                return [];
            }
            $stmt = $this->pdo->prepare("
                SELECT measurement_date, central_macular_thickness, eye, created_at
                FROM macular_thickness_results WHERE patient_id = ?
                ORDER BY measurement_date DESC
            ");
            $stmt->execute([$patientId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function buildStatistics(array $appointments, array $medRegister, array $history): array
    {
        $statusCounts = [];
        $monthCounts = [];
        $diagnosisCounts = [];
        $visitTypes = [];

        foreach ($appointments as $appt) {
            $st = $appt['status'] ?? 'Unknown';
            $statusCounts[$st] = ($statusCounts[$st] ?? 0) + 1;

            $vt = $appt['visit_type'] ?? 'Unknown';
            $visitTypes[$vt] = ($visitTypes[$vt] ?? 0) + 1;

            if (!empty($appt['date'])) {
                $mk = substr($appt['date'], 0, 7);
                $monthCounts[$mk] = ($monthCounts[$mk] ?? 0) + 1;
            }

            $dx = trim($appt['consultation_note']['diagnosis'] ?? '');
            if ($dx !== '') {
                $diagnosisCounts[$dx] = ($diagnosisCounts[$dx] ?? 0) + 1;
            }
        }

        arsort($diagnosisCounts);
        arsort($monthCounts);

        $drugCounts = [];
        foreach ($medRegister as $m) {
            $dn = trim($m['drug_name'] ?? '');
            if ($dn !== '') {
                $drugCounts[$dn] = ($drugCounts[$dn] ?? 0) + 1;
            }
        }
        arsort($drugCounts);

        return [
            'total_appointments' => count($appointments),
            'total_medications' => count($medRegister),
            'total_history_entries' => count($history),
            'status_breakdown' => $statusCounts,
            'visit_type_breakdown' => $visitTypes,
            'visits_by_month' => $monthCounts,
            'top_diagnoses' => array_slice($diagnosisCounts, 0, 15, true),
            'top_medications' => array_slice($drugCounts, 0, 15, true),
        ];
    }

    private function enrichAttachmentMeta(array $item): array
    {
        $item['is_image'] = $this->isImageItem($item);
        $item['view_url'] = !empty($item['id'])
            ? '/api/attachments/view/' . (int) $item['id']
            : null;
        return $item;
    }

    private function enrichPatientFileMeta(array $item): array
    {
        $item['is_image'] = $this->isImageItem($item);
        $item['view_url'] = !empty($item['id'])
            ? '/api/patients/files/view/' . (int) $item['id']
            : null;
        return $item;
    }

    private function isImageItem(array $item): bool
    {
        $mime = $item['mime_type'] ?? $item['file_type'] ?? '';
        if ($mime && stripos($mime, 'image/') === 0) {
            return true;
        }
        $name = $item['original_filename'] ?? $item['filename'] ?? $item['original_name'] ?? $item['file_path'] ?? '';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    }

    private function buildProfileImageManifest(array $files): array
    {
        $images = [];
        foreach ($files as $file) {
            if (!$this->isImageItem($file) || empty($file['id'])) {
                continue;
            }
            $path = $file['file_path'] ?? '';
            $images[] = [
                'id' => (int) $file['id'],
                'source' => 'patient_file',
                'url' => '/api/patients/files/view/' . (int) $file['id'],
                'name' => $file['original_filename'] ?? ($path ? basename($path) : 'Image'),
                'date' => $file['created_at'] ?? null,
                'description' => $file['description'] ?? null,
            ];
        }

        return $images;
    }

    private function buildProfileDocumentManifest(array $files): array
    {
        $documents = [];
        foreach ($files as $file) {
            if ($this->isImageItem($file) || empty($file['id'])) {
                continue;
            }
            $path = $file['file_path'] ?? '';
            $documents[] = [
                'id' => (int) $file['id'],
                'source' => 'patient_file',
                'url' => '/api/patients/files/view/' . (int) $file['id'],
                'name' => $file['original_filename'] ?? ($path ? basename($path) : 'Document'),
                'date' => $file['created_at'] ?? null,
                'description' => $file['description'] ?? null,
                'file_type' => $file['file_type'] ?? null,
            ];
        }

        return $documents;
    }
}
