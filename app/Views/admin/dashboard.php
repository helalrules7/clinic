<?php
/**
 * Admin Dashboard Template
 * لوحة تحكم الإدارة
 */
?>

<div class="row">
    <!-- System Statistics -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    System Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Users Statistics -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card bg-primary text-white">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= number_format($stats['users']['total_users'] ?? 0) ?></h3>
                                <p>Total Users</p>
                                <small>
                                    <i class="fas fa-user-md me-1"></i>
                                    <?= number_format($stats['users']['doctors'] ?? 0) ?> Doctors
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-user-tie me-1"></i>
                                    <?= number_format($stats['users']['secretaries'] ?? 0) ?> Secretaries
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Patients Statistics -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card bg-success text-white">
                            <div class="stat-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= number_format($stats['patients']['total_patients'] ?? 0) ?></h3>
                                <p>Total Patients</p>
                                <small>
                                    <i class="fas fa-check-circle me-1"></i>
                                    <?= number_format($stats['users']['active_users'] ?? 0) ?> Active
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Statistics -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card bg-info text-white">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= number_format($stats['appointments']['total_appointments'] ?? 0) ?></h3>
                                <p>Appointments (30 days)</p>
                                <small>
                                    <i class="fas fa-check me-1"></i>
                                    <?= number_format($stats['appointments']['completed'] ?? 0) ?> Completed
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-times me-1"></i>
                                    <?= number_format($stats['appointments']['cancelled'] ?? 0) ?> Cancelled
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Statistics -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card bg-warning text-white">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= number_format($stats['financial']['total_revenue'] ?? 0, 2) ?> EGP</h3>
                                <p>Revenue (30 days)</p>
                                <small>
                                    <i class="fas fa-receipt me-1"></i>
                                    <?= number_format($stats['financial']['total_payments'] ?? 0) ?> Transaction
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-percentage me-1"></i>
                                    <?= number_format($stats['financial']['total_discounts'] ?? 0, 2) ?> Discounts
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<br><br>

<div class="row">
    <!-- Recent Activities -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Recent Activities
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)): ?>
                    <div class="activity-list">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <?php
                                    // audit_logs columns: action, entity_table, reason (no action_type/description).
                                    $actAction = (string)($activity['action'] ?? '');
                                    $actEntity = (string)($activity['entity_table'] ?? '');
                                    $actDesc   = trim(ucfirst($actAction) . ($actEntity !== '' ? ' — ' . str_replace('_', ' ', $actEntity) : ''));
                                    if ($actDesc === '') { $actDesc = (string)($activity['reason'] ?? ''); }
                                    $actUser   = (string)($activity['user_name'] ?? 'System');
                                    $actWhen   = $activity['created_at'] ?? null;
                                ?>
                                <div class="activity-icon">
                                    <i class="fas fa-<?= stripos($actAction, 'login') !== false ? 'sign-in' : 'edit' ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <h6 class="mb-1"><?= htmlspecialchars($actUser) ?></h6>
                                    <p class="mb-1 text-muted"><?= htmlspecialchars($actDesc) ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= $actWhen ? date('Y-m-d H:i', strtotime((string)$actWhen)) : '' ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent activities</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="col-md-4" id="systemHealth">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-heartbeat me-2"></i>
                    System Health
                </h5>
            </div>
            <div class="card-body">
                <!-- Database Status -->
                <div class="health-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-database me-2"></i>
                            Database
                        </span>
                        <span class="badge bg-<?= $systemHealth['database'] === 'Connected' ? 'success' : 'danger' ?>">
                            <?= $systemHealth['database'] === 'Connected' ? 'Connected' : 'Error' ?>
                        </span>
                    </div>
                </div>

                <!-- Storage Status -->
                <div class="health-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>
                            <i class="fas fa-hdd me-2"></i>
                            Storage Space
                        </span>
                        <span class="badge bg-<?= $systemHealth['storage']['usage_percent'] < 80 ? 'success' : ($systemHealth['storage']['usage_percent'] < 90 ? 'warning' : 'danger') ?>">
                            <?= $systemHealth['storage']['usage_percent'] ?>%
                        </span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-<?= $systemHealth['storage']['usage_percent'] < 80 ? 'success' : ($systemHealth['storage']['usage_percent'] < 90 ? 'warning' : 'danger') ?>" 
                             style="width: <?= $systemHealth['storage']['usage_percent'] ?>%"></div>
                    </div>
                    <small class="text-muted">
                        <?= $systemHealth['storage']['used'] ?> / <?= $systemHealth['storage']['total'] ?>
                    </small>
                </div>

                <!-- PHP Version -->
                <div class="health-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fab fa-php me-2"></i>
                            PHP Version
                        </span>
                        <span class="badge bg-info"><?= $systemHealth['php_version'] ?></span>
                    </div>
                </div>

                <!-- Extensions Status -->
                <div class="health-item">
                    <h6 class="mb-2">Required Extensions:</h6>
                    <?php foreach ($systemHealth['extensions'] as $ext => $status): ?>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small><?= $ext ?></small>
                            <span class="badge bg-<?= $status === 'Loaded' ? 'success' : 'danger' ?> badge-sm">
                                <?= $status === 'Loaded' ? 'Loaded' : 'Missing' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View As Controls (Admin Only) -->
<?php if ($viewAsStatus['isAdmin']): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-eye me-2"></i>
                    View As - Role Interface Preview
                </h5>
            </div>
            <div class="card-body">
                <?php if ($viewAsStatus['isViewAsMode']): ?>
                    <!-- Currently in View As mode -->
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Active Preview Mode:</strong> 
                            You are currently viewing as <strong><?= ucfirst($viewAsStatus['currentRole']) ?></strong>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/admin/stop-view-as" class="btn btn-warning">
                            <i class="fas fa-arrow-left me-2"></i>
                            Return to Admin
                        </a>
                        <span class="btn btn-outline-secondary disabled">
                            <i class="fas fa-user-shield me-2"></i>
                            <?= ucfirst($viewAsStatus['originalRole']) ?> (Original)
                        </span>
                    </div>
                <?php else: ?>
                    <!-- Not in View As mode - show options -->
                    <p class="text-muted mb-3">
                        Use this tool to preview each role's interface as regular users see it
                    </p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                                    <h5>Doctor Interface</h5>
                                    <p class="text-muted small">Preview doctor dashboard and available functions</p>
                                    <a href="/admin/view-as?role=doctor" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>
                                        Preview Doctor Interface
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                                    <h5>Secretary Interface</h5>
                                    <p class="text-muted small">Preview secretary dashboard and available functions</p>
                                    <a href="/admin/view-as?role=secretary" class="btn btn-success">
                                        <i class="fas fa-eye me-2"></i>
                                        Preview Secretary Interface
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<style>
/* CSS Variables for Dark/Light Mode - Matching Doctor View */
:root {
    --warning: #f59e0b;
    --info: #06b6d4;
    --shadow: rgba(0, 0, 0, 0.1);
}

.dark {
    --warning: #fbbf24;
    --info: #22d3ee;
    --shadow: rgba(0, 0, 0, 0.3);
}

/* Card Styles */
.card {
    background-color: var(--card) !important;
    border: 1px solid var(--border) !important;
    border-radius: 12px;
    box-shadow: 0 4px 6px var(--shadow) !important;
    color: var(--text);
    transition: all 0.2s ease;
}

.card:hover {
    box-shadow: 0 8px 25px var(--shadow) !important;
    transform: translateY(-2px);
}

.card-header {
    background-color: transparent !important;
    border-bottom: 1px solid var(--border) !important;
    color: var(--text);
    padding: 1rem 1.5rem;
}

.card-body {
    background-color: transparent !important;
    padding: 1.5rem;
}

.card-title {
    color: var(--text) !important;
    font-weight: 600;
}

/* Stat Cards */
.stat-card {
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px var(--shadow);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px var(--shadow);
}

.stat-card.bg-primary {
    background: linear-gradient(135deg, var(--accent), #0284c7) !important;
}

.stat-card.bg-success {
    background: linear-gradient(135deg, var(--success), #059669) !important;
}

.stat-card.bg-info {
    background: linear-gradient(135deg, var(--info), #0891b2) !important;
}

.stat-card.bg-warning {
    background: linear-gradient(135deg, var(--warning), #d97706) !important;
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.stat-content h3 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-content p {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    opacity: 0.95;
}

.stat-content small {
    font-size: 0.85rem;
    opacity: 0.85;
}

/* Activity List */
.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-list::-webkit-scrollbar {
    width: 6px;
}

.activity-list::-webkit-scrollbar-track {
    background: var(--bg-alt);
    border-radius: 3px;
}

.activity-list::-webkit-scrollbar-thumb {
    background: var(--muted);
    border-radius: 3px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s ease;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: var(--bg-alt);
    margin: 0 -1rem;
    padding: 1rem;
    border-radius: 8px;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--accent), var(--success));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-content h6 {
    color: var(--text) !important;
    font-weight: 600;
}

.activity-content p {
    color: var(--muted) !important;
}

/* Health Items */
.health-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
}

.health-item:last-child {
    border-bottom: none;
}

.health-item span {
    color: var(--text);
}

.health-item h6 {
    color: var(--text) !important;
}

.health-item small {
    color: var(--text);
}

/* Progress Bar */
.progress {
    background-color: var(--bg-alt);
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
}

/* Badges */
.badge {
    font-weight: 500;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.badge.bg-primary {
    background-color: var(--accent) !important;
}

.badge.bg-success {
    background-color: var(--success) !important;
}

.badge.bg-info {
    background-color: var(--info) !important;
}

.badge.bg-warning {
    background-color: var(--warning) !important;
    color: #1e293b !important;
}

.badge.bg-danger {
    background-color: var(--danger) !important;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
}

/* Text Colors */
.text-muted {
    color: var(--muted) !important;
}

.text-primary {
    color: var(--accent) !important;
}

.text-success {
    color: var(--success) !important;
}

.text-danger {
    color: var(--danger) !important;
}

.text-warning {
    color: var(--warning) !important;
}

.text-info {
    color: var(--info) !important;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
}

.btn-primary:hover {
    background-color: #0284c7 !important;
    border-color: #0284c7 !important;
}

.btn-success {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
}

.btn-success:hover {
    background-color: #059669 !important;
    border-color: #059669 !important;
}

.btn-warning {
    background-color: var(--warning) !important;
    border-color: var(--warning) !important;
    color: #1e293b !important;
}

.btn-warning:hover {
    background-color: #d97706 !important;
    border-color: #d97706 !important;
}

.btn-outline-primary {
    color: var(--accent) !important;
    border-color: var(--accent) !important;
    background: transparent !important;
}

.btn-outline-primary:hover {
    background-color: var(--accent) !important;
    color: white !important;
}

.btn-outline-success {
    color: var(--success) !important;
    border-color: var(--success) !important;
    background: transparent !important;
}

.btn-outline-success:hover {
    background-color: var(--success) !important;
    color: white !important;
}

.btn-outline-warning {
    color: var(--warning) !important;
    border-color: var(--warning) !important;
    background: transparent !important;
}

.btn-outline-warning:hover {
    background-color: var(--warning) !important;
    color: #1e293b !important;
}

.btn-outline-info {
    color: var(--info) !important;
    border-color: var(--info) !important;
    background: transparent !important;
}

.btn-outline-info:hover {
    background-color: var(--info) !important;
    color: white !important;
}

.btn-outline-secondary {
    color: var(--muted) !important;
    border-color: var(--border) !important;
    background: transparent !important;
}

.btn-outline-secondary:hover {
    background-color: var(--bg-alt) !important;
    color: var(--text) !important;
}

.btn-secondary {
    background-color: var(--bg-alt) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.btn-secondary:hover {
    background-color: var(--border) !important;
}

/* Alerts */
.alert {
    border-radius: 10px;
    border: none;
}

.alert-warning {
    background-color: rgba(245, 158, 11, 0.15) !important;
    color: var(--warning) !important;
    border: 1px solid var(--warning) !important;
}

.dark .alert-warning {
    background-color: rgba(251, 191, 36, 0.15) !important;
}

/* Tables */
.table {
    color: var(--text);
}

.table thead th {
    background-color: var(--bg-alt) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
    font-weight: 600;
}

.table tbody tr {
    border-color: var(--border);
}

.table tbody tr:hover {
    background-color: var(--bg-alt);
}

.table td {
    border-color: var(--border);
    color: var(--text);
    vertical-align: middle;
}

/* View As Section */
.card.border-warning {
    border-color: var(--warning) !important;
}

.card.border-primary {
    border-color: var(--accent) !important;
}

.card.border-success {
    border-color: var(--success) !important;
}

.card-header.bg-warning {
    background: linear-gradient(135deg, var(--warning), #d97706) !important;
    color: #1e293b !important;
}

/* Form Controls */
.form-control,
.form-select {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
    border-radius: 8px;
}

.form-control:focus,
.form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.form-control::placeholder {
    color: var(--muted);
}

/* Modals */
.modal-content {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
    border-radius: 12px;
}

.modal-header {
    border-bottom-color: var(--border);
}

.modal-footer {
    border-top-color: var(--border);
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--bg-alt);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--muted);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--accent);
}

@media (max-width: 768px) {
    #systemHealth {
        margin-top: 20px !important;
    }
}
</style>

