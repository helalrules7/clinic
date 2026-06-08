<link href="/app/Views/secretary/assets/css/details.css?v=<?= file_exists(__DIR__ . '/assets/css/details.css') ? filemtime(__DIR__ . '/assets/css/details.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/../../doctor/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../../doctor/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<div class="container-fluid">
<!-- Payments Header -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-credit-card me-2"></i>
                إدارة المدفوعات والرصيد اليومي
            </h4>
            <div class="d-flex align-items-center ms-3" style="padding-bottom: 10px !important;">
                <label class="form-label mb-0 me-2" for="paymentsAutoRefresh">
                    <small class="text-muted arabic-text">تحديث تلقائي</small>
                </label>
                <div class="toggle-switch-wrapper">
                    <input type="checkbox" class="toggle-switch" id="paymentsAutoRefresh"
                           onchange="togglePaymentsAutoRefresh(this.checked)">
                </div>
            </div>
        </div>
        <p class="text-muted mb-0 arabic-text">تتبع وإدارة المدفوعات والرصيد اليومي</p>
        <div class="mt-2">
            <small class="text-muted arabic-text">
                <i class="bi bi-keyboard me-1"></i>
                اختصارات: 
                • تسجيل رصيد <kbd class="me-1">R</kbd> أو <kbd class="me-1">ر</kbd>
                • البحث <kbd class="me-1">F</kbd> أو <kbd class="me-1">ب</kbd>
                <kbd>Esc</kbd> إغلاق
            </small>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#dailyBalanceModal" 
                    title="تسجيل الرصيد اليومي">
                <i class="bi bi-plus-circle me-2"></i>
                تسجيل رصيد
                <span class="ms-2">
                    <kbd>R</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ر</kbd>
                </span>
            </button>
            <button class="btn btn-warning" 
                    data-bs-toggle="modal" 
                    data-bs-target="#expenseModal" 
                    title="تسجيل مصروف">
                <i class="bi bi-dash-circle me-2"></i>
                تسجيل مصروف
                <span class="ms-2">
                    <kbd>E</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">م</kbd>
                </span>
            </button>
            <button class="btn btn-info" 
                    data-bs-toggle="modal" 
                    data-bs-target="#searchModal" 
                    title="البحث في المدفوعات">
                <i class="bi bi-search me-2"></i>
                البحث
                <span class="ms-2">
                    <kbd>F</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ب</kbd>
                </span>
            </button>
            <?php if ($userRole === 'doctor'): ?>
            <button class="btn btn-warning" 
                    data-bs-toggle="modal" 
                    data-bs-target="#dailyClosureModal" 
                    title="إغلاق اليوم (للطبيب فقط)">
                <i class="bi bi-lock me-2"></i>
                إغلاق اليوم
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$paymentTrends = $paymentTrends ?? ['opening' => [], 'received' => [], 'expenses' => [], 'current' => [], 'transactions' => []];
$paymentTrendDeltas = $paymentTrendDeltas ?? ['opening' => 0, 'received' => 0, 'expenses' => 0, 'current' => 0, 'transactions' => 0];
?>
<script type="application/json" id="secPaymentsTrends"><?= json_encode($paymentTrends, JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="secPaymentsStatsInitial"><?= json_encode([
    'opening_balance' => (float)($dailyBalance['opening_balance'] ?? 0),
    'total_received' => (float)($dailyBalance['total_received'] ?? 0),
    'total_expenses' => (float)($dailyBalance['total_expenses'] ?? 0),
    'current_balance' => (float)($dailyBalance['current_balance'] ?? 0),
    'transactions_count' => (int)($dailyBalance['transactions_count'] ?? 0),
], JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="secPaymentsTrendDeltas"><?= json_encode($paymentTrendDeltas, JSON_UNESCAPED_UNICODE) ?></script>

<!-- Daily Balance Summary -->
<div class="row mb-4 stats-cards-wrapper sec-mini-stats sec-payments-stats">
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-success">
                <div class="mini-stat-icon"><i class="bi bi-wallet2"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPayStatOpening"><?= number_format((float)($dailyBalance['opening_balance'] ?? 0), 2) ?></span>
                    <span class="mini-stat-label arabic-text">الرصيد الافتتاحي</span>
                </div>
                <div class="mini-stat-chart" id="chartPayOpening"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPayOpening"><span>--</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-primary">
                <div class="mini-stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPayStatReceived"><?= number_format((float)($dailyBalance['total_received'] ?? 0), 2) ?></span>
                    <span class="mini-stat-label arabic-text">إجمالي المستلم</span>
                </div>
                <div class="mini-stat-chart" id="chartPayReceived"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPayReceived"><span>--</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-danger">
                <div class="mini-stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPayStatExpenses"><?= number_format((float)($dailyBalance['total_expenses'] ?? 0), 2) ?></span>
                    <span class="mini-stat-label arabic-text">إجمالي المصروفات</span>
                </div>
                <div class="mini-stat-chart" id="chartPayExpenses"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPayExpenses"><span>--</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-info">
                <div class="mini-stat-icon"><i class="bi bi-calculator"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPayStatCurrent"><?= number_format((float)($dailyBalance['current_balance'] ?? 0), 2) ?></span>
                    <span class="mini-stat-label arabic-text">الرصيد الحالي</span>
                </div>
                <div class="mini-stat-chart" id="chartPayCurrent"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPayCurrent"><span>--</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 mb-4 px-2">
        <div class="stats-card-wrapper">
            <div class="mini-stat-card mini-stat-warning">
                <div class="mini-stat-icon"><i class="bi bi-receipt"></i></div>
                <div class="mini-stat-content">
                    <span class="mini-stat-value arabic-text" id="secPayStatTx"><?= (int)($dailyBalance['transactions_count'] ?? 0) ?></span>
                    <span class="mini-stat-label arabic-text">معاملات اليوم</span>
                </div>
                <div class="mini-stat-chart" id="chartPayTx"></div>
                <div class="mini-stat-trend trend-neutral" id="trendPayTx"><span>--</span></div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Types Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card sec-pay-types-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-pie-chart me-2"></i>
                    ملخص المدفوعات حسب النوع
                </h5>
                <small class="text-muted arabic-text">اليوم</small>
            </div>
            <div class="card-body">
                <div class="sec-pay-types-grid">
                    <div class="sec-pay-type-tile" data-payment-type="new_booking">
                        <div class="sec-pay-type-icon sec-pay-type-primary"><i class="bi bi-calendar-plus"></i></div>
                        <div class="sec-pay-type-body">
                            <span class="sec-pay-type-label arabic-text">حجز جديد</span>
                            <span class="sec-pay-type-value arabic-text"><span class="payment-type-amount"><?= number_format((float)($paymentTypes['new_booking'] ?? 0), 2) ?></span> <small>جنيه</small></span>
                        </div>
                    </div>
                    <div class="sec-pay-type-tile" data-payment-type="followup">
                        <div class="sec-pay-type-icon sec-pay-type-info"><i class="bi bi-arrow-clockwise"></i></div>
                        <div class="sec-pay-type-body">
                            <span class="sec-pay-type-label arabic-text">إعادة كشف</span>
                            <span class="sec-pay-type-value arabic-text"><span class="payment-type-amount"><?= number_format((float)($paymentTypes['followup'] ?? 0), 2) ?></span> <small>جنيه</small></span>
                        </div>
                    </div>
                    <div class="sec-pay-type-tile" data-payment-type="consultation">
                        <div class="sec-pay-type-icon sec-pay-type-warning"><i class="bi bi-chat-dots"></i></div>
                        <div class="sec-pay-type-body">
                            <span class="sec-pay-type-label arabic-text">استشارة طبية</span>
                            <span class="sec-pay-type-value arabic-text"><span class="payment-type-amount"><?= number_format((float)($paymentTypes['consultation'] ?? 0), 2) ?></span> <small>جنيه</small></span>
                        </div>
                    </div>
                    <div class="sec-pay-type-tile" data-payment-type="procedure">
                        <div class="sec-pay-type-icon sec-pay-type-success"><i class="bi bi-activity"></i></div>
                        <div class="sec-pay-type-body">
                            <span class="sec-pay-type-label arabic-text">إجراء طبي</span>
                            <span class="sec-pay-type-value arabic-text"><span class="payment-type-amount"><?= number_format((float)($paymentTypes['procedure'] ?? 0), 2) ?></span> <small>جنيه</small></span>
                        </div>
                    </div>
                    <div class="sec-pay-type-tile" data-payment-type="other">
                        <div class="sec-pay-type-icon sec-pay-type-muted"><i class="bi bi-three-dots"></i></div>
                        <div class="sec-pay-type-body">
                            <span class="sec-pay-type-label arabic-text">أخرى</span>
                            <span class="sec-pay-type-value arabic-text"><span class="payment-type-amount"><?= number_format((float)($paymentTypes['other'] ?? 0), 2) ?></span> <small>جنيه</small></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Transactions Log -->
<div class="card mb-4">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-journal-text me-2"></i>
                    سجل المعاملات المالية
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <!-- Export to Excel -->
                    <button class="btn btn-success btn-sm" onclick="exportToExcel()" title="تصدير إلى Excel">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        <span class="d-none d-sm-inline">تصدير Excel</span>
                        <span class="d-sm-none">Excel</span>
                    </button>
                    <!-- Date Filter -->
                    <div class="d-flex align-items-center">
                        <label for="dateFilter" class="form-label mb-0 me-2 text-muted arabic-text d-none d-md-inline">التاريخ:</label>
                        <input type="date" class="form-control form-control-sm" id="dateFilter" style="min-width: 140px;">
                    </div>
                    <!-- Transaction Type Filter -->
                    <div class="d-flex align-items-center">
                        <label for="transactionTypeFilter" class="form-label mb-0 me-2 text-muted arabic-text d-none d-md-inline">النوع:</label>
                        <select class="form-select form-select-sm" id="transactionTypeFilter" style="min-width: 120px;">
                            <option value="all" class="arabic-text">الكل</option>
                            <option value="payment" class="arabic-text">مدفوعات</option>
                            <option value="expense" class="arabic-text">مصروفات</option>
                            <option value="balance" class="arabic-text">رصيد</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text text-end" dir="rtl">التاريخ</th>
                        <th class="arabic-text text-end" dir="rtl">النوع</th>
                        <th class="arabic-text text-end" dir="rtl">الوصف</th>
                        <th class="arabic-text text-end" dir="rtl">المبلغ</th>
                        <th class="arabic-text text-end" dir="rtl">الرصيد</th>
                        <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="transactionsTableBody">
                    <!-- Transactions will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="text-muted arabic-text">
                عرض <span id="showingFrom">1</span> إلى <span id="showingTo">10</span> من <span id="totalRecords">0</span> معاملة
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="transactionsPagination">
                    <!-- Pagination will be generated here -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-list-ul me-2"></i>
                    سجلات المدفوعات
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <!-- Quick Search -->
                    <div class="d-flex align-items-center">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="quickSearch" 
                                   placeholder="بحث سريع..."
                                   autocomplete="off"
                                   style="min-width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" id="clearQuickSearch">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Filter by type -->
                    <div class="d-flex align-items-center">
                        <label for="typeFilter" class="form-label mb-0 me-2 text-muted arabic-text d-none d-md-inline">النوع:</label>
                        <select class="form-select form-select-sm" id="typeFilter" style="min-width: 120px;">
                            <option value="all" class="arabic-text">الكل</option>
                            <option value="new_booking" class="arabic-text">حجز جديد</option>
                            <option value="followup" class="arabic-text">إعادة كشف</option>
                            <option value="consultation" class="arabic-text">استشارة طبية</option>
                            <option value="procedure" class="arabic-text">إجراء طبي</option>
                        </select>
                    </div>
                    <!-- Filter by payment method -->
                    <div class="d-flex align-items-center">
                        <label for="methodFilter" class="form-label mb-0 me-2 text-muted arabic-text d-none d-md-inline">طريقة الدفع:</label>
                        <select class="form-select form-select-sm" id="methodFilter" style="min-width: 120px;">
                            <option value="all" class="arabic-text">الكل</option>
                            <option value="Cash" class="arabic-text">نقدي</option>
                            <option value="Card" class="arabic-text">بطاقة ائتمان</option>
                            <option value="Transfer" class="arabic-text">تحويل بنكي</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text text-end" dir="rtl">التاريخ</th>
                        <th class="arabic-text text-end" dir="rtl">المريض</th>
                        <th class="arabic-text text-end" dir="rtl">المبلغ</th>
                        <th class="arabic-text text-end" dir="rtl">النوع</th>
                        <th class="arabic-text text-end" dir="rtl">طريقة الدفع</th>
                        <th class="arabic-text text-end" dir="rtl">الوصف</th>
                        <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0 arabic-text">لا توجد مدفوعات</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr data-type="<?= $payment['type'] ?? 'other' ?>" data-method="<?= $payment['method'] ?? 'cash' ?>">
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
                                            <div class="fw-semibold arabic-text patient-hover-name" data-patient-id="<?= (int)($payment['patient_id'] ?? 0) ?>"><?= htmlspecialchars($payment['patient_name'] ?? 'غير محدد') ?></div>
                                            <small class="text-muted"><?= $payment['patient_phone'] ?? 'غير محدد' ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success"><?= number_format($payment['amount'], 2) ?> جنيه</span>
                                </td>
                                <td>
                                    <span class="badge <?= $this->getPaymentTypeBadgeClass($payment['type'] ?? 'other') ?> arabic-text">
                                        <?= $this->getPaymentTypeText($payment['type'] ?? 'other') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $this->getPaymentMethodBadgeClass($payment['method'] ?? 'cash') ?> arabic-text">
                                        <?= $this->getPaymentMethodText($payment['method'] ?? 'cash') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted arabic-text"><?= $payment['description'] ?? 'لا يوجد وصف' ?></span>
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
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text" id="searchModalLabel">
                    <i class="bi bi-search me-2"></i>
                    البحث في المدفوعات
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
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
                               id="globalSearchPayments" 
                               placeholder="ابحث بالاسم أو رقم الهاتف أو رقم الدفعة..."
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearchPayments">
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
                <div id="searchResultsPayments">
                    <!-- Loading State -->
                    <div id="searchLoadingPayments" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden arabic-text">جاري البحث...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0 arabic-text">جاري البحث في المدفوعات...</p>
                    </div>

                    <!-- No Results -->
                    <div id="noResultsPayments" class="text-center py-4" style="display: none;">
                        <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2 arabic-text">لا توجد نتائج</h6>
                        <p class="text-muted mb-0 arabic-text">جرب مصطلحات بحث مختلفة</p>
                    </div>

                    <!-- Results Container -->
                    <div id="searchResultsListPayments" class="search-results-container">
                        <!-- Results will be populated here -->
                    </div>

                    <!-- Initial State -->
                    <div id="searchInitialPayments" class="text-center py-4">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2 arabic-text">البحث في المدفوعات</h6>
                        <p class="text-muted mb-0 arabic-text">أدخل الاسم أو رقم الهاتف أو رقم الدفعة للبحث</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Daily Balance Modal -->
<div class="modal fade" id="dailyBalanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-wallet2 me-2"></i>
                    تسجيل الرصيد اليومي
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
            </div>
            <form id="dailyBalanceForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="dailyBalanceMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-wallet2 me-1"></i>
                                تفاصيل الرصيد
                            </h6>
                            
                            <div class="mb-3">
                                <label for="balanceAmount" class="form-label arabic-text">مبلغ الرصيد <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">جنيه</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="balanceAmount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                                <div class="invalid-feedback"></div>
                                </div>
                            
                            <div class="mb-3">
                                <label for="balanceType" class="form-label arabic-text">نوع الرصيد <span class="text-danger">*</span></label>
                                <select class="form-select" id="balanceType" name="balance_type" required>
                                    <option value="" class="arabic-text">-- اختر نوع الرصيد --</option>
                                    <option value="opening" class="arabic-text">رصيد افتتاحي</option>
                                    <option value="additional" class="arabic-text">إضافة رصيد</option>
                                    <option value="withdrawal" class="arabic-text">سحب من الرصيد</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            </div>
                            
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-info-circle me-1"></i>
                                تفاصيل إضافية
                            </h6>
                            
                            <div class="mb-3">
                                <label for="balanceDescription" class="form-label arabic-text">الوصف</label>
                                <textarea class="form-control" 
                                          id="balanceDescription" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="وصف الرصيد..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="balanceDate" class="form-label arabic-text">تاريخ الرصيد</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="balanceDate" 
                                       name="balance_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary arabic-text" id="dailyBalanceSubmit">
                        <i class="bi bi-plus-circle me-1"></i>
                        <span class="btn-text arabic-text">تسجيل الرصيد</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
                                </div>
                            </div>
                        </div>
                        
<!-- Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-dash-circle me-2"></i>
                    تسجيل مصروف جديد
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
            </div>
            <form id="expenseForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="expenseMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-dash-circle me-1"></i>
                                تفاصيل المصروف
                            </h6>
                            
                            <div class="mb-3">
                                <label for="expenseAmount" class="form-label arabic-text">مبلغ المصروف <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">جنيه</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="expenseAmount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expenseName" class="form-label arabic-text">اسم المصروف <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="expenseName" 
                                       name="expense_name" 
                                       placeholder="أدخل اسم المصروف..."
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <!-- Expense Type Badges -->
                            <div class="mb-3">
                                <label class="form-label arabic-text">أنواع المصروفات السريعة:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="فاتورة مياه" 
                                          style="cursor: pointer;">
                                        فاتورة مياه
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="فاتورة كهرباء" 
                                          style="cursor: pointer;">
                                        فاتورة كهرباء
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="مستلزمات طبية" 
                                          style="cursor: pointer;">
                                        مستلزمات طبية
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="مصروفات نظافة" 
                                          style="cursor: pointer;">
                                        مصروفات نظافة
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="صيانة" 
                                          style="cursor: pointer;">
                                        صيانة
                                    </span>
                                    <span class="badge bg-light text-dark cursor-pointer expense-type-badge" 
                                          data-type="أخرى" 
                                          style="cursor: pointer;">
                                        أخرى
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-info-circle me-1"></i>
                                تفاصيل إضافية
                            </h6>
                            
                            <div class="mb-3">
                                <label for="expenseCategory" class="form-label arabic-text">فئة المصروف</label>
                                <select class="form-select" id="expenseCategory" name="category">
                                    <option value="utilities" class="arabic-text">مرافق عامة</option>
                                    <option value="medical" class="arabic-text">طبية</option>
                                    <option value="maintenance" class="arabic-text">صيانة</option>
                                    <option value="office" class="arabic-text">مكتبية</option>
                                    <option value="other" class="arabic-text">أخرى</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expenseNotes" class="form-label arabic-text">ملاحظات</label>
                                <textarea class="form-control" 
                                          id="expenseNotes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="ملاحظات حول المصروف..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expenseDate" class="form-label arabic-text">تاريخ المصروف</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="expenseDate" 
                                       name="expense_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning arabic-text" id="expenseSubmit">
                        <i class="bi bi-dash-circle me-1"></i>
                        <span class="btn-text arabic-text">تسجيل المصروف</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Daily Closure Modal (Doctor Only) -->
<?php if ($userRole === 'doctor'): ?>
<div class="modal fade" id="dailyClosureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-lock me-2"></i>
                    إغلاق اليوم
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
            </div>
            <form id="dailyClosureForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="dailyClosureMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="alert alert-warning arabic-text">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>تحذير:</strong> إغلاق اليوم سيؤدي إلى:
                        <ul class="mb-0 mt-2">
                            <li>إغلاق جميع المعاملات المالية لليوم</li>
                            <li>بدء يوم جديد برصيد صفر</li>
                            <li>عدم إمكانية تعديل المعاملات المغلقة</li>
                        </ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-calculator me-1"></i>
                                ملخص اليوم
                            </h6>
                            
                            <div class="mb-3">
                                <label class="form-label arabic-text">الرصيد الافتتاحي</label>
                                <div class="form-control-plaintext fw-bold text-success">
                                    <?= $dailyBalance['opening_balance'] ?? 0 ?> جنيه
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label arabic-text">إجمالي المستلم</label>
                                <div class="form-control-plaintext fw-bold text-primary">
                                    <?= $dailyBalance['total_received'] ?? 0 ?> جنيه
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label arabic-text">الرصيد النهائي</label>
                                <div class="form-control-plaintext fw-bold text-info">
                                    <?= $dailyBalance['current_balance'] ?? 0 ?> جنيه
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3 arabic-text">
                                <i class="bi bi-info-circle me-1"></i>
                                تفاصيل الإغلاق
                            </h6>
                            
                            <div class="mb-3">
                                <label for="closureNotes" class="form-label arabic-text">ملاحظات الإغلاق</label>
                                <textarea class="form-control" 
                                          id="closureNotes" 
                                          name="closure_notes" 
                                          rows="4" 
                                          placeholder="ملاحظات حول إغلاق اليوم..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirmClosure" required>
                                    <label class="form-check-label arabic-text" for="confirmClosure">
                                        أؤكد إغلاق اليوم والبدء في يوم جديد
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning arabic-text" id="dailyClosureSubmit">
                        <i class="bi bi-lock me-1"></i>
                        <span class="btn-text arabic-text">إغلاق اليوم</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="/app/Views/secretary/assets/js/sec-mini-stats.js?v=<?= file_exists(__DIR__ . '/assets/js/sec-mini-stats.js') ? filemtime(__DIR__ . '/assets/js/sec-mini-stats.js') : time() ?>"></script>
<script src="/app/Views/secretary/assets/js/sec-payments-page.js?v=<?= file_exists(__DIR__ . '/assets/js/sec-payments-page.js') ? filemtime(__DIR__ . '/assets/js/sec-payments-page.js') : time() ?>"></script>

<script>
// Payment management functions
function viewPayment(paymentId) {
    window.location.href = `/secretary/payments/${paymentId}`;
}

function printReceipt(paymentId) {
    window.open(`/secretary/payments/${paymentId}/receipt`, '_blank');
}

// Initialize when DOM is ready
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
    // Set current date and time as default
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('balanceDate').value = localDateTime;
    
    // Set current date and time as default for expense form
    document.getElementById('expenseDate').value = localDateTime;
    
    // Expense type badges functionality
    const expenseTypeBadges = document.querySelectorAll('.expense-type-badge');
    expenseTypeBadges.forEach(badge => {
        badge.addEventListener('click', function() {
            const expenseName = document.getElementById('expenseName');
            expenseName.value = this.dataset.type;
            
            // Update badge appearance
            expenseTypeBadges.forEach(b => b.classList.remove('bg-primary', 'text-white'));
            this.classList.add('bg-primary', 'text-white');
        });
    });
    
    // Expense form submission
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        expenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!expenseForm.checkValidity()) {
                expenseForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(expenseForm);
            
            // Show loading state
            const submitButton = document.getElementById('expenseSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'جاري التسجيل...';
            spinner.classList.remove('d-none');
            
            // Convert FormData to JSON
            const jsonData = {
                amount: formData.get('amount'),
                expense_name: formData.get('expense_name'),
                category: formData.get('category'),
                notes: formData.get('notes'),
                expense_date: formData.get('expense_date')
            };
            
            fetch('/api/expenses', {
                method: 'POST',
                body: JSON.stringify(jsonData),
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
                credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'تسجيل المصروف';
                spinner.classList.add('d-none');
                
        if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('expenseMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'تم تسجيل المصروف بنجاح!';
                    messageEl.classList.remove('d-none');
                    
                    // Reset form
                    expenseForm.reset();
                    expenseForm.classList.remove('was-validated');
                    document.getElementById('expenseDate').value = localDateTime;
                    
                    // Reset badges
                    expenseTypeBadges.forEach(b => b.classList.remove('bg-primary', 'text-white'));
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('expenseModal')).hide();
                        // Update cards and transactions without full reload
                        updateDashboardCards();
                        loadFinancialTransactions();
                    }, 1500);
        } else {
                    // Error
                    const messageEl = document.getElementById('expenseMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'فشل في تسجيل المصروف';
                    messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'تسجيل المصروف';
                spinner.classList.add('d-none');
                
        console.error('Error:', error);
                const messageEl = document.getElementById('expenseMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'خطأ في تسجيل المصروف';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Daily balance form submission
    const dailyBalanceForm = document.getElementById('dailyBalanceForm');
    if (dailyBalanceForm) {
        dailyBalanceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!dailyBalanceForm.checkValidity()) {
                dailyBalanceForm.classList.add('was-validated');
        return;
    }
    
            const formData = new FormData(dailyBalanceForm);
            
            // Show loading state
            const submitButton = document.getElementById('dailyBalanceSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'جاري التسجيل...';
            spinner.classList.remove('d-none');
            
            fetch('/api/daily-balance', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
    })
    .then(response => response.json())
    .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'تسجيل الرصيد';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('dailyBalanceMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'تم تسجيل الرصيد بنجاح!';
                    messageEl.classList.remove('d-none');
                    
                    // Reset form
                    dailyBalanceForm.reset();
                    dailyBalanceForm.classList.remove('was-validated');
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('dailyBalanceModal')).hide();
                        // Update cards and transactions without full reload
                        updateDashboardCards();
                        loadFinancialTransactions();
                    }, 1500);
        } else {
                    // Error
                    const messageEl = document.getElementById('dailyBalanceMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'فشل في تسجيل الرصيد';
                    messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'تسجيل الرصيد';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('dailyBalanceMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'خطأ في تسجيل الرصيد';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Daily closure form submission (Doctor only)
    const dailyClosureForm = document.getElementById('dailyClosureForm');
    if (dailyClosureForm) {
        dailyClosureForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!dailyClosureForm.checkValidity()) {
                dailyClosureForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(dailyClosureForm);
            
            // Show loading state
            const submitButton = document.getElementById('dailyClosureSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'جاري الإغلاق...';
            spinner.classList.remove('d-none');
            
            fetch('/api/daily-closure', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'إغلاق اليوم';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('dailyClosureMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'تم إغلاق اليوم بنجاح!';
                    messageEl.classList.remove('d-none');
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('dailyClosureModal')).hide();
                        // Update cards and transactions without full reload
                        updateDashboardCards();
                        loadFinancialTransactions();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('dailyClosureMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'فشل في إغلاق اليوم';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'إغلاق اليوم';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('dailyClosureMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'خطأ في إغلاق اليوم';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Type filter
    const typeFilter = document.getElementById('typeFilter');
    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            filterPaymentsByType(this.value);
        });
    }
    
    // Method filter
    const methodFilter = document.getElementById('methodFilter');
    if (methodFilter) {
        methodFilter.addEventListener('change', function() {
            filterPaymentsByMethod(this.value);
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
        
        // Open daily balance modal with 'R' key or Arabic 'ر' key
        const dailyBalanceKeys = ['r', 'ر'];
        const isDailyBalanceKey = dailyBalanceKeys.includes(e.key.toLowerCase()) || dailyBalanceKeys.includes(e.key);
        
        if (isDailyBalanceKey && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#dailyBalanceModal"]').click();
        }
        
        // Open expense modal with 'E' key or Arabic 'م' key
        const expenseKeys = ['e', 'م'];
        const isExpenseKey = expenseKeys.includes(e.key.toLowerCase()) || expenseKeys.includes(e.key);
        
        if (isExpenseKey && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#expenseModal"]').click();
        }
        
        // Open search modal with 'F' key or Arabic 'ب' key
        const searchKeys = ['f', 'ب'];
        const isSearchKey = searchKeys.includes(e.key.toLowerCase()) || searchKeys.includes(e.key);
        
        if (isSearchKey && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            const searchModalBtn = document.querySelector('[data-bs-target="#searchModal"]');
            if (searchModalBtn) {
                searchModalBtn.click();
            }
        }
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                e.preventDefault();
                const modalInstance = bootstrap.Modal.getInstance(openModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        }
    });
    
    // Initialize search modal properly
    const searchModal = document.getElementById('searchModal');
    if (searchModal) {
        // Initialize Bootstrap modal with proper config
        const modalInstance = new bootstrap.Modal(searchModal, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        
        // Focus on search input when modal is shown
        searchModal.addEventListener('shown.bs.modal', function() {
            const searchInput = document.getElementById('globalSearchPayments');
            if (searchInput) {
                searchInput.focus();
            }
        });
        
        // Clear search when modal is hidden
        searchModal.addEventListener('hidden.bs.modal', function() {
            const searchInput = document.getElementById('globalSearchPayments');
            const searchResults = document.getElementById('searchResultsPayments');
            if (searchInput) {
                searchInput.value = '';
            }
            if (searchResults) {
                document.getElementById('searchInitialPayments').style.display = 'block';
                document.getElementById('searchLoadingPayments').style.display = 'none';
                document.getElementById('noResultsPayments').style.display = 'none';
                document.getElementById('searchResultsListPayments').innerHTML = '';
            }
        });
    }
    
    // Load financial transactions
    loadFinancialTransactions();
    
    // Update dashboard cards on page load
    updateDashboardCards();
    
    // Transaction filters
    const dateFilter = document.getElementById('dateFilter');
    const transactionTypeFilter = document.getElementById('transactionTypeFilter');
    
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            loadFinancialTransactions();
        });
    }
    
    if (transactionTypeFilter) {
        transactionTypeFilter.addEventListener('change', function() {
            loadFinancialTransactions();
        });
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

// Financial transactions management
let currentPage = 1;
const itemsPerPage = 10;

function loadFinancialTransactions(page = 1) {
    currentPage = page;
    
    const dateFilter = document.getElementById('dateFilter').value;
    const transactionTypeFilter = document.getElementById('transactionTypeFilter').value;
    
    const params = new URLSearchParams({
        page: page,
        limit: itemsPerPage,
        date: dateFilter,
        type: transactionTypeFilter
    });
    
    fetch(`/api/financial-transactions?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayTransactions(data.data.transactions);
                updatePagination(data.data.pagination);
            } else {
                console.error('Error loading transactions:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayTransactions(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2 mb-0 arabic-text">لا توجد معاملات</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    transactions.forEach(transaction => {
        const typeBadge = getTransactionTypeBadge(transaction.type);
        const amountClass = transaction.type === 'expense' ? 'text-danger' : 'text-success';
        const amountPrefix = transaction.type === 'expense' ? '-' : '+';
        
        html += `
            <tr>
                <td>${formatDateTime(transaction.created_at)}</td>
                <td>${typeBadge}</td>
                <td>${transaction.description}</td>
                <td>
                    <span class="fw-bold ${amountClass}">
                        ${amountPrefix}${formatMoney(transaction.amount)} جنيه
                    </span>
                </td>
                <td>
                    <span class="fw-bold text-primary">${formatMoney(transaction.balance)} جنيه</span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        ${getTransactionActions(transaction)}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function getTransactionTypeBadge(type) {
    const badges = {
        'payment': '<span class="badge bg-success arabic-text">مدفوع</span>',
        'expense': '<span class="badge bg-danger arabic-text">مصروف</span>',
        'balance': '<span class="badge bg-info arabic-text">رصيد</span>'
    };
    return badges[type] || '<span class="badge bg-secondary arabic-text">غير محدد</span>';
}

function getTransactionActions(transaction) {
    let actions = '';
    
    if (transaction.type === 'payment') {
        actions += `
            <button type="button" class="btn btn-outline-primary btn-sm" 
                    onclick="viewPayment(${transaction.id})"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-title="عرض تفاصيل الدفعة">
                <i class="bi bi-eye"></i>
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" 
                    onclick="printReceipt(${transaction.id})"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-title="طباعة الإيصال">
                <i class="bi bi-printer"></i>
            </button>
        `;
    } else if (transaction.type === 'expense') {
        actions += `
            <button type="button" class="btn btn-outline-warning btn-sm" 
                    onclick="viewExpense(${transaction.id})"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-title="عرض تفاصيل المصروف">
                <i class="bi bi-eye"></i>
            </button>
        `;
    }
    
    return actions;
}

function updatePagination(pagination) {
    document.getElementById('showingFrom').textContent = pagination.from;
    document.getElementById('showingTo').textContent = pagination.to;
    document.getElementById('totalRecords').textContent = pagination.total;
    
    const paginationContainer = document.getElementById('transactionsPagination');
    let html = '';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${pagination.current_page - 1})">السابق</a>
            </li>
        `;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${i})">${i}</a>
            </li>
        `;
    }
    
    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${pagination.current_page + 1})">التالي</a>
            </li>
        `;
    }
    
    paginationContainer.innerHTML = html;
}

function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleDateString('ar-EG') + ' ' + date.toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit'});
}

function formatMoney(amount) {
    return new Intl.NumberFormat('ar-EG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

function exportToExcel() {
    const dateFilter = document.getElementById('dateFilter').value;
    const transactionTypeFilter = document.getElementById('transactionTypeFilter').value;
    
    // Show loading state
    const exportBtn = document.querySelector('[onclick="exportToExcel()"]');
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>جاري التصدير...';
    exportBtn.disabled = true;
    
    const params = new URLSearchParams({
        date: dateFilter,
        type: transactionTypeFilter
    });
    
    // Use window.open for direct download
    const exportUrl = `/api/financial-transactions/export?${params}`;
    window.open(exportUrl, '_blank');
    
    // Reset button
    setTimeout(() => {
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
        
        // Show success message
        showNotification('تم تصدير الملف بنجاح!', 'success');
    }, 1000);
}

function viewExpense(expenseId) {
    window.location.href = `/secretary/expenses/${expenseId}`;
}

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function filterPaymentsByType(type) {
    const rows = document.querySelectorAll('#paymentsTableBody tr[data-type]');
    
    rows.forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterPaymentsByMethod(method) {
    const rows = document.querySelectorAll('#paymentsTableBody tr[data-method]');
    
    rows.forEach(row => {
        if (method === 'all' || row.dataset.method === method) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterPaymentsBySearch(query) {
    const rows = document.querySelectorAll('#paymentsTableBody tr[data-type]');
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
/* RTL — icon spacing: sec-style.css §secretary icon spacing */
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

.sec-pay-types-card {
    background: var(--glass-bg-strong, var(--card));
    border: 1px solid var(--border);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
}
.sec-pay-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}
.sec-pay-type-tile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    background: color-mix(in srgb, var(--accent) 4%, var(--card));
    border: 1px solid color-mix(in srgb, var(--accent) 12%, var(--border));
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.sec-pay-type-tile:hover {
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--accent) 28%, var(--border));
    box-shadow: 0 8px 24px color-mix(in srgb, var(--accent) 12%, transparent);
}
.sec-pay-type-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.sec-pay-type-primary { background: linear-gradient(135deg, var(--accent), var(--ds-accent-2, var(--accent))); }
.sec-pay-type-info { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.sec-pay-type-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
.sec-pay-type-success { background: linear-gradient(135deg, #10b981, #059669); }
.sec-pay-type-muted { background: linear-gradient(135deg, #64748b, #475569); }
.sec-pay-type-body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.sec-pay-type-label {
    font-size: 0.8rem;
    color: var(--muted);
}
.sec-pay-type-value {
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--text);
}
.sec-pay-type-value small {
    font-weight: 500;
    font-size: 0.75rem;
    color: var(--muted);
}

.sec-payments-stats .mini-stat-danger {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(239, 68, 68, 0.02) 100%);
    border-color: rgba(239, 68, 68, 0.2);
}
.sec-payments-stats .mini-stat-danger .mini-stat-icon {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
}
.sec-payments-stats .mini-stat-danger .mini-stat-value { color: #ef4444; }
.sec-payments-stats .mini-stat-danger .sparkline-path { stroke: #ef4444; }
.sec-payments-stats .mini-stat-danger .sparkline-area { fill: #ef4444; }
.dark .sec-payments-stats .mini-stat-danger .mini-stat-value { color: #fb7185; }
.dark .sec-payments-stats .mini-stat-danger .sparkline-path { stroke: #fb7185; }
.dark .sec-payments-stats .mini-stat-danger .sparkline-area { fill: #fb7185; }

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

.btn-info kbd {
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
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: var(--accent) var(--bg);
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: var(--bg);
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: var(--accent);
        opacity: 0.8;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .keyboard-hint {
        position: static;
        margin-top: 10px;
    }
    
    /* Card header responsive */
    .card-header .row {
        margin: 0;
    }
    
    .card-header .col-md-6 {
        padding: 0.5rem 0;
    }
    
    .card-header .d-flex {
        flex-wrap: wrap;
        gap: 0.5rem !important;
    }
    
    /* Ensure filters stack on mobile */
    .card-header .d-flex > div {
        flex: 1 1 auto;
        min-width: 100%;
    }
    
    .card-header .input-group {
        width: 100% !important;
        margin-bottom: 0.5rem;
    }
    
    .card-header .form-select {
        width: 100% !important;
        margin-bottom: 0.5rem;
    }
    
    /* Table cells */
    .table th,
    .table td {
        white-space: nowrap;
        min-width: 100px;
    }
}

:root {
    --sidebar-width: 280px;
}

.dark {
}
/* Statistics Cards Styling */
.stat-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
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

.stat-content {
    flex: 1;
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
    background: #3498db; /* Sky blue for males */
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.avatar-female {
    background:rgb(255, 85, 224); /* Pink for females */
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

/* Hover effects */
.avatar-male:hover {
    background: #2980b9;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.avatar-female:hover {
    background:rgb(255, 85, 224); /* Pink for females */
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}

/* Default fallback for unknown gender */
.avatar-circle:not(.avatar-male):not(.avatar-female) {
    background: var(--accent);
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.3);
}

.card {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.card-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.table {
    background-color: var(--bg-dark);
    color: var(--text);
}

.table thead th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table-dark th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table tbody tr {
    background-color: var(--bg-dark);
    border-color: var(--border);
}

.table tbody tr:hover {
    background-color: var(--bg-alt);
}

.table td {
    background-color: var(--bg-dark);
    border-color: var(--border);
    color: var(--text);
}

.btn-group .btn {
    margin: 0 1px;
}

/* Search Modal Styles */
.modal-content {
    background: var(--card);
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

/* Apply gender colors to search result avatars */
.search-result-avatar.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.search-result-avatar.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.search-result-avatar:not(.avatar-male):not(.avatar-female) {
    background: var(--accent);
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.3);
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

#globalSearch:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

/* §24 — outline buttons: sec-style.css global gradient system */

.btn-secondary {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.btn-secondary:hover {
    background-color: var(--border);
    border-color: var(--border);
    color: var(--text);
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

/* RTL Modal Header Adjustments */
.modal-header {
    direction: rtl;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    position: relative;
    padding: 1rem 1.5rem;
}

.modal-header .btn-close {
    order: -1;
    margin-left: 0;
    margin-right: 0;
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.modal-header .keyboard-hint {
    order: -1;
    position: absolute;
    left: 10% !important;
    right: auto !important;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
    z-index: 9;
    white-space: nowrap;
}

.modal-header .modal-title {
    order: 0;
    margin-right: auto;
    margin-left: 0;
    flex: 1;
    text-align: right;
    padding-right: 0;
    padding-left: 60px; /* Space for close button */
}

.modal-header:has(.keyboard-hint) .modal-title {
    padding-left: 120px; /* Extra space when keyboard-hint exists */
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

/* Add Patient Modal Styling */
#addPatientModal .modal-content {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal .modal-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

#addPatientModal .modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

#addPatientModal .form-label {
    color: var(--text);
    font-weight: 500;
}

#addPatientModal .form-control,
#addPatientModal .form-select {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

#addPatientModal .form-control:focus,
#addPatientModal .form-select:focus {
    background-color: var(--bg);
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

/* Button styling for add patient modal */
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    color: white;
}

.btn-success:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
    opacity: 0.65;
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

/* Delete Patient Modal Styles */
#deletePatientModal .modal-content,
#deletePatientConfirmModal .modal-content {
    background: var(--card);
    color: var(--text);
}

#deletePatientModal .modal-header,
#deletePatientConfirmModal .modal-header {
    background-color: #dc3545 !important;
    border-bottom-color: #dc3545;
}

#deletePatientModal .modal-footer,
#deletePatientConfirmModal .modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

#deletePatientModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

[data-bs-theme="dark"] #deletePatientModal .alert-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #f5c6cb;
}

#deletePatientModal .alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border-color: #ffc107;
    color: #856404;
}

[data-bs-theme="dark"] #deletePatientModal .alert-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #ffeaa7;
}

#deletePatientModal .list-group-item {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

#deletePatientModal .card {
    background-color: var(--bg);
    border-color: #ffc107;
}

#deletePatientModal .card-body {
    background-color: var(--bg-alt);
}

/* §24 — btn-outline-danger: sec-style.css */

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
}

#deleteConfirmationText {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Courier New', monospace;
    font-weight: bold;
    letter-spacing: 2px;
}

#deleteConfirmationText:focus {
    background-color: var(--bg);
    border-color: #dc3545;
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

#deleteConfirmationText.is-valid {
    border-color: #28a745;
    background-color: var(--bg);
}

#deleteConfirmationText.is-invalid {
    border-color: #dc3545;
    background-color: var(--bg);
}

/* Arabic text styling for delete messages */
#deleteConfirmationMessage {
    font-family: 'Cairo', Arial, sans-serif;
    text-align: right;
    direction: rtl;
}

#deleteConfirmationMessage.alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #155724;
}

[data-bs-theme="dark"] #deleteConfirmationMessage.alert-success {
    background-color: rgba(40, 167, 69, 0.15);
    color: #d4edda;
}

#deleteConfirmationMessage.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border-color: #ffc107;
    color: #856404;
}

[data-bs-theme="dark"] #deleteConfirmationMessage.alert-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #fff3cd;
}

#deleteConfirmationMessage.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #721c24;
}

[data-bs-theme="dark"] #deleteConfirmationMessage.alert-danger {
    background-color: rgba(220, 53, 69, 0.15);
    color: #f8d7da;
}

/* Keyboard shortcuts info styling */
.text-muted kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.7rem;
    padding: 1px 4px;
    margin: 0 1px;
    border-radius: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Courier New', 'Cairo', monospace;
}

[data-bs-theme="dark"] .text-muted kbd {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
}

/* Delete help text styling for better visibility */
.delete-help-text {
    background-color: rgba(13, 110, 253, 0.1) !important;
    border: 1px solid rgba(13, 110, 253, 0.2) !important;
    border-radius: 6px !important;
    padding: 8px 12px !important;
    margin-top: 8px !important;
    color: var(--text) !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
}

[data-bs-theme="dark"] .delete-help-text {
    background-color: rgba(13, 110, 253, 0.15) !important;
    border-color: rgba(13, 110, 253, 0.3) !important;
    color: #ffffff !important;
}

[data-bs-theme="light"] .delete-help-text {
    background-color: rgba(13, 110, 253, 0.08) !important;
    border-color: rgba(13, 110, 253, 0.2) !important;
    color: #212529 !important;
}

/* Pagination Styling */
.card-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
    color: var(--text);
}

.pagination-info {
    font-family: 'Cairo', Arial, sans-serif;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Cairo', Arial, sans-serif;
    padding: 0.375rem 0.75rem;
    margin: 0 2px;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.pagination .page-link:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.3);
}

.pagination .page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.4);
}

.pagination .page-item.disabled .page-link {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--muted);
    opacity: 0.6;
    cursor: not-allowed;
}

.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
    border-radius: 6px;
}

.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Show/Hide pagination based on content */
#paginationContainer.d-none {
    display: none !important;
}

/* Pagination limit select styling */
#paginationLimit {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Cairo', Arial, sans-serif;
    font-size: 0.875rem;
    min-width: 80px;
}

#paginationLimit:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

/* Quick search styling */
#quickSearch {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    font-family: 'Cairo', Arial, sans-serif;
    font-size: 0.875rem;
    border-radius: 0;
    border-left: none;
    border-right: none;
}

#quickSearch:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: none;
    z-index: 3;
}

#quickSearch::placeholder {
    color: var(--muted);
    font-style: italic;
}

.input-group-sm .input-group-text {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
    font-size: 0.875rem;
    border-right: 1px solid var(--border);
}

/* §24 — input-group outline buttons: sec-style.css */

/* Quick search focus state */
#quickSearch:focus + .btn-outline-secondary {
    box-shadow: 0 0 0 0.15rem rgba(var(--accent-rgb), 0.2);
}

.input-group:focus-within .input-group-text {
    border-color: var(--accent);
}

/* Table header gap adjustments */
.card-header .gap-3 {
    gap: 1rem !important;
}

@media (max-width: 768px) {
    .card-header .d-flex.gap-3 {
        flex-direction: column;
        gap: 0.5rem !important;
        align-items: stretch !important;
    }
    
    .card-header .input-group {
        width: 100% !important;
    }
    
    .card-header .justify-content-end {
        justify-content: stretch !important;
    }
}

/* Loading state for table */
.table-loading {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
}

.table-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 2rem;
    height: 2rem;
    border: 3px solid var(--border);
    border-top: 3px solid var(--accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 10;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Responsive pagination */
@media (max-width: 768px) {
    .pagination-info {
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .pagination {
        justify-content: center !important;
    }
    
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        margin: 0 1px;
    }
}

/* Doctor Filter Styling */
#doctorFilterGroup .btn {
    border-radius: 6px;
    margin: 0 2px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

#doctorFilterGroup .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#doctorFilterGroup .btn.active {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* §24 — doctorFilterGroup active: sec-style.css */

#doctorFilterGroup .btn i {
    font-size: 0.9rem;
}

/* Filter card styling */
.card.border-info {
    border-color: var(--accent) !important;
}

.card-header.bg-info.bg-opacity-10 {
    background-color: rgba(var(--accent-rgb), 0.1) !important;
    border-bottom-color: rgba(var(--accent-rgb), 0.2) !important;
}

.text-info {
    color: var(--accent) !important;
}

/* Responsive filter buttons */
@media (max-width: 768px) {
    #doctorFilterGroup {
        flex-direction: column;
        width: 100%;
    }
    
    #doctorFilterGroup .btn {
        margin: 2px 0;
        width: 100%;
    }
}

/* Custom Tooltip Styling */
.tooltip {
    font-family: 'Cairo', sans-serif;
    font-size: 0.85rem;
    z-index: 9999;
}

.tooltip .tooltip-inner {
    background-color: rgba(33, 37, 41, 0.95);
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 12px;
    max-width: 280px;
    text-align: center;
    line-height: 1.4;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Dark mode tooltip styling */
.dark .tooltip .tooltip-inner {
    background-color: rgba(248, 250, 252, 0.95);
    color: #1e293b;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
}

/* Tooltip arrow styling */
.tooltip .tooltip-arrow::before {
    border-top-color: rgba(33, 37, 41, 0.95) !important;
    border-bottom-color: rgba(33, 37, 41, 0.95) !important;
    border-left-color: rgba(33, 37, 41, 0.95) !important;
    border-right-color: rgba(33, 37, 41, 0.95) !important;
}

.dark .tooltip .tooltip-arrow::before {
    border-top-color: rgba(248, 250, 252, 0.95) !important;
    border-bottom-color: rgba(248, 250, 252, 0.95) !important;
    border-left-color: rgba(248, 250, 252, 0.95) !important;
    border-right-color: rgba(248, 250, 252, 0.95) !important;
}

/* Improved button hover states with tooltips */
.btn:hover[data-bs-toggle="tooltip"] {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* §24 — outline tooltip hovers: sec-style.css */

h1, h2, h3, h4, h5, h6 {
color: var(--text) !important;
}

/* Modal centering — backdrop/z-index: sec-style.css + modal-kit.css */
.modal {
    align-items: center;
    justify-content: center;
    padding: 1rem !important;
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

/* §24 — btn-outline-warning: sec-style.css */
</style>

<script>
// Check for openModal query parameter and open the corresponding modal
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const openModal = urlParams.get('openModal');
    
    if (openModal === 'dailyBalance') {
        // Open daily balance modal
        setTimeout(() => {
            const dailyBalanceModal = document.getElementById('dailyBalanceModal');
            if (dailyBalanceModal) {
                const modal = new bootstrap.Modal(dailyBalanceModal);
                modal.show();
            }
        }, 100);
    } else if (openModal === 'expense') {
        // Open expense modal
        setTimeout(() => {
            const expenseModal = document.getElementById('expenseModal');
            if (expenseModal) {
                const modal = new bootstrap.Modal(expenseModal);
                modal.show();
            }
        }, 100);
    }
});

</script>