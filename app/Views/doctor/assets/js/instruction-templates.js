/**
 * instruction-templates.js — CRUD for medical instruction templates.
 * Public API: window.instructionTemplates.mount(container, addBtn)
 */
(function () {
    'use strict';

    const CATEGORIES = [
        { value: 'general', label: 'General' },
        { value: 'lifestyle', label: 'Lifestyle' },
        { value: 'exercise', label: 'Exercise' },
        { value: 'warnings', label: 'Warnings' },
        { value: 'followup', label: 'Follow-up' },
        { value: 'other', label: 'Other' }
    ];

    let containerEl = null;
    let templates = [];

    function api(method, url, body) {
        const opts = {
            method,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || `Request failed (${res.status})`);
            return data;
        });
    }

    function esc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function toast(type, title, msg) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, title, msg);
        } else if (type === 'error') {
            alert(msg || title);
        }
    }

    function catLabel(v) {
        const m = CATEGORIES.find(c => c.value === v);
        return m ? m.label : (v || 'Other');
    }

    function showMiConfirmModal({ title, bodyHtml, confirmLabel = 'Confirm', confirmClass = 'btn-primary', onConfirm }) {
        const id = 'miConfirmModal';
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
                            <button type="button" class="btn ${confirmClass}" id="miConfirmOk">${esc(confirmLabel)}</button>
                        </div>
                    </div>
                </div>
            </div>`);
        const el = document.getElementById(id);
        const modal = new bootstrap.Modal(el);
        modal.show();
        document.getElementById('miConfirmOk').addEventListener('click', () => {
            modal.hide();
            if (typeof onConfirm === 'function') onConfirm();
        });
        el.addEventListener('hidden.bs.modal', () => el.remove(), { once: true });
    }

    function buildTemplateSaveConfirmHtml(payload, isEdit) {
        const rows = [
            `<li><span class="mi-confirm-k">Title</span><span class="mi-confirm-v">${esc(payload.title)}</span></li>`,
            `<li><span class="mi-confirm-k">Category</span><span class="mi-confirm-v">${esc(catLabel(payload.category))}</span></li>`
        ];
        if (payload.diagnosis_keywords) {
            rows.push(`<li><span class="mi-confirm-k">Diagnosis</span><span class="mi-confirm-v">${esc(payload.diagnosis_keywords)}</span></li>`);
        }
        if (payload.icd_code) {
            rows.push(`<li><span class="mi-confirm-k">ICD code</span><span class="mi-confirm-v">${esc(payload.icd_code)}</span></li>`);
        }
        const verb = isEdit ? 'Update' : 'Create';
        return `<p class="mb-2">${verb} this <strong>clinic-wide</strong> template?</p><ul class="mi-confirm-details">${rows.join('')}</ul>`;
    }

    async function load() {
        const data = await api('GET', '/api/instruction-templates');
        templates = data.templates || [];
        render();
    }

    function render() {
        if (!containerEl) return;
        if (!templates.length) {
            containerEl.innerHTML = `
                <div class="inst-tpl-empty">
                    <i class="bi bi-journal-medical d-block mb-2" style="font-size:2rem;opacity:.45"></i>
                    <p class="mb-1">No instruction templates yet.</p>
                    <p class="small mb-0">Create clinic-wide templates linked to diagnosis keywords.</p>
                </div>`;
            return;
        }

        containerEl.innerHTML = templates.map(t => {
            const preview = (t.body_ar || '').trim().slice(0, 220);
            const kw = (t.diagnosis_keywords || '').trim();
            return `
                <div class="inst-tpl-card" data-id="${t.id}">
                    <div class="inst-tpl-card-head">
                        <div class="min-w-0">
                            <h6 class="inst-tpl-card-title">${esc(t.title)}</h6>
                            <div class="inst-tpl-card-meta">
                                <span class="inst-tpl-badge clinic">Clinic-wide</span>
                                <span class="inst-tpl-badge">${esc(catLabel(t.category))}</span>
                                ${t.use_count > 0 ? `<span class="inst-tpl-badge">Used ${t.use_count}×</span>` : ''}
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm flex-shrink-0">
                            <button type="button" class="btn btn-outline-primary inst-tpl-edit" data-id="${t.id}" title="Edit template"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-outline-danger inst-tpl-del" data-id="${t.id}" title="Delete template"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="inst-tpl-card-body">${esc(preview)}${preview.length >= 220 ? '…' : ''}</div>
                    ${kw ? `<div class="inst-tpl-kw"><i class="bi bi-tags me-1"></i>${esc(kw)}</div>` : ''}
                </div>`;
        }).join('');

        containerEl.querySelectorAll('.inst-tpl-edit').forEach(btn => {
            btn.addEventListener('click', () => openModal(parseInt(btn.dataset.id, 10)));
        });
        containerEl.querySelectorAll('.inst-tpl-del').forEach(btn => {
            btn.addEventListener('click', () => deleteTemplate(parseInt(btn.dataset.id, 10)));
        });
    }

    function openModal(editId) {
        const existing = editId ? templates.find(t => parseInt(t.id, 10) === editId) : null;
        const isEdit = !!existing;

        const modalHtml = `
            <div class="modal fade mi-theme-modal" id="instTplModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${isEdit ? 'Edit' : 'New'} Instruction Template</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="instTplTitle" maxlength="120" value="${esc(existing?.title || '')}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" id="instTplCategory">
                                        ${CATEGORIES.map(c => `<option value="${c.value}" ${existing?.category === c.value ? 'selected' : ''}>${c.label}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ICD code (optional)</label>
                                    <input type="text" class="form-control" id="instTplIcd" maxlength="20" value="${esc(existing?.icd_code || '')}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Diagnosis keywords</label>
                                    <input type="text" class="form-control" id="instTplKeywords" placeholder="خشونة, osteoarthritis, OA knee" value="${esc(existing?.diagnosis_keywords || '')}">
                                    <div class="form-text">Comma-separated. Matched against current diagnosis (≥85% similarity or substring).</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Instructions (Arabic) <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="instTplBodyAr" rows="6" dir="rtl">${esc(existing?.body_ar || '')}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Instructions (English, optional)</label>
                                    <textarea class="form-control" id="instTplBodyEn" rows="4">${esc(existing?.body_en || '')}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="instTplSaveBtn">${isEdit ? 'Save changes' : 'Create template'}</button>
                        </div>
                    </div>
                </div>
            </div>`;

        const prev = document.getElementById('instTplModal');
        if (prev) prev.remove();
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modalEl = document.getElementById('instTplModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        document.getElementById('instTplSaveBtn').addEventListener('click', () => {
            const payload = {
                title: document.getElementById('instTplTitle').value.trim(),
                category: document.getElementById('instTplCategory').value,
                body_ar: document.getElementById('instTplBodyAr').value,
                body_en: document.getElementById('instTplBodyEn').value.trim() || null,
                diagnosis_keywords: document.getElementById('instTplKeywords').value.trim() || null,
                icd_code: document.getElementById('instTplIcd').value.trim() || null
            };
            if (!payload.title) {
                toast('error', 'Required', 'Title is required.');
                return;
            }
            if (!payload.body_ar.trim()) {
                toast('error', 'Required', 'Arabic instructions are required.');
                return;
            }
            showMiConfirmModal({
                title: isEdit ? 'Update template' : 'Create template',
                bodyHtml: buildTemplateSaveConfirmHtml(payload, isEdit),
                confirmLabel: isEdit ? 'Save changes' : 'Create template',
                confirmClass: 'btn-primary',
                onConfirm: async () => {
                    try {
                        if (isEdit) {
                            await api('PATCH', `/api/instruction-templates/${editId}`, payload);
                            toast('success', 'Saved', 'Template updated.');
                        } else {
                            await api('POST', '/api/instruction-templates', payload);
                            toast('success', 'Created', 'Template created.');
                        }
                        modal.hide();
                        await load();
                    } catch (e) {
                        toast('error', 'Error', e.message);
                    }
                }
            });
        });

        modalEl.addEventListener('hidden.bs.modal', () => modalEl.remove());
    }

    async function deleteTemplate(id) {
        const tpl = templates.find(t => parseInt(t.id, 10) === id);
        showMiConfirmModal({
            title: 'Delete template',
            bodyHtml: `<p class="mb-2">Delete the clinic-wide template <strong>${esc(tpl?.title || '')}</strong>?</p><p class="mi-confirm-note mb-0">This cannot be undone. Existing appointment copies are kept.</p>`,
            confirmLabel: 'Delete',
            confirmClass: 'btn-danger',
            onConfirm: async () => {
                try {
                    await api('DELETE', `/api/instruction-templates/${id}`);
                    toast('success', 'Deleted', 'Template removed.');
                    await load();
                } catch (e) {
                    toast('error', 'Error', e.message);
                }
            }
        });
    }

    function mount(containerSelector, addBtnSelector) {
        containerEl = document.querySelector(containerSelector);
        const addBtn = document.querySelector(addBtnSelector);
        if (!containerEl) return;
        if (addBtn) {
            addBtn.addEventListener('click', () => openModal(null));
        }
        load().catch(err => {
            containerEl.innerHTML = `<div class="alert alert-danger">${esc(err.message)}</div>`;
        });
    }

    window.instructionTemplates = { mount, load };
})();
