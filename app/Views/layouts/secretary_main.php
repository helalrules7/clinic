<!DOCTYPE html>
<html lang="ar" dir="rtl" data-layout="secretary">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HClinic / عيادة رؤية - السكرتارية' ?></title>
    
    <!-- Favicons -->
    <!-- Favicons — theme-matched (Light/Dark). Swapped by the pre-paint script below. -->
    <link id="faviconIco"   rel="icon"            type="image/x-icon" href="/assets/fav/Light.ico">
    <link id="faviconApple" rel="apple-touch-icon" sizes="180x180"     href="/assets/fav/Light-180x180.png">
    <link id="favicon32"    rel="icon"            type="image/png" sizes="32x32"  href="/assets/fav/Light-32x32.png">
    <link id="favicon192"   rel="icon"            type="image/png" sizes="192x192" href="/assets/fav/Light-192x192.png">
    <link id="favicon512"   rel="icon"            type="image/png" sizes="512x512" href="/assets/fav/Light-512x512.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Doctor Pages Consolidated CSS -->
    <link href="/app/Views/layouts/sec-style.css?v=<?= filemtime(__DIR__ . '/sec-style.css') ?>" rel="stylesheet">
    <!-- Unified Glass / Indigo design system (loaded AFTER sec-style.css so its tokens win) -->
    <link href="/app/Views/layouts/design-system/tokens.css?v=<?= file_exists(__DIR__ . '/design-system/tokens.css') ? filemtime(__DIR__ . '/design-system/tokens.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/design-system/design-system.css?v=<?= file_exists(__DIR__ . '/design-system/design-system.css') ? filemtime(__DIR__ . '/design-system/design-system.css') : time() ?>" rel="stylesheet">
    <!-- Shared modal kit (center + animate + drag affordance) — after design-system so it wins -->
    <link href="/app/Views/layouts/modal-kit.css?v=<?= file_exists(__DIR__ . '/modal-kit.css') ? filemtime(__DIR__ . '/modal-kit.css') : time() ?>" rel="stylesheet">

    <!-- v11.0.0 feature CSS bundle -->
    <link href="/app/Views/doctor/assets/css/notification-center.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/notification-center.css') ? filemtime(__DIR__ . '/../doctor/assets/css/notification-center.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/todo-drawer.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/todo-drawer.css') ? filemtime(__DIR__ . '/../doctor/assets/css/todo-drawer.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/cmdk.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/cmdk.css') ? filemtime(__DIR__ . '/../doctor/assets/css/cmdk.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/patient-hover.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/patient-hover.css') ? filemtime(__DIR__ . '/../doctor/assets/css/patient-hover.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/keyboard-help.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/keyboard-help.css') ? filemtime(__DIR__ . '/../doctor/assets/css/keyboard-help.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/quick-note.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/quick-note.css') ? filemtime(__DIR__ . '/../doctor/assets/css/quick-note.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/note-templates.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/note-templates.css') ? filemtime(__DIR__ . '/../doctor/assets/css/note-templates.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/theme-palette.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/theme-palette.css') ? filemtime(__DIR__ . '/../doctor/assets/css/theme-palette.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/celebration.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/celebration.css') ? filemtime(__DIR__ . '/../doctor/assets/css/celebration.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/notes-drawer.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/notes-drawer.css') ? filemtime(__DIR__ . '/../doctor/assets/css/notes-drawer.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/header-chips.css?v=<?= file_exists(__DIR__ . '/header-chips.css') ? filemtime(__DIR__ . '/header-chips.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/global-search-panel.css?v=<?= file_exists(__DIR__ . '/global-search-panel.css') ? filemtime(__DIR__ . '/global-search-panel.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/push-toast-center.css?v=<?= file_exists(__DIR__ . '/push-toast-center.css') ? filemtime(__DIR__ . '/push-toast-center.css') : time() ?>" rel="stylesheet">
    <!-- v11: notice-bar clock/calendar + appointments popovers (ported from doctor style.css) -->
    <link href="/app/Views/layouts/secretary-notice-bar.css?v=<?= file_exists(__DIR__ . '/secretary-notice-bar.css') ? filemtime(__DIR__ . '/secretary-notice-bar.css') : time() ?>" rel="stylesheet">

    <!-- Theme + logo + favicon pre-paint. Runs synchronously in <head> so
         #clinicLogo and the favicon <link>s are rendered with the right
         theme variant on first paint — no Light→Dark flicker on refresh. -->
    <script>
        (function() {
            // v11.0.0 — Theme + Palette + Auto-schedule pre-paint (secretary layout).

            var html = document.documentElement;
            var ALLOWED_PALETTES = ['indigo','emerald','rose','slate','amber','ocean'];

            // 1) Palette
            var savedPalette = null;
            try { savedPalette = localStorage.getItem('appPalette'); } catch (e) {}
            var palette = (ALLOWED_PALETTES.indexOf(savedPalette) >= 0) ? savedPalette : 'indigo';
            html.setAttribute('data-palette', palette);

            // 2) Theme — manual OR auto-schedule, with fallback to system pref.
            var saved, autoSched = false, darkFrom = '19:00', lightFrom = '07:00';
            try {
                saved      = localStorage.getItem('appTheme') || localStorage.getItem('theme');
                autoSched  = localStorage.getItem('appThemeAutoSchedule') === '1';
                darkFrom   = localStorage.getItem('appThemeDarkFrom')  || '19:00';
                lightFrom  = localStorage.getItem('appThemeLightFrom') || '07:00';
            } catch (e) {}

            var theme;
            if (autoSched) {
                var now = new Date();
                var mins = now.getHours() * 60 + now.getMinutes();
                var parse = function (s) { var p = String(s).split(':'); return (+p[0]) * 60 + (+(p[1] || 0)); };
                var darkStart  = parse(darkFrom);
                var lightStart = parse(lightFrom);
                theme = ((mins >= darkStart) || (mins < lightStart)) ? 'dark' : 'light';
            } else {
                theme = (saved === 'light' || saved === 'dark') ? saved
                      : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            }
            html.classList.toggle('dark', theme === 'dark');
            html.classList.add('theme-loaded');

            // 3) Logo + favicons.
            window.__INITIAL_LOGO_SRC__ = theme === 'dark'
                ? '/assets/images/Dark.png'
                : '/assets/images/Light.png';

            var icon = theme === 'dark' ? 'Dark' : 'Light';
            var setHref = function(id, href) {
                var el = document.getElementById(id);
                if (el) el.href = href;
            };
            setHref('faviconIco',   '/assets/fav/' + icon + '.ico');
            setHref('faviconApple', '/assets/fav/' + icon + '-180x180.png');
            setHref('favicon32',    '/assets/fav/' + icon + '-32x32.png');
            setHref('favicon192',   '/assets/fav/' + icon + '-192x192.png');
            setHref('favicon512',   '/assets/fav/' + icon + '-512x512.png');
        })();
    </script>

    <style>
        /* Secretary specific styles - keep only what's not in sec-style.css */
        .secretary-badge {
            background: linear-gradient(135deg, var(--accent), var(--info));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* View As Banner Styles */
        .view-as-banner {
            animation: pulse 2s infinite;
            border: 3px solid #ffc107;
        }

        @keyframes pulse {
            0% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
            50% { box-shadow: 0 4px 25px rgba(255, 193, 7, 0.6); }
            100% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
        }

        .view-as-indicator {
            position: relative;
        }

        .view-as-indicator::before {
            content: "⚠️";
            position: absolute;
            top: -5px;
            left: -5px;
            font-size: 16px;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .clinic-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 1px 8px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--clinic-color, #6c757d) 14%, transparent);
            color: var(--clinic-color, #6c757d);
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1.4;
            white-space: nowrap;
        }
        .clinic-tag i { font-size: 1rem; }
        .dark .clinic-tag {
            background: color-mix(in srgb, var(--clinic-color, #adb5bd) 22%, transparent);
            filter: brightness(1.15);
        }
    </style>

    <!-- Clinics bootstrap: secretary sees only her own clinic (server-side
         pinned). The list is rendered into the page so modal dropdowns can
         populate synchronously on first open without waiting on /api/clinics. -->
    <?php
        try {
            $__clinicsBootstrapPdo = \App\Config\Database::getInstance()->getConnection();
            $__clinicsBootstrapAuth = new \App\Lib\Auth();
            $__clinicsBootstrapUser = $__clinicsBootstrapAuth->user();
            if (!empty($__clinicsBootstrapUser['clinic_id'])) {
                $__cstmt = $__clinicsBootstrapPdo->prepare("
                    SELECT id, code, name_ar, name_en
                    FROM clinics
                    WHERE is_active = 1 AND id = ?
                    ORDER BY sort_order ASC, id ASC
                ");
                $__cstmt->execute([(int)$__clinicsBootstrapUser['clinic_id']]);
                $__clinicsBootstrap = $__cstmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $__clinicsBootstrap = $__clinicsBootstrapPdo->query("
                    SELECT id, code, name_ar, name_en
                    FROM clinics
                    WHERE is_active = 1
                    ORDER BY sort_order ASC, id ASC
                ")->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Throwable $__e) {
            $__clinicsBootstrap = [];
        }
    ?>
    <script>
        window.CLINICS_BOOTSTRAP = <?= json_encode($__clinicsBootstrap, JSON_UNESCAPED_UNICODE) ?>;
    </script>

    <?php require __DIR__ . '/speculation-rules.php'; ?>
</head>
<body>
    <!-- استرجاع حالة sidebar قبل العرض — لازم يشتغل قبل ما الـ sidebar
         يترسم، عشان المستخدم مايشوفش وميض من "wide" إلى "mini" مع كل تنقل
         بين الصفحات. -->
    <script>
    (function () {
        try {
            var KEY = 'appSidebarMode';
            var TABLET_BP = 1366;
            var saved = null;
            try { saved = localStorage.getItem(KEY); } catch (e) {}
            var mode = saved || (window.innerWidth < TABLET_BP ? 'mini' : 'wide');
            if (mode === 'mini' && document.body) document.body.classList.add('sidebar-mini');
        } catch (e) { /* ignore */ }
    })();
    </script>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="clinic-logo">
                <img id="clinicLogo" src="/assets/images/Light.png" data-light-src="/assets/images/Light.png" data-dark-src="/assets/images/Dark.png" alt="HClinic / عيادة رؤية" style="width: 32px; height: 32px; margin-left: 0.75rem;">
                <script>
                    (function() {
                        var img = document.getElementById('clinicLogo');
                        if (img && window.__INITIAL_LOGO_SRC__) img.src = window.__INITIAL_LOGO_SRC__;
                    })();
                </script>
                <div class="clinic-name">HClinic / عيادة رؤية</div>
            </div>
        </div>
        
        <div class="user-info">
            <div class="d-flex align-items-center">
                <div class="user-avatar" id="sidebarUserAvatar">
                    <?php
                    $currentUser = $this->getCurrentUser();
                    if (!empty($currentUser['profile_image'])):
                        $sidebarImg = strpos($currentUser['profile_image'], '/public/') === 0
                            ? $currentUser['profile_image']
                            : '/public' . $currentUser['profile_image'];
                    ?>
                        <img src="<?= htmlspecialchars($sidebarImg) ?>" class="user-avatar-img" alt="الصورة الشخصية">
                    <?php else: ?>
                        <?= strtoupper(mb_substr($currentUser['name'] ?? 'س', 0, 1, 'UTF-8')) ?>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <h6><?= htmlspecialchars(($this->getCurrentUser()['name'] ?? 'المستخدم')) ?></h6>
                    <small class="secretary-badge">سكرتارية</small>
                </div>
            </div>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-item">
                <a href="/secretary/dashboard" class="nav-link <?= $this->isActiveRoute('/secretary/dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    لوحة التحكم
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/bookings" class="nav-link <?= $this->isActiveRoute('/secretary/bookings') ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i>
                    الحجوزات
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/payments" class="nav-link <?= $this->isActiveRoute('/secretary/payments') ? 'active' : '' ?>">
                    <i class="bi bi-credit-card"></i>
                    المدفوعات
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/patients" class="nav-link <?= $this->isActiveRoute('/secretary/patients') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    المرضى
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/settings" class="nav-link <?= $this->isActiveRoute('/secretary/settings') ? 'active' : '' ?>">
                    <i class="bi bi-sliders"></i>
                    الإعدادات
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/profile" class="nav-link <?= $this->isActiveRoute('/secretary/profile') ? 'active' : '' ?>">
                    <i class="bi bi-person-circle"></i>
                    الملف الشخصي
                </a>
            </div>
            
            <?php 
            // Check if admin is in View As mode using session directly
            if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
            ?>
            <div class="nav-item mt-auto">
                <a href="/admin/stop-view-as" class="nav-link text-warning" style="font-weight: bold; background-color: rgba(255, 193, 7, 0.1);">
                    <i class="fas fa-arrow-left"></i>
                    الخروج من وضع المعاينة
                </a>
            </div>
            <?php endif; ?>
            
            <div class="nav-item mt-auto">
                <a href="/logout" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    تسجيل الخروج
                </a>
            </div>
            
            <!-- Version info -->
            <div class="sidebar-footer p-3 text-center border-top">
                <small class="text-muted">
                    <div class="mb-1">
                        HClinic / Roaya Clinic v11.0.0
                        <span aria-hidden="true">·</span>
                        <a href="https://hclinic.clinic/docs/opth/" target="_blank" rel="noopener" style="color: var(--accent);"><i class="bi bi-book me-1"></i>Docs</a>
                    </div>
                    <div>© 2025 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none" style="color: var(--accent);">Ahmed Helal</a></div>
                </small>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>
        <?php 
        // View As Banner - Very visible indicator
        if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
        ?>
        <div class="view-as-banner" style="background: linear-gradient(135deg, #ffc107, #ff8f00); color: #000; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); text-align: center; position: relative;">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-0" style="font-weight: bold;">
                            <i class="fas fa-eye me-2"></i>
                            وضع المعاينة نشط - أنت تشاهد كـ: <strong><?= ucfirst($_SESSION['view_as_role'] ?? 'غير معروف') ?></strong>
                        </h4>
                        <small>أنت تشاهد حالياً واجهة <?= ucfirst($_SESSION['view_as_role'] ?? 'غير معروف') ?></small>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/stop-view-as" class="btn btn-dark btn-lg" style="font-weight: bold; padding: 10px 20px;">
                            <i class="fas fa-arrow-left me-2"></i>
                            الخروج من وضع المعاينة
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="top-bar">
            <!-- v11: notice bar (clock + next appointment) — header-height parity with the doctor -->
            <div class="notice-bar" id="secNoticeBar" dir="rtl">
                <span class="notice-bar-clock"><i class="bi bi-clock"></i><span id="secNoticeClock">—</span></span>
                <span class="notice-bar-next" id="secNoticeNext"><i class="bi bi-calendar-event"></i><span>…</span></span>
            </div>
            <button class="btn sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="page-title">
                <h1>
                    <?= $pageTitle ?? 'لوحة التحكم' ?>
                    <?php if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.6em; animation: pulse 2s infinite;">
                            معاينة: <?= strtoupper($_SESSION['view_as_role'] ?? 'غير معروف') ?>
                        </span>
                    <?php endif; ?>
                </h1>
                <?php if (!empty($pageSubtitle)): ?>
                <small><?= $pageSubtitle ?></small>
                <?php endif; ?>
            </div>
            
            <div class="top-bar-cluster">
            <div class="top-actions">
                <!-- بحث عالمي — مرضى · حجوزات · مدفوعات (روابط /secretary/…) -->
                <div class="global-search-wrapper">
                    <button type="button" class="global-search-toggle d-xl-none" id="globalSearchToggle" title="بحث" aria-label="بحث">
                        <i class="bi bi-search"></i>
                    </button>
                    <div class="global-search-container" id="globalSearchContainer">
                        <div class="global-search-backdrop" id="globalSearchBackdrop"></div>
                        <div class="global-search-input-wrapper">
                            <div class="global-search-input-field-wrapper">
                                <i class="bi bi-search global-search-icon"></i>
                                <input type="text"
                                       class="form-control global-search-input"
                                       id="globalSearchInput"
                                       placeholder="بحث: مرضى، حجوزات، مدفوعات…"
                                       autocomplete="off"
                                       dir="rtl">
                                <button class="btn btn-link global-search-clear d-none" id="globalSearchClear" type="button" aria-label="مسح البحث">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="global-search-hint" id="globalSearchHint">
                                <i class="bi bi-lightbulb"></i>
                                <span>نصيحة: استخدم &amp; لتضييق النتائج باسم المريض أو رقم الهوية، أو # للتاريخ.
                                    <br>أمثلة: «أحمد &amp; محمد»، «أحمد &amp; 123»، «حجز # 2026-06-07»</span>
                            </div>
                            <div class="global-search-results" id="globalSearchResults"></div>
                        </div>
                    </div>
                </div>

                <?php
                if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true):
                ?>
                    <div class="view-as-indicator me-3">
                        <div class="alert alert-warning d-flex align-items-center mb-0 py-2 px-3" style="font-size: 0.9rem; border: 2px solid #ffc107;">
                            <i class="fas fa-eye me-2"></i>
                            <span><strong>وضع المعاينة:</strong> <?= ucfirst($_SESSION['view_as_role'] ?? 'غير معروف') ?></span>
                            <a href="/admin/stop-view-as" class="btn btn-sm btn-outline-warning ms-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                خروج
                            </a>
                        </div>
                    </div>
                    <a href="/admin/stop-view-as" class="btn btn-warning me-2" title="الخروج من وضع المعاينة" style="font-weight: bold;">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        الخروج من المعاينة
                    </a>
                <?php endif; ?>

                <button type="button" class="btn btn-outline-secondary notifications-toggle" id="notificationsToggle" title="الإشعارات" aria-label="الإشعارات">
                    <i class="bi bi-bell"></i>
                    <span class="notifications-badge" id="notificationsBadge" style="display: none;">0</span>
                </button>
            </div>

            <div class="top-actions-quick" id="topActionsQuick" aria-label="أدوات سريعة"></div>

            <div class="top-actions-theme">
                <!-- Sun/Moon Theme Toggle Switch -->
                <label class="switch" for="themeToggleInput" title="تبديل المظهر">
                    <input type="checkbox" id="themeToggleInput">
                    <span class="slider round">
                        <span class="sun-moon">
                            <svg id="moon-dot-1" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-2" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-3" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-1" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-2" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-3" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                        </span>
                        <svg class="cloud-light" id="cloud-1" viewBox="0 0 100 100">
                            <path d="M50,92C78,92,100,71,100,47C100,23,78,2,50,2C22,2,0,23,0,47C0,71,22,92,50,92Z"></path>
                        </svg>
                        <svg class="cloud-dark" id="cloud-2" viewBox="0 0 100 100">
                            <path d="M50,92C78,92,100,71,100,47C100,23,78,2,50,2C22,2,0,23,0,47C0,71,22,92,50,92Z"></path>
                        </svg>
                        <svg class="cloud-light" id="cloud-3" viewBox="0 0 100 100">
                            <path d="M50,92C78,92,100,71,100,47C100,23,78,2,50,2C22,2,0,23,0,47C0,71,22,92,50,92Z"></path>
                        </svg>
                    </span>
                </label>
            </div>
            </div>
        </div>

        <!-- Page Content -->
        <?= $content ?>
    </div>    
    <!-- Scroll to Top Button (scroll-progress ring — parity with doctor layout) -->
    <button class="scroll-to-top" id="scrollToTop" aria-label="العودة للأعلى">
        <svg class="stt-ring" viewBox="0 0 44 44" aria-hidden="true">
            <circle class="stt-ring-track" cx="22" cy="22" r="19.5"></circle>
            <circle class="stt-ring-bar" cx="22" cy="22" r="19.5"></circle>
        </svg>
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Shared modal kit: unified pointer-drag + center + showConfirm/AlertModal (after Bootstrap) -->
    <script src="/app/Views/layouts/modal-kit.js?v=<?= file_exists(__DIR__ . '/modal-kit.js') ? filemtime(__DIR__ . '/modal-kit.js') : time() ?>"></script>
    <script src="/app/Views/layouts/clinics-loader.js?v=<?= filemtime(__DIR__ . '/clinics-loader.js') ?>"></script>

    <script>
        // Theme toggle functionality - synced with main layout using appTheme
        function updateThemeUI(theme) {
            const isDark = theme === 'dark';
            document.documentElement.classList.toggle('dark', isDark);

            // Update logo
            const logo = document.getElementById('clinicLogo');
            if (logo) {
                logo.src = isDark ? '/assets/images/Dark.png' : '/assets/images/Light.png';
            }

            // Favicon is now static (faicon.ico) - no need to update

            // Update checkbox state
            const themeCheckbox = document.getElementById('themeToggleInput');
            if (themeCheckbox) {
                themeCheckbox.checked = isDark;
            }
        }

        // Function to save theme to database and localStorage
        async function saveThemeToDatabase(theme) {
            // Save to localStorage immediately (synchronous, no delay)
            localStorage.setItem('appTheme', theme);
            localStorage.setItem('theme', theme);
            
            try {
                const response = await fetch('/api/secretary/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        theme: theme
                    })
                });
                
                if (!response.ok) {
                    console.error('Failed to save theme to database');
                }
            } catch (error) {
                console.error('Error saving theme to database:', error);
            }
        }
        
        // Function to load theme from database
        async function loadThemeFromDatabase() {
            try {
                const response = await fetch('/api/secretary/settings', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.settings && data.settings.theme) {
                        return data.settings.theme;
                    }
                }
            } catch (error) {
                console.error('Error loading theme from database:', error);
            }
            return null;
        }
        
        // Exposed so theme-palette.js auto-schedule can sync header + settings toggles.
        window.syncThemeUI = function () {
            updateThemeUI(document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        };

        async function onManualThemePick(nextTheme) {
            if (typeof window.disableThemeAutoSchedule === 'function') {
                await window.disableThemeAutoSchedule(true);
            } else {
                localStorage.setItem('appThemeAutoSchedule', '0');
            }
            updateThemeUI(nextTheme);
            localStorage.setItem('appTheme', nextTheme);
            localStorage.setItem('theme', nextTheme);
            await saveThemeToDatabase(nextTheme);
        }

        // Initialize theme — auto-schedule wins (pre-paint + theme-palette.js timer).
        (async function() {
            const autoSchedule = localStorage.getItem('appThemeAutoSchedule') === '1';
            if (autoSchedule) {
                const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                updateThemeUI(current);
                document.documentElement.classList.add('theme-loaded');
                const themeToggleInputAuto = document.getElementById('themeToggleInput');
                if (themeToggleInputAuto) {
                    themeToggleInputAuto.addEventListener('change', async function () {
                        await onManualThemePick(this.checked ? 'dark' : 'light');
                    });
                }
                return;
            }

            let savedTheme = localStorage.getItem('appTheme') || localStorage.getItem('theme');
            if (savedTheme !== 'light' && savedTheme !== 'dark') {
                savedTheme = null;
            }

            let dbTheme = null;
            if (!savedTheme) {
                dbTheme = await loadThemeFromDatabase();
                if (dbTheme === 'light' || dbTheme === 'dark') {
                    savedTheme = dbTheme;
                    localStorage.setItem('appTheme', savedTheme);
                    localStorage.setItem('theme', savedTheme);
                }
            } else {
                dbTheme = await loadThemeFromDatabase();
            }

            if (!savedTheme || (savedTheme !== 'light' && savedTheme !== 'dark')) {
                savedTheme = 'dark';
                localStorage.setItem('appTheme', savedTheme);
                localStorage.setItem('theme', savedTheme);
            }

            if (savedTheme && dbTheme && dbTheme !== savedTheme) {
                await saveThemeToDatabase(savedTheme);
            } else if (savedTheme && !dbTheme) {
                await saveThemeToDatabase(savedTheme);
            }

            updateThemeUI(savedTheme);
            document.documentElement.classList.add('theme-loaded');

            const themeToggleInput = document.getElementById('themeToggleInput');
            if (themeToggleInput) {
                themeToggleInput.addEventListener('change', async function () {
                    await onManualThemePick(this.checked ? 'dark' : 'light');
                });
            }
        })();

        // Sidebar toggle — three modes mirroring the doctor layout (main.js):
        //   • <768px (phone)      → off-canvas overlay drawer (sidebar.show)
        //   • 768–1365px (tablet) → 76px icon mini-rail (body.sidebar-mini)
        //   • ≥1366px (desktop)   → full expanded sidebar (user-toggleable)
        // A cramped floor (<1100px effective, e.g. heavy zoom) ALWAYS forces
        // mini even if the user previously chose wide. Mode persists across
        // pages via localStorage; the slide drops backdrop-filter for 60fps.
        (function setupSidebarToggle() {
            const SIDEBAR_MODE_KEY = 'appSidebarMode'; // 'wide' | 'mini'
            const MOBILE_BP = 768;
            const TABLET_BP = 1366;   // tablets (<1366) default to the mini rail
            const FORCE_MINI_BP = 1100; // hard floor: below this, always mini

            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (!sidebarToggle || !sidebar || !overlay) {
                console.error('Sidebar toggle elements not found');
                return;
            }

            const isMobile  = () => window.innerWidth < MOBILE_BP;
            const isCramped = () => window.innerWidth < FORCE_MINI_BP;

            let _animTimer = null;
            function applyMode(mode) {
                const wantMini = (mode === 'mini');
                const isMini = document.body.classList.contains('sidebar-mini');
                if (wantMini === isMini) return; // no state change → skip the transition
                // PERF: drop backdrop-filter on every glass surface for the
                // ~0.2s width/margin slide so the reflow doesn't re-blur each
                // frame (was dropping below 30fps on glass-heavy pages).
                document.body.classList.add('sidebar-animating');
                if (_animTimer) clearTimeout(_animTimer);
                _animTimer = setTimeout(() => {
                    document.body.classList.remove('sidebar-animating');
                    _animTimer = null;
                }, 280);
                document.body.classList.toggle('sidebar-mini', wantMini);
            }

            const defaultMode = () => window.innerWidth < TABLET_BP ? 'mini' : 'wide';
            const readSaved = () => { try { return localStorage.getItem(SIDEBAR_MODE_KEY); } catch (e) { return null; } };
            // Effective mode: a cramped viewport ALWAYS forces mini, even if the
            // user previously chose wide on a larger screen.
            const effectiveMode = () => isCramped() ? 'mini' : (readSaved() || defaultMode());

            applyMode(effectiveMode());

            sidebarToggle.addEventListener('click', () => {
                if (isMobile()) {
                    // Phone: classic off-canvas overlay drawer.
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                    return;
                }
                // Tablet / desktop: flip the rail and persist intent. We still
                // re-apply effectiveMode so a "wide" choice on a cramped screen
                // is recorded but doesn't override the safety floor.
                const wantMini = !document.body.classList.contains('sidebar-mini');
                try { localStorage.setItem(SIDEBAR_MODE_KEY, wantMini ? 'mini' : 'wide'); } catch (e) {}
                applyMode(effectiveMode());
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= MOBILE_BP) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
                // Recompute every resize — handles zoom (fires resize) and
                // rotation, toggling the force-mini floor on/off automatically.
                applyMode(effectiveMode());
            });
        })();

        // Mini-rail hover tooltip — matches the doctor layout: hovering a
        // collapsed icon shows a small arrow tooltip (page title + one-line
        // description) instead of widening the whole rail. RTL-aware.
        (function setupMiniTip() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            const DESC = {
                dashboard: 'لوحة المعلومات والإحصائيات',
                bookings:  'المواعيد والحجوزات',
                payments:  'الإدارة المالية',
                patients:  'سجلات المرضى',
                settings:  'المظهر والتفضيلات',
                profile:   'حسابك الشخصي',
            };

            const tip = document.createElement('div');
            tip.className = 'nav-mini-tip';
            tip.innerHTML = '<b></b><span></span>';
            document.body.appendChild(tip);
            const tipTitle = tip.querySelector('b');
            const tipDesc  = tip.querySelector('span');

            function railIsCollapsed() {
                return document.body.classList.contains('sidebar-mini') &&
                    window.innerWidth >= 768; // mobile uses the full overlay drawer
            }

            function showFor(link) {
                if (!railIsCollapsed()) return;
                const title = (link.textContent || '').replace(/\s+/g, ' ').trim();
                if (!title) return;
                const seg = (link.getAttribute('href') || '').split('?')[0].replace(/\/+$/, '').split('/').pop();
                const desc = DESC[seg] || '';
                tipTitle.textContent = title;
                tipDesc.textContent = desc;
                tipDesc.style.display = desc ? '' : 'none';

                const r = link.getBoundingClientRect();
                tip.style.visibility = 'hidden';
                tip.classList.add('show');
                const isRtl = (document.documentElement.getAttribute('dir') === 'rtl') ||
                    getComputedStyle(document.documentElement).direction === 'rtl';
                tip.classList.toggle('rtl', isRtl);
                const th = tip.offsetHeight, tw = tip.offsetWidth;
                let top = r.top + r.height / 2 - th / 2;
                top = Math.max(8, Math.min(top, window.innerHeight - th - 8));
                tip.style.top = Math.round(top) + 'px';
                // RTL rail is on the right → tip to the icon's left; LTR → right.
                tip.style.left = isRtl ? Math.round(r.left - tw - 12) + 'px' : Math.round(r.right + 12) + 'px';
                tip.style.visibility = 'visible';
            }
            const hide = () => tip.classList.remove('show');

            sidebar.querySelectorAll('.nav-link').forEach((link) => {
                link.addEventListener('mouseenter', () => showFor(link));
                link.addEventListener('mouseleave', hide);
                link.addEventListener('click', hide);
            });
            sidebar.addEventListener('scroll', hide, true);
            window.addEventListener('resize', hide);
        })();
        
        // Top-bar scroll effect
        const topBar = document.querySelector('.top-bar');
        if (topBar) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 0) {
                    topBar.classList.add('scrolled');
                } else {
                    topBar.classList.remove('scrolled');
                }
            });
        }
        
        // Scroll to Top — doctor parity: progress ring + rAF-throttled scroll tick
        // + back_to_top_display from /api/secretary/settings
        (function setupScrollToTop() {
            const scrollToTopBtn = document.getElementById('scrollToTop');
            if (!scrollToTopBtn) return;

            const sttRingBar = scrollToTopBtn.querySelector('.stt-ring-bar');
            const STT_RING_C = 122.52; // 2π·19.5
            let scrollBound = false;

            function onScrollTick() {
                if (!scrollBound) return;
                if (window._secSttScrollScheduled) return;
                window._secSttScrollScheduled = true;
                requestAnimationFrame(() => {
                    window._secSttScrollScheduled = false;
                    const y = window.pageYOffset;
                    const el = document.documentElement;
                    const max = (el.scrollHeight - el.clientHeight) || 1;
                    const ringP = Math.min(1, Math.max(0, y / max));
                    const wantShow = y > 300;
                    scrollToTopBtn.classList.toggle('show', wantShow);
                    if (sttRingBar) {
                        sttRingBar.style.strokeDashoffset = (STT_RING_C * (1 - ringP)).toFixed(2);
                    }
                });
            }

            function bindScrollHandlers() {
                if (scrollBound) return;
                scrollBound = true;
                window.addEventListener('scroll', onScrollTick, { passive: true });
                onScrollTick();
                scrollToTopBtn.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            function applyBackToTopPreference(enabled) {
                if (!enabled) {
                    if (scrollBound) {
                        window.removeEventListener('scroll', onScrollTick);
                        scrollBound = false;
                    }
                    scrollToTopBtn.classList.remove('show');
                    scrollToTopBtn.style.display = 'none';
                    return;
                }
                scrollToTopBtn.style.display = '';
                bindScrollHandlers();
            }

            window.secApplyBackToTopPreference = applyBackToTopPreference;

            (async function loadBackToTopPreference() {
                try {
                    const response = await fetch('/api/secretary/settings', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.settings) {
                            applyBackToTopPreference(data.settings.back_to_top_display !== false);
                            return;
                        }
                    }
                } catch (_) {}
                applyBackToTopPreference(true);
            })();
        })();

        // Make modals draggable globally
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
                
                modalHeader.addEventListener('mousedown', dragStart);
                
                function dragStart(e) {
                    // Don't drag if clicking on buttons or inputs
                    if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                        return;
                    }
                    
                    // Only start dragging if clicking on header (not on title text)
                    if (e.target === modalHeader || (modalHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
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
                });
            });
        }
        
        // Initialize draggable modals when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDraggableModals);
        } else {
            initializeDraggableModals();
        }
        
        // Re-initialize draggable modals when new modals are added dynamically
        const modalObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && (node.classList && node.classList.contains('modal'))) {
                        // Small delay to ensure modal is fully initialized
                        setTimeout(initializeDraggableModals, 100);
                    }
                });
            });
        });
        
        modalObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
    
    <!-- Session Expiry Warning Modal -->
    <div class="modal fade" id="sessionExpiryModal" tabindex="-1" aria-labelledby="sessionExpiryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="sessionExpiryModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Session Expiring Soon
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-3">Your session will expire due to inactivity in:</p>
                    <div class="session-countdown mb-3">
                        <span id="sessionCountdown" class="display-4 fw-bold text-danger">30</span>
                        <span class="fs-5 text-muted ms-2">seconds</span>
                    </div>
                    <p class="text-muted">Click "Stay Logged In" to extend your session.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" id="stayLoggedInBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        Stay Logged In
                    </button>
                    <button type="button" class="btn btn-secondary" id="logoutNowBtn">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout Now
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Session Expiry Warning Script -->
    <script>
        (function() {
            let sessionCheckInterval;
            let countdownInterval;
            let warningShown = false;
            const warningThreshold = 30; // Show warning 30 seconds before expiry
            const checkInterval = 5000; // Check every 5 seconds
            
            // Check session on page load (especially for settings page)
            function checkSessionOnLoad() {
                // Only check if we're on a protected page (not login page)
                if (window.location.pathname.includes('/login') || window.location.pathname.includes('/logout')) {
                    return;
                }
                
                fetch('/api/auth/session-time', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        // Session expired or not authenticated
                        window.location.href = '/login?expired=1';
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && (!data.success || data.remaining <= 0)) {
                        // Session expired or not authenticated
                        window.location.href = '/login?expired=1';
                    }
                })
                .catch(error => {
                    // Silent error handling for network errors
                });
            }
            
            // Run check on page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', checkSessionOnLoad);
            } else {
                checkSessionOnLoad();
            }
            
            function checkSessionTime() {
                fetch('/api/auth/session-time', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            // Session expired or not authenticated
                            clearIntervals();
                            window.location.href = '/login';
                            return;
                        }
                        
                        const remaining = Math.floor(data.remaining); // Ensure integer
                        
                        // Show warning modal if remaining time is less than threshold and not already shown
                        if (remaining <= warningThreshold && remaining > 0 && !warningShown) {
                            showWarningModal(remaining);
                        }
                        
                        // If session expired, redirect to login
                        if (remaining <= 0) {
                            clearIntervals();
                            window.location.href = '/login?expired=1';
                        }
                    })
                    .catch(error => {
                        // Silently handle network errors - don't spam console
                        // Only log if it's not a network error
                        if (error.name !== 'TypeError' || !error.message.includes('Load failed')) {
                            console.error('Error checking session time:', error);
                        }
                    });
            }
            
            function showWarningModal(remainingSeconds) {
                warningShown = true;
                const modal = new bootstrap.Modal(document.getElementById('sessionExpiryModal'));
                const countdownElement = document.getElementById('sessionCountdown');
                let countdown = Math.min(remainingSeconds, warningThreshold);
                
                // Update countdown display
                countdownElement.textContent = countdown;
                
                // Start countdown
                countdownInterval = setInterval(() => {
                    countdown--;
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        modal.hide();
                        window.location.href = '/login?expired=1';
                    } else {
                        countdownElement.textContent = countdown;
                    }
                }, 1000);
                
                // Show modal
                modal.show();
                
                // Handle "Stay Logged In" button
                document.getElementById('stayLoggedInBtn').addEventListener('click', function() {
                    clearInterval(countdownInterval);
                    modal.hide();
                    warningShown = false;
                    
                    // Make a request to refresh session
                    fetch('/api/auth/session-time', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                        .then(() => {
                            // Session refreshed, continue checking
                        })
                        .catch(error => {
                            // Silently handle network errors
                            if (error.name !== 'TypeError' || !error.message.includes('Load failed')) {
                                console.error('Error refreshing session:', error);
                            }
                        });
                }, { once: true });
                
                // Handle "Logout Now" button
                document.getElementById('logoutNowBtn').addEventListener('click', function() {
                    clearInterval(countdownInterval);
                    modal.hide();
                    window.location.href = '/logout';
                }, { once: true });
            }
            
            function clearIntervals() {
                if (sessionCheckInterval) {
                    clearInterval(sessionCheckInterval);
                }
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
            }
            
            // Start checking session time
            sessionCheckInterval = setInterval(checkSessionTime, checkInterval);
            
            // Initial check
            checkSessionTime();
            
            // Reset warning flag when user interacts with page
            let activityTimeout;
            ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, function() {
                    clearTimeout(activityTimeout);
                    activityTimeout = setTimeout(() => {
                        warningShown = false;
                    }, 1000);
                });
            });
        })();
    </script>

    <?php include __DIR__ . '/whats-new-secretary-modal.php'; ?>

    <!-- v11.0.0 feature surfaces -->
    <?php
        $GLOBALS['v11Lang'] = 'ar';
        $GLOBALS['v11Layout'] = 'secretary';
        $v11Lang = 'ar';
        $v11Layout = 'secretary';
        $notifCenterContext = 'secretary';
        $notifCenterLang = 'ar';
    ?>
    <?php include __DIR__ . '/notification-center.php'; ?>
    <?php include __DIR__ . '/todo-drawer.php'; ?>
    <?php include __DIR__ . '/cmdk-palette.php'; ?>
    <?php include __DIR__ . '/patient-hover-card.php'; ?>
    <?php include __DIR__ . '/keyboard-help.php'; ?>
    <?php include __DIR__ . '/quick-note-modal.php'; ?>
    <?php include __DIR__ . '/notes-drawer.php'; ?>

    <!-- v11.0.0 feature JS bundle -->
    <script>
        window.__GLOBAL_SEARCH_CONFIG__ = { mode: 'secretary' };
        window.kbdHelpRoutes = {
            dashboard: '/secretary/dashboard',
            calendar: '/secretary/bookings',
            bookings: '/secretary/bookings',
            payments: '/secretary/payments',
            patients: '/secretary/patients',
            profile: '/secretary/profile',
            board: '/secretary/dashboard',
            settings: '/secretary/settings'
        };
    </script>
    <script src="/app/Views/layouts/digit-normalizer.js?v=<?= file_exists(__DIR__ . '/digit-normalizer.js') ? filemtime(__DIR__ . '/digit-normalizer.js') : time() ?>"></script>
    <script defer src="/app/Views/layouts/v11-i18n.js?v=<?= file_exists(__DIR__ . '/v11-i18n.js') ? filemtime(__DIR__ . '/v11-i18n.js') : time() ?>"></script>
    <script defer src="/app/Views/layouts/global-search.js?v=<?= file_exists(__DIR__ . '/global-search.js') ? filemtime(__DIR__ . '/global-search.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/patient-color.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/patient-color.js') ? filemtime(__DIR__ . '/../doctor/assets/js/patient-color.js') : time() ?>"></script>
    <!-- Shared action registry — single source of truth for palette + dock (load before both) -->
    <script defer src="/app/Views/doctor/assets/js/actions-registry.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/actions-registry.js') ? filemtime(__DIR__ . '/../doctor/assets/js/actions-registry.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/notification-center.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/notification-center.js') ? filemtime(__DIR__ . '/../doctor/assets/js/notification-center.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/todo-drawer.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/todo-drawer.js') ? filemtime(__DIR__ . '/../doctor/assets/js/todo-drawer.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/cmdk.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/cmdk.js') ? filemtime(__DIR__ . '/../doctor/assets/js/cmdk.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/patient-hover.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/patient-hover.js') ? filemtime(__DIR__ . '/../doctor/assets/js/patient-hover.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/keyboard-help.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/keyboard-help.js') ? filemtime(__DIR__ . '/../doctor/assets/js/keyboard-help.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/quick-note.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/quick-note.js') ? filemtime(__DIR__ . '/../doctor/assets/js/quick-note.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/note-templates.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/note-templates.js') ? filemtime(__DIR__ . '/../doctor/assets/js/note-templates.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/focus-mode.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/focus-mode.js') ? filemtime(__DIR__ . '/../doctor/assets/js/focus-mode.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/notes-drawer.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/notes-drawer.js') ? filemtime(__DIR__ . '/../doctor/assets/js/notes-drawer.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/theme-palette.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/theme-palette.js') ? filemtime(__DIR__ . '/../doctor/assets/js/theme-palette.js') : time() ?>"></script>
    <!-- v11: notice-bar clock/calendar + appointments popovers -->
    <script defer src="/app/Views/secretary/assets/js/secretary-notice-bar.js?v=<?= file_exists(__DIR__ . '/../secretary/assets/js/secretary-notice-bar.js') ? filemtime(__DIR__ . '/../secretary/assets/js/secretary-notice-bar.js') : time() ?>"></script>
    <!-- v11: header notice bar — live clock + next appointment (clinic-scoped) -->
    <script>
    (function () {
        var DAYS = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        var MONTHS = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]; }); }
        function t12(hms) { if (!hms) return ''; var p = hms.split(':'); var H = parseInt(p[0], 10); var ap = H >= 12 ? 'م' : 'ص'; var h = H % 12 || 12; return h + ':' + p[1] + ' ' + ap; }

        var clockEl = document.getElementById('secNoticeClock');
        function tick() {
            if (!clockEl) return;
            var n = new Date(), h = n.getHours(), m = String(n.getMinutes()).padStart(2, '0'), s = String(n.getSeconds()).padStart(2, '0');
            var ap = h >= 12 ? 'م' : 'ص'; h = h % 12 || 12;
            clockEl.textContent = DAYS[n.getDay()] + ' ' + n.getDate() + ' ' + MONTHS[n.getMonth()] + ' · ' + h + ':' + m + ':' + s + ' ' + ap;
        }
        if (clockEl) { tick(); setInterval(tick, 1000); }

        var nextEl = document.getElementById('secNoticeNext');
        if (nextEl) {
            fetch('/api/secretary/next-appointments', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); }).then(function (res) {
                    var items = (res && res.items) || [];
                    if (!items.length) { nextEl.innerHTML = '<i class="bi bi-calendar-event"></i><span>لا مواعيد قادمة</span>'; return; }
                    var today = new Date(); today.setHours(0, 0, 0, 0);
                    var tom = new Date(today); tom.setDate(tom.getDate() + 1);
                    function when(d) { var ad = new Date(d + 'T00:00:00'); ad.setHours(0, 0, 0, 0); if (ad.getTime() === today.getTime()) return 'اليوم'; if (ad.getTime() === tom.getTime()) return 'غداً'; return d; }
                    function itemHtml(a) {
                        var name = ((a.first_name || '') + ' ' + (a.last_name || '')).replace(/\s+/g, ' ').trim() || 'مريض';
                        return '<span class="nb-next-item"><i class="bi bi-calendar-event"></i><span>الموعد التالي: <b>' + esc(name) + '</b> · ' + esc(when(a.date)) + ' ' + esc(t12(a.start_time)) + '</span></span>';
                    }
                    // Single appointment → no slider, no animation.
                    if (items.length === 1) { nextEl.innerHTML = itemHtml(items[0]); return; }
                    // Vertical slider: stack all items + a clone of the first for a seamless loop.
                    var H = 17, n = items.length;
                    var rows = items.map(itemHtml).join('') + itemHtml(items[0]);
                    nextEl.innerHTML = '<span class="nb-next-vp"><span class="nb-next-track">' + rows + '</span></span>';
                    var track = nextEl.querySelector('.nb-next-track');
                    var i = 0;
                    setInterval(function () {
                        i++;
                        track.classList.remove('no-anim');
                        track.style.transform = 'translateY(-' + (i * H) + 'px)';
                        if (i === n) { // landed on the clone (== first) → snap back to real first
                            setTimeout(function () {
                                track.classList.add('no-anim');
                                track.style.transform = 'translateY(0px)';
                                i = 0;
                            }, 480);
                        }
                    }, 4000);
                }).catch(function () { nextEl.innerHTML = ''; });
        }
    })();
    </script>
</body>
</html>
