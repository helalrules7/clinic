<!-- Patient Details Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <div class="avatar-circle avatar-<?= $patient['gender'] === 'Female' ? 'female' : 'male' ?> me-3" style="width: 60px; height: 60px;">
                <i class="bi bi-person-fill" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h4 class="mb-1 arabic-text">
                    <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                </h4>
                <p class="text-muted mb-0 arabic-text">
                    <i class="bi bi-person me-1"></i>
                    <?= $patient['gender'] === 'Female' ? 'أنثى' : 'ذكر' ?>
                    <?php if ($patient['dob']): ?>
                        • <?= $viewHelper->calculateAge($patient['dob']) ?> سنة
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <a href="/secretary/bookings?patient_id=<?= $patient['id'] ?>" class="btn btn-success">
                <i class="bi bi-calendar-plus me-2"></i>
                حجز موعد جديد
            </a>
            <a href="/secretary/payments?patient_id=<?= $patient['id'] ?>" class="btn btn-info">
                <i class="bi bi-credit-card me-2"></i>
                المعاملات المالية
            </a>
        </div>
    </div>
</div>

<!-- Patient Information Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 arabic-text">
                    <i class="bi bi-person me-2"></i>
                    المعلومات الشخصية
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">الاسم الكامل:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">الجنس:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <?= $patient['gender'] === 'Female' ? 'أنثى' : 'ذكر' ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">تاريخ الميلاد:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <?= $patient['dob'] ? $viewHelper->formatDate($patient['dob']) : 'غير محدد' ?>
                        <?php if ($patient['dob']): ?>
                            (<?= $viewHelper->calculateAge($patient['dob']) ?> سنة)
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($patient['national_id']): ?>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">الرقم القومي:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <?= htmlspecialchars($patient['national_id']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 arabic-text">
                    <i class="bi bi-telephone me-2"></i>
                    معلومات الاتصال
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">الهاتف الرئيسي:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <i class="bi bi-telephone me-1"></i>
                        <?= htmlspecialchars($patient['phone']) ?>
                    </div>
                </div>
                <?php if ($patient['alt_phone']): ?>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">هاتف بديل:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <i class="bi bi-telephone-plus me-1"></i>
                        <?= htmlspecialchars($patient['alt_phone']) ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($patient['emergency_contact']): ?>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">جهة الطوارئ:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <i class="bi bi-person-heart me-1"></i>
                        <?= htmlspecialchars($patient['emergency_contact']) ?>
                        <?php if ($patient['emergency_phone']): ?>
                            - <?= htmlspecialchars($patient['emergency_phone']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($patient['address']): ?>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">العنوان:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= htmlspecialchars($patient['address']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Appointments -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 arabic-text">
                    <i class="bi bi-calendar-check me-2"></i>
                    المواعيد الأخيرة
                </h6>
                <span class="badge bg-primary"><?= count($appointments) ?> موعد</span>
            </div>
            <div class="card-body">
                <?php if (empty($appointments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 arabic-text">لا توجد مواعيد مسجلة</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="arabic-text">التاريخ</th>
                                    <th class="arabic-text">الوقت</th>
                                    <th class="arabic-text">الطبيب</th>
                                    <th class="arabic-text">نوع الزيارة</th>
                                    <th class="arabic-text">الحالة</th>
                                    <th class="arabic-text">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td class="arabic-text">
                                            <?= $viewHelper->formatDate($appointment['date']) ?>
                                        </td>
                                        <td class="arabic-text">
                                            <?= date('H:i', strtotime($appointment['start_time'])) ?>
                                        </td>
                                        <td class="arabic-text">
                                            <?= htmlspecialchars($appointment['doctor_name']) ?>
                                            <?php if ($appointment['specialization']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($appointment['specialization']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $viewHelper->getVisitTypeBadgeClass($appointment['visit_type']) ?> arabic-text">
                                                <?= $viewHelper->getVisitTypeText($appointment['visit_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $viewHelper->getBookingStatusBadgeClass($appointment['status']) ?> arabic-text">
                                                <?= $viewHelper->getBookingStatusText($appointment['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/secretary/bookings/<?= $appointment['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 arabic-text">
                    <i class="bi bi-credit-card me-2"></i>
                    المدفوعات الأخيرة
                </h6>
                <span class="badge bg-success"><?= count($payments) ?> دفعة</span>
            </div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 arabic-text">لا توجد مدفوعات مسجلة</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="arabic-text">التاريخ</th>
                                    <th class="arabic-text">المبلغ</th>
                                    <th class="arabic-text">النوع</th>
                                    <th class="arabic-text">الطريقة</th>
                                    <th class="arabic-text">الوصف</th>
                                    <th class="arabic-text">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td class="arabic-text">
                                            <?= $viewHelper->formatDate($payment['created_at']) ?>
                                        </td>
                                        <td class="arabic-text">
                                            <strong><?= number_format($payment['amount'], 2) ?> جنيه</strong>
                                        </td>
                                        <td>
                                            <span class="badge <?= $viewHelper->getPaymentTypeBadgeClass($payment['type']) ?> arabic-text">
                                                <?= $viewHelper->getPaymentTypeText($payment['type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $viewHelper->getPaymentMethodBadgeClass($payment['method']) ?> arabic-text">
                                                <?= $viewHelper->getPaymentMethodText($payment['method']) ?>
                                            </span>
                                        </td>
                                        <td class="arabic-text">
                                            <?= $payment['description'] ? htmlspecialchars($payment['description']) : '-' ?>
                                        </td>
                                        <td>
                                            <a href="/secretary/payments/<?= $payment['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* RTL specific adjustments */
.me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
.me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
.ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
.ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }

/* Arabic text styling */
.arabic-text {
    font-family: 'Cairo', Arial, sans-serif;
    direction: rtl;
    text-align: right;
}

/* Avatar styling */
.avatar-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.avatar-male {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.avatar-female {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

/* Card styling */
.card {
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    font-weight: 600;
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
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

/* Dark mode support */
.dark .card {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .card-header {
    background-color: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

.dark .table th {
    background: var(--bg);
    color: var(--text);
    border-color: var(--border);
}

.dark .table td {
    color: var(--text);
    border-color: var(--border);
}

.dark .text-muted {
    color: var(--muted) !important;
}
</style>
