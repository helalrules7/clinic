<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Organizer</h4>
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-outline-info" id="prevMonthBtn">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-info" id="currentMonthBtn">
                    <i class="bi bi-calendar3 me-1"></i>
                    <span id="currentMonthDisplay"></span>
                </button>
                <button type="button" class="btn btn-outline-info" id="nextMonthBtn">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Month/Year Header -->
<div class="row mb-4">
    <div class="col-12 text-center">
        <h1 id="monthYearHeader" class="display-4 fw-bold mb-0" style="color: var(--text);">
            <!-- Will be populated by JavaScript -->
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div id="organizerCalendar">
                    <!-- Calendar will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading calendar...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/alert_modal.php'; ?>

<style>
    :root {
            --bg: #f8fafc;
            --text: #0f172a;
            --card: #ffffff;
            --muted: #475569;
            --accent: #0ea5e9;
            --success: #10b981;
            --danger: #ef4444;
            --border: #e2e8f0;
            --sidebar-width: 280px;
            --user-info-bg: rgb(248, 250, 252);
        }
        
        .dark {
            --bg: #0b1220;
            --text: #f8fafc;
            --card: #1e293b;
            --muted: #cbd5e1;
            --accent: #38bdf8;
            --success: #4ade80;
            --danger: #fb7185;
            --border: #334155;
            --user-info-bg: rgb(30, 41, 59);
        }
.organizer-calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.organizer-day-header {
    background: var(--bg);
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    color: var(--text);
    border-bottom: 2px solid var(--border);
}

.organizer-day {
    background: var(--card);
    min-height: 120px;
    padding: 0.5rem;
    position: relative;
    border: 1px solid var(--border);
    transition: all 0.2s ease;
}

.organizer-day:hover {
    background: var(--bg);
    transform: scale(1.02);
    z-index: 1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.organizer-day.other-month {
    opacity: 0.4;
    background: var(--bg);
}

.organizer-day.today {
    background: rgba(14, 165, 233, 0.1);
    border: 2px solid var(--accent);
}

.organizer-day-number {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.organizer-day.today .organizer-day-number {
    color: var(--accent);
    font-weight: 700;
}

.organizer-day-items {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    max-height: 80px;
    overflow-y: auto;
}

.organizer-item {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    position: relative;
}

.organizer-item:hover {
    transform: translateX(2px);
}

.organizer-item.appointment {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    border-left: 3px solid var(--success);
}

.organizer-item.note {
    background: rgba(59, 130, 246, 0.15);
    color: var(--accent);
    border-left: 3px solid var(--accent);
}

.organizer-item.alert {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger);
    border-left: 3px solid var(--danger);
}

.organizer-item i {
    font-size: 0.7rem;
}

.organizer-item-text {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.organizer-item-delete {
    opacity: 0;
    transition: opacity 0.2s ease;
    background: rgba(239, 68, 68, 0.2);
    border: none;
    color: var(--danger);
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
    cursor: pointer;
    font-size: 0.7rem;
}

.organizer-item:hover .organizer-item-delete {
    opacity: 1;
}

.organizer-item-delete:hover {
    background: rgba(239, 68, 68, 0.4);
}

.organizer-day-more {
    font-size: 0.7rem;
    color: var(--muted);
    margin-top: 0.25rem;
    text-align: center;
    cursor: pointer;
}

.organizer-day-more:hover {
    color: var(--accent);
}

/* Day Overlay for "Show All" */
.organizer-day-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 5;
    border-radius: 8px;
    transition: all 0.2s ease;
    opacity: 0;
    pointer-events: auto;
}

.organizer-day:hover .organizer-day-overlay {
    opacity: 1;
}

.organizer-day-overlay:hover {
    background: rgba(0, 0, 0, 0.3);
}

.organizer-day-overlay-text {
    color: #ffffff;
    font-weight: 600;
    font-size: 0.9rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    padding: 0.5rem 1rem;
    border: 1px solid var(--text);
    background: var(--bg);
    color: var(--text);
    border-radius: 6px;
    transition: all 0.8s ease;
}

.organizer-day-overlay:hover .organizer-day-overlay-text {
    border: 1px solid var(--accent);
    background: var(--bg);
    transform: scale(1.05);
}

.dark .organizer-day-overlay {
    background: rgba(0, 0, 0, 0.3);
}

.dark .organizer-day-overlay:hover {
    background: rgba(0, 0, 0, 0.3);
}

/* Scrollbar styling */
.organizer-day-items::-webkit-scrollbar {
    width: 4px;
}

.organizer-day-items::-webkit-scrollbar-track {
    background: transparent;
}

.organizer-day-items::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 2px;
}

.organizer-day-items::-webkit-scrollbar-thumb:hover {
    background: var(--muted);
}

/* Month/Year Header */
#monthYearHeader {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--accent);
    transition: all 0.3s ease;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    /* Header adjustments */
    #monthYearHeader {
        font-size: 1.5rem;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start !important;
    }
    
    .d-flex.gap-2.align-items-center {
        width: 100%;
        justify-content: space-between;
    }
    
    .d-flex.gap-2.align-items-center .btn {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
    }
    
    .d-flex.gap-2.align-items-center .btn i {
        font-size: 0.9rem;
    }
    
    h4.mb-0 {
        font-size: 1.25rem;
    }
    
    /* Calendar grid adjustments */
    .organizer-calendar {
        gap: 0.5px;
    }
    
    .organizer-day-header {
        padding: 0.5rem 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    /* Day cell adjustments */
    .organizer-day {
        min-height: 80px;
        padding: 0.25rem;
    }
    
    .organizer-day-number {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }
    
    /* Day items adjustments */
    .organizer-day-items {
        max-height: 60px;
        gap: 0.15rem;
    }
    
    .organizer-item {
        font-size: 0.65rem;
        padding: 0.15rem 0.3rem;
        gap: 0.15rem;
    }
    
    .organizer-item i {
        font-size: 0.6rem;
    }
    
    .organizer-item-text {
        font-size: 0.6rem;
    }
    
    .organizer-item-delete {
        padding: 0.05rem 0.2rem;
        font-size: 0.6rem;
    }
    
    /* Buttons adjustments */
    .organizer-day-view-btn,
    .organizer-day-add-alert-btn {
        padding: 0.15rem 0.3rem;
        font-size: 0.65rem;
        top: 0.15rem;
    }
    
    .organizer-day-view-btn {
        right: 0.15rem;
    }
    
    .organizer-day-add-alert-btn {
        right: 1.8rem;
    }
    
    /* Show more text */
    .organizer-day-more {
        font-size: 0.6rem;
        margin-top: 0.15rem;
    }
    
    /* Overlay adjustments */
    .organizer-day-overlay {
        border-radius: 4px;
    }
    
    .organizer-day-overlay-text {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
    }
    
    /* Make buttons always visible on mobile for better UX */
    .organizer-day-view-btn,
    .organizer-day-add-alert-btn {
        opacity: 1 !important;
    }
    
    /* Card body padding */
    .card-body.p-3 {
        padding: 0.75rem !important;
    }
    
    /* Modal adjustments */
    .organizer-modal-glass .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }
    
    .organizer-modal-glass .modal-content {
        font-size: 0.9rem;
    }
    
    .organizer-modal-glass .modal-header h5 {
        font-size: 1rem;
    }
    
    .organizer-modal-glass .modal-body {
        padding: 1rem;
    }
    
    .organizer-modal-glass .btn {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
}

/* Extra small devices */
@media (max-width: 576px) {
    #monthYearHeader {
        font-size: 1.25rem;
    }
    
    h4.mb-0 {
        font-size: 1.1rem;
    }
    
    .d-flex.gap-2.align-items-center .btn {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
    
    .d-flex.gap-2.align-items-center .btn span {
        font-size: 0.75rem;
    }
    
    .organizer-day {
        min-height: 70px;
        padding: 0.2rem;
    }
    
    .organizer-day-number {
        font-size: 0.7rem;
    }
    
    .organizer-day-items {
        max-height: 50px;
    }
    
    .organizer-item {
        font-size: 0.6rem;
        padding: 0.1rem 0.25rem;
    }
    
    .organizer-item-text {
        font-size: 0.55rem;
    }
    
    .organizer-day-view-btn,
    .organizer-day-add-alert-btn {
        padding: 0.1rem 0.25rem;
        font-size: 0.6rem;
    }
    
    .organizer-day-add-alert-btn {
        right: 1.5rem;
    }
    
    .organizer-day-overlay-text {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }
    
    /* Make buttons always visible on extra small devices */
    .organizer-day-view-btn,
    .organizer-day-add-alert-btn {
        opacity: 1 !important;
    }
    
    .organizer-day-header {
        font-size: 0.65rem;
        padding: 0.4rem 0.2rem;
    }
}

/* Day View Button */
.organizer-day-view-btn {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    background: rgba(14, 165, 233, 0.1);
    border: 1px solid rgba(14, 165, 233, 0.3);
    color: var(--accent);
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 15;
}

.organizer-day:hover .organizer-day-view-btn {
    opacity: 1;
}

.organizer-day-view-btn:hover {
    background: rgba(14, 165, 233, 0.2);
    transform: scale(1.1);
}

/* Day Add Alert Button */
.organizer-day-add-alert-btn {
    position: absolute;
    top: 0.25rem;
    right: 2.3rem;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: var(--danger);
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 15;
}

.organizer-day:hover .organizer-day-add-alert-btn {
    opacity: 1;
}

.organizer-day-add-alert-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    transform: scale(1.1);
}

/* Modal Glass Effect - Same as appointment.php */
.organizer-modal-glass .modal-content {
    background: rgba(248, 250, 252, 0.7) !important;
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(226, 232, 240, 0.4) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.15);
    color: var(--text) !important;
}

.dark .organizer-modal-glass .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
    color: var(--text) !important;
}

.organizer-modal-glass .modal-header {
    background: transparent !important;
    border-bottom-color: rgba(226, 232, 240, 0.4) !important;
    color: var(--text) !important;
}

.dark .organizer-modal-glass .modal-header {
    background: transparent !important;
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
    color: var(--text) !important;
}

.organizer-modal-glass .modal-body {
    background: transparent !important;
    color: var(--text) !important;
}

.organizer-modal-glass .modal-footer {
    background: transparent !important;
    border-top-color: rgba(226, 232, 240, 0.4) !important;
}

.dark .organizer-modal-glass .modal-footer {
    border-top-color: rgba(51, 65, 85, 0.3) !important;
}

/* Close button white in dark mode */
.dark .organizer-modal-glass .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

.dark .organizer-modal-glass .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

/* Enable dragging */
.organizer-modal-glass .modal-content {
    cursor: move;
}

.organizer-modal-glass .modal-dialog {
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
    position: relative;
}

.organizer-modal-glass .modal-header {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    cursor: move;
}

.organizer-modal-glass .modal-header button {
    cursor: pointer;
}

.organizer-modal-glass .modal-dialog.dragging {
    transition: none !important;
}
</style>

<script>
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth() + 1;
let organizerData = {};

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize month display
    updateMonthDisplay();
    
    loadOrganizerMonth();
    
    document.getElementById('prevMonthBtn').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        loadOrganizerMonth();
    });
    
    document.getElementById('nextMonthBtn').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        loadOrganizerMonth();
    });
    
    document.getElementById('currentMonthBtn').addEventListener('click', () => {
        const today = new Date();
        currentYear = today.getFullYear();
        currentMonth = today.getMonth() + 1;
        loadOrganizerMonth();
    });
});

function loadOrganizerMonth() {
    const container = document.getElementById('organizerCalendar');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading calendar...</p></div>';
    
    fetch(`/api/organizer/month?year=${currentYear}&month=${currentMonth}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.data) {
                organizerData = data.data.dataByDate || {};
                renderCalendar(data.data);
                updateMonthDisplay();
            } else {
                container.innerHTML = '<div class="alert alert-danger">Failed to load calendar data</div>';
            }
        })
        .catch(error => {
            console.error('Error loading organizer:', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading calendar</div>';
        });
}

function renderCalendar(data) {
    const container = document.getElementById('organizerCalendar');
    const firstDay = new Date(data.year, data.month - 1, 1);
    const lastDay = new Date(data.year, data.month, 0);
    const daysInMonth = lastDay.getDate();
    const startDayOfWeek = firstDay.getDay();
    
    let html = '<div class="organizer-calendar">';
    
    // Day headers
    dayNames.forEach(day => {
        html += `<div class="organizer-day-header">${day}</div>`;
    });
    
    // Empty cells for days before month starts
    for (let i = 0; i < startDayOfWeek; i++) {
        html += '<div class="organizer-day other-month"></div>';
    }
    
    // Days of the month
    const today = new Date();
    const isCurrentMonth = today.getFullYear() === data.year && today.getMonth() + 1 === data.month;
    
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${data.year}-${String(data.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = isCurrentMonth && day === today.getDate();
        const dayData = organizerData[dateStr] || { appointments: [], notes: [], alerts: [] };
        
        html += `<div class="organizer-day ${isToday ? 'today' : ''}">`;
        html += `<button class="organizer-day-view-btn" onclick="showDayDetails('${dateStr}')" title="View all day details">
            <i class="bi bi-eye"></i>
        </button>`;
        html += `<button class="organizer-day-add-alert-btn" onclick="openAddAlertModal('${dateStr}')" title="Add alert for this day">
            <i class="bi bi-bell"></i>
        </button>`;
        html += `<div class="organizer-day-number">${day}</div>`;
        html += '<div class="organizer-day-items">';
        
        // Filter alerts: exclude alerts that are related to appointments in the same day
        const appointmentIds = dayData.appointments.map(apt => apt.id);
        const filteredAlerts = dayData.alerts.filter(alert => {
            // If alert has appointment_id and that appointment exists in the same day, exclude it
            if (alert.appointment_id && appointmentIds.includes(parseInt(alert.appointment_id))) {
                return false;
            }
            return true;
        });
        
        // Show alerts first (at the top, sorted by time, newest first)
        const sortedAlerts = [...filteredAlerts].sort((a, b) => {
            const timeA = a.alert_time || '00:00:00';
            const timeB = b.alert_time || '00:00:00';
            return timeB.localeCompare(timeA); // Descending order (newest first)
        });
        sortedAlerts.slice(0, 2).forEach(alert => {
            const alertText = alert.message.length > 20 ? alert.message.substring(0, 20) + '...' : alert.message;
            html += `
                <div class="organizer-item alert" onclick="viewAlert(${alert.id})" title="${alert.message}">
                    <i class="bi bi-bell"></i>
                    <span class="organizer-item-text">${alertText}</span>
                </div>
            `;
        });
        
        // Show appointments
        dayData.appointments.slice(0, 3).forEach(appointment => {
            const time = appointment.start_time.substring(0, 5);
            html += `
                <div class="organizer-item appointment" onclick="viewAppointment(${appointment.id})" title="${appointment.patient_name} - ${time}">
                    <i class="bi bi-calendar-event"></i>
                    <span class="organizer-item-text">${time} - ${appointment.patient_name}</span>
                    <button class="organizer-item-delete" onclick="event.stopPropagation(); deleteAppointment(${appointment.id}, '${appointment.patient_name}', '${dateStr}')" title="Delete appointment">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        });
        
        // Show notes
        dayData.notes.slice(0, 2).forEach(note => {
            const noteText = note.content.length > 20 ? note.content.substring(0, 20) + '...' : note.content;
            html += `
                <div class="organizer-item note" onclick="viewNote(${note.id})" title="${note.content}">
                    <i class="bi bi-sticky"></i>
                    <span class="organizer-item-text">${noteText}</span>
                    <button class="organizer-item-delete" onclick="event.stopPropagation(); deleteNote(${note.id}, '${dateStr}')" title="Delete note">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        });
        
        // Show "more" indicator if there are more items
        // Use filtered alerts count for calculation
        const totalItems = dayData.appointments.length + dayData.notes.length + filteredAlerts.length;
        const shownItems = Math.min(dayData.appointments.length, 3) + Math.min(dayData.notes.length, 2) + Math.min(filteredAlerts.length, 2);
        if (totalItems > shownItems) {
            html += `<div class="organizer-day-more" onclick="showDayDetails('${dateStr}')">+${totalItems - shownItems} more</div>`;
        }
        
        html += '</div>';
        
        // Add semi-transparent overlay if there are more than 3 events
        if (totalItems > 3) {
            html += `<div class="organizer-day-overlay" onclick="showDayDetails('${dateStr}')">
                <span class="organizer-day-overlay-text">Show All</span>
            </div>`;
        }
        
        html += '</div>';
    }
    
    // Empty cells for days after month ends
    const totalCells = startDayOfWeek + daysInMonth;
    const remainingCells = 42 - totalCells; // 6 weeks * 7 days
    for (let i = 0; i < remainingCells && totalCells < 42; i++) {
        html += '<div class="organizer-day other-month"></div>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function updateMonthDisplay() {
    const displayText = `${monthNames[currentMonth - 1]} ${currentYear}`;
    document.getElementById('currentMonthDisplay').textContent = displayText;
    document.getElementById('monthYearHeader').textContent = displayText;
}

function viewAppointment(appointmentId) {
    window.location.href = `/doctor/appointments/${appointmentId}`;
}

function viewNote(noteId) {
    // Navigate to notes page or show note details
    window.location.href = `/doctor/notes#note-${noteId}`;
}

function viewAlert(alertId) {
    window.location.href = `/doctor/alerts#alert-${alertId}`;
}

function openAddAlertModal(dateStr) {
    // Open alert modal with the selected date pre-filled
    if (typeof openAlertModal === 'function') {
        // Open modal first
        openAlertModal(null, null, null);
        
        // Wait for modal to be shown, then set the date and time
        setTimeout(() => {
            const alertDateInput = document.getElementById('alertDate');
            if (alertDateInput) {
                alertDateInput.value = dateStr;
            }
            
            // Set default time to current time + 1 hour
            const now = new Date();
            now.setHours(now.getHours() + 1);
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const alertTimeInput = document.getElementById('alertTime');
            if (alertTimeInput) {
                alertTimeInput.value = `${hours}:${minutes}`;
            }
        }, 100);
    } else {
        console.error('openAlertModal function not found. Make sure alert_modal.php is included.');
    }
}

// Override saveAlert to reload organizer after creating alert
(function() {
    const originalSaveAlert = window.saveAlert;
    if (originalSaveAlert) {
        window.saveAlert = function() {
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
            
            // Get message from appropriate editor (text or HTML)
            let alertMessage = '';
            const htmlEditor = document.getElementById('alertMessageHtmlEditor');
            const textEditor = document.getElementById('alertMessage');
            
            if (htmlEditor && htmlEditor.style.display !== 'none') {
                alertMessage = htmlEditor.innerHTML;
            } else {
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
            
            const isEditMode = window.currentAlertIdToEdit !== null;
            const url = isEditMode ? '/api/alerts/' + window.currentAlertIdToEdit : '/api/alerts';
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
                    const modalElement = document.getElementById('alertModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                    
                    if (typeof showToast === 'function') {
                        const title = isEditMode ? 'Alert Updated' : 'Alert Created';
                        const message = isEditMode ? 'The alert has been updated successfully.' : 'The alert has been added to your notifications.';
                        showToast('success', title, message);
                    }
                    
                    // Reload organizer after creating alert
                    if (!isEditMode && typeof loadOrganizerMonth === 'function') {
                        loadOrganizerMonth();
                    }
                    
                    // Call original saveAlert for other callbacks
                    if (originalSaveAlert) {
                        originalSaveAlert();
                    }
                } else {
                    if (typeof showToast === 'function') {
                        const errorMsg = data.message || 'Failed to ' + (isEditMode ? 'update' : 'create') + ' alert';
                        showToast('error', 'Error', errorMsg);
                    }
                }
            })
            .catch(error => {
                if (typeof showToast === 'function') {
                    const errorMsg = 'Failed to ' + (isEditMode ? 'update' : 'create') + ' alert. Please try again.';
                    showToast('error', 'Error', errorMsg);
                }
            });
        };
    }
})();

function deleteAppointment(appointmentId, patientName, date) {
    showDeleteModal('appointment', appointmentId, patientName, date);
}

function confirmDeleteAppointment(appointmentId) {
    fetch(`/api/appointments/${appointmentId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok || data.success) {
            showToast('success', 'Appointment Deleted', 'The appointment has been deleted successfully.');
            loadOrganizerMonth();
        } else {
            showToast('error', 'Error', data.error || 'Failed to delete appointment');
        }
    })
    .catch(error => {
        console.error('Error deleting appointment:', error);
        showToast('error', 'Error', 'Failed to delete appointment');
    });
}

function deleteNote(noteId, date) {
    showDeleteModal('note', noteId, null, date);
}

function confirmDeleteNote(noteId) {
    fetch(`/api/notes/${noteId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Note Deleted', 'The note has been deleted successfully.');
            loadOrganizerMonth();
        } else {
            showToast('error', 'Error', data.message || 'Failed to delete note');
        }
    })
    .catch(error => {
        console.error('Error deleting note:', error);
        showToast('error', 'Error', 'Failed to delete note');
    });
}

function showDayDetails(dateStr) {
    const dayData = organizerData[dateStr] || { appointments: [], notes: [], alerts: [] };
    const date = new Date(dateStr);
    const dateFormatted = date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    let content = `<h5>${dateFormatted}</h5>`;
    content += '<div class="mt-3">';
    
    // Filter alerts: exclude alerts that are related to appointments in the same day
    const appointmentIds = dayData.appointments.map(apt => apt.id);
    const filteredAlerts = dayData.alerts.filter(alert => {
        // If alert has appointment_id and that appointment exists in the same day, exclude it
        if (alert.appointment_id && appointmentIds.includes(parseInt(alert.appointment_id))) {
            return false;
        }
        return true;
    });
    
    // Show alerts first (at the top)
    if (filteredAlerts.length > 0) {
        content += '<h6 class="text-danger"><i class="bi bi-bell me-2"></i>Alerts</h6>';
        // Sort alerts by time (newest first)
        const sortedAlerts = [...filteredAlerts].sort((a, b) => {
            const timeA = a.alert_time || '00:00:00';
            const timeB = b.alert_time || '00:00:00';
            return timeB.localeCompare(timeA); // Descending order (newest first)
        });
        sortedAlerts.forEach(alert => {
            const alertMessageEscaped = alert.message.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            content += `<div class="mb-2 p-2 border rounded alert-item-clickable" style="cursor: pointer; transition: all 0.2s ease;" onclick="viewAlert(${alert.id})" onmouseover="this.style.background='var(--bg)'; this.style.transform='translateX(4px)'" onmouseout="this.style.background='transparent'; this.style.transform='translateX(0)'">
                <strong>${alert.alert_time ? alert.alert_time.substring(0, 5) : 'N/A'}</strong> - ${alertMessageEscaped}
            </div>`;
        });
    }
    
    if (dayData.appointments.length > 0) {
        content += '<h6 class="text-success mt-3"><i class="bi bi-calendar-event me-2"></i>Appointments</h6>';
        dayData.appointments.forEach(apt => {
            const patientNameEscaped = apt.patient_name.replace(/'/g, "\\'");
            content += `<div class="mb-2 p-2 border rounded d-flex justify-content-between align-items-center appointment-item-clickable" style="cursor: pointer; transition: all 0.2s ease;" onclick="viewAppointment(${apt.id})" onmouseover="this.style.background='var(--bg)'; this.style.transform='translateX(4px)'" onmouseout="this.style.background='transparent'; this.style.transform='translateX(0)'">
                <div class="flex-grow-1">
                    <strong>${apt.start_time.substring(0, 5)}</strong> - ${patientNameEscaped} (${apt.visit_type})
                </div>
                <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteAppointment(${apt.id}, '${patientNameEscaped}', '${dateStr}')" title="Delete appointment">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
        });
    }
    
    if (dayData.notes.length > 0) {
        content += '<h6 class="text-primary mt-3"><i class="bi bi-sticky me-2"></i>Notes</h6>';
        dayData.notes.forEach(note => {
            const noteContentEscaped = note.content.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            content += `<div class="mb-2 p-2 border rounded d-flex justify-content-between align-items-center note-item-clickable" style="cursor: pointer; transition: all 0.2s ease;" onclick="viewNote(${note.id})" onmouseover="this.style.background='var(--bg)'; this.style.transform='translateX(4px)'" onmouseout="this.style.background='transparent'; this.style.transform='translateX(0)'">
                <div class="flex-grow-1">${noteContentEscaped}</div>
                <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteNote(${note.id}, '${dateStr}')" title="Delete note">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
        });
    }
    
    // Check if there are no events at all
    if (dayData.appointments.length === 0 && dayData.notes.length === 0 && filteredAlerts.length === 0) {
        content += `
            <div class="text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="color: var(--text) !important; font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted" style="color: var(--text) !important;">No events for this day</h5>
                <p class="text-muted mb-0" style="color: var(--text) !important;">There are no appointments, notes, or alerts scheduled for this day.</p>
            </div>
        `;
    }
    
    content += '</div>';
    
    // Show in modal with glass effect
    const modalId = 'dayDetailsModal-' + Date.now();
    const modalElement = document.createElement('div');
    modalElement.id = modalId;
    modalElement.className = 'modal fade organizer-modal-glass';
    modalElement.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Day Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modalElement);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Make modal draggable
    makeModalDraggable(modalElement);
    
    modalElement.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modalElement);
    });
}

function showDeleteModal(type, id, name, date) {
    const modalId = 'deleteModal-' + Date.now();
    const modalElement = document.createElement('div');
    modalElement.id = modalId;
    modalElement.className = 'modal fade organizer-modal-glass';
    
    let title = '';
    let message = '';
    let confirmFunction = '';
    
    if (type === 'appointment') {
        title = 'Delete Appointment';
        message = `Are you sure you want to delete the appointment for ${name} on ${date}?`;
        confirmFunction = `confirmDeleteAppointment(${id})`;
    } else if (type === 'note') {
        title = 'Delete Note';
        message = 'Are you sure you want to delete this note? This action cannot be undone.';
        confirmFunction = `confirmDeleteNote(${id})`;
    }
    
    modalElement.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="${confirmFunction}; bootstrap.Modal.getInstance(document.getElementById('${modalId}')).hide();">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modalElement);
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Make modal draggable
    makeModalDraggable(modalElement);
    
    modalElement.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modalElement);
    });
}

// Make modal draggable function
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

function showToast(type, title, message) {
    // Use Bootstrap toast if available
    const toastContainer = document.getElementById('toastContainer') || document.body;
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
</script>

