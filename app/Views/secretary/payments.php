<!-- Payments Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary arabic-text">
            <i class="bi bi-credit-card me-2"></i>
            إدارة المدفوعات
        </h4>
        <p class="text-muted mb-0 arabic-text">تتبع وإدارة مدفوعات المرضى</p>
        <div class="mt-2">
            <small class="text-muted arabic-text">
                <i class="bi bi-keyboard me-1"></i>
                اختصارات: 
                • تسجيل دفعة <kbd class="me-1">P</kbd> أو <kbd class="me-1">ث</kbd> أو <kbd class="me-1">Ctrl+P</kbd> 
                • البحث <kbd class="me-1">F</kbd> أو <kbd class="me-1">ب</kbd>
                <kbd>Esc</kbd> إغلاق
            </small>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group" role="group">
            <button class="btn btn-success" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addPaymentModal" 
                    title="استخدم P أو ث أو Ctrl+P لتسجيل دفعة جديدة">
                <i class="bi bi-plus-circle me-2"></i>
                تسجيل دفعة
                <span class="ms-2">
                    <kbd>P</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ث</kbd>
                </span>
            </button>
            <button class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#searchModal" 
                    title="استخدم F أو ب للبحث في المدفوعات">
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

<!-- Payment Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= $paymentStats['total_amount'] ?? 0 ?></h3>
                <p class="text-muted mb-0 arabic-text">إجمالي المدفوعات</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= $paymentStats['today_count'] ?? 0 ?></h3>
                <p class="text-muted mb-0 arabic-text">مدفوعات اليوم</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= $paymentStats['pending_count'] ?? 0 ?></h3>
                <p class="text-muted mb-0 arabic-text">في الانتظار</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-graph-up text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= $paymentStats['monthly_amount'] ?? 0 ?></h3>
                <p class="text-muted mb-0 arabic-text">هذا الشهر</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-list-ul me-2"></i>
                    المدفوعات الأخيرة
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
                    <!-- Filter by status -->
                    <div class="d-flex align-items-center">
                        <label for="statusFilter" class="form-label mb-0 me-2 text-muted arabic-text">الحالة:</label>
                        <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                            <option value="all" class="arabic-text">الكل</option>
                            <option value="Completed" class="arabic-text">مكتمل</option>
                            <option value="Pending" class="arabic-text">في الانتظار</option>
                            <option value="Failed" class="arabic-text">فشل</option>
                        </select>
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
                        <th class="arabic-text">التاريخ</th>
                        <th class="arabic-text">المريض</th>
                        <th class="arabic-text">المبلغ</th>
                        <th class="arabic-text">النوع</th>
                        <th class="arabic-text">الحالة</th>
                        <th class="arabic-text">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <?php if (empty($recentPayments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0 arabic-text">لا توجد مدفوعات</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPayments as $payment): ?>
                            <tr data-status="<?= $payment['status'] ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-2 text-primary"></i>
                                        <?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold arabic-text"><?= $payment['first_name'] . ' ' . $payment['last_name'] ?></div>
                                            <small class="text-muted"><?= $payment['phone'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success"><?= $this->formatMoney($payment['amount']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $this->getPaymentTypeBadgeClass($payment['type']) ?> arabic-text">
                                        <?= $this->getPaymentTypeText($payment['type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $this->getStatusBadgeClass($payment['status']) ?> arabic-text">
                                        <?= $this->getStatusText($payment['status']) ?>
                                    </span>
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
                                        <?php if ($payment['status'] === 'Pending'): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                    onclick="approvePayment(<?= $payment['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="الموافقة على الدفعة">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-plus-circle me-2"></i>
                    تسجيل دفعة جديدة
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="addPaymentMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <!-- Patient Selection -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-person me-1"></i>
                                بيانات المريض
                            </h6>
                            
                            <div class="mb-3">
                                <label for="patientSearch" class="form-label arabic-text">البحث عن المريض <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="patientSearch" 
                                           placeholder="ابحث بالاسم أو رقم الهاتف..."
                                           autocomplete="off">
                                </div>
                                <div id="patientSearchResults" class="mt-2" style="display: none;">
                                    <!-- Search results will appear here -->
                                </div>
                                <input type="hidden" id="selectedPatientId" name="patient_id">
                            </div>
                            
                            <div id="selectedPatientInfo" class="card border-success" style="display: none;">
                                <div class="card-body">
                                    <h6 class="arabic-text">المريض المحدد:</h6>
                                    <div id="patientDetails"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Details -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-credit-card me-1"></i>
                                تفاصيل الدفعة
                            </h6>
                            
                            <div class="mb-3">
                                <label for="amount" class="form-label arabic-text">المبلغ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="amount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="paymentType" class="form-label arabic-text">نوع الدفعة <span class="text-danger">*</span></label>
                                <select class="form-select" id="paymentType" name="payment_type" required>
                                    <option value="" class="arabic-text">-- اختر نوع الدفعة --</option>
                                    <option value="Cash" class="arabic-text">نقدي</option>
                                    <option value="Card" class="arabic-text">بطاقة ائتمان</option>
                                    <option value="Bank Transfer" class="arabic-text">تحويل بنكي</option>
                                    <option value="Insurance" class="arabic-text">تأمين</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label arabic-text">الوصف</label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="وصف الدفعة..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="paymentDate" class="form-label arabic-text">تاريخ الدفعة</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="paymentDate" 
                                       name="payment_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success arabic-text" id="addPaymentSubmit">
                        <i class="bi bi-plus-circle me-1"></i>
                        <span class="btn-text arabic-text">تسجيل الدفعة</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Payment management functions
function viewPayment(paymentId) {
    window.location.href = `/secretary/payments/${paymentId}`;
}

function printReceipt(paymentId) {
    window.open(`/secretary/payments/${paymentId}/receipt`, '_blank');
}

function approvePayment(paymentId) {
    if (confirm('الموافقة على هذه الدفعة؟')) {
        updatePaymentStatus(paymentId, 'Completed');
    }
}

function updatePaymentStatus(paymentId, status) {
    fetch(`/api/payments/${paymentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            location.reload();
        } else {
            alert('خطأ: ' + (data.error || 'فشل في تحديث حالة الدفعة'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('خطأ في تحديث حالة الدفعة');
    });
}

// Patient search functionality
let patientSearchTimeout;
let currentPatientSearchRequest;

function searchPatients(query) {
    if (!query || query.trim().length < 2) {
        document.getElementById('patientSearchResults').style.display = 'none';
        return;
    }
    
    // Cancel previous request
    if (currentPatientSearchRequest) {
        currentPatientSearchRequest.abort();
    }
    
    // Create new request
    currentPatientSearchRequest = new AbortController();
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query.trim())}`, {
        signal: currentPatientSearchRequest.signal
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.data && data.data.length > 0) {
            displayPatientSearchResults(data.data);
        } else {
            document.getElementById('patientSearchResults').innerHTML = '<div class="p-3 text-center text-muted arabic-text">لا توجد نتائج</div>';
            document.getElementById('patientSearchResults').style.display = 'block';
        }
    })
    .catch(error => {
        if (error.name !== 'AbortError') {
            console.error('Search error:', error);
            document.getElementById('patientSearchResults').innerHTML = '<div class="p-3 text-center text-danger arabic-text">خطأ في البحث</div>';
            document.getElementById('patientSearchResults').style.display = 'block';
        }
    });
}

function displayPatientSearchResults(patients) {
    const container = document.getElementById('patientSearchResults');
    let html = '';
    
    patients.forEach(patient => {
        const fullName = `${patient.first_name} ${patient.last_name}`;
        html += `
            <div class="patient-search-item" onclick="selectPatient(${patient.id}, '${fullName}', '${patient.phone || ''}')">
                <div class="fw-semibold arabic-text">${fullName}</div>
                <small class="text-muted">${patient.phone || 'لا يوجد رقم هاتف'}</small>
            </div>
        `;
    });
    
    container.innerHTML = html;
    container.style.display = 'block';
}

function selectPatient(patientId, patientName, patientPhone) {
    document.getElementById('selectedPatientId').value = patientId;
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('patientSearchResults').style.display = 'none';
    
    // Show selected patient info
    const patientInfo = document.getElementById('selectedPatientInfo');
    const patientDetails = document.getElementById('patientDetails');
    patientDetails.innerHTML = `
        <div class="arabic-text">
            <strong>الاسم:</strong> ${patientName}<br>
            <strong>الهاتف:</strong> ${patientPhone || 'غير محدد'}
        </div>
    `;
    patientInfo.style.display = 'block';
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Set current date and time as default
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('paymentDate').value = localDateTime;
    
    // Patient search
    const patientSearch = document.getElementById('patientSearch');
    if (patientSearch) {
        patientSearch.addEventListener('input', function() {
            clearTimeout(patientSearchTimeout);
            patientSearchTimeout = setTimeout(() => {
                searchPatients(this.value);
            }, 300);
        });
    }
    
    // Add payment form submission
    const addPaymentForm = document.getElementById('addPaymentForm');
    if (addPaymentForm) {
        addPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!addPaymentForm.checkValidity()) {
                addPaymentForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(addPaymentForm);
            
            // Show loading state
            const submitButton = document.getElementById('addPaymentSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'جاري التسجيل...';
            spinner.classList.remove('d-none');
            
            fetch('/api/payments', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'تسجيل الدفعة';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('addPaymentMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'تم تسجيل الدفعة بنجاح!';
                    messageEl.classList.remove('d-none');
                    
                    // Reset form
                    addPaymentForm.reset();
                    addPaymentForm.classList.remove('was-validated');
                    document.getElementById('selectedPatientInfo').style.display = 'none';
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('addPaymentModal')).hide();
                        location.reload();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('addPaymentMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'فشل في تسجيل الدفعة';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'تسجيل الدفعة';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('addPaymentMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'خطأ في تسجيل الدفعة';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterPaymentsByStatus(this.value);
        });
    }
    
    // Quick search
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    
    if (quickSearch) {
        quickSearch.addEventListener('input', function() {
            filterPaymentsBySearch(this.value);
        });
        
        if (clearQuickSearch) {
            clearQuickSearch.addEventListener('click', function() {
                quickSearch.value = '';
                filterPaymentsBySearch('');
                quickSearch.focus();
            });
        }
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const isModalOpen = document.querySelector('.modal.show');
        const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                             e.target.contentEditable === 'true';
        
        // Open add payment modal with 'P' key or Arabic 'ث' key
        const addPaymentKeys = ['p', 'ث'];
        const isAddPaymentKey = addPaymentKeys.includes(e.key.toLowerCase()) || addPaymentKeys.includes(e.key);
        const isCtrlP = (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p';
        
        if ((isAddPaymentKey || isCtrlP) && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#addPaymentModal"]').click();
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

function filterPaymentsByStatus(status) {
    const rows = document.querySelectorAll('#paymentsTableBody tr[data-status]');
    
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterPaymentsBySearch(query) {
    const rows = document.querySelectorAll('#paymentsTableBody tr[data-status]');
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

/* Patient search results styling */
#patientSearchResults {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--card);
}

.patient-search-item {
    padding: 10px 15px;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.patient-search-item:hover {
    background-color: var(--bg);
}

.patient-search-item:last-child {
    border-bottom: none;
}

.patient-search-item.selected {
    background-color: var(--accent);
    color: white;
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
</style>
