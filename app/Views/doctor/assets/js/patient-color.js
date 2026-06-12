/* =====================================================================
   v12.0.0 — Patient avatar color seeding.

   Deterministic per patient_id → consistent across the app. Used by:
   - Patient hover-card avatar
   - Patients Board cards (small accent dot)
   - Notification icons for patient-related notifications
   - Calendar event chips

   Pure JS, no DB changes. Two helpers + an auto-apply scan.

   Algorithm: HSL hue = (id * 137) mod 360. 137 is a coprime stride that
   spreads adjacent IDs widely around the color wheel.

   Public API
   ----------
   window.patientColor(id, isDark?)        → "hsl(247, 60%, 55%)"
   window.patientColorMuted(id, isDark?)   → tinted background
   window.applyPatientColors(root?)        → scans [data-patient-id] elements
                                             under `root` and sets CSS vars
                                             --pt-color / --pt-bg / --pt-fg.
   ===================================================================== */
(function (global) {
    'use strict';

    function isDarkMode() {
        return document.documentElement.classList.contains('dark');
    }

    function safeId(id) {
        if (id === null || id === undefined) return 0;
        var n = parseInt(String(id), 10);
        return Number.isFinite(n) && n >= 0 ? n : 0;
    }

    function patientColor(id, isDark) {
        var n = safeId(id);
        if (typeof isDark !== 'boolean') isDark = isDarkMode();
        if (!n) return isDark ? '#475569' : '#94A3B8';
        var hue = (n * 137) % 360;
        var L = isDark ? 65 : 55;
        return 'hsl(' + hue + ', 60%, ' + L + '%)';
    }

    function patientColorMuted(id, isDark) {
        var n = safeId(id);
        if (typeof isDark !== 'boolean') isDark = isDarkMode();
        if (!n) return isDark ? 'rgba(71,85,105,0.18)' : 'rgba(148,163,184,0.18)';
        var hue = (n * 137) % 360;
        var L = isDark ? 35 : 88;
        var alpha = isDark ? 0.50 : 0.85;
        return 'hsla(' + hue + ', 50%, ' + L + '%, ' + alpha + ')';
    }

    function patientColorFg(id, isDark) {
        // Foreground color for text/icons sitting on the muted bg. Same hue,
        // high contrast lightness.
        var n = safeId(id);
        if (typeof isDark !== 'boolean') isDark = isDarkMode();
        if (!n) return isDark ? '#E2E8F0' : '#0F172A';
        var hue = (n * 137) % 360;
        var L = isDark ? 85 : 28;
        return 'hsl(' + hue + ', 55%, ' + L + '%)';
    }

    function patientInitials(name) {
        if (!name) return '?';
        var parts = String(name).trim().split(/\s+/).slice(0, 2);
        return parts.map(function (p) { return p.charAt(0).toUpperCase(); }).join('');
    }

    function applyPatientColors(root) {
        var dark = isDarkMode();
        (root || document).querySelectorAll('[data-patient-id]').forEach(function (el) {
            var id = el.getAttribute('data-patient-id');
            el.style.setProperty('--pt-color', patientColor(id, dark));
            el.style.setProperty('--pt-bg',    patientColorMuted(id, dark));
            el.style.setProperty('--pt-fg',    patientColorFg(id, dark));
        });
    }

    // Re-apply when the theme flips (dark ↔ light) so backgrounds stay readable.
    function watchThemeChanges() {
        var html = document.documentElement;
        var obs = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].attributeName === 'class') {
                    applyPatientColors();
                    return;
                }
            }
        });
        obs.observe(html, { attributes: true, attributeFilter: ['class'] });
    }

    global.patientColor       = patientColor;
    global.patientColorMuted  = patientColorMuted;
    global.patientColorFg     = patientColorFg;
    global.patientInitials    = patientInitials;
    global.applyPatientColors = applyPatientColors;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            applyPatientColors();
            watchThemeChanges();
        });
    } else {
        applyPatientColors();
        watchThemeChanges();
    }
})(window);
