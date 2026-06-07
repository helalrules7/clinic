<?php
require_once __DIR__ . '/v11-i18n.php';
/**
 * Command Palette (Cmd+K / Ctrl+K)
 */
?>
<div class="cmdk" id="cmdk" role="dialog" aria-modal="true" aria-labelledby="cmdkTitle" hidden>
    <div class="cmdk__backdrop" id="cmdkBackdrop" aria-hidden="true"></div>

    <div class="cmdk__panel" id="cmdkPanel" role="document">
        <h2 id="cmdkTitle" class="visually-hidden"><?= v11e('cmdk.title', 'Command palette') ?></h2>

        <div class="cmdk__header">
            <span class="cmdk__search-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input
                type="text"
                id="cmdkInput"
                class="cmdk__input"
                placeholder="<?= v11e('cmdk.placeholder', 'Search patients, pages, actions, to-dos…') ?>"
                autocomplete="off"
                autocapitalize="off"
                spellcheck="false"
                aria-label="<?= v11e('cmdk.search', 'Search') ?>"
                aria-controls="cmdkResults"
                aria-autocomplete="list"
            />
            <button type="button" class="cmdk__help-btn" id="cmdkHelp" aria-label="<?= v11e('cmdk.smart_help', 'Smart actions help') ?>" title="<?= v11e('cmdk.smart_help', 'Smart actions help') ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </button>
            <button type="button" class="cmdk__close" id="cmdkClose" aria-label="<?= v11e('cmdk.close', 'Close command palette') ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <kbd class="cmdk__close-kbd" aria-hidden="true">Esc</kbd>
            </button>
        </div>

        <div class="cmdk__tabs" role="tablist" id="cmdkTabs" aria-label="<?= v11e('cmdk.filter', 'Filter results') ?>">
            <button type="button" class="cmdk__tab is-active" role="tab" data-scope="all" aria-selected="true"><?= v11e('cmdk.tab.all', 'All') ?></button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="patients" aria-selected="false"><?= v11e('cmdk.tab.patients', 'Patients') ?></button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="pages" aria-selected="false"><?= v11e('cmdk.tab.pages', 'Pages') ?></button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="actions" aria-selected="false"><?= v11e('cmdk.tab.actions', 'Actions') ?></button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="todos" aria-selected="false"><?= v11e('cmdk.tab.todos', 'To-dos') ?></button>
        </div>

        <div
            class="cmdk__results"
            id="cmdkResults"
            role="listbox"
            aria-label="<?= v11e('cmdk.results', 'Search results') ?>"
            tabindex="-1"
        >
            <div class="cmdk__empty" id="cmdkEmpty">
                <div class="cmdk__empty-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <p class="cmdk__empty-title"><?= v11e('cmdk.empty_title', 'Start typing to search') ?></p>
                <p class="cmdk__empty-sub"><?= v11e('cmdk.empty_sub', 'Patients, pages, actions and to-dos.') ?></p>
            </div>
        </div>

        <div class="cmdk__footer" aria-hidden="true">
            <span class="cmdk__hint"><kbd>&uarr;</kbd><kbd>&darr;</kbd> <?= v11e('cmdk.hint.navigate', 'navigate') ?></span>
            <span class="cmdk__hint"><kbd>&crarr;</kbd> <?= v11e('cmdk.hint.select', 'select') ?></span>
            <span class="cmdk__hint"><kbd>Esc</kbd> <?= v11e('cmdk.hint.close', 'close') ?></span>
            <span class="cmdk__hint cmdk__hint--brand">
                <kbd>&#8984;</kbd><kbd>K</kbd>
            </span>
        </div>
    </div>
</div>
