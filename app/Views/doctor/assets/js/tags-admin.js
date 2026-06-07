/**
 * Tags & Templates admin — patient tags, appointment tags, drug links, reports.
 */
(function () {
    'use strict';

    const ICON_OPTIONS = [
        { value: 'bi-tag', label: 'Tag' },
        { value: 'bi-star-fill', label: 'Star' },
        { value: 'bi-exclamation-triangle-fill', label: 'Alert' },
        { value: 'bi-heart-pulse-fill', label: 'Medical' },
        { value: 'bi-clock-history', label: 'Follow-up' },
        { value: 'bi-person-plus-fill', label: 'New patient' }
    ];

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

    function notify(msg, type) {
        if (typeof showNotification === 'function') showNotification(msg, type);
        else if (typeof showSuccessMessage === 'function' && type === 'success') showSuccessMessage(msg);
        else if (type === 'error' && typeof showErrorMessage === 'function') showErrorMessage(msg);
    }

    function initTooltips(root) {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            bootstrap.Tooltip.getOrCreateInstance(el);
        });
    }

    /* ── Modals ───────────────────────────────────────────────── */

    function showConfirmModal({ title, bodyHtml, confirmLabel = 'Confirm', confirmClass = 'btn-primary', onConfirm }) {
        const id = 'tagsConfirmModal';
        document.getElementById(id)?.remove();
        document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade mi-theme-modal" id="${id}" tabindex="-1" style="z-index:10650">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${esc(title)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start mi-confirm-body">${bodyHtml}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn ${confirmClass}" id="tagsConfirmOk">${esc(confirmLabel)}</button>
                        </div>
                    </div>
                </div>
            </div>`);
        const el = document.getElementById(id);
        const modal = new bootstrap.Modal(el);
        modal.show();
        document.getElementById('tagsConfirmOk').addEventListener('click', () => {
            modal.hide();
            if (typeof onConfirm === 'function') onConfirm();
        });
        el.addEventListener('hidden.bs.modal', () => el.remove(), { once: true });
    }

    function openEditTagModal({ kind, tag, onSaved }) {
        const isPatient = kind === 'patient';
        const apiBase = isPatient ? '/api/patient-tags' : '/api/appointment-tags';
        const id = 'tagsEditModal';
        document.getElementById(id)?.remove();

        const iconOptions = ICON_OPTIONS.map(o =>
            `<option value="${o.value}" ${tag.icon === o.value ? 'selected' : ''}>${o.label}</option>`
        ).join('');

        document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade mi-theme-modal" id="${id}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit ${isPatient ? 'patient' : 'appointment'} tag</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tagsEditName" maxlength="50" value="${esc(tag.name)}">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color w-100" id="tagsEditColor" value="${esc(tag.color || '#6366f1')}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Icon</label>
                                    <select class="form-select" id="tagsEditIcon">${iconOptions}</select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-muted small me-2">Scope:</span>
                                ${scopeBadge(tag.doctor_id)}
                            </div>
                            <div class="mt-3 p-2 rounded border d-flex align-items-center gap-2">
                                <span class="text-muted small">Preview:</span>
                                <span id="tagsEditPreview">${tagBadge({ ...tag })}</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="tagsEditSaveBtn">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>`);

        const el = document.getElementById(id);
        const modal = new bootstrap.Modal(el);
        const nameEl = el.querySelector('#tagsEditName');
        const colorEl = el.querySelector('#tagsEditColor');
        const iconEl = el.querySelector('#tagsEditIcon');
        const previewEl = el.querySelector('#tagsEditPreview');

        function updatePreview() {
            previewEl.innerHTML = tagBadge({
                name: nameEl.value.trim() || tag.name,
                color: colorEl.value,
                icon: iconEl.value
            });
        }
        nameEl.addEventListener('input', updatePreview);
        colorEl.addEventListener('input', updatePreview);
        iconEl.addEventListener('change', updatePreview);

        el.querySelector('#tagsEditSaveBtn').addEventListener('click', async () => {
            const name = nameEl.value.trim();
            if (!name) {
                notify('Tag name is required', 'error');
                return;
            }
            const payload = {
                name,
                color: colorEl.value,
                icon: iconEl.value
            };
            const data = await api(`${apiBase}/${tag.id}`, { method: 'PUT', json: payload });
            if (data.ok) {
                notify('Tag updated', 'success');
                modal.hide();
                if (typeof onSaved === 'function') onSaved();
            } else {
                notify(data.error || 'Failed to update', 'error');
            }
        });

        modal.show();
        el.addEventListener('hidden.bs.modal', () => el.remove(), { once: true });
    }

    /* ── Helpers ──────────────────────────────────────────────── */

    function scopeBadge(doctorId) {
        const isGlobal = doctorId === null || doctorId === undefined || doctorId === '';
        return `<span class="tag-scope-badge ${isGlobal ? '' : 'private'}">${isGlobal ? 'Global' : 'Private'}</span>`;
    }

    function tagBadge(tag) {
        const icon = tag.icon ? `<i class="bi ${esc(tag.icon)} me-1"></i>` : '';
        return `<span class="badge" style="background:${esc(tag.color || '#6366f1')}">${icon}${esc(tag.name)}</span>`;
    }

    function rowActions(editCls, delCls, id) {
        return `<div class="btn-group btn-group-sm flex-shrink-0" role="group">
            <button type="button" class="btn btn-outline-primary ${editCls}" data-id="${id}"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button type="button" class="btn btn-outline-danger ${delCls}" data-id="${id}"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    }

    function deleteAction(delCls, id) {
        return `<div class="btn-group btn-group-sm flex-shrink-0" role="group">
            <button type="button" class="btn btn-outline-danger ${delCls}" data-id="${id}"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Remove link">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    }

    function confirmDeleteTag(tag, kind, onDeleted) {
        const label = kind === 'patient' ? 'patient tag' : 'appointment tag';
        showConfirmModal({
            title: 'Delete tag',
            bodyHtml: `
                <p class="mb-2">Delete the ${label} <strong>${esc(tag.name)}</strong>?</p>
                <p class="mi-confirm-note mb-0">This cannot be undone. ${kind === 'patient' ? 'All patient assignments for this tag will be removed.' : 'All appointment assignments will be removed.'}</p>`,
            confirmLabel: 'Delete',
            confirmClass: 'btn-danger',
            onConfirm: async () => {
                const apiBase = kind === 'patient' ? '/api/patient-tags' : '/api/appointment-tags';
                const data = await api(`${apiBase}/${tag.id}`, { method: 'DELETE' });
                if (data.ok) {
                    notify('Tag deleted', 'success');
                    if (typeof onDeleted === 'function') onDeleted();
                } else {
                    notify(data.error || 'Failed to delete', 'error');
                }
            }
        });
    }

    /* ── Patient Tags ─────────────────────────────────────────── */

    async function loadPatientTagsSection(root) {
        const [tagsRes, reportsRes] = await Promise.all([
            api('/api/patient-tags'),
            api('/api/patient-tags/reports')
        ]);
        const tags = tagsRes.ok ? tagsRes.tags : [];
        const reports = reportsRes.ok ? reportsRes.reports : [];

        root.innerHTML = `
            <div class="tags-inline-form" id="ptNewTagForm">
                <div>
                    <label class="form-label">New patient tag</label>
                    <input type="text" class="form-control form-control-sm" id="ptNewName" placeholder="Tag name" maxlength="50">
                </div>
                <div>
                    <label class="form-label">Color</label>
                    <input type="color" class="form-control form-control-color form-control-sm" id="ptNewColor" value="#6366f1">
                </div>
                <div>
                    <label class="form-label">Scope</label>
                    <select class="form-select form-select-sm" id="ptNewScope">
                        <option value="private">Private</option>
                        <option value="global">Global</option>
                    </select>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="ptCreateBtn"><i class="bi bi-plus-lg me-1"></i>Create</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm tags-admin-table mb-0">
                    <thead><tr><th>Tag</th><th>Scope</th><th>Patients</th><th></th></tr></thead>
                    <tbody id="ptTagsTbody"></tbody>
                </table>
            </div>
            <div id="ptDrillPanel" class="tags-drill-panel d-none"></div>
        `;

        const tbody = root.querySelector('#ptTagsTbody');
        const reportMap = {};
        reports.forEach(r => { reportMap[r.id] = r.patient_count; });

        tags.forEach(tag => {
            const tr = document.createElement('tr');
            tr.className = 'tags-report-row';
            tr.innerHTML = `
                <td>${tagBadge(tag)}</td>
                <td>${scopeBadge(tag.doctor_id)}</td>
                <td><strong>${reportMap[tag.id] ?? 0}</strong></td>
                <td class="text-end text-nowrap">${rowActions('pt-edit', 'pt-del', tag.id)}</td>
            `;
            tr.addEventListener('click', (e) => {
                if (e.target.closest('.btn-group')) return;
                showPatientDrill(tag, root);
            });
            tbody.appendChild(tr);
        });

        root.querySelector('#ptCreateBtn').addEventListener('click', async () => {
            const name = root.querySelector('#ptNewName').value.trim();
            const color = root.querySelector('#ptNewColor').value;
            const scope = root.querySelector('#ptNewScope').value;
            if (!name) return notify('Name required', 'error');
            const payload = { name, color, icon: 'bi-tag' };
            if (scope === 'global') payload.doctor_id = null;
            const data = await api('/api/patient-tags', { method: 'POST', json: payload });
            if (data.ok) {
                notify('Tag created', 'success');
                loadPatientTagsSection(root);
            } else notify(data.error || 'Failed', 'error');
        });

        root.querySelectorAll('.pt-edit').forEach(btn => btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (!tag) return;
            openEditTagModal({
                kind: 'patient',
                tag,
                onSaved: () => loadPatientTagsSection(root)
            });
        }));
        root.querySelectorAll('.pt-del').forEach(btn => btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (!tag) return;
            confirmDeleteTag(tag, 'patient', () => loadPatientTagsSection(root));
        }));

        initTooltips(root);
    }

    async function showPatientDrill(tag, root) {
        const panel = root.querySelector('#ptDrillPanel');
        panel.classList.remove('d-none');
        panel.innerHTML = '<p class="text-muted small mb-0">Loading…</p>';
        const data = await api(`/api/patient-tags/${tag.id}/patients`);
        if (!data.ok) { panel.innerHTML = '<p class="text-danger small">Failed to load</p>'; return; }
        if (!data.patients.length) {
            panel.innerHTML = `<p class="small mb-0"><strong>${esc(tag.name)}</strong> — no patients assigned.</p>`;
            return;
        }
        panel.innerHTML = `
            <p class="small fw-bold mb-2">${esc(tag.name)} — ${data.patients.length} patient(s)</p>
            <ul class="list-unstyled mb-0 small">
                ${data.patients.map(p => `
                    <li class="mb-1">
                        <a href="/doctor/patients/${p.id}">${esc(p.first_name)} ${esc(p.last_name)}</a>
                        <span class="text-muted">#${p.id}</span>
                    </li>
                `).join('')}
            </ul>
        `;
    }

    /* ── Appointment Tags ───────────────────────────────────────── */

    async function loadAppointmentTagsSection(root) {
        const data = await api('/api/appointment-tags');
        const tags = data.ok ? data.tags : [];

        root.innerHTML = `
            <div class="tags-inline-form">
                <div><label class="form-label">New appointment tag</label>
                    <input type="text" class="form-control form-control-sm" id="atNewName" maxlength="50"></div>
                <div><label class="form-label">Color</label>
                    <input type="color" class="form-control form-control-color form-control-sm" id="atNewColor" value="#6366f1"></div>
                <div><label class="form-label">Scope</label>
                    <select class="form-select form-select-sm" id="atNewScope"><option value="private">Private</option><option value="global">Global</option></select></div>
                <button type="button" class="btn btn-primary btn-sm" id="atCreateBtn"><i class="bi bi-plus-lg me-1"></i>Create</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm tags-admin-table mb-0">
                    <thead><tr><th>Tag</th><th>Scope</th><th></th></tr></thead>
                    <tbody id="atTagsTbody"></tbody>
                </table>
            </div>
        `;

        const tbody = root.querySelector('#atTagsTbody');
        if (!tags.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-muted">No appointment tags yet</td></tr>';
        }
        tags.forEach(tag => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${tagBadge(tag)}</td>
                <td>${scopeBadge(tag.doctor_id)}</td>
                <td class="text-end text-nowrap">${rowActions('at-edit', 'at-del', tag.id)}</td>`;
            tbody.appendChild(tr);
        });

        root.querySelector('#atCreateBtn').addEventListener('click', async () => {
            const name = root.querySelector('#atNewName').value.trim();
            const color = root.querySelector('#atNewColor').value;
            const scope = root.querySelector('#atNewScope').value;
            if (!name) return notify('Name required', 'error');
            const payload = { name, color, icon: 'bi-tag' };
            if (scope === 'global') payload.doctor_id = null;
            const res = await api('/api/appointment-tags', { method: 'POST', json: payload });
            if (res.ok) { notify('Created', 'success'); loadAppointmentTagsSection(root); }
            else notify(res.error || 'Failed', 'error');
        });

        root.querySelectorAll('.at-edit').forEach(btn => btn.addEventListener('click', () => {
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (!tag) return;
            openEditTagModal({
                kind: 'appointment',
                tag,
                onSaved: () => loadAppointmentTagsSection(root)
            });
        }));
        root.querySelectorAll('.at-del').forEach(btn => btn.addEventListener('click', () => {
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (!tag) return;
            confirmDeleteTag(tag, 'appointment', () => loadAppointmentTagsSection(root));
        }));

        initTooltips(root);
    }

    /* ── Drug Tag Links ───────────────────────────────────────── */

    async function loadDrugLinksSection(root) {
        const [linksRes, tagsRes] = await Promise.all([
            api('/api/drug-tag-links'),
            api('/api/patient-tags')
        ]);
        const links = linksRes.ok ? linksRes.links : [];
        const patientTags = tagsRes.ok ? tagsRes.tags : [];

        root.innerHTML = `
            <p class="text-muted small">When a drug is prescribed, linked patient tags are suggested (with your approval).</p>
            <div class="tags-inline-form">
                <div class="flex-grow-1"><label class="form-label">Drug name</label>
                    <input type="text" class="form-control form-control-sm" id="dtDrugName" placeholder="Exact drug name"></div>
                <div><label class="form-label">Patient tag</label>
                    <select class="form-select form-select-sm" id="dtPatientTag">
                        <option value="">— Select —</option>
                        ${patientTags.map(t => `<option value="${t.id}">${esc(t.name)}</option>`).join('')}
                    </select></div>
                <div><label class="form-label">Scope</label>
                    <select class="form-select form-select-sm" id="dtScope"><option value="private">Private</option><option value="global">Global</option></select></div>
                <button type="button" class="btn btn-primary btn-sm" id="dtLinkBtn">Link</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm tags-admin-table mb-0">
                    <thead><tr><th>Drug</th><th>Patient tag</th><th>Scope</th><th></th></tr></thead>
                    <tbody id="dtLinksTbody"></tbody>
                </table>
            </div>
        `;

        const tbody = root.querySelector('#dtLinksTbody');
        links.forEach(link => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${esc(link.drug_name)}</td>
                <td><span class="badge" style="background:${esc(link.tag_color)}">${esc(link.tag_name)}</span></td>
                <td>${scopeBadge(link.doctor_id)}</td>
                <td class="text-end text-nowrap">${deleteAction('dt-del', link.id)}</td>
            `;
            tbody.appendChild(tr);
        });
        if (!links.length) tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No drug links yet</td></tr>';

        root.querySelector('#dtLinkBtn').addEventListener('click', async () => {
            const drug_name = root.querySelector('#dtDrugName').value.trim();
            const patient_tag_id = parseInt(root.querySelector('#dtPatientTag').value, 10);
            const scope = root.querySelector('#dtScope').value;
            if (!drug_name || !patient_tag_id) return notify('Drug and tag required', 'error');
            const payload = { drug_name, patient_tag_id };
            if (scope === 'global') payload.doctor_id = null;
            const r = await api('/api/drug-tag-links', { method: 'POST', json: payload });
            if (r.ok) { notify('Linked', 'success'); loadDrugLinksSection(root); }
            else notify(r.error || 'Failed', 'error');
        });
        root.querySelectorAll('.dt-del').forEach(btn => btn.addEventListener('click', () => {
            const link = links.find(l => String(l.id) === btn.dataset.id);
            if (!link) return;
            showConfirmModal({
                title: 'Remove drug link',
                bodyHtml: `
                    <p class="mb-2">Remove the link between <strong>${esc(link.drug_name)}</strong> and tag <strong>${esc(link.tag_name)}</strong>?</p>
                    <p class="mi-confirm-note mb-0">Prescriptions will no longer suggest this tag for this drug.</p>`,
                confirmLabel: 'Remove',
                confirmClass: 'btn-danger',
                onConfirm: async () => {
                    await api(`/api/drug-tag-links/${link.id}`, { method: 'DELETE' });
                    notify('Link removed', 'success');
                    loadDrugLinksSection(root);
                }
            });
        }));

        initTooltips(root);
    }

    function mount() {
        const ptRoot = document.getElementById('patientTagsAdminBody');
        const atRoot = document.getElementById('appointmentTagsAdminBody');
        const dtRoot = document.getElementById('drugTagsAdminBody');
        if (ptRoot) loadPatientTagsSection(ptRoot);
        if (atRoot) loadAppointmentTagsSection(atRoot);
        if (dtRoot) loadDrugLinksSection(dtRoot);
    }

    window.tagsAdmin = { mount };
    document.addEventListener('DOMContentLoaded', mount);
})();
