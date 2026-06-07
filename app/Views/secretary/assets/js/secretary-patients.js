/* global document, window, fetch, bootstrap */
/*
 * Secretary patient list — v11 organizational features (RTL/Arabic, glass).
 * Additive: the existing server-rendered TABLE stays; this adds CARDS + FOLDERS
 * views driven by the clinic-scoped API (/api/secretary/…), plus per-patient
 * color markers + tags. Backend: SecretaryPatientsController. See V11_SEC_LAYOUT §14.
 */
(function () {
    'use strict';

    var PALETTE = ['#ef4444', '#f59e0b', '#10b981', '#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899', '#64748b'];

    var state = {
        view: localStorage.getItem('secPatientsView') || 'table', // table | cards | folders
        folder: null,        // open folder id (folders view)
        folderName: '',
        selection: new Set(),
        folders: [],
        tags: []
    };

    // ----- DOM -------------------------------------------------------------
    var tableView   = document.getElementById('secTableView');
    var cardsView   = document.getElementById('secCardsView');
    var foldersView = document.getElementById('secFoldersView');
    if (!cardsView || !foldersView) return; // page didn't include the containers

    // ----- helpers ---------------------------------------------------------
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }
    function api(url, opts) {
        return fetch(url, Object.assign({ headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' } }, opts || {}))
            .then(function (r) { return r.json().catch(function () { return {}; }); });
    }
    function age(dob) {
        if (!dob) return '';
        var d = new Date(dob); if (isNaN(d)) return '';
        return Math.floor((Date.now() - d.getTime()) / 31557600000);
    }
    function fullName(p) { return (esc(p.first_name || '') + ' ' + esc(p.last_name || '')).replace(/\s+/g, ' ').trim(); }
    function initials(p) {
        var a = (p.first_name || '').trim(), b = (p.last_name || '').trim();
        return ((a ? a[0] : '') + (b ? b[0] : '')) || '؟';
    }
    function toast(msg, kind) {
        if (window.showNotification) { window.showNotification(msg, kind || 'success'); return; }
        // fallback
        try { console.log('[secPatients]', msg); } catch (e) {}
    }

    // Read the page's existing filter inputs into query params.
    function filterParams() {
        var p = new URLSearchParams();
        var q = (document.getElementById('quickSearch') || {}).value || (document.getElementById('searchInput') || {}).value || '';
        if (q.trim()) p.set('search', q.trim());
        var gender = (document.getElementById('genderFilter') || {}).value || '';
        if (gender) p.set('gender', gender);
        // active color filters
        cardsView.querySelectorAll('.sec-color-filter.active').forEach(function (b) { p.append('color', b.dataset.color); });
        foldersView.querySelectorAll('.sec-color-filter.active').forEach(function (b) { p.append('color', b.dataset.color); });
        return p;
    }

    // ====================================================================
    //  VIEW SWITCHING
    // ====================================================================
    function setView(v) {
        state.view = v;
        localStorage.setItem('secPatientsView', v);
        if (tableView)   tableView.style.display   = (v === 'table') ? '' : 'none';
        cardsView.style.display   = (v === 'cards') ? '' : 'none';
        foldersView.style.display = (v === 'folders') ? '' : 'none';
        document.querySelectorAll('.sec-view-btn').forEach(function (b) {
            b.classList.toggle('active', b.dataset.view === v);
        });
        if (v === 'cards') loadCards();
        if (v === 'folders') { state.folder = null; loadFolders(); }
    }

    // ====================================================================
    //  CARDS VIEW
    // ====================================================================
    function patientCardHtml(p, opts) {
        opts = opts || {};
        var marker = p.marker ? '<span class="sec-marker-dot" style="background:' + esc(p.marker) + '" title="علامة"></span>' : '';
        var tags = (p.tags || []).map(function (t) {
            return '<span class="sec-tag-chip" style="background:' + esc(t.color) + '22;color:' + esc(t.color) + ';border-color:' + esc(t.color) + '55">' + esc(t.name) + '</span>';
        }).join('');
        var sel = opts.selectable
            ? '<input type="checkbox" class="form-check-input sec-pcheck" data-id="' + p.id + '"' + (state.selection.has(String(p.id)) ? ' checked' : '') + '>'
            : '';
        return '' +
            '<div class="sec-pcard' + (state.selection.has(String(p.id)) ? ' is-selected' : '') + '" data-id="' + p.id + '">' +
                '<div class="sec-pcard-top">' + sel + marker +
                    '<span class="sec-avatar ' + (p.gender === 'Female' ? 'female' : 'male') + '">' + esc(initials(p)) + '</span>' +
                '</div>' +
                '<div class="sec-pcard-name">' + fullName(p) + '</div>' +
                '<div class="sec-pcard-meta">' + (age(p.dob) !== '' ? (age(p.dob) + ' سنة · ') : '') + (p.gender === 'Female' ? 'أنثى' : 'ذكر') + '</div>' +
                '<div class="sec-pcard-meta"><i class="bi bi-telephone"></i> ' + esc(p.phone || '—') + '</div>' +
                (tags ? ('<div class="sec-pcard-tags">' + tags + '</div>') : '') +
                '<div class="sec-pcard-actions">' +
                    '<a class="btn btn-sm btn-outline-primary" href="/secretary/patients/' + p.id + '" title="عرض"><i class="bi bi-eye"></i></a>' +
                    '<button class="btn btn-sm btn-outline-secondary sec-mark-btn" data-id="' + p.id + '" title="علامة لونية"><i class="bi bi-palette"></i></button>' +
                    '<button class="btn btn-sm btn-outline-secondary sec-tag-btn" data-id="' + p.id + '" title="وسوم"><i class="bi bi-tags"></i></button>' +
                    '<a class="btn btn-sm btn-outline-success" href="/secretary/bookings?patient_id=' + p.id + '" title="حجز"><i class="bi bi-calendar-plus"></i></a>' +
                '</div>' +
            '</div>';
    }

    function colorFilterBar() {
        return '<div class="sec-color-filters">' +
            PALETTE.map(function (c) { return '<button class="sec-color-filter" data-color="' + c + '" style="background:' + c + '" title="فلترة باللون"></button>'; }).join('') +
            '</div>';
    }

    function loadCards() {
        cardsView.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        var p = filterParams(); p.set('per_page', '60');
        api('/api/secretary/patients-list?' + p.toString()).then(function (res) {
            var list = (res && res.data && res.data.patients) || [];
            var total = (res && res.data && res.data.total) || list.length;
            cardsView.innerHTML =
                '<div class="sec-toolbar">' + colorFilterBar() +
                    '<span class="text-muted arabic-text">المجموع: ' + total + ' مريض</span>' +
                    '<button class="btn btn-sm btn-outline-secondary sec-export"><i class="bi bi-download me-1"></i>تصدير CSV</button>' +
                '</div>' +
                (list.length ? ('<div class="sec-pgrid">' + list.map(function (p) { return patientCardHtml(p); }).join('') + '</div>')
                             : '<div class="text-center py-5 text-muted arabic-text">لا يوجد مرضى مطابقون</div>');
        });
    }

    // ====================================================================
    //  FOLDERS VIEW
    // ====================================================================
    function folderCardHtml(f) {
        return '' +
            '<div class="sec-folder-card" data-id="' + f.id + '" style="background:' + esc(f.gradient_color || '') + '">' +
                '<button class="sec-folder-del" data-id="' + f.id + '" title="حذف"><i class="bi bi-trash"></i></button>' +
                '<button class="sec-folder-edit" data-id="' + f.id + '" data-name="' + esc(f.name) + '" title="إعادة تسمية"><i class="bi bi-pencil"></i></button>' +
                '<i class="bi ' + esc(f.icon || 'bi-folder') + ' sec-folder-icon"></i>' +
                '<div class="sec-folder-name">' + esc(f.name) + '</div>' +
                '<div class="sec-folder-count">' + (f.patient_count || 0) + ' مريض</div>' +
            '</div>';
    }

    function loadFolders() {
        state.selection.clear();
        foldersView.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        api('/api/secretary/patient-folders').then(function (res) {
            state.folders = (res && res.folders) || [];
            foldersView.innerHTML =
                '<div class="sec-toolbar">' +
                    '<button class="btn btn-sm btn-primary sec-new-folder"><i class="bi bi-folder-plus me-1"></i>مجلد جديد</button>' +
                    '<span class="text-muted arabic-text">' + state.folders.length + ' مجلد · ' + ((res && res.total_patients) || 0) + ' مريض</span>' +
                '</div>' +
                (state.folders.length
                    ? ('<div class="sec-folder-grid">' + state.folders.map(folderCardHtml).join('') + '</div>')
                    : '<div class="text-center py-5 text-muted arabic-text">لا توجد مجلدات بعد — أنشئ أول مجلد</div>');
        });
    }

    function openFolder(id, name) {
        state.folder = id; state.folderName = name; state.selection.clear();
        foldersView.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        var p = filterParams(); p.set('folder', id); p.set('per_page', '100');
        api('/api/secretary/patients-list?' + p.toString()).then(function (res) {
            var list = (res && res.data && res.data.patients) || [];
            foldersView.innerHTML =
                '<div class="sec-toolbar">' +
                    '<button class="btn btn-sm btn-outline-secondary sec-folder-back"><i class="bi bi-arrow-right me-1"></i>المجلدات</button>' +
                    '<strong class="arabic-text">' + esc(name) + '</strong>' +
                    '<span class="text-muted arabic-text">' + list.length + ' مريض</span>' +
                '</div>' +
                (list.length ? ('<div class="sec-pgrid">' + list.map(function (p) { return patientCardHtml(p, { selectable: true }); }).join('') + '</div>')
                             : '<div class="text-center py-5 text-muted arabic-text">المجلد فارغ</div>') +
                bulkBarHtml();
            updateBulkBar();
        });
    }

    function bulkBarHtml() {
        return '<div class="sec-bulk-bar" id="secBulkBar" style="display:none">' +
            '<span class="sec-bulk-count">0</span>' +
            '<button class="btn btn-sm btn-light sec-bulk-move"><i class="bi bi-folder-symlink me-1"></i>نقل</button>' +
            '<button class="btn btn-sm btn-light sec-bulk-copy"><i class="bi bi-files me-1"></i>نسخ</button>' +
            '<button class="btn btn-sm btn-light sec-bulk-remove"><i class="bi bi-folder-minus me-1"></i>إزالة من المجلد</button>' +
            '<button class="btn btn-sm btn-light sec-bulk-clear">إلغاء</button>' +
            '</div>';
    }
    function updateBulkBar() {
        var bar = document.getElementById('secBulkBar');
        if (!bar) return;
        bar.style.display = state.selection.size ? 'flex' : 'none';
        var c = bar.querySelector('.sec-bulk-count'); if (c) c.textContent = state.selection.size + ' محدد';
    }

    // ----- folder operations ----------------------------------------------
    function promptFolderPicker(title, cb) {
        var opts = state.folders.map(function (f) { return '<option value="' + f.id + '">' + esc(f.name) + '</option>'; }).join('');
        var sel = window.prompt(title + '\n' + state.folders.map(function (f) { return f.id + ': ' + f.name; }).join('\n') + '\n\nاكتب رقم المجلد:');
        if (sel === null) return;
        var id = parseInt(sel, 10);
        if (state.folders.some(function (f) { return f.id === id; })) cb(id);
        else toast('مجلد غير صالح', 'danger');
    }

    function doBulk(mode) {
        var ids = Array.from(state.selection).map(Number);
        if (!ids.length) return;
        if (mode === 'remove') {
            api('/api/secretary/patient-folders/move', { method: 'POST', body: JSON.stringify({ patient_ids: ids, from: state.folder, mode: 'remove' }) })
                .then(function () { toast('تمت الإزالة'); openFolder(state.folder, state.folderName); });
            return;
        }
        promptFolderPicker(mode === 'move' ? 'نقل إلى مجلد' : 'نسخ إلى مجلد', function (to) {
            api('/api/secretary/patient-folders/move', { method: 'POST', body: JSON.stringify({ patient_ids: ids, from: state.folder, to: to, mode: mode }) })
                .then(function () { toast(mode === 'move' ? 'تم النقل' : 'تم النسخ'); openFolder(state.folder, state.folderName); });
        });
    }

    // ----- marker + tag pickers -------------------------------------------
    function openMarkerPicker(patientId, anchor) {
        closePopovers();
        var pop = document.createElement('div');
        pop.className = 'sec-popover';
        pop.innerHTML = PALETTE.map(function (c) { return '<button class="sec-pop-color" data-c="' + c + '" style="background:' + c + '"></button>'; }).join('') +
            '<button class="sec-pop-color sec-pop-clear" data-c="" title="مسح"><i class="bi bi-x"></i></button>';
        document.body.appendChild(pop);
        positionPop(pop, anchor);
        pop.addEventListener('click', function (e) {
            var b = e.target.closest('.sec-pop-color'); if (!b) return;
            api('/api/secretary/patient-marker/' + patientId, { method: 'POST', body: JSON.stringify({ color_code: b.dataset.c }) })
                .then(function () { closePopovers(); refreshCurrent(); });
        });
    }
    function openTagPicker(patientId, anchor) {
        closePopovers();
        api('/api/secretary/patient-tags').then(function (res) {
            state.tags = (res && res.tags) || [];
            var pop = document.createElement('div');
            pop.className = 'sec-popover sec-popover-tags';
            pop.innerHTML = (state.tags.length
                ? state.tags.map(function (t) { return '<label class="sec-pop-tag"><input type="checkbox" data-id="' + t.id + '"> <span style="color:' + esc(t.color) + '">' + esc(t.name) + '</span></label>'; }).join('')
                : '<div class="text-muted small arabic-text px-2">لا توجد وسوم — أنشئ وسماً</div>') +
                '<div class="sec-pop-newtag"><input type="text" class="form-control form-control-sm" placeholder="وسم جديد"><button class="btn btn-sm btn-primary">+</button></div>';
            document.body.appendChild(pop);
            positionPop(pop, anchor);
            pop.querySelectorAll('input[type=checkbox]').forEach(function (cb) {
                cb.addEventListener('change', function () {
                    var body = { patient_id: patientId };
                    body[cb.checked ? 'add' : 'remove'] = [parseInt(cb.dataset.id, 10)];
                    api('/api/secretary/patient-tags/assign', { method: 'POST', body: JSON.stringify(body) }).then(refreshCurrent);
                });
            });
            var newBtn = pop.querySelector('.sec-pop-newtag button'), newInp = pop.querySelector('.sec-pop-newtag input');
            newBtn.addEventListener('click', function () {
                var name = (newInp.value || '').trim(); if (!name) return;
                api('/api/secretary/patient-tags', { method: 'POST', body: JSON.stringify({ name: name }) }).then(function (r) {
                    if (r && r.ok) { api('/api/secretary/patient-tags/assign', { method: 'POST', body: JSON.stringify({ patient_id: patientId, add: [r.data.id] }) }).then(function () { closePopovers(); refreshCurrent(); }); }
                    else toast((r && r.error) || 'تعذّر إنشاء الوسم', 'danger');
                });
            });
        });
    }
    function positionPop(pop, anchor) {
        var r = anchor.getBoundingClientRect();
        pop.style.position = 'fixed';
        pop.style.top = Math.min(r.bottom + 6, window.innerHeight - pop.offsetHeight - 8) + 'px';
        pop.style.left = Math.max(8, r.left - pop.offsetWidth + r.width) + 'px';
        pop.style.zIndex = 1000020;
    }
    function closePopovers() { document.querySelectorAll('.sec-popover').forEach(function (p) { p.remove(); }); }

    function refreshCurrent() {
        if (state.view === 'cards') loadCards();
        else if (state.view === 'folders') { if (state.folder) openFolder(state.folder, state.folderName); else loadFolders(); }
    }

    // ====================================================================
    //  EVENTS
    // ====================================================================
    document.addEventListener('click', function (e) {
        // view toggle
        var vb = e.target.closest('.sec-view-btn'); if (vb) { setView(vb.dataset.view); return; }
        // export
        if (e.target.closest('.sec-export')) { var p = filterParams(); window.location.href = '/api/secretary/patients-export?' + p.toString(); return; }
        // color filter toggle
        var cf = e.target.closest('.sec-color-filter'); if (cf) { cf.classList.toggle('active'); refreshCurrent(); return; }
        // marker / tag pickers
        var mb = e.target.closest('.sec-mark-btn'); if (mb) { openMarkerPicker(mb.dataset.id, mb); return; }
        var tb = e.target.closest('.sec-tag-btn'); if (tb) { openTagPicker(tb.dataset.id, tb); return; }
        // folders
        if (e.target.closest('.sec-new-folder')) {
            var name = window.prompt('اسم المجلد الجديد:'); if (!name) return;
            api('/api/secretary/patient-folders', { method: 'POST', body: JSON.stringify({ name: name }) }).then(function (r) { if (r && r.ok) loadFolders(); else toast((r && r.error) || 'خطأ', 'danger'); });
            return;
        }
        var fdel = e.target.closest('.sec-folder-del'); if (fdel) { e.stopPropagation();
            if (window.confirm('حذف هذا المجلد؟')) api('/api/secretary/patient-folders/' + fdel.dataset.id, { method: 'DELETE' }).then(loadFolders);
            return;
        }
        var fedit = e.target.closest('.sec-folder-edit'); if (fedit) { e.stopPropagation();
            var nn = window.prompt('إعادة تسمية المجلد:', fedit.dataset.name); if (nn === null || !nn.trim()) return;
            api('/api/secretary/patient-folders/' + fedit.dataset.id, { method: 'POST', body: JSON.stringify({ name: nn.trim() }) }).then(loadFolders);
            return;
        }
        var fcard = e.target.closest('.sec-folder-card'); if (fcard) { var f = state.folders.find(function (x) { return String(x.id) === fcard.dataset.id; }); openFolder(fcard.dataset.id, f ? f.name : ''); return; }
        if (e.target.closest('.sec-folder-back')) { state.folder = null; loadFolders(); return; }
        // bulk
        if (e.target.closest('.sec-bulk-move')) { doBulk('move'); return; }
        if (e.target.closest('.sec-bulk-copy')) { doBulk('copy'); return; }
        if (e.target.closest('.sec-bulk-remove')) { doBulk('remove'); return; }
        if (e.target.closest('.sec-bulk-clear')) { state.selection.clear(); refreshCurrent(); return; }
        // close popovers on outside click
        if (!e.target.closest('.sec-popover') && !e.target.closest('.sec-mark-btn') && !e.target.closest('.sec-tag-btn')) closePopovers();
    });

    // selection checkboxes
    document.addEventListener('change', function (e) {
        var cb = e.target.closest('.sec-pcheck'); if (!cb) return;
        if (cb.checked) state.selection.add(cb.dataset.id); else state.selection.delete(cb.dataset.id);
        var card = cb.closest('.sec-pcard'); if (card) card.classList.toggle('is-selected', cb.checked);
        updateBulkBar();
    });

    // re-run current view when the page's filters change
    ['quickSearch', 'searchInput', 'genderFilter'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function () { if (state.view !== 'table') refreshCurrent(); });
    });

    // init
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { setView(state.view); });
    else setView(state.view);

    window.secPatients = { setView: setView, reload: refreshCurrent };
})();
