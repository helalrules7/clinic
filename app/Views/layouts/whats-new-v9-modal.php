<?php
// "What's New" v9.0.0 — step-by-step wizard, shown once per browser via
// localStorage. Included from layouts/main.php (doctor/admin) and
// secretary_main.php. Bump WIZARD_STORAGE_KEY (see <script> at the bottom)
// in a future release to surface a fresh wizard again.
?>
<style>
    /* ---- shell ----------------------------------------------------------- */
    #whatsNewV9Modal .modal-dialog { max-width: 620px; }
    #whatsNewV9Modal .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #f6f7fb 100%);
    }
    .dark #whatsNewV9Modal .modal-content {
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
        color: #e2e8f0;
    }
    #whatsNewV9Modal .modal-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #0ea5e9 100%);
        color: #fff;
        border-bottom: none;
        padding: 1rem 1.4rem;
    }
    #whatsNewV9Modal .modal-title { font-weight: 700; letter-spacing: .3px; }
    #whatsNewV9Modal .version-pill {
        background: rgba(255,255,255,.18);
        padding: 3px 10px; border-radius: 999px;
        font-size: .72rem; font-weight: 600; margin-left: 8px;
    }
    #whatsNewV9Modal .modal-body { padding: 0; }

    /* ---- wizard track ---------------------------------------------------- */
    .wn-viewport { position: relative; overflow: hidden; }
    .wn-track { display: flex; transition: transform .4s cubic-bezier(.4,0,.2,1); }
    .wn-slide {
        min-width: 100%;
        padding: 1.6rem 1.8rem 1.2rem;
        box-sizing: border-box;
        text-align: center;
    }
    .wn-slide h3 {
        font-size: 1.25rem; font-weight: 800; margin: .9rem 0 .35rem;
        color: #4338ca;
    }
    .dark .wn-slide h3 { color: #a5b4fc; }
    .wn-slide p {
        font-size: .92rem; color: #475569; margin: 0 auto; max-width: 440px;
        line-height: 1.55;
    }
    .dark .wn-slide p { color: #94a3b8; }
    .wn-kicker {
        display:inline-block; font-size:.7rem; font-weight:700;
        letter-spacing:.08em; text-transform:uppercase;
        color:#8b5cf6; background:rgba(139,92,246,.12);
        padding:3px 10px; border-radius:999px;
    }

    /* ---- mockup stage ---------------------------------------------------- */
    .wn-stage {
        height: 200px; margin: 1rem auto 0; max-width: 460px;
        border-radius: 14px; position: relative; overflow: hidden;
        background: #0f172a;
        border: 1px solid rgba(148,163,184,.25);
        box-shadow: inset 0 0 40px rgba(0,0,0,.35);
    }

    /* clinic badges */
    .wn-badge {
        position:absolute; padding:.4rem .8rem; border-radius:8px;
        font-size:.8rem; font-weight:700; color:#fff;
        animation: wnPop 2.6s ease-in-out infinite;
    }
    .wn-badge.r { background:#166534; left:26%; top:38%; }
    .wn-badge.k { background:#4c1d95; left:50%; top:54%; animation-delay:.6s; }
    @keyframes wnPop {
        0%,100% { transform: scale(.92); opacity:.55; }
        50%     { transform: scale(1.05); opacity:1; }
    }

    /* drawing canvas */
    .wn-canvas { position:absolute; inset:14px; background:#fff; border-radius:8px; }
    .wn-pen {
        position:absolute; width:22px; height:22px; color:#6366f1;
        animation: wnPenMove 3.4s ease-in-out infinite;
    }
    .wn-ink {
        position:absolute; left:40px; top:120px; width:0; height:4px;
        background:#6366f1; border-radius:4px;
        animation: wnInk 3.4s ease-in-out infinite;
    }
    @keyframes wnPenMove {
        0%   { left:34px;  top:112px; }
        45%  { left:300px; top:60px; }
        55%  { left:300px; top:60px; }
        100% { left:34px;  top:112px; }
    }
    @keyframes wnInk {
        0%   { width:0; }
        45%  { width:280px; transform:translateY(-58px) rotate(-12deg); }
        55%  { width:280px; transform:translateY(-58px) rotate(-12deg); opacity:1; }
        70%  { opacity:0; }
        100% { width:0; opacity:0; }
    }

    /* contextual menu */
    .wn-shape {
        position:absolute; left:50%; top:54%; transform:translate(-50%,-50%);
        width:120px; height:74px; border:2px solid #0ea5e9; border-radius:8px;
        background:rgba(14,165,233,.12);
        animation: wnSelect 3s ease-in-out infinite;
    }
    @keyframes wnSelect {
        0%,100% { box-shadow:0 0 0 0 rgba(14,165,233,0); }
        50%     { box-shadow:0 0 0 4px rgba(14,165,233,.35); }
    }
    .wn-ctx {
        position:absolute; left:50%; top:20%; transform:translateX(-50%);
        display:flex; gap:6px; padding:6px 8px; border-radius:999px;
        background:#fff; box-shadow:0 8px 20px rgba(0,0,0,.3);
        animation: wnFloat 3s ease-in-out infinite;
    }
    .wn-ctx i {
        width:24px; height:24px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        background:#eef2ff; color:#4f46e5; font-size:.7rem;
    }
    .wn-ctx i.danger { background:#fee2e2; color:#dc2626; }
    @keyframes wnFloat {
        0%,100% { transform:translateX(-50%) translateY(0); }
        50%     { transform:translateX(-50%) translateY(-4px); }
    }

    /* patient row + badge */
    .wn-row {
        position:absolute; left:24px; right:24px; top:50%;
        transform:translateY(-50%);
        display:flex; align-items:center; gap:12px;
        background:#1e293b; padding:14px 16px; border-radius:10px;
    }
    .wn-av { width:34px; height:34px; border-radius:50%; background:#3b82f6; flex:0 0 auto; }
    .wn-lines { flex:1; }
    .wn-lines span { display:block; height:8px; border-radius:4px; background:#334155; }
    .wn-lines span:first-child { width:60%; margin-bottom:7px; background:#475569; }
    .wn-lines span:last-child { width:38%; }
    .wn-clinic-badge {
        font-size:.72rem; font-weight:700; color:#fff; padding:.32rem .6rem;
        border-radius:6px; background:#166534;
        animation: wnSwap 3s steps(1) infinite;
    }
    @keyframes wnSwap {
        0%,49%  { background:#166534; }
        50%,100%{ background:#4c1d95; }
    }

    /* sidebar collapse */
    .wn-app { position:absolute; inset:14px; display:flex; gap:8px; }
    .wn-sb {
        background:#1e293b; border-radius:8px; width:130px;
        animation: wnCollapse 3.6s ease-in-out infinite;
        overflow:hidden;
    }
    .wn-sb b { display:block; height:10px; margin:14px 12px; border-radius:4px; background:#475569; }
    .wn-main { flex:1; background:#1e293b; border-radius:8px; }
    @keyframes wnCollapse {
        0%,100% { width:130px; }
        50%     { width:42px; }
    }

    /* lock / safe edits */
    .wn-lock {
        position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
        text-align:center; color:#fbbf24;
    }
    .wn-lock .ico {
        font-size:3rem;
        animation: wnLockPulse 2.4s ease-in-out infinite;
        display:inline-block;
    }
    @keyframes wnLockPulse {
        0%,100% { transform:scale(1); filter:drop-shadow(0 0 0 rgba(251,191,36,0)); }
        50%     { transform:scale(1.12); filter:drop-shadow(0 0 14px rgba(251,191,36,.6)); }
    }
    .wn-lock small { display:block; margin-top:8px; color:#cbd5e1; font-size:.8rem; }

    /* ---- dots + footer --------------------------------------------------- */
    .wn-dots { display:flex; justify-content:center; gap:7px; padding:.4rem 0 0; }
    .wn-dots button {
        width:8px; height:8px; border-radius:50%; border:0; padding:0;
        background:#cbd5e1; cursor:pointer; transition:all .2s;
    }
    .dark .wn-dots button { background:#334155; }
    .wn-dots button.active { background:#6366f1; width:22px; border-radius:5px; }

    #whatsNewV9Modal .modal-footer {
        border-top:1px solid rgba(148,163,184,.18);
        padding:.9rem 1.4rem; gap:.5rem;
    }
    .wn-foot-end { display:none; gap:.5rem; }
    .wn-foot-end.show { display:flex; }

    @media (max-width: 575.98px) {
        #whatsNewV9Modal .wn-slide { padding: 1.2rem 1.1rem 1rem; }
        #whatsNewV9Modal .wn-stage { height: 168px; }
    }
</style>

<div class="modal fade" id="whatsNewV9Modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-stars me-2"></i>What's New
          <span class="version-pill">v9.0.0</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="wn-viewport">
          <div class="wn-track" id="wnTrack">

            <!-- 1 — Welcome -->
            <div class="wn-slide">
              <span class="wn-kicker">Release 9.0.0</span>
              <div class="wn-stage">
                <div class="wn-badge r">Riyadh</div>
                <div class="wn-badge k">Kafr El-Sheikh</div>
              </div>
              <h3>A big update is here</h3>
              <p>Multi-clinic support, a full drawing studio, smarter patient
                 lists and safer bookings. Take 30 seconds for the tour.</p>
            </div>

            <!-- 2 — Multi-clinic -->
            <div class="wn-slide">
              <span class="wn-kicker">Multi-Clinic</span>
              <div class="wn-stage">
                <div class="wn-badge r">Riyadh Clinic</div>
                <div class="wn-badge k">Kafr El-Sheikh Clinic</div>
              </div>
              <h3>Two clinics, one system</h3>
              <p>Every appointment, patient and payment is now tagged to its
                 clinic. Pick the clinic when booking; finances stay separated
                 per clinic automatically.</p>
            </div>

            <!-- 3 — Draw Consultation -->
            <div class="wn-slide">
              <span class="wn-kicker">Drawing Studio</span>
              <div class="wn-stage">
                <div class="wn-canvas"></div>
                <div class="wn-ink"></div>
                <div class="wn-pen"><i class="bi bi-pencil-fill" style="font-size:22px;"></i></div>
              </div>
              <h3>Draw the consultation</h3>
              <p>A full-screen canvas with pen, shapes, eye templates and
                 medical stamps — sketch findings right on the appointment and
                 it autosaves to attachments.</p>
            </div>

            <!-- 4 — Contextual menu + settings -->
            <div class="wn-slide">
              <span class="wn-kicker">Smart Editing</span>
              <div class="wn-stage">
                <div class="wn-ctx">
                  <i class="bi bi-arrow-up"></i>
                  <i class="bi bi-eye"></i>
                  <i class="bi bi-sliders"></i>
                  <i class="danger bi bi-trash"></i>
                </div>
                <div class="wn-shape"></div>
              </div>
              <h3>Canva-style quick menu</h3>
              <p>Select any element and a floating toolbar follows it — reorder,
                 hide, group, delete, or open a per-element settings panel for
                 arrows, shapes and text.</p>
            </div>

            <!-- 5 — Patient last clinic -->
            <div class="wn-slide">
              <span class="wn-kicker">Patient Lists</span>
              <div class="wn-stage">
                <div class="wn-row">
                  <div class="wn-av"></div>
                  <div class="wn-lines"><span></span><span></span></div>
                  <div class="wn-clinic-badge">Last clinic</div>
                </div>
              </div>
              <h3>See the last-visit clinic</h3>
              <p>Table, cards and folders now show a colour-coded badge for the
                 clinic of each patient's most recent visit, and you can group
                 patients by clinic.</p>
            </div>

            <!-- 6 — Sidebar -->
            <div class="wn-slide">
              <span class="wn-kicker">Navigation</span>
              <div class="wn-stage">
                <div class="wn-app">
                  <div class="wn-sb"><b></b><b style="width:70%"></b><b style="width:55%"></b></div>
                  <div class="wn-main"></div>
                </div>
              </div>
              <h3>Collapsible mini-sidebar</h3>
              <p>Collapse the sidebar to a slim icon rail for more screen space —
                 it remembers your choice and no longer flickers between pages.</p>
            </div>

            <!-- 7 — Safer bookings (final) -->
            <div class="wn-slide">
              <span class="wn-kicker">Financial Safety</span>
              <div class="wn-stage">
                <div class="wn-lock">
                  <span class="ico"><i class="bi bi-shield-lock-fill"></i></span>
                  <small>Paid bookings are protected</small>
                </div>
              </div>
              <h3>Safer booking edits</h3>
              <p>You can now edit a booking from the calendar. Once a payment
                 exists the price-affecting fields lock automatically, and an
                 audited "Correct visit type" action handles real mistakes —
                 so the books always stay balanced.</p>
            </div>

          </div>
        </div>
        <div class="wn-dots" id="wnDots"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="wnPrev" disabled>
          <i class="bi bi-arrow-left me-1"></i>Previous
        </button>
        <button type="button" class="btn btn-primary" id="wnNext">
          Next<i class="bi bi-arrow-right ms-1"></i>
        </button>
        <div class="wn-foot-end" id="wnEnd">
          <button type="button" class="btn btn-outline-secondary" id="wnDontShow">
            Don't show again
          </button>
          <button type="button" class="btn btn-primary" id="wnClose">
            <i class="bi bi-check-lg me-1"></i>Close
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
    // Bump this key whenever a future release wants the wizard to reappear
    // once for everyone. Changed for the v9.0.0 wizard redesign.
    const STORAGE_KEY = 'whatsNew_v9_0_0_wizard_dismissed';

    function init() {
        if (localStorage.getItem(STORAGE_KEY) === '1') return;
        const el = document.getElementById('whatsNewV9Modal');
        if (!el || typeof bootstrap === 'undefined') return;

        const track  = el.querySelector('#wnTrack');
        const slides = track ? track.children : [];
        const total  = slides.length;
        const dotsWrap = el.querySelector('#wnDots');
        const prevBtn = el.querySelector('#wnPrev');
        const nextBtn = el.querySelector('#wnNext');
        const endWrap = el.querySelector('#wnEnd');
        let idx = 0;

        // build dots
        for (let i = 0; i < total; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('aria-label', 'Go to step ' + (i + 1));
            b.addEventListener('click', () => go(i));
            dotsWrap.appendChild(b);
        }

        function render() {
            track.style.transform = 'translateX(-' + (idx * 100) + '%)';
            Array.from(dotsWrap.children).forEach((d, i) =>
                d.classList.toggle('active', i === idx));
            prevBtn.disabled = idx === 0;
            const last = idx === total - 1;
            nextBtn.style.display = last ? 'none' : '';
            endWrap.classList.toggle('show', last);
        }
        function go(i) { idx = Math.max(0, Math.min(total - 1, i)); render(); }

        nextBtn.addEventListener('click', () => go(idx + 1));
        prevBtn.addEventListener('click', () => go(idx - 1));

        // keyboard arrows
        el.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') go(idx + 1);
            if (e.key === 'ArrowLeft')  go(idx - 1);
        });

        const modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: true });

        function dismiss() {
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
            modal.hide();
        }
        el.querySelector('#wnDontShow').addEventListener('click', dismiss);
        el.querySelector('#wnClose').addEventListener('click', dismiss);
        // Any close (X / backdrop-esc) also counts as "seen once".
        el.addEventListener('hidden.bs.modal', () => {
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
        }, { once: true });

        render();
        // Let the page settle so we don't stack on toasts/session warnings.
        setTimeout(() => { try { modal.show(); } catch (e) {} }, 800);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
