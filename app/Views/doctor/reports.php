<?php
// Doctor Reports View
$reportTypes = [
    'appointments' => 'Appointments Reports',
    'patients' => 'Patients Reports', 
    'revenue' => 'Revenue Reports',
    'medical_prescriptions' => 'Medical Prescriptions Reports',
    'glasses_prescriptions' => 'Glasses Prescriptions Reports'
];
?>

<style>
/* Dark Mode Support */
.card {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
    box-shadow: 0 4px 20px var(--shadow);
    border-radius: 15px;
    margin-bottom: 30px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow);
}

.card-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
    padding: 20px;
    border-bottom: 1px solid var(--border);
}

.card-body {
    background-color: var(--bg);
    color: var(--text);
    padding: 25px;
}

.filter-section {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
}

.form-row {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 5px;
    display: block;
}

.form-control {
    background-color: var(--bg);
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 10px 15px;
    font-size: 14px;
    color: var(--text);
    transition: all 0.3s ease;
}

.form-control:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.form-select {
    background-color: var(--bg);
    border: 2px solid var(--border);
    color: var(--text);
    border-radius: 8px;
}

.form-select:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background-color: var(--accent);
    color: white;
    border: 2px solid var(--accent);
}

.btn-primary:hover {
    background-color: var(--accent);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(var(--accent-rgb), 0.3);
}

.btn-success {
    background-color: var(--success);
    color: white;
    border: 2px solid var(--success);
}

.btn-success:hover {
    background-color: var(--success);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.table-responsive {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px var(--shadow);
}

.table {
    background-color: var(--bg);
    color: var(--text);
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table thead th {
    background-color: var(--bg-dark);
    color: var(--text);
    border: none;
    padding: 15px;
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid var(--border);
}

.table tbody td {
    background-color: var(--bg);
    border: none;
    border-bottom: 1px solid var(--border);
    padding: 15px;
    text-align: center;
    vertical-align: middle;
    color: var(--text);
}

.table tbody tr:hover {
    background-color: var(--bg-alt);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background-color: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px var(--shadow);
    border-left: 4px solid var(--accent);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px var(--shadow);
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--accent);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--muted);
    font-size: 0.9rem;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--muted);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
    color: var(--muted);
}

.empty-state h4 {
    color: var(--text);
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--muted);
}

.text-muted {
    color: var(--muted) !important;
}

.text-success {
    color: var(--success) !important;
}

.text-danger {
    color: var(--danger) !important;
}

.text-warning {
    color: var(--warning) !important;
}

/* Badge Styles */
.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: var(--success) !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

.badge.bg-info {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-warning {
    background-color: var(--warning) !important;
    color: #212529;
}

.badge.bg-danger {
    background-color: var(--danger) !important;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .form-group {
        min-width: 100%;
    }
    
    .card-body {
        padding: 15px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .filter-section {
        padding: 15px;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Table Dark Mode */
.table-dark th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

/* Chart Container Dark Mode Adjustments */
.dark .card .card-body canvas {
    background-color: var(--card) !important;
}

/* Chart Text Dark Mode */
.dark .card .card-header h6 {
    color: var(--text) !important;
}

/* Dark Mode Specific Adjustments */
.dark .card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.dark .card-header {
    background-color: var(--bg) !important;
    border-bottom-color: var(--border) !important;
}

.dark .card-body {
    background-color: var(--card) !important;
}

.dark .filter-section {
    background-color: var(--bg) !important;
    border-color: var(--border) !important;
}

.dark .table {
    background-color: var(--card) !important;
}

.dark .table-dark th {
    background-color: var(--bg-dark) !important;
    border-bottom-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .table thead th {
    background-color: var(--bg-dark) !important;
    border-bottom-color: var(--border) !important;
}

.dark .table tbody td {
    background-color: var(--card) !important;
    border-bottom-color: var(--border) !important;
}

.dark .table tbody tr:hover {
    background-color: var(--bg) !important;
}

.dark .stat-card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.dark .form-control {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .form-control:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
}

.dark .form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
}

.dark .form-label {
    color: var(--text) !important;
}

.dark .empty-state h4 {
    color: var(--text) !important;
}

.dark .empty-state p {
    color: var(--muted) !important;
}

.dark .empty-state i {
    color: var(--muted) !important;
}

/* Hide sidebar toggle on desktop for this page specifically */
@media (min-width: 993px) {
    .sidebar-toggle {
        display: none !important;
    }
}

/* Ensure sidebar toggle works normally on mobile */
@media (max-width: 992px) {
    .sidebar-toggle {
        display: flex !important;
    }
}

/* Quick Date Range Buttons */
.quick-date-btn {
    border-radius: 8px !important;
    border-width: 2px !important;
    transition: all 0.3s ease !important;
    font-weight: 500 !important;
    padding: 0.5rem 1rem !important;
}

.quick-date-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.quick-date-btn.active {
    background: linear-gradient(135deg, var(--accent), #0284c7) !important;
    border-color: var(--accent) !important;
    color: white !important;
}

.dark .quick-date-btn.active {
    background: linear-gradient(135deg, var(--accent), #0284c7) !important;
    color: white !important;
}

/* Chart Container Styles - Same as dashboard */
.chart-container {
    background-color: #ffffff !important;
    border-radius: 8px;
    padding: 1rem;
    transition: background-color 0.3s ease;
}

.dark .chart-container {
    background-color: #1e293b !important;
}

.card .card-body canvas {
    background-color: #ffffff !important;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.dark .card .card-body canvas {
    background-color: #1e293b !important;
}

/* Pagination Styles - Same as dashboard.php */
.pagination {
    margin-top: 1rem;
}

.page-link {
    color: var(--accent);
    background-color: var(--card);
    border-color: var(--border);
    transition: all 0.3s ease;
}

.page-link:hover {
    color: white;
    background-color: var(--accent);
    border-color: var(--accent);
    transform: translateY(-1px);
}

.page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.page-item.disabled .page-link {
    color: var(--muted);
    background-color: var(--card);
    border-color: var(--border);
    cursor: not-allowed;
    opacity: 0.6;
}

.dark .page-link {
    color: var(--accent);
    background-color: var(--card);
    border-color: var(--border);
}

.dark .page-link:hover {
    color: white;
    background-color: var(--accent);
    border-color: var(--accent);
}

.dark .page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.dark .page-item.disabled .page-link {
    color: var(--muted);
    background-color: var(--card);
    border-color: var(--border);
}
</style>

<!-- Report Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-funnel me-2"></i>
            Report Filters
        </h5>
    </div>
    <div class="card-body">
        <!-- Quick Date Range Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-outline-primary quick-date-btn active" data-range="month">
                        <i class="bi bi-calendar4 me-1"></i>
                        This Month
                    </button>
                    <button type="button" class="btn btn-outline-primary quick-date-btn" data-range="all">
                        <i class="bi bi-calendar-range me-1"></i>
                        All Time
                    </button>
                    <button type="button" class="btn btn-outline-primary quick-date-btn" data-range="quarter">
                        <i class="bi bi-calendar3 me-1"></i>
                        Last Quarter
                    </button>
                    <button type="button" class="btn btn-outline-primary quick-date-btn" data-range="6months">
                        <i class="bi bi-calendar-month me-1"></i>
                        Last 6 Months
                    </button>
                    <button type="button" class="btn btn-outline-primary quick-date-btn" data-range="year">
                        <i class="bi bi-calendar-year me-1"></i>
                        Last Year
                    </button>
                </div>
            </div>
        </div>
        
        <form method="GET" action="/doctor/reports" id="reportForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="type" class="form-label">Report Type:</label>
                    <select name="type" id="type" class="form-select">
                        <?php foreach ($reportTypes as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($reportType === $value) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">From Date:</label>
                    <input type="date" name="start_date" id="start_date" 
                           value="<?= htmlspecialchars($startDate) ?>" class="form-control">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">To Date:</label>
                    <input type="date" name="end_date" id="end_date" 
                           value="<?= htmlspecialchars($endDate) ?>" class="form-control">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            View Report
                        </button>
                        <a href="/doctor/reports/export?type=<?= urlencode($reportType) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&format=csv" 
                           class="btn btn-success">
                            <i class="bi bi-download me-1"></i>
                            Export CSV
                        </a>
                        <?php if (!empty($reportData)): ?>
                        <button type="button" class="btn btn-danger" id="exportPdfBtn">
                            <i class="bi bi-file-pdf me-1"></i>
                            Export PDF
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Content -->
<?php if (!empty($reportData)): ?>
    <!-- Summary Statistics -->
    <?php if ($reportType === 'appointments'): ?>
        <?php
        // Use the same calculation logic as dashboard.php
        $totalAppointments = array_sum(array_column($reportData, 'total_appointments'));
        $totalCompleted = array_sum(array_column($reportData, 'completed'));
        $totalMissed = array_sum(array_column($reportData, 'missed'));
        $completionRatio = $totalAppointments > 0 ? round(($totalCompleted / $totalAppointments) * 100, 2) : 0;
        ?>
        
        <!-- Appointments Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check me-2"></i>
                    Appointments Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalAppointments) ?></div>
                        <div class="stat-label">Total Appointments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalCompleted) ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalMissed) ?></div>
                        <div class="stat-label">Missed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $completionRatio ?>%</div>
                        <div class="stat-label">Completion Ratio</div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($reportType === 'revenue'): ?>
        <?php
        $totalRevenue = array_sum(array_column($reportData, 'daily_revenue'));
        $totalTransactions = array_sum(array_column($reportData, 'transactions'));
        $totalDiscounts = array_sum(array_column($reportData, 'discounts'));
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        ?>
        
        <!-- Revenue Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-cash-coin me-2"></i>
                    Revenue Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalRevenue, 2) ?> EGP</div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalTransactions) ?></div>
                        <div class="stat-label">Total Transactions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($avgTransaction, 2) ?> EGP</div>
                        <div class="stat-label">Average Transaction</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalDiscounts, 2) ?> EGP</div>
                        <div class="stat-label">Total Discounts</div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($reportType === 'patients'): ?>
        <?php
        $totalNewPatients = array_sum(array_column($reportData, 'new_patients'));
        $totalMale = array_sum(array_column($reportData, 'male'));
        $totalFemale = array_sum(array_column($reportData, 'female'));
        $malePercentage = $totalNewPatients > 0 ? round(($totalMale / $totalNewPatients) * 100, 1) : 0;
        ?>
        
        <!-- Patients Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Patients Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalNewPatients) ?></div>
                        <div class="stat-label">Total New Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalMale) ?></div>
                        <div class="stat-label">Male Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalFemale) ?></div>
                        <div class="stat-label">Female Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $malePercentage ?>%</div>
                        <div class="stat-label">Male Percentage</div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($reportType === 'medical_prescriptions'): ?>
        <?php
        $totalPrescriptions = array_sum(array_column($reportData, 'total_prescriptions'));
        $totalAppointments = array_sum(array_column($reportData, 'appointments_with_prescriptions'));
        $totalPatients = array_sum(array_column($reportData, 'patients_count'));
        $avgPerAppointment = $totalAppointments > 0 ? round($totalPrescriptions / $totalAppointments, 1) : 0;
        ?>
        
        <!-- Medical Prescriptions Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-prescription me-2"></i>
                    Medical Prescriptions Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalPrescriptions) ?></div>
                        <div class="stat-label">Total Prescriptions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalAppointments) ?></div>
                        <div class="stat-label">Appointments with Prescriptions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalPatients) ?></div>
                        <div class="stat-label">Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $avgPerAppointment ?></div>
                        <div class="stat-label">Avg per Appointment</div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($reportType === 'glasses_prescriptions'): ?>
        <?php
        $totalPrescriptions = array_sum(array_column($reportData, 'total_prescriptions'));
        $totalAppointments = array_sum(array_column($reportData, 'appointments_with_prescriptions'));
        $totalPatients = array_sum(array_column($reportData, 'patients_count'));
        $withLensType = array_sum(array_column($reportData, 'with_lens_type'));
        ?>
        
        <!-- Glasses Prescriptions Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-eye me-2"></i>
                    Glasses Prescriptions Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalPrescriptions) ?></div>
                        <div class="stat-label">Total Prescriptions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalAppointments) ?></div>
                        <div class="stat-label">Appointments with Prescriptions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalPatients) ?></div>
                        <div class="stat-label">Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($withLensType) ?></div>
                        <div class="stat-label">With Lens Type</div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($topMedications)): ?>
        <!-- Top Medications Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-capsule me-2"></i>
                    Most Used Medications
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Rank</th>
                                <th>Medication Name</th>
                                <th>Usage Count</th>
                                <th>Prescriptions</th>
                                <th>Patients</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topMedications as $index => $med): ?>
                            <tr>
                                <td><strong>#<?= $index + 1 ?></strong></td>
                                <td><strong><?= htmlspecialchars($med['drug_name']) ?></strong></td>
                                <td><span class="badge bg-primary"><?= number_format($med['usage_count']) ?></span></td>
                                <td><?= number_format($med['prescription_count']) ?></td>
                                <td><?= number_format($med['patient_count']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php elseif ($reportType === 'glasses_prescriptions'): ?>
        <?php
        $totalPrescriptions = array_sum(array_column($reportData, 'total_prescriptions'));
        $totalAppointments = array_sum(array_column($reportData, 'appointments_with_prescriptions'));
        $totalPatients = array_sum(array_column($reportData, 'patients_count'));
        $withLensType = array_sum(array_column($reportData, 'with_lens_type'));
        ?>

        <!-- Glasses Prescriptions Statistics Card -->
    <div class="card mb-4">
        <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-eye me-2"></i>
                    Glasses Prescriptions Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalPrescriptions) ?></div>
                        <div class="stat-label">Total Prescriptions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalAppointments) ?></div>
                        <div class="stat-label">Appointments with Prescriptions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalPatients) ?></div>
                        <div class="stat-label">Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($withLensType) ?></div>
                        <div class="stat-label">With Lens Type</div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($glassesLensTypeStats)): ?>
        <!-- Lens Type Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-eye me-2"></i>
                    Lens Type Distribution
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Lens Type</th>
                                <th>Count</th>
                                <th>Prescriptions</th>
                                <th>Patients</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($glassesLensTypeStats as $stat): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($stat['lens_type']) ?></strong></td>
                                <td><span class="badge bg-info"><?= number_format($stat['count']) ?></span></td>
                                <td><?= number_format($stat['prescription_count']) ?></td>
                                <td><?= number_format($stat['patient_count']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Detailed Table Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Detailed Report Data
            </h5>
            <div class="d-flex align-items-center gap-2">
                <label for="reportPerPage" class="form-label mb-0 text-muted" style="font-size: 0.875rem;">View:</label>
                <select class="form-select form-select-sm" id="reportPerPage" style="width: auto;">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="reportDataTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <?php if ($reportType === 'appointments'): ?>
                                <th>Total Appointments</th>
                                <th>Completed</th>
                                <th>Missed</th>
                            <?php elseif ($reportType === 'revenue'): ?>
                                <th>Daily Revenue</th>
                                <th>Total Transactions</th>
                                <th>Discounts</th>
                            <?php elseif ($reportType === 'patients'): ?>
                                <th>New Patients</th>
                                <th>Male Patients</th>
                                <th>Female Patients</th>
                            <?php elseif ($reportType === 'medical_prescriptions'): ?>
                                <th>Total Prescriptions</th>
                                <th>Appointments</th>
                                <th>Patients</th>
                                <th>Drugs List</th>
                            <?php elseif ($reportType === 'glasses_prescriptions'): ?>
                                <th>Total Prescriptions</th>
                                <th>Appointments</th>
                                <th>Patients</th>
                                <th>With Lens Type</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="reportDataTableBody">
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= date('M j, Y', strtotime($row['date'])) ?></strong>
                                </td>
                                <?php if ($reportType === 'appointments'): ?>
                                    <td><?= number_format($row['total_appointments']) ?></td>
                                    <td><span class="badge bg-success"><?= number_format($row['completed']) ?></span></td>
                                    <td><span class="badge bg-danger"><?= number_format($row['missed']) ?></span></td>
                                <?php elseif ($reportType === 'revenue'): ?>
                                    <td><strong><?= number_format($row['daily_revenue'], 2) ?> EGP</strong></td>
                                    <td><?= number_format($row['transactions']) ?></td>
                                    <td><?= number_format($row['discounts'], 2) ?> EGP</td>
                                <?php elseif ($reportType === 'patients'): ?>
                                    <td><?= number_format($row['new_patients']) ?></td>
                                    <td><span class="badge bg-primary"><?= number_format($row['male']) ?></span></td>
                                    <td><span class="badge bg-info"><?= number_format($row['female']) ?></span></td>
                                <?php elseif ($reportType === 'medical_prescriptions'): ?>
                                    <td><?= number_format($row['total_prescriptions']) ?></td>
                                    <td><?= number_format($row['appointments_with_prescriptions']) ?></td>
                                    <td><?= number_format($row['patients_count']) ?></td>
                                    <td><small><?= htmlspecialchars(substr($row['drugs_list'] ?? '', 0, 50)) ?><?= strlen($row['drugs_list'] ?? '') > 50 ? '...' : '' ?></small></td>
                                <?php elseif ($reportType === 'glasses_prescriptions'): ?>
                                    <td><?= number_format($row['total_prescriptions']) ?></td>
                                    <td><?= number_format($row['appointments_with_prescriptions']) ?></td>
                                    <td><?= number_format($row['patients_count']) ?></td>
                                    <td><?= number_format($row['with_lens_type']) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Report Data Pagination" id="reportPaginationNav" style="display: none; padding: 1rem;">
                <ul class="pagination justify-content-center mb-0" id="reportPaginationList">
                </ul>
            </nav>
    </div>
</div>

    <!-- Charts Section -->
    <div class="card mb-4" id="chartsSection">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-graph-up me-2"></i>
                Visual Analytics
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php if ($reportType === 'appointments'): ?>
                    <!-- Appointments Line Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="appointmentsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointments Status Pie Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Status</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="appointmentsPieChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($reportType === 'revenue'): ?>
                    <!-- Revenue Line Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Revenue Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue vs Discounts Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Revenue vs Discounts</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="revenuePieChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($reportType === 'patients'): ?>
                    <!-- Patients Line Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">New Patients Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="patientsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gender Distribution Pie Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Gender Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="genderPieChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($reportType === 'medical_prescriptions'): ?>
                    <!-- Medical Prescriptions Trend Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Prescriptions Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="medicalPrescriptionsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Medications Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Top Medications</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="topMedicationsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($reportType === 'glasses_prescriptions'): ?>
                    <!-- Glasses Prescriptions Trend Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Prescriptions Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="glassesPrescriptionsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lens Type Distribution Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Lens Type Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="lensTypeChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Empty State Card -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <i class="bi bi-chart-line text-muted" style="font-size: 4rem;"></i>
                <h4>No data found for the selected period</h4>
                <p class="text-muted">Try changing the date range or report type</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- jsPDF and html2canvas for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
// Quick Date Range Buttons Handler
document.querySelectorAll('.quick-date-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const range = this.getAttribute('data-range');
        const today = new Date();
        let startDate, endDate;
        
        // Remove active class from all buttons
        document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
        // Add active class to clicked button
        this.classList.add('active');
        
        switch(range) {
            case 'month':
                // This month - first day of current month
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = today;
                break;
            case 'all':
                // All time - set to a very early date
                startDate = new Date('2000-01-01');
                endDate = today;
                break;
            case 'quarter':
                // Last quarter (3 months)
                startDate = new Date(today);
                startDate.setMonth(today.getMonth() - 3);
                endDate = today;
                break;
            case '6months':
                // Last 6 months
                startDate = new Date(today);
                startDate.setMonth(today.getMonth() - 6);
                endDate = today;
                break;
            case 'year':
                // Last year
                startDate = new Date(today);
                startDate.setFullYear(today.getFullYear() - 1);
                endDate = today;
                break;
            default:
                return;
        }
        
        // Format dates as YYYY-MM-DD
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };
        
        // Update form inputs
        document.getElementById('start_date').value = formatDate(startDate);
        document.getElementById('end_date').value = formatDate(endDate);
        
        // Submit form
        document.getElementById('reportForm').submit();
    });
});

// Set active button on page load based on current dates
(function() {
    // First, remove active class from all buttons
    document.querySelectorAll('.quick-date-btn').forEach(btn => btn.classList.remove('active'));
    
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        endDate.setHours(0, 0, 0, 0);
        startDate.setHours(0, 0, 0, 0);
        
        // Check if end date is today (or very close)
        const daysDiff = Math.floor((today - endDate) / (1000 * 60 * 60 * 24));
        
        // Check if start date is very early (All Time - before 2000-01-01 or very old)
        const allTimeThreshold = new Date('2000-01-01');
        allTimeThreshold.setHours(0, 0, 0, 0);
        const isAllTime = startDate.getTime() <= allTimeThreshold.getTime() && daysDiff <= 1;
        
        if (isAllTime) {
            // All Time is selected
            document.querySelector('.quick-date-btn[data-range="all"]')?.classList.add('active');
        } else if (daysDiff <= 1) {
            // End date is today, check which range matches
            const monthsDiff = (today.getFullYear() - startDate.getFullYear()) * 12 + (today.getMonth() - startDate.getMonth());
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            firstDayOfMonth.setHours(0, 0, 0, 0);
            
            // Check if it's this month (default)
            if (startDate.getTime() === firstDayOfMonth.getTime()) {
                document.querySelector('.quick-date-btn[data-range="month"]')?.classList.add('active');
            } else if (monthsDiff <= 3) {
                document.querySelector('.quick-date-btn[data-range="quarter"]')?.classList.add('active');
            } else if (monthsDiff <= 6) {
                document.querySelector('.quick-date-btn[data-range="6months"]')?.classList.add('active');
            } else if (monthsDiff <= 12) {
                document.querySelector('.quick-date-btn[data-range="year"]')?.classList.add('active');
            } else {
                // If it's more than a year but not All Time, default to All Time
                document.querySelector('.quick-date-btn[data-range="all"]')?.classList.add('active');
            }
        } else {
            // Default to this month if dates don't match any range
            document.querySelector('.quick-date-btn[data-range="month"]')?.classList.add('active');
        }
    } else {
        // If no dates set, default to this month
        document.querySelector('.quick-date-btn[data-range="month"]')?.classList.add('active');
    }
})();

// Auto-submit form when dates change
document.getElementById('start_date').addEventListener('change', function() {
    // Remove active class from all quick date buttons
    document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
    
    if (document.getElementById('end_date').value) {
        document.getElementById('reportForm').submit();
    }
});

document.getElementById('end_date').addEventListener('change', function() {
    // Remove active class from all quick date buttons
    document.querySelectorAll('.quick-date-btn').forEach(b => b.classList.remove('active'));
    
    if (document.getElementById('start_date').value) {
        document.getElementById('reportForm').submit();
    }
});

// Validate date range
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    
    if (startDate > endDate) {
        e.preventDefault();
        alert('Start date must be before end date');
    }
});

// Chart.js Configuration
const chartColors = {
    primary: '#007bff',
    success: '#28a745',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#17a2b8',
    secondary: '#6c757d',
    light: '#f8f9fa',
    dark: '#343a40'
};

// Dark mode colors
const darkModeColors = {
    primary: '#0d6efd',
    success: '#198754',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#0dcaf0',
    secondary: '#6c757d',
    light: '#f8f9fa',
    dark: '#212529'
};

// Get current theme colors
function getThemeColors() {
    return document.body.classList.contains('dark') ? darkModeColors : chartColors;
}

// Chart.js default configuration
if (Chart.defaults && Chart.defaults.font) {
Chart.defaults.font.family = "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
}

// Update Chart.js defaults based on theme
function updateChartDefaults() {
    const themeColors = getCurrentThemeColors();
    if (Chart.defaults) {
        Chart.defaults.color = themeColors.text;
    }
}

// Initialize defaults
updateChartDefaults();

// Get current theme colors dynamically - same as dashboard.php
function getCurrentThemeColors() {
    const isDark = document.documentElement.classList.contains('dark');
    
    if (isDark) {
    return {
            text: '#ffffff',
            muted: '#ffffff',
            grid: 'rgba(255, 255, 255, 0.15)',
            border: 'rgba(255, 255, 255, 0.3)',
            background: '#1e293b',
            tooltipBg: 'rgba(0, 0, 0, 0.95)',
            tooltipText: '#ffffff'
        };
    } else {
        return {
            text: '#0f172a',
            muted: '#475569',
            grid: 'rgba(0, 0, 0, 0.1)',
            border: 'rgba(0, 0, 0, 0.2)',
            background: '#ffffff',
            tooltipBg: 'rgba(255, 255, 255, 0.95)',
            tooltipText: '#0f172a'
    };
    }
}

<?php if (!empty($reportData)): ?>
// Prepare data for charts
const reportData = <?= json_encode($reportData) ?>;
const reportType = '<?= $reportType ?>';

// Common chart options
function getCommonOptions() {
    const themeColors = getCurrentThemeColors();
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12,
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    },
                    color: themeColors.text
                }
            },
            tooltip: {
                backgroundColor: themeColors.tooltipBg,
                titleColor: themeColors.tooltipText,
                bodyColor: themeColors.tooltipText,
                borderColor: themeColors.border,
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: true,
                titleFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 12
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: themeColors.grid,
                    drawBorder: false
                },
                ticks: {
                    color: themeColors.text,
                    font: {
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    }
                }
            },
            y: {
                grid: {
                    color: themeColors.grid,
                    drawBorder: false
                },
                ticks: {
                    color: themeColors.text,
                    font: {
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    }
                }
            }
        }
    };
}

// Pie chart options
function getPieOptions() {
    const themeColors = getCurrentThemeColors();
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12,
                        family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    },
                    color: themeColors.text
                }
            },
            tooltip: {
                backgroundColor: themeColors.tooltipBg,
                titleColor: themeColors.tooltipText,
                bodyColor: themeColors.tooltipText,
                borderColor: themeColors.border,
                borderWidth: 1,
                cornerRadius: 8,
                titleFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                    size: 12
                },
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    };
}

// Store chart instances for theme updates
window.chartInstances = {
    appointmentsChart: null,
    appointmentsPieChart: null,
    revenueChart: null,
    revenuePieChart: null,
    patientsChart: null,
    genderPieChart: null,
    medicalPrescriptionsChart: null,
    topMedicationsChart: null,
    glassesPrescriptionsChart: null,
    lensTypeChart: null
};

<?php if ($reportType === 'appointments'): ?>
// Appointments Line Chart - Same as dashboard.php
const appointmentsCtx = document.getElementById('appointmentsChart');
if (appointmentsCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.appointmentsChart) {
        window.chartInstances.appointmentsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const totalAppointments = reportData.map(item => item.total_appointments || 0);
    const completed = reportData.map(item => item.completed || 0);
    const missed = reportData.map(item => item.missed || 0);
    
    window.chartInstances.appointmentsChart = new Chart(appointmentsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Total Appointments',
                    data: totalAppointments,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Completed',
                    data: completed,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Missed',
                    data: missed,
                    borderColor: '#ef4444',
                    backgroundColor: '#ef4444' + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}

// Appointments Status Pie Chart - Same as dashboard.php
const appointmentsPieCtx = document.getElementById('appointmentsPieChart');
if (appointmentsPieCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.appointmentsPieChart) {
        window.chartInstances.appointmentsPieChart.destroy();
    }
    
    const totalCompleted = <?= $totalCompleted ?>;
    const totalMissed = <?= $totalMissed ?>;
    
    window.chartInstances.appointmentsPieChart = new Chart(appointmentsPieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Missed'],
            datasets: [{
                data: [totalCompleted, totalMissed],
                backgroundColor: [
                    chartColors.success,
                    chartColors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: getPieOptions()
    });
}

<?php elseif ($reportType === 'revenue'): ?>
// Revenue Line Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.revenueChart) {
        window.chartInstances.revenueChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const dailyRevenue = reportData.map(item => item.daily_revenue);
    const discounts = reportData.map(item => item.discounts);
    
    window.chartInstances.revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('ar-EG', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Daily Revenue (EGP)',
                    data: dailyRevenue,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Discounts (EGP)',
                    data: discounts,
                    borderColor: chartColors.danger,
                    backgroundColor: chartColors.danger + '20',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            ...getCommonOptions(),
            scales: {
                ...getCommonOptions().scales,
                y: {
                    ...getCommonOptions().scales.y,
                    beginAtZero: true,
                    ticks: {
                        ...getCommonOptions().scales.y.ticks,
                        callback: function(value) {
                            return value.toLocaleString('ar-EG') + ' EGP';
                        }
                    }
                }
            }
        }
    });
}

// Revenue vs Discounts Pie Chart
const revenuePieCtx = document.getElementById('revenuePieChart');
if (revenuePieCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.revenuePieChart) {
        window.chartInstances.revenuePieChart.destroy();
    }
    
    const totalRevenue = <?= $totalRevenue ?>;
    const totalDiscounts = <?= $totalDiscounts ?>;
    
    window.chartInstances.revenuePieChart = new Chart(revenuePieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Revenue', 'Discounts'],
            datasets: [{
                data: [totalRevenue, totalDiscounts],
                backgroundColor: [
                    chartColors.success,
                    chartColors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            ...getPieOptions(),
            plugins: {
                ...getPieOptions().plugins,
                tooltip: {
                    ...getPieOptions().plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            return `${label}: ${value.toLocaleString('ar-EG')} EGP`;
                        }
                    }
                }
            }
        }
    });
}

<?php elseif ($reportType === 'patients'): ?>
// Patients Line Chart
const patientsCtx = document.getElementById('patientsChart');
if (patientsCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.patientsChart) {
        window.chartInstances.patientsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const newPatients = reportData.map(item => item.new_patients);
    const malePatients = reportData.map(item => item.male);
    const femalePatients = reportData.map(item => item.female);
    
    window.chartInstances.patientsChart = new Chart(patientsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('ar-EG', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'New Patients',
                    data: newPatients,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Male Patients',
                    data: malePatients,
                    borderColor: chartColors.info,
                    backgroundColor: chartColors.info + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Female Patients',
                    data: femalePatients,
                    borderColor: chartColors.warning,
                    backgroundColor: chartColors.warning + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}

// Gender Distribution Pie Chart
const genderPieCtx = document.getElementById('genderPieChart');
if (genderPieCtx) {
    // Destroy existing chart if it exists
    if (window.chartInstances.genderPieChart) {
        window.chartInstances.genderPieChart.destroy();
    }
    
    const totalMale = <?= $totalMale ?>;
    const totalFemale = <?= $totalFemale ?>;
    
    window.chartInstances.genderPieChart = new Chart(genderPieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [totalMale, totalFemale],
                backgroundColor: [
                    chartColors.info,
                    chartColors.warning
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: getPieOptions()
    });
}

<?php elseif ($reportType === 'medical_prescriptions'): ?>
// Medical Prescriptions Trend Chart
const medicalPrescriptionsCtx = document.getElementById('medicalPrescriptionsChart');
if (medicalPrescriptionsCtx) {
    if (window.chartInstances.medicalPrescriptionsChart) {
        window.chartInstances.medicalPrescriptionsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const totalPrescriptions = reportData.map(item => item.total_prescriptions || 0);
    const appointmentsWithPrescriptions = reportData.map(item => item.appointments_with_prescriptions || 0);
    const patientsCount = reportData.map(item => item.patients_count || 0);
    
    window.chartInstances.medicalPrescriptionsChart = new Chart(medicalPrescriptionsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Total Prescriptions',
                    data: totalPrescriptions,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Appointments with Prescriptions',
                    data: appointmentsWithPrescriptions,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Patients',
                    data: patientsCount,
                    borderColor: chartColors.info,
                    backgroundColor: chartColors.info + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}

// Top Medications Chart
const topMedicationsCtx = document.getElementById('topMedicationsChart');
if (topMedicationsCtx) {
    if (window.chartInstances.topMedicationsChart) {
        window.chartInstances.topMedicationsChart.destroy();
    }
    
    const topMedications = <?= json_encode($topMedications ?? []) ?>;
    if (topMedications.length > 0) {
        const medicationNames = topMedications.map(m => m.drug_name.length > 15 ? m.drug_name.substring(0, 15) + '...' : m.drug_name);
        const usageCounts = topMedications.map(m => parseInt(m.usage_count || 0));
        
        window.chartInstances.topMedicationsChart = new Chart(topMedicationsCtx, {
            type: 'doughnut',
            data: {
                labels: medicationNames,
                datasets: [{
                    data: usageCounts,
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.success,
                        chartColors.info,
                        chartColors.warning,
                        chartColors.danger,
                        '#6c757d',
                        '#17a2b8',
                        '#ffc107',
                        '#28a745',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: getPieOptions()
        });
    }
}

<?php elseif ($reportType === 'glasses_prescriptions'): ?>
// Glasses Prescriptions Trend Chart
const glassesPrescriptionsCtx = document.getElementById('glassesPrescriptionsChart');
if (glassesPrescriptionsCtx) {
    if (window.chartInstances.glassesPrescriptionsChart) {
        window.chartInstances.glassesPrescriptionsChart.destroy();
    }
    
    const dates = reportData.map(item => item.date);
    const totalPrescriptions = reportData.map(item => item.total_prescriptions || 0);
    const appointmentsWithPrescriptions = reportData.map(item => item.appointments_with_prescriptions || 0);
    const patientsCount = reportData.map(item => item.patients_count || 0);
    const withLensType = reportData.map(item => item.with_lens_type || 0);
    
    window.chartInstances.glassesPrescriptionsChart = new Chart(glassesPrescriptionsCtx, {
        type: 'line',
        data: {
            labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            })),
            datasets: [
                {
                    label: 'Total Prescriptions',
                    data: totalPrescriptions,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Appointments with Prescriptions',
                    data: appointmentsWithPrescriptions,
                    borderColor: chartColors.success,
                    backgroundColor: chartColors.success + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Patients',
                    data: patientsCount,
                    borderColor: chartColors.info,
                    backgroundColor: chartColors.info + '20',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'With Lens Type',
                    data: withLensType,
                    borderColor: chartColors.warning,
                    backgroundColor: chartColors.warning + '20',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: getCommonOptions()
    });
}

// Lens Type Distribution Chart
const lensTypeCtx = document.getElementById('lensTypeChart');
if (lensTypeCtx) {
    if (window.chartInstances.lensTypeChart) {
        window.chartInstances.lensTypeChart.destroy();
    }
    
    const glassesLensTypeStats = <?= json_encode($glassesLensTypeStats ?? []) ?>;
    if (glassesLensTypeStats.length > 0) {
        const lensTypes = glassesLensTypeStats.map(s => s.lens_type);
        const counts = glassesLensTypeStats.map(s => parseInt(s.count || 0));
        
        window.chartInstances.lensTypeChart = new Chart(lensTypeCtx, {
            type: 'doughnut',
            data: {
                labels: lensTypes,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.success,
                        chartColors.info,
                        chartColors.warning,
                        chartColors.danger,
                        '#6c757d',
                        '#17a2b8',
                        '#ffc107'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: getPieOptions()
        });
    }
}

<?php endif; ?>

// Function to update charts when theme changes - same as dashboard.php
function updateChartsTheme() {
    updateChartDefaults();
    
    // Update chart container background
    document.querySelectorAll('.card-body canvas').forEach(canvas => {
        const container = canvas.closest('.card-body');
        if (container) {
    const themeColors = getCurrentThemeColors();
            container.style.backgroundColor = themeColors.background;
        }
    });
    
    // Reload charts by destroying and recreating them
    const themeColors = getCurrentThemeColors();
    
    // Update all chart instances
    Object.keys(window.chartInstances).forEach(key => {
        const chart = window.chartInstances[key];
        if (chart) {
            // Update chart options
        if (chart.options && chart.options.plugins) {
            if (chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = themeColors.text;
            }
                if (chart.options.plugins.tooltip) {
                    chart.options.plugins.tooltip.backgroundColor = themeColors.tooltipBg;
                    chart.options.plugins.tooltip.titleColor = themeColors.tooltipText;
                    chart.options.plugins.tooltip.bodyColor = themeColors.tooltipText;
                }
            if (chart.options.scales) {
                Object.keys(chart.options.scales).forEach(scaleKey => {
                    const scale = chart.options.scales[scaleKey];
                    if (scale.ticks) {
                        scale.ticks.color = themeColors.text;
                    }
                    if (scale.grid) {
                        scale.grid.color = themeColors.grid;
                    }
                });
            }
        }
        chart.update();
        }
    });
}

// Setup theme change listener - same as dashboard.php
function setupThemeListener() {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            setTimeout(() => {
                updateChartsTheme();
            }, 100);
        });
    }
    
    // Also listen for class changes on documentElement
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                setTimeout(() => {
                    updateChartsTheme();
                }, 100);
        }
    });
});

    observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});
}

// Initialize theme listener
setupThemeListener();

<?php endif; ?>

// Report Data Pagination
<?php if (!empty($reportData)): ?>
const reportDataArray = <?= json_encode($reportData) ?>;
const reportTypeStr = '<?= $reportType ?>';

let reportCurrentPage = 1;
let reportPerPage = 20;

// Initialize report pagination
function initializeReportPagination() {
    const perPageSelect = document.getElementById('reportPerPage');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            reportPerPage = this.value === 'all' ? 'all' : parseInt(this.value);
            reportCurrentPage = 1;
            renderReportTable();
            renderReportPagination();
        });
    }
    
    renderReportTable();
    renderReportPagination();
}

// Render report table with pagination
function renderReportTable() {
    const tbody = document.getElementById('reportDataTableBody');
    if (!tbody) return;
    
    const startIndex = reportPerPage === 'all' ? 0 : (reportCurrentPage - 1) * reportPerPage;
    const endIndex = reportPerPage === 'all' ? reportDataArray.length : startIndex + reportPerPage;
    const currentData = reportDataArray.slice(startIndex, endIndex);
    
    let html = '';
    currentData.forEach(row => {
        const date = new Date(row.date);
        const formattedDate = date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
        
        html += '<tr>';
        html += `<td><strong>${formattedDate}</strong></td>`;
        
        if (reportTypeStr === 'appointments') {
            html += `<td>${parseInt(row.total_appointments || 0).toLocaleString()}</td>`;
            html += `<td><span class="badge bg-success">${parseInt(row.completed || 0).toLocaleString()}</span></td>`;
            html += `<td><span class="badge bg-danger">${parseInt(row.missed || 0).toLocaleString()}</span></td>`;
        } else if (reportTypeStr === 'revenue') {
            html += `<td><strong>${parseFloat(row.daily_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} EGP</strong></td>`;
            html += `<td>${parseInt(row.transactions || 0).toLocaleString()}</td>`;
            html += `<td>${parseFloat(row.discounts || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} EGP</td>`;
        } else if (reportTypeStr === 'patients') {
            html += `<td>${parseInt(row.new_patients || 0).toLocaleString()}</td>`;
            html += `<td><span class="badge bg-primary">${parseInt(row.male || 0).toLocaleString()}</span></td>`;
            html += `<td><span class="badge bg-info">${parseInt(row.female || 0).toLocaleString()}</span></td>`;
        } else if (reportTypeStr === 'medical_prescriptions') {
            html += `<td>${parseInt(row.total_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.appointments_with_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.patients_count || 0).toLocaleString()}</td>`;
            const drugsList = (row.drugs_list || '').substring(0, 50);
            html += `<td><small>${drugsList}${(row.drugs_list || '').length > 50 ? '...' : ''}</small></td>`;
        } else if (reportTypeStr === 'glasses_prescriptions') {
            html += `<td>${parseInt(row.total_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.appointments_with_prescriptions || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.patients_count || 0).toLocaleString()}</td>`;
            html += `<td>${parseInt(row.with_lens_type || 0).toLocaleString()}</td>`;
        }
        
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

// Render pagination
function renderReportPagination() {
    const paginationNav = document.getElementById('reportPaginationNav');
    const paginationList = document.getElementById('reportPaginationList');
    
    if (!paginationNav || !paginationList) return;
    
    if (reportPerPage === 'all' || reportDataArray.length <= reportPerPage) {
        paginationNav.style.display = 'none';
        paginationList.innerHTML = '';
        return;
    }
    
    const totalPages = Math.ceil(reportDataArray.length / reportPerPage);
    
    if (totalPages <= 1) {
        paginationNav.style.display = 'none';
        paginationList.innerHTML = '';
        return;
    }
    
    paginationNav.style.display = 'block';
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${reportCurrentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${reportCurrentPage - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, reportCurrentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === reportCurrentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${totalPages}); return false;">${totalPages}</a></li>`;
    }
    
    // Next button
    html += `
        <li class="page-item ${reportCurrentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); changeReportPage(${reportCurrentPage + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationList.innerHTML = html;
}

// Change report page
function changeReportPage(page) {
    const totalPages = Math.ceil(reportDataArray.length / reportPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    reportCurrentPage = page;
    renderReportTable();
    renderReportPagination();
    
    // Scroll to table
    document.getElementById('reportDataTable').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

// Make function global
window.changeReportPage = changeReportPage;

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeReportPagination();
    });
} else {
    initializeReportPagination();
}

<?php endif; ?>

// PDF Export Functionality
<?php if (!empty($reportData)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function() {
            exportToPDF();
        });
    }
});

async function exportToPDF() {
    const btn = document.getElementById('exportPdfBtn');
    const originalText = btn.innerHTML;
    
    // Store original display states of controls
    const hiddenElements = [];
    
    try {
        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating PDF...';
        
        // Hide all controls (dropdowns, buttons, etc.) before export
        const controlsToHide = document.querySelectorAll('select, button, .quick-date-btn, .card-header .d-flex');
        controlsToHide.forEach(el => {
            if (el.style.display !== 'none') {
                hiddenElements.push({ element: el, display: el.style.display });
                el.style.display = 'none';
            }
        });
        
        // Wait for charts to be fully rendered
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 10;
        const contentWidth = pageWidth - (margin * 2);
        let yPosition = margin;
        
        // Helper function to add a new page if needed
        function checkPageBreak(neededHeight) {
            if (yPosition + neededHeight > pageHeight - margin) {
                pdf.addPage();
                yPosition = margin;
                return true;
            }
            return false;
        }
        
        // Add title
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        const reportTypeTitle = '<?php
            if ($reportType === 'appointments') {
                echo 'Appointments';
            } elseif ($reportType === 'revenue') {
                echo 'Revenue';
            } elseif ($reportType === 'patients') {
                echo 'Patients';
            } elseif ($reportType === 'medical_prescriptions') {
                echo 'Medical Prescriptions';
            } elseif ($reportType === 'glasses_prescriptions') {
                echo 'Glasses Prescriptions';
            } else {
                echo 'Report';
            }
        ?> Report';
        pdf.text(reportTypeTitle, pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 10;
        
        // Add date range
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'normal');
        const dateRange = '<?= date('M j, Y', strtotime($startDate)) ?> - <?= date('M j, Y', strtotime($endDate)) ?>';
        pdf.text('Date Range: ' + dateRange, pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 10;
        
        pdf.text('Generated: ' + new Date().toLocaleString(), pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 15;
        
        // Export Summary Statistics Card
        const summaryCard = document.querySelector('.stats-grid')?.closest('.card');
        if (summaryCard) {
            checkPageBreak(60);
            
            // Add section title as text
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'bold');
            const summaryTitle = summaryCard.querySelector('.card-header h5')?.textContent?.trim() || 'Summary Statistics';
            pdf.text(summaryTitle, margin, yPosition);
            yPosition += 8;
            
            const summaryCanvas = await html2canvas(summaryCard.querySelector('.card-body'), {
                backgroundColor: null,
                scale: 2,
                logging: false,
                useCORS: true,
                removeContainer: false
            });
            const summaryImg = summaryCanvas.toDataURL('image/png');
            const imgWidth = contentWidth;
            const imgHeight = (summaryCanvas.height * imgWidth) / summaryCanvas.width;
            checkPageBreak(imgHeight);
            pdf.addImage(summaryImg, 'PNG', margin, yPosition, imgWidth, imgHeight);
            yPosition += imgHeight + 10;
        }
        
        // Export Charts
        const chartsSection = document.getElementById('chartsSection');
        if (chartsSection) {
            // Add section title
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Visual Analytics', margin, yPosition);
            yPosition += 10;
            
            // Find all chart containers
            const chartContainers = chartsSection.querySelectorAll('canvas');
            
            for (let i = 0; i < chartContainers.length; i++) {
                const chartCard = chartContainers[i].closest('.card');
                if (chartCard) {
                    checkPageBreak(80);
                    
                    // Get chart title from card header
                    const chartTitle = chartCard.querySelector('.card-header h6')?.textContent?.trim() || 'Chart';
                    pdf.setFontSize(12);
                    pdf.setFont('helvetica', 'bold');
                    pdf.text(chartTitle, margin, yPosition);
                    yPosition += 6;
                    
                    // Capture only the chart body (canvas area)
                    const chartBody = chartCard.querySelector('.card-body');
                    if (chartBody) {
                        const chartCanvas = await html2canvas(chartBody, {
                            backgroundColor: null,
                            scale: 2,
                            logging: false,
                            useCORS: true,
                            allowTaint: true,
                            removeContainer: false
                        });
                        
                        const chartImg = chartCanvas.toDataURL('image/png');
                        const imgWidth = contentWidth;
                        const imgHeight = (chartCanvas.height * imgWidth) / chartCanvas.width;
                        
                        checkPageBreak(imgHeight);
                        pdf.addImage(chartImg, 'PNG', margin, yPosition, imgWidth, imgHeight);
                        yPosition += imgHeight + 10;
                    }
                }
            }
        }
        
        // Export Detailed Table - Build table manually to include all data and repeated headers
        const tableCard = document.getElementById('reportDataTable')?.closest('.card');
        if (tableCard) {
            checkPageBreak(60);
            
            // Add table title as text
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Detailed Report Data', margin, yPosition);
            yPosition += 8;
            
            // Get all data (not paginated) and report type
            const allData = reportDataArray;
            const reportTypeStr = '<?= $reportType ?>';
            if (allData && allData.length > 0) {
                // Get table headers
                const tableHeaders = [];
                const thead = tableCard.querySelector('thead tr');
                if (thead) {
                    thead.querySelectorAll('th').forEach(th => {
                        tableHeaders.push(th.textContent.trim());
                    });
                }
                
                // Calculate row height
                const rowHeight = 8;
                const headerHeight = 10;
                const footerHeight = 15; // Reserve space for footer
                const maxHeightPerPage = pageHeight - margin - yPosition - footerHeight;
                let currentPageStartRow = 0;
                
                // Function to add footer to current page
                function addFooter() {
                    const footerY = pageHeight - 10;
                    pdf.setFontSize(8);
                    pdf.setFont('helvetica', 'normal');
                    const clinicName = '<?= htmlspecialchars($clinicName ?? 'Clinic', ENT_QUOTES) ?>';
                    const doctorName = '<?= htmlspecialchars($doctorName ?? 'Doctor', ENT_QUOTES) ?>';
                    pdf.text(clinicName, margin, footerY);
                    pdf.text(`Exported by: ${doctorName}`, pageWidth - margin, footerY, { align: 'right' });
                }
                
                // Function to draw table header
                function drawTableHeader(startY) {
                    pdf.setFontSize(10);
                    pdf.setFont('helvetica', 'bold');
                    let xPos = margin;
                    const colWidths = [];
                    
                    // Calculate column widths based on content
                    if (reportTypeStr === 'appointments') {
                        colWidths.push(40, 35, 30, 30);
                    } else if (reportTypeStr === 'revenue') {
                        colWidths.push(40, 40, 35, 30);
                    } else if (reportTypeStr === 'patients') {
                        colWidths.push(40, 35, 30, 30);
                    } else if (reportTypeStr === 'medical_prescriptions') {
                        colWidths.push(30, 30, 25, 60);
                    } else if (reportTypeStr === 'glasses_prescriptions') {
                        colWidths.push(30, 30, 25, 30);
                    } else {
                        // Default equal widths
                        const defaultWidth = contentWidth / tableHeaders.length;
                        tableHeaders.forEach(() => colWidths.push(defaultWidth));
                    }
                    
                    // Draw header background and text
                    pdf.setFillColor(51, 51, 51);
                    pdf.rect(margin, startY - 5, contentWidth, headerHeight, 'F');
                    pdf.setTextColor(255, 255, 255);
                    
                    tableHeaders.forEach((header, idx) => {
                        if (xPos + colWidths[idx] <= pageWidth - margin) {
                            pdf.text(header, xPos, startY);
                            xPos += colWidths[idx];
                        }
                    });
                    
                    pdf.setTextColor(0, 0, 0);
                    return startY + headerHeight;
                }
                
                // Draw first header
                yPosition = drawTableHeader(yPosition);
                
                // Draw table rows
                pdf.setFontSize(9);
                pdf.setFont('helvetica', 'normal');
                
                for (let i = 0; i < allData.length; i++) {
                    const row = allData[i];
                    
                    // Check if we need a new page
                    if (yPosition + rowHeight > pageHeight - footerHeight) {
                        addFooter();
                        pdf.addPage();
                        yPosition = margin;
                        // Draw header again on new page
                        yPosition = drawTableHeader(yPosition);
                    }
                    
                    // Format date
                    const date = new Date(row.date);
                    const formattedDate = date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric',
                        year: 'numeric'
                    });
                    
                    let xPos = margin;
                    const colWidths = [];
                    
                    if (reportTypeStr === 'appointments') {
                        colWidths.push(40, 35, 30, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.total_appointments || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.completed || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.missed || 0).toLocaleString(), xPos, yPosition);
                    } else if (reportTypeStr === 'revenue') {
                        colWidths.push(40, 40, 35, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseFloat(row.daily_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' EGP', xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.transactions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseFloat(row.discounts || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' EGP', xPos, yPosition);
                    } else if (reportTypeStr === 'patients') {
                        colWidths.push(40, 35, 30, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.new_patients || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.male || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.female || 0).toLocaleString(), xPos, yPosition);
                    } else if (reportTypeStr === 'medical_prescriptions') {
                        colWidths.push(30, 30, 25, 60);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.total_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.appointments_with_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.patients_count || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[3];
                        const drugsList = (row.drugs_list || '').substring(0, 40);
                        pdf.text(drugsList + ((row.drugs_list || '').length > 40 ? '...' : ''), xPos, yPosition);
                    } else if (reportTypeStr === 'glasses_prescriptions') {
                        colWidths.push(30, 30, 25, 30);
                        pdf.text(formattedDate, xPos, yPosition);
                        xPos += colWidths[0];
                        pdf.text(parseInt(row.total_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[1];
                        pdf.text(parseInt(row.appointments_with_prescriptions || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[2];
                        pdf.text(parseInt(row.patients_count || 0).toLocaleString(), xPos, yPosition);
                        xPos += colWidths[3];
                        pdf.text(parseInt(row.with_lens_type || 0).toLocaleString(), xPos, yPosition);
                    }
                    
                    // Draw row border
                    pdf.setDrawColor(200, 200, 200);
                    pdf.line(margin, yPosition + 2, pageWidth - margin, yPosition + 2);
                    
                    yPosition += rowHeight;
                }
                
                // Add footer to last page after all rows are drawn
                addFooter();
            }
        }
        
        // Ensure footer is added to all pages (in case some pages were added without footer)
        const totalPages = pdf.internal.getNumberOfPages();
        const clinicName = '<?= htmlspecialchars($clinicName ?? 'Clinic', ENT_QUOTES) ?>';
        const doctorName = '<?= htmlspecialchars($doctorName ?? 'Doctor', ENT_QUOTES) ?>';
        
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            const footerY = pageHeight - 10;
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'normal');
            pdf.text(clinicName, margin, footerY);
            pdf.text(`Exported by: ${doctorName}`, pageWidth - margin, footerY, { align: 'right' });
            pdf.text(`Page ${i} of ${totalPages}`, pageWidth / 2, footerY, { align: 'center' });
        }
        
        // Generate filename
        const reportType = '<?= $reportType ?>';
        const filename = `${reportType}_report_<?= date('Y-m-d', strtotime($startDate)) ?>_to_<?= date('Y-m-d', strtotime($endDate)) ?>.pdf`;
        
        // Save PDF
        pdf.save(filename);
        
        // Restore hidden elements
        hiddenElements.forEach(item => {
            item.element.style.display = item.display || '';
        });
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = originalText;
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        
        // Restore hidden elements even on error
        hiddenElements.forEach(item => {
            item.element.style.display = item.display || '';
        });
        
        alert('Error generating PDF. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

<?php endif; ?>
</script>

