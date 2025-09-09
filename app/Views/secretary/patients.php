<!-- Patients Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary arabic-text">
            <i class="bi bi-people me-2"></i>
            إدارة المرضى
        </h4>
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
    <div class="col-md-4 text-end">
        <div class="btn-group" role="group">
            <a href="/secretary/patients/new" class="btn btn-success">
                <i class="bi bi-person-plus me-2"></i>
                مريض جديد
                <span class="ms-2">
                    <kbd>N</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ى</kbd>
                </span>
            </a>
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

<!-- Patients Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count($patients) ?></h3>
                <p class="text-muted mb-0 arabic-text">إجمالي المرضى</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($patients, fn($p) => $p['last_visit'] && date('Y-m-d', strtotime($p['last_visit'])) >= date('Y-m-d', strtotime('-7 days')))) ?></h3>
                <p class="text-muted mb-0 arabic-text">زيارات هذا الأسبوع</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-person-plus text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($patients, fn($p) => date('Y-m-d', strtotime($p['created_at'])) >= date('Y-m-d', strtotime('-30 days')))) ?></h3>
                <p class="text-muted mb-0 arabic-text">جدد هذا الشهر</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($patients, fn($p) => !$p['last_visit'] || date('Y-m-d', strtotime($p['last_visit'])) < date('Y-m-d', strtotime('-30 days')))) ?></h3>
                <p class="text-muted mb-0 arabic-text">غير نشطين</p>
            </div>
        </div>
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
                        <th class="arabic-text">إجمالي الزيارات</th>
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
                            $age = $patient['dob'] ? calculateAge($patient['dob']) : 'غير محدد';
                            $lastVisit = $patient['last_visit'] ? formatDate($patient['last_visit']) : 'لم يزر بعد';
                            $firstName = $patient['first_name'] || '';
                            $lastName = $patient['last_name'] || '';
                            $fullName = trim($firstName . ' ' . $lastName);
                            $firstChar = $firstName ? strtoupper($firstName[0]) : '؟';
                            $lastChar = $lastName ? strtoupper($lastName[0]) : '؟';
                            $avatarInitials = $firstChar . '.' . $lastChar;
                            $avatarClass = $patient['gender'] === 'Female' ? 'avatar-circle avatar-female me-3' : 'avatar-circle avatar-male me-3';
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
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="bi bi-telephone me-1"></i>
                                        <?= htmlspecialchars($patient['phone'] ?? 'غير متوفر') ?>
                                    </div>
                                    <?php if ($patient['alt_phone']): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-telephone-plus me-1"></i>
                                            <small class="text-muted"><?= htmlspecialchars($patient['alt_phone']) ?></small>
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
                                    <span class="badge bg-primary arabic-text"><?= $patient['total_appointments'] ?? 0 ?></span>
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
                                        <a href="/secretary/bookings?patient_id=<?= $patient['id'] ?>" 
                                           class="btn btn-outline-success btn-sm" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           data-bs-title="حجز موعد جديد">
                                            <i class="bi bi-calendar-plus"></i>
                                        </a>
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

<script>
// Helper functions
function calculateAge(dob) {
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
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-person me-1"></i>
                                    العمر: ${age} سنة
                                </small>
                                ${patient.national_id ? `<small class="text-muted d-block">
                                    <i class="bi bi-card-text me-1"></i>
                                    الرقم القومي: ${highlightedNationalId}
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
