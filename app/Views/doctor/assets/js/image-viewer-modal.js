/**
 * Unified image viewer — Bootstrap modal + zoom glass toolbar.
 * Used by patient.js, appointment.js; zoom helpers reused by comment-media lightbox.
 */
(function (global) {
    "use strict";

    var ZOOM_MIN = 0.5;
    var ZOOM_MAX = 4;
    var ZOOM_STEP = 0.25;

    function escapeHtml(text) {
        if (text == null) return "";
        var div = document.createElement("div");
        div.textContent = String(text);
        return div.innerHTML;
    }

    function escapeAttr(text) {
        return escapeHtml(text).replace(/"/g, "&quot;");
    }

    function zoomPanelHtml() {
        return (
            '<div class="iv-zoom-glass" role="toolbar" aria-label="Image zoom">' +
            '<button type="button" class="iv-zoom-btn" data-iv-zoom="out" aria-label="Zoom out"><i class="bi bi-zoom-out"></i></button>' +
            '<button type="button" class="iv-zoom-btn" data-iv-zoom="reset" aria-label="Reset zoom"><i class="bi bi-aspect-ratio"></i></button>' +
            '<button type="button" class="iv-zoom-btn" data-iv-zoom="in" aria-label="Zoom in"><i class="bi bi-zoom-in"></i></button>' +
            "</div>"
        );
    }

    function bindZoomControls(root, img) {
        var noop = { reset: function () {} };
        if (!root || !img) return noop;
        var scale = 1;
        function apply() {
            img.style.transform = "scale(" + scale + ")";
        }
        function reset() {
            scale = 1;
            apply();
        }
        root.addEventListener("click", function (e) {
            var btn = e.target.closest("[data-iv-zoom]");
            if (!btn || !root.contains(btn)) return;
            e.preventDefault();
            e.stopPropagation();
            var action = btn.getAttribute("data-iv-zoom");
            if (action === "in") scale = Math.min(ZOOM_MAX, +(scale + ZOOM_STEP).toFixed(2));
            else if (action === "out") scale = Math.max(ZOOM_MIN, +(scale - ZOOM_STEP).toFixed(2));
            else reset();
            apply();
        });
        apply();
        return { reset: reset };
    }

    function showImageViewerModal(opts) {
        opts = opts || {};
        var existing = document.getElementById("imageModal");
        if (existing) existing.remove();

        var title = opts.title || "View Image";
        var titleHtml = opts.titleHtml || ("<i class=\"bi bi-image me-2\"></i>" + escapeHtml(title));
        var footerHtml = opts.footerHtml || (
            '<button type="button" class="btn btn-secondary iv-footer-btn" data-bs-dismiss="modal">Close</button>'
        );
        var dialogClass = opts.dialogClass || "modal-xl";
        var contentClass = opts.contentClass || "image-modal-glass";
        var closeClass = opts.closeWhite ? "btn-close btn-close-white" : "btn-close";

        var html =
            '<div class="modal fade iv-image-modal" id="imageModal" tabindex="-1">' +
            '<div class="modal-dialog ' + dialogClass + ' modal-dialog-centered">' +
            '<div class="modal-content ' + contentClass + '">' +
            '<div class="modal-header image-modal-header">' +
            '<h5 class="modal-title">' + titleHtml + "</h5>" +
            '<button type="button" class="' + closeClass + '" data-bs-dismiss="modal" aria-label="Close"></button>' +
            "</div>" +
            '<div class="modal-body image-modal-body">' +
            '<div class="iv-image-stage">' +
            '<img src="' + escapeAttr(opts.imageUrl) + '" class="iv-image-target img-fluid" alt="' + escapeAttr(title) + '">' +
            zoomPanelHtml() +
            "</div></div>" +
            '<div class="modal-footer image-modal-footer iv-image-modal-footer">' + footerHtml + "</div>" +
            "</div></div></div>";

        document.body.insertAdjacentHTML("beforeend", html);
        var modalEl = document.getElementById("imageModal");
        var stage = modalEl.querySelector(".iv-image-stage");
        var img = modalEl.querySelector(".iv-image-target");
        bindZoomControls(stage, img);

        var modal = new bootstrap.Modal(modalEl);
        modalEl.addEventListener("shown.bs.modal", function () {
            document.body.classList.add("iv-image-modal-open");
        });
        modalEl.addEventListener("hidden.bs.modal", function () {
            document.body.classList.remove("iv-image-modal-open");
            modalEl.remove();
        });
        modal.show();

        if (typeof opts.onShown === "function") {
            modalEl.addEventListener("shown.bs.modal", function once() {
                opts.onShown(modalEl);
                modalEl.removeEventListener("shown.bs.modal", once);
            });
        }

        return modal;
    }

    global.ImageViewerModal = {
        show: showImageViewerModal,
        bindZoom: bindZoomControls,
        zoomPanelHtml: zoomPanelHtml,
    };
})(window);
