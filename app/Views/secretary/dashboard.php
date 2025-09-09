<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0 arabic-text">نظرة عامة على اليوم</h4>
            <div class="d-flex gap-2">
                <a href="/secretary/bookings" class="btn btn-primary">
                    <i class="bi bi-calendar-plus me-2"></i>
                    حجز جديد
                </a>
                <a href="/secretary/patients/new" class="btn btn-success">
                    <i class="bi bi-person-plus me-2"></i>
                    مريض جديد
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number"><?= $stats['total_appointments'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">إجمالي المواعيد</p>
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
                        <h3 class="stat-number"><?= $stats['checked_in'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">تم الحضور</p>
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
                        <h3 class="stat-number"><?= $stats['completed'] ?? 0 ?></h3>
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
                        <h3 class="stat-number"><?= $stats['booked'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">في الانتظار</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Appointments -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-calendar-day me-2"></i>
                    مواعيد اليوم
                </h5>
                <a href="/secretary/bookings" class="btn btn-sm btn-outline-primary arabic-text">عرض الكل</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayAppointments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x display-4 text-muted"></i>
                        <p class="text-muted mt-2 arabic-text">لا توجد مواعيد مجدولة لهذا اليوم</p>
                        <a href="/secretary/bookings" class="btn btn-primary arabic-text">حجز أول موعد</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="arabic-text">الوقت</th>
                                    <th class="arabic-text">المريض</th>
                                    <th class="arabic-text">الطبيب</th>
                                    <th class="arabic-text">النوع</th>
                                    <th class="arabic-text">الحالة</th>
                                    <th class="arabic-text">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayAppointments as $appointment): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-clock me-2 text-primary"></i>
                                                <?= date('H:i', strtotime($appointment['start_time'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold"><?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?></div>
                                                    <small class="text-muted"><?= $appointment['phone'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $appointment['doctor_name'] ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $this->getVisitTypeBadgeClass($appointment['visit_type']) ?>">
                                                <?= $appointment['visit_type'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $this->getStatusBadgeClass($appointment['status']) ?>">
                                                <?= $appointment['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        onclick="viewAppointment(<?= $appointment['id'] ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php if ($appointment['status'] === 'Booked'): ?>
                                                    <button type="button" class="btn btn-outline-success btn-sm"
                                                            onclick="checkInPatient(<?= $appointment['id'] ?>)">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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
    
    <!-- Recent Payments -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-credit-card me-2"></i>
                    المدفوعات الأخيرة
                </h5>
                <a href="/secretary/payments" class="btn btn-sm btn-outline-primary arabic-text">عرض الكل</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentPayments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-credit-card text-muted"></i>
                        <p class="text-muted mt-2 arabic-text">لا توجد مدفوعات حديثة</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($recentPayments, 0, 5) as $payment): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?= $payment['first_name'] . ' ' . $payment['last_name'] ?></div>
                                    <small class="text-muted"><?= $payment['type'] ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success"><?= $this->formatMoney($payment['amount']) ?></div>
                                    <small class="text-muted"><?= $this->formatTime($payment['created_at']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-lightning me-2"></i>
                    الإجراءات السريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="/secretary/bookings" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-calendar-plus display-6 mb-3"></i>
                            <span class="fw-semibold arabic-text">حجز جديد</span>
                            <small class="text-muted arabic-text">جدولة موعد</small>
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="/secretary/patients/new" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-person-plus display-6 mb-3"></i>
                            <span class="fw-semibold arabic-text">مريض جديد</span>
                            <small class="text-muted arabic-text">تسجيل مريض</small>
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="/secretary/payments" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-credit-card display-6 mb-3"></i>
                            <span class="fw-semibold arabic-text">تسجيل دفعة</span>
                            <small class="text-muted arabic-text">معالجة دفعة</small>
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="/secretary/patients" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-search display-6 mb-3"></i>
                            <span class="fw-semibold arabic-text">البحث عن مريض</span>
                            <small class="text-muted arabic-text">البحث في السجلات</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewAppointment(appointmentId) {
    // Redirect to appointment details or open modal
    window.location.href = `/secretary/appointments/${appointmentId}`;
}

function checkInPatient(appointmentId) {
    if (confirm('تأكيد حضور المريض؟')) {
        // Update appointment status via API
        fetch(`/api/appointments/${appointmentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: 'CheckedIn'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                location.reload();
            } else {
                alert('خطأ: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطأ في تحديث حالة الموعد');
        });
    }
}

// Auto-refresh dashboard every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>

<style>
    
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

.list-group-item {
    border: none;
    border-bottom: 1px solid var(--border);
    background: transparent;
}

.list-group-item:last-child {
    border-bottom: none;
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-right: 1px solid var(--border);
}

.quick-action-btn {
    transition: all 0.2s ease;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

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
