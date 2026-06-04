<?php
/**
 * Command Palette (Cmd+K / Ctrl+K)
 * Mounted at body level. Hidden until JS opens it.
 */
?>
<div class="cmdk" id="cmdk" role="dialog" aria-modal="true" aria-labelledby="cmdkTitle" hidden>
    <div class="cmdk__backdrop" id="cmdkBackdrop" aria-hidden="true"></div>

    <div class="cmdk__panel" id="cmdkPanel" role="document">
        <h2 id="cmdkTitle" class="visually-hidden">Command palette</h2>

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
                placeholder="Search patients, pages, actions, to-dos…"
                autocomplete="off"
                autocapitalize="off"
                spellcheck="false"
                aria-label="Search"
                aria-controls="cmdkResults"
                aria-autocomplete="list"
            />
            <button type="button" class="cmdk__close" id="cmdkClose" aria-label="Close command palette">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <kbd class="cmdk__close-kbd" aria-hidden="true">Esc</kbd>
            </button>
        </div>

        <div class="cmdk__tabs" role="tablist" id="cmdkTabs" aria-label="Filter results">
            <button type="button" class="cmdk__tab is-active" role="tab" data-scope="all" aria-selected="true">All</button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="patients" aria-selected="false">Patients</button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="pages" aria-selected="false">Pages</button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="actions" aria-selected="false">Actions</button>
            <button type="button" class="cmdk__tab" role="tab" data-scope="todos" aria-selected="false">To-dos</button>
        </div>

        <div
            class="cmdk__results"
            id="cmdkResults"
            role="listbox"
            aria-label="Search results"
            tabindex="-1"
        >
            <div class="cmdk__empty" id="cmdkEmpty">
                <div class="cmdk__empty-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <p class="cmdk__empty-title">Start typing to search</p>
                <p class="cmdk__empty-sub">Patients, pages, actions and to-dos.</p>
            </div>
        </div>

        <div class="cmdk__footer" aria-hidden="true">
            <span class="cmdk__hint"><kbd>&uarr;</kbd><kbd>&darr;</kbd> navigate</span>
            <span class="cmdk__hint"><kbd>&crarr;</kbd> select</span>
            <span class="cmdk__hint"><kbd>Esc</kbd> close</span>
            <span class="cmdk__hint cmdk__hint--brand">
                <kbd>&#8984;</kbd><kbd>K</kbd>
            </span>
        </div>
    </div>
</div>
