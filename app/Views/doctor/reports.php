<?php
// Doctor Reports View
$reportTypes = [
    'appointments' => 'Appointments Reports',
    'patients' => 'Patients Reports', 
    'revenue' => 'Revenue Reports'
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
        <form method="GET" action="/doctor/reports">
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
        $totalAppointments = array_sum(array_column($reportData, 'total_appointments'));
        $totalCompleted = array_sum(array_column($reportData, 'completed'));
        $totalCancelled = array_sum(array_column($reportData, 'cancelled'));
        $totalNoShow = array_sum(array_column($reportData, 'no_show'));
        $completionRate = $totalAppointments > 0 ? round(($totalCompleted / $totalAppointments) * 100, 1) : 0;
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
                        <div class="stat-label">Completed Appointments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $completionRate ?>%</div>
                        <div class="stat-label">Completion Rate</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($totalCancelled) ?></div>
                        <div class="stat-label">Cancelled Appointments</div>
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
    <?php endif; ?>

    <!-- Detailed Table Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Detailed Report Data
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <?php if ($reportType === 'appointments'): ?>
                                <th>Total Appointments</th>
                                <th>Completed</th>
                                <th>Cancelled</th>
                                <th>No Show</th>
                            <?php elseif ($reportType === 'revenue'): ?>
                                <th>Daily Revenue</th>
                                <th>Total Transactions</th>
                                <th>Discounts</th>
                            <?php elseif ($reportType === 'patients'): ?>
                                <th>New Patients</th>
                                <th>Male Patients</th>
                                <th>Female Patients</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= date('M j, Y', strtotime($row['date'])) ?></strong>
                                </td>
                                <?php if ($reportType === 'appointments'): ?>
                                    <td><?= number_format($row['total_appointments']) ?></td>
                                    <td><span class="badge bg-success"><?= number_format($row['completed']) ?></span></td>
                                    <td><span class="badge bg-danger"><?= number_format($row['cancelled']) ?></span></td>
                                    <td><span class="badge bg-warning text-dark"><?= number_format($row['no_show']) ?></span></td>
                                <?php elseif ($reportType === 'revenue'): ?>
                                    <td><strong><?= number_format($row['daily_revenue'], 2) ?> EGP</strong></td>
                                    <td><?= number_format($row['transactions']) ?></td>
                                    <td><?= number_format($row['discounts'], 2) ?> EGP</td>
                                <?php elseif ($reportType === 'patients'): ?>
                                    <td><?= number_format($row['new_patients']) ?></td>
                                    <td><span class="badge bg-primary"><?= number_format($row['male']) ?></span></td>
                                    <td><span class="badge bg-info"><?= number_format($row['female']) ?></span></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

<script>
// Auto-submit form when dates change
document.getElementById('start_date').addEventListener('change', function() {
    if (document.getElementById('end_date').value) {
        document.querySelector('form').submit();
    }
});

document.getElementById('end_date').addEventListener('change', function() {
    if (document.getElementById('start_date').value) {
        document.querySelector('form').submit();
    }
});

// Validate date range
document.querySelector('form').addEventListener('submit', function(e) {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    
    if (startDate > endDate) {
        e.preventDefault();
        alert('Start date must be before end date');
    }
});
</script>
