/**
 * todo-drawer.js — Multi-list to-do drawer.
 *
 * Public API (window):
 *   openTodoDrawer()  — slide in / scroll into view
 *   closeTodoDrawer() — slide out
 *   window.todoDrawer = {
 *       open, close, refreshLists, reloadCurrentList, focusList(id)
 *   }
 *
 * Endpoints (assumed available):
 *   GET  /api/todo-lists
 *   POST /api/todo-lists                      { name, color, icon }
 *   PATCH /api/todo-lists/:id                 { name?, color?, icon? }
 *   POST /api/todo-lists/:id/archive
 *   POST /api/todo-lists/reorder              { ids: [..] }
 *   GET  /api/todos?list_id=N
 *   POST /api/todos                           { list_id, title, ... }
 *   PATCH /api/todos/:id
 *   POST /api/todos/:id/done
 *   POST /api/todos/:id/reopen
 *   POST /api/todos/:id/snooze                { minutes }
 *   DELETE /api/todos/:id
 *   GET  /api/search/palette?q=&scope=patients
 */
(function () {
    'use strict';

    // ---------------------------------------------------------------- helpers
    const JSON_HEADERS = {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        Accept: 'application/json'
    };

    function api(url, opts) {
        opts = opts || {};
        opts.credentials = 'same-origin';
        opts.headers = Object.assign({}, JSON_HEADERS, opts.headers || {});
        if (opts.body && typeof opts.body !== 'string') {
            opts.body = JSON.stringify(opts.body);
        }
        return fetch(url, opts).then((r) => {
            if (!r.ok) {
                return r.text().then((t) => {
                    const err = new Error('HTTP ' + r.status);
                    err.status = r.status;
                    err.body = t;
                    throw err;
                });
            }
            const ct = r.headers.get('content-type') || '';
            if (ct.indexOf('application/json') !== -1) return r.json();
            return r.text();
        });
    }

    function $(sel, root) { return (root || document).querySelector(sel); }
    function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function debounce(fn, wait) {
        let t;
        return function () {
            const args = arguments, ctx = this;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(ctx, args), wait);
        };
    }

    function fmtDue(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        if (isNaN(d)) return '';
        const now = new Date();
        const sameDay = d.toDateString() === now.toDateString();
        const tom = new Date(now); tom.setDate(tom.getDate() + 1);
        const isTom = d.toDateString() === tom.toDateString();
        const hh = String(d.getHours()).padStart(2, '0');
        const mm = String(d.getMinutes()).padStart(2, '0');
        if (sameDay) return 'Today ' + hh + ':' + mm;
        if (isTom) return 'Tomorrow ' + hh + ':' + mm;
        const month = d.toLocaleString(undefined, { month: 'short' });
        return month + ' ' + d.getDate() + ' · ' + hh + ':' + mm;
    }

    function isOverdue(iso) {
        if (!iso) return false;
        const d = new Date(iso);
        if (isNaN(d)) return false;
        return d.getTime() < Date.now();
    }

    // ------------------------------------------------------------------ state
    const state = {
        loaded: false,
        lists: [],            // [{id,name,icon,color,open_count,total_count}]
        currentListId: null,
        tasks: [],            // tasks for current list
        filter: 'open',       // 'open' | 'done' | 'all'
        editingTaskId: null,
        patientSearchAbort: null
    };

    // --------------------------------------------------- DOM refs (cached)
    let drawer, backdrop, rail, body, rows, empty, loading,
        progressTitle, progressSub, progressBadge, progressFill, progressCard,
        quickForm, quickInput,
        fab, fullModal, fullForm,
        listPopover, snoozeMenu;

    function cacheRefs() {
        drawer        = document.getElementById('todoDrawer');
        backdrop      = document.getElementById('todoDrawerBackdrop');
        rail          = document.getElementById('todoListRail');
        body          = document.getElementById('todoListBody');
        rows          = document.getElementById('todoRows');
        empty         = document.getElementById('todoEmpty');
        loading       = document.getElementById('todoLoading');
        progressCard  = document.getElementById('todoProgressCard');
        progressTitle = document.getElementById('todoProgressTitle');
        progressSub   = document.getElementById('todoProgressSub');
        progressBadge = document.getElementById('todoProgressBadge');
        progressFill  = document.getElementById('todoProgressFill');
        quickForm     = document.getElementById('todoQuickAdd');
        quickInput    = document.getElementById('todoQuickAddInput');
        fab           = document.getElementById('todoFullAddFab');
        fullModal     = document.getElementById('todoFullModal');
        fullForm      = document.getElementById('todoFullForm');
        listPopover   = document.getElementById('todoListPopover');
    }

    // ----------------------------------------------------------- open / close
    function open() {
        if (!drawer) cacheRefs();
        if (!drawer) return;
        drawer.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
        backdrop.hidden = false;
        // next frame so transition triggers
        requestAnimationFrame(() => backdrop.classList.add('is-visible'));
        document.body.classList.add('td-no-scroll');
        if (!state.loaded) {
            loadLists().then(() => {
                if (state.lists.length) {
                    selectList(state.lists[0].id);
                }
                state.loaded = true;
            }).catch(handleError);
        } else if (state.currentListId) {
            // refresh tasks for current list silently
            loadTasks(state.currentListId).catch(handleError);
        }
        // focus input after slide
        setTimeout(() => { try { quickInput && quickInput.focus(); } catch (e) { /* */ } }, 320);
    }

    function close() {
        if (!drawer) return;
        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
        backdrop.classList.remove('is-visible');
        setTimeout(() => { backdrop.hidden = true; }, 250);
        document.body.classList.remove('td-no-scroll');
        hidePopover();
        closeFullModal();
    }

    function handleError(e) {
        // soft toast — there's no global toast hook spec'd here so we use alert modal
        const msg = (e && e.body) || (e && e.message) || 'Something went wrong.';
        if (typeof window.mkAlertModal === 'function') {
            window.mkAlertModal({ title: 'Error', message: msg });
        } else {
            console.error('[todo-drawer]', e);
        }
    }

    // ------------------------------------------------------ lists rendering
    function loadLists() {
        return api('/api/todo-lists').then((res) => {
            state.lists = Array.isArray(res) ? res : (res && res.data) || [];
            renderRail();
            populateListSelect();
            return state.lists;
        });
    }

    function renderRail() {
        rail.innerHTML = '';
        const tpl = document.getElementById('tpl-td-list-chip');
        state.lists.forEach((l) => {
            const chip = tpl.content.firstElementChild.cloneNode(true);
            chip.dataset.listId = String(l.id);
            chip.style.setProperty('--list-c', colorVar(l.color));
            const iconEl = chip.querySelector('.td-list-icon');
            iconEl.classList.add(l.icon || 'bi-list-task');
            chip.querySelector('.td-list-name').textContent = l.name || 'List';
            const count = chip.querySelector('.td-list-count');
            const openCount = (l.open_count != null) ? l.open_count : 0;
            count.textContent = openCount > 0 ? String(openCount) : '';
            if (openCount === 0) count.classList.add('is-empty');
            if (state.currentListId === l.id) {
                chip.classList.add('is-active');
                chip.setAttribute('aria-selected', 'true');
            }
            // click → select
            chip.addEventListener('click', () => selectList(l.id));
            // right-click / long-press → context popover
            chip.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                openPopover(chip, l);
            });
            attachLongPress(chip, () => openPopover(chip, l));
            rail.appendChild(chip);
        });
        // trailing "+ New list" chip
        const add = document.createElement('button');
        add.type = 'button';
        add.className = 'td-list-chip td-list-add';
        add.innerHTML =
            '<i class="bi bi-plus-lg" aria-hidden="true"></i><span>New list</span>';
        add.addEventListener('click', openNewListForm);
        rail.appendChild(add);
    }

    function colorVar(c) {
        const allowed = ['indigo', 'emerald', 'rose', 'slate', 'amber', 'ocean'];
        const k = (c && allowed.indexOf(c) !== -1) ? c : 'indigo';
        return 'var(--palette-' + k + ')';
    }

    function selectList(id) {
        state.currentListId = id;
        const list = state.lists.find((l) => l.id === id);
        if (progressCard && list) {
            progressCard.style.setProperty('--list-c', colorVar(list.color));
        }
        $$('.td-list-chip', rail).forEach((c) => {
            const active = String(c.dataset.listId) === String(id);
            c.classList.toggle('is-active', active);
            c.setAttribute('aria-selected', active ? 'true' : 'false');
            if (active) {
                try { c.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' }); } catch (e) { /* */ }
            }
        });
        loadTasks(id).catch(handleError);
    }

    // ------------------------------------------------------ tasks rendering
    function loadTasks(listId) {
        loading.hidden = false;
        rows.innerHTML = '';
        empty.hidden = true;
        return api('/api/todos?list_id=' + encodeURIComponent(listId)).then((res) => {
            loading.hidden = true;
            state.tasks = Array.isArray(res) ? res : (res && res.data) || [];
            renderTasks();
            updateProgress();
        }).catch((e) => {
            loading.hidden = true;
            throw e;
        });
    }

    function renderTasks() {
        rows.innerHTML = '';
        const tpl = document.getElementById('tpl-td-row');
        const visible = filteredTasks();
        if (visible.length === 0) {
            empty.hidden = false;
            return;
        }
        empty.hidden = true;
        const frag = document.createDocumentFragment();
        visible.forEach((t) => frag.appendChild(buildRow(tpl, t)));
        rows.appendChild(frag);
    }

    function filteredTasks() {
        if (state.filter === 'open') return state.tasks.filter((t) => !t.completed_at);
        if (state.filter === 'done') return state.tasks.filter((t) => !!t.completed_at);
        return state.tasks.slice();
    }

    function buildRow(tpl, t) {
        const row = tpl.content.firstElementChild.cloneNode(true);
        row.dataset.taskId = String(t.id);
        row.dataset.status = t.completed_at ? 'done' : 'open';
        if (t.priority) row.dataset.priority = t.priority;

        const list = state.lists.find((l) => l.id === t.list_id) || {};
        row.style.setProperty('--list-c', colorVar(list.color));

        row.querySelector('.td-row-title').textContent = t.title || '(untitled)';

        const dueWrap = row.querySelector('.td-row-due');
        if (t.due_at) {
            dueWrap.hidden = false;
            dueWrap.querySelector('.td-row-due-text').textContent = fmtDue(t.due_at);
            if (isOverdue(t.due_at) && !t.completed_at) dueWrap.classList.add('is-overdue');
        }

        const prio = row.querySelector('.td-row-priority');
        if (t.priority && t.priority !== 'normal') {
            prio.hidden = false;
            prio.textContent = t.priority;
            prio.classList.add('is-' + t.priority);
        }

        const pat = row.querySelector('.td-row-patient');
        if (t.patient_name) {
            pat.hidden = false;
            pat.querySelector('.td-row-patient-name').textContent = t.patient_name;
        }

        // checkbox
        const chk = row.querySelector('.td-check');
        if (t.completed_at) chk.classList.add('is-checked');
        chk.addEventListener('click', () => toggleDone(t.id));

        // actions
        row.querySelector('[data-act="edit"]').addEventListener('click', () => openFullModal(t));
        row.querySelector('[data-act="delete"]').addEventListener('click', () => deleteTask(t.id));
        row.querySelector('[data-act="snooze"]').addEventListener('click', (e) => openSnoozeMenu(e.currentTarget, t.id));

        return row;
    }

    // ------------------------------------------------- progress meter copy
    const PROGRESS_COPY = [
        { min: 0,   max: 24,  title: "Let's go!" },
        { min: 25,  max: 49,  title: 'Nice start' },
        { min: 50,  max: 74,  title: 'Keep it up!' },
        { min: 75,  max: 99,  title: 'Almost there!' },
        { min: 100, max: 100, title: 'All done!' }
    ];

    function updateProgress() {
        const total = state.tasks.length;
        const done = state.tasks.filter((t) => !!t.completed_at).length;
        const pct = total === 0 ? 0 : Math.round((done / total) * 100);
        const copy = PROGRESS_COPY.find((c) => pct >= c.min && pct <= c.max) || PROGRESS_COPY[0];
        progressTitle.textContent = pct === 100 ? copy.title + ' 🎉' : copy.title;
        progressSub.textContent = done + ' of ' + total + ' completed';
        progressBadge.textContent = pct + '%';
        progressFill.style.width = pct + '%';
        const bar = progressFill.parentElement;
        if (bar && bar.setAttribute) bar.setAttribute('aria-valuenow', String(pct));

        // update list counts in the rail
        const list = state.lists.find((l) => l.id === state.currentListId);
        if (list) {
            list.open_count = total - done;
            list.total_count = total;
            const chip = rail.querySelector('.td-list-chip[data-list-id="' + list.id + '"]');
            if (chip) {
                const c = chip.querySelector('.td-list-count');
                c.textContent = list.open_count > 0 ? String(list.open_count) : '';
                c.classList.toggle('is-empty', list.open_count === 0);
            }
        }
    }

    // -------------------------------------------------------- quick add
    function bindQuickAdd() {
        quickForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const title = (quickInput.value || '').trim();
            if (!title || !state.currentListId) return;
            // optimistic
            const tempId = 'tmp_' + Date.now();
            const optimistic = {
                id: tempId,
                list_id: state.currentListId,
                title: title,
                completed_at: null,
                priority: 'normal',
                _optimistic: true
            };
            state.tasks.unshift(optimistic);
            renderTasks();
            updateProgress();
            quickInput.value = '';

            api('/api/todos', {
                method: 'POST',
                body: { title: title, list_id: state.currentListId }
            }).then((created) => {
                // replace temp with real
                const idx = state.tasks.findIndex((t) => t.id === tempId);
                if (idx !== -1) {
                    state.tasks[idx] = created || Object.assign(optimistic, { _optimistic: false });
                    state.tasks[idx]._optimistic = false;
                }
                renderTasks();
                updateProgress();
            }).catch((err) => {
                // rollback
                state.tasks = state.tasks.filter((t) => t.id !== tempId);
                renderTasks();
                updateProgress();
                handleError(err);
            });
        });
    }

    // -------------------------------------------------------- toggle done
    function toggleDone(id) {
        const t = state.tasks.find((x) => String(x.id) === String(id));
        if (!t) return;
        const wasDone = !!t.completed_at;
        // optimistic
        t.completed_at = wasDone ? null : new Date().toISOString();
        const row = rows.querySelector('.td-row[data-task-id="' + id + '"]');
        if (row) {
            row.classList.add('is-toggling');
            row.dataset.status = wasDone ? 'open' : 'done';
            const chk = row.querySelector('.td-check');
            chk.classList.toggle('is-checked', !wasDone);
            setTimeout(() => row.classList.remove('is-toggling'), 350);
        }
        updateProgress();
        // possibly hide if filter is 'open' or 'done'
        if (state.filter !== 'all') {
            setTimeout(renderTasks, 360);
        }
        const endpoint = wasDone
            ? '/api/todos/' + id + '/reopen'
            : '/api/todos/' + id + '/done';
        api(endpoint, { method: 'POST' }).catch((err) => {
            // rollback
            t.completed_at = wasDone ? new Date().toISOString() : null;
            renderTasks();
            updateProgress();
            handleError(err);
        });
    }

    // -------------------------------------------------------- delete task
    function deleteTask(id) {
        const t = state.tasks.find((x) => String(x.id) === String(id));
        if (!t) return;
        const doDelete = function () {
            // optimistic removal
            state.tasks = state.tasks.filter((x) => String(x.id) !== String(id));
            renderTasks();
            updateProgress();
            api('/api/todos/' + id, { method: 'DELETE' }).catch((err) => {
                state.tasks.push(t);
                renderTasks();
                updateProgress();
                handleError(err);
            });
        };
        if (typeof window.mkConfirmModal === 'function') {
            window.mkConfirmModal({
                title: 'Delete task',
                message: 'Delete "' + (t.title || 'this task') + '"? This cannot be undone.',
                okText: 'Delete',
                okVariant: 'danger',
                cancelText: 'Cancel'
            }).then((ok) => { if (ok) doDelete(); });
        } else if (window.confirm('Delete this task?')) {
            doDelete();
        }
    }

    // -------------------------------------------------------- snooze
    function openSnoozeMenu(anchor, taskId) {
        // close existing
        if (snoozeMenu) { snoozeMenu.remove(); snoozeMenu = null; }
        const tpl = document.getElementById('tpl-td-snooze');
        snoozeMenu = tpl.content.firstElementChild.cloneNode(true);
        snoozeMenu.style.position = 'absolute';
        document.body.appendChild(snoozeMenu);
        const r = anchor.getBoundingClientRect();
        const w = snoozeMenu.offsetWidth || 180;
        let left = r.right - w;
        if (left < 8) left = 8;
        snoozeMenu.style.left = left + 'px';
        snoozeMenu.style.top = (r.bottom + 6) + 'px';
        snoozeMenu.classList.add('is-visible');

        function onClick(ev) {
            const btn = ev.target.closest('[data-snooze]');
            if (!btn) return;
            const minutes = parseInt(btn.dataset.snooze, 10);
            api('/api/todos/' + taskId + '/snooze', {
                method: 'POST',
                body: { minutes: minutes }
            }).then(() => loadTasks(state.currentListId)).catch(handleError);
            closeSnooze();
        }
        function closeSnooze() {
            if (!snoozeMenu) return;
            snoozeMenu.removeEventListener('click', onClick);
            document.removeEventListener('mousedown', onOutside, true);
            snoozeMenu.remove();
            snoozeMenu = null;
        }
        function onOutside(ev) {
            if (snoozeMenu && !snoozeMenu.contains(ev.target) && ev.target !== anchor) {
                closeSnooze();
            }
        }
        snoozeMenu.addEventListener('click', onClick);
        setTimeout(() => document.addEventListener('mousedown', onOutside, true), 0);
    }

    // -------------------------------------------------------- full add/edit modal
    function populateListSelect() {
        const sel = document.getElementById('todoFmList');
        if (!sel) return;
        sel.innerHTML = '';
        state.lists.forEach((l) => {
            const opt = document.createElement('option');
            opt.value = String(l.id);
            opt.textContent = l.name || 'List';
            sel.appendChild(opt);
        });
    }

    function openFullModal(task) {
        if (!fullModal) cacheRefs();
        if (!fullModal) return;
        populateListSelect();
        const isEdit = !!(task && task.id && !String(task.id).startsWith('tmp_'));
        state.editingTaskId = isEdit ? task.id : null;
        $('#todoFullModalTitle').textContent = isEdit ? 'Edit task' : 'New task';
        $('[data-submit-label]', fullForm).textContent = isEdit ? 'Save changes' : 'Add task';
        fullForm.reset();
        fullForm.elements.id.value = isEdit ? task.id : '';
        fullForm.elements.list_id.value = String((task && task.list_id) || state.currentListId || (state.lists[0] && state.lists[0].id) || '');
        fullForm.elements.title.value = (task && task.title) || '';
        fullForm.elements.description.value = (task && task.description) || '';
        fullForm.elements.due_at.value = (task && task.due_at) ? toLocalInput(task.due_at) : '';
        fullForm.elements.remind_before.value = (task && task.remind_before != null) ? String(task.remind_before) : '';
        fullForm.elements.priority.value = (task && task.priority) || 'normal';
        fullForm.elements.patient_id.value = (task && task.patient_id) ? String(task.patient_id) : '';
        $('#todoFmPatient').value = (task && task.patient_name) || '';

        fullModal.hidden = false;
        requestAnimationFrame(() => fullModal.classList.add('is-open'));
        setTimeout(() => { try { $('#todoFmTitle').focus(); } catch (e) { /* */ } }, 80);
    }

    function closeFullModal() {
        if (!fullModal) return;
        fullModal.classList.remove('is-open');
        setTimeout(() => { fullModal.hidden = true; }, 200);
        state.editingTaskId = null;
        const r = $('#todoFmPatientResults'); if (r) { r.innerHTML = ''; r.hidden = true; }
    }

    function toLocalInput(iso) {
        const d = new Date(iso);
        if (isNaN(d)) return '';
        const pad = (n) => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
            + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function bindFullModal() {
        if (!fullModal) return;
        $$('[data-close]', fullModal).forEach((el) => el.addEventListener('click', closeFullModal));
        fullForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(fullForm);
            const payload = {
                list_id: parseInt(fd.get('list_id'), 10) || null,
                title: (fd.get('title') || '').trim(),
                description: (fd.get('description') || '').trim() || null,
                due_at: fd.get('due_at') || null,
                remind_before: fd.get('remind_before') ? parseInt(fd.get('remind_before'), 10) : null,
                priority: fd.get('priority') || 'normal',
                patient_id: fd.get('patient_id') ? parseInt(fd.get('patient_id'), 10) : null
            };
            if (!payload.title || !payload.list_id) return;
            const isEdit = !!state.editingTaskId;
            const req = isEdit
                ? api('/api/todos/' + state.editingTaskId, { method: 'PATCH', body: payload })
                : api('/api/todos', { method: 'POST', body: payload });
            req.then(() => {
                closeFullModal();
                if (payload.list_id !== state.currentListId) {
                    selectList(payload.list_id);
                } else {
                    loadTasks(state.currentListId);
                }
            }).catch(handleError);
        });

        // patient typeahead
        const patInput = $('#todoFmPatient');
        const patHidden = $('#todoFmPatientId');
        const patResults = $('#todoFmPatientResults');
        const onSearch = debounce(function () {
            const q = (patInput.value || '').trim();
            patHidden.value = ''; // require explicit pick
            if (q.length < 2) { patResults.hidden = true; patResults.innerHTML = ''; return; }
            if (state.patientSearchAbort) {
                try { state.patientSearchAbort.abort(); } catch (e) { /* */ }
            }
            const ctl = new AbortController();
            state.patientSearchAbort = ctl;
            fetch('/api/search/palette?scope=patients&q=' + encodeURIComponent(q), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: ctl.signal
            }).then((r) => r.json()).then((res) => {
                const items = (res && (res.results || res.data || res)) || [];
                patResults.innerHTML = '';
                if (!items.length) {
                    patResults.hidden = true;
                    return;
                }
                items.slice(0, 8).forEach((it) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'td-typeahead-item';
                    const sub = it.phone || it.subtitle || '';
                    b.innerHTML = '<strong>' + escapeHtml(it.name || it.title || '') + '</strong>'
                        + (sub ? '<span>' + escapeHtml(sub) + '</span>' : '');
                    b.addEventListener('click', () => {
                        patInput.value = it.name || it.title || '';
                        patHidden.value = String(it.id || it.patient_id || '');
                        patResults.hidden = true;
                    });
                    patResults.appendChild(b);
                });
                patResults.hidden = false;
            }).catch(() => { /* aborted or net error */ });
        }, 220);
        patInput.addEventListener('input', onSearch);
        document.addEventListener('mousedown', (e) => {
            if (!patResults.contains(e.target) && e.target !== patInput) {
                patResults.hidden = true;
            }
        });
    }

    // -------------------------------------------------------- new-list inline
    function openNewListForm() {
        // already open?
        if (rail.querySelector('.td-new-list')) {
            rail.querySelector('.td-new-list-name').focus();
            return;
        }
        const tpl = document.getElementById('tpl-td-new-list');
        const form = tpl.content.firstElementChild.cloneNode(true);
        const addChip = rail.querySelector('.td-list-add');
        rail.insertBefore(form, addChip);

        const dots = $$('.td-dot', form);
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                dots.forEach((d) => { d.classList.remove('is-active'); d.setAttribute('aria-checked', 'false'); });
                dot.classList.add('is-active');
                dot.setAttribute('aria-checked', 'true');
            });
        });

        form.querySelector('[data-cancel]').addEventListener('click', () => form.remove());
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = (form.querySelector('[name="name"]').value || '').trim();
            const colorBtn = form.querySelector('.td-dot.is-active');
            const color = colorBtn ? colorBtn.dataset.color : 'indigo';
            const icon = form.querySelector('[name="icon"]').value || 'bi-list-task';
            if (!name) return;
            api('/api/todo-lists', {
                method: 'POST',
                body: { name: name, color: color, icon: icon }
            }).then((created) => {
                form.remove();
                const newList = created && created.id ? created
                    : { id: Date.now(), name: name, color: color, icon: icon, open_count: 0, total_count: 0 };
                state.lists.push(newList);
                renderRail();
                populateListSelect();
                selectList(newList.id);
            }).catch(handleError);
        });
        form.querySelector('.td-new-list-name').focus();
    }

    // -------------------------------------------------------- list popover
    let popoverList = null;
    function openPopover(chip, list) {
        popoverList = list;
        const r = chip.getBoundingClientRect();
        listPopover.hidden = false;
        listPopover.style.position = 'fixed';
        let left = r.left;
        const w = listPopover.offsetWidth || 200;
        if (left + w > window.innerWidth - 8) left = window.innerWidth - w - 8;
        listPopover.style.left = Math.max(8, left) + 'px';
        listPopover.style.top = (r.bottom + 6) + 'px';
        requestAnimationFrame(() => listPopover.classList.add('is-visible'));
    }
    function hidePopover() {
        if (!listPopover) return;
        listPopover.classList.remove('is-visible');
        listPopover.hidden = true;
        popoverList = null;
    }

    function bindPopover() {
        if (!listPopover) return;
        listPopover.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-act]');
            if (!btn || !popoverList) return;
            const act = btn.dataset.act;
            const list = popoverList;
            hidePopover();
            if (act === 'rename') {
                const next = window.prompt('Rename list', list.name || '');
                if (next && next.trim() && next.trim() !== list.name) {
                    api('/api/todo-lists/' + list.id, {
                        method: 'PATCH', body: { name: next.trim() }
                    }).then(() => { list.name = next.trim(); renderRail(); populateListSelect(); }).catch(handleError);
                }
            } else if (act === 'color') {
                cycleColor(list);
            } else if (act === 'up' || act === 'down') {
                moveList(list.id, act === 'up' ? -1 : 1);
            } else if (act === 'archive') {
                if (typeof window.mkConfirmModal === 'function') {
                    window.mkConfirmModal({
                        title: 'Archive list',
                        message: 'Archive "' + (list.name || 'this list') + '"? You can restore it later.',
                        okText: 'Archive', okVariant: 'warning'
                    }).then((ok) => { if (ok) archiveList(list.id); });
                } else if (window.confirm('Archive list?')) {
                    archiveList(list.id);
                }
            }
        });
        document.addEventListener('mousedown', (e) => {
            if (!listPopover.hidden && !listPopover.contains(e.target)) hidePopover();
        });
        window.addEventListener('scroll', hidePopover, true);
        window.addEventListener('resize', hidePopover);
    }

    function cycleColor(list) {
        const order = ['indigo', 'emerald', 'rose', 'amber', 'ocean', 'slate'];
        const idx = order.indexOf(list.color || 'indigo');
        const next = order[(idx + 1) % order.length];
        api('/api/todo-lists/' + list.id, {
            method: 'PATCH', body: { color: next }
        }).then(() => {
            list.color = next;
            renderRail();
            if (state.currentListId === list.id) {
                progressCard.style.setProperty('--list-c', colorVar(next));
            }
        }).catch(handleError);
    }

    function moveList(id, delta) {
        const idx = state.lists.findIndex((l) => l.id === id);
        const target = idx + delta;
        if (idx === -1 || target < 0 || target >= state.lists.length) return;
        const arr = state.lists.slice();
        const [item] = arr.splice(idx, 1);
        arr.splice(target, 0, item);
        state.lists = arr;
        renderRail();
        api('/api/todo-lists/reorder', {
            method: 'POST', body: { ids: arr.map((l) => l.id) }
        }).catch((err) => { handleError(err); loadLists(); });
    }

    function archiveList(id) {
        api('/api/todo-lists/' + id + '/archive', { method: 'POST' }).then(() => {
            state.lists = state.lists.filter((l) => l.id !== id);
            renderRail();
            populateListSelect();
            if (state.currentListId === id && state.lists[0]) {
                selectList(state.lists[0].id);
            } else if (!state.lists.length) {
                state.currentListId = null;
                state.tasks = [];
                renderTasks();
                updateProgress();
            }
        }).catch(handleError);
    }

    // -------------------------------------------------------- long-press
    function attachLongPress(el, onTrigger) {
        let timer = null;
        let triggered = false;
        let startX = 0, startY = 0;
        const THRESHOLD = 500;
        const MOVE_TOL = 10;
        el.addEventListener('touchstart', (e) => {
            triggered = false;
            const t = e.touches[0];
            startX = t.clientX; startY = t.clientY;
            timer = setTimeout(() => {
                triggered = true;
                try { if (navigator.vibrate) navigator.vibrate(15); } catch (err) { /* */ }
                onTrigger();
            }, THRESHOLD);
        }, { passive: true });
        el.addEventListener('touchmove', (e) => {
            const t = e.touches[0];
            if (Math.abs(t.clientX - startX) > MOVE_TOL || Math.abs(t.clientY - startY) > MOVE_TOL) {
                clearTimeout(timer); timer = null;
            }
        }, { passive: true });
        el.addEventListener('touchend', (e) => {
            clearTimeout(timer); timer = null;
            if (triggered) e.preventDefault();
        });
        el.addEventListener('touchcancel', () => { clearTimeout(timer); timer = null; });
    }

    // -------------------------------------------------------- filter chips
    function bindFilters() {
        $$('.td-filter', drawer).forEach((btn) => {
            btn.addEventListener('click', () => {
                state.filter = btn.dataset.filter;
                $$('.td-filter', drawer).forEach((b) => {
                    const active = b === btn;
                    b.classList.toggle('is-active', active);
                    b.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                renderTasks();
            });
        });
    }

    // -------------------------------------------------------- bottom-sheet drag
    function bindDrag() {
        const handle = drawer.querySelector('.td-drag-handle');
        if (!handle) return;
        let startY = 0;
        let dragging = false;
        let currentTranslate = 0;
        handle.addEventListener('touchstart', (e) => {
            if (window.innerWidth > 575.98) return;
            dragging = true;
            startY = e.touches[0].clientY;
            drawer.style.transition = 'none';
        }, { passive: true });
        handle.addEventListener('touchmove', (e) => {
            if (!dragging) return;
            const dy = e.touches[0].clientY - startY;
            if (dy < 0) return;
            currentTranslate = dy;
            drawer.style.transform = 'translateY(' + dy + 'px)';
        }, { passive: true });
        handle.addEventListener('touchend', () => {
            if (!dragging) return;
            dragging = false;
            drawer.style.transition = '';
            if (currentTranslate > 120) {
                drawer.style.transform = '';
                close();
            } else {
                drawer.style.transform = '';
            }
            currentTranslate = 0;
        });
    }

    // -------------------------------------------------------- header button
    // Mount a "To-Do" trigger next to the other v11 header chips
    // (cmdk, keyboard-help, palette). Hidden on mobile via CSS.
    function ensureHeaderButton() {
        if (document.getElementById('todoDrawerToggle')) return;
        var anchor =
            document.getElementById('cmdkToggle') ||
            document.getElementById('kbdHelpToggle') ||
            document.getElementById('paletteToggle') ||
            document.querySelector('label.switch[for="themeToggleInput"]');
        if (!anchor || !anchor.parentNode) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'todoDrawerToggle';
        btn.className = 'todo-drawer-toggle';
        btn.setAttribute('aria-label', 'Open to-do drawer');
        btn.setAttribute('title', 'To-Do (T)');
        btn.innerHTML =
            '<i class="bi bi-check2-square" aria-hidden="true"></i>' +
            '<span class="todo-drawer-toggle__badge" id="todoDrawerToggleBadge" hidden>0</span>';
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            open();
        });
        anchor.parentNode.insertBefore(btn, anchor);

        // Light count fetch on init so the badge reflects open-task count.
        refreshHeaderBadge();
        // Refresh every 60s while document is visible.
        setInterval(function () {
            if (document.visibilityState === 'visible') refreshHeaderBadge();
        }, 60 * 1000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') refreshHeaderBadge();
        });
    }

    function refreshHeaderBadge() {
        var badge = document.getElementById('todoDrawerToggleBadge');
        if (!badge) return;
        fetch('/api/todos/counts', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || !data.success) return;
                var n = +(data.open || 0);
                if (n > 0) {
                    badge.hidden = false;
                    badge.textContent = n > 99 ? '99+' : String(n);
                } else {
                    badge.hidden = true;
                }
            })
            .catch(function () {});
    }

    // Expose so other modules (e.g. drawer close) can ping the badge.
    window.__refreshTodoHeaderBadge = refreshHeaderBadge;

    // -------------------------------------------------------- init
    function init() {
        cacheRefs();
        if (!drawer) return;

        ensureHeaderButton();

        // close button
        $('#todoDrawerClose', drawer).addEventListener('click', close);
        // backdrop click — but only if full modal isn't open
        backdrop.addEventListener('click', () => {
            if (!fullModal.hidden && fullModal.classList.contains('is-open')) return;
            close();
        });
        // ESC
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!drawer.classList.contains('open')) return;
            if (!fullModal.hidden && fullModal.classList.contains('is-open')) {
                closeFullModal();
                return;
            }
            close();
        });

        // fab
        fab.addEventListener('click', () => openFullModal(null));

        bindQuickAdd();
        bindFilters();
        bindFullModal();
        bindPopover();
        bindDrag();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // -------------------------------------------------------- public API
    window.openTodoDrawer  = open;
    window.closeTodoDrawer = close;
    window.todoDrawer = {
        open: open,
        close: close,
        refreshLists: function () { return loadLists(); },
        reloadCurrentList: function () {
            if (state.currentListId) return loadTasks(state.currentListId);
            return Promise.resolve();
        },
        focusList: function (id) { selectList(id); }
    };
})();
