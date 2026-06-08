<?php
    $visitCost = (float)($booking['visit_cost'] ?? 0);
    $totalPaid = (float)($booking['total_paid'] ?? 0);
    $remaining = max(0, $visitCost - $totalPaid);
    $patientDisplayName = trim($booking['patient_name'] ?? '');
    if ($patientDisplayName === '' && !empty($patient)) {
        $patientDisplayName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
    }
    if ($patientDisplayName === '') {
        $patientDisplayName = 'غير محدد';
    }
    $_clinicVisuals = [
        'riyadh' => ['icon' => 'bi-buildings-fill', 'color' => '#0d6efd'],
        'kfs'    => ['icon' => 'bi-hospital-fill',  'color' => '#10b981'],
    ];
?>
<link href="/app/Views/secretary/assets/css/details.css?v=<?= file_exists(__DIR__ . '/assets/css/details.css') ? filemtime(__DIR__ . '/assets/css/details.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<div class="sec-booking-details-page" dir="rtl">

<!-- Header -->
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h4 class="mb-1 arabic-text">
            <i class="bi bi-calendar-check me-2 text-primary"></i>
            تفاصيل الحجز
            <span class="text-muted fw-normal fs-6">#<?= (int)$booking['id'] ?></span>
        </h4>
        <p class="text-muted mb-0 arabic-text small">عرض كامل لبيانات الموعد والمريض والمدفوعات المرتبطة</p>
    </div>
    <div class="col-md-5">
        <div class="d-flex gap-2 justify-content-md-end flex-wrap mt-3 mt-md-0">
            <button type="button" class="sec-detail-btn sec-detail-btn--muted" onclick="window.history.back()">
                <i class="bi bi-arrow-right"></i>
                <span class="arabic-text">العودة</span>
            </button>
            <button type="button" class="sec-detail-btn sec-detail-btn--info" onclick="printBooking(<?= (int)$booking['id'] ?>)">
                <i class="bi bi-printer"></i>
                <span class="arabic-text">طباعة</span>
            </button>
        </div>
    </div>
</div>

<!-- Summary chips -->
<div class="sec-booking-summary mb-4">
    <div class="sec-booking-summary__chip arabic-text">
        <i class="bi bi-calendar3"></i>
        <?= date('Y-m-d', strtotime($booking['date'])) ?>
    </div>
    <div class="sec-booking-summary__chip arabic-text">
        <i class="bi bi-clock"></i>
        <?= date('H:i', strtotime($booking['start_time'])) ?> – <?= date('H:i', strtotime($booking['end_time'])) ?>
    </div>
    <div class="sec-booking-summary__chip arabic-text">
        <i class="bi bi-flag"></i>
        <span class="badge <?= $this->getBookingStatusBadgeClass($booking['status']) ?> arabic-text mb-0">
            <?= $this->getBookingStatusText($booking['status']) ?>
        </span>
    </div>
</div>

<div class="row mb-4 g-3">
    <!-- Booking info -->
    <div class="col-lg-8">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold arabic-text">
                    <i class="bi bi-info-circle me-2"></i>
                    معلومات الحجز
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="sec-detail-kv">
                            <div class="sec-detail-kv__icon"><i class="bi bi-person-badge"></i></div>
                            <div>
                                <div class="sec-detail-kv__label arabic-text">الطبيب</div>
                                <p class="sec-detail-kv__value arabic-text"><?= htmlspecialchars($booking['doctor_display_name'] ?? 'غير محدد') ?></p>
                            </div>
                        </div>
                        <div class="sec-detail-kv">
                            <div class="sec-detail-kv__icon"><i class="bi bi-heart-pulse"></i></div>
                            <div>
                                <div class="sec-detail-kv__label arabic-text">نوع الزيارة</div>
                                <p class="sec-detail-kv__value mb-0">
                                    <span class="badge <?= $this->getVisitTypeBadgeClass($booking['visit_type']) ?> arabic-text">
                                        <?= $this->getVisitTypeText($booking['visit_type']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <?php if (!empty($booking['clinic_name_ar']) || !empty($booking['clinic_name_en'])):
                            $_v = $_clinicVisuals[$booking['clinic_code'] ?? ''] ?? ['icon' => 'bi-building', 'color' => '#6c757d'];
                        ?>
                        <div class="sec-detail-kv">
                            <div class="sec-detail-kv__icon"><i class="bi bi-building"></i></div>
                            <div>
                                <div class="sec-detail-kv__label arabic-text">العيادة</div>
                                <p class="sec-detail-kv__value mb-0">
                                    <span class="clinic-tag arabic-text" style="--clinic-color: <?= $_v['color'] ?>;">
                                        <i class="bi <?= $_v['icon'] ?>"></i>
                                        <?= htmlspecialchars($booking['clinic_name_ar'] ?: $booking['clinic_name_en']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="sec-detail-kv">
                            <div class="sec-detail-kv__icon"><i class="bi bi-plus-circle"></i></div>
                            <div>
                                <div class="sec-detail-kv__label arabic-text">تاريخ الإنشاء</div>
                                <p class="sec-detail-kv__value arabic-text"><?= date('Y-m-d H:i', strtotime($booking['created_at'])) ?></p>
                            </div>
                        </div>
                        <div class="sec-detail-kv">
                            <div class="sec-detail-kv__icon"><i class="bi bi-arrow-repeat"></i></div>
                            <div>
                                <div class="sec-detail-kv__label arabic-text">آخر تحديث</div>
                                <p class="sec-detail-kv__value arabic-text"><?= date('Y-m-d H:i', strtotime($booking['updated_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($visitCost > 0): ?>
                <div class="sec-finance-strip arabic-text">
                    <div class="sec-finance-tile">
                        <div class="sec-finance-tile__label">تكلفة الزيارة</div>
                        <div class="sec-finance-tile__value"><?= number_format($visitCost, 2) ?> جنيه</div>
                    </div>
                    <div class="sec-finance-tile sec-finance-tile--paid">
                        <div class="sec-finance-tile__label">المدفوع</div>
                        <div class="sec-finance-tile__value"><?= number_format($totalPaid, 2) ?> جنيه</div>
                    </div>
                    <div class="sec-finance-tile sec-finance-tile--due">
                        <div class="sec-finance-tile__label">المتبقي</div>
                        <div class="sec-finance-tile__value"><?= number_format($remaining, 2) ?> جنيه</div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($booking['notes'])): ?>
                <div class="mt-4">
                    <label class="form-label fw-bold arabic-text text-muted small">ملاحظات</label>
                    <div class="sec-notes-box arabic-text"><?= nl2br(htmlspecialchars($booking['notes'])) ?></div>
                </div>
                <?php else: ?>
                <p class="text-muted arabic-text small mb-0 mt-3"><i class="bi bi-chat-left-text me-1"></i>لا توجد ملاحظات على هذا الحجز</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Patient -->
    <div class="col-lg-4">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold arabic-text">
                    <i class="bi bi-person-circle me-2"></i>
                    معلومات المريض
                </h6>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-lg me-3">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="mb-1 arabic-text patient-hover-name text-truncate" data-patient-id="<?= (int)($booking['patient_id'] ?? 0) ?>">
                            <?= htmlspecialchars($patientDisplayName) ?>
                        </h6>
                        <small class="text-muted arabic-text d-block" dir="ltr"><?= htmlspecialchars($booking['patient_phone'] ?? $patient['phone'] ?? 'غير محدد') ?></small>
                    </div>
                </div>

                <div class="mb-2">
                    <span class="badge bg-info arabic-text text-wrap text-start">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= htmlspecialchars($patient['address'] ?? $booking['patient_address'] ?? 'غير محدد') ?>
                    </span>
                </div>
                <div class="mb-3">
                    <span class="badge bg-secondary arabic-text">
                        <i class="bi bi-card-text me-1"></i>
                        <?= htmlspecialchars($patient['national_id'] ?? $booking['patient_national_id'] ?? 'غير محدد') ?>
                    </span>
                </div>

                <?php if (!empty($patient['dob']) || !empty($booking['dob'])): ?>
                <div class="text-muted arabic-text small mb-3">
                    <i class="bi bi-cake2 me-1"></i>
                    العمر: <?= $this->calculateAge($patient['dob'] ?? $booking['dob']) ?>
                </div>
                <?php endif; ?>

                <a href="/secretary/patients/<?= (int)($booking['patient_id'] ?? 0) ?>" class="sec-detail-btn sec-detail-btn--primary mt-auto">
                    <i class="bi bi-folder2-open"></i>
                    <span class="arabic-text">عرض ملف المريض</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Payments -->
<?php if (!empty($payments)): ?>
<div class="card shadow dashboard-card mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold arabic-text">
            <i class="bi bi-credit-card me-2"></i>
            المدفوعات المرتبطة
        </h6>
        <span class="badge bg-success arabic-text"><?= count($payments) ?> دفعة</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="arabic-text text-end">التاريخ</th>
                        <th class="arabic-text text-end">المبلغ</th>
                        <th class="arabic-text text-end">النوع</th>
                        <th class="arabic-text text-end">طريقة الدفع</th>
                        <th class="arabic-text text-end">استلمها</th>
                        <th class="arabic-text text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td class="arabic-text"><?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?></td>
                        <td><span class="fw-bold text-success arabic-text"><?= number_format((float)$payment['amount'], 2) ?> جنيه</span></td>
                        <td>
                            <span class="badge <?= $this->getPaymentTypeBadgeClass($payment['type'] ?? 'other') ?> arabic-text">
                                <?= $this->getPaymentTypeText($payment['type'] ?? 'other') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getPaymentMethodBadgeClass($payment['method'] ?? 'Cash') ?> arabic-text">
                                <?= $this->getPaymentMethodText($payment['method'] ?? 'Cash') ?>
                            </span>
                        </td>
                        <td class="arabic-text text-muted"><?= htmlspecialchars($payment['received_by_name'] ?? '—') ?></td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="sec-detail-btn sec-detail-btn--sm sec-detail-btn--sky"
                                        onclick="viewPayment(<?= (int)$payment['id'] ?>)"
                                        title="عرض تفاصيل الدفعة">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="sec-detail-btn sec-detail-btn--sm sec-detail-btn--info"
                                        onclick="printReceipt(<?= (int)$payment['id'] ?>)"
                                        title="طباعة الإيصال">
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

<!-- Related bookings -->
<?php if (!empty($relatedBookings)): ?>
<div class="card shadow dashboard-card related-bookings-card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold arabic-text">
            <i class="bi bi-list-ul me-2"></i>
            حجوزات أخرى لنفس المريض
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="arabic-text text-end">التاريخ</th>
                        <th class="arabic-text text-end">الوقت</th>
                        <th class="arabic-text text-end">نوع الزيارة</th>
                        <th class="arabic-text text-end">الحالة</th>
                        <th class="arabic-text text-end">الطبيب</th>
                        <th class="arabic-text text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relatedBookings as $relatedBooking): ?>
                    <tr>
                        <td class="arabic-text"><?= date('Y-m-d', strtotime($relatedBooking['date'])) ?></td>
                        <td class="arabic-text"><?= date('H:i', strtotime($relatedBooking['start_time'])) ?></td>
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
                        <td class="arabic-text"><?= htmlspecialchars($relatedBooking['doctor_name'] ?? 'غير محدد') ?></td>
                        <td>
                            <button type="button" class="sec-detail-btn sec-detail-btn--sm sec-detail-btn--primary"
                                    onclick="viewBooking(<?= (int)$relatedBooking['id'] ?>)"
                                    title="عرض تفاصيل الحجز">
                                <i class="bi bi-eye"></i>
                                <span class="arabic-text d-none d-md-inline">عرض</span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(el);
        }
    });
});

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
    window.open(`/secretary/bookings/${bookingId}/print`, '_blank');
}
</script>
