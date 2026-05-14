/**
 * Draw Consultation
 * --------------------------------------------------------------------
 * Fabric.js-powered drawing modal. Used in two places:
 *   1. Appointment page  → saves into patient_attachments
 *   2. Patient profile   → saves into patient_files
 *
 * Each invocation accepts a context config so the same modal can target either
 * endpoint without duplication. EVERY call to .open() starts a brand-new
 * session — fresh canvas, no carry-over attachment id — even if a previous
 * session existed.
 *
 * Public API:
 *   DrawConsultation.open({ context })   // explicit context (preferred)
 *   DrawConsultation.open()              // legacy: falls back to APPOINTMENT_CONFIG
 *
 * Context shape:
 *   {
 *     uploadUrl:       '/api/...',           // POST URL for first save
 *     replaceUrl:      '/api/.../{id}',      // POST URL for subsequent saves; {id} placeholder
 *     fileField:       'attachment_file',    // multipart field name
 *     fields:          { ... },              // extra fields for the first save only
 *     idResponseKey:   'attachment_id',      // key on the JSON response holding the new id
 *     onSaved:         () => void,           // callback after every successful save
 *     title:           'Draw Consultation',  // optional modal title
 *     filename:        'consultation_drawing.png', // optional name sent to the server
 *   }
 *
 * Depends on Fabric.js v5 + Bootstrap 5.
 */

(function () {
    if (window.DrawConsultation) return;

    const AUTO_SAVE_MS = 30000;
    const HISTORY_LIMIT = 60;
    const MIN_SHAPE_SIZE = 4;
    const CANVAS_BG = '#ffffff';

    // ---- State -----------------------------------------------------------------
    let canvas = null;             // Fabric canvas
    let modalEl = null;
    let modal = null;
    let canvasInitialized = false;

    // Per-session state — fully reset on every open()
    let session = blankSession();

    // Tool defaults (persist across sessions; nicer UX than re-resetting every time)
    let currentTool = 'pencil';
    let strokeColor = '#0f172a';
    let fillColor = 'transparent';
    let strokeWidth = 3;
    let autoSaveTimer = null;
    let shapeDraft = null;
    let shapeStart = null;
    let lockHistory = false;

    function blankSession() {
        return {
            context: null,             // active context (see header docs)
            currentAttachmentId: null,
            isDirty: false,
            isSaving: false,
            history: [],
            future: [],
        };
    }

    function defaultAppointmentContext() {
        const cfg = window.APPOINTMENT_CONFIG || {};
        return {
            uploadUrl: '/api/attachments/upload',
            replaceUrl: '/api/attachments/replace/{id}',
            fileField: 'attachment_file',
            fields: {
                appointment_id: cfg.appointmentId,
                patient_id: cfg.patientId,
                attachment_type: 'photo',
                description: 'Consultation drawing',
            },
            idResponseKey: 'attachment_id',
            onSaved: () => { if (typeof window.reloadAttachments === 'function') window.reloadAttachments(); },
            title: 'Draw Consultation',
            filename: 'consultation_drawing.png',
        };
    }

    // ---- Public entry point ----------------------------------------------------
    function open(opts) {
        const context = (opts && opts.context) ? opts.context : defaultAppointmentContext();
        if (!context.uploadUrl) {
            console.error('DrawConsultation.open: context.uploadUrl is required');
            return;
        }
        ensureModal();

        // Start a brand-new session every time the button is clicked.
        // (Reset BEFORE the modal becomes visible so dirty state from a previous
        // session never sneaks into a save call.)
        session = blankSession();
        session.context = context;
        resetCanvasContent();
        setAutoSaveStatus('idle');
        applyContextToModal();
        refreshHistoryButtons();

        // Hide the global quick-access dock while drawing so it can't overlap
        // the toolbar / Save button. Restored on hidden.bs.modal.
        document.body.classList.add('draw-modal-open');

        modal.show();
        modalEl.addEventListener('shown.bs.modal', onModalShown, { once: true });
    }

    function onModalShown() {
        initCanvasOnce();
        resizeCanvas();
        // Push the (blank) state as the first history entry of this session.
        session.history = [];
        session.future = [];
        pushHistory();
        startAutoSave();
    }

    function initCanvasOnce() {
        if (canvasInitialized) return;
        canvasInitialized = true;
        const el = document.getElementById('drawCanvas');
        canvas = new fabric.Canvas(el, {
            backgroundColor: CANVAS_BG,
            selection: false,
            preserveObjectStacking: true,
            isDrawingMode: true,
        });
        canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
        applyBrush();

        window.addEventListener('resize', resizeCanvas);

        canvas.on('path:created',     () => { session.isDirty = true; pushHistory(); });
        canvas.on('object:modified',  () => { session.isDirty = true; pushHistory(); });
        canvas.on('object:removed',   () => { session.isDirty = true; });
        canvas.on('selection:created', updatePropPanelFromSelection);
        canvas.on('selection:updated', updatePropPanelFromSelection);

        // Shape-drawing handlers
        canvas.on('mouse:down', handleShapeMouseDown);
        canvas.on('mouse:move', handleShapeMouseMove);
        canvas.on('mouse:up',   handleShapeMouseUp);

        setTool(currentTool);
    }

    function resetCanvasContent() {
        if (!canvas) return;
        canvas.clear();
        canvas.backgroundColor = CANVAS_BG;
        canvas.renderAll();
    }

    function applyContextToModal() {
        const titleEl = modalEl.querySelector('#drawConsultationTitle');
        if (titleEl && session.context && session.context.title) {
            titleEl.innerHTML = `<i class="bi bi-pencil-square me-2 text-primary"></i>${session.context.title}`;
        }
    }

    // ---- Modal scaffold (built once) -------------------------------------------
    function ensureModal() {
        if (modalEl) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = renderModalHtml();
        modalEl = wrapper.firstElementChild;
        document.body.appendChild(modalEl);
        modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        bindToolbarEvents();

        // Always stop the timer when the modal closes; do NOT remove the listener
        // so it keeps firing across reopen cycles.
        modalEl.addEventListener('hidden.bs.modal', () => {
            stopAutoSave();
            // Clear visible canvas so the next quick reopen never flashes old strokes.
            resetCanvasContent();
            // Restore the global dock that was hidden in open().
            document.body.classList.remove('draw-modal-open');
        });
    }

    function renderModalHtml() {
        return `
<div class="modal fade" id="drawConsultationModal" tabindex="-1" aria-labelledby="drawConsultationTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="drawConsultationTitle">
          <i class="bi bi-pencil-square me-2 text-primary"></i>Draw Consultation
        </h5>
        <span class="draw-auto-save-indicator" id="drawAutoSaveStatus">
          <i class="bi bi-cloud"></i>
          <span>Not saved yet</span>
        </span>
        <button type="button" class="btn-close ms-2" aria-label="Close" id="drawCloseBtn"></button>
      </div>
      <div class="modal-body draw-modal-body">
        <div class="draw-toolbar" role="toolbar" aria-label="Drawing tools">
          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Tools</span>
            <button type="button" class="draw-tool-btn" data-tool="select" title="Select / Move (V)"><i class="bi bi-cursor-fill"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="pencil" title="Pencil (P)"><i class="bi bi-pencil"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="pen" title="Pen (B)"><i class="bi bi-pen"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="marker" title="Marker (M)"><i class="bi bi-brush-fill"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="eraser" title="Eraser (E)"><i class="bi bi-eraser"></i></button>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Shapes</span>
            <button type="button" class="draw-tool-btn" data-tool="rect" title="Rectangle (R)"><i class="bi bi-square"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="circle" title="Circle (C)"><i class="bi bi-circle"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="triangle" title="Triangle (T)"><i class="bi bi-triangle"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="line" title="Line (L)"><i class="bi bi-slash-lg"></i></button>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Stroke</span>
            <input type="color" class="draw-color-input" id="drawStrokeColor" value="#0f172a" title="Stroke color">
            <div class="draw-stroke-preview" aria-hidden="true"><div class="draw-stroke-preview__dot" id="drawStrokePreview"></div></div>
            <input type="range" class="draw-stroke-slider" id="drawStrokeWidth" min="1" max="40" value="3" title="Stroke width">
          </div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Fill</span>
            <input type="color" class="draw-color-input" id="drawFillColor" value="#ffffff" title="Fill color">
            <button type="button" class="draw-tool-btn" id="drawFillTransparent" title="No fill"><i class="bi bi-slash-circle"></i></button>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Edit</span>
            <button type="button" class="draw-tool-btn" id="drawUndoBtn" title="Undo (Ctrl+Z)"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button type="button" class="draw-tool-btn" id="drawRedoBtn" title="Redo (Ctrl+Shift+Z)"><i class="bi bi-arrow-clockwise"></i></button>
            <button type="button" class="draw-tool-btn" id="drawDeleteBtn" title="Delete selected"><i class="bi bi-trash"></i></button>
            <button type="button" class="draw-tool-btn" id="drawClearBtn" title="Clear all"><i class="bi bi-x-circle"></i></button>
          </div>
        </div>

        <div class="draw-canvas-wrap" id="drawCanvasWrap">
          <div class="draw-canvas-shell">
            <canvas id="drawCanvas"></canvas>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <span class="text-muted small" id="drawHintText">Tip: select an object to change its colors and size, or use Delete to remove it.</span>
        <button type="button" class="btn btn-secondary" id="drawCancelBtn">Close</button>
        <button type="button" class="btn btn-success" id="drawSaveBtn">
          <i class="bi bi-cloud-arrow-up me-1"></i><span>Save now</span>
        </button>
      </div>
    </div>
  </div>
</div>`;
    }

    // ---- Toolbar wiring --------------------------------------------------------
    function bindToolbarEvents() {
        modalEl.querySelectorAll('[data-tool]').forEach(btn => {
            btn.addEventListener('click', () => setTool(btn.dataset.tool));
        });

        const strokeInput = modalEl.querySelector('#drawStrokeColor');
        strokeInput.addEventListener('input', () => {
            strokeColor = strokeInput.value;
            applyBrush();
            updateStrokePreview();
            applyToSelection({ stroke: strokeColor });
        });

        const widthInput = modalEl.querySelector('#drawStrokeWidth');
        widthInput.addEventListener('input', () => {
            strokeWidth = parseInt(widthInput.value, 10) || 1;
            applyBrush();
            updateStrokePreview();
            applyToSelection({ strokeWidth: strokeWidth });
        });

        const fillInput = modalEl.querySelector('#drawFillColor');
        fillInput.addEventListener('input', () => {
            fillColor = fillInput.value;
            applyToSelection({ fill: fillColor });
            fillInput.classList.remove('is-transparent');
        });

        modalEl.querySelector('#drawFillTransparent').addEventListener('click', () => {
            fillColor = 'transparent';
            applyToSelection({ fill: 'transparent' });
            fillInput.classList.add('is-transparent');
        });

        modalEl.querySelector('#drawUndoBtn').addEventListener('click', undo);
        modalEl.querySelector('#drawRedoBtn').addEventListener('click', redo);
        modalEl.querySelector('#drawDeleteBtn').addEventListener('click', deleteSelected);
        modalEl.querySelector('#drawClearBtn').addEventListener('click', clearAll);

        modalEl.querySelector('#drawSaveBtn').addEventListener('click', () => save({ silent: false }));
        modalEl.querySelector('#drawCancelBtn').addEventListener('click', () => modal.hide());
        modalEl.querySelector('#drawCloseBtn').addEventListener('click', () => modal.hide());

        document.addEventListener('keydown', onKeydown);
        updateStrokePreview();
    }

    function onKeydown(e) {
        if (!modalEl.classList.contains('show')) return;
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === 'z') { e.preventDefault(); undo(); return; }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) { e.preventDefault(); redo(); return; }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save({ silent: false }); return; }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (canvas && canvas.getActiveObject()) { e.preventDefault(); deleteSelected(); }
            return;
        }

        const map = { v: 'select', p: 'pencil', b: 'pen', m: 'marker', e: 'eraser', r: 'rect', c: 'circle', t: 'triangle', l: 'line' };
        const key = e.key.toLowerCase();
        if (map[key]) { e.preventDefault(); setTool(map[key]); }
    }

    // ---- Tool dispatch ---------------------------------------------------------
    function setTool(tool) {
        currentTool = tool;
        modalEl.querySelectorAll('[data-tool]').forEach(b => b.classList.toggle('is-active', b.dataset.tool === tool));
        if (!canvas) return;

        const drawingTools = ['pencil', 'pen', 'marker', 'eraser'];
        const shapeTools = ['rect', 'circle', 'triangle', 'line'];

        if (drawingTools.includes(tool)) {
            canvas.isDrawingMode = true;
            canvas.selection = false;
            canvas.discardActiveObject();
            applyBrush();
        } else if (shapeTools.includes(tool)) {
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.discardActiveObject();
        } else { // select
            canvas.isDrawingMode = false;
            canvas.selection = true;
        }
        canvas.renderAll();
    }

    function applyBrush() {
        if (!canvas || !canvas.freeDrawingBrush) return;
        const brush = canvas.freeDrawingBrush;
        switch (currentTool) {
            case 'pencil':
                brush.color = strokeColor;
                brush.width = Math.max(1, strokeWidth - 1);
                brush.strokeLineCap = 'round';
                brush.strokeLineJoin = 'round';
                canvas.freeDrawingCursor = 'crosshair';
                break;
            case 'pen':
                brush.color = strokeColor;
                brush.width = strokeWidth + 1;
                brush.strokeLineCap = 'round';
                brush.strokeLineJoin = 'round';
                canvas.freeDrawingCursor = 'crosshair';
                break;
            case 'marker':
                brush.color = hexToRgba(strokeColor, 0.35);
                brush.width = strokeWidth * 3;
                brush.strokeLineCap = 'square';
                brush.strokeLineJoin = 'miter';
                canvas.freeDrawingCursor = 'crosshair';
                break;
            case 'eraser':
                brush.color = CANVAS_BG;
                brush.width = Math.max(8, strokeWidth * 3);
                brush.strokeLineCap = 'round';
                brush.strokeLineJoin = 'round';
                canvas.freeDrawingCursor = 'cell';
                break;
        }
    }

    // ---- Shape drawing ---------------------------------------------------------
    function handleShapeMouseDown(opt) {
        if (canvas.isDrawingMode) return;
        if (!['rect', 'circle', 'triangle', 'line'].includes(currentTool)) return;
        const p = canvas.getPointer(opt.e);
        shapeStart = { x: p.x, y: p.y };
        const common = {
            left: p.x,
            top: p.y,
            originX: 'left',
            originY: 'top',
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            fill: fillColor === 'transparent' ? '' : fillColor,
            selectable: true,
            objectCaching: false,
        };
        if (currentTool === 'rect')          shapeDraft = new fabric.Rect({ ...common, width: 1, height: 1 });
        else if (currentTool === 'circle')   shapeDraft = new fabric.Ellipse({ ...common, rx: 1, ry: 1 });
        else if (currentTool === 'triangle') shapeDraft = new fabric.Triangle({ ...common, width: 1, height: 1 });
        else if (currentTool === 'line')     shapeDraft = new fabric.Line([p.x, p.y, p.x, p.y], { stroke: strokeColor, strokeWidth: strokeWidth, selectable: true });

        canvas.add(shapeDraft);
        canvas.requestRenderAll();
    }

    function handleShapeMouseMove(opt) {
        if (!shapeDraft || !shapeStart) return;
        const p = canvas.getPointer(opt.e);
        const w = p.x - shapeStart.x;
        const h = p.y - shapeStart.y;
        if (shapeDraft.type === 'rect' || shapeDraft.type === 'triangle') {
            shapeDraft.set({
                left: w < 0 ? p.x : shapeStart.x,
                top:  h < 0 ? p.y : shapeStart.y,
                width:  Math.max(1, Math.abs(w)),
                height: Math.max(1, Math.abs(h)),
            });
        } else if (shapeDraft.type === 'ellipse') {
            shapeDraft.set({
                left: w < 0 ? p.x : shapeStart.x,
                top:  h < 0 ? p.y : shapeStart.y,
                rx:   Math.max(1, Math.abs(w) / 2),
                ry:   Math.max(1, Math.abs(h) / 2),
            });
        } else if (shapeDraft.type === 'line') {
            shapeDraft.set({ x2: p.x, y2: p.y });
        }
        canvas.requestRenderAll();
    }

    function handleShapeMouseUp() {
        if (!shapeDraft) return;
        const bb = shapeDraft.getBoundingRect();
        if (bb.width < MIN_SHAPE_SIZE && bb.height < MIN_SHAPE_SIZE) {
            canvas.remove(shapeDraft);
        } else {
            shapeDraft.setCoords();
            session.isDirty = true;
            pushHistory();
        }
        shapeDraft = null;
        shapeStart = null;
        setTool('select');
        if (canvas.getObjects().length) {
            canvas.setActiveObject(canvas.getObjects().slice(-1)[0]);
            canvas.requestRenderAll();
        }
    }

    // ---- Selection editing -----------------------------------------------------
    function applyToSelection(props) {
        if (!canvas) return;
        const active = canvas.getActiveObject();
        if (!active) return;
        active.set(props);
        canvas.requestRenderAll();
        session.isDirty = true;
        pushHistory();
    }

    function updatePropPanelFromSelection() {
        const active = canvas.getActiveObject();
        if (!active) return;
        if (active.stroke && typeof active.stroke === 'string' && active.stroke.startsWith('#')) {
            modalEl.querySelector('#drawStrokeColor').value = active.stroke;
        }
        if (typeof active.strokeWidth === 'number') {
            modalEl.querySelector('#drawStrokeWidth').value = active.strokeWidth;
            strokeWidth = active.strokeWidth;
            updateStrokePreview();
        }
        const fillInput = modalEl.querySelector('#drawFillColor');
        if (active.fill && active.fill !== 'transparent' && active.fill !== '' && typeof active.fill === 'string' && active.fill.startsWith('#')) {
            fillInput.value = active.fill;
            fillInput.classList.remove('is-transparent');
        }
    }

    function deleteSelected() {
        if (!canvas) return;
        const active = canvas.getActiveObjects();
        if (!active || !active.length) return;
        active.forEach(o => canvas.remove(o));
        canvas.discardActiveObject();
        canvas.requestRenderAll();
        session.isDirty = true;
        pushHistory();
    }

    function clearAll() {
        if (!canvas) return;
        if (!canvas.getObjects().length) return;
        if (!confirm('Clear the entire canvas?')) return;
        canvas.clear();
        canvas.backgroundColor = CANVAS_BG;
        canvas.renderAll();
        session.isDirty = true;
        pushHistory();
    }

    // ---- History (undo / redo) -------------------------------------------------
    function pushHistory() {
        if (lockHistory || !canvas) return;
        const snapshot = JSON.stringify(canvas.toJSON());
        if (session.history.length && session.history[session.history.length - 1] === snapshot) return;
        session.history.push(snapshot);
        if (session.history.length > HISTORY_LIMIT) session.history.shift();
        session.future = [];
        refreshHistoryButtons();
    }

    function undo() {
        if (!canvas || session.history.length < 2) return;
        session.future.push(session.history.pop());
        loadFromSnapshot(session.history[session.history.length - 1]);
        refreshHistoryButtons();
    }

    function redo() {
        if (!canvas || session.future.length === 0) return;
        const target = session.future.pop();
        session.history.push(target);
        loadFromSnapshot(target);
        refreshHistoryButtons();
    }

    function loadFromSnapshot(snapshot) {
        lockHistory = true;
        canvas.loadFromJSON(snapshot, () => {
            canvas.renderAll();
            lockHistory = false;
            session.isDirty = true;
        });
    }

    function refreshHistoryButtons() {
        const undoBtn = modalEl && modalEl.querySelector('#drawUndoBtn');
        const redoBtn = modalEl && modalEl.querySelector('#drawRedoBtn');
        if (undoBtn) undoBtn.disabled = session.history.length < 2;
        if (redoBtn) redoBtn.disabled = session.future.length === 0;
    }

    // ---- Save / auto-save ------------------------------------------------------
    function startAutoSave() {
        stopAutoSave();
        autoSaveTimer = setInterval(() => save({ silent: true }), AUTO_SAVE_MS);
    }
    function stopAutoSave() {
        if (autoSaveTimer) clearInterval(autoSaveTimer);
        autoSaveTimer = null;
    }

    async function save({ silent }) {
        if (!canvas || session.isSaving) return;

        const ctx = session.context;
        if (!ctx) return;

        if (!session.isDirty && session.currentAttachmentId) {
            if (!silent) setAutoSaveStatus('saved');
            return;
        }
        if (!canvas.getObjects().length && !session.currentAttachmentId) {
            if (!silent) setAutoSaveStatus('error', 'Draw something first.');
            return;
        }

        session.isSaving = true;
        setAutoSaveStatus('saving');

        try {
            const blob = await canvasToBlob();
            const fd = new FormData();
            fd.append(ctx.fileField || 'attachment_file', blob, ctx.filename || 'drawing.png');

            let url;
            if (session.currentAttachmentId) {
                url = (ctx.replaceUrl || '').replace('{id}', String(session.currentAttachmentId));
                if (!url) throw new Error('Replace URL not configured');
            } else {
                url = ctx.uploadUrl;
                const fields = ctx.fields || {};
                Object.keys(fields).forEach(k => {
                    if (fields[k] !== undefined && fields[k] !== null) {
                        fd.append(k, String(fields[k]));
                    }
                });
            }

            const res = await fetch(url, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (!json || !json.success) {
                // If the remote row is gone (e.g. user deleted it), drop our cached id so
                // the very next save creates a fresh attachment instead of replaying the 404.
                if (res.status === 404 && session.currentAttachmentId) {
                    session.currentAttachmentId = null;
                }
                throw new Error(json && json.message ? json.message : `Save failed (HTTP ${res.status})`);
            }
            const idKey = ctx.idResponseKey || 'attachment_id';
            if (json[idKey]) session.currentAttachmentId = json[idKey];
            session.isDirty = false;
            setAutoSaveStatus('saved');
            if (typeof ctx.onSaved === 'function') {
                try { ctx.onSaved(); } catch (e) { /* ignore */ }
            }
        } catch (err) {
            console.error('DrawConsultation save failed:', err);
            setAutoSaveStatus('error', err.message || 'Save failed');
        } finally {
            session.isSaving = false;
        }
    }

    function canvasToBlob() {
        return new Promise((resolve, reject) => {
            try {
                const dataUrl = canvas.toDataURL({ format: 'png', multiplier: 1 });
                fetch(dataUrl).then(r => r.blob()).then(resolve).catch(reject);
            } catch (e) { reject(e); }
        });
    }

    function setAutoSaveStatus(state, message) {
        const el = modalEl && modalEl.querySelector('#drawAutoSaveStatus');
        if (!el) return;
        el.classList.remove('is-saving', 'is-saved', 'is-error');
        if (state === 'saving') {
            el.classList.add('is-saving');
            el.innerHTML = '<i class="bi bi-arrow-repeat"></i><span>Saving…</span>';
        } else if (state === 'saved') {
            el.classList.add('is-saved');
            const time = new Date().toLocaleTimeString();
            el.innerHTML = `<i class="bi bi-cloud-check"></i><span>Saved at ${time}</span>`;
        } else if (state === 'error') {
            el.classList.add('is-error');
            el.innerHTML = `<i class="bi bi-exclamation-triangle"></i><span>${message || 'Save failed'}</span>`;
        } else {
            el.innerHTML = '<i class="bi bi-cloud"></i><span>Not saved yet</span>';
        }
    }

    // ---- Resize & helpers ------------------------------------------------------
    function resizeCanvas() {
        if (!canvas) return;
        const wrap = document.getElementById('drawCanvasWrap');
        if (!wrap) return;
        const padding = 32;
        const w = Math.max(320, wrap.clientWidth  - padding);
        const h = Math.max(240, wrap.clientHeight - padding);
        canvas.setWidth(w);
        canvas.setHeight(h);
        canvas.calcOffset();
        canvas.renderAll();
    }

    function updateStrokePreview() {
        const dot = modalEl && modalEl.querySelector('#drawStrokePreview');
        if (!dot) return;
        const size = Math.max(2, Math.min(28, strokeWidth));
        dot.style.width  = size + 'px';
        dot.style.height = size + 'px';
        dot.style.setProperty('--draw-stroke-color', strokeColor);
        dot.style.background = strokeColor;
    }

    function hexToRgba(hex, alpha) {
        const m = /^#?([a-f0-9]{6}|[a-f0-9]{3})$/i.exec(hex || '');
        if (!m) return hex;
        let h = m[1];
        if (h.length === 3) h = h.split('').map(c => c + c).join('');
        const r = parseInt(h.slice(0, 2), 16);
        const g = parseInt(h.slice(2, 4), 16);
        const b = parseInt(h.slice(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    // ---- Convenience builders for callers --------------------------------------
    function openForAppointment(appointmentId, patientId) {
        open({
            context: {
                uploadUrl: '/api/attachments/upload',
                replaceUrl: '/api/attachments/replace/{id}',
                fileField: 'attachment_file',
                fields: {
                    appointment_id: appointmentId,
                    patient_id: patientId,
                    attachment_type: 'photo',
                    description: 'Consultation drawing',
                },
                idResponseKey: 'attachment_id',
                onSaved: () => { if (typeof window.reloadAttachments === 'function') window.reloadAttachments(); },
                title: 'Draw Consultation',
                filename: 'consultation_drawing.png',
            }
        });
    }

    function openForPatient(patientId) {
        open({
            context: {
                uploadUrl: '/api/patients/files/upload',
                replaceUrl: '/api/patients/files/replace/{id}',
                fileField: 'patient_file',
                fields: {
                    patient_id: patientId,
                    file_type: 'photo',
                    description: 'Patient drawing',
                },
                idResponseKey: 'file_id',
                onSaved: () => { if (typeof window.reloadPatientFiles === 'function') window.reloadPatientFiles(); },
                title: 'Draw on Patient File',
                filename: 'patient_drawing.png',
            }
        });
    }

    window.DrawConsultation = { open, openForAppointment, openForPatient };
})();

/* ===========================================================================
   Shared bulk-delete confirmation modal.
   Matches the project's existing delete modals (bg-danger header + warning).
   Used by both the appointment attachments list and the patient files list.
   =========================================================================== */
(function () {
    if (window.openBulkDeleteConfirmModal) return;

    let modalEl = null;
    let modal = null;
    let pending = null;

    function ensure() {
        if (modalEl) return;
        const wrap = document.createElement('div');
        wrap.innerHTML = `
<div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <span id="bulkDeleteTitle">Confirm Bulk Deletion</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger d-flex align-items-start" role="alert">
          <i class="bi bi-shield-exclamation fs-3 me-3"></i>
          <div>
            <h6 class="alert-heading mb-2">Warning!</h6>
            <p class="mb-0" id="bulkDeleteBody">You are about to delete the selected items permanently. This action <strong>cannot be undone</strong>.</p>
          </div>
        </div>
        <div class="text-center text-danger fw-bold mt-3" id="bulkDeleteCountLine"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-danger" id="bulkDeleteConfirmBtn">
          <i class="bi bi-trash me-1"></i><span class="btn-text">Delete</span>
          <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
        </button>
      </div>
    </div>
  </div>
</div>`;
        modalEl = wrap.firstElementChild;
        document.body.appendChild(modalEl);
        modal = new bootstrap.Modal(modalEl);

        modalEl.querySelector('#bulkDeleteConfirmBtn').addEventListener('click', async () => {
            if (!pending) return;
            const btn = modalEl.querySelector('#bulkDeleteConfirmBtn');
            const spinner = btn.querySelector('.spinner-border');
            const text = btn.querySelector('.btn-text');
            btn.disabled = true; spinner.classList.remove('d-none'); text.textContent = 'Deleting…';
            try {
                await pending.onConfirm();
                modal.hide();
            } catch (err) {
                console.error('Bulk delete failed:', err);
                alert(err.message || 'Failed to delete selected items.');
            } finally {
                btn.disabled = false; spinner.classList.add('d-none'); text.textContent = 'Delete';
                pending = null;
            }
        });
    }

    window.openBulkDeleteConfirmModal = function ({ count, kind, onConfirm }) {
        ensure();
        pending = { onConfirm };
        const label = kind === 'file' ? 'file' : 'attachment';
        const plural = count === 1 ? label : label + 's';
        modalEl.querySelector('#bulkDeleteTitle').textContent = `Delete ${count} ${plural}?`;
        modalEl.querySelector('#bulkDeleteCountLine').textContent =
            `${count} ${plural} will be permanently removed from the server.`;
        modal.show();
    };
})();
