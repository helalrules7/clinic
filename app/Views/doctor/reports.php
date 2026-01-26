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

<!-- Reports CSS -->
<link href="/app/Views/doctor/assets/css/reports.css?v=<?= file_exists(__DIR__ . '/assets/css/reports.css') ? filemtime(__DIR__ . '/assets/css/reports.css') : time() ?>" rel="stylesheet">

<div class="reports-page">
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
                    <section class="field menu">
                        <label for="type" id="type-label" class="form-label">Report Type:</label>
                        <div class="control">
                            <select name="type" id="type" required class="d-none">
                                <?php foreach ($reportTypes as $value => $label): ?>
                                    <option value="<?= $value ?>" data-option="<?= $value ?>" <?= ($reportType === $value) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <button type="button" id="type-toggle" aria-hidden aria-describedby="type-label" aria-controls="type-menu" aria-expanded="false">
                                <?php 
                                    echo isset($reportTypes[$reportType]) ? $reportTypes[$reportType] : 'Select Report Type';
                                ?>
                            </button>
                            
                            <menu id="type-menu">
                                <li data-option="appointments" tabindex="0" role="button" class="<?= $reportType === 'appointments' ? 'selected' : '' ?>">
                                    <i class="bi bi-calendar-check fs-5"></i>
                                    <h3>Appointments Reports</h3>
                                    <p>View appointments statistics</p>
                                </li>
                                <li data-option="patients" tabindex="0" role="button" class="<?= $reportType === 'patients' ? 'selected' : '' ?>">
                                    <i class="bi bi-people fs-5"></i>
                                    <h3>Patients Reports</h3>
                                    <p>View new and returning patients</p>
                                </li>
                                <li data-option="revenue" tabindex="0" role="button" class="<?= $reportType === 'revenue' ? 'selected' : '' ?>">
                                    <i class="bi bi-cash-coin fs-5"></i>
                                    <h3>Revenue Reports</h3>
                                    <p>View financial revenue data</p>
                                </li>
                                <li data-option="medical_prescriptions" tabindex="0" role="button" class="<?= $reportType === 'medical_prescriptions' ? 'selected' : '' ?>">
                                    <i class="bi bi-prescription fs-5"></i>
                                    <h3>Medical Prescriptions</h3>
                                    <p>View prescriptions analytics</p>
                                </li>
                                <li data-option="glasses_prescriptions" tabindex="0" role="button" class="<?= $reportType === 'glasses_prescriptions' ? 'selected' : '' ?>">
                                    <i class="bi bi-eyeglasses fs-5"></i>
                                    <h3>Glasses Prescriptions</h3>
                                    <p>View glasses prescriptions stats</p>
                                </li>
                            </menu>
                        </div>
                    </section>
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
            <div class="card-header appointments-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check me-2"></i>
                    Appointments Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="mini-stats-grid">
                    <div class="mini-stat-card mini-stat-primary">
                        <div class="mini-stat-icon">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalAppointments) ?></span>
                            <span class="mini-stat-label">Total Appointments</span>
                        </div>
                        <div class="mini-stat-chart" id="chartTotalAppointments"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>All Time</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-success">
                        <div class="mini-stat-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalCompleted) ?></span>
                            <span class="mini-stat-label">Completed</span>
                        </div>
                        <div class="mini-stat-chart" id="chartCompleted"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-activity"></i>
                            <span><?= $completionRatio ?>%</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-warning">
                        <div class="mini-stat-icon">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalMissed) ?></span>
                            <span class="mini-stat-label">Missed</span>
                        </div>
                        <div class="mini-stat-chart" id="chartMissed"></div>
                        <div class="mini-stat-trend trend-down">
                            <i class="bi bi-graph-down-arrow"></i>
                            <span><?= round((100 - $completionRatio), 1) ?>%</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-info">
                        <div class="mini-stat-icon">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= $completionRatio ?>%</span>
                            <span class="mini-stat-label">Completion Ratio</span>
                        </div>
                        <div class="mini-stat-chart" id="chartCompletionRatio"></div>
                        <div class="mini-stat-trend trend-neutral">
                            <i class="bi bi-pie-chart"></i>
                            <span>Ratio</span>
                        </div>
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
            <div class="card-header revenue-header">
                <h5 class="mb-0">
                    <i class="bi bi-cash-coin me-2"></i>
                    Revenue Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="mini-stats-grid">
                    <div class="mini-stat-card mini-stat-success">
                        <div class="mini-stat-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalRevenue, 2) ?></span>
                            <span class="mini-stat-label">Total Revenue (EGP)</span>
                        </div>
                        <div class="mini-stat-chart" id="chartTotalRevenue"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Revenue</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-primary">
                        <div class="mini-stat-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalTransactions) ?></span>
                            <span class="mini-stat-label">Total Transactions</span>
                        </div>
                        <div class="mini-stat-chart" id="chartTotalTransactions"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-activity"></i>
                            <span>Transactions</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-info">
                        <div class="mini-stat-icon">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($avgTransaction, 2) ?></span>
                            <span class="mini-stat-label">Avg Transaction (EGP)</span>
                        </div>
                        <div class="mini-stat-chart" id="chartAvgTransaction"></div>
                        <div class="mini-stat-trend trend-neutral">
                            <i class="bi bi-pie-chart"></i>
                            <span>Average</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-warning">
                        <div class="mini-stat-icon">
                            <i class="bi bi-tag"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalDiscounts, 2) ?></span>
                            <span class="mini-stat-label">Total Discounts (EGP)</span>
                        </div>
                        <div class="mini-stat-chart" id="chartTotalDiscounts"></div>
                        <div class="mini-stat-trend trend-down">
                            <i class="bi bi-graph-down-arrow"></i>
                            <span>Discounts</span>
                        </div>
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
            <div class="card-header patients-header">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Patients Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="mini-stats-grid">
                    <div class="mini-stat-card mini-stat-primary">
                        <div class="mini-stat-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalNewPatients) ?></span>
                            <span class="mini-stat-label">Total New Patients</span>
                        </div>
                        <div class="mini-stat-chart" id="chartTotalNewPatients"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>New Patients</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-male">
                        <div class="mini-stat-icon">
                            <i class="bi bi-gender-male"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalMale) ?></span>
                            <span class="mini-stat-label">Male Patients</span>
                        </div>
                        <div class="mini-stat-chart" id="chartMalePatients"></div>
                        <div class="mini-stat-trend trend-neutral">
                            <i class="bi bi-pie-chart"></i>
                            <span><?= $malePercentage ?>%</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-female">
                        <div class="mini-stat-icon">
                            <i class="bi bi-gender-female"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalFemale) ?></span>
                            <span class="mini-stat-label">Female Patients</span>
                        </div>
                        <div class="mini-stat-chart" id="chartFemalePatients"></div>
                        <div class="mini-stat-trend trend-neutral">
                            <i class="bi bi-pie-chart"></i>
                            <span><?= round((100 - $malePercentage), 1) ?>%</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-info">
                        <div class="mini-stat-icon">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= $malePercentage ?>%</span>
                            <span class="mini-stat-label">Male Percentage</span>
                        </div>
                        <div class="mini-stat-chart" id="chartMalePercentage"></div>
                        <div class="mini-stat-trend trend-neutral">
                            <i class="bi bi-pie-chart"></i>
                            <span>Percentage</span>
                        </div>
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
            <div class="card-header medical-prescriptions-header">
                <h5 class="mb-0">
                    <i class="bi bi-prescription me-2"></i>
                    Medical Prescriptions Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="mini-stats-grid">
                    <div class="mini-stat-card mini-stat-warning">
                        <div class="mini-stat-icon">
                            <i class="bi bi-prescription"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalPrescriptions) ?></span>
                            <span class="mini-stat-label">Total Prescriptions</span>
                        </div>
                        <div class="mini-stat-chart" id="chartTotalPrescriptions"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Prescriptions</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-primary">
                        <div class="mini-stat-icon">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalAppointments) ?></span>
                            <span class="mini-stat-label">Appointments with Rx</span>
                        </div>
                        <div class="mini-stat-chart" id="chartAppointmentsWithRx"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-activity"></i>
                            <span>Appointments</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-success">
                        <div class="mini-stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalPatients) ?></span>
                            <span class="mini-stat-label">Patients</span>
                        </div>
                        <div class="mini-stat-chart" id="chartPatientsCount"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Patients</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-info">
                        <div class="mini-stat-icon">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= $avgPerAppointment ?></span>
                            <span class="mini-stat-label">Avg per Appointment</span>
                        </div>
                        <div class="mini-stat-chart" id="chartAvgPerAppointment"></div>
                        <div class="mini-stat-trend trend-neutral">
                            <i class="bi bi-pie-chart"></i>
                            <span>Average</span>
                        </div>
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
            <div class="card-header glasses-prescriptions-header">
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
                <div class="mini-stats-grid">
                    <div class="mini-stat-card mini-stat-purple">
                        <div class="mini-stat-icon">
                            <i class="bi bi-eyeglasses"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalPrescriptions) ?></span>
                            <span class="mini-stat-label">Total Prescriptions</span>
                        </div>
                        <div class="mini-stat-chart" id="chartGlassesTotalPrescriptions2"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Prescriptions</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-primary">
                        <div class="mini-stat-icon">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalAppointments) ?></span>
                            <span class="mini-stat-label">Appointments with Rx</span>
                        </div>
                        <div class="mini-stat-chart" id="chartGlassesAppointments2"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-activity"></i>
                            <span>Appointments</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-success">
                        <div class="mini-stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($totalPatients) ?></span>
                            <span class="mini-stat-label">Patients</span>
                        </div>
                        <div class="mini-stat-chart" id="chartGlassesPatients2"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Patients</span>
                        </div>
                    </div>
                    <div class="mini-stat-card mini-stat-info">
                        <div class="mini-stat-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="mini-stat-content">
                            <span class="mini-stat-value"><?= number_format($withLensType) ?></span>
                            <span class="mini-stat-label">With Lens Type</span>
                        </div>
                        <div class="mini-stat-chart" id="chartWithLensType2"></div>
                        <div class="mini-stat-trend trend-up">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Lens Type</span>
                        </div>
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
                <label for="reportPerPage" class="form-label mb-0 me-2 text small">View:</label>
                <section class="field menu" style="min-width: 70px; width: auto;">
                    <div class="control">
                        <select class="form-select form-select-sm d-none center-select" id="reportPerPage" style="width: auto;">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">All</option>
                        </select>
                        <button type="button" class="custom-select-toggle" aria-expanded="false" style="font-size: 0.875rem; padding: 0.75rem 2rem 0.75rem 0.75rem;">20</button>
                        <menu>
                            <li data-option="10" tabindex="0" role="button"><h3>10</h3></li>
                            <li data-option="20" tabindex="0" role="button" class="selected"><h3>20</h3></li>
                            <li data-option="50" tabindex="0" role="button"><h3>50</h3></li>
                            <li data-option="100" tabindex="0" role="button"><h3>100</h3></li>
                            <li data-option="all" tabindex="0" role="button"><h3>All</h3></li>
                        </menu>
                    </div>
                </section>
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
                <h4 style="color: var(--text) !important;">No data found for the selected period</h4>
                <p class="text-muted" style="color: var(--text) !important;">Try changing the date range or report type</p>
            </div>
        </div>
    </div>
<?php endif; ?>
</div> <!-- End reports-page -->

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- jsPDF and html2canvas for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- Initialize REPORTS_CONFIG with PHP variables -->
<script>
    // Initialize REPORTS_CONFIG with PHP variables
    <?php
    ?>

    window.REPORTS_CONFIG = {
        reportType: '<?= $reportType ?>',
        reportData: <?= isset($reportData) ? json_encode($reportData) : '[]' ?>,
        startDate: '<?= $startDate ?>',
        endDate: '<?= $endDate ?>',
        clinicName: <?= isset($clinicName) ? json_encode($clinicName, JSON_UNESCAPED_UNICODE) : 'null' ?>,
        doctorName: <?= isset($doctorName) ? json_encode($doctorName, JSON_UNESCAPED_UNICODE) : 'null' ?>,
        // Appointments
        totalCompleted: <?= isset($totalCompleted) ? (int)$totalCompleted : 0 ?>,
        totalMissed: <?= isset($totalMissed) ? (int)$totalMissed : 0 ?>,
        // Revenue
        totalRevenue: <?= isset($totalRevenue) ? (float)$totalRevenue : 0 ?>,
        totalDiscounts: <?= isset($totalDiscounts) ? (float)$totalDiscounts : 0 ?>,
        // Patients
        totalMale: <?= isset($totalMale) ? (int)$totalMale : 0 ?>,
        totalFemale: <?= isset($totalFemale) ? (int)$totalFemale : 0 ?>,
        // Prescriptions
        totalPrescriptions: <?= isset($totalPrescriptions) ? (int)$totalPrescriptions : 0 ?>,
        totalAppointments: <?= isset($totalAppointments) ? (int)$totalAppointments : 0 ?>,
        totalPatients: <?= isset($totalPatients) ? (int)$totalPatients : 0 ?>,
        withLensType: <?= isset($withLensType) ? (int)$withLensType : 0 ?>,
        // Additional data
        topMedications: <?= isset($topMedications) ? json_encode($topMedications) : '[]' ?>,
        glassesLensTypeStats: <?= isset($glassesLensTypeStats) ? json_encode($glassesLensTypeStats) : '[]' ?>
    };
</script>

<script src="/app/Views/doctor/assets/js/reports.js?v=<?= file_exists(__DIR__ . '/assets/js/reports.js') ? filemtime(__DIR__ . '/assets/js/reports.js') : time() ?>"></script>