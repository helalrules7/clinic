<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">
                    <i class="bi bi-bell me-2"></i>Create Alert
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="alertForm">
                    <input type="hidden" id="alertPatientId" name="patient_id">
                    <input type="hidden" id="alertAppointmentId" name="appointment_id">
                    
                    <div class="mb-3">
                        <label for="alertPatientSearch" class="form-label">
                            <i class="bi bi-person me-1"></i>Patient (Optional)
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="alertPatientSearch" 
                                   placeholder="Search patient by name or phone...">
                        </div>
                        <div id="alertPatientSearchResults" class="search-results"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alertMessage" class="form-label">
                            <i class="bi bi-chat-text me-1"></i>Alert Message <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="alertMessage" name="message" rows="3" required placeholder="Enter alert message..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alertDate" class="form-label">
                                <i class="bi bi-calendar me-1"></i>Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="alertDate" name="alert_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="alertTime" class="form-label">
                                <i class="bi bi-clock me-1"></i>Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control" id="alertTime" name="alert_time" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alertRepeatCount" class="form-label">
                                <i class="bi bi-arrow-repeat me-1"></i>Repeat Count
                            </label>
                            <input type="number" class="form-control" id="alertRepeatCount" name="repeat_count" min="1" max="100" value="1">
                            <small class="text-muted">Number of times to show this alert (0 = infinite)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="alertRepeatInterval" class="form-label">
                                <i class="bi bi-calendar-week me-1"></i>Repeat Interval (Days)
                            </label>
                            <input type="number" class="form-control" id="alertRepeatInterval" name="repeat_interval" min="0" value="0">
                            <small class="text-muted">Days between each repeat (0 = same day only)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAlert()">
                    <i class="bi bi-check-circle me-1"></i>Create Alert
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Alert Modal Dark Mode Support */
#alertModal .modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#alertModal .modal-header {
    background-color: var(--bg) !important;
    border-bottom-color: var(--border) !important;
    color: var(--text) !important;
}

#alertModal .modal-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

#alertModal .modal-footer {
    background-color: var(--card) !important;
    border-top-color: var(--border) !important;
}

#alertModal .form-label {
    color: var(--text) !important;
}

#alertModal .form-control,
#alertModal .form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#alertModal .form-control:focus,
#alertModal .form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
}

#alertModal .text-muted {
    color: var(--muted) !important;
}

/* Patient Search Results Styles */
#alertModal .search-results {
    position: relative;
    z-index: 1000;
    margin-top: 0.5rem;
}

#alertModal .search-result-item {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-top: none;
    background: var(--card);
    cursor: pointer;
    transition: background-color 0.2s ease;
}

#alertModal .search-result-item:first-child {
    border-top: 1px solid var(--border);
    border-radius: 8px 8px 0 0;
}

#alertModal .search-result-item:last-child {
    border-radius: 0 0 8px 8px;
}

#alertModal .search-result-item:only-child {
    border-radius: 8px;
}

#alertModal .search-result-item:hover {
    background: var(--bg);
}

#alertModal .patient-name {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.25rem;
}

#alertModal .patient-details {
    font-size: 0.875rem;
    color: var(--muted);
}

#alertModal .selected-patient-info {
    margin-top: 0.5rem;
    border-radius: 8px;
    border: 1px solid #b3d9ff;
    background: rgba(13, 110, 253, 0.1);
    padding: 0.75rem;
}
</style>

<script>
// Debounce function
function debounce(func, wait) {
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

// Alert Modal Functions
let currentAlertIdToEdit = null;
let originalAlertData = null; // Store original alert data for comparison

function openAlertModal(patientId, appointmentId, alertData = null) {
    // Reset to create mode or set edit mode
    currentAlertIdToEdit = alertData ? alertData.id : null;
    originalAlertData = alertData ? {...alertData} : null; // Store copy of original data
    
    // Clear patient search
    document.getElementById('alertPatientSearch').value = '';
    document.getElementById('alertPatientSearchResults').innerHTML = '';
    document.getElementById('alertPatientId').value = patientId || '';
    document.getElementById('alertAppointmentId').value = appointmentId || '';
    
    if (alertData) {
        // Edit mode - populate form with alert data
        document.getElementById('alertMessage').value = alertData.message || '';
        document.getElementById('alertDate').value = alertData.alert_date || '';
        document.getElementById('alertTime').value = alertData.alert_time || '';
        document.getElementById('alertRepeatCount').value = alertData.repeat_count || '1';
        document.getElementById('alertRepeatInterval').value = alertData.repeat_interval || '0';
        
        // Set patient info if available
        if (alertData.patient_id) {
            const fullName = alertData.patient_first_name && alertData.patient_last_name 
                ? `${alertData.patient_first_name} ${alertData.patient_last_name}` 
                : '';
            if (fullName) {
                document.getElementById('alertPatientSearch').value = fullName;
                document.getElementById('alertPatientId').value = alertData.patient_id;
                
                document.getElementById('alertPatientSearchResults').innerHTML = `
                    <div class="selected-patient-info alert alert-info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Selected Patient:</strong> ${fullName}<br>
                                <small>Phone: ${alertData.patient_phone || 'N/A'}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                                Change Patient
                            </button>
                        </div>
                    </div>
                `;
            }
        }
        
        // Update modal title and button
        document.getElementById('alertModalLabel').innerHTML = '<i class="bi bi-bell me-2"></i>Edit Alert';
        const saveBtn = document.querySelector('#alertModal .modal-footer .btn-primary');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Update Alert';
            saveBtn.setAttribute('onclick', 'saveAlert()');
        }
    } else {
        // Create mode - clear form
        document.getElementById('alertMessage').value = '';
        document.getElementById('alertDate').value = '';
        document.getElementById('alertTime').value = '';
        document.getElementById('alertRepeatCount').value = '1';
        document.getElementById('alertRepeatInterval').value = '0';
        
        // Check if we have patient info from the page (for patient.php)
        const currentPatientInfo = window.currentPatientInfo || null;
        
        // If patientId is provided, set patient info immediately if available, otherwise fetch
        if (patientId) {
            // If we have patient info from the page, use it immediately
            if (currentPatientInfo && currentPatientInfo.id == patientId) {
                const fullName = `${currentPatientInfo.first_name} ${currentPatientInfo.last_name}`;
                document.getElementById('alertPatientSearch').value = fullName;
                document.getElementById('alertPatientId').value = patientId;
                
                // Show selected patient info immediately
                document.getElementById('alertPatientSearchResults').innerHTML = `
                    <div class="selected-patient-info alert alert-info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Selected Patient:</strong> ${fullName}<br>
                                <small>Phone: ${currentPatientInfo.phone || 'N/A'} • Age: ${currentPatientInfo.age || 'N/A'}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                                Change Patient
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // Fetch patient info from API
                fetch(`/api/patients/${patientId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok && data.data) {
                            const patient = data.data;
                            const fullName = `${patient.first_name} ${patient.last_name}`;
                            document.getElementById('alertPatientSearch').value = fullName;
                            document.getElementById('alertPatientId').value = patientId;
                            
                            // Show selected patient info
                            document.getElementById('alertPatientSearchResults').innerHTML = `
                                <div class="selected-patient-info alert alert-info">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>Selected Patient:</strong> ${fullName}<br>
                                            <small>Phone: ${patient.phone || 'N/A'} • Age: ${patient.age || 'N/A'}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                                            Change Patient
                                        </button>
                                    </div>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.debug('Error fetching patient:', error);
                    });
            }
        }
        
        // Update modal title and button
        document.getElementById('alertModalLabel').innerHTML = '<i class="bi bi-bell me-2"></i>Create Alert';
        const saveBtn = document.querySelector('#alertModal .modal-footer .btn-primary');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Create Alert';
            saveBtn.setAttribute('onclick', 'saveAlert()');
        }
        
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('alertDate').value = today;
        
        // Set default time to current time + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('alertTime').value = `${hours}:${minutes}`;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    modal.show();
}

// Patient search for alert modal
function searchAlertPatients() {
    const query = document.getElementById('alertPatientSearch').value.trim();
    if (query.length < 2) {
        document.getElementById('alertPatientSearchResults').innerHTML = '';
        return;
    }
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayAlertPatientSearchResults(data.data);
            }
        })
        .catch(error => {
            console.debug('Error searching patients:', error);
        });
}

function displayAlertPatientSearchResults(patients) {
    const resultsContainer = document.getElementById('alertPatientSearchResults');
    
    if (patients.length === 0) {
        resultsContainer.innerHTML = '<div class="search-result-item text-muted">No patients found</div>';
        return;
    }
    
    let html = '';
    patients.forEach(patient => {
        html += `
            <div class="search-result-item" onclick="selectAlertPatient(${patient.id}, '${patient.first_name} ${patient.last_name}', '${patient.phone || ''}', ${patient.age || 'null'})">
                <div class="patient-name">${patient.first_name} ${patient.last_name}</div>
                <div class="patient-details">${patient.phone || 'N/A'} • Age: ${patient.age || 'N/A'}</div>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

function selectAlertPatient(patientId, patientName, phone, age) {
    document.getElementById('alertPatientId').value = patientId;
    document.getElementById('alertPatientSearch').value = patientName;
    document.getElementById('alertPatientSearchResults').innerHTML = `
        <div class="selected-patient-info alert alert-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>Selected Patient:</strong> ${patientName}<br>
                    <small>Phone: ${phone || 'N/A'} • Age: ${age || 'N/A'}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                    Change Patient
                </button>
            </div>
        </div>
    `;
}

function clearAlertPatientSelection() {
    document.getElementById('alertPatientId').value = '';
    document.getElementById('alertPatientSearch').value = '';
    document.getElementById('alertPatientSearchResults').innerHTML = '';
}

// Initialize patient search on modal show
document.addEventListener('DOMContentLoaded', function() {
    const alertModal = document.getElementById('alertModal');
    if (alertModal) {
        // Add event listener for patient search
        const patientSearchField = document.getElementById('alertPatientSearch');
        if (patientSearchField) {
            patientSearchField.addEventListener('input', debounce(searchAlertPatients, 300));
        }
        
        // Clear search results when modal is hidden
        alertModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('alertPatientSearch').value = '';
            document.getElementById('alertPatientSearchResults').innerHTML = '';
        });
    }
});

function saveAlert() {
    const form = document.getElementById('alertForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const alertDate = document.getElementById('alertDate').value;
    const alertTime = document.getElementById('alertTime').value;
    
    // Check if the new date/time is in the future
    const alertDateTime = alertDate && alertTime ? new Date(`${alertDate}T${alertTime}`) : null;
    const isFuture = alertDateTime && alertDateTime > new Date();
    
    // Check if alert was previously inactive or dismissed
    const wasInactive = originalAlertData && (originalAlertData.is_active == 0 || originalAlertData.is_dismissed == 1);
    
    const formData = {
        patient_id: document.getElementById('alertPatientId').value || null,
        appointment_id: document.getElementById('alertAppointmentId').value || null,
        message: document.getElementById('alertMessage').value,
        alert_date: alertDate,
        alert_time: alertTime,
        repeat_count: parseInt(document.getElementById('alertRepeatCount').value) || 1,
        repeat_interval: parseInt(document.getElementById('alertRepeatInterval').value) || 0
    };
    
    // If editing and new date/time is in the future, reactivate the alert
    const isEditMode = currentAlertIdToEdit !== null;
    if (isEditMode && isFuture && wasInactive) {
        formData.is_active = 1;
        formData.is_dismissed = 0;
    } else if (isEditMode) {
        // Preserve current state if not changing to future date
        formData.is_active = originalAlertData ? (originalAlertData.is_active || 0) : 1;
        formData.is_dismissed = originalAlertData ? (originalAlertData.is_dismissed || 0) : 0;
    }
    
    const url = isEditMode ? `/api/alerts/${currentAlertIdToEdit}` : '/api/alerts';
    const method = isEditMode ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('alertModal'));
            modal.hide();
            
            // Reset edit mode
            currentAlertIdToEdit = null;
            originalAlertData = null;
            
            // Show success message
            if (typeof showToast === 'function') {
                showToast('success', isEditMode ? 'Alert Updated' : 'Alert Created', 
                    isEditMode ? 'The alert has been updated successfully.' : 'The alert has been added to your notifications.');
            }
            
            // Reload alerts if on alerts page
            if (typeof loadAlerts === 'function') {
                loadAlerts();
            }
            
            // Reload patient alerts if on patient page
            if (typeof loadPatientAlerts === 'function') {
                loadPatientAlerts();
            }
        } else {
            if (typeof showToast === 'function') {
                showToast('error', 'Error', data.message || `Failed to ${isEditMode ? 'update' : 'create'} alert`);
            }
        }
    })
    .catch(error => {
        if (typeof showToast === 'function') {
            showToast('error', 'Error', `Failed to ${isEditMode ? 'update' : 'create'} alert. Please try again.`);
        }
    });
}
</script>

