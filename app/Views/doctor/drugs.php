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

<style>
/* Update Database Button Glass Style with Blue Gradient - Same as Day Close Button */
.day-close-btn {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.4), rgba(37, 99, 235, 0.5)) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(96, 165, 250, 0.4) !important;
    box-shadow: 2px 0 8px 0 rgba(59, 130, 246, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.2);
    color: white !important;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.day-close-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.day-close-btn:hover::before {
    left: 100%;
}

.day-close-btn:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.55), rgba(37, 99, 235, 0.65)) !important;
    transform: translateY(-2px);
    box-shadow: 4px 4px 16px 0 rgba(59, 130, 246, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.3);
    color: white !important;
    text-decoration: none;
    border-color: rgba(96, 165, 250, 0.6) !important;
}

.day-close-btn i {
    font-size: 1.3rem;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.2));
}

.dark .day-close-btn {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.5), rgba(29, 78, 216, 0.6)) !important;
    border: 1px solid rgba(96, 165, 250, 0.5) !important;
    box-shadow: 2px 0 8px 0 rgba(37, 99, 235, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.dark .day-close-btn:hover {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.65), rgba(29, 78, 216, 0.75)) !important;
    box-shadow: 4px 4px 16px 0 rgba(37, 99, 235, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
    border-color: rgba(96, 165, 250, 0.7) !important;
}

/* Drug Card Styles */
.drug-card {
    background-color: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
    height: 100%;
}

.drug-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: var(--accent);
}

.drug-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.drug-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text);
    margin: 0 0 0.25rem 0;
}

.drug-ingredient {
    color: var(--muted);
    font-size: 0.875rem;
    margin: 0;
}

.drug-price {
    font-size: 1rem;
    font-weight: 600;
    color: var(--success);
}

.drug-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
    margin-top: 0.75rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 0.75rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.detail-value {
    font-size: 0.875rem;
    color: var(--text);
    font-weight: 500;
}

/* Auto-complete suggestions - Portal approach */
#searchSuggestions-portal {
    position: absolute;
    z-index: 9999;
    background: var(--card, white);
    border: 1px solid var(--border, #dee2e6);
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 240px;
    overflow-y: auto;
    display: none;
}

.suggestion-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.15s ease-in-out;
}

.suggestion-item:hover {
    background-color: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item .drug-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #212529;
    margin-bottom: 0.125rem;
}

.suggestion-item .drug-company {
    font-size: 0.75rem;
    color: #6c757d;
}

/* Dark mode support */
.dark #searchSuggestions-portal {
    background: var(--card);
    border-color: var(--border);
    color: var(--text) !important;
}

.dark .suggestion-item {
    border-bottom-color: var(--border);
    color: var(--text) !important;
}

.dark .suggestion-item:hover {
    background-color: var(--bg);
}

.dark .suggestion-item .drug-name {
    color: var(--text) !important;
    font-weight: 700;
}

.dark .suggestion-item .drug-company {
    color: var(--muted) !important;
    font-weight: 500;
}

.dark .modal-content {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .modal-header {
    background-color: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

.dark .modal-body {
    background-color: var(--card);
    color: var(--text);
}

.dark .modal-footer {
    background-color: var(--bg);
    border-top-color: var(--border);
}

.dark .form-control {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-control:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.dark .form-select {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.dark .card {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .card-header {
    background-color: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

/* Modal Styles - Glass Effect */
.modal-content {
    /* Glass effect - similar to sidebar */
    background: rgba(248, 250, 252, 0.35) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
    color: var(--text) !important;
}

.dark .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(226, 232, 240, 0.3) !important;
    color: var(--text) !important;
}

.dark .modal-header {
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
}

/* Close button white in dark mode */
.dark .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

.dark .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

/* Enable dragging */
.modal-content {
    cursor: move;
}

.modal-dialog {
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
}

.modal-header {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.modal-body {
    background: transparent !important;
    color: var(--text) !important;
}

.modal-footer {
    background: transparent !important;
    border-top: 1px solid rgba(226, 232, 240, 0.3) !important;
}

.dark .modal-footer {
    border-top-color: rgba(51, 65, 85, 0.3) !important;
}

/* Modal Alerts and Cards Dark Mode Support */
.modal-body .text-muted {
    color: var(--muted) !important;
}

.modal-body .form-label {
    color: var(--text);
}

.modal-body .alert {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.modal-body .alert-info {
    background-color: rgba(13, 202, 240, 0.1);
    border-color: rgba(13, 202, 240, 0.3);
    color: var(--text);
}

.modal-body .alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.3);
    color: var(--text);
}

.modal-body .alert-warning {
    background-color: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
    color: var(--text);
}

.modal-body .alert-danger {
    background-color: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: var(--text);
}

.modal-body .card {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.modal-body .card.bg-primary,
.modal-body .card.bg-success,
.modal-body .card.bg-info {
    color: white !important;
}

.modal-body .progress {
    background-color: var(--bg);
}

.modal-body .progress-bar {
    background-color: var(--success);
}

.modal-header.bg-success {
    background-color: var(--success) !important;
    color: white !important;
}

/* Form controls - Using same pattern as patients.php */
.form-control {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.form-control:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.form-select {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.form-select:focus {
    background-color: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

/* Card styling - Using same pattern as patients.php */
.card {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.card-header {
    background-color: transparent !important;
    border-bottom-color: var(--border);
    color: var(--text);
}

/* Text colors - Using same pattern as patients.php */
.text-primary {
    color: var(--accent) !important;
}

.text-muted {
    color: var(--muted) !important;
}

.text-success {
    color: var(--success) !important;
}

/* Button styling - Using same pattern as patients.php */
.btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-primary {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
    opacity: 0.9;
}

.btn-secondary {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.btn-secondary:hover {
    background-color: var(--border);
    border-color: var(--border);
    color: var(--text);
}

/* Badge styling - Using same pattern as patients.php */
.badge.bg-success {
    background-color: var(--success) !important;
    color: white;
}

.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

/* Ensure search input container has proper positioning context */
.position-relative {
    position: relative;
}

/* Responsive Design */
@media (max-width: 768px) {
    .drug-details {
        grid-template-columns: 1fr;
    }
    
    .drug-card-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .drug-price {
        margin-top: 0.5rem;
    }
}
</style>

<script>
class DrugSearch {
    constructor() {
        this.searchInput = document.getElementById('drugSearchInput');
        this.suggestions = document.getElementById('searchSuggestions');
        this.clearBtn = document.getElementById('clearSearchBtn');
        this.searchBtn = document.getElementById('searchBtn');
        this.resultsContainer = document.getElementById('drugResults');
        this.loadingIndicator = document.getElementById('loadingIndicator');
        this.noResults = document.getElementById('noResults');
        this.resultsCount = document.getElementById('resultsCount');
        this.resultsTitle = document.getElementById('resultsTitle');
        this.loadMoreBtn = document.getElementById('loadMoreBtn');
        this.loadMoreContainer = document.getElementById('loadMoreContainer');
        
        this.categoryFilter = document.getElementById('categoryFilter');
        this.companyFilter = document.getElementById('companyFilter');
        this.routeFilter = document.getElementById('routeFilter');
        this.applyFiltersBtn = document.getElementById('applyFiltersBtn');
        this.clearFiltersBtn = document.getElementById('clearFiltersBtn');
        
        this.currentPage = 1;
        this.currentSearchTerm = '';
        this.isLoading = false;
        this.hasMoreResults = false;
        this.portal = null;
        this._portalUpdater = null;
        
        this.init();
    }
    
    init() {
        this.createPortal();
        this.setupEventListeners();
        this.loadFilterOptions();
        this.updateFilterState(); // Initialize filter button state
    }
    
    createPortal() {
        this.portal = document.getElementById('searchSuggestions-portal');
        if (!this.portal) {
            this.portal = document.createElement('div');
            this.portal.id = 'searchSuggestions-portal';
            this.portal.setAttribute('role', 'listbox');
            document.body.appendChild(this.portal);
        }
        this.portal.style.display = 'none';
        this.portal.classList.add('shadow-sm');
    }
    
    positionPortal() {
        const rect = this.searchInput.getBoundingClientRect();
        this.portal.style.minWidth = rect.width + 'px';
        this.portal.style.left = (window.scrollX + rect.left) + 'px';
        this.portal.style.top = (window.scrollY + rect.bottom) + 'px';
    }
    
    setupEventListeners() {
        // Search input events
        this.searchInput.addEventListener('input', this.debounce(this.handleSearch.bind(this), 300));
        this.searchInput.addEventListener('focus', this.showSuggestions.bind(this));
        this.searchInput.addEventListener('blur', this.hideSuggestions.bind(this));
        
        // Search button
        const searchBtn = document.getElementById('searchBtn');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.performSearch(this.searchInput.value.trim(), false);
            });
        }
        
        // Clear search button
        this.clearBtn.addEventListener('click', this.clearSearch.bind(this));
        
        // Filter events
        this.categoryFilter.addEventListener('change', this.handleFilterChange.bind(this));
        this.companyFilter.addEventListener('change', this.handleFilterChange.bind(this));
        this.routeFilter.addEventListener('change', this.handleFilterChange.bind(this));
        this.applyFiltersBtn.addEventListener('click', this.applyFilters.bind(this));
        this.clearFiltersBtn.addEventListener('click', this.clearFilters.bind(this));
        
        // Load more button
        this.loadMoreBtn.addEventListener('click', this.loadMoreResults.bind(this));
        
        // Modal events
        this.setupModalEvents();
    }
    
    setupModalEvents() {
        // Bootstrap modal events are handled automatically
        // No need for custom modal event handlers
    }
    
    async loadFilterOptions() {
        try {
            const response = await fetch('/api/getFilterOptions');
            const data = await response.json();
            
            if (data.categories) {
                this.populateSelect(this.categoryFilter, data.categories);
            }
            
            if (data.companies) {
                this.populateSelect(this.companyFilter, data.companies);
            }
            
            if (data.routes) {
                this.populateSelect(this.routeFilter, data.routes);
            }
        } catch (error) {
            console.error('Error loading filter options:', error);
        }
    }
    
    populateSelect(selectElement, options) {
        // Clear existing options except the first one
        while (selectElement.children.length > 1) {
            selectElement.removeChild(selectElement.lastChild);
        }
        
        // Add new options
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option;
            optionElement.textContent = option;
            selectElement.appendChild(optionElement);
        });
    }
    
    async handleSearch() {
        const searchTerm = this.searchInput.value.trim();
        
        // Show/hide clear button and adjust button widths
        if (searchTerm.length > 0) {
            this.clearBtn.style.display = 'block';
            // Make search button 50% width and clear button 50% width (both in same line)
            if (this.searchBtn) {
                this.searchBtn.classList.remove('w-100');
                this.searchBtn.classList.add('flex-grow-1');
            }
        } else {
            this.clearBtn.style.display = 'none';
            // Reset search button to full width
            if (this.searchBtn) {
                this.searchBtn.classList.remove('flex-grow-1');
                this.searchBtn.classList.add('w-100');
            }
        }
        
        if (searchTerm.length < 2) {
            this.hideSuggestions();
            this.clearResults();
            return;
        }
        
        this.currentSearchTerm = searchTerm;
        this.currentPage = 1;
        
        await this.performSearch(searchTerm, true);
    }
    
    async performSearch(searchTerm, showSuggestions = false) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const params = new URLSearchParams({
                q: searchTerm,
                limit: 20,
                page: this.currentPage
            });
            
            // Add filters
            if (this.categoryFilter.value) {
                params.append('category', this.categoryFilter.value);
            }
            if (this.companyFilter.value) {
                params.append('company', this.companyFilter.value);
            }
            if (this.routeFilter.value) {
                params.append('route', this.routeFilter.value);
            }
            
            const response = await fetch(`/api/searchDrugs?${params}`);
            const data = await response.json();
            
            if (data.drugs) {
                if (showSuggestions) {
                    this.displaySuggestions(data.drugs);
                } else {
                    this.displayResults(data.drugs, this.currentPage === 1);
                }
                this.hasMoreResults = data.drugs.length === 20;
            } else {
                this.showNoResults();
            }
            
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Failed to search medications');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    displaySuggestions(drugs) {
        
        // Clear previous
        this.portal.innerHTML = '';
        if (!drugs || drugs.length === 0) {
            this.portal.style.display = 'none';
            return;
        }

        drugs.slice(0, 8).forEach((drug, idx) => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.setAttribute('role', 'option');
            item.id = `suggestion-${idx}`;
            item.innerHTML = `
                <div class="drug-name">${drug.drug_name}</div>
                <div class="drug-company">${drug.active_ingredient || ''} ${drug.Company ? '- ' + drug.Company : ''}</div>
            `;
            item.addEventListener('click', () => {
                this.searchInput.value = drug.drug_name;
                this.hideSuggestions();
                this.performSearch(drug.drug_name, false);
            });
            this.portal.appendChild(item);
        });

        this.positionPortal();
        this.portal.style.display = 'block';

        // Register listeners to reposition on scroll/resize
        this._portalUpdater = () => this.positionPortal();
        window.addEventListener('scroll', this._portalUpdater, true);
        window.addEventListener('resize', this._portalUpdater);
    }
    
    displayResults(drugs, clearPrevious = true) {
        if (clearPrevious) {
            this.resultsContainer.innerHTML = '';
            this.currentPage = 1;
        }
        
        if (drugs.length === 0) {
            this.showNoResults();
            return;
        }
        
        drugs.forEach(drug => {
            const drugCard = this.createDrugCard(drug);
            this.resultsContainer.appendChild(drugCard);
        });
        
        this.updateResultsCount(drugs.length, clearPrevious);
        this.updateLoadMoreButton();
        this.hideNoResults();
    }
    
    createDrugCard(drug) {
        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4 mb-3';
        
        card.innerHTML = `
            <div class="drug-card">
                <div class="drug-card-header">
                    <div>
                        <h5 class="drug-name">${drug.drug_name}</h5>
                        <p class="drug-ingredient">${drug.active_ingredient}</p>
                    </div>
                    <div class="drug-price">${drug.price ? 'EGP ' + drug.price : 'Price N/A'}</div>
                </div>
                <div class="drug-details">
                    <div class="detail-item">
                        <span class="detail-label">Company</span>
                        <span class="detail-value">${drug.Company || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Category</span>
                        <span class="detail-value">${drug.category || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Route</span>
                        <span class="detail-value">${drug.administration_route || 'N/A'}</span>
                    </div>
                </div>
            </div>
        `;
        
        card.addEventListener('click', () => {
            this.showDrugDetails(drug.ID);
        });
        
        return card;
    }
    
    async showDrugDetails(drugId) {
        try {
            const response = await fetch(`/api/getDrugDetails?id=${drugId}`);
            const data = await response.json();
            
            if (data.drug) {
                this.displayDrugModal(data.drug);
            }
        } catch (error) {
            console.error('Error fetching drug details:', error);
        }
    }
    
    displayDrugModal(drug) {
        const modal = document.getElementById('drugDetailsModal');
        const modalTitle = document.getElementById('modalDrugName');
        const modalBody = document.getElementById('modalDrugDetails');
        
        modalTitle.textContent = drug.drug_name;
        
        modalBody.innerHTML = `
            <div class="mb-3">
                <h5 class="text-primary mb-2">Active Ingredient</h5>
                <p class="text-muted mb-0">${drug.active_ingredient || 'N/A'}</p>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <h6 class="text-primary mb-1">Company</h6>
                    <p class="text-muted mb-0">${drug.Company || 'N/A'}</p>
                </div>
                <div class="col-md-6 mb-2">
                    <h6 class="text-primary mb-1">Category</h6>
                    <p class="text-muted mb-0">${drug.category || 'N/A'}</p>
                </div>
                <div class="col-md-6 mb-2">
                    <h6 class="text-primary mb-1">Price</h6>
                    <p class="text-success fw-bold mb-0">${drug.price ? 'EGP ' + drug.price : 'N/A'}</p>
                </div>
                <div class="col-md-6 mb-2">
                    <h6 class="text-primary mb-1">Route</h6>
                    <p class="text-muted mb-0">${drug.administration_route || 'N/A'}</p>
                </div>
            </div>
            
            ${drug.GI ? `
                <div class="mb-3">
                    <h6 class="text-primary mb-2">General Information</h6>
                    <p class="text-muted mb-0" style="line-height: 1.6;">${drug.GI}</p>
                </div>
            ` : ''}
            
            ${drug.SRDE ? `
                <div>
                    <h6 class="text-primary mb-2">Additional Information</h6>
                    <p class="text-muted mb-0" style="line-height: 1.6;">${drug.SRDE}</p>
                </div>
            ` : ''}
        `;
        
        // Show Bootstrap modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    async loadMoreResults() {
        if (this.isLoading || !this.hasMoreResults) return;
        
        this.currentPage++;
        await this.performSearch(this.currentSearchTerm, false);
    }
    
    handleFilterChange() {
        // Just update the UI state, don't auto-apply filters
        this.updateFilterState();
    }
    
    updateFilterState() {
        const hasFilters = this.categoryFilter.value || this.companyFilter.value || this.routeFilter.value;
        
        // Update apply button state
        if (hasFilters) {
            this.applyFiltersBtn.classList.remove('btn-primary');
            this.applyFiltersBtn.classList.add('btn-success');
            this.applyFiltersBtn.innerHTML = '<i class="bi bi-funnel-fill me-1"></i>Apply Filters';
        } else {
            this.applyFiltersBtn.classList.remove('btn-success');
            this.applyFiltersBtn.classList.add('btn-primary');
            this.applyFiltersBtn.innerHTML = '<i class="bi bi-funnel me-1"></i>Apply Filters';
        }
    }
    
    applyFilters() {
        this.currentPage = 1;
        
        // Check if any filters are selected
        const hasFilters = this.categoryFilter.value || this.companyFilter.value || this.routeFilter.value;
        
        if (this.currentSearchTerm) {
            // If there's a search term, search with filters
            this.performSearch(this.currentSearchTerm, false);
        } else if (hasFilters) {
            // If no search term but filters are applied, show filtered results
            this.performSearch('', false);
        } else {
            // If no search term and no filters, clear results
            this.clearResults();
        }
    }
    
    clearFilters() {
        this.categoryFilter.value = '';
        this.companyFilter.value = '';
        this.routeFilter.value = '';
        
        // Update filter state
        this.updateFilterState();
        
        if (this.currentSearchTerm) {
            this.currentPage = 1;
            this.performSearch(this.currentSearchTerm, false);
        } else {
            this.clearResults();
        }
    }
    
    clearSearch() {
        this.searchInput.value = '';
        this.clearBtn.style.display = 'none';
        // Reset search button to full width
        if (this.searchBtn) {
            this.searchBtn.classList.remove('flex-grow-1');
            this.searchBtn.classList.add('w-100');
        }
        this.hideSuggestions();
        this.clearResults();
        this.searchInput.focus();
    }
    
    clearResults() {
        this.resultsContainer.innerHTML = '';
        this.resultsCount.textContent = '0 medications found';
        this.hideLoadMore();
        this.hideNoResults();
    }
    
    showSuggestions() {
        if (this.searchInput.value.trim().length >= 2) {
            this.portal.style.display = 'block';
        }
    }
    
    hideSuggestions() {
        if (this.portal) {
            this.portal.style.display = 'none';
        }
        // Cleanup listeners
        if (this._portalUpdater) {
            window.removeEventListener('scroll', this._portalUpdater, true);
            window.removeEventListener('resize', this._portalUpdater);
            this._portalUpdater = null;
        }
    }
    
    showLoading() {
        this.loadingIndicator.style.display = 'flex';
    }
    
    hideLoading() {
        this.loadingIndicator.style.display = 'none';
    }
    
    showNoResults() {
        this.noResults.style.display = 'block';
        this.hideLoadMore();
    }
    
    hideNoResults() {
        this.noResults.style.display = 'none';
    }
    
    updateResultsCount(newCount, clearPrevious) {
        if (clearPrevious) {
            this.resultsCount.textContent = `${newCount} medications found`;
        } else {
            const currentCount = parseInt(this.resultsCount.textContent) || 0;
            this.resultsCount.textContent = `${currentCount + newCount} medications found`;
        }
    }
    
    updateLoadMoreButton() {
        if (this.hasMoreResults) {
            this.loadMoreContainer.style.display = 'block';
        } else {
            this.hideLoadMore();
        }
    }
    
    hideLoadMore() {
        this.loadMoreContainer.style.display = 'none';
    }
    
    showError(message) {
        // You could implement a toast notification here
        console.error(message);
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Cleanup method to prevent memory leaks
    destroy() {
        this.hideSuggestions();
        if (this.portal && this.portal.parentNode) {
            this.portal.parentNode.removeChild(this.portal);
        }
    }
}

// Initialize the drug search when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new DrugSearch();
});

// Database Update Functions
function showUpdateDatabaseModal() {
    const modal = new bootstrap.Modal(document.getElementById('updateDatabaseModal'));
    modal.show();
    
    // Reset modal state
    resetUpdateModal();
}

function resetUpdateModal() {
    document.getElementById('updateProgressBar').style.width = '0%';
    document.getElementById('updateProgressBar').setAttribute('aria-valuenow', '0');
    document.getElementById('progressText').textContent = '0%';
    document.getElementById('progressLabel').textContent = 'Preparing...';
    document.getElementById('updateStatusMessages').innerHTML = '';
    document.getElementById('updateStatistics').style.display = 'none';
    document.getElementById('totalRecords').textContent = '0';
    document.getElementById('insertedRecords').textContent = '0';
    document.getElementById('updatedRecords').textContent = '0';
    document.getElementById('startUpdateBtn').disabled = false;
    document.getElementById('startUpdateBtn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Start Update';
    document.getElementById('closeUpdateModalBtn').disabled = false;
    document.getElementById('updateSpinner').style.display = 'none';
}

function startDatabaseUpdate() {
    const startBtn = document.getElementById('startUpdateBtn');
    const closeBtn = document.getElementById('closeUpdateModalBtn');
    
    // Disable buttons
    startBtn.disabled = true;
    closeBtn.disabled = true;
    startBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
    
    // Show spinner
    document.getElementById('updateSpinner').style.display = 'block';
    
    // Reset progress
    resetUpdateModal();
    
    // Show statistics
    document.getElementById('updateStatistics').style.display = 'flex';
    
    // Add initial status message
    addStatusMessage('info', 'Starting update process...');
    
    // Start update process
    updateDatabase();
}

function updateDatabase() {
    // Step 1: Downloading
    document.getElementById('progressLabel').textContent = 'Downloading database file...';
    updateProgress(10);
    addStatusMessage('info', 'Downloading database file from server...');
    
    // Simulate progress steps
    setTimeout(() => {
        updateProgress(30);
        addStatusMessage('success', 'Database downloaded successfully');
        
        // Step 2: Extracting data
        document.getElementById('progressLabel').textContent = 'Extracting data from source database...';
        updateProgress(50);
        addStatusMessage('info', 'Extracting data from source database...');
        
        setTimeout(() => {
            updateProgress(70);
            addStatusMessage('success', 'Data extracted successfully');
            
            // Step 3: Updating database
            document.getElementById('progressLabel').textContent = 'Updating data in current database...';
            updateProgress(80);
            addStatusMessage('info', 'Updating data in current database...');
            
            // Make API call
            fetch('/api/drugs/update-database', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateProgress(100);
                    document.getElementById('progressLabel').textContent = 'Update completed successfully!';
                    addStatusMessage('success', 'Database updated successfully!');
                    
                    // Hide spinner
                    document.getElementById('updateSpinner').style.display = 'none';
                    
                    // Update statistics
                    if (data.statistics) {
                        document.getElementById('totalRecords').textContent = data.statistics.total || 0;
                        document.getElementById('insertedRecords').textContent = data.statistics.inserted || 0;
                        document.getElementById('updatedRecords').textContent = data.statistics.updated || 0;
                    }
                    
                    // Enable close button
                    document.getElementById('closeUpdateModalBtn').disabled = false;
                    document.getElementById('closeUpdateModalBtn').textContent = 'Close';
                    
                    // Enable start button for potential retry
                    document.getElementById('startUpdateBtn').disabled = false;
                    document.getElementById('startUpdateBtn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Start Update';
                    
                    // Show success message
                    setTimeout(() => {
                        alert('Database updated successfully!');
                        location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.error || data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Update error:', error);
                updateProgress(0);
                document.getElementById('progressLabel').textContent = 'An error occurred during update';
                addStatusMessage('danger', 'Error: ' + error.message);
                
                // Hide spinner
                document.getElementById('updateSpinner').style.display = 'none';
                
                // Enable buttons
                document.getElementById('startUpdateBtn').disabled = false;
                document.getElementById('startUpdateBtn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Retry';
                document.getElementById('closeUpdateModalBtn').disabled = false;
            });
        }, 1000);
    }, 1500);
}

function updateProgress(percent) {
    const progressBar = document.getElementById('updateProgressBar');
    const progressText = document.getElementById('progressText');
    
    progressBar.style.width = percent + '%';
    progressBar.setAttribute('aria-valuenow', percent);
    progressText.textContent = percent + '%';
}

function addStatusMessage(type, message) {
    const container = document.getElementById('updateStatusMessages');
    const alertClass = {
        'info': 'alert-info',
        'success': 'alert-success',
        'warning': 'alert-warning',
        'danger': 'alert-danger'
    }[type] || 'alert-info';
    
    const icon = {
        'info': 'bi-info-circle',
        'success': 'bi-check-circle',
        'warning': 'bi-exclamation-triangle',
        'danger': 'bi-x-circle'
    }[type] || 'bi-info-circle';
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    messageDiv.innerHTML = `
        <i class="bi ${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(messageDiv);
    
    // Auto scroll to bottom
    container.scrollTop = container.scrollHeight;
}
</script>

