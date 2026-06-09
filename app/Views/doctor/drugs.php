<link
    href="/app/Views/doctor/assets/css/drugs.css?v=<?= file_exists(__DIR__ . '/assets/css/drugs.css') ? filemtime(__DIR__ . '/assets/css/drugs.css') : time() ?>"
    rel="stylesheet">

<!-- Drug Search Header -->
<div class="drugs-header mb-4">
    <div class="drugs-header-main">
        <span class="drugs-header-icon"><i class="bi bi-capsule-pill"></i></span>
        <div class="drugs-header-text">
            <h4 class="drugs-header-title">Drug Search</h4>
            <p class="drugs-header-sub">Search and browse the medications database</p>
            <div class="drugs-shortcuts">
                <i class="bi bi-keyboard"></i>
                <span class="drugs-shortcuts-label">Shortcuts</span>
                <span class="kbd-pill">Search <kbd>F</kbd><kbd>ب</kbd></span>
                <span class="kbd-pill">Clear <kbd>Esc</kbd></span>
            </div>
        </div>
    </div>
    <button class="drugs-update-btn" id="updateDatabaseBtn" onclick="showUpdateDatabaseModal()">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Update Database</span>
    </button>
</div>

<!-- Search Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary bg-opacity-10">
                <h6 class="mb-0 text-primary">
                    <i class="bi bi-search me-2"></i>
                    Search Medications
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="position-relative">
                            <input type="text" id="drugSearchInput" class="form-control form-control-lg"
                                placeholder="Search for medications, active ingredients, or companies..."
                                autocomplete="off">
                            <div class="position-absolute top-50 end-0 translate-middle-y pe-3">
                                <i class="bi bi-search text-muted"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2" id="searchButtonsContainer">
                            <button class="btn btn-primary btn-lg w-100" id="searchBtn">
                                <i class="bi bi-search me-2"></i>
                                Search
                            </button>
                            <button class="btn btn-outline-primary flex-grow-1" id="clearSearchBtn"
                                style="display: none;">
                                <i class="bi bi-x-circle me-2"></i>
                                Clear Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 text-info">
                    <i class="bi bi-funnel me-2"></i>
                    Filters
                </h6>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label for="categoryFilter" class="form-label small">Category:</label>
                        <select id="categoryFilter" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="companyFilter" class="form-label small">Company:</label>
                        <select id="companyFilter" class="form-select form-select-sm">
                            <option value="">All Companies</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="routeFilter" class="form-label small">Route:</label>
                        <select id="routeFilter" class="form-select form-select-sm">
                            <option value="">All Routes</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-end">
                        <button class="btn btn-primary btn-sm me-2" id="applyFiltersBtn">
                            <i class="bi bi-funnel-fill me-1"></i>
                            Apply Filters
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="clearFiltersBtn">
                            <i class="bi bi-x-circle me-1"></i>
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Section -->
<div class="row">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-success" id="resultsTitle">
                        <i class="bi bi-list-ul me-2"></i>
                        Search Results
                    </h6>
                    <span class="badge bg-success" id="resultsCount">0 medications found</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Initial Search Message -->
                <div id="initialSearchMessage" class="initial-search-message text-center py-5">
                    <i class="bi bi-search-heart text-primary icon-lg"></i>
                    <h5 class="mt-3 text-muted">Search to find drugs information</h5>
                    <p class="text-muted small mb-0">Enter a drug name, active ingredient, or company to start</p>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="flex-column justify-content-center align-items-center py-5"
                    style="display: none; min-height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted mb-0">Searching medications...</p>
                </div>

                <!-- No Results -->
                <div id="noResults" class="text-center py-5" style="display: none;">
                    <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No medications found</h5>
                    <p class="text-muted">Try adjusting your search terms or filters</p>
                </div>

                <!-- Results Grid -->
                <div id="drugResults" class="row g-3">
                    <!-- Results will be populated here -->
                </div>

                <!-- Load More Button -->
                <div id="loadMoreContainer" class="text-center mt-4" style="display: none;">
                    <button id="loadMoreBtn" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-down-circle me-2"></i>
                        Load More Results
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drug Details Modal -->
<div class="modal fade" id="drugDetailsModal" tabindex="-1" aria-labelledby="drugDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDrugName">Drug Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDrugDetails">
                <!-- Drug details will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary me-auto" id="addDrugTemplateBtn" onclick="openDrugTemplateModal()">
                    <i class="bi bi-bookmark-star me-1"></i>Add drug instruction template
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Drug Instruction Template Modal -->
<div class="modal fade" id="drugTemplateModal" tabindex="-1" aria-labelledby="drugTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="drugTemplateModalLabel">
                    <i class="bi bi-bookmark-star me-2"></i>Drug Instruction Template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Saved per doctor. These values auto-fill the prescription form whenever you pick this drug during an appointment.
                </p>
                <div class="mb-3">
                    <label class="form-label">Drug</label>
                    <input type="text" class="form-control" id="tplDrugName" readonly>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dose</label>
                        <input type="text" class="form-control" id="tplDose" placeholder="e.g., 1 tablet">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Frequency</label>
                        <input type="text" class="form-control" id="tplFrequency" placeholder="e.g., Twice daily">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" id="tplDuration" placeholder="e.g., 7 days">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Route</label>
                    <select class="form-select" id="tplRoute">
                        <option value="">— None —</option>
                        <option value="Topical">Topical</option>
                        <option value="Oral">Oral</option>
                        <option value="IV">IV</option>
                        <option value="IM">IM</option>
                        <option value="Sublingual">Sublingual</option>
                        <option value="Inhalation">Inhalation</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Instructions</label>
                    <textarea class="form-control" id="tplInstructions" rows="3" placeholder="e.g., After meals, avoid sunlight..."></textarea>
                </div>
                <div id="tplStatus" class="small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="tplClearBtn" onclick="confirmDeleteDrugTemplate()" style="display: none;">
                    <i class="bi bi-trash me-1"></i>Delete template
                </button>
                <button type="button" class="btn btn-outline-secondary me-auto" id="tplClearFieldsBtn" onclick="clearDrugTemplateFields()" title="Clear dose, frequency, duration, route and instructions">
                    <i class="bi bi-eraser me-1"></i>Clear fields
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveDrugTemplateFromModal()">
                    <i class="bi bi-save me-1"></i>Save template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Drug Instruction Template — confirmation -->
<div class="modal fade" id="drugTplDeleteModal" tabindex="-1" aria-labelledby="drugTplDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="drugTplDeleteModalLabel"><i class="bi bi-trash me-2 text-danger"></i>Delete template?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Delete the saved instruction template for <strong id="drugTplDeleteName"></strong>? This can't be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="drugTplDeleteConfirmBtn" onclick="doDeleteDrugTemplate()">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Database Modal -->
<div class="modal fade" id="updateDatabaseModal" tabindex="-1" aria-labelledby="updateDatabaseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="updateDatabaseModalLabel">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Update Drugs Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="updateProgressContainer">
                    <div class="mb-3">
                        <p class="text-muted">The drugs database will be downloaded and updated from the official
                            source.</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> This process may take a few minutes depending on the data size.
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0" id="progressLabel">Preparing...</label>
                            <div id="updateSpinner" style="display: none;">
                                <div class="spinner-border" role="status"
                                    style="width: 1.5rem; height: 1.5rem; border-width: 3px; border-color: #0dcaf0; border-right-color: transparent;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                id="updateProgressBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                aria-valuemax="100">
                                <span id="progressText">0%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Messages -->
                    <div id="updateStatusMessages" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                        <!-- Status messages will be added here -->
                    </div>

                    <!-- Statistics -->
                    <div id="updateStatistics" class="row mt-3" style="display: none;">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6>Total Records</h6>
                                    <h3 id="totalRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6>Inserted</h6>
                                    <h3 id="insertedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6>Updated</h6>
                                    <h3 id="updatedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    id="closeUpdateModalBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="startUpdateBtn" onclick="startDatabaseUpdate()">
                    <i class="bi bi-play-circle me-2"></i>
                    Start Update
                </button>
            </div>
        </div>
    </div>
</div>

<script
    src="/app/Views/doctor/assets/js/drugs.js?v=<?= file_exists(__DIR__ . '/assets/js/drugs.js') ? filemtime(__DIR__ . '/assets/js/drugs.js') : time() ?>"></script>

<style>
.dark .modal-content {
        background: rgba(11, 18, 32, 0.8) !important;
    }

    .modal-content {
        background: var(--card) !important;
    }
</style>