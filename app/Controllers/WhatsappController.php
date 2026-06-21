<?php
namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use PDO;

class WhatsappController
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    private function requireAuth()
    {
        if (!$this->auth->check()) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        return $this->auth->user();
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * GET /api/whatsapp/templates
     */
    public function getTemplates()
    {
        $user = $this->requireAuth();
        try {
            $role = $user['role'] ?? '';
            // Secretaries get appointment templates only — never clinical documents,
            // instructions, schedules, requests, reports or medical warnings.
            if ($role === 'secretary') {
                $stmt = $this->pdo->prepare("SELECT * FROM communication_templates WHERE is_active = 1 AND category IN ('confirmation','reminder','cancellation') ORDER BY id ASC");
            } else {
                $stmt = $this->pdo->prepare("SELECT * FROM communication_templates WHERE is_active = 1 ORDER BY id ASC");
            }
            $stmt->execute();
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(['success' => true, 'templates' => $templates]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Per-user settings table for the role (doctors vs secretaries each have their own). */
    private function userSettingsTable(array $user): string
    {
        return (($user['role'] ?? '') === 'secretary') ? 'secretary_settings' : 'doctor_settings';
    }

    /** Read the current user's WhatsApp prefs as the WHATSAPP_CONFIG shape. */
    private function readUserWhatsapp(array $user): array
    {
        $table = $this->userSettingsTable($user);
        $stmt = $this->pdo->prepare("SELECT setting_key, setting_value FROM {$table} WHERE user_id = ? AND setting_key IN ('whatsapp_enabled', 'whatsapp_advanced_features', 'whatsapp_mod_appointments', 'whatsapp_mod_visits', 'whatsapp_mod_report', 'whatsapp_mod_patientlog')");
        $stmt->execute([$user['id']]);
        $s = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return [
            'enabled'  => ($s['whatsapp_enabled'] ?? '0') === '1',
            'advanced' => ($s['whatsapp_advanced_features'] ?? '0') === '1',
            'modules'  => [
                'appointments' => ($s['whatsapp_mod_appointments'] ?? '1') === '1',
                'visits'       => ($s['whatsapp_mod_visits'] ?? '1') === '1',
                'report'       => ($s['whatsapp_mod_report'] ?? '1') === '1',
                'patientLog'   => ($s['whatsapp_mod_patientlog'] ?? '1') === '1',
            ],
        ];
    }

    /**
     * GET /api/whatsapp/config — the CURRENT USER's personal WhatsApp toggles
     * (doctor_settings or secretary_settings, keyed by user_id). The web layouts
     * (main.php / secretary_main.php) read the same per-user store to build
     * window.WHATSAPP_CONFIG, so each user's switches drive their own triggers on
     * web AND mobile.
     */
    public function getConfig()
    {
        $user = $this->requireAuth();
        try {
            $this->jsonResponse(array_merge(['success' => true], $this->readUserWhatsapp($user)));
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/whatsapp/config — save the current user's WhatsApp toggles to their
     * own per-user table. Body: { enabled?, advanced?, modules?: {appointments,
     * visits, report, patientLog} } — only provided keys are written. Returns the
     * refreshed config.
     */
    public function saveConfig()
    {
        $user = $this->requireAuth();
        try {
            $raw = json_decode(file_get_contents('php://input'), true);
            $body = is_array($raw) ? $raw : $_POST;
            $mods = isset($body['modules']) && is_array($body['modules']) ? $body['modules'] : [];
            $vals = [
                'whatsapp_enabled'           => array_key_exists('enabled', $body) ? ($body['enabled'] ? '1' : '0') : null,
                'whatsapp_advanced_features' => array_key_exists('advanced', $body) ? ($body['advanced'] ? '1' : '0') : null,
                'whatsapp_mod_appointments'  => array_key_exists('appointments', $mods) ? ($mods['appointments'] ? '1' : '0') : null,
                'whatsapp_mod_visits'        => array_key_exists('visits', $mods) ? ($mods['visits'] ? '1' : '0') : null,
                'whatsapp_mod_report'        => array_key_exists('report', $mods) ? ($mods['report'] ? '1' : '0') : null,
                'whatsapp_mod_patientlog'    => array_key_exists('patientLog', $mods) ? ($mods['patientLog'] ? '1' : '0') : null,
            ];
            $table = $this->userSettingsTable($user);
            $up = $this->pdo->prepare("INSERT INTO {$table} (user_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'boolean') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type), updated_at = CURRENT_TIMESTAMP");
            foreach ($vals as $key => $v) {
                if ($v !== null) {
                    $up->execute([$user['id'], $key, $v]);
                }
            }
            $this->jsonResponse(array_merge(['success' => true], $this->readUserWhatsapp($user)));
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/whatsapp/logs/{patientId}
     */
    public function getLogs($patientId)
    {
        $this->requireAuth();
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.name as user_name 
                FROM patient_communications c
                LEFT JOIN users u ON c.sent_by = u.id
                WHERE c.patient_id = ?
                ORDER BY c.sent_at DESC
            ");
            $stmt->execute([$patientId]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(['success' => true, 'logs' => $logs]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/whatsapp/resolve
     */
    public function resolveMessage()
    {
        $user = $this->requireAuth();

        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: [];

        $patientId = $data['patient_id'] ?? null;
        $appointmentId = $data['appointment_id'] ?? null;
        $templateId = $data['template_id'] ?? null;
        $customText = $data['custom_text'] ?? null;

        if (!$patientId) {
            $this->jsonResponse(['success' => false, 'message' => 'Patient ID is required.'], 400);
        }

        try {
            // Fetch patient details
            $patientStmt = $this->pdo->prepare("SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM patients WHERE id = ?");
            $patientStmt->execute([$patientId]);
            $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);

            if (!$patient) {
                $this->jsonResponse(['success' => false, 'message' => 'Patient not found.'], 404);
            }

            // Fetch template details if templateId is provided
            $templateBody = '';
            if ($templateId) {
                $tplStmt = $this->pdo->prepare("SELECT body FROM communication_templates WHERE id = ?");
                $tplStmt->execute([$templateId]);
                $templateBody = $tplStmt->fetchColumn() ?: '';
            } elseif ($customText) {
                $templateBody = $customText;
            }

            // Fetch clinic settings
            $settingsStmt = $this->pdo->prepare("SELECT setting_key, setting_value FROM settings");
            $settingsStmt->execute();
            $settingsRows = $settingsStmt->fetchAll(PDO::FETCH_ASSOC);
            $settings = [];
            foreach ($settingsRows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            $clinicName = $settings['clinic_name_arabic'] ?? $settings['clinic_name'] ?? 'عيادة رؤية للعيون';
            $clinicAddress = $settings['clinic_address'] ?? 'القاهرة، مصر';

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
            $absoluteBaseUrl = $protocol . $host;
            
            // Try to find the doctor's Arabic name if they are logged in as doctor
            $loggedInDocName = '';
            if (($user['role'] ?? '') === 'doctor') {
                $docStmt = $this->pdo->prepare("SELECT display_name_ar, display_name FROM doctors WHERE user_id = ?");
                $docStmt->execute([$user['id']]);
                $docRow = $docStmt->fetch(PDO::FETCH_ASSOC);
                if ($docRow) {
                    $loggedInDocName = !empty($docRow['display_name_ar']) ? $docRow['display_name_ar'] : $docRow['display_name'];
                }
            }

            // Resolve clinic details dynamically if clinic_id is present (or fallback to user/settings)
            $defaultClinicName = $clinicName;
            $defaultClinicAddress = $clinicAddress;
            $defaultClinicPhone = $settings['clinic_phone'] ?? '';
            $userClinicId = $user['clinic_id'] ?? null;
            $clinicIdToResolve = $data['clinic_id'] ?? $userClinicId;
            if ($clinicIdToResolve) {
                $clinicStmt = $this->pdo->prepare("SELECT * FROM clinics WHERE id = ?");
                $clinicStmt->execute([$clinicIdToResolve]);
                $clinicInfo = $clinicStmt->fetch(PDO::FETCH_ASSOC);
                if ($clinicInfo) {
                    $branchName = str_replace(['عيادة ', 'العيادة '], '', $clinicInfo['name_ar']);
                    $defaultClinicName = "مركز رؤية - " . $branchName;
                    if (!empty($clinicInfo['phone'])) {
                        $defaultClinicPhone = $clinicInfo['phone'];
                    }

                    $addrParts = [];
                    $addrParts[] = $defaultClinicName;
                    if (!empty($clinicInfo['address_ar'])) {
                        $addrParts[] = $clinicInfo['address_ar'];
                    }
                    if (!empty($clinicInfo['phone'])) {
                        $addrParts[] = "ت: " . $clinicInfo['phone'];
                    }
                    $defaultClinicAddress = implode("، ", $addrParts);
                }
            }

            // Default placeholders (including fallbacks for deleted appointments)
            $placeholders = [
                '{{patient_name}}' => $patient['full_name'],
                '{{doctor_name}}' => $data['doctor_name'] ?? ($loggedInDocName ?: ($user['name'] ?? '')),
                '{{appointment_date}}' => $data['appointment_date'] ?? '',
                '{{appointment_time}}' => $data['appointment_time'] ?? '',
                '{{clinic_name}}' => $defaultClinicName,
                '{{clinic_address}}' => $defaultClinicAddress,
                '{{clinic_phone}}' => $defaultClinicPhone,
                '{{visit_summary}}' => '',
                '{{follow_up_date}}' => '',
                '{{eye_drops_schedule}}' => '',
                '{{diagnosis}}' => '',
                '{{prescription_summary}}' => '',
                '{{glasses_prescription}}' => '',
                '{{requested_tests}}' => '',
                '{{surgery_type}}' => '',
                '{{surgery_date}}' => '',
                '{{prescription_pdf_url}}' => '',
                '{{glasses_pdf_url}}' => '',
                '{{visit_report_pdf_url}}' => '', // comprehensive visit report dropped from patient messages
                '{{prescription_section}}' => '',
                '{{glasses_section}}' => '',
                '{{instructions_section}}' => '',
                '{{visit_documents_section}}' => ''
            ];

            // Resolve appointment variables
            $appt = null;
            if ($appointmentId) {
                $apptStmt = $this->pdo->prepare("
                    SELECT a.*, u.name as doc_name, d.display_name as doc_display, d.display_name_ar as doc_display_ar 
                    FROM appointments a
                    JOIN doctors d ON a.doctor_id = d.id
                    JOIN users u ON d.user_id = u.id
                    WHERE a.id = ?
                ");
                $apptStmt->execute([$appointmentId]);
                $appt = $apptStmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Fetch latest appointment for patient as fallback
                $apptStmt = $this->pdo->prepare("
                    SELECT a.*, u.name as doc_name, d.display_name as doc_display, d.display_name_ar as doc_display_ar 
                    FROM appointments a
                    JOIN doctors d ON a.doctor_id = d.id
                    JOIN users u ON d.user_id = u.id
                    WHERE a.patient_id = ?
                    ORDER BY a.date DESC, a.start_time DESC LIMIT 1
                ");
                $apptStmt->execute([$patientId]);
                $appt = $apptStmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($appt) {
                $placeholders['{{doctor_name}}'] = !empty($loggedInDocName) ? $loggedInDocName : (!empty($appt['doc_display_ar']) ? $appt['doc_display_ar'] : (!empty($appt['doc_display']) ? $appt['doc_display'] : $appt['doc_name']));
                $placeholders['{{appointment_date}}'] = date('Y-m-d', strtotime($appt['date']));
                $placeholders['{{appointment_time}}'] = date('g:i A', strtotime($appt['start_time']));
                
                // Patient-facing document links are minted below as ONE public,
                // token-based link AFTER this visit's documents are resolved. The
                // staff-only /print/* URLs are intentionally NOT sent to patients
                // (they 401 without a staff session).

                // Resolve clinic details dynamically if clinic_id is present
                if (!empty($appt['clinic_id'])) {
                    $clinicStmt = $this->pdo->prepare("SELECT * FROM clinics WHERE id = ?");
                    $clinicStmt->execute([$appt['clinic_id']]);
                    $clinicInfo = $clinicStmt->fetch(PDO::FETCH_ASSOC);
                    if ($clinicInfo) {
                        $branchName = str_replace(['عيادة ', 'العيادة '], '', $clinicInfo['name_ar']);
                        $placeholders['{{clinic_name}}'] = "مركز رؤية - " . $branchName;
                        // Phone/address MUST come from THIS appointment's branch (two-clinic setup).
                        if (!empty($clinicInfo['phone'])) {
                            $placeholders['{{clinic_phone}}'] = $clinicInfo['phone'];
                        }

                        $addrParts = [];
                        $addrParts[] = $placeholders['{{clinic_name}}'];
                        if (!empty($clinicInfo['address_ar'])) {
                            $addrParts[] = $clinicInfo['address_ar'];
                        }
                        if (!empty($clinicInfo['phone'])) {
                            $addrParts[] = "ت: " . $clinicInfo['phone'];
                        }
                        $placeholders['{{clinic_address}}'] = implode("， ", $addrParts);
                    }
                }

                // Resolve consultation notes (diagnosis, plan/summary)
                $noteStmt = $this->pdo->prepare("SELECT * FROM consultation_notes WHERE appointment_id = ? ORDER BY id DESC LIMIT 1");
                $noteStmt->execute([$appt['id']]);
                $note = $noteStmt->fetch(PDO::FETCH_ASSOC);

                if ($note) {
                    $placeholders['{{diagnosis}}'] = $note['diagnosis'] ?: '';
                    $placeholders['{{visit_summary}}'] = $note['plan'] ?: '';

                    // Resolve follow up date
                    if (!empty($note['followup_days']) && is_numeric($note['followup_days'])) {
                        $followupDate = date('Y-m-d', strtotime($appt['date'] . ' + ' . $note['followup_days'] . ' days'));
                        $placeholders['{{follow_up_date}}'] = $followupDate;
                    }
                }

                // Resolve medical instructions / eye drops schedule
                $instStmt = $this->pdo->prepare("SELECT title, body_ar FROM appointment_medical_instructions WHERE appointment_id = ? ORDER BY sort_order ASC");
                $instStmt->execute([$appt['id']]);
                $instructions = $instStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($instructions)) {
                    $instTexts = [];
                    foreach ($instructions as $inst) {
                        $instTexts[] = "- " . $inst['title'] . ": " . $inst['body_ar'];
                    }
                    $placeholders['{{eye_drops_schedule}}'] = implode("\n", $instTexts);
                    $placeholders['{{instructions_section}}'] = "التعليمات الطبية:\n" . $placeholders['{{eye_drops_schedule}}'] . "\n\n";
                }

                // Resolve medication prescriptions
                $rxStmt = $this->pdo->prepare("SELECT drug_name, dose, frequency, duration, notes FROM prescriptions WHERE appointment_id = ?");
                $rxStmt->execute([$appt['id']]);
                $prescriptions = $rxStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($prescriptions)) {
                    $rxTexts = [];
                    foreach ($prescriptions as $rx) {
                        $parts = [];
                        if ($rx['dose']) $parts[] = $rx['dose'];
                        if ($rx['frequency']) $parts[] = $rx['frequency'];
                        if ($rx['duration']) $parts[] = $rx['duration'];
                        if ($rx['notes']) $parts[] = "(" . $rx['notes'] . ")";
                        $rxTexts[] = "* " . $rx['drug_name'] . " " . (implode(' - ', $parts));
                    }
                    $placeholders['{{prescription_summary}}'] = implode("\n", $rxTexts);
                }

                // Resolve glasses prescriptions
                $glassesStmt = $this->pdo->prepare("SELECT * FROM glasses_prescriptions WHERE appointment_id = ? ORDER BY id DESC LIMIT 1");
                $glassesStmt->execute([$appt['id']]);
                $glasses = $glassesStmt->fetch(PDO::FETCH_ASSOC);
                if ($glasses) {
                    $glText = "نظارة مسافة:\n";
                    $glText .= "R (يمين): SPH: {$glasses['distance_sphere_r']} CYL: {$glasses['distance_cylinder_r']} AXIS: {$glasses['distance_axis_r']}\n";
                    $glText .= "L (يسار): SPH: {$glasses['distance_sphere_l']} CYL: {$glasses['distance_cylinder_l']} AXIS: {$glasses['distance_axis_l']}\n";
                    if ($glasses['PD_DISTANCE']) $glText .= "PD: {$glasses['PD_DISTANCE']}\n";

                    if ($glasses['near_sphere_r'] || $glasses['near_sphere_l']) {
                        $glText .= "نظارة قراءة:\n";
                        $glText .= "R (يمين): SPH: {$glasses['near_sphere_r']} CYL: {$glasses['near_cylinder_r']} AXIS: {$glasses['near_axis_r']}\n";
                        $glText .= "L (يسار): SPH: {$glasses['near_sphere_l']} CYL: {$glasses['near_cylinder_l']} AXIS: {$glasses['near_axis_l']}\n";
                        if ($glasses['PD_NEAR']) $glText .= "PD: {$glasses['PD_NEAR']}\n";
                    }
                    if ($glasses['lens_type']) $glText .= "نوع العدسة: {$glasses['lens_type']}\n";
                    if ($glasses['comments']) $glText .= "ملاحظات: {$glasses['comments']}";

                    $placeholders['{{glasses_prescription}}'] = $glText;
                }

                // Resolve requested tests — split into labs vs radiology (two-clinic safe).
                $testStmt = $this->pdo->prepare("SELECT test_type, test_name FROM lab_tests WHERE appointment_id = ?");
                $testStmt->execute([$appt['id']]);
                $testRows = $testStmt->fetchAll(PDO::FETCH_ASSOC);
                $labTests = [];
                $radTests = [];
                foreach ($testRows as $t) {
                    if (($t['test_type'] ?? '') === 'radiology') {
                        $radTests[] = $t['test_name'];
                    } else {
                        $labTests[] = $t['test_name'];
                    }
                }
                if (!empty($testRows)) {
                    $placeholders['{{requested_tests}}'] = implode("\n", array_map(fn($r) => "- " . $r['test_name'], $testRows));
                }

                // Resolve surgery details (look for 'operation' or 'surgery' or 'cataract' or 'lasik' in plan / diagnosis)
                if ($note) {
                    $allNotesText = ($note['diagnosis'] ?? '') . ' ' . ($note['plan'] ?? '');
                    if (preg_match('/(عملية|جراحة|مياه بيضاء|ليزك|cataract|lasik|surgery|injection|حقن)/iu', $allNotesText, $matches)) {
                        $placeholders['{{surgery_type}}'] = $matches[0];
                        $placeholders['{{surgery_date}}'] = date('Y-m-d', strtotime($appt['date'] . ' + 7 days')); // estimate
                    }
                }

                // --- ONE public, patient-accessible link for this visit's documents ---
                // Opens a single no-login page showing whichever of {prescription,
                // glasses, instructions} exist for this visit. Replaces the staff-only
                // /print/* links (which 401 for patients). Minted only when the caller
                // is allowed to see this appointment's clinic (closes the cross-clinic
                // leak for the public surface).
                $hasMeds    = !empty($prescriptions);
                $hasGlasses = !empty($glasses);
                $hasInstr   = !empty($instructions);
                $hasLabs    = !empty($labTests);
                $hasRad     = !empty($radTests);
                if (($hasMeds || $hasGlasses || $hasInstr || $hasLabs || $hasRad) && $this->appointmentInScope($user, $appt)) {
                    $shareToken = $this->mintShareToken($appt, $user);
                    if ($shareToken) {
                        $docsUrl = $absoluteBaseUrl . base_url("/p/v/{$shareToken}");
                        // Legacy single-doc placeholders now resolve to the same combined page.
                        $placeholders['{{prescription_pdf_url}}'] = $docsUrl;
                        $placeholders['{{glasses_pdf_url}}']      = $docsUrl;

                        // Priority order: prescription first, then instructions, glasses, labs, radiology.
                        $labelParts = [];
                        if ($hasMeds)    { $labelParts[] = 'الوصفة العلاجية'; }
                        if ($hasInstr)   { $labelParts[] = 'التعليمات الطبية'; }
                        if ($hasGlasses) { $labelParts[] = 'مقاس النظارة'; }
                        if ($hasLabs)    { $labelParts[] = 'التحاليل'; }
                        if ($hasRad)     { $labelParts[] = 'الأشعة'; }
                        $label = 'إليك رابط تقرير الزيارة ويشمل (' . implode(' - ', $labelParts) . ')';
                        $placeholders['{{visit_documents_section}}'] = $label . ":\n" . $docsUrl . "\n\n";

                        if ($hasMeds) {
                            $placeholders['{{prescription_section}}'] = "رابط الوصفة الطبية:\n" . $docsUrl . "\n\n";
                        }
                        if ($hasGlasses) {
                            $placeholders['{{glasses_section}}'] = "رابط مقاس النظارة:\n" . $docsUrl . "\n\n";
                        }
                    }
                }
            }

            // Normalize the doctor honorific: every template prepends "د. " but some
            // display names already start with "د." or "Dr." → "د. د." / "د. Dr.".
            $placeholders['{{doctor_name}}'] = preg_replace('/^\s*(?:د|dr)\.?\s*/iu', '', trim((string) $placeholders['{{doctor_name}}']));

            // Perform template substitution
            $resolvedBody = $templateBody;
            foreach ($placeholders as $key => $val) {
                $resolvedBody = str_replace($key, $val, $resolvedBody);
            }

            // Flag if message contains sensitive clinical data
            $hasSensitiveData = false;
            $sensitiveTriggers = ['مياه بيضاء', 'عملية', 'حقن', 'قطرة', 'قطرات', 'جراحة', 'تشخيص', 'cataract', 'lasik', 'surgery', 'injection', 'eye drops', 'diagnosis'];
            foreach ($sensitiveTriggers as $trigger) {
                if (mb_stripos($resolvedBody, $trigger) !== false) {
                    $hasSensitiveData = true;
                    break;
                }
            }
            // Also if prescription summaries are explicitly non-empty
            if ($placeholders['{{prescription_summary}}'] !== '' || $placeholders['{{glasses_prescription}}'] !== '') {
                $hasSensitiveData = true;
            }

            $consent = (int) ($patient['whatsapp_consent'] ?? 1);

            $this->jsonResponse([
                'success' => true,
                'resolved_body' => $resolvedBody,
                'phone' => $patient['phone'],
                'consent' => $consent,
                'has_sensitive_data' => $hasSensitiveData
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Can the resolving staff member see this appointment's clinic?
     * Admins and doctors keep the app's existing (intentionally broad) read model;
     * secretaries are pinned to their own clinic, so a secretary may not mint a
     * public link for another branch's patient. Fail-closed only for secretaries
     * with a known mismatching clinic.
     */
    private function appointmentInScope($user, $appt)
    {
        $role = $user['role'] ?? '';
        if ($role === 'admin' || $role === 'doctor') {
            return true;
        }
        $userClinic = $user['clinic_id'] ?? null;
        $apptClinic = $appt['clinic_id'] ?? null;
        if ($userClinic && $apptClinic && (string) $userClinic !== (string) $apptClinic) {
            return false;
        }
        return true;
    }

    /**
     * Mint a public, single-link share token for one appointment's documents.
     * Stores only the SHA-256 hash; the raw token is returned and lives only in
     * the outgoing WhatsApp message. Returns null on failure (fail-safe: no link).
     */
    /**
     * Short, URL-friendly, unguessable token: 12 base62 chars (~71 bits).
     * Far shorter than a 64-hex string while staying non-enumerable; links also
     * expire and are rate-limited. Uses the CSPRNG random_int().
     */
    private function shortToken($len = 12)
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($alphabet) - 1;
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }

    private function mintShareToken($appt, $user)
    {
        try {
            $raw  = $this->shortToken(); // 12 base62 chars, non-enumerable
            $hash = hash('sha256', $raw);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+90 days'));
            $stmt = $this->pdo->prepare("
                INSERT INTO prescription_share_tokens
                    (token_hash, appointment_id, patient_id, clinic_id, created_by, expires_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $hash,
                $appt['id'],
                $appt['patient_id'] ?? null,
                $appt['clinic_id'] ?? null,
                $user['id'] ?? 0,
                $expiresAt,
            ]);
            return $raw;
        } catch (\Exception $e) {
            error_log('mintShareToken failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * POST /api/whatsapp/share/revoke/{tokenId}
     * Staff can instantly kill a previously-issued public link.
     */
    public function revokeShare($tokenId)
    {
        $user = $this->requireAuth();
        try {
            $stmt = $this->pdo->prepare("SELECT clinic_id FROM prescription_share_tokens WHERE id = ?");
            $stmt->execute([$tokenId]);
            $tok = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tok) {
                $this->jsonResponse(['success' => false, 'message' => 'Not found'], 404);
            }
            $role = $user['role'] ?? '';
            if ($role === 'secretary' && !empty($user['clinic_id']) && !empty($tok['clinic_id'])
                && (string) $user['clinic_id'] !== (string) $tok['clinic_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
            }
            $upd = $this->pdo->prepare("UPDATE prescription_share_tokens SET revoked = 1 WHERE id = ?");
            $upd->execute([$tokenId]);
            $this->jsonResponse(['success' => true, 'message' => 'Link revoked']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/whatsapp/consent
     */
    public function updateConsent()
    {
        $this->requireAuth();

        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: [];

        $patientId = $data['patient_id'] ?? null;
        $consent = isset($data['consent']) ? (int) $data['consent'] : 1;

        if (!$patientId) {
            $this->jsonResponse(['success' => false, 'message' => 'Patient ID is required.'], 400);
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE patients SET whatsapp_consent = ? WHERE id = ?");
            $stmt->execute([$consent, $patientId]);

            $this->jsonResponse(['success' => true, 'message' => 'Consent updated successfully.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/whatsapp/log
     */
    public function logCommunication()
    {
        $user = $this->requireAuth();

        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: [];

        $patientId = $data['patient_id'] ?? null;
        $appointmentId = $data['appointment_id'] ?? null;
        $templateId = $data['template_id'] ?? null;
        $messageBody = $data['message_body'] ?? '';
        $phoneNumber = $data['phone_number'] ?? '';
        $status = $data['status'] ?? 'opened';
        $relatedEye = $data['related_eye'] ?? 'not_applicable';
        $relatedService = $data['related_service'] ?? null;
        $relatedTestType = $data['related_test_type'] ?? null;

        if (!$patientId || empty($messageBody) || empty($phoneNumber)) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required log details.'], 400);
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO patient_communications (
                    patient_id, appointment_id, template_id, message_body, 
                    phone_number, sent_by, status, related_eye, 
                    related_service, related_test_type
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $patientId,
                $appointmentId,
                $templateId ?: null,
                $messageBody,
                $phoneNumber,
                $user['id'],
                $status,
                $relatedEye,
                $relatedService,
                $relatedTestType
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Communication logged successfully.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/whatsapp/templates/{id}
     */
    public function updateTemplate($id)
    {
        $user = $this->requireAuth();
        
        // Ensure doctor/admin role
        if ($user['role'] !== 'doctor' && $user['role'] !== 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: [];

        $title = $data['title'] ?? '';
        $body = $data['body'] ?? '';

        if (empty($title) || empty($body)) {
            $this->jsonResponse(['success' => false, 'message' => 'Title and Body are required.'], 400);
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE communication_templates 
                SET title = ?, body = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$title, $body, $id]);

            $this->jsonResponse(['success' => true, 'message' => 'Template updated successfully.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
