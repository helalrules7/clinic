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
                        <div class="alert-editor-wrapper">
                            <div class="btn-group btn-group-sm mb-2" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="setAlertEditorMode('text')" id="alertEditorTextBtn">
                                    <i class="bi bi-type"></i> Text
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setAlertEditorMode('html')" id="alertEditorHtmlBtn">
                                    <i class="bi bi-code-slash"></i> HTML
                                </button>
                            </div>
                            <textarea class="form-control" id="alertMessage" name="message" rows="3" required placeholder="Enter alert message..."></textarea>
                            <div id="alertMessageHtmlEditor" class="form-control" contenteditable="true" style="display: none; min-height: 100px; max-height: 300px; overflow-y: auto;" placeholder="Enter HTML content..."></div>
                            <small class="text-muted">You can use HTML formatting in alerts</small>
                        </div>
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
/* Alert Modal Glass Effect - Same as sidebar */
#alertModal .modal-content {
    /* Glass effect - similar to sidebar */
    background: rgba(248, 250, 252, 0.35) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
    color: var(--text) !important;
}

.dark #alertModal .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
}

#alertModal .modal-header {
    background: transparent !important;
    border-bottom-color: rgba(226, 232, 240, 0.3) !important;
    color: var(--text) !important;
}

.dark #alertModal .modal-header {
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
}

/* Close button white in dark mode */
.dark #alertModal .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

.dark #alertModal .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

/* Enable dragging */
#alertModal .modal-content {
    cursor: move;
}

#alertModal .modal-dialog {
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
}

#alertModal .modal-header {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

#alertModal .modal-body {
    background: transparent !important;
    color: var(--text) !important;
}

#alertModal .modal-footer {
    background: transparent !important;
    border-top-color: rgba(226, 232, 240, 0.3) !important;
}

.dark #alertModal .modal-footer {
    border-top-color: rgba(51, 65, 85, 0.3) !important;
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

/* Alert Editor Styles */
.alert-editor-wrapper {
    position: relative;
}

#alertMessageHtmlEditor {
    border: 2px solid var(--border);
    border-radius: 6px;
    padding: 0.75rem;
    background: var(--card);
    color: var(--text);
    min-height: 100px;
    max-height: 300px;
    overflow-y: auto;
    font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.5;
}

#alertMessageHtmlEditor:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
}

#alertMessageHtmlEditor:empty:before {
    content: attr(placeholder);
    opacity: 0.5;
    pointer-events: none;
}

.dark #alertMessageHtmlEditor {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark #alertMessageHtmlEditor:focus {
    border-color: var(--accent);
}

.alert-editor-wrapper .btn-group .btn.active {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.dark .alert-editor-wrapper .btn-group .btn.active {
    background-color: var(--accent);
    border-color: var(--accent);
    color: #0b1220;
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
        // Check if message contains HTML tags
        const hasHtml = /<[a-z][\s\S]*>/i.test(alertData.message || '');
        if (hasHtml) {
            setAlertEditorMode('html');
            document.getElementById('alertMessageHtmlEditor').innerHTML = alertData.message || '';
        } else {
            setAlertEditorMode('text');
            document.getElementById('alertMessage').value = alertData.message || '';
        }
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
        setAlertEditorMode('text');
        document.getElementById('alertMessage').value = '';
        document.getElementById('alertMessageHtmlEditor').innerHTML = '';
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

// Make alertModal draggable (separate from main.php to avoid conflicts)
function initializeAlertModalDraggable() {
    const modal = document.getElementById('alertModal');
    if (!modal) return;
    
    const modalDialog = modal.querySelector('.modal-dialog');
    if (!modalDialog) return;
    
    const modalHeader = modal.querySelector('.modal-header');
    if (!modalHeader) return;
    
    let isDragging = false;
    let currentX = 0;
    let currentY = 0;
    let initialX = 0;
    let initialY = 0;
    let xOffset = 0;
    let yOffset = 0;
    
    modalHeader.style.cursor = 'move';
    
    function setTranslate(xPos, yPos, el) {
        // Get viewport dimensions
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Get modal dimensions
        const modalRect = el.getBoundingClientRect();
        const modalWidth = modalRect.width;
        const modalHeight = modalRect.height;
        
        // Get the original position (center of viewport)
        // For Bootstrap centered modal, it's typically at 50% from top (not 50px)
        const originalLeft = (viewportWidth - modalWidth) / 2;
        const originalTop = (viewportHeight - modalHeight) / 2;
        
        // Calculate boundaries relative to original position
        // Allow movement within viewport bounds for both X and Y axes
        const minX = -(originalLeft - 20); // Allow 20px from left edge
        const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
        const minY = -(originalTop - 20); // Allow 20px from top
        const maxY = viewportHeight - modalHeight - originalTop + 20; // Allow 20px from bottom
        
        // Constrain movement on both axes
        const constrainedX = Math.max(minX, Math.min(maxX, xPos));
        const constrainedY = Math.max(minY, Math.min(maxY, yPos));
        
        // Apply transform to both X and Y axes
        el.style.transform = 'translate(' + constrainedX + 'px, ' + constrainedY + 'px)';
    }
    
    function drag(e) {
        if (isDragging) {
            e.preventDefault();
            e.stopPropagation();
            
            // Calculate new position based on mouse movement on both axes
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            
            // Update offsets for both axes
            xOffset = currentX;
            yOffset = currentY;
            
            // Apply translation to both X and Y
            setTranslate(currentX, currentY, modalDialog);
        }
    }
    
    function dragEnd(e) {
        if (isDragging) {
            initialX = currentX;
            initialY = currentY;
            isDragging = false;
            modalDialog.style.transition = '';
        }
    }
    
    function dragStart(e) {
        // Don't drag if clicking on buttons or inputs
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
            return;
        }
        
        // Only start dragging if clicking on header (not on title text)
        if (e.target === modalHeader || (modalHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
            // Get current transform values to continue from current position
            const transform = modalDialog.style.transform;
            if (transform) {
                const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                if (match) {
                    xOffset = parseFloat(match[1]) || 0;
                    yOffset = parseFloat(match[2]) || 0;
                }
            }
            
            // Calculate initial position for both axes
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
            
            // Store start position to detect drag vs click
            const startX = e.clientX;
            const startY = e.clientY;
            let hasMoved = false;
            
            function checkMove(moveEvent) {
                // Check movement on both axes (X and Y)
                const deltaX = Math.abs(moveEvent.clientX - startX);
                const deltaY = Math.abs(moveEvent.clientY - startY);
                // Start dragging if moved more than 5px on either axis
                if (deltaX > 5 || deltaY > 5) {
                    hasMoved = true;
                    isDragging = true;
                    modalDialog.style.transition = 'none';
                    // Update initial positions when dragging starts
                    initialX = moveEvent.clientX - xOffset;
                    initialY = moveEvent.clientY - yOffset;
                    moveEvent.preventDefault();
                    moveEvent.stopPropagation();
                }
            }
            
            function handleMove(moveEvent) {
                if (hasMoved) {
                    drag(moveEvent);
                } else {
                    checkMove(moveEvent);
                }
            }
            
            function handleEnd(endEvent) {
                if (!hasMoved) {
                    // It was just a click, allow normal behavior
                    document.removeEventListener('mousemove', handleMove);
                    document.removeEventListener('mouseup', handleEnd);
                    return;
                }
                dragEnd(endEvent);
                document.removeEventListener('mousemove', handleMove);
                document.removeEventListener('mouseup', handleEnd);
            }
            
            document.addEventListener('mousemove', handleMove);
            document.addEventListener('mouseup', handleEnd);
        }
    }
    
    modalHeader.addEventListener('mousedown', dragStart);
    
    modal.addEventListener('hidden.bs.modal', function() {
        xOffset = 0;
        yOffset = 0;
        modalDialog.style.transform = '';
    });
}

// Initialize patient search on modal show
document.addEventListener('DOMContentLoaded', function() {
    const alertModal = document.getElementById('alertModal');
    if (alertModal) {
        const patientSearchField = document.getElementById('alertPatientSearch');
        if (patientSearchField) {
            patientSearchField.addEventListener('input', debounce(searchAlertPatients, 300));
        }
        
        alertModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('alertPatientSearch').value = '';
            document.getElementById('alertPatientSearchResults').innerHTML = '';
        });
    }
    
    initializeAlertModalDraggable();
});

// Set alert editor mode (text or HTML)
function setAlertEditorMode(mode) {
    const textEditor = document.getElementById('alertMessage');
    const htmlEditor = document.getElementById('alertMessageHtmlEditor');
    const textBtn = document.getElementById('alertEditorTextBtn');
    const htmlBtn = document.getElementById('alertEditorHtmlBtn');
    
    if (!textEditor || !htmlEditor || !textBtn || !htmlBtn) return;
    
    if (mode === 'html') {
        // Switch to HTML mode
        textEditor.style.display = 'none';
        textEditor.removeAttribute('required');
        htmlEditor.style.display = 'block';
        htmlEditor.setAttribute('required', 'required');
        
        // Transfer content if text editor has content
        if (textEditor.value && !htmlEditor.innerHTML.trim()) {
            htmlEditor.innerHTML = escapeHtml(textEditor.value).replace(/\n/g, '<br>');
        }
        
        // Update button states
        textBtn.classList.remove('active');
        htmlBtn.classList.add('active');
    } else {
        // Switch to text mode
        htmlEditor.style.display = 'none';
        htmlEditor.removeAttribute('required');
        textEditor.style.display = 'block';
        textEditor.setAttribute('required', 'required');
        
        // Transfer content if HTML editor has content
        if (htmlEditor.innerHTML && !textEditor.value) {
            // Strip HTML tags and convert <br> to newlines
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlEditor.innerHTML;
            textEditor.value = tempDiv.textContent || tempDiv.innerText || '';
        }
        
        // Update button states
        htmlBtn.classList.remove('active');
        textBtn.classList.add('active');
    }
}

// Escape HTML function
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function saveAlert() {
    const form = document.getElementById('alertForm');
    if (!form || !form.checkValidity()) {
        if (form) {
            form.reportValidity();
        }
        return;
    }
    
    const alertDate = document.getElementById('alertDate').value;
    const alertTime = document.getElementById('alertTime').value;
    
    if (!alertDate || !alertTime) {
        if (typeof showToast === 'function') {
            showToast('error', 'Error', 'Please fill in all required fields.');
        }
        return;
    }
    
    const alertDateTime = new Date(alertDate + 'T' + alertTime);
    const isFuture = alertDateTime > new Date();
    const wasInactive = originalAlertData && (originalAlertData.is_active == 0 || originalAlertData.is_dismissed == 1);
    
    // Get message from appropriate editor (text or HTML)
    let alertMessage = '';
    const htmlEditor = document.getElementById('alertMessageHtmlEditor');
    const textEditor = document.getElementById('alertMessage');
    
    if (htmlEditor && htmlEditor.style.display !== 'none') {
        // HTML mode
        alertMessage = htmlEditor.innerHTML;
    } else {
        // Text mode
        alertMessage = textEditor.value;
    }
    
    const formData = {
        patient_id: document.getElementById('alertPatientId').value || null,
        appointment_id: document.getElementById('alertAppointmentId').value || null,
        message: alertMessage,
        alert_date: alertDate,
        alert_time: alertTime,
        repeat_count: parseInt(document.getElementById('alertRepeatCount').value) || 1,
        repeat_interval: parseInt(document.getElementById('alertRepeatInterval').value) || 0
    };
    
    const isEditMode = currentAlertIdToEdit !== null;
    
    if (isEditMode && isFuture && wasInactive) {
        formData.is_active = 1;
        formData.is_dismissed = 0;
    } else if (isEditMode) {
        formData.is_active = originalAlertData ? (originalAlertData.is_active || 0) : 1;
        formData.is_dismissed = originalAlertData ? (originalAlertData.is_dismissed || 0) : 0;
    }
    
    const url = isEditMode ? '/api/alerts/' + currentAlertIdToEdit : '/api/alerts';
    const method = isEditMode ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            const modalElement = document.getElementById('alertModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
            
            currentAlertIdToEdit = null;
            originalAlertData = null;
            
            if (typeof showToast === 'function') {
                const title = isEditMode ? 'Alert Updated' : 'Alert Created';
                const message = isEditMode ? 'The alert has been updated successfully.' : 'The alert has been added to your notifications.';
                showToast('success', title, message);
            }
            
            if (typeof loadAlerts === 'function') {
                loadAlerts();
            }
            
            if (typeof loadPatientAlerts === 'function') {
                loadPatientAlerts();
            }
        } else {
            if (typeof showToast === 'function') {
                const errorMsg = data.message || 'Failed to ' + (isEditMode ? 'update' : 'create') + ' alert';
                showToast('error', 'Error', errorMsg);
            }
        }
    })
    .catch(function(error) {
        if (typeof showToast === 'function') {
            const errorMsg = 'Failed to ' + (isEditMode ? 'update' : 'create') + ' alert. Please try again.';
            showToast('error', 'Error', errorMsg);
        }
    });
}
</script>

