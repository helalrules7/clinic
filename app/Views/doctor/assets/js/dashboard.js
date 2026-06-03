// ============================================
// Mini Sparkline Charts for Stats Cards
// ============================================

function generateSparklineSVG(data) {
    const width = 100;
    const height = 35;
    const padding = 2;

    // Normalize data
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;

    // Generate points
    const points = data.map((value, index) => {
        const x = padding + (index / (data.length - 1)) * (width - padding * 2);
        const y = height - padding - ((value - min) / range) * (height - padding * 2);
        return `${x},${y}`;
    });

    // Create path
    const linePath = `M ${points.join(' L ')}`;

    // Create area path (closed for fill)
    const areaPath = `M ${padding},${height} L ${points.join(' L ')} L ${width - padding},${height} Z`;

    return `
        <svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none">
            <path class="sparkline-area" d="${areaPath}"/>
            <path class="sparkline-path" d="${linePath}"/>
        </svg>
    `;
}

function initMiniStatsCharts() {
    // Generate trend data for dashboard stats cards
    const chartConfigs = [
        { id: 'chartTotalAppointmentsToday', trend: [15, 18, 20, 17, 22, 19, 25, 23, 20] },
        { id: 'chartCompletedAppointments', trend: [12, 15, 17, 14, 18, 16, 20, 19, 17] },
        { id: 'chartMissedAppointments', trend: [3, 3, 3, 3, 4, 3, 5, 4, 3] },
        { id: 'chartNewPatients', trend: [2, 3, 2, 4, 3, 5, 4, 3, 4] },
        { id: 'chartTotalPrescriptions', trend: [8, 10, 9, 12, 11, 13, 12, 11, 10] }
    ];

    chartConfigs.forEach(config => {
        const container = document.getElementById(config.id);
        if (container) {
            // Ensure we have at least 2 data points
            const trendData = config.trend.length >= 2 ? config.trend : [config.trend[0] || 0, config.trend[0] || 1];
            container.innerHTML = generateSparklineSVG(trendData);
        }
    });
}

// Initialize sparkline charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure CSS is loaded
    setTimeout(initMiniStatsCharts, 100);
});

// ============================================
// Quick Actions Horizontal Scroll
// ============================================
function initQuickActionsScroll() {
    const wrapper = document.getElementById('quickActionsWrapper');
    const grid = document.getElementById('quickActionsGrid');
    const navLeft = document.getElementById('qaNavLeft');
    const navRight = document.getElementById('qaNavRight');

    if (!wrapper || !grid || !navLeft || !navRight) return;

    const scrollAmount = 160; // Card width + gap

    // Update navigation arrows and fade indicators based on scroll position
    function updateScrollState() {
        const scrollLeft = grid.scrollLeft;
        const maxScroll = grid.scrollWidth - grid.clientWidth;

        // Update left arrow/fade
        if (scrollLeft <= 5) {
            navLeft.classList.add('hidden');
            wrapper.classList.remove('show-left-fade');
        } else {
            navLeft.classList.remove('hidden');
            wrapper.classList.add('show-left-fade');
        }

        // Update right arrow/fade
        if (scrollLeft >= maxScroll - 5) {
            navRight.classList.add('hidden');
            wrapper.classList.remove('show-right-fade');
        } else {
            navRight.classList.remove('hidden');
            wrapper.classList.add('show-right-fade');
        }
    }

    // Scroll left
    navLeft.addEventListener('click', function() {
        grid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });

    // Scroll right
    navRight.addEventListener('click', function() {
        grid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });

    // Listen for scroll events
    grid.addEventListener('scroll', updateScrollState);

    // Initial state check
    updateScrollState();

    // Re-check on window resize
    window.addEventListener('resize', updateScrollState);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initQuickActionsScroll();
});

// Load today's alerts
function loadTodayAlerts() {
    fetch('/api/alerts/today')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('todayAlertsContainer');
            if (data.success && data.alerts && data.alerts.length > 0) {
                let html = '<div class="list-group">';
                data.alerts.forEach(alert => {
                    const patientName = alert.patient_first_name && alert.patient_last_name 
                        ? `${alert.patient_first_name} ${alert.patient_last_name}` 
                        : 'N/A';
                    // Convert 24-hour time to 12-hour format
                    let timeStr = '';
                    if (alert.alert_time) {
                        const [hours, minutes] = alert.alert_time.split(':');
                        const hour24 = parseInt(hours);
                        const hour12 = hour24 === 0 ? 12 : (hour24 > 12 ? hour24 - 12 : hour24);
                        const period = hour24 >= 12 ? 'PM' : 'AM';
                        timeStr = `${hour12}:${minutes} ${period}`;
                    }
                    
                    html += `
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <i class="bi bi-bell-fill text-warning me-2"></i>
                                        <div class="alert-message-content" style="word-wrap: break-word;">${alert.message}</div>
                                    </h6>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-clock me-1"></i>${timeStr}
                                        ${alert.patient_id ? ` | <i class="bi bi-person me-1"></i>${escapeHtml(patientName)}` : ''}
                                    </p>
                                </div>
                                ${alert.patient_id ? `
                                    <a href="/doctor/patients/${alert.patient_id}" class="btn btn-sm btn-primary ms-2">
                                        <i class="bi bi-person me-1"></i>View Patient
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No alerts for today</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('todayAlertsContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error loading alerts
                </div>
            `;
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Quick Actions Functions
function quickActionBookAppointment() {
    // Navigate to calendar page and trigger add appointment modal
    window.location.href = '/doctor/calendar?openModal=addAppointment';
}

function quickActionAddPatient() {
    // Navigate to patients page and trigger add patient modal
    window.location.href = '/doctor/patients?openModal=addPatient';
}
function quickActionEditProfile() {
    // Navigate to profile page
    window.location.href = '/doctor/profile';
}

function quickActionAddBalance() {
    // Navigate to payments page and trigger add balance modal
    window.location.href = '/doctor/payments?openModal=dailyBalance';
}

function quickActionAddExpense() {
    // Navigate to payments page and trigger add expense modal
    window.location.href = '/doctor/payments?openModal=expense';
}

async function quickActionAddNote() {
    try {
        // Get container dimensions for positioning (we'll use defaults since we're not on the notes page yet)
        const isMobile = window.innerWidth <= 768;
        const widgetWidth = isMobile ? 250 : 300;
        const widgetHeight = isMobile ? 180 : 200;
        
        // Default position (will be centered when notes page loads)
        const x = 0;
        const y = 0;
        
        // Get current note color (default is warning yellow)
        const currentNoteColor = '#fbbf24';
        
        // Create the note via API
        const response = await fetch('/api/notes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: '',
                content: '',
                background_color: currentNoteColor,
                position_x: x,
                position_y: y,
                width: widgetWidth,
                height: widgetHeight,
                z_index: 1
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Store flag in sessionStorage to indicate we should focus the new note
            sessionStorage.setItem('focusNewNote', 'true');
            sessionStorage.setItem('newNoteId', data.note?.id || '');
            
            // Navigate to notes page
            window.location.href = '/doctor/notes';
        } else {
            // If creation fails, still navigate but show error
            console.error('Error creating note:', data.message || 'Unknown error');
            window.location.href = '/doctor/notes';
        }
    } catch (error) {
        console.error('Error creating note:', error);
        // Navigate anyway - user can create note manually
        window.location.href = '/doctor/notes';
    }
}

function quickActionAddAlert() {
    // Navigate to alerts page and trigger add alert modal
    window.location.href = '/doctor/alerts?openModal=addAlert';
}

function quickActionEditProfile() {
    // Navigate to profile page and trigger edit profile modal
    window.location.href = '/doctor/profile?openModal=editProfile';
}

document.addEventListener('DOMContentLoaded', function() {
    loadTodayAlerts();
    loadDoctorSettings().then(() => {
        // Initialize resize handle after settings are loaded
        initializeNotesDashboardResize();
        // Load and apply card order
        loadDashboardCardOrder().then(() => {
            // Update buttons after loading order
            updateCardButtons();
        });
    });
    
    // Initial button update (in case loadDashboardCardOrder hasn't finished)
    setTimeout(() => {
        updateCardButtons();
    }, 200);
    loadDashboardNotes();
    // Initialize drag and drop for cards
    initializeDashboardCardDragDrop();
    
    // Dashboard Note Alert Functions
    window.dashboardShowNoteAlertPicker = function(noteId, event) {
        event.stopPropagation();
        
        // Close all other alert pickers
        document.querySelectorAll('.dashboard-note-alert-picker-dropdown').forEach(picker => {
            if (picker.id !== `dashboardAlertPicker-${noteId}`) {
                picker.style.display = 'none';
            }
        });
        
        // Close color pickers
        document.querySelectorAll('.dashboard-note-color-picker-dropdown').forEach(picker => {
            picker.style.display = 'none';
        });
        
        // Toggle current picker
        const picker = document.getElementById(`dashboardAlertPicker-${noteId}`);
        if (picker) {
            if (picker.style.display === 'none' || !picker.style.display) {
                picker.style.display = 'block';
                // Close on outside click
                setTimeout(() => {
                    document.addEventListener('click', function closePicker(e) {
                        if (!picker.contains(e.target) && !e.target.closest(`#dashboardAlertPicker-${noteId}`) && !e.target.closest(`button[onclick*="dashboardShowNoteAlertPicker(${noteId}"]`)) {
                            picker.style.display = 'none';
                            document.removeEventListener('click', closePicker);
                        }
                    });
                }, 10);
            } else {
                picker.style.display = 'none';
            }
        }
    };
    
    // Format 24-hour time to 12-hour format
    window.dashboardFormat12HourTime = function(time24) {
        const [hours, minutes] = time24.split(':');
        const hour12 = parseInt(hours) % 12 || 12;
        const ampm = parseInt(hours) < 12 ? 'AM' : 'PM';
        return `${hour12}:${minutes} ${ampm}`;
    };
    
    // Convert 12-hour time to 24-hour format
    function dashboardConvertTo24Hour(hour, minute, ampm) {
        let hour24 = parseInt(hour);
        if (ampm === 'PM' && hour24 !== 12) {
            hour24 += 12;
        } else if (ampm === 'AM' && hour24 === 12) {
            hour24 = 0;
        }
        return `${hour24.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}:00`;
    }
    
    window.dashboardCreateAlertFromNote = async function(noteId) {
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        if (!widget) return;
        
        const noteContent = widget.querySelector('.dashboard-note-widget-content');
        if (!noteContent) return;
        
        const alertDate = document.getElementById(`dashboardAlertDate-${noteId}`)?.value;
        const alertHour = document.getElementById(`dashboardAlertHour-${noteId}`)?.value;
        const alertMinute = document.getElementById(`dashboardAlertMinute-${noteId}`)?.value;
        const alertAmPm = document.getElementById(`dashboardAlertAmPm-${noteId}`)?.value;
        
        if (!alertDate || !alertHour || !alertMinute || !alertAmPm) {
            alert('Please select date and time for the alert');
            return;
        }
        
        // Convert to 24-hour format
        const alertTime = dashboardConvertTo24Hour(alertHour, alertMinute, alertAmPm);
        
        // Get note content (HTML)
        const noteHtml = noteContent.innerHTML;
        
        // Close picker
        const picker = document.getElementById(`dashboardAlertPicker-${noteId}`);
        if (picker) {
            picker.style.display = 'none';
        }
        
        try {
            const response = await fetch('/api/alerts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    message: noteHtml, // Send HTML content
                    alert_date: alertDate,
                    alert_time: alertTime,
                    repeat_count: 1,
                    repeat_interval: 0
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success alert-dismissible fade show position-fixed';
                successMsg.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px;';
                successMsg.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>Alert ${data.updated ? 'updated' : 'created'} successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(successMsg);
                
                setTimeout(() => {
                    if (successMsg.parentNode) {
                        successMsg.remove();
                    }
                }, 3000);
                
                // Reload notes to update alert status
                loadDashboardNotes();
            } else {
                alert('Failed to create alert: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error creating alert:', error);
            alert('Failed to create alert: ' + error.message);
        }
    };
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Upcoming Appointments Pagination
    let upcomingCurrentPage = 1;
    let upcomingPerPage = 4; // Limit to 4 appointments per page
    
    // Load upcoming appointments on page load
    loadUpcomingAppointments(upcomingCurrentPage, upcomingPerPage);

    // Load the at-a-glance mini widgets (status donut / board snapshot / revenue)
    loadDashMiniWidgets();

    function loadDashMiniWidgets() {
        loadDashStatusDonut();
        loadDashBoardSnapshot();
        loadDashRevenue();
    }

    const DASH_STATUS_COLORS = {
        Booked: '#3b82f6', CheckedIn: '#22c55e', InProgress: '#f59e0b',
        Completed: '#06b6d4', Rescheduled: '#a78bfa'
    };
    const DASH_STATUS_LABELS = {
        Booked: 'Booked', CheckedIn: 'Checked In', InProgress: 'In Progress',
        Completed: 'Completed', Rescheduled: 'Rescheduled'
    };

    function dashDonutSvg(segments, total, centerNum, centerSub) {
        const C = 2 * Math.PI * 42;
        let off = 0;
        const segs = segments.filter(s => s.value > 0).map(s => {
            const len = total > 0 ? (s.value / total) * C : 0;
            const c = `<circle cx="60" cy="60" r="42" fill="none" stroke="${s.color}" stroke-width="13"
                stroke-dasharray="${len.toFixed(2)} ${(C - len).toFixed(2)}" stroke-dashoffset="${(-off).toFixed(2)}"
                transform="rotate(-90 60 60)" class="dash-donut-seg"/>`;
            off += len;
            return c;
        }).join('');
        return `<svg viewBox="0 0 120 120" class="dash-donut">
            <circle cx="60" cy="60" r="42" fill="none" stroke="var(--border)" stroke-width="13" opacity="0.35"/>
            ${segs}
            <text x="60" y="57" text-anchor="middle" class="dash-donut-num">${centerNum}</text>
            <text x="60" y="75" text-anchor="middle" class="dash-donut-sub">${centerSub}</text>
        </svg>`;
    }

    function loadDashStatusDonut() {
        const body = document.getElementById('dashStatusBody');
        if (!body) return;
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const y = now.getFullYear(), m = now.getMonth() + 1;
        const todayStr = `${y}-${pad(m)}-${pad(now.getDate())}`;
        fetch(`/api/organizer/month?year=${y}&month=${m}`)
            .then(r => r.json())
            .then(res => {
                const byDate = (res && res.ok && res.data && res.data.dataByDate) ? res.data.dataByDate : {};
                const appts = (byDate[todayStr] && byDate[todayStr].appointments) ? byDate[todayStr].appointments : [];
                const counts = {};
                appts.forEach(a => { counts[a.status] = (counts[a.status] || 0) + 1; });
                const total = appts.length;
                if (total === 0) {
                    body.innerHTML = '<div class="dash-mini-empty"><i class="bi bi-calendar-check"></i><span>No appointments today</span></div>';
                    return;
                }
                const order = ['Booked', 'CheckedIn', 'InProgress', 'Completed', 'Rescheduled'];
                const segments = order.map(s => ({ value: counts[s] || 0, color: DASH_STATUS_COLORS[s] }));
                const legend = order.filter(s => counts[s]).map(s =>
                    `<div class="dash-legend-item"><span class="dash-legend-dot" style="background:${DASH_STATUS_COLORS[s]}"></span>`
                    + `<span class="dash-legend-lbl">${DASH_STATUS_LABELS[s]}</span><span class="dash-legend-val">${counts[s]}</span></div>`
                ).join('');
                body.innerHTML = `<div class="dash-donut-wrap">${dashDonutSvg(segments, total, total, total === 1 ? 'appt' : 'appts')}</div>`
                    + `<div class="dash-legend">${legend}</div>`;
            })
            .catch(() => { body.innerHTML = '<div class="dash-mini-empty">—</div>'; });
    }

    function loadDashBoardSnapshot() {
        const body = document.getElementById('dashBoardBody');
        if (!body) return;
        fetch('/api/board/snapshot')
            .then(r => r.json())
            .then(res => {
                const cols = (res && res.ok && Array.isArray(res.data)) ? res.data : [];
                if (!cols.length) {
                    body.innerHTML = '<div class="dash-mini-empty"><i class="bi bi-columns-gap"></i><span>No board columns</span></div>';
                    return;
                }
                const totalCards = cols.reduce((s, c) => s + (c.count || 0), 0);
                const rows = cols.map(c => {
                    const pct = totalCards > 0 ? Math.round((c.count / totalCards) * 100) : 0;
                    return `<div class="dash-board-row">
                        <span class="dash-board-name"><span class="dash-board-dot" style="background:${c.color}"></span>${escapeHtml(c.name)}</span>
                        <span class="dash-board-bar"><span class="dash-board-fill" style="width:${pct}%;background:${c.color}"></span></span>
                        <span class="dash-board-count">${c.count}</span>
                    </div>`;
                }).join('');
                body.innerHTML = `<div class="dash-board-list">${rows}</div>`;
            })
            .catch(() => { body.innerHTML = '<div class="dash-mini-empty">—</div>'; });
    }

    function loadDashRevenue() {
        const body = document.getElementById('dashRevenueBody');
        if (!body) return;
        fetch('/api/dashboard-summary')
            .then(r => r.json())
            .then(res => {
                const d = (res && res.ok && res.data) ? res.data : null;
                const bal = d && d.dailyBalance ? d.dailyBalance : null;
                if (!bal) { body.innerHTML = '<div class="dash-mini-empty">—</div>'; return; }
                const received = Number(bal.total_received || 0);
                const tx = Number(bal.transactions_count || 0);
                const balance = Number(bal.current_balance || 0);
                const fmt = n => Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
                body.innerHTML = `
                    <div class="dash-rev-main">
                        <span class="dash-rev-amt">${fmt(received)}</span>
                        <span class="dash-rev-cur">EGP</span>
                    </div>
                    <div class="dash-rev-sub">Received today</div>
                    <div class="dash-rev-foot">
                        <span><i class="bi bi-receipt"></i> ${tx} ${tx === 1 ? 'transaction' : 'transactions'}</span>
                        <span><i class="bi bi-wallet2"></i> ${fmt(balance)} balance</span>
                    </div>`;
            })
            .catch(() => { body.innerHTML = '<div class="dash-mini-empty">—</div>'; });
    }
    
    // Handle per page change for upcoming appointments (removed - pagination selector removed from UI)
    // Note: Per page selector has been removed from the UI, default value of 10 is used
    
    function loadUpcomingAppointments(page, limit) {
        const container = document.getElementById('upcomingAppointmentsContainer');
        const paginationNav = document.getElementById('upcomingPaginationNav');
        
        // Show loading
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        paginationNav.style.display = 'none';
        
        fetch(`/api/upcoming-appointments?page=${page}&per_page=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    renderUpcomingAppointments(data.data.items);
                    renderUpcomingPagination(data.data.pagination);
                } else {
                    container.innerHTML = '<p class="text-muted text-center py-3">Error loading upcoming appointments</p>';
                }
            })
            .catch(error => {
                console.error('Error loading upcoming appointments:', error);
                container.innerHTML = '<p class="text-muted text-center py-3">Error loading upcoming appointments</p>';
            });
    }
    
    function renderUpcomingAppointments(appointments) {
        const container = document.getElementById('upcomingAppointmentsContainer');
        
        if (!appointments || appointments.length === 0) {
            container.innerHTML = '<div class="appt-empty"><i class="bi bi-calendar-check"></i><p>No upcoming appointments</p><a href="/doctor/calendar" class="btn btn-sm btn-primary"><i class="bi bi-calendar-plus me-1"></i>Schedule one</a></div>';
            return;
        }

        // --- helpers for the enhanced item ---
        const apptInitials = (f, l) => (((f || '').charAt(0) + (l || '').charAt(0)).toUpperCase() || '?');
        const apptRelDate = (ds) => {
            const d = new Date(ds + 'T00:00:00'); const t = new Date(); t.setHours(0, 0, 0, 0);
            const tm = new Date(t); tm.setDate(tm.getDate() + 1);
            const dd = new Date(d); dd.setHours(0, 0, 0, 0);
            if (dd.getTime() === t.getTime()) return 'Today';
            if (dd.getTime() === tm.getTime()) return 'Tomorrow';
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
        };
        const apptTimeUntil = (ds, st) => {
            if (!ds || !st) return '';
            const ms = new Date(`${ds}T${st}`).getTime() - Date.now();
            if (ms <= 0) return '';
            const mins = Math.round(ms / 60000);
            if (mins < 60) return `in ${mins} min`;
            const hrs = Math.floor(mins / 60), rem = mins % 60;
            if (hrs < 24) return rem ? `in ${hrs}h ${rem}m` : `in ${hrs}h`;
            return `in ${Math.floor(hrs / 24)}d`;
        };
        const apptVisitIcon = (t) => ({ New: 'bi-stars', FollowUp: 'bi-arrow-repeat', Procedure: 'bi-clipboard2-pulse' }[t] || 'bi-calendar2-event');
        const STATUS_C = { Booked: '#3b82f6', CheckedIn: '#22c55e', InProgress: '#f59e0b', Completed: '#06b6d4', Cancelled: '#ef4444', NoShow: '#94a3b8', Rescheduled: '#a855f7' };
        const actionFor = (st) => ({ Booked: 'Check in', CheckedIn: 'Start visit', InProgress: 'Continue' }[st] || '');

        let html = '<div class="list-group list-group-flush">';
        appointments.forEach((appointment, idx) => {
            const statusBadgeClass = getStatusBadgeClass(appointment.status);
            const sColor = STATUS_C[appointment.status] || '#64748b';
            const relDate = apptRelDate(appointment.date);
            const startT = appointment.start_time ? appointment.start_time.substring(0, 5) : '';
            const endT = appointment.end_time ? appointment.end_time.substring(0, 5) : '';
            const initials = apptInitials(appointment.first_name, appointment.last_name);
            const until = apptTimeUntil(appointment.date, appointment.start_time);
            const isNext = idx === 0 && until !== '';
            const vIcon = apptVisitIcon(appointment.visit_type);
            const action = actionFor(appointment.status);
            const active = appointment.status && ['completed', 'rescheduled', 'cancelled', 'noshow'].indexOf(appointment.status.toLowerCase()) === -1;

            html += `
                <div class="list-group-item appt-list-item appt-card border-0 mb-2 ${isNext ? 'appt-next' : ''}" style="--appt-color:${sColor}">
                    <div class="appt-row">
                        <div class="appt-avatar" style="background:${sColor}" title="${escapeHtml((appointment.first_name || '') + ' ' + (appointment.last_name || ''))}">${escapeHtml(initials)}</div>
                        <div class="appt-main">
                            <div class="appt-name-row">
                                <a href="/doctor/patients/${appointment.patient_id}" class="appt-name patient-name-link"
                                   data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View Patient Profile">
                                    ${escapeHtml((appointment.first_name || '') + ' ' + (appointment.last_name || ''))}
                                </a>
                                ${isNext ? '<span class="appt-nextup-chip"><i class="bi bi-stars"></i> Next up</span>' : ''}
                            </div>
                            <div class="appt-meta">
                                <span class="appt-meta-time" onclick="window.location.href='/doctor/calendar?date=${appointment.date}&appointment_id=${appointment.id}'" title="Open in calendar"><i class="bi bi-clock"></i> ${startT}${endT ? ' - ' + endT : ''}</span>
                                <span class="appt-meta-sep">·</span>
                                <span class="appt-meta-date">${relDate}</span>
                                <span class="appt-meta-vtype"><i class="bi ${vIcon}"></i> ${escapeHtml(appointment.visit_type || '')}</span>
                            </div>
                            ${active ? `
                            <div class="appointment-progress-container mt-2" data-appointment-id="${appointment.id}" data-date="${appointment.date}" data-start-time="${appointment.start_time}" data-end-time="${appointment.end_time}">
                                <div class="glass-progress-bar">
                                    <div class="glass-progress-fill" style="width: 0%;"></div>
                                    <div class="glass-progress-text">00:00</div>
                                </div>
                            </div>` : ''}
                        </div>
                        <div class="appt-side">
                            <span class="badge ${statusBadgeClass}">${appointment.status}</span>
                            ${until ? `<span class="appt-until">${until}</span>` : ''}
                            <div class="appt-actions">
                                ${action ? `<a href="/doctor/appointments/${appointment.id}" class="btn btn-sm btn-primary appt-act-primary" title="${action}"><i class="bi bi-play-circle me-1"></i>${action}</a>` : ''}
                                <a href="/doctor/appointments/${appointment.id}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Appointment Details"><i class="bi bi-calendar-event"></i></a>
                                <a href="/doctor/patients/${appointment.patient_id}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Patient Profile"><i class="bi bi-person-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
        
        // Reinitialize tooltips (including appointment time links)
        const tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Also initialize tooltips for appointment time links
        const appointmentTimeLinks = container.querySelectorAll('.appointment-time-link');
        appointmentTimeLinks.forEach(link => {
            if (!link.hasAttribute('data-bs-toggle')) {
                link.setAttribute('data-bs-toggle', 'tooltip');
                link.setAttribute('data-bs-placement', 'top');
                link.setAttribute('data-bs-title', 'Navigate to Appointment in your calendar');
                new bootstrap.Tooltip(link);
            }
        });
        
        // Initialize progress bars
        initializeAppointmentProgressBars();
    }
    
    function renderUpcomingPagination(pagination) {
        const paginationNav = document.getElementById('upcomingPaginationNav');
        const paginationList = document.getElementById('upcomingPaginationList');
        
        if (!pagination || pagination.total_pages <= 1) {
            paginationNav.style.display = 'none';
            return;
        }
        
        paginationNav.style.display = 'block';
        
        let html = '';
        const currentPageNum = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        // Previous button
        html += `
            <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${currentPageNum - 1}); return false;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${totalPages}); return false;">${totalPages}</a></li>`;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUpcomingAppointmentsPage(${currentPageNum + 1}); return false;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationList.innerHTML = html;
    }
    
    // Global function for upcoming appointments pagination
    window.loadUpcomingAppointmentsPage = function(page) {
        upcomingCurrentPage = page;
        loadUpcomingAppointments(upcomingCurrentPage, upcomingPerPage);
        
        // Scroll the list top just below the fixed header so the first record shows
        setTimeout(() => {
            const card = document.getElementById('upcomingAppointmentsContainer');
            const target = (card && card.closest('.card')) || document.getElementById('upcomingAppointmentsRow');
            if (window.scrollListToTop) window.scrollListToTop(target);
            else if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100); // Small delay to ensure DOM is updated after data loads
    };
    
    // Missed Appointments Pagination
    let missedCurrentPage = 1;
    let missedPerPage = 5;
    
    // Load missed appointments on page load
    loadMissedAppointments(missedCurrentPage, missedPerPage);
    
    // Handle per page change for missed appointments
    document.getElementById('missedPerPageSelect').addEventListener('change', function() {
        missedPerPage = parseInt(this.value);
        missedCurrentPage = 1;
        loadMissedAppointments(missedCurrentPage, missedPerPage);
    });
    
    function loadMissedAppointments(page, limit) {
        const container = document.getElementById('missedAppointmentsContainer');
        const paginationNav = document.getElementById('missedPaginationNav');
        
        // Show loading
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        paginationNav.style.display = 'none';
        
        fetch(`/api/missed-appointments?page=${page}&per_page=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    renderMissedAppointments(data.data.items);
                    renderMissedPagination(data.data.pagination);
                } else {
                    container.innerHTML = '<p class="text-muted text-center py-3">Error loading missed appointments</p>';
                }
            })
            .catch(error => {
                console.error('Error loading missed appointments:', error);
                container.innerHTML = '<p class="text-muted text-center py-3">Error loading missed appointments</p>';
            });
    }
    
    function renderMissedAppointments(appointments) {
        const container = document.getElementById('missedAppointmentsContainer');
        
        if (!appointments || appointments.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No missed appointments</p>';
            return;
        }
        
        let html = '<div class="list-group list-group-flush">';
        appointments.forEach(appointment => {
            const statusBadgeClass = getStatusBadgeClass(appointment.status);
            const formattedDate = new Date(appointment.date).toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const formattedStartTime = appointment.start_time ? appointment.start_time.substring(0, 5) : '';
            const formattedEndTime = appointment.end_time ? appointment.end_time.substring(0, 5) : '';
            
            html += `
                <div class="list-group-item appt-list-item d-flex justify-content-between align-items-center border-0 mb-1">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <a href="/doctor/patients/${appointment.patient_id}"
                               class="patient-name-link"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               data-bs-title="View Patient Profile">
                                ${escapeHtml((appointment.first_name || '') + ' ' + (appointment.last_name || ''))}
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            ${formattedStartTime} - ${formattedEndTime}
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            ${formattedDate}
                        </small>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="text-end">
                            <span class="badge ${statusBadgeClass}">
                                ${appointment.status}
                            </span>
                            <br>
                            <small class="text-muted">
                                ${appointment.visit_type || ''}
                            </small>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" 
                                    class="btn btn-outline-success"
                                    onclick="markMissedAppointmentCompleted(${appointment.id})"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Mark as Completed">
                                <i class="bi bi-check-circle me-1"></i>Mark Completed
                            </button>
                            <a href="/doctor/appointments/${appointment.id}" 
                               class="btn btn-outline-primary"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Appointment Details">
                                <i class="bi bi-calendar-event me-1"></i> Appointment
                            </a>
                            <a href="/doctor/patients/${appointment.patient_id}" 
                               class="btn btn-outline-info"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               data-bs-title="View Patient Profile">
                                <i class="bi bi-person-circle me-1"></i>View Patient
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
        
        // Reinitialize tooltips
        const tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    function renderMissedPagination(pagination) {
        const paginationNav = document.getElementById('missedPaginationNav');
        const paginationList = document.getElementById('missedPaginationList');
        
        if (!pagination || pagination.total_pages <= 1) {
            paginationNav.style.display = 'none';
            return;
        }
        
        paginationNav.style.display = 'block';
        
        let html = '';
        const currentPageNum = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        // Previous button
        html += `
            <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${currentPageNum - 1}); return false;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${totalPages}); return false;">${totalPages}</a></li>`;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadMissedAppointmentsPage(${currentPageNum + 1}); return false;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationList.innerHTML = html;
    }
    
    // Global function for missed appointments pagination
    window.loadMissedAppointmentsPage = function(page) {
        missedCurrentPage = page;
        loadMissedAppointments(missedCurrentPage, missedPerPage);
        // Scroll the list top just below the fixed header so the first record shows
        setTimeout(() => {
            const c = document.getElementById('missedAppointmentsContainer');
            const target = (c && c.closest('.card')) || c;
            if (window.scrollListToTop) window.scrollListToTop(target);
            else if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    };
    
    // Function to mark missed appointment as completed (shows modal)
    window.markMissedAppointmentCompleted = function(appointmentId) {
        showMissedAppointmentCompletionModal(appointmentId);
    };
    
    // Show completion confirmation modal for missed appointments
    function showMissedAppointmentCompletionModal(appointmentId) {
        const modalHtml = `
            <div class="modal fade" id="missedAppointmentCompletionModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background-color: var(--card); border-color: var(--border); color: var(--text);">
                        <div class="modal-header bg-success text-white" style="background-color: var(--success) !important; border-bottom-color: var(--border) !important;">
                            <h5 class="modal-title" style="color: white !important;">
                                <i class="bi bi-check-circle me-2"></i>Confirm Appointment Completion
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center" style="background-color: var(--card); color: var(--text);">
                            <div class="mb-4">
                                <i class="bi bi-question-circle-fill text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h6 class="mb-3" style="color: var(--text);">Are you sure you want to mark this appointment as completed?</h6>
                            <p class="text-muted mb-0" style="color: var(--muted);">
                                This will update the appointment status to "completed" and cannot be undone.
                            </p>
                        </div>
                        <div class="modal-footer justify-content-center" style="background-color: var(--card); border-top-color: var(--border);">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-success" onclick="confirmMissedAppointmentCompleted(${appointmentId})">
                                <i class="bi bi-check-circle me-1"></i>Confirm Completion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('missedAppointmentCompletionModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('missedAppointmentCompletionModal'));
        modal.show();
        
        // Clean up modal after hide
        document.getElementById('missedAppointmentCompletionModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    // Confirm missed appointment completion
    window.confirmMissedAppointmentCompleted = function(appointmentId) {
        // Hide the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('missedAppointmentCompletionModal'));
        modal.hide();
        
        // Show loading state
        const button = document.querySelector(`button[onclick="confirmMissedAppointmentCompleted(${appointmentId})"]`);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
        }
        
        // API call to update status to Completed
        fetch(`/api/appointments/${appointmentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: 'Completed',
                reason: null
            })
        })
        .then(() => {
            // Always reload page regardless of response
            window.location.reload();
        })
        .catch(() => {
            // Even on error, reload page
            window.location.reload();
        });
    };
    
    function getStatusBadgeClass(status) {
        const statusClasses = {
            'Booked': 'badge-primary',
            'CheckedIn': 'badge-info',
            'InProgress': 'badge-warning',
            'Completed': 'badge-success',
            'Cancelled': 'badge-danger',
            'NoShow': 'badge-secondary',
            'Rescheduled': 'badge-info'
        };
        return statusClasses[status] || 'badge-secondary';
    }
    
    // Recent Activity - Dashboard (Limited to 5)
    let dashboardCurrentPage = 1;
    let dashboardPerPage = 5;
    
    // Load recent activity on page load (dashboard - limited to 5)
    loadRecentActivity(dashboardCurrentPage, dashboardPerPage, 'recentActivityContainer', false);
    
    function loadRecentActivity(page, limit, containerId, showPagination = true) {
        const container = document.getElementById(containerId);
        const paginationNav = showPagination ? document.getElementById('paginationNav') : null;
        
        if (!container) return;
        
        // Show loading
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        if (paginationNav) paginationNav.style.display = 'none';
        
        fetch(`/api/recent-activity?page=${page}&per_page=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    renderRecentActivity(data.data.items, containerId);
                    if (showPagination && paginationNav) {
                        renderPagination(data.data.pagination, 'paginationList', 'loadRecentActivityPage');
                    }
                } else {
                    container.innerHTML = '<p class="text-muted text-center py-3">Error loading recent activity</p>';
                }
            })
            .catch(error => {
                console.error('Error loading recent activity:', error);
                container.innerHTML = '<p class="text-muted text-center py-3">Error loading recent activity</p>';
            });
    }
    
    function renderRecentActivity(events, containerId = 'recentActivityContainer', filterQuery = '') {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        if (!events || events.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No recent activity</p>';
            return;
        }
        
        let html = '<div class="timeline">';
        events.forEach(event => {
            const date = new Date(event.created_at);
            const formattedDate = date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Patient name - clickable if patient_id exists, with highlight
            const patientName = (event.first_name || '') + ' ' + (event.last_name || '');
            let patientNameHtml = patientName;
            if (event.patient_id) {
                const highlightedName = highlightText(patientName, filterQuery);
                patientNameHtml = `<a href="/doctor/patients/${event.patient_id}" class="patient-name-link" style="text-decoration: none; color: var(--accent); font-weight: 600; transition: all 0.2s ease;" onmouseover="this.style.opacity='0.8'; this.style.textDecoration='underline';" onmouseout="this.style.opacity='1'; this.style.textDecoration='none';">${highlightedName}</a>`;
            } else {
                patientNameHtml = highlightText(patientName, filterQuery);
            }
            
            // Event summary with highlight
            const highlightedSummary = highlightText(event.event_summary || '', filterQuery);
            
            // Appointment link - clickable if appointment_id exists
            let appointmentLinkHtml = '';
            if (event.appointment_id) {
                appointmentLinkHtml = ` | <a href="/doctor/appointments/${event.appointment_id}" class="text-primary" style="text-decoration: none; font-weight: 600; transition: all 0.2s ease;" onmouseover="this.style.opacity='0.8'; this.style.textDecoration='underline';" onmouseout="this.style.opacity='1'; this.style.textDecoration='none';">
                    <i class="bi bi-calendar-event me-1"></i>View Appointment
                </a>`;
            }
            
            html += `
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="timeline-marker me-3">
                            <div class="bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${highlightedSummary}</h6>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-person me-1"></i>
                                ${patientNameHtml}
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                ${formattedDate}
                                ${appointmentLinkHtml}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }
    
    function renderPagination(pagination, listId = 'paginationList', pageFunction = 'loadRecentActivityPage', navId = null) {
        // Determine which nav to use - if navId is provided, use it, otherwise try to detect
        let paginationNav;
        if (navId) {
            paginationNav = document.getElementById(navId);
        } else {
            // Auto-detect: if listId contains 'modal', use modal nav, otherwise use dashboard nav
            paginationNav = listId.includes('modal') ? document.getElementById('modalPaginationNav') : document.getElementById('paginationNav');
        }
        
        const paginationList = document.getElementById(listId);
        
        // Validate inputs
        if (!pagination) {
            if (paginationNav) paginationNav.style.display = 'none';
            if (paginationList) paginationList.innerHTML = '';
            return;
        }
        
        if (!paginationList) {
            console.error('Pagination list element not found:', listId);
            return;
        }
        
        // Hide pagination if only one page or no pages
        if (pagination.total_pages <= 1) {
            if (paginationNav) paginationNav.style.display = 'none';
            paginationList.innerHTML = '';
            return;
        }
        
        // Show pagination nav
        if (paginationNav) {
            paginationNav.style.display = 'block';
        }
        
        let html = '';
        const currentPageNum = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        // Previous button
        html += `
            <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); ${pageFunction}(${currentPageNum - 1}); return false;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); ${pageFunction}(1); return false;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); ${pageFunction}(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); ${pageFunction}(${totalPages}); return false;">${totalPages}</a></li>`;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); ${pageFunction}(${currentPageNum + 1}); return false;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationList.innerHTML = html;
    }
    
    // Global function for dashboard pagination (not used, but kept for compatibility)
    window.loadRecentActivityPage = function(page) {
        dashboardCurrentPage = page;
        loadRecentActivity(dashboardCurrentPage, dashboardPerPage, 'recentActivityContainer', false);
    };
    
    // Modal Activities Management
    let modalCurrentPage = 1;
    let modalPerPage = 5;
    let modalFilterQuery = '';
    let modalFilterTimeout = null;
    let modalAllActivities = []; // Store all activities for client-side filtering
    
    // Load all activities for modal (client-side filtering)
    function loadModalActivities(page, limit, filter = '') {
        const container = document.getElementById('allActivitiesContainer');
        const paginationNav = document.getElementById('modalPaginationNav');
        
        if (!container) return;
        
        // If we don't have all activities loaded yet, load them first
        if (modalAllActivities.length === 0) {
            // Show loading
            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            if (paginationNav) paginationNav.style.display = 'none';
            
            // Load all activities (use a large limit to get all)
            fetch(`/api/recent-activity?page=1&per_page=1000`)
                .then(response => response.json())
                .then(data => {
                    if (data.ok && data.data && data.data.items) {
                        modalAllActivities = data.data.items;
                        applyFilterAndRender(page, limit, filter, container, paginationNav);
                    } else {
                        container.innerHTML = '<p class="text-muted text-center py-5">Error loading activities</p>';
                        if (paginationNav) paginationNav.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading activities:', error);
                    container.innerHTML = '<p class="text-muted text-center py-5">Error loading activities</p>';
                    if (paginationNav) paginationNav.style.display = 'none';
                });
        } else {
            // We already have the data, just apply filter and render
            applyFilterAndRender(page, limit, filter, container, paginationNav);
        }
    }
    
    // Apply filter and render activities
    function applyFilterAndRender(page, limit, filter, container, paginationNav) {
        // Filter activities client-side
        let filteredActivities = modalAllActivities;
        
        if (filter && filter.trim() !== '') {
            const filterLower = filter.toLowerCase().trim();
            filteredActivities = modalAllActivities.filter(event => {
                const eventSummary = (event.event_summary || '').toLowerCase();
                const firstName = (event.first_name || '').toLowerCase();
                const lastName = (event.last_name || '').toLowerCase();
                const fullName = `${firstName} ${lastName}`.toLowerCase();
                const phone = (event.phone || '').toLowerCase();
                
                return eventSummary.includes(filterLower) ||
                       firstName.includes(filterLower) ||
                       lastName.includes(filterLower) ||
                       fullName.includes(filterLower) ||
                       phone.includes(filterLower);
            });
        }
        
        // Calculate pagination
        const total = filteredActivities.length;
        const totalPages = Math.ceil(total / limit);
        const offset = (page - 1) * limit;
        const paginatedActivities = filteredActivities.slice(offset, offset + limit);
        
        // Render activities with highlight
        renderRecentActivity(paginatedActivities, 'allActivitiesContainer', filter);
        
        // Render pagination
        if (paginationNav) {
            if (totalPages > 1) {
                const pagination = {
                    current_page: page,
                    per_page: limit,
                    total: total,
                    total_pages: totalPages,
                    has_previous: page > 1,
                    has_next: page < totalPages
                };
                renderPagination(pagination, 'modalPaginationList', 'loadModalActivityPage', 'modalPaginationNav');
            } else {
                paginationNav.style.display = 'none';
            }
        }
    }
    
    // Global function for modal pagination
    window.loadModalActivityPage = function(page) {
        modalCurrentPage = page;
        loadModalActivities(modalCurrentPage, modalPerPage, modalFilterQuery);
        const container = document.getElementById('allActivitiesContainer');
        if (container) {
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };
    
    // Function to highlight text with yellow background
    function highlightText(text, query) {
        if (!query || query.trim() === '') {
            return escapeHtml(text);
        }
        
        const escapedText = escapeHtml(text);
        const escapedQuery = escapeHtml(query);
        const regex = new RegExp(`(${escapedQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        
        return escapedText.replace(regex, '<mark style="background-color: #ffeb3b; color: #000; padding: 2px 4px; border-radius: 3px;">$1</mark>');
    }
    
    // Setup modal event listeners
    const allActivitiesModal = document.getElementById('allActivitiesModal');
    if (allActivitiesModal) {
        // Load activities when modal is shown
        allActivitiesModal.addEventListener('show.bs.modal', function() {
            modalCurrentPage = 1;
            modalPerPage = 5;
            modalFilterQuery = '';
            modalAllActivities = []; // Reset to reload all activities
            const filterInput = document.getElementById('activitiesFilterInput');
            if (filterInput) filterInput.value = '';
            // Update the custom select button text
            const modalPerPageSelect = document.getElementById('modalPerPageSelect');
            const customSelectToggle = modalPerPageSelect?.closest('.field.menu')?.querySelector('.custom-select-toggle');
            if (customSelectToggle) {
                customSelectToggle.textContent = '5 per page';
            }
            // Update selected option in menu
            const menuItems = modalPerPageSelect?.closest('.field.menu')?.querySelectorAll('menu li');
            if (menuItems) {
                menuItems.forEach(item => {
                    item.classList.remove('selected');
                    if (item.getAttribute('data-option') === '5') {
                        item.classList.add('selected');
                    }
                });
            }
            // Update select value
            if (modalPerPageSelect) {
                modalPerPageSelect.value = '5';
            }
            loadModalActivities(modalCurrentPage, modalPerPage);
        });
        
        // Handle per page change in modal
        const modalPerPageSelect = document.getElementById('modalPerPageSelect');
        if (modalPerPageSelect) {
            modalPerPageSelect.addEventListener('change', function() {
                modalPerPage = parseInt(this.value);
                modalCurrentPage = 1;
                loadModalActivities(modalCurrentPage, modalPerPage, modalFilterQuery);
            });
        }
        
        // Handle filter input
        const activitiesFilterInput = document.getElementById('activitiesFilterInput');
        if (activitiesFilterInput) {
            activitiesFilterInput.addEventListener('input', function() {
                clearTimeout(modalFilterTimeout);
                modalFilterTimeout = setTimeout(() => {
                    modalFilterQuery = this.value.trim();
                    modalCurrentPage = 1;
                    loadModalActivities(modalCurrentPage, modalPerPage, modalFilterQuery);
                }, 300); // Debounce filter
            });
        }
        
        // Handle clear filter button
        const clearActivitiesFilter = document.getElementById('clearActivitiesFilter');
        if (clearActivitiesFilter) {
            clearActivitiesFilter.addEventListener('click', function() {
                modalFilterQuery = '';
                if (activitiesFilterInput) {
                    activitiesFilterInput.value = '';
                }
                modalCurrentPage = 1;
                loadModalActivities(modalCurrentPage, modalPerPage, '');
            });
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Dashboard Notes Management
    const dashboardColorMap = {
        'white': { bg: '#ffffff', class: 'white', text: 'dark-text' },
        'red': { bg: '#ef4444', class: 'red', text: 'light-text' },
        'black': { bg: '#1e293b', class: 'black', text: 'light-text' },
        'dodgerblue': { bg: '#1e90ff', class: 'dodgerblue', text: 'light-text' },
        'warning': { bg: '#fbbf24', class: 'warning', text: 'dark-text' },
        'success': { bg: '#10b981', class: 'success', text: 'light-text' }
    };
    
    let dashboardNotes = [];
    let dashboardIsDragging = false;
    let dashboardCurrentDragNote = null;
    let dashboardDragOffset = { x: 0, y: 0 };
    
    // Notes Dashboard Resize Management
    let notesDashboardIsResizing = false;
    let notesDashboardResizeStart = { x: 0, y: 0, height: 0 };
    const DEFAULT_NOTES_DASHBOARD_HEIGHT = 400;
    
    // Load doctor settings
    async function loadDoctorSettings() {
        try {
            const response = await fetch('/api/doctor/settings', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.settings) {
                const cardBody = document.getElementById('notesDashboardCardBody');
                if (cardBody) {
                    // Apply saved height if exists
                    if (data.settings.notes_dashboard_height) {
                        cardBody.style.height = `${data.settings.notes_dashboard_height}px`;
                    } else {
                        cardBody.style.height = `${DEFAULT_NOTES_DASHBOARD_HEIGHT}px`;
                    }
                }
            }
        } catch (error) {
            // Set default height on error
            const cardBody = document.getElementById('notesDashboardCardBody');
            if (cardBody) {
                cardBody.style.height = `${DEFAULT_NOTES_DASHBOARD_HEIGHT}px`;
            }
        }
    }
    
    // Save doctor settings
    async function saveDoctorSettings(settings) {
        try {
            const response = await fetch('/api/doctor/settings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(settings)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data.success === true;
        } catch (error) {
            return false;
        }
    }
    
    // Notes Dashboard Resize Functions
    function startNotesDashboardResize(event) {
        event.preventDefault();
        event.stopPropagation();
        
        notesDashboardIsResizing = true;
        const cardBody = document.getElementById('notesDashboardCardBody');
        
        if (!cardBody) {
            notesDashboardIsResizing = false;
            return;
        }
        
        const rect = cardBody.getBoundingClientRect();
        
        notesDashboardResizeStart.x = event.clientY;
        notesDashboardResizeStart.height = rect.height;
        
        
        document.addEventListener('mousemove', onNotesDashboardResize);
        document.addEventListener('mouseup', stopNotesDashboardResize);
    }
    
    function onNotesDashboardResize(event) {
        if (!notesDashboardIsResizing) return;
        
        event.preventDefault();
        
        const cardBody = document.getElementById('notesDashboardCardBody');
        if (!cardBody) {
            stopNotesDashboardResize();
            return;
        }
        
        const deltaY = event.clientY - notesDashboardResizeStart.x;
        const newHeight = notesDashboardResizeStart.height + deltaY;
        
        // Min height: 400px, Max height: 800px
        const minHeight = 400;
        const maxHeight = 800;
        
        const constrainedHeight = Math.max(minHeight, Math.min(newHeight, maxHeight));
        
        cardBody.style.height = `${constrainedHeight}px`;
        // Update container height as well
        const container = document.getElementById('dashboardNotesContainer');
        if (container) {
            container.style.height = '100%';
        }
    }
    
    function stopNotesDashboardResize() {
        if (notesDashboardIsResizing) {
            const cardBody = document.getElementById('notesDashboardCardBody');
            if (cardBody) {
                const height = parseInt(cardBody.style.height) || DEFAULT_NOTES_DASHBOARD_HEIGHT;
                
                // Save to database
                saveDoctorSettings({
                    notes_dashboard_height: height
                });
            }
            
            notesDashboardIsResizing = false;
        }
        
        document.removeEventListener('mousemove', onNotesDashboardResize);
        document.removeEventListener('mouseup', stopNotesDashboardResize);
    }
    
    // Initialize resize handle - wait for DOM to be ready
    function initializeNotesDashboardResize() {
        const notesDashboardResizeHandle = document.getElementById('notesDashboardResizeHandle');
        if (notesDashboardResizeHandle) {
            // Remove any existing listeners to prevent duplicates
            const newHandle = notesDashboardResizeHandle.cloneNode(true);
            notesDashboardResizeHandle.parentNode.replaceChild(newHandle, notesDashboardResizeHandle);
            
            // Add event listener to the new element
            newHandle.addEventListener('mousedown', startNotesDashboardResize);
        } else {
            // Retry after a short delay
            setTimeout(initializeNotesDashboardResize, 100);
        }
    }
    
    // Dashboard Cards Drag and Drop Management
    let dashboardCardDragging = null;
    let dashboardCardDragOffset = { x: 0, y: 0 };
    
    // Canonical dashboard card order (must match the DB normalization + DOM):
    // Patient Boards → Notes → Visual Analytics → Alerts → Unified Clinical Dashboard
    // → Recent Activities → Missed Appointments.
    const DEFAULT_CARD_ORDER = [
        'patient-boards',
        'notes-dashboard',
        'visual-analytics',
        'today-alerts',
        'unified-clinical-dashboard',
        'recent-activity',
        'missed-appointments'
    ];
    
    // Initialize drag and drop for dashboard cards
    function initializeDashboardCardDragDrop() {
        const dragHandles = document.querySelectorAll('.dashboard-card-drag-handle');
        dragHandles.forEach(handle => {
            handle.addEventListener('mousedown', startCardDrag);
        });
    }
    
    // Start dragging a card
    function startCardDrag(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const cardRow = event.target.closest('.dashboard-card-row');
        if (!cardRow) return;
        
        dashboardCardDragging = cardRow;
        const rect = cardRow.getBoundingClientRect();
        
        dashboardCardDragOffset.x = event.clientX - rect.left;
        dashboardCardDragOffset.y = event.clientY - rect.top;
        
        cardRow.classList.add('dragging');
        cardRow.style.position = 'relative';
        cardRow.style.zIndex = '1000';
        
        document.addEventListener('mousemove', onCardDrag);
        document.addEventListener('mouseup', stopCardDrag);
    }
    
    // Handle card dragging
    function onCardDrag(event) {
        if (!dashboardCardDragging) return;
        
        event.preventDefault();
        
        const allCards = Array.from(document.querySelectorAll('.dashboard-card-row'));
        const draggingIndex = allCards.indexOf(dashboardCardDragging);
        
        if (draggingIndex === -1) return;
        
        // Find the card we're hovering over and determine new position
        let targetCard = null;
        let insertBefore = false;
        
        for (let i = 0; i < allCards.length; i++) {
            if (i === draggingIndex) continue;
            
            const cardRect = allCards[i].getBoundingClientRect();
            const cardCenterY = cardRect.top + (cardRect.height / 2);
            
            // Check if mouse is over this card
            if (event.clientY >= cardRect.top && event.clientY <= cardRect.bottom) {
                targetCard = allCards[i];
                // Determine if we should insert before or after based on mouse position
                insertBefore = event.clientY < cardCenterY;
                break;
            }
        }
        
        // Reorder cards only if we found a valid target
        if (targetCard && targetCard !== dashboardCardDragging) {
            const container = dashboardCardDragging.parentElement;
            if (!container) return;
            
            try {
                // Use nextElementSibling instead of nextSibling to skip text nodes
                if (insertBefore) {
                    // Insert before target card
                    if (targetCard.parentElement === container) {
                        container.insertBefore(dashboardCardDragging, targetCard);
                    }
                } else {
                    // Insert after target card
                    const nextElement = targetCard.nextElementSibling;
                    if (nextElement && nextElement.parentElement === container) {
                        container.insertBefore(dashboardCardDragging, nextElement);
                    } else {
                        // Target is the last element, append dragging card
                        container.appendChild(dashboardCardDragging);
                    }
                }
            } catch (e) {
                // Silently handle error
            }
        }
    }
    
    // Stop dragging a card
    function stopCardDrag() {
        if (dashboardCardDragging) {
            dashboardCardDragging.classList.remove('dragging');
            dashboardCardDragging.style.position = '';
            dashboardCardDragging.style.zIndex = '';
            
            // Save new order
            saveDashboardCardOrder();
            
            // Update buttons after drag
            updateCardButtons();
            
            dashboardCardDragging = null;
        }
        
        document.removeEventListener('mousemove', onCardDrag);
        document.removeEventListener('mouseup', stopCardDrag);
    }
    
    // Update card buttons visibility based on position
    function updateCardButtons() {
        const allCards = Array.from(document.querySelectorAll('.dashboard-card-row'));
        
        allCards.forEach((card, index) => {
            const cardId = card.getAttribute('data-card-id');
            const upButton = card.querySelector(`button[onclick="moveCardUp('${cardId}')"]`);
            const downButton = card.querySelector(`button[onclick="moveCardDown('${cardId}')"]`);
            
            // Hide Up button for first card
            if (upButton) {
                if (index === 0) {
                    upButton.style.display = 'none';
                } else {
                    upButton.style.display = 'flex';
                }
            }
            
            // Hide Down button for last card
            if (downButton) {
                if (index === allCards.length - 1) {
                    downButton.style.display = 'none';
                } else {
                    downButton.style.display = 'flex';
                }
            }
        });
    }
    
    // Move card up
    function moveCardUp(cardId) {
        const allCards = Array.from(document.querySelectorAll('.dashboard-card-row'));
        const currentCard = allCards.find(card => card.getAttribute('data-card-id') === cardId);
        
        if (!currentCard) return;
        
        const currentIndex = allCards.indexOf(currentCard);
        if (currentIndex === 0) return; // Already at top
        
        const container = currentCard.parentElement;
        if (!container) return;
        
        const previousCard = allCards[currentIndex - 1];
        if (previousCard) {
            container.insertBefore(currentCard, previousCard);
            saveDashboardCardOrder();
            updateCardButtons(); // Update buttons after move
        }
    }
    
    // Move card down
    function moveCardDown(cardId) {
        const allCards = Array.from(document.querySelectorAll('.dashboard-card-row'));
        const currentCard = allCards.find(card => card.getAttribute('data-card-id') === cardId);
        
        if (!currentCard) return;
        
        const currentIndex = allCards.indexOf(currentCard);
        if (currentIndex === allCards.length - 1) return; // Already at bottom
        
        const container = currentCard.parentElement;
        if (!container) return;
        
        const nextCard = allCards[currentIndex + 1];
        if (nextCard) {
            const nextNextSibling = nextCard.nextElementSibling;
            if (nextNextSibling) {
                container.insertBefore(currentCard, nextNextSibling);
            } else {
                container.appendChild(currentCard);
            }
            saveDashboardCardOrder();
            updateCardButtons(); // Update buttons after move
        }
    }
    
    // Make functions global
    window.moveCardUp = moveCardUp;
    window.moveCardDown = moveCardDown;
    
    // Toggle dashboard rearrange buttons visibility on mobile
    async function toggleDashboardRearrangeButtons() {
        try {
            const response = await fetch('/api/doctor/settings', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.settings) {
                    const rearrangeEnabled = data.settings.dashboard_rearrange_mobile || false;
                    const isMobile = window.innerWidth <= 768;
                    
                    // Get all rearrange buttons and drag handles
                    const moveButtons = document.querySelectorAll('.dashboard-card-move-btn');
                    const dragHandles = document.querySelectorAll('.dashboard-card-drag-handle');
                    
                    if (isMobile) {
                        // On mobile: show/hide based on setting
                        moveButtons.forEach(btn => {
                            btn.style.display = rearrangeEnabled ? 'flex' : 'none';
                        });
                        dragHandles.forEach(handle => {
                            handle.style.display = rearrangeEnabled ? 'flex' : 'none';
                        });
                    } else {
                        // On desktop: always show
                        moveButtons.forEach(btn => {
                            btn.style.display = 'flex';
                        });
                        dragHandles.forEach(handle => {
                            handle.style.display = 'flex';
                        });
                    }
                }
            }
        } catch (error) {
            console.error('Error loading dashboard rearrange setting:', error);
        }
    }
    
    // Call on page load and window resize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            toggleDashboardRearrangeButtons();
        });
    } else {
        toggleDashboardRearrangeButtons();
    }
    
    // Update on window resize
    window.addEventListener('resize', toggleDashboardRearrangeButtons);
    
    // Save card order to database
    async function saveDashboardCardOrder() {
        try {
            const cards = Array.from(document.querySelectorAll('.dashboard-card-row'));
            const order = cards.map(card => card.getAttribute('data-card-id'));
            
            await saveDoctorSettings({
                dashboard_cards_order: JSON.stringify(order)
            });
        } catch (error) {
            // Silently handle error
        }
    }
    
    // Load and apply card order from database
    async function loadDashboardCardOrder() {
        try {
            const response = await fetch('/api/doctor/settings', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) return;
            
            const data = await response.json();
            
            if (data.success && data.settings && data.settings.dashboard_cards_order) {
                let order;
                try {
                    order = typeof data.settings.dashboard_cards_order === 'string' 
                        ? JSON.parse(data.settings.dashboard_cards_order)
                        : data.settings.dashboard_cards_order;
                } catch (e) {
                    order = DEFAULT_CARD_ORDER;
                }
                
                // Validate order - filter out invalid IDs and ensure all default cards are included
                const validOrder = order.filter(id => DEFAULT_CARD_ORDER.includes(id));
                const missingCards = DEFAULT_CARD_ORDER.filter(id => !validOrder.includes(id));
                // Insert any missing/new card (e.g. recent-activity for existing users)
                // at its DEFAULT position — right after its default predecessor — instead
                // of dumping it at the end, so the intended order is preserved.
                const finalOrder = [...validOrder];
                missingCards.forEach(id => {
                    const defIdx = DEFAULT_CARD_ORDER.indexOf(id);
                    let insertAt = -1;
                    // Place after the nearest preceding default card that's already placed…
                    for (let i = defIdx - 1; i >= 0; i--) {
                        const pos = finalOrder.indexOf(DEFAULT_CARD_ORDER[i]);
                        if (pos !== -1) { insertAt = pos + 1; break; }
                    }
                    // …otherwise before the nearest following default card (so e.g.
                    // patient-boards lands right before notes-dashboard instead of at the end).
                    if (insertAt === -1) {
                        for (let i = defIdx + 1; i < DEFAULT_CARD_ORDER.length; i++) {
                            const pos = finalOrder.indexOf(DEFAULT_CARD_ORDER[i]);
                            if (pos !== -1) { insertAt = pos; break; }
                        }
                    }
                    if (insertAt === -1) insertAt = finalOrder.length;
                    finalOrder.splice(insertAt, 0, id);
                });

                // Apply order - find the container that holds all cards
                const cards = Array.from(document.querySelectorAll('.dashboard-card-row'));
                if (cards.length === 0) return;
                
                const cardMap = new Map(cards.map(card => [card.getAttribute('data-card-id'), card]));
                
                // Get the parent container (should be the main content area)
                const firstCard = cards[0];
                if (!firstCard) return;
                
                const mainContainer = firstCard.parentElement;
                if (!mainContainer) return;
                
                // Reorder cards based on finalOrder
                // Only reorder cards that exist in the DOM
                finalOrder.forEach(cardId => {
                    const card = cardMap.get(cardId);
                    if (card && card.parentElement === mainContainer) {
                        // Remove card from current position and append to end (will be reordered)
                        mainContainer.appendChild(card);
                    }
                });
                
                // Ensure unified-clinical-dashboard is included in saved order if it exists
                // This handles migration for existing users
                const unifiedCard = cardMap.get('unified-clinical-dashboard');
                if (unifiedCard && !validOrder.includes('unified-clinical-dashboard')) {
                    // Card exists but wasn't in saved order, save updated order
                    setTimeout(() => {
                        saveDashboardCardOrder();
                    }, 100);
                }
                
                // Update buttons after loading order
                updateCardButtons();
            }
        } catch (error) {
            // Silently handle error
        }
    }
    
    function getDashboardColorClass(backgroundColor) {
        for (const [key, value] of Object.entries(dashboardColorMap)) {
            if (value.bg.toLowerCase() === backgroundColor.toLowerCase()) {
                return value.class;
            }
        }
        return 'warning';
    }
    
    function getDashboardTextColor(backgroundColor) {
        const hex = backgroundColor.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return brightness > 128 ? 'dark-text' : 'light-text';
    }
    
    function createDashboardNoteWidget(note, index = 0) {
        const bgColor = note.background_color || '#fbbf24';
        const colorClass = getDashboardColorClass(bgColor);
        const textColorClass = getDashboardTextColor(bgColor);
        
        // Always use default size for dashboard display (regardless of saved size in DB)
        const defaultWidth = 300;
        const defaultHeight = 250;
        
        // Position notes side by side instead of using DB position
        const spacing = 20; // Space between notes
        const notesPerRow = 3; // Number of notes per row
        const row = Math.floor(index / notesPerRow);
        const col = index % notesPerRow;
        const x = col * (defaultWidth + spacing) + spacing;
        const y = row * (defaultHeight + spacing) + spacing;
        
        const widget = document.createElement('div');
        widget.className = `dashboard-note-widget color-${colorClass} ${textColorClass}`;
        widget.id = `dashboard-note-${note.id}`;
        widget.style.left = `${x}px`;
        widget.style.top = `${y}px`;
        widget.style.width = `${defaultWidth}px`;
        widget.style.height = `${defaultHeight}px`;
        widget.style.zIndex = note.z_index || 1;
        
        widget.innerHTML = `
            <div class="dashboard-note-widget-header" onmousedown="dashboardStartDrag(event, ${note.id})">
                <input type="text" class="dashboard-note-widget-title" placeholder="Title..." value="${escapeHtml(note.title || '')}" 
                       data-note-id="${note.id}" onblur="dashboardUpdateNoteTitle(${note.id}, this.value)">
                <div class="dashboard-note-widget-actions">
                    <div class="dashboard-note-color-picker-wrapper" style="position: relative;">
                        <button class="dashboard-note-widget-btn" onclick="dashboardToggleColorPicker(${note.id}, event)" title="Change color">
                            <i class="bi bi-palette"></i>
                        </button>
                        <div class="dashboard-note-color-picker-dropdown" id="dashboardColorPicker-${note.id}" style="display: none;">
                            <div class="dashboard-color-option-dropdown white" onclick="dashboardChangeNoteColor(${note.id}, '#ffffff')"></div>
                            <div class="dashboard-color-option-dropdown red" onclick="dashboardChangeNoteColor(${note.id}, '#ef4444')"></div>
                            <div class="dashboard-color-option-dropdown black" onclick="dashboardChangeNoteColor(${note.id}, '#1e293b')"></div>
                            <div class="dashboard-color-option-dropdown dodgerblue" onclick="dashboardChangeNoteColor(${note.id}, '#1e90ff')"></div>
                            <div class="dashboard-color-option-dropdown warning" onclick="dashboardChangeNoteColor(${note.id}, '#fbbf24')"></div>
                            <div class="dashboard-color-option-dropdown success" onclick="dashboardChangeNoteColor(${note.id}, '#10b981')"></div>
                        </div>
                    </div>
                    <div class="dashboard-note-alert-wrapper" style="position: relative;">
                        <button class="dashboard-note-widget-btn" onclick="dashboardShowNoteAlertPicker(${note.id}, event)" title="Create alert from this note">
                            <i class="bi bi-bell"></i>
                        </button>
                        <div class="dashboard-note-alert-picker-dropdown" id="dashboardAlertPicker-${note.id}" style="display: none;">
                            <div class="dashboard-alert-picker-content">
                                <div class="mb-2">
                                    <label class="form-label small">Date:</label>
                                    <input type="date" class="form-control form-control-sm" id="dashboardAlertDate-${note.id}" value="${note.alert ? note.alert.alert_date : new Date().toISOString().split('T')[0]}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Time:</label>
                                    <div class="d-flex gap-1 align-items-center">
                                        <input type="number" class="form-control form-control-sm" id="dashboardAlertHour-${note.id}" min="1" max="12" value="${note.alert ? (parseInt(note.alert.alert_time.split(':')[0]) % 12 || 12) : (new Date().getHours() % 12 || 12)}" style="width: 60px;">
                                        <span>:</span>
                                        <input type="number" class="form-control form-control-sm" id="dashboardAlertMinute-${note.id}" min="0" max="59" value="${note.alert ? note.alert.alert_time.split(':')[1] : new Date().getMinutes().toString().padStart(2, '0')}" style="width: 60px;">
                                        <select class="form-select form-select-sm" id="dashboardAlertAmPm-${note.id}" style="width: 70px;">
                                            <option value="AM" ${note.alert ? (parseInt(note.alert.alert_time.split(':')[0]) < 12 ? 'selected' : '') : (new Date().getHours() < 12 ? 'selected' : '')}>AM</option>
                                            <option value="PM" ${note.alert ? (parseInt(note.alert.alert_time.split(':')[0]) >= 12 ? 'selected' : '') : (new Date().getHours() >= 12 ? 'selected' : '')}>PM</option>
                                        </select>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-primary w-100" onclick="dashboardCreateAlertFromNote(${note.id})">
                                    <i class="bi bi-check-circle me-1"></i>${note.alert ? 'Update Alert' : 'Create Alert'}
                                </button>
                            </div>
                        </div>
                    </div>
                    <button class="dashboard-note-widget-btn" onclick="dashboardBringToFront(${note.id})" title="Bring to front">
                        <i class="bi bi-layers"></i>
                    </button>
                    <button class="dashboard-note-widget-btn" onclick="dashboardFitToSize(${note.id})" title="Fit to default size">
                        <i class="bi bi-arrows-angle-contract"></i>
                    </button>
                    <button class="dashboard-note-widget-btn delete" onclick="dashboardDeleteNote(${note.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="dashboard-note-widget-body">
                <div class="dashboard-note-widget-content"
                     contenteditable="true"
                     data-placeholder="Write your note... (Use @ for patients, # for appointments, $ for drugs)"
                     data-note-id="${note.id}"
                     onblur="dashboardUpdateNoteContent(${note.id}, this.innerHTML)">${note.content || ''}</div>
            </div>
            <div class="dashboard-note-widget-footer">
                <span>Created: ${new Date(note.created_at).toLocaleDateString()}</span>
                <span>Updated: ${new Date(note.updated_at).toLocaleDateString()}</span>
                ${note.alert ? `<span class="dashboard-note-alert-status"><i class="bi bi-bell-fill me-1"></i>Alert: ${new Date(note.alert.alert_date).toLocaleDateString()} ${dashboardFormat12HourTime(note.alert.alert_time)}</span>` : ''}
            </div>
            <div class="dashboard-note-widget-resize" onmousedown="dashboardStartResize(event, ${note.id})"></div>
        `;
        
        return widget;
    }
    
    async function loadDashboardNotes() {
        try {
            const response = await fetch('/api/notes', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }
            
            const data = await response.json();
            const container = document.getElementById('dashboardNotesContainer');
            const loading = document.getElementById('dashboardNotesLoading');
            const empty = document.getElementById('dashboardNotesEmpty');
            
            if (data.success && data.notes && data.notes.length > 0) {
                loading.style.display = 'none';
                empty.style.display = 'none';
                
                // Load all notes (no limit)
                dashboardNotes = data.notes;
                
                // Clear container
                container.querySelectorAll('.dashboard-note-widget').forEach(w => w.remove());
                
                // Add notes (with index for positioning)
                dashboardNotes.forEach((note, index) => {
                    const widget = createDashboardNoteWidget(note, index);
                    container.appendChild(widget);
                    
                    // Add click listener to widget to hide autocomplete portal when clicking anywhere on widget
                    widget.addEventListener('mousedown', function(event) {
                        // Don't hide if clicking on contenteditable (autocomplete should work there)
                        const contentEditable = widget.querySelector('.dashboard-note-widget-content[contenteditable="true"]');
                        if (contentEditable && (contentEditable.contains(event.target) || contentEditable === event.target)) {
                            return; // Allow autocomplete to work in contenteditable
                        }
                        // Hide autocomplete portal when clicking anywhere else on the widget
                        dashboardHideAutocomplete();
                    });
                    
                    // Initialize autocomplete for this contenteditable
                    const contentEditable = widget.querySelector('.dashboard-note-widget-content[contenteditable="true"]');
                    if (contentEditable) {
                        // Add click listener to contenteditable to hide autocomplete portal when clicking
                        // (but not when clicking on autocomplete items or links)
                        contentEditable.addEventListener('mousedown', function(event) {
                            const target = event.target;
                            // Don't hide if clicking on autocomplete items (links, badges) or autocomplete portal
                            const isAutocompleteItem = target.closest('a[data-type], span[data-type]');
                            const isAutocompletePortal = target.closest('.dashboard-note-autocomplete-portal');
                            
                            if (!isAutocompleteItem && !isAutocompletePortal) {
                                // Check if cursor is at a position with trigger symbol
                                const selection = window.getSelection();
                                if (selection.rangeCount > 0) {
                                    const range = selection.getRangeAt(0);
                                    const fullRange = document.createRange();
                                    fullRange.selectNodeContents(contentEditable);
                                    fullRange.setEnd(range.startContainer, range.startOffset);
                                    const textBeforeCursor = fullRange.toString();
                                    const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
                                    
                                    // Only hide if there's no active trigger symbol
                                    if (!match) {
                                        dashboardHideAutocomplete();
                                    }
                                } else {
                                    dashboardHideAutocomplete();
                                }
                            }
                        });
                        
                        dashboardInitAutocomplete(contentEditable);
                    }
                });
                
                // Initialize drug badge click handlers after loading notes
                dashboardInitDrugBadges();
                
                // Keep header button enabled (no limit)
                const headerBtn = document.getElementById('dashboardAddNoteBtnHeader');
                if (headerBtn) {
                    headerBtn.disabled = false;
                    headerBtn.title = '';
                }
            } else {
                loading.style.display = 'none';
                empty.style.display = 'block';
                
                // Keep header button enabled (no limit)
                const headerBtn = document.getElementById('dashboardAddNoteBtnHeader');
                if (headerBtn) {
                    headerBtn.disabled = false;
                    headerBtn.title = '';
                }
            }
        } catch (error) {
            console.error('Error loading dashboard notes:', error);
            document.getElementById('dashboardNotesLoading').style.display = 'none';
        }
    }
    
    async function dashboardAddNote() {
        const container = document.getElementById('dashboardNotesContainer');
        const containerRect = container.getBoundingClientRect();
        
        // Default size for new notes
        const defaultWidth = 300;
        const defaultHeight = 250;
        
        // Position new note next to existing notes (side by side)
        const existingNotes = container.querySelectorAll('.dashboard-note-widget');
        const spacing = 20;
        const notesPerRow = 3;
        const index = existingNotes.length;
        const row = Math.floor(index / notesPerRow);
        const col = index % notesPerRow;
        const x = col * (defaultWidth + spacing) + spacing;
        const y = row * (defaultHeight + spacing) + spacing;
        
        try {
            const response = await fetch('/api/notes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: '',
                    content: '',
                    background_color: '#fbbf24',
                    position_x: x,
                    position_y: y,
                    width: defaultWidth,
                    height: defaultHeight,
                    z_index: 1
                })
            });
            
            const data = await response.json();
            if (data.success) {
                loadDashboardNotes();
            }
        } catch (error) {
            console.error('Error creating note:', error);
            alert('Failed to create note. Please try again.');
        }
    }
    
    function dashboardStartDrag(event, noteId) {
        if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'BUTTON') {
            return;
        }
        
        dashboardIsDragging = true;
        dashboardCurrentDragNote = noteId;
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        const rect = widget.getBoundingClientRect();
        const containerRect = document.getElementById('dashboardNotesContainer').getBoundingClientRect();
        
        dashboardDragOffset.x = event.clientX - rect.left;
        dashboardDragOffset.y = event.clientY - rect.top;
        
        widget.classList.add('dragging');
        
        document.addEventListener('mousemove', dashboardOnDrag);
        document.addEventListener('mouseup', dashboardStopDrag);
        event.preventDefault();
    }
    
    function dashboardOnDrag(event) {
        if (!dashboardIsDragging || !dashboardCurrentDragNote) return;
        
        const widget = document.getElementById(`dashboard-note-${dashboardCurrentDragNote}`);
        const container = document.getElementById('dashboardNotesContainer');
        const containerRect = container.getBoundingClientRect();
        
        let x = event.clientX - containerRect.left - dashboardDragOffset.x;
        let y = event.clientY - containerRect.top - dashboardDragOffset.y;
        
        x = Math.max(0, Math.min(x, containerRect.width - widget.offsetWidth));
        y = Math.max(0, Math.min(y, containerRect.height - widget.offsetHeight));
        
        widget.style.left = `${x}px`;
        widget.style.top = `${y}px`;
    }
    
    function dashboardStopDrag() {
        if (dashboardIsDragging && dashboardCurrentDragNote) {
            const widget = document.getElementById(`dashboard-note-${dashboardCurrentDragNote}`);
            widget.classList.remove('dragging');
            
            dashboardUpdateNotePosition(
                dashboardCurrentDragNote,
                parseInt(widget.style.left),
                parseInt(widget.style.top)
            );
            
            dashboardIsDragging = false;
            dashboardCurrentDragNote = null;
        }
        
        document.removeEventListener('mousemove', dashboardOnDrag);
        document.removeEventListener('mouseup', dashboardStopDrag);
    }
    
    function dashboardToggleColorPicker(noteId, event) {
        event.stopPropagation();
        
        document.querySelectorAll('.dashboard-note-color-picker-dropdown').forEach(picker => {
            if (picker.id !== `dashboardColorPicker-${noteId}`) {
                picker.style.display = 'none';
            }
        });
        
        const picker = document.getElementById(`dashboardColorPicker-${noteId}`);
        if (picker) {
            picker.style.display = picker.style.display === 'none' ? 'flex' : 'none';
            
            setTimeout(() => {
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && !e.target.closest(`#dashboardColorPicker-${noteId}`)) {
                        picker.style.display = 'none';
                        document.removeEventListener('click', closePicker);
                    }
                });
            }, 10);
        }
    }
    
    function dashboardChangeNoteColor(noteId, color) {
        const picker = document.getElementById(`dashboardColorPicker-${noteId}`);
        if (picker) picker.style.display = 'none';
        
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        if (!widget) return;
        
        const colorClass = getDashboardColorClass(color);
        const textColorClass = getDashboardTextColor(color);
        
        widget.classList.remove('color-white', 'color-red', 'color-black', 'color-dodgerblue', 'color-warning', 'color-success');
        widget.classList.remove('light-text', 'dark-text');
        widget.classList.add(`color-${colorClass}`);
        widget.classList.add(textColorClass);
        
        dashboardUpdateNote(noteId, { background_color: color });
    }
    
    async function dashboardUpdateNoteTitle(noteId, title) {
        await dashboardUpdateNote(noteId, { title });
    }
    
    async function dashboardUpdateNoteContent(noteId, content) {
        // content is already HTML from contenteditable innerHTML
        await dashboardUpdateNote(noteId, { content: content });
    }
    
    async function dashboardUpdateNotePosition(noteId, x, y) {
        await dashboardUpdateNote(noteId, { position_x: x, position_y: y });
    }
    
    // Resize functionality for dashboard notes
    let dashboardIsResizing = false;
    let dashboardCurrentResizeNote = null;
    let dashboardResizeStart = { x: 0, y: 0, width: 0, height: 0 };
    
    function dashboardStartResize(event, noteId) {
        event.preventDefault();
        event.stopPropagation();
        
        dashboardIsResizing = true;
        dashboardCurrentResizeNote = noteId;
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        
        if (!widget) {
            dashboardIsResizing = false;
            return;
        }
        
        const rect = widget.getBoundingClientRect();
        
        dashboardResizeStart.x = event.clientX;
        dashboardResizeStart.y = event.clientY;
        dashboardResizeStart.width = rect.width;
        dashboardResizeStart.height = rect.height;
        
        // Bring note to front during resize
        const container = document.getElementById('dashboardNotesContainer');
        if (container) {
            const allNotes = container.querySelectorAll('.dashboard-note-widget');
            let maxZIndex = 0;
            allNotes.forEach(note => {
                const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
                if (zIndex > maxZIndex) maxZIndex = zIndex;
            });
            widget.style.zIndex = maxZIndex + 1;
        }
        
        document.addEventListener('mousemove', dashboardOnResize);
        document.addEventListener('mouseup', dashboardStopResize);
    }
    
    function dashboardOnResize(event) {
        if (!dashboardIsResizing || !dashboardCurrentResizeNote) return;
        
        event.preventDefault();
        
        const widget = document.getElementById(`dashboard-note-${dashboardCurrentResizeNote}`);
        if (!widget) {
            dashboardStopResize();
            return;
        }
        
        const container = document.getElementById('dashboardNotesContainer');
        if (!container) {
            dashboardStopResize();
            return;
        }
        
        const containerRect = container.getBoundingClientRect();
        const widgetRect = widget.getBoundingClientRect();
        
        const deltaX = event.clientX - dashboardResizeStart.x;
        const deltaY = event.clientY - dashboardResizeStart.y;
        
        let newWidth = dashboardResizeStart.width + deltaX;
        let newHeight = dashboardResizeStart.height + deltaY;
        
        // Constrain to container and min size only (no max size)
        const minWidth = 250;
        const minHeight = 200;
        
        // Calculate max width/height based on container bounds only
        const maxAllowedWidth = containerRect.width - (widgetRect.left - containerRect.left);
        const maxAllowedHeight = containerRect.height - (widgetRect.top - containerRect.top);
        
        newWidth = Math.max(minWidth, Math.min(newWidth, maxAllowedWidth));
        newHeight = Math.max(minHeight, Math.min(newHeight, maxAllowedHeight));
        
        widget.style.width = `${newWidth}px`;
        widget.style.height = `${newHeight}px`;
    }
    
    function dashboardStopResize() {
        if (dashboardIsResizing && dashboardCurrentResizeNote) {
            const widget = document.getElementById(`dashboard-note-${dashboardCurrentResizeNote}`);
            
            // Save size
            dashboardUpdateNoteSize(
                dashboardCurrentResizeNote,
                parseInt(widget.style.width),
                parseInt(widget.style.height)
            );
            
            dashboardIsResizing = false;
            dashboardCurrentResizeNote = null;
        }
        
        document.removeEventListener('mousemove', dashboardOnResize);
        document.removeEventListener('mouseup', dashboardStopResize);
    }
    
    async function dashboardUpdateNoteSize(noteId, width, height) {
        await dashboardUpdateNote(noteId, { width, height });
    }
    
    // Bring to front
    function dashboardBringToFront(noteId) {
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        const container = document.getElementById('dashboardNotesContainer');
        if (!widget || !container) return;
        
        const allNotes = container.querySelectorAll('.dashboard-note-widget');
        let maxZIndex = 0;
        allNotes.forEach(note => {
            const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
            if (zIndex > maxZIndex) maxZIndex = zIndex;
        });
        
        widget.style.zIndex = maxZIndex + 1;
        dashboardUpdateNote(noteId, { z_index: maxZIndex + 1 });
    }
    
    // Fit to default size
    function dashboardFitToSize(noteId) {
        const widget = document.getElementById(`dashboard-note-${noteId}`);
        if (!widget) return;
        
        const defaultWidth = 300;
        const defaultHeight = 250;
        
        widget.style.width = `${defaultWidth}px`;
        widget.style.height = `${defaultHeight}px`;
        
        dashboardUpdateNoteSize(noteId, defaultWidth, defaultHeight);
    }
    
    async function dashboardUpdateNote(noteId, data) {
        try {
            const response = await fetch(`/api/notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Network error' }));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to update note');
            }
        } catch (error) {
            console.error('Error updating note:', error);
            // Don't show alert for every update - it's too frequent
            // Only log to console
        }
    }
    
    async function dashboardDeleteNote(noteId) {
        // Show confirmation modal
        dashboardShowDeleteConfirmModal(noteId);
    }
    
    // Show delete confirmation modal
    function dashboardShowDeleteConfirmModal(noteId) {
        const modal = document.getElementById('dashboardDeleteNoteModal');
        if (!modal) {
            // Create modal if it doesn't exist
            const modalHtml = `
                <div class="modal fade" id="dashboardDeleteNoteModal" tabindex="-1" aria-labelledby="dashboardDeleteNoteModalLabel" aria-hidden="true" style="z-index: 99999;">
                    <div class="modal-dialog modal-dialog-centered" style="z-index: 100000;">
                        <div class="modal-content" style="background: var(--card); border: 1px solid var(--border); z-index: 100001;">
                            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                                <h5 class="modal-title" id="dashboardDeleteNoteModalLabel" style="color: var(--text);">
                                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                    Delete Note
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="color: var(--text);">
                                <p>Are you sure you want to delete this note? This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer" style="border-top: 1px solid var(--border);">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="dashboardConfirmDeleteBtn">
                                    <i class="bi bi-trash me-2"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        const modalInstance = new bootstrap.Modal(document.getElementById('dashboardDeleteNoteModal'));
        const confirmBtn = document.getElementById('dashboardConfirmDeleteBtn');
        
        // Remove previous event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Add new event listener
        newConfirmBtn.addEventListener('click', async function() {
            await dashboardPerformDelete(noteId);
            modalInstance.hide();
        });
        
        modalInstance.show();
    }
    
    // Perform the actual delete
    async function dashboardPerformDelete(noteId) {
        try {
            const response = await fetch(`/api/notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            
            // Check if response is ok
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Network error' }));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                const widget = document.getElementById(`dashboard-note-${noteId}`);
                if (widget) {
                    widget.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    widget.style.opacity = '0';
                    widget.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        widget.remove();
                        loadDashboardNotes();
                    }, 300);
                }
            } else {
                throw new Error(data.message || 'Failed to delete note');
            }
        } catch (error) {
            console.error('Error deleting note:', error);
            alert('Failed to delete note: ' + error.message);
        }
    }
    
    // Autocomplete functionality for dashboard notes
    let dashboardAutocompletePortal = null;
    let dashboardCurrentAutocompleteType = null;
    let dashboardCurrentAutocompleteQuery = '';
    let dashboardCurrentAutocompleteItems = [];
    let dashboardSelectedAutocompleteIndex = -1;
    let dashboardAutocompleteTextarea = null;
    let dashboardAutocompleteCursorPosition = 0;
    let dashboardAutocompleteDebounceTimer = null;
    let dashboardAutocompleteUpdateHandler = null;
    
    // Process autocomplete input - must be defined before dashboardInitAutocomplete
    function dashboardProcessAutocompleteInput(event) {
        const contentEditable = event.target;
        const selection = window.getSelection();
        
        if (!selection.rangeCount) {
            dashboardHideAutocomplete();
            return;
        }
        
        const range = selection.getRangeAt(0).cloneRange();
        
        const fullRange = document.createRange();
        fullRange.selectNodeContents(contentEditable);
        fullRange.setEnd(range.startContainer, range.startOffset);
        const textBeforeCursor = fullRange.toString();
        
        const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
        
        if (match) {
            const trigger = match[1];
            const query = match[2];
            
            
            // Minimum query length: 2 characters for patients and drugs
            // For appointments: if numeric (ID search), allow 1 char; if date format, allow 8+ chars; if text (patient name), require 2 chars
            let minLength = 2;
            if (trigger === '#') {
                // For appointments: check if it's a date format, numeric ID, or patient name
                if (query.length === 0) {
                    minLength = 0; // Allow showing recent appointments when just typing #
                } else {
                    // Check if query looks like a date (DD-MM-YYYY, YYYY-MM-DD, DD/MM/YYYY, etc.)
                    const datePattern = /^(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}|\d{2,4}[-\/]\d{1,2}[-\/]\d{1,2})$/;
                    if (datePattern.test(query)) {
                        minLength = 8; // Minimum date length (DD-MM-YY)
                    } else if (/^\d+$/.test(query)) {
                        minLength = 1; // Numeric ID search
                    } else {
                        minLength = 2; // Patient name search
                    }
                }
            } else if (trigger === '$') {
                minLength = 2; // Drug search
            } else if (trigger === '@') {
                minLength = 2; // Patient search
            }
            
            
            if (query.length >= minLength && query !== dashboardCurrentAutocompleteQuery) {
                dashboardCurrentAutocompleteType = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
                dashboardCurrentAutocompleteQuery = query;
                dashboardAutocompleteTextarea = contentEditable;
                
                
                const rect = range.getBoundingClientRect();
                dashboardAutocompleteCursorPosition = {
                    range: range,
                    textBefore: textBeforeCursor,
                    match: match
                };
                
                dashboardShowAutocomplete(contentEditable, rect, query);
            } else if (query.length < minLength) {
                dashboardHideAutocomplete();
            }
        } else {
            // No trigger symbol found - hide autocomplete
            dashboardHideAutocomplete();
        }
    }
    
    // Handle contenteditable input with debounce
    function dashboardHandleContentEditableInput(event) {
        const contentEditable = event.target;
        
        // Check if user is deleting content from autocomplete elements
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const startContainer = range.startContainer;
            
            // Check if cursor is inside or at the edge of an autocomplete element
            let autocompleteElement = null;
            if (startContainer.nodeType === Node.TEXT_NODE) {
                autocompleteElement = startContainer.parentElement;
            } else if (startContainer.nodeType === Node.ELEMENT_NODE) {
                autocompleteElement = startContainer;
            }
            
            // Check if it's an autocomplete element (patient, appointment, or drug)
            while (autocompleteElement && autocompleteElement !== contentEditable) {
                const dataType = autocompleteElement.getAttribute('data-type');
                if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                    // Check if user is actually deleting (not just clicking)
                    const inputType = event.inputType;
                    if (inputType === 'deleteContentBackward' || inputType === 'deleteContentForward' || 
                        inputType === 'deleteByDrag' || inputType === 'deleteByCut' ||
                        (!inputType && event.data === null)) {
                        // User is editing/deleting from an autocomplete element
                        // Remove the entire element
                        const parent = autocompleteElement.parentNode;
                        if (parent) {
                            // Create a text node with space to maintain cursor position
                            const space = document.createTextNode(' ');
                            parent.replaceChild(space, autocompleteElement);
                            
                            // Set cursor after space
                            const newRange = document.createRange();
                            newRange.setStartAfter(space);
                            newRange.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(newRange);
                            
                            // Ensure focus
                            contentEditable.focus();
                            
                            // Update note content
                            const noteId = contentEditable.getAttribute('data-note-id');
                            if (noteId) {
                                dashboardUpdateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                            }
                        }
                        // Hide autocomplete when deleting autocomplete element
                        dashboardHideAutocomplete();
                        return; // Don't process autocomplete after deletion
                    }
                    // If not deleting, allow normal interaction
                    break;
                }
                autocompleteElement = autocompleteElement.parentElement;
            }
        }
        
        // Check immediately if trigger symbol was deleted (for immediate response)
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0).cloneRange();
            const fullRange = document.createRange();
            fullRange.selectNodeContents(contentEditable);
            fullRange.setEnd(range.startContainer, range.startOffset);
            const textBeforeCursor = fullRange.toString();
            const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
            
            // If no trigger symbol found, hide immediately
            if (!match && dashboardAutocompletePortal) {
                const computedStyle = window.getComputedStyle(dashboardAutocompletePortal);
                const isVisible = computedStyle.display !== 'none' && computedStyle.visibility !== 'hidden' && computedStyle.opacity !== '0' && !dashboardAutocompletePortal.classList.contains('hidden');
                if (isVisible) {
                    dashboardHideAutocomplete();
                }
            }
        }
        
        // Debounce autocomplete processing
        if (dashboardAutocompleteDebounceTimer) {
            clearTimeout(dashboardAutocompleteDebounceTimer);
        }
        
        dashboardAutocompleteDebounceTimer = setTimeout(() => {
            dashboardProcessAutocompleteInput(event);
        }, 150);
    }
    
    // Initialize autocomplete for a contenteditable div
    function dashboardInitAutocomplete(contentEditable) {
        if (!contentEditable) return;
        
        contentEditable.addEventListener('input', dashboardHandleContentEditableInput);
        contentEditable.addEventListener('keydown', dashboardHandleContentEditableKeydown);
        contentEditable.addEventListener('blur', function() {
            setTimeout(() => {
                dashboardHideAutocomplete();
            }, 200);
        });
        
        contentEditable.addEventListener('click', function(event) {
            const target = event.target;
            const link = target.closest('a[data-type]');
            
            if (link) {
                event.stopPropagation();
                event.preventDefault();
                window.open(link.href, '_blank');
                return false;
            }
        }, true);
        
        contentEditable.addEventListener('mousedown', function(event) {
            const target = event.target;
            const link = target.closest('a[data-type]');
            const badge = target.closest('span[data-type]');
            
            if (link) {
                event.stopPropagation();
                return true;
            }
            
            if (badge) {
                event.preventDefault();
                event.stopPropagation();
                const range = document.createRange();
                const selection = window.getSelection();
                const badgeRect = badge.getBoundingClientRect();
                const clickX = event.clientX;
                
                if (clickX < badgeRect.left + badgeRect.width / 2) {
                    range.setStartBefore(badge);
                } else {
                    range.setStartAfter(badge);
                }
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                contentEditable.focus();
            }
        }, true);
    }
    
    function dashboardHandleContentEditableInput(event) {
        const contentEditable = event.target;
        const selection = window.getSelection();
        
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const startContainer = range.startContainer;
            
            let autocompleteElement = null;
            if (startContainer.nodeType === Node.TEXT_NODE) {
                autocompleteElement = startContainer.parentElement;
            } else if (startContainer.nodeType === Node.ELEMENT_NODE) {
                autocompleteElement = startContainer;
            }
            
            while (autocompleteElement && autocompleteElement !== contentEditable) {
                const dataType = autocompleteElement.getAttribute('data-type');
                if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                    const inputType = event.inputType;
                    if (inputType === 'deleteContentBackward' || inputType === 'deleteContentForward' || 
                        inputType === 'deleteByDrag' || inputType === 'deleteByCut' ||
                        (!inputType && event.data === null)) {
                        const parent = autocompleteElement.parentNode;
                        if (parent) {
                            const space = document.createTextNode(' ');
                            parent.replaceChild(space, autocompleteElement);
                            
                            const newRange = document.createRange();
                            newRange.setStartAfter(space);
                            newRange.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(newRange);
                            contentEditable.focus();
                            
                            const noteId = contentEditable.getAttribute('data-note-id');
                            if (noteId) {
                                dashboardUpdateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                            }
                        }
                        return;
                    }
                    break;
                }
                autocompleteElement = autocompleteElement.parentElement;
            }
        }
        
        if (dashboardAutocompleteDebounceTimer) {
            clearTimeout(dashboardAutocompleteDebounceTimer);
        }
        
        dashboardAutocompleteDebounceTimer = setTimeout(() => {
            dashboardProcessAutocompleteInput(event);
        }, 300);
    }
    
    function dashboardProcessAutocompleteInput(event) {
        const contentEditable = event.target;
        const selection = window.getSelection();
        
        if (!selection.rangeCount) {
            dashboardHideAutocomplete();
            return;
        }
        
        const range = selection.getRangeAt(0).cloneRange();
        
        const fullRange = document.createRange();
        fullRange.selectNodeContents(contentEditable);
        fullRange.setEnd(range.startContainer, range.startOffset);
        const textBeforeCursor = fullRange.toString();
        
        const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
        
        if (match) {
            const trigger = match[1];
            const query = match[2];
            
            
            // Minimum query length: 2 characters for patients and drugs
            // For appointments: if numeric (ID search), allow 1 char; if date format, allow 8+ chars; if text (patient name), require 2 chars
            let minLength = 2;
            if (trigger === '#') {
                // For appointments: check if it's a date format, numeric ID, or patient name
                if (query.length === 0) {
                    minLength = 0; // Allow showing recent appointments when just typing #
                } else {
                    // Check if query looks like a date (DD-MM-YYYY, YYYY-MM-DD, DD/MM/YYYY, etc.)
                    const datePattern = /^(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}|\d{2,4}[-\/]\d{1,2}[-\/]\d{1,2})$/;
                    if (datePattern.test(query)) {
                        minLength = 8; // Minimum date length (DD-MM-YY)
                    } else if (/^\d+$/.test(query)) {
                        minLength = 1; // Numeric ID search
                    } else {
                        minLength = 2; // Patient name search
            }
                }
            } else if (trigger === '$') {
                minLength = 2; // Drug search
            } else if (trigger === '@') {
                minLength = 2; // Patient search
            }
            
            
            if (query.length >= minLength && query !== dashboardCurrentAutocompleteQuery) {
                dashboardCurrentAutocompleteType = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
                dashboardCurrentAutocompleteQuery = query;
                dashboardAutocompleteTextarea = contentEditable;
                
                
                const rect = range.getBoundingClientRect();
                dashboardAutocompleteCursorPosition = {
                    range: range,
                    textBefore: textBeforeCursor,
                    match: match
                };
                
                dashboardShowAutocomplete(contentEditable, rect, query);
            } else if (query.length < minLength) {
                dashboardHideAutocomplete();
            }
        } else {
            // No trigger symbol found - hide autocomplete
            dashboardHideAutocomplete();
        }
    }
    
    function dashboardHandleContentEditableKeydown(event) {
        const contentEditable = event.target;
        const selection = window.getSelection();
        
        // Handle Backspace/Delete to immediately check if trigger symbols are deleted
        if (event.key === 'Backspace' || event.key === 'Delete' || event.keyCode === 8 || event.keyCode === 46) {
            // Use setTimeout to check after the deletion happens
            setTimeout(() => {
                if (!selection.rangeCount) {
                    dashboardHideAutocomplete();
                    return;
                }
                
                const range = selection.getRangeAt(0).cloneRange();
                const fullRange = document.createRange();
                fullRange.selectNodeContents(contentEditable);
                fullRange.setEnd(range.startContainer, range.startOffset);
                const textBeforeCursor = fullRange.toString();
                
                // Check if trigger symbol still exists
                const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
                if (!match) {
                    // Trigger symbol was deleted - hide autocomplete immediately
                    dashboardHideAutocomplete();
                }
            }, 0);
        }
        
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            let node = range.startContainer;
            
            while (node && node !== contentEditable) {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    const dataType = node.getAttribute('data-type');
                    if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                        if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar' || event.keyCode === 13 || event.keyCode === 32) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            const textContent = (event.key === 'Enter' || event.keyCode === 13) ? '\n' : ' ';
                            const newTextNode = document.createTextNode(textContent);
                            const parent = node.parentNode;
                            
                            if (parent) {
                                parent.insertBefore(newTextNode, node.nextSibling);
                                
                                const newRange = document.createRange();
                                newRange.setStartAfter(newTextNode);
                                newRange.collapse(true);
                                selection.removeAllRanges();
                                selection.addRange(newRange);
                                contentEditable.focus();
                                
                                const noteId = contentEditable.getAttribute('data-note-id');
                                if (noteId) {
                                    dashboardUpdateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                                }
                            }
                            return;
                        }
                        break;
                    }
                }
                node = node.parentNode;
            }
        }
        
        if (!dashboardAutocompletePortal || dashboardAutocompletePortal.style.display === 'none') {
            return;
        }
        
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            dashboardSelectedAutocompleteIndex = Math.min(dashboardSelectedAutocompleteIndex + 1, dashboardCurrentAutocompleteItems.length - 1);
            dashboardUpdateAutocompleteSelection();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            dashboardSelectedAutocompleteIndex = Math.max(dashboardSelectedAutocompleteIndex - 1, -1);
            dashboardUpdateAutocompleteSelection();
        } else if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            if (dashboardSelectedAutocompleteIndex >= 0 && dashboardCurrentAutocompleteItems[dashboardSelectedAutocompleteIndex]) {
                dashboardSelectAutocompleteItem(dashboardCurrentAutocompleteItems[dashboardSelectedAutocompleteIndex]);
            }
        } else if (event.key === 'Escape') {
            dashboardHideAutocomplete();
        }
    }
    
    async function dashboardShowAutocomplete(contentEditable, cursorRect, query) {
        if (!dashboardAutocompletePortal) {
            dashboardAutocompletePortal = document.createElement('div');
            dashboardAutocompletePortal.className = 'dashboard-note-autocomplete-portal';
            dashboardAutocompletePortal.id = 'dashboardNoteAutocompletePortal';
            document.body.appendChild(dashboardAutocompletePortal);
        }
        
        // Remove existing click handler before adding a new one (to avoid duplicates)
        document.removeEventListener('click', dashboardHandleClickOutside, true);
        // Add click outside handler to close autocomplete (use capture phase for early detection)
        document.addEventListener('click', dashboardHandleClickOutside, true);
        
        // Also add mousedown handler for better responsiveness
        document.removeEventListener('mousedown', dashboardHandleClickOutside, true);
        document.addEventListener('mousedown', dashboardHandleClickOutside, true);
        
        // Position portal at cursor location (not following mouse)
        // For position: fixed, we use viewport coordinates directly (no scroll offset needed)
        const x = cursorRect.left;
        const y = cursorRect.bottom + 5;
        
        // Remove hidden class first
        dashboardAutocompletePortal.classList.remove('hidden');
        
        dashboardAutocompletePortal.style.position = 'fixed';
        dashboardAutocompletePortal.style.left = `${x}px`;
        dashboardAutocompletePortal.style.top = `${y}px`;
        dashboardAutocompletePortal.style.display = 'block';
        dashboardAutocompletePortal.style.visibility = 'visible';
        dashboardAutocompletePortal.style.opacity = '1';
        dashboardAutocompletePortal.style.zIndex = '10000010';
        dashboardAutocompletePortal.style.pointerEvents = 'auto';
        
        // Remove any existing mouse tracking handler (we don't want it to follow mouse)
        if (dashboardAutocompleteUpdateHandler) {
            document.removeEventListener('mousemove', dashboardAutocompleteUpdateHandler);
            dashboardAutocompleteUpdateHandler = null;
        }
        
        await dashboardLoadAutocompleteItems(query);
    }
    
    // Handle click outside autocomplete portal
    function dashboardHandleClickOutside(event) {
        if (!dashboardAutocompletePortal) {
            return;
        }
        
        // Check if portal is visible
        const computedStyle = window.getComputedStyle(dashboardAutocompletePortal);
        const isHidden = computedStyle.display === 'none' || computedStyle.visibility === 'hidden' || computedStyle.opacity === '0' || dashboardAutocompletePortal.classList.contains('hidden');
        
        if (isHidden) {
            return;
        }
        
        // Check if click is outside portal and contenteditable
        const target = event.target;
        const clickedOnPortal = dashboardAutocompletePortal.contains(target);
        
        // Check if clicking on contenteditable
        let clickedOnContentEditable = false;
        if (dashboardAutocompleteTextarea) {
            clickedOnContentEditable = (
                dashboardAutocompleteTextarea.contains(target) || 
                dashboardAutocompleteTextarea === target ||
                dashboardAutocompleteTextarea.isSameNode(target)
            );
            
            // Also check if target is inside the contenteditable's parent container
            if (!clickedOnContentEditable) {
                const contentEditableParent = dashboardAutocompleteTextarea.closest('.note-widget-content-container, .note-widget-content');
                if (contentEditableParent && contentEditableParent.contains(target)) {
                    clickedOnContentEditable = true;
                }
            }
        }
        
        // Also check if clicking on autocomplete items (they should not close the portal)
        const clickedOnAutocompleteItem = target.closest('.dashboard-note-autocomplete-item');
        
        // If clicking outside both portal and contenteditable, close the portal
        if (!clickedOnPortal && !clickedOnContentEditable && !clickedOnAutocompleteItem) {
            dashboardHideAutocomplete();
        }
    }
    
    async function dashboardLoadAutocompleteItems(query) {
        try {
            if (query !== dashboardCurrentAutocompleteQuery) {
                return;
            }
            
            let url = '';
            if (dashboardCurrentAutocompleteType === 'patient') {
                url = `/api/patients/search?q=${encodeURIComponent(query)}`;
            } else if (dashboardCurrentAutocompleteType === 'appointment') {
                url = `/api/appointments/search?q=${encodeURIComponent(query)}&limit=10`;
            } else if (dashboardCurrentAutocompleteType === 'drug') {
                url = `/api/searchDrugsAutocomplete?q=${encodeURIComponent(query)}&limit=10`;
            }
            
            if (!url) {
                return;
            }
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                return;
            }
            
            const data = await response.json();
            
            if (query !== dashboardCurrentAutocompleteQuery) {
                return;
            }
            
            let items = [];
            
            if (dashboardCurrentAutocompleteType === 'patient' && data.ok && data.data) {
                items = data.data.map(patient => ({
                    type: 'patient',
                    id: patient.id,
                    title: `${patient.first_name} ${patient.last_name}`,
                    subtitle: patient.phone || '',
                    data: patient
                }));
            } else if (dashboardCurrentAutocompleteType === 'appointment' && data.ok && data.data) {
                items = data.data.map(apt => {
                    // Handle date properly - apt.date is in 'YYYY-MM-DD' format
                    // Convert to DD-MM-YYYY format
                    let dateStr = '';
                    if (apt.date) {
                        try {
                            // Parse date string (YYYY-MM-DD) and convert to DD-MM-YYYY
                            const dateParts = apt.date.split('-');
                            if (dateParts.length === 3) {
                                // Format: DD-MM-YYYY
                                const day = dateParts[2].padStart(2, '0');
                                const month = dateParts[1].padStart(2, '0');
                                const year = dateParts[0];
                                dateStr = `${day}-${month}-${year}`;
                            } else {
                                dateStr = apt.date;
                            }
                        } catch (e) {
                            dateStr = apt.date; // Fallback to original string
                        }
                    }
                    const timeStr = apt.start_time ? apt.start_time.substring(0, 5) : '';
                    const patientName = escapeHtml(apt.patient_name || 'Unknown');
                    const status = escapeHtml(apt.status || '');
                    return {
                        type: 'appointment',
                        id: apt.id,
                        title: `#${apt.id} - ${patientName}`,
                        subtitle: `${dateStr}${timeStr ? ' at ' + timeStr : ''}${status ? ' - ' + status : ''}`,
                        data: apt
                    };
                });
            } else if (dashboardCurrentAutocompleteType === 'drug' && data.drugs) {
                items = data.drugs.map(drug => ({
                    type: 'drug',
                    id: drug.ID,
                    title: drug.drug_name,
                    subtitle: drug.active_ingredient || drug.Company || '',
                    data: drug
                }));
            }
            
            if (query === dashboardCurrentAutocompleteQuery) {
                dashboardCurrentAutocompleteItems = items;
                dashboardSelectedAutocompleteIndex = -1;
                dashboardRenderAutocompleteItems(items);
            }
        } catch (error) {
            // Silent error handling
        }
    }
    
    function dashboardRenderAutocompleteItems(items) {
        if (!dashboardAutocompletePortal) return;
        
        if (items.length === 0) {
            dashboardAutocompletePortal.innerHTML = '<div class="dashboard-note-autocomplete-item"><div class="item-content">No results found</div></div>';
            dashboardAutocompletePortal.style.display = 'block';
            dashboardAutocompletePortal.style.visibility = 'visible';
            dashboardAutocompletePortal.style.opacity = '1';
            return;
        }
        
        let html = '';
        items.forEach((item, index) => {
            const icon = item.type === 'patient' ? 'bi-person' : (item.type === 'appointment' ? 'bi-calendar-event' : 'bi-capsule');
            html += `
                <div class="dashboard-note-autocomplete-item ${index === dashboardSelectedAutocompleteIndex ? 'selected' : ''}" 
                     data-index="${index}"
                     onclick="event.stopPropagation(); dashboardSelectAutocompleteItem(${JSON.stringify(item).replace(/"/g, '&quot;')}); return false;">
                    <i class="bi ${icon} item-icon"></i>
                    <div class="item-content">
                        <div class="item-title">${escapeHtml(item.title)}</div>
                        ${item.subtitle ? `<div class="item-subtitle">${escapeHtml(item.subtitle)}</div>` : ''}
                    </div>
                </div>
            `;
        });
        
        dashboardAutocompletePortal.innerHTML = html;
        dashboardAutocompletePortal.style.display = 'block';
        dashboardAutocompletePortal.style.visibility = 'visible';
        dashboardAutocompletePortal.style.opacity = '1';
    }
    
    function dashboardUpdateAutocompleteSelection() {
        if (!dashboardAutocompletePortal) return;
        
        const items = dashboardAutocompletePortal.querySelectorAll('.dashboard-note-autocomplete-item');
        items.forEach((item, index) => {
            if (index === dashboardSelectedAutocompleteIndex) {
                item.classList.add('selected');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('selected');
            }
        });
    }
    
    function dashboardSelectAutocompleteItem(item) {
        if (!dashboardAutocompleteTextarea || !item || !dashboardAutocompleteCursorPosition) {
            dashboardHideAutocomplete();
            return;
        }
        
        const contentEditable = dashboardAutocompleteTextarea;
        const range = dashboardAutocompleteCursorPosition.range;
        const match = dashboardAutocompleteCursorPosition.match;
        
        // Hide autocomplete immediately to prevent any delays
        dashboardHideAutocomplete();
        
        if (match && range) {
            range.setStart(range.startContainer, range.startOffset - match[0].length);
            range.deleteContents();
            
            let replacement = null;
            if (item.type === 'patient') {
                replacement = document.createElement('a');
                replacement.href = `/doctor/patients/${item.id}`;
                // Use standard class name (without dashboard- prefix) for compatibility
                replacement.className = 'note-content-link';
                replacement.target = '_blank';
                replacement.setAttribute('data-type', 'patient');
                replacement.setAttribute('data-id', item.id);
                replacement.innerHTML = `<i class="bi bi-person patient-icon"></i>${escapeHtml(item.title)}`;
            } else if (item.type === 'appointment') {
                replacement = document.createElement('a');
                replacement.href = `/doctor/appointments/${item.id}`;
                // Use standard class name (without dashboard- prefix) for compatibility
                replacement.className = 'note-content-appointment-link';
                replacement.target = '_blank';
                replacement.setAttribute('data-type', 'appointment');
                replacement.setAttribute('data-id', item.id);
                replacement.innerHTML = `<i class="bi bi-calendar-event appointment-icon"></i>#${item.id}`;
            } else if (item.type === 'drug') {
                replacement = document.createElement('span');
                // Use standard class name (without dashboard- prefix) for compatibility
                replacement.className = 'note-content-drug-badge';
                replacement.setAttribute('data-type', 'drug');
                replacement.setAttribute('data-id', item.id);
                replacement.innerHTML = `<i class="bi bi-capsule drug-icon"></i>${escapeHtml(item.title)}`;
                // Add click event to show drug popover
                replacement.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dashboardShowDrugPopover(item.id, item.title, e);
                });
            }
            
            if (replacement) {
                range.insertNode(replacement);
                
                const spaceAfter = document.createTextNode(' ');
                range.setStartAfter(replacement);
                range.insertNode(spaceAfter);
                
                const newRange = document.createRange();
                newRange.setStartAfter(spaceAfter);
                newRange.collapse(true);
                
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(newRange);
                
                setTimeout(() => {
                    contentEditable.focus();
                    
                    const finalRange = document.createRange();
                    try {
                        finalRange.setStartAfter(spaceAfter);
                        finalRange.collapse(true);
                    } catch (e) {
                        const endTextNode = document.createTextNode(' ');
                        contentEditable.appendChild(endTextNode);
                        finalRange.setStartAfter(endTextNode);
                        finalRange.collapse(true);
                    }
                    
                    const finalSelection = window.getSelection();
                    finalSelection.removeAllRanges();
                    finalSelection.addRange(finalRange);
                    contentEditable.focus();
                    
                    setTimeout(() => {
                        const checkRange = finalSelection.getRangeAt(0);
                        let checkNode = checkRange.startContainer;
                        while (checkNode && checkNode !== contentEditable) {
                            if (checkNode === replacement) {
                                const parent = replacement.parentNode;
                                if (parent) {
                                    const newTextNode = document.createTextNode(' ');
                                    parent.insertBefore(newTextNode, replacement.nextSibling);
                                    const newRange = document.createRange();
                                    newRange.setStartAfter(newTextNode);
                                    newRange.collapse(true);
                                    finalSelection.removeAllRanges();
                                    finalSelection.addRange(newRange);
                                    contentEditable.focus();
                                }
                                break;
                            }
                            checkNode = checkNode.parentNode;
                        }
                    }, 50);
                }, 200);
                
                const noteId = contentEditable.getAttribute('data-note-id');
                if (noteId) {
                    dashboardUpdateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                }
            }
        }
        
        contentEditable.focus();
    }
    
    function dashboardHideAutocomplete() {
        if (dashboardAutocompletePortal) {
            // Use multiple methods to ensure hiding
            dashboardAutocompletePortal.style.display = 'none';
            dashboardAutocompletePortal.style.visibility = 'hidden';
            dashboardAutocompletePortal.style.opacity = '0';
            dashboardAutocompletePortal.style.pointerEvents = 'none';
            dashboardAutocompletePortal.classList.add('hidden');
        }
        
        // Remove mouse tracking handler
        if (dashboardAutocompleteUpdateHandler) {
            document.removeEventListener('mousemove', dashboardAutocompleteUpdateHandler);
            dashboardAutocompleteUpdateHandler = null;
        }
        
        // Remove click and mousedown outside handlers
        document.removeEventListener('click', dashboardHandleClickOutside, true);
        document.removeEventListener('mousedown', dashboardHandleClickOutside, true);
        
        dashboardCurrentAutocompleteType = null;
        dashboardCurrentAutocompleteQuery = '';
        dashboardCurrentAutocompleteItems = [];
        dashboardSelectedAutocompleteIndex = -1;
        dashboardAutocompleteTextarea = null;
    }
    
    // Drug Popover Functions
    let dashboardCurrentDrugPopover = null;
    
    async function dashboardShowDrugPopover(drugId, drugName, event) {
        // Close existing popover if any
        if (dashboardCurrentDrugPopover) {
            dashboardCloseDrugPopover();
        }
        
        // Create popover element
        const popover = document.createElement('div');
        popover.className = 'note-drug-popover';
        popover.id = 'dashboardNoteDrugPopover';
        
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'note-drug-popover-backdrop';
        backdrop.addEventListener('click', dashboardCloseDrugPopover);
        
        // Position popover in center of viewport
        popover.style.position = 'fixed';
        popover.style.left = '50%';
        popover.style.top = '50%';
        popover.style.transform = 'translate(-50%, -50%)';
        popover.style.zIndex = '10000000';
        popover.style.maxWidth = '600px';
        popover.style.width = '90%';
        popover.style.maxHeight = '80vh';
        popover.style.overflowY = 'auto';
        
        // Show loading state
        popover.innerHTML = `
            <div class="note-drug-popover-header">
                <h5 class="note-drug-popover-title">${escapeHtml(drugName)}</h5>
                <button type="button" class="note-drug-popover-close" onclick="dashboardCloseDrugPopover()" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="note-drug-popover-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(backdrop);
        document.body.appendChild(popover);
        dashboardCurrentDrugPopover = popover;
        
        try {
            // Fetch drug details
            const response = await fetch(`/api/getDrugDetails?id=${drugId}`);
            const data = await response.json();
            
            if (data.drug) {
                const drug = data.drug;
                popover.innerHTML = `
                    <div class="note-drug-popover-header">
                        <h5 class="note-drug-popover-title">${escapeHtml(drug.drug_name || drugName)}</h5>
                        <button type="button" class="note-drug-popover-close" onclick="dashboardCloseDrugPopover()" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="note-drug-popover-body">
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">Active Ingredient</h6>
                            <p class="mb-0">${escapeHtml(drug.active_ingredient || 'N/A')}</p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <h6 class="text-primary mb-1">Company</h6>
                                <p class="mb-0">${escapeHtml(drug.Company || 'N/A')}</p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <h6 class="text-primary mb-1">Category</h6>
                                <p class="mb-0">${escapeHtml(drug.category || 'N/A')}</p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <h6 class="text-primary mb-1">Price</h6>
                                <p class="text-success fw-bold mb-0">${drug.price ? 'EGP ' + escapeHtml(drug.price) : 'N/A'}</p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <h6 class="text-primary mb-1">Route</h6>
                                <p class="mb-0">${escapeHtml(drug.administration_route || 'N/A')}</p>
                            </div>
                        </div>
                        
                        ${drug.GI ? `
                            <div class="mb-3">
                                <h6 class="text-primary mb-2">General Information</h6>
                                <p class="mb-0" style="line-height: 1.6;">${escapeHtml(drug.GI)}</p>
                            </div>
                        ` : ''}
                        
                        ${drug.SRDE ? `
                            <div>
                                <h6 class="text-primary mb-2">Additional Information</h6>
                                <p class="mb-0" style="line-height: 1.6;">${escapeHtml(drug.SRDE)}</p>
                            </div>
                        ` : ''}
                    </div>
                `;
            } else {
                popover.innerHTML = `
                    <div class="note-drug-popover-header">
                        <h5 class="note-drug-popover-title">${escapeHtml(drugName)}</h5>
                        <button type="button" class="note-drug-popover-close" onclick="dashboardCloseDrugPopover()" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="note-drug-popover-body">
                        <div class="text-center py-4">
                            <p class="mb-0">Drug information not available</p>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error fetching drug details:', error);
            popover.innerHTML = `
                <div class="note-drug-popover-header">
                    <h5 class="note-drug-popover-title">${escapeHtml(drugName)}</h5>
                    <button type="button" class="note-drug-popover-close" onclick="dashboardCloseDrugPopover()" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="note-drug-popover-body">
                    <div class="text-center py-4">
                        <p class="text-danger mb-0">Error loading drug information</p>
                    </div>
                </div>
            `;
        }
    }
    
    function dashboardCloseDrugPopover() {
        if (dashboardCurrentDrugPopover) {
            dashboardCurrentDrugPopover.remove();
            dashboardCurrentDrugPopover = null;
        }
        const backdrop = document.querySelector('.note-drug-popover-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
    
    // Initialize drug badge click handlers
    function dashboardInitDrugBadges() {
        document.querySelectorAll('.note-content-drug-badge').forEach(badge => {
            const drugId = badge.getAttribute('data-id');
            const drugName = badge.textContent.trim();
            
            badge.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (drugId) {
                    dashboardShowDrugPopover(parseInt(drugId), drugName, e);
                }
            });
        });
    }
    
    // Make functions global
    window.dashboardStartDrag = dashboardStartDrag;
    window.dashboardStartResize = dashboardStartResize;
    window.dashboardToggleColorPicker = dashboardToggleColorPicker;
    window.dashboardChangeNoteColor = dashboardChangeNoteColor;
    window.dashboardBringToFront = dashboardBringToFront;
    window.dashboardFitToSize = dashboardFitToSize;
    window.dashboardDeleteNote = dashboardDeleteNote;
    window.dashboardUpdateNoteTitle = dashboardUpdateNoteTitle;
    window.dashboardUpdateNoteContent = dashboardUpdateNoteContent;
    window.dashboardAddNote = dashboardAddNote;
    window.dashboardSelectAutocompleteItem = dashboardSelectAutocompleteItem;
    window.dashboardShowDrugPopover = dashboardShowDrugPopover;
    window.dashboardCloseDrugPopover = dashboardCloseDrugPopover;
    window.dashboardInitDrugBadges = dashboardInitDrugBadges;
});

// Wait for Chart.js to load
document.addEventListener('DOMContentLoaded', function() {
    function initCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initCharts, 100);
            return;
        }

        // Chart.js Configuration for Dashboard
        const chartColors = {
            primary: '#6366F1',   // Indigo (design-system)
            success: '#10B981',   // Emerald
            danger: '#EF4444',    // Red
            warning: '#F59E0B',   // Amber
            info: '#0EA5E9',      // Sky
            secondary: '#64748B', // Slate
            male: '#3B82F6',      // Blue
            female: '#EC4899'     // Pink
        };

        // hex (#rrggbb) → rgba string with the given alpha.
        function chartHexToRgba(hex, alpha) {
            const h = (hex || '#6366F1').replace('#', '');
            const r = parseInt(h.substring(0, 2), 16);
            const g = parseInt(h.substring(2, 4), 16);
            const b = parseInt(h.substring(4, 6), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        // Scriptable backgroundColor: soft vertical gradient fading the line
        // colour from a tinted top to near-transparent bottom. Returns a flat
        // tint until Chart.js has computed the chart area.
        function chartAreaGradient(hex, topAlpha = 0.34, bottomAlpha = 0.01) {
            return function (context) {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) return chartHexToRgba(hex, topAlpha * 0.4);
                const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                g.addColorStop(0, chartHexToRgba(hex, topAlpha));
                g.addColorStop(1, chartHexToRgba(hex, bottomAlpha));
                return g;
            };
        }
        window.chartAreaGradient = chartAreaGradient;
        window.chartHexToRgba = chartHexToRgba;

        // Get current theme colors dynamically
        function getCurrentThemeColors() {
            const isDark = document.documentElement.classList.contains('dark');
            
            if (isDark) {
                return {
                    text: '#F1F5F9',
                    muted: '#94A3B8',
                    grid: 'rgba(148, 163, 184, 0.16)',
                    border: 'rgba(99, 102, 241, 0.35)',
                    background: '#0C111F',
                    tooltipBg: 'rgba(12, 17, 31, 0.96)',
                    tooltipText: '#F1F5F9'
                };
            } else {
                return {
                    text: '#0f172a',
                    muted: '#64748B',
                    grid: 'rgba(15, 23, 42, 0.07)',
                    border: 'rgba(99, 102, 241, 0.30)',
                    background: '#ffffff',
                    tooltipBg: 'rgba(255, 255, 255, 0.96)',
                    tooltipText: '#0f172a'
                };
            }
        }

        // Chart.js default configuration
        if (Chart.defaults && Chart.defaults.font) {
            Chart.defaults.font.family = "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        }
        
        // Update Chart.js defaults based on theme
        function updateChartDefaults() {
            const themeColors = getCurrentThemeColors();
            if (Chart.defaults) {
                Chart.defaults.color = themeColors.text;
            }
        }
        
        // Initialize defaults
        updateChartDefaults();

        // Make functions and variables global
        window.chartColors = chartColors;
        window.getCurrentThemeColors = getCurrentThemeColors;
        
        // Define chart functions
        window.getCommonOptions = function() {
            const themeColors = getCurrentThemeColors();
            const fontFamily = "'Plus Jakarta Sans', 'Cairo', 'Segoe UI', Tahoma, sans-serif";
            return {
                responsive: true,
                maintainAspectRatio: false,
                // Smooth index-mode hover: the whole vertical slice lights up.
                interaction: { mode: 'index', intersect: false },
                animation: { duration: 800, easing: 'easeOutQuart' },
                layout: { padding: { top: 6, right: 6, bottom: 0, left: 0 } },
                // Premium line/point defaults — clean lines, points appear on hover.
                elements: {
                    line: { borderWidth: 3, tension: 0.4, borderCapStyle: 'round', borderJoinStyle: 'round' },
                    point: {
                        radius: 0,
                        hoverRadius: 6,
                        hoverBorderWidth: 3,
                        hitRadius: 14,
                        hoverBackgroundColor: themeColors.background
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 18,
                            font: { size: 12, weight: '600', family: fontFamily },
                            color: themeColors.text
                        }
                    },
                    tooltip: {
                        backgroundColor: themeColors.tooltipBg,
                        titleColor: themeColors.tooltipText,
                        bodyColor: themeColors.tooltipText,
                        borderColor: themeColors.border,
                        borderWidth: 1,
                        cornerRadius: 12,
                        padding: 12,
                        caretSize: 6,
                        usePointStyle: true,
                        boxPadding: 6,
                        displayColors: true,
                        titleFont: { family: fontFamily, size: 13, weight: 'bold' },
                        bodyFont: { family: fontFamily, size: 12 }
                    }
                },
                scales: {
                    x: {
                        border: { display: false },
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            color: themeColors.muted,
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8,
                            padding: 8,
                            font: { size: 11, family: fontFamily }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: {
                            color: themeColors.grid,
                            drawBorder: false,
                            drawTicks: false,
                            lineWidth: 1,
                            borderDash: [4, 5]
                        },
                        ticks: {
                            color: themeColors.muted,
                            padding: 10,
                            maxTicksLimit: 6,
                            font: { size: 11, family: fontFamily }
                        }
                    }
                }
            };
        };

        window.getPieOptions = function() {
            const themeColors = getCurrentThemeColors();
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: themeColors.text
                        }
                    },
                    tooltip: {
                        backgroundColor: themeColors.tooltipBg,
                        titleColor: themeColors.tooltipText,
                        bodyColor: themeColors.tooltipText,
                        borderColor: themeColors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        titleFont: {
                            family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            };
        };

        window.loadChartsData = function() {
            fetch('/api/dashboard-charts')
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        renderAppointmentsChart(data.data.trend);
                        
                        // Render New Patients Trend Chart
                        if (data.data.patients && data.data.patients.length > 0) {
                            renderNewPatientsTrendChart(data.data.patients);
                        }
                        
                        // Wait for amCharts to load before rendering gender chart with multiple retries
                        let retryCount = 0;
                        const maxRetries = 10;
                        const checkAmCharts = setInterval(() => {
                            if (typeof am4core !== 'undefined' && typeof am4charts !== 'undefined' && typeof am4themes_animated !== 'undefined') {
                                clearInterval(checkAmCharts);
                                renderGenderPieChart(data.data.gender);
                            } else {
                                retryCount++;
                                if (retryCount >= maxRetries) {
                                    clearInterval(checkAmCharts);
                                    console.error('amCharts library failed to load after multiple retries');
                                }
                            }
                        }, 200);
                        
                        renderLiquidCircles(data.data.status);
                    } else {
                        console.error('Error loading charts data:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error loading charts data:', error);
                });
        };
        
        // New Patients Trend Chart function
        window.renderNewPatientsTrendChart = function(patientsData) {
            const ctx = document.getElementById('newPatientsTrendChart');
            if (!ctx || !patientsData || patientsData.length === 0) return;
            
            // Destroy existing chart if it exists
            if (window.chartInstances.newPatientsTrendChart) {
                window.chartInstances.newPatientsTrendChart.destroy();
            }
            
            const isDark = document.documentElement.classList.contains('dark');
            const themeColors = getCurrentThemeColors();
            
            const dates = patientsData.map(item => item.date);
            const totalPatients = patientsData.map(item => parseInt(item.new_patients || 0));
            const malePatients = patientsData.map(item => parseInt(item.male || 0));
            const femalePatients = patientsData.map(item => parseInt(item.female || 0));
            
            // Use same options as appointmentsChart for consistent height
            const chartOptions = getCommonOptions();
            // Override legend position and colors for this specific chart
            chartOptions.plugins.legend.position = 'top';
            chartOptions.plugins.legend.labels.color = themeColors.text;
            
            window.chartInstances.newPatientsTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric' 
                    })),
                    datasets: [
                        {
                            label: 'Total Patients',
                            data: totalPatients,
                            borderColor: chartColors.primary, // Indigo
                            backgroundColor: chartAreaGradient(chartColors.primary, 0.30),
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: chartColors.primary,
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3
                        },
                        {
                            label: 'Male',
                            data: malePatients,
                            borderColor: chartColors.male,
                            backgroundColor: chartAreaGradient(chartColors.male, 0.12),
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: chartColors.male,
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2.5
                        },
                        {
                            label: 'Female',
                            data: femalePatients,
                            borderColor: chartColors.female,
                            backgroundColor: chartAreaGradient(chartColors.female, 0.12),
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: chartColors.female,
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2.5
                        }
                    ]
                },
                options: chartOptions
            });
        };
        
        window.renderGenderPieChart = function(genderData) {
            const chartDiv = document.getElementById('genderPieChart');
            if (!chartDiv || !genderData) return;
            
            // Destroy existing chart if it exists
            if (window.chartInstances.genderPieChart) {
                window.chartInstances.genderPieChart.dispose();
                window.chartInstances.genderPieChart = null;
            }
            
            const totalMale = genderData.total_male || 0;
            const totalFemale = genderData.total_female || 0;
            
            if (totalMale === 0 && totalFemale === 0) {
                chartDiv.innerHTML = '<p class="text-muted text-center py-3">No data available</p>';
                return;
            }
            
            // Check if amCharts is loaded
            if (typeof am4core === 'undefined' || typeof am4charts === 'undefined') {
                console.error('amCharts library not loaded');
                return;
            }
            
            // Use animated theme if available
            if (typeof am4themes_animated !== 'undefined') {
                am4core.useTheme(am4themes_animated);
            }
            
            // Create 3D Pie Chart
            const chart = am4core.create("genderPieChart", am4charts.PieChart3D);
            chart.hiddenState.properties.opacity = 0; // Initial fade-in
            
            // Set data
            chart.data = [
                {
                    gender: "Male",
                    count: totalMale
                },
                {
                    gender: "Female",
                    count: totalFemale
                }
            ];
            
            // Configure chart - smaller size
            chart.innerRadius = am4core.percent(50);
            chart.depth = 60;
            chart.radius = am4core.percent(70); // Make chart smaller
            
            // Hide chart title/watermark
            chart.logo.disabled = true;
            chart.copyright = undefined;
            
            // Remove legend
            chart.legend = new am4charts.Legend();
            chart.legend.disabled = true;
            
            // Create series
            const series = chart.series.push(new am4charts.PieSeries3D());
            series.dataFields.value = "count";
            series.dataFields.depthValue = "count";
            series.dataFields.category = "gender";
            series.slices.template.cornerRadius = 5;
            series.colors.step = 3;
            
            // Set colors - hot pink for females, dodgerblue for males
            const isDark = document.documentElement.classList.contains('dark');
            const maleColor = am4core.color("#1E90FF"); // Dodgerblue
            const femaleColor = am4core.color("#FF1493"); // Hot pink (#FF1493)
            
            series.colors.list = [
                maleColor,
                femaleColor
            ];
            
            // Configure labels - black in light mode, white in dark mode
            const labelColor = isDark ? "#ffffff" : "#000000";
            series.labels.template.fill = am4core.color(labelColor);
            series.labels.template.fontSize = 14;
            series.labels.template.fontWeight = "bold";
            
            // Configure tooltips
            series.tooltip.getFillFromObject = false;
            series.tooltip.background.fill = am4core.color("#000000");
            series.tooltip.background.fillOpacity = 0.9;
            series.tooltip.label.fill = am4core.color("#ffffff");
            series.tooltip.label.fontSize = 12;
            
            // Store chart instance
            window.chartInstances.genderPieChart = chart;
            
            // Hide amCharts watermark/title SVG element
            setTimeout(() => {
                const svgTitle = chartDiv.querySelector('title');
                if (svgTitle) {
                    svgTitle.style.display = 'none';
                    svgTitle.setAttribute('aria-hidden', 'true');
                }
                // Also hide any g elements with opacity="0.4" that contain the watermark
                const watermarkElements = chartDiv.querySelectorAll('g[opacity="0.4"]');
                watermarkElements.forEach(el => {
                    const title = el.querySelector('title');
                    if (title && title.textContent.includes('amCharts')) {
                        el.style.display = 'none';
                        el.setAttribute('aria-hidden', 'true');
                    }
                });
            }, 100);
        };
        
        window.renderLiquidCircles = function(statusData) {
            if (!statusData) {
                initFluidMeter('completed', 0);
                initFluidMeter('missed', 0);
                return;
            }
            
            // Use completion_ratio and missed_ratio directly from API (same as reports.js)
            // If ratios are not available, calculate from completed/missed and total
            let completedPercent = 0;
            let missedPercent = 0;
            
            if (statusData.completion_ratio !== undefined && statusData.completion_ratio !== null) {
                completedPercent = Math.round(parseFloat(statusData.completion_ratio) || 0);
            } else {
                // Fallback: calculate from completed and total
                const total = parseInt(statusData.total_appointments) || 0;
                const completed = parseInt(statusData.completed) || 0;
                if (total > 0) {
                    completedPercent = Math.round((completed / total) * 100);
                }
            }
            
            if (statusData.missed_ratio !== undefined && statusData.missed_ratio !== null) {
                missedPercent = Math.round(parseFloat(statusData.missed_ratio) || 0);
            } else {
                // Fallback: calculate from missed and total
                const total = parseInt(statusData.total_appointments) || 0;
                const missed = parseInt(statusData.missed) || 0;
                if (total > 0) {
                    missedPercent = Math.round((missed / total) * 100);
                }
            }
            
            // Initialize FluidMeter for both circles
            initFluidMeter('completed', completedPercent);
            initFluidMeter('missed', missedPercent);
        };
        
        // FluidMeter implementation
        function initFluidMeter(type, percentage) {
            const canvasId = type === 'completed' ? 'completedFluidMeter' : 'missedFluidMeter';
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            
            const isDark = document.documentElement.classList.contains('dark');
            const size = 150; // Smaller size
            const borderWidth = 15;
            const centerX = size / 2;
            const centerY = size / 2;
            const radius = (size - borderWidth * 2) / 2;
            
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');
            
            // Colors based on type and theme
            let backgroundColor, foregroundColor, foregroundFluidColor, backgroundFluidColor;
            
            if (type === 'completed') {
                backgroundColor = isDark ? '#1e293b' : '#e2e2e2';
                foregroundColor = isDark ? '#0f172a' : '#fafafa';
                foregroundFluidColor = '#10b981'; // Green
                backgroundFluidColor = '#34d399'; // Lighter green
            } else {
                backgroundColor = isDark ? '#1e293b' : '#e2e2e2';
                foregroundColor = isDark ? '#0f172a' : '#fafafa';
                foregroundFluidColor = '#ef4444'; // Red
                backgroundFluidColor = '#f87171'; // Lighter red
            }
            
            let animationFrame = 0;
            let bubbles = [];
            
            // Create bubbles
            for (let i = 0; i < 5; i++) {
                bubbles.push({
                    x: Math.random() * size,
                    y: Math.random() * size,
                    radius: Math.random() * 3 + 1,
                    speed: Math.random() * 0.5 + 0.2
                });
            }
            
            function draw() {
                ctx.clearRect(0, 0, size, size);
                
                // Draw background circle
                ctx.beginPath();
                ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
                ctx.fillStyle = backgroundColor;
                ctx.fill();
                ctx.strokeStyle = foregroundColor;
                ctx.lineWidth = borderWidth;
                ctx.stroke();
                
                // Calculate fill height
                const fillHeight = (percentage / 100) * (radius * 2);
                const fillY = centerY + radius - fillHeight;
                
                if (percentage > 0) {
                    // Create clipping region for fluid
                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, radius - borderWidth / 2, 0, Math.PI * 2);
                    ctx.clip();
                    
                    // Draw fluid layers with wave effect
                    const time = animationFrame * 0.05;
                    const waveAmplitude = 5;
                    const waveFrequency = 0.02;
                    
                    // Background fluid layer
                    ctx.fillStyle = backgroundFluidColor;
                    ctx.beginPath();
                    ctx.moveTo(0, fillY);
                    for (let x = 0; x <= size; x += 2) {
                        const y = fillY + Math.sin((x * waveFrequency) + (time * 150)) * waveAmplitude;
                        ctx.lineTo(x, y);
                    }
                    ctx.lineTo(size, size);
                    ctx.lineTo(0, size);
                    ctx.closePath();
                    ctx.fill();
                    
                    // Foreground fluid layer
                    ctx.fillStyle = foregroundFluidColor;
                    ctx.beginPath();
                    ctx.moveTo(0, fillY);
                    for (let x = 0; x <= size; x += 2) {
                        const y = fillY + Math.sin((x * waveFrequency) + (time * -100)) * (waveAmplitude * 0.8);
                        ctx.lineTo(x, y);
                    }
                    ctx.lineTo(size, size);
                    ctx.lineTo(0, size);
                    ctx.closePath();
                    ctx.fill();
                    
                    // Draw bubbles
                    bubbles.forEach(bubble => {
                        bubble.y -= bubble.speed;
                        if (bubble.y < fillY) {
                            bubble.y = size;
                            bubble.x = Math.random() * size;
                        }
                        
                        if (bubble.y > fillY && bubble.y < size) {
                            ctx.beginPath();
                            ctx.arc(bubble.x, bubble.y, bubble.radius, 0, Math.PI * 2);
                            ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';
                            ctx.fill();
                        }
                    });
                    
                    ctx.restore();
                }
                
                // Draw percentage text
                ctx.fillStyle = isDark ? '#ffffff' : '#0f172a';
                const fontSize = size * 0.25; // Responsive font size
                ctx.font = `bold ${fontSize}px Arial`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(percentage + '%', centerX, centerY);
                
                animationFrame++;
                requestAnimationFrame(draw);
            }
            
            draw();
        }
        
        function updateLiquidCircle(type, percentage) {
            const container = document.getElementById(type === 'completed' ? 'completedCircleContainer' : 'missedCircleContainer');
            if (!container) {
                console.warn(`Container not found for type: ${type}`);
                return;
            }
            
            // Ensure percentage is a valid number
            percentage = Math.max(0, Math.min(100, parseFloat(percentage) || 0));
            
            // Determine wave class based on percentage
            let waveClass = '_0';
            if (percentage > 0 && percentage <= 25) {
                waveClass = '_25';
            } else if (percentage > 25 && percentage <= 50) {
                waveClass = '_50';
            } else if (percentage > 50 && percentage <= 75) {
                waveClass = '_75';
            } else if (percentage > 75) {
                waveClass = '_100';
            }
            
            // Update wave elements
            const waves = container.querySelectorAll('.wave');
            const waveBelow = container.querySelector('.wave-below');
            const desc = container.querySelector('.desc');
            
            if (waves.length === 0) {
                console.warn(`No wave elements found in container for type: ${type}`);
                return;
            }
            
            waves.forEach(wave => {
                // Remove all wave classes and add the new one
                wave.className = wave.className.replace(/\s*_\d+/g, '');
                wave.className += ' ' + waveClass;
            });
            
            if (waveBelow) {
                // Remove all wave-below classes and add the new one
                waveBelow.className = waveBelow.className.replace(/\s*_\d+/g, '');
                waveBelow.className += ' ' + waveClass;
            }
            
            if (desc) {
                // Update desc class for text color
                desc.className = desc.className.replace(/\s*_\d+/g, '');
                desc.className += ' ' + waveClass;
            }
        }

        // Store chart instances for theme updates
        window.chartInstances = {
            appointmentsChart: null,
            genderPieChart: null,
            newPatientsTrendChart: null
        };
        
        window.renderAppointmentsChart = function(trendData) {
            const ctx = document.getElementById('appointmentsChart');
            if (!ctx || !trendData || trendData.length === 0) return;
            
            // Destroy existing chart if it exists
            if (window.chartInstances.appointmentsChart) {
                window.chartInstances.appointmentsChart.destroy();
            }
            
            const dates = trendData.map(item => item.date);
            const totalAppointments = trendData.map(item => item.total_appointments || 0);
            const completed = trendData.map(item => item.completed || 0);
            const missed = trendData.map(item => item.missed || 0);
            
            window.chartInstances.appointmentsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates.map(date => new Date(date).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric' 
                    })),
                    datasets: [
                        {
                            label: 'Total Appointments',
                            data: totalAppointments,
                            borderColor: chartColors.primary,
                            backgroundColor: chartAreaGradient(chartColors.primary, 0.34),
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: chartColors.primary,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Completed',
                            data: completed,
                            borderColor: chartColors.success,
                            backgroundColor: chartAreaGradient(chartColors.success, 0.16),
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: chartColors.success,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Missed',
                            data: missed,
                            borderColor: chartColors.danger,
                            backgroundColor: chartAreaGradient(chartColors.danger, 0.14),
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: chartColors.danger,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: getCommonOptions()
            });
        };

        window.renderAppointmentsPieChart = function(statusData) {
            const ctx = document.getElementById('appointmentsPieChart');
            if (!ctx || !statusData) return;
            
            // Destroy existing chart if it exists
            if (window.chartInstances.appointmentsPieChart) {
                window.chartInstances.appointmentsPieChart.destroy();
            }
            
            const totalCompleted = statusData.completed || 0;
            const totalMissed = statusData.missed || 0;
            
            if (totalCompleted === 0 && totalMissed === 0) {
                ctx.parentElement.innerHTML = '<p class="text-muted text-center py-3">No data available</p>';
                return;
            }
            
            const themeColors = getCurrentThemeColors();
            
            window.chartInstances.appointmentsPieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Missed'],
                    datasets: [{
                        data: [totalCompleted, totalMissed],
                        backgroundColor: [
                            chartColors.success,
                            '#ef4444'
                        ],
                        borderWidth: 2,
                        borderColor: themeColors.background
                    }]
                },
                options: getPieOptions()
            });
        };
        
        // Function to update charts when theme changes
        window.updateChartsTheme = function() {
            updateChartDefaults();
            
            // Update chart container background
            const chartContainers = document.querySelectorAll('.chart-container');
            const themeColors = getCurrentThemeColors();
            chartContainers.forEach(container => {
                container.style.backgroundColor = themeColors.background;
            });
            
            // Reload charts data to redraw with new theme
            if (window.chartInstances.appointmentsChart) {
                loadChartsData();
            }
            
            // Update New Patients Trend Chart theme
            if (window.chartInstances.newPatientsTrendChart) {
                loadChartsData(); // Reload to update theme
            }
            
            // Update amCharts theme if needed
            if (window.chartInstances.genderPieChart) {
                const isDark = document.documentElement.classList.contains('dark');
                const maleColor = am4core.color("#1E90FF"); // Dodgerblue
                const femaleColor = am4core.color("#FF1493"); // Hot pink
                const labelColor = isDark ? "#ffffff" : "#000000";
                
                if (window.chartInstances.genderPieChart.series && window.chartInstances.genderPieChart.series.length > 0) {
                    const series = window.chartInstances.genderPieChart.series.getIndex(0);
                    series.colors.list = [
                        maleColor,
                        femaleColor
                    ];
                    series.labels.template.fill = am4core.color(labelColor);
                }
            }
        };
        
        // Load charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                loadChartsData();
                setupThemeListener();
            });
        } else {
            loadChartsData();
            setupThemeListener();
        }
        
        // Setup theme change listener
        function setupThemeListener() {
            // Listen for theme toggle button click
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    // Wait a bit for theme class to be applied
                    setTimeout(() => {
                        updateChartsTheme();
                    }, 100);
                });
            }
            
            // Also listen for class changes on documentElement (for programmatic theme changes)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        setTimeout(() => {
                            updateChartsTheme();
                        }, 100);
                    }
                });
            });
            
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        }
    }
    
    initCharts();
});

// Custom Select Menu Logic for Dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeCustomSelects();
});

function initializeCustomSelects() {
    const fields = document.querySelectorAll('.field.menu');
    
    fields.forEach(field => {
        const select = field.querySelector('select');
        const button = field.querySelector('.custom-select-toggle');
        const menu = field.querySelector('menu');
        const options = menu.querySelectorAll('li');

        // Validate all elements exist before proceeding
        if (!select || !button || !menu || !options || options.length === 0) {
            console.warn('Custom select menu elements not found or incomplete', field);
            return;
        }

        // Functions scoped to each menu instance
        function toggleMenu() {
            if (field.classList.contains('open')) {
                closeMenu();
            } else {
                // Close other open menus first
                document.querySelectorAll('.field.menu.open').forEach(openField => {
                    if (openField !== field) {
                        openField.classList.remove('open');
                        const openBtn = openField.querySelector('.custom-select-toggle');
                        if(openBtn) openBtn.setAttribute('aria-expanded', 'false');
                        // Reset z-index for closed menus
                        const parent = openField.closest('.d-flex, .card-header, .col-12, .card');
                        if (parent) {
                            setTimeout(() => {
                                if (!openField.classList.contains('open')) {
                                    parent.style.zIndex = '';
                                    parent.style.position = ''; 
                                }
                            }, 300);
                        }
                    }
                });
                openMenu();
            }
        }

        function openMenu() {
            field.classList.add('open');
            button.setAttribute('aria-expanded', 'true');
            
            // Fix z-index issue by elevating parent containers manually
            // This is a fallback/reinforcement for the CSS :has() selector
            const parent = field.closest('.d-flex, .card-header, .col-12, .card');
            if (parent) {
                parent.style.zIndex = '1000002'; // Match CSS value
                parent.style.position = 'relative'; 
            }

            // Focus first selected or first option
            const selected = menu.querySelector('.selected') || options[0];
            if (selected) selected.focus();
        }

        function closeMenu() {
            field.classList.remove('open');
            button.setAttribute('aria-expanded', 'false');
            button.focus();
            
            // Reset parent z-index with a slight delay
            const parent = field.closest('.d-flex, .card-header, .col-12, .card');
            if (parent) {
                setTimeout(() => {
                    if (!field.classList.contains('open')) {
                        parent.style.zIndex = '';
                        parent.style.position = ''; 
                    }
                }, 300);
            }
        }

        function setOption(optionEl) {
            const value = optionEl.dataset.option;
            const text = optionEl.querySelector('h3').textContent;
            
            // Update hidden select
            select.value = value;
            
            // Manually trigger change event
            const event = new Event('change', { bubbles: true });
            select.dispatchEvent(event);
            
            // Update button text
            button.textContent = text;
            
            // Update UI classes
            options.forEach(el => el.classList.remove('selected'));
            optionEl.classList.add('selected');
            
            closeMenu();
        }

        // Event Listeners
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        button.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openMenu();
            }
        });

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
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
        
        // Initial set of button text based on select value
        const initialValue = select.value;
        const initialOption = menu.querySelector(`li[data-option="${initialValue}"]`);
        if (initialOption) {
            button.textContent = initialOption.querySelector('h3').textContent;
            options.forEach(el => el.classList.remove('selected'));
            initialOption.classList.add('selected');
        }
    });

    // Close on click outside (global listener)
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.field.menu')) {
            document.querySelectorAll('.field.menu.open').forEach(field => {
                field.classList.remove('open');
                const btn = field.querySelector('.custom-select-toggle');
                if(btn) btn.setAttribute('aria-expanded', 'false');
                
                // Reset z-index
                const parent = field.closest('.d-flex, .card-header, .col-12, .card');
                if (parent) {
                    setTimeout(() => {
                        if (!field.classList.contains('open')) {
                            parent.style.zIndex = '';
                            parent.style.position = ''; 
                        }
                    }, 300);
                }
            });
        }
    });
}

// ============================================
// Statistics Cards Charts
// ============================================

let statsChart2Instance = null;
let statsChart3Instance = null;
let statsChart4Instance = null;
let statsChart5Instance = null;

function initStatsCardsCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(initStatsCardsCharts, 100);
        return;
    }

    const chartOptions = {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                enabled: false,
            }
        },
        elements: {
            point: {
                radius: 0
            },
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                display: false
            },
            y: {
                grid: {
                    display: false
                },
                display: false
            }
        }
    };

    // Load real data for Completed and Missed charts
    loadStatsCardsData();
}

// Load real data for stats cards
function loadStatsCardsData() {
    fetch('/api/dashboard-charts')
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.data && data.data.trend) {
                const trendData = data.data.trend;
                const statusData = data.data.status;
                const patientsData = data.data.patients || [];
                const prescriptionsData = data.data.prescriptions || [];
                
                // Chart 2 - Completed (Success) - Last 30 days
                renderCompletedChart(trendData, statusData);
                
                // Chart 3 - Missed (Danger) - Last 30 days
                renderMissedChart(trendData, statusData);
                
                // Chart 4 - New Patients (Warning) - Last 30 days
                renderNewPatientsChart(patientsData);
                
                // Chart 5 - Total Prescriptions (Info) - Last 30 days
                renderTotalPrescriptionsChart(prescriptionsData);
            }
        })
        .catch(error => {
            console.error('Error loading stats cards data:', error);
        });
}

function renderCompletedChart(trendData, statusData) {
    // Note: Chart.js canvas is replaced with mini sparkline SVG
    // This function now updates the mini sparkline chart and trend badge
    const chartContainer = document.getElementById('chartCompletedAppointments');
    if (!chartContainer) return;
    
    // Extract completed data from trend
    const completedData = trendData.map(item => item.completed || 0);
    
    // Update mini sparkline chart with actual data
    if (completedData.length > 0) {
        chartContainer.innerHTML = generateSparklineSVG(completedData);
    }
    
    // Update completed value from status data
    if (statusData && statusData.completed !== undefined) {
        const completedValue = parseInt(statusData.completed) || 0;
        const completedValueElement = document.querySelector('.mini-stat-success .mini-stat-value');
        if (completedValueElement) {
            completedValueElement.textContent = completedValue;
        }
    }
    
    // Calculate completion ratio from status data (same as reports.js)
    if (statusData && statusData.completion_ratio !== undefined) {
        const completionRatio = parseFloat(statusData.completion_ratio) || 0;
        const changeElement = document.getElementById('completedChange');
        if (changeElement) {
            const spanElement = changeElement.querySelector('span');
            if (spanElement) {
                spanElement.textContent = `${completionRatio.toFixed(1)}%`;
            }
            // Update trend class
            changeElement.className = 'mini-stat-trend trend-up';
        }
    }
}

function renderMissedChart(trendData, statusData) {
    // Note: Chart.js canvas is replaced with mini sparkline SVG
    // This function now updates the mini sparkline chart and trend badge
    const chartContainer = document.getElementById('chartMissedAppointments');
    if (!chartContainer) return;
    
    // Extract missed data from trend
    const missedData = trendData.map(item => item.missed || 0);
    
    // Update mini sparkline chart with actual data
    if (missedData.length > 0) {
        chartContainer.innerHTML = generateSparklineSVG(missedData);
    }
    
    // Calculate missed ratio from status data (same as reports.js)
    if (statusData && statusData.missed_ratio !== undefined) {
        const missedRatio = parseFloat(statusData.missed_ratio) || 0;
        const changeElement = document.getElementById('missedChange');
        if (changeElement) {
            const spanElement = changeElement.querySelector('span');
            if (spanElement) {
                spanElement.textContent = `${missedRatio.toFixed(1)}%`;
            }
            // Update trend class
            changeElement.className = 'mini-stat-trend trend-down';
        }
    }
}

function renderNewPatientsChart(patientsData) {
    // Note: Chart.js canvas is replaced with mini sparkline SVG
    // This function now updates the mini sparkline chart and trend badge
    const newPatientsChartContainer = document.getElementById('chartNewPatients');
    if (!newPatientsChartContainer) return;
    
    // Extract new patients data
    const newPatientsData = patientsData.map(item => parseInt(item.new_patients || 0));
    
    // Calculate total and percentage change
    const totalNewPatients = newPatientsData.reduce((a, b) => a + b, 0);
    const valueElement = document.getElementById('newPatientsValue');
    if (valueElement) {
        valueElement.textContent = totalNewPatients;
    }
    
    // Calculate percentage change (comparing last 7 days average vs previous 7 days average)
    if (newPatientsData.length >= 14) {
        const last7Days = newPatientsData.slice(-7);
        const prev7Days = newPatientsData.slice(-14, -7);
        const last7Avg = last7Days.reduce((a, b) => a + b, 0) / 7;
        const prev7Avg = prev7Days.reduce((a, b) => a + b, 0) / 7;
        let percentage = 0;
        if (prev7Avg > 0) {
            percentage = ((last7Avg - prev7Avg) / prev7Avg) * 100;
        } else if (last7Avg > 0) {
            percentage = 100;
        }
        const changeElement = document.getElementById('newPatientsChange');
        if (changeElement) {
            const spanElement = changeElement.querySelector('span');
            if (spanElement) {
                spanElement.textContent = `${Math.abs(percentage).toFixed(1)}%`;
            }
            // Update trend class and icon
            changeElement.className = percentage >= 0 ? 'mini-stat-trend trend-up' : 'mini-stat-trend trend-down';
            const iconElement = changeElement.querySelector('i');
            if (iconElement) {
                iconElement.className = percentage >= 0 ? 'bi bi-graph-up-arrow' : 'bi bi-graph-down-arrow';
            }
        }
    } else if (newPatientsData.length >= 2) {
        // Fallback: compare first vs last
        const firstValue = newPatientsData[0] || 0;
        const lastValue = newPatientsData[newPatientsData.length - 1] || 0;
        let percentage = 0;
        if (firstValue > 0) {
            percentage = ((lastValue - firstValue) / firstValue) * 100;
        } else if (lastValue > 0) {
            percentage = 100;
        }
        const changeElement = document.getElementById('newPatientsChange');
        if (changeElement) {
            const spanElement = changeElement.querySelector('span');
            if (spanElement) {
                spanElement.textContent = `${Math.abs(percentage).toFixed(1)}%`;
            }
            // Update trend class and icon
            changeElement.className = percentage >= 0 ? 'mini-stat-trend trend-up' : 'mini-stat-trend trend-down';
            const iconElement = changeElement.querySelector('i');
            if (iconElement) {
                iconElement.className = percentage >= 0 ? 'bi bi-graph-up-arrow' : 'bi bi-graph-down-arrow';
            }
        }
    }
    
    // Update mini sparkline chart with actual data
    if (newPatientsData.length > 0) {
        newPatientsChartContainer.innerHTML = generateSparklineSVG(newPatientsData);
    }
}

function renderTotalPrescriptionsChart(prescriptionsData) {
    // Note: Chart.js canvas is replaced with mini sparkline SVG
    // This function now updates the mini sparkline chart and trend badge
    const prescriptionsChartContainer = document.getElementById('chartTotalPrescriptions');
    if (!prescriptionsChartContainer) return;
    
    // Extract total prescriptions data
    const totalPrescriptionsData = prescriptionsData.map(item => parseInt(item.total_prescriptions || 0));
    
    // Calculate total and percentage change
    const totalPrescriptions = totalPrescriptionsData.reduce((a, b) => a + b, 0);
    const valueElement = document.getElementById('totalPrescriptionsValue');
    if (valueElement) {
        valueElement.textContent = totalPrescriptions;
    }
    
    // Calculate percentage change (comparing last 7 days average vs previous 7 days average)
    if (totalPrescriptionsData.length >= 14) {
        const last7Days = totalPrescriptionsData.slice(-7);
        const prev7Days = totalPrescriptionsData.slice(-14, -7);
        const last7Avg = last7Days.reduce((a, b) => a + b, 0) / 7;
        const prev7Avg = prev7Days.reduce((a, b) => a + b, 0) / 7;
        let percentage = 0;
        if (prev7Avg > 0) {
            percentage = ((last7Avg - prev7Avg) / prev7Avg) * 100;
        } else if (last7Avg > 0) {
            percentage = 100;
        }
        const changeElement = document.getElementById('totalPrescriptionsChange');
        if (changeElement) {
            const spanElement = changeElement.querySelector('span');
            if (spanElement) {
                spanElement.textContent = `${Math.abs(percentage).toFixed(1)}%`;
            }
            // Update trend class and icon
            changeElement.className = percentage >= 0 ? 'mini-stat-trend trend-up' : 'mini-stat-trend trend-down';
            const iconElement = changeElement.querySelector('i');
            if (iconElement) {
                iconElement.className = percentage >= 0 ? 'bi bi-graph-up-arrow' : 'bi bi-graph-down-arrow';
            }
        }
    } else if (totalPrescriptionsData.length >= 2) {
        // Fallback: compare first vs last
        const firstValue = totalPrescriptionsData[0] || 0;
        const lastValue = totalPrescriptionsData[totalPrescriptionsData.length - 1] || 0;
        let percentage = 0;
        if (firstValue > 0) {
            percentage = ((lastValue - firstValue) / firstValue) * 100;
        } else if (lastValue > 0) {
            percentage = 100;
        }
        const changeElement = document.getElementById('totalPrescriptionsChange');
        if (changeElement) {
            const spanElement = changeElement.querySelector('span');
            if (spanElement) {
                spanElement.textContent = `${Math.abs(percentage).toFixed(1)}%`;
            }
            // Update trend class and icon
            changeElement.className = percentage >= 0 ? 'mini-stat-trend trend-up' : 'mini-stat-trend trend-down';
            const iconElement = changeElement.querySelector('i');
            if (iconElement) {
                iconElement.className = percentage >= 0 ? 'bi bi-graph-up-arrow' : 'bi bi-graph-down-arrow';
            }
        }
    }
    
    // Update mini sparkline chart with actual data
    if (totalPrescriptionsData.length > 0) {
        prescriptionsChartContainer.innerHTML = generateSparklineSVG(totalPrescriptionsData);
    }
}

// Initialize stats cards charts on page load
document.addEventListener('DOMContentLoaded', function() {
    initStatsCardsCharts();
    
    // Re-initialize charts on theme change
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                // Destroy existing charts
                if (statsChart2Instance) {
                    statsChart2Instance.destroy();
                    statsChart2Instance = null;
                }
                if (statsChart3Instance) {
                    statsChart3Instance.destroy();
                    statsChart3Instance = null;
                }
                if (statsChart4Instance) {
                    statsChart4Instance.destroy();
                    statsChart4Instance = null;
                }
                if (statsChart5Instance) {
                    statsChart5Instance.destroy();
                    statsChart5Instance = null;
                }
                // Re-initialize with new theme
                setTimeout(initStatsCardsCharts, 100);
            }
        });
    });
    
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});

// Hover effect with radial gradient - glowing effect following mouse
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.stats-card');
    const wrapper = document.querySelector('.stats-cards-wrapper');

    if (wrapper && cards.length > 0) {
        wrapper.addEventListener('mousemove', function (event) {
            cards.forEach((card) => {
                const cardContent = card.querySelector('.stats-card-content');
                if (!cardContent) return;
                
                const rect = cardContent.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                // Get card type and corresponding color
                let color = 'rgba(59, 248, 251, 0.3)';
                if (card.classList.contains('stats-card-primary')) {
                    color = 'rgba(14, 165, 233, 0.4)';
                } else if (card.classList.contains('stats-card-success')) {
                    color = 'rgba(16, 185, 129, 0.4)';
                } else if (card.classList.contains('stats-card-danger')) {
                    color = 'rgba(239, 68, 68, 0.4)';
                } else if (card.classList.contains('stats-card-warning')) {
                    color = 'rgba(245, 158, 11, 0.4)';
                } else if (card.classList.contains('stats-card-info')) {
                    color = 'rgba(54, 185, 204, 0.4)';
                }

                // Apply gradient to card-content, overlay on top of background-color
                // Use multiple backgrounds: gradient on top, solid color below
                cardContent.style.background = `radial-gradient(960px circle at ${x}px ${y}px, ${color}, transparent 15%), var(--card)`;
            });
        });
        
        // Reset background when mouse leaves wrapper
        wrapper.addEventListener('mouseleave', function() {
            cards.forEach((card) => {
                const cardContent = card.querySelector('.stats-card-content');
                if (cardContent) {
                    cardContent.style.background = '';
                }
            });
        });
    }
});

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
            
            // Format time text (show days if more than 24 hours)
            let timeValue = '';
            const secondsPerDay = 24 * 60 * 60; // 86400 seconds
            if (remainingSeconds >= secondsPerDay) {
                const days = Math.floor(remainingSeconds / secondsPerDay);
                const remainingAfterDays = remainingSeconds % secondsPerDay;
                const hours = Math.floor(remainingAfterDays / 3600);
                const minutes = Math.floor((remainingAfterDays % 3600) / 60);
                const seconds = remainingAfterDays % 60;
                timeValue = `${days} day${days !== 1 ? 's' : ''}, ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            } else if (remainingSeconds >= 3600) {
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

// Load Ophthalmology News
function loadOphthalmologyNews() {
    const ticker = document.getElementById('newsTicker');
    if (!ticker) return;
    
    ticker.innerHTML = '<span>Loading ophthalmology news...</span>';
    
    fetch('/api/ophthalmology-news')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success && data.articles && data.articles.length > 0) {
                    renderNewsTicker(data.articles);
                } else {
                    ticker.innerHTML = '<span>No ophthalmology news available</span>';
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', text);
                ticker.innerHTML = '<span>Unable to load news</span>';
            }
        })
        .catch(error => {
            console.error('Error loading news:', error);
            ticker.innerHTML = '<span>Unable to load news</span>';
        });
}

function renderNewsTicker(articles) {
    const ticker = document.getElementById('newsTicker');
    if (!ticker) return;

    // Local escaper — matches the file-level escapeHtml() helper but inlined
    // so this function stays self-contained if someone moves it.
    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    };

    ticker.innerHTML = '';
    articles.forEach(a => {
        const span = document.createElement('span');
        const parts = [];

        // Stacked badges. BREAKING and FDA are mutually exclusive (backend
        // already enforces this — is_fda is forced false when is_breaking
        // is true). TRIAL and NEW can stack on top. NEW is suppressed when
        // BREAKING is already firing — today's urgent news is by definition new.
        if (a.is_breaking) {
            parts.push('<span class="news-tag news-breaking"><i class="bi bi-exclamation-triangle-fill"></i> BREAKING</span>');
        } else if (a.is_fda) {
            parts.push('<span class="news-tag news-fda"><i class="bi bi-shield-check"></i> FDA</span>');
        }
        if (a.is_trial) {
            parts.push('<span class="news-tag news-trial"><i class="bi bi-clipboard2-pulse"></i> TRIAL</span>');
        }
        if (a.is_new && !a.is_breaking) {
            parts.push('<span class="news-tag news-new"><i class="bi bi-stars"></i> NEW</span>');
        }

        // Source pill — icon (server-controlled emoji or <i> from the feed list)
        // + short source name.
        const sourceName = a.source_name || a.source;
        if (sourceName) {
            const iconHtml = a.source_icon || '<i class="bi bi-rss"></i>';
            parts.push('<span class="news-source-pill" title="' + esc(sourceName) + '">'
                       + iconHtml + ' ' + esc(sourceName) + '</span>');
        }

        const href = (typeof a.link === 'string' && /^https?:\/\//i.test(a.link))
                   ? a.link : '#';
        parts.push('<a class="news-title" href="' + esc(href)
                   + '" target="_blank" rel="noopener noreferrer">'
                   + esc(a.title) + '</a>');

        span.innerHTML = parts.join('');
        ticker.appendChild(span);
    });

    // Restart the marquee so length changes don't desync the loop.
    ticker.style.animation = 'none';
    setTimeout(() => { ticker.style.animation = 'tickerMove 100s linear infinite'; }, 10);
}

// Initialize news on page load
document.addEventListener('DOMContentLoaded', function() {
    loadOphthalmologyNews();

    // Refresh news every 30 minutes
    setInterval(loadOphthalmologyNews, 30 * 60 * 1000);
});

// ============================================
// Weather & Allergy Index Card
// ============================================

// Weather condition to icon mapping
const weatherIconMap = {
    'clear': 'sun',
    'sunny': 'sun',
    'partly cloudy': 'partly-cloudy',
    'partly sunny': 'partly-cloudy',
    'mostly cloudy': 'cloud',
    'cloudy': 'cloud',
    'overcast': 'cloud',
    'fog': 'mist',
    'mist': 'mist',
    'haze': 'mist',
    'rain': 'rain',
    'light rain': 'rain',
    'heavy rain': 'rain',
    'showers': 'rain',
    'drizzle': 'rain',
    'thunderstorm': 'thunder',
    'thunder': 'thunder',
    'storm': 'thunder',
    'snow': 'snow',
    'light snow': 'snow',
    'heavy snow': 'snow',
    'sleet': 'snow',
    'windy': 'wind',
    'breezy': 'wind'
};

// Check if it's currently nighttime
function isNightTime() {
    const hour = new Date().getHours();
    return hour < 6 || hour >= 19; // Night is 7 PM to 6 AM
}

// Get weather icon type from condition
function getWeatherIconType(condition) {
    const lowerCondition = condition.toLowerCase();
    const isNight = isNightTime();

    for (const [key, value] of Object.entries(weatherIconMap)) {
        if (lowerCondition.includes(key)) {
            // Return night variants for various conditions
            if (isNight) {
                if (value === 'sun') return 'moon';
                if (value === 'partly-cloudy') return 'partly-cloudy-night';
                if (value === 'rain') return 'rain-night';
                if (value === 'snow') return 'snow-night';
            }
            return value;
        }
    }
    // Default to moon at night, sun during day
    return isNight ? 'moon' : 'sun';
}

// Render weather icon HTML using SVG animations
function renderWeatherIcon(iconType) {
    const icons = {
        'sun': `
            <svg class="weather-svg-icon icon-sunny" viewBox="0 0 220 220">
                <g class="sunny-short-ray">
                    <path fill="#EDC951" d="M111.961,65.447l-0.014-8.394c-0.003-1.617-1.318-2.927-2.935-2.925c-1.616,0.003-2.924,1.318-2.924,2.935l0.014,8.474C108.064,65.375,110.021,65.354,111.961,65.447z"/>
                    <path fill="#EDC951" d="M75.396,81.343c1.257-1.484,2.607-2.9,4.063-4.221l-5.938-5.918c-1.146-1.142-3-1.14-4.143,0.005c-1.142,1.146-1.139,3.001,0.008,4.142L75.396,81.343z"/>
                    <path fill="#EDC951" d="M163.276,112.648c0.388-0.001,0.756-0.078,1.094-0.213c1.074-0.437,1.83-1.492,1.83-2.721c-0.004-1.617-1.315-2.927-2.933-2.925l-8.478,0.015c0.164,1.96,0.186,3.917,0.091,5.856L163.276,112.648z"/>
                    <path fill="#EDC951" d="M143.207,80.158l5.918-5.937c1.144-1.146,1.14-3-0.005-4.142c-1.147-1.143-3.001-1.14-4.143,0.004l-5.992,6.013C140.471,77.353,141.884,78.704,143.207,80.158z"/>
                    <path fill="#EDC951" d="M56.353,108.382c-1.619,0.002-2.928,1.317-2.924,2.935c0.004,1.615,1.318,2.925,2.934,2.923l8.473-0.014c-0.16-1.963-0.182-3.917-0.088-5.858L56.353,108.382z"/>
                    <path fill="#EDC951" d="M144.234,139.686c-1.258,1.484-2.609,2.899-4.063,4.223l5.939,5.918c0.857,0.855,2.111,1.068,3.167,0.639c0.354-0.143,0.687-0.357,0.974-0.646c1.143-1.145,1.139-3-0.006-4.141L144.234,139.686z"/>
                    <path fill="#EDC951" d="M107.669,155.582l0.013,8.395c0.003,1.617,1.317,2.928,2.934,2.922c0.388,0,0.755-0.074,1.093-0.213c1.077-0.434,1.834-1.488,1.83-2.719l-0.014-8.475C111.564,155.654,109.608,155.676,107.669,155.582z"/>
                    <path fill="#EDC951" d="M76.421,140.871l-5.917,5.938c-1.142,1.144-1.141,2.999,0.006,4.142c0.857,0.855,2.112,1.068,3.17,0.641c0.354-0.144,0.687-0.361,0.972-0.646l5.991-6.012C79.159,143.676,77.743,142.326,76.421,140.871z"/>
                </g>
                <g class="sunny-long-ray">
                    <path fill="#EDC951" d="M138.495,51.723c0.936-2.209-0.096-4.761-2.307-5.697c-2.211-0.938-4.763,0.096-5.697,2.306l-7.959,18.792c-0.014,0.034-0.021,0.07-0.035,0.103c2.787,0.818,5.487,1.9,8.064,3.232L138.495,51.723z"/>
                    <path fill="#EDC951" d="M88.124,70.841c0.014,0.031,0.035,0.058,0.051,0.091c1.508-0.822,3.072-1.576,4.703-2.238c1.087-0.44,2.184-0.82,3.283-1.17l-7.639-18.862c-0.901-2.226-3.436-3.298-5.662-2.397c-2.223,0.901-3.299,3.435-2.395,5.66L88.124,70.841z"/>
                    <path fill="#EDC951" d="M47.633,89.838l18.79,7.959c0.033,0.012,0.07,0.021,0.104,0.032c0.818-2.786,1.901-5.485,3.234-8.061l-18.74-7.935c-2.209-0.937-4.761,0.098-5.696,2.308C44.388,86.354,45.423,88.904,47.633,89.838z"/>
                    <path fill="#EDC951" d="M149.397,88.874c0.821,1.508,1.576,3.074,2.236,4.705c0.439,1.088,0.821,2.183,1.171,3.284l18.862-7.638c2.226-0.902,3.299-3.437,2.398-5.661c-0.901-2.224-3.437-3.299-5.661-2.398l-18.916,7.66C149.458,88.837,149.43,88.859,149.397,88.874z"/>
                    <path fill="#EDC951" d="M81.135,169.308c-0.937,2.21,0.097,4.761,2.308,5.696c1.105,0.469,2.295,0.445,3.324,0.027c1.034-0.418,1.905-1.229,2.371-2.334l7.959-18.789c0.016-0.035,0.023-0.071,0.037-0.104c-2.787-0.818-5.488-1.901-8.065-3.233L81.135,169.308z"/>
                    <path fill="#EDC951" d="M131.503,150.19c-0.012-0.033-0.031-0.062-0.047-0.093c-1.508,0.822-3.074,1.574-4.704,2.238c-1.089,0.439-2.185,0.82-3.284,1.17l7.639,18.863c0.901,2.225,3.436,3.297,5.662,2.395c2.223-0.901,3.297-3.434,2.397-5.659L131.503,150.19z"/>
                    <path fill="#EDC951" d="M70.233,132.157c-0.824-1.51-1.578-3.074-2.238-4.707c-0.441-1.085-0.821-2.183-1.171-3.282l-18.862,7.641c-2.225,0.899-3.297,3.436-2.396,5.658c0.9,2.227,3.435,3.299,5.66,2.398l18.914-7.66C70.173,132.191,70.2,132.172,70.233,132.157z"/>
                    <path fill="#EDC951" d="M171.997,131.191l-18.791-7.959c-0.033-0.014-0.068-0.02-0.104-0.033c-0.818,2.786-1.9,5.484-3.234,8.062l18.739,7.936c1.104,0.467,2.295,0.443,3.327,0.025c1.029-0.417,1.902-1.228,2.371-2.334C175.24,134.678,174.207,132.127,171.997,131.191z"/>
                </g>
                <g class="sunny-body">
                    <path fill="#EDC951" d="M142.702,97.196c-7.357-18.162-28.043-26.923-46.205-19.568c-18.164,7.356-26.925,28.045-19.568,46.205c7.354,18.165,28.043,26.926,46.205,19.569C141.298,136.045,150.058,115.36,142.702,97.196z M117.348,84.979c-0.411,1.812-2.217,2.948-4.026,2.535c-4.427-1.007-8.997-0.636-13.221,1.075c-5.488,2.224-9.782,6.45-12.091,11.9c-2.308,5.452-2.356,11.475-0.134,16.964c0.697,1.721-0.134,3.684-1.857,4.381c-0.413,0.168-0.841,0.248-1.262,0.248c-1.33,0-2.588-0.795-3.117-2.104c-2.898-7.154-2.836-15.008,0.174-22.113c3.007-7.108,8.605-12.619,15.76-15.516c5.504-2.229,11.469-2.715,17.241-1.398C116.626,81.363,117.762,83.167,117.348,84.979z"/>
                </g>
            </svg>`,
        'partly-cloudy': `
            <svg class="weather-svg-icon icon-partly-cloudy" viewBox="0 0 220 220">
                <g class="sunny-short-ray">
                    <path fill="#EDC951" d="M147.961,63.447l-0.014-8.394c-0.003-1.617-1.318-2.927-2.935-2.925c-1.616,0.003-2.924,1.318-2.924,2.935l0.014,8.474C144.064,63.375,146.021,63.354,147.961,63.447z"/>
                    <path fill="#EDC951" d="M111.396,79.343c1.257-1.484,2.607-2.9,4.063-4.221l-5.938-5.918c-1.146-1.142-3-1.14-4.143,0.005c-1.142,1.146-1.139,3.001,0.008,4.142L111.396,79.343z"/>
                    <path fill="#EDC951" d="M199.276,110.648c0.388-0.001,0.756-0.078,1.094-0.213c1.074-0.437,1.83-1.492,1.83-2.721c-0.004-1.617-1.315-2.927-2.933-2.925l-8.478,0.015c0.164,1.96,0.186,3.917,0.091,5.856L199.276,110.648z"/>
                    <path fill="#EDC951" d="M179.207,78.158l5.918-5.937c1.144-1.146,1.14-3-0.005-4.142c-1.147-1.143-3.001-1.14-4.143,0.004l-5.992,6.013C176.471,75.353,177.884,76.704,179.207,78.158z"/>
                </g>
                <g class="sunny-long-ray">
                    <path fill="#EDC951" d="M174.495,49.723c0.936-2.209-0.096-4.761-2.307-5.697c-2.211-0.938-4.763,0.096-5.697,2.306l-7.959,18.792c-0.014,0.034-0.021,0.07-0.035,0.103c2.787,0.818,5.487,1.9,8.064,3.232L174.495,49.723z"/>
                    <path fill="#EDC951" d="M124.124,68.841c0.014,0.031,0.035,0.058,0.051,0.091c1.508-0.822,3.072-1.576,4.703-2.238c1.087-0.44,2.184-0.82,3.283-1.17l-7.639-18.862c-0.901-2.226-3.436-3.298-5.662-2.397c-2.223,0.901-3.299,3.435-2.395,5.66L124.124,68.841z"/>
                    <path fill="#EDC951" d="M207.997,129.191l-18.791-7.959c-0.033-0.014-0.068-0.02-0.104-0.033c-0.818,2.786-1.9,5.484-3.234,8.062l18.739,7.936c1.104,0.467,2.295,0.443,3.327,0.025c1.029-0.417,1.902-1.228,2.371-2.334C211.24,132.678,210.207,130.127,207.997,129.191z"/>
                </g>
                <g class="sunny-body">
                    <path fill="#EDC951" d="M178.702,95.196c-7.357-18.162-28.043-26.923-46.205-19.568c-18.164,7.356-26.925,28.045-19.568,46.205c7.354,18.165,28.043,26.926,46.205,19.569C177.298,134.045,186.058,113.36,178.702,95.196z M153.348,82.979c-0.411,1.812-2.217,2.948-4.026,2.535c-4.427-1.007-8.997-0.636-13.221,1.075c-5.488,2.224-9.782,6.45-12.091,11.9c-2.308,5.452-2.356,11.475-0.134,16.964c0.697,1.721-0.134,3.684-1.857,4.381c-0.413,0.168-0.841,0.248-1.262,0.248c-1.33,0-2.588-0.795-3.117-2.104c-2.898-7.154-2.836-15.008,0.174-22.113c3.007-7.108,8.605-12.619,15.76-15.516c5.504-2.229,11.469-2.715,17.241-1.398C152.626,79.363,153.762,81.167,153.348,82.979z"/>
                </g>
                <g class="cloud-offset">
                    <path fill="var(--weather-card-bg, #1a1d2e)" d="M113.903,179.264c-6.173,0-12.273-1.229-17.931-3.585c-6.062,2.515-12.218,3.585-19.999,3.585c-8.325,0-16.356-1.866-23.959-5.559c-5.329,2.711-11.262,4.119-17.492,4.119c-21.27,0-38.574-17.306-38.574-38.576c0-15.345,9.325-29.175,22.996-35.269c6.653-25.268,29.615-42.96,57.029-42.96c19.873,0,38.259,9.958,49.18,26.313c20.532,5.085,35.406,23.653,35.406,45.276C160.56,158.334,139.63,179.264,113.903,179.264z"/>
                </g>
                <g class="main-cloud">
                    <path fill="#00A0B0" d="M118.294,97.231c-8.359-15.388-24.715-25.212-42.32-25.212c-24.457,0-44.283,17.108-47.506,40.333c-12.301,2.767-21.52,13.771-21.52,26.896c0,15.205,12.369,27.576,27.574,27.576c6.396,0,12.348-2.078,17.133-5.917c7.713,4.944,15.705,7.356,24.318,7.356c8.145,0,13.68-1.295,20.043-4.816c5.418,3.152,11.541,4.816,17.887,4.816c19.662,0,35.656-15.996,35.656-35.656C149.56,114.432,135.888,99.401,118.294,97.231z"/>
                </g>
            </svg>`,
        'cloud': `
            <svg class="weather-svg-icon icon-cloudy" viewBox="0 0 220 220">
                <g class="small-cloud">
                    <path fill="#00A0B0" d="M69.054,67.463c-5.109-9.405-15.105-15.409-25.866-15.409c-14.947,0-27.066,10.456-29.036,24.651C6.634,78.396,1,85.121,1,93.143c0,9.293,7.561,16.854,16.853,16.854c3.911,0,7.547-1.27,10.472-3.617c4.715,3.022,9.6,4.497,14.864,4.497c4.978,0,8.361-0.792,12.25-2.944c3.312,1.927,7.053,2.944,10.932,2.944c12.016,0,21.792-9.776,21.792-21.792C88.162,77.976,79.807,68.789,69.054,67.463z"/>
                </g>
                <g class="cloud-offset">
                    <path fill="var(--weather-card-bg, #1a1d2e)" d="M113.903,179.264c-6.173,0-12.273-1.229-17.931-3.585c-6.062,2.515-12.218,3.585-19.999,3.585c-8.325,0-16.356-1.866-23.959-5.559c-5.329,2.711-11.262,4.119-17.492,4.119c-21.27,0-38.574-17.306-38.574-38.576c0-15.345,9.325-29.175,22.996-35.269c6.653-25.268,29.615-42.96,57.029-42.96c19.873,0,38.259,9.958,49.18,26.313c20.532,5.085,35.406,23.653,35.406,45.276C160.56,158.334,139.63,179.264,113.903,179.264z"/>
                </g>
                <g class="main-cloud">
                    <path fill="#00A0B0" d="M118.294,97.231c-8.359-15.388-24.715-25.212-42.32-25.212c-24.457,0-44.283,17.108-47.506,40.333c-12.301,2.767-21.52,13.771-21.52,26.896c0,15.205,12.369,27.576,27.574,27.576c6.396,0,12.348-2.078,17.133-5.917c7.713,4.944,15.705,7.356,24.318,7.356c8.145,0,13.68-1.295,20.043-4.816c5.418,3.152,11.541,4.816,17.887,4.816c19.662,0,35.656-15.996,35.656-35.656C149.56,114.432,135.888,99.401,118.294,97.231z"/>
                </g>
            </svg>`,
        'rain': `
            <svg class="weather-svg-icon icon-rainy" viewBox="0 0 220 220">
                <g class="rain-drops">
                    <path fill="#00A0B0" d="M69.942,143.08c-0.852,6.32-11.666,18.842-11.666,27.824c0,6.443,5.225,11.664,11.666,11.664c6.443,0,11.666-5.221,11.666-11.664C81.608,161.521,70.696,149.551,69.942,143.08z"/>
                    <path fill="#00A0B0" d="M110.126,143.08c-0.854,6.32-11.666,18.842-11.666,27.824c0,6.443,5.223,11.664,11.666,11.664s11.666-5.221,11.666-11.664C121.792,161.521,110.878,149.551,110.126,143.08z"/>
                    <path fill="#00A0B0" d="M150.308,143.08c-0.854,6.32-11.664,18.842-11.664,27.824c0,6.443,5.223,11.664,11.664,11.664c6.445,0,11.666-5.221,11.666-11.664C161.974,161.521,151.062,149.551,150.308,143.08z"/>
                </g>
                <g class="cloud-offset">
                    <path fill="var(--weather-card-bg, #1a1d2e)" d="M144.901,144.943c-6.173,0-12.273-1.229-17.932-3.586c-6.06,2.516-12.216,3.586-19.998,3.586c-8.323,0-16.355-1.867-23.959-5.56c-5.329,2.71-11.261,4.118-17.492,4.118c-21.27,0-38.574-17.305-38.574-38.575c0-15.344,9.324-29.174,22.996-35.267c6.651-25.269,29.613-42.961,57.03-42.961c19.872,0,38.257,9.958,49.177,26.311c20.533,5.087,35.409,23.656,35.409,45.277C191.558,124.014,170.628,144.943,144.901,144.943z"/>
                </g>
                <g class="rain-cloud">
                    <path fill="#666" d="M150.288,62.909c-8.357-15.386-24.713-25.209-42.316-25.209c-24.459,0-44.285,17.107-47.506,40.334c-12.301,2.766-21.52,13.77-21.52,26.894c0,15.204,12.369,27.575,27.574,27.575c6.396,0,12.348-2.076,17.133-5.916c7.713,4.943,15.701,7.357,24.318,7.357c8.145,0,13.682-1.295,20.041-4.818c5.42,3.154,11.541,4.818,17.889,4.818c19.66,0,35.656-15.996,35.656-35.656C181.558,80.111,167.886,65.081,150.288,62.909z"/>
                </g>
            </svg>`,
        'thunder': `
            <svg class="weather-svg-icon icon-rainy icon-thunder" viewBox="0 0 220 220">
                <g class="rain-drops">
                    <path fill="#00A0B0" d="M69.942,143.08c-0.852,6.32-11.666,18.842-11.666,27.824c0,6.443,5.225,11.664,11.666,11.664c6.443,0,11.666-5.221,11.666-11.664C81.608,161.521,70.696,149.551,69.942,143.08z"/>
                    <path fill="#00A0B0" d="M150.308,143.08c-0.854,6.32-11.664,18.842-11.664,27.824c0,6.443,5.223,11.664,11.664,11.664c6.445,0,11.666-5.221,11.666-11.664C161.974,161.521,151.062,149.551,150.308,143.08z"/>
                </g>
                <g class="lightning-bolt">
                    <path fill="#EDC951" d="M115,140l-8,25h12l-6,22l20-28h-14l10-19z"/>
                </g>
                <g class="cloud-offset">
                    <path fill="var(--weather-card-bg, #1a1d2e)" d="M144.901,144.943c-6.173,0-12.273-1.229-17.932-3.586c-6.06,2.516-12.216,3.586-19.998,3.586c-8.323,0-16.355-1.867-23.959-5.56c-5.329,2.71-11.261,4.118-17.492,4.118c-21.27,0-38.574-17.305-38.574-38.575c0-15.344,9.324-29.174,22.996-35.267c6.651-25.269,29.613-42.961,57.03-42.961c19.872,0,38.257,9.958,49.177,26.311c20.533,5.087,35.409,23.656,35.409,45.277C191.558,124.014,170.628,144.943,144.901,144.943z"/>
                </g>
                <g class="rain-cloud thunder-cloud">
                    <path fill="#555" d="M150.288,62.909c-8.357-15.386-24.713-25.209-42.316-25.209c-24.459,0-44.285,17.107-47.506,40.334c-12.301,2.766-21.52,13.77-21.52,26.894c0,15.204,12.369,27.575,27.574,27.575c6.396,0,12.348-2.076,17.133-5.916c7.713,4.943,15.701,7.357,24.318,7.357c8.145,0,13.682-1.295,20.041-4.818c5.42,3.154,11.541,4.818,17.889,4.818c19.66,0,35.656-15.996,35.656-35.656C181.558,80.111,167.886,65.081,150.288,62.909z"/>
                </g>
            </svg>`,
        'snow': `
            <svg class="weather-svg-icon icon-snowy" viewBox="0 0 220 220">
                <g class="snowflakes">
                    <path fill="#CCC" d="M84.535,166.239l-5.663,1.73l-3.644-2.104c0.089-0.392,0.141-0.798,0.141-1.218c0-0.418-0.052-0.824-0.141-1.216l3.645-2.104l5.662,1.729c0.156,0.048,0.314,0.071,0.47,0.071c0.688,0,1.324-0.445,1.536-1.138c0.26-0.849-0.218-1.747-1.067-2.006l-2.795-0.854l1.482-0.856c0.769-0.443,1.032-1.426,0.588-2.194s-1.426-1.032-2.195-0.589l-1.483,0.856l0.658-2.848c0.2-0.865-0.339-1.728-1.204-1.928c-0.865-0.2-1.728,0.339-1.927,1.204l-1.333,5.769l-3.648,2.106c-0.595-0.553-1.309-0.979-2.104-1.224v-4.204l4.33-4.039c0.649-0.605,0.685-1.621,0.079-2.271c-0.605-0.648-1.622-0.685-2.271-0.078l-2.138,1.993v-1.712c0-0.888-0.72-1.607-1.606-1.607c-0.888,0-1.607,0.72-1.607,1.607v1.712l-2.138-1.993c-0.648-0.606-1.666-0.57-2.271,0.078c-0.605,0.649-0.57,1.665,0.079,2.271l4.33,4.039v4.204c-0.795,0.245-1.509,0.67-2.104,1.224l-3.649-2.106l-1.332-5.77c-0.2-0.864-1.062-1.403-1.927-1.203c-0.865,0.199-1.403,1.063-1.204,1.927l0.658,2.849l-1.483-0.856c-0.769-0.443-1.752-0.18-2.195,0.589c-0.444,0.768-0.18,1.751,0.588,2.194l1.483,0.856l-2.796,0.854c-0.849,0.26-1.326,1.158-1.067,2.006c0.212,0.693,0.848,1.139,1.537,1.139c0.155,0,0.313-0.023,0.47-0.071l5.662-1.729l3.645,2.104c-0.09,0.393-0.142,0.798-0.142,1.217s0.052,0.825,0.142,1.218l-3.646,2.104l-5.662-1.73c-0.848-0.259-1.747,0.218-2.006,1.067c-0.259,0.849,0.219,1.746,1.067,2.006l2.796,0.854l-1.483,0.856c-0.769,0.443-1.032,1.427-0.588,2.195c0.298,0.515,0.838,0.804,1.393,0.804c0.273,0,0.549-0.07,0.802-0.216l1.483-0.856l-0.658,2.849c-0.2,0.864,0.339,1.728,1.204,1.927c0.121,0.028,0.243,0.042,0.362,0.042c0.731,0,1.393-0.503,1.564-1.245l1.333-5.769l3.649-2.107c0.595,0.553,1.31,0.979,2.104,1.224v4.204l-4.329,4.039c-0.649,0.604-0.685,1.622-0.079,2.271c0.605,0.649,1.623,0.685,2.271,0.079l2.137-1.994v1.712c0,0.888,0.72,1.607,1.606,1.607c0.887,0,1.607-0.72,1.607-1.607v-1.712l2.138,1.994c0.31,0.289,0.703,0.432,1.095,0.432c0.431,0,0.859-0.171,1.176-0.511c0.605-0.648,0.57-1.666-0.079-2.271l-4.33-4.039v-4.204c0.795-0.245,1.509-0.671,2.104-1.224l3.649,2.107l1.333,5.769c0.171,0.743,0.833,1.245,1.564,1.245c0.12,0,0.241-0.014,0.362-0.042c0.865-0.199,1.404-1.063,1.205-1.927l-0.658-2.849l1.482,0.856c0.253,0.146,0.529,0.216,0.802,0.216c0.556,0,1.096-0.288,1.393-0.804c0.444-0.769,0.181-1.751-0.588-2.194l-1.483-0.857l2.796-0.854c0.849-0.259,1.327-1.157,1.067-2.006C86.281,166.457,85.382,165.979,84.535,166.239z M69.906,167.54c-1.594,0-2.892-1.297-2.892-2.893c0-1.594,1.297-2.892,2.892-2.892c1.595,0,2.893,1.298,2.893,2.892C72.798,166.243,71.501,167.54,69.906,167.54z"/>
                    <path fill="#CCC" d="M123.582,166.239l-5.662,1.73l-3.645-2.104c0.09-0.392,0.142-0.798,0.142-1.218c0-0.418-0.052-0.824-0.142-1.216l3.645-2.104l5.662,1.729c0.156,0.048,0.314,0.071,0.471,0.071c0.688,0,1.324-0.445,1.535-1.138c0.26-0.849-0.218-1.747-1.066-2.006l-2.795-0.854l1.482-0.856c0.768-0.443,1.031-1.426,0.588-2.194s-1.426-1.032-2.195-0.589l-1.482,0.856l0.658-2.848c0.2-0.865-0.339-1.728-1.203-1.928c-0.865-0.2-1.729,0.339-1.928,1.204l-1.333,5.769l-3.648,2.106c-0.595-0.553-1.31-0.979-2.104-1.224v-4.204l4.33-4.039c0.648-0.605,0.685-1.621,0.078-2.271c-0.604-0.648-1.621-0.685-2.27-0.078l-2.138,1.993v-1.712c0-0.888-0.72-1.607-1.606-1.607c-0.888,0-1.607,0.72-1.607,1.607v1.712l-2.138-1.993c-0.648-0.606-1.666-0.57-2.271,0.078c-0.605,0.649-0.57,1.665,0.079,2.271l4.33,4.039v4.204c-0.795,0.245-1.509,0.67-2.104,1.224l-3.649-2.106l-1.332-5.77c-0.2-0.864-1.062-1.403-1.927-1.203c-0.865,0.199-1.403,1.063-1.204,1.927l0.658,2.849l-1.483-0.856c-0.769-0.443-1.752-0.18-2.195,0.589c-0.444,0.768-0.18,1.751,0.588,2.194l1.483,0.856l-2.796,0.854c-0.849,0.26-1.326,1.158-1.067,2.006c0.212,0.693,0.848,1.139,1.537,1.139c0.155,0,0.313-0.023,0.47-0.071l5.662-1.729l3.645,2.104c-0.09,0.393-0.142,0.798-0.142,1.217s0.052,0.825,0.142,1.218l-3.646,2.104l-5.662-1.73c-0.848-0.259-1.747,0.218-2.006,1.067c-0.259,0.849,0.219,1.746,1.067,2.006l2.796,0.854l-1.483,0.856c-0.769,0.443-1.032,1.427-0.588,2.195c0.298,0.515,0.838,0.804,1.393,0.804c0.273,0,0.549-0.07,0.802-0.216l1.483-0.856l-0.658,2.849c-0.2,0.864,0.339,1.728,1.204,1.927c0.121,0.028,0.243,0.042,0.362,0.042c0.731,0,1.393-0.503,1.564-1.245l1.333-5.769l3.649-2.107c0.595,0.553,1.31,0.979,2.104,1.224v4.204l-4.329,4.039c-0.649,0.604-0.685,1.622-0.079,2.271c0.605,0.649,1.623,0.685,2.271,0.079l2.137-1.994v1.712c0,0.888,0.72,1.607,1.606,1.607c0.887,0,1.607-0.72,1.607-1.607v-1.712l2.138,1.994c0.31,0.289,0.703,0.432,1.095,0.432c0.432,0,0.859-0.171,1.176-0.511c0.605-0.648,0.57-1.666-0.078-2.271l-4.33-4.039v-4.204c0.795-0.245,1.51-0.671,2.104-1.224l3.65,2.107l1.332,5.769c0.172,0.743,0.832,1.245,1.564,1.245c0.119,0,0.24-0.014,0.361-0.042c0.865-0.199,1.404-1.063,1.205-1.927l-0.658-2.849l1.482,0.856c0.254,0.146,0.529,0.216,0.802,0.216c0.556,0,1.097-0.288,1.394-0.804c0.443-0.769,0.18-1.751-0.588-2.194l-1.483-0.857l2.796-0.854c0.849-0.259,1.326-1.157,1.066-2.006C125.328,166.457,124.43,165.979,123.582,166.239z M108.954,167.54c-1.594,0-2.892-1.297-2.892-2.893c0-1.594,1.297-2.892,2.892-2.892c1.595,0,2.892,1.298,2.892,2.892C111.846,166.243,110.549,167.54,108.954,167.54z"/>
                    <path fill="#CCC" d="M162.632,166.239l-5.662,1.73l-3.645-2.104c0.09-0.392,0.142-0.798,0.142-1.218c0-0.418-0.052-0.824-0.142-1.216l3.645-2.104l5.662,1.729c0.156,0.048,0.314,0.071,0.471,0.071c0.688,0,1.324-0.445,1.535-1.138c0.26-0.849-0.218-1.747-1.066-2.006l-2.795-0.854l1.482-0.856c0.768-0.443,1.031-1.426,0.588-2.194s-1.426-1.032-2.195-0.589l-1.482,0.856l0.658-2.848c0.2-0.865-0.339-1.728-1.203-1.928c-0.865-0.2-1.729,0.339-1.928,1.204l-1.333,5.769l-3.648,2.106c-0.595-0.553-1.31-0.979-2.104-1.224v-4.204l4.329-4.039c0.648-0.605,0.685-1.621,0.078-2.271c-0.604-0.648-1.621-0.685-2.27-0.078l-2.138,1.993v-1.712c0-0.888-0.721-1.607-1.607-1.607s-1.606,0.72-1.606,1.607v1.712l-2.138-1.993c-0.648-0.606-1.666-0.57-2.271,0.078c-0.605,0.649-0.57,1.665,0.08,2.271l4.329,4.039v4.204c-0.795,0.245-1.509,0.67-2.104,1.224l-3.648-2.106l-1.332-5.77c-0.2-0.864-1.063-1.403-1.928-1.203c-0.865,0.199-1.403,1.063-1.203,1.927l0.658,2.849l-1.483-0.856c-0.769-0.443-1.752-0.18-2.195,0.589c-0.444,0.768-0.181,1.751,0.589,2.194l1.482,0.856l-2.796,0.854c-0.849,0.26-1.326,1.158-1.067,2.006c0.212,0.693,0.848,1.139,1.537,1.139c0.154,0,0.313-0.023,0.469-0.071l5.662-1.729l3.646,2.104c-0.09,0.393-0.142,0.798-0.142,1.217s0.052,0.825,0.142,1.218l-3.646,2.104l-5.662-1.73c-0.848-0.259-1.746,0.218-2.006,1.067c-0.259,0.849,0.219,1.746,1.067,2.006l2.796,0.854l-1.482,0.856c-0.77,0.443-1.033,1.427-0.589,2.195c0.298,0.515,0.838,0.804,1.394,0.804c0.272,0,0.549-0.07,0.802-0.216l1.483-0.856l-0.658,2.849c-0.201,0.864,0.338,1.728,1.203,1.927c0.121,0.028,0.243,0.042,0.362,0.042c0.731,0,1.394-0.503,1.564-1.245l1.333-5.769l3.648-2.107c0.595,0.553,1.31,0.979,2.104,1.224v4.204l-4.328,4.039c-0.65,0.604-0.686,1.622-0.08,2.271c0.605,0.649,1.623,0.685,2.271,0.079l2.137-1.994v1.712c0,0.888,0.721,1.607,1.607,1.607s1.606-0.72,1.606-1.607v-1.712l2.138,1.994c0.31,0.289,0.703,0.432,1.095,0.432c0.432,0,0.859-0.171,1.176-0.511c0.605-0.648,0.57-1.666-0.078-2.271l-4.33-4.039v-4.204c0.795-0.245,1.51-0.671,2.104-1.224l3.65,2.107l1.332,5.769c0.172,0.743,0.832,1.245,1.564,1.245c0.119,0,0.24-0.014,0.361-0.042c0.865-0.199,1.404-1.063,1.205-1.927l-0.658-2.849l1.482,0.856c0.254,0.146,0.529,0.216,0.802,0.216c0.556,0,1.097-0.288,1.394-0.804c0.443-0.769,0.18-1.751-0.588-2.194l-1.483-0.857l2.796-0.854c0.849-0.259,1.326-1.157,1.066-2.006C164.378,166.457,163.479,165.979,162.632,166.239z M148.004,167.54c-1.595,0-2.893-1.297-2.893-2.893c0-1.594,1.298-2.892,2.893-2.892s2.892,1.298,2.892,2.892C150.896,166.243,149.599,167.54,148.004,167.54z"/>
                </g>
                <g class="cloud-offset">
                    <path fill="var(--weather-card-bg, #1a1d2e)" d="M144.979,144.945c-6.177,0-12.277-1.229-17.934-3.585c-6.06,2.515-12.216,3.585-19.997,3.585c-8.326,0-16.357-1.866-23.96-5.56c-5.329,2.71-11.261,4.118-17.491,4.118c-21.271,0-38.576-17.305-38.576-38.575c0-15.344,9.325-29.173,22.996-35.267c6.651-25.269,29.614-42.96,57.032-42.96c19.87,0,38.255,9.958,49.176,26.31c20.533,5.087,35.41,23.656,35.41,45.278C191.635,124.016,170.705,144.945,144.979,144.945z"/>
                </g>
                <g class="snow-cloud">
                    <path fill="#CCC" d="M149.365,62.911c-8.359-15.386-24.712-25.209-42.316-25.209c-24.461,0-44.287,17.107-47.508,40.333c-12.299,2.766-21.52,13.77-21.52,26.894c0,15.206,12.369,27.575,27.576,27.575c6.395,0,12.346-2.076,17.133-5.916c7.713,4.945,15.701,7.357,24.318,7.357c8.141,0,13.678-1.293,20.041-4.818c5.419,3.156,11.542,4.818,17.89,4.818c19.658,0,35.655-15.994,35.655-35.656C180.635,80.114,166.961,65.083,149.365,62.911z"/>
                </g>
            </svg>`,
        'mist': `
            <svg class="weather-svg-icon icon-windy" viewBox="0 0 220 220">
                <g class="small-cloud">
                    <path fill="#00A0B0" d="M69.054,67.463c-5.109-9.405-15.105-15.409-25.866-15.409c-14.947,0-27.066,10.456-29.036,24.651C6.634,78.396,1,85.121,1,93.143c0,9.293,7.561,16.854,16.853,16.854c3.911,0,7.547-1.27,10.472-3.617c4.715,3.022,9.6,4.497,14.864,4.497c4.978,0,8.361-0.792,12.25-2.944c3.312,1.927,7.053,2.944,10.932,2.944c12.016,0,21.792-9.776,21.792-21.792C88.162,77.976,79.807,68.789,69.054,67.463z"/>
                </g>
                <g class="cloud-offset">
                    <path fill="var(--weather-card-bg, #1a1d2e)" d="M113.903,179.264c-6.173,0-12.273-1.229-17.931-3.585c-6.062,2.515-12.218,3.585-19.999,3.585c-8.325,0-16.356-1.866-23.959-5.559c-5.329,2.711-11.262,4.119-17.492,4.119c-21.27,0-38.574-17.306-38.574-38.576c0-15.345,9.325-29.175,22.996-35.269c6.653-25.268,29.615-42.96,57.029-42.96c19.873,0,38.259,9.958,49.18,26.313c20.532,5.085,35.406,23.653,35.406,45.276C160.56,158.334,139.63,179.264,113.903,179.264z"/>
                </g>
                <g class="main-cloud">
                    <path fill="#00A0B0" d="M118.294,97.231c-8.359-15.388-24.715-25.212-42.32-25.212c-24.457,0-44.283,17.108-47.506,40.333c-12.301,2.767-21.52,13.771-21.52,26.896c0,15.205,12.369,27.576,27.574,27.576c6.396,0,12.348-2.078,17.133-5.917c7.713,4.944,15.705,7.356,24.318,7.356c8.145,0,13.68-1.295,20.043-4.816c5.418,3.152,11.541,4.816,17.887,4.816c19.662,0,35.656-15.996,35.656-35.656C149.56,114.432,135.888,99.401,118.294,97.231z"/>
                </g>
                <g class="wind-string">
                    <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M85.263,105.176c3.002-1.646,6.403-2.549,9.903-2.549c11.375,0,20.633,9.256,20.633,20.633s-9.258,20.633-20.633,20.633H3.473"/>
                    <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M69.756,113.884c1.62-0.888,3.457-1.376,5.345-1.376c6.14,0,11.136,4.996,11.136,11.137c0,6.14-4.996,11.136-11.136,11.136H25.313"/>
                    <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M75.536,180.462c2.131,1.166,4.545,1.809,7.027,1.809c8.072,0,14.642-6.569,14.642-14.643s-6.569-14.643-14.642-14.643H18.043"/>
                </g>
            </svg>`,
        'wind': `
            <svg class="weather-svg-icon icon-windy" viewBox="0 0 220 220">
                <g class="small-cloud">
                    <path fill="#00A0B0" d="M69.054,67.463c-5.109-9.405-15.105-15.409-25.866-15.409c-14.947,0-27.066,10.456-29.036,24.651C6.634,78.396,1,85.121,1,93.143c0,9.293,7.561,16.854,16.853,16.854c3.911,0,7.547-1.27,10.472-3.617c4.715,3.022,9.6,4.497,14.864,4.497c4.978,0,8.361-0.792,12.25-2.944c3.312,1.927,7.053,2.944,10.932,2.944c12.016,0,21.792-9.776,21.792-21.792C88.162,77.976,79.807,68.789,69.054,67.463z"/>
                </g>
                <g class="cloud-offset">
                    <path fill="var(--weather-card-bg, #1a1d2e)" d="M113.903,179.264c-6.173,0-12.273-1.229-17.931-3.585c-6.062,2.515-12.218,3.585-19.999,3.585c-8.325,0-16.356-1.866-23.959-5.559c-5.329,2.711-11.262,4.119-17.492,4.119c-21.27,0-38.574-17.306-38.574-38.576c0-15.345,9.325-29.175,22.996-35.269c6.653-25.268,29.615-42.96,57.029-42.96c19.873,0,38.259,9.958,49.18,26.313c20.532,5.085,35.406,23.653,35.406,45.276C160.56,158.334,139.63,179.264,113.903,179.264z"/>
                </g>
                <g class="main-cloud">
                    <path fill="#00A0B0" d="M118.294,97.231c-8.359-15.388-24.715-25.212-42.32-25.212c-24.457,0-44.283,17.108-47.506,40.333c-12.301,2.767-21.52,13.771-21.52,26.896c0,15.205,12.369,27.576,27.574,27.576c6.396,0,12.348-2.078,17.133-5.917c7.713,4.944,15.705,7.356,24.318,7.356c8.145,0,13.68-1.295,20.043-4.816c5.418,3.152,11.541,4.816,17.887,4.816c19.662,0,35.656-15.996,35.656-35.656C149.56,114.432,135.888,99.401,118.294,97.231z"/>
                </g>
                <g class="wind-string">
                    <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M85.263,105.176c3.002-1.646,6.403-2.549,9.903-2.549c11.375,0,20.633,9.256,20.633,20.633s-9.258,20.633-20.633,20.633H3.473"/>
                    <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M69.756,113.884c1.62-0.888,3.457-1.376,5.345-1.376c6.14,0,11.136,4.996,11.136,11.137c0,6.14-4.996,11.136-11.136,11.136H25.313"/>
                    <path fill="none" stroke="#CCC" stroke-width="7" stroke-linecap="round" stroke-miterlimit="10" d="M75.536,180.462c2.131,1.166,4.545,1.809,7.027,1.809c8.072,0,14.642-6.569,14.642-14.643s-6.569-14.643-14.642-14.643H18.043"/>
                </g>
            </svg>`,
        'moon': `
            <svg class="weather-svg-icon icon-moon" viewBox="0 0 100 100">
                <defs>
                    <linearGradient id="moonGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#F5F3CE"/>
                        <stop offset="100%" style="stop-color:#E8E4B8"/>
                    </linearGradient>
                </defs>
                <g class="moon-stars">
                    <polygon fill="#F5F3CE" points="67,35 68.5,39.5 73,41 68.5,42.5 67,47 65.5,42.5 61,41 65.5,39.5" class="star star-1"/>
                    <polygon fill="#F5F3CE" points="86,42 87,45 90,46 87,47 86,50 85,47 82,46 85,45" class="star star-2"/>
                    <polygon fill="#F5F3CE" points="80,60 81,63 84,64 81,65 80,68 79,65 76,64 79,63" class="star star-3"/>
                </g>
                <g class="moon-body">
                    <path fill="url(#moonGrad)" d="M35,15 C15,15 0,35 0,55 C0,75 15,95 35,95 C55,95 70,80 70,60 C55,70 35,65 25,50 C20,40 25,25 40,18 C38,16 36,15 35,15 Z"/>
                    <circle fill="#D4D0A0" cx="25" cy="45" r="5" opacity="0.4"/>
                    <circle fill="#D4D0A0" cx="35" cy="65" r="3" opacity="0.3"/>
                    <circle fill="#D4D0A0" cx="20" cy="60" r="2" opacity="0.25"/>
                </g>
            </svg>`,
        'partly-cloudy-night': `
            <svg class="weather-svg-icon icon-partly-cloudy-night" viewBox="0 0 100 100">
                <defs>
                    <linearGradient id="moonGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#F5F3CE"/>
                        <stop offset="100%" style="stop-color:#E8E4B8"/>
                    </linearGradient>
                    <linearGradient id="cloudGradNight" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#00b4c6"/>
                        <stop offset="100%" style="stop-color:#00A0B0"/>
                    </linearGradient>
                    <linearGradient id="grayCloudGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#a0a0a0"/>
                        <stop offset="100%" style="stop-color:#808080"/>
                    </linearGradient>
                </defs>
                <g class="moon-stars">
                    <polygon fill="#F5F3CE" points="75,15 76.5,19.5 81,21 76.5,22.5 75,27 73.5,22.5 69,21 73.5,19.5" class="star star-1"/>
                    <polygon fill="#F5F3CE" points="90,25 91,28 94,29 91,30 90,33 89,30 86,29 89,28" class="star star-2"/>
                </g>
                <g class="moon-body">
                    <path fill="url(#moonGrad2)" d="M55,5 C40,5 28,18 28,33 C28,48 40,60 55,60 C60,60 65,58 68,55 C63,58 56,58 50,55 C40,50 35,38 40,28 C43,22 50,18 58,18 C64,18 70,22 72,28 C70,15 63,5 55,5 Z" transform="translate(5,-5)"/>
                </g>
                <g class="small-cloud-night">
                    <ellipse fill="url(#grayCloudGrad)" cx="25" cy="55" rx="18" ry="12"/>
                    <ellipse fill="url(#grayCloudGrad)" cx="40" cy="52" rx="15" ry="10"/>
                    <ellipse fill="url(#grayCloudGrad)" cx="32" cy="48" rx="12" ry="8"/>
                </g>
                <g class="main-cloud-night">
                    <ellipse fill="url(#cloudGradNight)" cx="45" cy="72" rx="25" ry="16"/>
                    <ellipse fill="url(#cloudGradNight)" cx="65" cy="68" rx="20" ry="14"/>
                    <ellipse fill="url(#cloudGradNight)" cx="55" cy="62" rx="18" ry="12"/>
                    <ellipse fill="url(#cloudGradNight)" cx="75" cy="75" rx="15" ry="10"/>
                </g>
            </svg>`,
        'rain-night': `
            <svg class="weather-svg-icon icon-rain-night" viewBox="0 0 100 100">
                <defs>
                    <linearGradient id="moonGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#F5F3CE"/>
                        <stop offset="100%" style="stop-color:#E8E4B8"/>
                    </linearGradient>
                    <linearGradient id="cloudGradRainNight" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#00b4c6"/>
                        <stop offset="100%" style="stop-color:#00A0B0"/>
                    </linearGradient>
                    <linearGradient id="grayCloudGrad2" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#888"/>
                        <stop offset="100%" style="stop-color:#666"/>
                    </linearGradient>
                </defs>
                <g class="moon-body">
                    <path fill="url(#moonGrad3)" d="M55,5 C40,5 28,18 28,33 C28,48 40,60 55,60 C60,60 65,58 68,55 C63,58 56,58 50,55 C40,50 35,38 40,28 C43,22 50,18 58,18 C64,18 70,22 72,28 C70,15 63,5 55,5 Z" transform="translate(5,-5)"/>
                </g>
                <g class="small-cloud-night">
                    <ellipse fill="url(#grayCloudGrad2)" cx="25" cy="45" rx="18" ry="12"/>
                    <ellipse fill="url(#grayCloudGrad2)" cx="40" cy="42" rx="15" ry="10"/>
                    <ellipse fill="url(#grayCloudGrad2)" cx="32" cy="38" rx="12" ry="8"/>
                </g>
                <g class="rain-drops-night">
                    <path fill="#00A0B0" d="M30,65 Q33,72 30,78 Q27,72 30,65 Z" class="drop drop-1"/>
                    <path fill="#00A0B0" d="M50,65 Q53,72 50,78 Q47,72 50,65 Z" class="drop drop-2"/>
                </g>
                <g class="main-cloud-night">
                    <ellipse fill="url(#cloudGradRainNight)" cx="45" cy="58" rx="25" ry="16"/>
                    <ellipse fill="url(#cloudGradRainNight)" cx="65" cy="54" rx="20" ry="14"/>
                    <ellipse fill="url(#cloudGradRainNight)" cx="55" cy="48" rx="18" ry="12"/>
                    <ellipse fill="url(#cloudGradRainNight)" cx="75" cy="60" rx="15" ry="10"/>
                </g>
            </svg>`,
        'snow-night': `
            <svg class="weather-svg-icon icon-snow-night" viewBox="0 0 100 100">
                <defs>
                    <linearGradient id="moonGrad4" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#F5F3CE"/>
                        <stop offset="100%" style="stop-color:#E8E4B8"/>
                    </linearGradient>
                    <linearGradient id="cloudGradSnowNight" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#00b4c6"/>
                        <stop offset="100%" style="stop-color:#00A0B0"/>
                    </linearGradient>
                    <linearGradient id="grayCloudGrad3" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#ccc"/>
                        <stop offset="100%" style="stop-color:#aaa"/>
                    </linearGradient>
                </defs>
                <g class="moon-body">
                    <path fill="url(#moonGrad4)" d="M55,5 C40,5 28,18 28,33 C28,48 40,60 55,60 C60,60 65,58 68,55 C63,58 56,58 50,55 C40,50 35,38 40,28 C43,22 50,18 58,18 C64,18 70,22 72,28 C70,15 63,5 55,5 Z" transform="translate(5,-5)"/>
                </g>
                <g class="small-cloud-night">
                    <ellipse fill="url(#grayCloudGrad3)" cx="25" cy="45" rx="18" ry="12"/>
                    <ellipse fill="url(#grayCloudGrad3)" cx="40" cy="42" rx="15" ry="10"/>
                    <ellipse fill="url(#grayCloudGrad3)" cx="32" cy="38" rx="12" ry="8"/>
                </g>
                <g class="snowflakes-night">
                    <g class="snowflake snowflake-1" transform="translate(35, 70)">
                        <line x1="0" y1="-5" x2="0" y2="5" stroke="white" stroke-width="1"/>
                        <line x1="-5" y1="0" x2="5" y2="0" stroke="white" stroke-width="1"/>
                        <line x1="-3.5" y1="-3.5" x2="3.5" y2="3.5" stroke="white" stroke-width="1"/>
                        <line x1="-3.5" y1="3.5" x2="3.5" y2="-3.5" stroke="white" stroke-width="1"/>
                    </g>
                    <g class="snowflake snowflake-2" transform="translate(50, 75)">
                        <line x1="0" y1="-4" x2="0" y2="4" stroke="white" stroke-width="1"/>
                        <line x1="-4" y1="0" x2="4" y2="0" stroke="white" stroke-width="1"/>
                        <line x1="-2.8" y1="-2.8" x2="2.8" y2="2.8" stroke="white" stroke-width="1"/>
                        <line x1="-2.8" y1="2.8" x2="2.8" y2="-2.8" stroke="white" stroke-width="1"/>
                    </g>
                    <g class="snowflake snowflake-3" transform="translate(63, 68)">
                        <line x1="0" y1="-4" x2="0" y2="4" stroke="white" stroke-width="1"/>
                        <line x1="-4" y1="0" x2="4" y2="0" stroke="white" stroke-width="1"/>
                        <line x1="-2.8" y1="-2.8" x2="2.8" y2="2.8" stroke="white" stroke-width="1"/>
                        <line x1="-2.8" y1="2.8" x2="2.8" y2="-2.8" stroke="white" stroke-width="1"/>
                    </g>
                </g>
                <g class="main-cloud-night">
                    <ellipse fill="url(#cloudGradSnowNight)" cx="45" cy="58" rx="25" ry="16"/>
                    <ellipse fill="url(#cloudGradSnowNight)" cx="65" cy="54" rx="20" ry="14"/>
                    <ellipse fill="url(#cloudGradSnowNight)" cx="55" cy="48" rx="18" ry="12"/>
                    <ellipse fill="url(#cloudGradSnowNight)" cx="75" cy="60" rx="15" ry="10"/>
                </g>
            </svg>`
    };
    return icons[iconType] || icons['sun'];
}

// Calculate pollen index based on weather conditions
function calculatePollenIndex(weatherData) {
    // Factors affecting pollen: temperature, humidity, wind, rain
    let pollenScore = 50; // Base score

    const temp = weatherData.temperature || 20;
    const humidity = weatherData.humidity || 50;
    const windSpeed = weatherData.windSpeed || 10;
    const isRaining = weatherData.condition?.toLowerCase().includes('rain');

    // Temperature factor (15-25°C is peak pollen)
    if (temp >= 15 && temp <= 25) {
        pollenScore += 20;
    } else if (temp > 25 && temp <= 30) {
        pollenScore += 10;
    } else if (temp < 10 || temp > 35) {
        pollenScore -= 20;
    }

    // Humidity factor (low humidity = more airborne pollen)
    if (humidity < 40) {
        pollenScore += 15;
    } else if (humidity > 70) {
        pollenScore -= 15;
    }

    // Wind factor (moderate wind spreads pollen)
    if (windSpeed >= 10 && windSpeed <= 25) {
        pollenScore += 15;
    } else if (windSpeed > 30) {
        pollenScore -= 10;
    }

    // Rain washes away pollen
    if (isRaining) {
        pollenScore -= 30;
    }

    return Math.max(0, Math.min(100, pollenScore));
}

// Calculate dry eye risk based on weather conditions
function calculateDryEyeRisk(weatherData) {
    let riskScore = 30; // Base score

    const temp = weatherData.temperature || 20;
    const humidity = weatherData.humidity || 50;
    const windSpeed = weatherData.windSpeed || 10;
    const uvIndex = weatherData.uvIndex || 5;

    // Low humidity increases dry eye risk significantly
    if (humidity < 30) {
        riskScore += 35;
    } else if (humidity < 45) {
        riskScore += 20;
    } else if (humidity > 60) {
        riskScore -= 15;
    }

    // High temperature with low humidity
    if (temp > 30 && humidity < 50) {
        riskScore += 15;
    }

    // Wind increases evaporation
    if (windSpeed > 20) {
        riskScore += 20;
    } else if (windSpeed > 10) {
        riskScore += 10;
    }

    // High UV exposure
    if (uvIndex > 7) {
        riskScore += 15;
    } else if (uvIndex > 5) {
        riskScore += 8;
    }

    return Math.max(0, Math.min(100, riskScore));
}

// Get level class based on score
function getLevelClass(score) {
    if (score <= 25) return 'low';
    if (score <= 50) return 'moderate';
    if (score <= 75) return 'high';
    return 'very-high';
}

// Get level text based on score
function getLevelText(score) {
    if (score <= 25) return 'Low';
    if (score <= 50) return 'Moderate';
    if (score <= 75) return 'High';
    return 'Very High';
}

// Update weather card UI
function updateWeatherCard(weatherData) {
    const widget = document.getElementById('weatherWidget');
    const iconContainer = document.getElementById('weatherIconContainer');
    const tempElement = document.getElementById('weatherTemp');
    const descElement = document.getElementById('weatherDesc');
    const dateElement = document.getElementById('weatherDate');
    const locationElement = document.getElementById('weatherLocation');
    const pollenValue = document.getElementById('pollenIndexValue');
    const pollenBar = document.getElementById('pollenIndexFill');
    const dryEyeValue = document.getElementById('dryEyeIndexValue');
    const dryEyeBar = document.getElementById('dryEyeIndexFill');

    if (!iconContainer) {
        return;
    }

    // Day / night theme — prefer the API's is_day, fall back to local hour.
    const isNight = (weatherData.isDay !== undefined && weatherData.isDay !== null)
        ? (Number(weatherData.isDay) === 0)
        : isNightTime();
    if (widget) {
        widget.classList.toggle('weather-widget--night', isNight);
        widget.classList.toggle('weather-widget--day', !isNight);
    }

    // Update weather display — animated WeatherFx icon (fallback to legacy)
    if (window.WeatherFx) {
        iconContainer.innerHTML = WeatherFx.iconHTML(weatherData, 72);
    } else {
        const iconType = getWeatherIconType(weatherData.condition || 'clear');
        iconContainer.innerHTML = renderWeatherIcon(iconType);
    }

    // Animated scene behind the whole widget (UV + advisory live in the popover/forecast
    // to keep this dashboard card compact and aligned with the sibling stat cards).
    if (window.WeatherFx && widget) {
        widget.classList.add('wx-hero');
        const old = widget.querySelector(':scope > .wx-scene');
        if (old) old.remove();
        widget.insertAdjacentHTML('afterbegin', WeatherFx.sceneHTML(weatherData));
    }

    if (tempElement) {
        tempElement.innerHTML = `${Math.round(weatherData.temperature || 0)}<span class="weather-deg">°</span>`;
    }

    if (descElement) {
        descElement.textContent = weatherData.condition || 'Clear';
    }

    if (dateElement) {
        dateElement.textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long', day: 'numeric', month: 'long'
        });
    }

    if (locationElement) {
        locationElement.innerHTML = `<i class="bi bi-geo-alt-fill"></i> <span>${weatherData.location || 'Unknown'}</span>`;
    }

    // Calculate and update health indices
    const pollenIndex = calculatePollenIndex(weatherData);
    const dryEyeRisk = calculateDryEyeRisk(weatherData);

    // Update Pollen Index
    if (pollenValue) {
        pollenValue.textContent = `${Math.round(pollenIndex)}%`;
    }
    if (pollenBar) {
        const levelClass = getLevelClass(pollenIndex);
        pollenBar.style.width = `${pollenIndex}%`;
        // Remove all level classes first
        pollenBar.classList.remove('index-low', 'index-moderate', 'index-high', 'index-very-high');
        // Add the appropriate level class
        pollenBar.classList.add(`index-${levelClass}`);
    }

    // Update Dry Eye Risk
    if (dryEyeValue) {
        dryEyeValue.textContent = `${Math.round(dryEyeRisk)}%`;
    }
    if (dryEyeBar) {
        const levelClass = getLevelClass(dryEyeRisk);
        dryEyeBar.style.width = `${dryEyeRisk}%`;
        // Remove all level classes first
        dryEyeBar.classList.remove('index-low', 'index-moderate', 'index-high', 'index-very-high');
        // Add the appropriate level class
        dryEyeBar.classList.add(`index-${levelClass}`);
    }
}

// Fetch weather data from backend
async function fetchWeatherData(latitude, longitude, saveToStorage = true) {
    try {
        const response = await fetch(`/api/weather?lat=${latitude}&lon=${longitude}`);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || `Weather API error: ${response.status}`);
        }
        const data = await response.json();

        if (data.success && data.weather) {
            updateWeatherCard(data.weather);
            
            // Save to localStorage with timestamp
            if (saveToStorage) {
                const weatherData = {
                    data: data.weather,
                    latitude: latitude,
                    longitude: longitude,
                    timestamp: Date.now()
                };
                localStorage.setItem('dashboard_weather_data', JSON.stringify(weatherData));
            }
        } else {
            throw new Error(data.error || data.message || 'Failed to get weather data');
        }
    } catch (error) {
        // Show user-friendly error message (API error, not geolocation error)
        const locationElement = document.getElementById('weatherLocation');
        if (locationElement) {
            locationElement.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i><span>Unable to fetch weather data: ${error.message}</span>`;
        }
        
        // Show error in weather card
        const tempElement = document.getElementById('weatherTemp');
        const descElement = document.getElementById('weatherDesc');
        if (tempElement) tempElement.textContent = '--°C';
        if (descElement) descElement.textContent = 'Weather API error';
    }
}

// Load weather data from localStorage
function loadWeatherFromStorage() {
    try {
        const stored = localStorage.getItem('dashboard_weather_data');
        if (!stored) return null;
        
        const weatherData = JSON.parse(stored);
        const now = Date.now();
        const age = now - weatherData.timestamp;
        const maxAge = 15 * 60 * 1000; // 15 minutes
        
        // Return data if it's less than 15 minutes old
        if (age < maxAge && weatherData.data) {
            return weatherData;
        }
        
        // Data is too old, remove it
        localStorage.removeItem('dashboard_weather_data');
        return null;
    } catch (error) {
        console.error('Error loading weather from storage:', error);
        localStorage.removeItem('dashboard_weather_data');
        return null;
    }
}

// Get user location and load weather
function initWeatherCard() {
    const iconContainer = document.getElementById('weatherIconContainer');
    if (!iconContainer) return; // Weather card not present

    // Default location: Kafr El Sheikh, Egypt
    const DEFAULT_LAT = 31.1117;
    const DEFAULT_LON = 30.9397;
    const DEFAULT_LOCATION_NAME = 'Kafr El Sheikh';
    
    const locationElement = document.getElementById('weatherLocation');
    
    // Show default location immediately
    if (locationElement) {
        locationElement.innerHTML = `<i class="bi bi-geo-alt-fill"></i><span>${DEFAULT_LOCATION_NAME}</span>`;
    }
    
    // Try browser geolocation - only 1 attempt, then fallback to default
    if ('geolocation' in navigator) {
        const geoOptions = {
            enableHighAccuracy: false,
            timeout: 3000,  // 3 second timeout - quick fallback
            maximumAge: 600000
        };
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                // Success - update location and fetch weather
                if (locationElement) {
                    locationElement.innerHTML = `<i class="bi bi-geo-alt-fill"></i><span>Getting weather data...</span>`;
                }
                fetchWeatherData(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                // Error - use default location immediately (no retry)
                if (locationElement) {
                    locationElement.innerHTML = `<i class="bi bi-geo-alt-fill"></i><span>${DEFAULT_LOCATION_NAME}</span>`;
                }
                fetchWeatherData(DEFAULT_LAT, DEFAULT_LON);
            },
            geoOptions
        );
    } else {
        // Geolocation not supported - use default location
        fetchWeatherData(DEFAULT_LAT, DEFAULT_LON);
    }
}

// Fallback: fetch weather based on IP location (DISABLED for now)
async function fetchWeatherFromIP() {
    // IP geolocation is disabled - function kept for reference but not used
}

// Weather forecast popover
let weatherForecastPopover = null;

function showWeatherForecastPopover() {
    // Close if already open
    if (weatherForecastPopover) {
        closeWeatherForecastPopover();
        return;
    }
    
    // Get current location from weather card
    const locationElement = document.getElementById('weatherLocation');
    if (!locationElement) return;
    
    // Try to get coordinates from current weather data or use default
    const DEFAULT_LAT = 31.1117;
    const DEFAULT_LON = 30.9397;
    
    // Create popover
    const popover = document.createElement('div');
    popover.className = 'weather-forecast-popover';
    popover.id = 'weatherForecastPopover';
    popover.innerHTML = `
        <div class="weather-forecast-popover-content">
            <div class="weather-forecast-popover-header">
                <h5>4-Day Weather Forecast</h5>
                <button class="weather-forecast-close" id="weatherForecastClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="weather-forecast-popover-body" id="weatherForecastBody">
                <div class="weather-forecast-loading">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span>Loading forecast...</span>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(popover);
    weatherForecastPopover = popover;
    
    // Close button handler
    document.getElementById('weatherForecastClose').addEventListener('click', closeWeatherForecastPopover);
    
    // Close on backdrop click
    popover.addEventListener('click', (e) => {
        if (e.target === popover) {
            closeWeatherForecastPopover();
        }
    });
    
    // Close on ESC key
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeWeatherForecastPopover();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
    
    // Fetch forecast data
    fetchWeatherForecast(DEFAULT_LAT, DEFAULT_LON);
}

function closeWeatherForecastPopover() {
    if (weatherForecastPopover) {
        weatherForecastPopover.remove();
        weatherForecastPopover = null;
    }
}

async function fetchWeatherForecast(latitude, longitude) {
    try {
        const response = await fetch(`/api/weather-forecast?lat=${latitude}&lon=${longitude}`);
        if (!response.ok) {
            throw new Error('Weather forecast API error');
        }
        const data = await response.json();
        
        if (data.success && data.forecast) {
            renderWeatherForecast(data.forecast);
        } else {
            throw new Error(data.error || 'Failed to get forecast data');
        }
    } catch (error) {
        const body = document.getElementById('weatherForecastBody');
        if (body) {
            body.innerHTML = `
                <div class="weather-forecast-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Unable to load forecast: ${error.message}</span>
                </div>
            `;
        }
    }
}

function renderWeatherForecast(forecast) {
    const body = document.getElementById('weatherForecastBody');
    if (!body) return;
    
    if (!forecast || forecast.length === 0) {
        body.innerHTML = `
            <div class="weather-forecast-error">
                <i class="bi bi-info-circle-fill"></i>
                <span>No forecast data available</span>
            </div>
        `;
        return;
    }
    
    let html = '<div class="weather-forecast-days">';
    
    forecast.forEach((day, index) => {
        const date = new Date(day.date);
        const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
        const dayNumber = date.getDate();
        const month = date.toLocaleDateString('en-US', { month: 'short' });
        
        const pollenIndex = calculatePollenIndex(day);
        const dryEyeRisk = calculateDryEyeRisk(day);
        const pollenLevel = getLevelClass(pollenIndex);
        const dryEyeLevel = getLevelClass(dryEyeRisk);
        
        html += `
            <div class="weather-forecast-day">
                <div class="forecast-day-header">
                    <div class="forecast-day-name">${dayName}</div>
                    <div class="forecast-day-date">${dayNumber} ${month}</div>
                </div>
                <div class="forecast-day-weather">
                    <div class="forecast-day-icon">
                        ${renderWeatherIcon(getWeatherIconType(day.condition || 'clear'))}
                    </div>
                    <div class="forecast-day-temp">
                        <span class="forecast-temp-high">${Math.round(day.tempMax || day.temperature)}°</span>
                        <span class="forecast-temp-low">${Math.round(day.tempMin || day.temperature - 5)}°</span>
                    </div>
                    <div class="forecast-day-condition">${day.condition || 'Clear'}</div>
                </div>
                <div class="forecast-day-indices">
                    <div class="forecast-index-item">
                        <div class="forecast-index-label">
                            <i class="bi bi-flower1"></i>
                            <span>Pollen</span>
                        </div>
                        <div class="forecast-index-bar">
                            <div class="forecast-index-fill index-${pollenLevel}" style="width: ${Math.max(2, pollenIndex)}%"></div>
                        </div>
                        <div class="forecast-index-value">${Math.round(pollenIndex)}%</div>
                    </div>
                    <div class="forecast-index-item">
                        <div class="forecast-index-label">
                            <i class="bi bi-eye"></i>
                            <span>Dry Eye</span>
                        </div>
                        <div class="forecast-index-bar">
                            <div class="forecast-index-fill index-${dryEyeLevel}" style="width: ${Math.max(2, dryEyeRisk)}%"></div>
                        </div>
                        <div class="forecast-index-value">${Math.round(dryEyeRisk)}%</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    body.innerHTML = html;
}

// Initialize weather on page load - load last after all dashboard content
document.addEventListener('DOMContentLoaded', function() {
    // Forecast button handler
    // The whole weather card opens the unified ortho-style frosted forecast window
    // (defined in main.js); fall back to the legacy popover if it isn't available.
    const weatherWidget = document.getElementById('weatherWidget');
    if (weatherWidget) {
        const openForecast = function() {
            if (typeof window.openWeatherForecastWindow === 'function') {
                window.openWeatherForecastWindow();
            } else {
                showWeatherForecastPopover();
            }
        };
        weatherWidget.addEventListener('click', openForecast);
        weatherWidget.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openForecast(); }
        });
    }

    // Weather loading is intentionally NON-BLOCKING so it never delays the rest of
    // the dashboard. `/api/weather` makes a server-side external call that can be slow
    // on a cold cache and would otherwise contend with the dashboard's own requests
    // (especially on a single-threaded dev server). So:
    //   1) paint instantly from the localStorage cache (no network), then
    //   2) do the real network refresh only once the page is idle and the rest of
    //      the dashboard has finished loading.
    const cachedWeather = (typeof loadWeatherFromStorage === 'function') ? loadWeatherFromStorage() : null;
    if (cachedWeather && cachedWeather.data) {
        updateWeatherCard(cachedWeather.data);
    }

    const refreshWeather = () => initWeatherCard();
    window.addEventListener('load', () => {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(refreshWeather, { timeout: 5000 });
        } else {
            setTimeout(refreshWeather, 2500);
        }
    });

    // Refresh weather every 15 minutes
    setInterval(refreshWeather, 15 * 60 * 1000);

    // Load Unified Clinical Dashboard
    loadUnifiedClinicalDashboard();
});

// ============================================
// Unified Clinical Dashboard
// ============================================

/**
 * Load Unified Clinical Dashboard data
 */
async function loadUnifiedClinicalDashboard() {
    // Get last viewed patient_id from localStorage
    const lastViewedPatientId = localStorage.getItem('lastViewedPatientId');
    
    const noPatientDiv = document.getElementById('unifiedClinicalDashboardNoPatient');
    const contentDiv = document.getElementById('unifiedClinicalDashboardContent');
    
    if (!lastViewedPatientId || !lastViewedPatientId.match(/^\d+$/)) {
        // No patient selected
        if (noPatientDiv) noPatientDiv.style.display = 'block';
        if (contentDiv) contentDiv.style.display = 'none';
        return;
    }

    try {
        // Show loading state
        if (noPatientDiv) noPatientDiv.style.display = 'none';
        if (contentDiv) contentDiv.style.display = 'block';

        const response = await fetch(`/api/clinical-dashboard/snapshot?patient_id=${lastViewedPatientId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.ok && data.data) {
            renderClinicalDashboard(data.data);
            // Show patient info notice with patient ID for clickable link
            renderPatientInfoNotice(
                data.data.patient_name || 'Unknown Patient',
                data.data.patient_id || lastViewedPatientId
            );
        } else {
            // Error or no data
            if (noPatientDiv) noPatientDiv.style.display = 'block';
            if (contentDiv) contentDiv.style.display = 'none';
            // Hide patient info notice
            const noticeDiv = document.getElementById('patientInfoNotice');
            if (noticeDiv) noticeDiv.style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading clinical dashboard:', error);
        if (noPatientDiv) noPatientDiv.style.display = 'block';
        if (contentDiv) contentDiv.style.display = 'none';
    }
}

/**
 * Render patient info notice
 */
function renderPatientInfoNotice(patientName, patientId) {
    const noticeDiv = document.getElementById('patientInfoNotice');
    const nameEl = document.getElementById('patientInfoName');
    const noticeContent = document.getElementById('patientInfoNoticeContent');
    
    if (noticeDiv && nameEl) {
        nameEl.textContent = patientName || 'Unknown Patient';
        noticeDiv.style.display = 'block';
        
        // Make clickable if patientId is provided
        if (patientId && noticeContent) {
            noticeContent.style.cursor = 'pointer';
            noticeContent.onclick = () => {
                window.location.href = `/doctor/patients/${patientId}`;
            };
            
            // Add hover effect class
            noticeContent.classList.add('patient-info-clickable');
            
            // Make name link style
            if (nameEl) {
                nameEl.classList.add('patient-info-name-link');
            }
        }
    }
}

/**
 * Render clinical dashboard data
 */
function renderClinicalDashboard(data) {
    const snapshot = data.snapshot || {};
    
    // Render IOP Status
    renderIOPStatus(snapshot.iop || {});
    
    // Render Visual Acuity
    renderVisualAcuity(snapshot.visual_acuity || {});
    
    // Render Cataract Status
    renderCataractStatus(snapshot.cataract || {});
    
    // Render Dry Eye Status
    renderDryEyeStatus(snapshot.dry_eye || {});
    
    // Render Alerts
    renderClinicalAlerts(data.alerts || []);
    
    // Render Mini Trends
    renderMiniTrends(snapshot);
    
    // Render Clinical Summary
    renderClinicalSummary(data.summary || 'Clinical data not available.');
}

/**
 * Render IOP Status indicator
 */
function renderIOPStatus(iop) {
    const valueEl = document.getElementById('iopValue');
    const statusEl = document.getElementById('iopStatus');
    
    if (!valueEl || !statusEl) return;
    
    if (iop.value === null || iop.value === undefined) {
        valueEl.textContent = '--';
        statusEl.innerHTML = '<span class="badge bg-secondary">Not available</span>';
        return;
    }
    
    valueEl.textContent = `${iop.value} mmHg`;
    if (iop.target !== null && iop.target !== undefined) {
        valueEl.textContent += ` (Target: ${iop.target} mmHg)`;
    }
    
    let badgeClass = 'bg-success';
    if (iop.status === 'warning') {
        badgeClass = 'bg-warning text-dark';
    } else if (iop.status === 'critical') {
        badgeClass = 'bg-danger';
    }
    
    statusEl.innerHTML = `<span class="badge ${badgeClass}">${iop.message || 'Normal'}</span>`;
    
    // Make clickable to navigate to appointment
    if (iop.appointment_id) {
        const indicatorCard = document.getElementById('clinicalIndicatorIOP');
        if (indicatorCard) {
            indicatorCard.style.cursor = 'pointer';
            indicatorCard.onclick = () => {
                window.location.href = `/doctor/appointments/${iop.appointment_id}`;
            };
        }
    }
}

/**
 * Render Visual Acuity indicator
 */
function renderVisualAcuity(va) {
    const valueEl = document.getElementById('vaValue');
    const trendEl = document.getElementById('vaTrend');
    
    if (!valueEl || !trendEl) return;
    
    if (va.last === null || va.last === undefined) {
        valueEl.textContent = '--';
        trendEl.innerHTML = '<span class="trend-indicator">→</span><span class="trend-text">Not available</span>';
        return;
    }
    
    // Truncate if too long
    const displayValue = va.last.length > 30 ? va.last.substring(0, 30) + '...' : va.last;
    valueEl.textContent = displayValue;
    
    const trendIcon = va.trend === '↑' ? '↑' : (va.trend === '↓' ? '↓' : '→');
    const trendClass = va.trend === '↑' ? 'text-danger' : (va.trend === '↓' ? 'text-success' : 'text-muted');
    
    trendEl.innerHTML = `<span class="trend-indicator ${trendClass}">${trendIcon}</span><span class="trend-text">${va.message || 'Stable'}</span>`;
    
    // Make clickable
    if (va.appointment_id) {
        const indicatorCard = document.getElementById('clinicalIndicatorVA');
        if (indicatorCard) {
            indicatorCard.style.cursor = 'pointer';
            indicatorCard.onclick = () => {
                window.location.href = `/doctor/appointments/${va.appointment_id}`;
            };
        }
    }
}

/**
 * Render Cataract Status indicator
 */
function renderCataractStatus(cataract) {
    const valueEl = document.getElementById('cataractValue');
    const statusEl = document.getElementById('cataractStatus');
    
    if (!valueEl || !statusEl) return;
    
    if (cataract.readiness === null || cataract.readiness === undefined) {
        valueEl.textContent = '--';
        statusEl.innerHTML = '<span class="badge bg-secondary">Not available</span>';
        return;
    }
    
    valueEl.textContent = cataract.readiness || '--';
    
    let badgeClass = 'bg-info';
    if (cataract.status === 'surgery_recommended') {
        badgeClass = 'bg-danger';
    } else if (cataract.status === 'consider_surgery') {
        badgeClass = 'bg-warning text-dark';
    }
    
    statusEl.innerHTML = `<span class="badge ${badgeClass}">${cataract.message || 'Monitor'}</span>`;
    
    // Make clickable
    if (cataract.appointment_id) {
        const indicatorCard = document.getElementById('clinicalIndicatorCataract');
        if (indicatorCard) {
            indicatorCard.style.cursor = 'pointer';
            indicatorCard.onclick = () => {
                window.location.href = `/doctor/appointments/${cataract.appointment_id}`;
            };
        }
    }
}

/**
 * Render Dry Eye Status indicator
 */
function renderDryEyeStatus(dryEye) {
    const valueEl = document.getElementById('dryEyeValue');
    const trendEl = document.getElementById('dryEyeTrend');
    
    if (!valueEl || !trendEl) return;
    
    if (dryEye.osdi_score === null || dryEye.osdi_score === undefined) {
        valueEl.textContent = '--';
        trendEl.innerHTML = '<span class="trend-indicator">→</span><span class="trend-text">Not available</span>';
        return;
    }
    
    valueEl.textContent = `OSDI: ${dryEye.osdi_score}`;
    if (dryEye.severity) {
        valueEl.textContent += ` (${dryEye.severity})`;
    }
    
    const trendText = dryEye.trend === 'improving' ? 'Improving' : 
                     (dryEye.trend === 'worsening' ? 'Worsening' : 'Stable');
    const trendClass = dryEye.trend === 'worsening' ? 'text-danger' : 
                      (dryEye.trend === 'improving' ? 'text-success' : 'text-muted');
    const trendIcon = dryEye.trend === 'worsening' ? '↑' : 
                     (dryEye.trend === 'improving' ? '↓' : '→');
    
    trendEl.innerHTML = `<span class="trend-indicator ${trendClass}">${trendIcon}</span><span class="trend-text">${trendText}</span>`;
    
    // Make clickable
    if (dryEye.appointment_id) {
        const indicatorCard = document.getElementById('clinicalIndicatorDryEye');
        if (indicatorCard) {
            indicatorCard.style.cursor = 'pointer';
            indicatorCard.onclick = () => {
                window.location.href = `/doctor/appointments/${dryEye.appointment_id}`;
            };
        }
    }
}

/**
 * Render Clinical Alerts
 */
function renderClinicalAlerts(alerts) {
    const container = document.getElementById('clinicalAlertsContainer');
    if (!container) return;
    
    if (alerts.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                No active alerts
            </div>
        `;
        return;
    }
    
    let alertsHTML = '';
    alerts.forEach(alert => {
        let alertClass = 'alert-info';
        if (alert.severity === 'warning') {
            alertClass = 'alert-warning';
        } else if (alert.severity === 'critical') {
            alertClass = 'alert-danger';
        }
        
        const clickHandler = alert.appointment_id ? 
            `onclick="window.location.href='/doctor/appointments/${alert.appointment_id}'" style="cursor: pointer;"` : '';
        
        alertsHTML += `
            <div class="alert ${alertClass} mb-2" ${clickHandler}>
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${escapeHtml(alert.message)}
            </div>
        `;
    });
    
    container.innerHTML = alertsHTML;
}

/**
 * Render Mini Trends
 */
function renderMiniTrends(snapshot) {
    // IOP Trend (simplified - just show status)
    const iopTrendEl = document.getElementById('iopTrendChart');
    if (iopTrendEl) {
        if (snapshot.iop && snapshot.iop.value !== null) {
            const status = snapshot.iop.status === 'warning' ? '⚠️ Above target' : '✓ Within target';
            iopTrendEl.innerHTML = `<div class="mini-trend-status">${status}</div>`;
        } else {
            iopTrendEl.innerHTML = '<div class="mini-trend-placeholder">No data</div>';
        }
    }
    
    // Visual Acuity Trend
    const vaTrendEl = document.getElementById('vaTrendChart');
    if (vaTrendEl) {
        if (snapshot.visual_acuity && snapshot.visual_acuity.trend) {
            const trendIcon = snapshot.visual_acuity.trend === '↑' ? '↑ Worsening' : 
                            (snapshot.visual_acuity.trend === '↓' ? '↓ Improving' : '→ Stable');
            vaTrendEl.innerHTML = `<div class="mini-trend-status">${trendIcon}</div>`;
        } else {
            vaTrendEl.innerHTML = '<div class="mini-trend-placeholder">No data</div>';
        }
    }
    
    // Macular Thickness Trend
    const macularTrendEl = document.getElementById('macularTrendChart');
    if (macularTrendEl) {
        if (snapshot.macular_thickness && snapshot.macular_thickness.latest !== null) {
            const trendText = snapshot.macular_thickness.trend === 'worsening' ? '⚠️ Worsening' :
                            (snapshot.macular_thickness.trend === 'improving' ? '↓ Improving' : '→ Stable');
            macularTrendEl.innerHTML = `<div class="mini-trend-status">${trendText}</div>`;
        } else {
            macularTrendEl.innerHTML = '<div class="mini-trend-placeholder">No data</div>';
        }
    }
}

/**
 * Render Clinical Summary
 */
function renderClinicalSummary(summary) {
    const summaryEl = document.getElementById('clinicalSummaryText');
    if (summaryEl) {
        summaryEl.textContent = summary;
    }
}

/**
 * Copy clinical summary to clipboard
 */
function copyClinicalSummary() {
    const summaryEl = document.getElementById('clinicalSummaryText');
    if (!summaryEl) return;
    
    const summaryText = summaryEl.textContent;
    
    navigator.clipboard.writeText(summaryText).then(() => {
        const btn = document.getElementById('copySummaryBtn');
        if (btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Listen for patient page views and appointment page views to update lastViewedPatientId
if (typeof window !== 'undefined') {
    function savePatientIdAndReload(patientId) {
        if (patientId && patientId !== 'null' && patientId !== null) {
            localStorage.setItem('lastViewedPatientId', patientId.toString());
            if (typeof loadUnifiedClinicalDashboard === 'function') {
                loadUnifiedClinicalDashboard();
            }
        }
    }
    
    // Save patient ID when viewing patient page
    const currentPath = window.location.pathname;
    const patientMatch = currentPath.match(/\/doctor\/patients\/(\d+)/);
    if (patientMatch) {
        savePatientIdAndReload(patientMatch[1]);
    }
    
    // Save patient ID when viewing appointment page (from APPOINTMENT_CONFIG)
    const appointmentMatch = currentPath.match(/\/doctor\/appointments\/(\d+)/);
    if (appointmentMatch) {
        // Wait for APPOINTMENT_CONFIG to be available
        if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
            savePatientIdAndReload(window.APPOINTMENT_CONFIG.patientId);
        } else {
            // Wait a bit for script to load
            setTimeout(() => {
                if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                    savePatientIdAndReload(window.APPOINTMENT_CONFIG.patientId);
                }
            }, 500);
        }
    }
    
    // Also listen for navigation events
    window.addEventListener('popstate', () => {
        const path = window.location.pathname;
        const patientMatch = path.match(/\/doctor\/patients\/(\d+)/);
        if (patientMatch) {
            savePatientIdAndReload(patientMatch[1]);
        }
        
        const appointmentMatch = path.match(/\/doctor\/appointments\/(\d+)/);
        if (appointmentMatch && window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
            savePatientIdAndReload(window.APPOINTMENT_CONFIG.patientId);
        }
    });
    
    // Monitor APPOINTMENT_CONFIG if it loads after page load
    if (appointmentMatch) {
        let configCheckCount = 0;
        const configCheckInterval = setInterval(() => {
            configCheckCount++;
            if (window.APPOINTMENT_CONFIG && window.APPOINTMENT_CONFIG.patientId) {
                savePatientIdAndReload(window.APPOINTMENT_CONFIG.patientId);
                clearInterval(configCheckInterval);
            } else if (configCheckCount >= 50) {
                // Stop checking after 5 seconds (50 * 100ms)
                clearInterval(configCheckInterval);
            }
        }, 100);
    }
}