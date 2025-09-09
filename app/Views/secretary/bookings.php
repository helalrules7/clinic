<!-- Bookings Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary arabic-text">
            <i class="bi bi-calendar-check me-2"></i>
            إدارة الحجوزات
        </h4>
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
    <div class="col-md-4 text-end">
        <div class="btn-group" role="group">
            <button class="btn btn-success" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addBookingModal" 
                    title="استخدم N أو ى أو Ctrl+N لحجز موعد جديد">
                <i class="bi bi-calendar-plus me-2"></i>
                حجز جديد
                <span class="ms-2">
                    <kbd>N</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ى</kbd>
                </span>
            </button>
            <button class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#searchModal" 
                    title="استخدم F أو ب للبحث في الحجوزات">
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

<!-- Bookings Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count($todayBookings) ?></h3>
                <p class="text-muted mb-0 arabic-text">حجوزات اليوم</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($todayBookings, fn($b) => $b['status'] === 'Completed')) ?></h3>
                <p class="text-muted mb-0 arabic-text">مكتملة</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($todayBookings, fn($b) => $b['status'] === 'Booked')) ?></h3>
                <p class="text-muted mb-0 arabic-text">في الانتظار</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-person-check text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($todayBookings, fn($b) => $b['status'] === 'CheckedIn')) ?></h3>
                <p class="text-muted mb-0 arabic-text">تم الحضور</p>
            </div>
        </div>
    </div>
</div>

<!-- Today's Bookings -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-calendar-day me-2"></i>
                    حجوزات اليوم
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
                            <option value="Booked" class="arabic-text">محجوز</option>
                            <option value="CheckedIn" class="arabic-text">تم الحضور</option>
                            <option value="Completed" class="arabic-text">مكتمل</option>
                            <option value="Cancelled" class="arabic-text">ملغي</option>
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
                        <th class="arabic-text">الوقت</th>
                        <th class="arabic-text">المريض</th>
                        <th class="arabic-text">الطبيب</th>
                        <th class="arabic-text">نوع الزيارة</th>
                        <th class="arabic-text">الحالة</th>
                        <th class="arabic-text">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <?php if (empty($todayBookings)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0 arabic-text">لا توجد حجوزات لهذا اليوم</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($todayBookings as $booking): ?>
                            <tr data-status="<?= $booking['status'] ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock me-2 text-primary"></i>
                                        <?= date('H:i', strtotime($booking['start_time'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold arabic-text"><?= $booking['first_name'] . ' ' . $booking['last_name'] ?></div>
                                            <small class="text-muted"><?= $booking['phone'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info arabic-text"><?= $booking['doctor_name'] ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $this->getVisitTypeBadgeClass($booking['visit_type']) ?> arabic-text">
                                        <?= $booking['visit_type'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $this->getStatusBadgeClass($booking['status']) ?> arabic-text">
                                        <?= $this->getStatusText($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="viewBooking(<?= $booking['id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="عرض تفاصيل الحجز">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($booking['status'] === 'Booked'): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                    onclick="checkInPatient(<?= $booking['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="تأكيد حضور المريض">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (in_array($booking['status'], ['Booked', 'CheckedIn'])): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="cancelBooking(<?= $booking['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="إلغاء الحجز">
                                                <i class="bi bi-x-circle"></i>
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

<script>
// Booking management functions
function viewBooking(bookingId) {
    window.location.href = `/secretary/bookings/${bookingId}`;
}

function checkInPatient(bookingId) {
    if (confirm('تأكيد حضور المريض؟')) {
        updateBookingStatus(bookingId, 'CheckedIn');
    }
}

function cancelBooking(bookingId) {
    if (confirm('هل أنت متأكد من إلغاء هذا الحجز؟')) {
        updateBookingStatus(bookingId, 'Cancelled');
    }
}

function updateBookingStatus(bookingId, status) {
    fetch(`/api/bookings/${bookingId}`, {
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
            alert('خطأ: ' + (data.error || 'فشل في تحديث حالة الحجز'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('خطأ في تحديث حالة الحجز');
    });
}

// Status filter
function filterBookingsByStatus(status) {
    const rows = document.querySelectorAll('#bookingsTableBody tr[data-status]');
    
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterBookingsBySearch(query) {
    const rows = document.querySelectorAll('#bookingsTableBody tr[data-status]');
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterBookingsByStatus(this.value);
        });
    }
    
    // Quick search
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    
    if (quickSearch) {
        quickSearch.addEventListener('input', function() {
            filterBookingsBySearch(this.value);
        });
        
        if (clearQuickSearch) {
            clearQuickSearch.addEventListener('click', function() {
                quickSearch.value = '';
                filterBookingsBySearch('');
                quickSearch.focus();
            });
        }
    }
    
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
</style>