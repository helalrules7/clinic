<!-- Payments Header -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-credit-card me-2"></i>
                إدارة المدفوعات والرصيد اليومي
            </h4>
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

<!-- Daily Balance Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="openingBalance"><?= $dailyBalance['opening_balance'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">الرصيد الافتتاحي</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="totalReceived"><?= $dailyBalance['total_received'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">إجمالي المستلم</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-danger">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="totalExpenses"><?= $dailyBalance['total_expenses'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">إجمالي المصروفات</p>
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
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-number" id="currentBalance"><?= $dailyBalance['current_balance'] ?? 0 ?></h3>
                        <p class="stat-label arabic-text">الرصيد الحالي</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Types Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 arabic-text">
                    <i class="bi bi-pie-chart me-2"></i>
                    ملخص المدفوعات حسب النوع
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="payment-type-icon bg-primary me-3">
                                <i class="bi bi-calendar-plus"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 arabic-text">حجز جديد</h6>
                                <span class="text-success fw-bold"><?= $paymentTypes['new_booking'] ?? 0 ?> جنيه</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="payment-type-icon bg-info me-3">
                                <i class="bi bi-arrow-clockwise"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 arabic-text">إعادة كشف</h6>
                                <span class="text-success fw-bold"><?= $paymentTypes['followup'] ?? 0 ?> جنيه</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="payment-type-icon bg-warning me-3">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 arabic-text">استشارة طبية</h6>
                                <span class="text-success fw-bold"><?= $paymentTypes['consultation'] ?? 0 ?> جنيه</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="payment-type-icon bg-success me-3">
                                <i class="bi bi-activity"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 arabic-text">إجراء طبي</h6>
                                <span class="text-success fw-bold"><?= $paymentTypes['procedure'] ?? 0 ?> جنيه</span>
                            </div>
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
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Export to Excel -->
                    <button class="btn btn-success btn-sm" onclick="exportToExcel()" title="تصدير إلى Excel">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        تصدير Excel
                    </button>
                    <!-- Date Filter -->
                    <div class="d-flex align-items-center">
                        <label for="dateFilter" class="form-label mb-0 me-2 text-muted arabic-text">التاريخ:</label>
                        <input type="date" class="form-control form-control-sm" id="dateFilter" style="width: auto;">
                    </div>
                    <!-- Transaction Type Filter -->
                    <div class="d-flex align-items-center">
                        <label for="transactionTypeFilter" class="form-label mb-0 me-2 text-muted arabic-text">النوع:</label>
                        <select class="form-select form-select-sm" id="transactionTypeFilter" style="width: auto;">
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
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text">التاريخ</th>
                        <th class="arabic-text">النوع</th>
                        <th class="arabic-text">الوصف</th>
                        <th class="arabic-text">المبلغ</th>
                        <th class="arabic-text">الرصيد</th>
                        <th class="arabic-text">الإجراءات</th>
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
                    <!-- Filter by type -->
                    <div class="d-flex align-items-center">
                        <label for="typeFilter" class="form-label mb-0 me-2 text-muted arabic-text">النوع:</label>
                        <select class="form-select form-select-sm" id="typeFilter" style="width: auto;">
                            <option value="all" class="arabic-text">الكل</option>
                            <option value="new_booking" class="arabic-text">حجز جديد</option>
                            <option value="followup" class="arabic-text">إعادة كشف</option>
                            <option value="consultation" class="arabic-text">استشارة طبية</option>
                            <option value="procedure" class="arabic-text">إجراء طبي</option>
                        </select>
                    </div>
                    <!-- Filter by payment method -->
                    <div class="d-flex align-items-center">
                        <label for="methodFilter" class="form-label mb-0 me-2 text-muted arabic-text">طريقة الدفع:</label>
                        <select class="form-select form-select-sm" id="methodFilter" style="width: auto;">
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
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text">التاريخ</th>
                        <th class="arabic-text">المريض</th>
                        <th class="arabic-text">المبلغ</th>
                        <th class="arabic-text">النوع</th>
                        <th class="arabic-text">طريقة الدفع</th>
                        <th class="arabic-text">الوصف</th>
                        <th class="arabic-text">الإجراءات</th>
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
                                            <div class="fw-semibold arabic-text"><?= $payment['patient_name'] ?? 'غير محدد' ?></div>
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

<!-- Daily Balance Modal -->
<div class="modal fade" id="dailyBalanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-wallet2 me-2"></i>
                    تسجيل الرصيد اليومي
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-dash-circle me-2"></i>
                    تسجيل مصروف جديد
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-lock me-2"></i>
                    إغلاق اليوم
                </h5>
                <div class="keyboard-hint">
                    <span class="arabic-text">اضغط</span>
                    <kbd>Esc</kbd>
                    <span class="arabic-text">للإغلاق</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                e.preventDefault();
                bootstrap.Modal.getInstance(openModal).hide();
            }
        }
    });
    
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

// Update dashboard cards with fresh data
function updateDashboardCards() {
    fetch('/api/dashboard-summary', {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Update daily balance cards
                if (data.data.dailyBalance) {
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
                
                // Update payment types summary
                if (data.data.paymentTypes) {
                    Object.keys(data.data.paymentTypes).forEach(type => {
                        const element = document.getElementById(type + 'Count');
                        if (element) {
                            element.textContent = data.data.paymentTypes[type].count;
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error updating dashboard cards:', error);
        });
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

.payment-type-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
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