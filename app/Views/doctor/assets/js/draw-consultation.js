/**
 * Draw Consultation
 * --------------------------------------------------------------------
 * Fabric.js v5.3-powered drawing modal for ophthalmology consultations.
 *
 * Features: Select/Pointer, Pen/Pencil, Brush, Eraser, Shapes, Text,
 * Arrow, Eye Templates (Cornea/Iris, Fundus, Retina, Eyelids),
 * Medical Stamps, OD/OS Toggle, Fill Opacity, Layers Panel,
 * Zoom/Pan, Clock-Hour Guide, Undo/Redo, Save/Load with metadata.
 *
 * Depends on Fabric.js v5.3 + Bootstrap 5.
 */

(function () {
    if (window.DrawConsultation) return;

    const AUTO_SAVE_MS = 30000;
    const HISTORY_LIMIT = 60;
    const MIN_SHAPE_SIZE = 10;
    const CANVAS_BG = '#ffffff';
    const MIN_ZOOM = 0.5;
    const MAX_ZOOM = 4;

    const CUSTOM_PROPERTIES = [
        'objectType', 'medicalType', 'eyeSide', 'layerType',
        'isTemplate', 'isOverlay', 'isStamp', 'templateType', 'locked'
    ];

    const MEDICAL_STAMPS = {
        cornealOpacity: { label: 'Corneal Opacity', medicalType: 'cornealOpacity', shape: 'cloud', color: '#94a3b8', icon: 'bi-cloud' },
        scar: { label: 'Scar', medicalType: 'scar', shape: 'line', color: '#64748b', icon: 'bi-dash-lg' },
        ulcer: { label: 'Ulcer', medicalType: 'ulcer', shape: 'triangle', color: '#dc2626', icon: 'bi-triangle' },
        foreignBody: { label: 'Foreign Body', medicalType: 'foreignBody', shape: 'star', color: '#374151', icon: 'bi-star' },
        hemorrhageDot: { label: 'Hemorrhage Dot', medicalType: 'hemorrhageDot', shape: 'circle', color: '#dc2626', icon: 'bi-circle-fill' },
        retinalTear: { label: 'Retinal Tear', medicalType: 'retinalTear', shape: 'triangle', color: '#dc2626', icon: 'bi-triangle' },
        retinalDetachment: { label: 'Retinal Detachment', medicalType: 'retinalDetachment', shape: 'wave', color: '#7c3aed', icon: 'bi-water' },
        laserSpot: { label: 'Laser Spot', medicalType: 'laserSpot', shape: 'circle', color: '#fbbf24', icon: 'bi-circle-fill' },
        edema: { label: 'Edema', medicalType: 'edema', shape: 'circle', color: '#60a5fa', icon: 'bi-circle' },
        cataractOpacity: { label: 'Cataract Opacity', medicalType: 'cataractOpacity', shape: 'cloud', color: '#94a3b8', icon: 'bi-cloud' },
        sutures: { label: 'Sutures', medicalType: 'sutures', shape: 'cross', color: '#0ea5e9', icon: 'bi-plus' },
        inflammation: { label: 'Inflammation', medicalType: 'inflammation', shape: 'circle', color: '#ef4444', icon: 'bi-circle-fill' }
    };

    const EYE_TEMPLATES = {
        corneaIris: { label: 'Cornea / Iris', medicalType: 'corneaIris' },
        fundus: { label: 'Fundus / Retina', medicalType: 'fundus' },
        opticDiscMacula: { label: 'Optic Disc + Macula', medicalType: 'opticDiscMacula' },
        eyelids: { label: 'Eyelids', medicalType: 'eyelids' }
    };

    let canvas = null;
    let modalEl = null;
    let modal = null;
    let canvasInitialized = false;

    let session = blankSession();

    let currentTool = 'select';
    let strokeColor = '#0f172a';
    let fillColor = 'transparent';
    let strokeWidth = 3;
    let fillOpacity = 1;
    let autoSaveTimer = null;
    let shapeDraft = null;
    let shapeStart = null;
    let lockHistory = false;
    let currentEyeSide = 'OD';
    let currentTemplateType = null;
    let clockGuideVisible = false;
    let clockGuideObject = null;
    let isPanning = false;
    let lastPanPoint = null;
    let arrowStartPoint = null;
    let arrowDraft = null;

    // Set while we programmatically exit text editing inside setTool() so the
    // text:editing:exited handler doesn't fight the user's tool switch.
    let isExitingTextEditing = false;
    // True when the current pan session was started by holding spacebar.
    let spacePanActive = false;

    function isFabricText(obj) {
        return !!(obj && (obj.type === 'i-text' || obj.type === 'textbox' || obj.type === 'text'));
    }

    function blankSession() {
        return {
            context: null,
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

    function open(opts) {
        const context = (opts && opts.context) ? opts.context : defaultAppointmentContext();
        if (!context.uploadUrl) {
            console.error('DrawConsultation.open: context.uploadUrl is required');
            return;
        }
        ensureModal();

        session = blankSession();
        session.context = context;
        resetCanvasContent();
        setAutoSaveStatus('idle');
        applyContextToModal();
        refreshHistoryButtons();
        updateCopyPreviousVisibility();

        document.body.classList.add('draw-modal-open');

        modal.show();
        modalEl.addEventListener('shown.bs.modal', onModalShown, { once: true });
    }

    function updateCopyPreviousVisibility() {
        const btn = modalEl && modalEl.querySelector('#copyPreviousBtn');
        if (!btn) return;
        const ctx = session.context || {};
        const available = !!ctx.previousDrawing || typeof ctx.getPreviousDrawing === 'function';
        btn.style.display = available ? '' : 'none';
    }

    function onModalShown() {
        initCanvasOnce();
        resizeCanvas();
        // Bootstrap 5's focus-trap re-focuses the modal element on every
        // focusin event whose target it can't find in its tabindex list.
        // Fabric's hidden textarea (used by IText/Textbox while editing) has
        // tabindex="-1" so the trap rejects it and yanks focus back, which
        // means typing into a text annotation silently fails. Deactivating
        // the trap on each show is safe here: this modal is full-screen,
        // has its own ESC handling, and nothing outside it can be reached
        // by tab while it is open anyway.
        try {
            if (modal && modal._focustrap && typeof modal._focustrap.deactivate === 'function') {
                modal._focustrap.deactivate();
            }
        } catch (e) { /* ignore */ }

        session.history = [];
        session.future = [];
        pushHistory();
        startAutoSave();
        refreshLayersPanel();
        updateEyeSideUI();
        setTool('select');

        // Layers panel is CLOSED by default on every viewport — the new
        // Canva-style contextual menu (drawCtxMenu) handles the common
        // per-object actions (delete, bring forward, hide, group, …) so
        // the layers panel only needs to be opened when the user wants
        // a tree overview. Also reset any drag offset from the previous
        // session so when the user does open it, it lands at the CSS-
        // defined anchor (top-right of canvas).
        try {
            const panel = modalEl.querySelector('#layersPanel');
            if (panel) {
                resetLayersPanelDrag(panel);
                panel.classList.remove('is-open');
            }
            const ctxMenu = modalEl.querySelector('#drawCtxMenu');
            if (ctxMenu) ctxMenu.hidden = true;
        } catch (e) { /* feature-detect failure — leave panel as-is */ }
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
        window.addEventListener('orientationchange', () => {
            setTimeout(resizeCanvas, 150);
        });

        canvas.on('path:created', (e) => {
            if (e && e.path) {
                const isEraser = currentTool === 'eraser';
                e.path.objectType = isEraser ? 'eraserMark' : 'path';
                e.path.medicalType = isEraser ? 'eraser' : currentTool;
                e.path.layerType = 'drawing';
                e.path.eyeSide = currentEyeSide;
                if (isEraser) {
                    // Real eraser: punch through underlying pixels instead of
                    // painting over them with white. Fabric serialises this
                    // composite mode in toJSON by default, so it survives
                    // save/load round-trips.
                    e.path.set({
                        globalCompositeOperation: 'destination-out',
                        selectable: false,
                        evented: false
                    });
                    canvas.requestRenderAll();
                }
            }
            if (lockHistory) return;
            session.isDirty = true;
            pushHistory();
        });
        canvas.on('object:modified', () => { if (lockHistory) return; session.isDirty = true; pushHistory(); refreshLayersPanel(); updateCtxMenuPosition(); });
        canvas.on('object:added', () => { if (lockHistory) return; session.isDirty = true; refreshLayersPanel(); });
        canvas.on('object:removed', () => { if (lockHistory) return; session.isDirty = true; refreshLayersPanel(); hideCtxMenu(); });
        canvas.on('selection:created', () => { updatePropPanelFromSelection(); showCtxMenu(); });
        canvas.on('selection:updated', () => { updatePropPanelFromSelection(); showCtxMenu(); });
        canvas.on('selection:cleared', () => { refreshLayersPanel(); hideCtxMenu(); });
        canvas.on('object:moving', updateCtxMenuPosition);
        canvas.on('object:scaling', updateCtxMenuPosition);
        canvas.on('object:rotating', updateCtxMenuPosition);
        /* NOTE: do NOT bind to `after:render`. Fabric re-renders every
           frame while a Textbox is being edited (cursor blink, every
           keystroke, every blink — 60 fps). Recomputing the contextual
           menu position on every one of those caused the typing UI to
           glitch and click-to-edit to flake out. The events above
           (object:moving/scaling/rotating + selection:*) plus the window
           resize listener bound in bindCtxMenuEvents are enough to keep
           the menu glued to the object during user interaction. */

        canvas.on('mouse:down', handleMouseDown);
        canvas.on('mouse:move', handleMouseMove);
        canvas.on('mouse:up', handleMouseUp);
        canvas.on('mouse:wheel', handleMouseWheel);

        canvas.on('text:editing:entered', function () {
            currentTool = 'textEditing';
            // Pull the contextual menu out of the way so the typing cursor
            // and Fabric's hidden textarea aren't fighting for hit targets.
            hideCtxMenu();
        });

        canvas.on('text:editing:exited', function () {
            // pushHistory dedupes identical snapshots, so a final push here is safe.
            pushHistory();
            refreshLayersPanel();
            // Don't fight a user-initiated tool switch (setTool exits editing first
            // and sets isExitingTextEditing while doing so).
            if (!isExitingTextEditing && currentTool === 'textEditing') {
                setTool('select');
            }
            // Bring the menu back if the text is still the active object.
            showCtxMenu();
        });

        setTool(currentTool);
    }

    function resetCanvasContent() {
        if (!canvas) return;
        canvas.clear();
        canvas.backgroundColor = CANVAS_BG;
        canvas.viewportTransform = [1, 0, 0, 1, 0, 0];
        currentTemplateType = null;
        clockGuideVisible = false;
        clockGuideObject = null;
        if (modalEl) refreshLayersPanel();
        if (modalEl) updateZoomUI();
    }

    function applyContextToModal() {
        const titleEl = modalEl.querySelector('#drawConsultationTitle');
        if (titleEl && session.context && session.context.title) {
            titleEl.innerHTML = `<i class="bi bi-pencil-square me-2 text-primary"></i>${session.context.title}`;
        }
    }

    function ensureModal() {
        if (modalEl) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = renderModalHtml();
        modalEl = wrapper.firstElementChild;
        document.body.appendChild(modalEl);
        modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });

        // Permanently disable Bootstrap's focus-trap on this modal. Fabric's
        // hidden textarea (used by IText/Textbox during editing) lives at
        // tabindex="-1" inside the canvas wrapper. Bootstrap's focus-trap
        // doesn't recognise tabindex="-1" descendants as "focusable" inside
        // the modal and yanks focus back to the modal element on every
        // focusin event — which silently swallows every keystroke. Replacing
        // activate/deactivate with no-ops kills the trap for good.
        try {
            if (modal && modal._focustrap) {
                modal._focustrap.activate = function () { };
                modal._focustrap.deactivate = function () { };
            }
        } catch (e) { /* ignore */ }

        // Bind events defensively: a failure here must not leave the modal in
        // a partially-initialised state (which manifests as "click the draw
        // button twice to open").
        try { bindToolbarEvents(); } catch (e) { console.error('Draw: bindToolbarEvents failed', e); }
        try { bindLayersPanelEvents(); } catch (e) { console.error('Draw: bindLayersPanelEvents failed', e); }
        try { bindCtxMenuEvents(); } catch (e) { console.error('Draw: bindCtxMenuEvents failed', e); }

        modalEl.addEventListener('hidden.bs.modal', () => {
            stopAutoSave();
            resetCanvasContent();
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
            <span class="draw-tool-group__label">Eye Side</span>
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-primary eye-side-btn is-active" data-eye="OD" title="Right Eye (OD)">
                <i class="bi bi-person"></i> OD
              </button>
              <button type="button" class="btn btn-outline-primary eye-side-btn" data-eye="OS" title="Left Eye (OS)">
                <i class="bi bi-person-fill"></i> OS
              </button>
            </div>
            <span class="eye-side-label text-muted small ms-1" id="eyeSideLabel">Right Eye</span>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Template</span>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="templateDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Eye Templates">
                <i class="bi bi-eye"></i> Templates
              </button>
              <ul class="dropdown-menu" aria-labelledby="templateDropdown">
                <li><h6 class="dropdown-header">Anatomical Templates</h6></li>
                <li><a class="dropdown-item template-item d-flex align-items-center gap-2" href="#" data-template="corneaIris"><i class="bi bi-circle text-secondary"></i> Cornea / Iris</a></li>
                <li><a class="dropdown-item template-item d-flex align-items-center gap-2" href="#" data-template="fundus"><i class="bi bi-eye text-secondary"></i> Fundus / Retina</a></li>
                <li><a class="dropdown-item template-item d-flex align-items-center gap-2" href="#" data-template="opticDiscMacula"><i class="bi bi-record-circle text-warning"></i> Optic Disc + Macula</a></li>
                <li><a class="dropdown-item template-item d-flex align-items-center gap-2" href="#" data-template="eyelids"><i class="bi bi-sunglasses text-secondary"></i> Eyelids</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header">Actions</h6></li>
                <li><a class="dropdown-item text-danger" href="#" id="clearTemplatesBtn"><i class="bi bi-trash"></i> Clear All Templates</a></li>
              </ul>
            </div>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Annotate</span>
            <button type="button" class="draw-tool-btn" data-tool="text" title="Text Annotation (X)"><i class="bi bi-type"></i></button>
            <button type="button" class="draw-tool-btn" data-tool="arrow" title="Arrow Annotation (A)"><i class="bi bi-arrow-right"></i></button>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="stampDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Medical Stamps">
                <i class="bi bi-stamp"></i> Stamps
              </button>
              <ul class="dropdown-menu" aria-labelledby="stampDropdown" style="max-height: 300px; overflow-y: auto;">
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="cornealOpacity"><i class="bi bi-cloud text-secondary"></i> Corneal Opacity</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="scar"><i class="bi bi-dash-lg text-secondary"></i> Scar</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="ulcer"><i class="bi bi-triangle text-danger"></i> Ulcer</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="foreignBody"><i class="bi bi-star" style="color: #ffffff; text-shadow: 0 0 2px #000;"></i> Foreign Body</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="hemorrhageDot"><i class="bi bi-circle-fill text-danger"></i> Hemorrhage Dot</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="retinalTear"><i class="bi bi-triangle text-danger"></i> Retinal Tear</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="retinalDetachment"><i class="bi bi-water text-secondary"></i> Retinal Detachment</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="laserSpot"><i class="bi bi-circle-fill text-warning"></i> Laser Spot</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="edema"><i class="bi bi-circle text-primary"></i> Edema</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="cataractOpacity"><i class="bi bi-cloud text-secondary"></i> Cataract Opacity</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="sutures"><i class="bi bi-plus text-info"></i> Sutures</a></li>
                <li><a class="dropdown-item stamp-item d-flex align-items-center gap-2" href="#" data-stamp="inflammation"><i class="bi bi-circle-fill text-danger"></i> Inflammation</a></li>
              </ul>
            </div>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Overlay</span>
            <button type="button" class="draw-tool-btn" id="toggleClockGuide" title="Clock-Hour Guide"><i class="bi bi-clock"></i></button>
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
            <input type="range" class="draw-opacity-slider" id="drawFillOpacity" min="0" max="100" value="100" title="Fill opacity">
            <button type="button" class="draw-tool-btn" id="drawFillTransparent" title="No fill"><i class="bi bi-slash-circle"></i></button>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">Edit</span>
            <button type="button" class="draw-tool-btn" id="drawUndoBtn" title="Undo (Ctrl+Z)"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button type="button" class="draw-tool-btn" id="drawRedoBtn" title="Redo (Ctrl+Shift+Z)"><i class="bi bi-arrow-clockwise"></i></button>
            <button type="button" class="draw-tool-btn" id="drawDeleteBtn" title="Delete selected"><i class="bi bi-trash"></i></button>
            <button type="button" class="draw-tool-btn" id="drawClearBtn" title="Clear all"><i class="bi bi-x-circle"></i></button>
            <button type="button" class="draw-tool-btn" id="copyPreviousBtn" title="Copy from previous consultation drawing" style="display:none;"><i class="bi bi-clipboard-plus"></i></button>
          </div>

          <div class="vr"></div>

          <div class="draw-tool-group">
            <span class="draw-tool-group__label">View</span>
            <button type="button" class="draw-tool-btn" id="zoomInBtn" title="Zoom In (+)"><i class="bi bi-zoom-in"></i></button>
            <button type="button" class="draw-tool-btn" id="zoomOutBtn" title="Zoom Out (-)"><i class="bi bi-zoom-out"></i></button>
            <button type="button" class="draw-tool-btn" id="zoomResetBtn" title="Reset Zoom"><i class="bi bi-arrow-repeat"></i></button>
            <button type="button" class="draw-tool-btn" id="panBtn" title="Pan (Space+Drag)"><i class="bi bi-arrows-move"></i></button>
            <button type="button" class="draw-tool-btn" id="layersBtn" title="Layers Panel"><i class="bi bi-layers"></i></button>
          </div>
        </div>

        <div class="draw-canvas-wrap" id="drawCanvasWrap">
          <div class="draw-canvas-shell">
            <canvas id="drawCanvas"></canvas>
          </div>
          <!-- Canva-style contextual action menu shown on selection. -->
          <div class="draw-ctx-menu" id="drawCtxMenu" aria-hidden="true" hidden>
            <button type="button" class="draw-ctx-btn" data-ctx-action="bring-forward" title="Bring forward">
              <i class="bi bi-arrow-up-square"></i>
            </button>
            <button type="button" class="draw-ctx-btn" data-ctx-action="send-backward" title="Send backward">
              <i class="bi bi-arrow-down-square"></i>
            </button>
            <span class="draw-ctx-sep" aria-hidden="true"></span>
            <button type="button" class="draw-ctx-btn" data-ctx-action="toggle-visibility" title="Hide / Show">
              <i class="bi bi-eye"></i>
            </button>
            <button type="button" class="draw-ctx-btn" data-ctx-action="toggle-lock" title="Lock / Unlock">
              <i class="bi bi-unlock"></i>
            </button>
            <span class="draw-ctx-sep draw-ctx-sep-group" aria-hidden="true"></span>
            <button type="button" class="draw-ctx-btn draw-ctx-group-only" data-ctx-action="group" title="Group">
              <i class="bi bi-collection"></i>
            </button>
            <button type="button" class="draw-ctx-btn draw-ctx-ungroup-only" data-ctx-action="ungroup" title="Ungroup">
              <i class="bi bi-grid-1x2"></i>
            </button>
            <span class="draw-ctx-sep" aria-hidden="true"></span>
            <button type="button" class="draw-ctx-btn draw-ctx-btn-danger" data-ctx-action="delete" title="Delete">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>

        <div class="draw-layers-panel" id="layersPanel">
          <div class="draw-layers-header">
            <span>Layers</span>
            <button type="button" class="draw-layers-close" id="closeLayersBtn" aria-label="Close layers panel">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                <path d="M3 3 L13 13 M13 3 L3 13"/>
              </svg>
            </button>
          </div>
          <div class="draw-layers-list" id="layersList"></div>
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

    function bindToolbarEvents() {
        modalEl.querySelectorAll('[data-tool]').forEach(btn => {
            btn.addEventListener('click', () => setTool(btn.dataset.tool));
        });

        const strokeInput = modalEl.querySelector('#drawStrokeColor');
        strokeInput.addEventListener('input', () => {
            strokeColor = strokeInput.value;
            applyBrush();
            updateStrokePreview();
            const active = canvas && canvas.getActiveObject();
            if (active && (active.type === 'textbox' || active.type === 'i-text' || active.type === 'text')) {
                // For text objects, colour is the fill, not the stroke.
                applyToSelection({ fill: strokeColor });
            } else {
                applyToSelection({ stroke: strokeColor });
            }
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
            applyFillOpacityToSelection();
            fillInput.classList.remove('is-transparent');
        });

        const fillOpacityInput = modalEl.querySelector('#drawFillOpacity');
        fillOpacityInput.addEventListener('input', () => {
            fillOpacity = parseInt(fillOpacityInput.value, 10) / 100;
            applyFillOpacityToSelection();
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
        modalEl.querySelector('#copyPreviousBtn').addEventListener('click', copyFromPrevious);

        modalEl.querySelectorAll('.eye-side-btn').forEach(btn => {
            btn.addEventListener('click', () => setEyeSide(btn.dataset.eye));
        });

        modalEl.querySelectorAll('.template-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                addEyeTemplate(item.dataset.template);
            });
        });

        const clearTemplatesBtn = modalEl.querySelector('#clearTemplatesBtn');
        if (clearTemplatesBtn) {
            clearTemplatesBtn.addEventListener('click', (e) => {
                e.preventDefault();
                clearAllTemplates();
            });
        }

        modalEl.querySelectorAll('.stamp-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!canvas || !canvasInitialized) {
                    console.warn('Canvas not initialized');
                    return;
                }
                const stampType = item.dataset.stamp;
                const wrap = document.getElementById('drawCanvasWrap');
                let w = 800, h = 600;
                if (wrap) {
                    const cs = getComputedStyle(wrap);
                    const padX = (parseFloat(cs.paddingLeft) || 0) + (parseFloat(cs.paddingRight) || 0);
                    const padY = (parseFloat(cs.paddingTop) || 0) + (parseFloat(cs.paddingBottom) || 0);
                    w = Math.max(320, (wrap.clientWidth || 800) - padX);
                    h = Math.max(240, (wrap.clientHeight || 600) - padY);
                }
                const centerX = w / 2;
                const centerY = h / 2;
                addMedicalStamp(stampType, { x: centerX, y: centerY });
                setTool('select');
            });
        });

        modalEl.querySelector('#toggleClockGuide').addEventListener('click', toggleClockGuide);

        modalEl.querySelector('#zoomInBtn').addEventListener('click', () => zoomBy(1.2));
        modalEl.querySelector('#zoomOutBtn').addEventListener('click', () => zoomBy(0.8));
        modalEl.querySelector('#zoomResetBtn').addEventListener('click', resetZoom);
        modalEl.querySelector('#panBtn').addEventListener('click', togglePan);

        modalEl.querySelector('#layersBtn').addEventListener('click', () => {
            const panel = modalEl.querySelector('#layersPanel');
            const isOpen = panel.classList.toggle('is-open');
            if (isOpen) refreshLayersPanel();
        });

        modalEl.querySelector('#drawSaveBtn').addEventListener('click', () => save({ silent: false }));
        modalEl.querySelector('#drawCancelBtn').addEventListener('click', () => modal.hide());
        modalEl.querySelector('#drawCloseBtn').addEventListener('click', () => modal.hide());

        // Defensive: guard each binding so that one missing handler can never
        // tear down the whole toolbar (which would leave the modal open with
        // no working buttons — the previously-seen "click twice to open" bug).
        try { document.addEventListener('keydown', onKeydown); } catch (e) { console.warn('Draw: keydown bind failed', e); }
        try { if (typeof onKeyup === 'function') document.addEventListener('keyup', onKeyup); } catch (e) { console.warn('Draw: keyup bind failed', e); }
        updateStrokePreview();
        updateZoomUI();
    }

    /* ====================================================================
       Contextual action menu (Canva-style). Floats above the currently
       selected element on the canvas with quick actions: bring forward /
       send backward, hide/show, lock/unlock, group/ungroup, delete.
       Position is recomputed on selection, modification, scroll/resize.
       The menu is suppressed for templates, overlays and the clock guide
       since those aren't user-manipulable.
       ==================================================================== */

    function isContextMenuEligible(obj) {
        if (!obj) return false;
        if (obj.isOverlay) return false;
        if (obj.eraserMark) return false;
        // Hide menu while text is being edited (any text class) so cursor /
        // typing isn't fought over by the floating button cluster.
        const isAnyText = obj.type === 'i-text' || obj.type === 'textbox' || obj.type === 'text';
        if (isAnyText && obj.isEditing) return false;
        return true;
    }
    function isAnchoredLayer(obj) {
        return !!(obj && (obj.isTemplate || obj._isTemplate || obj.isOverlay));
    }

    function hideCtxMenu() {
        const menu = modalEl && modalEl.querySelector('#drawCtxMenu');
        if (!menu) return;
        menu.hidden = true;
        menu.style.transform = '';
    }

    function showCtxMenu() {
        const menu = modalEl && modalEl.querySelector('#drawCtxMenu');
        if (!menu || !canvas) return;
        const active = canvas.getActiveObject();
        if (!isContextMenuEligible(active)) { hideCtxMenu(); return; }

        const isMulti = active.type === 'activeSelection';
        const anchored = isAnchoredLayer(active);
        menu.querySelectorAll('.draw-ctx-group-only').forEach(b => { b.hidden = !isMulti; });
        menu.querySelectorAll('.draw-ctx-ungroup-only').forEach(b => { b.hidden = active.type !== 'group'; });
        menu.querySelectorAll('.draw-ctx-sep-group').forEach(s => {
            s.hidden = !(isMulti || active.type === 'group');
        });
        // Templates + overlays are anchored at the bottom of the z-order —
        // exposing bring-forward / send-backward on them would break the
        // "drawing always on top of template" rule, so we hide those two.
        menu.querySelectorAll('[data-ctx-action="bring-forward"], [data-ctx-action="send-backward"]').forEach(b => {
            b.hidden = anchored;
        });

        // Visibility/lock icon state
        const visBtn = menu.querySelector('[data-ctx-action="toggle-visibility"] i');
        if (visBtn) {
            visBtn.classList.toggle('bi-eye', active.visible !== false);
            visBtn.classList.toggle('bi-eye-slash', active.visible === false);
        }
        const lockBtn = menu.querySelector('[data-ctx-action="toggle-lock"] i');
        if (lockBtn) {
            const locked = !!active.locked;
            lockBtn.classList.toggle('bi-unlock', !locked);
            lockBtn.classList.toggle('bi-lock-fill', locked);
        }

        menu.hidden = false;
        updateCtxMenuPosition();
    }

    function updateCtxMenuPosition() {
        const menu = modalEl && modalEl.querySelector('#drawCtxMenu');
        if (!menu || menu.hidden || !canvas) return;
        const active = canvas.getActiveObject();
        if (!isContextMenuEligible(active)) { hideCtxMenu(); return; }

        const wrap = modalEl.querySelector('#drawCanvasWrap');
        if (!wrap) return;

        // Object bounding rect in canvas coords. Use absolute bounding rect so
        // grouped / transformed shapes still report sensible coords.
        const br = active.getBoundingRect(true, true);

        // Canvas element is positioned inside the .draw-canvas-shell which is
        // centred inside .draw-canvas-wrap. Translate canvas coords → wrap
        // coords by offsetting with the canvas DOM rect relative to the wrap.
        const canvasEl = canvas.lowerCanvasEl || canvas.upperCanvasEl;
        if (!canvasEl) return;
        const canvasRect = canvasEl.getBoundingClientRect();
        const wrapRect = wrap.getBoundingClientRect();
        const offsetX = canvasRect.left - wrapRect.left;
        const offsetY = canvasRect.top  - wrapRect.top;

        // Centre menu horizontally over the bounding rect; place it 10px
        // above the rect with an 8px fallback to below when there's no
        // room at the top.
        const menuWidth = menu.offsetWidth || 280;
        const menuHeight = menu.offsetHeight || 44;
        let left = offsetX + br.left + (br.width / 2) - (menuWidth / 2);
        let top  = offsetY + br.top - menuHeight - 12;

        // Keep inside the wrap horizontally
        const wrapW = wrap.clientWidth;
        const wrapH = wrap.clientHeight;
        if (left < 6) left = 6;
        if (left + menuWidth > wrapW - 6) left = wrapW - menuWidth - 6;
        // If above the rect would clip the top of the wrap, drop the menu
        // below the object instead.
        if (top < 6) {
            top = offsetY + br.top + br.height + 12;
            // And if even below would clip, pin to the top of the wrap.
            if (top + menuHeight > wrapH - 6) top = 6;
        }

        menu.style.transform = 'translate(' + Math.round(left) + 'px, ' + Math.round(top) + 'px)';
    }

    function bindCtxMenuEvents() {
        const menu = modalEl && modalEl.querySelector('#drawCtxMenu');
        if (!menu) return;
        menu.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-ctx-action]');
            if (!btn) return;
            const action = btn.dataset.ctxAction;
            handleCtxMenuAction(action);
        });
        // Re-position on viewport / scroll changes.
        window.addEventListener('resize', updateCtxMenuPosition);
        window.addEventListener('scroll', updateCtxMenuPosition, true);
    }

    function handleCtxMenuAction(action) {
        if (!canvas) return;
        const active = canvas.getActiveObject();
        if (!isContextMenuEligible(active)) return;

        switch (action) {
            case 'delete':
                deleteSelected();
                break;
            case 'bring-forward':
                canvas.bringForward(active);
                pinAnchoredLayers();
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
                break;
            case 'send-backward':
                canvas.sendBackwards(active);
                pinAnchoredLayers();
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
                break;
            case 'toggle-visibility': {
                const next = active.visible === false;
                active.visible = next;
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
                showCtxMenu();
                break;
            }
            case 'toggle-lock': {
                const locked = !active.locked;
                active.locked = locked;
                active.selectable = !locked;
                active.evented = !locked;
                if (locked && active === canvas.getActiveObject()) {
                    canvas.discardActiveObject();
                    canvas.requestRenderAll();
                    hideCtxMenu();
                } else {
                    canvas.requestRenderAll();
                    showCtxMenu();
                }
                refreshLayersPanel();
                pushHistory();
                break;
            }
            case 'group': {
                if (active.type !== 'activeSelection') return;
                active.toGroup();
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
                showCtxMenu();
                break;
            }
            case 'ungroup': {
                if (active.type !== 'group') return;
                active.toActiveSelection();
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
                showCtxMenu();
                break;
            }
        }
    }

    // Anchored layers (templates + overlays) must stay at the bottom of the
    // z-order. Re-pin them after any user-initiated reorder.
    function pinAnchoredLayers() {
        canvas.getObjects().forEach(o => { if (o.isOverlay) canvas.sendToBack(o); });
        canvas.getObjects().forEach(o => { if (o.isTemplate || o._isTemplate) canvas.sendToBack(o); });
    }

    function bindLayersPanelEvents() {
        modalEl.querySelector('#closeLayersBtn').addEventListener('click', () => {
            const p = modalEl.querySelector('#layersPanel');
            p.classList.remove('is-open');
            resetLayersPanelDrag(p);
        });
        enableLayersPanelDrag();
    }

    function resetLayersPanelDrag(panel) {
        if (!panel) return;
        delete panel.dataset.dragX;
        delete panel.dataset.dragY;
        panel.style.transform = '';
    }

    /* Make the layers panel draggable by its header — both mouse and touch.
       The panel is positioned with `top`/`right` (desktop) or `bottom`/
       `left`/`right` (mobile bottom-sheet). Rather than touching those
       anchors we layer a `transform: translate(dx, dy)` on top, which is
       additive to whatever positioning the CSS chose. Drag deltas reset
       each modal-open so the panel returns to its CSS-defined spot. */
    function enableLayersPanelDrag() {
        const panel = modalEl.querySelector('#layersPanel');
        const header = panel && panel.querySelector('.draw-layers-header');
        if (!panel || !header) return;

        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let baseX = 0;
        let baseY = 0;

        function getXY(e) {
            if (e.touches && e.touches.length) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
            return { x: e.clientX, y: e.clientY };
        }

        function onDown(e) {
            // Ignore clicks on the close button so it still works
            if (e.target.closest('.btn-close, .draw-layers-close')) return;
            const p = getXY(e);
            isDragging = true;
            startX = p.x;
            startY = p.y;
            baseX = parseFloat(panel.dataset.dragX || '0');
            baseY = parseFloat(panel.dataset.dragY || '0');
            header.style.cursor = 'grabbing';
            panel.classList.add('is-dragging');
            if (e.cancelable) e.preventDefault();
        }

        function onMove(e) {
            if (!isDragging) return;
            const p = getXY(e);
            const dx = baseX + (p.x - startX);
            const dy = baseY + (p.y - startY);
            panel.dataset.dragX = String(dx);
            panel.dataset.dragY = String(dy);
            panel.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)';
            if (e.cancelable) e.preventDefault();
        }

        function onUp() {
            if (!isDragging) return;
            isDragging = false;
            header.style.cursor = 'grab';
            panel.classList.remove('is-dragging');
        }

        header.style.cursor = 'grab';
        header.style.userSelect = 'none';
        header.style.touchAction = 'none';

        header.addEventListener('mousedown', onDown);
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);

        header.addEventListener('touchstart', onDown, { passive: false });
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('touchend', onUp);
        document.addEventListener('touchcancel', onUp);
    }

    function onKeydown(e) {
        if (!modalEl.classList.contains('show')) return;
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        const activeObj = canvas ? canvas.getActiveObject() : null;
        const isEditingText = activeObj && (activeObj.type === 'textbox' || activeObj.type === 'i-text') && activeObj.isEditing;

        if (e.key === 'Escape' && isEditingText) {
            activeObj.exitEditing();
            setTool('select');
            return;
        }

        // Hold-to-pan: spacebar enables pan only while held. Ignore key-repeat so
        // a held space doesn't ping-pong with togglePan.
        if (e.key === ' ' && !isEditingText) {
            e.preventDefault();
            if (!isPanning && !e.repeat) {
                spacePanActive = true;
                isPanning = true;
                if (canvas) {
                    canvas.selection = false;
                    canvas.isDrawingMode = false;
                    canvas.defaultCursor = 'grab';
                }
                const panBtn = modalEl.querySelector('#panBtn');
                if (panBtn) panBtn.classList.add('is-active');
            }
            return;
        }

        if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === 'z') { e.preventDefault(); undo(); return; }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) { e.preventDefault(); redo(); return; }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save({ silent: false }); return; }
        if ((e.key === 'Delete' || e.key === 'Backspace') && !isEditingText) {
            if (canvas && canvas.getActiveObject()) { e.preventDefault(); deleteSelected(); }
            return;
        }

        // Tool shortcuts must never override typing into a text being edited.
        if (isEditingText) return;
        const map = { v: 'select', p: 'pencil', b: 'pen', m: 'marker', e: 'eraser', r: 'rect', c: 'circle', t: 'triangle', l: 'line', x: 'text', a: 'arrow' };
        const key = e.key.toLowerCase();
        if (map[key]) { e.preventDefault(); setTool(map[key]); }
        if (e.key === '+' || e.key === '=') { e.preventDefault(); zoomBy(1.2); }
        if (e.key === '-') { e.preventDefault(); zoomBy(0.8); }
    }

    function onKeyup(e) {
        if (!modalEl || !modalEl.classList.contains('show')) return;
        if (e.key === ' ' && spacePanActive) {
            e.preventDefault();
            spacePanActive = false;
            isPanning = false;
            const panBtn = modalEl.querySelector('#panBtn');
            if (panBtn) panBtn.classList.remove('is-active');
            if (canvas) canvas.defaultCursor = 'default';
            // Re-apply the current tool to restore cursor/selection/drawingMode.
            setTool(currentTool);
        }
    }

    function setTool(tool) {
        // If a text object is currently being edited and the user switches to
        // anything other than textEditing mode, exit editing cleanly so we
        // don't end up with an editable text plus a different active tool.
        if (canvas && tool !== 'textEditing') {
            const active = canvas.getActiveObject();
            if (active && isFabricText(active) && active.isEditing) {
                isExitingTextEditing = true;
                try { active.exitEditing(); } catch (e) { /* ignore */ }
                isExitingTextEditing = false;
            }
        }

        // Discard any in-flight shape/arrow draft when the tool changes so we
        // don't leak orphan objects on the canvas.
        if (canvas) {
            if (shapeDraft) {
                try { canvas.remove(shapeDraft); } catch (e) { /* ignore */ }
                shapeDraft = null;
                shapeStart = null;
            }
            if (arrowDraft) {
                try { canvas.remove(arrowDraft); } catch (e) { /* ignore */ }
                arrowDraft = null;
            }
            arrowStartPoint = null;
        }

        // Selecting any tool (other than implicit pan resumes) exits pan mode.
        if (isPanning && !spacePanActive) {
            isPanning = false;
            if (canvas) canvas.defaultCursor = 'default';
        }

        currentTool = tool;
        modalEl.querySelectorAll('[data-tool]').forEach(b => b.classList.toggle('is-active', b.dataset.tool === tool));

        const panBtn = modalEl.querySelector('#panBtn');
        if (panBtn) panBtn.classList.toggle('is-active', isPanning);

        if (!canvas) return;

        const drawingTools = ['pencil', 'pen', 'marker', 'eraser'];
        const shapeTools = ['rect', 'circle', 'triangle', 'line'];

        if (tool === 'text' || tool === 'textEditing') {
            // selection=false prevents Fabric from starting a rubber-band group
            // selection on click-on-empty-space (which fights the new textbox
            // we are about to add and steals its active state).
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.defaultCursor = 'text';
            canvas.hoverCursor = 'text';
            return;
        }

        if (tool === 'arrow' || tool === 'stamp') {
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.defaultCursor = 'crosshair';
            return;
        }

        if (drawingTools.includes(tool)) {
            canvas.isDrawingMode = true;
            canvas.selection = false;
            canvas.discardActiveObject();
            applyBrush();
        } else if (shapeTools.includes(tool)) {
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.discardActiveObject();
        } else {
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
                // Color is irrelevant once we apply destination-out on the
                // resulting path (see path:created handler) — solid black with
                // full alpha guarantees the erase reaches alpha 0.
                brush.color = '#000000';
                brush.width = Math.max(8, strokeWidth * 3);
                brush.strokeLineCap = 'round';
                brush.strokeLineJoin = 'round';
                canvas.freeDrawingCursor = 'cell';
                break;
        }
    }

    function handleMouseDown(opt) {
        if (isPanning) {
            lastPanPoint = { x: opt.e.clientX, y: opt.e.clientY };
            return;
        }

        if (currentTool === 'text' || currentTool === 'textEditing') {
            const target = opt.target;
            const isTextTarget = target && (target.type === 'textbox' || target.type === 'i-text' || target.type === 'text');
            if (isTextTarget) {
                if (target.isEditing) return;
                canvas.setActiveObject(target);
                try {
                    target.enterEditing();
                    if (typeof target.selectAll === 'function') target.selectAll();
                } catch (e) { /* ignore */ }
                canvas.requestRenderAll();
                return;
            }
            if (currentTool === 'text') {
                const newText = addTextAnnotation(canvas.getPointer(opt.e));
                // Hand the new textbox to Fabric as the click target so its
                // post-event selection logic doesn't deactivate it.
                if (newText) opt.target = newText;
                return;
            }
        }

        if (currentTool === 'arrow') {
            const p = canvas.getPointer(opt.e);
            arrowStartPoint = { x: p.x, y: p.y };
            arrowDraft = null;
            return;
        }

        if (canvas.isDrawingMode) return;
        if (!['rect', 'circle', 'triangle', 'line'].includes(currentTool)) return;

        const p = canvas.getPointer(opt.e);
        shapeStart = { x: p.x, y: p.y };
        const fillValue = fillColor === 'transparent' ? '' : hexToRgba(fillColor, fillOpacity);
        const common = {
            left: p.x,
            top: p.y,
            originX: 'left',
            originY: 'top',
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            fill: fillValue,
            selectable: true,
            objectCaching: false,
            objectType: 'shape',
            medicalType: currentTool,
            eyeSide: currentEyeSide,
            layerType: 'drawing'
        };

        if (currentTool === 'rect') shapeDraft = new fabric.Rect({ ...common, width: 80, height: 60 });
        else if (currentTool === 'circle') shapeDraft = new fabric.Ellipse({ ...common, rx: 40, ry: 30 });
        else if (currentTool === 'triangle') shapeDraft = new fabric.Triangle({ ...common, width: 80, height: 70 });
        else if (currentTool === 'line') shapeDraft = new fabric.Line([p.x, p.y, p.x + 80, p.y], { stroke: strokeColor, strokeWidth: strokeWidth, selectable: true, objectType: 'shape', medicalType: 'line', eyeSide: currentEyeSide, layerType: 'drawing' });

        canvas.add(shapeDraft);
        canvas.requestRenderAll();
    }

    function handleMouseMove(opt) {
        if (isPanning && lastPanPoint) {
            const dx = opt.e.clientX - lastPanPoint.x;
            const dy = opt.e.clientY - lastPanPoint.y;
            const vpt = canvas.viewportTransform;
            vpt[4] += dx;
            vpt[5] += dy;
            canvas.requestRenderAll();
            lastPanPoint = { x: opt.e.clientX, y: opt.e.clientY };
            return;
        }

        if (currentTool === 'arrow' && arrowStartPoint) {
            const p = canvas.getPointer(opt.e);
            if (arrowDraft) canvas.remove(arrowDraft);
            arrowDraft = createArrow(arrowStartPoint, { x: p.x, y: p.y });
            canvas.add(arrowDraft);
            canvas.requestRenderAll();
            return;
        }

        if (!shapeDraft || !shapeStart) return;
        const p = canvas.getPointer(opt.e);
        const w = p.x - shapeStart.x;
        const h = p.y - shapeStart.y;

        if (shapeDraft.type === 'rect' || shapeDraft.type === 'triangle') {
            shapeDraft.set({
                left: w < 0 ? p.x : shapeStart.x,
                top: h < 0 ? p.y : shapeStart.y,
                width: Math.max(1, Math.abs(w)),
                height: Math.max(1, Math.abs(h)),
            });
        } else if (shapeDraft.type === 'ellipse') {
            shapeDraft.set({
                left: w < 0 ? p.x : shapeStart.x,
                top: h < 0 ? p.y : shapeStart.y,
                rx: Math.max(1, Math.abs(w) / 2),
                ry: Math.max(1, Math.abs(h) / 2),
            });
        } else if (shapeDraft.type === 'line') {
            shapeDraft.set({ x2: p.x, y2: p.y });
        }
        canvas.requestRenderAll();
    }

    function handleMouseUp(opt) {
        if (isPanning) {
            lastPanPoint = null;
            return;
        }

        if (currentTool === 'arrow') {
            if (arrowStartPoint && opt && opt.e) {
                const p = canvas.getPointer(opt.e);
                if (arrowDraft) {
                    canvas.remove(arrowDraft);
                }
                if (Math.abs(p.x - arrowStartPoint.x) > 2 || Math.abs(p.y - arrowStartPoint.y) > 2) {
                    const arrow = createArrow(arrowStartPoint, { x: p.x, y: p.y });
                    canvas.add(arrow);
                    canvas.setActiveObject(arrow);
                    pushHistory();
                }
                arrowStartPoint = null;
                arrowDraft = null;
                setTool('select');
                canvas.requestRenderAll();
                return;
            }
            arrowStartPoint = null;
            arrowDraft = null;
            return;
        }

        if (!shapeDraft) return;
        const bb = shapeDraft.getBoundingRect();
        if (bb.width < MIN_SHAPE_SIZE && bb.height < MIN_SHAPE_SIZE) {
            canvas.remove(shapeDraft);
            shapeDraft = null;
            shapeStart = null;
            return;
        }
        shapeDraft.setCoords();
        session.isDirty = true;
        pushHistory();
        canvas.setActiveObject(shapeDraft);
        shapeDraft = null;
        shapeStart = null;
        setTool('select');
        canvas.requestRenderAll();
    }

    function handleMouseWheel(opt) {
        if (!opt.e.ctrlKey && !opt.e.metaKey) return;
        opt.e.preventDefault();
        const delta = opt.e.deltaY > 0 ? 0.9 : 1.1;
        const point = { x: opt.e.offsetX, y: opt.e.offsetY };
        zoomToPoint(point, delta);
    }

    function zoomBy(factor) {
        const center = { x: canvas.getWidth() / 2, y: canvas.getHeight() / 2 };
        zoomToPoint(center, factor);
    }

    function zoomToPoint(point, factor) {
        let zoom = canvas.getZoom() * factor;
        zoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, zoom));
        canvas.zoomToPoint(new fabric.Point(point.x, point.y), zoom);
        updateZoomUI();
    }

    function resetZoom() {
        canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
        updateZoomUI();
    }

    function togglePan() {
        isPanning = !isPanning;
        const panBtn = modalEl.querySelector('#panBtn');
        if (panBtn) panBtn.classList.toggle('is-active', isPanning);
        if (isPanning) {
            canvas.selection = false;
            canvas.isDrawingMode = false;
            canvas.defaultCursor = 'grab';
        } else {
            canvas.defaultCursor = 'default';
            setTool(currentTool);
        }
    }

    function updateZoomUI() {
        if (!canvas) return;
        const zoom = Math.round(canvas.getZoom() * 100);
        let zoomText = modalEl.querySelector('#zoomLevelText');
        if (!zoomText) {
            zoomText = document.createElement('span');
            zoomText.id = 'zoomLevelText';
            zoomText.className = 'draw-zoom-level';
            const indicator = modalEl.querySelector('#drawAutoSaveStatus');
            if (indicator) indicator.parentNode.insertBefore(zoomText, indicator.nextSibling);
        }
        zoomText.textContent = zoom + '%';
    }

    let pushHistoryDebounceTimer = null;
    function pushHistoryDebounced(delay) {
        if (pushHistoryDebounceTimer) clearTimeout(pushHistoryDebounceTimer);
        pushHistoryDebounceTimer = setTimeout(() => {
            pushHistoryDebounceTimer = null;
            pushHistory();
        }, delay || 250);
    }

    function applyToSelection(props) {
        if (!canvas) return;
        const active = canvas.getActiveObject();
        if (!active) return;
        active.set(props);
        canvas.requestRenderAll();
        session.isDirty = true;
        // Slider-style adjustments fire many `input` events; debounce so we
        // record one history step at the end of the drag, not 60.
        pushHistoryDebounced(250);
    }

    function applyFillOpacityToSelection() {
        if (!canvas) return;
        const active = canvas.getActiveObject();
        if (!active) return;
        const fillValue = fillColor === 'transparent' ? 'transparent' : hexToRgba(fillColor, fillOpacity);
        active.set({ fill: fillValue });
        canvas.requestRenderAll();
        session.isDirty = true;
        pushHistoryDebounced(250);
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
        refreshLayersPanel();
    }

    function deleteSelected() {
        if (!canvas) return;
        const active = canvas.getActiveObjects();
        if (!active || !active.length) return;
        active.forEach(o => {
            if (o.isTemplate || o._isTemplate || o.isOverlay) return;
            canvas.remove(o);
        });
        canvas.discardActiveObject();
        canvas.requestRenderAll();
        session.isDirty = true;
        pushHistory();
    }

    function clearAll() {
        if (!canvas) return;
        if (!canvas.getObjects().length) return;
        if (!confirm('Clear the entire canvas?')) return;
        const nonTemplateObjects = canvas.getObjects().filter(o => !o.isTemplate && !o._isTemplate && !o.isOverlay);
        nonTemplateObjects.forEach(o => canvas.remove(o));
        canvas.requestRenderAll();
        session.isDirty = true;
        pushHistory();
        refreshLayersPanel();
    }

    function setEyeSide(side) {
        currentEyeSide = side;
        modalEl.querySelectorAll('.eye-side-btn').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.eye === side);
        });
        const label = modalEl.querySelector('#eyeSideLabel');
        if (label) {
            label.textContent = side === 'OD' ? 'Right Eye' : 'Left Eye';
        }
    }

    function updateEyeSideUI() {
        setEyeSide(currentEyeSide);
    }

    function addEyeTemplate(templateType) {
        if (!canvas) return;

        const templates = {
            corneaIris: createCorneaIrisTemplate,
            fundus: createFundusTemplate,
            opticDiscMacula: createOpticDiscMaculaTemplate,
            eyelids: createEyelidsTemplate
        };

        const creator = templates[templateType];
        if (!creator) return;

        const template = creator();
        template.isTemplate = true;
        template.templateType = templateType;
        template.layerType = 'template';

        if (currentEyeSide === 'OS') {
            template.set({ flipX: true });
        }

        canvas.add(template);
        canvas.sendToBack(template);
        template.selectable = true;
        template.evented = true;
        template.hasControls = true;
        template.hasBorders = true;
        template.lockMovementX = false;
        template.lockMovementY = false;
        template.lockScalingX = false;
        template.lockScalingY = false;
        template.lockRotation = false;

        currentTemplateType = templateType;
        canvas.requestRenderAll();
        pushHistory();
        refreshLayersPanel();
        setTool('select');
    }

    function clearAllTemplates() {
        if (!canvas) return;
        const templates = canvas.getObjects().filter(o => o.isTemplate);
        if (templates.length === 0) return;
        templates.forEach(t => canvas.remove(t));
        currentTemplateType = null;
        canvas.requestRenderAll();
        pushHistory();
        refreshLayersPanel();
    }

    function createCorneaIrisTemplate() {
        const scale = Math.min(canvas.getWidth(), canvas.getHeight()) * 0.4;
        const centerX = canvas.getWidth() / 2;
        const centerY = canvas.getHeight() / 2;

        const outerCircle = new fabric.Circle({
            radius: scale,
            fill: '#e2e8f0',
            stroke: '#94a3b8',
            strokeWidth: 2,
            originX: 'center',
            originY: 'center'
        });

        const innerCircle = new fabric.Circle({
            radius: scale * 0.6,
            fill: '#cbd5e1',
            stroke: '#64748b',
            strokeWidth: 1,
            originX: 'center',
            originY: 'center'
        });

        const pupil = new fabric.Circle({
            radius: scale * 0.3,
            fill: '#1e293b',
            stroke: '#0f172a',
            strokeWidth: 1,
            originX: 'center',
            originY: 'center'
        });

        const group = new fabric.Group([outerCircle, innerCircle, pupil], {
            left: centerX,
            top: centerY,
            originX: 'center',
            originY: 'center'
        });

        group.objectType = 'template';
        group.medicalType = 'corneaIris';
        group.eyeSide = currentEyeSide;

        return group;
    }

    function createFundusTemplate() {
        const scale = Math.min(canvas.getWidth(), canvas.getHeight()) * 0.4;
        const centerX = canvas.getWidth() / 2;
        const centerY = canvas.getHeight() / 2;

        const fundus = new fabric.Circle({
            radius: scale,
            fill: '#7c3aed',
            stroke: '#5b21b6',
            strokeWidth: 2,
            originX: 'center',
            originY: 'center'
        });

        const vessels = [];
        for (let i = 0; i < 4; i++) {
            const angle = (i * 90) * Math.PI / 180;
            const startX = Math.cos(angle) * scale * 0.3;
            const startY = Math.sin(angle) * scale * 0.3;
            const endX = Math.cos(angle) * scale * 0.95;
            const endY = Math.sin(angle) * scale * 0.95;
            vessels.push(new fabric.Line([startX, startY, endX, endY], {
                stroke: '#a78bfa',
                strokeWidth: 3,
                originX: 'center',
                originY: 'center'
            }));
        }

        const opticDisc = new fabric.Circle({
            radius: scale * 0.15,
            fill: '#fbbf24',
            stroke: '#d97706',
            strokeWidth: 1,
            originX: 'center',
            originY: 'center',
            left: scale * 0.5,
            top: 0
        });

        const macula = new fabric.Circle({
            radius: scale * 0.1,
            fill: '#1e293b',
            originX: 'center',
            originY: 'center',
            left: -scale * 0.3,
            top: -scale * 0.1
        });

        const group = new fabric.Group([fundus, ...vessels, opticDisc, macula], {
            left: centerX,
            top: centerY,
            originX: 'center',
            originY: 'center'
        });

        group.objectType = 'template';
        group.medicalType = 'fundus';
        group.eyeSide = currentEyeSide;

        return group;
    }

    function createOpticDiscMaculaTemplate() {
        const scale = Math.min(canvas.getWidth(), canvas.getHeight()) * 0.35;
        const centerX = canvas.getWidth() / 2;
        const centerY = canvas.getHeight() / 2;

        const opticDisc = new fabric.Circle({
            radius: scale,
            fill: '#fbbf24',
            stroke: '#d97706',
            strokeWidth: 2,
            originX: 'center',
            originY: 'center'
        });

        const cup = new fabric.Circle({
            radius: scale * 0.4,
            fill: '#f59e0b',
            stroke: '#b45309',
            strokeWidth: 1,
            originX: 'center',
            originY: 'center'
        });

        const vessels = [];
        for (let i = 0; i < 6; i++) {
            const angle = (i * 60 + 30) * Math.PI / 180;
            const startX = Math.cos(angle) * scale * 0.5;
            const startY = Math.sin(angle) * scale * 0.5;
            const endX = Math.cos(angle) * scale * 1.2;
            const endY = Math.sin(angle) * scale * 1.2;
            vessels.push(new fabric.Line([startX, startY, endX, endY], {
                stroke: '#dc2626',
                strokeWidth: 2,
                originX: 'center',
                originY: 'center'
            }));
        }

        const macula = new fabric.Circle({
            radius: scale * 0.3,
            fill: 'transparent',
            stroke: '#1e293b',
            strokeWidth: 2,
            originX: 'center',
            originY: 'center',
            left: scale * 2,
            top: 0
        });

        const maculaLabel = new fabric.Text('M', {
            fontSize: 14,
            fill: '#1e293b',
            fontWeight: 'bold',
            originX: 'center',
            originY: 'center',
            left: scale * 2,
            top: 0
        });

        const group = new fabric.Group([opticDisc, cup, ...vessels, macula, maculaLabel], {
            left: centerX,
            top: centerY,
            originX: 'center',
            originY: 'center'
        });

        group.objectType = 'template';
        group.medicalType = 'opticDiscMacula';
        group.eyeSide = currentEyeSide;

        return group;
    }

    function createEyelidsTemplate() {
        const scale = Math.min(canvas.getWidth(), canvas.getHeight()) * 0.45;
        const centerX = canvas.getWidth() / 2;
        const centerY = canvas.getHeight() / 2;

        const upperLid = new fabric.Ellipse({
            rx: scale,
            ry: scale * 0.5,
            fill: 'transparent',
            stroke: '#64748b',
            strokeWidth: 3,
            originX: 'center',
            originY: 'center'
        });

        const lowerLid = new fabric.Ellipse({
            rx: scale,
            ry: scale * 0.4,
            fill: 'transparent',
            stroke: '#64748b',
            strokeWidth: 3,
            originX: 'center',
            originY: 'center'
        });

        const group = new fabric.Group([upperLid, lowerLid], {
            left: centerX,
            top: centerY,
            originX: 'center',
            originY: 'center'
        });

        group.objectType = 'template';
        group.medicalType = 'eyelids';
        group.eyeSide = currentEyeSide;

        return group;
    }

    function addTextAnnotation(point) {
        if (!canvas) return;

        const safeColor = (strokeColor && /^#?[0-9a-f]{3,8}$/i.test(strokeColor)) ? strokeColor : '#0f172a';

        // CRITICAL for Bootstrap modal compatibility: Fabric v5.3 appends the
        // hidden textarea to document.body by default, which puts it OUTSIDE
        // the modal. Bootstrap's focus-trap then steals focus back to the
        // modal element, so the textarea never receives keystrokes. Pointing
        // hiddenTextareaContainer at the canvas wrapper (which is inside the
        // modal) keeps focus where we want it.
        const hiddenContainer = (canvas && canvas.wrapperEl) ? canvas.wrapperEl : null;

        const text = new fabric.Textbox('Note', {
            left: point.x,
            top: point.y,
            width: 150,
            fontSize: 20,
            fontFamily: 'Arial',
            fill: safeColor,
            originX: 'left',
            originY: 'top',
            selectable: true,
            evented: true,
            editable: true,
            hasControls: true,
            hasBorders: true,
            splitByGrapheme: false,
            hiddenTextareaContainer: hiddenContainer,
            objectType: 'text',
            medicalType: 'annotation',
            layerType: 'annotation',
            eyeSide: currentEyeSide
        });

        canvas.add(text);
        canvas.setActiveObject(text);
        canvas.requestRenderAll();
        pushHistory();
        refreshLayersPanel();

        // Enter edit mode synchronously from the mouse:down user-gesture so
        // the browser allows focusing Fabric's hidden textarea. Also force
        // focus on it directly in case Bootstrap's modal focus-trap stole it.
        try {
            text.enterEditing();
            if (typeof text.selectAll === 'function') text.selectAll();
            if (text.hiddenTextarea && typeof text.hiddenTextarea.focus === 'function') {
                text.hiddenTextarea.focus();
            }
            canvas.requestRenderAll();
        } catch (e) { /* ignore */ }

        // A second deferred focus attempt covers cases where the modal's
        // focus-trap moves focus right after our sync call returns.
        setTimeout(() => {
            if (!canvas || canvas.getActiveObject() !== text) return;
            if (!text.isEditing) {
                try { text.enterEditing(); } catch (e) { /* ignore */ }
            }
            if (text.hiddenTextarea && document.activeElement !== text.hiddenTextarea) {
                try { text.hiddenTextarea.focus(); } catch (e) { /* ignore */ }
            }
            canvas.requestRenderAll();
        }, 30);

        // Don't auto-switch tools here. `text:editing:exited` will set the
        // tool back to Select when the user clicks away or hits Escape, which
        // also prevents this function from yanking focus off the new textbox.
        return text;
    }

    function createArrow(startPoint, endPoint) {
        const dx = endPoint.x - startPoint.x;
        const dy = endPoint.y - startPoint.y;
        const length = Math.sqrt(dx * dx + dy * dy);
        const angle = Math.atan2(dy, dx) * 180 / Math.PI;
        const headLength = Math.min(20, length * 0.3);

        const midX = (startPoint.x + endPoint.x) / 2;
        const midY = (startPoint.y + endPoint.y) / 2;

        const line = new fabric.Line([-length / 2, 0, length / 2, 0], {
            stroke: strokeColor,
            strokeWidth: strokeWidth,
            objectType: 'arrowLine',
            medicalType: 'annotation',
            layerType: 'annotation',
            eyeSide: currentEyeSide
        });

        const triangle = new fabric.Triangle({
            left: length / 2,
            top: 0,
            originX: 'center',
            originY: 'center',
            width: headLength,
            height: headLength,
            fill: strokeColor,
            angle: 0,
            objectType: 'arrowHead',
            medicalType: 'annotation',
            layerType: 'annotation',
            eyeSide: currentEyeSide
        });

        const group = new fabric.Group([line, triangle], {
            left: midX,
            top: midY,
            originX: 'center',
            originY: 'center',
            angle: angle,
            objectType: 'arrow',
            medicalType: 'annotation',
            layerType: 'annotation',
            eyeSide: currentEyeSide
        });

        return group;
    }

    function addMedicalStamp(stampType, point) {
        if (!canvas) return;

        const stampConfig = MEDICAL_STAMPS[stampType];
        if (!stampConfig) return;

        const stamp = createStampShape(stampConfig, point);

        stamp.set({
            objectType: 'medicalStamp',
            medicalType: stampConfig.medicalType,
            layerType: 'stamp',
            eyeSide: currentEyeSide,
            isStamp: true
        });

        canvas.add(stamp);
        canvas.setActiveObject(stamp);
        canvas.renderAll();
        pushHistory();
        refreshLayersPanel();
        setTool('select');
    }

    function createStampShape(config, point) {
        const size = 30;
        let shape;
        let isGroup = false;

        switch (config.shape) {
            case 'circle':
                shape = new fabric.Circle({
                    radius: size,
                    fill: hexToRgba(config.color, 0.6),
                    stroke: config.color,
                    strokeWidth: 2,
                    originX: 'center',
                    originY: 'center',
                    left: point.x,
                    top: point.y
                });
                break;
            case 'triangle':
                shape = new fabric.Triangle({
                    width: size * 2,
                    height: size * 2,
                    fill: hexToRgba(config.color, 0.6),
                    stroke: config.color,
                    strokeWidth: 2,
                    originX: 'center',
                    originY: 'center',
                    left: point.x,
                    top: point.y
                });
                break;
            case 'line':
                shape = new fabric.Line([-size, 0, size, 0], {
                    stroke: config.color,
                    strokeWidth: 4,
                    originX: 'center',
                    originY: 'center',
                    left: point.x,
                    top: point.y
                });
                break;
            case 'star':
                const starPoints = [];
                for (let i = 0; i < 5; i++) {
                    starPoints.push({
                        x: Math.cos((i * 72 - 90) * Math.PI / 180) * size,
                        y: Math.sin((i * 72 - 90) * Math.PI / 180) * size
                    });
                    starPoints.push({
                        x: Math.cos(((i * 72) + 36 - 90) * Math.PI / 180) * size * 0.4,
                        y: Math.sin(((i * 72) + 36 - 90) * Math.PI / 180) * size * 0.4
                    });
                }
                shape = new fabric.Polygon(starPoints, {
                    fill: hexToRgba(config.color, 0.6),
                    stroke: config.color,
                    strokeWidth: 2,
                    originX: 'center',
                    originY: 'center',
                    left: point.x,
                    top: point.y
                });
                break;
            case 'wave':
                const wavePath = new fabric.Path(`M ${-size * 2} 0 Q ${-size} ${-size * 0.8} 0 0 T ${size * 2} 0`, {
                    fill: 'transparent',
                    stroke: config.color,
                    strokeWidth: 3,
                    originX: 'center',
                    originY: 'center'
                });
                shape = new fabric.Group([wavePath], {
                    left: point.x,
                    top: point.y,
                    originX: 'center',
                    originY: 'center'
                });
                isGroup = true;
                break;
            case 'cloud':
                const cloudCircle = new fabric.Circle({
                    radius: size * 0.5,
                    fill: hexToRgba(config.color, 0.6),
                    stroke: config.color,
                    strokeWidth: 2,
                    originX: 'center',
                    originY: 'center'
                });
                shape = new fabric.Group([cloudCircle], {
                    left: point.x,
                    top: point.y,
                    originX: 'center',
                    originY: 'center'
                });
                isGroup = true;
                break;
            case 'cross':
                const hLine = new fabric.Line([-size, 0, size, 0], {
                    stroke: config.color,
                    strokeWidth: 4,
                    originX: 'center',
                    originY: 'center'
                });
                const vLine = new fabric.Line([0, -size, 0, size], {
                    stroke: config.color,
                    strokeWidth: 4,
                    originX: 'center',
                    originY: 'center'
                });
                shape = new fabric.Group([hLine, vLine], {
                    left: point.x,
                    top: point.y,
                    originX: 'center',
                    originY: 'center'
                });
                isGroup = true;
                break;
            default:
                shape = new fabric.Circle({
                    radius: size,
                    fill: hexToRgba(config.color, 0.6),
                    stroke: config.color,
                    strokeWidth: 2,
                    originX: 'center',
                    originY: 'center',
                    left: point.x,
                    top: point.y
                });
        }

        if (!isGroup && shape.type === 'group') {
            shape.set({
                left: point.x,
                top: point.y,
                originX: 'center',
                originY: 'center'
            });
        }

        return shape;
    }

    function toggleClockGuide() {
        if (!canvas) return;

        // Look up the current overlay on the canvas instead of trusting the JS ref —
        // loadFromJSON (undo/redo/load) rebuilds objects so the cached ref goes stale.
        const existing = canvas.getObjects().find(o => o.isOverlay && o.layerType === 'overlay');
        if (existing) {
            canvas.remove(existing);
            clockGuideObject = null;
            clockGuideVisible = false;
            modalEl.querySelector('#toggleClockGuide').classList.remove('is-active');
        } else {
            const centerX = canvas.getWidth() / 2;
            const centerY = canvas.getHeight() / 2;
            const radius = Math.min(canvas.getWidth(), canvas.getHeight()) * 0.4;

            const circle = new fabric.Circle({
                radius: radius,
                fill: 'transparent',
                stroke: 'rgba(148, 163, 184, 0.3)',
                strokeWidth: 1,
                originX: 'center',
                originY: 'center',
                left: centerX,
                top: centerY,
                selectable: false,
                evented: false
            });

            const hourLabels = [];
            const hours = [12, 3, 6, 9];
            hours.forEach(h => {
                const angle = (h - 3) * 30 * Math.PI / 180;
                const x = centerX + Math.cos(angle) * (radius + 20);
                const y = centerY + Math.sin(angle) * (radius + 20);
                hourLabels.push(new fabric.Text(h.toString(), {
                    left: x,
                    top: y,
                    fontSize: 14,
                    fill: 'rgba(148, 163, 184, 0.5)',
                    originX: 'center',
                    originY: 'center'
                }));
            });

            clockGuideObject = new fabric.Group([circle, ...hourLabels], {
                selectable: false,
                evented: false,
                isOverlay: true,
                layerType: 'overlay'
            });

            canvas.add(clockGuideObject);
            canvas.sendToBack(clockGuideObject);
            clockGuideVisible = true;
            modalEl.querySelector('#toggleClockGuide').classList.add('is-active');
        }

        canvas.requestRenderAll();
        refreshLayersPanel();
    }

    function refreshLayersPanel() {
        if (!canvas || !modalEl) return;

        const panel = modalEl.querySelector('#layersPanel');
        const list = modalEl.querySelector('#layersList');
        if (!panel || !list) {
            console.warn('Layers panel elements not found');
            return;
        }

        list.innerHTML = '';

        const objects = canvas.getObjects();
        // Hide overlays and eraser marks from the panel — they aren't user-
        // manageable individually; undo/save handle them.
        const visibleObjects = objects.filter(o => !o.isOverlay && o.objectType !== 'eraserMark');

        if (visibleObjects.length === 0) {
            list.innerHTML = '<div class="text-muted small p-2">No layers</div>';
            return;
        }

        visibleObjects.slice().reverse().forEach((obj, idx) => {
            const realIdx = objects.indexOf(obj);
            const rawType = obj.objectType || obj.type || 'object';
            const medical = obj.medicalType || '';
            const layerType = obj.layerType || 'drawing';
            const isLocked = !obj.selectable;
            const isTemplate = !!(obj.isTemplate || obj._isTemplate);
            const isOverlay = !!obj.isOverlay;
            const isStamp = !!obj.isStamp || rawType === 'medicalStamp';
            const canDelete = !isOverlay && !isTemplate && obj.selectable;
            const canReorder = !isOverlay && !isTemplate;

            const div = document.createElement('div');
            div.className = 'draw-layer-item' + (isLocked ? ' is-locked' : '') + (isTemplate ? ' is-template' : '');

            const niceName = (() => {
                if (isTemplate) return `Template: ${medical || 'eye'}`;
                if (isStamp) return `Stamp: ${medical || 'stamp'}`;
                if (rawType === 'text') return 'Text Annotation';
                if (rawType === 'arrow') return 'Arrow Annotation';
                if (rawType === 'shape') return `Shape: ${medical || 'shape'}`;
                if (isOverlay) return 'Clock Guide';
                return rawType;
            })();

            let typeIcon = '';
            if (rawType === 'text') typeIcon = '📝';
            else if (rawType === 'arrow') typeIcon = '➡️';
            else if (isStamp) typeIcon = '🏥';
            else if (rawType === 'shape') typeIcon = '⬜';
            else if (isTemplate) typeIcon = '📋';
            else if (isOverlay) typeIcon = '🕐';
            else typeIcon = '📄';

            const deleteBtnAttrs = canDelete ? '' : 'disabled aria-disabled="true"';
            const reorderBtnAttrs = canReorder ? '' : 'disabled aria-disabled="true"';

            div.innerHTML = `
                <div class="draw-layer-info">
                    <span class="draw-layer-type">${typeIcon} ${niceName}</span>
                    <span class="draw-layer-badge">${layerType}</span>
                </div>
                <div class="draw-layer-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-visibility" title="${obj.visible ? 'Hide' : 'Show'}">
                        <i class="bi ${obj.visible ? 'bi-eye' : 'bi-eye-slash'}"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-lock ${isLocked ? 'is-locked' : ''}" title="${isLocked ? 'Unlock' : 'Lock'}" ${isOverlay ? 'disabled aria-disabled="true"' : ''}>
                        <i class="bi ${isLocked ? 'bi-lock' : 'bi-unlock'}"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary bring-forward" title="Bring Forward" ${reorderBtnAttrs}>
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary send-backward" title="Send Backward" ${reorderBtnAttrs}>
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-layer" title="${canDelete ? 'Delete' : 'Protected'}" ${deleteBtnAttrs}>
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;

            div.querySelector('.toggle-visibility').addEventListener('click', () => {
                obj.visible = !obj.visible;
                if (!obj.visible && canvas.getActiveObject() === obj) {
                    canvas.discardActiveObject();
                }
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
            });

            div.querySelector('.toggle-lock').addEventListener('click', () => {
                if (isOverlay) return;
                const nextLocked = !isLocked;
                obj.selectable = !nextLocked;
                obj.evented = !nextLocked;
                if (nextLocked && canvas.getActiveObject() === obj) {
                    canvas.discardActiveObject();
                }
                obj.locked = nextLocked;
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
            });

            div.querySelector('.bring-forward').addEventListener('click', () => {
                if (!canReorder) return;
                canvas.bringForward(obj);
                // Keep templates/overlays anchored at the bottom regardless.
                canvas.getObjects().forEach(o => {
                    if (o.isOverlay) canvas.sendToBack(o);
                });
                canvas.getObjects().forEach(o => {
                    if (o.isTemplate || o._isTemplate) canvas.sendToBack(o);
                });
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
            });

            div.querySelector('.send-backward').addEventListener('click', () => {
                if (!canReorder) return;
                canvas.sendBackwards(obj);
                canvas.getObjects().forEach(o => {
                    if (o.isOverlay) canvas.sendToBack(o);
                });
                canvas.getObjects().forEach(o => {
                    if (o.isTemplate || o._isTemplate) canvas.sendToBack(o);
                });
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
            });

            div.querySelector('.delete-layer').addEventListener('click', () => {
                if (!canDelete) return;
                canvas.remove(obj);
                canvas.requestRenderAll();
                refreshLayersPanel();
                pushHistory();
            });

            list.appendChild(div);
        });
    }

    function pushHistory() {
        if (lockHistory || !canvas) return;
        const snapshot = JSON.stringify(canvas.toJSON(CUSTOM_PROPERTIES));
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
        refreshLayersPanel();
    }

    function redo() {
        if (!canvas || session.future.length === 0) return;
        const target = session.future.pop();
        session.history.push(target);
        loadFromSnapshot(target);
        refreshHistoryButtons();
        refreshLayersPanel();
    }

    function loadFromSnapshot(snapshot) {
        lockHistory = true;
        canvas.loadFromJSON(snapshot, () => {
            canvas.renderAll();
            lockHistory = false;
            session.isDirty = true;
            refreshLayersPanel();
        });
    }

    function refreshHistoryButtons() {
        const undoBtn = modalEl && modalEl.querySelector('#drawUndoBtn');
        const redoBtn = modalEl && modalEl.querySelector('#drawRedoBtn');
        if (undoBtn) undoBtn.disabled = session.history.length < 2;
        if (redoBtn) redoBtn.disabled = session.future.length === 0;
    }

    function startAutoSave() {
        stopAutoSave();
        autoSaveTimer = setInterval(() => save({ silent: true }), AUTO_SAVE_MS);
    }

    function stopAutoSave() {
        if (autoSaveTimer) clearInterval(autoSaveTimer);
        autoSaveTimer = null;
    }

    function serializeDrawing() {
        return {
            version: '1.0',
            fabricVersion: '5.3',
            module: 'ophthalmology-consultation-drawing',
            eyeSide: currentEyeSide,
            templateType: currentTemplateType,
            width: canvas.getWidth(),
            height: canvas.getHeight(),
            fabricJson: canvas.toJSON(CUSTOM_PROPERTIES),
            thumbnailPng: canvas.toDataURL({ format: 'png', quality: 0.8 }),
            updatedAt: new Date().toISOString()
        };
    }

    function loadDrawing(savedDrawing) {
        if (!canvas || !savedDrawing) return;

        lockHistory = true;

        if (savedDrawing.eyeSide) {
            currentEyeSide = savedDrawing.eyeSide;
            updateEyeSideUI();
        }

        if (savedDrawing.fabricJson) {
            canvas.loadFromJSON(savedDrawing.fabricJson, () => {
                canvas.renderAll();
                lockHistory = false;
                session.isDirty = false;
                session.history = [];
                session.future = [];
                pushHistory();
                refreshLayersPanel();
                refreshHistoryButtons();
            });
        } else {
            lockHistory = false;
        }
    }

    async function copyFromPrevious() {
        if (!canvas || !session.context) return;
        const ctx = session.context;

        // Resolve the previous-drawing payload from context. Two ways:
        //   - context.previousDrawing: already-loaded JSON (sync, fastest).
        //   - context.getPreviousDrawing(): async function returning the JSON.
        let previous = ctx.previousDrawing || null;
        if (!previous && typeof ctx.getPreviousDrawing === 'function') {
            try { previous = await ctx.getPreviousDrawing(); }
            catch (e) {
                console.error('Draw: getPreviousDrawing failed', e);
                alert('Failed to fetch the previous drawing.');
                return;
            }
        }
        if (!previous) {
            alert('No previous drawing is available for this patient.');
            return;
        }

        const hasContent = canvas.getObjects().some(o => !o.isOverlay);
        if (hasContent && !confirm('This will replace the current canvas with the previous consultation drawing. Continue?')) {
            return;
        }

        loadPreviousDrawing(previous);
    }

    function loadPreviousDrawing(previousDrawingJson) {
        if (!previousDrawingJson || !canvas) return;

        const saved = typeof previousDrawingJson === 'string' ? JSON.parse(previousDrawingJson) : previousDrawingJson;

        lockHistory = true;
        canvas.clear();
        canvas.backgroundColor = CANVAS_BG;

        if (saved.eyeSide) {
            currentEyeSide = saved.eyeSide;
            updateEyeSideUI();
        }

        if (saved.fabricJson) {
            canvas.loadFromJSON(saved.fabricJson, () => {
                canvas.renderAll();
                lockHistory = false;
                // Copying a previous drawing IS a user-initiated change, so
                // mark dirty so it can be saved as a new attachment.
                session.isDirty = true;
                session.history = [];
                session.future = [];
                pushHistory();
                refreshLayersPanel();
                refreshHistoryButtons();
            });
        } else {
            lockHistory = false;
        }
    }

    async function save({ silent }) {
        if (!canvas || session.isSaving) return;

        const ctx = session.context;
        if (!ctx) return;

        if (!session.isDirty && session.currentAttachmentId) {
            if (!silent) setAutoSaveStatus('saved');
            return;
        }
        const totalObjects = canvas.getObjects().length;
        const drawableObjects = canvas.getObjects().filter(o => !o.isOverlay);
        if (!totalObjects && !session.currentAttachmentId) {
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

            const drawingMeta = serializeDrawing();
            fd.append('drawing_meta', JSON.stringify(drawingMeta));

            const res = await fetch(url, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (!json || !json.success) {
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

    function resizeCanvas() {
        if (!canvas) return;
        const wrap = document.getElementById('drawCanvasWrap');
        if (!wrap) return;
        const cs = getComputedStyle(wrap);
        const padX = (parseFloat(cs.paddingLeft) || 0) + (parseFloat(cs.paddingRight) || 0);
        const padY = (parseFloat(cs.paddingTop) || 0) + (parseFloat(cs.paddingBottom) || 0);
        const w = Math.max(320, wrap.clientWidth - padX);
        const h = Math.max(240, wrap.clientHeight - padY);
        canvas.setWidth(w);
        canvas.setHeight(h);
        canvas.calcOffset();
        canvas.renderAll();
    }

    function updateStrokePreview() {
        const dot = modalEl && modalEl.querySelector('#drawStrokePreview');
        if (!dot) return;
        const size = Math.max(2, Math.min(28, strokeWidth));
        dot.style.width = size + 'px';
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

    // openForAppointment / openForPatient accept an optional `options` arg so
    // host pages can opt in to features like "Copy Previous Drawing":
    //   DrawConsultation.openForAppointment(appointmentId, patientId, {
    //       previousDrawing: <serializeDrawing() JSON from prior consultation>,
    //       // -- OR --
    //       getPreviousDrawing: async () => fetch('/api/...').then(r => r.json())
    //   });
    // If neither is provided, the Copy Previous button stays hidden.
    function openForAppointment(appointmentId, patientId, options) {
        const opts = options || {};
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
                previousDrawing: opts.previousDrawing || null,
                getPreviousDrawing: typeof opts.getPreviousDrawing === 'function' ? opts.getPreviousDrawing : null,
            }
        });
    }

    function openForPatient(patientId, options) {
        const opts = options || {};
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
                previousDrawing: opts.previousDrawing || null,
                getPreviousDrawing: typeof opts.getPreviousDrawing === 'function' ? opts.getPreviousDrawing : null,
            }
        });
    }

    window.DrawConsultation = {
        open,
        openForAppointment,
        openForPatient,
        serializeDrawing,
        loadDrawing,
        loadPreviousDrawing,
        addEyeTemplate,
        addTextAnnotation,
        addMedicalStamp,
        toggleClockGuide,
        refreshLayersPanel,
        MEDICAL_STAMPS,
        EYE_TEMPLATES
    };
})();

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