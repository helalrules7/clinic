<link href="/app/Views/secretary/assets/css/details.css?v=<?= file_exists(__DIR__ . '/assets/css/details.css') ? filemtime(__DIR__ . '/assets/css/details.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/../../doctor/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../../doctor/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

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
                <?php $__td = (!empty($appointments) && !empty($appointments[0]['doctor_name'])) ? $appointments[0]['doctor_name'] : ''; ?>
                <?php if ($__td): ?>
                <p class="text-muted mb-0 arabic-text small"><i class="bi bi-person-badge me-1"></i>الطبيب المعالج: <?= htmlspecialchars($__td) ?></p>
                <?php endif; ?>
                <div id="secProfileOrg" class="mt-2 d-flex align-items-center gap-2 flex-wrap" data-patient="<?= (int)$patient['id'] ?>"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end flex-wrap sec-pa-actions">
            <a href="/secretary/bookings?patient_id=<?= $patient['id'] ?>" class="sec-pa-btn sec-pa-btn--book">
                <i class="bi bi-calendar-plus"></i>حجز موعد جديد
            </a>
            <a href="/secretary/payments?patient_id=<?= $patient['id'] ?>" class="sec-pa-btn sec-pa-btn--pay">
                <i class="bi bi-credit-card"></i>المعاملات المالية
            </a>
            <a href="/secretary/patients/<?= $patient['id'] ?>/invoice" target="_blank" class="sec-pa-btn sec-pa-btn--invoice">
                <i class="bi bi-receipt"></i>طباعة الفاتورة
            </a>
            <button type="button" class="sec-pa-btn sec-pa-btn--edit" id="secEditPatientBtn">
                <i class="bi bi-pencil"></i>تعديل البيانات
            </button>
        </div>
    </div>
</div>

<!-- Patient Information Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
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
                <?php if (!empty($patient['clinic_name_ar']) || !empty($patient['clinic_name_en'])): ?>
                <?php
                    $_clinicVisuals = [
                        'riyadh' => ['icon' => 'bi-buildings-fill', 'color' => '#0d6efd'],
                        'kfs'    => ['icon' => 'bi-hospital-fill',  'color' => '#10b981'],
                    ];
                    $_v = $_clinicVisuals[$patient['clinic_code'] ?? ''] ?? ['icon' => 'bi-building', 'color' => '#6c757d'];
                ?>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong class="arabic-text">العيادة:</strong>
                    </div>
                    <div class="col-sm-8 arabic-text">
                        <span class="clinic-tag" style="--clinic-color: <?= $_v['color'] ?>;">
                            <i class="bi <?= $_v['icon'] ?>"></i>
                            <?= htmlspecialchars($patient['clinic_name_ar'] ?: $patient['clinic_name_en']) ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
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
        <div class="card shadow dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
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
                            <thead class="table-dark">
                                <tr>
                                    <th class="arabic-text text-end" dir="rtl">التاريخ</th>
                                    <th class="arabic-text text-end" dir="rtl">الوقت</th>
                                    <th class="arabic-text text-end" dir="rtl">الطبيب</th>
                                    <th class="arabic-text text-end" dir="rtl">نوع الزيارة</th>
                                    <th class="arabic-text text-end" dir="rtl">الحالة</th>
                                    <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
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
                                            <a href="/secretary/bookings/<?= (int) $appointment['id'] ?>" class="btn btn-sm btn-outline-primary"
                                               onclick="if (window.navigateToSecretaryBooking) { event.preventDefault(); window.navigateToSecretaryBooking(<?= (int) $appointment['id'] ?>); }">
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
        <div class="card shadow dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-credit-card me-2"></i>
                    المدفوعات الأخيرة
                </h6>
                <span class="badge bg-success"><?= count($payments) ?> دفعة</span>
            </div>
            <div class="card-body">
                <?php $__totalPaid = 0; foreach ($payments as $__p) { $__totalPaid += (float)($__p['amount'] ?? 0); } ?>
                <div class="sec-fin-summary mb-3">
                    <div class="sec-fin-item"><span class="sec-fin-label arabic-text">إجمالي المدفوع</span><span class="sec-fin-value text-success"><?= number_format($__totalPaid, 2) ?> ج.م</span></div>
                    <div class="sec-fin-item"><span class="sec-fin-label arabic-text">عدد الدفعات</span><span class="sec-fin-value"><?= count($payments) ?></span></div>
                    <?php if (!empty($payments)): ?>
                    <div class="sec-fin-item"><span class="sec-fin-label arabic-text">آخر دفعة</span><span class="sec-fin-value"><?= number_format((float)($payments[0]['amount'] ?? 0), 2) ?> ج.م</span></div>
                    <?php endif; ?>
                </div>
                <?php if (empty($payments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 arabic-text">لا توجد مدفوعات مسجلة</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th class="arabic-text text-end" dir="rtl">التاريخ</th>
                                    <th class="arabic-text text-end" dir="rtl">المبلغ</th>
                                    <th class="arabic-text text-end" dir="rtl">النوع</th>
                                    <th class="arabic-text text-end" dir="rtl">الطريقة</th>
                                    <th class="arabic-text text-end" dir="rtl">الوصف</th>
                                    <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
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

<!-- v11: Administrative documents (secretary scope: audience='administrative') -->
<div class="row mb-4 mt-4 pt-2">
  <div class="col-12">
    <div class="card shadow dashboard-card">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary arabic-text"><i class="bi bi-folder2-open me-2"></i>المستندات الإدارية</h6>
        <div class="d-flex gap-2 align-items-center">
          <select class="form-select form-select-sm" id="secFileCategory" style="width:auto">
            <option value="id">هوية</option>
            <option value="insurance">تأمين</option>
            <option value="receipt">إيصال</option>
            <option value="other">أخرى</option>
          </select>
          <label class="btn btn-sm btn-primary mb-0"><i class="bi bi-upload me-1"></i>رفع مستند<input type="file" id="secFileInput" hidden accept="image/*,.pdf,.doc,.docx,.txt"></label>
        </div>
      </div>
      <div class="card-body" id="secFilesBody" data-patient="<?= (int)$patient['id'] ?>">
        <div class="text-muted arabic-text">جارٍ التحميل…</div>
      </div>
    </div>
  </div>
</div>

<style>
/* RTL — icon spacing: sec-style.css §secretary icon spacing */

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

<!-- v11: Edit patient (basics) modal — secretary operational scope, no delete -->
<div class="modal fade" id="secEditPatientModal" tabindex="-1" dir="rtl" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title arabic-text"><i class="bi bi-pencil me-2"></i>تعديل بيانات المريض</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      <div class="modal-body">
        <div id="secEditMsg" class="alert d-none arabic-text" role="alert"></div>
        <form id="secEditForm" class="row g-3">
          <div class="col-md-6"><label class="form-label arabic-text">الاسم الأول</label><input class="form-control" name="first_name" maxlength="50" value="<?= htmlspecialchars($patient['first_name'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label arabic-text">اسم العائلة</label><input class="form-control" name="last_name" maxlength="50" value="<?= htmlspecialchars($patient['last_name'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label arabic-text">الجنس</label><select class="form-select" name="gender"><option value="Male"<?= ($patient['gender'] ?? '') === 'Male' ? ' selected' : '' ?>>ذكر</option><option value="Female"<?= ($patient['gender'] ?? '') === 'Female' ? ' selected' : '' ?>>أنثى</option></select></div>
          <div class="col-md-6"><label class="form-label arabic-text">تاريخ الميلاد</label><input type="date" class="form-control" name="dob" value="<?= htmlspecialchars($patient['dob'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label arabic-text">الهاتف</label><input class="form-control" name="phone" maxlength="20" value="<?= htmlspecialchars($patient['phone'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label arabic-text">هاتف بديل</label><input class="form-control" name="alt_phone" maxlength="20" value="<?= htmlspecialchars($patient['alt_phone'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label arabic-text">الرقم القومي</label><input class="form-control" name="national_id" maxlength="20" value="<?= htmlspecialchars($patient['national_id'] ?? '') ?>"></div>
          <div class="col-md-6"><label class="form-label arabic-text">العنوان</label><input class="form-control" name="address" maxlength="500" value="<?= htmlspecialchars($patient['address'] ?? '') ?>"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="button" class="btn btn-primary" id="secEditSave"><i class="bi bi-check-lg me-1"></i>حفظ</button>
      </div>
    </div>
  </div>
</div>
<script src="/app/Views/secretary/assets/js/secretary-patient-profile.js?v=<?= file_exists(__DIR__ . '/assets/js/secretary-patient-profile.js') ? filemtime(__DIR__ . '/assets/js/secretary-patient-profile.js') : time() ?>"></script>
<script>
(function () {
    var pid = <?= (int)$patient['id'] ?>;
    var btn = document.getElementById('secEditPatientBtn');
    var modalEl = document.getElementById('secEditPatientModal');
    if (!btn || !modalEl) return;
    // Create the instance lazily at click time — this inline script runs during
    // parse, before the layout's Bootstrap bundle (echoed after the content) loads.
    btn.addEventListener('click', function () {
        if (window.bootstrap && bootstrap.Modal) { bootstrap.Modal.getOrCreateInstance(modalEl).show(); }
    });
    document.getElementById('secEditSave').addEventListener('click', function () {
        var save = this, form = document.getElementById('secEditForm'), msg = document.getElementById('secEditMsg');
        var data = {};
        Array.prototype.forEach.call(form.elements, function (el) { if (el.name) data[el.name] = el.value; });
        if (!(data.first_name || '').trim() || !(data.last_name || '').trim()) {
            msg.className = 'alert alert-warning arabic-text'; msg.textContent = 'الاسم الأول واسم العائلة مطلوبان'; return;
        }
        save.disabled = true; save.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        fetch('/api/secretary/patients/' + pid + '/update', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify(data)
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (res && res.ok) { location.reload(); }
            else { msg.className = 'alert alert-danger arabic-text'; msg.textContent = (res && res.error) || 'تعذّر الحفظ'; save.disabled = false; save.innerHTML = 'حفظ'; }
        }).catch(function () { msg.className = 'alert alert-danger arabic-text'; msg.textContent = 'خطأ في الاتصال'; save.disabled = false; save.innerHTML = 'حفظ'; });
    });
})();
</script>
