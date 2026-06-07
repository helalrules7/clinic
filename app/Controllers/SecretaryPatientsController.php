<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Config\Database;

/**
 * SecretaryPatientsController
 * ---------------------------------------------------------------------------
 * Clinic-scoped patient organization for the secretary layout (v11 list/profile
 * parity, "operational only"). Folders / tags / color markers here are owned at
 * the CLINIC level (separate from the doctor's doctor_id-owned data) per the
 * locked product decisions — see V11_SEC_LAYOUT.md §14.
 *
 * Owner model (folders/tags): clinic-owned rows carry doctor_id NULL + clinic_id
 * = the secretary's clinic. Color markers live in the dedicated
 * patient_clinic_color_markers table. Patients themselves are GLOBAL (a secretary
 * sees every patient); only the organization layer + financials are clinic-scoped.
 *
 * All endpoints are mounted under /api/secretary/… and require role secretary|admin.
 */
class SecretaryPatientsController
{
    private $auth;
    private $pdo;
    private $clinicId;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth->requireRole(['secretary', 'admin']);
        $user = $this->auth->user();
        $this->clinicId = !empty($user['clinic_id']) ? (int)$user['clinic_id'] : 0;
    }

    // ----- helpers ----------------------------------------------------------
    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function body(): array
    {
        $raw = file_get_contents('php://input');
        $d = json_decode($raw, true);
        if (is_array($d)) return $d;
        return $_POST ?: [];
    }

    /** All clinic writes require a clinic. Returns false (and emits 403) if missing. */
    private function needClinic(): bool
    {
        if ($this->clinicId <= 0) {
            $this->json(['error' => 'لا توجد عيادة مرتبطة بحسابك'], 403);
            return false;
        }
        return true;
    }

    private function safeIcon($icon): string
    {
        $icon = trim((string)$icon);
        return preg_match('/^bi-[a-z0-9-]{1,40}$/', $icon) ? $icon : 'bi-folder';
    }

    /** Allow only a safe charset for a CSS gradient (no XSS via stored gradient). */
    private function safeGradient($g): string
    {
        $g = trim((string)$g);
        $default = 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)';
        if ($g === '') return $default;
        if (strlen($g) > 200) return $default;
        // linear-gradient with only hex/rgb()/percent/deg/digits/commas/spaces/parens.
        if (preg_match('/^linear-gradient\([#a-z0-9%,\.\s\(\)-]+\)$/i', $g)) return $g;
        return $default;
    }

    private function safeColor($c): ?string
    {
        $c = trim((string)$c);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? $c : null;
    }

    // ========================================================================
    //  FOLDERS  (clinic-owned, parent_type='custom')
    // ========================================================================
    public function folders()
    {
        if (!$this->needClinic()) return;
        try {
            $stmt = $this->pdo->prepare("
                SELECT pf.id, pf.name, pf.icon, pf.gradient_color, pf.parent_id, pf.created_at,
                       COUNT(DISTINCT pfp.patient_id) AS patient_count
                FROM patient_folders pf
                LEFT JOIN patient_folder_patients pfp ON pfp.folder_id = pf.id
                WHERE pf.clinic_id = ? AND pf.parent_type = 'custom'
                GROUP BY pf.id
                ORDER BY pf.name ASC
            ");
            $stmt->execute([$this->clinicId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // total patients (distinct) filed into any clinic folder + grand total
            $total = (int)$this->pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
            return $this->json(['ok' => true, 'folders' => $rows, 'total_patients' => $total]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createFolder()
    {
        if (!$this->needClinic()) return;
        try {
            $d = $this->body();
            $name = trim((string)($d['name'] ?? ''));
            if ($name === '' || mb_strlen($name) > 120) {
                return $this->json(['error' => 'اسم المجلد مطلوب (حتى 120 حرفاً)'], 422);
            }
            $parentId = isset($d['parent_id']) && $d['parent_id'] !== '' ? (int)$d['parent_id'] : null;
            if ($parentId !== null && !$this->ownsFolder($parentId)) {
                return $this->json(['error' => 'المجلد الأصل غير صالح'], 422);
            }
            $icon = $this->safeIcon($d['icon'] ?? 'bi-folder');
            $grad = $this->safeGradient($d['gradient_color'] ?? '');

            $stmt = $this->pdo->prepare("
                INSERT INTO patient_folders
                    (doctor_id, clinic_id, name, created_by_user_id, icon, gradient_color, parent_id, parent_type)
                VALUES (NULL, ?, ?, ?, ?, ?, ?, 'custom')
            ");
            $stmt->execute([$this->clinicId, $name, $this->auth->user()['id'], $icon, $grad, $parentId]);
            $id = (int)$this->pdo->lastInsertId();
            return $this->json(['ok' => true, 'data' => ['id' => $id, 'name' => $name, 'icon' => $icon,
                'gradient_color' => $grad, 'parent_id' => $parentId, 'patient_count' => 0]]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFolder($id)
    {
        if (!$this->needClinic()) return;
        try {
            $id = (int)$id;
            if (!$this->ownsFolder($id)) return $this->json(['error' => 'المجلد غير موجود'], 404);
            $d = $this->body();
            $sets = [];
            $params = [];
            if (array_key_exists('name', $d)) {
                $name = trim((string)$d['name']);
                if ($name === '' || mb_strlen($name) > 120) return $this->json(['error' => 'اسم غير صالح'], 422);
                $sets[] = 'name = ?'; $params[] = $name;
            }
            if (array_key_exists('icon', $d)) { $sets[] = 'icon = ?'; $params[] = $this->safeIcon($d['icon']); }
            if (array_key_exists('gradient_color', $d)) { $sets[] = 'gradient_color = ?'; $params[] = $this->safeGradient($d['gradient_color']); }
            if (array_key_exists('parent_id', $d)) {
                $pid = $d['parent_id'] !== '' && $d['parent_id'] !== null ? (int)$d['parent_id'] : null;
                if ($pid === $id) return $this->json(['error' => 'لا يمكن جعل المجلد أصلاً لنفسه'], 422);
                if ($pid !== null && !$this->ownsFolder($pid)) return $this->json(['error' => 'الأصل غير صالح'], 422);
                $sets[] = 'parent_id = ?'; $params[] = $pid;
            }
            if (!$sets) return $this->json(['ok' => true]);
            $params[] = $id; $params[] = $this->clinicId;
            $stmt = $this->pdo->prepare("UPDATE patient_folders SET " . implode(', ', $sets) . " WHERE id = ? AND clinic_id = ?");
            $stmt->execute($params);
            return $this->json(['ok' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFolder($id)
    {
        if (!$this->needClinic()) return;
        try {
            $id = (int)$id;
            if (!$this->ownsFolder($id)) return $this->json(['error' => 'المجلد غير موجود'], 404);
            // collect this folder + its (clinic-owned) sub-folders
            $ids = [$id];
            $sub = $this->pdo->prepare("SELECT id FROM patient_folders WHERE parent_id = ? AND clinic_id = ?");
            $sub->execute([$id, $this->clinicId]);
            foreach ($sub->fetchAll(\PDO::FETCH_COLUMN) as $sid) $ids[] = (int)$sid;
            $in = implode(',', array_fill(0, count($ids), '?'));
            $this->pdo->prepare("DELETE FROM patient_folder_patients WHERE folder_id IN ($in)")->execute($ids);
            $del = $this->pdo->prepare("DELETE FROM patient_folders WHERE id IN ($in) AND clinic_id = ?");
            $del->execute(array_merge($ids, [$this->clinicId]));
            return $this->json(['ok' => true, 'deleted' => $ids]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Move / copy / remove patients across clinic folders. */
    public function movePatients()
    {
        if (!$this->needClinic()) return;
        try {
            $d = $this->body();
            $ids = array_values(array_filter(array_map('intval', (array)($d['patient_ids'] ?? []))));
            if (!$ids) return $this->json(['error' => 'لم يتم تحديد مرضى'], 422);
            $mode = in_array(($d['mode'] ?? 'move'), ['move', 'copy', 'remove'], true) ? $d['mode'] : 'move';
            $to   = isset($d['to']) && $d['to'] !== '' && $d['to'] !== null ? (int)$d['to'] : null;
            $from = isset($d['from']) && $d['from'] !== '' && $d['from'] !== null ? (int)$d['from'] : null;

            if (($mode === 'move' || $mode === 'copy')) {
                if ($to === null || !$this->ownsFolder($to)) return $this->json(['error' => 'المجلد الوجهة غير صالح'], 422);
                $ins = $this->pdo->prepare("INSERT IGNORE INTO patient_folder_patients (folder_id, patient_id) VALUES (?, ?)");
                foreach ($ids as $pid) $ins->execute([$to, $pid]);
            }
            if (($mode === 'move' || $mode === 'remove') && $from !== null && $this->ownsFolder($from)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $del = $this->pdo->prepare("DELETE FROM patient_folder_patients WHERE folder_id = ? AND patient_id IN ($in)");
                $del->execute(array_merge([$from], $ids));
            }
            return $this->json(['ok' => true, 'count' => count($ids)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function ownsFolder(int $id): bool
    {
        $s = $this->pdo->prepare("SELECT 1 FROM patient_folders WHERE id = ? AND clinic_id = ? LIMIT 1");
        $s->execute([$id, $this->clinicId]);
        return (bool)$s->fetchColumn();
    }

    /**
     * Auto-organize: create one clinic folder per registration month and file every
     * patient registered that month into it (idempotent — re-runnable, INSERT IGNORE).
     */
    public function autoOrganizeByMonth()
    {
        if (!$this->needClinic()) return;
        try {
            $months = [1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
                       7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'];
            $groups = $this->pdo->query("
                SELECT YEAR(created_at) y, MONTH(created_at) m, COUNT(*) c
                FROM patients WHERE created_at IS NOT NULL
                GROUP BY y, m ORDER BY y, m
            ")->fetchAll(\PDO::FETCH_ASSOC);

            $foldersCreated = 0; $filed = 0;
            $find = $this->pdo->prepare("SELECT id FROM patient_folders WHERE clinic_id = ? AND parent_type='custom' AND name = ? LIMIT 1");
            $ins  = $this->pdo->prepare("INSERT INTO patient_folders (doctor_id, clinic_id, name, created_by_user_id, icon, gradient_color, parent_id, parent_type) VALUES (NULL, ?, ?, ?, 'bi-calendar-month', ?, NULL, 'custom')");
            $fill = $this->pdo->prepare("INSERT IGNORE INTO patient_folder_patients (folder_id, patient_id) SELECT ?, id FROM patients WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?");
            $grad = 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)';
            $uid  = $this->auth->user()['id'];

            foreach ($groups as $g) {
                $y = (int)$g['y']; $m = (int)$g['m'];
                if ($m < 1 || $m > 12 || $y < 2000) continue;
                $name = $months[$m] . ' ' . $y;
                $find->execute([$this->clinicId, $name]);
                $fid = $find->fetchColumn();
                if (!$fid) {
                    $ins->execute([$this->clinicId, $name, $uid, $grad]);
                    $fid = (int)$this->pdo->lastInsertId();
                    $foldersCreated++;
                }
                $fill->execute([(int)$fid, $y, $m]);
                $filed += $fill->rowCount();
            }
            return $this->json(['ok' => true, 'folders_created' => $foldersCreated, 'patients_filed' => $filed]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========================================================================
    //  TAGS  (clinic-owned)
    // ========================================================================
    public function tags()
    {
        if (!$this->needClinic()) return;
        try {
            $s = $this->pdo->prepare("
                SELECT id, name, color, icon, sort_order
                FROM patient_tags
                WHERE clinic_id = ?
                ORDER BY sort_order ASC, name ASC
            ");
            $s->execute([$this->clinicId]);
            return $this->json(['ok' => true, 'tags' => $s->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createTag()
    {
        if (!$this->needClinic()) return;
        try {
            $d = $this->body();
            $name = trim((string)($d['name'] ?? ''));
            if ($name === '' || mb_strlen($name) > 50) return $this->json(['error' => 'اسم الوسم مطلوب (حتى 50 حرفاً)'], 422);
            $color = $this->safeColor($d['color'] ?? '') ?? '#6366f1';
            $icon  = $this->safeIcon($d['icon'] ?? 'bi-tag');
            // dup within this clinic
            $dup = $this->pdo->prepare("SELECT id FROM patient_tags WHERE name = ? AND clinic_id = ?");
            $dup->execute([$name, $this->clinicId]);
            if ($dup->fetchColumn()) return $this->json(['error' => 'يوجد وسم بهذا الاسم'], 409);
            $s = $this->pdo->prepare("INSERT INTO patient_tags (name, color, icon, doctor_id, clinic_id, sort_order) VALUES (?, ?, ?, NULL, ?, 0)");
            $s->execute([$name, $color, $icon, $this->clinicId]);
            $id = (int)$this->pdo->lastInsertId();
            return $this->json(['ok' => true, 'data' => ['id' => $id, 'name' => $name, 'color' => $color, 'icon' => $icon]]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteTag($id)
    {
        if (!$this->needClinic()) return;
        try {
            $id = (int)$id;
            $own = $this->pdo->prepare("SELECT 1 FROM patient_tags WHERE id = ? AND clinic_id = ?");
            $own->execute([$id, $this->clinicId]);
            if (!$own->fetchColumn()) return $this->json(['error' => 'الوسم غير موجود'], 404);
            $this->pdo->prepare("DELETE FROM patient_tag_assignments WHERE tag_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM patient_tags WHERE id = ? AND clinic_id = ?")->execute([$id, $this->clinicId]);
            return $this->json(['ok' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Assign/unassign clinic tags to a patient. body: { patient_id, add:[], remove:[] } */
    public function assignTags()
    {
        if (!$this->needClinic()) return;
        try {
            $d = $this->body();
            $pid = (int)($d['patient_id'] ?? 0);
            if ($pid <= 0) return $this->json(['error' => 'مريض غير صالح'], 422);
            $add = array_values(array_filter(array_map('intval', (array)($d['add'] ?? []))));
            $rem = array_values(array_filter(array_map('intval', (array)($d['remove'] ?? []))));
            // only this clinic's tags may be assigned
            $add = $this->filterClinicTagIds($add);
            $rem = $this->filterClinicTagIds($rem);
            if ($add) {
                $ins = $this->pdo->prepare("INSERT IGNORE INTO patient_tag_assignments (patient_id, tag_id) VALUES (?, ?)");
                foreach ($add as $t) $ins->execute([$pid, $t]);
            }
            if ($rem) {
                $in = implode(',', array_fill(0, count($rem), '?'));
                $this->pdo->prepare("DELETE FROM patient_tag_assignments WHERE patient_id = ? AND tag_id IN ($in)")
                    ->execute(array_merge([$pid], $rem));
            }
            return $this->json(['ok' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function filterClinicTagIds(array $ids): array
    {
        if (!$ids) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $s = $this->pdo->prepare("SELECT id FROM patient_tags WHERE clinic_id = ? AND id IN ($in)");
        $s->execute(array_merge([$this->clinicId], $ids));
        return array_map('intval', $s->fetchAll(\PDO::FETCH_COLUMN));
    }

    // ========================================================================
    //  COLOR MARKERS  (clinic-owned → patient_clinic_color_markers)
    // ========================================================================
    public function setMarker($patientId)
    {
        if (!$this->needClinic()) return;
        try {
            $pid = (int)$patientId;
            $d = $this->body();
            $color = $this->safeColor($d['color_code'] ?? ($d['color'] ?? ''));
            if ($color === null) {
                // empty/invalid → clear
                $this->pdo->prepare("DELETE FROM patient_clinic_color_markers WHERE patient_id = ? AND clinic_id = ?")
                    ->execute([$pid, $this->clinicId]);
                return $this->json(['ok' => true, 'cleared' => true]);
            }
            $s = $this->pdo->prepare("
                INSERT INTO patient_clinic_color_markers (patient_id, clinic_id, color_code)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE color_code = VALUES(color_code)
            ");
            $s->execute([$pid, $this->clinicId, $color]);
            return $this->json(['ok' => true, 'color_code' => $color]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========================================================================
    //  PATIENT — edit basics (operational scope: NO delete)
    // ========================================================================
    public function updatePatientBasics($id)
    {
        if (!$this->auth->check()) return $this->json(['error' => 'Unauthorized'], 401);
        try {
            $id = (int)$id;
            $d = \App\Lib\DigitNormalizer::normalizePatientNumericFields($this->body());
            $sets = []; $params = [];
            foreach (['first_name', 'last_name', 'phone', 'alt_phone', 'national_id', 'address', 'emergency_contact', 'emergency_phone'] as $f) {
                if (!array_key_exists($f, $d)) continue;
                $v = trim((string)$d[$f]);
                if (($f === 'first_name' || $f === 'last_name') && $v === '') return $this->json(['error' => 'الاسم الأول واسم العائلة مطلوبان'], 422);
                $sets[] = "$f = ?"; $params[] = $v;
            }
            if (array_key_exists('gender', $d) && in_array($d['gender'], ['Male', 'Female'], true)) { $sets[] = 'gender = ?'; $params[] = $d['gender']; }
            if (!empty($d['dob'])) { $sets[] = 'dob = ?'; $params[] = $d['dob']; }
            elseif (isset($d['age']) && $d['age'] !== '' && is_numeric($d['age'])) {
                $sets[] = 'dob = ?'; $params[] = date('Y-m-d', strtotime('-' . (int)$d['age'] . ' years'));
            }
            if (!empty($d['clinic_id'])) {
                $ck = $this->pdo->prepare("SELECT 1 FROM clinics WHERE id = ? AND is_active = 1");
                $ck->execute([(int)$d['clinic_id']]);
                if ($ck->fetchColumn()) { $sets[] = 'clinic_id = ?'; $params[] = (int)$d['clinic_id']; }
            }
            if (!$sets) return $this->json(['ok' => true]);
            $params[] = $id;
            $this->pdo->prepare("UPDATE patients SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
            return $this->json(['ok' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Profile header data: this patient's clinic marker + tags + the clinic tag list. */
    public function patientOrg($id)
    {
        if (!$this->needClinic()) return;
        try {
            $pid = (int)$id;
            $mk = $this->pdo->prepare("SELECT color_code FROM patient_clinic_color_markers WHERE patient_id = ? AND clinic_id = ?");
            $mk->execute([$pid, $this->clinicId]);
            $marker = $mk->fetchColumn() ?: null;
            $tg = $this->pdo->prepare("
                SELECT t.id, t.name, t.color, t.icon
                FROM patient_tag_assignments ta
                JOIN patient_tags t ON t.id = ta.tag_id AND t.clinic_id = ?
                WHERE ta.patient_id = ?
            ");
            $tg->execute([$this->clinicId, $pid]);
            $tags = $tg->fetchAll(\PDO::FETCH_ASSOC);
            $all = $this->pdo->prepare("SELECT id, name, color, icon FROM patient_tags WHERE clinic_id = ? ORDER BY name ASC");
            $all->execute([$this->clinicId]);
            return $this->json(['ok' => true, 'marker' => $marker, 'tags' => $tags, 'all_tags' => $all->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Bulk marker+tags for a set of patient ids (used to decorate the table view). */
    public function patientOrgBulk()
    {
        if (!$this->needClinic()) return;
        try {
            $ids = array_values(array_filter(array_map('intval', explode(',', (string)($_GET['ids'] ?? '')))));
            if (!$ids) return $this->json(['ok' => true, 'markers' => (object)[], 'tags' => (object)[]]);
            $ids = array_slice($ids, 0, 200);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $mk = $this->pdo->prepare("SELECT patient_id, color_code FROM patient_clinic_color_markers WHERE clinic_id = ? AND patient_id IN ($in)");
            $mk->execute(array_merge([$this->clinicId], $ids));
            $markers = [];
            foreach ($mk->fetchAll(\PDO::FETCH_ASSOC) as $r) $markers[(string)$r['patient_id']] = $r['color_code'];
            $tg = $this->pdo->prepare("
                SELECT ta.patient_id, t.name, t.color
                FROM patient_tag_assignments ta
                JOIN patient_tags t ON t.id = ta.tag_id AND t.clinic_id = ?
                WHERE ta.patient_id IN ($in)
            ");
            $tg->execute(array_merge([$this->clinicId], $ids));
            $tags = [];
            foreach ($tg->fetchAll(\PDO::FETCH_ASSOC) as $r) $tags[(string)$r['patient_id']][] = ['name' => $r['name'], 'color' => $r['color']];
            return $this->json(['ok' => true, 'markers' => (object)$markers, 'tags' => (object)$tags]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========================================================================
    //  ADMINISTRATIVE FILES  (patient_files, audience='administrative')
    //  Secretary uploads operational docs (ID / insurance / receipt) — it never
    //  sees clinical attachments (audience NULL/clinical).
    // ========================================================================
    private function filesDir(): string
    {
        $dir = __DIR__ . '/../../uploads/patients/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir;
    }

    public function listFiles($id)
    {
        if (!$this->needClinic()) return;
        try {
            $s = $this->pdo->prepare("
                SELECT id, original_filename, file_path, file_type, file_size, category, created_at
                FROM patient_files WHERE patient_id = ? AND audience = 'administrative'
                ORDER BY created_at DESC
            ");
            $s->execute([(int)$id]);
            return $this->json(['ok' => true, 'files' => $s->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function uploadFile($id)
    {
        if (!$this->needClinic()) return;
        try {
            $pid = (int)$id;
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) return $this->json(['error' => 'لم يتم رفع ملف'], 400);
            $file = $_FILES['file'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            if (!in_array($file['type'], $allowed, true)) return $this->json(['error' => 'نوع الملف غير مسموح'], 400);
            if ($file['size'] > 5 * 1024 * 1024) return $this->json(['error' => 'الحجم أكبر من 5 ميجابايت'], 400);
            $cat = (isset($_POST['category']) && preg_match('/^[a-z_]{1,40}$/', $_POST['category'])) ? $_POST['category'] : 'other';
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fname = 'secadmin_' . $pid . '_' . time() . '_' . uniqid() . ($ext ? '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext) : '');
            if (!move_uploaded_file($file['tmp_name'], $this->filesDir() . $fname)) return $this->json(['error' => 'تعذّر حفظ الملف'], 500);
            $s = $this->pdo->prepare("
                INSERT INTO patient_files (patient_id, original_filename, file_path, file_type, file_size, description, uploaded_by, audience, category, created_at)
                VALUES (?, ?, ?, ?, ?, '', ?, 'administrative', ?, NOW())
            ");
            $s->execute([$pid, $file['name'], 'uploads/patients/' . $fname, $file['type'], $file['size'], $this->auth->user()['id'], $cat]);
            return $this->json(['ok' => true, 'file_id' => (int)$this->pdo->lastInsertId()]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function viewFile($fileId)
    {
        if (!$this->needClinic()) { http_response_code(403); echo 'Forbidden'; return; }
        $s = $this->pdo->prepare("SELECT original_filename, file_path, file_type FROM patient_files WHERE id = ? AND audience = 'administrative'");
        $s->execute([(int)$fileId]);
        $f = $s->fetch(\PDO::FETCH_ASSOC);
        if (!$f) { http_response_code(404); echo 'Not found'; return; }
        $path = realpath(__DIR__ . '/../../' . $f['file_path']);
        $base = realpath(__DIR__ . '/../../uploads/patients/');
        if (!$path || !$base || strpos($path, $base) !== 0 || !is_file($path)) { http_response_code(404); echo 'Missing'; return; }
        header('Content-Type: ' . ($f['file_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . basename($f['original_filename']) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    public function deleteFile($fileId)
    {
        if (!$this->needClinic()) return;
        try {
            $s = $this->pdo->prepare("SELECT file_path FROM patient_files WHERE id = ? AND audience = 'administrative'");
            $s->execute([(int)$fileId]);
            $fp = $s->fetchColumn();
            if ($fp === false) return $this->json(['error' => 'غير موجود'], 404);
            $path = realpath(__DIR__ . '/../../' . $fp);
            $base = realpath(__DIR__ . '/../../uploads/patients/');
            if ($path && $base && strpos($path, $base) === 0 && is_file($path)) @unlink($path);
            $this->pdo->prepare("DELETE FROM patient_files WHERE id = ? AND audience = 'administrative'")->execute([(int)$fileId]);
            return $this->json(['ok' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ========================================================================
    //  LIST  (server-side, global patients + clinic organization overlay)
    // ========================================================================
    public function list()
    {
        if (!$this->needClinic()) return;
        try {
            [$sql, $params, $countSql, $countParams, $page, $perPage] = $this->buildListQuery();
            $total = (int)($this->execScalar($countSql, $countParams));
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $patients = $this->attachOrganization($patients);
            return $this->json([
                'ok' => true,
                'data' => [
                    'patients' => $patients,
                    'total'    => $total,
                    'page'     => $page,
                    'per_page' => $perPage,
                    'pages'    => $perPage > 0 ? (int)ceil($total / $perPage) : 1,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportCsv()
    {
        if (!$this->needClinic()) return;
        try {
            [$sql, $params] = $this->buildListQuery(true); // no LIMIT
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $this->attachOrganization($stmt->fetchAll(\PDO::FETCH_ASSOC));

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="patients.csv"');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel/Arabic
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'الاسم', 'الجنس', 'السن', 'الهاتف', 'هاتف بديل', 'الرقم القومي', 'آخر زيارة', 'عدد الزيارات', 'الوسوم']);
            foreach ($rows as $r) {
                $age = !empty($r['dob']) ? (int)((time() - strtotime($r['dob'])) / 31557600) : '';
                $tags = implode(' | ', array_map(function ($t) { return $t['name']; }, $r['tags'] ?? []));
                fputcsv($out, [
                    $r['id'],
                    trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                    $r['gender'] ?? '',
                    $age,
                    $r['phone'] ?? '',
                    $r['alt_phone'] ?? '',
                    $r['national_id'] ?? '',
                    $r['last_visit'] ?? '',
                    $r['total_appointments'] ?? 0,
                    $tags,
                ]);
            }
            fclose($out);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Shared filtered query builder for list() + exportCsv().
     * Returns [sql, params, countSql, countParams, page, perPage] (or [sql, params] when $noLimit).
     */
    private function buildListQuery(bool $noLimit = false): array
    {
        $g = $_GET;
        $where = [];
        $params = [];
        $joins = [];

        // search (name / phone / national id)
        $search = trim((string)($g['search'] ?? ''));
        if ($search !== '') {
            $where[] = "(CONCAT(p.first_name,' ',p.last_name) LIKE ? OR p.phone LIKE ? OR p.alt_phone LIKE ? OR p.national_id LIKE ?)";
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }
        // gender
        if (in_array(($g['gender'] ?? ''), ['Male', 'Female'], true)) {
            $where[] = "p.gender = ?"; $params[] = $g['gender'];
        }
        // age min/max (years)
        if (isset($g['age_min']) && $g['age_min'] !== '') {
            $where[] = "TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) >= ?"; $params[] = (int)$g['age_min'];
        }
        if (isset($g['age_max']) && $g['age_max'] !== '') {
            $where[] = "TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) <= ?"; $params[] = (int)$g['age_max'];
        }
        // folder filter (clinic-owned) or 'unfiled'
        $folder = $g['folder'] ?? '';
        if ($folder !== '' && $folder !== 'all') {
            if ($folder === 'unfiled') {
                $where[] = "p.id NOT IN (SELECT pfp.patient_id FROM patient_folder_patients pfp
                            JOIN patient_folders f ON f.id = pfp.folder_id WHERE f.clinic_id = ?)";
                $params[] = $this->clinicId;
            } else {
                $joins[] = "JOIN patient_folder_patients pfp ON pfp.patient_id = p.id AND pfp.folder_id = ?";
                // folder param goes with the join — must precede WHERE params, handled below
                $folderId = (int)$folder;
            }
        }
        // color markers (clinic)
        $colors = array_values(array_filter((array)($g['color'] ?? []), function ($c) { return $this->safeColor($c) !== null; }));
        if ($colors) {
            $cin = implode(',', array_fill(0, count($colors), '?'));
            $joins[] = "JOIN patient_clinic_color_markers m ON m.patient_id = p.id AND m.clinic_id = ? AND m.color_code IN ($cin)";
            $colorJoinParams = array_merge([$this->clinicId], $colors);
        }
        // tags (clinic) — patient must have ALL? use ANY (IN)
        $tags = array_values(array_filter(array_map('intval', (array)($g['tag'] ?? []))));
        if ($tags) {
            $tin = implode(',', array_fill(0, count($tags), '?'));
            $joins[] = "JOIN patient_tag_assignments ta ON ta.patient_id = p.id AND ta.tag_id IN ($tin)";
            $tagJoinParams = $tags;
        }

        // Assemble JOIN params in the order joins appear.
        $joinParams = [];
        foreach ($joins as $j) {
            if (strpos($j, 'patient_folder_patients pfp ON') !== false) $joinParams[] = $folderId;
            elseif (strpos($j, 'patient_clinic_color_markers') !== false) $joinParams = array_merge($joinParams, $colorJoinParams);
            elseif (strpos($j, 'patient_tag_assignments ta') !== false) $joinParams = array_merge($joinParams, $tagJoinParams);
        }

        $joinSql = implode("\n", $joins);
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // last-visit range needs the aggregated subquery; apply via HAVING
        $having = [];
        $havingParams = [];
        if (!empty($g['last_visit_from'])) { $having[] = 'last_visit >= ?'; $havingParams[] = $g['last_visit_from']; }
        if (!empty($g['last_visit_to']))   { $having[] = 'last_visit <= ?'; $havingParams[] = $g['last_visit_to']; }
        if (($g['last_visit'] ?? '') === 'never') { $having[] = 'last_visit IS NULL'; }
        $havingSql = $having ? ('HAVING ' . implode(' AND ', $having)) : '';

        // sort
        $sortMap = [
            'name'   => 'p.last_name, p.first_name',
            'age'    => 'p.dob',
            'last_visit' => 'last_visit',
            'visits' => 'total_appointments',
            'created' => 'p.created_at',
        ];
        $sortKey = $sortMap[$g['sort'] ?? 'name'] ?? $sortMap['name'];
        $order = (strtolower($g['order'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

        $selectCore = "
            SELECT DISTINCT p.id, p.first_name, p.last_name, p.dob, p.gender, p.phone, p.alt_phone, p.national_id, p.created_at,
                   (SELECT MAX(a.date) FROM appointments a WHERE a.patient_id = p.id) AS last_visit,
                   (SELECT COUNT(*) FROM appointments a WHERE a.patient_id = p.id) AS total_appointments
            FROM patients p
            $joinSql
            $whereSql
            $havingSql
        ";
        // Order of params: joins, then where, then having.
        $allParams = array_merge($joinParams, $params, $havingParams);

        if ($noLimit) {
            return [$selectCore . " ORDER BY $sortKey $order", $allParams];
        }

        $page = max(1, (int)($g['page'] ?? 1));
        $perPage = (int)($g['per_page'] ?? 20);
        if ($perPage <= 0 || $perPage > 200) $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $sql = $selectCore . " ORDER BY $sortKey $order LIMIT $perPage OFFSET $offset";

        // count: wrap the core (DISTINCT + HAVING make a simple COUNT unreliable)
        $countSql = "SELECT COUNT(*) FROM ( " . $selectCore . " ) cnt";
        return [$sql, $allParams, $countSql, $allParams, $page, $perPage];
    }

    private function execScalar(string $sql, array $params)
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($params);
        return $s->fetchColumn();
    }

    /** Attach this clinic's color marker + tags to each patient row. */
    private function attachOrganization(array $patients): array
    {
        if (!$patients) return $patients;
        $ids = array_map(function ($p) { return (int)$p['id']; }, $patients);
        $in = implode(',', array_fill(0, count($ids), '?'));

        // markers (clinic)
        $mk = $this->pdo->prepare("SELECT patient_id, color_code FROM patient_clinic_color_markers WHERE clinic_id = ? AND patient_id IN ($in)");
        $mk->execute(array_merge([$this->clinicId], $ids));
        $markers = [];
        foreach ($mk->fetchAll(\PDO::FETCH_ASSOC) as $r) $markers[(int)$r['patient_id']] = $r['color_code'];

        // tags (clinic)
        $tg = $this->pdo->prepare("
            SELECT ta.patient_id, t.id, t.name, t.color, t.icon
            FROM patient_tag_assignments ta
            JOIN patient_tags t ON t.id = ta.tag_id AND t.clinic_id = ?
            WHERE ta.patient_id IN ($in)
        ");
        $tg->execute(array_merge([$this->clinicId], $ids));
        $tagsByPatient = [];
        foreach ($tg->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $tagsByPatient[(int)$r['patient_id']][] = ['id' => (int)$r['id'], 'name' => $r['name'], 'color' => $r['color'], 'icon' => $r['icon']];
        }

        foreach ($patients as &$p) {
            $pid = (int)$p['id'];
            $p['marker'] = $markers[$pid] ?? null;
            $p['tags'] = $tagsByPatient[$pid] ?? [];
        }
        unset($p);
        return $patients;
    }
}
