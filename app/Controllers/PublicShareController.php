<?php

namespace App\Controllers;

use App\Config\Database;
use App\Lib\View;
use PDO;

/**
 * PublicShareController
 *
 * INTENTIONALLY PUBLIC — every method MUST validate the share token itself.
 * There is NO Auth::check() here. It serves a single patient-facing page that
 * shows whichever of {medication prescription, glasses prescription, medical
 * instructions} exist for ONE visit, opened from a WhatsApp link.
 *
 * Security: 256-bit token (only its sha256 hash is stored), shape-validated
 * before any DB hit, links expire / can be revoked, a missing appointment is
 * treated as "no longer available" (appointments are hard-deleted), failed
 * lookups are throttled per-IP, and every access is audited.
 */
class PublicShareController
{
    private $pdo;
    private $view;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->view = new View();
    }

    /**
     * GET /p/visit/{token}
     */
    public function visitDocuments($token)
    {
        header('X-Robots-Tag: noindex, nofollow');

        // Throttle scanning (token space is 2^256, so this is belt-and-suspenders).
        if ($this->tooManyFailures()) {
            $this->deny('rate_limited', null);
        }

        // Shape-validate BEFORE touching the DB — junk tokens 404 instantly.
        // Accepts the short base62 token (12 chars) and legacy 64-hex tokens.
        if (!preg_match('/^[0-9A-Za-z]{8,64}$/', (string) $token)) {
            $this->deny('invalid', null);
        }

        $hash = hash('sha256', (string) $token);
        $stmt = $this->pdo->prepare("SELECT * FROM prescription_share_tokens WHERE token_hash = ? LIMIT 1");
        $stmt->execute([$hash]);
        $tok = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tok) {
            $this->deny('not_found', null);
        }
        if ((int) $tok['revoked'] === 1) {
            $this->deny('revoked', $tok['id']);
        }
        if (!empty($tok['expires_at']) && strtotime($tok['expires_at']) < time()) {
            $this->deny('expired', $tok['id']);
        }
        if ((int) $tok['max_uses'] > 0 && (int) $tok['used_count'] >= (int) $tok['max_uses']) {
            $this->deny('used_up', $tok['id']);
        }

        // Social / link-preview crawlers (WhatsApp, Facebook, Twitter, …) get a
        // PHI-free Open Graph card (clinic branding only) and do NOT consume a use
        // of the link or touch the medical record.
        if ($this->isPreviewBot()) {
            $clinic = $this->getClinicForAppointment($tok['clinic_id'] ?? null);
            $this->logAccess($tok['id'], 'preview');
            header('Content-Type: text/html; charset=utf-8');
            header('X-Robots-Tag: noindex, nofollow');
            echo $this->view->render('print/public/link-preview', [
                'clinic' => $clinic,
                'og'     => $this->buildOg($clinic),
            ]);
            return;
        }

        $appointment = $this->getAppointment($tok['appointment_id']);
        if (!$appointment) {
            // Appointment hard-deleted → the link points at nothing. Fail closed.
            $this->deny('unavailable', $tok['id']);
        }

        $meds         = $this->getMedications($tok['appointment_id']);
        $glasses      = $this->getGlasses($tok['appointment_id']);
        $instructions = $this->getInstructions($tok['appointment_id']);
        $labs         = $this->getLabTests($tok['appointment_id'], 'laboratory');
        $radiology    = $this->getLabTests($tok['appointment_id'], 'radiology');

        if (empty($meds) && !$glasses && empty($instructions) && empty($labs) && empty($radiology)) {
            $this->deny('unavailable', $tok['id']);
        }

        $patient    = $this->getPatient($appointment['patient_id']);
        $doctorName = $this->getDoctorDisplay($appointment['doctor_id']);
        $clinic     = $this->getClinicForAppointment($appointment['clinic_id'] ?? null);

        // Count the view + audit success.
        try {
            $upd = $this->pdo->prepare("UPDATE prescription_share_tokens SET used_count = used_count + 1 WHERE id = ?");
            $upd->execute([$tok['id']]);
        } catch (\Exception $e) {
            // non-fatal
        }
        $this->logAccess($tok['id'], 'served');

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');

        echo $this->view->render('print/public/visit-documents', [
            'patient'      => $patient,
            'appointment'  => $appointment,
            'doctorName'   => $doctorName,
            'clinic'       => $clinic,
            'meds'         => $meds,
            'glasses'      => $glasses,
            'instructions' => $instructions,
            'labs'         => $labs,
            'radiology'    => $radiology,
            'og'           => $this->buildOg($clinic),
        ]);
    }

    // ---------------------------------------------------------------------
    // Internal helpers (self-contained — this controller never trusts a session)
    // ---------------------------------------------------------------------

    private function deny($outcome, $tokenId = null)
    {
        $this->logAccess($tokenId, $outcome);
        http_response_code(404); // uniform status for every failure → no oracle
        header('Content-Type: text/html; charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');
        echo $this->view->render('print/public/link-invalid', []);
        exit;
    }

    private function clientIp()
    {
        $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($xff !== '') {
            $parts = explode(',', $xff);
            return substr(trim($parts[0]), 0, 64);
        }
        return substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 64);
    }

    private function logAccess($tokenId, $outcome)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO prescription_share_access_log (token_id, outcome, ip_address, user_agent)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $tokenId,
                $outcome,
                $this->clientIp(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Exception $e) {
            // auditing must never break serving
        }
    }

    private function tooManyFailures()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM prescription_share_access_log
                WHERE ip_address = ?
                  AND outcome IN ('invalid', 'not_found', 'rate_limited')
                  AND accessed_at > (NOW() - INTERVAL 10 MINUTE)
            ");
            $stmt->execute([$this->clientIp()]);
            return (int) $stmt->fetchColumn() >= 60;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isPreviewBot()
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') {
            return false;
        }
        $bots = [
            'facebookexternalhit', 'facebot', 'whatsapp', 'twitterbot', 'linkedinbot',
            'slackbot', 'telegrambot', 'discordbot', 'skypeuripreview', 'pinterest',
            'vkshare', 'embedly', 'redditbot', 'applebot', 'googlebot', 'bingbot',
        ];
        foreach ($bots as $b) {
            if (strpos($ua, $b) !== false) {
                return true;
            }
        }
        return false;
    }

    private function buildOg($clinic)
    {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $base  = $proto . ($_SERVER['HTTP_HOST'] ?? '');
        $logo  = $clinic['logo'] ?? '';
        if ($logo !== '' && $logo[0] === '/') {
            $logo = $base . $logo;
        }
        return [
            'title' => 'تقرير الزيارة - ' . ($clinic['name'] ?? 'مركز رؤية'),
            'desc'  => 'اضغط لعرض وتحميل تقرير زيارتك الطبي (الوصفة العلاجية والنظارة والتحاليل والتعليمات).',
            'image' => $logo,
            'url'   => $base . ($_SERVER['REQUEST_URI'] ?? ''),
            'site'  => $clinic['name'] ?? 'مركز رؤية',
        ];
    }

    private function getAppointment($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, patient_id, doctor_id, clinic_id, date, start_time, visit_type
            FROM appointments WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getPatient($id)
    {
        $stmt = $this->pdo->prepare("SELECT first_name, last_name, dob FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($p) {
            $p['full_name'] = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
            $p['age'] = null;
            if (!empty($p['dob'])) {
                try {
                    $p['age'] = (new \DateTime($p['dob']))->diff(new \DateTime())->y;
                } catch (\Exception $e) {
                    $p['age'] = null;
                }
            }
        }
        return $p ?: ['full_name' => '', 'age' => null];
    }

    private function getDoctorDisplay($doctorId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.display_name, d.display_name_ar, u.name AS user_name
                FROM doctors d JOIN users u ON d.user_id = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$doctorId]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$r) {
                return '';
            }
            return !empty($r['display_name_ar']) ? $r['display_name_ar']
                : (!empty($r['display_name']) ? $r['display_name'] : ($r['user_name'] ?? ''));
        } catch (\Exception $e) {
            return '';
        }
    }

    private function getMedications($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT drug_name, dose, frequency, duration, notes
            FROM prescriptions WHERE appointment_id = ?
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getGlasses($appointmentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM glasses_prescriptions WHERE appointment_id = ? ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$appointmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getInstructions($appointmentId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT title, body_ar FROM appointment_medical_instructions
                WHERE appointment_id = ? ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute([$appointmentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getLabTests($appointmentId, $type)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT test_name, test_category, notes FROM lab_tests
                WHERE appointment_id = ? AND test_type = ?
                ORDER BY id ASC
            ");
            $stmt->execute([$appointmentId, $type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getSettings()
    {
        try {
            $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $out = [];
            foreach ($rows as $row) {
                $out[$row['setting_key']] = $row['setting_value'];
            }
            return $out;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getClinicForAppointment($clinicId)
    {
        $s = $this->getSettings();
        $logo = $s['clinic_logo'] ?? '/assets/images/Light.png';
        if ($clinicId) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM clinics WHERE id = ?");
                $stmt->execute([$clinicId]);
                $c = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($c) {
                    $branch = str_replace(['عيادة ', 'العيادة '], '', $c['name_ar'] ?? '');
                    return [
                        'name'    => 'مركز رؤية - ' . $branch,
                        'address' => $c['address_ar'] ?? ($s['clinic_address'] ?? ''),
                        'phone'   => $c['phone'] ?? ($s['clinic_phone'] ?? ''),
                        'logo'    => $logo,
                    ];
                }
            } catch (\Exception $e) {
                // fall through to settings
            }
        }
        return [
            'name'    => $s['clinic_name_arabic'] ?? $s['clinic_name'] ?? 'مركز رؤية للعيون',
            'address' => $s['clinic_address'] ?? '',
            'phone'   => $s['clinic_phone'] ?? '',
            'logo'    => $logo,
        ];
    }
}
