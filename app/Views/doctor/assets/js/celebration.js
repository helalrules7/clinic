/* =====================================================================
   v12.0.0 — Celebration confetti for the dashboard notice bar.

   Fires once per browser session (sessionStorage flag, managed by the
   caller in dashboard.php). Respects prefers-reduced-motion.

   Public API
   ----------
   window.fireCelebrationConfetti(container, options?)
       options.waves  — burst waves (default 1)
       options.count  — particles per wave (default 40)
   ===================================================================== */
(function (global) {
    'use strict';

    var COLORS = [
        '#6366f1',
        '#8b5cf6',
        '#ec4899',
        '#f59e0b',
        '#10b981',
        '#22d3ee',
        '#fbbf24',
        '#f43f5e',
        '#a78bfa',
        '#ffffff',
    ];

    var SHAPES = ['rect', 'circle', 'strip', 'star'];

    function reduced() {
        try {
            return window.matchMedia &&
                   window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        } catch (_) { return false; }
    }

    function rand(min, max) { return min + Math.random() * (max - min); }
    function pick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

    function fireCelebrationConfetti(container, options) {
        if (!container || reduced()) return;

        options = options || {};
        var waves = Math.max(1, Math.min(3, options.waves || 1));
        var count = Math.max(12, Math.min(80, options.count || 40));

        if (!container.style.position) container.style.position = 'absolute';
        container.style.pointerEvents = 'none';
        container.style.overflow = 'visible';

        for (var w = 0; w < waves; w++) {
            (function (waveIndex) {
                var delay = waveIndex * 520;
                var originX = waveIndex === 0 ? rand(42, 58) : (waveIndex % 2 ? rand(18, 32) : rand(68, 82));
                setTimeout(function () {
                    for (var i = 0; i < count; i++) {
                        spawnParticle(container, i, originX, waveIndex);
                    }
                }, delay);
            })(w);
        }

        var cleanupMs = 520 * waves + 2800;
        setTimeout(function () {
            container.querySelectorAll('.confetti-piece').forEach(function (p) {
                p.remove();
            });
        }, cleanupMs);
    }

    function shapeStyles(shape, size, color) {
        if (shape === 'circle') {
            return 'width:' + size + 'px;height:' + size + 'px;border-radius:50%;';
        }
        if (shape === 'strip') {
            return 'width:' + (size * 0.45) + 'px;height:' + (size * 2.2) + 'px;border-radius:1px;';
        }
        if (shape === 'star') {
            return 'width:' + size + 'px;height:' + size + 'px;' +
                'clip-path:polygon(50% 0%,61% 35%,98% 35%,68% 57%,79% 91%,50% 70%,21% 91%,32% 57%,2% 35%,39% 35%);';
        }
        return 'width:' + size + 'px;height:' + (size * 1.35) + 'px;border-radius:2px;';
    }

    function spawnParticle(container, i, originX, waveIndex) {
        var p = document.createElement('span');
        p.className = 'confetti-piece';

        var color = COLORS[i % COLORS.length];
        var shape = pick(SHAPES);
        var size  = rand(5, 11);
        var startX = originX + rand(-8, 8);
        var dx     = rand(-280, 280) * (waveIndex === 0 ? 1 : 0.75);
        var dy     = rand(-220, -70);
        var rot    = rand(-720, 720);
        var dur    = rand(1.4, 2.2);
        var delay  = rand(0, 0.18);

        p.style.cssText =
            'position:absolute;' +
            'left:' + startX + '%;' +
            'top:50%;' +
            shapeStyles(shape, size, color) +
            'background:' + color + ';' +
            'opacity:1;' +
            'transform:translate(-50%,-50%) rotate(0deg);' +
            'will-change:transform,opacity;' +
            'pointer-events:none;' +
            'box-shadow:0 0 8px ' + color + '88;';

        container.appendChild(p);

        var keyframes = [
            {
                transform: 'translate(-50%, -50%) rotate(0deg) scale(1)',
                opacity: 1,
                offset: 0
            },
            {
                transform: 'translate(calc(-50% + ' + dx + 'px), calc(-50% + ' + dy + 'px)) rotate(' + (rot * 0.45) + 'deg) scale(1.1)',
                opacity: 1,
                offset: 0.4
            },
            {
                transform: 'translate(calc(-50% + ' + (dx * 1.5) + 'px), calc(-50% + 140px)) rotate(' + rot + 'deg) scale(0.6)',
                opacity: 0,
                offset: 1
            }
        ];

        try {
            var anim = p.animate(keyframes, {
                duration: dur * 1000,
                delay: delay * 1000,
                fill: 'forwards',
                easing: 'cubic-bezier(.18, .72, .32, 1)'
            });
            anim.onfinish = function () { p.remove(); };
        } catch (_) {
            setTimeout(function () { p.remove(); }, dur * 1000 + 200);
        }
    }

    global.fireCelebrationConfetti = fireCelebrationConfetti;
})(window);
