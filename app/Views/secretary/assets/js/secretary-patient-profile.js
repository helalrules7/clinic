/* global document, window, fetch */
/*
 * Secretary patient PROFILE — v11 organizational header strip (color marker + tags).
 * Reuses the clinic-scoped endpoints (/api/secretary/patient-marker, /patient-tags).
 * The edit-patient modal is inline in patient_details.php. RTL + glass.
 */
(function () {
    'use strict';
    var PALETTE = ['#ef4444', '#f59e0b', '#10b981', '#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899', '#64748b'];
    var strip = document.getElementById('secProfileOrg');
    if (!strip) return;
    var pid = strip.getAttribute('data-patient');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }
    function api(url, opts) {
        return fetch(url, Object.assign({ headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' } }, opts || {}))
            .then(function (r) { return r.json().catch(function () { return {}; }); });
    }
    function closePop() { document.querySelectorAll('.sec-popover').forEach(function (p) { p.remove(); }); }
    function positionPop(pop, anchor) {
        var r = anchor.getBoundingClientRect();
        pop.style.position = 'fixed';
        pop.style.top = Math.min(r.bottom + 6, window.innerHeight - 220) + 'px';
        pop.style.left = Math.max(8, r.left - pop.offsetWidth + r.width) + 'px';
        pop.style.zIndex = 1000020;
    }

    var data = { marker: null, tags: [], all_tags: [] };

    function render() {
        var marker = data.marker
            ? '<button class="sec-marker-pill" id="secMarkBtn" title="تغيير العلامة"><span class="sec-marker-dot" style="position:static;border:none;background:' + esc(data.marker) + '"></span> العلامة</button>'
            : '<button class="btn btn-sm btn-outline-secondary" id="secMarkBtn"><i class="bi bi-palette me-1"></i>علامة لونية</button>';
        var tags = data.tags.map(function (t) {
            return '<span class="sec-tag-chip" style="background:' + esc(t.color) + '22;color:' + esc(t.color) + ';border-color:' + esc(t.color) + '55">' + esc(t.name) + '</span>';
        }).join('');
        strip.innerHTML = marker +
            '<button class="btn btn-sm btn-outline-secondary" id="secTagBtn"><i class="bi bi-tags me-1"></i>وسوم</button>' +
            (tags ? ('<span class="sec-profile-tags">' + tags + '</span>') : '');
    }

    function load() {
        api('/api/secretary/patient-org/' + pid).then(function (res) {
            if (res && res.ok) { data.marker = res.marker; data.tags = res.tags || []; data.all_tags = res.all_tags || []; render(); }
        });
    }

    function openMarkerPicker(anchor) {
        closePop();
        var pop = document.createElement('div'); pop.className = 'sec-popover';
        pop.innerHTML = PALETTE.map(function (c) { return '<button class="sec-pop-color" data-c="' + c + '" style="background:' + c + '"></button>'; }).join('') +
            '<button class="sec-pop-color sec-pop-clear" data-c=""><i class="bi bi-x"></i></button>';
        document.body.appendChild(pop); positionPop(pop, anchor);
        pop.addEventListener('click', function (e) {
            var b = e.target.closest('.sec-pop-color'); if (!b) return;
            api('/api/secretary/patient-marker/' + pid, { method: 'POST', body: JSON.stringify({ color_code: b.dataset.c }) }).then(function () { closePop(); load(); });
        });
    }
    function openTagPicker(anchor) {
        closePop();
        var assigned = {}; data.tags.forEach(function (t) { assigned[t.id] = true; });
        var pop = document.createElement('div'); pop.className = 'sec-popover sec-popover-tags';
        pop.innerHTML = (data.all_tags.length
            ? data.all_tags.map(function (t) { return '<label class="sec-pop-tag"><input type="checkbox" data-id="' + t.id + '"' + (assigned[t.id] ? ' checked' : '') + '> <span style="color:' + esc(t.color) + '">' + esc(t.name) + '</span></label>'; }).join('')
            : '<div class="text-muted small arabic-text px-2">لا توجد وسوم</div>') +
            '<div class="sec-pop-newtag"><input type="text" class="form-control form-control-sm" placeholder="وسم جديد"><button class="btn btn-sm btn-primary">+</button></div>';
        document.body.appendChild(pop); positionPop(pop, anchor);
        pop.querySelectorAll('input[type=checkbox]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var body = { patient_id: pid }; body[cb.checked ? 'add' : 'remove'] = [parseInt(cb.dataset.id, 10)];
                api('/api/secretary/patient-tags/assign', { method: 'POST', body: JSON.stringify(body) }).then(load);
            });
        });
        var nb = pop.querySelector('.sec-pop-newtag button'), ni = pop.querySelector('.sec-pop-newtag input');
        nb.addEventListener('click', function () {
            var name = (ni.value || '').trim(); if (!name) return;
            api('/api/secretary/patient-tags', { method: 'POST', body: JSON.stringify({ name: name }) }).then(function (r) {
                if (r && r.ok) api('/api/secretary/patient-tags/assign', { method: 'POST', body: JSON.stringify({ patient_id: pid, add: [r.data.id] }) }).then(function () { closePop(); load(); });
            });
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#secMarkBtn')) { openMarkerPicker(e.target.closest('#secMarkBtn')); return; }
        if (e.target.closest('#secTagBtn')) { openTagPicker(e.target.closest('#secTagBtn')); return; }
        if (!e.target.closest('.sec-popover')) closePop();
    });

    // ----- administrative files -------------------------------------------
    var filesBody = document.getElementById('secFilesBody');
    var fileInput = document.getElementById('secFileInput');
    var CAT = { id: 'هوية', insurance: 'تأمين', receipt: 'إيصال', other: 'أخرى' };
    function fileIcon(t) { t = t || ''; if (/pdf/.test(t)) return 'bi-file-earmark-pdf'; if (/image/.test(t)) return 'bi-file-earmark-image'; if (/word|document/.test(t)) return 'bi-file-earmark-word'; return 'bi-file-earmark-text'; }
    function pConfirm(msg) {
        return new Promise(function (resolve) {
            var w = document.createElement('div'); w.className = 'modal fade'; w.setAttribute('dir', 'rtl');
            w.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
                '<div class="modal-body arabic-text">' + esc(msg) + '</div><div class="modal-footer">' +
                '<button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button class="btn btn-danger pc-ok">حذف</button></div></div></div>';
            document.body.appendChild(w);
            var bs = (window.bootstrap && bootstrap.Modal) ? new bootstrap.Modal(w) : null, done = false;
            w.addEventListener('hidden.bs.modal', function () { if (!done) resolve(false); w.remove(); });
            w.querySelector('.pc-ok').addEventListener('click', function () { done = true; if (bs) bs.hide(); else { w.remove(); } resolve(true); });
            if (bs) bs.show(); else { w.style.display = 'block'; }
        });
    }
    function loadFiles() {
        if (!filesBody) return;
        api('/api/secretary/patient-files/' + pid).then(function (res) {
            var list = (res && res.files) || [];
            filesBody.innerHTML = list.length
                ? '<div class="sec-files-grid">' + list.map(function (f) {
                    return '<div class="sec-file-card">' +
                        '<a href="/api/secretary/patient-files/view/' + f.id + '" target="_blank" class="sec-file-link">' +
                          '<i class="bi ' + fileIcon(f.file_type) + '"></i><span class="sec-file-name">' + esc(f.original_filename) + '</span></a>' +
                        '<span class="sec-file-cat">' + esc(CAT[f.category] || f.category || '') + '</span>' +
                        '<button class="sec-file-del" data-id="' + f.id + '" title="حذف"><i class="bi bi-trash"></i></button>' +
                      '</div>';
                }).join('') + '</div>'
                : '<div class="text-muted arabic-text">لا توجد مستندات إدارية بعد</div>';
        });
    }
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var f = fileInput.files[0]; if (!f) return;
            var cat = (document.getElementById('secFileCategory') || {}).value || 'other';
            var fd = new FormData(); fd.append('file', f); fd.append('category', cat);
            filesBody.innerHTML = '<div class="text-muted arabic-text"><span class="spinner-border spinner-border-sm me-1"></span>جارٍ الرفع…</div>';
            fetch('/api/secretary/patient-files/' + pid, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); }).then(function (res) {
                    if (!(res && res.ok) && window.showNotification) window.showNotification((res && res.error) || 'تعذّر الرفع', 'danger');
                    fileInput.value = ''; loadFiles();
                }).catch(function () { fileInput.value = ''; loadFiles(); });
        });
    }
    document.addEventListener('click', function (e) {
        var d = e.target.closest('.sec-file-del'); if (!d) return;
        pConfirm('حذف هذا المستند الإداري؟').then(function (ok) {
            if (ok) api('/api/secretary/patient-files/' + d.dataset.id, { method: 'DELETE' }).then(loadFiles);
        });
    });

    load();
    loadFiles();
})();
