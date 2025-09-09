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
                                <input type="date" class="form-control" id="appointmentDate" name="date" 
                                       min="<?= date('Y-m-d') ?>" required>
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Cannot select a date before today (Local timezone: Egypt)
                                </div>
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
// Get server time for Egypt timezone
<?php
date_default_timezone_set('Africa/Cairo');
$serverDate = date('Y-m-d');
$serverDateTime = date('Y-m-d H:i:s');
$serverTimestamp = time();
?>

const SERVER_DATE = '<?= $serverDate ?>';
const SERVER_DATETIME = '<?= $serverDateTime ?>';
const SERVER_TIMESTAMP = <?= $serverTimestamp ?>;

let currentDate = new Date();
// Ensure currentDate is set to today at noon to avoid timezone issues
const today = new Date();
currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
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
        const today = new Date();
        currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
        loadCalendar();
    });
    
    document.getElementById('prevDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() - 24 * 60 * 60 * 1000);
        loadCalendar();
    });
    
    document.getElementById('nextDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() + 24 * 60 * 60 * 1000);
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
    document.getElementById('appointmentDate').addEventListener('change', (e) => {
        const selectedDate = e.target.value;
        
        // Validate selected date
        const validation = validateDateSelection(selectedDate);
        if (!validation.valid) {
            showErrorMessage(validation.message);
            // Reset to server date
            e.target.value = SERVER_DATE;
            return;
        }
        
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
    
    
    // Any doctor can load calendar data
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderCalendar(data.data);
                updateDateDisplay();
                updateLastUpdate();
                // Initialize tooltips after calendar is loaded
                initializeTooltips();
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
    
    // Check if it's Friday (official holiday) - use the date from server data
    const dateStr = data.date || currentDate.toISOString().split('T')[0];
    const currentDateObj = new Date(dateStr + 'T12:00:00'); // Use noon to avoid timezone issues
    const isFriday = currentDateObj.getDay() === 5; // 5 = Friday (0=Sunday, 1=Monday, ..., 6=Saturday)
    
    
    let html = '<div class="calendar-grid">';
    
    // Header row
    html += '<div class="calendar-header">';
    html += '<div class="time-column">Time</div>';
    html += '<div class="appointment-column">Appointments</div>';
    html += '</div>';
    
    // If it's Friday, show official holiday for all slots
    if (isFriday || data.is_friday) {
        const dayName = currentDateObj.toLocaleDateString('en-US', {weekday: 'long'});
        timeSlots.forEach(time => {
            html += '<div class="calendar-row">';
            html += `<div class="time-slot">${formatTime(time)}</div>`;
            html += '<div class="appointment-slot">';
            html += `<div class="unavailable-slot official-holiday" title="Official Holiday - ${dayName}">
                       <i class="bi bi-calendar-x me-2"></i>
                       <div class="holiday-info">
                           <div class="holiday-title">Official Holiday</div>
                           <div class="holiday-subtitle">${dayName}</div>
                       </div>
                     </div>`;
            html += '</div>';
            html += '</div>';
        });
    } else {
        // Normal day processing (any doctor can see all appointments)
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
                // Show unavailable information (only outside working hours now)
                if (unavailableSlot && unavailableSlot.reason === 'Outside working hours') {
                    html += `<div class="unavailable-slot outside-hours" title="Outside working hours">
                               <i class="bi bi-clock me-2"></i>Outside working hours
                             </div>`;
                } else {
                    // This should not happen - let's debug why
                    const debugInfo = unavailableSlot && unavailableSlot.debug_info ? unavailableSlot.debug_info : `No slot data - Time: ${time}`;
                    const reason = unavailableSlot && unavailableSlot.reason ? unavailableSlot.reason : 'No data available';
                    
                    html += `<div class="unavailable-slot debug-slot" title="Debug: ${reason}">
                               <i class="bi bi-bug me-2"></i>
                               <div class="debug-info">
                                   <div class="debug-title">🔍 Debug Info:</div>
                                   <div class="debug-details">${debugInfo}</div>
                               </div>
                             </div>`;
                }
            }
            
            html += '</div>';
            html += '</div>';
        });
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function renderAppointmentSlot(appointment) {
    const statusClass = getStatusBadgeClass(appointment.status);
    const visitTypeClass = getVisitTypeBadgeClass(appointment.visit_type);
    
    // Create detailed tooltip content (any doctor can see appointment details)
    const tooltipContent = `
        <div class="appointment-tooltip">
            <div class="tooltip-header">
                <strong>Appointment Details</strong>
            </div>
            <div class="tooltip-body">
                <div class="tooltip-row">
                    <span class="tooltip-label">Patient:</span>
                    <span class="tooltip-value">${appointment.patient_name}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Doctor:</span>
                    <span class="tooltip-value">${appointment.doctor_display_name || 'N/A'}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Phone:</span>
                    <span class="tooltip-value">${appointment.phone || 'N/A'}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Age:</span>
                    <span class="tooltip-value">${calculateAge(appointment.dob) || 'N/A'}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Visit Type:</span>
                    <span class="tooltip-value">${appointment.visit_type}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Time:</span>
                    <span class="tooltip-value">${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Status:</span>
                    <span class="tooltip-value">${appointment.status}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Source:</span>
                    <span class="tooltip-value">${appointment.source || 'Unavailable'}</span>
                </div>
                ${appointment.notes ? `
                <div class="tooltip-row">
                    <span class="tooltip-label">Notes:</span>
                    <span class="tooltip-value">${appointment.notes}</span>
                </div>
                ` : ''}
            </div>
            <div class="tooltip-footer">
                <small>Click to navigate to appointment page</small>
            </div>
        </div>
    `.replace(/\n\s+/g, ' ').trim();
    
    return `
        <div class="appointment-card ${appointment.status.toLowerCase()}" 
             onclick="navigateToAppointment(${appointment.id})"
             data-bs-toggle="tooltip" 
             data-bs-placement="right" 
             data-bs-html="true"
             data-bs-title="${tooltipContent.replace(/"/g, '&quot;')}">
            <div class="appointment-header">
                <div class="appointment-info">
                    <div class="info-line"><span class="label">Patient:</span> ${appointment.patient_name}</div>
                    <div class="info-line"><span class="label">Doctor:</span> ${appointment.doctor_display_name || 'N/A'}</div>
                    <div class="info-line"><span class="label">Type:</span> ${appointment.visit_type}</div>
                    <div class="info-line"><span class="label">Time:</span> ${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</div>
                </div>
                <span class="${statusClass}">${appointment.status}</span>
            </div>
            <div class="appointment-notes">
                ${appointment.notes ? appointment.notes.substring(0, 50) + '...' : 'No notes'}
            </div>
        </div>
    `;
}

function generateTimeSlots() {
    // Generate time slots for any doctor to use
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

function navigateToAppointment(appointmentId) {
    // Navigate to appointment page (any doctor can access any appointment)
    window.location.href = `/doctor/appointments/${appointmentId}`;
}

function calculateAge(dob) {
    // Calculate age for any doctor to see
    if (!dob) return null;
    
    try {
        const birthDate = new Date(dob);
        const today = new Date();
        
        if (isNaN(birthDate.getTime())) return null;
        
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age > 0 ? `${age} years` : null;
    } catch (error) {
        console.error('Error calculating age:', error);
        return null;
    }
}

function initializeTooltips() {
    // Dispose existing tooltips first (any doctor can use tooltips)
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Initialize new tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true,
            trigger: 'hover focus',
            delay: { show: 300, hide: 100 },
            container: 'body'
        });
    });
}

function showAppointmentDetails(appointmentId) {
    // Any doctor can view any appointment details
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
    // Any doctor can populate appointment modal
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
                <p><strong>Doctor:</strong> ${appointment.doctor_display_name || 'N/A'}</p>
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
    // Hide all buttons first (any doctor can perform actions)
    document.querySelectorAll('#appointmentModal .modal-footer button:not(.btn-secondary)').forEach(btn => {
        btn.style.display = 'none';
    });
    
    // Show relevant buttons based on status (any doctor can perform actions)
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
    // Auto refresh for any doctor
    refreshInterval = setInterval(() => {
        loadCalendar();
        updateStatusIndicator();
    }, 60000); // 60 seconds
}

function updateStatusIndicator() {
    // Update status indicator for any doctor
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
    // Update date display for any doctor
    const display = document.getElementById('currentDateDisplay');
    // Use the date string from currentDate to avoid timezone issues
    const dateStr = currentDate.toISOString().split('T')[0];
    const displayDate = new Date(dateStr + 'T12:00:00');
    display.textContent = displayDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function updateLastUpdate() {
    // Update last update time for any doctor
    const lastUpdate = document.getElementById('lastUpdate');
    lastUpdate.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
}

function getStatusBadgeClass(status) {
    // Get status badge class for any doctor to see
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
    // Get visit type badge class for any doctor to see
    const classes = {
        'New': 'badge bg-primary',
        'FollowUp': 'badge bg-success',
        'Procedure': 'badge bg-warning'
    };
    return classes[type] || 'badge bg-secondary';
}

function formatTime(time) {
    // Format time for any doctor to see
    if (!time) return '';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(date) {
    // Format date for any doctor to see
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Add appointment functions
function openAddAppointmentModal(preselectedTime = null, preselectedDate = null) {
    // Set date - use preselected date or current date (any doctor can add appointments)
    const dateToUse = preselectedDate || currentDate.toISOString().split('T')[0];
    
    // Validate date before opening modal
    const validation = validateDateSelection(dateToUse);
    if (!validation.valid) {
        showErrorMessage(validation.message);
        return;
    }
    
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
    const selectedDate = currentDate.toISOString().split('T')[0];
    
    // Check if selected date is in the past
    if (isDateInPast(selectedDate)) {
        showErrorMessage('Cannot add appointment on a past date. Please select today or a future date.');
        return;
    }
    
    // Set the current date being viewed and the selected time (any doctor can add appointments)
    openAddAppointmentModal(time, selectedDate);
}

// Function to check if date is in the past based on server time
function isDateInPast(dateString) {
    return dateString < SERVER_DATE;
}

// Function to validate date selection
function validateDateSelection(dateString) {
    if (isDateInPast(dateString)) {
        return {
            valid: false,
            message: 'Cannot select a date before today. Current date (Egypt timezone): ' + formatDateArabic(SERVER_DATE)
        };
    }
    return { valid: true };
}

// Format date in English (any doctor can see formatted dates)
function formatDateArabic(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        weekday: 'long'
    };
    return date.toLocaleDateString('en-US', options);
}

function searchPatients() {
    // Don't search if patient is preselected (any doctor can search patients)
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
    // Display patient search results for any doctor
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
    // Any doctor can select any patient
    document.getElementById('selectedPatientId').value = patientId;
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('patientSearchResults').innerHTML = '';
}

function loadAvailableTimeSlots(preselectedTime = null) {
    const date = document.getElementById('appointmentDate').value;
    if (!date) return;
    
    const doctorId = <?= $doctorId ?>;
    
    // Any doctor can load available time slots
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateTimeSlots(data.data.available_slots, preselectedTime);
                
                // Ensure preselected time is selected after population
                if (preselectedTime) {
                    setTimeout(() => {
                        const timeField = document.getElementById('appointmentTime');
                        if (timeField.value !== preselectedTime) {
                            timeField.value = preselectedTime;
                        }
                        if (timeField.value === preselectedTime) {
                            timeField.classList.add('preselected-field');
                        }
                    }, 100);
                }
            }
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
        });
}

function populateTimeSlots(availableSlots, preselectedTime = null) {
    const timeSelect = document.getElementById('appointmentTime');
    timeSelect.innerHTML = '<option value="">Select time slot...</option>';
    
    // Add all available slots (any doctor can see all available slots)
    availableSlots.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = formatTime(time);
        timeSelect.appendChild(option);
    });
    
    // If there's a preselected time that's not in available slots, add it
    if (preselectedTime && !availableSlots.includes(preselectedTime)) {
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
    
    // If preselected time exists, select it immediately
    if (preselectedTime) {
        setTimeout(() => {
            timeSelect.value = preselectedTime;
            if (timeSelect.value === preselectedTime) {
                timeSelect.classList.add('preselected-field');
            }
        }, 50);
    }
}

function handleAddAppointment(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const appointmentData = Object.fromEntries(formData);
    
    
    // Validation (any doctor can add appointments)
    if (!appointmentData.patient_id) {
        showErrorMessage('Please select a patient');
        return;
    }
    
    if (!appointmentData.date) {
        showErrorMessage('Please select a date');
        return;
    }
    
    // Final validation: Check if date is in the past
    const validation = validateDateSelection(appointmentData.date);
    if (!validation.valid) {
        showErrorMessage(validation.message);
        return;
    }
    
    if (!appointmentData.start_time) {
        showErrorMessage('Please select an appointment time');
        return;
    }
    
    if (!appointmentData.visit_type) {
        showErrorMessage('Please select a visit type');
        return;
    }
    
    // Add doctor_id (any doctor can book appointments)
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
            const errorMessage = data.message || data.error || 'Unknown error occurred';
            console.error('API Error:', errorMessage);
            alert('Error: ' + errorMessage);
        }
    })
    .catch(error => {
        console.error('Error saving appointment:', error);
        alert('Error saving appointment: ' + error.message);
    });
}

function showNotification(message, type = 'info') {
    // Create notification element (any doctor can see notifications)
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Enhanced error message function (any doctor can see error messages)
function showErrorMessage(message) {
    showNotification(message, 'danger');
}

// Enhanced success message function (any doctor can see success messages)
function showSuccessMessage(message) {
    showNotification(message, 'success');
}

function clearPreselectedPatient() {
    // Clear preselected patient (any doctor can change patient selection)
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
    // Debounce function for any doctor to use
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

// Cleanup on page unload (any doctor can use cleanup)
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style>
/* CSS Variables for Dark Mode */
:root {
    --bg: #f8fafc;
    --text: #0f172a;
    --card: #ffffff;
    --muted: #475569;
    --accent: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
    --border: #e2e8f0;
    --shadow: rgba(0, 0, 0, 0.1);
    --success-rgb: 16, 185, 129;
}

.dark {
    --bg: #0b1220;
    --text: #f8fafc;
    --card: #1e293b;
    --muted: #94a3b8;
    --accent: #38bdf8;
    --success: #4ade80;
    --danger: #fb7185;
    --border: #334155;
    --shadow: rgba(0, 0, 0, 0.3);
    --success-rgb: 74, 222, 128;
}

/* Calendar Grid Styles */
.calendar-grid {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: var(--card);
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
    color: var(--text);
}

/* Dark Mode Calendar Styles */
.dark .calendar-header {
    background: var(--bg);
    color: var(--text);
}

.dark .calendar-grid {
    background: var(--card);
    border-color: var(--border);
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
    color: var(--text);
}

/* Dark Mode Appointment Cards */
.dark .appointment-card {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .appointment-card:hover {
    box-shadow: 0 4px 12px var(--shadow);
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
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.appointment-header .appointment-info {
    flex: 1;
}

.appointment-header .appointment-info .info-line {
    font-size: 0.85em;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
}

.appointment-header .appointment-info .info-line .label {
    font-weight: 600;
    min-width: 55px;
    margin-right: 4px;
    color: var(--muted);
}

.patient-name {
    font-weight: 600;
    color: var(--text);
}

/* Dark Mode Text Colors */
.dark .patient-name {
    color: var(--text);
}

.dark .appointment-header .appointment-info .info-line .label {
    color: var(--muted);
}

.dark .appointment-notes {
    color: var(--muted);
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

/* Dark Mode Available Slots */
.dark .available-slot {
    color: var(--success);
    border-color: var(--success);
    background: rgba(var(--success-rgb), 0.1);
}

.dark .available-slot:hover {
    background: rgba(var(--success-rgb), 0.2);
    border-color: var(--accent);
    color: var(--accent);
}

.unavailable-slot {
    background: linear-gradient(135deg, #dc3545, #b02a37);
    color: white;
    padding: 10px 12px;
    border-radius: 6px;
    text-align: center;
    font-size: 0.85em;
    line-height: 1.4;
    width: 100%;
    font-weight: 500;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.unavailable-slot.outside-hours {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: #f8f9fa;
}

.unavailable-slot.reserved-slot {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 8px 10px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.unavailable-slot.reserved-slot .slot-details {
    flex: 1;
    text-align: left;
    line-height: 1.3;
}

.unavailable-slot.reserved-slot .info-line {
    font-size: 0.8em;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
}

.unavailable-slot.reserved-slot .info-line .label {
    font-weight: 600;
    min-width: 60px;
    margin-right: 4px;
    opacity: 0.9;
}

.unavailable-slot.debug-slot {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
    padding: 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.unavailable-slot.debug-slot .debug-info {
    flex: 1;
    text-align: left;
    font-size: 0.75em;
    line-height: 1.3;
}

.unavailable-slot.debug-slot .debug-title {
    font-weight: 600;
    margin-bottom: 3px;
    color: #212529;
}

.unavailable-slot.debug-slot .debug-details {
    font-family: 'Courier New', monospace;
    background: rgba(0, 0, 0, 0.1);
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.7em;
    word-break: break-all;
    max-height: 60px;
    overflow-y: auto;
}

.unavailable-slot.official-holiday {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.unavailable-slot.official-holiday .holiday-info {
    flex: 1;
    text-align: left;
}

.unavailable-slot.official-holiday .holiday-title {
    font-weight: 600;
    font-size: 0.9em;
    margin-bottom: 2px;
    color: white;
}

.unavailable-slot.official-holiday .holiday-subtitle {
    font-size: 0.75em;
    opacity: 0.9;
    color: rgba(255, 255, 255, 0.9);
}

.unavailable-slot.official-holiday i {
    font-size: 1.1em;
    opacity: 1;
    color: white;
}

.unavailable-slot i {
    font-size: 0.85em;
    opacity: 0.9;
    flex-shrink: 0;
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
    background: var(--card);
    color: var(--text);
}

.modal-header {
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
    color: var(--text);
}

.modal-body {
    background: var(--card);
    color: var(--text);
}

.modal-footer {
    background: var(--card);
    border-top: 1px solid var(--border);
}

/* Dark Mode Modal Styles */
.dark .modal-content {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .modal-header {
    background: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

.dark .modal-body {
    background: var(--card);
    color: var(--text);
}

.dark .modal-footer {
    background: var(--card);
    border-top-color: var(--border);
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

/* Dark Mode Search Results */
.dark .search-result-item {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .search-result-item:hover {
    background: var(--bg);
}

.dark .patient-details {
    color: var(--muted);
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

/* Form Controls Dark Mode */
.form-control {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
}

.form-control:focus {
    background: var(--card);
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    color: var(--text);
}

.form-select {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
}

.form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    color: var(--text);
}

.dark .form-control {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-control:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
}

.dark .form-select {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
}

.dark .form-label {
    color: var(--text);
}

.btn-group .btn {
    border-radius: 6px !important;
}

/* Notification styles */
.alert {
    box-shadow: 0 4px 12px var(--shadow);
    border-radius: 8px;
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
}

/* Dark Mode Alert Styles */
.dark .alert {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
    box-shadow: 0 4px 12px var(--shadow);
}

.dark .alert-info {
    background: rgba(56, 189, 248, 0.1);
    border-color: var(--accent);
    color: var(--text);
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
    color: var(--text) !important;
}

/* Dark Mode Readonly Fields */
.dark input[readonly] {
    background-color: var(--bg) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
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

/* Custom Tooltip Styling */
.tooltip {
    font-size: 0.875rem;
    max-width: 350px;
}

.tooltip-inner {
    background-color: #2c3e50;
    color: #ffffff;
    border-radius: 8px;
    padding: 12px 16px;
    text-align: left;
    direction: ltr;
    box-shadow: 0 4px 12px var(--shadow);
}

/* Dark Mode Tooltips */
.dark .tooltip-inner {
    background-color: var(--card);
    color: var(--text);
    border: 1px solid var(--border);
    box-shadow: 0 4px 12px var(--shadow);
}

.appointment-tooltip {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.appointment-tooltip .tooltip-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 8px;
    margin-bottom: 10px;
    font-size: 0.95rem;
}

.appointment-tooltip .tooltip-body {
    line-height: 1.5;
}

.appointment-tooltip .tooltip-row {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 6px;
    align-items: flex-start;
}

.appointment-tooltip .tooltip-label {
    font-weight: 600;
    color: #bdc3c7;
    min-width: 80px;
    margin-right: 8px;
}

.appointment-tooltip .tooltip-value {
    color: #ffffff;
    text-align: left;
    flex: 1;
    word-break: break-word;
}

.appointment-tooltip .tooltip-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 8px;
    margin-top: 10px;
    text-align: center;
}

.appointment-tooltip .tooltip-footer small {
    color: #95a5a6;
    font-style: italic;
}

/* Dark Mode Buttons */
.dark .btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.dark .btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: #0b1220;
}

.dark .btn-success {
    background-color: var(--success);
    border-color: var(--success);
    color: #0b1220;
}

.dark .btn-success:hover {
    background-color: #059669;
    border-color: #059669;
}

.dark .btn-secondary {
    background-color: #64748b;
    border-color: #64748b;
    color: white;
}

.dark .btn-secondary:hover {
    background-color: #475569;
    border-color: #475569;
}

.dark .btn-danger {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white;
}

.dark .btn-warning {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: #0b1220;
}

/* Dark Mode Cards */
.dark .card {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .card-header {
    background-color: var(--bg);
    border-bottom-color: var(--border);
    color: var(--text);
}

.dark .card-body {
    background-color: var(--card);
    color: var(--text);
}

/* Dark Mode Badge Styles */
.dark .badge {
    color: white;
}

.dark .badge.bg-success {
    background-color: var(--success) !important;
}

.dark .badge.bg-primary {
    background-color: var(--accent) !important;
}

.dark .badge.bg-info {
    background-color: #0ea5e9 !important;
}

.dark .badge.bg-warning {
    background-color: #f59e0b !important;
    color: #0b1220 !important;
}

.dark .badge.bg-danger {
    background-color: var(--danger) !important;
}

/* Dark Mode Text Colors */
.dark h4, .dark h5, .dark h6 {
    color: var(--text);
}

.dark .text-muted {
    color: var(--muted) !important;
}

.dark small {
    color: var(--muted);
}
</style>
