<?php
/**
 * Medications Prescriptions Gallery Page
 * Displays all medication prescriptions grouped by patient
 */
?>
<link href="/app/Views/doctor/assets/css/medications.css?v=<?= file_exists(__DIR__ . '/assets/css/medications.css') ? filemtime(__DIR__ . '/assets/css/medications.css') : time() ?>" rel="stylesheet">
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

    <!-- Medications Prescriptions Gallery Grid -->
    <div id="medicationsGallery" class="row g-4 mb-4">
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
        <i class="bi bi-capsule" style="font-size: 4rem; color: var(--muted);"></i>
        <h4 class="mt-3" style="color: var(--text);">No medication prescriptions found</h4>
        <p style="color: var(--muted);">No medication prescriptions are available at this time.</p>
    </div>
</div>

<!-- Prescription Preview Modal -->
<div class="modal fade" id="prescriptionPreviewModal" tabindex="-1" aria-labelledby="prescriptionPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="background: var(--card); border: 1px solid var(--border);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" id="prescriptionPreviewModalLabel" style="color: var(--text);">
                    <i class="bi bi-capsule me-2"></i>
                    Medication Prescriptions Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="prescriptionPreviewContent" style="color: var(--text);">
                <!-- Prescription preview will be loaded here -->
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border); background: var(--card);">
                <a href="#" id="viewPatientLink" class="btn btn-info" target="_blank">
                    <i class="bi bi-person-fill me-2"></i>
                    View Patient
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="color: var(--text);">Close</button>
            </div>
        </div>
    </div>
</div>

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
    document.getElementById('medicationsGallery').innerHTML = '';
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
    document.getElementById('medicationsGallery').innerHTML = '';
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
    let url = `/api/medications/prescriptions?page=${currentPage}`;
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
    const gallery = document.getElementById('medicationsGallery');
    
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
        icon.className = 'bi bi-capsule prescription-preview-icon';
        
        const info = document.createElement('div');
        info.className = 'prescription-preview-info';
        info.innerHTML = `
            <div style="font-weight: 600; margin-bottom: 0.25rem;">${patient.prescription_data ? patient.prescription_data.drug_name : 'No prescription data'}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">
                ${patient.prescription_data && patient.prescription_data.dose ? `Dose: ${patient.prescription_data.dose}` : ''}
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
        badge.innerHTML = `<i class="bi bi-calendar-check me-1"></i>${patient.appointment_count}`;
        
        const patientInfo = document.createElement('div');
        patientInfo.innerHTML = `
            <div class="prescription-patient-name">${patient.patient_name}</div>
            <div class="prescription-count">${patient.appointment_count} ${patient.appointment_count === 1 ? 'appointment' : 'appointments'}</div>
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
    
    document.getElementById('prescriptionPreviewModalLabel').textContent = `${patientName} - Medication Prescriptions`;
    
    // Load patient prescriptions
    fetch(`/api/medications/prescriptions/patient?patient_id=${patientId}`, {
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
                        <i class="bi bi-capsule text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No medication prescriptions found for this patient.</p>
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

function renderPrescriptionList(appointments, patientId) {
    const container = document.getElementById('prescriptionPreviewContent');
    
    // Sort appointments by date (newest first)
    appointments.sort((a, b) => {
        const dateA = new Date(a.appointment_date + ' ' + (a.appointment_time || '00:00:00'));
        const dateB = new Date(b.appointment_date + ' ' + (b.appointment_time || '00:00:00'));
        return dateB - dateA;
    });
    
    let html = '<div class="prescription-list accordion" id="prescriptionAccordion">';
    
    appointments.forEach((appointment, index) => {
        const isExpanded = index === 0; // First (newest) appointment is expanded
        const appointmentId = `appointment-${appointment.appointment_id}`;
        
        html += `
            <div class="appointment-group" data-appointment-id="${appointment.appointment_id}">
                <div class="appointment-header ${isExpanded ? 'expanded' : 'collapsed'}" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#${appointmentId}"
                     aria-expanded="${isExpanded}"
                     aria-controls="${appointmentId}"
                     onclick="toggleAppointment('${appointmentId}')">
                    <div class="appointment-info">
                        <div class="appointment-title">
                            <i class="bi bi-calendar-check me-2"></i>
                            Appointment #${appointment.appointment_id}
                        </div>
                        <div class="appointment-date">
                            ${formatDate(appointment.appointment_date)} ${appointment.appointment_time ? formatTime(appointment.appointment_time) : ''} | 
                            ${appointment.visit_type || 'N/A'} | 
                            ${appointment.prescription_count} ${appointment.prescription_count === 1 ? 'prescription' : 'prescriptions'}
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <a href="/doctor/appointments/${appointment.appointment_id}" 
                           class="btn btn-sm btn-success" 
                           target="_blank"
                           title="View Appointment"
                           onclick="event.stopPropagation(); event.preventDefault(); window.open(this.href, '_blank');">
                            <i class="bi bi-calendar-check me-1"></i>
                            View Appointment
                        </a>
                        <a href="/print/prescription/${appointment.appointment_id}" 
                           class="btn btn-sm btn-primary" 
                           target="_blank"
                           title="Print Prescription"
                           onclick="event.stopPropagation(); event.preventDefault(); window.open(this.href, '_blank');">
                            <i class="bi bi-printer me-1"></i>
                            Print Prescription
                        </a>
                    </div>
                    <i class="bi bi-chevron-down collapse-icon"></i>
                </div>
                <div id="${appointmentId}" 
                     class="appointment-body collapse ${isExpanded ? 'show' : ''}" 
                     data-bs-parent="#prescriptionAccordion">
        `;
        
        appointment.prescriptions.forEach(prescription => {
            html += `
                <div class="medication-item">
                    <div class="medication-header">
                        <div class="drug-name">${escapeHtml(prescription.drug_name)}</div>
                        <div class="rx-symbol">℞</div>
                    </div>
                    ${(prescription.dose || prescription.frequency || prescription.duration || prescription.route) ? `
                    <div class="medication-details">
                        ${prescription.dose ? `
                        <div class="detail-group">
                            <span class="detail-label">Dose:</span>
                            <span class="detail-value">${escapeHtml(prescription.dose)}</span>
                        </div>
                        ` : ''}
                        ${prescription.frequency ? `
                        <div class="detail-group">
                            <span class="detail-label">Frequency:</span>
                            <span class="detail-value">${escapeHtml(prescription.frequency)}</span>
                        </div>
                        ` : ''}
                        ${prescription.duration ? `
                        <div class="detail-group">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value">${escapeHtml(prescription.duration)}</span>
                        </div>
                        ` : ''}
                        ${prescription.route ? `
                        <div class="detail-group">
                            <span class="detail-label">Route:</span>
                            <span class="detail-value">${escapeHtml(prescription.route)}</span>
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}
                    ${prescription.notes ? `
                    <div class="notes-section">
                        <div class="notes-label">Special Instructions:</div>
                        <div class="notes-text">${escapeHtml(prescription.notes)}</div>
                    </div>
                    ` : ''}
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Update patient link
    document.getElementById('viewPatientLink').href = `/doctor/patients/${patientId}`;
}

function toggleAppointment(appointmentId) {
    // This function is called when clicking on appointment header
    // Bootstrap collapse handles the expand/collapse automatically
    // We just need to update the expanded class
    const header = document.querySelector(`[data-bs-target="#${appointmentId}"]`);
    const body = document.getElementById(appointmentId);
    
    if (body.classList.contains('show')) {
        header.classList.add('expanded');
        header.classList.remove('collapsed');
    } else {
        header.classList.remove('expanded');
        header.classList.add('collapsed');
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
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

