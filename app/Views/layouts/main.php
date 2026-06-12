<?php
    // Eye-tools gate — the ophthalmology calculators (header markup AND the ~5.3k-line
    // ophthalmology-tools.js bundle) load ONLY on the Appointment + Patient-profile
    // detail pages, cutting header clutter and JS payload off every other doctor page.
    // Detail pages only (numeric id) — not the /doctor/appointments or /doctor/patients lists.
    $__obPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $__showEyeTools = (bool) preg_match('#^/doctor/(appointments|patients)/\d+#', $__obPath);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-layout="doctor">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HClinic / Roaya Clinic' ?></title>
    
    <!-- Meta Description -->
    <meta name="description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta name="keywords" content="clinic, eye care, ophthalmology, medical, healthcare">
    <meta name="author" content="Ahmed Helal">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?><?= $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'HClinic / Roaya Clinic') ?>">
    <meta property="og:description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta property="og:image" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?>/assets/images/Light.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="HClinic / Roaya Clinic">
    <meta property="og:locale" content="ar_EG">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?><?= $_SERVER['REQUEST_URI'] ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($title ?? 'HClinic / Roaya Clinic') ?>">
    <meta name="twitter:description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta name="twitter:image" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?>/assets/images/Light.png">
    
    <!-- Favicons (web root is site/public, so /assets/fav/* is the correct URL).
         Theme-matched (Light/Dark) variants — actual <link> hrefs are swapped
         to the right theme by the inline pre-paint script below before first
         paint. These defaults render in the light variant in the rare case
         that the script doesn't run (JS off). -->
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
    
    <!-- Cairo Font + Plus Jakarta Sans (LTR doctor/admin UI font) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Doctor Pages Consolidated CSS -->
    <link href="/app/Views/layouts/style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/doc-sidebar-icons.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/doc-sidebar-icons.css') ? filemtime(__DIR__ . '/../doctor/assets/css/doc-sidebar-icons.css') : time() ?>" rel="stylesheet">
    <!-- Unified Glass / Indigo design system (loaded AFTER style.css so its tokens win) -->
    <link href="/app/Views/layouts/design-system/tokens.css?v=<?= file_exists(__DIR__ . '/design-system/tokens.css') ? filemtime(__DIR__ . '/design-system/tokens.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/design-system/design-system.css?v=<?= file_exists(__DIR__ . '/design-system/design-system.css') ? filemtime(__DIR__ . '/design-system/design-system.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/design-system/scrollbars.css?v=<?= file_exists(__DIR__ . '/design-system/scrollbars.css') ? filemtime(__DIR__ . '/design-system/scrollbars.css') : time() ?>" rel="stylesheet">
    <!-- Shared modal kit (center + animate + drag affordance) — after design-system so it wins -->
    <link href="/app/Views/layouts/modal-kit.css?v=<?= file_exists(__DIR__ . '/modal-kit.css') ? filemtime(__DIR__ . '/modal-kit.css') : time() ?>" rel="stylesheet">
    <!-- Shared weather visuals (animated scene/icons + eye-care advisory + UV) -->
    <link href="/app/Views/layouts/weather-fx.css?v=<?= file_exists(__DIR__ . '/weather-fx.css') ? filemtime(__DIR__ . '/weather-fx.css') : time() ?>" rel="stylesheet">

    <!-- v12.0.0 feature CSS bundle -->
    <link href="/app/Views/doctor/assets/css/notification-center.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/notification-center.css') ? filemtime(__DIR__ . '/../doctor/assets/css/notification-center.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/chat-widget.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/chat-widget.css') ? filemtime(__DIR__ . '/../doctor/assets/css/chat-widget.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/todo-drawer.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/todo-drawer.css') ? filemtime(__DIR__ . '/../doctor/assets/css/todo-drawer.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/cmdk.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/cmdk.css') ? filemtime(__DIR__ . '/../doctor/assets/css/cmdk.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/patient-hover.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/patient-hover.css') ? filemtime(__DIR__ . '/../doctor/assets/css/patient-hover.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/keyboard-help.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/keyboard-help.css') ? filemtime(__DIR__ . '/../doctor/assets/css/keyboard-help.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/quick-note.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/quick-note.css') ? filemtime(__DIR__ . '/../doctor/assets/css/quick-note.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/note-templates.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/note-templates.css') ? filemtime(__DIR__ . '/../doctor/assets/css/note-templates.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/focus-mode.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/focus-mode.css') ? filemtime(__DIR__ . '/../doctor/assets/css/focus-mode.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/theme-palette.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/theme-palette.css') ? filemtime(__DIR__ . '/../doctor/assets/css/theme-palette.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/celebration.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/celebration.css') ? filemtime(__DIR__ . '/../doctor/assets/css/celebration.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/image-viewer-modal.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/image-viewer-modal.css') ? filemtime(__DIR__ . '/../doctor/assets/css/image-viewer-modal.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/notes-drawer.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/notes-drawer.css') ? filemtime(__DIR__ . '/../doctor/assets/css/notes-drawer.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/header-chips.css?v=<?= file_exists(__DIR__ . '/header-chips.css') ? filemtime(__DIR__ . '/header-chips.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/global-search-panel.css?v=<?= file_exists(__DIR__ . '/global-search-panel.css') ? filemtime(__DIR__ . '/global-search-panel.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/layouts/push-toast-center.css?v=<?= file_exists(__DIR__ . '/push-toast-center.css') ? filemtime(__DIR__ . '/push-toast-center.css') : time() ?>" rel="stylesheet">
    <link href="/app/Views/doctor/assets/css/note-bg.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/note-bg.css') ? filemtime(__DIR__ . '/../doctor/assets/css/note-bg.css') : time() ?>" rel="stylesheet">
    <!-- Timepicker UI CSS (Local) -->
    <link href="/app/Views/layouts/timepicker-ui-main/css/main.css?v=<?= file_exists(__DIR__ . '/timepicker-ui-main/css/main.css') ? filemtime(__DIR__ . '/timepicker-ui-main/css/main.css') : time() ?>" rel="stylesheet">
    
    <!-- Theme + logo + favicon pre-paint. Runs synchronously in <head> BEFORE
         any <body> content is parsed, so by the time #clinicLogo and the
         favicon <link> tags are interpreted, they already point at the
         theme-matched asset — no flash from Light → Dark on dark-mode refreshes. -->
    <script>
        (function() {
            // v12.0.0 — Theme + Palette + Auto-schedule pre-paint. Runs in <head>
            // BEFORE any <body> content parses, so logo, favicon AND data-palette
            // are all resolved on first paint — no flashes.

            var html = document.documentElement;
            var ALLOWED_PALETTES = ['indigo','emerald','rose','slate','amber','ocean'];

            // 1) Palette (data-palette attr). Falls back to indigo.
            var savedPalette = null;
            try { savedPalette = localStorage.getItem('appPalette'); } catch (e) {}
            var palette = (ALLOWED_PALETTES.indexOf(savedPalette) >= 0) ? savedPalette : 'indigo';
            html.setAttribute('data-palette', palette);

            // 2) Theme — manual saved theme OR auto-schedule (v11 new).
            var savedTheme = null, autoSched = false, darkFrom = '19:00', lightFrom = '07:00';
            try {
                savedTheme = localStorage.getItem('appTheme');
                autoSched  = localStorage.getItem('appThemeAutoSchedule') === '1';
                darkFrom   = localStorage.getItem('appThemeDarkFrom')  || '19:00';
                lightFrom  = localStorage.getItem('appThemeLightFrom') || '07:00';
            } catch (e) {}

            var theme;
            if (autoSched) {
                // Auto-schedule mode: pick by current local time, wrap-around range.
                var now = new Date();
                var mins = now.getHours() * 60 + now.getMinutes();
                var parse = function (s) { var p = String(s).split(':'); return (+p[0]) * 60 + (+(p[1] || 0)); };
                var darkStart  = parse(darkFrom);
                var lightStart = parse(lightFrom);
                var isDark = (mins >= darkStart) || (mins < lightStart);
                theme = isDark ? 'dark' : 'light';
            } else {
                theme = (savedTheme === 'light' || savedTheme === 'dark') ? savedTheme : 'dark';
            }
            html.classList.toggle('dark', theme === 'dark');
            html.classList.add('theme-loaded');

            // 3) Pre-resolve the sidebar logo + favicon URLs.
            window.__INITIAL_LOGO_SRC__ = theme === 'dark'
                ? '/assets/images/Dark.png'
                : '/assets/images/Light.png';
            window.__INITIAL_FAVICONS__ = {
                ico:   theme === 'dark' ? '/assets/fav/Dark.ico'         : '/assets/fav/Light.ico',
                p32:   theme === 'dark' ? '/assets/fav/Dark-32x32.png'   : '/assets/fav/Light-32x32.png',
                p192:  theme === 'dark' ? '/assets/fav/Dark-192x192.png' : '/assets/fav/Light-192x192.png',
                p512:  theme === 'dark' ? '/assets/fav/Dark-512x512.png' : '/assets/fav/Light-512x512.png',
                apple: theme === 'dark' ? '/assets/fav/Dark-180x180.png' : '/assets/fav/Light-180x180.png',
            };

            // 4) Rewrite favicon <link> hrefs NOW so the tab icon never starts wrong.
            var setHref = function(id, href) {
                var el = document.getElementById(id);
                if (el) el.href = href;
            };
            setHref('faviconIco',   window.__INITIAL_FAVICONS__.ico);
            setHref('faviconApple', window.__INITIAL_FAVICONS__.apple);
            setHref('favicon32',    window.__INITIAL_FAVICONS__.p32);
            setHref('favicon192',   window.__INITIAL_FAVICONS__.p192);
            setHref('favicon512',   window.__INITIAL_FAVICONS__.p512);
        })();
    </script>

    <!-- Clinics bootstrap: render the (small, static) clinic list server-side so
         every modal dropdown can populate synchronously on first open without
         waiting on /api/clinics. Doctors/admins see all active clinics; the
         secretary layout overrides this list with just their own clinic. -->
    <?php
        try {
            $__clinicsBootstrapPdo = \App\Config\Database::getInstance()->getConnection();
            $__clinicsBootstrap = $__clinicsBootstrapPdo->query("
                SELECT id, code, name_ar, name_en
                FROM clinics
                WHERE is_active = 1
                ORDER BY sort_order ASC, id ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);
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
    <!-- Pre-paint sidebar mode restore — must run before the sidebar paints,
         otherwise the user sees a "wide → mini" flicker on every navigation. -->
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
    <?php require __DIR__ . '/../doctor/partials/doc-nav-icons.php'; ?>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="clinic-logo">
                <img id="clinicLogo" src="/assets/images/Light.png" data-light-src="/assets/images/Light.png" data-dark-src="/assets/images/Dark.png" alt="HClinic / Roaya Clinic" style="width: 32px; height: 32px; margin-right: 0.75rem;">
                <script>
                    // Runs immediately after #clinicLogo enters the DOM and BEFORE
                    // it commits the initial Light.png src to the network. Rewrites
                    // src to the theme-correct asset that was resolved in <head>,
                    // so the user never sees the Light→Dark swap on dark-mode refresh.
                    (function() {
                        var img = document.getElementById('clinicLogo');
                        if (img && window.__INITIAL_LOGO_SRC__) img.src = window.__INITIAL_LOGO_SRC__;
                    })();
                </script>
                <div class="clinic-name">HClinic / Roaya</div>
            </div>
        </div>
        
        <div class="user-info">
            <div class="d-flex flex-column align-items-center text-center">
                <div class="user-avatar mb-2" id="sidebarUserAvatar">
                    <?php 
                    $currentUser = $this->getCurrentUser();
                    if (!empty($currentUser['profile_image'])): 
                        $profileImagePath = strpos($currentUser['profile_image'], '/public/') === 0 ? $currentUser['profile_image'] : '/public' . $currentUser['profile_image'];
                    ?>
                        <img src="<?= htmlspecialchars(avatar_thumb($profileImagePath, 96)) ?>"
                             alt="Profile"
                             class="user-avatar-img"
                             data-profile-image="<?= htmlspecialchars($profileImagePath) ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="user-avatar-fallback" style="display: none;">
                            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="htooltip user-avatar-htooltip">
                            <img src="<?= htmlspecialchars(avatar_thumb($profileImagePath, 256)) ?>"
                                 alt="Profile"
                                 class="user-avatar-preview-image">
                        </span>
                    <?php else: ?>
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <a href="/doctor/profile" class="user-name-link">
                        <h6 class="mb-1"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></h6>
                    </a>
                    <small><?= ucfirst($currentUser['role'] ?? 'user') ?></small>
                </div>
            </div>
        </div>
        
        <nav class="nav-menu">
            <?php if ($this->getCurrentUser()['role'] === 'doctor'): ?>
                <div class="nav-item">
                    <a href="/doctor/dashboard" class="nav-link <?= $this->isActiveRoute('/doctor/dashboard') ? 'active' : '' ?>">
                        <?= doc_nav_icon('dashboard') ?>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/calendar" class="nav-link <?= $this->isActiveRoute('/doctor/calendar') ? 'active' : '' ?>">
                        <?= doc_nav_icon('calendar') ?>
                        Calendar
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/patients" class="nav-link <?= $this->isActiveRoute('/doctor/patients') ? 'active' : '' ?>">
                        <?= doc_nav_icon('patients') ?>
                        Patients
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/board" class="nav-link <?= $this->isActiveRoute('/doctor/board') ? 'active' : '' ?>">
                        <?= doc_nav_icon('board') ?>
                        Patients Board
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/drugs" class="nav-link <?= $this->isActiveRoute('/doctor/drugs') ? 'active' : '' ?>">
                        <?= doc_nav_icon('drugs') ?>
                        Drugs Database
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/instruction-templates" class="nav-link <?= $this->isActiveRoute('/doctor/instruction-templates') ? 'active' : '' ?>">
                        <?= doc_nav_icon('tags') ?>
                        Tags and Templates
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/payments" class="nav-link <?= $this->isActiveRoute('/doctor/payments') ? 'active' : '' ?>">
                        <?= doc_nav_icon('payments') ?>
                        Financial Management
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/reports" class="nav-link <?= $this->isActiveRoute('/doctor/reports') ? 'active' : '' ?>">
                        <?= doc_nav_icon('reports') ?>
                        Reports
                    </a>
                </div>
                <div class="nav-item has-submenu <?= ($this->isActiveRoute('/doctor/glasses') || $this->isActiveRoute('/doctor/medications')) ? 'active' : '' ?>">
                    <a href="#" class="nav-link nav-link-toggle">
                        <i class="bi bi-archive"></i>
                        Medical Storage
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>
                    <div class="nav-submenu">
                        <a href="/doctor/medications" class="nav-submenu-link <?= $this->isActiveRoute('/doctor/medications') ? 'active' : '' ?>">
                            <i class="bi bi-capsule"></i>
                            Medical Prescriptions
                        </a>
                        <a href="/doctor/glasses" class="nav-submenu-link <?= $this->isActiveRoute('/doctor/glasses') ? 'active' : '' ?>">
                            <i class="bi bi-eyeglasses"></i>
                            Glasses Prescriptions
                        </a>
                        <a href="/doctor/media" class="nav-submenu-link <?= $this->isActiveRoute('/doctor/media') ? 'active' : '' ?>">
                            <i class="bi bi-images"></i>
                            Patients Media
                        </a>
                    </div>
                </div>
                <div class="nav-item">
                    <a href="/doctor/alerts" class="nav-link <?= $this->isActiveRoute('/doctor/alerts') ? 'active' : '' ?>">
                        <?= doc_nav_icon('alerts') ?>
                        Alerts
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/notes" class="nav-link <?= $this->isActiveRoute('/doctor/notes') ? 'active' : '' ?>">
                        <?= doc_nav_icon('notes') ?>
                        Notes
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/settings" class="nav-link <?= $this->isActiveRoute('/doctor/settings') ? 'active' : '' ?>">
                        <?= doc_nav_icon('settings') ?>
                        Settings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/profile" class="nav-link <?= $this->isActiveRoute('/doctor/profile') ? 'active' : '' ?>">
                        <?= doc_nav_icon('profile') ?>
                        Profile
                    </a>
                </div>
            <?php elseif ($this->getCurrentUser()['role'] === 'secretary'): ?>
                <div class="nav-item">
                    <a href="/secretary/dashboard" class="nav-link <?= $this->isActiveRoute('/secretary/dashboard') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/secretary/bookings" class="nav-link <?= $this->isActiveRoute('/secretary/bookings') ? 'active' : '' ?>">
                        <i class="bi bi-calendar-check"></i>
                        Bookings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/secretary/payments" class="nav-link <?= $this->isActiveRoute('/secretary/payments') ? 'active' : '' ?>">
                        <i class="bi bi-credit-card"></i>
                        Payments
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/secretary/patients" class="nav-link <?= $this->isActiveRoute('/secretary/patients') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        Patients
                    </a>
                </div>
            <?php elseif ($this->getCurrentUser()['role'] === 'admin'): ?>
                <div class="nav-item">
                    <a href="/admin/dashboard" class="nav-link <?= $this->isActiveRoute('/admin/dashboard') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/users" class="nav-link <?= $this->isActiveRoute('/admin/users') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        Users
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/backup" class="nav-link <?= $this->isActiveRoute('/admin/backup') ? 'active' : '' ?>">
                        <i class="bi bi-database"></i>
                        Backup
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/media" class="nav-link <?= $this->isActiveRoute('/admin/media') ? 'active' : '' ?>">
                        <i class="bi bi-images"></i>
                        Media
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/notifications" class="nav-link <?= $this->isActiveRoute('/admin/notifications') ? 'active' : '' ?>">
                        <i class="bi bi-bell"></i>
                        System Notifications
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/settings" class="nav-link <?= $this->isActiveRoute('/admin/settings') ? 'active' : '' ?>">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                </div>
            <?php endif; ?>
            
            <?php 
            // Check if admin is in View As mode using session directly
            if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
            ?>
            <div class="nav-item mt-auto">
                <a href="/admin/stop-view-as" class="nav-link text-warning" style="font-weight: bold; background-color: rgba(255, 193, 7, 0.1);">
                    <i class="fas fa-arrow-left"></i>
                    Exit View As
                </a>
            </div>
            <?php endif; ?>
            
            <div class="nav-item mt-auto">
                <a href="/logout" class="nav-link text-danger">
                    <?php if ($this->getCurrentUser()['role'] === 'doctor'): ?>
                        <?= doc_nav_icon('logout') ?>
                    <?php else: ?>
                        <i class="bi bi-box-arrow-right"></i>
                    <?php endif; ?>
                    Logout
                </a>
            </div>
            
            <!-- Version info -->
            <div class="sidebar-footer p-3 text-center border-top">
                <small class="sidebar-footer-text">
                    <div class="mb-1">
                        HClinic / Roaya Clinic v12.0.0
                        <span aria-hidden="true">·</span>
                        <a href="https://hclinic.clinic/docs/opth/" target="_blank" rel="noopener" class="sidebar-footer-link"><i class="bi bi-book me-1"></i>Docs</a>
                    </div>
                    <div>© 2026 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none sidebar-footer-link">Ahmed Helal</a></div>
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
                            VIEW AS MODE ACTIVE - You are viewing as: <strong><?= ucfirst($_SESSION['view_as_role'] ?? 'Unknown') ?></strong>
                        </h4>
                        <small>You are currently previewing the <?= ucfirst($_SESSION['view_as_role'] ?? 'Unknown') ?> interface</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/stop-view-as" class="btn btn-dark btn-lg" style="font-weight: bold; padding: 10px 20px;">
                            <i class="fas fa-arrow-left me-2"></i>
                            EXIT VIEW AS
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="top-bar">
            <!-- Notice Bar Widget - At the top of all actions -->
            <div class="notice-bar">
                <div class="notice-bar-content">
                    <?php if ($this->getCurrentUser()['role'] === 'doctor'): ?>
                    <?php if ($__showEyeTools): /* eye tools only on appointment/patient pages — see top of file */ ?>
                    <div class="notice-bar-column notice-bar-column-4">
                        <div class="notice-bar-column-4-inner">
                            <!-- Mobile/Tablet Tools Button -->
                            <button class="notice-bar-ophthalmology-tools-btn" id="noticeBarOphthalmologyToolsBtn" style="display: none;">
                                <i class="bi bi-grid-3x3-gap"></i>
                                <span>Ophthalmology Tools</span>
                            </button>
                            
                            <div class="notice-bar-column-4-left">
                                <nav class="notice-bar-tools-nav">
                                    <ul class="notice-bar-tools-menu">
                                        <li class="notice-bar-tools-parent">
                                            <a href="#" class="notice-bar-iol-btn" id="noticeBarIOLBtn">
                                                <i class="bi bi-calculator"></i>
                                                <span>Core Calculators</span>
                                            </a>
                                            <ul class="notice-bar-tools-child">
                                                <li>
                                                    <a href="#" id="calculatorsDropdownIOL">
                                                        <i class="bi bi-calculator me-2"></i>
                                                        <span>IOL Power Calculator</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="calculatorsDropdownPediatric">
                                                        <i class="bi bi-child me-2"></i>
                                                        <span>Pediatric IOL Undercorrection</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="calculatorsDropdownAstigmatism">
                                                        <i class="bi bi-circle-half me-2"></i>
                                                        <span>Corneal Astigmatism Calculator</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="notice-bar-tools-parent">
                                            <a href="#" class="notice-bar-iol-btn" id="noticeBarGlaucomaBtn">
                                                <i class="bi bi-eyedropper"></i>
                                                <span>Glaucoma Calc</span>
                                            </a>
                                            <ul class="notice-bar-tools-child">
                                                <li>
                                                    <a href="#" id="glaucomaDropdownIOP">
                                                        <i class="bi bi-graph-up me-2"></i>
                                                        <span>IOP Trend Analyzer</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="glaucomaDropdownTargetIOP">
                                                        <i class="bi bi-bullseye me-2"></i>
                                                        <span>Target IOP Calculator</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="notice-bar-tools-parent">
                                            <a href="#" class="notice-bar-iol-btn" id="noticeBarVisionBtn">
                                                <i class="bi bi-eye"></i>
                                                <span>Vision</span>
                                            </a>
                                            <ul class="notice-bar-tools-child">
                                                <li>
                                                    <a href="#" id="visionDropdownRefraction">
                                                        <i class="bi bi-eye me-2"></i>
                                                        <span>Refraction Consistency Checker</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="visionDropdownVA">
                                                        <i class="bi bi-graph-up-arrow me-2"></i>
                                                        <span>Visual Acuity Progress Calculator</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="notice-bar-tools-parent">
                                            <a href="#" class="notice-bar-iol-btn" id="noticeBarCorneaBtn">
                                                <i class="bi bi-droplet"></i>
                                                <span>Cornea</span>
                                            </a>
                                            <ul class="notice-bar-tools-child">
                                                <li>
                                                    <a href="#" id="corneaDropdownOSDI">
                                                        <i class="bi bi-droplet me-2"></i>
                                                        <span>Dry Eye Severity Index (OSDI)</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="corneaDropdownPachymetry">
                                                        <i class="bi bi-eye me-2"></i>
                                                        <span>Pachymetry-Adjusted IOP Calculator</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="notice-bar-tools-parent">
                                            <a href="#" class="notice-bar-iol-btn" id="noticeBarRetinaBtn">
                                                <i class="bi bi-circle"></i>
                                                <span>Retina</span>
                                            </a>
                                            <ul class="notice-bar-tools-child">
                                                <li>
                                                    <a href="#" id="retinaDropdownDiabetic">
                                                        <i class="bi bi-heart-pulse me-2"></i>
                                                        <span>Diabetic Retinopathy Risk Estimator</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="retinaDropdownMacular">
                                                        <i class="bi bi-graph-up me-2"></i>
                                                        <span>Macular Thickness Trend Analyzer</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="notice-bar-tools-parent">
                                            <a href="#" class="notice-bar-iol-btn" id="noticeBarCataractBtn">
                                                <i class="bi bi-scissors"></i>
                                                <span>Cataract</span>
                                            </a>
                                            <ul class="notice-bar-tools-child">
                                                <li>
                                                    <a href="#" id="cataractDropdownReadiness">
                                                        <i class="bi bi-clipboard-check me-2"></i>
                                                        <span>Cataract Surgery Readiness Score</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="cataractDropdownOutcome">
                                                        <i class="bi bi-graph-up-arrow me-2"></i>
                                                        <span>Post-Operative Outcome Analyzer</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                            <div class="notice-bar-column-4-right">

                            </div>
                        </div>
                    </div>
                    
                    <!-- Unified Ophthalmology Tools Dropdown (Mobile/Tablet) -->
                    <div class="ophthalmology-tools-dropdown" id="ophthalmologyToolsDropdown">
                        <div class="ophthalmology-tools-dropdown-content">
                            <!-- Core Calculators Section -->
                            <div class="ophthalmology-tools-section">
                                <div class="ophthalmology-tools-section-header">
                                    <i class="bi bi-calculator"></i>
                                    <span style="font-weight: bold;">Core Calculators</span>
                                </div>
                                <ul class="ophthalmology-tools-section-items">
                                    <li>
                                        <a href="#" id="mobileCalculatorsDropdownIOL">
                                            <i class="bi bi-calculator me-2"></i>
                                            <span>IOL Power Calculator</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="mobileCalculatorsDropdownPediatric">
                                            <i class="bi bi-child me-2"></i>
                                            <span>Pediatric IOL Undercorrection</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="mobileCalculatorsDropdownAstigmatism">
                                            <i class="bi bi-circle-half me-2"></i>
                                            <span>Corneal Astigmatism Calculator</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Glaucoma Calculators Section -->
                            <div class="ophthalmology-tools-section">
                                <div class="ophthalmology-tools-section-header">
                                    <i class="bi bi-eyedropper"></i>
                                    <span style="font-weight: bold;">Glaucoma Calculators</span>
                                </div>
                                <ul class="ophthalmology-tools-section-items">
                                    <li>
                                        <a href="#" id="mobileGlaucomaDropdownIOP">
                                            <i class="bi bi-graph-up me-2"></i>
                                            <span>IOP Trend Analyzer</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="mobileGlaucomaDropdownTargetIOP">
                                            <i class="bi bi-bullseye me-2"></i>
                                            <span>Target IOP Calculator</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Vision Tools Section -->
                            <div class="ophthalmology-tools-section">
                                <div class="ophthalmology-tools-section-header">
                                    <i class="bi bi-eye"></i>
                                    <span style="font-weight: bold;">Vision Tools</span>
                                </div>
                                <ul class="ophthalmology-tools-section-items">
                                    <li>
                                        <a href="#" id="mobileVisionDropdownRefraction">
                                            <i class="bi bi-eye me-2"></i>
                                            <span>Refraction Consistency Checker</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="mobileVisionDropdownVA">
                                            <i class="bi bi-graph-up-arrow me-2"></i>
                                            <span>Visual Acuity Progress Calculator</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Cornea Tools Section -->
                            <div class="ophthalmology-tools-section">
                                <div class="ophthalmology-tools-section-header">
                                    <i class="bi bi-droplet"></i>
                                    <span style="font-weight: bold;">Cornea Tools</span>
                                </div>
                                <ul class="ophthalmology-tools-section-items">
                                    <li>
                                        <a href="#" id="mobileCorneaDropdownOSDI">
                                            <i class="bi bi-droplet me-2"></i>
                                            <span>Dry Eye Severity Index (OSDI)</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="mobileCorneaDropdownPachymetry">
                                            <i class="bi bi-eye me-2"></i>
                                            <span>Pachymetry-Adjusted IOP Calculator</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Retina Tools Section -->
                            <div class="ophthalmology-tools-section">
                                <div class="ophthalmology-tools-section-header">
                                    <i class="bi bi-circle"></i>
                                    <span style="font-weight: bold;">Retina Tools</span>
                                </div>
                                <ul class="ophthalmology-tools-section-items">
                                    <li>
                                        <a href="#" id="mobileRetinaDropdownDiabetic">
                                            <i class="bi bi-heart-pulse me-2"></i>
                                            <span>Diabetic Retinopathy Risk Estimator</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="mobileRetinaDropdownMacular">
                                            <i class="bi bi-graph-up me-2"></i>
                                            <span>Macular Thickness Trend Analyzer</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Cataract Tools Section -->
                            <div class="ophthalmology-tools-section">
                                <div class="ophthalmology-tools-section-header">
                                    <i class="bi bi-scissors"></i>
                                    <span style="font-weight: bold;">Cataract Tools</span>
                                </div>
                                <ul class="ophthalmology-tools-section-items">
                                    <li>
                                        <a href="#" id="mobileCataractDropdownReadiness">
                                            <i class="bi bi-clipboard-check me-2"></i>
                                            <span>Cataract Surgery Readiness Score</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="mobileCataractDropdownOutcome">
                                            <i class="bi bi-graph-up-arrow me-2"></i>
                                            <span>Post-Operative Outcome Analyzer</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; /* $__showEyeTools — eye tools only on appointment/patient detail pages */ ?>

                    <div class="notice-bar-column notice-bar-column-3">
                        <i class="bi bi-calendar3"></i>
                        <span class="notice-bar-appointment-label">Next Appointment:</span>
                        <div class="notice-bar-appointment-slider">
                            <div class="appointment-scroller">
                                <div class="appointment-inner" id="noticeBarNextAppointment">
                                    <span class="spinner-border spinner-border-sm" role="status" style="width: 0.5rem; height: 0.5rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="notice-bar-column notice-bar-column-weather">
                        <div class="notice-bar-weather-icon" id="noticeBarWeatherIcon">
                            <div class="weather-icon-loading">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <span class="notice-bar-weather-temp" id="noticeBarWeatherTemp">--°C</span>
                        <!-- Warning Icons -->
                        <span class="notice-bar-weather-warning" id="noticeBarWeatherWarning" style="display: none;">
                            <span class="warning-triangle-icon">
                                <i class="bi bi-triangle-fill"></i>
                                <i class="bi bi-exclamation-lg warning-exclamation"></i>
                            </span>
                            <sup class="warning-icon-label">
                                <i class="bi bi-flower1" id="warningPollenIcon" style="display: none;"></i>
                                <i class="bi bi-eye" id="warningDryEyeIcon" style="display: none;"></i>
                            </sup>
                        </span>
                    </div>
                    <div class="notice-bar-column notice-bar-column-1">
                        <i class="bi bi-clock"></i>
                        <span id="noticeBarDateTime">Loading...</span>
                    </div>
                </div>
            </div>
            
            <button class="btn sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="page-title">
                <h1>
                    <?= $pageTitle ?? 'Dashboard' ?>
                    <?php if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.6em; animation: pulse 2s infinite;">
                            VIEW AS: <?= strtoupper($_SESSION['view_as_role'] ?? 'Unknown') ?>
                        </span>
                    <?php endif; ?>
                </h1>
                <?php if (!empty($pageSubtitle)): ?>
                <small><?= $pageSubtitle ?></small>
                <?php endif; ?>
            </div>
            
            <div class="top-bar-cluster">
            <div class="top-actions">
                <?php if ($this->getCurrentUser()['role'] === 'doctor'): ?>
                <!-- Global Search Bar -->
                <div class="global-search-wrapper">
                    <button type="button" class="global-search-toggle d-xl-none" id="globalSearchToggle" title="Search" aria-label="Search">
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
                                       placeholder="Search appointments, patients, drugs, media..." 
                                       autocomplete="off">
                                <button class="btn btn-link global-search-clear d-none" id="globalSearchClear" type="button">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="global-search-hint" id="globalSearchHint">
                                <i class="bi bi-lightbulb"></i>
                                <span>Tip: Use '&' to refine by patient (name/ID) or '#' for date.
                                    <br>Examples: 'blured & Ahmed', 'blured & 123', 'blured # 2024-01-15'</span>
                            </div>
                            <div class="global-search-results" id="globalSearchResults"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php 
                // Check if admin is in View As mode using session directly
                if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
                ?>
                    <div class="view-as-indicator me-3">
                        <div class="alert alert-warning d-flex align-items-center mb-0 py-2 px-3" style="font-size: 0.9rem; border: 2px solid #ffc107;">
                            <i class="fas fa-eye me-2"></i>
                            <span><strong>VIEW AS MODE:</strong> <?= ucfirst($_SESSION['view_as_role'] ?? 'Unknown') ?></span>
                            <a href="/admin/stop-view-as" class="btn btn-sm btn-outline-warning ms-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                Exit
                            </a>
                        </div>
                    </div>
                    
                    <!-- Additional Exit Button -->
                    <a href="/admin/stop-view-as" class="btn btn-warning me-2" title="Exit View As Mode" style="font-weight: bold;">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Exit View As
                    </a>
                <?php endif; ?>
                
                <!-- Notifications Icon (Hidden for Admin) -->
                <?php if ($this->getCurrentUser()['role'] !== 'admin'): ?>
                <button class="btn btn-outline-secondary notifications-toggle" id="notificationsToggle" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notifications-badge" id="notificationsBadge" style="display: none;">0</span>
                </button>
                <?php endif; ?>
            </div>

            <div class="top-actions-quick" id="topActionsQuick" aria-label="Quick tools"></div>

            <div class="top-actions-theme">
                <label class="switch" for="themeToggleInput">
                    <input id="themeToggleInput" type="checkbox" />
                    <div class="slider round">
                        <div class="sun-moon">
                            <svg id="moon-dot-1" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-2" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-3" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-1" class="light-ray" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-2" class="light-ray" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-3" class="light-ray" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-1" class="cloud-dark" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-2" class="cloud-dark" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-3" class="cloud-dark" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-4" class="cloud-light" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-5" class="cloud-light" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-6" class="cloud-light" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                        </div>
                        <div class="stars">
                            <svg id="star-1" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                            <svg id="star-2" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                            <svg id="star-3" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                            <svg id="star-4" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                        </div>
                    </div>
                </label>
            </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <?= $content ?>
    </div>
    
    <!-- Scroll to Top Button (with scroll-progress ring) -->
    <button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
        <svg class="stt-ring" viewBox="0 0 44 44" aria-hidden="true">
            <circle class="stt-ring-track" cx="22" cy="22" r="19.5"></circle>
            <circle class="stt-ring-bar" cx="22" cy="22" r="19.5"></circle>
        </svg>
        <i class="bi bi-arrow-up"></i>
    </button>
    
    <!-- Notifications Panel (Hidden for Admin) -->
    <?php if ($this->getCurrentUser()['role'] !== 'admin'): ?>
    <div class="notifications-panel-overlay" id="notificationsOverlay"></div>
    <div class="notifications-panel" id="notificationsPanel">
        <div class="notifications-panel-content">
            <div class="notifications-panel-header">
                <h5><i class="bi bi-bell me-2"></i>Notifications</h5>
                <div class="notifications-panel-header-actions">
                    <button class="btn btn-sm btn-outline-secondary" id="markAllReadBtn" title="Mark All Read">
                        <i class="bi bi-check-all"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="clearAllBtn" title="Clear All">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="closeNotificationsBtn" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="notifications-panel-body" id="notificationsBody">
                <div class="notifications-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Quick Access Dock (Desktop Only - Hidden for Admin).
         visibility:hidden until updateDockVisibility() finishes — without
         this the dock paints full-size for one frame and then JS shrinks
         it to its saved minimized state, which the user sees as a shiver
         on every page navigation. main.js removes the inline style at the
         end of init so we get no flicker either way. -->
    <?php if ($this->getCurrentUser()['role'] !== 'admin'): ?>
    <div class="quick-access-dock" id="quickAccessDock" style="visibility:hidden;">
        <div class="dock-container">
            <a href="/doctor/calendar" class="dock-item" title="View Calendar">
                <i class="bi bi-calendar3"></i>
                <span class="htooltip">View Calendar</span>
            </a>
            <a href="/doctor/patients" class="dock-item" title="Patient List">
                <i class="bi bi-people"></i>
                <span class="htooltip">Patient List</span>
            </a>
            <a href="/doctor/board" class="dock-item" title="Patients Board">
                <i class="bi bi-kanban"></i>
                <span class="htooltip">Patients Board</span>
            </a>
            <a href="/doctor/drugs" class="dock-item" title="Drugs">
                <i class="bi bi-capsule"></i>
                <span class="htooltip">Drugs</span>
            </a>
            <a href="/doctor/notes" class="dock-item" title="Notes">
                <i class="bi bi-sticky"></i>
                <span class="htooltip">Notes</span>
            </a>
            <a href="/doctor/payments" class="dock-item" title="Financial">
                <i class="bi bi-credit-card"></i>
                <span class="htooltip">Financial</span>
            </a>
            <div class="dock-item dock-item-stack" id="medicalStorageDockItem" title="Medical Storage">
                <i class="bi bi-archive"></i>
                <span class="htooltip">Medical Storage</span>
                <div class="dock-stack-menu" id="medicalStorageStackMenu">
                    <a href="/doctor/medications" class="dock-stack-item">
                        <i class="bi bi-prescription"></i>
                        <span>Medical Prescriptions</span>
                    </a>
                    <a href="/doctor/glasses" class="dock-stack-item">
                        <i class="bi bi-eyeglasses"></i>
                        <span>Glasses Prescriptions</span>
                    </a>
                    <a href="/doctor/media" class="dock-stack-item">
                        <i class="bi bi-images"></i>
                        <span>Media</span>
                    </a>
                </div>
            </div>
            <a href="/doctor/alerts" class="dock-item" title="Alerts">
                <i class="bi bi-bell"></i>
                <span class="htooltip">Alerts</span>
            </a>
            <a href="/doctor/reports" class="dock-item" title="Reports">
                <i class="bi bi-graph-up"></i>
                <span class="htooltip">Reports</span>
            </a>
            <a href="/doctor/profile" class="dock-item" title="My Profile">
                <?php 
                $currentUser = $this->getCurrentUser();
                if (!empty($currentUser['profile_image'])): 
                    $profileImagePath = strpos($currentUser['profile_image'], '/public/') === 0 ? $currentUser['profile_image'] : '/public' . $currentUser['profile_image'];
                ?>
                    <img src="<?= htmlspecialchars(avatar_thumb($profileImagePath, 96)) ?>"
                         alt="My Profile"
                         class="dock-profile-image"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="bi bi-person-circle dock-profile-fallback" style="display: none;"></i>
                <?php else: ?>
                    <i class="bi bi-person-circle"></i>
                <?php endif; ?>
                <span class="htooltip">My Profile</span>
            </a>
            <a href="/doctor/settings" class="dock-item" title="Settings">
                <i class="bi bi-gear"></i>
                <span class="htooltip">Settings</span>
            </a>
            <div class="dock-divider"></div>
            <button class="dock-chat-btn" id="dockChatBtn" title="Chat" aria-label="Chat">
                <i class="bi bi-chat-dots"></i>
                <span class="chat-unread-badge" id="chatUnreadBadge" hidden>0</span>
            </button>
            <button class="dock-autohide-btn" id="dockAutohideBtn" title="Auto Hide Dock">
                <i class="bi bi-eye-slash" id="dockAutohideIcon"></i>
                <span class="htooltip" id="dockAutohideTooltip">Auto Hide Dock</span>
            </button>
            <button class="dock-minimize-btn" id="dockMinimizeBtn" title="Minimize Dock">
                <i class="bi bi-fullscreen-exit"></i>
                <div class="minimized-icon">
                    <!-- Row 1: 3 squares (columns 1, 2, 3) -->
                    <span class="minimized-icon-rect rect-r1-c1"></span>
                    <span class="minimized-icon-rect rect-r1-c2"></span>
                    <span class="minimized-icon-rect rect-r1-c3"></span>
                    <!-- Row 2: 4 squares (columns 1, 2, 3, 4) -->
                    <span class="minimized-icon-rect rect-r2-c1"></span>
                    <span class="minimized-icon-rect rect-r2-c2"></span>
                    <span class="minimized-icon-rect rect-r2-c3"></span>
                    <span class="minimized-icon-rect rect-r2-c4"></span>
                    <!-- Row 3: 3 squares starting from column 2 (columns 2, 3, 4) -->
                    <span class="minimized-icon-rect rect-r3-c2"></span>
                    <span class="minimized-icon-rect rect-r3-c3"></span>
                    <span class="minimized-icon-rect rect-r3-c4"></span>
                </div>
                <span class="htooltip" id="dockMinimizeTooltip">Minimize Dock</span>
            </button>
        </div>
    </div>
    <?php endif; ?>

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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Shared modal kit: unified pointer-drag + center + showConfirm/AlertModal (after Bootstrap) -->
    <script src="/app/Views/layouts/modal-kit.js?v=<?= file_exists(__DIR__ . '/modal-kit.js') ? filemtime(__DIR__ . '/modal-kit.js') : time() ?>"></script>
    <script src="/app/Views/doctor/assets/js/image-viewer-modal.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/image-viewer-modal.js') ? filemtime(__DIR__ . '/../doctor/assets/js/image-viewer-modal.js') : time() ?>"></script>
    <!-- Timepicker UI JS -->
    <script type="module">
        // Load TimepickerUI from local files
        import { TimepickerUI } from "/app/Views/layouts/timepicker-ui-main/timepicker-loader.js";
        // TimepickerUI is already exposed globally by the loader
    </script>
    <!-- Shared weather visuals/logic — must load before main.js + dashboard.js use it -->
    <script src="/app/Views/layouts/weather-fx.js?v=<?= filemtime(__DIR__ . '/weather-fx.js') ?>"></script>
    <script src="/app/Views/layouts/digit-normalizer.js?v=<?= file_exists(__DIR__ . '/digit-normalizer.js') ? filemtime(__DIR__ . '/digit-normalizer.js') : time() ?>"></script>
    <script src="/app/Views/layouts/main.js?v=<?= filemtime(__DIR__ . '/main.js') ?>"></script>
    <?php if ($__showEyeTools): ?>
    <!-- Ophthalmology calculators/tools — extracted from main.js, loaded ONLY on Appointment + Patient-profile pages (must follow main.js + the page content above). -->
    <script src="/app/Views/layouts/ophthalmology-tools.js?v=<?= filemtime(__DIR__ . '/ophthalmology-tools.js') ?>"></script>
    <?php endif; ?>
    <script src="/app/Views/layouts/clinics-loader.js?v=<?= filemtime(__DIR__ . '/clinics-loader.js') ?>"></script>
    
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

    <?php /* v12: the v9 modal file is kept on disk but the active What's-New wizard is now v12 (doctor-only). */ ?>
    <?php include __DIR__ . '/whats-new-v12-modal.php'; ?>

    <!-- v12.0.0 feature surfaces -->
    <?php include __DIR__ . '/notification-center.php'; ?>
    <?php include __DIR__ . '/todo-drawer.php'; ?>
    <?php include __DIR__ . '/cmdk-palette.php'; ?>
    <?php include __DIR__ . '/patient-hover-card.php'; ?>
    <?php include __DIR__ . '/keyboard-help.php'; ?>
    <?php include __DIR__ . '/quick-note-modal.php'; ?>
    <?php include __DIR__ . '/notes-drawer.php'; ?>
    <?php include __DIR__ . '/whatsapp-modal.php'; ?>

    <!-- v12.0.0 feature JS bundle (deferred so it doesn't block paint) -->
    <script defer src="/app/Views/doctor/assets/js/patient-color.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/patient-color.js') ? filemtime(__DIR__ . '/../doctor/assets/js/patient-color.js') : time() ?>"></script>
    <!-- Shared notes background presets + cross-surface live-sync bus (load before note surfaces) -->
    <script defer src="/app/Views/doctor/assets/js/note-bg.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/note-bg.js') ? filemtime(__DIR__ . '/../doctor/assets/js/note-bg.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/notes-sync.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/notes-sync.js') ? filemtime(__DIR__ . '/../doctor/assets/js/notes-sync.js') : time() ?>"></script>
    <!-- Merged view over both note stores (quick_notes + notes) for cross-surface display -->
    <script defer src="/app/Views/doctor/assets/js/notes-bridge.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/notes-bridge.js') ? filemtime(__DIR__ . '/../doctor/assets/js/notes-bridge.js') : time() ?>"></script>
    <!-- Shared action registry — single source of truth for palette + dock (load before both) -->
    <script defer src="/app/Views/doctor/assets/js/actions-registry.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/actions-registry.js') ? filemtime(__DIR__ . '/../doctor/assets/js/actions-registry.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/notification-center.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/notification-center.js') ? filemtime(__DIR__ . '/../doctor/assets/js/notification-center.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/chat-widget.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/chat-widget.js') ? filemtime(__DIR__ . '/../doctor/assets/js/chat-widget.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/todo-drawer.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/todo-drawer.js') ? filemtime(__DIR__ . '/../doctor/assets/js/todo-drawer.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/cmdk.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/cmdk.js') ? filemtime(__DIR__ . '/../doctor/assets/js/cmdk.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/patient-hover.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/patient-hover.js') ? filemtime(__DIR__ . '/../doctor/assets/js/patient-hover.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/keyboard-help.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/keyboard-help.js') ? filemtime(__DIR__ . '/../doctor/assets/js/keyboard-help.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/quick-note.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/quick-note.js') ? filemtime(__DIR__ . '/../doctor/assets/js/quick-note.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/note-templates.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/note-templates.js') ? filemtime(__DIR__ . '/../doctor/assets/js/note-templates.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/focus-mode.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/focus-mode.js') ? filemtime(__DIR__ . '/../doctor/assets/js/focus-mode.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/notes-drawer.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/notes-drawer.js') ? filemtime(__DIR__ . '/../doctor/assets/js/notes-drawer.js') : time() ?>"></script>
    <script defer src="/app/Views/doctor/assets/js/theme-palette.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/theme-palette.js') ? filemtime(__DIR__ . '/../doctor/assets/js/theme-palette.js') : time() ?>"></script>
    <?php
    $__waDb = \App\Config\Database::getInstance()->getConnection();
    $__waSettingsStmt = $__waDb->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('whatsapp_enabled', 'whatsapp_advanced_features', 'whatsapp_mod_appointments', 'whatsapp_mod_visits', 'whatsapp_mod_report', 'whatsapp_mod_patientlog')");
    $__waSettingsStmt->execute();
    $__waSettings = $__waSettingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $__waEnabled = (bool)($__waSettings['whatsapp_enabled'] ?? false);
    $__waAdvanced = (bool)($__waSettings['whatsapp_advanced_features'] ?? false);
    // Module flags default ON when the row is absent.
    $__waModAppt   = ($__waSettings['whatsapp_mod_appointments'] ?? '1') === '1';
    $__waModVisits = ($__waSettings['whatsapp_mod_visits'] ?? '1') === '1';
    $__waModReport = ($__waSettings['whatsapp_mod_report'] ?? '1') === '1';
    $__waModPlog   = ($__waSettings['whatsapp_mod_patientlog'] ?? '1') === '1';
    ?>
    <script>
        window.WHATSAPP_CONFIG = {
            enabled: <?= json_encode($__waEnabled) ?>,
            advanced: <?= json_encode($__waAdvanced) ?>,
            modules: {
                appointments: <?= json_encode($__waModAppt) ?>,
                visits: <?= json_encode($__waModVisits) ?>,
                report: <?= json_encode($__waModReport) ?>,
                patientLog: <?= json_encode($__waModPlog) ?>
            }
        };
    </script>
    <script defer src="/app/Views/doctor/assets/js/whatsapp.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/whatsapp.js') ? filemtime(__DIR__ . '/../doctor/assets/js/whatsapp.js') : time() ?>"></script>
</body>
</html>
