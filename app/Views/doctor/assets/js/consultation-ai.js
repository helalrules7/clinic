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

    function init() {
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
        initICD10();
        initChips();
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
