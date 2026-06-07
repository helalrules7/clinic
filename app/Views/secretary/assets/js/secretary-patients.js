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

    // ----- modals (replace window.prompt / window.confirm) -----------------
    function buildModal(bodyHtml, footerHtml) {
        var wrap = document.createElement('div');
        wrap.className = 'modal fade sec-modal';
        wrap.setAttribute('tabindex', '-1');
        wrap.setAttribute('dir', 'rtl');
        wrap.innerHTML =
            '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
                bodyHtml + '<div class="modal-footer">' + footerHtml + '</div>' +
            '</div></div>';
        document.body.appendChild(wrap);
        var bs = (window.bootstrap && bootstrap.Modal) ? new bootstrap.Modal(wrap) : null;
        wrap.addEventListener('hidden.bs.modal', function () { wrap.remove(); });
        return { el: wrap, show: function () { if (bs) bs.show(); else wrap.style.display = 'block'; },
                 hide: function () { if (bs) bs.hide(); else wrap.remove(); } };
    }
    function head(title) {
        return '<div class="modal-header"><h5 class="modal-title arabic-text">' + esc(title) + '</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>';
    }
    function secPrompt(o) {
        o = o || {};
        return new Promise(function (resolve) {
            var m = buildModal(
                head(o.title || '') +
                '<div class="modal-body"><label class="form-label arabic-text">' + esc(o.label || '') + '</label>' +
                '<input type="text" class="form-control sec-modal-input" value="' + esc(o.value || '') + '" placeholder="' + esc(o.placeholder || '') + '"></div>',
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>' +
                '<button type="button" class="btn btn-primary sec-modal-ok">' + esc(o.confirmText || 'حفظ') + '</button>'
            );
            var input = m.el.querySelector('.sec-modal-input'), done = false;
            function ok() { if (done) return; var v = (input.value || '').trim(); done = true; m.hide(); resolve(v || null); }
            m.el.querySelector('.sec-modal-ok').addEventListener('click', ok);
            input.addEventListener('keydown', function (e) { if (e.key === 'Enter') ok(); });
            m.el.addEventListener('hidden.bs.modal', function () { if (!done) resolve(null); });
            m.show();
            setTimeout(function () { try { input.focus(); input.select(); } catch (e) {} }, 220);
        });
    }
    function secConfirm(o) {
        o = o || {};
        return new Promise(function (resolve) {
            var m = buildModal(
                head(o.title || 'تأكيد') +
                '<div class="modal-body arabic-text">' + esc(o.message || '') + '</div>',
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>' +
                '<button type="button" class="btn ' + (o.danger ? 'btn-danger' : 'btn-primary') + ' sec-modal-ok">' + esc(o.confirmText || 'تأكيد') + '</button>'
            );
            var done = false;
            m.el.querySelector('.sec-modal-ok').addEventListener('click', function () { done = true; m.hide(); resolve(true); });
            m.el.addEventListener('hidden.bs.modal', function () { if (!done) resolve(false); });
            m.show();
        });
    }
    function secFolderPick(o) {
        o = o || {};
        return new Promise(function (resolve) {
            var list = state.folders.filter(function (f) { return !o.exclude || String(f.id) !== String(o.exclude); });
            var rows = list.length ? list.map(function (f) {
                return '<label class="sec-pick-row"><input type="radio" name="secpick" value="' + f.id + '">' +
                    '<i class="bi ' + esc(f.icon || 'bi-folder') + '"></i><span>' + esc(f.name) + '</span>' +
                    '<span class="text-muted">(' + (f.patient_count || 0) + ')</span></label>';
            }).join('') : '<div class="text-muted arabic-text">لا توجد مجلدات — أنشئ مجلداً أولاً</div>';
            var m = buildModal(
                head(o.title || 'اختر مجلداً') + '<div class="modal-body sec-pick-list">' + rows + '</div>',
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>' +
                '<button type="button" class="btn btn-primary sec-modal-ok">' + esc(o.confirmText || 'تأكيد') + '</button>'
            );
            var done = false;
            m.el.querySelector('.sec-modal-ok').addEventListener('click', function () {
                var sel = m.el.querySelector('input[name="secpick"]:checked');
                if (!sel) return;
                done = true; m.hide(); resolve(parseInt(sel.value, 10));
            });
            m.el.addEventListener('hidden.bs.modal', function () { if (!done) resolve(null); });
            m.show();
        });
    }

    // Read the page's existing filter card inputs into query params, mapping the
    // band-style age/last-visit dropdowns to the API's min/max + date params.
    function val(id) { var el = document.getElementById(id); return el ? (el.value || '') : ''; }
    function filterParams() {
        var p = new URLSearchParams();
        var q = val('search') || val('quickSearch');
        if (q.trim()) p.set('search', q.trim());
        var gender = val('gender'); // #gender (filter select; the add-patient modal one comes later in DOM)
        if (gender === 'Male' || gender === 'Female') p.set('gender', gender);

        var AGE = { '0-18': [0, 18], '19-30': [19, 30], '31-50': [31, 50], '51-65': [51, 65], '65+': [65, null] };
        var ab = AGE[val('age_range')];
        if (ab) { p.set('age_min', ab[0]); if (ab[1] != null) p.set('age_max', ab[1]); }

        var lv = val('last_visit');
        if (lv === 'never') { p.set('last_visit', 'never'); }
        else if (lv) {
            var days = { today: 0, week: 7, month: 30, '3months': 90, '6months': 180, year: 365 }[lv];
            if (days != null) { var d = new Date(); d.setDate(d.getDate() - days); p.set('last_visit_from', d.toISOString().slice(0, 10)); }
        }
        // active color filters (cards/folders toolbars)
        cardsView.querySelectorAll('.sec-color-filter.active').forEach(function (b) { p.append('color', b.dataset.color); });
        foldersView.querySelectorAll('.sec-color-filter.active').forEach(function (b) { p.append('color', b.dataset.color); });
        return p;
    }

    // Decorate the server-rendered TABLE rows with this clinic's marker + tags.
    // Scope to the patient-name cell only — patient-hover.js also stamps profile
    // links in the actions column (.btn-group), which must not receive org chips.
    function decorateTable() {
        if (!tableView) return;
        tableView.querySelectorAll('.btn-group .sec-table-org').forEach(function (el) { el.remove(); });
        var names = tableView.querySelectorAll('tbody tr td:first-child .patient-hover-name[data-patient-id]');
        if (!names.length) return;
        var ids = [];
        names.forEach(function (n) { ids.push(n.getAttribute('data-patient-id')); });
        api('/api/secretary/patient-org-bulk?ids=' + ids.join(',')).then(function (res) {
            if (!res || !res.ok) return;
            var markers = res.markers || {}, tags = res.tags || {};
            names.forEach(function (n) {
                var pid = n.getAttribute('data-patient-id');
                var prev = n.parentNode.querySelector('.sec-table-org'); if (prev) prev.remove();
                var marker = markers[pid] ? '<span class="sec-marker-dot" style="position:static;border:none;background:' + esc(markers[pid]) + '"></span>' : '';
                var tg = (tags[pid] || []).map(function (t) {
                    return '<span class="sec-tag-chip" style="background:' + esc(t.color) + '22;color:' + esc(t.color) + ';border-color:' + esc(t.color) + '55">' + esc(t.name) + '</span>';
                }).join('');
                if (marker || tg) {
                    var span = document.createElement('span');
                    span.className = 'sec-table-org';
                    span.innerHTML = marker + tg;
                    n.parentNode.insertBefore(span, n.nextSibling);
                }
            });
        });
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
        if (v === 'table') decorateTable();
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
                '<div class="sec-pcard-name patient-hover-name" data-patient-id="' + p.id + '">' + fullName(p) + '</div>' +
                '<div class="sec-pcard-meta">' + (age(p.dob) !== '' ? (age(p.dob) + ' سنة · ') : '') + (p.gender === 'Female' ? 'أنثى' : 'ذكر') + '</div>' +
                '<div class="sec-pcard-meta"><i class="bi bi-telephone"></i> ' + esc(p.phone || '—') + '</div>' +
                (tags ? ('<div class="sec-pcard-tags">' + tags + '</div>') : '') +
                '<div class="sec-pcard-actions">' +
                    '<a class="btn btn-sm btn-outline-warning" href="/secretary/patients/' + p.id + '" title="عرض"><i class="bi bi-eye"></i></a>' +
                    '<a class="btn btn-sm btn-outline-info" href="/secretary/payments?patient_id=' + p.id + '" title="مدفوعات"><i class="bi bi-credit-card"></i></a>' +
                    '<a class="btn btn-sm btn-outline-success" href="/secretary/bookings?patient_id=' + p.id + '" title="حجز"><i class="bi bi-calendar-plus"></i></a>' +
                    '<button class="btn btn-sm btn-outline-secondary sec-mark-btn" data-id="' + p.id + '" title="علامة لونية"><i class="bi bi-palette"></i></button>' +
                    '<button class="btn btn-sm btn-outline-secondary sec-tag-btn" data-id="' + p.id + '" title="وسوم"><i class="bi bi-tags"></i></button>' +
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
            if (window.patientHover && typeof window.patientHover.retag === 'function') {
                window.patientHover.retag(cardsView);
            }
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
                    '<button class="btn btn-sm btn-outline-primary sec-auto-month"><i class="bi bi-calendar-month me-1"></i>تنظيم تلقائي بالشهور</button>' +
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
            if (window.patientHover && typeof window.patientHover.retag === 'function') {
                window.patientHover.retag(foldersView);
            }
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
    function doBulk(mode) {
        var ids = Array.from(state.selection).map(Number);
        if (!ids.length) return;
        if (mode === 'remove') {
            secConfirm({ title: 'إزالة من المجلد', message: 'إزالة ' + ids.length + ' مريض من هذا المجلد؟ (لن يُحذف المرضى أنفسهم)', confirmText: 'إزالة' }).then(function (ok) {
                if (!ok) return;
                api('/api/secretary/patient-folders/move', { method: 'POST', body: JSON.stringify({ patient_ids: ids, from: state.folder, mode: 'remove' }) })
                    .then(function () { toast('تمت الإزالة'); openFolder(state.folder, state.folderName); });
            });
            return;
        }
        secFolderPick({ title: mode === 'move' ? 'نقل إلى مجلد' : 'نسخ إلى مجلد', confirmText: mode === 'move' ? 'نقل' : 'نسخ', exclude: state.folder }).then(function (to) {
            if (!to) return;
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
            secPrompt({ title: 'مجلد جديد', label: 'اسم المجلد', placeholder: 'مثال: مرضى مميّزون', confirmText: 'إنشاء' }).then(function (name) {
                if (!name) return;
                api('/api/secretary/patient-folders', { method: 'POST', body: JSON.stringify({ name: name }) }).then(function (r) { if (r && r.ok) loadFolders(); else toast((r && r.error) || 'خطأ', 'danger'); });
            });
            return;
        }
        if (e.target.closest('.sec-auto-month')) {
            secConfirm({ title: 'تنظيم تلقائي بالشهور', message: 'سيتم إنشاء مجلد لكل شهر تسجيل وإضافة المرضى إليه تلقائياً (يمكن تكراره بأمان). متابعة؟', confirmText: 'تنظيم' }).then(function (ok) {
                if (!ok) return;
                var btn = document.querySelector('.sec-auto-month');
                if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
                api('/api/secretary/patient-folders/auto-month', { method: 'POST', body: '{}' }).then(function (r) {
                    if (r && r.ok) toast('تم إنشاء ' + r.folders_created + ' مجلد وتنظيم ' + r.patients_filed + ' مريض');
                    else toast((r && r.error) || 'خطأ', 'danger');
                    loadFolders();
                });
            });
            return;
        }
        var fdel = e.target.closest('.sec-folder-del'); if (fdel) { e.stopPropagation();
            secConfirm({ title: 'حذف المجلد', message: 'سيتم حذف هذا المجلد (ومجلداته الفرعية إن وُجدت). لن يُحذف المرضى أنفسهم.', danger: true, confirmText: 'حذف' }).then(function (ok) {
                if (ok) api('/api/secretary/patient-folders/' + fdel.dataset.id, { method: 'DELETE' }).then(loadFolders);
            });
            return;
        }
        var fedit = e.target.closest('.sec-folder-edit'); if (fedit) { e.stopPropagation();
            secPrompt({ title: 'إعادة تسمية المجلد', label: 'الاسم الجديد', value: fedit.dataset.name, confirmText: 'حفظ' }).then(function (nn) {
                if (!nn) return;
                api('/api/secretary/patient-folders/' + fedit.dataset.id, { method: 'POST', body: JSON.stringify({ name: nn }) }).then(loadFolders);
            });
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

    // re-run cards/folders when the page's filter inputs change (table keeps its own logic).
    ['search', 'quickSearch'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function () { if (state.view !== 'table') refreshCurrent(); });
    });
    ['gender', 'age_range', 'last_visit'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', function () { if (state.view !== 'table') refreshCurrent(); });
    });

    // init
    function boot() { setView(state.view); decorateTable(); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();

    window.secPatients = { setView: setView, reload: refreshCurrent };
})();
