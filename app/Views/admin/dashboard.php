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
                                <div class="activity-icon">
                                    <i class="fas fa-<?= $activity['action_type'] === 'login' ? 'sign-in' : 'edit' ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <h6 class="mb-1"><?= htmlspecialchars($activity['user_name']) ?></h6>
                                    <p class="mb-1 text-muted"><?= htmlspecialchars($activity['description']) ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= date('Y-m-d H:i', strtotime($activity['created_at'])) ?>
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
    <div class="col-md-4">
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

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="/admin/users" class="btn btn-outline-primary w-100">
                            <i class="fas fa-users me-2"></i>
                            Users Management
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="/admin/reports" class="btn btn-outline-success w-100">
                            <i class="fas fa-chart-line me-2"></i>
                            Reports
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="/admin/backup" class="btn btn-outline-warning w-100">
                            <i class="fas fa-download me-2"></i>
                            Backup
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="/admin/settings" class="btn btn-outline-info w-100">
                            <i class="fas fa-cog me-2"></i>
                            Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dark mode variables */
:root {
    --bg: #1a1a1a;
    --bg-alt: #2d2d2d;
    --bg-dark: #1e1e1e;
    --text: #ffffff;
    --text-muted: #b0b0b0;
    --accent: #0d6efd;
    --accent-rgb: 13, 110, 253;
    --border: #404040;
    --muted: #6c757d;
}

[data-bs-theme="light"] {
    --bg: #ffffff;
    --bg-alt: #f8f9fa;
    --bg-dark: #ffffff;
    --text: #212529;
    --text-muted: #6c757d;
    --accent: #0d6efd;
    --accent-rgb: 13, 110, 253;
    --border: #dee2e6;
    --muted: #6c757d;
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
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
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

.stat-card {
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.8;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-content p {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.stat-content small {
    font-size: 0.9rem;
    opacity: 0.8;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: var(--bg-alt);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: var(--muted);
}

.activity-content {
    flex: 1;
}

.health-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
}

.health-item:last-child {
    border-bottom: none;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.text-muted {
    color: var(--muted) !important;
}

.btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-outline-success {
    color: #28a745;
    border-color: #28a745;
}

.btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-outline-warning {
    color: #ffc107;
    border-color: #ffc107;
}

.btn-outline-warning:hover {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-outline-info {
    color: #17a2b8;
    border-color: #17a2b8;
}

.btn-outline-info:hover {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.btn-outline-secondary {
    color: var(--muted);
    border-color: var(--border);
}

.btn-outline-secondary:hover {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

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

.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

.progress {
    background-color: var(--bg-alt);
}

.progress-bar {
    background-color: var(--accent);
}
</style>
