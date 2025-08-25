<!-- Patients Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary">
            <i class="bi bi-people me-2"></i>
            Patient Records
        </h4>
        <p class="text-muted mb-0">Manage and view patient information</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
            <i class="bi bi-search me-2"></i>
            Search Patients
        </button>
    </div>
</div>

<!-- Patients Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count($patients) ?></h3>
                <p class="text-muted mb-0">Total Patients</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= array_sum(array_column($patients, 'total_appointments')) ?></h3>
                <p class="text-muted mb-0">Total Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-calendar-week text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($patients, fn($p) => $p['last_visit'] && date('Y-m-d', strtotime($p['last_visit'])) >= date('Y-m-d', strtotime('-7 days')))) ?></h3>
                <p class="text-muted mb-0">Recent Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-person-plus text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1"><?= count(array_filter($patients, fn($p) => date('Y-m-d', strtotime($p['created_at'])) >= date('Y-m-d', strtotime('-30 days')))) ?></h3>
                <p class="text-muted mb-0">New This Month</p>
            </div>
        </div>
    </div>
</div>

<!-- Patients Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>
            Patient List
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Patient Info</th>
                        <th>Contact</th>
                        <th>Age</th>
                        <th>Last Visit</th>
                        <th>Total Visits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">No patients found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h6>
                                            <small class="text-muted">ID: #<?= $patient['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="bi bi-telephone me-1"></i>
                                        <?= htmlspecialchars($patient['phone'] ?? 'N/A') ?>
                                    </div>
                                    <?php if (!empty($patient['alt_phone'])): ?>
                                        <div class="mt-1">
                                            <i class="bi bi-telephone-plus me-1"></i>
                                            <small class="text-muted"><?= htmlspecialchars($patient['alt_phone']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($patient['dob']): ?>
                                        <?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> years
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($patient['last_visit']): ?>
                                        <span class="badge bg-success">
                                            <?= date('M j, Y', strtotime($patient['last_visit'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= $patient['total_appointments'] ?></span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/doctor/patients/<?= $patient['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-success" onclick="bookAppointment(<?= $patient['id'] ?>)">
                                            <i class="bi bi-calendar-plus"></i>
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
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Patients</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="searchForm">
                    <div class="mb-3">
                        <label for="searchName" class="form-label">Patient Name</label>
                        <input type="text" class="form-control" id="searchName" placeholder="Enter patient name">
                    </div>
                    <div class="mb-3">
                        <label for="searchPhone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="searchPhone" placeholder="Enter phone number">
                    </div>
                    <div class="mb-3">
                        <label for="searchNationalId" class="form-label">National ID</label>
                        <input type="text" class="form-control" id="searchNationalId" placeholder="Enter national ID">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="performSearch()">Search</button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--accent);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: var(--bg);
}

.btn-group .btn {
    margin: 0 1px;
}
</style>

<script>
function bookAppointment(patientId) {
    // Redirect to calendar with patient pre-selected
    window.location.href = `/doctor/calendar?patient_id=${patientId}`;
}

function performSearch() {
    const name = document.getElementById('searchName').value;
    const phone = document.getElementById('searchPhone').value;
    const nationalId = document.getElementById('searchNationalId').value;
    
    // Build search query
    let params = new URLSearchParams();
    if (name) params.append('name', name);
    if (phone) params.append('phone', phone);
    if (nationalId) params.append('national_id', nationalId);
    
    // Reload page with search parameters
    if (params.toString()) {
        window.location.href = `/doctor/patients?${params.toString()}`;
    }
}

// Auto-refresh every 30 seconds
setInterval(() => {
    window.location.reload();
}, 30000);
</script>
