<?php
require_once __DIR__ . '/v11-i18n.php';
// =====================================================================
// Notes Drawer — right-side glass drawer that lists quick notes.
// =====================================================================
?>

<aside class="notes-drawer" id="notesDrawer" aria-hidden="true" role="dialog" aria-labelledby="notesDrawerTitle">
    <div class="notes-drawer__backdrop" id="notesDrawerBackdrop" tabindex="-1"></div>

    <div class="notes-drawer__panel" role="document">
        <div class="notes-drawer__drag-handle" aria-hidden="true"></div>

        <header class="notes-drawer__head">
            <div class="notes-drawer__title-wrap">
                <span class="notes-drawer__icon" aria-hidden="true">
                    <i class="bi bi-journal-text"></i>
                </span>
                <h3 id="notesDrawerTitle"><?= v11e('notes.title', 'Notes') ?></h3>
                <span class="notes-drawer__count" id="notesDrawerCount" hidden>0</span>
            </div>
            <button type="button" class="notes-drawer__close" id="notesDrawerClose" aria-label="<?= v11e('notes.close', 'Close notes') ?>">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </header>

        <div class="notes-drawer__filters" role="tablist" aria-label="<?= v11e('notes.filter', 'Filter notes') ?>">
            <button type="button" class="nd-filter active" data-filter="all" role="tab" aria-selected="true"><?= v11e('notes.filter.all', 'All') ?></button>
            <button type="button" class="nd-filter" data-filter="pinned" role="tab" aria-selected="false">
                <i class="bi bi-pin-angle-fill" aria-hidden="true"></i>
                <?= v11e('notes.filter.pinned', 'Pinned') ?>
            </button>
            <button type="button" class="nd-filter" data-filter="recent" role="tab" aria-selected="false"><?= v11e('notes.filter.recent', 'Recent') ?></button>
        </div>

        <div class="notes-drawer__search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="text" class="nd-search-input" id="notesDrawerSearch"
                   placeholder="<?= v11e('notes.search_ph', 'Search notes…') ?>" autocomplete="off" spellcheck="false">
            <button type="button" class="nd-search-clear" id="notesDrawerSearchClear" hidden aria-label="<?= v11e('notes.clear_search', 'Clear search') ?>">
                <i class="bi bi-x" aria-hidden="true"></i>
            </button>
        </div>

        <form class="notes-drawer__quick-add" id="notesDrawerQuickAdd" autocomplete="off">
            <textarea
                class="nd-quick-input"
                id="notesDrawerQuickInput"
                placeholder="<?= v11e('notes.quick_ph', 'Jot a quick note… (⌘+Enter to save)') ?>"
                rows="1"
                maxlength="50000"></textarea>
            <button type="submit" class="nd-quick-submit" aria-label="<?= v11e('notes.save', 'Save note') ?>" disabled>
                <i class="bi bi-arrow-up" aria-hidden="true"></i>
            </button>
        </form>

        <div class="notes-drawer__list" id="notesDrawerList" role="list">
            <div class="nd-skeleton" aria-hidden="true">
                <div class="nd-skeleton__row"></div>
                <div class="nd-skeleton__row"></div>
                <div class="nd-skeleton__row"></div>
            </div>
        </div>

        <button type="button" class="nd-fab" id="notesDrawerFab" aria-label="<?= v11e('notes.fab', 'New note with full options') ?>">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
        </button>
    </div>

    <div class="nd-modal" id="notesDrawerModal" hidden aria-hidden="true" role="dialog" aria-labelledby="ndModalTitle">
        <div class="nd-modal__backdrop"></div>
        <div class="nd-modal__panel" role="document">
            <header class="nd-modal__head">
                <h3 id="ndModalTitle"><?= v11e('notes.new_note', 'New note') ?></h3>
                <button type="button" class="nd-modal__close" id="ndModalClose" aria-label="<?= v11e('notes.close', 'Close') ?>">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </header>
            <form class="nd-modal__body" id="ndModalForm">
                <input type="hidden" name="id" value="">
                <label class="nd-field">
                    <span class="nd-field__label"><?= v11e('notes.field.title', 'Title (optional)') ?></span>
                    <input type="text" name="title" maxlength="200"
                           placeholder="<?= v11e('notes.field.title_ph', 'Short heading') ?>">
                </label>
                <label class="nd-field">
                    <span class="nd-field__label"><?= v11e('notes.field.body', 'Body') ?></span>
                    <textarea name="body" rows="8" required maxlength="50000"
                              placeholder="<?= v11e('notes.field.body_ph', 'Write your note…') ?>"></textarea>
                </label>
                <div class="nd-field note-swatch-field">
                    <span class="note-swatch-label"><?= v11e('notes.field.bg', 'Background') ?></span>
                    <div id="ndModalSwatches"></div>
                    <input type="hidden" name="background_color" id="ndModalBg" value="">
                </div>
                <label class="nd-field nd-field--inline">
                    <input type="checkbox" name="pinned" value="1">
                    <span><i class="bi bi-pin-angle" aria-hidden="true"></i> <?= v11e('notes.pin_top', 'Pin to top') ?></span>
                </label>
                <footer class="nd-modal__foot">
                    <button type="button" class="nd-btn nd-btn--ghost" data-nd-cancel><?= v11e('notes.cancel', 'Cancel') ?></button>
                    <button type="submit" class="nd-btn nd-btn--primary"><?= v11e('notes.save_btn', 'Save') ?></button>
                </footer>
            </form>
        </div>
    </div>

    <template id="notesDrawerRowTpl">
        <article class="nd-row" role="listitem">
            <header class="nd-row__head">
                <button type="button" class="nd-row__pin" data-act="pin" aria-label="<?= v11e('notes.pin_toggle', 'Pin / unpin') ?>">
                    <i class="bi bi-pin-angle" aria-hidden="true"></i>
                </button>
                <div class="nd-row__title-wrap">
                    <h4 class="nd-row__title" data-field="title"></h4>
                    <time class="nd-row__time" data-field="time"></time>
                </div>
                <div class="nd-row__actions">
                    <button type="button" class="nd-row__act" data-act="edit" aria-label="<?= v11e('notes.edit', 'Edit') ?>">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="nd-row__act nd-row__act--danger" data-act="delete" aria-label="<?= v11e('notes.delete', 'Delete') ?>">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </header>
            <p class="nd-row__body" data-field="body"></p>
        </article>
    </template>
</aside>
