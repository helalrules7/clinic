
let searchTimeout;
let currentSearchRequest;

// Pagination state
let paginationState = {
    currentPage: 1,
    itemsPerPage: 20,
    totalItems: 0,
    allPatients: [],
    filteredPatients: [],
    currentDoctorFilter: 'all',
    currentGenderFilter: null, // 'Male', 'Female', or null
    currentAgeFilter: { min: null, max: null }, // Age range filter
    currentLastVisitFilter: { from: null, to: null }, // Last visit date range filter
    sortBy: null,
    sortOrder: null
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

// View mode state
let currentViewMode = localStorage.getItem('patientsViewMode') || 'table';
let foldersData = [];

// Initialize pagination with PHP data
function initializePagination() {
    // Get patients data from PHP
    const patientsData = window.PATIENTS_CONFIG.patients;
    const doctorsData = window.PATIENTS_CONFIG.doctors;
    
    paginationState.allPatients = patientsData;
    paginationState.filteredPatients = [...patientsData];
    paginationState.totalItems = patientsData.length;
    paginationState.doctors = doctorsData;
    
    // Apply initial doctor filter
    applyDoctorFilter();
    
    // Load folders if folders view is active
    if (currentViewMode === 'folders') {
        loadFolders();
    }
    
    // Render initial page based on view mode
    switchViewMode(currentViewMode, false);
    
    // Initialize sort button states if sort is active
    if (paginationState.sortBy && paginationState.sortOrder) {
        const activeBtn = document.querySelector(`[data-sort="${paginationState.sortBy}"][data-order="${paginationState.sortOrder}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }
}

// Switch view mode
function switchViewMode(mode, saveToStorage = true) {
    currentViewMode = mode;
    
    if (saveToStorage) {
        localStorage.setItem('patientsViewMode', mode);
    }
    
    // Update toggle buttons (both toggles)
    document.querySelectorAll('#viewModeToggle button, #viewModeToggleCards button').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === mode) {
            btn.classList.add('active');
        }
    });
    
    // Hide all views
    document.getElementById('patientsTableCard').style.display = 'none';
    document.getElementById('patientsCardsCard').style.display = 'none';
    document.getElementById('patientsFoldersCard').style.display = 'none';
    
    // Show/hide Filter by Doctor div
    const doctorFilterRow = document.querySelector('.row.mb-3');
    if (doctorFilterRow && doctorFilterRow.querySelector('#doctorFilterGroup')) {
        if (mode === 'table') {
            doctorFilterRow.style.display = 'block';
        } else {
            doctorFilterRow.style.display = 'none';
        }
    }
    
    // Show selected view
    switch(mode) {
        case 'table':
            document.getElementById('patientsTableCard').style.display = 'block';
            renderPatientsTable();
            updatePaginationInfo();
            renderPaginationNav();
            stopFoldersAutoRefresh();
            break;
        case 'cards':
            document.getElementById('patientsCardsCard').style.display = 'block';
            renderPatientsCards();
            updatePaginationInfoCards();
            renderPaginationNavCards();
            stopFoldersAutoRefresh();
            break;
        case 'folders':
            document.getElementById('patientsFoldersCard').style.display = 'block';
            if (foldersData.length === 0) {
                loadFolders();
            } else {
                renderFoldersView();
            }
            // Start auto-refresh for folders
            startFoldersAutoRefresh();
            break;
    }
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
                    <td colspan="7" class="text-center py-4">
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
                                    <h6 class="mb-1">
                                        <a href="/doctor/patients/${patient.id}" 
                                           class="patient-name-link" 
                                           style="text-decoration: none; color: var(--accent) !important; font-weight: 600; transition: all 0.2s ease;" 
                                           onmouseover="this.style.color='var(--text) !important'; this.style.fontWeight='800 !important'; this.style.textDecoration='none';" 
                                           onmouseout="this.style.color='var(--accent) !important'; this.style.fontWeight='600 !important'; this.style.textDecoration='none';">
                                            ${escapeHtml(fullName)}
                                        </a>
                                    </h6>
                                    <small class="text-muted">ID: #${patient.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            ${patient.phone ? `
                                <div class="phone-number-container" style="position: relative; display: inline-block;">
                                    <a href="tel:${escapeHtml(patient.phone)}" 
                                       class="phone-number-link" 
                                       style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                        <i class="bi bi-telephone me-1"></i>
                                        ${escapeHtml(patient.phone)}
                                    </a>
                                    <span class="phone-htooltip">
                                        <div class="phone-actions">
                                            <a href="tel:${escapeHtml(patient.phone)}" class="phone-action-btn" title="Call">
                                                <i class="bi bi-telephone-fill"></i>
                                                <span>Call</span>
                                            </a>
                                            <a href="https://wa.me/+2${escapeHtml(patient.phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                                <span>WhatsApp</span>
                                            </a>
                                        </div>
                                    </span>
                                </div>
                            ` : '<span class="text-muted">Not available</span>'}
                            ${patient.alt_phone ? `
                                <div class="phone-number-container mt-1" style="position: relative; display: inline-block;">
                                    <a href="tel:${escapeHtml(patient.alt_phone)}" 
                                       class="phone-number-link" 
                                       style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                        <i class="bi bi-telephone-plus me-1"></i>
                                        <small>${escapeHtml(patient.alt_phone)}</small>
                                    </a>
                                    <span class="phone-htooltip">
                                        <div class="phone-actions">
                                            <a href="tel:${escapeHtml(patient.alt_phone)}" class="phone-action-btn" title="Call">
                                                <i class="bi bi-telephone-fill"></i>
                                                <span>Call</span>
                                            </a>
                                            <a href="https://wa.me/+2${escapeHtml(patient.alt_phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                                <span>WhatsApp</span>
                                            </a>
                                        </div>
                                    </span>
                                </div>
                            ` : ''}
                        </td>
                        <td>
                            ${patient.gender ? `
                                <span class="badge ${patient.gender === 'Female' ? 'bg-pink' : 'bg-primary'}" style="font-size: 0.875rem; padding: 0.4rem 0.6rem;">
                                    <i class="bi ${patient.gender === 'Female' ? 'bi-gender-female' : 'bi-gender-male'} me-1"></i>
                                    ${escapeHtml(patient.gender)}
                                </span>
                            ` : '<span class="text-muted">Not specified</span>'}
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
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   data-bs-title="View patient details and medical history">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="editPatient(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Edit patient information and details">
                                    <i class="bi bi-pencil"></i>
                                </button>
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
    
    // Update clear filters button visibility
    updateClearFiltersVisibility();
}

// Apply doctor filter to patients
function applyDoctorFilter() {
    const { currentDoctorFilter, currentGenderFilter, currentAgeFilter, currentLastVisitFilter, allPatients } = paginationState;
    
    let filtered = [...allPatients];
    
    // Apply doctor filter
    if (currentDoctorFilter !== 'all') {
        filtered = filtered.filter(patient => {
            return patient.created_by_doctor_id == currentDoctorFilter;
        });
    }
    
    // Apply gender filter
    if (currentGenderFilter) {
        filtered = filtered.filter(patient => {
            return patient.gender === currentGenderFilter;
        });
    }
    
    // Apply age filter
    if (currentAgeFilter.min !== null || currentAgeFilter.max !== null) {
        filtered = filtered.filter(patient => {
            if (!patient.dob) return false;
            const age = calculateAge(patient.dob);
            
            if (currentAgeFilter.min !== null && age < currentAgeFilter.min) {
                return false;
            }
            if (currentAgeFilter.max !== null && age > currentAgeFilter.max) {
                return false;
            }
            return true;
        });
    }
    
    // Apply last visit filter
    if (currentLastVisitFilter.from !== null || currentLastVisitFilter.to !== null) {
        filtered = filtered.filter(patient => {
            if (!patient.last_visit) {
                // If filtering by date range and patient has no visit, exclude them
                return false;
            }
            
            const visitDate = new Date(patient.last_visit);
            visitDate.setHours(0, 0, 0, 0);
            
            if (currentLastVisitFilter.from !== null) {
                const fromDate = new Date(currentLastVisitFilter.from);
                fromDate.setHours(0, 0, 0, 0);
                if (visitDate < fromDate) {
                    return false;
                }
            }
            
            if (currentLastVisitFilter.to !== null) {
                const toDate = new Date(currentLastVisitFilter.to);
                toDate.setHours(23, 59, 59, 999);
                if (visitDate > toDate) {
                    return false;
                }
            }
            
            return true;
        });
    }
    
    // Removed: nameFilter logic - filter by name feature removed
    
    paginationState.filteredPatients = filtered;
    paginationState.totalItems = filtered.length;
    
    // Reset to first page
    paginationState.currentPage = 1;
}

// Apply gender filter
function applyGenderFilter(gender) {
    paginationState.currentGenderFilter = gender;
    
    // Update filter button appearance
    const filterBtn = document.querySelector('.gender-filter-btn');
    if (filterBtn) {
        if (gender) {
            filterBtn.classList.add('active');
            filterBtn.style.color = 'var(--accent)';
        } else {
            filterBtn.classList.remove('active');
            filterBtn.style.color = 'var(--accent)';
        }
    }
    
    // Apply gender filter
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
    
    // Update clear filters button visibility
    updateClearFiltersVisibility();
}

// Update clear filters and sorting buttons visibility
function updateClearFiltersVisibility() {
    const clearFiltersGroup = document.getElementById('clearFiltersGroup');
    const clearSortingBtn = document.getElementById('clearSortingBtn');
    
    if (!clearFiltersGroup) return;
    
    // Check if any filter is active
    const hasActiveFilter = 
        paginationState.currentDoctorFilter !== 'all' ||
        paginationState.currentGenderFilter !== null ||
        (paginationState.currentAgeFilter.min !== null || paginationState.currentAgeFilter.max !== null) ||
        (paginationState.currentLastVisitFilter.from !== null || paginationState.currentLastVisitFilter.to !== null);
    
    // Check if sorting is active
    const hasActiveSort = paginationState.sortBy !== null && paginationState.sortOrder !== null;
    
    // Show/hide clear filters button
    if (hasActiveFilter || hasActiveSort) {
        clearFiltersGroup.classList.remove('d-none');
        clearFiltersGroup.style.display = '';
    } else {
        clearFiltersGroup.classList.add('d-none');
    }
    
    // Show/hide clear sorting button
    if (hasActiveSort) {
        if (clearSortingBtn) {
            clearSortingBtn.classList.remove('d-none');
        }
    } else {
        if (clearSortingBtn) {
            clearSortingBtn.classList.add('d-none');
        }
    }
}

// Clear all filters
function clearAllFilters() {
    // Reset all filters
    paginationState.currentDoctorFilter = 'all';
    paginationState.currentGenderFilter = null;
    paginationState.currentAgeFilter = { min: null, max: null };
    paginationState.currentLastVisitFilter = { from: null, to: null };
    
    // Update filter buttons appearance
    const genderFilterBtn = document.querySelector('.gender-filter-btn');
    if (genderFilterBtn) {
        genderFilterBtn.classList.remove('active');
    }
    
    const ageFilterBtn = document.querySelector('.age-filter-btn');
    if (ageFilterBtn) {
        ageFilterBtn.classList.remove('active');
    }
    
    const lastVisitFilterBtn = document.querySelector('.last-visit-filter-btn');
    if (lastVisitFilterBtn) {
        lastVisitFilterBtn.classList.remove('active');
    }
    
    // Update doctor filter buttons
    document.querySelectorAll('#doctorFilterGroup button').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.doctor === 'all') {
            btn.classList.add('active');
        }
    });
    
    // Apply filters (will reset to all)
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
    
    // Update visibility
    updateClearFiltersVisibility();
}

// Clear sorting
function clearSorting() {
    // Reset sorting
    paginationState.sortBy = null;
    paginationState.sortOrder = null;
    
    // Remove active class from all sort buttons
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Reload data without sorting
    fetch('/api/patients', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.patients) {
            paginationState.allPatients = data.patients;
            
            // Reapply current filters
            applyDoctorFilter();
            
            // Reapply quick search if exists
            const quickSearch = document.getElementById('quickSearch');
            if (quickSearch && quickSearch.value.trim()) {
                filterPatientsLocally(quickSearch.value);
            } else {
                // Just re-render with current filters
                renderPatientsTable();
                updatePaginationInfo();
                renderPaginationNav();
            }
            
            // Update statistics
            updateStatistics(data.patients);
        }
    })
    .catch(error => {
        console.error('Error loading patients:', error);
    });
    
    // Update visibility
    updateClearFiltersVisibility();
}

// Filter patients by name
// Removed: filterByName function - filter by name feature removed

// Clear patient name filter
// Removed: clearPatientNameFilter function - filter by name feature removed

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
    
    // Update display based on current view mode
    if (currentViewMode === 'table') {
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    } else if (currentViewMode === 'cards') {
        renderPatientsCards();
        updatePaginationInfoCards();
        renderPaginationNavCards();
    }
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
    // Check if we need to open add patient modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openModal') === 'addPatient') {
        setTimeout(() => {
            const addPatientBtn = document.querySelector('[data-bs-target="#addPatientModal"]');
            if (addPatientBtn) {
                addPatientBtn.click();
            }
            // Clean URL
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=addPatient/, '').replace(/^&/, '?');
            window.history.replaceState({}, '', newUrl);
        }, 500);
    }
    
    // Initialize pagination first
    initializePagination();
    
    // Setup pagination limit selector
    // Handle pagination limit change (now using custom select)
    const paginationLimitSelect = document.getElementById('paginationLimit');
    if (paginationLimitSelect) {
        paginationLimitSelect.addEventListener('change', function() {
            changeItemsPerPage(this.value);
        });
        
        // Also listen for custom select changes
        const paginationLimitField = paginationLimitSelect.closest('.field.menu');
        if (paginationLimitField) {
            const paginationLimitButton = paginationLimitField.querySelector('.custom-select-toggle');
            if (paginationLimitButton) {
                paginationLimitButton.addEventListener('click', function() {
                    // Ensure custom select is initialized
                    setTimeout(() => {
                        initCustomSelects();
                    }, 50);
                });
            }
        }
    }
    
    // Setup quick search
    const quickSearch = document.getElementById('quickSearch');
    const clearQuickSearch = document.getElementById('clearQuickSearch');
    
    if (quickSearch) {
        // Debounced search for main table
        const debouncedQuickSearch = debounce(filterPatientsLocally, 300);
        
        quickSearch.addEventListener('input', function() {
            const hasValue = this.value.trim().length > 0;
            // Show/hide clear button based on input value
            if (clearQuickSearch) {
                clearQuickSearch.style.display = hasValue ? 'block' : 'none';
            }
            debouncedQuickSearch(this.value);
        });
        
        // Clear search
        if (clearQuickSearch) {
            clearQuickSearch.addEventListener('click', function() {
                quickSearch.value = '';
                this.style.display = 'none';
                filterPatientsLocally('');
                quickSearch.focus();
            });
        }
    }
    
    // Quick search for cards view
    const quickSearchCards = document.getElementById('quickSearchCards');
    const clearQuickSearchCards = document.getElementById('clearQuickSearchCards');
    
    if (quickSearchCards) {
        const debouncedQuickSearchCards = debounce(filterPatientsLocally, 300);
        
        quickSearchCards.addEventListener('input', function() {
            const hasValue = this.value.trim().length > 0;
            if (clearQuickSearchCards) {
                clearQuickSearchCards.style.display = hasValue ? 'block' : 'none';
            }
            debouncedQuickSearchCards(this.value);
        });
        
        if (clearQuickSearchCards) {
            clearQuickSearchCards.addEventListener('click', function() {
                quickSearchCards.value = '';
                this.style.display = 'none';
                filterPatientsLocally('');
                quickSearchCards.focus();
            });
        }
    }
    
    // Pagination limit for cards
    const paginationLimitCards = document.getElementById('paginationLimitCards');
    if (paginationLimitCards) {
        paginationLimitCards.addEventListener('change', function() {
            paginationState.currentPage = 1;
            if (currentViewMode === 'cards') {
                renderPatientsCards();
                updatePaginationInfoCards();
                renderPaginationNavCards();
            }
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const isModalOpen = document.querySelector('.modal.show');
        const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                             e.target.contentEditable === 'true';
        
        // Quick search shortcut (Ctrl+F when not in modal)
        if ((e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 'f' && !isModalOpen) {
            e.preventDefault();
            const activeQuickSearch = document.getElementById('quickSearch') || document.getElementById('quickSearchCards');
            if (activeQuickSearch) {
                activeQuickSearch.focus();
                activeQuickSearch.select();
            }
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
        // Skip if key is undefined
        if (!e.key) {
            return;
        }
        
        // Open search modal with 'F' key or Arabic 'ب' key (only if no input is focused)
        // Also support Arabic keyboard layout alternatives
        const searchKeys = ['f', 'ب']; // F key and Arabic 'ba' (same position on keyboard)
        const isSearchKey = searchKeys.includes(e.key.toLowerCase()) || searchKeys.includes(e.key);
        
        if (isSearchKey && !isInputFocused() && !searchModal.classList.contains('show')) {
            e.preventDefault();
            searchButton.click();
        }
        
        // Open add patient modal with 'Ctrl+N' or 'N' key or Arabic 'ى' key
        const addPatientKeys = ['n', 'ى']; // N key and Arabic 'ya' (same position on keyboard)
        const isAddPatientKey = e.key && (addPatientKeys.includes(e.key.toLowerCase()) || addPatientKeys.includes(e.key));
        const isCtrlN = (e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 'n';
        
        if ((isAddPatientKey || isCtrlN) && !isInputFocused() && !document.querySelector('.modal.show')) {
            e.preventDefault();
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
        if ((e.ctrlKey || e.metaKey) && e.key && (e.key.toLowerCase() === 'f' || e.key === 'ب') && searchModal.classList.contains('show')) {
            e.preventDefault();
            globalSearch.focus();
            globalSearch.select();
        }
        
        // Save patient with 'Ctrl+S' when add patient modal is open
        if ((e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 's' && document.getElementById('addPatientModal').classList.contains('show')) {
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
    
    // Initialize phone tooltip click handlers
    initializePhoneTooltips();
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
    
    window.currentPatientToDelete = {
        id: patientId,
        name: patientName
    };
    
    // Store in localStorage as backup
    localStorage.setItem('deletePatientData', JSON.stringify(window.currentPatientToDelete));
    
    
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
    
    // Try to recover from localStorage if main variable is lost
    if (!window.currentPatientToDelete) {
        const savedData = localStorage.getItem('deletePatientData');
        if (savedData) {
            try {
                window.currentPatientToDelete = JSON.parse(savedData);
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
                
                // Refresh page to update patient list and fix table dimensions
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
        resetDeleteConfirmation();
        
        // Reset patient data only if not transitioning back to warning modal
        if (!isTransitioning) {
            window.currentPatientToDelete = null;
            localStorage.removeItem('deletePatientData');
        }
    });
    
    // Reset patient data when warning modal is hidden
    document.getElementById('deletePatientModal').addEventListener('hidden.bs.modal', function() {
        
        // Don't reset if we're transitioning to confirmation modal
        setTimeout(() => {
            if (!document.getElementById('deletePatientConfirmModal').classList.contains('show')) {
                window.currentPatientToDelete = null;
                localStorage.removeItem('deletePatientData');
            }
        }, 100); // Reduced timeout for faster response
    });
    
    // Override showDeleteConfirmation to prevent data loss
    const originalShowDeleteConfirmation = window.showDeleteConfirmation;
    window.showDeleteConfirmation = function() {
        isTransitioning = true;
        
        originalShowDeleteConfirmation();
        
        // Reset transition flag after modal is shown
        setTimeout(() => {
            isTransitioning = false;
        }, 500);
    };
    
    // Override backToDeleteWarning to prevent data loss
    const originalBackToDeleteWarning = window.backToDeleteWarning;
    window.backToDeleteWarning = function() {
        isTransitioning = true;
        
        originalBackToDeleteWarning();
        
        // Reset transition flag after modal is shown
        setTimeout(() => {
            isTransitioning = false;
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

// Initialize phone tooltip click handlers
function initializePhoneTooltips() {
    // Use event delegation for dynamically added phone numbers
    document.addEventListener('click', function(e) {
        // Check if click is on a phone number link
        const phoneLink = e.target.closest('.phone-number-link');
        if (phoneLink) {
            e.preventDefault();
            e.stopPropagation();
            
            const container = phoneLink.closest('.phone-number-container');
            if (container) {
                // Toggle tooltip visibility
                const isActive = container.classList.contains('active');
                
                // Close all other tooltips
                document.querySelectorAll('.phone-number-container.active').forEach(activeContainer => {
                    if (activeContainer !== container) {
                        activeContainer.classList.remove('active');
                    }
                });
                
                // Toggle current tooltip
                if (isActive) {
                    container.classList.remove('active');
                } else {
                    container.classList.add('active');
                }
            }
        } else {
            // Close all tooltips when clicking outside
            document.querySelectorAll('.phone-number-container.active').forEach(container => {
                container.classList.remove('active');
            });
        }
    });
    
    // Prevent tooltip from closing when clicking inside it
    document.addEventListener('click', function(e) {
        if (e.target.closest('.phone-htooltip') || e.target.closest('.phone-action-btn')) {
            e.stopPropagation();
        }
    });
}

// Auto-refresh patients data every 60 seconds using AJAX (pause when modals are open or user is interacting)
let refreshInterval = setInterval(() => {
    const searchModal = document.getElementById('searchModal');
    const addPatientModal = document.getElementById('addPatientModal');
    const deleteModal = document.getElementById('deletePatientModal');
    const deleteConfirmModal = document.getElementById('deletePatientConfirmModal');
    const editPatientModal = document.getElementById('editPatientModal');
    const quickSearch = document.getElementById('quickSearch');
    
    // Don't refresh if user is actively using the page
    const isUserActive = document.activeElement === quickSearch || 
                        quickSearch.value.trim().length > 0 ||
                        paginationState.currentPage > 1 ||
                        paginationState.itemsPerPage !== 20 ||
                        document.querySelector('.modal.show') !== null;
    
    if (!searchModal.classList.contains('show') && 
        !addPatientModal.classList.contains('show') &&
        !deleteModal.classList.contains('show') &&
        !deleteConfirmModal.classList.contains('show') &&
        !editPatientModal?.classList.contains('show') &&
        !isUserActive) {
        refreshPatientsData();
    }
}, 60000);

// Function to refresh patients data via AJAX
function refreshPatientsData() {
    // Show subtle loading indicator
    const tableBody = document.getElementById('patientsTableBody');
    if (tableBody) {
        tableBody.parentElement.classList.add('table-loading');
    }
    
    // Build query parameters
    const params = new URLSearchParams();
    if (paginationState.sortBy) {
        params.append('sort_by', paginationState.sortBy);
    }
    if (paginationState.sortOrder) {
        params.append('sort_order', paginationState.sortOrder);
    }
    
    const url = '/api/patients' + (params.toString() ? '?' + params.toString() : '');
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.patients && data.doctors) {
            // Update pagination state
            paginationState.allPatients = data.patients;
            paginationState.doctors = data.doctors;
            
            // Reapply current filters
            applyDoctorFilter();
            
            // Reapply quick search if exists
            const quickSearch = document.getElementById('quickSearch');
            if (quickSearch && quickSearch.value.trim()) {
                filterPatientsLocally(quickSearch.value);
            } else {
                // Just re-render with current filters
                renderPatientsTable();
                updatePaginationInfo();
                renderPaginationNav();
            }
            
            // Update statistics
            updateStatistics(data.patients);
        }
    })
    .catch(error => {
        console.error('Error refreshing patients data:', error);
        // Silently fail - don't show error to user for background refresh
    })
    .finally(() => {
        // Remove loading indicator
        const tableBody = document.getElementById('patientsTableBody');
        if (tableBody) {
            tableBody.parentElement.classList.remove('table-loading');
        }
    });
}

// Function to update statistics cards
function updateStatistics(patients) {
    const totalPatients = patients.length;
    const totalVisits = patients.reduce((sum, p) => sum + (parseInt(p.total_appointments) || 0), 0);
    
    // Calculate recent visits (last 7 days)
    const recentVisits = patients.filter(p => {
        if (!p.last_visit) return false;
        const visitDate = new Date(p.last_visit);
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        return visitDate >= sevenDaysAgo;
    }).length;
    
    // Calculate new this month (last 30 days)
    const newThisMonth = patients.filter(p => {
        if (!p.created_at) return false;
        const createdDate = new Date(p.created_at);
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        return createdDate >= thirtyDaysAgo;
    }).length;
    
    // Update statistics cards - use IDs for new stats cards
    const totalPatientsEl = document.getElementById('statsTotalPatients');
    const totalVisitsEl = document.getElementById('statsTotalVisits');
    const recentVisitsEl = document.getElementById('statsRecentVisits');
    const newThisMonthEl = document.getElementById('statsNewThisMonth');
    
    if (totalPatientsEl) totalPatientsEl.textContent = totalPatients;
    if (totalVisitsEl) totalVisitsEl.textContent = totalVisits;
    if (recentVisitsEl) recentVisitsEl.textContent = recentVisits;
    if (newThisMonthEl) newThisMonthEl.textContent = newThisMonth;
    
    // Fallback: Update old style cards if they exist
    const statsRow = document.querySelector('.row.mb-4');
    if (statsRow && !totalPatientsEl) {
        const statsCards = statsRow.querySelectorAll('.card-body h3');
        if (statsCards.length >= 4) {
            statsCards[0].textContent = totalPatients;
            statsCards[1].textContent = totalVisits;
            statsCards[2].textContent = recentVisits;
            statsCards[3].textContent = newThisMonth;
        }
    }
    
    // Update total count in header
    const totalCountElement = document.getElementById('totalPatientsCount');
    if (totalCountElement) {
        totalCountElement.textContent = totalPatients;
    }
}

// Edit Patient Function
function editPatient(patientId) {
    // Remove existing modal if present
    const existingModal = document.getElementById('editPatientModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Fetch patient data
    fetch(`/api/patients/${patientId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.ok || !data.patient) {
            showNotification('Error loading patient data', 'error');
            return;
        }
        
        const patient = data.patient;
        
        // Escape HTML for safety
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        const modalHtml = `
            <div class="modal fade" id="editPatientModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-person-gear me-2"></i>
                                Edit Patient Information
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="editPatientForm">
                            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                <!-- Basic Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Basic Information</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="edit_first_name" name="first_name" 
                                               value="${escapeHtml(patient.first_name)}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="edit_last_name" name="last_name" 
                                               value="${escapeHtml(patient.last_name)}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Contact Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Contact Information</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="edit_phone" name="phone" 
                                               value="${escapeHtml(patient.phone)}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_alt_phone" class="form-label">Alternative Phone</label>
                                        <input type="tel" class="form-control" id="edit_alt_phone" name="alt_phone" 
                                               value="${escapeHtml(patient.alt_phone || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Personal Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Personal Information</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_dob" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="edit_dob" name="dob" 
                                               value="${patient.dob || ''}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_national_id" class="form-label">National ID</label>
                                        <input type="text" class="form-control" id="edit_national_id" name="national_id" 
                                               value="${escapeHtml(patient.national_id || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Address -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Address</h6>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="edit_address" class="form-label">Full Address</label>
                                        <textarea class="form-control" id="edit_address" name="address" rows="3">${escapeHtml(patient.address || '')}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <!-- Emergency Contact -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3" style="color: var(--text); border-bottom-color: var(--border) !important;">Emergency Contact</h6>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_emergency_contact" class="form-label">Emergency Contact Name</label>
                                        <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact" 
                                               value="${escapeHtml(patient.emergency_contact || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_emergency_phone" class="form-label">Emergency Contact Phone</label>
                                        <input type="tel" class="form-control" id="edit_emergency_phone" name="emergency_phone" 
                                               value="${escapeHtml(patient.emergency_phone || '')}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg me-2"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="resetEditPatientForm()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary" id="savePatientBtn">
                                    <span class="spinner-border spinner-border-sm d-none" id="savePatientSpinner"></span>
                                    <i class="bi bi-check-lg me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalElement = document.getElementById('editPatientModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Store original form data for reset
        window.originalPatientData = {
            first_name: patient.first_name,
            last_name: patient.last_name,
            phone: patient.phone,
            alt_phone: patient.alt_phone || '',
            dob: patient.dob || '',
            national_id: patient.national_id || '',
            address: patient.address || '',
            emergency_contact: patient.emergency_contact || '',
            emergency_phone: patient.emergency_phone || ''
        };
        
        // Apply glass style and draggable
        setTimeout(function() {
            if (typeof applyGlassStyleToModal === 'function') {
                applyGlassStyleToModal('editPatientModal');
            }
            if (typeof initializeDraggableModals === 'function') {
                initializeDraggableModals();
            }
        }, 50);
        
        // Handle form submission
        document.getElementById('editPatientForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous validation errors
            clearEditPatientValidationErrors();
            
            // Basic validation
            if (!validateEditPatientForm()) {
                return;
            }
            
            // Show loading state
            const saveBtn = document.getElementById('savePatientBtn');
            const spinner = document.getElementById('savePatientSpinner');
            saveBtn.disabled = true;
            spinner.classList.remove('d-none');
            
            // Submit form
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            
            fetch(`/doctor/patients/${patientId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    showNotification('Patient updated successfully!', 'success');
                    modal.hide();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Failed to update patient');
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating patient: ' + error.message, 'error');
            })
            .finally(() => {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
            });
        });
        
        // Clean up modal on hide
        modalElement.addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    })
    .catch(error => {
        console.error('Error loading patient data:', error);
        showNotification('Error loading patient data: ' + error.message, 'error');
    });
}

function validateEditPatientForm() {
    let isValid = true;
    
    // Required fields
    const requiredFields = ['edit_first_name', 'edit_last_name', 'edit_phone'];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field || !field.value.trim()) {
            showEditPatientFieldError(fieldId, 'This field is required');
            isValid = false;
        }
    });
    
    // Phone validation
    const phone = document.getElementById('edit_phone')?.value.trim();
    if (phone && !isValidPhone(phone)) {
        showEditPatientFieldError('edit_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    const altPhone = document.getElementById('edit_alt_phone')?.value.trim();
    if (altPhone && !isValidPhone(altPhone)) {
        showEditPatientFieldError('edit_alt_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    const emergencyPhone = document.getElementById('edit_emergency_phone')?.value.trim();
    if (emergencyPhone && !isValidPhone(emergencyPhone)) {
        showEditPatientFieldError('edit_emergency_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    return isValid;
}

function isValidPhone(phone) {
    // Egyptian phone number validation
    const phoneRegex = /^(\+20|0)?1[0-9]{9}$/;
    return phoneRegex.test(phone.replace(/\s+/g, ''));
}

function clearEditPatientValidationErrors() {
    const modal = document.getElementById('editPatientModal');
    if (!modal) return;
    
    modal.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    modal.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}

function showEditPatientFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const feedback = field.nextElementSibling;
    
    field.classList.add('is-invalid');
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

function resetEditPatientForm() {
    if (!window.originalPatientData) return;
    
    const data = window.originalPatientData;
    document.getElementById('edit_first_name').value = data.first_name;
    document.getElementById('edit_last_name').value = data.last_name;
    document.getElementById('edit_phone').value = data.phone;
    document.getElementById('edit_alt_phone').value = data.alt_phone;
    document.getElementById('edit_dob').value = data.dob;
    document.getElementById('edit_national_id').value = data.national_id;
    document.getElementById('edit_address').value = data.address;
    document.getElementById('edit_emergency_contact').value = data.emergency_contact;
    document.getElementById('edit_emergency_phone').value = data.emergency_phone;
    
    clearEditPatientValidationErrors();
}

// Check if showNotification exists from main layout, otherwise define it
if (typeof window.showNotification !== 'function') {
    window.showNotification = function(message, type = 'info') {
        // Fallback notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    };
}

// =========================================
// Custom Select Menu Logic
// =========================================

// Custom Select Menu Logic
function initCustomSelects() {
    const customSelects = document.querySelectorAll('.field.menu:not([data-initialized])');

    customSelects.forEach(field => {
        const select = field.querySelector('select');
        const button = field.querySelector('.custom-select-toggle');
        const menu = field.querySelector('menu');
        const options = menu ? menu.querySelectorAll('li') : [];

        if (!select || !button || !menu || options.length === 0) {
            console.warn('Missing elements for custom select initialization:', field);
            return;
        }
        
        // Mark as initialized to prevent duplicate event listeners
        field.setAttribute('data-initialized', 'true');

        // Set initial button text
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            const correspondingLi = Array.from(options).find(li => li.dataset.option === selectedOption.value);
            if (correspondingLi) {
                button.textContent = correspondingLi.querySelector('h3')?.textContent || selectedOption.textContent;
                correspondingLi.classList.add('selected');
            } else {
                button.textContent = selectedOption.textContent;
            }
        } else {
            button.textContent = 'Select an option';
        }

        function openMenu() {
            // Close any other open menus first
            document.querySelectorAll('.field.menu.open').forEach(openField => {
                if (openField !== field) {
                    const openButton = openField.querySelector('.custom-select-toggle');
                    openField.classList.remove('open');
                    if (openButton) openButton.setAttribute('aria-expanded', 'false');
                    const openParent = openField.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
                    if (openParent && !openParent.classList.contains('modal')) {
                        openParent.style.zIndex = '';
                        openParent.style.position = '';
                    } else {
                        const openModal = openField.closest('.modal');
                        if (openModal) {
                            openModal.style.zIndex = '';
                        }
                    }
                }
            });

            field.classList.add('open');
            button.setAttribute('aria-expanded', 'true');

            // Fix z-index issue by elevating parent containers manually
            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                parent.style.zIndex = '1000002';
                parent.style.position = 'relative';
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    modal.style.zIndex = '1000002';
                }
            }

            const selected = menu.querySelector('.selected') || options[0];
            if (selected) {
                selected.focus();
                
                // Scroll to selected item if menu has many options
                setTimeout(() => {
                    selected.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 150);
            }
        }

        function closeMenu() {
            field.classList.remove('open');
            button.setAttribute('aria-expanded', 'false');
            if (document.activeElement === document.body || document.activeElement === null) {
                button.focus();
            }

            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                setTimeout(() => {
                    if (!field.classList.contains('open')) {
                        parent.style.zIndex = '';
                        parent.style.position = '';
                    }
                }, 300);
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    setTimeout(() => {
                        if (!field.classList.contains('open')) {
                            modal.style.zIndex = '';
                        }
                    }, 300);
                }
            }
        }

        function setOption(optionEl) {
            const value = optionEl.dataset.option;
            const text = optionEl.querySelector('h3')?.textContent || optionEl.textContent;

            select.value = value;
            select.dispatchEvent(new Event('change'));

            button.textContent = text;

            options.forEach(el => el.classList.remove('selected'));
            optionEl.classList.add('selected');

            closeMenu();
        }

        button.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            if (field.classList.contains('open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        button.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openMenu();
            }
        });

        // Prevent clicks on menu from closing modal
        menu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                setOption(option);
            });

            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setOption(option);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = option.nextElementSibling;
                    if (next) next.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = option.previousElementSibling;
                    if (prev) prev.focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeMenu();
                }
            });
        });

        // Close menu when clicking outside, but prevent modal from closing
        const handleOutsideClick = (e) => {
            const target = e.target;
            const isInteractiveElement = target.tagName === 'INPUT' || 
                                        target.tagName === 'TEXTAREA' || 
                                        target.tagName === 'SELECT' ||
                                        target.isContentEditable ||
                                        target.closest('input, textarea, select, [contenteditable]');
            
            if (isInteractiveElement) {
                return;
            }
            
            if (field.classList.contains('open') && !field.contains(target)) {
                const modal = field.closest('.modal');
                if (modal && target === modal) {
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                closeMenu();
            }
        };
        
        // Store handler for cleanup
        field._outsideClickHandler = handleOutsideClick;
        document.addEventListener('click', handleOutsideClick, false);
    });
}

// Sort patients function
function sortPatients(sortBy, sortOrder) {
    // Update sort state
    paginationState.sortBy = sortBy;
    paginationState.sortOrder = sortOrder;
    
    // Reset to first page
    paginationState.currentPage = 1;
    
    // Update sort button states
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    const activeBtn = document.querySelector(`[data-sort="${sortBy}"][data-order="${sortOrder}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Show loading indicator
    const tableBody = document.getElementById('patientsTableBody');
    if (tableBody) {
        tableBody.parentElement.classList.add('table-loading');
    }
    
    // Build query parameters
    const params = new URLSearchParams();
    params.append('sort_by', sortBy);
    params.append('sort_order', sortOrder);
    
    // Fetch sorted data
    fetch('/api/patients?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.patients && data.doctors) {
            // Update pagination state
            paginationState.allPatients = data.patients;
            paginationState.doctors = data.doctors;
            
            // Reapply current filters
            applyDoctorFilter();
            
            // Reapply quick search if exists
            const quickSearch = document.getElementById('quickSearch');
            if (quickSearch && quickSearch.value.trim()) {
                filterPatientsLocally(quickSearch.value);
            } else {
                // Just re-render with current filters
                renderPatientsTable();
                updatePaginationInfo();
                renderPaginationNav();
            }
            
            // Update statistics
            updateStatistics(data.patients);
            
            // Update clear filters button visibility
            updateClearFiltersVisibility();
        }
    })
    .catch(error => {
        console.error('Error sorting patients:', error);
        showNotification('Error sorting patients. Please try again.', 'error');
    })
    .finally(() => {
        // Remove loading indicator
        const tableBody = document.getElementById('patientsTableBody');
        if (tableBody) {
            tableBody.parentElement.classList.remove('table-loading');
        }
    });
}

// Initialize gender filter popover
function initGenderFilterPopover() {
    const filterBtn = document.querySelector('.gender-filter-btn');
    if (!filterBtn) return;
    
    // Remove existing popover instance if any
    const existingPopover = bootstrap.Popover.getInstance(filterBtn);
    if (existingPopover) {
        existingPopover.dispose();
    }
    
    // Remove existing tooltip if any (to avoid conflicts)
    const existingTooltip = bootstrap.Tooltip.getInstance(filterBtn);
    if (existingTooltip) {
        existingTooltip.dispose();
    }
    
    // Create popover content function that returns HTML string
    const getPopoverContent = function() {
        const currentFilter = paginationState.currentGenderFilter;
        return `
            <div class="gender-filter-popover">
                <div class="mb-3">
                    <div class="d-flex flex-column gap-2" style="margin-left: 10px !important;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="genderFilter" id="genderFilterMale" value="Male" ${currentFilter === 'Male' ? 'checked' : ''}>
                            <label class="form-check-label" for="genderFilterMale" style="color: var(--text); cursor: pointer;">
                                <i class="bi bi-gender-male me-2" style="color: var(--accent);"></i>Male
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="genderFilter" id="genderFilterFemale" value="Female" ${currentFilter === 'Female' ? 'checked' : ''}>
                            <label class="form-check-label" for="genderFilterFemale" style="color: var(--text); cursor: pointer;">
                                <i class="bi bi-gender-female me-2" style="color: rgb(255, 85, 224);"></i>Female
                            </label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary w-100 clear-gender-filter-btn" style="font-size: 0.875rem;">
                        <i class="bi bi-x-circle me-1"></i>Clear Filter
                    </button>
                </div>
            </div>
        `;
    };
    
    // Create popover title with close button
    const getPopoverTitle = function() {
        return `
            <div class="d-flex justify-content-between align-items-center w-100">
                <span style=font-weight: 300 !important;">Filter by Gender</span>
            </div>
        `;
    };
    
    // Initialize Bootstrap popover using getOrCreateInstance
    const popover = bootstrap.Popover.getOrCreateInstance(filterBtn, {
        title: getPopoverTitle,
        content: getPopoverContent,
        html: true,
        sanitize: false,
        placement: 'bottom',
        trigger: 'click',
        container: 'body',
        customClass: 'gender-filter-popover-glass'
    });
    
    // Handle popover shown event
    const handlePopoverShown = function() {
        // Use setTimeout to ensure popover is fully rendered
        setTimeout(() => {
            // Find popover element by class - try multiple selectors
            let popoverElement = document.querySelector('.popover.gender-filter-popover-glass');
            if (!popoverElement) {
                popoverElement = document.querySelector('.gender-filter-popover-glass');
            }
            if (!popoverElement) {
                // Try to get from popover instance
                const popoverInstance = bootstrap.Popover.getInstance(filterBtn);
                if (popoverInstance && popoverInstance.tip) {
                    popoverElement = popoverInstance.tip;
                }
            }
            
            if (!popoverElement) {
                console.error('Popover element not found');
                return;
            }
            
            // Find the popover body and header
            const popoverBody = popoverElement.querySelector('.popover-body');
            const popoverHeader = popoverElement.querySelector('.popover-header');
            
            if (!popoverBody) {
                console.error('Popover body not found');
                return;
            }
            
            // Set current selection
            const currentFilter = paginationState.currentGenderFilter;
            const maleRadio = popoverBody.querySelector('#genderFilterMale');
            const femaleRadio = popoverBody.querySelector('#genderFilterFemale');
            
            if (maleRadio && femaleRadio) {
                maleRadio.checked = (currentFilter === 'Male');
                femaleRadio.checked = (currentFilter === 'Female');
            }
            
            // Handle radio button changes (use event delegation on popover body)
            const handleRadioChange = function(e) {
                if (e.target.name === 'genderFilter' && e.target.checked) {
                    applyGenderFilter(e.target.value);
                    popover.hide();
                }
            };
            
            // Remove old listener and add new one
            popoverBody.removeEventListener('change', handleRadioChange);
            popoverBody.addEventListener('change', handleRadioChange);
            
            // Handle clear filter button
            const clearBtn = popoverBody.querySelector('.clear-gender-filter-btn');
            if (clearBtn) {
                const handleClearClick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    applyGenderFilter(null);
                    // Uncheck all radios
                    if (maleRadio) maleRadio.checked = false;
                    if (femaleRadio) femaleRadio.checked = false;
                    popover.hide();
                };
                
                // Remove old listener and add new one
                clearBtn.removeEventListener('click', handleClearClick);
                clearBtn.addEventListener('click', handleClearClick);
            }
            
            // Handle close button in header
            const closeBtn = popoverHeader ? popoverHeader.querySelector('.gender-filter-close-btn') : null;
            if (closeBtn) {
                const handleCloseClick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    popover.hide();
                };
                
                // Remove old listener and add new one
                closeBtn.removeEventListener('click', handleCloseClick);
                closeBtn.addEventListener('click', handleCloseClick);
            }
        }, 100);
    };
    
    // Remove existing listener if any
    filterBtn.removeEventListener('shown.bs.popover', handlePopoverShown);
    // Add new listener
    filterBtn.addEventListener('shown.bs.popover', handlePopoverShown);
    
    // Handle click outside popover to close it
    const handleClickOutside = function(event) {
        const popoverInstance = bootstrap.Popover.getInstance(filterBtn);
        if (!popoverInstance || !popoverInstance.tip) {
            return;
        }
        
        const popoverElement = popoverInstance.tip;
        const isClickInsidePopover = popoverElement.contains(event.target);
        const isClickOnFilterBtn = filterBtn.contains(event.target);
        
        // If click is outside both popover and filter button, close popover
        if (!isClickInsidePopover && !isClickOnFilterBtn) {
            popoverInstance.hide();
        }
    };
    
    // Add click outside listener when popover is shown
    filterBtn.addEventListener('shown.bs.popover', function() {
        // Use setTimeout to ensure popover is rendered
        setTimeout(() => {
            document.addEventListener('click', handleClickOutside);
        }, 10);
    });
    
    // Remove click outside listener when popover is hidden
    filterBtn.addEventListener('hidden.bs.popover', function() {
        document.removeEventListener('click', handleClickOutside);
    });
}

// Initialize age filter popover
function initAgeFilterPopover() {
    const filterBtn = document.querySelector('.age-filter-btn');
    if (!filterBtn) return;
    
    // Remove existing popover instance if any
    const existingPopover = bootstrap.Popover.getInstance(filterBtn);
    if (existingPopover) {
        existingPopover.dispose();
    }
    
    // Remove existing tooltip if any (to avoid conflicts)
    const existingTooltip = bootstrap.Tooltip.getInstance(filterBtn);
    if (existingTooltip) {
        existingTooltip.dispose();
    }
    
    // Create popover content function that returns HTML string
    const getPopoverContent = function() {
        const currentFilter = paginationState.currentAgeFilter;
        return `
            <div class="age-filter-popover">
                <div class="mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="ageFilterMin" class="form-label small" style="color: var(--text);">Min Age</label>
                            <input type="number" class="form-control form-control-sm" id="ageFilterMin" 
                                   placeholder="Min" min="0" max="150" 
                                   value="${currentFilter.min !== null ? currentFilter.min : ''}"
                                   style="color: var(--text); background-color: var(--bg-alt); border-color: var(--border);">
                        </div>
                        <div class="col-6">
                            <label for="ageFilterMax" class="form-label small" style="color: var(--text);">Max Age</label>
                            <input type="number" class="form-control form-control-sm" id="ageFilterMax" 
                                   placeholder="Max" min="0" max="150"
                                   value="${currentFilter.max !== null ? currentFilter.max : ''}"
                                   style="color: var(--text); background-color: var(--bg-alt); border-color: var(--border);">
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary w-100 apply-age-filter-btn" style="font-size: 0.875rem;">
                        <i class="bi bi-check-circle me-1"></i>Apply Filter
                    </button>
                    <button class="btn btn-sm btn-outline-secondary w-100 clear-age-filter-btn" style="font-size: 0.875rem;">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>
            </div>
        `;
    };
    
    // Create popover title
    const getPopoverTitle = function() {
        return `
            <div class="d-flex justify-content-between align-items-center w-100">
                <span style="font-weight: 300 !important;">Filter by Age Range</span>
            </div>
        `;
    };
    
    // Initialize Bootstrap popover using getOrCreateInstance
    const popover = bootstrap.Popover.getOrCreateInstance(filterBtn, {
        title: getPopoverTitle,
        content: getPopoverContent,
        html: true,
        sanitize: false,
        placement: 'bottom',
        trigger: 'click',
        container: 'body',
        customClass: 'age-filter-popover-glass'
    });
    
    // Handle popover shown event
    const handlePopoverShown = function() {
        // Use setTimeout to ensure popover is fully rendered
        setTimeout(() => {
            // Find popover element by class - try multiple selectors
            let popoverElement = document.querySelector('.popover.age-filter-popover-glass');
            if (!popoverElement) {
                popoverElement = document.querySelector('.age-filter-popover-glass');
            }
            if (!popoverElement) {
                // Try to get from popover instance
                const popoverInstance = bootstrap.Popover.getInstance(filterBtn);
                if (popoverInstance && popoverInstance.tip) {
                    popoverElement = popoverInstance.tip;
                }
            }
            
            if (!popoverElement) {
                console.error('Popover element not found');
                return;
            }
            
            // Find the popover body
            const popoverBody = popoverElement.querySelector('.popover-body');
            if (!popoverBody) {
                console.error('Popover body not found');
                return;
            }
            
            // Get input fields
            const minInput = popoverBody.querySelector('#ageFilterMin');
            const maxInput = popoverBody.querySelector('#ageFilterMax');
            
            // Handle apply filter button
            const applyBtn = popoverBody.querySelector('.apply-age-filter-btn');
            if (applyBtn) {
                const handleApplyClick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const minValue = minInput.value ? parseInt(minInput.value) : null;
                    const maxValue = maxInput.value ? parseInt(maxInput.value) : null;
                    
                    // Validate range
                    if (minValue !== null && maxValue !== null && minValue > maxValue) {
                        alert('Minimum age cannot be greater than maximum age');
                        return;
                    }
                    
                    // Apply filter
                    paginationState.currentAgeFilter = {
                        min: minValue,
                        max: maxValue
                    };
                    
                    // Update filter button appearance
                    if (minValue !== null || maxValue !== null) {
                        filterBtn.classList.add('active');
                    } else {
                        filterBtn.classList.remove('active');
                    }
                    
                    // Apply filters
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
                
                // Update clear filters button visibility
                updateClearFiltersVisibility();
                
                popover.hide();
            };
            
            // Remove old listener and add new one
            applyBtn.removeEventListener('click', handleApplyClick);
            applyBtn.addEventListener('click', handleApplyClick);
        }
        
        // Handle clear filter button
        const clearBtn = popoverBody.querySelector('.clear-age-filter-btn');
            if (clearBtn) {
                const handleClearClick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Clear filter
                    paginationState.currentAgeFilter = { min: null, max: null };
                    
                    // Clear inputs
                    if (minInput) minInput.value = '';
                    if (maxInput) maxInput.value = '';
                    
                    // Update filter button appearance
                    filterBtn.classList.remove('active');
                    
                    // Apply filters
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
                    
                    // Update clear filters button visibility
                    updateClearFiltersVisibility();
                    
                    popover.hide();
                };
                
                // Remove old listener and add new one
                clearBtn.removeEventListener('click', handleClearClick);
                clearBtn.addEventListener('click', handleClearClick);
            }
            
            // Handle Enter key on inputs
            if (minInput && maxInput) {
                const handleInputKeyPress = function(e) {
                    if (e.key === 'Enter') {
                        applyBtn.click();
                    }
                };
                
                minInput.removeEventListener('keypress', handleInputKeyPress);
                maxInput.removeEventListener('keypress', handleInputKeyPress);
                minInput.addEventListener('keypress', handleInputKeyPress);
                maxInput.addEventListener('keypress', handleInputKeyPress);
            }
        }, 100);
    };
    
    // Remove existing listener if any
    filterBtn.removeEventListener('shown.bs.popover', handlePopoverShown);
    // Add new listener
    filterBtn.addEventListener('shown.bs.popover', handlePopoverShown);
    
    // Handle click outside popover to close it
    const handleClickOutside = function(event) {
        const popoverInstance = bootstrap.Popover.getInstance(filterBtn);
        if (!popoverInstance || !popoverInstance.tip) {
            return;
        }
        
        const popoverElement = popoverInstance.tip;
        const isClickInsidePopover = popoverElement.contains(event.target);
        const isClickOnFilterBtn = filterBtn.contains(event.target);
        
        // If click is outside both popover and filter button, close popover
        if (!isClickInsidePopover && !isClickOnFilterBtn) {
            popoverInstance.hide();
        }
    };
    
    // Add click outside listener when popover is shown
    filterBtn.addEventListener('shown.bs.popover', function() {
        // Use setTimeout to ensure popover is rendered
        setTimeout(() => {
            document.addEventListener('click', handleClickOutside);
        }, 10);
    });
    
    // Remove click outside listener when popover is hidden
    filterBtn.addEventListener('hidden.bs.popover', function() {
        document.removeEventListener('click', handleClickOutside);
    });
}

// Initialize last visit filter popover
function initLastVisitFilterPopover() {
    const filterBtn = document.querySelector('.last-visit-filter-btn');
    if (!filterBtn) return;
    
    // Remove existing popover instance if any
    const existingPopover = bootstrap.Popover.getInstance(filterBtn);
    if (existingPopover) {
        existingPopover.dispose();
    }
    
    // Remove existing tooltip if any (to avoid conflicts)
    const existingTooltip = bootstrap.Tooltip.getInstance(filterBtn);
    if (existingTooltip) {
        existingTooltip.dispose();
    }
    
    // Create popover content function that returns HTML string
    const getPopoverContent = function() {
        const currentFilter = paginationState.currentLastVisitFilter;
        return `
            <div class="last-visit-filter-popover">
                <div class="mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="lastVisitFilterFrom" class="form-label small" style="color: var(--text);">From Date</label>
                            <input type="date" class="form-control form-control-sm" id="lastVisitFilterFrom" 
                                   value="${currentFilter.from !== null ? currentFilter.from : ''}"
                                   style="color: var(--text); background-color: var(--bg-alt); border-color: var(--border);">
                        </div>
                        <div class="col-6">
                            <label for="lastVisitFilterTo" class="form-label small" style="color: var(--text);">To Date</label>
                            <input type="date" class="form-control form-control-sm" id="lastVisitFilterTo" 
                                   value="${currentFilter.to !== null ? currentFilter.to : ''}"
                                   style="color: var(--text); background-color: var(--bg-alt); border-color: var(--border);">
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary w-100 apply-last-visit-filter-btn" style="font-size: 0.875rem;">
                        <i class="bi bi-check-circle me-1"></i>Apply Filter
                    </button>
                    <button class="btn btn-sm btn-outline-secondary w-100 clear-last-visit-filter-btn" style="font-size: 0.875rem;">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>
            </div>
        `;
    };
    
    // Create popover title
    const getPopoverTitle = function() {
        return `
            <div class="d-flex justify-content-between align-items-center w-100">
                <span style="font-weight: 300 !important;">Filter by Last Visit</span>
            </div>
        `;
    };
    
    // Initialize Bootstrap popover using getOrCreateInstance
    const popover = bootstrap.Popover.getOrCreateInstance(filterBtn, {
        title: getPopoverTitle,
        content: getPopoverContent,
        html: true,
        sanitize: false,
        placement: 'bottom',
        trigger: 'click',
        container: 'body',
        customClass: 'last-visit-filter-popover-glass'
    });
    
    // Handle popover shown event
    const handlePopoverShown = function() {
        // Use setTimeout to ensure popover is fully rendered
        setTimeout(() => {
            // Find popover element by class - try multiple selectors
            let popoverElement = document.querySelector('.popover.last-visit-filter-popover-glass');
            if (!popoverElement) {
                popoverElement = document.querySelector('.last-visit-filter-popover-glass');
            }
            if (!popoverElement) {
                // Try to get from popover instance
                const popoverInstance = bootstrap.Popover.getInstance(filterBtn);
                if (popoverInstance && popoverInstance.tip) {
                    popoverElement = popoverInstance.tip;
                }
            }
            
            if (!popoverElement) {
                console.error('Popover element not found');
                return;
            }
            
            // Find the popover body
            const popoverBody = popoverElement.querySelector('.popover-body');
            if (!popoverBody) {
                console.error('Popover body not found');
                return;
            }
            
            // Get input fields
            const fromInput = popoverBody.querySelector('#lastVisitFilterFrom');
            const toInput = popoverBody.querySelector('#lastVisitFilterTo');
            
            // Handle apply filter button
            const applyBtn = popoverBody.querySelector('.apply-last-visit-filter-btn');
            if (applyBtn) {
                const handleApplyClick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const fromValue = fromInput.value || null;
                    const toValue = toInput.value || null;
                    
                    // Validate range
                    if (fromValue && toValue && new Date(fromValue) > new Date(toValue)) {
                        alert('From date cannot be greater than To date');
                        return;
                    }
                    
                    // Apply filter
                    paginationState.currentLastVisitFilter = {
                        from: fromValue,
                        to: toValue
                    };
                    
                    // Update filter button appearance
                    if (fromValue || toValue) {
                        filterBtn.classList.add('active');
                    } else {
                        filterBtn.classList.remove('active');
                    }
                    
                    // Apply filters
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
                
                // Update clear filters button visibility
                updateClearFiltersVisibility();
                
                popover.hide();
            };
            
            // Remove old listener and add new one
            applyBtn.removeEventListener('click', handleApplyClick);
            applyBtn.addEventListener('click', handleApplyClick);
        }
        
        // Handle clear filter button
        const clearBtn = popoverBody.querySelector('.clear-last-visit-filter-btn');
            if (clearBtn) {
                const handleClearClick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Clear filter
                    paginationState.currentLastVisitFilter = { from: null, to: null };
                    
                    // Clear inputs
                    if (fromInput) fromInput.value = '';
                    if (toInput) toInput.value = '';
                    
                    // Update filter button appearance
                    filterBtn.classList.remove('active');
                    
                    // Apply filters
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
                    
                    // Update clear filters button visibility
                    updateClearFiltersVisibility();
                    
                    popover.hide();
                };
                
                // Remove old listener and add new one
                clearBtn.removeEventListener('click', handleClearClick);
                clearBtn.addEventListener('click', handleClearClick);
            }
            
            // Handle Enter key on inputs
            if (fromInput && toInput) {
                const handleInputKeyPress = function(e) {
                    if (e.key === 'Enter') {
                        applyBtn.click();
                    }
                };
                
                fromInput.removeEventListener('keypress', handleInputKeyPress);
                toInput.removeEventListener('keypress', handleInputKeyPress);
                fromInput.addEventListener('keypress', handleInputKeyPress);
                toInput.addEventListener('keypress', handleInputKeyPress);
            }
        }, 100);
    };
    
    // Remove existing listener if any
    filterBtn.removeEventListener('shown.bs.popover', handlePopoverShown);
    // Add new listener
    filterBtn.addEventListener('shown.bs.popover', handlePopoverShown);
    
    // Handle click outside popover to close it
    const handleClickOutside = function(event) {
        const popoverInstance = bootstrap.Popover.getInstance(filterBtn);
        if (!popoverInstance || !popoverInstance.tip) {
            return;
        }
        
        const popoverElement = popoverInstance.tip;
        const isClickInsidePopover = popoverElement.contains(event.target);
        const isClickOnFilterBtn = filterBtn.contains(event.target);
        
        // If click is outside both popover and filter button, close popover
        if (!isClickInsidePopover && !isClickOnFilterBtn) {
            popoverInstance.hide();
        }
    };
    
    // Add click outside listener when popover is shown
    filterBtn.addEventListener('shown.bs.popover', function() {
        // Use setTimeout to ensure popover is rendered
        setTimeout(() => {
            document.addEventListener('click', handleClickOutside);
        }, 10);
    });
    
    // Remove click outside listener when popover is hidden
    filterBtn.addEventListener('hidden.bs.popover', function() {
        document.removeEventListener('click', handleClickOutside);
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initCustomSelects();
    
    // Initialize sort buttons
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const sortBy = this.dataset.sort;
            const sortOrder = this.dataset.order;
            
            if (sortBy && sortOrder) {
                sortPatients(sortBy, sortOrder);
            }
        });
    });
    
    // Initialize gender filter popover
    initGenderFilterPopover();
    
    // Initialize age filter popover
    initAgeFilterPopover();
    
    // Initialize last visit filter popover
    initLastVisitFilterPopover();
    
    // Initialize clear filters buttons
    const clearAllFiltersBtn = document.querySelector('.clear-all-filters-btn');
    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearAllFilters();
        });
    }
    
    const clearSortingBtn = document.getElementById('clearSortingBtn');
    if (clearSortingBtn) {
        clearSortingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearSorting();
        });
    }
    
    // Initial visibility check
    updateClearFiltersVisibility();
});

// Also initialize when modals are shown
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});

// ============================================
// Cards View Functions
// ============================================

// Render patients as cards
function renderPatientsCards() {
    const container = document.getElementById('patientsCardsContainer');
    const { currentPage, itemsPerPage, filteredPatients } = paginationState;
    
    // Calculate pagination
    let startIndex, endIndex, patientsToShow;
    
    // Use different items per page for cards if available
    const cardsItemsPerPage = document.getElementById('paginationLimitCards') 
        ? parseInt(document.getElementById('paginationLimitCards').value) || 24 
        : 24;
    
    if (cardsItemsPerPage === 'all' || cardsItemsPerPage === 'All') {
        startIndex = 0;
        endIndex = filteredPatients.length;
        patientsToShow = filteredPatients;
    } else {
        startIndex = (currentPage - 1) * cardsItemsPerPage;
        endIndex = Math.min(startIndex + cardsItemsPerPage, filteredPatients.length);
        patientsToShow = filteredPatients.slice(startIndex, endIndex);
    }
    
    // Clear container
    container.innerHTML = '';
    
    if (patientsToShow.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No patients to display</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    patientsToShow.forEach(patient => {
        const age = patient.dob ? calculateAge(patient.dob) : null;
        const ageText = age !== null ? `${age} years` : 'Not specified';
        const lastAppointment = patient.last_appointment_datetime 
            ? formatDate(patient.last_appointment_datetime) 
            : (patient.last_visit ? formatDate(patient.last_visit) : 'Not visited yet');
        
        const firstName = patient.first_name || '';
        const lastName = patient.last_name || '';
        const fullName = `${firstName} ${lastName}`.trim();
        
        // Get patient image URL (using dedicated endpoint for cards/folders views)
        const imageUrl = patient.latest_attachment_id 
            ? `/api/patients/images/${patient.latest_attachment_id}`
            : null;
        
        // Gender icon
        const genderIcon = patient.gender === 'Female' 
            ? '<i class="bi bi-gender-female"></i>' 
            : (patient.gender === 'Male' 
                ? '<i class="bi bi-gender-male"></i>' 
                : '<i class="bi bi-gender-ambiguous"></i>');
        
        const genderBadgeClass = patient.gender === 'Female' 
            ? 'bg-pink' 
            : (patient.gender === 'Male' ? 'bg-primary' : 'bg-secondary');
        
        html += `
            <div class="col-md-4 col-lg-3 mb-3">
                <div class="card patient-card clickable h-100" 
                     style="border: 1px solid var(--border); cursor: pointer;" 
                     onclick="viewPatient(${patient.id})">
                    <!-- Patient Image -->
                    <div class="position-relative" style="height: 200px; overflow: hidden; background: var(--bg-alt);">
                        ${imageUrl ? `
                            <img src="${imageUrl}" 
                                 alt="${escapeHtml(fullName)}" 
                                 class="w-100 h-100 patient-card-image" 
                                 style="object-fit: cover;"
                                 data-patient-id="${patient.id}"
                                 data-image-url="${imageUrl}"
                                 onerror="(function(img, url, pid) { console.error('[Cards View] Image load failed:', {patientId: pid, url: url, img: img}); img.style.display='none'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='flex'; } })(this, '${imageUrl}', ${patient.id});"
                                 onload="(function(img) { img.style.display='block'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='none'; } })(this);">
                        ` : ''}
                        <div class="d-flex align-items-center justify-content-center h-100 w-100 patient-card-placeholder" 
                             style="background: linear-gradient(135deg, var(--accent) 0%, var(--bg-alt) 100%); ${imageUrl ? 'display: none;' : 'display: flex;'}">
                            <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <!-- Badges overlay -->
                        <div class="position-absolute top-0 start-0 p-2 d-flex gap-2 flex-wrap">
                            ${age !== null ? `
                                <span class="badge bg-info">${age} years</span>
                            ` : ''}
                            <span class="badge ${genderBadgeClass}">${genderIcon}</span>
                        </div>
                        <!-- Treatment Doctor Badge -->
                        ${patient.created_by_doctor_name ? `
                            <div class="position-absolute bottom-0 start-0 p-2">
                                <span class="badge bg-primary" style="font-size: 0.7rem; backdrop-filter: blur(4px); background: rgba(14, 165, 233, 0.9) !important; border: 1px solid rgba(255, 255, 255, 0.3);">
                                    TD: ${escapeHtml(patient.created_by_doctor_name)}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-2">
                            <span style="color: var(--accent); font-weight: 600;">
                                ${escapeHtml(fullName)}
                            </span>
                        </h6>
                        
                        <div class="mb-2">
                            ${patient.phone ? `
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    <div class="phone-number-container" style="position: relative; display: inline-block;" onclick="event.stopPropagation();">
                                        <a href="tel:${escapeHtml(patient.phone)}" 
                                           class="phone-number-link" 
                                           style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                            ${escapeHtml(patient.phone)}
                                        </a>
                                        <span class="phone-htooltip">
                                            <div class="phone-actions">
                                                <a href="tel:${escapeHtml(patient.phone)}" class="phone-action-btn" title="Call" onclick="event.stopPropagation();">
                                                    <i class="bi bi-telephone-fill"></i>
                                                    <span>Call</span>
                                                </a>
                                                <a href="https://wa.me/+2${escapeHtml(patient.phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp" onclick="event.stopPropagation();">
                                                    <i class="bi bi-whatsapp"></i>
                                                    <span>WhatsApp</span>
                                                </a>
                                            </div>
                                        </span>
                                    </div>
                                </small>
                            ` : ''}
                        </div>
                        
                        <div class="mt-auto pt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    ${patient.total_appointments || 0} visits
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    ${lastAppointment}
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-capsule me-1"></i>
                                    ${patient.prescriptions_count || 0} prescriptions
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-eyeglasses me-1"></i>
                                    ${patient.glasses_count || 0} glasses
                                </small>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="d-flex gap-1 justify-content-end mt-2 pt-2 border-top" onclick="event.stopPropagation();">
                                <a href="/doctor/patients/${patient.id}" 
                                   class="card-action-btn card-action-view" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   data-bs-title="View patient"
                                   onclick="event.stopPropagation();">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="card-action-btn card-action-book" 
                                        onclick="event.stopPropagation(); bookAppointment(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Book appointment">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                <button class="card-action-btn card-action-edit" 
                                        onclick="event.stopPropagation(); editPatient(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Edit patient">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="card-action-btn card-action-move" 
                                        onclick="event.stopPropagation(); showMovePatientModal(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Move to folder">
                                    <i class="bi bi-folder"></i>
                                </button>
                                <button class="card-action-btn card-action-delete" 
                                        onclick="event.stopPropagation(); deletePatient(${patient.id}, '${escapeHtml(fullName).replace(/'/g, "\\'")}')" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Delete patient">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Refresh tooltips
    setTimeout(() => {
        refreshTooltips();
    }, 100);
}

// Update pagination info for cards
function updatePaginationInfoCards() {
    const { currentPage, filteredPatients } = paginationState;
    const cardsItemsPerPage = document.getElementById('paginationLimitCards') 
        ? parseInt(document.getElementById('paginationLimitCards').value) || 24 
        : 24;
    
    document.getElementById('totalPatientsCountCards').textContent = filteredPatients.length;
    document.getElementById('totalPatientsCards').textContent = filteredPatients.length;
    
    if (cardsItemsPerPage === 'all' || cardsItemsPerPage === 'All') {
        document.getElementById('showingFromCards').textContent = filteredPatients.length > 0 ? '1' : '0';
        document.getElementById('showingToCards').textContent = filteredPatients.length;
        document.getElementById('paginationNavCards').style.display = 'none';
    } else {
        const startIndex = (currentPage - 1) * cardsItemsPerPage + 1;
        const endIndex = Math.min(currentPage * cardsItemsPerPage, filteredPatients.length);
        
        document.getElementById('showingFromCards').textContent = filteredPatients.length > 0 ? startIndex : '0';
        document.getElementById('showingToCards').textContent = endIndex;
        document.getElementById('paginationNavCards').style.display = 'flex';
    }
}

// Render pagination nav for cards
function renderPaginationNavCards() {
    const { currentPage, filteredPatients } = paginationState;
    const cardsItemsPerPage = document.getElementById('paginationLimitCards') 
        ? parseInt(document.getElementById('paginationLimitCards').value) || 24 
        : 24;
    
    if (cardsItemsPerPage === 'all' || cardsItemsPerPage === 'All') {
        return;
    }
    
    const totalPages = Math.ceil(filteredPatients.length / cardsItemsPerPage);
    const nav = document.getElementById('paginationNavCards');
    
    if (totalPages <= 1) {
        nav.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>
        </li>
    `;
    
    nav.innerHTML = html;
}

// Change page (works for both table and cards)
function changePage(page) {
    paginationState.currentPage = page;
    
    if (currentViewMode === 'table') {
        renderPatientsTable();
        updatePaginationInfo();
        renderPaginationNav();
    } else if (currentViewMode === 'cards') {
        renderPatientsCards();
        updatePaginationInfoCards();
        renderPaginationNavCards();
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ============================================
// Folders View Functions
// ============================================

// Auto-refresh interval for folders
let foldersRefreshInterval = null;

// Start auto-refresh for folders
function startFoldersAutoRefresh() {
    if (foldersRefreshInterval) clearInterval(foldersRefreshInterval);
    foldersRefreshInterval = setInterval(() => {
        if (currentViewMode === 'folders' && !currentFolderId) {
            loadFolders();
        }
    }, 30000); // كل 30 ثانية
}

// Stop auto-refresh for folders
function stopFoldersAutoRefresh() {
    if (foldersRefreshInterval) {
        clearInterval(foldersRefreshInterval);
        foldersRefreshInterval = null;
    }
}

// Update folder patient count locally
function updateFolderPatientCount(folderId, increment = 1) {
    const folder = foldersData.find(f => f.id === folderId);
    if (folder) {
        folder.patient_count = Math.max(0, (folder.patient_count || 0) + increment);
        renderFoldersView();
    }
}

// Separate data storage for system and custom folders
let systemFoldersData = [];
let customFoldersData = [];

// Load folders from API
function loadFolders() {
    fetch('/api/patient-folders', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Separate system and custom folders
            systemFoldersData = data.system_folders || [];
            customFoldersData = data.custom_folders || [];
            
            // Apply system folder preferences from localStorage
            const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
            systemFoldersData.forEach(folder => {
                if (systemFolderPrefs[folder.id]) {
                    if (systemFolderPrefs[folder.id].icon) {
                        folder.icon = systemFolderPrefs[folder.id].icon;
                    }
                    if (systemFolderPrefs[folder.id].gradient_color) {
                        folder.gradient_color = systemFolderPrefs[folder.id].gradient_color;
                    }
                }
            });
            
            // Keep foldersData for backward compatibility (merged)
            foldersData = [...systemFoldersData, ...customFoldersData];
            
            renderFoldersView();
        }
    })
    .catch(error => {
        console.error('Error loading folders:', error);
    });
}

// Render folders view
function renderFoldersView() {
    currentFolderId = null;
    currentFolderName = null;
    currentFolderType = null;
    
    // Show the header toggle buttons again
    const headerToggle = document.getElementById('viewModeToggleFoldersHeader');
    if (headerToggle) {
        headerToggle.style.display = 'flex';
    }
    
    // Show Create Folder button and clear header actions
    const createFolderBtn = document.querySelector('button[onclick="showCreateFolderModal()"]');
    if (createFolderBtn) {
        createFolderBtn.style.display = 'inline-block';
    }
    
    // Clear folder header actions
    const headerActionsContainer = document.querySelector('#viewModeToggleFoldersHeader .folder-header-actions');
    if (headerActionsContainer) {
        headerActionsContainer.innerHTML = '';
    }
    
    const container = document.getElementById('patientsFoldersContainer');
    
    // Check if both are empty
    if (systemFoldersData.length === 0 && customFoldersData.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-folder-x text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No folders available</p>
                <button class="btn btn-success mt-3" onclick="showCreateFolderModal()">
                    <i class="bi bi-folder-plus me-1"></i>
                    Create Your First Folder
                </button>
            </div>
        `;
        return;
    }
    
    // Hide Create Folder button when viewing System Folders
    if (createFolderBtn && systemFoldersData.length > 0) {
        createFolderBtn.style.display = 'none';
    } else if (createFolderBtn) {
        createFolderBtn.style.display = 'inline-block';
    }
    
    let html = '';
    
    // System Folders Section
    if (systemFoldersData.length > 0) {
        html += `
            <div class="system-folders-section mb-4">
                <h5 class="mb-3" style="color: var(--text); font-weight: 600;">
                    <i class="bi bi-folder-fill me-2" style="color: var(--accent);"></i>
                    System Folders
                </h5>
                <div class="row g-3">
        `;
        
        systemFoldersData.forEach(folder => {
            html += renderFolderCard(folder, true);
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    // Custom Folders Section
    if (customFoldersData.length > 0) {
        html += `
            <div class="custom-folders-section">
                <h5 class="mb-3" style="color: var(--text); font-weight: 600;">
                    <i class="bi bi-folder me-2" style="color: var(--accent);"></i>
                    Custom Folders
                </h5>
                <div class="row g-3">
        `;
        
        customFoldersData.forEach(folder => {
            html += renderFolderCard(folder, false);
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

// Render a single folder card (helper function)
function renderFolderCard(folder, isSystem) {
    const folderId = folder.id;
    
    // Get folder icon (default or custom)
    // Check localStorage for system folders first
    let folderIcon = folder.icon;
    if (isSystem) {
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        if (systemFolderPrefs[folderId] && systemFolderPrefs[folderId].icon) {
            folderIcon = systemFolderPrefs[folderId].icon;
        } else if (!folderIcon) {
            folderIcon = 'bi-folder-fill';
        }
    } else {
        if (!folderIcon) {
            folderIcon = 'bi-folder';
        }
    }
    
    // Get gradient color (default or custom)
    // Check localStorage for system folders first
    let gradientColor = folder.gradient_color;
    if (isSystem) {
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        if (systemFolderPrefs[folderId] && systemFolderPrefs[folderId].gradient_color) {
            gradientColor = systemFolderPrefs[folderId].gradient_color;
        } else if (!gradientColor) {
            gradientColor = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    } else {
        // Check localStorage for custom folders too (in case they were customized)
        const customFolderPrefs = JSON.parse(localStorage.getItem('customFolderPreferences') || '{}');
        if (customFolderPrefs[folderId] && customFolderPrefs[folderId].gradient_color) {
            gradientColor = customFolderPrefs[folderId].gradient_color;
        } else if (!gradientColor) {
            gradientColor = 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
        }
    }
    
    // Get doctor image for system folders
    let doctorImageHtml = '';
    if (isSystem && folder.profile_image) {
        doctorImageHtml = `
            <img src="${escapeHtml(folder.profile_image)}" 
                 alt="${escapeHtml(folder.name)}" 
                 class="folder-doctor-avatar"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        `;
    }
    
    // Get doctor initial
    const doctorInitial = folder.name ? folder.name.charAt(0).toUpperCase() : 'F';
    
    // Escape gradient for use in style attribute - use single quotes to avoid issues
    const safeGradient = gradientColor.replace(/'/g, "\\'");
    
    return `
        <div class="col-md-4 col-lg-3">
            <div class="card folder-card h-100" 
                 style="border: 1px solid var(--border); cursor: pointer; background: ${safeGradient} !important; background-color: transparent !important;" 
                 onclick="openFolder('${folderId}')"
                 data-gradient="${escapeHtml(gradientColor)}"
                 data-folder-id="${folderId}">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="folder-icon-wrapper" style="width: 80px; height: 80px; position: relative;">
                            ${isSystem && folder.profile_image ? `
                                <div class="folder-doctor-image-container" style="width: 100%; height: 100%; border-radius: 50%; overflow: hidden; background: rgba(255, 255, 255, 0.25); display: flex; align-items: center; justify-content: center; border: 3px solid rgba(255, 255, 255, 0.4); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                                    ${doctorImageHtml}
                                    <div class="folder-doctor-avatar-fallback" style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                        ${doctorInitial}
                                    </div>
                                </div>
                            ` : `
                                <div class="folder-icon-large" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.25); border-radius: 50%; border: 3px solid rgba(255, 255, 255, 0.4); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                                    <i class="bi ${folderIcon}" style="font-size: 3rem; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"></i>
                                </div>
                            `}
                        </div>
                        ${!isSystem ? `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link p-0 text-white" 
                                        type="button" 
                                        data-bs-toggle="dropdown"
                                        onclick="event.stopPropagation();"
                                        style="opacity: 0.9; backdrop-filter: blur(4px);">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showChangeFolderIconModal(${folder.id}, '${escapeHtml(folderIcon).replace(/'/g, "\\'")}', '${escapeHtml(gradientColor).replace(/'/g, "\\'")}');">
                                            <i class="bi bi-palette me-2"></i>
                                            Change Icon & Color
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showRenameFolderModal(${folder.id}, '${escapeHtml(folder.name).replace(/'/g, "\\'")}');">
                                            <i class="bi bi-pencil me-2"></i>
                                            Rename
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFolder(${folder.id});">
                                            <i class="bi bi-trash me-2"></i>
                                            Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        ` : `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link p-0 text-white" 
                                        type="button" 
                                        data-bs-toggle="dropdown"
                                        onclick="event.stopPropagation();"
                                        style="opacity: 0.9; backdrop-filter: blur(4px);">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showChangeFolderIconModal('${folderId}', '${escapeHtml(folderIcon).replace(/'/g, "\\'")}', '${escapeHtml(gradientColor).replace(/'/g, "\\'")}');">
                                            <i class="bi bi-palette me-2"></i>
                                            Change Icon & Color
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        `}
                    </div>
                    <h6 class="card-title mb-2 text-white" style="font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        ${escapeHtml(folder.name)}
                    </h6>
                    <p class="text-white mb-2" style="opacity: 0.95; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        <i class="bi bi-people me-1"></i>
                        <span class="folder-patient-count">${folder.patient_count || 0}</span> patients
                        ${folder.sub_folders_count > 0 ? ` • <i class="bi bi-folder me-1"></i>${folder.sub_folders_count} sub-folders` : ''}
                    </p>
                    ${isSystem ? `
                        <small class="text-white" style="opacity: 0.85; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                            <i class="bi bi-info-circle me-1"></i>
                            System folder
                        </small>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

// Open folder and show patients
let currentFolderId = null;
let currentFolderName = null;
let currentFolderType = null; // 'system' or 'custom'

function openFolder(folderId) {
    currentFolderId = folderId;
    
    // Determine folder type and get folder info
    const isSystem = folderId.toString().startsWith('system_');
    currentFolderType = isSystem ? 'system' : 'custom';
    
    // Get folder name from appropriate data source
    let folder = null;
    if (isSystem) {
        folder = systemFoldersData.find(f => f.id === folderId);
    } else {
        folder = customFoldersData.find(f => f.id === folderId);
    }
    
    if (!folder) {
        folder = foldersData.find(f => f.id === folderId);
    }
    
    currentFolderName = folder ? folder.name : 'Folder';
    
    // Hide the header toggle buttons to avoid duplication
    const headerToggle = document.getElementById('viewModeToggleFoldersHeader');
    if (headerToggle) {
        headerToggle.style.display = 'none';
    }
    
    // Hide Create Folder button when in system folder
    const createFolderBtn = document.querySelector('button[onclick="showCreateFolderModal()"]');
    if (createFolderBtn && isSystem) {
        createFolderBtn.style.display = 'none';
    }
    
    // Create header actions (Create Sub-folder + Group by buttons for system folders)
    const headerActions = isSystem ? `
        <div class="d-flex align-items-center gap-2" style="flex-wrap: wrap;">
            <button class="btn btn-sm folder-action-btn" onclick="showCreateSubFolderModal('${folderId}', '${currentFolderType}', '${escapeHtml(currentFolderName).replace(/'/g, "\\'")}')" title="Create Sub-folder">
                <i class="bi bi-folder-plus me-1"></i>
                Create Sub-folder
            </button>
            <button class="btn btn-sm folder-action-btn" onclick="quickSortSystemFolder('${folderId}', 'by_date_created')" title="Group by Date Created">
                <i class="bi bi-calendar-event me-1"></i>
                Group by Date Created
            </button>
            <button class="btn btn-sm folder-action-btn" onclick="quickSortSystemFolder('${folderId}', 'by_visits')" title="Group by Visits">
                <i class="bi bi-calendar-check me-1"></i>
                Group by Visits
            </button>
        </div>
    ` : `
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm folder-action-btn" onclick="showCreateSubFolderModal('${folderId}', '${currentFolderType}', '${escapeHtml(currentFolderName).replace(/'/g, "\\'")}')" title="Create Sub-folder">
                <i class="bi bi-folder-plus me-1"></i>
                Create Sub-folder
            </button>
        </div>
    `;
    
    // Update UI to show folder with sub-folders and patients
    const container = document.getElementById('patientsFoldersContainer');
    
    container.innerHTML = `
        <div class="mb-4">
            <button class="btn btn-link text-decoration-none p-0 mb-2 back-to-folders-btn" 
                    onclick="renderFoldersView()"
                    style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--accent); font-weight: 500; transition: all 0.2s ease; padding: 0.5rem 0 !important;">
                <i class="bi bi-arrow-left" style="font-size: 1.1rem;"></i>
                <i class="bi bi-folder" style="font-size: 0.9rem; opacity: 0.8;"></i>
                <span>Back to Folders</span>
            </button>
            <div class="mt-2 d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-1" style="color: var(--text); font-weight: 600;">
                        ${escapeHtml(currentFolderName)}
                    </h5>
                    <small class="text-muted">Patients in this folder</small>
                </div>
                ${headerActions}
            </div>
        </div>
        <div id="subFoldersContainer" class="mb-4">
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading sub-folders...</span>
                </div>
            </div>
        </div>
        <div id="folderPatientsContainer" class="row g-3">
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    `;
    
    // Load sub-folders first
    loadSubFolders(folderId, currentFolderType);
    
    // Then load patients
    
    // Load folder patients
    fetch(`/api/patient-folders/${folderId}/patients`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // If there are sub-folders, render them
            if (data.folders && data.folders.length > 0) {
                renderSubFolders(data.folders, folderId, currentFolderType);
            } else {
                // Clear sub-folders container if no sub-folders
                const subFoldersContainer = document.getElementById('subFoldersContainer');
                if (subFoldersContainer) {
                    subFoldersContainer.innerHTML = '';
                }
            }
            
            // Render patients
            if (data.patients) {
                renderFolderPatients(data.patients);
            }
        }
    })
    .catch(error => {
        console.error('Error loading folder patients:', error);
    });
}

// Load sub-folders for a parent folder
function loadSubFolders(parentId, parentType) {
    fetch(`/api/patient-folders/${parentId}/sub-folders/${parentType}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('subFoldersContainer');
        if (data.ok && data.sub_folders && data.sub_folders.length > 0) {
            renderSubFolders(data.sub_folders, parentId, parentType);
        } else {
            container.innerHTML = '';
        }
    })
    .catch(error => {
        console.error('Error loading sub-folders:', error);
        const container = document.getElementById('subFoldersContainer');
        container.innerHTML = '';
    });
}

// Render sub-folders
function renderSubFolders(subFolders, parentId, parentType) {
    const container = document.getElementById('subFoldersContainer');
    
    if (subFolders.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    let html = `
        <div class="sub-folders-section mb-4">
            <h6 class="mb-3" style="color: var(--text); font-weight: 600;">
                <i class="bi bi-folder2-open me-2" style="color: var(--accent);"></i>
                Sub-folders
            </h6>
            <div class="row g-2">
    `;
    
    subFolders.forEach(subFolder => {
        const safeGradient = (subFolder.gradient_color || 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)').replace(/'/g, "\\'");
        const subFolderIcon = subFolder.icon || 'bi-folder';
        
        html += `
            <div class="col-md-3 col-lg-2">
                <div class="card sub-folder-card h-100" 
                     style="border: 1px solid var(--border); cursor: pointer; background: ${safeGradient} !important; background-color: transparent !important;" 
                     onclick="openSubFolder(${subFolder.id})">
                    <div class="card-body p-3 d-flex flex-column align-items-center text-center position-relative">
                        <div class="dropdown position-absolute" style="top: 0.5rem; right: 0.5rem; z-index: 10;" onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-link p-0 text-white" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                    style="opacity: 0.9; backdrop-filter: blur(4px);">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showChangeFolderIconModal(${subFolder.id}, '${escapeHtml(subFolderIcon).replace(/'/g, "\\'")}', '${escapeHtml(subFolder.gradient_color || '').replace(/'/g, "\\'")}');">
                                        <i class="bi bi-palette me-2"></i>
                                        Change Icon & Color
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.stopPropagation(); showRenameFolderModal(${subFolder.id}, '${escapeHtml(subFolder.name).replace(/'/g, "\\'")}');">
                                        <i class="bi bi-pencil me-2"></i>
                                        Rename
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFolder(${subFolder.id});">
                                        <i class="bi bi-trash me-2"></i>
                                        Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <i class="bi ${subFolderIcon}" style="font-size: 2rem; color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"></i>
                        </div>
                        <h6 class="card-title mb-1 text-white" style="font-size: 0.85rem; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                            ${escapeHtml(subFolder.name)}
                        </h6>
                        <small class="text-white" style="opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                            <i class="bi bi-people me-1"></i>
                            ${subFolder.patient_count || 0}
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// Open sub-folder
function openSubFolder(subFolderId) {
    // Treat sub-folder like a regular folder
    openFolder(subFolderId.toString());
}

// Show create sub-folder modal
function showCreateSubFolderModal(parentId, parentType, parentName) {
    const modal = new bootstrap.Modal(document.getElementById('createSubFolderModal'));
    document.getElementById('subFolderParentId').value = parentId;
    document.getElementById('subFolderParentType').value = parentType;
    document.getElementById('subFolderParentName').textContent = parentName;
    document.getElementById('subFolderName').value = '';
    document.getElementById('createSubFolderMessage').classList.add('d-none');
    modal.show();
}

// Quick sort system folder
function quickSortSystemFolder(systemFolderId, sortType) {
    if (!confirm(`Are you sure you want to sort patients by ${sortType === 'by_date_created' ? 'Date Created' : 'Visits'}? This will create sub-folders automatically.`)) {
        return;
    }
    
    // Show loading
    const container = document.getElementById('subFoldersContainer');
    const originalContent = container.innerHTML;
    container.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Sorting patients...</span>
            </div>
            <p class="mt-2 text-muted">Sorting patients into sub-folders...</p>
        </div>
    `;
    
    fetch(`/api/patient-folders/${systemFolderId}/quick-sort/${sortType}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification(`Patients sorted successfully! ${data.patients_distributed} patients distributed into ${data.sub_folders_created.length} sub-folders.`, 'success');
            // Reload sub-folders
            loadSubFolders(systemFolderId, 'system');
        } else {
            showNotification(data.error || 'Failed to sort patients', 'error');
            container.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error sorting patients:', error);
        showNotification('An error occurred while sorting patients', 'error');
        container.innerHTML = originalContent;
    });
}

// Render patients in a folder (styled like cards view)
function renderFolderPatients(patients) {
    const container = document.getElementById('folderPatientsContainer');
    
    if (patients.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No patients in this folder</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    patients.forEach(patient => {
        const age = patient.dob ? calculateAge(patient.dob) : null;
        const ageText = age !== null ? `${age} years` : 'Not specified';
        const lastAppointment = patient.last_appointment_datetime 
            ? formatDate(patient.last_appointment_datetime) 
            : (patient.last_visit ? formatDate(patient.last_visit) : 'Not visited yet');
        
        const firstName = patient.first_name || '';
        const lastName = patient.last_name || '';
        const fullName = `${firstName} ${lastName}`.trim();
        
        // Get patient image URL (using dedicated endpoint for cards/folders views)
        const imageUrl = patient.latest_attachment_id 
            ? `/api/patients/images/${patient.latest_attachment_id}`
            : null;
        
        // Gender icon
        const genderIcon = patient.gender === 'Female' 
            ? '<i class="bi bi-gender-female"></i>' 
            : (patient.gender === 'Male' 
                ? '<i class="bi bi-gender-male"></i>' 
                : '<i class="bi bi-gender-ambiguous"></i>');
        
        const genderBadgeClass = patient.gender === 'Female' 
            ? 'bg-pink' 
            : (patient.gender === 'Male' ? 'bg-primary' : 'bg-secondary');
        
        // Check if patient is in a system folder (cannot remove from system folders)
        const isSystemFolder = currentFolderId && currentFolderId.toString().startsWith('system_');
        
        html += `
            <div class="col-md-4 col-lg-3 mb-3">
                <div class="card patient-card clickable h-100" 
                     style="border: 1px solid var(--border); cursor: pointer;" 
                     onclick="viewPatient(${patient.id})">
                    <!-- Patient Image -->
                    <div class="position-relative" style="height: 200px; overflow: hidden; background: var(--bg-alt);">
                        ${imageUrl ? `
                            <img src="${imageUrl}" 
                                 alt="${escapeHtml(fullName)}" 
                                 class="w-100 h-100 patient-card-image" 
                                 style="object-fit: cover;"
                                 data-patient-id="${patient.id}"
                                 data-image-url="${imageUrl}"
                                 onerror="(function(img, url, pid) { console.error('[Folder View] Image load failed:', {patientId: pid, url: url, img: img}); img.style.display='none'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='flex'; } })(this, '${imageUrl}', ${patient.id});"
                                 onload="(function(img) { img.style.display='block'; const placeholder = img.nextElementSibling; if (placeholder) { placeholder.style.display='none'; } })(this);">
                        ` : ''}
                        <div class="d-flex align-items-center justify-content-center h-100 w-100 patient-card-placeholder" 
                             style="background: linear-gradient(135deg, var(--accent) 0%, var(--bg-alt) 100%); ${imageUrl ? 'display: none;' : 'display: flex;'}">
                            <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <!-- Badges overlay -->
                        <div class="position-absolute top-0 start-0 p-2 d-flex gap-2 flex-wrap">
                            ${age !== null ? `
                                <span class="badge bg-info">${age} years</span>
                            ` : ''}
                            <span class="badge ${genderBadgeClass}">${genderIcon}</span>
                        </div>
                        <!-- Treatment Doctor Badge -->
                        ${patient.created_by_doctor_name ? `
                            <div class="position-absolute bottom-0 start-0 p-2">
                                <span class="badge bg-primary" style="font-size: 0.7rem; backdrop-filter: blur(4px); background: rgba(14, 165, 233, 0.9) !important; border: 1px solid rgba(255, 255, 255, 0.3);">
                                    TD: ${escapeHtml(patient.created_by_doctor_name)}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-2">
                            <span style="color: var(--accent); font-weight: 600;">
                                ${escapeHtml(fullName)}
                            </span>
                        </h6>
                        
                        <div class="mb-2">
                            ${patient.phone ? `
                                <small class="text-muted d-block">
                                    <i class="bi bi-telephone me-1"></i>
                                    <div class="phone-number-container" style="position: relative; display: inline-block;" onclick="event.stopPropagation();">
                                        <a href="tel:${escapeHtml(patient.phone)}" 
                                           class="phone-number-link" 
                                           style="text-decoration: none; color: var(--accent); font-weight: 500; cursor: pointer; transition: all 0.2s ease;">
                                            ${escapeHtml(patient.phone)}
                                        </a>
                                        <span class="phone-htooltip">
                                            <div class="phone-actions">
                                                <a href="tel:${escapeHtml(patient.phone)}" class="phone-action-btn" title="Call" onclick="event.stopPropagation();">
                                                    <i class="bi bi-telephone-fill"></i>
                                                    <span>Call</span>
                                                </a>
                                                <a href="https://wa.me/+2${escapeHtml(patient.phone).replace(/[^0-9]/g, '')}" target="_blank" class="phone-action-btn whatsapp-btn" title="WhatsApp" onclick="event.stopPropagation();">
                                                    <i class="bi bi-whatsapp"></i>
                                                    <span>WhatsApp</span>
                                                </a>
                                            </div>
                                        </span>
                                    </div>
                                </small>
                            ` : ''}
                        </div>
                        
                        <div class="mt-auto pt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    ${patient.total_appointments || 0} visits
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    ${lastAppointment}
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-capsule me-1"></i>
                                    ${patient.prescriptions_count || 0} prescriptions
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-eyeglasses me-1"></i>
                                    ${patient.glasses_count || 0} glasses
                                </small>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="d-flex gap-1 justify-content-end mt-2 pt-2 border-top" onclick="event.stopPropagation();">
                                <a href="/doctor/patients/${patient.id}" 
                                   class="card-action-btn card-action-view" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   data-bs-title="View patient"
                                   onclick="event.stopPropagation();">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="card-action-btn card-action-book" 
                                        onclick="event.stopPropagation(); bookAppointment(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Book appointment">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                <button class="card-action-btn card-action-edit" 
                                        onclick="event.stopPropagation(); editPatient(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Edit patient">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="card-action-btn card-action-move" 
                                        onclick="event.stopPropagation(); showMovePatientModal(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Move to folder">
                                    <i class="bi bi-folder"></i>
                                </button>
                                <button class="card-action-btn card-action-add" 
                                        onclick="event.stopPropagation(); showAddToFolderModal(${patient.id})" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="Add to folder"
                                        style="background: var(--success, #28a745); color: white;">
                                    <i class="bi bi-folder-plus"></i>
                                </button>
                                ${!isSystemFolder && currentFolderId ? `
                                    <button class="card-action-btn card-action-remove" 
                                            onclick="event.stopPropagation(); removePatientFromFolder(${patient.id})" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Remove from folder"
                                            style="background: var(--danger, #dc3545); color: white;">
                                        <i class="bi bi-folder-minus"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Refresh tooltips
    setTimeout(() => {
        refreshTooltips();
    }, 100);
}

// ============================================
// Folder Management Functions
// ============================================

// Show create folder modal
function showCreateFolderModal() {
    const modal = new bootstrap.Modal(document.getElementById('createFolderModal'));
    document.getElementById('folderName').value = '';
    document.getElementById('createFolderMessage').classList.add('d-none');
    modal.show();
}

// Show change folder icon modal
function showChangeFolderIconModal(folderId, currentIcon, currentGradient) {
    const modal = new bootstrap.Modal(document.getElementById('changeFolderIconModal'));
    
    // Check if it's a system folder and get preferences from localStorage
    const isSystemFolder = folderId.toString().startsWith('system_');
    let actualIcon = currentIcon;
    let actualGradient = currentGradient;
    
    if (isSystemFolder) {
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        if (systemFolderPrefs[folderId]) {
            actualIcon = systemFolderPrefs[folderId].icon || currentIcon || 'bi-folder-fill';
            actualGradient = systemFolderPrefs[folderId].gradient_color || currentGradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        } else {
            actualIcon = currentIcon || 'bi-folder-fill';
            actualGradient = currentGradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    } else {
        actualIcon = currentIcon || 'bi-folder';
        actualGradient = currentGradient || 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
    }
    
    document.getElementById('changeFolderIconId').value = folderId;
    document.getElementById('selectedIcon').value = actualIcon;
    document.getElementById('selectedGradient').value = actualGradient;
    document.getElementById('customGradient').value = '';
    document.getElementById('changeFolderIconMessage').classList.add('d-none');
    
    // Populate Bootstrap Icons
    const iconGrid = document.getElementById('iconSelectionGrid');
    const folderIcons = [
        'bi-folder', 'bi-folder-fill', 'bi-folder2', 'bi-folder2-open',
        'bi-folder-symlink', 'bi-folder-symlink-fill', 'bi-folder-check',
        'bi-folder-x', 'bi-folder-plus', 'bi-folder-minus',
        'bi-archive', 'bi-archive-fill', 'bi-briefcase', 'bi-briefcase-fill',
        'bi-collection', 'bi-collection-fill', 'bi-inbox', 'bi-inbox-fill'
    ];
    
    iconGrid.innerHTML = '';
    folderIcons.forEach(iconClass => {
        const isSelected = iconClass === currentIcon;
        iconGrid.innerHTML += `
            <div class="col-3 col-md-2">
                <div class="icon-option ${isSelected ? 'selected' : ''}" 
                     data-icon="${iconClass}"
                     onclick="selectIcon('${iconClass}')"
                     style="padding: 1rem; text-align: center; border: 2px solid ${isSelected ? 'var(--accent)' : 'var(--border)'}; border-radius: 8px; cursor: pointer; background: ${isSelected ? 'var(--accent)' : 'var(--bg)'}; transition: all 0.2s ease;">
                    <i class="bi ${iconClass}" style="font-size: 2rem; color: ${isSelected ? 'white' : 'var(--text)'};"></i>
                </div>
            </div>
        `;
    });
    
    // Populate Gradient Presets
    const gradientGrid = document.getElementById('gradientSelectionGrid');
    const gradients = [
        { name: 'Purple', value: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' },
        { name: 'Pink', value: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' },
        { name: 'Blue', value: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' },
        { name: 'Green', value: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' },
        { name: 'Orange', value: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' },
        { name: 'Teal', value: 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)' },
        { name: 'Red', value: 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)' },
        { name: 'Dark', value: 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)' }
    ];
    
    gradientGrid.innerHTML = '';
    gradients.forEach((grad, index) => {
        // Compare gradients more flexibly (handle whitespace differences)
        const isSelected = grad.value.trim() === actualGradient.trim();
        gradientGrid.innerHTML += `
            <div class="col-6 col-md-3">
                <div class="gradient-option ${isSelected ? 'selected' : ''}" 
                     data-gradient="${grad.value}"
                     onclick="selectGradient('${grad.value}')"
                     style="height: 60px; border: 2px solid ${isSelected ? 'var(--accent)' : 'var(--border)'}; border-radius: 8px; cursor: pointer; background: ${grad.value}; position: relative; overflow: hidden;">
                    <div style="position: absolute; bottom: 4px; left: 4px; right: 4px; background: rgba(0,0,0,0.6); color: white; padding: 2px 4px; border-radius: 4px; font-size: 0.7rem; text-align: center;">
                        ${grad.name}
                    </div>
                    ${isSelected ? '<i class="bi bi-check-circle-fill" style="position: absolute; top: 4px; right: 4px; color: white; font-size: 1.2rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"></i>' : ''}
                </div>
            </div>
        `;
    });
    
    modal.show();
}

// Select icon
function selectIcon(iconClass) {
    document.getElementById('selectedIcon').value = iconClass;
    document.querySelectorAll('.icon-option').forEach(el => {
        el.classList.remove('selected');
        el.style.borderColor = 'var(--border)';
        el.style.background = 'var(--bg)';
        el.querySelector('i').style.color = 'var(--text)';
    });
    const selected = document.querySelector(`[data-icon="${iconClass}"]`);
    if (selected) {
        selected.classList.add('selected');
        selected.style.borderColor = 'var(--accent)';
        selected.style.background = 'var(--accent)';
        selected.querySelector('i').style.color = 'white';
    }
}

// Select gradient
function selectGradient(gradientValue) {
    document.getElementById('selectedGradient').value = gradientValue;
    document.getElementById('customGradient').value = '';
    document.querySelectorAll('.gradient-option').forEach(el => {
        el.classList.remove('selected');
        el.style.borderColor = 'var(--border)';
        el.querySelector('.bi-check-circle-fill')?.remove();
    });
    const selected = document.querySelector(`[data-gradient="${gradientValue}"]`);
    if (selected) {
        selected.classList.add('selected');
        selected.style.borderColor = 'var(--accent)';
        selected.innerHTML += '<i class="bi bi-check-circle-fill" style="position: absolute; top: 4px; right: 4px; color: white; font-size: 1.2rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"></i>';
    }
}

// Create folder
document.getElementById('createFolderForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('folderName').value.trim();
    const messageEl = document.getElementById('createFolderMessage');
    
    if (!name) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Folder name is required';
        messageEl.classList.remove('d-none');
        return;
    }
    
    fetch('/api/patient-folders', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('createFolderModal'));
            modal.hide();
            loadFolders();
        } else {
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = data.error || 'Failed to create folder';
            messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error creating folder:', error);
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'An error occurred while creating the folder';
        messageEl.classList.remove('d-none');
    });
});

// Create sub-folder
document.getElementById('createSubFolderForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('subFolderName').value.trim();
    const parentId = document.getElementById('subFolderParentId').value;
    const parentType = document.getElementById('subFolderParentType').value;
    const messageEl = document.getElementById('createSubFolderMessage');
    
    if (!name) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Sub-folder name is required';
        messageEl.classList.remove('d-none');
        return;
    }
    
    fetch('/api/patient-folders', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
            name,
            parent_id: parentId,
            parent_type: parentType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('createSubFolderModal'));
            modal.hide();
            showNotification('Sub-folder created successfully', 'success');
            // Reload sub-folders if we're in a folder view
            if (currentFolderId) {
                loadSubFolders(currentFolderId, currentFolderType);
            } else {
                loadFolders();
            }
        } else {
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = data.error || 'Failed to create sub-folder';
            messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error creating sub-folder:', error);
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'An error occurred while creating the sub-folder';
        messageEl.classList.remove('d-none');
    });
});

// Show rename folder modal
function showRenameFolderModal(folderId, currentName) {
    const modal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
    document.getElementById('renameFolderId').value = folderId;
    document.getElementById('renameFolderName').value = currentName;
    document.getElementById('renameFolderMessage').classList.add('d-none');
    modal.show();
}

// Rename folder
document.getElementById('renameFolderForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const folderId = document.getElementById('renameFolderId').value;
    const name = document.getElementById('renameFolderName').value.trim();
    const messageEl = document.getElementById('renameFolderMessage');
    
    if (!name) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Folder name is required';
        messageEl.classList.remove('d-none');
        return;
    }
    
    fetch(`/api/patient-folders/${folderId}`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('renameFolderModal'));
            modal.hide();
            showNotification('Folder renamed successfully', 'success');
            // If we're in a folder view, reload sub-folders
            if (currentFolderId) {
                const isSystem = currentFolderId.toString().startsWith('system_');
                const parentType = isSystem ? 'system' : 'custom';
                loadSubFolders(currentFolderId, parentType);
            } else {
                loadFolders();
            }
        } else {
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = data.error || 'Failed to rename folder';
            messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error renaming folder:', error);
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'An error occurred while renaming the folder';
        messageEl.classList.remove('d-none');
    });
});

// Delete folder
function deleteFolder(folderId) {
    if (!confirm('Are you sure you want to delete this folder? Patients will not be deleted, only removed from the folder.')) {
        return;
    }
    
    fetch(`/api/patient-folders/${folderId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification('Folder deleted successfully', 'success');
            // If we're in a folder view, reload sub-folders
            if (currentFolderId) {
                const isSystem = currentFolderId.toString().startsWith('system_');
                const parentType = isSystem ? 'system' : 'custom';
                loadSubFolders(currentFolderId, parentType);
            } else {
                loadFolders();
            }
        } else {
            showNotification(data.error || 'Failed to delete folder', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting folder:', error);
        showNotification('An error occurred while deleting the folder', 'error');
    });
}

// Load all folders recursively (including sub-folders) for modals
async function loadAllFoldersForModal() {
    const allFolders = [];
    
    // Add system folders
    for (const systemFolder of systemFoldersData) {
        allFolders.push({
            id: systemFolder.id,
            name: systemFolder.name,
            type: 'system',
            level: 0
        });
        
        // Load sub-folders for system folder
        try {
            const response = await fetch(`/api/patient-folders/${systemFolder.id}/sub-folders/system`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.ok && data.sub_folders) {
                data.sub_folders.forEach(subFolder => {
                    allFolders.push({
                        id: subFolder.id,
                        name: subFolder.name,
                        type: 'custom',
                        level: 1,
                        parentName: systemFolder.name
                    });
                });
            }
        } catch (error) {
            console.error('Error loading system sub-folders:', error);
        }
    }
    
    // Add custom folders
    for (const customFolder of customFoldersData) {
        allFolders.push({
            id: customFolder.id,
            name: customFolder.name,
            type: 'custom',
            level: 0
        });
        
        // Load sub-folders for custom folder
        try {
            const response = await fetch(`/api/patient-folders/${customFolder.id}/sub-folders/custom`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.ok && data.sub_folders) {
                data.sub_folders.forEach(subFolder => {
                    allFolders.push({
                        id: subFolder.id,
                        name: subFolder.name,
                        type: 'custom',
                        level: 1,
                        parentName: customFolder.name
                    });
                });
            }
        } catch (error) {
            console.error('Error loading custom sub-folders:', error);
        }
    }
    
    return allFolders;
}

// Show move patient modal
async function showMovePatientModal(patientId) {
    const modal = new bootstrap.Modal(document.getElementById('movePatientModal'));
    document.getElementById('movePatientId').value = patientId;
    document.getElementById('movePatientMessage').classList.add('d-none');
    
    // Set modal title
    const modalTitle = document.querySelector('#movePatientModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = '<i class="bi bi-folder me-2"></i>Move Patient to Folder';
    }
    
    // Change button text
    const buttonText = document.getElementById('movePatientButtonText');
    if (buttonText) {
        buttonText.textContent = 'Move Patient';
    }
    
    // Populate folder select
    const select = document.getElementById('movePatientFolderSelect');
    select.innerHTML = '<option value="">-- Select Folder --</option>';
    
    // Load all folders including sub-folders
    const allFolders = await loadAllFoldersForModal();
    
    allFolders.forEach(folder => {
        const option = document.createElement('option');
        option.value = folder.id;
        const displayName = folder.level === 1 
            ? `${folder.parentName} > ${folder.name}`
            : folder.name;
        option.textContent = displayName;
        select.appendChild(option);
    });
    
    modal.show();
}

// Confirm move/add patient
function confirmMovePatient() {
    const patientId = document.getElementById('movePatientId').value;
    const folderId = document.getElementById('movePatientFolderSelect').value;
    const messageEl = document.getElementById('movePatientMessage');
    
    if (!folderId) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Please select a folder';
        messageEl.classList.remove('d-none');
        return;
    }
    
    fetch(`/api/patient-folders/${folderId}/patients`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ patient_id: parseInt(patientId) })
    })
    .then(response => response.json())
    .then(data => {
            if (data.ok) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('movePatientModal'));
            modal.hide();
            
            // Reset modal title and button text
            const modalTitle = document.querySelector('#movePatientModal .modal-title');
            if (modalTitle) {
                modalTitle.innerHTML = '<i class="bi bi-folder me-2"></i>Move Patient to Folder';
            }
            const buttonText = document.getElementById('movePatientButtonText');
            if (buttonText) {
                buttonText.textContent = 'Move Patient';
            }
            
            if (currentFolderId) {
                openFolder(currentFolderId);
                // Update patient count locally
                updateFolderPatientCount(currentFolderId, 1);
            } else {
                // Refresh current view if in folders view
                if (currentViewMode === 'folders') {
                    loadFolders();
                }
            }
            showNotification('Patient added to folder successfully', 'success');
        } else {
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = data.error || 'Failed to add patient to folder';
            messageEl.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error adding patient to folder:', error);
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'An error occurred while adding the patient to folder';
        messageEl.classList.remove('d-none');
    });
}

// Change folder icon and color
document.getElementById('changeFolderIconForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const folderId = document.getElementById('changeFolderIconId').value;
    const icon = document.getElementById('selectedIcon').value;
    const gradientColor = document.getElementById('selectedGradient').value || document.getElementById('customGradient').value;
    const messageEl = document.getElementById('changeFolderIconMessage');
    
    if (!icon || !gradientColor) {
        messageEl.className = 'alert alert-danger';
        messageEl.textContent = 'Please select an icon and gradient color';
        messageEl.classList.remove('d-none');
        return;
    }
    
    // Check if it's a system folder (starts with 'system_')
    const isSystemFolder = folderId.toString().startsWith('system_');
    
    if (isSystemFolder) {
        // Store system folder customization in localStorage
        const systemFolderPrefs = JSON.parse(localStorage.getItem('systemFolderPreferences') || '{}');
        systemFolderPrefs[folderId] = {
            icon: icon,
            gradient_color: gradientColor
        };
        localStorage.setItem('systemFolderPreferences', JSON.stringify(systemFolderPrefs));
        
        // Update the folder in foldersData immediately
        const folder = foldersData.find(f => f.id === folderId);
        if (folder) {
            folder.icon = icon;
            folder.gradient_color = gradientColor;
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('changeFolderIconModal'));
        modal.hide();
        
        // Force re-render to apply changes
        renderFoldersView();
        showNotification('Folder icon and color updated successfully', 'success');
    } else {
        // Update custom folder via API
        const actualFolderId = parseInt(folderId);
        
        fetch(`/api/patient-folders/${actualFolderId}`, {
            method: 'PUT',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ icon, gradient_color: gradientColor })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('changeFolderIconModal'));
                modal.hide();
                showNotification('Folder icon and color updated successfully', 'success');
                // If we're in a folder view, reload sub-folders
                if (currentFolderId) {
                    const isSystem = currentFolderId.toString().startsWith('system_');
                    const parentType = isSystem ? 'system' : 'custom';
                    loadSubFolders(currentFolderId, parentType);
                } else {
                    loadFolders();
                }
            } else {
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = data.error || 'Failed to update folder';
                messageEl.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Error updating folder icon:', error);
            messageEl.className = 'alert alert-danger';
            messageEl.textContent = 'An error occurred while updating the folder';
            messageEl.classList.remove('d-none');
        });
    }
});

// Remove patient from folder
function removePatientFromFolder(patientId) {
    if (!currentFolderId) return;
    
    if (!confirm('Remove this patient from the folder?')) {
        return;
    }
    
    fetch(`/api/patient-folders/${currentFolderId}/patients/${patientId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Update patient count locally before refreshing
            updateFolderPatientCount(currentFolderId, -1);
            openFolder(currentFolderId);
            showNotification('Patient removed from folder successfully', 'success');
        } else {
            showNotification(data.error || 'Failed to remove patient', 'error');
        }
    })
    .catch(error => {
        console.error('Error removing patient:', error);
        showNotification('An error occurred while removing the patient', 'error');
    });
}

// Show add to folder modal
async function showAddToFolderModal(patientId) {
    const modal = new bootstrap.Modal(document.getElementById('movePatientModal'));
    document.getElementById('movePatientId').value = patientId;
    document.getElementById('movePatientMessage').classList.add('d-none');
    
    // Change modal title
    const modalTitle = document.querySelector('#movePatientModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = '<i class="bi bi-folder-plus me-2"></i>Add Patient to Folder';
    }
    
    // Change button text
    const buttonText = document.getElementById('movePatientButtonText');
    if (buttonText) {
        buttonText.textContent = 'Add to Folder';
    }
    
    // Populate folder select
    const select = document.getElementById('movePatientFolderSelect');
    select.innerHTML = '<option value="">-- Select Folder --</option>';
    
    // Load all folders including sub-folders
    const allFolders = await loadAllFoldersForModal();
    
    allFolders.forEach(folder => {
        const option = document.createElement('option');
        option.value = folder.id;
        const displayName = folder.level === 1 
            ? `${folder.parentName} > ${folder.name}`
            : folder.name;
        option.textContent = displayName;
        select.appendChild(option);
    });
    
    modal.show();
}