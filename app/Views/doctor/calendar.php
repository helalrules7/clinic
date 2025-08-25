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

<script>
let currentDate = new Date();
let selectedAppointment = null;
let refreshInterval;

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
        
        html += '<div class="calendar-row">';
        html += `<div class="time-slot">${formatTime(time)}</div>`;
        html += '<div class="appointment-slot">';
        
        if (appointment) {
            html += renderAppointmentSlot(appointment);
        } else if (isAvailable) {
            html += '<div class="available-slot">Available</div>';
        } else {
            html += '<div class="unavailable-slot">Unavailable</div>';
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
    font-style: italic;
    text-align: center;
    width: 100%;
}

.unavailable-slot {
    color: var(--muted);
    font-style: italic;
    text-align: center;
    width: 100%;
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
</style>
