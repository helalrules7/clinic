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

<?php
$trends = $trends ?? ['total' => [], 'booked' => [], 'checked_in' => [], 'completed' => [], 'missed' => []];
?>
<script type="application/json" id="secDashboardTrends"><?= json_encode($trends, JSON_UNESCAPED_UNICODE) ?></script>

<div class="row stats-cards-wrapper sec-dashboard-stats">
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-primary">
                <div class="mini-stat-icon"><i class="bi bi-calendar3-fill"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secStatTotal"><?= (int)($stats['total_appointments'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">إجمالي المواعيد اليوم</span>
                </div>
                <div class="mini-stat-chart" id="chartSecTotal" data-trend-key="total"></div>
                <div class="mini-stat-trend trend-neutral" id="trendSecTotal">
                    <i class="bi bi-calendar-day"></i><span class="arabic-text">اليوم</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-warning">
                <div class="mini-stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secStatBooked"><?= (int)($stats['booked'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">في الانتظار</span>
                </div>
                <div class="mini-stat-chart" id="chartSecBooked" data-trend-key="booked"></div>
                <div class="mini-stat-trend trend-neutral" id="trendSecBooked">
                    <i class="bi bi-graph-up-arrow"></i><span>--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-info">
                <div class="mini-stat-icon"><i class="bi bi-person-check-fill"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secStatCheckedIn"><?= (int)($stats['checked_in'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">تم الحضور</span>
                </div>
                <div class="mini-stat-chart" id="chartSecCheckedIn" data-trend-key="checked_in"></div>
                <div class="mini-stat-trend trend-neutral" id="trendSecCheckedIn">
                    <i class="bi bi-graph-up-arrow"></i><span>--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-success">
                <div class="mini-stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secStatCompleted"><?= (int)($stats['completed'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">مواعيد مكتملة</span>
                </div>
                <div class="mini-stat-chart" id="chartSecCompleted" data-trend-key="completed"></div>
                <div class="mini-stat-trend trend-neutral" id="trendSecCompleted">
                    <i class="bi bi-graph-up-arrow"></i><span>--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-danger">
                <div class="mini-stat-icon"><i class="bi bi-x-circle-fill"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secStatMissed"><?= (int)($stats['missed'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">لم يحضر</span>
                </div>
                <div class="mini-stat-chart" id="chartSecMissed" data-trend-key="missed"></div>
                <div class="mini-stat-trend trend-down" id="trendSecMissed">
                    <i class="bi bi-graph-down-arrow"></i><span>--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- بطاقة الطقس — نسخة الطبيب الزجاجية -->
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="stats-card stats-card-weather">
                <div class="stats-card-content">
                    <div class="weather-widget weather-widget--day" id="weatherWidget"
                         role="button" tabindex="0" title="تفاصيل الطقس والتوقعات"
                         aria-label="تفاصيل الطقس والتوقعات">
                        <div class="weather-widget-top">
                            <div class="weather-widget-body">
                                <div class="weather-widget-primary">
                                    <div class="weather-desc arabic-text" id="weatherDesc">جاري التحميل…</div>
                                    <div class="weather-temp" id="weatherTemp">--<span class="weather-deg">°</span></div>
                                </div>
                                <div class="weather-widget-meta">
                                    <div class="weather-date arabic-text" id="weatherDate">—</div>
                                    <div class="weather-location arabic-text" id="weatherLocation">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span>كفر الشيخ</span>
                                    </div>
                                </div>
                            </div>
                            <div class="weather-icon-container" id="weatherIconContainer">
                                <div class="weather-icon-loading">
                                    <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                                </div>
                            </div>
                        </div>
                        <div class="health-indices">
                            <div class="health-index pollen-index">
                                <div class="index-icon"><i class="bi bi-flower1"></i></div>
                                <div class="index-info">
                                    <span class="index-label arabic-text">مؤشر حبوب اللقاح</span>
                                    <div class="index-bar"><div class="index-fill" id="pollenIndexFill" style="width:0%"></div></div>
                                    <span class="index-value" id="pollenIndexValue">--</span>
                                </div>
                            </div>
                            <div class="health-index dry-eye-index">
                                <div class="index-icon"><i class="bi bi-eye"></i></div>
                                <div class="index-info">
                                    <span class="index-label arabic-text">جفاف العين</span>
                                    <div class="index-bar"><div class="index-fill" id="dryEyeIndexFill" style="width:0%"></div></div>
                                    <span class="index-value" id="dryEyeIndexValue">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$secDailyTips = [
    'سجّل حضور المريض فور وصوله — يظهر للطبيب مباشرة في قائمة الانتظار.',
    'استخدم البحث السريع (Ctrl+K) للوصول لأي مريض أو موعد خلال ثوانٍ.',
    'راجع مواعيد الغد قبل نهاية اليوم لتجنب التداخل أو الفجوات في الجدول.',
    'أكّد رقم الهاتف عند الحجز — يسهّل التذكير وتقليل حالات «لم يحضر».',
    'بعد كل دفعة، تأكد من ظهورها في «المدفوعات الأخيرة» قبل إغلاق النافذة.',
    'للمريض الجديد: أدخل الاسم والهوية بدقة — يوفّر وقت الطبيب لاحقاً.',
    'عند إعادة الجدولة، اختر سبباً واضحاً في الملاحظات لسهولة المتابعة.',
    'راجع بطاقة «حالة اليوم» كل ساعة لمعرفة من ما زال في الانتظار.',
    'استخدم اختصار الحجز السريع من لوحة التحكم بدل التنقل بين الصفحات.',
    'إذا تأخر المريض أكثر من ١٥ دقيقة، تواصل هاتفياً قبل تغيير الحالة.',
    'احفظ بيانات التأمين في ملف المريض مرة واحدة — تُستخدم في كل زيارة.',
    'قبل إغلاق العيادة: تأكد أن كل موعد «مكتمل» أو «ملغى» وليس «جاري».',
    'نظّم المواعيد حسب الطبيب عند العيادات متعددة الأطباء لتقليل الانتظار.',
    'أضف ملاحظة قصيرة للمواعيد الخاصة (أطفال، كبار سن) لتنبيه الاستقبال.',
    'راجع «إيرادات اليوم» مع صندوق التحصيل — التطابق يمنع أخطاء الإغلاق.',
    'للمواعيد المتكررة، استخدم نفس نوع الزيارة لتقارير أوضح لاحقاً.',
    'عند ازدحام الانتظار، أبلغ الطبيب بعدد المرضى الحاضرين باختصار.',
    'تحقق من رصيد المريض قبل الموعد إن كانت الزيارة تتطلب دفعاً مسبقاً.',
    'استخدم فلتر التاريخ في الحجوزات لمراجعة أسبوع كامل دفعة واحدة.',
    'نصيحة: ابتسم واذكر اسم المريض — تجربة بسيطة ترفع رضا الزيارة.',
    'حدّث حالة الموعد فور انتهاء الكشف — يحافظ على إحصائيات اليوم دقيقة.',
    'احتفظ بنسخة من إيصال الدفع عند طلب المريض — متاح من صفحة المدفوعات.',
    'راجع الطقس صباحاً — أيام الجفاف قد تزيد زيارات التهيج؛ جهّز الاستقبال.',
    'للحالات العاجلة، أضف موعداً بملاحظة «عاجل» ليظهر أعلى القائمة.',
    'قبل العطلات: راجع المواعيد المحجوزة وأرسل تذكيرات للمرضى المعروفين.',
    'استخدم «مريض جديد» من الإجراءات السريعة — يفتح النموذج مباشرة.',
    'إذا نسيت كلمة مرور حسابك، تواصل مع الإدارة — لا تشارك بيانات الدخول.',
    'نظّف قائمة الانتظار مساءً: حوّل «في الانتظار» المتأخرين إلى «لم يحضر».',
    'سجّل وقت الوصول الفعلي — يساعد في تحليل أوقات الذروة لاحقاً.',
    'عند شكوى مريض من الانتظار، سجّل الملاحظة في ملفه للمتابعة.',
    'راجع المدفوعات المعلقة أسبوعياً — تسريع التحصيل يحسّن التدفق النقدي.',
];
$secTipIndex = (int) date('z') % max(1, count($secDailyTips));
$secTipToday = $secDailyTips[$secTipIndex];
?>
<!-- نصيحة اليوم — بديل خفيف لشريط الأخبار -->
<div class="sec-tip-banner sec-tip-banner--ready mb-3" id="secTipOfDay" role="note" aria-live="polite">
    <div class="sec-tip-glow" aria-hidden="true"></div>
    <div class="sec-tip-icon" aria-hidden="true"><i class="bi bi-lightbulb-fill"></i></div>
    <div class="sec-tip-body">
        <span class="sec-tip-label arabic-text">نصيحة اليوم</span>
        <p class="sec-tip-text arabic-text" id="secTipText"><?= htmlspecialchars($secTipToday, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-calendar-event me-2"></i>مواعيد اليوم
                </h6>
                <a href="/secretary/bookings" class="btn btn-sm btn-primary arabic-text">
                    <i class="bi bi-calendar3 me-1"></i>عرض الكل
                </a>
            </div>
            <div class="card-body" id="secTodayApptContainer">
                <?php if (empty($todayAppointments)): ?>
                    <div class="appt-empty arabic-text text-center py-4">
                        <i class="bi bi-calendar-x display-6 text-muted"></i>
                        <p class="text-muted mt-2">لا توجد مواعيد اليوم</p>
                        <a href="/secretary/bookings" class="btn btn-primary btn-sm">حجز موعد</a>
                    </div>
                <?php endif; ?>
            </div>
            <nav class="px-3 pb-3" id="secTodayApptPagination" style="display:none" aria-label="ترقيم مواعيد اليوم">
                <ul class="pagination pagination-sm justify-content-center mb-0"></ul>
            </nav>
        </div>
    </div>

    <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
        <div class="dash-mini-grid h-100">
            <div class="dash-mini-card" id="secDashStatusCard">
                <div class="dash-mini-head arabic-text"><i class="bi bi-pie-chart-fill"></i><span>حالة اليوم</span></div>
                <div class="dash-mini-body" id="secDashStatusBody">
                    <div class="dash-mini-spinner"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>
            </div>
            <div class="dash-mini-card" id="secDashRevenueCard">
                <div class="dash-mini-head arabic-text"><i class="bi bi-cash-coin"></i><span>إيرادات اليوم</span></div>
                <div class="dash-mini-body" id="secDashRevenueBody">
                    <div class="dash-mini-spinner"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>
            </div>
            <div class="dash-mini-card" id="secDashQuickCard">
                <div class="dash-mini-head arabic-text"><i class="bi bi-lightning-charge-fill"></i><span>إجراءات سريعة</span></div>
                <div class="dash-mini-body dash-quick-actions">
                    <a href="/secretary/bookings?openModal=addBooking" class="dash-quick-tile dqt-indigo arabic-text"><i class="bi bi-calendar-plus"></i><span>حجز جديد</span></a>
                    <a href="/secretary/patients?openModal=addPatient" class="dash-quick-tile dqt-teal arabic-text"><i class="bi bi-person-plus"></i><span>مريض جديد</span></a>
                    <a href="/secretary/payments" class="dash-quick-tile dqt-violet arabic-text"><i class="bi bi-credit-card"></i><span>المدفوعات</span></a>
                    <a href="/secretary/bookings" class="dash-quick-tile dqt-amber arabic-text"><i class="bi bi-calendar3"></i><span>التقويم</span></a>
                </div>
            </div>
            <div class="dash-mini-card dash-mini-clickable" onclick="window.location.href='/secretary/payments'" role="button" tabindex="0">
                <div class="dash-mini-head arabic-text"><i class="bi bi-wallet2"></i><span>ملخص مالي</span></div>
                <div class="dash-mini-body arabic-text" style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);font-size:.85rem;">
                    <span>عرض التحصيل والرصيد اليومي ←</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Recent Payments -->
    <div class="col-md-12">
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
            <div class="card-body p-0" id="secRecentPaymentsBody">
                <?php if (empty($recentPayments)): ?>
                    <div class="text-center py-4" id="secRecentPaymentsEmpty">
                        <i class="bi bi-credit-card text-muted"></i>
                        <p class="text-muted mt-2 arabic-text">لا توجد مدفوعات حديثة</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush" id="secRecentPaymentsList">
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
<script src="/app/Views/secretary/assets/js/sec-dashboard-widgets.js?v=<?= file_exists(__DIR__ . '/assets/js/sec-dashboard-widgets.js') ? filemtime(__DIR__ . '/assets/js/sec-dashboard-widgets.js') : time() ?>"></script>
<script type="application/json" id="secTodayApptsInitial"><?= json_encode($todayAppointments ?? [], JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="secDashboardStatsInitial"><?= json_encode([
    'total_appointments' => (int)($stats['total_appointments'] ?? 0),
    'booked' => (int)($stats['booked'] ?? 0),
    'checked_in' => (int)($stats['checked_in'] ?? 0),
    'completed' => (int)($stats['completed'] ?? 0),
    'missed' => (int)($stats['missed'] ?? 0),
], JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="secRevenueInitial"><?= json_encode($revenue ?? null, JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="secDailyTipsData"><?= json_encode($secDailyTips, JSON_UNESCAPED_UNICODE) ?></script>

<script>
// Dashboard auto-refresh using API polling
(function() {
    let refreshInterval = null;
    const REFRESH_INTERVAL = 30000; // 30 seconds
    let isRefreshing = false;

    /**
     * Update statistics cards
     */
    function updateStatsCards(stats, trends) {
        var fields = [
            ['secStatTotal', 'total_appointments'],
            ['secStatBooked', 'booked'],
            ['secStatCheckedIn', 'checked_in'],
            ['secStatCompleted', 'completed'],
            ['secStatMissed', 'missed']
        ];
        fields.forEach(function (pair) {
            var el = document.getElementById(pair[0]);
            if (el) el.textContent = stats[pair[1]] || 0;
        });
        if (typeof window.secRefreshDashboardCharts === 'function') {
            window.secRefreshDashboardCharts(trends);
        }
    }

    /**
     * Update today's appointments table
     */
    function updateAppointmentsTable(appointments) {
        if (window.secDashboardWidgets && typeof window.secDashboardWidgets.renderTodayAppointments === 'function') {
            window.secDashboardWidgets.renderTodayAppointments(appointments);
        }
    }

    /**
     * Update recent payments list
     */
    function updateRecentPayments(payments) {
        const body = document.getElementById('secRecentPaymentsBody');
        if (!body) return;

        if (!payments || payments.length === 0) {
            body.innerHTML = `
                <div class="text-center py-4" id="secRecentPaymentsEmpty">
                    <i class="bi bi-credit-card text-muted"></i>
                    <p class="text-muted mt-2 arabic-text">لا توجد مدفوعات حديثة</p>
                </div>`;
            return;
        }

        body.innerHTML = `<div class="list-group list-group-flush" id="secRecentPaymentsList">${
            payments.map(payment => `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">${payment.first_name} ${payment.last_name}</div>
                    <small class="text-muted">${payment.type || ''}</small>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success">${formatMoney(payment.amount)}</div>
                    <small class="text-muted">${formatTime(payment.created_at)}</small>
                </div>
            </div>`).join('')
        }</div>`;
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
                    updateStatsCards(result.data.stats, result.data.trends);
                }

                if (window.secDashboardWidgets && typeof window.secDashboardWidgets.refresh === 'function') {
                    window.secDashboardWidgets.refresh(result.data);
                } else {
                    if (result.data.todayAppointments) {
                        updateAppointmentsTable(result.data.todayAppointments);
                    }
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
});
</script>

<style>
    
/* Table header RTL alignment */
.table thead th {
    text-align: right !important;
    direction: rtl;
}

.table thead th.arabic-text {
    text-align: right !important;
    direction: rtl;
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
