<?php
// =====================================================================
// Notes Drawer — right-side glass drawer that lists quick notes.
//
// Open via: window.openNotesDrawer()  (defined in /assets/js/notes-drawer.js)
// Backed by: GET /api/quick-notes, POST, PATCH, DELETE, .../pin .../unpin
//
// Mirrors the visual + behaviour patterns of the To-Do drawer
// (right-side slide-in on desktop, full-screen bottom-sheet on mobile).
// =====================================================================
?>

<aside class="notes-drawer" id="notesDrawer" aria-hidden="true" role="dialog" aria-labelledby="notesDrawerTitle">
    <div class="notes-drawer__backdrop" id="notesDrawerBackdrop" tabindex="-1"></div>

    <div class="notes-drawer__panel" role="document">
        <div class="notes-drawer__drag-handle" aria-hidden="true"></div>

        <!-- Header ----------------------------------------------------- -->
        <header class="notes-drawer__head">
            <div class="notes-drawer__title-wrap">
                <span class="notes-drawer__icon" aria-hidden="true">
                    <i class="bi bi-journal-text"></i>
                </span>
                <h3 id="notesDrawerTitle">Notes</h3>
                <span class="notes-drawer__count" id="notesDrawerCount" hidden>0</span>
            </div>
            <button type="button" class="notes-drawer__close" id="notesDrawerClose" aria-label="Close notes">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </header>

        <!-- Filters --------------------------------------------------- -->
        <div class="notes-drawer__filters" role="tablist" aria-label="Filter notes">
            <button type="button" class="nd-filter active" data-filter="all" role="tab" aria-selected="true">All</button>
            <button type="button" class="nd-filter" data-filter="pinned" role="tab" aria-selected="false">
                <i class="bi bi-pin-angle-fill" aria-hidden="true"></i>
                Pinned
            </button>
            <button type="button" class="nd-filter" data-filter="recent" role="tab" aria-selected="false">Recent</button>
        </div>

        <!-- Search ---------------------------------------------------- -->
        <div class="notes-drawer__search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="text" class="nd-search-input" id="notesDrawerSearch"
                   placeholder="Search notes…" autocomplete="off" spellcheck="false">
            <button type="button" class="nd-search-clear" id="notesDrawerSearchClear" hidden aria-label="Clear search">
                <i class="bi bi-x" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Quick add ------------------------------------------------- -->
        <form class="notes-drawer__quick-add" id="notesDrawerQuickAdd" autocomplete="off">
            <textarea
                class="nd-quick-input"
                id="notesDrawerQuickInput"
                placeholder="Jot a quick note… (⌘+Enter to save)"
                rows="1"
                maxlength="50000"></textarea>
            <button type="submit" class="nd-quick-submit" aria-label="Save note" disabled>
                <i class="bi bi-arrow-up" aria-hidden="true"></i>
            </button>
        </form>

        <!-- Notes list ------------------------------------------------ -->
        <div class="notes-drawer__list" id="notesDrawerList" role="list">
            <!-- Skeleton -->
            <div class="nd-skeleton" aria-hidden="true">
                <div class="nd-skeleton__row"></div>
                <div class="nd-skeleton__row"></div>
                <div class="nd-skeleton__row"></div>
            </div>
        </div>

        <!-- FAB — open full-form editor (title + body + pin) ----------- -->
        <button type="button" class="nd-fab" id="notesDrawerFab" aria-label="New note with full options">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Full editor modal — nested inside drawer for stacking control -->
    <div class="nd-modal" id="notesDrawerModal" hidden aria-hidden="true" role="dialog" aria-labelledby="ndModalTitle">
        <div class="nd-modal__backdrop"></div>
        <div class="nd-modal__panel" role="document">
            <header class="nd-modal__head">
                <h3 id="ndModalTitle">New note</h3>
                <button type="button" class="nd-modal__close" id="ndModalClose" aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>
            <form class="nd-modal__body" id="ndModalForm">
                <input type="hidden" name="id" value="">
                <label class="nd-field">
                    <span class="nd-field__label">Title (optional)</span>
                    <input type="text" name="title" maxlength="200"
                           placeholder="Short heading">
                </label>
                <label class="nd-field">
                    <span class="nd-field__label">Body</span>
                    <textarea name="body" rows="8" required maxlength="50000"
                              placeholder="Write your note…"></textarea>
                </label>
                <label class="nd-field nd-field--inline">
                    <input type="checkbox" name="pinned" value="1">
                    <span><i class="bi bi-pin-angle" aria-hidden="true"></i> Pin to top</span>
                </label>
                <footer class="nd-modal__foot">
                    <button type="button" class="nd-btn nd-btn--ghost" data-nd-cancel>Cancel</button>
                    <button type="submit" class="nd-btn nd-btn--primary">Save</button>
                </footer>
            </form>
        </div>
    </div>

    <!-- Row template (cloned per note) ----------------------------- -->
    <template id="notesDrawerRowTpl">
        <article class="nd-row" role="listitem">
            <header class="nd-row__head">
                <button type="button" class="nd-row__pin" data-act="pin" aria-label="Pin / unpin">
                    <i class="bi bi-pin-angle" aria-hidden="true"></i>
                </button>
                <div class="nd-row__title-wrap">
                    <h4 class="nd-row__title" data-field="title"></h4>
                    <time class="nd-row__time" data-field="time"></time>
                </div>
                <div class="nd-row__actions">
                    <button type="button" class="nd-row__act" data-act="edit" aria-label="Edit">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="nd-row__act nd-row__act--danger" data-act="delete" aria-label="Delete">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </header>
            <p class="nd-row__body" data-field="body"></p>
        </article>
    </template>
</aside>
