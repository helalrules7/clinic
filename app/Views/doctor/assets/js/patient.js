
/* Helper function to apply glass style to dynamically created modals */
function applyGlassStyleToModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const modalContent = modal.querySelector('.modal-content');
    const modalHeader = modal.querySelector('.modal-header');
    const modalBody = modal.querySelector('.modal-body');
    const modalFooter = modal.querySelector('.modal-footer');
    const modalDialog = modal.querySelector('.modal-dialog');
    
    if (modalContent) {
        modalContent.style.background = 'rgba(248, 250, 252, 0.35)';
        modalContent.style.backdropFilter = 'blur(10px)';
        modalContent.style.webkitBackdropFilter = 'blur(10px)';
        modalContent.style.border = '1px solid rgba(226, 232, 240, 0.3)';
        modalContent.style.boxShadow = '2px 0 8px 0 rgba(0, 0, 0, 0.08)';
        modalContent.style.color = 'var(--text)';
        modalContent.style.cursor = 'move';
    }
    
    if (modalHeader) {
        modalHeader.style.background = 'transparent';
        modalHeader.style.borderBottomColor = 'rgba(226, 232, 240, 0.3)';
        modalHeader.style.color = 'var(--text)';
        modalHeader.style.userSelect = 'none';
    }
    
    if (modalBody) {
        modalBody.style.background = 'transparent';
        modalBody.style.color = 'var(--text)';
    }
    
    if (modalFooter) {
        modalFooter.style.background = 'transparent';
        modalFooter.style.borderTopColor = 'rgba(226, 232, 240, 0.3)';
    }
    
    if (modalDialog) {
        modalDialog.style.cursor = 'default';
        modalDialog.style.transition = 'transform 0.2s ease';
        modalDialog.style.margin = '1.75rem auto';
    }
    
    // Add dark mode styles
    const styleId = 'glass-style-' + modalId;
    let style = document.getElementById(styleId);
    if (!style) {
        style = document.createElement('style');
        style.id = styleId;
        document.head.appendChild(style);
    }
    style.textContent = `
        .dark #${modalId} .modal-content {
            background: rgba(11, 18, 32, 0.40) !important;
            border: 1px solid rgba(51, 65, 85, 0.3) !important;
            box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
        }
        .dark #${modalId} .modal-header {
            border-bottom-color: rgba(51, 65, 85, 0.3) !important;
        }
        .dark #${modalId} .modal-header .btn-close {
            filter: invert(1) brightness(2);
            opacity: 0.9;
        }
        .dark #${modalId} .modal-header .btn-close:hover {
            opacity: 1;
            filter: invert(1) brightness(2.5);
        }
        .dark #${modalId} .modal-footer {
            border-top-color: rgba(51, 65, 85, 0.3) !important;
        }
    `;
}

function bookNewAppointment(patientId) {
    // Redirect to calendar with patient pre-selected
    window.location.href = `/doctor/calendar?patient_id=${patientId}`;
}

function printPatientSummary() {
    // Open print dialog for patient summary
    window.print();
}

function exportPatientData() {
    // Get patient ID from URL
    const patientId = window.location.pathname.split('/').pop();
    
    // Show loading notification
    showNotification('Preparing patient data export...', 'info');
    
    // Create a temporary loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'exportLoadingOverlay';
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    const loadingContent = document.createElement('div');
    loadingContent.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    `;
    
    loadingContent.innerHTML = `
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5>Exporting Patient Data</h5>
        <p class="text-muted mb-0">Generating Word document with all patient information...</p>
    `;
    
    loadingOverlay.appendChild(loadingContent);
    document.body.appendChild(loadingOverlay);
    
    // First, test if the user is authenticated by making a fetch request
    fetch(`/api/patients/${patientId}/export`, {
        method: 'HEAD', // Use HEAD to test without downloading
        credentials: 'same-origin' // Include cookies
    })
    .then(response => {
        if (response.status === 401 || response.status === 403) {
            // User not authenticated or not authorized
            document.body.removeChild(loadingOverlay);
            showNotification('You must be logged in to export patient data. Please refresh the page and try again.', 'warning');
            return;
        }
        
        if (!response.ok && response.status !== 200) {
            // Other error
            document.body.removeChild(loadingOverlay);
            showNotification('Error accessing export function. Please try again.', 'error');
            return;
        }
        
        // User is authenticated, proceed with download
        downloadPatientData(patientId, loadingOverlay);
    })
    .catch(error => {
        console.error('Error testing export access:', error);
        document.body.removeChild(loadingOverlay);
        showNotification('Network error. Please check your connection and try again.', 'error');
    });
}

function downloadPatientData(patientId, loadingOverlay) {
    // Create download link and trigger export
    const downloadLink = document.createElement('a');
    downloadLink.href = `/api/patients/${patientId}/export`;
    downloadLink.download = `Patient_${patientId}_${new Date().toISOString().split('T')[0]}.docx`;
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    
    // Handle download completion/error
    let downloadCompleted = false;
    
    // Set a timeout to remove loading overlay in case of issues
    const timeoutId = setTimeout(() => {
        if (!downloadCompleted) {
            if (document.body.contains(loadingOverlay)) {
                document.body.removeChild(loadingOverlay);
            }
            downloadCompleted = true;
            showNotification('Export completed! Check your downloads folder.', 'success');
        }
    }, 8000); // 8 seconds timeout (increased for larger files)
    
    // Listen for window focus to detect download completion
    const handleWindowFocus = () => {
        if (!downloadCompleted) {
            setTimeout(() => {
                if (!downloadCompleted) {
                    if (document.body.contains(loadingOverlay)) {
                        document.body.removeChild(loadingOverlay);
                    }
                    downloadCompleted = true;
                    clearTimeout(timeoutId);
                    showNotification('Export completed! Check your downloads folder.', 'success');
                }
            }, 1500);
        }
        window.removeEventListener('focus', handleWindowFocus);
    };
    
    window.addEventListener('focus', handleWindowFocus);
    
    // Trigger the download
    try {
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        // For immediate feedback
        setTimeout(() => {
            if (!downloadCompleted) {
                showNotification('Download started. Generating your document...', 'info');
            }
        }, 1000);
        
    } catch (error) {
        console.error('Error triggering download:', error);
        if (!downloadCompleted) {
            if (document.body.contains(loadingOverlay)) {
                document.body.removeChild(loadingOverlay);
            }
            downloadCompleted = true;
            clearTimeout(timeoutId);
        }
        showNotification('Error starting export. Please try again.', 'error');
        if (document.body.contains(downloadLink)) {
            document.body.removeChild(downloadLink);
        }
    }
}

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
            applyGlassStyleToModal('editPatientModal');
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

// Appointment timeline functions - moved to top to ensure availability
function handleAppointmentHeaderClick(event, collapseId) {
    // Check if click originated from a button, link, or any interactive element
    const clickedElement = event.target;
    const isButtonClick = clickedElement.closest('button') || 
                         clickedElement.closest('a') || 
                         clickedElement.closest('.btn-group') ||
                         clickedElement.closest('.btn');
    
    // If click is on a button, link, or button group, don't toggle collapse
    if (isButtonClick && !clickedElement.closest('.collapse-icon')) {
        event.stopPropagation();
        event.preventDefault();
        return; // Let the button/link handle its own click
    }
    
    // Otherwise, toggle the collapse
    toggleAppointmentTimeline(collapseId);
}

function toggleAppointmentTimeline(collapseId) {
    const header = document.querySelector(`[data-bs-target="#${collapseId}"]`);
    const body = document.getElementById(collapseId);
    
    if (!header || !body) return;
    
    // Use Bootstrap Collapse API
    const bsCollapse = bootstrap.Collapse.getInstance(body) || new bootstrap.Collapse(body, {
        toggle: false
    });
    
    if (body.classList.contains('show')) {
        bsCollapse.hide();
    } else {
        bsCollapse.show();
    }
    
    // Update header classes after animation
    body.addEventListener('shown.bs.collapse', function updateHeader() {
        header.classList.add('expanded');
        header.classList.remove('collapsed');
        body.removeEventListener('shown.bs.collapse', updateHeader);
        
        // Update expand all button state and icon
        setTimeout(() => {
            updateExpandAllAppointmentsButton();
        }, 100);
    });
    
    body.addEventListener('hidden.bs.collapse', function updateHeader() {
        header.classList.remove('expanded');
        header.classList.add('collapsed');
        body.removeEventListener('hidden.bs.collapse', updateHeader);
        
        // Update expand all button state and icon
        setTimeout(() => {
            updateExpandAllAppointmentsButton();
        }, 100);
    });
    
    // Also update immediately for better UX
    setTimeout(() => {
        updateExpandAllAppointmentsButton();
    }, 350);
}

function expandCollapseAllAppointments() {
    const timeline = document.querySelector('.appointment-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllAppointmentsBtn');
    const text = document.getElementById('expandAllAppointmentsText');
    
    if (!btn || !text) return;
    
    // Check if all are expanded
    let allExpanded = true;
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    // Toggle all
    allCollapses.forEach(collapse => {
        const collapseId = collapse.id;
        const header = document.querySelector(`[data-bs-target="#${collapseId}"]`);
        if (!header) return;
        
        if (allExpanded) {
            // Collapse all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.hide();
            } else {
                collapse.classList.remove('show');
                header.classList.remove('expanded');
                header.classList.add('collapsed');
            }
        } else {
            // Expand all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.show();
            } else {
                collapse.classList.add('show');
                header.classList.remove('collapsed');
                header.classList.add('expanded');
            }
        }
    });
    
    // Update button text after a short delay to allow animations
    setTimeout(() => {
        updateExpandAllAppointmentsButton();
    }, 350);
}

function updateExpandAllAppointmentsButton() {
    const timeline = document.querySelector('.appointment-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllAppointmentsBtn');
    const text = document.getElementById('expandAllAppointmentsText');
    
    if (!btn || !text) return;
    
    let allExpanded = true;
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    const icon = btn.querySelector('i');
    if (allExpanded) {
        text.textContent = 'Collapse All';
        if (icon) icon.className = 'bi bi-chevron-double-up me-1';
    } else {
        text.textContent = 'Expand All';
        if (icon) icon.className = 'bi bi-chevron-double-down me-1';
    }
}

// Prescription timeline functions - moved to top to ensure availability
function handlePrescriptionHeaderClick(event, collapseId) {
    // Check if click originated from a button, link, or any interactive element
    const clickedElement = event.target;
    const isButtonClick = clickedElement.closest('button') || 
                         clickedElement.closest('a') || 
                         clickedElement.closest('.btn-group') ||
                         clickedElement.closest('.btn');
    
    // If click is on a button, link, or button group, don't toggle collapse
    if (isButtonClick && !clickedElement.closest('.collapse-icon')) {
        event.stopPropagation();
        event.preventDefault();
        return; // Let the button/link handle its own click
    }
    
    // Otherwise, toggle the collapse
    togglePrescriptionTimeline(collapseId);
}

function togglePrescriptionTimeline(collapseId) {
    const header = document.querySelector(`[data-bs-target="#${collapseId}"]`);
    const body = document.getElementById(collapseId);
    
    if (!header || !body) return;
    
    // Use Bootstrap Collapse API
    const bsCollapse = bootstrap.Collapse.getInstance(body) || new bootstrap.Collapse(body, {
        toggle: false
    });
    
    if (body.classList.contains('show')) {
        bsCollapse.hide();
    } else {
        bsCollapse.show();
    }
    
    // Update header classes after animation
    body.addEventListener('shown.bs.collapse', function updateHeader() {
        header.classList.add('expanded');
        header.classList.remove('collapsed');
        body.removeEventListener('shown.bs.collapse', updateHeader);
        
        // Update expand all button states and icons
        setTimeout(() => {
            updateExpandAllMedicationsButton();
            updateExpandAllGlassesButton();
            updateExpandAllPrescriptionsButton();
        }, 100);
    });
    
    body.addEventListener('hidden.bs.collapse', function updateHeader() {
        header.classList.remove('expanded');
        header.classList.add('collapsed');
        body.removeEventListener('hidden.bs.collapse', updateHeader);
        
        // Update expand all button states and icons
        setTimeout(() => {
            updateExpandAllMedicationsButton();
            updateExpandAllGlassesButton();
            updateExpandAllPrescriptionsButton();
        }, 100);
    });
    
    // Also update immediately for better UX
    setTimeout(() => {
        updateExpandAllMedicationsButton();
        updateExpandAllGlassesButton();
        updateExpandAllPrescriptionsButton();
    }, 350);
}

function expandCollapseAllMedications() {
    const timeline = document.querySelector('#medications .prescription-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllMedicationsBtn');
    const text = document.getElementById('expandAllMedicationsText');
    
    if (!btn || !text) return;
    
    // Check if all are expanded
    let allExpanded = true;
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    // Toggle all
    allCollapses.forEach(collapse => {
        const collapseId = collapse.id;
        const header = document.querySelector(`[data-bs-target="#${collapseId}"]`);
        if (!header) return;
        
        if (allExpanded) {
            // Collapse all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.hide();
            } else {
                collapse.classList.remove('show');
                header.classList.remove('expanded');
                header.classList.add('collapsed');
            }
        } else {
            // Expand all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.show();
            } else {
                collapse.classList.add('show');
                header.classList.remove('collapsed');
                header.classList.add('expanded');
            }
        }
    });
    
    // Update button text after a short delay
    setTimeout(() => {
        updateExpandAllMedicationsButton();
    }, 350);
}

function updateExpandAllMedicationsButton() {
    const timeline = document.querySelector('#medications .prescription-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllMedicationsBtn');
    const text = document.getElementById('expandAllMedicationsText');
    
    if (!btn || !text) return;
    
    let allExpanded = true;
    let hasCollapses = allCollapses.length > 0;
    
    if (!hasCollapses) return;
    
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    const icon = btn.querySelector('i');
    if (allExpanded) {
        text.textContent = 'Collapse All';
        if (icon) icon.className = 'bi bi-chevron-double-up me-1';
    } else {
        text.textContent = 'Expand All';
        if (icon) icon.className = 'bi bi-chevron-double-down me-1';
    }
}

function expandCollapseAllGlasses() {
    const timeline = document.querySelector('#glasses .prescription-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllGlassesBtn');
    const text = document.getElementById('expandAllGlassesText');
    
    if (!btn || !text) return;
    
    // Check if all are expanded
    let allExpanded = true;
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    // Toggle all
    allCollapses.forEach(collapse => {
        const collapseId = collapse.id;
        const header = document.querySelector(`[data-bs-target="#${collapseId}"]`);
        if (!header) return;
        
        if (allExpanded) {
            // Collapse all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.hide();
            } else {
                collapse.classList.remove('show');
                header.classList.remove('expanded');
                header.classList.add('collapsed');
            }
        } else {
            // Expand all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.show();
            } else {
                collapse.classList.add('show');
                header.classList.remove('collapsed');
                header.classList.add('expanded');
            }
        }
    });
    
    // Update button text after a short delay
    setTimeout(() => {
        updateExpandAllGlassesButton();
    }, 350);
}

function updateExpandAllGlassesButton() {
    const timeline = document.querySelector('#glasses .prescription-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllGlassesBtn');
    const text = document.getElementById('expandAllGlassesText');
    
    if (!btn || !text) return;
    
    let allExpanded = true;
    let hasCollapses = allCollapses.length > 0;
    
    if (!hasCollapses) return;
    
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    const icon = btn.querySelector('i');
    if (allExpanded) {
        text.textContent = 'Collapse All';
        if (icon) icon.className = 'bi bi-chevron-double-up me-1';
    } else {
        text.textContent = 'Expand All';
        if (icon) icon.className = 'bi bi-chevron-double-down me-1';
    }
}

function expandCollapseAllPrescriptions() {
    const timeline = document.querySelector('#all-prescriptions .prescription-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllPrescriptionsBtn');
    const text = document.getElementById('expandAllPrescriptionsText');
    
    if (!btn || !text) return;
    
    // Check if all are expanded
    let allExpanded = true;
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    // Toggle all
    allCollapses.forEach(collapse => {
        const collapseId = collapse.id;
        const header = document.querySelector(`[data-bs-target="#${collapseId}"]`);
        if (!header) return;
        
        if (allExpanded) {
            // Collapse all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.hide();
            } else {
                collapse.classList.remove('show');
                header.classList.remove('expanded');
                header.classList.add('collapsed');
            }
        } else {
            // Expand all
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) {
                bsCollapse.show();
            } else {
                collapse.classList.add('show');
                header.classList.remove('collapsed');
                header.classList.add('expanded');
            }
        }
    });
    
    // Update button text after a short delay
    setTimeout(() => {
        updateExpandAllPrescriptionsButton();
    }, 350);
}

function updateExpandAllPrescriptionsButton() {
    const timeline = document.querySelector('#all-prescriptions .prescription-timeline');
    if (!timeline) return;
    
    const allCollapses = timeline.querySelectorAll('.collapse');
    const btn = document.getElementById('expandAllPrescriptionsBtn');
    const text = document.getElementById('expandAllPrescriptionsText');
    
    if (!btn || !text) return;
    
    let allExpanded = true;
    let hasCollapses = allCollapses.length > 0;
    
    if (!hasCollapses) return;
    
    allCollapses.forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            allExpanded = false;
        }
    });
    
    const icon = btn.querySelector('i');
    if (allExpanded) {
        text.textContent = 'Collapse All';
        if (icon) icon.className = 'bi bi-chevron-double-up me-1';
    } else {
        text.textContent = 'Expand All';
        if (icon) icon.className = 'bi bi-chevron-double-down me-1';
    }
}

function addMedicalHistory() {
    const patientId = window.PATIENT_CONFIG.patientId;
    showAddMedicalHistoryModal(patientId);
}

function showAddMedicalHistoryModal(patientId) {
    const modalId = 'addMedicalHistoryModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="background: rgba(248, 250, 252, 0.35) !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.3) !important; box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08); color: var(--text) !important; cursor: move;">
                    <div class="modal-header" style="background: transparent !important; border-bottom-color: rgba(226, 232, 240, 0.3) !important; color: var(--text) !important; user-select: none;">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add Medical History Entry
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="background: transparent !important; color: var(--text) !important;">
                        <form id="addMedicalHistoryForm">
                            <input type="hidden" id="addPatientId" value="${patientId}">
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="addConditionName" class="form-label">Condition Name *</label>
                                    <input type="text" class="form-control" id="addConditionName" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="addDiagnosisDate" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="addDiagnosisDate">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="addCategory" class="form-label">Category</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-select d-none" id="addCategory">
                                                <option value="general" selected>General</option>
                                                <option value="allergy">Allergy</option>
                                                <option value="medication">Medication</option>
                                                <option value="surgery">Surgery</option>
                                                <option value="family_history">Family History</option>
                                                <option value="social_history">Social History</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false">General</button>
                                            <menu>
                                                <li data-option="general" tabindex="0" role="button" class="selected"><i class="bi-tags fs-5"></i><h3>General</h3></li>
                                                <li data-option="allergy" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Allergy</h3></li>
                                                <li data-option="medication" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Medication</h3></li>
                                                <li data-option="surgery" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Surgery</h3></li>
                                                <li data-option="family_history" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Family History</h3></li>
                                                <li data-option="social_history" tabindex="0" role="button"><i class="bi-tags fs-5"></i><h3>Social History</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                                <div class="col-md-6">
                                    <label for="addStatus" class="form-label">Status</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-select d-none" id="addStatus">
                                                <option value="active" selected>Active</option>
                                                <option value="resolved">Resolved</option>
                                                <option value="chronic">Chronic</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false">Active</button>
                                            <menu>
                                                <li data-option="active" tabindex="0" role="button" class="selected"><i class="bi-check-circle fs-5"></i><h3>Active</h3></li>
                                                <li data-option="resolved" tabindex="0" role="button"><i class="bi-check-circle fs-5"></i><h3>Resolved</h3></li>
                                                <li data-option="chronic" tabindex="0" role="button"><i class="bi-check-circle fs-5"></i><h3>Chronic</h3></li>
                                                <li data-option="inactive" tabindex="0" role="button"><i class="bi-check-circle fs-5"></i><h3>Inactive</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="addNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="addNotes" rows="4"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer" style="background: transparent !important; border-top-color: rgba(226, 232, 240, 0.3) !important;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveNewMedicalHistory()">
                            <i class="bi bi-check me-1"></i>Add Entry
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .dark #${modalId} .modal-content {
                background: rgba(11, 18, 32, 0.40) !important;
                border: 1px solid rgba(51, 65, 85, 0.3) !important;
                box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
            }
            .dark #${modalId} .modal-header {
                border-bottom-color: rgba(51, 65, 85, 0.3) !important;
            }
            .dark #${modalId} .modal-header .btn-close {
                filter: invert(1) brightness(2);
                opacity: 0.9;
            }
            .dark #${modalId} .modal-header .btn-close:hover {
                opacity: 1;
                filter: invert(1) brightness(2.5);
            }
            .dark #${modalId} .modal-footer {
                border-top-color: rgba(51, 65, 85, 0.3) !important;
            }
            #${modalId} .modal-dialog {
                cursor: default;
                transition: transform 0.2s ease;
                margin: 1.75rem auto;
            }
        </style>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal(modalId);
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Clean up when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function saveNewMedicalHistory() {
    const patientId = document.getElementById('addPatientId').value;
    const formData = {
        condition: document.getElementById('addConditionName').value,
        diagnosis_date: document.getElementById('addDiagnosisDate').value,
        category: document.getElementById('addCategory').value,
        status: document.getElementById('addStatus').value,
        notes: document.getElementById('addNotes').value
    };
    
    // Validate required fields
    if (!formData.condition.trim()) {
        showNotification('Condition name is required', 'danger');
        return;
    }
    
    fetch(`/api/patients/${patientId}/medical-history`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Medical history added successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addMedicalHistoryModal'));
            modal.hide();
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error adding medical history: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding medical history: ' + error.message, 'danger');
    });
}
    
    // Handle edit note buttons
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-note-btn')) {
            const button = e.target.closest('.edit-note-btn');
            const noteId = button.getAttribute('data-note-id');
            const noteTitle = button.getAttribute('data-note-title');
            const noteContent = button.getAttribute('data-note-content');
            
            editPatientNote(noteId, noteTitle, noteContent);
        }
        });
    });

function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const feedback = field.nextElementSibling;
    
    field.classList.add('is-invalid');
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
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
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function escapeHtml(text) {
    if (typeof text !== 'string') {
        return text;
    }
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Patient Files Functions
function showPatientUploadModal(patientId) {
    const modalHtml = `
        <div class="modal fade" id="patientUploadModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Patient File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="patientUploadForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="patient_id" value="${patientId}">
                            
                            <div class="mb-3">
                                <label class="form-label">File Type</label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" name="file_type" required>
                                            <option value="photo" selected>Photo</option>
                                            <option value="medical_record">Medical Record</option>
                                            <option value="xray">X-ray</option>
                                            <option value="ct_scan">CT Scan</option>
                                            <option value="mri">MRI</option>
                                            <option value="ultrasound">Ultrasound</option>
                                            <option value="lab_report">Lab Report</option>
                                            <option value="blood_test">Blood Test</option>
                                            <option value="prescription">Prescription</option>
                                            <option value="insurance">Insurance Document</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">Photo</button>
                                        <menu>
                                            <li data-option="photo" tabindex="0" role="button" class="selected"><i class="bi-paperclip fs-5"></i><h3>Photo</h3></li>
                                            <li data-option="medical_record" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Medical Record</h3></li>
                                            <li data-option="xray" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>X-ray</h3></li>
                                            <li data-option="ct_scan" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>CT Scan</h3></li>
                                            <li data-option="mri" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>MRI</h3></li>
                                            <li data-option="ultrasound" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Ultrasound</h3></li>
                                            <li data-option="lab_report" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Lab Report</h3></li>
                                            <li data-option="blood_test" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Blood Test</h3></li>
                                            <li data-option="prescription" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Prescription</h3></li>
                                            <li data-option="insurance" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Insurance Document</h3></li>
                                            <li data-option="other" tabindex="0" role="button"><i class="bi-paperclip fs-5"></i><h3>Other</h3></li>
                                        </menu>
                                    </div>
                                </section>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">File</label>
                                <input type="file" class="form-control" name="patient_file" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt" required>
                                <div class="form-text" style="color: var(--text);">
                                    Supported Files: Images (JPG, PNG, GIF), PDF, Word Documents, Text Files
                                    <br>Maximum File Size: 5 MB
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" 
                                          placeholder="Add a description for the file (optional)"></textarea>
                            </div>
                            
                            <div id="patientUploadProgress" class="mb-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Uploading...</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="patientUploadBtn">
                                <i class="bi bi-cloud-upload me-2"></i>Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('patientUploadModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('patientUploadModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Handle form submission
    document.getElementById('patientUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = document.getElementById('patientUploadBtn');
        const progressDiv = document.getElementById('patientUploadProgress');
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
                        showNotification('File uploaded successfully', 'success');
                        // Wait a bit for modal to close, then reload patient files via Ajax
                        setTimeout(() => {
                            reloadPatientFiles();
                        }, 300);
                    } else {
                        showNotification('Error: ' + (response.message || 'Upload failed'), 'error');
                    }
                } catch (parseError) {
                    console.error('Response parsing error:', parseError);
                    console.error('Raw response:', xhr.responseText);
                    showNotification('Server response error', 'error');
                }
            } else {
                console.error('HTTP Error:', xhr.status, xhr.statusText);
                showNotification('HTTP Error ' + xhr.status, 'error');
            }
        });
        
        xhr.addEventListener('error', function() {
            showNotification('Upload error', 'error');
            uploadBtn.disabled = false;
            progressDiv.style.display = 'none';
        });
        
        xhr.open('POST', '/api/patients/files/upload');
        xhr.send(formData);
    });
    
    // Clean up modal on hide
    document.getElementById('patientUploadModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function openPatientCameraModal(patientId) {
    const modalHtml = `
        <div class="modal fade" id="patientCameraModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-camera me-2"></i>Take Photo for Patient
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="patientCameraId" value="${patientId}">
                        
                        <div class="mb-3" id="patientPhotoTypeContainer">
                            <label class="form-label">Photo Type</label>
                            <section class="field menu" style="min-width: 100%;">
                                <div class="control">
                                    <select class="form-select d-none" id="patientPhotoType" required>
                                        <option value="medical_photo" selected>Medical Photo</option>
                                        <option value="xray">X-ray</option>
                                        <option value="scan">Scan</option>
                                        <option value="lab_result">Lab Result</option>
                                        <option value="prescription">Prescription</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <button type="button" class="custom-select-toggle" aria-expanded="false">Medical Photo</button>
                                    <menu>
                                        <li data-option="medical_photo" tabindex="0" role="button" class="selected"><i class="bi-camera fs-5"></i><h3>Medical Photo</h3></li>
                                        <li data-option="xray" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>X-ray</h3></li>
                                        <li data-option="scan" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Scan</h3></li>
                                        <li data-option="lab_result" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Lab Result</h3></li>
                                        <li data-option="prescription" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Prescription</h3></li>
                                        <li data-option="other" tabindex="0" role="button"><i class="bi-camera fs-5"></i><h3>Other</h3></li>
                                    </menu>
                                </div>
                            </section>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Photo Description</label>
                            <textarea class="form-control" id="patientPhotoDescription" rows="2" 
                                      placeholder="Add a description for the photo (optional)"></textarea>
                        </div>
                        
                        <!-- Camera View -->
                        <div class="text-center mb-3">
                            <div id="patientCameraContainer" class="border rounded p-3" style="background: #f8f9fa; min-height: 300px;">
                                <video id="patientCameraVideo" width="100%" height="300" style="max-width: 100%; border-radius: 8px; display: none;" autoplay playsinline></video>
                                <canvas id="patientCameraCanvas" width="640" height="480" style="max-width: 100%; border-radius: 8px; display: none;"></canvas>
                                <div id="patientCameraPlaceholder" class="d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 300px;">
                                    <i class="bi bi-camera text-muted" style="font-size: 4rem;"></i>
                                    <p class="text-muted mt-2">Loading camera...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Camera Controls -->
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-success" id="capturePatientPhotoBtn" onclick="capturePatientPhoto()">
                                <i class="bi bi-camera me-2"></i>Take Photo
                            </button>
                            <button type="button" class="btn btn-warning" id="retakePatientPhotoBtn" onclick="retakePatientPhoto()" style="display: none;">
                                <i class="bi bi-arrow-clockwise me-2"></i>Retake
                            </button>
                            <button type="button" class="btn btn-danger" id="stopPatientCameraBtn" onclick="stopPatientCamera()">
                                <i class="bi bi-stop-circle me-2"></i>Stop Camera
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="savePatientPhotoBtn" onclick="savePatientPhoto()" style="display: none;">
                            <i class="bi bi-check-lg me-2"></i>Save Photo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('patientCameraModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('patientCameraModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Start camera automatically when modal is shown
    document.getElementById('patientCameraModal').addEventListener('shown.bs.modal', function() {
        startPatientCamera();
    });
    
    // Clean up modal and stop camera on hide
    document.getElementById('patientCameraModal').addEventListener('hidden.bs.modal', function() {
        stopPatientCamera();
        this.remove();
    });
}

let patientCameraStream = null;
let capturedPatientImageData = null;

function startPatientCamera() {
    const video = document.getElementById('patientCameraVideo');
    const placeholder = document.getElementById('patientCameraPlaceholder');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const stopBtn = document.getElementById('stopPatientCameraBtn');
    const photoTypeContainer = document.getElementById('patientPhotoTypeContainer');
    
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'environment'
        } 
    })
    .then(function(stream) {
        patientCameraStream = stream;
        video.srcObject = stream;
        
        placeholder.style.display = 'none';
        video.style.display = 'block';
        
        // Hide photo type field when camera starts
        if (photoTypeContainer) {
            photoTypeContainer.style.display = 'none';
        }
        
        captureBtn.style.display = 'inline-block';
        stopBtn.style.display = 'inline-block';
        
        showNotification('Camera started successfully', 'success');
    })
    .catch(function(error) {
        showNotification('Error accessing camera: ' + error.message, 'error');
    });
}

function capturePatientPhoto() {
    const video = document.getElementById('patientCameraVideo');
    const canvas = document.getElementById('patientCameraCanvas');
    const context = canvas.getContext('2d');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const retakeBtn = document.getElementById('retakePatientPhotoBtn');
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    canvas.toBlob(function(blob) {
        capturedPatientImageData = blob;
        
        video.style.display = 'none';
        canvas.style.display = 'block';
        
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
        saveBtn.style.display = 'inline-block';
        
        showNotification('Photo captured! You can now save it or retake.', 'success');
    }, 'image/jpeg', 0.8);
}

function retakePatientPhoto() {
    const video = document.getElementById('patientCameraVideo');
    const canvas = document.getElementById('patientCameraCanvas');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const retakeBtn = document.getElementById('retakePatientPhotoBtn');
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    
    capturedPatientImageData = null;
    
    canvas.style.display = 'none';
    video.style.display = 'block';
    
    retakeBtn.style.display = 'none';
    saveBtn.style.display = 'none';
    captureBtn.style.display = 'inline-block';
}

function stopPatientCamera() {
    if (patientCameraStream) {
        patientCameraStream.getTracks().forEach(track => track.stop());
        patientCameraStream = null;
    }
    
    const video = document.getElementById('patientCameraVideo');
    const canvas = document.getElementById('patientCameraCanvas');
    const placeholder = document.getElementById('patientCameraPlaceholder');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const retakeBtn = document.getElementById('retakePatientPhotoBtn');
    const stopBtn = document.getElementById('stopPatientCameraBtn');
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    const photoTypeContainer = document.getElementById('patientPhotoTypeContainer');
    
    if (video) {
        video.style.display = 'none';
        video.srcObject = null;
    }
    
    if (canvas) canvas.style.display = 'none';
    if (placeholder) placeholder.style.display = 'flex';
    
    // Show photo type field when camera stops
    if (photoTypeContainer) {
        photoTypeContainer.style.display = 'block';
    }
    
    if (captureBtn) captureBtn.style.display = 'inline-block';
    if (retakeBtn) retakeBtn.style.display = 'none';
    if (stopBtn) stopBtn.style.display = 'inline-block';
    if (saveBtn) saveBtn.style.display = 'none';
    
    capturedPatientImageData = null;
}

function savePatientPhoto() {
    if (!capturedPatientImageData) {
        showNotification('No photo captured', 'error');
        return;
    }
    
    const patientId = document.getElementById('patientCameraId').value;
    const photoType = document.getElementById('patientPhotoType').value;
    const description = document.getElementById('patientPhotoDescription').value;
    
    if (!photoType) {
        showNotification('Please select a photo type', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('patient_id', patientId);
    formData.append('file_type', photoType);
    formData.append('description', description);
    formData.append('patient_file', capturedPatientImageData, 'patient_photo_' + Date.now() + '.jpg');
    
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    saveBtn.disabled = true;
    
    fetch('/api/patients/files/upload', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('patientCameraModal'));
            modal.hide();
            showNotification('Photo saved successfully', 'success');
            // Wait a bit for modal to close, then reload patient files via Ajax
            setTimeout(() => {
                reloadPatientFiles();
            }, 300);
        } else {
            showNotification('Error: ' + (data.message || 'Save failed'), 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
    });
}

function viewPatientAttachment(attachmentId, filePath, fileExt, isAppointmentAttachment = false) {
    // Check if this is an appointment attachment or patient file
    // Appointment attachments use /api/attachments/view/
    // Patient files use /api/patients/files/view/
    const viewUrl = isAppointmentAttachment 
        ? `/api/attachments/view/${attachmentId}`
        : `/api/patients/files/view/${attachmentId}`;
    
    if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExt.toLowerCase())) {
        showImageModal(viewUrl, attachmentId, isAppointmentAttachment);
    } else if (fileExt.toLowerCase() === 'pdf') {
        window.open(viewUrl, '_blank');
    } else {
        downloadPatientAttachment(attachmentId, null, isAppointmentAttachment);
    }
}

function showImageModal(imageUrl, attachmentId, isAppointmentAttachment = false) {
    const modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">View Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageUrl}" class="img-fluid" style="max-height: 80vh;" alt="Patient Image">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="downloadPatientAttachment(${attachmentId}, null, ${isAppointmentAttachment})">
                            <i class="bi bi-download me-2"></i>Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('imageModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('imageModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Clean up modal on hide
    document.getElementById('imageModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function downloadPatientAttachment(attachmentId, filename, isAppointmentAttachment = false) {
    // Use /api/attachments/download/ for appointment attachments
    // Use /api/patients/files/download/ for patient files
    const downloadUrl = isAppointmentAttachment 
        ? `/api/attachments/download/${attachmentId}`
        : `/api/patients/files/download/${attachmentId}`;
    
    const link = document.createElement('a');
    link.href = downloadUrl;
    if (filename) {
        link.download = filename;
    }
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function deletePatientAttachment(attachmentId) {
    // Hide all tooltips first to prevent conflicts
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.hide();
        }
    });
    
    // Wait a bit for tooltips to hide, then show modal
    setTimeout(() => {
        showGeneralDeleteConfirmationModal(
            'Delete File',
            'Are you sure you want to delete this file? This action cannot be undone.',
            'Delete File',
            () => {
                fetch(`/api/patients/files/${attachmentId}`, {
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
                        showNotification('File deleted successfully', 'success');
                        // Remove file card from DOM
                        const fileCard = document.querySelector(`[data-attachment-id="${attachmentId}"]`);
                        if (fileCard) {
                            fileCard.closest('.col-md-6').remove();
                            // Check if no files left
                            const filesRow = document.getElementById('patientFilesRow');
                            if (filesRow && filesRow.children.length === 0) {
                                filesRow.remove();
                                const emptyMsg = document.getElementById('emptyPatientFilesMessage');
                                if (!emptyMsg) {
                                    const container = document.getElementById('patientFilesContainer');
                                    container.innerHTML = `
                                        <div class="text-center py-4" id="emptyPatientFilesMessage">
                                            <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2 mb-0">No files or documents found for this patient</p>
                                        </div>
                                    `;
                                }
                            }
                        }
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error: ' + error.message, 'error');
                });
            }
        );
    }, 100);
}

// Patient Notes Functions
function showAddPatientNoteModal(patientId) {
    const modalHtml = `
        <div class="modal fade" id="patientNoteModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Medical Note</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="patientNoteForm">
                        <div class="modal-body">
                            <input type="hidden" name="patient_id" value="${patientId}">
                            
                            <div class="mb-3">
                                <label class="form-label">Note Title</label>
                                <input type="text" class="form-control" name="title" required 
                                       placeholder="Enter note title (e.g., General Examination, Follow-up)">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Note Content</label>
                                <textarea class="form-control" name="content" rows="6" required
                                          placeholder="Enter detailed medical note..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Save Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('patientNoteModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('patientNoteModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Handle form submission
    document.getElementById('patientNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/patients/notes', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showNotification('Note added successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + (data.message || 'Failed to add note'), 'error');
            }
        })
        .catch(error => {
            showNotification('Error: ' + error.message, 'error');
        });
    });
    
    // Clean up modal on hide
    document.getElementById('patientNoteModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function editPatientNote(noteId, title, content) {
    // Data is already escaped from HTML attributes, no need to escape again
    // Just ensure we have valid strings
    const safeTitle = title || '';
    const safeContent = content || '';
    
    const modalHtml = `
        <div class="modal fade" id="editPatientNoteModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Medical Note</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editPatientNoteForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Note Title</label>
                                <input type="text" class="form-control" name="title" required 
                                       value="${safeTitle}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Note Content</label>
                                <textarea class="form-control" name="content" rows="6" required>${safeContent}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="updateNoteBtn">
                                <span class="spinner-border spinner-border-sm d-none" id="updateNoteSpinner"></span>
                                <i class="bi bi-check-lg me-2"></i>Update Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('editPatientNoteModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('editPatientNoteModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Handle form submission
    document.getElementById('editPatientNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const updateBtn = document.getElementById('updateNoteBtn');
        const spinner = document.getElementById('updateNoteSpinner');
        
        // Show loading state
        updateBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        // Convert FormData to URLSearchParams for PUT request
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }
        
        fetch(`/api/patients/notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString()
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            if (data.success) {
                modal.hide();
                showNotification('Note updated successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + (data.error || data.message || 'Failed to update note'), 'error');
            }
        })
        .catch(error => {
            console.error('Error updating note:', error);
            showNotification('Error: ' + error.message, 'error');
        })
        .finally(() => {
            // Hide loading state
            updateBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editPatientNoteModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function deletePatientNote(noteId) {
    // Hide all tooltips first to prevent conflicts
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.hide();
        }
    });
    
    // Wait a bit for tooltips to hide, then show modal
    setTimeout(() => {
        showGeneralDeleteConfirmationModal(
            'Delete Note',
            'Are you sure you want to delete this medical note? This action cannot be undone.',
            'Delete Note',
            () => {
                fetch(`/api/patients/notes/${noteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Note deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error: ' + error.message, 'error');
                });
            }
        );
    }, 100);
}

// Confirmation Modal Functions
function showGeneralDeleteConfirmationModal(title, message, buttonText, onConfirm) {
    const modalId = 'deleteConfirmationModal';
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="bi bi-trash me-2"></i>${buttonText}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal(modalId);
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Handle confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        modal.hide();
        onConfirm();
    });
    
    // Clean up modal on hide
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function showInfoModal(title, message) {
    const modalId = 'infoModal';
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background: rgba(248, 250, 252, 0.35) !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.3) !important; box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08); color: var(--text) !important; cursor: move;">
                    <div class="modal-header" style="background: transparent !important; border-bottom-color: rgba(226, 232, 240, 0.3) !important; color: var(--text) !important; user-select: none;">
                        <h5 class="modal-title text-primary">
                            <i class="bi bi-info-circle me-2"></i>
                            ${title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="background: transparent !important; color: var(--text) !important;">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer" style="background: transparent !important; border-top-color: rgba(226, 232, 240, 0.3) !important;">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                            <i class="bi bi-check-circle me-2"></i>OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .dark #${modalId} .modal-content {
                background: rgba(11, 18, 32, 0.40) !important;
                border: 1px solid rgba(51, 65, 85, 0.3) !important;
                box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
            }
            .dark #${modalId} .modal-header {
                border-bottom-color: rgba(51, 65, 85, 0.3) !important;
            }
            .dark #${modalId} .modal-header .btn-close {
                filter: invert(1) brightness(2);
                opacity: 0.9;
            }
            .dark #${modalId} .modal-header .btn-close:hover {
                opacity: 1;
                filter: invert(1) brightness(2.5);
            }
            .dark #${modalId} .modal-footer {
                border-top-color: rgba(51, 65, 85, 0.3) !important;
            }
            #${modalId} .modal-dialog {
                cursor: default;
                transition: transform 0.2s ease;
                margin: 1.75rem auto;
            }
        </style>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Initialize draggable for this modal
    if (typeof initializeDraggableModals === 'function') {
        setTimeout(function() {
            initializeDraggableModals();
        }, 100);
    }
    
    // Clean up modal on hide
        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
            this.remove();
    });
}
// Initialize Bootstrap Tooltips
function initializeTooltips() {
    // Initialize tooltips for elements with data-bs-toggle="tooltip"
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    // Initialize tooltips for elements with title attribute (including dropdown triggers)
    const titleElements = document.querySelectorAll('[title]:not([data-bs-toggle="tooltip"]):not([data-bs-toggle="dropdown"])');
    const titleTooltipList = [...titleElements].map(titleEl => new bootstrap.Tooltip(titleEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    // Initialize tooltips for dropdown buttons with title
    const dropdownTitleElements = document.querySelectorAll('[data-bs-toggle="dropdown"][title]');
    const dropdownTooltipList = [...dropdownTitleElements].map(dropdownEl => new bootstrap.Tooltip(dropdownEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    return [...tooltipList, ...titleTooltipList, ...dropdownTooltipList];
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
    
    // Initialize expand all button states
    setTimeout(() => {
        updateExpandAllAppointmentsButton();
        updateExpandAllMedicationsButton();
        updateExpandAllGlassesButton();
        updateExpandAllPrescriptionsButton();
    }, 100);
});

// Refresh tooltips when modals are shown
document.addEventListener('shown.bs.modal', function() {
    setTimeout(() => {
        refreshTooltips();
    }, 100);
});

// Medical History Functions
function switchMedicalHistoryView(viewType) {
    const timelineView = document.getElementById('timelineView');
    const detailsView = document.getElementById('detailsView');
    const timelineBtn = document.getElementById('timelineViewBtn');
    const detailsBtn = document.getElementById('detailsViewBtn');
    
    if (viewType === 'timeline') {
        timelineView.style.display = 'block';
        detailsView.style.display = 'none';
        timelineBtn.classList.add('active');
        detailsBtn.classList.remove('active');
    } else {
        timelineView.style.display = 'none';
        detailsView.style.display = 'block';
        timelineBtn.classList.remove('active');
        detailsBtn.classList.add('active');
    }
}

function viewMedicalHistory(historyId) {
    const patientId = window.PATIENT_CONFIG.patientId;
    
    // Check if this is an old format entry (from medical_history table)
    // Old format entries don't have individual API endpoints, so we'll handle them differently
    const historyElement = document.querySelector(`[onclick*="viewMedicalHistory(${historyId})"]`);
    if (historyElement) {
        const timelineItem = historyElement.closest('.timeline-item, .accordion-item');
        if (timelineItem && timelineItem.querySelector('[data-entry-type="old_format"]')) {
            showNotification('Viewing old format medical history is not supported yet. Please use the Details view to see all information.', 'info');
            return;
        }
    }
    
    // Fetch and display medical history details in a modal
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMedicalHistoryModal(data.data, 'view');
            } else {
                showNotification('Error loading medical history details: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading medical history details: ' + error.message, 'danger');
        });
}

function editMedicalHistory(historyId) {
    const patientId = window.PATIENT_CONFIG.patientId;
    
    // Check if this is an old format entry (from medical_history table)
    // Old format entries don't have individual API endpoints, so we'll handle them differently
    const historyElement = document.querySelector(`[onclick*="editMedicalHistory(${historyId})"]`);
    if (historyElement) {
        const timelineItem = historyElement.closest('.timeline-item, .accordion-item');
        if (timelineItem && timelineItem.querySelector('[data-entry-type="old_format"]')) {
            showNotification('Editing old format medical history is not supported yet. Please create a new entry with the updated information.', 'info');
            return;
        }
    }
    
    // Fetch and display medical history for editing
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMedicalHistoryModal(data.data, 'edit');
            } else {
                showNotification('Error loading medical history details: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading medical history details: ' + error.message, 'danger');
        });
}

function showMedicalHistoryModal(data, mode) {
    // Create and show modal for viewing/editing medical history
    const modalId = 'medicalHistoryModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const isEdit = mode === 'edit';
    const isView = mode === 'view';
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" style="z-index: 1000000 !important;">
            <div class="modal-dialog modal-lg modal-dialog-centered" style="z-index: 1000001 !important;">
                <div class="modal-content" style="background: rgba(248, 250, 252, 0.35) !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.3) !important; box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08); color: var(--text) !important; cursor: move; z-index: 1000001 !important;">
                    <div class="modal-header" style="background: transparent !important; border-bottom-color: rgba(226, 232, 240, 0.3) !important; color: var(--text) !important; user-select: none;">
                        <h5 class="modal-title">
                            <i class="bi bi-clipboard-heart me-2"></i>
                            ${isEdit ? 'Edit' : 'View'} Medical History
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="background: transparent !important; color: var(--text) !important;">
                        <form id="medicalHistoryForm">
                            <input type="hidden" id="historyId" value="${data.id}">
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="conditionName" class="form-label">Condition Name</label>
                                    <input type="text" class="form-control" id="conditionName" 
                                           value="${data.condition_name || ''}" ${isView ? 'readonly' : ''}>
                                </div>
                                <div class="col-md-4">
                                    <label for="diagnosisDate" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="diagnosisDate" 
                                           value="${data.diagnosis_date || ''}" ${isView ? 'readonly' : ''}>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-select d-none" id="category" ${isView ? 'disabled' : ''}>
                                                <option value="general" ${data.category === 'general' ? 'selected' : ''}>General</option>
                                                <option value="allergy" ${data.category === 'allergy' ? 'selected' : ''}>Allergy</option>
                                                <option value="medication" ${data.category === 'medication' ? 'selected' : ''}>Medication</option>
                                                <option value="surgery" ${data.category === 'surgery' ? 'selected' : ''}>Surgery</option>
                                                <option value="family_history" ${data.category === 'family_history' ? 'selected' : ''}>Family History</option>
                                                <option value="social_history" ${data.category === 'social_history' ? 'selected' : ''}>Social History</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false" ${isView ? 'disabled' : ''}>${data.category ? data.category.charAt(0).toUpperCase() + data.category.slice(1).replace('_', ' ') : 'General'}</button>
                                            <menu>
                                                <li data-option="general" tabindex="0" role="button" ${data.category === 'general' ? 'class="selected"' : ''}><i class="bi-tags fs-5"></i><h3>General</h3></li>
                                                <li data-option="allergy" tabindex="0" role="button" ${data.category === 'allergy' ? 'class="selected"' : ''}><i class="bi-tags fs-5"></i><h3>Allergy</h3></li>
                                                <li data-option="medication" tabindex="0" role="button" ${data.category === 'medication' ? 'class="selected"' : ''}><i class="bi-tags fs-5"></i><h3>Medication</h3></li>
                                                <li data-option="surgery" tabindex="0" role="button" ${data.category === 'surgery' ? 'class="selected"' : ''}><i class="bi-tags fs-5"></i><h3>Surgery</h3></li>
                                                <li data-option="family_history" tabindex="0" role="button" ${data.category === 'family_history' ? 'class="selected"' : ''}><i class="bi-tags fs-5"></i><h3>Family History</h3></li>
                                                <li data-option="social_history" tabindex="0" role="button" ${data.category === 'social_history' ? 'class="selected"' : ''}><i class="bi-tags fs-5"></i><h3>Social History</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-select d-none" id="status" ${isView ? 'disabled' : ''}>
                                                <option value="active" ${data.status === 'active' ? 'selected' : ''}>Active</option>
                                                <option value="resolved" ${data.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                                                <option value="chronic" ${data.status === 'chronic' ? 'selected' : ''}>Chronic</option>
                                                <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false" ${isView ? 'disabled' : ''}>${data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'Active'}</button>
                                            <menu>
                                                <li data-option="active" tabindex="0" role="button" ${data.status === 'active' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>Active</h3></li>
                                                <li data-option="resolved" tabindex="0" role="button" ${data.status === 'resolved' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>Resolved</h3></li>
                                                <li data-option="chronic" tabindex="0" role="button" ${data.status === 'chronic' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>Chronic</h3></li>
                                                <li data-option="inactive" tabindex="0" role="button" ${data.status === 'inactive' ? 'class="selected"' : ''}><i class="bi-check-circle fs-5"></i><h3>Inactive</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" rows="4" ${isView ? 'readonly' : ''}>${data.notes || ''}</textarea>
                            </div>
                            
                            ${data.created_at ? `
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i>
                                    Created: ${new Date(data.created_at).toLocaleDateString()}
                                    ${data.doctor_name ? ` by ${data.doctor_name}` : ''}
                                </div>
                            ` : ''}
                        </form>
                    </div>
                    <div class="modal-footer" style="background: transparent !important; border-top-color: rgba(226, 232, 240, 0.3) !important;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            ${isView ? 'Close' : 'Cancel'}
                        </button>
                        ${isEdit ? `
                            <button type="button" class="btn btn-primary" onclick="saveEditMedicalHistory()">
                                <i class="bi bi-check me-1"></i>Save Changes
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
        <style>
            .dark #${modalId} .modal-content {
                background: rgba(11, 18, 32, 0.40) !important;
                border: 1px solid rgba(51, 65, 85, 0.3) !important;
                box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
            }
            .dark #${modalId} .modal-header {
                border-bottom-color: rgba(51, 65, 85, 0.3) !important;
            }
            .dark #${modalId} .modal-header .btn-close {
                filter: invert(1) brightness(2);
                opacity: 0.9;
            }
            .dark #${modalId} .modal-header .btn-close:hover {
                opacity: 1;
                filter: invert(1) brightness(2.5);
            }
            .dark #${modalId} .modal-footer {
                border-top-color: rgba(51, 65, 85, 0.3) !important;
            }
            #${modalId} .modal-dialog {
                cursor: default;
                transition: transform 0.2s ease;
                margin: 1.75rem auto;
            }
        </style>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal(modalId);
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Clean up when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function saveEditMedicalHistory() {
    const patientId = window.PATIENT_CONFIG.patientId;
    const historyId = document.getElementById('historyId').value;
    const formData = {
        condition: document.getElementById('conditionName').value,
        diagnosis_date: document.getElementById('diagnosisDate').value,
        category: document.getElementById('category').value,
        status: document.getElementById('status').value,
        notes: document.getElementById('notes').value
    };
    
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Medical history updated successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('medicalHistoryModal'));
            modal.hide();
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error updating medical history: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating medical history: ' + error.message, 'danger');
    });
}

function deleteMedicalHistory(historyId) {
    // Check if this is an old format entry (from medical_history table)
    const historyElement = document.querySelector(`[onclick*="deleteMedicalHistory(${historyId})"]`);
    if (historyElement) {
        const timelineItem = historyElement.closest('.timeline-item, .accordion-item');
        if (timelineItem && timelineItem.querySelector('[data-entry-type="old_format"]')) {
            showNotification('Deleting old format medical history is not supported yet. Please contact administrator for assistance.', 'warning');
            return;
        }
    }
    
    showDeleteConfirmationModal(historyId);
}

function showDeleteConfirmationModal(historyId) {
    const modalId = 'deleteConfirmationModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 mb-2">Delete Medical History Entry</h6>
                            <p class="text-muted">Are you sure you want to delete this medical history entry? This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteMedicalHistory(${historyId})">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal(modalId);
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Clean up when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function confirmDeleteMedicalHistory(historyId) {
    const patientId = window.PATIENT_CONFIG.patientId;
    
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`, {
        method: 'DELETE'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Medical history deleted successfully', 'success');
            // Hide the confirmation modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
            modal.hide();
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error deleting medical history: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting medical history: ' + error.message, 'danger');
    });
}

// Glasses Prescriptions Functions
function showAddGlassesPrescriptionModal(patientId) {
    const modalId = 'addGlassesPrescriptionModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-eyeglasses me-2"></i>
                            Add Glasses Prescription
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addGlassesPrescriptionForm">
                            <input type="hidden" id="glassesPatientId" value="${patientId}">
                            
                            <!-- Lens Type and Appointment Selection -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="glassesAppointmentId" class="form-label">Select Appointment *</label>
                                    <select class="form-select" id="glassesAppointmentId" required>
                                        <option value="">Loading appointments...</option>
                                    </select>
                            </div>
                                <div class="col-md-6">
                                    <label for="glassesLensType" class="form-label">Lens Type *</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-select d-none" id="glassesLensType" required>
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
                            
                            <!-- Distance Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="distanceSphereR" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Sphere power</div>
                            </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="distanceCylinderR" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Cylinder power</div>
                    </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="distanceAxisR" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*">
                                                    <div class="form-text">Axis (0-180)</div>
                    </div>
                </div>
            </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="distanceSphereL" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Sphere power</div>
        </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="distanceCylinderL" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Cylinder power</div>
                    </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="distanceAxisL" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*">
                                                    <div class="form-text">Axis (0-180)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                            
                            <!-- Near Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-book me-2"></i>Near Vision</h6>
                    </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="nearSphereR" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Sphere power</div>
                    </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="nearCylinderR" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Cylinder power</div>
                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="nearAxisR" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*">
                                                    <div class="form-text">Axis (0-180)</div>
            </div>
        </div>
                    </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="nearSphereL" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Sphere power</div>
                        </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="nearCylinderL" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                                    <div class="form-text">Cylinder power</div>
                    </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="nearAxisL" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*">
                                                    <div class="form-text">Axis (0-180)</div>
                    </div>
                </div>
            </div>
        </div>
                                </div>
                            </div>
                            
                            <!-- PD and Comments -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pdDistance" class="form-label">PD Distance (mm)</label>
                                    <input type="text" class="form-control" id="pdDistance" 
                                           placeholder="62.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 62.0, +2, -1)</div>
                    </div>
                                <div class="col-md-6">
                                    <label for="pdNear" class="form-label">PD Near (mm)</label>
                                    <input type="text" class="form-control" id="pdNear" 
                                           placeholder="60.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 60.0, +2, -1)</div>
                        </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="glassesComments" class="form-label">Comments</label>
                                <textarea class="form-control" id="glassesComments" rows="3" 
                                          placeholder="Additional notes or comments about the prescription..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveGlassesPrescription()">
                            <i class="bi bi-check me-1"></i>Add Prescription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal(modalId);
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Load patient appointments
    loadPatientAppointments(patientId);
    
    // Clean up when modal is hidden
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function loadPatientAppointments(patientId) {
    const select = document.getElementById('glassesAppointmentId');
    
    fetch(`/api/patients/${patientId}/appointments`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                select.innerHTML = '<option value="">Select an appointment</option>';
                data.data.forEach(appointment => {
                    const option = document.createElement('option');
                    option.value = appointment.id;
                    option.textContent = `${appointment.date} at ${appointment.start_time} - ${appointment.visit_type}`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No appointments available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            select.innerHTML = '<option value="">Error loading appointments</option>';
        });
}

function saveGlassesPrescription() {
    const formData = {
        appointment_id: document.getElementById('glassesAppointmentId').value,
        lens_type: document.getElementById('glassesLensType').value,
        distance_sphere_r: document.getElementById('distanceSphereR').value || null,
        distance_cylinder_r: document.getElementById('distanceCylinderR').value || null,
        distance_axis_r: document.getElementById('distanceAxisR').value || null,
        distance_sphere_l: document.getElementById('distanceSphereL').value || null,
        distance_cylinder_l: document.getElementById('distanceCylinderL').value || null,
        distance_axis_l: document.getElementById('distanceAxisL').value || null,
        near_sphere_r: document.getElementById('nearSphereR').value || null,
        near_cylinder_r: document.getElementById('nearCylinderR').value || null,
        near_axis_r: document.getElementById('nearAxisR').value || null,
        near_sphere_l: document.getElementById('nearSphereL').value || null,
        near_cylinder_l: document.getElementById('nearCylinderL').value || null,
        near_axis_l: document.getElementById('nearAxisL').value || null,
        PD_DISTANCE: document.getElementById('pdDistance').value || null,
        PD_NEAR: document.getElementById('pdNear').value || null,
        comments: document.getElementById('glassesComments').value || null
    };
    
    // Validate required fields
    if (!formData.appointment_id) {
        showNotification('Please select an appointment', 'danger');
        return;
    }
    
    if (!formData.lens_type) {
        showNotification('Please select a lens type', 'danger');
        return;
    }
    
    // Convert FormData to URLSearchParams for POST request
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(formData)) {
        if (value !== null && value !== '') {
            params.append(key, value);
        }
    }
    
    fetch('/api/prescriptions/glasses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Glasses prescription added successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addGlassesPrescriptionModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (data.error || data.message || 'Failed to add prescription'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error: ' + error.message, 'error');
    });
}

function viewGlassesPrescription(prescriptionId) {
    const url = `/api/prescriptions/glasses/${prescriptionId}?t=${Date.now()}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        },
        cache: 'no-cache'
    })
        .then(response => {
            if (!response.ok) {
                // Log the response text for debugging
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status} - ${text.substring(0, 100)}`);
                });
            }
            return response.json();
        })
    .then(data => {
        if (data.success) {
                showGlassesPrescriptionModal(data.data, 'view');
        } else {
                showNotification('Error loading prescription details: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
            console.error('View Error:', error);
            showNotification('Error: ' + error.message, 'error');
        });
}

function editGlassesPrescription(prescriptionId) {
    const url = `/api/prescriptions/glasses/${prescriptionId}?t=${Date.now()}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        },
        cache: 'no-cache'
    })
        .then(response => {
            if (!response.ok) {
                // Log the response text for debugging
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status} - ${text.substring(0, 100)}`);
                });
            }
            return response.json();
        })
    .then(data => {
        if (data.success) {
                showGlassesPrescriptionModal(data.data, 'edit');
        } else {
                showNotification('Error loading prescription details: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
            console.error('Edit Error:', error);
            showNotification('Error: ' + error.message, 'error');
        });
}

function showGlassesPrescriptionModal(data, mode) {
    const modalId = 'viewEditGlassesPrescriptionModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const isEdit = mode === 'edit';
    const isView = mode === 'view';
    const readonly = isView ? 'readonly' : '';
    const disabled = isView ? 'disabled' : '';
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-eyeglasses me-2"></i>
                            ${isEdit ? 'Edit' : 'View'} Glasses Prescription
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editGlassesPrescriptionForm">
                            <input type="hidden" id="editPrescriptionId" value="${data.id}">
                            
                            <!-- Lens Type -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Lens Type</label>
                                    <section class="field menu" style="min-width: 100%;">
                                        <div class="control">
                                            <select class="form-select d-none" id="editLensType" ${disabled}>
                                                <option value="Single Vision" ${data.lens_type === 'Single Vision' ? 'selected' : ''}>Single Vision</option>
                                                <option value="Bifocal" ${data.lens_type === 'Bifocal' ? 'selected' : ''}>Bifocal</option>
                                                <option value="Progressive" ${data.lens_type === 'Progressive' ? 'selected' : ''}>Progressive</option>
                                                <option value="Reading" ${data.lens_type === 'Reading' ? 'selected' : ''}>Reading</option>
                                            </select>
                                            <button type="button" class="custom-select-toggle" aria-expanded="false" ${disabled}>${data.lens_type || 'Single Vision'}</button>
                                            <menu>
                                                <li data-option="Single Vision" tabindex="0" role="button" ${data.lens_type === 'Single Vision' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Single Vision</h3></li>
                                                <li data-option="Bifocal" tabindex="0" role="button" ${data.lens_type === 'Bifocal' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Bifocal</h3></li>
                                                <li data-option="Progressive" tabindex="0" role="button" ${data.lens_type === 'Progressive' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Progressive</h3></li>
                                                <li data-option="Reading" tabindex="0" role="button" ${data.lens_type === 'Reading' ? 'class="selected"' : ''}><i class="bi-eye fs-5"></i><h3>Reading</h3></li>
                                            </menu>
                                        </div>
                                    </section>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Created Date</label>
                                    <input type="text" class="form-control" value="${new Date(data.created_at).toLocaleString()}" readonly>
                                </div>
                                </div>
                            
                            <!-- Distance Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                <div class="col-md-6">
                                            <h6 class="text-primary">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="editDistanceSphereR" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.distance_sphere_r || ''}" ${readonly}>
                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="editDistanceCylinderR" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.distance_cylinder_r || ''}" ${readonly}>
                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="editDistanceAxisR" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*" value="${data.distance_axis_r || ''}" ${readonly}>
                            </div>
                                </div>
                                </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="editDistanceSphereL" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.distance_sphere_l || ''}" ${readonly}>
                            </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="editDistanceCylinderL" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.distance_cylinder_l || ''}" ${readonly}>
                            </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="editDistanceAxisL" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*" value="${data.distance_axis_l || ''}" ${readonly}>
                    </div>
                    </div>
                </div>
            </div>
        </div>
                            </div>
                            
                            <!-- Near Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-book me-2"></i>Near Vision</h6>
                    </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="editNearSphereR" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.near_sphere_r || ''}" ${readonly}>
                        </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="editNearCylinderR" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.near_cylinder_r || ''}" ${readonly}>
                            </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="editNearAxisR" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*" value="${data.near_axis_r || ''}" ${readonly}>
                            </div>
                            </div>
                    </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="text" class="form-control" id="editNearSphereL" 
                                                           placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.near_sphere_l || ''}" ${readonly}>
                    </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="text" class="form-control" id="editNearCylinderL" 
                                                           placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.near_cylinder_l || ''}" ${readonly}>
                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="text" class="form-control" id="editNearAxisL" 
                                                           placeholder="0, 90, 180" pattern="[0-9]*" value="${data.near_axis_l || ''}" ${readonly}>
            </div>
        </div>
                            </div>
                                        </div>
                                        </div>
                                        </div>
                            
                            <!-- PD and Comments -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                    <label class="form-label">PD Distance (mm)</label>
                                    <input type="text" class="form-control" id="editPdDistance" 
                                           placeholder="62.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.PD_DISTANCE || ''}" ${readonly}>
                                        </div>
                                        <div class="col-md-6">
                                    <label class="form-label">PD Near (mm)</label>
                                    <input type="text" class="form-control" id="editPdNear" 
                                           placeholder="60.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*" value="${data.PD_NEAR || ''}" ${readonly}>
                                        </div>
                                    </div>
                            
                                    <div class="mb-3">
                                <label class="form-label">Comments</label>
                                <textarea class="form-control" id="editGlassesComments" rows="3" ${readonly}>${data.comments || ''}</textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            ${isView ? 'Close' : 'Cancel'}
                                </button>
                        ${isEdit ? `
                            <button type="button" class="btn btn-primary" onclick="updateGlassesPrescription()">
                                <i class="bi bi-check me-1"></i>Save Changes
                            </button>
                        ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal(modalId);
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    // Clean up when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
                this.remove();
    });
}

function updateGlassesPrescription() {
    const prescriptionId = document.getElementById('editPrescriptionId').value;
    const formData = {
        lens_type: document.getElementById('editLensType').value,
        distance_sphere_r: document.getElementById('editDistanceSphereR').value || null,
        distance_cylinder_r: document.getElementById('editDistanceCylinderR').value || null,
        distance_axis_r: document.getElementById('editDistanceAxisR').value || null,
        distance_sphere_l: document.getElementById('editDistanceSphereL').value || null,
        distance_cylinder_l: document.getElementById('editDistanceCylinderL').value || null,
        distance_axis_l: document.getElementById('editDistanceAxisL').value || null,
        near_sphere_r: document.getElementById('editNearSphereR').value || null,
        near_cylinder_r: document.getElementById('editNearCylinderR').value || null,
        near_axis_r: document.getElementById('editNearAxisR').value || null,
        near_sphere_l: document.getElementById('editNearSphereL').value || null,
        near_cylinder_l: document.getElementById('editNearCylinderL').value || null,
        near_axis_l: document.getElementById('editNearAxisL').value || null,
        PD_DISTANCE: document.getElementById('editPdDistance').value || null,
        PD_NEAR: document.getElementById('editPdNear').value || null,
        comments: document.getElementById('editGlassesComments').value || null
    };
    
    // Convert FormData to URLSearchParams for PUT request
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(formData)) {
        if (value !== null && value !== '') {
            params.append(key, value);
        }
    }
    
    fetch(`/api/prescriptions/glasses/${prescriptionId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Glasses prescription updated successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('viewEditGlassesPrescriptionModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (data.error || data.message || 'Failed to update prescription'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error: ' + error.message, 'error');
    });
}

function deleteGlassesPrescription(prescriptionId) {
    // Hide all tooltips first to prevent conflicts
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.hide();
        }
    });
    
    // Wait a bit for tooltips to hide, then show modal
    setTimeout(() => {
        showGeneralDeleteConfirmationModal(
            'Delete Glasses Prescription',
            'Are you sure you want to delete this glasses prescription? This action cannot be undone.',
            'Delete Prescription',
            () => {
                fetch(`/api/prescriptions/glasses/${prescriptionId}`, {
        method: 'DELETE',
        headers: {
                        'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Glasses prescription deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
        } else {
                        showNotification('Error: ' + (data.error || data.message), 'error');
        }
    })
    .catch(error => {
                    showNotification('Error: ' + error.message, 'error');
                });
            }
        );
    }, 100);
}

function printGlassesPrescription(prescriptionId) {
    const printUrl = `/print/glasses-prescription/${prescriptionId}?t=${Date.now()}`;
    window.open(printUrl, '_blank');
}

// Helper function to clear browser cache for specific URLs
function clearApiCache() {
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
                                }
                            });
                        }
                    }

// Call this when the page loads to ensure fresh data
document.addEventListener('DOMContentLoaded', function() {
    // Clear any cached API responses
    clearApiCache();
    
    // Add a refresh handler for the glasses prescriptions section
    // Find the card that contains "Glasses Prescriptions" in its h5
    const allCards = document.querySelectorAll('.card');
    let glassesSection = null;
    for (let card of allCards) {
        const h5 = card.querySelector('h5');
        if (h5 && h5.textContent.includes('Glasses Prescriptions')) {
            glassesSection = card;
            break;
        }
    }
    
    if (glassesSection) {
        // Add a small refresh button to the glasses prescriptions header
        const header = glassesSection.querySelector('.card-header .d-flex');
        if (header && !header.querySelector('.refresh-glasses-btn')) {
            const refreshBtn = document.createElement('button');
            refreshBtn.className = 'btn btn-outline-secondary btn-sm me-2 refresh-glasses-btn';
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
            refreshBtn.title = 'Refresh glasses prescriptions';
            refreshBtn.onclick = function() {
                clearApiCache();
                location.reload();
            };
            header.appendChild(refreshBtn);
        }
    }
});

// Prescription History Functions
function viewAllMedicationsForAppointment(button) {
    const medications = JSON.parse(button.getAttribute('data-medications-data'));
    const appointmentId = button.getAttribute('data-appointment-id');
    const appointmentDate = button.getAttribute('data-appointment-date');
    const appointmentTime = button.getAttribute('data-appointment-time');
    const doctorName = button.getAttribute('data-doctor-name');
    
    let medicationsHtml = '';
    medications.forEach((med, index) => {
        medicationsHtml += `
            <div class="card mb-3" style="border-left: 3px solid var(--success);">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="bi bi-capsule me-2"></i>
                        ${escapeHtml(med.drug_name || 'N/A')}
                    </h6>
                    ${med.notes ? `
                    <p class="card-text text-muted mb-0">
                        <i class="bi bi-sticky me-1"></i>
                        ${escapeHtml(med.notes)}
                    </p>
                    ` : ''}
                </div>
            </div>
        `;
    });
            
            const modalHtml = `
        <div class="modal fade" id="allMedicationsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-capsule me-2 text-success"></i>
                            All Medication Prescriptions - Appointment #${appointmentId}
                                </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                        <div class="mb-3">
                            <div class="row">
                                    <div class="col-md-6">
                                    <strong>Appointment Date:</strong>
                                    <p class="mb-0">${new Date(appointmentDate).toLocaleDateString()} at ${new Date(appointmentDate + ' ' + appointmentTime).toLocaleTimeString()}</p>
                                    </div>
                                    <div class="col-md-6">
                                    <strong>Doctor:</strong>
                                    <p class="mb-0">${escapeHtml(doctorName || 'N/A')}</p>
                                    </div>
                                </div>
                                </div>
                        <hr>
                        <h6 class="mb-3">
                            <i class="bi bi-list-ul me-2"></i>
                            Prescriptions (${medications.length})
                        </h6>
                        ${medicationsHtml}
                            </div>
                            <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" onclick="printMedicationPrescription(${appointmentId})">
                            <i class="bi bi-printer me-2"></i>Print All Prescriptions
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('allMedicationsModal');
    const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('allMedicationsModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    modalElement.addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function viewMedicationDetails(button) {
    const medData = JSON.parse(button.getAttribute('data-med-data'));
    const modalHtml = `
        <div class="modal fade" id="medicationDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-capsule me-2 text-success"></i>
                            Medication Prescription Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Drug Name:</strong>
                                <p class="mb-0">${escapeHtml(medData.drug_name || 'N/A')}</p>
                                </div>
                            <div class="col-md-6">
                                <strong>Appointment Date:</strong>
                                <p class="mb-0">${new Date(medData.appointment_date).toLocaleDateString()} at ${new Date(medData.appointment_date + ' ' + medData.appointment_time).toLocaleTimeString()}</p>
                            </div>
                        </div>
                        ${medData.notes ? `
                        <div class="mb-3">
                            <strong>Notes:</strong>
                            <p class="mb-0">${escapeHtml(medData.notes)}</p>
                        </div>
                        ` : ''}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Doctor:</strong>
                                <p class="mb-0">${escapeHtml(medData.doctor_display_name || medData.doctor_name || 'N/A')}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Appointment ID:</strong>
                                <p class="mb-0">#${medData.appointment_id}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" onclick="printMedicationPrescription(${medData.appointment_id})">
                            <i class="bi bi-printer me-2"></i>Print Prescription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('medicationDetailsModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('medicationDetailsModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    modalElement.addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function viewGlassesDetails(button) {
    const glassData = JSON.parse(button.getAttribute('data-glass-data'));
    const modalHtml = `
        <div class="modal fade" id="glassesDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-eyeglasses me-2 text-info"></i>
                            Glasses Prescription Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Lens Type:</strong>
                                <p class="mb-0">${escapeHtml(glassData.lens_type || 'N/A')}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Appointment Date:</strong>
                                <p class="mb-0">${new Date(glassData.appointment_date).toLocaleDateString()} at ${new Date(glassData.appointment_date + ' ' + glassData.appointment_time).toLocaleTimeString()}</p>
                        </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-primary">Right Eye (OD)</h6>
                                <p class="mb-1">
                                    <strong>SPH:</strong> ${glassData.distance_sphere_r || 'N/A'}<br>
                                    <strong>CYL:</strong> ${glassData.distance_cylinder_r || 'N/A'}<br>
                                    <strong>AXIS:</strong> ${glassData.distance_axis_r || 'N/A'}
                                </p>
                                <h6 class="text-success mt-3">Near Vision</h6>
                                <p class="mb-1">
                                    <strong>SPH:</strong> ${glassData.near_sphere_r || 'N/A'}<br>
                                    <strong>CYL:</strong> ${glassData.near_cylinder_r || 'N/A'}<br>
                                    <strong>AXIS:</strong> ${glassData.near_axis_r || 'N/A'}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Left Eye (OS)</h6>
                                <p class="mb-1">
                                    <strong>SPH:</strong> ${glassData.distance_sphere_l || 'N/A'}<br>
                                    <strong>CYL:</strong> ${glassData.distance_cylinder_l || 'N/A'}<br>
                                    <strong>AXIS:</strong> ${glassData.distance_axis_l || 'N/A'}
                                </p>
                                <h6 class="text-success mt-3">Near Vision</h6>
                                <p class="mb-1">
                                    <strong>SPH:</strong> ${glassData.near_sphere_l || 'N/A'}<br>
                                    <strong>CYL:</strong> ${glassData.near_cylinder_l || 'N/A'}<br>
                                    <strong>AXIS:</strong> ${glassData.near_axis_l || 'N/A'}
                                </p>
                            </div>
                        </div>
                        ${glassData.PD_DISTANCE || glassData.PD_NEAR ? `
                        <div class="row mb-3">
                            ${glassData.PD_DISTANCE ? `
                            <div class="col-md-6">
                                <strong>PD Distance:</strong>
                                <p class="mb-0">${glassData.PD_DISTANCE}mm</p>
                            </div>
                            ` : ''}
                            ${glassData.PD_NEAR ? `
                            <div class="col-md-6">
                                <strong>PD Near:</strong>
                                <p class="mb-0">${glassData.PD_NEAR}mm</p>
                            </div>
                            ` : ''}
                        </div>
                        ` : ''}
                        ${glassData.comments ? `
                        <div class="mb-3">
                            <strong>Comments:</strong>
                            <p class="mb-0">${escapeHtml(glassData.comments)}</p>
                        </div>
                        ` : ''}
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Doctor:</strong>
                                <p class="mb-0">${escapeHtml(glassData.doctor_display_name || glassData.doctor_name || 'N/A')}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Appointment ID:</strong>
                                <p class="mb-0">#${glassData.appointment_id}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" onclick="printGlassesPrescription(${glassData.appointment_id})">
                            <i class="bi bi-printer me-2"></i>Print Prescription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElement = document.getElementById('glassesDetailsModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Apply glass style and draggable
    setTimeout(function() {
        applyGlassStyleToModal('glassesDetailsModal');
        if (typeof initializeDraggableModals === 'function') {
            initializeDraggableModals();
        }
    }, 50);
    
    modalElement.addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function printMedicationPrescription(appointmentId) {
    const printUrl = `/print/prescription/${appointmentId}?t=${Date.now()}`;
    window.open(printUrl, '_blank');
}

// Reload patient files via Ajax
function reloadPatientFiles() {
    const patientId = window.PATIENT_CONFIG.patientId;
    if (!patientId) {
        console.error('No patient ID found');
        return;
    }
    
    fetch(`/api/patients/${patientId}/files`, {
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
            if (data.success && data.files !== undefined) {
                const container = document.getElementById('patientFilesContainer');
                if (!container) {
                    console.error('patientFilesContainer not found');
        return;
    }
    
                if (data.files.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4" id="emptyPatientFilesMessage">
                            <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">No files or documents found for this patient</p>
                        </div>
                    `;
                } else {
                    let html = '<div class="row" id="patientFilesRow">';
                    data.files.forEach(file => {
                        const fileExt = file.original_filename.split('.').pop().toLowerCase();
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExt);
                        const viewUrl = `/api/patients/files/view/${file.id}`;
                        
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
                        }
                        
                        const displayName = file.original_filename.length > 20 
                            ? file.original_filename.substring(0, 10) + '...' 
                            : file.original_filename;
                        const fileSize = (file.file_size / 1024).toFixed(1);
                        const createdDate = new Date(file.created_at).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        // Escape special characters for safe use in HTML/JavaScript
                        const safeFilePath = (file.file_path || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        const safeFileName = (file.original_filename || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        const safeDescription = (file.description || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="attachment-card p-2 border rounded" data-attachment-id="${file.id}" style="min-height: ${isImage ? '200px' : '140px'}; display: flex; flex-direction: column;">
                                    ${isImage ? `
                                    <div class="mb-2 text-center" style="cursor: pointer;" 
                                         onclick="viewPatientAttachment(${file.id}, '${safeFilePath}', '${fileExt}')"
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         data-bs-title="View Attachement/Photo">
                                        <img src="${viewUrl}" 
                                             alt="${safeFileName}"
                                             class="img-thumbnail" 
                                             style="max-width: 100%; max-height: 120px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="display: none; width: 100%; height: 120px; background: #f8f9fa; border-radius: 8px; align-items: center; justify-content: center; flex-direction: column;">
                                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                            <small class="text-muted">Image not available</small>
                    </div>
                                    </div>
                                    ` : ''}
                                    <div class="d-flex align-items-center mb-2 flex-grow-1">
                                        <i class="bi ${iconClass} text-primary me-2" style="font-size: 1.2rem; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <h6 class="mb-0" style="font-size: 0.8rem; line-height: 1.1;" 
                                                    title="${safeFileName}"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top">
                                                    ${displayName}
                                                </h6>
                                                <span class="badge ${badgeClass} ms-2" style="font-size: 0.6rem;">
                                                    ${fileType}
                                                </span>
                                </div>
                                            <small class="text-muted d-block" style="font-size: 0.65rem;">
                                                ${fileSize} KB
                                            </small>
                                            <small class="text-muted d-block" style="font-size: 0.65rem;">
                                                ${createdDate}
                                            </small>
                            </div>
                                    </div>
                                    ${file.description ? `
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small" style="font-size: 0.7rem;"
                                           title="${safeDescription}"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="bottom">
                                           ${file.description.length > 40 ? file.description.substring(0, 37) + '...' : file.description}
                                        </p>
                                </div>
                                    ` : '<div class="flex-grow-1"></div>'}
                                    <div class="btn-group btn-group-sm w-100 mt-auto" role="group">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="viewPatientAttachment(${file.id}, '${safeFilePath}', '${fileExt}')" 
                                                style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="View Attachement/Photo">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="downloadPatientAttachment(${file.id}, '${safeFileName}')"
                                                style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                            <i class="bi bi-download me-1"></i>Download
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="deletePatientAttachment(${file.id})"
                                                style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                </div>
            </div>
        </div>
    `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                    
                    // Reinitialize tooltips
                    var tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            } else {
                console.error('Invalid response format:', data);
                if (data.message) {
                    showNotification('Error loading files: ' + data.message, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error reloading patient files:', error);
            showNotification('Error reloading files. Please refresh the page.', 'error');
            // Fallback: reload page after 2 seconds
            setTimeout(() => location.reload(), 2000);
        });
}

// Load Patient Alerts
function loadPatientAlerts() {
    const patientId = window.PATIENT_CONFIG.patientId;
    if (!patientId) {
        document.getElementById('patientAlertsContainer').innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-bell text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No patient selected</p>
            </div>
        `;
            return;
        }
        
    fetch(`/api/alerts/patient/${patientId}`, {
        method: 'GET',
            headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        const container = document.getElementById('patientAlertsContainer');
        const countBadge = document.getElementById('patientAlertsCount');
        
        if (data.success && data.alerts && data.alerts.length > 0) {
            countBadge.textContent = data.alerts.length;
            
            let html = '<div class="list-group">';
            data.alerts.forEach(alert => {
                const alertDate = alert.alert_date ? new Date(alert.alert_date).toLocaleDateString('en-GB') : 'N/A';
                const alertTime = alert.alert_time || 'N/A';
                const isActive = alert.is_active == 1;
                const isDismissed = alert.is_dismissed == 1;
                
                // Determine alert status class
                let alertStatusClass = '';
                if (isDismissed) {
                    alertStatusClass = 'alert-dismissed';
                } else if (isActive) {
                    alertStatusClass = 'alert-active';
            } else {
                    alertStatusClass = 'alert-inactive';
                }
                
                // Check if alert date/time has passed
                const alertDateTime = alert.alert_date && alert.alert_time 
                    ? new Date(`${alert.alert_date}T${alert.alert_time}`) 
                    : null;
                const isPast = alertDateTime && alertDateTime < new Date();
                
                const statusBadge = isDismissed 
                    ? '<span class="badge bg-secondary">Dismissed</span>' 
                    : (isActive 
                        ? (isPast 
                            ? '<span class="badge bg-warning">Past Due</span>' 
                            : '<span class="badge bg-success">Active</span>')
                        : '<span class="badge bg-secondary">Inactive</span>');
                
                html += `
                    <div class="list-group-item ${alertStatusClass}" data-alert-id="${alert.id}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-bell-fill me-2"></i>
                                    <h6 class="mb-0"><div class="alert-message-content" style="word-wrap: break-word;">${alert.message || 'No message'}</div></h6>
                                    </div>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar me-1"></i>${alertDate}
                                    <i class="bi bi-clock ms-3 me-1"></i>${alertTime}
                                    ${alert.repeat_count > 1 ? `<span class="ms-3"><i class="bi bi-arrow-repeat me-1"></i>Repeat: ${alert.repeat_count} times</span>` : ''}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-3">
                                ${statusBadge}
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="editPatientAlert(${alert.id})" title="Edit Alert">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="showDeletePatientAlertModal(${alert.id})" title="Delete Alert">
                                        <i class="bi bi-trash"></i>
                                    </button>
                            </div>
                        </div>
            </div>
        </div>
    `;
            });
            html += '</div>';
            container.innerHTML = html;
            } else {
            countBadge.textContent = '0';
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-bell text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2 mb-0">No alerts found for this patient</p>
                    <button class="btn btn-primary btn-sm mt-2" onclick="openAlertModal(${patientId}, null)">
                        <i class="bi bi-plus me-1"></i>Add First Alert
                    </button>
                </div>
            `;
            }
        })
        .catch(error => {
        console.error('Error loading patient alerts:', error);
        document.getElementById('patientAlertsContainer').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Error loading alerts. Please try again.
            </div>
        `;
    });
}

// Edit Patient Alert
function editPatientAlert(alertId) {
    fetch(`/api/alerts/${alertId}`, {
        method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
        if (data.success && data.alert) {
            const alert = data.alert;
            const patientId = window.PATIENT_CONFIG.patientId;
            openAlertModal(patientId, alert.appointment_id || null, alert);
            } else {
            alert('Error loading alert details');
            }
        })
        .catch(error => {
        console.error('Error loading alert:', error);
        alert('Error loading alert details');
    });
}

// Show Delete Patient Alert Modal
let currentPatientAlertIdToDelete = null;

function showDeletePatientAlertModal(alertId) {
    if (!alertId) {
        return;
    }
    
    // Store alert ID
    currentPatientAlertIdToDelete = alertId;
    const confirmBtn = document.getElementById('confirmDeletePatientAlertBtn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-alert-id', alertId);
    }
    
    const modal = new bootstrap.Modal(document.getElementById('deletePatientAlertModal'));
    modal.show();
}

// Confirm Delete Patient Alert
function confirmDeletePatientAlert() {
    // Try to get alert ID from multiple sources
    let alertId = currentPatientAlertIdToDelete;
    
    // Fallback: try to get from button data attribute
    if (!alertId) {
        const confirmBtn = document.getElementById('confirmDeletePatientAlertBtn');
        if (confirmBtn && confirmBtn.getAttribute('data-alert-id')) {
            alertId = confirmBtn.getAttribute('data-alert-id');
        }
    }
    
    if (!alertId) {
        return;
    }
    
    // Disable button during deletion
    const confirmBtn = document.getElementById('confirmDeletePatientAlertBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
    }
    
    fetch(`/api/alerts/${alertId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deletePatientAlertModal'));
        
        // Re-enable button
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Alert';
        }
        
        if (data.success) {
            modal.hide();
            currentPatientAlertIdToDelete = null;
            if (confirmBtn) {
                confirmBtn.removeAttribute('data-alert-id');
            }
            
            loadPatientAlerts();
            
            // Show success message
            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                    <i class="bi bi-check-circle me-2"></i>Alert deleted successfully
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            setTimeout(() => {
                const alertEl = document.querySelector('.alert-success');
                if (alertEl) alertEl.remove();
            }, 3000);
        } else {
            // Don't hide modal on error so user can try again
            const errorHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>${data.message || 'Failed to delete alert'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            const modalBody = document.querySelector('#deletePatientAlertModal .modal-body');
            if (modalBody) {
                const existingAlert = modalBody.querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }
                modalBody.insertAdjacentHTML('afterbegin', errorHtml);
            }
        }
    })
    .catch(error => {
        console.error('Error deleting alert:', error);
        
        // Re-enable button
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Alert';
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deletePatientAlertModal'));
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>Error deleting alert. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        const modalBody = document.querySelector('#deletePatientAlertModal .modal-body');
        if (modalBody) {
            const existingAlert = modalBody.querySelector('.alert-danger');
            if (existingAlert) {
                existingAlert.remove();
            }
            modalBody.insertAdjacentHTML('afterbegin', errorHtml);
        }
    });
}

// Reset delete modal when closed
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deletePatientAlertModal');
    if (deleteModal) {
        deleteModal.addEventListener('hidden.bs.modal', function() {
            // Clear any error messages
            const errorAlert = this.querySelector('.alert-danger');
            if (errorAlert) {
                errorAlert.remove();
            }
            // Reset button state
            const confirmBtn = document.getElementById('confirmDeletePatientAlertBtn');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Alert';
            }
        });
    }
});

// Store current patient info globally for alert modal
window.currentPatientInfo = {
    id: window.PATIENT_CONFIG.patientId,
    first_name: window.PATIENT_CONFIG.patientFirstName,
    last_name: window.PATIENT_CONFIG.patientLastName,
    phone: window.PATIENT_CONFIG.patientPhone,
    age: window.PATIENT_CONFIG.patientAge,
};

// Load alerts when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadPatientAlerts();
    loadPatientForumTopics();
    
    // Reload alerts after alert modal is closed (if alert was created/updated)
    const alertModal = document.getElementById('alertModal');
    if (alertModal) {
        alertModal.addEventListener('hidden.bs.modal', function() {
            // Small delay to ensure server has processed the request
            setTimeout(() => {
                loadPatientAlerts();
            }, 500);
        });
    }
});

// Forum Topics Section
async function loadPatientForumTopics() {
    const patientId = window.PATIENT_CONFIG.patientId;
    if (!patientId) return;
    
    try {
        const response = await fetch(`/api/forum/topics/patient/${patientId}`);
        const data = await response.json();
        
        if (data.success) {
            renderPatientForumTopics(data.topics);
        }
    } catch (error) {
        console.error('Error loading forum topics:', error);
    }
}

function renderPatientForumTopics(topics) {
    const container = document.getElementById('patientForumTopics');
    if (!container) return;
    
    if (topics.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <p class="text-muted">No forum topics yet for this patient.</p>
                <button class="btn btn-primary btn-sm" onclick="createPatientForumTopic()">
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
            <button class="btn btn-primary btn-sm" onclick="createPatientForumTopic()">
                <i class="bi bi-plus-circle me-1"></i>Create New Topic
            </button>
        </div>
    `;
    
    container.innerHTML = html;
}

function createPatientForumTopic() {
    const patientId = window.PATIENT_CONFIG ? window.PATIENT_CONFIG.patientId : null;
    if (patientId) {
        window.location.href = `/doctor/forum?patient_id=${patientId}&create=true`;
    } else {
        window.location.href = `/doctor/forum?create=true`;
    }
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

// =========================================
// Custom Select Menu Logic (from appointment.js)
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

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initCustomSelects();
});

// Also initialize when modals are shown
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});
