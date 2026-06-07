/**
 * Tags & Templates admin — analytics, patient/appointment tags, drug links.
 * Scope: Public (shared) or Private (per doctor).
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

    const analyticsState = {
        type: 'all',
        scope: 'all',
        q: '',
        from: '',
        to: '',
        context: 'all',
        selectedTag: null,
        highlight: null
    };

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

    function scopeBadge(doctorId) {
        const isPublic = doctorId === null || doctorId === undefined || doctorId === '';
        return `<span class="tag-scope-badge ${isPublic ? 'public' : 'private'}">${isPublic ? 'Public' : 'Private'}</span>`;
    }

    function scopePayload(scope, extra = {}) {
        const payload = { ...extra };
        if (scope === 'public') payload.doctor_id = null;
        return payload;
    }

    function scopeSelectHtml(id, defaultVal = 'private') {
        return `<select class="form-select form-select-sm" id="${id}">
            <option value="private" ${defaultVal === 'private' ? 'selected' : ''}>Private</option>
            <option value="public" ${defaultVal === 'public' ? 'selected' : ''}>Public</option>
        </select>`;
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

    function formatDateTime(val) {
        if (!val) return '—';
        try {
            const d = new Date(String(val).replace(' ', 'T'));
            if (isNaN(d.getTime())) return esc(val);
            return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
        } catch (e) {
            return esc(val);
        }
    }

    function tagBadge(tag) {
        const icon = tag.icon ? `<i class="bi ${esc(tag.icon)} me-1"></i>` : '';
        return `<span class="badge" style="background:${esc(tag.color || '#6366f1')}">${icon}${esc(tag.name)}</span>`;
    }

    function typeBadge(tagType) {
        const map = {
            patient: ['Patient', 'bg-primary'],
            appointment: ['Appointment', 'bg-info text-dark']
        };
        const [label, cls] = map[tagType] || ['Tag', 'bg-secondary'];
        return `<span class="badge ${cls}">${label}</span>`;
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

    /* ── Drug autocomplete (theme-aware) ────────────────────────── */

    function setupDrugAutocomplete(inputEl, onSelect) {
        if (!inputEl || inputEl.dataset.drugAcBound) return;
        inputEl.dataset.drugAcBound = '1';

        const wrap = document.createElement('div');
        wrap.className = 'tags-drug-ac-wrap position-relative flex-grow-1';
        inputEl.parentNode.insertBefore(wrap, inputEl);
        wrap.appendChild(inputEl);

        const dropdown = document.createElement('div');
        dropdown.className = 'drug-suggest-dropdown position-absolute w-100 shadow-sm';
        dropdown.style.cssText = 'display:none;z-index:1050;max-height:220px;overflow-y:auto;top:100%;left:0';
        wrap.appendChild(dropdown);

        let timer = null;

        async function search(term) {
            if (term.length < 2) {
                dropdown.style.display = 'none';
                return;
            }
            try {
                const data = await api(`/api/searchDrugsAutocomplete?q=${encodeURIComponent(term)}&limit=8`);
                dropdown.innerHTML = '';
                if (data.drugs && data.drugs.length) {
                    data.drugs.forEach(drug => {
                        const item = document.createElement('div');
                        item.className = 'p-2 suggestion-item';
                        item.style.cursor = 'pointer';
                        const price = drug.price ? `<span class="badge bg-success ms-2" style="font-size:.65rem">EGP ${esc(drug.price)}</span>` : '';
                        item.innerHTML = `
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold" style="font-size:.85rem">${esc(drug.drug_name)}</div>
                                    <small class="text-muted" style="font-size:.72rem">${esc(drug.active_ingredient || '')}${drug.Company ? ' — ' + esc(drug.Company) : ''}</small>
                                </div>${price}
                            </div>`;
                        item.addEventListener('click', () => {
                            inputEl.value = drug.drug_name;
                            dropdown.style.display = 'none';
                            if (typeof onSelect === 'function') onSelect(drug.drug_name);
                        });
                        dropdown.appendChild(item);
                    });
                    dropdown.style.display = 'block';
                } else {
                    dropdown.innerHTML = '<div class="p-2 text-muted text-center small">No drugs found</div>';
                    dropdown.style.display = 'block';
                }
            } catch (e) {
                dropdown.innerHTML = '<div class="p-2 text-danger text-center small">Search failed</div>';
                dropdown.style.display = 'block';
            }
        }

        inputEl.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => search(inputEl.value.trim()), 280);
        });
        inputEl.addEventListener('focus', () => {
            if (inputEl.value.trim().length >= 2) search(inputEl.value.trim());
        });
        document.addEventListener('click', (e) => {
            if (!wrap.contains(e.target)) dropdown.style.display = 'none';
        });
        inputEl.setAttribute('autocomplete', 'off');
        inputEl.setAttribute('placeholder', 'Search drugs database…');
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
                                <span id="tagsEditPreview">${tagBadge(tag)}</span>
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
            if (!name) return notify('Tag name is required', 'error');
            const data = await api(`${apiBase}/${tag.id}`, {
                method: 'PUT',
                json: { name, color: colorEl.value, icon: iconEl.value }
            });
            if (data.ok) {
                notify('Tag updated', 'success');
                modal.hide();
                if (typeof onSaved === 'function') onSaved();
            } else notify(data.error || 'Failed', 'error');
        });

        modal.show();
        el.addEventListener('hidden.bs.modal', () => el.remove(), { once: true });
    }

    function confirmDeleteTag(tag, kind, onDeleted) {
        showConfirmModal({
            title: 'Delete tag',
            bodyHtml: `<p class="mb-2">Delete <strong>${esc(tag.name)}</strong>?</p>
                <p class="mi-confirm-note mb-0">This cannot be undone. All assignments will be removed.</p>`,
            confirmLabel: 'Delete',
            confirmClass: 'btn-danger',
            onConfirm: async () => {
                const apiBase = kind === 'patient' ? '/api/patient-tags' : '/api/appointment-tags';
                const data = await api(`${apiBase}/${tag.id}`, { method: 'DELETE' });
                if (data.ok) {
                    notify('Tag deleted', 'success');
                    if (typeof onDeleted === 'function') onDeleted();
                } else notify(data.error || 'Failed', 'error');
            }
        });
    }

    /* ── Tag Analytics ──────────────────────────────────────────── */

    function analyticsQuery() {
        const p = new URLSearchParams();
        p.set('type', analyticsState.type);
        p.set('scope', analyticsState.scope);
        if (analyticsState.q) p.set('q', analyticsState.q);
        if (analyticsState.from) p.set('from', analyticsState.from);
        if (analyticsState.to) p.set('to', analyticsState.to);
        return p.toString();
    }

    async function loadUsageEvents(tag) {
        const panel = document.getElementById('tagUsageDetailPanel');
        if (!panel) return;
        panel.classList.remove('d-none');
        panel.innerHTML = '<p class="text-muted small mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Loading usage…</p>';

        const ctx = analyticsState.context;
        const dateQ = [];
        if (analyticsState.from) dateQ.push(`from=${encodeURIComponent(analyticsState.from)}`);
        if (analyticsState.to) dateQ.push(`to=${encodeURIComponent(analyticsState.to)}`);
        const dateStr = dateQ.length ? '&' + dateQ.join('&') : '';

        const base = tag.tag_type === 'patient'
            ? `/api/patient-tags/${tag.id}/usage-events?context=${ctx}${dateStr}`
            : `/api/appointment-tags/${tag.id}/usage-events?${dateQ.join('&').replace(/^/, '')}`;

        const url = tag.tag_type === 'appointment'
            ? `/api/appointment-tags/${tag.id}/usage-events${dateStr ? '?' + dateQ.join('&') : ''}`
            : `/api/patient-tags/${tag.id}/usage-events?context=${ctx}${dateStr}`;

        const data = await api(url);
        if (!data.ok) {
            panel.innerHTML = '<p class="text-danger small">Failed to load usage events</p>';
            return;
        }

        const events = data.events || [];
        const contextChips = tag.tag_type === 'patient'
            ? [['all', 'All'], ['patient', 'Patient records'], ['drug', 'Drug links']]
            : [['all', 'All']];

        panel.innerHTML = `
            <div class="tags-usage-detail-head d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    ${tagBadge(tag)}
                    ${typeBadge(tag.tag_type)}
                    ${scopeBadge(tag.doctor_id)}
                    <span class="text-muted small">Created ${formatDateTime(tag.created_at)}</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="tagUsageCloseBtn"><i class="bi bi-x-lg"></i></button>
            </div>
            ${tag.tag_type === 'patient' ? `
            <div class="tags-context-chips mb-3" id="tagContextChips">
                ${contextChips.map(([v, l]) => `
                    <button type="button" class="btn btn-sm ${analyticsState.context === v ? 'btn-primary' : 'btn-outline-secondary'} tag-ctx-chip" data-ctx="${v}">${l}</button>
                `).join('')}
            </div>` : ''}
            <div class="tags-usage-timeline">
                ${events.length ? events.map(ev => `
                    <div class="tags-usage-event">
                        <div class="tags-usage-event-meta">
                            <span class="badge tags-usage-ctx-badge tags-usage-ctx-${ev.context}">${esc(ev.context_label)}</span>
                            <span class="text-muted small">${formatDateTime(ev.occurred_at)}</span>
                        </div>
                        <div class="tags-usage-event-body">
                            ${ev.url
                                ? `<a href="${esc(ev.url)}" class="fw-semibold">${esc(ev.entity_label)}</a>`
                                : `<span class="fw-semibold">${esc(ev.entity_label)}</span>`}
                            ${ev.appointment_date ? `<span class="text-muted small ms-2">${esc(ev.appointment_date)}</span>` : ''}
                        </div>
                    </div>
                `).join('') : '<p class="text-muted small mb-0">No usage events in this filter range.</p>'}
            </div>`;

        panel.querySelector('#tagUsageCloseBtn')?.addEventListener('click', () => {
            analyticsState.selectedTag = null;
            panel.classList.add('d-none');
            document.querySelectorAll('.tags-analytics-row').forEach(r => r.classList.remove('active'));
        });
        panel.querySelectorAll('.tag-ctx-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                analyticsState.context = chip.dataset.ctx;
                loadUsageEvents(tag);
            });
        });
    }

    async function loadTagAnalyticsSection(root) {
        const data = await api(`/api/tags/analytics?${analyticsQuery()}`);
        const summary = data.ok ? data.summary : {};
        let tags = data.ok ? (data.tags || []) : [];

        if (analyticsState.highlight === 'patient') {
            tags = tags.filter(t => t.patient_count > 0);
        } else if (analyticsState.highlight === 'appointment') {
            tags = tags.filter(t => t.appointment_count > 0);
        } else if (analyticsState.highlight === 'drug_links') {
            tags = tags.filter(t => t.drug_link_count > 0);
        }

        root.innerHTML = `
            <div class="tags-analytics-filters mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Search tag</label>
                        <input type="text" class="form-control form-control-sm" id="taSearch" value="${esc(analyticsState.q)}" placeholder="Tag name…">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Type</label>
                        <select class="form-select form-select-sm" id="taType">
                            <option value="all" ${analyticsState.type === 'all' ? 'selected' : ''}>All types</option>
                            <option value="patient" ${analyticsState.type === 'patient' ? 'selected' : ''}>Patient</option>
                            <option value="appointment" ${analyticsState.type === 'appointment' ? 'selected' : ''}>Appointment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Scope</label>
                        <select class="form-select form-select-sm" id="taScope">
                            <option value="all" ${analyticsState.scope === 'all' ? 'selected' : ''}>All scopes</option>
                            <option value="public" ${analyticsState.scope === 'public' ? 'selected' : ''}>Public</option>
                            <option value="private" ${analyticsState.scope === 'private' ? 'selected' : ''}>Private</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">From</label>
                        <input type="date" class="form-control form-control-sm" id="taFrom" value="${esc(analyticsState.from)}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">To</label>
                        <input type="date" class="form-control form-control-sm" id="taTo" value="${esc(analyticsState.to)}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm flex-grow-1" id="taApplyBtn"><i class="bi bi-funnel me-1"></i>Apply</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="taResetBtn">Reset</button>
                    </div>
                </div>
            </div>

            <div class="tags-stat-cards row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <button type="button" class="tags-stat-card w-100 ${!analyticsState.highlight ? 'active' : ''}" data-highlight="">
                        <span class="tags-stat-val">${summary.total_tags ?? 0}</span>
                        <span class="tags-stat-lbl">Total tags</span>
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button type="button" class="tags-stat-card w-100 ${analyticsState.highlight === 'patient' ? 'active' : ''}" data-highlight="patient">
                        <span class="tags-stat-val">${summary.patient_assignments ?? 0}</span>
                        <span class="tags-stat-lbl">Patient uses</span>
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button type="button" class="tags-stat-card w-100 ${analyticsState.highlight === 'appointment' ? 'active' : ''}" data-highlight="appointment">
                        <span class="tags-stat-val">${summary.appointment_assignments ?? 0}</span>
                        <span class="tags-stat-lbl">Appointment uses</span>
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button type="button" class="tags-stat-card w-100 ${analyticsState.highlight === 'drug_links' ? 'active' : ''}" data-highlight="drug_links">
                        <span class="tags-stat-val">${summary.drug_links ?? 0}</span>
                        <span class="tags-stat-lbl">Drug links</span>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm tags-admin-table tags-analytics-table mb-0">
                    <thead>
                        <tr>
                            <th>Tag</th><th>Type</th><th>Scope</th><th>Created</th>
                            <th>Patients</th><th>Appts</th><th>Drugs</th>
                            <th>First used</th><th>Last used</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="taTbody"></tbody>
                </table>
            </div>
            <div id="tagUsageDetailPanel" class="tags-usage-detail-panel d-none"></div>
        `;

        const tbody = root.querySelector('#taTbody');
        if (!tags.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-muted">No tags match filters</td></tr>';
        } else {
            tags.forEach(tag => {
                const tr = document.createElement('tr');
                tr.className = 'tags-analytics-row';
                if (analyticsState.selectedTag && analyticsState.selectedTag.id === tag.id && analyticsState.selectedTag.tag_type === tag.tag_type) {
                    tr.classList.add('active');
                }
                tr.innerHTML = `
                    <td>${tagBadge(tag)}</td>
                    <td>${typeBadge(tag.tag_type)}</td>
                    <td>${scopeBadge(tag.doctor_id)}</td>
                    <td class="small text-muted">${formatDateTime(tag.created_at)}</td>
                    <td><strong>${tag.patient_count ?? 0}</strong></td>
                    <td><strong>${tag.appointment_count ?? 0}</strong></td>
                    <td><strong>${tag.drug_link_count ?? 0}</strong></td>
                    <td class="small">${formatDateTime(tag.first_used_at)}</td>
                    <td class="small">${formatDateTime(tag.last_used_at)}</td>
                    <td><span class="badge bg-dark">${tag.usage_count ?? 0}</span></td>`;
                tr.addEventListener('click', () => {
                    document.querySelectorAll('.tags-analytics-row').forEach(r => r.classList.remove('active'));
                    tr.classList.add('active');
                    analyticsState.selectedTag = tag;
                    analyticsState.context = 'all';
                    loadUsageEvents(tag);
                });
                tbody.appendChild(tr);
            });
        }

        function applyFilters() {
            analyticsState.q = root.querySelector('#taSearch').value.trim();
            analyticsState.type = root.querySelector('#taType').value;
            analyticsState.scope = root.querySelector('#taScope').value;
            analyticsState.from = root.querySelector('#taFrom').value;
            analyticsState.to = root.querySelector('#taTo').value;
            analyticsState.selectedTag = null;
            loadTagAnalyticsSection(root);
        }

        root.querySelector('#taApplyBtn').addEventListener('click', applyFilters);
        root.querySelector('#taSearch').addEventListener('keydown', e => { if (e.key === 'Enter') applyFilters(); });
        root.querySelector('#taResetBtn').addEventListener('click', () => {
            analyticsState.q = '';
            analyticsState.type = 'all';
            analyticsState.scope = 'all';
            analyticsState.from = '';
            analyticsState.to = '';
            analyticsState.highlight = null;
            analyticsState.selectedTag = null;
            analyticsState.context = 'all';
            loadTagAnalyticsSection(root);
        });

        root.querySelectorAll('.tags-stat-card').forEach(card => {
            card.addEventListener('click', () => {
                const h = card.dataset.highlight || null;
                analyticsState.highlight = h || null;
                analyticsState.selectedTag = null;
                loadTagAnalyticsSection(root);
            });
        });

        if (analyticsState.selectedTag) {
            const still = tags.find(t => t.id === analyticsState.selectedTag.id && t.tag_type === analyticsState.selectedTag.tag_type);
            if (still) loadUsageEvents(still);
        }
    }

    /* ── Patient Tags CRUD ────────────────────────────────────── */

    async function loadPatientTagsSection(root) {
        const tagsRes = await api('/api/patient-tags');
        const tags = tagsRes.ok ? tagsRes.tags : [];

        root.innerHTML = `
            <div class="tags-inline-form" id="ptNewTagForm">
                <div class="flex-grow-1">
                    <label class="form-label">New patient tag</label>
                    <input type="text" class="form-control form-control-sm" id="ptNewName" placeholder="Tag name" maxlength="50">
                </div>
                <div>
                    <label class="form-label">Color</label>
                    <input type="color" class="form-control form-control-color form-control-sm" id="ptNewColor" value="#6366f1">
                </div>
                <div>
                    <label class="form-label">Scope</label>
                    ${scopeSelectHtml('ptNewScope')}
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="ptCreateBtn"><i class="bi bi-plus-lg me-1"></i>Create</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm tags-admin-table mb-0">
                    <thead><tr><th>Tag</th><th>Scope</th><th>Created</th><th></th></tr></thead>
                    <tbody id="ptTagsTbody"></tbody>
                </table>
            </div>`;

        const tbody = root.querySelector('#ptTagsTbody');
        if (!tags.length) tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No patient tags yet</td></tr>';
        tags.forEach(tag => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${tagBadge(tag)}</td>
                <td>${scopeBadge(tag.doctor_id)}</td>
                <td class="small text-muted">${formatDateTime(tag.created_at)}</td>
                <td class="text-end text-nowrap">${rowActions('pt-edit', 'pt-del', tag.id)}</td>`;
            tbody.appendChild(tr);
        });

        root.querySelector('#ptCreateBtn').addEventListener('click', async () => {
            const name = root.querySelector('#ptNewName').value.trim();
            const color = root.querySelector('#ptNewColor').value;
            if (!name) return notify('Name required', 'error');
            const scope = root.querySelector('#ptNewScope').value;
            const data = await api('/api/patient-tags', {
                method: 'POST',
                json: scopePayload(scope, { name, color, icon: 'bi-tag' })
            });
            if (data.ok) {
                notify('Tag created', 'success');
                refreshAll();
            } else notify(data.error || 'Failed', 'error');
        });

        root.querySelectorAll('.pt-edit').forEach(btn => btn.addEventListener('click', () => {
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (tag) openEditTagModal({ kind: 'patient', tag, onSaved: refreshAll });
        }));
        root.querySelectorAll('.pt-del').forEach(btn => btn.addEventListener('click', () => {
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (tag) confirmDeleteTag(tag, 'patient', refreshAll);
        }));
        initTooltips(root);
    }

    /* ── Appointment Tags CRUD ────────────────────────────────── */

    async function loadAppointmentTagsSection(root) {
        const data = await api('/api/appointment-tags');
        const tags = data.ok ? data.tags : [];

        root.innerHTML = `
            <div class="tags-inline-form">
                <div class="flex-grow-1"><label class="form-label">New appointment tag</label>
                    <input type="text" class="form-control form-control-sm" id="atNewName" maxlength="50"></div>
                <div><label class="form-label">Color</label>
                    <input type="color" class="form-control form-control-color form-control-sm" id="atNewColor" value="#6366f1"></div>
                <div><label class="form-label">Scope</label>
                    ${scopeSelectHtml('atNewScope')}</div>
                <button type="button" class="btn btn-primary btn-sm" id="atCreateBtn"><i class="bi bi-plus-lg me-1"></i>Create</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm tags-admin-table mb-0">
                    <thead><tr><th>Tag</th><th>Scope</th><th>Created</th><th></th></tr></thead>
                    <tbody id="atTagsTbody"></tbody>
                </table>
            </div>`;

        const tbody = root.querySelector('#atTagsTbody');
        if (!tags.length) tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No appointment tags yet</td></tr>';
        tags.forEach(tag => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${tagBadge(tag)}</td>
                <td>${scopeBadge(tag.doctor_id)}</td>
                <td class="small text-muted">${formatDateTime(tag.created_at)}</td>
                <td class="text-end text-nowrap">${rowActions('at-edit', 'at-del', tag.id)}</td>`;
            tbody.appendChild(tr);
        });

        root.querySelector('#atCreateBtn').addEventListener('click', async () => {
            const name = root.querySelector('#atNewName').value.trim();
            const color = root.querySelector('#atNewColor').value;
            if (!name) return notify('Name required', 'error');
            const scope = root.querySelector('#atNewScope').value;
            const res = await api('/api/appointment-tags', {
                method: 'POST',
                json: scopePayload(scope, { name, color, icon: 'bi-tag' })
            });
            if (res.ok) { notify('Created', 'success'); refreshAll(); }
            else notify(res.error || 'Failed', 'error');
        });

        root.querySelectorAll('.at-edit').forEach(btn => btn.addEventListener('click', () => {
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (tag) openEditTagModal({ kind: 'appointment', tag, onSaved: refreshAll });
        }));
        root.querySelectorAll('.at-del').forEach(btn => btn.addEventListener('click', () => {
            const tag = tags.find(t => String(t.id) === btn.dataset.id);
            if (tag) confirmDeleteTag(tag, 'appointment', refreshAll);
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
        const tagOptionsHtml = patientTags.map(t => {
            const scopeLabel = t.doctor_id ? 'Private' : 'Public';
            return `<option value="${t.id}">${esc(t.name)} (${scopeLabel})</option>`;
        }).join('');

        root.innerHTML = `
            <p class="text-muted small">Pick a drug from the database. When prescribed, linked patient tags are suggested (with your approval).</p>
            <div class="tags-inline-form tags-drug-link-form">
                <div class="tags-drug-field">
                    <label class="form-label">Drug</label>
                    <input type="text" class="form-control form-control-sm" id="dtDrugName">
                </div>
                <div>
                    <label class="form-label">Patient tag</label>
                    <select class="form-select form-select-sm" id="dtPatientTag">
                        <option value="">— Select —</option>
                        ${tagOptionsHtml}
                    </select>
                </div>
                <div>
                    <label class="form-label">Link scope</label>
                    ${scopeSelectHtml('dtScope')}
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="dtLinkBtn"><i class="bi bi-link-45deg me-1"></i>Link</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm tags-admin-table mb-0">
                    <thead><tr><th>Drug</th><th>Patient tag</th><th>Scope</th><th>Linked</th><th></th></tr></thead>
                    <tbody id="dtLinksTbody"></tbody>
                </table>
            </div>`;

        setupDrugAutocomplete(root.querySelector('#dtDrugName'));

        const tbody = root.querySelector('#dtLinksTbody');
        links.forEach(link => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${esc(link.drug_name)}</td>
                <td><span class="badge" style="background:${esc(link.tag_color)}">${esc(link.tag_name)}</span></td>
                <td>${scopeBadge(link.doctor_id)}</td>
                <td class="small text-muted">${formatDateTime(link.created_at)}</td>
                <td class="text-end text-nowrap">${deleteAction('dt-del', link.id)}</td>`;
            tbody.appendChild(tr);
        });
        if (!links.length) tbody.innerHTML = '<tr><td colspan="5" class="text-muted">No drug links yet</td></tr>';

        root.querySelector('#dtLinkBtn').addEventListener('click', async () => {
            const drug_name = root.querySelector('#dtDrugName').value.trim();
            const patient_tag_id = parseInt(root.querySelector('#dtPatientTag').value, 10);
            const scope = root.querySelector('#dtScope').value;
            if (!drug_name || !patient_tag_id) return notify('Select drug and tag', 'error');
            const r = await api('/api/drug-tag-links', {
                method: 'POST',
                json: scopePayload(scope, { drug_name, patient_tag_id })
            });
            if (r.ok) { notify('Linked', 'success'); refreshAll(); }
            else notify(r.error || 'Failed', 'error');
        });
        root.querySelectorAll('.dt-del').forEach(btn => btn.addEventListener('click', () => {
            const link = links.find(l => String(l.id) === btn.dataset.id);
            if (!link) return;
            showConfirmModal({
                title: 'Remove drug link',
                bodyHtml: `<p class="mb-0">Remove link between <strong>${esc(link.drug_name)}</strong> and <strong>${esc(link.tag_name)}</strong>?</p>`,
                confirmLabel: 'Remove',
                confirmClass: 'btn-danger',
                onConfirm: async () => {
                    await api(`/api/drug-tag-links/${link.id}`, { method: 'DELETE' });
                    notify('Link removed', 'success');
                    refreshAll();
                }
            });
        }));
        initTooltips(root);
    }

    function refreshAll() {
        const a = document.getElementById('tagAnalyticsBody');
        const p = document.getElementById('patientTagsAdminBody');
        const ap = document.getElementById('appointmentTagsAdminBody');
        const d = document.getElementById('drugTagsAdminBody');
        if (a) loadTagAnalyticsSection(a);
        if (p) loadPatientTagsSection(p);
        if (ap) loadAppointmentTagsSection(ap);
        if (d) loadDrugLinksSection(d);
    }

    function mount() {
        const analyticsRoot = document.getElementById('tagAnalyticsBody');
        const ptRoot = document.getElementById('patientTagsAdminBody');
        const atRoot = document.getElementById('appointmentTagsAdminBody');
        const dtRoot = document.getElementById('drugTagsAdminBody');
        if (analyticsRoot) loadTagAnalyticsSection(analyticsRoot);
        if (ptRoot) loadPatientTagsSection(ptRoot);
        if (atRoot) loadAppointmentTagsSection(atRoot);
        if (dtRoot) loadDrugLinksSection(dtRoot);
    }

    window.tagsAdmin = { mount, refreshAll };
    document.addEventListener('DOMContentLoaded', mount);
})();
