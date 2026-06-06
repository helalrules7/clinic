/* =====================================================================
 * Quick Note  —  notepad / scratchpad modal.
 *
 * Public API
 *   window.openQuickNoteModal()           open empty (create) form
 *   window.openQuickNoteModal({ id })     open and prefill an existing note
 *   window.quickNote.refresh()            re-load the recent panel
 *
 * Backend endpoints (assumed available)
 *   GET    /api/quick-notes               list (recent first, pinned first)
 *   POST   /api/quick-notes               create  { title, body, pinned }
 *   PATCH  /api/quick-notes/:id           update  { title?, body?, pinned? }
 *   DELETE /api/quick-notes/:id           delete
 *   POST   /api/quick-notes/:id/pin       pin
 *   POST   /api/quick-notes/:id/unpin     unpin
 *
 * Conventions
 *   - Plain fetch with credentials + JSON. No jQuery.
 *   - Confirm via window.mkConfirmModal (modal-kit). Never legacy showConfirmModal.
 *   - Saves with Ctrl/Cmd+Enter from the body textarea.
 *   - Renders into #quickNoteModal as defined in layouts/quick-note-modal.php.
 * ===================================================================== */
(function () {
    'use strict';

    if (window.quickNote && window.quickNote.__inited) return;

    // ---------------------------------------------------------------- DOM refs
    var els = {
        modal: null, form: null,
        idInput: null, titleInput: null, titleCount: null,
        bodyInput: null, bodyError: null,
        pinInput: null,
        swatches: null, bgInput: null,
        inlineConfirm: null, inlineConfirmText: null,
        recentToggle: null, recentToggleText: null,
        recentCount: null, recentPanel: null,
        recentList: null, recentStatus: null,
        refreshBtn: null,
        saveBtn: null, saveBtnLabel: null, saveBtnSpinner: null,
        cancelBtn: null
    };

    // -------------------------------------------------------------- state
    var state = {
        editingId: null,         // null = creating; otherwise the cross-store key being patched
        editingRec: null,        // the normalized record being edited (carries origin)
        saving: false,
        loading: false,
        loaded: false,
        notes: [],               // normalized records (NotesBridge merged view)
        pendingOpenId: null      // requested via openQuickNoteModal({id})
    };

    function findByKey(key) {
        for (var i = 0; i < state.notes.length; i++) {
            var n = state.notes[i];
            if (String(n.key || ('q:' + n.id)) === String(key)) return n;
        }
        return null;
    }

    // -------------------------------------------------------------- fetch helper
    function api(method, url, body) {
        var opts = {
            method: method,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        };
        if (body !== undefined && body !== null) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function (res) {
            var ct = res.headers.get('content-type') || '';
            var parser = ct.indexOf('application/json') !== -1
                ? res.json().catch(function () { return null; })
                : Promise.resolve(null);
            return parser.then(function (data) {
                if (!res.ok) {
                    var msg = (data && (data.message || data.error)) || ('Request failed (' + res.status + ')');
                    var err = new Error(msg);
                    err.status = res.status;
                    err.data = data;
                    throw err;
                }
                return data;
            });
        });
    }

    // -------------------------------------------------------------- utils
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function firstLine(s, max) {
        if (!s) return '';
        var line = String(s).split(/\r?\n/)[0] || '';
        line = line.trim();
        if (max && line.length > max) line = line.slice(0, max - 1) + '…';
        return line;
    }

    function excerpt(s, max) {
        if (!s) return '';
        var compact = String(s).replace(/\s+/g, ' ').trim();
        if (max && compact.length > max) compact = compact.slice(0, max - 1) + '…';
        return compact;
    }

    function timeAgo(input) {
        if (!input) return '';
        var d = (input instanceof Date) ? input : new Date(input);
        if (isNaN(d.getTime())) return '';
        var diff = Math.max(0, Math.floor((Date.now() - d.getTime()) / 1000));
        if (diff < 5)        return 'just now';
        if (diff < 60)       return diff + 's ago';
        if (diff < 3600)     return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400)    return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800)   return Math.floor(diff / 86400) + 'd ago';
        if (diff < 2592000)  return Math.floor(diff / 604800) + 'w ago';
        if (diff < 31536000) return Math.floor(diff / 2592000) + 'mo ago';
        return Math.floor(diff / 31536000) + 'y ago';
    }

    function toast(kind, title, message) {
        if (typeof window.showToast === 'function') {
            try { return window.showToast(kind, title || '', message || ''); } catch (_) {}
        }
        // Inline fallback (matches the visual language of patient.js patientToast)
        var color = kind === 'error'   ? '#ef4444'
                  : kind === 'success' ? '#10b981'
                  :                       '#6366f1';
        var holder = document.getElementById('__qnToastHolder');
        if (!holder) {
            holder = document.createElement('div');
            holder.id = '__qnToastHolder';
            holder.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:11000;display:flex;flex-direction:column;gap:.5rem;pointer-events:none;';
            document.body.appendChild(holder);
        }
        var dark = document.documentElement.classList.contains('dark');
        var t = document.createElement('div');
        t.style.cssText =
            'pointer-events:auto;min-width:240px;max-width:360px;padding:.7rem .9rem;border-radius:12px;' +
            'background:' + (dark ? 'rgba(11,18,32,0.95)' : 'rgba(248,250,252,0.95)') + ';' +
            'color:' + (dark ? '#F8FAFC' : '#0F172A') + ';border:1px solid ' + color + ';' +
            'box-shadow:0 8px 28px rgba(15,23,42,0.18);font-size:.88rem;' +
            'transform:translateX(20px);opacity:0;transition:transform .25s ease,opacity .25s ease;';
        t.innerHTML =
            '<div style="display:flex;align-items:center;gap:.45rem;font-weight:600;color:' + color + ';margin-bottom:.15rem;">' +
                '<i class="bi ' + (kind === 'error' ? 'bi-exclamation-circle-fill' : kind === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill') + '"></i>' +
                '<span>' + esc(title || '') + '</span>' +
            '</div>' +
            (message ? '<div style="opacity:.85;">' + esc(message) + '</div>' : '');
        holder.appendChild(t);
        requestAnimationFrame(function () { t.style.transform = 'translateX(0)'; t.style.opacity = '1'; });
        setTimeout(function () {
            t.style.transform = 'translateX(20px)'; t.style.opacity = '0';
            setTimeout(function () { try { t.remove(); } catch (_) {} }, 250);
        }, 3000);
    }

    function confirmDialog(opts) {
        if (typeof window.mkConfirmModal === 'function') {
            return window.mkConfirmModal(opts);
        }
        if (typeof window.showConfirmModal === 'function') {
            // Legacy fallback (only used if mk variant is missing for any reason).
            return new Promise(function (resolve) {
                try { window.showConfirmModal((opts && opts.title) || 'Confirm', (opts && opts.message) || '', function () { resolve(true); }); }
                catch (_) { resolve(window.confirm((opts && opts.message) || '')); }
            });
        }
        return Promise.resolve(window.confirm((opts && opts.message) || ''));
    }

    // -------------------------------------------------------------- form helpers
    function renderSwatches(active) {
        if (els.bgInput) els.bgInput.value = active || '';
        if (els.swatches && window.NoteBG) els.swatches.innerHTML = window.NoteBG.swatchHTML(active || '');
    }

    function clearForm() {
        state.editingId = null;
        state.editingRec = null;
        if (els.pinInput) {
            var pf = els.pinInput.closest('.qn-row, .qn-pin-toggle');
            if (pf) pf.style.display = '';
        }
        if (els.idInput)    els.idInput.value    = '';
        if (els.titleInput) els.titleInput.value = '';
        if (els.bodyInput)  els.bodyInput.value  = '';
        if (els.pinInput)   els.pinInput.checked = false;
        renderSwatches('');
        updateTitleCount();
        autosizeBody();
        hideBodyError();
        setSaveLabel('Save');
        if (els.form) els.form.classList.remove('qn-form--editing');
    }

    function fillForm(note) {
        if (!note) return clearForm();
        state.editingId = note.key || ('q:' + note.id);
        state.editingRec = note;
        if (els.idInput)    els.idInput.value    = state.editingId;
        if (els.titleInput) els.titleInput.value = note.title || '';
        if (els.bodyInput)  els.bodyInput.value  = note.body  || '';
        if (els.pinInput)   els.pinInput.checked = !!note.pinned;
        // Board notes can't be pinned — hide the pin toggle when editing one.
        if (els.pinInput) {
            var pinField = els.pinInput.closest('.qn-pin-toggle') || els.pinInput.closest('.qn-row');
            if (pinField) pinField.style.display = (note.origin === 'board') ? 'none' : '';
        }
        renderSwatches(note.background_color || '');
        updateTitleCount();
        autosizeBody();
        hideBodyError();
        setSaveLabel('Update');
        if (els.form) els.form.classList.add('qn-form--editing');
        // Focus textarea for quick editing.
        setTimeout(function () {
            if (els.bodyInput) { els.bodyInput.focus(); }
        }, 30);
        // Scroll the modal back to top so the user sees the form.
        try {
            var body = els.modal && els.modal.querySelector('.modal-body');
            if (body) body.scrollTop = 0;
        } catch (_) {}
    }

    function updateTitleCount() {
        if (!els.titleInput || !els.titleCount) return;
        var n = els.titleInput.value.length;
        els.titleCount.textContent = String(n);
        els.titleCount.parentElement.classList.toggle('qn-counter--max', n >= 200);
    }

    function autosizeBody() {
        var ta = els.bodyInput;
        if (!ta) return;
        ta.style.height = 'auto';
        // Cap auto-grow so the modal body remains scrollable rather than the page.
        var max = 360;
        var h = Math.min(max, ta.scrollHeight);
        ta.style.height = h + 'px';
    }

    function showBodyError() {
        if (els.bodyError) els.bodyError.hidden = false;
        if (els.bodyInput) {
            els.bodyInput.classList.add('is-invalid');
            els.bodyInput.setAttribute('aria-invalid', 'true');
        }
    }

    function hideBodyError() {
        if (els.bodyError) els.bodyError.hidden = true;
        if (els.bodyInput) {
            els.bodyInput.classList.remove('is-invalid');
            els.bodyInput.removeAttribute('aria-invalid');
        }
    }

    function setSaveLabel(text) {
        if (els.saveBtnLabel) els.saveBtnLabel.textContent = text;
    }

    function setSaving(flag) {
        state.saving = !!flag;
        if (!els.saveBtn) return;
        els.saveBtn.disabled = !!flag;
        if (els.saveBtnSpinner) els.saveBtnSpinner.hidden = !flag;
        els.saveBtn.classList.toggle('is-loading', !!flag);
    }

    function flashInlineConfirm(text) {
        if (!els.inlineConfirm) return;
        if (els.inlineConfirmText) els.inlineConfirmText.textContent = text || 'Saved.';
        els.inlineConfirm.hidden = false;
        els.inlineConfirm.classList.remove('qn-inline-confirm--in');
        // Re-trigger animation
        void els.inlineConfirm.offsetWidth;
        els.inlineConfirm.classList.add('qn-inline-confirm--in');
        clearTimeout(flashInlineConfirm._t);
        flashInlineConfirm._t = setTimeout(function () {
            if (els.inlineConfirm) els.inlineConfirm.hidden = true;
        }, 2400);
    }

    // -------------------------------------------------------------- recent panel
    function setRecentLoading() {
        state.loading = true;
        if (!els.recentStatus) return;
        els.recentStatus.hidden = false;
        els.recentStatus.innerHTML =
            '<div class="qn-loading">' +
                '<span class="qn-spinner" aria-hidden="true"></span>' +
                '<span>Loading saved notes…</span>' +
            '</div>';
        if (els.recentList) els.recentList.hidden = true;
    }

    function setRecentEmpty() {
        if (!els.recentStatus) return;
        els.recentStatus.hidden = false;
        els.recentStatus.innerHTML =
            '<div class="qn-empty">' +
                '<i class="bi bi-journal-x" aria-hidden="true"></i>' +
                '<div class="qn-empty-title">No saved notes yet</div>' +
                '<div class="qn-empty-sub">Notes you save will appear here.</div>' +
            '</div>';
        if (els.recentList) {
            els.recentList.hidden = true;
            els.recentList.innerHTML = '';
        }
    }

    function setRecentError(msg) {
        if (!els.recentStatus) return;
        els.recentStatus.hidden = false;
        els.recentStatus.innerHTML =
            '<div class="qn-error-box" role="alert">' +
                '<i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>' +
                '<span>' + esc(msg || 'Failed to load saved notes.') + '</span>' +
            '</div>';
        if (els.recentList) {
            els.recentList.hidden = true;
            els.recentList.innerHTML = '';
        }
    }

    function sortNotes(list) {
        return (list || []).slice().sort(function (a, b) {
            var ap = a && a.pinned ? 1 : 0;
            var bp = b && b.pinned ? 1 : 0;
            if (ap !== bp) return bp - ap;            // pinned first
            var at = new Date(a && (a.created_at || a.updated_at) || 0).getTime();
            var bt = new Date(b && (b.created_at || b.updated_at) || 0).getTime();
            return bt - at;                            // newest first
        });
    }

    function renderRecent() {
        if (!els.recentList) return;
        var list = sortNotes(state.notes).slice(0, 20);
        if (els.recentCount) {
            els.recentCount.textContent = String(list.length);
            els.recentCount.hidden = list.length === 0;
        }
        if (!list.length) {
            setRecentEmpty();
            return;
        }
        els.recentStatus.hidden = true;
        els.recentList.hidden = false;
        var html = '';
        for (var i = 0; i < list.length; i++) {
            var n = list[i] || {};
            var key = n.key || ('q:' + n.id);
            var isBoard = n.origin === 'board';
            var title = (n.title && n.title.trim()) || firstLine(n.body, 80) || 'Untitled note';
            var preview = excerpt(n.body, 140);
            var when = n.updated_at || n.created_at;
            html +=
                '<li class="qn-note' + (n.pinned ? ' qn-note--pinned' : '') + '" data-id="' + esc(key) + '" data-origin="' + esc(n.origin || 'quick') + '">' +
                    '<div class="qn-note-main">' +
                        '<div class="qn-note-head">' +
                            '<span class="qn-note-title">' + esc(title) + '</span>' +
                            (isBoard ? '<span class="qn-origin-badge" title="From the notes board"><i class="bi bi-easel" aria-hidden="true"></i>Board</span>' : '') +
                            (n.pinned ? '<span class="qn-pin-badge" title="Pinned"><i class="bi bi-pin-angle-fill" aria-hidden="true"></i>Pinned</span>' : '') +
                        '</div>' +
                        (preview ? '<div class="qn-note-body">' + esc(preview) + '</div>' : '') +
                        '<div class="qn-note-meta">' +
                            '<i class="bi bi-clock" aria-hidden="true"></i>' +
                            '<span>' + esc(timeAgo(when)) + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="qn-note-actions">' +
                        (isBoard ? '' :
                        '<button type="button" class="qn-act qn-act-pin' + (n.pinned ? ' is-on' : '') + '" ' +
                                'data-action="' + (n.pinned ? 'unpin' : 'pin') + '" ' +
                                'title="' + (n.pinned ? 'Unpin' : 'Pin') + '" ' +
                                'aria-label="' + (n.pinned ? 'Unpin note' : 'Pin note') + '">' +
                            '<i class="bi ' + (n.pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle') + '" aria-hidden="true"></i>' +
                        '</button>') +
                        '<button type="button" class="qn-act qn-act-edit" data-action="edit" title="Edit" aria-label="Edit note">' +
                            '<i class="bi bi-pencil" aria-hidden="true"></i>' +
                        '</button>' +
                        '<button type="button" class="qn-act qn-act-delete" data-action="delete" title="Delete" aria-label="Delete note">' +
                            '<i class="bi bi-trash" aria-hidden="true"></i>' +
                        '</button>' +
                    '</div>' +
                '</li>';
        }
        els.recentList.innerHTML = html;
        // Paint gradient / glassmorphism backgrounds (shared NoteBG).
        if (window.NoteBG) {
            var rows = els.recentList.querySelectorAll('.qn-note');
            for (var r = 0; r < rows.length; r++) {
                var rkey = rows[r].getAttribute('data-id');
                var match = list.filter(function (x) { return String(x.key || ('q:' + x.id)) === String(rkey); })[0];
                window.NoteBG.apply(rows[r], match && match.background_color);
            }
        }
    }

    function loadRecent(opts) {
        opts = opts || {};
        if (state.loading) return Promise.resolve(state.notes);
        if (opts.showSpinner !== false) setRecentLoading();
        var fetchP = window.NotesBridge
            ? window.NotesBridge.list()
            : api('GET', '/api/quick-notes').then(function (data) {
                var arr = Array.isArray(data) ? data
                        : (data && Array.isArray(data.data)) ? data.data
                        : (data && Array.isArray(data.notes)) ? data.notes
                        : [];
                return arr.map(function (n) {
                    return { key: 'q:' + n.id, origin: 'quick', id: n.id, title: n.title || '',
                             body: n.body || '', background_color: n.background_color || '',
                             pinned: !!n.pinned, created_at: n.created_at, updated_at: n.updated_at, raw: n };
                });
            });
        return fetchP
            .then(function (arr) {
                state.notes = arr;
                state.loaded = true;
                renderRecent();
                if (els.refreshBtn) els.refreshBtn.hidden = false;
                // If a specific note was requested, prefill it now (quick-note id).
                if (state.pendingOpenId != null) {
                    var want = String(state.pendingOpenId);
                    state.pendingOpenId = null;
                    var match = arr.filter(function (x) { return x.origin === 'quick' && String(x.id) === want; })[0];
                    if (match) fillForm(match);
                }
                return arr;
            })
            .catch(function (err) {
                setRecentError(err && err.message);
                throw err;
            })
            .then(function (v) { state.loading = false; return v; }, function (e) { state.loading = false; throw e; });
    }

    // -------------------------------------------------------------- save
    function save() {
        if (state.saving) return;
        var title = (els.titleInput && els.titleInput.value || '').trim();
        var body  = (els.bodyInput  && els.bodyInput.value  || '').trim();
        var pinned = !!(els.pinInput && els.pinInput.checked);

        if (!body) {
            showBodyError();
            if (els.bodyInput) els.bodyInput.focus();
            return;
        }
        hideBodyError();

        var bg = (els.bgInput && els.bgInput.value || '').trim();
        var editingRec = state.editingRec;
        setSaving(true);

        var req;
        if (editingRec) {
            // Edit — route to the note's origin store via the bridge.
            var fields = { title: title, body: body, background_color: bg || null };
            if (editingRec.origin === 'quick') fields.pinned = pinned;
            req = window.NotesBridge
                ? window.NotesBridge.save(editingRec, fields)
                : api('PATCH', '/api/quick-notes/' + encodeURIComponent(editingRec.id), fields)
                    .then(function (d) { return window.NotesBridge ? null : (d && (d.data || d.note)); });
        } else {
            // New note → always a quick note.
            req = api('POST', '/api/quick-notes', { title: title, body: body, pinned: pinned, background_color: bg || null })
                .then(function (d) {
                    var raw = (d && (d.data || d.note || d)) || null;
                    return raw && window.NotesBridge ? window.NotesBridge._normQuick(raw) : raw;
                });
        }

        req
            .then(function (note) {
                // note is a normalized record (or null). Patch local state.
                if (note && note.key) {
                    var existingIx = -1;
                    for (var i = 0; i < state.notes.length; i++) {
                        if (String(state.notes[i].key) === String(note.key)) { existingIx = i; break; }
                    }
                    if (existingIx >= 0) state.notes[existingIx] = note;
                    else                 state.notes.unshift(note);
                    renderRecent();
                } else {
                    loadRecent({ showSpinner: false });
                }
                flashInlineConfirm(editingRec ? 'Note updated.' : 'Saved.');
                toast('success', editingRec ? 'Note updated' : 'Note saved', '');
                notifyChanged(editingRec ? 'update' : 'create', note || editingRec);
                clearForm();
                if (els.titleInput) els.titleInput.focus();
            })
            .catch(function (err) {
                toast('error', 'Could not save note', (err && err.message) || 'Please try again.');
            })
            .then(function () { setSaving(false); });
    }

    // -------------------------------------------------------------- pin / unpin
    function togglePin(key, makePinned) {
        var rec = findByKey(key);
        if (!rec || rec.origin === 'board') return;   // board notes can't pin
        var prev = rec.pinned;
        rec.pinned = !!makePinned;                     // optimistic
        renderRecent();
        var op = window.NotesBridge
            ? window.NotesBridge.setPinned(rec, !!makePinned)
            : api('POST', '/api/quick-notes/' + encodeURIComponent(rec.id) + (makePinned ? '/pin' : '/unpin'));
        return op
            .then(function () { notifyChanged('pin', rec); })
            .catch(function (err) {
                rec.pinned = !!prev;
                renderRecent();
                toast('error', 'Pin failed', (err && err.message) || 'Please try again.');
            });
    }

    // -------------------------------------------------------------- delete
    function deleteNote(key) {
        var rec = findByKey(key);
        if (!rec) return;
        var title = (rec.title || firstLine(rec.body, 60)) || 'this note';
        return confirmDialog({
            title: 'Delete note?',
            message: 'Permanently delete <strong>' + esc(title) + '</strong>? This cannot be undone.',
            html: true,
            icon: 'bi-trash',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            confirmClass: 'btn-danger'
        }).then(function (ok) {
            if (!ok) return;
            var op = window.NotesBridge
                ? window.NotesBridge.remove(rec)
                : api('DELETE', '/api/quick-notes/' + encodeURIComponent(rec.id));
            return op
                .then(function () {
                    state.notes = state.notes.filter(function (n) { return String(n.key) !== String(rec.key); });
                    renderRecent();
                    if (String(state.editingId) === String(rec.key)) clearForm();
                    toast('success', 'Note deleted', '');
                    notifyChanged('delete', rec);
                })
                .catch(function (err) {
                    toast('error', 'Delete failed', (err && err.message) || 'Please try again.');
                });
        });
    }

    // -------------------------------------------------------------- recent panel events
    function onRecentClick(e) {
        var btn = e.target && e.target.closest && e.target.closest('.qn-act');
        if (!btn) return;
        var row = btn.closest('.qn-note');
        if (!row) return;
        var key = row.getAttribute('data-id');
        var action = btn.getAttribute('data-action');
        if (!key || !action) return;
        e.preventDefault();
        if (action === 'pin')         return togglePin(key, true);
        if (action === 'unpin')       return togglePin(key, false);
        if (action === 'delete')      return deleteNote(key);
        if (action === 'edit') {
            var note = findByKey(key);
            if (note) fillForm(note);
        }
    }

    function toggleRecent(forceOpen) {
        if (!els.recentPanel || !els.recentToggle) return;
        var isOpen = !els.recentPanel.hidden;
        var nextOpen = (typeof forceOpen === 'boolean') ? forceOpen : !isOpen;
        els.recentPanel.hidden = !nextOpen;
        els.recentToggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
        els.recentToggle.classList.toggle('qn-toggle--open', nextOpen);
        if (els.recentToggleText) {
            els.recentToggleText.textContent = nextOpen ? 'Hide saved notes' : 'View saved notes';
        }
        if (nextOpen && !state.loaded) loadRecent();
    }

    // -------------------------------------------------------------- wire up
    function wire() {
        if (els.form && !els.form.__qnWired) {
            els.form.__qnWired = true;
            els.form.addEventListener('submit', function (e) {
                e.preventDefault();
                save();
            });
        }
        if (els.titleInput && !els.titleInput.__qnWired) {
            els.titleInput.__qnWired = true;
            els.titleInput.addEventListener('input', updateTitleCount);
        }
        if (els.bodyInput && !els.bodyInput.__qnWired) {
            els.bodyInput.__qnWired = true;
            els.bodyInput.addEventListener('input', function () {
                hideBodyError();
                autosizeBody();
            });
            els.bodyInput.addEventListener('keydown', function (e) {
                // Cmd+Enter / Ctrl+Enter saves
                if ((e.metaKey || e.ctrlKey) && (e.key === 'Enter' || e.keyCode === 13)) {
                    e.preventDefault();
                    save();
                }
            });
        }
        if (els.saveBtn && !els.saveBtn.__qnWired) {
            els.saveBtn.__qnWired = true;
            els.saveBtn.addEventListener('click', function (e) { e.preventDefault(); save(); });
        }
        if (els.cancelBtn && !els.cancelBtn.__qnWired) {
            els.cancelBtn.__qnWired = true;
            // Bootstrap dismisses; we also reset form state so next open is clean.
        }
        if (els.recentToggle && !els.recentToggle.__qnWired) {
            els.recentToggle.__qnWired = true;
            els.recentToggle.addEventListener('click', function () { toggleRecent(); });
        }
        if (els.recentList && !els.recentList.__qnWired) {
            els.recentList.__qnWired = true;
            els.recentList.addEventListener('click', onRecentClick);
        }
        if (els.swatches && !els.swatches.__qnWired) {
            els.swatches.__qnWired = true;
            els.swatches.addEventListener('click', function (e) {
                var sw = e.target.closest && e.target.closest('.note-swatch');
                if (!sw) return;
                e.preventDefault();
                if (els.bgInput) els.bgInput.value = sw.getAttribute('data-note-swatch') || '';
                els.swatches.querySelectorAll('.note-swatch').forEach(function (b) {
                    var on = b === sw;
                    b.classList.toggle('is-active', on);
                    b.setAttribute('aria-checked', on ? 'true' : 'false');
                });
            });
        }
        if (els.refreshBtn && !els.refreshBtn.__qnWired) {
            els.refreshBtn.__qnWired = true;
            els.refreshBtn.addEventListener('click', function () { loadRecent(); });
        }
        if (els.modal && !els.modal.__qnWired) {
            els.modal.__qnWired = true;
            // Reset state every time the modal hides so it always reopens clean.
            els.modal.addEventListener('hidden.bs.modal', function () {
                clearForm();
                toggleRecent(false);
            });
            els.modal.addEventListener('shown.bs.modal', function () {
                // Focus title or body depending on whether we're editing.
                if (state.editingId) {
                    if (els.bodyInput) els.bodyInput.focus();
                } else {
                    if (els.titleInput) els.titleInput.focus();
                }
            });
        }
    }

    function pickRefs() {
        els.modal             = document.getElementById('quickNoteModal');
        if (!els.modal) return false;
        els.form              = els.modal.querySelector('#quickNoteForm');
        els.idInput           = els.modal.querySelector('#qnNoteId');
        els.titleInput        = els.modal.querySelector('#qnTitle');
        els.titleCount        = els.modal.querySelector('#qnTitleCount');
        els.bodyInput         = els.modal.querySelector('#qnBody');
        els.bodyError         = els.modal.querySelector('#qnBodyError');
        els.pinInput          = els.modal.querySelector('#qnPin');
        els.swatches          = els.modal.querySelector('#qnSwatches');
        els.bgInput           = els.modal.querySelector('#qnBg');
        els.inlineConfirm     = els.modal.querySelector('#qnInlineConfirm');
        els.inlineConfirmText = els.modal.querySelector('#qnInlineConfirmText');
        els.recentToggle      = els.modal.querySelector('#qnRecentToggle');
        els.recentToggleText  = els.modal.querySelector('.qn-toggle-text');
        els.recentCount       = els.modal.querySelector('#qnRecentCount');
        els.recentPanel       = els.modal.querySelector('#qnRecentPanel');
        els.recentList        = els.modal.querySelector('#qnRecentList');
        els.recentStatus      = els.modal.querySelector('#qnRecentStatus');
        els.refreshBtn        = els.modal.querySelector('#qnRefresh');
        els.saveBtn           = els.modal.querySelector('#qnSaveBtn');
        els.saveBtnLabel      = els.modal.querySelector('#qnSaveBtnLabel');
        els.saveBtnSpinner    = els.modal.querySelector('.qn-btn-spinner');
        els.cancelBtn         = els.modal.querySelector('#qnCancelBtn');
        return true;
    }

    // -------------------------------------------------------------- open API
    function open(options) {
        options = options || {};
        if (!pickRefs()) {
            // Modal markup hasn't been included on this page.
            // eslint-disable-next-line no-console
            console.warn('[quick-note] #quickNoteModal not found in DOM.');
            return;
        }
        wire();

        if (options.id != null) {
            // If we already have the note locally, prefill now; otherwise queue.
            var match = null;
            for (var i = 0; i < state.notes.length; i++) {
                if (String(state.notes[i].id) === String(options.id)) { match = state.notes[i]; break; }
            }
            if (match) fillForm(match);
            else       { state.pendingOpenId = options.id; clearForm(); }
        } else {
            clearForm();
        }

        // Show modal (use Bootstrap if available; otherwise fall back to classes).
        if (window.bootstrap && window.bootstrap.Modal) {
            var inst = window.bootstrap.Modal.getInstance(els.modal) || new window.bootstrap.Modal(els.modal);
            inst.show();
        } else {
            els.modal.classList.add('show');
            els.modal.style.display = 'block';
            els.modal.setAttribute('aria-hidden', 'false');
        }

        // Load recent panel data eagerly so it's ready when toggled.
        loadRecent({ showSpinner: !state.loaded });
    }

    // -------------------------------------------------------------- live sync
    // Announce our own changes, and refresh when another surface (notes drawer,
    // dashboard widget, notes page) changes a quick note — no page reload.
    var _selfEmitting = false;
    function notifyChanged(action, note) {
        if (!window.NotesSync) return;
        _selfEmitting = true;
        try { window.NotesSync.emit((note && note.origin) || 'quick', { action: action, note: note }); }
        finally { _selfEmitting = false; }
    }
    // notes-sync.js is deferred and may not be parsed when this script first
    // runs (the modal partial loads quick-note.js inline). Bind once it exists —
    // on DOMContentLoaded all deferred scripts have executed.
    function bindSync() {
        if (bindSync.done || !window.NotesSync) return;
        bindSync.done = true;
        window.NotesSync.on(function () {
            if (_selfEmitting) return;            // ignore our own echo
            // Merged view → refresh on ANY note change (quick or board).
            if (state.loaded && !state.loading) loadRecent({ showSpinner: false });
        });
    }
    if (window.NotesSync) bindSync();
    else if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindSync);
    else bindSync();

    // -------------------------------------------------------------- exposed API
    window.openQuickNoteModal = open;
    window.quickNote = {
        __inited: true,
        open: open,
        refresh: function () { return loadRecent({ showSpinner: false }); }
    };

    // If the modal exists at script load time, wire it up so external triggers
    // (e.g. data-bs-toggle="modal" data-bs-target="#quickNoteModal") also work.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            if (pickRefs()) wire();
        });
    } else {
        if (pickRefs()) wire();
    }
})();
