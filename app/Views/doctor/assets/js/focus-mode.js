/**
 * focus-mode.js
 * Edit Consultation "Focus mode" — hides sidebar/dock/breadcrumb and presents
 * a minimal centered editing surface with a thin top-bar.
 *
 * Public API:
 *   window.toggleFocusMode(force?)
 *     - force === true  → enable
 *     - force === false → disable
 *     - force omitted   → toggle
 *
 * State persisted in sessionStorage as 'focusMode' = '1' | '0'.
 * Auto-mounts on the Edit Consultation page only.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'focusMode';
    var BODY_ATTR = 'data-focus-mode';
    var LEAVING_CLASS = 'focus-mode-leaving';
    var ENTERING_CLASS = 'focus-mode-entering';
    var LEAVE_DURATION_MS = 240;

    // ---------------------------------------------------------------------
    // Page detection — only mount on the Edit Consultation page.
    // ---------------------------------------------------------------------
    function isEditConsultationPage() {
        try {
            var path = (window.location && window.location.pathname) || '';
            if (/\/doctor\/edit[-_]consultation/i.test(path)) return true;
            if (document.body && document.body.classList.contains('edit-consultation-page')) return true;
            // Heuristic fallback — the page exposes the consultation form.
            if (document.getElementById('consultationForm')) return true;
            return false;
        } catch (e) {
            return false;
        }
    }

    // ---------------------------------------------------------------------
    // Top-bar markup.
    // ---------------------------------------------------------------------
    function buildTopBar() {
        if (document.querySelector('.focus-topbar')) {
            return document.querySelector('.focus-topbar');
        }

        var body = document.body;
        var patientName = (body && body.getAttribute('data-patient-name')) || '';
        var appointmentId = (body && body.getAttribute('data-appointment-id')) || '';

        // Fallbacks — read from the breadcrumb if data attributes are absent.
        if (!patientName) {
            var crumbName = document.querySelector('.app-breadcrumb .app-crumb-link');
            if (crumbName) patientName = (crumbName.textContent || '').trim();
        }
        if (!appointmentId) {
            var apptMatch = (window.location.pathname || '').match(/edit[-_]consultation\/(\d+)/i);
            if (apptMatch) appointmentId = apptMatch[1];
        }

        var bar = document.createElement('div');
        bar.className = 'focus-topbar';
        bar.setAttribute('role', 'toolbar');
        bar.setAttribute('aria-label', 'Focus mode toolbar');

        var exitBtn = document.createElement('button');
        exitBtn.type = 'button';
        exitBtn.className = 'focus-topbar__exit';
        exitBtn.setAttribute('aria-label', 'Exit focus mode');
        exitBtn.innerHTML =
            '<i class="bi bi-arrow-left" aria-hidden="true"></i>' +
            '<span class="focus-topbar__exit-label">Exit Focus</span>';
        exitBtn.addEventListener('click', function () {
            apply(false);
        });

        var meta = document.createElement('div');
        meta.className = 'focus-topbar__meta';

        var patientEl = document.createElement('span');
        patientEl.className = 'focus-topbar__patient';
        patientEl.textContent = patientName || 'Consultation';

        meta.appendChild(patientEl);

        if (appointmentId) {
            var sep = document.createElement('span');
            sep.className = 'focus-topbar__sep';
            sep.setAttribute('aria-hidden', 'true');
            sep.textContent = '·';

            var apptEl = document.createElement('span');
            apptEl.className = 'focus-topbar__appt';
            apptEl.textContent = 'Appointment #' + appointmentId;

            meta.appendChild(sep);
            meta.appendChild(apptEl);
        }

        var spacer = document.createElement('div');
        spacer.className = 'focus-topbar__spacer';
        spacer.setAttribute('aria-hidden', 'true');

        var hint = document.createElement('span');
        hint.className = 'focus-topbar__hint';
        hint.setAttribute('aria-hidden', 'true');
        hint.innerHTML = 'Press <kbd>F</kbd> to exit';

        bar.appendChild(exitBtn);
        bar.appendChild(meta);
        bar.appendChild(spacer);
        bar.appendChild(hint);

        document.body.appendChild(bar);
        return bar;
    }

    // ---------------------------------------------------------------------
    // Header toggle button.
    // ---------------------------------------------------------------------
    function findHeaderHost() {
        return (
            document.querySelector('.consultation-header') ||
            document.querySelector('.dashboard-header') ||
            // Edit Consultation uses a plain flex header — use the breadcrumb row.
            document.querySelector('.app-breadcrumb') ||
            null
        );
    }

    function mountToggleButton() {
        if (document.getElementById('focusModeToggle')) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'focusModeToggle';
        btn.className = 'focus-toggle-btn';
        btn.setAttribute('aria-pressed', 'false');
        btn.setAttribute('title', 'Focus mode (F)');
        btn.setAttribute('aria-label', 'Toggle focus mode');
        btn.innerHTML =
            '<i class="bi bi-fullscreen" aria-hidden="true"></i>' +
            '<span class="focus-toggle-btn__label">Focus</span>';
        btn.addEventListener('click', function () {
            apply();
        });

        // Prefer mounting alongside an existing "Back to Appointment" button so
        // the chrome stays on one row; otherwise drop next to the breadcrumb.
        var backBtn = null;
        var actionRow = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
        if (actionRow) {
            backBtn = actionRow.querySelector('a.btn, button.btn');
        }

        if (backBtn && backBtn.parentNode) {
            // Wrap into an inline group if not already grouped.
            var group = backBtn.parentNode;
            if (!group.classList.contains('focus-toggle-group')) {
                // Build a small wrapper that keeps both controls together.
                var wrap = document.createElement('div');
                wrap.className = 'd-inline-flex align-items-center gap-2 focus-toggle-group';
                backBtn.parentNode.insertBefore(wrap, backBtn);
                wrap.appendChild(btn);
                wrap.appendChild(backBtn);
            } else {
                group.insertBefore(btn, backBtn);
            }
            return;
        }

        var host = findHeaderHost();
        if (host && host.parentNode) {
            host.parentNode.insertBefore(btn, host.nextSibling);
        } else {
            document.body.appendChild(btn);
        }
    }

    function syncToggleButton(active) {
        var btn = document.getElementById('focusModeToggle');
        if (!btn) return;
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        btn.classList.toggle('is-active', !!active);
        var icon = btn.querySelector('i');
        if (icon) {
            icon.className = active ? 'bi bi-fullscreen-exit' : 'bi bi-fullscreen';
        }
        var label = btn.querySelector('.focus-toggle-btn__label');
        if (label) label.textContent = active ? 'Exit Focus' : 'Focus';
        btn.setAttribute('title', active ? 'Exit focus mode (F)' : 'Focus mode (F)');
    }

    // ---------------------------------------------------------------------
    // Apply / persist state.
    // ---------------------------------------------------------------------
    var _leaveTimer = null;

    function persist(active) {
        try {
            sessionStorage.setItem(STORAGE_KEY, active ? '1' : '0');
        } catch (e) {
            /* sessionStorage may be unavailable in private mode — ignore. */
        }
    }

    function isActive() {
        return document.body && document.body.hasAttribute(BODY_ATTR);
    }

    function apply(force) {
        var current = isActive();
        var next = (typeof force === 'boolean') ? force : !current;

        if (next === current) return next;

        var body = document.body;
        if (!body) return current;

        // Clear any in-flight leave transition.
        if (_leaveTimer) {
            clearTimeout(_leaveTimer);
            _leaveTimer = null;
            body.classList.remove(LEAVING_CLASS);
        }

        if (next) {
            buildTopBar();
            body.classList.add(ENTERING_CLASS);
            body.setAttribute(BODY_ATTR, '');
            // Drop the entering class after one frame so the transition runs.
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    body.classList.remove(ENTERING_CLASS);
                });
            });
            persist(true);
            syncToggleButton(true);
            dispatch(true);
        } else {
            // Run the leave animation first, then remove the attribute so the
            // hidden chrome (sidebar/dock/etc) doesn't display:none mid-fade.
            body.classList.add(LEAVING_CLASS);
            _leaveTimer = setTimeout(function () {
                body.removeAttribute(BODY_ATTR);
                body.classList.remove(LEAVING_CLASS);
                _leaveTimer = null;
            }, LEAVE_DURATION_MS);
            persist(false);
            syncToggleButton(false);
            dispatch(false);
        }

        return next;
    }

    function dispatch(active) {
        try {
            window.dispatchEvent(new CustomEvent('focusmodechange', {
                detail: { active: !!active }
            }));
        } catch (e) { /* no-op */ }
    }

    // ---------------------------------------------------------------------
    // Keyboard shortcut: F (when no input is focused).
    // ---------------------------------------------------------------------
    function isTypingTarget(el) {
        if (!el) return false;
        var tag = (el.tagName || '').toUpperCase();
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
        if (el.isContentEditable) return true;
        // CodeMirror / TinyMCE / Quill containers commonly mark themselves editable.
        if (el.closest && el.closest('[contenteditable="true"], .tox-edit-area, .ql-editor, .CodeMirror')) {
            return true;
        }
        return false;
    }

    function onKeydown(e) {
        if (!e || e.defaultPrevented) return;
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        var key = (e.key || '').toLowerCase();
        if (key !== 'f') return;
        if (isTypingTarget(document.activeElement) || isTypingTarget(e.target)) return;

        e.preventDefault();
        apply();
    }

    function registerShortcut() {
        // Prefer the central keyboard-help registry if present.
        var help = window.keyboardHelp;
        if (help) {
            try {
                if (typeof help.register === 'function') {
                    help.register({
                        keys: ['F'],
                        description: 'Toggle focus mode',
                        scope: 'Edit Consultation',
                        handler: function () { apply(); }
                    });
                } else if (typeof help.add === 'function') {
                    help.add('F', 'Toggle focus mode', function () { apply(); });
                }
            } catch (e) { /* fall through to native listener */ }
        }

        // Always attach our own listener as a guaranteed fallback.
        document.addEventListener('keydown', onKeydown, false);
    }

    // ---------------------------------------------------------------------
    // Bootstrap.
    // ---------------------------------------------------------------------
    function restore() {
        var saved = null;
        try { saved = sessionStorage.getItem(STORAGE_KEY); } catch (e) { saved = null; }
        if (saved === '1') {
            // Skip leave animation on initial paint — apply immediately.
            buildTopBar();
            document.body.setAttribute(BODY_ATTR, '');
            syncToggleButton(true);
        } else {
            syncToggleButton(false);
        }
    }

    function init() {
        if (!isEditConsultationPage()) return;
        if (!document.body) return;

        mountToggleButton();
        restore();
        registerShortcut();

        // Re-mount the toggle button if the header gets re-rendered later.
        if ('MutationObserver' in window) {
            var mo = new MutationObserver(function () {
                if (!document.getElementById('focusModeToggle')) {
                    mountToggleButton();
                    syncToggleButton(isActive());
                }
            });
            try {
                mo.observe(document.body, { childList: true, subtree: false });
            } catch (e) { /* ignore */ }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

    // ---------------------------------------------------------------------
    // Public API.
    // ---------------------------------------------------------------------
    window.toggleFocusMode = function (force) {
        return apply(typeof force === 'boolean' ? force : undefined);
    };

    window.focusMode = {
        toggle: function (force) { return apply(typeof force === 'boolean' ? force : undefined); },
        enable: function () { return apply(true); },
        disable: function () { return apply(false); },
        isActive: isActive
    };
})();
