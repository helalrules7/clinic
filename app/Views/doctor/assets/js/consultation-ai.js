/**
 * Smart Consultation — AI assists (Phase 1)
 *
 * Fully self-contained IIFE. Exposes a single namespace
 * window.ConsultationAI so it can never collide with the globals in
 * edit_consultation.js. Reads its config from an inline
 * window.CONSULTATION_AI = { appointmentId, patientId, csrfToken } block
 * rendered by edit_consultation.php.
 *
 * Safety posture (matches the approved plan):
 *  - Every AI surface carries a persistent amber "AI — review before
 *    saving" badge.
 *  - The prior-visit summary is READ-ONLY (Copy / Dismiss only) — it is
 *    situational awareness, never chart content.
 *  - ICD-10 only ever sets the single #diagnosis_code field, and only on
 *    an explicit click; clinic-history codes are listed and trusted
 *    above AI codes, and every AI code was already regex-validated
 *    server-side before it reached the browser.
 *  - Nothing here auto-commits, auto-submits, or fires on page load.
 */
(function () {
    'use strict';

    var CFG = window.CONSULTATION_AI || {};

    /* ---------------------------------------------------------------
     * Core: fetch wrapper. Normalises transport + app errors into a
     * single { ok, data, error } shape so callers never juggle both.
     * --------------------------------------------------------------- */
    async function aiFetch(url, opts) {
        opts = opts || {};
        var headers = { 'Accept': 'application/json' };
        if (opts.body && !(opts.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }
        if (CFG.csrfToken) {
            headers['X-CSRF-Token'] = CFG.csrfToken;
        }
        try {
            var res = await fetch(url, {
                method: opts.method || 'GET',
                headers: headers,
                credentials: 'same-origin',
                body: opts.body || undefined
            });
            var json = null;
            try { json = await res.json(); } catch (e) { json = null; }
            if (!res.ok || !json || json.ok === false) {
                return {
                    ok: false,
                    data: null,
                    error: (json && json.error) ||
                        ('Request failed (' + res.status + ')')
                };
            }
            return { ok: true, data: json.data, error: '' };
        } catch (e) {
            return { ok: false, data: null, error: 'Network error. Please retry.' };
        }
    }

    /* ---------------------------------------------------------------
     * Core: write text into a form field the same way edit_consultation
     * .js does it (value + input event + textarea auto-resize), so
     * autocomplete / dirty-tracking / validation all stay in sync.
     * Reusable infra — not wired to any free-text writer in Phase 1.
     * --------------------------------------------------------------- */
    function applyToField(fieldId, text, options) {
        options = options || {};
        var field = document.getElementById(fieldId);
        if (!field) { return false; }
        var next = options.append && field.value.trim()
            ? field.value.replace(/\s+$/, '') + '\n' + text
            : text;
        field.value = next;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        if (field.tagName === 'TEXTAREA') {
            field.style.height = 'auto';
            field.style.height = field.scrollHeight + 'px';
        }
        field.focus();
        return true;
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function spinner() { return '<span class="cai-spinner"></span>'; }

    function toast(msg) {
        // Lightweight, dependency-free; the page has no global toast.
        try {
            var n = document.createElement('div');
            n.textContent = msg;
            n.style.cssText =
                'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);' +
                'background:#0f172a;color:#fff;padding:10px 18px;border-radius:8px;' +
                'font-size:0.85rem;z-index:2000;box-shadow:0 6px 20px rgba(0,0,0,.25)';
            document.body.appendChild(n);
            setTimeout(function () { n.remove(); }, 2200);
        } catch (e) { /* no-op */ }
    }

    /* ---------------------------------------------------------------
     * Reusable amber review card (Insert / Append / Copy / Dismiss).
     * Provided as infra per the plan; Phase 1.2 deliberately uses only
     * the read-only variant (no target field => no Insert/Append).
     * --------------------------------------------------------------- */
    function renderReviewCard(mountEl, text, opts) {
        opts = opts || {};
        if (!mountEl) { return; }
        var canWrite = !!opts.targetFieldId;
        var card = document.createElement('div');
        card.className = 'cai-review';
        card.innerHTML =
            '<div class="cai-review-head">' +
                '<span class="cai-badge"><i class="bi bi-stars"></i> ' +
                    'AI — review before saving</span>' +
                '<button type="button" class="btn btn-sm btn-link text-muted ' +
                    'p-0 cai-x">Dismiss</button>' +
            '</div>' +
            '<div class="cai-review-body">' + escapeHtml(text) + '</div>' +
            '<div class="cai-review-actions"></div>';
        var actions = card.querySelector('.cai-review-actions');

        if (canWrite) {
            var ins = document.createElement('button');
            ins.type = 'button';
            ins.className = 'btn btn-sm btn-primary';
            ins.innerHTML = '<i class="bi bi-box-arrow-in-down"></i> Insert';
            ins.onclick = function () {
                applyToField(opts.targetFieldId, text, { append: false });
                toast('Inserted — review before saving.');
            };
            var app = document.createElement('button');
            app.type = 'button';
            app.className = 'btn btn-sm btn-outline-primary';
            app.innerHTML = '<i class="bi bi-plus-lg"></i> Append';
            app.onclick = function () {
                applyToField(opts.targetFieldId, text, { append: true });
                toast('Appended — review before saving.');
            };
            actions.appendChild(ins);
            actions.appendChild(app);
        }

        var copy = document.createElement('button');
        copy.type = 'button';
        copy.className = 'btn btn-sm btn-outline-secondary';
        copy.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
        copy.onclick = function () {
            navigator.clipboard &&
                navigator.clipboard.writeText(text).then(function () {
                    toast('Copied to clipboard.');
                });
        };
        actions.appendChild(copy);

        card.querySelector('.cai-x').onclick = function () { card.remove(); };
        mountEl.innerHTML = '';
        mountEl.appendChild(card);
    }

    /* ---------------------------------------------------------------
     * Feature 1.2 — lazy prior-visit clinical summary (read-only).
     * --------------------------------------------------------------- */
    function initPriorSummary() {
        var btn = document.getElementById('caiSummarizeBtn');
        var out = document.getElementById('caiSummaryOutput');
        if (!btn || !out || !CFG.appointmentId) { return; }

        btn.addEventListener('click', async function () {
            btn.disabled = true;
            var original = btn.innerHTML;
            btn.innerHTML = spinner() + ' Summarizing…';
            out.textContent = '';
            var r = await aiFetch(
                '/api/consultation/prior-summary?appointment_id=' +
                encodeURIComponent(CFG.appointmentId)
            );
            btn.disabled = false;
            btn.innerHTML = original;
            if (!r.ok) {
                out.innerHTML =
                    '<span class="text-danger">' + escapeHtml(r.error) +
                    '</span>';
                return;
            }
            out.textContent = (r.data && r.data.summary) || 'No summary returned.';
            btn.innerHTML =
                '<i class="bi bi-arrow-clockwise"></i> Regenerate';
        });
    }

    /* ---------------------------------------------------------------
     * Feature 1.3 — Diagnosis → ICD-10 suggestion popover.
     * Clicking a row sets ONLY #diagnosis_code.
     * --------------------------------------------------------------- */
    function initICD10() {
        var btn = document.getElementById('caiIcd10Btn');
        var pop = document.getElementById('caiIcd10Popover');
        var dxCode = document.getElementById('diagnosis_code');
        var dx = document.getElementById('diagnosis');
        var cc = document.getElementById('chief_complaint');
        if (!btn || !pop || !dxCode || !dx) { return; }

        function close() { pop.classList.remove('show'); }
        document.addEventListener('click', function (e) {
            if (!pop.contains(e.target) && e.target !== btn &&
                !btn.contains(e.target)) {
                close();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { close(); }
        });

        function row(code, label, metaText, metaClass) {
            var el = document.createElement('div');
            el.className = 'cai-icd-item';
            el.setAttribute('role', 'button');
            el.tabIndex = 0;
            el.innerHTML =
                '<span class="cai-icd-code">' + escapeHtml(code) + '</span>' +
                '<span class="cai-icd-label">' + escapeHtml(label || '') +
                    '</span>' +
                '<span class="cai-icd-meta ' + metaClass + '">' +
                    escapeHtml(metaText) + '</span>';
            function pick() {
                dxCode.value = code;
                dxCode.dispatchEvent(new Event('input', { bubbles: true }));
                dxCode.dispatchEvent(new Event('change', { bubbles: true }));
                close();
                dxCode.focus();
                toast('ICD-10 set to ' + code + ' — review before saving.');
            }
            el.addEventListener('click', pick);
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    pick();
                }
            });
            return el;
        }

        btn.addEventListener('click', async function () {
            var diagnosis = (dx.value || '').trim();
            if (!diagnosis) {
                pop.innerHTML =
                    '<div class="cai-icd-error">Enter a diagnosis first.</div>';
                pop.classList.add('show');
                return;
            }
            btn.disabled = true;
            var original = btn.innerHTML;
            btn.innerHTML = spinner();
            pop.innerHTML =
                '<div class="cai-icd-empty">' + spinner() +
                ' Finding codes…</div>';
            pop.classList.add('show');

            var r = await aiFetch('/api/consultation/icd10-suggest', {
                method: 'POST',
                body: JSON.stringify({
                    diagnosis: diagnosis,
                    complaint: cc ? (cc.value || '').trim() : ''
                })
            });

            btn.disabled = false;
            btn.innerHTML = original;

            if (!r.ok) {
                pop.innerHTML =
                    '<div class="cai-icd-error">' + escapeHtml(r.error) +
                    '</div>';
                return;
            }

            var d = r.data || {};
            var hist = d.from_history || [];
            var ai = d.ai || [];
            pop.innerHTML = '';

            if (!hist.length && !ai.length) {
                var msg = d.ai_error
                    ? 'No clinic-history match, and AI is unavailable (' +
                      escapeHtml(d.ai_error) + ').'
                    : 'No ICD-10 suggestions found.';
                pop.innerHTML = '<div class="cai-icd-empty">' + msg +
                    '</div>';
                return;
            }

            if (hist.length) {
                var hl = document.createElement('div');
                hl.className = 'cai-icd-group-label';
                hl.textContent = 'Used in this clinic';
                pop.appendChild(hl);
                hist.forEach(function (h) {
                    pop.appendChild(row(
                        h.code, '',
                        'used ' + h.count + '×',
                        'cai-icd-meta--history'
                    ));
                });
            }

            if (ai.length) {
                var al = document.createElement('div');
                al.className = 'cai-icd-group-label';
                al.textContent = 'AI suggestions — verify before saving';
                pop.appendChild(al);
                ai.forEach(function (a) {
                    var conf = (a.confidence != null)
                        ? 'AI ' + Math.round(a.confidence * 100) + '%'
                        : 'AI';
                    pop.appendChild(row(
                        a.code, a.label, conf, 'cai-icd-meta--ai'
                    ));
                });
            }
        });
    }

    /* ---------------------------------------------------------------
     * Feature 1.1 — preset chips that drive the floating chat widget.
     * --------------------------------------------------------------- */
    function initChips() {
        var chips = document.querySelectorAll('.cai-chip[data-cai-prompt]');
        if (!chips.length) { return; }
        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                var prompt = chip.getAttribute('data-cai-prompt');
                var ctx = chip.getAttribute('data-cai-context') || 'general';
                if (typeof window.initAIChatWidget !== 'function' ||
                    typeof window.sendAIChatMessage !== 'function') {
                    toast('AI assistant is still loading…');
                    return;
                }
                // Open the widget window if it is currently closed.
                var win = document.getElementById('aiChatWindow');
                if (win && win.style.display === 'none' &&
                    typeof window.toggleAIChatWidget === 'function') {
                    window.toggleAIChatWidget();
                }
                window.sendAIChatMessage(prompt, ctx);
            });
        });
    }

    // Doctor Auto Complete preferences (Settings → Auto Complete). Both
    // default ON when the config is absent (e.g. older cached page).
    var AC = (CFG && CFG.autocomplete) || {};
    var acConsultation = AC.consultation !== false;
    var acIcd10 = AC.icd10 !== false;

    /* ---------------------------------------------------------------
     * Feature — Chief Complaint + Diagnosis history typeahead.
     * Suggests previously-recorded values (from /api/consultation/
     * suggestions) as the doctor types. Gated by the "Consultation
     * suggestions" Auto-Complete switch (only called from the
     * acConsultation branch of init()).
     * --------------------------------------------------------------- */
    function initFieldSuggestions() {
        wireFieldSuggest('chief_complaint');
        wireFieldSuggest('diagnosis');
    }

    function wireFieldSuggest(field) {
        var el = document.getElementById(field);
        if (!el || el.dataset.caiSuggest) { return; }
        el.dataset.caiSuggest = '1';
        el.setAttribute('autocomplete', 'off');

        var menu = document.createElement('div');
        menu.className = 'cai-suggest-menu';
        menu.hidden = true;
        document.body.appendChild(menu);

        var items = [], active = -1, deb = null, lastQ = '';

        function hide() { menu.hidden = true; items = []; active = -1; }
        function position() {
            var r = el.getBoundingClientRect();
            menu.style.left = r.left + 'px';
            menu.style.width = r.width + 'px';
            var below = window.innerHeight - r.bottom;
            if (below < 200 && r.top > below) {
                menu.style.top = 'auto';
                menu.style.bottom = (window.innerHeight - r.top + 4) + 'px';
                menu.style.maxHeight = Math.min(260, r.top - 12) + 'px';
            } else {
                menu.style.bottom = 'auto';
                menu.style.top = (r.bottom + 4) + 'px';
                menu.style.maxHeight = Math.min(260, below - 12) + 'px';
            }
        }
        function paint() {
            Array.prototype.forEach.call(menu.children, function (c, i) {
                c.classList.toggle('is-active', i === active);
            });
        }
        function render() {
            if (!items.length) { hide(); return; }
            menu.innerHTML = items.map(function (s, i) {
                return '<div class="cai-suggest-item' + (i === 0 ? ' is-active' : '') +
                       '" data-i="' + i + '">' + escapeHtml(s) + '</div>';
            }).join('');
            active = 0; position(); menu.hidden = false;
        }
        function choose(i) {
            var s = items[i]; if (s == null) { return; }
            applyToField(field, s, { append: false });
            hide();
        }

        el.addEventListener('input', function () {
            var q = el.value.trim();
            if (q.length < 3) { hide(); return; }
            if (q === lastQ && !menu.hidden) { return; }
            lastQ = q;
            clearTimeout(deb);
            deb = setTimeout(function () {
                aiFetch('/api/consultation/suggestions?field=' +
                        encodeURIComponent(field) + '&query=' + encodeURIComponent(q))
                    .then(function (r) {
                        if (!r.ok || !r.data) { hide(); return; }
                        // value can equal the query exactly → nothing to add
                        items = r.data.map(function (x) {
                            return (x && x.suggestion != null) ? x.suggestion : x;
                        }).filter(function (s) {
                            return s && s.trim().toLowerCase() !== q.toLowerCase();
                        });
                        render();
                    }).catch(hide);
            }, 200);
        });

        el.addEventListener('keydown', function (e) {
            if (menu.hidden) { return; }
            if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(active + 1, items.length - 1); paint(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(active - 1, 0); paint(); }
            else if (e.key === 'Enter' && active > -1) { e.preventDefault(); choose(active); }
            else if (e.key === 'Escape') { hide(); }
        });
        el.addEventListener('blur', function () { setTimeout(hide, 150); });

        menu.addEventListener('mousedown', function (e) {
            var it = e.target.closest('.cai-suggest-item'); if (!it) { return; }
            e.preventDefault(); choose(parseInt(it.dataset.i, 10));
        });
        window.addEventListener('scroll', function () { if (!menu.hidden) hide(); }, true);
    }

    /* -----------------------------------------------------------------
     * Voice dictation — a mic on the main free-text fields. Records via
     * MediaRecorder, sends the clip to /api/speech/transcribe (Groq
     * Whisper) and appends the returned text into the field. The doctor
     * reviews before saving (nothing auto-commits). The language is FORCED
     * (ar/en, a localStorage toggle) so Whisper never mis-detects a third
     * language.
     * ----------------------------------------------------------------- */
    var MIC_FIELDS = ['chief_complaint', 'hx_present_illness', 'systemic_disease',
                      'medication', 'diagnosis', 'plan'];
    var DICT_LANG_KEY = 'cai_dictation_lang';

    function dictLang() {
        var v = '';
        try { v = localStorage.getItem(DICT_LANG_KEY) || ''; } catch (e) {}
        return v === 'ar' ? 'ar' : 'en';
    }
    function setDictLang(l) {
        try { localStorage.setItem(DICT_LANG_KEY, l); } catch (e) {}
    }

    function micTranscribe(blob) {
        var fd = new FormData();
        fd.append('audio', blob, 'dictation.webm');
        fd.append('language', dictLang());
        var headers = { 'Accept': 'application/json' };
        if (CFG.csrfToken) { headers['X-CSRF-Token'] = CFG.csrfToken; }
        return fetch('/api/speech/transcribe', {
            method: 'POST', headers: headers, credentials: 'same-origin', body: fd
        }).then(function (res) {
            return res.json().catch(function () { return null; }).then(function (json) {
                if (!res.ok || !json || json.ok === false) {
                    throw new Error((json && json.error) ||
                        ('Request failed (' + res.status + ')'));
                }
                return (json.text || '').trim();
            });
        });
    }

    function makeMicButton(fieldId) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cai-mic-btn';
        btn.title = 'Dictate (voice to text)';
        btn.textContent = '🎙';
        btn.style.cssText =
            'position:absolute;bottom:8px;right:8px;width:30px;height:30px;' +
            'border-radius:50%;border:none;cursor:pointer;font-size:15px;line-height:1;' +
            'display:flex;align-items:center;justify-content:center;z-index:5;' +
            'background:rgba(79,70,229,.12);color:#4F46E5';

        var rec = null, chunks = [], recording = false, busy = false;
        function idle() { btn.textContent = '🎙'; btn.style.background = 'rgba(79,70,229,.12)'; }
        function rond() { btn.textContent = '⏹'; btn.style.background = 'rgba(239,68,68,.18)'; }

        btn.addEventListener('click', function () {
            if (busy) { return; }
            if (recording) { try { rec && rec.stop(); } catch (e) {} return; }
            if (!navigator.mediaDevices || !window.MediaRecorder) {
                toast('Voice recording is not supported in this browser.'); return;
            }
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
                var mime = (window.MediaRecorder.isTypeSupported &&
                    MediaRecorder.isTypeSupported('audio/webm')) ? 'audio/webm' : '';
                try { rec = mime ? new MediaRecorder(stream, { mimeType: mime }) : new MediaRecorder(stream); }
                catch (e) { rec = new MediaRecorder(stream); }
                chunks = [];
                rec.ondataavailable = function (e) { if (e.data && e.data.size) { chunks.push(e.data); } };
                rec.onstop = function () {
                    recording = false;
                    stream.getTracks().forEach(function (t) { t.stop(); });
                    var blob = new Blob(chunks, { type: (rec && rec.mimeType) || 'audio/webm' });
                    if (!blob.size) { idle(); return; }
                    busy = true; btn.textContent = '…';
                    micTranscribe(blob).then(function (text) {
                        if (text) { applyToField(fieldId, text, { append: true }); }
                        else { toast('No speech detected.'); }
                    }).catch(function (err) {
                        toast((err && err.message) ? err.message : 'Transcription failed.');
                    }).then(function () { busy = false; idle(); });
                };
                rec.start();
                recording = true; rond();
            }).catch(function () { toast('Microphone permission denied.'); });
        });
        return btn;
    }

    function paintLang(wrap) {
        var cur = dictLang();
        wrap.querySelectorAll('button[data-lang]').forEach(function (b) {
            var on = b.dataset.lang === cur;
            b.style.background = on ? '#4F46E5' : '#e2e8f0';
            b.style.color = on ? '#fff' : '#334155';
        });
    }
    function makeLangToggle() {
        var wrap = document.createElement('div');
        wrap.style.cssText =
            'display:flex;gap:6px;align-items:center;margin:0 0 8px;font-size:.8rem;color:#64748b';
        var lbl = document.createElement('span'); lbl.textContent = '🎙 Dictation:';
        wrap.appendChild(lbl);
        ['en', 'ar'].forEach(function (l) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = (l === 'en') ? 'English' : 'عربي';
            b.dataset.lang = l;
            b.style.cssText = 'border:none;border-radius:6px;padding:3px 10px;cursor:pointer;font-size:.8rem';
            b.addEventListener('click', function () { setDictLang(l); paintLang(wrap); });
            wrap.appendChild(b);
        });
        paintLang(wrap);
        return wrap;
    }

    function initMicDictation() {
        if (!('MediaRecorder' in window)) { return; }
        var firstDone = false;
        MIC_FIELDS.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el || el.dataset.caiMic) { return; }
            el.dataset.caiMic = '1';
            var wrap = document.createElement('div');
            wrap.style.position = 'relative';
            el.parentNode.insertBefore(wrap, el);
            wrap.appendChild(el);
            wrap.appendChild(makeMicButton(id));
            if (!firstDone) {
                firstDone = true;
                wrap.parentNode.insertBefore(makeLangToggle(), wrap);
            }
        });
    }

    function init() {
        initMicDictation();   // voice dictation mic on the free-text fields (always available)
        // Consultation smart-assists (prior-visit summary + quick chips + the
        // floating chat widget) are gated by the "Consultation suggestions"
        // switch. ICD-10 is gated by its own switch.
        if (acConsultation) {
            // The floating chat widget (Feature 1.1). Safe no-op if its
            // script failed to load.
            if (typeof window.initAIChatWidget === 'function') {
                try {
                    window.initAIChatWidget(
                        CFG.patientId || null,
                        CFG.appointmentId || null
                    );
                } catch (e) { /* widget optional */ }
            }
            initPriorSummary();
            initChips();
            initFieldSuggestions();   // Chief Complaint + Diagnosis typeahead
        } else {
            // Switch is OFF — hide the assist UI so nothing dangles.
            hideEl('caiSummarizeBtn');
            hideEl('aiChatWidget');
            document.querySelectorAll('.cai-chip').forEach(function (c) {
                c.style.display = 'none';
            });
        }

        if (acIcd10) {
            initICD10();
        } else {
            hideEl('caiIcd10Btn');
        }
    }

    function hideEl(id) {
        var el = document.getElementById(id);
        if (el) { el.style.display = 'none'; }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Public surface (intentionally small).
    window.ConsultationAI = {
        aiFetch: aiFetch,
        applyToField: applyToField,
        renderReviewCard: renderReviewCard
    };
})();
