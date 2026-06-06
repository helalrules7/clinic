/* =====================================================================
 * note-bg.js — shared gradient / glassmorphism background presets for the
 * whole notes system (quick-note modal, notes drawer, dashboard board-notes
 * widget and the full notes page). One source of truth so every surface
 * offers the same swatches and renders a saved note identically.
 *
 * A note's `background_color` is stored as either:
 *   - a preset token  → "grad-aurora", "grad-ocean", … (see PRESETS)
 *   - a raw hex/color → "#fbbf24" (legacy board notes still work)
 *   - empty / null    → default themed surface
 *
 * Public API (window.NoteBG):
 *   NoteBG.PRESETS            ordered array of { id, label, css }
 *   NoteBG.isPreset(token)    → bool
 *   NoteBG.apply(el, token)   paint an element (adds .note-glass + gradient)
 *   NoteBG.swatchHTML(active) build the swatch picker markup (radio buttons)
 * ===================================================================== */
(function (global) {
    'use strict';

    if (global.NoteBG) return;

    // Translucent gradients so the frosted-glass backdrop-filter reads through
    // them — that is what gives the "glassmorphism" look rather than a flat fill.
    var PRESETS = [
        { id: 'grad-aurora', label: 'Aurora', css: 'linear-gradient(135deg, rgba(99,102,241,.92), rgba(168,85,247,.92))' },
        { id: 'grad-ocean',  label: 'Ocean',  css: 'linear-gradient(135deg, rgba(6,182,212,.92), rgba(59,130,246,.92))' },
        { id: 'grad-mint',   label: 'Mint',   css: 'linear-gradient(135deg, rgba(16,185,129,.92), rgba(20,184,166,.92))' },
        { id: 'grad-sunset', label: 'Sunset', css: 'linear-gradient(135deg, rgba(249,115,22,.92), rgba(239,68,68,.92))' },
        { id: 'grad-rose',   label: 'Rose',   css: 'linear-gradient(135deg, rgba(244,63,94,.92), rgba(236,72,153,.92))' },
        { id: 'grad-gold',   label: 'Gold',   css: 'linear-gradient(135deg, rgba(245,158,11,.92), rgba(234,179,8,.92))' },
        { id: 'grad-violet', label: 'Violet', css: 'linear-gradient(135deg, rgba(139,92,246,.92), rgba(236,72,153,.92))' },
        { id: 'grad-night',  label: 'Night',  css: 'linear-gradient(135deg, rgba(30,41,59,.94), rgba(2,6,23,.94))' }
    ];

    var byId = {};
    PRESETS.forEach(function (p) { byId[p.id] = p; });

    function isPreset(token) {
        return !!(token && byId[token]);
    }

    function clear(el) {
        if (!el) return;
        el.classList.remove('note-glass');
        el.style.removeProperty('--note-grad');
        el.style.removeProperty('background');
        el.removeAttribute('data-note-bg');
    }

    // Paint `el` with the given token. Adds .note-glass (frosted treatment in
    // note-bg.css) and sets the gradient via the --note-grad custom property.
    function apply(el, token) {
        if (!el) return;
        clear(el);
        if (!token) return;
        el.setAttribute('data-note-bg', token);
        if (byId[token]) {
            el.style.setProperty('--note-grad', byId[token].css);
            el.classList.add('note-glass');
        } else if (/^#|^rgb|^hsl/i.test(String(token))) {
            // Legacy solid colour — still give it the frosted glass shell.
            el.style.setProperty('--note-grad', String(token));
            el.classList.add('note-glass');
        }
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // Build a swatch picker. `active` is the currently-selected token.
    // Each swatch is a button[data-note-swatch="<id>"]; a "None" option clears.
    function swatchHTML(active) {
        var html = '<div class="note-swatches" role="radiogroup" aria-label="Note background">';
        html += '<button type="button" class="note-swatch note-swatch--none' +
                (!active ? ' is-active' : '') + '" data-note-swatch="" ' +
                'role="radio" aria-checked="' + (!active ? 'true' : 'false') + '" ' +
                'title="Default" aria-label="Default background"><i class="bi bi-slash-circle"></i></button>';
        PRESETS.forEach(function (p) {
            var on = active === p.id;
            html += '<button type="button" class="note-swatch' + (on ? ' is-active' : '') + '" ' +
                    'data-note-swatch="' + esc(p.id) + '" style="background:' + p.css + '" ' +
                    'role="radio" aria-checked="' + (on ? 'true' : 'false') + '" ' +
                    'title="' + esc(p.label) + '" aria-label="' + esc(p.label) + ' background"></button>';
        });
        html += '</div>';
        return html;
    }

    global.NoteBG = {
        PRESETS: PRESETS.slice(),
        isPreset: isPreset,
        apply: apply,
        clear: clear,
        swatchHTML: swatchHTML
    };
})(window);
