/* =====================================================================
   v11.0.0 — Celebration confetti for the dashboard notice bar.

   Fires once per browser session (sessionStorage flag, managed by the
   caller in dashboard.php). Respects prefers-reduced-motion.

   Public API
   ----------
   window.fireCelebrationConfetti(container)
       Spawn ~30 small DOM particles inside the given container, animate
       them outward + downward (gravity), fade out, then remove.
   ===================================================================== */
(function (global) {
    'use strict';

    var COLORS = [
        '#6366f1', // indigo
        '#8b5cf6', // violet
        '#ec4899', // pink
        '#f59e0b', // amber
        '#10b981', // emerald
        '#22d3ee', // cyan
        '#fbbf24', // gold
    ];

    function reduced() {
        try {
            return window.matchMedia &&
                   window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        } catch (_) { return false; }
    }

    function rand(min, max) { return min + Math.random() * (max - min); }

    function fireCelebrationConfetti(container) {
        if (!container || reduced()) return;
        if (container.dataset.confettiFired === '1') return;
        container.dataset.confettiFired = '1';

        // Ensure the container is set up for particles to anchor against.
        var prev = container.style.position;
        if (!prev) container.style.position = 'absolute';
        container.style.pointerEvents = 'none';
        container.style.overflow = 'visible';

        var count = 32;
        for (var i = 0; i < count; i++) {
            spawnParticle(container, i);
        }

        // Auto-cleanup after the longest animation finishes (1.8s + slop).
        setTimeout(function () {
            // Only clear our own particles, not anything that was in the box.
            container.querySelectorAll('.confetti-piece').forEach(function (p) {
                p.remove();
            });
        }, 2400);
    }

    function spawnParticle(container, i) {
        var p = document.createElement('span');
        p.className = 'confetti-piece';

        var color = COLORS[i % COLORS.length];
        var startX = rand(40, 60);              // % horizontally — burst from centre-ish
        var dx     = rand(-220, 220);            // px horizontal drift
        var dy     = rand(-180, -90);            // px initial upward jump (negative = up)
        var rot    = rand(-540, 540);            // total rotation degrees
        var size   = rand(5, 9);
        var height = rand(8, 14);
        var dur    = rand(1.2, 1.8);
        var delay  = rand(0, 0.12);

        p.style.cssText =
            'position:absolute;' +
            'left:' + startX + '%;' +
            'top:50%;' +
            'width:' + size + 'px;' +
            'height:' + height + 'px;' +
            'background:' + color + ';' +
            'border-radius:2px;' +
            'opacity:1;' +
            'transform:translate(-50%,-50%) rotate(0deg);' +
            'will-change:transform,opacity;' +
            'pointer-events:none;' +
            'box-shadow:0 0 6px ' + color + '66;';

        container.appendChild(p);

        // Two-stage animation via Web Animations API for smooth motion.
        var keyframes = [
            { transform: 'translate(-50%, -50%) rotate(0deg)',
              opacity: 1, offset: 0 },
            { transform: 'translate(calc(-50% + ' + dx + 'px), calc(-50% + ' + dy + 'px)) rotate(' + (rot * 0.5) + 'deg)',
              opacity: 1, offset: 0.35 },
            { transform: 'translate(calc(-50% + ' + (dx * 1.4) + 'px), calc(-50% + 120px)) rotate(' + rot + 'deg)',
              opacity: 0, offset: 1 },
        ];
        try {
            var anim = p.animate(keyframes, {
                duration: dur * 1000,
                delay: delay * 1000,
                fill: 'forwards',
                easing: 'cubic-bezier(.22, .61, .36, 1)',
            });
            anim.onfinish = function () { p.remove(); };
        } catch (_) {
            // Fallback for very old browsers — just remove after a timeout.
            setTimeout(function () { p.remove(); }, dur * 1000 + 200);
        }
    }

    global.fireCelebrationConfetti = fireCelebrationConfetti;
})(window);
