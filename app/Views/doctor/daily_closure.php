<?php
$title = 'Roaya Clinic - Daily Closure';
$pageTitle = 'Daily Closure';
$pageSubtitle = 'Review and close daily operations for ' . date('F j, Y', strtotime($today));
?>

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

/* Table Styles */
.table {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.table th {
    background-color: var(--bg) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table td {
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table-dark th {
    background-color: #1e293b !important;
    color: var(--text) !important;
}

.table-light th {
    background-color: var(--bg) !important;
    color: var(--text) !important;
}

/* Form Controls */
.form-control, .form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.form-control:focus, .form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25) !important;
}

.form-control::placeholder {
    color: var(--muted) !important;
}

.form-label {
    color: var(--text) !important;
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

.btn-outline-danger {
    color: var(--danger) !important;
    border-color: var(--danger) !important;
}

/* Dark Mode Button Hover States */
.dark .btn-outline-primary:hover {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: #0b1220 !important;
}

.dark .btn-outline-success:hover {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: #0b1220 !important;
}

.dark .btn-outline-info:hover {
    background-color: #36b9cc !important;
    border-color: #36b9cc !important;
    color: #0b1220 !important;
}

.dark .btn-outline-warning:hover {
    background-color: #f6c23e !important;
    border-color: #f6c23e !important;
    color: #0b1220 !important;
}

.dark .btn-outline-danger:hover {
    background-color: var(--danger) !important;
    border-color: var(--danger) !important;
    color: #0b1220 !important;
}

/* Badge Styles */
.badge {
    color: white !important;
}

.dark .badge {
    color: var(--text) !important;
}

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

.badge-danger {
    background-color: var(--danger) !important;
    color: white !important;
}

.badge-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

/* Text Colors */
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

.text-danger {
    color: var(--danger) !important;
}

.text-secondary {
    color: #6c757d !important;
}

/* Border Colors */
.border-primary {
    border-color: var(--accent) !important;
}

.border-success {
    border-color: var(--success) !important;
}

.border-danger {
    border-color: var(--danger) !important;
}

.border-info {
    border-color: #36b9cc !important;
}

/* Alert Styles */
.alert {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1) !important;
    border-color: var(--success) !important;
    color: var(--success) !important;
}

.alert-danger {
    background-color: rgba(239, 68, 68, 0.1) !important;
    border-color: var(--danger) !important;
    color: var(--danger) !important;
}

.alert-warning {
    background-color: rgba(246, 194, 62, 0.1) !important;
    border-color: #f6c23e !important;
    color: #f6c23e !important;
}

.alert-info {
    background-color: rgba(54, 185, 204, 0.1) !important;
    border-color: #36b9cc !important;
    color: #36b9cc !important;
}

/* Modal Styles */
.modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.modal-header {
    background-color: var(--card) !important;
    border-bottom-color: var(--border) !important;
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


.card {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

h6, h5, h4, h3, h2, h1 {
    color: var(--text) !important;

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

.modal-footer {
    background-color: var(--card) !important;
    border-top-color: var(--border) !important;
}

/* Search Modal Styles */
.modal-content {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.modal-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

/* Input Group */
.input-group-text {
    background-color: var(--bg) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

/* Keyboard Shortcuts */
kbd {
    background-color: var(--bg) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem !important;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    .table-responsive {
        font-size: 0.875rem !important;
    }
}
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-1">Daily Closure</h2>
            <p class="text-muted mb-0">Review and close daily operations for <?= date('F j, Y', strtotime($today)) ?></p>
        </div>
        <div class="col-md-6 text-end">
            <?php if (!$isClosed): ?>
            <button class="btn btn-success btn-lg" 
                    onclick="closeDay()" 
                    id="closeDayBtn"
                    title="Close the day">
                <i class="bi bi-check-circle me-2"></i>
                Close Day
            </button>
            <?php else: ?>
            <div class="alert alert-info d-inline-block">
                <i class="bi bi-info-circle me-2"></i>
                Day is already closed
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Daily Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-wallet2" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="text-primary mb-1"><?= number_format($dailySummary['opening_balance'], 2) ?> EGP</h4>
                    <p class="text-muted mb-0">Opening Balance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-arrow-down-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="text-success mb-1"><?= number_format($dailySummary['total_payments'], 2) ?> EGP</h4>
                    <p class="text-muted mb-0">Total Payments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="bi bi-arrow-up-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="text-danger mb-1"><?= number_format($dailySummary['total_expenses'], 2) ?> EGP</h4>
                    <p class="text-muted mb-0">Total Expenses</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-calculator" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="text-info mb-1"><?= number_format($dailySummary['net_amount'], 2) ?> EGP</h4>
                    <p class="text-muted mb-0">Net Amount</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Opening Balance Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-wallet2 me-2"></i>
                Daily Balance Summary
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary bg-opacity-10 rounded">
                        <div>
                            <h6 class="mb-1">Opening Balance</h6>
                            <small class="text-muted">Starting amount for the day</small>
                        </div>
                        <div class="text-end">
                            <h4 class="text-primary mb-0"><?= number_format($dailySummary['opening_balance'], 2) ?> EGP</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-success bg-opacity-10 rounded">
                        <div>
                            <h6 class="mb-1">Additional Balance</h6>
                            <small class="text-muted">Additional amounts added during the day</small>
                        </div>
                        <div class="text-end">
                            <h4 class="text-info mb-0"><?= number_format($dailySummary['additional_balance'] ?? 0, 2) ?> EGP</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-warning bg-opacity-10 rounded">
                        <div>
                            <h6 class="mb-1">Withdrawals</h6>
                            <small class="text-muted">Amounts withdrawn during the day</small>
                        </div>
                        <div class="text-end">
                            <h4 class="text-warning mb-0"><?= number_format($dailySummary['total_withdrawals'] ?? 0, 2) ?> EGP</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-credit-card me-2"></i>
                Payments Received
                <span class="badge bg-success ms-2"><?= count($dailySummary['payments']) ?> transactions</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($dailySummary['payments'])): ?>
            <div class="text-center py-4">
                <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No payments received today</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailySummary['payments'] as $payment): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($payment['created_at'])) ?></td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($payment['patient_name'] ?? 'N/A') ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $viewHelper->getPaymentTypeBadgeClass($payment['type']) ?>">
                                    <?= $viewHelper->getPaymentTypeText($payment['type']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $viewHelper->getPaymentMethodBadgeClass($payment['method']) ?>">
                                    <?= $viewHelper->getPaymentMethodText($payment['method']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-success"><?= number_format($payment['amount'], 2) ?> EGP</strong>
                            </td>
                            <td><?= htmlspecialchars($payment['description'] ?? 'N/A') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total Payments:</th>
                            <th class="text-success"><?= number_format($dailySummary['total_payments'], 2) ?> EGP</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Withdrawals Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-arrow-up-circle me-2"></i>
                Withdrawals
                <span class="badge bg-warning ms-2"><?= count($dailySummary['withdrawals'] ?? []) ?> transactions</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($dailySummary['withdrawals'])): ?>
            <div class="text-center py-4">
                <i class="bi bi-arrow-up-circle text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No withdrawals recorded today</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailySummary['withdrawals'] as $withdrawal): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($withdrawal['created_at'])) ?></td>
                            <td>
                                <strong class="text-warning"><?= number_format($withdrawal['amount'], 2) ?> EGP</strong>
                            </td>
                            <td><?= htmlspecialchars($withdrawal['description'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($withdrawal['created_by_name'] ?? 'N/A') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="1" class="text-end">Total Withdrawals:</th>
                            <th class="text-warning"><?= number_format($dailySummary['total_withdrawals'] ?? 0, 2) ?> EGP</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Expenses Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-dash-circle me-2"></i>
                Expenses
                <span class="badge bg-danger ms-2"><?= count($dailySummary['expenses']) ?> transactions</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($dailySummary['expenses'])): ?>
            <div class="text-center py-4">
                <i class="bi bi-dash-circle text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No expenses recorded today</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Expense Name</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Notes</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailySummary['expenses'] as $expense): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($expense['created_at'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($expense['expense_name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= ucfirst($expense['category']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-danger"><?= number_format($expense['amount'], 2) ?> EGP</strong>
                            </td>
                            <td><?= htmlspecialchars($expense['notes'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($expense['created_by_name'] ?? 'N/A') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total Expenses:</th>
                            <th class="text-danger"><?= number_format($dailySummary['total_expenses'], 2) ?> EGP</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Net Amount Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-calculator me-2"></i>
                Daily Summary
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-success bg-opacity-10 rounded">
                        <div>
                            <h6 class="mb-1 text-success">Total Income</h6>
                            <small class="text-muted">Opening Balance + Payments</small>
                        </div>
                        <div class="text-end">
                            <h4 class="text-success mb-0"><?= number_format($dailySummary['opening_balance'] + $dailySummary['total_payments'], 2) ?> EGP</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-danger bg-opacity-10 rounded">
                        <div>
                            <h6 class="mb-1 text-danger">Total Expenses</h6>
                            <small class="text-muted">All expenses for the day</small>
                        </div>
                        <div class="text-end">
                            <h4 class="text-danger mb-0"><?= number_format($dailySummary['total_expenses'], 2) ?> EGP</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center p-4 bg-info bg-opacity-10 rounded">
                        <div>
                            <h5 class="mb-1 text-info">Net Amount</h5>
                            <small class="text-muted">Final balance for the day</small>
                        </div>
                        <div class="text-end">
                            <h3 class="text-info mb-0"><?= number_format($dailySummary['net_amount'], 2) ?> EGP</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Close Day Confirmation Modal -->
<div class="modal fade" id="closeDayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>
                    Close Day Confirmation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Once you close the day, no more transactions can be added for today.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-success">Total Income</h6>
                            <h4 class="text-success"><?= number_format($dailySummary['opening_balance'] + $dailySummary['total_payments'], 2) ?> EGP</h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-danger">Total Expenses</h6>
                            <h4 class="text-danger"><?= number_format($dailySummary['total_expenses'], 2) ?> EGP</h4>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <h5 class="text-info">Net Amount: <?= number_format($dailySummary['net_amount'], 2) ?> EGP</h5>
                </div>
                
                <div class="mt-3">
                    <label for="closureNotes" class="form-label">Closure Notes (Optional)</label>
                    <textarea class="form-control" id="closureNotes" rows="3" placeholder="Add any notes about the day's closure..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmCloseDay()">
                    <i class="bi bi-check-circle me-1"></i>
                    Close Day
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function closeDay() {
    const modal = new bootstrap.Modal(document.getElementById('closeDayModal'));
    modal.show();
}

function confirmCloseDay() {
    const notes = document.getElementById('closureNotes').value;
    
    // Show loading state
    const btn = document.querySelector('#closeDayModal .btn-success');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Closing...';
    btn.disabled = true;
    
    fetch('/api/daily-closure', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Success
            bootstrap.Modal.getInstance(document.getElementById('closeDayModal')).hide();
            
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                Day closed successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
            
            // Update UI
            document.getElementById('closeDayBtn').style.display = 'none';
            const closedAlert = document.createElement('div');
            closedAlert.className = 'alert alert-info d-inline-block';
            closedAlert.innerHTML = '<i class="bi bi-info-circle me-2"></i>Day is already closed';
            document.querySelector('.col-md-6.text-end').appendChild(closedAlert);
            
            // Reload page after delay
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            // Error
            alert('Error closing day: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error closing day: ' + error.message);
    })
    .finally(() => {
        // Reset button
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    const isModalOpen = document.querySelector('.modal.show');
    const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                         e.target.contentEditable === 'true';
    
    // Close day with 'C' key
    if (e.key.toLowerCase() === 'c' && !isInputFocused && !isModalOpen && !<?= $isClosed ? 'true' : 'false' ?>) {
        e.preventDefault();
        closeDay();
    }
    
    // Close modals with 'Escape' key
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            bootstrap.Modal.getInstance(openModal).hide();
        }
    }
});
</script>
