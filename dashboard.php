<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
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

    <div class="col-xl-3 col-md-6 mb-4">
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

    <div class="col-xl-3 col-md-6 mb-4">
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

    <div class="col-xl-3 col-md-6 mb-4">
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

    <div class="col-xl-3 col-md-6 mb-4">
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
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="/doctor/calendar" class="btn btn-outline-primary quick-action-btn w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-calendar3 fa-2x mb-2"></i>
                            <span>View Calendar</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/doctor/patients" class="btn btn-outline-success quick-action-btn w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-people fa-2x mb-2"></i>
                            <span>Patient List</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/doctor/profile" class="btn btn-outline-info quick-action-btn w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-person-circle fa-2x mb-2"></i>
                            <span>My Profile</span>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/doctor/reports" class="btn btn-outline-warning quick-action-btn w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-graph-up fa-2x mb-2"></i>
                            <span>Reports</span>
                        </a>
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
                    <div class="col-lg-8 mb-4">
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
                    <div class="col-lg-4 mb-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Status</h6>
                            </div>
                            <div class="card-body chart-container">
                                <canvas id="appointmentsPieChart" height="300"></canvas>
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
}

.btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
}

.btn-outline-warning:hover {
    background-color: #f6c23e;
    border-color: #f6c23e;
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
    color: #0b1220;
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
    border-radius: 12px !important;
    border-width: 2px !important;
    transition: all 0.3s ease !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    position: relative;
    overflow: hidden;
    min-height: 120px;
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

/* Ensure chart text is always white */
canvas {
    filter: brightness(1);
}

.dark canvas {
    filter: brightness(1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Function to mark missed appointment as completed
    window.markMissedAppointmentCompleted = function(appointmentId) {
        if (!confirm('Are you sure you want to mark this appointment as completed?')) {
            return;
        }
        
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
            // Reload page to refresh data
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
                    } else {
                        console.error('Error loading charts data:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error loading charts data:', error);
                });
        };

        window.renderAppointmentsChart = function(trendData) {
            const ctx = document.getElementById('appointmentsChart');
            if (!ctx || !trendData || trendData.length === 0) return;
            
            const dates = trendData.map(item => item.date);
            const totalAppointments = trendData.map(item => item.total_appointments || 0);
            const completed = trendData.map(item => item.completed || 0);
            const cancelled = trendData.map(item => item.cancelled || 0);
            const noShow = trendData.map(item => item.no_show || 0);
            
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
                            label: 'Cancelled',
                            data: cancelled,
                            borderColor: chartColors.danger,
                            backgroundColor: chartColors.danger + '20',
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'No Show',
                            data: noShow,
                            borderColor: chartColors.warning,
                            backgroundColor: chartColors.warning + '20',
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
            const totalCancelled = statusData.cancelled || 0;
            const totalNoShow = statusData.no_show || 0;
            
            if (totalCompleted === 0 && totalCancelled === 0 && totalNoShow === 0) {
                ctx.parentElement.innerHTML = '<p class="text-muted text-center py-3">No data available</p>';
                return;
            }
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Cancelled', 'No Show'],
                    datasets: [{
                        data: [totalCompleted, totalCancelled, totalNoShow],
                        backgroundColor: [
                            chartColors.success,
                            chartColors.danger,
                            chartColors.warning
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
