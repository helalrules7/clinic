<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<!-- v11.0.0 celebration notice bar — chat release fanfare. Layered FX:
     entrance reveal, aurora mesh, rotating border-beam, floating orbs,
     shimmer sweep, sparkle drift, gradient sweep, pulsing pill + CTA,
     one-shot confetti burst (sessionStorage). Dismissable per browser.
     7-day TTL from launch timestamp. Wizard auto-show disabled — bar only.
     Respects prefers-reduced-motion. -->
<?php
    // Launch epoch — bump when shipping a NEW release to extend visibility.
    $whatsNewLaunchTs   = strtotime('2026-06-06 12:00:00');
    $whatsNewLaunchDays = 7;
    $whatsNewVisibleUntil = $whatsNewLaunchTs + ($whatsNewLaunchDays * 24 * 3600);
    $whatsNewVersion = 'v11_0_4';
?>
<?php if (time() < $whatsNewVisibleUntil): ?>
<div class="whatsnew-notice whatsnew-celebrate mb-4" id="whatsNewNotice"
     data-version="<?= htmlspecialchars($whatsNewVersion) ?>"
     data-visible-until="<?= (int)$whatsNewVisibleUntil ?>">
    <span class="wn-aurora" aria-hidden="true"></span>
    <span class="wn-border-beam" aria-hidden="true"></span>
    <span class="whatsnew-notice-glow" aria-hidden="true"></span>
    <span class="wn-orbs" aria-hidden="true">
        <span class="wn-orb wn-orb-1"></span>
        <span class="wn-orb wn-orb-2"></span>
        <span class="wn-orb wn-orb-3"></span>
    </span>
    <span class="whatsnew-sparkles" aria-hidden="true"></span>
    <span class="wn-shimmer-sweep" aria-hidden="true"></span>
    <span class="whatsnew-confetti" id="whatsNewConfetti" aria-hidden="true"></span>
    <span class="whatsnew-notice-pill">
        <span class="wn-pill-ring" aria-hidden="true"></span>
        <i class="bi bi-stars wn-pill-star" aria-hidden="true"></i>
        <span class="wn-pill-label">v11.0.0</span>
        <span class="wn-pill-spark" aria-hidden="true">NEW</span>
    </span>
    <span class="whatsnew-notice-text">
        <strong class="wn-headline">Real-time chat is here!</strong>
        <span class="wn-subcopy">Message your secretary from the dock &mdash; glass panel, groups,
        emoji reactions, voice notes, photos, <strong>@patient</strong> chips, and
        <strong>✓✓</strong> read receipts. Your thread stays open as you move between pages.</span>
    </span>
    <button type="button" class="whatsnew-notice-cta"
            data-bs-toggle="modal" data-bs-target="#whatsNewV9Modal">
        <span class="wn-cta-shine" aria-hidden="true"></span>
        <span class="wn-cta-label">Tour what's new</span>
        <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </button>
    <button type="button" class="whatsnew-notice-close" id="whatsNewNoticeClose"
            aria-label="Dismiss update notice">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
<script src="/app/Views/doctor/assets/js/celebration.js?v=<?= file_exists(__DIR__ . '/assets/js/celebration.js') ? filemtime(__DIR__ . '/assets/js/celebration.js') : time() ?>"></script>
<script>
    (function () {
        var el  = document.getElementById('whatsNewNotice');
        if (!el) return;
        var ver = el.getAttribute('data-version');
        var dismissKey  = 'whatsNew_' + ver + '_noticeDismissed';
        var confettiKey = 'whatsNew_' + ver + '_confettiShown';
        try {
            if (localStorage.getItem(dismissKey) === '1') { el.style.display = 'none'; return; }
        } catch (_) {}
        var btn = document.getElementById('whatsNewNoticeClose');
        requestAnimationFrame(function () {
            el.classList.add('is-visible');
        });
        if (btn) btn.addEventListener('click', function () {
            el.classList.add('is-dismissing');
            setTimeout(function () { el.style.display = 'none'; }, 420);
            try { localStorage.setItem(dismissKey, '1'); } catch (_) {}
        });
        // Confetti: dual-wave burst once per session on first dashboard load.
        try {
            var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!reduced && sessionStorage.getItem(confettiKey) !== '1' && window.fireCelebrationConfetti) {
                var box = document.getElementById('whatsNewConfetti');
                window.fireCelebrationConfetti(box, { waves: 2, count: 56 });
                sessionStorage.setItem(confettiKey, '1');
            }
        } catch (_) {}
    })();
</script>
<?php endif; ?>

<!-- Welcome / overview hero (B1) -->
<?php $h = (int) date('H'); $heroGreet = $h < 12 ? 'Good morning' : ($h < 18 ? 'Good afternoon' : 'Good evening'); ?>
<div class="ds-hero mb-4">
    <h1><?= $heroGreet ?>, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Doctor') ?> 👋</h1>
    <p>Here's today's overview — <strong><?= $stats['total'] ?? 0 ?></strong> appointment<?= (($stats['total'] ?? 0) == 1 ? '' : 's') ?> today, <strong><?= $stats['completed'] ?? 0 ?></strong> completed.</p>
    <div class="ds-hero-actions">
        <a href="/doctor/calendar" class="btn btn-light"><i class="bi bi-calendar3 me-1"></i> View Schedule</a>
        <button type="button" class="btn btn-outline-light" onclick="quickActionAddPatient()"><i class="bi bi-person-plus me-1"></i> Add Patient</button>
    </div>
</div>

<div class="row stats-cards-wrapper">
    <!-- Statistics Cards -->
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-primary">
                <div class="mini-stat-icon">
                    <i class="bi bi-calendar3-fill"></i>
                </div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value"><?= $stats['total'] ?? 0 ?></span>
                    <span class="mini-stat-label">Total Appointments Today</span>
                </div>
                <div class="mini-stat-chart" id="chartTotalAppointmentsToday"></div>
                <div class="mini-stat-trend trend-up">
                    <i class="bi bi-calendar-day"></i>
                    <span> Today</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-success">
                <div class="mini-stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value"><?= $stats['completed'] ?? 0 ?></span>
                    <span class="mini-stat-label">Completed Appointments</span>
                </div>
                <div class="mini-stat-chart" id="chartCompletedAppointments"></div>
                <div class="mini-stat-trend trend-up" id="completedChange">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-warning">
                <div class="mini-stat-icon">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value"><?= $stats['missed_appointments'] ?? 0 ?></span>
                    <span class="mini-stat-label">Missed Appointments</span>
                </div>
                <div class="mini-stat-chart" id="chartMissedAppointments"></div>
                <div class="mini-stat-trend trend-down" id="missedChange">
                    <i class="bi bi-graph-down-arrow"></i>
                    <span>--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-info">
                <div class="mini-stat-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value" id="newPatientsValue">0</span>
                    <span class="mini-stat-label">New Patients</span>
                </div>
                <div class="mini-stat-chart" id="chartNewPatients"></div>
                <div class="mini-stat-trend trend-up" id="newPatientsChange">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-purple">
                <div class="mini-stat-icon">
                    <i class="bi bi-prescription"></i>
                </div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value" id="totalPrescriptionsValue">0</span>
                    <span class="mini-stat-label">Prescriptions Described</span>
                </div>
                <div class="mini-stat-chart" id="chartTotalPrescriptions"></div>
                <div class="mini-stat-trend trend-up" id="totalPrescriptionsChange">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Weather & Allergy Index Card -->
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-weather">
                <div class="stats-card-content">
                    <!-- Weather widget — day/night themed gradient (see dashboard.js).
                         roaya keeps its Pollen Index + Dry Eye Risk as glass pills below. -->
                    <div class="weather-widget weather-widget--day" id="weatherWidget"
                         role="button" tabindex="0" title="View weather details &amp; forecast"
                         aria-label="View weather details and forecast">
                        <!-- Weather Section -->
                        <div class="weather-widget-top">
                            <div class="weather-widget-body">
                                <div class="weather-widget-primary">
                                    <div class="weather-desc" id="weatherDesc">Loading…</div>
                                    <div class="weather-temp" id="weatherTemp">--<span class="weather-deg">°</span></div>
                                </div>
                                <div class="weather-widget-meta">
                                    <div class="weather-date" id="weatherDate">—</div>
                                    <div class="weather-location" id="weatherLocation">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span>Detecting location...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="weather-icon-container" id="weatherIconContainer">
                                <div class="weather-icon-loading">
                                    <div class="spinner-border spinner-border-sm text-light" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Health Indices (kept + restyled as glass pills) -->
                        <div class="health-indices">
                            <!-- Pollen Index -->
                            <div class="health-index pollen-index">
                                <div class="index-icon">
                                    <i class="bi bi-flower1"></i>
                                </div>
                                <div class="index-info">
                                    <span class="index-label">Pollen Index</span>
                                    <div class="index-bar">
                                        <div class="index-fill" id="pollenIndexFill" style="width: 0%"></div>
                                    </div>
                                    <span class="index-value" id="pollenIndexValue">--</span>
                                </div>
                            </div>

                            <!-- Dry Eye Risk -->
                            <div class="health-index dry-eye-index">
                                <div class="index-icon">
                                    <i class="bi bi-eye"></i>
                                </div>
                                <div class="index-info">
                                    <span class="index-label">Dry Eye Risk</span>
                                    <div class="index-bar">
                                        <div class="index-fill" id="dryEyeIndexFill" style="width: 0%"></div>
                                    </div>
                                    <span class="index-value" id="dryEyeIndexValue">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ophthalmology News Bar -->
<div class="news-bar-wrapper mb-3">
    <div class="news-bar">
        <span class="label">
            <i class="bi bi-broadcast"></i>
            <span class="news-bar-live"></span>
            Ophthalmology News
        </span>
        <div class="ticker-wrap">
            <div class="ticker" id="newsTicker">
                <span>Loading ophthalmology news...</span>
            </div>
        </div>
    </div>
</div>


<!-- Upcoming Appointments and Recent Activity - Equal Width Layout -->
<div class="row mb-4" id="upcomingAppointmentsRow">
    <!-- Upcoming Appointments - 50% width -->
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-4">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-calendar-event me-2"></i>
                    Upcoming Appointments
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="/doctor/calendar" class="btn btn-sm btn-primary">
                        <i class="bi bi-calendar-event me-1"></i>View All
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div id="upcomingAppointmentsContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <nav aria-label="Upcoming Appointments Pagination" id="upcomingPaginationNav" style="display: none;">
                    <ul class="pagination pagination-sm justify-content-center mb-0" id="upcomingPaginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- At-a-glance widgets (2x2) — replaces the old Recent Activities slot here -->
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-4">
        <div class="dash-mini-grid h-100">
            <!-- Today's appointment status donut -->
            <div class="dash-mini-card" id="dashStatusCard">
                <div class="dash-mini-head"><i class="bi bi-pie-chart-fill"></i><span>Today's Status</span></div>
                <div class="dash-mini-body" id="dashStatusBody">
                    <div class="dash-mini-spinner"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>
                </div>
            </div>

            <!-- Patients Board snapshot (per-column counts; click → full board) -->
            <div class="dash-mini-card dash-mini-clickable" id="dashBoardCard" role="button" tabindex="0"
                 onclick="window.location.href='/doctor/board'" title="Open Patients Board">
                <div class="dash-mini-head"><i class="bi bi-columns-gap"></i><span>Board Snapshot</span></div>
                <div class="dash-mini-body" id="dashBoardBody">
                    <div class="dash-mini-spinner"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>
                </div>
            </div>

            <!-- Today's revenue -->
            <div class="dash-mini-card" id="dashRevenueCard">
                <div class="dash-mini-head"><i class="bi bi-cash-coin"></i><span>Today's Revenue</span></div>
                <div class="dash-mini-body" id="dashRevenueBody">
                    <div class="dash-mini-spinner"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="dash-mini-card" id="dashQuickCard">
                <div class="dash-mini-head"><i class="bi bi-lightning-charge-fill"></i><span>Quick Actions</span></div>
                <div class="dash-mini-body dash-quick-actions">
                    <a href="/doctor/calendar" class="dash-quick-tile dqt-indigo"><i class="bi bi-calendar-plus"></i><span>New Appointment</span></a>
                    <a href="/doctor/patients?new=1" class="dash-quick-tile dqt-teal"><i class="bi bi-person-plus"></i><span>New Patient</span></a>
                    <a href="/doctor/board" class="dash-quick-tile dqt-violet"><i class="bi bi-columns-gap"></i><span>Open Board</span></a>
                    <a href="/doctor/calendar" class="dash-quick-tile dqt-amber"><i class="bi bi-calendar3"></i><span>Calendar</span></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Boards -->
<div class="row mb-4 dashboard-card-row" data-card-id="patient-boards">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="patient-boards">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-columns-gap me-2"></i>
                    Patient Boards
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="/doctor/board" class="btn btn-sm btn-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Open full page
                    </a>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('patient-boards')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('patient-boards')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php $boardEmbedded = true; $user = $user ?? $this->getCurrentUser(); include __DIR__ . '/board.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Notes Dashboard (embeds the canonical /doctor/notes board — single source of truth) -->
<div class="row mb-4 dashboard-card-row" data-card-id="notes-dashboard">
    <div class="col-12">
        <div class="card shadow dashboard-card" id="notesDashboardCard" data-card-id="notes-dashboard">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-sticky me-2"></i>
                    Notes Board
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-success" onclick="document.getElementById('addNoteBtn')?.click()">
                        <i class="bi bi-plus-circle me-1"></i>Add Note
                    </button>
                    <a href="/doctor/notes" class="btn btn-sm btn-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Open full page
                    </a>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('notes-dashboard')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('notes-dashboard')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php $notesEmbedded = true; $user = $user ?? $this->getCurrentUser(); include __DIR__ . '/notes/index.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Unified Clinical Dashboard -->
<div class="row mb-4 dashboard-card-row" data-card-id="unified-clinical-dashboard">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="unified-clinical-dashboard">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-clipboard-pulse me-2"></i>
                    Unified Clinical Dashboard
                </h6>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('unified-clinical-dashboard')" title="Move up">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('unified-clinical-dashboard')" title="Move down">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <div class="dashboard-card-drag-handle" title="Drag to reorder">
                        <i class="bi bi-grip-vertical text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Patient Selection Message (if no patient selected) -->
                <div id="unifiedClinicalDashboardNoPatient" class="text-center py-4 text-muted" style="display: none;">
                    <i class="bi bi-person-circle fs-1 mb-3"></i>
                    <p class="mb-0">Please select a patient to view clinical dashboard</p>
                    <small>Open a patient profile to see their clinical snapshot</small>
                </div>

                <!-- Clinical Dashboard Content -->
                <div id="unifiedClinicalDashboardContent" style="display: none;">
                    <!-- Clinical Snapshot Section -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-clipboard-data me-2"></i>Clinical Snapshot</h6>
                        
                        <!-- Patient Info Notice -->
                        <div class="patient-info-notice" id="patientInfoNotice" style="display: none;">
                            <div class="patient-info-notice-content" id="patientInfoNoticeContent">
                                <div class="patient-info-notice-icon">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <div class="patient-info-notice-text">
                                    <div class="patient-info-notice-main">
                                        Patient Data: <strong id="patientInfoName" class="patient-info-name-link">--</strong>
                                    </div>
                                    <div class="patient-info-notice-sub">
                                        Last profile or appointment you viewed
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <!-- IOP Status -->
                            <div class="col-md-6 col-lg-3">
                                <div class="clinical-indicator-card" id="clinicalIndicatorIOP">
                                    <div class="clinical-indicator-header">
                                        <i class="bi bi-eyedropper me-2"></i>
                                        <span>IOP Status</span>
                                    </div>
                                    <div class="clinical-indicator-value" id="iopValue">--</div>
                                    <div class="clinical-indicator-status" id="iopStatus">
                                        <span class="badge bg-secondary">Not available</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Visual Acuity -->
                            <div class="col-md-6 col-lg-3">
                                <div class="clinical-indicator-card" id="clinicalIndicatorVA">
                                    <div class="clinical-indicator-header">
                                        <i class="bi bi-eye me-2"></i>
                                        <span>Visual Acuity</span>
                                    </div>
                                    <div class="clinical-indicator-value" id="vaValue">--</div>
                                    <div class="clinical-indicator-trend" id="vaTrend">
                                        <span class="trend-indicator">→</span>
                                        <span class="trend-text">Stable</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cataract Status -->
                            <div class="col-md-6 col-lg-3">
                                <div class="clinical-indicator-card" id="clinicalIndicatorCataract">
                                    <div class="clinical-indicator-header">
                                        <i class="bi bi-scissors me-2"></i>
                                        <span>Cataract Status</span>
                                    </div>
                                    <div class="clinical-indicator-value" id="cataractValue">--</div>
                                    <div class="clinical-indicator-status" id="cataractStatus">
                                        <span class="badge bg-secondary">Not available</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dry Eye Status -->
                            <div class="col-md-6 col-lg-3">
                                <div class="clinical-indicator-card" id="clinicalIndicatorDryEye">
                                    <div class="clinical-indicator-header">
                                        <i class="bi bi-droplet me-2"></i>
                                        <span>Dry Eye Status</span>
                                    </div>
                                    <div class="clinical-indicator-value" id="dryEyeValue">--</div>
                                    <div class="clinical-indicator-trend" id="dryEyeTrend">
                                        <span class="trend-indicator">→</span>
                                        <span class="trend-text">Stable</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Clinical Alerts Section -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Active Clinical Alerts</h6>
                        <div id="clinicalAlertsContainer">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                No active alerts
                            </div>
                        </div>
                    </div>

                    <!-- Mini Trends Overview -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-graph-up me-2"></i>Mini Trends Overview</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="mini-trend-card">
                                    <div class="mini-trend-label">IOP Trend</div>
                                    <div class="mini-trend-chart" id="iopTrendChart">
                                        <div class="mini-trend-placeholder">No data</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mini-trend-card">
                                    <div class="mini-trend-label">Visual Acuity Trend</div>
                                    <div class="mini-trend-chart" id="vaTrendChart">
                                        <div class="mini-trend-placeholder">No data</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mini-trend-card">
                                    <div class="mini-trend-label">Macular Thickness Trend</div>
                                    <div class="mini-trend-chart" id="macularTrendChart">
                                        <div class="mini-trend-placeholder">No data</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Summary Section -->
                    <div class="mb-0">
                        <h6 class="mb-3"><i class="bi bi-file-text me-2"></i>Clinical Summary</h6>
                        <div class="clinical-summary-box">
                            <p id="clinicalSummaryText" class="mb-0">Loading clinical summary...</p>
                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyClinicalSummary()" id="copySummaryBtn">
                                <i class="bi bi-clipboard me-1"></i>Copy to Clipboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visual Analytics -->
<div class="row mb-4 dashboard-card-row" data-card-id="visual-analytics">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="visual-analytics">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-graph-up me-2"></i>
                    Visual Analytics
                </h6>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('visual-analytics')" title="Move up">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('visual-analytics')" title="Move down">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <div class="dashboard-card-drag-handle" title="Drag to reorder">
                        <i class="bi bi-grip-vertical text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Appointments Trend Line Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card chart-card h-100 d-flex flex-column">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Trend</h6>
                            </div>
                            <div class="card-body chart-container flex-grow-1">
                                <canvas id="appointmentsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                                        <!-- Appointments Trend Line Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card chart-card h-100 d-flex flex-column">
                            <div class="card-header">
                                <h6 class="mb-0">New Patients Trend</h6>
                            </div>
                            <div class="card-body chart-container flex-grow-1">
                                <canvas id="newPatientsTrendChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>            
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Alerts (embeds the canonical /doctor/alerts page in compact "today" mode) -->
<div class="row mb-4 dashboard-card-row" data-card-id="today-alerts">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="today-alerts">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-bell me-2"></i>
                    Today's Alerts
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="/doctor/alerts" class="btn btn-sm btn-outline-warning manage-alerts-btn">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Manage Alerts
                    </a>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('today-alerts')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('today-alerts')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php $alertsEmbedded = true; include __DIR__ . '/alerts/index.php'; ?>
            </div>
        </div>
    </div>
</div>


<!-- Recent Activities card removed (v12_perf): moved to the dedicated, clinic-scoped
     Activities page (/doctor/activities). The Notification Center "Activity" tab (capped
     at 10) links to it. This retires the old unscoped /api/recent-activity + 1000-row modal. -->

<div class="row mb-4 dashboard-card-row" data-card-id="missed-appointments">
    <!-- Missed Appointments -->
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="missed-appointments">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Missed Appointments
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <section class="field menu" style="min-width: 150px;">
                        <div class="control">
                            <select class="form-select form-select-sm d-none" id="missedPerPageSelect">
                                <option value="5" selected>5 per page</option>
                                <option value="10">10 per page</option>
                                <option value="20">20 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                            <button type="button" class="custom-select-toggle" aria-expanded="false">5 per page</button>
                            <menu>
                                <li data-option="5" tabindex="0" role="button" class="selected"><h3>5 per page</h3></li>
                                <li data-option="10" tabindex="0" role="button"><h3>10 per page</h3></li>
                                <li data-option="20" tabindex="0" role="button"><h3>20 per page</h3></li>
                                <li data-option="50" tabindex="0" role="button"><h3>50 per page</h3></li>
                            </menu>
                        </div>
                    </section>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('missed-appointments')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('missed-appointments')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="missedAppointmentsContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <nav aria-label="Missed Appointments Pagination" id="missedPaginationNav" style="display: none;">
                    <ul class="pagination justify-content-center mb-0" id="missedPaginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>



<style>
    .btn-group-sm .btn{
        border-radius: 0.8rem !important;
        margin-right: 5px !important;
    }
    .btn-outline-primary, .dark .btn-outline-primary{
        background-color: var(--accent) !important;
        border-color: var(--accent) !important;
        color: white !important;
    }

    .btn-outline-warning, .dark .btn-outline-warning{
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: white !important;
    }

    .btn-outline-warning:hover, .dark .btn-outline-warning:hover{
        background-color:rgb(233, 175, 0) !important;
        border-color:rgb(233, 175, 0) !important;
        color: white !important;
    }

    .btn-outline-warning:hover i, .dark .btn-outline-warning:hover i{
        color: white !important;
    }

    .btn-outline-success, .dark .btn-outline-success{
        background-color:rgb(6, 204, 138) !important;
        border-color:rgb(6, 204, 138) !important;
        color: white !important;
    }

    .btn-outline-success:hover, .dark .btn-outline-success:hover{
        background-color:rgb(10, 179, 129) !important;
        border-color:rgb(10, 179, 129) !important;
        color: white !important;
    }

    .btn-outline-primary:hover, .dark .btn-outline-primary:hover{
        background-color: #00a3eb !important;
    }

    .btn-outline-info, .dark .btn-outline-info{
        background-color: #36b9cc !important;
        border-color: #36b9cc !important;
        color: white !important;
    }

    .btn-outline-info:hover, .dark .btn-outline-info:hover{
        background-color:rgb(13, 146, 167) !important;
        border-color:rgb(13, 146, 167) !important;
        color: white !important;
    }
    
    .modal-dialog {
        width: 70% !important;
        max-height: calc(100vh - 4rem) !important;
        margin: 2rem auto 0 !important;
        top: 0 !important;
        transform: translateX(-50%) !important;
        left: 50% !important;
        position: absolute !important;
    }
    
    .modal-content {
        max-height: calc(100vh - 4rem) !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    .modal-body {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        flex: 1 1 auto !important;
    }
    
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 1rem auto 0 !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            max-width: 95% !important;
        }
    }
.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>

<!-- Chart.js (the only charting lib the dashboard needs). Deferred so it doesn't
     block paint — dashboard.js already polls `typeof Chart` and its chart renderers
     run on DOMContentLoaded (after deferred scripts execute), so Chart is ready by then. -->
<script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- amCharts 4 removed (v12_perf): ~500KB of deprecated, render-blocking CDN scripts
     that only ever powered a gender pie chart whose container (#genderPieChart) lives on
     the Reports page, NOT the dashboard — so on both dashboards they loaded for nothing
     (and triggered a 2s retry-poll). Reports renders that chart with its own Chart.js. -->
<script src="/app/Views/doctor/assets/js/dashboard.js?v=<?= file_exists(__DIR__ . '/assets/js/dashboard.js') ? filemtime(__DIR__ . '/assets/js/dashboard.js') : time() ?>"></script>