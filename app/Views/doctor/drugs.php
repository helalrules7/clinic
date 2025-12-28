<link href="/app/Views/doctor/assets/css/drugs.css?v=<?= file_exists(__DIR__ . '/assets/css/drugs.css') ? filemtime(__DIR__ . '/assets/css/drugs.css') : time() ?>" rel="stylesheet">

<!-- Drug Search Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary">
            <i class="bi bi-capsule me-2"></i>
            Drug Search
        </h4>
        <p class="text-muted mb-0">Search and browse medications database</p>
        <div class="mt-2">
            <small class="text-muted">
                <i class="bi bi-keyboard me-1"></i>
                Shortcuts: 
                • Search <kbd class="me-1">F</kbd> or <kbd class="me-1">ب</kbd>
                • Clear <kbd class="me-1">Esc</kbd>
            </small>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group" role="group">
            <button class="day-close-btn" id="updateDatabaseBtn" onclick="showUpdateDatabaseModal()">
                <i class="bi bi-arrow-clockwise me-2"></i>
                Update Database
            </button>
        </div>
    </div>
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
                            <input 
                                type="text" 
                                id="drugSearchInput" 
                                class="form-control form-control-lg" 
                                placeholder="Search for medications, active ingredients, or companies..."
                                autocomplete="off"
                            >
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
                            <button class="btn btn-outline-primary flex-grow-1" id="clearSearchBtn" style="display: none;">
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
                    <h6 class="mb-0 text-success">
                        <i class="bi bi-list-ul me-2"></i>
                        Search Results
                    </h6>
                    <span class="badge bg-success" id="resultsCount">0 medications found</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Searching medications...</p>
                </div>
                
                <!-- No Results -->
                <div id="noResults" class="text-center py-5" style="display: none;">
                    <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No medications found</h5>
                    <p class="text-muted">Try adjusting your search terms or filters</p>
                </div>
                
                <!-- Results Grid -->
                <div id="drugResults" class="row">
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDrugName">Drug Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDrugDetails">
                <!-- Drug details will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Database Modal -->
<div class="modal fade" id="updateDatabaseModal" tabindex="-1" aria-labelledby="updateDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="updateDatabaseModalLabel">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Update Drugs Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="updateProgressContainer">
                    <div class="mb-3">
                        <p class="text-muted">The drugs database will be downloaded and updated from the official source.</p>
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
                                <div class="spinner-border" role="status" style="width: 1.5rem; height: 1.5rem; border-width: 3px; border-color: #0dcaf0; border-right-color: transparent;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 id="updateProgressBar" 
                                 style="width: 0%"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeUpdateModalBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="startUpdateBtn" onclick="startDatabaseUpdate()">
                    <i class="bi bi-play-circle me-2"></i>
                    Start Update
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/app/Views/doctor/assets/js/drugs.js?v=<?= file_exists(__DIR__ . '/assets/js/drugs.js') ? filemtime(__DIR__ . '/assets/js/drugs.js') : time() ?>"></script>

<style>
    .modal-backdrop.show{
        display: none !important;
    }
    body > div.modal-backdrop.fade.show{
        display: none !important;
    }
    .dark .modal-content{
    background: rgba(11, 18, 32, 0.8) !important;
    }
    .modal-content{
    background: rgba(248, 250, 252, 0.8) !important;
    }
</style>