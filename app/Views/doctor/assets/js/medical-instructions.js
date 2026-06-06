/**
 * medical-instructions.js — Appointment card: Medical Instructions
 */
(function () {
    'use strict';

    const cfg = () => window.APPOINTMENT_CONFIG || {};
    const apptId = () => cfg().appointmentId;
    const patientId = () => cfg().patientId;

    let instructions = [];
    let suggestions = [];

    function api(method, url, body) {
        const opts = {
            method,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
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
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function toast(type, title, msg) {
        if (typeof window.showToast === 'function') window.showToast(type, title, msg);
        else if (type === 'error') alert(msg || title);
    }

    function visitTemplateMeta() {
        const dx = (cfg().latestDiagnosis || '').trim();
        const icd = (cfg().latestDiagnosisCode || '').trim();
        return {
            diagnosis_keywords: dx || null,
            icd_code: icd || null
        };
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

    function buildSaveTemplateConfirmHtml(form, meta) {
        const rows = [
            `<li><span class="mi-confirm-k">Title</span><span class="mi-confirm-v">${esc(form.title)}</span></li>`
        ];
        if (meta.diagnosis_keywords) {
            rows.push(`<li><span class="mi-confirm-k">Diagnosis</span><span class="mi-confirm-v">${esc(meta.diagnosis_keywords)}</span></li>`);
        }
        if (meta.icd_code) {
            rows.push(`<li><span class="mi-confirm-k">ICD code</span><span class="mi-confirm-v">${esc(meta.icd_code)}</span></li>`);
        }
        const autoNote = (meta.diagnosis_keywords || meta.icd_code)
            ? '<p class="mi-confirm-note mb-2"><i class="bi bi-magic me-1"></i>Diagnosis and ICD are taken from this visit automatically.</p>'
            : '<p class="mi-confirm-note mb-2 text-muted">No diagnosis or ICD code on this visit — template will be saved without matching keywords.</p>';
        return `${autoNote}<p class="mb-2">Save as a <strong>clinic-wide</strong> reusable template?</p><ul class="mi-confirm-details">${rows.join('')}</ul>`;
    }

    function sourceLabel(src) {
        const map = {
            auto_diagnosis: 'Diagnosis',
            auto_history: 'History',
            template: 'Template',
            custom: 'Custom'
        };
        return map[src] || 'Custom';
    }

    function renderList() {
        const list = document.getElementById('medicalInstructionsList');
        const empty = document.getElementById('emptyMedicalInstructionsMessage');
        if (!list) return;

        if (!instructions.length) {
            list.innerHTML = '';
            if (empty) empty.style.display = '';
            return;
        }
        if (empty) empty.style.display = 'none';

        list.innerHTML = instructions.map(item => `
            <div class="mi-item" data-id="${item.id}">
                <div class="mi-item-head">
                    <div class="min-w-0">
                        <h6 class="mi-item-title">${esc(item.title)}</h6>
                        <span class="mi-source-badge">${esc(sourceLabel(item.source))}</span>
                    </div>
                    <div class="btn-group btn-group-sm flex-shrink-0">
                        <button type="button" class="btn btn-outline-primary mi-edit" data-id="${item.id}" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-outline-danger mi-del" data-id="${item.id}" title="Delete"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <p class="mi-item-body" dir="rtl">${esc(item.body_ar)}</p>
                ${item.body_en ? `<p class="mi-item-body-en">${esc(item.body_en)}</p>` : ''}
            </div>
        `).join('');

        list.querySelectorAll('.mi-edit').forEach(btn => {
            btn.addEventListener('click', () => openCustomModal(parseInt(btn.dataset.id, 10)));
        });
        list.querySelectorAll('.mi-del').forEach(btn => {
            btn.addEventListener('click', () => deleteInstruction(parseInt(btn.dataset.id, 10)));
        });
    }

    function renderSuggestions() {
        const panel = document.getElementById('medicalInstructionsSuggestions');
        if (!panel) return;

        if (!suggestions.length) {
            panel.hidden = true;
            panel.innerHTML = '';
            return;
        }

        panel.hidden = false;
        panel.innerHTML = `
            <h6><i class="bi bi-lightbulb me-1"></i>Suggested from templates</h6>
            ${suggestions.map(s => `
                <div class="mi-suggest-row">
                    <div class="min-w-0">
                        <strong class="small">${esc(s.title)}</strong>
                        <div class="mi-suggest-meta">${esc(s.match_diagnosis || '')} · ${s.match_source === 'current' ? 'Current visit' : 'Patient history'}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0 mi-copy-one" data-tpl="${s.id}">
                        <i class="bi bi-clipboard-plus"></i>
                    </button>
                </div>
            `).join('')}
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-sm btn-primary" id="miCopyAllSuggested">
                    <i class="bi bi-clipboard-check me-1"></i>Copy all suggested
                </button>
            </div>`;

        panel.querySelectorAll('.mi-copy-one').forEach(btn => {
            btn.addEventListener('click', () => copySuggestions([parseInt(btn.dataset.tpl, 10)]));
        });
        const allBtn = document.getElementById('miCopyAllSuggested');
        if (allBtn) {
            allBtn.addEventListener('click', () => copySuggestions(suggestions.map(s => parseInt(s.id, 10))));
        }
    }

    async function loadInstructions() {
        const data = await api('GET', `/api/appointments/${apptId()}/medical-instructions`);
        instructions = data.instructions || [];
        renderList();
    }

    async function loadSuggestions() {
        const dx = (cfg().latestDiagnosis || '').trim();
        const params = new URLSearchParams();
        if (dx) params.set('diagnosis', dx);
        if (patientId()) params.set('patient_id', String(patientId()));
        const data = await api('GET', `/api/instruction-templates/suggestions?${params}`);
        suggestions = data.suggestions || [];
        renderSuggestions();
    }

    async function copySuggestions(templateIds) {
        const items = suggestions
            .filter(s => templateIds.includes(parseInt(s.id, 10)))
            .map(s => ({
                template_id: s.id,
                title: s.title,
                body_ar: s.body_ar,
                body_en: s.body_en,
                source: s.match_source === 'current' ? 'auto_diagnosis' : 'auto_history'
            }));
        if (!items.length) return;
        try {
            await api('POST', `/api/appointments/${apptId()}/medical-instructions`, { items });
            toast('success', 'Added', `${items.length} instruction(s) copied.`);
            await loadInstructions();
            await loadSuggestions();
        } catch (e) {
            toast('error', 'Error', e.message);
        }
    }

    function openCustomModal(editId) {
        const existing = editId ? instructions.find(i => parseInt(i.id, 10) === editId) : null;
        const isEdit = !!existing;

        const html = `
            <div class="modal fade mi-theme-modal" id="miCustomModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${isEdit ? 'Edit' : 'Add'} Medical Instruction</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="miCustomTitle" maxlength="120" value="${esc(existing?.title || '')}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructions (Arabic) <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="miCustomBodyAr" rows="6" dir="rtl">${esc(existing?.body_ar || '')}</textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Instructions (English, optional)</label>
                                <textarea class="form-control" id="miCustomBodyEn" rows="4">${esc(existing?.body_en || '')}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-success me-auto" id="miCustomSaveTpl" title="Save as a clinic-wide reusable template">
                                <i class="bi bi-journal-plus me-1"></i>Save as template
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="miCustomSave">${isEdit ? 'Save' : 'Add'}</button>
                        </div>
                    </div>
                </div>
            </div>`;
        const prev = document.getElementById('miCustomModal');
        if (prev) prev.remove();
        document.body.insertAdjacentHTML('beforeend', html);
        const el = document.getElementById('miCustomModal');
        const modal = new bootstrap.Modal(el);
        modal.show();

        function readCustomForm() {
            return {
                title: document.getElementById('miCustomTitle').value.trim(),
                body_ar: document.getElementById('miCustomBodyAr').value,
                body_en: document.getElementById('miCustomBodyEn').value.trim() || null
            };
        }

        document.getElementById('miCustomSaveTpl').addEventListener('click', () => {
            const form = readCustomForm();
            if (!form.title || !form.body_ar.trim()) {
                toast('error', 'Required', 'Title and Arabic instructions are required to save a template.');
                return;
            }
            const meta = visitTemplateMeta();
            showMiConfirmModal({
                title: 'Save as template',
                bodyHtml: buildSaveTemplateConfirmHtml(form, meta),
                confirmLabel: 'Save template',
                confirmClass: 'btn-success',
                onConfirm: async () => {
                    const saveTplBtn = document.getElementById('miCustomSaveTpl');
                    saveTplBtn.disabled = true;
                    try {
                        await api('POST', '/api/instruction-templates', {
                            title: form.title,
                            body_ar: form.body_ar,
                            body_en: form.body_en,
                            category: 'general',
                            diagnosis_keywords: meta.diagnosis_keywords,
                            icd_code: meta.icd_code
                        });
                        toast('success', 'Template saved', 'Saved as a clinic-wide template.');
                        await loadSuggestions();
                    } catch (e) {
                        toast('error', 'Error', e.message);
                    } finally {
                        saveTplBtn.disabled = false;
                    }
                }
            });
        });

        document.getElementById('miCustomSave').addEventListener('click', async () => {
            const form = readCustomForm();
            const payload = { ...form, source: 'custom' };
            try {
                if (isEdit) {
                    await api('PATCH', `/api/appointments/${apptId()}/medical-instructions/${editId}`, payload);
                } else {
                    await api('POST', `/api/appointments/${apptId()}/medical-instructions`, payload);
                }
                modal.hide();
                toast('success', 'Saved', 'Instruction saved.');
                await loadInstructions();
            } catch (e) {
                toast('error', 'Error', e.message);
            }
        });
        el.addEventListener('hidden.bs.modal', () => el.remove());
    }

    async function openTemplatesPicker() {
        const data = await api('GET', '/api/instruction-templates');
        const tpls = data.templates || [];
        if (!tpls.length) {
            toast('info', 'No templates', 'Create templates from the sidebar page first.');
            return;
        }

        const html = `
            <div class="modal fade mi-theme-modal" id="miTplPickerModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Choose from templates</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="list-group list-group-flush" id="miTplPickerList"></div>
                        </div>
                    </div>
                </div>
            </div>`;
        const prev = document.getElementById('miTplPickerModal');
        if (prev) prev.remove();
        document.body.insertAdjacentHTML('beforeend', html);
        const el = document.getElementById('miTplPickerModal');
        const list = document.getElementById('miTplPickerList');
        list.innerHTML = tpls.map(t => `
            <button type="button" class="list-group-item list-group-item-action text-start mi-tpl-pick" data-id="${t.id}">
                <strong>${esc(t.title)}</strong>
                <div class="small text-muted text-truncate">${esc((t.body_ar || '').slice(0, 120))}</div>
            </button>
        `).join('');
        const modal = new bootstrap.Modal(el);
        modal.show();

        list.querySelectorAll('.mi-tpl-pick').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = parseInt(btn.dataset.id, 10);
                const tpl = tpls.find(t => parseInt(t.id, 10) === id);
                if (!tpl) return;
                try {
                    await api('POST', `/api/appointments/${apptId()}/medical-instructions`, {
                        template_id: tpl.id,
                        title: tpl.title,
                        body_ar: tpl.body_ar,
                        body_en: tpl.body_en,
                        source: 'template'
                    });
                    modal.hide();
                    toast('success', 'Added', 'Template applied.');
                    await loadInstructions();
                } catch (e) {
                    toast('error', 'Error', e.message);
                }
            });
        });
        el.addEventListener('hidden.bs.modal', () => el.remove());
    }

    async function deleteInstruction(id) {
        if (!confirm('Remove this instruction from the appointment?')) return;
        try {
            await api('DELETE', `/api/appointments/${apptId()}/medical-instructions/${id}`);
            await loadInstructions();
        } catch (e) {
            toast('error', 'Error', e.message);
        }
    }

    function bindButtons() {
        document.getElementById('miAddCustomBtn')?.addEventListener('click', () => openCustomModal(null));
        document.getElementById('miFromTemplatesBtn')?.addEventListener('click', () => openTemplatesPicker());
        document.getElementById('miCopySuggestedBtn')?.addEventListener('click', () => {
            if (!suggestions.length) {
                toast('info', 'No suggestions', 'No matching templates for this diagnosis or history.');
                return;
            }
            copySuggestions(suggestions.map(s => parseInt(s.id, 10)));
        });
    }

    function init() {
        if (!apptId() || !document.getElementById('medicalInstructionsCard')) return;
        instructions = Array.isArray(cfg().medicalInstructions) ? cfg().medicalInstructions.slice() : [];
        renderList();
        bindButtons();
        loadSuggestions().catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', init);

    window.medicalInstructions = { reload: loadInstructions, reloadSuggestions: loadSuggestions };
})();
