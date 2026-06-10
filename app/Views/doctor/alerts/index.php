<link href="/app/Views/doctor/assets/css/alerts.css?v=<?= file_exists(__DIR__ . '/assets/css/alerts.css') ? filemtime(__DIR__ . '/assets/css/alerts.css') : time() ?>" rel="stylesheet">
<div class="container-fluid py-4 <?= !empty($alertsEmbedded) ? 'alerts-page--embedded' : '' ?>">
    <!-- Page header -->
    <div class="alerts-header mb-4">
        <div class="alerts-header-main">
            <span class="alerts-header-icon"><i class="bi bi-bell-fill"></i></span>
            <div class="alerts-header-text">
                <h2 class="alerts-header-title">Alerts Management</h2>
                <p class="alerts-header-sub">Manage your notifications and reminders</p>
            </div>
        </div>
        <button class="btn btn-primary alerts-create-btn" onclick="openAlertModal(null, null)">
            <i class="bi bi-plus-circle me-2"></i>Create New Alert
        </button>
    </div>

    <!-- Overview stat cards -->
    <div class="fin-stats-grid mb-4" id="alertsStatsGrid">
        <div class="fin-stat" style="--kpi-c: #6366F1;">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="alertsStatTotal">0</div>
                    <div class="fin-stat-label">Total Alerts</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-bell"></i></span>
            </div>
        </div>
        <div class="fin-stat" style="--kpi-c: #10B981;">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="alertsStatActive">0</div>
                    <div class="fin-stat-label">Active</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-bell-fill"></i></span>
            </div>
        </div>
        <div class="fin-stat" style="--kpi-c: #F59E0B;">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="alertsStatPastDue">0</div>
                    <div class="fin-stat-label">Past Due</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-alarm"></i></span>
            </div>
        </div>
        <div class="fin-stat" style="--kpi-c: #64748B;">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="alertsStatDismissed">0</div>
                    <div class="fin-stat-label">Dismissed</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-check2-circle"></i></span>
            </div>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="card alerts-list-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>All Alerts
                </h5>
                <button class="btn btn-sm btn-outline-warning" onclick="showDisableAllConfirmation()" title="Disable All Alerts">
                    <i class="bi bi-pause-circle me-1"></i>Disable All
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="showDeleteAllConfirmation()" title="Delete All Alerts">
                    <i class="bi bi-trash me-1"></i>Delete All
                </button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm" id="alertsPerPageSelect" style="width: auto;">
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="alertsListContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <nav aria-label="Alerts Pagination" id="alertsPaginationNav" style="display: none;">
                <ul class="pagination justify-content-center mb-0" id="alertsPaginationList">
                </ul>
            </nav>
        </div>
    </div>
</div>


<!-- Alerts Modals - Moved outside container for proper z-index and draggability -->
<?php include __DIR__ . '/../alert_modal.php'; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade alerts-modal-glass" id="deleteAlertModal" tabindex="-1" aria-labelledby="deleteAlertModalLabel" aria-hidden="true" style="z-index: 1000003 !important;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAlertModalLabel">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this alert?</p>
                <p class="text-muted mb-0"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDeleteAlert()">
                    <i class="bi bi-trash me-1"></i>Delete Alert
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Disable All Confirmation Modal -->
<div class="modal fade alerts-modal-glass" id="disableAllAlertsModal" tabindex="-1" aria-labelledby="disableAllAlertsModalLabel" aria-hidden="true" style="z-index: 1000003 !important;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disableAllAlertsModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirm Disable All
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to disable all alerts?</p>
                <p class="text-muted mb-0"><small>All alerts will be set to inactive. You can reactivate them individually later.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmDisableAllBtn" onclick="confirmDisableAllAlerts()">
                    <i class="bi bi-pause-circle me-1"></i>Disable All Alerts
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete All Confirmation Modal -->
<div class="modal fade alerts-modal-glass" id="deleteAllAlertsModal" tabindex="-1" aria-labelledby="deleteAllAlertsModalLabel" aria-hidden="true" style="z-index: 1000003 !important;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAllAlertsModalLabel">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm Delete All
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>ALL</strong> alerts?</p>
                <p class="text-muted mb-0"><small>This action cannot be undone. All alerts will be permanently deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAllBtn" onclick="confirmDeleteAllAlerts()">
                    <i class="bi bi-trash me-1"></i>Delete All Alerts
                </button>
            </div>
        </div>
    </div>
</div>

<script defer src="/app/Views/doctor/assets/js/alerts-page.js?v=<?= file_exists(__DIR__ . '/../assets/js/alerts-page.js') ? filemtime(__DIR__ . '/../assets/js/alerts-page.js') : time() ?>"></script>
