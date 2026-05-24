/* ============================================================================
   WeatherFx — shared weather visuals + clinic logic, used by all three weather
   surfaces (notice-bar popover, dashboard widget, 7-day forecast window).
   Loaded once in the layout so every surface renders identically.
   Public API (window.WeatherFx):
     normCondition(condition)        -> 'clear'|'partly'|'clouds'|'rain'|'drizzle'|'snow'|'fog'|'thunder'
     isDay(data)                     -> bool   (uses data.isDay, else icon d/n suffix, else local hour)
     computePollen(data)             -> 0..100
     computeDryEye(data)             -> 0..100
     uvInfo(uv)                      -> { value, label, color, pct }
     advisory(data)                  -> { level, icon, title, text }
     sceneHTML(data)                 -> animated scene background markup
     iconHTML(data, sizePx)          -> animated weather icon markup
     uvMeterHTML(uv)                 -> UV index meter markup
     advisoryHTML(data)             -> eye-care advisory markup
   All strings are English.
   ============================================================================ */
(function () {
    'use strict';

    function normCondition(c) {
        c = (c || '').toString().toLowerCase();
        if (c.includes('thunder') || c.includes('storm')) return 'thunder';
        if (c.includes('snow') || c.includes('sleet') || c.includes('ice') || c.includes('flurr') || c.includes('blizzard')) return 'snow';
        if (c.includes('drizzle')) return 'drizzle';
        if (c.includes('rain') || c.includes('shower')) return 'rain';
        if (c.includes('fog') || c.includes('mist') || c.includes('haze') || c.includes('smoke') || c.includes('rime')) return 'fog';
        if (c.includes('overcast')) return 'clouds';
        // "Mainly Clear" is treated as CLEAR (sun only) — not partly — so it doesn't pull
        // the sun+cloud icon. Genuine partly/scattered/few-clouds stay 'partly'.
        if (c.includes('partly') || c.includes('scattered') || c.includes('few')) return 'partly';
        if (c.includes('broken')) return 'clouds';
        if (c.includes('cloud')) return 'clouds';
        if (c.includes('clear') || c.includes('sun')) return 'clear';
        return 'clear';
    }

    function isDay(d) {
        if (d && (d.isDay === 0 || d.isDay === 1)) return d.isDay === 1;
        if (d && typeof d.icon === 'string') {
            if (d.icon.endsWith('n')) return false;
            if (d.icon.endsWith('d')) return true;
        }
        const h = new Date().getHours();
        return h >= 6 && h < 18;
    }

    function clamp(n) { return Math.max(0, Math.min(100, n)); }

    // --- Clinic health indices (identical formulas to the legacy inline ones) ---
    function computePollen(w) {
        let s = 50;
        const temp = w.temperature ?? 20;
        const humidity = w.humidity ?? 50;
        const wind = w.windSpeed ?? 10;
        const raining = (w.condition || '').toLowerCase().includes('rain');
        if (temp >= 15 && temp <= 25) s += 20; else if (temp > 25 && temp <= 30) s += 10; else if (temp < 10 || temp > 35) s -= 20;
        if (humidity < 40) s += 15; else if (humidity > 70) s -= 15;
        if (wind >= 10 && wind <= 25) s += 15; else if (wind > 30) s -= 10;
        if (raining) s -= 30;
        return clamp(s);
    }

    function computeDryEye(w) {
        let s = 30;
        const temp = w.temperature ?? 20;
        const humidity = w.humidity ?? 50;
        const wind = w.windSpeed ?? 10;
        const uv = w.uvIndex ?? 5;
        if (humidity < 30) s += 35; else if (humidity < 45) s += 20; else if (humidity > 60) s -= 15;
        if (temp > 30 && humidity < 50) s += 15;
        if (wind > 20) s += 20; else if (wind > 10) s += 10;
        if (uv > 7) s += 15; else if (uv > 5) s += 8;
        return clamp(s);
    }

    function uvInfo(uv) {
        uv = Math.round(uv || 0);
        let label, color;
        if (uv <= 2) { label = 'Low'; color = '#22c55e'; }
        else if (uv <= 5) { label = 'Moderate'; color = '#eab308'; }
        else if (uv <= 7) { label = 'High'; color = '#f97316'; }
        else if (uv <= 10) { label = 'Very High'; color = '#ef4444'; }
        else { label = 'Extreme'; color = '#a855f7'; }
        return { value: uv, label, color, pct: Math.min(100, (uv / 11) * 100) };
    }

    // --- Eye-care advisory: ophthalmology-relevant tip derived from the weather ---
    function advisory(w) {
        const uv = Math.round(w.uvIndex ?? 0);
        const humidity = w.humidity ?? 50;
        const wind = w.windSpeed ?? 0;
        const pollen = computePollen(w);
        const dryEye = computeDryEye(w);

        if (uv >= 8) {
            return { level: 'alert', icon: 'bi-sun', title: 'Very high UV',
                text: 'Advise UV-blocking sunglasses and a brimmed hat outdoors.' };
        }
        if (dryEye >= 60 || (humidity < 35 && wind > 20)) {
            return { level: 'warn', icon: 'bi-eye', title: 'High dry-eye risk',
                text: 'Recommend lubricating drops and regular screen breaks.' };
        }
        if (pollen >= 65) {
            return { level: 'warn', icon: 'bi-flower1', title: 'High pollen',
                text: 'Allergic conjunctivitis risk — consider antihistamine drops.' };
        }
        if (uv >= 6) {
            return { level: 'info', icon: 'bi-brightness-high', title: 'Moderate UV',
                text: 'Sunglasses recommended for outdoor exposure.' };
        }
        if (dryEye >= 45) {
            return { level: 'info', icon: 'bi-eye', title: 'Mild dry-eye risk',
                text: 'Stay hydrated; lubricating drops if eyes feel dry.' };
        }
        return { level: 'info', icon: 'bi-check-circle', title: 'Comfortable',
            text: 'Good conditions for outdoor eye comfort today.' };
    }

    // --- scene markup helpers (clean, soft, performant — class prefix .wxs-) ---
    function rnd(min, max) { return (Math.random() * (max - min) + min); }

    function sunGlow() { return '<span class="wxs-sun"></span>'; }
    function moonEl() { return '<span class="wxs-moon"></span>'; }
    function fogBands() {
        return '<span class="wxs-fog wxs-fog--1"></span><span class="wxs-fog wxs-fog--2"></span><span class="wxs-fog wxs-fog--3"></span>';
    }

    function stars(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const x = rnd(3, 97).toFixed(1), y = rnd(4, 66).toFixed(1);
            const s = rnd(1, 2.4).toFixed(1), dl = rnd(0, 3).toFixed(2), du = rnd(1.8, 3.6).toFixed(2);
            h += `<span class="wxs-star" style="left:${x}%;top:${y}%;width:${s}px;height:${s}px;animation-delay:-${dl}s;animation-duration:${du}s"></span>`;
        }
        return h;
    }
    function volumetricCloud(type = 'front', isNight = false) {
        const top = rnd(type === 'back' ? 2 : 12, type === 'back' ? 32 : 45).toFixed(0);
        const scale = type === 'back' ? rnd(0.55, 0.85).toFixed(2) : rnd(1.0, 1.45).toFixed(2);
        const dur = type === 'back' ? rnd(55, 85).toFixed(0) : rnd(28, 42).toFixed(0);
        const delay = (-rnd(0, 80)).toFixed(0);
        const op = type === 'back' 
            ? (isNight ? rnd(0.25, 0.45).toFixed(2) : rnd(0.35, 0.65).toFixed(2)) 
            : (isNight ? rnd(0.55, 0.75).toFixed(2) : rnd(0.75, 0.95).toFixed(2));
        const uniqueId = `wx-cgrad-${Math.random().toString(36).substr(2, 9)}`;

        let gradLight, gradDark;
        if (isNight) {
            gradLight = `
                <stop offset="0%" stop-color="#475569" stop-opacity="0.8" />
                <stop offset="50%" stop-color="#334155" stop-opacity="0.75" />
                <stop offset="100%" stop-color="#1e293b" stop-opacity="0.7" />
            `;
            gradDark = `
                <stop offset="0%" stop-color="#1e293b" stop-opacity="0.8" />
                <stop offset="100%" stop-color="#0f172a" stop-opacity="0.8" />
            `;
        } else {
            gradLight = `
                <stop offset="0%" stop-color="#ffffff" />
                <stop offset="45%" stop-color="#f8fafc" />
                <stop offset="100%" stop-color="#cbd5e1" />
            `;
            gradDark = `
                <stop offset="0%" stop-color="#e2e8f0" />
                <stop offset="50%" stop-color="#cbd5e1" />
                <stop offset="100%" stop-color="#94a3b8" />
            `;
        }

        return `
        <svg class="wx-cloud-svg wx-cloud-svg--${type}" style="top:${top}%;transform:scale(${scale});animation-duration:${dur}s;animation-delay:${delay}s;opacity:${op}" viewBox="0 0 200 100" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="${uniqueId}-light" x1="0%" y1="0%" x2="0%" y2="100%">
                    ${gradLight}
                </linearGradient>
                <linearGradient id="${uniqueId}-dark" x1="0%" y1="0%" x2="0%" y2="100%">
                    ${gradDark}
                </linearGradient>
                <filter id="${uniqueId}-blur" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="3.2" />
                </filter>
            </defs>
            <!-- Shadow Layer -->
            <path d="M 30,75 A 25,25 0 0,1 60,50 A 35,35 0 0,1 125,45 A 25,25 0 0,1 165,60 A 20,20 0 0,1 165,78 A 15,15 0 0,1 30,75 Z" fill="url(#${uniqueId}-dark)" filter="url(#${uniqueId}-blur)" transform="translate(0, 3.5)" opacity="0.65" />
            <!-- Core Cloud Layer -->
            <path d="M 30,75 A 25,25 0 0,1 60,50 A 35,35 0 0,1 125,45 A 25,25 0 0,1 165,60 A 20,20 0 0,1 165,78 A 15,15 0 0,1 30,75 Z" fill="url(#${uniqueId}-light)" filter="url(#${uniqueId}-blur)" />
        </svg>
        `;
    }

    function clouds(n, type = 'front', isNight = false) {
        const back = type === 'back';
        let h = '';
        for (let i = 0; i < n; i++) {
            const w = (back ? rnd(30, 44) : rnd(50, 70)).toFixed(0);
            const top = (back ? rnd(2, 26) : rnd(14, 50)).toFixed(0);
            const dur = (back ? rnd(60, 95) : rnd(34, 52)).toFixed(0);
            const delay = rnd(0, 70).toFixed(0);
            const op = (back ? rnd(0.4, 0.65) : rnd(0.78, 1)).toFixed(2);
            h += `<span class="wxs-cloud${isNight ? ' wxs-cloud--night' : ''}" style="width:${w}%;top:${top}%;opacity:${op};animation-duration:${dur}s;animation-delay:-${delay}s"></span>`;
        }
        return h;
    }

    function bokeh(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const left = rnd(5, 85).toFixed(1);
            const top = rnd(10, 75).toFixed(1);
            const size = rnd(30, 65).toFixed(0);
            const delay = (-rnd(0, 12)).toFixed(1);
            const dur = rnd(12, 24).toFixed(0);
            const op = rnd(0.05, 0.13).toFixed(3);
            h += `<span class="wx-bokeh" style="left:${left}%;top:${top}%;width:${size}px;height:${size}px;animation-delay:${delay}s;animation-duration:${dur}s;opacity:${op}"></span>`;
        }
        return h;
    }

    function wxVolumetricSunSvg() {
        return `
        <svg class="wx-bg-sun-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="wx-sc-core-grad" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#ffffff" />
                    <stop offset="25%" stop-color="#fffbeb" />
                    <stop offset="65%" stop-color="#fef08a" />
                    <stop offset="100%" stop-color="#f59e0b" stop-opacity="0" />
                </radialGradient>
                <filter id="wx-sc-sun-blur" x="-50%" y="-50%" width="200%" height="200%">
                    <feGaussianBlur stdDeviation="8" />
                </filter>
            </defs>
            <circle cx="100" cy="100" r="54" fill="url(#wx-sc-core-grad)" />
            <g class="wx-bg-sun-beams" filter="url(#wx-sc-sun-blur)">
                <polygon points="100,10 93,100 107,100" fill="#fef08a" opacity="0.25" />
                <polygon points="100,190 93,100 107,100" fill="#fef08a" opacity="0.25" />
                <polygon points="10,100 100,93 100,107" fill="#fef08a" opacity="0.25" />
                <polygon points="190,100 100,93 100,107" fill="#fef08a" opacity="0.25" />
                
                <polygon points="40,40 100,94 100,106" fill="#fef08a" opacity="0.2" transform="rotate(45 100 100)" />
                <polygon points="160,160 100,94 100,106" fill="#fef08a" opacity="0.2" transform="rotate(45 100 100)" />
                <polygon points="40,160 94,100 106,100" fill="#fef08a" opacity="0.2" transform="rotate(45 100 100)" />
                <polygon points="160,40 94,100 106,100" fill="#fef08a" opacity="0.2" transform="rotate(45 100 100)" />

                <polygon points="100,10 94,100 106,100" fill="#fef08a" opacity="0.16" transform="rotate(22.5 100 100)" />
                <polygon points="100,190 94,100 106,100" fill="#fef08a" opacity="0.16" transform="rotate(22.5 100 100)" />
                <polygon points="10,100 100,94 100,106" fill="#fef08a" opacity="0.16" transform="rotate(22.5 100 100)" />
                <polygon points="190,100 100,94 100,106" fill="#fef08a" opacity="0.16" transform="rotate(22.5 100 100)" />

                <polygon points="100,10 94,100 106,100" fill="#fef08a" opacity="0.16" transform="rotate(67.5 100 100)" />
                <polygon points="100,190 94,100 106,100" fill="#fef08a" opacity="0.16" transform="rotate(67.5 100 100)" />
                <polygon points="10,100 100,94 100,106" fill="#fef08a" opacity="0.16" transform="rotate(67.5 100 100)" />
                <polygon points="190,100 100,94 100,106" fill="#fef08a" opacity="0.16" transform="rotate(67.5 100 100)" />
            </g>
            <circle cx="100" cy="100" r="16" fill="#ffffff" filter="url(#wx-sc-sun-blur)" opacity="0.95" />
        </svg>
        `;
    }

    function rain(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const x = rnd(0, 100).toFixed(1);
            const dl = rnd(0, 1.2).toFixed(2);
            const du = rnd(0.45, 0.8).toFixed(2);
            const op = rnd(0.3, 0.65).toFixed(2);
            const hPx = rnd(16, 30).toFixed(0);
            h += `<span class="wxs-drop" style="left:${x}%;height:${hPx}px;animation-delay:-${dl}s;animation-duration:${du}s;opacity:${op}"></span>`;
        }
        return h;
    }
    function snow(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const x = rnd(0, 100).toFixed(1);
            const dl = rnd(0, 4).toFixed(2);
            const du = rnd(4, 7.5).toFixed(2);
            const s = rnd(3, 6.5).toFixed(1);
            const op = rnd(0.4, 0.9).toFixed(2);
            h += `<span class="wxs-flake" style="left:${x}%;width:${s}px;height:${s}px;animation-delay:-${dl}s;animation-duration:${du}s;opacity:${op}"></span>`;
        }
        return h;
    }

    function sceneHTML(d) {
        const key = normCondition(d && d.condition);
        const day = isDay(d);
        // Scenes are ATMOSPHERE only (sky + glow/moon/stars + precipitation/haze/flash).
        // The cloud (and every weather symbol) is conveyed by the Gemini foreground icon —
        // so the scene deliberately renders NO cloud shapes of its own.
        let inner = '';
        if (key === 'clear' || key === 'partly') {
            inner = day ? sunGlow() : (moonEl() + stars(key === 'clear' ? 22 : 14));
        } else if (key === 'clouds' || key === 'fog') {
            inner = fogBands();
        } else if (key === 'rain') {
            inner = rain(22);
        } else if (key === 'drizzle') {
            inner = rain(11);
        } else if (key === 'snow') {
            inner = snow(16);
        } else if (key === 'thunder') {
            inner = rain(16) + '<span class="wxs-flash"></span>';
        }
        return `<div class="wx-scene wx-scene--${key} ${day ? 'wx--day' : 'wx--night'}">${inner}</div>`;
    }

    // Clean SVG sun (circular disc + 8 attached rays that spin) — robust at any size.
    function wxSunSvg() {
        let rays = '';
        for (let i = 0; i < 8; i++) {
            const a = (i * 45) * Math.PI / 180;
            const x1 = (50 + 29 * Math.cos(a)).toFixed(1), y1 = (50 + 29 * Math.sin(a)).toFixed(1);
            const x2 = (50 + 44 * Math.cos(a)).toFixed(1), y2 = (50 + 44 * Math.sin(a)).toFixed(1);
            rays += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}"/>`;
        }
        return `<svg class="wxi-svg-sun" viewBox="0 0 100 100"><g class="wxi-sun-rays">${rays}</g>`
            + `<circle class="wxi-sun-disc" cx="50" cy="50" r="20"/></svg>`;
    }

    // --- Animated CSS weather icon ---
    function iconHTML(d, sizePx) {
        const key = normCondition(d && d.condition);
        const day = isDay(d);
        const size = sizePx || 56;
        let type, body = '';
        if (key === 'clear') {
            if (day) {
                type = 'sun';
                body = `<svg class="wx-svg-icon wx-svg-sun" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <radialGradient id="wx-sun-disc-grad" cx="35%" cy="35%" r="65%">
                            <stop offset="0%" stop-color="#fff9c4" />
                            <stop offset="45%" stop-color="#ffb300" />
                            <stop offset="90%" stop-color="#ff8f00" />
                            <stop offset="100%" stop-color="#e65100" />
                        </radialGradient>
                        <linearGradient id="wx-sun-ray-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#ffe082" />
                            <stop offset="50%" stop-color="#ffb300" />
                            <stop offset="100%" stop-color="#ff8f00" />
                        </linearGradient>
                        <radialGradient id="wx-sun-glow-grad" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#ffb300" stop-opacity="0.4" />
                            <stop offset="40%" stop-color="#ff9100" stop-opacity="0.15" />
                            <stop offset="100%" stop-color="#ff6f00" stop-opacity="0" />
                        </radialGradient>
                    </defs>
                    <circle cx="50" cy="50" r="48" fill="url(#wx-sun-glow-grad)" class="wx-sun-glow-bg"/>
                    <g class="wx-sun-rays">
                        <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-sun-ray-grad)" />
                        <rect x="47" y="80" width="6" height="15" rx="3" fill="url(#wx-sun-ray-grad)" />
                        <rect x="5" y="47" width="15" height="6" rx="3" fill="url(#wx-sun-ray-grad)" />
                        <rect x="80" y="47" width="15" height="6" rx="3" fill="url(#wx-sun-ray-grad)" />
                        <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-sun-ray-grad)" transform="rotate(45 50 50)" />
                        <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-sun-ray-grad)" transform="rotate(135 50 50)" />
                        <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-sun-ray-grad)" transform="rotate(225 50 50)" />
                        <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-sun-ray-grad)" transform="rotate(315 50 50)" />
                    </g>
                    <circle cx="50" cy="50" r="19" fill="url(#wx-sun-disc-grad)" />
                    <path d="M 37,37 A 19,19 0 0,1 63,37 A 16,16 0 0,0 37,37 Z" fill="#ffffff" opacity="0.4" />
                </svg>`;
            } else {
                type = 'moon';
                body = `<svg class="wx-svg-icon wx-svg-moon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="wx-moon-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#ffffff" />
                            <stop offset="35%" stop-color="#e8eaf6" />
                            <stop offset="70%" stop-color="#c5cae9" />
                            <stop offset="100%" stop-color="#9fa8da" />
                        </linearGradient>
                        <radialGradient id="wx-moon-glow-grad" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#c5cae9" stop-opacity="0.5" />
                            <stop offset="100%" stop-color="#c5cae9" stop-opacity="0" />
                        </radialGradient>
                    </defs>
                    <circle cx="48" cy="48" r="35" fill="url(#wx-moon-glow-grad)" class="wx-moon-glow-bg" />
                    <path d="M 62,28 A 24,24 0 1,0 62,68 A 20,20 0 1,1 62,28 Z" fill="url(#wx-moon-grad)" class="wx-moon-crescent" />
                    <path d="M 62,28 A 24,24 0 0,0 42,48 A 20,20 0 0,1 62,28 Z" fill="#ffffff" opacity="0.25" />
                    <g class="wx-moon-stars">
                        <path class="wx-star-1" d="M 30,22 Q 30,26 34,26 Q 30,26 30,30 Q 30,26 26,26 Q 30,26 30,22 Z" fill="#fff" />
                        <path class="wx-star-2" d="M 74,18 Q 74,21 77,21 Q 74,21 74,24 Q 74,21 71,21 Q 74,21 74,18 Z" fill="#fff" />
                        <path class="wx-star-3" d="M 76,46 Q 76,48 78,48 Q 76,48 76,50 Q 76,48 74,48 Q 76,48 76,46 Z" fill="#fff" />
                    </g>
                </svg>`;
            }
        } else if (key === 'partly') {
            if (day) {
                type = 'partly-day';
                body = `<svg class="wx-svg-icon wx-svg-partly-day" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <radialGradient id="wx-pd-sun-grad" cx="35%" cy="35%" r="65%">
                            <stop offset="0%" stop-color="#fff9c4" />
                            <stop offset="45%" stop-color="#ffb300" />
                            <stop offset="90%" stop-color="#ff8f00" />
                            <stop offset="100%" stop-color="#e65100" />
                        </radialGradient>
                        <linearGradient id="wx-pd-ray-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#ffe082" />
                            <stop offset="50%" stop-color="#ffb300" />
                            <stop offset="100%" stop-color="#ff8f00" />
                        </linearGradient>
                        <linearGradient id="wx-pd-cloud-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#ffffff" />
                            <stop offset="30%" stop-color="#f8fafc" />
                            <stop offset="75%" stop-color="#e2e8f0" />
                            <stop offset="100%" stop-color="#cbd5e1" />
                        </linearGradient>
                    </defs>
                    <g class="wx-pd-sun" transform="translate(18, 14) scale(0.68)">
                        <g class="wx-sun-rays">
                            <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-pd-ray-grad)" />
                            <rect x="47" y="80" width="6" height="15" rx="3" fill="url(#wx-pd-ray-grad)" />
                            <rect x="5" y="47" width="15" height="6" rx="3" fill="url(#wx-pd-ray-grad)" />
                            <rect x="80" y="47" width="15" height="6" rx="3" fill="url(#wx-pd-ray-grad)" />
                            <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-pd-ray-grad)" transform="rotate(45 50 50)" />
                            <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-pd-ray-grad)" transform="rotate(135 50 50)" />
                            <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-pd-ray-grad)" transform="rotate(225 50 50)" />
                            <rect x="47" y="5" width="6" height="15" rx="3" fill="url(#wx-pd-ray-grad)" transform="rotate(315 50 50)" />
                        </g>
                        <circle cx="50" cy="50" r="19" fill="url(#wx-pd-sun-grad)" />
                        <path d="M 37,37 A 19,19 0 0,1 63,37 A 16,16 0 0,0 37,37 Z" fill="#ffffff" opacity="0.4" />
                    </g>
                    <path d="M 28,70 A 14,14 0 0,1 40,48 A 18,18 0 0,1 70,50 A 13,13 0 0,1 80,70 Z" fill="#0f172a" opacity="0.12" transform="translate(0, 3)" />
                    <path d="M 28,70 A 14,14 0 0,1 40,48 A 18,18 0 0,1 70,50 A 13,13 0 0,1 80,70 Z" fill="url(#wx-pd-cloud-grad)" stroke="rgba(255, 255, 255, 0.9)" stroke-width="1" class="wx-cloud-front" />
                </svg>`;
            } else {
                type = 'partly-night';
                body = `<svg class="wx-svg-icon wx-svg-partly-night" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="wx-pn-moon-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#ffffff" />
                            <stop offset="35%" stop-color="#e8eaf6" />
                            <stop offset="70%" stop-color="#c5cae9" />
                            <stop offset="100%" stop-color="#9fa8da" />
                        </linearGradient>
                        <linearGradient id="wx-pn-cloud-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#ffffff" />
                            <stop offset="40%" stop-color="#cbd5e1" />
                            <stop offset="100%" stop-color="#94a3b8" />
                        </linearGradient>
                    </defs>
                    <g class="wx-moon-stars">
                        <path class="wx-star-1" d="M 22,22 Q 22,24 24,24 Q 22,24 22,26 Q 22,24 20,24 Q 22,24 22,22 Z" fill="#fff" />
                        <path class="wx-star-2" d="M 76,20 Q 76,22 78,22 Q 76,22 76,24 Q 76,22 74,22 Q 76,22 76,20 Z" fill="#fff" />
                    </g>
                    <g class="wx-pn-moon" transform="translate(14, 10) scale(0.72)">
                        <path d="M 62,28 A 24,24 0 1,0 62,68 A 20,20 0 1,1 62,28 Z" fill="url(#wx-pn-moon-grad)" />
                        <path d="M 62,28 A 24,24 0 0,0 42,48 A 20,20 0 0,1 62,28 Z" fill="#ffffff" opacity="0.2" />
                    </g>
                    <path d="M 28,70 A 14,14 0 0,1 40,48 A 18,18 0 0,1 70,50 A 13,13 0 0,1 80,70 Z" fill="#0f172a" opacity="0.18" transform="translate(0, 3)" />
                    <path d="M 28,70 A 14,14 0 0,1 40,48 A 18,18 0 0,1 70,50 A 13,13 0 0,1 80,70 Z" fill="url(#wx-pn-cloud-grad)" stroke="rgba(255, 255, 255, 0.9)" stroke-width="1" class="wx-cloud-front" />
                </svg>`;
            }
        } else if (key === 'clouds') {
            type = 'clouds';
            body = `<svg class="wx-svg-icon wx-svg-clouds" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="wx-c-front-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="55%" stop-color="#f1f5f9" />
                        <stop offset="100%" stop-color="#cbd5e1" />
                    </linearGradient>
                    <linearGradient id="wx-c-back-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#cbd5e1" />
                        <stop offset="60%" stop-color="#94a3b8" />
                        <stop offset="100%" stop-color="#64748b" />
                    </linearGradient>
                </defs>
                <g class="wx-cloud-back" transform="translate(-8, -10) scale(0.95)">
                    <path d="M 28,70 A 14,14 0 0,1 40,48 A 18,18 0 0,1 70,50 A 13,13 0 0,1 80,70 Z" fill="#0f172a" opacity="0.12" transform="translate(0, 2)" />
                    <path d="M 28,70 A 14,14 0 0,1 40,48 A 18,18 0 0,1 70,50 A 13,13 0 0,1 80,70 Z" fill="url(#wx-c-back-grad)" stroke="rgba(255, 255, 255, 0.7)" stroke-width="0.8" />
                </g>
                <g class="wx-cloud-front-group">
                    <path d="M 28,70 A 14,14 0 0,1 40,48 A 18,18 0 0,1 70,50 A 13,13 0 0,1 80,70 Z" fill="#0f172a" opacity="0.15" transform="translate(4, 5)" />
                    <path d="M 32,72 A 14,14 0 0,1 44,50 A 18,18 0 0,1 74,52 A 13,13 0 0,1 84,72 Z" fill="url(#wx-c-front-grad)" stroke="rgba(255, 255, 255, 0.9)" stroke-width="1" />
                </g>
            </svg>`;
        } else if (key === 'fog') {
            type = 'fog';
            body = `<svg class="wx-svg-icon wx-svg-fog" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="wx-fog-cloud-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="60%" stop-color="#e2e8f0" />
                        <stop offset="100%" stop-color="#cbd5e1" />
                    </linearGradient>
                    <linearGradient id="wx-fog-line-grad" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#cbd5e1" stop-opacity="0" />
                        <stop offset="25%" stop-color="#cbd5e1" stop-opacity="0.9" />
                        <stop offset="75%" stop-color="#cbd5e1" stop-opacity="0.9" />
                        <stop offset="100%" stop-color="#cbd5e1" stop-opacity="0" />
                    </linearGradient>
                    <linearGradient id="wx-fog-line-grad-2" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#94a3b8" stop-opacity="0" />
                        <stop offset="20%" stop-color="#e2e8f0" stop-opacity="0.95" />
                        <stop offset="80%" stop-color="#e2e8f0" stop-opacity="0.95" />
                        <stop offset="100%" stop-color="#94a3b8" stop-opacity="0" />
                    </linearGradient>
                </defs>
                <path d="M 28,64 A 14,14 0 0,1 40,42 A 18,18 0 0,1 70,44 A 13,13 0 0,1 80,64 Z" fill="#0f172a" opacity="0.1" transform="translate(0, 2)" />
                <path d="M 28,64 A 14,14 0 0,1 40,42 A 18,18 0 0,1 70,44 A 13,13 0 0,1 80,64 Z" fill="url(#wx-fog-cloud-grad)" stroke="rgba(255, 255, 255, 0.9)" stroke-width="1" class="wx-cloud-front" />
                <g class="wx-fog-lines">
                    <rect class="wx-fog-line-1" x="15" y="58" width="70" height="4.5" rx="2.25" fill="url(#wx-fog-line-grad)" />
                    <rect class="wx-fog-line-2" x="10" y="66" width="80" height="4.5" rx="2.25" fill="url(#wx-fog-line-grad-2)" />
                    <rect class="wx-fog-line-3" x="20" y="74" width="60" height="4" rx="2" fill="url(#wx-fog-line-grad)" />
                </g>
            </svg>`;
        } else if (key === 'drizzle') {
            type = 'drizzle';
            body = `<svg class="wx-svg-icon wx-svg-drizzle" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="wx-drizzle-cloud-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="55%" stop-color="#cbd5e1" />
                        <stop offset="100%" stop-color="#94a3b8" />
                    </linearGradient>
                    <linearGradient id="wx-drizzle-drop-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#60a5fa" />
                        <stop offset="100%" stop-color="#2563eb" />
                    </linearGradient>
                </defs>
                <path d="M 28,60 A 14,14 0 0,1 40,38 A 18,18 0 0,1 70,40 A 13,13 0 0,1 80,60 Z" fill="#0f172a" opacity="0.1" transform="translate(0, 2)" />
                <path d="M 28,60 A 14,14 0 0,1 40,38 A 18,18 0 0,1 70,40 A 13,13 0 0,1 80,60 Z" fill="url(#wx-drizzle-cloud-grad)" stroke="rgba(255, 255, 255, 0.9)" stroke-width="1" class="wx-cloud-front" />
                <g class="wx-rain-drops">
                    <rect class="wx-drop-1" x="38" y="66" width="3" height="9" rx="1.5" fill="url(#wx-drizzle-drop-grad)" />
                    <rect class="wx-drop-2" x="50" y="70" width="3" height="9" rx="1.5" fill="url(#wx-drizzle-drop-grad)" />
                    <rect class="wx-drop-3" x="62" y="65" width="3" height="9" rx="1.5" fill="url(#wx-drizzle-drop-grad)" />
                </g>
            </svg>`;
        } else if (key === 'rain') {
            type = 'rain';
            body = `<svg class="wx-svg-icon wx-svg-rain" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="wx-rain-cloud-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#f1f5f9" />
                        <stop offset="55%" stop-color="#94a3b8" />
                        <stop offset="100%" stop-color="#475569" />
                    </linearGradient>
                    <linearGradient id="wx-heavy-drop-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="100%" stop-color="#1d4ed8" />
                    </linearGradient>
                </defs>
                <path d="M 28,58 A 14,14 0 0,1 40,36 A 18,18 0 0,1 70,38 A 13,13 0 0,1 80,58 Z" fill="#0f172a" opacity="0.15" transform="translate(0, 3)" />
                <path d="M 28,58 A 14,14 0 0,1 40,36 A 18,18 0 0,1 70,38 A 13,13 0 0,1 80,58 Z" fill="url(#wx-rain-cloud-grad)" stroke="rgba(255, 255, 255, 0.85)" stroke-width="1" class="wx-cloud-front" />
                <g class="wx-rain-drops">
                    <rect class="wx-drop-1" x="35" y="64" width="3.5" height="11" rx="1.75" fill="url(#wx-heavy-drop-grad)" />
                    <rect class="wx-drop-2" x="46" y="68" width="3.5" height="11" rx="1.75" fill="url(#wx-heavy-drop-grad)" />
                    <rect class="wx-drop-3" x="57" y="65" width="3.5" height="11" rx="1.75" fill="url(#wx-heavy-drop-grad)" />
                    <rect class="wx-drop-4" x="68" y="67" width="3.5" height="11" rx="1.75" fill="url(#wx-heavy-drop-grad)" />
                    <rect class="wx-drop-5" x="42" y="74" width="3.5" height="11" rx="1.75" fill="url(#wx-heavy-drop-grad)" />
                </g>
            </svg>`;
        } else if (key === 'snow') {
            type = 'snow';
            body = `<svg class="wx-svg-icon wx-svg-snow" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="wx-snow-cloud-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="60%" stop-color="#e2e8f0" />
                        <stop offset="100%" stop-color="#b8c5d6" />
                    </linearGradient>
                    <radialGradient id="wx-snowflake-glow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="100%" stop-color="#e2e8f0" stop-opacity="0" />
                    </radialGradient>
                </defs>
                <path d="M 28,58 A 14,14 0 0,1 40,36 A 18,18 0 0,1 70,38 A 13,13 0 0,1 80,58 Z" fill="#0f172a" opacity="0.1" transform="translate(0, 2)" />
                <path d="M 28,58 A 14,14 0 0,1 40,36 A 18,18 0 0,1 70,38 A 13,13 0 0,1 80,58 Z" fill="url(#wx-snow-cloud-grad)" stroke="rgba(255, 255, 255, 0.9)" stroke-width="1" class="wx-cloud-front" />
                <g class="wx-snowflakes">
                    <g class="wx-snowflake-1">
                        <circle cx="36" cy="66" r="8" fill="url(#wx-snowflake-glow)" opacity="0.3" />
                        <path d="M36 60 v12 M30 66 h12 M31.8 61.8 l8.4 8.4 M31.8 70.2 l8.4 -8.4" stroke="#ffffff" stroke-width="1.6" stroke-linecap="round" />
                    </g>
                    <g class="wx-snowflake-2">
                        <circle cx="50" cy="72" r="7" fill="url(#wx-snowflake-glow)" opacity="0.3" />
                        <path d="M50 67 v10 M45 72 h10 M46.5 68.5 l7 7 M46.5 75.5 l7 -7" stroke="#ffffff" stroke-width="1.4" stroke-linecap="round" />
                    </g>
                    <g class="wx-snowflake-3">
                        <circle cx="64" cy="64" r="8" fill="url(#wx-snowflake-glow)" opacity="0.3" />
                        <path d="M64 58 v12 M58 64 h12 M59.8 59.8 l8.4 8.4 M59.8 68.2 l8.4 -8.4" stroke="#ffffff" stroke-width="1.6" stroke-linecap="round" />
                    </g>
                </g>
            </svg>`;
        } else if (key === 'thunder') {
            type = 'thunder';
            body = `<svg class="wx-svg-icon wx-svg-thunder" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="wx-thunder-cloud-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#475569" />
                        <stop offset="60%" stop-color="#334155" />
                        <stop offset="100%" stop-color="#1e293b" />
                    </linearGradient>
                    <linearGradient id="wx-lightning-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#fffde7" />
                        <stop offset="30%" stop-color="#ffd54f" />
                        <stop offset="100%" stop-color="#ff8f00" />
                    </linearGradient>
                    <filter id="wx-lightning-glow" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="3" result="blur" />
                        <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>
                </defs>
                <path d="M 28,56 A 14,14 0 0,1 40,34 A 18,18 0 0,1 70,36 A 13,13 0 0,1 80,56 Z" fill="#020617" opacity="0.3" transform="translate(0, 3)" />
                <path d="M 28,56 A 14,14 0 0,1 40,34 A 18,18 0 0,1 70,36 A 13,13 0 0,1 80,56 Z" fill="url(#wx-thunder-cloud-grad)" stroke="rgba(255, 255, 255, 0.7)" stroke-width="0.8" class="wx-cloud-front" />
                <g class="wx-rain-drops">
                    <rect class="wx-drop-1" x="35" y="62" width="3" height="10" rx="1.5" fill="#3b82f6" />
                    <rect class="wx-drop-2" x="65" y="62" width="3" height="10" rx="1.5" fill="#3b82f6" />
                </g>
                <polygon class="wx-lightning-bolt" points="52,48 40,68 48,68 44,90 60,60 50,60" fill="url(#wx-lightning-grad)" filter="url(#wx-lightning-glow)" />
            </svg>`;
        }
        return `<span class="wx-icon wx-icon--${type}" style="--wx-size:${size}px">${body}</span>`;
    }

    function uvMeterHTML(uv) {
        const info = uvInfo(uv);
        return `<div class="wx-uv" title="UV Index ${info.value} — ${info.label}">
            <div class="wx-uv-head"><span class="wx-uv-label"><i class="bi bi-sun"></i> UV Index</span>`
            + `<span class="wx-uv-val" style="color:${info.color}">${info.value} · ${info.label}</span></div>`
            + `<div class="wx-uv-track"><span class="wx-uv-fill" style="width:${info.pct}%;background:${info.color}"></span></div></div>`;
    }

    function advisoryHTML(d) {
        const a = advisory(d);
        return `<div class="wx-advisory wx-advisory--${a.level}">`
            + `<span class="wx-advisory-ic"><i class="bi ${a.icon}"></i></span>`
            + `<span class="wx-advisory-tx"><strong>${a.title}.</strong> ${a.text}</span></div>`;
    }

    window.WeatherFx = {
        normCondition, isDay, computePollen, computeDryEye, uvInfo, advisory,
        sceneHTML, iconHTML, uvMeterHTML, advisoryHTML
    };
})();
