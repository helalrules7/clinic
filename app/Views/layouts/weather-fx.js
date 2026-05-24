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
        if (c.includes('partly') || c.includes('scattered') || c.includes('few') || (c.includes('mainly') && c.includes('clear'))) return 'partly';
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

    // --- small markup helpers (randomised particles) ---
    function rnd(min, max) { return (Math.random() * (max - min) + min); }

    function stars(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const x = rnd(4, 96).toFixed(1), y = rnd(6, 78).toFixed(1);
            const s = rnd(1.2, 2.6).toFixed(1), dl = rnd(0, 3).toFixed(2), du = rnd(1.6, 3.2).toFixed(2);
            h += `<span class="wx-star" style="left:${x}%;top:${y}%;width:${s}px;height:${s}px;animation-delay:${dl}s;animation-duration:${du}s"></span>`;
        }
        return h;
    }
    function clouds(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const top = rnd(8, 52).toFixed(0), scale = rnd(0.7, 1.25).toFixed(2);
            const dur = rnd(26, 46).toFixed(0), delay = (-rnd(0, 30)).toFixed(0), op = rnd(0.45, 0.85).toFixed(2);
            h += `<span class="wx-cloud" style="top:${top}%;transform:scale(${scale});animation-duration:${dur}s;animation-delay:${delay}s;opacity:${op}"></span>`;
        }
        return h;
    }
    function rain(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const x = rnd(2, 98).toFixed(1), dl = rnd(0, 1.2).toFixed(2), du = rnd(0.6, 1.1).toFixed(2), op = rnd(0.35, 0.75).toFixed(2);
            h += `<span class="wx-drop" style="left:${x}%;animation-delay:${dl}s;animation-duration:${du}s;opacity:${op}"></span>`;
        }
        return h;
    }
    function snow(n) {
        let h = '';
        for (let i = 0; i < n; i++) {
            const x = rnd(2, 98).toFixed(1), dl = rnd(0, 3).toFixed(2), du = rnd(4, 8).toFixed(2), s = rnd(3, 6).toFixed(1);
            h += `<span class="wx-flake" style="left:${x}%;width:${s}px;height:${s}px;animation-delay:${dl}s;animation-duration:${du}s"></span>`;
        }
        return h;
    }

    function sceneHTML(d) {
        const key = normCondition(d && d.condition);
        const day = isDay(d);
        let inner = '';
        if (key === 'clear') {
            inner = day ? ('<span class="wx-sun-glow"></span>') : ('<span class="wx-moon-glow"></span>' + stars(20));
        } else if (key === 'partly') {
            inner = (day ? '<span class="wx-sun-glow wx-sun-glow--corner"></span>' : '<span class="wx-moon-glow wx-moon-glow--corner"></span>' + stars(10)) + clouds(2);
        } else if (key === 'clouds') {
            inner = clouds(3);
        } else if (key === 'fog') {
            inner = clouds(2) + '<span class="wx-fog"></span><span class="wx-fog wx-fog--2"></span>';
        } else if (key === 'rain') {
            inner = clouds(2) + rain(16);
        } else if (key === 'drizzle') {
            inner = clouds(2) + rain(9);
        } else if (key === 'snow') {
            inner = clouds(2) + snow(16);
        } else if (key === 'thunder') {
            inner = clouds(2) + rain(13) + '<span class="wx-flash"></span>';
        }
        return `<div class="wx-scene wx-scene--${key} ${day ? 'wx--day' : 'wx--night'}">${inner}</div>`;
    }

    // --- Animated CSS weather icon ---
    function iconHTML(d, sizePx) {
        const key = normCondition(d && d.condition);
        const day = isDay(d);
        const size = sizePx || 56;
        let type, body = '';
        if (key === 'clear') {
            if (day) { type = 'sun'; body = '<span class="wxi-sun"><span class="wxi-rays"></span><span class="wxi-disc"></span></span>'; }
            else { type = 'moon'; body = '<span class="wxi-moon"></span><span class="wxi-twinkle wxi-twinkle--1"></span><span class="wxi-twinkle wxi-twinkle--2"></span>'; }
        } else if (key === 'partly') {
            type = day ? 'partly-day' : 'partly-night';
            body = (day ? '<span class="wxi-sun wxi-sun--sm"><span class="wxi-rays"></span><span class="wxi-disc"></span></span>'
                        : '<span class="wxi-moon wxi-moon--sm"></span>')
                 + '<span class="wxi-cloud"></span>';
        } else if (key === 'clouds' || key === 'fog') {
            type = key; body = '<span class="wxi-cloud"></span>' + (key === 'clouds' ? '<span class="wxi-cloud wxi-cloud--back"></span>' : '<span class="wxi-foglines"></span>');
        } else if (key === 'rain' || key === 'drizzle') {
            type = key; body = '<span class="wxi-cloud"></span><span class="wxi-rainline wxi-rainline--1"></span><span class="wxi-rainline wxi-rainline--2"></span><span class="wxi-rainline wxi-rainline--3"></span>';
        } else if (key === 'snow') {
            type = 'snow'; body = '<span class="wxi-cloud"></span><span class="wxi-snowdot wxi-snowdot--1"></span><span class="wxi-snowdot wxi-snowdot--2"></span><span class="wxi-snowdot wxi-snowdot--3"></span>';
        } else if (key === 'thunder') {
            type = 'thunder'; body = '<span class="wxi-cloud"></span><span class="wxi-bolt"></span>';
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
