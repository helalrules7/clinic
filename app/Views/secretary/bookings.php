<!-- Bookings Header -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-calendar-check me-2"></i>
                إدارة الحجوزات
            </h4>
            <div class="refresh-indicator d-flex align-items-center">
                <i class="bi bi-arrow-clockwise me-2"></i>
                <small class="text-muted arabic-text">تحديث تلقائي كل 60 ثانية</small>
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
        <div class="d-flex gap-2 justify-content-end">
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
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="totalBookings">0</h3>
                        <p class="stat-label arabic-text">إجمالي الحجوزات</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="completedBookings">0</h3>
                        <p class="stat-label arabic-text">مكتملة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="pendingBookings">0</h3>
                        <p class="stat-label arabic-text">في الانتظار</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="checkedInBookings">0</h3>
                        <p class="stat-label arabic-text">تم الحضور</p>
                    </div>
                </div>
            </div>
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
                    <span class="badge bg-success me-2" id="statusIndicator">
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
            <div class="modal-header">
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-calendar-plus me-2"></i>
                    حجز موعد جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                <div class="input-group">
                                    <input type="text" class="form-control" id="patientSearch" 
                                           placeholder="البحث عن المريض بالاسم أو رقم الهاتف..." required>
                                    <button type="button" class="btn btn-outline-primary" id="newPatientBtn">
                                        <i class="bi bi-person-plus"></i>
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
                                <label for="visitType" class="form-label arabic-text">نوع الزيارة <span class="text-danger">*</span></label>
                                <select class="form-select" id="visitType" name="visit_type" required onchange="updateVisitCost()">
                                    <option value="">اختر نوع الزيارة...</option>
                                    <option value="New" class="arabic-text" data-cost="<?= $settings['new_visit_cost'] ?? 150 ?>">زيارة جديدة - <?= $settings['new_visit_cost'] ?? 150 ?> جنيه</option>
                                    <option value="FollowUp" class="arabic-text" data-cost="<?= $settings['repeated_visit_cost'] ?? 100 ?>">إعادة كشف - <?= $settings['repeated_visit_cost'] ?? 100 ?> جنيه</option>
                                    <option value="Consultation" class="arabic-text" data-cost="<?= $settings['consultation_cost'] ?? 100 ?>">استشارة / إجراء طبي - <?= $settings['consultation_cost'] ?? 100 ?> جنيه</option>

                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bookingSource" class="form-label arabic-text">مصدر الحجز</label>
                                <select class="form-select" id="bookingSource" name="source">
                                    <option value="Walk-in" class="arabic-text">حضوري</option>
                                    <option value="Phone" class="arabic-text">هاتف</option>
                                </select>
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
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-person-plus me-2"></i>
                    إضافة مريض جديد
                </h5>
                <div class="keyboard-hint">
                    <span>اضغط</span>
                    <kbd>Esc</kbd>
                    <span>للإغلاق</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
            <div class="modal-header bg-success text-white d-flex justify-content-between align-items-center">
                <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal"></button>
                <h5 class="modal-title arabic-text mb-0 ms-auto">
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
            <div class="modal-header bg-primary text-white d-flex justify-content-between align-items-center">
                <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal"></button>
                <h5 class="modal-title arabic-text mb-0 ms-auto">
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
            <div class="modal-header bg-danger text-white d-flex justify-content-between align-items-center">
                <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal"></button>
                <h5 class="modal-title arabic-text mb-0 ms-auto">
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

<style>
/* RTL specific adjustments */
.me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
.me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
.ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
.ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }

/* Modal header specific adjustments for RTL */
.modal-header .btn-close {
    margin-left: 0 !important;
    margin-right: 0 !important;
    order: 1;
}

.modal-header .modal-title {
    margin-left: auto !important;
    margin-right: 0 !important;
}

/* Fix aria-hidden focus issue */
.modal.show {
    aria-hidden: false !important;
}

.modal.show *:focus {
    outline: 2px solid #0d6efd !important;
    outline-offset: 2px !important;
}

/* Status badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

.badge i {
    font-size: 0.875rem;
}

/* Specific status badge colors */
.badge.bg-success {
    background-color: #198754 !important;
    color: white;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
    color: white;
}

.badge.bg-info {
    background-color: #0dcaf0 !important;
    color: #000;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white;
}
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

.btn-success kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

/* Arabic keyboard shortcut styling */
kbd[lang="ar"] {
    font-family: 'Cairo', 'Courier New', monospace;
    font-weight: 600;
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
}

/* Default (Light) Mode */
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
    --success-rgb: 16, 185, 129;
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
    --success-rgb: 74, 222, 128;
}

/* Calendar Grid Styles */
.calendar-grid {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: var(--card);
}

.calendar-header {
    display: grid;
    grid-template-columns: 120px 1fr;
    background: var(--bg);
    border-bottom: 2px solid var(--border);
    font-weight: 600;
}

.calendar-header > div {
    padding: 1rem;
    border-right: 1px solid var(--border);
    color: var(--text);
}

/* Dark Mode Calendar Styles */
.dark .calendar-header {
    background: var(--bg);
    color: var(--text);
}

.dark .calendar-grid {
    background: var(--card);
    border-color: var(--border);
}

.calendar-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    border-bottom: 1px solid var(--border);
    min-height: 80px;
}

.calendar-row:last-child {
    border-bottom: none;
}

.time-slot {
    padding: 1rem;
    border-right: 1px solid var(--border);
    background: var(--bg);
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text);
}

.appointment-slot {
    padding: 0.5rem;
    display: flex;
    align-items: center;
}

.appointment-card {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
}

/* Dark Mode Appointment Cards */
.dark .appointment-card {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .appointment-card:hover {
    box-shadow: 0 4px 12px var(--shadow);
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.appointment-card.booked {
    border-left: 4px solid var(--accent);
}

.appointment-card.checkedin {
    border-left: 4px solid #17a2b8;
}

.appointment-card.inprogress {
    border-left: 4px solid #ffc107;
}

.appointment-card.completed {
    border-left: 4px solid var(--success);
}

.appointment-card.cancelled {
    border-left: 4px solid var(--danger);
    opacity: 0.7;
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.appointment-header .appointment-info {
    flex: 1;
    cursor: pointer;
}

.appointment-header .appointment-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.appointment-header .appointment-info .info-line {
    font-size: 0.85em;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
}

.appointment-header .appointment-info .info-line .label {
    font-weight: 600;
    min-width: 55px;
    margin-right: 4px;
    color: var(--muted);
}

.patient-name {
    font-weight: 600;
    color: var(--text);
}

/* Dark Mode Text Colors */
.dark .patient-name {
    color: var(--text);
}

.dark .appointment-header .appointment-info .info-line .label {
    color: var(--muted);
}

.dark .appointment-notes {
    color: var(--muted);
}

.appointment-details {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.appointment-notes {
    font-size: 0.875rem;
    color: var(--muted);
    line-height: 1.4;
}

.available-slot {
    color: var(--success);
    text-align: center;
    width: 100%;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 6px;
    border: 2px dashed var(--success);
    background: rgba(var(--success-rgb), 0.05);
    transition: all 0.2s ease;
    font-weight: 500;
}

.available-slot:hover {
    background: rgba(var(--success-rgb), 0.15);
    border-color: var(--accent);
    color: var(--accent);
    transform: translateY(-1px);
}

/* Dark Mode Available Slots */
.dark .available-slot {
    color: var(--success);
    border-color: var(--success);
    background: rgba(var(--success-rgb), 0.1);
}

.dark .available-slot:hover {
    background: rgba(var(--success-rgb), 0.2);
    border-color: var(--accent);
    color: var(--accent);
}

.unavailable-slot {
    background: linear-gradient(135deg, #dc3545, #b02a37);
    color: white;
    padding: 10px 12px;
    border-radius: 6px;
    text-align: center;
    font-size: 0.85em;
    line-height: 1.4;
    width: 100%;
    font-weight: 500;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.unavailable-slot.outside-hours {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: #f8f9fa;
}

.unavailable-slot.reserved-slot {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 8px 10px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.unavailable-slot.reserved-slot .slot-details {
    flex: 1;
    text-align: left;
    line-height: 1.3;
}

.unavailable-slot.reserved-slot .info-line {
    font-size: 0.8em;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
}

.unavailable-slot.reserved-slot .info-line .label {
    font-weight: 600;
    min-width: 60px;
    margin-right: 4px;
    opacity: 0.9;
}

.unavailable-slot.debug-slot {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
    padding: 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.unavailable-slot.debug-slot .debug-info {
    flex: 1;
    text-align: left;
    font-size: 0.75em;
    line-height: 1.3;
}

.unavailable-slot.debug-slot .debug-title {
    font-weight: 600;
    margin-bottom: 3px;
    color: #212529;
}

.unavailable-slot.debug-slot .debug-details {
    font-family: 'Courier New', monospace;
    background: rgba(0, 0, 0, 0.1);
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.7em;
    word-break: break-all;
    max-height: 60px;
    overflow-y: auto;
}

.unavailable-slot.official-holiday {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.unavailable-slot.official-holiday .holiday-info {
    flex: 1;
    text-align: left;
}

.unavailable-slot.official-holiday .holiday-title {
    font-weight: 600;
    font-size: 0.9em;
    margin-bottom: 2px;
    color: white;
}

.unavailable-slot.official-holiday .holiday-subtitle {
    font-size: 0.75em;
    opacity: 0.9;
    color: rgba(255, 255, 255, 0.9);
}

.unavailable-slot.official-holiday i {
    font-size: 1.1em;
    opacity: 1;
    color: white;
}

.unavailable-slot i {
    font-size: 0.85em;
    opacity: 0.9;
    flex-shrink: 0;
}

.refresh-indicator {
    animation: pulseOnce 0.6s ease;
}

@keyframes pulseOnce {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.status-indicator {
    transition: all 0.3s ease;
}

.modal-content {
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
}

.modal-header {
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
    color: var(--text);
}

.modal-body {
    background: var(--card);
    color: var(--text);
}

.modal-footer {
    background: var(--card);
    border-top: 1px solid var(--border);
}

/* Dark Mode Modal Styles */
.dark .modal-content {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .modal-header {
    background: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

.dark .modal-body {
    background: var(--card);
    color: var(--text);
}

.dark .modal-footer {
    background: var(--card);
    border-top-color: var(--border);
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-right: 1px solid var(--border);
}

@media (max-width: 768px) {
    .calendar-header,
    .calendar-row {
        grid-template-columns: 100px 1fr;
    }
    
    .time-slot {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .appointment-slot {
        padding: 0.25rem;
    }
    
    .appointment-card {
        padding: 0.5rem;
    }
    
    .appointment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}

/* Search Results Styles */
.search-results {
    position: relative;
    z-index: 1000;
}

.search-result-item {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-top: none;
    background: var(--card);
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-result-item:first-child {
    border-top: 1px solid var(--border);
    border-radius: 8px 8px 0 0;
}

.search-result-item:last-child {
    border-radius: 0 0 8px 8px;
}

.search-result-item:only-child {
    border-radius: 8px;
}

.search-result-item:hover {
    background: var(--bg);
}

/* Dark Mode Search Results */
.dark .search-result-item {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .search-result-item:hover {
    background: var(--bg);
}

.dark .patient-details {
    color: var(--muted);
}

.patient-name {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.25rem;
}

.patient-details {
    font-size: 0.875rem;
    color: var(--muted);
}

/* Modal improvements */
.modal-content {
    border-radius: 12px;
}

.form-label {
    font-weight: 600;
    color: var(--text);
}

/* Form Controls Dark Mode */
.form-control {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
}

.form-control:focus {
    background: var(--card);
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    color: var(--text);
}

.form-select {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
}

.form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    color: var(--text);
}

.dark .form-control {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-control:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
}

.dark .form-select {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
}

.dark .form-label {
    color: var(--text);
}

.btn-group .btn {
    border-radius: 6px !important;
}

/* Notification styles */
.alert {
    box-shadow: 0 4px 12px var(--shadow);
    border-radius: 8px;
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
}

/* Dark Mode Alert Styles */
.dark .alert {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
    box-shadow: 0 4px 12px var(--shadow);
}

.dark .alert-info {
    background: rgba(56, 189, 248, 0.1);
    border-color: var(--accent);
    color: var(--text);
}

/* Selected patient info */
.selected-patient-info {
    margin-top: 0.5rem;
    border-radius: 8px;
    border: 1px solid #b3d9ff;
    background: rgba(13, 110, 253, 0.1);
}

/* Readonly field styling */
input[readonly] {
    background-color: var(--bg) !important;
    cursor: not-allowed !important;
    opacity: 0.8;
    color: var(--text) !important;
}

/* Dark Mode Readonly Fields */
.dark input[readonly] {
    background-color: var(--bg) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

/* Preselected fields styling */
.preselected-field {
    background-color: rgba(var(--success-rgb), 0.1) !important;
    border-color: var(--success) !important;
    font-weight: 600;
}

.preselected-field:focus {
    box-shadow: 0 0 0 0.2rem rgba(var(--success-rgb), 0.25) !important;
}

/* Custom Tooltip Styling */
.tooltip {
    font-size: 0.875rem;
    max-width: 350px;
}

.tooltip-inner {
    background-color: #2c3e50;
    color: #ffffff;
    border-radius: 8px;
    padding: 12px 16px;
    text-align: left;
    direction: ltr;
    box-shadow: 0 4px 12px var(--shadow);
}

/* Dark Mode Tooltips */
.dark .tooltip-inner {
    background-color: var(--card);
    color: var(--text);
    border: 1px solid var(--border);
    box-shadow: 0 4px 12px var(--shadow);
}

.appointment-tooltip {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.appointment-tooltip .tooltip-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 8px;
    margin-bottom: 10px;
    font-size: 0.95rem;
}

.appointment-tooltip .tooltip-body {
    line-height: 1.5;
}

.appointment-tooltip .tooltip-row {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 6px;
    align-items: flex-start;
}

.appointment-tooltip .tooltip-label {
    font-weight: 600;
    color: #bdc3c7;
    min-width: 80px;
    margin-right: 8px;
}

.appointment-tooltip .tooltip-value {
    color: #ffffff;
    text-align: left;
    flex: 1;
    word-break: break-word;
}

.appointment-tooltip .tooltip-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 8px;
    margin-top: 10px;
    text-align: center;
}

.appointment-tooltip .tooltip-footer small {
    color: #95a5a6;
    font-style: italic;
}

/* Dark Mode Buttons */
.dark .btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.dark .btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: #0b1220;
}

.dark .btn-success {
    background-color: var(--success);
    border-color: var(--success);
    color: #0b1220;
}

.dark .btn-success:hover {
    background-color: #059669;
    border-color: #059669;
}

.dark .btn-secondary {
    background-color: #64748b;
    border-color: #64748b;
    color: white;
}

.dark .btn-secondary:hover {
    background-color: #475569;
    border-color: #475569;
}

.dark .btn-danger {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white;
}

.dark .btn-warning {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: #0b1220;
}

/* Dark Mode Cards */
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

.dark .card-body {
    background-color: var(--card);
    color: var(--text);
}

/* Dark Mode Badge Styles */
.dark .badge {
    color: white;
}

.dark .badge.bg-success {
    background-color: var(--success) !important;
}

.dark .badge.bg-primary {
    background-color: var(--accent) !important;
}

.dark .badge.bg-info {
    background-color: #0ea5e9 !important;
}

.dark .badge.bg-warning {
    background-color: #f59e0b !important;
    color: #0b1220 !important;
}

.dark .badge.bg-danger {
    background-color: var(--danger) !important;
}

/* Dark Mode Text Colors */
.dark h4, .dark h5, .dark h6 {
    color: var(--text);
}

.dark .text-muted {
    color: var(--muted) !important;
}

.dark small {
    color: var(--muted);
}

/* Add Patient Modal Styling */
#addPatientModal .modal-content {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal .modal-header {
    background-color: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

#addPatientModal .modal-footer {
    background-color: var(--card);
    border-top-color: var(--border);
}

#addPatientModal .form-label {
    color: var(--text);
    font-weight: 500;
}

#addPatientModal .form-control,
#addPatientModal .form-select {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal .form-control:focus,
#addPatientModal .form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

#addPatientModal .form-text {
    color: var(--muted);
    font-size: 0.875rem;
}

#addPatientModal .text-primary {
    color: var(--accent) !important;
}

#addPatientModal .text-danger {
    color: #dc3545 !important;
}

#addPatientModal .invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
}

#addPatientModal .form-control.is-invalid,
#addPatientModal .form-select.is-invalid {
    border-color: #dc3545;
}

#addPatientModal .alert {
    border-radius: 8px;
    margin-bottom: 1rem;
}

#addPatientModal .alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #155724;
}

#addPatientModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

/* Keyboard shortcut hint styling */
.keyboard-hint {
    position: absolute;
    top: 10px;
    right: 15px;
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

/* Delete appointment button styling */
.delete-appointment-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 4px;
    transition: all 0.2s ease;
    opacity: 0.7;
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.delete-appointment-btn:hover {
    opacity: 1;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    background-color: #c82333;
    border-color: #bd2130;
    color: white;
}

.appointment-card:hover .delete-appointment-btn {
    opacity: 1;
}

/* Dark mode delete button */
.dark .delete-appointment-btn {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.dark .delete-appointment-btn:hover {
    background-color: #c82333;
    border-color: #bd2130;
    color: white;
}

/* Delete Appointment Modal Styling */
#deleteAppointmentModal .modal-content {
    background-color: var(--card);
    color: var(--text);
}

#deleteAppointmentModal .modal-header {
    background-color: #dc3545 !important;
    border-bottom-color: #dc3545;
}

#deleteAppointmentModal .modal-footer {
    background-color: var(--card);
    border-top-color: var(--border);
}

#deleteAppointmentModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

[data-bs-theme="dark"] #deleteAppointmentModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #f5c6cb;
}

#deleteAppointmentModal .list-group-item {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#deleteAppointmentModal .card {
    background-color: var(--card);
    border-color: #ffc107;
}

#deleteAppointmentModal .card-body {
    background-color: var(--bg);
}

/* Secretary Calendar Styles */
.bookings-calendar-grid {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: var(--card);
}

.bookings-calendar-header {
    display: grid;
    grid-template-columns: 120px 1fr;
    background: var(--bg);
    border-bottom: 2px solid var(--border);
    font-weight: 600;
}

.bookings-calendar-header > div {
    padding: 1rem;
    border-right: 1px solid var(--border);
    color: var(--text);
}

.bookings-calendar-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    border-bottom: 1px solid var(--border);
    min-height: 80px;
}

.bookings-calendar-row:last-child {
    border-bottom: none;
}

.bookings-time-slot {
    padding: 1rem;
    border-right: 1px solid var(--border);
    background: var(--bg);
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text);
}

.bookings-appointment-slot {
    padding: 0.5rem;
    display: flex;
    align-items: center;
}

.bookings-appointment-card {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
}

.bookings-appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.bookings-available-slot {
    color: var(--success);
    text-align: center;
    width: 100%;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 6px;
    border: 2px dashed var(--success);
    background: rgba(var(--success-rgb), 0.05);
    transition: all 0.2s ease;
    font-weight: 500;
}

.bookings-available-slot:hover {
    background: rgba(var(--success-rgb), 0.15);
    border-color: var(--accent);
    color: var(--accent);
    transform: translateY(-1px);
}

.bookings-unavailable-slot {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: #f8f9fa;
    padding: 10px 12px;
    border-radius: 6px;
    text-align: center;
    font-size: 0.85em;
    line-height: 1.4;
    width: 100%;
    font-weight: 500;
}

.bookings-unavailable-slot.official-holiday {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.bookings-unavailable-slot.official-holiday .holiday-info {
    flex: 1;
    text-align: left;
}

.bookings-unavailable-slot.official-holiday .holiday-title {
    font-weight: 600;
    font-size: 0.9em;
    margin-bottom: 2px;
    color: white;
}

.bookings-unavailable-slot.official-holiday .holiday-subtitle {
    font-size: 0.75em;
    opacity: 0.9;
    color: rgba(255, 255, 255, 0.9);
}

/* Payment Section Styling */
.payment-section {
    border: 2px solid var(--accent);
    border-radius: 8px;
    background: rgba(var(--accent-rgb), 0.05);
}

.payment-section .card-header {
    background: var(--accent);
    color: white;
    border-radius: 6px 6px 0 0;
}

.payment-amount-input {
    border: 2px solid var(--success);
    background: rgba(var(--success-rgb), 0.1);
}

.payment-amount-input:focus {
    border-color: var(--success);
    box-shadow: 0 0 0 0.2rem rgba(var(--success-rgb), 0.25);
}

.max-payment-info {
    font-size: 0.875rem;
    color: var(--muted);
    font-style: italic;
}

.payment-validation-error {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Dark Button Styles */
.btn-success-dark {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

.btn-success-dark:hover {
    background-color: #218838 !important;
    border-color: #1e7e34 !important;
    color: white !important;
}

.btn-primary-dark {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
}

.btn-primary-dark:hover {
    background-color: #0056b3 !important;
    border-color: #004085 !important;
    color: white !important;
}

.btn-danger-dark {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: white !important;
}

.btn-danger-dark:hover {
    background-color: #c82333 !important;
    border-color: #bd2130 !important;
    color: white !important;
}

/* Payment Info Styling */
.payment-info {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    padding: 0.5rem;
    margin-top: 0.5rem;
}

.payment-info .text-success {
    color: #28a745 !important;
    font-weight: 600;
}

.payment-info .text-warning {
    color: #ffc107 !important;
    font-weight: 600;
}
</style>

<script>
// Get server time for Egypt timezone
<?php
date_default_timezone_set('Africa/Cairo');
$serverDate = date('Y-m-d');
$serverDateTime = date('Y-m-d H:i:s');
$serverTimestamp = time();
?>

const SERVER_DATE = '<?= $serverDate ?>';
const SERVER_DATETIME = '<?= $serverDateTime ?>';
const SERVER_TIMESTAMP = <?= $serverTimestamp ?>;

// System settings
const SYSTEM_SETTINGS = <?= json_encode($settings) ?>;

let currentDate = new Date();
let selectedDoctorId = null;
let refreshInterval;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    updateStatistics([]);
    
    // Set current date to today
    const today = new Date();
    currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
    
    loadCalendar(); // Load calendar automatically
});

function setupEventListeners() {
    // Navigation buttons
    document.getElementById('todayBtn').addEventListener('click', () => {
        const today = new Date();
        currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
        updateDateDisplay();
        loadCalendar();
    });
    
    document.getElementById('prevDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() - 24 * 60 * 60 * 1000);
        updateDateDisplay();
        loadCalendar();
    });
    
    document.getElementById('nextDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() + 24 * 60 * 60 * 1000);
        updateDateDisplay();
        loadCalendar();
    });
    
    // Add booking button
    document.getElementById('addBookingBtn').addEventListener('click', () => {
        openAddBookingModal();
    });
    
    // Patient search
    document.getElementById('patientSearch').addEventListener('input', debounce(searchPatients, 300));
    
    // Visit type change - update cost
    document.getElementById('visitType').addEventListener('change', updateVisitCost);
    
    // Payment amount validation
    document.getElementById('paymentAmount').addEventListener('input', validatePaymentAmount);
    document.getElementById('paymentAmount').addEventListener('change', validatePaymentAmount);
    
    // Add booking form submission
    document.getElementById('addBookingForm').addEventListener('submit', handleAddBooking);
    
    // New patient button
    document.getElementById('newPatientBtn').addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('addBookingModal')).hide();
        setTimeout(() => {
            const addPatientModal = new bootstrap.Modal(document.getElementById('addPatientModal'));
            addPatientModal.show();
        }, 300);
    });
    
    // Delete booking confirmation
    document.getElementById('confirmDeleteBookingBtn').addEventListener('click', confirmDeleteBooking);
    
    // Confirm attendance
    document.getElementById('confirmAttendanceBtn').addEventListener('click', confirmAttendanceAction);
    
    // Edit booking
    document.getElementById('saveEditBookingBtn').addEventListener('click', saveEditBooking);
    
    // Edit patient search
    document.getElementById('editPatientSearch').addEventListener('input', debounce(editSearchPatients, 300));
    
    // Edit form change events
    document.getElementById('editVisitType').addEventListener('change', updateEditVisitCost);
    document.getElementById('editAdditionalPayment').addEventListener('input', updateEditPaymentInfo);
    document.getElementById('editBookingDate').addEventListener('change', function() {
        const doctorId = document.getElementById('editDoctor').value;
        const currentBookingId = document.getElementById('editBookingId').value;
        const currentTime = document.getElementById('editBookingTime').value;
        if (doctorId && this.value) {
            loadEditAvailableTimeSlots(this.value, doctorId, currentBookingId, currentTime);
        }
    });
    document.getElementById('editDoctor').addEventListener('change', function() {
        const date = document.getElementById('editBookingDate').value;
        const currentBookingId = document.getElementById('editBookingId').value;
        const currentTime = document.getElementById('editBookingTime').value;
        if (date && this.value) {
            loadEditAvailableTimeSlots(date, this.value, currentBookingId, currentTime);
        }
    });
}

function loadCalendar() {
    const dateStr = currentDate.toISOString().split('T')[0];
    
    fetch(`/secretary/bookings/calendar?date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderCalendar(data.data);
                updateDateDisplay();
                updateLastUpdate();
                updateStatistics(data.data.appointments || []);
                
                // Only start auto refresh if not already started
                if (!refreshInterval) {
                    startAutoRefresh();
                }
            } else {
                showNotification('خطأ في تحميل التقويم: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            showNotification('خطأ في تحميل التقويم', 'danger');
        });
}

function renderCalendar(data) {
    const container = document.getElementById('bookingsCalendarContainer');
    const timeSlots = generateTimeSlots();
    
    // Check if it's Friday (official holiday)
    const dateStr = data.date || currentDate.toISOString().split('T')[0];
    const currentDateObj = new Date(dateStr + 'T12:00:00');
    const isFriday = currentDateObj.getDay() === 5;
    
    let html = '<div class="bookings-calendar-grid">';
    
    // Header row
    html += '<div class="bookings-calendar-header">';
    html += '<div class="arabic-text">الوقت</div>';
    html += '<div class="arabic-text">الحجوزات</div>';
    html += '</div>';
    
    // If it's Friday, show official holiday for all slots
    if (isFriday || data.is_friday) {
        const dayName = currentDateObj.toLocaleDateString('ar-EG', {weekday: 'long'});
        timeSlots.forEach(time => {
            html += '<div class="bookings-calendar-row">';
            html += `<div class="bookings-time-slot">${formatTime(time)}</div>`;
            html += '<div class="bookings-appointment-slot">';
            html += `<div class="bookings-unavailable-slot official-holiday">
                       <i class="bi bi-calendar-x me-2"></i>
                       <div class="holiday-info">
                           <div class="holiday-title">عطلة رسمية</div>
                           <div class="holiday-subtitle">${dayName}</div>
                       </div>
                     </div>`;
            html += '</div>';
            html += '</div>';
        });
    } else {
        // Normal day processing
        timeSlots.forEach(time => {
            // Convert time to match database format (HH:MM:SS)
            const timeWithSeconds = time + ':00';
            const appointments = (data.appointments || []).filter(apt => apt.start_time === timeWithSeconds);
            const isAvailable = (data.available_slots || []).includes(time);
            const unavailableSlot = data.unavailable_slots ? data.unavailable_slots.find(slot => slot.time === time) : null;
            
            html += '<div class="bookings-calendar-row">';
            html += `<div class="bookings-time-slot">${formatTime(time)}</div>`;
            html += '<div class="bookings-appointment-slot">';
            
            if (appointments.length > 0) {
                // Show all appointments for this time slot
                appointments.forEach(appointment => {
                    html += renderAppointmentSlot(appointment);
                });
            } else if (isAvailable) {
                html += `<div class="bookings-available-slot" onclick="quickAddBooking('${time}')" 
                              title="اضغط لحجز موعد في ${formatTime(time)}">
                            <i class="bi bi-plus-circle me-2"></i>متاح - ${formatTime(time)}
                         </div>`;
            } else {
                if (unavailableSlot && unavailableSlot.reason === 'Outside working hours') {
                    html += `<div class="bookings-unavailable-slot">
                               <i class="bi bi-clock me-2"></i>خارج ساعات العمل
                             </div>`;
                } else {
                    html += `<div class="bookings-unavailable-slot">
                               <i class="bi bi-x-circle me-2"></i>غير متاح
                             </div>`;
                }
            }
            
            html += '</div>';
            html += '</div>';
        });
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function renderAppointmentSlot(appointment) {
    const statusClass = getStatusBadgeClass(appointment.status);
    
    // Calculate payment info
    const totalPaid = appointment.total_paid || 0;
    const visitCost = appointment.visit_cost || 0;
    const remainingAmount = visitCost - totalPaid;
    
    return `
        <div class="bookings-appointment-card ${appointment.status.toLowerCase()}" 
             onclick="viewAppointmentDetails(${appointment.id})">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="arabic-text">
                       <strong>المريض: </strong> <strong>${appointment.patient_name}</strong>
                    </div>
                    <div class="text-muted small arabic-text mb-2">
                      <strong>نوع الحجز: </strong> ${getVisitTypeInArabic(appointment.visit_type)}
                    </div>
                    <div class="text-muted small arabic-text mb-2">
                       <strong>الوقت: </strong> ${formatTime(appointment.start_time.substring(0, 5))}
                    </div>
                       <div class="text-muted small arabic-text mb-2">
                      <strong>الطبيب: </strong> ${appointment.doctor_display_name}
                    </div>
                    <div class="payment-info arabic-text">
                        <div class="small">
                            <div class="text-success mb-1">
                                <i class="bi bi-currency-dollar me-1"></i>
                                المدفوع: ${totalPaid} جنيه
                            </div>
                            <div class="text-warning">
                                <i class="bi bi-clock me-1"></i>
                                المتبقي: ${remainingAmount} جنيه
                            </div>
                        </div>
                    </div>
                    ${appointment.notes ? `<div class="text-muted small mt-1">${appointment.notes.substring(0, 30)}...</div>` : ''}
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge ${statusClass} d-flex align-items-center gap-1">
                        <i class="bi ${getStatusIcon(appointment.status)}"></i>
                        ${getStatusDisplayText(appointment.status)}
                    </span>
                    <div class="btn-group" role="group">
                        ${appointment.status === 'Booked' ? `
                            <button class="btn btn-sm btn-success-dark" 
                                    onclick="event.stopPropagation(); confirmAttendance(${appointment.id}, '${appointment.patient_name}', '${formatTime(appointment.start_time.substring(0, 5))}', '${appointment.doctor_display_name}', '${appointment.visit_type}', ${totalPaid}, ${remainingAmount})"
                                    title="تأكيد الحضور">&nbsp;
                                <i class="bi bi-check-circle text-white"> تأكيد الحضور</i>
                            </button>
                        ` : ''}
                        ${appointment.status !== 'CheckedIn' && appointment.status !== 'Completed' ? `
                            <button class="btn btn-sm btn-primary-dark" 
                                    onclick="event.stopPropagation(); editBooking(${appointment.id})"
                                    title="تعديل الحجز">&nbsp;
                                <i class="bi bi-pencil text-white"> تعديل الحجز</i>
                            </button>
                            <button class="btn btn-sm btn-danger-dark" 
                                    onclick="event.stopPropagation(); deleteBooking(${appointment.id}, '${appointment.patient_name}', '${formatTime(appointment.start_time.substring(0, 5))}', '${appointment.doctor_display_name}', '${getVisitTypeInArabic(appointment.visit_type)}', ${totalPaid}, ${remainingAmount}, '${appointment.notes || ''}')"
                                    title="حذف الحجز">&nbsp;
                                <i class="bi bi-trash text-white"> حذف الحجز</i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generateTimeSlots() {
    const slots = [];
    const start = new Date();
    start.setHours(14, 0, 0, 0); // 2:00 PM
    
    const end = new Date();
    end.setHours(23, 0, 0, 0); // 11:00 PM
    
    const current = new Date(start);
    
    while (current < end) {
        slots.push(current.toTimeString().substring(0, 5));
        current.setMinutes(current.getMinutes() + 15);
    }
    
    return slots;
}

function clearCalendar() {
    const container = document.getElementById('bookingsCalendarContainer');
    container.innerHTML = `
        <div class="text-center p-5">
            <i class="bi bi-calendar3 text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3 arabic-text">اضغط على "حجز جديد" لبدء إنشاء موعد</p>
        </div>
    `;
    
    // Reset statistics
    updateStatistics([]);
}

function openAddBookingModal(preselectedTime = null) {
    // Set preselected time
    if (preselectedTime) {
        document.getElementById('bookingTime').value = preselectedTime;
    }
    
    // Set date
    const dateToUse = currentDate.toISOString().split('T')[0];
    document.getElementById('bookingDate').value = dateToUse;
    
    // Check if patient is preselected from URL
    const urlParams = new URLSearchParams(window.location.search);
    const patientId = urlParams.get('patient_id');
    
    // Clear form
    document.getElementById('addBookingForm').reset();
    
    // Re-set values after reset
    document.getElementById('bookingDate').value = dateToUse;
    if (preselectedTime) {
        document.getElementById('bookingTime').value = preselectedTime;
    }
    
    // If patient is preselected, restore the patient selection
    if (patientId) {
        document.getElementById('selectedPatientId').value = patientId;
        document.getElementById('patientSearch').value = `مريض رقم ${patientId}`;
        document.getElementById('patientSearchResults').innerHTML = `
            <div class="selected-patient-info alert alert-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>المريض المحدد:</strong> مريض رقم ${patientId}<br>
                        <small>تم تحديد المريض مسبقاً</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                        تغيير المريض
                    </button>
                </div>
            </div>
        `;
        document.getElementById('preselectedLabel').style.display = 'inline';
        
        // Make patient search field readonly
        const patientSearchField = document.getElementById('patientSearch');
        patientSearchField.readOnly = true;
        patientSearchField.style.backgroundColor = 'var(--bg)';
        patientSearchField.style.cursor = 'not-allowed';
        
        // Hide new patient button
        document.getElementById('newPatientBtn').style.display = 'none';
    } else {
        document.getElementById('selectedPatientId').value = '';
        document.getElementById('patientSearchResults').innerHTML = '';
        document.getElementById('preselectedLabel').style.display = 'none';
        
        // Enable patient search
        const patientSearchField = document.getElementById('patientSearch');
        patientSearchField.readOnly = false;
        patientSearchField.style.backgroundColor = '';
        patientSearchField.style.cursor = '';
        document.getElementById('newPatientBtn').style.display = 'block';
    }
    
    // Load available time slots
    loadAvailableTimeSlots(preselectedTime);
    
    // Update visit cost and payment limits
    updateVisitCost();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addBookingModal'));
    modal.show();
}

function quickAddBooking(time) {
    const selectedDate = currentDate.toISOString().split('T')[0];
    
    if (isDateInPast(selectedDate)) {
        showNotification('لا يمكن حجز موعد في تاريخ سابق', 'warning');
        return;
    }
    
    openAddBookingModal(time);
}

function loadAvailableTimeSlots(preselectedTime = null) {
    const date = document.getElementById('bookingDate').value;
    if (!date) return;
    
    fetch(`/secretary/bookings/calendar?date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateTimeSlots(data.data.available_slots, preselectedTime);
            }
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
        });
}

function populateTimeSlots(availableSlots, preselectedTime = null) {
    const timeSelect = document.getElementById('bookingTime');
    timeSelect.innerHTML = '<option value="">اختر الوقت...</option>';
    
    availableSlots.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = formatTime(time);
        timeSelect.appendChild(option);
    });
    
    if (preselectedTime) {
        timeSelect.value = preselectedTime;
    }
}

function updateVisitCost() {
    const visitTypeSelect = document.getElementById('visitType');
    const visitType = visitTypeSelect.value;
    const costField = document.getElementById('visitCost');
    const paymentAmountField = document.getElementById('paymentAmount');
    const maxPaymentInfo = document.querySelector('.max-payment-info');
    
    // Get cost from selected option's data-cost attribute
    const selectedOption = visitTypeSelect.querySelector(`option[value="${visitType}"]`);
    const cost = selectedOption ? parseFloat(selectedOption.getAttribute('data-cost')) : 0;
    
    if (visitType && cost > 0) {
        costField.value = cost;
        
        // Update payment amount max attribute
        paymentAmountField.setAttribute('max', cost);
        
        // Update max payment info text
        maxPaymentInfo.textContent = `الحد الأقصى المسموح: ${cost} جنيه (تكلفة الزيارة)`;
    } else {
        costField.value = '';
        paymentAmountField.removeAttribute('max');
        maxPaymentInfo.textContent = 'الحد الأقصى المسموح: تكلفة الزيارة نفسها';
    }
    
    // Update payment amount validation
    validatePaymentAmount();
}

function validatePaymentAmount() {
    const paymentAmount = document.getElementById('paymentAmount');
    const visitTypeSelect = document.getElementById('visitType');
    const visitType = visitTypeSelect.value;
    
    const amount = parseFloat(paymentAmount.value) || 0;
    
    // Get cost from selected option's data-cost attribute
    const selectedOption = visitTypeSelect.querySelector(`option[value="${visitType}"]`);
    const cost = selectedOption ? parseFloat(selectedOption.getAttribute('data-cost')) : 0;
    
    // Clear previous validation
    paymentAmount.classList.remove('is-invalid');
    const existingError = document.querySelector('.payment-validation-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Check for negative amounts
    if (amount < 0) {
        paymentAmount.classList.add('is-invalid');
        showPaymentError('المبلغ لا يمكن أن يكون سالباً');
        return;
    }
    
    if (amount > 0) {
        if (amount > cost) {
            paymentAmount.classList.add('is-invalid');
            showPaymentError(`المبلغ لا يمكن أن يتجاوز تكلفة الزيارة (${cost} جنيه)`);
        }
    }
    
    // Update max payment info if cost is available
    if (cost > 0) {
        const maxPaymentInfo = document.querySelector('.max-payment-info');
        if (maxPaymentInfo) {
            maxPaymentInfo.textContent = `الحد الأقصى المسموح: ${cost} جنيه (تكلفة الزيارة)`;
        }
    }
}

function showPaymentError(message) {
    const paymentAmount = document.getElementById('paymentAmount');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'payment-validation-error';
    errorDiv.textContent = message;
    paymentAmount.parentNode.appendChild(errorDiv);
}

function handleAddBooking(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const bookingData = Object.fromEntries(formData);
    
    // Debug logging
    console.log('Form data:', bookingData);
    console.log('Selected patient ID:', document.getElementById('selectedPatientId').value);
    
    // Validation
    if (!bookingData.patient_id) {
        showNotification('يرجى اختيار المريض', 'warning');
        return;
    }
    
    if (!bookingData.doctor_id) {
        showNotification('يرجى اختيار الطبيب', 'warning');
        return;
    }
    
    if (!bookingData.date) {
        showNotification('يرجى اختيار التاريخ', 'warning');
        return;
    }
    
    if (!bookingData.start_time) {
        showNotification('يرجى اختيار الوقت', 'warning');
        return;
    }
    
    if (!bookingData.visit_type) {
        showNotification('يرجى اختيار نوع الزيارة', 'warning');
        return;
    }
    
    // Get visit cost from selected option's data-cost attribute
    const visitTypeSelect = document.getElementById('visitType');
    const selectedOption = visitTypeSelect.querySelector(`option[value="${bookingData.visit_type}"]`);
    const visitCost = selectedOption ? parseFloat(selectedOption.getAttribute('data-cost')) : 0;
    
    // Validate payment amount
    const paymentAmount = parseFloat(bookingData.payment_amount) || 0;
    
    if (paymentAmount < 0) {
        showNotification('المبلغ لا يمكن أن يكون سالباً', 'warning');
        return;
    }
    
    if (paymentAmount > visitCost) {
        showNotification(`المبلغ لا يمكن أن يتجاوز تكلفة الزيارة (${visitCost} جنيه)`, 'warning');
        return;
    }
    
    // Add visit cost to booking data
    bookingData.visit_cost = visitCost;
    
    // Save booking
    fetch('/secretary/bookings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addBookingModal')).hide();
            
            // Show success message
            showNotification('تم إنشاء الحجز بنجاح!', 'success');
            
            // Refresh calendar immediately and then again after a short delay
            loadCalendar();
            setTimeout(() => {
                loadCalendar();
            }, 1000);
        } else {
            const errorMessage = data.error || 'حدث خطأ غير معروف';
            showNotification('خطأ: ' + errorMessage, 'danger');
        }
    })
    .catch(error => {
        console.error('Error saving booking:', error);
        showNotification('خطأ في حفظ الحجز: ' + error.message, 'danger');
    });
}

let currentDeleteBookingId = null;

function deleteBooking(bookingId, patientName, appointmentTime, doctorName, visitType, totalPaid, remainingAmount, notes) {
    // Store booking ID for later use
    currentDeleteBookingId = bookingId;
    
    // Populate modal with booking details
    document.getElementById('deleteBookingPatientName').textContent = patientName;
    document.getElementById('deleteBookingTime').textContent = appointmentTime;
    document.getElementById('deleteBookingDoctor').textContent = doctorName;
    document.getElementById('deleteBookingVisitType').textContent = getVisitTypeInArabic(visitType);
    document.getElementById('deleteBookingPaid').textContent = totalPaid + ' جنيه';
    document.getElementById('deleteBookingRemaining').textContent = remainingAmount + ' جنيه';
    
    // Show notes if available
    if (notes && notes.trim() !== '') {
        document.getElementById('deleteBookingNotes').textContent = notes;
        document.getElementById('deleteBookingNotesRow').style.display = 'block';
    } else {
        document.getElementById('deleteBookingNotesRow').style.display = 'none';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteBookingModal'));
    modal.show();
}

function confirmDeleteBooking() {
    if (!currentDeleteBookingId) return;
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmDeleteBookingBtn');
    const btnText = confirmBtn.querySelector('.btn-text');
    const spinner = confirmBtn.querySelector('.spinner-border');
    
    btnText.classList.add('d-none');
    spinner.classList.remove('d-none');
    confirmBtn.disabled = true;
    
    fetch(`/secretary/bookings/${currentDeleteBookingId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification('تم حذف الحجز بنجاح!', 'success');
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('deleteBookingModal')).hide();
            // Refresh calendar immediately
            loadCalendar();
        } else {
            showNotification('خطأ في حذف الحجز: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        showNotification('خطأ في حذف الحجز', 'danger');
    })
    .finally(() => {
        // Reset button state
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        currentDeleteBookingId = null;
    });
}

let currentConfirmAttendanceId = null;

function confirmAttendance(bookingId, patientName, appointmentTime, doctorName, visitType, totalPaid, remainingAmount) {
    // Store booking ID for later use
    currentConfirmAttendanceId = bookingId;
    
    // Populate modal with booking details
    document.getElementById('confirmAttendancePatientName').textContent = patientName;
    document.getElementById('confirmAttendanceTime').textContent = appointmentTime;
    document.getElementById('confirmAttendanceDoctor').textContent = doctorName;
    document.getElementById('confirmAttendanceVisitType').textContent = getVisitTypeInArabic(visitType);
    document.getElementById('confirmAttendancePaid').textContent = totalPaid + ' جنيه';
    document.getElementById('confirmAttendanceRemaining').textContent = remainingAmount + ' جنيه';
    
    // Show/hide payment section based on remaining amount
    const remainingPaymentSection = document.getElementById('remainingPaymentSection');
    const remainingAmountInput = document.getElementById('remainingAmount');
    const receivedAmountInput = document.getElementById('receivedAmount');
    
    if (remainingAmount > 0) {
        remainingPaymentSection.style.display = 'block';
        remainingAmountInput.value = remainingAmount;
        receivedAmountInput.value = remainingAmount;
        receivedAmountInput.max = remainingAmount;
    } else {
        remainingPaymentSection.style.display = 'none';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('confirmAttendanceModal'));
    modal.show();
}

function confirmAttendanceAction() {
    if (!currentConfirmAttendanceId) return;
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmAttendanceBtn');
    const btnText = confirmBtn.querySelector('.btn-text');
    const spinner = confirmBtn.querySelector('.spinner-border');
    
    btnText.classList.add('d-none');
    spinner.classList.remove('d-none');
    confirmBtn.disabled = true;
    
    // Check if there's remaining payment
    const remainingAmount = parseFloat(document.getElementById('remainingAmount').value) || 0;
    const receivedAmount = parseFloat(document.getElementById('receivedAmount').value) || 0;
    const paymentMethod = document.getElementById('paymentMethod').value;
    const paymentNotes = document.getElementById('paymentNotes').value;
    
    // Validate payment data
    if (receivedAmount < 0) {
        showNotification('المبلغ المستلم لا يمكن أن يكون سالباً', 'warning');
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        return;
    }
    
    if (remainingAmount > 0) {
        if (!receivedAmount || receivedAmount <= 0) {
            showNotification('يرجى إدخال المبلغ المستلم', 'warning');
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            confirmBtn.disabled = false;
            return;
        }
        
        if (receivedAmount < remainingAmount) {
            showNotification(`المبلغ المستلم (${receivedAmount} جنيه) يجب أن يكون مساوياً للمبلغ المتبقي (${remainingAmount} جنيه)`, 'warning');
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            confirmBtn.disabled = false;
            return;
        }
        
        if (receivedAmount > remainingAmount) {
            showNotification(`المبلغ المستلم (${receivedAmount} جنيه) أكبر من المبلغ المتبقي (${remainingAmount} جنيه)`, 'warning');
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            confirmBtn.disabled = false;
            return;
        }
    }
    
    const data = {
        booking_id: currentConfirmAttendanceId,
        remaining_amount: remainingAmount,
        received_amount: receivedAmount,
        payment_method: paymentMethod,
        payment_notes: paymentNotes
    };
    
    fetch(`/secretary/bookings/${currentConfirmAttendanceId}/confirm`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.ok) {
            showNotification('تم تأكيد الحضور بنجاح!', 'success');
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('confirmAttendanceModal')).hide();
            // Refresh calendar immediately
            loadCalendar();
            // Update financial dashboard cards if on payments page
            if (typeof updateDashboardCards === 'function') {
                updateDashboardCards();
            } else {
                // If updateDashboardCards is not available, try to update via API
                updateFinancialCards();
            }
        } else {
            showNotification('خطأ في تأكيد الحضور: ' + (data.error || 'خطأ غير معروف'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error confirming attendance:', error);
        
        // Check if response is HTML (error page)
        if (error.message.includes('Unexpected token')) {
            showNotification('خطأ في الخادم: يرجى المحاولة مرة أخرى', 'danger');
        } else {
            showNotification('خطأ في تأكيد الحضور: ' + error.message, 'danger');
        }
    })
    .finally(() => {
        // Reset button state
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        currentConfirmAttendanceId = null;
    });
}

let currentEditBookingId = null;

function editBooking(bookingId) {
    currentEditBookingId = bookingId;
    
    // Fetch booking details
    fetch(`/secretary/bookings/${bookingId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateEditForm(data.booking);
                const modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
                modal.show();
            } else {
                showNotification('خطأ في تحميل تفاصيل الحجز: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            showNotification('خطأ في تحميل تفاصيل الحجز', 'danger');
        });
}

function populateEditForm(booking) {
    // Set booking ID
    document.getElementById('editBookingId').value = booking.id;
    
    // Set patient info
    document.getElementById('editSelectedPatientId').value = booking.patient_id;
    document.getElementById('editSelectedPatientInfo').innerHTML = `
        <div class="alert alert-info">
            <strong>المريض المحدد:</strong> ${booking.patient_name} - ${booking.patient_phone}
        </div>
    `;
    document.getElementById('editSelectedPatientInfo').style.display = 'block';
    
    // Set doctor
    document.getElementById('editDoctor').value = booking.doctor_id;
    
    // Set date and time
    document.getElementById('editBookingDate').value = booking.date;
    
    // Set visit type
    document.getElementById('editVisitType').value = booking.visit_type;
    updateEditVisitCost();
    
    // Set payment info
    document.getElementById('editTotalPaid').value = booking.total_paid || 0;
    updateEditPaymentInfo();
    
    // Set notes
    document.getElementById('editNotes').value = booking.notes || '';
    
    // Load available time slots for the selected date and select current time
    loadEditAvailableTimeSlots(booking.date, booking.doctor_id, booking.id, booking.start_time);
}

function updateEditVisitCost() {
    const visitTypeSelect = document.getElementById('editVisitType');
    const visitCostInput = document.getElementById('editVisitCost');
    const selectedOption = visitTypeSelect.options[visitTypeSelect.selectedIndex];
    
    if (selectedOption && selectedOption.dataset.cost) {
        const cost = parseFloat(selectedOption.dataset.cost);
        visitCostInput.value = cost;
        updateEditPaymentInfo();
    } else {
        visitCostInput.value = '';
    }
}

function updateEditPaymentInfo() {
    const visitCost = parseFloat(document.getElementById('editVisitCost').value) || 0;
    const totalPaid = parseFloat(document.getElementById('editTotalPaid').value) || 0;
    const additionalPayment = parseFloat(document.getElementById('editAdditionalPayment').value) || 0;
    
    const newTotalPaid = totalPaid + additionalPayment;
    const remainingAmount = Math.max(0, visitCost - newTotalPaid);
    
    document.getElementById('editRemainingAmount').value = remainingAmount;
}

function loadEditAvailableTimeSlots(date, doctorId, currentBookingId = null, currentTime = null) {
    // Get all time slots first
    const allSlots = getAllTimeSlots();
    
    // Get unavailable slots for the specific doctor and date
    fetch(`/secretary/bookings/calendar?date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Get all appointments for the date to find booked slots
                const appointments = data.data.appointments || [];
                
                // Get unavailable slots for the specific doctor
                const unavailableSlots = [];
                appointments.forEach(appointment => {
                    if (appointment.doctor_id == doctorId && 
                        appointment.status !== 'Cancelled' && 
                        appointment.id != currentBookingId) { // Exclude current booking
                        unavailableSlots.push(appointment.start_time.substring(0, 5)); // Remove seconds
                    }
                });
                
                // Filter out unavailable slots
                const availableSlots = allSlots.filter(slot => !unavailableSlots.includes(slot));
                
                const timeSelect = document.getElementById('editBookingTime');
                timeSelect.innerHTML = '<option value="">اختر الوقت...</option>';
                
                availableSlots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = formatTime(slot);
                    timeSelect.appendChild(option);
                });
                
                // Select current time if provided and available
                if (currentTime) {
                    const timeToSelect = currentTime.substring(0, 5); // Remove seconds
                    timeSelect.value = timeToSelect;
                }
            }
        })
        .catch(error => {
            console.error('Error loading available time slots:', error);
            // Fallback to all slots if API fails
            const timeSelect = document.getElementById('editBookingTime');
            timeSelect.innerHTML = '<option value="">اختر الوقت...</option>';
            
            allSlots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = formatTime(slot);
                timeSelect.appendChild(option);
            });
            
            // Select current time if provided
            if (currentTime) {
                const timeToSelect = currentTime.substring(0, 5); // Remove seconds
                timeSelect.value = timeToSelect;
            }
        });
}

function editSearchPatients() {
    const query = document.getElementById('editPatientSearch').value.trim();
    if (query.length < 2) {
        document.getElementById('editPatientSearchResults').style.display = 'none';
        return;
    }
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayEditPatientSearchResults(data.patients);
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
        });
}

function displayEditPatientSearchResults(patients) {
    const resultsDiv = document.getElementById('editPatientSearchResults');
    
    if (patients.length === 0) {
        resultsDiv.innerHTML = '<div class="list-group-item text-muted">لا توجد نتائج</div>';
    } else {
        resultsDiv.innerHTML = patients.map(patient => `
            <div class="list-group-item list-group-item-action" onclick="selectEditPatient(${patient.id}, '${patient.first_name} ${patient.last_name}', '${patient.phone}')">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 arabic-text">${patient.first_name} ${patient.last_name}</h6>
                    <small>${patient.phone}</small>
                </div>
                <small class="text-muted">${patient.dob || 'تاريخ الميلاد غير محدد'}</small>
            </div>
        `).join('');
    }
    
    resultsDiv.style.display = 'block';
}

function selectEditPatient(patientId, patientName, patientPhone) {
    document.getElementById('editSelectedPatientId').value = patientId;
    document.getElementById('editSelectedPatientInfo').innerHTML = `
        <div class="alert alert-info">
            <strong>المريض المحدد:</strong> ${patientName} - ${patientPhone}
        </div>
    `;
    document.getElementById('editSelectedPatientInfo').style.display = 'block';
    document.getElementById('editPatientSearchResults').style.display = 'none';
    document.getElementById('editPatientSearch').value = '';
}

function saveEditBooking() {
    if (!currentEditBookingId) return;
    
    // Show loading state
    const saveBtn = document.getElementById('saveEditBookingBtn');
    const btnText = saveBtn.querySelector('.btn-text');
    const spinner = saveBtn.querySelector('.spinner-border');
    
    btnText.classList.add('d-none');
    spinner.classList.remove('d-none');
    saveBtn.disabled = true;
    
    // Collect form data
    const formData = {
        booking_id: currentEditBookingId,
        patient_id: document.getElementById('editSelectedPatientId').value,
        doctor_id: document.getElementById('editDoctor').value,
        date: document.getElementById('editBookingDate').value,
        start_time: document.getElementById('editBookingTime').value,
        visit_type: document.getElementById('editVisitType').value,
        notes: document.getElementById('editNotes').value,
        additional_payment: parseFloat(document.getElementById('editAdditionalPayment').value) || 0,
        payment_method: document.getElementById('editPaymentMethod').value
    };
    
    // Validate required fields
    if (!formData.patient_id || !formData.doctor_id || !formData.date || !formData.start_time || !formData.visit_type) {
        showNotification('يرجى ملء جميع الحقول المطلوبة', 'warning');
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        saveBtn.disabled = false;
        return;
    }
    
    // Validate additional payment amount
    if (formData.additional_payment < 0) {
        showNotification('المبلغ الإضافي لا يمكن أن يكون سالباً', 'warning');
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        saveBtn.disabled = false;
        return;
    }
    
    fetch(`/secretary/bookings/${currentEditBookingId}/update`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification('تم تحديث الحجز بنجاح!', 'success');
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editBookingModal')).hide();
            // Refresh calendar immediately
            loadCalendar();
        } else {
            showNotification('خطأ في تحديث الحجز: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        showNotification('خطأ في تحديث الحجز', 'danger');
    })
    .finally(() => {
        // Reset button state
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        saveBtn.disabled = false;
        currentEditBookingId = null;
    });
}

function viewAppointmentDetails(appointmentId) {
    // Navigate to booking details page
    window.location.href = `/secretary/bookings/${appointmentId}`;
}

function searchPatients() {
    const query = document.getElementById('patientSearch').value.trim();
    
    if (query.length < 2) {
        document.getElementById('patientSearchResults').innerHTML = '';
        return;
    }
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayPatientSearchResults(data.data);
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
        });
}

function displayPatientSearchResults(patients) {
    const resultsContainer = document.getElementById('patientSearchResults');
    
    if (patients.length === 0) {
        resultsContainer.innerHTML = '<div class="search-result-item text-muted arabic-text">لم يتم العثور على مرضى</div>';
        return;
    }
    
    let html = '';
    patients.forEach(patient => {
        html += `
            <div class="search-result-item" onclick="selectPatient(${patient.id}, '${patient.first_name} ${patient.last_name}')">
                <div class="patient-name arabic-text">${patient.first_name} ${patient.last_name}</div>
                <div class="patient-details arabic-text">${patient.phone} • العمر: ${patient.age || 'غير محدد'}</div>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

function selectPatient(patientId, patientName) {
    document.getElementById('selectedPatientId').value = patientId;
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('patientSearchResults').innerHTML = '';
}

function updateStatistics(appointments = []) {
    // Update statistics based on current calendar data
    if (appointments.length > 0) {
        const totalBookings = appointments.length;
        const completedBookings = appointments.filter(apt => apt.status === 'Completed').length;
        const pendingBookings = appointments.filter(apt => apt.status === 'Booked').length;
        const checkedInBookings = appointments.filter(apt => apt.status === 'CheckedIn').length;
        
        document.getElementById('totalBookings').textContent = totalBookings;
        document.getElementById('completedBookings').textContent = completedBookings;
        document.getElementById('pendingBookings').textContent = pendingBookings;
        document.getElementById('checkedInBookings').textContent = checkedInBookings;
    } else {
        // Reset statistics if no appointments
        document.getElementById('totalBookings').textContent = '0';
        document.getElementById('completedBookings').textContent = '0';
        document.getElementById('pendingBookings').textContent = '0';
        document.getElementById('checkedInBookings').textContent = '0';
    }
}

function updateDateDisplay() {
    const display = document.getElementById('currentDateDisplay');
    const dateStr = currentDate.toISOString().split('T')[0];
    const displayDate = new Date(dateStr + 'T12:00:00');
    const formattedDate = displayDate.toLocaleDateString('ar-EG', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    display.textContent = formattedDate;
}

function updateLastUpdate() {
    const lastUpdate = document.getElementById('lastUpdate');
    const timeString = new Date().toLocaleTimeString('ar-EG');
    lastUpdate.textContent = `آخر تحديث: ${timeString}`;
}

function startAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    refreshInterval = setInterval(() => {
        loadCalendar();
    }, 60000); // 60 seconds
}

function getStatusBadgeClass(status) {
    const classes = {
        'Booked': 'bg-primary',
        'CheckedIn': 'bg-success',
        'InProgress': 'bg-warning',
        'Completed': 'bg-info',
        'Cancelled': 'bg-danger',
        'NoShow': 'bg-secondary',
        'Rescheduled': 'bg-info'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusDisplayText(status) {
    const statusTexts = {
        'Booked': 'محجوز',
        'CheckedIn': 'تم الحضور',
        'InProgress': 'قيد التنفيذ',
        'Completed': 'مكتمل',
        'Cancelled': 'ملغي',
        'NoShow': 'لم يحضر',
        'Rescheduled': 'مؤجل'
    };
    return statusTexts[status] || status;
}

function getStatusIcon(status) {
    const icons = {
        'Booked': 'bi-calendar-check',
        'CheckedIn': 'bi-check-circle-fill',
        'InProgress': 'bi-hourglass-split',
        'Completed': 'bi-check2-all',
        'Cancelled': 'bi-x-circle-fill',
        'NoShow': 'bi-clock-fill',
        'Rescheduled': 'bi-arrow-clockwise'
    };
    return icons[status] || 'bi-question-circle';
}

function formatTime(time) {
    if (!time) return '';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('ar-EG', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function getVisitTypeInArabic(visitType) {
    const visitTypes = {
        'New': 'زيارة جديدة',
        'FollowUp': 'إعادة زيارة',
        'Consultation': 'استشارة طبية'
    };
    return visitTypes[visitType] || visitType;
}

function getAllTimeSlots() {
    const slots = [];
    const startHour = 14; // 2 PM
    const endHour = 23;   // 11 PM
    
    for (let hour = startHour; hour <= endHour; hour++) {
        for (let minute = 0; minute < 60; minute += 15) {
            if (hour === endHour && minute > 0) break; // Stop at 11:00 PM
            const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            slots.push(timeString);
        }
    }
    
    return slots;
}

function isDateInPast(dateString) {
    return dateString < SERVER_DATE;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <div class="flex-grow-1 arabic-text">${message}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add Patient functionality - Age and Date of Birth conversion
function initializeAddPatientModal() {
    const addPatientForm = document.getElementById('addPatientForm');
    const addPatientModal = document.getElementById('addPatientModal');
    const addPatientSubmit = document.getElementById('addPatientSubmit');
    const addPatientMessage = document.getElementById('addPatientMessage');
    
    // Reset form when modal opens
    addPatientModal.addEventListener('show.bs.modal', function() {
        addPatientForm.reset();
        addPatientForm.classList.remove('was-validated');
        hideMessage();
        resetSubmitButton();
        
        // Focus on first name field
        setTimeout(() => {
            document.getElementById('firstName').focus();
        }, 300);
    });
    
    // Handle form submission
    addPatientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!addPatientForm.checkValidity()) {
            addPatientForm.classList.add('was-validated');
            showMessage('يرجى ملء جميع الحقول المطلوبة بشكل صحيح.', 'error');
            return;
        }
        
        // Additional validation
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const gender = document.getElementById('gender').value;
        
        if (!firstName || !lastName || !phone) {
            showMessage('الاسم الأول والاسم الأخير ورقم الهاتف مطلوبة.', 'error');
            return;
        }
        
        if (!gender) {
            showMessage('يرجى اختيار جنس المريض.', 'error');
            document.getElementById('gender').focus();
            return;
        }
        
        // Validate phone number format
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        const phoneRegex = /^(\+\d{1,3})?\d{7,15}$/;
        if (!phoneRegex.test(cleanPhone)) {
            showMessage('يرجى إدخال رقم هاتف صحيح (7-15 رقم، مع إمكانية إضافة رمز الدولة).', 'error');
            return;
        }
        
        // Submit form
        submitPatientForm();
    });
    
    function submitPatientForm() {
        const formData = new FormData(addPatientForm);
        
        // Show loading state
        setSubmitButtonLoading(true);
        hideMessage();
        
        // Send AJAX request
        fetch('/api/patients', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            setSubmitButtonLoading(false);
            
            if (data.ok) {
                // Success
                showMessage('تم إضافة المريض بنجاح!', 'success');
                
                // Save form data before resetting
                const formData = new FormData(addPatientForm);
                const savedFormData = {
                    first_name: formData.get('first_name'),
                    last_name: formData.get('last_name'),
                    phone: formData.get('phone'),
                    gender: formData.get('gender'),
                    dob: formData.get('dob'),
                    age: formData.get('age')
                };
                
                // Reset form
                addPatientForm.reset();
                addPatientForm.classList.remove('was-validated');
                
                // Close modal after delay and return to appointment modal
                setTimeout(() => {
                    bootstrap.Modal.getInstance(addPatientModal).hide();
                    
                    // Return to appointment modal with new patient selected
                    setTimeout(() => {
                        const appointmentModal = new bootstrap.Modal(document.getElementById('addBookingModal'));
                        appointmentModal.show();
                        
                        // Auto-select the new patient
                        const patientData = data.data || data.patient || data;
                        
                        if (patientData && (patientData.id || patientData.patient_id)) {
                            const patientInfo = {
                                id: patientData.id || patientData.patient_id,
                                first_name: savedFormData.first_name,
                                last_name: savedFormData.last_name,
                                phone: savedFormData.phone,
                                gender: savedFormData.gender,
                                dob: savedFormData.dob,
                                age: savedFormData.age
                            };
                            
                            selectNewPatient(patientInfo);
                            
                            // Set visit type to "New" automatically
                            document.getElementById('visitType').value = 'New';
                        } else {
                            showNotification('تم إضافة المريض ولكن لا يمكن تحديده تلقائياً. يرجى البحث عن المريض يدوياً.', 'warning');
                        }
                    }, 300);
                }, 1500);
                
            } else {
                // Error from server
                const errorMsg = data.error || data.message || 'فشل في إضافة المريض. يرجى المحاولة مرة أخرى.';
                showMessage(errorMsg, 'error');
                
                // Show validation errors if available
                if (data.details) {
                    showValidationErrors(data.details);
                }
            }
        })
        .catch(error => {
            setSubmitButtonLoading(false);
            showMessage('حدث خطأ أثناء إضافة المريض. يرجى المحاولة مرة أخرى.', 'error');
        });
    }
    
    function selectNewPatient(patientData) {
        const firstName = patientData.first_name || patientData.firstName || '';
        const lastName = patientData.last_name || patientData.lastName || '';
        const fullName = `${firstName} ${lastName}`.trim();
        const patientId = patientData.id || patientData.patient_id;
        const phone = patientData.phone || patientData.phone_number || '';
        const age = patientData.age || calculateAgeFromDOB(patientData.dob) || 'غير محدد';
        
        // Fill patient search field
        document.getElementById('patientSearch').value = fullName;
        document.getElementById('selectedPatientId').value = patientId;
        
        // Show patient info
        document.getElementById('patientSearchResults').innerHTML = `
            <div class="selected-patient-info alert alert-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>تم إضافة مريض جديد:</strong> ${fullName}<br>
                        <small>الهاتف: ${phone} • العمر: ${age}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                        تغيير المريض
                    </button>
                </div>
            </div>
        `;
    }
    
    function calculateAgeFromDOB(dob) {
        if (!dob) return null;
        try {
            const today = new Date();
            const birthDate = new Date(dob);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age > 0 ? age : null;
        } catch (error) {
            return null;
        }
    }
    
    function showMessage(message, type) {
        addPatientMessage.className = `alert alert-${type === 'error' ? 'danger' : type}`;
        addPatientMessage.textContent = message;
        addPatientMessage.classList.remove('d-none');
    }
    
    function hideMessage() {
        addPatientMessage.classList.add('d-none');
    }
    
    function setSubmitButtonLoading(loading) {
        const btnText = addPatientSubmit.querySelector('.btn-text');
        const spinner = addPatientSubmit.querySelector('.spinner-border');
        
        if (loading) {
            addPatientSubmit.disabled = true;
            btnText.textContent = 'جاري الإضافة...';
            spinner.classList.remove('d-none');
        } else {
            addPatientSubmit.disabled = false;
            btnText.textContent = 'إضافة المريض';
            spinner.classList.add('d-none');
        }
    }
    
    function resetSubmitButton() {
        setSubmitButtonLoading(false);
    }
    
    function showValidationErrors(errors) {
        // Clear previous validation errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
        
        // Show new validation errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[field];
                }
            }
        });
    }
    
    // Clear validation errors on input
    addPatientForm.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    });
    
    // Age and Date of Birth conversion
    const dobInput = document.getElementById('dob');
    const ageInput = document.getElementById('age');
    
    // Convert age to date of birth
    ageInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        if (age && age > 0 && age <= 150) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            const birthDate = new Date(birthYear, today.getMonth(), today.getDate());
            dobInput.value = birthDate.toISOString().split('T')[0];
            
            // Clear age field after conversion
            setTimeout(() => {
                this.value = '';
            }, 1000);
        }
    });
    
    // Convert date of birth to age
    dobInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age >= 0 && age <= 150) {
                ageInput.placeholder = `العمر المحسوب: ${age} سنة`;
                setTimeout(() => {
                    ageInput.placeholder = 'أدخل العمر بالسنوات';
                }, 3000);
            }
        }
    });
}

// Update financial dashboard cards
function updateFinancialCards() {
    fetch('/api/dashboard-summary', {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.data.dailyBalance) {
                // Update cards if they exist on the page
                const openingBalanceEl = document.getElementById('openingBalance');
                const totalReceivedEl = document.getElementById('totalReceived');
                const totalExpensesEl = document.getElementById('totalExpenses');
                const currentBalanceEl = document.getElementById('currentBalance');
                
                if (openingBalanceEl) {
                    openingBalanceEl.textContent = formatMoney(data.data.dailyBalance.opening_balance) + ' جنيه';
                }
                if (totalReceivedEl) {
                    totalReceivedEl.textContent = formatMoney(data.data.dailyBalance.total_received) + ' جنيه';
                }
                if (totalExpensesEl) {
                    totalExpensesEl.textContent = formatMoney(data.data.dailyBalance.total_expenses) + ' جنيه';
                }
                if (currentBalanceEl) {
                    currentBalanceEl.textContent = formatMoney(data.data.dailyBalance.current_balance) + ' جنيه';
                }
            }
        })
        .catch(error => {
            console.error('Error updating financial cards:', error);
        });
}

// Format money function
function formatMoney(amount) {
    return new Intl.NumberFormat('ar-EG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

// Initialize add patient modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAddPatientModal();
    
    // Check if patient_id is in URL and pre-select patient
    const urlParams = new URLSearchParams(window.location.search);
    const patientId = urlParams.get('patient_id');
    if (patientId) {
        // Set patient ID immediately
        document.getElementById('selectedPatientId').value = patientId;
        
        // Try to fetch patient details, but don't wait for it
        fetch(`/api/patients/${patientId}`, {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.ok && data.patient) {
                    const patient = data.patient;
                    const fullName = `${patient.first_name} ${patient.last_name}`;
                    
                    // Update patient search field with real data
                    document.getElementById('patientSearch').value = fullName;
                    
                    // Show patient info with real data
                    document.getElementById('patientSearchResults').innerHTML = `
                        <div class="selected-patient-info alert alert-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>المريض المحدد:</strong> ${fullName}<br>
                                    <small>الهاتف: ${patient.phone} • العمر: ${patient.age || 'غير محدد'}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                                    تغيير المريض
                                </button>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching patient:', error);
                // Show patient info with limited data
                document.getElementById('patientSearch').value = `مريض رقم ${patientId}`;
                document.getElementById('patientSearchResults').innerHTML = `
                    <div class="selected-patient-info alert alert-info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>المريض المحدد:</strong> مريض رقم ${patientId}<br>
                                <small>تم تحديد المريض مسبقاً</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                                تغيير المريض
                            </button>
                        </div>
                    </div>
                `;
            });
        
        // Show preselected label and set up UI
        document.getElementById('preselectedLabel').style.display = 'inline';
        
        // Make patient search field readonly
        const patientSearchField = document.getElementById('patientSearch');
        patientSearchField.readOnly = true;
        patientSearchField.style.backgroundColor = 'var(--bg)';
        patientSearchField.style.cursor = 'not-allowed';
        
        // Hide new patient button
        document.getElementById('newPatientBtn').style.display = 'none';
        
        // Set visit type to "New" automatically
        document.getElementById('visitType').value = 'New';
        updateVisitCost();
    }
});

// Clear preselected patient function
function clearPreselectedPatient() {
    // Clear form fields
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('patientSearch').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';
    
    // Enable patient search
    const patientSearchField = document.getElementById('patientSearch');
    const newPatientBtn = document.getElementById('newPatientBtn');
    const preselectedLabel = document.getElementById('preselectedLabel');
    
    patientSearchField.readOnly = false;
    patientSearchField.style.backgroundColor = '';
    patientSearchField.style.cursor = '';
    newPatientBtn.style.display = 'block';
    preselectedLabel.style.display = 'none';
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>