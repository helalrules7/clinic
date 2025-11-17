<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Today
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['total'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar3 fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['completed'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Missed Appointments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['missed_appointments'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            In Progress
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['in_progress'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Booked
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['booked'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col mb-2">
                        <a href="/doctor/calendar" class="btn btn-outline-primary quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-calendar3 mb-1"></i>
                            <span style="font-size: 0.75rem;">View Calendar</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/patients" class="btn btn-outline-success quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-people mb-1"></i>
                            <span style="font-size: 0.75rem;">Patient List</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/profile" class="btn btn-outline-info quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-person-circle mb-1"></i>
                            <span style="font-size: 0.75rem;">My Profile</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/reports" class="btn btn-outline-warning quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-graph-up mb-1"></i>
                            <span style="font-size: 0.75rem;">Reports</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/alerts" class="btn btn-outline-danger quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-bell mb-1"></i>
                            <span style="font-size: 0.75rem;">Alerts</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/drugs" class="btn btn-outline-success quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-capsule mb-1"></i>
                            <span style="font-size: 0.75rem;">Drugs</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/notes" class="btn btn-outline-warning quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-sticky mb-1"></i>
                            <span style="font-size: 0.75rem;">Notes</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/medications" class="btn btn-outline-info quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-prescription mb-1"></i>
                            <span style="font-size: 0.75rem;">Prescriptions</span>
                        </a>
                    </div>
                    <div class="col mb-2">
                        <a href="/doctor/glasses" class="btn btn-outline-primary quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-eyeglasses mb-1"></i>
                            <span style="font-size: 0.75rem;">Glasses Prescriptions</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-sticky me-2"></i>
                    Notes Dashboard
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-success" id="dashboardAddNoteBtnHeader" onclick="dashboardAddNote()">
                        <i class="bi bi-plus-circle me-1"></i>Add Note
                    </button>
                    <a href="/doctor/notes" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>Open Notes Dashboard
                    </a>
                </div>
            </div>
            <div class="card-body">
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
            </div>
        </div>
    </div>
</div>

<!-- Today's Alerts -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-bell me-2"></i>
                    Today's Alerts
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="/doctor/alerts" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-gear me-1"></i>Manage Alerts
                    </a>
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

<div class="row">
    <!-- Upcoming Appointments -->
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-calendar-event me-2"></i>
                    Upcoming Appointments
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" id="upcomingPerPageSelect" style="width: auto;">
                        <option value="5">5 per page</option>
                        <option value="10" selected>10 per page</option>
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                    <a href="/doctor/calendar" class="btn btn-sm btn-primary">
                        View All
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
                    <ul class="pagination justify-content-center mb-0" id="upcomingPaginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Missed Appointments -->
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Missed Appointments
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" id="missedPerPageSelect" style="width: auto;">
                        <option value="5">5 per page</option>
                        <option value="10" selected>10 per page</option>
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                    </select>
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

<!-- Visual Analytics -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-graph-up me-2"></i>
                    Visual Analytics
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Appointments Trend Line Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Trend</h6>
                            </div>
                            <div class="card-body chart-container">
                                <canvas id="appointmentsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointments Status Pie Chart -->
                    <div class="col-lg-3 mb-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Status</h6>
                            </div>
                            <div class="card-body chart-container">
                                <canvas id="appointmentsPieChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointments Statistics Table -->
                    <div class="col-lg-3 mb-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h6 class="mb-0">Statistics</h6>
                            </div>
                            <div class="card-body" style="background-color: var(--card);">
                                <table class="table table-sm table-borderless mb-0" id="appointmentsStatsTable" style="color: var(--text);">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted" style="color: var(--muted) !important;">All Appointments:</td>
                                            <td class="text-end fw-bold" style="color: var(--text) !important;" id="statsTotal">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="color: var(--muted) !important;">Completed:</td>
                                            <td class="text-end fw-bold text-success" style="color: var(--success) !important;" id="statsCompleted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="color: var(--muted) !important;">Missed:</td>
                                            <td class="text-end fw-bold text-danger" style="color: var(--danger) !important;" id="statsMissed">-</td>
                                        </tr>
                                        <tr class="border-top" style="border-top-color: var(--border) !important;">
                                            <td class="text-muted" style="color: var(--muted) !important;">Completion Ratio:</td>
                                            <td class="text-end fw-bold text-primary" style="color: var(--accent) !important;" id="statsRatio">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Timeline Events -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-activity me-2"></i>
                    Recent Activity
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                        <option value="5">5 per page</option>
                        <option value="10" selected>10 per page</option>
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                    </select>
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
                <nav aria-label="Recent Activity Pagination" id="paginationNav" style="display: none;">
                    <ul class="pagination justify-content-center mb-0" id="paginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Variables for Dark Mode */
:root {
    --bg: #f8fafc;
    --text: #0f172a;
    --card: #ffffff;
    --muted: #475569;
    --accent: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
    --border: #e2e8f0;
    --shadow: rgba(0, 0, 0, 0.1);
}

.dark {
    --bg: #0b1220;
    --text: #f8fafc;
    --card: #1e293b;
    --muted: #94a3b8;
    --accent: #38bdf8;
    --success: #4ade80;
    --danger: #fb7185;
    --border: #334155;
    --shadow: rgba(0, 0, 0, 0.3);
}

/* Card Styles */
.card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    box-shadow: 0 0.15rem 1.75rem 0 var(--shadow) !important;
}

.card-header {
    background-color: var(--card) !important;
    border-bottom-color: var(--border) !important;
}

.card-body {
    background-color: var(--card) !important;
}

/* Text Colors */
.text-muted {
    color: var(--muted) !important;
}

.dark .text-muted {
    color: #94a3b8 !important;
}

/* List Group Items */
.list-group-item {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .list-group-item {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

/* Timeline Styles for Dark Mode */
.dark .timeline-item h6 {
    color: var(--text) !important;
}

.dark .timeline-item .text-muted {
    color: #94a3b8 !important;
}

.dark .timeline-marker .bg-primary {
    background-color: var(--accent) !important;
}

/* Button Styles */
.btn-outline-primary {
    color: var(--accent) !important;
    border-color: var(--accent) !important;
}

.btn-outline-success {
    color: var(--success) !important;
    border-color: var(--success) !important;
}

.btn-outline-info {
    color: #36b9cc !important;
    border-color: #36b9cc !important;
}

.btn-outline-warning {
    color: #f6c23e !important;
    border-color: #f6c23e !important;
}

.dark .btn-outline-primary {
    color: var(--accent) !important;
    border-color: var(--accent) !important;
}

.dark .btn-outline-success {
    color: var(--success) !important;
    border-color: var(--success) !important;
}

.border-left-primary {
    border-left: 0.25rem solid var(--accent) !important;
}

.border-left-success {
    border-left: 0.25rem solid var(--success) !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-danger {
    border-left: 0.25rem solid var(--danger) !important;
}

.text-gray-300 {
    color: var(--muted) !important;
}

.dark .text-gray-300 {
    color: #64748b !important;
}

.text-gray-800 {
    color: var(--text) !important;
}

.dark .text-gray-800 {
    color: var(--text) !important;
}

/* Statistics Cards - Responsive adjustments */
@media (min-width: 1200px) {
    .col-xl .card-body {
        padding: 0.75rem !important;
    }
    
    .col-xl .text-xs {
        font-size: 0.7rem !important;
    }
    
    .col-xl .h5 {
        font-size: 1.25rem !important;
    }
    
    .col-xl .fa-2x {
        font-size: 1.5rem !important;
    }
}

/* Statistics Cards Dark Mode */
.dark .h5 {
    color: var(--text) !important;
}

.dark .font-weight-bold {
    color: var(--text) !important;
}

.text-primary {
    color: var(--accent) !important;
}

.text-success {
    color: var(--success) !important;
}

.text-warning {
    color: #f6c23e !important;
}

.text-info {
    color: #36b9cc !important;
}

.timeline-marker {
    flex-shrink: 0;
}

.timeline-item:not(:last-child) .timeline-marker::after {
    content: '';
    position: absolute;
    left: 6px;
    top: 12px;
    width: 2px;
    height: 20px;
    background: var(--border);
}

.dark .timeline-item:not(:last-child) .timeline-marker::after {
    background: var(--border);
}

.timeline-marker {
    position: relative;
}

.btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
}

.btn-outline-success:hover {
    background-color: var(--success);
    border-color: var(--success);
    color: white !important;
}

.btn-outline-success:hover i {
    color: white !important;
}

.btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
}

.btn-outline-warning:hover {
    background-color: #f6c23e;
    border-color: #f6c23e;
}

.btn-outline-danger {
    color: var(--danger) !important;
    border-color: var(--danger) !important;
}

.btn-outline-danger:hover {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white !important;
}

.btn-outline-danger:hover i {
    color: white !important;
}

/* Dark Mode Button Hover States */
.dark .btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: #0b1220;
}

.dark .btn-outline-success:hover {
    background-color: var(--success);
    border-color: var(--success);
    color: white !important;
}

.dark .btn-outline-success:hover i {
    color: white !important;
}

.dark .btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
    color: #0b1220;
}

.dark .btn-outline-warning:hover {
    background-color: #f6c23e;
    border-color: #f6c23e;
    color: #0b1220;
}

.dark .btn-outline-danger {
    color: var(--danger) !important;
    border-color: var(--danger) !important;
}

.dark .btn-outline-danger:hover {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white !important;
}

.dark .btn-outline-danger:hover i {
    color: white !important;
}

/* Badge Styles for Dark Mode */
.dark .badge {
    color: var(--text) !important;
}

/* Additional Text Improvements */
.dark h6 {
    color: var(--text) !important;
}

.dark p {
    color: var(--text) !important;
}

.dark small {
    color: var(--muted) !important;
}

/* Status Badge Classes */
.badge-primary {
    background-color: var(--accent) !important;
    color: white !important;
}

.badge-success {
    background-color: var(--success) !important;
    color: white !important;
}

.badge-warning {
    background-color: #f6c23e !important;
    color: #0b1220 !important;
}

.badge-info {
    background-color: #36b9cc !important;
    color: white !important;
}

/* Quick Actions Professional Styling */
.quick-action-btn {
    border-radius: 8px !important;
    border-width: 2px !important;
    transition: all 0.3s ease !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    position: relative;
    overflow: hidden;
    min-height: 60px;
    white-space: nowrap;
    width: 100%;
}

.quick-action-btn:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.5s;
}

.quick-action-btn:hover:before {
    left: 100%;
}

.quick-action-btn i {
    transition: transform 0.3s ease;
}

.quick-action-btn:hover i {
    transform: translateY(-2px) scale(1.1);
}

.quick-action-btn span {
    font-size: 0.9rem;
    font-weight: 600;
}

/* Enhanced Hover Effects */
.quick-action-btn.btn-outline-primary:hover {
    background: linear-gradient(135deg, var(--accent), #0284c7) !important;
    border-color: var(--accent) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
}

.quick-action-btn.btn-outline-success:hover {
    background: linear-gradient(135deg, var(--success), #059669) !important;
    border-color: var(--success) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.quick-action-btn.btn-outline-info:hover {
    background: linear-gradient(135deg, #36b9cc, #0891b2) !important;
    border-color: #36b9cc !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(54, 185, 204, 0.3);
}

.quick-action-btn.btn-outline-warning:hover {
    background: linear-gradient(135deg, #f6c23e, #d97706) !important;
    border-color: #f6c23e !important;
    color: #0b1220 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(246, 194, 62, 0.3);
}

.quick-action-btn.btn-outline-danger:hover {
    background: linear-gradient(135deg, var(--danger), #dc2626) !important;
    border-color: var(--danger) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

/* Dark Mode Quick Actions */
.dark .quick-action-btn.btn-outline-primary {
    color: var(--accent) !important;
    border-color: var(--accent) !important;
}

.dark .quick-action-btn.btn-outline-success {
    color: var(--success) !important;
    border-color: var(--success) !important;
}

.dark .quick-action-btn.btn-outline-info {
    color: #36b9cc !important;
    border-color: #36b9cc !important;
}

.dark .quick-action-btn.btn-outline-warning {
    color: #f6c23e !important;
    border-color: #f6c23e !important;
}

.dark .quick-action-btn.btn-outline-primary:hover {
    background: linear-gradient(135deg, var(--accent), #0284c7) !important;
    color: #0b1220 !important;
}

.dark .quick-action-btn.btn-outline-success:hover {
    background: linear-gradient(135deg, var(--success), #059669) !important;
    color: #0b1220 !important;
}

.dark .quick-action-btn.btn-outline-info:hover {
    background: linear-gradient(135deg, #36b9cc, #0891b2) !important;
    color: #0b1220 !important;
}

.dark .quick-action-btn.btn-outline-warning:hover {
    background: linear-gradient(135deg, #f6c23e, #d97706) !important;
    color: #0b1220 !important;
}

.dark .quick-action-btn.btn-outline-danger {
    color: var(--danger) !important;
    border-color: var(--danger) !important;
}

.dark .quick-action-btn.btn-outline-danger:hover {
    background: linear-gradient(135deg, var(--danger), #dc2626) !important;
    color: white !important;
}

.dark .quick-action-btn.btn-outline-danger:hover i {
    color: white !important;
}

/* Patient Name Link Styles */
.patient-name-link {
    color: var(--accent) !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    cursor: pointer;
}

.patient-name-link:hover {
    color: #0284c7 !important;
    text-decoration: underline !important;
    transform: translateX(2px);
}

.dark .patient-name-link {
    color: var(--accent) !important;
}

.dark .patient-name-link:hover {
    color: #7dd3fc !important;
}

/* Button Group Styles for Appointments */
.btn-group-sm .btn {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn-group-sm .btn-outline-primary {
    border-right: none;
}

.btn-group-sm .btn-outline-primary:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.btn-group-sm .btn-outline-info:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.btn-group-sm .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.dark .btn-group-sm .btn:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
}

.btn-group-sm .btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white !important;
    z-index: 1;
}

.btn-group-sm .btn-outline-primary:hover i {
    color: white !important;
}

.btn-group-sm .btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
    color: white !important;
    z-index: 1;
}

.btn-group-sm .btn-outline-info:hover i {
    color: white !important;
}

.dark .btn-group-sm .btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white !important;
}

.dark .btn-group-sm .btn-outline-primary:hover i {
    color: white !important;
}

.dark .btn-group-sm .btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
    color: white !important;
}

.dark .btn-group-sm .btn-outline-info:hover i {
    color: white !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .quick-action-btn {
        min-height: 100px;
        margin-bottom: 1rem;
    }
    
    .quick-action-btn i {
        font-size: 1.5rem !important;
    }
    
    .quick-action-btn span {
        font-size: 0.8rem;
    }
    
    .btn-group-sm {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group-sm .btn {
        width: 100%;
        border-radius: 0.25rem !important;
        margin-bottom: 0.25rem;
    }
    
    .btn-group-sm .btn-outline-primary {
        border-right: 1px solid var(--accent) !important;
    }
}

/* Pagination Styles */
.pagination {
    margin-top: 1rem;
}

.page-link {
    color: var(--accent);
    background-color: var(--card);
    border-color: var(--border);
    transition: all 0.3s ease;
}

.page-link:hover {
    color: white;
    background-color: var(--accent);
    border-color: var(--accent);
    transform: translateY(-1px);
}

.page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.page-item.disabled .page-link {
    color: var(--muted);
    background-color: var(--card);
    border-color: var(--border);
    cursor: not-allowed;
    opacity: 0.6;
}

.dark .page-link {
    color: var(--accent);
    background-color: var(--card);
    border-color: var(--border);
}

.dark .page-link:hover {
    color: white;
    background-color: var(--accent);
    border-color: var(--accent);
}

.dark .page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.dark .page-item.disabled .page-link {
    color: var(--muted);
    background-color: var(--card);
    border-color: var(--border);
}

/* Form Select Styles */
.form-select {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
}

.dark .form-select {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
}

/* Chart Container Styles */
.chart-container {
    background-color: #1e293b !important; /* Dark background for charts */
    border-radius: 8px;
    padding: 1rem;
}

.dark .chart-container {
    background-color: #1e293b !important;
}

.chart-card {
    background-color: var(--card) !important;
}

.chart-card .card-header {
    background-color: var(--card) !important;
    border-bottom-color: var(--border) !important;
}

.chart-card .card-body {
    background-color: #1e293b !important;
    border-radius: 8px;
}

.dark .chart-card .card-body {
    background-color: #1e293b !important;
}

/* Dark Mode Table Styles */
.dark .table {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark .table tbody {
    background-color: var(--card) !important;
}

.dark .table tbody tr {
    background-color: var(--card) !important;
}

.dark .table td {
    background-color: var(--card) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

.dark .table .text-muted {
    color: var(--muted) !important;
}

.dark .table .text-success {
    color: var(--success) !important;
}

.dark .table .text-danger {
    color: var(--danger) !important;
}

.dark .table .text-primary {
    color: var(--accent) !important;
}

/* Light Mode Table Styles */
.table {
    background-color: var(--card) !important;
}

.table tbody {
    background-color: var(--card) !important;
}

.table tbody tr {
    background-color: var(--card) !important;
}

.table td {
    background-color: var(--card) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

/* Ensure chart text is always white */
canvas {
    filter: brightness(1);
}

.dark canvas {
    filter: brightness(1);
}

/* Notes Dashboard Styles */
.dashboard-notes-container {
    position: relative;
    width: 100%;
    min-height: 300px;
    padding: 1rem;
    background: 
        linear-gradient(135deg, rgba(14, 165, 233, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%),
        repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(0, 0, 0, 0.02) 10px,
            rgba(0, 0, 0, 0.02) 20px
        ),
        var(--bg);
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}

.dark .dashboard-notes-container {
    background: 
        linear-gradient(135deg, rgba(56, 189, 248, 0.08) 0%, rgba(74, 222, 128, 0.08) 100%),
        repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(255, 255, 255, 0.02) 10px,
            rgba(255, 255, 255, 0.02) 20px
        ),
        var(--bg);
    border: 1px solid var(--border);
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.2);
}

.dashboard-note-widget {
    position: absolute;
    min-width: 250px;
    min-height: 200px;
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    cursor: move;
    transition: box-shadow 0.3s ease, transform 0.2s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    font-size: 0.85rem;
}

.dashboard-note-widget.color-white {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(255, 255, 255, 0.3);
}

.dashboard-note-widget.color-red {
    background: rgba(239, 68, 68, 0.85);
    border-color: rgba(239, 68, 68, 0.4);
}

.dashboard-note-widget.color-black {
    background: rgba(30, 41, 59, 0.85);
    border-color: rgba(30, 41, 59, 0.4);
}

.dashboard-note-widget.color-dodgerblue {
    background: rgba(30, 144, 255, 0.85);
    border-color: rgba(30, 144, 255, 0.4);
}

.dashboard-note-widget.color-warning {
    background: rgba(251, 191, 36, 0.85);
    border-color: rgba(251, 191, 36, 0.4);
}

.dashboard-note-widget.color-success {
    background: rgba(16, 185, 129, 0.85);
    border-color: rgba(16, 185, 129, 0.4);
}

.dashboard-note-widget:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.dashboard-note-widget.dragging {
    opacity: 0.8;
    z-index: 10000 !important;
    transform: rotate(1deg);
}

.dashboard-note-widget-header {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    cursor: move;
}

.dark .dashboard-note-widget-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(30, 41, 59, 0.5);
}

.dashboard-note-widget-title {
    font-weight: 600;
    font-size: 0.85rem;
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.dashboard-note-widget-title:focus {
    background: rgba(255, 255, 255, 0.5);
}

.dashboard-note-widget-actions {
    display: flex;
    gap: 0.3rem;
}

.dashboard-note-widget-btn {
    width: 22px;
    height: 22px;
    border: none;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(5px);
    color: var(--text);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.7rem;
    padding: 0;
}

.dashboard-note-widget-btn:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: scale(1.1);
}

.dashboard-note-widget-btn.delete {
    color: var(--danger);
}

.dashboard-note-widget-btn.delete:hover {
    background: rgba(239, 68, 68, 0.2);
}

.dark .dashboard-note-widget-btn {
    background: rgba(30, 41, 59, 0.6);
    color: var(--text);
}

.dark .dashboard-note-widget-btn:hover {
    background: rgba(30, 41, 59, 0.9);
}

.dashboard-note-widget-body {
    flex: 1;
    padding: 0.75rem;
    overflow-y: auto;
    font-size: 0.8rem;
    line-height: 1.4;
    background-image: 
        repeating-linear-gradient(
            transparent,
            transparent 31px,
            rgba(0, 0, 0, 0.1) 31px,
            rgba(0, 0, 0, 0.1) 32px
        );
    background-size: 100% 32px;
}

.dark .dashboard-note-widget-body {
    background-image: 
        repeating-linear-gradient(
            transparent,
            transparent 31px,
            rgba(255, 255, 255, 0.1) 31px,
            rgba(255, 255, 255, 0.1) 32px
        );
}

.dashboard-note-widget-content {
    width: 100%;
    height: 100%;
    border: none;
    background: transparent;
    outline: none;
    resize: none;
    font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 0.8rem;
    line-height: 1.4;
    padding: 0;
    margin: 0;
    overflow-y: auto;
    word-wrap: break-word;
}

.dashboard-note-widget-content[contenteditable="true"]:empty:before {
    content: attr(data-placeholder);
    opacity: 0.5;
    pointer-events: none;
}

.dashboard-note-widget-footer {
    padding: 0.4rem 0.75rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    font-size: 0.7rem;
    opacity: 0.7;
}

.dark .dashboard-note-widget-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(30, 41, 59, 0.5);
}

.dashboard-note-widget.light-text .dashboard-note-widget-footer {
    border-top-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.dashboard-note-widget.dark-text .dashboard-note-widget-footer {
    border-top-color: rgba(0, 0, 0, 0.1);
    color: #0f172a;
}

/* Ensure autocomplete elements don't interfere with contenteditable */
.dashboard-note-widget-content a[data-type] {
    pointer-events: auto !important;
    user-select: none;
    cursor: pointer !important;
    text-decoration: none;
    display: inline-flex;
}

.dashboard-note-widget-content a[data-type]:hover {
    opacity: 0.9;
}

.dashboard-note-widget-content span[data-type] {
    pointer-events: auto;
    user-select: none;
    cursor: default;
    display: inline-flex;
    position: relative;
}

/* Prevent contenteditable from editing inside badges and links */
.dashboard-note-widget-content a[data-type],
.dashboard-note-widget-content span[data-type] {
    -webkit-user-modify: read-only;
    -moz-user-modify: read-only;
    user-modify: read-only;
}

/* Ensure links are fully clickable */
.dashboard-note-widget-content a[data-type="patient"],
.dashboard-note-widget-content a[data-type="appointment"] {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Prevent contenteditable from capturing clicks on link content */
.dashboard-note-widget-content a[data-type] * {
    pointer-events: none;
}

/* Patient link in note content - Badge Primary style */
.dashboard-note-content-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.dashboard-note-content-link:hover {
    background: var(--accent);
    opacity: 0.9;
    transform: translateY(-1px);
    color: white;
}

.dashboard-note-content-link .patient-icon {
    font-size: 0.9rem;
}

.dark .dashboard-note-content-link {
    background: var(--accent);
    color: white;
}

.dark .dashboard-note-content-link:hover {
    background: var(--accent);
    color: white;
    opacity: 0.9;
}

/* Appointment link in note content - Badge Secondary style */
.dashboard-note-content-appointment-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.dashboard-note-content-appointment-link:hover {
    background: #5a6268;
    transform: translateY(-1px);
    color: white;
}

.dashboard-note-content-appointment-link .appointment-icon {
    font-size: 0.9rem;
}

.dark .dashboard-note-content-appointment-link {
    background: #6c757d;
    color: white;
}

.dark .dashboard-note-content-appointment-link:hover {
    background: #5a6268;
    color: white;
}

/* Drug badge in note content */
.dashboard-note-content-drug-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.dark .dashboard-note-content-drug-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #4ade80;
    border-color: rgba(16, 185, 129, 0.4);
}

.dashboard-note-content-drug-badge .drug-icon {
    font-size: 0.9rem;
}

/* Support for notes created in index.php (without dashboard- prefix) */
.dashboard-note-widget-content .note-content-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.dashboard-note-widget-content .note-content-link:hover {
    background: var(--accent);
    opacity: 0.9;
    transform: translateY(-1px);
    color: white;
}

.dashboard-note-widget-content .note-content-link .patient-icon {
    font-size: 0.9rem;
}

.dark .dashboard-note-widget-content .note-content-link {
    background: var(--accent);
    color: white;
}

.dark .dashboard-note-widget-content .note-content-link:hover {
    background: var(--accent);
    color: white;
    opacity: 0.9;
}

.dashboard-note-widget-content .note-content-appointment-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.dashboard-note-widget-content .note-content-appointment-link:hover {
    background: #5a6268;
    transform: translateY(-1px);
    color: white;
}

.dashboard-note-widget-content .note-content-appointment-link .appointment-icon {
    font-size: 0.9rem;
}

.dark .dashboard-note-widget-content .note-content-appointment-link {
    background: #6c757d;
    color: white;
}

.dark .dashboard-note-widget-content .note-content-appointment-link:hover {
    background: #5a6268;
    color: white;
}

.dashboard-note-widget-content .note-content-drug-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0 0.2rem;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.dark .dashboard-note-widget-content .note-content-drug-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #4ade80;
    border-color: rgba(16, 185, 129, 0.4);
}

.dashboard-note-widget-content .note-content-drug-badge .drug-icon {
    font-size: 0.9rem;
}

/* Autocomplete Portal Styles */
.dashboard-note-autocomplete-portal {
    position: fixed !important;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    z-index: 9999999 !important;
    min-width: 250px;
    max-width: 400px;
}

.dark .dashboard-note-autocomplete-portal {
    background: var(--card);
    border-color: var(--border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.dashboard-note-autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dashboard-note-autocomplete-item:last-child {
    border-bottom: none;
}

.dashboard-note-autocomplete-item:hover,
.dashboard-note-autocomplete-item.selected {
    background: var(--accent);
    color: white;
}

.dashboard-note-autocomplete-item .item-icon {
    font-size: 1.2rem;
    opacity: 0.8;
}

.dashboard-note-autocomplete-item .item-content {
    flex: 1;
}

.dashboard-note-autocomplete-item .item-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.dashboard-note-autocomplete-item .item-subtitle {
    font-size: 0.75rem;
    opacity: 0.8;
}

.dashboard-note-widget.light-text {
    color: #ffffff;
}

.dashboard-note-widget.light-text .dashboard-note-widget-title,
.dashboard-note-widget.light-text .dashboard-note-widget-content {
    color: #ffffff;
}

.dashboard-note-widget.dark-text {
    color: #0f172a;
}

.dashboard-note-widget.dark-text .dashboard-note-widget-title,
.dashboard-note-widget.dark-text .dashboard-note-widget-content {
    color: #0f172a;
}

.dashboard-note-color-picker-wrapper {
    position: relative;
}

.dashboard-note-color-picker-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.3rem;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 0.4rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    min-width: 150px;
}

.dashboard-color-option-dropdown {
    width: 28px;
    height: 28px;
    border: 2px solid var(--border);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dashboard-color-option-dropdown:hover {
    transform: scale(1.15);
    border-color: var(--accent);
}

.dashboard-color-option-dropdown.white { background: #ffffff; }
.dashboard-color-option-dropdown.red { background: #ef4444; }
.dashboard-color-option-dropdown.black { background: #1e293b; }
.dashboard-color-option-dropdown.dodgerblue { background: #1e90ff; }
.dashboard-color-option-dropdown.warning { background: #fbbf24; }
.dashboard-color-option-dropdown.success { background: #10b981; }

.dark .dashboard-note-color-picker-dropdown {
    background: var(--card);
    border-color: var(--border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Hide the floating add button since we moved it to header */
#dashboardAddNoteBtn {
    display: none !important;
}

/* Resize handle for dashboard notes */
.dashboard-note-widget-resize {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 20px;
    height: 20px;
    cursor: nwse-resize;
    background: linear-gradient(-45deg, transparent 30%, rgba(0, 0, 0, 0.2) 30%, rgba(0, 0, 0, 0.2) 35%, transparent 35%, transparent 65%, rgba(0, 0, 0, 0.2) 65%, rgba(0, 0, 0, 0.2) 70%, transparent 70%);
    z-index: 10;
}

.dark .dashboard-note-widget-resize {
    background: linear-gradient(-45deg, transparent 30%, rgba(255, 255, 255, 0.2) 30%, rgba(255, 255, 255, 0.2) 35%, transparent 35%, transparent 65%, rgba(255, 255, 255, 0.2) 65%, rgba(255, 255, 255, 0.2) 70%, transparent 70%);
}

/* Ensure delete modal is always on top for dashboard */
#dashboardDeleteNoteModal {
    z-index: 99999 !important;
}

#dashboardDeleteNoteModal .modal-backdrop {
    z-index: 99998 !important;
}

#dashboardDeleteNoteModal .modal-dialog {
    z-index: 100000 !important;
}
</style>

<script>
// Load today's alerts
function loadTodayAlerts() {
    fetch('/api/alerts/today')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('todayAlertsContainer');
            if (data.success && data.alerts && data.alerts.length > 0) {
                let html = '<div class="list-group">';
                data.alerts.forEach(alert => {
                    const patientName = alert.patient_first_name && alert.patient_last_name 
                        ? `${alert.patient_first_name} ${alert.patient_last_name}` 
                        : 'N/A';
                    const alertDateTime = new Date(`${alert.alert_date}T${alert.alert_time}`);
                    const timeStr = alertDateTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    
                    html += `
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <i class="bi bi-bell-fill text-warning me-2"></i>
                                        ${escapeHtml(alert.message)}
                                    </h6>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-clock me-1"></i>${timeStr}
                                        ${alert.patient_id ? ` | <i class="bi bi-person me-1"></i>${escapeHtml(patientName)}` : ''}
                                    </p>
                                </div>
                                ${alert.patient_id ? `
                                    <a href="/doctor/patients/${alert.patient_id}" class="btn btn-sm btn-primary ms-2">
                                        <i class="bi bi-person me-1"></i>View Patient
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No alerts for today</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('todayAlertsContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error loading alerts
                </div>
            `;
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    loadTodayAlerts();
    loadDashboardNotes();
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Upcoming Appointments Pagination
    let upcomingCurrentPage = 1;
    let upcomingPerPage = 10;
    
    // Load upcoming appointments on page load
    loadUpcomingAppointments(upcomingCurrentPage, upcomingPerPage);
    
    // Handle per page change for upcoming appointments
    document.getElementById('upcomingPerPageSelect').addEventListener('change', function() {
        upcomingPerPage = parseInt(this.value);
        upcomingCurrentPage = 1;
        loadUpcomingAppointments(upcomingCurrentPage, upcomingPerPage);
    });
    
    function loadUpcomingAppointments(page, limit) {
        const container = document.getElementById('upcomingAppointmentsContainer');
        const paginationNav = document.getElementById('upcomingPaginationNav');
        
        // Show loading
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        paginationNav.style.display = 'none';
        
        fetch(`/api/upcoming-appointments?page=${page}&per_page=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    renderUpcomingAppointments(data.data.items);
                    renderUpcomingPagination(data.data.pagination);
                } else {
                    container.innerHTML = '<p class="text-muted text-center py-3">Error loading upcoming appointments</p>';
                }
            })
            .catch(error => {
                console.error('Error loading upcoming appointments:', error);
                container.innerHTML = '<p class="text-muted text-center py-3">Error loading upcoming appointments</p>';
            });
    }
    
    function renderUpcomingAppointments(appointments) {
        const container = document.getElementById('upcomingAppointmentsContainer');
        
        if (!appointments || appointments.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No upcoming appointments</p>';
            return;
        }
        
        let html = '<div class="list-group list-group-flush">';
        appointments.forEach(appointment => {
            const statusBadgeClass = getStatusBadgeClass(appointment.status);
            const formattedDate = new Date(appointment.date).toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const formattedStartTime = appointment.start_time ? appointment.start_time.substring(0, 5) : '';
            const formattedEndTime = appointment.end_time ? appointment.end_time.substring(0, 5) : '';
            
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-3 mb-2" style="border-bottom: 1px solid var(--border) !important;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <a href="/doctor/patients/${appointment.patient_id}" 
                               class="patient-name-link"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Patient Profile">
                                ${escapeHtml((appointment.first_name || '') + ' ' + (appointment.last_name || ''))}
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            ${formattedStartTime} - ${formattedEndTime}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            ${formattedDate}
                        </small>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="text-end">
                            <span class="badge ${statusBadgeClass}">
                                ${appointment.status}
                            </span>
                            <br>
                            <small class="text-muted">
                                ${appointment.visit_type || ''}
                            </small>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="/doctor/appointments/${appointment.id}" 
                               class="btn btn-outline-primary"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Appointment Details">
                                <i class="bi bi-calendar-event me-1"></i>View Appointment
                            </a>
                            <a href="/doctor/patients/${appointment.patient_id}" 
                               class="btn btn-outline-info"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Patient Profile">
                                <i class="bi bi-person-circle me-1"></i>View Patient
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
        
        // Reinitialize tooltips
        const tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    function renderUpcomingPagination(pagination) {
        const paginationNav = document.getElementById('upcomingPaginationNav');
        const paginationList = document.getElementById('upcomingPaginationList');
        
        if (!pagination || pagination.total_pages <= 1) {
            paginationNav.style.display = 'none';
            return;
        }
        
        paginationNav.style.display = 'block';
        
        let html = '';
        const currentPageNum = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        // Previous button
        html += `
            <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${currentPageNum - 1}); return false;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${totalPages}); return false;">${totalPages}</a></li>`;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${currentPageNum + 1}); return false;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationList.innerHTML = html;
    }
    
    // Global function for upcoming appointments pagination
    window.loadUpcomingAppointmentsPage = function(page) {
        upcomingCurrentPage = page;
        loadUpcomingAppointments(upcomingCurrentPage, upcomingPerPage);
        // Scroll to top of container
        document.getElementById('upcomingAppointmentsContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    
    // Missed Appointments Pagination
    let missedCurrentPage = 1;
    let missedPerPage = 10;
    
    // Load missed appointments on page load
    loadMissedAppointments(missedCurrentPage, missedPerPage);
    
    // Handle per page change for missed appointments
    document.getElementById('missedPerPageSelect').addEventListener('change', function() {
        missedPerPage = parseInt(this.value);
        missedCurrentPage = 1;
        loadMissedAppointments(missedCurrentPage, missedPerPage);
    });
    
    function loadMissedAppointments(page, limit) {
        const container = document.getElementById('missedAppointmentsContainer');
        const paginationNav = document.getElementById('missedPaginationNav');
        
        // Show loading
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        paginationNav.style.display = 'none';
        
        fetch(`/api/missed-appointments?page=${page}&per_page=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    renderMissedAppointments(data.data.items);
                    renderMissedPagination(data.data.pagination);
                } else {
                    container.innerHTML = '<p class="text-muted text-center py-3">Error loading missed appointments</p>';
                }
            })
            .catch(error => {
                console.error('Error loading missed appointments:', error);
                container.innerHTML = '<p class="text-muted text-center py-3">Error loading missed appointments</p>';
            });
    }
    
    function renderMissedAppointments(appointments) {
        const container = document.getElementById('missedAppointmentsContainer');
        
        if (!appointments || appointments.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No missed appointments</p>';
            return;
        }
        
        let html = '<div class="list-group list-group-flush">';
        appointments.forEach(appointment => {
            const statusBadgeClass = getStatusBadgeClass(appointment.status);
            const formattedDate = new Date(appointment.date).toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const formattedStartTime = appointment.start_time ? appointment.start_time.substring(0, 5) : '';
            const formattedEndTime = appointment.end_time ? appointment.end_time.substring(0, 5) : '';
            
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-3 mb-2" style="border-bottom: 1px solid var(--border) !important;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <a href="/doctor/patients/${appointment.patient_id}" 
                               class="patient-name-link"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Patient Profile">
                                ${escapeHtml((appointment.first_name || '') + ' ' + (appointment.last_name || ''))}
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            ${formattedStartTime} - ${formattedEndTime}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            ${formattedDate}
                        </small>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="text-end">
                            <span class="badge ${statusBadgeClass}">
                                ${appointment.status}
                            </span>
                            <br>
                            <small class="text-muted">
                                ${appointment.visit_type || ''}
                            </small>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" 
                                    class="btn btn-outline-success"
                                    onclick="markMissedAppointmentCompleted(${appointment.id})"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Mark as Completed">
                                <i class="bi bi-check-circle me-1"></i>Mark Completed
                            </button>
                            <a href="/doctor/appointments/${appointment.id}" 
                               class="btn btn-outline-primary"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Appointment Details">
                                <i class="bi bi-calendar-event me-1"></i>View Appointment
                            </a>
                            <a href="/doctor/patients/${appointment.patient_id}" 
                               class="btn btn-outline-info"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Patient Profile">
                                <i class="bi bi-person-circle me-1"></i>View Patient
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
        
        // Reinitialize tooltips
        const tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    function renderMissedPagination(pagination) {
        const paginationNav = document.getElementById('missedPaginationNav');
        const paginationList = document.getElementById('missedPaginationList');
        
        if (!pagination || pagination.total_pages <= 1) {
            paginationNav.style.display = 'none';
            return;
        }
        
        paginationNav.style.display = 'block';
        
        let html = '';
        const currentPageNum = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        // Previous button
        html += `
            <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${currentPageNum - 1}); return false;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${totalPages}); return false;">${totalPages}</a></li>`;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${currentPageNum + 1}); return false;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationList.innerHTML = html;
    }
    
    // Global function for missed appointments pagination
    window.loadMissedAppointmentsPage = function(page) {
        missedCurrentPage = page;
        loadMissedAppointments(missedCurrentPage, missedPerPage);
        // Scroll to top of container
        document.getElementById('missedAppointmentsContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    
    // Function to mark missed appointment as completed (shows modal)
    window.markMissedAppointmentCompleted = function(appointmentId) {
        showMissedAppointmentCompletionModal(appointmentId);
    };
    
    // Show completion confirmation modal for missed appointments
    function showMissedAppointmentCompletionModal(appointmentId) {
        const modalHtml = `
            <div class="modal fade" id="missedAppointmentCompletionModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background-color: var(--card); border-color: var(--border); color: var(--text);">
                        <div class="modal-header bg-success text-white" style="background-color: var(--success) !important; border-bottom-color: var(--border) !important;">
                            <h5 class="modal-title" style="color: white !important;">
                                <i class="bi bi-check-circle me-2"></i>Confirm Appointment Completion
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center" style="background-color: var(--card); color: var(--text);">
                            <div class="mb-4">
                                <i class="bi bi-question-circle-fill text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h6 class="mb-3" style="color: var(--text);">Are you sure you want to mark this appointment as completed?</h6>
                            <p class="text-muted mb-0" style="color: var(--muted);">
                                This will update the appointment status to "completed" and cannot be undone.
                            </p>
                        </div>
                        <div class="modal-footer justify-content-center" style="background-color: var(--card); border-top-color: var(--border);">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-success" onclick="confirmMissedAppointmentCompleted(${appointmentId})">
                                <i class="bi bi-check-circle me-1"></i>Confirm Completion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('missedAppointmentCompletionModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('missedAppointmentCompletionModal'));
        modal.show();
        
        // Clean up modal after hide
        document.getElementById('missedAppointmentCompletionModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    // Confirm missed appointment completion
    window.confirmMissedAppointmentCompleted = function(appointmentId) {
        // Hide the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('missedAppointmentCompletionModal'));
        modal.hide();
        
        // Show loading state
        const button = document.querySelector(`button[onclick="confirmMissedAppointmentCompleted(${appointmentId})"]`);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
        }
        
        // API call to update status to Completed
        fetch(`/api/appointments/${appointmentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: 'Completed',
                reason: null
            })
        })
        .then(() => {
            // Always reload page regardless of response
            window.location.reload();
        })
        .catch(() => {
            // Even on error, reload page
            window.location.reload();
        });
    };
    
    function getStatusBadgeClass(status) {
        const statusClasses = {
            'Booked': 'badge-primary',
            'CheckedIn': 'badge-info',
            'InProgress': 'badge-warning',
            'Completed': 'badge-success',
            'Cancelled': 'badge-danger',
            'NoShow': 'badge-secondary',
            'Rescheduled': 'badge-info'
        };
        return statusClasses[status] || 'badge-secondary';
    }
    
    // Recent Activity Pagination
    let currentPage = 1;
    let perPage = 10;
    
    // Load recent activity on page load
    loadRecentActivity(currentPage, perPage);
    
    // Handle per page change
    document.getElementById('perPageSelect').addEventListener('change', function() {
        perPage = parseInt(this.value);
        currentPage = 1;
        loadRecentActivity(currentPage, perPage);
    });
    
    function loadRecentActivity(page, limit) {
        const container = document.getElementById('recentActivityContainer');
        const paginationNav = document.getElementById('paginationNav');
        
        // Show loading
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        paginationNav.style.display = 'none';
        
        fetch(`/api/recent-activity?page=${page}&per_page=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    renderRecentActivity(data.data.items);
                    renderPagination(data.data.pagination);
                } else {
                    container.innerHTML = '<p class="text-muted text-center py-3">Error loading recent activity</p>';
                }
            })
            .catch(error => {
                console.error('Error loading recent activity:', error);
                container.innerHTML = '<p class="text-muted text-center py-3">Error loading recent activity</p>';
            });
    }
    
    function renderRecentActivity(events) {
        const container = document.getElementById('recentActivityContainer');
        
        if (!events || events.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No recent activity</p>';
            return;
        }
        
        let html = '<div class="timeline">';
        events.forEach(event => {
            const date = new Date(event.created_at);
            const formattedDate = date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            html += `
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="timeline-marker me-3">
                            <div class="bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${escapeHtml(event.event_summary || '')}</h6>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-person me-1"></i>
                                ${escapeHtml((event.first_name || '') + ' ' + (event.last_name || ''))}
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                ${formattedDate}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }
    
    function renderPagination(pagination) {
        const paginationNav = document.getElementById('paginationNav');
        const paginationList = document.getElementById('paginationList');
        
        if (!pagination || pagination.total_pages <= 1) {
            paginationNav.style.display = 'none';
            return;
        }
        
        paginationNav.style.display = 'block';
        
        let html = '';
        const currentPageNum = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        // Previous button
        html += `
            <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadRecentActivityPage(${currentPageNum - 1}); return false;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadRecentActivityPage(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadRecentActivityPage(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadRecentActivityPage(${totalPages}); return false;">${totalPages}</a></li>`;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadRecentActivityPage(${currentPageNum + 1}); return false;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationList.innerHTML = html;
    }
    
    // Global function for pagination
    window.loadRecentActivityPage = function(page) {
        currentPage = page;
        loadRecentActivity(currentPage, perPage);
        // Scroll to top of container
        document.getElementById('recentActivityContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Dashboard Notes Management
    const dashboardColorMap = {
        'white': { bg: '#ffffff', class: 'white', text: 'dark-text' },
        'red': { bg: '#ef4444', class: 'red', text: 'light-text' },
        'black': { bg: '#1e293b', class: 'black', text: 'light-text' },
        'dodgerblue': { bg: '#1e90ff', class: 'dodgerblue', text: 'light-text' },
        'warning': { bg: '#fbbf24', class: 'warning', text: 'dark-text' },
        'success': { bg: '#10b981', class: 'success', text: 'light-text' }
    };
    
    let dashboardNotes = [];
    let dashboardIsDragging = false;
    let dashboardCurrentDragNote = null;
    let dashboardDragOffset = { x: 0, y: 0 };
    
    function getDashboardColorClass(backgroundColor) {
        for (const [key, value] of Object.entries(dashboardColorMap)) {
            if (value.bg.toLowerCase() === backgroundColor.toLowerCase()) {
                return value.class;
            }
        }
        return 'warning';
    }
    
    function getDashboardTextColor(backgroundColor) {
        const hex = backgroundColor.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return brightness > 128 ? 'dark-text' : 'light-text';
    }
    
    function createDashboardNoteWidget(note, index = 0) {
        const bgColor = note.background_color || '#fbbf24';
        const colorClass = getDashboardColorClass(bgColor);
        const textColorClass = getDashboardTextColor(bgColor);
        
        // Always use default size for dashboard display (regardless of saved size in DB)
        const defaultWidth = 300;
        const defaultHeight = 250;
        
        // Position notes side by side instead of using DB position
        const spacing = 20; // Space between notes
        const notesPerRow = 3; // Number of notes per row
        const row = Math.floor(index / notesPerRow);
        const col = index % notesPerRow;
        const x = col * (defaultWidth + spacing) + spacing;
        const y = row * (defaultHeight + spacing) + spacing;
        
        const widget = document.createElement('div');
        widget.className = `dashboard-note-widget color-${colorClass} ${textColorClass}`;
        widget.id = `dashboard-note-${note.id}`;
        widget.style.left = `${x}px`;
        widget.style.top = `${y}px`;
        widget.style.width = `${defaultWidth}px`;
        widget.style.height = `${defaultHeight}px`;
        widget.style.zIndex = note.z_index || 1;
        
        widget.innerHTML = `
            <div class="dashboard-note-widget-header" onmousedown="dashboardStartDrag(event, ${note.id})">
                <input type="text" class="dashboard-note-widget-title" placeholder="Title..." value="${escapeHtml(note.title || '')}" 
                       data-note-id="${note.id}" onblur="dashboardUpdateNoteTitle(${note.id}, this.value)">
                <div class="dashboard-note-widget-actions">
                    <div class="dashboard-note-color-picker-wrapper" style="position: relative;">
                        <button class="dashboard-note-widget-btn" onclick="dashboardToggleColorPicker(${note.id}, event)" title="Change color">
                            <i class="bi bi-palette"></i>
                        </button>
                        <div class="dashboard-note-color-picker-dropdown" id="dashboardColorPicker-${note.id}" style="display: none;">
                            <div class="dashboard-color-option-dropdown white" onclick="dashboardChangeNoteColor(${note.id}, '#ffffff')"></div>
                            <div class="dashboard-color-option-dropdown red" onclick="dashboardChangeNoteColor(${note.id}, '#ef4444')"></div>
                            <div class="dashboard-color-option-dropdown black" onclick="dashboardChangeNoteColor(${note.id}, '#1e293b')"></div>
                            <div class="dashboard-color-option-dropdown dodgerblue" onclick="dashboardChangeNoteColor(${note.id}, '#1e90ff')"></div>
                            <div class="dashboard-color-option-dropdown warning" onclick="dashboardChangeNoteColor(${note.id}, '#fbbf24')"></div>
                            <div class="dashboard-color-option-dropdown success" onclick="dashboardChangeNoteColor(${note.id}, '#10b981')"></div>
                        </div>
                    </div>
                    <button class="dashboard-note-widget-btn" onclick="dashboardBringToFront(${note.id})" title="Bring to front">
                        <i class="bi bi-layers"></i>
                    </button>
                    <button class="dashboard-note-widget-btn" onclick="dashboardFitToSize(${note.id})" title="Fit to default size">
                        <i class="bi bi-arrows-angle-contract"></i>
                    </button>
                    <button class="dashboard-note-widget-btn delete" onclick="dashboardDeleteNote(${note.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="dashboard-note-widget-body">
                <div class="dashboard-note-widget-content"
                     contenteditable="true"
                     data-placeholder="Write your note... (Use @ for patients, # for appointments, $ for drugs)"
                     data-note-id="${note.id}"
                     onblur="dashboardUpdateNoteContent(${note.id}, this.innerHTML)">${note.content || ''}</div>
            </div>
            <div class="dashboard-note-widget-footer">
                <span>Created: ${new Date(note.created_at).toLocaleDateString()}</span>
                <span>Updated: ${new Date(note.updated_at).toLocaleDateString()}</span>
            </div>
            <div class="dashboard-note-widget-resize" onmousedown="dashboardStartResize(event, ${note.id})"></div>
        `;
        
        return widget;
    }
    
    async function loadDashboardNotes() {
        try {
            const response = await fetch('/api/notes', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }
            
            const data = await response.json();
            const container = document.getElementById('dashboardNotesContainer');
            const loading = document.getElementById('dashboardNotesLoading');
            const empty = document.getElementById('dashboardNotesEmpty');
            
            if (data.success && data.notes && data.notes.length > 0) {
                loading.style.display = 'none';
                empty.style.display = 'none';
                
                // Load all notes (no limit)
                dashboardNotes = data.notes;
                
                // Clear container
                container.querySelectorAll('.dashboard-note-widget').forEach(w => w.remove());
                
                // Add notes (with index for positioning)
                dashboardNotes.forEach((note, index) => {
                    const widget = createDashboardNoteWidget(note, index);
                    container.appendChild(widget);
                    
                    // Initialize autocomplete for this contenteditable
                    const contentEditable = widget.querySelector('.dashboard-note-widget-content[contenteditable="true"]');
                    if (contentEditable) {
                        dashboardInitAutocomplete(contentEditable);
                    }
                });
                
                // Keep header button enabled (no limit)
                const headerBtn = document.getElementById('dashboardAddNoteBtnHeader');
                if (headerBtn) {
                    headerBtn.disabled = false;
                    headerBtn.title = '';
                }
            } else {
                loading.style.display = 'none';
                empty.style.display = 'block';
                
                // Keep header button enabled (no limit)
                const headerBtn = document.getElementById('dashboardAddNoteBtnHeader');
                if (headerBtn) {
                    headerBtn.disabled = false;
                    headerBtn.title = '';
                }
            }
        } catch (error) {
            console.error('Error loading dashboard notes:', error);
            document.getElementById('dashboardNotesLoading').style.display = 'none';
        }
    }
    
    async function dashboardAddNote() {
        const container = document.getElementById('dashboardNotesContainer');
        const containerRect = container.getBoundingClientRect();
        
        // Default size for new notes
        const defaultWidth = 300;
        const defaultHeight = 250;
        
        // Position new note next to existing notes (side by side)
        const existingNotes = container.querySelectorAll('.dashboard-note-widget');
        const spacing = 20;
        const notesPerRow = 3;
        const index = existingNotes.length;
        const row = Math.floor(index / notesPerRow);
        const col = index % notesPerRow;
        const x = col * (defaultWidth + spacing) + spacing;
        const y = row * (defaultHeight + spacing) + spacing;
        
        try {
            const response = await fetch('/api/notes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: '',
                    content: '',
                    background_color: '#fbbf24',
                    position_x: x,
                    position_y: y,
                    width: defaultWidth,
                    height: defaultHeight,
                    z_index: 1
                })
            });
            
            const data = await response.json();
            if (data.success) {
                loadDashboardNotes();
            }
        } catch (error) {
            console.error('Error creating note:', error);
            alert('Failed to create note. Please try again.');
        }
    }
    
    function dashboardStartDrag(event, noteId) {
        if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'BUTTON') {
            return;
        }
        
        dashboardIsDragging = true;
        dashboardCurrentDragNote = noteId;
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        const rect = widget.getBoundingClientRect();
        const containerRect = document.getElementById('dashboardNotesContainer').getBoundingClientRect();
        
        dashboardDragOffset.x = event.clientX - rect.left;
        dashboardDragOffset.y = event.clientY - rect.top;
        
        widget.classList.add('dragging');
        
        document.addEventListener('mousemove', dashboardOnDrag);
        document.addEventListener('mouseup', dashboardStopDrag);
        event.preventDefault();
    }
    
    function dashboardOnDrag(event) {
        if (!dashboardIsDragging || !dashboardCurrentDragNote) return;
        
        const widget = document.getElementById(`dashboard-note-${dashboardCurrentDragNote}`);
        const container = document.getElementById('dashboardNotesContainer');
        const containerRect = container.getBoundingClientRect();
        
        let x = event.clientX - containerRect.left - dashboardDragOffset.x;
        let y = event.clientY - containerRect.top - dashboardDragOffset.y;
        
        x = Math.max(0, Math.min(x, containerRect.width - widget.offsetWidth));
        y = Math.max(0, Math.min(y, containerRect.height - widget.offsetHeight));
        
        widget.style.left = `${x}px`;
        widget.style.top = `${y}px`;
    }
    
    function dashboardStopDrag() {
        if (dashboardIsDragging && dashboardCurrentDragNote) {
            const widget = document.getElementById(`dashboard-note-${dashboardCurrentDragNote}`);
            widget.classList.remove('dragging');
            
            dashboardUpdateNotePosition(
                dashboardCurrentDragNote,
                parseInt(widget.style.left),
                parseInt(widget.style.top)
            );
            
            dashboardIsDragging = false;
            dashboardCurrentDragNote = null;
        }
        
        document.removeEventListener('mousemove', dashboardOnDrag);
        document.removeEventListener('mouseup', dashboardStopDrag);
    }
    
    function dashboardToggleColorPicker(noteId, event) {
        event.stopPropagation();
        
        document.querySelectorAll('.dashboard-note-color-picker-dropdown').forEach(picker => {
            if (picker.id !== `dashboardColorPicker-${noteId}`) {
                picker.style.display = 'none';
            }
        });
        
        const picker = document.getElementById(`dashboardColorPicker-${noteId}`);
        if (picker) {
            picker.style.display = picker.style.display === 'none' ? 'flex' : 'none';
            
            setTimeout(() => {
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && !e.target.closest(`#dashboardColorPicker-${noteId}`)) {
                        picker.style.display = 'none';
                        document.removeEventListener('click', closePicker);
                    }
                });
            }, 10);
        }
    }
    
    function dashboardChangeNoteColor(noteId, color) {
        const picker = document.getElementById(`dashboardColorPicker-${noteId}`);
        if (picker) picker.style.display = 'none';
        
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        if (!widget) return;
        
        const colorClass = getDashboardColorClass(color);
        const textColorClass = getDashboardTextColor(color);
        
        widget.classList.remove('color-white', 'color-red', 'color-black', 'color-dodgerblue', 'color-warning', 'color-success');
        widget.classList.remove('light-text', 'dark-text');
        widget.classList.add(`color-${colorClass}`);
        widget.classList.add(textColorClass);
        
        dashboardUpdateNote(noteId, { background_color: color });
    }
    
    async function dashboardUpdateNoteTitle(noteId, title) {
        await dashboardUpdateNote(noteId, { title });
    }
    
    async function dashboardUpdateNoteContent(noteId, content) {
        // content is already HTML from contenteditable innerHTML
        await dashboardUpdateNote(noteId, { content: content });
    }
    
    async function dashboardUpdateNotePosition(noteId, x, y) {
        await dashboardUpdateNote(noteId, { position_x: x, position_y: y });
    }
    
    // Resize functionality for dashboard notes
    let dashboardIsResizing = false;
    let dashboardCurrentResizeNote = null;
    let dashboardResizeStart = { x: 0, y: 0, width: 0, height: 0 };
    
    function dashboardStartResize(event, noteId) {
        event.preventDefault();
        event.stopPropagation();
        
        dashboardIsResizing = true;
        dashboardCurrentResizeNote = noteId;
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        
        if (!widget) {
            dashboardIsResizing = false;
            return;
        }
        
        const rect = widget.getBoundingClientRect();
        
        dashboardResizeStart.x = event.clientX;
        dashboardResizeStart.y = event.clientY;
        dashboardResizeStart.width = rect.width;
        dashboardResizeStart.height = rect.height;
        
        // Bring note to front during resize
        const container = document.getElementById('dashboardNotesContainer');
        if (container) {
            const allNotes = container.querySelectorAll('.dashboard-note-widget');
            let maxZIndex = 0;
            allNotes.forEach(note => {
                const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
                if (zIndex > maxZIndex) maxZIndex = zIndex;
            });
            widget.style.zIndex = maxZIndex + 1;
        }
        
        document.addEventListener('mousemove', dashboardOnResize);
        document.addEventListener('mouseup', dashboardStopResize);
    }
    
    function dashboardOnResize(event) {
        if (!dashboardIsResizing || !dashboardCurrentResizeNote) return;
        
        event.preventDefault();
        
        const widget = document.getElementById(`dashboard-note-${dashboardCurrentResizeNote}`);
        if (!widget) {
            dashboardStopResize();
            return;
        }
        
        const container = document.getElementById('dashboardNotesContainer');
        if (!container) {
            dashboardStopResize();
            return;
        }
        
        const containerRect = container.getBoundingClientRect();
        const widgetRect = widget.getBoundingClientRect();
        
        const deltaX = event.clientX - dashboardResizeStart.x;
        const deltaY = event.clientY - dashboardResizeStart.y;
        
        let newWidth = dashboardResizeStart.width + deltaX;
        let newHeight = dashboardResizeStart.height + deltaY;
        
        // Constrain to container and min size only (no max size)
        const minWidth = 250;
        const minHeight = 200;
        
        // Calculate max width/height based on container bounds only
        const maxAllowedWidth = containerRect.width - (widgetRect.left - containerRect.left);
        const maxAllowedHeight = containerRect.height - (widgetRect.top - containerRect.top);
        
        newWidth = Math.max(minWidth, Math.min(newWidth, maxAllowedWidth));
        newHeight = Math.max(minHeight, Math.min(newHeight, maxAllowedHeight));
        
        widget.style.width = `${newWidth}px`;
        widget.style.height = `${newHeight}px`;
    }
    
    function dashboardStopResize() {
        if (dashboardIsResizing && dashboardCurrentResizeNote) {
            const widget = document.getElementById(`dashboard-note-${dashboardCurrentResizeNote}`);
            
            // Save size
            dashboardUpdateNoteSize(
                dashboardCurrentResizeNote,
                parseInt(widget.style.width),
                parseInt(widget.style.height)
            );
            
            dashboardIsResizing = false;
            dashboardCurrentResizeNote = null;
        }
        
        document.removeEventListener('mousemove', dashboardOnResize);
        document.removeEventListener('mouseup', dashboardStopResize);
    }
    
    async function dashboardUpdateNoteSize(noteId, width, height) {
        await dashboardUpdateNote(noteId, { width, height });
    }
    
    // Bring to front
    function dashboardBringToFront(noteId) {
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        const container = document.getElementById('dashboardNotesContainer');
        if (!widget || !container) return;
        
        const allNotes = container.querySelectorAll('.dashboard-note-widget');
        let maxZIndex = 0;
        allNotes.forEach(note => {
            const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
            if (zIndex > maxZIndex) maxZIndex = zIndex;
        });
        
        widget.style.zIndex = maxZIndex + 1;
        dashboardUpdateNote(noteId, { z_index: maxZIndex + 1 });
    }
    
    // Fit to default size
    function dashboardFitToSize(noteId) {
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        if (!widget) return;
        
        const defaultWidth = 300;
        const defaultHeight = 250;
        
        widget.style.width = `${defaultWidth}px`;
        widget.style.height = `${defaultHeight}px`;
        
        dashboardUpdateNoteSize(noteId, defaultWidth, defaultHeight);
    }
    
    async function dashboardUpdateNote(noteId, data) {
        try {
            const response = await fetch(`/api/notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Network error' }));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to update note');
            }
        } catch (error) {
            console.error('Error updating note:', error);
            // Don't show alert for every update - it's too frequent
            // Only log to console
        }
    }
    
    async function dashboardDeleteNote(noteId) {
        // Show confirmation modal
        dashboardShowDeleteConfirmModal(noteId);
    }
    
    // Show delete confirmation modal
    function dashboardShowDeleteConfirmModal(noteId) {
        const modal = document.getElementById('dashboardDeleteNoteModal');
        if (!modal) {
            // Create modal if it doesn't exist
            const modalHtml = `
                <div class="modal fade" id="dashboardDeleteNoteModal" tabindex="-1" aria-labelledby="dashboardDeleteNoteModalLabel" aria-hidden="true" style="z-index: 99999;">
                    <div class="modal-dialog modal-dialog-centered" style="z-index: 100000;">
                        <div class="modal-content" style="background: var(--card); border: 1px solid var(--border); z-index: 100001;">
                            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                                <h5 class="modal-title" id="dashboardDeleteNoteModalLabel" style="color: var(--text);">
                                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                    Delete Note
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="color: var(--text);">
                                <p>Are you sure you want to delete this note? This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer" style="border-top: 1px solid var(--border);">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="dashboardConfirmDeleteBtn">
                                    <i class="bi bi-trash me-2"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        const modalInstance = new bootstrap.Modal(document.getElementById('dashboardDeleteNoteModal'));
        const confirmBtn = document.getElementById('dashboardConfirmDeleteBtn');
        
        // Remove previous event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Add new event listener
        newConfirmBtn.addEventListener('click', async function() {
            await dashboardPerformDelete(noteId);
            modalInstance.hide();
        });
        
        modalInstance.show();
    }
    
    // Perform the actual delete
    async function dashboardPerformDelete(noteId) {
        try {
            const response = await fetch(`/api/notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            
            // Check if response is ok
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Network error' }));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                const widget = document.getElementById(`dashboard-note-${noteId}`);
                if (widget) {
                    widget.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    widget.style.opacity = '0';
                    widget.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        widget.remove();
                        loadDashboardNotes();
                    }, 300);
                }
            } else {
                throw new Error(data.message || 'Failed to delete note');
            }
        } catch (error) {
            console.error('Error deleting note:', error);
            alert('Failed to delete note: ' + error.message);
        }
    }
    
    // Autocomplete functionality for dashboard notes
    let dashboardAutocompletePortal = null;
    let dashboardCurrentAutocompleteType = null;
    let dashboardCurrentAutocompleteQuery = '';
    let dashboardCurrentAutocompleteItems = [];
    let dashboardSelectedAutocompleteIndex = -1;
    let dashboardAutocompleteTextarea = null;
    let dashboardAutocompleteCursorPosition = 0;
    let dashboardAutocompleteDebounceTimer = null;
    let dashboardAutocompleteUpdateHandler = null;
    
    // Initialize autocomplete for a contenteditable div
    function dashboardInitAutocomplete(contentEditable) {
        if (!contentEditable) return;
        
        contentEditable.addEventListener('input', dashboardHandleContentEditableInput);
        contentEditable.addEventListener('keydown', dashboardHandleContentEditableKeydown);
        contentEditable.addEventListener('blur', function() {
            setTimeout(() => {
                dashboardHideAutocomplete();
            }, 200);
        });
        
        contentEditable.addEventListener('click', function(event) {
            const target = event.target;
            const link = target.closest('a[data-type]');
            
            if (link) {
                event.stopPropagation();
                event.preventDefault();
                window.open(link.href, '_blank');
                return false;
            }
        }, true);
        
        contentEditable.addEventListener('mousedown', function(event) {
            const target = event.target;
            const link = target.closest('a[data-type]');
            const badge = target.closest('span[data-type]');
            
            if (link) {
                event.stopPropagation();
                return true;
            }
            
            if (badge) {
                event.preventDefault();
                event.stopPropagation();
                const range = document.createRange();
                const selection = window.getSelection();
                const badgeRect = badge.getBoundingClientRect();
                const clickX = event.clientX;
                
                if (clickX < badgeRect.left + badgeRect.width / 2) {
                    range.setStartBefore(badge);
                } else {
                    range.setStartAfter(badge);
                }
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                contentEditable.focus();
            }
        }, true);
    }
    
    function dashboardHandleContentEditableInput(event) {
        const contentEditable = event.target;
        const selection = window.getSelection();
        
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const startContainer = range.startContainer;
            
            let autocompleteElement = null;
            if (startContainer.nodeType === Node.TEXT_NODE) {
                autocompleteElement = startContainer.parentElement;
            } else if (startContainer.nodeType === Node.ELEMENT_NODE) {
                autocompleteElement = startContainer;
            }
            
            while (autocompleteElement && autocompleteElement !== contentEditable) {
                const dataType = autocompleteElement.getAttribute('data-type');
                if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                    const inputType = event.inputType;
                    if (inputType === 'deleteContentBackward' || inputType === 'deleteContentForward' || 
                        inputType === 'deleteByDrag' || inputType === 'deleteByCut' ||
                        (!inputType && event.data === null)) {
                        const parent = autocompleteElement.parentNode;
                        if (parent) {
                            const space = document.createTextNode(' ');
                            parent.replaceChild(space, autocompleteElement);
                            
                            const newRange = document.createRange();
                            newRange.setStartAfter(space);
                            newRange.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(newRange);
                            contentEditable.focus();
                            
                            const noteId = contentEditable.getAttribute('data-note-id');
                            if (noteId) {
                                dashboardUpdateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                            }
                        }
                        return;
                    }
                    break;
                }
                autocompleteElement = autocompleteElement.parentElement;
            }
        }
        
        if (dashboardAutocompleteDebounceTimer) {
            clearTimeout(dashboardAutocompleteDebounceTimer);
        }
        
        dashboardAutocompleteDebounceTimer = setTimeout(() => {
            dashboardProcessAutocompleteInput(event);
        }, 300);
    }
    
    function dashboardProcessAutocompleteInput(event) {
        const contentEditable = event.target;
        const selection = window.getSelection();
        
        if (!selection.rangeCount) {
            dashboardHideAutocomplete();
            return;
        }
        
        const range = selection.getRangeAt(0).cloneRange();
        
        const fullRange = document.createRange();
        fullRange.selectNodeContents(contentEditable);
        fullRange.setEnd(range.startContainer, range.startOffset);
        const textBeforeCursor = fullRange.toString();
        
        const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
        
        if (match) {
            const trigger = match[1];
            const query = match[2];
            
            let minLength = 2;
            if (trigger === '#') {
                minLength = /^\d+$/.test(query) ? 1 : 2;
            }
            
            if (query.length >= minLength && query !== dashboardCurrentAutocompleteQuery) {
                dashboardCurrentAutocompleteType = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
                dashboardCurrentAutocompleteQuery = query;
                dashboardAutocompleteTextarea = contentEditable;
                
                const rect = range.getBoundingClientRect();
                dashboardAutocompleteCursorPosition = {
                    range: range,
                    textBefore: textBeforeCursor,
                    match: match
                };
                
                dashboardShowAutocomplete(contentEditable, rect, query);
            } else if (query.length < minLength) {
                dashboardHideAutocomplete();
            }
        } else {
            dashboardHideAutocomplete();
        }
    }
    
    function dashboardHandleContentEditableKeydown(event) {
        const contentEditable = event.target;
        const selection = window.getSelection();
        
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            let node = range.startContainer;
            
            while (node && node !== contentEditable) {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    const dataType = node.getAttribute('data-type');
                    if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                        if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar' || event.keyCode === 13 || event.keyCode === 32) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            const textContent = (event.key === 'Enter' || event.keyCode === 13) ? '\n' : ' ';
                            const newTextNode = document.createTextNode(textContent);
                            const parent = node.parentNode;
                            
                            if (parent) {
                                parent.insertBefore(newTextNode, node.nextSibling);
                                
                                const newRange = document.createRange();
                                newRange.setStartAfter(newTextNode);
                                newRange.collapse(true);
                                selection.removeAllRanges();
                                selection.addRange(newRange);
                                contentEditable.focus();
                                
                                const noteId = contentEditable.getAttribute('data-note-id');
                                if (noteId) {
                                    dashboardUpdateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                                }
                            }
                            return;
                        }
                        break;
                    }
                }
                node = node.parentNode;
            }
        }
        
        if (!dashboardAutocompletePortal || dashboardAutocompletePortal.style.display === 'none') {
            return;
        }
        
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            dashboardSelectedAutocompleteIndex = Math.min(dashboardSelectedAutocompleteIndex + 1, dashboardCurrentAutocompleteItems.length - 1);
            dashboardUpdateAutocompleteSelection();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            dashboardSelectedAutocompleteIndex = Math.max(dashboardSelectedAutocompleteIndex - 1, -1);
            dashboardUpdateAutocompleteSelection();
        } else if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            if (dashboardSelectedAutocompleteIndex >= 0 && dashboardCurrentAutocompleteItems[dashboardSelectedAutocompleteIndex]) {
                dashboardSelectAutocompleteItem(dashboardCurrentAutocompleteItems[dashboardSelectedAutocompleteIndex]);
            }
        } else if (event.key === 'Escape') {
            dashboardHideAutocomplete();
        }
    }
    
    async function dashboardShowAutocomplete(contentEditable, cursorRect, query) {
        if (!dashboardAutocompletePortal) {
            dashboardAutocompletePortal = document.createElement('div');
            dashboardAutocompletePortal.className = 'dashboard-note-autocomplete-portal';
            dashboardAutocompletePortal.id = 'dashboardNoteAutocompletePortal';
            document.body.appendChild(dashboardAutocompletePortal);
        }
        
        // Position portal at cursor location (not following mouse)
        const x = cursorRect.left + window.scrollX;
        const y = cursorRect.bottom + window.scrollY + 5;
        
        dashboardAutocompletePortal.style.position = 'fixed';
        dashboardAutocompletePortal.style.left = `${x}px`;
        dashboardAutocompletePortal.style.top = `${y}px`;
        dashboardAutocompletePortal.style.display = 'block';
        dashboardAutocompletePortal.style.zIndex = '9999999';
        
        // Remove any existing mouse tracking handler (we don't want it to follow mouse)
        if (dashboardAutocompleteUpdateHandler) {
            document.removeEventListener('mousemove', dashboardAutocompleteUpdateHandler);
            dashboardAutocompleteUpdateHandler = null;
        }
        
        await dashboardLoadAutocompleteItems(query);
    }
    
    async function dashboardLoadAutocompleteItems(query) {
        try {
            if (query !== dashboardCurrentAutocompleteQuery) {
                return;
            }
            
            let url = '';
            if (dashboardCurrentAutocompleteType === 'patient') {
                url = `/api/patients/search?q=${encodeURIComponent(query)}`;
            } else if (dashboardCurrentAutocompleteType === 'appointment') {
                url = `/api/appointments/search?q=${encodeURIComponent(query)}&limit=10`;
            } else if (dashboardCurrentAutocompleteType === 'drug') {
                url = `/api/searchDrugsAutocomplete?q=${encodeURIComponent(query)}&limit=10`;
            }
            
            if (!url) return;
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                if (response.status !== 400 && response.status !== 404) {
                    console.error('Error loading autocomplete:', response.status);
                }
                return;
            }
            
            const data = await response.json();
            
            if (query !== dashboardCurrentAutocompleteQuery) {
                return;
            }
            
            let items = [];
            
            if (dashboardCurrentAutocompleteType === 'patient' && data.ok && data.data) {
                items = data.data.map(patient => ({
                    type: 'patient',
                    id: patient.id,
                    title: `${patient.first_name} ${patient.last_name}`,
                    subtitle: patient.phone || '',
                    data: patient
                }));
            } else if (dashboardCurrentAutocompleteType === 'appointment' && data.ok && data.data) {
                items = data.data.map(apt => {
                    const date = new Date(apt.date);
                    const dateStr = date.toLocaleDateString('en-GB');
                    const timeStr = apt.start_time ? apt.start_time.substring(0, 5) : '';
                    const patientName = escapeHtml(apt.patient_name || 'Unknown');
                    const status = escapeHtml(apt.status || '');
                    return {
                        type: 'appointment',
                        id: apt.id,
                        title: `#${apt.id} - ${patientName}`,
                        subtitle: `${dateStr} ${timeStr} - ${status}`,
                        data: apt
                    };
                });
            } else if (dashboardCurrentAutocompleteType === 'drug' && data.drugs) {
                items = data.drugs.map(drug => ({
                    type: 'drug',
                    id: drug.ID,
                    title: drug.drug_name,
                    subtitle: drug.active_ingredient || drug.Company || '',
                    data: drug
                }));
            }
            
            if (query === dashboardCurrentAutocompleteQuery) {
                dashboardCurrentAutocompleteItems = items;
                dashboardSelectedAutocompleteIndex = -1;
                dashboardRenderAutocompleteItems(items);
            }
        } catch (error) {
            console.error('Error loading autocomplete items:', error);
        }
    }
    
    function dashboardRenderAutocompleteItems(items) {
        if (!dashboardAutocompletePortal) return;
        
        if (items.length === 0) {
            dashboardAutocompletePortal.innerHTML = '<div class="dashboard-note-autocomplete-item"><div class="item-content">No results found</div></div>';
            return;
        }
        
        let html = '';
        items.forEach((item, index) => {
            const icon = item.type === 'patient' ? 'bi-person' : (item.type === 'appointment' ? 'bi-calendar-event' : 'bi-capsule');
            html += `
                <div class="dashboard-note-autocomplete-item ${index === dashboardSelectedAutocompleteIndex ? 'selected' : ''}" 
                     data-index="${index}"
                     onclick="dashboardSelectAutocompleteItem(${JSON.stringify(item).replace(/"/g, '&quot;')})">
                    <i class="bi ${icon} item-icon"></i>
                    <div class="item-content">
                        <div class="item-title">${escapeHtml(item.title)}</div>
                        ${item.subtitle ? `<div class="item-subtitle">${escapeHtml(item.subtitle)}</div>` : ''}
                    </div>
                </div>
            `;
        });
        
        dashboardAutocompletePortal.innerHTML = html;
    }
    
    function dashboardUpdateAutocompleteSelection() {
        if (!dashboardAutocompletePortal) return;
        
        const items = dashboardAutocompletePortal.querySelectorAll('.dashboard-note-autocomplete-item');
        items.forEach((item, index) => {
            if (index === dashboardSelectedAutocompleteIndex) {
                item.classList.add('selected');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('selected');
            }
        });
    }
    
    function dashboardSelectAutocompleteItem(item) {
        if (!dashboardAutocompleteTextarea || !item || !dashboardAutocompleteCursorPosition) return;
        
        const contentEditable = dashboardAutocompleteTextarea;
        const range = dashboardAutocompleteCursorPosition.range;
        const match = dashboardAutocompleteCursorPosition.match;
        
        if (match && range) {
            range.setStart(range.startContainer, range.startOffset - match[0].length);
            range.deleteContents();
            
            let replacement = null;
            if (item.type === 'patient') {
                replacement = document.createElement('a');
                replacement.href = `/doctor/patients/${item.id}`;
                // Use standard class name (without dashboard- prefix) for compatibility
                replacement.className = 'note-content-link';
                replacement.target = '_blank';
                replacement.setAttribute('data-type', 'patient');
                replacement.setAttribute('data-id', item.id);
                replacement.innerHTML = `<i class="bi bi-person patient-icon"></i>${escapeHtml(item.title)}`;
            } else if (item.type === 'appointment') {
                replacement = document.createElement('a');
                replacement.href = `/doctor/appointments/${item.id}`;
                // Use standard class name (without dashboard- prefix) for compatibility
                replacement.className = 'note-content-appointment-link';
                replacement.target = '_blank';
                replacement.setAttribute('data-type', 'appointment');
                replacement.setAttribute('data-id', item.id);
                replacement.innerHTML = `<i class="bi bi-calendar-event appointment-icon"></i>#${item.id}`;
            } else if (item.type === 'drug') {
                replacement = document.createElement('span');
                // Use standard class name (without dashboard- prefix) for compatibility
                replacement.className = 'note-content-drug-badge';
                replacement.setAttribute('data-type', 'drug');
                replacement.setAttribute('data-id', item.id);
                replacement.innerHTML = `<i class="bi bi-capsule drug-icon"></i>${escapeHtml(item.title)}`;
            }
            
            if (replacement) {
                range.insertNode(replacement);
                
                const spaceAfter = document.createTextNode(' ');
                range.setStartAfter(replacement);
                range.insertNode(spaceAfter);
                
                const newRange = document.createRange();
                newRange.setStartAfter(spaceAfter);
                newRange.collapse(true);
                
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(newRange);
                
                setTimeout(() => {
                    contentEditable.focus();
                    
                    const finalRange = document.createRange();
                    try {
                        finalRange.setStartAfter(spaceAfter);
                        finalRange.collapse(true);
                    } catch (e) {
                        const endTextNode = document.createTextNode(' ');
                        contentEditable.appendChild(endTextNode);
                        finalRange.setStartAfter(endTextNode);
                        finalRange.collapse(true);
                    }
                    
                    const finalSelection = window.getSelection();
                    finalSelection.removeAllRanges();
                    finalSelection.addRange(finalRange);
                    contentEditable.focus();
                    
                    setTimeout(() => {
                        const checkRange = finalSelection.getRangeAt(0);
                        let checkNode = checkRange.startContainer;
                        while (checkNode && checkNode !== contentEditable) {
                            if (checkNode === replacement) {
                                const parent = replacement.parentNode;
                                if (parent) {
                                    const newTextNode = document.createTextNode(' ');
                                    parent.insertBefore(newTextNode, replacement.nextSibling);
                                    const newRange = document.createRange();
                                    newRange.setStartAfter(newTextNode);
                                    newRange.collapse(true);
                                    finalSelection.removeAllRanges();
                                    finalSelection.addRange(newRange);
                                    contentEditable.focus();
                                }
                                break;
                            }
                            checkNode = checkNode.parentNode;
                        }
                    }, 50);
                }, 200);
                
                const noteId = contentEditable.getAttribute('data-note-id');
                if (noteId) {
                    dashboardUpdateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                }
            }
        }
        
        dashboardHideAutocomplete();
        contentEditable.focus();
    }
    
    function dashboardHideAutocomplete() {
        if (dashboardAutocompletePortal) {
            dashboardAutocompletePortal.style.display = 'none';
        }
        
        // Remove mouse tracking handler
        if (dashboardAutocompleteUpdateHandler) {
            document.removeEventListener('mousemove', dashboardAutocompleteUpdateHandler);
            dashboardAutocompleteUpdateHandler = null;
        }
        
        dashboardCurrentAutocompleteType = null;
        dashboardCurrentAutocompleteQuery = '';
        dashboardCurrentAutocompleteItems = [];
        dashboardSelectedAutocompleteIndex = -1;
        dashboardAutocompleteTextarea = null;
    }
    
    // Make functions global
    window.dashboardStartDrag = dashboardStartDrag;
    window.dashboardStartResize = dashboardStartResize;
    window.dashboardToggleColorPicker = dashboardToggleColorPicker;
    window.dashboardChangeNoteColor = dashboardChangeNoteColor;
    window.dashboardBringToFront = dashboardBringToFront;
    window.dashboardFitToSize = dashboardFitToSize;
    window.dashboardDeleteNote = dashboardDeleteNote;
    window.dashboardUpdateNoteTitle = dashboardUpdateNoteTitle;
    window.dashboardUpdateNoteContent = dashboardUpdateNoteContent;
    window.dashboardAddNote = dashboardAddNote;
    window.dashboardSelectAutocompleteItem = dashboardSelectAutocompleteItem;
});
</script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Wait for Chart.js to load
(function() {
    function initCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initCharts, 100);
            return;
        }

        // Chart.js Configuration for Dashboard
        const chartColors = {
            primary: '#007bff',
            success: '#28a745',
            danger: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8',
            secondary: '#6c757d'
        };

        // Get current theme colors dynamically
        function getCurrentThemeColors() {
            return {
                text: '#ffffff', // Always white for charts as requested
                muted: '#ffffff',
                grid: 'rgba(255, 255, 255, 0.15)', // Always white grid for dark chart background
                border: 'rgba(255, 255, 255, 0.3)', // Always white border
                background: '#1e293b' // Always dark background for charts
            };
        }

        // Chart.js default configuration
        if (Chart.defaults && Chart.defaults.font) {
            Chart.defaults.font.family = "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        }
        if (Chart.defaults) {
            Chart.defaults.color = '#ffffff'; // Always white for charts
        }

        // Make functions and variables global
        window.chartColors = chartColors;
        window.getCurrentThemeColors = getCurrentThemeColors;
        
        // Define chart functions
        window.getCommonOptions = function() {
            const themeColors = getCurrentThemeColors();
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: '#ffffff' // Always white for charts
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: themeColors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        titleFont: {
                            family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                            size: 12
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: themeColors.grid,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#ffffff', // Always white for charts
                            font: {
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: themeColors.grid,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#ffffff', // Always white for charts
                            font: {
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    }
                }
            };
        };

        window.getPieOptions = function() {
            const themeColors = getCurrentThemeColors();
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: '#ffffff' // Always white for charts
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: themeColors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        titleFont: {
                            family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            };
        };

        window.loadChartsData = function() {
            fetch('/api/dashboard-charts')
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        renderAppointmentsChart(data.data.trend);
                        renderAppointmentsPieChart(data.data.status);
                        renderAppointmentsStats(data.data.status);
                    } else {
                        console.error('Error loading charts data:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error loading charts data:', error);
                });
        };
        
        window.renderAppointmentsStats = function(statusData) {
            const total = statusData.total_appointments || 0;
            const completed = statusData.completed || 0;
            const missed = statusData.missed || 0;
            const ratio = statusData.completion_ratio || 0;
            
            document.getElementById('statsTotal').textContent = total;
            document.getElementById('statsCompleted').textContent = completed;
            document.getElementById('statsMissed').textContent = missed;
            document.getElementById('statsRatio').textContent = ratio + '%';
        };

        window.renderAppointmentsChart = function(trendData) {
            const ctx = document.getElementById('appointmentsChart');
            if (!ctx || !trendData || trendData.length === 0) return;
            
            const dates = trendData.map(item => item.date);
            const totalAppointments = trendData.map(item => item.total_appointments || 0);
            const completed = trendData.map(item => item.completed || 0);
            const missed = trendData.map(item => item.missed || 0);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric' 
                    })),
                    datasets: [
                        {
                            label: 'Total Appointments',
                            data: totalAppointments,
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Completed',
                            data: completed,
                            borderColor: chartColors.success,
                            backgroundColor: chartColors.success + '20',
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'Missed',
                            data: missed,
                            borderColor: '#ef4444',
                            backgroundColor: '#ef4444' + '20',
                            tension: 0.4,
                            fill: false
                        }
                    ]
                },
                options: getCommonOptions()
            });
        };

        window.renderAppointmentsPieChart = function(statusData) {
            const ctx = document.getElementById('appointmentsPieChart');
            if (!ctx || !statusData) return;
            
            const totalCompleted = statusData.completed || 0;
            const totalMissed = statusData.missed || 0;
            
            if (totalCompleted === 0 && totalMissed === 0) {
                ctx.parentElement.innerHTML = '<p class="text-muted text-center py-3">No data available</p>';
                return;
            }
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Missed'],
                    datasets: [{
                        data: [totalCompleted, totalMissed],
                        backgroundColor: [
                            chartColors.success,
                            '#ef4444'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: getPieOptions()
            });
        };
        
        // Load charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                loadChartsData();
            });
        } else {
            loadChartsData();
        }
    }
    
    initCharts();
})();
</script>
