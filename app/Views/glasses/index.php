<?php
/**
 * Glasses Prescriptions Gallery Page
 * Displays all glasses prescriptions grouped by patient
 */
?>

<div class="container-fluid py-4">
    <!-- Patient Filter Section -->
    <div class="card mb-4" style="background: var(--card); border: 1px solid var(--border); position: relative; z-index: 100;">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8" style="position: relative; z-index: 10000;">
                    <label for="patientSearch" class="form-label" style="color: var(--text); font-weight: 600;">
                        <i class="bi bi-search me-2"></i>Filter by Patient Name
                    </label>
                    <input 
                        type="text" 
                        id="patientSearch" 
                        class="form-control" 
                        placeholder="Type patient name to search..."
                        autocomplete="off"
                        style="background: var(--card); border: 2px solid var(--border); color: var(--text); position: relative; z-index: 1;"
                    >
                    <div id="autocompleteResults" class="autocomplete-dropdown" style="z-index: 9999 !important;"></div>
                </div>
                <div class="col-md-4">
                    <div id="filterActiveSection" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary" id="selectedPatientBadge" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                <i class="bi bi-person-fill me-1"></i>
                                <span id="selectedPatientName"></span>
                            </span>
                            <button id="clearFilterBtn" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Clear Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Glasses Prescriptions Gallery Grid -->
    <div id="glassesGallery" class="row g-4 mb-4">
        <!-- Prescription cards will be loaded here -->
    </div>

    <!-- Load More Button with Horizontal Line -->
    <div class="mt-5 mb-4" id="loadMoreContainer">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1" style="height: 1px; background: var(--border);"></div>
            <div class="px-4">
                <button id="loadMoreBtn" class="btn btn-primary btn-lg" style="display: none;">
                    <i class="bi bi-arrow-down-circle me-2"></i>
                    Load More
                </button>
                <div id="loadingIndicator" class="spinner-border text-primary" role="status" style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="flex-grow-1" style="height: 1px; background: var(--border);"></div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-5" style="display: none;">
        <i class="bi bi-eyeglasses" style="font-size: 4rem; color: var(--muted);"></i>
        <h4 class="mt-3" style="color: var(--text);">No glasses prescriptions found</h4>
        <p style="color: var(--muted);">No glasses prescriptions are available at this time.</p>
    </div>
</div>

<!-- Prescription Preview Modal -->
<div class="modal fade" id="prescriptionPreviewModal" tabindex="-1" aria-labelledby="prescriptionPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="background: var(--card); border: 1px solid var(--border);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" id="prescriptionPreviewModalLabel" style="color: var(--text);">
                    <i class="bi bi-eyeglasses me-2"></i>
                    Glasses Prescription Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="prescriptionPreviewContent" style="color: var(--text);">
                <!-- Prescription preview will be loaded here -->
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border); background: var(--card);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="color: var(--text);">Close</button>
                <a href="#" id="viewAppointmentLink" class="btn btn-success" target="_blank" style="display: none;">
                    <i class="bi bi-calendar-check me-2"></i>
                    View Appointment
                </a>
                <a href="#" id="viewFullPrescriptionLink" class="btn btn-primary" target="_blank">
                    <i class="bi bi-printer me-2"></i>
                    Print Prescription
                </a>
                <a href="#" id="viewPatientLink" class="btn btn-info" target="_blank">
                    <i class="bi bi-person-fill me-2"></i>
                    View Patient
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Glasses Prescription Card Styles */
.prescription-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: var(--card);
    border: 2px solid var(--border);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    height: 100%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dark .prescription-card {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.prescription-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border-color: var(--accent);
}

.dark .prescription-card:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.prescription-thumbnail {
    width: 100%;
    height: 280px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.prescription-thumbnail::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"/><circle cx="30" cy="50" r="15" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"/><circle cx="70" cy="50" r="15" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"/></svg>') center/contain no-repeat;
    opacity: 0.3;
}

.prescription-preview {
    position: relative;
    z-index: 1;
    text-align: center;
    color: white;
    padding: 1rem;
}

.prescription-preview-icon {
    font-size: 4rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.prescription-preview-info {
    font-size: 0.9rem;
    opacity: 0.85;
}

.prescription-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.85) 100%);
    display: flex;
    align-items: flex-end;
    padding: 1rem;
    opacity: 1;
    transition: opacity 0.3s ease, background 0.3s ease;
    z-index: 1;
}

.dark .prescription-overlay {
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.9) 100%);
}

.prescription-card:hover .prescription-overlay {
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.95) 100%);
}

.dark .prescription-card:hover .prescription-overlay {
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.98) 100%);
}

.prescription-count-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.85);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    z-index: 2;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .prescription-count-badge {
    background: rgba(0, 0, 0, 0.9);
    border-color: rgba(255, 255, 255, 0.2);
}

.prescription-patient-name {
    color: white;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.prescription-count {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin-top: 0.25rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.prescription-date {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.85rem;
    margin-top: 0.25rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

/* Prescription Preview Modal Styles */
#prescriptionPreviewModal .modal-content {
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
}

#prescriptionPreviewModal .modal-header {
    background: var(--card);
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

#prescriptionPreviewModal .modal-footer {
    background: var(--card);
    border-top: 1px solid var(--border);
}

#prescriptionPreviewContent {
    font-family: 'Cairo', 'Arial', sans-serif;
    background: var(--card);
    padding: 2rem;
    border-radius: 8px;
    color: var(--text);
}

.prescription-preview-header {
    text-align: center;
    margin-bottom: 1.5rem;
    border-bottom: 3px solid var(--border);
    padding-bottom: 1rem;
}

.prescription-preview-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--text);
    margin-bottom: 0.5rem;
}

.dark .prescription-preview-title {
    color: var(--text);
}

.prescription-preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.prescription-preview-eye {
    border: 2px solid #3498db;
    padding: 1rem;
    border-radius: 8px;
    background: var(--bg);
}

.dark .prescription-preview-eye {
    background: var(--card);
    border-color: var(--accent);
}

.prescription-preview-eye-title {
    font-weight: bold;
    color: #3498db;
    margin-bottom: 0.75rem;
    text-align: center;
}

.dark .prescription-preview-eye-title {
    color: var(--accent);
}

.prescription-preview-measurement {
    display: flex;
    justify-content: space-between;
    margin: 0.5rem 0;
    padding: 0.25rem 0;
    border-bottom: 1px solid var(--border);
}

.prescription-preview-label {
    font-weight: 600;
    color: var(--muted);
}

.dark .prescription-preview-label {
    color: var(--muted);
}

.prescription-preview-value {
    font-weight: bold;
    color: var(--text);
}

.dark .prescription-preview-value {
    color: var(--text);
}

/* Patient Search Autocomplete Styles */
#patientSearch {
    position: relative;
    z-index: 1;
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card);
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 9999 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: none;
    margin-top: -1px;
}

.dark .autocomplete-dropdown {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 9999 !important;
}

.autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background-color 0.2s ease;
    color: var(--text);
}

.autocomplete-item:hover {
    background: var(--bg);
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item.active {
    background: var(--accent);
    color: white;
}

.autocomplete-item .patient-name {
    font-weight: 600;
    font-size: 0.95rem;
}

.autocomplete-item .patient-info {
    font-size: 0.85rem;
    color: var(--muted);
    margin-top: 0.25rem;
}

.autocomplete-item.active .patient-info {
    color: rgba(255, 255, 255, 0.8);
}

#filterActiveSection {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Dark Mode Support */
.dark .container-fluid h2 {
    color: var(--text) !important;
}

.dark .container-fluid p {
    color: var(--muted) !important;
}

.dark #loadMoreBtn {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
}

.dark #loadMoreBtn:hover {
    background: var(--success);
    border-color: var(--success);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
}

.dark #loadingIndicator {
    color: var(--accent) !important;
}

.dark #emptyState h4 {
    color: var(--text) !important;
}

.dark #emptyState p {
    color: var(--muted) !important;
}

.dark #emptyState .bi-eyeglasses {
    color: var(--muted) !important;
}

/* Dark Mode for Prescription Preview Modal */
.dark .prescription-preview-header {
    border-bottom-color: var(--border);
}

.dark .prescription-preview-header div {
    color: var(--muted);
}

.dark .prescription-pd-box {
    background: rgba(243, 156, 18, 0.1) !important;
    border-color: #f39c12 !important;
}

.dark .prescription-pd-box strong,
.dark .prescription-pd-box span {
    color: #f39c12 !important;
}

.dark .prescription-comments-box {
    background: rgba(155, 89, 182, 0.1) !important;
    border-color: #9b59b6 !important;
}

.dark .prescription-comments-box strong,
.dark .prescription-comments-box div {
    color: #9b59b6 !important;
}

.dark .prescription-preview-measurement {
    border-bottom-color: var(--border);
}

.dark #prescriptionPreviewModal .btn-secondary {
    background: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.dark #prescriptionPreviewModal .btn-secondary:hover {
    background: var(--muted);
    color: white;
}
</style>

<script>
let currentPage = 1;
let isLoading = false;
let hasMore = true;
let currentPatientPrescriptions = [];
let currentPatientId = null;
let selectedPatientId = null;
let selectedPatientName = null;
let searchTimeout = null;
let autocompleteItems = [];
let selectedAutocompleteIndex = -1;

// Load initial prescriptions
document.addEventListener('DOMContentLoaded', function() {
    loadPrescriptions();
    
    // Load more button
    document.getElementById('loadMoreBtn').addEventListener('click', function() {
        if (!isLoading && hasMore) {
            currentPage++;
            loadPrescriptions();
        }
    });
    
    // Patient search autocomplete
    setupPatientSearch();
    
    // Clear filter button
    document.getElementById('clearFilterBtn').addEventListener('click', function() {
        clearFilter();
    });
});

function setupPatientSearch() {
    const searchInput = document.getElementById('patientSearch');
    const autocompleteResults = document.getElementById('autocompleteResults');
    
    if (!searchInput || !autocompleteResults) {
        console.error('Patient search elements not found');
        return;
    }
    
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideAutocomplete();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchPatients(query);
        }, 300);
    });
    
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            navigateAutocomplete(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            navigateAutocomplete(-1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            selectAutocompleteItem();
        } else if (e.key === 'Escape') {
            hideAutocomplete();
        }
    });
    
    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
            hideAutocomplete();
        }
    });
}

function searchPatients(query) {
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}&limit=10`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if ((data.ok || data.success) && data.data && data.data.length > 0) {
                displayAutocomplete(data.data);
            } else {
                hideAutocomplete();
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
            hideAutocomplete();
        });
}

function displayAutocomplete(patients) {
    const autocompleteResults = document.getElementById('autocompleteResults');
    autocompleteItems = patients;
    selectedAutocompleteIndex = -1;
    
    autocompleteResults.innerHTML = '';
    
    patients.forEach((patient, index) => {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.dataset.index = index;
        item.dataset.patientId = patient.id;
        
        const name = `${patient.first_name} ${patient.last_name}`;
        item.innerHTML = `
            <div class="patient-name">${name}</div>
            <div class="patient-info">
                ${patient.phone ? `📞 ${patient.phone}` : ''}
                ${patient.national_id ? ` | 🆔 ${patient.national_id}` : ''}
            </div>
        `;
        
        item.addEventListener('click', function() {
            selectPatient(patient.id, name);
        });
        
        item.addEventListener('mouseenter', function() {
            selectedAutocompleteIndex = index;
            updateAutocompleteSelection();
        });
        
        autocompleteResults.appendChild(item);
    });
    
    autocompleteResults.style.display = 'block';
}

function navigateAutocomplete(direction) {
    if (autocompleteItems.length === 0) return;
    
    selectedAutocompleteIndex += direction;
    
    if (selectedAutocompleteIndex < 0) {
        selectedAutocompleteIndex = autocompleteItems.length - 1;
    } else if (selectedAutocompleteIndex >= autocompleteItems.length) {
        selectedAutocompleteIndex = 0;
    }
    
    updateAutocompleteSelection();
}

function updateAutocompleteSelection() {
    const items = document.querySelectorAll('.autocomplete-item');
    items.forEach((item, index) => {
        if (index === selectedAutocompleteIndex) {
            item.classList.add('active');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('active');
        }
    });
}

function selectAutocompleteItem() {
    if (selectedAutocompleteIndex >= 0 && selectedAutocompleteIndex < autocompleteItems.length) {
        const patient = autocompleteItems[selectedAutocompleteIndex];
        const name = `${patient.first_name} ${patient.last_name}`;
        selectPatient(patient.id, name);
    }
}

function selectPatient(patientId, patientName) {
    selectedPatientId = patientId;
    selectedPatientName = patientName;
    
    // Update UI
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('selectedPatientName').textContent = patientName;
    document.getElementById('filterActiveSection').style.display = 'block';
    
    hideAutocomplete();
    
    // Reset and reload prescriptions
    currentPage = 1;
    document.getElementById('glassesGallery').innerHTML = '';
    loadPrescriptions();
}

function clearFilter() {
    selectedPatientId = null;
    selectedPatientName = null;
    
    // Update UI
    document.getElementById('patientSearch').value = '';
    document.getElementById('filterActiveSection').style.display = 'none';
    hideAutocomplete();
    
    // Reset and reload prescriptions
    currentPage = 1;
    document.getElementById('glassesGallery').innerHTML = '';
    loadPrescriptions();
}

function hideAutocomplete() {
    document.getElementById('autocompleteResults').style.display = 'none';
    selectedAutocompleteIndex = -1;
}

function loadPrescriptions() {
    if (isLoading) return;
    
    isLoading = true;
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('loadMoreBtn').style.display = 'none';
    
    // Add patient filter to URL if selected
    let url = `/api/glasses/prescriptions?page=${currentPage}`;
    if (selectedPatientId) {
        url += `&patient_id=${selectedPatientId}`;
    }
    
    fetch(url, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
            
            if (data.success && data.data.length > 0) {
                renderPrescriptionCards(data.data);
                
                // Update pagination
                hasMore = data.pagination.has_more;
                if (hasMore) {
                    document.getElementById('loadMoreBtn').style.display = 'block';
                }
                
                // Hide empty state
                document.getElementById('emptyState').style.display = 'none';
            } else {
                if (currentPage === 1) {
                    document.getElementById('emptyState').style.display = 'block';
                }
                hasMore = false;
            }
        })
        .catch(error => {
            console.error('Error loading prescriptions:', error);
            isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
        });
}

function renderPrescriptionCards(patients) {
    const gallery = document.getElementById('glassesGallery');
    
    patients.forEach(patient => {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-lg-3 col-sm-6';
        
        const card = document.createElement('div');
        card.className = 'prescription-card';
        card.onclick = () => openPrescriptionView(patient.patient_id, patient.patient_name);
        
        // Thumbnail section
        const thumbnail = document.createElement('div');
        thumbnail.className = 'prescription-thumbnail';
        
        const preview = document.createElement('div');
        preview.className = 'prescription-preview';
        
        const icon = document.createElement('i');
        icon.className = 'bi bi-eyeglasses prescription-preview-icon';
        
        const info = document.createElement('div');
        info.className = 'prescription-preview-info';
        info.innerHTML = `
            <div style="font-weight: 600; margin-bottom: 0.25rem;">${patient.latest_lens_type || 'Single Vision'}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">
                ${patient.prescription_data ? `
                    OD: ${patient.prescription_data.distance_sphere_r || '0.00'} | 
                    OS: ${patient.prescription_data.distance_sphere_l || '0.00'}
                ` : 'No prescription data'}
            </div>
        `;
        
        preview.appendChild(icon);
        preview.appendChild(info);
        thumbnail.appendChild(preview);
        
        // Overlay
        const overlay = document.createElement('div');
        overlay.className = 'prescription-overlay';
        
        const badge = document.createElement('div');
        badge.className = 'prescription-count-badge';
        badge.innerHTML = `<i class="bi bi-file-earmark-text me-1"></i>${patient.prescription_count}`;
        
        const patientInfo = document.createElement('div');
        patientInfo.innerHTML = `
            <div class="prescription-patient-name">${patient.patient_name}</div>
            <div class="prescription-count">${patient.prescription_count} ${patient.prescription_count === 1 ? 'prescription' : 'prescriptions'}</div>
            <div class="prescription-date">Last: ${formatDate(patient.last_prescription_date)}</div>
        `;
        
        overlay.appendChild(patientInfo);
        card.appendChild(thumbnail);
        card.appendChild(badge);
        card.appendChild(overlay);
        col.appendChild(card);
        gallery.appendChild(col);
    });
}

function openPrescriptionView(patientId, patientName) {
    currentPatientId = patientId;
    
    // Show loading
    const modal = new bootstrap.Modal(document.getElementById('prescriptionPreviewModal'));
    modal.show();
    
    document.getElementById('prescriptionPreviewModalLabel').textContent = `${patientName} - Glasses Prescriptions`;
    
    // Load patient prescriptions
    fetch(`/api/glasses/prescriptions/patient?patient_id=${patientId}`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                currentPatientPrescriptions = data.data;
                renderPrescriptionList(data.data, patientId);
            } else {
                document.getElementById('prescriptionPreviewContent').innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-eyeglasses text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No glasses prescriptions found for this patient.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading patient prescriptions:', error);
            document.getElementById('prescriptionPreviewContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading prescriptions. Please try again.
                </div>
            `;
        });
}

function renderPrescriptionList(prescriptions, patientId) {
    const container = document.getElementById('prescriptionPreviewContent');
    
    let html = '<div class="prescription-list">';
    
    prescriptions.forEach((prescription, index) => {
        const isActive = index === 0;
        html += `
            <div class="prescription-item mb-4 ${isActive ? 'active' : ''}" data-prescription-id="${prescription.id}">
                <div class="prescription-preview-header">
                    <div class="prescription-preview-title">
                        Prescription #${prescription.id} - ${prescription.lens_type || 'Single Vision'}
                    </div>
                    <div style="font-size: 0.9rem; color: #666;">
                        Date: ${formatDate(prescription.created_at)} | 
                        Appointment: ${formatDate(prescription.appointment_date)} ${prescription.appointment_time ? formatTime(prescription.appointment_time) : ''}
                    </div>
                </div>
                
                <div class="prescription-preview-grid">
                    <div class="prescription-preview-eye">
                        <div class="prescription-preview-eye-title">Right Eye (OD)</div>
                        <div class="prescription-preview-measurement">
                            <span class="prescription-preview-label">Sphere:</span>
                            <span class="prescription-preview-value">${prescription.distance_sphere_r || '0.00'} D</span>
                        </div>
                        <div class="prescription-preview-measurement">
                            <span class="prescription-preview-label">Cylinder:</span>
                            <span class="prescription-preview-value">${prescription.distance_cylinder_r || '0.00'} D</span>
                        </div>
                        <div class="prescription-preview-measurement">
                            <span class="prescription-preview-label">Axis:</span>
                            <span class="prescription-preview-value">${prescription.distance_axis_r || '0'}°</span>
                        </div>
                        ${prescription.near_sphere_r ? `
                        <div class="prescription-preview-measurement" style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border);">
                            <span class="prescription-preview-label">Near Sphere:</span>
                            <span class="prescription-preview-value">${prescription.near_sphere_r} D</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="prescription-preview-eye">
                        <div class="prescription-preview-eye-title">Left Eye (OS)</div>
                        <div class="prescription-preview-measurement">
                            <span class="prescription-preview-label">Sphere:</span>
                            <span class="prescription-preview-value">${prescription.distance_sphere_l || '0.00'} D</span>
                        </div>
                        <div class="prescription-preview-measurement">
                            <span class="prescription-preview-label">Cylinder:</span>
                            <span class="prescription-preview-value">${prescription.distance_cylinder_l || '0.00'} D</span>
                        </div>
                        <div class="prescription-preview-measurement">
                            <span class="prescription-preview-label">Axis:</span>
                            <span class="prescription-preview-value">${prescription.distance_axis_l || '0'}°</span>
                        </div>
                        ${prescription.near_sphere_l ? `
                        <div class="prescription-preview-measurement" style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border);">
                            <span class="prescription-preview-label">Near Sphere:</span>
                            <span class="prescription-preview-value">${prescription.near_sphere_l} D</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                ${(prescription.PD_DISTANCE || prescription.PD_NEAR) ? `
                <div class="prescription-pd-box" style="margin-top: 1rem; padding: 0.75rem; background: #fffbf0; border: 2px solid #f39c12; border-radius: 8px; text-align: center;">
                    <strong style="color: #f39c12;">Pupillary Distance:</strong>
                    ${prescription.PD_DISTANCE ? `<span style="margin: 0 1rem;">Distance: ${prescription.PD_DISTANCE}mm</span>` : ''}
                    ${prescription.PD_NEAR ? `<span>Near: ${prescription.PD_NEAR}mm</span>` : ''}
                </div>
                ` : ''}
                
                ${prescription.comments ? `
                <div class="prescription-comments-box" style="margin-top: 1rem; padding: 0.75rem; background: #f8f4fd; border: 2px solid #9b59b6; border-radius: 8px;">
                    <strong style="color: #9b59b6;">Comments:</strong>
                    <div style="color: #9b59b6; margin-top: 0.5rem;">${escapeHtml(prescription.comments)}</div>
                </div>
                ` : ''}
                
                <hr style="margin: 1.5rem 0; border-color: var(--border);">
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Update links for the first prescription
    if (prescriptions.length > 0) {
        const firstPrescription = prescriptions[0];
        document.getElementById('viewFullPrescriptionLink').href = `/print/glasses/${firstPrescription.id}`;
        document.getElementById('viewPatientLink').href = `/doctor/patients/${patientId}`;
        
        // Show appointment link if appointment_id exists
        if (firstPrescription.appointment_id) {
            document.getElementById('viewAppointmentLink').href = `/doctor/appointments/${firstPrescription.appointment_id}`;
            document.getElementById('viewAppointmentLink').style.display = 'inline-block';
        } else {
            document.getElementById('viewAppointmentLink').style.display = 'none';
        }
    }
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    if (!timeString) return '';
    return timeString.substring(0, 5);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

