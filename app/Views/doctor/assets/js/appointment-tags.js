/**
 * Appointment page — patient tags modal, persistent appointment tags, session labels.
 */
(function () {
    'use strict';

    const cfg = window.__appointmentTagsConfig || {};
    const appointmentId = cfg.appointmentId;
    const patientId = cfg.patientId;

    if (!appointmentId) return;

    function esc(s) {
        if (typeof escapeHtml === 'function') return escapeHtml(s);
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    async function api(url, opts = {}) {
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', ...(opts.json ? { 'Content-Type': 'application/json' } : {}) },
            ...opts,
            body: opts.json ? JSON.stringify(opts.json) : opts.body
        });
        return res.json();
    }

    function badge(tag, removable, onRemove) {
        const el = document.createElement('span');
        el.className = 'badge appt-tag-chip me-1 mb-1';
        el.style.background = tag.color || '#6366f1';
        el.style.cursor = removable ? 'pointer' : 'default';
        const icon = tag.icon ? `<i class="bi ${esc(tag.icon)} me-1"></i>` : '';
        el.innerHTML = `${icon}${esc(tag.name)}${removable ? ' <i class="bi bi-x-lg ms-1" style="font-size:.65rem"></i>' : ''}`;
        if (removable && onRemove) el.addEventListener('click', onRemove);
        return el;
    }

    /* ── Session labels (header only) ─────────────────────────── */

    let sessionLabels = [];

    async function loadSessionLabels() {
        const data = await api(`/api/appointments/${appointmentId}/session-labels`);
        sessionLabels = data.ok ? (data.labels || []) : [];
        renderSessionLabels();
    }

    function renderSessionLabels() {
        const wrap = document.getElementById('apptSessionLabels');
        if (!wrap) return;
        wrap.innerHTML = '';
        sessionLabels.forEach((lbl, idx) => {
            const chip = document.createElement('span');
            chip.className = 'badge appt-session-label me-1 mb-1';
            chip.style.background = lbl.color || '#f59e0b';
            chip.style.cursor = 'pointer';
            chip.textContent = lbl.label_text;
            chip.title = 'Click to remove';
            chip.addEventListener('click', () => {
                sessionLabels.splice(idx, 1);
                saveSessionLabels();
            });
            wrap.appendChild(chip);
        });
        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'btn btn-sm btn-outline-light py-0 px-2';
        addBtn.style.fontSize = '.75rem';
        addBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Label';
        addBtn.addEventListener('click', addSessionLabel);
        wrap.appendChild(addBtn);
    }

    function addSessionLabel() {
        const text = prompt('Session label (shown in header only)');
        if (!text || !text.trim()) return;
        const colors = ['#f59e0b', '#ef4444', '#22c55e', '#3b82f6', '#8b5cf6'];
        sessionLabels.push({ label_text: text.trim(), color: colors[sessionLabels.length % colors.length] });
        saveSessionLabels();
    }

    async function saveSessionLabels() {
        await api(`/api/appointments/${appointmentId}/session-labels`, {
            method: 'PUT',
            json: { labels: sessionLabels }
        });
        await loadSessionLabels();
    }

    /* ── Appointment persistent tags ────────────────────────────── */

    async function loadApptTags() {
        const data = await api(`/api/appointments/${appointmentId}/tags`);
        const tags = data.ok ? data.tags : [];
        const wrap = document.getElementById('apptPersistentTags');
        if (!wrap) return;
        wrap.innerHTML = '';
        tags.forEach(tag => {
            wrap.appendChild(badge(tag, true, async () => {
                await api(`/api/appointments/${appointmentId}/tags/${tag.id}`, { method: 'DELETE' });
                loadApptTags();
            }));
        });
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-light py-0 px-2';
        btn.style.fontSize = '.75rem';
        btn.innerHTML = '<i class="bi bi-plus-lg"></i>';
        btn.title = 'Add appointment tag';
        btn.addEventListener('click', showAppointmentTagsModal);
        wrap.appendChild(btn);
    }

    async function showAppointmentTagsModal() {
        const [allRes, curRes] = await Promise.all([
            api('/api/appointment-tags'),
            api(`/api/appointments/${appointmentId}/tags`)
        ]);
        const all = allRes.ok ? allRes.tags : [];
        const cur = curRes.ok ? curRes.tags : [];
        const curIds = new Set(cur.map(t => t.id));

        let modal = document.getElementById('apptTagsModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'apptTagsModal';
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-tags me-2"></i>Appointment Tags</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body" id="apptTagsModalList"></div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button></div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
        }

        const list = modal.querySelector('#apptTagsModalList');
        list.innerHTML = all.length ? '' : '<p class="text-muted small mb-0">No tags defined. Create them under Tags and Templates.</p>';
        all.forEach(tag => {
            const row = document.createElement('label');
            row.className = 'd-flex align-items-center gap-2 mb-2 p-2 rounded border';
            row.style.cursor = 'pointer';
            const checked = curIds.has(tag.id);
            row.innerHTML = `<input type="checkbox" class="form-check-input" ${checked ? 'checked' : ''}>
                <span class="badge" style="background:${esc(tag.color)}">${esc(tag.name)}</span>`;
            row.querySelector('input').addEventListener('change', async (e) => {
                const url = `/api/appointments/${appointmentId}/tags/${tag.id}`;
                await api(url, { method: e.target.checked ? 'POST' : 'DELETE' });
                loadApptTags();
            });
            list.appendChild(row);
        });
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    /* ── Drug → patient tag suggest ───────────────────────────── */

    window.maybeSuggestPatientTagsFromDrug = async function (drugName, pid) {
        const pId = pid || patientId;
        if (!drugName || !pId) return;
        const data = await api(`/api/drug-tag-links/suggestions?drug_name=${encodeURIComponent(drugName)}`);
        if (!data.ok || !data.tags || !data.tags.length) return;

        const current = await api(`/api/patients/${pId}/tags`);
        const currentIds = new Set((current.ok ? current.tags : []).map(t => t.id));
        const toSuggest = data.tags.filter(t => !currentIds.has(t.id));
        if (!toSuggest.length) return;

        const names = toSuggest.map(t => t.name).join(', ');
        if (!confirm(`Suggest patient tag(s) for this prescription?\n\n${names}\n\nClick OK to assign.`)) return;

        for (const tag of toSuggest) {
            await api(`/api/patients/${pId}/tags/${tag.id}`, { method: 'POST' });
        }
        if (typeof showNotification === 'function') showNotification('Patient tag(s) added', 'success');
    };

    /* ── Patient tags modal (standalone — no patients.js) ─────── */

    async function showPatientTagsModal(pid) {
        const [tagsRes, patientRes] = await Promise.all([
            api('/api/patient-tags'),
            api(`/api/patients/${pid}/tags`)
        ]);
        const available = tagsRes.ok ? tagsRes.tags : [];
        const assigned = patientRes.ok ? patientRes.tags : [];
        const assignedIds = new Set(assigned.map(t => t.id));

        let modal = document.getElementById('apptPatientTagsModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'apptPatientTagsModal';
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Patient Tags</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="tags-inline-form mb-3" style="background:var(--card)">
                                <input type="text" class="form-control form-control-sm" id="apptNewTagName" placeholder="New tag name">
                                <input type="color" class="form-control form-control-color form-control-sm" id="apptNewTagColor" value="#6366f1">
                                <button type="button" class="btn btn-primary btn-sm" id="apptCreateTagBtn">Create</button>
                            </div>
                            <div id="apptPatientTagsList"></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button></div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
        }

        const list = modal.querySelector('#apptPatientTagsList');
        function renderList() {
            list.innerHTML = available.length ? '' : '<p class="text-muted small">No tags yet. Create one above.</p>';
            available.forEach(tag => {
                const row = document.createElement('label');
                row.className = 'd-flex align-items-center gap-2 mb-2 p-2 rounded border';
                row.style.cursor = 'pointer';
                const checked = assignedIds.has(tag.id);
                row.innerHTML = `<input type="checkbox" class="form-check-input" ${checked ? 'checked' : ''}>
                    <span class="badge" style="background:${esc(tag.color)}">${esc(tag.name)}</span>`;
                row.querySelector('input').addEventListener('change', async (e) => {
                    const url = `/api/patients/${pid}/tags/${tag.id}`;
                    await api(url, { method: e.target.checked ? 'POST' : 'DELETE' });
                    const fresh = await api(`/api/patients/${pid}/tags`);
                    if (fresh.ok) {
                        assignedIds.clear();
                        fresh.tags.forEach(t => assignedIds.add(t.id));
                    }
                });
                list.appendChild(row);
            });
        }
        renderList();

        modal.querySelector('#apptCreateTagBtn').onclick = async () => {
            const name = modal.querySelector('#apptNewTagName').value.trim();
            const color = modal.querySelector('#apptNewTagColor').value;
            if (!name) return;
            const r = await api('/api/patient-tags', { method: 'POST', json: { name, color, icon: 'bi-tag' } });
            if (r.ok && r.tag) {
                available.push(r.tag);
                modal.querySelector('#apptNewTagName').value = '';
                renderList();
            }
        };

        bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    window.showTagManagementModal = showPatientTagsModal;

    /* ── Init ─────────────────────────────────────────────────── */

    function bindPatientTagsBtn() {
        const btn = document.getElementById('apptManagePatientTagsBtn');
        if (btn && patientId) {
            btn.addEventListener('click', () => showPatientTagsModal(patientId));
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSessionLabels();
        loadApptTags();
        bindPatientTagsBtn();
    });

    window.appointmentTags = { loadApptTags, loadSessionLabels, showAppointmentTagsModal };
})();
