<?php
// "What's New" v10.1.0 — step-by-step wizard. Same display policy as before
// (per-login session, 2-day window from first sight, opt-out persistent),
// but bumping VERSION below to v10_1_0 deliberately RESETS the timer +
// opt-out for every browser, so the new wizard surfaces fresh on the
// next login. Included from layouts/main.php (doctor/admin) and
// secretary_main.php. Bump VERSION again in a future release to
// resurface the next wizard.
?>
<style>
    /* ---- shell ----------------------------------------------------------- */
    #whatsNewV9Modal .modal-dialog { max-width: 640px; }
    #whatsNewV9Modal .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        background: var(--card);
    }
    .dark #whatsNewV9Modal .modal-content {
        background: var(--card);
        color: #e2e8f0;
    }
    #whatsNewV9Modal .modal-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #4F46E5 100%);
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
        font-size: .92rem; color: #475569; margin: 0 auto; max-width: 460px;
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
        height: 210px; margin: 1rem auto 0; max-width: 480px;
        border-radius: 14px; position: relative; overflow: hidden;
        background: #0f172a;
        border: 1px solid rgba(148,163,184,.25);
        box-shadow: inset 0 0 40px rgba(0,0,0,.35);
    }

    /* ---- Slide 1 — animated version label -------------------------------- */
    .wn-ver {
        position:absolute; inset:0;
        display:flex; flex-direction:column;
        align-items:center; justify-content:center; gap:6px;
        background:
          radial-gradient(circle at 50% 45%, rgba(99,102,241,.30), transparent 60%);
    }
    .wn-ver-label {
        font-size:.8rem; font-weight:700; letter-spacing:.5em;
        text-transform:uppercase; color:#94a3b8;
        opacity:0; transform:translateY(8px);
        animation: wnVerLabel .8s ease-out .2s forwards;
    }
    .wn-ver-num {
        font-size:3.4rem; font-weight:900; line-height:1;
        background:linear-gradient(135deg,#818cf8 0%,#a78bfa 45%,#6366F1 100%);
        -webkit-background-clip:text; background-clip:text;
        -webkit-text-fill-color:transparent; color:transparent;
        filter:drop-shadow(0 4px 18px rgba(99,102,241,.45));
        transform:scale(.6); opacity:0;
        animation: wnVerNum .7s cubic-bezier(.34,1.56,.64,1) .35s forwards,
                   wnVerGlow 2.8s ease-in-out 1.1s infinite;
    }
    @keyframes wnVerLabel { to { opacity:1; transform:translateY(0); } }
    @keyframes wnVerNum   { to { opacity:1; transform:scale(1); } }
    @keyframes wnVerGlow {
        0%,100% { filter:drop-shadow(0 4px 14px rgba(99,102,241,.35)); }
        50%     { filter:drop-shadow(0 6px 26px rgba(56,189,248,.65)); }
    }

    /* ---- Slide 2 — AI Assistant card mockup ------------------------------ */
    .wn-ai {
        position:absolute; inset:18px;
        background:#1e293b; border-radius:10px;
        border:1px solid rgba(148,163,184,.18);
        padding:14px 16px; text-align:left;
        display:flex; flex-direction:column; gap:12px;
    }
    .wn-ai-head {
        display:flex; align-items:center; gap:10px; flex-wrap:wrap;
    }
    .wn-ai-icon {
        width:30px; height:30px; border-radius:8px;
        display:inline-flex; align-items:center; justify-content:center;
        background:rgba(245,158,11,.18); color:#fbbf24; font-size:1rem;
        animation: wnAiIcon 2.2s ease-in-out infinite;
    }
    @keyframes wnAiIcon {
        0%,100% { box-shadow:0 0 0 0 rgba(251,191,36,0); transform:scale(1); }
        50%     { box-shadow:0 0 0 8px rgba(251,191,36,.18); transform:scale(1.06); }
    }
    .wn-ai-title { font-weight:700; color:#f1f5f9; font-size:.95rem; }
    .wn-ai-badge {
        font-size:.66rem; font-weight:700;
        color:#fde68a; background:rgba(251,191,36,.14);
        border:1px solid rgba(251,191,36,.4);
        padding:3px 8px; border-radius:999px;
        margin-left:auto; white-space:nowrap;
    }
    .wn-ai-chips { display:flex; flex-wrap:wrap; gap:8px; }
    .wn-ai-chip {
        display:inline-flex; align-items:center; gap:6px;
        font-size:.74rem; padding:6px 11px; border-radius:999px;
        background:#0f172a; color:#e2e8f0;
        border:1px solid rgba(148,163,184,.25);
        opacity:0; transform:translateY(6px);
        animation: wnChipIn .6s ease-out forwards;
    }
    .wn-ai-chip i { color:#6366F1; }
    .wn-ai-chip:nth-child(1) { animation-delay:.5s; }
    .wn-ai-chip:nth-child(2) { animation-delay:.85s; }
    @keyframes wnChipIn { to { opacity:1; transform:translateY(0); } }

    /* ---- Slide 3 — Prior-visit summary bullets --------------------------- */
    .wn-sum {
        position:absolute; inset:18px;
        background:#1e293b; border-radius:10px;
        border:1px solid rgba(148,163,184,.18);
        padding:12px 14px; text-align:left;
        display:flex; flex-direction:column; gap:8px;
    }
    .wn-sum-btn {
        align-self:flex-start;
        display:inline-flex; align-items:center; gap:6px;
        font-size:.74rem; padding:5px 11px; border-radius:6px;
        color:#bfdbfe; background:rgba(59,130,246,.16);
        border:1px solid rgba(59,130,246,.45);
        animation: wnSumBtn 4.5s ease-in-out infinite;
    }
    @keyframes wnSumBtn {
        0%,100% { box-shadow:0 0 0 0 rgba(59,130,246,0); }
        12%     { box-shadow:0 0 0 4px rgba(59,130,246,.4); }
        25%     { box-shadow:0 0 0 0 rgba(59,130,246,0); }
    }
    .wn-sum-list {
        margin:0; padding:0; list-style:none;
        font-size:.78rem; color:#cbd5e1; line-height:1.55;
    }
    .wn-sum-list li {
        opacity:0; transform:translateY(4px);
        animation: wnBullet 4.5s ease-out infinite;
    }
    .wn-sum-list li em { color:#fbbf24; font-style:normal; font-weight:700; }
    .wn-sum-list li:nth-child(1) { animation-delay:.6s; }
    .wn-sum-list li:nth-child(2) { animation-delay:1.0s; }
    .wn-sum-list li:nth-child(3) { animation-delay:1.4s; }
    .wn-sum-list li:nth-child(4) { animation-delay:1.8s; }
    @keyframes wnBullet {
        0%   { opacity:0; transform:translateY(4px); }
        15%  { opacity:1; transform:translateY(0); }
        85%  { opacity:1; transform:translateY(0); }
        100% { opacity:0; transform:translateY(0); }
    }

    /* ---- Slide 4 — ICD-10 popover ---------------------------------------- */
    .wn-icd {
        position:absolute; inset:18px;
        background:#1e293b; border-radius:10px;
        border:1px solid rgba(148,163,184,.18);
        padding:12px 14px; text-align:left;
        display:flex; flex-direction:column; gap:8px;
    }
    .wn-icd-row1 {
        display:flex; align-items:center; gap:10px;
    }
    .wn-icd-input {
        flex:1; font-size:.76rem; color:#e2e8f0;
        background:#0f172a; border:1px solid rgba(148,163,184,.25);
        border-radius:6px; padding:5px 9px;
    }
    .wn-icd-btn {
        display:inline-flex; align-items:center; gap:6px;
        font-size:.72rem; padding:5px 10px; border-radius:6px;
        color:#bfdbfe; background:rgba(59,130,246,.16);
        border:1px solid rgba(59,130,246,.45); white-space:nowrap;
        animation: wnSumBtn 3.6s ease-in-out infinite;
    }
    .wn-icd-pop {
        background:#0f172a; border:1px solid rgba(148,163,184,.25);
        border-radius:8px; overflow:hidden;
        opacity:0; transform:translateY(-6px);
        animation: wnIcdPop 3.6s ease-out infinite;
    }
    @keyframes wnIcdPop {
        0%, 22% { opacity:0; transform:translateY(-6px); }
        32%     { opacity:1; transform:translateY(0); }
        90%     { opacity:1; transform:translateY(0); }
        100%    { opacity:0; transform:translateY(-6px); }
    }
    .wn-icd-pop-label {
        font-size:.62rem; font-weight:700; text-transform:uppercase;
        letter-spacing:.04em; color:#94a3b8; padding:6px 10px 2px;
    }
    .wn-icd-r {
        display:flex; align-items:center; gap:10px;
        padding:6px 10px; border-top:1px solid rgba(148,163,184,.12);
        font-size:.76rem;
    }
    .wn-icd-r b {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        color:#6366F1; font-weight:700;
    }
    .wn-icd-r span { flex:1; color:#cbd5e1; }
    .wn-icd-r i {
        font-size:.62rem; padding:2px 7px; border-radius:999px;
        background:rgba(251,191,36,.14); color:#fde68a; font-style:normal;
    }

    /* ---- Slide 5 — Drawing: animated eye SVG (ophthalmology) ------------- */
    .wn-eye-wrap {
        position:absolute; inset:0;
        display:flex; align-items:center; justify-content:center;
        background: radial-gradient(circle at 50% 50%, rgba(56,189,248,.18), transparent 70%);
    }
    .wn-eye {
        width: 88%; height: 88%;
    }
    .wn-eye-outline {
        fill:none; stroke:#60a5fa; stroke-width:2.5;
        stroke-linecap:round; stroke-linejoin:round;
        stroke-dasharray:760; stroke-dashoffset:760;
        animation: wnEyeOutline 6s ease-in-out infinite;
    }
    .wn-eye-iris {
        fill:rgba(14,165,233,.14); stroke:#4F46E5; stroke-width:2.5;
        stroke-dasharray:270; stroke-dashoffset:270; opacity:0;
        animation: wnEyeIris 6s ease-in-out infinite;
    }
    .wn-eye-rays {
        opacity:0; stroke:#4F46E5; stroke-width:1;
        animation: wnEyeRays 6s ease-in-out infinite;
    }
    .wn-eye-pupil { fill:#0f172a; opacity:0; animation: wnEyePupil 6s ease-in-out infinite; }
    .wn-eye-glint { fill:#fff; opacity:0; animation: wnEyeGlint 6s ease-in-out infinite; }
    .wn-eye-label {
        opacity:0; animation: wnEyeLabel 6s ease-in-out infinite;
    }
    .wn-eye-label line { stroke:#fbbf24; stroke-width:2; stroke-linecap:round; }
    .wn-eye-label text { fill:#fbbf24; font-weight:700; font-size:13px;
                         font-family: ui-monospace, monospace; }
    @keyframes wnEyeOutline {
        0%   { stroke-dashoffset:760; opacity:1; }
        18%  { stroke-dashoffset:0;   opacity:1; }
        88%  { stroke-dashoffset:0;   opacity:1; }
        100% { stroke-dashoffset:0;   opacity:0; }
    }
    @keyframes wnEyeIris {
        0%,18% { stroke-dashoffset:270; opacity:1; }
        30%    { stroke-dashoffset:0;   opacity:1; }
        88%    { stroke-dashoffset:0;   opacity:1; }
        100%   { stroke-dashoffset:0;   opacity:0; }
    }
    @keyframes wnEyeRays {
        0%,32% { opacity:0; }
        42%    { opacity:.6; }
        88%    { opacity:.6; }
        100%   { opacity:0; }
    }
    @keyframes wnEyePupil {
        0%,38% { opacity:0; }
        48%    { opacity:1; }
        88%    { opacity:1; }
        100%   { opacity:0; }
    }
    @keyframes wnEyeGlint {
        0%,50% { opacity:0; }
        60%    { opacity:1; }
        88%    { opacity:1; }
        100%   { opacity:0; }
    }
    @keyframes wnEyeLabel {
        0%,62% { opacity:0; }
        72%    { opacity:1; }
        88%    { opacity:1; }
        100%   { opacity:0; }
    }

    /* ---- Slide 6 — Bug Fixes -------------------------------------------- */
    .wn-bugs {
        position:absolute; inset:18px;
        background:#1e293b; border-radius:10px;
        border:1px solid rgba(148,163,184,.18);
        padding:14px 16px; text-align:left;
        list-style:none; margin:0;
        display:flex; flex-direction:column; justify-content:center;
        gap:0;
    }
    .wn-bugs li {
        display:flex; align-items:center; gap:12px;
        padding:8px 0; color:#e2e8f0; font-size:.82rem;
    }
    .wn-bugs li + li { border-top:1px solid rgba(148,163,184,.14); }
    .wn-bug-icon {
        width:28px; height:28px;
        display:inline-flex; align-items:center; justify-content:center;
        flex-shrink:0; position:relative;
    }
    .wn-bug-icon i {
        position:absolute; line-height:1;
    }
    .wn-bug-icon .bi-bug-fill {
        color:#ef4444; font-size:1.05rem;
        animation: wnBugOut 3.6s ease-in-out infinite;
    }
    .wn-bug-icon .bi-check-circle-fill {
        color:#10b981; font-size:1.15rem; opacity:0; transform:scale(.4);
        animation: wnBugIn 3.6s ease-in-out infinite;
    }
    @keyframes wnBugOut {
        0%,40% { opacity:1; transform:scale(1) rotate(0); }
        55%    { opacity:0; transform:scale(0) rotate(180deg); }
        100%   { opacity:0; transform:scale(0) rotate(180deg); }
    }
    @keyframes wnBugIn {
        0%,50% { opacity:0; transform:scale(.4); }
        65%    { opacity:1; transform:scale(1.15); }
        80%    { opacity:1; transform:scale(1); }
        100%   { opacity:1; transform:scale(1); }
    }
    .wn-bug-label { position:relative; flex:1; }
    .wn-bug-label::after {
        content:""; position:absolute; left:0; right:0; top:50%; height:1px;
        background:#94a3b8; transform-origin:left; transform:scaleX(0);
        animation: wnStrike 3.6s ease-in-out infinite;
    }
    @keyframes wnStrike {
        0%,30% { transform:scaleX(0); opacity:.65; }
        50%    { transform:scaleX(1); opacity:.65; }
        62%    { transform:scaleX(1); opacity:0; }
        100%   { transform:scaleX(1); opacity:0; }
    }
    .wn-bugs li:nth-child(2) .wn-bug-icon .bi-bug-fill,
    .wn-bugs li:nth-child(2) .wn-bug-icon .bi-check-circle-fill,
    .wn-bugs li:nth-child(2) .wn-bug-label::after { animation-delay:.45s; }
    .wn-bugs li:nth-child(3) .wn-bug-icon .bi-bug-fill,
    .wn-bugs li:nth-child(3) .wn-bug-icon .bi-check-circle-fill,
    .wn-bugs li:nth-child(3) .wn-bug-label::after { animation-delay:.9s; }

    /* ---- v10: new-look (light/dark glass) mockup ------------------------- */
    .wn-theme { position:absolute; inset:16px; display:flex; gap:12px; }
    .wn-theme-half {
        flex:1; border-radius:12px; padding:14px; position:relative; overflow:hidden;
        border:1px solid rgba(148,163,184,.25);
        display:flex; flex-direction:column; gap:9px;
    }
    .wn-theme-light { background:linear-gradient(160deg,#eef2ff 0%,#f8fafc 70%); }
    .wn-theme-dark  { background:linear-gradient(160deg,#1e293b 0%,#0b1220 70%); }
    .wn-theme-dot { width:26px; height:26px; border-radius:8px;
        background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 6px 14px rgba(99,102,241,.45); }
    .wn-theme-bar { height:8px; border-radius:99px; background:rgba(99,102,241,.55); width:80%;
        animation:wnThemeBar 2.6s ease-in-out infinite; }
    .wn-theme-bar.short { width:55%; opacity:.6; animation-delay:.3s; }
    .wn-theme-light .wn-theme-bar { background:rgba(99,102,241,.35); }
    .wn-theme-tag { position:absolute; bottom:10px; right:12px; font-size:.66rem; font-weight:700;
        letter-spacing:.04em; text-transform:uppercase; }
    .wn-theme-light .wn-theme-tag { color:#6366f1; }
    .wn-theme-dark  .wn-theme-tag { color:#a5b4fc; }
    @keyframes wnThemeBar { 0%,100% { transform:scaleX(.85); transform-origin:left; } 50% { transform:scaleX(1); } }

    /* ---- v10: Patients Board mockup -------------------------------------- */
    .wn-board { position:absolute; inset:16px; display:flex; gap:10px; }
    .wn-board-col {
        flex:1; background:rgba(148,163,184,.10); border:1px solid rgba(148,163,184,.20);
        border-radius:10px; padding:8px; display:flex; flex-direction:column; gap:7px;
    }
    .wn-board-col-head {
        font-size:.66rem; font-weight:700; color:#fff; text-align:center;
        background:var(--c,#6366f1); border-radius:6px; padding:3px 0; letter-spacing:.03em;
    }
    .wn-board-card { height:24px; border-radius:6px; background:#fff; border:1px solid rgba(148,163,184,.3);
        box-shadow:0 1px 3px rgba(15,23,42,.12); }
    .dark .wn-board-card { background:#0f172a; border-color:rgba(148,163,184,.22); }
    .wn-board-card--drag {
        border:1px solid #6366f1; box-shadow:0 8px 18px rgba(99,102,241,.4);
        animation:wnBoardDrag 3.2s ease-in-out infinite;
    }
    @keyframes wnBoardDrag {
        0%,12%   { transform:translate(0,0) rotate(0); opacity:1; }
        45%,55%  { transform:translate(0,-34px) rotate(-3deg); opacity:.92; }
        88%,100% { transform:translate(0,0) rotate(0); opacity:1; }
    }

    /* ---- v10: Auto Complete settings mockup ------------------------------ */
    .wn-acset { position:absolute; inset:20px; display:flex; flex-direction:column; gap:10px;
        justify-content:center; }
    .wn-acset-row {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        background:rgba(148,163,184,.10); border:1px solid rgba(148,163,184,.2);
        border-radius:10px; padding:10px 14px; font-size:.8rem; color:#e2e8f0;
    }
    .wn-acset-row span:first-child { color:#cbd5e1; }
    .wn-acset-sw { width:38px; height:21px; border-radius:99px; background:#475569; position:relative;
        flex-shrink:0; transition:background .3s; }
    .wn-acset-sw::after { content:''; position:absolute; top:2px; left:2px; width:17px; height:17px;
        border-radius:50%; background:#fff; transition:left .3s; }
    .wn-acset-sw.on { background:linear-gradient(135deg,#6366f1,#818cf8); }
    .wn-acset-sw.on::after { left:19px; }
    .wn-acset-sw.on { animation:wnSwPulse 2.8s ease-in-out infinite; }
    @keyframes wnSwPulse { 0%,100% { box-shadow:0 0 0 0 rgba(99,102,241,0); } 50% { box-shadow:0 0 0 5px rgba(99,102,241,.18); } }

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
        #whatsNewV9Modal .wn-stage { height: 178px; }
        .wn-ai-badge { display:none; }
        .wn-sum-list { font-size:.72rem; }
    }
</style>

<div class="modal fade" id="whatsNewV9Modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-stars me-2"></i>What's New
          <span class="version-pill">v10.1.0</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="wn-viewport">
          <div class="wn-track" id="wnTrack">

            <!-- 1 — Welcome v10.1.0 -->
            <div class="wn-slide">
              <span class="wn-kicker">Major Release</span>
              <div class="wn-stage">
                <div class="wn-ver">
                  <span class="wn-ver-label">version</span>
                  <span class="wn-ver-num">10.1.0</span>
                </div>
              </div>
              <h3>A bold new HClinic</h3>
              <p>Version 10 is our biggest update yet — a complete visual
                 redesign, a brand-new Patients Board, and full control over
                 smart Auto Complete. Here's a quick tour.</p>
            </div>

            <!-- 2 — New look: Glass / Indigo, Dark + Light -->
            <div class="wn-slide">
              <span class="wn-kicker">New Design</span>
              <div class="wn-stage">
                <div class="wn-theme">
                  <div class="wn-theme-half wn-theme-light">
                    <span class="wn-theme-dot"></span>
                    <div class="wn-theme-bar"></div>
                    <div class="wn-theme-bar short"></div>
                    <span class="wn-theme-tag">Light</span>
                  </div>
                  <div class="wn-theme-half wn-theme-dark">
                    <span class="wn-theme-dot"></span>
                    <div class="wn-theme-bar"></div>
                    <div class="wn-theme-bar short"></div>
                    <span class="wn-theme-tag">Dark</span>
                  </div>
                </div>
              </div>
              <h3>A premium new look</h3>
              <p>The whole app moves to a unified glassmorphism design system
                 in a refined <strong>Indigo</strong> palette — frosted cards,
                 cleaner depth and spacing, and a gorgeous <strong>Dark
                 mode</strong> that's easy on the eyes during long clinics.
                 Everything feels calmer and more consistent, page to page.</p>
            </div>

            <!-- 3 — Patients Board -->
            <div class="wn-slide">
              <span class="wn-kicker">New Feature</span>
              <div class="wn-stage">
                <div class="wn-board">
                  <div class="wn-board-col">
                    <div class="wn-board-col-head" style="--c:#6366f1">New</div>
                    <div class="wn-board-card"></div>
                    <div class="wn-board-card"></div>
                  </div>
                  <div class="wn-board-col">
                    <div class="wn-board-col-head" style="--c:#f59e0b">Awaiting</div>
                    <div class="wn-board-card wn-board-card--drag"></div>
                  </div>
                  <div class="wn-board-col">
                    <div class="wn-board-col-head" style="--c:#10b981">Done</div>
                    <div class="wn-board-card"></div>
                  </div>
                </div>
              </div>
              <h3>Meet the Patients Board</h3>
              <p>Organize your patients on a beautiful <strong>Trello-style
                 board</strong>. Drag cards between your own workflow columns,
                 group patients by stage, search and filter instantly, and
                 open a full profile in one click. Your whole clinic, at a
                 glance — with a quick overview that drills into detail.</p>
            </div>

            <!-- 4 — Auto Complete settings -->
            <div class="wn-slide">
              <span class="wn-kicker">Settings</span>
              <div class="wn-stage">
                <div class="wn-acset">
                  <div class="wn-acset-row">
                    <span>Consultation suggestions</span>
                    <span class="wn-acset-sw on"></span>
                  </div>
                  <div class="wn-acset-row">
                    <span>ICD-10 code suggestions</span>
                    <span class="wn-acset-sw on"></span>
                  </div>
                  <div class="wn-acset-row">
                    <span>Medication auto complete</span>
                    <span class="wn-acset-sw"></span>
                  </div>
                </div>
              </div>
              <h3>You're in control of Auto Complete</h3>
              <p>A new <strong>Auto Complete</strong> section in Settings lets
                 you turn the smart suggestions on or off — consultation
                 assists, ICD-10 codes, and medication name lookup — each with
                 a live animated preview. Set it once; it follows you across
                 the Edit Consultation page and prescribing.</p>
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
    // Display policy:
    //   • Once per login session, on every login, for a 2-day window
    //     starting the first time the wizard is ever seen.
    //   • "Don't show again" opts out permanently.
    // Bumping VERSION (e.g. v9_0_0 → v9_1_0) RESETS first-seen / opt-out /
    // session-shown for every browser, so the wizard resurfaces fresh.
    const VERSION       = 'v10_1_0';
    const OPT_OUT_KEY   = 'whatsNew_' + VERSION + '_optOut';     // permanent
    const FIRST_SEEN_KEY= 'whatsNew_' + VERSION + '_firstSeen';  // ms epoch
    const SESSION_KEY   = 'whatsNew_' + VERSION + '_shownSession';
    const WINDOW_MS     = 2 * 24 * 60 * 60 * 1000;               // 2 days

    function shouldShow() {
        // v10.1.0+ — auto-show DISABLED so the wizard doesn't pop on
        // login. Markup + gate logic preserved; to re-enable for a
        // future release, delete this `return false;` and bump VERSION.
        return false;
        try {
            if (localStorage.getItem(OPT_OUT_KEY) === '1') return false;
            if (sessionStorage.getItem(SESSION_KEY) === '1') return false;
            const now = Date.now();
            let first = parseInt(localStorage.getItem(FIRST_SEEN_KEY) || '0', 10);
            if (!first) {
                first = now;
                localStorage.setItem(FIRST_SEEN_KEY, String(first));
            }
            if (now - first > WINDOW_MS) return false;
            return true;
        } catch (e) {
            return false;
        }
    }

    function init() {
        if (!shouldShow()) return;
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

        el.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') go(idx + 1);
            if (e.key === 'ArrowLeft')  go(idx - 1);
        });

        const modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: true });

        el.querySelector('#wnDontShow').addEventListener('click', function () {
            try { localStorage.setItem(OPT_OUT_KEY, '1'); } catch (e) {}
            modal.hide();
        });
        el.querySelector('#wnClose').addEventListener('click', function () {
            modal.hide();
        });

        render();
        try { sessionStorage.setItem(SESSION_KEY, '1'); } catch (e) {}
        setTimeout(() => { try { modal.show(); } catch (e) {} }, 800);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
