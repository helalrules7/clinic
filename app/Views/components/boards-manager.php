<?php
/**
 * Reusable Boards manager (card + modal + JS).
 * Included by doctor/settings.php and admin/settings.php.
 * Backend: GET/POST /api/board/boards, PUT/DELETE /api/board/boards/{id}
 * (doctor + admin permitted). Self-guards against double-include.
 */
?>
<div class="row mt-4" id="boardsManagerRoot">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i class="bi bi-columns-gap me-2"></i>
                    Patient Boards
                </h5>
                <button type="button" class="btn btn-sm btn-primary" id="bmAddBtn">
                    <i class="bi bi-plus-lg me-1"></i> Add board
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Boards are the workflow stages patients move through. Manage them here, or open the
                    <a href="/doctor/board">Patient Boards</a> page to work with patients inside each board.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="bmTable">
                        <thead>
                            <tr>
                                <th style="width:60px;">Color</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th style="width:90px;">Patients</th>
                                <th style="width:110px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bmTableBody">
                            <tr><td colspan="5" class="text-center text-muted py-3">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Board edit modal -->
<div class="modal fade" id="bmEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="bmEditForm" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-columns-gap me-1"></i> <span id="bmModalTitle">Add board</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bmEditId" value="">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Board name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bmEditName" maxlength="80" required placeholder="e.g. Post-op follow-up">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Description</label>
                        <textarea class="form-control" id="bmEditDesc" maxlength="255" rows="2" placeholder="Short description (optional)"></textarea>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small text-muted">Color</label>
                        <div id="bmColorRow" class="d-flex flex-wrap gap-2"></div>
                        <input type="hidden" id="bmEditColor" value="#0ea5e9">
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="bmEditError" role="alert"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" id="bmDeleteBtn" hidden><i class="bi bi-trash"></i> Delete</button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="bmSaveBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bm-color-dot { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; transition: transform .12s ease; }
    .bm-color-dot:hover { transform: scale(1.1); }
    .bm-color-dot.is-selected { border-color: var(--card, #fff); box-shadow: 0 0 0 2px var(--text, #0f172a); }
    #bmTable .bm-swatch { width: 18px; height: 18px; border-radius: 5px; display: inline-block; }
</style>

<script>
(function () {
    'use strict';
    if (window.__boardsManagerLoaded) return;
    window.__boardsManagerLoaded = true;

    const COLORS = ['#0ea5e9','#10b981','#f59e0b','#a855f7','#ef4444','#22c55e','#3b82f6','#ec4899','#64748b','#14b8a6'];
    const TBODY  = document.getElementById('bmTableBody');
    const MODAL  = document.getElementById('bmEditModal');
    const FORM   = document.getElementById('bmEditForm');
    const F_ID   = document.getElementById('bmEditId');
    const F_NAME = document.getElementById('bmEditName');
    const F_DESC = document.getElementById('bmEditDesc');
    const F_COLOR= document.getElementById('bmEditColor');
    const TITLE  = document.getElementById('bmModalTitle');
    const SAVE   = document.getElementById('bmSaveBtn');
    const DELBTN = document.getElementById('bmDeleteBtn');
    const ERR    = document.getElementById('bmEditError');
    const COLORROW = document.getElementById('bmColorRow');

    let bsModal = null, editing = null;
    function getModal(){ if(!bsModal && window.bootstrap) bsModal = new bootstrap.Modal(MODAL); return bsModal; }
    function esc(s){ return String(s ?? '').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    function rowHtml(b){
        return `
            <tr data-id="${b.id}">
                <td><span class="bm-swatch" style="background:${esc(b.color||'#0ea5e9')}"></span></td>
                <td>${esc(b.name)}${b.is_default ? ' <span class="badge bg-secondary ms-1">default</span>' : ''}</td>
                <td class="text-muted small">${esc(b.description||'')}</td>
                <td><span class="badge bg-light text-dark border">${b.patient_count||0}</span></td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-primary bm-edit" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger bm-del" title="Delete" ${b.is_default?'disabled':''}><i class="bi bi-trash"></i></button>
                </td>
            </tr>`;
    }

    let cache = [];
    async function load(){
        TBODY.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Loading…</td></tr>';
        try {
            const r = await fetch('/api/board/boards', { credentials:'same-origin' });
            const j = await r.json();
            if(!j.ok) throw new Error(j.error||'Failed to load');
            cache = j.data || [];
            TBODY.innerHTML = cache.length ? cache.map(rowHtml).join('')
                : '<tr><td colspan="5" class="text-center text-muted py-3">No boards yet — click <strong>Add board</strong>.</td></tr>';
        } catch(e){ TBODY.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">${esc(e.message)}</td></tr>`; }
    }

    function buildColors(sel){
        COLORROW.innerHTML = '';
        COLORS.forEach(c=>{
            const d = document.createElement('button');
            d.type='button'; d.className='bm-color-dot'+(c===sel?' is-selected':''); d.style.background=c;
            d.addEventListener('click', ()=>{ F_COLOR.value=c; COLORROW.querySelectorAll('.bm-color-dot').forEach(x=>x.classList.remove('is-selected')); d.classList.add('is-selected'); });
            COLORROW.appendChild(d);
        });
    }

    function openModal(b){
        editing = b || null;
        ERR.classList.add('d-none'); ERR.textContent='';
        TITLE.textContent = b ? 'Edit board' : 'Add board';
        F_ID.value = b ? b.id : '';
        F_NAME.value = b ? (b.name||'') : '';
        F_DESC.value = b ? (b.description||'') : '';
        const color = b ? (b.color||'#0ea5e9') : '#0ea5e9';
        F_COLOR.value = color; buildColors(color);
        DELBTN.hidden = !(b && !b.is_default);
        const m = getModal(); if(m) m.show();
    }

    async function save(ev){
        ev.preventDefault();
        ERR.classList.add('d-none'); SAVE.disabled = true;
        const id = F_ID.value;
        const payload = { name: F_NAME.value.trim(), description: F_DESC.value.trim(), color: F_COLOR.value||'#0ea5e9' };
        if(!payload.name){ ERR.textContent='Board name is required.'; ERR.classList.remove('d-none'); SAVE.disabled=false; return; }
        try {
            const r = await fetch(id?`/api/board/boards/${id}`:'/api/board/boards', {
                method: id?'PUT':'POST', credentials:'same-origin',
                headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)
            });
            const j = await r.json();
            if(!j.ok) throw new Error(j.error||'Save failed');
            const m=getModal(); if(m) m.hide();
            await load();
        } catch(e){ ERR.textContent=e.message; ERR.classList.remove('d-none'); }
        finally { SAVE.disabled=false; }
    }

    async function confirmDelete(message){
        if (typeof window.showConfirmModal === 'function') {
            return await window.showConfirmModal({
                title: 'Delete board',
                message: message,
                confirmText: 'Delete',
                confirmClass: 'btn-danger',
                icon: 'bi-trash'
            });
        }
        return window.confirm(message);
    }
    function notify(message){
        if (typeof window.showAlertModal === 'function') {
            window.showAlertModal({ title: 'Error', message: message, icon: 'bi-exclamation-octagon' });
        } else {
            window.alert(message);
        }
    }

    async function del(id){
        if(!await confirmDelete('Delete this board? Patients in it will move to the default board.')) return;
        try {
            const r = await fetch(`/api/board/boards/${id}`, { method:'DELETE', credentials:'same-origin' });
            const j = await r.json();
            if(!j.ok) throw new Error(j.error||'Delete failed');
            await load();
        } catch(e){ notify(e.message); }
    }

    document.getElementById('bmAddBtn').addEventListener('click', ()=>openModal(null));
    FORM.addEventListener('submit', save);
    DELBTN.addEventListener('click', ()=>{ if(editing){ const m=getModal(); if(m) m.hide(); del(editing.id); } });
    TBODY.addEventListener('click', (e)=>{
        const ed = e.target.closest('.bm-edit'); const dl = e.target.closest('.bm-del');
        if(!ed && !dl) return;
        const tr = e.target.closest('tr[data-id]'); const id = tr && parseInt(tr.dataset.id,10);
        if(!id) return;
        const b = cache.find(x=>x.id===id);
        if(ed) openModal(b); else if(dl && b && !b.is_default) del(id);
    });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', load);
    else load();
})();
</script>
