<link href="/app/Views/doctor/assets/css/patients.css?v=<?= file_exists(__DIR__ . '/assets/css/patients.css') ? filemtime(__DIR__ . '/assets/css/patients.css') : time() ?>" rel="stylesheet">

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
                            class="btn <?= $colorClass ?> d-flex align-items-center" 
                            data-doctor="<?= $doctor['id'] ?>" 
                            onclick="filterByDoctor('<?= $doctor['id'] ?>')">
                        <?php if (!empty($doctor['profile_image'])): 
                            $doctorImagePath = strpos($doctor['profile_image'], '/public/') === 0 ? $doctor['profile_image'] : '/public' . $doctor['profile_image'];
                        ?>
                            <img src="<?= htmlspecialchars($doctorImagePath) ?>" 
                                 alt="<?= htmlspecialchars($doctor['display_name']) ?>" 
                                 class="doctor-filter-avatar me-2"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="doctor-filter-avatar-fallback me-2" style="display: none;">
                                <?= strtoupper(substr($doctor['display_name'] ?? 'D', 0, 1)) ?>
                            </div>
                        <?php else: ?>
                            <div class="doctor-filter-avatar me-2">
                                <?= strtoupper(substr($doctor['display_name'] ?? 'D', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
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
                        <section class="field menu" style="min-width: 70px; width: auto;">
                            <div class="control">
                                <select class="form-select form-select-sm d-none center-select" id="paginationLimit" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="30">30</option>
                                    <option value="50">50</option>
                                    <option value="all">All</option>
                                </select>
                                <button type="button" class="custom-select-toggle" aria-expanded="false" style="font-size: 0.875rem; padding: 0.75rem 2rem 0.75rem 0.75rem;">20</button>
                                <menu>
                                    <li data-option="10" tabindex="0" role="button"><h3>10</h3></li>
                                    <li data-option="20" tabindex="0" role="button" class="selected"><h3>20</h3></li>
                                    <li data-option="30" tabindex="0" role="button"><h3>30</h3></li>
                                    <li data-option="50" tabindex="0" role="button"><h3>50</h3></li>
                                    <li data-option="all" tabindex="0" role="button"><h3>All</h3></li>
                                </menu>
                            </div>
                        </section>
                    </div>
                    <div class="text-muted">
                        <small>Total: <span id="totalPatientsCount"><?= count($patients) ?></span> patients</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0" style="min-height: 600px; overflow: hidden;">
        <div class="table-responsive" style="min-height: 700px; overflow-y: hidden; overflow-x: auto;">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <div class="d-flex flex-column">
                                <span>Patient Info</span>
                                <div class="mt-2 position-relative">
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           id="patientNameFilter" 
                                           placeholder="Filter by name..."
                                           autocomplete="off"
                                           style="min-width: 150px; padding-right: 30px;">
                                    <button type="button" 
                                            class="btn-close btn-close-white position-absolute" 
                                            id="clearNameFilter"
                                            style="top: 50%; right: 5px; transform: translateY(-50%); display: none; font-size: 0.7rem; opacity: 0.7; cursor: pointer;"
                                            aria-label="Clear filter"
                                            onclick="clearPatientNameFilter()">
                                    </button>
                                </div>
                            </div>
                        </th>
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
                            
                            <div class="mb-3" style="display: none;">
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
                            
                            <div class="mb-3" style="display: none;">
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
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="gender" name="gender" required>
                                            <option value="Male" selected>Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">Male</button>
                                        <menu>
                                            <li data-option="Male" tabindex="0" role="button" class="selected"><i class="bi bi-gender-male fs-5"></i><h3>Male</h3></li>
                                            <li data-option="Female" tabindex="0" role="button"><i class="bi bi-gender-female fs-5"></i><h3>Female</h3></li>
                                        </menu>
                                    </div>
                                </section>
                                <div class="invalid-feedback"></div>
                                <div class="form-text text-danger"><strong>Required:</strong> Change the gender if needed</div>
                            </div>
                            
                            <div class="mb-3" style="display: none;">
                                <label for="emergencyContact" class="form-label">Emergency Contact</label>
                                <input type="text" class="form-control" id="emergencyContact" name="emergency_contact" maxlength="100">
                                <div class="form-text">Emergency contact person name</div>
                            </div>
                            
                            <div class="mb-3" style="display: none;">
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
<script>
    // Initialize PATIENTS_CONFIG with PHP variables
    <?php
    ?>

    window.PATIENTS_CONFIG = {
        patients: <?= json_encode($patients, JSON_UNESCAPED_UNICODE) ?>,
        doctors: <?= json_encode($doctors, JSON_UNESCAPED_UNICODE) ?>,
    };
</script>
<script src="/app/Views/doctor/assets/js/patients.js?v=<?= file_exists(__DIR__ . '/assets/js/patients.js') ? filemtime(__DIR__ . '/assets/js/patients.js') : time() ?>"></script>
