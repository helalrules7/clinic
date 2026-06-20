<?php
$title = 'Roaya Clinic - Financial Management';
$pageTitle = 'Financial Management';
$pageSubtitle = 'Manage payments, expenses, and daily operations';
?>
<link
    href="/app/Views/doctor/assets/css/payments.css?v=<?= file_exists(__DIR__ . '/assets/css/payments.css') ? filemtime(__DIR__ . '/assets/css/payments.css') : time() ?>"
    rel="stylesheet">

<div class="container-fluid">
    <!-- Page Header / Toolbar -->
    <div class="fin-toolbar mb-4">
        <a href="/doctor/daily-closure" class="fin-dayclose-btn">
            <i class="bi bi-calendar-check"></i>
            <span>Day Close</span>
        </a>
        <div class="fin-toolbar-actions">
            <!-- Clinic filter (doctor only — secretaries are server-side pinned) -->
            <form method="get" id="financeClinicFilterForm" class="fin-clinic-filter">
                <i class="bi bi-building"></i>
                <select id="financeClinicFilter" name="clinic_id" class="form-select form-select-sm"
                        onchange="document.getElementById('financeClinicFilterForm').submit()">
                    <option value="" <?= empty($selectedClinicId) ? 'selected' : '' ?>>All Clinics</option>
                    <?php foreach (($clinics ?? []) as $_clinic): ?>
                        <option value="<?= (int)$_clinic['id'] ?>" <?= ($selectedClinicId == $_clinic['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($_clinic['name_en'] ?: $_clinic['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <button class="btn btn-primary fin-action-btn" data-bs-toggle="modal" data-bs-target="#dailyBalanceModal"
                title="Add Daily Balance">
                <i class="bi bi-plus-circle me-2"></i>Add Balance <kbd>B</kbd>
            </button>
            <button class="btn fin-action-btn fin-action-expense" data-bs-toggle="modal" data-bs-target="#expenseModal"
                title="Add Expense">
                <i class="bi bi-dash-circle me-2"></i>Add Expense <kbd>E</kbd>
            </button>
            <button class="btn fin-action-btn fin-action-search" data-bs-toggle="modal" data-bs-target="#searchModal"
                title="Search Transactions">
                <i class="bi bi-search me-2"></i>Search <kbd>S</kbd>
            </button>
        </div>
    </div>

    <!-- Daily Balance Overview -->
    <?php
        $__trend = is_array($financialTrend ?? null) ? $financialTrend : ['opening' => [], 'received' => [], 'expenses' => [], 'current' => []];
    ?>
    <div class="fin-stats-grid mb-4">
        <div class="fin-stat fin-stat--opening">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="openingBalance"><?= number_format($dailyBalance['opening_balance'], 2) ?> EGP</div>
                    <div class="fin-stat-label">Opening Balance</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-wallet2"></i></span>
            </div>
            <div class="fin-stat-spark" data-spark='<?= htmlspecialchars(json_encode(array_map("floatval", $__trend["opening"] ?? [])), ENT_QUOTES) ?>'></div>
        </div>
        <div class="fin-stat fin-stat--received">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="totalReceived"><?= number_format($dailyBalance['total_received'], 2) ?> EGP</div>
                    <div class="fin-stat-label">Total Received</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-arrow-down-circle"></i></span>
            </div>
            <div class="fin-stat-spark" data-spark='<?= htmlspecialchars(json_encode(array_map("floatval", $__trend["received"] ?? [])), ENT_QUOTES) ?>'></div>
        </div>
        <div class="fin-stat fin-stat--expenses">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="totalExpenses"><?= number_format($dailyBalance['total_expenses'], 2) ?> EGP</div>
                    <div class="fin-stat-label">Total Expenses</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-arrow-up-circle"></i></span>
            </div>
            <div class="fin-stat-spark" data-spark='<?= htmlspecialchars(json_encode(array_map("floatval", $__trend["expenses"] ?? [])), ENT_QUOTES) ?>'></div>
        </div>
        <div class="fin-stat fin-stat--current">
            <div class="fin-stat-top">
                <div class="fin-stat-text">
                    <div class="fin-stat-value" id="currentBalance"><?= number_format($dailyBalance['current_balance'], 2) ?> EGP</div>
                    <div class="fin-stat-label">Current Balance</div>
                </div>
                <span class="fin-stat-ic"><i class="bi bi-calculator"></i></span>
            </div>
            <div class="fin-stat-spark" data-spark='<?= htmlspecialchars(json_encode(array_map("floatval", $__trend["current"] ?? [])), ENT_QUOTES) ?>'></div>
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
                    <div class="fin-types">
                        <div class="fin-type fin-type--booking">
                            <span class="fin-type-tag">New Booking</span>
                            <span class="fin-type-count" id="BookingCount"><?= $paymentTypes['Booking']['count'] ?? 0 ?></span>
                            <span class="fin-type-amount"><?= number_format($paymentTypes['Booking']['total'] ?? 0, 2) ?> EGP</span>
                        </div>
                        <div class="fin-type fin-type--followup">
                            <span class="fin-type-tag">Follow-up</span>
                            <span class="fin-type-count" id="FollowUpCount"><?= $paymentTypes['FollowUp']['count'] ?? 0 ?></span>
                            <span class="fin-type-amount"><?= number_format($paymentTypes['FollowUp']['total'] ?? 0, 2) ?> EGP</span>
                        </div>
                        <div class="fin-type fin-type--consultation">
                            <span class="fin-type-tag">Consultation</span>
                            <span class="fin-type-count" id="ConsultationCount"><?= $paymentTypes['Consultation']['count'] ?? 0 ?></span>
                            <span class="fin-type-amount"><?= number_format($paymentTypes['Consultation']['total'] ?? 0, 2) ?> EGP</span>
                        </div>
                        <div class="fin-type fin-type--procedure">
                            <span class="fin-type-tag">Procedure</span>
                            <span class="fin-type-count" id="ProcedureCount"><?= $paymentTypes['Procedure']['count'] ?? 0 ?></span>
                            <span class="fin-type-amount"><?= number_format($paymentTypes['Procedure']['total'] ?? 0, 2) ?> EGP</span>
                        </div>
                        <div class="fin-type fin-type--other">
                            <span class="fin-type-tag">Other</span>
                            <span class="fin-type-count" id="OtherCount"><?= $paymentTypes['Other']['count'] ?? 0 ?></span>
                            <span class="fin-type-amount"><?= number_format($paymentTypes['Other']['total'] ?? 0, 2) ?> EGP</span>
                        </div>
                        <div class="fin-type fin-type--withdrawals">
                            <span class="fin-type-tag">Withdrawals</span>
                            <span class="fin-type-count" id="withdrawalsCount"><?= $dailyBalance['withdrawals_count'] ?? 0 ?></span>
                            <span class="fin-type-amount"><?= number_format($dailyBalance['total_withdrawals'] ?? 0, 2) ?> EGP</span>
                        </div>
                        <div class="fin-type fin-type--expenses">
                            <span class="fin-type-tag">Expenses</span>
                            <span class="fin-type-count"><?= count($expenses) ?></span>
                            <span class="fin-type-amount"><?= number_format(array_sum(array_column($expenses, 'amount')), 2) ?> EGP</span>
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
                            <input type="date" class="form-control form-control-sm" id="dateFilter"
                                style="width: auto;">
                        </div>
                        <!-- Transaction Type Filter -->
                        <div class="d-flex align-items-center">
                            <label for="transactionTypeFilter" class="form-label mb-0 me-2 text-muted">Type:</label>
                            <section class="field menu" style="min-width: auto; width: auto;">
                                <div class="control">
                                    <select class="form-select form-select-sm d-none" id="transactionTypeFilter"
                                        style="width: auto;">
                                        <option value="all" selected>All</option>
                                        <option value="payment">Payments</option>
                                        <option value="expense">Expenses</option>
                                        <option value="balance">Balance</option>
                                    </select>
                                    <button type="button" class="custom-select-toggle" aria-expanded="false"
                                        style="font-size: 0.875rem; padding: 0.375rem 2rem 0.375rem 0.75rem;">All</button>
                                    <menu>
                                        <li data-option="all" tabindex="0" role="button" class="selected"><i
                                                class="bi-list fs-5"></i>
                                            <h3>All</h3>
                                        </li>
                                        <li data-option="payment" tabindex="0" role="button"><i
                                                class="bi-list fs-5"></i>
                                            <h3>Payments</h3>
                                        </li>
                                        <li data-option="expense" tabindex="0" role="button"><i
                                                class="bi-list fs-5"></i>
                                            <h3>Expenses</h3>
                                        </li>
                                        <li data-option="balance" tabindex="0" role="button"><i
                                                class="bi-list fs-5"></i>
                                            <h3>Balance</h3>
                                        </li>
                                    </menu>
                                </div>
                            </section>
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
                    Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span
                        id="totalRecords">0</span> transactions
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
                        <input type="text" class="form-control form-control-sm" id="paymentSearch"
                            placeholder="Search payments..." style="width: 200px;">
                        <button class="btn btn-outline-primary btn-sm"
                            onclick="filterPaymentsByType('all')">All</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="filterPaymentsByType('Booking')">New
                            Booking</button>
                        <button class="btn btn-outline-primary btn-sm"
                            onclick="filterPaymentsByType('FollowUp')">Follow-up</button>
                        <button class="btn btn-outline-primary btn-sm"
                            onclick="filterPaymentsByType('Consultation')">Consultation</button>
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
                            <th>Clinic</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <?php
                            $_clinicVisuals = [
                                'riyadh' => ['icon' => 'bi-buildings-fill', 'color' => '#0d6efd'],
                                'kfs'    => ['icon' => 'bi-hospital-fill',  'color' => '#10b981'],
                            ];
                        ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr data-type="<?= $payment['type'] ?>" data-clinic="<?= (int)($payment['clinic_id'] ?? 0) ?>">
                                <td><?= date('H:i', strtotime($payment['created_at'])) ?></td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($payment['patient_name'] ?? 'N/A') ?></strong>
                                        <br><small
                                            class="text-muted"><?= htmlspecialchars($payment['phone'] ?? 'N/A') ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $_clinicName = $payment['clinic_name_ar'] ?? $payment['clinic_name_en'] ?? null;
                                        if ($_clinicName):
                                            $_v = $_clinicVisuals[$payment['clinic_code'] ?? ''] ?? ['icon' => 'bi-building', 'color' => '#6c757d'];
                                    ?>
                                        <span class="clinic-tag" style="--clinic-color: <?= $_v['color'] ?>;" dir="rtl">
                                            <i class="bi <?= $_v['icon'] ?>"></i>
                                            <?= htmlspecialchars($_clinicName) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
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
                                            onclick="viewPayment(<?= $payment['id'] ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm"
                                            onclick="printReceipt(<?= $payment['id'] ?>)" title="Print Receipt">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                            onclick="editPayment(<?= $payment['id'] ?>)" title="Edit Payment">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="deletePayment(<?= $payment['id'] ?>)" title="Delete Payment">
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
                                <label for="balanceAmount" class="form-label">Balance Amount <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" class="form-control" id="balanceAmount" name="amount"
                                        step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="balanceType" class="form-label">Balance Type <span
                                        class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="balanceType" name="balance_type"
                                            required>
                                            <option value="">Select type...</option>
                                            <option value="opening">Opening Balance</option>
                                            <option value="additional">Additional Balance</option>
                                            <option value="withdrawal">Withdrawal</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">Select
                                            type...</button>
                                        <menu>
                                            <li data-option="" tabindex="0" role="button" class="selected"><i
                                                    class="bi-wallet fs-5"></i>
                                                <h3>Select type...</h3>
                                            </li>
                                            <li data-option="opening" tabindex="0" role="button"><i
                                                    class="bi-wallet fs-5"></i>
                                                <h3>Opening Balance</h3>
                                            </li>
                                            <li data-option="additional" tabindex="0" role="button"><i
                                                    class="bi-wallet fs-5"></i>
                                                <h3>Additional Balance</h3>
                                            </li>
                                            <li data-option="withdrawal" tabindex="0" role="button"><i
                                                    class="bi-wallet fs-5"></i>
                                                <h3>Withdrawal</h3>
                                            </li>
                                        </menu>
                                    </div>
                                </section>
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
                                <textarea class="form-control" id="balanceDescription" name="description" rows="3"
                                    placeholder="Enter description..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="balanceDate" class="form-label">Balance Date</label>
                                <input type="datetime-local" class="form-control" id="balanceDate" name="balance_date">
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
                                <label for="expenseAmount" class="form-label">Expense Amount <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" class="form-control" id="expenseAmount" name="amount"
                                        step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="expenseName" class="form-label">Expense Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="expenseName" name="expense_name"
                                    placeholder="Enter expense name..." required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Expense Type Badges -->
                            <div class="mb-3">
                                <label class="form-label">Quick Expense Types:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge"
                                        data-type="Water Bill" style="cursor: pointer;">
                                        Water Bill
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge"
                                        data-type="Electricity Bill" style="cursor: pointer;">
                                        Electricity Bill
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge"
                                        data-type="Medical Supplies" style="cursor: pointer;">
                                        Medical Supplies
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge"
                                        data-type="Cleaning Expenses" style="cursor: pointer;">
                                        Cleaning Expenses
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge"
                                        data-type="Secretary Salary" style="cursor: pointer;">
                                        Secretary Salary
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge"
                                        data-type="Maintenance" style="cursor: pointer;">
                                        Maintenance
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge"
                                        data-type="Other" style="cursor: pointer;">
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
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="expenseCategory" name="category">
                                            <option value="utilities" selected>Utilities</option>
                                            <option value="medical">Medical</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="office">Office</option>
                                            <option value="salary">Salary</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle"
                                            aria-expanded="false">Utilities</button>
                                        <menu>
                                            <li data-option="utilities" tabindex="0" role="button" class="selected"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Utilities</h3>
                                            </li>
                                            <li data-option="medical" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Medical</h3>
                                            </li>
                                            <li data-option="maintenance" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Maintenance</h3>
                                            </li>
                                            <li data-option="office" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Office</h3>
                                            </li>
                                            <li data-option="salary" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Salary</h3>
                                            </li>
                                            <li data-option="other" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Other</h3>
                                            </li>
                                        </menu>
                                    </div>
                                </section>
                            </div>

                            <div class="mb-3">
                                <label for="expenseNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="expenseNotes" name="notes" rows="3"
                                    placeholder="Notes about the expense..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="expenseDate" class="form-label">Expense Date</label>
                                <input type="datetime-local" class="form-control" id="expenseDate" name="expense_date">
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
                            <section class="field menu" style="min-width: 100%;">
                                <div class="control">
                                    <select class="form-select d-none" id="searchType">
                                        <option value="" selected>All Types</option>
                                        <option value="payment">Payments</option>
                                        <option value="expense">Expenses</option>
                                        <option value="balance">Balance</option>
                                    </select>
                                    <button type="button" class="custom-select-toggle" aria-expanded="false">All
                                        Types</button>
                                    <menu>
                                        <li data-option="" tabindex="0" role="button" class="selected"><i
                                                class="bi-list fs-5"></i>
                                            <h3>All Types</h3>
                                        </li>
                                        <li data-option="payment" tabindex="0" role="button"><i
                                                class="bi-list fs-5"></i>
                                            <h3>Payments</h3>
                                        </li>
                                        <li data-option="expense" tabindex="0" role="button"><i
                                                class="bi-list fs-5"></i>
                                            <h3>Expenses</h3>
                                        </li>
                                        <li data-option="balance" tabindex="0" role="button"><i
                                                class="bi-list fs-5"></i>
                                            <h3>Balance</h3>
                                        </li>
                                    </menu>
                                </div>
                            </section>
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
                            <input type="text" class="form-control" id="searchKeyword"
                                placeholder="Search in descriptions...">
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
                                <label for="editPaymentAmount" class="form-label">Amount <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" class="form-control" id="editPaymentAmount" name="amount"
                                        step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="editPaymentType" class="form-label">Payment Type <span
                                        class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="editPaymentType" name="type" required>
                                            <option value="Booking" selected>New Booking</option>
                                            <option value="FollowUp">Follow-up</option>
                                            <option value="Consultation">Consultation</option>
                                            <option value="Procedure">Procedure</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">New
                                            Booking</button>
                                        <menu>
                                            <li data-option="Booking" tabindex="0" role="button" class="selected"><i
                                                    class="bi-credit-card fs-5"></i>
                                                <h3>New Booking</h3>
                                            </li>
                                            <li data-option="FollowUp" tabindex="0" role="button"><i
                                                    class="bi-credit-card fs-5"></i>
                                                <h3>Follow-up</h3>
                                            </li>
                                            <li data-option="Consultation" tabindex="0" role="button"><i
                                                    class="bi-credit-card fs-5"></i>
                                                <h3>Consultation</h3>
                                            </li>
                                            <li data-option="Procedure" tabindex="0" role="button"><i
                                                    class="bi-credit-card fs-5"></i>
                                                <h3>Procedure</h3>
                                            </li>
                                            <li data-option="Other" tabindex="0" role="button"><i
                                                    class="bi-credit-card fs-5"></i>
                                                <h3>Other</h3>
                                            </li>
                                        </menu>
                                    </div>
                                </section>
                            </div>

                            <div class="mb-3">
                                <label for="editPaymentMethod" class="form-label">Payment Method <span
                                        class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="editPaymentMethod" name="method"
                                            required>
                                            <option value="Cash" selected>Cash</option>
                                            <option value="Card">Card</option>
                                            <option value="Transfer">Transfer</option>
                                            <option value="Wallet">Wallet</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle"
                                            aria-expanded="false">Cash</button>
                                        <menu>
                                            <li data-option="Cash" tabindex="0" role="button" class="selected"><i
                                                    class="bi-cash fs-5"></i>
                                                <h3>Cash</h3>
                                            </li>
                                            <li data-option="Card" tabindex="0" role="button"><i
                                                    class="bi-cash fs-5"></i>
                                                <h3>Card</h3>
                                            </li>
                                            <li data-option="Transfer" tabindex="0" role="button"><i
                                                    class="bi-cash fs-5"></i>
                                                <h3>Transfer</h3>
                                            </li>
                                            <li data-option="Wallet" tabindex="0" role="button"><i
                                                    class="bi-cash fs-5"></i>
                                                <h3>Wallet</h3>
                                            </li>
                                        </menu>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editPaymentDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editPaymentDescription" name="description" rows="3"
                                    placeholder="Payment description..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="editPaymentDate" class="form-label">Payment Date</label>
                                <input type="datetime-local" class="form-control" id="editPaymentDate"
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

<!-- Refund Payment Modal (doctor) -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Refund Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="refundError" class="alert alert-danger d-none"></div>
                <p class="text-muted small mb-3">Refundable up to <strong id="refundMax">0.00 EGP</strong>. A refund books a negative payment dated today.</p>
                <div class="mb-3">
                    <label class="form-label">Refund amount (EGP)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="refundAmount" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="refundReason" placeholder="Required">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="refundSubmit" onclick="submitRefund()">Confirm refund</button>
            </div>
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
                                <label for="editExpenseAmount" class="form-label">Amount <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">EGP</span>
                                    <input type="number" class="form-control" id="editExpenseAmount" name="amount"
                                        step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="editExpenseName" class="form-label">Expense Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editExpenseName" name="expense_name"
                                    placeholder="Enter expense name..." required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editExpenseCategory" class="form-label">Category</label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="editExpenseCategory" name="category">
                                            <option value="utilities" selected>Utilities</option>
                                            <option value="medical">Medical</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="office">Office</option>
                                            <option value="salary">Salary</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle"
                                            aria-expanded="false">Utilities</button>
                                        <menu>
                                            <li data-option="utilities" tabindex="0" role="button" class="selected"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Utilities</h3>
                                            </li>
                                            <li data-option="medical" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Medical</h3>
                                            </li>
                                            <li data-option="maintenance" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Maintenance</h3>
                                            </li>
                                            <li data-option="office" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Office</h3>
                                            </li>
                                            <li data-option="salary" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Salary</h3>
                                            </li>
                                            <li data-option="other" tabindex="0" role="button"><i
                                                    class="bi-tags fs-5"></i>
                                                <h3>Other</h3>
                                            </li>
                                        </menu>
                                    </div>
                                </section>
                            </div>

                            <div class="mb-3">
                                <label for="editExpenseDate" class="form-label">Expense Date</label>
                                <input type="datetime-local" class="form-control" id="editExpenseDate"
                                    name="expense_date">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editExpenseNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="editExpenseNotes" name="notes" rows="3"
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
    // Initialize PAYMENTS_CONFIG with PHP variables
    <?php
    ?>

    window.PAYMENTS_CONFIG = {
        payments: <?= json_encode($payments, JSON_UNESCAPED_UNICODE) ?>,
        expenses: <?= json_encode($expenses, JSON_UNESCAPED_UNICODE) ?>,
        dailyBalance: <?= json_encode($dailyBalance, JSON_UNESCAPED_UNICODE) ?>,
        paymentTypes: <?= json_encode($paymentTypes, JSON_UNESCAPED_UNICODE) ?>,
    };
</script>
<script
    src="/app/Views/doctor/assets/js/payments.js?v=<?= file_exists(__DIR__ . '/assets/js/payments.js') ? filemtime(__DIR__ . '/assets/js/payments.js') : time() ?>"></script>

<!-- AI Chat Widget -->
<link href="/app/Views/doctor/assets/css/ai-chat-widget.css" rel="stylesheet">
<script src="/app/Views/doctor/assets/js/ai-chat-widget.js"></script>
<style>
    /*.modal-backdrop.show{
        display: none !important;
    }
    body > div.modal-backdrop.fade.show{
        display: none !important;
    }*/
    .dark .modal-content {
        background: var(--card) !important;
    }

    .modal-content {
        background: var(--card) !important;
    }

.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>