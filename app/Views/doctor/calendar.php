<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3">Calendar</h4>
            <div class="refresh-indicator d-flex align-items-center">
                <i class="bi bi-arrow-clockwise me-2"></i>
                <small class="text-muted">Auto-refresh every 60s</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-success" id="addAppointmentBtn">
                <i class="bi bi-plus-circle me-2"></i>
                Add Appointment
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" id="todayBtn">Today</button>
                <button type="button" class="btn btn-outline-primary" id="prevDayBtn">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="nextDayBtn">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="currentDateDisplay">
                    <?= date('l, F j, Y') ?>
                </h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2" id="statusIndicator">
                        <i class="bi bi-circle-fill me-1"></i>
                        Live
                    </span>
                    <small class="text-muted" id="lastUpdate">
                        Last updated: <?= date('H:i:s') ?>
                    </small>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="calendarContainer">
                    <!-- Calendar will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="startVisitBtn" style="display: none;">
                    <i class="bi bi-play-circle me-2"></i>
                    Start Visit
                </button>
                <button type="button" class="btn btn-success" id="completeVisitBtn" style="display: none;">
                    <i class="bi bi-check-circle me-2"></i>
                    Complete
                </button>
                <button type="button" class="btn btn-warning" id="rescheduleBtn" style="display: none;">
                    <i class="bi bi-calendar-event me-2"></i>
                    Reschedule
                </button>
                <button type="button" class="btn btn-danger" id="cancelBtn" style="display: none;">
                    <i class="bi bi-x-circle me-2"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cancelForm">
                    <div class="mb-3">
                        <label for="cancellationReason" class="form-label">Cancellation Reason *</label>
                        <textarea class="form-control" id="cancellationReason" name="cancellation_reason" 
                                  rows="3" required placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="bi bi-x-circle me-2"></i>
                    Confirm Cancellation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAppointmentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patientSearch" class="form-label">
                                    Patient * 
                                    <span id="preselectedLabel" class="badge bg-info ms-2" style="display: none;">Pre-selected</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="patientSearch" 
                                           placeholder="Search patient by name or phone..." required>
                                    <button type="button" class="btn btn-outline-primary" id="newPatientBtn">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="selectedPatientId" name="patient_id">
                                <div id="patientSearchResults" class="search-results"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentDate" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="appointmentDate" name="date" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentTime" class="form-label">Time *</label>
                                <select class="form-select" id="appointmentTime" name="start_time" required>
                                    <option value="">Select time slot...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="visitType" class="form-label">Visit Type *</label>
                                <select class="form-select" id="visitType" name="visit_type" required>
                                    <option value="">Select visit type...</option>
                                    <option value="New">New Patient</option>
                                    <option value="FollowUp">Follow Up</option>
                                    <option value="Procedure">Procedure</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentSource" class="form-label">Source</label>
                                <select class="form-select" id="appointmentSource" name="source">
                                    <option value="Walk-in">Walk-in</option>
                                    <option value="Phone">Phone</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="appointmentNotes" name="notes" 
                                          rows="3" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveAppointmentBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        Save Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentDate = new Date();
let selectedAppointment = null;
let refreshInterval;
let preselectedPatient = <?= $preselectedPatient ? json_encode($preselectedPatient) : 'null' ?>;

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    loadCalendar();
    startAutoRefresh();
    setupEventListeners();
});

function setupEventListeners() {
    // Navigation buttons
    document.getElementById('todayBtn').addEventListener('click', () => {
        currentDate = new Date();
        loadCalendar();
    });
    
    document.getElementById('prevDayBtn').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() - 1);
        loadCalendar();
    });
    
    document.getElementById('nextDayBtn').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() + 1);
        loadCalendar();
    });
    
    // Add appointment button
    document.getElementById('addAppointmentBtn').addEventListener('click', () => {
        // Use current date being viewed in calendar
        openAddAppointmentModal(null, currentDate.toISOString().split('T')[0]);
    });
    
    // Patient search
    document.getElementById('patientSearch').addEventListener('input', debounce(searchPatients, 300));
    
    // Date change - load available time slots
    document.getElementById('appointmentDate').addEventListener('change', () => {
        // Keep any preselected time when date changes
        const currentSelectedTime = document.getElementById('appointmentTime').value;
        loadAvailableTimeSlots(currentSelectedTime || null);
    });
    
    // Add appointment form submission
    document.getElementById('addAppointmentForm').addEventListener('submit', handleAddAppointment);
    
    // New patient button
    document.getElementById('newPatientBtn').addEventListener('click', () => {
        // Redirect to new patient page or open modal
        window.open('/secretary/patients/new', '_blank');
    });
}

function loadCalendar() {
    const dateStr = currentDate.toISOString().split('T')[0];
    const doctorId = <?= $doctorId ?>;
    
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderCalendar(data.data);
                updateDateDisplay();
                updateLastUpdate();
            } else {
                console.error('Error loading calendar:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading calendar:', error);
        });
}

function renderCalendar(data) {
    const container = document.getElementById('calendarContainer');
    const timeSlots = generateTimeSlots();
    
    let html = '<div class="calendar-grid">';
    
    // Header row
    html += '<div class="calendar-header">';
    html += '<div class="time-column">Time</div>';
    html += '<div class="appointment-column">Appointments</div>';
    html += '</div>';
    
    // Time slots
    timeSlots.forEach(time => {
        const appointment = data.appointments.find(apt => apt.start_time === time);
        const isAvailable = data.available_slots.includes(time);
        const unavailableSlot = data.unavailable_slots ? data.unavailable_slots.find(slot => slot.time === time) : null;
        
        html += '<div class="calendar-row">';
        html += `<div class="time-slot">${formatTime(time)}</div>`;
        html += '<div class="appointment-slot">';
        
        if (appointment) {
            html += renderAppointmentSlot(appointment);
        } else if (isAvailable) {
            html += `<div class="available-slot" onclick="quickAddAppointment('${time}')" 
                          title="Click to schedule appointment at ${formatTime(time)}">
                        <i class="bi bi-plus-circle me-2"></i>Available - ${formatTime(time)}
                     </div>`;
        } else {
            // Show detailed unavailable information
            if (unavailableSlot && unavailableSlot.doctor_name) {
                html += `<div class="unavailable-slot" title="${unavailableSlot.reason}">
                           <i class="bi bi-person-fill-lock me-2"></i>Unavailable - ${unavailableSlot.reason}
                         </div>`;
            } else if (unavailableSlot && unavailableSlot.reason === 'Outside working hours') {
                html += `<div class="unavailable-slot outside-hours" title="Outside working hours">
                           <i class="bi bi-clock me-2"></i>Unavailable - Outside working hours
                         </div>`;
            } else {
                html += '<div class="unavailable-slot">Unavailable</div>';
            }
        }
        
        html += '</div>';
        html += '</div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function renderAppointmentSlot(appointment) {
    const statusClass = getStatusBadgeClass(appointment.status);
    const visitTypeClass = getVisitTypeBadgeClass(appointment.visit_type);
    
    return `
        <div class="appointment-card ${appointment.status.toLowerCase()}" 
             onclick="showAppointmentDetails(${appointment.id})">
            <div class="appointment-header">
                <span class="patient-name">${appointment.patient_name}</span>
                <span class="${statusClass}">${appointment.status}</span>
            </div>
            <div class="appointment-details">
                <span class="${visitTypeClass}">${appointment.visit_type}</span>
                <span class="appointment-time">${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</span>
            </div>
            <div class="appointment-notes">
                ${appointment.notes ? appointment.notes.substring(0, 50) + '...' : 'No notes'}
            </div>
        </div>
    `;
}

function generateTimeSlots() {
    const slots = [];
    const start = new Date();
    start.setHours(14, 0, 0, 0); // 2:00 PM
    
    const end = new Date();
    end.setHours(23, 0, 0, 0); // 11:00 PM
    
    const current = new Date(start);
    
    while (current < end) {
        slots.push(current.toTimeString().substring(0, 5));
        current.setMinutes(current.getMinutes() + 15);
    }
    
    return slots;
}

function showAppointmentDetails(appointmentId) {
    fetch(`/api/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                selectedAppointment = data.data;
                populateAppointmentModal(data.data);
                showActionButtons(data.data.status);
                new bootstrap.Modal(document.getElementById('appointmentModal')).show();
            }
        })
        .catch(error => {
            console.error('Error loading appointment:', error);
        });
}

function populateAppointmentModal(appointment) {
    const modalBody = document.getElementById('appointmentModalBody');
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Patient Information</h6>
                <p><strong>Name:</strong> ${appointment.patient_name}</p>
                <p><strong>Phone:</strong> ${appointment.patient_phone}</p>
                <p><strong>Age:</strong> ${appointment.patient_age || 'N/A'}</p>
                <p><strong>Gender:</strong> ${appointment.patient_gender || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6>Appointment Details</h6>
                <p><strong>Date:</strong> ${formatDate(appointment.date)}</p>
                <p><strong>Time:</strong> ${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</p>
                <p><strong>Type:</strong> ${appointment.visit_type}</p>
                <p><strong>Source:</strong> ${appointment.source}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Notes</h6>
                <p>${appointment.notes || 'No notes available'}</p>
            </div>
        </div>
    `;
}

function showActionButtons(status) {
    // Hide all buttons first
    document.querySelectorAll('#appointmentModal .modal-footer button:not(.btn-secondary)').forEach(btn => {
        btn.style.display = 'none';
    });
    
    // Show relevant buttons based on status
    switch (status) {
        case 'Booked':
            document.getElementById('startVisitBtn').style.display = 'inline-block';
            document.getElementById('rescheduleBtn').style.display = 'inline-block';
            document.getElementById('cancelBtn').style.display = 'inline-block';
            break;
        case 'CheckedIn':
            document.getElementById('startVisitBtn').style.display = 'inline-block';
            document.getElementById('rescheduleBtn').style.display = 'inline-block';
            break;
        case 'InProgress':
            document.getElementById('completeVisitBtn').style.display = 'inline-block';
            break;
    }
}

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        loadCalendar();
        updateStatusIndicator();
    }, 60000); // 60 seconds
}

function updateStatusIndicator() {
    const indicator = document.getElementById('statusIndicator');
    indicator.innerHTML = '<i class="bi bi-circle-fill me-1"></i> Live';
    indicator.className = 'badge bg-success me-2';
    
    // Add pulse animation
    indicator.style.animation = 'pulseOnce 0.6s ease';
    setTimeout(() => {
        indicator.style.animation = '';
    }, 600);
}

function updateDateDisplay() {
    const display = document.getElementById('currentDateDisplay');
    display.textContent = currentDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function updateLastUpdate() {
    const lastUpdate = document.getElementById('lastUpdate');
    lastUpdate.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
}

function getStatusBadgeClass(status) {
    const classes = {
        'Booked': 'badge bg-primary',
        'CheckedIn': 'badge bg-info',
        'InProgress': 'badge bg-warning',
        'Completed': 'badge bg-success',
        'Cancelled': 'badge bg-danger',
        'NoShow': 'badge bg-secondary',
        'Rescheduled': 'badge bg-info'
    };
    return classes[status] || 'badge bg-secondary';
}

function getVisitTypeBadgeClass(type) {
    const classes = {
        'New': 'badge bg-primary',
        'FollowUp': 'badge bg-success',
        'Procedure': 'badge bg-warning'
    };
    return classes[type] || 'badge bg-secondary';
}

function formatTime(time) {
    if (!time) return '';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(date) {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Add appointment functions
function openAddAppointmentModal(preselectedTime = null, preselectedDate = null) {
    // Set date - use preselected date or current date
    const dateToUse = preselectedDate || currentDate.toISOString().split('T')[0];
    document.getElementById('appointmentDate').value = dateToUse;
    
    // Clear form
    document.getElementById('addAppointmentForm').reset();
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';
    
    // Re-set the date after form reset
    document.getElementById('appointmentDate').value = dateToUse;
    
    // Handle preselected patient
    const patientSearchField = document.getElementById('patientSearch');
    const newPatientBtn = document.getElementById('newPatientBtn');
    const preselectedLabel = document.getElementById('preselectedLabel');
    
    if (preselectedPatient) {
        // Fill patient info
        document.getElementById('selectedPatientId').value = preselectedPatient.id;
        patientSearchField.value = preselectedPatient.full_name;
        
        // Make patient field readonly
        patientSearchField.readOnly = true;
        patientSearchField.style.backgroundColor = 'var(--bg)';
        patientSearchField.style.cursor = 'not-allowed';
        
        // Hide new patient button
        newPatientBtn.style.display = 'none';
        
        // Show preselected label
        preselectedLabel.style.display = 'inline-block';
        
        // Show patient info
        document.getElementById('patientSearchResults').innerHTML = `
            <div class="selected-patient-info alert alert-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>Selected Patient:</strong> ${preselectedPatient.full_name}<br>
                        <small>Phone: ${preselectedPatient.phone} • Age: ${preselectedPatient.age || 'N/A'}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                        Change Patient
                    </button>
                </div>
            </div>
        `;
    } else {
        // Enable patient search
        patientSearchField.readOnly = false;
        patientSearchField.style.backgroundColor = '';
        patientSearchField.style.cursor = '';
        newPatientBtn.style.display = 'block';
        preselectedLabel.style.display = 'none';
    }
    
    // Load available time slots for selected date
    loadAvailableTimeSlots(preselectedTime);
    
    // If preselected time is provided, select it after slots are loaded
    if (preselectedTime) {
        setTimeout(() => {
            const timeField = document.getElementById('appointmentTime');
            timeField.value = preselectedTime;
            timeField.classList.add('preselected-field');
        }, 300);
    }
    
    // Add styling to preselected date field if it's different from today
    const dateField = document.getElementById('appointmentDate');
    const today = new Date().toISOString().split('T')[0];
    if (dateToUse !== today) {
        dateField.classList.add('preselected-field');
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
    
    // Clean up styling when modal is hidden
    document.getElementById('addAppointmentModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('appointmentDate').classList.remove('preselected-field');
        document.getElementById('appointmentTime').classList.remove('preselected-field');
    });
    
    modal.show();
}

function quickAddAppointment(time) {
    // Set the current date being viewed and the selected time
    openAddAppointmentModal(time, currentDate.toISOString().split('T')[0]);
}

function searchPatients() {
    // Don't search if patient is preselected
    if (preselectedPatient) {
        return;
    }
    
    const query = document.getElementById('patientSearch').value.trim();
    if (query.length < 2) {
        document.getElementById('patientSearchResults').innerHTML = '';
        return;
    }
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayPatientSearchResults(data.data);
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
        });
}

function displayPatientSearchResults(patients) {
    const resultsContainer = document.getElementById('patientSearchResults');
    
    if (patients.length === 0) {
        resultsContainer.innerHTML = '<div class="search-result-item text-muted">No patients found</div>';
        return;
    }
    
    let html = '';
    patients.forEach(patient => {
        html += `
            <div class="search-result-item" onclick="selectPatient(${patient.id}, '${patient.first_name} ${patient.last_name}')">
                <div class="patient-name">${patient.first_name} ${patient.last_name}</div>
                <div class="patient-details">${patient.phone} • Age: ${patient.age || 'N/A'}</div>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

function selectPatient(patientId, patientName) {
    document.getElementById('selectedPatientId').value = patientId;
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('patientSearchResults').innerHTML = '';
}

function loadAvailableTimeSlots(preselectedTime = null) {
    const date = document.getElementById('appointmentDate').value;
    if (!date) return;
    
    const doctorId = <?= $doctorId ?>;
    
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateTimeSlots(data.data.available_slots, preselectedTime);
            }
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
        });
}

function populateTimeSlots(availableSlots, preselectedTime = null) {
    const timeSelect = document.getElementById('appointmentTime');
    timeSelect.innerHTML = '<option value="">Select time slot...</option>';
    
    console.log('Populating time slots:', { availableSlots, preselectedTime });
    
    // Add all available slots
    availableSlots.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = formatTime(time);
        timeSelect.appendChild(option);
    });
    
    // If there's a preselected time that's not in available slots, add it
    if (preselectedTime && !availableSlots.includes(preselectedTime)) {
        console.log('Adding preselected time not in available slots:', preselectedTime);
        const option = document.createElement('option');
        option.value = preselectedTime;
        option.textContent = formatTime(preselectedTime) + ' (Selected)';
        option.style.fontWeight = 'bold';
        option.style.color = '#28a745';
        option.style.backgroundColor = '#f8f9fa';
        timeSelect.appendChild(option);
    }
    
    // Sort all options by time (except the first "Select..." option)
    const options = Array.from(timeSelect.options).slice(1); // Skip first "Select..." option
    options.sort((a, b) => a.value.localeCompare(b.value));
    
    // Clear and re-add sorted options
    timeSelect.innerHTML = '<option value="">Select time slot...</option>';
    options.forEach(option => timeSelect.appendChild(option));
    
    console.log('Final time slots count:', timeSelect.options.length - 1);
}

function handleAddAppointment(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const appointmentData = Object.fromEntries(formData);
    
    // Validation
    if (!appointmentData.patient_id) {
        alert('Please select a patient');
        return;
    }
    
    // Add doctor_id
    appointmentData.doctor_id = <?= $doctorId ?>;
    
    // Save appointment
    fetch('/api/appointments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(appointmentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal')).hide();
            
            // Refresh calendar
            loadCalendar();
            
            // Show success message
            showNotification('Appointment added successfully!', 'success');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving appointment:', error);
        alert('Error saving appointment');
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
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

function clearPreselectedPatient() {
    // Clear preselected patient
    preselectedPatient = null;
    
    // Clear form fields
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('patientSearch').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';
    
    // Enable patient search
    const patientSearchField = document.getElementById('patientSearch');
    const newPatientBtn = document.getElementById('newPatientBtn');
    const preselectedLabel = document.getElementById('preselectedLabel');
    
    patientSearchField.readOnly = false;
    patientSearchField.style.backgroundColor = '';
    patientSearchField.style.cursor = '';
    patientSearchField.placeholder = 'Search patient by name or phone...';
    
    newPatientBtn.style.display = 'block';
    preselectedLabel.style.display = 'none';
    
    // Focus on search field
    patientSearchField.focus();
}

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

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style>
.calendar-grid {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.calendar-header {
    display: grid;
    grid-template-columns: 120px 1fr;
    background: var(--bg);
    border-bottom: 2px solid var(--border);
    font-weight: 600;
}

.calendar-header > div {
    padding: 1rem;
    border-right: 1px solid var(--border);
}

.calendar-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    border-bottom: 1px solid var(--border);
    min-height: 80px;
}

.calendar-row:last-child {
    border-bottom: none;
}

.time-slot {
    padding: 1rem;
    border-right: 1px solid var(--border);
    background: var(--bg);
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text);
}

.appointment-slot {
    padding: 0.5rem;
    display: flex;
    align-items: center;
}

.appointment-card {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid var(--border);
    background: var(--card);
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.appointment-card.booked {
    border-left: 4px solid var(--accent);
}

.appointment-card.checkedin {
    border-left: 4px solid #17a2b8;
}

.appointment-card.inprogress {
    border-left: 4px solid #ffc107;
}

.appointment-card.completed {
    border-left: 4px solid var(--success);
}

.appointment-card.cancelled {
    border-left: 4px solid var(--danger);
    opacity: 0.7;
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.patient-name {
    font-weight: 600;
    color: var(--text);
}

.appointment-details {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.appointment-notes {
    font-size: 0.875rem;
    color: var(--muted);
    line-height: 1.4;
}

.available-slot {
    color: var(--success);
    text-align: center;
    width: 100%;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 6px;
    border: 2px dashed var(--success);
    background: rgba(var(--success-rgb), 0.05);
    transition: all 0.2s ease;
    font-weight: 500;
}

.available-slot:hover {
    background: rgba(var(--success-rgb), 0.15);
    border-color: var(--accent);
    color: var(--accent);
    transform: translateY(-1px);
}

.unavailable-slot {
    background: linear-gradient(135deg, #dc3545, #b02a37);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    text-align: center;
    font-size: 0.9em;
    line-height: 1.3;
    width: 100%;
}

.unavailable-slot.outside-hours {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: #f8f9fa;
}

.unavailable-slot i {
    font-size: 0.85em;
    opacity: 0.9;
}

.refresh-indicator {
    animation: pulseOnce 0.6s ease;
}

@keyframes pulseOnce {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.status-indicator {
    transition: all 0.3s ease;
}

.modal-content {
    border-radius: 12px;
    border: 1px solid var(--border);
}

.modal-header {
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-right: 1px solid var(--border);
}

@media (max-width: 768px) {
    .calendar-header,
    .calendar-row {
        grid-template-columns: 100px 1fr;
    }
    
    .time-slot {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .appointment-slot {
        padding: 0.25rem;
    }
    
    .appointment-card {
        padding: 0.5rem;
    }
    
    .appointment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}

/* Search Results Styles */
.search-results {
    position: relative;
    z-index: 1000;
}

.search-result-item {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-top: none;
    background: var(--card);
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-result-item:first-child {
    border-top: 1px solid var(--border);
    border-radius: 8px 8px 0 0;
}

.search-result-item:last-child {
    border-radius: 0 0 8px 8px;
}

.search-result-item:only-child {
    border-radius: 8px;
}

.search-result-item:hover {
    background: var(--bg);
}

.patient-name {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.25rem;
}

.patient-details {
    font-size: 0.875rem;
    color: var(--muted);
}

/* Modal improvements */
.modal-content {
    border-radius: 12px;
}

.form-label {
    font-weight: 600;
    color: var(--text);
}

.btn-group .btn {
    border-radius: 6px !important;
}

/* Notification styles */
.alert {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
}

/* Selected patient info */
.selected-patient-info {
    margin-top: 0.5rem;
    border-radius: 8px;
    border: 1px solid #b3d9ff;
    background: rgba(13, 110, 253, 0.1);
}

/* Readonly field styling */
input[readonly] {
    background-color: var(--bg) !important;
    cursor: not-allowed !important;
    opacity: 0.8;
}

/* Preselected fields styling */
.preselected-field {
    background-color: rgba(var(--success-rgb), 0.1) !important;
    border-color: var(--success) !important;
    font-weight: 600;
}

.preselected-field:focus {
    box-shadow: 0 0 0 0.2rem rgba(var(--success-rgb), 0.25) !important;
}
</style>
