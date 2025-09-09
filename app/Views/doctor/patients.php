<!-- Patients Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary">
            <i class="bi bi-people me-2"></i>
            Patient Records
        </h4>
        <p class="text-muted mb-0">Manage and view patient information</p>
        <div class="mt-2">
            <small class="text-muted">
                <i class="bi bi-keyboard me-1"></i>
                Shortcuts: 
                • Add Patient <kbd class="me-1">N</kbd> or <kbd class="me-1">ى</kbd> or <kbd class="me-1">Ctrl+N</kbd> 
                • Search <kbd class="me-1">F</kbd> or <kbd class="me-1">ب</kbd>
                <kbd>Esc</kbd> Close
            </small>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group" role="group">
            <button class="btn btn-success" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addPatientModal" 
                    title="Use N or ى or Ctrl+N to add a new patient">
                <i class="bi bi-person-plus me-2"></i>
                Add Patient
                <span class="ms-2">
                    <kbd>N</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ى</kbd>
                </span>
            </button>
        <button class="btn btn-primary" 
                data-bs-toggle="modal" 
                data-bs-target="#searchModal" 
                title="Use F or ب to search for patients">
            <i class="bi bi-search me-2"></i>
            Search Patients
            <span class="ms-2">
                <kbd>F</kbd>
                <span class="text-white-50 mx-1">/</span>
                <kbd lang="ar">ب</kbd>
            </span>
        </button>
        </div>
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

<!-- Doctor Filter -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 text-info">
                    <i class="bi bi-funnel me-2"></i>
                    Filter by Doctor
                </h6>
            </div>
            <div class="card-body py-2">
                <div class="btn-group" role="group" id="doctorFilterGroup">
                    <button type="button" 
                            class="btn btn-outline-primary active" 
                            data-doctor="all" 
                            onclick="filterByDoctor('all')">
                        <i class="bi bi-people me-1"></i>
                        All Doctors
                    </button>
                    <?php 
                    $buttonColors = ['btn-outline-success', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-secondary'];
                    $colorIndex = 0;
                    foreach ($doctors as $doctor): 
                        $colorClass = $buttonColors[$colorIndex % count($buttonColors)];
                        $colorIndex++;
                    ?>
                    <button type="button" 
                            class="btn <?= $colorClass ?>" 
                            data-doctor="<?= $doctor['id'] ?>" 
                            onclick="filterByDoctor('<?= $doctor['id'] ?>')">
                        <i class="bi bi-person-badge me-1"></i>
                        <?= htmlspecialchars($doctor['display_name']) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Showing patients for: <span id="currentFilterText">All Doctors</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patients Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>
                    Patient List
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
                                   placeholder="Quick search..."
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearQuickSearch">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Items per page -->
                    <div class="d-flex align-items-center">
                        <label for="paginationLimit" class="form-label mb-0 me-2 text-muted">View:</label>
                        <select class="form-select form-select-sm" id="paginationLimit" style="width: auto;">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                    <div class="text-muted">
                        <small>Total: <span id="totalPatientsCount"><?= count($patients) ?></span> patients</small>
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
                        <th>Patient Info</th>
                        <th>Contact</th>
                        <th>Age</th>
                        <th>Doctors</th>
                        <th>Last Visit</th>
                        <th>Total Visits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="patientsTableBody">
                    <!-- Patients will be rendered here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination Controls -->
    <div class="card-footer" id="paginationContainer">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="pagination-info text-muted">
                    <small>
                        View <span id="showingFrom">1</span> to <span id="showingTo">20</span> 
                        of <span id="totalPatients"><?= count($patients) ?></span> patients
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <nav aria-label="Patients pagination">
                    <ul class="pagination pagination-sm justify-content-end mb-0" id="paginationNav">
                        <!-- Pagination items will be generated here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-search me-2"></i>
                    Search Patients
                </h5>
                <div class="keyboard-hint">
                    <span>Press</span>
                    <kbd>Esc</kbd>
                    <span>to close</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                               id="globalSearch" 
                               placeholder="Search by name, phone, or national ID..."
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-text d-flex justify-content-between align-items-center search-help-text">
                        <span class="search-instruction">
                            <i class="bi bi-info-circle me-1"></i>
                            Start typing to search automatically
                        </span>
                        <small class="search-shortcut">
                            <kbd>Ctrl</kbd>+<kbd>F</kbd> to focus search
                        </small>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="searchResults">
                    <!-- Loading State -->
                    <div id="searchLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Searching...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Searching patients...</p>
                    </div>

                    <!-- No Results -->
                    <div id="noResults" class="text-center py-4" style="display: none;">
                        <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2">No patients found</h6>
                        <p class="text-muted mb-0">Try different search terms</p>
                    </div>

                    <!-- Results Container -->
                    <div id="searchResultsList" class="search-results-container">
                        <!-- Results will be populated here -->
                    </div>

                    <!-- Initial State -->
                    <div id="searchInitial" class="text-center py-4">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2">Search Patients</h6>
                        <p class="text-muted mb-0">Enter name, phone number, or national ID to search</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>
                    Add New Patient
                </h5>
                <div class="keyboard-hint">
                    <span>Press</span>
                    <kbd>Esc</kbd>
                    <span>to close</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="addPatientMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-1"></i>
                                Basic Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="age" class="form-label">Age (Years)</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" placeholder="Enter age in years">
                                <div class="form-text">Alternative: Enter age to automatically calculate date of birth</div>
                            </div>

                            <div class="mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob">
                                <div class="form-text">Patient's date of birth (if empty, today's date will be used)</div>
                            </div>
                            

                            
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">-- Please select gender --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="form-text text-danger"><strong>Required:</strong> Please select the patient's gender</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nationalId" class="form-label">National ID</label>
                                <input type="text" class="form-control" id="nationalId" name="national_id" maxlength="20">
                                <div class="form-text">Government issued ID number (optional)</div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-telephone me-1"></i>
                                Contact Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required maxlength="20">
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Primary contact number</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="altPhone" class="form-label">Alternative Phone</label>
                                <input type="tel" class="form-control" id="altPhone" name="alt_phone" maxlength="20">
                                <div class="form-text">Secondary contact number (optional)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" maxlength="500"></textarea>
                                <div class="form-text">Home address (optional)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="emergencyContact" class="form-label">Emergency Contact</label>
                                <input type="text" class="form-control" id="emergencyContact" name="emergency_contact" maxlength="100">
                                <div class="form-text">Emergency contact person name</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="emergencyPhone" class="form-label">Emergency Phone</label>
                                <input type="tel" class="form-control" id="emergencyPhone" name="emergency_phone" maxlength="20">
                                <div class="form-text">Emergency contact phone number</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="addPatientSubmit" title="Save patient - Press 'Ctrl+S'">
                        <i class="bi bi-person-plus me-1"></i>
                        <span class="btn-text">Add Patient</span>
                        <small class="ms-2 text-white-50">
                            <kbd style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); font-size: 0.7rem;">Ctrl+S</kbd>
                        </small>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Patient Warning Modal -->
<div class="modal fade" id="deletePatientModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Warining: Delete Patient
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="bi bi-shield-exclamation fs-3 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Important Warning!</h6>
                        <p class="mb-0">You are about to delete the patient permanently from the system. This action <strong>cannot be undone</strong>.</p>
                    </div>
                </div>
                
                <div class="patient-delete-info mb-4">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-person-x me-2"></i>
                        Patient Data to be Deleted:
                    </h6>
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3" id="deletePatientAvatar"></div>
                                <div>
                                    <h6 class="mb-1" id="deletePatientName"></h6>
                                    <small class="text-muted">Patient ID: #<span id="deletePatientId"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="deletion-consequences">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-list-check me-2"></i>
                        The following data will be deleted permanently:
                    </h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-person text-danger me-2"></i>
                            <span>All patient personal data</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-calendar-event text-danger me-2"></i>
                            <span>All appointments and previous visits</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-medical text-danger me-2"></i>
                            <span>Medical history and diagnoses</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-receipt text-danger me-2"></i>
                            <span>All invoices and payments</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-earmark text-danger me-2"></i>
                            <span>All files and attachments</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-chat-left-text text-danger me-2"></i>
                            <span>All notes and reports</span>
                        </li>
                    </ul>
                </div>
                
                <div class="alert alert-warning mt-4" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> It is recommended to take a backup of important data before proceeding.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-warning" onclick="showDeleteConfirmation()">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    I understand the risks, proceed
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Patient Confirmation Modal -->
<div class="modal fade" id="deletePatientConfirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    Final Confirmation
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
                    <h5 class="text-danger mt-3">Final Confirmation Required</h5>
                    <p class="text-muted">This is the final warning before the final deletion</p>
                </div>
                
                <div class="alert alert-danger" role="alert">
                    <strong>To proceed and delete finally:</strong><br>
                    Type the word <kbd>DELETE</kbd> or <kbd>DEL</kbd> in the field below
                </div>
                
                <div class="mb-3">
                    <label for="deleteConfirmationText" class="form-label">Confirmation Word:</label>
                    <input type="text" 
                           class="form-control form-control-lg text-center" 
                           id="deleteConfirmationText" 
                           placeholder="Type DELETE or DEL"
                           autocomplete="off">
                    <div class="form-text text-center delete-help-text">The confirmation word must be typed in uppercase English letters</div>
                </div>
                
                <div id="deleteConfirmationMessage" class="alert d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="backToDeleteWarning()">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back
                </button>
                <button type="button" class="btn btn-danger" id="finalDeleteButton" onclick="confirmPatientDeletion()" disabled>
                    <i class="bi bi-trash me-1"></i>
                    <span class="btn-text">Final Delete</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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
    background-color: var(--bg);
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

/* Button styling for dark mode */
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

.btn-outline-secondary {
    color: var(--muted);
    border-color: var(--border);
}

.btn-outline-secondary:hover {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

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

/* Keyboard shortcut hint in modal */
.keyboard-hint {
    position: absolute;
    top: 10px;
    right: 15px;
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
    background-color: var(--bg);
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
    background-color: var(--bg);
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

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

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

.input-group-sm .btn-outline-secondary {
    border-color: var(--border);
    color: var(--muted);
    font-size: 0.875rem;
    border-left: 1px solid var(--border);
}

.input-group-sm .btn-outline-secondary:hover {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

/* Quick search focus state */
#quickSearch:focus + .btn-outline-secondary {
    border-color: var(--accent);
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

#doctorFilterGroup .btn-outline-primary.active {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

#doctorFilterGroup .btn-outline-success.active {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

#doctorFilterGroup .btn-outline-warning.active {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

#doctorFilterGroup .btn-outline-info.active {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

#doctorFilterGroup .btn-outline-secondary.active {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

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

.btn-outline-primary:hover[data-bs-toggle="tooltip"] {
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-outline-success:hover[data-bs-toggle="tooltip"] {
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
}

.btn-outline-danger:hover[data-bs-toggle="tooltip"] {
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}
</style>

<script>
let searchTimeout;
let currentSearchRequest;

// Pagination state
let paginationState = {
    currentPage: 1,
    itemsPerPage: 20,
    totalItems: 0,
    allPatients: [],
    filteredPatients: [],
    currentDoctorFilter: 'all'
};

// Debounce function
function debounce(func, wait) {
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(searchTimeout);
            func(...args);
        };
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(later, wait);
    };
}

// Book appointment function
function bookAppointment(patientId) {
    // Redirect to calendar with patient pre-selected
    window.location.href = `/doctor/calendar?patient_id=${patientId}`;
}

// View patient function
function viewPatient(patientId) {
    window.location.href = `/doctor/patients/${patientId}`;
}

// Initialize pagination with PHP data
function initializePagination() {
    // Get patients data from PHP
    const patientsData = <?= json_encode($patients, JSON_UNESCAPED_UNICODE) ?>;
    const doctorsData = <?= json_encode($doctors, JSON_UNESCAPED_UNICODE) ?>;
    
    paginationState.allPatients = patientsData;
    paginationState.filteredPatients = [...patientsData];
    paginationState.totalItems = patientsData.length;
    paginationState.doctors = doctorsData;
    
    // Apply initial doctor filter
    applyDoctorFilter();
    
    // Render initial page
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
    
    console.log('Pagination initialized with', paginationState.totalItems, 'patients');
    console.log('Doctors available:', paginationState.doctors);
}

// Render patients table with current page data
function renderPatientsTable() {
    const tableBody = document.getElementById('patientsTableBody');
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    // Add loading state
    tableBody.parentElement.classList.add('table-loading');
    
    // Calculate pagination
    let startIndex, endIndex, patientsToShow;
    
    if (itemsPerPage === 'all') {
        startIndex = 0;
        endIndex = filteredPatients.length;
        patientsToShow = filteredPatients;
    } else {
        startIndex = (currentPage - 1) * itemsPerPage;
        endIndex = Math.min(startIndex + itemsPerPage, filteredPatients.length);
        patientsToShow = filteredPatients.slice(startIndex, endIndex);
    }
    
    // Clear table
    tableBody.innerHTML = '';
    
    // Add delay for smooth transition
    setTimeout(() => {
        if (patientsToShow.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No patients to display</p>
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            
            patientsToShow.forEach(patient => {
                const age = patient.dob ? calculateAge(patient.dob) : 'Not specified';
                const lastVisit = patient.last_visit ? formatDate(patient.last_visit) : 'Not visited yet';
                
                // Handle Arabic and English names properly
                const firstName = patient.first_name || '';
                const lastName = patient.last_name || '';
                const fullName = `${firstName} ${lastName}`.trim();
                
                // Get avatar initials and gender-based styling
                const firstChar = firstName.charAt(0).toUpperCase();
                const lastChar = lastName.charAt(0).toUpperCase();
                const avatarInitials = firstChar && lastChar ? `${firstChar}.${lastChar}` : '?.?';
                
                // Gender-based avatar color
                const avatarClass = patient.gender === 'Female' ? 'avatar-circle avatar-female me-3' : 'avatar-circle avatar-male me-3';
                
                // Get doctor who created this patient
                let doctorInfo = 'Unknown';
                if (patient.created_by_doctor_name) {
                    doctorInfo = patient.created_by_doctor_name;
                } else if (patient.created_by_name) {
                    doctorInfo = patient.created_by_name;
                }
                
                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="${avatarClass}">
                                    ${avatarInitials}
                                </div>
                                <div>
                                    <h6 class="mb-1">${escapeHtml(fullName)}</h6>
                                    <small class="text-muted">ID: #${patient.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-telephone me-1"></i>
                                ${escapeHtml(patient.phone || 'Not available')}
                            </div>
                            ${patient.alt_phone ? `<div class="mt-1">
                                <i class="bi bi-telephone-plus me-1"></i>
                                <small class="text-muted">${escapeHtml(patient.alt_phone)}</small>
                            </div>` : ''}
                        </td>
                        <td>
                            ${age !== 'Not specified' ? `${age} years` : '<span class="text-muted">Not specified</span>'}
                        </td>
                        <td>
                            <div class="doctor-info">
                                ${doctorInfo === 'Unknown' ? 
                                    '<span class="badge bg-secondary">Unknown</span>' :
                                    `<span class="badge bg-info">${escapeHtml(doctorInfo)}</span>`
                                }
                            </div>
                        </td>
                        <td>
                            ${patient.last_visit ? 
                                `<span class="badge bg-success">${lastVisit}</span>` : 
                                '<span class="badge bg-secondary">Not visited yet</span>'
                            }
                        </td>
                        <td>
                            <span class="badge bg-primary">${patient.total_appointments || 0}</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/doctor/patients/${patient.id}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   data-bs-title="View patient details and medical history">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-success" 
                                        onclick="bookAppointment(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Book a new appointment for this patient">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deletePatient(${patient.id}, '${escapeHtml(fullName).replace(/'/g, '\\\'')}')" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Delete the patient from the system (cannot be undone)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = html;
            
            // Refresh tooltips for new content
            setTimeout(() => {
                refreshTooltips();
            }, 100);
        }
        
        // Remove loading state
        tableBody.parentElement.classList.remove('table-loading');
        
    }, 150); // Short delay for smooth transition
}

// Update pagination information
function updatePaginationInfo() {
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    document.getElementById('totalPatientsCount').textContent = filteredPatients.length;
    document.getElementById('totalPatients').textContent = filteredPatients.length;
    
    if (itemsPerPage === 'all') {
        document.getElementById('showingFrom').textContent = filteredPatients.length > 0 ? '1' : '0';
        document.getElementById('showingTo').textContent = filteredPatients.length;
        
        // Hide pagination nav when showing all
        document.getElementById('paginationContainer').style.display = 'block';
        document.getElementById('paginationNav').style.display = 'none';
    } else {
        const startIndex = (currentPage - 1) * itemsPerPage + 1;
        const endIndex = Math.min(currentPage * itemsPerPage, filteredPatients.length);
        
        document.getElementById('showingFrom').textContent = filteredPatients.length > 0 ? startIndex : '0';
        document.getElementById('showingTo').textContent = endIndex;
        
        // Show pagination nav
        document.getElementById('paginationNav').style.display = 'flex';
    }
}

// Render pagination navigation
function renderPaginationNav() {
    const paginationNav = document.getElementById('paginationNav');
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    if (itemsPerPage === 'all') {
        paginationNav.innerHTML = '';
        return;
    }
    
    const totalPages = Math.ceil(filteredPatients.length / itemsPerPage);
    
    if (totalPages <= 1) {
        paginationNav.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})" aria-label="Previous">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    // Page numbers with smart pagination
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    // Adjust if we're near the beginning or end
    if (currentPage <= 3) {
        startPage = 1;
        endPage = Math.min(5, totalPages);
    } else if (currentPage >= totalPages - 2) {
        startPage = Math.max(1, totalPages - 4);
        endPage = totalPages;
    }
    
    // First page and ellipsis
    if (startPage > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>
        `;
        if (startPage > 2) {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }
    
    // Last page and ellipsis
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>
            </li>
        `;
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})" aria-label="Next">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    paginationNav.innerHTML = html;
}

// Change page function
function changePage(page) {
    const { itemsPerPage, filteredPatients } = paginationState;
    
    if (itemsPerPage === 'all') return;
    
    const totalPages = Math.ceil(filteredPatients.length / itemsPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    paginationState.currentPage = page;
    
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
    
    // Smooth scroll to table top
    document.querySelector('.card').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

// Change items per page
function changeItemsPerPage(newLimit) {
    paginationState.itemsPerPage = newLimit === 'all' ? 'all' : parseInt(newLimit);
    paginationState.currentPage = 1; // Reset to first page
    
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
    
    console.log('Items per page changed to:', newLimit);
}

// Escape HTML function
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
}

// Filter patients by doctor
function filterByDoctor(doctorId) {
    console.log('Filtering by doctor:', doctorId);
    
    // Update active button
    document.querySelectorAll('#doctorFilterGroup .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-doctor="${doctorId}"]`).classList.add('active');
    
    // Update current filter
    paginationState.currentDoctorFilter = doctorId;
    
    // Update filter text
    let filterText = 'All Doctors';
    if (doctorId !== 'all') {
        const doctor = paginationState.doctors.find(d => d.id == doctorId);
        if (doctor) {
            filterText = doctor.display_name;
        }
    }
    document.getElementById('currentFilterText').textContent = filterText;
    
    // Apply doctor filter
    applyDoctorFilter();
    
    // Apply current search filter if exists
    const quickSearch = document.getElementById('quickSearch');
    if (quickSearch && quickSearch.value.trim()) {
        filterPatientsLocally(quickSearch.value);
    } else {
        // Update display
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    }
}

// Apply doctor filter to patients
function applyDoctorFilter() {
    const { currentDoctorFilter, allPatients } = paginationState;
    
    if (currentDoctorFilter === 'all') {
        paginationState.filteredPatients = [...allPatients];
    } else {
        // Filter patients by doctor ID based on who created the patient profile
        paginationState.filteredPatients = allPatients.filter(patient => {
            // Check if patient was created by the selected doctor
            return patient.created_by_doctor_id == currentDoctorFilter;
        });
    }
    
    // Reset to first page
    paginationState.currentPage = 1;
}

// Filter patients locally (for main table pagination)
function filterPatientsLocally(query) {
    // First apply doctor filter
    applyDoctorFilter();
    
    if (!query || query.trim().length < 2) {
        // No search query, just use doctor filter results
    } else {
        const searchTerm = query.trim().toLowerCase();
        paginationState.filteredPatients = paginationState.filteredPatients.filter(patient => {
            const fullName = `${patient.first_name} ${patient.last_name}`.toLowerCase();
            const phone = (patient.phone || '').toLowerCase();
            const altPhone = (patient.alt_phone || '').toLowerCase();
            const nationalId = (patient.national_id || '').toLowerCase();
            
            return fullName.includes(searchTerm) || 
                   phone.includes(searchTerm) || 
                   altPhone.includes(searchTerm) || 
                   nationalId.includes(searchTerm);
        });
    }
    
    // Reset to first page after filtering
    paginationState.currentPage = 1;
    
    // Update display
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
}

// Search patients function (for modal)
function searchPatients(query) {
    const searchLoading = document.getElementById('searchLoading');
    const searchInitial = document.getElementById('searchInitial');
    const noResults = document.getElementById('noResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    // Hide all states
    searchInitial.style.display = 'none';
    noResults.style.display = 'none';
    searchResultsList.style.display = 'none';
    
    if (!query || query.trim().length < 2) {
        searchInitial.style.display = 'block';
        return;
    }
    
    // Show loading
    searchLoading.style.display = 'block';
    
    // Cancel previous request
    if (currentSearchRequest) {
        currentSearchRequest.abort();
    }
    
    // Create new request
    currentSearchRequest = new AbortController();
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query.trim())}`, {
        signal: currentSearchRequest.signal
    })
    .then(response => response.json())
    .then(data => {
        searchLoading.style.display = 'none';
        
        if (data.ok && data.data && data.data.length > 0) {
            displaySearchResults(data.data, query.trim());
        } else {
            noResults.style.display = 'block';
        }
    })
    .catch(error => {
        searchLoading.style.display = 'none';
        if (error.name !== 'AbortError') {
            console.error('Search error:', error);
            noResults.style.display = 'block';
        }
    });
}

// Display search results
function displaySearchResults(patients, searchTerm) {
    const searchResultsList = document.getElementById('searchResultsList');
    let html = '';
    
    patients.forEach(patient => {
        const fullName = `${patient.first_name} ${patient.last_name}`;
        const age = patient.dob ? calculateAge(patient.dob) : 'N/A';
        const lastVisit = patient.last_visit ? formatDate(patient.last_visit) : 'Never';
        
        // Highlight search terms
        const highlightedName = highlightSearchTerm(fullName, searchTerm);
        const highlightedPhone = highlightSearchTerm(patient.phone || '', searchTerm);
        const highlightedNationalId = highlightSearchTerm(patient.national_id || '', searchTerm);
        
        html += `
            <div class="search-result-item" onclick="selectSearchResult(${patient.id})">
                <div class="d-flex align-items-center">
                                                        <div class="search-result-avatar ${patient.gender === 'Female' ? 'avatar-female' : 'avatar-male'} me-3">
                                        ${getAvatarInitials(patient.first_name, patient.last_name)}
                                    </div>
                    <div class="search-result-info flex-grow-1">
                        <h6 class="mb-1">${highlightedName}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    ${highlightedPhone || 'No phone'}
                                </small>
                                ${patient.alt_phone ? `<small class="text-muted d-block">
                                    <i class="bi bi-telephone-plus me-1"></i>
                                    ${patient.alt_phone}
                                </small>` : ''}
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="bi bi-person me-1"></i>
                                    Age: ${age} years
                                </small>
                                ${patient.national_id ? `<small class="text-muted d-block">
                                    <i class="bi bi-card-text me-1"></i>
                                    ID: ${highlightedNationalId}
                                </small>` : ''}
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-primary me-2">${patient.total_appointments || 0} visits</span>
                            <span class="badge bg-success">Last: ${lastVisit}</span>
                        </div>
                    </div>
                    <div class="search-result-actions ms-3">
                        <div class="btn-group-vertical">
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="event.stopPropagation(); viewPatient(${patient.id})"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="View full patient details and medical history">
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                            <button class="btn btn-sm btn-outline-success" 
                                    onclick="event.stopPropagation(); bookAppointment(${patient.id})"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Book a new appointment for this patient">
                                <i class="bi bi-calendar-plus me-1"></i>Book
                            </button>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="event.stopPropagation(); deletePatient(${patient.id}, '${patient.first_name} ${patient.last_name}')"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Delete patient permanently from the system (cannot be undone)">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    searchResultsList.innerHTML = html;
    searchResultsList.style.display = 'block';
}

// Select search result
function selectSearchResult(patientId) {
    viewPatient(patientId);
}

// Highlight search terms
function highlightSearchTerm(text, searchTerm) {
    if (!text || !searchTerm) return text;
    
    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<span class="search-highlight">$1</span>');
}

// Get avatar initials with proper UTF-8 support for Arabic names
function getAvatarInitials(firstName, lastName) {
    if (!firstName || !lastName) {
        return '?.?';
    }
    
    // Get first character of each name using proper Unicode handling
    const firstChar = firstName.charAt(0).toUpperCase();
    const lastChar = lastName.charAt(0).toUpperCase();
    
    return firstChar + '.' + lastChar;
}

// Calculate age
function calculateAge(dob) {
    const today = new Date();
    const birthDate = new Date(dob);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Initialize search functionality  
document.addEventListener('DOMContentLoaded', function() {
    // Initialize pagination first
    initializePagination();
    
    // Setup pagination limit selector
    const paginationLimitSelect = document.getElementById('paginationLimit');
    if (paginationLimitSelect) {
        paginationLimitSelect.addEventListener('change', function() {
            changeItemsPerPage(this.value);
        });
    }
    
    // Setup quick search
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    
    if (quickSearch) {
        // Debounced search for main table
        const debouncedQuickSearch = debounce(filterPatientsLocally, 300);
        
        quickSearch.addEventListener('input', function() {
            debouncedQuickSearch(this.value);
        });
        
        // Clear search
        if (clearQuickSearch) {
            clearQuickSearch.addEventListener('click', function() {
                quickSearch.value = '';
                filterPatientsLocally('');
                quickSearch.focus();
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            const isModalOpen = document.querySelector('.modal.show');
            const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                                 e.target.contentEditable === 'true';
            
            // Quick search shortcut (Ctrl+F when not in modal)
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'f' && !isModalOpen) {
                e.preventDefault();
                quickSearch.focus();
                quickSearch.select();
                return;
            }
            
            // Pagination shortcuts (only when not typing in inputs and no modal is open)
            if (!isInputFocused && !isModalOpen && paginationState.itemsPerPage !== 'all') {
                const totalPages = Math.ceil(paginationState.filteredPatients.length / paginationState.itemsPerPage);
                
                switch(e.key) {
                    case 'ArrowLeft':
                    case 'ArrowRight':
                        e.preventDefault();
                        if (e.key === 'ArrowLeft' && paginationState.currentPage < totalPages) {
                            changePage(paginationState.currentPage + 1);
                        } else if (e.key === 'ArrowRight' && paginationState.currentPage > 1) {
                            changePage(paginationState.currentPage - 1);
                        }
                        break;
                        
                    case 'Home':
                        e.preventDefault();
                        changePage(1);
                        break;
                        
                    case 'End':
                        e.preventDefault();
                        changePage(totalPages);
                        break;
                }
            }
        });
    }
    
    const globalSearch = document.getElementById('globalSearch');
    const clearSearch = document.getElementById('clearSearch');
    const searchModal = document.getElementById('searchModal');
    const searchButton = document.querySelector('[data-bs-target="#searchModal"]');
    
    // Debounced search
    const debouncedSearch = debounce(searchPatients, 300);
    
    // Search input event
    globalSearch.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
    
    // Clear search
    clearSearch.addEventListener('click', function() {
        globalSearch.value = '';
        globalSearch.focus();
        document.getElementById('searchInitial').style.display = 'block';
        document.getElementById('searchLoading').style.display = 'none';
        document.getElementById('noResults').style.display = 'none';
        document.getElementById('searchResultsList').style.display = 'none';
    });
    
    // Focus search input when modal opens
    searchModal.addEventListener('shown.bs.modal', function() {
        globalSearch.focus();
    });
    
    // Reset search when modal closes
    searchModal.addEventListener('hidden.bs.modal', function() {
        globalSearch.value = '';
        document.getElementById('searchInitial').style.display = 'block';
        document.getElementById('searchLoading').style.display = 'none';
        document.getElementById('noResults').style.display = 'none';
        document.getElementById('searchResultsList').style.display = 'none';
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Debug keyboard input (remove in production)
        if (e.key === 'ب' || e.key.toLowerCase() === 'f') {
            console.log('Search key pressed:', {
                key: e.key,
                keyCode: e.keyCode,
                code: e.code,
                isInputFocused: isInputFocused(),
                modalOpen: searchModal.classList.contains('show')
            });
        }
        
        // Open search modal with 'F' key or Arabic 'ب' key (only if no input is focused)
        // Also support Arabic keyboard layout alternatives
        const searchKeys = ['f', 'ب']; // F key and Arabic 'ba' (same position on keyboard)
        const isSearchKey = searchKeys.includes(e.key.toLowerCase()) || searchKeys.includes(e.key);
        
        if (isSearchKey && !isInputFocused() && !searchModal.classList.contains('show')) {
            e.preventDefault();
            console.log('Opening search modal with key:', e.key);
            searchButton.click();
        }
        
        // Open add patient modal with 'Ctrl+N' or 'N' key or Arabic 'ى' key
        const addPatientKeys = ['n', 'ى']; // N key and Arabic 'ya' (same position on keyboard)
        const isAddPatientKey = addPatientKeys.includes(e.key.toLowerCase()) || addPatientKeys.includes(e.key);
        const isCtrlN = (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'n';
        
        if ((isAddPatientKey || isCtrlN) && !isInputFocused() && !document.querySelector('.modal.show')) {
            e.preventDefault();
            console.log('Opening add patient modal with key:', e.key, 'Ctrl pressed:', e.ctrlKey);
            document.querySelector('[data-bs-target="#addPatientModal"]').click();
        }
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            if (searchModal.classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(searchModal).hide();
            } else if (document.getElementById('addPatientModal').classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('addPatientModal')).hide();
            } else if (document.getElementById('deletePatientModal').classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('deletePatientModal')).hide();
            } else if (document.getElementById('deletePatientConfirmModal').classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('deletePatientConfirmModal')).hide();
            }
        }
        
        // Focus search input with 'Ctrl+F' or 'Cmd+F' when modal is open
        // Also support Arabic layout
        if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'f' || e.key === 'ب') && searchModal.classList.contains('show')) {
            e.preventDefault();
            globalSearch.focus();
            globalSearch.select();
        }
        
        // Save patient with 'Ctrl+S' when add patient modal is open
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's' && document.getElementById('addPatientModal').classList.contains('show')) {
            e.preventDefault();
            const submitButton = document.getElementById('addPatientSubmit');
            if (!submitButton.disabled) {
                submitButton.click();
            }
        }
    });
    
    // Helper function to check if any input is currently focused
    function isInputFocused() {
        const activeElement = document.activeElement;
        return activeElement && (
            activeElement.tagName === 'INPUT' || 
            activeElement.tagName === 'TEXTAREA' || 
            activeElement.tagName === 'SELECT' ||
            activeElement.contentEditable === 'true'
        );
    }
    
    // Initialize Add Patient Modal
    initializeAddPatientModal();
});

// Add Patient functionality
function initializeAddPatientModal() {
    const addPatientForm = document.getElementById('addPatientForm');
    const addPatientModal = document.getElementById('addPatientModal');
    const addPatientSubmit = document.getElementById('addPatientSubmit');
    const addPatientMessage = document.getElementById('addPatientMessage');
    
    // Reset form when modal opens
    addPatientModal.addEventListener('show.bs.modal', function() {
        addPatientForm.reset();
        addPatientForm.classList.remove('was-validated');
        hideMessage();
        resetSubmitButton();
        
        // Focus on first name field
        setTimeout(() => {
            document.getElementById('firstName').focus();
        }, 300);
    });
    
    // Handle form submission
    addPatientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!addPatientForm.checkValidity()) {
            addPatientForm.classList.add('was-validated');
            showMessage('Please fill in all required fields correctly.', 'error');
            return;
        }
        
        // Additional validation
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const gender = document.getElementById('gender').value;
        
        if (!firstName || !lastName || !phone) {
            showMessage('First name, last name, and phone number are required.', 'error');
            return;
        }
        
        if (!gender) {
            showMessage('Please select the patient\'s gender.', 'error');
            document.getElementById('gender').focus();
            return;
        }
        
        // Validate phone number format (more flexible validation)
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, ''); // Remove spaces, dashes, parentheses
        const phoneRegex = /^(\+\d{1,3})?\d{7,15}$/;
        if (!phoneRegex.test(cleanPhone)) {
            showMessage('Please enter a valid phone number (7-15 digits, optionally with country code).', 'error');
            return;
        }
        
        // Submit form
        submitPatientForm();
    });
    
    function submitPatientForm() {
        const formData = new FormData(addPatientForm);
        
        // Show loading state
        setSubmitButtonLoading(true);
        hideMessage();
        
        // Send AJAX request
        fetch('/api/patients', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            setSubmitButtonLoading(false);
            
            if (data.ok) {
                // Success
                showMessage('Patient added successfully!', 'success');
                
                // Reset form
                addPatientForm.reset();
                addPatientForm.classList.remove('was-validated');
                
                // Close modal after delay
                setTimeout(() => {
                    bootstrap.Modal.getInstance(addPatientModal).hide();
                    
                    // Refresh page to show new patient
                    window.location.reload();
                }, 1500);
                
            } else {
                // Error from server
                const errorMsg = data.error || data.message || 'Failed to add patient. Please try again.';
                showMessage(errorMsg, 'error');
                
                // Show validation errors if available
                if (data.details) {
                    showValidationErrors(data.details);
                }
            }
        })
        .catch(error => {
            setSubmitButtonLoading(false);
            console.error('Error adding patient:', error);
            showMessage('An error occurred while adding the patient. Please try again.', 'error');
        });
    }
    
    function showMessage(message, type) {
        addPatientMessage.className = `alert alert-${type === 'error' ? 'danger' : type}`;
        addPatientMessage.textContent = message;
        addPatientMessage.classList.remove('d-none');
    }
    
    function hideMessage() {
        addPatientMessage.classList.add('d-none');
    }
    
    function setSubmitButtonLoading(loading) {
        const btnText = addPatientSubmit.querySelector('.btn-text');
        const spinner = addPatientSubmit.querySelector('.spinner-border');
        
        if (loading) {
            addPatientSubmit.disabled = true;
            btnText.textContent = 'Adding...';
            spinner.classList.remove('d-none');
        } else {
            addPatientSubmit.disabled = false;
            btnText.textContent = 'Add Patient';
            spinner.classList.add('d-none');
        }
    }
    
    function resetSubmitButton() {
        setSubmitButtonLoading(false);
    }
    
    function showValidationErrors(errors) {
        // Clear previous validation errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
        
        // Show new validation errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[field];
                }
            }
        });
    }
    
    // Clear validation errors on input
    addPatientForm.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    });
    
    // Age and Date of Birth conversion
    const dobInput = document.getElementById('dob');
    const ageInput = document.getElementById('age');
    
    // Convert age to date of birth
    ageInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        if (age && age > 0 && age <= 150) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            const birthDate = new Date(birthYear, today.getMonth(), today.getDate());
            dobInput.value = birthDate.toISOString().split('T')[0];
            
            // Clear age field after conversion to avoid confusion
            setTimeout(() => {
                this.value = '';
            }, 1000);
        }
    });
    
    // Convert date of birth to age (when user changes date)
    dobInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age >= 0 && age <= 150) {
                // Show calculated age in placeholder temporarily
                ageInput.placeholder = `Calculated age: ${age} years`;
                setTimeout(() => {
                    ageInput.placeholder = 'Enter age in years';
                }, 3000);
            }
        }
    });
}

// Delete Patient functionality - use window object for global scope
window.currentPatientToDelete = null;

function deletePatient(patientId, patientName) {
    console.log('deletePatient called with:', { patientId, patientName });
    
    window.currentPatientToDelete = {
        id: patientId,
        name: patientName
    };
    
    // Store in localStorage as backup
    localStorage.setItem('deletePatientData', JSON.stringify(window.currentPatientToDelete));
    
    console.log('window.currentPatientToDelete set to:', window.currentPatientToDelete);
    
    // Set patient info in modal
    document.getElementById('deletePatientId').textContent = patientId;
    document.getElementById('deletePatientName').textContent = patientName;
    
    // Set avatar initials
    const nameParts = patientName.split(' ');
    let initials;
    if (nameParts.length >= 2) {
        initials = getAvatarInitials(nameParts[0], nameParts[1]);
    } else {
        // If only one name, use first two characters with dot
        const name = nameParts[0];
        const firstChar = name.charAt(0).toUpperCase();
        const secondChar = name.length > 1 ? name.charAt(1).toUpperCase() : '?';
        initials = firstChar + '.' + secondChar;
    }
    
    // Find patient gender for avatar color
    const patient = paginationState.allPatients.find(p => p.id == patientId);
    const avatarElement = document.getElementById('deletePatientAvatar');
    avatarElement.textContent = initials;
    
    // Apply gender-based class
    avatarElement.className = 'avatar-circle';
    if (patient && patient.gender === 'Female') {
        avatarElement.classList.add('avatar-female');
    } else if (patient && patient.gender === 'Male') {
        avatarElement.classList.add('avatar-male');
    }
    
    // Show warning modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deletePatientModal'));
    deleteModal.show();
}

function showDeleteConfirmation() {
    console.log('showDeleteConfirmation called, window.currentPatientToDelete:', window.currentPatientToDelete);
    
    // Hide warning modal
    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePatientModal'));
    deleteModal.hide();
    
    // Reset confirmation modal
    resetDeleteConfirmation();
    
    // Show confirmation modal
    const confirmModal = new bootstrap.Modal(document.getElementById('deletePatientConfirmModal'));
    confirmModal.show();
    
    // Focus on text input
    setTimeout(() => {
        document.getElementById('deleteConfirmationText').focus();
    }, 300);
}

function backToDeleteWarning() {
    // Hide confirmation modal
    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('deletePatientConfirmModal'));
    confirmModal.hide();
    
    // Show warning modal again
    setTimeout(() => {
        const deleteModal = new bootstrap.Modal(document.getElementById('deletePatientModal'));
        deleteModal.show();
    }, 300);
}

function resetDeleteConfirmation() {
    const confirmText = document.getElementById('deleteConfirmationText');
    const finalButton = document.getElementById('finalDeleteButton');
    const message = document.getElementById('deleteConfirmationMessage');
    
    confirmText.value = '';
    confirmText.classList.remove('is-valid', 'is-invalid');
    finalButton.disabled = true;
    message.classList.add('d-none');
}

function confirmPatientDeletion() {
    console.log('confirmPatientDeletion called, window.currentPatientToDelete:', window.currentPatientToDelete);
    
    // Try to recover from localStorage if main variable is lost
    if (!window.currentPatientToDelete) {
        const savedData = localStorage.getItem('deletePatientData');
        if (savedData) {
            try {
                window.currentPatientToDelete = JSON.parse(savedData);
                console.log('Recovered patient data from localStorage:', window.currentPatientToDelete);
            } catch (e) {
                console.error('Failed to parse saved patient data:', e);
            }
        }
    }
    
    if (!window.currentPatientToDelete) {
        console.error('window.currentPatientToDelete is null or undefined');
        showDeleteMessage('Error: The patient was not selected for deletion', 'error');
        return;
    }
    
    const confirmText = document.getElementById('deleteConfirmationText');
    const enteredText = confirmText.value.trim().toUpperCase();
    
    if (enteredText !== 'DELETE' && enteredText !== 'DEL') {
        showDeleteMessage('The word (DELETE or DEL) must be typed in uppercase English letters', 'error');
        confirmText.focus();
        return;
    }
    
    // Show loading state
    setDeleteButtonLoading(true);
    
    // Send delete request
    fetch(`/api/patients/${window.currentPatientToDelete.id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        setDeleteButtonLoading(false);
        
        if (data.ok) {
            // Success
            let successMsg = '✅ The patient was deleted successfully';
            if (data.data) {
                const attachments = data.data.attachments_deleted || 0;
                const files = data.data.files_deleted || 0;
                if (attachments > 0 || files > 0) {
                    successMsg += `\n📁 Deleted: ${attachments} attachments, ${files} files`;
                }
            }
            showDeleteMessage(successMsg, 'success');
            
            // Clean up data
            window.currentPatientToDelete = null;
            localStorage.removeItem('deletePatientData');
            
            // Close modal after delay and refresh page
            setTimeout(() => {
                const confirmModal = bootstrap.Modal.getInstance(document.getElementById('deletePatientConfirmModal'));
                confirmModal.hide();
                
                // Refresh page to update patient list
                window.location.reload();
            }, 1500);
            
        } else {
            // Error from server
            const errorMsg = data.error || data.message || 'Failed to delete the patient. Please try again.';
            showDeleteMessage(errorMsg, 'error');
        }
    })
    .catch(error => {
        setDeleteButtonLoading(false);
        console.error('Error deleting patient:', error);
        showDeleteMessage('An error occurred while deleting the patient. Please try again.', 'error');
    });
}

function showDeleteMessage(message, type) {
    const messageEl = document.getElementById('deleteConfirmationMessage');
    messageEl.className = `alert alert-${type === 'error' ? 'danger' : type}`;
    
    // Handle multi-line messages
    if (message.includes('\n')) {
        messageEl.innerHTML = message.split('\n').map(line => 
            line.trim() ? `<div>${line}</div>` : ''
        ).join('');
    } else {
        messageEl.textContent = message;
    }
    
    messageEl.classList.remove('d-none');
}

function setDeleteButtonLoading(loading) {
    const finalButton = document.getElementById('finalDeleteButton');
    const btnText = finalButton.querySelector('.btn-text');
    const spinner = finalButton.querySelector('.spinner-border');
    
    if (loading) {
        finalButton.disabled = true;
        btnText.textContent = 'Deleting...';
        spinner.classList.remove('d-none');
    } else {
        finalButton.disabled = false;
        btnText.textContent = 'Final Delete';
        spinner.classList.add('d-none');
    }
}

// Initialize delete confirmation functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteConfirmationText = document.getElementById('deleteConfirmationText');
    const finalDeleteButton = document.getElementById('finalDeleteButton');
    
    // Validate confirmation text input
    deleteConfirmationText.addEventListener('input', function() {
        const value = this.value.trim().toUpperCase();
        const isValid = value === 'DELETE' || value === 'DEL';
        
        if (value) {
            if (isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                finalDeleteButton.disabled = false;
                showDeleteMessage('✓ The confirmation word is correct', 'success');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                finalDeleteButton.disabled = true;
                showDeleteMessage('The word (DELETE or DEL) must be typed in uppercase English letters', 'warning');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
            finalDeleteButton.disabled = true;
            document.getElementById('deleteConfirmationMessage').classList.add('d-none');
        }
    });
    
    // Handle Enter key in confirmation input
    deleteConfirmationText.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !finalDeleteButton.disabled) {
            e.preventDefault();
            confirmPatientDeletion();
        }
    });
    
    // Track modal states to prevent data loss during transitions
    let isTransitioning = false;
    
    // Reset confirmation when modal is hidden
    document.getElementById('deletePatientConfirmModal').addEventListener('hidden.bs.modal', function() {
        console.log('deletePatientConfirmModal hidden');
        resetDeleteConfirmation();
        
        // Reset patient data only if not transitioning back to warning modal
        if (!isTransitioning) {
            console.log('Resetting window.currentPatientToDelete from confirm modal');
            window.currentPatientToDelete = null;
            localStorage.removeItem('deletePatientData');
        }
    });
    
    // Reset patient data when warning modal is hidden
    document.getElementById('deletePatientModal').addEventListener('hidden.bs.modal', function() {
        console.log('deletePatientModal hidden');
        
        // Don't reset if we're transitioning to confirmation modal
        setTimeout(() => {
            if (!document.getElementById('deletePatientConfirmModal').classList.contains('show')) {
                console.log('Resetting window.currentPatientToDelete from warning modal');
                window.currentPatientToDelete = null;
                localStorage.removeItem('deletePatientData');
            }
        }, 100); // Reduced timeout for faster response
    });
    
    // Override showDeleteConfirmation to prevent data loss
    const originalShowDeleteConfirmation = window.showDeleteConfirmation;
    window.showDeleteConfirmation = function() {
        isTransitioning = true;
        console.log('Starting transition to confirmation modal');
        
        originalShowDeleteConfirmation();
        
        // Reset transition flag after modal is shown
        setTimeout(() => {
            isTransitioning = false;
            console.log('Transition completed');
        }, 500);
    };
    
    // Override backToDeleteWarning to prevent data loss
    const originalBackToDeleteWarning = window.backToDeleteWarning;
    window.backToDeleteWarning = function() {
        isTransitioning = true;
        console.log('Starting transition back to warning modal');
        
        originalBackToDeleteWarning();
        
        // Reset transition flag after modal is shown
        setTimeout(() => {
            isTransitioning = false;
            console.log('Back transition completed');
        }, 500);
    };
});

// Initialize Bootstrap Tooltips
function initializeTooltips() {
    // Initialize tooltips for elements with data-bs-toggle="tooltip"
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false, // Allow Arabic text
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    // Initialize tooltips for elements with title attribute (including modal trigger buttons)
    const titleElements = document.querySelectorAll('[title]:not([data-bs-toggle="tooltip"])');
    const titleTooltipList = [...titleElements].map(titleEl => new bootstrap.Tooltip(titleEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    return [...tooltipList, ...titleTooltipList];
}

// Function to refresh tooltips for dynamically added content
function refreshTooltips() {
    // Dispose of existing tooltips
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"], [title]');
    existingTooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Reinitialize all tooltips
    initializeTooltips();
}

// Initialize tooltips when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
});

// Override displaySearchResults to include tooltip initialization
const originalDisplaySearchResults = displaySearchResults;
displaySearchResults = function(patients, searchTerm) {
    originalDisplaySearchResults(patients, searchTerm);
    
    // Initialize tooltips for newly added search result buttons
    setTimeout(() => {
        refreshTooltips();
    }, 100);
};

// Auto-refresh every 30 seconds (pause when modals are open or user is interacting)
setInterval(() => {
    const searchModal = document.getElementById('searchModal');
    const addPatientModal = document.getElementById('addPatientModal');
    const deleteModal = document.getElementById('deletePatientModal');
    const deleteConfirmModal = document.getElementById('deletePatientConfirmModal');
    const quickSearch = document.getElementById('quickSearch');
    
    // Don't refresh if user is actively using the page
    const isUserActive = document.activeElement === quickSearch || 
                        quickSearch.value.trim().length > 0 ||
                        paginationState.currentPage > 1 ||
                        paginationState.itemsPerPage !== 20;
    
    if (!searchModal.classList.contains('show') && 
        !addPatientModal.classList.contains('show') &&
        !deleteModal.classList.contains('show') &&
        !deleteConfirmModal.classList.contains('show') &&
        !isUserActive) {
        window.location.reload();
    }
}, 30000);
</script>
