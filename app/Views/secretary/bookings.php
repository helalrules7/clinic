<?php
    // Server-render the secretary's clinic so the dropdown ships its options
    // in the initial HTML (no JS fetch, no race with Bootstrap modal show).
    try {
        $__cal_pdo  = \App\Config\Database::getInstance()->getConnection();
        $__cal_auth = new \App\Lib\Auth();
        $__cal_user = $__cal_auth->user();
        if (!empty($__cal_user['clinic_id'])) {
            $__s = $__cal_pdo->prepare("SELECT id, code, name_ar, name_en FROM clinics WHERE is_active = 1 AND id = ? ORDER BY sort_order, id");
            $__s->execute([(int)$__cal_user['clinic_id']]);
            $__calClinics = $__s->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $__calClinics = $__cal_pdo->query("SELECT id, code, name_ar, name_en FROM clinics WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(\PDO::FETCH_ASSOC);
        }
    } catch (\Throwable $__e) {
        $__calClinics = [];
    }
    /* Per-clinic visual identity (icon + colour). Used to prepend an icon
       next to every clinic <select> across the booking forms so the user
       can recognise their clinic at a glance — matches the same map used
       on the doctor calendar page. */
    $__clinicVisuals = [
        'riyadh' => ['icon' => 'bi-buildings-fill', 'color' => '#0d6efd'],
        'kfs'    => ['icon' => 'bi-hospital-fill',  'color' => '#10b981'],
    ];
    $__c0 = $__calClinics[0] ?? null;
    $__v0 = $__c0 ? ($__clinicVisuals[$__c0['code']] ?? ['icon' => 'bi-building', 'color' => '#6c757d']) : ['icon' => 'bi-building', 'color' => '#6c757d'];
?>
<link href="/app/Views/doctor/assets/css/calendar.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/calendar.css') ? filemtime(__DIR__ . '/../doctor/assets/css/calendar.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/bookings.css?v=<?= file_exists(__DIR__ . '/assets/css/bookings.css') ? filemtime(__DIR__ . '/assets/css/bookings.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../doctor/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<!-- Bookings Header -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-calendar-check me-2"></i>
                إدارة الحجوزات
            </h4>
            <div class="d-flex align-items-center ms-3" style="padding-bottom: 10px !important;">
                <label class="form-label mb-0 me-2" for="bookingsAutoRefresh">
                    <small class="text-muted arabic-text">تحديث تلقائي</small>
                </label>
                <div class="toggle-switch-wrapper">
                    <input type="checkbox" class="toggle-switch" id="bookingsAutoRefresh" 
                           onchange="toggleBookingsAutoRefresh(this.checked)">
                </div>
            </div>
        </div>
        <p class="text-muted mb-0 arabic-text">إنشاء وإدارة مواعيد المرضى</p>
        <div class="mt-2">
            <small class="text-muted arabic-text">
                <i class="bi bi-keyboard me-1"></i>
                اختصارات: 
                • حجز جديد <kbd class="me-1">N</kbd> أو <kbd class="me-1">ى</kbd> أو <kbd class="me-1">Ctrl+N</kbd> 
                • البحث <kbd class="me-1">F</kbd> أو <kbd class="me-1">ب</kbd>
                <kbd>Esc</kbd> إغلاق
            </small>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex gap-2 justify-content-end flex-wrap">
            <button type="button" class="btn btn-info" id="goToDateBtn"
                    data-bs-toggle="popover"
                    data-bs-placement="bottom"
                    data-bs-html="true"
                    data-bs-content="<div class='date-picker-tooltip'><label class='form-label mb-2 arabic-text'>اختر التاريخ:</label><input type='date' id='tooltipDatePicker' class='form-control'><button type='button' class='btn btn-sm btn-outline-info w-100 mt-2 arabic-text' onclick='goToSelectedDate()'>انتقل للتاريخ</button></div>"
                    data-bs-trigger="click">
                <i class="bi bi-calendar-event me-1"></i>
                انتقل لتاريخ
            </button>
            <button type="button" class="btn btn-success" id="addBookingBtn">
                <i class="bi bi-calendar-plus me-2"></i>
                حجز جديد
                <span class="ms-2">
                    <kbd>N</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ى</kbd>
                </span>
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" id="todayBtn">اليوم</button>
                <button type="button" class="btn btn-outline-primary" id="prevDayBtn">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="nextDayBtn">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bookings Statistics -->
<div class="row mb-4 stats-cards-wrapper">
    <div class="col-md-3 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-primary">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text">إجمالي الحجوزات</h4>
                        <h3 class="stats-card-value arabic-text" id="totalBookings">0</h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-success">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text">في الإنتظار</h4>
                        <h3 class="stats-card-value arabic-text" id="pendingBookings">0</h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar2-range"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-info">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text">تم الحضور</h4>
                        <h3 class="stats-card-value arabic-text" id="checkedInBookings">0</h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-warning">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text">مكتملة</h4>
                        <h3 class="stats-card-value arabic-text" id="completedBookings">0</h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar-heart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- فلاتر الوقت -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-none d-md-flex gap-2 flex-wrap align-items-center">
            <span class="text-muted me-2 arabic-text"><i class="bi bi-funnel me-1"></i>تصفية الأوقات:</span>
            <button type="button" class="btn btn-sm btn-outline-info filter-time-btn arabic-text" data-filter="2pm-6pm" id="filter2pm6pm">
                <i class="bi bi-clock me-1"></i>٢:٠٠ م – ٦:٠٠ م
            </button>
            <button type="button" class="btn btn-sm btn-outline-success filter-time-btn arabic-text" data-filter="6pm-1045pm" id="filter6pm1045pm">
                <i class="bi bi-clock me-1"></i>٦:٠٠ م – ١٠:٤٥ م
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary filter-time-btn arabic-text" data-filter="available" id="filterAvailable">
                <i class="bi bi-check-circle me-1"></i>المتاح فقط
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning filter-time-btn arabic-text" data-filter="unavailable" id="filterUnavailable">
                <i class="bi bi-x-circle me-1"></i>الحجوزات فقط
            </button>
            <button type="button" class="btn btn-sm btn-secondary filter-time-btn arabic-text" data-filter="none" id="filterNone">
                <i class="bi bi-x-lg me-1"></i>إلغاء التصفية
            </button>
        </div>
        <div class="d-md-none">
            <button type="button" class="btn btn-sm btn-outline-primary w-100 arabic-text" id="mobileFilterBtn"
                    data-bs-toggle="popover" data-bs-placement="bottom" data-bs-html="true"
                    data-bs-trigger="click" data-bs-content="">
                <i class="bi bi-funnel me-2"></i>تصفية الأوقات
            </button>
        </div>
    </div>
</div>

<!-- Calendar Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-calendar3 me-2"></i>
                    تقويم الحجوزات
                </h5>
            </div>
        </div>
    </div>
</div>

<!-- Bookings Calendar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 arabic-text" id="currentDateDisplay">
                    <?= date('l, F j, Y') ?>
                </h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2 status-indicator" id="statusIndicator">
                        <i class="bi bi-circle-fill me-1"></i>
                        مباشر
                    </span>
                    <small class="text-muted" id="lastUpdate">
                        آخر تحديث: <?= date('H:i:s') ?>
                    </small>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="bookingsCalendarContainer">

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Booking Modal -->
<div class="modal fade" id="addBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-calendar-plus me-2"></i>
                    حجز موعد جديد
                </h5>
            </div>
            <form id="addBookingForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patientSearch" class="form-label arabic-text">
                                    المريض <span class="text-danger">*</span>
                                    <span id="preselectedLabel" class="badge bg-info ms-2" style="display: none;">محدد مسبقاً</span>
                                </label>
                                <div class="input-group patient-search-group">
                                    <input type="text" class="form-control patient-search-input" id="patientSearch" 
                                           placeholder="البحث عن المريض بالاسم أو رقم الهاتف..." required>
                                    <button type="button" class="btn patient-add-btn" id="newPatientBtn"
                                            title="إضافة مريض جديد" aria-label="إضافة مريض جديد">
                                        <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                                        <span class="patient-add-btn-label arabic-text">جديد</span>
                                    </button>
                                </div>
                                <input type="hidden" id="selectedPatientId" name="patient_id">
                                <div id="patientSearchResults" class="search-results"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bookingDate" class="form-label arabic-text">التاريخ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="bookingDate" name="date" 
                                       min="<?= date('Y-m-d') ?>" required>
                                <div class="form-text text-muted arabic-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    لا يمكن اختيار تاريخ قبل اليوم (التوقيت المحلي: مصر)
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bookingTime" class="form-label arabic-text">الوقت <span class="text-danger">*</span></label>
                                <select class="form-select" id="bookingTime" name="start_time" required>
                                    <option value="">اختر الوقت...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="selectedDoctor" class="form-label arabic-text">الطبيب المعالج <span class="text-danger">*</span></label>
                                <select class="form-select" id="selectedDoctor" name="doctor_id" required>
                                    <option value="">اختر الطبيب...</option>
                                    <?php if (!empty($doctors)): ?>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= $doctor['id'] ?>" class="arabic-text">
                                                د. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                                <?php if (!empty($doctor['specialization'])): ?>
                                                    - <?= htmlspecialchars($doctor['specialization']) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="bookingClinic" class="form-label arabic-text">العيادة <span class="text-danger">*</span></label>
                                <div class="input-group clinic-input-group" style="--clinic-color: <?= $__v0['color'] ?>;">
                                    <span class="input-group-text clinic-icon-chip" id="bookingClinicIcon" aria-hidden="true">
                                        <i class="bi <?= $__v0['icon'] ?>"></i>
                                    </span>
                                    <select class="form-select arabic-text" id="bookingClinic" name="clinic_id" required
                                            <?php if (count($__calClinics) === 1): ?> aria-readonly="true" tabindex="-1" style="pointer-events:none;background-color:#f1f5f9;" <?php endif; ?>>
                                        <?php if (count($__calClinics) !== 1): ?>
                                            <option value="">اختر العيادة...</option>
                                        <?php endif; ?>
                                        <?php foreach ($__calClinics as $__c): ?>
                                            <option value="<?= (int)$__c['id'] ?>"
                                                    data-clinic-code="<?= htmlspecialchars($__c['code']) ?>"
                                                    <?= count($__calClinics) === 1 ? 'selected' : '' ?>><?= htmlspecialchars($__c['name_ar'] ?: $__c['name_en']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="visitType" class="form-label arabic-text">نوع الزيارة <span class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="visitType" name="visit_type" required>
                                            <option value="">اختر نوع الزيارة...</option>
                                            <option value="New" class="arabic-text" data-cost="<?= $settings['new_visit_cost'] ?? 150 ?>">زيارة جديدة - <?= $settings['new_visit_cost'] ?? 150 ?> جنيه</option>
                                            <option value="FollowUp" class="arabic-text" data-cost="<?= $settings['repeated_visit_cost'] ?? 100 ?>">إعادة كشف - <?= $settings['repeated_visit_cost'] ?? 100 ?> جنيه</option>
                                            <option value="Consultation" class="arabic-text" data-cost="<?= $settings['consultation_cost'] ?? 100 ?>">استشارة / إجراء طبي - <?= $settings['consultation_cost'] ?? 100 ?> جنيه</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle arabic-text" aria-expanded="false"><i class="bi bi-person-plus fs-5"></i> <h3>اختر نوع الزيارة...</h3></button>
                                        <menu>
                                            <li data-option="" tabindex="0" role="button" class="selected"><h3>اختر نوع الزيارة...</h3></li>
                                            <li data-option="New" tabindex="0" role="button"><i class="bi bi-person-plus fs-5"></i> <h3>زيارة جديدة - <?= $settings['new_visit_cost'] ?? 150 ?> جنيه</h3></li>
                                            <li data-option="FollowUp" tabindex="0" role="button"><i class="bi bi-person-check fs-5"></i> <h3>إعادة كشف - <?= $settings['repeated_visit_cost'] ?? 100 ?> جنيه</h3></li>
                                            <li data-option="Consultation" tabindex="0" role="button"><i class="bi bi-file-earmark-medical fs-5"></i> <h3>استشارة / إجراء طبي - <?= $settings['consultation_cost'] ?? 100 ?> جنيه</h3></li>
                                        </menu>
                                    </div>
                                </section>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bookingSource" class="form-label arabic-text">مصدر الحجز</label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="bookingSource" name="source">
                                            <option value="Walk-in" class="arabic-text" selected>حضوري</option>
                                            <option value="Phone" class="arabic-text">هاتف</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle arabic-text" aria-expanded="false"><i class="bi bi-people fs-5"></i> <h3>حضوري</h3></button>
                                        <menu>
                                            <li data-option="Walk-in" tabindex="0" role="button" class="selected"><i class="bi bi-people fs-5"></i> <h3>حضوري</h3></li>
                                            <li data-option="Phone" tabindex="0" role="button"><i class="bi bi-telephone fs-5"></i> <h3>هاتف</h3></li>
                                        </menu>
                                    </div>
                                </section>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bookingNotes" class="form-label arabic-text">ملاحظات</label>
                                <textarea class="form-control" id="bookingNotes" name="notes" 
                                          rows="3" placeholder="أي ملاحظات إضافية..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Section -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card payment-section">
                                <div class="card-header">
                                    <h6 class="mb-0 arabic-text">
                                        <i class="bi bi-credit-card me-2"></i>
                                        الدفع
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="visitCost" class="form-label arabic-text">تكلفة الزيارة</label>
                                                <input type="number" class="form-control" id="visitCost" 
                                                       name="visit_cost" readonly>
                                                <div class="form-text arabic-text">سيتم حسابها تلقائياً حسب نوع الزيارة</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="paymentAmount" class="form-label arabic-text">المبلغ المدفوع</label>
                                                <input type="number" class="form-control payment-amount-input" id="paymentAmount" 
                                                       name="payment_amount" min="0" step="0.01">
                                                <div class="form-text arabic-text">يمكن تركها فارغة للدفع لاحقاً</div>
                                                <div class="max-payment-info arabic-text">
                                                    الحد الأقصى المسموح: تكلفة الزيارة نفسها
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label arabic-text">طريقة الدفع</label>
                                <select class="form-select" id="paymentMethod" name="payment_method" disabled>
                                    <option value="Cash" class="arabic-text" selected>نقداً</option>
                                </select>
                                <div class="form-text arabic-text">الدفع نقداً فقط</div>
                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success" id="saveBookingBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        حفظ الحجز
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-person-plus me-2"></i>
                    إضافة مريض جديد
                </h5>
                <div class="keyboard-hint">
                    <span>اضغط</span>
                    <kbd>Esc</kbd>
                    <span>للإغلاق</span>
                </div>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="addPatientMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-person me-1"></i>
                                المعلومات الأساسية
                            </h6>
                            
                            <div class="mb-3">
                                <label for="firstName" class="form-label arabic-text">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lastName" class="form-label arabic-text">الاسم الأخير <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="age" class="form-label arabic-text">العمر (بالسنوات)</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" placeholder="أدخل العمر بالسنوات">
                                <div class="form-text arabic-text">بديل: أدخل العمر لحساب تاريخ الميلاد تلقائياً</div>
                            </div>

                            <div class="mb-3">
                                <label for="dob" class="form-label arabic-text">تاريخ الميلاد</label>
                                <input type="date" class="form-control" id="dob" name="dob">
                                <div class="form-text arabic-text">تاريخ ميلاد المريض (إذا ترك فارغاً سيتم استخدام تاريخ اليوم)</div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-telephone me-1"></i>
                                معلومات الاتصال
                            </h6>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label arabic-text">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required maxlength="20">
                                <div class="invalid-feedback"></div>
                                <div class="form-text arabic-text">رقم الاتصال الأساسي</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label arabic-text">العنوان</label>
                                <textarea class="form-control" id="address" name="address" rows="3" maxlength="500"></textarea>
                                <div class="form-text arabic-text">عنوان المنزل (اختياري)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gender" class="form-label arabic-text">الجنس <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="Male" class="arabic-text">ذكر</option>
                                    <option value="Female" class="arabic-text">أنثى</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="form-text text-danger arabic-text"><strong>مطلوب:</strong> غير الجنس إذا لزم الأمر</div>
                            </div>

                            <div class="mb-3">
                                <label for="patientClinic" class="form-label arabic-text">العيادة <span class="text-danger">*</span></label>
                                <div class="input-group clinic-input-group" style="--clinic-color: <?= $__v0['color'] ?>;">
                                    <span class="input-group-text clinic-icon-chip" id="patientClinicIcon" aria-hidden="true">
                                        <i class="bi <?= $__v0['icon'] ?>"></i>
                                    </span>
                                    <select class="form-select arabic-text" id="patientClinic" name="clinic_id" required
                                            <?php if (count($__calClinics) === 1): ?> aria-readonly="true" tabindex="-1" style="pointer-events:none;background-color:#f1f5f9;" <?php endif; ?>>
                                        <?php if (count($__calClinics) !== 1): ?>
                                            <option value="">اختر العيادة...</option>
                                        <?php endif; ?>
                                        <?php foreach ($__calClinics as $__c): ?>
                                            <option value="<?= (int)$__c['id'] ?>"
                                                    data-clinic-code="<?= htmlspecialchars($__c['code']) ?>"
                                                    <?= count($__calClinics) === 1 ? 'selected' : '' ?>><?= htmlspecialchars($__c['name_ar'] ?: $__c['name_en']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success" id="addPatientSubmit" title="حفظ المريض - اضغط 'Ctrl+S'">
                        <i class="bi bi-person-plus me-1"></i>
                        <span class="btn-text">إضافة المريض</span>
                        <small class="ms-2 text-white-50">
                            <kbd style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); font-size: 0.7rem;">Ctrl+S</kbd>
                        </small>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Attendance Modal -->
<div class="modal fade" id="confirmAttendanceModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white position-relative">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    تأكيد حضور المريض
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-success d-flex align-items-start" role="alert">
                    <i class="bi bi-shield-check fs-3 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2 arabic-text">تأكيد الحضور</h6>
                        <p class="mb-0 arabic-text">تأكيد حضور المريض وتحديث حالة الحجز.</p>
                    </div>
                </div>
                
                <div class="booking-confirm-info mb-4">
                    <h6 class="text-success mb-3 arabic-text">
                        <i class="bi bi-calendar-event me-2"></i>
                        تفاصيل الحجز:
                    </h6>
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-person-circle text-primary me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="confirmAttendancePatientName">-</h6>
                                            <small class="text-muted arabic-text">اسم المريض</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-clock text-info me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="confirmAttendanceTime">-</h6>
                                            <small class="text-muted arabic-text">وقت الموعد</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-person-badge text-success me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="confirmAttendanceDoctor">-</h6>
                                            <small class="text-muted arabic-text">الطبيب المعالج</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-calendar-check text-warning me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="confirmAttendanceVisitType">-</h6>
                                            <small class="text-muted arabic-text">نوع الزيارة</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-currency-dollar text-success me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="confirmAttendancePaid">-</h6>
                                            <small class="text-muted arabic-text">المبلغ المدفوع</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-clock text-warning me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="confirmAttendanceRemaining">-</h6>
                                            <small class="text-muted arabic-text">المبلغ المتبقي</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Section -->
                <div id="remainingPaymentSection" style="display: none;">
                    <h6 class="text-warning mb-3 arabic-text">
                        <i class="bi bi-credit-card me-2"></i>
                        استلام المبلغ المتبقي:
                    </h6>
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="remainingAmount" class="form-label arabic-text">المبلغ المتبقي</label>
                                    <input type="number" class="form-control" id="remainingAmount" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="receivedAmount" class="form-label arabic-text">المبلغ المستلم <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="receivedAmount" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="paymentMethod" class="form-label arabic-text">طريقة الدفع</label>
                                    <select class="form-select" id="paymentMethod">
                                        <option value="cash">نقداً</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="paymentNotes" class="form-label arabic-text">ملاحظات الدفع</label>
                                    <input type="text" class="form-control" id="paymentNotes" placeholder="ملاحظات اختيارية">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    إلغاء
                </button>
                <button type="button" class="btn btn-success" id="confirmAttendanceBtn">
                    <i class="bi bi-check-circle me-1"></i>
                    <span class="btn-text">تأكيد الحضور</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-primary">
            <div class="modal-header bg-primary text-white position-relative">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    تعديل الحجز
                </h5>
            </div>
            <div class="modal-body">
                <form id="editBookingForm">
                    <input type="hidden" id="editBookingId" name="booking_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editPatientSearch" class="form-label arabic-text">البحث عن المريض <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editPatientSearch" placeholder="ابحث بالاسم أو رقم الهاتف...">
                                <div id="editPatientSearchResults" class="list-group mt-2" style="display: none;"></div>
                                <input type="hidden" id="editSelectedPatientId" name="patient_id">
                                <div id="editSelectedPatientInfo" class="mt-2" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editDoctor" class="form-label arabic-text">الطبيب المعالج <span class="text-danger">*</span></label>
                                <select class="form-select" id="editDoctor" name="doctor_id" required>
                                    <option value="">اختر الطبيب...</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['id'] ?>" class="arabic-text"><?= htmlspecialchars($doctor['display_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editBookingDate" class="form-label arabic-text">تاريخ الموعد <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="editBookingDate" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editBookingTime" class="form-label arabic-text">وقت الموعد <span class="text-danger">*</span></label>
                                <select class="form-select" id="editBookingTime" name="start_time" required>
                                    <option value="">اختر الوقت...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVisitType" class="form-label arabic-text">نوع الزيارة <span class="text-danger">*</span></label>
                                <select class="form-select" id="editVisitType" name="visit_type" required onchange="updateEditVisitCost()">
                                    <option value="">اختر نوع الزيارة...</option>
                                    <option value="New" class="arabic-text" data-cost="<?= $settings['new_visit_cost'] ?? 150 ?>">زيارة جديدة - <?= $settings['new_visit_cost'] ?? 150 ?> جنيه</option>
                                    <option value="FollowUp" class="arabic-text" data-cost="<?= $settings['repeated_visit_cost'] ?? 100 ?>">إعادة كشف - <?= $settings['repeated_visit_cost'] ?? 100 ?> جنيه</option>
                                    <option value="Consultation" class="arabic-text" data-cost="<?= $settings['consultation_cost'] ?? 100 ?>">استشارة / إجراء طبي - <?= $settings['consultation_cost'] ?? 100 ?> جنيه</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editVisitCost" class="form-label arabic-text">تكلفة الزيارة</label>
                                <input type="number" class="form-control" id="editVisitCost" name="visit_cost" readonly>
                                <div class="form-text arabic-text">سيتم حسابها تلقائياً حسب نوع الزيارة</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editTotalPaid" class="form-label arabic-text">إجمالي المدفوع</label>
                                <input type="number" class="form-control" id="editTotalPaid" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editRemainingAmount" class="form-label arabic-text">المبلغ المتبقي</label>
                                <input type="number" class="form-control" id="editRemainingAmount" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editAdditionalPayment" class="form-label arabic-text">دفعة إضافية</label>
                                <input type="number" class="form-control" id="editAdditionalPayment" min="0" step="0.01" onchange="updateEditPaymentInfo()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editPaymentMethod" class="form-label arabic-text">طريقة الدفع</label>
                                <select class="form-select" id="editPaymentMethod">
                                    <option value="cash">نقداً</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editNotes" class="form-label arabic-text">ملاحظات</label>
                        <textarea class="form-control" id="editNotes" name="notes" rows="3" placeholder="ملاحظات إضافية..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    إلغاء
                </button>
                <button type="button" class="btn btn-primary" id="saveEditBookingBtn">
                    <i class="bi bi-save me-1"></i>
                    <span class="btn-text">حفظ التعديلات</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Booking Modal -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white position-relative">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    تأكيد حذف الحجز
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="bi bi-shield-exclamation fs-3 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2 arabic-text">تحذير!</h6>
                        <p class="mb-0 arabic-text">أنت على وشك حذف هذا الحجز نهائياً. هذا الإجراء <strong>لا يمكن التراجع عنه</strong>.</p>
                    </div>
                </div>
                
                <div class="booking-delete-info mb-4">
                    <h6 class="text-danger mb-3 arabic-text">
                        <i class="bi bi-calendar-event me-2"></i>
                        تفاصيل الحجز المراد حذفه:
                    </h6>
                    <div class="card border-danger">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-person-circle text-primary me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="deleteBookingPatientName">-</h6>
                                            <small class="text-muted arabic-text">اسم المريض</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-clock text-info me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="deleteBookingTime">-</h6>
                                            <small class="text-muted arabic-text">وقت الموعد</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-person-badge text-success me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="deleteBookingDoctor">-</h6>
                                            <small class="text-muted arabic-text">الطبيب المعالج</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-calendar-check text-warning me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="deleteBookingVisitType">-</h6>
                                            <small class="text-muted arabic-text">نوع الزيارة</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-currency-dollar text-success me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="deleteBookingPaid">-</h6>
                                            <small class="text-muted arabic-text">المبلغ المدفوع</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-clock text-warning me-3" style="font-size: 2rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="deleteBookingRemaining">-</h6>
                                            <small class="text-muted arabic-text">المبلغ المتبقي</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="deleteBookingNotesRow" style="display: none;">
                                <div class="col-12">
                                    <div class="d-flex align-items-start mb-3">
                                        <i class="bi bi-chat-text text-info me-3 mt-1" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <h6 class="mb-1 arabic-text" id="deleteBookingNotes">-</h6>
                                            <small class="text-muted arabic-text">ملاحظات</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    إلغاء
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBookingBtn">
                    <i class="bi bi-trash me-1"></i>
                    <span class="btn-text">حذف الحجز نهائياً</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
date_default_timezone_set('Africa/Cairo');
$serverDate = date('Y-m-d');
$serverDateTime = date('Y-m-d H:i:s');
$serverTimestamp = time();
?>
<script>
window.BOOKINGS_CONFIG = {
    serverDate: '<?= $serverDate ?>',
    serverDateTime: '<?= $serverDateTime ?>',
    serverTimestamp: <?= $serverTimestamp ?>,
    settings: <?= json_encode($settings ?? [], JSON_UNESCAPED_UNICODE) ?>,
    routes: {
        calendar: '/secretary/bookings',
        bookingDetail: '/secretary/bookings',
        patientProfile: '/secretary/patients'
    }
};
</script>
<script src="/app/Views/secretary/assets/js/bookings.js?v=<?= file_exists(__DIR__ . '/assets/js/bookings.js') ? filemtime(__DIR__ . '/assets/js/bookings.js') : time() ?>"></script>
