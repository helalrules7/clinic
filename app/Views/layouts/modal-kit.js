/* =====================================================================
   MODAL KIT  —  shared drag behaviour for EVERY Bootstrap modal.
   Loaded by layouts/main.php and layouts/secretary_main.php.

   Why delegation (one listener on document) instead of binding each
   modal: modals are created dynamically all over the app (appointment.js,
   patient.js, patients.js, draw-consultation.js, …). A single delegated
   handler covers static AND not-yet-existing modals with zero per-modal
   wiring — and it never double-binds.

   Behaviour:
     - Drag from the .modal-header; the .modal-dialog moves via transform.
     - Movement is FREE across the whole page (not boxed into a region),
       with only a minimal guard so a modal can never be lost 100%
       off-screen.
     - Centering + open/close animation are handled in modal-kit.css.
     - Works with mouse, touch and pen (Pointer Events).

   Exclusions: #drawConsultationModal (full-screen tool), any
   .modal-fullscreen*, and [data-no-drag]. The global search is a custom
   overlay (not a .modal) so it is never matched here.
   ===================================================================== */
(function () {
    if (window.__modalKit) return;
    window.__modalKit = true;

    // Legacy shim: ~30 call-sites still do
    //   if (typeof initializeDraggableModals === 'function') initializeDraggableModals();
    // Drag is now global/delegated, so this is just a harmless no-op that
    // keeps those guards happy. (The old per-page copies are neutralised.)
    if (typeof window.initializeDraggableModals !== 'function') {
        window.initializeDraggableModals = function () {};
    }

    var EXCLUDE_IDS = { drawConsultationModal: 1 };
    var GUARD = 64; // px of the dialog kept on-screen on each axis.

    function isExcluded(modal) {
        if (!modal) return true;
        if (EXCLUDE_IDS[modal.id]) return true;
        if (modal.hasAttribute('data-no-drag')) return true;
        if (/modal-fullscreen/.test(modal.className)) return true;
        return false;
    }

    // True if the pointer started on something the user expects to click,
    // so we don't hijack buttons / inputs / links / the close icon.
    function isInteractive(el, stopAt) {
        while (el && el !== stopAt && el.nodeType === 1) {
            var t = el.tagName;
            if (t === 'BUTTON' || t === 'A' || t === 'INPUT' || t === 'SELECT' ||
                t === 'TEXTAREA' || t === 'LABEL' ||
                (el.classList && el.classList.contains('btn-close'))) {
                return true;
            }
            el = el.parentElement;
        }
        return false;
    }

    var drag = null;

    function onPointerDown(e) {
        // Primary button / touch / pen only.
        if (e.button !== undefined && e.button !== 0) return;
        var target = e.target;
        if (!target || !target.closest) return;

        var header = target.closest('.modal-header');
        if (!header) return;
        var modal = header.closest('.modal');
        if (isExcluded(modal)) return;
        if (isInteractive(target, header)) return;

        var dialog = modal.querySelector('.modal-dialog');
        if (!dialog) return;

        var baseX = parseFloat(dialog.dataset.mkX || '0') || 0;
        var baseY = parseFloat(dialog.dataset.mkY || '0') || 0;

        // Capture the dialog's untransformed position once, so the on-screen
        // guard math stays stable for the whole drag.
        var r = dialog.getBoundingClientRect();
        drag = {
            dialog: dialog,
            header: header,
            startX: e.clientX,
            startY: e.clientY,
            baseX: baseX,
            baseY: baseY,
            untransLeft: r.left - baseX,
            untransTop: r.top - baseY,
            w: r.width,
            h: r.height,
            moved: false
        };

        header.classList.add('mk-grabbing');
        window.addEventListener('pointermove', onPointerMove, true);
        window.addEventListener('pointerup', onPointerUp, true);
        window.addEventListener('pointercancel', onPointerUp, true);
    }

    function onPointerMove(e) {
        if (!drag) return;
        var dx = e.clientX - drag.startX;
        var dy = e.clientY - drag.startY;

        if (!drag.moved) {
            // Click-vs-drag threshold so a plain click on the header title
            // (e.g. to focus) isn't treated as a drag.
            if (Math.abs(dx) < 3 && Math.abs(dy) < 3) return;
            drag.moved = true;
            drag.dialog.classList.add('mk-dragging');
        }
        e.preventDefault();
        e.stopPropagation();

        var x = drag.baseX + dx;
        var y = drag.baseY + dy;

        // Free movement, guarded so >= GUARD px of the dialog always stays
        // within the viewport (can never be dragged fully off-screen).
        var vw = window.innerWidth;
        var vh = window.innerHeight;
        var minX = GUARD - drag.w - drag.untransLeft;
        var maxX = vw - GUARD - drag.untransLeft;
        var minY = GUARD - drag.h - drag.untransTop;
        var maxY = vh - GUARD - drag.untransTop;
        if (x < minX) x = minX;
        if (x > maxX) x = maxX;
        if (y < minY) y = minY;
        if (y > maxY) y = maxY;

        drag.dialog.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
        drag.dialog.dataset.mkX = x;
        drag.dialog.dataset.mkY = y;
    }

    function onPointerUp() {
        if (!drag) return;
        if (drag.header) drag.header.classList.remove('mk-grabbing');
        if (drag.dialog) drag.dialog.classList.remove('mk-dragging');
        drag = null;
        window.removeEventListener('pointermove', onPointerMove, true);
        window.removeEventListener('pointerup', onPointerUp, true);
        window.removeEventListener('pointercancel', onPointerUp, true);
    }

    // Reset position whenever a modal closes, so it always re-opens centered.
    function onHidden(e) {
        var modal = e.target;
        if (!modal || !modal.querySelector) return;
        var dialog = modal.querySelector('.modal-dialog');
        if (!dialog) return;
        dialog.style.transform = '';
        dialog.classList.remove('mk-dragging');
        delete dialog.dataset.mkX;
        delete dialog.dataset.mkY;
    }

    // Capture phase so we run before any (now-neutralised) element-bound
    // legacy handlers, and so we reliably catch bubbled Bootstrap events.
    document.addEventListener('pointerdown', onPointerDown, true);
    document.addEventListener('hidden.bs.modal', onHidden, true);

    // =====================================================================
    // Reusable confirm / alert modals — a themed replacement for the native
    // window.confirm() / window.alert(). The dialog is a normal Bootstrap
    // .modal so it inherits centering, animation and drag from this kit, and
    // its colours come from .modal-content tokens (so dark mode just works).
    //
    //   const ok = await showConfirmModal({ title, message, confirmText,
    //                                       cancelText, confirmClass, icon });
    //   await showAlertModal({ title, message, okText, icon });
    // =====================================================================
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function v11t(key, en) {
        return (window.V11I18n && window.V11I18n.t(key, en)) || en;
    }

    function resolveConfirmClass(opts, withCancel) {
        if (opts.confirmClass) return opts.confirmClass;
        var variant = opts.confirmVariant || opts.okVariant;
        if (variant) {
            return variant.indexOf('btn-') === 0 ? variant : 'btn-' + variant;
        }
        return withCancel ? 'btn-danger' : 'btn-primary';
    }

    function buildDialog(opts, withCancel) {
        opts = opts || {};
        var title = opts.title != null ? opts.title : (withCancel ? v11t('modal.confirm_title', 'Please confirm') : v11t('modal.notice', 'Notice'));
        var message = opts.message != null ? opts.message : (withCancel ? v11t('modal.confirm_msg', 'Are you sure?') : '');
        var icon = opts.icon || (withCancel ? 'bi-exclamation-triangle' : 'bi-info-circle');
        var confirmText = opts.confirmText || opts.okText || (withCancel ? v11t('modal.confirm', 'Confirm') : v11t('modal.ok', 'OK'));
        var cancelText = opts.cancelText || v11t('modal.cancel', 'Cancel');
        var confirmClass = resolveConfirmClass(opts, withCancel);
        var body = opts.html ? message : esc(message);

        var wrap = document.createElement('div');
        wrap.innerHTML =
            '<div class="modal fade mk-dialog" tabindex="-1" aria-hidden="true">' +
              '<div class="modal-dialog modal-dialog-centered">' +
                '<div class="modal-content">' +
                  '<div class="modal-header">' +
                    '<h5 class="modal-title d-flex align-items-center gap-2"><i class="bi ' + icon + '" aria-hidden="true"></i><span>' + esc(title) + '</span></h5>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' + esc(v11t('modal.close', 'Close')) + '"></button>' +
                  '</div>' +
                  '<div class="modal-body">' + body + '</div>' +
                  '<div class="modal-footer">' +
                    (withCancel ? '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' + esc(cancelText) + '</button>' : '') +
                    '<button type="button" class="btn ' + confirmClass + '" data-mk-confirm>' + esc(confirmText) + '</button>' +
                  '</div>' +
                '</div>' +
              '</div>' +
            '</div>';
        return wrap.firstChild;
    }

    function runDialog(opts, withCancel) {
        return new Promise(function (resolve) {
            // No Bootstrap (shouldn't happen) → fall back to the native dialog.
            if (!window.bootstrap || !window.bootstrap.Modal) {
                if (withCancel) resolve(window.confirm((opts && opts.message) || ''));
                else { window.alert((opts && opts.message) || ''); resolve(true); }
                return;
            }
            var modalEl = buildDialog(opts, withCancel);
            document.body.appendChild(modalEl);
            var modal = new bootstrap.Modal(modalEl, { backdrop: withCancel ? true : true });
            var confirmed = false;
            var btn = modalEl.querySelector('[data-mk-confirm]');
            if (btn) btn.addEventListener('click', function () { confirmed = true; modal.hide(); });
            modalEl.addEventListener('hidden.bs.modal', function () {
                modalEl.remove();
                resolve(withCancel ? confirmed : true);
            });
            modal.show();
            // Focus the primary action for keyboard users.
            modalEl.addEventListener('shown.bs.modal', function () { if (btn) btn.focus(); });
        });
    }

    if (typeof window.showConfirmModal !== 'function') {
        window.showConfirmModal = function (opts) { return runDialog(opts, true); };
    }
    if (typeof window.showAlertModal !== 'function') {
        window.showAlertModal = function (opts) { return runDialog(opts, false); };
    }
    // Collision-proof aliases. patients.js declares its own legacy
    // `function showConfirmModal(title, message, onConfirm, ...)` at the
    // top level (hoists to window before this script runs and wins the
    // `typeof` guard above), so callers that want the modal-kit Promise
    // API should use these names instead.
    window.mkConfirmModal = function (opts) { return runDialog(opts, true); };
    window.mkAlertModal   = function (opts) { return runDialog(opts, false); };
})();
