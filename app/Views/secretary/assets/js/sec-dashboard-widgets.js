/**
 * Secretary dashboard — P1/P2 widgets (donut, revenue, quick actions, rich appts, tip of the day).
 */
(function () {
    'use strict';

    var STATUS_COLORS = {
        Booked: '#3b82f6', CheckedIn: '#22c55e', InProgress: '#f59e0b',
        Completed: '#06b6d4', Rescheduled: '#a78bfa', Missed: '#ef4444', Cancelled: '#94a3b8'
    };
    var STATUS_LABELS_AR = {
        Booked: 'في الانتظار', CheckedIn: 'تم الحضور', InProgress: 'جارية',
        Completed: 'مكتملة', Rescheduled: 'أُعيدت', Missed: 'لم يحضر', Cancelled: 'ملغاة'
    };

    var todayPage = 1;
    var todayPerPage = 5;
    var todayMeta = { total: 0, total_pages: 0 };
    var todayLoading = false;

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('ar-EG', { maximumFractionDigits: 0 });
    }

    function apptCountLabel(n) {
        n = Number(n || 0);
        if (n === 1) return 'موعد';
        if (n === 2) return 'موعدان';
        if (n >= 3 && n <= 10) return 'مواعيد';
        return 'موعداً';
    }

    function dashDonutSvg(segments, total, centerNum, centerSub) {
        var C = 2 * Math.PI * 42;
        var off = 0;
        var segs = segments.filter(function (s) { return s.value > 0; }).map(function (s) {
            var len = total > 0 ? (s.value / total) * C : 0;
            var c = '<circle cx="60" cy="60" r="42" fill="none" stroke="' + s.color + '" stroke-width="13"' +
                ' stroke-dasharray="' + len.toFixed(2) + ' ' + (C - len).toFixed(2) + '" stroke-dashoffset="' + (-off).toFixed(2) + '"' +
                ' transform="rotate(-90 60 60)" class="dash-donut-seg"/>';
            off += len;
            return c;
        }).join('');
        return '<div class="dash-donut-wrap sec-dash-donut-wrap">' +
            '<svg viewBox="0 0 120 120" class="dash-donut dash-donut--ring" aria-hidden="true">' +
            '<circle cx="60" cy="60" r="42" fill="none" stroke="var(--border)" stroke-width="13" opacity="0.35"/>' +
            segs +
            '</svg>' +
            '<div class="dash-donut-center arabic-text">' +
            '<span class="dash-donut-num">' + centerNum + '</span>' +
            '<span class="dash-donut-sub">' + esc(centerSub) + '</span>' +
            '</div></div>';
    }

    function renderStatusDonut(stats) {
        var body = document.getElementById('secDashStatusBody');
        if (!body || !stats) return;
        var order = ['Booked', 'CheckedIn', 'Completed', 'Missed'];
        var counts = {
            Booked: stats.booked || 0,
            CheckedIn: stats.checked_in || 0,
            Completed: stats.completed || 0,
            Missed: stats.missed || 0
        };
        var total = stats.total_appointments || 0;
        if (total === 0) {
            body.innerHTML = '<div class="dash-mini-empty"><i class="bi bi-calendar-check"></i><span class="arabic-text">لا مواعيد اليوم</span></div>';
            return;
        }
        var segments = order.map(function (s) {
            return { value: counts[s] || 0, color: STATUS_COLORS[s] || '#64748b' };
        });
        var legend = order.filter(function (s) { return counts[s]; }).map(function (s) {
            return '<div class="dash-legend-item"><span class="dash-legend-dot" style="background:' + STATUS_COLORS[s] + '"></span>' +
                '<span class="dash-legend-lbl arabic-text">' + STATUS_LABELS_AR[s] + '</span>' +
                '<span class="dash-legend-val">' + counts[s] + '</span></div>';
        }).join('');
        body.innerHTML = dashDonutSvg(segments, total, total, apptCountLabel(total)) +
            '<div class="dash-legend">' + legend + '</div>';
    }

    function renderRevenue(revenue) {
        var body = document.getElementById('secDashRevenueBody');
        if (!body) return;
        var bal = revenue || null;
        if (!bal) {
            body.innerHTML = '<div class="dash-mini-empty">—</div>';
            return;
        }
        var received = Number(bal.total_received || bal.totalReceived || 0);
        var tx = Number(bal.transactions_count || bal.transactionsCount || 0);
        var balance = Number(bal.current_balance || bal.currentBalance || 0);
        body.innerHTML =
            '<div class="dash-rev-main">' +
            '<span class="dash-rev-amt">' + fmtMoney(received) + '</span>' +
            '<span class="dash-rev-cur">ج.م</span></div>' +
            '<div class="dash-rev-sub arabic-text">المحصّل اليوم</div>' +
            '<div class="dash-rev-foot arabic-text">' +
            '<span><i class="bi bi-receipt"></i> ' + tx + ' عملية</span>' +
            '<span><i class="bi bi-wallet2"></i> ' + fmtMoney(balance) + ' رصيد</span></div>';
    }

    function statusBadgeClass(status) {
        var map = {
            Booked: 'bg-warning text-dark', CheckedIn: 'bg-info',
            Completed: 'bg-success', Cancelled: 'bg-secondary',
            Missed: 'bg-danger', InProgress: 'bg-primary'
        };
        return map[status] || 'bg-secondary';
    }

    function retagPatientHover(root) {
        if (window.patientHover && typeof window.patientHover.retag === 'function') {
            window.patientHover.retag(root || document.getElementById('secTodayApptContainer') || document);
        }
    }

    function renderTodayAppointmentsPage(items, meta) {
        var container = document.getElementById('secTodayApptContainer');
        var nav = document.getElementById('secTodayApptPagination');
        if (!container) return;

        meta = meta || {};
        todayMeta.total = meta.total != null ? meta.total : (items ? items.length : 0);
        todayMeta.total_pages = meta.total_pages != null ? meta.total_pages : 0;
        todayPage = meta.page || todayPage;

        if (!items || !items.length) {
            container.innerHTML = '<div class="appt-empty arabic-text"><i class="bi bi-calendar-x"></i><p>لا توجد مواعيد اليوم</p>' +
                '<a href="/secretary/bookings" class="btn btn-sm btn-primary">حجز موعد</a></div>';
            if (nav) nav.style.display = 'none';
            return;
        }

        var html = '<div class="list-group list-group-flush">';
        items.forEach(function (apt, idx) {
            var globalIdx = ((todayPage - 1) * todayPerPage) + idx;
            var sColor = STATUS_COLORS[apt.status] || '#64748b';
            var initials = ((apt.first_name || '').charAt(0) + (apt.last_name || '').charAt(0)).toUpperCase() || '?';
            var startT = apt.start_time ? String(apt.start_time).substr(0, 5) : '';
            var isNext = globalIdx === 0;
            var pid = apt.patient_id || apt.patientId || '';
            var fullName = ((apt.first_name || '') + ' ' + (apt.last_name || '')).trim();
            html += '<div class="list-group-item appt-list-item appt-card border-0 mb-2' + (isNext ? ' appt-next' : '') + '" style="--appt-color:' + sColor + '">' +
                '<div class="appt-row">' +
                '<div class="appt-avatar" style="background:' + sColor + '">' + esc(initials) + '</div>' +
                '<div class="appt-main">' +
                '<div class="appt-name-row">' +
                '<a href="/secretary/patients/' + pid + '" class="appt-name patient-hover-name" data-patient-id="' + pid + '">' +
                esc(fullName) + '</a>' +
                (isNext ? '<span class="appt-nextup-chip arabic-text"><i class="bi bi-stars"></i> التالي</span>' : '') +
                '</div>' +
                '<div class="appt-meta arabic-text">' +
                '<span class="appt-meta-time"><i class="bi bi-clock"></i> ' + startT + '</span>' +
                '<span class="appt-meta-sep">·</span>' +
                '<span class="appt-meta-vtype"><i class="bi bi-person-badge"></i> ' + esc(apt.doctor_name || '') + '</span>' +
                '</div></div>' +
                '<div class="appt-side">' +
                '<span class="badge ' + statusBadgeClass(apt.status) + ' arabic-text">' + esc(STATUS_LABELS_AR[apt.status] || apt.status || '') + '</span>' +
                '<div class="appt-actions">' +
                '<a href="/secretary/bookings/' + apt.id + '" class="btn btn-sm btn-outline-primary" title="عرض"><i class="bi bi-eye"></i></a>' +
                (apt.status === 'Booked' ? '<button type="button" class="btn btn-sm btn-outline-success" onclick="checkInPatient(' + apt.id + ')" title="تسجيل حضور"><i class="bi bi-check-circle"></i></button>' : '') +
                '</div></div></div></div>';
        });
        html += '</div>';
        container.innerHTML = html;
        retagPatientHover(container);

        if (nav) {
            var totalPages = todayMeta.total_pages || Math.ceil(todayMeta.total / todayPerPage);
            if (totalPages <= 1) {
                nav.style.display = 'none';
            } else {
                nav.style.display = '';
                var ul = nav.querySelector('.pagination') || nav;
                var itemsHtml = '';
                for (var p = 1; p <= totalPages; p++) {
                    itemsHtml += '<li class="page-item' + (p === todayPage ? ' active' : '') + '">' +
                        '<a class="page-link" href="#" data-page="' + p + '" aria-label="صفحة ' + p + '">' + p + '</a></li>';
                }
                ul.innerHTML = itemsHtml;
                ul.querySelectorAll('[data-page]').forEach(function (a) {
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        var page = parseInt(a.getAttribute('data-page'), 10) || 1;
                        loadTodayAppointmentsPage(page);
                    });
                });
            }
        }
    }

    function loadTodayAppointmentsPage(page) {
        if (todayLoading) return Promise.resolve();
        todayLoading = true;
        todayPage = page || 1;

        var container = document.getElementById('secTodayApptContainer');
        if (container) {
            container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        }

        return fetch('/api/secretary/today-appointments?page=' + todayPage + '&per_page=' + todayPerPage, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (res) {
                if (!res || !res.ok || !res.data) throw new Error('bad response');
                renderTodayAppointmentsPage(res.data.items || [], res.data);
            })
            .catch(function () {
                if (container) {
                    container.innerHTML = '<div class="appt-empty arabic-text text-danger"><i class="bi bi-exclamation-triangle"></i><p>تعذّر تحميل المواعيد</p></div>';
                }
            })
            .finally(function () {
                todayLoading = false;
            });
    }

    function renderTodayAppointments(list) {
        /* Legacy: full list from poll — re-fetch current AJAX page instead. */
        loadTodayAppointmentsPage(todayPage);
    }

    function loadDailyTips() {
        try {
            var el = document.getElementById('secDailyTipsData');
            if (!el) return [];
            var tips = JSON.parse(el.textContent || '[]');
            return Array.isArray(tips) ? tips : [];
        } catch (_) {
            return [];
        }
    }

    function tipIndexForToday(len) {
        var start = new Date(new Date().getFullYear(), 0, 0);
        var day = Math.floor((Date.now() - start.getTime()) / 86400000);
        return len > 0 ? day % len : 0;
    }

    function renderTipOfDay() {
        var tips = loadDailyTips();
        var el = document.getElementById('secTipText');
        if (!el || !tips.length) return;
        var tip = tips[tipIndexForToday(tips.length)];
        if (el.textContent.trim() !== tip) {
            el.textContent = tip;
        }
        var banner = document.getElementById('secTipOfDay');
        if (banner) banner.classList.add('sec-tip-banner--ready');
    }

    function refreshWidgets(data) {
        if (!data) return;
        if (data.stats) renderStatusDonut(data.stats);
        if (data.revenue) renderRevenue(data.revenue);
        loadTodayAppointmentsPage(todayPage);
    }

    window.secDashboardWidgets = {
        refresh: refreshWidgets,
        renderTodayAppointments: renderTodayAppointments,
        loadTodayAppointmentsPage: loadTodayAppointmentsPage,
        renderStatusDonut: renderStatusDonut,
        renderRevenue: renderRevenue
    };

    document.addEventListener('DOMContentLoaded', function () {
        renderTipOfDay();

        var stats = null;
        var apptsMeta = null;
        try {
            var sEl = document.getElementById('secDashboardStatsInitial');
            if (sEl) stats = JSON.parse(sEl.textContent || '{}');
        } catch (_) {}
        try {
            var mEl = document.getElementById('secTodayApptsMetaInitial');
            if (mEl) apptsMeta = JSON.parse(mEl.textContent || '{}');
        } catch (_) {}

        if (stats) renderStatusDonut(stats);

        if (apptsMeta && apptsMeta.items) {
            todayPage = apptsMeta.page || 1;
            renderTodayAppointmentsPage(apptsMeta.items, apptsMeta);
        } else {
            loadTodayAppointmentsPage(1);
        }

        var revenue = null;
        try {
            var rEl = document.getElementById('secRevenueInitial');
            if (rEl) revenue = JSON.parse(rEl.textContent || 'null');
        } catch (_) {}
        if (revenue) {
            renderRevenue(revenue);
        } else {
            fetch('/api/dashboard-summary', { credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (res) {
                    if (res && res.ok && res.data && res.data.dailyBalance) {
                        renderRevenue(res.data.dailyBalance);
                    }
                })
                .catch(function () {});
        }
    });
})();
