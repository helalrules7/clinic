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
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
        var today = new Date(); today.setHours(0, 0, 0, 0);
        var yesterday = new Date(today.getTime() - 86400000);
        if (d >= today) return 'Today';
        if (d >= yesterday) return 'Yesterday';
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
        return api('/api/quick-notes')
            .then(function (res) {
                state.notes = (res && res.data) || [];
                render();
            })
            .catch(function () {
                listEl.innerHTML =
                    '<div class="nd-empty nd-empty--error">' +
                      '<i class="bi bi-cloud-slash" aria-hidden="true"></i>' +
                      '<p>Couldn&rsquo;t load notes.</p>' +
                      '<button type="button" class="nd-btn nd-btn--ghost" data-retry>Retry</button>' +
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
                  '<p>' + (state.query ? 'No notes match your search.' : 'No notes yet.') + '</p>' +
                  (state.query ? '' :
                    '<p class="nd-empty__sub">Jot something above to start.</p>') +
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
        node.dataset.id = String(n.id);
        if (n.pinned) node.classList.add('is-pinned');
        var titleEl = $('[data-field="title"]', node);
        var bodyEl  = $('[data-field="body"]',  node);
        var timeEl  = $('[data-field="time"]',  node);
        var title   = (n.title || '').trim();
        if (!title) {
            // First line of body becomes the title
            title = (n.body || '').split('\n')[0].slice(0, 80) || '(untitled)';
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
                        state.notes.unshift(res.data);
                        render();
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

    function findById(id) {
        for (var i = 0; i < state.notes.length; i++) {
            if (String(state.notes[i].id) === String(id)) return state.notes[i];
        }
        return null;
    }

    function togglePin(note, row) {
        var endpoint = note.pinned ? '/unpin' : '/pin';
        var newState = !note.pinned;
        // Optimistic
        note.pinned = newState;
        row.classList.toggle('is-pinned', newState);
        var ico = row.querySelector('.nd-row__pin i');
        if (ico) ico.className = 'bi ' + (newState ? 'bi-pin-angle-fill' : 'bi-pin-angle');
        // Re-render to re-sort
        render();
        api('/api/quick-notes/' + encodeURIComponent(note.id) + endpoint, { method: 'POST' })
            .catch(function () {
                // Revert
                note.pinned = !newState;
                render();
            });
    }

    function doDelete(note, row) {
        var run = function () {
            row.classList.add('is-removing');
            setTimeout(function () {
                var idx = state.notes.findIndex(function (n) { return String(n.id) === String(note.id); });
                if (idx !== -1) state.notes.splice(idx, 1);
                render();
            }, 200);
            api('/api/quick-notes/' + encodeURIComponent(note.id), { method: 'DELETE' })
                .catch(function () {
                    // Restore on failure
                    loadNotes();
                });
        };
        // Use modal-kit confirm if available, otherwise a plain confirm()
        if (window.mkConfirmModal) {
            window.mkConfirmModal({
                title: 'Delete note',
                message: 'This note will be permanently removed.',
                confirmText: 'Delete',
                confirmVariant: 'danger',
                onConfirm: run
            });
        } else if (confirm('Delete this note?')) {
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
            idInput.value    = note.id;
            titleEl.textContent = 'Edit note';
        } else {
            titleInput.value = '';
            bodyInput.value  = '';
            pinInput.checked = false;
            idInput.value    = '';
            titleEl.textContent = 'New note';
        }
        setTimeout(function () { bodyInput.focus(); }, 200);
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
        });
        modalForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(modalForm);
            var id     = fd.get('id') || '';
            var title  = (fd.get('title') || '').toString().trim();
            var body   = (fd.get('body')  || '').toString();
            var pinned = !!fd.get('pinned');
            if (!body.trim()) return;
            var payload = { title: title || null, body: body, pinned: pinned };
            var p;
            if (id) {
                p = api('/api/quick-notes/' + encodeURIComponent(id), { method: 'PATCH', body: payload });
            } else {
                p = api('/api/quick-notes', { method: 'POST', body: payload });
            }
            p.then(function (res) {
                if (res && res.success && res.data) {
                    if (id) {
                        var idx = state.notes.findIndex(function (n) { return String(n.id) === String(id); });
                        if (idx !== -1) state.notes[idx] = res.data;
                    } else {
                        state.notes.unshift(res.data);
                    }
                    render();
                    closeModal();
                }
            }).catch(function () { /* keep open, simple no-op */ });
        });
    }

    // -------------------------------------------------------- header trigger
    function ensureHeaderButton() {
        if (document.getElementById('notesDrawerToggle')) return;
        var anchor =
            document.getElementById('todoDrawerToggle') ||
            document.getElementById('cmdkToggle') ||
            document.getElementById('kbdHelpToggle') ||
            document.getElementById('paletteToggle') ||
            document.querySelector('label.switch[for="themeToggleInput"]');
        if (!anchor || !anchor.parentNode) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'notesDrawerToggle';
        btn.className = 'notes-drawer-toggle';
        btn.setAttribute('aria-label', 'Open notes');
        btn.setAttribute('title', 'Notes');
        btn.innerHTML =
            '<i class="bi bi-journal-text" aria-hidden="true"></i>' +
            '<span class="notes-drawer-toggle__badge" id="notesDrawerToggleBadge" hidden>0</span>';
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            open();
        });
        anchor.parentNode.insertBefore(btn, anchor);

        // Periodic light count refresh so the badge reflects pinned count.
        refreshHeaderBadge();
        setInterval(function () {
            if (document.visibilityState === 'visible') refreshHeaderBadge();
        }, 60 * 1000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') refreshHeaderBadge();
        });
    }

    function refreshHeaderBadge() {
        var badge = document.getElementById('notesDrawerToggleBadge');
        if (!badge) return;
        fetch('/api/quick-notes', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || !data.success) return;
                var rows = data.data || [];
                var n = rows.filter(function (r) { return r.pinned; }).length;
                if (n > 0) {
                    badge.hidden = false;
                    badge.textContent = n > 99 ? '99+' : String(n);
                } else {
                    badge.hidden = true;
                }
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

        // Refresh after pin/delete/etc. from outside
        document.addEventListener('quicknote:changed', function () { loadNotes(); });
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
