
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
        this.shouldOpenFirstResult = false; // Flag to open first result modal after search
        
        this.init();
    }
    
    init() {
        this.createPortal();
        this.setupEventListeners();
        this.loadFilterOptions();
        this.updateFilterState(); // Initialize filter button state
        
        // Check if search parameter exists in URL and perform search
        this.checkUrlSearchParam();
    }
    
    checkUrlSearchParam() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        const drugIdParam = urlParams.get('drugId');
        
        // If drugId is provided, open that drug's modal directly
        if (drugIdParam) {
            this.showDrugDetails(parseInt(drugIdParam));
            return;
        }
        
        if (searchParam && searchParam.trim().length > 0) {
            const searchTerm = searchParam.trim();
            // Set the search input value
            this.searchInput.value = searchTerm;
            // Perform the search automatically
            this.currentSearchTerm = searchTerm;
            this.currentPage = 1;
            // Set flag to open first result modal after search completes
            this.shouldOpenFirstResult = true;
            this.performSearch(searchTerm, false);
            
            // Update URL without reload (remove search parameter to keep clean URL)
            // But keep it for now so user can see what they searched for
            // window.history.replaceState({}, '', window.location.pathname);
        }
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
                    // If search was triggered from URL and we should open first result
                    if (this.shouldOpenFirstResult && data.drugs.length > 0 && this.currentPage === 1) {
                        // Small delay to ensure DOM is updated
                        setTimeout(() => {
                            this.showDrugDetails(data.drugs[0].ID);
                            this.shouldOpenFirstResult = false; // Reset flag
                        }, 300);
                    }
                }
                this.hasMoreResults = data.drugs.length === 20;
            } else {
                this.showNoResults();
                this.shouldOpenFirstResult = false; // Reset flag if no results
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
        
        // Remove any existing backdrops (from global search or other modals)
        const existingBackdrops = document.querySelectorAll('.global-search-backdrop, .modal-backdrop');
        existingBackdrops.forEach(backdrop => backdrop.remove());
        
        // Ensure body overflow is restored
        document.body.style.overflow = '';
        
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