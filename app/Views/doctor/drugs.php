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
            <button class="btn btn-outline-primary" id="clearSearchBtn" style="display: none;">
                <i class="bi bi-x-circle me-2"></i>
                Clear Search
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
                        <button class="btn btn-primary btn-lg w-100" id="searchBtn">
                            <i class="bi bi-search me-2"></i>
                            Search
                        </button>
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

<style>
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

/* Modal Styles - Using same pattern as patients.php */
.modal-content {
    background-color: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
}

.modal-header {
    background-color: var(--bg);
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

.modal-body {
    background-color: var(--card);
    color: var(--text);
}

.modal-footer {
    background-color: var(--bg);
    border-top: 1px solid var(--border);
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
    background-color: var(--bg);
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
        
        // Show/hide clear button
        if (searchTerm.length > 0) {
            this.clearBtn.style.display = 'block';
        } else {
            this.clearBtn.style.display = 'none';
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
        
        console.log('Performing search:', {
            searchTerm,
            showSuggestions,
            page: this.currentPage,
            filters: {
                category: this.categoryFilter.value,
                company: this.companyFilter.value,
                route: this.routeFilter.value
            }
        });
        
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
                console.log('Added category filter:', this.categoryFilter.value);
            }
            if (this.companyFilter.value) {
                params.append('company', this.companyFilter.value);
                console.log('Added company filter:', this.companyFilter.value);
            }
            if (this.routeFilter.value) {
                params.append('route', this.routeFilter.value);
                console.log('Added route filter:', this.routeFilter.value);
            }
            
            console.log('API URL:', `/api/searchDrugs?${params}`);
            const response = await fetch(`/api/searchDrugs?${params}`);
            const data = await response.json();
            console.log('API Response:', data);
            
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
        console.log('Displaying suggestions:', drugs);
        
        // Clear previous
        this.portal.innerHTML = '';
        if (!drugs || drugs.length === 0) {
            console.log('No suggestions to display');
            this.portal.style.display = 'none';
            return;
        }

        console.log('Showing', drugs.length, 'suggestions');
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
        console.log('Filter changed:', {
            category: this.categoryFilter.value,
            company: this.companyFilter.value,
            route: this.routeFilter.value
        });
        
        // Just update the UI state, don't auto-apply filters
        this.updateFilterState();
    }
    
    updateFilterState() {
        const hasFilters = this.categoryFilter.value || this.companyFilter.value || this.routeFilter.value;
        console.log('Filter state updated:', { hasFilters });
        
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
        console.log('Applying filters...');
        this.currentPage = 1;
        
        // Check if any filters are selected
        const hasFilters = this.categoryFilter.value || this.companyFilter.value || this.routeFilter.value;
        console.log('Has filters:', hasFilters);
        
        if (this.currentSearchTerm) {
            // If there's a search term, search with filters
            console.log('Searching with term and filters:', this.currentSearchTerm);
            this.performSearch(this.currentSearchTerm, false);
        } else if (hasFilters) {
            // If no search term but filters are applied, show filtered results
            console.log('Showing filtered results only');
            this.performSearch('', false);
        } else {
            // If no search term and no filters, clear results
            console.log('No filters applied, clearing results');
            this.clearResults();
        }
    }
    
    clearFilters() {
        console.log('Clearing filters...');
        this.categoryFilter.value = '';
        this.companyFilter.value = '';
        this.routeFilter.value = '';
        
        // Update filter state
        this.updateFilterState();
        
        if (this.currentSearchTerm) {
            console.log('Re-searching with cleared filters:', this.currentSearchTerm);
            this.currentPage = 1;
            this.performSearch(this.currentSearchTerm, false);
        } else {
            console.log('Clearing results (no search term)');
            this.clearResults();
        }
    }
    
    clearSearch() {
        this.searchInput.value = '';
        this.clearBtn.style.display = 'none';
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
</script>
