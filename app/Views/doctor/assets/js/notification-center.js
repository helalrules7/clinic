/**
 * Notification Center
 * iOS-style notification panel attached to the topbar bell (#notificationsToggle).
 * Public hooks: window.notifCenter = { open, close, refresh, toggle }
 */
(function () {
    'use strict';

    try {
    var POLL_MS = 60 * 1000;
    var REDUCED_MOTION = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // --- DOM ----------------------------------------------------------------
    var panel = document.getElementById('notifPanel');
    if (!panel) return; // view not included on this page

    var panelLang = panel.getAttribute('data-notif-lang') || 'en';
    var isAr = panelLang === 'ar';

    var body = panel.querySelector('#notifBody');
    var closeBtn = panel.querySelector('#notifCloseBtn');
    var clearAllBtn = panel.querySelector('#notifClearAllBtn');
    var tabsEl = panel.querySelector('.notif-tabs');
    var tabBtns = panel.querySelectorAll('.notif-tab');
    var tabIndicator = panel.querySelector('.notif-tabs__indicator');
    var tabCountEl = panel.querySelector('#notifTabCount');
    var dock = panel.querySelector('.notif-dock');
    var bell = document.getElementById('notificationsToggle');
    var badge = document.getElementById('notificationsBadge');

    var tplRow = panel.querySelector('#notifRowTemplate');
    var tplStack = panel.querySelector('#notifStackTemplate');
    var tplActivity = panel.querySelector('#notifActivityTemplate');
    var tplBucket = panel.querySelector('#notifBucketTemplate');
    var tplSnooze = panel.querySelector('#notifSnoozeTemplate');

    // --- State --------------------------------------------------------------
    var state = {
        open: false,
        tab: 'notifications',
        notifications: null,   // grouped { pinned: [], buckets: { today:[], yesterday:[], week:[], older:[] } }
        activity: null,
        loading: false,
        pollTimer: null,
        bgPollTimer: null,
        unreadCount: 0,
        snoozePopover: null,
        snoozeAnchor: null,
        expandedStacks: new Set()
    };

    // --- Helpers ------------------------------------------------------------
    function api(url, opts) {
        opts = opts || {};
        opts.credentials = 'same-origin';
        opts.headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }, opts.headers || {});
        if (opts.body && typeof opts.body !== 'string') {
            opts.body = JSON.stringify(opts.body);
        }
        return fetch(url, opts).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            var ct = r.headers.get('content-type') || '';
            return ct.indexOf('application/json') !== -1 ? r.json() : r.text();
        });
    }

    function escapeHTML(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function timeAgo(iso) {
        if (!iso) return '';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        var diff = Math.max(0, (Date.now() - d.getTime()) / 1000);
        if (isAr) {
            if (diff < 5) return 'الآن';
            if (diff < 60) return 'منذ ' + Math.round(diff) + ' ث';
            if (diff < 90) return 'منذ دقيقة';
            if (diff < 3600) return 'منذ ' + Math.round(diff / 60) + ' د';
            if (diff < 5400) return 'منذ ساعة';
            if (diff < 86400) return 'منذ ' + Math.round(diff / 3600) + ' س';
            if (diff < 172800) return 'أمس';
            if (diff < 604800) return 'منذ ' + Math.round(diff / 86400) + ' ي';
            return d.toLocaleDateString('ar-EG');
        }
        if (diff < 5) return 'just now';
        if (diff < 60) return Math.round(diff) + 's ago';
        if (diff < 90) return '1m ago';
        if (diff < 3600) return Math.round(diff / 60) + 'm ago';
        if (diff < 5400) return '1h ago';
        if (diff < 86400) return Math.round(diff / 3600) + 'h ago';
        if (diff < 172800) return 'yesterday';
        if (diff < 604800) return Math.round(diff / 86400) + 'd ago';
        return d.toLocaleDateString();
    }

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function fmtLocalDT(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) +
            ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    /**
     * Type-to-accent mapping. Returns a CSS color expression.
     * Falls back to var(--accent) for unknown types.
     */
    var TYPE_COLORS = {
        appointment: 'var(--palette-indigo, var(--accent))',
        reminder: 'var(--palette-amber, #f59e0b)',
        alert: 'var(--palette-rose, #f43f5e)',
        message: 'var(--palette-sky, #0ea5e9)',
        system: 'var(--palette-slate, #64748b)',
        payment: 'var(--palette-emerald, #10b981)',
        patient: 'var(--palette-violet, #8b5cf6)',
        board: 'var(--palette-teal, #14b8a6)',
        todo: 'var(--palette-emerald, #10b981)',
        note: 'var(--palette-amber, #f59e0b)'
    };
    var TYPE_ICONS = {
        appointment: 'bi-calendar-event',
        reminder: 'bi-alarm',
        alert: 'bi-exclamation-triangle',
        message: 'bi-chat-dots',
        system: 'bi-gear',
        payment: 'bi-cash-coin',
        patient: 'bi-person-circle',
        board: 'bi-grid-3x3-gap',
        todo: 'bi-check2-square',
        note: 'bi-sticky'
    };

    function colorFor(type) { return TYPE_COLORS[type] || 'var(--accent)'; }
    function iconFor(type) { return TYPE_ICONS[type] || 'bi-bell'; }

    function bucketKey(iso) {
        if (!iso) return 'older';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return 'older';
        var now = new Date();
        var startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
        var t = d.getTime();
        var dayMs = 86400000;
        if (t >= startOfToday) return 'today';
        if (t >= startOfToday - dayMs) return 'yesterday';
        if (t >= startOfToday - 6 * dayMs) return 'week';
        return 'older';
    }

    var BUCKET_LABELS = {
        today: isAr ? 'اليوم' : 'Today',
        yesterday: isAr ? 'أمس' : 'Yesterday',
        week: isAr ? 'هذا الأسبوع' : 'This week',
        older: isAr ? 'أقدم' : 'Older',
        pinned: isAr ? 'مثبّت' : 'Pinned'
    };

    // --- Open / close -------------------------------------------------------
    function open() {
        if (state.open) return;
        state.open = true;
        panel.hidden = false;
        panel.removeAttribute('aria-hidden');
        // force reflow so transition triggers
        // eslint-disable-next-line no-unused-expressions
        panel.offsetWidth;
        panel.classList.add('is-open');
        if (bell) bell.setAttribute('aria-expanded', 'true');
        document.addEventListener('mousedown', onDocClick, true);
        document.addEventListener('keydown', onKeyDown, true);
        startPolling();
        loadActiveTab(true);
        positionPanel();
        window.addEventListener('resize', positionPanel);
    }

    function close() {
        if (!state.open) return;
        state.open = false;
        panel.classList.remove('is-open');
        closeSnoozePopover();
        if (bell) bell.setAttribute('aria-expanded', 'false');
        document.removeEventListener('mousedown', onDocClick, true);
        document.removeEventListener('keydown', onKeyDown, true);
        window.removeEventListener('resize', positionPanel);
        stopPolling();
        var done = function () {
            panel.hidden = true;
            panel.setAttribute('aria-hidden', 'true');
            panel.removeEventListener('transitionend', done);
        };
        if (REDUCED_MOTION) {
            done();
        } else {
            panel.addEventListener('transitionend', done);
            // safety fallback
            setTimeout(done, 350);
        }
    }

    function toggle() { state.open ? close() : open(); }

    function positionPanel() {
        if (!bell) return;
        if (window.matchMedia('(max-width: 575.98px)').matches) {
            panel.style.top = '';
            panel.style.right = '';
            return;
        }
        var rect = bell.getBoundingClientRect();
        var top = Math.max(8, rect.bottom + 10);
        var right = Math.max(8, window.innerWidth - rect.right);
        panel.style.top = top + 'px';
        panel.style.right = right + 'px';
    }

    // --- Outside click / keys ----------------------------------------------
    function onDocClick(e) {
        if (!state.open) return;
        if (panel.contains(e.target)) return;
        if (bell && bell.contains(e.target)) return;
        if (state.snoozePopover && state.snoozePopover.contains(e.target)) return;
        close();
    }
    function onKeyDown(e) {
        if (e.key === 'Escape') {
            if (state.snoozePopover) { closeSnoozePopover(); return; }
            close();
        }
    }

    // --- Polling ------------------------------------------------------------
    //
    // Two-tier strategy so the bell badge stays current even when the panel
    // is closed:
    //
    //   1. ALWAYS-ON background poll (every BG_POLL_MS, default 30s):
    //      hits the lightweight /api/notifications/unread-count and refreshes
    //      the bell badge. Runs whenever the document is visible, regardless
    //      of whether the panel is open.
    //
    //   2. ACTIVE poll while the panel is open (every POLL_MS, default 60s):
    //      hits /api/notifications/grouped and re-renders the active tab.
    //      Replaces the background poll for the duration the panel is open.
    //
    // Both pause when the tab is hidden (visibilitychange handler resumes them).
    var BG_POLL_MS = 30 * 1000;

    function startBackgroundPolling() {
        stopBackgroundPolling();
        state.bgPollTimer = setInterval(function () {
            if (document.visibilityState !== 'visible') return;
            if (state.open) return; // active poll covers this case
            loadUnreadCount();
        }, BG_POLL_MS);
    }
    function stopBackgroundPolling() {
        if (state.bgPollTimer) { clearInterval(state.bgPollTimer); state.bgPollTimer = null; }
    }

    function startPolling() {
        stopPolling();
        state.pollTimer = setInterval(function () {
            if (document.visibilityState !== 'visible') return;
            if (state.tab === 'notifications') loadNotifications(false);
            else loadActivity(false);
        }, POLL_MS);
    }
    function stopPolling() {
        if (state.pollTimer) { clearInterval(state.pollTimer); state.pollTimer = null; }
    }

    /**
     * Lightweight refresh: hits /api/notifications/unread-count and updates
     * ONLY the bell badge. Used by background polling + by row actions that
     * change unread state (we can't always trust the local cache after the
     * server-side cron may have inserted new rows).
     */
    function loadUnreadCount() {
        return api('/api/notifications/unread-count')
            .then(function (data) {
                if (!data) return;
                var n = (data.unread_count != null) ? +data.unread_count
                      : (data.count != null)        ? +data.count
                      : (data.unread != null)       ? +data.unread
                      : null;
                if (n == null || isNaN(n)) return;
                state.unreadCount = n;
                applyBadgeNumber(n);
            })
            .catch(noop);
    }

    function applyBadgeNumber(n) {
        if (!badge) return;
        if (n > 0) {
            badge.textContent = n > 99 ? '99+' : String(n);
            badge.style.display = '';
        } else {
            badge.textContent = '0';
            badge.style.display = 'none';
        }
        if (tabCountEl) {
            if (n > 0) {
                tabCountEl.hidden = false;
                tabCountEl.textContent = n > 99 ? '99+' : String(n);
            } else {
                tabCountEl.hidden = true;
            }
        }
    }

    // --- Loaders ------------------------------------------------------------
    function loadActiveTab(forceSpinner) {
        if (state.tab === 'notifications') loadNotifications(forceSpinner);
        else loadActivity(forceSpinner);
    }

    function loadNotifications(showSpinner) {
        if (showSpinner) showSkeleton();
        state.loading = true;
        return api('/api/notifications/grouped')
            .then(function (data) {
                state.notifications = normalizeGrouped(data);
                if (state.tab === 'notifications') renderNotifications();
                updateBadge();
            })
            .catch(function () {
                if (state.tab === 'notifications') renderError('notifications');
            })
            .then(function () {
                state.loading = false;
                updateClearAllBtn();
            });
    }

    /**
     * ActivityController returns { success, events: [...] }.
     * Older drafts used { items: [...] } — accept both (+ raw array).
     */
    function parseActivityPayload(data) {
        if (Array.isArray(data)) return data;
        if (!data) return [];
        if (Array.isArray(data.events)) return data.events;
        if (Array.isArray(data.items)) return data.items;
        return [];
    }

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
    // "للمريضة فلانة" / "للمريض فلان" / "للمريض/ة فلان" (gender-aware), prefixed.
    function arForPatient(a) {
        var n = a.patient_name ? escHtml(a.patient_name) : '';
        if (!n) return '';
        var w = a.patient_gender === 'Female' ? 'للمريضة' : (a.patient_gender === 'Male' ? 'للمريض' : 'للمريض/ة');
        return ' ' + w + ' ' + n;
    }
    // "المريضة فلانة" (no «لـ» prefix) — for the check-in phrasing.
    function arBarePatient(a) {
        var n = a.patient_name ? escHtml(a.patient_name) : '';
        if (!n) return '';
        var w = a.patient_gender === 'Female' ? 'المريضة' : (a.patient_gender === 'Male' ? 'المريض' : 'المريض/ة');
        return ' ' + w + ' ' + n;
    }
    // Arabic activity verb phrase (incl. the patient). Returns null for unknown codes
    // so the caller falls back to the English `action`. `did` = «قمت بـ» (you) / «قام بـ» (other).
    function arActivityVerb(a) {
        var self = !!a.actor_is_self;
        var did = self ? 'قمت ب' : 'قام ب';
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

    // Returns an HTML string (inserted via innerHTML). The actor is a bold "You"/"أنت"
    // when the logged-in user performed it. For the Arabic panel the whole line is
    // built in Arabic (gender-aware) from the structured action_code; the English
    // panel uses the server-rendered `action` + patient label. Everything is escaped.
    function formatActivityLine(a) {
        if (!a) return '';
        if (a.text || a.message || a.title) {
            return escHtml(a.text || a.message || a.title);
        }
        var actorPart = a.actor_is_self
            ? '<strong>' + (isAr ? 'أنت' : 'You') + '</strong>'
            : (a.actor_name ? escHtml(a.actor_name) : (isAr ? 'مستخدم' : 'Someone'));

        if (isAr && a.action_code) {
            var verb = arActivityVerb(a);
            if (verb) return actorPart + ' ' + verb;
        }

        // English / fallback
        var parts = [actorPart];
        if (a.action) parts.push(escHtml(a.action));
        if (a.target_label) parts.push(escHtml(a.target_label));
        if (parts.length > 1) return parts.join(' ');
        return isAr ? 'نشاط' : 'Activity';
    }

    function loadActivity(showSpinner) {
        if (showSpinner) showSkeleton();
        state.loading = true;
        return api('/api/activity')
            .then(function (data) {
                state.activity = parseActivityPayload(data);
                if (state.tab === 'activity') renderActivity();
            })
            .catch(function () {
                if (state.tab === 'activity') renderError('activity');
            })
            .then(function () { state.loading = false; });
    }

    /**
     * Accepts either:
     *   { buckets: {today, yesterday, thisWeek|week, older}, [pinned: [...]] }
     *   { items: [...] }
     *   { groups: [{key,items,...}, ...] }
     *   raw array
     *
     * The roaya backend returns:
     *   { success: true,
     *     buckets: { today: [...], yesterday: [...], thisWeek: [...], older: [...] },
     *     unread_count: N }
     *
     * (No `pinned` array — pinned items live inside their natural bucket and are
     * surfaced by `pinned_at`. We collect them into `result.pinned` ourselves.)
     */
    function normalizeGrouped(raw) {
        var result = { pinned: [], buckets: { today: [], yesterday: [], week: [], older: [] } };
        if (!raw) return result;

        if (Array.isArray(raw)) {
            raw.forEach(function (n) { placeIntoBuckets(result, n); });
            return result;
        }
        if (raw.buckets) {
            // Map both naming conventions so future backend renames don't break us.
            var keyMap = {
                today:     'today',
                yesterday: 'yesterday',
                thisWeek:  'week',
                week:      'week',
                older:     'older'
            };
            Object.keys(raw.buckets).forEach(function (apiKey) {
                var localKey = keyMap[apiKey];
                if (!localKey) return;
                (raw.buckets[apiKey] || []).forEach(function (n) {
                    var norm = normalizeNotif(n);
                    if (!norm) return;
                    if (norm.pinned) result.pinned.push(norm);
                    else result.buckets[localKey].push(norm);
                });
            });
            // Backend may also send an explicit `pinned` array — merge if present.
            if (Array.isArray(raw.pinned)) {
                raw.pinned.forEach(function (n) {
                    var norm = normalizeNotif(n);
                    if (norm) result.pinned.push(norm);
                });
            }
            return result;
        }
        if (Array.isArray(raw.items)) {
            raw.items.forEach(function (n) { placeIntoBuckets(result, n); });
            return result;
        }
        if (Array.isArray(raw.groups)) {
            raw.groups.forEach(function (g) {
                (g.items || []).forEach(function (n) { placeIntoBuckets(result, n); });
            });
            return result;
        }
        return result;
    }

    function normalizeNotif(n) {
        if (!n) return null;
        return {
            id: n.id != null ? String(n.id) : null,
            type: n.type || n.category || 'system',
            title: n.title || n.subject || '',
            body: n.body || n.message || n.excerpt || '',
            created_at: n.created_at || n.createdAt || n.time || null,
            // Pinned: prefer the explicit boolean if set, else infer from
            // pinned_at timestamp returned by the backend.
            pinned: !!(n.pinned || n.pinned_at),
            pinned_at: n.pinned_at || null,
            // Read: backend uses is_read (1/0); also accept the boolean alias.
            read: !!(n.read || n.is_read),
            // Snoozed: backend returns the timestamp, not a boolean.
            snoozed: !!(n.snoozed || n.snoozed_until),
            snoozed_until: n.snoozed_until || null,
            stack_size: n.stack_size || n.count || 1,
            stack_ids: n.stack_ids || n.children_ids || null,
            meta: n.meta || null,
            // Backend returns `link` for deep-links; older code used `url`.
            url: n.url || n.href || n.link || null,
            icon: n.icon || null,
            patient_id: n.patient_id || null,
            patient_name: n.patient_name || null,
            // related_type/related_id power deep-actions (e.g. chat → open conversation)
            related_type: n.related_type || n.relatedType || null,
            related_id: n.related_id || n.relatedId || null,
            time_ago: n.time_ago || null
        };
    }

    function placeIntoBuckets(result, raw) {
        var n = normalizeNotif(raw);
        if (!n) return;
        if (n.pinned) { result.pinned.push(n); return; }
        var key = bucketKey(n.created_at);
        result.buckets[key].push(n);
    }

    // --- Rendering ----------------------------------------------------------
    function showSkeleton() {
        body.innerHTML =
            '<div class="notif-skeleton" aria-hidden="true">' +
            '<div class="notif-skeleton__row"></div>' +
            '<div class="notif-skeleton__row"></div>' +
            '<div class="notif-skeleton__row"></div>' +
            '</div>';
    }

    function renderError(which) {
        body.innerHTML =
            '<div class="notif-empty notif-empty--error">' +
            '<i class="bi bi-cloud-slash" aria-hidden="true"></i>' +
            '<p>Could not load ' + escapeHTML(which) + '.</p>' +
            '<button type="button" class="notif-empty__retry" data-retry="' + which + '">Try again</button>' +
            '</div>';
    }

    function renderEmpty(msg, icon) {
        body.innerHTML =
            '<div class="notif-empty">' +
            '<i class="bi ' + escapeHTML(icon || 'bi-check2-circle') + '" aria-hidden="true"></i>' +
            '<p>' + escapeHTML(msg) + '</p>' +
            '</div>';
    }

    function updateClearAllBtn() {
        if (!clearAllBtn) return;
        var show = state.tab === 'notifications' && state.notifications && !isStateEmpty();
        clearAllBtn.hidden = !show;
        clearAllBtn.disabled = !!state.loading;
    }

    function handleClearAll() {
        if (state.tab !== 'notifications' || state.loading) return;
        if (!state.notifications || isStateEmpty()) return;

        state.loading = true;
        updateClearAllBtn();

        api('/api/notifications/clear-all', { method: 'DELETE' })
            .then(function () { return loadNotifications(false); })
            .then(loadUnreadCount)
            .catch(function () { renderError('notifications'); })
            .finally(function () {
                state.loading = false;
                updateClearAllBtn();
            });
    }

    function renderNotifications() {
        if (!state.notifications) return;
        var data = state.notifications;
        var totalItems = data.pinned.length +
            data.buckets.today.length + data.buckets.yesterday.length +
            data.buckets.week.length + data.buckets.older.length;
        if (totalItems === 0) {
            renderEmpty(isAr ? 'لا توجد إشعارات جديدة.' : 'You are all caught up.', 'bi-check2-circle');
            updateClearAllBtn();
            return;
        }
        body.innerHTML = '';
        var frag = document.createDocumentFragment();
        if (data.pinned.length) frag.appendChild(renderBucket('pinned', data.pinned));
        ['today', 'yesterday', 'week', 'older'].forEach(function (k) {
            if (data.buckets[k].length) frag.appendChild(renderBucket(k, data.buckets[k]));
        });
        body.appendChild(frag);
        updateClearAllBtn();
    }

    function renderBucket(key, items) {
        var node = tplBucket.content.firstElementChild.cloneNode(true);
        node.dataset.bucket = key;
        node.querySelector('[data-title]').textContent = BUCKET_LABELS[key] || key;
        var list = node.querySelector('[data-list]');
        items.forEach(function (n) {
            if (n.stack_size > 1 && !state.expandedStacks.has(n.id)) {
                list.appendChild(renderStack(n));
            } else {
                list.appendChild(renderRow(n));
            }
        });
        return node;
    }

    function renderRow(n) {
        var node = tplRow.content.firstElementChild.cloneNode(true);
        node.dataset.id = n.id || '';
        node.dataset.type = n.type;
        if (n.read) node.classList.add('is-read');
        if (n.pinned) node.classList.add('is-pinned');
        if (n.snoozed) node.classList.add('is-snoozed');
        node.style.setProperty('--row-tint', colorFor(n.type));

        var tile = node.querySelector('[data-tile]');
        tile.innerHTML = '<i class="bi ' + iconFor(n.type) + '" aria-hidden="true"></i>';

        node.querySelector('[data-title]').textContent = n.title || '(untitled)';
        node.querySelector('[data-body]').textContent = n.body || '';
        node.querySelector('[data-time]').textContent = timeAgo(n.created_at);

        var pinBtn = node.querySelector('[data-act="pin"]');
        if (pinBtn) {
            var pinIcon = pinBtn.querySelector('i');
            pinIcon.className = 'bi ' + (n.pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle');
            pinBtn.setAttribute('aria-label', n.pinned ? 'Unpin' : 'Pin');
            pinBtn.title = n.pinned ? 'Unpin' : 'Pin';
        }
        var readBtn = node.querySelector('[data-act="read"]');
        if (readBtn && n.read) {
            readBtn.querySelector('i').className = 'bi bi-check2-all';
            readBtn.setAttribute('aria-label', 'Read');
            readBtn.title = 'Read';
        }
        return node;
    }

    function renderStack(n) {
        var node = tplStack.content.firstElementChild.cloneNode(true);
        node.dataset.id = n.id || '';
        node.dataset.type = n.type;
        node.style.setProperty('--row-tint', colorFor(n.type));
        var tile = node.querySelector('[data-tile]');
        tile.innerHTML = '<i class="bi ' + iconFor(n.type) + '" aria-hidden="true"></i>';
        node.querySelector('[data-title]').textContent = n.title || (n.type + ' updates');
        node.querySelector('[data-time]').textContent = timeAgo(n.created_at);
        node.querySelector('[data-count]').textContent = n.stack_size + ' items';
        return node;
    }

    function renderActivity() {
        var items = state.activity || [];
        if (!items.length) {
            renderEmpty(isAr ? 'لا يوجد نشاط حديث.' : 'No recent activity.', 'bi-activity');
            return;
        }
        body.innerHTML = '';
        var wrap = document.createElement('div');
        wrap.className = 'notif-activity-list';
        wrap.setAttribute('role', 'list');
        items.forEach(function (a) {
            var node = tplActivity.content.firstElementChild.cloneNode(true);
            var type = a.type || a.event_type || 'system';
            node.dataset.type = type;
            var dot = node.querySelector('[data-dot]');
            dot.style.background = colorFor(type);
            node.querySelector('[data-text]').innerHTML = formatActivityLine(a);
            // Prefer the client's localized timeAgo (the server's time_ago is English).
            node.querySelector('[data-time]').textContent = timeAgo(a.ts || a.created_at || a.time) || a.time_ago;
            wrap.appendChild(node);
        });
        body.appendChild(wrap);
    }

    function updateBadge() {
        if (!badge || !state.notifications) return;
        var unread = 0;
        var all = [].concat(
            state.notifications.pinned,
            state.notifications.buckets.today,
            state.notifications.buckets.yesterday,
            state.notifications.buckets.week,
            state.notifications.buckets.older
        );
        all.forEach(function (n) { if (!n.read && !n.snoozed) unread += (n.stack_size || 1); });
        if (unread > 0) {
            badge.textContent = unread > 99 ? '99+' : String(unread);
            badge.style.display = '';
        } else {
            badge.textContent = '0';
            badge.style.display = 'none';
        }
        if (tabCountEl) {
            if (unread > 0) {
                tabCountEl.hidden = false;
                tabCountEl.textContent = unread > 99 ? '99+' : String(unread);
            } else {
                tabCountEl.hidden = true;
            }
        }
    }

    // --- Tabs ---------------------------------------------------------------
    function setTab(name) {
        if (state.tab === name) return;
        state.tab = name;
        tabBtns.forEach(function (b) {
            var active = b.dataset.tab === name;
            b.classList.toggle('is-active', active);
            b.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        positionTabIndicator();
        body.setAttribute('aria-labelledby', name === 'activity' ? 'notifTabActivity' : 'notifTabNotifications');
        if (name === 'activity') {
            if (!state.activity) loadActivity(true);
            else renderActivity();
        } else {
            if (!state.notifications) loadNotifications(true);
            else renderNotifications();
        }
        updateClearAllBtn();
    }

    function positionTabIndicator() {
        if (!tabIndicator) return;
        var active = tabsEl.querySelector('.notif-tab.is-active');
        if (!active) return;
        var rect = active.getBoundingClientRect();
        var parentRect = tabsEl.getBoundingClientRect();
        tabIndicator.style.width = rect.width + 'px';
        tabIndicator.style.transform = 'translateX(' + (rect.left - parentRect.left) + 'px)';
    }

    // --- Row actions --------------------------------------------------------
    function handleRowAction(rowEl, act, btnEl) {
        var id = rowEl.dataset.id;
        if (!id) return;
        if (act === 'snooze') {
            openSnoozePopover(rowEl, btnEl);
            return;
        }
        if (act === 'pin') {
            var wasPinned = rowEl.classList.contains('is-pinned');
            var url = '/api/notifications/' + encodeURIComponent(id) + (wasPinned ? '/unpin' : '/pin');
            api(url, { method: 'POST' })
                .then(function () { return loadNotifications(false); })
                .catch(noop);
            return;
        }
        if (act === 'read') {
            rowEl.classList.add('is-read');
            // Mutate the cached row so updateBadge() recomputes correctly.
            markCachedAsRead(id);
            updateBadge();
            api('/api/notifications/' + encodeURIComponent(id) + '/read', { method: 'PUT' })
                .then(loadUnreadCount)        // reconcile with server truth
                .catch(noop);
            return;
        }
        if (act === 'delete') {
            // Optimistic remove
            rowEl.classList.add('is-removing');
            var removeNode = function () {
                if (rowEl.parentNode) rowEl.parentNode.removeChild(rowEl);
                cleanupEmptyBuckets();
                // Update local state
                if (state.notifications) {
                    removeFromGrouped(state.notifications, id);
                    updateBadge();
                    if (isStateEmpty()) renderEmpty(isAr ? 'لا توجد إشعارات جديدة.' : 'You are all caught up.', 'bi-check2-circle');
                }
            };
            if (REDUCED_MOTION) removeNode();
            else setTimeout(removeNode, 220);
            api('/api/notifications/' + encodeURIComponent(id), { method: 'DELETE' })
                .then(loadUnreadCount)
                .catch(function () {
                    // On error, refetch to restore truth
                    loadNotifications(false);
                });
            return;
        }
    }

    function removeFromGrouped(grouped, id) {
        var remove = function (arr) {
            var idx = arr.findIndex(function (x) { return x.id === id; });
            if (idx !== -1) arr.splice(idx, 1);
        };
        remove(grouped.pinned);
        Object.keys(grouped.buckets).forEach(function (k) { remove(grouped.buckets[k]); });
    }

    /**
     * Find a notification in the cached `state.notifications` (pinned +
     * every bucket) and set its `read` flag to true. Returns true if found.
     * Without this, marking-as-read updates the DOM class but updateBadge()
     * still counts the row as unread because it reads from the cache.
     */
    function markCachedAsRead(id) {
        if (!state.notifications) return false;
        var sId = String(id);
        var hit = function (arr) {
            for (var i = 0; i < arr.length; i++) {
                if (arr[i] && arr[i].id === sId) { arr[i].read = true; return true; }
            }
            return false;
        };
        var found = hit(state.notifications.pinned);
        if (!found) {
            var b = state.notifications.buckets;
            found = hit(b.today) || hit(b.yesterday) || hit(b.week) || hit(b.older);
        }
        return found;
    }

    function isStateEmpty() {
        if (!state.notifications) return true;
        var g = state.notifications;
        return !g.pinned.length && !g.buckets.today.length && !g.buckets.yesterday.length &&
            !g.buckets.week.length && !g.buckets.older.length;
    }

    function cleanupEmptyBuckets() {
        body.querySelectorAll('.notif-bucket').forEach(function (b) {
            if (!b.querySelector('.notif-row')) b.remove();
        });
    }

    // --- Snooze popover -----------------------------------------------------
    function openSnoozePopover(rowEl, anchor) {
        closeSnoozePopover();
        var frag = tplSnooze.content.firstElementChild.cloneNode(true);
        frag.dataset.id = rowEl.dataset.id;
        // hints
        var now = new Date();
        var in1h = new Date(now.getTime() + 3600 * 1000);
        var in4h = new Date(now.getTime() + 4 * 3600 * 1000);
        var tomorrow9 = new Date(now); tomorrow9.setDate(now.getDate() + 1); tomorrow9.setHours(9, 0, 0, 0);
        var nextWeek = new Date(now); nextWeek.setDate(now.getDate() + 7); nextWeek.setHours(9, 0, 0, 0);
        var setHint = function (sel, d) {
            var el = frag.querySelector('[data-hint="' + sel + '"]');
            if (el) el.textContent = d.toLocaleString([], { hour: 'numeric', minute: '2-digit', weekday: 'short' });
        };
        setHint('1h', in1h);
        setHint('4h', in4h);
        setHint('tomorrow', tomorrow9);
        setHint('week', nextWeek);

        document.body.appendChild(frag);
        state.snoozePopover = frag;
        state.snoozeAnchor = anchor;
        positionSnoozePopover();

        frag.addEventListener('click', function (e) {
            var opt = e.target.closest('.notif-snooze__opt');
            if (opt) {
                var kind = opt.dataset.snooze;
                if (kind === 'custom') {
                    var custom = frag.querySelector('.notif-snooze__custom');
                    custom.hidden = !custom.hidden;
                    var input = custom.querySelector('.notif-snooze__input');
                    if (!custom.hidden) {
                        input.value = fmtLocalDT(in1h).replace(' ', 'T');
                        input.focus();
                    }
                    return;
                }
                applySnooze(rowEl.dataset.id, kind, null);
                return;
            }
            if (e.target.closest('.notif-snooze__apply')) {
                var inputEl = frag.querySelector('.notif-snooze__input');
                if (inputEl && inputEl.value) {
                    applySnooze(rowEl.dataset.id, 'custom', inputEl.value);
                }
            }
        });

        // close on outside click within tick
        setTimeout(function () {
            document.addEventListener('mousedown', onSnoozeOutside, true);
        }, 0);
    }

    function onSnoozeOutside(e) {
        if (!state.snoozePopover) return;
        if (state.snoozePopover.contains(e.target)) return;
        if (state.snoozeAnchor && state.snoozeAnchor.contains(e.target)) return;
        closeSnoozePopover();
    }

    function closeSnoozePopover() {
        document.removeEventListener('mousedown', onSnoozeOutside, true);
        if (state.snoozePopover && state.snoozePopover.parentNode) {
            state.snoozePopover.parentNode.removeChild(state.snoozePopover);
        }
        state.snoozePopover = null;
        state.snoozeAnchor = null;
    }

    function positionSnoozePopover() {
        if (!state.snoozePopover || !state.snoozeAnchor) return;
        var pop = state.snoozePopover;
        var anchorRect = state.snoozeAnchor.getBoundingClientRect();
        // Show next to anchor on desktop, bottom-sheet on mobile
        if (window.matchMedia('(max-width: 575.98px)').matches) {
            pop.classList.add('notif-snooze--sheet');
            return;
        }
        pop.style.position = 'fixed';
        var top = anchorRect.bottom + 6;
        pop.style.top = top + 'px';
        // align right edge to anchor
        var popRect = pop.getBoundingClientRect();
        var left = anchorRect.right - popRect.width;
        left = Math.max(8, Math.min(left, window.innerWidth - popRect.width - 8));
        pop.style.left = left + 'px';
        // Flip up if overflowing
        if (top + popRect.height > window.innerHeight - 8) {
            pop.style.top = Math.max(8, anchorRect.top - popRect.height - 6) + 'px';
        }
    }

    function applySnooze(id, kind, customISO) {
        var until;
        var now = new Date();
        switch (kind) {
            case '1h': until = new Date(now.getTime() + 3600 * 1000); break;
            case '4h': until = new Date(now.getTime() + 4 * 3600 * 1000); break;
            case 'tomorrow':
                until = new Date(now); until.setDate(now.getDate() + 1); until.setHours(9, 0, 0, 0); break;
            case 'week':
                until = new Date(now); until.setDate(now.getDate() + 7); until.setHours(9, 0, 0, 0); break;
            case 'custom':
                until = customISO ? new Date(customISO) : null;
                if (!until || isNaN(until.getTime())) return;
                break;
            default: return;
        }
        closeSnoozePopover();
        api('/api/notifications/' + encodeURIComponent(id) + '/snooze', {
            method: 'POST',
            body: { until: until.toISOString() }
        })
            .then(function () { return loadNotifications(false); })
            .catch(noop);
    }

    // --- Dock actions -------------------------------------------------------
    // Rebuild the quick-action dock from the shared registry so the palette and
    // the dock can never drift. Falls back to the server-rendered buttons if the
    // registry script didn't load.
    function renderDock() {
        if (!dock) return;
        if (!(window.ActionRegistry && typeof window.ActionRegistry.dockActions === 'function')) return;
        var items = window.ActionRegistry.dockActions();
        if (!items.length) return;
        var html = '';
        for (var i = 0; i < items.length; i++) {
            var a = items[i];
            var label = a.label || a.id;
            var icon = a.dockIcon || 'bi-lightning-charge';
            html += '<button type="button" class="qa-btn" data-action="' + escapeHTML(a.id) + '"'
                  + ' aria-label="' + escapeHTML(label) + '" title="' + escapeHTML(label) + '">'
                  + '<i class="bi ' + escapeHTML(icon) + '" aria-hidden="true"></i></button>';
        }
        dock.innerHTML = html;
    }

    function handleDockAction(action) {
        close();
        // Primary path: shared registry (opens here or hands off to owning page).
        if (window.ActionRegistry && window.ActionRegistry.byId(action)) {
            window.ActionRegistry.run(action);
            return;
        }
        // Legacy fallback (registry script unavailable).
        switch (action) {
            case 'new-patient':
                if (typeof window.openNewPatientModal === 'function') window.openNewPatientModal();
                else window.location.href = '/doctor/patients';
                break;
            case 'new-note':
                if (typeof window.openQuickNoteModal === 'function') window.openQuickNoteModal();
                else if (window.quickNotes && typeof window.quickNotes.openCreate === 'function') window.quickNotes.openCreate();
                else window.location.href = '/doctor/notes';
                break;
            case 'notes-drawer':
                if (typeof window.openNotesDrawer === 'function') window.openNotesDrawer();
                else if (window.notesDrawer && typeof window.notesDrawer.open === 'function') window.notesDrawer.open();
                else window.location.href = '/doctor/notes';
                break;
            case 'calendar':
                window.location.href = isAr && document.documentElement.getAttribute('data-layout') === 'secretary'
                    ? '/secretary/bookings' : '/doctor/calendar';
                break;
            case 'boards':
                window.location.href = '/doctor/board';
                break;
            case 'todo':
                if (typeof window.openTodoDrawer === 'function') window.openTodoDrawer();
                else if (window.todoDrawer && typeof window.todoDrawer.open === 'function') window.todoDrawer.open();
                else window.location.href = '/doctor/todo';
                break;
        }
    }

    // --- Stack expand -------------------------------------------------------
    function toggleStack(rowEl) {
        var id = rowEl.dataset.id;
        if (!id) return;
        if (state.expandedStacks.has(id)) state.expandedStacks.delete(id);
        else state.expandedStacks.add(id);
        renderNotifications();
    }

    // --- Wire up events -----------------------------------------------------
    function bindEvents() {
        // v11 transition guard: neutralise the legacy #notificationsPanel
        // that main.js opens via its own toggleNotifications. Two steps:
        //
        //   1. Yank the legacy panel + overlay OUT of the DOM. main.js still
        //      holds references but they're now detached — classList writes
        //      to them are invisible.
        //
        //   2. Attach OUR click handler in the CAPTURE phase with
        //      stopImmediatePropagation. The bell already has a bubble
        //      listener from main.js (registered at parse-time, before this
        //      script runs). Capture-phase fires first on the target, and
        //      stopImmediatePropagation kills every subsequent listener
        //      regardless of phase — so main.js's toggleNotifications
        //      never runs on this click.
        //
        // We deliberately do NOT cloneNode-replace the bell: the
        // #notificationsBadge is a CHILD of the bell, so cloning would
        // orphan the badge element AND every cached reference to it
        // (mine + main.js's), leaving badge updates writing to nothing.
        ['notificationsPanel', 'notificationsOverlay'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.parentNode) {
                el.parentNode.removeChild(el);
            }
        });

        if (bell) {
            bell.setAttribute('aria-haspopup', 'dialog');
            bell.setAttribute('aria-expanded', 'false');
            bell.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                toggle();
            }, true); // capture phase — fires before main.js's bubble listener
        }
        if (closeBtn) closeBtn.addEventListener('click', close);
        if (clearAllBtn) clearAllBtn.addEventListener('click', handleClearAll);

        tabsEl.addEventListener('click', function (e) {
            var t = e.target.closest('.notif-tab');
            if (!t) return;
            setTab(t.dataset.tab);
        });

        body.addEventListener('click', function (e) {
            var retry = e.target.closest('[data-retry]');
            if (retry) {
                if (retry.dataset.retry === 'notifications') loadNotifications(true);
                else loadActivity(true);
                return;
            }
            var stackExpand = e.target.closest('.notif-row__expand');
            if (stackExpand) {
                var stackRow = stackExpand.closest('.notif-row--stack');
                if (stackRow) toggleStack(stackRow);
                return;
            }
            var actBtn = e.target.closest('.notif-act');
            if (actBtn) {
                e.stopPropagation();
                var row = actBtn.closest('.notif-row');
                if (row) handleRowAction(row, actBtn.dataset.act, actBtn);
                return;
            }
            // Row click — open url if provided, else mark as read
            var rowEl = e.target.closest('.notif-row:not(.notif-row--stack)');
            if (rowEl) {
                var id = rowEl.dataset.id;
                if (!id) return;
                if (state.notifications) {
                    var found = findInGrouped(state.notifications, id);
                    // chat notification → open the conversation in the chat widget (no navigation)
                    if (found && found.related_type === 'chat' && found.related_id &&
                        window.RoayaChat && typeof window.RoayaChat.openConversation === 'function') {
                        window.RoayaChat.openConversation(found.related_id);
                        if (!rowEl.classList.contains('is-read')) {
                            rowEl.classList.add('is-read'); markCachedAsRead(id); updateBadge();
                            api('/api/notifications/' + encodeURIComponent(id) + '/read', { method: 'PUT' }).then(loadUnreadCount).catch(noop);
                        }
                        return;
                    }
                    if (found && found.url) { window.location.href = found.url; return; }
                    // To-do notifications without a deep-link (the secretary has no
                    // tasks page) open the shared to-do drawer instead. Then fall
                    // through to mark-as-read below.
                    if (found && found.type && found.type.indexOf('todo') !== -1) {
                        if (typeof window.openTodoDrawer === 'function') window.openTodoDrawer();
                        else if (window.todoDrawer && typeof window.todoDrawer.open === 'function') window.todoDrawer.open();
                    }
                }
                if (!rowEl.classList.contains('is-read')) {
                    rowEl.classList.add('is-read');
                    markCachedAsRead(id);
                    updateBadge();
                    api('/api/notifications/' + encodeURIComponent(id) + '/read', { method: 'PUT' })
                        .then(loadUnreadCount)
                        .catch(noop);
                }
            }
        });

        dock.addEventListener('click', function (e) {
            var btn = e.target.closest('.qa-btn');
            if (!btn) return;
            handleDockAction(btn.dataset.action);
        });

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState !== 'visible') return;
            // Tab became visible again — get the truth from the server.
            // If the panel is open, do the heavy grouped refresh; otherwise
            // just the lightweight badge count.
            if (state.open) loadActiveTab(false);
            else loadUnreadCount();
        });
    }

    function findInGrouped(grouped, id) {
        var pools = [grouped.pinned, grouped.buckets.today, grouped.buckets.yesterday, grouped.buckets.week, grouped.buckets.older];
        for (var i = 0; i < pools.length; i++) {
            for (var j = 0; j < pools[i].length; j++) {
                if (pools[i][j].id === id) return pools[i][j];
            }
        }
        return null;
    }

    function noop() {}

    function refresh() {
        if (state.tab === 'activity') return loadActivity(false);
        return loadNotifications(false);
    }

    // --- Init ---------------------------------------------------------------
    function bootstrap() {
        renderDock();
        bindEvents();
        positionTabIndicator();
        // Initial badge: lightweight count call, doesn't require the panel open.
        loadUnreadCount();
        // Always-on background poll keeps the badge fresh while the panel
        // is closed — every 30s, fires only when the tab is visible.
        startBackgroundPolling();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }

    window.notifCenter = {
        open: open,
        close: close,
        toggle: toggle,
        refresh: refresh
    };
    } catch (err) {
        // Defensive: log to console so future regressions are visible in DevTools,
        // but never let a partial init silently kill the bell's old fallback.
        if (window.console && console.error) console.error('[notifCenter] init error:', err);
    }
})();
