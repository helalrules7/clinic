<?php
require_once __DIR__ . '/v11-i18n.php';
$v11Layout = $v11Layout ?? 'doctor';
$isSecLayout = ($v11Layout === 'secretary');
/**
 * Keyboard Shortcuts Help Overlay
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
          <h2 class="kbd-help__title" id="kbdHelpTitle"><?= v11e('kbd.title', 'Keyboard Shortcuts') ?></h2>
          <p class="kbd-help__subtitle" id="kbdHelpSubtitle">
            <?= v11_lang() === 'ar'
                ? 'اضغط <kbd class="kbd">?</kbd> في أي وقت لإعادة فتح هذه اللوحة.'
                : 'Press <kbd class="kbd">?</kbd> any time to reopen this panel.' ?>
          </p>
        </div>
      </div>
      <button
        type="button"
        class="kbd-help__close"
        data-kbd-help-close="1"
        aria-label="<?= v11e('kbd.close', 'Close keyboard shortcuts') ?>"
      >
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>
    </header>

    <div class="kbd-help__body">
      <section class="kbd-help__section" aria-labelledby="kbdHelpGlobal">
        <h3 class="kbd-help__section-title" id="kbdHelpGlobal"><?= v11e('kbd.section.global', 'Global') ?></h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">⌘K</kbd>
              <span class="kbd-help__sep" aria-hidden="true">/</span>
              <kbd class="kbd">Ctrl</kbd><span class="kbd-help__plus" aria-hidden="true">+</span><kbd class="kbd">K</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.open_palette', 'Open command palette') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">?</kbd></span>
            <span class="kbd-help__label"><?= v11e('kbd.show_help', 'Show this help') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">Esc</kbd></span>
            <span class="kbd-help__label"><?= v11e('kbd.esc_close', 'Close modal, drawer, or palette') ?></span>
          </li>
        </ul>
      </section>

      <section class="kbd-help__section" aria-labelledby="kbdHelpNav">
        <h3 class="kbd-help__section-title" id="kbdHelpNav"><?= v11e('kbd.section.nav', 'Navigation') ?></h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">D</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_dashboard', 'Go to Dashboard') ?></span>
          </li>
          <?php if ($isSecLayout): ?>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">B</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_bookings', 'Go to Bookings') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">M</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_payments', 'Go to Payments') ?></span>
          </li>
          <?php else: ?>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">C</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_calendar', 'Go to Calendar') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">B</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_boards', 'Go to Boards') ?></span>
          </li>
          <?php endif; ?>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">P</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_patients', 'Go to Patients') ?></span>
          </li>
          <?php if ($isSecLayout): ?>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">R</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_profile', 'Go to Profile') ?></span>
          </li>
          <?php else: ?>
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">G</kbd>
              <span class="kbd-help__then" aria-hidden="true"><?= v11e('kbd.then', 'then') ?></span>
              <kbd class="kbd">S</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.go_settings', 'Go to Settings') ?></span>
          </li>
          <?php endif; ?>
        </ul>
      </section>

      <section class="kbd-help__section" aria-labelledby="kbdHelpActions">
        <h3 class="kbd-help__section-title" id="kbdHelpActions"><?= v11e('kbd.section.actions', 'Actions') ?></h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">N</kbd></span>
            <span class="kbd-help__label"><?= v11e('kbd.new_patient', 'New patient') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">T</kbd></span>
            <span class="kbd-help__label"><?= v11e('kbd.open_todo', 'Open To-Do drawer') ?></span>
          </li>
          <?php if (!$isSecLayout): ?>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">A</kbd></span>
            <span class="kbd-help__label"><?= v11e('kbd.new_alert', 'New alert') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">F</kbd></span>
            <span class="kbd-help__label">
              <?= v11e('kbd.focus_mode', 'Toggle Focus mode') ?>
              <span class="kbd-help__hint"><?= v11e('kbd.focus_hint', '(Edit Consultation page)') ?></span>
            </span>
          </li>
          <?php endif; ?>
        </ul>
      </section>

      <section class="kbd-help__section" aria-labelledby="kbdHelpNotif">
        <h3 class="kbd-help__section-title" id="kbdHelpNotif"><?= v11e('kbd.section.notif', 'Notifications') ?></h3>
        <ul class="kbd-help__list" role="list">
          <li class="kbd-help__row">
            <span class="kbd-help__keys">
              <kbd class="kbd">J</kbd>
              <span class="kbd-help__sep" aria-hidden="true">/</span>
              <kbd class="kbd">K</kbd>
            </span>
            <span class="kbd-help__label"><?= v11e('kbd.notif_nav', 'Next / previous notification') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">S</kbd></span>
            <span class="kbd-help__label"><?= v11e('kbd.notif_snooze', 'Snooze focused notification') ?></span>
          </li>
          <li class="kbd-help__row">
            <span class="kbd-help__keys"><kbd class="kbd">P</kbd></span>
            <span class="kbd-help__label"><?= v11e('kbd.notif_pin', 'Pin focused notification') ?></span>
          </li>
        </ul>
        <p class="kbd-help__note">
          <i class="bi bi-info-circle" aria-hidden="true"></i>
          <?= v11e('kbd.notif_note', 'These keys only fire while the notification panel is open.') ?>
        </p>
      </section>
    </div>

    <footer class="kbd-help__footer">
      <span class="kbd-help__footer-hint">
        <?= v11e('kbd.footer_tip', 'Tip: shortcuts are ignored while typing in inputs.') ?>
      </span>
      <button
        type="button"
        class="kbd-help__btn"
        data-kbd-help-close="1"
      >
        <?= v11e('kbd.close_btn', 'Close') ?>
      </button>
    </footer>
  </div>
</div>
