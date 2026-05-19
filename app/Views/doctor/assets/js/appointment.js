
function editConsultation(appointmentId) {
    // Redirect to edit consultation page
    window.location.href = `/doctor/appointments/${appointmentId}/edit`;
}

function addPrescription(appointmentId) {
    // Show prescription modal
    showPrescriptionModal(appointmentId);
}

function printReport(appointmentId) {
    // Open print view
    window.open(`/print/appointment/${appointmentId}`, '_blank');
}

function rescheduleAppointment(appointmentId) {
    // Show reschedule modal
    showRescheduleModal(appointmentId);
}

function addConsultationNotes(appointmentId) {
    // Redirect to edit consultation page (where notes can be added/edited)
    window.location.href = `/doctor/appointments/${appointmentId}/edit`;
}

function markCompleted(appointmentId) {
    // Show confirmation modal instead of simple confirm
    showCompletionConfirmModal(appointmentId);
}

function confirmMarkCompleted(appointmentId) {
    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('completionConfirmModal'));
    modal.hide();
    
    // Show loading state
    const button = document.querySelector(`button[onclick="markCompleted(${appointmentId})"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
    button.disabled = true;
    
    // API call to update status
    fetch(`/api/appointments/${appointmentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            status: 'Completed'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.ok) {
            // Update UI to show completed status using new functions
            updateStatusBadge('Completed');
            
            // Hide the complete button
            button.style.display = 'none';
            
            // Show success message
            showNotification('Appointment marked as completed successfully!', 'success');
        } else {
            throw new Error(data.error || data.message || 'Error updating appointment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating appointment: ' + error.message, 'error');
        
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function scheduleFollowUp(appointmentId) {
    // Show follow-up scheduling modal
    alert('Schedule follow-up functionality will be implemented soon');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function viewPatient(patientId) {
    // Redirect to patient profile
    window.location.href = `/doctor/patients/${patientId}`;
}

function printPrescription(appointmentId) {
    // Open prescription print view
    window.open(`/print/prescription/${appointmentId}`, '_blank');
}

function printGlassesPrescription(appointmentId) {
    // Open glasses prescription print view
    window.open(`/print/glasses/${appointmentId}`, '_blank');
}

function showPrescriptionModal(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="prescriptionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Prescription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="prescriptionForm" action="/api/prescriptions/meds" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            
                            <!-- Most Used Drugs Suggestions -->
                            <div class="mb-4">
                                <label class="form-label">Most Used Drugs</label>
                                <div id="mostUsedDrugs" class="d-flex flex-wrap gap-2">
                                    <div class="text-muted">
                                        <i class="bi bi-hourglass-split me-1"></i>Loading suggestions...
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Drug Name <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control" name="drug_name" id="drugNameInput" required autocomplete="off">
                                        <div id="drugSuggestions" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto;"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Dose</label>
                                    <input type="text" class="form-control" name="dose" placeholder="e.g., 1 tablet, 2 drops">
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Frequency</label>
                                    <input type="text" class="form-control" name="frequency" placeholder="e.g., Twice daily, Every 6 hours">
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" name="duration" placeholder="e.g., 7 days, 2 weeks">
                                </div>
                                <div class="col-12 mb-3" style="display: none;">
                                    <label class="form-label">Route</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-control d-none" name="route">
                                                <option value="Topical" selected>Topical</option>
                                                <option value="Oral">Oral</option>
                                                <option value="IV">IV</option>
                                                <option value="IM">IM</option>
                                                <option value="Sublingual">Sublingual</option>
                                                <option value="Inhalation">Inhalation</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false">Topical</button>
                                            <menu>
                                                <li data-option="Topical" tabindex="0" role="button" class="selected"><i class="bi-arrow-right-circle fs-5"></i><h3>Topical</h3></li>
                                                <li data-option="Oral" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Oral</h3></li>
                                                <li data-option="IV" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>IV</h3></li>
                                                <li data-option="IM" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>IM</h3></li>
                                                <li data-option="Sublingual" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Sublingual</h3></li>
                                                <li data-option="Inhalation" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Inhalation</h3></li>
                                                <li data-option="Other" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Other</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('prescriptionModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    // Handle form submission
    document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/prescriptions/meds', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Prescription added successfully');
                setTimeout(() => {
                    reloadMedications();
                }, 300);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Load most used drugs suggestions
    loadMostUsedDrugs();
    
    // Setup autocomplete for drug name input
    setupDrugNameAutocomplete();
    
    // Clean up modal on hide
    document.getElementById('prescriptionModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Show prescription suggestions modal based on diagnosis
async function showPrescriptionSuggestions(appointmentId, diagnosis, complaint) {
    const modalHtml = `
        <div class="modal fade" id="prescriptionSuggestionsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-lightbulb me-2"></i>Prescription Suggestions
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <p class="mb-1"><strong>Diagnosis:</strong> <span id="suggestionDiagnosis">${escapeHtml(diagnosis)}</span></p>
                            ${complaint ? `<p class="mb-0 text-muted"><small><strong>Complaint:</strong> ${escapeHtml(complaint)}</small></p>` : ''}
                        </div>
                        <div id="prescriptionSuggestionsLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading suggestions...</p>
                        </div>
                        <div id="prescriptionSuggestionsContent" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Select prescriptions to add:</span>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllPrescriptions()">Select All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllPrescriptions()">Deselect All</button>
                                </div>
                            </div>
                            <div id="prescriptionSuggestionsList" class="list-group"></div>
                            <div id="prescriptionSuggestionsEmpty" class="alert alert-info mt-3" style="display: none;">
                                <i class="bi bi-info-circle me-2"></i>No prescription suggestions found for this diagnosis.
                            </div>
                        </div>
                        <div id="prescriptionSuggestionsError" class="alert alert-danger" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span id="suggestionsErrorMessage"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="addSelectedPrescriptionsBtn" onclick="addSelectedPrescriptions(${appointmentId})" disabled>
                            <i class="bi bi-plus-circle me-1"></i>Add Selected (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('prescriptionSuggestionsModal'));
    modal.show();
    
    // Store selected prescriptions
    window.selectedPrescriptions = [];
    
    // Fetch suggestions
    try {
        const params = new URLSearchParams({
            diagnosis: diagnosis,
            complaint: complaint || ''
        });
        const response = await fetch(`/api/prescriptions/suggestions?${params}`);
        const data = await response.json();
        
        document.getElementById('prescriptionSuggestionsLoading').style.display = 'none';
        
        if (data.ok && data.data && data.data.length > 0) {
            displayPrescriptionSuggestions(data.data);
            document.getElementById('prescriptionSuggestionsContent').style.display = 'block';
        } else {
            document.getElementById('prescriptionSuggestionsEmpty').style.display = 'block';
            document.getElementById('prescriptionSuggestionsContent').style.display = 'block';
        }
    } catch (error) {
        document.getElementById('prescriptionSuggestionsLoading').style.display = 'none';
        document.getElementById('prescriptionSuggestionsError').style.display = 'block';
        document.getElementById('suggestionsErrorMessage').textContent = 'Failed to load suggestions: ' + error.message;
    }
    
    // Clean up modal on hide
    document.getElementById('prescriptionSuggestionsModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
        window.selectedPrescriptions = [];
    });
}

// Display prescription suggestions in the modal
function displayPrescriptionSuggestions(suggestions) {
    const container = document.getElementById('prescriptionSuggestionsList');
    container.innerHTML = '';
    
    suggestions.forEach((prescription, index) => {
        const item = document.createElement('div');
        item.className = 'list-group-item prescription-suggestion-item';
        item.innerHTML = `
            <div class="form-check d-flex align-items-start">
                <input class="form-check-input prescription-checkbox" type="checkbox" 
                       value="${index}" 
                       id="prescription_${index}"
                       data-drug-name="${escapeHtml(prescription.drug_name)}"
                       data-notes="${escapeHtml(prescription.notes || '')}"
                       data-route="${escapeHtml(prescription.route || 'Topical')}"
                       onchange="updateSelectedPrescriptions()">
                <label class="form-check-label flex-grow-1 ms-2" for="prescription_${index}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-capsule me-1"></i>${escapeHtml(prescription.drug_name)}
                            </h6>
                            ${prescription.notes ? `<p class="mb-1 text-muted small">${escapeHtml(prescription.notes)}</p>` : ''}
                            <small class="text-muted">
                                <i class="bi bi-arrow-right-circle me-1"></i>Route: ${escapeHtml(prescription.route)}
                            </small>
                        </div>
                        <span class="badge bg-primary ms-2">Used ${prescription.count} ${prescription.count === 1 ? 'time' : 'times'}</span>
                    </div>
                </label>
            </div>
        `;
        container.appendChild(item);
    });
}

// Update selected prescriptions array
function updateSelectedPrescriptions() {
    const checkboxes = document.querySelectorAll('.prescription-checkbox:checked');
    window.selectedPrescriptions = Array.from(checkboxes).map(cb => ({
        drug_name: cb.getAttribute('data-drug-name'),
        notes: cb.getAttribute('data-notes'),
        route: cb.getAttribute('data-route')
    }));
    
    const count = window.selectedPrescriptions.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('addSelectedPrescriptionsBtn').disabled = count === 0;
}

// Select all prescriptions
function selectAllPrescriptions() {
    document.querySelectorAll('.prescription-checkbox').forEach(cb => {
        cb.checked = true;
    });
    updateSelectedPrescriptions();
}

// Deselect all prescriptions
function deselectAllPrescriptions() {
    document.querySelectorAll('.prescription-checkbox').forEach(cb => {
        cb.checked = false;
    });
    updateSelectedPrescriptions();
}

// Add selected prescriptions to appointment
async function addSelectedPrescriptions(appointmentId) {
    if (!window.selectedPrescriptions || window.selectedPrescriptions.length === 0) {
        showErrorMessage('No prescriptions selected');
        return;
    }
    
    const addBtn = document.getElementById('addSelectedPrescriptionsBtn');
    const originalText = addBtn.innerHTML;
    addBtn.disabled = true;
    addBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Adding...';
    
    let successCount = 0;
    let errorCount = 0;
    
    // Add each prescription sequentially
    for (const prescription of window.selectedPrescriptions) {
        try {
            const formData = new FormData();
            formData.append('appointment_id', appointmentId);
            formData.append('drug_name', prescription.drug_name);
            formData.append('notes', prescription.notes || '');
            formData.append('route', prescription.route || 'Topical');
            
            const response = await fetch('/api/prescriptions/meds', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                successCount++;
            } else {
                errorCount++;
                console.error('Failed to add prescription:', prescription.drug_name, data.message);
            }
        } catch (error) {
            errorCount++;
            console.error('Error adding prescription:', prescription.drug_name, error);
        }
    }
    
    // Show result message
    if (successCount > 0) {
        showSuccessMessage(`Successfully added ${successCount} prescription${successCount > 1 ? 's' : ''}`);
    }
    if (errorCount > 0) {
        showErrorMessage(`Failed to add ${errorCount} prescription${errorCount > 1 ? 's' : ''}`);
    }
    
    // Close modal and reload medications
    const modal = bootstrap.Modal.getInstance(document.getElementById('prescriptionSuggestionsModal'));
    modal.hide();
    
    setTimeout(() => {
        reloadMedications();
    }, 300);
}

// Load most used drugs and display as clickable badges
async function loadMostUsedDrugs(containerId = 'mostUsedDrugs', targetInputId = 'drugNameInput') {
    try {
        const response = await fetch('/api/getMostUsedDrugs?limit=10');
        const data = await response.json();
        
        const container = document.getElementById(containerId);
        if (!container) return; // Silent return if container doesn't exist
        
        if (data.drugs && data.drugs.length > 0) {
            container.innerHTML = '';
            
            // Render all drugs with prices directly from API response
            data.drugs.forEach(drug => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary me-2 mb-2 drug-suggestion-badge';
                badge.style.cursor = 'pointer';
                badge.setAttribute('data-drug-name', drug.drug_name);
                
                // Check for special case: Fluca Ed
                let displayPrice = drug.price;
                if (drug.drug_name && drug.drug_name.toLowerCase().includes('fluca ed')) {
                    displayPrice = '66';
                }
                
                // Show price circle - always show, use "unknown" if no price
                const priceValue = displayPrice ? `EGP ${displayPrice}` : 'unknown';
                const priceCircle = `<span class="drug-price-circle me-1" title="${priceValue}">${priceValue}</span>`;
                
                badge.innerHTML = `
                    <i class="bi bi-capsule me-1"></i>
                    ${drug.drug_name}
                    ${priceCircle}
                    <span class="usage-count-badge">${drug.usage_count}</span>
                    <span class="drug-info-btn ms-2" title="View details">
                        <i class="bi bi-exclamation"></i>
                    </span>
                `;
                
                const priceText = displayPrice ? `. Price: EGP ${displayPrice}` : '. Price: unknown';
                badge.title = `Used ${drug.usage_count} times. Common doses: ${drug.common_doses || 'N/A'}. Common frequencies: ${drug.common_frequencies || 'N/A'}${priceText}`;
                
                badge.addEventListener('click', (e) => {
                    // Check if info button was clicked (bubble up)
                    const infoBtn = e.target.closest('.drug-info-btn');
                    if (infoBtn) {
                        e.stopPropagation();
                        // Pass the event object e so showDrugPopoverFromName can call preventDefault()
                        if (typeof showDrugPopoverFromName === 'function') {
                            showDrugPopoverFromName(drug.drug_name, e, 'modal');
                        }
                        return;
                    }

                    const targetInput = document.getElementById(targetInputId);
                    if (targetInput) {
                        targetInput.value = drug.drug_name;
                        // Trigger input event if needed
                        targetInput.dispatchEvent(new Event('input'));
                    }
                    
                    // Hide suggestions when drug is selected (only if using main input)
                    if (targetInputId === 'drugNameInput') {
                        const suggestions = document.getElementById('drugSuggestions');
                        if (suggestions) suggestions.style.display = 'none';
                    }
                });
                
                container.appendChild(badge);
            });
        } else {
            container.innerHTML = '<div class="text-muted"><i class="bi bi-info-circle me-1"></i>No drug usage data available</div>';
        }
    } catch (error) {
        console.error('Error loading most used drugs:', error);
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '<div class="text-muted"><i class="bi bi-exclamation-triangle me-1"></i>Failed to load suggestions</div>';
        }
    }
}

// Setup autocomplete functionality for drug name input
function setupDrugNameAutocomplete() {
    const drugNameInput = document.getElementById('drugNameInput');
    const suggestionsContainer = document.getElementById('drugSuggestions');
    let searchTimeout;
    
    drugNameInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (searchTerm.length < 3) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            searchDrugsAutocomplete(searchTerm);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!drugNameInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    // Show suggestions when input is focused and has content
    drugNameInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 3) {
            searchDrugsAutocomplete(this.value.trim());
        }
    });
}
    
// Search drugs for autocomplete
    async function searchDrugsAutocomplete(searchTerm) {
        try {
        const response = await fetch(`/api/searchDrugsAutocomplete?q=${encodeURIComponent(searchTerm)}&limit=6`);
            const data = await response.json();
        
        const suggestionsContainer = document.getElementById('drugSuggestions');
            
            if (data.drugs && data.drugs.length > 0) {
            suggestionsContainer.innerHTML = '';
            
            data.drugs.forEach(drug => {
                const suggestionItem = document.createElement('div');
                suggestionItem.className = 'p-1 border-bottom suggestion-item';
                suggestionItem.style.cursor = 'pointer';
                const priceBadge = drug.price ? `<span class="badge bg-success text-white ms-2" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">EGP ${drug.price}</span>` : '';
                suggestionItem.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                    <div class="fw-bold text-primary" style="font-size: 0.8rem;">${drug.drug_name}</div>
                    <small class="text-muted" style="font-size: 0.7rem;">${drug.active_ingredient || ''} ${drug.Company ? '- ' + drug.Company : ''}</small>
                        </div>
                        ${priceBadge}
                    </div>
                `;
                
                suggestionItem.addEventListener('click', () => {
                    document.getElementById('drugNameInput').value = drug.drug_name;
                    suggestionsContainer.style.display = 'none';
                });
                
                suggestionItem.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#f8f9fa';
                    });
                    
                suggestionItem.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '';
                    });
                    
                suggestionsContainer.appendChild(suggestionItem);
                });
                
            suggestionsContainer.style.display = 'block';
            } else {
            suggestionsContainer.innerHTML = '<div class="p-2 text-muted text-center">No drugs found</div>';
            suggestionsContainer.style.display = 'block';
            }
        } catch (error) {
            console.error('Error searching drugs:', error);
        const suggestionsContainer = document.getElementById('drugSuggestions');
        suggestionsContainer.innerHTML = '<div class="p-2 text-danger text-center">Error loading suggestions</div>';
        suggestionsContainer.style.display = 'block';
    }
}

// Format time for display (HH:mm to 12-hour format) - Global function
function formatTimeForReschedule(timeStr) {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

// Validate date selection (same as calendar.php) - Global function
function validateDateSelection(dateString) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedDate = new Date(dateString + 'T00:00:00');
    
    if (selectedDate < today) {
        return {
            valid: false,
            message: 'Cannot select a date before today. Please select today or a future date.'
        };
    }
    return { valid: true };
}

function showRescheduleModal(appointmentId) {
    // Get current appointment data
    const currentDate = window.APPOINTMENT_CONFIG.appointmentDate;
    const currentTime = window.APPOINTMENT_CONFIG.appointmentTime;
    const currentStatus = window.APPOINTMENT_CONFIG.appointmentStatus;
    const patientName = window.APPOINTMENT_CONFIG.patientName;
    
    // Check if appointment is completed
    if (currentStatus === 'Completed') {
        showErrorMessage('Cannot reschedule a completed appointment');
        return;
    }
    
    const modalHtml = `
        <div class="modal fade" id="rescheduleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-plus me-2"></i>Reschedule Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="rescheduleForm">
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Patient:</strong> ${patientName}<br>
                                <strong>Current Appointment:</strong> ${currentDate} at ${currentTime.substring(0, 5)}
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="new_date" id="newDateInput" required 
                                       min="${new Date().toISOString().split('T')[0]}">
                                <div class="text-muted" style="color: var(--text-muted);">Must be a future date</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Time <span class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" name="new_time" id="newTimeInput" required>
                                            <option value="">Select available time slot...</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">Select available time slot...</button>
                                        <menu>
                                            <li data-option="" tabindex="0" role="button" class="selected"><i class="bi-clock fs-5"></i><h3>Select available time slot...</h3></li>
                                        </menu>
                                    </div>
                                </section>
                                <div class="text-muted" style="color: var(--text-muted);">Only available time slots from calendar are shown</div>
                                <div id="timeSlotsLoading" class="text-muted mt-2" style="display: none;">
                                    <i class="bi bi-hourglass-split me-1"></i>Loading available time slots...
                                </div>
                                <div id="timeSlotsError" class="alert alert-warning mt-2" style="display: none;"></div>
                            </div>
                            <div id="rescheduleError" class="alert alert-danger" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning" id="rescheduleSubmitBtn">
                                <i class="bi bi-calendar-check me-1"></i>Reschedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    const newDateInput = document.getElementById('newDateInput');
    const newTimeInput = document.getElementById('newTimeInput');
    const errorDiv = document.getElementById('rescheduleError');
    const submitBtn = document.getElementById('rescheduleSubmitBtn');
    const timeSlotsLoading = document.getElementById('timeSlotsLoading');
    const timeSlotsError = document.getElementById('timeSlotsError');
    const doctorId = window.APPOINTMENT_CONFIG.doctorId;
    
    // Set minimum date to tomorrow if current date is today
    const today = new Date();
    const appointmentDate = new Date(currentDate);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (appointmentDate.toDateString() === today.toDateString()) {
        // If appointment is today, new date must be tomorrow or later
        newDateInput.min = tomorrow.toISOString().split('T')[0];
    } else {
        // If appointment is in the future, new date must be after appointment date
        const minDate = new Date(appointmentDate);
        minDate.setDate(minDate.getDate() + 1);
        newDateInput.min = minDate.toISOString().split('T')[0];
    }
    
    // Load available time slots from calendar
    function loadAvailableTimeSlotsForReschedule(selectedDate) {
        if (!selectedDate || !doctorId) {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
            const timeField = newTimeInput.closest('.field.menu');
            const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
            if (timeButton) {
                timeButton.textContent = 'Please select a date first';
            }
            return;
        }
        
        // Validate selected date
        const validation = validateDateSelection(selectedDate);
        if (!validation.valid) {
            timeSlotsError.textContent = validation.message;
            timeSlotsError.style.display = 'block';
            newTimeInput.innerHTML = '<option value="">Invalid date</option>';
            const timeField = newTimeInput.closest('.field.menu');
            const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
            if (timeButton) {
                timeButton.textContent = 'Invalid date';
            }
            return;
        }
        
        timeSlotsLoading.style.display = 'block';
        timeSlotsError.style.display = 'none';
        newTimeInput.disabled = true;
        newTimeInput.innerHTML = '<option value="">Loading...</option>';
        const timeField = newTimeInput.closest('.field.menu');
        const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
        if (timeButton) {
            timeButton.textContent = 'Loading...';
        }
        
        // Fetch available slots from calendar API
        fetch(`/api/calendar?doctor_id=${doctorId}&date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                
                if (data.ok && data.data && data.data.available_slots) {
                    const availableSlots = data.data.available_slots;
                    
                    if (availableSlots.length === 0) {
                        newTimeInput.innerHTML = '<option value="">No available time slots for this date</option>';
                        const timeField = newTimeInput.closest('.field.menu');
                        const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
                        if (timeButton) {
                            timeButton.textContent = 'No available time slots for this date';
                        }
                        timeSlotsError.textContent = 'No available time slots found for the selected date. Please choose another date.';
                        timeSlotsError.style.display = 'block';
                        return;
                    }
                    
                    // Filter slots that are later than current appointment if same date
                    const currentDateTime = new Date(currentDate + 'T' + currentTime);
                    let filteredSlots = availableSlots;
                    
                    if (selectedDate === currentDate) {
                        filteredSlots = availableSlots.filter(slot => {
                            const slotDateTime = new Date(selectedDate + 'T' + slot);
                            return slotDateTime > currentDateTime;
                        });
                    }
                    
                    if (filteredSlots.length === 0) {
                        newTimeInput.innerHTML = '<option value="">No later time slots available for this date</option>';
                        const timeField = newTimeInput.closest('.field.menu');
                        const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
                        if (timeButton) {
                            timeButton.textContent = 'No later time slots available for this date';
                        }
                        timeSlotsError.textContent = 'No later time slots available for the selected date. Please choose another date.';
                        timeSlotsError.style.display = 'block';
                        return;
                    }
                    
                    // Populate time slots using populateTimeSlots function (like calendar.js)
                    populateTimeSlots(newTimeInput, filteredSlots);
                    
                    timeSlotsError.style.display = 'none';
                } else {
                    newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                    const timeField = newTimeInput.closest('.field.menu');
                    const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
                    if (timeButton) {
                        timeButton.textContent = 'Error loading time slots';
                    }
                    timeSlotsError.textContent = 'Failed to load available time slots. Please try again.';
                    timeSlotsError.style.display = 'block';
                }
            })
            .catch(error => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                const timeField = newTimeInput.closest('.field.menu');
                const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
                if (timeButton) {
                    timeButton.textContent = 'Error loading time slots';
                }
                timeSlotsError.textContent = 'Error loading available time slots: ' + error.message;
                timeSlotsError.style.display = 'block';
                console.error('Error loading time slots:', error);
            });
    }
    
    // Format time for display (HH:mm to 12-hour format)
    function formatTimeForReschedule(timeStr) {
        if (!timeStr) return '';
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }
    
    // Load time slots when date changes
    newDateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            loadAvailableTimeSlotsForReschedule(selectedDate);
            // Clear time selection when date changes
            newTimeInput.value = '';
        } else {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
        }
        validateRescheduleForm();
    });
    
    // Validation function
    function validateRescheduleForm() {
        const newDate = newDateInput.value;
        const newTime = newTimeInput.value;
        
        if (!newDate || !newTime) {
            errorDiv.style.display = 'none';
            submitBtn.disabled = false;
            return;
        }
        
        // Normalize time format (ensure HH:MM format)
        const normalizeTime = (timeStr) => {
            if (!timeStr) return '';
            // Remove seconds if present (HH:MM:SS -> HH:MM)
            return timeStr.substring(0, 5);
        };
        
        const normalizedCurrentTime = normalizeTime(currentTime);
        const normalizedNewTime = normalizeTime(newTime);
        
        try {
            const currentDateTime = new Date(currentDate + 'T' + normalizedCurrentTime + ':00');
            const newDateTime = new Date(newDate + 'T' + normalizedNewTime + ':00');
            const now = new Date();
            
            // Check if dates are valid
            if (isNaN(currentDateTime.getTime()) || isNaN(newDateTime.getTime())) {
                errorDiv.style.display = 'none';
                submitBtn.disabled = false;
                return;
            }
            
            // Check if new date/time is in the future
            if (newDateTime <= now) {
                errorDiv.textContent = 'New appointment date and time must be in the future';
                errorDiv.style.display = 'block';
                submitBtn.disabled = true;
                return;
            }
            
            // Check if new date/time is later than current appointment
            if (newDateTime <= currentDateTime) {
                errorDiv.textContent = 'New appointment date and time must be later than the current appointment';
                errorDiv.style.display = 'block';
                submitBtn.disabled = true;
                return;
            }
        } catch (error) {
            console.error('Date validation error:', error);
            errorDiv.style.display = 'none';
            submitBtn.disabled = false;
            return;
        }
        
        errorDiv.style.display = 'none';
        submitBtn.disabled = false;
    }
    
    // Real-time validation when time changes
    newTimeInput.addEventListener('change', function() {
        validateRescheduleForm();
    });
    
    // Load time slots for initial date (if date is pre-filled)
    if (newDateInput.value) {
        loadAvailableTimeSlotsForReschedule(newDateInput.value);
    }
    
    // Handle form submission
    document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate before submission
        const newDate = newDateInput.value;
        const newTime = newTimeInput.value;
        
        if (!newDate || !newTime) {
            errorDiv.textContent = 'Please fill in all required fields';
            errorDiv.style.display = 'block';
            return;
        }
        
        // Normalize time format (ensure HH:MM format)
        const normalizeTime = (timeStr) => {
            if (!timeStr) return '';
            // Remove seconds if present (HH:MM:SS -> HH:MM)
            return timeStr.substring(0, 5);
        };
        
        const normalizedCurrentTime = normalizeTime(currentTime);
        const normalizedNewTime = normalizeTime(newTime);
        
        // Validate date/time format before creating Date objects
        try {
            const currentDateTime = new Date(currentDate + 'T' + normalizedCurrentTime + ':00');
            const newDateTime = new Date(newDate + 'T' + normalizedNewTime + ':00');
            const now = new Date();
            
            // Check if dates are valid
            if (isNaN(currentDateTime.getTime())) {
                throw new Error('Invalid current appointment date/time');
            }
            if (isNaN(newDateTime.getTime())) {
                throw new Error('Invalid new appointment date/time');
            }
        
            // Check if new date/time is in the future
            if (newDateTime <= now) {
                errorDiv.textContent = 'New appointment date and time must be in the future';
                errorDiv.style.display = 'block';
                return;
            }
            
            // Check if new date/time is later than current appointment
            if (newDateTime <= currentDateTime) {
                errorDiv.textContent = 'New appointment date and time must be later than the current appointment';
                errorDiv.style.display = 'block';
                return;
            }
        } catch (error) {
            console.error('Date validation error:', error);
            errorDiv.textContent = 'Invalid date or time format. Please check your input.';
            errorDiv.style.display = 'block';
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Rescheduling...';
        errorDiv.style.display = 'none';
        
        // Simple form data - just new_date and new_time
        const params = new URLSearchParams();
        params.append('new_date', newDate);
        params.append('new_time', newTime);
        
        fetch('/api/appointments/' + appointmentId + '/reschedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString(),
            credentials: 'same-origin'
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            let responseData;
            
            if (contentType && contentType.includes('application/json')) {
                try {
                    responseData = await response.json();
                } catch (e) {
                    console.error('Failed to parse JSON response:', e);
                    const text = await response.text();
                    console.error('Response text:', text);
                    throw new Error('Invalid JSON response from server');
                }
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            }
            
            if (!response.ok) {
                throw new Error(responseData.error || responseData.message || `HTTP ${response.status}`);
            }
            
            return responseData;
        })
        .then(data => {
            if (data.ok || data.success) {
                modal.hide();
                
                // Show toast notification with rescheduled info
                const formattedDate = data.data?.formatted_date || newDate;
                const formattedTime = data.data?.formatted_time || formatTimeForReschedule(newTime);
                const toastMessage = `Appointment rescheduled to ${formattedDate} at ${formattedTime}`;
                
                showRescheduleToast(toastMessage, formattedDate, formattedTime);
                
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                throw new Error(data.error || data.message || 'Failed to reschedule appointment');
            }
        })
        .catch(error => {
            console.error('Reschedule error:', error);
            errorDiv.textContent = error.message || 'Error rescheduling appointment. Please try again.';
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Clean up modal on hide
    document.getElementById('rescheduleModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function rescheduleFollowupAppointment(appointmentId) {
    // Show reschedule followup modal
    showRescheduleFollowupModal(appointmentId);
}

function showRescheduleFollowupModal(appointmentId) {
    // Get current appointment data
    const currentDate = window.APPOINTMENT_CONFIG.appointmentDate;
    const currentTime = window.APPOINTMENT_CONFIG.appointmentTime;
    const currentStatus = window.APPOINTMENT_CONFIG.appointmentStatus;
    const patientName = window.APPOINTMENT_CONFIG.patientName;
    
    // Note: rescheduleFollowup can be done even for completed appointments
    
    const modalHtml = `
        <div class="modal fade" id="rescheduleFollowupModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-check me-2"></i>Reschedule Follow-up Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="rescheduleFollowupForm">
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Patient:</strong> ${patientName}<br>
                                <strong>Current Appointment:</strong> ${currentDate} at ${currentTime.substring(0, 5)}
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="new_date" id="newDateInputFollowup" required 
                                       min="${new Date().toISOString().split('T')[0]}">
                                <div class="text-muted" style="color: var(--text-muted);">Must be a future date</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Time <span class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" name="new_time" id="newTimeInputFollowup" required>
                                            <option value="">Select available time slot...</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">Select available time slot...</button>
                                        <menu>
                                            <li data-option="" tabindex="0" role="button" class="selected"><i class="bi-clock fs-5"></i><h3>Select available time slot...</h3></li>
                                        </menu>
                                    </div>
                                </section>
                                <div class="text-muted" style="color: var(--text-muted);">Only available time slots from calendar are shown</div>
                                <div id="timeSlotsLoadingFollowup" class="text-muted mt-2" style="display: none;">
                                    <i class="bi bi-hourglass-split me-1"></i>Loading available time slots...
                                </div>
                                <div id="timeSlotsErrorFollowup" class="alert alert-warning mt-2" style="display: none;"></div>
                            </div>
                            <div id="rescheduleFollowupError" class="alert alert-danger" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning" id="rescheduleFollowupSubmitBtn">
                                <i class="bi bi-calendar-check me-1"></i>Schedule Follow-up
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('rescheduleFollowupModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    const newDateInput = document.getElementById('newDateInputFollowup');
    const newTimeInput = document.getElementById('newTimeInputFollowup');
    const errorDiv = document.getElementById('rescheduleFollowupError');
    const submitBtn = document.getElementById('rescheduleFollowupSubmitBtn');
    const timeSlotsLoading = document.getElementById('timeSlotsLoadingFollowup');
    const timeSlotsError = document.getElementById('timeSlotsErrorFollowup');
    const doctorId = window.APPOINTMENT_CONFIG.doctorId;
    
    // Set minimum date to tomorrow
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    newDateInput.min = tomorrow.toISOString().split('T')[0];
    
    // Load available time slots from calendar
    function loadAvailableTimeSlotsForFollowup(selectedDate) {
        if (!selectedDate || !doctorId) {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
            updateTimeSelectCustomMenu(newTimeInput, 'Please select a date first');
            return;
        }
        
        const validation = validateDateSelection(selectedDate);
        if (!validation.valid) {
            timeSlotsError.textContent = validation.message;
            timeSlotsError.style.display = 'block';
            newTimeInput.innerHTML = '<option value="">Invalid date</option>';
            updateTimeSelectCustomMenu(newTimeInput, 'Invalid date');
            return;
        }
        
        timeSlotsLoading.style.display = 'block';
        timeSlotsError.style.display = 'none';
        newTimeInput.disabled = true;
        newTimeInput.innerHTML = '<option value="">Loading...</option>';
        updateTimeSelectCustomMenu(newTimeInput, 'Loading...');
        
        fetch(`/api/calendar?doctor_id=${doctorId}&date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                
                if (data.ok && data.data && data.data.available_slots) {
                    const availableSlots = data.data.available_slots;
                    
                    if (availableSlots.length === 0) {
                        newTimeInput.innerHTML = '<option value="">No available time slots for this date</option>';
                        updateTimeSelectCustomMenu(newTimeInput, 'No available time slots for this date');
                        timeSlotsError.textContent = 'No available time slots found for the selected date. Please choose another date.';
                        timeSlotsError.style.display = 'block';
                        return;
                    }
                    
                    // Populate time slots using populateTimeSlots function (like calendar.js)
                    populateTimeSlots(newTimeInput, availableSlots);
                    
                    timeSlotsError.style.display = 'none';
                } else {
                    newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                    updateTimeSelectCustomMenu(newTimeInput, 'Error loading time slots');
                    timeSlotsError.textContent = 'Failed to load available time slots. Please try again.';
                    timeSlotsError.style.display = 'block';
                }
            })
            .catch(error => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                timeSlotsError.textContent = 'Error loading available time slots: ' + error.message;
                timeSlotsError.style.display = 'block';
                console.error('Error loading time slots:', error);
            });
    }
    
    // Load time slots when date changes
    newDateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            loadAvailableTimeSlotsForFollowup(selectedDate);
            newTimeInput.value = '';
        } else {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
        }
    });
    
    // Handle form submission
    document.getElementById('rescheduleFollowupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newDate = newDateInput.value;
        const newTime = newTimeInput.value;
        
        if (!newDate || !newTime) {
            errorDiv.textContent = 'Please fill in all required fields';
            errorDiv.style.display = 'block';
            return;
        }
        
        // Normalize time format (ensure HH:MM format)
        const normalizeTime = (timeStr) => {
            if (!timeStr) return '';
            // Remove seconds if present (HH:MM:SS -> HH:MM)
            return timeStr.substring(0, 5);
        };
        
        const normalizedNewTime = normalizeTime(newTime);
        
        try {
            const newDateTime = new Date(newDate + 'T' + normalizedNewTime + ':00');
            const now = new Date();
            
            // Check if date is valid
            if (isNaN(newDateTime.getTime())) {
                throw new Error('Invalid date/time format');
            }
            
            if (newDateTime <= now) {
                errorDiv.textContent = 'New appointment date and time must be in the future';
                errorDiv.style.display = 'block';
                return;
            }
        } catch (error) {
            console.error('Date validation error:', error);
            errorDiv.textContent = 'Invalid date or time format. Please check your input.';
            errorDiv.style.display = 'block';
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Scheduling...';
        errorDiv.style.display = 'none';
        
        // Simple form data - just new_date and new_time
        const params = new URLSearchParams();
        params.append('new_date', newDate);
        params.append('new_time', newTime);
        
        fetch('/api/appointments/' + appointmentId + '/reschedule-followup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString(),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.error || err.message || `HTTP ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.ok || data.success) {
                modal.hide();
                showSuccessMessage('Follow-up appointment scheduled successfully');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                throw new Error(data.error || data.message || 'Failed to schedule follow-up appointment');
            }
        })
        .catch(error => {
            console.error('RescheduleFollowup error:', error);
            errorDiv.textContent = error.message || 'Error scheduling follow-up appointment. Please try again.';
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Clean up modal on hide
    document.getElementById('rescheduleFollowupModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Camera Functions
function openCameraModal(appointmentId, patientId) {
    const modalHtml = `
        <div class="modal fade" id="cameraModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-camera me-2"></i>Take Photo
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="cameraAppointmentId" value="${appointmentId}">
                        <input type="hidden" id="cameraPatientId" value="${patientId}">
                        
                        <div class="mb-3" id="cameraAttachmentTypeContainer">
                            <label class="form-label">Photo Type</label>
                            <section class="field menu" style="min-width: 100%;">
                                <div class="control">
                                    <select class="form-select d-none" id="cameraAttachmentType" required>
                                        <option value="photo" selected>Photo</option>
                                        <option value="xray">X-ray</option>
                                        <option value="ct_scan">CT Scan</option>
                                        <option value="mri">MRI</option>
                                        <option value="ultrasound">Ultrasound</option>
                                        <option value="eye_photo">Eye Photo</option>
                                        <option value="retina_photo">Retina Photo</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <button type="button" class="custom-select-toggle" aria-expanded="false">Photo</button>
                                    <menu>
                                        <li data-option="photo" tabindex="0" role="button" class="selected"><i class="bi-camera fs-5"></i><h3>Photo</h3></li>
                                        <li data-option="xray" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>X-ray</h3></li>
                                        <li data-option="ct_scan" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>CT Scan</h3></li>
                                        <li data-option="mri" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>MRI</h3></li>
                                        <li data-option="ultrasound" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Ultrasound</h3></li>
                                        <li data-option="eye_photo" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Eye Photo</h3></li>
                                        <li data-option="retina_photo" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Retina Photo</h3></li>
                                        <li data-option="other" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Other</h3></li>
                                    </menu>
                                </div>
                            </section>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Photo Description</label>
                            <textarea class="form-control" id="cameraDescription" rows="2" 
                                      placeholder="Add a description for the photo (optional)"></textarea>
                        </div>
                        
                        <!-- Camera View -->
                        <div class="text-center mb-3">
                            <div id="cameraContainer" class="border rounded p-3" style="background: #f8f9fa; min-height: 300px;">
                                <video id="cameraVideo" width="100%" height="300" style="max-width: 100%; border-radius: 8px; display: none;" autoplay playsinline></video>
                                <canvas id="cameraCanvas" width="640" height="480" style="max-width: 100%; border-radius: 8px; display: none;"></canvas>
                                <div id="cameraPlaceholder" class="d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 300px;">
                                    <i class="bi bi-camera text-muted" style="font-size: 4rem;"></i>
                                    <p class="text-muted mt-2">Loading camera...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Camera Controls -->
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-success" id="capturePhotoBtn" onclick="capturePhoto()">
                                <i class="bi bi-camera me-2"></i>Take Photo
                            </button>
                            <button type="button" class="btn btn-warning" id="retakePhotoBtn" onclick="retakePhoto()" style="display: none;">
                                <i class="bi bi-arrow-clockwise me-2"></i>Retake
                            </button>
                            <button type="button" class="btn btn-danger" id="stopCameraBtn" onclick="stopCamera()">
                                <i class="bi bi-stop-circle me-2"></i>Stop Camera
                            </button>
                        </div>
                        
                        <div id="cameraProgress" class="mb-3" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">Uploading photo...</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="savePhotoBtn" onclick="savePhoto()" style="display: none;">
                            <i class="bi bi-check-lg me-2"></i>Save Photo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    // Start camera automatically when modal is shown
    document.getElementById('cameraModal').addEventListener('shown.bs.modal', function() {
        startCamera();
    });
    
    // Clean up modal and stop camera on hide
    document.getElementById('cameraModal').addEventListener('hidden.bs.modal', function() {
        stopCamera();
        this.remove();
    });
}

let cameraStream = null;
let capturedImageData = null;

function startCamera() {
    const video = document.getElementById('cameraVideo');
    const placeholder = document.getElementById('cameraPlaceholder');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const attachmentTypeContainer = document.getElementById('cameraAttachmentTypeContainer');
    
    // Check if camera is supported
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showErrorMessage('Camera is not supported in this browser');
        return;
    }
    
    navigator.mediaDevices.getUserMedia({ 
                            video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'environment' // Use back camera on mobile
        } 
    })
        .then(function(stream) {
            cameraStream = stream;
            video.srcObject = stream;
            
            // Show video, hide placeholder
            placeholder.style.display = 'none';
            video.style.display = 'block';
            
            // Hide photo type field when camera starts
            if (attachmentTypeContainer) {
                attachmentTypeContainer.style.display = 'none';
            }
            
            // Update buttons
        captureBtn.style.display = 'inline-block';
        stopBtn.style.display = 'inline-block';
            
            showSuccessMessage('Camera started successfully');
        })
        .catch(function(error) {
        console.error('Error accessing camera:', error);
        showErrorMessage('Error accessing camera: ' + error.message);
        });
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const retakeBtn = document.getElementById('retakePhotoBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    
    // Set canvas size to match video
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    // Draw the video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert canvas to blob
    canvas.toBlob(function(blob) {
        capturedImageData = blob;
        
        // Hide video, show canvas
        video.style.display = 'none';
        canvas.style.display = 'block';
        
        // Update buttons
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
        saveBtn.style.display = 'inline-block';
        
        showSuccessMessage('Photo captured! You can now save it or retake.');
    }, 'image/jpeg', 0.8);
}

function retakePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const retakeBtn = document.getElementById('retakePhotoBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    
    // Clear captured image
    capturedImageData = null;
    
    // Show video, hide canvas
    canvas.style.display = 'none';
    video.style.display = 'block';
    
    // Update buttons
    retakeBtn.style.display = 'none';
    saveBtn.style.display = 'none';
    captureBtn.style.display = 'inline-block';
}

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const placeholder = document.getElementById('cameraPlaceholder');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const retakeBtn = document.getElementById('retakePhotoBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    const attachmentTypeContainer = document.getElementById('cameraAttachmentTypeContainer');
    
    if (video) {
        video.style.display = 'none';
        video.srcObject = null;
    }
    
    if (canvas) {
        canvas.style.display = 'none';
    }
    
    if (placeholder) {
        placeholder.style.display = 'flex';
    }
    
    // Show photo type field when camera stops
    if (attachmentTypeContainer) {
        attachmentTypeContainer.style.display = 'block';
    }
    
    // Reset buttons
    if (captureBtn) captureBtn.style.display = 'inline-block';
    if (retakeBtn) retakeBtn.style.display = 'none';
    if (stopBtn) stopBtn.style.display = 'inline-block';
    if (saveBtn) saveBtn.style.display = 'none';
    
    // Clear captured image
    capturedImageData = null;
}

function savePhoto() {
    if (!capturedImageData) {
        showErrorMessage('No photo captured');
        return;
    }
    
    const appointmentId = document.getElementById('cameraAppointmentId').value;
    const patientId = document.getElementById('cameraPatientId').value;
    const attachmentType = document.getElementById('cameraAttachmentType').value;
    const description = document.getElementById('cameraDescription').value;
    
    if (!attachmentType) {
        showErrorMessage('Please select a photo type');
        return;
    }
    
    const formData = new FormData();
    formData.append('appointment_id', appointmentId);
    formData.append('patient_id', patientId);
    formData.append('attachment_type', attachmentType);
    formData.append('description', description);
    formData.append('attachment_file', capturedImageData, 'camera_photo_' + Date.now() + '.jpg');
    
    const saveBtn = document.getElementById('savePhotoBtn');
    const progressDiv = document.getElementById('cameraProgress');
    const progressBar = progressDiv.querySelector('.progress-bar');
    
    // Show progress
    saveBtn.disabled = true;
    progressDiv.style.display = 'block';
    
    // Create XMLHttpRequest for progress tracking
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        saveBtn.disabled = false;
        progressDiv.style.display = 'none';
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('cameraModal'));
                    modal.hide();
                    showSuccessMessage('Photo saved successfully');
                    // Wait a bit for modal to close, then reload attachments via Ajax
                    setTimeout(() => {
                        reloadAttachments();
                    }, 300);
                } else {
                    showErrorMessage('Error: ' + (response.message || 'Save failed'));
                }
            } catch (parseError) {
                console.error('Response parsing error:', parseError);
                console.error('Raw response:', xhr.responseText);
                showErrorMessage('Server response error');
            }
        } else {
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            showErrorMessage('HTTP Error ' + xhr.status + ': ' + xhr.statusText);
        }
    });
    
    xhr.addEventListener('error', function() {
        showErrorMessage('Error: ' + xhr.statusText);
        saveBtn.disabled = false;
        progressDiv.style.display = 'none';
    });
    
    xhr.open('POST', '/api/attachments/upload');
    xhr.withCredentials = true;
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
}

// Medical Attachments Functions
function showUploadModal(appointmentId, patientId) {
    const modalHtml = `
        <div class="modal fade" id="uploadModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload New Attachment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            <input type="hidden" name="patient_id" value="${patientId}">
                            
                            <div class="mb-3">
                                <label class="form-label">Attachment Type</label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" name="attachment_type" required>
                                            <option value="photo" selected>Photo</option>
                                            <option value="xray">X-ray</option>
                                            <option value="ct_scan">CT Scan</option>
                                            <option value="mri">MRI</option>
                                            <option value="ultrasound">Ultrasound</option>
                                            <option value="lab_report">Lab Report</option>
                                            <option value="blood_test">Blood Test</option>
                                            <option value="report">Report</option>
                                            <option value="prescription">Prescription</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">Photo</button>
                                        <menu>
                                            <li data-option="photo" tabindex="0" role="button" class="selected"><i class="bi-paperclip fs-5"></i><h3>Photo</h3></li>
                                            <li data-option="xray" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>X-ray</h3></li>
                                            <li data-option="ct_scan" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>CT Scan</h3></li>
                                            <li data-option="mri" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>MRI</h3></li>
                                            <li data-option="ultrasound" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Ultrasound</h3></li>
                                            <li data-option="lab_report" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Lab Report</h3></li>
                                            <li data-option="blood_test" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Blood Test</h3></li>
                                            <li data-option="report" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Report</h3></li>
                                            <li data-option="prescription" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Prescription</h3></li>
                                            <li data-option="other" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Other</h3></li>
                                        </menu>
                                    </div>
                                </section>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">File</label>
                                <input type="file" class="form-control" name="attachment_file" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt" required>
                                <div class="form-text">
                                    Supported Files: Images (JPG, PNG, GIF), PDF, Word Documents, Text Files
                                    <br>Maximum File Size: 2 MB
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Attachment Description</label>
                                <textarea class="form-control" name="description" rows="3" 
                                          placeholder="Add a description for the attachment (optional)"></textarea>
                            </div>
                            
                            <div id="uploadProgress" class="mb-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Uploading...</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="uploadBtn">
                                <i class="bi bi-cloud-upload me-2"></i>Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    // Handle file selection
    const fileInput = document.querySelector('#uploadModal input[type="file"]');
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size is too large. Maximum 2 MB.');
                this.value = '';
                return;
            }
            
            // Show file info
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
        }
    });
    
    // Handle form submission
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = document.getElementById('uploadBtn');
        const progressDiv = document.getElementById('uploadProgress');
        const progressBar = progressDiv.querySelector('.progress-bar');
        
        // Show progress
        uploadBtn.disabled = true;
        progressDiv.style.display = 'block';
        
        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
                progressBar.textContent = Math.round(percentComplete) + '%';
            }
        });
        
        xhr.addEventListener('load', function() {
            uploadBtn.disabled = false;
            progressDiv.style.display = 'none';
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        modal.hide();
                        showSuccessMessage('Attachment uploaded successfully');
                        // Wait a bit for modal to close, then reload attachments via Ajax
                        setTimeout(() => {
                            reloadAttachments();
                        }, 300);
                    } else {
                        showErrorMessage('Error: ' + (response.message || 'Upload failed'));
                    }
                } catch (parseError) {
                    console.error('Response parsing error:', parseError);
                    console.error('Raw response:', xhr.responseText);
                    showErrorMessage('Server response error. Please check if the API endpoint exists.');
                }
            } else {
                console.error('HTTP Error:', xhr.status, xhr.statusText);
                showErrorMessage('HTTP Error ' + xhr.status + ': ' + xhr.statusText);
            }
        });
        
        xhr.addEventListener('error', function() {
            showErrorMessage('Error: ' + xhr.statusText);
            uploadBtn.disabled = false;
            progressDiv.style.display = 'none';
        });
        
        xhr.open('POST', '/api/attachments/upload');
        xhr.withCredentials = true;
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });
    
    // Clean up modal on hide
    document.getElementById('uploadModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

/**
 * Open the drawing studio with an existing image attachment pre-loaded.
 * The modal is in "edit existing" mode — its footer shows two save
 * options (replace the original / save as a new attachment).
 */
function editAttachmentDrawing(attachmentId, imageUrl, filename) {
    if (typeof DrawConsultation === 'undefined' ||
        typeof DrawConsultation.openForAppointment !== 'function') {
        alert('Drawing tool is not loaded yet — please retry in a moment.');
        return;
    }
    const cfg = window.APPOINTMENT_CONFIG || {};
    const appointmentId = cfg.appointmentId;
    const patientId     = cfg.patientId;
    if (!appointmentId || !patientId) {
        alert('Cannot open the drawing editor: missing appointment context.');
        return;
    }
    DrawConsultation.openForAppointment(appointmentId, patientId, {
        editAttachment: {
            id: attachmentId,
            url: imageUrl,
            filename: filename || 'attachment.png',
        }
    });
}

function viewAttachment(attachmentId, filePath, fileExt) {
    const viewUrl = `/api/attachments/view/${attachmentId}`;
    
    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt.toLowerCase())) {
        // Show image in modal
        showImageModal(viewUrl, attachmentId);
    } else if (fileExt.toLowerCase() === 'pdf') {
        // Open PDF in new tab
        window.open(viewUrl, '_blank');
    } else {
        // Download other file types
        downloadAttachment(attachmentId);
    }
}

function showImageModal(imageUrl, attachmentId, filename) {
    filename = filename || 'View Image';
    const safeFilenameJs = String(filename).replace(/'/g, "\\'");
    const modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content image-modal-glass">
                    <div class="modal-header image-modal-header">
                        <h5 class="modal-title"><i class="bi bi-image me-2"></i>${escapeHtml(filename)}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center image-modal-body">
                        <img src="${imageUrl}" class="img-fluid" style="max-height: 80vh; border-radius: 8px;" alt="Medical Image">
                    </div>
                    <div class="modal-footer image-modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <!-- Edit + Delete added to the preview modal so the doctor
                             doesn't have to close it and hunt for the card's
                             overlay buttons. Edit closes this modal first so
                             the drawing modal isn't stacked on top of it. -->
                        <button type="button" class="btn btn-danger"
                                onclick="(function(){ bootstrap.Modal.getInstance(document.getElementById('imageModal'))?.hide(); deleteAttachment(${attachmentId}); })();">
                            <i class="bi bi-trash me-2"></i>Delete
                        </button>
                        <button type="button" class="btn btn-info text-white"
                                onclick="(function(){ bootstrap.Modal.getInstance(document.getElementById('imageModal'))?.hide(); editAttachmentDrawing(${attachmentId}, '${String(imageUrl).replace(/'/g, "\\'")}', '${safeFilenameJs}'); })();">
                            <i class="bi bi-pencil-square me-2"></i>Edit
                        </button>
                        <button type="button" class="btn btn-primary" onclick="downloadAttachment(${attachmentId}, '${safeFilenameJs}')">
                            <i class="bi bi-download me-2"></i>Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();

    // Clean up modal on hide
    document.getElementById('imageModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function downloadAttachment(attachmentId, filename) {
    const downloadUrl = `/api/attachments/download/${attachmentId}`;
    
    // Create temporary link and click it
    const link = document.createElement('a');
    link.href = downloadUrl;
    if (filename) {
        link.download = filename;
    }
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function deleteAttachment(attachmentId) {
    showDeleteConfirmModal(
        'Delete Attachment',
        'Are you sure you want to delete this attachment?',
        'This action cannot be undone.',
        () => {
            fetch(`/api/attachments/${attachmentId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Attachment deleted successfully');
                    // Remove attachment card from DOM
                    const attachmentCard = document.querySelector(`[data-attachment-id="${attachmentId}"]`);
                    if (attachmentCard) {
                        attachmentCard.closest('.col-md-6').remove();
                        // Check if no attachments left
                        const attachmentsRow = document.getElementById('attachmentsRow');
                        if (attachmentsRow && attachmentsRow.children.length === 0) {
                            attachmentsRow.remove();
                            const emptyMsg = document.getElementById('emptyAttachmentsMessage');
                            if (!emptyMsg) {
                                const container = document.getElementById('attachmentsContainer');
                                container.innerHTML = `
                                    <div class="text-center py-4" id="emptyAttachmentsMessage">
                                            <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">No images or attachments found</p>
                                        </div>
                                    `;
                                }
                            }
                    }
                } else {
                    showErrorMessage('Error: ' + data.message);
                }
            })
            .catch(error => {
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

// Utility functions for notifications
function showSuccessMessage(message) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="bi bi-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-success');
        if (alert) {
            alert.remove();
        }
    }, 3000);
}

function showErrorMessage(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-danger');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Delete Confirmation Modal
function showDeleteConfirmModal(title, message, warning, onConfirm) {
    const modalHtml = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>${title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">${message}</p>
                        <p class="text-muted mb-0"><small><i class="bi bi-info-circle me-1"></i>${warning}</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="bi bi-trash me-2"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
    
    // Handle confirm button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        modal.hide();
        onConfirm();
    });
    
    // Clean up modal on hide
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Delete Consultation Note Function
function deleteConsultationNote(noteId, noteTitle) {
    const modalHtml = `
        <div class="modal fade" id="deleteConsultationNoteModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Delete Consultation Notes
                            <i class="bi bi-exclamation-triangle me-2"></i>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="bi bi-trash-fill text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h6 class="mb-3">Are you sure you want to delete this consultation note?</h6>
                        <p class="text-muted mb-3">
                            <strong>Consultation Note Title:</strong> ${noteTitle}
                        </p>
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone and all data in this note will be permanently lost.
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteConsultationNote">
                            <i class="bi bi-trash me-1"></i>Delete Consultation Note
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('deleteConsultationNoteModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConsultationNoteModal'));
    modal.show();
    
    // Handle confirm button
    document.getElementById('confirmDeleteConsultationNote').addEventListener('click', function() {
        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';
        
        fetch(`/api/consultation-notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Consultation note deleted successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('Error: ' + (data.message || 'Failed to delete consultation note'));
                // Restore button state
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Consultation Note';
            }
        })
        .catch(error => {
            console.error('Consultation note delete error:', error);
            showErrorMessage('Error: ' + error.message);
            // Restore button state
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Consultation Note';
        });
    });
    
    // Clean up modal after hide
    document.getElementById('deleteConsultationNoteModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Delete Medication Function
function deleteMedication(medicationId) {
    showDeleteConfirmModal(
        'Delete Medication',
        'Are you sure you want to delete this medication?',
        'This action cannot be undone.',
        () => {
            fetch(`/api/prescriptions/meds/${medicationId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Medication deleted successfully');
                    // Remove medication card from DOM
                    const medCard = document.querySelector(`[data-medication-id="${medicationId}"]`);
                    if (medCard) {
                        medCard.remove();
                        // Check if no medications left
                        const container = document.getElementById('medicationsContainer');
                        if (container && container.querySelectorAll('[data-medication-id]').length === 0) {
                            container.innerHTML = `
                                <div class="text-center" id="emptyMedicationsMessage">
                                    <i class="bi bi-capsule text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No medications prescribed</p>
                                </div>
                            `;
                        }
                    } else {
                        // If card not found, reload all medications
                        reloadMedications();
                    }
                } else {
                    showErrorMessage('Error: ' + data.message);
                }
            })
            .catch(error => {
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

// Edit Medication Function
function editMedication(medicationId, drugName, notes) {
    const modalHtml = `
        <div class="modal fade" id="editMedicationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Medication</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editMedicationForm">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Dose</label>
                                    <input type="text" class="form-control" name="dose" placeholder="e.g., 1 tablet, 2 drops">
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Frequency</label>
                                    <input type="text" class="form-control" name="frequency" placeholder="e.g., Twice daily, Every 6 hours">
                            </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" name="duration" placeholder="e.g., 7 days, 2 weeks">
                                </div>
                                <div class="col-12 mb-3" style="display: none;">
                                    <label class="form-label">Route</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-control d-none" name="route">
                                                <option value="Topical" selected>Topical</option>
                                                <option value="Oral">Oral</option>
                                                <option value="IV">IV</option>
                                                <option value="IM">IM</option>
                                                <option value="Sublingual">Sublingual</option>
                                                <option value="Inhalation">Inhalation</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false">Topical</button>
                                            <menu>
                                                <li data-option="Topical" tabindex="0" role="button" class="selected"><i class="bi-arrow-right-circle fs-5"></i><h3>Topical</h3></li>
                                                <li data-option="Oral" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Oral</h3></li>
                                                <li data-option="IV" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>IV</h3></li>
                                                <li data-option="IM" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>IM</h3></li>
                                                <li data-option="Sublingual" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Sublingual</h3></li>
                                                <li data-option="Inhalation" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Inhalation</h3></li>
                                                <li data-option="Other" tabindex="0" role="button"><i class="bi-arrow-right-circle fs-5"></i><h3>Other</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                                <div class="mb-3">
                            <label class="form-label">Drug Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="drug_name" id="editDrugName" value="${drugName}" required>
                        </div>
                        
                        <!-- Most Used Drugs Container for Edit Modal -->
                        <div class="mb-3">
                            <label class="form-label text-muted small"><i class="bi bi-star me-1"></i>Most Used Suggestions</label>
                            <div id="editMostUsedDrugs" class="d-flex flex-wrap gap-1">
                                <!-- Suggestions will be loaded here -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3">${notes}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editMedicationModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    // Load most used drugs for the edit modal
    // Pass the container ID and the input ID
    setTimeout(() => {
        loadMostUsedDrugs('editMostUsedDrugs', 'editDrugName');
    }, 100);
    
    // Handle form submission
    document.getElementById('editMedicationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Convert FormData to URLSearchParams for PUT request
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            params.append(key, value);
        }
        
        fetch(`/api/prescriptions/meds/${medicationId}`, {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Medication updated successfully');
                setTimeout(() => {
                    reloadMedications();
                }, 300);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editMedicationModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Glasses Prescription Functions
function addGlassesPrescription(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="glassesModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Glasses Prescription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="glassesForm">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            
                            <!-- PD and Lens Type Section -->
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Distance (PD)</label>
                                    <input type="text" class="form-control" name="PD_DISTANCE" placeholder="62.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 62.0, +2, -1)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Near (NPD)</label>
                                    <input type="text" class="form-control" name="PD_NEAR" placeholder="58.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 58.0, +2, -1)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Lens Type</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-control d-none" name="lens_type">
                                                <option value="Single Vision" selected>Single Vision</option>
                                                <option value="Bifocal">Bifocal</option>
                                                <option value="Progressive">Progressive</option>
                                                <option value="Reading">Reading</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false">Single Vision</button>
                                            <menu>
                                                <li data-option="Single Vision" tabindex="0" role="button" class="selected"><i class="bi-eye fs-5"></i><h3>Single Vision</h3></li>
                                                <li data-option="Bifocal" tabindex="0" role="button"><i class="bi-eye fs-5"></i><h3>Bifocal</h3></li>
                                                <li data-option="Progressive" tabindex="0" role="button"><i class="bi-eye fs-5"></i><h3>Progressive</h3></li>
                                                <li data-option="Reading" tabindex="0" role="button"><i class="bi-eye fs-5"></i><h3>Reading</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Distance Vision Section -->
                            <h6 class="text-success mb-3"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="distance_sphere_r" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_r" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_r" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="distance_sphere_l" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_l" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_l" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Near Vision Section -->
                            <h6 class="text-info mb-3"><i class="bi bi-book me-2"></i>Near Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="near_sphere_r" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_r" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_r" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="near_sphere_l" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_l" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_l" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comments</label>
                                <textarea class="form-control" name="comments" rows="3" placeholder="Additional notes or instructions"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Glasses Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('glassesModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    // Handle form submission
    document.getElementById('glassesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/prescriptions/glasses', {
            method: 'POST',
            credentials: 'same-origin',
                headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
            .then(data => {
                if (data.success) {
                modal.hide();
                showSuccessMessage('Glasses prescription added successfully');
                setTimeout(() => {
                    reloadGlasses();
                }, 300);
                } else {
                showErrorMessage('Error: ' + (data.error || data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
            console.error('Glasses prescription error:', error);
                showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('glassesModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function editGlassesPrescription(glassesId, glassesData) {
    const modalHtml = `
        <div class="modal fade" id="editGlassesModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Glasses Prescription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editGlassesForm">
                        <div class="modal-body">
                            <!-- PD and Lens Type Section -->
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Distance (PD)</label>
                                    <input type="text" class="form-control" name="PD_DISTANCE" value="${glassesData.PD_DISTANCE || ''}" placeholder="62.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 62.0, +2, -1)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Near (NPD)</label>
                                    <input type="text" class="form-control" name="PD_NEAR" value="${glassesData.PD_NEAR || ''}" placeholder="58.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 58.0, +2, -1)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Lens Type</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-control d-none" name="lens_type">
                                                <option value="Single Vision" ${glassesData.lens_type === 'Single Vision' ? 'selected' : ''}>Single Vision</option>
                                                <option value="Bifocal" ${glassesData.lens_type === 'Bifocal' ? 'selected' : ''}>Bifocal</option>
                                                <option value="Progressive" ${glassesData.lens_type === 'Progressive' ? 'selected' : ''}>Progressive</option>
                                                <option value="Reading" ${glassesData.lens_type === 'Reading' ? 'selected' : ''}>Reading</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false">${glassesData.lens_type || 'Single Vision'}</button>
                                            <menu>
                                                <li data-option="Single Vision" tabindex="0" role="button" ${glassesData.lens_type === 'Single Vision' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Single Vision</h3></li>
                                                <li data-option="Bifocal" tabindex="0" role="button" ${glassesData.lens_type === 'Bifocal' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Bifocal</h3></li>
                                                <li data-option="Progressive" tabindex="0" role="button" ${glassesData.lens_type === 'Progressive' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Progressive</h3></li>
                                                <li data-option="Reading" tabindex="0" role="button" ${glassesData.lens_type === 'Reading' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Reading</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Distance Vision Section -->
                            <h6 class="text-success mb-3"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                            <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="distance_sphere_r" value="${glassesData.distance_sphere_r || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_r" value="${glassesData.distance_cylinder_r || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_r" value="${glassesData.distance_axis_r || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                </div>
                                </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="distance_sphere_l" value="${glassesData.distance_sphere_l || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_l" value="${glassesData.distance_cylinder_l || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_l" value="${glassesData.distance_axis_l || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Near Vision Section -->
                            <h6 class="text-info mb-3"><i class="bi bi-book me-2"></i>Near Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="near_sphere_r" value="${glassesData.near_sphere_r || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_r" value="${glassesData.near_cylinder_r || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_r" value="${glassesData.near_axis_r || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="near_sphere_l" value="${glassesData.near_sphere_l || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_l" value="${glassesData.near_cylinder_l || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_l" value="${glassesData.near_axis_l || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comments</label>
                                <textarea class="form-control" name="comments" rows="3">${glassesData.comments || ''}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Update Glasses Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editGlassesModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    // Handle form submission
    document.getElementById('editGlassesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Convert FormData to URLSearchParams for PUT request
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            params.append(key, value);
        }
        
        fetch(`/api/prescriptions/glasses/${glassesId}`, {
                method: 'PUT',
            credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Glasses prescription updated successfully');
                setTimeout(() => {
                    reloadGlasses();
                }, 300);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editGlassesModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function deleteGlassesPrescription(glassesId) {
    showDeleteConfirmModal(
        'Delete Glasses Prescription',
        'Are you sure you want to delete this glasses prescription?',
        'This action cannot be undone.',
        () => {
            fetch(`/api/prescriptions/glasses/${glassesId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Glasses prescription deleted successfully');
                    // Remove glasses card from DOM
                    const glassesCard = document.querySelector(`[data-glasses-id="${glassesId}"]`);
                    if (glassesCard) {
                        glassesCard.remove();
                        // Check if no glasses left
                        const container = document.getElementById('glassesContainer');
                        if (container && container.querySelectorAll('[data-glasses-id]').length === 0) {
                            container.innerHTML = `
                                <div class="text-center" id="emptyGlassesMessage">
                                    <i class="bi bi-eyeglasses text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No glasses prescription</p>
                                </div>
                            `;
                        }
                    } else {
                        // If card not found, reload all glasses
                        reloadGlasses();
                    }
                } else {
                    showErrorMessage('Error: ' + data.message);
                }
            })
            .catch(error => {
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

// Lab Tests & Radiology Functions
function addLabTest(appointmentId) {
    const modalHtml = '<div class="modal fade" id="labTestModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h5 class="modal-title">' +
        '<i class="bi bi-clipboard-data me-2"></i>Add Lab Test / Radiology' +
        '</h5>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '</div>' +
        '<form id="labTestForm">' +
        '<div class="modal-body">' +
        '<input type="hidden" name="appointment_id" value="' + appointmentId + '">' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Type</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="test_type" required id="testTypeSelect">' +
        '<option value="">Select Test Type</option>' +
        '<option value="laboratory">Laboratory Test</option>' +
        '<option value="radiology">Radiology</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">Select Test Type</button>' +
        '<menu>' +
        '<li data-option="" tabindex="0" role="button" class="selected"><i class="bi-clipboard-check fs-5"></i><h3>Select Test Type</h3></li>' +
        '<li data-option="laboratory" tabindex="0" role="button"><i class="bi-clipboard-check fs-5"></i><h3>Laboratory Test</h3></li>' +
        '<li data-option="radiology" tabindex="0" role="button"><i class="bi-clipboard-check fs-5"></i><h3>Radiology</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Category</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="test_category" required id="testCategorySelect">' +
        '<option value="">Select Category First</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">Select Category First</button>' +
        '<menu>' +
        '<li data-option="" tabindex="0" role="button" class="selected"><i class="bi-tags fs-5"></i><h3>Select Category First</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Test Name</label>' +
        '<input type="text" class="form-control" name="test_name" required placeholder="Enter specific test name">' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Priority</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="priority">' +
        '<option value="normal" selected>Normal</option>' +
        '<option value="high">High</option>' +
        '<option value="urgent">Urgent</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">Normal</button>' +
        '<menu>' +
        '<li data-option="normal" tabindex="0" role="button" class="selected"><i class="bi-flag fs-5" style="color: dodgerblue !important;"></i><h3 style="color: dodgerblue !important;">Normal</h3></li>' +
        '<li data-option="high" tabindex="0" role="button"><i class="bi-flag fs-5" style="color: orange !important;"></i><h3 style="color: orange !important;">High</h3></li>' +
        '<li data-option="urgent" tabindex="0" role="button"><i class="bi-flag fs-5" style="color: red !important;"></i><h3 style="color: red !important;">Urgent</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Status</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="status">' +
        '<option value="ordered" selected>Ordered</option>' +
        '<option value="pending">Pending</option>' +
        '<option value="completed">Completed</option>' +
        '<option value="cancelled">Cancelled</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">Ordered</button>' +
        '<menu>' +
        '<li data-option="ordered" tabindex="0" role="button" class="selected"><i class="bi-check-circle fs-5"></i><h3>Ordered</h3></li>' +
        '<li data-option="pending" tabindex="0" role="button"><i class="bi-check-circle fs-5"></i><h3>Pending</h3></li>' +
        '<li data-option="completed" tabindex="0" role="button"><i class="bi-check-circle fs-5"></i><h3>Completed</h3></li>' +
        '<li data-option="cancelled" tabindex="0" role="button"><i class="bi-check-circle fs-5"></i><h3>Cancelled</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Ordered Date</label>' +
        '<input type="date" class="form-control" name="ordered_date" value="' + new Date().toISOString().split('T')[0] + '">' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Expected Date</label>' +
        '<input type="date" class="form-control" name="expected_date" value="' + (() => { const tomorrow = new Date(); tomorrow.setDate(tomorrow.getDate() + 1); return tomorrow.toISOString().split('T')[0]; })() + '">' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Clinical Notes</label>' +
        '<textarea class="form-control" name="notes" rows="3" placeholder="Clinical indication, special instructions, etc."></textarea>' +
        '</div>' +
        '<div class="mb-3" id="resultsSection" style="display: none;">' +
        '<label class="form-label">Results</label>' +
        '<textarea class="form-control" name="results" rows="4" placeholder="Test results (if completed)"></textarea>' +
        '</div>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
        '<button type="submit" class="btn btn-primary">' +
        '<i class="bi bi-check-lg me-2"></i>Add Test' +
        '</button>' +
        '</div>' +
        '</form>' +
        '</div>' +
        '</div>' +
        '</div>';
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('labTestModal');
    const modal = new bootstrap.Modal(modalElement);
    
    // Attach status change listener immediately
    const statusSelect = modalElement.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const resultsSection = document.getElementById('resultsSection');
            if (this.value === 'completed') {
                resultsSection.style.display = 'block';
            } else {
                resultsSection.style.display = 'none';
            }
        });
    }
    
    // Wait for modal to be shown before initializing custom selects
    modalElement.addEventListener('shown.bs.modal', function() {
        // Initialize custom selects first
        setTimeout(() => {
            initCustomSelects();
            
            // Ensure category select is initialized (even if empty)
            const categorySelect = modalElement.querySelector('#testCategorySelect');
            if (categorySelect) {
                const categoryFieldMenu = categorySelect.closest('.field.menu');
                if (categoryFieldMenu && !categoryFieldMenu.hasAttribute('data-initialized')) {
                    // Force initialization of category select
                    const categoryMenu = categoryFieldMenu.querySelector('menu');
                    if (categoryMenu && categoryMenu.children.length === 0) {
                        // Add default option to menu if empty
                        const defaultLi = document.createElement('li');
                        defaultLi.setAttribute('data-option', '');
                        defaultLi.setAttribute('tabindex', '0');
                        defaultLi.setAttribute('role', 'button');
                        defaultLi.className = 'selected';
                        const defaultIcon = document.createElement('i');
                        defaultIcon.className = 'bi-tags fs-5';
                        defaultLi.appendChild(defaultIcon);
                        const defaultH3 = document.createElement('h3');
                        defaultH3.textContent = 'Select Category';
                        defaultLi.appendChild(defaultH3);
                        categoryMenu.appendChild(defaultLi);
                    }
                    initCustomSelects();
                }
            }
            
            // Attach test type change listener AFTER custom selects are initialized
            // This ensures the select element exists and the listener works properly
            const testTypeSelect = modalElement.querySelector('select[name="test_type"]');
            if (testTypeSelect) {
                // Check if listener already attached to avoid duplicates
                if (!testTypeSelect._testTypeChangeHandler) {
                    const handleTestTypeChange = function() {
                        const testType = this.value;
                        if (testType) {
                            // Small delay to ensure DOM is ready
                            setTimeout(() => {
                                updateTestCategories(testType);
                            }, 50);
                        }
                    };
                    testTypeSelect.addEventListener('change', handleTestTypeChange);
                    testTypeSelect._testTypeChangeHandler = handleTestTypeChange;
                }
            }
        }, 100);
    });
    
    modal.show();
    
    // Handle form submission
    document.getElementById('labTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Convert FormData to object
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        // Ensure appointment_id is included
        if (!data.appointment_id) {
            data.appointment_id = appointmentId;
        }
        
        // Get select values directly from hidden selects (in case FormData doesn't capture them)
        const testTypeSelect = this.querySelector('select[name="test_type"]');
        const testCategorySelect = this.querySelector('select[name="test_category"]');
        
        if (testTypeSelect) {
            data.test_type = testTypeSelect.value || data.test_type;
        }
        if (testCategorySelect) {
            data.test_category = testCategorySelect.value || data.test_category;
        }
        
        // Ensure all required fields are present
        if (!data.appointment_id || !data.test_type || !data.test_category || !data.test_name) {
            const missingFields = [];
            if (!data.appointment_id) missingFields.push('Appointment ID');
            if (!data.test_type) missingFields.push('Test Type');
            if (!data.test_category) missingFields.push('Test Category');
            if (!data.test_name) missingFields.push('Test Name');
            showErrorMessage('Please fill in all required fields: ' + missingFields.join(', '));
            return;
        }
        
        fetch('/api/lab-tests', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const errorData = JSON.parse(text);
                        return Promise.reject(new Error(errorData.error || errorData.message || 'Server error'));
                    } catch (e) {
                        // If response is HTML (error page), extract text content
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(text, 'text/html');
                        const errorText = doc.querySelector('h1, p')?.textContent || text.substring(0, 100);
                        return Promise.reject(new Error(errorText || `HTTP error! status: ${response.status}`));
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Lab test added successfully');
                setTimeout(() => {
                    reloadLabTests();
                }, 300);
            } else {
                showErrorMessage('Error: ' + (data.error || data.message || 'Failed to add lab test'));
            }
        })
        .catch(error => {
            console.error('Lab test error:', error);
            showErrorMessage('Error: ' + (error.message || 'Failed to add lab test'));
        });
    });
    
    // Clean up modal on hide
    document.getElementById('labTestModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function updateTestCategories(testType) {
    if (!testType || testType === '') {
        return;
    }
    
    // Normalize test type to handle any case variations
    testType = String(testType).toLowerCase().trim();
    
    const categorySelect = document.getElementById('testCategorySelect');
    if (!categorySelect) {
        return;
    }
    
    const fieldMenu = categorySelect.closest('.field.menu');
    const menu = fieldMenu ? fieldMenu.querySelector('menu') : null;
    const button = fieldMenu ? fieldMenu.querySelector('.custom-select-toggle') : null;
    
    // Clear existing options
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    // Clear menu
    if (menu) {
        menu.innerHTML = '';
        
        // Add default "Select Category" option to menu
        const defaultLi = document.createElement('li');
        defaultLi.setAttribute('data-option', '');
        defaultLi.setAttribute('tabindex', '0');
        defaultLi.setAttribute('role', 'button');
        defaultLi.className = 'selected';
        
        const defaultIcon = document.createElement('i');
        defaultIcon.className = 'bi-tags fs-5';
        defaultLi.appendChild(defaultIcon);
        
        const defaultH3 = document.createElement('h3');
        defaultH3.textContent = 'Select Category';
        defaultLi.appendChild(defaultH3);
        
        menu.appendChild(defaultLi);
    }
    
    let categories = [];
    if (testType === 'laboratory') {
        categories = [
            'Hematology',
            'Biochemistry', 
            'Immunology',
            'Microbiology',
            'Hormones',
            'Tumor Markers',
            'Cardiac Markers',
            'Coagulation',
            'Urine Analysis',
            'Stool Analysis'
        ];
    } else if (testType === 'radiology') {
        categories = [
            'X-Ray',
            'CT Scan',
            'MRI',
            'Ultrasound',
            'Mammography',
            'Fluoroscopy',
            'Nuclear Medicine',
            'PET Scan',
            'Angiography'
        ];
    } else {
        // Invalid test type
        return;
    }
    
    // Add categories to select and menu
    categories.forEach(category => {
        const optionValue = category.toLowerCase().replace(/\s+/g, '_');
        
        // Add to select
        const option = document.createElement('option');
        option.value = optionValue;
        option.textContent = category;
        categorySelect.appendChild(option);
        
        // Add to menu
        if (menu) {
            const li = document.createElement('li');
            li.setAttribute('data-option', optionValue);
            li.setAttribute('tabindex', '0');
            li.setAttribute('role', 'button');
            
            const icon = document.createElement('i');
            icon.className = 'bi-tags fs-5';
            li.appendChild(icon);
            
            const h3 = document.createElement('h3');
            h3.textContent = category;
            li.appendChild(h3);
            
            menu.appendChild(li);
        }
    });
    
    // Update button text
    if (button) {
        button.textContent = 'Select Category';
    }
    
    // Reset select value
    categorySelect.value = '';
    
    // Manually re-initialize this specific custom select field
    if (fieldMenu && menu && button && categorySelect) {
        const menuItems = menu.querySelectorAll('li');
        
        // Remove initialization flag
        fieldMenu.removeAttribute('data-initialized');
        
        // Remove existing event listeners from button
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        const newButtonRef = fieldMenu.querySelector('.custom-select-toggle');
        
        // Remove existing event listeners from menu items by cloning
        const newMenuItems = [];
        menuItems.forEach(item => {
            const cloned = item.cloneNode(true);
            newMenuItems.push(cloned);
        });
        menu.innerHTML = '';
        newMenuItems.forEach(item => menu.appendChild(item));
        
        // Get fresh references after cloning
        const finalMenuItems = menu.querySelectorAll('li');
        const finalSelect = fieldMenu.querySelector('select');
        const finalButton = fieldMenu.querySelector('.custom-select-toggle');
        
        // Now manually attach all event listeners like initCustomSelects does
        function openMenu() {
            // Close any other open menus first
            document.querySelectorAll('.field.menu.open').forEach(openField => {
                if (openField !== fieldMenu) {
                    const openButton = openField.querySelector('.custom-select-toggle');
                    openField.classList.remove('open');
                    if (openButton) openButton.setAttribute('aria-expanded', 'false');
                }
            });
            
            fieldMenu.classList.add('open');
            finalButton.setAttribute('aria-expanded', 'true');
        }
        
        function closeMenu() {
            fieldMenu.classList.remove('open');
            finalButton.setAttribute('aria-expanded', 'false');
        }
        
        function setOption(optionEl) {
            const value = optionEl.dataset.option;
            const text = optionEl.querySelector('h3')?.textContent || optionEl.textContent;
            
            finalSelect.value = value;
            finalSelect.dispatchEvent(new Event('change', { bubbles: true }));
            
            finalButton.textContent = text;
            
            finalMenuItems.forEach(el => el.classList.remove('selected'));
            optionEl.classList.add('selected');
            
            closeMenu();
        }
        
        // Attach button click handler
        finalButton.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            if (fieldMenu.classList.contains('open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });
        
        // Attach button keyboard handler
        finalButton.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openMenu();
            }
        });
        
        // Attach menu item click handlers
        finalMenuItems.forEach(option => {
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
        
        // Prevent clicks on menu from closing modal
        menu.addEventListener('click', (e) => {
            e.stopPropagation();
        });
        
        // Set up outside click handler
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
            
            if (fieldMenu.classList.contains('open') && !fieldMenu.contains(target)) {
                const modal = fieldMenu.closest('.modal');
                if (modal && target === modal) {
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                closeMenu();
            }
        };
        
        // Remove old handler if exists
        if (fieldMenu._outsideClickHandler) {
            document.removeEventListener('click', fieldMenu._outsideClickHandler);
        }
        
        // Store and attach new handler
        fieldMenu._outsideClickHandler = handleOutsideClick;
        document.addEventListener('click', handleOutsideClick, false);
        
        // Mark as initialized
        fieldMenu.setAttribute('data-initialized', 'true');
        
        // Update button text
        const selectedOption = finalSelect.options[finalSelect.selectedIndex];
        if (selectedOption && selectedOption.value === '') {
            finalButton.textContent = 'Select Category';
        }
    }
}

function updateEditTestCategories(testType) {
    const categorySelect = document.getElementById('editTestCategorySelect');
    const fieldMenu = categorySelect.closest('.field.menu');
    const menu = fieldMenu ? fieldMenu.querySelector('menu') : null;
    const button = fieldMenu ? fieldMenu.querySelector('.custom-select-toggle') : null;
    const currentValue = categorySelect.querySelector('option') ? categorySelect.querySelector('option').value : '';
    
    categorySelect.innerHTML = '<option value="' + currentValue + '">' + (currentValue || 'Select Category') + '</option>';
    
    if (menu) {
        menu.innerHTML = '<li data-option="' + currentValue + '" tabindex="0" role="button" class="selected"><i class="bi-tags fs-5"></i><h3>' + (currentValue || 'Select Category') + '</h3></li>';
    }
    
    let categories = [];
    if (testType === 'laboratory') {
        categories = [
            'Hematology', 'Biochemistry', 'Immunology', 'Microbiology',
            'Hormones', 'Tumor Markers', 'Cardiac Markers', 'Coagulation',
            'Urine Analysis', 'Stool Analysis'
        ];
    } else if (testType === 'radiology') {
        categories = [
            'X-Ray', 'CT Scan', 'MRI', 'Ultrasound', 'Mammography',
            'Fluoroscopy', 'Nuclear Medicine', 'PET Scan', 'Angiography'
        ];
    }
    
    categories.forEach(category => {
        const categoryValue = category.toLowerCase().replace(/\s+/g, '_');
        if (categoryValue !== currentValue) {
            const option = document.createElement('option');
            option.value = categoryValue;
            option.textContent = category;
            categorySelect.appendChild(option);
            
            if (menu) {
                const li = document.createElement('li');
                li.setAttribute('data-option', categoryValue);
                li.setAttribute('tabindex', '0');
                li.setAttribute('role', 'button');
                const icon = document.createElement('i');
                icon.className = 'bi-tags fs-5';
                li.appendChild(icon);
                const h3 = document.createElement('h3');
                h3.textContent = category;
                li.appendChild(h3);
                menu.appendChild(li);
            }
        }
    });
    
    // Remove initialization flag to allow re-initialization
    if (fieldMenu) {
        fieldMenu.removeAttribute('data-initialized');
    }
    
    // Re-initialize custom select if menu exists
    if (fieldMenu) {
        setTimeout(() => {
            initCustomSelects();
        }, 50);
    }
}

function editLabTest(testId, testData) {
    const modalHtml = '<div class="modal fade" id="editLabTestModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h5 class="modal-title">' +
        '<i class="bi bi-pencil me-2"></i>Edit Lab Test / Radiology' +
        '</h5>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '</div>' +
        '<form id="editLabTestForm">' +
        '<div class="modal-body">' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Type</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="test_type" required id="editTestTypeSelect">' +
        '<option value="laboratory"' + (testData.test_type === 'laboratory' ? ' selected' : '') + '>Laboratory Test</option>' +
        '<option value="radiology"' + (testData.test_type === 'radiology' ? ' selected' : '') + '>Radiology</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">' + (testData.test_type === 'laboratory' ? 'Laboratory Test' : (testData.test_type === 'radiology' ? 'Radiology' : 'Laboratory Test')) + '</button>' +
        '<menu>' +
        '<li data-option="laboratory" tabindex="0" role="button" ' + (testData.test_type === 'laboratory' ? 'class="selected"' : '') + '><i class="bi-clipboard-check fs-5"></i><h3>Laboratory Test</h3></li>' +
        '<li data-option="radiology" tabindex="0" role="button" ' + (testData.test_type === 'radiology' ? 'class="selected"' : '') + '><i class="bi-clipboard-check fs-5"></i><h3>Radiology</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Category</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="test_category" required id="editTestCategorySelect">' +
        '<option value="' + (testData.test_category || '') + '">' + (testData.test_category || 'Select Category') + '</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">' + (testData.test_category || 'Select Category') + '</button>' +
        '<menu>' +
        '<li data-option="' + (testData.test_category || '') + '" tabindex="0" role="button" class="selected"><i class="bi-tags fs-5"></i><h3>' + (testData.test_category || 'Select Category') + '</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Test Name</label>' +
        '<input type="text" class="form-control" name="test_name" required value="' + (testData.test_name || '') + '" placeholder="Enter specific test name">' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Priority</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="priority">' +
        '<option value="normal"' + (testData.priority === 'normal' ? ' selected' : '') + '>Normal</option>' +
        '<option value="high"' + (testData.priority === 'high' ? ' selected' : '') + '>High</option>' +
        '<option value="urgent"' + (testData.priority === 'urgent' ? ' selected' : '') + '>Urgent</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">' + (testData.priority === 'normal' ? 'Normal' : (testData.priority === 'high' ? 'High' : (testData.priority === 'urgent' ? 'Urgent' : 'Normal'))) + '</button>' +
        '<menu>' +
        '<li data-option="normal" tabindex="0" role="button" ' + (testData.priority === 'normal' ? 'class="selected"' : '') + '><i class="bi-flag fs-5" style="color: dodgerblue !important;"></i><h3 style="color: dodgerblue !important;">Normal</h3></li>' +
        '<li data-option="high" tabindex="0" role="button" ' + (testData.priority === 'high' ? 'class="selected"' : '') + '><i class="bi-flag fs-5" style="color: orange !important;"></i><h3 style="color: orange !important;">High</h3></li>' +
        '<li data-option="urgent" tabindex="0" role="button" ' + (testData.priority === 'urgent' ? 'class="selected"' : '') + '><i class="bi-flag fs-5" style="color: red !important;"></i><h3 style="color: red !important;">Urgent</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Status</label>' +
        '<section class="field menu" style="min-width: 100%;">' +
        '<div class="control">' +
        '<select class="form-select d-none" name="status" onchange="toggleEditResultsSection(this.value)">' +
        '<option value="ordered"' + (testData.status === 'ordered' ? ' selected' : '') + '>Ordered</option>' +
        '<option value="pending"' + (testData.status === 'pending' ? ' selected' : '') + '>Pending</option>' +
        '<option value="completed"' + (testData.status === 'completed' ? ' selected' : '') + '>Completed</option>' +
        '<option value="cancelled"' + (testData.status === 'cancelled' ? ' selected' : '') + '>Cancelled</option>' +
        '</select>' +
        '<button type="button" class="custom-select-toggle" aria-expanded="false">' + (testData.status === 'ordered' ? 'Ordered' : (testData.status === 'pending' ? 'Pending' : (testData.status === 'completed' ? 'Completed' : (testData.status === 'cancelled' ? 'Cancelled' : 'Ordered')))) + '</button>' +
        '<menu>' +
        '<li data-option="ordered" tabindex="0" role="button" ' + (testData.status === 'ordered' ? 'class="selected"' : '') + '><i class="bi-check-circle fs-5"></i><h3>Ordered</h3></li>' +
        '<li data-option="pending" tabindex="0" role="button" ' + (testData.status === 'pending' ? 'class="selected"' : '') + '><i class="bi-check-circle fs-5"></i><h3>Pending</h3></li>' +
        '<li data-option="completed" tabindex="0" role="button" ' + (testData.status === 'completed' ? 'class="selected"' : '') + '><i class="bi-check-circle fs-5"></i><h3>Completed</h3></li>' +
        '<li data-option="cancelled" tabindex="0" role="button" ' + (testData.status === 'cancelled' ? 'class="selected"' : '') + '><i class="bi-check-circle fs-5"></i><h3>Cancelled</h3></li>' +
        '</menu>' +
        '</div>' +
        '</section>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Ordered Date</label>' +
        '<input type="date" class="form-control" name="ordered_date" value="' + (testData.ordered_date || '') + '">' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Expected Date</label>' +
        '<input type="date" class="form-control" name="expected_date" value="' + (testData.expected_date || '') + '">' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Clinical Notes</label>' +
        '<textarea class="form-control" name="notes" rows="3" placeholder="Clinical indication, special instructions, etc.">' + (testData.notes || '') + '</textarea>' +
        '</div>' +
        '<div class="mb-3" id="editResultsSection" style="display: ' + (testData.status === 'completed' ? 'block' : 'none') + ';">' +
        '<label class="form-label">Results</label>' +
        '<textarea class="form-control" name="results" rows="4" placeholder="Test results">' + (testData.results || '') + '</textarea>' +
        '</div>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
        '<button type="submit" class="btn btn-primary">' +
        '<i class="bi bi-check-lg me-2"></i>Update Test' +
        '</button>' +
        '</div>' +
        '</form>' +
        '</div>' +
        '</div>' +
        '</div>';
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const editModalElement = document.getElementById('editLabTestModal');
    const modal = new bootstrap.Modal(editModalElement);
    
    // Attach status change listener immediately
    const statusSelect = editModalElement.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const resultsSection = document.getElementById('editResultsSection');
            if (this.value === 'completed') {
                resultsSection.style.display = 'block';
            } else {
                resultsSection.style.display = 'none';
            }
        });
    }
    
    // Wait for modal to be shown before initializing custom selects
    editModalElement.addEventListener('shown.bs.modal', function() {
        setTimeout(() => {
            // First, populate categories for the current test type BEFORE initializing custom selects
            if (testData.test_type) {
                updateEditTestCategories(testData.test_type);
            }
            
            // Then initialize custom selects
            setTimeout(() => {
                initCustomSelects();
                
                // Attach test type change listener AFTER custom selects are initialized
                const testTypeSelect = editModalElement.querySelector('select[name="test_type"]');
                if (testTypeSelect) {
                    // Check if listener already attached to avoid duplicates
                    if (!testTypeSelect._testTypeChangeHandler) {
                        const handleEditTestTypeChange = function() {
                            const testType = this.value;
                            if (testType) {
                                updateEditTestCategories(testType);
                            }
                        };
                        testTypeSelect.addEventListener('change', handleEditTestTypeChange);
                        testTypeSelect._testTypeChangeHandler = handleEditTestTypeChange;
                    }
                }
            }, 50);
        }, 100);
    });
    
    modal.show();
    
    // Handle form submission
    document.getElementById('editLabTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Convert FormData to JSON object
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        
        fetch('/api/lab-tests/' + testId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Lab test updated successfully');
                setTimeout(() => {
                    reloadLabTests();
                }, 300);
            } else {
                showErrorMessage('Error: ' + (data.message || 'Failed to update lab test'));
            }
        })
        .catch(error => {
            console.error('Lab test update error:', error);
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editLabTestModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function updateEditTestCategories(testType) {
    if (!testType || testType === '') {
        return;
    }
    
    const categorySelect = document.getElementById('editTestCategorySelect');
    if (!categorySelect) {
        return;
    }
    
    const fieldMenu = categorySelect.closest('.field.menu');
    const menu = fieldMenu ? fieldMenu.querySelector('menu') : null;
    const button = fieldMenu ? fieldMenu.querySelector('.custom-select-toggle') : null;
    
    // Get current value from the select (preserve existing selection)
    const currentValue = categorySelect.value || '';
    
    // Clear existing options
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    // Clear menu and add default option
    if (menu) {
        menu.innerHTML = '';
        const defaultLi = document.createElement('li');
        defaultLi.setAttribute('data-option', '');
        defaultLi.setAttribute('tabindex', '0');
        defaultLi.setAttribute('role', 'button');
        if (!currentValue) {
            defaultLi.className = 'selected';
        }
        const defaultIcon = document.createElement('i');
        defaultIcon.className = 'bi-tags fs-5';
        defaultLi.appendChild(defaultIcon);
        const defaultH3 = document.createElement('h3');
        defaultH3.textContent = 'Select Category';
        defaultLi.appendChild(defaultH3);
        menu.appendChild(defaultLi);
    }
    
    let categories = [];
    if (testType === 'laboratory') {
        categories = [
            'Hematology', 'Biochemistry', 'Immunology', 'Microbiology',
            'Hormones', 'Tumor Markers', 'Cardiac Markers', 'Coagulation',
            'Urine Analysis', 'Stool Analysis'
        ];
    } else if (testType === 'radiology') {
        categories = [
            'X-Ray', 'CT Scan', 'MRI', 'Ultrasound', 'Mammography',
            'Fluoroscopy', 'Nuclear Medicine', 'PET Scan', 'Angiography'
        ];
    } else {
        return;
    }
    
    // Add ALL categories, including the current one
    categories.forEach(category => {
        const categoryValue = category.toLowerCase().replace(/\s+/g, '_');
        const option = document.createElement('option');
        option.value = categoryValue;
        option.textContent = category;
        if (categoryValue === currentValue) {
            option.selected = true;
        }
        categorySelect.appendChild(option);
        
        if (menu) {
            const li = document.createElement('li');
            li.setAttribute('data-option', categoryValue);
            li.setAttribute('tabindex', '0');
            li.setAttribute('role', 'button');
            if (categoryValue === currentValue) {
                li.className = 'selected';
                const defaultLi = menu.querySelector('li[data-option=""]');
                if (defaultLi) {
                    defaultLi.classList.remove('selected');
                }
            }
            const icon = document.createElement('i');
            icon.className = 'bi-tags fs-5';
            li.appendChild(icon);
            const h3 = document.createElement('h3');
            h3.textContent = category;
            li.appendChild(h3);
            menu.appendChild(li);
        }
    });
    
    // Update button text to show current selection
    if (button && currentValue) {
        const selectedCategory = categories.find(cat => cat.toLowerCase().replace(/\s+/g, '_') === currentValue);
        if (selectedCategory) {
            button.textContent = selectedCategory;
        }
    } else if (button) {
        button.textContent = 'Select Category';
    }
    
    // Remove initialization flag to allow re-initialization
    if (fieldMenu) {
        fieldMenu.removeAttribute('data-initialized');
    }
    
    // Re-initialize custom select if menu exists
    if (fieldMenu) {
        setTimeout(() => {
            initCustomSelects();
        }, 50);
    }
}

function toggleEditResultsSection(status) {
    const resultsSection = document.getElementById('editResultsSection');
    if (status === 'completed') {
        resultsSection.style.display = 'block';
    } else {
        resultsSection.style.display = 'none';
    }
}

function deleteLabTest(testId) {
    showDeleteConfirmModal(
        'Delete Lab Test',
        'Are you sure you want to delete this lab test?',
        'This action cannot be undone.',
        () => {
            fetch('/api/lab-tests/' + testId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Lab test deleted successfully');
                    setTimeout(() => {
                        reloadLabTests();
                    }, 300);
                } else {
                    showErrorMessage('Error: ' + (data.message || 'Failed to delete lab test'));
                }
            })
            .catch(error => {
                console.error('Lab test delete error:', error);
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

function printLabTest(testId) {
    // Open lab test print view
    window.open('/print/lab-test/' + testId, '_blank');
}

function printLabTests(appointmentId) {
    // Open all lab tests print view for this appointment
    window.open('/print/lab-tests/' + appointmentId, '_blank');
}

// Status badge functions
/* Single source of truth for "is this appointment missed". The
   APPOINTMENT_CONFIG.appointmentIsMissed flag is set by PHP using the
   server's timezone, so a Booked-but-past-date appointment is consistently
   tagged everywhere without a JS-vs-server timezone drift. The fallback
   for the rare case of a freshly-loaded status (e.g. just transitioned to
   Booked client-side) uses the local browser date, NOT UTC. */
function _isAppointmentMissed(status) {
    if (status !== 'Booked') return false;
    if (window.APPOINTMENT_CONFIG && typeof window.APPOINTMENT_CONFIG.appointmentIsMissed === 'boolean') {
        return window.APPOINTMENT_CONFIG.appointmentIsMissed;
    }
    const apptDate = window.APPOINTMENT_CONFIG?.appointmentDate || '';
    if (!apptDate) return false;
    const n = new Date();
    const today = n.getFullYear() + '-' +
                  String(n.getMonth() + 1).padStart(2, '0') + '-' +
                  String(n.getDate()).padStart(2, '0');
    return apptDate < today;
}

function getStatusBadgeClass(status) {
    if (_isAppointmentMissed(status)) {
        return 'bg-danger text-white';
    }
    
    const classes = {
        'Booked': 'bg-primary',
        'CheckedIn': 'bg-success',
        'InProgress': 'bg-warning',
        'Completed': 'bg-info',
        'Cancelled': 'bg-danger',
        'NoShow': 'bg-secondary',
        'Rescheduled': 'bg-info',
        'Closed': 'bg-danger',
        'Missed': 'bg-danger text-white'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusDisplayText(status) {
    if (_isAppointmentMissed(status)) {
        return 'Missed';
    }
    
    const statusTexts = {
        'Booked': 'Booked',
        'CheckedIn': 'Checked In',
        'InProgress': 'In Progress',
        'Completed': 'Completed',
        'Cancelled': 'Cancelled',
        'NoShow': 'No Show',
        'Rescheduled': 'Rescheduled',
        'Closed': 'Closed',
        'Missed': 'Missed'
    };
    return statusTexts[status] || status;
}

function getStatusIcon(status) {
    if (_isAppointmentMissed(status)) {
        return 'bi-exclamation-triangle-fill';
    }
    
    const icons = {
        'Booked': 'bi-calendar-check',
        'CheckedIn': 'bi-check-circle-fill',
        'InProgress': 'bi-hourglass-split',
        'Completed': 'bi-check2-all',
        'Cancelled': 'bi-x-circle-fill',
        'NoShow': 'bi-clock-fill',
        'Rescheduled': 'bi-arrow-clockwise',
        'Closed': 'bi-lock-fill',
        'Missed': 'bi-exclamation-triangle-fill'
    };
    return icons[status] || 'bi-question-circle';
}

function updateStatusBadge(status) {
    const badge = document.getElementById('appointmentStatusBadge');
    const icon = document.getElementById('statusIcon');
    const text = document.getElementById('statusText');
    const markCompletedBtn = document.getElementById('markCompletedBtn');

    if (badge && icon && text) {
        /* Pass `status` through the helpers — they'll switch to "Missed"
           internally when _isAppointmentMissed(status) is true. Previously
           the function computed displayStatus locally and then never used
           it, so the badge stayed "Booked" while only the header turned
           red on past-Booked appointments. */
        badge.className = `status-badge d-flex align-items-center gap-2 ${getStatusBadgeClass(status)}`;
        icon.className = `bi ${getStatusIcon(status)}`;
        text.textContent = getStatusDisplayText(status);

        // Show/hide "Mark as Completed" button based on status
        if (markCompletedBtn) {
            if (status === 'Completed') {
                markCompletedBtn.style.display = 'none';
            } else {
                markCompletedBtn.style.display = 'inline-block';
            }
        }
    }

    // Update appointment header background colors
    updateAppointmentHeaderClasses(status);
}

// Function to update appointment header classes based on status
function updateAppointmentHeaderClasses(status) {
    const header = document.querySelector('.appointment-header');
    if (!header) return;

    // Remove existing status classes
    header.classList.remove('completed', 'missed', 'closed', 'rescheduled');

    const statusLower = (status || '').toLowerCase();
    const isMissed = _isAppointmentMissed(status);

    if (statusLower === 'completed') {
        header.classList.add('completed');
    } else if (isMissed || statusLower === 'cancelled' || statusLower === 'noshow') {
        header.classList.add('missed');
    } else if (status === 'Closed') {
        header.classList.add('closed');
    } else if (status === 'Rescheduled') {
        header.classList.add('rescheduled');
    }
}

// Helper function to close dropdown and execute action
function closeDropdownAndExecute(dropdownId, action) {
    const dropdownElement = document.getElementById(dropdownId);
    
    if (dropdownElement) {
        const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
        if (dropdown) {
            dropdown.hide();
        } else {
            // Try to create instance if it doesn't exist
            try {
                const newDropdown = new bootstrap.Dropdown(dropdownElement);
                newDropdown.hide();
            } catch (e) {
                // Error creating Dropdown instance
            }
        }
    }
    
    // Execute action after a small delay to ensure dropdown closes
    setTimeout(function() {
        if (typeof action === 'function') {
            action();
        }
    }, 100);
}

// Initialize More Actions Popover
function initMoreActionsPopover() {
    const moreActionsBtn = document.getElementById('moreActionsBtn');
    if (moreActionsBtn) {
        const popover = new bootstrap.Popover(moreActionsBtn, {
            html: true,
            trigger: 'click',
            placement: 'bottom',
            sanitize: false,
            container: 'body'
        });
        
        // Update popover content dynamically when needed
        window.updateMoreActionsPopover = function() {
            const appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
            const patientId = window.APPOINTMENT_CONFIG.patientId;
            const appointmentStatus = window.APPOINTMENT_CONFIG.appointmentStatus;
            
            // Fetch current data and update popover content
            Promise.all([
                fetch(`/api/appointments/${appointmentId}/medications`, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).catch(() => ({ success: false })),
                fetch(`/api/appointments/${appointmentId}/glasses`, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).catch(() => ({ success: false })),
                fetch(`/api/lab-tests/appointment/${appointmentId}`, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).catch(() => ({ success: false }))
            ]).then(([medData, glassesData, labTestsData]) => {
                const hasMedications = medData.success && medData.medications && medData.medications.length > 0;
                const hasGlasses = glassesData.success && glassesData.glasses && glassesData.glasses.length > 0;
                const hasLabTests = labTestsData.success && labTestsData.lab_tests && labTestsData.lab_tests.length > 0;
                
                let content = '<div class="more-actions-popover">';
                content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); editConsultation(${appointmentId});"><i class="bi bi-pencil me-2"></i>Edit Consultation</div>`;
                content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); printReport(${appointmentId});"><i class="bi bi-printer me-2"></i>Print Report</div>`;
                
                if (appointmentStatus !== 'Completed') {
                    content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); rescheduleAppointment(${appointmentId});"><i class="bi bi-calendar-plus me-2"></i>Reschedule</div>`;
                } else {
                    content += `<div class="more-actions-popover-item disabled" onclick="return false;" title="Cannot reschedule completed appointments"><i class="bi bi-calendar-plus me-2"></i>Reschedule</div>`;
                }
                
                content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); openAlertModal(${patientId}, ${appointmentId});"><i class="bi bi-bell me-2"></i>Set Alert</div>`;
                
                if (hasMedications) {
                    content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); printPrescription(${appointmentId});"><i class="bi bi-printer me-2"></i>Print Prescription</div>`;
                }
                if (hasGlasses) {
                    content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); printGlassesPrescription(${appointmentId});"><i class="bi bi-eyeglasses me-2"></i>Print Glasses</div>`;
                }
                if (hasLabTests) {
                    content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); printLabTests(${appointmentId});"><i class="bi bi-clipboard-data me-2"></i>Print Lab Tests</div>`;
                }
                
                content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); viewPatient(${patientId});"><i class="bi bi-person me-2"></i>View Patient Profile</div>`;
                content += `<div class="more-actions-popover-item" onclick="bootstrap.Popover.getInstance(document.getElementById('moreActionsBtn')).hide(); scheduleFollowUp(${appointmentId});"><i class="bi bi-calendar-plus me-2"></i>Schedule Follow-up</div>`;
                content += '</div>';
                
                popover.setContent({ '.popover-body': content });
            });
        };
    }
}

// Initialize tooltips for file attachments
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Medical History Carousel
    initMedicalHistoryCarousel();
    
    // Load medications with prices from API
    if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentId) {
        reloadMedications();
    }
    
    // Initialize status badge
    updateStatusBadge(window.APPOINTMENT_CONFIG.appointmentStatus);
    
    // Show follow-up toast if follow-up appointment exists
    if (window.APPOINTMENT_CONFIG.followupAppointment) {
        const followupDate = window.APPOINTMENT_CONFIG.followupAppointmentDate;
        const followupTime = window.APPOINTMENT_CONFIG.followupAppointmentTime;
        const patientName = window.APPOINTMENT_CONFIG.patientName;
    
    setTimeout(function() {
        showFollowupToast(patientName, followupDate, followupTime);
    }, 500);
    }
    
    // Update print buttons on page load
    updateActionPrintButtons();
    
    // Initialize More Actions Popover
    initMoreActionsPopover();
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap dropdowns manually to ensure they work
    const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.forEach(function (dropdownTriggerEl) {
        try {
            const dropdown = new bootstrap.Dropdown(dropdownTriggerEl, {
                popperConfig: null // Disable Popper.js positioning
            });
        } catch (e) {
            console.error('Error initializing dropdown:', e);
        }
    });
    
    // Specifically check for moreActionsDropdown
    const moreActionsBtn = document.getElementById('moreActionsDropdown');
    if (moreActionsBtn) {
        
        // Check dropdown menu
        const dropdownMenu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
        if (dropdownMenu) {
        }
        
        // Function to position dropdown menu below button
        let positionAnimationFrame = null;
        
        function positionDropdownMenu() {
            const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
            if (menu && menu.classList.contains('show')) {
                const buttonRect = moreActionsBtn.getBoundingClientRect();
                
                // Calculate position: menu should be directly below button
                // getBoundingClientRect() gives position relative to viewport, perfect for fixed positioning
                let top = buttonRect.bottom + 4;
                
                // Check if menu would go off screen at bottom
                const estimatedMenuHeight = 220; // Approximate menu height
                if (buttonRect.bottom + estimatedMenuHeight > window.innerHeight) {
                    // Show menu above button instead
                    top = buttonRect.top - estimatedMenuHeight - 4;
                }
                
                // Calculate left position: align with button's left edge + 15px
                // Menu width should match button width
                const buttonWidth = buttonRect.width;
                let leftValue = buttonRect.left + 15;
                
                // Ensure menu doesn't go off screen on the right
                if (leftValue + buttonWidth > window.innerWidth - 16) {
                    leftValue = window.innerWidth - buttonWidth - 16; // 16px margin from right
                }
                
                // Ensure menu doesn't go off screen on the left
                if (leftValue < 16) {
                    leftValue = 16; // 16px margin from left
                }
                
                // Apply fixed positioning below button - same width as button, 15px left offset
                menu.style.position = 'fixed';
                menu.style.left = leftValue + 'px';
                menu.style.transform = 'none';
                menu.style.top = top + 'px';
                menu.style.width = buttonWidth + 'px';
                menu.style.minWidth = buttonWidth + 'px';
                menu.style.maxWidth = buttonWidth + 'px';
                menu.style.right = 'auto';
                
            }
        }
        
        // Continuous positioning update using requestAnimationFrame
        function startPositionTracking() {
            if (positionAnimationFrame) {
                cancelAnimationFrame(positionAnimationFrame);
            }
            
            function updatePosition() {
                const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
                if (menu && menu.classList.contains('show')) {
                    positionDropdownMenu();
                    positionAnimationFrame = requestAnimationFrame(updatePosition);
                } else {
                    positionAnimationFrame = null;
                }
            }
            
            positionAnimationFrame = requestAnimationFrame(updatePosition);
        }
        
        // Add click event listener for positioning
        moreActionsBtn.addEventListener('click', function(e) {
        
            // Start continuous positioning after Bootstrap shows it
        setTimeout(() => {
                positionDropdownMenu();
                startPositionTracking();
            }, 10);
        });
        
        // Hide dropdown on scroll if it's open
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
            if (menu && menu.classList.contains('show')) {
                // Hide dropdown immediately on scroll
                const dropdown = bootstrap.Dropdown.getInstance(moreActionsBtn);
                if (dropdown) {
                    dropdown.hide();
                }
                if (positionAnimationFrame) {
                    cancelAnimationFrame(positionAnimationFrame);
                    positionAnimationFrame = null;
                }
            }
        }, { passive: true });
        
        // Update position on resize
        window.addEventListener('resize', function() {
            const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
            if (menu && menu.classList.contains('show')) {
                positionDropdownMenu();
            }
        }, { passive: true });
        
        // Listen to Bootstrap dropdown events
        moreActionsBtn.addEventListener('show.bs.dropdown', function() {
        });
        moreActionsBtn.addEventListener('shown.bs.dropdown', function() {
            // Position menu after Bootstrap animation completes and start tracking
            positionDropdownMenu();
            startPositionTracking();
        });
        moreActionsBtn.addEventListener('hide.bs.dropdown', function() {
        });
        moreActionsBtn.addEventListener('hidden.bs.dropdown', function() {
        });
    } else {
        console.warn('More Actions button NOT found!');
    }
    
    // Add hover effects to attachment cards
    const attachmentCards = document.querySelectorAll('.attachment-card');
    attachmentCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'all 0.2s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
            this.style.transform = '';
        });
    });
    
    // Initialize popover for Appointment History button
    const appointmentHistoryBtn = document.getElementById('appointmentHistoryBtn');
    
    if (appointmentHistoryBtn) {
        const patientId = appointmentHistoryBtn.getAttribute('data-patient-id');
        const appointmentId = appointmentHistoryBtn.getAttribute('data-appointment-id');
        
        // Set initial content
        const initialContent = '<div id="appointmentHistoryContent" class="appointment-history-popover-wrapper"><div class="appointment-history-header d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom"><h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Appointment History</h6><button type="button" class="btn-close appointment-history-close-btn" onclick="bootstrap.Popover.getInstance(document.getElementById(\'appointmentHistoryBtn\')).hide()" aria-label="Close"></button></div><div class="appointment-history-content"><div class="text-center py-4"><i class="bi bi-hourglass-split text-muted" style="font-size: 2rem;"></i><p class="text-muted mt-2">Loading appointment history...</p></div></div></div>';
        
        const popover = new bootstrap.Popover(appointmentHistoryBtn, {
            html: true,
            trigger: 'manual',
            placement: 'bottom',
            container: 'body',
            sanitize: false,
            content: initialContent,
            dismissible: true
        });
        
        // Handle button click to toggle popover
        appointmentHistoryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const popoverInstance = bootstrap.Popover.getInstance(appointmentHistoryBtn);
            const isShown = popoverInstance && document.querySelector('.popover[data-bs-popper]');
            
            if (popoverInstance) {
                if (isShown) {
                    popoverInstance.hide();
                } else {
                    popoverInstance.show();
                    
                    // Load appointment history after showing
                    setTimeout(() => {                        if (typeof loadAppointmentHistory === 'function') {                            loadAppointmentHistory(patientId, appointmentId);
                        } else {                        }
                    }, 100);
                }
            } else {            }
        });
        
        // Also listen to shown event as backup
        appointmentHistoryBtn.addEventListener('shown.bs.popover', function() {            setTimeout(() => {
                if (typeof loadAppointmentHistory === 'function') {
                    loadAppointmentHistory(patientId, appointmentId);
                }
            }, 100);
        });        // Close popover when clicking outside
        document.addEventListener('click', function(e) {
            const popoverInstance = bootstrap.Popover.getInstance(appointmentHistoryBtn);
            const isPopoverShown = popoverInstance && document.querySelector('.popover[data-bs-popper]');
            if (popoverInstance && isPopoverShown) {
                const popoverElement = document.querySelector('.popover');
                if (popoverElement && !appointmentHistoryBtn.contains(e.target) && !popoverElement.contains(e.target)) {
                    popoverInstance.hide();
                }
            }
        });
    } else {    }
});

// Toggle consultation note details
function toggleNoteDetails(noteId) {
    const noteElement = document.getElementById(noteId);
    const button = document.querySelector(`button[onclick="toggleNoteDetails('${noteId}')"]`);
    const icon = button.querySelector('i');
    
    if (noteElement.classList.contains('show')) {
        // Hide details
        noteElement.classList.remove('show');
        icon.className = 'bi bi-eye';
        button.title = 'Show details';
    } else {
        // Show details
        noteElement.classList.add('show');
        icon.className = 'bi bi-eye-slash';
        button.title = 'Hide details';
    }
}

function scheduleFollowUp(appointmentId) {
    // Show follow-up scheduling modal
    alert('Schedule follow-up functionality will be implemented soon');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function viewPatient(patientId) {
    // Redirect to patient profile
    window.location.href = `/doctor/patients/${patientId}`;
}

function printPrescription(appointmentId) {
    // Open prescription print view
    window.open(`/print/prescription/${appointmentId}`, '_blank');
}

function printGlassesPrescription(appointmentId) {
    // Open glasses prescription print view
    window.open(`/print/glasses/${appointmentId}`, '_blank');
}

// Mark as Completed directly (with confirmation modal)
function markAsCompleted(appointmentId) {
    // Show confirmation modal
    showCompletionConfirmModal(appointmentId);
}

function confirmMarkCompleted(appointmentId) {
    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('completionConfirmModal'));
    modal.hide();
    
    // Show loading state
    const badge = document.getElementById('appointmentStatusBadge');
    if (badge) {
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
        badge.style.pointerEvents = 'none';
    }
    
    // API call to update status to Completed
    fetch(`/api/appointments/${appointmentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            status: 'Completed',
            reason: null
        })
    })
    .then(() => {
        // Always reload page regardless of response
        window.location.reload();
    })
    .catch(() => {
        // Even on error, reload page
        window.location.reload();
    });
}

// Show completion confirmation modal
function showCompletionConfirmModal(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="completionConfirmModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-check-circle me-2"></i>Confirm Appointment Completion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="bi bi-question-circle-fill text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h6 class="mb-3">Are you sure you want to mark this appointment as completed?</h6>
                        <p class="text-muted mb-0">
                            This will update the appointment status to "completed" and cannot be undone.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success" onclick="confirmMarkCompleted(${appointmentId})">
                            <i class="bi bi-check-circle me-1"></i>Confirm Completion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('completionConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('completionConfirmModal'));
    modal.show();
    
    // Clean up modal after hide
    document.getElementById('completionConfirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Show change status modal
function showChangeStatusModal(appointmentId) {
    const currentStatus = document.getElementById('statusText').textContent.trim();
    const modalHtml = `
        <div class="modal fade" id="changeStatusModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-arrow-repeat me-2"></i>Change Appointment Status
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            <strong>Current Status:</strong> 
                            <span class="badge ${getStatusBadgeClass(currentStatus)}">${currentStatus}</span>
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Select New Status</label>
                            <section class="field menu" style="min-width: 100%;">
                                <div class="control">
                                    <select class="form-select d-none" id="newStatusSelect" required>
                                        <option value="Booked" ${currentStatus === 'Booked' ? 'selected' : ''}>Booked</option>
                                        <option value="NoShow" ${currentStatus === 'NoShow' ? 'selected' : ''}>No Show</option>
                                        <option value="Completed" ${currentStatus === 'Completed' ? 'selected' : ''}>Completed</option>
                                        <option value="Closed" ${currentStatus === 'Closed' ? 'selected' : ''}>Closed</option>
                                    </select>
                                    <button type="button" class="custom-select-toggle" aria-expanded="false">${currentStatus || 'Booked'}</button>
                                    <menu>
                                        <li data-option="Booked" tabindex="0" role="button" ${currentStatus === 'Booked' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>Booked</h3></li>
                                        <li data-option="NoShow" tabindex="0" role="button" ${currentStatus === 'NoShow' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>No Show</h3></li>
                                        <li data-option="Completed" tabindex="0" role="button" ${currentStatus === 'Completed' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>Completed</h3></li>
                                        <li data-option="Closed" tabindex="0" role="button" ${currentStatus === 'Closed' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>Closed</h3></li>
                                    </menu>
                                </div>
                            </section>
                        </div>
                        <div class="mb-3" id="statusReasonSection" style="display: none;">
                            <label class="form-label">Reason (Optional)</label>
                            <textarea class="form-control" id="statusReason" rows="3" placeholder="Enter reason for status change..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="confirmChangeStatus(${appointmentId})">
                            <i class="bi bi-check-circle me-1"></i>Change Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('changeStatusModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
    modal.show();
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
    
    // Show/hide reason section based on status
    document.getElementById('newStatusSelect').addEventListener('change', function() {
        const reasonSection = document.getElementById('statusReasonSection');
        if (this.value === 'NoShow' || this.value === 'Cancelled') {
            reasonSection.style.display = 'block';
        } else {
            reasonSection.style.display = 'none';
        }
    });
    
    // Clean up modal after hide
    document.getElementById('changeStatusModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function confirmChangeStatus(appointmentId) {
    const newStatus = document.getElementById('newStatusSelect').value;
    const reason = document.getElementById('statusReason').value.trim() || null;
    
    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('changeStatusModal'));
    modal.hide();
    
    // Show loading state
    const badge = document.getElementById('appointmentStatusBadge');
    if (badge) {
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
        badge.style.pointerEvents = 'none';
    }
    
    // API call to update status
    fetch(`/api/appointments/${appointmentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            status: newStatus,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Update status badge and header classes before reload
            updateStatusBadge(newStatus);
            updateAppointmentHeaderClasses(newStatus);
            
            // Show success message
            showNotification('Appointment status updated successfully!', 'success');
            
            // Reload after a short delay to show the update
            setTimeout(() => {
        window.location.reload();
            }, 500);
        } else {
            window.location.reload();
        }
    })
    .catch(() => {
        // Even on error, reload page
        window.location.reload();
    });
}

// Close doctor info badge
function closeDoctorInfo() {
    const infoBadge = document.getElementById('doctorInfoBadge');
    if (infoBadge) {
        infoBadge.style.animation = 'slideUp 0.3s ease-out forwards';
        setTimeout(() => {
            infoBadge.remove();
        }, 300);
    }
}

// Add slideUp animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    }
`;
document.head.appendChild(style);

// Auto-hide doctor info after 10 seconds
document.addEventListener('DOMContentLoaded', function() {
    const infoBadge = document.getElementById('doctorInfoBadge');
    if (infoBadge) {
        setTimeout(() => {
            closeDoctorInfo();
        }, 10000); // Auto-hide after 10 seconds
    }
});

// Reload attachments via Ajax
window.ATTACHMENTS_PER_PAGE = 4;
window.attachmentsCurrentPage = 1;

// On page load: if the server rendered more than the per-page limit, swap to the paginated view.
document.addEventListener('DOMContentLoaded', function () {
    const rendered = document.querySelectorAll('#attachmentsRow > .col-md-6').length;
    if (rendered > window.ATTACHMENTS_PER_PAGE) {
        reloadAttachments(1);
    }
});

function showAttachmentsLoading() {
    const card = document.querySelector('.card .card-body #attachmentsContainer');
    const wrap = card ? card.closest('.card-body') : null;
    if (!wrap) return;
    if (wrap.querySelector('.attachments-loading-overlay')) return;
    wrap.classList.add('attachments-loading');
    const overlay = document.createElement('div');
    overlay.className = 'attachments-loading-overlay';
    overlay.innerHTML = '<div class="spinner-border text-primary" role="status" aria-hidden="true"></div><span class="ms-2">Loading…</span>';
    wrap.appendChild(overlay);
}

function hideAttachmentsLoading() {
    const card = document.querySelector('.card .card-body #attachmentsContainer');
    const wrap = card ? card.closest('.card-body') : null;
    if (!wrap) return;
    wrap.classList.remove('attachments-loading');
    const overlay = wrap.querySelector('.attachments-loading-overlay');
    if (overlay) overlay.remove();
}

function reloadAttachments(page) {
    const appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
    if (!appointmentId) {
        console.error('No appointment ID found');
        return;
    }
    const requestedPage = Math.max(1, parseInt(page, 10) || window.attachmentsCurrentPage || 1);

    showAttachmentsLoading();
    fetch(`/api/appointments/${appointmentId}/attachments?page=${requestedPage}&perPage=${window.ATTACHMENTS_PER_PAGE}`, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.attachments !== undefined) {
                const container = document.getElementById('attachmentsContainer');
                if (!container) {
                    console.error('attachmentsContainer not found');
                    return;
                }

                const pag = data.pagination || { page: requestedPage, perPage: window.ATTACHMENTS_PER_PAGE, total: data.attachments.length, totalPages: 1 };
                window.attachmentsCurrentPage = pag.page;

                if (data.attachments.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4" id="emptyAttachmentsMessage">
                            <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">No images or attachments found</p>
                        </div>`;
                    renderAttachmentsPagination(pag);
                } else {
                    let html = '<div class="row" id="attachmentsRow">';
                    data.attachments.forEach(attachment => {
                        const fileExt = attachment.original_filename.split('.').pop().toLowerCase();
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExt);
                        const viewUrl = `/api/attachments/view/${attachment.id}`;
                        
                        // Determine file type and badge
                        let iconClass = 'bi-file-earmark';
                        let fileType = 'Document';
                        let badgeClass = 'bg-secondary';
                        
                        if (isImage) {
                            iconClass = 'bi-image';
                            fileType = 'Photo';
                            badgeClass = 'bg-warning text-dark';
                        } else if (fileExt === 'pdf') {
                            iconClass = 'bi-file-earmark-pdf';
                            fileType = 'PDF Document';
                            badgeClass = 'bg-danger';
                        } else if (['doc', 'docx'].includes(fileExt)) {
                            iconClass = 'bi-file-earmark-word';
                            fileType = 'Word Document';
                            badgeClass = 'bg-primary';
                        } else if (['xls', 'xlsx'].includes(fileExt)) {
                            iconClass = 'bi-file-earmark-excel';
                            fileType = 'Excel Sheet';
                            badgeClass = 'bg-success';
                        }
                        
                        // Show the full filename; CSS handles overflow via ellipsis when truly needed.
                        const displayName = attachment.original_filename;
                        const fileSize = (attachment.file_size / 1024).toFixed(1);
                        const createdDate = new Date(attachment.created_at).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        // Escape attributes for use in inline onclick (single quotes)
                        const safeName = String(attachment.original_filename || '').replace(/'/g, "\\'");
                        const safePath = String(attachment.file_path || '').replace(/'/g, "\\'");
                        const safeViewUrl = String(viewUrl || '').replace(/'/g, "\\'");
                        const descrTrunc = attachment.description
                            ? (attachment.description.length > 40
                                ? attachment.description.substring(0, 37) + '...'
                                : attachment.description)
                            : '';

                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="attachment-card p-2 rounded" data-attachment-id="${attachment.id}" style="min-height: ${isImage ? '230px' : '88px'}; display: flex; flex-direction: column;">
                                    ${isImage ? `
                                    <div class="attachment-image-wrap">
                                        <img src="${viewUrl}"
                                             alt="${attachment.original_filename}"
                                             onclick="viewAttachment(${attachment.id}, '${safePath}', '${fileExt}')"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="display:none; width:100%; height:160px; background:var(--bg); align-items:center; justify-content:center; flex-direction:column;">
                                            <i class="bi bi-image text-muted" style="font-size:2rem;"></i>
                                            <small class="text-muted">Image not available</small>
                                        </div>
                                        <div class="attachment-overlay-actions" role="group">
                                            <button type="button" class="attachment-action-btn is-edit"
                                                    onclick="editAttachmentDrawing(${attachment.id}, '${safeViewUrl}', '${safeName}')"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit (open in Drawing)" aria-label="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="attachment-action-btn is-view"
                                                    onclick="viewAttachment(${attachment.id}, '${safePath}', '${fileExt}')"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View" aria-label="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="attachment-action-btn is-download"
                                                    onclick="downloadAttachment(${attachment.id}, '${safeName}')"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download" aria-label="Download">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <button type="button" class="attachment-action-btn is-delete"
                                                    onclick="deleteAttachment(${attachment.id})"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" aria-label="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    ` : ''}

                                    <div class="d-flex align-items-center" style="min-width:0;">
                                        <i class="bi ${iconClass} text-primary me-2" style="font-size:1.1rem; flex-shrink:0;"></i>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="d-flex align-items-center justify-content-between mb-1" style="gap:.4rem;">
                                                <h6 class="mb-0 attachment-filename text-truncate" style="font-size:.8rem; line-height:1.15; min-width:0;"
                                                    title="${attachment.original_filename}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                                    ${displayName}
                                                </h6>
                                                <span class="badge ${badgeClass}" style="font-size:.6rem; flex-shrink:0; font-weight:500; border-radius:8px;">
                                                    ${fileType}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block" style="font-size:.65rem; line-height:1.15;">
                                                ${fileSize} KB · ${createdDate}
                                            </small>
                                        </div>
                                    </div>

                                    ${!isImage ? `
                                    <div class="attachment-actions-row">
                                        <button type="button" class="attachment-action-btn is-view"
                                                onclick="viewAttachment(${attachment.id}, '${safePath}', '${fileExt}')"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View" aria-label="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="attachment-action-btn is-download"
                                                onclick="downloadAttachment(${attachment.id}, '${safeName}')"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download" aria-label="Download">
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <button type="button" class="attachment-action-btn is-delete"
                                                onclick="deleteAttachment(${attachment.id})"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" aria-label="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    ` : ''}

                                    ${attachment.description ? `
                                    <p class="text-muted mb-0 small mt-1" style="font-size:.7rem; line-height:1.25;"
                                       title="${attachment.description}"
                                       data-bs-toggle="tooltip" data-bs-placement="bottom">
                                       ${descrTrunc}
                                    </p>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                    renderAttachmentsPagination(pag);

                    // Reinitialize tooltips
                    var tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            } else {
                console.error('Invalid response format:', data);
                if (data.message) {
                    showErrorMessage('Error loading attachments: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error reloading attachments:', error);
            showErrorMessage('Error reloading attachments. Please refresh the page.');
            // Fallback: reload page after 2 seconds
            setTimeout(() => location.reload(), 2000);
        })
        .finally(() => {
            hideAttachmentsLoading();
        });
}

function renderAttachmentsPagination(pag) {
    const el = document.getElementById('attachmentsPagination');
    if (!el) return;
    if (!pag || !pag.total || pag.total <= (pag.perPage || 0) || pag.totalPages <= 1) {
        el.innerHTML = '';
        return;
    }
    el.innerHTML = buildProjectPaginationHTML(pag, 'attachmentsChangePage');
}

// Project-wide pagination markup (matches patients.js renderPaginationNav).
// Renders a "X–Y of Z" info row above a Bootstrap <ul class="pagination">.
function buildProjectPaginationHTML(pag, onClickFn) {
    const current = pag.page;
    const total   = pag.totalPages;

    let startPage = Math.max(1, current - 2);
    let endPage   = Math.min(total, current + 2);
    if (current <= 3) {
        startPage = 1;
        endPage = Math.min(5, total);
    } else if (current >= total - 2) {
        startPage = Math.max(1, total - 4);
        endPage = total;
    }

    const firstShown = (current - 1) * pag.perPage + 1;
    const lastShown  = Math.min(current * pag.perPage, pag.total);

    let html = '';
    html += `<div class="pagination-info text-muted mb-2 text-center small">Showing ${firstShown}–${lastShown} of ${pag.total}</div>`;
    html += `<nav aria-label="Attachments pagination"><ul class="pagination pagination-sm justify-content-center mb-0">`;

    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="${onClickFn}(${current - 1}); return false;" aria-label="Previous">
            <i class="bi bi-chevron-left"></i>
        </a>
    </li>`;

    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${onClickFn}(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i === current ? 'active' : ''}">
            <a class="page-link" href="#" onclick="${onClickFn}(${i}); return false;">${i}</a>
        </li>`;
    }

    if (endPage < total) {
        if (endPage < total - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${onClickFn}(${total}); return false;">${total}</a></li>`;
    }

    html += `<li class="page-item ${current === total ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="${onClickFn}(${current + 1}); return false;" aria-label="Next">
            <i class="bi bi-chevron-right"></i>
        </a>
    </li>`;

    html += `</ul></nav>`;
    return html;
}

// Exposed for the onclick attribute in the pagination markup.
window.attachmentsChangePage = function (page) {
    const target = parseInt(page, 10);
    if (!Number.isFinite(target) || target < 1) return;
    if (target === window.attachmentsCurrentPage) return;
    reloadAttachments(target);
};

// ===========================================================================
// Attachment selection + bulk delete
// ===========================================================================
window.attachmentsSelectedIds = new Set();

function attachmentsRefreshSelectionUI() {
    // Wire checkboxes that may have been re-rendered by reloadAttachments().
    document.querySelectorAll('#attachmentsRow .attachment-select-checkbox').forEach(cb => {
        if (cb.dataset.wired === '1') return;
        cb.dataset.wired = '1';
        cb.addEventListener('click', (e) => {
            e.stopPropagation();
            attachmentsToggleId(parseInt(cb.dataset.id, 10), cb.checked);
        });
        // Restore checked state when re-rendering
        const id = parseInt(cb.dataset.id, 10);
        if (window.attachmentsSelectedIds.has(id)) {
            cb.checked = true;
            cb.closest('.attachment-card')?.classList.add('is-selected');
        }
    });
    attachmentsSyncSelectAllButton();
    attachmentsSyncDeleteButton();
}

function attachmentsToggleId(id, checked) {
    if (!Number.isFinite(id)) return;
    const card = document.querySelector(`#attachmentsRow .attachment-card[data-attachment-id="${id}"]`);
    if (checked) {
        window.attachmentsSelectedIds.add(id);
        card?.classList.add('is-selected');
    } else {
        window.attachmentsSelectedIds.delete(id);
        card?.classList.remove('is-selected');
    }
    attachmentsSyncSelectAllButton();
    attachmentsSyncDeleteButton();
}

function attachmentsToggleSelectAll() {
    const checkboxes = document.querySelectorAll('#attachmentsRow .attachment-select-checkbox');
    if (!checkboxes.length) return;
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => {
        const id = parseInt(cb.dataset.id, 10);
        cb.checked = !allChecked;
        attachmentsToggleId(id, !allChecked);
    });
}

function attachmentsSyncSelectAllButton() {
    const btn = document.getElementById('attachmentsSelectAllBtn');
    if (!btn) return;
    const checkboxes = document.querySelectorAll('#attachmentsRow .attachment-select-checkbox');
    const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
    btn.classList.toggle('active', allChecked);
    const icon = btn.querySelector('i');
    if (icon) icon.className = allChecked ? 'bi bi-x-square' : 'bi bi-check2-square';
    btn.title = allChecked ? 'Clear selection on this page' : 'Select all on this page';
}

function attachmentsSyncDeleteButton() {
    const btn = document.getElementById('attachmentsDeleteSelectedBtn');
    if (!btn) return;
    const count = window.attachmentsSelectedIds.size;
    btn.disabled = count === 0;
    const badge = document.getElementById('attachmentsSelectedBadge');
    if (badge) {
        if (count > 0) { badge.textContent = String(count); badge.classList.remove('d-none'); }
        else { badge.classList.add('d-none'); }
    }
}

function attachmentsConfirmDeleteSelected() {
    const ids = Array.from(window.attachmentsSelectedIds);
    if (!ids.length) return;
    openBulkDeleteConfirmModal({
        count: ids.length,
        kind: 'attachment',
        onConfirm: () => attachmentsBulkDeleteRequest(ids),
    });
}

function attachmentsBulkDeleteRequest(ids) {
    const cfg = window.APPOINTMENT_CONFIG || {};
    const appointmentId = cfg.appointmentId;
    if (!appointmentId) return Promise.reject(new Error('No appointment id'));

    return fetch('/api/attachments/bulk-delete', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ appointment_id: appointmentId, ids })
    })
    .then(r => r.json())
    .then(json => {
        if (!json || !json.success) {
            throw new Error(json && json.message ? json.message : 'Bulk delete failed');
        }
        window.attachmentsSelectedIds.clear();
        attachmentsSyncDeleteButton();
        reloadAttachments(window.attachmentsCurrentPage);
        return json;
    });
}

function attachmentsInjectCheckboxes() {
    document.querySelectorAll('#attachmentsRow .attachment-card').forEach(card => {
        if (card.querySelector('.attachment-select-checkbox')) return;
        const id = card.dataset.attachmentId;
        if (!id) return;
        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.className = 'attachment-select-checkbox';
        cb.dataset.id = id;
        cb.title = 'Select for bulk delete';

        // Pin the checkbox to the top-LEFT corner of the IMAGE thumbnail
        // when the card has one (so it sits visibly on the image itself),
        // otherwise fall back to the card's top-left corner for document
        // cards. The wrapper class moved from `.mb-2.text-center` to
        // `.attachment-image-wrap` when the card was redesigned — check
        // both so older cached pages don't lose the checkbox entirely.
        const thumb = card.querySelector(':scope > .attachment-image-wrap')
            || card.querySelector(':scope > .mb-2.text-center');
        if (thumb) {
            thumb.classList.add('attachment-thumb-host');
            thumb.appendChild(cb);
        } else {
            card.classList.add('attachment-card--no-thumb');
            card.appendChild(cb);
        }
    });
    attachmentsRefreshSelectionUI();
}

// Inject checkboxes once on first paint, then every time #attachmentsContainer
// re-renders (reloadAttachments replaces innerHTML rather than returning a promise).
document.addEventListener('DOMContentLoaded', () => {
    attachmentsInjectCheckboxes();
    const container = document.getElementById('attachmentsContainer');
    if (container && 'MutationObserver' in window) {
        new MutationObserver(() => attachmentsInjectCheckboxes())
            .observe(container, { childList: true, subtree: true });
    }
});

function reloadMedications() {
    const appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
    if (!appointmentId) {
        console.error('No appointment ID found');
        return;
    }
    
    fetch(`/api/appointments/${appointmentId}/medications`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.medications !== undefined) {
                const container = document.getElementById('medicationsContainer');
                if (!container) {
                    console.error('medicationsContainer not found');
        return;
    }
    
                if (data.medications.length === 0) {
                    container.innerHTML = `
                        <div class="text-center" id="emptyMedicationsMessage">
                            <i class="bi bi-capsule text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No medications prescribed</p>
                        </div>
                    `;
                } else {
                    let html = '';
                    data.medications.forEach(med => {
                        const priceBadge = med.drug_price ? `
                            <span class="drug-price-badge badge bg-success text-white">
                                <i class="bi bi-currency-exchange me-1"></i>
                                <span class="drug-price-value">EGP ${med.drug_price}</span>
                            </span>
                        ` : '';
                        
                        html += `
                            <div class="prescription-card p-3 mb-3" data-medication-id="${med.id}" data-drug-name="${escapeHtml(med.drug_name)}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h6 class="text-primary mb-0" onclick="showDrugPopoverFromName('${escapeHtml(med.drug_name).replace(/'/g, "\\'")}', event, 'card', ${med.id})" style="cursor: pointer;">${escapeHtml(med.drug_name)}</h6>
                                        ${priceBadge}
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" onclick="event.stopPropagation(); editMedication(${med.id}, '${escapeHtml(med.drug_name).replace(/'/g, "\\'")}', '${escapeHtml(med.notes || '').replace(/'/g, "\\'")}')" title="Edit Medication">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="event.stopPropagation(); showDrugPopoverFromName('${escapeHtml(med.drug_name).replace(/'/g, "\\'")}', event, 'card', ${med.id})" title="Replace/Alternative">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="event.stopPropagation(); deleteMedication(${med.id})" title="Delete Medication">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                ${med.notes ? `
                                    <p class="text-muted mb-0">
                                        <small>${escapeHtml(med.notes)}</small>
                                    </p>
                                ` : ''}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
    
    // Add event delegation for collapse triggers
    container.querySelectorAll('.history-collapse-trigger').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const collapseId = this.getAttribute('data-collapse-id');
            if (collapseId) {
                toggleHistoryCollapse(collapseId);
            }
        });
    });
                }
                
                // Update print button in header
                updateMedicationsPrintButton(data.medications.length > 0);
                // Update print buttons in action area
                updateActionPrintButtons();
            } else {
                console.error('Invalid response format:', data);
            }
        })
        .catch(error => {
            console.error('Error reloading medications:', error);
            showErrorMessage('Error loading medications');
        });
}

function reloadLabTests() {
    const appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
    if (!appointmentId) {
        console.error('No appointment ID found');
        return;
    }
    
    fetch(`/api/lab-tests/appointment/${appointmentId}`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.lab_tests !== undefined) {
                const container = document.getElementById('labTestsContainer');
                if (!container) {
                    console.error('labTestsContainer not found');
                    return;
                }
    
                if (data.lab_tests.length === 0) {
                    container.innerHTML = `
                        <div class="text-center">
                            <i class="bi bi-clipboard-data text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No lab tests or radiology ordered</p>
                        </div>
                    `;
                } else {
                    let html = '';
                    data.lab_tests.forEach(test => {
                        const statusBadgeClass = test.status === 'completed' ? 'success' : (test.status === 'pending' ? 'warning' : 'secondary');
                        const priorityBadgeClass = test.priority === 'urgent' ? 'danger' : (test.priority === 'high' ? 'warning' : 'primary');
                        const testIcon = test.test_type === 'radiology' ? 'camera-reels' : 'clipboard-data';
                        const testTypeCapitalized = test.test_type.charAt(0).toUpperCase() + test.test_type.slice(1);
                        const statusCapitalized = test.status.charAt(0).toUpperCase() + test.status.slice(1);
                        const priorityCapitalized = test.priority ? test.priority.charAt(0).toUpperCase() + test.priority.slice(1) : '';
                        
                        const orderedDate = test.ordered_date ? formatDate(test.ordered_date) : '';
                        const expectedDate = test.expected_date ? formatDate(test.expected_date) : '';
                        
                        const testDataJson = JSON.stringify(test).replace(/"/g, '&quot;');
                        html += `
                            <div class="prescription-card p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="text-primary mb-0">
                                        <i class="bi bi-${testIcon} me-1"></i>
                                        ${escapeHtml(test.test_name)}
                                    </h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary edit-lab-test-btn" data-test-id="${test.id}" data-test-data="${testDataJson}" title="Edit Test">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="printLabTest(${test.id})" title="Print Test">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteLabTest(${test.id})" title="Delete Test">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="mb-1">
                                    <strong>Type:</strong> ${escapeHtml(testTypeCapitalized)}<br>
                                    <strong>Status:</strong> 
                                    <span class="badge bg-${statusBadgeClass}">
                                        ${escapeHtml(statusCapitalized)}
                                    </span><br>
                                    ${test.priority ? `
                                        <strong>Priority:</strong> 
                                        <span class="badge bg-${priorityBadgeClass}">
                                            ${escapeHtml(priorityCapitalized)}
                                        </span><br>
                                    ` : ''}
                                    ${orderedDate ? `
                                        <strong>Ordered Date:</strong> ${orderedDate}<br>
                                    ` : ''}
                                    ${expectedDate ? `
                                        <strong>Expected Date:</strong> ${expectedDate}
                                    ` : ''}
                                </p>
                                ${test.notes ? `
                                    <p class="text-muted mb-1">
                                        <small><strong>Notes:</strong> ${escapeHtml(test.notes)}</small>
                                    </p>
                                ` : ''}
                                ${test.results ? `
                                    <p class="text-success mb-0">
                                        <small><strong>Results:</strong> ${escapeHtml(test.results)}</small>
                                    </p>
                                ` : ''}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                    
                    // Attach event listeners for edit buttons
                    container.querySelectorAll('.edit-lab-test-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const testId = this.getAttribute('data-test-id');
                            const testDataJson = this.getAttribute('data-test-data');
                            try {
                                const testData = JSON.parse(testDataJson);
                                editLabTest(testId, testData);
                            } catch (e) {
                                console.error('Error parsing test data:', e);
                                showErrorMessage('Error loading test data');
                            }
                        });
                    });
                }
                
                // Update print button visibility
                const printBtn = document.querySelector('[onclick*="printLabTests"]');
                if (printBtn) {
                    printBtn.style.display = data.lab_tests.length > 0 ? '' : 'none';
                }
            } else {
                console.error('Invalid response format:', data);
            }
        })
        .catch(error => {
            console.error('Error reloading lab tests:', error);
            showErrorMessage('Error loading lab tests');
        });
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

function reloadGlasses() {
    const appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
    if (!appointmentId) {
        console.error('No appointment ID found');
        return;
    }
    
    fetch(`/api/appointments/${appointmentId}/glasses`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.glasses !== undefined) {
                const container = document.getElementById('glassesContainer');
                if (!container) {
                    console.error('glassesContainer not found');
                    return;
                }
                
                if (data.glasses.length === 0) {
                    container.innerHTML = `
                        <div class="text-center" id="emptyGlassesMessage">
                            <i class="bi bi-eyeglasses text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No glasses prescription</p>
                        </div>
                    `;
        } else {
                    let html = '';
                    data.glasses.forEach(glass => {
                        const glassData = JSON.stringify(glass).replace(/"/g, '&quot;');
                        html += `
                            <div class="prescription-card p-3 mb-3" data-glasses-id="${glass.id}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="text-success mb-0">
                                        <i class="bi bi-eyeglasses me-1"></i>
                                        ${escapeHtml((glass.lens_type || 'Single Vision').charAt(0).toUpperCase() + (glass.lens_type || 'Single Vision').slice(1))}
                                    </h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" onclick="editGlassesPrescription(${glass.id}, ${glassData})" title="Edit Glasses">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteGlassesPrescription(${glass.id})" title="Delete Glasses">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Distance Vision -->
                                <div class="mb-3">
                                    <h6 class="text-success"><i class="bi bi-eye me-1"></i>Distance Vision</h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h6 class="text-primary">Right Eye (OD)</h6>
                                            <p class="mb-1">
                                                SPH: ${escapeHtml(glass.distance_sphere_r || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.distance_cylinder_r || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.distance_axis_r || 'N/A')}
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <p class="mb-1">
                                                SPH: ${escapeHtml(glass.distance_sphere_l || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.distance_cylinder_l || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.distance_axis_l || 'N/A')}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                ${(glass.near_sphere_r || glass.near_sphere_l || glass.near_cylinder_r || glass.near_cylinder_l) ? `
                                <!-- Near Vision -->
                                <div class="mb-3">
                                    <h6 class="text-info"><i class="bi bi-book me-1"></i>Near Vision</h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h6 class="text-primary">Right Eye (OD)</h6>
                                            <p class="mb-1">
                                                SPH: ${escapeHtml(glass.near_sphere_r || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.near_cylinder_r || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.near_axis_r || 'N/A')}
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <p class="mb-1">
                                                SPH: ${escapeHtml(glass.near_sphere_l || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.near_cylinder_l || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.near_axis_l || 'N/A')}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                                ${(glass.PD_DISTANCE || glass.PD_NEAR) ? `
                                    <div class="text-center mt-2">
                                        ${glass.PD_DISTANCE ? `<strong>PD Distance:</strong> ${escapeHtml(glass.PD_DISTANCE)}mm` : ''}
                                        ${glass.PD_DISTANCE && glass.PD_NEAR ? ' | ' : ''}
                                        ${glass.PD_NEAR ? `<strong>PD Near:</strong> ${escapeHtml(glass.PD_NEAR)}mm` : ''}
                                    </div>
                                ` : ''}
                                ${glass.comments ? `
                                    <p class="text-muted mt-2 mb-0">
                                        <small>${escapeHtml(glass.comments)}</small>
                                    </p>
                                ` : ''}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                    
    
    // Add event delegation for collapse triggers
    container.querySelectorAll('.history-collapse-trigger').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const collapseId = this.getAttribute('data-collapse-id');
            if (collapseId) {
                toggleHistoryCollapse(collapseId);
            }
        });
    });
                }
                
                // Update print button in header
                updateGlassesPrintButton(data.glasses.length > 0);
                // Update print buttons in action area
                updateActionPrintButtons();
            } else {
                console.error('Invalid response format:', data);
        }
    })
    .catch(error => {
            console.error('Error reloading glasses:', error);
            showErrorMessage('Error loading glasses');
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show reschedule toast notification with red styling
function showRescheduleToast(message, date, time) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastId = 'toast-reschedule-' + Date.now();
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 350px;">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-x me-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Appointment rescheduled</strong><br>
                            <small>${escapeHtml(message)}</small>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">${escapeHtml(date)} - ${escapeHtml(time)}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: false // Don't auto-hide, let user dismiss manually
    });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.getElementById('toastContainer');
    if (container) return container;
    
    const newContainer = document.createElement('div');
    newContainer.id = 'toastContainer';
    newContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    newContainer.style.zIndex = '9999';
    document.body.appendChild(newContainer);
    return newContainer;
}

// Show follow-up toast notification
function showFollowupToast(patientName, date, time) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastId = 'toast-followup-' + Date.now();
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 350px;">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-check me-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Follow-up appointment scheduled</strong><br>
                            <small>Follow-up appointment for (${escapeHtml(patientName)})</small>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">${escapeHtml(date)} - ${escapeHtml(time)}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000 // Auto-hide after 5 seconds
    });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Set current patient info for alert modal
window.currentPatientInfo = {
    id: window.APPOINTMENT_CONFIG.patientId,
    first_name: window.APPOINTMENT_CONFIG.patientFirstName,
    last_name: window.APPOINTMENT_CONFIG.patientLastName,
    phone: window.APPOINTMENT_CONFIG.patientPhone,
    age: window.APPOINTMENT_CONFIG.patientAge
};

    
// Load appointment history for popover
function loadAppointmentHistory(patientId, excludeAppointmentId) {
    const contentDiv = document.getElementById("appointmentHistoryContent");
    if (!contentDiv) {
        return;
    }
    
    contentDiv.innerHTML = '<div class="text-center py-4"><i class="bi bi-hourglass-split text-muted" style="font-size: 2rem;"></i><p class="text-muted mt-2">Loading appointment history...</p></div>';
    fetch(`/api/patients/${patientId}/appointments/history?exclude=${excludeAppointmentId}`, {
        credentials: "same-origin",
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.data && data.data.length > 0) {
            renderAppointmentHistory(data.data, contentDiv);
        } else {
            contentDiv.innerHTML = '<div class="text-center py-4"><i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i><p class="text-muted mt-2">No other appointments found</p></div>';
        }
    })
    .catch(error => {
        contentDiv.innerHTML = '<div class="text-center py-4"><i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i><p class="text-danger mt-2">Error loading appointment history</p></div>';
    });
}

function renderAppointmentHistory(appointments, container) {
    let html = '<div class="appointment-history-popover-wrapper">';
    html += '<div class="appointment-history-header d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">';
    html += '<h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Appointment History</h6>';
    html += '<div class="d-flex align-items-center gap-2">';
    html += '<span class="badge bg-primary">' + appointments.length + '</span>';
    html += '<button type="button" class="btn-close appointment-history-close-btn" onclick="bootstrap.Popover.getInstance(document.getElementById(\'appointmentHistoryBtn\')).hide()" aria-label="Close"></button>';
    html += '</div>';
    html += '</div>';
    html += '<div class="appointment-history-content" style="max-height: 500px; overflow-y: auto;">';
    
    appointments.forEach((appointment, index) => {
        const statusColor = appointment.status === "Completed" ? "success" : (appointment.status === "Cancelled" ? "danger" : (appointment.status === "InProgress" ? "warning" : "primary"));
        const isFollowup = appointment.is_followup === true || appointment.is_followup === 1;
        const collapseId = "historyCollapse" + appointment.id;
        
        html += '<div class="appointment-history-item mb-3">';
        html += '<div class="card border-' + statusColor + ' border-start border-3">';
        html += '<div class="card-header appointment-header collapsed history-collapse-trigger" data-collapse-id="' + collapseId + '" style="cursor: pointer;">';
        html += '<div class="d-flex justify-content-between align-items-start w-100">';
        html += '<div class="flex-grow-1">';
        html += '<h6 class="mb-1">';
        html += '<a href="/doctor/appointments/' + appointment.id + '" class="text-decoration-none" onclick="event.stopPropagation();">';
        html += 'Appointment #' + appointment.id;
        html += '</a>';
        if (isFollowup) {
            html += '<span class="badge bg-info ms-2"><i class="bi bi-arrow-return-right me-1"></i>Follow-up</span>';
        }
        html += '<span class="badge bg-' + statusColor + ' ms-2">' + (appointment.status || "N/A") + '</span>';
        html += '</h6>';
        html += '<div class="d-flex flex-wrap gap-2 mb-2">';
        html += '<small class="text-muted"><i class="bi bi-calendar3 me-1"></i>' + formatDate(appointment.date) + '</small>';
        html += '<small class="text-muted"><i class="bi bi-clock me-1"></i>' + formatTime(appointment.start_time) + '</small>';
        if (appointment.doctor_display_name || appointment.doctor_name) {
            html += '<small class="text-muted"><i class="bi bi-person-badge me-1"></i>' + escapeHtml(appointment.doctor_display_name || appointment.doctor_name) + '</small>';
        }
        if (appointment.visit_type) {
            html += '<small class="text-muted"><i class="bi bi-tag me-1"></i>' + escapeHtml(appointment.visit_type) + '</small>';
        }
        html += '</div>';
        html += '</div>';
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<a href="/doctor/appointments/' + appointment.id + '" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">';
        html += '<i class="bi bi-eye me-1"></i>View';
        html += '</a>';
        html += '<i class="bi bi-chevron-down collapse-icon"></i>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        html += '<div id="' + collapseId + '" class="card-body collapse">';
        
        if (appointment.consultation_note) {
            html += '<div class="mb-3 p-3 bg-outline-primary rounded">';
            html += '<h6 class="text-primary mb-2"><i class="bi bi-clipboard-pulse me-2"></i>Consultation Notes</h6>';
            if (appointment.consultation_note.chief_complaint) {
                html += '<p class="mb-2"><strong>Chief Complaint:</strong> ' + escapeHtml(appointment.consultation_note.chief_complaint) + '</p>';
            }
            if (appointment.consultation_note.diagnosis) {
                html += '<p class="mb-2"><strong>Diagnosis:</strong> <span class="badge bg-danger">' + escapeHtml(appointment.consultation_note.diagnosis) + '</span></p>';
            }
            if (appointment.consultation_note.plan) {
                html += '<p class="mb-0"><strong>Plan:</strong> ' + escapeHtml(appointment.consultation_note.plan).replace(/\n/g, "<br>") + '</p>';
            }
            html += '</div>';
        }
        
        if (appointment.medications && appointment.medications.length > 0) {
            html += '<div class="mb-3">';
            html += '<h6 class="text-success mb-2"><i class="bi bi-capsule me-2"></i>Medications <span class="badge bg-success ms-2">' + appointment.medications.length + '</span></h6>';
            appointment.medications.forEach(med => {
                html += '<div class="card border-success border-start border-3 mb-2">';
                html += '<div class="card-body p-2">';
                html += '<h6 class="card-title mb-1 text-success">' + escapeHtml(med.drug_name) + '</h6>';
                if (med.notes) {
                    html += '<p class="card-text small mb-0 text-muted">' + escapeHtml(med.notes) + '</p>';
                }
                html += '</div></div>';
            });
            html += '</div>';
        }
        
        if (appointment.glasses && appointment.glasses.length > 0) {
            html += '<div class="mb-3">';
            html += '<h6 class="text-info mb-2"><i class="bi bi-eyeglasses me-2"></i>Glasses <span class="badge bg-info ms-2">' + appointment.glasses.length + '</span></h6>';
            appointment.glasses.forEach(glass => {
                html += '<div class="card border-info border-start border-3 mb-2">';
                html += '<div class="card-body p-2">';
                html += '<h6 class="card-title mb-1 text-info">Glasses Prescription</h6>';
                if (glass.right_sphere || glass.left_sphere) {
                    html += '<p class="card-text small mb-1 text-muted">';
                    if (glass.right_sphere) {
                        html += '<strong>OD:</strong> ' + escapeHtml(glass.right_sphere);
                        if (glass.right_cylinder) html += ' / ' + escapeHtml(glass.right_cylinder);
                        if (glass.right_axis) html += ' x ' + escapeHtml(glass.right_axis);
                    }
                    if (glass.left_sphere) {
                        if (glass.right_sphere) html += ' | ';
                        html += '<strong>OS:</strong> ' + escapeHtml(glass.left_sphere);
                        if (glass.left_cylinder) html += ' / ' + escapeHtml(glass.left_cylinder);
                        if (glass.left_axis) html += ' x ' + escapeHtml(glass.left_axis);
                    }
                    html += '</p>';
                }
                if (glass.notes) {
                    html += '<p class="card-text small mb-0 text-muted">' + escapeHtml(glass.notes) + '</p>';
                }
                html += '</div></div>';
            });
            html += '</div>';
        }
        
        if (appointment.attachments && appointment.attachments.length > 0) {
            html += '<div class="mb-3">';
            html += '<h6 class="text-warning mb-2"><i class="bi bi-paperclip me-2"></i>Attachments <span class="badge bg-warning ms-2">' + appointment.attachments.length + '</span></h6>';
            html += '<div class="row g-2">';
            appointment.attachments.forEach(attachment => {
                const isImage = attachment.mime_type && attachment.mime_type.startsWith('image/');
                const viewUrl = '/api/attachments/view/' + attachment.id;
                const downloadUrl = '/api/attachments/download/' + attachment.id;
                
                if (isImage) {
                    html += '<div class="col-6 col-md-4">';
                    html += '<div class="card border-warning border-start border-3 mb-2">';
                    html += '<img src="' + viewUrl + '" class="card-img-top" style="height: 100px; object-fit: cover; cursor: pointer;" alt="' + escapeHtml(attachment.original_filename || attachment.filename) + '" onclick="event.stopPropagation(); showImageModal(\'' + viewUrl + '\', ' + attachment.id + ', \'' + escapeHtml(attachment.original_filename || attachment.filename) + '\');">';
                    html += '<div class="card-body p-2">';
                    html += '<p class="card-text small mb-0 text-muted" style="font-size: 0.7rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + escapeHtml(attachment.original_filename || attachment.filename) + '">';
                    html += escapeHtml(attachment.original_filename || attachment.filename);
                    html += '</p>';
                    if (attachment.description) {
                        html += '<p class="card-text small mb-0 text-muted" style="font-size: 0.65rem;">' + escapeHtml(attachment.description) + '</p>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                } else {
                    html += '<div class="col-12">';
                    html += '<div class="card border-warning border-start border-3 mb-2">';
                    html += '<div class="card-body p-2">';
                    html += '<div class="d-flex align-items-center justify-content-between">';
                    html += '<div class="flex-grow-1">';
                    html += '<h6 class="card-title mb-1 text-warning" style="font-size: 0.85rem;">';
                    html += '<i class="bi bi-file-earmark me-1"></i>' + escapeHtml(attachment.original_filename || attachment.filename);
                    html += '</h6>';
                    if (attachment.description) {
                        html += '<p class="card-text small mb-0 text-muted" style="font-size: 0.7rem;">' + escapeHtml(attachment.description) + '</p>';
                    }
                    html += '</div>';
                    html += '<a href="' + downloadUrl + '" class="btn btn-sm btn-outline-warning" onclick="event.stopPropagation();" download>';
                    html += '<i class="bi bi-download"></i>';
                    html += '</a>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                }
            });
            html += '</div>';
            html += '</div>';
        }
        
        html += '</div>'; // Close card-body
        html += '</div>'; // Close card
        html += '</div>'; // Close appointment-history-item
    });
    
    html += '</div>'; // Close appointment-history-content
    html += '</div>'; // Close appointment-history-popover-wrapper
    
    container.innerHTML = html;
    
    // Add event delegation for collapse triggers
    container.querySelectorAll('.history-collapse-trigger').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const collapseId = this.getAttribute('data-collapse-id');
            if (collapseId) {
                toggleHistoryCollapse(collapseId);
            }
        });
    });
}

// ============================================
// Unified Clinical Dashboard Popover (from patient.js)
// ============================================

/**
 * Show Unified Clinical Dashboard Popover
 * This function is shared between patient.js and appointment.js
 */
function showUnifiedClinicalDashboardPopover(patientId) {
    // Close any existing popover
    const existingPopover = document.getElementById('unifiedClinicalDashboardPopover');
    if (existingPopover) {
        existingPopover.remove();
    }
    
    // Create popover container
    const popover = document.createElement('div');
    popover.id = 'unifiedClinicalDashboardPopover';
    popover.className = 'unified-clinical-dashboard-popover';
    
    // Create backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'unified-clinical-dashboard-popover-backdrop';
    backdrop.onclick = () => closeUnifiedClinicalDashboardPopover();
    
    // Create popover content
    const content = document.createElement('div');
    content.className = 'unified-clinical-dashboard-popover-content';
    
    // Create header
    const header = document.createElement('div');
    header.className = 'unified-clinical-dashboard-popover-header';
    header.innerHTML = `
        <h3><i class="bi bi-clipboard-pulse me-2"></i>Unified Clinical Dashboard</h3>
        <button class="btn-close-popover" onclick="closeUnifiedClinicalDashboardPopover()" aria-label="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    
    // Create body
    const body = document.createElement('div');
    body.className = 'unified-clinical-dashboard-popover-body';
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="text-muted mt-2">Loading clinical snapshot...</p></div>';
    
    content.appendChild(header);
    content.appendChild(body);
    popover.appendChild(backdrop);
    popover.appendChild(content);
    
    document.body.appendChild(popover);
    
    // Center the popover
    centerUnifiedClinicalDashboardPopover(content);
    
    // Fetch clinical dashboard data
    fetch(`/api/clinical-dashboard/snapshot?patient_id=${patientId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.data) {
            renderClinicalDashboardInPopover(data.data, body);
        } else {
            body.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${data.error || 'No clinical data available'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading clinical dashboard:', error);
        body.innerHTML = `
            <div class="alert alert-danger text-center">
                <i class="bi bi-exclamation-circle me-2"></i>
                Error loading clinical data
            </div>
        `;
    });
}

/**
 * Close Unified Clinical Dashboard Popover
 */
function closeUnifiedClinicalDashboardPopover() {
    const popover = document.getElementById('unifiedClinicalDashboardPopover');
    if (popover) {
        popover.remove();
    }
}

/**
 * Center popover on screen
 */
function centerUnifiedClinicalDashboardPopover(content) {
    content.style.position = 'fixed';
    content.style.left = '50%';
    content.style.top = '50%';
    content.style.transform = 'translate(-50%, -50%)';
    content.style.maxWidth = '90vw';
    content.style.maxHeight = '90vh';
    content.style.overflow = 'auto';
}

/**
 * Render clinical dashboard data in popover
 */
function renderClinicalDashboardInPopover(data, container) {
    const snapshot = data.snapshot || {};
    
    let html = '';
    
    // Clinical Snapshot Section
    html += `<div class="mb-4">
        <h6 class="mb-3"><i class="bi bi-clipboard-data me-2"></i>Clinical Snapshot</h6>
        <div class="row g-3">`;
    
    // IOP Status
    if (snapshot.iop) {
        const iop = snapshot.iop;
        const badgeClass = iop.status === 'warning' ? 'bg-warning text-dark' : (iop.status === 'critical' ? 'bg-danger' : 'bg-success');
        const iopValue = iop.value !== null && iop.value !== undefined ? 
            `${iop.value} mmHg${iop.target !== null && iop.target !== undefined ? ` (Target: ${iop.target} mmHg)` : ''}` : 
            '--';
        html += `<div class="col-md-6 col-lg-3">
            <div class="clinical-indicator-card" ${iop.appointment_id ? `onclick="window.location.href='/doctor/appointments/${iop.appointment_id}'" style="cursor: pointer;"` : ''}>
                <div class="clinical-indicator-header">
                    <i class="bi bi-eyedropper me-2"></i>
                    <span>IOP Status</span>
                </div>
                <div class="clinical-indicator-value">${iopValue}</div>
                <div class="clinical-indicator-status">
                    <span class="badge ${badgeClass}">${iop.message || 'Normal'}</span>
                </div>
            </div>
        </div>`;
    }
    
    // Visual Acuity
    if (snapshot.visual_acuity) {
        const va = snapshot.visual_acuity;
        const trendIcon = va.trend === '↑' ? '↑' : (va.trend === '↓' ? '↓' : '→');
        const trendClass = va.trend === '↑' ? 'text-danger' : (va.trend === '↓' ? 'text-success' : 'text-muted');
        const displayValue = va.last && va.last.length > 30 ? va.last.substring(0, 30) + '...' : (va.last || '--');
        html += `<div class="col-md-6 col-lg-3">
            <div class="clinical-indicator-card" ${va.appointment_id ? `onclick="window.location.href='/doctor/appointments/${va.appointment_id}'" style="cursor: pointer;"` : ''}>
                <div class="clinical-indicator-header">
                    <i class="bi bi-eye me-2"></i>
                    <span>Visual Acuity</span>
                </div>
                <div class="clinical-indicator-value">${displayValue}</div>
                <div class="clinical-indicator-trend">
                    <span class="trend-indicator ${trendClass}">${trendIcon}</span>
                    <span class="trend-text">${va.message || 'Stable'}</span>
                </div>
            </div>
        </div>`;
    }
    
    // Cataract Status
    if (snapshot.cataract) {
        const cataract = snapshot.cataract;
        let badgeClass = 'bg-info';
        if (cataract.status === 'surgery_recommended') {
            badgeClass = 'bg-danger';
        } else if (cataract.status === 'consider_surgery') {
            badgeClass = 'bg-warning text-dark';
        }
        html += `<div class="col-md-6 col-lg-3">
            <div class="clinical-indicator-card" ${cataract.appointment_id ? `onclick="window.location.href='/doctor/appointments/${cataract.appointment_id}'" style="cursor: pointer;"` : ''}>
                <div class="clinical-indicator-header">
                    <i class="bi bi-scissors me-2"></i>
                    <span>Cataract Status</span>
                </div>
                <div class="clinical-indicator-value">${cataract.readiness || '--'}</div>
                <div class="clinical-indicator-status">
                    <span class="badge ${badgeClass}">${cataract.message || 'Monitor'}</span>
                </div>
            </div>
        </div>`;
    }
    
    // Dry Eye Status
    if (snapshot.dry_eye) {
        const dryEye = snapshot.dry_eye;
        const trendText = dryEye.trend === 'improving' ? 'Improving' : 
                         (dryEye.trend === 'worsening' ? 'Worsening' : 'Stable');
        const trendClass = dryEye.trend === 'worsening' ? 'text-danger' : 
                          (dryEye.trend === 'improving' ? 'text-success' : 'text-muted');
        const trendIcon = dryEye.trend === 'worsening' ? '↑' : 
                         (dryEye.trend === 'improving' ? '↓' : '→');
        const dryEyeValue = dryEye.osdi_score !== null && dryEye.osdi_score !== undefined ? 
            `OSDI: ${dryEye.osdi_score}${dryEye.severity ? ` (${dryEye.severity})` : ''}` : 
            '--';
        html += `<div class="col-md-6 col-lg-3">
            <div class="clinical-indicator-card" ${dryEye.appointment_id ? `onclick="window.location.href='/doctor/appointments/${dryEye.appointment_id}'" style="cursor: pointer;"` : ''}>
                <div class="clinical-indicator-header">
                    <i class="bi bi-droplet me-2"></i>
                    <span>Dry Eye Status</span>
                </div>
                <div class="clinical-indicator-value">${dryEyeValue}</div>
                <div class="clinical-indicator-trend">
                    <span class="trend-indicator ${trendClass}">${trendIcon}</span>
                    <span class="trend-text">${trendText}</span>
                </div>
            </div>
        </div>`;
    }
    
    html += `</div></div>`;
    
    // Active Clinical Alerts
    if (data.alerts && data.alerts.length > 0) {
        html += `<div class="mb-4">
            <h6 class="mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Active Clinical Alerts</h6>
            <div class="list-group">`;
        data.alerts.forEach(alert => {
            const alertClass = alert.severity === 'critical' ? 'list-group-item-danger' : 
                            (alert.severity === 'warning' ? 'list-group-item-warning' : 'list-group-item-info');
            const clickHandler = alert.appointment_id ? 
                `onclick="window.location.href='/doctor/appointments/${alert.appointment_id}'" style="cursor: pointer;"` : '';
            html += `<div class="list-group-item ${alertClass}" ${clickHandler}>
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${escapeHtmlForPopover(alert.message)}
            </div>`;
        });
        html += `</div></div>`;
    }
    
    // Mini Trends Overview
    if (snapshot.macular_thickness || snapshot.iop || snapshot.visual_acuity) {
        html += `<div class="mb-4">
            <h6 class="mb-3"><i class="bi bi-graph-up me-2"></i>Mini Trends Overview</h6>
            <div class="row g-3">`;
        
        if (snapshot.iop && snapshot.iop.value !== null) {
            const status = snapshot.iop.status === 'warning' ? '⚠️ Above target' : '✓ Within target';
            html += `<div class="col-md-4">
                <div class="mini-trend-card">
                    <div class="mini-trend-label">IOP Trend</div>
                    <div class="mini-trend-chart">
                        <div class="mini-trend-status">${status}</div>
                    </div>
                </div>
            </div>`;
        }
        
        if (snapshot.visual_acuity && snapshot.visual_acuity.trend) {
            const trendIcon = snapshot.visual_acuity.trend === '↑' ? '↑ Worsening' : 
                            (snapshot.visual_acuity.trend === '↓' ? '↓ Improving' : '→ Stable');
            html += `<div class="col-md-4">
                <div class="mini-trend-card">
                    <div class="mini-trend-label">Visual Acuity Trend</div>
                    <div class="mini-trend-chart">
                        <div class="mini-trend-status">${trendIcon}</div>
                    </div>
                </div>
            </div>`;
        }
        
        if (snapshot.macular_thickness && snapshot.macular_thickness.latest !== null) {
            const trendText = snapshot.macular_thickness.trend === 'worsening' ? '⚠️ Worsening' :
                            (snapshot.macular_thickness.trend === 'improving' ? '↓ Improving' : '→ Stable');
            html += `<div class="col-md-4">
                <div class="mini-trend-card">
                    <div class="mini-trend-label">Macular Thickness Trend</div>
                    <div class="mini-trend-chart">
                        <div class="mini-trend-status">${trendText}</div>
                    </div>
                </div>
            </div>`;
        }
        
        html += `</div></div>`;
    }
    
    // Clinical Summary
    if (data.summary) {
        html += `<div class="mb-0">
            <h6 class="mb-3"><i class="bi bi-file-text me-2"></i>Clinical Summary</h6>
            <div class="clinical-summary-box">
                <p class="mb-0">${escapeHtmlForPopover(data.summary)}</p>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyClinicalSummaryFromPopover()">
                    <i class="bi bi-clipboard me-1"></i>Copy to Clipboard
                </button>
            </div>
        </div>`;
    }
    
    container.innerHTML = html;
}

/**
 * Copy clinical summary from popover
 */
function copyClinicalSummaryFromPopover() {
    const summaryBox = document.querySelector('#unifiedClinicalDashboardPopover .clinical-summary-box p');
    if (!summaryBox) return;
    
    const summaryText = summaryBox.textContent;
    
    navigator.clipboard.writeText(summaryText).then(() => {
        const btn = event.target.closest('button');
        if (btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtmlForPopover(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleHistoryCollapse(collapseId) {
    const collapseElement = document.getElementById(collapseId);
    if (collapseElement) {
        const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: true });
    }
}

function formatDate(dateStr) {
    if (!dateStr) return "N/A";
    const date = new Date(dateStr + "T00:00:00");
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}

function formatTime(timeStr) {
    if (!timeStr) return "N/A";
    const [hours, minutes] = timeStr.split(":");
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? "PM" : "AM";
    const displayHour = hour % 12 || 12;
    return displayHour + ":" + minutes + " " + ampm;
}

// Make all modals draggable
function makeModalDraggable(modalElement) {
    const modalDialog = modalElement.querySelector('.modal-dialog');
    if (!modalDialog) return;
    
    let isDragging = false;
    let currentX;
    let currentY;
    let initialX;
    let initialY;
    let xOffset = 0;
    let yOffset = 0;
    
    // Reset position when modal is shown
    modalElement.addEventListener('show.bs.modal', function() {
        xOffset = 0;
        yOffset = 0;
        modalDialog.style.transform = 'translate(0px, 0px)';
        modalDialog.style.transition = 'none';
    });
    
    // Make modal header draggable
    const modalHeader = modalElement.querySelector('.modal-header');
    if (modalHeader) {
        modalHeader.style.cursor = 'move';
        
        modalHeader.addEventListener('mousedown', dragStart);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', dragEnd);
    }
    
    function dragStart(e) {
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            return; // Don't drag if clicking on buttons
        }
        
        initialX = e.clientX - xOffset;
        initialY = e.clientY - yOffset;
        
        if (e.target === modalHeader || modalHeader.contains(e.target)) {
            isDragging = true;
            modalDialog.style.transition = 'none';
        }
    }
    
    function drag(e) {
        if (isDragging) {
            e.preventDefault();
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            
            xOffset = currentX;
            yOffset = currentY;
            
            modalDialog.style.transform = `translate(${currentX}px, ${currentY}px)`;
        }
    }
    
    function dragEnd(e) {
        initialX = currentX;
        initialY = currentY;
        isDragging = false;
    }
}

// Initialize draggable for all existing modals
document.addEventListener('DOMContentLoaded', function() {
    // Make all existing modals draggable
    document.querySelectorAll('.modal').forEach(function(modal) {
        makeModalDraggable(modal);
    });
    
    // Watch for dynamically created modals
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    if (node.classList && node.classList.contains('modal')) {
                        makeModalDraggable(node);
                    }
                    // Also check for modals inside added nodes
                    const modals = node.querySelectorAll ? node.querySelectorAll('.modal') : [];
                    modals.forEach(function(modal) {
                        makeModalDraggable(modal);
                    });
                }
            });
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// Drug Popover Functions
let currentDrugPopover = null;
let currentDrugPopoverOverlay = null;


// Helper to render alternatives/similar drugs
function renderPopoverAlternativesList(drugs, type, context, currentPrescriptionId) {
    if (!drugs || drugs.length === 0) return '';
    
    return drugs.map(drug => {
        let actionButtons = '';
        const drugNameEscaped = escapeHtml(drug.drug_name).replace(/'/g, "\\'");
        
        if (context === 'modal') {
            actionButtons = `
                <button class="btn btn-sm btn-primary" onclick="selectDrugFromPopover('${drugNameEscaped}')">
                    <i class="bi bi-check-lg me-1"></i>Select
                </button>
            `;
        } else if (context === 'card') {
            actionButtons = `
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-warning" onclick="replaceDrugFromPopover(${currentPrescriptionId}, '${drugNameEscaped}')" title="Replace current drug with this one">
                        <i class="bi bi-arrow-repeat me-1"></i>Replace
                    </button>
                    <button class="btn btn-sm btn-success" onclick="addDrugFromPopover('${drugNameEscaped}')" title="Add this drug as a new prescription">
                        <i class="bi bi-plus-lg me-1"></i>Add
                    </button>
                </div>
            `;
        }
        
        return `
            <div class="forum-drug-popover-item" style="display: block; margin-bottom: 0.5rem; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="fw-bold">${escapeHtml(drug.drug_name)}</div>
                        <small class="text-muted d-block">${escapeHtml(drug.Company || '')}</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-success small fw-medium">
                        ${drug.price ? 'EGP ' + escapeHtml(drug.price) : ''}
                    </div>
                    ${actionButtons}
                </div>
            </div>
        `;
    }).join('');
}

// Action Handlers
function selectDrugFromPopover(drugName) {
    const input = document.getElementById('drugNameInput');
    const suggestions = document.getElementById('drugSuggestions');
    
    if (input) {
        input.value = drugName;
        // Trigger input event to update label float if needed
        input.dispatchEvent(new Event('input'));
    } else {
        console.error('drugNameInput not found');
        // If we are in this state, try to open the modal first? 
        // Or show error message
        showErrorMessage('Error: Could not select drug (input not found)');
    }
    
    if (suggestions) {
        suggestions.style.display = 'none';
    }
    
    closeDrugPopover();
}

/**
 * Show a custom confirmation modal
 * @param {string} title - Modal title
 * @param {string} message - Modal message (HTML supported)
 * @param {Function} onConfirm - Callback function when confirmed
 */
/**
 * Show a custom confirmation modal
 * @param {string} title - Modal title
 * @param {string} message - Modal message (HTML supported)
 * @param {Function} onConfirm - Callback function when confirmed
 * @param {Function} [onCancel] - Optional callback function when cancelled
 */
function showConfirmationModal(title, message, onConfirm, onCancel) {
    // Check if modal container exists
    let modalEl = document.getElementById('customConfirmationModal');
    
    // Create modal if it doesn't exist
    if (!modalEl) {
        const modalHtml = `
            <div class="modal fade" id="customConfirmationModal" tabindex="-1" aria-hidden="true" style="z-index: 10600;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customConfirmationModalLabel">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="customConfirmationModalMessage"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="customConfirmationModalCancelBtn" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="customConfirmationModalConfirmBtn">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modalEl = document.getElementById('customConfirmationModal');
    }
    
    // Set content
    document.getElementById('customConfirmationModalLabel').textContent = title;
    document.getElementById('customConfirmationModalMessage').innerHTML = message;
    
    // Setup confirm button
    const confirmBtn = document.getElementById('customConfirmationModalConfirmBtn');
    
    // Clean up old listeners by cloning
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Setup cancel button/cleanup listeners
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    // Helper to handle close/cancel
    const handleCancel = () => {
        if (typeof onCancel === 'function') {
            onCancel();
        }
    };

    // We can't easily replace listeners on the X button or Cancel button if they are just data-bs-dismiss
    // But we can listen for the modal hidden event.
    // However, the hidden event fires on confirm too.
    // So we'll use a flag.
    let confirmed = false;

    newConfirmBtn.addEventListener('click', () => {
        confirmed = true;
        modalInstance.hide();
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });

    // Listen for hidden event *once* to trigger onCancel if not confirmed
    const hiddenHandler = () => {
        if (!confirmed) {
            handleCancel();
        }
        modalEl.removeEventListener('hidden.bs.modal', hiddenHandler);
    };
    modalEl.addEventListener('hidden.bs.modal', hiddenHandler);
    
    // Show modal
    modalInstance.show();
}

function replaceDrugFromPopover(oldPrescriptionId, newDrugName) {
    // Hide the popover temporarily
    if (currentDrugPopoverOverlay) {
        currentDrugPopoverOverlay.style.display = 'none';
        if (currentDrugPopover) currentDrugPopover.style.display = 'none';
    }

    showConfirmationModal(
        'Replace Medication',
        'Are you sure you want to replace this medication? This action cannot be undone.',
        () => {
            // CONFIRMED: Proceed with replacement
            
            // First delete the old one
            fetch(`/api/prescriptions/meds/${oldPrescriptionId}`, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Then add the new one
                    const formData = new FormData();
                    formData.append('appointment_id', window.APPOINTMENT_CONFIG.appointmentId);
                    formData.append('drug_name', newDrugName);
                    
                    return fetch('/api/prescriptions/meds', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    });
                }
                throw new Error(data.message || 'Failed to delete old prescription');
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Success: Close popover deeply (cleanup)
                    closeDrugPopover();
                    reloadMedications();
                    showSuccessMessage('Medication replaced successfully');
                } else {
                    throw new Error(data.message || 'Failed to add new prescription');
                }
            })
            .catch(err => {
                // Error: Restore popover
                if (currentDrugPopoverOverlay) {
                    currentDrugPopoverOverlay.style.display = 'block';
                    if (currentDrugPopover) currentDrugPopover.style.display = 'block';
                }
                console.error('Replace error:', err);
                showErrorMessage('Error replacing medication: ' + err.message);
            });
        },
        () => {
            // CANCELLED: Restore popover
            if (currentDrugPopoverOverlay) {
                currentDrugPopoverOverlay.style.display = 'block';
                if (currentDrugPopover) currentDrugPopover.style.display = 'block';
            }
        }
    );
}

function addDrugFromPopover(newDrugName) {
    const formData = new FormData();
    formData.append('appointment_id', window.APPOINTMENT_CONFIG.appointmentId);
    formData.append('drug_name', newDrugName);
    
    fetch('/api/prescriptions/meds', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeDrugPopover();
            reloadMedications();
            showSuccessMessage('Medication added successfully');
        } else {
            showErrorMessage('Error adding medication: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Add error:', err);
        showErrorMessage('Error adding medication');
    });
}

async function showDrugPopoverFromName(drugName, event, context = 'modal', currentPrescriptionId = null) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Close existing popover if any
    closeDrugPopover();
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'forum-drug-popover-overlay';
    overlay.onclick = closeDrugPopover;
    document.body.appendChild(overlay);
    currentDrugPopoverOverlay = overlay;
    
    // Create popover
    const popover = document.createElement('div');
    popover.className = 'forum-drug-popover';
    popover.innerHTML = `
        <div class="forum-drug-popover-header">
            <h3 class="forum-drug-popover-title">Loading...</h3>
            <button class="forum-drug-popover-close" onclick="closeDrugPopover()">&times;</button>
        </div>
        <div class="forum-drug-popover-body">
            <div style="text-align: center; padding: 2rem;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    `;
    
    // Position popover
    popover.style.position = 'fixed';
    popover.style.left = '50%';
    popover.style.top = '50%';
    popover.style.transform = 'translate(-50%, -50%)';
    popover.style.zIndex = '10000000';
    popover.style.maxHeight = '80vh';
    popover.style.overflowY = 'auto';
    
    document.body.appendChild(popover);
    currentDrugPopover = popover;
    
    try {
        // First, search for drug by name to get drug ID
        const searchResponse = await fetch(`/api/searchDrugsAutocomplete?q=${encodeURIComponent(drugName)}&limit=1`);
        const searchData = await searchResponse.json();
        
        if (searchData.drugs && searchData.drugs.length > 0) {
            const drug = searchData.drugs.find(d => d.drug_name === drugName) || searchData.drugs[0];
            const drugId = drug.ID;
            
            // Fetch drug details
            const response = await fetch(`/api/getDrugDetails?id=${drugId}`);
            const data = await response.json();
            
            if (data.drug) {
                const drugDetails = data.drug;
                const exactAlternatives = data.exact_alternatives || [];
                const similarProducts = data.similar_products || [];
                
                // Add Context Badge
                let contextBadge = '';
                if (context === 'modal') {
                    contextBadge = '<span class="badge bg-primary ms-2" style="font-size: 0.7em;">New Prescription</span>';
                } else if (context === 'card') {
                    contextBadge = '<span class="badge bg-info text-dark ms-2" style="font-size: 0.7em;">Current Prescription</span>';
                }

                popover.innerHTML = `
                    <div class="forum-drug-popover-header">
                        <div class="d-flex align-items-center">
                            <h3 class="forum-drug-popover-title">${escapeHtml(drugDetails.drug_name || drugName)}</h3>
                            ${contextBadge}
                        </div>
                        <button class="forum-drug-popover-close" onclick="closeDrugPopover()">&times;</button>
                    </div>
                    <div class="forum-drug-popover-body">
                        <!-- Main Details -->
                        ${drugDetails.Company ? `
                            <div class="forum-drug-popover-item">
                                <div class="forum-drug-popover-label">Company</div>
                                <div class="forum-drug-popover-value">${escapeHtml(drugDetails.Company)}</div>
                            </div>
                        ` : ''}
                        ${drugDetails.category ? `
                            <div class="forum-drug-popover-item">
                                <div class="forum-drug-popover-label">Category</div>
                                <div class="forum-drug-popover-value">${escapeHtml(drugDetails.category)}</div>
                            </div>
                        ` : ''}
                        ${drugDetails.price ? `
                            <div class="forum-drug-popover-item">
                                <div class="forum-drug-popover-label">Price</div>
                                <div class="forum-drug-popover-value">EGP ${escapeHtml(drugDetails.price)}</div>
                            </div>
                        ` : ''}
                        ${drugDetails.administration_route ? `
                            <div class="forum-drug-popover-item">
                                <div class="forum-drug-popover-label">Route</div>
                                <div class="forum-drug-popover-value">${escapeHtml(drugDetails.administration_route)}</div>
                            </div>
                        ` : ''}
                        ${drugDetails.SRDE ? `
                            <div class="forum-drug-popover-item">
                                <div class="forum-drug-popover-label">Additional Information</div>
                                <div class="forum-drug-popover-value">${escapeHtml(drugDetails.SRDE)}</div>
                            </div>
                        ` : ''}
                        
                        <!-- Alternatives Section -->
                        ${(exactAlternatives.length > 0 || similarProducts.length > 0) ? '<hr class="my-3">' : ''}
                        
                        ${exactAlternatives.length > 0 ? `
                            <div class="mb-3">
                                <h6 class="text-success mb-2">
                                    <i class="bi bi-check-circle me-1"></i>Alternative Drugs
                                    <small class="text-muted d-block fw-normal" style="font-size: 0.75em;">Same use & route, different ingredient</small>
                                </h6>
                                ${renderPopoverAlternativesList(exactAlternatives, 'exact', context, currentPrescriptionId)}
                            </div>
                        ` : ''}
                        
                        ${similarProducts.length > 0 ? `
                            <div>
                                <h6 class="text-info mb-2">
                                    <i class="bi bi-capsule me-1"></i>Similar Drugs
                                    <small class="text-muted d-block fw-normal" style="font-size: 0.75em;">Same ingredient & route</small>
                                </h6>
                                ${renderPopoverAlternativesList(similarProducts, 'similar', context, currentPrescriptionId)}
                            </div>
                        ` : ''}
                        
                        ${(exactAlternatives.length === 0 && similarProducts.length === 0) ? `
                            <div class="text-center text-muted py-2">
                                <small>No alternatives found</small>
                            </div>
                        ` : ''}
                    </div>
                `;
            } else {
                popover.innerHTML = getErrorPopoverContent(drugName, 'Drug information not available');
            }
        } else {
            popover.innerHTML = getErrorPopoverContent(drugName, 'Drug not found in database');
        }
    } catch (error) {
        console.error('Error in popover:', error);
        popover.innerHTML = getErrorPopoverContent(drugName, 'Error loading details');
    }
}

function getErrorPopoverContent(drugName, message) {
    return `
        <div class="forum-drug-popover-header">
            <h3 class="forum-drug-popover-title">${escapeHtml(drugName)}</h3>
            <button class="forum-drug-popover-close" onclick="closeDrugPopover()">&times;</button>
        </div>
        <div class="forum-drug-popover-body">
            <div class="forum-drug-popover-item">
                <div class="forum-drug-popover-value" style="color: var(--muted);">${message}</div>
            </div>
        </div>
    `;
}

function closeDrugPopover() {
    if (currentDrugPopover) {
        currentDrugPopover.remove();
        currentDrugPopover = null;
    }
    if (currentDrugPopoverOverlay) {
        currentDrugPopoverOverlay.remove();
        currentDrugPopoverOverlay = null;
    }
}

// Close popover on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && currentDrugPopover) {
        closeDrugPopover();
    }
});

// Override showImageModal to make the modal draggable
const originalShowImageModal = window.showImageModal;
if (originalShowImageModal) {
    window.showImageModal = function(imageUrl, attachmentId, filename) {
        originalShowImageModal.call(this, imageUrl, attachmentId, filename);
        // Wait a bit for modal to be added to DOM
        setTimeout(function() {
            const imageModal = document.getElementById('imageModal');
            if (imageModal) {
                makeModalDraggable(imageModal);
            }
        }, 100);
    };
}

// Override showUploadModal to make the modal draggable
const originalShowUploadModal = window.showUploadModal;
if (originalShowUploadModal) {
    window.showUploadModal = function(appointmentId, patientId) {
        originalShowUploadModal.call(this, appointmentId, patientId);
        setTimeout(function() {
            const uploadModal = document.getElementById('uploadModal');
            if (uploadModal) {
                makeModalDraggable(uploadModal);
            }
        }, 100);
    };
}

// Override openCameraModal to make the modal draggable
const originalOpenCameraModal = window.openCameraModal;
if (originalOpenCameraModal) {
    window.openCameraModal = function(appointmentId, patientId) {
        originalOpenCameraModal.call(this, appointmentId, patientId);
        setTimeout(function() {
            const cameraModal = document.getElementById('cameraModal');
            if (cameraModal) {
                makeModalDraggable(cameraModal);
            }
        }, 100);
    };
}

// Forum Topics Section
document.addEventListener('DOMContentLoaded', function() {
    loadAppointmentForumTopics();
});

async function loadAppointmentForumTopics() {
    const appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
    if (!appointmentId) return;
    
    try {
        const response = await fetch(`/api/forum/topics/appointment/${appointmentId}`);
        const data = await response.json();
        
        if (data.success) {
            renderAppointmentForumTopics(data.topics);
        }
    } catch (error) {
        console.error('Error loading forum topics:', error);
    }
}

function renderAppointmentForumTopics(topics) {
    const container = document.getElementById('appointmentForumTopics');
    if (!container) return;
    
    if (topics.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <p class="text-muted">No forum topics yet for this appointment.</p>
                <button class="btn btn-primary btn-sm" onclick="createAppointmentForumTopic()">
                    <i class="bi bi-plus-circle me-1"></i>Create Topic
                </button>
            </div>
        `;
        return;
    }
    
    let html = '<div class="list-group">';
    topics.forEach(topic => {
        const timeAgo = getTimeAgo(topic.created_at);
        html += `
            <a href="/doctor/forum/topic/${topic.id}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${escapeHtml(topic.title)}</h6>
                    <small>${timeAgo}</small>
                </div>
                <p class="mb-1">${escapeHtml(topic.content.substring(0, 100))}${topic.content.length > 100 ? '...' : ''}</p>
                <small><i class="bi bi-chat"></i> ${topic.replies_count || 0} replies</small>
            </a>
        `;
    });
    html += '</div>';
    html += `
        <div class="mt-3 text-center">
            <button class="btn btn-primary btn-sm" onclick="createAppointmentForumTopic()">
                <i class="bi bi-plus-circle me-1"></i>Create New Topic
            </button>
        </div>
    `;
    
    container.innerHTML = html;
}

function createAppointmentForumTopic() {
    window.location.href = `/doctor/forum?appointment_id=${window.APPOINTMENT_CONFIG.appointmentId}&create=true`;
}

function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Update print buttons in headers and action area
function updateMedicationsPrintButton(hasMedications) {
    const medicationsCard = document.querySelector('.card.mb-4');
    if (!medicationsCard) return;
    
    const header = medicationsCard.querySelector('.card-header');
    if (!header) return;
    
    const btnGroup = header.querySelector('.btn-group');
    if (!btnGroup) return;
    
    const existingPrintBtn = btnGroup.querySelector('button[onclick*="printPrescription"]');
    
    if (hasMedications && !existingPrintBtn) {
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-sm btn-outline-warning';
        printBtn.setAttribute('onclick', `printPrescription(${window.APPOINTMENT_CONFIG.appointmentId})`);
        printBtn.setAttribute('title', 'Print Prescription');
        printBtn.innerHTML = '<i class="bi bi-printer"></i>';
        btnGroup.insertBefore(printBtn, btnGroup.firstChild);
    } else if (!hasMedications && existingPrintBtn) {
        existingPrintBtn.remove();
    }
}

function updateGlassesPrintButton(hasGlasses) {
    const glassesCards = document.querySelectorAll('.card.mb-4');
    let glassesCard = null;
    glassesCards.forEach(card => {
        const header = card.querySelector('.card-header');
        if (header && header.textContent.includes('Glasses Prescription')) {
            glassesCard = card;
        }
    });
    
    if (!glassesCard) return;
    
    const header = glassesCard.querySelector('.card-header');
    if (!header) return;
    
    const btnGroup = header.querySelector('.btn-group');
    if (!btnGroup) return;
    
    const existingPrintBtn = btnGroup.querySelector('button[onclick*="printGlassesPrescription"]');
    
    if (hasGlasses && !existingPrintBtn) {
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-sm btn-outline-info';
        printBtn.setAttribute('onclick', `printGlassesPrescription(${window.APPOINTMENT_CONFIG.appointmentId})`);
        printBtn.setAttribute('title', 'Print Glasses');
        printBtn.innerHTML = '<i class="bi bi-printer"></i>';
        btnGroup.insertBefore(printBtn, btnGroup.firstChild);
    } else if (!hasGlasses && existingPrintBtn) {
        existingPrintBtn.remove();
    }
}

function updateLabTestsPrintButton(hasLabTests) {
    const labTestsCards = document.querySelectorAll('.card.mb-4');
    let labTestsCard = null;
    labTestsCards.forEach(card => {
        const header = card.querySelector('.card-header');
        if (header && header.textContent.includes('Lab Tests')) {
            labTestsCard = card;
        }
    });
    
    if (!labTestsCard) return;
    
    const header = labTestsCard.querySelector('.card-header');
    if (!header) return;
    
    const btnGroup = header.querySelector('.btn-group');
    if (!btnGroup) return;
    
    const existingPrintBtn = btnGroup.querySelector('button[onclick*="printLabTests"]');
    
    if (hasLabTests && !existingPrintBtn) {
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-sm btn-outline-secondary';
        printBtn.setAttribute('onclick', `printLabTests(${window.APPOINTMENT_CONFIG.appointmentId})`);
        printBtn.setAttribute('title', 'Print Lab Tests');
        printBtn.innerHTML = '<i class="bi bi-printer"></i>';
        btnGroup.insertBefore(printBtn, btnGroup.firstChild);
    } else if (!hasLabTests && existingPrintBtn) {
        existingPrintBtn.remove();
    }
}

// Update print buttons in action area
async function updateActionPrintButtons() {
    const appointmentId = window.APPOINTMENT_CONFIG.appointmentId;
    if (!appointmentId) return;
    
    const actionButtonsGroup = document.querySelector('.action-buttons-group');
    if (!actionButtonsGroup) return;
    
    // Check medications
    try {
        const medResponse = await fetch(`/api/appointments/${appointmentId}/medications`, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const medData = await medResponse.json();
        const hasMedications = medData.success && medData.medications && medData.medications.length > 0;
        
        let printPrescriptionBtn = actionButtonsGroup.querySelector('button[onclick*="printPrescription"]');
        if (hasMedications && !printPrescriptionBtn) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-warning hide-on-mobile';
            btn.setAttribute('onclick', `printPrescription(${appointmentId})`);
            btn.innerHTML = '<i class="bi bi-printer me-1"></i>Print Prescription';
            const historyBtn = actionButtonsGroup.querySelector('#appointmentHistoryBtn');
            if (historyBtn) {
                historyBtn.insertAdjacentElement('afterend', btn);
            }
        } else if (!hasMedications && printPrescriptionBtn) {
            printPrescriptionBtn.remove();
        }
    } catch (error) {
        console.error('Error checking medications:', error);
    }
    
    // Check glasses
    try {
        const glassesResponse = await fetch(`/api/appointments/${appointmentId}/glasses`, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const glassesData = await glassesResponse.json();
        const hasGlasses = glassesData.success && glassesData.glasses && glassesData.glasses.length > 0;
        
        let printGlassesBtn = actionButtonsGroup.querySelector('button[onclick*="printGlassesPrescription"]');
        if (hasGlasses && !printGlassesBtn) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-info hide-on-mobile';
            btn.setAttribute('onclick', `printGlassesPrescription(${appointmentId})`);
            btn.innerHTML = '<i class="bi bi-eyeglasses me-1"></i>Print Glasses';
            const printPrescriptionBtn = actionButtonsGroup.querySelector('button[onclick*="printPrescription"]');
            if (printPrescriptionBtn) {
                printPrescriptionBtn.insertAdjacentElement('afterend', btn);
            } else {
                const historyBtn = actionButtonsGroup.querySelector('#appointmentHistoryBtn');
                if (historyBtn) {
                    historyBtn.insertAdjacentElement('afterend', btn);
                }
            }
        } else if (!hasGlasses && printGlassesBtn) {
            printGlassesBtn.remove();
        }
    } catch (error) {
        console.error('Error checking glasses:', error);
    }
    
    // Check lab tests
    try {
        const labTestsResponse = await fetch(`/api/lab-tests/appointment/${appointmentId}`, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const labTestsData = await labTestsResponse.json();
        const hasLabTests = labTestsData.success && labTestsData.lab_tests && labTestsData.lab_tests.length > 0;
        
        let printLabTestsBtn = actionButtonsGroup.querySelector('button[onclick*="printLabTests"]');
        if (hasLabTests && !printLabTestsBtn) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-secondary hide-on-mobile';
            btn.setAttribute('onclick', `printLabTests(${appointmentId})`);
            btn.innerHTML = '<i class="bi bi-clipboard-data me-1"></i>Print Lab Tests';
            const printGlassesBtn = actionButtonsGroup.querySelector('button[onclick*="printGlassesPrescription"]');
            if (printGlassesBtn) {
                printGlassesBtn.insertAdjacentElement('afterend', btn);
            } else {
                const printPrescriptionBtn = actionButtonsGroup.querySelector('button[onclick*="printPrescription"]');
                if (printPrescriptionBtn) {
                    printPrescriptionBtn.insertAdjacentElement('afterend', btn);
                } else {
                    const historyBtn = actionButtonsGroup.querySelector('#appointmentHistoryBtn');
                    if (historyBtn) {
                        historyBtn.insertAdjacentElement('afterend', btn);
                    }
                }
            }
        } else if (!hasLabTests && printLabTestsBtn) {
            printLabTestsBtn.remove();
        }
    } catch (error) {
        console.error('Error checking lab tests:', error);
    }
    
    // Update more actions popover
    updateMoreActionsPopover();
}

// Update more actions popover (if function exists)
function updateMoreActionsPopover() {
    if (typeof window.updateMoreActionsPopover === 'function') {
        window.updateMoreActionsPopover();
    }
}

// =========================================
// Custom Select Menu Functions
// =========================================

// Function to convert a select element to custom menu
function convertSelectToCustomMenu(selectElement, iconClass = 'bi-list') {
    if (!selectElement || selectElement.tagName !== 'SELECT') {
        return null;
    }
    
    // Check if already converted
    if (selectElement.closest('.field.menu')) {
        return selectElement.closest('.field.menu');
    }
    
    // Get select options and selected value
    const options = Array.from(selectElement.options);
    const selectedValue = selectElement.value;
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const selectedText = selectedOption ? selectedOption.textContent : (options[0] ? options[0].textContent : 'Select an option');
    
    // Create custom menu structure
    const fieldMenu = document.createElement('section');
    fieldMenu.className = 'field menu';
    fieldMenu.style.minWidth = '100%';
    
    const control = document.createElement('div');
    control.className = 'control';
    
    // Hide original select
    selectElement.classList.add('d-none');
    
    // Create button
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'custom-select-toggle';
    button.setAttribute('aria-expanded', 'false');
    button.textContent = selectedText;
    
    // Create menu
    const menu = document.createElement('menu');
    menu.className = 'custom-select-menu';
    
    // Create menu items with icons
    options.forEach(option => {
        const li = document.createElement('li');
        li.setAttribute('data-option', option.value);
        li.setAttribute('tabindex', '0');
        li.setAttribute('role', 'button');
        
        if (option.value === selectedValue) {
            li.classList.add('selected');
        }
        
        // Add icon
        const icon = document.createElement('i');
        icon.className = `${iconClass} fs-5`;
        li.appendChild(icon);
        
        // Add text
        const h3 = document.createElement('h3');
        h3.textContent = option.textContent;
        li.appendChild(h3);
        
        menu.appendChild(li);
    });
    
    // Assemble structure
    control.appendChild(selectElement);
    control.appendChild(button);
    control.appendChild(menu);
    fieldMenu.appendChild(control);
    
    // Replace select with custom menu
    selectElement.parentNode.insertBefore(fieldMenu, selectElement);
    selectElement.parentNode.removeChild(selectElement);
    
    // Move select inside control
    control.insertBefore(selectElement, button);
    
    return fieldMenu;
}

// Icon mapping for different select types
const selectIconMap = {
    'route': 'bi-arrow-right-circle',
    'new_time': 'bi-clock',
    'newTimeInput': 'bi-clock',
    'newTimeInputFollowup': 'bi-clock',
    'cameraAttachmentType': 'bi-camera',
    'attachment_type': 'bi-paperclip',
    'lens_type': 'bi-eye',
    'test_type': 'bi-clipboard-check',
    'test_category': 'bi-tags',
    'priority': 'bi-flag',
    'status': 'bi-check-circle',
    'newStatusSelect': 'bi-check-circle'
};

// Function to get appropriate icon for select
function getIconForSelect(selectElement) {
    const name = selectElement.name || '';
    const id = selectElement.id || '';
    
    // Check by name first
    if (selectIconMap[name]) {
        return selectIconMap[name];
    }
    
    // Check by id
    if (selectIconMap[id]) {
        return selectIconMap[id];
    }
    
    // Default icon
    return 'bi-list';
}

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
            // Dispatch change event with bubbles: true to ensure it propagates
            select.dispatchEvent(new Event('change', { bubbles: true }));

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

// Function to convert all selects in a container to custom menus
function convertSelectsInContainer(container) {
    if (!container) return;
    
    const selects = container.querySelectorAll('select:not(.d-none)');
    selects.forEach(select => {
        // Skip if already converted
        if (select.closest('.field.menu')) return;
        
        const iconClass = getIconForSelect(select);
        convertSelectToCustomMenu(select, iconClass);
    });
    
    // Initialize custom selects
    setTimeout(() => {
        initCustomSelects();
    }, 100);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Convert existing selects
    convertSelectsInContainer(document.body);
    initCustomSelects();
});

// Also initialize when modals are shown
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    convertSelectsInContainer(modal);
});

// Format time function (similar to calendar.js)
function formatTime(time) {
    if (!time) return '';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

// Populate time slots function (similar to calendar.js)
function populateTimeSlots(selectElement, availableSlots, preselectedTime = null) {
    const timeField = selectElement.closest('.field.menu');
    const timeMenu = timeField ? timeField.querySelector('menu') : null;
    const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
    
    selectElement.innerHTML = '<option value="">Select available time slot...</option>';
    
    // Add all available slots
    availableSlots.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = formatTime(time);
        selectElement.appendChild(option);
    });
    
    // If there's a preselected time that's not in available slots, add it
    if (preselectedTime && !availableSlots.includes(preselectedTime)) {
        const option = document.createElement('option');
        option.value = preselectedTime;
        option.textContent = formatTime(preselectedTime) + ' (Selected)';
        option.style.fontWeight = 'bold';
        option.style.color = '#28a745';
        option.style.backgroundColor = '#f8f9fa';
        selectElement.appendChild(option);
    }
    
    // Sort all options by time (except the first "Select..." option)
    const options = Array.from(selectElement.options).slice(1); // Skip first "Select..." option
    options.sort((a, b) => a.value.localeCompare(b.value));
    
    // Clear and re-add sorted options
    selectElement.innerHTML = '<option value="">Select available time slot...</option>';
    options.forEach(option => selectElement.appendChild(option));
    
    // Update custom menu if it exists
    if (timeMenu) {
        timeMenu.innerHTML = '<li data-option="" tabindex="0" role="button" class="selected"><i class="bi-clock fs-5"></i><h3>Select available time slot...</h3></li>';
        
        // Add all sorted options to custom menu
        options.forEach(option => {
            const li = document.createElement('li');
            li.setAttribute('data-option', option.value);
            li.setAttribute('tabindex', '0');
            li.setAttribute('role', 'button');
            
            // Add clock icon
            const icon = document.createElement('i');
            icon.className = 'bi-clock fs-5';
            li.appendChild(icon);
            
            // Add text content
            const h3 = document.createElement('h3');
            h3.textContent = option.textContent;
            li.appendChild(h3);
            
            timeMenu.appendChild(li);
        });
        
        // Remove initialization flag to allow re-initialization
        if (timeField) {
            timeField.removeAttribute('data-initialized');
        }
        
        // Re-initialize custom select to attach event listeners
        setTimeout(() => {
            initCustomSelects();
        }, 50);
    }
    
    // If preselected time exists, select it immediately
    if (preselectedTime) {
        setTimeout(() => {
            selectElement.value = preselectedTime;
            if (selectElement.value === preselectedTime) {
                // Update custom menu selection
                if (timeMenu && timeButton) {
                    const selectedLi = timeMenu.querySelector(`li[data-option="${preselectedTime}"]`);
                    if (selectedLi) {
                        timeMenu.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                        selectedLi.classList.add('selected');
                        timeButton.textContent = selectedLi.querySelector('h3')?.textContent || formatTime(preselectedTime);
                        
                        // Store scroll function for when menu opens
                        const field = timeMenu.closest('.field.menu');
                        if (field) {
                            field.dataset.selectedValue = preselectedTime;
                        }
                    }
                }
            }
        }, 50);
    }
}

// Initialize Medical History Carousel with modern design
function initMedicalHistoryCarousel() {
    const track = document.getElementById('medicalHistoryTrack');
    if (!track) return;
    
    const wrap = track.parentElement;
    const cards = Array.from(track.children);
    const prev = document.getElementById('medicalHistoryPrev');
    const next = document.getElementById('medicalHistoryNext');
    const dotsBox = document.getElementById('medicalHistoryDots');
    
    if (!prev || !next || !dotsBox) return;
    
    const isMobile = () => matchMedia("(max-width:767px)").matches;
    
    // Create dots
    cards.forEach((_, i) => {
        const dot = document.createElement('span');
        dot.className = 'medical-history-dot';
        dot.onclick = () => activate(i, true);
        dotsBox.appendChild(dot);
    });
    const dots = Array.from(dotsBox.children);
    
    let current = 0;
    let autoPlayInterval = null;
    const INTERVAL_TIME = 30000; // 30 seconds
    
    function center(i) {
        const card = cards[i];
        if (!card) return;
        
        // Always use vertical scrolling with smooth animation
        const start = card.offsetTop;
        const containerHeight = wrap.clientHeight;
        const cardHeight = card.clientHeight;
        const targetScroll = start - (containerHeight / 2 - cardHeight / 2);
        
        // Use requestAnimationFrame for smoother scrolling
        let startScroll = wrap.scrollTop;
        const distance = targetScroll - startScroll;
        const duration = 700; // 700ms for smooth animation
        let startTime = null;
        
        function animateScroll(currentTime) {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const progress = Math.min(timeElapsed / duration, 1);
            
            // Use easeOutCubic for smooth deceleration
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            wrap.scrollTop = startScroll + (distance * easeOutCubic);
            
            if (progress < 1) {
                requestAnimationFrame(animateScroll);
            }
        }
        
        requestAnimationFrame(animateScroll);
    }
    
    function toggleUI(i) {
        // Add smooth transition class before toggling
        cards.forEach((c, k) => {
            if (k === i) {
                c.classList.add('transitioning');
                setTimeout(() => {
                    c.classList.add('active');
                    c.classList.remove('transitioning');
                }, 10);
            } else {
                c.classList.remove('active');
            }
        });
        dots.forEach((d, k) => d.classList.toggle('active', k === i));
        prev.disabled = i === 0;
        next.disabled = i === cards.length - 1;
    }
    
    function activate(i, scroll) {
        if (i === current || i < 0 || i >= cards.length) return;
        const prevIndex = current;
        current = i;
        toggleUI(i);
        if (scroll) {
            // Small delay to allow CSS transitions to start
            setTimeout(() => {
                center(i);
            }, 50);
        }
    }
    
    function go(step) {
        activate(Math.min(Math.max(current + step, 0), cards.length - 1), true);
    }
    
    prev.onclick = () => go(-1);
    next.onclick = () => go(1);
    
    // Keyboard navigation
    addEventListener('keydown', (e) => {
        if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') return;
        // Vertical navigation: ArrowDown goes down, ArrowUp goes up
        if (['ArrowDown'].includes(e.key)) go(1);
        if (['ArrowUp'].includes(e.key)) go(-1);
    }, { passive: true });
    
    // Card interactions
    cards.forEach((card, i) => {
        card.addEventListener('mouseenter', () => {
            if (matchMedia('(hover:hover)').matches) activate(i, true);
        });
        card.addEventListener('click', () => activate(i, true));
    });
    
    // Touch swipe
    let sx = 0, sy = 0;
    track.addEventListener('touchstart', (e) => {
        sx = e.touches[0].clientX;
        sy = e.touches[0].clientY;
    }, { passive: true });
    
    track.addEventListener('touchend', (e) => {
        const dx = e.changedTouches[0].clientX - sx;
        const dy = e.changedTouches[0].clientY - sy;
        // Always use vertical swipe
        if (Math.abs(dy) > 60) {
            go(dy > 0 ? -1 : 1);
        }
    }, { passive: true });
    
    // Auto-play function
    function checkAndStartAutoPlay() {
        if (cards.length <= 1) {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
            return;
        }
        
        // Check if container can fit more than one item (vertical)
        const containerHeight = wrap.clientHeight;
        const firstCard = cards[0];
        if (!firstCard) return;
        
        const cardStyle = window.getComputedStyle(firstCard);
        const cardHeight = firstCard.offsetHeight + 
                          parseFloat(cardStyle.marginTop) + 
                          parseFloat(cardStyle.marginBottom);
        
        // If container can only fit one item, start auto-play
        if (containerHeight < cardHeight * 1.5 && !isMobile()) {
            if (!autoPlayInterval) {
                autoPlayInterval = setInterval(() => {
                    go(1);
                }, INTERVAL_TIME);
            }
        } else {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }
    }
    
    // Pause on hover
    track.addEventListener('mouseenter', () => {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
            autoPlayInterval = null;
        }
    });
    
    // Resume on mouse leave
    track.addEventListener('mouseleave', () => {
        checkAndStartAutoPlay();
    });
    
    // Hide dots on mobile
    if (isMobile()) dotsBox.style.display = 'none';
    
    // Handle resize
    addEventListener('resize', () => {
        center(current);
        checkAndStartAutoPlay();
        if (isMobile()) {
            dotsBox.style.display = 'none';
        } else {
            dotsBox.style.display = 'flex';
        }
    });
    
    // Initialize
    toggleUI(0);
    center(0);
    setTimeout(checkAndStartAutoPlay, 100);
}


function updateTimeSelectCustomMenu(selectElement, text) {
    const fieldMenu = selectElement.closest('.field.menu');
    const menu = fieldMenu ? fieldMenu.querySelector('menu') : null;
    const button = fieldMenu ? fieldMenu.querySelector('.custom-select-toggle') : null;
    
    if (menu && button) {
        // Get all options from select
        const options = Array.from(selectElement.options);
        menu.innerHTML = '';
        
        options.forEach(option => {
            const li = document.createElement('li');
            li.setAttribute('data-option', option.value);
            li.setAttribute('tabindex', '0');
            li.setAttribute('role', 'button');
            if (option.selected) {
                li.classList.add('selected');
            }
            const icon = document.createElement('i');
            icon.className = 'bi-clock fs-5';
            li.appendChild(icon);
            const h3 = document.createElement('h3');
            h3.textContent = option.textContent;
            li.appendChild(h3);
            menu.appendChild(li);
        });
        
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        button.textContent = selectedOption ? selectedOption.textContent : text || 'Select available time slot...';
        
        // Remove initialization flag to allow re-initialization
        if (fieldMenu) {
            fieldMenu.removeAttribute('data-initialized');
        }
        
        // Re-initialize custom select
        setTimeout(() => {
            initCustomSelects();
        }, 50);
    }
}

// Fix modal z-index issues by moving them to body
function fixAppointmentModalPlacement() {
    // Look for any existing modals (though most are dynamic)
    document.querySelectorAll('.modal').forEach(modal => {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });

    // Validating dynamic modals are added to body is already handled in the codebase,
    // but we can add a listener just in case other scripts add them incorrectly.
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('modal')) {
                        if (node.parentElement !== document.body) {
                            document.body.appendChild(node);
                        }
                    }
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

// Call the fix on load
document.addEventListener('DOMContentLoaded', fixAppointmentModalPlacement);

// Initialize AI Chat Widget
document.addEventListener('DOMContentLoaded', function() {
    if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.appointmentId) {
        if (typeof initAIChatWidget === 'function') {
            initAIChatWidget(
                window.APPOINTMENT_CONFIG.patientId, 
                window.APPOINTMENT_CONFIG.appointmentId
            );
        }
    }
});
