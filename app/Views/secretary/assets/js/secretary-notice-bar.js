/* ============================================================================
 * Secretary notice-bar popovers — clock/calendar + upcoming-appointments.
 * Ported from the doctor layout (main.js) and adapted for RTL/Arabic and the
 * clinic-scoped endpoints:
 *   - /api/secretary/month?year&month   → { data:{ dataByDate } }   (calendar dots + today stats)
 *   - /api/secretary/next-appointments  → { items:[…] }             (next countdown + appt popover)
 * Triggers: click .notice-bar-clock (clock popover) / #secNoticeNext (appt popover).
 * The visible bar text (live clock + cycling next-appt) is still driven by the
 * inline script in secretary_main.php; this file only adds the click popovers.
 * See V11_SEC_LAYOUT.md §16.
 * ========================================================================== */
(function () {
    'use strict';

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : text;
        return div.innerHTML;
    }

    const AR_MONTHS = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
        'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
    const AR_WEEK_SHORT = ['أحد', 'إثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت'];
    const AR_WEEK_LONG = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

    const CLOCK_STATUS_COLORS = {
        Booked: '#3b82f6', CheckedIn: '#22c55e', InProgress: '#f59e0b',
        Completed: '#06b6d4', Rescheduled: '#a78bfa'
    };
    const CLOCK_STATUS_LABELS = {
        Booked: 'محجوز', CheckedIn: 'تم الوصول', InProgress: 'جارٍ',
        Completed: 'مكتمل', Rescheduled: 'معاد جدولته'
    };

    const pad = (n) => String(n).padStart(2, '0');
    function t12(t) {
        if (!t) return '';
        const [hh, mm] = t.split(':');
        let h = parseInt(hh, 10);
        const ap = h >= 12 ? 'م' : 'ص';
        h = h % 12 || 12;
        return `${h}:${mm} ${ap}`;
    }

    // ---- shared month cache -------------------------------------------------
    let _monthCache = {};
    function getMonthData(year, month1) {
        const key = `${year}-${month1}`;
        if (_monthCache[key]) return Promise.resolve(_monthCache[key]);
        return fetch(`/api/secretary/month?year=${year}&month=${month1}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => {
                const byDate = (res && res.ok && res.data && res.data.dataByDate) ? res.data.dataByDate : {};
                _monthCache[key] = byDate;
                return byDate;
            })
            .catch(() => ({}));
    }

    /* ======================================================================
     *  CLOCK + CALENDAR POPOVER
     * ==================================================================== */
    let clockCalendarPopover = null;

    function createClockCalendarPopover() {
        if (clockCalendarPopover) clockCalendarPopover.remove();

        const popover = document.createElement('div');
        popover.className = 'clock-calendar-popover';
        popover.id = 'clockCalendarPopover';

        // Working-hours arc (clinic day = 14:00–23:00, i.e. 2 → 11 on the dial)
        const workArcPath = (() => {
            const R = 122, cx = 160, cy = 160;
            const pt = (h) => {
                const a = (h * 30 - 90) * Math.PI / 180;
                return [cx + R * Math.cos(a), cy + R * Math.sin(a)];
            };
            const [sx, sy] = pt(2);
            const [ex, ey] = pt(11);
            return `M ${sx.toFixed(1)} ${sy.toFixed(1)} A ${R} ${R} 0 1 1 ${ex.toFixed(1)} ${ey.toFixed(1)}`;
        })();

        const hourNumbers = Array.from({ length: 12 }, (_, i) => {
            const num = i === 0 ? 12 : i;
            const angle = (i * 30 - 90) * Math.PI / 180;
            const x = 160 + 108 * Math.cos(angle);
            const y = 160 + 108 * Math.sin(angle);
            return `<text class="cf-num" x="${x.toFixed(1)}" y="${y.toFixed(1)}" text-anchor="middle" dominant-baseline="central">${num}</text>`;
        }).join('');

        const hourTicks = Array.from({ length: 12 }, (_, i) => {
            const angle = (i * 30 - 90) * Math.PI / 180;
            const x1 = 160 + 130 * Math.cos(angle), y1 = 160 + 130 * Math.sin(angle);
            const x2 = 160 + 145 * Math.cos(angle), y2 = 160 + 145 * Math.sin(angle);
            return `<line class="cf-tick-h" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"/>`;
        }).join('');

        const minuteTicks = Array.from({ length: 60 }, (_, i) => {
            if (i % 5 === 0) return '';
            const angle = (i * 6 - 90) * Math.PI / 180;
            const x1 = 160 + 135 * Math.cos(angle), y1 = 160 + 135 * Math.sin(angle);
            const x2 = 160 + 142 * Math.cos(angle), y2 = 160 + 142 * Math.sin(angle);
            return `<line class="cf-tick-m" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"/>`;
        }).join('');

        const weekdayLis = AR_WEEK_SHORT.map(d => `<li>${d}</li>`).join('');

        popover.innerHTML = `
            <div class="clock-calendar-popover-content">
                <div class="clock-calendar-column clock-column">
                    <div class="clock">
                        <svg class="clock-face" viewBox="0 0 320 320" xmlns="http://www.w3.org/2000/svg">
                            <circle class="cf-bg" cx="160" cy="160" r="152"/>
                            <circle class="cf-ring" cx="160" cy="160" r="150"/>
                            ${hourTicks}
                            ${minuteTicks}
                            ${hourNumbers}
                            <path class="work-arc" d="${workArcPath}"/>
                            <circle class="cf-center" cx="160" cy="160" r="8"/>
                        </svg>
                        <div class="hour hand" id="secClockHour"></div>
                        <div class="minute hand" id="secClockMinute"></div>
                        <div class="seconds hand" id="secClockSeconds"></div>
                    </div>
                    <div class="clock-extras">
                        <div class="clock-digital">
                            <span class="clock-digital-time" id="secClockDigitalTime">--:--:--</span>
                            <span class="clock-digital-date" id="secClockDigitalDate"></span>
                        </div>
                        <div class="clock-stats" id="secClockStats">
                            <div class="clock-stat"><span class="clock-stat-num" id="secClockStatTotal">–</span><span class="clock-stat-label">اليوم</span></div>
                            <div class="clock-stat is-done"><span class="clock-stat-num" id="secClockStatDone">–</span><span class="clock-stat-label">منجز</span></div>
                            <div class="clock-stat is-left"><span class="clock-stat-num" id="secClockStatLeft">–</span><span class="clock-stat-label">متبقٍ</span></div>
                        </div>
                        <div class="clock-next is-empty" id="secClockNext">
                            <i class="bi bi-hourglass-split"></i>
                            <span class="clock-next-text">جارٍ التحميل…</span>
                        </div>
                    </div>
                </div>
                <div class="clock-calendar-column calendar-column">
                    <div class="calendar-container" dir="ltr">
                        <header class="calendar-header">
                            <p class="calendar-current-date"></p>
                            <div class="calendar-navigation">
                                <span id="sec-calendar-prev" class="bi bi-chevron-left"></span>
                                <span id="sec-calendar-next" class="bi bi-chevron-right"></span>
                            </div>
                        </header>
                        <div class="calendar-body">
                            <ul class="calendar-weekdays">${weekdayLis}</ul>
                            <ul class="calendar-dates"></ul>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(popover);
        clockCalendarPopover = popover;
        clockCalendarPopover._intervals = [];

        initAnalogClock(popover);
        initDigitalClock(popover);
        initCalendar(popover);
        initClockData(popover);
        positionClockCalendarPopover();

        const backdrop = document.createElement('div');
        backdrop.className = 'clock-calendar-popover-backdrop';
        backdrop.addEventListener('click', closeClockCalendarPopover);
        document.body.appendChild(backdrop);
    }

    function positionClockCalendarPopover() {
        if (!clockCalendarPopover) return;
        const noticeBar = document.querySelector('.notice-bar');
        if (!noticeBar) return;
        const rect = noticeBar.getBoundingClientRect();
        clockCalendarPopover.style.top = (rect.bottom + 10) + 'px';
        clockCalendarPopover.style.left = '50%';
        clockCalendarPopover.style.transform = 'translateX(-50%)';
    }

    function closeClockCalendarPopover() {
        const el = clockCalendarPopover || document.getElementById('clockCalendarPopover');
        if (el) {
            if (Array.isArray(el._intervals)) el._intervals.forEach(id => clearInterval(id));
            el.remove();
        }
        clockCalendarPopover = null;
        const backdrop = document.querySelector('.clock-calendar-popover-backdrop');
        if (backdrop) backdrop.remove();
        const tip = document.getElementById('secCalDayTooltip');
        if (tip) tip.remove();
    }

    function initAnalogClock(popover) {
        const hour = popover.querySelector('#secClockHour');
        const minute = popover.querySelector('#secClockMinute');
        const seconds = popover.querySelector('#secClockSeconds');
        if (!hour || !minute || !seconds) return;
        function updateClock() {
            const d = new Date();
            const hr = d.getHours(), min = d.getMinutes(), sec = d.getSeconds();
            hour.style.transform = `rotate(${(hr * 30) + (min / 2)}deg)`;
            minute.style.transform = `rotate(${min * 6}deg)`;
            seconds.style.transform = `rotate(${sec * 6}deg)`;
        }
        updateClock();
        popover._intervals.push(setInterval(updateClock, 1000));
    }

    function initDigitalClock(popover) {
        const timeEl = popover.querySelector('#secClockDigitalTime');
        const dateEl = popover.querySelector('#secClockDigitalDate');
        if (!timeEl) return;
        const tick = () => {
            const now = new Date();
            let h = now.getHours();
            const m = pad(now.getMinutes()), s = pad(now.getSeconds());
            const ap = h >= 12 ? 'م' : 'ص';
            h = h % 12 || 12;
            timeEl.innerHTML = `${h}:${m}:${s}<span class="ampm">${ap}</span>`;
            if (dateEl) dateEl.textContent = `${AR_WEEK_LONG[now.getDay()]} ${now.getDate()} ${AR_MONTHS[now.getMonth()]}`;
        };
        tick();
        popover._intervals.push(setInterval(tick, 1000));
    }

    // ---- calendar day tooltip ----------------------------------------------
    function showCalDayTooltip(li, dateStr, appts) {
        if (!appts || !appts.length) return;
        let tip = document.getElementById('secCalDayTooltip');
        if (!tip) {
            tip = document.createElement('div');
            tip.id = 'secCalDayTooltip';
            tip.className = 'cal-day-tooltip';
            document.body.appendChild(tip);
        }
        const d = new Date(dateStr + 'T12:00:00');
        const head = `${AR_WEEK_LONG[d.getDay()]} ${d.getDate()} ${AR_MONTHS[d.getMonth()]}`;
        const sorted = appts.slice().sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''));
        const MAX = 8;
        const rows = sorted.slice(0, MAX).map(a => {
            const c = CLOCK_STATUS_COLORS[a.status] || '#94a3b8';
            const label = CLOCK_STATUS_LABELS[a.status] || a.status || '';
            return `<div class="cal-tip-item" style="border-left-color:${c}">`
                + `<span class="cal-tip-time">${t12(a.start_time)}</span>`
                + `<span class="cal-tip-name">${escapeHtml(a.patient_name || 'مريض')}</span>`
                + `<span class="cal-tip-status" style="color:${c}">${escapeHtml(label)}</span>`
                + `</div>`;
        }).join('');
        const more = sorted.length > MAX ? `<div class="cal-tip-more">+${sorted.length - MAX} المزيد</div>` : '';
        tip.innerHTML = `<div class="cal-tip-head"><span>${head}</span>`
            + `<span class="cal-tip-count">${appts.length} موعد</span></div>`
            + `<div class="cal-tip-list">${rows}${more}</div>`;
        tip.style.display = 'block';
        const r = li.getBoundingClientRect();
        const tr = tip.getBoundingClientRect();
        let top = r.top - tr.height - 10;
        if (top < 8) top = r.bottom + 10;
        let left = r.left + r.width / 2 - tr.width / 2;
        left = Math.max(8, Math.min(left, window.innerWidth - tr.width - 8));
        tip.style.top = top + 'px';
        tip.style.left = left + 'px';
    }
    function hideCalDayTooltip() {
        const tip = document.getElementById('secCalDayTooltip');
        if (tip) tip.style.display = 'none';
    }

    function initCalendar(popover) {
        let date = new Date();
        let year = date.getFullYear();
        let month = date.getMonth();

        const day = popover.querySelector('.calendar-dates');
        const currdate = popover.querySelector('.calendar-current-date');
        const prenexIcons = popover.querySelectorAll('.calendar-navigation span');
        if (!day || !currdate) return;

        const dayMarkup = (appts) => {
            const count = appts.length;
            if (!count) return '';
            const dots = appts.slice(0, 3).map(a => {
                const c = CLOCK_STATUS_COLORS[a.status] || '#94a3b8';
                return `<span class="cal-dot" style="background:${c}"></span>`;
            }).join('');
            return `<span class="cal-dots">${dots}</span><span class="cal-count">${count > 99 ? '99+' : count}</span>`;
        };

        const render = (byDate) => {
            let dayone = new Date(year, month, 1).getDay();
            let lastdate = new Date(year, month + 1, 0).getDate();
            let dayend = new Date(year, month, lastdate).getDay();
            let monthlastdate = new Date(year, month, 0).getDate();
            let lit = '';
            for (let i = dayone; i > 0; i--) lit += `<li class="inactive">${monthlastdate - i + 1}</li>`;
            const realNow = new Date();
            for (let i = 1; i <= lastdate; i++) {
                const isToday = i === realNow.getDate() && month === realNow.getMonth() && year === realNow.getFullYear();
                const dateStr = `${year}-${pad(month + 1)}-${pad(i)}`;
                const appts = (byDate[dateStr] && byDate[dateStr].appointments) ? byDate[dateStr].appointments : [];
                const classes = ['cal-day'];
                if (isToday) classes.push('active');
                if (appts.length) classes.push('has-appts');
                const title = appts.length ? `${appts.length} موعد` : 'لا مواعيد';
                lit += `<li class="${classes.join(' ')}" data-date="${dateStr}" title="${title}">`
                    + `${dayMarkup(appts)}<span class="cal-daynum">${i}</span></li>`;
            }
            for (let i = dayend; i < 6; i++) lit += `<li class="inactive">${i - dayend + 1}</li>`;
            currdate.innerText = `${AR_MONTHS[month]} ${year}`;
            day.innerHTML = lit;
            day.querySelectorAll('li.cal-day').forEach(li => {
                const d = li.getAttribute('data-date');
                const appts = (byDate[d] && byDate[d].appointments) ? byDate[d].appointments : [];
                li.addEventListener('click', () => { if (d) window.location.href = `/secretary/bookings?date=${d}`; });
                if (appts.length) {
                    li.addEventListener('mouseenter', () => showCalDayTooltip(li, d, appts));
                    li.addEventListener('mouseleave', hideCalDayTooltip);
                }
            });
        };

        const loadMonth = () => {
            render({});
            getMonthData(year, month + 1).then(render);
        };
        loadMonth();

        prenexIcons.forEach(icon => {
            icon.addEventListener('click', () => {
                month = icon.id === 'sec-calendar-prev' ? month - 1 : month + 1;
                if (month < 0 || month > 11) {
                    date = new Date(year, month, new Date().getDate());
                    year = date.getFullYear();
                    month = date.getMonth();
                } else {
                    date = new Date();
                }
                loadMonth();
            });
        });
    }

    function initClockData(popover) {
        const now = new Date();
        const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
        getMonthData(now.getFullYear(), now.getMonth() + 1).then(byDate => {
            const appts = (byDate[todayStr] && byDate[todayStr].appointments) ? byDate[todayStr].appointments : [];
            const total = appts.length;
            const done = appts.filter(a => a.status === 'Completed').length;
            const left = total - done;
            const set = (id, v) => { const el = popover.querySelector('#' + id); if (el) el.textContent = v; };
            set('secClockStatTotal', total);
            set('secClockStatDone', done);
            set('secClockStatLeft', left);
        });

        const nextEl = popover.querySelector('#secClockNext');
        if (!nextEl) return;
        const renderEmpty = (msg) => {
            nextEl.classList.add('is-empty');
            nextEl.style.cursor = '';
            nextEl.onclick = null;
            nextEl.innerHTML = `<i class="bi bi-calendar-check"></i><span class="clock-next-text">${msg}</span>`;
        };

        fetch('/api/secretary/next-appointments', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => {
                const items = (res && res.items) || [];
                let next = null;
                for (const a of items) {
                    if (!a.date || !a.start_time) continue;
                    const dt = new Date(`${a.date}T${a.start_time}`);
                    if (dt.getTime() > Date.now()) { next = { dt, a }; break; }
                }
                if (!next) { renderEmpty('لا مواعيد قادمة'); return; }
                const name = `${next.a.first_name || ''} ${next.a.last_name || ''}`.trim() || 'مريض';
                const apptId = next.a.id;
                const fmtEta = (ms) => {
                    if (ms <= 0) return 'الآن';
                    const mins = Math.round(ms / 60000);
                    if (mins < 60) return `خلال ${mins} د`;
                    const hrs = Math.floor(mins / 60), rem = mins % 60;
                    if (hrs < 24) return rem ? `خلال ${hrs}س ${rem}د` : `خلال ${hrs}س`;
                    const days = Math.floor(hrs / 24);
                    return `خلال ${days} يوم`;
                };
                const paint = () => {
                    nextEl.classList.remove('is-empty');
                    nextEl.innerHTML = `<i class="bi bi-hourglass-split"></i>`
                        + `<span class="clock-next-text">التالي: <span class="clock-next-name">${escapeHtml(name)}</span> `
                        + `<span class="clock-next-eta">${fmtEta(next.dt.getTime() - Date.now())}</span></span>`;
                };
                paint();
                if (apptId) {
                    nextEl.style.cursor = 'pointer';
                    nextEl.onclick = () => { window.location.href = `/secretary/bookings/${apptId}`; };
                }
                popover._intervals.push(setInterval(paint, 1000));
            })
            .catch(() => renderEmpty('تعذّر تحميل المواعيد'));
    }

    /* ======================================================================
     *  UPCOMING-APPOINTMENTS POPOVER
     * ==================================================================== */
    let appointmentsPopover = null;

    function createAppointmentsPopover() {
        if (appointmentsPopover) appointmentsPopover.remove();
        const popover = document.createElement('div');
        popover.className = 'appointments-popover';
        popover.id = 'appointmentsPopover';
        popover.setAttribute('dir', 'rtl');
        popover.innerHTML = `
            <div class="appointments-popover-content">
                <div class="appointments-popover-header"><h5>المواعيد القادمة</h5></div>
                <div class="appointments-popover-body" id="secApptPopoverBody">
                    <div class="appointments-loading">
                        <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">…</span></div>
                        <span>جارٍ تحميل المواعيد…</span>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(popover);
        appointmentsPopover = popover;
        positionAppointmentsPopover();
        loadAppointmentsPopover();
        const backdrop = document.createElement('div');
        backdrop.className = 'appointments-popover-backdrop';
        backdrop.addEventListener('click', closeAppointmentsPopover);
        document.body.appendChild(backdrop);
    }

    function positionAppointmentsPopover() {
        if (!appointmentsPopover) return;
        const noticeBar = document.querySelector('.notice-bar');
        if (!noticeBar) return;
        const rect = noticeBar.getBoundingClientRect();
        appointmentsPopover.style.top = (rect.bottom + 10) + 'px';
        appointmentsPopover.style.left = '50%';
        appointmentsPopover.style.transform = 'translateX(-50%)';
    }

    function closeAppointmentsPopover() {
        if (appointmentsPopover) { appointmentsPopover.remove(); appointmentsPopover = null; }
        const backdrop = document.querySelector('.appointments-popover-backdrop');
        if (backdrop) backdrop.remove();
    }

    function apptInitials(name) {
        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return '؟';
        if (parts.length === 1) return parts[0].slice(0, 2);
        return (parts[0][0] || '') + (parts[1][0] || '');
    }

    function loadAppointmentsPopover() {
        const container = document.getElementById('secApptPopoverBody');
        if (!container) return;
        fetch('/api/secretary/next-appointments', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => renderAppointmentsPopover((res && res.items) || []))
            .catch(() => {
                container.innerHTML = '<div class="appointments-empty"><i class="bi bi-exclamation-circle"></i><span>تعذّر تحميل المواعيد</span></div>';
            });
    }

    function renderAppointmentsPopover(appointments) {
        const container = document.getElementById('secApptPopoverBody');
        if (!container) return;
        if (!appointments.length) {
            container.innerHTML = '<div class="appointments-empty"><i class="bi bi-calendar-check"></i><span>لا مواعيد قادمة</span></div>';
            return;
        }
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const tomorrow = new Date(today); tomorrow.setDate(tomorrow.getDate() + 1);
        const apptUntil = (ds, st) => {
            if (!ds || !st) return '';
            const dt = new Date(`${ds}T${st}`);
            const ms = dt.getTime() - Date.now();
            if (ms <= 0) return 'الآن';
            const mins = Math.round(ms / 60000);
            if (mins < 60) return `خلال ${mins} د`;
            const hrs = Math.floor(mins / 60), rem = mins % 60;
            if (hrs < 24) return rem ? `خلال ${hrs}س ${rem}د` : `خلال ${hrs}س`;
            const days = Math.floor(hrs / 24);
            return `خلال ${days} يوم`;
        };

        let html = '';
        appointments.forEach((a, idx) => {
            const patientName = `${a.first_name || ''} ${a.last_name || ''}`.trim() || 'مريض';
            const sColor = CLOCK_STATUS_COLORS[a.status] || 'var(--accent)';
            const isNext = idx === 0;
            const timeStr = t12(a.start_time);

            let dateStr = '';
            if (a.date) {
                const ad = new Date(a.date + 'T00:00:00'); ad.setHours(0, 0, 0, 0);
                if (ad.getTime() === today.getTime()) dateStr = 'اليوم';
                else if (ad.getTime() === tomorrow.getTime()) dateStr = 'غداً';
                else dateStr = `${AR_WEEK_LONG[ad.getDay()]} ${ad.getDate()} ${AR_MONTHS[ad.getMonth()]}`;
            }
            const until = apptUntil(a.date, a.start_time);
            const doctor = a.doctor_name ? `<span class="appointment-item-type"><i class="bi bi-person-badge"></i> ${escapeHtml(a.doctor_name)}</span>` : '';

            html += `
                <div class="appointment-item ${isNext ? 'is-next' : ''}" data-appointment-id="${a.id}" style="--appt-color:${sColor}">
                    <div class="appointment-item-avatar" style="background:${sColor}">${escapeHtml(apptInitials(patientName))}</div>
                    <div class="appointment-item-content" onclick="window.location.href='/secretary/bookings/${a.id}'">
                        <div class="appointment-item-header">
                            <div class="appointment-item-patient">${escapeHtml(patientName)}${isNext ? '<span class="appt-pop-next"><i class="bi bi-stars"></i> التالي</span>' : ''}</div>
                            <div class="appointment-item-time">${timeStr}</div>
                        </div>
                        <div class="appointment-item-meta">
                            <span class="appointment-item-date"><i class="bi bi-calendar3"></i> ${escapeHtml(dateStr)}</span>
                            ${doctor}
                            ${until ? `<span class="appt-pop-until">${until}</span>` : ''}
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    }

    /* ======================================================================
     *  WIRING
     * ==================================================================== */
    function wire() {
        const clockTrigger = document.querySelector('.notice-bar .notice-bar-clock');
        if (clockTrigger) {
            clockTrigger.style.cursor = 'pointer';
            clockTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (document.getElementById('clockCalendarPopover')) closeClockCalendarPopover();
                else createClockCalendarPopover();
            });
        }
        const nextTrigger = document.getElementById('secNoticeNext');
        if (nextTrigger) {
            nextTrigger.style.cursor = 'pointer';
            nextTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (document.getElementById('appointmentsPopover')) closeAppointmentsPopover();
                else createAppointmentsPopover();
            });
        }
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (clockCalendarPopover) closeClockCalendarPopover();
            if (appointmentsPopover) closeAppointmentsPopover();
        });
        window.addEventListener('resize', () => {
            positionClockCalendarPopover();
            positionAppointmentsPopover();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wire);
    } else {
        wire();
    }
})();
