/* global */
(function () {
    'use strict';

    // ----- Elements -----
    const root      = document.getElementById('cmdk');
    if (!root) return;

    const panel     = document.getElementById('cmdkPanel');
    const backdrop  = document.getElementById('cmdkBackdrop');
    const input     = document.getElementById('cmdkInput');
    const closeBtn  = document.getElementById('cmdkClose');
    const tabsBox   = document.getElementById('cmdkTabs');
    const tabs      = tabsBox ? Array.from(tabsBox.querySelectorAll('.cmdk__tab')) : [];
    const results   = document.getElementById('cmdkResults');
    const emptyTpl  = document.getElementById('cmdkEmpty');
    const emptyHTML = emptyTpl ? emptyTpl.outerHTML : '';

    // ----- State -----
    let isOpen     = false;
    let activeScope = 'all';
    let debounceId = 0;
    let abortCtrl  = null;
    let lastQuery  = '';
    let activeIdx  = -1;
    let flatRows   = [];     // flat array of selectable rows (for arrow keys)
    let lastFocused = null;  // element to restore focus to on close

    const DEBOUNCE_MS = 150;

    // ----- Local action catalogue (always available, fuzzy-matched client side) -----
    // Source of truth: window.ActionRegistry (actions-registry.js, loaded first).
    // The hardcoded list below is only a fallback if that script failed to load,
    // so the palette degrades gracefully instead of going blank.
    const LOCAL_ACTIONS_FALLBACK = [
        { id: 'action:new-patient',    label: 'New patient',         sub: 'Create a patient record',     icon: 'user-plus',  keys: ['new','patient','add','create'] },
        { id: 'action:new-todo',       label: 'New to-do',           sub: 'Open the to-do drawer',       icon: 'check-square', keys: ['new','todo','task'] },
        { id: 'action:new-note',       label: 'New quick note',      sub: 'Open the quick-note modal',   icon: 'sticky-note', keys: ['new','note','quick'] },
        { id: 'action:new-alert',      label: 'New alert',           sub: 'Create a patient alert',      icon: 'bell-plus',  keys: ['new','alert','reminder'] },
        { id: 'action:focus-mode',     label: 'Toggle focus mode',   sub: 'Hide chrome and concentrate', icon: 'eye',        keys: ['focus','mode','zen'] },
        { id: 'action:theme-picker',   label: 'Open theme picker',   sub: 'Change palette and mode',     icon: 'palette',    keys: ['theme','palette','color','dark','light'] },
        { id: 'action:keyboard-help',  label: 'Keyboard shortcuts',  sub: 'Show all shortcuts',          icon: 'keyboard',   keys: ['keyboard','shortcuts','help','keys'] }
    ];

    // Pull the palette-visible actions from the shared registry, mapped to the
    // cmdk row shape (legacy 'action:<id>' ids). Falls back when registry absent.
    function getLocalActions() {
        if (window.ActionRegistry && typeof window.ActionRegistry.paletteActions === 'function') {
            return window.ActionRegistry.paletteActions().map(function (a) {
                return { id: 'action:' + a.id, label: a.label, sub: a.sub, icon: a.icon, keys: a.keys || [] };
            });
        }
        return LOCAL_ACTIONS_FALLBACK;
    }

    // ----- Utility -----
    function escapeHTML(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function debounce(fn, wait) {
        return function () {
            const args = arguments;
            clearTimeout(debounceId);
            debounceId = setTimeout(function () { fn.apply(null, args); }, wait);
        };
    }

    function highlightMatch(text, query) {
        const safe = escapeHTML(text);
        if (!query) return safe;
        const q = query.trim();
        if (!q) return safe;
        try {
            const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
            return safe.replace(re, '<mark class="cmdk__mark">$1</mark>');
        } catch (e) {
            return safe;
        }
    }

    // SVG icon set — small, decorative, aria-hidden
    const ICONS = {
        'user':         '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'user-plus':    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>',
        'page':         '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        'check-square': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
        'sticky-note':  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10z"/><polyline points="15 21 15 15 21 15"/></svg>',
        'bell-plus':    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
        'eye':          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
        'palette':      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".9" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".9" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".9" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".9" fill="currentColor"/><path d="M12 2a10 10 0 0 0 0 20 2.5 2.5 0 0 0 2.5-2.5c0-.66-.26-1.27-.69-1.71a2.5 2.5 0 0 1 1.77-4.29H18a4 4 0 0 0 4-4 10 10 0 0 0-10-8z"/></svg>',
        'keyboard':     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h0M10 10h0M14 10h0M18 10h0M6 14h12"/></svg>',
        'todo':         '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 12 8 17 21 4"/></svg>',
        'arrow':        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>'
    };

    function iconFor(name) {
        return ICONS[name] || ICONS['arrow'];
    }

    function iconForKind(kind) {
        switch (kind) {
            case 'patient': return 'user';
            case 'page':    return 'page';
            case 'action':  return 'arrow';
            case 'todo':    return 'check-square';
            default:        return 'arrow';
        }
    }

    function sectionLabel(kind) {
        switch (kind) {
            case 'patient': return 'Patients';
            case 'page':    return 'Pages';
            case 'action':  return 'Actions';
            case 'todo':    return 'To-dos';
            default:        return kind;
        }
    }

    // ----- Fetch -----
    function fetchPalette(q, scope) {
        if (abortCtrl) { try { abortCtrl.abort(); } catch (e) {} }
        abortCtrl = (typeof AbortController !== 'undefined') ? new AbortController() : null;

        const params = new URLSearchParams();
        params.set('q', q || '');
        if (scope && scope !== 'all') params.set('scope', scope);

        return fetch('/api/search/palette?' + params.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: abortCtrl ? abortCtrl.signal : undefined
        })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return null;
                return { error: true };
            });
    }

    // ----- Local action filtering -----
    function localActionsFor(q) {
        const catalogue = getLocalActions();
        if (!q) return catalogue.slice(0, 5);
        const needle = q.toLowerCase();
        return catalogue.filter(function (a) {
            if (a.label.toLowerCase().indexOf(needle) !== -1) return true;
            if (a.sub && a.sub.toLowerCase().indexOf(needle) !== -1) return true;
            for (let i = 0; i < a.keys.length; i++) {
                if (a.keys[i].indexOf(needle) !== -1) return true;
            }
            return false;
        });
    }

    // ----- Render -----
    function renderEmptyState(html) {
        results.innerHTML = html;
        flatRows = [];
        activeIdx = -1;
    }

    function renderError() {
        renderEmptyState(
            '<div class="cmdk__empty cmdk__empty--err">' +
                '<p class="cmdk__empty-title">Couldn&rsquo;t reach search</p>' +
                '<p class="cmdk__empty-sub">Check your connection and try again.</p>' +
            '</div>'
        );
    }

    function renderNoResults(q) {
        renderEmptyState(
            '<div class="cmdk__empty">' +
                '<p class="cmdk__empty-title">No results for &ldquo;' + escapeHTML(q) + '&rdquo;</p>' +
                '<p class="cmdk__empty-sub">Try a different search or scope.</p>' +
            '</div>'
        );
    }

    function normalizeResponse(data, q) {
        // Defensive: support several response shapes from the backend.
        // The roaya SearchController returns:
        //   { success: true, results: { patients:[], pages:[], actions:[], todos:[] } }
        // We also support the un-wrapped form and a flat array, so the
        // palette keeps working if SearchController is ever simplified.
        const out = { patients: [], pages: [], actions: [], todos: [] };
        if (!data || data.error) return out;

        // Unwrap { results: {...} } if present (the canonical shape).
        const src = (data && typeof data === 'object' && data.results && typeof data.results === 'object')
            ? data.results
            : data;

        if (Array.isArray(src)) {
            src.forEach(function (item) {
                if (!item) return;
                const k = item.kind || item.type;
                if (k === 'patient' && out.patients.length < 8) out.patients.push(item);
                else if (k === 'page' && out.pages.length < 6) out.pages.push(item);
                else if (k === 'action' && out.actions.length < 6) out.actions.push(item);
                else if (k === 'todo' && out.todos.length < 6) out.todos.push(item);
            });
        } else {
            if (Array.isArray(src.patients)) out.patients = src.patients.slice(0, 8);
            if (Array.isArray(src.pages))    out.pages    = src.pages.slice(0, 6);
            if (Array.isArray(src.actions))  out.actions  = src.actions.slice(0, 6);
            if (Array.isArray(src.todos))    out.todos    = src.todos.slice(0, 6);
        }

        // Merge / replace actions with the local catalogue when scope allows.
        if (activeScope === 'all' || activeScope === 'actions') {
            const locals = localActionsFor(q);
            const seen = {};
            out.actions.forEach(function (a) { if (a && a.id) seen[a.id] = true; });
            locals.forEach(function (a) {
                if (!seen[a.id]) out.actions.push({
                    kind: 'action',
                    id: a.id,
                    label: a.label,
                    sub: a.sub,
                    icon: a.icon
                });
            });
            // Limit
            out.actions = out.actions.slice(0, 7);
        }

        return out;
    }

    function buildRow(kind, item, query) {
        const label = item.label || item.name || item.title || '';
        const sub   = item.sub || item.sublabel || item.subtitle || item.url || '';
        const url   = item.url || item.href || item.link || '';
        const id    = item.id || item.action || '';
        const iconName = item.icon || iconForKind(kind);

        const dataKind = escapeHTML(kind);
        const dataUrl  = escapeHTML(url);
        const dataId   = escapeHTML(id);

        return (
            '<div class="cmdk__row" role="option" aria-selected="false" tabindex="-1"' +
                ' data-kind="' + dataKind + '"' +
                ' data-url="' + dataUrl + '"' +
                ' data-id="' + dataId + '">' +
                '<span class="cmdk__row-icon cmdk__row-icon--' + dataKind + '" aria-hidden="true">' + iconFor(iconName) + '</span>' +
                '<span class="cmdk__row-body">' +
                    '<span class="cmdk__row-label">' + highlightMatch(label, query) + '</span>' +
                    (sub ? '<span class="cmdk__row-sub">' + highlightMatch(sub, query) + '</span>' : '') +
                '</span>' +
                '<span class="cmdk__row-kbd" aria-hidden="true">&crarr;</span>' +
            '</div>'
        );
    }

    function buildSection(kind, items, query) {
        if (!items || !items.length) return '';
        const heading = '<div class="cmdk__section-head">' + escapeHTML(sectionLabel(kind)) + '</div>';
        const rows = items.map(function (it) { return buildRow(kind, it, query); }).join('');
        return '<section class="cmdk__section" data-section="' + escapeHTML(kind) + '">' + heading + rows + '</section>';
    }

    function render(data, q) {
        const total = (data.patients.length + data.pages.length + data.actions.length + data.todos.length);

        if (total === 0) {
            if (q && q.length) renderNoResults(q);
            else renderEmptyState(emptyHTML);
            return;
        }

        let html = '';
        if (activeScope === 'all' || activeScope === 'patients') html += buildSection('patient', data.patients, q);
        if (activeScope === 'all' || activeScope === 'pages')    html += buildSection('page',    data.pages,    q);
        if (activeScope === 'all' || activeScope === 'actions')  html += buildSection('action',  data.actions,  q);
        if (activeScope === 'all' || activeScope === 'todos')    html += buildSection('todo',    data.todos,    q);

        results.innerHTML = html;

        flatRows = Array.from(results.querySelectorAll('.cmdk__row'));
        activeIdx = flatRows.length ? 0 : -1;
        updateActiveRow();
    }

    function updateActiveRow() {
        flatRows.forEach(function (row, i) {
            const on = (i === activeIdx);
            row.classList.toggle('is-active', on);
            row.setAttribute('aria-selected', on ? 'true' : 'false');
            if (on) {
                input.setAttribute('aria-activedescendant', row.id || ('cmdk-row-' + i));
                if (!row.id) row.id = 'cmdk-row-' + i;
                input.setAttribute('aria-activedescendant', row.id);
                scrollRowIntoView(row);
            }
        });
        if (activeIdx < 0) input.removeAttribute('aria-activedescendant');
    }

    function scrollRowIntoView(row) {
        const rRect = row.getBoundingClientRect();
        const cRect = results.getBoundingClientRect();
        if (rRect.top < cRect.top + 4) {
            results.scrollTop -= (cRect.top + 4 - rRect.top);
        } else if (rRect.bottom > cRect.bottom - 4) {
            results.scrollTop += (rRect.bottom - cRect.bottom + 4);
        }
    }

    function moveActive(delta) {
        if (!flatRows.length) return;
        let next = activeIdx + delta;
        if (next < 0) next = flatRows.length - 1;
        if (next >= flatRows.length) next = 0;
        activeIdx = next;
        updateActiveRow();
    }

    // ----- Selection / activation -----
    function activateRow(row) {
        if (!row) return;
        const kind = row.getAttribute('data-kind');
        const url  = row.getAttribute('data-url');
        const id   = row.getAttribute('data-id');

        if (kind === 'action') {
            triggerAction(id, url);
            return;
        }

        // patient / page / todo → navigate
        if (url) {
            close();
            window.location.href = url;
        }
    }

    function triggerAction(id, fallbackUrl) {
        // Primary path: the shared registry resolves the action and either opens
        // the modal on this page or hands off to the owning page (?action=…).
        if (window.ActionRegistry && window.ActionRegistry.byId(id)) {
            close();
            if (window.ActionRegistry.run(id)) return;
            // run() returned false (no opener + no page) — fall through to URL.
            if (fallbackUrl) { window.location.href = fallbackUrl; }
            return;
        }

        // Legacy fallback (registry script unavailable).
        switch (id) {
            case 'action:new-patient':
                if (typeof window.openNewPatientModal === 'function') { close(); window.openNewPatientModal(); return; }
                break;
            case 'action:new-todo':
                if (typeof window.openTodoDrawer === 'function') { close(); window.openTodoDrawer(); return; }
                break;
            case 'action:new-note':
                if (typeof window.openQuickNoteModal === 'function') { close(); window.openQuickNoteModal(); return; }
                break;
            case 'action:new-alert':
                if (typeof window.openNewAlertModal === 'function') { close(); window.openNewAlertModal(); return; }
                close();
                window.location.href = fallbackUrl || '/doctor/alerts';
                return;
            case 'action:focus-mode':
                if (typeof window.toggleFocusMode === 'function') { close(); window.toggleFocusMode(); return; }
                break;
            case 'action:theme-picker':
                if (typeof window.openThemePicker === 'function') { close(); window.openThemePicker(); return; }
                break;
            case 'action:keyboard-help':
                if (typeof window.openKeyboardHelp === 'function') { close(); window.openKeyboardHelp(); return; }
                break;
        }

        // Fallback: if a URL was supplied, navigate.
        if (fallbackUrl) {
            close();
            window.location.href = fallbackUrl;
        } else {
            close();
        }
    }

    // ----- Query handling -----
    const runQuery = debounce(function (q) {
        lastQuery = q;
        fetchPalette(q, activeScope).then(function (data) {
            if (data == null) return; // aborted
            if (data && data.error) {
                // Network error — still show local actions if any.
                if (activeScope === 'all' || activeScope === 'actions') {
                    render({ patients: [], pages: [], actions: localActionsFor(q).map(toActionItem), todos: [] }, q);
                } else {
                    renderError();
                }
                return;
            }
            render(normalizeResponse(data, q), q);
        });
    }, DEBOUNCE_MS);

    function toActionItem(a) {
        return { kind: 'action', id: a.id, label: a.label, sub: a.sub, icon: a.icon };
    }

    function refreshFromInput() {
        const q = (input.value || '').trim();
        if (!q && activeScope === 'all') {
            // No query + no scope filter → friendly defaults (local actions only).
            render({ patients: [], pages: [], actions: getLocalActions().slice(0, 5).map(toActionItem), todos: [] }, '');
            lastQuery = '';
            return;
        }
        runQuery(q);
    }

    // ----- Open / close -----
    function open() {
        if (isOpen) return;
        isOpen = true;
        lastFocused = document.activeElement;

        root.hidden = false;
        // Force a frame so the CSS transition runs.
        requestAnimationFrame(function () {
            root.classList.add('is-open');
        });

        document.documentElement.classList.add('cmdk-lock');
        document.body.classList.add('cmdk-lock');

        // Reset visible state
        input.value = '';
        setScope('all', false);
        renderEmptyState(emptyHTML);

        // Autofocus — defer briefly so mobile keyboards animate cleanly.
        setTimeout(function () {
            try { input.focus({ preventScroll: true }); } catch (e) { input.focus(); }
        }, 30);

        // Seed with local actions immediately.
        refreshFromInput();
    }

    function close() {
        if (!isOpen) return;
        isOpen = false;
        root.classList.remove('is-open');

        document.documentElement.classList.remove('cmdk-lock');
        document.body.classList.remove('cmdk-lock');

        // Hide after transition
        setTimeout(function () {
            if (!isOpen) root.hidden = true;
        }, 220);

        if (abortCtrl) { try { abortCtrl.abort(); } catch (e) {} abortCtrl = null; }

        // Restore focus
        if (lastFocused && typeof lastFocused.focus === 'function') {
            try { lastFocused.focus({ preventScroll: true }); } catch (e) {}
        }
    }

    function toggle() {
        if (isOpen) close(); else open();
    }

    // ----- Scope (tabs) -----
    function setScope(scope, refetch) {
        activeScope = scope || 'all';
        tabs.forEach(function (t) {
            const on = (t.getAttribute('data-scope') === activeScope);
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (refetch !== false) refreshFromInput();
    }

    // ----- Event bindings -----
    // Global open shortcut
    document.addEventListener('keydown', function (ev) {
        const mod = ev.metaKey || ev.ctrlKey;
        // Cmd/Ctrl+K
        if (mod && !ev.altKey && !ev.shiftKey && (ev.key === 'k' || ev.key === 'K')) {
            ev.preventDefault();
            toggle();
            return;
        }
        if (!isOpen) return;

        // Esc anywhere when open
        if (ev.key === 'Escape') {
            ev.preventDefault();
            close();
            return;
        }

        // Arrow / Enter only meaningful when focus inside the palette.
        if (!root.contains(ev.target)) return;

        if (ev.key === 'ArrowDown') {
            ev.preventDefault();
            moveActive(1);
        } else if (ev.key === 'ArrowUp') {
            ev.preventDefault();
            moveActive(-1);
        } else if (ev.key === 'Home') {
            if (flatRows.length) { ev.preventDefault(); activeIdx = 0; updateActiveRow(); }
        } else if (ev.key === 'End') {
            if (flatRows.length) { ev.preventDefault(); activeIdx = flatRows.length - 1; updateActiveRow(); }
        } else if (ev.key === 'Enter') {
            if (activeIdx >= 0 && flatRows[activeIdx]) {
                ev.preventDefault();
                activateRow(flatRows[activeIdx]);
            }
        } else if (ev.key === 'Tab') {
            // Cycle tabs with Tab / Shift+Tab when Alt held; otherwise let focus move naturally.
            if (ev.altKey) {
                ev.preventDefault();
                cycleScope(ev.shiftKey ? -1 : 1);
            }
        }
    });

    function cycleScope(delta) {
        if (!tabs.length) return;
        const order = tabs.map(function (t) { return t.getAttribute('data-scope'); });
        let idx = order.indexOf(activeScope);
        if (idx < 0) idx = 0;
        idx = (idx + delta + order.length) % order.length;
        setScope(order[idx], true);
    }

    // Input typing
    input.addEventListener('input', function () {
        refreshFromInput();
    });

    // Tab clicks
    tabs.forEach(function (t) {
        t.addEventListener('click', function () {
            setScope(t.getAttribute('data-scope'), true);
            input.focus();
        });
    });

    // Click outside panel → close
    backdrop.addEventListener('click', function () {
        close();
    });

    // Close button
    if (closeBtn) closeBtn.addEventListener('click', function () { close(); });

    // Row click / hover
    results.addEventListener('click', function (ev) {
        const row = ev.target.closest('.cmdk__row');
        if (!row) return;
        // Sync active index then activate
        const idx = flatRows.indexOf(row);
        if (idx >= 0) { activeIdx = idx; updateActiveRow(); }
        activateRow(row);
    });

    results.addEventListener('mousemove', function (ev) {
        const row = ev.target.closest('.cmdk__row');
        if (!row) return;
        const idx = flatRows.indexOf(row);
        if (idx >= 0 && idx !== activeIdx) {
            activeIdx = idx;
            updateActiveRow();
        }
    });

    // Stop clicks inside the panel from bubbling to backdrop (defensive — backdrop is a sibling).
    panel.addEventListener('click', function (ev) { ev.stopPropagation(); });

    // ----- Header trigger -----
    // Mount into #topActionsQuick (notes · to-do · ⌘K row on mobile).
    function ensureHeaderButton() {
        if (document.getElementById('cmdkToggle')) return;
        var mount = document.getElementById('topActionsQuick');
        if (!mount) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'cmdkToggle';
        btn.className = 'cmdk-toggle';
        var keyHint = /Mac|iPhone|iPod|iPad/.test(navigator.platform) ? '⌘K' : 'Ctrl+K';
        btn.setAttribute('aria-label', 'Open command palette (' + keyHint + ')');
        btn.setAttribute('title', 'Command palette (' + keyHint + ')');
        btn.innerHTML =
            '<i class="bi bi-command" aria-hidden="true"></i>' +
            '<span class="cmdk-toggle__hint" aria-hidden="true">' + keyHint + '</span>';
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            open();
        });
        mount.appendChild(btn);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureHeaderButton, { once: true });
    } else {
        ensureHeaderButton();
    }

    // ----- Public API -----
    window.cmdk = {
        open: open,
        close: close,
        toggle: toggle,
        isOpen: function () { return isOpen; }
    };
})();
