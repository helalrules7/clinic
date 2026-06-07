/* global window, document, sessionStorage, history, URLSearchParams */
/*
 * Shared Action Registry  (v11 — Command Palette & Quick-Action Dock unification)
 * --------------------------------------------------------------------------------
 * SINGLE source of truth for every system-wide action. Both the command palette
 * (cmdk.js) and the notification-center quick-action dock (notification-center.js)
 * read from this registry and execute through ActionRegistry.run() — so the two
 * surfaces can never drift again.
 *
 * Each action descriptor:
 *   id        kebab id, unique. The palette legacy ids are 'action:<id>'; byId()
 *             resolves both forms (and `aliases`).
 *   label     human label (palette row + dock aria/title)
 *   sub       palette secondary line
 *   icon      cmdk.js SVG icon key  (see ICONS map in cmdk.js)
 *   dockIcon  Bootstrap-Icons class for the dock button (e.g. 'bi-person-plus')
 *   keys      search synonyms (client-side fuzzy match)
 *   opener    name of a window.<fn> to invoke when present on the CURRENT page
 *             (the fast path — no navigation). Omit for nav-only actions.
 *   page      URL that OWNS the opener (or a plain nav target). Used for the
 *             cross-page handoff when `opener` isn't defined on this page.
 *   run       optional custom handler(payload) — overrides opener/page.
 *   palette   show in the command palette?  (default true)
 *   dock      dock slot order (1..N) or false to hide from the dock
 *   category  'create' | 'navigate' | 'toggle' | 'smart'  (for future grouping)
 *
 * THE CROSS-PAGE FIX
 *   run(id) tries window[opener]() first (instant, same page). If the opener
 *   isn't on this page, it navigates to `page` with ?action=<id> (and, for
 *   data-carrying actions, a sessionStorage `pendingAction` payload). On load,
 *   consumePending() fires the opener once the page defines it, then scrubs the
 *   URL so a refresh won't re-trigger.
 *
 * SYNC NOTE: SearchController::searchActions() (PHP) mirrors the *searchable*
 * subset for server-side results. Keep the two action sets in step when adding
 * palette actions.
 */
(function () {
    'use strict';

    var ACTIONS = [
        // ---- Create -------------------------------------------------------
        {
            id: 'new-patient', label: 'New patient', sub: 'Create a patient record',
            icon: 'user-plus', dockIcon: 'bi-person-plus',
            keys: ['new', 'patient', 'add', 'create'],
            opener: 'openNewPatientModal', page: '/doctor/patients',
            palette: true, dock: 1, category: 'create'
        },
        {
            id: 'new-booking', label: 'New booking', sub: 'Open the add-appointment modal',
            icon: 'calendar-plus', dockIcon: 'bi-calendar-plus',
            keys: ['new', 'booking', 'appointment', 'book', 'schedule', 'visit'],
            opener: 'openAddAppointmentModal', page: '/doctor/calendar',
            palette: true, dock: false, category: 'create'
        },
        {
            id: 'new-note', label: 'New quick note', sub: 'Open the quick-note modal',
            icon: 'sticky-note', dockIcon: 'bi-pencil-square',
            keys: ['new', 'note', 'quick'],
            opener: 'openQuickNoteModal', page: '/doctor/notes',
            palette: true, dock: 2, category: 'create'
        },
        {
            id: 'new-todo', label: 'New to-do', sub: 'Open the to-do drawer',
            icon: 'check-square', dockIcon: 'bi-check2-square',
            keys: ['new', 'todo', 'task'], aliases: ['todo'],
            opener: 'openTodoDrawer', page: '/doctor/todo',
            palette: true, dock: 6, category: 'create'
        },
        {
            // No real modal opener exists yet (openNewAlertModal is unimplemented),
            // so this is nav-only for now — matches the prior behaviour of landing
            // on /doctor/alerts. Wire a real opener here in the next round.
            id: 'new-alert', label: 'New alert', sub: 'Create a patient alert',
            icon: 'bell-plus',
            keys: ['new', 'alert', 'reminder'],
            page: '/doctor/alerts',
            palette: true, dock: false, category: 'create'
        },

        // ---- Navigate / drawers (dock) ------------------------------------
        {
            id: 'notes-drawer', label: 'Open notes', sub: 'Open the notes drawer',
            icon: 'sticky-note', dockIcon: 'bi-journal-text',
            keys: ['notes', 'drawer', 'open'],
            opener: 'openNotesDrawer', page: '/doctor/notes',
            palette: false, dock: 3, category: 'navigate'
        },
        {
            id: 'calendar', label: 'Open calendar', sub: 'Go to the calendar',
            icon: 'page', dockIcon: 'bi-calendar3',
            keys: ['calendar', 'appointments', 'schedule'],
            page: '/doctor/calendar',
            palette: false, dock: 4, category: 'navigate'
        },
        {
            id: 'boards', label: 'Open boards', sub: 'Go to the boards',
            icon: 'page', dockIcon: 'bi-grid-3x3-gap-fill',
            keys: ['board', 'boards', 'kanban'],
            page: '/doctor/board',
            palette: false, dock: 5, category: 'navigate'
        },

        // ---- Toggles ------------------------------------------------------
        {
            id: 'focus-mode', label: 'Toggle focus mode', sub: 'Hide chrome and concentrate',
            icon: 'eye',
            keys: ['focus', 'mode', 'zen'],
            opener: 'toggleFocusMode',
            palette: true, dock: false, category: 'toggle'
        },
        {
            id: 'theme-picker', label: 'Open theme picker', sub: 'Change palette and mode',
            icon: 'palette',
            keys: ['theme', 'palette', 'color', 'dark', 'light'],
            opener: 'openThemePicker',
            palette: true, dock: false, category: 'toggle'
        },
        {
            id: 'keyboard-help', label: 'Keyboard shortcuts', sub: 'Show all shortcuts',
            icon: 'keyboard',
            keys: ['keyboard', 'shortcuts', 'help', 'keys'],
            opener: 'openKeyboardHelp',
            palette: true, dock: false, category: 'toggle'
        },

        // ---- Smart / compound -------------------------------------------
        {
            // Dynamic: surfaced by the palette when a phone number is typed, and
            // run with the number as payload. Lives on the calendar page (owns the
            // booking APIs + doctor/clinic context). Always shows a confirm step.
            id: 'book-by-phone', label: 'Book nearest slot by phone',
            sub: 'Find the soonest free appointment for a phone number',
            icon: 'calendar-plus',
            keys: ['book', 'phone', 'nearest', 'slot', 'appointment', 'quick'],
            opener: 'openBookByPhone', page: '/doctor/calendar',
            palette: false, dock: false, category: 'smart', smart: true,
            usage: 'Type a phone number in the palette, then pick “Book nearest slot”. It finds the patient (or creates one), books the soonest free slot, and asks you to confirm.'
        },
        {
            id: 'go-to-today', label: 'Go to today', sub: 'Open the calendar on today',
            icon: 'calendar', keys: ['today', 'now', 'calendar', 'go'],
            page: '/doctor/calendar',
            palette: true, dock: false, category: 'smart', smart: true,
            usage: 'Jump straight to today’s calendar.'
        },
        {
            id: 'daily-closure', label: 'Daily closure', sub: 'Open the daily-closure page',
            icon: 'page', keys: ['daily', 'closure', 'close', 'cash', 'end of day'],
            page: '/doctor/daily-closure',
            palette: true, dock: false, category: 'smart', smart: true,
            usage: 'Open today’s cash/visit closure.'
        },
        {
            id: 'reports', label: 'Reports', sub: 'Open reports & statistics',
            icon: 'page', keys: ['reports', 'stats', 'statistics', 'analytics', 'revenue'],
            page: '/doctor/reports',
            palette: true, dock: false, category: 'smart', smart: true,
            usage: 'Open the reports & statistics dashboard.'
        },
        {
            id: 'payments', label: 'Payments', sub: 'Open payments & invoices',
            icon: 'page', keys: ['payments', 'invoices', 'unpaid', 'money', 'billing'],
            page: '/doctor/payments',
            palette: true, dock: false, category: 'smart', smart: true,
            usage: 'Open payments & outstanding invoices.'
        }
    ];

    // ----- Lookup -----------------------------------------------------------
    function normId(id) {
        if (id == null) return '';
        id = String(id);
        return id.indexOf('action:') === 0 ? id.slice(7) : id;
    }

    function byId(id) {
        var key = normId(id);
        for (var i = 0; i < ACTIONS.length; i++) {
            var a = ACTIONS[i];
            if (a.id === key) return a;
            if (a.aliases && a.aliases.indexOf(key) !== -1) return a;
        }
        return null;
    }

    function isSecretaryLayout() {
        return document.documentElement.getAttribute('data-layout') === 'secretary';
    }

    function mapPageForLayout(page) {
        if (!page || !isSecretaryLayout()) return page;
        var map = {
            '/doctor/patients': '/secretary/patients',
            '/doctor/calendar': '/secretary/bookings',
            '/doctor/payments': '/secretary/payments'
        };
        var keys = Object.keys(map);
        for (var i = 0; i < keys.length; i++) {
            var from = keys[i];
            var to = map[from];
            if (page === from || page.indexOf(from + '/') === 0 || page.indexOf(from + '?') === 0) {
                return page.replace(from, to);
            }
        }
        return page;
    }

    function all() { return ACTIONS.slice(); }

    function paletteActions() {
        return ACTIONS.filter(function (a) { return a.palette !== false; });
    }

    function dockActions() {
        return ACTIONS
            .filter(function (a) { return a.dock !== false && a.dock != null; })
            .sort(function (x, y) { return x.dock - y.dock; });
    }

    function smartActions() {
        return ACTIONS.filter(function (a) { return a.smart === true; });
    }

    // ----- Pending payload handoff -----------------------------------------
    function stashPayload(id, payload) {
        try {
            sessionStorage.setItem('pendingAction', JSON.stringify({ id: id, payload: payload }));
        } catch (e) { /* private mode / quota — fall back to query only */ }
    }
    function readStash() {
        try { return JSON.parse(sessionStorage.getItem('pendingAction') || 'null'); }
        catch (e) { return null; }
    }
    function clearStash() {
        try { sessionStorage.removeItem('pendingAction'); } catch (e) { /* noop */ }
    }
    function scrubUrl() {
        try {
            var params = new URLSearchParams(window.location.search || '');
            if (!params.has('action')) return;
            params.delete('action');
            var qs = params.toString();
            history.replaceState({}, '', window.location.pathname + (qs ? ('?' + qs) : '') + window.location.hash);
        } catch (e) { /* noop */ }
    }

    // ----- Execute ----------------------------------------------------------
    // Returns true if the action was handled (run/opened/navigated).
    function run(idOrAction, payload) {
        var a = (typeof idOrAction === 'object' && idOrAction) ? idOrAction : byId(idOrAction);
        if (!a) return false;

        if (typeof a.run === 'function') { a.run(payload); return true; }

        // Fast path — the owning modal/drawer is already on this page.
        if (a.opener && typeof window[a.opener] === 'function') {
            window[a.opener](payload);
            return true;
        }

        // Handoff / navigation.
        if (a.page) {
            var url = mapPageForLayout(a.page);
            if (a.opener) {
                // We need the target page to auto-open the modal on arrival.
                if (payload != null) stashPayload(a.id, payload);
                url += (url.indexOf('?') < 0 ? '?' : '&') + 'action=' + encodeURIComponent(a.id);
            }
            window.location.href = url;
            return true;
        }

        return false;
    }

    // ----- Consume a pending action on page load ---------------------------
    // Fires the opener for ?action=<id> (or a sessionStorage payload) once the
    // current page actually defines that opener. Polls briefly because page
    // scripts may register their window.<opener> inside their own DOMContentLoaded.
    function consumePending() {
        var params;
        try { params = new URLSearchParams(window.location.search || ''); }
        catch (e) { params = null; }

        var actionId = params ? params.get('action') : null;
        var stash = readStash();
        if (!actionId && stash) actionId = stash.id;
        if (!actionId) return;

        var a = byId(actionId);
        if (!a || !a.opener) { clearStash(); return; }

        var payload = (stash && normId(stash.id) === normId(actionId)) ? stash.payload : null;
        var tries = 0;

        (function attempt() {
            if (typeof window[a.opener] === 'function') {
                clearStash();
                scrubUrl();
                try { window[a.opener](payload); } catch (e) {
                    if (window.console && console.error) console.error('[ActionRegistry] opener failed:', a.opener, e);
                }
                return;
            }
            if (tries++ < 25) {            // ~2.5s of grace for lazy openers
                setTimeout(attempt, 100);
            } else {
                // Opener never showed up on this page — give up cleanly.
                clearStash();
                scrubUrl();
            }
        })();
    }

    // ----- Public API -------------------------------------------------------
    window.ActionRegistry = {
        all: all,
        byId: byId,
        run: run,
        paletteActions: paletteActions,
        dockActions: dockActions,
        smartActions: smartActions
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', consumePending);
    } else {
        consumePending();
    }
})();
