<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<div class="row stats-cards-wrapper">
    <!-- Statistics Cards -->
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-primary">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text">إجمالي المواعيد</h4>
                        <h3 class="stats-card-value arabic-text"><?= $stats['total_appointments'] ?? 0 ?></h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-danger">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text arabic-font">في الإنتظار</h4>
                        <h3 class="stats-card-value arabic-text arabic-font"><?= $stats['booked'] ?? 0 ?></h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar2-range"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-success">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text">تم الحضور</h4>
                        <h3 class="stats-card-value arabic-text arabic-font font"><?= $stats['checked_in'] ?? 0 ?></h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-danger">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text arabic-font">مواعيد مكتملة</h4>
                        <h3 class="stats-card-value arabic-text arabic-font"><?= $stats['completed'] ?? 0 ?></h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar-heart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-danger">
                <div class="stats-card-content">
                    <div class="stats-card-header">
                        <h4 class="stats-card-title arabic-text arabic-font">لم يحضر</h4>
                        <h3 class="stats-card-value arabic-text arabic-font"><?= $stats['missed'] ?? 0 ?></h3>
                    </div>
                    <div class="stats-card-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weather & Allergy Index Card -->
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-weather">
                <div class="stats-card-content">
                    <div class="weather-card-inner">
                        <!-- Weather Section -->
                        <div class="weather-main">
                            <button class="weather-forecast-btn" id="weatherForecastBtn" title="5-Day Forecast">
                                <i class="bi bi-calendar3"></i>
                            </button>
                            <div class="weather-icon-container" id="weatherIconContainer">
                                <div class="weather-icon-loading">
                                    <div class="spinner-border spinner-border-sm text-light" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="weather-info">
                                <div class="weather-temp" id="weatherTemp">--°C</div>
                                <div class="weather-desc" id="weatherDesc">Loading...</div>
                                <div class="weather-location" id="weatherLocation">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>جاري تحديد الموقع...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Quick Actions - New iOS-style Cards -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow dashboard-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-lightning-charge me-2"></i>
                    الإجراءات السريعة
                </h6>
            </div>
            <div class="card-body">
                <div class="quick-actions-wrapper" id="quickActionsWrapper">
                    <!-- Navigation Arrows -->
                    <button class="quick-actions-nav nav-left hidden" id="qaNavLeft" aria-label="Scroll left">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="quick-actions-nav nav-right" id="qaNavRight" aria-label="Scroll right">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                            <div class="quick-actions-grid" id="quickActionsGrid">
                            <!-- Bookings Card -->
                            <div class="quick-action-card calendar-card">
                                <div class="qa-background"></div>
                                <div class="qa-logo">
                                    <i class="bi bi-calendar-check"></i>
                                    <span class="qa-logo-name">الحجوزات</span>
                                </div>
                                <div class="qa-box qa-box1" onclick="window.location.href='/secretary/bookings'">
                                    <span class="qa-icon">
                                        <i class="bi bi-calendar3"></i>
                                    </span>
                                    <span class="qa-label">عرض</span>
                                </div>
                                <div class="qa-box qa-box2" onclick="window.location.href='/secretary/bookings'">
                                    <span class="qa-icon">
                                        <i class="bi bi-calendar-plus-fill"></i>
                                    </span>
                                    <span class="qa-label">حجز</span>
                                </div>
                            </div>

                            <!-- Patients Card -->
                            <div class="quick-action-card patients-card">
                                <div class="qa-background"></div>
                                <div class="qa-logo">
                                    <i class="bi bi-people-fill"></i>
                                    <span class="qa-logo-name">المرضى</span>
                                </div>
                                <div class="qa-box qa-box1" onclick="window.location.href='/secretary/patients'">
                                    <span class="qa-icon">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </span>
                                    <span class="qa-label">عرض</span>
                                </div>
                                <div class="qa-box qa-box2" onclick="window.location.href='/secretary/patients/new'">
                                    <span class="qa-icon">
                                        <i class="bi bi-person-plus-fill"></i>
                                    </span>
                                    <span class="qa-label">إضافة</span>
                                </div>
                            </div>

                            <!-- Payments Card -->
                            <div class="quick-action-card financial-card">
                                <div class="qa-background"></div>
                                <div class="qa-logo">
                                    <i class="bi bi-credit-card"></i>
                                    <span class="qa-logo-name">المدفوعات</span>
                                </div>
                                <div class="qa-box qa-box1" onclick="window.location.href='/secretary/payments'">
                                    <span class="qa-icon">
                                        <i class="bi bi-wallet2"></i>
                                    </span>
                                    <span class="qa-label">عرض</span>
                                </div>
                                <div class="qa-box qa-box2" onclick="window.location.href='/secretary/payments'">
                                    <span class="qa-icon">
                                        <i class="bi bi-plus-circle-fill"></i>
                                    </span>
                                    <span class="qa-label">إضافة</span>
                                </div>
                            </div>

                            <!-- Profile Card (View only) -->
                            <div class="quick-action-card profile-card single-action-card">
                                <div class="qa-background"></div>
                                <div class="qa-logo">
                                    <i class="bi bi-person-circle"></i>
                                    <span class="qa-logo-name">الملف الشخصي</span>
                                </div>
                                <div class="qa-box qa-box1" onclick="window.location.href='/secretary/profile'">
                                    <span class="qa-icon">
                                        <i class="bi bi-person-vcard"></i>
                                    </span>
                                    <span class="qa-label">عرض</span>
                                </div>    
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>  
</div>

<div class="row mb-4">
    <!-- Today's Appointments -->
    <div class="col-md-8">
        <div class="card shadow dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-calendar-event me-2">&nbsp;</i>
                    مواعيد اليوم
                </h6>
                <a href="/secretary/bookings" class="btn btn-sm btn-primary arabic-text">
                    <i class="bi bi-calendar-event me-1">&nbsp;</i>عرض الكل
                </a>
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
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-credit-card me-2">&nbsp;</i>
                    المدفوعات الأخيرة
                </h6>
                <a href="/secretary/payments" class="btn btn-sm btn-primary arabic-text">
                    <i class="bi bi-credit-card me-1">&nbsp;</i>عرض الكل
                </a>
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



<script src="/app/Views/secretary/assets/js/dashboard.js?v=<?= file_exists(__DIR__ . '/assets/js/dashboard.js') ? filemtime(__DIR__ . '/assets/js/dashboard.js') : time() ?>"></script>

<script>
// Dashboard auto-refresh using API polling
(function() {
    let refreshInterval = null;
    const REFRESH_INTERVAL = 30000; // 30 seconds
    let isRefreshing = false;

    /**
     * Update statistics cards
     */
    function updateStatsCards(stats) {
        // Update total appointments
        const totalEl = document.querySelector('.stats-card-primary .stats-card-value');
        if (totalEl) {
            totalEl.textContent = stats.total_appointments || 0;
        }

        // Update booked
        const bookedEl = document.querySelector('.stats-card-danger .stats-card-value');
        if (bookedEl && bookedEl.textContent.includes('في الإنتظار') || bookedEl?.closest('.stats-card-danger')) {
            const bookedCard = Array.from(document.querySelectorAll('.stats-card-danger')).find(card => 
                card.querySelector('.stats-card-title')?.textContent.includes('في الإنتظار')
            );
            if (bookedCard) {
                const valueEl = bookedCard.querySelector('.stats-card-value');
                if (valueEl) valueEl.textContent = stats.booked || 0;
            }
        }

        // Update checked in
        const checkedInEl = document.querySelector('.stats-card-success .stats-card-value');
        if (checkedInEl) {
            checkedInEl.textContent = stats.checked_in || 0;
        }

        // Update completed
        const completedCards = Array.from(document.querySelectorAll('.stats-card-danger'));
        const completedCard = completedCards.find(card => 
            card.querySelector('.stats-card-title')?.textContent.includes('مواعيد مكتملة')
        );
        if (completedCard) {
            const valueEl = completedCard.querySelector('.stats-card-value');
            if (valueEl) valueEl.textContent = stats.completed || 0;
        }

        // Update missed
        const missedCard = completedCards.find(card => 
            card.querySelector('.stats-card-title')?.textContent.includes('لم يحضر')
        );
        if (missedCard) {
            const valueEl = missedCard.querySelector('.stats-card-value');
            if (valueEl) valueEl.textContent = stats.missed || 0;
        }
    }

    /**
     * Update today's appointments table
     */
    function updateAppointmentsTable(appointments) {
        const container = document.querySelector('#todayAppointmentsContainer') || 
                         document.querySelector('.table-responsive tbody');
        
        if (!container) return;

        if (appointments.length === 0) {
            const table = container.closest('.table-responsive');
            if (table) {
                table.outerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x display-4 text-muted"></i>
                        <p class="text-muted mt-2 arabic-text">لا توجد مواعيد مجدولة لهذا اليوم</p>
                        <a href="/secretary/bookings" class="btn btn-primary arabic-text">حجز أول موعد</a>
                    </div>
                `;
            }
            return;
        }

        // If container is tbody, update it
        if (container.tagName === 'TBODY') {
            container.innerHTML = appointments.map(apt => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock me-2 text-primary"></i>
                            ${new Date('2000-01-01 ' + apt.start_time).toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit'})}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">${apt.first_name} ${apt.last_name}</div>
                                <small class="text-muted">${apt.phone || ''}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info">${apt.doctor_name || ''}</span>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${apt.visit_type || ''}</span>
                    </td>
                    <td>
                        <span class="badge ${getStatusBadgeClass(apt.status)}">${apt.status || ''}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    onclick="viewAppointment(${apt.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            ${apt.status === 'Booked' ? `
                                <button type="button" class="btn btn-outline-success btn-sm"
                                        onclick="checkInPatient(${apt.id})">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    }

    /**
     * Update recent payments list
     */
    function updateRecentPayments(payments) {
        const container = document.querySelector('.list-group-flush');
        if (!container) return;

        if (payments.length === 0) {
            container.outerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-credit-card text-muted"></i>
                    <p class="text-muted mt-2 arabic-text">لا توجد مدفوعات حديثة</p>
                </div>
            `;
            return;
        }

        container.innerHTML = payments.map(payment => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">${payment.first_name} ${payment.last_name}</div>
                    <small class="text-muted">${payment.type || ''}</small>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success">${formatMoney(payment.amount)}</div>
                    <small class="text-muted">${formatTime(payment.created_at)}</small>
                </div>
            </div>
        `).join('');
    }

    /**
     * Helper function to get status badge class
     */
    function getStatusBadgeClass(status) {
        const classes = {
            'Booked': 'bg-warning',
            'CheckedIn': 'bg-info',
            'Completed': 'bg-success',
            'Cancelled': 'bg-secondary',
            'Missed': 'bg-danger'
        };
        return classes[status] || 'bg-secondary';
    }

    /**
     * Format money
     */
    function formatMoney(amount) {
        return new Intl.NumberFormat('ar-EG', {
            style: 'currency',
            currency: 'EGP',
            minimumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Format time
     */
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit'});
    }

    /**
     * Refresh dashboard data from API
     */
    async function refreshDashboard() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        
        try {
            const response = await fetch('/api/secretary/dashboard', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            
            if (result.ok && result.data) {
                // Update stats cards
                if (result.data.stats) {
                    updateStatsCards(result.data.stats);
                }
                
                // Update appointments table
                if (result.data.todayAppointments) {
                    updateAppointmentsTable(result.data.todayAppointments);
                }
                
                // Update recent payments
                if (result.data.recentPayments) {
                    updateRecentPayments(result.data.recentPayments);
                }
            }
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
            // Silently fail - don't show error to user
        } finally {
            isRefreshing = false;
        }
    }

    /**
     * Start auto-refresh
     */
    function startAutoRefresh() {
        // Refresh immediately on page load
        refreshDashboard();
        
        // Then refresh every 30 seconds
        refreshInterval = setInterval(refreshDashboard, REFRESH_INTERVAL);
    }

    /**
     * Stop auto-refresh
     */
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    /**
     * View appointment function
     */
    window.viewAppointment = function(appointmentId) {
        window.location.href = `/secretary/bookings/${appointmentId}`;
    };

    /**
     * Check in patient function
     */
    window.checkInPatient = async function(appointmentId) {
        if (!confirm('تأكيد حضور المريض؟')) {
            return;
        }

        try {
            const response = await fetch(`/api/appointments/${appointmentId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    status: 'CheckedIn'
                })
            });

            const data = await response.json();
            
            if (data.ok) {
                // Refresh dashboard instead of reloading page
                await refreshDashboard();
            } else {
                alert('خطأ: ' + (data.error || 'فشل في تحديث حالة الموعد'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('خطأ في تحديث حالة الموعد');
        }
    };

    // Start auto-refresh when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startAutoRefresh);
    } else {
        startAutoRefresh();
    }

    // Stop auto-refresh when page is hidden (tab switch)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', stopAutoRefresh);
})();

// Hover effect with radial gradient - glowing effect following mouse
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.stats-card');
    const wrapper = document.querySelector('.stats-cards-wrapper');

    if (wrapper && cards.length > 0) {
        wrapper.addEventListener('mousemove', function (event) {
            cards.forEach((card) => {
                const cardContent = card.querySelector('.stats-card-content');
                if (!cardContent) return;
                
                const rect = cardContent.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                // Get card type and corresponding color
                let color = 'rgba(59, 248, 251, 0.3)';
                if (card.classList.contains('stats-card-primary')) {
                    color = 'rgba(14, 165, 233, 0.4)';
                } else if (card.classList.contains('stats-card-success')) {
                    color = 'rgba(16, 185, 129, 0.4)';
                } else if (card.classList.contains('stats-card-danger')) {
                    color = 'rgba(239, 68, 68, 0.4)';
                } else if (card.classList.contains('stats-card-warning')) {
                    color = 'rgba(245, 158, 11, 0.4)';
                } else if (card.classList.contains('stats-card-info')) {
                    color = 'rgba(187, 54, 204, 0.4)';
                }

                // Apply gradient to card-content, overlay on top of background-color
                // Use multiple backgrounds: gradient on top, solid color below
                cardContent.style.background = `radial-gradient(960px circle at ${x}px ${y}px, ${color}, transparent 15%), var(--card)`;
            });
        });
        
        // Reset background when mouse leaves wrapper
        wrapper.addEventListener('mouseleave', function() {
            cards.forEach((card) => {
                const cardContent = card.querySelector('.stats-card-content');
                if (cardContent) {
                    cardContent.style.background = '';
                }
            });
        });
    }
});
</script>

<style>
    
/* Stats Cards - Center Content and Background Colors */
.stats-cards-wrapper {
    margin: 0 -0.5rem;
}

.stats-card-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 180px;
}

.stats-card {
    width: 100%;
    height: 100%;
    background: none;
    border-radius: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    transition: all 0.2s ease;
    cursor: pointer;
}

.stats-card-content {
    background-color: var(--card);
    border-radius: inherit;
    transition: all 0.25s ease;
    height: calc(100% - 2px);
    width: calc(100% - 2px);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px var(--shadow);
    border: 1px solid var(--border);
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.stats-card:hover {
    transform: scale(0.98);
}

.stats-card-header {
    padding: 1.5rem 1rem 0.5rem 1rem;
    text-align: center;
    position: relative;
    z-index: 10;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.stats-card-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--muted);
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.stats-card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text);
    margin: 0.5rem 0;
    line-height: 1.2;
}

.stats-card-icon {
    position: absolute;
    right: 1rem; /* RTL: right instead of left */
    top: 50%;
    transform: translateY(-50%);
    z-index: 3;
    opacity: 0.15;
    pointer-events: none;
}

.stats-card-icon i {
    font-size: 4rem;
    color: var(--text);
}

/* Background colors for stats cards - Light Mode */
.stats-card-primary .stats-card-content {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(14, 165, 233, 0.05) 100%), var(--card);
    border-color: rgba(14, 165, 233, 0.3);
}

.stats-card-success .stats-card-content {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%), var(--card);
    border-color: rgba(16, 185, 129, 0.3);
}

.stats-card-info .stats-card-content {
    background: linear-gradient(135deg, rgba(187, 54, 204, 0.1) 0%, rgba(187, 54, 204, 0.05) 100%), var(--card);
    border-color: rgba(187, 54, 204, 0.3);
}

.stats-card-warning .stats-card-content {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%), var(--card);
    border-color: rgba(245, 158, 11, 0.3);
}

.stats-card-danger .stats-card-content {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%), var(--card);
    border-color: rgba(239, 68, 68, 0.3);
}

/* Dark Mode Stats Cards */
.dark .stats-card-content {
    background-color: var(--card);
    border-color: var(--border);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.dark .stats-card-primary .stats-card-content {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(56, 189, 248, 0.08) 100%), var(--card);
    border-color: rgba(56, 189, 248, 0.4);
}

.dark .stats-card-success .stats-card-content {
    background: linear-gradient(135deg, rgba(74, 222, 128, 0.15) 0%, rgba(74, 222, 128, 0.08) 100%), var(--card);
    border-color: rgba(74, 222, 128, 0.4);
}

.dark .stats-card-info .stats-card-content {
    background: linear-gradient(135deg, rgba(192, 132, 252, 0.15) 0%, rgba(192, 132, 252, 0.08) 100%), var(--card);
    border-color: rgba(192, 132, 252, 0.4);
}

.dark .stats-card-warning .stats-card-content {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(251, 191, 36, 0.08) 100%), var(--card);
    border-color: rgba(251, 191, 36, 0.4);
}

.dark .stats-card-danger .stats-card-content {
    background: linear-gradient(135deg, rgba(248, 113, 113, 0.15) 0%, rgba(248, 113, 113, 0.08) 100%), var(--card);
    border-color: rgba(248, 113, 113, 0.4);
}

.dark .stats-card-title {
    color: var(--muted);
}

.dark .stats-card-value {
    color: var(--text);
}

.dark .stats-card-icon i {
    opacity: 0.2;
}

/* Table header RTL alignment */
.table thead th {
    text-align: right !important;
    direction: rtl;
}

.table thead th.arabic-text {
    text-align: right !important;
    direction: rtl;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card-wrapper {
        min-height: 160px;
    }
    
    .stats-card-value {
        font-size: 1.75rem;
    }
    
    .stats-card-title {
        font-size: 0.7rem;
    }
    
    .stats-card-header {
        padding: 1.25rem 0.75rem 0.5rem 0.75rem;
    }
}
</style>
