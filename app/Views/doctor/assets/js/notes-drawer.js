/**
 * Notes Drawer
 * Right-side glass drawer that lists / creates / edits / pins / deletes quick notes.
 * Backed by /api/quick-notes (CRUD + pin/unpin).
 *
 * Public API:
 *   window.openNotesDrawer()
 *   window.closeNotesDrawer()
 *   window.notesDrawer.refresh()
 *
 * Header trigger button (.notes-drawer-toggle) is auto-injected next to the
 * other v11 header chips, hidden on mobile via CSS.
 */
(function () {
    'use strict';

    try {

    var t = function (k, fb, vars) {
        return (window.V11I18n && window.V11I18n.t(k, fb, vars)) || fb;
    };

    // -------------------------------------------------------- DOM refs
    var drawer, panel, backdrop, listEl, searchInput, searchClear,
        quickAdd, quickInput, quickSubmit, fab, modal, modalForm, modalCloseBtn, countEl;
    var rowTpl;

    // -------------------------------------------------------- state
    var state = {
        open: false,
        notes: [],
        filter: 'all',   // 'all' | 'pinned' | 'recent'
        query: '',
        loading: false
    };

    // -------------------------------------------------------- helpers
    function $(sel, root) { return (root || document).querySelector(sel); }
    function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function escapeHTML(s) {
        var div = document.createElement('div');
        div.textContent = s == null ? '' : String(s);
        return div.innerHTML;
    }

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
            return r.json();
        });
    }

    function timeAgo(iso) {
        if (!iso) return '';
        var d = new Date(iso.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        var diff = (Date.now() - d.getTime()) / 1000;
        if (diff < 60) return t('notes.time.just_now', 'just now');
        if (diff < 3600) return t('notes.time.min', Math.floor(diff / 60) + ' min ago', { n: Math.floor(diff / 60) });
        if (diff < 86400) return t('notes.time.hr', Math.floor(diff / 3600) + ' hr ago', { n: Math.floor(diff / 3600) });
        var today = new Date(); today.setHours(0, 0, 0, 0);
        var yesterday = new Date(today.getTime() - 86400000);
        if (d >= today) return t('notes.time.today', 'Today');
        if (d >= yesterday) return t('notes.time.yesterday', 'Yesterday');
        return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }

    // -------------------------------------------------------- DOM mount
    function cacheRefs() {
        drawer        = document.getElementById('notesDrawer');
        if (!drawer) return false;
        panel         = drawer.querySelector('.notes-drawer__panel');
        backdrop      = drawer.querySelector('#notesDrawerBackdrop');
        listEl        = drawer.querySelector('#notesDrawerList');
        countEl       = drawer.querySelector('#notesDrawerCount');
        searchInput   = drawer.querySelector('#notesDrawerSearch');
        searchClear   = drawer.querySelector('#notesDrawerSearchClear');
        quickAdd      = drawer.querySelector('#notesDrawerQuickAdd');
        quickInput    = drawer.querySelector('#notesDrawerQuickInput');
        quickSubmit   = quickAdd ? quickAdd.querySelector('button[type="submit"]') : null;
        fab           = drawer.querySelector('#notesDrawerFab');
        modal         = drawer.querySelector('#notesDrawerModal');
        modalForm     = drawer.querySelector('#ndModalForm');
        modalCloseBtn = drawer.querySelector('#ndModalClose');
        rowTpl        = drawer.querySelector('#notesDrawerRowTpl');
        return true;
    }

    // -------------------------------------------------------- open / close
    function open() {
        if (!drawer || state.open) return;
        state.open = true;
        drawer.setAttribute('aria-hidden', 'false');
        drawer.classList.add('open');
        document.addEventListener('keydown', onKeyDown, true);
        loadNotes();
        // Move focus to quick input for fast capture
        setTimeout(function () { quickInput && quickInput.focus(); }, 300);
    }

    function close() {
        if (!drawer || !state.open) return;
        state.open = false;
        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
        document.removeEventListener('keydown', onKeyDown, true);
        closeModal();
    }

    function onKeyDown(e) {
        if (e.key === 'Escape') {
            if (modal && !modal.hidden && modal.classList.contains('is-open')) {
                closeModal();
                return;
            }
            close();
        }
    }

    // -------------------------------------------------------- list / filter
    function loadNotes() {
        if (state.loading) return;
        state.loading = true;
        // Merged view across BOTH stores (quick_notes + board notes). Falls back
        // to quick-only if the bridge isn't present.
        var p = window.NotesBridge
            ? window.NotesBridge.list()
            : api('/api/quick-notes').then(function (res) {
                return ((res && res.data) || []).map(function (n) {
                    return { key: 'q:' + n.id, origin: 'quick', id: n.id, title: n.title || '',
                             body: n.body || '', background_color: n.background_color || '',
                             pinned: !!n.pinned, created_at: n.created_at, updated_at: n.updated_at, raw: n };
                });
            });
        return p
            .then(function (notes) {
                state.notes = notes || [];
                state.loaded = true;
                render();
                setHeaderBadge(state.notes.length);
            })
            .catch(function () {
                listEl.innerHTML =
                    '<div class="nd-empty nd-empty--error">' +
                      '<i class="bi bi-cloud-slash" aria-hidden="true"></i>' +
                      '<p>' + t('notes.couldnt_load', "Couldn't load notes.") + '</p>' +
                      '<button type="button" class="nd-btn nd-btn--ghost" data-retry>' + t('notes.retry', 'Retry') + '</button>' +
                    '</div>';
            })
            .then(function () { state.loading = false; });
    }

    function filterNotes() {
        var q = state.query.trim().toLowerCase();
        return state.notes.filter(function (n) {
            if (state.filter === 'pinned' && !n.pinned) return false;
            if (state.filter === 'recent') {
                // Recent = last 7 days
                if (n.created_at) {
                    var d = new Date(n.created_at.replace(' ', 'T'));
                    if ((Date.now() - d.getTime()) / 86400000 > 7) return false;
                }
            }
            if (q) {
                var hay = ((n.title || '') + ' ' + (n.body || '')).toLowerCase();
                if (hay.indexOf(q) === -1) return false;
            }
            return true;
        }).sort(function (a, b) {
            // Pinned first, then newest first
            if (!!b.pinned !== !!a.pinned) return b.pinned ? 1 : -1;
            var da = new Date((a.created_at || '').replace(' ', 'T')).getTime();
            var db = new Date((b.created_at || '').replace(' ', 'T')).getTime();
            return db - da;
        });
    }

    function render() {
        var notes = filterNotes();
        if (countEl) {
            if (state.notes.length > 0) {
                countEl.hidden = false;
                countEl.textContent = state.notes.length > 99 ? '99+' : String(state.notes.length);
            } else {
                countEl.hidden = true;
            }
        }
        if (!notes.length) {
            listEl.innerHTML =
                '<div class="nd-empty">' +
                  '<i class="bi bi-journal" aria-hidden="true"></i>' +
                  '<p>' + (state.query ? t('notes.no_match', 'No notes match your search.') : t('notes.no_notes', 'No notes yet.')) + '</p>' +
                  (state.query ? '' :
                    '<p class="nd-empty__sub">' + t('notes.empty_sub', 'Jot something above to start.') + '</p>') +
                '</div>';
            return;
        }
        var frag = document.createDocumentFragment();
        notes.forEach(function (n) { frag.appendChild(buildRow(n)); });
        listEl.innerHTML = '';
        listEl.appendChild(frag);
    }

    function buildRow(n) {
        var node = rowTpl.content.firstElementChild.cloneNode(true);
        // Use the cross-store key (e.g. "q:7" / "b:7") since ids can collide.
        node.dataset.id = String(n.key || ('q:' + n.id));
        node.dataset.origin = n.origin || 'quick';
        if (n.pinned) node.classList.add('is-pinned');
        // Board-origin notes can't be pinned and shouldn't show the pin toggle.
        if (n.origin === 'board') {
            var pinBtn = $('.nd-row__pin', node);
            if (pinBtn) { pinBtn.style.visibility = 'hidden'; pinBtn.setAttribute('tabindex', '-1'); }
            var titleWrap = $('.nd-row__title-wrap', node) || node;
            var tag = document.createElement('span');
            tag.className = 'nd-row__origin';
            tag.title = t('notes.from_board', 'From the notes board');
            tag.innerHTML = '<i class="bi bi-easel" aria-hidden="true"></i>';
            titleWrap.appendChild(tag);
        }
        var titleEl = $('[data-field="title"]', node);
        var bodyEl  = $('[data-field="body"]',  node);
        var timeEl  = $('[data-field="time"]',  node);
        var title   = (n.title || '').trim();
        if (!title) {
            // First line of body becomes the title
            title = (n.body || '').split('\n')[0].slice(0, 80) || t('notes.untitled', '(untitled)');
            titleEl.classList.add('is-derived');
        }
        titleEl.textContent = title;
        bodyEl.textContent  = (n.body || '').slice(0, 280);
        if ((n.body || '').length > 280) bodyEl.textContent += '…';
        timeEl.textContent  = timeAgo(n.created_at);
        timeEl.setAttribute('datetime', n.created_at || '');
        // Pin icon state
        var pinIcon = $('.nd-row__pin i', node);
        if (pinIcon) pinIcon.className = 'bi ' + (n.pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle');
        // Gradient / glassmorphism background (shared NoteBG).
        if (window.NoteBG) window.NoteBG.apply(node, n.background_color);
        return node;
    }

    // -------------------------------------------------------- quick-add
    function bindQuickAdd() {
        if (!quickAdd) return;
        // Auto-resize
        quickInput.addEventListener('input', function () {
            quickInput.style.height = 'auto';
            quickInput.style.height = Math.min(quickInput.scrollHeight, 160) + 'px';
            quickSubmit.disabled = quickInput.value.trim().length === 0;
        });
        // Cmd+Enter / Ctrl+Enter saves
        quickInput.addEventListener('keydown', function (e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                e.preventDefault();
                quickAdd.requestSubmit();
            }
        });
        quickAdd.addEventListener('submit', function (e) {
            e.preventDefault();
            var body = quickInput.value.trim();
            if (!body) return;
            quickSubmit.disabled = true;
            api('/api/quick-notes', { method: 'POST', body: { body: body } })
                .then(function (res) {
                    if (res && res.success && res.data) {
                        var norm = window.NotesBridge ? window.NotesBridge._normQuick(res.data) : res.data;
                        state.notes.unshift(norm);
                        render();
                        setHeaderBadge(state.notes.length);
                        notifyChanged('create', norm);
                    }
                    quickInput.value = '';
                    quickInput.style.height = '';
                })
                .catch(function () {
                    // Show inline error briefly
                    quickInput.classList.add('is-error');
                    setTimeout(function () { quickInput.classList.remove('is-error'); }, 1200);
                })
                .then(function () {
                    quickSubmit.disabled = quickInput.value.trim().length === 0;
                });
        });
    }

    // -------------------------------------------------------- filters + search
    function bindFilters() {
        drawer.addEventListener('click', function (e) {
            var f = e.target.closest('.nd-filter');
            if (!f) return;
            $$('.nd-filter', drawer).forEach(function (b) {
                b.classList.toggle('active', b === f);
                b.setAttribute('aria-selected', b === f ? 'true' : 'false');
            });
            state.filter = f.dataset.filter || 'all';
            render();
        });
        searchInput && searchInput.addEventListener('input', function () {
            state.query = searchInput.value;
            searchClear.hidden = !searchInput.value.length;
            render();
        });
        searchClear && searchClear.addEventListener('click', function () {
            searchInput.value = '';
            state.query = '';
            searchClear.hidden = true;
            render();
            searchInput.focus();
        });
    }

    // -------------------------------------------------------- row actions
    function bindList() {
        listEl.addEventListener('click', function (e) {
            var retry = e.target.closest('[data-retry]');
            if (retry) { loadNotes(); return; }
            var actBtn = e.target.closest('.nd-row__pin, .nd-row__act');
            if (!actBtn) {
                // Tap on row body opens for edit
                var row = e.target.closest('.nd-row');
                if (row && row.dataset.id) openEditor(findById(row.dataset.id));
                return;
            }
            var row = actBtn.closest('.nd-row');
            if (!row) return;
            var note = findById(row.dataset.id);
            if (!note) return;
            var act = actBtn.dataset.act;
            if (act === 'pin') {
                togglePin(note, row);
            } else if (act === 'edit') {
                openEditor(note);
            } else if (act === 'delete') {
                doDelete(note, row);
            }
        });
    }

    function findById(key) {
        for (var i = 0; i < state.notes.length; i++) {
            var n = state.notes[i];
            if (String(n.key || ('q:' + n.id)) === String(key)) return n;
        }
        return null;
    }

    function togglePin(note, row) {
        if (note.origin === 'board') return;   // board notes have no pin
        var newState = !note.pinned;
        // Optimistic
        note.pinned = newState;
        row.classList.toggle('is-pinned', newState);
        var ico = row.querySelector('.nd-row__pin i');
        if (ico) ico.className = 'bi ' + (newState ? 'bi-pin-angle-fill' : 'bi-pin-angle');
        // Re-render to re-sort
        render();
        var op = window.NotesBridge
            ? window.NotesBridge.setPinned(note, newState)
            : api('/api/quick-notes/' + encodeURIComponent(note.id) + (newState ? '/pin' : '/unpin'), { method: 'POST' });
        op.then(function () { notifyChanged('pin', note); })
          .catch(function () { note.pinned = !newState; render(); });
    }

    function doDelete(note, row) {
        var run = function () {
            row.classList.add('is-removing');
            setTimeout(function () {
                var idx = state.notes.findIndex(function (n) { return String(n.key) === String(note.key); });
                if (idx !== -1) state.notes.splice(idx, 1);
                render();
                setHeaderBadge(state.notes.length);
            }, 200);
            var op = window.NotesBridge
                ? window.NotesBridge.remove(note)
                : api('/api/quick-notes/' + encodeURIComponent(note.id), { method: 'DELETE' });
            op.then(function () { notifyChanged('delete', note); })
              .catch(function () { loadNotes(); });
        };
        // Use modal-kit confirm if available, otherwise a plain confirm()
        if (window.mkConfirmModal) {
            window.mkConfirmModal({
                title: t('notes.delete_note', 'Delete note'),
                message: t('notes.delete_msg', 'Delete this note permanently?'),
                confirmText: t('notes.delete', 'Delete'),
                confirmVariant: 'danger'
            }).then(function (ok) { if (ok) run(); });
        } else if (confirm(t('notes.delete_msg', 'Delete this note?'))) {
            run();
        }
    }

    // -------------------------------------------------------- editor modal
    function openEditor(note) {
        if (!modal) return;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        // Force reflow then animate in
        // eslint-disable-next-line no-unused-expressions
        modal.offsetWidth;
        modal.classList.add('is-open');
        var titleInput = modalForm.elements['title'];
        var bodyInput  = modalForm.elements['body'];
        var pinInput   = modalForm.elements['pinned'];
        var idInput    = modalForm.elements['id'];
        var titleEl    = drawer.querySelector('#ndModalTitle');
        if (note) {
            titleInput.value = note.title || '';
            bodyInput.value  = note.body || '';
            pinInput.checked = !!note.pinned;
            idInput.value    = note.key || ('q:' + note.id);
            renderModalSwatches(note.background_color || '');
            // Board notes can't be pinned — hide the pin row in the editor.
            var pinField = pinInput.closest('.nd-field--inline');
            if (pinField) pinField.style.display = (note.origin === 'board') ? 'none' : '';
            titleEl.textContent = t('notes.edit_note', 'Edit note');
        } else {
            titleInput.value = '';
            bodyInput.value  = '';
            pinInput.checked = false;
            idInput.value    = '';
            var pinFieldNew = pinInput.closest('.nd-field--inline');
            if (pinFieldNew) pinFieldNew.style.display = '';
            renderModalSwatches('');
            titleEl.textContent = t('notes.new_note', 'New note');
        }
        setTimeout(function () { bodyInput.focus(); }, 200);
    }

    // ---- background swatches (shared NoteBG) ----
    function renderModalSwatches(active) {
        var box = drawer.querySelector('#ndModalSwatches');
        var hidden = drawer.querySelector('#ndModalBg');
        if (hidden) hidden.value = active || '';
        if (box && window.NoteBG) box.innerHTML = window.NoteBG.swatchHTML(active || '');
    }
    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        setTimeout(function () { modal.hidden = true; }, 200);
    }
    function bindModal() {
        if (!modal) return;
        modalCloseBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target.closest('.nd-modal__backdrop')) closeModal();
            if (e.target.closest('[data-nd-cancel]')) closeModal();
            // Background swatch selection.
            var sw = e.target.closest('.note-swatch');
            if (sw) {
                var box = drawer.querySelector('#ndModalSwatches');
                var hidden = drawer.querySelector('#ndModalBg');
                if (hidden) hidden.value = sw.getAttribute('data-note-swatch') || '';
                if (box) box.querySelectorAll('.note-swatch').forEach(function (b) {
                    var on = b === sw;
                    b.classList.toggle('is-active', on);
                    b.setAttribute('aria-checked', on ? 'true' : 'false');
                });
            }
        });
        modalForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(modalForm);
            var key    = (fd.get('id') || '').toString();
            var title  = (fd.get('title') || '').toString().trim();
            var body   = (fd.get('body')  || '').toString();
            var pinned = !!fd.get('pinned');
            var bg     = (fd.get('background_color') || '').toString();
            if (!body.trim()) return;

            if (key) {
                // Editing an existing note — route to its origin store via the bridge.
                var rec = findById(key);
                if (!rec) { closeModal(); return; }
                var fields = { title: title || null, body: body, background_color: bg };
                if (rec.origin === 'quick') fields.pinned = pinned;
                var op = window.NotesBridge
                    ? window.NotesBridge.save(rec, fields)
                    : api('/api/quick-notes/' + encodeURIComponent(rec.id), { method: 'PATCH', body: fields })
                        .then(function (res) { return res && res.data; });
                op.then(function (updated) {
                    var idx = state.notes.findIndex(function (n) { return String(n.key) === String(key); });
                    if (idx !== -1 && updated) state.notes[idx] = updated;
                    render();
                    closeModal();
                    notifyChanged('update', updated || rec);
                }).catch(function () { /* keep open */ });
            } else {
                // New note → always a quick note.
                api('/api/quick-notes', { method: 'POST', body: { title: title || null, body: body, pinned: pinned, background_color: bg } })
                    .then(function (res) {
                        if (res && res.success && res.data) {
                            var norm = window.NotesBridge
                                ? window.NotesBridge._normQuick(res.data)
                                : res.data;
                            state.notes.unshift(norm);
                            render();
                            setHeaderBadge(state.notes.length);
                            closeModal();
                            notifyChanged('create', norm);
                        }
                    }).catch(function () { /* keep open */ });
            }
        });
    }

    // Broadcast a change so the Cmd+K quick-note modal and any other surface
    // refresh live without a page reload.
    var _ndSelfEmit = false;
    function notifyChanged(action, note) {
        if (!window.NotesSync) return;
        _ndSelfEmit = true;
        try { window.NotesSync.emit((note && note.origin) || 'quick', { action: action, note: note }); }
        finally { _ndSelfEmit = false; }
    }

    // -------------------------------------------------------- header trigger
    function ensureHeaderButton() {
        if (document.getElementById('notesDrawerToggle')) return;
        var mount = document.getElementById('topActionsQuick');
        if (!mount) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'notesDrawerToggle';
        btn.className = 'notes-drawer-toggle';
        btn.setAttribute('aria-label', t('notes.open_drawer', 'Open notes'));
        btn.setAttribute('title', 'Notes');
        btn.innerHTML =
            '<i class="bi bi-journal-text" aria-hidden="true"></i>' +
            '<span class="notes-drawer-toggle__badge" id="notesDrawerToggleBadge" hidden>0</span>';
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            open();
        });
        var todo = document.getElementById('todoDrawerToggle');
        if (todo) mount.insertBefore(btn, todo);
        else mount.appendChild(btn);

        // Periodic light count refresh + immediate refresh on any quick-note
        // change (add / delete / edit / pin) from any surface, so the badge is
        // always current without a page reload.
        refreshHeaderBadge();
        setInterval(function () {
            if (document.visibilityState === 'visible') refreshHeaderBadge();
        }, 60 * 1000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') refreshHeaderBadge();
        });
        if (window.NotesSync) {
            // Badge reflects the merged total, so react to any note change.
            window.NotesSync.on(function () { refreshHeaderBadge(); });
        }
    }

    // The header badge reflects the total number of quick notes, so it ticks up
    // on add and down on delete. If the drawer already has fresh state loaded,
    // use it to avoid an extra round-trip; otherwise fetch.
    function setHeaderBadge(n) {
        var badge = document.getElementById('notesDrawerToggleBadge');
        if (!badge) return;
        if (n > 0) {
            badge.hidden = false;
            badge.textContent = n > 99 ? '99+' : String(n);
        } else {
            badge.hidden = true;
            badge.textContent = '0';
        }
    }

    function refreshHeaderBadge() {
        var badge = document.getElementById('notesDrawerToggleBadge');
        if (!badge) return;
        if (state && state.loaded && Array.isArray(state.notes)) {
            setHeaderBadge(state.notes.length);
            return;
        }
        if (window.NotesBridge) {
            window.NotesBridge.list()
                .then(function (rows) { setHeaderBadge((rows || []).length); })
                .catch(function () {});
            return;
        }
        fetch('/api/quick-notes', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || !data.success) return;
                var rows = data.data || [];
                setHeaderBadge(rows.length);
            })
            .catch(function () {});
    }

    // -------------------------------------------------------- init
    function init() {
        if (!cacheRefs()) return;

        ensureHeaderButton();

        // Close interactions
        $('#notesDrawerClose', drawer).addEventListener('click', close);
        backdrop.addEventListener('click', function () {
            // Don't close if the editor modal is open
            if (modal && !modal.hidden && modal.classList.contains('is-open')) return;
            close();
        });

        bindQuickAdd();
        bindFilters();
        bindList();
        bindModal();

        // FAB ("New note with full options") — open the full editor. This was
        // never wired, so the button did nothing.
        if (fab) {
            fab.addEventListener('click', function (e) {
                e.preventDefault();
                openEditor(null);
            });
        }

        // The drawer shows a MERGED view, so refresh on ANY note change from
        // any surface/store (quick or board). NotesSync dispatches the legacy
        // 'quicknote:changed' DOM event for all scopes. Ignore our own echo.
        document.addEventListener('quicknote:changed', function () {
            if (_ndSelfEmit) return;
            loadNotes();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // -------------------------------------------------------- public API
    window.openNotesDrawer  = open;
    window.closeNotesDrawer = close;
    window.notesDrawer = {
        open: open,
        close: close,
        refresh: loadNotes
    };

    } catch (err) {
        if (window.console && console.error) console.error('[notesDrawer] init error:', err);
    }
})();
