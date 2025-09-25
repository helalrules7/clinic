<?php
$title = 'Roaya Clinic - Financial Management';
$pageTitle = 'Financial Management';
$pageSubtitle = 'Manage payments, expenses, and daily operations';
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

/* Pagination */
.pagination .page-link {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.pagination .page-link:hover {
    background-color: var(--bg) !important;
    border-color: var(--accent) !important;
    color: var(--accent) !important;
}

.pagination .page-item.active .page-link {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: white !important;
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

/* Modal Styles */
.modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.modal-header {
    background-color: var(--card) !important;
    border-bottom-color: var(--border) !important;
}

.modal-footer {
    background-color: var(--card) !important;
    border-top-color: var(--border) !important;
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

/* Expense Type Badges */
.expense-type-badge {
    transition: all 0.3s ease !important;
    cursor: pointer !important;
}

.expense-type-badge:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

/* Dark Mode Specific */
.dark .expense-type-badge {
    background-color: var(--bg) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

.dark .expense-type-badge:hover {
    background-color: var(--accent) !important;
    color: #0b1220 !important;
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
        </div>
        <div class="col-md-6 text-end">
            <div class="d-flex gap-2 justify-content-end">
                <button class="btn btn-primary" 
                        data-bs-toggle="modal" 
                        data-bs-target="#dailyBalanceModal" 
                        title="Add Daily Balance">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Balance
                    <span class="ms-2">
                        <kbd>B</kbd>
                    </span>
                </button>
                <button class="btn btn-warning" 
                        data-bs-toggle="modal" 
                        data-bs-target="#expenseModal" 
                        title="Add Expense">
                    <i class="bi bi-dash-circle me-2"></i>
                    Add Expense
                    <span class="ms-2">
                        <kbd>E</kbd>
                    </span>
                </button>
                <button class="btn btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#searchModal" 
                        title="Search Transactions">
                    <i class="bi bi-search me-2"></i>
                    Search
                    <span class="ms-2">
                        <kbd>S</kbd>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Daily Balance Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-wallet2" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="text-primary mb-1" id="openingBalance"><?= number_format($dailyBalance['opening_balance'], 2) ?> EGP</h4>
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
                    <h4 class="text-success mb-1" id="totalReceived"><?= number_format($dailyBalance['total_received'], 2) ?> EGP</h4>
                    <p class="text-muted mb-0">Total Received</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="bi bi-arrow-up-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="text-danger mb-1" id="totalExpenses"><?= number_format($dailyBalance['total_expenses'], 2) ?> EGP</h4>
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
                    <h4 class="text-info mb-1" id="currentBalance"><?= number_format($dailyBalance['current_balance'], 2) ?> EGP</h4>
                    <p class="text-muted mb-0">Current Balance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Types Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        Payment Types Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="badge bg-primary fs-6 mb-2">New Booking</div>
                                <h4 id="BookingCount"><?= $paymentTypes['Booking']['count'] ?? 0 ?></h4>
                                <small class="text-muted"><?= number_format($paymentTypes['Booking']['total'] ?? 0, 2) ?> EGP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="badge bg-success fs-6 mb-2">Follow-up</div>
                                <h4 id="FollowUpCount"><?= $paymentTypes['FollowUp']['count'] ?? 0 ?></h4>
                                <small class="text-muted"><?= number_format($paymentTypes['FollowUp']['total'] ?? 0, 2) ?> EGP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="badge bg-info fs-6 mb-2">Consultation</div>
                                <h4 id="ConsultationCount"><?= $paymentTypes['Consultation']['count'] ?? 0 ?></h4>
                                <small class="text-muted"><?= number_format($paymentTypes['Consultation']['total'] ?? 0, 2) ?> EGP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="badge bg-warning fs-6 mb-2">Procedure</div>
                                <h4 id="ProcedureCount"><?= $paymentTypes['Procedure']['count'] ?? 0 ?></h4>
                                <small class="text-muted"><?= number_format($paymentTypes['Procedure']['total'] ?? 0, 2) ?> EGP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="badge bg-secondary fs-6 mb-2">Other</div>
                                <h4 id="OtherCount"><?= $paymentTypes['Other']['count'] ?? 0 ?></h4>
                                <small class="text-muted"><?= number_format($paymentTypes['Other']['total'] ?? 0, 2) ?> EGP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="badge bg-warning fs-6 mb-2">Withdrawals</div>
                                <h4 id="withdrawalsCount"><?= $dailyBalance['withdrawals_count'] ?? 0 ?></h4>
                                <small class="text-muted"><?= number_format($dailyBalance['total_withdrawals'] ?? 0, 2) ?> EGP</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="badge bg-danger fs-6 mb-2">Expenses</div>
                                <h4><?= count($expenses) ?></h4>
                                <small class="text-muted"><?= number_format(array_sum(array_column($expenses, 'amount')), 2) ?> EGP</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Transactions Log -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="bi bi-journal-text me-2"></i>
                        Financial Transactions Log
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <!-- Export to Excel -->
                        <button class="btn btn-success btn-sm" onclick="exportToExcel()" title="Export to Excel">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Export Excel
                        </button>
                        <!-- Date Filter -->
                        <div class="d-flex align-items-center">
                            <label for="dateFilter" class="form-label mb-0 me-2 text-muted">Date:</label>
                            <input type="date" class="form-control form-control-sm" id="dateFilter" style="width: auto;">
                        </div>
                        <!-- Transaction Type Filter -->
                        <div class="d-flex align-items-center">
                            <label for="transactionTypeFilter" class="form-label mb-0 me-2 text-muted">Type:</label>
                            <select class="form-select form-select-sm" id="transactionTypeFilter" style="width: auto;">
                                <option value="all">All</option>
                                <option value="payment">Payments</option>
                                <option value="expense">Expenses</option>
                                <option value="balance">Balance</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTableBody">
                        <!-- Transactions will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div class="text-muted">
                    Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span id="totalRecords">0</span> transactions
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="transactionsPagination">
                        <!-- Pagination will be generated here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Today's Payments -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>
                        Today's Payments
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <input type="text" class="form-control form-control-sm" id="paymentSearch" placeholder="Search payments..." style="width: 200px;">
                        <button class="btn btn-outline-primary btn-sm" onclick="filterPaymentsByType('all')">All</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="filterPaymentsByType('Booking')">New Booking</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="filterPaymentsByType('FollowUp')">Follow-up</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="filterPaymentsByType('Consultation')">Consultation</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <?php foreach ($payments as $payment): ?>
                        <tr data-type="<?= $payment['type'] ?>">
                            <td><?= date('H:i', strtotime($payment['created_at'])) ?></td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($payment['patient_name'] ?? 'N/A') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($payment['phone'] ?? 'N/A') ?></small>
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
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            onclick="viewPayment(<?= $payment['id'] ?>)" 
                                            title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                            onclick="printReceipt(<?= $payment['id'] ?>)" 
                                            title="Print Receipt">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm" 
                                            onclick="editPayment(<?= $payment['id'] ?>)" 
                                            title="Edit Payment">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="deletePayment(<?= $payment['id'] ?>)" 
                                            title="Delete Payment">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Daily Balance Modal -->
<div class="modal fade" id="dailyBalanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Daily Balance
                </h5>
                <div class="keyboard-hint">
                    Press <kbd>Esc</kbd> to close
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="dailyBalanceForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="dailyBalanceMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-plus-circle me-1"></i>
                                Balance Details
                            </h6>
                            
                            <div class="mb-3">
                                <label for="balanceAmount" class="form-label">Balance Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="balanceAmount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="balanceType" class="form-label">Balance Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="balanceType" name="balance_type" required>
                                    <option value="">Select type...</option>
                                    <option value="opening">Opening Balance</option>
                                    <option value="additional">Additional Balance</option>
                                    <option value="withdrawal">Withdrawal</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Additional Details
                            </h6>
                            
                            <div class="mb-3">
                                <label for="balanceDescription" class="form-label">Description</label>
                                <textarea class="form-control" 
                                          id="balanceDescription" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="Enter description..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="balanceDate" class="form-label">Balance Date</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="balanceDate" 
                                       name="balance_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="balanceSubmit">
                        <i class="bi bi-plus-circle me-1"></i>
                        <span class="btn-text">Add Balance</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-dash-circle me-2"></i>
                    Add New Expense
                </h5>
                <div class="keyboard-hint">
                    Press <kbd>Esc</kbd> to close
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="expenseForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="expenseMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-dash-circle me-1"></i>
                                Expense Details
                            </h6>
                            
                            <div class="mb-3">
                                <label for="expenseAmount" class="form-label">Expense Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="expenseAmount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expenseName" class="form-label">Expense Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="expenseName" 
                                       name="expense_name" 
                                       placeholder="Enter expense name..."
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <!-- Expense Type Badges -->
                            <div class="mb-3">
                                <label class="form-label">Quick Expense Types:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="Water Bill" 
                                          style="cursor: pointer;">
                                        Water Bill
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="Electricity Bill" 
                                          style="cursor: pointer;">
                                        Electricity Bill
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="Medical Supplies" 
                                          style="cursor: pointer;">
                                        Medical Supplies
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="Cleaning Expenses" 
                                          style="cursor: pointer;">
                                        Cleaning Expenses
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="Secretary Salary" 
                                          style="cursor: pointer;">
                                        Secretary Salary
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="Maintenance" 
                                          style="cursor: pointer;">
                                        Maintenance
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="Other" 
                                          style="cursor: pointer;">
                                        Other
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Additional Details
                            </h6>
                            
                            <div class="mb-3">
                                <label for="expenseCategory" class="form-label">Expense Category</label>
                                <select class="form-select" id="expenseCategory" name="category">
                                    <option value="utilities">Utilities</option>
                                    <option value="medical">Medical</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="office">Office</option>
                                    <option value="salary">Salary</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expenseNotes" class="form-label">Notes</label>
                                <textarea class="form-control" 
                                          id="expenseNotes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Notes about the expense..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expenseDate" class="form-label">Expense Date</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="expenseDate" 
                                       name="expense_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="expenseSubmit">
                        <i class="bi bi-dash-circle me-1"></i>
                        <span class="btn-text">Add Expense</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-search me-2"></i>
                    Search Transactions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="searchDate" class="form-label">Date Range</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="date" class="form-control" id="searchDateFrom" placeholder="From">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control" id="searchDateTo" placeholder="To">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="searchType" class="form-label">Transaction Type</label>
                            <select class="form-select" id="searchType">
                                <option value="">All Types</option>
                                <option value="payment">Payments</option>
                                <option value="expense">Expenses</option>
                                <option value="balance">Balance</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="searchAmount" class="form-label">Amount Range</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" id="searchAmountFrom" placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" id="searchAmountTo" placeholder="Max">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="searchKeyword" class="form-label">Keyword</label>
                            <input type="text" class="form-control" id="searchKeyword" placeholder="Search in descriptions...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="performSearch()">
                    <i class="bi bi-search me-1"></i>
                    Search
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p id="deleteConfirmationMessage"></p>
                <p class="text-muted mb-0" id="deleteConfirmationDetails"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    Edit Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPaymentForm">
                <div class="modal-body">
                    <div id="editPaymentMessage" class="alert d-none" role="alert"></div>
                    
                    <input type="hidden" id="editPaymentId" name="payment_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editPaymentAmount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="editPaymentAmount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editPaymentType" class="form-label">Payment Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="editPaymentType" name="type" required>
                                    <option value="Booking">New Booking</option>
                                    <option value="FollowUp">Follow-up</option>
                                    <option value="Consultation">Consultation</option>
                                    <option value="Procedure">Procedure</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editPaymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="editPaymentMethod" name="method" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Card">Card</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Wallet">Wallet</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editPaymentDescription" class="form-label">Description</label>
                                <textarea class="form-control" 
                                          id="editPaymentDescription" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="Payment description..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editPaymentDate" class="form-label">Payment Date</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="editPaymentDate" 
                                       name="payment_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editPaymentSubmit">
                        <i class="bi bi-pencil me-1"></i>
                        <span class="btn-text">Update Payment</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    Edit Expense
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editExpenseForm">
                <div class="modal-body">
                    <div id="editExpenseMessage" class="alert d-none" role="alert"></div>
                    
                    <input type="hidden" id="editExpenseId" name="expense_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editExpenseAmount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="editExpenseAmount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editExpenseName" class="form-label">Expense Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="editExpenseName" 
                                       name="expense_name" 
                                       placeholder="Enter expense name..."
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editExpenseCategory" class="form-label">Category</label>
                                <select class="form-select" id="editExpenseCategory" name="category">
                                    <option value="utilities">Utilities</option>
                                    <option value="medical">Medical</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="office">Office</option>
                                    <option value="salary">Salary</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editExpenseDate" class="form-label">Expense Date</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="editExpenseDate" 
                                       name="expense_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editExpenseNotes" class="form-label">Notes</label>
                        <textarea class="form-control" 
                                  id="editExpenseNotes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Notes about the expense..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="editExpenseSubmit">
                        <i class="bi bi-pencil me-1"></i>
                        <span class="btn-text">Update Expense</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalTitle">
                    <i class="bi bi-info-circle me-2 text-info"></i>
                    Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="infoModalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set current date and time as default
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('balanceDate').value = localDateTime;
    document.getElementById('expenseDate').value = localDateTime;
    
    // Expense type badges functionality
    const expenseTypeBadges = document.querySelectorAll('.expense-type-badge');
    expenseTypeBadges.forEach(badge => {
        badge.addEventListener('click', function() {
            const expenseName = document.getElementById('expenseName');
            expenseName.value = this.dataset.type;
            
            // Update badge appearance
            expenseTypeBadges.forEach(b => b.classList.remove('bg-primary', 'text-white'));
            this.classList.add('bg-primary', 'text-white');
        });
    });
    
    // Daily balance form submission
    const dailyBalanceForm = document.getElementById('dailyBalanceForm');
    if (dailyBalanceForm) {
        dailyBalanceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!dailyBalanceForm.checkValidity()) {
                dailyBalanceForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(dailyBalanceForm);
            
            // Show loading state
            const submitButton = document.getElementById('balanceSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Adding...';
            spinner.classList.remove('d-none');
            
            fetch('/api/daily-balance', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Balance';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('dailyBalanceMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Balance added successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Reset form
                    dailyBalanceForm.reset();
                    dailyBalanceForm.classList.remove('was-validated');
                    document.getElementById('balanceDate').value = localDateTime;
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('dailyBalanceModal')).hide();
                        // Update cards and transactions without full reload
                        updateDashboardCards();
                        loadFinancialTransactions();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('dailyBalanceMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to add balance';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Balance';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('dailyBalanceMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error adding balance';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Expense form submission
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        expenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!expenseForm.checkValidity()) {
                expenseForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(expenseForm);
            
            // Show loading state
            const submitButton = document.getElementById('expenseSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Adding...';
            spinner.classList.remove('d-none');
            
            const jsonData = {
                amount: formData.get('amount'),
                expense_name: formData.get('expense_name'),
                category: formData.get('category'),
                notes: formData.get('notes'),
                expense_date: formData.get('expense_date')
            };
            
            fetch('/api/expenses', {
                method: 'POST',
                body: JSON.stringify(jsonData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Expense';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('expenseMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Expense added successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Reset form
                    expenseForm.reset();
                    expenseForm.classList.remove('was-validated');
                    document.getElementById('expenseDate').value = localDateTime;
                    
                    // Reset badges
                    expenseTypeBadges.forEach(b => b.classList.remove('bg-primary', 'text-white'));
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('expenseModal')).hide();
                        // Update cards and transactions without full reload
                        updateDashboardCards();
                        loadFinancialTransactions();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('expenseMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to add expense';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Expense';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('expenseMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error adding expense';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Edit Payment Form Submission
    const editPaymentForm = document.getElementById('editPaymentForm');
    if (editPaymentForm) {
        editPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!editPaymentForm.checkValidity()) {
                editPaymentForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(editPaymentForm);
            const paymentId = document.getElementById('editPaymentId').value;
            
            // Show loading state
            const submitButton = document.getElementById('editPaymentSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Updating...';
            spinner.classList.remove('d-none');
            
            const jsonData = {
                amount: formData.get('amount'),
                type: formData.get('type'),
                method: formData.get('method'),
                description: formData.get('description')
            };
            
            fetch(`/api/payments/${paymentId}`, {
                method: 'PUT',
                body: JSON.stringify(jsonData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Update Payment';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('editPaymentMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Payment updated successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editPaymentModal')).hide();
                        // Update dashboard and transactions
                        updateDashboardCards();
                        loadFinancialTransactions();
                        location.reload();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('editPaymentMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to update payment';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'Update Payment';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('editPaymentMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error updating payment';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Edit Expense Form Submission
    const editExpenseForm = document.getElementById('editExpenseForm');
    if (editExpenseForm) {
        editExpenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!editExpenseForm.checkValidity()) {
                editExpenseForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(editExpenseForm);
            const expenseId = document.getElementById('editExpenseId').value;
            
            // Show loading state
            const submitButton = document.getElementById('editExpenseSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Updating...';
            spinner.classList.remove('d-none');
            
            const jsonData = {
                amount: formData.get('amount'),
                expense_name: formData.get('expense_name'),
                category: formData.get('category'),
                notes: formData.get('notes'),
                expense_date: formData.get('expense_date')
            };
            
            fetch(`/api/expenses/${expenseId}`, {
                method: 'PUT',
                body: JSON.stringify(jsonData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Update Expense';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('editExpenseMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Expense updated successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editExpenseModal')).hide();
                        // Update dashboard and transactions
                        updateDashboardCards();
                        loadFinancialTransactions();
                        location.reload();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('editExpenseMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to update expense';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error updating expense:', error);
                
                submitButton.disabled = false;
                btnText.textContent = 'Update Expense';
                spinner.classList.add('d-none');
                
                const messageEl = document.getElementById('editExpenseMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error updating expense: ' + error.message;
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Load financial transactions
    loadFinancialTransactions();
    
    // Transaction filters
    const dateFilter = document.getElementById('dateFilter');
    const transactionTypeFilter = document.getElementById('transactionTypeFilter');
    
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            loadFinancialTransactions();
        });
    }
    
    if (transactionTypeFilter) {
        transactionTypeFilter.addEventListener('change', function() {
            loadFinancialTransactions();
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const isModalOpen = document.querySelector('.modal.show');
        const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                             e.target.contentEditable === 'true';
        
        // Open daily balance modal with 'B' key
        if (e.key.toLowerCase() === 'b' && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#dailyBalanceModal"]').click();
        }
        
        // Open expense modal with 'E' key
        if (e.key.toLowerCase() === 'e' && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#expenseModal"]').click();
        }
        
        // Open search modal with 'S' key
        if (e.key.toLowerCase() === 's' && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#searchModal"]').click();
        }
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                bootstrap.Modal.getInstance(openModal).hide();
            }
        }
    });
});

// Financial transactions management
let currentPage = 1;
const itemsPerPage = 10;

function loadFinancialTransactions(page = 1) {
    currentPage = page;
    
    const dateFilter = document.getElementById('dateFilter').value;
    const transactionTypeFilter = document.getElementById('transactionTypeFilter').value;
    
    const params = new URLSearchParams({
        page: page,
        limit: itemsPerPage,
        date: dateFilter,
        type: transactionTypeFilter
    });
    
    fetch(`/api/financial-transactions?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayTransactions(data.data.transactions);
                updatePagination(data.data.pagination);
            } else {
                console.error('Error loading transactions:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayTransactions(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2 mb-0">No transactions found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    transactions.forEach(transaction => {
        const typeBadge = getTransactionTypeBadge(transaction.type);
        const amountClass = transaction.type === 'expense' ? 'text-danger' : 'text-success';
        const amountPrefix = transaction.type === 'expense' ? '-' : '+';
        
        html += `
            <tr>
                <td>${formatDateTime(transaction.created_at)}</td>
                <td>${typeBadge}</td>
                <td>${transaction.description}</td>
                <td>
                    <span class="fw-bold ${amountClass}">
                        ${amountPrefix}${formatMoney(transaction.amount)}
                    </span>
                </td>
                <td>
                    <span class="fw-bold text-primary">${formatMoney(transaction.balance)}</span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        ${getTransactionActions(transaction)}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function getTransactionTypeBadge(type) {
    const badges = {
        'payment': '<span class="badge bg-success">Payment</span>',
        'expense': '<span class="badge bg-danger">Expense</span>',
        'balance': '<span class="badge bg-info">Balance</span>'
    };
    return badges[type] || '<span class="badge bg-secondary">Unknown</span>';
}

function getTransactionActions(transaction) {
    let actions = '';
    
    if (transaction.type === 'payment') {
        actions += `
            <button type="button" class="btn btn-outline-primary btn-sm" 
                    onclick="viewPayment(${transaction.id})"
                    title="View Payment Details">
                <i class="bi bi-eye"></i>
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" 
                    onclick="printReceipt(${transaction.id})"
                    title="Print Receipt">
                <i class="bi bi-printer"></i>
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" 
                    onclick="editPayment(${transaction.id})"
                    title="Edit Payment">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" 
                    onclick="deletePayment(${transaction.id})"
                    title="Delete Payment">
                <i class="bi bi-trash"></i>
            </button>
        `;
    } else if (transaction.type === 'expense') {
        actions += `
            <button type="button" class="btn btn-outline-warning btn-sm" 
                    onclick="editExpense(${transaction.id})"
                    title="Edit Expense">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" 
                    onclick="deleteExpense(${transaction.id})"
                    title="Delete Expense">
                <i class="bi bi-trash"></i>
            </button>
        `;
    }
    
    return actions;
}

function updatePagination(pagination) {
    document.getElementById('showingFrom').textContent = pagination.from;
    document.getElementById('showingTo').textContent = pagination.to;
    document.getElementById('totalRecords').textContent = pagination.total;
    
    const paginationContainer = document.getElementById('transactionsPagination');
    let html = '';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${pagination.current_page - 1})">Previous</a>
            </li>
        `;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${i})">${i}</a>
            </li>
        `;
    }
    
    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${pagination.current_page + 1})">Next</a>
            </li>
        `;
    }
    
    paginationContainer.innerHTML = html;
}

function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleDateString('en-US') + ' ' + date.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
}

function formatMoney(amount) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount) + ' EGP';
}

function exportToExcel() {
    const dateFilter = document.getElementById('dateFilter').value;
    const transactionTypeFilter = document.getElementById('transactionTypeFilter').value;
    
    // Show loading state
    const exportBtn = document.querySelector('[onclick="exportToExcel()"]');
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Exporting...';
    exportBtn.disabled = true;
    
    const params = new URLSearchParams({
        date: dateFilter,
        type: transactionTypeFilter
    });
    
    // Use window.open for direct download
    const exportUrl = `/api/financial-transactions/export?${params}`;
    window.open(exportUrl, '_blank');
    
    // Reset button
    setTimeout(() => {
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
        
        // Show success message
        showNotification('File exported successfully!', 'success');
    }, 1000);
}

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function updateDashboardCards() {
    fetch('/api/dashboard-summary', {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Update daily balance cards
                if (data.data.dailyBalance) {
                    const openingBalanceEl = document.getElementById('openingBalance');
                    const totalReceivedEl = document.getElementById('totalReceived');
                    const currentBalanceEl = document.getElementById('currentBalance');
                    const transactionsCountEl = document.getElementById('transactionsCount');
                    
                    if (openingBalanceEl) openingBalanceEl.textContent = formatMoney(data.data.dailyBalance.opening_balance) + ' EGP';
                    if (totalReceivedEl) totalReceivedEl.textContent = formatMoney(data.data.dailyBalance.total_received) + ' EGP';
                    if (currentBalanceEl) currentBalanceEl.textContent = formatMoney(data.data.dailyBalance.current_balance) + ' EGP';
                    if (transactionsCountEl) transactionsCountEl.textContent = data.data.dailyBalance.transactions_count;
                }
                
                // Update payment types summary
                if (data.data.paymentTypes) {
                    Object.keys(data.data.paymentTypes).forEach(type => {
                        const element = document.getElementById(type + 'Count');
                        if (element) {
                            element.textContent = data.data.paymentTypes[type].count;
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error updating dashboard cards:', error);
        });
}

// Action functions
function viewPayment(paymentId) {
    window.open(`/secretary/payments/${paymentId}`, '_blank');
}

function printReceipt(paymentId) {
    window.open(`/secretary/payments/${paymentId}/receipt`, '_blank');
}

function editPayment(paymentId) {
    // Fetch payment data and populate the edit modal
    fetch(`/api/payments/${paymentId}`, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Populate the edit form
                document.getElementById('editPaymentId').value = data.data.id;
                document.getElementById('editPaymentAmount').value = data.data.amount;
                document.getElementById('editPaymentType').value = data.data.type;
                document.getElementById('editPaymentMethod').value = data.data.method;
                document.getElementById('editPaymentDescription').value = data.data.description || '';
                
                // Format date for datetime-local input
                if (data.data.created_at) {
                    const date = new Date(data.data.created_at);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                    document.getElementById('editPaymentDate').value = localDateTime;
                }
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
                modal.show();
            } else {
                showErrorModal('Error loading payment data: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading payment data:', error);
            showErrorModal('Error loading payment data: ' + error.message);
        });
}

function deletePayment(paymentId) {
    showDeleteConfirmation('payment', paymentId, 'Are you sure you want to delete this payment?', 'This action cannot be undone.');
}

function editExpense(expenseId) {
    // Fetch expense data and populate the edit modal
    fetch(`/api/expenses/${expenseId}`, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Populate the edit form
                document.getElementById('editExpenseId').value = data.data.id;
                document.getElementById('editExpenseAmount').value = data.data.amount;
                document.getElementById('editExpenseName').value = data.data.expense_name;
                document.getElementById('editExpenseCategory').value = data.data.category;
                document.getElementById('editExpenseNotes').value = data.data.notes || '';
                
                // Format date for datetime-local input
                if (data.data.created_at) {
                    const date = new Date(data.data.created_at);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                    document.getElementById('editExpenseDate').value = localDateTime;
                }
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                modal.show();
            } else {
                showErrorModal('Error loading expense data: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading expense data:', error);
            showErrorModal('Error loading expense data: ' + error.message);
        });
}

function deleteExpense(expenseId) {
    showDeleteConfirmation('expense', expenseId, 'Are you sure you want to delete this expense?', 'This action cannot be undone.');
}

function filterPaymentsByType(type) {
    const rows = document.querySelectorAll('#paymentsTableBody tr[data-type]');
    
    rows.forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function performSearch() {
    // TODO: Implement search functionality
    showInfoModal('Search', 'Search functionality will be implemented soon', 'info');
}

// Modal Functions
function showDeleteConfirmation(type, id, message, details) {
    document.getElementById('deleteConfirmationMessage').textContent = message;
    document.getElementById('deleteConfirmationDetails').textContent = details;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    modal.show();
    
    // Store the delete action
    document.getElementById('confirmDeleteBtn').onclick = function() {
        executeDelete(type, id);
        modal.hide();
    };
}

function executeDelete(type, id) {
    const endpoint = type === 'payment' ? `/api/payments/${id}` : `/api/expenses/${id}`;
    
    fetch(endpoint, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showSuccessModal(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully`);
            // Update dashboard and transactions
            updateDashboardCards();
            loadFinancialTransactions();
            // Reload payments table
            location.reload();
        } else {
            showErrorModal(`Error deleting ${type}: ${data.error || 'Unknown error'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal(`Error deleting ${type}: ${error.message}`);
    });
}

function showInfoModal(title, message, type = 'info') {
    const iconClass = type === 'info' ? 'bi-info-circle text-info' : 
                     type === 'warning' ? 'bi-exclamation-triangle text-warning' : 
                     type === 'success' ? 'bi-check-circle text-success' : 
                     'bi-info-circle text-info';
    
    document.getElementById('infoModalTitle').innerHTML = `<i class="bi ${iconClass} me-2"></i>${title}`;
    document.getElementById('infoModalMessage').textContent = message;
    
    const modal = new bootstrap.Modal(document.getElementById('infoModal'));
    modal.show();
}

function showSuccessModal(message) {
    showInfoModal('Success', message, 'success');
}

function showErrorModal(message) {
    showInfoModal('Error', message, 'error');
}
</script>
