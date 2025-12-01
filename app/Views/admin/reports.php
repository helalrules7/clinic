<?php
/**
 * Admin Reports Template
 * قالب تقارير الإدارة
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    System Reports
                </h5>
            </div>
            <div class="card-body">
                <!-- Report Filters -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <section class="field menu">
                            <label for="reportType" id="reportType-label" class="form-label">Report Type</label>
                            <div class="control">
                                <select id="reportType" required hidden>
                                    <option selected disabled hidden>Select an option</option>
                                    <option value="revenue" data-option="revenue" <?= ($reportType ?? '') === 'revenue' ? 'selected' : '' ?>>Revenue Report</option>
                                    <option value="appointments" data-option="appointments" <?= ($reportType ?? '') === 'appointments' ? 'selected' : '' ?>>Appointments Report</option>
                                    <option value="patients" data-option="patients" <?= ($reportType ?? '') === 'patients' ? 'selected' : '' ?>>Patients Report</option>
                                    <option value="doctors" data-option="doctors" <?= ($reportType ?? '') === 'doctors' ? 'selected' : '' ?>>Doctors Report</option>
                                </select>
                                
                                <button id="reportType-toggle" aria-hidden aria-describedby="reportType-label" aria-controls="reportType-menu" aria-expanded="false">
                                    <?php 
                                    $selectedText = 'Select an option';
                                    if (($reportType ?? '') === 'revenue') $selectedText = 'Revenue Report';
                                    elseif (($reportType ?? '') === 'appointments') $selectedText = 'Appointments Report';
                                    elseif (($reportType ?? '') === 'patients') $selectedText = 'Patients Report';
                                    elseif (($reportType ?? '') === 'doctors') $selectedText = 'Doctors Report';
                                    echo htmlspecialchars($selectedText);
                                    ?>
                                </button>
                                
                                <menu id="reportType-menu">
                                    <li data-option="revenue" tabindex="0" role="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                        <h3>Revenue Report</h3>
                                        <p>View financial revenue data</p>
                                    </li>
                                    
                                    <li data-option="appointments" tabindex="0" role="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                                        </svg>
                                        <h3>Appointments Report</h3>
                                        <p>View appointments statistics</p>
                                    </li>
                                    
                                    <li data-option="patients" tabindex="0" role="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                        </svg>
                                        <h3>Patients Report</h3>
                                        <p>View patients statistics</p>
                                    </li>
                                    
                                    <li data-option="doctors" tabindex="0" role="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                        <h3>Doctors Report</h3>
                                        <p>View doctors performance</p>
                                    </li>
                                </menu>
                            </div>
                        </section>
                    </div>
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="startDate" name="start_date" 
                               value="<?= $startDate ?? date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="endDate" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="endDate" name="end_date" 
                               value="<?= $endDate ?? date('Y-m-t') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button class="btn btn-primary" onclick="generateReport()">
                                <i class="fas fa-search me-2"></i>
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Export Options:</h6>
                            <div>
                                <button class="btn btn-success me-2" onclick="exportReport('csv')">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Export CSV
                                </button>
                                <button class="btn btn-info" onclick="exportReport('pdf')">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Content -->
                <div id="reportContent">
                    <?php if (!empty($reportData)): ?>
                        <?php if ($reportType === 'revenue'): ?>
                            <!-- Revenue Report -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Daily Revenue</th>
                                            <th>Number of Transactions</th>
                                            <th>Discounts</th>
                                            <th>Net Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                                                <td><?= number_format($row['daily_revenue'], 2) ?> EGP</td>
                                                <td><?= number_format($row['transactions']) ?></td>
                                                <td><?= number_format($row['discounts'], 2) ?> EGP</td>
                                                <td><?= number_format($row['daily_revenue'] - $row['discounts'], 2) ?> EGP</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total</th>
                                            <th><?= number_format(array_sum(array_column($reportData, 'daily_revenue')), 2) ?> EGP</th>
                                            <th><?= number_format(array_sum(array_column($reportData, 'transactions'))) ?></th>
                                            <th><?= number_format(array_sum(array_column($reportData, 'discounts')), 2) ?> EGP</th>
                                            <th><?= number_format(array_sum(array_column($reportData, 'daily_revenue')) - array_sum(array_column($reportData, 'discounts')), 2) ?> EGP</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        <?php elseif ($reportType === 'appointments'): ?>
                            <!-- Appointments Report -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Appointments</th>
                                            <th>Completed</th>
                                            <th>Cancelled</th>
                                            <th>No Show</th>
                                            <th>Completion Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <?php 
                                            $completionRate = $row['total_appointments'] > 0 
                                                ? round(($row['completed'] / $row['total_appointments']) * 100, 1) 
                                                : 0;
                                            ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                                                <td><?= number_format($row['total_appointments']) ?></td>
                                                <td><span class="badge bg-success"><?= number_format($row['completed']) ?></span></td>
                                                <td><span class="badge bg-danger"><?= number_format($row['cancelled']) ?></span></td>
                                                <td><span class="badge bg-warning"><?= number_format($row['no_show']) ?></span></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" style="width: <?= $completionRate ?>%">
                                                            <?= $completionRate ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php elseif ($reportType === 'patients'): ?>
                            <!-- Patients Report -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>New Patients</th>
                                            <th>Male</th>
                                            <th>Female</th>
                                            <th>Male</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <?php 
                                            $malePercentage = $row['new_patients'] > 0 
                                                ? round(($row['male'] / $row['new_patients']) * 100, 1) 
                                                : 0;
                                            ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                                                <td><?= number_format($row['new_patients']) ?></td>
                                                <td><span class="badge bg-primary"><?= number_format($row['male']) ?></span></td>
                                                <td><span class="badge bg-pink"><?= number_format($row['female']) ?></span></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-primary" style="width: <?= $malePercentage ?>%">
                                                            <?= $malePercentage ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php elseif ($reportType === 'doctors'): ?>
                            <!-- Doctors Report -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Doctor Name</th>
                                            <th>Total Appointments</th>
                                            <th>Completed</th>
                                            <th>Cancelled</th>
                                            <th>Completion Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['display_name']) ?></td>
                                                <td><?= number_format($row['total_appointments']) ?></td>
                                                <td><span class="badge bg-success"><?= number_format($row['completed']) ?></span></td>
                                                <td><span class="badge bg-danger"><?= number_format($row['cancelled']) ?></span></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" style="width: <?= $row['completion_rate'] ?>%">
                                                            <?= number_format($row['completion_rate'], 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No data to display</h5>
                            <p class="text-muted">Select report type and date range to display data</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<?php if (!empty($reportData)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Charts
                </h5>
            </div>
            <div class="card-body">
                <canvas id="reportChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Dark mode variables */
:root {
    --bg: #1a1a1a;
    --bg-alt: #2d2d2d;
    --bg-dark: #1e1e1e;
    --text: #ffffff;
    --text-muted: #b0b0b0;
    --accent: #0d6efd;
    --accent-rgb: 13, 110, 253;
    --border: #404040;
    --muted: #6c757d;
}

[data-bs-theme="light"] {
    --bg: #ffffff;
    --bg-alt: #f8f9fa;
    --bg-dark: #ffffff;
    --text: #212529;
    --text-muted: #6c757d;
    --accent: #0d6efd;
    --accent-rgb: 13, 110, 253;
    --border: #dee2e6;
    --muted: #6c757d;
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

.table th {
    border-top: none;
    font-weight: 600;
}

.badge.bg-pink {
    background-color: #e91e63 !important;
}

.progress {
    border-radius: 10px;
    background-color: var(--bg-alt);
}

.progress-bar {
    border-radius: 10px;
}

.text-muted {
    color: var(--muted) !important;
}

.btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-outline-success {
    color: #28a745;
    border-color: #28a745;
}

.btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-outline-info {
    color: #17a2b8;
    border-color: #17a2b8;
}

.btn-outline-info:hover {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.btn-primary {
    background-color: var(--accent);
    border-color: var(--accent);
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white;
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

.form-select {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.form-select:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.form-label {
    color: var(--text);
    font-weight: 500;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function updateReportForm() {
    const reportType = document.getElementById('reportType').value;
    // يمكن إضافة منطق إضافي هنا لتحديث النموذج حسب نوع التقرير
}

function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    const url = new URL(window.location);
    url.searchParams.set('type', reportType);
    url.searchParams.set('start_date', startDate);
    url.searchParams.set('end_date', endDate);
    
    window.location.href = url.toString();
}

function exportReport(format) {
    const reportType = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    const url = `/admin/reports/export?type=${reportType}&start_date=${startDate}&end_date=${endDate}&format=${format}`;
    window.open(url, '_blank');
}

// Chart rendering
<?php if (!empty($reportData)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reportChart').getContext('2d');
    const reportType = '<?= $reportType ?? 'revenue' ?>';
    const reportData = <?= json_encode($reportData) ?>;
    
    let chartData, chartLabels, chartTitle;
    
    switch(reportType) {
        case 'revenue':
            chartLabels = reportData.map(row => row.date);
            chartData = reportData.map(row => parseFloat(row.daily_revenue));
            chartTitle = 'Daily Revenue';
            break;
        case 'appointments':
            chartLabels = reportData.map(row => row.date);
            chartData = reportData.map(row => parseInt(row.total_appointments));
            chartTitle = 'Daily Appointments';
            break;
        case 'patients':
            chartLabels = reportData.map(row => row.date);
            chartData = reportData.map(row => parseInt(row.new_patients));
            chartTitle = 'New Patients';
            break;
        case 'doctors':
            chartLabels = reportData.map(row => row.display_name);
            chartData = reportData.map(row => parseInt(row.total_appointments));
            chartTitle = 'Doctors Appointments';
            break;
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: chartTitle,
                data: chartData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: chartTitle
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
<?php endif; ?>
</script>
