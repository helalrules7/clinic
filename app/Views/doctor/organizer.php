<link href="/app/Views/doctor/assets/css/organizer.css?v=<?= file_exists(__DIR__ . '/assets/css/organizer.css') ? filemtime(__DIR__ . '/assets/css/organizer.css') : time() ?>" rel="stylesheet">
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

<!-- Mobile Navigation Buttons (visible on mobile only) -->
<div class="row mb-3 d-md-none">
    <div class="col-12">
        <div class="d-flex justify-content-center gap-2 align-items-center">
            <button type="button" class="btn btn-outline-info organizer-mobile-nav-btn" id="mobilePrevDayBtn" title="Previous day">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="btn btn-info organizer-mobile-nav-btn" id="mobileGoToDateBtn" title="Go to specific date">
                <i class="bi bi-calendar-event me-1"></i>
                Go to Date
            </button>
            <button type="button" class="btn btn-success organizer-mobile-nav-btn" id="mobileGoToTodayBtn" title="Go to today">
                <i class="bi bi-calendar-check me-1"></i>
                Today
            </button>
            <button type="button" class="btn btn-outline-info organizer-mobile-nav-btn" id="mobileNextDayBtn" title="Next day">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
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

<!-- Mobile Fullscreen Navigation Overlay (for day name/date display during navigation only) -->
<div id="mobileNavigationOverlay" class="organizer-mobile-navigation-overlay d-md-none">
    <div class="organizer-mobile-navigation-overlay-content">
        <div id="mobileNavigationOverlayDayName" class="organizer-mobile-navigation-overlay-day-name"></div>
        <div id="mobileNavigationOverlayDate" class="organizer-mobile-navigation-overlay-date"></div>
    </div>
</div>

<!-- Date Picker Popover (Mobile only) -->
<div class="modal fade organizer-modal-glass" id="datePickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="datePickerYear" class="form-label">Year</label>
                    <select class="form-select" id="datePickerYear">
                        <!-- Will be populated by JavaScript -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="datePickerMonth" class="form-label">Month</label>
                    <select class="form-select" id="datePickerMonth">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="datePickerDay" class="form-label">Day</label>
                    <select class="form-select" id="datePickerDay">
                        <!-- Will be populated by JavaScript -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="datePickerGoBtn">Go</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/alert_modal.php'; ?>

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
    
    // Mobile navigation buttons
    document.getElementById('mobilePrevDayBtn')?.addEventListener('click', () => {
        navigateToDay(-1);
    });
    
    document.getElementById('mobileNextDayBtn')?.addEventListener('click', () => {
        navigateToDay(1);
    });
    
    document.getElementById('mobileGoToTodayBtn')?.addEventListener('click', () => {
        goToToday();
    });
    
    document.getElementById('mobileGoToDateBtn')?.addEventListener('click', () => {
        openDatePicker();
    });
    
    // Date picker handlers
    document.getElementById('datePickerGoBtn')?.addEventListener('click', () => {
        const year = parseInt(document.getElementById('datePickerYear').value);
        const month = parseInt(document.getElementById('datePickerMonth').value);
        const day = parseInt(document.getElementById('datePickerDay').value);
        goToSpecificDate(year, month, day);
    });
    
    // Update day options when month changes
    document.getElementById('datePickerMonth')?.addEventListener('change', () => {
        updateDatePickerDays();
    });
    
    document.getElementById('datePickerYear')?.addEventListener('change', () => {
        updateDatePickerDays();
    });
    
});

function loadOrganizerMonth() {
    const container = document.getElementById('organizerCalendar');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading calendar...</p></div>';
    
    return fetch(`/api/organizer/month?year=${currentYear}&month=${currentMonth}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.data) {
                organizerData = data.data.dataByDate || {};
                renderCalendar(data.data);
                updateMonthDisplay();
                return Promise.resolve();
            } else {
                container.innerHTML = '<div class="alert alert-danger">Failed to load calendar data</div>';
                return Promise.reject('Failed to load calendar data');
            }
        })
        .catch(error => {
            console.error('Error loading organizer:', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading calendar</div>';
            return Promise.reject(error);
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
        html += '<div class="organizer-day other-month" onclick="event.stopPropagation();"></div>';
    }
    
    // Days of the month
    const today = new Date();
    const isCurrentMonth = today.getFullYear() === data.year && today.getMonth() + 1 === data.month;
    
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${data.year}-${String(data.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = isCurrentMonth && day === today.getDate();
        const dayData = organizerData[dateStr] || { appointments: [], notes: [], alerts: [] };
        
        // Get day name
        const dayDate = new Date(data.year, data.month - 1, day);
        const dayName = dayNames[dayDate.getDay()];
        const fullDateStr = `${dayName}, ${day} ${monthNames[data.month - 1]} ${data.year}`;
        
        html += `<div class="organizer-day ${isToday ? 'today' : ''}" data-date="${dateStr}" data-day-name="${dayName}" data-full-date="${fullDateStr}" onclick="handleDayClick('${dateStr}', event)">`;
        html += `<button class="organizer-day-view-btn" onclick="event.stopPropagation(); showDayDetails('${dateStr}')" title="View all day details">
            <i class="bi bi-eye"></i>
        </button>`;
        html += `<button class="organizer-day-add-alert-btn" onclick="event.stopPropagation(); openAddAlertModal('${dateStr}')" title="Add alert for this day">
            <i class="bi bi-bell"></i>
        </button>`;
        html += `<div class="organizer-day-navigation-info">
            <div class="organizer-day-navigation-info-day-name">${dayName}</div>
            <div class="organizer-day-navigation-info-date">${day} ${monthNames[data.month - 1]} ${data.year}</div>
        </div>`;
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
        
        // Combine all items for unified display logic
        const sortedAlerts = [...filteredAlerts].sort((a, b) => {
            const timeA = a.alert_time || '00:00:00';
            const timeB = b.alert_time || '00:00:00';
            return timeB.localeCompare(timeA); // Descending order (newest first)
        });
        
        // Create combined items array
        const allItems = [];
        
        // Add alerts first
        sortedAlerts.forEach(alert => {
            allItems.push({ type: 'alert', data: alert });
        });
        
        // Add appointments
        dayData.appointments.forEach(appointment => {
            allItems.push({ type: 'appointment', data: appointment });
        });
        
        // Add notes
        dayData.notes.forEach(note => {
            allItems.push({ type: 'note', data: note });
        });
        
        const totalItems = allItems.length;
        const isMobile = window.innerWidth <= 768;
        
        // Determine how many items to show
        // On mobile: show all items (will be handled in renderFocusedDayContent when focused)
        // On desktop: show limited items
        let itemsToShow;
        if (isMobile) {
            // On mobile, show all items initially (they will be limited by CSS max-height)
            itemsToShow = totalItems;
        } else {
            const maxItemsToShow = Math.min(dayData.appointments.length, 3) + Math.min(dayData.notes.length, 2) + Math.min(filteredAlerts.length, 2);
            itemsToShow = maxItemsToShow;
        }
        
        // Show items
        allItems.slice(0, itemsToShow).forEach(item => {
            if (item.type === 'alert') {
                const alert = item.data;
                const alertText = alert.message.length > 20 ? alert.message.substring(0, 20) + '...' : alert.message;
                html += `
                    <div class="organizer-item alert" onclick="viewAlert(${alert.id})" title="${alert.message}">
                        <i class="bi bi-bell"></i>
                        <span class="organizer-item-text">${alertText}</span>
                    </div>
                `;
            } else if (item.type === 'appointment') {
                const appointment = item.data;
                const time = appointment.start_time.substring(0, 5);
                // Don't show delete button on mobile in calendar view
                const deleteBtn = isMobile ? '' : `<button class="organizer-item-delete" onclick="event.stopPropagation(); deleteAppointment(${appointment.id}, '${appointment.patient_name}', '${dateStr}')" title="Delete appointment">
                            <i class="bi bi-x"></i>
                        </button>`;
                html += `
                    <div class="organizer-item appointment" onclick="viewAppointment(${appointment.id})" title="${appointment.patient_name} - ${time}">
                        <i class="bi bi-calendar-event"></i>
                        <span class="organizer-item-text">${time} - ${appointment.patient_name}</span>
                        ${deleteBtn}
                    </div>
                `;
            } else if (item.type === 'note') {
                const note = item.data;
                const noteText = note.content.length > 20 ? note.content.substring(0, 20) + '...' : note.content;
                // Don't show delete button on mobile in calendar view
                const deleteBtn = isMobile ? '' : `<button class="organizer-item-delete" onclick="event.stopPropagation(); deleteNote(${note.id}, '${dateStr}')" title="Delete note">
                            <i class="bi bi-x"></i>
                        </button>`;
                html += `
                    <div class="organizer-item note" onclick="viewNote(${note.id})" title="${note.content}">
                        <i class="bi bi-sticky"></i>
                        <span class="organizer-item-text">${noteText}</span>
                        ${deleteBtn}
                    </div>
                `;
            }
        });
        
        html += '</div>';
        
        // Show "Show All" button on mobile only if day is focused (not during navigation)
        // This will be handled dynamically in renderFocusedDayContent
        
        // Add semi-transparent overlay on desktop if there are more than 3 events
        if (!isMobile && totalItems > 3) {
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
        html += '<div class="organizer-day other-month" onclick="event.stopPropagation();"></div>';
    }
    
    html += '</div>';
    container.innerHTML = html;
    
        // On mobile, focus today's date by default
        if (window.innerWidth <= 768) {
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
            if (isCurrentMonth && today.getDate() <= daysInMonth) {
                setTimeout(() => {
                    focusDay(todayStr, false);
                }, 100);
            } else if (daysInMonth > 0) {
                // Focus first day of month if today is not in current month
                const firstDayStr = `${data.year}-${String(data.month).padStart(2, '0')}-01`;
                setTimeout(() => {
                    focusDay(firstDayStr, false);
                }, 100);
            }
        }
}

function updateMonthDisplay() {
    const displayText = `${monthNames[currentMonth - 1]} ${currentYear}`;
    document.getElementById('currentMonthDisplay').textContent = displayText;
    document.getElementById('monthYearHeader').textContent = displayText;
}

// Handle day click - different behavior for mobile vs desktop
function handleDayClick(dateStr, event) {
    // Check if we're on mobile
    if (window.innerWidth <= 768) {
        // Mobile: open fullscreen overlay
        focusDay(dateStr, false);
    }
    // Desktop: do nothing on day click
}

// Focus day on mobile - center it and make it larger
let currentFocusedDate = null;

function focusDay(dateStr, showNavigationInfo = false) {
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        if (showNavigationInfo) {
            // Show navigation overlay with day name and date
            showNavigationOverlay(dateStr);
            // After delay, hide overlay and focus the day
            setTimeout(() => {
                hideNavigationOverlay();
                focusDayInCalendar(dateStr);
            }, 500);
        } else {
            // Directly focus the day in calendar
            focusDayInCalendar(dateStr);
        }
    } else {
        // Desktop behavior (keep existing)
        document.querySelectorAll('.organizer-day').forEach(day => {
            day.classList.remove('focused', 'navigating');
        });
        
        const dayElement = document.querySelector(`.organizer-day[data-date="${dateStr}"]`);
        if (dayElement) {
            dayElement.classList.add('focused');
            currentFocusedDate = dateStr;
        }
    }
}

// Focus day in calendar (mobile) - expand it and show all content
function focusDayInCalendar(dateStr) {
    currentFocusedDate = dateStr;
    
    // Remove focused class from all days
    document.querySelectorAll('.organizer-day').forEach(day => {
        day.classList.remove('focused');
    });
    
    // Add focused class to selected day
    const dayElement = document.querySelector(`.organizer-day[data-date="${dateStr}"]`);
    if (dayElement) {
        dayElement.classList.add('focused');
        
        // Render all content for focused day
        renderFocusedDayContent(dateStr);
        
        // Scroll page to show the focused day completely with all its content visible
        setTimeout(() => {
            // Wait for content to render
            setTimeout(() => {
                // Scroll the entire page to show the focused day
                const dayRect = dayElement.getBoundingClientRect();
                const currentScrollY = window.scrollY || window.pageYOffset;
                
                // Calculate scroll position to show the day's bottom edge
                const scrollToY = currentScrollY + dayRect.top - (window.innerHeight * 0.1); // Leave 10% margin at top
                
                window.scrollTo({
                    top: scrollToY,
                    behavior: 'smooth'
                });
                
                // Also scroll calendar container horizontally if needed
                const calendarContainer = document.querySelector('.organizer-calendar');
                if (calendarContainer) {
                    const containerRect = calendarContainer.getBoundingClientRect();
                    const scrollLeft = calendarContainer.scrollLeft + (dayRect.right - containerRect.right);
                    
                    calendarContainer.scrollTo({
                        left: scrollLeft,
                        behavior: 'smooth'
                    });
                }
            }, 200);
        }, 100);
    }
}

// Show navigation overlay (day name and date only)
function showNavigationOverlay(dateStr) {
    const overlay = document.getElementById('mobileNavigationOverlay');
    const dayNameEl = document.getElementById('mobileNavigationOverlayDayName');
    const dateEl = document.getElementById('mobileNavigationOverlayDate');
    
    if (!overlay || !dayNameEl || !dateEl) return;
    
    const dayDate = new Date(dateStr);
    const dayName = dayNames[dayDate.getDay()];
    const dayNumber = dayDate.getDate();
    const monthName = monthNames[dayDate.getMonth()];
    const year = dayDate.getFullYear();
    
    dayNameEl.textContent = dayName;
    dateEl.textContent = `${dayNumber} ${monthName} ${year}`;
    
    overlay.classList.add('active');
}

// Hide navigation overlay
function hideNavigationOverlay() {
    const overlay = document.getElementById('mobileNavigationOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Render focused day content with all items
function renderFocusedDayContent(dateStr) {
    const isMobile = window.innerWidth <= 768;
    
    const dayElement = document.querySelector(`.organizer-day[data-date="${dateStr}"]`);
    if (!dayElement) return;
    
    const dayData = organizerData[dateStr] || { appointments: [], notes: [], alerts: [] };
    
    // Filter alerts
    const appointmentIds = dayData.appointments.map(apt => apt.id);
    const filteredAlerts = dayData.alerts.filter(alert => {
        if (alert.appointment_id && appointmentIds.includes(parseInt(alert.appointment_id))) {
            return false;
        }
        return true;
    });
    
    // Combine all items
    const sortedAlerts = [...filteredAlerts].sort((a, b) => {
        const timeA = a.alert_time || '00:00:00';
        const timeB = b.alert_time || '00:00:00';
        return timeB.localeCompare(timeA);
    });
    
    const allItems = [];
    sortedAlerts.forEach(alert => {
        allItems.push({ type: 'alert', data: alert });
    });
    dayData.appointments.forEach(appointment => {
        allItems.push({ type: 'appointment', data: appointment });
    });
    dayData.notes.forEach(note => {
        allItems.push({ type: 'note', data: note });
    });
    
    const totalItems = allItems.length;
    
    // Get items container
    const itemsContainer = dayElement.querySelector('.organizer-day-items');
    if (!itemsContainer) return;
    
    // Clear existing items
    itemsContainer.innerHTML = '';
    
    // Render all items (on mobile, show all items when focused)
    allItems.forEach(item => {
        if (item.type === 'alert') {
            const alert = item.data;
            const alertText = isMobile ? alert.message : (alert.message.length > 20 ? alert.message.substring(0, 20) + '...' : alert.message);
            itemsContainer.innerHTML += `
                <div class="organizer-item alert" onclick="viewAlert(${alert.id})" title="${alert.message}">
                    <i class="bi bi-bell"></i>
                    <span class="organizer-item-text">${alertText}</span>
                </div>
            `;
        } else if (item.type === 'appointment') {
            const appointment = item.data;
            const time = appointment.start_time.substring(0, 5);
            // On mobile, show delete button only when focused
            const deleteBtn = isMobile ? `<button class="organizer-item-delete" onclick="event.stopPropagation(); deleteAppointment(${appointment.id}, '${appointment.patient_name}', '${dateStr}')" title="Delete appointment">
                        <i class="bi bi-x"></i>
                    </button>` : `<button class="organizer-item-delete" onclick="event.stopPropagation(); deleteAppointment(${appointment.id}, '${appointment.patient_name}', '${dateStr}')" title="Delete appointment">
                        <i class="bi bi-x"></i>
                    </button>`;
            itemsContainer.innerHTML += `
                <div class="organizer-item appointment" onclick="viewAppointment(${appointment.id})" title="${appointment.patient_name} - ${time}">
                    <i class="bi bi-calendar-event"></i>
                    <span class="organizer-item-text">${time} - ${appointment.patient_name}</span>
                    ${deleteBtn}
                </div>
            `;
        } else if (item.type === 'note') {
            const note = item.data;
            const noteText = isMobile ? note.content : (note.content.length > 20 ? note.content.substring(0, 20) + '...' : note.content);
            // On mobile, show delete button only when focused
            const deleteBtn = isMobile ? `<button class="organizer-item-delete" onclick="event.stopPropagation(); deleteNote(${note.id}, '${dateStr}')" title="Delete note">
                        <i class="bi bi-x"></i>
                    </button>` : `<button class="organizer-item-delete" onclick="event.stopPropagation(); deleteNote(${note.id}, '${dateStr}')" title="Delete note">
                        <i class="bi bi-x"></i>
                    </button>`;
            itemsContainer.innerHTML += `
                <div class="organizer-item note" onclick="viewNote(${note.id})" title="${note.content}">
                    <i class="bi bi-sticky"></i>
                    <span class="organizer-item-text">${noteText}</span>
                    ${deleteBtn}
                </div>
            `;
        }
    });
    
    // Remove existing Show All button if any
    const existingShowAllBtn = dayElement.querySelector('.organizer-day-show-all-btn');
    if (existingShowAllBtn) {
        existingShowAllBtn.remove();
    }
    
    // On mobile, check if items exceed view height and add Show All button if needed
    if (isMobile) {
        setTimeout(() => {
            const itemsHeight = itemsContainer.scrollHeight;
            const containerHeight = itemsContainer.clientHeight;
            
            // Only show "Show All" button if items actually exceed the visible height
            // This means user can scroll within the day to see all items
            if (itemsHeight > containerHeight) {
                // Items exceed view height, add Show All button to open modal
                const showAllBtn = document.createElement('button');
                showAllBtn.className = 'organizer-day-show-all-btn';
                showAllBtn.onclick = (e) => {
                    e.stopPropagation();
                    showDayDetails(dateStr);
                };
                const remainingItems = Math.ceil((itemsHeight - containerHeight) / 50); // Approximate items not visible
                showAllBtn.innerHTML = `<i class="bi bi-arrow-down-circle me-1"></i>Show All (${totalItems} items)`;
                dayElement.appendChild(showAllBtn);
            }
        }, 300);
    }
}


// Navigate to previous/next day
function navigateToDay(direction) {
    if (!currentFocusedDate) {
        // If no day is focused, focus today
        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        focusDay(todayStr, false);
        return;
    }
    
    const currentDate = new Date(currentFocusedDate);
    currentDate.setDate(currentDate.getDate() + direction);
    
    // Check if we need to load a different month
    const newYear = currentDate.getFullYear();
    const newMonth = currentDate.getMonth() + 1;
    
    if (newYear !== currentYear || newMonth !== currentMonth) {
        currentYear = newYear;
        currentMonth = newMonth;
        loadOrganizerMonth().then(() => {
            const dateStr = `${newYear}-${String(newMonth).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`;
            setTimeout(() => {
                focusDay(dateStr, false);
            }, 300);
        });
    } else {
        const dateStr = `${newYear}-${String(newMonth).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`;
        focusDay(dateStr, false);
    }
}

// Go to today
function goToToday() {
    const today = new Date();
    const todayYear = today.getFullYear();
    const todayMonth = today.getMonth() + 1;
    const todayDay = today.getDate();
    
    if (todayYear !== currentYear || todayMonth !== currentMonth) {
        currentYear = todayYear;
        currentMonth = todayMonth;
        loadOrganizerMonth().then(() => {
            const dateStr = `${todayYear}-${String(todayMonth).padStart(2, '0')}-${String(todayDay).padStart(2, '0')}`;
            setTimeout(() => {
                focusDay(dateStr, false);
            }, 300);
        });
    } else {
        const dateStr = `${todayYear}-${String(todayMonth).padStart(2, '0')}-${String(todayDay).padStart(2, '0')}`;
        focusDay(dateStr, false);
    }
}

// Open date picker modal
function openDatePicker() {
    // Populate year dropdown
    const yearSelect = document.getElementById('datePickerYear');
    if (yearSelect) {
        yearSelect.innerHTML = '';
        const currentYearNum = new Date().getFullYear();
        for (let year = currentYearNum - 5; year <= currentYearNum + 5; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) {
                option.selected = true;
            }
            yearSelect.appendChild(option);
        }
    }
    
    // Set month
    const monthSelect = document.getElementById('datePickerMonth');
    if (monthSelect) {
        monthSelect.value = currentMonth;
    }
    
    // Update days
    updateDatePickerDays();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('datePickerModal'));
    modal.show();
}

// Update day options based on selected year and month
function updateDatePickerDays() {
    const yearSelect = document.getElementById('datePickerYear');
    const monthSelect = document.getElementById('datePickerMonth');
    const daySelect = document.getElementById('datePickerDay');
    
    if (!yearSelect || !monthSelect || !daySelect) return;
    
    const year = parseInt(yearSelect.value);
    const month = parseInt(monthSelect.value);
    const daysInMonth = new Date(year, month, 0).getDate();
    
    daySelect.innerHTML = '';
    for (let day = 1; day <= daysInMonth; day++) {
        const option = document.createElement('option');
        option.value = day;
        option.textContent = day;
        if (day === new Date().getDate() && year === currentYear && month === currentMonth) {
            option.selected = true;
        }
        daySelect.appendChild(option);
    }
}

// Go to specific date
function goToSpecificDate(year, month, day) {
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('datePickerModal'));
    if (modal) {
        modal.hide();
    }
    
    if (year !== currentYear || month !== currentMonth) {
        currentYear = year;
        currentMonth = month;
        loadOrganizerMonth().then(() => {
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            setTimeout(() => {
                focusDay(dateStr, false);
            }, 300);
        });
    } else {
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        focusDay(dateStr, false);
    }
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
    const overlayDate = currentFocusedDate;
    
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
            loadOrganizerMonth().then(() => {
                // Reopen overlay if it was open
                if (wasOverlayOpen && overlayDate) {
                    setTimeout(() => {
                        focusDay(overlayDate, false);
                    }, 300);
                }
            });
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
    const overlayDate = currentFocusedDate;
    
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
            loadOrganizerMonth().then(() => {
                // Reopen overlay if it was open
                if (wasOverlayOpen && overlayDate) {
                    setTimeout(() => {
                        focusDay(overlayDate, false);
                    }, 300);
                }
            });
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

