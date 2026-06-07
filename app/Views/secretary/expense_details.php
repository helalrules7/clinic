<link href="/app/Views/secretary/assets/css/details.css?v=<?= file_exists(__DIR__ . '/assets/css/details.css') ? filemtime(__DIR__ . '/assets/css/details.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/../../doctor/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../../doctor/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<!-- Expense Details Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-dash-circle me-2"></i>
                تفاصيل المصروف
            </h4>
        </div>
        <p class="text-muted mb-0 arabic-text">عرض تفاصيل المصروف رقم <?= $expense['id'] ?></p>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-outline-primary" onclick="window.history.back()">
                <i class="bi bi-arrow-right me-2"></i>
                العودة
            </button>
            <button class="btn btn-outline-info" onclick="printExpense(<?= $expense['id'] ?>)">
                <i class="bi bi-printer me-2"></i>
                طباعة
            </button>
        </div>
    </div>
</div>

<!-- Expense Information -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-info-circle me-2"></i>
                    معلومات المصروف
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">اسم المصروف:</label>
                            <p class="form-control-plaintext arabic-text"><?= htmlspecialchars($expense['expense_name']) ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">المبلغ:</label>
                            <p class="form-control-plaintext fw-bold text-danger fs-5">
                                <?= number_format($expense['amount'], 2) ?> جنيه
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">فئة المصروف:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getExpenseCategoryBadgeClass($expense['category']) ?> arabic-text">
                                    <?= $this->getExpenseCategoryText($expense['category']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">تاريخ الإنشاء:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-calendar me-2"></i>
                                <?= date('Y-m-d H:i', strtotime($expense['created_at'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">أنشأ بواسطة:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($creator['name'] ?? 'غير محدد') ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">الدور:</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info arabic-text">
                                    <?= $creator['role'] === 'doctor' ? 'طبيب' : ($creator['role'] === 'secretary' ? 'سكرتارية' : 'غير محدد') ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($expense['notes'])): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold arabic-text">ملاحظات:</label>
                    <div class="form-control-plaintext bg-light p-3 rounded arabic-text">
                        <?= nl2br(htmlspecialchars($expense['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-person-circle me-2"></i>
                    معلومات المنشئ
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-lg me-3">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 arabic-text"><?= htmlspecialchars($creator['name'] ?? 'غير محدد') ?></h6>
                        <small class="text-muted arabic-text"><?= $creator['email'] ?? 'غير محدد' ?></small>
                    </div>
                </div>
                
                <div class="mb-2">
                    <span class="badge bg-primary arabic-text">
                        <?= $creator['role'] === 'doctor' ? 'طبيب' : ($creator['role'] === 'secretary' ? 'سكرتارية' : 'غير محدد') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Expenses -->
<?php if (!empty($relatedExpenses)): ?>
<div class="card shadow dashboard-card">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary arabic-text">
            <i class="bi bi-list-ul me-2"></i>
            مصروفات أخرى في نفس اليوم
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text text-end" dir="rtl">الوقت</th>
                        <th class="arabic-text text-end" dir="rtl">اسم المصروف</th>
                        <th class="arabic-text text-end" dir="rtl">المبلغ</th>
                        <th class="arabic-text text-end" dir="rtl">الفئة</th>
                        <th class="arabic-text text-end" dir="rtl">المنشئ</th>
                        <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relatedExpenses as $relatedExpense): ?>
                    <tr <?= $relatedExpense['id'] == $expense['id'] ? 'class="table-active"' : '' ?>>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock me-2 text-primary"></i>
                                <?= date('H:i', strtotime($relatedExpense['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold arabic-text"><?= htmlspecialchars($relatedExpense['expense_name']) ?></div>
                            <?php if ($relatedExpense['id'] == $expense['id']): ?>
                            <small class="text-primary arabic-text">المصروف الحالي</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="fw-bold text-danger"><?= number_format($relatedExpense['amount'], 2) ?> جنيه</span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getExpenseCategoryBadgeClass($relatedExpense['category']) ?> arabic-text">
                                <?= $this->getExpenseCategoryText($relatedExpense['category']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($relatedExpense['created_by_name'] ?? 'غير محدد') ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($relatedExpense['id'] != $expense['id']): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    onclick="viewExpense(<?= $relatedExpense['id'] ?>)"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="عرض تفاصيل المصروف">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php else: ?>
                            <span class="text-muted arabic-text">الحالي</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function viewExpense(expenseId) {
    window.location.href = `/secretary/expenses/${expenseId}`;
}

function printExpense(expenseId) {
    // TODO: Implement expense printing
    alert('طباعة المصروف - سيتم تطويره قريباً');
}
</script>

<style>
/* RTL — icon spacing: sec-style.css §secretary icon spacing */
.text-start { text-align: right !important; }
.text-end { text-align: left !important; }
.justify-content-start { justify-content: flex-end !important; }
.justify-content-end { justify-content: flex-start !important; }

/* Arabic text styling */
.arabic-text {
    font-family: 'Cairo', Arial, sans-serif;
    direction: rtl;
    text-align: right;
}

/* Avatar styling */
.avatar-lg {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    font-size: 2rem;
}

/* Card styling */
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

/* Table styling */
.table th {
    border-top: none;
    font-weight: 600;
    color: var(--text);
    background: var(--bg);
}

.table td {
    vertical-align: middle;
    border-top: 1px solid var(--border);
}

/* Badge styling */
.badge.bg-primary {
    background-color: var(--accent) !important;
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

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

/* RTL — icon spacing: sec-style.css §secretary icon spacing */
.text-start { text-align: right !important; }
.text-end { text-align: left !important; }
.justify-content-start { justify-content: flex-end !important; }
.justify-content-end { justify-content: flex-start !important; }

/* Arabic text styling */
.arabic-text {
    font-family: 'Cairo', Arial, sans-serif;
    direction: rtl;
    text-align: right;
}

/* Secretary specific styles */
.stat-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.stat-label {
    margin: 0;
    color: var(--muted);
    font-size: 0.875rem;
}

.payment-type-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: var(--text);
    background: var(--bg);
}

.table td {
    vertical-align: middle;
    border-top: 1px solid var(--border);
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-left: 1px solid var(--border);
}

/* Keyboard shortcut styling */
kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-family: 'Courier New', 'Cairo', monospace;
    color: var(--text);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    min-width: 20px;
    text-align: center;
    display: inline-block;
}

.btn-primary kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

.btn-info kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

/* Arabic keyboard shortcut styling */
kbd[lang="ar"] {
    font-family: 'Cairo', 'Courier New', monospace;
    font-weight: 600;
}

/* RTL Modal Header Adjustments */
.modal-header {
    direction: rtl;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    position: relative;
    padding: 1rem 1.5rem;
}

.modal-header .btn-close {
    order: -1;
    margin-left: 0;
    margin-right: 0;
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.modal-header .keyboard-hint {
    order: -1;
    position: absolute;
    left: 10% !important;
    right: auto !important;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
    z-index: 9;
    white-space: nowrap;
}

.modal-header .modal-title {
    order: 0;
    margin-right: auto;
    margin-left: 0;
    flex: 1;
    text-align: right;
    padding-right: 0;
    padding-left: 60px; /* Space for close button */
}

.modal-header:has(.keyboard-hint) .modal-title {
    padding-left: 120px; /* Extra space when keyboard-hint exists */
}

/* Keyboard shortcut hint in modal */
.keyboard-hint {
    position: absolute;
    top: 10px;
    left: 15px;
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.keyboard-hint kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.65rem;
    padding: 1px 4px;
}

/* Badge styling for dark mode */
.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

/* Text muted styling */
.text-muted {
    color: var(--muted) !important;
}

/* Form validation styling */
.was-validated .form-control:valid {
    border-color: #28a745;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
}

.was-validated .form-select:valid {
    border-color: #28a745;
}

.was-validated .form-select:invalid {
    border-color: #dc3545;
}

/* Spinner styling */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.1em;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .keyboard-hint {
        position: static;
        margin-top: 10px;
    }
}

p{
    color: var(--text) !important;
}
label{
    color: dodgerblue !important;
}

/* Modal centering — backdrop/z-index: sec-style.css + modal-kit.css */
.modal {
    align-items: center;
    justify-content: center;
    padding: 1rem !important;
}

.modal-dialog {
    margin: 0 auto;
    max-width: 500px;
}

.modal-dialog.modal-lg {
    max-width: 800px;
}

.modal-dialog.modal-xl {
    max-width: 1140px;
}

.modal-dialog.modal-sm {
    max-width: 300px;
}

.btn-outline-primary{
    color: white !important;
    background: var(--accent) !important;
    border: 1px solid var(--accent) !important;
    border-radius: 10px !important;
}

.btn-outline-primary:hover {
    background: rgb(0, 131, 201) !important;
    color: white !important;
}

.dark .btn-outline-primary{
    color: white !important;
    background: var(--accent) !important;
    border: 1px solid var(--accent) !important;
    border-radius: 10px !important;
}

.dark .btn-outline-primary:hover {
    background: rgb(0, 131, 201) !important;
    color: white !important;
}
.btn-outline-info{
    color: white !important;
    background:rgb(14, 229, 233) !important;
    border: 1px solid rgb(14, 229, 233) !important;
    border-radius: 10px !important;
}

.btn-outline-info:hover {
    background:rgb(0, 162, 165) !important;
    color: white !important;
}

.dark .btn-outline-info{
    color: white !important;
    background:rgb(14, 229, 233) !important;
    border: 1px solid rgb(14, 229, 233) !important;
    border-radius: 10px !important;
}   

.dark .btn-outline-info:hover {
    background:rgb(0, 162, 165) !important;
    color: white !important;
}
</style>
