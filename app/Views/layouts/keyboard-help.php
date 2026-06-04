<?php
/**
 * Keyboard Shortcuts Help Overlay
 *
 * Body-level overlay surfaced via:
 *   - the global "?" key (handled in keyboard-help.js)
 *   - window.openKeyboardHelp() (public hook)
 *
 * The view ships hidden; JS toggles the `hidden` attribute and the
 * `.is-open` class to drive the fade/scale transition.
 */
?>
<div
  class="kbd-help"
  id="kbdHelp"
  role="dialog"
  aria-modal="true"
  aria-labelledby="kbdHelpTitle"
  aria-describedby="kbdHelpSubtitle"
  hidden
>
  <div class="kbd-help__backdrop" data-kbd-help-close="1" aria-hidden="true"></div>

  <div class="kbd-help__panel" role="document" tabindex="-1">
    <header class="kbd-help__header">
      <div class="kbd-help__heading">
        <span class="kbd-help__icon" aria-hidden="true">
          <i class="bi bi-keyboard"></i>
        </span>
        <div>
          <h2 class="kbd-help__title" id="kbdHelpTitle">Keyboard Shortcuts</h2>
          <p class="kbd-help__subtitle" id="kbdHelpSubtitle">
            Press <kbd class="kbd">?</kbd> any time to reopen this panel.
          </p>
        </div>
      </div>
      <button
        type="button"
        class="kbd-help__close"
        data-kbd-help-close="1"
        aria-label="Close keyboard shortcuts"
      >
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>
    </header>

    <div class="kbd-help__body">
      <section class="kbd-help__section" aria-labelledby="kbdHelpGlobal">
        <h3 class="kbd-help__section-title" id="kbdHelpGlobal">Global</h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">⌘K</kbd>
              <span class="kbd-help__sep" aria-hidden="true">/</span>
              <kbd class="kbd">Ctrl</kbd><span class="kbd-help__plus" aria-hidden="true">+</span><kbd class="kbd">K</kbd>
            </span>
            <span class="kbd-help__label">Open command palette</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">?</kbd></span>
            <span class="kbd-help__label">Show this help</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">Esc</kbd></span>
            <span class="kbd-help__label">Close modal, drawer, or palette</span>
          </li>
        </ul>
      </section>

      <section class="kbd-help__section" aria-labelledby="kbdHelpNav">
        <h3 class="kbd-help__section-title" id="kbdHelpNav">Navigation</h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true">then</span>
              <kbd class="kbd">D</kbd>
            </span>
            <span class="kbd-help__label">Go to Dashboard</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true">then</span>
              <kbd class="kbd">C</kbd>
            </span>
            <span class="kbd-help__label">Go to Calendar</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true">then</span>
              <kbd class="kbd">B</kbd>
            </span>
            <span class="kbd-help__label">Go to Boards</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true">then</span>
              <kbd class="kbd">P</kbd>
            </span>
            <span class="kbd-help__label">Go to Patients</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true">then</span>
              <kbd class="kbd">S</kbd>
            </span>
            <span class="kbd-help__label">Go to Settings</span>
          </li>
        </ul>
      </section>

      <section class="kbd-help__section" aria-labelledby="kbdHelpActions">
        <h3 class="kbd-help__section-title" id="kbdHelpActions">Actions</h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">N</kbd></span>
            <span class="kbd-help__label">New patient</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">T</kbd></span>
            <span class="kbd-help__label">Open To-Do drawer</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">A</kbd></span>
            <span class="kbd-help__label">New alert</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">F</kbd></span>
            <span class="kbd-help__label">
              Toggle Focus mode
              <span class="kbd-help__hint">(Edit Consultation page)</span>
            </span>
          </li>
        </ul>
      </section>

      <section class="kbd-help__section" aria-labelledby="kbdHelpNotif">
        <h3 class="kbd-help__section-title" id="kbdHelpNotif">Notifications</h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">J</kbd>
              <span class="kbd-help__sep" aria-hidden="true">/</span>
              <kbd class="kbd">K</kbd>
            </span>
            <span class="kbd-help__label">Next / previous notification</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">S</kbd></span>
            <span class="kbd-help__label">Snooze focused notification</span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">P</kbd></span>
            <span class="kbd-help__label">Pin focused notification</span>
          </li>
        </ul>
        <p class="kbd-help__note">
          <i class="bi bi-info-circle" aria-hidden="true"></i>
          These keys only fire while the notification panel is open.
        </p>
      </section>
    </div>

    <footer class="kbd-help__footer">
      <span class="kbd-help__footer-hint">
        Tip: shortcuts are ignored while typing in inputs.
      </span>
      <button
        type="button"
        class="kbd-help__btn"
        data-kbd-help-close="1"
      >
        Close
      </button>
    </footer>
  </div>
</div>
