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
 *   GET  /api/todos/due-check                  -> { fired: [...] }  (reminder poll)
 *   DELETE /api/todo-lists/:id                 { force: true }       (delete list + tasks)
 *   GET  /api/search/palette?q=&scope=patients
 */
(function () {
    'use strict';

    const tr = (k, fb, vars) => (window.V11I18n && window.V11I18n.t(k, fb, vars)) || fb;

    function deleteListPermMsg(list) {
        const name = (list && list.name) ? list.name : tr('todo.list_default', 'List');
        return tr(
            'todo.delete_list_perm_msg',
            'Permanently delete "' + name + '" and all of its tasks? This cannot be undone.',
            { name: name }
        );
    }

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
        listModal, listForm, archivedModal,
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
        listModal     = document.getElementById('todoListModal');
        listForm      = document.getElementById('todoListForm');
        archivedModal = document.getElementById('todoArchivedModal');
        listPopover   = document.getElementById('todoListPopover');
    }

    // ----------------------------------------------------------- open / close
    function open() {
        if (!drawer) cacheRefs();
        if (!drawer) return;
        // Opening the drawer is a user gesture — a good moment to ask for
        // desktop-notification permission so at-due reminders can pop natively.
        ensureNotifyPermission();
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
        closeListModal();
        closeArchivedModal();
    }

    function handleError(e) {
        // soft toast — there's no global toast hook spec'd here so we use alert modal
        const msg = (e && e.body) || (e && e.message) || tr('error.generic', 'Something went wrong.');
        if (typeof window.mkAlertModal === 'function') {
            window.mkAlertModal({ title: tr('error.title', 'Error'), message: msg });
        } else {
            console.error('[todo-drawer]', e);
        }
    }

    // ------------------------------------------------------ lists rendering
    function loadLists() {
        return api('/api/todo-lists').then((res) => {
            // /api/todo-lists returns { success: true, lists: [...] }. Older
            // shape kept as a fallback for forward-compatibility.
            state.lists = Array.isArray(res) ? res : ((res && (res.lists || res.data)) || []);
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
            chip.querySelector('.td-list-name').textContent = l.name || tr('todo.list_default', 'List');
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
            // explicit options affordance (kebab) → context popover
            const optsBtn = chip.querySelector('.td-list-opts');
            if (optsBtn) {
                optsBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openPopover(chip, l);
                });
            }
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
            '<i class="bi bi-plus-lg" aria-hidden="true"></i><span>' + tr('todo.new_list', 'New list') + '</span>';
        add.addEventListener('click', () => openListModal(null));
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
        return api('/api/todos?status=all&list_id=' + encodeURIComponent(listId)).then((res) => {
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
            var patName = pat.querySelector('.td-row-patient-name');
            patName.textContent = t.patient_name;
            if (t.patient_id) {
                patName.classList.add('patient-hover-name');
                patName.setAttribute('data-patient-id', String(t.patient_id));
            } else {
                patName.classList.remove('patient-hover-name');
                patName.removeAttribute('data-patient-id');
            }
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
        { min: 0,   max: 24,  title: tr('todo.progress.lets_go', "Let's go!") },
        { min: 25,  max: 49,  title: tr('todo.progress.nice_start', 'Nice start') },
        { min: 50,  max: 74,  title: tr('todo.progress.keep_up', 'Keep it up!') },
        { min: 75,  max: 99,  title: tr('todo.progress.almost', 'Almost there!') },
        { min: 100, max: 100, title: tr('todo.progress.all_done', 'All done!') }
    ];

    function updateProgress() {
        const total = state.tasks.length;
        const done = state.tasks.filter((t) => !!t.completed_at).length;
        const pct = total === 0 ? 0 : Math.round((done / total) * 100);
        const copy = PROGRESS_COPY.find((c) => pct >= c.min && pct <= c.max) || PROGRESS_COPY[0];
        progressTitle.textContent = pct === 100 ? copy.title + ' 🎉' : copy.title;
        progressSub.textContent = tr('todo.progress.sub', done + ' of ' + total + ' completed', { done: done, total: total });
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
            }).then((res) => {
                // Backend returns { success: true, data: { ...todo } }.
                // Unwrap so the optimistic placeholder gets replaced with a
                // proper task row (otherwise the wrapper object would have
                // no id/title and the row would render blank).
                const created = (res && res.data) ? res.data : res;
                const idx = state.tasks.findIndex((t) => t.id === tempId);
                if (idx !== -1 && created && created.id) {
                    state.tasks[idx] = Object.assign({}, created, { _optimistic: false });
                } else if (idx !== -1) {
                    // Couldn't read created task — at least clear the optimistic flag
                    state.tasks[idx]._optimistic = false;
                }
                renderTasks();
                updateProgress();
                // Refresh header badge so the open-count chip stays accurate
                if (typeof window.__refreshTodoHeaderBadge === 'function') {
                    window.__refreshTodoHeaderBadge();
                }
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
        api(endpoint, { method: 'POST' })
            .then((res) => {
                // Server may return the updated task; sync if present so the
                // optimistic completed_at matches the server stamp.
                const updated = (res && res.data) ? res.data : null;
                if (updated && updated.id != null) {
                    const idx = state.tasks.findIndex((x) => String(x.id) === String(updated.id));
                    if (idx !== -1) state.tasks[idx] = updated;
                }
                if (typeof window.__refreshTodoHeaderBadge === 'function') {
                    window.__refreshTodoHeaderBadge();
                }
            })
            .catch((err) => {
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
            api('/api/todos/' + id, { method: 'DELETE' })
                .then(() => {
                    if (typeof window.__refreshTodoHeaderBadge === 'function') {
                        window.__refreshTodoHeaderBadge();
                    }
                })
                .catch((err) => {
                state.tasks.push(t);
                renderTasks();
                updateProgress();
                handleError(err);
            });
        };
        if (typeof window.mkConfirmModal === 'function') {
            window.mkConfirmModal({
                title: tr('todo.delete_task', 'Delete task'),
                message: tr('todo.delete_task_msg', 'Delete this task?'),
                confirmText: tr('todo.delete', 'Delete'),
                confirmClass: 'btn-danger',
                cancelText: tr('modal.cancel', 'Cancel')
            }).then((ok) => { if (ok) doDelete(); });
        } else if (window.confirm(tr('todo.delete_task_msg', 'Delete this task?'))) {
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
        // todoFmList starts empty in markup; build/sync custom menu once lists load
        if (typeof ensureCustomSelectSynced === 'function') {
            ensureCustomSelectSynced(sel);
        } else if (typeof convertSelectsInContainer === 'function') {
            convertSelectsInContainer(sel.parentElement);
        }
    }

    function openFullModal(task) {
        if (!fullModal) cacheRefs();
        if (!fullModal) return;
        populateListSelect();
        const isEdit = !!(task && task.id && !String(task.id).startsWith('tmp_'));
        state.editingTaskId = isEdit ? task.id : null;
        $('#todoFullModalTitle').textContent = isEdit ? tr('todo.edit_task', 'Edit task') : tr('todo.new_task', 'New task');
        $('[data-submit-label]', fullForm).textContent = isEdit ? 'Save changes' : 'Add task';
        fullForm.reset();
        fullForm.elements.id.value = isEdit ? task.id : '';
        fullForm.elements.list_id.value = String((task && task.list_id) || state.currentListId || (state.lists[0] && state.lists[0].id) || '');
        fullForm.elements.title.value = (task && task.title) || '';
        fullForm.elements.description.value = (task && task.description) || '';
        fullForm.elements.due_at.value = (task && task.due_at) ? toLocalInput(task.due_at) : '';
        // Form field is now named `remind_before_minutes` (matches backend);
        // backend may return `remind_before_minutes` (preferred) or the older
        // `remind_before` alias.
        fullForm.elements.remind_before_minutes.value = (task && (task.remind_before_minutes != null ? task.remind_before_minutes : task.remind_before) != null)
            ? String(task.remind_before_minutes != null ? task.remind_before_minutes : task.remind_before)
            : '';
        fullForm.elements.priority.value = (task && task.priority) || 'med';
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
                remind_before_minutes: fd.get('remind_before_minutes') ? parseInt(fd.get('remind_before_minutes'), 10) : null,
                priority: fd.get('priority') || 'med',
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

    // -------------------------------------------------- list create/edit modal
    // `list` null → create mode; an existing list object → edit mode (used by
    // the popover's Rename + Color actions, replacing the old prompt()/cycle).
    // `focusField` ('name' | 'color') just decides what to focus on open.
    function openListModal(list, focusField) {
        if (!listModal) cacheRefs();
        if (!listModal) return;
        const isEdit = !!(list && list.id);
        listForm.reset();

        listForm.elements.id.value = isEdit ? String(list.id) : '';
        listForm.elements.name.value = isEdit ? (list.name || '') : '';

        const targetColor = isEdit ? (list.color || 'indigo') : 'indigo';
        const dots = $$('.td-dot', listForm);
        dots.forEach((d) => {
            const active = d.dataset.color === targetColor;
            d.classList.toggle('is-active', active);
            d.setAttribute('aria-checked', active ? 'true' : 'false');
        });
        // Fallback: if the stored color isn't in the swatch set, select the first.
        if (!listForm.querySelector('.td-dot.is-active') && dots[0]) {
            dots[0].classList.add('is-active');
            dots[0].setAttribute('aria-checked', 'true');
        }

        if (isEdit && list.icon) listForm.elements.icon.value = list.icon;

        const titleEl = document.getElementById('todoListModalTitle');
        const labelEl = listForm.querySelector('[data-list-submit-label]');
        if (titleEl) titleEl.textContent = isEdit ? tr('todo.edit_list_modal', 'Edit list') : tr('todo.new_list_modal', 'New list');
        if (labelEl) labelEl.textContent = isEdit ? tr('todo.save_list', 'Save list') : tr('todo.create_list', 'Create list');

        listModal.hidden = false;
        requestAnimationFrame(() => listModal.classList.add('is-open'));
        setTimeout(() => {
            try {
                if (focusField === 'color') {
                    const active = listForm.querySelector('.td-dot.is-active');
                    if (active) active.focus();
                } else {
                    listForm.elements.name.focus();
                    listForm.elements.name.select();
                }
            } catch (e) { /* */ }
        }, 80);
    }

    function closeListModal() {
        if (!listModal) return;
        listModal.classList.remove('is-open');
        setTimeout(() => { listModal.hidden = true; }, 200);
    }

    function bindListModal() {
        if (!listModal) return;
        $$('[data-close]', listModal).forEach((el) => el.addEventListener('click', closeListModal));

        const dots = $$('.td-dot', listForm);
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                dots.forEach((d) => { d.classList.remove('is-active'); d.setAttribute('aria-checked', 'false'); });
                dot.classList.add('is-active');
                dot.setAttribute('aria-checked', 'true');
            });
        });

        listForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = (listForm.elements.id.value || '').trim();
            const name = (listForm.elements.name.value || '').trim();
            const colorBtn = listForm.querySelector('.td-dot.is-active');
            const color = colorBtn ? colorBtn.dataset.color : 'indigo';
            const icon = listForm.elements.icon.value || 'bi-list-task';
            if (!name) return;
            const payload = { name: name, color: color, icon: icon };

            if (id) {
                // edit existing list
                api('/api/todo-lists/' + id, { method: 'PATCH', body: payload })
                    .then((res) => {
                        const updated = (res && res.list) ? res.list : null;
                        const list = state.lists.find((l) => String(l.id) === String(id));
                        if (list) {
                            list.name = (updated && updated.name) || name;
                            list.color = (updated && updated.color) || color;
                            list.icon = (updated && updated.icon) || icon;
                        }
                        renderRail();
                        populateListSelect();
                        if (state.currentListId != null && String(state.currentListId) === String(id) && progressCard) {
                            progressCard.style.setProperty('--list-c', colorVar(color));
                        }
                        closeListModal();
                    }).catch(handleError);
            } else {
                // create new list
                api('/api/todo-lists', { method: 'POST', body: payload })
                    .then((res) => {
                        // Backend returns { success: true, list: {...} }; tolerate
                        // the older bare-object shape too.
                        const created = (res && res.list) ? res.list : res;
                        const newList = created && created.id ? created
                            : { id: Date.now(), name: name, color: color, icon: icon, open_count: 0, total_count: 0 };
                        state.lists.push(newList);
                        renderRail();
                        populateListSelect();
                        selectList(newList.id);
                        closeListModal();
                    }).catch(handleError);
            }
        });
    }

    // -------------------------------------------------------- archived lists
    function openArchivedModal() {
        if (!archivedModal) cacheRefs();
        if (!archivedModal) return;
        const bodyEl = document.getElementById('todoArchivedBody');
        const emptyEl = document.getElementById('todoArchivedEmpty');
        const loadEl = document.getElementById('todoArchivedLoading');
        if (bodyEl) bodyEl.innerHTML = '';
        if (emptyEl) emptyEl.hidden = true;
        if (loadEl) loadEl.hidden = false;

        archivedModal.hidden = false;
        requestAnimationFrame(() => archivedModal.classList.add('is-open'));

        api('/api/todo-lists?include_archived=1').then((res) => {
            const all = Array.isArray(res) ? res : ((res && (res.lists || res.data)) || []);
            const archived = all.filter((l) => l.archived_at);
            if (loadEl) loadEl.hidden = true;
            renderArchived(archived);
        }).catch((e) => {
            if (loadEl) loadEl.hidden = true;
            handleError(e);
        });
    }

    function renderArchived(archived) {
        const bodyEl = document.getElementById('todoArchivedBody');
        const emptyEl = document.getElementById('todoArchivedEmpty');
        if (!bodyEl) return;
        bodyEl.innerHTML = '';
        if (!archived.length) {
            if (emptyEl) emptyEl.hidden = false;
            return;
        }
        if (emptyEl) emptyEl.hidden = true;
        const tpl = document.getElementById('tpl-td-archived-row');
        const frag = document.createDocumentFragment();
        archived.forEach((l) => {
            const row = tpl.content.firstElementChild.cloneNode(true);
            row.dataset.listId = String(l.id);
            row.style.setProperty('--list-c', colorVar(l.color));
            row.querySelector('.td-archived-icon').classList.add(l.icon || 'bi-list-task');
            row.querySelector('.td-archived-name').textContent = l.name || 'List';
            const total = (l.total_count != null) ? l.total_count : 0;
            row.querySelector('.td-archived-count').textContent =
                total > 0 ? (total + (total === 1 ? ' task' : ' tasks')) : 'Empty';
            row.querySelector('[data-act="restore"]').addEventListener('click', () => restoreList(l.id, row));
            row.querySelector('[data-act="delete"]').addEventListener('click', () => deleteArchivedList(l, row));
            frag.appendChild(row);
        });
        bodyEl.appendChild(frag);
    }

    function restoreList(id, row) {
        api('/api/todo-lists/' + id + '/restore', { method: 'POST' }).then((res) => {
            const restored = (res && res.list) ? res.list : null;
            if (row && row.parentNode) row.parentNode.removeChild(row);
            // pull it back into the active rail
            const existing = state.lists.find((l) => String(l.id) === String(id));
            if (!existing) {
                state.lists.push(restored || { id: id, name: 'List', color: 'indigo', icon: 'bi-list-task', open_count: 0, total_count: 0 });
            } else if (restored) {
                existing.archived_at = null;
            }
            renderRail();
            populateListSelect();
            const bodyEl = document.getElementById('todoArchivedBody');
            const emptyEl = document.getElementById('todoArchivedEmpty');
            if (bodyEl && !bodyEl.children.length && emptyEl) emptyEl.hidden = false;
            if (typeof window.__refreshTodoHeaderBadge === 'function') window.__refreshTodoHeaderBadge();
        }).catch(handleError);
    }

    function deleteArchivedList(list, row) {
        const msg = deleteListPermMsg(list);
        const run = function () {
            api('/api/todo-lists/' + list.id, { method: 'DELETE', body: { force: true } }).then(() => {
                if (row && row.parentNode) row.parentNode.removeChild(row);
                state.lists = state.lists.filter((l) => String(l.id) !== String(list.id));
                const bodyEl = document.getElementById('todoArchivedBody');
                const emptyEl = document.getElementById('todoArchivedEmpty');
                if (bodyEl && !bodyEl.children.length && emptyEl) emptyEl.hidden = false;
                if (typeof window.__refreshTodoHeaderBadge === 'function') window.__refreshTodoHeaderBadge();
            }).catch(handleError);
        };
        if (typeof window.mkConfirmModal === 'function') {
            window.mkConfirmModal({
                title: tr('todo.delete_list', 'Delete list'),
                message: msg,
                confirmText: tr('todo.delete', 'Delete'),
                confirmClass: 'btn-danger',
                cancelText: tr('modal.cancel', 'Cancel')
            }).then((ok) => { if (ok) run(); });
        } else if (window.confirm(msg)) {
            run();
        }
    }

    function closeArchivedModal() {
        if (!archivedModal) return;
        archivedModal.classList.remove('is-open');
        setTimeout(() => { archivedModal.hidden = true; }, 200);
    }

    function bindArchivedModal() {
        if (!archivedModal) return;
        $$('[data-close]', archivedModal).forEach((el) => el.addEventListener('click', closeArchivedModal));
    }

    // -------------------------------------------------------- list popover
    let popoverList = null;
    function openPopover(chip, list) {
        popoverList = list;
        // The default list can't be archived or deleted (backend rejects it),
        // so hide those actions for it.
        const isDefault = !!(list && (list.is_default === 1 || list.is_default === true));
        const archiveBtn = listPopover.querySelector('[data-popover-archive]');
        const deleteBtn = listPopover.querySelector('[data-popover-delete]');
        if (archiveBtn) archiveBtn.hidden = isDefault;
        if (deleteBtn) deleteBtn.hidden = isDefault;
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
                openListModal(list, 'name');
            } else if (act === 'color') {
                openListModal(list, 'color');
            } else if (act === 'up' || act === 'down') {
                moveList(list.id, act === 'up' ? -1 : 1);
            } else if (act === 'archive') {
                if (typeof window.mkConfirmModal === 'function') {
                    window.mkConfirmModal({
                        title: tr('todo.archive_list', 'Archive list'),
                        message: tr('todo.archive_list_msg', 'Archive this list?'),
                        confirmText: tr('todo.archive_btn', 'Archive'),
                        confirmClass: 'btn-warning',
                        cancelText: tr('modal.cancel', 'Cancel')
                    }).then((ok) => { if (ok) archiveList(list.id); });
                } else if (window.confirm(tr('todo.archive_list_msg', 'Archive this list?'))) {
                    archiveList(list.id);
                }
            } else if (act === 'delete') {
                const msg = deleteListPermMsg(list);
                if (typeof window.mkConfirmModal === 'function') {
                    window.mkConfirmModal({
                        title: tr('todo.delete_list', 'Delete list'),
                        message: msg,
                        confirmText: tr('todo.delete', 'Delete'),
                        confirmClass: 'btn-danger',
                        cancelText: tr('modal.cancel', 'Cancel')
                    }).then((ok) => { if (ok) deleteList(list.id); });
                } else if (window.confirm(msg)) {
                    deleteList(list.id);
                }
            }
        });
        document.addEventListener('mousedown', (e) => {
            if (!listPopover.hidden && !listPopover.contains(e.target)) hidePopover();
        });
        window.addEventListener('scroll', hidePopover, true);
        window.addEventListener('resize', hidePopover);
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

    function deleteList(id) {
        // `force: true` tells the backend to drop the list AND its tasks in one
        // transaction (no archive-first / must-be-empty dance).
        api('/api/todo-lists/' + id, { method: 'DELETE', body: { force: true } }).then(() => {
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
            if (typeof window.__refreshTodoHeaderBadge === 'function') {
                window.__refreshTodoHeaderBadge();
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
    // Mount into #topActionsQuick (notes · to-do · ⌘K row on mobile).
    function ensureHeaderButton() {
        if (document.getElementById('todoDrawerToggle')) return;
        var mount = document.getElementById('topActionsQuick');
        if (!mount) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'todoDrawerToggle';
        btn.className = 'todo-drawer-toggle';
        btn.setAttribute('aria-label', tr('todo.open_drawer', 'Open to-do drawer'));
        btn.setAttribute('title', tr('todo.title', 'To-Do') + ' (T)');
        btn.innerHTML =
            '<i class="bi bi-check2-square" aria-hidden="true"></i>' +
            '<span class="todo-drawer-toggle__badge" id="todoDrawerToggleBadge" hidden>0</span>';
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            open();
        });
        var cmdk = document.getElementById('cmdkToggle');
        if (cmdk) mount.insertBefore(btn, cmdk);
        else mount.appendChild(btn);

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

    // ------------------------------------------------ due / reminder polling
    //
    // Polls /api/todos/due-check on an interval (and whenever the tab regains
    // focus). That endpoint runs the same lead-time + at-due dispatch the
    // 5-minute cron does — but scoped to the current user — and returns the
    // freshly-fired items so we can raise an immediate in-app toast and (when
    // permitted) a desktop notification. This means reminders work even when
    // no OS cron is configured, and fire whether or not the drawer is open.
    // Server-side flags (todo_reminded_at / todo_notified_at) guarantee each
    // reminder is delivered exactly once.
    const REMINDER_POLL_MS = 60 * 1000;
    let reminderTimer = null;
    let reminderStarted = false;

    function startReminderPolling() {
        if (reminderStarted) return;
        reminderStarted = true;
        // First check shortly after load so a just-due task surfaces quickly,
        // then settle into the steady interval.
        setTimeout(reminderTick, 4000);
        reminderTimer = setInterval(reminderTick, REMINDER_POLL_MS);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') reminderTick();
        });
    }

    function reminderTick() {
        if (document.visibilityState !== 'visible') return;
        fetch('/api/todos/due-check', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then((r) => (r.ok ? r.json() : null))
            .then((data) => {
                if (!data || !data.success || !Array.isArray(data.fired) || !data.fired.length) return;
                handleFired(data.fired);
            })
            .catch(() => { /* offline / transient — retry next tick */ });
    }

    function handleFired(items) {
        maybeDesktopNotify(items);
        // Cap in-app toasts so a backlog of overdue tasks can't flood the UI.
        const MAX = 3;
        items.slice(0, MAX).forEach(showReminderToast);
        if (items.length > MAX) {
            const extra = items.length - MAX;
            showReminderToast({
                kind: 'summary',
                title: tr('todo.toast_title', extra + ' tasks need attention', { n: extra }),
                body: tr('todo.toast_body', 'Open the to-do drawer to review them.')
            });
        }
        // Keep the bell + header badge in sync with the new notifications.
        try { if (window.notifCenter && window.notifCenter.refresh) window.notifCenter.refresh(); } catch (e) { /* */ }
        if (typeof window.__refreshTodoHeaderBadge === 'function') window.__refreshTodoHeaderBadge();
        // If the drawer is open, refresh rows so snoozed/overdue state updates.
        if (drawer && drawer.classList.contains('open') && state.currentListId) {
            loadTasks(state.currentListId).catch(() => { /* */ });
        }
    }

    function ensureNotifyPermission() {
        try {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        } catch (e) { /* */ }
    }

    function maybeDesktopNotify(items) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        items.slice(0, 3).forEach((it) => {
            try {
                const n = new Notification(it.title || 'Task reminder', {
                    body: it.body || '',
                    tag: 'todo-' + (it.id || it.kind)
                });
                n.onclick = function () {
                    window.focus();
                    if (typeof window.openTodoDrawer === 'function') window.openTodoDrawer();
                    n.close();
                };
            } catch (e) { /* */ }
        });
    }

    // ------------------------------------------------ in-app reminder toast
    let toastHost = null;
    function reminderToastHost() {
        if (toastHost && document.body.contains(toastHost)) return toastHost;
        toastHost = document.getElementById('tdReminderToasts');
        if (!toastHost) {
            toastHost = document.createElement('div');
            toastHost.id = 'tdReminderToasts';
            toastHost.className = 'td-toast-host';
            toastHost.setAttribute('aria-live', 'polite');
            document.body.appendChild(toastHost);
        }
        return toastHost;
    }

    function showReminderToast(item) {
        const host = reminderToastHost();
        const el = document.createElement('div');
        el.className = 'td-toast' +
            (item.kind === 'due' ? ' is-due' : item.kind === 'reminder' ? ' is-reminder' : '');
        el.setAttribute('role', 'status');

        const icon = item.kind === 'due' ? 'bi-exclamation-circle'
            : item.kind === 'reminder' ? 'bi-alarm' : 'bi-list-task';
        const patient = item.patient_name
            ? '<span class="td-toast-patient"><i class="bi bi-person" aria-hidden="true"></i>' +
              escapeHtml(item.patient_name) + '</span>'
            : '';
        const canSnooze = item.id && item.kind !== 'summary';

        el.innerHTML =
            '<span class="td-toast-icon"><i class="bi ' + icon + '" aria-hidden="true"></i></span>' +
            '<div class="td-toast-main">' +
                '<p class="td-toast-title">' + escapeHtml(item.title || 'Task') + '</p>' +
                (item.body ? '<p class="td-toast-body">' + escapeHtml(item.body) + '</p>' : '') +
                patient +
            '</div>' +
            '<div class="td-toast-actions">' +
                (canSnooze ? '<button type="button" class="td-toast-btn" data-toast-snooze>Snooze 1h</button>' : '') +
                '<button type="button" class="td-toast-btn td-toast-open" data-toast-open>Open</button>' +
                '<button type="button" class="td-toast-close" data-toast-close aria-label="Dismiss">' +
                    '<i class="bi bi-x-lg" aria-hidden="true"></i></button>' +
            '</div>';

        let hideTimer = null;
        const dismiss = function () {
            if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
            el.classList.remove('is-in');
            el.classList.add('is-out');
            setTimeout(() => { if (el.parentNode) el.parentNode.removeChild(el); }, 240);
        };

        el.addEventListener('click', (e) => {
            if (e.target.closest('[data-toast-close]')) { dismiss(); return; }
            if (e.target.closest('[data-toast-snooze]')) {
                if (item.id) {
                    api('/api/todos/' + item.id + '/snooze', { method: 'POST', body: { minutes: 60 } })
                        .then(() => {
                            if (drawer && drawer.classList.contains('open') && state.currentListId) {
                                loadTasks(state.currentListId).catch(() => { /* */ });
                            }
                        })
                        .catch(handleError);
                }
                dismiss();
                return;
            }
            if (e.target.closest('[data-toast-open]') || e.target.closest('.td-toast-main')) {
                if (typeof window.openTodoDrawer === 'function') window.openTodoDrawer();
                dismiss();
            }
        });

        host.appendChild(el);
        requestAnimationFrame(() => el.classList.add('is-in'));
        // At-due toasts linger a little longer than lead reminders.
        const ttl = item.kind === 'due' ? 15000 : 9000;
        hideTimer = setTimeout(dismiss, ttl);
    }

    // -------------------------------------------------------- init
    function init() {
        cacheRefs();
        if (!drawer) return;

        // The drawer uses `transform` for its slide animation, which makes it
        // the containing block for any `position: fixed` descendant AND clips
        // them via `overflow: hidden`. The list popover must therefore live on
        // <body> so it positions against the viewport and isn't clipped.
        if (listPopover && listPopover.parentElement !== document.body) {
            document.body.appendChild(listPopover);
        }

        ensureHeaderButton();

        // close button
        $('#todoDrawerClose', drawer).addEventListener('click', close);
        const innerModalOpen = (m) => m && !m.hidden && m.classList.contains('is-open');
        // backdrop click — but only if no inner modal is open
        backdrop.addEventListener('click', () => {
            if (innerModalOpen(fullModal)) return;
            if (innerModalOpen(listModal)) return;
            if (innerModalOpen(archivedModal)) return;
            close();
        });
        // ESC — closes the top-most layer first (inner modals → drawer)
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (!drawer.classList.contains('open')) return;
            if (innerModalOpen(archivedModal)) { closeArchivedModal(); return; }
            if (innerModalOpen(listModal)) { closeListModal(); return; }
            if (innerModalOpen(fullModal)) { closeFullModal(); return; }
            close();
        });

        // fab
        fab.addEventListener('click', () => openFullModal(null));

        // Archived-lists entry point in the header.
        const archivedBtn = document.getElementById('todoArchivedBtn');
        if (archivedBtn) archivedBtn.addEventListener('click', openArchivedModal);

        bindQuickAdd();
        bindFilters();
        bindFullModal();
        bindListModal();
        bindArchivedModal();
        bindPopover();
        bindDrag();

        // Start polling for due/at-due reminders site-wide (independent of
        // whether the drawer is ever opened).
        startReminderPolling();
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
