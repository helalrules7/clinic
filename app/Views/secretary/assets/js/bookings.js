const CFG = window.BOOKINGS_CONFIG || {};
const SERVER_DATE = CFG.serverDate || new Date().toISOString().slice(0, 10);
const SERVER_DATETIME = CFG.serverDateTime || '';
const SERVER_TIMESTAMP = CFG.serverTimestamp || Math.floor(Date.now() / 1000);
const SYSTEM_SETTINGS = CFG.settings || {};
const ROUTES = CFG.routes || {
    calendar: '/secretary/bookings',
    bookingDetail: '/secretary/bookings',
    patientProfile: '/secretary/patients'
};

function normalizePreselectedPatient(p) {
    if (!p) return null;
    const firstName = p.first_name || '';
    const lastName = p.last_name || '';
    const fullName = (p.full_name || `${firstName} ${lastName}`).trim();
    return {
        id: p.id,
        full_name: fullName,
        phone: p.phone || '',
        age: p.age != null ? p.age : null,
        first_name: firstName,
        last_name: lastName
    };
}

let preselectedPatient = CFG.preselectedPatient
    ? normalizePreselectedPatient(CFG.preselectedPatient)
    : null;

let currentDate = new Date();
let selectedDoctorId = null;
let refreshInterval;
let highlightedAppointmentId = null;
let currentTimeFilter = null;
let calendarData = null;

window.SEC_BOOKINGS_MINI_CARDS = [
    { chartId: 'chartBkTotal', trendId: 'trendBkTotal', trendKey: 'total', statKey: 'total_appointments', valueId: 'totalBookings', staticToday: true },
    { chartId: 'chartBkBooked', trendId: 'trendBkBooked', trendKey: 'booked', statKey: 'booked', valueId: 'pendingBookings' },
    { chartId: 'chartBkCheckedIn', trendId: 'trendBkCheckedIn', trendKey: 'checked_in', statKey: 'checked_in', valueId: 'checkedInBookings' },
    { chartId: 'chartBkCompleted', trendId: 'trendBkCompleted', trendKey: 'completed', statKey: 'completed', valueId: 'completedBookings' }
];

function initBookingsMiniStats() {
    if (!window.secMiniStats) return;
    window.secMiniStats.init(window.SEC_BOOKINGS_MINI_CARDS, {
        trendsId: 'secBookingsTrends',
        statsId: 'secBookingsStatsInitial',
        deltasId: 'secBookingsTrendDeltas'
    });
}

function initializeDateFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');
    const appointmentIdParam = urlParams.get('appointment_id');

    if (appointmentIdParam) {
        highlightedAppointmentId = parseInt(appointmentIdParam, 10);
    }

    if (dateParam && /^\d{4}-\d{2}-\d{2}$/.test(dateParam)) {
        const [year, month, day] = dateParam.split('-').map(Number);
        currentDate = new Date(year, month - 1, day, 12, 0, 0);
    } else {
        const today = new Date();
        currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeDateFromURL();
    initBookingsMiniStats();
    setupEventListeners();
    updateStatistics([]);
    initCustomSelects();
    setupBookingsActionsTooltipGuard();
    setupBookingsTooltipLifecycle();

    const autoRefreshEnabled = getAutoRefreshState();
    const toggleSwitch = document.getElementById('bookingsAutoRefresh');
    if (toggleSwitch) {
        toggleSwitch.checked = autoRefreshEnabled;
    }

    loadCalendar();

    if (autoRefreshEnabled) {
        startAutoRefresh();
    }
});

function setupEventListeners() {
    // Navigation buttons
    document.getElementById('todayBtn').addEventListener('click', () => {
        const today = new Date();
        currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12, 0, 0);
        updateDateDisplay();
        loadCalendar();
    });
    
    document.getElementById('prevDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() - 24 * 60 * 60 * 1000);
        updateDateDisplay();
        loadCalendar();
    });
    
    document.getElementById('nextDayBtn').addEventListener('click', () => {
        currentDate = new Date(currentDate.getTime() + 24 * 60 * 60 * 1000);
        updateDateDisplay();
        loadCalendar();
    });
    
    // Add booking button
    document.getElementById('addBookingBtn').addEventListener('click', () => {
        openAddBookingModal();
    });
    
    // Patient search
    document.getElementById('patientSearch').addEventListener('input', debounce(searchPatients, 300));
    
    // Visit type change - update cost
    document.getElementById('visitType').addEventListener('change', updateVisitCost);
    
    // Payment amount validation
    document.getElementById('paymentAmount').addEventListener('input', validatePaymentAmount);
    document.getElementById('paymentAmount').addEventListener('change', validatePaymentAmount);
    
    // Add booking form submission
    document.getElementById('addBookingForm').addEventListener('submit', handleAddBooking);
    
    // New patient button
    document.getElementById('newPatientBtn').addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('addBookingModal')).hide();
        setTimeout(() => {
            const addPatientModal = new bootstrap.Modal(document.getElementById('addPatientModal'));
            addPatientModal.show();
        }, 300);
    });
    
    // Delete booking confirmation
    document.getElementById('confirmDeleteBookingBtn').addEventListener('click', confirmDeleteBooking);
    
    // Confirm attendance
    document.getElementById('confirmAttendanceBtn').addEventListener('click', confirmAttendanceAction);
    
    // Edit booking
    document.getElementById('saveEditBookingBtn').addEventListener('click', saveEditBooking);
    
    // Edit patient search
    document.getElementById('editPatientSearch').addEventListener('input', debounce(editSearchPatients, 300));
    
    // Edit form change events
    document.getElementById('editVisitType').addEventListener('change', updateEditVisitCost);
    document.getElementById('editAdditionalPayment').addEventListener('input', updateEditPaymentInfo);
    document.getElementById('editBookingDate').addEventListener('change', function() {
        const doctorId = document.getElementById('editDoctor').value;
        const currentBookingId = document.getElementById('editBookingId').value;
        const currentTime = document.getElementById('editBookingTime').value;
        if (doctorId && this.value) {
            loadEditAvailableTimeSlots(this.value, doctorId, currentBookingId, currentTime);
        }
    });
    document.getElementById('editDoctor').addEventListener('change', function() {
        const date = document.getElementById('editBookingDate').value;
        const currentBookingId = document.getElementById('editBookingId').value;
        const currentTime = document.getElementById('editBookingTime').value;
        if (date && this.value) {
            loadEditAvailableTimeSlots(date, this.value, currentBookingId, currentTime);
        }
    });

    bindClinicIconSync('bookingClinic', 'bookingClinicIcon');
    bindClinicIconSync('patientClinic', 'patientClinicIcon');

    document.querySelectorAll('.filter-time-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            applyTimeFilter(this.getAttribute('data-filter'));
        });
    });

    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    if (mobileFilterBtn) {
        const filterButtonsHTML = `
            <div class="mobile-filter-popover">
                <div class="mb-3">
                    <h6 class="mb-3 arabic-text"><i class="bi bi-funnel me-2"></i>تصفية الأوقات</h6>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info filter-time-btn w-100 arabic-text" data-filter="2pm-6pm">
                            <i class="bi bi-clock me-1"></i>٢:٠٠ م – ٦:٠٠ م
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-time-btn w-100 arabic-text" data-filter="6pm-1045pm">
                            <i class="bi bi-clock me-1"></i>٦:٠٠ م – ١٠:٤٥ م
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary filter-time-btn w-100 arabic-text" data-filter="available">
                            <i class="bi bi-check-circle me-1"></i>المتاح فقط
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning filter-time-btn w-100 arabic-text" data-filter="unavailable">
                            <i class="bi bi-x-circle me-1"></i>الحجوزات فقط
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary filter-time-btn w-100 arabic-text" data-filter="none">
                            <i class="bi bi-x-lg me-1"></i>إلغاء التصفية
                        </button>
                    </div>
                </div>
            </div>`;

        const popoverInstance = new bootstrap.Popover(mobileFilterBtn, {
            html: true,
            content: filterButtonsHTML,
            placement: 'bottom',
            trigger: 'click',
            container: 'body',
            sanitize: false
        });

        mobileFilterBtn.addEventListener('shown.bs.popover', function () {
            const popoverElement = document.querySelector('.popover');
            if (!popoverElement) return;
            popoverElement.classList.add('mobile-filter-popover-glass');
            popoverElement.querySelectorAll('.filter-time-btn').forEach((btn) => {
                btn.addEventListener('click', function () {
                    applyTimeFilter(this.getAttribute('data-filter'));
                    setTimeout(() => popoverInstance.hide(), 300);
                });
            });
        });

        mobileFilterBtn.addEventListener('hidden.bs.popover', function () {
            const popoverElement = document.querySelector('.popover');
            if (popoverElement) {
                popoverElement.classList.remove('mobile-filter-popover-glass');
            }
            updateFilterButtonStates();
        });
    }
}

function loadCalendar() {
    const dateStr = currentDate.toISOString().split('T')[0];
    
    // Show loading indicator
    const calendarContainer = document.getElementById('bookingsCalendarContainer');
    if (calendarContainer) {
        calendarContainer.parentElement.classList.add('table-loading', 'loading');
    }
    
    fetch(`/secretary/bookings/calendar?date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                calendarData = data.data;
                renderCalendar(data.data);
                updateDateDisplay();
                updateLastUpdate();
                updateStatistics(data.data.appointments || []);
                updateStatusIndicator();
                setTimeout(() => {
                    initializeTooltips();
                    initializeAppointmentProgressBars();
                    if (highlightedAppointmentId) {
                        setTimeout(scrollToHighlightedAppointment, 300);
                    }
                }, 100);
            } else {
                showNotification('خطأ في تحميل التقويم: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            showNotification('خطأ في تحميل التقويم', 'danger');
        })
        .finally(() => {
            // Remove loading indicator
            const calendarContainer = document.getElementById('bookingsCalendarContainer');
            if (calendarContainer) {
                calendarContainer.parentElement.classList.remove('table-loading', 'loading');
            }
        });
}

function purgeOrphanedBookingsTooltipNodes() {
    document.querySelectorAll('body > .tooltip').forEach((node) => node.remove());
}

function disposeAllBookingsTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        const tip = bootstrap.Tooltip.getInstance(element);
        if (tip) tip.dispose();
    });
    purgeOrphanedBookingsTooltipNodes();
}

function hideAllBookingsTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        const tip = bootstrap.Tooltip.getInstance(element);
        if (tip) tip.hide();
    });
    purgeOrphanedBookingsTooltipNodes();
}

function renderCalendar(data) {
    disposeAllBookingsTooltips();
    const container = document.getElementById('bookingsCalendarContainer');
    const timeSlots = generateTimeSlots();
    const dateStr = data.date || currentDate.toISOString().split('T')[0];
    const currentDateObj = new Date(dateStr + 'T12:00:00');
    const isFriday = currentDateObj.getDay() === 5;

    let html = '<div class="calendar-grid bookings-calendar-grid">';

    html += '<div class="calendar-header bookings-calendar-header">';
    html += '<div class="arabic-text">الوقت</div>';
    html += '<div class="arabic-text">الحجوزات</div>';
    html += '</div>';

    if (isFriday || data.is_friday) {
        const dayName = currentDateObj.toLocaleDateString('ar-EG', { weekday: 'long' });
        timeSlots.forEach(time => {
            if (!shouldDisplayTimeSlot(time, data)) return;
            html += '<div class="calendar-row bookings-calendar-row">';
            html += `<div class="time-slot bookings-time-slot">${formatTime(time)}</div>`;
            html += '<div class="appointment-slot bookings-appointment-slot">';
            html += `<div class="unavailable-slot bookings-unavailable-slot official-holiday">
                       <i class="bi bi-calendar-x me-2"></i>
                       <div class="holiday-info">
                           <div class="holiday-title arabic-text">عطلة رسمية</div>
                           <div class="holiday-subtitle arabic-text">${dayName}</div>
                       </div>
                     </div>`;
            html += '</div></div>';
        });
    } else {
        timeSlots.forEach(time => {
            if (!shouldDisplayTimeSlot(time, data)) return;

            const appointments = findAppointmentsAtSlot(data, time);
            const isAvailable = (data.available_slots || []).includes(time);
            const unavailableSlot = data.unavailable_slots
                ? data.unavailable_slots.find(slot => slot.time === time)
                : null;

            html += '<div class="calendar-row bookings-calendar-row">';
            html += `<div class="time-slot bookings-time-slot">${formatTime(time)}</div>`;
            html += '<div class="appointment-slot bookings-appointment-slot">';

            if (appointments.length > 0) {
                appointments.forEach(appointment => {
                    html += renderAppointmentSlot(appointment);
                });
            } else if (isAvailable) {
                html += `<div class="available-slot bookings-available-slot" onclick="quickAddBooking('${time}')"
                              title="اضغط لحجز موعد في ${formatTime(time)}">
                            <i class="bi bi-plus-circle me-2"></i><span class="arabic-text">متاح - ${formatTime(time)}</span>
                         </div>`;
            } else if (unavailableSlot && unavailableSlot.reason === 'Outside working hours') {
                html += `<div class="unavailable-slot bookings-unavailable-slot arabic-text">
                           <i class="bi bi-clock me-2"></i>خارج ساعات العمل
                         </div>`;
            } else {
                html += `<div class="unavailable-slot bookings-unavailable-slot arabic-text">
                           <i class="bi bi-x-circle me-2"></i>غير متاح
                         </div>`;
            }

            html += '</div></div>';
        });
    }

    html += '</div>';
    container.innerHTML = html;
    if (window.patientHover && typeof window.patientHover.retag === 'function') {
        window.patientHover.retag(container);
    }
}

function renderAppointmentSlot(appointment) {
    const appointmentDate = appointment.date || currentDate.toISOString().split('T')[0];
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const aptDate = new Date(appointmentDate + 'T00:00:00');
    const isMissed = appointment.status === 'Booked' && today > aptDate;
    const isCompleted = appointment.status && appointment.status.toLowerCase() === 'completed';
    const effectiveStatus = isMissed ? 'missed' : (appointment.status || '').toLowerCase();

    let statusClass, statusText, statusIcon;
    if (isMissed) {
        statusClass = 'bg-danger text-white';
        statusText = 'فائت';
        statusIcon = 'bi-exclamation-triangle';
    } else {
        statusClass = getStatusBadgeClass(appointment.status);
        statusText = getStatusDisplayText(appointment.status);
        statusIcon = getStatusIcon(appointment.status);
    }

    const totalPaid = appointment.total_paid || 0;
    const visitCost = appointment.visit_cost || 0;
    const remainingAmount = visitCost - totalPaid;
    const isHighlighted = highlightedAppointmentId && appointment.id === highlightedAppointmentId;
    const highlightClass = isHighlighted ? 'highlighted-appointment' : '';
    const safeName = escAttr(appointment.patient_name);
    const safeDoctor = escAttr(appointment.doctor_display_name);
    const safeNotes = escAttr(appointment.notes || '');
    const safeVisitAr = escAttr(getVisitTypeInArabic(appointment.visit_type));
    const timeShort = formatTime(normalizeStartTime(appointment.start_time));
    const clinicMeta = resolveClinicMeta(appointment);

    const tooltipContent = `
        <div class="appointment-tooltip">
            <div class="tooltip-header"><strong class="arabic-text">تفاصيل الحجز</strong></div>
            <div class="tooltip-body">
                <div class="tooltip-row"><span class="tooltip-label arabic-text">المريض:</span>${tooltipValueHtml(appointment.patient_name)}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">الطبيب:</span>${tooltipValueHtml(appointment.doctor_display_name || '—')}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">العيادة:</span>${tooltipValueHtml(clinicMeta.name ? renderClinicChip(appointment) : '—')}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">الهاتف:</span>${tooltipValueHtml(appointment.phone || '—')}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">نوع الزيارة:</span>${tooltipValueHtml(getVisitTypeInArabic(appointment.visit_type))}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">الوقت:</span>${tooltipValueHtml(`${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}`)}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">الحالة:</span>${tooltipValueHtml(statusText)}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">المدفوع:</span>${tooltipValueHtml(`${totalPaid} جنيه`)}</div>
                <div class="tooltip-row"><span class="tooltip-label arabic-text">المتبقي:</span>${tooltipValueHtml(`${remainingAmount} جنيه`)}</div>
                ${appointment.notes ? `<div class="tooltip-row"><span class="tooltip-label arabic-text">ملاحظات:</span>${tooltipValueHtml(appointment.notes)}</div>` : ''}
            </div>
            <div class="tooltip-footer"><small class="arabic-text">اضغط لعرض تفاصيل الحجز</small></div>
        </div>`
        .replace(/\n\s+/g, ' ')
        .trim();

    const showProgress = appointment.status
        && appointment.status.toLowerCase() !== 'completed'
        && appointment.status.toLowerCase() !== 'rescheduled'
        && !isMissed;

    return `
        <div class="appointment-card bookings-appointment-card ${effectiveStatus} ${highlightClass}"
             data-appointment-id="${appointment.id}"
             ${clinicMeta.id != null ? `data-clinic-id="${clinicMeta.id}"` : ''}>
            <div class="appointment-header ${isMissed ? 'missed' : ''} ${isCompleted ? 'completed' : ''}">
                <div class="appointment-info"
                     data-bs-toggle="tooltip"
                     data-bs-placement="right"
                     data-bs-html="true"
                     data-bs-title="${tooltipContent.replace(/"/g, '&quot;')}"
                     onclick="viewAppointmentDetails(${appointment.id})">
                    <div class="info-line arabic-text"><span class="label">المريض:</span>
                        <span class="patient-hover-name" data-patient-id="${appointment.patient_id}">${appointment.patient_name}</span>
                    </div>
                    <div class="info-line arabic-text"><span class="label">الطبيب:</span> ${appointment.doctor_display_name || '—'}</div>
                    ${clinicMeta.name
                        ? `<div class="info-line arabic-text"><span class="label">العيادة:</span> ${renderClinicChip(appointment)}</div>` : ''}
                    <div class="info-line arabic-text"><span class="label">النوع:</span> ${getVisitTypeInArabic(appointment.visit_type)}</div>
                    <div class="info-line arabic-text"><span class="label">الوقت:</span> ${formatTime(appointment.start_time)} - ${formatTime(appointment.end_time)}</div>
                    <div class="info-line arabic-text small">المدفوع: ${totalPaid} جنيه · المتبقي: ${remainingAmount} جنيه</div>
                    ${showProgress ? `
                    <div class="appointment-progress-container mt-2"
                         data-appointment-id="${appointment.id}"
                         data-date="${appointmentDate}"
                         data-start-time="${appointment.start_time}"
                         data-end-time="${appointment.end_time}">
                        <div class="glass-progress-bar">
                            <div class="glass-progress-fill" style="width:0%;"></div>
                            <div class="glass-progress-text arabic-text">00:00</div>
                        </div>
                    </div>` : ''}
                </div>
                <div class="appointment-status">
                    ${appointment.status === 'Rescheduled' ? `
                    <a href="${ROUTES.bookingDetail}/${appointment.id}"
                       class="badge bg-warning text-dark d-flex align-items-center gap-1 arabic-text"
                       style="text-decoration:none;font-weight:bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="تم تأجيل هذا الموعد">
                        <i class="bi bi-arrow-clockwise"></i>مؤجل
                    </a>` : ''}
                    ${appointment.has_followup ? `
                    <a href="${ROUTES.bookingDetail}/${appointment.followup_id}"
                       class="badge bg-success d-flex align-items-center gap-1 arabic-text"
                       style="text-decoration:none;font-weight:bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="موعد متابعة — اضغط للعرض">
                        <i class="bi bi-calendar-check"></i>متابعة
                    </a>` : ''}
                    ${appointment.is_followup && appointment.original_appointment_id ? `
                    <a href="${ROUTES.bookingDetail}/${appointment.original_appointment_id}"
                       class="badge bg-info d-flex align-items-center gap-1 arabic-text"
                       style="text-decoration:none;font-weight:bold;"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="الموعد الأصلي">
                        <i class="bi bi-calendar-event"></i>أصلي
                    </a>` : ''}
                    <span class="badge ${statusClass} d-flex align-items-center gap-1 arabic-text">
                        <i class="bi ${statusIcon}"></i>${statusText}
                    </span>
                </div>
                <div class="appointment-actions">
                    <a href="${ROUTES.patientProfile}/${appointment.patient_id}"
                       class="btn btn-sm btn-outline-info view-patient-btn"
                       onclick="event.stopPropagation();"
                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="ملف المريض">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    ${appointment.status === 'Booked' && !isMissed ? `
                    <button class="btn btn-sm confirm-attendance-btn"
                            onclick="event.stopPropagation(); confirmAttendance(${appointment.id}, '${safeName}', '${timeShort}', '${safeDoctor}', '${appointment.visit_type}', ${totalPaid}, ${remainingAmount})"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="تأكيد الحضور">
                        <i class="bi bi-check-circle"></i>
                    </button>` : ''}
                    ${appointment.status !== 'CheckedIn' && appointment.status !== 'Completed' ? `
                    <button class="btn btn-sm btn-outline-primary edit-appointment-btn"
                            onclick="event.stopPropagation(); editBooking(${appointment.id})"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="تعديل الحجز">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-appointment-btn"
                            onclick="event.stopPropagation(); deleteBooking(${appointment.id}, '${safeName}', '${timeShort}', '${safeDoctor}', '${safeVisitAr}', ${totalPaid}, ${remainingAmount}, '${safeNotes}')"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="حذف الحجز">
                        <i class="bi bi-trash"></i>
                    </button>` : ''}
                </div>
            </div>
            <div class="appointment-notes arabic-text" onclick="viewAppointmentDetails(${appointment.id})">
                ${appointment.notes ? appointment.notes.substring(0, 50) + '...' : 'لا توجد ملاحظات'}
            </div>
        </div>`;
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

function isMostlyLatinText(text) {
    const plain = String(text).replace(/<[^>]+>/g, '').trim();
    if (!plain) return false;
    const latin = (plain.match(/[A-Za-z]/g) || []).length;
    const arabic = (plain.match(/[\u0600-\u06FF]/g) || []).length;
    return latin > 0 && latin >= arabic;
}

function tooltipValueHtml(value) {
    const cls = isMostlyLatinText(value)
        ? 'tooltip-value tooltip-value-ltr'
        : 'tooltip-value tooltip-value-rtl';
    return `<span class="${cls}">${value}</span>`;
}

function buildPreselectedPatientInfoHtml(patient) {
    const fullName = patient.full_name || `مريض رقم ${patient.id}`;
    const phone = patient.phone || '—';
    const age = patient.age != null ? patient.age : 'غير محدد';
    const hasDetails = Boolean(patient.full_name && patient.phone);
    const subText = hasDetails
        ? `الهاتف: ${phone} • العمر: ${age}`
        : 'تم تحديد المريض مسبقاً';

    return `
        <div class="selected-patient-info alert alert-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>المريض المحدد:</strong> ${fullName}<br>
                    <small>${subText}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                    تغيير المريض
                </button>
            </div>
        </div>
    `;
}

function applyPreselectedPatientUI(patient) {
    if (!patient || !patient.id) return;
    const normalized = normalizePreselectedPatient(patient);
    preselectedPatient = normalized;

    document.getElementById('selectedPatientId').value = normalized.id;
    document.getElementById('patientSearch').value = normalized.full_name || `مريض رقم ${normalized.id}`;
    document.getElementById('patientSearchResults').innerHTML = buildPreselectedPatientInfoHtml(normalized);
    document.getElementById('preselectedLabel').style.display = 'inline';

    const patientSearchField = document.getElementById('patientSearch');
    patientSearchField.readOnly = true;
    patientSearchField.style.backgroundColor = 'var(--bg)';
    patientSearchField.style.cursor = 'not-allowed';
    document.getElementById('newPatientBtn').style.display = 'none';
}

function clearPreselectedPatientUI() {
    preselectedPatient = null;
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('patientSearch').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';

    const patientSearchField = document.getElementById('patientSearch');
    patientSearchField.readOnly = false;
    patientSearchField.style.backgroundColor = '';
    patientSearchField.style.cursor = '';
    document.getElementById('newPatientBtn').style.display = 'block';
    document.getElementById('preselectedLabel').style.display = 'none';
}

function fetchPreselectedPatient(patientId) {
    return fetch(`/api/patients/${patientId}`, { credentials: 'same-origin' })
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.patient) {
                return normalizePreselectedPatient(data.patient);
            }
            throw new Error('Patient not found');
        });
}

function ensurePreselectedPatient(patientId) {
    if (!patientId) return Promise.resolve(null);
    if (preselectedPatient && String(preselectedPatient.id) === String(patientId) && preselectedPatient.full_name) {
        return Promise.resolve(preselectedPatient);
    }
    return fetchPreselectedPatient(patientId).then(patient => {
        preselectedPatient = patient;
        return patient;
    });
}

function clearCalendar() {
    const container = document.getElementById('bookingsCalendarContainer');
    container.innerHTML = `
        <div class="text-center p-5">
            <i class="bi bi-calendar3 text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3 arabic-text">اضغط على "حجز جديد" لبدء إنشاء موعد</p>
        </div>
    `;
    
    // Reset statistics
    updateStatistics([]);
}

function openAddBookingModal(preselectedTime = null) {
    // Set preselected time
    if (preselectedTime) {
        document.getElementById('bookingTime').value = preselectedTime;
    }
    
    // Set date
    const dateToUse = currentDate.toISOString().split('T')[0];
    document.getElementById('bookingDate').value = dateToUse;
    
    const urlParams = new URLSearchParams(window.location.search);
    const patientId = urlParams.get('patient_id') || (preselectedPatient ? preselectedPatient.id : null);
    
    // Clear form
    document.getElementById('addBookingForm').reset();
    
    // Re-set values after reset
    document.getElementById('bookingDate').value = dateToUse;
    if (preselectedTime) {
        document.getElementById('bookingTime').value = preselectedTime;
    }
    
    if (patientId) {
        if (preselectedPatient && String(preselectedPatient.id) === String(patientId) && preselectedPatient.full_name) {
            applyPreselectedPatientUI(preselectedPatient);
        } else {
            applyPreselectedPatientUI({ id: patientId });
            ensurePreselectedPatient(patientId)
                .then(patient => applyPreselectedPatientUI(patient))
                .catch(() => applyPreselectedPatientUI({ id: patientId }));
        }
    } else {
        clearPreselectedPatientUI();
    }

    if (patientId) {
        document.getElementById('visitType').value = 'New';
        updateVisitCost();
    }
    
    // Load available time slots
    loadAvailableTimeSlots(preselectedTime);
    
    // Update visit cost and payment limits
    updateVisitCost();
    syncFieldMenuSelect('visitType');
    syncFieldMenuSelect('bookingSource');
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addBookingModal'));
    modal.show();
}

function quickAddBooking(time) {
    const selectedDate = currentDate.toISOString().split('T')[0];
    
    if (isDateInPast(selectedDate)) {
        showNotification('لا يمكن حجز موعد في تاريخ سابق', 'warning');
        return;
    }
    
    openAddBookingModal(time);
}

function loadAvailableTimeSlots(preselectedTime = null) {
    const date = document.getElementById('bookingDate').value;
    if (!date) return;
    
    fetch(`/secretary/bookings/calendar?date=${date}`)
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
    const timeSelect = document.getElementById('bookingTime');
    timeSelect.innerHTML = '<option value="">اختر الوقت...</option>';
    
    availableSlots.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = formatTime(time);
        timeSelect.appendChild(option);
    });
    
    if (preselectedTime) {
        timeSelect.value = preselectedTime;
    }
}

function updateVisitCost() {
    const visitTypeSelect = document.getElementById('visitType');
    const visitType = visitTypeSelect.value;
    const costField = document.getElementById('visitCost');
    const paymentAmountField = document.getElementById('paymentAmount');
    const maxPaymentInfo = document.querySelector('.max-payment-info');
    
    // Get cost from selected option's data-cost attribute
    const selectedOption = visitTypeSelect.querySelector(`option[value="${visitType}"]`);
    const cost = selectedOption ? parseFloat(selectedOption.getAttribute('data-cost')) : 0;
    
    if (visitType && cost > 0) {
        costField.value = cost;
        
        // Update payment amount max attribute
        paymentAmountField.setAttribute('max', cost);
        
        // Update max payment info text
        maxPaymentInfo.textContent = `الحد الأقصى المسموح: ${cost} جنيه (تكلفة الزيارة)`;
    } else {
        costField.value = '';
        paymentAmountField.removeAttribute('max');
        maxPaymentInfo.textContent = 'الحد الأقصى المسموح: تكلفة الزيارة نفسها';
    }
    
    // Update payment amount validation
    validatePaymentAmount();
}

function validatePaymentAmount() {
    const paymentAmount = document.getElementById('paymentAmount');
    const visitTypeSelect = document.getElementById('visitType');
    const visitType = visitTypeSelect.value;
    
    const amount = parseFloat(paymentAmount.value) || 0;
    
    // Get cost from selected option's data-cost attribute
    const selectedOption = visitTypeSelect.querySelector(`option[value="${visitType}"]`);
    const cost = selectedOption ? parseFloat(selectedOption.getAttribute('data-cost')) : 0;
    
    // Clear previous validation
    paymentAmount.classList.remove('is-invalid');
    const existingError = document.querySelector('.payment-validation-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Check for negative amounts
    if (amount < 0) {
        paymentAmount.classList.add('is-invalid');
        showPaymentError('المبلغ لا يمكن أن يكون سالباً');
        return;
    }
    
    if (amount > 0) {
        if (amount > cost) {
            paymentAmount.classList.add('is-invalid');
            showPaymentError(`المبلغ لا يمكن أن يتجاوز تكلفة الزيارة (${cost} جنيه)`);
        }
    }
    
    // Update max payment info if cost is available
    if (cost > 0) {
        const maxPaymentInfo = document.querySelector('.max-payment-info');
        if (maxPaymentInfo) {
            maxPaymentInfo.textContent = `الحد الأقصى المسموح: ${cost} جنيه (تكلفة الزيارة)`;
        }
    }
}

function showPaymentError(message) {
    const paymentAmount = document.getElementById('paymentAmount');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'payment-validation-error';
    errorDiv.textContent = message;
    paymentAmount.parentNode.appendChild(errorDiv);
}

async function handleAddBooking(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const bookingData = Object.fromEntries(formData);
    
    // Validation
    if (!bookingData.patient_id) {
        showNotification('يرجى اختيار المريض', 'warning');
        return;
    }
    
    if (!bookingData.doctor_id) {
        showNotification('يرجى اختيار الطبيب', 'warning');
        return;
    }
    
    if (!bookingData.date) {
        showNotification('يرجى اختيار التاريخ', 'warning');
        return;
    }
    
    if (!bookingData.start_time) {
        showNotification('يرجى اختيار الوقت', 'warning');
        return;
    }
    
    if (!bookingData.visit_type) {
        showNotification('يرجى اختيار نوع الزيارة', 'warning');
        return;
    }

    if (!bookingData.clinic_id) {
        showNotification('يرجى اختيار العيادة', 'warning');
        return;
    }

    // Get visit cost from selected option's data-cost attribute
    const visitTypeSelect = document.getElementById('visitType');
    const selectedOption = visitTypeSelect.querySelector(`option[value="${bookingData.visit_type}"]`);
    const visitCost = selectedOption ? parseFloat(selectedOption.getAttribute('data-cost')) : 0;
    
    // Validate payment amount
    const paymentAmount = parseFloat(bookingData.payment_amount) || 0;
    
    if (paymentAmount < 0) {
        showNotification('المبلغ لا يمكن أن يكون سالباً', 'warning');
        return;
    }
    
    if (paymentAmount > visitCost) {
        showNotification(`المبلغ لا يمكن أن يتجاوز تكلفة الزيارة (${visitCost} جنيه)`, 'warning');
        return;
    }
    
    // Add visit cost to booking data
    bookingData.visit_cost = visitCost;
    
    // Check if date is today - if so, check for existing appointments
    const today = new Date().toISOString().split('T')[0];
    if (bookingData.date === today && bookingData.patient_id) {
        try {
            const checkResponse = await fetch(`/api/patients/${bookingData.patient_id}/appointments/check-active`);
            const checkData = await checkResponse.json();
            
            if (checkData.has_active) {
                showNotification('هذا المريض لديه موعد محجوز اليوم بالفعل. يرجى إكمال أو إلغاء الموعد الموجود أولاً.', 'danger');
                return;
            }
        } catch (error) {
            console.error('Error checking active appointments:', error);
            // Continue with booking creation if check fails
        }
    }
    
    // Save booking
    fetch('/secretary/bookings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
            disposeAllBookingsTooltips();

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addBookingModal')).hide();
            
            // Dispatch custom event to update carousel
            window.dispatchEvent(new CustomEvent('appointmentAdded'));
            
            // Show success message
            showNotification('تم إنشاء الحجز بنجاح!', 'success');
            
            // Refresh calendar immediately and then again after a short delay
            loadCalendar();
            setTimeout(() => {
                loadCalendar();
            }, 1000);
        } else {
            const errorMessage = data.error || 'حدث خطأ غير معروف';
            showNotification('خطأ: ' + errorMessage, 'danger');
        }
    })
    .catch(error => {
        console.error('Error saving booking:', error);
        showNotification('خطأ في حفظ الحجز: ' + error.message, 'danger');
    });
}

let currentDeleteBookingId = null;

function deleteBooking(bookingId, patientName, appointmentTime, doctorName, visitType, totalPaid, remainingAmount, notes) {
    // Store booking ID for later use
    currentDeleteBookingId = bookingId;
    
    // Populate modal with booking details
    document.getElementById('deleteBookingPatientName').textContent = patientName;
    document.getElementById('deleteBookingTime').textContent = appointmentTime;
    document.getElementById('deleteBookingDoctor').textContent = doctorName;
    document.getElementById('deleteBookingVisitType').textContent = getVisitTypeInArabic(visitType);
    document.getElementById('deleteBookingPaid').textContent = totalPaid + ' جنيه';
    document.getElementById('deleteBookingRemaining').textContent = remainingAmount + ' جنيه';
    
    // Show notes if available
    if (notes && notes.trim() !== '') {
        document.getElementById('deleteBookingNotes').textContent = notes;
        document.getElementById('deleteBookingNotesRow').style.display = 'block';
    } else {
        document.getElementById('deleteBookingNotesRow').style.display = 'none';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteBookingModal'));
    modal.show();
}

function confirmDeleteBooking() {
    if (!currentDeleteBookingId) return;
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmDeleteBookingBtn');
    const btnText = confirmBtn.querySelector('.btn-text');
    const spinner = confirmBtn.querySelector('.spinner-border');
    
    btnText.classList.add('d-none');
    spinner.classList.remove('d-none');
    confirmBtn.disabled = true;
    
    fetch(`/secretary/bookings/${currentDeleteBookingId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification('تم حذف الحجز بنجاح!', 'success');
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('deleteBookingModal')).hide();
            // Refresh calendar immediately
            loadCalendar();
        } else {
            showNotification('خطأ في حذف الحجز: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        showNotification('خطأ في حذف الحجز', 'danger');
    })
    .finally(() => {
        // Reset button state
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        currentDeleteBookingId = null;
    });
}

let currentConfirmAttendanceId = null;

function confirmAttendance(bookingId, patientName, appointmentTime, doctorName, visitType, totalPaid, remainingAmount) {
    // Store booking ID for later use
    currentConfirmAttendanceId = bookingId;
    
    // Populate modal with booking details
    document.getElementById('confirmAttendancePatientName').textContent = patientName;
    document.getElementById('confirmAttendanceTime').textContent = appointmentTime;
    document.getElementById('confirmAttendanceDoctor').textContent = doctorName;
    document.getElementById('confirmAttendanceVisitType').textContent = getVisitTypeInArabic(visitType);
    document.getElementById('confirmAttendancePaid').textContent = totalPaid + ' جنيه';
    document.getElementById('confirmAttendanceRemaining').textContent = remainingAmount + ' جنيه';
    
    // Show/hide payment section based on remaining amount
    const remainingPaymentSection = document.getElementById('remainingPaymentSection');
    const remainingAmountInput = document.getElementById('remainingAmount');
    const receivedAmountInput = document.getElementById('receivedAmount');
    
    if (remainingAmount > 0) {
        remainingPaymentSection.style.display = 'block';
        remainingAmountInput.value = remainingAmount;
        receivedAmountInput.value = remainingAmount;
        receivedAmountInput.max = remainingAmount;
    } else {
        remainingPaymentSection.style.display = 'none';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('confirmAttendanceModal'));
    modal.show();
}

function confirmAttendanceAction() {
    if (!currentConfirmAttendanceId) return;
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmAttendanceBtn');
    const btnText = confirmBtn.querySelector('.btn-text');
    const spinner = confirmBtn.querySelector('.spinner-border');
    
    btnText.classList.add('d-none');
    spinner.classList.remove('d-none');
    confirmBtn.disabled = true;
    
    // Check if there's remaining payment
    const remainingAmount = parseFloat(document.getElementById('remainingAmount').value) || 0;
    const receivedAmount = parseFloat(document.getElementById('receivedAmount').value) || 0;
    const paymentMethod = document.getElementById('paymentMethod').value;
    const paymentNotes = document.getElementById('paymentNotes').value;
    
    // Validate payment data
    if (receivedAmount < 0) {
        showNotification('المبلغ المستلم لا يمكن أن يكون سالباً', 'warning');
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        return;
    }
    
    if (remainingAmount > 0) {
        if (!receivedAmount || receivedAmount <= 0) {
            showNotification('يرجى إدخال المبلغ المستلم', 'warning');
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            confirmBtn.disabled = false;
            return;
        }
        
        if (receivedAmount < remainingAmount) {
            showNotification(`المبلغ المستلم (${receivedAmount} جنيه) يجب أن يكون مساوياً للمبلغ المتبقي (${remainingAmount} جنيه)`, 'warning');
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            confirmBtn.disabled = false;
            return;
        }
        
        if (receivedAmount > remainingAmount) {
            showNotification(`المبلغ المستلم (${receivedAmount} جنيه) أكبر من المبلغ المتبقي (${remainingAmount} جنيه)`, 'warning');
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            confirmBtn.disabled = false;
            return;
        }
    }
    
    const data = {
        booking_id: currentConfirmAttendanceId,
        remaining_amount: remainingAmount,
        received_amount: receivedAmount,
        payment_method: paymentMethod,
        payment_notes: paymentNotes
    };
    
    fetch(`/secretary/bookings/${currentConfirmAttendanceId}/confirm`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.ok) {
            showNotification('تم تأكيد الحضور بنجاح!', 'success');
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('confirmAttendanceModal')).hide();
            // Refresh calendar immediately
            loadCalendar();
            // Update financial dashboard cards if on payments page
            if (typeof updateDashboardCards === 'function') {
                updateDashboardCards();
            } else {
                // If updateDashboardCards is not available, try to update via API
                updateFinancialCards();
            }
        } else {
            showNotification('خطأ في تأكيد الحضور: ' + (data.error || 'خطأ غير معروف'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error confirming attendance:', error);
        
        // Check if response is HTML (error page)
        if (error.message.includes('Unexpected token')) {
            showNotification('خطأ في الخادم: يرجى المحاولة مرة أخرى', 'danger');
        } else {
            showNotification('خطأ في تأكيد الحضور: ' + error.message, 'danger');
        }
    })
    .finally(() => {
        // Reset button state
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        confirmBtn.disabled = false;
        currentConfirmAttendanceId = null;
    });
}

let currentEditBookingId = null;

function editBooking(bookingId) {
    currentEditBookingId = bookingId;
    
    // Fetch booking details
    fetch(`/secretary/bookings/${bookingId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateEditForm(data.booking);
                const modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
                modal.show();
            } else {
                showNotification('خطأ في تحميل تفاصيل الحجز: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            showNotification('خطأ في تحميل تفاصيل الحجز', 'danger');
        });
}

function populateEditForm(booking) {
    // Set booking ID
    document.getElementById('editBookingId').value = booking.id;
    
    // Set patient info
    document.getElementById('editSelectedPatientId').value = booking.patient_id;
    document.getElementById('editSelectedPatientInfo').innerHTML = `
        <div class="alert alert-info">
            <strong>المريض المحدد:</strong> ${booking.patient_name} - ${booking.patient_phone}
        </div>
    `;
    document.getElementById('editSelectedPatientInfo').style.display = 'block';
    
    // Set doctor
    document.getElementById('editDoctor').value = booking.doctor_id;
    
    // Set date and time
    document.getElementById('editBookingDate').value = booking.date;
    
    // Set visit type
    document.getElementById('editVisitType').value = booking.visit_type;
    updateEditVisitCost();
    
    // Set payment info
    document.getElementById('editTotalPaid').value = booking.total_paid || 0;
    updateEditPaymentInfo();
    
    // Set notes
    document.getElementById('editNotes').value = booking.notes || '';
    
    // Load available time slots for the selected date and select current time
    loadEditAvailableTimeSlots(booking.date, booking.doctor_id, booking.id, booking.start_time);

    applyEditLockState(booking);
}

/* Mirror the backend FULL-LOCK rules in the UI so the secretary sees why
   a field can't change instead of getting a 422 after submitting:
   - money_locked (a payment exists): visit_type + patient are frozen.
   - day_closed (booking's financial day is closed): the whole edit is
     blocked for the secretary. */
function applyEditLockState(booking) {
    const visitTypeEl = document.getElementById('editVisitType');
    const patientSearchEl = document.getElementById('editPatientSearch');
    const saveBtn = document.getElementById('saveEditBookingBtn');
    const hintId = 'editLockHint';
    let hint = document.getElementById(hintId);
    if (!hint) {
        hint = document.createElement('div');
        hint.id = hintId;
        hint.className = 'alert alert-warning py-2 px-3 mb-3 arabic-text';
        hint.style.fontSize = '0.85rem';
        const form = document.getElementById('editBookingForm');
        if (form) form.prepend(hint);
    }

    const moneyLocked = !!booking.money_locked;
    const dayClosed = !!booking.day_closed;

    // Reset to editable first
    [visitTypeEl, patientSearchEl].forEach(el => { if (el) { el.disabled = false; el.classList.remove('bg-light'); } });
    if (saveBtn) { saveBtn.disabled = false; }
    hint.style.display = 'none';
    hint.innerHTML = '';

    if (dayClosed) {
        // Hard stop — the secretary cannot touch a closed-day booking.
        document.querySelectorAll('#editBookingForm input, #editBookingForm select, #editBookingForm textarea')
            .forEach(el => { el.disabled = true; });
        if (saveBtn) saveBtn.disabled = true;
        hint.style.display = 'block';
        hint.innerHTML = '<i class="bi bi-lock-fill me-1"></i> اليوم المالى لهذا الحجز مقفول — لا يمكن للسكرتارية تعديله. راجع الطبيب أو الأدمن.';
        return;
    }

    if (moneyLocked) {
        if (visitTypeEl) { visitTypeEl.disabled = true; visitTypeEl.classList.add('bg-light'); }
        if (patientSearchEl) { patientSearchEl.disabled = true; patientSearchEl.classList.add('bg-light'); }
        hint.style.display = 'block';
        hint.innerHTML = '<i class="bi bi-shield-lock me-1"></i> هذا الحجز عليه دفعة: <strong>نوع الزيارة</strong> و<strong>المريض</strong> مقفولان. للتصحيح ألغِ/استرجع الدفعة من شاشة المدفوعات أولاً.';
    }
}

function updateEditVisitCost() {
    const visitTypeSelect = document.getElementById('editVisitType');
    const visitCostInput = document.getElementById('editVisitCost');
    const selectedOption = visitTypeSelect.options[visitTypeSelect.selectedIndex];
    
    if (selectedOption && selectedOption.dataset.cost) {
        const cost = parseFloat(selectedOption.dataset.cost);
        visitCostInput.value = cost;
        updateEditPaymentInfo();
    } else {
        visitCostInput.value = '';
    }
}

function updateEditPaymentInfo() {
    const visitCost = parseFloat(document.getElementById('editVisitCost').value) || 0;
    const totalPaid = parseFloat(document.getElementById('editTotalPaid').value) || 0;
    const additionalPayment = parseFloat(document.getElementById('editAdditionalPayment').value) || 0;
    
    const newTotalPaid = totalPaid + additionalPayment;
    const remainingAmount = Math.max(0, visitCost - newTotalPaid);
    
    document.getElementById('editRemainingAmount').value = remainingAmount;
}

function loadEditAvailableTimeSlots(date, doctorId, currentBookingId = null, currentTime = null) {
    // Get all time slots first
    const allSlots = getAllTimeSlots();
    
    // Get unavailable slots for the specific doctor and date
    fetch(`/secretary/bookings/calendar?date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Get all appointments for the date to find booked slots
                const appointments = data.data.appointments || [];
                
                // Get unavailable slots for the specific doctor
                const unavailableSlots = [];
                appointments.forEach(appointment => {
                    if (appointment.doctor_id == doctorId && 
                        appointment.status !== 'Cancelled' && 
                        appointment.id != currentBookingId) { // Exclude current booking
                        unavailableSlots.push(appointment.start_time.substring(0, 5)); // Remove seconds
                    }
                });
                
                // Filter out unavailable slots
                const availableSlots = allSlots.filter(slot => !unavailableSlots.includes(slot));
                
                const timeSelect = document.getElementById('editBookingTime');
                timeSelect.innerHTML = '<option value="">اختر الوقت...</option>';
                
                availableSlots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = formatTime(slot);
                    timeSelect.appendChild(option);
                });
                
                // Select current time if provided and available
                if (currentTime) {
                    const timeToSelect = currentTime.substring(0, 5); // Remove seconds
                    timeSelect.value = timeToSelect;
                }
            }
        })
        .catch(error => {
            console.error('Error loading available time slots:', error);
            // Fallback to all slots if API fails
            const timeSelect = document.getElementById('editBookingTime');
            timeSelect.innerHTML = '<option value="">اختر الوقت...</option>';
            
            allSlots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = formatTime(slot);
                timeSelect.appendChild(option);
            });
            
            // Select current time if provided
            if (currentTime) {
                const timeToSelect = currentTime.substring(0, 5); // Remove seconds
                timeSelect.value = timeToSelect;
            }
        });
}

function editSearchPatients() {
    const query = document.getElementById('editPatientSearch').value.trim();
    if (query.length < 2) {
        document.getElementById('editPatientSearchResults').style.display = 'none';
        return;
    }
    
    fetch((window.DigitNormalizer && window.DigitNormalizer.patientSearchUrl)
        ? window.DigitNormalizer.patientSearchUrl(query)
        : `/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayEditPatientSearchResults(data.patients);
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
        });
}

function displayEditPatientSearchResults(patients) {
    const resultsDiv = document.getElementById('editPatientSearchResults');
    
    if (patients.length === 0) {
        resultsDiv.innerHTML = '<div class="list-group-item text-muted">لا توجد نتائج</div>';
    } else {
        resultsDiv.innerHTML = patients.map(patient => `
            <div class="list-group-item list-group-item-action" onclick="selectEditPatient(${patient.id}, '${patient.first_name} ${patient.last_name}', '${patient.phone}')">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 arabic-text">${patient.first_name} ${patient.last_name}</h6>
                    <small>${patient.phone}</small>
                </div>
                <small class="text-muted">${patient.dob || 'تاريخ الميلاد غير محدد'}</small>
            </div>
        `).join('');
    }
    
    resultsDiv.style.display = 'block';
}

function selectEditPatient(patientId, patientName, patientPhone) {
    document.getElementById('editSelectedPatientId').value = patientId;
    document.getElementById('editSelectedPatientInfo').innerHTML = `
        <div class="alert alert-info">
            <strong>المريض المحدد:</strong> ${patientName} - ${patientPhone}
        </div>
    `;
    document.getElementById('editSelectedPatientInfo').style.display = 'block';
    document.getElementById('editPatientSearchResults').style.display = 'none';
    document.getElementById('editPatientSearch').value = '';
}

function saveEditBooking() {
    if (!currentEditBookingId) return;
    
    // Show loading state
    const saveBtn = document.getElementById('saveEditBookingBtn');
    const btnText = saveBtn.querySelector('.btn-text');
    const spinner = saveBtn.querySelector('.spinner-border');
    
    btnText.classList.add('d-none');
    spinner.classList.remove('d-none');
    saveBtn.disabled = true;
    
    // Collect form data
    const formData = {
        booking_id: currentEditBookingId,
        patient_id: document.getElementById('editSelectedPatientId').value,
        doctor_id: document.getElementById('editDoctor').value,
        date: document.getElementById('editBookingDate').value,
        start_time: document.getElementById('editBookingTime').value,
        visit_type: document.getElementById('editVisitType').value,
        notes: document.getElementById('editNotes').value,
        additional_payment: parseFloat(document.getElementById('editAdditionalPayment').value) || 0,
        payment_method: document.getElementById('editPaymentMethod').value
    };
    
    // Validate required fields
    if (!formData.patient_id || !formData.doctor_id || !formData.date || !formData.start_time || !formData.visit_type) {
        showNotification('يرجى ملء جميع الحقول المطلوبة', 'warning');
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        saveBtn.disabled = false;
        return;
    }
    
    // Validate additional payment amount
    if (formData.additional_payment < 0) {
        showNotification('المبلغ الإضافي لا يمكن أن يكون سالباً', 'warning');
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        saveBtn.disabled = false;
        return;
    }
    
    fetch(`/secretary/bookings/${currentEditBookingId}/update`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showNotification('تم تحديث الحجز بنجاح!', 'success');
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editBookingModal')).hide();
            // Refresh calendar immediately
            loadCalendar();
        } else {
            showNotification('خطأ في تحديث الحجز: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        showNotification('خطأ في تحديث الحجز', 'danger');
    })
    .finally(() => {
        // Reset button state
        btnText.classList.remove('d-none');
        spinner.classList.add('d-none');
        saveBtn.disabled = false;
        currentEditBookingId = null;
    });
}

function viewAppointmentDetails(appointmentId) {
    if (typeof window.navigateToSecretaryBooking === 'function') {
        window.navigateToSecretaryBooking(appointmentId);
        return;
    }
    window.location.href = `/secretary/bookings/${appointmentId}`;
}

function searchPatients() {
    const query = document.getElementById('patientSearch').value.trim();
    
    if (query.length < 2) {
        document.getElementById('patientSearchResults').innerHTML = '';
        return;
    }
    
    fetch((window.DigitNormalizer && window.DigitNormalizer.patientSearchUrl)
        ? window.DigitNormalizer.patientSearchUrl(query)
        : `/api/patients/search?q=${encodeURIComponent(query)}`)
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
        resultsContainer.innerHTML = '<div class="search-result-item text-muted arabic-text">لم يتم العثور على مرضى</div>';
        return;
    }
    
    let html = '';
    patients.forEach(patient => {
        html += `
            <div class="search-result-item" onclick="selectPatient(${patient.id}, '${patient.first_name} ${patient.last_name}')">
                <div class="patient-name arabic-text">${patient.first_name} ${patient.last_name}</div>
                <div class="patient-details arabic-text">${patient.phone} • العمر: ${patient.age || 'غير محدد'}</div>
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

function updateStatistics(appointments = []) {
    const totalBookings = appointments.length;
    const completedBookings = appointments.filter(function (apt) { return apt.status === 'Completed'; }).length;
    const pendingBookings = appointments.filter(function (apt) { return apt.status === 'Booked'; }).length;
    const checkedInBookings = appointments.filter(function (apt) { return apt.status === 'CheckedIn'; }).length;

    const stats = {
        total_appointments: totalBookings,
        booked: pendingBookings,
        checked_in: checkedInBookings,
        completed: completedBookings
    };

    if (window.secMiniStats && window.SEC_BOOKINGS_MINI_CARDS) {
        window.secMiniStats.updateStatValues(window.SEC_BOOKINGS_MINI_CARDS, stats);
        const trends = window.secMiniStats.readJsonScript('secBookingsTrends') || {};
        const dates = window.secMiniStats.readJsonScript('secBookingsTrendDates') || [];
        const y = currentDate.getFullYear();
        const m = String(currentDate.getMonth() + 1).padStart(2, '0');
        const d = String(currentDate.getDate()).padStart(2, '0');
        const dateStr = y + '-' + m + '-' + d;
        const today = new Date();
        const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        const deltas = window.secMiniStats.computeDeltasForDate(dates, trends, dateStr, stats, window.SEC_BOOKINGS_MINI_CARDS);
        window.secMiniStats.refresh(window.SEC_BOOKINGS_MINI_CARDS, trends, stats, deltas, {
            trendsId: 'secBookingsTrends',
            statsId: 'secBookingsStatsInitial',
            deltasId: 'secBookingsTrendDeltas',
            neutralLabel: dateStr === todayStr ? 'اليوم' : 'ذلك اليوم',
            syncLastPoint: false
        });
    } else {
        var el;
        el = document.getElementById('totalBookings'); if (el) el.textContent = totalBookings;
        el = document.getElementById('completedBookings'); if (el) el.textContent = completedBookings;
        el = document.getElementById('pendingBookings'); if (el) el.textContent = pendingBookings;
        el = document.getElementById('checkedInBookings'); if (el) el.textContent = checkedInBookings;
    }
}

function updateDateDisplay() {
    const display = document.getElementById('currentDateDisplay');
    const dateStr = currentDate.toISOString().split('T')[0];
    const displayDate = new Date(dateStr + 'T12:00:00');
    const formattedDate = displayDate.toLocaleDateString('ar-EG', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    display.textContent = formattedDate;
}

function updateLastUpdate() {
    const lastUpdate = document.getElementById('lastUpdate');
    const timeString = new Date().toLocaleTimeString('ar-EG');
    lastUpdate.textContent = `آخر تحديث: ${timeString}`;
}

// Auto-refresh state management
function getAutoRefreshState() {
    const saved = localStorage.getItem('bookingsAutoRefresh');
    return saved === null ? true : saved === 'true'; // Default is ON
}

function saveAutoRefreshState(enabled) {
    localStorage.setItem('bookingsAutoRefresh', enabled ? 'true' : 'false');
}

function toggleBookingsAutoRefresh(enabled) {
    saveAutoRefreshState(enabled);
    
    if (enabled) {
        if (!refreshInterval) {
            startAutoRefresh();
        }
    } else {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }
}

// Live calendar sync: poll the shared change-cursor (~5s) and refetch only when
// something actually changed — including changes made by the doctor — because
// /api/calendar/version is computed server-side over the shared rows for the date.
// Paused while any modal is open or the tab is hidden.
let lastCalendarVersion = null;
let lastSyncDate = null;

function checkCalendarVersion() {
    if (document.hidden) return;
    if (document.querySelector('.modal.show') !== null) return;
    const dateStr = currentDate.toISOString().split('T')[0];
    fetch(`/api/calendar/version?date=${dateStr}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        if (!d || !d.ok) return;
        if (dateStr !== lastSyncDate || lastCalendarVersion === null) {
            lastSyncDate = dateStr;
            lastCalendarVersion = d.version;
            return;
        }
        if (d.version !== lastCalendarVersion) {
            lastCalendarVersion = d.version;
            refreshCalendarData(); // remote change -> refetch + re-render
        }
    })
    .catch(() => {});
}

function startAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    refreshInterval = setInterval(checkCalendarVersion, 5000); // ~5s near-real-time
}

// Function to refresh calendar data via AJAX
function refreshCalendarData() {
    const dateStr = currentDate.toISOString().split('T')[0];
    
    // Show subtle loading indicator
    const calendarContainer = document.getElementById('bookingsCalendarContainer');
    if (calendarContainer) {
        calendarContainer.parentElement.classList.add('table-loading', 'loading');
    }
    
    fetch(`/secretary/bookings/calendar?date=${dateStr}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            calendarData = data.data;
            renderCalendar(data.data);
            updateDateDisplay();
            updateLastUpdate();
            updateStatistics(data.data.appointments || []);
            updateStatusIndicator();
            setTimeout(() => {
                initializeTooltips();
                initializeAppointmentProgressBars();
            }, 100);
        }
    })
    .catch(error => {
        console.error('Error refreshing calendar:', error);
    })
    .finally(() => {
        // Remove loading indicator
        const calendarContainer = document.getElementById('bookingsCalendarContainer');
        if (calendarContainer) {
            calendarContainer.parentElement.classList.remove('table-loading', 'loading');
        }
    });
}

function updateStatusIndicator() {
    const indicator = document.getElementById('statusIndicator');
    if (indicator) {
        indicator.innerHTML = '<i class="bi bi-circle-fill me-1"></i> مباشر';
        indicator.className = 'badge bg-success me-2 status-indicator';
        
        // Add pulse animation
        indicator.style.animation = 'pulseOnce 0.6s ease';
        setTimeout(() => {
            indicator.style.animation = '';
        }, 600);
    }
}

function getStatusBadgeClass(status) {
    const classes = {
        'Booked': 'bg-primary',
        'CheckedIn': 'bg-success',
        'InProgress': 'bg-warning',
        'Completed': 'bg-info',
        'Cancelled': 'bg-danger',
        'NoShow': 'bg-secondary',
        'Rescheduled': 'bg-info'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusDisplayText(status) {
    const statusTexts = {
        'Booked': 'محجوز',
        'CheckedIn': 'تم الحضور',
        'InProgress': 'قيد التنفيذ',
        'Completed': 'مكتمل',
        'Cancelled': 'ملغي',
        'NoShow': 'لم يحضر',
        'Rescheduled': 'مؤجل'
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

function formatTime(time) {
    if (!time) return '';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('ar-EG', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function getVisitTypeInArabic(visitType) {
    const visitTypes = {
        'New': 'زيارة جديدة',
        'FollowUp': 'إعادة زيارة',
        'Consultation': 'استشارة طبية'
    };
    return visitTypes[visitType] || visitType;
}

function getAllTimeSlots() {
    const slots = [];
    const startHour = 14; // 2 PM
    const endHour = 23;   // 11 PM
    
    for (let hour = startHour; hour <= endHour; hour++) {
        for (let minute = 0; minute < 60; minute += 15) {
            if (hour === endHour && minute > 0) break; // Stop at 11:00 PM
            const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            slots.push(timeString);
        }
    }
    
    return slots;
}

function isDateInPast(dateString) {
    return dateString < SERVER_DATE;
}

function showNotification(message, type = 'info') {
    // Ensure Bootstrap is loaded
    if (typeof bootstrap === 'undefined' || typeof bootstrap.Toast === 'undefined') {
        // Fallback to alert if Bootstrap Toast is not available
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
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Create toast HTML
    const toastHtml = `
        <div id="${toastId}" class="toast ${toastClass} align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000" style="min-width: 350px; max-width: 500px; pointer-events: auto;">
            <div class="d-flex align-items-center">
                <div class="toast-body flex-grow-1">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-${iconClass} me-2" style="font-size: 1.25rem;"></i>
                        <div class="flex-grow-1 arabic-text">${escapeHtml(message)}</div>
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
            // Fallback to alert if toast fails
            alert(message);
            toastElement.remove();
        }
    }
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

// Add Patient functionality - Age and Date of Birth conversion
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
            showMessage('يرجى ملء جميع الحقول المطلوبة بشكل صحيح.', 'error');
            return;
        }
        
        // Additional validation
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const gender = document.getElementById('gender').value;
        
        if (!firstName || !lastName || !phone) {
            showMessage('الاسم الأول والاسم الأخير ورقم الهاتف مطلوبة.', 'error');
            return;
        }
        
        if (!gender) {
            showMessage('يرجى اختيار جنس المريض.', 'error');
            document.getElementById('gender').focus();
            return;
        }

        const clinicSelect = document.getElementById('patientClinic');
        if (clinicSelect && !clinicSelect.value) {
            showMessage('يرجى اختيار العيادة.', 'error');
            clinicSelect.focus();
            return;
        }

        // Validate phone number format
        const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
        const phoneRegex = /^(\+\d{1,3})?\d{7,15}$/;
        if (!phoneRegex.test(cleanPhone)) {
            showMessage('يرجى إدخال رقم هاتف صحيح (7-15 رقم، مع إمكانية إضافة رمز الدولة).', 'error');
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
                showMessage('تم إضافة المريض بنجاح!', 'success');
                
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
                        const appointmentModal = new bootstrap.Modal(document.getElementById('addBookingModal'));
                        appointmentModal.show();
                        
                        // Auto-select the new patient
                        const patientData = data.data || data.patient || data;
                        
                        if (patientData && (patientData.id || patientData.patient_id)) {
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
                        } else {
                            showNotification('تم إضافة المريض ولكن لا يمكن تحديده تلقائياً. يرجى البحث عن المريض يدوياً.', 'warning');
                        }
                    }, 300);
                }, 1500);
                
            } else {
                // Error from server
                const errorMsg = data.error || data.message || 'فشل في إضافة المريض. يرجى المحاولة مرة أخرى.';
                showMessage(errorMsg, 'error');
                
                // Show validation errors if available
                if (data.details) {
                    showValidationErrors(data.details);
                }
            }
        })
        .catch(error => {
            setSubmitButtonLoading(false);
            showMessage('حدث خطأ أثناء إضافة المريض. يرجى المحاولة مرة أخرى.', 'error');
        });
    }
    
    function selectNewPatient(patientData) {
        const firstName = patientData.first_name || patientData.firstName || '';
        const lastName = patientData.last_name || patientData.lastName || '';
        const fullName = `${firstName} ${lastName}`.trim();
        const patientId = patientData.id || patientData.patient_id;
        const phone = patientData.phone || patientData.phone_number || '';
        const age = patientData.age || calculateAgeFromDOB(patientData.dob) || 'غير محدد';
        
        // Fill patient search field
        document.getElementById('patientSearch').value = fullName;
        document.getElementById('selectedPatientId').value = patientId;
        
        // Show patient info
        document.getElementById('patientSearchResults').innerHTML = `
            <div class="selected-patient-info alert alert-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>تم إضافة مريض جديد:</strong> ${fullName}<br>
                        <small>الهاتف: ${phone} • العمر: ${age}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearPreselectedPatient()">
                        تغيير المريض
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
            btnText.textContent = 'جاري الإضافة...';
            spinner.classList.remove('d-none');
        } else {
            addPatientSubmit.disabled = false;
            btnText.textContent = 'إضافة المريض';
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
                ageInput.placeholder = `العمر المحسوب: ${age} سنة`;
                setTimeout(() => {
                    ageInput.placeholder = 'أدخل العمر بالسنوات';
                }, 3000);
            }
        }
    });
}

// Update financial dashboard cards
function updateFinancialCards() {
    fetch('/api/dashboard-summary', {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.data.dailyBalance) {
                // Update cards if they exist on the page
                const openingBalanceEl = document.getElementById('openingBalance');
                const totalReceivedEl = document.getElementById('totalReceived');
                const totalExpensesEl = document.getElementById('totalExpenses');
                const currentBalanceEl = document.getElementById('currentBalance');
                
                if (openingBalanceEl) {
                    openingBalanceEl.textContent = formatMoney(data.data.dailyBalance.opening_balance) + ' جنيه';
                }
                if (totalReceivedEl) {
                    totalReceivedEl.textContent = formatMoney(data.data.dailyBalance.total_received) + ' جنيه';
                }
                if (totalExpensesEl) {
                    totalExpensesEl.textContent = formatMoney(data.data.dailyBalance.total_expenses) + ' جنيه';
                }
                if (currentBalanceEl) {
                    currentBalanceEl.textContent = formatMoney(data.data.dailyBalance.current_balance) + ' جنيه';
                }
            }
        })
        .catch(error => {
            console.error('Error updating financial cards:', error);
        });
}

// Format money function
function formatMoney(amount) {
    return new Intl.NumberFormat('ar-EG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

// Initialize add patient modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeAddPatientModal();

    // Clinic dropdowns are now server-rendered above (with the secretary's
    // own clinic, disabled when only one option is available). No JS populate
    // is needed any more — the modal opens with the dropdown ready.

    const urlParams = new URLSearchParams(window.location.search);
    const openModal = urlParams.get('openModal');
    const patientId = urlParams.get('patient_id');

    const applyPatientPreselection = () => {
        if (preselectedPatient) {
            applyPreselectedPatientUI(preselectedPatient);
        } else if (patientId) {
            applyPreselectedPatientUI({ id: patientId });
            ensurePreselectedPatient(patientId)
                .then(p => applyPreselectedPatientUI(p))
                .catch(() => applyPreselectedPatientUI({ id: patientId }));
        }
        if (patientId) {
            document.getElementById('visitType').value = 'New';
            updateVisitCost();
        }
    };

    applyPatientPreselection();

    if (openModal === 'addBooking') {
        setTimeout(() => openAddBookingModal(), 100);
    }
});

function clearPreselectedPatient() {
    clearPreselectedPatientUI();
}


/* Secretary bookings calendar helpers (filters, tooltips, progress, navigation) */

function escAttr(str) {
    return String(str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function normalizeStartTime(t) {
    if (t == null || t === '') return '';
    return String(t).substring(0, 5);
}

function findAppointmentsAtSlot(data, slotTime) {
    if (!data || !Array.isArray(data.appointments)) return [];
    const key = normalizeStartTime(slotTime);
    return data.appointments.filter((apt) => normalizeStartTime(apt.start_time) === key);
}

function getClinicsCatalog() {
    if (CFG.clinics && CFG.clinics.length) return CFG.clinics;
    if (Array.isArray(window.CLINICS_BOOTSTRAP) && window.CLINICS_BOOTSTRAP.length) {
        return window.CLINICS_BOOTSTRAP;
    }
    return [];
}

function resolveClinicMeta(appointment) {
    if (!appointment) return { id: null, code: null, name: '' };
    let id = appointment.clinic_id != null ? appointment.clinic_id : null;
    let code = appointment.clinic_code || null;
    let nameAr = appointment.clinic_name_ar || '';
    let nameEn = appointment.clinic_name_en || '';
    if ((!nameAr && !nameEn) && id != null) {
        const hit = getClinicsCatalog().find((c) => String(c.id) === String(id));
        if (hit) {
            code = code || hit.code;
            nameAr = nameAr || hit.name_ar || '';
            nameEn = nameEn || hit.name_en || '';
        }
    }
    const name = nameAr || nameEn || (code ? String(code).toUpperCase() : '');
    return { id, code, nameAr, nameEn, name };
}

function getClinicVisual(code) {
    if (window.ClinicsLoader && typeof window.ClinicsLoader.getVisual === 'function') {
        return window.ClinicsLoader.getVisual(code);
    }
    const map = {
        riyadh: { icon: 'bi-buildings-fill', color: '#0d6efd' },
        kfs: { icon: 'bi-hospital-fill', color: '#10b981' }
    };
    return map[code] || { icon: 'bi-building', color: '#6c757d' };
}

function renderClinicChip(appointment, { withName = true } = {}) {
    const meta = resolveClinicMeta(appointment);
    if (!meta.name && meta.id == null) return '';
    const v = getClinicVisual(meta.code);
    return `<span class="clinic-tag" style="--clinic-color:${v.color}" dir="rtl"><i class="bi ${v.icon}"></i>${withName ? ` ${meta.name}` : ''}</span>`;
}

function renderClinicBadge(appointment) {
    const chip = renderClinicChip(appointment);
    if (!chip) return '';
    return `<div class="appointment-clinic-badge" aria-label="العيادة">${chip}</div>`;
}

function timeInRange(time, startTime, endTime) {
    const timeToMinutes = (t) => {
        const [hours, minutes] = t.split(':').map(Number);
        return hours * 60 + minutes;
    };
    return timeToMinutes(time) >= timeToMinutes(startTime) && timeToMinutes(time) <= timeToMinutes(endTime);
}

function shouldDisplayTimeSlot(time, data) {
    if (!currentTimeFilter || currentTimeFilter === 'none') return true;

    const appointment = findAppointmentsAtSlot(data, time)[0] || null;
    const isAvailable = (data.available_slots || []).includes(time);

    if (currentTimeFilter === '2pm-6pm') return timeInRange(time, '14:00', '18:00');
    if (currentTimeFilter === '6pm-1045pm') return timeInRange(time, '18:00', '22:45');
    if (currentTimeFilter === 'available') return isAvailable && !appointment;
    if (currentTimeFilter === 'unavailable') return !isAvailable || !!appointment;
    return true;
}

function applyTimeFilter(filter) {
    currentTimeFilter = filter === 'none' ? null : filter;
    updateFilterButtonStates();
    if (calendarData) {
        renderCalendar(calendarData);
        updateDateDisplay();
        setTimeout(() => initializeTooltips(), 100);
    }
}

function updateFilterButtonStates() {
    document.querySelectorAll('.filter-time-btn').forEach((btn) => {
        btn.classList.remove('active');
        const f = btn.getAttribute('data-filter');
        if (f === currentTimeFilter || (f === 'none' && !currentTimeFilter)) {
            btn.classList.add('active');
        }
    });
}

function goToSelectedDate() {
    const datePicker = document.getElementById('tooltipDatePicker');
    if (!datePicker || !datePicker.value) return;

    const goToDateBtn = document.getElementById('goToDateBtn');
    if (goToDateBtn) {
        const popover = bootstrap.Popover.getInstance(goToDateBtn);
        if (popover) popover.hide();
    }

    window.location.href = `${ROUTES.calendar}?date=${datePicker.value}`;
}

function scrollToHighlightedAppointment() {
    if (!highlightedAppointmentId) return;
    const card = document.querySelector(`[data-appointment-id="${highlightedAppointmentId}"]`);
    if (!card) return;
    card.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
    card.style.animation = 'pulseHighlight 2s ease-in-out 3';
    setTimeout(() => { card.style.animation = ''; }, 6000);
}

function isBookingsDesktopViewport() {
    return window.matchMedia('(min-width: 992px)').matches;
}

function shouldInitBookingsTooltip(el) {
    if (!isBookingsDesktopViewport()) return true;
    return !el.closest('.appointment-actions');
}

function hideBookingsTooltipsInActions() {
    if (!isBookingsDesktopViewport()) return;
    document.querySelectorAll(".appointment-info[data-bs-toggle='tooltip']").forEach((info) => {
        const tip = bootstrap.Tooltip.getInstance(info);
        if (tip) tip.hide();
    });
    purgeOrphanedBookingsTooltipNodes();
}

function setupBookingsActionsTooltipGuard() {
    document.addEventListener('mouseenter', function (e) {
        if (e.target.closest('.appointment-actions')) {
            hideBookingsTooltipsInActions();
        }
    }, true);
}

function setupBookingsTooltipLifecycle() {
    const addBookingModal = document.getElementById('addBookingModal');
    if (addBookingModal) {
        addBookingModal.addEventListener('show.bs.modal', disposeAllBookingsTooltips);
        addBookingModal.addEventListener('hidden.bs.modal', disposeAllBookingsTooltips);
    }

    ['editBookingModal', 'deleteBookingModal', 'confirmAttendanceModal', 'addPatientModal'].forEach((id) => {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', disposeAllBookingsTooltips);
        }
    });

    document.addEventListener('scroll', hideAllBookingsTooltips, true);
    window.addEventListener('resize', hideAllBookingsTooltips);
}

function initializeTooltips() {
    disposeAllBookingsTooltips();

    const tooltipTriggerList = [].slice
        .call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        .filter(shouldInitBookingsTooltip);

    tooltipTriggerList.forEach((tooltipTriggerEl) => {
        const isAppointmentInfo = tooltipTriggerEl.classList.contains('appointment-info');
        new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true,
            trigger: isAppointmentInfo ? 'hover' : 'hover focus',
            delay: { show: 300, hide: 100 },
            container: 'body',
            popperConfig: function (defaultConfig) {
                return {
                    ...defaultConfig,
                    strategy: 'fixed',
                    modifiers: [
                        ...(defaultConfig.modifiers || []),
                        {
                            name: 'flip',
                            options: {
                                fallbackPlacements: ['left', 'top', 'bottom'],
                                rootBoundary: 'viewport',
                                padding: 8
                            }
                        },
                        {
                            name: 'preventOverflow',
                            options: { rootBoundary: 'viewport', altAxis: true, padding: 8 }
                        }
                    ]
                };
            }
        });
    });

    const goToDateBtn = document.getElementById('goToDateBtn');
    if (goToDateBtn) {
        const existingPopover = bootstrap.Popover.getInstance(goToDateBtn);
        if (existingPopover) existingPopover.dispose();

        const popover = new bootstrap.Popover(goToDateBtn, {
            html: true,
            trigger: 'click',
            placement: 'bottom',
            container: 'body',
            sanitize: false
        });

        goToDateBtn.addEventListener('shown.bs.popover', function () {
            setTimeout(() => {
                const datePicker = document.getElementById('tooltipDatePicker');
                if (!datePicker) return;
                datePicker.value = currentDate.toISOString().split('T')[0];
                datePicker.focus();
                datePicker.addEventListener('keydown', function onEnter(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        goToSelectedDate();
                    }
                });
            }, 100);
        });

        document.addEventListener('click', function onOutsideClick(e) {
            const popoverInstance = bootstrap.Popover.getInstance(goToDateBtn);
            if (!popoverInstance || !popoverInstance._isShown()) return;
            const popoverElement = document.querySelector('.popover');
            if (popoverElement && !goToDateBtn.contains(e.target) && !popoverElement.contains(e.target)) {
                popoverInstance.hide();
            }
        });
    }
}

function initializeAppointmentProgressBars() {
    const progressContainers = document.querySelectorAll('.appointment-progress-container');
    progressContainers.forEach((container) => updateAppointmentProgressBar(container));

    if (progressContainers.length > 0) {
        if (window.appointmentProgressInterval) clearInterval(window.appointmentProgressInterval);
        window.appointmentProgressInterval = setInterval(() => {
            document.querySelectorAll('.appointment-progress-container').forEach(updateAppointmentProgressBar);
        }, 1000);
    }
}

function updateAppointmentProgressBar(container) {
    if (!container) return;

    const dateStr = container.getAttribute('data-date');
    const startTimeStr = container.getAttribute('data-start-time');
    const endTimeStr = container.getAttribute('data-end-time');
    if (!dateStr || !startTimeStr || !endTimeStr) return;

    const now = new Date();
    let appointmentDate;
    try {
        appointmentDate = new Date(dateStr);
        if (isNaN(appointmentDate.getTime())) return;
    } catch (e) {
        return;
    }

    const startParts = startTimeStr.split(':');
    const endParts = endTimeStr.split(':');
    const startDateTime = new Date(appointmentDate);
    startDateTime.setHours(parseInt(startParts[0], 10) || 0, parseInt(startParts[1], 10) || 0, parseInt(startParts[2], 10) || 0, 0);
    const endDateTime = new Date(appointmentDate);
    endDateTime.setHours(parseInt(endParts[0], 10) || 0, parseInt(endParts[1], 10) || 0, parseInt(endParts[2], 10) || 0, 0);

    const appointmentDuration = 15 * 60;
    const progressFill = container.querySelector('.glass-progress-fill');
    const progressText = container.querySelector('.glass-progress-text');
    if (!progressFill || !progressText) return;

    const nowTime = now.getTime();
    const startTime = startDateTime.getTime();
    const endTime = endDateTime.getTime();
    let progress = 0;
    let timeText = '00:00';
    let progressType = 'before';
    let prefixText = '';

    if (nowTime < startTime) {
        progressType = 'before';
        prefixText = 'متبقي: ';
        const secondsUntilStart = Math.floor((startTime - nowTime) / 1000);
        const remainingSeconds = Math.max(0, secondsUntilStart);
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        timeText = prefixText + `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        const maxCountdownTime = 2 * 60 * 60;
        const adjustedTotal = Math.min(secondsUntilStart, maxCountdownTime);
        progress = adjustedTotal > 0 ? ((maxCountdownTime - adjustedTotal) / maxCountdownTime) * 100 : 100;
    } else if (nowTime >= startTime && nowTime <= endTime) {
        progressType = 'during';
        prefixText = 'جاري: ';
        const elapsedSeconds = Math.floor((nowTime - startTime) / 1000);
        progress = (elapsedSeconds / appointmentDuration) * 100;
        const minutes = Math.floor(elapsedSeconds / 60);
        const seconds = elapsedSeconds % 60;
        timeText = prefixText + `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    } else {
        progressType = 'overdue';
        prefixText = 'متأخر: ';
        const overdueSeconds = Math.floor((nowTime - endTime) / 1000);
        progress = 100;
        const minutes = Math.floor(overdueSeconds / 60);
        const seconds = overdueSeconds % 60;
        timeText = prefixText + `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    progress = Math.min(100, Math.max(0, progress));
    progressFill.style.width = `${progress}%`;
    progressFill.className = 'glass-progress-fill';
    if (progressType === 'before') progressFill.classList.add('glass-progress-cyan');
    else if (progressType === 'during') progressFill.classList.add('glass-progress-green');
    else progressFill.classList.add('glass-progress-red');
    progressText.textContent = timeText;
}

function getCustomSelectOptionText(optionEl) {
    if (!optionEl) return '';
    return (
        optionEl.querySelector('.custom-select-text')?.textContent ||
        optionEl.querySelector('h3')?.textContent ||
        optionEl.textContent ||
        ''
    ).trim();
}

function setCustomSelectToggleLabel(toggle, text) {
    if (!toggle) return;
    const icon = toggle.querySelector('i');
    const label = `<span class="custom-select-text">${text}</span>`;
    toggle.innerHTML = icon ? `${icon.outerHTML} ${label}` : label;
}

function syncFieldMenuSelect(selectId) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    const field = sel.closest('.field.menu');
    if (!field) return;
    const menu = field.querySelector('menu');
    const toggle = field.querySelector('.custom-select-toggle');
    const value = sel.value || '';
    if (menu) {
        menu.querySelectorAll('li').forEach((li) => li.classList.remove('selected'));
        const matchLi = menu.querySelector(`li[data-option="${value}"]`) || menu.querySelector('li[data-option=""]');
        if (matchLi) matchLi.classList.add('selected');
    }
    if (toggle) {
        const opt = Array.from(sel.options).find((o) => o.value === value);
        const text = opt ? opt.textContent.trim() : (sel.options[0]?.textContent || '');
        setCustomSelectToggleLabel(toggle, text);
    }
}

function initCustomSelects() {
  const customSelects = document.querySelectorAll(".field.menu");

  customSelects.forEach((field) => {
    const select = field.querySelector("select");
    const button = field.querySelector(".custom-select-toggle");
    const menu = field.querySelector("menu");
    const options = menu.querySelectorAll("li");

    if (!select || !button || !menu || options.length === 0) {
      console.warn("Missing elements for custom select initialization:", field);
      return;
    }

    // Set initial button text
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption) {
      const correspondingLi = Array.from(options).find(
        (li) => li.dataset.option === selectedOption.value
      );
      if (correspondingLi) {
        setCustomSelectToggleLabel(
          button,
          getCustomSelectOptionText(correspondingLi) || selectedOption.textContent.trim()
        );
        correspondingLi.classList.add("selected");
      } else {
        setCustomSelectToggleLabel(button, selectedOption.textContent.trim());
      }
    } else {
      setCustomSelectToggleLabel(button, "Select an option");
    }

    function openMenu() {
      // Close any other open menus first
      document.querySelectorAll(".field.menu.open").forEach((openField) => {
        if (openField !== field) {
          const openButton = openField.querySelector(".custom-select-toggle");
          const openMenuEl = openField.querySelector("menu");
          openField.classList.remove("open");
          openButton.setAttribute("aria-expanded", "false");
          const openParent = openField.closest(
            ".d-flex, .card-header, .col-12, .card, .modal, .modal-body, .mb-3"
          );
          if (openParent) {
            openParent.style.zIndex = "";
            openParent.style.position = "";
          }
        }
      });

      field.classList.add("open");
      button.setAttribute("aria-expanded", "true");

      // Fix z-index issue by elevating parent containers manually
      // Avoid setting position:relative on .modal itself as it breaks positioning
      const parent = field.closest(
        ".mb-3, .modal-body, .d-flex, .card-header, .col-12, .card"
      );
      if (parent && !parent.classList.contains("modal")) {
        parent.style.zIndex = "1000002"; // Match CSS value
        parent.style.position = "relative";
      } else {
        // For modal, z-index is handled by main.js
        const modal = field.closest(".modal");
        if (modal && typeof adjustSidebarZIndex === "function") {
          // Just trigger sidebar z-index adjustment
          adjustSidebarZIndex();
        }
      }

      // Prevent modal from closing when clicking on menu
      const modal = field.closest(".modal");
      if (modal) {
        // Store original backdrop setting if not already stored
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance && !modal.dataset.originalBackdrop) {
          modal.dataset.originalBackdrop =
            modalInstance._config.backdrop || "true";
        }
      }

      const selected = menu.querySelector(".selected") || options[0];
      if (selected) {
        selected.focus();

        // Scroll to selected item if it's the bookingTime menu
        if (select.id === "bookingTime" && selected) {
          // Wait for menu to be fully visible, then scroll
          setTimeout(() => {
            selected.scrollIntoView({
              behavior: "smooth",
              block: "center",
              inline: "nearest",
            });
          }, 150);
        }
      } else if (
        select.id === "bookingTime" &&
        field.dataset.selectedValue
      ) {
        // If no selected item found but we have a stored value, try to find and scroll to it
        const storedValue = field.dataset.selectedValue;
        const storedSelected = menu.querySelector(
          `li[data-option="${storedValue}"]`
        );
        if (storedSelected) {
          setTimeout(() => {
            storedSelected.scrollIntoView({
              behavior: "smooth",
              block: "center",
              inline: "nearest",
            });
            storedSelected.focus();
          }, 150);
        }
      }
    }

    function closeMenu() {
      field.classList.remove("open");
      button.setAttribute("aria-expanded", "false");
      // Don't focus button if user is interacting with other form fields
      // Only focus if no other element has focus
      if (
        document.activeElement === document.body ||
        document.activeElement === null
      ) {
        button.focus();
      }

      const parent = field.closest(
        ".mb-3, .modal-body, .d-flex, .card-header, .col-12, .card"
      );
      if (parent && !parent.classList.contains("modal")) {
        setTimeout(() => {
          if (!field.classList.contains("open")) {
            parent.style.zIndex = "";
            parent.style.position = "";
          }
        }, 300);
      } else {
        // For modal, only reset z-index
        const modal = field.closest(".modal");
        if (modal) {
          setTimeout(() => {
            if (
              !field.classList.contains("open") &&
              typeof adjustSidebarZIndex === "function"
            ) {
              // z-index is handled by main.js, just trigger sidebar z-index adjustment
              adjustSidebarZIndex();
            }
          }, 300);
        }
      }

      // Re-enable modal backdrop behavior after menu closes
      const modal = field.closest(".modal");
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
      const text = getCustomSelectOptionText(optionEl);

      select.value = value;
      select.dispatchEvent(new Event("change")); // Trigger change event

      setCustomSelectToggleLabel(button, text);

      options.forEach((el) => el.classList.remove("selected"));
      optionEl.classList.add("selected");

      closeMenu();
    }

    button.addEventListener("click", (e) => {
      e.stopPropagation();
      e.preventDefault();
      if (field.classList.contains("open")) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    button.addEventListener("keydown", (e) => {
      if (e.key === "ArrowDown" || e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        openMenu();
      }
    });

    // Prevent clicks on menu from closing modal
    menu.addEventListener("click", (e) => {
      e.stopPropagation();
    });

    options.forEach((option) => {
      option.addEventListener("click", (e) => {
        e.stopPropagation();
        e.preventDefault();
        setOption(option);
      });

      option.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          setOption(option);
        } else if (e.key === "ArrowDown") {
          e.preventDefault();
          const next = option.nextElementSibling;
          if (next) next.focus();
        } else if (e.key === "ArrowUp") {
          e.preventDefault();
          const prev = option.previousElementSibling;
          if (prev) prev.focus();
        } else if (e.key === "Escape") {
          e.preventDefault();
          closeMenu();
        }
      });
    });

    // Close menu when clicking outside, but prevent modal from closing
    const handleOutsideClick = (e) => {
      // Don't interfere with input, textarea, or other interactive elements
      const target = e.target;
      const isInteractiveElement =
        target.tagName === "INPUT" ||
        target.tagName === "TEXTAREA" ||
        target.tagName === "SELECT" ||
        target.isContentEditable ||
        target.closest("input, textarea, select, [contenteditable]");

      // If clicking on interactive element, don't close menu
      if (isInteractiveElement) {
        return;
      }

      // Only close if menu is open and click is outside the field
      if (field.classList.contains("open") && !field.contains(target)) {
        const modal = field.closest(".modal");
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
    document.addEventListener("click", handleOutsideClick, false);

    // Clean up listener when menu is removed (if needed)
    field.addEventListener("remove", () => {
      document.removeEventListener("click", handleOutsideClick, false);
    });
  });
}


// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    if (window.appointmentProgressInterval) {
        clearInterval(window.appointmentProgressInterval);
    }
});


