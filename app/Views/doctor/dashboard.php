<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<div class="row stats-cards-wrapper">
    <!-- Statistics Cards -->
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-primary">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title">Total Appointments Today</h4>
                        <h3 class="stats-card-value"><?= $stats['total'] ?? 0 ?></h3>
                        <p class="stats-card-change stats-card-change-positive">Today</p>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-success">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title">Completed Appointments</h4>
                        <h3 class="stats-card-value"><?= $stats['completed'] ?? 0 ?></h3>
                        <p class="stats-card-change stats-card-change-positive" id="completedChange">--</p>
                    </div>
                    <div class="stats-card-chart">
                        <canvas id="statsChart2" height="70"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-danger">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title">Missed Appointments</h4>
                        <h3 class="stats-card-value"><?= $stats['missed_appointments'] ?? 0 ?></h3>
                        <p class="stats-card-change stats-card-change-negative" id="missedChange">--</p>
                    </div>
                    <div class="stats-card-chart">
                        <canvas id="statsChart3" height="70"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-warning">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title">New Patients</h4>
                        <h3 class="stats-card-value" id="newPatientsValue">0</h3>
                        <p class="stats-card-change stats-card-change-positive" id="newPatientsChange">--</p>
                    </div>
                    <div class="stats-card-chart">
                        <canvas id="statsChart4" height="70"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-info">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title">Prescriptions Described</h4>
                        <h3 class="stats-card-value" id="totalPrescriptionsValue">0</h3>
                        <p class="stats-card-change stats-card-change-positive" id="totalPrescriptionsChange">--</p>
                    </div>
                    <div class="stats-card-chart">
                        <canvas id="statsChart5" height="70"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weather & Allergy Index Card -->
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-weather">
                <div class="stats-card-content">
                    <div class="weather-card-inner">
                        <!-- Weather Section -->
                        <div class="weather-main">
                            <div class="weather-icon-container" id="weatherIconContainer">
                                <div class="weather-icon-loading">
                                    <div class="spinner-border spinner-border-sm text-light" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="weather-info">
                                <div class="weather-temp" id="weatherTemp">--°C</div>
                                <div class="weather-desc" id="weatherDesc">Loading...</div>
                                <div class="weather-location" id="weatherLocation">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>Detecting location...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Health Indices -->
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
        <span class="label">Ophthalmology News</span>
        <div class="ticker-wrap">
            <div class="ticker" id="newsTicker">
                <span>Loading ophthalmology news...</span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions - New iOS-style Cards -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow dashboard-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="quick-actions-wrapper" id="quickActionsWrapper">
                    <!-- Navigation Arrows -->
                    <button class="quick-actions-nav nav-left hidden" id="qaNavLeft" aria-label="Scroll left">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="quick-actions-nav nav-right" id="qaNavRight" aria-label="Scroll right">
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <div class="quick-actions-grid" id="quickActionsGrid">
                    <!-- Patients Card -->
                    <div class="quick-action-card patients-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-people-fill"></i>
                            <span class="qa-logo-name">Patients</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/patients'">
                            <span class="qa-icon">
                                <i class="bi bi-person-lines-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                        <div class="qa-box qa-box2" onclick="quickActionAddPatient()">
                            <span class="qa-icon">
                                <i class="bi bi-person-plus-fill"></i>
                            </span>
                            <span class="qa-label">Add</span>
                        </div>
                    </div>

                    <!-- Calendar Card -->
                    <div class="quick-action-card calendar-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-calendar3"></i>
                            <span class="qa-logo-name">Calendar</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/calendar'">
                            <span class="qa-icon">
                                <i class="bi bi-calendar3"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                        <div class="qa-box qa-box2" onclick="quickActionAddAppointment()">
                            <span class="qa-icon">
                                <i class="bi bi-calendar-plus-fill"></i>
                            </span>
                            <span class="qa-label">Add</span>
                        </div>
                    </div>

                    <!-- Discussion Card -->
                    <div class="quick-action-card discussion-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-chat-dots-fill"></i>
                            <span class="qa-logo-name">Discussion</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/discussions'">
                            <span class="qa-icon">
                                <i class="bi bi-chat-left-text-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                        <div class="qa-box qa-box2" onclick="quickActionAddDiscussion()">
                            <span class="qa-icon">
                                <i class="bi bi-chat-quote-fill"></i>
                            </span>
                            <span class="qa-label">Add</span>
                        </div>
                    </div>

                    <!-- Financial Card -->
                    <div class="quick-action-card financial-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-wallet2"></i>
                            <span class="qa-logo-name">Financial</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="quickActionAddBalance()">
                            <span class="qa-icon">
                                <i class="bi bi-plus-circle-fill"></i>
                            </span>
                            <span class="qa-label">Balance</span>
                        </div>
                        <div class="qa-box qa-box2" style="width:55% !important" onclick="quickActionAddExpense()">
                            <span class="qa-icon">
                                <i class="bi bi-dash-circle-fill"></i>
                            </span>
                            <span class="qa-label">Expense</span>
                        </div>
                    </div>

                    <!-- Notes Card -->
                    <div class="quick-action-card notes-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-journal-text"></i>
                            <span class="qa-logo-name">Notes</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/notes'">
                            <span class="qa-icon">
                                <i class="bi bi-card-checklist"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                        <div class="qa-box qa-box2" onclick="quickActionAddNote()">
                            <span class="qa-icon">
                                <i class="bi bi-journal-plus"></i>
                            </span>
                            <span class="qa-label">Add</span>
                        </div>
                    </div>

                    <!-- Alerts Card -->
                    <div class="quick-action-card alerts-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-bell-fill"></i>
                            <span class="qa-logo-name">Alerts</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/alerts'">
                            <span class="qa-icon">
                                <i class="bi bi-alarm-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                        <div class="qa-box qa-box2" onclick="quickActionAddAlert()">
                            <span class="qa-icon">
                                <i class="bi bi-plus-circle-fill"></i>
                            </span>
                            <span class="qa-label">Add</span>
                        </div>
                    </div>

                    <!-- Profile Card (View only) -->
                    <div class="quick-action-card profile-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-person-circle"></i>
                            <span class="qa-logo-name">Profile</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/profile'">
                            <span class="qa-icon">
                                <i class="bi bi bi-person-vcard"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>

                    <!-- Drugs Card (View only) -->
                    <div class="quick-action-card drugs-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-capsule"></i>
                            <span class="qa-logo-name">Drugs</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/drugs'">
                            <span class="qa-icon">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>

                    <!-- Settings Card (View only) -->
                    <div class="quick-action-card settings-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-gear-fill"></i>
                            <span class="qa-logo-name">Settings</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/settings'">
                            <span class="qa-icon">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>

                    <!-- Organizer Card (View only) -->
                    <div class="quick-action-card organizer-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-kanban-fill"></i>
                            <span class="qa-logo-name">Organizer</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/organizer'">
                            <span class="qa-icon">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>

                    <!-- Media Card (View only) -->
                    <div class="quick-action-card media-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-images"></i>
                            <span class="qa-logo-name">Media</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/media'">
                            <span class="qa-icon">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>

                    <!-- Medical Prescriptions Card (View only) -->
                    <div class="quick-action-card medical-rx-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-file-earmark-medical-fill"></i>
                            <span class="qa-logo-name">Medical Rx</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/medications'">
                            <span class="qa-icon">
                                <i class="bi bi-prescription"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>

                    <!-- Glasses Prescriptions Card (View only) -->
                    <div class="quick-action-card glasses-rx-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-eyeglasses"></i>
                            <span class="qa-logo-name">Glasses Rx</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/glasses'">
                            <span class="qa-icon">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>

                    <!-- Reports Card (View only) -->
                    <div class="quick-action-card reports-card single-action-card">
                        <div class="qa-background"></div>
                        <div class="qa-logo">
                            <i class="bi bi-bar-chart-fill"></i>
                            <span class="qa-logo-name">Reports</span>
                        </div>
                        <div class="qa-box qa-box1" onclick="window.location.href='/doctor/reports'">
                            <span class="qa-icon">
                                <i class="bi bi-clipboard2-data"></i>
                            </span>
                            <span class="qa-label">View</span>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Appointments and Recent Activity - Equal Width Layout -->
<div class="row mb-4">
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

    <!-- Recent Activity - 50% width -->
    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-4">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-activity me-2"></i>
                    Recent Activities
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allActivitiesModal">
                        <i class="bi bi-list-ul me-1"></i>View All
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="recentActivityContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes Dashboard -->
<div class="row mb-4 dashboard-card-row" data-card-id="notes-dashboard">
    <div class="col-12">
        <div class="card shadow dashboard-card" id="notesDashboardCard" data-card-id="notes-dashboard">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-sticky me-2"></i>
                    Notes Board
                </h6>
                <div class="d-flex align-items-center gap-2">

                    <button class="btn btn-sm btn-success" id="dashboardAddNoteBtnHeader" onclick="dashboardAddNote()">
                        <i class="bi bi-plus-circle me-1"></i>Add Note
                    </button>
                    <a href="/doctor/notes" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>Notes Board
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
            <div class="card-body" id="notesDashboardCardBody" style="position: relative; overflow: hidden;">
                <div id="dashboardNotesContainer" class="dashboard-notes-container">
                    <div class="text-center py-3" id="dashboardNotesLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="text-center py-4" id="dashboardNotesEmpty" style="display: none;">
                        <i class="bi bi-sticky text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No notes yet. Click "Add Note" to create your first note.</p>
                    </div>
                </div>
                <div class="dashboard-notes-resize-handle" id="notesDashboardResizeHandle" title="Drag to resize"></div>
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

<!-- Today's Alerts -->
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
                        <i class="bi bi-gear me-1"></i>Manage Alerts
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
                <div id="todayAlertsContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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
                                <option value="5">5 per page</option>
                                <option value="10" selected>10 per page</option>
                                <option value="20">20 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                            <button type="button" class="custom-select-toggle" aria-expanded="false">10 per page</button>
                            <menu>
                                <li data-option="5" tabindex="0" role="button"><h3>5 per page</h3></li>
                                <li data-option="10" tabindex="0" role="button" class="selected"><h3>10 per page</h3></li>
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


<!-- All Activities Modal - Extra Large -->
<div class="modal fade" id="allActivitiesModal" tabindex="-1" aria-labelledby="allActivitiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allActivitiesModalLabel">
                    <i class="bi bi-activity me-2"></i>
                    All Activities
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-funnel"></i>
                            </span>
                            <input type="text" class="form-control" id="activitiesFilterInput" placeholder="Filter activities...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <section class="field menu" style="min-width: 150px;">
                            <div class="control">
                                <select class="form-select d-none" id="modalPerPageSelect">
                                    <option value="10" selected>10 per page</option>
                                    <option value="20">20 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                                <button type="button" class="custom-select-toggle" aria-expanded="false">10 per page</button>
                                <menu>
                                    <li data-option="10" tabindex="0" role="button" class="selected"><h3>10 per page</h3></li>
                                    <li data-option="20" tabindex="0" role="button"><h3>20 per page</h3></li>
                                    <li data-option="50" tabindex="0" role="button"><h3>50 per page</h3></li>
                                    <li data-option="100" tabindex="0" role="button"><h3>100 per page</h3></li>
                                </menu>
                            </div>
                        </section>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-outline-secondary" id="clearActivitiesFilter">
                            <i class="bi bi-x-circle me-1"></i>Clear Filter
                        </button>
                    </div>
                </div>
                <div id="allActivitiesContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <nav aria-label="All Activities Pagination" id="modalPaginationNav" style="display: none; margin-top: 1.5rem;">
                    <ul class="pagination justify-content-center mb-0" id="modalPaginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- amCharts 4 -->
<script src="https://www.amcharts.com/lib/4/core.js"></script>
<script src="https://www.amcharts.com/lib/4/charts.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>
<script src="/app/Views/doctor/assets/js/dashboard.js?v=<?= file_exists(__DIR__ . '/assets/js/dashboard.js') ? filemtime(__DIR__ . '/assets/js/dashboard.js') : time() ?>"></script>