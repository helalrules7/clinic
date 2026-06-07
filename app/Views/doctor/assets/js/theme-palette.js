/* =====================================================================
   v11.0.0 — Theme palette picker + auto-schedule applier.

   What this file owns
   -------------------
   1. The palette state (6 named accents: indigo, emerald, rose, slate, amber, ocean).
   2. The header palette-dot button + popover swatch picker.
   3. The auto dark/light schedule applier (runs every 60s + on visibility change).
   4. Settings page integration: read /api/settings/appearance on load to sync DB → localStorage.

   Pre-paint script (in main.php / secretary_main.php) already reads
   localStorage('appPalette') + 'appThemeAutoSchedule' + 'appThemeDarkFrom'
   + 'appThemeLightFrom' BEFORE first paint and sets html[data-palette] +
   html.dark accordingly. This file handles runtime changes after the page
   is alive.

   Public API
   ----------
   window.setThemePalette(name)            — persist + apply (POST to API)
   window.applyThemeSchedule()             — re-evaluate dark/light by clock
   window.openThemePicker(anchorEl?)       — opens the header popover
   window.closeThemePicker()
   ===================================================================== */
(function (global) {
    'use strict';

    var PALETTES = [
        { id: 'indigo',  label: 'Indigo',  swatch: 'linear-gradient(135deg, #6366f1, #8b5cf6)' },
        { id: 'emerald', label: 'Emerald', swatch: 'linear-gradient(135deg, #10b981, #14b8a6)' },
        { id: 'rose',    label: 'Rose',    swatch: 'linear-gradient(135deg, #f43f5e, #ec4899)' },
        { id: 'slate',   label: 'Slate',   swatch: 'linear-gradient(135deg, #64748b, #334155)' },
        { id: 'amber',   label: 'Amber',   swatch: 'linear-gradient(135deg, #f59e0b, #ef4444)' },
        { id: 'ocean',   label: 'Ocean',   swatch: 'linear-gradient(135deg, #06b6d4, #3b82f6)' },
    ];

    var LS_PALETTE   = 'appPalette';
    var LS_AUTO      = 'appThemeAutoSchedule';
    var LS_DARK_FROM = 'appThemeDarkFrom';
    var LS_LIGHT_FROM= 'appThemeLightFrom';

    /* ---------- helpers ---------- */
    function safeGet(key, fallback) {
        try { var v = localStorage.getItem(key); return v == null ? fallback : v; } catch (_) { return fallback; }
    }
    function safeSet(key, value) {
        try { localStorage.setItem(key, value); } catch (_) {}
    }
    function paletteValid(p) {
        for (var i = 0; i < PALETTES.length; i++) if (PALETTES[i].id === p) return true;
        return false;
    }
    function parseTime(s) {
        var p = String(s || '').split(':');
        var h = parseInt(p[0], 10); var m = parseInt(p[1] || '0', 10);
        if (!Number.isFinite(h) || !Number.isFinite(m)) return 0;
        return Math.max(0, Math.min(23, h)) * 60 + Math.max(0, Math.min(59, m));
    }
    function postJSON(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(body || {}),
        }).then(function (r) { return r.ok ? r.json() : null; }).catch(function () { return null; });
    }

    /* ---------- palette ---------- */
    function applyPalette(name, opts) {
        opts = opts || {};
        if (!paletteValid(name)) name = 'indigo';
        var html = document.documentElement;
        if (html.getAttribute('data-palette') === name && !opts.force) return;

        // Smooth transitions while the color tokens swap.
        html.classList.add('palette-transitioning');
        html.setAttribute('data-palette', name);
        setTimeout(function () { html.classList.remove('palette-transitioning'); }, 320);

        safeSet(LS_PALETTE, name);

        // Sync visible header dot if mounted.
        var dot = document.querySelector('#paletteToggle .palette-dot');
        if (dot) {
            var def = PALETTES.find(function (p) { return p.id === name; });
            if (def) dot.style.background = def.swatch;
        }
        // Reflect active state in the popover swatches (if open).
        document.querySelectorAll('.palette-swatch').forEach(function (sw) {
            sw.toggleAttribute('data-active', sw.getAttribute('data-palette') === name);
        });
    }

    function setThemePalette(name) {
        applyPalette(name, { force: true });
        postJSON('/api/settings/theme-palette', { palette: name });
    }

    /* ---------- auto schedule ---------- */
    // Keep the header dark/light toggle + clinic logo in sync with the class
    // that the schedule (or any other code) sets on <html>. main.js owns the
    // canonical updater (window.syncThemeUI); fall back to a local version so
    // the auto-switch still updates the button + logo even if main.js changes.
    function syncToggleUI(isDark) {
        if (typeof global.syncThemeUI === 'function') { global.syncThemeUI(); return; }
        var input = document.getElementById('themeToggleInput');
        if (input) input.checked = !!isDark;
        var secInput = document.getElementById('secCurrentModeInput');
        if (secInput) secInput.checked = !!isDark;
        var logo = document.getElementById('clinicLogo');
        if (logo) logo.src = isDark ? '/assets/images/Dark.png' : '/assets/images/Light.png';
    }

    /** Manual dark/light picks turn off auto-schedule so the timer stops fighting. */
    function disableThemeAutoSchedule(persist) {
        safeSet(LS_AUTO, '0');
        ['secThemeAutoSchedule', 'themeAutoSchedule'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.checked = false;
        });
        ['secThemeScheduleTimes', 'themeScheduleTimes'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.hidden = true;
        });
        if (!persist) return Promise.resolve(null);
        return postJSON('/api/settings/theme-auto-schedule', {
            enabled: false,
            dark_from:  safeGet(LS_DARK_FROM,  '19:00'),
            light_from: safeGet(LS_LIGHT_FROM, '07:00'),
        });
    }

    function applyThemeSchedule() {
        var enabled = safeGet(LS_AUTO, '0') === '1';
        if (!enabled) return;
        var darkFrom  = safeGet(LS_DARK_FROM,  '19:00');
        var lightFrom = safeGet(LS_LIGHT_FROM, '07:00');
        var d = new Date();
        var mins = d.getHours() * 60 + d.getMinutes();
        var darkStart  = parseTime(darkFrom);
        var lightStart = parseTime(lightFrom);
        var isDark = (mins >= darkStart) || (mins < lightStart);
        document.documentElement.classList.toggle('dark', isDark);
        // Persist so reloads + other tabs agree, and reflect on the toggle/logo.
        safeSet('appTheme', isDark ? 'dark' : 'light');
        safeSet('theme',    isDark ? 'dark' : 'light');
        syncToggleUI(isDark);
    }

    /* ---------- header popover ---------- */
    var popoverEl = null;
    function buildPopover() {
        if (popoverEl) return popoverEl;
        var pop = document.createElement('div');
        pop.className = 'palette-popover';
        pop.id = 'palettePopover';
        pop.setAttribute('role', 'menu');
        pop.setAttribute('aria-label', 'Choose color palette');
        var grid = document.createElement('div');
        grid.className = 'palette-popover__grid';
        PALETTES.forEach(function (p) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'palette-swatch';
            btn.setAttribute('data-palette', p.id);
            btn.setAttribute('role', 'menuitemradio');
            btn.setAttribute('aria-label', 'Use ' + p.label + ' palette');
            btn.innerHTML =
                '<span class="palette-swatch__dot" style="background: ' + p.swatch + ';"></span>' +
                '<span class="palette-swatch__label">' + p.label + '</span>';
            btn.addEventListener('click', function () {
                setThemePalette(p.id);
                closeThemePicker();
            });
            grid.appendChild(btn);
        });
        pop.appendChild(grid);
        document.body.appendChild(pop);
        popoverEl = pop;
        return pop;
    }

    function positionPopover(anchor) {
        if (!popoverEl || !anchor) return;
        var r = anchor.getBoundingClientRect();
        var w = popoverEl.offsetWidth || 260;
        var top  = r.bottom + 8 + window.scrollY;
        var left = r.right - w + window.scrollX;
        if (left < 8) left = 8;
        popoverEl.style.top = top + 'px';
        popoverEl.style.left = left + 'px';
    }

    function openThemePicker(anchor) {
        var pop = buildPopover();
        anchor = anchor || document.getElementById('paletteToggle');
        if (!anchor) return;
        pop.classList.add('is-open');
        pop.setAttribute('aria-hidden', 'false');
        positionPopover(anchor);
        // Mark current palette active.
        applyPalette(safeGet(LS_PALETTE, 'indigo'), { force: true });
        // Close on outside click.
        setTimeout(function () {
            document.addEventListener('click', outsideClickHandler, true);
            document.addEventListener('keydown', escHandler, true);
            window.addEventListener('resize', resizeHandler);
        }, 0);
    }
    function closeThemePicker() {
        if (!popoverEl) return;
        popoverEl.classList.remove('is-open');
        popoverEl.setAttribute('aria-hidden', 'true');
        document.removeEventListener('click', outsideClickHandler, true);
        document.removeEventListener('keydown', escHandler, true);
        window.removeEventListener('resize', resizeHandler);
    }
    function outsideClickHandler(e) {
        if (!popoverEl) return;
        var toggle = document.getElementById('paletteToggle');
        if (popoverEl.contains(e.target)) return;
        if (toggle && toggle.contains(e.target)) return;
        closeThemePicker();
    }
    function escHandler(e) { if (e.key === 'Escape') closeThemePicker(); }
    function resizeHandler() { positionPopover(document.getElementById('paletteToggle')); }

    function ensureHeaderToggle() {
        if (document.getElementById('paletteToggle')) return;
        // Mount into #topActionsQuick (row 2 on mobile: notes · to-do · ⌘K · palette).
        // Falls back to before the theme switch on layouts without the quick row.
        var mount = document.getElementById('topActionsQuick');
        var themeLabel = document.querySelector('label.switch[for="themeToggleInput"]');
        if (!mount && (!themeLabel || !themeLabel.parentNode)) return;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'paletteToggle';
        btn.className = 'palette-toggle';
        var t = function (k, fb) { return (window.V11I18n && window.V11I18n.t(k, fb)) || fb; };
        btn.setAttribute('aria-label', t('palette.toggle', 'Theme palette'));
        btn.setAttribute('title', t('palette.toggle', 'Theme palette'));
        btn.innerHTML = '<span class="palette-dot" aria-hidden="true"></span>';
        if (mount) {
            mount.appendChild(btn);
        } else {
            themeLabel.parentNode.insertBefore(btn, themeLabel);
        }

        // Sync the dot to current palette.
        var current = safeGet(LS_PALETTE, 'indigo');
        var def = PALETTES.find(function (p) { return p.id === current; });
        if (def) btn.querySelector('.palette-dot').style.background = def.swatch;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (popoverEl && popoverEl.classList.contains('is-open')) {
                closeThemePicker();
            } else {
                openThemePicker(btn);
            }
        });
    }

    /* ---------- Sync from DB on first load ---------- */
    function syncFromServer() {
        fetch('/api/settings/appearance', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        }).then(function (r) { return r.ok ? r.json() : null; })
          .then(function (data) {
              if (!data || !data.success) return;
              if (data.palette && paletteValid(data.palette)) {
                  if (safeGet(LS_PALETTE, null) !== data.palette) {
                      applyPalette(data.palette);
                  }
              }
              if (typeof data.theme_auto_schedule !== 'undefined') {
                  safeSet(LS_AUTO, data.theme_auto_schedule ? '1' : '0');
              }
              if (data.theme_dark_from)  safeSet(LS_DARK_FROM,  String(data.theme_dark_from).substr(0,5));
              if (data.theme_light_from) safeSet(LS_LIGHT_FROM, String(data.theme_light_from).substr(0,5));
              applyThemeSchedule();
          })
          .catch(function () { /* offline / unauth — no-op */ });
    }

    /* ---------- init ---------- */
    function init() {
        ensureHeaderToggle();

        // Re-evaluate schedule periodically + on visibility change.
        applyThemeSchedule();
        setInterval(applyThemeSchedule, 60 * 1000);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) applyThemeSchedule();
        });

        // Sync from server (so multi-device users see consistent settings).
        syncFromServer();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /* ---------- public API ---------- */
    global.setThemePalette           = setThemePalette;
    global.applyThemeSchedule        = applyThemeSchedule;
    global.disableThemeAutoSchedule  = disableThemeAutoSchedule;
    global.openThemePicker           = openThemePicker;
    global.closeThemePicker          = closeThemePicker;
    global.THEME_PALETTES            = PALETTES.slice();
})(window);
