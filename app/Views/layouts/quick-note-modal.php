<?php
// =====================================================================
// Quick Note Modal  —  lightweight notepad / scratchpad.
//
// Opens via window.openQuickNoteModal() (see assets/js/quick-note.js).
// Saves to POST /api/quick-notes; recent panel toggles below the form
// to list/edit/pin/delete the most recent 20 notes (pinned first).
//
// All accent colours come from the design-system tokens in tokens.css
// (--accent / --ds-primary / --glass-*), so palette switching just works.
// Drag, centering and dark-mode are inherited from modal-kit.
// =====================================================================
?>
<link rel="stylesheet" href="<?php echo base_url('app/Views/doctor/assets/css/quick-note.css'); ?>">

<div class="modal fade quick-note-modal"
     id="quickNoteModal"
     tabindex="-1"
     aria-labelledby="quickNoteModalTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content qn-content">

            <div class="modal-header qn-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="quickNoteModalTitle">
                    <span class="qn-icon" aria-hidden="true">
                        <i class="bi bi-sticky-fill"></i>
                    </span>
                    <span>Quick Note</span>
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body qn-body">
                <form id="quickNoteForm" class="qn-form" autocomplete="off" novalidate>
                    <input type="hidden" name="id" id="qnNoteId" value="">

                    <div class="qn-field">
                        <label for="qnTitle" class="qn-label">
                            Title
                            <span class="qn-label-hint">optional</span>
                        </label>
                        <input type="text"
                               class="form-control qn-input"
                               id="qnTitle"
                               name="title"
                               maxlength="200"
                               placeholder="Give this note a title (optional)">
                        <div class="qn-counter" aria-live="polite">
                            <span id="qnTitleCount">0</span><span class="qn-counter-sep">/</span><span>200</span>
                        </div>
                    </div>

                    <div class="qn-field">
                        <label for="qnBody" class="qn-label">
                            Note
                            <span class="qn-label-hint qn-label-hint--req">required</span>
                        </label>
                        <textarea class="form-control qn-textarea"
                                  id="qnBody"
                                  name="body"
                                  rows="7"
                                  required
                                  aria-required="true"
                                  placeholder="Jot something down… (Ctrl/Cmd+Enter to save)"></textarea>
                        <div class="qn-error" id="qnBodyError" role="alert" hidden>
                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                            <span>Please write something before saving.</span>
                        </div>
                    </div>

                    <div class="qn-row">
                        <label class="qn-pin-toggle">
                            <input type="checkbox" id="qnPin" name="pinned">
                            <span class="qn-pin-track" aria-hidden="true">
                                <span class="qn-pin-thumb">
                                    <i class="bi bi-pin-angle-fill"></i>
                                </span>
                            </span>
                            <span class="qn-pin-text">Pin this note</span>
                        </label>

                        <div class="qn-inline-confirm" id="qnInlineConfirm" role="status" aria-live="polite" hidden>
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            <span id="qnInlineConfirmText">Saved.</span>
                        </div>
                    </div>

                    <div class="qn-field note-swatch-field">
                        <span class="note-swatch-label">Background</span>
                        <div id="qnSwatches"><!-- NoteBG.swatchHTML injected by JS --></div>
                        <input type="hidden" name="background_color" id="qnBg" value="">
                    </div>
                </form>

                <div class="qn-divider" aria-hidden="true"></div>

                <div class="qn-recent-bar">
                    <button type="button"
                            class="qn-toggle"
                            id="qnRecentToggle"
                            aria-expanded="false"
                            aria-controls="qnRecentPanel">
                        <i class="bi bi-journal-text" aria-hidden="true"></i>
                        <span class="qn-toggle-text">View saved notes</span>
                        <span class="qn-toggle-count" id="qnRecentCount" hidden>0</span>
                        <i class="bi bi-chevron-down qn-toggle-chev" aria-hidden="true"></i>
                    </button>
                    <button type="button"
                            class="qn-refresh"
                            id="qnRefresh"
                            title="Refresh list"
                            aria-label="Refresh saved notes"
                            hidden>
                        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="qn-recent-panel"
                     id="qnRecentPanel"
                     role="region"
                     aria-labelledby="qnRecentToggle"
                     hidden>
                    <div class="qn-recent-status" id="qnRecentStatus" aria-live="polite">
                        <div class="qn-loading">
                            <span class="qn-spinner" aria-hidden="true"></span>
                            <span>Loading saved notes…</span>
                        </div>
                    </div>
                    <ul class="qn-recent-list" id="qnRecentList" hidden></ul>
                </div>
            </div>

            <div class="modal-footer qn-footer">
                <span class="qn-footer-hint" aria-hidden="true">
                    <kbd>Ctrl</kbd><span>+</span><kbd>Enter</kbd>
                    <span class="qn-footer-hint-sep">to save</span>
                </span>
                <div class="qn-footer-actions">
                    <button type="button"
                            class="btn btn-light qn-btn qn-btn-cancel"
                            id="qnCancelBtn"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button"
                            class="btn btn-primary qn-btn qn-btn-save"
                            id="qnSaveBtn">
                        <span class="qn-btn-icon" aria-hidden="true">
                            <i class="bi bi-check2"></i>
                        </span>
                        <span class="qn-btn-label" id="qnSaveBtnLabel">Save</span>
                        <span class="qn-btn-spinner" aria-hidden="true" hidden></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
<?php /* quick-note.js is loaded once (deferred + cache-busted) from layouts/main.php.
        Do NOT re-include it here: a second, un-versioned tag served a stale cached
        copy that won the init-guard race and shipped the old (pre-gradient) behaviour. */ ?>
