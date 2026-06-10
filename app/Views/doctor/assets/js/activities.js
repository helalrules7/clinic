/* ============================================================================
 * Activities page — dedicated, filterable, paginated view over
 * GET /api/activity/page (ActivityController::page). Shared by the doctor
 * (English) and secretary (Arabic, clinic-scoped) — language is taken from
 * <html lang>. Renders the SAME event line as the notification center's
 * Activity tab (mirrors formatActivityLine/arActivityVerb) so the two surfaces
 * stay consistent. Added 2026-06-10 (v12_perf — Activities consolidation).
 * ========================================================================== */
(function () {
    var elList = document.getElementById('activitiesPageList');
    if (!elList) return; // not on the activities page

    var isAr = (document.documentElement.getAttribute('lang') === 'ar') ||
               !!(window.V11I18n && window.V11I18n.isAr && window.V11I18n.isAr());

    // ---- rendering (mirror of notification-center.js, Arabic-aware) ----------
    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    var STATUS_AR = {
        Booked: 'محجوز', CheckedIn: 'تم الحضور', InProgress: 'جارٍ',
        Completed: 'مكتمل', Cancelled: 'ملغي', NoShow: 'لم يحضر',
        Rescheduled: 'معاد جدولته', Closed: 'مغلق'
    };
    function arForPatient(a) {
        var n = a.patient_name ? escHtml(a.patient_name) : ''; if (!n) return '';
        var w = a.patient_gender === 'Female' ? 'للمريضة' : (a.patient_gender === 'Male' ? 'للمريض' : 'للمريض/ة');
        return ' ' + w + ' ' + n;
    }
    function arBarePatient(a) {
        var n = a.patient_name ? escHtml(a.patient_name) : ''; if (!n) return '';
        var w = a.patient_gender === 'Female' ? 'المريضة' : (a.patient_gender === 'Male' ? 'المريض' : 'المريض/ة');
        return ' ' + w + ' ' + n;
    }
    function arActivityVerb(a) {
        var self = !!a.actor_is_self, did = self ? 'قمت ب' : 'قام ب';
        switch (a.action_code) {
            case 'booked':         return did + 'حجز موعد' + arForPatient(a);
            case 'status_changed': return did + 'تغيير حالة الموعد إلى «' + escHtml(STATUS_AR[a.detail] || a.detail || '') + '»' + arForPatient(a);
            case 'deleted':        return did + 'حذف موعد' + arForPatient(a);
            case 'rescheduled':    return did + 'إعادة جدولة الموعد' + arForPatient(a);
            case 'edited':         return did + 'تعديل الموعد' + arForPatient(a);
            case 'checked_in':     return (self ? 'سجّلت' : 'سجّل') + ' حضور' + (arBarePatient(a) || ' المريض');
            case 'note_added':     return did + 'إضافة ملاحظة طبية' + arForPatient(a);
            case 'alert_created':  return did + 'إنشاء تنبيه' + arForPatient(a);
            case 'todo_created':   return (self ? 'أضفت' : 'أضاف') + ' مهمة' + (a.detail ? ' «' + escHtml(a.detail) + '»' : '');
            default:               return null;
        }
    }
    function formatActivityLine(a) {
        if (!a) return '';
        if (a.text || a.message || a.title) return escHtml(a.text || a.message || a.title);
        var actorPart = a.actor_is_self
            ? '<strong>' + (isAr ? 'أنت' : 'You') + '</strong>'
            : (a.actor_name ? escHtml(a.actor_name) : (isAr ? 'مستخدم' : 'Someone'));
        if (isAr && a.action_code) { var verb = arActivityVerb(a); if (verb) return actorPart + ' ' + verb; }
        var parts = [actorPart];
        if (a.action) parts.push(escHtml(a.action));
        if (a.target_label) parts.push(escHtml(a.target_label));
        return parts.length > 1 ? parts.join(' ') : (isAr ? 'نشاط' : 'Activity');
    }
    var TYPE_COLOR = { appointment: '#6366F1', consultation_note: '#10B981', alert: '#F59E0B', todo: '#0EA5E9', system: '#64748B' };
    function colorFor(t) { return TYPE_COLOR[t] || TYPE_COLOR.system; }
    function timeAgo(ts) {
        if (!ts) return '';
        var then = new Date(ts).getTime(); if (!then) return '';
        var d = Math.max(0, (Date.now() - then) / 1000);
        if (d < 60)      return isAr ? 'الآن' : 'just now';
        if (d < 3600)    { var m = Math.floor(d / 60);     return isAr ? ('منذ ' + m + ' د') : (m + 'm ago'); }
        if (d < 86400)   { var h = Math.floor(d / 3600);   return isAr ? ('منذ ' + h + ' س') : (h + 'h ago'); }
        if (d < 604800)  { var dd = Math.floor(d / 86400); return isAr ? ('منذ ' + dd + ' ي') : (dd + 'd ago'); }
        var w = Math.floor(d / 604800);                    return isAr ? ('منذ ' + w + ' أ') : (w + 'w ago');
    }
    function rowHtml(a) {
        var type = a.type || 'system';
        return '<div class="act-row" role="listitem" data-type="' + escHtml(type) + '">'
            + '<span class="act-dot" style="background:' + colorFor(type) + '"></span>'
            + '<div class="act-body"><div class="act-text">' + formatActivityLine(a) + '</div>'
            + '<div class="act-time">' + escHtml(timeAgo(a.ts) || a.time_ago || '') + '</div></div></div>';
    }

    // ---- state + filters -----------------------------------------------------
    var state = { page: 1, perPage: 20, type: 'all', from: '', to: '', q: '', loading: false, hasMore: false };
    var elEmpty  = document.getElementById('activitiesEmpty'),
        elMore   = document.getElementById('activitiesLoadMore'),
        elType   = document.getElementById('actFilterType'),
        elFrom   = document.getElementById('actFilterFrom'),
        elTo     = document.getElementById('actFilterTo'),
        elSearch = document.getElementById('actFilterSearch'),
        elApply  = document.getElementById('actFilterApply'),
        elReset  = document.getElementById('actFilterReset');

    function buildQuery() {
        var p = new URLSearchParams();
        p.set('page', state.page); p.set('per_page', state.perPage);
        if (state.type && state.type !== 'all') p.set('type', state.type);
        if (state.from) p.set('from', state.from);
        if (state.to) p.set('to', state.to);
        if (state.q) p.set('q', state.q);
        return p.toString();
    }
    function load(reset) {
        if (state.loading) return;
        state.loading = true;
        if (reset) { state.page = 1; elList.setAttribute('aria-busy', 'true'); elList.innerHTML = '<div class="act-loading">' + (isAr ? 'جارٍ التحميل…' : 'Loading…') + '</div>'; }
        fetch('/api/activity/page?' + buildQuery(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var events = (d && d.events) || [];
                if (reset) elList.innerHTML = '';
                if (reset && events.length === 0) {
                    if (elEmpty) elEmpty.style.display = '';
                    elList.style.display = 'none';
                } else {
                    if (elEmpty) elEmpty.style.display = 'none';
                    elList.style.display = '';
                    elList.insertAdjacentHTML('beforeend', events.map(rowHtml).join(''));
                }
                state.hasMore = !!(d && d.has_more);
                if (elMore) elMore.style.display = state.hasMore ? '' : 'none';
            })
            .catch(function () {
                if (reset) elList.innerHTML = '<div class="act-error">' + (isAr ? 'تعذّر تحميل النشاط' : 'Failed to load activity') + '</div>';
            })
            .then(function () { state.loading = false; elList.removeAttribute('aria-busy'); });
    }
    function applyFilters() {
        state.type = elType ? elType.value : 'all';
        state.from = elFrom ? elFrom.value : '';
        state.to   = elTo ? elTo.value : '';
        state.q    = elSearch ? elSearch.value.trim() : '';
        load(true);
    }

    if (elApply)  elApply.addEventListener('click', applyFilters);
    if (elType)   elType.addEventListener('change', applyFilters);
    if (elFrom)   elFrom.addEventListener('change', applyFilters);
    if (elTo)     elTo.addEventListener('change', applyFilters);
    if (elSearch) elSearch.addEventListener('keydown', function (e) { if (e.key === 'Enter') applyFilters(); });
    if (elReset)  elReset.addEventListener('click', function () {
        if (elType) elType.value = 'all'; if (elFrom) elFrom.value = ''; if (elTo) elTo.value = ''; if (elSearch) elSearch.value = '';
        applyFilters();
    });
    if (elMore)   elMore.addEventListener('click', function () { if (state.loading || !state.hasMore) return; state.page++; load(false); });

    load(true);
})();
