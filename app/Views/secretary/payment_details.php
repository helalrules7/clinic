<!-- Payment Details Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-credit-card me-2"></i>
                تفاصيل الدفعة رقم <?= $payment['id'] ?>
            </h4>
        </div>
        <p class="text-muted mb-0 arabic-text">عرض تفاصيل الدفعة والبيانات المرتبطة بها</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-primary" 
                    onclick="printReceipt(<?= $payment['id'] ?>)"
                    title="طباعة الإيصال">
                <i class="bi bi-printer me-2"></i>
                طباعة الإيصال
            </button>
            <button class="btn btn-secondary" 
                    onclick="window.history.back()"
                    title="العودة">
                <i class="bi bi-arrow-right me-2"></i>
                العودة
            </button>
        </div>
    </div>
</div>

<!-- Payment Information -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-info-circle me-2"></i>
                    معلومات الدفعة
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">رقم الدفعة:</label>
                            <p class="form-control-plaintext"><?= $payment['id'] ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">المبلغ:</label>
                            <p class="form-control-plaintext text-success fw-bold"><?= number_format($payment['amount'], 2) ?> جنيه</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">نوع الدفعة:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getPaymentTypeBadgeClass($payment['type']) ?> arabic-text">
                                    <?= $this->getPaymentTypeText($payment['type']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">طريقة الدفع:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getPaymentMethodBadgeClass($payment['method']) ?> arabic-text">
                                    <?= $this->getPaymentMethodText($payment['method']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">تاريخ الدفعة:</label>
                            <p class="form-control-plaintext"><?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">استلمها:</label>
                            <p class="form-control-plaintext"><?= $payment['received_by_name'] ?? 'غير محدد' ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($payment['description'])): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold arabic-text">الوصف:</label>
                    <p class="form-control-plaintext"><?= htmlspecialchars($payment['description']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($payment['discount_amount'] > 0): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold arabic-text text-warning">مبلغ الخصم:</label>
                    <p class="form-control-plaintext text-warning fw-bold"><?= number_format($payment['discount_amount'], 2) ?> جنيه</p>
                </div>
                <?php endif; ?>
                
                <?php if ($payment['is_exempt']): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold arabic-text text-info">إعفاء:</label>
                    <p class="form-control-plaintext text-info fw-bold">معفى من الدفع</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-person me-2"></i>
                    بيانات المريض
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">الاسم:</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($payment['patient_name']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">رقم الهاتف:</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($payment['patient_phone'] ?? 'غير محدد') ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">العنوان:</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($payment['patient_address'] ?? 'غير محدد') ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">رقم الهوية:</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($payment['patient_national_id'] ?? 'غير محدد') ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="/secretary/patients/<?= $payment['patient_id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person me-1"></i>
                        عرض ملف المريض
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Information (if exists) -->
<?php if ($appointment): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-calendar me-2"></i>
                    تفاصيل الموعد المرتبط
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">رقم الموعد:</label>
                            <p class="form-control-plaintext"><?= $appointment['id'] ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">التاريخ:</label>
                            <p class="form-control-plaintext"><?= date('Y-m-d', strtotime($appointment['date'])) ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">الوقت:</label>
                            <p class="form-control-plaintext"><?= $appointment['start_time'] ?> - <?= $appointment['end_time'] ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">الطبيب:</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($appointment['doctor_name'] ?? 'غير محدد') ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">نوع الزيارة:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getVisitTypeBadgeClass($appointment['visit_type']) ?> arabic-text">
                                    <?= $appointment['visit_type'] ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">الحالة:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getStatusBadgeClass($appointment['status']) ?> arabic-text">
                                    <?= $this->getStatusText($appointment['status']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($appointment['notes'])): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold arabic-text">ملاحظات:</label>
                    <p class="form-control-plaintext"><?= htmlspecialchars($appointment['notes']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="/secretary/bookings/<?= $appointment['id'] ?>" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-calendar me-1"></i>
                        عرض تفاصيل الموعد
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Related Payments -->
<?php if (!empty($relatedPayments)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-list-ul me-2"></i>
                    مدفوعات أخرى لنفس المريض
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="arabic-text">التاريخ</th>
                                <th class="arabic-text">المبلغ</th>
                                <th class="arabic-text">النوع</th>
                                <th class="arabic-text">طريقة الدفع</th>
                                <th class="arabic-text">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($relatedPayments as $relatedPayment): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i', strtotime($relatedPayment['created_at'])) ?></td>
                                    <td>
                                        <span class="fw-bold text-success"><?= number_format($relatedPayment['amount'], 2) ?> جنيه</span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $this->getPaymentTypeBadgeClass($relatedPayment['type']) ?> arabic-text">
                                            <?= $this->getPaymentTypeText($relatedPayment['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $this->getPaymentMethodBadgeClass($relatedPayment['method']) ?> arabic-text">
                                            <?= $this->getPaymentMethodText($relatedPayment['method']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    onclick="viewPayment(<?= $relatedPayment['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="عرض تفاصيل الدفعة">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    onclick="printReceipt(<?= $relatedPayment['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="طباعة الإيصال">
                                                <i class="bi bi-printer"></i>
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
</div>
<?php endif; ?>

<script>
function printReceipt(paymentId) {
    window.open(`/secretary/payments/${paymentId}/receipt`, '_blank');
}

function viewPayment(paymentId) {
    window.location.href = `/secretary/payments/${paymentId}`;
}

// Initialize Bootstrap Tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
});
</script>

<style>
/* RTL specific adjustments */
.me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
.me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
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

.card-header {
    background: var(--bg);
    border-bottom: 2px solid var(--accent);
}

.card-header h5 {
    color: var(--accent);
    font-weight: 600;
}

/* Form styling */
.form-label {
    color: var(--text);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-control-plaintext {
    color: var(--text);
    font-weight: 500;
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
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

/* Button styling */
.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-left: 1px solid var(--border);
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
p{
    color: var(--text) !important;
}
label{
    color: dodgerblue !important;
}
</style>
