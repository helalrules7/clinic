/**
 * Keyboard Shortcuts Help Overlay
 *
 * Owns:
 *   - The "?" overlay (open/close, focus trap, Esc handling)
 *   - The global chord state machine (G then D/C/B/P/S)
 *   - Single-key action shortcuts (N / T / A / F)
 *   - Notification panel shortcuts (J / K / S / P) — only when notifCenter is open
 *
 * Public hooks on window:
 *   - window.openKeyboardHelp()
 *   - window.closeKeyboardHelp()
 *   - window.keyboardHelp.isOpen()
 */
(function () {
  'use strict';

  // ---------------------------------------------------------------------------
  // Config
  // ---------------------------------------------------------------------------

  var CHORD_TIMEOUT_MS = 1500;

  // Routes for the G-prefixed navigation chord.
  // Built from window location so deploys under a sub-path still work.
  function basePath() {
    var path = window.location.pathname || '/';
    // Strip trailing filename if any; keep up to the last "/"
    var i = path.lastIndexOf('/');
    return i >= 0 ? path.substring(0, i + 1) : '/';
  }

  function navTarget(slug) {
    // Doctor app routes are flat (?page=foo or /foo). We default to ?page=<slug>.
    // If the app uses a different scheme, callers can override window.kbdHelpRoutes.
    var routes = window.kbdHelpRoutes || {};
    if (routes[slug]) return routes[slug];
    return '?page=' + encodeURIComponent(slug);
  }

  var NAV_CHORDS = {
    d: 'dashboard',
    c: 'calendar',
    b: 'board',
    p: 'patients',
    s: 'settings'
  };

  // ---------------------------------------------------------------------------
  // State
  // ---------------------------------------------------------------------------

  var pendingChord = null;
  var chordTimer = null;
  var lastFocusedBeforeOpen = null;
  var isOpen = false;

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function getRoot() {
    return document.getElementById('kbdHelp');
  }

  /**
   * True when keyboard focus is inside a text input or content-editable
   * region — we never steal those keystrokes.
   */
  function isTypingTarget(el) {
    if (!el) return false;
    if (el.isContentEditable) return true;
    var tag = (el.tagName || '').toUpperCase();
    if (tag === 'INPUT') {
      var type = (el.getAttribute('type') || 'text').toLowerCase();
      // Treat checkbox/radio/button as non-typing — they don't capture text
      if (type === 'checkbox' || type === 'radio' || type === 'button' ||
          type === 'submit' || type === 'reset' || type === 'file' ||
          type === 'range' || type === 'color') {
        return false;
      }
      return true;
    }
    if (tag === 'TEXTAREA' || tag === 'SELECT') return true;
    return false;
  }

  function hasModifier(ev) {
    return ev.ctrlKey || ev.metaKey || ev.altKey;
  }

  function clearChord() {
    pendingChord = null;
    if (chordTimer) {
      clearTimeout(chordTimer);
      chordTimer = null;
    }
    document.documentElement.removeAttribute('data-kbd-chord');
  }

  function armChord(prefix) {
    pendingChord = prefix;
    document.documentElement.setAttribute('data-kbd-chord', prefix);
    if (chordTimer) clearTimeout(chordTimer);
    chordTimer = setTimeout(clearChord, CHORD_TIMEOUT_MS);
  }

  // ---------------------------------------------------------------------------
  // Open / close
  // ---------------------------------------------------------------------------

  function openHelp() {
    var root = getRoot();
    if (!root) return;
    if (isOpen) return;

    lastFocusedBeforeOpen = document.activeElement;
    root.hidden = false;
    // Allow the browser to flush layout before applying the open class
    // so the transition actually plays.
    requestAnimationFrame(function () {
      root.classList.add('is-open');
    });
    document.body.classList.add('kbd-help-open');
    isOpen = true;

    var panel = $('.kbd-help__panel', root);
    if (panel && typeof panel.focus === 'function') {
      panel.focus({ preventScroll: true });
    }
  }

  function closeHelp() {
    var root = getRoot();
    if (!root) return;
    if (!isOpen) return;

    root.classList.remove('is-open');
    document.body.classList.remove('kbd-help-open');
    isOpen = false;

    // Wait for the transition (kept short — 180ms) before hiding.
    setTimeout(function () {
      if (!isOpen) root.hidden = true;
    }, 200);

    if (lastFocusedBeforeOpen &&
        typeof lastFocusedBeforeOpen.focus === 'function' &&
        document.contains(lastFocusedBeforeOpen)) {
      try { lastFocusedBeforeOpen.focus({ preventScroll: true }); } catch (e) { /* noop */ }
    }
    lastFocusedBeforeOpen = null;
  }

  // ---------------------------------------------------------------------------
  // Action dispatchers
  //
  // Each one is wrapped so the absence of a target widget never throws — the
  // shortcut simply becomes a no-op until that widget is loaded on the page.
  // ---------------------------------------------------------------------------

  function safeCall(fn) {
    try {
      if (typeof fn === 'function') {
        fn();
        return true;
      }
    } catch (e) {
      if (window.console && console.warn) console.warn('[kbd-help] handler failed', e);
    }
    return false;
  }

  function openCommandPalette() {
    if (window.commandPalette && typeof window.commandPalette.open === 'function') {
      return safeCall(window.commandPalette.open);
    }
    if (typeof window.openCommandPalette === 'function') {
      return safeCall(window.openCommandPalette);
    }
    return false;
  }

  function closeTopmostOverlay() {
    // Help itself takes priority
    if (isOpen) { closeHelp(); return true; }

    // Modal kit
    if (window.mkConfirmModal && typeof window.mkConfirmModal.close === 'function') {
      if (window.mkConfirmModal.isOpen && window.mkConfirmModal.isOpen()) {
        window.mkConfirmModal.close();
        return true;
      }
    }

    // Command palette
    if (window.commandPalette && typeof window.commandPalette.close === 'function') {
      if (window.commandPalette.isOpen && window.commandPalette.isOpen()) {
        window.commandPalette.close();
        return true;
      }
    }

    // Todo drawer
    if (window.todoDrawer && typeof window.todoDrawer.close === 'function') {
      if (window.todoDrawer.isOpen && window.todoDrawer.isOpen()) {
        window.todoDrawer.close();
        return true;
      }
    }

    // Notification center
    if (window.notifCenter && typeof window.notifCenter.close === 'function') {
      if (window.notifCenter.isOpen && window.notifCenter.isOpen()) {
        window.notifCenter.close();
        return true;
      }
    }

    return false;
  }

  function newPatient() {
    if (window.patients && typeof window.patients.openNew === 'function') {
      return safeCall(window.patients.openNew);
    }
    if (typeof window.openNewPatient === 'function') {
      return safeCall(window.openNewPatient);
    }
    var btn = document.querySelector('[data-action="new-patient"]') ||
              document.getElementById('btnNewPatient');
    if (btn) { btn.click(); return true; }
    return false;
  }

  function openTodoDrawer() {
    if (window.todoDrawer && typeof window.todoDrawer.open === 'function') {
      return safeCall(window.todoDrawer.open);
    }
    if (typeof window.openTodoDrawer === 'function') {
      return safeCall(window.openTodoDrawer);
    }
    var btn = document.querySelector('[data-action="open-todo"]') ||
              document.getElementById('btnTodoDrawer');
    if (btn) { btn.click(); return true; }
    return false;
  }

  function newAlert() {
    if (window.alerts && typeof window.alerts.openNew === 'function') {
      return safeCall(window.alerts.openNew);
    }
    if (typeof window.openNewAlert === 'function') {
      return safeCall(window.openNewAlert);
    }
    var btn = document.querySelector('[data-action="new-alert"]') ||
              document.getElementById('btnNewAlert');
    if (btn) { btn.click(); return true; }
    return false;
  }

  function toggleFocusMode() {
    // Only meaningful on Edit Consultation; bail silently elsewhere.
    if (window.editConsultation && typeof window.editConsultation.toggleFocus === 'function') {
      return safeCall(window.editConsultation.toggleFocus);
    }
    if (typeof window.toggleFocusMode === 'function') {
      return safeCall(window.toggleFocusMode);
    }
    var btn = document.querySelector('[data-action="toggle-focus"]');
    if (btn) { btn.click(); return true; }
    return false;
  }

  function navigate(slug) {
    if (window.router && typeof window.router.go === 'function') {
      try { window.router.go(slug); return true; } catch (e) { /* fall through */ }
    }
    window.location.href = navTarget(slug);
    return true;
  }

  // ---------------------------------------------------------------------------
  // Notification panel shortcuts
  // ---------------------------------------------------------------------------

  function notifIsOpen() {
    return !!(window.notifCenter &&
              typeof window.notifCenter.isOpen === 'function' &&
              window.notifCenter.isOpen());
  }

  function notifNext()    { return window.notifCenter && safeCall(window.notifCenter.focusNext); }
  function notifPrev()    { return window.notifCenter && safeCall(window.notifCenter.focusPrev); }
  function notifSnooze()  { return window.notifCenter && safeCall(window.notifCenter.snoozeFocused); }
  function notifPin()     { return window.notifCenter && safeCall(window.notifCenter.pinFocused); }

  // ---------------------------------------------------------------------------
  // Main keydown handler
  // ---------------------------------------------------------------------------

  function handleKeydown(ev) {
    if (ev.defaultPrevented) return;

    var key = ev.key;
    var lower = (typeof key === 'string') ? key.toLowerCase() : '';

    // Esc — handled even from inputs (close overlays)
    if (key === 'Escape' || key === 'Esc') {
      if (closeTopmostOverlay()) {
        ev.preventDefault();
        ev.stopPropagation();
      }
      clearChord();
      return;
    }

    // Cmd/Ctrl+K — command palette (works even inside inputs by design)
    if ((ev.metaKey || ev.ctrlKey) && !ev.shiftKey && !ev.altKey && lower === 'k') {
      if (openCommandPalette()) {
        ev.preventDefault();
      }
      return;
    }

    // From here on, never steal keystrokes from typing surfaces.
    if (isTypingTarget(ev.target)) {
      clearChord();
      return;
    }

    // "?" — Show help. On most layouts this is Shift+/ producing key="?".
    if (key === '?' && !ev.ctrlKey && !ev.metaKey && !ev.altKey) {
      ev.preventDefault();
      if (isOpen) { closeHelp(); } else { openHelp(); }
      clearChord();
      return;
    }

    // While help is open, swallow other shortcuts.
    if (isOpen) return;

    // Any plain modifier-key event from here on we ignore.
    if (hasModifier(ev)) {
      // A bare modifier (e.g. just "Shift") shouldn't break a pending chord
      if (key !== 'Shift' && key !== 'Control' && key !== 'Meta' && key !== 'Alt') {
        clearChord();
      }
      return;
    }

    // Pending G-chord resolution
    if (pendingChord === 'g') {
      if (Object.prototype.hasOwnProperty.call(NAV_CHORDS, lower)) {
        ev.preventDefault();
        navigate(NAV_CHORDS[lower]);
        clearChord();
        return;
      }
      // Any other key cancels the chord (and is then handled normally below)
      clearChord();
    }

    // Notification-panel-only chord keys
    if (notifIsOpen()) {
      if (lower === 'j') { ev.preventDefault(); notifNext(); return; }
      if (lower === 'k') { ev.preventDefault(); notifPrev(); return; }
      if (lower === 's') { ev.preventDefault(); notifSnooze(); return; }
      if (lower === 'p') { ev.preventDefault(); notifPin(); return; }
      // Fall through for other keys (e.g. "g" to start a nav chord).
    }

    // Single-key actions
    switch (lower) {
      case 'g':
        ev.preventDefault();
        armChord('g');
        return;
      case 'n':
        if (newPatient()) ev.preventDefault();
        return;
      case 't':
        if (openTodoDrawer()) ev.preventDefault();
        return;
      case 'a':
        if (newAlert()) ev.preventDefault();
        return;
      case 'f':
        if (toggleFocusMode()) ev.preventDefault();
        return;
      default:
        // Nothing — let the keystroke pass through.
        return;
    }
  }

  // ---------------------------------------------------------------------------
  // Wire up the overlay's own click handlers
  // ---------------------------------------------------------------------------

  function bindOverlay() {
    var root = getRoot();
    if (!root) return;

    root.addEventListener('click', function (ev) {
      var t = ev.target;
      if (!t) return;
      // Backdrop or any [data-kbd-help-close] element closes the panel.
      if (t.closest && t.closest('[data-kbd-help-close="1"]')) {
        ev.preventDefault();
        closeHelp();
      }
    });
  }

  // Mount a header trigger button next to the theme/palette controls.
  // Hidden on mobile via CSS (see keyboard-help.css → @media max-width: 767.98px).
  function ensureHeaderButton() {
    if (document.getElementById('kbdHelpToggle')) return;
    // Always mount next to the dark/light switch — palette lives in #topActionsQuick.
    var anchor = document.querySelector('label.switch[for="themeToggleInput"]');
    if (!anchor || !anchor.parentNode) return;

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'kbdHelpToggle';
    btn.className = 'kbd-help-toggle';
    btn.setAttribute('aria-label', 'Keyboard shortcuts');
    btn.setAttribute('title', 'Keyboard shortcuts (?)');
    btn.innerHTML = '<i class="bi bi-question-lg" aria-hidden="true"></i>';
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      openHelp();
    });
    anchor.parentNode.insertBefore(btn, anchor);
  }

  function init() {
    bindOverlay();
    ensureHeaderButton();
    document.addEventListener('keydown', handleKeydown, true);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }

  // ---------------------------------------------------------------------------
  // Public API
  // ---------------------------------------------------------------------------

  window.openKeyboardHelp = openHelp;
  window.closeKeyboardHelp = closeHelp;
  window.keyboardHelp = {
    open: openHelp,
    close: closeHelp,
    isOpen: function () { return isOpen; }
  };

  // Suppress unused warning for basePath (kept for future deploy-path overrides)
  void basePath;
})();
