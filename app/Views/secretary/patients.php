<!-- Patients Header -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-people me-2"></i>
                إدارة المرضى
            </h4>
        </div>
        <p class="text-muted mb-0 arabic-text">عرض وإدارة سجلات المرضى</p>
        <div class="mt-2">
            <small class="text-muted arabic-text">
                <i class="bi bi-keyboard me-1"></i>
                اختصارات: 
                • مريض جديد <kbd class="me-1">N</kbd> أو <kbd class="me-1">ى</kbd> أو <kbd class="me-1">Ctrl+N</kbd> 
                • البحث <kbd class="me-1">F</kbd> أو <kbd class="me-1">ب</kbd>
                <kbd>Esc</kbd> إغلاق
            </small>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                <i class="bi bi-person-plus me-2"></i>
                مريض جديد
                <span class="ms-2">
                    <kbd>N</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ى</kbd>
                </span>
            </button>
            <button class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#searchModal" 
                    title="استخدم F أو ب للبحث في المرضى">
                <i class="bi bi-search me-2"></i>
                البحث
                <span class="ms-2">
                    <kbd>F</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ب</kbd>
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Patient Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number"><?= $stats['total'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">إجمالي المرضى</p>
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
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number"><?= $stats['active'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">مرضى نشطين</p>
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
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number"><?= $stats['recent'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">جدد هذا الشهر</p>
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
                        <i class="bi bi-gender-ambiguous"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number"><?= ($stats['gender']['Female'] ?? 0) ?></h3>
                        <p class="stat-label arabic-text">إناث</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 arabic-text">
            <i class="bi bi-funnel me-2"></i>
            فلاتر البحث
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label arabic-text">البحث</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="الاسم، الهاتف، الرقم القومي...">
                </div>
                <div class="col-md-2">
                    <label for="gender" class="form-label arabic-text">الجنس</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">الكل</option>
                        <?php foreach ($genderOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $gender === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="age_range" class="form-label arabic-text">الفئة العمرية</label>
                    <select class="form-select" id="age_range" name="age_range">
                        <option value="">الكل</option>
                        <?php foreach ($ageRangeOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $ageRange === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="last_visit" class="form-label arabic-text">آخر زيارة</label>
                    <select class="form-select" id="last_visit" name="last_visit">
                        <option value="">الكل</option>
                        <?php foreach ($lastVisitOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $lastVisit === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                        <a href="/secretary/patients" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Patients Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-table me-2"></i>
                    قائمة المرضى
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Quick Search -->
                    <div class="d-flex align-items-center">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="quickSearch" 
                                   placeholder="بحث سريع..."
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearQuickSearch">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Items per page -->
                    <div class="d-flex align-items-center">
                        <label for="paginationLimit" class="form-label mb-0 me-2 text-muted arabic-text">عرض:</label>
                        <select class="form-select form-select-sm" id="paginationLimit" style="width: auto;">
                            <option value="10" class="arabic-text">10</option>
                            <option value="20" selected class="arabic-text">20</option>
                            <option value="30" class="arabic-text">30</option>
                            <option value="50" class="arabic-text">50</option>
                            <option value="all" class="arabic-text">الكل</option>
                        </select>
                    </div>
                    <div class="text-muted">
                        <small class="arabic-text">المجموع: <span id="totalPatientsCount"><?= count($patients) ?></span> مريض</small>
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
                        <th class="arabic-text">بيانات المريض</th>
                        <th class="arabic-text">التواصل</th>
                        <th class="arabic-text">العمر</th>
                        <th class="arabic-text">آخر زيارة</th>
                        <th class="arabic-text">إجمالي المدفوعات</th>
                        <th class="arabic-text">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="patientsTableBody">
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0 arabic-text">لا توجد سجلات مرضى</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <?php
                            $age = $viewHelper->calculateAge($patient['dob']);
                            $lastVisit = $patient['last_visit'] ? $viewHelper->formatDateSimple($patient['last_visit']) : 'لم يزر بعد';
                            $firstName = $patient['first_name'] ?? '';
                            $lastName = $patient['last_name'] ?? '';
                            $fullName = trim($firstName . ' ' . $lastName);
                            $firstChar = !empty($firstName) ? strtoupper($firstName[0]) : '؟';
                            $lastChar = !empty($lastName) ? strtoupper($lastName[0]) : '؟';
                            $avatarInitials = $firstChar . '.' . $lastChar;
                            $avatarClass = ($patient['gender'] ?? '') === 'Female' ? 'avatar-circle avatar-female me-3' : 'avatar-circle avatar-male me-3';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="<?= $avatarClass ?>">
                                            <?= $avatarInitials ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 arabic-text"><?= htmlspecialchars($fullName) ?></h6>
                                            <small class="text-muted">ID: #<?= $patient['id'] ?></small>
                                            <?php if (!empty($patient['gender'])): ?>
                                                <br><small class="text-muted">
                                                    <i class="bi bi-<?= $patient['gender'] === 'Female' ? 'gender-female' : 'gender-male' ?> me-1"></i>
                                                    <?= $patient['gender'] === 'Female' ? 'أنثى' : 'ذكر' ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="bi bi-telephone me-1"></i>
                                        <?= htmlspecialchars($patient['phone'] ?? 'غير متوفر') ?>
                                    </div>
                                    <?php if (!empty($patient['alt_phone'])): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-telephone-plus me-1"></i>
                                            <small class="text-muted"><?= htmlspecialchars($patient['alt_phone']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['national_id'])): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-card-text me-1"></i>
                                            <small class="text-muted"><?= htmlspecialchars($patient['national_id']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['emergency_contact'])): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-person-heart me-1"></i>
                                            <small class="text-muted"><?= htmlspecialchars($patient['emergency_contact']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $age !== 'غير محدد' ? $age . ' سنة' : '<span class="text-muted">غير محدد</span>' ?>
                                </td>
                                <td>
                                    <?= $patient['last_visit'] ? 
                                        '<span class="badge bg-success arabic-text">' . $lastVisit . '</span>' : 
                                        '<span class="badge bg-secondary arabic-text">لم يزر بعد</span>'
                                    ?>
                                </td>
                                <td>
                                    <?php if (($patient['total_paid'] ?? 0) > 0): ?>
                                        <span class="badge bg-success arabic-text"><?= number_format($patient['total_paid'], 2) ?> جنيه</span>
                                        <br><small class="text-muted"><?= $patient['total_appointments'] ?? 0 ?> زيارة</small>
                                    <?php else: ?>
                                        <span class="badge bg-secondary arabic-text">لم يدفع</span>
                                        <br><small class="text-muted"><?= $patient['total_appointments'] ?? 0 ?> زيارة</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/secretary/patients/<?= $patient['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           data-bs-title="عرض تفاصيل المريض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-success btn-sm" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="حجز موعد جديد"
                                                onclick="openBookingModal(<?= $patient['id'] ?>, '<?= htmlspecialchars($fullName) ?>')">
                                            <i class="bi bi-calendar-plus"></i>
                                        </button>
                                        <a href="/secretary/payments?patient_id=<?= $patient['id'] ?>" 
                                           class="btn btn-outline-info btn-sm" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           data-bs-title="عرض المدفوعات">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-search me-2"></i>
                    البحث في المرضى
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Search Input -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="globalSearch" 
                               placeholder="ابحث بالاسم أو رقم الهاتف أو الرقم القومي..."
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-text d-flex justify-content-between align-items-center search-help-text">
                        <span class="search-instruction arabic-text">
                            <i class="bi bi-info-circle me-1"></i>
                            ابدأ بالكتابة للبحث تلقائياً
                        </span>
                        <small class="search-shortcut">
                            <kbd>Ctrl</kbd>+<kbd>F</kbd> للتركيز على البحث
                        </small>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="searchResults">
                    <!-- Loading State -->
                    <div id="searchLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden arabic-text">جاري البحث...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0 arabic-text">جاري البحث في المرضى...</p>
                    </div>

                    <!-- No Results -->
                    <div id="noResults" class="text-center py-4" style="display: none;">
                        <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2 arabic-text">لا توجد نتائج</h6>
                        <p class="text-muted mb-0 arabic-text">جرب مصطلحات بحث مختلفة</p>
                    </div>

                    <!-- Results Container -->
                    <div id="searchResultsList" class="search-results-container">
                        <!-- Results will be populated here -->
                    </div>

                    <!-- Initial State -->
                    <div id="searchInitial" class="text-center py-4">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2 arabic-text">البحث في المرضى</h6>
                        <p class="text-muted mb-0 arabic-text">أدخل الاسم أو رقم الهاتف أو الرقم القومي للبحث</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">إغلاق</button>
            </div>
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
                                <label for="altPhone" class="form-label arabic-text">رقم هاتف بديل</label>
                                <input type="tel" class="form-control" id="altPhone" name="alt_phone" maxlength="20">
                                <div class="form-text arabic-text">رقم هاتف إضافي (اختياري)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nationalId" class="form-label arabic-text">الرقم القومي</label>
                                <input type="text" class="form-control" id="nationalId" name="national_id" maxlength="20">
                                <div class="form-text arabic-text">الرقم القومي (اختياري)</div>
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

<script>
// Helper functions
function calculateAge(dob) {
    if (!dob) return 'غير محدد';
    
    const today = new Date();
    const birthDate = new Date(dob);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

function formatDate(dateString) {
    if (!dateString) return 'غير محدد';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Search functionality
let searchTimeout;
let currentSearchRequest;

function searchPatients(query) {
    const searchLoading = document.getElementById('searchLoading');
    const searchInitial = document.getElementById('searchInitial');
    const noResults = document.getElementById('noResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    // Hide all states
    searchInitial.style.display = 'none';
    noResults.style.display = 'none';
    searchResultsList.style.display = 'none';
    
    if (!query || query.trim().length < 2) {
        searchInitial.style.display = 'block';
        return;
    }
    
    // Show loading
    searchLoading.style.display = 'block';
    
    // Cancel previous request
    if (currentSearchRequest) {
        currentSearchRequest.abort();
    }
    
    // Create new request
    currentSearchRequest = new AbortController();
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query.trim())}`, {
        signal: currentSearchRequest.signal
    })
    .then(response => response.json())
    .then(data => {
        searchLoading.style.display = 'none';
        
        if (data.ok && data.data && data.data.length > 0) {
            displaySearchResults(data.data, query.trim());
        } else {
            noResults.style.display = 'block';
        }
    })
    .catch(error => {
        searchLoading.style.display = 'none';
        if (error.name !== 'AbortError') {
            console.error('Search error:', error);
            noResults.style.display = 'block';
        }
    });
}

function displaySearchResults(patients, searchTerm) {
    const searchResultsList = document.getElementById('searchResultsList');
    let html = '';
    
    patients.forEach(patient => {
        const fullName = `${patient.first_name} ${patient.last_name}`;
        const age = patient.dob ? calculateAge(patient.dob) : 'غير محدد';
        const lastVisit = patient.last_visit ? formatDate(patient.last_visit) : 'لم يزر';
        
        // Highlight search terms
        const highlightedName = highlightSearchTerm(fullName, searchTerm);
        const highlightedPhone = highlightSearchTerm(patient.phone || '', searchTerm);
        const highlightedNationalId = highlightSearchTerm(patient.national_id || '', searchTerm);
        
        html += `
            <div class="search-result-item" onclick="selectSearchResult(${patient.id})">
                <div class="d-flex align-items-center">
                    <div class="search-result-avatar ${patient.gender === 'Female' ? 'avatar-female' : 'avatar-male'} me-3">
                        ${getAvatarInitials(patient.first_name, patient.last_name)}
                    </div>
                    <div class="search-result-info flex-grow-1">
                        <h6 class="mb-1 arabic-text">${highlightedName}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    ${highlightedPhone || 'لا يوجد هاتف'}
                                </small>
                                ${patient.alt_phone ? `<small class="text-muted d-block">
                                    <i class="bi bi-telephone-plus me-1"></i>
                                    ${patient.alt_phone}
                                </small>` : ''}
                                ${patient.national_id ? `<small class="text-muted d-block">
                                    <i class="bi bi-card-text me-1"></i>
                                    الرقم القومي: ${highlightedNationalId}
                                </small>` : ''}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-person me-1"></i>
                                    العمر: ${age} سنة
                                </small>
                                ${patient.emergency_contact ? `<small class="text-muted d-block">
                                    <i class="bi bi-person-heart me-1"></i>
                                    طوارئ: ${patient.emergency_contact}
                                </small>` : ''}
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-primary me-2 arabic-text">${patient.total_appointments || 0} زيارة</span>
                            <span class="badge bg-success arabic-text">آخر زيارة: ${lastVisit}</span>
                        </div>
                    </div>
                    <div class="search-result-actions ms-3">
                        <div class="btn-group-vertical">
                            <a href="/secretary/patients/${patient.id}" class="btn btn-sm btn-outline-primary arabic-text">
                                <i class="bi bi-eye me-1"></i>عرض
                            </a>
                            <a href="/secretary/bookings?patient_id=${patient.id}" class="btn btn-sm btn-outline-success arabic-text">
                                <i class="bi bi-calendar-plus me-1"></i>حجز
                            </a>
                            <a href="/secretary/payments?patient_id=${patient.id}" class="btn btn-sm btn-outline-info arabic-text">
                                <i class="bi bi-credit-card me-1"></i>دفعات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    searchResultsList.innerHTML = html;
    searchResultsList.style.display = 'block';
}

function selectSearchResult(patientId) {
    window.location.href = `/secretary/patients/${patientId}`;
}

function highlightSearchTerm(text, searchTerm) {
    if (!text || !searchTerm) return text;
    
    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<span class="search-highlight">$1</span>');
}

function getAvatarInitials(firstName, lastName) {
    if (!firstName || !lastName) {
        return '؟.؟';
    }
    
    const firstChar = firstName.charAt(0).toUpperCase();
    const lastChar = lastName.charAt(0).toUpperCase();
    
    return firstChar + '.' + lastChar;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Quick search
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    
    if (quickSearch) {
        quickSearch.addEventListener('input', function() {
            filterPatientsBySearch(this.value);
        });
        
        if (clearQuickSearch) {
            clearQuickSearch.addEventListener('click', function() {
                quickSearch.value = '';
                filterPatientsBySearch('');
                quickSearch.focus();
            });
        }
    }
    
    // Global search
    const globalSearch = document.getElementById('globalSearch');
    const clearSearch = document.getElementById('clearSearch');
    const searchModal = document.getElementById('searchModal');
    
    if (globalSearch) {
        globalSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchPatients(this.value);
            }, 300);
        });
        
        if (clearSearch) {
            clearSearch.addEventListener('click', function() {
                globalSearch.value = '';
                globalSearch.focus();
                document.getElementById('searchInitial').style.display = 'block';
                document.getElementById('searchLoading').style.display = 'none';
                document.getElementById('noResults').style.display = 'none';
                document.getElementById('searchResultsList').style.display = 'none';
            });
        }
    }
    
    // Focus search input when modal opens
    if (searchModal) {
        searchModal.addEventListener('shown.bs.modal', function() {
            globalSearch.focus();
        });
        
        // Reset search when modal closes
        searchModal.addEventListener('hidden.bs.modal', function() {
            globalSearch.value = '';
            document.getElementById('searchInitial').style.display = 'block';
            document.getElementById('searchLoading').style.display = 'none';
            document.getElementById('noResults').style.display = 'none';
            document.getElementById('searchResultsList').style.display = 'none';
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const isModalOpen = document.querySelector('.modal.show');
        const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                             e.target.contentEditable === 'true';
        
        // Open search modal with 'F' key or Arabic 'ب' key
        const searchKeys = ['f', 'ب'];
        const isSearchKey = searchKeys.includes(e.key.toLowerCase()) || searchKeys.includes(e.key);
        
        if (isSearchKey && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#searchModal"]').click();
        }
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                e.preventDefault();
                bootstrap.Modal.getInstance(openModal).hide();
            }
        }
    });
    
    // Initialize Bootstrap Tooltips
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

function filterPatientsBySearch(query) {
    const rows = document.querySelectorAll('#patientsTableBody tr');
    const searchTerm = query.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openBookingModal(patientId, patientName) {
    // Redirect to bookings page with patient pre-selected
    window.location.href = `/secretary/bookings?patient_id=${patientId}`;
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
                
                // Reset form
                addPatientForm.reset();
                addPatientForm.classList.remove('was-validated');
                
                // Close modal after delay and refresh page
                setTimeout(() => {
                    bootstrap.Modal.getInstance(addPatientModal).hide();
                    // Refresh the page to show the new patient
                    window.location.reload();
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

// Initialize add patient modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAddPatientModal();
});
</script>

<style>
/* RTL specific adjustments */
.me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
.me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
.ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
.ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
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

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

/* Gender-based avatar colors */
.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.avatar-male:hover {
    background: #2980b9;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.avatar-female:hover {
    background: #c2185b;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
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

/* Search Modal Styles */
.modal-content {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.modal-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

.form-control {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.form-control:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.input-group-text {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.search-results-container {
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background: var(--bg);
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-result-item:hover {
    border-color: var(--accent);
    background: var(--bg-alt);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-result-item:last-child {
    margin-bottom: 0;
}

.search-result-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.search-result-avatar.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.search-result-avatar.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.search-result-info h6 {
    margin-bottom: 5px;
    color: var(--text);
}

.search-result-info .text-muted {
    font-size: 0.9rem;
    color: var(--muted) !important;
}

.search-result-actions .btn {
    padding: 5px 10px;
    font-size: 0.85rem;
}

.search-highlight {
    background-color: rgba(255, 193, 7, 0.3);
    padding: 1px 3px;
    border-radius: 3px;
    font-weight: 600;
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

/* Search help text styling for dark mode */
.search-help-text {
    background: rgba(var(--accent-rgb), 0.05);
    border: 1px solid rgba(var(--accent-rgb), 0.15);
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 8px;
    transition: all 0.2s ease;
}

.search-help-text:hover {
    background: rgba(var(--accent-rgb), 0.08);
    border-color: rgba(var(--accent-rgb), 0.2);
}

.search-help-text .search-instruction {
    color: var(--text);
    font-weight: 500;
    font-size: 0.875rem;
}

.search-help-text .search-instruction i {
    color: var(--accent);
    opacity: 0.8;
    margin-right: 4px;
}

.search-help-text .search-shortcut {
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 400;
}

.search-help-text kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.7rem;
    padding: 2px 6px;
    margin: 0 1px;
    border-radius: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Courier New', 'Cairo', monospace;
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
</style>