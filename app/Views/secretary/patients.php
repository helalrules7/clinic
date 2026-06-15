<?php
    // Server-render the secretary's clinic for the Add Patient dropdown.
    try {
        $__cal_pdo  = \App\Config\Database::getInstance()->getConnection();
        $__cal_auth = new \App\Lib\Auth();
        $__cal_user = $__cal_auth->user();
        if (!empty($__cal_user['clinic_id'])) {
            $__s = $__cal_pdo->prepare("SELECT id, code, name_ar, name_en FROM clinics WHERE is_active = 1 AND id = ? ORDER BY sort_order, id");
            $__s->execute([(int)$__cal_user['clinic_id']]);
            $__calClinics = $__s->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $__calClinics = $__cal_pdo->query("SELECT id, code, name_ar, name_en FROM clinics WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(\PDO::FETCH_ASSOC);
        }
    } catch (\Throwable $__e) {
        $__calClinics = [];
    }
    $__clinicVisuals = [
        'riyadh' => ['icon' => 'bi-buildings-fill', 'color' => '#0d6efd'],
        'kfs'    => ['icon' => 'bi-hospital-fill',  'color' => '#10b981'],
    ];
    $__c0 = $__calClinics[0] ?? null;
    $__v0 = $__c0 ? ($__clinicVisuals[$__c0['code']] ?? ['icon' => 'bi-building', 'color' => '#6c757d']) : ['icon' => 'bi-building', 'color' => '#6c757d'];
?>
<!-- Patients Header -->
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<?php require __DIR__ . '/partials/sec-icons-bundle.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <?= sec_dash_icon_inline('patients', 'page') ?>
                إدارة المرضى
            </h4>
            <div class="d-flex align-items-center ms-3" style="padding-bottom: 10px !important;">
                <label class="form-label mb-0 me-2" for="patientsAutoRefresh">
                    <small class="text-muted arabic-text">تحديث تلقائي</small>
                </label>
                <div class="toggle-switch-wrapper">
                    <input type="checkbox" class="toggle-switch" id="patientsAutoRefresh" 
                           onchange="togglePatientsAutoRefresh(this.checked)">
                </div>
            </div>
        </div>
        <p class="text-muted mb-0 arabic-text">عرض وإدارة سجلات المرضى</p>
        <div class="mt-2">
            <small class="text-muted arabic-text">
                <i class="bi bi-keyboard me-1"></i>
                اختصارات: 
                • مريض جديد <kbd class="me-1">N</kbd> أو <kbd class="me-1">ى</kbd> أو <kbd class="me-1">Ctrl+N</kbd> 
                • البحث <kbd class="me-1">F</kbd> أو <kbd class="me-1">ب</kbd>
                <kbd>Esc</kbd> إغلاق
            </small>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                <?= sec_dash_icon_inline('patient-plus', 'md') ?>
                مريض جديد
                <span class="ms-2">
                    <kbd>N</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ى</kbd>
                </span>
            </button>
            <button class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#searchModal" 
                    title="استخدم F أو ب للبحث في المرضى">
                <?= sec_dash_icon_inline('search', 'md') ?>
                البحث
                <span class="ms-2">
                    <kbd>F</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ب</kbd>
                </span>
            </button>
        </div>
    </div>
</div>

<?php
$patientTrends = $patientTrends ?? ['total' => [], 'active' => [], 'new' => [], 'payments' => []];
$patientTrendDeltas = $patientTrendDeltas ?? ['total' => 0, 'active' => 0, 'new' => 0, 'payments' => 0];
?>
<script type="application/json" id="secPatientsTrends"><?= json_encode($patientTrends, JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="secPatientsStatsInitial"><?= json_encode([
    'total' => (int)($stats['total'] ?? 0),
    'active' => (int)($stats['active'] ?? 0),
    'recent' => (int)($stats['recent'] ?? 0),
    'total_paid' => (float)($stats['total_paid'] ?? 0),
], JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="secPatientsTrendDeltas"><?= json_encode($patientTrendDeltas, JSON_UNESCAPED_UNICODE) ?></script>

<!-- Patient Statistics -->
<div class="row stats-cards-wrapper sec-mini-stats sec-patients-stats mb-4">
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-primary">
                <div class="mini-stat-icon"><?= sec_dash_icon('patients', 'stat') ?></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPatStatTotal"><?= (int)($stats['total'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">إجمالي المرضى</span>
                </div>
                <div class="mini-stat-chart" id="chartPatTotal"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPatTotal"><span>--</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-success">
                <div class="mini-stat-icon"><?= sec_dash_icon('user-check', 'stat') ?></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPatStatActive"><?= (int)($stats['active'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">مرضى نشطين</span>
                </div>
                <div class="mini-stat-chart" id="chartPatActive"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPatActive"><span>--</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-warning">
                <div class="mini-stat-icon"><?= sec_dash_icon('patient-plus', 'stat') ?></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPatStatRecent"><?= (int)($stats['recent'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">جدد (٣٠ يوم)</span>
                </div>
                <div class="mini-stat-chart" id="chartPatNew"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPatNew"><span>--</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-info">
                <div class="mini-stat-icon"><?= sec_dash_icon('credit-card', 'stat') ?></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPatStatPaid"><?= number_format((float)($stats['total_paid'] ?? 0), 0) ?></span>
                    <span class="mini-stat-label arabic-text">مدفوعات إجمالية</span>
                </div>
                <div class="mini-stat-chart" id="chartPatPayments"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPatPayments"><span>--</span></div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 arabic-text">
            <?= sec_dash_icon_inline('funnel', 'page') ?>
            فلاتر البحث
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label arabic-text">البحث</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="الاسم، الهاتف، الرقم القومي...">
                </div>
                <div class="col-md-2">
                    <label for="gender" class="form-label arabic-text">الجنس</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">الكل</option>
                        <?php foreach ($genderOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $gender === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="age_range" class="form-label arabic-text">الفئة العمرية</label>
                    <select class="form-select" id="age_range" name="age_range">
                        <option value="">الكل</option>
                        <?php foreach ($ageRangeOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $ageRange === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="last_visit" class="form-label arabic-text">آخر زيارة</label>
                    <select class="form-select" id="last_visit" name="last_visit">
                        <option value="">الكل</option>
                        <?php foreach ($lastVisitOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $lastVisit === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                        <a href="/secretary/patients" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Patients Table -->
<!-- v11: view-mode toggle (table = existing server-rendered; cards/folders = API-driven) -->
<div class="sec-view-toggle mb-3">
    <button type="button" class="sec-view-btn active" data-view="table">جدول</button>
    <button type="button" class="sec-view-btn" data-view="cards">كروت</button>
    <button type="button" class="sec-view-btn" data-view="folders">مجلدات</button>
</div>

<div class="card" id="secTableView">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-table me-2"></i>
                    قائمة المرضى
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2 header-actions-row">
                        <div class="input-group input-group-sm quick-search-input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control border-start-0 border-end-0"
                                   id="quickSearch"
                                   placeholder="بحث سريع..."
                                   autocomplete="off"
                                   style="border-left: none; border-right: none;">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="clearQuickSearch" style="display: none;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <label for="paginationLimit" class="form-label mb-0 me-2 text-muted small per-page-label arabic-text">عرض:</label>
                            <select class="form-select form-select-sm" id="paginationLimit" style="width: auto;">
                                <option value="10" class="arabic-text">10</option>
                                <option value="20" selected class="arabic-text">20</option>
                                <option value="30" class="arabic-text">30</option>
                                <option value="50" class="arabic-text">50</option>
                                <option value="all" class="arabic-text">الكل</option>
                            </select>
                        </div>
                        <div class="text-muted total-count-label">
                            <small class="arabic-text">المجموع: <span id="totalPatientsCount"><?= count($patients) ?></span> مريض</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0" style="min-height: 600px; overflow: hidden;">
        <div class="table-responsive" style="min-height: 700px; overflow-y: hidden; overflow-x: auto;">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text sortable-header text-end" dir="rtl">بيانات المريض <i class="bi bi-arrow-down-up ms-1" style="opacity: 0.5;"></i></th>
                        <th class="arabic-text sortable-header text-end" dir="rtl">التواصل <i class="bi bi-arrow-down-up ms-1" style="opacity: 0.5;"></i></th>
                        <th class="arabic-text sortable-header text-end" dir="rtl">العمر <i class="bi bi-arrow-down-up ms-1" style="opacity: 0.5;"></i></th>
                        <th class="arabic-text sortable-header text-end" dir="rtl">آخر زيارة <i class="bi bi-arrow-down-up ms-1" style="opacity: 0.5;"></i></th>
                        <th class="arabic-text sortable-header text-end" dir="rtl">آخر عيادة <i class="bi bi-arrow-down-up ms-1" style="opacity: 0.5;"></i></th>
                        <th class="arabic-text sortable-header text-end" dir="rtl">إجمالي المدفوعات <i class="bi bi-arrow-down-up ms-1" style="opacity: 0.5;"></i></th>
                        <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="patientsTableBody">
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0 arabic-text">لا توجد سجلات مرضى</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <?php
                            $age = $viewHelper->calculateAge($patient['dob']);
                            $lastVisit = $patient['last_visit'] ? $viewHelper->formatDateSimple($patient['last_visit']) : 'لم يزر بعد';
                            $firstName = $patient['first_name'] ?? '';
                            $lastName = $patient['last_name'] ?? '';
                            $fullName = trim($firstName . ' ' . $lastName);
                            // Get Arabic characters properly
                            $firstChar = !empty($firstName) ? mb_substr($firstName, 0, 1, 'UTF-8') : '؟';
                            $lastChar = !empty($lastName) ? mb_substr($lastName, 0, 1, 'UTF-8') : '؟';
                            // For Arabic text, use Arabic characters directly
                            if (preg_match('/[\x{0600}-\x{06FF}]/u', $firstName . $lastName)) {
                                $avatarInitials = $firstChar . $lastChar;
                            } else {
                                $avatarInitials = strtoupper($firstChar) . '.' . strtoupper($lastChar);
                            }
                            $avatarClass = ($patient['gender'] ?? '') === 'Female' ? 'avatar-circle avatar-female me-3' : 'avatar-circle avatar-male me-3';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="<?= $avatarClass ?>">
                                            <?= $avatarInitials ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 arabic-text patient-hover-name" data-patient-id="<?= (int)$patient['id'] ?>"><?= htmlspecialchars($fullName) ?></h6>
                                            <small class="text-muted">ID: #<?= $patient['id'] ?></small>
                                            <?php if (!empty($patient['gender'])): ?>
                                                <br><small class="text-muted">
                                                    <i class="bi bi-<?= $patient['gender'] === 'Female' ? 'gender-female' : 'gender-male' ?> me-1"></i>
                                                    <?= $patient['gender'] === 'Female' ? 'أنثى' : 'ذكر' ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($patient['phone'])): ?>
                                        <div class="phone-number-container mt-1" style="position: relative; display: inline-block;">
                                            <a href="tel:<?= htmlspecialchars($patient['phone']) ?>" 
                                               class="phone-number-link" 
                                               style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                                <i class="bi bi-phone me-1"></i>
                                                <?= htmlspecialchars($patient['phone']) ?>
                                            </a>
                                            <span class="phone-htooltip">
                                                <div class="phone-actions">
                                                    <a href="tel:<?= htmlspecialchars($patient['phone']) ?>" class="phone-action-btn" title="اتصال">
                                                        <i class="bi bi-phone-vibrate"></i>
                                                        <span>اتصال</span>
                                                    </a>
                                                    <a href="https://wa.me/+2<?= preg_replace('/[^0-9]/', '', $patient['phone']) ?>" target="_blank" class="phone-action-btn whatsapp-btn" title="واتساب">
                                                        <i class="bi bi-whatsapp"></i>
                                                        <span>واتساب</span>
                                                    </a>
                                                </div>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">غير متوفر</span>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['alt_phone'])): ?>
                                        <div class="phone-number-container mt-1" style="position: relative; display: inline-block;">
                                            <a href="tel:<?= htmlspecialchars($patient['alt_phone']) ?>" 
                                               class="phone-number-link" 
                                               style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                                <i class="bi bi-telephone-plus me-1"></i>
                                                <small><?= htmlspecialchars($patient['alt_phone']) ?></small>
                                            </a>
                                            <span class="phone-htooltip">
                                                <div class="phone-actions">
                                                    <a href="tel:<?= htmlspecialchars($patient['alt_phone']) ?>" class="phone-action-btn" title="اتصال">
                                                        <i class="bi bi-telephone-fill"></i>
                                                        <span>اتصال</span>
                                                    </a>
                                                    <a href="https://wa.me/+2<?= preg_replace('/[^0-9]/', '', $patient['alt_phone']) ?>" target="_blank" class="phone-action-btn whatsapp-btn" title="واتساب">
                                                        <i class="bi bi-whatsapp"></i>
                                                        <span>واتساب</span>
                                                    </a>
                                                </div>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['national_id'])): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-card-text me-1"></i>
                                            <small class="text-muted"><?= htmlspecialchars($patient['national_id']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['emergency_contact'])): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-person-heart me-1"></i>
                                            <small class="text-muted"><?= htmlspecialchars($patient['emergency_contact']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $age !== 'غير محدد' ? $age . ' سنة' : '<span class="text-muted">غير محدد</span>' ?>
                                </td>
                                <td>
                                    <?= $patient['last_visit'] ?
                                        '<span class="badge bg-success arabic-text">' . $lastVisit . '</span>' :
                                        '<span class="badge bg-secondary arabic-text">لم يزر بعد</span>'
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $__lcCode = strtolower((string)($patient['last_clinic_code'] ?? ''));
                                    $__lcName = $patient['last_clinic_name_ar'] ?? $patient['last_clinic_name_en'] ?? null;
                                    /* Solid-colour badges — same shape as the "Last Visit" green
                                       date badge, just with a per-clinic background so the eye can
                                       distinguish them. No icon, per user feedback. */
                                    $__lcTheme = match ($__lcCode) {
                                        'riyadh' => ['bg' => '#166534', 'fg' => '#ffffff'],  // dark green
                                        'kfs'    => ['bg' => '#4c1d95', 'fg' => '#ffffff'],  // dark violet
                                        default  => ['bg' => '#334155', 'fg' => '#ffffff'],  // dark slate
                                    };
                                    ?>
                                    <?php if ($__lcName): ?>
                                        <span class="badge clinic-badge clinic-badge-<?= htmlspecialchars($__lcCode ?: 'none') ?> arabic-text" style="background: <?= $__lcTheme['bg'] ?>; color: <?= $__lcTheme['fg'] ?>; font-weight: 600; font-size: 0.7rem; padding: 0.35em 0.55em; border-radius: 6px;">
                                            <?= htmlspecialchars($__lcName) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($patient['total_paid'] ?? 0) > 0): ?>
                                        <span class="badge bg-success arabic-text"><?= number_format($patient['total_paid'], 2) ?> جنيه</span>
                                        <br><small class="text-muted"><?= $patient['total_appointments'] ?? 0 ?> زيارة</small>
                                    <?php else: ?>
                                        <span class="badge bg-secondary arabic-text">لم يدفع</span>
                                        <br><small class="text-muted"><?= $patient['total_appointments'] ?? 0 ?> زيارة</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="sec-patient-actions" role="group">
                                        <a href="/secretary/patients/<?= $patient['id'] ?>"
                                           class="btn btn-sm btn-outline-warning"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           data-bs-title="عرض تفاصيل المريض">
                                            <?= sec_dash_icon('eye', 'sm') ?>
                                        </a>
                                        <a href="/secretary/payments?patient_id=<?= $patient['id'] ?>"
                                           class="btn btn-sm btn-outline-info"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           data-bs-title="عرض المدفوعات">
                                            <?= sec_dash_icon('credit-card', 'sm') ?>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                data-bs-title="حجز موعد جديد"
                                                onclick="openBookingModal(<?= $patient['id'] ?>, '<?= htmlspecialchars($fullName, ENT_QUOTES) ?>')">
                                            <?= sec_dash_icon('calendar-plus', 'sm') ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- v11: API-driven Cards + Folders views (rendered by secretary-patients.js) -->
<div id="secCardsView" class="sec-view-pane" style="display:none"></div>
<div id="secFoldersView" class="sec-view-pane" style="display:none"></div>
<?php require __DIR__ . '/partials/sec-icons-scripts.php'; ?>
<script src="/app/Views/secretary/assets/js/sec-mini-stats.js?v=<?= file_exists(__DIR__ . '/assets/js/sec-mini-stats.js') ? filemtime(__DIR__ . '/assets/js/sec-mini-stats.js') : time() ?>"></script>
<script src="/app/Views/secretary/assets/js/sec-clinic-chip.js?v=<?= file_exists(__DIR__ . '/assets/js/sec-clinic-chip.js') ? filemtime(__DIR__ . '/assets/js/sec-clinic-chip.js') : time() ?>"></script>
<script src="/app/Views/secretary/assets/js/secretary-patients.js?v=<?= file_exists(__DIR__ . '/assets/js/secretary-patients.js') ? filemtime(__DIR__ . '/assets/js/secretary-patients.js') : time() ?>"></script>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-search me-2"></i>
                    البحث في المرضى
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
            </div>
            <div class="modal-body">
                <!-- Search Input -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="globalSearch" 
                               placeholder="ابحث بالاسم أو رقم الهاتف أو الرقم القومي..."
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-text d-flex justify-content-between align-items-center search-help-text">
                        <span class="search-instruction arabic-text">
                            <i class="bi bi-info-circle me-1"></i>
                            ابدأ بالكتابة للبحث تلقائياً
                        </span>
                        <small class="search-shortcut">
                            <kbd>Ctrl</kbd>+<kbd>F</kbd> للتركيز على البحث
                        </small>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="searchResults">
                    <!-- Loading State -->
                    <div id="searchLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden arabic-text">جاري البحث...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0 arabic-text">جاري البحث في المرضى...</p>
                    </div>

                    <!-- No Results -->
                    <div id="noResults" class="text-center py-4" style="display: none;">
                        <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2 arabic-text">لا توجد نتائج</h6>
                        <p class="text-muted mb-0 arabic-text">جرب مصطلحات بحث مختلفة</p>
                    </div>

                    <!-- Results Container -->
                    <div id="searchResultsList" class="search-results-container">
                        <!-- Results will be populated here -->
                    </div>

                    <!-- Initial State -->
                    <div id="searchInitial" class="text-center py-4">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2 arabic-text">البحث في المرضى</h6>
                        <p class="text-muted mb-0 arabic-text">أدخل الاسم أو رقم الهاتف أو الرقم القومي للبحث</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-person-plus me-2"></i>
                    إضافة مريض جديد
                </h5>
                <div class="keyboard-hint">
                    <span>اضغط</span>
                    <kbd>Esc</kbd>
                    <span>للإغلاق</span>
                </div>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="addPatientMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row add-patient-form-row">
                        <!-- Basic Information -->
                        <div class="col-md-6 add-patient-col">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-person me-1"></i>
                                المعلومات الأساسية
                            </h6>
                            
                            <div class="mb-3">
                                <label for="firstName" class="form-label arabic-text">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lastName" class="form-label arabic-text">الاسم الأخير <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="age" class="form-label arabic-text">العمر (بالسنوات)</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" placeholder="أدخل العمر بالسنوات">
                                <div class="form-text arabic-text">بديل: أدخل العمر لحساب تاريخ الميلاد تلقائياً</div>
                            </div>

                            <div class="mb-3">
                                <label for="dob" class="form-label arabic-text">تاريخ الميلاد</label>
                                <input type="date" class="form-control" id="dob" name="dob">
                                <div class="form-text arabic-text">تاريخ ميلاد المريض (إذا ترك فارغاً سيتم استخدام تاريخ اليوم)</div>
                            </div>

                            <div class="mb-3">
                                <label for="gender" class="form-label arabic-text">الجنس <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="Male" class="arabic-text">ذكر</option>
                                    <option value="Female" class="arabic-text">أنثى</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="form-text text-danger arabic-text"><strong>مطلوب:</strong> غير الجنس إذا لزم الأمر</div>
                            </div>

                            <div class="mb-3">
                                <label for="patientClinic" class="form-label arabic-text">العيادة <span class="text-danger">*</span></label>
                                <div class="input-group clinic-input-group" style="--clinic-color: <?= $__v0['color'] ?>;">
                                    <span class="input-group-text clinic-icon-chip" id="patientClinicIcon" aria-hidden="true">
                                        <i class="bi <?= $__v0['icon'] ?>"></i>
                                    </span>
                                    <select class="form-select arabic-text<?= count($__calClinics) === 1 ? ' is-clinic-locked' : '' ?>" id="patientClinic" name="clinic_id" required
                                            <?php if (count($__calClinics) === 1): ?> aria-readonly="true" tabindex="-1" <?php endif; ?>>
                                        <?php if (count($__calClinics) !== 1): ?>
                                            <option value="">اختر العيادة...</option>
                                        <?php endif; ?>
                                        <?php foreach ($__calClinics as $__c): ?>
                                            <option value="<?= (int)$__c['id'] ?>"
                                                    data-clinic-code="<?= htmlspecialchars($__c['code']) ?>"
                                                    <?= count($__calClinics) === 1 ? 'selected' : '' ?>><?= htmlspecialchars($__c['name_ar'] ?: $__c['name_en']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-md-6 add-patient-col">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-telephone me-1"></i>
                                معلومات الاتصال
                            </h6>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label arabic-text">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required maxlength="20">
                                <div class="invalid-feedback"></div>
                                <div class="form-text arabic-text">رقم الاتصال الأساسي</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="altPhone" class="form-label arabic-text">رقم هاتف بديل</label>
                                <input type="tel" class="form-control" id="altPhone" name="alt_phone" maxlength="20">
                                <div class="form-text arabic-text">رقم هاتف إضافي (اختياري)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nationalId" class="form-label arabic-text">الرقم القومي</label>
                                <input type="text" class="form-control" id="nationalId" name="national_id" maxlength="20">
                                <div class="form-text arabic-text">الرقم القومي (اختياري)</div>
                            </div>
                            
                            <div class="mb-0 add-patient-address-field">
                                <label for="address" class="form-label arabic-text">العنوان</label>
                                <textarea class="form-control add-patient-address-input" id="address" name="address" rows="3" maxlength="500"></textarea>
                                <div class="form-text arabic-text">عنوان المنزل (اختياري)</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success" id="addPatientSubmit" title="حفظ المريض - اضغط 'Ctrl+S'">
                        <i class="bi bi-person-plus me-1"></i>
                        <span class="btn-text">إضافة المريض</span>
                        <small class="ms-2 text-white-50">
                            <kbd style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); font-size: 0.7rem;">Ctrl+S</kbd>
                        </small>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Helper functions
function calculateAge(dob) {
    if (!dob) return 'غير محدد';
    
    const today = new Date();
    const birthDate = new Date(dob);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

function formatDate(dateString) {
    if (!dateString) return 'غير محدد';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
let searchTimeout;
let currentSearchRequest;

function searchPatients(query) {
    const searchLoading = document.getElementById('searchLoading');
    const searchInitial = document.getElementById('searchInitial');
    const noResults = document.getElementById('noResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    // Hide all states
    searchInitial.style.display = 'none';
    noResults.style.display = 'none';
    searchResultsList.style.display = 'none';
    
    if (!query || query.trim().length < 2) {
        searchInitial.style.display = 'block';
        return;
    }
    
    // Show loading
    searchLoading.style.display = 'block';
    
    // Cancel previous request
    if (currentSearchRequest) {
        currentSearchRequest.abort();
    }
    
    // Create new request
    currentSearchRequest = new AbortController();
    
    const searchUrl = (window.DigitNormalizer && window.DigitNormalizer.patientSearchUrl)
        ? window.DigitNormalizer.patientSearchUrl(query.trim())
        : `/api/patients/search?q=${encodeURIComponent(query.trim())}`;
    fetch(searchUrl, {
        signal: currentSearchRequest.signal
    })
    .then(response => response.json())
    .then(data => {
        searchLoading.style.display = 'none';
        
        if (data.ok && data.data && data.data.length > 0) {
            const normTerm = (window.DigitNormalizer && window.DigitNormalizer.normalizeSearchQuery)
                ? window.DigitNormalizer.normalizeSearchQuery(query.trim()) : query.trim();
            displaySearchResults(data.data, normTerm);
        } else {
            noResults.style.display = 'block';
        }
    })
    .catch(error => {
        searchLoading.style.display = 'none';
        if (error.name !== 'AbortError') {
            console.error('Search error:', error);
            noResults.style.display = 'block';
        }
    });
}

function displaySearchResults(patients, searchTerm) {
    const searchResultsList = document.getElementById('searchResultsList');
    let html = '';
    
    patients.forEach(patient => {
        const fullName = `${patient.first_name} ${patient.last_name}`;
        const age = patient.dob ? calculateAge(patient.dob) : 'غير محدد';
        const lastVisit = patient.last_visit ? formatDate(patient.last_visit) : 'لم يزر';
        
        // Highlight search terms
        const highlightedName = highlightSearchTerm(fullName, searchTerm);
        const highlightedPhone = highlightSearchTerm(patient.phone || '', searchTerm);
        const highlightedNationalId = highlightSearchTerm(patient.national_id || '', searchTerm);
        
        html += `
            <div class="search-result-item" onclick="selectSearchResult(${patient.id})">
                <div class="d-flex align-items-center">
                    <div class="search-result-avatar ${patient.gender === 'Female' ? 'avatar-female' : 'avatar-male'} me-3">
                        ${getAvatarInitials(patient.first_name, patient.last_name)}
                    </div>
                    <div class="search-result-info flex-grow-1">
                        <h6 class="mb-1 arabic-text">${highlightedName}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    ${highlightedPhone || 'لا يوجد هاتف'}
                                </small>
                                ${patient.alt_phone ? `<small class="text-muted d-block">
                                    <i class="bi bi-telephone-plus me-1"></i>
                                    ${patient.alt_phone}
                                </small>` : ''}
                                ${patient.national_id ? `<small class="text-muted d-block">
                                    <i class="bi bi-card-text me-1"></i>
                                    الرقم القومي: ${highlightedNationalId}
                                </small>` : ''}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-person me-1"></i>
                                    العمر: ${age} سنة
                                </small>
                                ${patient.emergency_contact ? `<small class="text-muted d-block">
                                    <i class="bi bi-person-heart me-1"></i>
                                    طوارئ: ${patient.emergency_contact}
                                </small>` : ''}
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-primary me-2 arabic-text">${patient.total_appointments || 0} زيارة</span>
                            <span class="badge bg-success arabic-text">آخر زيارة: ${lastVisit}</span>
                        </div>
                    </div>
                    <div class="search-result-actions ms-3">
                        <div class="sec-patient-actions sec-patient-actions--stack">
                            <a href="/secretary/patients/${patient.id}" class="btn btn-sm btn-outline-warning arabic-text">
                                <?= sec_dash_icon_inline('eye', 'sm') ?>عرض
                            </a>
                            <a href="/secretary/payments?patient_id=${patient.id}" class="btn btn-sm btn-outline-info arabic-text">
                                <?= sec_dash_icon_inline('credit-card', 'sm') ?>دفعات
                            </a>
                            <a href="/secretary/bookings?patient_id=${patient.id}" class="btn btn-sm btn-outline-success arabic-text">
                                <?= sec_dash_icon_inline('calendar-plus', 'sm') ?>حجز
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    searchResultsList.innerHTML = html;
    searchResultsList.style.display = 'block';
}

function selectSearchResult(patientId) {
    window.location.href = `/secretary/patients/${patientId}`;
}

function highlightSearchTerm(text, searchTerm) {
    if (!text || !searchTerm) return text;
    
    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<span class="search-highlight">$1</span>');
}

function getAvatarInitials(firstName, lastName) {
    if (!firstName || !lastName) {
        return '؟؟';
    }
    
    // Check if text contains Arabic characters
    const hasArabic = /[\u0600-\u06FF]/.test(firstName + lastName);
    
    if (hasArabic) {
        // For Arabic text, use Arabic characters directly
        const firstChar = firstName.charAt(0);
        const lastChar = lastName.charAt(0);
        return firstChar + lastChar;
    } else {
        // For non-Arabic text, use uppercase with dot
        const firstChar = firstName.charAt(0).toUpperCase();
        const lastChar = lastName.charAt(0).toUpperCase();
        return firstChar + '.' + lastChar;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals with proper backdrop configuration
    const initializeModals = () => {
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(modalEl => {
            // Skip if already initialized
            if (bootstrap.Modal.getInstance(modalEl)) return;
            
            // Get backdrop setting from data attribute or default to true
            const backdropSetting = modalEl.dataset.bsBackdrop !== undefined 
                ? (modalEl.dataset.bsBackdrop === 'static' ? 'static' : modalEl.dataset.bsBackdrop === 'false' ? false : true)
                : true;
            
            // Get keyboard setting
            const keyboardSetting = modalEl.dataset.bsKeyboard !== undefined 
                ? modalEl.dataset.bsKeyboard !== 'false'
                : true;
            
            // Initialize modal with proper config
            new bootstrap.Modal(modalEl, {
                backdrop: backdropSetting,
                keyboard: keyboardSetting,
                focus: true
            });
        });
    };
    
    // Initialize modals immediately
    initializeModals();
    
    // Re-initialize modals if new ones are added dynamically
    const observer = new MutationObserver(() => {
        initializeModals();
        // Re-initialize draggable after modals are initialized
        setTimeout(initializeDraggableModals, 100);
    });
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Make modals draggable
    function initializeDraggableModals() {
    /* Drag/center/animation unified in layouts/modal-kit.js. No-op. */
    return;
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            // Skip alertModal - it has its own draggable implementation
            if (modal.id === 'alertModal') {
                return;
            }
            const modalDialog = modal.querySelector('.modal-dialog');
            if (!modalDialog) return;
            
            // Skip if already initialized
            if (modalDialog.dataset.draggableInitialized === 'true') return;
            modalDialog.dataset.draggableInitialized = 'true';
            
            let isDragging = false;
            let currentX;
            let currentY;
            let initialX;
            let initialY;
            let xOffset = 0;
            let yOffset = 0;
            
            // Make modal header the drag handle
            const modalHeader = modal.querySelector('.modal-header');
            if (!modalHeader) return;
            
            modalHeader.style.cursor = 'move';
            
            // Remove existing listeners to avoid duplicates
            const newHeader = modalHeader.cloneNode(true);
            modalHeader.parentNode.replaceChild(newHeader, modalHeader);
            const freshHeader = modal.querySelector('.modal-header');
            
            freshHeader.addEventListener('mousedown', dragStart);
            
            function dragStart(e) {
                // Don't drag if clicking on buttons or inputs
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                    return;
                }
                
                // Only start dragging if clicking on header (not on title text)
                if (e.target === freshHeader || (freshHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
                    // Get current transform values
                    const transform = modalDialog.style.transform;
                    if (transform) {
                        const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                        if (match) {
                            xOffset = parseFloat(match[1]) || 0;
                            yOffset = parseFloat(match[2]) || 0;
                        }
                    }
                    
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;
                    
                    // Store initial mouse position to detect if it's a drag or click
                    const startX = e.clientX;
                    const startY = e.clientY;
                    
                    // Set a flag to track if mouse moved
                    let hasMoved = false;
                    
                    function checkMove(moveEvent) {
                        const deltaX = Math.abs(moveEvent.clientX - startX);
                        const deltaY = Math.abs(moveEvent.clientY - startY);
                        if (deltaX > 5 || deltaY > 5) {
                            hasMoved = true;
                            isDragging = true;
                            modalDialog.style.transition = 'none';
                            moveEvent.preventDefault();
                            moveEvent.stopPropagation();
                        }
                    }
                    
                    function handleMove(moveEvent) {
                        if (hasMoved) {
                            drag(moveEvent);
                        } else {
                            checkMove(moveEvent);
                        }
                    }
                    
                    function handleEnd(endEvent) {
                        if (!hasMoved) {
                            // It was just a click, allow normal behavior
                            document.removeEventListener('mousemove', handleMove);
                            document.removeEventListener('mouseup', handleEnd);
                            return;
                        }
                        dragEnd(endEvent);
                        document.removeEventListener('mousemove', handleMove);
                        document.removeEventListener('mouseup', handleEnd);
                    }
                    
                    document.addEventListener('mousemove', handleMove);
                    document.addEventListener('mouseup', handleEnd);
                }
            }
            
            function drag(e) {
                if (isDragging) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent modal from closing
                    currentX = e.clientX - initialX;
                    currentY = e.clientY - initialY;
                    
                    xOffset = currentX;
                    yOffset = currentY;
                    
                    setTranslate(currentX, currentY, modalDialog);
                }
            }
            
            function dragEnd(e) {
                initialX = currentX;
                initialY = currentY;
                isDragging = false;
                modalDialog.style.transition = '';
            }
            
            function setTranslate(xPos, yPos, el) {
                // Get viewport dimensions
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                
                // Get modal dimensions
                const modalRect = el.getBoundingClientRect();
                const modalWidth = modalRect.width;
                const modalHeight = modalRect.height;
                
                // Get the original position (center of viewport)
                const originalLeft = (viewportWidth - modalWidth) / 2;
                const originalTop = 50; // Keep at least 50px from top
                
                // Calculate boundaries relative to original position
                // Allow movement within viewport bounds
                const minX = -(originalLeft - 20); // Allow 20px from left edge
                const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
                const minY = -(originalTop - 20); // Allow 20px from top
                const maxY = viewportHeight - modalHeight - originalTop - 20; // Allow 20px from bottom
                
                // Constrain movement
                const constrainedX = Math.max(minX, Math.min(maxX, xPos));
                const constrainedY = Math.max(minY, Math.min(maxY, yPos));
                
                el.style.transform = `translate(${constrainedX}px, ${constrainedY}px)`;
            }
            
            // Reset position when modal is hidden
            modal.addEventListener('hidden.bs.modal', function() {
                xOffset = 0;
                yOffset = 0;
                modalDialog.style.transform = '';
                modalDialog.dataset.draggableInitialized = 'false';
            });
        });
    }
    
    // Initialize draggable modals after a short delay to ensure modals are ready
    setTimeout(initializeDraggableModals, 200);
    
    // Initialize table touch/drag support for mobile
    initTableTouchSupport();
    
    // Quick search
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    
    if (quickSearch) {
        quickSearch.addEventListener('input', debounce(function() {
            if (clearQuickSearch) {
                clearQuickSearch.style.display = quickSearch.value.trim() ? 'block' : 'none';
            }
            filterPatientsTable();
        }, 300));
        
        if (clearQuickSearch) {
            clearQuickSearch.addEventListener('click', function() {
                quickSearch.value = '';
                clearQuickSearch.style.display = 'none';
                filterPatientsTable();
                quickSearch.focus();
            });
        }
    }
    
    // Global search
    const globalSearch = document.getElementById('globalSearch');
    const clearSearch = document.getElementById('clearSearch');
    const searchModal = document.getElementById('searchModal');
    
    if (globalSearch) {
        globalSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchPatients(this.value);
            }, 300);
        });
        
        if (clearSearch) {
            clearSearch.addEventListener('click', function() {
                globalSearch.value = '';
                globalSearch.focus();
                document.getElementById('searchInitial').style.display = 'block';
                document.getElementById('searchLoading').style.display = 'none';
                document.getElementById('noResults').style.display = 'none';
                document.getElementById('searchResultsList').style.display = 'none';
            });
        }
    }
    
    // Focus search input when modal opens
    if (searchModal) {
        searchModal.addEventListener('shown.bs.modal', function() {
            globalSearch.focus();
        });
        
        // Reset search when modal closes
        searchModal.addEventListener('hidden.bs.modal', function() {
            globalSearch.value = '';
            document.getElementById('searchInitial').style.display = 'block';
            document.getElementById('searchLoading').style.display = 'none';
            document.getElementById('noResults').style.display = 'none';
            document.getElementById('searchResultsList').style.display = 'none';
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const isModalOpen = document.querySelector('.modal.show');
        const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                             e.target.contentEditable === 'true';
        
        // Open search modal with 'F' key or Arabic 'ب' key
        const searchKeys = ['f', 'ب'];
        const isSearchKey = searchKeys.includes(e.key.toLowerCase()) || searchKeys.includes(e.key);
        
        if (isSearchKey && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#searchModal"]').click();
        }
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                e.preventDefault();
                bootstrap.Modal.getInstance(openModal).hide();
            }
        }
    });
    
    // Initialize table touch/drag support
    function initTableTouchSupport() {
        const tableWrapper = document.querySelector('.table-responsive');
        if (!tableWrapper) return;
        
        let isScrolling = false;
        let startX = 0;
        let scrollLeft = 0;
        
        // Touch support
        tableWrapper.addEventListener('touchstart', function(e) {
            isScrolling = true;
            startX = e.touches[0].pageX - tableWrapper.offsetLeft;
            scrollLeft = tableWrapper.scrollLeft;
        }, { passive: true });
        
        tableWrapper.addEventListener('touchmove', function(e) {
            if (!isScrolling) return;
            e.preventDefault();
            const x = e.touches[0].pageX - tableWrapper.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed multiplier
            tableWrapper.scrollLeft = scrollLeft - walk;
        }, { passive: false });
        
        tableWrapper.addEventListener('touchend', function() {
            isScrolling = false;
        }, { passive: true });
        
        // Mouse drag support
        let isDown = false;
        let startMouseX = 0;
        let scrollStartLeft = 0;
        
        tableWrapper.addEventListener('mousedown', function(e) {
            isDown = true;
            tableWrapper.style.cursor = 'grabbing';
            startMouseX = e.pageX - tableWrapper.offsetLeft;
            scrollStartLeft = tableWrapper.scrollLeft;
        });
        
        tableWrapper.addEventListener('mouseleave', function() {
            isDown = false;
            tableWrapper.style.cursor = 'grab';
        });
        
        tableWrapper.addEventListener('mouseup', function() {
            isDown = false;
            tableWrapper.style.cursor = 'grab';
        });
        
        tableWrapper.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - tableWrapper.offsetLeft;
            const walk = (x - startMouseX) * 2;
            tableWrapper.scrollLeft = scrollStartLeft - walk;
        });
        
        // Set initial cursor style
        tableWrapper.style.cursor = 'grab';
    }
    
    // Initialize Bootstrap Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
});

function filterPatientsBySearch(query) {
    const rows = document.querySelectorAll('#patientsTableBody tr');
    const searchTerm = query.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openBookingModal(patientId, patientName) {
    // Redirect to bookings page with patient pre-selected
    window.location.href = `/secretary/bookings?patient_id=${patientId}`;
}

// Add Patient functionality - Age and Date of Birth conversion
function initializeAddPatientModal() {
    const addPatientForm = document.getElementById('addPatientForm');
    const addPatientModal = document.getElementById('addPatientModal');
    const addPatientSubmit = document.getElementById('addPatientSubmit');
    const addPatientMessage = document.getElementById('addPatientMessage');
    
    // Reset form when modal opens
    addPatientModal.addEventListener('show.bs.modal', function() {
        addPatientForm.reset();
        addPatientForm.classList.remove('was-validated');
        hideMessage();
        resetSubmitButton();

        // Clinic dropdown is server-rendered in this view (and locked when
        // only the secretary's own clinic is visible), so nothing to do here.

        // Focus on first name field
        setTimeout(() => {
            document.getElementById('firstName').focus();
        }, 300);
    });
    
    // Handle form submission
    addPatientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!addPatientForm.checkValidity()) {
            addPatientForm.classList.add('was-validated');
            showMessage('يرجى ملء جميع الحقول المطلوبة بشكل صحيح.', 'error');
            return;
        }
        
        // Additional validation
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const gender = document.getElementById('gender').value;
        
        if (!firstName || !lastName || !phone) {
            showMessage('الاسم الأول والاسم الأخير ورقم الهاتف مطلوبة.', 'error');
            return;
        }
        
        if (!gender) {
            showMessage('يرجى اختيار جنس المريض.', 'error');
            document.getElementById('gender').focus();
            return;
        }

        const clinicSelect = document.getElementById('patientClinic');
        if (clinicSelect && !clinicSelect.value) {
            showMessage('يرجى اختيار العيادة.', 'error');
            clinicSelect.focus();
            return;
        }

        // Validate phone number format
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        const phoneRegex = /^(\+\d{1,3})?\d{7,15}$/;
        if (!phoneRegex.test(cleanPhone)) {
            showMessage('يرجى إدخال رقم هاتف صحيح (7-15 رقم، مع إمكانية إضافة رمز الدولة).', 'error');
            return;
        }

        // Submit form
        submitPatientForm();
    });
    
    function submitPatientForm() {
        const formData = new FormData(addPatientForm);
        
        // Show loading state
        setSubmitButtonLoading(true);
        hideMessage();
        
        // Send AJAX request
        fetch('/api/patients', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            setSubmitButtonLoading(false);
            
            if (data.ok) {
                // Success
                showMessage('تم إضافة المريض بنجاح!', 'success');
                
                // Reset form
                addPatientForm.reset();
                addPatientForm.classList.remove('was-validated');
                
                // Close modal after delay and refresh page
                setTimeout(() => {
                    bootstrap.Modal.getInstance(addPatientModal).hide();
                    // Refresh the page to show the new patient
                    window.location.reload();
                }, 1500);
                
            } else {
                // Error from server
                const errorMsg = data.error || data.message || 'فشل في إضافة المريض. يرجى المحاولة مرة أخرى.';
                showMessage(errorMsg, 'error');
                
                // Show validation errors if available
                if (data.details) {
                    showValidationErrors(data.details);
                }
            }
        })
        .catch(error => {
            setSubmitButtonLoading(false);
            showMessage('حدث خطأ أثناء إضافة المريض. يرجى المحاولة مرة أخرى.', 'error');
        });
    }
    
    function showMessage(message, type) {
        addPatientMessage.className = `alert alert-${type === 'error' ? 'danger' : type}`;
        addPatientMessage.textContent = message;
        addPatientMessage.classList.remove('d-none');
    }
    
    function hideMessage() {
        addPatientMessage.classList.add('d-none');
    }
    
    function setSubmitButtonLoading(loading) {
        const btnText = addPatientSubmit.querySelector('.btn-text');
        const spinner = addPatientSubmit.querySelector('.spinner-border');
        
        if (loading) {
            addPatientSubmit.disabled = true;
            btnText.textContent = 'جاري الإضافة...';
            spinner.classList.remove('d-none');
        } else {
            addPatientSubmit.disabled = false;
            btnText.textContent = 'إضافة المريض';
            spinner.classList.add('d-none');
        }
    }
    
    function resetSubmitButton() {
        setSubmitButtonLoading(false);
    }
    
    function showValidationErrors(errors) {
        // Clear previous validation errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
        
        // Show new validation errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[field];
                }
            }
        });
    }
    
    // Clear validation errors on input
    addPatientForm.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    });
    
    // Age and Date of Birth conversion
    const dobInput = document.getElementById('dob');
    const ageInput = document.getElementById('age');
    
    // Convert age to date of birth
    ageInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        if (age && age > 0 && age <= 150) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            const birthDate = new Date(birthYear, today.getMonth(), today.getDate());
            dobInput.value = birthDate.toISOString().split('T')[0];
            
            // Clear age field after conversion
            setTimeout(() => {
                this.value = '';
            }, 1000);
        }
    });
    
    // Convert date of birth to age
    dobInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age >= 0 && age <= 150) {
                ageInput.placeholder = `العمر المحسوب: ${age} سنة`;
                setTimeout(() => {
                    ageInput.placeholder = 'أدخل العمر بالسنوات';
                }, 3000);
            }
        }
    });
}

// Initialize add patient modal when DOM is ready
// Initialize phone tooltip click handlers
function initializePhoneTooltips() {
    // Use event delegation for dynamically added phone numbers
    document.addEventListener('click', function(e) {
        // Check if click is on a phone number link
        const phoneLink = e.target.closest('.phone-number-link');
        if (phoneLink) {
            e.preventDefault();
            e.stopPropagation();
            
            const container = phoneLink.closest('.phone-number-container');
            if (container) {
                // Toggle tooltip visibility
                const isActive = container.classList.contains('active');
                
                // Close all other tooltips
                document.querySelectorAll('.phone-number-container.active').forEach(activeContainer => {
                    if (activeContainer !== container) {
                        activeContainer.classList.remove('active');
                    }
                });
                
                // Toggle current tooltip
                if (isActive) {
                    container.classList.remove('active');
                } else {
                    container.classList.add('active');
                }
            }
        } else {
            // Close all tooltips when clicking outside
            document.querySelectorAll('.phone-number-container.active').forEach(container => {
                container.classList.remove('active');
            });
        }
    });
    
    // Prevent tooltip from closing when clicking inside it
    document.addEventListener('click', function(e) {
        if (e.target.closest('.phone-htooltip')) {
            e.stopPropagation();
        }
    });
}

// Sort and filter functions
let currentSortColumn = null;
let currentSortDirection = 'asc';
let filteredPatients = [];

function sortTable(column) {
    const tbody = document.getElementById('patientsTableBody');
    if (!tbody) return;
    
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Toggle sort direction if clicking same column
    if (currentSortColumn === column) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortColumn = column;
        currentSortDirection = 'asc';
    }
    
    // Remove existing sort indicators
    document.querySelectorAll('.sort-indicator').forEach(ind => ind.remove());
    
    // Add sort indicator to current column header
    const headers = document.querySelectorAll('thead th');
    headers.forEach((header, index) => {
        if (index === column) {
            const indicator = document.createElement('i');
            indicator.className = `bi bi-arrow-${currentSortDirection === 'asc' ? 'up' : 'down'} sort-indicator ms-1`;
            header.appendChild(indicator);
        }
    });
    
    rows.sort((a, b) => {
        const aText = a.cells[column]?.textContent.trim() || '';
        const bText = b.cells[column]?.textContent.trim() || '';
        
        // Try to parse as number
        const aNum = parseFloat(aText.replace(/[^0-9.]/g, ''));
        const bNum = parseFloat(bText.replace(/[^0-9.]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return currentSortDirection === 'asc' ? aNum - bNum : bNum - aNum;
        }
        
        // String comparison
        return currentSortDirection === 'asc' 
            ? aText.localeCompare(bText, 'ar')
            : bText.localeCompare(aText, 'ar');
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Re-initialize phone tooltips after sorting
    setTimeout(() => {
        initializePhoneTooltips();
    }, 100);
}

// Add sort functionality to table headers
function addSortFunctionality() {
    const headers = document.querySelectorAll('thead th');
    headers.forEach((header, index) => {
        // Skip action column (last column)
        if (index < headers.length - 1) {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => sortTable(index));
            header.title = 'انقر للفرز';
        }
    });
}

// Filter patients table
function filterPatientsTable() {
    const searchTerm = document.getElementById('quickSearch')?.value.toLowerCase() || '';
    const tbody = document.getElementById('patientsTableBody');
    if (!tbody) return;
    
    const rows = tbody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const isVisible = text.includes(searchTerm);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });
    
    // Update total count
    const totalCountEl = document.getElementById('totalPatientsCount');
    if (totalCountEl) {
        totalCountEl.textContent = visibleCount;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeAddPatientModal();
    if (typeof window.bindClinicIconSync === 'function') {
        window.bindClinicIconSync('patientClinic', 'patientClinicIcon');
    }
    initializePhoneTooltips();
    addSortFunctionality();
    
    // Check for openModal query parameter and open the corresponding modal
    const urlParams = new URLSearchParams(window.location.search);
    const openModal = urlParams.get('openModal');
    
    if (openModal === 'addPatient') {
        // Open add patient modal
        setTimeout(() => {
            const addPatientModal = document.getElementById('addPatientModal');
            if (addPatientModal) {
                const modal = new bootstrap.Modal(addPatientModal);
                modal.show();
            }
        }, 100);
    }
    
    // Quick search filter
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    if (quickSearch) {
        quickSearch.addEventListener('input', debounce(function () {
            if (clearQuickSearch) {
                clearQuickSearch.style.display = quickSearch.value.trim() ? 'block' : 'none';
            }
            filterPatientsTable();
        }, 300));
    }
    
    if (clearQuickSearch && quickSearch) {
        clearQuickSearch.addEventListener('click', function () {
            quickSearch.value = '';
            clearQuickSearch.style.display = 'none';
            filterPatientsTable();
            quickSearch.focus();
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize auto-refresh toggle from localStorage
    const autoRefreshEnabled = getPatientsAutoRefreshState();
    const toggleSwitch = document.getElementById('patientsAutoRefresh');
    if (toggleSwitch) {
        toggleSwitch.checked = autoRefreshEnabled;
    }
    
    // Start auto-refresh if enabled
    if (autoRefreshEnabled) {
        startPatientsAutoRefresh();
    }
});

// Auto-refresh state management for patients
let patientsRefreshInterval = null;

function getPatientsAutoRefreshState() {
    const saved = localStorage.getItem('patientsAutoRefresh');
    return saved === null ? true : saved === 'true'; // Default is ON
}

function savePatientsAutoRefreshState(enabled) {
    localStorage.setItem('patientsAutoRefresh', enabled ? 'true' : 'false');
}

function togglePatientsAutoRefresh(enabled) {
    savePatientsAutoRefreshState(enabled);
    
    if (enabled) {
        if (!patientsRefreshInterval) {
            startPatientsAutoRefresh();
        }
    } else {
        if (patientsRefreshInterval) {
            clearInterval(patientsRefreshInterval);
            patientsRefreshInterval = null;
        }
    }
}

function startPatientsAutoRefresh() {
    // Clear any existing interval
    if (patientsRefreshInterval) {
        clearInterval(patientsRefreshInterval);
    }
    
    patientsRefreshInterval = setInterval(() => {
        const addPatientModal = document.getElementById('addPatientModal');
        const searchModal = document.getElementById('searchModal');
        
        // Don't refresh if any modal is open
        const isModalOpen = addPatientModal?.classList.contains('show') ||
                           searchModal?.classList.contains('show') ||
                           document.querySelector('.modal.show') !== null;
        
        if (!isModalOpen) {
            refreshPatientsData();
        }
    }, 60000); // 60 seconds
}

// ---------------------------------------------------------------------------
// v12_perf: live, scroll-safe refresh of the (server-rendered) patients table.
// The row markup is owned by PHP, so instead of rebuilding rows in JS we fetch
// the current URL, lift out #patientsTableBody and swap it in a SINGLE
// synchronous assignment — there is no clear-then-refill gap, so the page
// height never collapses and the scroll position is preserved. A signature
// guard skips the swap when the rows are byte-for-byte unchanged, and we pause
// while the user is sorting / searching / has a modal open so their current
// view is never clobbered.
// ---------------------------------------------------------------------------
let __secPatientsTableSig = null;

function __secHash(s) {
    let h = 5381;
    for (let i = 0; i < s.length; i++) h = ((h << 5) + h + s.charCodeAt(i)) | 0;
    return s.length + ':' + (h >>> 0);
}

function __secPatientsTablePaused() {
    if (document.querySelector('.modal.show')) return true;
    if (typeof currentSortColumn !== 'undefined' && currentSortColumn !== null) return true; // client-side sort active
    const qs = document.getElementById('quickSearch');
    if (qs && (document.activeElement === qs || qs.value.trim() !== '')) return true;        // searching
    return false;
}

function refreshSecretaryPatientsTable() {
    const tbody = document.getElementById('patientsTableBody');
    if (!tbody) return;
    if (__secPatientsTablePaused()) return;

    fetch(window.location.href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(r => r.text())
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const fresh = doc.getElementById('patientsTableBody');
        if (!fresh) return;

        const sig = __secHash(fresh.innerHTML);
        // First successful poll just records the baseline (the table already
        // shows this exact server render). Comparing fetched-vs-fetched from
        // here on avoids a spurious first swap from live-DOM serialization
        // differences (e.g. Bootstrap tooltip init mutating row attributes).
        if (__secPatientsTableSig === null) { __secPatientsTableSig = sig; return; }
        if (sig === __secPatientsTableSig) return;   // unchanged -> leave DOM (and scroll) alone
        __secPatientsTableSig = sig;

        // state may have changed during the fetch — re-check before touching the DOM
        if (__secPatientsTablePaused()) return;
        const live = document.getElementById('patientsTableBody');
        if (!live) return;

        live.innerHTML = fresh.innerHTML;            // single synchronous swap = scroll-safe

        // phone tooltips are delegated on document (they handle new rows
        // automatically); only the per-element Bootstrap tooltips need re-init.
        try {
            live.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                const inst = bootstrap.Tooltip.getInstance(el);
                if (inst) inst.dispose();
                new bootstrap.Tooltip(el);
            });
        } catch (e) {}

        // keep the header total in sync with the freshly rendered rows
        const freshTotal = doc.getElementById('totalPatientsCount');
        const liveTotal = document.getElementById('totalPatientsCount');
        if (freshTotal && liveTotal) liveTotal.textContent = freshTotal.textContent;
    })
    .catch(() => { /* silent background refresh */ });
}

// Function to refresh patients data via AJAX
function refreshPatientsData() {
    // v12_perf: live-refresh the table rows in place (scroll-safe), alongside
    // the background stats update below.
    refreshSecretaryPatientsTable();

    // Get current filter values
    const search = document.getElementById('search')?.value || '';
    const gender = document.getElementById('gender')?.value || '';
    const ageRange = document.getElementById('age_range')?.value || '';
    const lastVisit = document.getElementById('last_visit')?.value || '';
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (gender) params.append('gender', gender);
    if (ageRange) params.append('age_range', ageRange);
    if (lastVisit) params.append('last_visit', lastVisit);
    params.append('page', '1'); // Always get first page for stats
    
    // Show subtle loading indicator
    const tableBody = document.getElementById('patientsTableBody');
    if (tableBody) {
        tableBody.parentElement.classList.add('table-loading');
    }
    
    fetch(`/api/secretary/patients?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.data) {
            // Update statistics cards
            if (data.data.stats) {
                updatePatientsStats(data.data.stats, data.data.trends, data.data.trendDeltas);
            }
            
            // Note: We don't update the table automatically to avoid disrupting user's current view
            // The stats are updated silently in the background
        }
    })
    .catch(error => {
        // Silently fail - don't show error to user for background refresh
        console.error('Error refreshing patients data:', error);
    })
    .finally(() => {
        // Remove loading indicator
        const tableBody = document.getElementById('patientsTableBody');
        if (tableBody) {
            tableBody.parentElement.classList.remove('table-loading');
        }
    });
}

window.SEC_PATIENTS_MINI_CARDS = [
    { chartId: 'chartPatTotal', trendId: 'trendPatTotal', trendKey: 'total', statKey: 'total', valueId: 'secPatStatTotal' },
    { chartId: 'chartPatActive', trendId: 'trendPatActive', trendKey: 'active', statKey: 'active', valueId: 'secPatStatActive' },
    { chartId: 'chartPatNew', trendId: 'trendPatNew', trendKey: 'new', statKey: 'recent', valueId: 'secPatStatRecent', syncStatToSeries: false },
    { chartId: 'chartPatPayments', trendId: 'trendPatPayments', trendKey: 'payments', statKey: 'total_paid', valueId: 'secPatStatPaid', syncStatToSeries: false, format: 'money' }
];

function initPatientsMiniStats() {
    if (!window.secMiniStats) return;
    window.secMiniStats.init(window.SEC_PATIENTS_MINI_CARDS, {
        trendsId: 'secPatientsTrends',
        statsId: 'secPatientsStatsInitial',
        deltasId: 'secPatientsTrendDeltas'
    });
}

function updatePatientsStats(stats, trends, trendDeltas) {
    if (!stats) return;
    const payload = {
        total: stats.total || 0,
        active: stats.active || 0,
        recent: stats.recent || 0,
        total_paid: stats.total_paid || 0
    };
    if (window.secMiniStats && window.SEC_PATIENTS_MINI_CARDS) {
        window.secMiniStats.updateStatValues(window.SEC_PATIENTS_MINI_CARDS, payload);
        const t = trends || window.secMiniStats.readJsonScript('secPatientsTrends') || {};
        const d = trendDeltas || window.secMiniStats.readJsonScript('secPatientsTrendDeltas') || {};
        window.secMiniStats.refresh(window.SEC_PATIENTS_MINI_CARDS, t, payload, d, {
            trendsId: 'secPatientsTrends',
            statsId: 'secPatientsStatsInitial',
            deltasId: 'secPatientsTrendDeltas'
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initPatientsMiniStats();
});
</script>

<link href="/app/Views/doctor/assets/css/patients.css?v=<?= file_exists(__DIR__ . '/../../doctor/assets/css/patients.css') ? filemtime(__DIR__ . '/../../doctor/assets/css/patients.css') : time() ?>" rel="stylesheet">

<style>
/* RTL — icon spacing: sec-style.css §secretary icon spacing */
.text-start { text-align: right !important; }
.text-end { text-align: left !important; }
.justify-content-start { justify-content: flex-end !important; }
.justify-content-end { justify-content: flex-start !important; }

/* Arabic text styling */
.arabic-text {
    font-family: 'Cairo', Arial, sans-serif;
    direction: rtl;
    text-align: right;
}

/* Secretary specific styles */
.stat-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.stat-label {
    margin: 0;
    color: var(--muted);
    font-size: 0.875rem;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    direction: rtl;
    text-align: center;
    font-family: 'Cairo', 'Arial', sans-serif;
    line-height: 1;
    unicode-bidi: bidi-override;
}

/* Gender-based avatar colors */
.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.avatar-male:hover {
    background: #2980b9;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.avatar-female:hover {
    background: #c2185b;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: var(--text);
    background: var(--bg);
}

.table td {
    vertical-align: middle;
    border-top: 1px solid var(--border);
}

/* §25 — sec-patient-actions: sec-style.css */

/* Search Modal Styles */
.modal-content {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

/* RTL Modal Header Adjustments */
.modal-header {
    direction: rtl;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    position: relative;
    padding: 1rem 1.5rem;
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.modal-header .btn-close {
    order: -1;
    margin-left: 0;
    margin-right: 0;
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.modal-header .keyboard-hint {
    order: -1;
    position: absolute;
    left: 10% !important;
    right: auto !important;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
    z-index: 9;
    white-space: nowrap;
}

.modal-header .modal-title {
    order: 0;
    margin-right: auto;
    margin-left: 0;
    flex: 1;
    text-align: right;
    padding-right: 0;
    padding-left: 60px; /* Space for close button */
}

.modal-header:has(.keyboard-hint) .modal-title {
    padding-left: 120px; /* Extra space when keyboard-hint exists */
}

.modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

.main-content .form-control {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.main-content .form-control:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.main-content .input-group-text {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.search-results-container {
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background: var(--bg);
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-result-item:hover {
    border-color: var(--accent);
    background: var(--bg-alt);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-result-item:last-child {
    margin-bottom: 0;
}

.search-result-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.search-result-avatar.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.search-result-avatar.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.search-result-info h6 {
    margin-bottom: 5px;
    color: var(--text);
}

.search-result-info .text-muted {
    font-size: 0.9rem;
    color: var(--muted) !important;
}

.search-result-actions .btn {
    padding: 5px 10px;
    font-size: 0.85rem;
}

.search-highlight {
    background-color: rgba(255, 193, 7, 0.3);
    padding: 1px 3px;
    border-radius: 3px;
    font-weight: 600;
}

/* Keyboard shortcut styling */
kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-family: 'Courier New', 'Cairo', monospace;
    color: var(--text);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    min-width: 20px;
    text-align: center;
    display: inline-block;
}

.btn-primary kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

.btn-success kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

/* Arabic keyboard shortcut styling */
kbd[lang="ar"] {
    font-family: 'Cairo', 'Courier New', monospace;
    font-weight: 600;
}

/* Keyboard shortcut hint in modal */
.keyboard-hint {
    position: absolute;
    top: 10px;
    left: 15px;
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.keyboard-hint kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.65rem;
    padding: 1px 4px;
}

/* Badge styling for dark mode */
.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

/* Text muted styling */
.text-muted {
    color: var(--muted) !important;
}

/* Search help text styling for dark mode */
.search-help-text {
    background: rgba(var(--accent-rgb), 0.05);
    border: 1px solid rgba(var(--accent-rgb), 0.15);
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 8px;
    transition: all 0.2s ease;
}

.search-help-text:hover {
    background: rgba(var(--accent-rgb), 0.08);
    border-color: rgba(var(--accent-rgb), 0.2);
}

.search-help-text .search-instruction {
    color: var(--text);
    font-weight: 500;
    font-size: 0.875rem;
}

.search-help-text .search-instruction i {
    color: var(--accent);
    opacity: 0.8;
    margin-right: 4px;
}

.search-help-text .search-shortcut {
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 400;
}

.search-help-text kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.7rem;
    padding: 2px 6px;
    margin: 0 1px;
    border-radius: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Courier New', 'Cairo', monospace;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .keyboard-hint {
        position: static;
        margin-top: 10px;
    }
}

:root {
    --sidebar-width: 280px;
}

.dark {
}
/* Statistics Cards Styling */
.stat-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.stat-label {
    margin: 0;
    color: var(--muted);
    font-size: 0.875rem;
}

.stat-content {
    flex: 1;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    direction: rtl;
    text-align: center;
    font-family: 'Cairo', 'Arial', sans-serif;
    line-height: 1;
    unicode-bidi: bidi-override;
}

/* Gender-based avatar colors */
.avatar-male {
    background: #3498db; /* Sky blue for males */
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.avatar-female {
    background:rgb(255, 85, 224); /* Pink for females */
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

/* Hover effects */
.avatar-male:hover {
    background: #2980b9;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.avatar-female:hover {
    background:rgb(255, 85, 224); /* Pink for females */
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}

/* Default fallback for unknown gender */
.avatar-circle:not(.avatar-male):not(.avatar-female) {
    background: var(--accent);
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.3);
}

.card {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.card-header {
    background-color: transparent !important;
    border-bottom-color: var(--border);
    color: var(--text);
}

/* Patient list card — parity with doctor #patientsTableCard */
#secTableView .card-header .header-actions-row {
    flex-wrap: nowrap !important;
    align-items: center;
}
#secTableView .card-header .header-actions-row .total-count-label {
    white-space: nowrap;
    flex-shrink: 0;
}
#secTableView .table thead th,
#secTableView .table-dark th {
    padding: 15px !important;
    font-weight: 600;
    border-bottom: 2px solid var(--border) !important;
    vertical-align: middle;
}
#secTableView .table tbody td {
    padding: 15px;
    vertical-align: middle;
}

.table {
    background-color: var(--bg-dark);
    color: var(--text);
}

.table thead th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table-dark th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table tbody tr {
    background-color: var(--bg-dark);
    border-color: var(--border);
}

.table tbody tr:hover {
    background-color: var(--bg-alt);
}

.table td {
    background-color: var(--bg-dark);
    border-color: var(--border);
    color: var(--text);
}

/* §25 — sec-patient-actions: sec-style.css */

/* Search Modal Styles */
.modal-content {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

/* RTL Modal Header Adjustments */
.modal-header {
    direction: rtl;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    position: relative;
    padding: 1rem 1.5rem;
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.modal-header .btn-close {
    order: -1;
    margin-left: 0;
    margin-right: 0;
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.modal-header .keyboard-hint {
    order: -1;
    position: absolute;
    left: 10% !important;
    right: auto !important;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
    z-index: 9;
    white-space: nowrap;
}

.modal-header .modal-title {
    order: 0;
    margin-right: auto;
    margin-left: 0;
    flex: 1;
    text-align: right;
    padding-right: 0;
    padding-left: 60px; /* Space for close button */
}

.modal-header:has(.keyboard-hint) .modal-title {
    padding-left: 120px; /* Extra space when keyboard-hint exists */
}

.modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

.main-content .form-control {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.main-content .form-control:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.main-content .input-group-text {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.search-results-container {
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background: var(--bg);
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-result-item:hover {
    border-color: var(--accent);
    background: var(--bg-alt);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-result-item:last-child {
    margin-bottom: 0;
}

.search-result-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

/* Apply gender colors to search result avatars */
.search-result-avatar.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.search-result-avatar.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.search-result-avatar:not(.avatar-male):not(.avatar-female) {
    background: var(--accent);
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.3);
}

.search-result-info h6 {
    margin-bottom: 5px;
    color: var(--text);
}

.search-result-info .text-muted {
    font-size: 0.9rem;
    color: var(--muted) !important;
}

.search-result-actions .btn {
    padding: 5px 10px;
    font-size: 0.85rem;
}

.search-highlight {
    background-color: rgba(255, 193, 7, 0.3);
    padding: 1px 3px;
    border-radius: 3px;
    font-weight: 600;
}

#globalSearch:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

/* §24 — outline buttons: sec-style.css global gradient system */


.btn-secondary {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.btn-secondary:hover {
    background-color: var(--border);
    border-color: var(--border);
    color: var(--text);
}

/* Keyboard shortcut styling */
kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-family: 'Courier New', 'Cairo', monospace;
    color: var(--text);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    min-width: 20px;
    text-align: center;
    display: inline-block;
}

.btn-primary kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

.btn-success kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

/* Arabic keyboard shortcut styling */
kbd[lang="ar"] {
    font-family: 'Cairo', 'Courier New', monospace;
    font-weight: 600;
}

/* Keyboard shortcut hint in modal */
.keyboard-hint {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.keyboard-hint kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.65rem;
    padding: 1px 4px;
}

/* Badge styling for dark mode */
.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

/* Text muted styling */
.text-muted {
    color: var(--muted) !important;
}

/* Add Patient Modal Styling */
#addPatientModal .modal-content {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal /* RTL Modal Header Adjustments */
.modal-header {
    direction: rtl;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    position: relative;
    padding: 1rem 1.5rem;
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.modal-header .btn-close {
    order: -1;
    margin-left: 0;
    margin-right: 0;
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.modal-header .keyboard-hint {
    order: -1;
    position: absolute;
    left: 10% !important;
    right: auto !important;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
    z-index: 9;
    white-space: nowrap;
}

.modal-header .modal-title {
    order: 0;
    margin-right: auto;
    margin-left: 0;
    flex: 1;
    text-align: right;
    padding-right: 0;
    padding-left: 60px; /* Space for close button */
}

.modal-header:has(.keyboard-hint) .modal-title {
    padding-left: 120px; /* Extra space when keyboard-hint exists */
}

#addPatientModal .modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

#addPatientModal .form-label {
    color: var(--text);
    font-weight: 500;
}

#addPatientModal .form-control,
#addPatientModal .form-select {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal .form-control:focus,
#addPatientModal .form-select:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

#addPatientModal .form-text {
    color: var(--muted);
    font-size: 0.875rem;
}

#addPatientModal .text-primary {
    color: var(--accent) !important;
}

#addPatientModal .text-danger {
    color: #dc3545 !important;
}

#addPatientModal .invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
}

#addPatientModal .form-control.is-invalid,
#addPatientModal .form-select.is-invalid {
    border-color: #dc3545;
}

#addPatientModal .alert {
    border-radius: 8px;
    margin-bottom: 1rem;
}

#addPatientModal .alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #155724;
}

#addPatientModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

/* Button styling for add patient modal */
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    color: white;
}

.btn-success:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
    opacity: 0.65;
}

/* Form validation styling */
.was-validated .form-control:valid {
    border-color: #28a745;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
}

.was-validated .form-select:valid {
    border-color: #28a745;
}

.was-validated .form-select:invalid {
    border-color: #dc3545;
}

/* Spinner styling */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.1em;
}

/* Search help text styling for dark mode */
.search-help-text {
    background: rgba(var(--accent-rgb), 0.05);
    border: 1px solid rgba(var(--accent-rgb), 0.15);
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 8px;
    transition: all 0.2s ease;
}

.search-help-text:hover {
    background: rgba(var(--accent-rgb), 0.08);
    border-color: rgba(var(--accent-rgb), 0.2);
}

.search-help-text .search-instruction {
    color: var(--text);
    font-weight: 500;
    font-size: 0.875rem;
}

.search-help-text .search-instruction i {
    color: var(--accent);
    opacity: 0.8;
    margin-right: 4px;
}

.search-help-text .search-shortcut {
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 400;
}

.search-help-text kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.7rem;
    padding: 2px 6px;
    margin: 0 1px;
    border-radius: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Courier New', 'Cairo', monospace;
}

/* Delete Patient Modal Styles */
#deletePatientModal .modal-content,
#deletePatientConfirmModal .modal-content {
    background: var(--card);
    color: var(--text);
}

#deletePatientModal .modal-header,
#deletePatientConfirmModal .modal-header {
    background-color: #dc3545 !important;
    border-bottom-color: #dc3545;
}

#deletePatientModal .modal-footer,
#deletePatientConfirmModal .modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

#deletePatientModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

[data-bs-theme="dark"] #deletePatientModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #f5c6cb;
}

#deletePatientModal .alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border-color: #ffc107;
    color: #856404;
}

[data-bs-theme="dark"] #deletePatientModal .alert-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #ffeaa7;
}

#deletePatientModal .list-group-item {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

#deletePatientModal .card {
    background-color: var(--bg);
    border-color: #ffc107;
}

#deletePatientModal .card-body {
    background-color: var(--bg-alt);
}

/* §24 — btn-outline-danger: sec-style.css */

.btn-warning {
    background: #ffc107 !important;
    border-color: #ffc107 !important;
    color: white !important;
    border-radius: 10px !important;
}

.btn-warning:hover {
    background:rgb(255, 201, 47) !important;
    border-color:rgb(255, 201, 47) !important;
    color: white !important;
}

#deleteConfirmationText {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Courier New', monospace;
    font-weight: bold;
    letter-spacing: 2px;
}

#deleteConfirmationText:focus {
    background-color: var(--bg);
    border-color: #dc3545;
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

#deleteConfirmationText.is-valid {
    border-color: #28a745;
    background-color: var(--bg);
}

#deleteConfirmationText.is-invalid {
    border-color: #dc3545;
    background-color: var(--bg);
}

/* Arabic text styling for delete messages */
#deleteConfirmationMessage {
    font-family: 'Cairo', Arial, sans-serif;
    text-align: right;
    direction: rtl;
}

#deleteConfirmationMessage.alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #155724;
}

[data-bs-theme="dark"] #deleteConfirmationMessage.alert-success {
    background-color: rgba(40, 167, 69, 0.15);
    color: #d4edda;
}

#deleteConfirmationMessage.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border-color: #ffc107;
    color: #856404;
}

[data-bs-theme="dark"] #deleteConfirmationMessage.alert-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #fff3cd;
}

#deleteConfirmationMessage.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

[data-bs-theme="dark"] #deleteConfirmationMessage.alert-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #f8d7da;
}

/* Keyboard shortcuts info styling */
.text-muted kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.7rem;
    padding: 1px 4px;
    margin: 0 1px;
    border-radius: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Courier New', 'Cairo', monospace;
}

[data-bs-theme="dark"] .text-muted kbd {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
}

/* Delete help text styling for better visibility */
.delete-help-text {
    background-color: rgba(13, 110, 253, 0.1) !important;
    border: 1px solid rgba(13, 110, 253, 0.2) !important;
    border-radius: 6px !important;
    padding: 8px 12px !important;
    margin-top: 8px !important;
    color: var(--text) !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
}

[data-bs-theme="dark"] .delete-help-text {
    background-color: rgba(13, 110, 253, 0.15) !important;
    border-color: rgba(13, 110, 253, 0.3) !important;
    color: #ffffff !important;
}

[data-bs-theme="light"] .delete-help-text {
    background-color: rgba(13, 110, 253, 0.08) !important;
    border-color: rgba(13, 110, 253, 0.2) !important;
    color: #212529 !important;
}

/* Pagination Styling */
.card-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
    color: var(--text);
}

.pagination-info {
    font-family: 'Cairo', Arial, sans-serif;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Cairo', Arial, sans-serif;
    padding: 0.375rem 0.75rem;
    margin: 0 2px;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.pagination .page-link:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.3);
}

.pagination .page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.4);
}

.pagination .page-item.disabled .page-link {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--muted);
    opacity: 0.6;
    cursor: not-allowed;
}

.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
    border-radius: 6px;
}

.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Show/Hide pagination based on content */
#paginationContainer.d-none {
    display: none !important;
}

/* Pagination limit select styling */
#paginationLimit {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Cairo', Arial, sans-serif;
    font-size: 0.875rem;
    min-width: 80px;
}

#paginationLimit:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

/* Quick search styling */
#quickSearch {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Cairo', Arial, sans-serif;
    font-size: 0.875rem;
    border-radius: 0;
    border-left: none;
    border-right: none;
}

#quickSearch:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: none;
    z-index: 3;
}

#quickSearch::placeholder {
    color: var(--muted);
    font-style: italic;
}

.input-group-sm .input-group-text {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
    font-size: 0.875rem;
    border-right: 1px solid var(--border);
}

/* §24 — input-group outline buttons: sec-style.css */

/* Quick search focus state */
#quickSearch:focus + .btn-outline-secondary {
    box-shadow: 0 0 0 0.15rem rgba(var(--accent-rgb), 0.2);
}

.input-group:focus-within .input-group-text {
    border-color: var(--accent);
}

/* Table header gap adjustments */
.card-header .gap-3 {
    gap: 1rem !important;
}

@media (max-width: 768px) {
    .card-header .d-flex.gap-3 {
        flex-direction: column;
        gap: 0.5rem !important;
        align-items: stretch !important;
    }
    
    .card-header .input-group {
        width: 100% !important;
    }
    
    .card-header .justify-content-end {
        justify-content: stretch !important;
    }
}

/* Loading state for table */
.table-loading {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
}

.table-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 2rem;
    height: 2rem;
    border: 3px solid var(--border);
    border-top: 3px solid var(--accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 10;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Responsive pagination */
@media (max-width: 768px) {
    .pagination-info {
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .pagination {
        justify-content: center !important;
    }
    
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        margin: 0 1px;
    }
}

/* Doctor Filter Styling */
#doctorFilterGroup .btn {
    border-radius: 6px;
    margin: 0 2px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

#doctorFilterGroup .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#doctorFilterGroup .btn.active {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* §24 — doctorFilterGroup active: sec-style.css */

#doctorFilterGroup .btn i {
    font-size: 0.9rem;
}

/* Filter card styling */
.card.border-info {
    border-color: var(--accent) !important;
}

.card-header.bg-info.bg-opacity-10 {
    background-color: rgba(var(--accent-rgb), 0.1) !important;
    border-bottom-color: rgba(var(--accent-rgb), 0.2) !important;
}

.text-info {
    color: var(--accent) !important;
}

/* Responsive filter buttons */
@media (max-width: 768px) {
    #doctorFilterGroup {
        flex-direction: column;
        width: 100%;
    }
    
    #doctorFilterGroup .btn {
        margin: 2px 0;
        width: 100%;
    }
}

/* §25 — Bootstrap tooltips + patient action gaps: sec-style.css */

/* Improved button hover states with tooltips */
.btn:hover[data-bs-toggle="tooltip"] {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* §24 — outline tooltip hovers: sec-style.css */

h1, h2, h3, h4, h5, h6 {
color: var(--text) !important;
}

/* Sortable header styling */
.sortable-header {
    transition: all 0.2s ease;
    position: relative;
}

.sortable-header:hover {
    background-color: rgba(var(--accent-rgb), 0.1);
    cursor: pointer;
}

.sortable-header .sort-indicator {
    opacity: 1 !important;
    color: var(--accent);
    font-weight: bold;
}

/* Action buttons styling */
.btn-group-sm .btn {
    transition: all 0.2s ease;
    border-radius: 10px !important;
}

.btn-group-sm .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* §24 — outline hover shadows: sec-style.css */

/* Toggle Switch */
.toggle-switch-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.toggle-switch {
    height: 32px;
    width: 64px;
    background: var(--card);
    appearance: none;
    border-radius: 32px;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.1),
                inset 0 2px 2px rgba(0,0,0,0.1),
                inset 0 -1px 1px rgba(0,0,0,0.1);
    position: relative;
    outline: none;
    cursor: pointer;
    transition: 0.5s;
    border: 2px solid var(--border);
}

.toggle-switch::before {
    height: 26px;
    width: 26px;
    position: absolute;
    top: 1px;
    right: 1px; /* RTL: right instead of left */
    content: "";
    background: linear-gradient(to bottom, var(--card), var(--bg));
    border-radius: 50%;
    transform: scale(0.9);
    transition: 0.5s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3),
                inset 1px 1px rgba(255,255,255,0.2),
                inset -1px 1px rgba(255,255,255,0.2);
}

.toggle-switch:checked {
    background: var(--accent);
    box-shadow: inset 0 1px 10px rgba(0,0,0,0.1),
                inset 0 1px 2px rgba(0,0,0,0.1),
                inset 0 -1px 1px rgba(0,0,0,0.05);
    border-color: var(--accent);
}

.toggle-switch:checked::before {
    right: 33px; /* RTL: right instead of left */
    box-shadow: 0 2px 5px rgba(0,0,0,0.2),
                inset 1px 1px rgba(255,255,255,1),
                inset -1px 1px rgba(255,255,255,1);
    background: linear-gradient(to bottom, #ffffff, #f0f0f0);
}

.toggle-switch::after {
    content: "OFF";
    position: absolute;
    right: 8px; /* RTL: right instead of left */
    top: 50%;
    transform: translateY(-50%);
    font-size: 8px;
    font-weight: 700;
    color: var(--text) !important;
    opacity: 0.7;
    transition: 0.5s;
    pointer-events: none;
}

.toggle-switch:checked::after {
    content: "ON";
    right: 40px; /* RTL: right instead of left */
    color: black !important;
    opacity: 1;
}

.dark .toggle-switch {
    background: var(--card);
    box-shadow: inset 0 4px 20px rgba(0,0,0,0.3),
                inset 0 4px 4px rgba(0,0,0,0.2),
                inset 0 -2px 2px rgba(0,0,0,0.2);
}

.dark .toggle-switch::before {
    background: linear-gradient(to bottom, #334155, #1e293b);
    box-shadow: 0 4px 15px rgba(0,0,0,0.5),
                inset 2px 2px rgba(255,255,255,0.1),
                inset -2px 2px rgba(255,255,255,0.1);
}

.dark .toggle-switch:checked {
    background: var(--accent);
    box-shadow: inset 0 2px 20px rgba(0,0,0,0.2),
                inset 0 2px 4px rgba(0,0,0,0.1),
                inset 0 -2px 2px rgba(0,0,0,0.05);
}

.dark .toggle-switch:checked::before {
    background: linear-gradient(to bottom, #e2e8f0, #cbd5e1);
    box-shadow: 0 4px 10px rgba(0,0,0,0.3),
                inset 2px 2px rgba(255,255,255,1),
                inset -2px 2px rgba(255,255,255,1);
}

.dark .toggle-switch::after {
    color: var(--text);
}

.dark .toggle-switch:checked::after {
    color: white !important;
}

/* Stats Cards - Center Content and Background Colors */
.stats-cards-wrapper {
    margin: 0 -0.5rem;
}

.stats-card-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 180px;
}

.stats-card {
    width: 100%;
    height: 100%;
    background: none;
    border-radius: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    transition: all 0.2s ease;
    cursor: pointer;
}

.stats-card-content {
    background-color: var(--card);
    border-radius: inherit;
    transition: all 0.25s ease;
    height: calc(100% - 2px);
    width: calc(100% - 2px);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px var(--shadow);
    border: 1px solid var(--border);
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.stats-card:hover {
    transform: scale(0.98);
}

.stats-card-header {
    padding: 1.5rem 1rem 0.5rem 1rem;
    text-align: center;
    position: relative;
    z-index: 10;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.stats-card-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--muted);
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.stats-card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text);
    margin: 0.5rem 0;
    line-height: 1.2;
}

.stats-card-icon {
    position: absolute;
    right: 1rem; /* RTL: right instead of left */
    top: 50%;
    transform: translateY(-50%);
    z-index: 3;
    opacity: 0.15;
    pointer-events: none;
}

.stats-card-icon i {
    font-size: 4rem;
    color: var(--text);
}

/* Background colors for stats cards - Light Mode */
.stats-card-primary .stats-card-content {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(14, 165, 233, 0.05) 100%), var(--card);
    border-color: rgba(14, 165, 233, 0.3);
}

.stats-card-success .stats-card-content {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%), var(--card);
    border-color: rgba(16, 185, 129, 0.3);
}

.stats-card-info .stats-card-content {
    background: linear-gradient(135deg, rgba(187, 54, 204, 0.1) 0%, rgba(187, 54, 204, 0.05) 100%), var(--card);
    border-color: rgba(187, 54, 204, 0.3);
}

.stats-card-warning .stats-card-content {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%), var(--card);
    border-color: rgba(245, 158, 11, 0.3);
}

.stats-card-danger .stats-card-content {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%), var(--card);
    border-color: rgba(239, 68, 68, 0.3);
}

/* Dark Mode Stats Cards */
.dark .stats-card-content {
    background-color: var(--card);
    border-color: var(--border);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.dark .stats-card-primary .stats-card-content {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(56, 189, 248, 0.08) 100%), var(--card);
    border-color: rgba(56, 189, 248, 0.4);
}

.dark .stats-card-success .stats-card-content {
    background: linear-gradient(135deg, rgba(74, 222, 128, 0.15) 0%, rgba(74, 222, 128, 0.08) 100%), var(--card);
    border-color: rgba(74, 222, 128, 0.4);
}

.dark .stats-card-info .stats-card-content {
    background: linear-gradient(135deg, rgba(192, 132, 252, 0.15) 0%, rgba(192, 132, 252, 0.08) 100%), var(--card);
    border-color: rgba(192, 132, 252, 0.4);
}

.dark .stats-card-warning .stats-card-content {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(251, 191, 36, 0.08) 100%), var(--card);
    border-color: rgba(251, 191, 36, 0.4);
}

.dark .stats-card-danger .stats-card-content {
    background: linear-gradient(135deg, rgba(248, 113, 113, 0.15) 0%, rgba(248, 113, 113, 0.08) 100%), var(--card);
    border-color: rgba(248, 113, 113, 0.4);
}

.dark .stats-card-title {
    color: var(--muted);
}

.dark .stats-card-value {
    color: var(--text);
}

.dark .stats-card-icon i {
    opacity: 0.2;
}

/* Table header RTL alignment */
.table thead th {
    text-align: right !important;
    direction: rtl;
}

.table thead th.arabic-text {
    text-align: right !important;
    direction: rtl;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card-wrapper {
        min-height: 160px;
    }
    
    .stats-card-value {
        font-size: 1.75rem;
    }
    
    .stats-card-title {
        font-size: 0.7rem;
    }
    
    .stats-card-header {
        padding: 1.25rem 0.75rem 0.5rem 0.75rem;
    }
    
    /* Table responsive on mobile */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: var(--accent) var(--bg);
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: var(--bg);
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: var(--accent);
        opacity: 0.8;
    }
    
    /* Make table cells stack better on mobile */
    .table th,
    .table td {
        white-space: nowrap;
        min-width: 120px;
    }
}

/* Table action pills + thead height — doctor patients.css; RTL column titles only */
#secTableView .table thead th.arabic-text {
    text-align: right !important;
    direction: rtl;
}

/* Modal centering — backdrop/z-index: sec-style.css + modal-kit.css */
.modal {
    align-items: center;
    justify-content: center;
    padding: 1rem !important;
}

.modal-dialog {
    margin: 0 auto;
    max-width: 500px;
}

.modal-dialog.modal-lg {
    max-width: 800px;
}

.modal-dialog.modal-xl {
    max-width: 1140px;
}

.modal-dialog.modal-sm {
    max-width: 300px;
}

@media screen and (max-width: 768px) {
    #secTableView .card-header .header-actions-row {
        flex-wrap: wrap !important;
    }
}
</style>