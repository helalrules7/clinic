/* =====================================================================
   patient-hover.js — v11.0.0

   Patient hover-card behaviour.

   - Single #patientCard element (rendered by layouts/patient-hover-card.php)
     is recycled across every trigger.
   - Delegated mouseenter on [data-patient-id]: 400ms intent debounce, then
     fetch /api/patients/:id/summary and reveal.
   - tagPatientHoverTriggers() + MutationObserver stamp data-patient-id on every
     a[href*="/doctor/patient…"] site-wide; retag() exposed on window.patientHover.
   - 200ms grace timer between trigger ↔ card so the cursor can travel.
   - Touch: 600ms long-press on the trigger pops the card centered as a
     small dialog; tap outside dismisses.
   - Smart positioning: prefer right of trigger + 8px, flip horizontally and/
     or vertically when overflowing the viewport.
   - Avatar colour is set from window.patientColor / patientColorFg / Muted
     via CSS custom props; initials come from window.patientInitials.
   - Summary responses are cached for 60s to avoid hammering the API on
     repeated hovers of the same patient.

   Public API (window.patientHover):
       open(triggerEl, patientId)   — programmatic open
       close()                      — close now
       refresh(patientId?)          — bust cache for an id (or all) and
                                       re-fetch if currently shown
   ===================================================================== */
(function () {
    'use strict';

    // ---------------------------------------------------------------- config
    var HOVER_DELAY      = 400;   // ms — intent debounce before fetch
    var CLOSE_DELAY      = 200;   // ms — grace between trigger ↔ card
    var LONG_PRESS_DELAY = 600;   // ms — touch long-press
    var GUTTER           = 8;     // px — viewport / trigger gap
    var CACHE_TTL        = 60000; // ms — patient summary cache lifetime
    var REQUEST_TIMEOUT  = 8000;  // ms — fetch abort threshold
    var MOBILE_BP        = 575.98;

    var I18N = {
        lastVisit:       { en: 'Last visit', ar: 'آخر زيارة' },
        nextAppt:        { en: 'Next appointment', ar: 'الموعد القادم' },
        activeAlerts:    { en: 'Active alerts', ar: 'تنبيهات نشطة' },
        openProfile:     { en: 'Open profile', ar: 'فتح الملف' },
        unknownPatient:  { en: 'Unknown patient', ar: 'مريض غير معروف' },
        noPrevVisits:    { en: 'No previous visits', ar: 'لا زيارات سابقة' },
        noneScheduled:   { en: 'None scheduled', ar: 'لا موعد مجدول' },
        alert:           { en: 'alert', ar: 'تنبيه' },
        alerts:          { en: 'alerts', ar: 'تنبيهات' },
        loadError:       { en: 'Couldn’t load patient summary.', ar: 'تعذّر تحميل ملخص المريض.' },
        loading:         { en: 'Loading patient summary…', ar: 'جاري تحميل ملخص المريض…' },
        yrs:             { en: 'yrs', ar: 'سنة' }
    };

    function isArabicUI() {
        var html = document.documentElement;
        var lang = (html.getAttribute('lang') || '').toLowerCase();
        return lang.indexOf('ar') === 0 || html.getAttribute('dir') === 'rtl';
    }

    function uiLocale() {
        return isArabicUI() ? 'ar-EG' : undefined;
    }

    function t(key) {
        var row = I18N[key];
        if (!row) return key;
        return isArabicUI() ? row.ar : row.en;
    }

    function patientProfilePath(id) {
        var layout = document.documentElement.getAttribute('data-layout');
        if (layout === 'secretary') return '/secretary/patients/' + encodeURIComponent(id);
        return '/doctor/patients/' + encodeURIComponent(id);
    }

    function translateGender(g) {
        if (!g) return '';
        var s = String(g).trim().toLowerCase();
        if (!isArabicUI()) {
            return s.charAt(0).toUpperCase() + s.slice(1);
        }
        if (s === 'male' || s === 'm') return 'ذكر';
        if (s === 'female' || s === 'f') return 'أنثى';
        return g;
    }

    function applyLocaleLabels() {
        if (!card) return;
        var map = [
            ['[data-pc-row="last-visit"] .pc-stat-label span:last-child', 'lastVisit'],
            ['[data-pc-row="next-appt"] .pc-stat-label span:last-child', 'nextAppt'],
            ['[data-pc-row="alerts"] .pc-stat-label span:last-child', 'activeAlerts'],
            ['[data-pc-link] span:first-child', 'openProfile']
        ];
        map.forEach(function (pair) {
            var el = card.querySelector(pair[0]);
            if (el) el.textContent = t(pair[1]);
        });
        var skelHidden = card.querySelector('.pc-skeleton .visually-hidden');
        if (skelHidden) skelHidden.textContent = t('loading');
        var errMsg = card.querySelector('[data-pc-error-msg]');
        if (errMsg && !errMsg.dataset.userMsg) errMsg.textContent = t('loadError');
        var ctaIcon = card.querySelector('[data-pc-link] i');
        if (ctaIcon) {
            ctaIcon.classList.toggle('bi-arrow-right-short', !isArabicUI());
            ctaIcon.classList.toggle('bi-arrow-left-short', isArabicUI());
        }
    }

    // ---------------------------------------------------------------- state
    var card           = null;   // <aside id="patientCard">
    var bodyEl         = null;
    var skelEl         = null;
    var errorEl        = null;
    var currentTrigger = null;   // element that opened the card
    var currentId      = null;   // patient id currently shown / fetching
    var openTimer      = null;
    var closeTimer     = null;
    var pressTimer     = null;
    var activeFetch    = null;   // AbortController for in-flight request
    var cache          = Object.create(null);
    var isTouchOpen    = false;  // true when card was opened via long-press
    var pressStartXY   = null;   // {x,y} to cancel long-press on scroll

    // ---------------------------------------------------------------- utils
    function isMobile() {
        // Use matchMedia so it tracks viewport changes (rotation, devtools).
        return window.matchMedia('(max-width: ' + MOBILE_BP + 'px)').matches;
    }

    function clearTimer(t) {
        if (t) clearTimeout(t);
        return null;
    }

    function findTrigger(target) {
        if (!target || target.nodeType !== 1) return null;
        // closest() handles nested elements (e.g. icon inside the trigger).
        var t = target.closest('[data-patient-id]');
        if (!t) return null;
        // Opt-out hook for rows where we explicitly don't want a hover-card.
        if (t.hasAttribute('data-no-hover')) return null;
        var id = t.getAttribute('data-patient-id');
        if (!id || id === '0') return null;
        return t;
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function fmtDate(value) {
        if (!value) return null;
        var d = (value instanceof Date) ? value : new Date(value);
        if (isNaN(d.getTime())) return null;
        try {
            return d.toLocaleDateString(uiLocale(), { year: 'numeric', month: 'short', day: 'numeric' });
        } catch (e) {
            return d.toDateString();
        }
    }

    function fmtDateTime(value) {
        if (!value) return null;
        var d = (value instanceof Date) ? value : new Date(value);
        if (isNaN(d.getTime())) return null;
        try {
            var loc = uiLocale();
            var datePart = d.toLocaleDateString(loc, {
                year: 'numeric', month: 'short', day: 'numeric'
            });
            var timePart = d.toLocaleTimeString(loc, {
                hour: 'numeric', minute: '2-digit', hour12: !isArabicUI()
            });
            return datePart + ' · ' + timePart;
        } catch (e) {
            return d.toString();
        }
    }

    function relativeFromNow(value) {
        if (!value) return null;
        var d = (value instanceof Date) ? value : new Date(value);
        if (isNaN(d.getTime())) return null;
        var diff = d.getTime() - Date.now();
        var abs  = Math.abs(diff);
        var DAY  = 86400000;
        var HOUR = 3600000;
        var ar = isArabicUI();
        if (abs < HOUR)  return diff < 0 ? (ar ? 'الآن' : 'just now') : (ar ? 'قريباً' : 'soon');
        if (abs < DAY) {
            var hrs = Math.round(abs / HOUR);
            if (ar) return diff < 0 ? ('منذ ' + hrs + ' س') : ('بعد ' + hrs + ' س');
            return hrs + 'h ' + (diff < 0 ? 'ago' : 'from now');
        }
        if (abs < 30 * DAY) {
            var days = Math.round(abs / DAY);
            if (ar) return diff < 0 ? ('منذ ' + days + ' ي') : ('بعد ' + days + ' ي');
            return days + 'd ' + (diff < 0 ? 'ago' : 'from now');
        }
        return null;
    }

    /** API returns last_visit / next_appointment at the JSON root, not inside patient. */
    function visitDateValue(v) {
        if (v == null || v === '') return null;
        if (typeof v === 'string' || typeof v === 'number') return v;
        if (v instanceof Date) return v;
        if (typeof v === 'object') {
            var d = v.date || v.at || v.datetime;
            var t = v.time || v.start_time;
            if (d && t) {
                var ts = String(t);
                if (ts.length <= 8 && ts.indexOf('T') === -1) return d + 'T' + ts;
            }
            return d || null;
        }
        return null;
    }

    function normalizeSummary(json) {
        if (!json || typeof json !== 'object') return {};
        var patient = json.patient || json.data || json;
        var name = patient.name;
        if (!name && (patient.first_name || patient.last_name)) {
            name = [patient.first_name, patient.last_name].filter(Boolean).join(' ');
        }
        return {
            id: patient.id,
            name: name,
            age: patient.age,
            gender: patient.gender,
            phone: patient.phone,
            dob: patient.dob || patient.date_of_birth,
            last_visit: json.last_visit != null ? json.last_visit : (patient.last_visit || patient.lastVisit || patient.last_visit_at),
            next_appointment: json.next_appointment != null ? json.next_appointment : (patient.next_appointment || patient.nextAppointment || patient.next_appt),
            active_alerts_count: json.active_alerts_count != null ? json.active_alerts_count : patient.active_alerts_count,
            active_alerts: json.active_alerts || patient.active_alerts || patient.alerts
        };
    }

    // -------------------------------------------------------------- network
    function fetchSummary(id) {
        // Serve cached results when fresh.
        var hit = cache[id];
        var now = Date.now();
        if (hit && hit.expiresAt > now) {
            return Promise.resolve(hit.data);
        }

        // Cancel any in-flight request before issuing a new one.
        if (activeFetch) {
            try { activeFetch.abort(); } catch (e) {}
            activeFetch = null;
        }

        var controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        activeFetch = controller;

        var timeout = setTimeout(function () {
            if (controller) {
                try { controller.abort(); } catch (e) {}
            }
        }, REQUEST_TIMEOUT);

        return fetch('/api/patients/' + encodeURIComponent(id) + '/summary', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: controller ? controller.signal : undefined
        }).then(function (res) {
            clearTimeout(timeout);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }).then(function (json) {
            var data = normalizeSummary(json);
            cache[id] = { data: data, expiresAt: Date.now() + CACHE_TTL };
            return data;
        }).finally(function () {
            clearTimeout(timeout);
            if (activeFetch === controller) activeFetch = null;
        });
    }

    // ----------------------------------------------------------- rendering
    function setText(selector, value, fallback) {
        var el = card.querySelector(selector);
        if (!el) return;
        el.textContent = (value == null || value === '') ? (fallback || '—') : value;
    }

    function showSkeleton() {
        if (!skelEl || !bodyEl || !errorEl) return;
        skelEl.hidden  = false;
        bodyEl.hidden  = true;
        errorEl.hidden = true;
    }

    function showError(message) {
        if (!skelEl || !bodyEl || !errorEl) return;
        skelEl.hidden  = true;
        bodyEl.hidden  = true;
        errorEl.hidden = false;
        var msg = errorEl.querySelector('[data-pc-error-msg]');
        if (msg && message) msg.textContent = message;
    }

    function showBody() {
        if (!skelEl || !bodyEl || !errorEl) return;
        skelEl.hidden  = true;
        bodyEl.hidden  = false;
        errorEl.hidden = true;
    }

    function paint(id, p) {
        if (!card) return;

        // Avatar: deterministic colour from id, initials from name.
        var color  = (typeof window.patientColor === 'function') ? window.patientColor(id) : null;
        var bg     = (typeof window.patientColorMuted === 'function') ? window.patientColorMuted(id) : null;
        var fg     = (typeof window.patientColorFg === 'function') ? window.patientColorFg(id) : null;
        var inits  = (typeof window.patientInitials === 'function') ? window.patientInitials(p && p.name) : '?';

        var avatar = card.querySelector('[data-pc-avatar]');
        if (avatar) {
            if (color) avatar.style.setProperty('--pt-color', color);
            if (bg)    avatar.style.setProperty('--pt-bg', bg);
            if (fg)    avatar.style.setProperty('--pt-fg', fg);
        }
        var initialsEl = card.querySelector('[data-pc-initials]');
        if (initialsEl) initialsEl.textContent = inits || '?';

        // Identity.
        setText('[data-pc-name]', p && p.name, t('unknownPatient'));

        var ageStr = '';
        var ageUnit = t('yrs');
        if (p && (p.age != null && p.age !== '')) {
            ageStr = p.age + (typeof p.age === 'number' ? ' ' + ageUnit : '');
        } else if (p && p.dob) {
            var dob = new Date(p.dob);
            if (!isNaN(dob.getTime())) {
                var diffMs = Date.now() - dob.getTime();
                var years = Math.floor(diffMs / (365.25 * 86400000));
                if (years >= 0 && years < 130) ageStr = years + ' ' + ageUnit;
            }
        }
        setText('[data-pc-age]', ageStr, '');

        setText('[data-pc-gender]', translateGender(p && p.gender), '');

        // Hide the “·” separator when age or gender is missing.
        var sep = card.querySelector('.pc-meta-sep');
        if (sep) sep.style.display = (ageStr && gender) ? '' : 'none';

        // Phone (optional).
        var phoneEl = card.querySelector('[data-pc-phone]');
        var phoneSep = card.querySelector('.pc-meta-sep-phone');
        if (p && p.phone) {
            if (phoneEl) {
                phoneEl.textContent = p.phone;
                phoneEl.hidden = false;
            }
            if (phoneSep) phoneSep.hidden = !(ageStr || gender);
        } else {
            if (phoneEl) { phoneEl.textContent = ''; phoneEl.hidden = true; }
            if (phoneSep) phoneSep.hidden = true;
        }

        // Last visit row.
        var lastVisitText = '—';
        var lvRaw = p && (p.last_visit || p.lastVisit || p.last_visit_at);
        var lv = visitDateValue(lvRaw);
        if (lv) {
            var lvDate = fmtDate(lv);
            var lvRel  = relativeFromNow(lv);
            lastVisitText = (lvDate || '—') + (lvRel ? ' · ' + lvRel : '');
        } else {
            lastVisitText = t('noPrevVisits');
        }
        setText('[data-pc-last-visit]', lastVisitText);

        // Next appointment row.
        var nextText = '—';
        var nxRaw = p && (p.next_appointment || p.nextAppointment || p.next_appt);
        var nx = visitDateValue(nxRaw);
        if (nx) {
            var nxDate = fmtDateTime(nx);
            var nxRel  = relativeFromNow(nx);
            nextText = (nxDate || '—') + (nxRel ? ' · ' + nxRel : '');
        } else {
            nextText = t('noneScheduled');
        }
        setText('[data-pc-next-appt]', nextText);

        // Alerts chip — hidden when count is 0.
        var alertsRow = card.querySelector('[data-pc-row="alerts"]');
        var alertCount = 0;
        if (p && p.active_alerts_count != null) alertCount = parseInt(p.active_alerts_count, 10) || 0;
        else if (p && p.active_alerts != null && !Array.isArray(p.active_alerts)) alertCount = parseInt(p.active_alerts, 10) || 0;
        else if (p && Array.isArray(p.active_alerts)) alertCount = p.active_alerts.length;
        else if (p && Array.isArray(p.alerts)) alertCount = p.alerts.length;

        if (alertsRow) {
            if (alertCount > 0) {
                alertsRow.hidden = false;
                var countEl = alertsRow.querySelector('[data-pc-alerts-count]');
                var labelEl = alertsRow.querySelector('[data-pc-alerts-label]');
                if (countEl) countEl.textContent = alertCount;
                if (labelEl) labelEl.textContent = alertCount === 1 ? t('alert') : t('alerts');
                var chip = alertsRow.querySelector('.pc-alerts-chip');
                if (chip) chip.classList.add('pc-alerts-chip--pulse');
            } else {
                alertsRow.hidden = true;
            }
        }

        // CTA link.
        var link = card.querySelector('[data-pc-link]');
        if (link) link.setAttribute('href', patientProfilePath(id));

        showBody();
    }

    // ----------------------------------------------------------- positioning
    function position(triggerEl, centered) {
        if (!card) return;

        // Centered mode is used for the long-press dialog on mobile.
        if (centered) {
            card.classList.add('patient-card--centered');
            card.style.top     = '';
            card.style.left    = '';
            card.style.right   = '';
            card.style.bottom  = '';
            return;
        }
        card.classList.remove('patient-card--centered');

        if (!triggerEl) return;

        var trigRect = triggerEl.getBoundingClientRect();
        var cardRect = card.getBoundingClientRect();
        var vw       = window.innerWidth;
        var vh       = window.innerHeight;
        var cw       = cardRect.width  || 280;
        var ch       = cardRect.height || 200;

        // Prefer to the right of the trigger.
        var left = trigRect.right + GUTTER;
        var placement = 'right';
        if (left + cw + GUTTER > vw) {
            // Flip to the left.
            left = trigRect.left - cw - GUTTER;
            placement = 'left';
            if (left < GUTTER) {
                // Neither side fits — pin to the side with more room.
                var spaceRight = vw - trigRect.right;
                var spaceLeft  = trigRect.left;
                if (spaceRight >= spaceLeft) {
                    left = Math.max(GUTTER, vw - cw - GUTTER);
                    placement = 'right';
                } else {
                    left = GUTTER;
                    placement = 'left';
                }
            }
        }

        // Vertically align centred on the trigger, then clamp into viewport.
        var top = trigRect.top + (trigRect.height / 2) - (ch / 2);
        if (top + ch + GUTTER > vh) top = vh - ch - GUTTER;
        if (top < GUTTER) top = GUTTER;

        card.style.left = Math.round(left) + 'px';
        card.style.top  = Math.round(top)  + 'px';
        card.setAttribute('data-placement', placement);
    }

    // ------------------------------------------------------ open / close
    function reveal(triggerEl, centered) {
        if (!card) return;
        card.hidden = false;
        // First paint with no animation class to measure correctly.
        card.classList.remove('patient-card--show');
        // Reset positioning before measuring.
        card.style.left = '-9999px';
        card.style.top  = '-9999px';
        // Two rAFs: one to apply the un-hidden state, one to measure +
        // position before triggering the show animation.
        requestAnimationFrame(function () {
            position(triggerEl, centered);
            requestAnimationFrame(function () {
                card.classList.add('patient-card--show');
            });
        });
    }

    function open(triggerEl, id, opts) {
        if (!card || !id) return;
        opts = opts || {};
        currentTrigger = triggerEl || null;
        currentId      = String(id);
        isTouchOpen    = !!opts.touch;

        showSkeleton();
        reveal(triggerEl, !!opts.centered);

        fetchSummary(currentId).then(function (data) {
            // Bail if the user has since opened a different patient.
            if (currentId !== String(id)) return;
            paint(id, data || {});
            // Re-position after the body height changes.
            position(triggerEl, !!opts.centered);
        }).catch(function (err) {
            if (currentId !== String(id)) return;
            if (err && err.name === 'AbortError') return;
            showError();
            position(triggerEl, !!opts.centered);
        });
    }

    function close(immediate) {
        if (!card || card.hidden) return;
        openTimer  = clearTimer(openTimer);
        closeTimer = clearTimer(closeTimer);
        pressTimer = clearTimer(pressTimer);

        currentTrigger = null;
        currentId      = null;
        isTouchOpen    = false;

        if (activeFetch) {
            try { activeFetch.abort(); } catch (e) {}
            activeFetch = null;
        }

        if (immediate) {
            card.classList.remove('patient-card--show');
            card.classList.remove('patient-card--centered');
            card.hidden = true;
            return;
        }

        card.classList.remove('patient-card--show');
        // Wait for the hide animation, then hide the node.
        var onEnd = function () {
            card.removeEventListener('transitionend', onEnd);
            // Only hide if we haven't been re-opened in the meantime.
            if (!card.classList.contains('patient-card--show')) {
                card.hidden = true;
                card.classList.remove('patient-card--centered');
            }
        };
        card.addEventListener('transitionend', onEnd);
        // Safety fallback for browsers that don't fire transitionend
        // (e.g. when reduced-motion zeros the transition).
        setTimeout(onEnd, 200);
    }

    function scheduleClose() {
        closeTimer = clearTimer(closeTimer);
        closeTimer = setTimeout(function () { close(false); }, CLOSE_DELAY);
    }

    function cancelScheduledClose() {
        closeTimer = clearTimer(closeTimer);
    }

    // ------------------------------------------------------ event handlers
    function onPointerOver(e) {
        if (isMobile()) return; // touch handled separately
        var trigger = findTrigger(e.target);
        if (!trigger) return;

        // If we're already showing this patient, just cancel any close.
        var id = trigger.getAttribute('data-patient-id');
        if (currentId === id && !card.hidden) {
            cancelScheduledClose();
            return;
        }

        openTimer = clearTimer(openTimer);
        openTimer = setTimeout(function () {
            // Trigger may have been removed from DOM while waiting.
            if (!document.body.contains(trigger)) return;
            cancelScheduledClose();
            open(trigger, id);
        }, HOVER_DELAY);
    }

    function onPointerOut(e) {
        if (isMobile()) return;
        var trigger = findTrigger(e.target);
        if (!trigger) return;

        // mouseleave / mouseout fires when moving between child nodes too,
        // so only act when we're actually leaving the trigger.
        var to = e.relatedTarget;
        if (to && (trigger.contains(to) || (card && card.contains(to)))) return;

        openTimer = clearTimer(openTimer);
        if (!card.hidden) scheduleClose();
    }

    function onCardEnter() {
        if (isTouchOpen) return; // centred dialog handles its own dismiss
        cancelScheduledClose();
    }

    function onCardLeave(e) {
        if (isTouchOpen) return;
        var to = e.relatedTarget;
        // If moving back to the trigger, treat as still hovering.
        if (to && currentTrigger && currentTrigger.contains(to)) {
            cancelScheduledClose();
            return;
        }
        scheduleClose();
    }

    // ---- Touch: long-press to open, tap-outside to dismiss ----
    function onTouchStart(e) {
        if (!isMobile()) return;
        if (e.touches && e.touches.length > 1) return;
        var trigger = findTrigger(e.target);
        if (!trigger) return;

        var t = e.touches ? e.touches[0] : e;
        pressStartXY = { x: t.clientX, y: t.clientY };

        pressTimer = clearTimer(pressTimer);
        pressTimer = setTimeout(function () {
            // Suppress the synthetic click that follows the long-press so
            // the user doesn't navigate into the row they were inspecting.
            suppressNextClick = true;
            var id = trigger.getAttribute('data-patient-id');
            open(trigger, id, { touch: true, centered: true });
            // Haptic nudge if supported.
            if (navigator.vibrate) {
                try { navigator.vibrate(10); } catch (err) {}
            }
        }, LONG_PRESS_DELAY);
    }

    function onTouchMove(e) {
        if (!pressTimer || !pressStartXY) return;
        var t = e.touches ? e.touches[0] : e;
        var dx = Math.abs(t.clientX - pressStartXY.x);
        var dy = Math.abs(t.clientY - pressStartXY.y);
        if (dx > 8 || dy > 8) {
            pressTimer = clearTimer(pressTimer);
            pressStartXY = null;
        }
    }

    function onTouchEnd() {
        pressTimer   = clearTimer(pressTimer);
        pressStartXY = null;
    }

    var suppressNextClick = false;
    function onDocumentClick(e) {
        if (suppressNextClick) {
            suppressNextClick = false;
            e.preventDefault();
            e.stopPropagation();
            return;
        }
        if (card && !card.hidden && !card.contains(e.target)) {
            // Tap outside dismisses the card (mainly for the touch dialog).
            var trigger = findTrigger(e.target);
            if (!trigger) close(false);
        }
    }

    function onKeyDown(e) {
        if (e.key === 'Escape' && card && !card.hidden) {
            close(true);
            if (currentTrigger && typeof currentTrigger.focus === 'function') {
                try { currentTrigger.focus(); } catch (err) {}
            }
        }
    }

    function onScrollOrResize() {
        // Keep the card glued to its trigger; close on big scrolls in case
        // the trigger has moved offscreen.
        if (!card || card.hidden) return;
        if (card.classList.contains('patient-card--centered')) return;
        if (currentTrigger && document.body.contains(currentTrigger)) {
            var r = currentTrigger.getBoundingClientRect();
            // If the trigger is fully out of view, close.
            if (r.bottom < 0 || r.top > window.innerHeight || r.right < 0 || r.left > window.innerWidth) {
                close(true);
                return;
            }
            position(currentTrigger, false);
        }
    }

    // ---------------------------------------- universal trigger tagging
    var PATIENT_HREF_RE = /\/(?:doctor|secretary)\/patients?\/(\d+)/i;
    var tagTimer = null;

    function extractPatientIdFromHref(href) {
        if (!href) return null;
        var m = String(href).match(PATIENT_HREF_RE);
        return m ? m[1] : null;
    }

    function shouldSkipTag(el) {
        return !el || el.hasAttribute('data-no-hover') || el.closest('[data-no-hover]');
    }

    /** Stamp data-patient-id on any patient profile link / name hook site-wide. */
    function tagPatientHoverTriggers(root) {
        root = root || document;
        if (!root.querySelectorAll) return;

        root.querySelectorAll('a[href*="/doctor/patient"], a[href*="/secretary/patient"]').forEach(function (a) {
            if (shouldSkipTag(a) || a.hasAttribute('data-patient-id')) return;
            var id = extractPatientIdFromHref(a.getAttribute('href'));
            if (id && id !== '0') a.setAttribute('data-patient-id', id);
        });

        root.querySelectorAll('[data-patient-id]').forEach(function (el) {
            if (shouldSkipTag(el)) return;
            var id = el.getAttribute('data-patient-id');
            if (!id || id === '0') return;
            el.querySelectorAll('.patient-name, .patient-name-link, .appt-name, .patient-hover-name, .card-title span, h6 .patient-name-link').forEach(function (nameEl) {
                if (!nameEl.hasAttribute('data-patient-id')) {
                    nameEl.setAttribute('data-patient-id', id);
                }
            });
        });
    }

    function scheduleTag(root) {
        if (tagTimer) clearTimeout(tagTimer);
        tagTimer = setTimeout(function () {
            tagPatientHoverTriggers(root || document);
            if (typeof window.applyPatientColors === 'function') {
                try { window.applyPatientColors(root || document); } catch (e) {}
            }
        }, 30);
    }

    function observePatientTriggers() {
        if (typeof MutationObserver === 'undefined') return;
        var obs = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].addedNodes && mutations[i].addedNodes.length) {
                    scheduleTag(document);
                    return;
                }
            }
        });
        obs.observe(document.body, { childList: true, subtree: true });
    }

    // ------------------------------------------------------------- bootstrap
    function init() {
        card = document.getElementById('patientCard');
        if (!card) {
            // The view partial wasn't included on this page — silently no-op.
            return;
        }
        bodyEl  = card.querySelector('[data-pc-body]');
        skelEl  = card.querySelector('[data-pc-skeleton]');
        errorEl = card.querySelector('[data-pc-error]');

        applyLocaleLabels();

        // Delegated trigger listeners.
        document.addEventListener('mouseover',  onPointerOver, true);
        document.addEventListener('mouseout',   onPointerOut,  true);
        document.addEventListener('focusin',    onPointerOver, true);
        document.addEventListener('focusout',   onPointerOut,  true);

        // Card-local listeners (keep alive when the cursor moves into it).
        card.addEventListener('mouseenter', onCardEnter);
        card.addEventListener('mouseleave', onCardLeave);

        // Touch — passive to avoid blocking scroll.
        document.addEventListener('touchstart', onTouchStart, { passive: true });
        document.addEventListener('touchmove',  onTouchMove,  { passive: true });
        document.addEventListener('touchend',   onTouchEnd,   { passive: true });
        document.addEventListener('touchcancel',onTouchEnd,   { passive: true });

        // Outside / Esc / scroll.
        document.addEventListener('click',     onDocumentClick, true);
        document.addEventListener('keydown',   onKeyDown);
        window.addEventListener('scroll',      onScrollOrResize, true);
        window.addEventListener('resize',      onScrollOrResize);

        // Close immediately on route changes so the card doesn't linger.
        window.addEventListener('pagehide', function () { close(true); });

        tagPatientHoverTriggers(document);
        observePatientTriggers();

        // Public hooks.
        window.patientHover = {
            open: function (triggerEl, id) {
                if (!id && triggerEl) id = triggerEl.getAttribute && triggerEl.getAttribute('data-patient-id');
                if (id) open(triggerEl, id, { centered: isMobile(), touch: isMobile() });
            },
            close: function () { close(true); },
            refresh: function (id) {
                if (id) {
                    delete cache[String(id)];
                    if (currentId === String(id)) open(currentTrigger, currentId, { touch: isTouchOpen, centered: isTouchOpen });
                } else {
                    cache = Object.create(null);
                }
            },
            retag: function (root) { tagPatientHoverTriggers(root || document); }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
