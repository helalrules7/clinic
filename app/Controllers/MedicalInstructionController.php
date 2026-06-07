<?php
namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use App\Models\MedicalInstructionModel;
use PDO;

class MedicalInstructionController
{
    private PDO $pdo;
    private Auth $auth;
    private MedicalInstructionModel $model;

    public function __construct()
    {
        $this->pdo   = Database::getInstance()->getConnection();
        $this->auth  = new Auth();
        $this->model = new MedicalInstructionModel($this->pdo);
    }

    /* ── Page ──────────────────────────────────────────────────── */

    public function page(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $view = new \App\Lib\View();
        $content = $view->render('doctor/instruction-templates', ['user' => $user]);
        echo $view->render('layouts/main', [
            'title'        => 'HClinic / Roaya | Tags and Templates',
            'pageTitle'    => 'Tags and Templates',
            'pageSubtitle' => 'Tags and Templates',
            'content'      => $content,
        ]);
    }

    /* ── Template API ──────────────────────────────────────────── */

    public function indexTemplates(): void
    {
        $user = $this->requireUser();
        if (!$user) return;

        $this->jsonOk(['templates' => $this->model->listTemplatesForUser((int) $user['id'])]);
    }

    public function showTemplate(int $id): void
    {
        $user = $this->requireUser();
        if (!$user) return;

        $tpl = $this->model->findTemplate($id, (int) $user['id']);
        if (!$tpl) {
            $this->jsonFail(404, 'Template not found');
            return;
        }
        $this->jsonOk(['template' => $tpl]);
    }

    public function createTemplate(): void
    {
        $user = $this->requireUser();
        if (!$user) return;

        $in = $this->readJsonBody();
        [$data, $errors] = $this->validateTemplate($in, false);
        if ($errors) {
            $this->jsonFail(422, 'Validation failed', ['errors' => $errors]);
            return;
        }

        $data['user_id'] = null;

        if (!isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        $id = $this->model->createTemplate($data);
        $tpl = $this->model->findTemplate($id, (int) $user['id']);
        $this->jsonOk(['template' => $tpl], 201);
    }

    public function updateTemplate(int $id): void
    {
        $user = $this->requireUser();
        if (!$user) return;

        $existing = $this->model->findClinicTemplate($id);
        if (!$existing) {
            $this->jsonFail(404, 'Template not found');
            return;
        }

        $in = $this->readJsonBody();
        [$data, $errors] = $this->validateTemplate($in, true);
        if ($errors) {
            $this->jsonFail(422, 'Validation failed', ['errors' => $errors]);
            return;
        }

        $this->model->updateTemplate($id, $data);
        $tpl = $this->model->findTemplate($id, (int) $user['id']);
        $this->jsonOk(['template' => $tpl]);
    }

    public function deleteTemplate(int $id): void
    {
        $user = $this->requireUser();
        if (!$user) return;

        if (!$this->model->deleteTemplate($id)) {
            $this->jsonFail(404, 'Template not found');
            return;
        }
        $this->jsonOk([]);
    }

    public function suggestions(): void
    {
        $user = $this->requireUser();
        if (!$user) return;

        $diagnosis  = trim((string) ($_GET['diagnosis'] ?? ''));
        $patientId  = (int) ($_GET['patient_id'] ?? 0);

        $diagnoses = [];
        if ($diagnosis !== '') {
            $diagnoses[] = ['diagnosis' => $diagnosis, 'source' => 'current'];
        }

        if ($patientId > 0) {
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT cn.diagnosis
                FROM consultation_notes cn
                JOIN appointments a ON a.id = cn.appointment_id
                WHERE a.patient_id = :pid
                  AND cn.diagnosis IS NOT NULL
                  AND TRIM(cn.diagnosis) != ''
                ORDER BY cn.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([':pid' => $patientId]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $dx = trim((string) $row['diagnosis']);
                if ($dx === '' || ($diagnosis !== '' && MedicalInstructionModel::normalizeText($dx) === MedicalInstructionModel::normalizeText($diagnosis))) {
                    continue;
                }
                $diagnoses[] = ['diagnosis' => $dx, 'source' => 'history'];
            }
        }

        $suggestions = $this->model->suggestTemplates((int) $user['id'], $diagnoses);
        $this->jsonOk(['suggestions' => $suggestions]);
    }

    /* ── Appointment instructions API ──────────────────────────── */

    public function indexAppointment(int $appointmentId): void
    {
        $user = $this->requireUser();
        if (!$user) return;
        if (!$this->appointmentAccessible($appointmentId)) {
            $this->jsonFail(404, 'Appointment not found');
            return;
        }

        $items = $this->model->listForAppointment($appointmentId);
        $this->jsonOk(['instructions' => $items]);
    }

    public function createAppointment(int $appointmentId): void
    {
        $user = $this->requireUser();
        if (!$user) return;
        if (!$this->appointmentAccessible($appointmentId)) {
            $this->jsonFail(404, 'Appointment not found');
            return;
        }

        $in = $this->readJsonBody();
        $items = isset($in['items']) && is_array($in['items']) ? $in['items'] : [$in];
        $created = [];

        foreach ($items as $item) {
            [$data, $errors] = $this->validateAppointmentItem($item, false);
            if ($errors) {
                $this->jsonFail(422, 'Validation failed', ['errors' => $errors]);
                return;
            }

            $templateId = isset($item['template_id']) ? (int) $item['template_id'] : null;
            if ($templateId) {
                $tpl = $this->model->findTemplate($templateId, (int) $user['id']);
                if ($tpl) {
                    $data['template_id'] = $templateId;
                    if (empty($data['title']))   $data['title']   = $tpl['title'];
                    if (empty($data['body_ar'])) $data['body_ar'] = $tpl['body_ar'];
                    if (!array_key_exists('body_en', $data) || $data['body_en'] === null) {
                        $data['body_en'] = $tpl['body_en'];
                    }
                    $this->model->incrementTemplateUse($templateId);
                }
            }

            $source = $item['source'] ?? 'custom';
            if (!in_array($source, ['auto_diagnosis', 'auto_history', 'template', 'custom'], true)) {
                $source = 'custom';
            }

            $id = $this->model->createAppointmentInstruction([
                'appointment_id' => $appointmentId,
                'template_id'    => $data['template_id'] ?? null,
                'title'          => $data['title'],
                'body_ar'        => $data['body_ar'],
                'body_en'        => $data['body_en'] ?? null,
                'source'         => $source,
                'sort_order'     => $data['sort_order'] ?? $this->model->nextSortOrder($appointmentId),
                'created_by'     => (int) $user['id'],
            ]);

            $row = $this->model->findAppointmentInstruction($id, $appointmentId);
            if ($row) {
                $created[] = $row;
            }
        }

        $this->jsonOk(['instructions' => $created], 201);
    }

    public function updateAppointment(int $appointmentId, int $id): void
    {
        $user = $this->requireUser();
        if (!$user) return;
        if (!$this->appointmentAccessible($appointmentId)) {
            $this->jsonFail(404, 'Appointment not found');
            return;
        }

        $existing = $this->model->findAppointmentInstruction($id, $appointmentId);
        if (!$existing) {
            $this->jsonFail(404, 'Instruction not found');
            return;
        }

        $in = $this->readJsonBody();
        [$data, $errors] = $this->validateAppointmentItem($in, true);
        if ($errors) {
            $this->jsonFail(422, 'Validation failed', ['errors' => $errors]);
            return;
        }

        $this->model->updateAppointmentInstruction($id, $appointmentId, $data);
        $row = $this->model->findAppointmentInstruction($id, $appointmentId);
        $this->jsonOk(['instruction' => $row]);
    }

    public function deleteAppointment(int $appointmentId, int $id): void
    {
        $user = $this->requireUser();
        if (!$user) return;
        if (!$this->appointmentAccessible($appointmentId)) {
            $this->jsonFail(404, 'Appointment not found');
            return;
        }

        if (!$this->model->deleteAppointmentInstruction($id, $appointmentId)) {
            $this->jsonFail(404, 'Instruction not found');
            return;
        }
        $this->jsonOk([]);
    }

    /* ── Helpers ───────────────────────────────────────────────── */

    private function appointmentAccessible(int $appointmentId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM appointments WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $appointmentId]);
        return (bool) $stmt->fetchColumn();
    }

    private function validateTemplate(array $in, bool $partial): array
    {
        $data = [];
        $errors = [];

        if (!$partial || array_key_exists('title', $in)) {
            $title = trim((string) ($in['title'] ?? ''));
            if ($title === '') {
                $errors['title'] = 'Title is required.';
            } elseif (mb_strlen($title) > 120) {
                $errors['title'] = 'Title must be at most 120 characters.';
            } else {
                $data['title'] = $title;
            }
        }

        if (!$partial || array_key_exists('body_ar', $in)) {
            $body = (string) ($in['body_ar'] ?? '');
            if (trim($body) === '') {
                $errors['body_ar'] = 'Arabic instructions are required.';
            } else {
                $data['body_ar'] = $body;
            }
        }

        if (array_key_exists('body_en', $in)) {
            $en = trim((string) $in['body_en']);
            $data['body_en'] = $en === '' ? null : $en;
        } elseif (!$partial) {
            $data['body_en'] = null;
        }

        if (array_key_exists('category', $in)) {
            $cat = trim((string) ($in['category'] ?? ''));
            $data['category'] = $cat === '' ? null : mb_substr($cat, 0, 40);
        } elseif (!$partial) {
            $data['category'] = null;
        }

        if (array_key_exists('diagnosis_keywords', $in)) {
            $kw = trim((string) ($in['diagnosis_keywords'] ?? ''));
            $data['diagnosis_keywords'] = $kw === '' ? null : $kw;
        } elseif (!$partial) {
            $data['diagnosis_keywords'] = null;
        }

        if (array_key_exists('icd_code', $in)) {
            $icd = trim((string) ($in['icd_code'] ?? ''));
            $data['icd_code'] = $icd === '' ? null : mb_substr($icd, 0, 20);
        } elseif (!$partial) {
            $data['icd_code'] = null;
        }

        if (array_key_exists('sort_order', $in) && is_numeric($in['sort_order'])) {
            $data['sort_order'] = (int) $in['sort_order'];
        }

        return [$data, $errors];
    }

    private function validateAppointmentItem(array $in, bool $partial): array
    {
        $data = [];
        $errors = [];

        if (!$partial || array_key_exists('title', $in)) {
            $title = trim((string) ($in['title'] ?? ''));
            if ($title === '') {
                $errors['title'] = 'Title is required.';
            } else {
                $data['title'] = mb_substr($title, 0, 120);
            }
        }

        if (!$partial || array_key_exists('body_ar', $in)) {
            $body = (string) ($in['body_ar'] ?? '');
            if (trim($body) === '') {
                $errors['body_ar'] = 'Arabic instructions are required.';
            } else {
                $data['body_ar'] = $body;
            }
        }

        if (array_key_exists('body_en', $in)) {
            $en = trim((string) ($in['body_en']));
            $data['body_en'] = $en === '' ? null : $en;
        }

        if (array_key_exists('sort_order', $in) && is_numeric($in['sort_order'])) {
            $data['sort_order'] = (int) $in['sort_order'];
        }

        if (array_key_exists('template_id', $in) && $in['template_id'] !== null && $in['template_id'] !== '') {
            $data['template_id'] = (int) $in['template_id'];
        }

        return [$data, $errors];
    }

    private function requireUser(): ?array
    {
        $this->jsonHeader();
        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
            return null;
        }
        return $user;
    }

    private function jsonHeader(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function jsonOk(array $payload, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode(array_merge(['success' => true], $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function jsonFail(int $code, string $message, array $extra = []): void
    {
        http_response_code($code);
        echo json_encode(array_merge(['success' => false, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
