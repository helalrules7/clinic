/**
 * Patient Board — two-level (overview → detail), Trello-inspired. English UI.
 *
 *   Overview : grid of board cards, each a workflow stage with a patient
 *              count. Create / edit / delete boards.
 *   Detail   : open a board → patient cards with quick actions
 *              (open profile, quick-edit, remove from board) + per-patient
 *              notes (comments of type `board_card`, each with the author's
 *              avatar and a date/time stamp).
 *
 * Backend (BoardController):
 *   GET    /api/board/boards
 *   POST   /api/board/boards
 *   PUT    /api/board/boards/{id}
 *   DELETE /api/board/boards/{id}
 *   GET    /api/board/boards/{id}/cards?q=&sort=
 *   POST   /api/board/boards/{id}/patients   {patient_id}
 *   DELETE /api/board/boards/{id}/patients/{pid}
 *   PUT    /api/board/patients/{pid}          (safe partial edit)
 *   Notes reuse /api/comments/board_card/{patient_id}
 *
 * Navigation uses location.hash (#board-{id}) so the browser Back button
 * returns to the overview.
 */
(function () {
    'use strict';

    // Idempotency guard: if board.js is ever included more than once on a page
    // (e.g. the board embedded in the dashboard plus a stray include), the second
    // run would bind duplicate listeners that race over the same #boardGrid /
    // #patientGrid and can leave the detail view blank. Initialise only once.
    if (window.__boardInited) return;
    window.__boardInited = true;

    const CFG  = window.BOARD_CONFIG || {};
    const CSRF = CFG.csrfToken || '';
    const ME   = CFG.currentUser || {};
    const CAN_MANAGE = ME.role === 'doctor' || ME.role === 'admin';

    // Expanded swatch palette (16 colors covering the full hue wheel).
    const COLORS = [
        '#0ea5e9', // sky
        '#06b6d4', // cyan
        '#14b8a6', // teal
        '#10b981', // emerald
        '#22c55e', // green
        '#84cc16', // lime
        '#eab308', // yellow
        '#f59e0b', // amber
        '#f97316', // orange
        '#ef4444', // red
        '#f43f5e', // rose
        '#ec4899', // pink
        '#d946ef', // fuchsia
        '#a855f7', // purple
        '#6366f1', // indigo
        '#3b82f6', // blue
        '#64748b', // slate (neutral)
        '#475569', // darker slate
    ];

    // Curated icon palette for boards. Each value is a Bootstrap Icons class
    // name. Order roughly follows clinical workflow stages.
    const ICONS = [
        'bi-kanban',              // default — generic board
        'bi-clipboard-pulse',     // new consultation
        'bi-person-plus',         // intake / registration
        'bi-clipboard2-check',    // assessment complete
        'bi-camera',              // imaging
        'bi-droplet-half',        // labs
        'bi-eye',                 // ophthalmology
        'bi-heart-pulse',         // cardiology / vitals
        'bi-activity',            // monitoring
        'bi-bandaid',             // surgical
        'bi-hospital',            // inpatient
        'bi-house-heart',         // discharged
        'bi-clock-history',       // follow-up
        'bi-arrow-repeat',        // returning patient
        'bi-prescription2',       // prescription
        'bi-graph-up-arrow',      // progress / outcome
        'bi-stars',               // VIP / priority
        'bi-flag',                // flagged / urgent
        'bi-exclamation-triangle',// urgent
        'bi-check2-circle',       // completed
        'bi-bookmark-star',       // bookmarked / starred
        'bi-people',              // group / cohort
        'bi-pin-angle',           // pinned
        'bi-hourglass-split',     // waiting / pending
    ];

    // ---- DOM ---------------------------------------------------------------
    const $ = (id) => document.getElementById(id);

    const overviewSection = $('boardOverview');
    const detailSection   = $('boardDetail');
    const overviewHead    = $('boardOverviewHead');
    const detailHead      = $('boardDetailHead');

    const boardGrid       = $('boardGrid');
    const boardsSkeleton  = $('boardsSkeleton');
    const boardsEmpty     = $('boardsEmpty');
    const boardsNoResults = $('boardsNoResults');
    const boardsCount     = $('boardsCount');
    const boardsSearch    = $('boardsSearch');

    const patientGrid       = $('patientGrid');
    const patientsSkeleton  = $('patientsSkeleton');
    const patientsEmpty     = $('patientsEmpty');
    const patientsNoResults = $('patientsNoResults');
    const patientsSearch    = $('patientsSearch');
    const patientsSort      = $('patientsSort');

    const detailName   = $('boardDetailName');
    const detailDesc   = $('boardDetailDesc');
    const detailCount  = $('boardDetailCount');
    const detailSwatch = $('boardDetailSwatch');
    const crumbName    = $('boardCrumbName');

    // ---- State -------------------------------------------------------------
    const state = {
        boards: [],
        boardsQuery: '',
        current: null,
        cards: [],
        patientsQuery: '',
        patientsSort: 'moved',
        editingBoard: null,
        pendingDeleteBoard: null,
        pendingRemove: null,
    };

    // ---- Modals (lazy; bootstrap is loaded before this deferred script) ---
    // We relocate each modal element to <body> on first use. When this board
    // is embedded (e.g. inside the dashboard's Patient Boards card), the modal
    // markup otherwise sits deep inside a card whose ancestors can clip/anchor
    // it (overflow, transform, stacking context) so the backdrop paints over
    // the dialog. Moving it to <body> guarantees correct full-screen layering.
    const modals = {};
    function modal(id) {
        if (!modals[id]) {
            const el = $(id);
            if (el && window.bootstrap) {
                if (el.parentElement !== document.body) document.body.appendChild(el);
                modals[id] = new bootstrap.Modal(el);
            }
        }
        return modals[id];
    }

    // ---- API + toast -------------------------------------------------------
    function api(url, opts = {}) {
        return fetch(url, Object.assign({
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
        }, opts)).then(async (r) => {
            let body = null;
            try { body = await r.json(); } catch (_) { body = null; }
            return { status: r.status, body: body };
        });
    }

    let toastStack = null;
    function toast(msg, kind) {
        if (!toastStack) {
            toastStack = document.createElement('div');
            toastStack.className = 'board-toast-stack';
            document.body.appendChild(toastStack);
        }
        const icon = kind === 'is-success' ? 'bi-check-circle-fill'
                   : kind === 'is-error'   ? 'bi-exclamation-octagon-fill'
                   : 'bi-info-circle-fill';
        const el = document.createElement('div');
        el.className = 'board-toast ' + (kind || 'is-info');
        el.setAttribute('role', 'status');
        el.innerHTML = `<i class="bi ${icon}"></i><span></span>`;
        el.querySelector('span').textContent = msg;
        toastStack.appendChild(el);
        requestAnimationFrame(() => el.classList.add('is-shown'));
        setTimeout(() => {
            el.classList.remove('is-shown');
            setTimeout(() => el.remove(), 300);
        }, 3000);
    }

    // ---- Helpers -----------------------------------------------------------
    function escapeHtml(s) {
        return (s == null ? '' : String(s))
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function escapeAttr(s) { return (s == null ? '' : String(s)).replace(/"/g, '&quot;'); }

    // Render a note body as safe HTML: escape everything, then turn a controlled
    // markdown link `[label](href)` into an <a>. href is restricted to internal
    // paths (/…) or http(s) URLs, so there's no script/attribute-injection risk
    // (label + href both come from the already-escaped string).
    function linkifyNote(text) {
        return escapeHtml(text)
            .replace(/\[([^\]\n]+)\]\(([^)\s]+)\)/g, (m, label, href) => {
                const raw = href.replace(/&amp;/g, '&');
                if (!/^(\/[\w\-./?=&#%]*|https?:\/\/[\w\-./?=&#%:]+)$/i.test(raw)) return m;
                return `<a href="${href}" class="note-link">${label}</a>`;
            })
            .replace(/\n/g, '<br>');
    }

    function initials(name) {
        return (name || '?').trim().split(/\s+/).slice(0, 2)
            .map((s) => s[0] || '').join('').toUpperCase() || '?';
    }
    function profileSrc(img) {
        if (!img) return null;
        return img.indexOf('/public/') === 0 ? img : '/public' + img;
    }
    function ageFromDob(dob) {
        if (!dob) return null;
        const d = new Date(dob);
        if (isNaN(d)) return null;
        const now = new Date();
        let a = now.getFullYear() - d.getFullYear();
        const m = now.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < d.getDate())) a--;
        return a >= 0 && a < 150 ? a : null;
    }
    function genderLabel(g) {
        return g === 'Male' ? 'Male' : g === 'Female' ? 'Female' : g === 'Other' ? 'Other' : '';
    }
    function daysSince(date) {
        if (!date) return null;
        const d = new Date(date);
        if (isNaN(d)) return null;
        return Math.floor((Date.now() - d.getTime()) / 86400000);
    }
    function relativeTime(date) {
        const days = daysSince(date);
        if (days === null) return '';
        if (days <= 0) return 'today';
        if (days === 1) return 'yesterday';
        if (days < 7) return `${days} days ago`;
        if (days < 30) { const w = Math.floor(days / 7); return `${w} ${w === 1 ? 'week' : 'weeks'} ago`; }
        if (days < 365) { const mo = Math.floor(days / 30); return `${mo} ${mo === 1 ? 'month' : 'months'} ago`; }
        const y = Math.floor(days / 365); return `${y} ${y === 1 ? 'year' : 'years'} ago`;
    }
    const DT_FMT = (function () {
        try {
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: 'numeric', minute: '2-digit',
            });
        } catch (_) { return null; }
    })();
    function commentDateTime(ts) {
        if (!ts) return '';
        const d = new Date(String(ts).replace(' ', 'T'));
        if (isNaN(d)) return ts;
        return DT_FMT ? DT_FMT.format(d) : d.toLocaleString();
    }

    // =====================================================================
    //  View switching
    // =====================================================================
    function showOverview() {
        state.current = null;
        detailSection.hidden = true;
        detailHead.hidden = true;
        overviewSection.hidden = false;
        overviewHead.hidden = false;
        loadBoards({ silent: state.boards.length > 0 });
    }

    function routeFromHash() {
        const m = (location.hash || '').match(/^#board-(\d+)$/);
        if (m) openBoardById(parseInt(m[1], 10));
        else showOverview();
    }

    // =====================================================================
    //  Overview
    // =====================================================================
    function loadBoards(opts = {}) {
        if (!opts.silent) {
            boardsSkeleton.hidden = false;
            boardGrid.innerHTML = '';
            boardsEmpty.hidden = true;
            boardsNoResults.hidden = true;
        }
        return api('/api/board/boards').then((res) => {
            boardsSkeleton.hidden = true;
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to load boards');
            }
            state.boards = res.body.data || [];
            renderBoards();
        }).catch((err) => {
            boardsSkeleton.hidden = true;
            toast(err.message || 'Failed to load boards', 'is-error');
        });
    }

    function visibleBoards() {
        const q = state.boardsQuery.trim().toLowerCase();
        if (!q) return state.boards;
        return state.boards.filter((b) =>
            (b.name || '').toLowerCase().includes(q) ||
            (b.description || '').toLowerCase().includes(q));
    }

    function renderBoards() {
        const total = state.boards.length;
        boardsCount.hidden = total === 0;
        boardsCount.textContent = `${total} ${total === 1 ? 'board' : 'boards'}`;

        if (total === 0) {
            boardGrid.innerHTML = '';
            boardsEmpty.hidden = false;
            boardsNoResults.hidden = true;
            return;
        }
        boardsEmpty.hidden = true;

        const list = visibleBoards();
        if (list.length === 0) {
            boardGrid.innerHTML = '';
            boardsNoResults.hidden = false;
            return;
        }
        boardsNoResults.hidden = true;
        boardGrid.innerHTML = '';
        list.forEach((b) => boardGrid.appendChild(boardCardEl(b)));
    }

    function boardCardEl(b) {
        const el = document.createElement('div');
        el.className = 'board-card';
        el.style.setProperty('--card-accent', b.color || '#0ea5e9');
        el.setAttribute('role', 'listitem');
        el.tabIndex = 0;
        el.setAttribute('aria-label', `Open board ${b.name}`);

        const count = b.patient_count || 0;
        const manageBtn = CAN_MANAGE
            ? `<button type="button" class="board-card-menu" aria-label="Edit board" title="Edit">
                   <i class="bi bi-three-dots"></i>
               </button>`
            : '';

        // Patient preview rows — up to 2 shown, then "+N Others" if more.
        // Whole card remains a single click target → open the board.
        const previews = Array.isArray(b.patients) ? b.patients : [];
        const MAX_ROWS = 2;
        let previewHtml = '';
        if (previews.length > 0) {
            const visible = previews.slice(0, MAX_ROWS);
            const overflow = count - visible.length;
            const rows = visible.map((p) => {
                const initials = escapeHtml(p.initials || 'P');
                const name = escapeHtml(p.name || 'Patient');
                const cm = p.comments_count | 0;
                const at = p.attachments_count | 0;
                return `
                    <div class="board-card-patient" aria-hidden="true">
                        <span class="bcp-avatar" data-pid="${p.patient_id | 0}">${initials}</span>
                        <span class="bcp-name">${name}</span>
                        <span class="bcp-meta">
                            <span class="bcp-icon" title="${cm} comments"><i class="bi bi-chat"></i> ${cm}</span>
                            <span class="bcp-icon" title="${at} attachments"><i class="bi bi-paperclip"></i> ${at}</span>
                        </span>
                    </div>`;
            }).join('');
            const more = overflow > 0
                ? `<div class="board-card-patient bcp-more" aria-hidden="true">
                       <span class="bcp-avatar bcp-avatar-more">+${overflow}</span>
                       <span class="bcp-name">${overflow} ${overflow === 1 ? 'other' : 'others'}</span>
                   </div>`
                : '';
            previewHtml = `<div class="board-card-patients">${rows}${more}</div>`;
        }

        const iconClass = (b.icon && /^bi-[a-z0-9-]+$/.test(b.icon)) ? b.icon : 'bi-kanban';
        el.innerHTML = `
            <div class="board-card-top">
                <h3 class="board-card-name">
                    <span class="board-icon-chip" aria-hidden="true"><i class="bi ${iconClass}"></i></span>
                    <span class="txt">${escapeHtml(b.name)}</span>
                </h3>
                ${manageBtn}
            </div>
            ${b.description ? `<p class="board-card-desc">${escapeHtml(b.description)}</p>` : ''}
            ${previewHtml}
            <div class="board-card-foot">
                <span class="board-count-badge ${count === 0 ? 'is-empty' : ''}">
                    <i class="bi bi-people-fill"></i> ${count} ${count === 1 ? 'patient' : 'patients'}
                </span>
                <span class="board-card-open">Open <i class="bi bi-arrow-right"></i></span>
            </div>
        `;

        // Stable per-patient accent color on the avatar based on patient_id.
        el.querySelectorAll('.bcp-avatar[data-pid]').forEach((node) => {
            const pid = parseInt(node.getAttribute('data-pid'), 10) || 0;
            node.style.setProperty('--bcp-accent', avatarHue(pid));
        });

        el.addEventListener('click', (e) => {
            if (e.target.closest('.board-card-menu')) return;
            openBoard(b);
        });
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openBoard(b); }
        });
        const menu = el.querySelector('.board-card-menu');
        if (menu) menu.addEventListener('click', (e) => { e.stopPropagation(); openBoardEditor(b); });

        return el;
    }

    // Stable HSL hue per patient (used by mini-row avatar bg).
    const AVATAR_PALETTE = [
        '#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899',
        '#a855f7', '#14b8a6', '#3b82f6', '#ef4444', '#84cc16',
    ];
    function avatarHue(pid) {
        return AVATAR_PALETTE[(pid >>> 0) % AVATAR_PALETTE.length];
    }

    // =====================================================================
    //  Detail
    // =====================================================================
    function openBoard(board) {
        if (location.hash === '#board-' + board.id) enterBoard(board);
        else location.hash = 'board-' + board.id;
    }
    function openBoardById(id) {
        const b = state.boards.find((x) => x.id === id);
        if (b) enterBoard(b);
        else {
            loadBoards().then(() => {
                const found = state.boards.find((x) => x.id === id);
                if (found) enterBoard(found);
                else showOverview();
            });
        }
    }

    function enterBoard(board) {
        state.current = board;
        state.patientsQuery = '';
        patientsSearch.value = '';
        overviewSection.hidden = true;
        overviewHead.hidden = true;
        detailSection.hidden = false;
        detailHead.hidden = false;

        detailName.textContent = board.name;
        crumbName.textContent = board.name;
        detailDesc.textContent = board.description || '';
        detailDesc.hidden = !board.description;
        applySwatchChip(detailSwatch, board);
        loadCards();
    }

    // Render the detail header swatch as a colored icon chip (replaces the
    // legacy plain colored dot). Used in detail header + post-save refresh.
    function applySwatchChip(el, board) {
        if (!el) return;
        el.style.setProperty('--card-accent', board.color || '#0ea5e9');
        const icon = (board.icon && /^bi-[a-z0-9-]+$/.test(board.icon)) ? board.icon : 'bi-kanban';
        el.innerHTML = '<i class="bi ' + icon + '"></i>';
        el.classList.add('board-icon-chip');
        el.style.background = '';   // override legacy inline bg from earlier renders
    }

    function loadCards() {
        if (!state.current) return;
        patientsSkeleton.hidden = false;
        patientGrid.innerHTML = '';
        patientsEmpty.hidden = true;
        patientsNoResults.hidden = true;

        const params = new URLSearchParams();
        if (state.patientsQuery) params.set('q', state.patientsQuery);
        params.set('sort', state.patientsSort);

        api(`/api/board/boards/${state.current.id}/cards?` + params.toString()).then((res) => {
            patientsSkeleton.hidden = true;
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to load patients');
            }
            state.cards = (res.body.data && res.body.data.cards) || [];
            if (res.body.data && res.body.data.board) {
                state.current = Object.assign(state.current, res.body.data.board);
            }
            renderCards();
        }).catch((err) => {
            patientsSkeleton.hidden = true;
            toast(err.message || 'Failed to load patients', 'is-error');
        });
    }

    function renderCards() {
        detailCount.textContent = state.cards.length;

        if (state.cards.length === 0) {
            patientGrid.innerHTML = '';
            if (state.patientsQuery) { patientsNoResults.hidden = false; patientsEmpty.hidden = true; }
            else { patientsEmpty.hidden = false; patientsNoResults.hidden = true; }
            return;
        }
        patientsEmpty.hidden = true;
        patientsNoResults.hidden = true;
        patientGrid.innerHTML = '';
        state.cards.forEach((c) => patientGrid.appendChild(patientCardEl(c)));
    }

    function patientCardEl(c) {
        const el = document.createElement('div');
        el.className = 'board-patient-card';
        el.id = 'patient-' + c.patient_id;
        el.setAttribute('role', 'listitem');

        const age = ageFromDob(c.dob);
        const recent = (() => { const d = daysSince(c.last_visit); return d !== null && d <= 14; })();
        if (recent) el.classList.add('is-recent');

        const sub = [];
        if (age !== null) sub.push(`${age} yrs`);
        const g = genderLabel(c.gender); if (g) sub.push(g);

        const visitChip = c.visit_count > 0
            ? `<span class="patient-chip"><i class="bi bi-calendar-check"></i> ${c.visit_count} ${c.visit_count === 1 ? 'visit' : 'visits'}</span>`
            : `<span class="patient-chip"><i class="bi bi-calendar-x"></i> No visits</span>`;
        const lastChip = c.last_visit
            ? `<span class="patient-chip"><i class="bi bi-clock-history"></i> Last visit ${escapeHtml(relativeTime(c.last_visit))}</span>`
            : '';
        const phoneChip = c.phone
            ? `<span class="patient-chip"><i class="bi bi-telephone"></i> ${escapeHtml(c.phone)}</span>`
            : '';
        const recentFlag = recent ? `<span class="recent-flag"><i class="bi bi-stars"></i> Recent visit</span>` : '';
        const notesCount = c.notes_count || 0;

        el.innerHTML = `
            <div class="patient-card-head">
                <span class="patient-avatar">${escapeHtml(initials(c.name))}</span>
                <div class="patient-id-main">
                    <h3 class="patient-name">${escapeHtml(c.name || 'Unnamed')}</h3>
                    <div class="patient-sub">
                        ${sub.map((s) => `<span>${escapeHtml(s)}</span>`).join('<span class="sep">•</span>')}
                        ${recentFlag ? (sub.length ? '<span class="sep">•</span>' : '') + recentFlag : ''}
                    </div>
                </div>
            </div>
            <div class="patient-card-meta">
                ${visitChip}
                ${lastChip}
                ${phoneChip}
            </div>
            <div class="patient-card-actions">
                <a class="patient-action is-open" href="/doctor/patients/${c.patient_id}"
                   title="Open full profile" aria-label="Open patient's full profile">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <button type="button" class="patient-action js-edit" title="Edit details" aria-label="Edit patient details">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="patient-action is-danger js-remove" title="Remove from board" aria-label="Remove patient from board">
                    <i class="bi bi-person-dash"></i>
                </button>
                <span class="patient-action-spacer"></span>
                <button type="button" class="patient-notes-toggle js-notes" aria-expanded="false">
                    <i class="bi bi-chat-left-text"></i>
                    <span>Notes</span>
                    ${notesCount ? `<span class="count">${notesCount}</span>` : ''}
                </button>
            </div>
        `;

        el.querySelector('.js-edit').addEventListener('click', () => openPatientEdit(c));
        el.querySelector('.js-remove').addEventListener('click', () => openPatientRemove(c));
        el.querySelector('.js-notes').addEventListener('click', (e) => toggleNotes(el, c, e.currentTarget));

        return el;
    }

    function backToOverview() {
        if (location.hash) location.hash = '';
        else showOverview();
    }

    // =====================================================================
    //  Board create / edit / delete
    // =====================================================================
    function buildColorRow(selected) {
        const row = $('boardColorRow');
        if (!row) return;
        row.innerHTML = '';
        COLORS.forEach((c) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'board-color-dot' + (c === selected ? ' is-selected' : '');
            dot.style.background = c;
            dot.setAttribute('role', 'radio');
            dot.setAttribute('aria-checked', c === selected ? 'true' : 'false');
            dot.setAttribute('aria-label', 'Color');
            dot.addEventListener('click', () => {
                $('boardEditColor').value = c;
                row.querySelectorAll('.board-color-dot').forEach((d) => {
                    d.classList.remove('is-selected'); d.setAttribute('aria-checked', 'false');
                });
                dot.classList.add('is-selected'); dot.setAttribute('aria-checked', 'true');
                syncIconPreview();
            });
            row.appendChild(dot);
        });
    }

    function buildIconRow(selected) {
        const row = $('boardIconRow');
        if (!row) return;
        row.innerHTML = '';
        ICONS.forEach((ic) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'board-icon-swatch' + (ic === selected ? ' is-selected' : '');
            btn.innerHTML = '<i class="bi ' + ic + '"></i>';
            btn.setAttribute('role', 'radio');
            btn.setAttribute('aria-checked', ic === selected ? 'true' : 'false');
            btn.setAttribute('aria-label', ic.replace(/^bi-/, '').replace(/-/g, ' '));
            btn.title = btn.getAttribute('aria-label');
            btn.addEventListener('click', () => {
                $('boardEditIcon').value = ic;
                row.querySelectorAll('.board-icon-swatch').forEach((d) => {
                    d.classList.remove('is-selected'); d.setAttribute('aria-checked', 'false');
                });
                btn.classList.add('is-selected'); btn.setAttribute('aria-checked', 'true');
                syncIconPreview();
            });
            row.appendChild(btn);
        });
        syncIconPreview();
    }

    // Live preview chip inside the modal so the user sees what the
    // color + icon combination will look like before saving.
    function syncIconPreview() {
        const prev = $('boardEditPreview');
        if (!prev) return;
        const color = ($('boardEditColor') && $('boardEditColor').value) || '#0ea5e9';
        const icon  = ($('boardEditIcon')  && $('boardEditIcon').value)  || 'bi-kanban';
        prev.style.setProperty('--card-accent', color);
        prev.innerHTML = '<i class="bi ' + icon + '"></i>';
    }

    function openBoardEditor(board) {
        state.editingBoard = board || null;
        const err = $('boardEditError'); err.hidden = true; err.textContent = '';
        $('boardEditTitle').textContent = board ? 'Edit board' : 'New board';
        $('boardEditId').value = board ? board.id : '';
        $('boardEditName').value = board ? (board.name || '') : '';
        $('boardEditDesc').value = board ? (board.description || '') : '';
        const color = board ? (board.color || '#0ea5e9') : '#0ea5e9';
        const icon  = board && /^bi-[a-z0-9-]+$/.test(board.icon || '') ? board.icon : 'bi-kanban';
        $('boardEditColor').value = color;
        $('boardEditIcon').value  = icon;
        buildColorRow(color);
        buildIconRow(icon);

        const delBtn = $('boardEditDelete');
        delBtn.hidden = !(board && CAN_MANAGE && !board.is_default);
        modal('boardEditModal').show();
    }

    function saveBoard(ev) {
        ev.preventDefault();
        const err = $('boardEditError'); err.hidden = true;
        const id = $('boardEditId').value;
        const payload = {
            name: $('boardEditName').value.trim(),
            description: $('boardEditDesc').value.trim(),
            color: $('boardEditColor').value || '#0ea5e9',
            icon:  $('boardEditIcon').value  || 'bi-kanban',
        };
        if (!payload.name) { err.textContent = 'Board name is required.'; err.hidden = false; return; }

        const saveBtn = $('boardEditSave');
        saveBtn.disabled = true;
        const req = id
            ? api('/api/board/boards/' + id, { method: 'PUT', body: JSON.stringify(payload) })
            : api('/api/board/boards', { method: 'POST', body: JSON.stringify(payload) });

        req.then((res) => {
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to save');
            }
            modal('boardEditModal').hide();
            toast(id ? 'Board updated' : 'Board created', 'is-success');
            return loadBoards({ silent: true }).then(() => {
                if (state.current && String(state.current.id) === String(id)) {
                    const fresh = state.boards.find((b) => String(b.id) === String(id));
                    if (fresh) {
                        state.current = fresh;
                        detailName.textContent = fresh.name;
                        crumbName.textContent = fresh.name;
                        detailDesc.textContent = fresh.description || '';
                        detailDesc.hidden = !fresh.description;
                        applySwatchChip(detailSwatch, fresh);
                    }
                }
            });
        }).catch((e) => { err.textContent = e.message; err.hidden = false; })
          .finally(() => { saveBtn.disabled = false; });
    }

    function openBoardDelete() {
        const board = state.editingBoard;
        if (!board) return;
        state.pendingDeleteBoard = board;
        const err = $('boardDeleteError'); err.hidden = true;
        $('boardDeleteName').textContent = board.name;
        modal('boardEditModal').hide();
        modal('boardDeleteModal').show();
    }

    function confirmDeleteBoard() {
        const board = state.pendingDeleteBoard;
        if (!board) return;
        const err = $('boardDeleteError'); err.hidden = true;
        const btn = $('boardDeleteConfirm'); btn.disabled = true;
        api('/api/board/boards/' + board.id, { method: 'DELETE' }).then((res) => {
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to delete');
            }
            modal('boardDeleteModal').hide();
            toast('Board deleted', 'is-success');
            state.pendingDeleteBoard = null;
            if (state.current && state.current.id === board.id) backToOverview();
            else loadBoards({ silent: true });
        }).catch((e) => { err.textContent = e.message; err.hidden = false; })
          .finally(() => { btn.disabled = false; });
    }

    // =====================================================================
    //  Add patient to board
    // =====================================================================
    let addSearchDebounce = null;
    function openAddPatient() {
        if (!state.current) return;
        const err = $('addPatientError'); err.hidden = true;
        $('addPatientSearch').value = '';
        $('addPatientResults').innerHTML =
            '<p class="board-muted small text-center py-3 mb-0">Start typing to search for patients (at least 2 characters).</p>';
        modal('addPatientModal').show();
        setTimeout(() => $('addPatientSearch').focus(), 250);
    }

    function runAddSearch(q) {
        const box = $('addPatientResults');
        if (q.trim().length < 2) {
            box.innerHTML = '<p class="board-muted small text-center py-3 mb-0">Start typing to search for patients (at least 2 characters).</p>';
            return;
        }
        box.innerHTML = '<div class="add-loading"><i class="bi bi-arrow-repeat"></i> Searching…</div>';
        api('/api/patients/search?q=' + encodeURIComponent(q.trim())).then((res) => {
            if (!res.body || !res.body.ok) throw new Error((res.body && res.body.error) || 'Search failed');
            const list = res.body.data || [];
            if (list.length === 0) {
                box.innerHTML = '<p class="board-muted small text-center py-3 mb-0">No matching results.</p>';
                return;
            }
            const currentIds = new Set(state.cards.map((c) => c.patient_id));
            box.innerHTML = '';
            list.forEach((p) => {
                const name = p.full_name || `${p.first_name || ''} ${p.last_name || ''}`.trim();
                const inBoard = currentIds.has(parseInt(p.id, 10));
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'add-result' + (inBoard ? ' is-current' : '');
                const meta = [];
                if (p.phone) meta.push(p.phone);
                if (p.total_appointments != null) meta.push(`${p.total_appointments} visits`);
                btn.innerHTML = `
                    <span class="ar-avatar">${escapeHtml(initials(name))}</span>
                    <span class="ar-main">
                        <span class="ar-name">${escapeHtml(name || 'Unnamed')}</span>
                        <span class="ar-meta">${escapeHtml(meta.join(' · '))}</span>
                    </span>
                    <span class="ar-add"><i class="bi ${inBoard ? 'bi-check2-circle' : 'bi-plus-circle'}"></i></span>
                `;
                if (!inBoard) btn.addEventListener('click', () => addPatient(parseInt(p.id, 10)));
                box.appendChild(btn);
            });
        }).catch((e) => {
            box.innerHTML = `<p class="board-form-error" style="display:block">${escapeHtml(e.message)}</p>`;
        });
    }

    function addPatient(patientId) {
        if (!state.current) return;
        const err = $('addPatientError'); err.hidden = true;
        api(`/api/board/boards/${state.current.id}/patients`, {
            method: 'POST', body: JSON.stringify({ patient_id: patientId }),
        }).then((res) => {
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to add');
            }
            modal('addPatientModal').hide();
            toast('Patient added to board', 'is-success');
            loadCards();
        }).catch((e) => { err.textContent = e.message; err.hidden = false; });
    }

    // =====================================================================
    //  Quick-edit patient
    // =====================================================================
    function openPatientEdit(c) {
        const err = $('patientEditError'); err.hidden = true;
        $('patientEditId').value = c.patient_id;
        $('patientEditFirst').value = c.first_name || '';
        $('patientEditLast').value = c.last_name || '';
        $('patientEditPhone').value = c.phone || '';
        $('patientEditAltPhone').value = c.alt_phone || '';
        $('patientEditGender').value = (c.gender === 'Male' || c.gender === 'Female' || c.gender === 'Other') ? c.gender : 'Male';
        $('patientEditDob').value = c.dob || '';
        modal('patientEditModal').show();
    }

    function savePatientEdit(ev) {
        ev.preventDefault();
        const err = $('patientEditError'); err.hidden = true;
        const id = $('patientEditId').value;
        const payload = {
            first_name: $('patientEditFirst').value.trim(),
            last_name:  $('patientEditLast').value.trim(),
            phone:      $('patientEditPhone').value.trim(),
            alt_phone:  $('patientEditAltPhone').value.trim(),
            gender:     $('patientEditGender').value,
            dob:        $('patientEditDob').value,
        };
        if (!payload.first_name || !payload.last_name || !payload.phone) {
            err.textContent = 'First name, last name and phone are required.'; err.hidden = false; return;
        }
        const btn = $('patientEditSave'); btn.disabled = true;
        api('/api/board/patients/' + id, { method: 'PUT', body: JSON.stringify(payload) }).then((res) => {
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to save');
            }
            modal('patientEditModal').hide();
            toast('Patient details updated', 'is-success');
            loadCards();
        }).catch((e) => { err.textContent = e.message; err.hidden = false; })
          .finally(() => { btn.disabled = false; });
    }

    // =====================================================================
    //  Remove patient from board
    // =====================================================================
    function openPatientRemove(c) {
        state.pendingRemove = { patientId: c.patient_id, name: c.name };
        const err = $('patientRemoveError'); err.hidden = true;
        $('patientRemoveName').textContent = c.name || 'this patient';
        $('patientRemoveBoard').textContent = state.current ? state.current.name : '';
        modal('patientRemoveModal').show();
    }

    function confirmRemovePatient() {
        const p = state.pendingRemove;
        if (!p || !state.current) return;
        const err = $('patientRemoveError'); err.hidden = true;
        const btn = $('patientRemoveConfirm'); btn.disabled = true;
        api(`/api/board/boards/${state.current.id}/patients/${p.patientId}`, { method: 'DELETE' }).then((res) => {
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to remove');
            }
            modal('patientRemoveModal').hide();
            toast('Patient removed from board', 'is-success');
            state.pendingRemove = null;
            loadCards();
        }).catch((e) => { err.textContent = e.message; err.hidden = false; })
          .finally(() => { btn.disabled = false; });
    }

    // =====================================================================
    //  Per-patient notes (comments of type board_card)
    // =====================================================================
    function toggleNotes(cardEl, c, toggleBtn) {
        let panel = cardEl.querySelector('.patient-notes');
        if (panel) {
            const willShow = panel.hidden;
            panel.hidden = !willShow;
            toggleBtn.setAttribute('aria-expanded', willShow ? 'true' : 'false');
            return;
        }
        panel = document.createElement('div');
        panel.className = 'patient-notes';
        panel.innerHTML = `
            <div class="note-list" data-loading="1">
                <div class="note-empty"><i class="bi bi-arrow-repeat"></i> Loading…</div>
            </div>
            <div class="note-compose">
                <textarea class="note-input" rows="1" maxlength="4000"
                          placeholder="Add a note about this patient in this board…"
                          aria-label="Add a note"></textarea>
                <button type="button" class="note-send" aria-label="Send note" disabled>
                    <i class="bi bi-send"></i>
                </button>
            </div>
        `;
        cardEl.appendChild(panel);
        toggleBtn.setAttribute('aria-expanded', 'true');

        const input = panel.querySelector('.note-input');
        const send  = panel.querySelector('.note-send');

        // Rich composer: image/camera/audio + @-mention. Falls back to a plain
        // textarea if the shared module didn't load.
        let composer = null;
        if (window.CommentMedia) {
            composer = CommentMedia.attachComposer({
                textarea: input,
                getCsrf: () => CSRF,
                onError: (m) => toast(m, 'is-error'),
                onChange: () => { send.disabled = !composer.hasContent() || composer.isUploading(); },
                onSubmit: () => addNote(c, panel, input, send, toggleBtn)
            });
        }
        panel._composer = composer;

        input.addEventListener('input', () => {
            send.disabled = composer ? (!composer.hasContent() || composer.isUploading()) : input.value.trim() === '';
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); addNote(c, panel, input, send, toggleBtn); }
        });
        send.addEventListener('click', () => addNote(c, panel, input, send, toggleBtn));

        loadNotes(c, panel);
    }

    function loadNotes(c, panel) {
        const listEl = panel.querySelector('.note-list');
        api('/api/comments/board_card/' + c.patient_id).then((res) => {
            if (!res.body || !res.body.ok) throw new Error('Failed to load notes');
            const rows = (res.body.data || []).filter((r) => !r.deleted_at);
            renderNotes(listEl, rows, c);
        }).catch(() => {
            listEl.innerHTML = '<div class="note-empty">Failed to load notes</div>';
        });
    }

    function renderNotes(listEl, rows, c, expanded) {
        listEl.removeAttribute('data-loading');
        if (rows.length === 0) {
            listEl.innerHTML = '<div class="note-empty">No notes yet — add the first one.</div>';
            return;
        }
        const LIMIT = 3;
        const showAll = expanded || rows.length <= LIMIT;
        const shown = showAll ? rows : rows.slice(rows.length - LIMIT);
        listEl.innerHTML = '';

        if (!showAll) {
            const more = document.createElement('button');
            more.type = 'button';
            more.className = 'note-more';
            more.textContent = `Show ${rows.length - LIMIT} older ${rows.length - LIMIT === 1 ? 'note' : 'notes'}`;
            more.addEventListener('click', () => renderNotes(listEl, rows, c, true));
            listEl.appendChild(more);
        }

        shown.forEach((r) => {
            const item = document.createElement('div');
            item.className = 'note-item';
            const canDelete = r.can_edit;
            const src = profileSrc(r.author_image);
            const avatar = src
                ? `<img class="note-avatar" src="${escapeAttr(src)}" alt="" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'note-avatar note-avatar--initials',textContent:'${escapeAttr(initials(r.author_name))}'}))">`
                : `<span class="note-avatar note-avatar--initials">${escapeHtml(initials(r.author_name))}</span>`;
            item.innerHTML = `
                <div class="note-top">
                    ${avatar}
                    <div class="note-id">
                        <span class="note-author">${escapeHtml(r.author_name || 'User')}</span>
                        <span class="note-time"><i class="bi bi-clock"></i> ${escapeHtml(commentDateTime(r.created_at))}${r.edited_at ? ' · edited' : ''}</span>
                    </div>
                    ${canDelete ? '<button type="button" class="note-del" aria-label="Delete note" title="Delete"><i class="bi bi-trash"></i></button>' : ''}
                </div>
                <div class="note-body">${window.CommentMedia ? CommentMedia.renderBody(r.body, r.mentions) : linkifyNote(r.body)}</div>
                ${window.CommentMedia ? CommentMedia.renderAttachments(r.attachments) : ''}
            `;
            const del = item.querySelector('.note-del');
            if (del) del.addEventListener('click', async () => {
                const ok = typeof window.mkConfirmModal === 'function'
                    ? await window.mkConfirmModal({
                        title: 'Delete note?',
                        message: 'This board note will be removed. This action cannot be undone.',
                        confirmText: 'Delete',
                        confirmClass: 'btn-danger',
                        icon: 'bi-trash',
                    })
                    : window.confirm('Delete this note?');
                if (ok) deleteNote(r.id, listEl, c);
            });
            listEl.appendChild(item);
        });
    }

    function addNote(c, panel, input, send, toggleBtn) {
        const composer = panel._composer;
        const body = composer ? composer.getBody() : input.value.trim();
        const attachmentIds = composer ? composer.getAttachmentIds() : [];
        if (!body && !attachmentIds.length) return;
        if (composer && composer.isUploading()) { toast('Wait for the upload to finish', 'is-error'); return; }
        send.disabled = true;
        api('/api/comments/board_card/' + c.patient_id, {
            method: 'POST', body: JSON.stringify({ body: body, attachment_ids: attachmentIds }),
        }).then((res) => {
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to add note');
            }
            if (composer) composer.reset(); else { input.value = ''; }
            input.style.height = 'auto';
            c.notes_count = (c.notes_count || 0) + 1;
            updateNotesBadge(toggleBtn, c.notes_count);
            loadNotes(c, panel);
        }).catch((e) => { toast(e.message, 'is-error'); send.disabled = false; });
    }

    function deleteNote(id, listEl, c) {
        api('/api/comments/' + id, { method: 'DELETE' }).then((res) => {
            if (res.status >= 400 || !res.body || !res.body.ok) {
                throw new Error((res.body && res.body.error) || 'Failed to delete');
            }
            c.notes_count = Math.max(0, (c.notes_count || 1) - 1);
            const card = document.getElementById('patient-' + c.patient_id);
            if (card) updateNotesBadge(card.querySelector('.js-notes'), c.notes_count);
            const panel = listEl.closest('.patient-notes');
            if (panel) loadNotes(c, panel);
        }).catch((e) => toast(e.message, 'is-error'));
    }

    function updateNotesBadge(toggleBtn, count) {
        if (!toggleBtn) return;
        let badge = toggleBtn.querySelector('.count');
        if (count > 0) {
            if (!badge) { badge = document.createElement('span'); badge.className = 'count'; toggleBtn.appendChild(badge); }
            badge.textContent = count;
        } else if (badge) { badge.remove(); }
    }

    // =====================================================================
    //  Wire events
    // =====================================================================
    let boardsSearchDebounce = null;
    boardsSearch.addEventListener('input', () => {
        clearTimeout(boardsSearchDebounce);
        boardsSearchDebounce = setTimeout(() => {
            state.boardsQuery = boardsSearch.value;
            renderBoards();
        }, 160);
    });

    let patientsSearchDebounce = null;
    patientsSearch.addEventListener('input', () => {
        clearTimeout(patientsSearchDebounce);
        patientsSearchDebounce = setTimeout(() => {
            state.patientsQuery = patientsSearch.value.trim();
            loadCards();
        }, 240);
    });

    patientsSort.addEventListener('change', () => {
        state.patientsSort = patientsSort.value;
        loadCards();
    });

    $('boardBackBtn').addEventListener('click', backToOverview);
    $('boardCrumbRoot').addEventListener('click', backToOverview);

    const createBtn = $('boardCreateBtn');
    if (createBtn) createBtn.addEventListener('click', () => openBoardEditor(null));
    const emptyCreate = $('boardsEmptyCreateBtn');
    if (emptyCreate) emptyCreate.addEventListener('click', () => openBoardEditor(null));
    if (!CAN_MANAGE && createBtn) createBtn.style.display = 'none';

    $('boardEditForm').addEventListener('submit', saveBoard);
    $('boardEditDelete').addEventListener('click', openBoardDelete);
    $('boardDeleteConfirm').addEventListener('click', confirmDeleteBoard);

    $('boardEditBtn').addEventListener('click', () => { if (state.current) openBoardEditor(state.current); });
    $('patientAddBtn').addEventListener('click', openAddPatient);
    $('patientsEmptyAddBtn').addEventListener('click', openAddPatient);

    $('addPatientSearch').addEventListener('input', (e) => {
        clearTimeout(addSearchDebounce);
        const v = e.target.value;
        addSearchDebounce = setTimeout(() => runAddSearch(v), 280);
    });

    $('patientEditForm').addEventListener('submit', savePatientEdit);
    $('patientRemoveConfirm').addEventListener('click', confirmRemovePatient);

    window.addEventListener('hashchange', routeFromHash);

    // =====================================================================
    //  Card-size control (Compact / Default / Large) — persisted locally.
    // =====================================================================
    const SIZE_KEY = 'boardCardSize';
    const boardPage = $('boardPage');
    function applyCardSize(sz) {
        const s = ['sm', 'md', 'lg'].includes(sz) ? sz : 'md';
        if (boardPage) boardPage.dataset.size = s;
        document.querySelectorAll('.board-size-btn').forEach((b) => {
            const on = b.dataset.size === s;
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        try { localStorage.setItem(SIZE_KEY, s); } catch (_) {}
    }
    document.querySelectorAll('.board-size-btn').forEach((b) => {
        b.addEventListener('click', () => applyCardSize(b.dataset.size));
    });
    applyCardSize((function () {
        try { return localStorage.getItem(SIZE_KEY) || 'md'; } catch (_) { return 'md'; }
    })());

    // =====================================================================
    //  Init — load boards once, then honor a deep-link hash if present.
    // =====================================================================
    loadBoards().then(() => {
        const m = (location.hash || '').match(/^#board-(\d+)$/);
        if (m) openBoardById(parseInt(m[1], 10));
    });
})();
