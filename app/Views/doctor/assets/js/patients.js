
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
    nameFilter: '',
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
    
    // Render initial page
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
    
    // Initialize sort button states if sort is active
    if (paginationState.sortBy && paginationState.sortOrder) {
        const activeBtn = document.querySelector(`[data-sort="${paginationState.sortBy}"][data-order="${paginationState.sortOrder}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
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
}

// Apply doctor filter to patients
function applyDoctorFilter() {
    const { currentDoctorFilter, allPatients, nameFilter } = paginationState;
    
    let filtered = [...allPatients];
    
    // Apply doctor filter
    if (currentDoctorFilter !== 'all') {
        filtered = filtered.filter(patient => {
            return patient.created_by_doctor_id == currentDoctorFilter;
        });
    }
    
    // Apply name filter
    if (nameFilter) {
        filtered = filtered.filter(patient => {
            const firstName = (patient.first_name || '').toLowerCase();
            const lastName = (patient.last_name || '').toLowerCase();
            const fullName = `${firstName} ${lastName}`.trim();
            return fullName.includes(nameFilter) || 
                   firstName.includes(nameFilter) || 
                   lastName.includes(nameFilter);
        });
    }
    
    paginationState.filteredPatients = filtered;
    paginationState.totalItems = filtered.length;
    
    // Reset to first page
    paginationState.currentPage = 1;
}

// Filter patients by name
function filterByName(nameQuery) {
    paginationState.nameFilter = nameQuery.toLowerCase().trim();
    applyDoctorFilter();
    renderPatientsTable();
    updatePaginationInfo();
    renderPaginationNav();
    
    // Show/hide clear button
    const clearBtn = document.getElementById('clearNameFilter');
    if (clearBtn) {
        clearBtn.style.display = nameQuery.trim() ? 'block' : 'none';
    }
}

// Clear patient name filter
function clearPatientNameFilter() {
    const filterInput = document.getElementById('patientNameFilter');
    if (filterInput) {
        filterInput.value = '';
        filterByName('');
        filterInput.focus();
    }
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
    
    // Setup patient name filter
    const patientNameFilter = document.getElementById('patientNameFilter');
    if (patientNameFilter) {
        const debouncedNameFilter = debounce(function(value) {
            filterByName(value);
        }, 300);
        
        patientNameFilter.addEventListener('input', function() {
            debouncedNameFilter(this.value);
            // Show/hide clear button based on input value
            const clearBtn = document.getElementById('clearNameFilter');
            if (clearBtn) {
                clearBtn.style.display = this.value.trim() ? 'block' : 'none';
            }
        });
        
        // Clear filter on Escape key
        patientNameFilter.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                clearPatientNameFilter();
            }
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
        const isAddPatientKey = addPatientKeys.includes(e.key.toLowerCase()) || addPatientKeys.includes(e.key);
        const isCtrlN = (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'n';
        
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
            
            // Reapply name filter if exists
            const nameFilter = document.getElementById('patientNameFilter');
            if (nameFilter && nameFilter.value.trim()) {
                filterByName(nameFilter.value);
            } else {
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
    
    // Update statistics cards - find h3 elements in the statistics row
    const statsRow = document.querySelector('.row.mb-4');
    if (statsRow) {
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
            
            // Reapply name filter if exists
            const nameFilter = document.getElementById('patientNameFilter');
            if (nameFilter && nameFilter.value.trim()) {
                filterByName(nameFilter.value);
            } else {
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
            }
            
            // Update statistics
            updateStatistics(data.patients);
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
});

// Also initialize when modals are shown
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});