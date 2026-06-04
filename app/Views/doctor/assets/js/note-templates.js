/*
 * note-templates.js
 * Two surfaces:
 *   (A) Insert-template dropdown on Edit Consultation page (mountDropdown)
 *   (B) Settings page CRUD manager (mountSettings)
 *
 * Public API: window.noteTemplates = { mountDropdown, mountSettings, refresh }
 *
 * Backend endpoints used:
 *   GET    /api/note-templates
 *   POST   /api/note-templates              { title, body, category, color }
 *   PATCH  /api/note-templates/:id          { title?, body?, category?, color? }
 *   DELETE /api/note-templates/:id
 *   POST   /api/note-templates/reorder      { order: [id, id, ...] }
 *   POST   /api/note-templates/:id/used
 *   POST   /api/note-templates/seed-defaults
 */
(function () {
    'use strict';

    // ---------- shared state & helpers --------------------------------------

    const state = {
        templates: null,           // null = not loaded, [] = empty
        loading: false,
        loadPromise: null,
        listeners: new Set()       // re-render callbacks when state changes
    };

    const CATEGORY_OPTIONS = [
        { value: 'general',       label: 'General' },
        { value: 'examination',   label: 'Examination' },
        { value: 'diagnosis',     label: 'Diagnosis' },
        { value: 'plan',          label: 'Plan' },
        { value: 'followup',      label: 'Follow-up' },
        { value: 'prescription',  label: 'Prescription' },
        { value: 'discharge',     label: 'Discharge' },
        { value: 'other',         label: 'Other' }
    ];

    const COLOR_OPTIONS = [
        { value: 'indigo',  hex: 'var(--palette-indigo,  #6366f1)' },
        { value: 'emerald', hex: 'var(--palette-emerald, #10b981)' },
        { value: 'rose',    hex: 'var(--palette-rose,    #f43f5e)' },
        { value: 'amber',   hex: 'var(--palette-amber,   #f59e0b)' },
        { value: 'sky',     hex: 'var(--palette-sky,     #0ea5e9)' },
        { value: 'violet',  hex: 'var(--palette-violet,  #8b5cf6)' }
    ];

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
            const text = await res.text();
            let data = null;
            try { data = text ? JSON.parse(text) : null; } catch (e) { /* non-json */ }
            if (!res.ok) {
                const msg = (data && (data.message || data.error)) || `Request failed (${res.status})`;
                const err = new Error(msg);
                err.status = res.status;
                err.data = data;
                throw err;
            }
            return data;
        });
    }

    function toast(type, title, message) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, title, message);
        } else if (type === 'error') {
            console.error(title, message);
        } else {
            console.log(title, message);
        }
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function categoryLabel(value) {
        const match = CATEGORY_OPTIONS.find(c => c.value === value);
        return match ? match.label : (value || 'Other');
    }

    function colorHex(value) {
        const match = COLOR_OPTIONS.find(c => c.value === value);
        return match ? match.hex : COLOR_OPTIONS[0].hex;
    }

    function notifyListeners() {
        state.listeners.forEach((cb) => {
            try { cb(state.templates); } catch (e) { console.error('[note-templates] listener', e); }
        });
    }

    function loadTemplates(force) {
        if (!force && state.templates !== null) return Promise.resolve(state.templates);
        if (state.loadPromise) return state.loadPromise;
        state.loading = true;
        state.loadPromise = api('GET', '/api/note-templates')
            .then((data) => {
                const list = Array.isArray(data) ? data : (data && Array.isArray(data.items) ? data.items : []);
                state.templates = list.slice().sort((a, b) => {
                    const ap = (a.position != null) ? a.position : 0;
                    const bp = (b.position != null) ? b.position : 0;
                    if (ap !== bp) return ap - bp;
                    return String(a.title || '').localeCompare(String(b.title || ''));
                });
                return state.templates;
            })
            .catch((err) => {
                console.error('[note-templates] load failed', err);
                state.templates = [];
                throw err;
            })
            .finally(() => {
                state.loading = false;
                state.loadPromise = null;
                notifyListeners();
            });
        return state.loadPromise;
    }

    function groupByCategory(templates) {
        const groups = new Map();
        templates.forEach((t) => {
            const key = t.category || 'other';
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(t);
        });
        // Order groups by CATEGORY_OPTIONS sequence then alphabetical for anything else
        const ordered = [];
        CATEGORY_OPTIONS.forEach((opt) => {
            if (groups.has(opt.value)) {
                ordered.push({ key: opt.value, label: opt.label, items: groups.get(opt.value) });
                groups.delete(opt.value);
            }
        });
        Array.from(groups.entries()).forEach(([key, items]) => {
            ordered.push({ key, label: categoryLabel(key), items });
        });
        return ordered;
    }

    // ---------- (A) Dropdown popover --------------------------------------

    function mountDropdown(textareaSelector, dropdownAnchor) {
        const textarea = typeof textareaSelector === 'string'
            ? document.querySelector(textareaSelector)
            : textareaSelector;
        if (!textarea) {
            console.warn('[note-templates] mountDropdown: textarea not found', textareaSelector);
            return null;
        }

        const anchor = dropdownAnchor
            ? (typeof dropdownAnchor === 'string' ? document.querySelector(dropdownAnchor) : dropdownAnchor)
            : null;

        // Build trigger button
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'note-tpl-trigger btn btn-sm';
        trigger.setAttribute('aria-haspopup', 'menu');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.innerHTML = '<i class="bi bi-file-earmark-text" aria-hidden="true"></i><span class="note-tpl-trigger-label">Insert template</span><i class="bi bi-chevron-down note-tpl-trigger-caret" aria-hidden="true"></i>';

        if (anchor) {
            anchor.appendChild(trigger);
        } else {
            // Insert just before the textarea
            textarea.parentNode.insertBefore(trigger, textarea);
        }

        // Popover element (appended to body, positioned absolutely)
        const pop = document.createElement('div');
        pop.className = 'note-tpl-dropdown';
        pop.setAttribute('role', 'menu');
        pop.setAttribute('aria-label', 'Insert note template');
        pop.hidden = true;

        pop.innerHTML = `
            <div class="note-tpl-dropdown-head">
                <div class="note-tpl-search-wrap">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="text" class="note-tpl-search" placeholder="Search templates…" aria-label="Search templates" autocomplete="off" spellcheck="false">
                </div>
            </div>
            <div class="note-tpl-dropdown-body" role="none"></div>
            <div class="note-tpl-dropdown-foot">
                <span class="note-tpl-foot-hint">Click a template to insert at cursor</span>
            </div>
        `;
        document.body.appendChild(pop);

        const body = pop.querySelector('.note-tpl-dropdown-body');
        const search = pop.querySelector('.note-tpl-search');
        let isOpen = false;
        let filterText = '';

        function renderRows() {
            const list = state.templates || [];
            const q = filterText.trim().toLowerCase();
            const filtered = q
                ? list.filter(t =>
                    String(t.title || '').toLowerCase().includes(q) ||
                    String(t.body || '').toLowerCase().includes(q) ||
                    String(t.category || '').toLowerCase().includes(q))
                : list;

            if (state.loading && !list.length) {
                body.innerHTML = '<div class="note-tpl-empty"><i class="bi bi-hourglass-split" aria-hidden="true"></i><div>Loading templates…</div></div>';
                return;
            }
            if (!filtered.length) {
                if (!list.length) {
                    body.innerHTML = '<div class="note-tpl-empty"><i class="bi bi-stickies" aria-hidden="true"></i><div>No templates yet</div><div class="note-tpl-empty-hint">Create them in Settings → Note Templates.</div></div>';
                } else {
                    body.innerHTML = '<div class="note-tpl-empty"><i class="bi bi-search" aria-hidden="true"></i><div>No matches for “' + escHtml(q) + '”</div></div>';
                }
                return;
            }

            const groups = groupByCategory(filtered);
            const parts = [];
            groups.forEach((g) => {
                parts.push(`<div class="note-tpl-group" role="none">`);
                parts.push(`<div class="note-tpl-group-head">${escHtml(g.label)}</div>`);
                g.items.forEach((t) => {
                    const hex = colorHex(t.color);
                    const preview = String(t.body || '').replace(/\s+/g, ' ').slice(0, 80);
                    parts.push(`
                        <button type="button" class="note-tpl-row" role="menuitem" data-id="${escHtml(t.id)}" data-color="${escHtml(t.color || 'indigo')}" style="--row-accent:${hex};">
                            <span class="note-tpl-row-swatch" aria-hidden="true"></span>
                            <span class="note-tpl-row-text">
                                <span class="note-tpl-row-title">${escHtml(t.title || 'Untitled')}</span>
                                <span class="note-tpl-row-preview">${escHtml(preview)}</span>
                            </span>
                            <i class="bi bi-arrow-return-left note-tpl-row-icon" aria-hidden="true"></i>
                        </button>
                    `);
                });
                parts.push(`</div>`);
            });
            body.innerHTML = parts.join('');
        }

        function position() {
            const rect = trigger.getBoundingClientRect();
            const vw = window.innerWidth;
            const vh = window.innerHeight;
            const minWidth = Math.max(rect.width, 320);
            const maxWidth = Math.min(vw - 16, 480);
            const width = Math.min(maxWidth, Math.max(minWidth, 360));
            let left = rect.left;
            if (left + width > vw - 8) left = Math.max(8, vw - width - 8);
            if (left < 8) left = 8;

            // Default below; flip above if not enough room
            const popHeight = Math.min(vh * 0.6, 520);
            let top = rect.bottom + 6;
            if (top + popHeight > vh - 8 && rect.top > popHeight + 12) {
                top = rect.top - popHeight - 6;
            }
            pop.style.left = left + 'px';
            pop.style.top = top + 'px';
            pop.style.width = width + 'px';
        }

        function open() {
            if (isOpen) return;
            isOpen = true;
            trigger.setAttribute('aria-expanded', 'true');
            trigger.classList.add('is-open');
            pop.hidden = false;
            // Animate in next frame
            requestAnimationFrame(() => pop.classList.add('is-open'));
            position();
            renderRows();
            // Focus search after open
            setTimeout(() => { try { search.focus({ preventScroll: true }); } catch (e) { search.focus(); } }, 30);

            document.addEventListener('mousedown', onDocClick, true);
            document.addEventListener('keydown', onKeyDown, true);
            window.addEventListener('resize', position);
            window.addEventListener('scroll', position, true);

            // Load if needed
            if (state.templates === null && !state.loading) {
                loadTemplates().then(renderRows).catch(() => renderRows());
            }
        }

        function close() {
            if (!isOpen) return;
            isOpen = false;
            trigger.setAttribute('aria-expanded', 'false');
            trigger.classList.remove('is-open');
            pop.classList.remove('is-open');
            // Hide after transition
            setTimeout(() => { if (!isOpen) pop.hidden = true; }, 160);
            document.removeEventListener('mousedown', onDocClick, true);
            document.removeEventListener('keydown', onKeyDown, true);
            window.removeEventListener('resize', position);
            window.removeEventListener('scroll', position, true);
        }

        function onDocClick(e) {
            if (pop.contains(e.target) || trigger.contains(e.target)) return;
            close();
        }

        function onKeyDown(e) {
            if (e.key === 'Escape') { e.preventDefault(); close(); trigger.focus(); return; }
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                const rows = Array.from(pop.querySelectorAll('.note-tpl-row'));
                if (!rows.length) return;
                e.preventDefault();
                const active = document.activeElement;
                let idx = rows.indexOf(active);
                if (idx === -1) {
                    idx = e.key === 'ArrowDown' ? 0 : rows.length - 1;
                } else {
                    idx += (e.key === 'ArrowDown' ? 1 : -1);
                    if (idx < 0) idx = rows.length - 1;
                    if (idx >= rows.length) idx = 0;
                }
                rows[idx].focus();
            }
        }

        function insertAtCaret(text) {
            const t = textarea;
            // Restore focus so caret position is known
            t.focus();
            const start = (typeof t.selectionStart === 'number') ? t.selectionStart : t.value.length;
            const end   = (typeof t.selectionEnd   === 'number') ? t.selectionEnd   : start;
            const before = t.value.slice(0, start);
            const needsLeadingNl = before.length > 0 && !/[\n]\s*$/.test(before) ? '\n' : '';
            const after = t.value.slice(end);
            const needsTrailingNl = after.length > 0 && !/^\s*[\n]/.test(after) ? '\n' : '';
            const payload = `${needsLeadingNl}${text}${needsTrailingNl}`;

            if (typeof t.setRangeText === 'function') {
                t.setRangeText(payload, start, end, 'end');
            } else {
                t.value = before + payload + after;
                const pos = start + payload.length;
                t.setSelectionRange(pos, pos);
            }
            t.dispatchEvent(new Event('input',  { bubbles: true }));
            t.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // ---------- Events on popover ----
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (isOpen) close(); else open();
        });

        search.addEventListener('input', () => {
            filterText = search.value;
            renderRows();
        });
        search.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const first = pop.querySelector('.note-tpl-row');
                if (first) { e.preventDefault(); first.click(); }
            }
        });

        pop.addEventListener('click', (e) => {
            const row = e.target.closest('.note-tpl-row');
            if (!row) return;
            const id = row.getAttribute('data-id');
            const tpl = (state.templates || []).find(t => String(t.id) === String(id));
            if (!tpl) return;
            insertAtCaret(tpl.body || '');
            close();
            api('POST', `/api/note-templates/${encodeURIComponent(id)}/used`).catch((err) => {
                console.warn('[note-templates] used ping failed', err);
            });
        });

        // Listen for state changes (e.g., user adds a template in Settings on same page)
        const listener = () => { if (isOpen) renderRows(); };
        state.listeners.add(listener);

        return {
            open, close,
            destroy() {
                state.listeners.delete(listener);
                close();
                trigger.remove();
                pop.remove();
            }
        };
    }

    // ---------- (B) Settings CRUD manager ---------------------------------

    function mountSettings(containerSelector) {
        const container = typeof containerSelector === 'string'
            ? document.querySelector(containerSelector)
            : containerSelector;
        if (!container) {
            console.warn('[note-templates] mountSettings: container not found', containerSelector);
            return null;
        }

        container.classList.add('note-tpl-settings');
        container.innerHTML = `
            <div class="note-tpl-settings-head">
                <div>
                    <h3 class="note-tpl-settings-title"><i class="bi bi-stickies" aria-hidden="true"></i> Note Templates</h3>
                    <p class="note-tpl-settings-subtitle">Quick snippets you can insert into consultation notes.</p>
                </div>
                <button type="button" class="btn btn-sm note-tpl-add" data-action="add">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i><span>Add Template</span>
                </button>
            </div>
            <div class="note-tpl-seed-banner" hidden>
                <div class="note-tpl-seed-banner-body">
                    <i class="bi bi-magic" aria-hidden="true"></i>
                    <div>
                        <div class="note-tpl-seed-banner-title">No templates yet</div>
                        <div class="note-tpl-seed-banner-text">We can create a starter set of common templates to get you going.</div>
                    </div>
                </div>
                <div class="note-tpl-seed-banner-actions">
                    <button type="button" class="btn btn-sm btn-primary" data-action="seed">Seed defaults</button>
                    <button type="button" class="btn btn-sm btn-ghost" data-action="seed-dismiss" aria-label="Dismiss">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="note-tpl-list" role="list" aria-label="Note templates"></div>
            <div class="note-tpl-empty-state" hidden>
                <i class="bi bi-stickies" aria-hidden="true"></i>
                <div class="note-tpl-empty-state-title">No templates</div>
                <div class="note-tpl-empty-state-text">Click “Add Template” to create your first one.</div>
            </div>
        `;

        const listEl       = container.querySelector('.note-tpl-list');
        const seedBanner   = container.querySelector('.note-tpl-seed-banner');
        const emptyState   = container.querySelector('.note-tpl-empty-state');
        const addBtn       = container.querySelector('[data-action="add"]');

        let seedDismissed = false;
        try { seedDismissed = localStorage.getItem('note-tpl-seed-dismissed') === '1'; } catch (e) { /* noop */ }

        function render() {
            const list = state.templates || [];
            // Seed banner visibility: empty + not dismissed
            const showSeed = list.length === 0 && !seedDismissed && !state.loading;
            seedBanner.hidden = !showSeed;
            emptyState.hidden = !(list.length === 0 && !state.loading && (seedDismissed || true) === true && true) || showSeed;
            // Simpler: show empty-state only when list is empty AND seed banner not shown
            emptyState.hidden = !(list.length === 0 && !state.loading && !showSeed);

            if (state.loading && list.length === 0) {
                listEl.innerHTML = '<div class="note-tpl-loading"><i class="bi bi-arrow-repeat note-tpl-spin" aria-hidden="true"></i> Loading templates…</div>';
                return;
            }

            listEl.innerHTML = '';
            list.forEach((t) => listEl.appendChild(rowEl(t)));
        }

        function categorySelectHtml(selected) {
            return CATEGORY_OPTIONS.map(opt =>
                `<option value="${escHtml(opt.value)}" ${opt.value === selected ? 'selected' : ''}>${escHtml(opt.label)}</option>`
            ).join('');
        }

        function colorSwatchHtml(selected) {
            return COLOR_OPTIONS.map(opt =>
                `<button type="button" class="note-tpl-color-swatch ${opt.value === selected ? 'is-active' : ''}" data-color="${escHtml(opt.value)}" aria-label="Color ${escHtml(opt.value)}" style="--swatch:${opt.hex}"></button>`
            ).join('');
        }

        function rowEl(t) {
            const row = document.createElement('div');
            row.className = 'note-tpl-item';
            row.setAttribute('role', 'listitem');
            row.setAttribute('data-id', String(t.id));
            row.setAttribute('draggable', 'true');
            const hex = colorHex(t.color);
            row.style.setProperty('--row-accent', hex);

            row.innerHTML = `
                <div class="note-tpl-item-summary">
                    <button type="button" class="note-tpl-handle" aria-label="Drag to reorder" title="Drag to reorder">
                        <i class="bi bi-grip-vertical" aria-hidden="true"></i>
                    </button>
                    <span class="note-tpl-item-swatch" aria-hidden="true"></span>
                    <input type="text" class="note-tpl-item-title" value="${escHtml(t.title || '')}" aria-label="Template title" placeholder="Untitled template">
                    <span class="note-tpl-item-category-chip" aria-hidden="true">${escHtml(categoryLabel(t.category))}</span>
                    <button type="button" class="note-tpl-item-toggle" aria-expanded="false" aria-label="Expand template">
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="note-tpl-item-del" aria-label="Delete template" title="Delete">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="note-tpl-item-editor" hidden>
                    <div class="note-tpl-editor-grid">
                        <label class="note-tpl-field">
                            <span class="note-tpl-field-label">Category</span>
                            <select class="note-tpl-item-category form-select form-select-sm">${categorySelectHtml(t.category || 'general')}</select>
                        </label>
                        <div class="note-tpl-field">
                            <span class="note-tpl-field-label">Color tag</span>
                            <div class="note-tpl-color-row" role="radiogroup" aria-label="Color tag">
                                ${colorSwatchHtml(t.color || 'indigo')}
                            </div>
                        </div>
                    </div>
                    <label class="note-tpl-field note-tpl-field-body">
                        <span class="note-tpl-field-label">Body</span>
                        <textarea class="note-tpl-item-body form-control" rows="5" placeholder="Template body — inserted at cursor when chosen">${escHtml(t.body || '')}</textarea>
                    </label>
                    <div class="note-tpl-editor-foot">
                        <span class="note-tpl-save-state" aria-live="polite"></span>
                        <button type="button" class="btn btn-sm note-tpl-collapse-btn"><i class="bi bi-chevron-up" aria-hidden="true"></i> Collapse</button>
                    </div>
                </div>
            `;

            // ----- wire up interactions ------------------------------------
            const summary    = row.querySelector('.note-tpl-item-summary');
            const titleInput = row.querySelector('.note-tpl-item-title');
            const catChip    = row.querySelector('.note-tpl-item-category-chip');
            const toggleBtn  = row.querySelector('.note-tpl-item-toggle');
            const delBtn     = row.querySelector('.note-tpl-item-del');
            const editor     = row.querySelector('.note-tpl-item-editor');
            const catSelect  = row.querySelector('.note-tpl-item-category');
            const bodyTa     = row.querySelector('.note-tpl-item-body');
            const colorRow   = row.querySelector('.note-tpl-color-row');
            const collapseBtn= row.querySelector('.note-tpl-collapse-btn');
            const saveState  = row.querySelector('.note-tpl-save-state');
            const handle     = row.querySelector('.note-tpl-handle');

            let saveTimer = null;
            let lastSent = { title: t.title || '', body: t.body || '', category: t.category || 'general', color: t.color || 'indigo' };

            function flashSaved() {
                saveState.textContent = 'Saved';
                saveState.classList.add('is-shown');
                setTimeout(() => { saveState.classList.remove('is-shown'); saveState.textContent = ''; }, 1400);
            }
            function flashSaving() {
                saveState.textContent = 'Saving…';
                saveState.classList.add('is-shown');
            }

            function queueSave() {
                const payload = {
                    title: titleInput.value.trim(),
                    body: bodyTa.value,
                    category: catSelect.value,
                    color: row.dataset.color || (t.color || 'indigo')
                };
                if (
                    payload.title === lastSent.title &&
                    payload.body === lastSent.body &&
                    payload.category === lastSent.category &&
                    payload.color === lastSent.color
                ) return;
                clearTimeout(saveTimer);
                flashSaving();
                saveTimer = setTimeout(() => {
                    api('PATCH', `/api/note-templates/${encodeURIComponent(t.id)}`, payload)
                        .then((updated) => {
                            lastSent = Object.assign({}, payload);
                            // Update in-memory copy
                            Object.assign(t, payload, updated || {});
                            catChip.textContent = categoryLabel(t.category);
                            const hx = colorHex(t.color);
                            row.style.setProperty('--row-accent', hx);
                            notifyListeners();
                            flashSaved();
                        })
                        .catch((err) => {
                            saveState.textContent = 'Save failed';
                            toast('error', 'Save failed', err.message || 'Could not save template.');
                        });
                }, 450);
            }

            // Expand / collapse
            function setExpanded(open) {
                editor.hidden = !open;
                toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                row.classList.toggle('is-expanded', open);
                toggleBtn.querySelector('i').className = open ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
            }
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                setExpanded(editor.hidden);
            });
            collapseBtn.addEventListener('click', () => setExpanded(false));

            // Title clickable to toggle (but typing in input does not toggle)
            summary.addEventListener('click', (e) => {
                if (e.target.closest('input, button, select')) return;
                setExpanded(editor.hidden);
            });

            titleInput.addEventListener('input', queueSave);
            titleInput.addEventListener('blur', () => { clearTimeout(saveTimer); queueSave(); });
            bodyTa.addEventListener('input', queueSave);
            bodyTa.addEventListener('blur', () => { clearTimeout(saveTimer); queueSave(); });
            catSelect.addEventListener('change', () => { catChip.textContent = categoryLabel(catSelect.value); queueSave(); });

            // Color swatches
            row.dataset.color = t.color || 'indigo';
            colorRow.addEventListener('click', (e) => {
                const sw = e.target.closest('.note-tpl-color-swatch');
                if (!sw) return;
                const c = sw.getAttribute('data-color');
                row.dataset.color = c;
                colorRow.querySelectorAll('.note-tpl-color-swatch').forEach(el => el.classList.toggle('is-active', el === sw));
                row.style.setProperty('--row-accent', colorHex(c));
                queueSave();
            });

            // Delete
            delBtn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const ok = (typeof window.mkConfirmModal === 'function')
                    ? await window.mkConfirmModal({
                        title: 'Delete template?',
                        message: `“${t.title || 'Untitled'}” will be removed permanently.`,
                        confirmText: 'Delete',
                        confirmClass: 'btn-danger',
                        icon: 'bi-trash'
                    })
                    : window.confirm('Delete this template?');
                if (!ok) return;
                row.classList.add('is-leaving');
                api('DELETE', `/api/note-templates/${encodeURIComponent(t.id)}`)
                    .then(() => {
                        state.templates = (state.templates || []).filter(x => String(x.id) !== String(t.id));
                        notifyListeners();
                        render();
                        toast('success', 'Template deleted', t.title || 'Untitled');
                    })
                    .catch((err) => {
                        row.classList.remove('is-leaving');
                        toast('error', 'Delete failed', err.message || 'Could not delete template.');
                    });
            });

            // Drag & drop reorder ------------------------
            row.addEventListener('dragstart', (e) => {
                row.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
                try { e.dataTransfer.setData('text/plain', String(t.id)); } catch (_) { /* IE */ }
                // Use the handle as drag image if possible
                if (e.dataTransfer.setDragImage) {
                    e.dataTransfer.setDragImage(row, 20, 20);
                }
            });
            row.addEventListener('dragend', () => {
                row.classList.remove('is-dragging');
                listEl.querySelectorAll('.note-tpl-item.is-drop-target').forEach(el => el.classList.remove('is-drop-target', 'drop-above', 'drop-below'));
            });

            // Make handle visually the drag origin (focus is enough; drag is row-level)
            handle.addEventListener('mousedown', () => row.setAttribute('draggable', 'true'));

            return row;
        }

        // ---------- List-level DnD ------------------------------------------
        listEl.addEventListener('dragover', (e) => {
            const dragging = listEl.querySelector('.note-tpl-item.is-dragging');
            if (!dragging) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const target = e.target.closest('.note-tpl-item');
            listEl.querySelectorAll('.note-tpl-item.is-drop-target').forEach(el => el.classList.remove('is-drop-target', 'drop-above', 'drop-below'));
            if (!target || target === dragging) return;
            const rect = target.getBoundingClientRect();
            const before = (e.clientY - rect.top) < rect.height / 2;
            target.classList.add('is-drop-target', before ? 'drop-above' : 'drop-below');
        });

        listEl.addEventListener('drop', (e) => {
            const dragging = listEl.querySelector('.note-tpl-item.is-dragging');
            if (!dragging) return;
            e.preventDefault();
            const target = e.target.closest('.note-tpl-item');
            if (!target || target === dragging) {
                listEl.querySelectorAll('.note-tpl-item.is-drop-target').forEach(el => el.classList.remove('is-drop-target', 'drop-above', 'drop-below'));
                return;
            }
            const rect = target.getBoundingClientRect();
            const before = (e.clientY - rect.top) < rect.height / 2;
            if (before) listEl.insertBefore(dragging, target);
            else        listEl.insertBefore(dragging, target.nextSibling);

            listEl.querySelectorAll('.note-tpl-item.is-drop-target').forEach(el => el.classList.remove('is-drop-target', 'drop-above', 'drop-below'));

            // Persist new order
            const order = Array.from(listEl.querySelectorAll('.note-tpl-item')).map(el => el.getAttribute('data-id'));
            // Update in-memory positions
            const byId = new Map((state.templates || []).map(t => [String(t.id), t]));
            state.templates = order.map((id, idx) => {
                const t = byId.get(String(id));
                if (t) t.position = idx;
                return t;
            }).filter(Boolean);
            notifyListeners();

            api('POST', '/api/note-templates/reorder', { order })
                .catch((err) => {
                    toast('error', 'Reorder failed', err.message || 'Could not save the new order.');
                    // Re-render from server to recover
                    loadTemplates(true).then(render);
                });
        });

        // ---------- Top-level actions ---------------------------------------
        addBtn.addEventListener('click', () => {
            const payload = {
                title: 'New template',
                body: '',
                category: 'general',
                color: 'indigo'
            };
            addBtn.disabled = true;
            api('POST', '/api/note-templates', payload)
                .then((created) => {
                    const item = created && created.id ? created : Object.assign({ id: 'tmp-' + Date.now() }, payload);
                    state.templates = (state.templates || []).concat([item]);
                    notifyListeners();
                    render();
                    // Focus title of new row
                    const newRow = listEl.querySelector(`.note-tpl-item[data-id="${CSS.escape(String(item.id))}"]`);
                    if (newRow) {
                        newRow.querySelector('.note-tpl-item-toggle')?.click();
                        const ti = newRow.querySelector('.note-tpl-item-title');
                        if (ti) { ti.focus(); ti.select(); }
                        newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                })
                .catch((err) => {
                    toast('error', 'Create failed', err.message || 'Could not create template.');
                })
                .finally(() => { addBtn.disabled = false; });
        });

        seedBanner.addEventListener('click', (e) => {
            const action = e.target.closest('[data-action]')?.getAttribute('data-action');
            if (!action) return;
            if (action === 'seed-dismiss') {
                seedDismissed = true;
                try { localStorage.setItem('note-tpl-seed-dismissed', '1'); } catch (_) { /* noop */ }
                seedBanner.hidden = true;
                render();
                return;
            }
            if (action === 'seed') {
                const btn = e.target.closest('button');
                if (btn) btn.disabled = true;
                api('POST', '/api/note-templates/seed-defaults')
                    .then(() => loadTemplates(true))
                    .then(() => {
                        toast('success', 'Templates added', 'Default templates were added to your library.');
                        seedBanner.hidden = true;
                        render();
                    })
                    .catch((err) => {
                        toast('error', 'Seed failed', err.message || 'Could not seed default templates.');
                    })
                    .finally(() => { if (btn) btn.disabled = false; });
            }
        });

        // Listen for external state changes & initial load
        const listener = () => render();
        state.listeners.add(listener);

        loadTemplates()
            .then(() => render())
            .catch(() => render());

        return {
            refresh: () => loadTemplates(true).then(render),
            destroy() {
                state.listeners.delete(listener);
                container.innerHTML = '';
                container.classList.remove('note-tpl-settings');
            }
        };
    }

    // ---------- public surface --------------------------------------------

    window.noteTemplates = {
        mountDropdown,
        mountSettings,
        refresh() { return loadTemplates(true); },
        _state: state  // for debugging — not part of public API
    };
})();
