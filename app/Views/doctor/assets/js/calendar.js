// Get server time from window.CALENDAR_CONFIG (set in calendar.php)
const SERVER_DATE = window.CALENDAR_CONFIG.serverDate;
const SERVER_DATETIME = window.CALENDAR_CONFIG.serverDateTime;
const SERVER_TIMESTAMP = window.CALENDAR_CONFIG.serverTimestamp;

// Initialize currentDate - will be set based on URL parameter or today
let currentDate = new Date();
const today = new Date();
currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);

let selectedAppointment = null;
let refreshInterval;
let preselectedPatient = window.CALENDAR_CONFIG.preselectedPatient;
let highlightedAppointmentId = null; // Store appointment ID from URL to highlight
let currentTimeFilter = null; // Current time filter: '2pm-6pm', '6pm-1045pm', 'available', 'unavailable', or null
let calendarData = null; // Store calendar data for filtering

// Function to initialize date from URL parameter
function initializeDateFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');
    const appointmentIdParam = urlParams.get('appointment_id');
    
    // Store appointment ID if provided
    if (appointmentIdParam) {
        highlightedAppointmentId = parseInt(appointmentIdParam);
    }
    
    if (dateParam && /^\d{4}-\d{2}-\d{2}$/.test(dateParam)) {
        // Use date from URL parameter
        const [year, month, day] = dateParam.split('-').map(Number);
        currentDate = new Date(year, month - 1, day, 12, 0, 0);
    } else {
        // Default to today at noon to avoid timezone issues
        const today = new Date();
        currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
    }
}

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date from URL FIRST before loading calendar
    initializeDateFromURL();
    loadCalendar();
    
    // Initialize auto-refresh toggle from localStorage
    const autoRefreshEnabled = getAutoRefreshState();
    const toggleSwitch = document.getElementById('calendarAutoRefresh');
    if (toggleSwitch) {
        toggleSwitch.checked = autoRefreshEnabled;
    }
    
    // Start auto-refresh if enabled
    if (autoRefreshEnabled) {
        startAutoRefresh();
    }
    
    setupEventListeners();
    initCustomSelects(); // Initialize custom select menus
    
    // Check if we need to open add appointment modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openModal') === 'addAppointment') {
        setTimeout(() => {
            openAddAppointmentModal(null, currentDate.toISOString().split('T')[0]);
            // Clean URL
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=addAppointment/, '').replace(/^&/, '?');
            window.history.replaceState({}, '', newUrl);
        }, 500);
    }
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
        // Close current modal first
        bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal')).hide();
        
        // Open add patient modal
        setTimeout(() => {
            const addPatientModal = new bootstrap.Modal(document.getElementById('addPatientModal'));
            addPatientModal.show();
        }, 300);
    });
    
    // Time filter buttons
    document.querySelectorAll('.filter-time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            applyTimeFilter(filter);
        });
    });
    
    // Mobile filter popover
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    if (mobileFilterBtn) {
        // Create filter buttons HTML for popover
        const filterButtonsHTML = `
            <div class="mobile-filter-popover">
                <div class="mb-3">
                    <h6 class="mb-3"><i class="bi bi-funnel me-2"></i>Filter Times</h6>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info filter-time-btn w-100" data-filter="2pm-6pm">
                            <i class="bi bi-clock me-1"></i>
                            2:00 PM - 6:00 PM
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-time-btn w-100" data-filter="6pm-1045pm">
                            <i class="bi bi-clock me-1"></i>
                            6:00 PM - 10:45 PM
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary filter-time-btn w-100" data-filter="available">
                            <i class="bi bi-check-circle me-1"></i>
                            Available Only
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning filter-time-btn w-100" data-filter="unavailable">
                            <i class="bi bi-x-circle me-1"></i>
                            Appointments Only
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary filter-time-btn w-100" data-filter="none">
                            <i class="bi bi-x-lg me-1"></i>
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Initialize popover
        const popoverInstance = new bootstrap.Popover(mobileFilterBtn, {
            html: true,
            content: filterButtonsHTML,
            placement: 'bottom',
            trigger: 'click',
            container: 'body',
            sanitize: false
        });
        
        // Add event listener for when popover is shown
        mobileFilterBtn.addEventListener('shown.bs.popover', function() {
            // Attach event listeners to filter buttons inside popover
            const popoverElement = document.querySelector('.popover');
            if (popoverElement) {
                // Add glass effect class to popover
                popoverElement.classList.add('mobile-filter-popover-glass');
                
                // Attach event listeners to filter buttons
                popoverElement.querySelectorAll('.filter-time-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const filter = this.getAttribute('data-filter');
                        applyTimeFilter(filter);
                        // Close popover after selection
                        setTimeout(() => {
                            popoverInstance.hide();
                        }, 300);
                    });
                });
            }
        });
        
        // Remove glass effect class when popover is hidden
        mobileFilterBtn.addEventListener('hidden.bs.popover', function() {
            const popoverElement = document.querySelector('.popover');
            if (popoverElement) {
                popoverElement.classList.remove('mobile-filter-popover-glass');
            }
        });
        
        // Update filter button states when filter changes
        mobileFilterBtn.addEventListener('hidden.bs.popover', function() {
            // Update button states when popover is closed
            updateFilterButtonStates();
        });
    }
}

function updateFilterButtonStates() {
    // Update all filter buttons (both desktop and mobile) to reflect current filter
    document.querySelectorAll('.filter-time-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-filter') === currentTimeFilter) {
            btn.classList.add('active');
        }
    });
}

function loadCalendar() {
    const dateStr = currentDate.toISOString().split('T')[0];
    const doctorId = window.CALENDAR_CONFIG.doctorId;
    
    
    // Any doctor can load calendar data
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                calendarData = data.data; // Store calendar data for filtering
                renderCalendar(data.data);
                updateDateDisplay();
                updateLastUpdate();
                // Initialize tooltips after calendar is loaded
                initializeTooltips();
                // Initialize progress bars after calendar is loaded
                initializeAppointmentProgressBars();
                // Scroll to highlighted appointment if exists
                if (highlightedAppointmentId) {
                    setTimeout(() => {
                        scrollToHighlightedAppointment();
                    }, 300);
                }
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
            // Apply time filter
            if (!shouldDisplayTimeSlot(time, data)) {
                return; // Skip this time slot if it doesn't match the filter
            }
            
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
    // Get appointment date, fallback to currentDate if not available
    const appointmentDate = appointment.date || currentDate.toISOString().split('T')[0];
    
    // Check if appointment is missed (Booked status and current date > appointment date)
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const aptDate = new Date(appointmentDate + 'T00:00:00');
    const isMissed = appointment.status && appointment.status.toLowerCase() === 'booked' && 
                     today > aptDate;
    
    // Check if appointment is completed
    const isCompleted = appointment.status && appointment.status.toLowerCase() === 'completed';
    
    // Determine status class and display text
    let statusClass, statusText, statusIcon;
    if (isMissed) {
        statusClass = 'bg-danger text-white';
        statusText = 'Missed';
        statusIcon = 'bi-exclamation-triangle';
    } else {
        statusClass = getStatusBadgeClass(appointment.status);
        statusText = getStatusDisplayText(appointment.status);
        statusIcon = getStatusIcon(appointment.status);
    }
    
    const visitTypeClass = getVisitTypeBadgeClass(appointment.visit_type);
    
    // Check if this is the highlighted appointment
    const isHighlighted = highlightedAppointmentId && appointment.id === highlightedAppointmentId;
    const highlightClass = isHighlighted ? 'highlighted-appointment' : '';
    
    // Create detailed tooltip content (any doctor can see appointment details)
    const tooltipContent = `
        <div class="appointment-tooltip" style="font-family: 'Cairo', sans-serif; !important;">
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
        <div class="appointment-card ${appointment.status.toLowerCase()} ${highlightClass}"
             data-appointment-id="${appointment.id}"
             data-bs-toggle="tooltip"
             data-bs-placement="right"
             data-bs-html="true"
             data-bs-title="${tooltipContent.replace(/"/g, '&quot;')}">
            <div class="appointment-header ${isMissed ? 'missed' : ''} ${isCompleted ? 'completed' : ''}">
                <div class="appointment-info" onclick="navigateToAppointment(${appointment.id})">
                    <div class="info-line"><span class="label">Patient:</span> ${appointment.patient_name}</div>
                    <div class="info-line"><span class="label">Doctor:</span> ${appointment.doctor_display_name || 'N/A'}</div>
                    <div class="info-line"><span class="label">Type:</span> ${appointment.visit_type}</div>
                    <div class="info-line"><span class="label">Time:</span> ${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</div>
                    ${appointment.status && appointment.status.toLowerCase() !== 'completed' && appointment.status.toLowerCase() !== 'rescheduled' ? `
                    <div class="appointment-progress-container mt-2" data-appointment-id="${appointment.id}" data-date="${appointmentDate}" data-start-time="${appointment.start_time}" data-end-time="${appointment.end_time}">
                        <div class="glass-progress-bar">
                            <div class="glass-progress-fill" style="width: 0%;"></div>
                            <div class="glass-progress-text">00:00</div>
                        </div>
                    </div>
                    ` : ''}
                </div>
                <div class="appointment-status">
                    ${appointment.status === 'Rescheduled' ? `
                    <a href="/doctor/appointments/${appointment.id}"
                       class="badge bg-warning text-dark d-flex align-items-center gap-1" style="text-decoration: none; font-weight: bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       data-bs-title="This appointment was rescheduled">
                        <i class="bi bi-arrow-clockwise"></i>
                        Rescheduled
                    </a>
                    ` : ''}
                    ${appointment.has_followup ? `
                    <a href="/doctor/appointments/${appointment.followup_id}"
                       class="badge bg-success d-flex align-items-center gap-1" style="text-decoration: none; font-weight: bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       data-bs-title="Follow-up appointment scheduled - Click to view">
                        <i class="bi bi-calendar-check"></i>
                        Follow-up
                    </a>
                    ` : ''}
                    ${appointment.is_followup && appointment.original_appointment_id ? `
                    <a href="/doctor/appointments/${appointment.original_appointment_id}"
                       class="badge bg-info d-flex align-items-center gap-1" style="text-decoration: none; font-weight: bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       data-bs-title="Original appointment - Click to view">
                        <i class="bi bi-calendar-event"></i>
                        Original
                    </a>
                    ` : ''}
                    <span class="badge ${statusClass} d-flex align-items-center gap-1">
                        <i class="bi ${statusIcon}"></i>
                        ${statusText}
                    </span>
                </div>
                <div class="appointment-actions">
                    <button class="btn btn-sm btn-outline-warning view-medical-history-btn"
                            onclick="event.stopPropagation(); showMedicalHistoryPopover(${appointment.patient_id}, this)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-title="View Medical History">
                        <i class="bi bi-clipboard-heart"></i>
                    </button>
                    <a href="/doctor/patients/${appointment.patient_id}"
                       class="btn btn-sm btn-outline-info view-patient-btn"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       data-bs-title="View Patient Profile">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger delete-appointment-btn"
                            onclick="event.stopPropagation(); deleteAppointment(${appointment.id}, '${appointment.patient_name}', '${formatTime(appointment.start_time)}')"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-title="Delete this appointment">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="appointment-notes" onclick="navigateToAppointment(${appointment.id})">
                ${appointment.notes ? appointment.notes.substring(0, 50) + '...' : 'No notes'}
            </div>
        </div>
    `;
}

function scrollToHighlightedAppointment() {
    if (!highlightedAppointmentId) return;
    
    const appointmentCard = document.querySelector(`[data-appointment-id="${highlightedAppointmentId}"]`);
    if (appointmentCard) {
        // Scroll to the appointment card with smooth behavior
        appointmentCard.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center',
            inline: 'nearest'
        });
        
        // Add a pulse animation to draw attention
        appointmentCard.style.animation = 'pulseHighlight 2s ease-in-out 3';
        
        // Remove animation after it completes
        setTimeout(() => {
            appointmentCard.style.animation = '';
        }, 6000);
    }
}

// Go to selected date function
function goToSelectedDate() {
    const datePicker = document.getElementById('tooltipDatePicker');
    if (!datePicker || !datePicker.value) {
        return;
    }
    
    const selectedDate = datePicker.value;
    
    // Hide popover
    const goToDateBtn = document.getElementById('goToDateBtn');
    if (goToDateBtn) {
        const popover = bootstrap.Popover.getInstance(goToDateBtn);
        if (popover) {
            popover.hide();
        }
    }
    
    // Navigate to the selected date
    window.location.href = `/doctor/calendar?date=${selectedDate}`;
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

// Time filter functions
function timeInRange(time, startTime, endTime) {
    // Convert time string (HH:MM) to minutes for comparison
    const timeToMinutes = (t) => {
        const [hours, minutes] = t.split(':').map(Number);
        return hours * 60 + minutes;
    };
    
    const timeMins = timeToMinutes(time);
    const startMins = timeToMinutes(startTime);
    const endMins = timeToMinutes(endTime);
    
    return timeMins >= startMins && timeMins <= endMins;
}

function shouldDisplayTimeSlot(time, data) {
    // If no filter is active, show all slots
    if (!currentTimeFilter || currentTimeFilter === 'none') {
        return true;
    }
    
    const appointment = data.appointments.find(apt => apt.start_time === time);
    const isAvailable = data.available_slots.includes(time);
    
    // Filter by time range
    if (currentTimeFilter === '2pm-6pm') {
        return timeInRange(time, '14:00', '18:00');
    }
    
    if (currentTimeFilter === '6pm-1045pm') {
        return timeInRange(time, '18:00', '22:45');
    }
    
    // Filter by availability
    if (currentTimeFilter === 'available') {
        return isAvailable && !appointment;
    }
    
    if (currentTimeFilter === 'unavailable') {
        return !isAvailable || !!appointment;
    }
    
    return true;
}

function applyTimeFilter(filter) {
    // Update current filter
    currentTimeFilter = filter === 'none' ? null : filter;
    
    // Update button styles (both desktop and mobile)
    updateFilterButtonStates();
    
    // Re-render calendar with filter applied
    if (calendarData) {
        renderCalendar(calendarData);
        updateDateDisplay();
        // Initialize tooltips after calendar is re-rendered
        setTimeout(() => {
            initializeTooltips();
        }, 100);
    }
}

function navigateToAppointment(appointmentId) {
    // Navigate to appointment page (any doctor can access any appointment)
    window.location.href = `/doctor/appointments/${appointmentId}`;
}

// Global variables for delete appointment
let currentAppointmentToDelete = null;

function deleteAppointment(appointmentId, patientName, appointmentTime) {
    // Store appointment data
    currentAppointmentToDelete = {
        id: appointmentId,
        patientName: patientName,
        time: appointmentTime
    };
    
    // Update modal content
    document.getElementById('deleteAppointmentPatientName').textContent = patientName;
    document.getElementById('deleteAppointmentTime').textContent = appointmentTime;
    
    // Show modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteAppointmentModal'));
    deleteModal.show();
}

// Handle confirm delete button click
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmDeleteAppointmentBtn').addEventListener('click', function() {
        if (currentAppointmentToDelete) {
            confirmDeleteAppointment();
        }
    });
});

function confirmDeleteAppointment() {
    if (!currentAppointmentToDelete) {
        showNotification('No appointment selected for deletion.', 'danger');
        return;
    }
    
    const { id, patientName, time } = currentAppointmentToDelete;
    
    // Show loading state
    setDeleteAppointmentButtonLoading(true);
    
    // Send delete request
    fetch(`/api/appointments/${id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        setDeleteAppointmentButtonLoading(false);
        
        if (data.ok) {
            // Success
            showNotification('Appointment deleted successfully!', 'success');
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('deleteAppointmentModal')).hide();
            
            // Clear current appointment
            currentAppointmentToDelete = null;
            
            // Refresh calendar
            loadCalendar();
            
            // Dispatch custom event to update carousel
            window.dispatchEvent(new CustomEvent('appointmentDeleted'));
        } else {
            // Error from server
            const errorMsg = data.error || data.message || 'Failed to delete appointment. Please try again.';
            showNotification(errorMsg, 'danger');
        }
    })
    .catch(error => {
        setDeleteAppointmentButtonLoading(false);
        console.error('Error deleting appointment:', error);
        showNotification('An error occurred while deleting the appointment. Please try again.', 'danger');
    });
}

function setDeleteAppointmentButtonLoading(loading) {
    const btn = document.getElementById('confirmDeleteAppointmentBtn');
    const btnText = btn.querySelector('.btn-text');
    const spinner = btn.querySelector('.spinner-border');
    
    if (loading) {
        btn.disabled = true;
        btnText.textContent = 'Deleting...';
        spinner.classList.remove('d-none');
    } else {
        btn.disabled = false;
        btnText.textContent = 'Delete Appointment';
        spinner.classList.add('d-none');
    }
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
    
    // Initialize popover for "Go to Date" button
    const goToDateBtn = document.getElementById('goToDateBtn');
    if (goToDateBtn) {
        // Dispose existing popover if any
        const existingPopover = bootstrap.Popover.getInstance(goToDateBtn);
        if (existingPopover) {
            existingPopover.dispose();
        }
        
        // Create new popover
        const popover = new bootstrap.Popover(goToDateBtn, {
            html: true,
            trigger: 'click',
            placement: 'bottom',
            container: 'body',
            sanitize: false
        });
        
        // Set current date when popover is shown
        goToDateBtn.addEventListener('shown.bs.popover', function() {
            setTimeout(() => {
                const datePicker = document.getElementById('tooltipDatePicker');
                if (datePicker) {
                    const currentDateStr = currentDate.toISOString().split('T')[0];
                    datePicker.value = currentDateStr;
                    
                    // Focus on date picker
                    datePicker.focus();
                    
                    // Add Enter key support
                    datePicker.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            goToSelectedDate();
                        }
                    });
                }
            }, 100);
        });
        
        // Close popover when clicking outside
        document.addEventListener('click', function(e) {
            const popoverInstance = bootstrap.Popover.getInstance(goToDateBtn);
            if (popoverInstance && popoverInstance._isShown()) {
                const popoverElement = document.querySelector('.popover');
                if (popoverElement && !goToDateBtn.contains(e.target) && !popoverElement.contains(e.target)) {
                    popoverInstance.hide();
                }
            }
        });
    }
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

// Get auto-refresh state from localStorage (default: true/ON)
function getAutoRefreshState() {
    const saved = localStorage.getItem('calendarAutoRefresh');
    return saved === null ? true : saved === 'true'; // Default is ON
}

// Save auto-refresh state to localStorage
function saveAutoRefreshState(enabled) {
    localStorage.setItem('calendarAutoRefresh', enabled ? 'true' : 'false');
}

// Toggle auto-refresh on/off
function toggleCalendarAutoRefresh(enabled) {
    saveAutoRefreshState(enabled);
    
    if (enabled) {
        // Start auto-refresh if not already running
        if (!refreshInterval) {
            startAutoRefresh();
        }
    } else {
        // Stop auto-refresh
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }
}

// Auto-refresh calendar data every 60 seconds using AJAX (pause when modals are open or user is interacting)
function startAutoRefresh() {
    // Clear any existing interval
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    refreshInterval = setInterval(() => {
        const searchModal = document.getElementById('searchModal');
        const addAppointmentModal = document.getElementById('addAppointmentModal');
        const addPatientModal = document.getElementById('addPatientModal');
        const appointmentModal = document.getElementById('appointmentModal');
        const cancelModal = document.getElementById('cancelModal');
        const deleteAppointmentModal = document.getElementById('deleteAppointmentModal');
        
        // Don't refresh if any modal is open
        const isModalOpen = searchModal?.classList.contains('show') ||
                           addAppointmentModal?.classList.contains('show') ||
                           addPatientModal?.classList.contains('show') ||
                           appointmentModal?.classList.contains('show') ||
                           cancelModal?.classList.contains('show') ||
                           deleteAppointmentModal?.classList.contains('show') ||
                           document.querySelector('.modal.show') !== null;
        
        if (!isModalOpen) {
            refreshCalendarData();
        }
    }, 60000); // 60 seconds
}

// Function to refresh calendar data via AJAX
function refreshCalendarData() {
    const dateStr = currentDate.toISOString().split('T')[0];
    const doctorId = window.CALENDAR_CONFIG.doctorId;
    
    // Show subtle loading indicator
    const calendarContainer = document.getElementById('calendarContainer');
    if (calendarContainer) {
        calendarContainer.parentElement.classList.add('table-loading');
    }
    
    fetch(`/api/calendar?doctor_id=${doctorId}&date=${dateStr}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        
        if (data.ok) {
            calendarData = data.data; // Store calendar data for filtering
            renderCalendar(data.data);
            updateDateDisplay();
            updateLastUpdate();
            updateStatusIndicator();
            // Initialize tooltips after calendar is refreshed
            setTimeout(() => {
                initializeTooltips();
                // Initialize progress bars after calendar is refreshed
                initializeAppointmentProgressBars();
            }, 100);
        } else {
        }
    })
    .catch(error => {
        // Silently fail - don't show error to user for background refresh
    })
    .finally(() => {
        // Remove loading indicator
        const calendarContainer = document.getElementById('calendarContainer');
        if (calendarContainer) {
            calendarContainer.parentElement.classList.remove('table-loading');
        }
    });
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
    if (lastUpdate) {
        const now = new Date();
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        const hoursStr = hours.toString().padStart(2, '0');
        const timeString = `${hoursStr}:${minutes}:${seconds} ${ampm}`;
        lastUpdate.textContent = `Last updated: ${timeString}`;
    }
}

function getStatusBadgeClass(status) {
    // Get status badge class for any doctor to see
    const classes = {
        'Booked': 'bg-primary',
        'CheckedIn': 'bg-success',
        'InProgress': 'bg-warning',
        'Completed': 'bg-success',
        'Cancelled': 'bg-danger',
        'NoShow': 'bg-secondary',
        'Rescheduled': 'bg-info'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusDisplayText(status) {
    const statusTexts = {
        'Booked': 'Booked',
        'CheckedIn': 'Checked In',
        'InProgress': 'In Progress',
        'Completed': 'Completed',
        'Cancelled': 'Cancelled',
        'NoShow': 'No Show',
        'Rescheduled': 'Rescheduled'
    };
    return statusTexts[status] || status;
}

function getStatusIcon(status) {
    const icons = {
        'Booked': 'bi-calendar-check',
        'CheckedIn': 'bi-check-circle-fill',
        'InProgress': 'bi-hourglass-split',
        'Completed': 'bi-check2-all',
        'Cancelled': 'bi-x-circle-fill',
        'NoShow': 'bi-clock-fill',
        'Rescheduled': 'bi-arrow-clockwise'
    };
    return icons[status] || 'bi-question-circle';
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
    
    // Check for visit_type from URL parameters or CALENDAR_CONFIG
    const urlParams = new URLSearchParams(window.location.search);
    const visitTypeFromURL = urlParams.get('visit_type');
    const visitTypeFromConfig = window.CALENDAR_CONFIG?.visitType || window.CALENDAR_CONFIG?.preselectedVisitType;
    const preselectedVisitType = visitTypeFromURL || visitTypeFromConfig;
    
    // Clear form
    document.getElementById('addAppointmentForm').reset();
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';
    
    // Re-set the date after form reset
    document.getElementById('appointmentDate').value = dateToUse;
    
    // Reset visit_type unless it was preselected from another page
    // Use setTimeout to ensure modal is fully rendered before accessing elements
    setTimeout(() => {
        const visitTypeSelect = document.getElementById('visitType');
        if (!visitTypeSelect) return;
        
        const visitTypeField = visitTypeSelect.closest('.field.menu');
        const visitTypeButton = visitTypeField?.querySelector('.custom-select-toggle');
        const visitTypeMenu = visitTypeField?.querySelector('menu');
        
        if (preselectedVisitType && visitTypeSelect.querySelector(`option[value="${preselectedVisitType}"]`)) {
            // Use preselected visit_type from another page
            visitTypeSelect.value = preselectedVisitType;
            if (visitTypeButton && visitTypeMenu) {
                const selectedOption = visitTypeMenu.querySelector(`li[data-option="${preselectedVisitType}"]`);
                if (selectedOption) {
                    visitTypeMenu.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                    selectedOption.classList.add('selected');
                    // Copy HTML structure from selected option to button (preserves icon + text)
                    const optionIcon = selectedOption.querySelector('i');
                    const optionText = selectedOption.querySelector('h3')?.textContent || preselectedVisitType;
                    if (optionIcon) {
                        visitTypeButton.innerHTML = optionIcon.outerHTML + ' <h3>' + optionText + '</h3>';
                    } else {
                        visitTypeButton.innerHTML = '<h3>' + optionText + '</h3>';
                    }
                }
            }
        } else {
            // Reset visit_type to default (empty) - use original HTML structure
            visitTypeSelect.value = '';
            if (visitTypeButton && visitTypeMenu) {
                visitTypeMenu.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                const defaultOption = visitTypeMenu.querySelector('li[data-option=""]');
                if (defaultOption) {
                    defaultOption.classList.add('selected');
                    // Reset to original HTML structure
                    visitTypeButton.innerHTML = '<h6>Select visit type...</h6>';
                }
            }
        }
        
        // Clear visit_type from URL after using it (if it was from URL)
        if (visitTypeFromURL) {
            const newUrl = new URL(window.location);
            newUrl.searchParams.delete('visit_type');
            window.history.replaceState({}, '', newUrl);
        }
    }, 100);
    
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
    
    const doctorId = window.CALENDAR_CONFIG.doctorId;
    
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
    const timeField = timeSelect.closest('.field.menu');
    const timeMenu = timeField ? timeField.querySelector('menu') : null;
    const timeButton = timeField ? timeField.querySelector('.custom-select-toggle') : null;
    
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
    
    // Update custom menu if it exists
    if (timeMenu) {
        timeMenu.innerHTML = '<li data-option="" tabindex="0" role="button" class="selected"><h3>Select time slot...</h3></li>';
        
        // Add all sorted options to custom menu
        options.forEach(option => {
            const li = document.createElement('li');
            li.setAttribute('data-option', option.value);
            li.setAttribute('tabindex', '0');
            li.setAttribute('role', 'button');
            
            // Add clock icon
            const icon = document.createElement('i');
            icon.className = 'bi bi-clock fs-5';
            li.appendChild(icon);
            
            // Add text content
            const h3 = document.createElement('h3');
            h3.textContent = option.textContent;
            li.appendChild(h3);
            
            timeMenu.appendChild(li);
        });
        
        // Re-initialize event listeners for new menu items
        const newOptions = timeMenu.querySelectorAll('li');
        newOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                const value = option.dataset.option;
                const text = option.querySelector('h3')?.textContent || option.textContent;
                
                timeSelect.value = value;
                timeSelect.dispatchEvent(new Event('change'));
                
                if (timeButton) {
                    timeButton.textContent = text;
                }
                
                newOptions.forEach(el => el.classList.remove('selected'));
                option.classList.add('selected');
                
                // Store selected value for scroll on next open
                const field = timeMenu.closest('.field.menu');
                if (field) {
                    field.dataset.selectedValue = value;
                    field.classList.remove('open');
                    if (timeButton) {
                        timeButton.setAttribute('aria-expanded', 'false');
                    }
                }
            });
            
            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    option.click();
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
                    const field = timeMenu.closest('.field.menu');
                    if (field) {
                        field.classList.remove('open');
                        if (timeButton) {
                            timeButton.setAttribute('aria-expanded', 'false');
                        }
                    }
                }
            });
        });
    }
    
    // If preselected time exists, select it immediately
    if (preselectedTime) {
        setTimeout(() => {
            timeSelect.value = preselectedTime;
            if (timeSelect.value === preselectedTime) {
                timeSelect.classList.add('preselected-field');
                
                // Update custom menu selection
                if (timeMenu && timeButton) {
                    const selectedLi = timeMenu.querySelector(`li[data-option="${preselectedTime}"]`);
                    if (selectedLi) {
                        timeMenu.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                        selectedLi.classList.add('selected');
                        timeButton.textContent = selectedLi.querySelector('h3')?.textContent || formatTime(preselectedTime);
                        
                        // Scroll to selected item when menu opens
                        const field = timeMenu.closest('.field.menu');
                        if (field) {
                            // Store scroll function for when menu opens
                            field.dataset.scrollToSelected = 'true';
                            field.dataset.selectedValue = preselectedTime;
                        }
                    }
                }
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
    appointmentData.doctor_id = window.CALENDAR_CONFIG.doctorId;
    
    // Check if date is today - if so, check for existing appointments
    const today = new Date().toISOString().split('T')[0];
    if (appointmentData.date === today && appointmentData.patient_id) {
        // Use promise-based approach
        fetch(`/api/patients/${appointmentData.patient_id}/appointments/check-active`)
            .then(checkResponse => checkResponse.json())
            .then(checkData => {
                if (checkData.has_active) {
                    showErrorMessage('This patient already has an appointment scheduled for today. Please complete or cancel the existing appointment first.');
                    return;
                }
                // Continue with appointment creation
                proceedWithAppointmentCreation(appointmentData);
            })
            .catch(error => {
                console.error('Error checking active appointments:', error);
                // Continue with appointment creation if check fails
                proceedWithAppointmentCreation(appointmentData);
            });
        return; // Exit early, proceedWithAppointmentCreation will handle the rest
    }
    
    // If date is not today, proceed directly
    proceedWithAppointmentCreation(appointmentData);
}

function proceedWithAppointmentCreation(appointmentData) {
    // Save appointment
    fetch('/api/appointments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(appointmentData)
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            // Try to parse error response
            return response.text().then(text => {
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || errorData.error || 'Unknown error occurred');
                } catch (e) {
                    // If parsing fails, use the text as error message
                    throw new Error(text || 'Unknown error occurred');
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.ok) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal')).hide();
            
            // Refresh calendar
            loadCalendar();
            
            // Dispatch custom event to update carousel
            window.dispatchEvent(new CustomEvent('appointmentAdded'));
            
            // Show success message
            showNotification('Appointment added successfully!', 'success');
        } else {
            const errorMessage = data.message || data.error || 'Unknown error occurred';
            console.error('API Error:', errorMessage);
            showNotification(errorMessage, 'danger');
        }
    })
    .catch(error => {
        console.error('Error saving appointment:', error);
        const errorMessage = error.message || 'Error saving appointment: ' + error.message;
        showNotification(errorMessage, 'danger');
    });
}

function showNotification(message, type = 'info') {
    // Ensure Bootstrap is loaded
    if (typeof bootstrap === 'undefined' || typeof bootstrap.Toast === 'undefined') {
        console.error('Bootstrap Toast is not available. Falling back to alert.');
        alert(message);
        return;
    }
    
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 start-50 translate-middle-x p-3';
        toastContainer.style.zIndex = '99999';
        toastContainer.style.pointerEvents = 'none';
        document.body.appendChild(toastContainer);
    }
    
    // Create unique toast ID
    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    
    // Determine icon and classes based on type
    let iconClass = 'info-circle';
    let toastClass = 'alert-toast-glass';
    
    if (type === 'success') {
        iconClass = 'check-circle';
    } else if (type === 'danger') {
        iconClass = 'exclamation-triangle';
        toastClass = 'alert-toast-glass alert-toast-danger';
    } else if (type === 'warning') {
        iconClass = 'exclamation-triangle';
        toastClass = 'alert-toast-glass alert-toast-warning';
    }
    
    // Create toast HTML
    const toastHtml = `
        <div id="${toastId}" class="toast ${toastClass} align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000" style="min-width: 350px; max-width: 500px; pointer-events: auto;">
            <div class="d-flex align-items-center">
                <div class="toast-body flex-grow-1">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-${iconClass} me-2" style="font-size: 1.25rem;"></i>
                        <div class="flex-grow-1">${escapeHtml(message)}</div>
                    </div>
                </div>
                <button type="button" class="btn-close alert-toast-close-btn me-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    
    if (toastElement) {
        try {
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            
            // Add exit animation when toast is being hidden
            toastElement.addEventListener('hide.bs.toast', function() {
                if (!toastElement.classList.contains('hiding')) {
                    toastElement.classList.add('hiding');
                }
            });
            
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        } catch (error) {
            console.error('Error showing toast:', error);
            // Fallback to alert if toast fails
            alert(message);
            toastElement.remove();
        }
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
        
        // Validate phone number format
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
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
                
                // Save form data before resetting
                const formData = new FormData(addPatientForm);
                const savedFormData = {
                    first_name: formData.get('first_name'),
                    last_name: formData.get('last_name'),
                    phone: formData.get('phone'),
                    gender: formData.get('gender'),
                    dob: formData.get('dob'),
                    age: formData.get('age')
                };
                
                // Reset form
                addPatientForm.reset();
                addPatientForm.classList.remove('was-validated');
                
                // Close modal after delay and return to appointment modal
                setTimeout(() => {
                    bootstrap.Modal.getInstance(addPatientModal).hide();
                    
                    // Return to appointment modal with new patient selected
                    setTimeout(() => {
                        const appointmentModal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
                        appointmentModal.show();
                        
                        // Auto-select the new patient - handle different response formats
                        const patientData = data.data || data.patient || data;
                        
                        if (patientData && (patientData.id || patientData.patient_id)) {
                            // Use saved form data to create patient info
                            const patientInfo = {
                                id: patientData.id || patientData.patient_id,
                                first_name: savedFormData.first_name,
                                last_name: savedFormData.last_name,
                                phone: savedFormData.phone,
                                gender: savedFormData.gender,
                                dob: savedFormData.dob,
                                age: savedFormData.age
                            };
                            
                            selectNewPatient(patientInfo);
                            
                            // Set visit type to "New" automatically
                            document.getElementById('visitType').value = 'New';
                            
                            // Also refresh the patient search to make sure the new patient appears in search results
                            setTimeout(() => {
                                const searchQuery = document.getElementById('patientSearch').value;
                                if (searchQuery) {
                                    searchPatients(searchQuery);
                                }
                            }, 1000);
                        } else {
                            console.error('No valid patient data found in response:', data);
                            showNotification('Patient added but could not auto-select. Please search for the patient manually.', 'warning');
                        }
                    }, 300);
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
    
    function selectNewPatient(patientData) {
        
        // Handle different response formats
        const firstName = patientData.first_name || patientData.firstName || '';
        const lastName = patientData.last_name || patientData.lastName || '';
        const fullName = `${firstName} ${lastName}`.trim();
        const patientId = patientData.id || patientData.patient_id;
        const phone = patientData.phone || patientData.phone_number || '';
        const age = patientData.age || calculateAgeFromDOB(patientData.dob) || 'N/A';
        
        
        // Fill patient search field
        document.getElementById('patientSearch').value = fullName;
        document.getElementById('selectedPatientId').value = patientId;
        
        // Show patient info
        document.getElementById('patientSearchResults').innerHTML = `
            <div class="selected-patient-info alert alert-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>New Patient Added:</strong> ${fullName}<br>
                        <small>Phone: ${phone} • Age: ${age}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                        Change Patient
                    </button>
                </div>
            </div>
        `;
    }
    
    function calculateAgeFromDOB(dob) {
        if (!dob) return null;
        try {
            const today = new Date();
            const birthDate = new Date(dob);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age > 0 ? age : null;
        } catch (error) {
            console.error('Error calculating age:', error);
            return null;
        }
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
            
            // Clear age field after conversion
            setTimeout(() => {
                this.value = '';
            }, 1000);
        }
    });
    
    // Convert date of birth to age
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
                ageInput.placeholder = `Calculated age: ${age} years`;
                setTimeout(() => {
                    ageInput.placeholder = 'Enter age in years';
                }, 3000);
            }
        }
    });
}

// Initialize add patient modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAddPatientModal();
    // Initialize draggable modals
    initializeDraggableModals();
});

// Make modals draggable
function initializeDraggableModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const modalDialog = modal.querySelector('.modal-dialog');
        if (!modalDialog) return;
        
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;
        
        // Make modal header the drag handle
        const modalHeader = modal.querySelector('.modal-header');
        if (!modalHeader) return;
        
        modalHeader.style.cursor = 'move';
        
        modalHeader.addEventListener('mousedown', dragStart);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', dragEnd);
        
        function dragStart(e) {
            // Don't drag if clicking on buttons or inputs
            if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                return;
            }
            
            // Get current transform values
            const transform = modalDialog.style.transform;
            if (transform) {
                const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                if (match) {
                    xOffset = parseFloat(match[1]) || 0;
                    yOffset = parseFloat(match[2]) || 0;
                }
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
                
                setTranslate(currentX, currentY, modalDialog);
            }
        }
        
        function dragEnd(e) {
            initialX = currentX;
            initialY = currentY;
            isDragging = false;
            modalDialog.style.transition = '';
        }
        
        function setTranslate(xPos, yPos, el) {
            // Get viewport dimensions
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            // Get modal dimensions
            const modalRect = el.getBoundingClientRect();
            const modalWidth = modalRect.width;
            const modalHeight = modalRect.height;
            
            // Get the original position (center of viewport)
            const originalLeft = (viewportWidth - modalWidth) / 2;
            const originalTop = 50; // Keep at least 50px from top
            
            // Calculate boundaries relative to original position
            // Allow movement within viewport bounds
            const minX = -(originalLeft - 20); // Allow 20px from left edge
            const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
            const minY = -(originalTop - 20); // Allow 20px from top
            const maxY = viewportHeight - modalHeight - originalTop - 20; // Allow 20px from bottom
            
            // Constrain movement
            const constrainedX = Math.max(minX, Math.min(maxX, xPos));
            const constrainedY = Math.max(minY, Math.min(maxY, yPos));
            
            el.style.transform = `translate(${constrainedX}px, ${constrainedY}px)`;
        }
        
        // Reset position when modal is hidden
        modal.addEventListener('hidden.bs.modal', function() {
            xOffset = 0;
            yOffset = 0;
            modalDialog.style.transform = '';
        });
    });
}

// Custom Select Menu Logic
function initCustomSelects() {
    const customSelects = document.querySelectorAll('.field.menu');

    customSelects.forEach(field => {
        const select = field.querySelector('select');
        const button = field.querySelector('.custom-select-toggle');
        const menu = field.querySelector('menu');
        const options = menu.querySelectorAll('li');

        if (!select || !button || !menu || options.length === 0) {
            console.warn('Missing elements for custom select initialization:', field);
            return;
        }

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
                    const openMenuEl = openField.querySelector('menu');
                    openField.classList.remove('open');
                    openButton.setAttribute('aria-expanded', 'false');
                    const openParent = openField.closest('.d-flex, .card-header, .col-12, .card, .modal, .modal-body, .mb-3');
                    if (openParent) {
                        openParent.style.zIndex = '';
                        openParent.style.position = '';
                    }
                }
            });

            field.classList.add('open');
            button.setAttribute('aria-expanded', 'true');

            // Fix z-index issue by elevating parent containers manually
            // Avoid setting position:relative on .modal itself as it breaks positioning
            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                parent.style.zIndex = '1000002'; // Match CSS value
                parent.style.position = 'relative';
            } else {
                // For modal, only set z-index without position
                const modal = field.closest('.modal');
                if (modal) {
                    modal.style.zIndex = '1000002';
                }
            }
            
            // Prevent modal from closing when clicking on menu
            const modal = field.closest('.modal');
            if (modal) {
                // Store original backdrop setting if not already stored
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance && !modal.dataset.originalBackdrop) {
                    modal.dataset.originalBackdrop = modalInstance._config.backdrop || 'true';
                }
            }

            const selected = menu.querySelector('.selected') || options[0];
            if (selected) {
                selected.focus();
                
                // Scroll to selected item if it's the appointmentTime menu
                if (select.id === 'appointmentTime' && selected) {
                    // Wait for menu to be fully visible, then scroll
                    setTimeout(() => {
                        selected.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'nearest'
                        });
                    }, 150);
                }
            } else if (select.id === 'appointmentTime' && field.dataset.selectedValue) {
                // If no selected item found but we have a stored value, try to find and scroll to it
                const storedValue = field.dataset.selectedValue;
                const storedSelected = menu.querySelector(`li[data-option="${storedValue}"]`);
                if (storedSelected) {
                    setTimeout(() => {
                        storedSelected.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'nearest'
                        });
                        storedSelected.focus();
                    }, 150);
                }
            }
        }

        function closeMenu() {
            field.classList.remove('open');
            button.setAttribute('aria-expanded', 'false');
            // Don't focus button if user is interacting with other form fields
            // Only focus if no other element has focus
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
                // For modal, only reset z-index
                const modal = field.closest('.modal');
                if (modal) {
                    setTimeout(() => {
                        if (!field.classList.contains('open')) {
                            modal.style.zIndex = '';
                        }
                    }, 300);
                }
            }
            
            // Re-enable modal backdrop behavior after menu closes
            const modal = field.closest('.modal');
            if (modal) {
                // Remove any temporary event listeners
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance && modal.dataset.originalBackdrop) {
                    // Restore original backdrop behavior if needed
                    setTimeout(() => {
                        // Modal backdrop should work normally now
                    }, 100);
                }
            }
        }

        function setOption(optionEl) {
            const value = optionEl.dataset.option;
            const text = optionEl.querySelector('h3')?.textContent || optionEl.textContent;

            select.value = value;
            select.dispatchEvent(new Event('change')); // Trigger change event

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
            // Don't interfere with input, textarea, or other interactive elements
            const target = e.target;
            const isInteractiveElement = target.tagName === 'INPUT' || 
                                        target.tagName === 'TEXTAREA' || 
                                        target.tagName === 'SELECT' ||
                                        target.isContentEditable ||
                                        target.closest('input, textarea, select, [contenteditable]');
            
            // If clicking on interactive element, don't close menu
            if (isInteractiveElement) {
                return;
            }
            
            // Only close if menu is open and click is outside the field
            if (field.classList.contains('open') && !field.contains(target)) {
                const modal = field.closest('.modal');
                // If clicking on modal backdrop while menu is open, prevent modal from closing
                if (modal && target === modal) {
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                closeMenu();
            }
        };
        
        // Use bubble phase instead of capture to avoid interfering with other elements
        document.addEventListener('click', handleOutsideClick, false);
        
        // Clean up listener when menu is removed (if needed)
        field.addEventListener('remove', () => {
            document.removeEventListener('click', handleOutsideClick, false);
        });
    });
}

// Initialize and update appointment progress bars - Global scope
function initializeAppointmentProgressBars() {
    const progressContainers = document.querySelectorAll('.appointment-progress-container');
    
    progressContainers.forEach(container => {
        updateAppointmentProgressBar(container);
    });
    
    // Update every second
    if (progressContainers.length > 0) {
        if (window.appointmentProgressInterval) {
            clearInterval(window.appointmentProgressInterval);
        }
        window.appointmentProgressInterval = setInterval(() => {
            progressContainers.forEach(container => {
                updateAppointmentProgressBar(container);
            });
        }, 1000);
    }
}

function updateAppointmentProgressBar(container) {
    if (!container) return;
    
    const appointmentId = container.getAttribute('data-appointment-id');
    const dateStr = container.getAttribute('data-date');
    const startTimeStr = container.getAttribute('data-start-time');
    const endTimeStr = container.getAttribute('data-end-time');
    
    if (!dateStr || !startTimeStr || !endTimeStr) {
        console.warn('Missing appointment data:', { dateStr, startTimeStr, endTimeStr });
        return;
    }
    
    const now = new Date();
    let appointmentDate;
    
    try {
        appointmentDate = new Date(dateStr);
        if (isNaN(appointmentDate.getTime())) {
            console.warn('Invalid date:', dateStr);
            return;
        }
    } catch (e) {
        console.warn('Error parsing date:', dateStr, e);
        return;
    }
    
    // Parse time strings (handle both HH:MM:SS and HH:MM formats)
    const startTimeParts = startTimeStr.split(':');
    const endTimeParts = endTimeStr.split(':');
    
    const startHours = parseInt(startTimeParts[0]) || 0;
    const startMinutes = parseInt(startTimeParts[1]) || 0;
    const startSeconds = parseInt(startTimeParts[2]) || 0;
    
    const endHours = parseInt(endTimeParts[0]) || 0;
    const endMinutes = parseInt(endTimeParts[1]) || 0;
    const endSeconds = parseInt(endTimeParts[2]) || 0;
    
    const startDateTime = new Date(appointmentDate);
    startDateTime.setHours(startHours, startMinutes, startSeconds, 0);
    
    const endDateTime = new Date(appointmentDate);
    endDateTime.setHours(endHours, endMinutes, endSeconds, 0);
    
    const appointmentDuration = 15 * 60; // 15 minutes in seconds (900 seconds)
    const progressFill = container.querySelector('.glass-progress-fill');
    const progressText = container.querySelector('.glass-progress-text');
    
    if (!progressFill || !progressText) {
        console.warn('Progress bar elements not found in container');
        return;
    }
    
    const nowTime = now.getTime();
    const startTime = startDateTime.getTime();
    const endTime = endDateTime.getTime();
    
    let progress = 0;
    let timeText = '00:00';
    let progressType = 'before'; // 'before', 'during', 'overdue'
    let prefixText = '';
    
    if (nowTime < startTime) {
        // Before appointment: show countdown to start
        progressType = 'before';
        prefixText = 'Remaining: ';
        const secondsUntilStart = Math.floor((startTime - nowTime) / 1000);
        const remainingSeconds = Math.max(0, secondsUntilStart);
        
        // Format time text (show hours if more than 60 minutes)
        let timeValue = '';
        if (remainingSeconds >= 3600) {
            const hours = Math.floor(remainingSeconds / 3600);
            const minutes = Math.floor((remainingSeconds % 3600) / 60);
            const seconds = remainingSeconds % 60;
            timeValue = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        } else {
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            timeValue = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
        timeText = prefixText + timeValue;
        
        // Progress bar fills as we get closer to start time
        // Use a reasonable max time (e.g., 2 hours = 7200 seconds)
        const maxCountdownTime = 2 * 60 * 60; // 2 hours
        const adjustedTotal = Math.min(secondsUntilStart, maxCountdownTime);
        progress = adjustedTotal > 0 ? ((maxCountdownTime - adjustedTotal) / maxCountdownTime) * 100 : 100;
        progress = Math.min(100, Math.max(0, progress));
    } else if (nowTime >= startTime && nowTime <= endTime) {
        // During appointment: show elapsed time
        progressType = 'during';
        prefixText = 'Progress: ';
        const elapsedSeconds = Math.floor((nowTime - startTime) / 1000);
        progress = (elapsedSeconds / appointmentDuration) * 100;
        progress = Math.min(100, Math.max(0, progress));
        
        // Format time text (show hours if more than 60 minutes)
        let timeValue = '';
        if (elapsedSeconds >= 3600) {
            const hours = Math.floor(elapsedSeconds / 3600);
            const minutes = Math.floor((elapsedSeconds % 3600) / 60);
            const seconds = elapsedSeconds % 60;
            timeValue = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        } else {
            const minutes = Math.floor(elapsedSeconds / 60);
            const seconds = elapsedSeconds % 60;
            timeValue = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
        timeText = prefixText + timeValue;
    } else {
        // After appointment: show overdue time
        progressType = 'overdue';
        prefixText = 'Overdue since: ';
        const overdueSeconds = Math.floor((nowTime - endTime) / 1000);
        progress = 100;
        
        // Format time text (show days if more than 24 hours)
        let timeValue = '';
        const secondsPerDay = 24 * 60 * 60; // 86400 seconds
        if (overdueSeconds >= secondsPerDay) {
            const days = Math.floor(overdueSeconds / secondsPerDay);
            const remainingSeconds = overdueSeconds % secondsPerDay;
            const hours = Math.floor(remainingSeconds / 3600);
            const minutes = Math.floor((remainingSeconds % 3600) / 60);
            const seconds = remainingSeconds % 60;
            timeValue = `${days} day${days !== 1 ? 's' : ''}, ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        } else if (overdueSeconds >= 3600) {
            const hours = Math.floor(overdueSeconds / 3600);
            const minutes = Math.floor((overdueSeconds % 3600) / 60);
            const seconds = overdueSeconds % 60;
            timeValue = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        } else {
            const minutes = Math.floor(overdueSeconds / 60);
            const seconds = overdueSeconds % 60;
            timeValue = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
        timeText = prefixText + timeValue;
    }
    
    // Update progress bar
    progressFill.style.width = `${progress}%`;
    
    // Update colors based on type
    progressFill.className = 'glass-progress-fill';
    if (progressType === 'before') {
        progressFill.classList.add('glass-progress-cyan');
    } else if (progressType === 'during') {
        progressFill.classList.add('glass-progress-green');
    } else {
        progressFill.classList.add('glass-progress-red');
    }
    
    // Update text
    progressText.textContent = timeText;
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show Medical History Popover
function showMedicalHistoryPopover(patientId, buttonElement) {
    // Close any existing popover
    const existingPopover = document.getElementById('medicalHistoryPopover');
    if (existingPopover) {
        existingPopover.remove();
    }
    
    // Create popover container
    const popover = document.createElement('div');
    popover.id = 'medicalHistoryPopover';
    popover.className = 'medical-history-popover';
    
    // Create backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'medical-history-popover-backdrop';
    backdrop.onclick = () => closeMedicalHistoryPopover();
    
    // Create popover content
    const content = document.createElement('div');
    content.className = 'medical-history-popover-content';
    
    // Create header
    const header = document.createElement('div');
    header.className = 'medical-history-popover-header';
    header.innerHTML = `
        <h3><i class="bi bi-clipboard-heart me-2"></i>Medical History</h3>
        <button class="btn-close-popover" onclick="closeMedicalHistoryPopover()" aria-label="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    
    // Create body
    const body = document.createElement('div');
    body.className = 'medical-history-popover-body';
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    content.appendChild(header);
    content.appendChild(body);
    popover.appendChild(backdrop);
    popover.appendChild(content);
    
    document.body.appendChild(popover);
    
    // Calculate position
    updatePopoverPosition(buttonElement, content);
    
    // Fetch medical history
    fetch(`/api/patients/${patientId}/medical-history`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                renderMedicalHistory(data.data, body);
            } else {
                body.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-inbox me-2"></i>No medical history available</div>';
            }
        })
        .catch(error => {
            console.error('Error loading medical history:', error);
            body.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error loading medical history</div>';
        });
    
    // Update position on scroll and resize
    const updatePosition = () => updatePopoverPosition(buttonElement, content);
    window.addEventListener('scroll', updatePosition, true);
    window.addEventListener('resize', updatePosition);
    
    // Store cleanup function
    popover._cleanup = () => {
        window.removeEventListener('scroll', updatePosition, true);
        window.removeEventListener('resize', updatePosition);
    };
}

function updatePopoverPosition(buttonElement, popoverContent) {
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const popoverWidth = Math.min(400, viewportWidth - 40);
    const popoverHeight = Math.min(600, viewportHeight - 40);
    
    // Center the popover in the viewport using CSS transform
    // CSS will handle the centering with left: 50%, top: 50%, transform: translate(-50%, -50%)
    popoverContent.style.width = `${popoverWidth}px`;
    popoverContent.style.maxHeight = `${popoverHeight}px`;
}

function renderMedicalHistory(history, container) {
    if (!history || history.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-inbox me-2"></i>No medical history available</div>';
        return;
    }
    
    let html = '<div class="medical-history-list">';
    
    history.forEach((entry, index) => {
        const date = entry.diagnosis_date || entry.created_at;
        const formattedDate = date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
        const doctorName = entry.doctor_name || 'Unknown';
        
        // Get notes text
        let notesText = '';
        if (entry.notes && entry.notes.trim() !== '') {
            notesText = escapeHtml(entry.notes.replace(/\r\n|\r|\n/g, ' '));
        } else if (entry.entry_type !== 'new_format') {
            const oldFormatNotes = [];
            if (entry.allergies) oldFormatNotes.push('Allergies: ' + escapeHtml(entry.allergies));
            if (entry.medications) oldFormatNotes.push('Medications: ' + escapeHtml(entry.medications));
            if (entry.systemic_history) oldFormatNotes.push('Systemic: ' + escapeHtml(entry.systemic_history));
            if (entry.ocular_history) oldFormatNotes.push('Ocular: ' + escapeHtml(entry.ocular_history));
            if (entry.prior_surgeries) oldFormatNotes.push('Surgeries: ' + escapeHtml(entry.prior_surgeries));
            if (entry.family_history) oldFormatNotes.push('Family: ' + escapeHtml(entry.family_history));
            if (oldFormatNotes.length > 0) {
                notesText = oldFormatNotes.join(' | ');
            }
        }
        
        // Highlight titles
        const titles = ['Chief Complaint', 'Plan', 'History of Present Illness', 'Allergies', 'Medications', 'Systemic', 'Ocular', 'Surgeries', 'Family', 'Diagnosis', 'Treatment'];
        titles.forEach(title => {
            const pattern = new RegExp('\\b(' + title.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '):\\s*', 'gi');
            notesText = notesText.replace(pattern, '<strong style="color: dodgerblue;">$1:</strong> ');
        });
        
        html += `
            <div class="medical-history-item ${index === 0 ? 'active' : ''}">
                <div class="medical-history-item-header">
                    <h4>${escapeHtml(entry.condition_name || `Medical Record #${entry.id}`)}</h4>
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>${escapeHtml(formattedDate)}
                        ${doctorName !== 'Unknown' ? ` • by ${escapeHtml(doctorName)}` : ''}
                    </small>
                </div>
                ${notesText ? `<div class="medical-history-item-content">${notesText}</div>` : ''}
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function closeMedicalHistoryPopover() {
    const popover = document.getElementById('medicalHistoryPopover');
    if (popover) {
        if (popover._cleanup) {
            popover._cleanup();
        }
        popover.remove();
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    if (window.appointmentProgressInterval) {
        clearInterval(window.appointmentProgressInterval);
    }
    closeMedicalHistoryPopover();
});