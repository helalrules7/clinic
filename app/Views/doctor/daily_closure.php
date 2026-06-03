<?php
$title = 'Roaya Clinic - Daily Closure';
$pageTitle = 'Daily Closure';
$pageSubtitle = 'Review and close daily operations for ' . date('F j, Y', strtotime($today));
?>

<link href="/app/Views/doctor/assets/css/daily_closure.css?v=<?= file_exists(__DIR__ . '/assets/css/daily_closure.css') ? filemtime(__DIR__ . '/assets/css/daily_closure.css') : time() ?>" rel="stylesheet">
<div class="container-fluid">
    <!-- Breadcrumb: Daily Closure is a sub-page of Payments -->
    <nav class="app-breadcrumb" aria-label="Breadcrumb">
        <a href="/doctor/payments" class="app-crumb-back" aria-label="Back to Payments">
            <i class="bi bi-arrow-left"></i>
        </a>
        <a href="/doctor/payments" class="app-crumb-link">Payments</a>
        <i class="bi bi-chevron-right app-crumb-sep" aria-hidden="true"></i>
        <span class="app-crumb-current">Daily Closure</span>
    </nav>

    <!-- Page Header / Toolbar -->
    <div class="dc-toolbar mb-4">
        <div class="dc-toolbar-text">
            <span class="dc-toolbar-icon"><i class="bi bi-calendar-check"></i></span>
            <div>
                <h2 class="dc-toolbar-title">Daily Closure</h2>
                <p class="dc-toolbar-sub">Review and close daily operations for <?= date('F j, Y', strtotime($today)) ?></p>
            </div>
        </div>
        <?php if (!$isClosed): ?>
        <button class="dc-close-btn" onclick="closeDay()" id="closeDayBtn" title="Close the day">
            <i class="bi bi-check-circle"></i>
            <span>Close Day</span>
            <kbd>C</kbd>
        </button>
        <?php else: ?>
        <div class="dc-closed-badge">
            <i class="bi bi-lock-fill"></i>
            Day is already closed
        </div>
        <?php endif; ?>
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

<style>
.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>
