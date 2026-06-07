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

    load();
})();
