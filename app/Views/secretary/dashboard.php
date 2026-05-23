<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<!-- بطاقة الترحيب / النظرة العامة (B1) -->
<?php $h = (int) date('H'); $heroGreet = $h < 12 ? 'صباح الخير' : 'مساء الخير'; ?>
<div class="ds-hero mb-4">
    <h1 class="arabic-text"><?= $heroGreet ?>، <?= htmlspecialchars($_SESSION['user']['name'] ?? 'المستخدم') ?> 👋</h1>
    <p class="arabic-text">نظرة على اليوم — <strong><?= $stats['total_appointments'] ?? 0 ?></strong> موعد اليوم، منها <strong><?= $stats['completed'] ?? 0 ?></strong> مكتمل.</p>
    <div class="ds-hero-actions">
        <a href="/secretary/bookings" class="btn btn-light arabic-text"><i class="bi bi-calendar3 me-1"></i> عرض المواعيد</a>
        <button type="button" class="btn btn-outline-light arabic-text" onclick="quickActionAddBooking()"><i class="bi bi-calendar-plus me-1"></i> حجز جديد</button>
    </div>
</div>

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
            <div class="stats-card stats-card-warning">
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
            <div class="stats-card stats-card-info">
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
        const bookedCard = Array.from(document.querySelectorAll('.stats-card-warning')).find(card => 
            card.querySelector('.stats-card-title')?.textContent.includes('في الإنتظار')
        );
        if (bookedCard) {
            const valueEl = bookedCard.querySelector('.stats-card-value');
            if (valueEl) valueEl.textContent = stats.booked || 0;
        }

        // Update checked in
        const checkedInEl = document.querySelector('.stats-card-success .stats-card-value');
        if (checkedInEl) {
            checkedInEl.textContent = stats.checked_in || 0;
        }

        // Update completed
        const completedCard = Array.from(document.querySelectorAll('.stats-card-info')).find(card => 
            card.querySelector('.stats-card-title')?.textContent.includes('مواعيد مكتملة')
        );
        if (completedCard) {
            const valueEl = completedCard.querySelector('.stats-card-value');
            if (valueEl) valueEl.textContent = stats.completed || 0;
        }

        // Update missed
        const missedCard = Array.from(document.querySelectorAll('.stats-card-danger')).find(card => 
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

// Quick Actions Functions
function quickActionAddBooking() {
    window.location.href = '/secretary/bookings?openModal=addBooking';
}

function quickActionAddPatient() {
    window.location.href = '/secretary/patients?openModal=addPatient';
}

function quickActionAddBalance() {
    window.location.href = '/secretary/payments?openModal=dailyBalance';
}

function quickActionAddExpense() {
    window.location.href = '/secretary/payments?openModal=expense';
}

// Quick Actions Horizontal Scroll with RTL support
function initQuickActionsScroll() {
    const wrapper = document.getElementById('quickActionsWrapper');
    const grid = document.getElementById('quickActionsGrid');
    const navLeft = document.getElementById('qaNavLeft');
    const navRight = document.getElementById('qaNavRight');

    if (!wrapper || !grid || !navLeft || !navRight) return;

    const scrollAmount = 160; // Card width + gap
    const isRTL = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl' || 
                  getComputedStyle(document.documentElement).direction === 'rtl';

    // Update navigation arrows and fade indicators based on scroll position
    function updateScrollState() {
        const scrollLeft = grid.scrollLeft;
        const maxScroll = grid.scrollWidth - grid.clientWidth;

        // Since grid has direction: ltr, scrollLeft works normally
        // In RTL layout, left arrow should scroll right (show more content on the right)
        // and right arrow should scroll left (show more content on the left)
        
        if (isRTL) {
            // In RTL: left arrow shows when there's content to scroll right (positive scrollLeft)
            if (scrollLeft <= 5) {
                navLeft.classList.add('hidden');
                wrapper.classList.remove('show-left-fade');
            } else {
                navLeft.classList.remove('hidden');
                wrapper.classList.add('show-left-fade');
            }

            // Right arrow shows when there's content to scroll left (negative scrollLeft or not at max)
            if (scrollLeft >= maxScroll - 5) {
                navRight.classList.add('hidden');
                wrapper.classList.remove('show-right-fade');
            } else {
                navRight.classList.remove('hidden');
                wrapper.classList.add('show-right-fade');
            }
        } else {
            // LTR mode - standard behavior
            if (scrollLeft <= 5) {
                navLeft.classList.add('hidden');
                wrapper.classList.remove('show-left-fade');
            } else {
                navLeft.classList.remove('hidden');
                wrapper.classList.add('show-left-fade');
            }

            if (scrollLeft >= maxScroll - 5) {
                navRight.classList.add('hidden');
                wrapper.classList.remove('show-right-fade');
            } else {
                navRight.classList.remove('hidden');
                wrapper.classList.add('show-right-fade');
            }
        }
    }

    // Scroll handlers - arrows work the same way since grid is LTR
    // But visually, in RTL, left arrow is on the right side and scrolls right
    navLeft.addEventListener('click', function() {
        grid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });

    navRight.addEventListener('click', function() {
        grid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });

    // Touch support for mobile devices
    let touchStartX = 0;
    let touchEndX = 0;
    let touchStartY = 0;
    let touchEndY = 0;
    let isScrolling = false;

    grid.addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isScrolling = false;
    }, { passive: true });

    grid.addEventListener('touchmove', function(e) {
        if (!isScrolling) {
            const deltaX = Math.abs(e.touches[0].clientX - touchStartX);
            const deltaY = Math.abs(e.touches[0].clientY - touchStartY);
            // Only consider it scrolling if horizontal movement is greater than vertical
            if (deltaX > deltaY) {
                isScrolling = true;
            }
        }
    }, { passive: true });

    grid.addEventListener('touchend', function(e) {
        if (!isScrolling) return;
        
        touchEndX = e.changedTouches[0].clientX;
        touchEndY = e.changedTouches[0].clientY;
        const diffX = touchStartX - touchEndX;
        const diffY = Math.abs(touchStartY - touchEndY);
        const threshold = 50; // Minimum swipe distance

        // Only process if horizontal swipe is significant and greater than vertical
        if (Math.abs(diffX) > threshold && Math.abs(diffX) > diffY) {
            if (isRTL) {
                // In RTL: swipe right (positive diffX) scrolls left, swipe left (negative diffX) scrolls right
                if (diffX > 0) {
                    grid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                } else {
                    grid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                }
            } else {
                // In LTR: swipe left (negative diffX) scrolls right, swipe right (positive diffX) scrolls left
                if (diffX < 0) {
                    grid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                } else {
                    grid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                }
            }
        }
        isScrolling = false;
    }, { passive: true });

    // Mouse wheel support
    grid.addEventListener('wheel', function(e) {
        // Only prevent default if scrolling horizontally
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
            e.preventDefault();
            grid.scrollBy({ left: e.deltaX, behavior: 'smooth' });
        }
    }, { passive: false });

    // Listen for scroll events
    grid.addEventListener('scroll', updateScrollState);

    // Initial state check
    updateScrollState();

    // Re-check on window resize
    window.addEventListener('resize', updateScrollState);
}

// Initialize quick actions scroll when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initQuickActionsScroll();
});

// Hover effect with radial gradient - glowing effect following mouse
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals with proper backdrop configuration
    const initializeModals = () => {
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(modalEl => {
            // Skip if already initialized
            if (bootstrap.Modal.getInstance(modalEl)) return;
            
            // Get backdrop setting from data attribute or default to true
            const backdropSetting = modalEl.dataset.bsBackdrop !== undefined 
                ? (modalEl.dataset.bsBackdrop === 'static' ? 'static' : modalEl.dataset.bsBackdrop === 'false' ? false : true)
                : true;
            
            // Get keyboard setting
            const keyboardSetting = modalEl.dataset.bsKeyboard !== undefined 
                ? modalEl.dataset.bsKeyboard !== 'false'
                : true;
            
            // Initialize modal with proper config
            new bootstrap.Modal(modalEl, {
                backdrop: backdropSetting,
                keyboard: keyboardSetting,
                focus: true
            });
        });
    };
    
    // Initialize modals immediately
    initializeModals();
    
    // Re-initialize modals if new ones are added dynamically
    const observer = new MutationObserver(() => {
        initializeModals();
        // Re-initialize draggable after modals are initialized
        setTimeout(initializeDraggableModals, 100);
    });
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Make modals draggable
    function initializeDraggableModals() {
    /* Drag/center/animation unified in layouts/modal-kit.js. No-op. */
    return;
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            // Skip alertModal - it has its own draggable implementation
            if (modal.id === 'alertModal') {
                return;
            }
            const modalDialog = modal.querySelector('.modal-dialog');
            if (!modalDialog) return;
            
            // Skip if already initialized
            if (modalDialog.dataset.draggableInitialized === 'true') return;
            modalDialog.dataset.draggableInitialized = 'true';
            
            let isDragging = false;
            let currentX;
            let currentY;
            let initialX;
            let initialY;
            let xOffset = 0;
            let yOffset = 0;
            
            // Make modal header the drag handle
            const modalHeader = modal.querySelector('.modal-header');
            if (!modalHeader) return;
            
            modalHeader.style.cursor = 'move';
            
            // Remove existing listeners to avoid duplicates
            const newHeader = modalHeader.cloneNode(true);
            modalHeader.parentNode.replaceChild(newHeader, modalHeader);
            const freshHeader = modal.querySelector('.modal-header');
            
            freshHeader.addEventListener('mousedown', dragStart);
            
            function dragStart(e) {
                // Don't drag if clicking on buttons or inputs
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                    return;
                }
                
                // Only start dragging if clicking on header (not on title text)
                if (e.target === freshHeader || (freshHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
                    // Get current transform values
                    const transform = modalDialog.style.transform;
                    if (transform) {
                        const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                        if (match) {
                            xOffset = parseFloat(match[1]) || 0;
                            yOffset = parseFloat(match[2]) || 0;
                        }
                    }
                    
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;
                    
                    // Store initial mouse position to detect if it's a drag or click
                    const startX = e.clientX;
                    const startY = e.clientY;
                    
                    // Set a flag to track if mouse moved
                    let hasMoved = false;
                    
                    function checkMove(moveEvent) {
                        const deltaX = Math.abs(moveEvent.clientX - startX);
                        const deltaY = Math.abs(moveEvent.clientY - startY);
                        if (deltaX > 5 || deltaY > 5) {
                            hasMoved = true;
                            isDragging = true;
                            modalDialog.style.transition = 'none';
                            moveEvent.preventDefault();
                            moveEvent.stopPropagation();
                        }
                    }
                    
                    function handleMove(moveEvent) {
                        if (hasMoved) {
                            drag(moveEvent);
                        } else {
                            checkMove(moveEvent);
                        }
                    }
                    
                    function handleEnd(endEvent) {
                        if (!hasMoved) {
                            // It was just a click, allow normal behavior
                            document.removeEventListener('mousemove', handleMove);
                            document.removeEventListener('mouseup', handleEnd);
                            return;
                        }
                        dragEnd(endEvent);
                        document.removeEventListener('mousemove', handleMove);
                        document.removeEventListener('mouseup', handleEnd);
                    }
                    
                    document.addEventListener('mousemove', handleMove);
                    document.addEventListener('mouseup', handleEnd);
                }
            }
            
            function drag(e) {
                if (isDragging) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent modal from closing
                    currentX = e.clientX - initialX;
                    currentY = e.clientY - initialY;
                    
                    xOffset = currentX;
                    yOffset = currentY;
                    
                    setTranslate(currentX, currentY, modalDialog);
                }
            }
            
            function dragEnd(e) {
                initialX = currentX;
                initialY = currentY;
                isDragging = false;
                modalDialog.style.transition = '';
            }
            
            function setTranslate(xPos, yPos, el) {
                // Get viewport dimensions
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                
                // Get modal dimensions
                const modalRect = el.getBoundingClientRect();
                const modalWidth = modalRect.width;
                const modalHeight = modalRect.height;
                
                // Get the original position (center of viewport)
                const originalLeft = (viewportWidth - modalWidth) / 2;
                const originalTop = 50; // Keep at least 50px from top
                
                // Calculate boundaries relative to original position
                // Allow movement within viewport bounds
                const minX = -(originalLeft - 20); // Allow 20px from left edge
                const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
                const minY = -(originalTop - 20); // Allow 20px from top
                const maxY = viewportHeight - modalHeight - originalTop - 20; // Allow 20px from bottom
                
                // Constrain movement
                const constrainedX = Math.max(minX, Math.min(maxX, xPos));
                const constrainedY = Math.max(minY, Math.min(maxY, yPos));
                
                el.style.transform = `translate(${constrainedX}px, ${constrainedY}px)`;
            }
            
            // Reset position when modal is hidden
            modal.addEventListener('hidden.bs.modal', function() {
                xOffset = 0;
                yOffset = 0;
                modalDialog.style.transform = '';
                modalDialog.dataset.draggableInitialized = 'false';
            });
        });
    }
    
    // Initialize draggable modals after a short delay to ensure modals are ready
    setTimeout(initializeDraggableModals, 200);
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
                } else if (card.classList.contains('stats-card-weather')) {
                    // Skip weather card - don't apply hover effect
                    return;
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


/* ============================================
       Action Buttons in Table - Ensure proper borders and styles
       ============================================ */
    .btn-group .btn {
        border: 1px solid var(--border) !important;
        border-radius: 10px !important;
        margin: 5px !important;
        padding: 0.375rem 0.75rem !important;
        transition: all 0.2s ease !important;
    }
    
    .btn-group .btn-outline-primary {
        color: white !important;
        background: var(--accent) !important;
        border-radius: 10px !important;
        border: 1px solid var(--accent) !important;
    }
    
    .btn-group .btn-outline-primary:hover {
        background: var(--accent) !important;
        color: white !important;
    }

    .btn-group .btn-outline-warning {
        color: white !important;
        background: #ffc107 !important;
        border: 1px solid #ffc107 !important;
        border-radius: 10px !important;
    }
    
    .btn-group .btn-outline-warning:hover {
        background: #ffc107 !important;
        color: white !important;
    }

    .btn-group .btn-outline-info {
        color: white !important;
        background: #4F46E5 !important;
        border: 1px solid #4F46E5 !important;
        border-radius: 10px !important;
    }
    
    .btn-group .btn-outline-info:hover {
        background: #4F46E5 !important;
        color: white !important;
    }
    
    .btn-group .btn-outline-success {
        color: white !important;
        background: var(--success) !important;
        border: 1px solid var(--success) !important;
        border-radius: 10px !important;
    }
    
    .btn-group .btn-outline-success:hover {
        background: var(--success) !important;
        color: white !important;
    }
    
    .btn-group .btn-outline-danger {
        color: white !important;
        background: var(--danger) !important;
        border: 1px solid var(--danger) !important;
        border-radius: 10px !important;
    }
    
    .btn-group .btn-outline-danger:hover {
        background: var(--danger) !important;
        color: white !important;
    }

/* Quick Actions Wrapper - RTL Support */
.quick-actions-wrapper {
    position: relative;
    width: 100%;
    padding: 0.5rem 0;
}

/* Quick Actions Grid - Center alignment with RTL support */
.quick-actions-grid {
    display: flex !important;
    flex-wrap: nowrap !important;
    gap: 1rem !important;
    justify-content: center !important;
    align-items: center !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    padding: 0.5rem 0.25rem 1rem 0.25rem !important;
    scroll-behavior: smooth !important;
    -webkit-overflow-scrolling: touch !important;
    scroll-snap-type: x mandatory !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
    direction: ltr; /* Force LTR for scroll behavior */
}

.quick-actions-grid::-webkit-scrollbar {
    display: none !important;
}

/* Fade indicators on edges - RTL support */
.quick-actions-wrapper::before,
.quick-actions-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 40px;
    pointer-events: none;
    z-index: 10;
    opacity: 0;
    transition: opacity 0.3s ease;
}

[dir="rtl"] .quick-actions-wrapper::before {
    right: 0;
    left: auto;
    background: linear-gradient(to left, var(--card) 0%, transparent 100%);
}

[dir="rtl"] .quick-actions-wrapper::after {
    left: 0;
    right: auto;
    background: linear-gradient(to right, var(--card) 0%, transparent 100%);
}

[dir="ltr"] .quick-actions-wrapper::before {
    left: 0;
    right: auto;
    background: linear-gradient(to right, var(--card) 0%, transparent 100%);
}

[dir="ltr"] .quick-actions-wrapper::after {
    right: 0;
    left: auto;
    background: linear-gradient(to left, var(--card) 0%, transparent 100%);
}

.quick-actions-wrapper.show-left-fade::before {
    opacity: 1;
}

.quick-actions-wrapper.show-right-fade::after {
    opacity: 1;
}

/* Navigation arrows - RTL support */
.quick-actions-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 15;
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.quick-actions-nav:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
    transform: translateY(-50%) scale(1.1);
}

[dir="rtl"] .quick-actions-nav.nav-left {
    right: -10px;
    left: auto;
}

[dir="rtl"] .quick-actions-nav.nav-right {
    left: -10px;
    right: auto;
}

[dir="ltr"] .quick-actions-nav.nav-left {
    left: -10px;
    right: auto;
}

[dir="ltr"] .quick-actions-nav.nav-right {
    right: -10px;
    left: auto;
}

.quick-actions-wrapper:hover .quick-actions-nav {
    opacity: 1;
}

.quick-actions-nav.hidden {
    opacity: 0 !important;
    pointer-events: none;
}

.dark .quick-actions-nav {
    background: var(--card);
    border-color: var(--border);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

.dark .quick-actions-nav:hover {
    background: var(--accent);
    color: white;
}

/* Hide arrows on touch devices */
@media (hover: none) and (pointer: coarse) {
    .quick-actions-nav {
        display: none;
    }
}

/* Stats Card Weather - Proper coloring */
.stats-card-weather .stats-card-content {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(14, 165, 233, 0.05) 100%), var(--card) !important;
    border-color: rgba(14, 165, 233, 0.3) !important;
}

.dark .stats-card-weather .stats-card-content {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(56, 189, 248, 0.08) 100%), var(--card) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
}

/* Modal z-index and centering */
.modal {
    z-index: 1000002 !important;
    align-items: center;
    justify-content: center;
    padding: 1rem !important;
}

.modal-backdrop {
    z-index: 1000000 !important;
}

.modal-dialog {
    z-index: 1000002 !important;
    margin: 0 auto;
    max-width: 500px;
}

.modal-dialog.modal-lg {
    max-width: 800px;
}

.modal-dialog.modal-xl {
    max-width: 1140px;
}

.modal-dialog.modal-sm {
    max-width: 300px;
}

.weather-temp{
    margin-top: 2rem !important;
}

/* Expenses Card Styles */
.expenses-card .qa-background {
    background: radial-gradient(circle at 100% 107%, #dc2626 0%, #ef4444 30%, #f97316 60%, #fb923c 100%);
}

.expenses-card .qa-box1::before {
    background: radial-gradient(circle at 30% 107%, #fee2e2 0%, #ef4444 60%, #dc2626 100%);
}

.expenses-card .qa-box2::before {
    background: radial-gradient(circle at 30% 107%, #ffedd5 0%, #f97316 60%, #ea580c 100%);
}

/* Search Results Styles */
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    margin-top: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.search-result-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: var(--accent);
    color: white;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item strong {
    display: block;
    margin-bottom: 0.25rem;
}

.search-result-item small {
    color: var(--muted);
    font-size: 0.875rem;
}

.search-result-item:hover small {
    color: rgba(255, 255, 255, 0.8);
}
</style>
