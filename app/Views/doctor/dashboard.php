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
                        <a href="/admin/reports" class="btn btn-outline-warning quick-action-btn w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
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
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-calendar-event me-2"></i>
                    Upcoming Appointments
                </h6>
                <a href="/doctor/calendar" class="btn btn-sm btn-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingAppointments)): ?>
                    <p class="text-muted text-center py-3">No upcoming appointments</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcomingAppointments as $appointment): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <div>
                                    <h6 class="mb-1">
                                        <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= $this->formatTime($appointment['start_time']) ?> - 
                                        <?= $this->formatTime($appointment['end_time']) ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= $this->formatDate($appointment['date']) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="<?= $this->getStatusBadgeClass($appointment['status']) ?>">
                                        <?= $appointment['status'] ?>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <?= $appointment['visit_type'] ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Timeline Events -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-activity me-2"></i>
                    Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($recentEvents)): ?>
                    <p class="text-muted text-center py-3">No recent activity</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($recentEvents as $event): ?>
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-marker me-3">
                                        <div class="bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($event['event_summary']) ?></h6>
                                        <p class="mb-1 text-muted">
                                            <i class="bi bi-person me-1"></i>
                                            <?= htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= $this->formatDate($event['created_at'], 'd/m/Y H:i') ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
}
</style>
