<!-- Booking Details Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-calendar-check me-2"></i>
                تفاصيل الحجز
            </h4>
        </div>
        <p class="text-muted mb-0 arabic-text">عرض تفاصيل الحجز رقم <?= $booking['id'] ?></p>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-outline-primary" onclick="window.history.back()">
                <i class="bi bi-arrow-right me-2"></i>
                العودة
            </button>
            <button class="btn btn-outline-info" onclick="printBooking(<?= $booking['id'] ?>)">
                <i class="bi bi-printer me-2"></i>
                طباعة
            </button>
        </div>
    </div>
</div>

<!-- Booking Information -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-info-circle me-2"></i>
                    معلومات الحجز
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">تاريخ الموعد:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-calendar me-2"></i>
                                <?= date('Y-m-d', strtotime($booking['date'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">وقت الموعد:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-clock me-2"></i>
                                <?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">نوع الزيارة:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getVisitTypeBadgeClass($booking['visit_type']) ?> arabic-text">
                                    <?= $this->getVisitTypeText($booking['visit_type']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">حالة الحجز:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getBookingStatusBadgeClass($booking['status']) ?> arabic-text">
                                    <?= $this->getBookingStatusText($booking['status']) ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">تاريخ الإنشاء:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-calendar me-2"></i>
                                <?= date('Y-m-d H:i', strtotime($booking['created_at'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">آخر تحديث:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-clock me-2"></i>
                                <?= date('Y-m-d H:i', strtotime($booking['updated_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($booking['notes'])): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold arabic-text">ملاحظات:</label>
                    <div class="form-control-plaintext bg-light p-3 rounded arabic-text">
                        <?= nl2br(htmlspecialchars($booking['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-person-circle me-2"></i>
                    معلومات المريض
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-lg me-3">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 arabic-text"><?= htmlspecialchars($booking['patient_name'] ?? 'غير محدد') ?></h6>
                        <small class="text-muted arabic-text"><?= $booking['patient_phone'] ?? 'غير محدد' ?></small>
                    </div>
                </div>
                
                <div class="mb-2">
                    <span class="badge bg-info arabic-text">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= $booking['patient_address'] ?? 'غير محدد' ?>
                    </span>
                </div>
                
                <div class="mb-2">
                    <span class="badge bg-secondary arabic-text">
                        <i class="bi bi-card-text me-1"></i>
                        <?= $booking['patient_national_id'] ?? 'غير محدد' ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-person-badge me-2"></i>
                    معلومات الطبيب
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 arabic-text"><?= htmlspecialchars($doctor['name'] ?? 'غير محدد') ?></h6>
                        <small class="text-muted arabic-text">طبيب</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payments Section -->
<?php if (!empty($payments)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 arabic-text">
            <i class="bi bi-credit-card me-2"></i>
            المدفوعات المرتبطة
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
                        <th class="arabic-text">الوصف</th>
                        <th class="arabic-text">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar me-2 text-primary"></i>
                                <?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-success"><?= number_format($payment['amount'], 2) ?> جنيه</span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getPaymentTypeBadgeClass($payment['type'] ?? 'other') ?> arabic-text">
                                <?= $this->getPaymentTypeText($payment['type'] ?? 'other') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getPaymentMethodBadgeClass($payment['method'] ?? 'cash') ?> arabic-text">
                                <?= $this->getPaymentMethodText($payment['method'] ?? 'cash') ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-muted arabic-text"><?= $payment['description'] ?? 'لا يوجد وصف' ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="viewPayment(<?= $payment['id'] ?>)"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="عرض تفاصيل الدفعة">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" 
                                        onclick="printReceipt(<?= $payment['id'] ?>)"
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
<?php endif; ?>

<!-- Related Bookings -->
<?php if (!empty($relatedBookings)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 arabic-text">
            <i class="bi bi-list-ul me-2"></i>
            حجوزات أخرى لنفس المريض
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text">التاريخ</th>
                        <th class="arabic-text">الوقت</th>
                        <th class="arabic-text">نوع الزيارة</th>
                        <th class="arabic-text">الحالة</th>
                        <th class="arabic-text">الطبيب</th>
                        <th class="arabic-text">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relatedBookings as $relatedBooking): ?>
                    <tr <?= $relatedBooking['id'] == $booking['id'] ? 'class="table-active"' : '' ?>>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar me-2 text-primary"></i>
                                <?= date('Y-m-d', strtotime($relatedBooking['date'])) ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock me-2 text-info"></i>
                                <?= date('H:i', strtotime($relatedBooking['start_time'])) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $this->getVisitTypeBadgeClass($relatedBooking['visit_type']) ?> arabic-text">
                                <?= $this->getVisitTypeText($relatedBooking['visit_type']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getBookingStatusBadgeClass($relatedBooking['status']) ?> arabic-text">
                                <?= $this->getBookingStatusText($relatedBooking['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($relatedBooking['doctor_name'] ?? 'غير محدد') ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($relatedBooking['id'] != $booking['id']): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    onclick="viewBooking(<?= $relatedBooking['id'] ?>)"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="عرض تفاصيل الحجز">
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
function viewBooking(bookingId) {
    window.location.href = `/secretary/bookings/${bookingId}`;
}

function viewPayment(paymentId) {
    window.location.href = `/secretary/payments/${paymentId}`;
}

function printReceipt(paymentId) {
    window.open(`/secretary/payments/${paymentId}/receipt`, '_blank');
}

function printBooking(bookingId) {
    // Open print page in new window
    window.open(`/secretary/bookings/${bookingId}/print`, '_blank');
}
</script>

<style>
/* RTL specific adjustments */
.me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
.me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
.ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
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

.avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    font-size: 1.2rem;
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
