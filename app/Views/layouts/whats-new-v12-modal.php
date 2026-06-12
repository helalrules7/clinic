<?php
/**
 * whats-new-v12-modal.php — v12.0.0 ("Novak Djokovic") What's New wizard.
 * Doctor-only: included from layouts/main.php, never from secretary_main.php.
 * Opened by the dashboard v12 notice bar (CTA → #whatsNewV12Modal). Self-contained
 * (own .wn12-* CSS + nav JS); the older whats-new-v9-modal.php is left untouched.
 * Feature copy is sourced from the v12_perf/ docs.
 */
?>
<style>
    #whatsNewV12Modal .modal-dialog { max-width: 640px; }
    #whatsNewV12Modal .modal-content {
        border: none; border-radius: 20px; overflow: hidden;
        background: var(--card, #fff); color: var(--text, #0f172a);
        box-shadow: 0 30px 80px rgba(0,0,0,.35);
    }
    #whatsNewV12Modal .modal-header {
        background: linear-gradient(135deg, #6366F1 0%, #4F46E5 55%, #4338CA 100%);
        color: #fff; border: none; padding: 1rem 1.25rem;
    }
    #whatsNewV12Modal .modal-title { font-weight: 800; display: flex; align-items: center; gap: .5rem; }
    #whatsNewV12Modal .wn12-vpill {
        font-size: .72rem; font-weight: 800; letter-spacing: .04em;
        background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.35);
        padding: 2px 9px; border-radius: 999px;
    }
    #whatsNewV12Modal .modal-body { padding: 1.1rem 1.25rem .9rem; }

    .wn12-viewport { overflow: hidden; }
    .wn12-track { display: flex; transition: transform .4s cubic-bezier(.4,0,.2,1); }
    .wn12-slide { flex: 0 0 100%; min-width: 100%; box-sizing: border-box; padding: 2px 4px; }

    .wn12-stage {
        height: 168px; border-radius: 16px; margin-bottom: 1rem;
        display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;
        background: linear-gradient(135deg, rgba(99,102,241,.12), rgba(67,56,202,.10));
        border: 1px solid rgba(99,102,241,.18);
    }
    .dark .wn12-stage { background: linear-gradient(135deg, rgba(99,102,241,.20), rgba(30,27,75,.35)); border-color: rgba(99,102,241,.30); }
    .wn12-stage .wn12-bigicon { font-size: 4.2rem; color: #4F46E5; filter: drop-shadow(0 6px 14px rgba(79,70,229,.35)); }
    .dark .wn12-stage .wn12-bigicon { color: #a5b4fc; }
    .wn12-stage--wa { background: linear-gradient(135deg, rgba(37,211,102,.14), rgba(18,140,80,.12)); border-color: rgba(37,211,102,.30); }
    .wn12-stage--wa .wn12-bigicon { color: #1faf54; }
    .dark .wn12-stage--wa .wn12-bigicon { color: #4ade80; }

    /* Novak portrait — theme-switched */
    .wn12-nd { height: 150px; width: auto; object-fit: contain; }
    .wn12-nd.nd-dark { display: none; }
    .dark .wn12-nd.nd-light { display: none; }
    .dark .wn12-nd.nd-dark { display: block; }

    .wn12-kicker {
        display: inline-block; font-size: .68rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .08em; color: #4F46E5; background: rgba(99,102,241,.12);
        padding: 2px 9px; border-radius: 999px; margin-bottom: .5rem;
    }
    .dark .wn12-kicker { color: #a5b4fc; background: rgba(99,102,241,.20); }
    .wn12-kicker.k-wa { color: #1faf54; background: rgba(37,211,102,.14); }
    .dark .wn12-kicker.k-wa { color: #4ade80; background: rgba(37,211,102,.20); }

    .wn12-title { font-size: 1.28rem; font-weight: 800; margin: 0 0 .55rem; line-height: 1.3; }
    .wn12-lead { color: var(--muted, #64748b); font-size: .92rem; margin: 0 0 .7rem; line-height: 1.65; }
    .wn12-list { list-style: none; margin: 0; padding: 0; }
    .wn12-list li { display: flex; gap: .55rem; align-items: flex-start; margin-bottom: .5rem; font-size: .9rem; line-height: 1.5; }
    .wn12-list li i { color: #22c55e; font-size: 1rem; flex-shrink: 0; margin-top: 2px; }
    .wn12-list li b { font-weight: 700; }

    .wn12-dots { display: flex; justify-content: center; gap: 6px; padding: .5rem 0 .2rem; flex-wrap: wrap; }
    .wn12-dots button { width: 7px; height: 7px; border-radius: 50%; border: none; background: #cbd5e1; padding: 0; cursor: pointer; transition: all .2s; }
    .dark .wn12-dots button { background: #475569; }
    .wn12-dots button.active { background: #6366f1; width: 20px; border-radius: 4px; }

    #whatsNewV12Modal .modal-footer { border: none; padding: .6rem 1.25rem 1.1rem; display: flex; justify-content: space-between; align-items: center; gap: .5rem; }
    #whatsNewV12Modal .wn12-foot-left .btn { font-size: .82rem; }
    #whatsNewV12Modal .wn12-foot-right { display: flex; gap: .5rem; }
    #whatsNewV12Modal .wn12-end { display: none; }
    #whatsNewV12Modal .wn12-end.show { display: flex; gap: .5rem; }
    @media (max-width: 575.98px) {
        .wn12-stage { height: 140px; }
        .wn12-nd { height: 124px; }
        .wn12-title { font-size: 1.12rem; }
    }

    /* ── Slide mockups (replace the plain icons) ── */
    .wn12-mk { display: flex; align-items: center; justify-content: center; gap: 16px; width: 100%; position: relative; z-index: 1; }
    .wn12-twelve { font-size: 5rem; font-weight: 900; line-height: 1; letter-spacing: -3px;
        background: linear-gradient(135deg,#6366F1,#4338CA); -webkit-background-clip: text; background-clip: text; color: transparent;
        filter: drop-shadow(0 6px 16px rgba(79,70,229,.4)); text-align: center; }
    .dark .wn12-twelve { background: linear-gradient(135deg,#c7d2fe,#6366F1); -webkit-background-clip: text; background-clip: text; }
    .wn12-twelve small { display: block; font-size: .95rem; letter-spacing: 2px; -webkit-text-fill-color: #4F46E5; color: #4F46E5; font-weight: 800; margin-top: 2px; }
    .dark .wn12-twelve small { -webkit-text-fill-color: #a5b4fc; color: #a5b4fc; }

    .wn12-phone { width: 116px; height: 152px; border-radius: 16px; background: var(--card,#fff);
        border: 2px solid rgba(99,102,241,.35); box-shadow: 0 8px 22px rgba(0,0,0,.18); padding: 8px; display: flex; flex-direction: column; gap: 6px; overflow: hidden; }
    .dark .wn12-phone { background: #0b1220; border-color: rgba(99,102,241,.45); }
    .wn12-wabar { background: #25D366; color: #fff; font-size: .58rem; font-weight: 700; border-radius: 7px; padding: 3px 7px; display: flex; align-items: center; gap: 4px; }
    .wn12-bub { background: rgba(37,211,102,.18); border-radius: 9px 9px 9px 3px; height: 22px; }
    .wn12-bub.short { width: 62%; height: 12px; }
    .wn12-sendrow { margin-top: auto; display: flex; align-items: center; gap: 5px; }
    .wn12-sendrow span { flex: 1; height: 15px; border-radius: 8px; background: rgba(148,163,184,.25); }
    .wn12-sendrow i { color: #25D366; }
    .wn12-linkpill { background: rgba(99,102,241,.14); color: #4F46E5; font-size: .54rem; font-weight: 700; border-radius: 7px; padding: 3px 6px; display: flex; align-items: center; gap: 3px; overflow: hidden; white-space: nowrap; }
    .dark .wn12-linkpill { color: #a5b4fc; }
    .wn12-doc { flex: 1; background: rgba(148,163,184,.10); border-radius: 8px; padding: 7px; display: flex; flex-direction: column; gap: 5px; }
    .wn12-dochead { height: 16px; border-radius: 4px; background: linear-gradient(90deg,#6366F1,#4338CA); }
    .wn12-docrow { height: 7px; border-radius: 3px; background: rgba(148,163,184,.4); }
    .wn12-docrow.short { width: 60%; }

    .wn12-card { width: 184px; background: var(--card,#fff); border: 1px solid rgba(99,102,241,.2); border-radius: 12px; box-shadow: 0 8px 22px rgba(0,0,0,.12); padding: 10px; }
    .dark .wn12-card { background: #0b1220; border-color: rgba(99,102,241,.3); }
    .wn12-tpls { display: flex; flex-direction: column; gap: 6px; }
    .wn12-tpl { display: flex; align-items: center; gap: 6px; font-size: .7rem; font-weight: 600; padding: 5px 8px; border-radius: 8px; background: rgba(148,163,184,.12); }
    .wn12-tpl i { color: #4F46E5; }
    .wn12-tpl.active { background: rgba(37,211,102,.18); }
    .wn12-tpl.active i { color: #1faf54; }
    .wn12-chart { display: flex; align-items: flex-end; gap: 10px; height: 120px; }
    .wn12-chart span { flex: 1; border-radius: 5px 5px 0 0; background: linear-gradient(180deg,#6366F1,#4338CA); }
    .wn12-toast { width: 212px; background: var(--card,#fff); border: 1px solid rgba(99,102,241,.2); border-radius: 12px; box-shadow: 0 10px 26px rgba(0,0,0,.16); padding: 11px 13px; display: flex; gap: 9px; align-items: center; }
    .dark .wn12-toast { background: #0b1220; }
    .wn12-toast > i { color: #4F46E5; font-size: 1.3rem; }
    .wn12-toast > div { flex: 1; display: flex; flex-direction: column; gap: 6px; }
    .wn12-tline { height: 8px; border-radius: 4px; background: rgba(148,163,184,.5); }
    .wn12-tline.short { width: 55%; }
    .wn12-paper { width: 116px; background: #fff; border-radius: 6px; box-shadow: 0 8px 22px rgba(0,0,0,.2); padding: 9px; display: flex; flex-direction: column; gap: 6px; }
    .wn12-phead { height: 15px; border-radius: 3px; background: linear-gradient(90deg,#4F46E5,#4338CA); }
    .wn12-phead.small { height: 11px; opacity: .7; }
    .wn12-prow { height: 6px; border-radius: 2px; background: rgba(15,23,42,.18); }
    .wn12-gauge { width: 132px; height: 78px; border-radius: 132px 132px 0 0; position: relative;
        background: conic-gradient(from 270deg at 50% 100%, #22c55e 0 32%, #eab308 32% 66%, #ef4444 66% 90%, transparent 90%); }
    .wn12-gauge::after { content: ""; position: absolute; left: 13px; right: 13px; bottom: 0; top: 24px; border-radius: 120px 120px 0 0; background: var(--card,#fff); }
    .dark .wn12-gauge::after { background: #0b1220; }
    .wn12-needle { position: absolute; left: calc(50% - 1.5px); bottom: 2px; width: 3px; height: 52px; background: #4F46E5; transform-origin: bottom center; transform: rotate(40deg); border-radius: 2px; z-index: 1; }
    .wn12-checks { display: flex; flex-direction: column; gap: 9px; }
    .wn12-checks > div { display: flex; align-items: center; gap: 8px; }
    .wn12-checks i { color: #22c55e; font-size: 1rem; }
    .wn12-checks span { flex: 1; height: 8px; border-radius: 4px; background: rgba(148,163,184,.4); }
    .wn12-trophy { font-size: 4.4rem; color: #eab308; filter: drop-shadow(0 6px 14px rgba(234,179,8,.4)); }

    /* ════ Animated mockups + intro celebration ════ */
    @media (prefers-reduced-motion: no-preference) {
        /* Reports — equalizer bars */
        .wn12-chart span { transform-origin: bottom; animation: wn12bars 1.6s ease-in-out infinite alternate; }
        .wn12-chart span:nth-child(1){animation-delay:0s} .wn12-chart span:nth-child(2){animation-delay:.2s}
        .wn12-chart span:nth-child(3){animation-delay:.4s} .wn12-chart span:nth-child(4){animation-delay:.6s}
        .wn12-chart span:nth-child(5){animation-delay:.8s}
        @keyframes wn12bars { from{transform:scaleY(.78)} to{transform:scaleY(1.06)} }

        /* WhatsApp — bubbles loop in + send pulse */
        .wn12-bub { transform-origin: left center; animation: wn12bub 3.2s ease-in-out infinite; }
        .wn12-bub.short { animation-delay:.4s; }
        @keyframes wn12bub { 0%{opacity:0;transform:translateY(6px) scale(.92)} 12%,82%{opacity:1;transform:none} 100%{opacity:0;transform:translateY(6px) scale(.92)} }
        .wn12-sendrow i { animation: wn12send 1.5s ease-in-out infinite; }
        @keyframes wn12send { 0%,100%{transform:none;opacity:.7} 50%{transform:translateX(3px) scale(1.18);opacity:1} }

        /* Templates — highlight cycles down the list */
        .wn12-tpls .wn12-tpl { animation: wn12tpl 4s infinite; }
        .wn12-tpls .wn12-tpl:nth-child(1){animation-delay:0s} .wn12-tpls .wn12-tpl:nth-child(2){animation-delay:1s}
        .wn12-tpls .wn12-tpl:nth-child(3){animation-delay:2s} .wn12-tpls .wn12-tpl:nth-child(4){animation-delay:3s}
        @keyframes wn12tpl { 0%,18%{background:rgba(37,211,102,.20)} 24%,100%{background:rgba(148,163,184,.12)} }

        /* Public link — doc rows shimmer, link pill blink */
        .wn12-docrow { animation: wn12row 1.8s ease-in-out infinite; }
        .wn12-docrow:nth-child(3){animation-delay:.2s} .wn12-docrow:nth-child(4){animation-delay:.4s}
        @keyframes wn12row { 0%,100%{opacity:.45} 50%{opacity:1} }
        .wn12-linkpill i { animation: wn12send 1.6s ease-in-out infinite; }

        /* Notifications — toast floats in, bell rings */
        .wn12-toast { animation: wn12toast 3.4s ease-in-out infinite; }
        @keyframes wn12toast { 0%{transform:translateY(-9px);opacity:.3} 14%,86%{transform:none;opacity:1} 100%{transform:translateY(-9px);opacity:.3} }
        .wn12-toast > i { transform-origin: top center; animation: wn12bell 2.4s ease-in-out infinite; }
        @keyframes wn12bell { 0%,68%,100%{transform:rotate(0)} 72%{transform:rotate(15deg)} 78%{transform:rotate(-12deg)} 84%{transform:rotate(8deg)} 90%{transform:rotate(-4deg)} }

        /* Print — header shimmer */
        .wn12-phead { background: linear-gradient(90deg,#4F46E5,#818cf8,#4338CA); background-size:200% 100%; animation: wn12shine 2.4s linear infinite; }
        .wn12-prow { animation: wn12row 1.9s ease-in-out infinite; }
        @keyframes wn12shine { from{background-position:0 0} to{background-position:200% 0} }

        /* Performance — gauge needle sweeps */
        .wn12-needle { transform-origin: bottom center; animation: wn12sweep 2.8s ease-in-out infinite; }
        @keyframes wn12sweep { 0%,100%{transform:rotate(-34deg)} 50%{transform:rotate(50deg)} }

        /* Fixes — checks pop in sequence */
        .wn12-checks > div i { animation: wn12check 2.4s ease infinite; }
        .wn12-checks > div:nth-child(1) i{animation-delay:0s} .wn12-checks > div:nth-child(2) i{animation-delay:.3s}
        .wn12-checks > div:nth-child(3) i{animation-delay:.6s}
        @keyframes wn12check { 0%,38%{transform:scale(.5);opacity:.25} 50%,100%{transform:scale(1);opacity:1} }

        /* Outro trophy bob */
        .wn12-trophy { animation: wn12float 2.6s ease-in-out infinite; }

        /* Phone gentle bob */
        .wn12-phone { animation: wn12phone 4s ease-in-out infinite; }
        @keyframes wn12phone { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }
    }

    /* ── Intro slide celebration: tennis balls + rackets + sparkles ── */
    .wn12-fx { position: absolute; inset: 0; pointer-events: none; overflow: hidden; z-index: 0; }
    .wn12-fx .wn12-tb { position: absolute; left: var(--x); top: var(--y); width: var(--s,12px); height: var(--s,12px); border-radius: 50%;
        background: radial-gradient(circle at 34% 30%, #f2ff7a, #c4ec5a 52%, #93c83f); box-shadow: 0 0 7px rgba(196,236,90,.75); }
    .wn12-fx .wn12-tb::before { content:""; position:absolute; inset:0; border-radius:50%;
        background: linear-gradient(115deg, transparent 43%, rgba(255,255,255,.9) 47% 52%, transparent 56%); }
    .wn12-fx .wn12-rk { position: absolute; left: var(--x); top: var(--y); width: 17px; height: 17px; border: 2px solid rgba(99,102,241,.55); border-radius: 50%; }
    .wn12-fx .wn12-rk::after { content:""; position:absolute; top:100%; left:50%; width:2px; height:10px; background: rgba(99,102,241,.55); transform: translateX(-50%); }
    .dark .wn12-fx .wn12-rk { border-color: rgba(165,180,252,.65); } .dark .wn12-fx .wn12-rk::after { background: rgba(165,180,252,.65); }
    .wn12-fx .wn12-spark { position: absolute; left: var(--x); top: var(--y); width: 6px; height: 6px; color: #fbbf24;
        background: conic-gradient(from 0deg, transparent 0 20%, currentColor 25%, transparent 30% 45%, currentColor 50%, transparent 55% 70%, currentColor 75%, transparent 80%); }
    @media (prefers-reduced-motion: no-preference) {
        .wn12-fx .wn12-tb { animation: wn12float 3.2s ease-in-out var(--d,0s) infinite; }
        .wn12-fx .wn12-rk { animation: wn12spin 3.6s linear var(--d,0s) infinite; }
        .wn12-fx .wn12-spark { animation: wn12twinkle 1.8s ease-in-out var(--d,0s) infinite; }
    }
    @keyframes wn12float { 0%,100%{transform:translateY(0) rotate(0)} 50%{transform:translateY(-11px) rotate(180deg)} }
    @keyframes wn12spin { from{transform:rotate(0)} to{transform:rotate(360deg)} }
    @keyframes wn12twinkle { 0%,100%{opacity:.2;transform:scale(.6)} 50%{opacity:1;transform:scale(1.1)} }
</style>

<div class="modal fade" id="whatsNewV12Modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-stars"></i> What's New <span class="wn12-vpill">v12.0.0 · ND</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="wn12-viewport">
          <div class="wn12-track" id="wn12Track">

            <!-- 1 · Intro / Novak -->
            <div class="wn12-slide">
              <div class="wn12-stage">
                <span class="wn12-fx" aria-hidden="true">
                  <i class="wn12-tb" style="--x:7%;--y:20%;--d:0s;--s:13px"></i>
                  <i class="wn12-tb" style="--x:89%;--y:22%;--d:.6s;--s:11px"></i>
                  <i class="wn12-tb" style="--x:15%;--y:72%;--d:1.1s;--s:10px"></i>
                  <i class="wn12-tb" style="--x:81%;--y:78%;--d:.3s;--s:14px"></i>
                  <i class="wn12-tb" style="--x:46%;--y:7%;--d:.9s;--s:9px"></i>
                  <i class="wn12-rk" style="--x:3%;--y:52%;--d:0s"></i>
                  <i class="wn12-rk" style="--x:92%;--y:55%;--d:1.4s"></i>
                  <i class="wn12-spark" style="--x:30%;--y:30%;--d:.2s"></i>
                  <i class="wn12-spark" style="--x:66%;--y:60%;--d:.8s"></i>
                  <i class="wn12-spark" style="--x:54%;--y:85%;--d:1.3s"></i>
                </span>
                <div class="wn12-mk">
                  <span class="wn12-twelve">12<small>NOVAK · ND</small></span>
                  <img class="wn12-nd nd-light" src="/app/Views/doctor/assets/svg/nd-light.png" alt="Novak Djokovic" onerror="this.style.display='none'">
                  <img class="wn12-nd nd-dark" src="/app/Views/doctor/assets/svg/nd-dark.png" alt="Novak Djokovic" onerror="this.style.display='none'">
                </div>
              </div>
              <span class="wn12-kicker">A major release</span>
              <h3 class="wn12-title">v12.0.0 — Novak Djokovic Is Here</h3>
              <p class="wn12-lead">Consistency, power and precision. v12 brings the big one — a full
                <b>WhatsApp Integration</b> module with a secure patient share-link — plus smarter reports,
                print/PDF, notifications and a wave of performance &amp; polish.</p>
              <ul class="wn12-list">
                <li><i class="bi bi-whatsapp"></i> One-tap WhatsApp messaging + templates</li>
                <li><i class="bi bi-link-45deg"></i> Public patient link for prescriptions &amp; instructions</li>
                <li><i class="bi bi-lightning-charge"></i> Faster dashboard, patients &amp; calendar</li>
              </ul>
            </div>

            <!-- 2 · WhatsApp overview -->
            <div class="wn12-slide">
              <div class="wn12-stage wn12-stage--wa"><div class="wn12-mk"><div class="wn12-phone">
                <div class="wn12-wabar"><i class="bi bi-whatsapp"></i> WhatsApp</div>
                <div class="wn12-bub"></div><div class="wn12-bub short"></div>
                <div class="wn12-sendrow"><span></span><i class="bi bi-send-fill"></i></div>
              </div></div></div>
              <span class="wn12-kicker k-wa">WhatsApp Integration · 1 of 3</span>
              <h3 class="wn12-title">Message patients in one tap</h3>
              <p class="wn12-lead">A new <b>Send WhatsApp</b> action on the appointment, patient &amp; secretary
                screens opens WhatsApp with a ready, professional message — no third-party API, nothing leaves
                your control until you press send.</p>
              <ul class="wn12-list">
                <li><i class="bi bi-check2-circle"></i> Per-patient <b>consent</b> toggle, shown before every send</li>
                <li><i class="bi bi-toggles"></i> Off by default — enable it in <b>Settings → WhatsApp</b></li>
                <li><i class="bi bi-translate"></i> Arabic messages, auto-filled with patient &amp; doctor names</li>
                <li><i class="bi bi-phone"></i> The full-height editor lets you tweak the text before sending</li>
              </ul>
            </div>

            <!-- 3 · WhatsApp templates -->
            <div class="wn12-slide">
              <div class="wn12-stage wn12-stage--wa"><div class="wn12-mk"><div class="wn12-card wn12-tpls">
                <div class="wn12-tpl"><i class="bi bi-calendar-check"></i> Confirmation</div>
                <div class="wn12-tpl active"><i class="bi bi-eyedropper"></i> Eye-drops schedule</div>
                <div class="wn12-tpl"><i class="bi bi-bandaid"></i> Post-op care</div>
                <div class="wn12-tpl"><i class="bi bi-arrow-repeat"></i> Follow-up</div>
              </div></div></div>
              <span class="wn12-kicker k-wa">WhatsApp Integration · 2 of 3</span>
              <h3 class="wn12-title">Ophthalmology-ready templates</h3>
              <p class="wn12-lead">Pick a template and it fills itself in — each one is signed with the
                branch's <b>clinic name &amp; phone</b> automatically.</p>
              <ul class="wn12-list">
                <li><i class="bi bi-calendar-check"></i> Appointment <b>confirmation</b> &amp; <b>reminder</b></li>
                <li><i class="bi bi-eyedropper"></i> <b>Eye-drops schedule</b> &amp; pupil-dilation instructions</li>
                <li><i class="bi bi-bandaid"></i> Post-op: <b>cataract</b>, <b>LASIK/PRK</b>, <b>injection</b></li>
                <li><i class="bi bi-arrow-repeat"></i> Follow-up reminder &amp; emergency-warning notice</li>
              </ul>
            </div>

            <!-- 4 · Public patient link -->
            <div class="wn12-slide">
              <div class="wn12-stage wn12-stage--wa"><div class="wn12-mk"><div class="wn12-phone">
                <div class="wn12-linkpill"><i class="bi bi-link-45deg"></i> roaya…/p/v/…</div>
                <div class="wn12-doc">
                  <div class="wn12-dochead"></div>
                  <div class="wn12-docrow"></div><div class="wn12-docrow"></div><div class="wn12-docrow short"></div>
                </div>
              </div></div></div>
              <span class="wn12-kicker k-wa">WhatsApp Integration · 3 of 3</span>
              <h3 class="wn12-title">A secure link your patient can open</h3>
              <p class="wn12-lead">The <b>Comprehensive Visit Report</b> sends one short link
                (<code>/p/v/…</code>). The patient opens it — no login — and sees whichever of their documents
                exist for that visit.</p>
              <ul class="wn12-list">
                <li><i class="bi bi-capsule"></i> Prescription, <b>glasses</b> measurements &amp; medical instructions</li>
                <li><i class="bi bi-shield-lock"></i> Random token (hashed), <b>expires</b>, revocable, every open audited</li>
                <li><i class="bi bi-filetype-pdf"></i> Download as <b>PDF</b> or print — clinic header on every page</li>
                <li><i class="bi bi-phone-vibrate"></i> Looks great on the patient's phone</li>
              </ul>
            </div>

            <!-- 5 · Reports -->
            <div class="wn12-slide">
              <div class="wn12-stage"><div class="wn12-mk"><div class="wn12-card wn12-chart">
                <span style="height:42%"></span><span style="height:72%"></span><span style="height:55%"></span><span style="height:88%"></span><span style="height:63%"></span>
              </div></div></div>
              <span class="wn12-kicker">Reports</span>
              <h3 class="wn12-title">Drug reports that work on any screen</h3>
              <ul class="wn12-list">
                <li><i class="bi bi-arrows-fullscreen"></i> All tables now <b>scroll horizontally</b> on mobile &amp; tablet — reach every column</li>
                <li><i class="bi bi-tags"></i> Chart legends show the <b>full drug names</b> (no more cut-off labels)</li>
              </ul>
            </div>

            <!-- 6 · Notifications -->
            <div class="wn12-slide">
              <div class="wn12-stage"><div class="wn12-mk"><div class="wn12-toast">
                <i class="bi bi-bell-fill"></i>
                <div><div class="wn12-tline"></div><div class="wn12-tline short"></div></div>
              </div></div></div>
              <span class="wn12-kicker">Notifications</span>
              <h3 class="wn12-title">Never miss an alert</h3>
              <ul class="wn12-list">
                <li><i class="bi bi-bell-fill"></i> Secretary now gets the <b>“enable notifications”</b> prompt — in Arabic</li>
                <li><i class="bi bi-calendar-week"></i> A gentle <b>weekly reminder</b> for doctor &amp; secretary to turn them on</li>
                <li><i class="bi bi-check-circle"></i> Already subscribed? You'll never be nagged again</li>
              </ul>
            </div>

            <!-- 7 · Appointment & print polish -->
            <div class="wn12-slide">
              <div class="wn12-stage"><div class="wn12-mk"><div class="wn12-paper">
                <div class="wn12-phead"></div>
                <div class="wn12-prow"></div><div class="wn12-prow"></div>
                <div class="wn12-phead small"></div>
                <div class="wn12-prow"></div>
              </div></div></div>
              <span class="wn12-kicker">Appointment &amp; Print</span>
              <h3 class="wn12-title">Cleaner screens, smarter print</h3>
              <ul class="wn12-list">
                <li><i class="bi bi-ui-checks"></i> Tidier appointment action bar; print buttons live on each card</li>
                <li><i class="bi bi-journal-medical"></i> Medical-Instructions buttons (Suggest / Templates / Add) on the title row</li>
                <li><i class="bi bi-bookmark-star"></i> Session label is now a clean modal</li>
                <li><i class="bi bi-file-earmark-break"></i> Print/PDF: sections reflow to fill pages and a <b>table is never split</b></li>
              </ul>
            </div>

            <!-- 8 · Performance -->
            <div class="wn12-slide">
              <div class="wn12-stage"><div class="wn12-mk"><div class="wn12-gauge"><span class="wn12-needle"></span></div></div></div>
              <span class="wn12-kicker">Performance</span>
              <h3 class="wn12-title">Lighter &amp; faster</h3>
              <ul class="wn12-list">
                <li><i class="bi bi-speedometer2"></i> Faster dashboard — thumbnail avatars &amp; lazy clinical dashboard</li>
                <li><i class="bi bi-people"></i> Patients page: true <b>server-side pagination &amp; search</b></li>
                <li><i class="bi bi-clock-history"></i> Dedicated <b>Activities</b> page with filters</li>
                <li><i class="bi bi-calendar3"></i> Smoother calendar &amp; less scroll jank</li>
              </ul>
            </div>

            <!-- 9 · Fixes -->
            <div class="wn12-slide">
              <div class="wn12-stage"><div class="wn12-mk"><div class="wn12-card wn12-checks">
                <div><i class="bi bi-check2-circle"></i><span></span></div>
                <div><i class="bi bi-check2-circle"></i><span></span></div>
                <div><i class="bi bi-check2-circle"></i><span></span></div>
              </div></div></div>
              <span class="wn12-kicker">Fixes &amp; polish</span>
              <h3 class="wn12-title">Lots of small wins</h3>
              <ul class="wn12-list">
                <li><i class="bi bi-eye-slash"></i> WhatsApp buttons now correctly hide when the feature is off</li>
                <li><i class="bi bi-chat-dots"></i> Chat window always sits <b>above</b> the AI assistant on mobile</li>
                <li><i class="bi bi-clock"></i> Notice-bar clock + next-appointment show on mobile (both roles)</li>
                <li><i class="bi bi-stars"></i> …and many more refinements</li>
              </ul>
            </div>

            <!-- 10 · Outro -->
            <div class="wn12-slide">
              <div class="wn12-stage"><div class="wn12-mk"><i class="bi bi-trophy-fill wn12-trophy"></i></div></div>
              <span class="wn12-kicker">That's v12</span>
              <h3 class="wn12-title">Thanks for playing 🎾</h3>
              <p class="wn12-lead">Enjoy v12.0.0 — “Novak Djokovic”. Explore the new WhatsApp tools from any
                patient or appointment, and find the toggle under <b>Settings → WhatsApp</b>.</p>
            </div>

          </div>
        </div>
        <div class="wn12-dots" id="wn12Dots"></div>
      </div>

      <div class="modal-footer">
        <div class="wn12-foot-left">
          <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none" id="wn12DontShow">Don't show again</button>
        </div>
        <div class="wn12-foot-right">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="wn12Prev"><i class="bi bi-arrow-left"></i></button>
          <button type="button" class="btn btn-sm btn-primary" id="wn12Next">Next <i class="bi bi-arrow-right"></i></button>
          <span class="wn12-end" id="wn12End">
            <button type="button" class="btn btn-sm btn-primary" id="wn12Close" data-bs-dismiss="modal">Got it 🎾</button>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
    function init() {
        var el = document.getElementById('whatsNewV12Modal');
        if (!el) return;
        var track   = el.querySelector('#wn12Track');
        var dotsWrap = el.querySelector('#wn12Dots');
        var prevBtn = el.querySelector('#wn12Prev');
        var nextBtn = el.querySelector('#wn12Next');
        var endWrap = el.querySelector('#wn12End');
        var slides  = track ? track.children.length : 0;
        if (!track || !slides) return;
        var idx = 0;

        for (var i = 0; i < slides; i++) {
            var b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
            (function (n) { b.addEventListener('click', function () { go(n); }); })(i);
            dotsWrap.appendChild(b);
        }
        function render() {
            track.style.transform = 'translateX(-' + (idx * 100) + '%)';
            Array.prototype.forEach.call(dotsWrap.children, function (d, i) { d.classList.toggle('active', i === idx); });
            prevBtn.disabled = idx === 0;
            var last = idx === slides - 1;
            nextBtn.style.display = last ? 'none' : '';
            endWrap.classList.toggle('show', last);
        }
        function go(i) { idx = Math.max(0, Math.min(slides - 1, i)); render(); }
        nextBtn.addEventListener('click', function () { go(idx + 1); });
        prevBtn.addEventListener('click', function () { go(idx - 1); });
        el.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight') go(idx + 1);
            if (e.key === 'ArrowLeft')  go(idx - 1);
        });
        el.addEventListener('show.bs.modal', function () { idx = 0; render(); });

        var OPT_OUT_KEY = 'whatsNewV12_optOut';
        function modal() { return bootstrap.Modal.getOrCreateInstance(el); }
        el.querySelector('#wn12DontShow').addEventListener('click', function () {
            try { localStorage.setItem(OPT_OUT_KEY, '1'); } catch (e) {}
            modal().hide();
        });
        render();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
</script>
