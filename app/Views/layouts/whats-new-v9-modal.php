<?php
// "What's New" v10.2.0 — step-by-step wizard. Same display policy as before
// (per-login session, 2-day window from first sight, opt-out persistent),
// but bumping VERSION below to v10_2_0 deliberately RESETS the timer +
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

    /* ====================================================================== */
    /* v10.2.0 — animated change-slides (polish / perf / crumb / forum / bv2)  */
    /* ====================================================================== */

    /* ===== Polish slide ===== */
    .wn-polish {
        position: absolute; inset: 14px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }
    .wn-polish-card {
        background: #1e293b;
        border: 1px solid rgba(148,163,184,.18);
        border-radius: 10px;
        padding: 10px 10px 12px;
        display: flex; flex-direction: column; gap: 8px;
        position: relative; overflow: hidden;
        min-width: 0;
    }
    .wn-polish-label {
        font-size: .62rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #94a3b8;
        display: inline-flex; align-items: center; gap: 6px;
        white-space: nowrap;
    }
    .wn-polish-label i { color: #a5b4fc; font-size: .78rem; }
    .wn-polish-sidebar {
        flex: 1;
        background: rgba(99,102,241,.08);
        border: 1px solid rgba(148,163,184,.12);
        border-radius: 8px;
        padding: 8px 7px;
        display: flex; flex-direction: column; gap: 6px;
        position: relative;
    }
    .wn-polish-logo {
        width: 26px; height: 26px; border-radius: 6px;
        background: linear-gradient(135deg, #4F46E5, #8b5cf6);
        display: inline-flex; align-items: center; justify-content: center;
        color: #fde68a; font-size: .8rem;
        position: relative; overflow: hidden;
        box-shadow: 0 2px 6px rgba(79,70,229,.35);
    }
    .wn-polish-logo-flash {
        position: absolute; inset: 0;
        background: #f8fafc;
        animation: wnPolishFlicker 4.4s ease-in-out infinite;
    }
    @keyframes wnPolishFlicker {
        0%      { opacity: .85; }
        6%      { opacity: 0; }
        10%     { opacity: .6; }
        14%     { opacity: 0; }
        18%,100%{ opacity: 0; }
    }
    .wn-polish-row {
        height: 6px; border-radius: 3px;
        background: rgba(148,163,184,.22);
        width: 80%;
    }
    .wn-polish-row-b { width: 62%; background: rgba(148,163,184,.16); }
    .wn-polish-row-c { width: 70%; background: rgba(148,163,184,.16); }
    .wn-polish-appt {
        flex: 1;
        position: relative;
        background: #0f172a;
        border: 1px solid rgba(148,163,184,.18);
        border-radius: 12px 6px 6px 12px;
        overflow: hidden;
        display: flex;
        animation: wnPolishCorner 4.4s ease-in-out infinite;
    }
    @keyframes wnPolishCorner {
        0%, 30%   { border-radius: 12px 14px 14px 12px; }
        55%, 90%  { border-radius: 12px 4px 4px 12px; }
        100%      { border-radius: 12px 14px 14px 12px; }
    }
    .wn-polish-strip {
        width: 5px;
        background: linear-gradient(180deg, #6366f1, #8b5cf6);
        flex-shrink: 0;
    }
    .wn-polish-appt-body {
        flex: 1;
        padding: 8px 9px;
        display: flex; flex-direction: column; gap: 6px; justify-content: center;
    }
    .wn-polish-appt-name {
        height: 7px; border-radius: 3px;
        background: rgba(226,232,240,.55);
        width: 78%;
    }
    .wn-polish-appt-time {
        height: 6px; border-radius: 3px;
        background: rgba(56,189,248,.55);
        width: 50%;
    }
    .wn-polish-field {
        flex: 1;
        display: flex; flex-direction: column; gap: 5px;
        position: relative;
    }
    .wn-polish-input {
        height: 22px;
        border-radius: 6px;
        border: 1px dashed rgba(148,163,184,.4);
        background: rgba(15,23,42,.6);
        color: #cbd5e1; font-size: .68rem;
        display: inline-flex; align-items: center; gap: 5px;
        padding: 0 7px;
    }
    .wn-polish-input i { color: #a5b4fc; font-size: .78rem; }
    .wn-polish-input span {
        height: 5px; border-radius: 3px;
        background: rgba(148,163,184,.3);
        flex: 1;
    }
    .wn-polish-warn {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .58rem; color: #fca5a5;
        background: rgba(239,68,68,.12);
        border-left: 2px solid #ef4444;
        padding: 3px 6px; border-radius: 0 4px 4px 0;
        animation: wnPolishWarn 4.4s ease-in-out infinite;
        transform-origin: left center;
    }
    .wn-polish-warn i { font-size: .68rem; color: #ef4444; }
    .wn-polish-warn-text {
        height: 4px; border-radius: 2px;
        background: rgba(252,165,165,.55);
        width: 70px;
    }
    @keyframes wnPolishWarn {
        0%, 30%   { opacity: 1; transform: translateX(0) scaleY(1); }
        45%, 90%  { opacity: 0; transform: translateX(-6px) scaleY(.4); }
        100%      { opacity: 1; transform: translateX(0) scaleY(1); }
    }
    .wn-polish-ok {
        position: absolute;
        left: 0; right: 0;
        top: 27px;
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .62rem; font-weight: 600; color: #6ee7b7;
        background: rgba(16,185,129,.12);
        border-left: 2px solid #10b981;
        padding: 3px 6px; border-radius: 0 4px 4px 0;
        animation: wnPolishOk 4.4s ease-in-out infinite;
        transform-origin: left center;
    }
    .wn-polish-ok i { font-size: .72rem; color: #10b981; }
    @keyframes wnPolishOk {
        0%, 35%   { opacity: 0; transform: translateX(-6px) scale(.85); }
        55%, 90%  { opacity: 1; transform: translateX(0) scale(1); }
        100%      { opacity: 0; transform: translateX(-6px) scale(.85); }
    }
    .wn-polish-c1 { animation: wnPolishLift 4.4s ease-in-out infinite; }
    .wn-polish-c2 { animation: wnPolishLift 4.4s ease-in-out .15s infinite; }
    .wn-polish-c3 { animation: wnPolishLift 4.4s ease-in-out .3s infinite; }
    @keyframes wnPolishLift {
        0%, 30%   { box-shadow: 0 0 0 0 rgba(99,102,241,0); border-color: rgba(148,163,184,.18); }
        55%, 85%  { box-shadow: 0 4px 14px rgba(99,102,241,.18); border-color: rgba(165,180,252,.4); }
        100%      { box-shadow: 0 0 0 0 rgba(99,102,241,0); border-color: rgba(148,163,184,.18); }
    }

    /* ===== Performance slide ===== */
    .wn-perf {
        position: absolute; inset: 14px;
        background: #1e293b; border-radius: 10px;
        border: 1px solid rgba(148,163,184,.18);
        padding: 12px 14px;
        display: flex; flex-direction: column; gap: 10px;
        text-align: left; overflow: hidden;
    }
    .wn-perf-head {
        display: flex; align-items: center; gap: 10px;
    }
    .wn-perf-icon {
        width: 28px; height: 28px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(245,158,11,.18); color: #fbbf24; font-size: .95rem;
        animation: wnPerfBoltPulse 1.6s ease-in-out infinite;
    }
    @keyframes wnPerfBoltPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251,191,36,0); transform: scale(1); }
        50%      { box-shadow: 0 0 0 7px rgba(251,191,36,.18); transform: scale(1.08); }
    }
    .wn-perf-title {
        color: #e2e8f0; font-size: .85rem; font-weight: 600; flex: 1;
    }
    .wn-perf-delta {
        color: #6ee7b7; font-size: .72rem; font-weight: 700;
        background: rgba(16,185,129,.14);
        border: 1px solid rgba(16,185,129,.35);
        padding: 2px 8px; border-radius: 999px; letter-spacing: .3px;
    }
    .wn-perf-races {
        display: flex; flex-direction: column; gap: 6px;
    }
    .wn-perf-race {
        display: grid; grid-template-columns: 52px 1fr 38px;
        align-items: center; gap: 8px;
    }
    .wn-perf-label {
        color: #94a3b8; font-size: .68rem; text-transform: uppercase;
        letter-spacing: .5px; font-weight: 600;
    }
    .wn-perf-track {
        position: relative; height: 8px; border-radius: 999px;
        background: rgba(148,163,184,.12);
        border: 1px solid rgba(148,163,184,.18);
        overflow: hidden;
    }
    .wn-perf-fill {
        position: absolute; inset: 0 auto 0 0; height: 100%;
        border-radius: 999px; width: 0;
    }
    .wn-perf-fill-slow {
        background: linear-gradient(90deg, #f59e0b, #ef4444);
        animation: wnPerfSlowFill 3.2s ease-in-out infinite;
    }
    .wn-perf-fill-fast {
        background: linear-gradient(90deg, #818cf8, #10b981);
        box-shadow: 0 0 12px rgba(16,185,129,.45);
        animation: wnPerfFastFill 3.2s ease-out infinite;
    }
    @keyframes wnPerfSlowFill {
        0%       { width: 0; }
        78%      { width: 100%; }
        85%, 100%{ width: 100%; }
    }
    @keyframes wnPerfFastFill {
        0%, 8%   { width: 0; }
        18%      { width: 100%; }
        85%, 100%{ width: 100%; }
    }
    .wn-perf-bolt {
        position: absolute; top: 50%; left: 0;
        transform: translate(-50%, -50%);
        color: #fde68a; font-size: .7rem;
        text-shadow: 0 0 8px rgba(251,191,36,.7);
        animation: wnPerfBoltSlide 3.2s ease-out infinite;
    }
    @keyframes wnPerfBoltSlide {
        0%, 8%   { left: 0;    opacity: 0; }
        14%      { opacity: 1; }
        18%      { left: 100%; opacity: 1; }
        24%      { opacity: 0; }
        85%, 100%{ left: 100%; opacity: 0; }
    }
    .wn-perf-time {
        font-size: .72rem; font-weight: 700;
        font-variant-numeric: tabular-nums; text-align: right;
    }
    .wn-perf-time-slow { color: #fca5a5; }
    .wn-perf-time-fast {
        color: #6ee7b7;
        animation: wnPerfTimePop 3.2s ease-out infinite;
    }
    @keyframes wnPerfTimePop {
        0%, 15%  { transform: scale(.85); opacity: .4; }
        22%      { transform: scale(1.15); opacity: 1; }
        30%, 100%{ transform: scale(1); opacity: 1; }
    }
    .wn-perf-chips {
        display: flex; gap: 6px; flex-wrap: wrap; margin-top: auto;
    }
    .wn-perf-chip {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .68rem; font-weight: 600; color: #cbd5e1;
        background: rgba(99,102,241,.12);
        border: 1px solid rgba(129,140,248,.35);
        padding: 3px 8px; border-radius: 999px;
        opacity: 0; transform: translateY(4px);
    }
    .wn-perf-chip i { color: #a5b4fc; font-size: .78rem; }
    .wn-perf-chip-1 { animation: wnPerfChipIn .5s ease-out .35s forwards; }
    .wn-perf-chip-2 { animation: wnPerfChipIn .5s ease-out .65s forwards; }
    .wn-perf-chip-3 { animation: wnPerfChipIn .5s ease-out .95s forwards; }
    @keyframes wnPerfChipIn {
        0%   { opacity: 0; transform: translateY(4px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    /* ===== Unified breadcrumb slide ===== */
    .wn-crumb-scene {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 14px; padding: 16px;
    }
    .wn-crumb-pill {
        display: inline-flex; align-items: center;
        gap: 8px;
        background: rgba(30, 41, 59, .85);
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 999px;
        padding: 6px 14px 6px 6px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .35),
                    inset 0 1px 0 rgba(255, 255, 255, .04);
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
        max-width: 92%;
        overflow: hidden;
        position: relative;
    }
    .wn-crumb-pill::before {
        content: "";
        position: absolute; inset: 0;
        border-radius: 999px;
        background: linear-gradient(120deg,
            rgba(99, 102, 241, .12) 0%,
            rgba(139, 92, 246, .08) 50%,
            rgba(56, 189, 248, .10) 100%);
        pointer-events: none;
    }
    .wn-crumb-back {
        width: 30px; height: 30px;
        border-radius: 50%;
        flex: 0 0 30px;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #6366f1, #4F46E5);
        color: #fff;
        font-size: .95rem;
        box-shadow: 0 2px 8px rgba(79, 70, 229, .45);
        position: relative; z-index: 1;
        animation: wnCrumbBack 2.6s ease-in-out infinite;
    }
    .wn-crumb-back::after {
        content: "";
        position: absolute; inset: -3px;
        border-radius: 50%;
        border: 1.5px solid rgba(129, 140, 248, .55);
        animation: wnCrumbRing 2.6s ease-out infinite;
    }
    .wn-crumb-back i { animation: wnCrumbArrow 2.6s ease-in-out infinite; }
    .wn-crumb-track {
        position: relative;
        height: 22px;
        min-width: 200px;
        max-width: 320px;
        flex: 1 1 auto;
        overflow: hidden;
    }
    .wn-crumb-route {
        position: absolute; inset: 0;
        display: inline-flex; align-items: center;
        gap: 6px;
        font-size: .78rem;
        color: #cbd5e1;
        white-space: nowrap;
        opacity: 0;
        transform: translateY(8px);
        animation: wnCrumbSwap 6s ease-in-out infinite;
    }
    .wn-crumb-route:nth-child(1) { animation-delay: 0s; }
    .wn-crumb-route:nth-child(2) { animation-delay: 2s; }
    .wn-crumb-route:nth-child(3) { animation-delay: 4s; }
    .wn-crumb-route > i:first-child {
        color: #a5b4fc;
        font-size: .85rem;
    }
    .wn-crumb-root { color: #94a3b8; }
    .wn-crumb-mid  { color: #cbd5e1; }
    .wn-crumb-leaf {
        color: #fde68a;
        font-weight: 600;
        text-shadow: 0 0 8px rgba(251, 191, 36, .25);
    }
    .wn-crumb-sep {
        color: #64748b;
        font-size: .65rem;
        opacity: .8;
    }
    .wn-crumb-dots {
        display: inline-flex; gap: 5px;
    }
    .wn-crumb-dot {
        width: 5px; height: 5px; border-radius: 50%;
        background: rgba(148, 163, 184, .35);
        animation: wnCrumbDot 6s ease-in-out infinite;
    }
    .wn-crumb-dot:nth-child(1) { animation-delay: 0s; }
    .wn-crumb-dot:nth-child(2) { animation-delay: 2s; }
    .wn-crumb-dot:nth-child(3) { animation-delay: 4s; }
    .wn-crumb-pages {
        display: flex; flex-wrap: wrap;
        justify-content: center; gap: 5px 6px;
        max-width: 100%;
    }
    .wn-crumb-tag {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px;
        font-size: .65rem;
        color: #cbd5e1;
        background: rgba(99, 102, 241, .12);
        border: 1px solid rgba(129, 140, 248, .25);
        border-radius: 999px;
        animation: wnCrumbTag 6s ease-in-out infinite;
    }
    .wn-crumb-tag i { font-size: .7rem; color: #a5b4fc; }
    .wn-crumb-tag:nth-child(1) { animation-delay: 0s; }
    .wn-crumb-tag:nth-child(2) { animation-delay: .4s; }
    .wn-crumb-tag:nth-child(3) { animation-delay: .8s; }
    .wn-crumb-tag:nth-child(4) { animation-delay: 1.2s; }
    .wn-crumb-tag:nth-child(5) { animation-delay: 1.6s; }
    @keyframes wnCrumbSwap {
        0%    { opacity: 0; transform: translateY(8px); }
        5%    { opacity: 1; transform: translateY(0); }
        30%   { opacity: 1; transform: translateY(0); }
        35%   { opacity: 0; transform: translateY(-8px); }
        100%  { opacity: 0; transform: translateY(8px); }
    }
    @keyframes wnCrumbBack {
        0%, 100% { transform: scale(1); box-shadow: 0 2px 8px rgba(79, 70, 229, .45); }
        50%      { transform: scale(1.08); box-shadow: 0 2px 14px rgba(99, 102, 241, .7); }
    }
    @keyframes wnCrumbArrow {
        0%, 70%, 100% { transform: translateX(0); }
        35%           { transform: translateX(-3px); }
    }
    @keyframes wnCrumbRing {
        0%   { opacity: .7; transform: scale(1); }
        70%  { opacity: 0;  transform: scale(1.45); }
        100% { opacity: 0;  transform: scale(1.45); }
    }
    @keyframes wnCrumbDot {
        0%, 5%   { background: #818cf8; transform: scale(1.25); }
        33%, 100%{ background: rgba(148, 163, 184, .35); transform: scale(1); }
    }
    @keyframes wnCrumbTag {
        0%, 100% { background: rgba(99, 102, 241, .12); border-color: rgba(129, 140, 248, .25); color: #cbd5e1; }
        8%, 20%  { background: rgba(245, 158, 11, .18); border-color: rgba(251, 191, 36, .55); color: #fde68a; }
    }

    /* ===== Forum removed slide ===== */
    .wn-forum {
        position: absolute; inset: 14px;
        display: flex; flex-direction: column; gap: 12px;
        text-align: left;
    }
    .wn-forum-dock {
        background: #1e293b;
        border: 1px solid rgba(148,163,184,.18);
        border-radius: 12px;
        padding: 10px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        flex: 1;
        min-height: 0;
    }
    .wn-forum-dock-item {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 4px;
        border-radius: 9px;
        padding: 8px 4px;
        background: rgba(148,163,184,.06);
        border: 1px solid rgba(148,163,184,.12);
        color: #94a3b8;
        font-size: .62rem;
        font-weight: 600;
        letter-spacing: .02em;
        min-width: 0;
    }
    .wn-forum-dock-item > i {
        font-size: 1.05rem;
        line-height: 1;
    }
    .wn-forum-dock-item > span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .wn-forum-dock-slot {
        position: relative;
        overflow: hidden;
        background: rgba(99,102,241,.10);
        border-color: rgba(129,140,248,.35);
        padding: 0;
    }
    .wn-forum-tile {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 4px;
        border-radius: 8px;
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .02em;
        padding: 6px 4px;
    }
    .wn-forum-tile > i { font-size: 1.05rem; line-height: 1; }
    .wn-forum-tile > span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .wn-forum-tile-old {
        color: #cbd5e1;
        background: rgba(148,163,184,.10);
        animation: wnForumOld 5s ease-in-out infinite;
    }
    .wn-forum-tile-new {
        color: #a5b4fc;
        background: linear-gradient(160deg, rgba(99,102,241,.28), rgba(139,92,246,.22));
        box-shadow: inset 0 0 0 1px rgba(129,140,248,.55), 0 6px 18px -8px rgba(99,102,241,.6);
        transform: translateY(110%);
        opacity: 0;
        animation: wnForumNew 5s ease-in-out infinite;
    }
    .wn-forum-strike {
        position: absolute;
        left: 10%; right: 10%;
        top: 50%;
        height: 2px;
        background: #ef4444;
        border-radius: 2px;
        transform: scaleX(0);
        transform-origin: left center;
        animation: wnForumStrike 5s ease-in-out infinite;
    }
    .wn-forum-spark {
        position: absolute;
        inset: -2px;
        border-radius: 9px;
        background: radial-gradient(circle at 50% 50%, rgba(165,180,252,.55), transparent 60%);
        opacity: 0;
        animation: wnForumSpark 5s ease-in-out infinite;
        pointer-events: none;
    }
    .wn-forum-status {
        display: flex; align-items: center; justify-content: center;
        gap: 10px;
        font-size: .68rem;
        font-weight: 600;
    }
    .wn-forum-tag {
        display: inline-flex; align-items: center;
        gap: 5px;
        padding: 4px 9px;
        border-radius: 999px;
        border: 1px solid transparent;
        line-height: 1;
    }
    .wn-forum-tag > i { font-size: .8rem; }
    .wn-forum-tag-removed {
        color: #fca5a5;
        background: rgba(239,68,68,.12);
        border-color: rgba(239,68,68,.35);
        animation: wnForumTagOld 5s ease-in-out infinite;
    }
    .wn-forum-tag-added {
        color: #a5b4fc;
        background: rgba(99,102,241,.15);
        border-color: rgba(129,140,248,.45);
        animation: wnForumTagNew 5s ease-in-out infinite;
    }
    .wn-forum-arrow {
        color: #64748b;
        font-size: .85rem;
        line-height: 1;
        display: inline-flex;
        animation: wnForumArrow 5s ease-in-out infinite;
    }
    @keyframes wnForumOld {
        0%, 18%   { transform: translateY(0); opacity: 1; filter: none; }
        32%       { transform: translateY(0); opacity: .85; filter: grayscale(.6); }
        46%, 86%  { transform: translateY(-115%); opacity: 0; filter: grayscale(1); }
        100%      { transform: translateY(0); opacity: 1; filter: none; }
    }
    @keyframes wnForumNew {
        0%, 38%   { transform: translateY(110%); opacity: 0; }
        52%       { transform: translateY(0); opacity: 1; }
        80%       { transform: translateY(0); opacity: 1; }
        92%, 100% { transform: translateY(110%); opacity: 0; }
    }
    @keyframes wnForumStrike {
        0%, 18%   { transform: scaleX(0); opacity: 0; }
        30%       { transform: scaleX(1); opacity: 1; }
        42%       { transform: scaleX(1); opacity: 1; }
        50%, 100% { transform: scaleX(0); opacity: 0; }
    }
    @keyframes wnForumSpark {
        0%, 48%   { opacity: 0; transform: scale(.6); }
        58%       { opacity: 1; transform: scale(1.05); }
        72%       { opacity: .4; transform: scale(1); }
        82%, 100% { opacity: 0; transform: scale(.6); }
    }
    @keyframes wnForumTagOld {
        0%, 20%   { opacity: 1; transform: translateY(0); }
        40%, 80%  { opacity: .35; transform: translateY(0); }
        100%      { opacity: 1; transform: translateY(0); }
    }
    @keyframes wnForumTagNew {
        0%, 38%   { opacity: .35; transform: translateY(0) scale(.96); }
        55%, 80%  { opacity: 1;   transform: translateY(0) scale(1); }
        100%      { opacity: .35; transform: translateY(0) scale(.96); }
    }
    @keyframes wnForumArrow {
        0%, 30%   { transform: translateX(-2px); opacity: .5; }
        50%, 80%  { transform: translateX(2px);  opacity: 1; }
        100%      { transform: translateX(-2px); opacity: .5; }
    }

    /* ===== Smarter Patients Board slide ===== */
    .wn-bv2 {
        position: absolute; inset: 0;
        overflow: hidden;
        font-family: inherit;
    }
    .wn-bv2-bg {
        position: absolute; inset: 0;
        background:
            radial-gradient(circle at 18% 12%, rgba(99,102,241,.32), transparent 55%),
            radial-gradient(circle at 88% 92%, rgba(139,92,246,.28), transparent 60%),
            linear-gradient(135deg, rgba(56,189,248,.10), rgba(236,72,153,.10));
        animation: wnBv2Bg 7.2s ease-in-out infinite;
    }
    @keyframes wnBv2Bg {
        0%,100% { filter: hue-rotate(0deg) brightness(1); }
        50%     { filter: hue-rotate(18deg) brightness(1.08); }
    }
    .wn-bv2-head {
        position: absolute; top: 12px; left: 14px; right: 14px;
        display: flex; align-items: center; gap: 8px;
        font-size: .72rem; color: #e2e8f0;
    }
    .wn-bv2-logo {
        width: 22px; height: 22px; border-radius: 6px;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; font-size: .78rem;
        box-shadow: 0 4px 10px rgba(99,102,241,.45);
    }
    .wn-bv2-title { font-weight: 600; letter-spacing: .2px; }
    .wn-bv2-auto {
        margin-left: auto;
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 9px; border-radius: 999px;
        background: rgba(245,158,11,.18);
        color: #fde68a;
        border: 1px solid rgba(245,158,11,.45);
        font-size: .65rem; font-weight: 600;
        animation: wnBv2Auto 6.8s ease-in-out infinite;
    }
    .wn-bv2-auto i { font-size: .72rem; color: #fbbf24; }
    @keyframes wnBv2Auto {
        0%, 12%   { box-shadow: 0 0 0 0 rgba(251,191,36,0); transform: scale(1); }
        16%       { box-shadow: 0 0 0 6px rgba(251,191,36,.25); transform: scale(1.07); }
        22%, 100% { box-shadow: 0 0 0 0 rgba(251,191,36,0); transform: scale(1); }
    }
    .wn-bv2-cols {
        position: absolute; left: 14px; right: 14px; bottom: 14px;
        height: 92px;
        display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;
    }
    .wn-bv2-col {
        position: relative;
        border-radius: 9px;
        background: rgba(30,41,59,.72);
        border: 1px solid rgba(148,163,184,.18);
        padding: 8px 6px 6px;
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
    }
    .wn-bv2-coltag {
        display: block;
        height: 6px; width: 60%;
        border-radius: 999px;
        margin-bottom: 6px;
    }
    .wn-bv2-tag-a { background: #38bdf8; }
    .wn-bv2-tag-b { background: #f59e0b; }
    .wn-bv2-tag-c { background: #10b981; }
    .wn-bv2-col-b {
        border-color: rgba(245,158,11,.45);
        animation: wnBv2ColPulse 6.8s ease-in-out infinite;
    }
    @keyframes wnBv2ColPulse {
        0%, 30%   { box-shadow: 0 0 0 0 rgba(245,158,11,0); }
        38%, 52%  { box-shadow: 0 0 0 3px rgba(245,158,11,.35); border-color: rgba(245,158,11,.8); }
        60%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); }
    }
    .wn-bv2-slot {
        display: block; height: 28px;
        border-radius: 6px;
        border: 1.5px dashed rgba(245,158,11,.55);
        background: rgba(245,158,11,.08);
        opacity: 0;
        animation: wnBv2Slot 6.8s ease-in-out infinite;
    }
    @keyframes wnBv2Slot {
        0%, 18%   { opacity: 0; }
        24%, 50%  { opacity: 1; }
        56%, 100% { opacity: 0; }
    }
    .wn-bv2-card {
        position: absolute;
        top: 42px; left: 28px;
        width: 90px; height: 34px;
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(99,102,241,.95), rgba(139,92,246,.95));
        border: 1px solid rgba(165,180,252,.55);
        box-shadow: 0 8px 18px rgba(15,23,42,.55), 0 0 0 1px rgba(255,255,255,.04) inset;
        display: flex; flex-direction: column; gap: 4px;
        padding: 7px 9px;
        animation: wnBv2Card 6.8s cubic-bezier(.55,.05,.3,1) infinite;
        z-index: 3;
    }
    .wn-bv2-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #fde68a;
        box-shadow: 0 0 6px rgba(251,191,36,.7);
    }
    .wn-bv2-bar {
        height: 4px; border-radius: 999px;
        background: rgba(255,255,255,.6);
    }
    .wn-bv2-bar-1 { width: 70%; }
    .wn-bv2-bar-2 { width: 45%; background: rgba(255,255,255,.4); }
    @keyframes wnBv2Card {
        0%, 14%   { top: 42px; left: 28px; transform: scale(1) rotate(0deg); opacity: 1; }
        22%       { top: 42px; left: 28px; transform: scale(1.05) rotate(-2deg); }
        34%       { top: 70px; left: 50%; transform: translateX(-50%) scale(.9) rotate(2deg); opacity: .95; }
        44%, 78%  { top: 118px; left: 50%; transform: translateX(-50%) scale(.78) rotate(0deg); opacity: 1; }
        88%, 100% { top: 42px; left: 28px; transform: scale(1) rotate(0deg); opacity: 1; }
    }
    .wn-bv2-trail {
        position: absolute;
        top: 58px; left: 78px;
        width: 100px; height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(251,191,36,0), rgba(251,191,36,.9), rgba(251,191,36,0));
        transform: rotate(28deg); transform-origin: left center;
        opacity: 0;
        animation: wnBv2Trail 6.8s ease-in-out infinite;
        z-index: 2;
    }
    @keyframes wnBv2Trail {
        0%, 22%   { opacity: 0; transform: rotate(28deg) scaleX(.2); }
        30%       { opacity: 1; transform: rotate(28deg) scaleX(1); }
        42%, 100% { opacity: 0; transform: rotate(28deg) scaleX(1); }
    }
    .wn-bv2-note {
        position: absolute;
        top: 40px; right: 14px;
        width: 138px;
        background: #1e293b;
        border: 1px solid rgba(148,163,184,.22);
        border-radius: 10px;
        padding: 7px 8px;
        display: flex; flex-direction: column; gap: 5px;
        box-shadow: 0 10px 22px rgba(0,0,0,.4);
        opacity: 0; transform: translateY(6px) scale(.96);
        animation: wnBv2Note 6.8s ease-in-out infinite;
        z-index: 4;
    }
    @keyframes wnBv2Note {
        0%, 46%   { opacity: 0; transform: translateY(6px) scale(.96); }
        54%, 86%  { opacity: 1; transform: translateY(0) scale(1); }
        94%, 100% { opacity: 0; transform: translateY(6px) scale(.96); }
    }
    .wn-bv2-note-head {
        display: flex; align-items: center; gap: 5px;
        font-size: .62rem; color: #cbd5e1; font-weight: 600;
    }
    .wn-bv2-note-head i { color: #fbbf24; font-size: .72rem; }
    .wn-bv2-chips {
        display: flex; gap: 4px; flex-wrap: nowrap;
    }
    .wn-bv2-chip {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .55rem; font-weight: 600;
        padding: 2px 5px; border-radius: 5px;
        color: #e2e8f0;
        background: rgba(148,163,184,.15);
        border: 1px solid rgba(148,163,184,.25);
        opacity: 0; transform: translateY(4px);
    }
    .wn-bv2-chip i { font-size: .65rem; }
    .wn-bv2-chip-pdf i { color: #ef4444; }
    .wn-bv2-chip-xls i { color: #10b981; }
    .wn-bv2-chip-doc i { color: #60a5fa; }
    .wn-bv2-chip-pdf { animation: wnBv2Chip 6.8s ease-in-out infinite; animation-delay: 0s; }
    .wn-bv2-chip-xls { animation: wnBv2Chip 6.8s ease-in-out infinite; animation-delay: .15s; }
    .wn-bv2-chip-doc { animation: wnBv2Chip 6.8s ease-in-out infinite; animation-delay: .3s; }
    @keyframes wnBv2Chip {
        0%, 54%   { opacity: 0; transform: translateY(4px) scale(.85); }
        62%, 86%  { opacity: 1; transform: translateY(0) scale(1); }
        94%, 100% { opacity: 0; transform: translateY(4px) scale(.85); }
    }
    .wn-bv2-acts {
        display: flex; gap: 5px; margin-top: 1px;
    }
    .wn-bv2-btn {
        flex: 1;
        display: inline-flex; align-items: center; justify-content: center; gap: 3px;
        font-size: .58rem; font-weight: 600;
        padding: 3px 4px; border-radius: 5px;
        border: 1px solid transparent;
    }
    .wn-bv2-btn i { font-size: .65rem; }
    .wn-bv2-btn-add {
        color: #a5b4fc;
        background: rgba(99,102,241,.18);
        border-color: rgba(99,102,241,.45);
        animation: wnBv2Add 6.8s ease-in-out infinite;
    }
    .wn-bv2-btn-del {
        color: #fca5a5;
        background: rgba(239,68,68,.15);
        border-color: rgba(239,68,68,.4);
        animation: wnBv2Del 6.8s ease-in-out infinite;
    }
    @keyframes wnBv2Add {
        0%, 70%   { box-shadow: 0 0 0 0 rgba(99,102,241,0); transform: scale(1); }
        75%       { box-shadow: 0 0 0 4px rgba(99,102,241,.32); transform: scale(1.06); }
        82%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); transform: scale(1); }
    }
    @keyframes wnBv2Del {
        0%, 80%   { box-shadow: 0 0 0 0 rgba(239,68,68,0); transform: scale(1); }
        85%       { box-shadow: 0 0 0 4px rgba(239,68,68,.32); transform: scale(1.06); }
        92%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); transform: scale(1); }
    }

    /* ===== v10.2.0 slides — mobile (stage 178px) ===== */
    @media (max-width: 575.98px) {
        .wn-polish { inset: 10px; gap: 7px; }
        .wn-polish-card { padding: 7px 7px 8px; gap: 6px; border-radius: 8px; }
        .wn-polish-label { font-size: .54rem; gap: 4px; }
        .wn-polish-label i { font-size: .66rem; }
        .wn-polish-logo { width: 22px; height: 22px; font-size: .7rem; }
        .wn-polish-row { height: 4px; }
        .wn-polish-strip { width: 4px; }
        .wn-polish-appt-name { height: 5px; }
        .wn-polish-appt-time { height: 4px; }
        .wn-polish-input { height: 18px; font-size: .58rem; }
        .wn-polish-input i { font-size: .66rem; }
        .wn-polish-warn { font-size: .5rem; padding: 2px 5px; }
        .wn-polish-warn i { font-size: .58rem; }
        .wn-polish-warn-text { width: 44px; height: 3px; }
        .wn-polish-ok { font-size: .54rem; padding: 2px 5px; top: 22px; }
        .wn-polish-ok i { font-size: .62rem; }

        .wn-perf { inset: 10px; padding: 10px 12px; gap: 7px; }
        .wn-perf-title { font-size: .78rem; }
        .wn-perf-delta { font-size: .65rem; padding: 1px 6px; }
        .wn-perf-race { grid-template-columns: 44px 1fr 32px; gap: 6px; }
        .wn-perf-label { font-size: .6rem; }
        .wn-perf-time { font-size: .65rem; }
        .wn-perf-chip { font-size: .6rem; padding: 2px 6px; }
        .wn-perf-chip i { font-size: .68rem; }

        .wn-crumb-scene { gap: 10px; padding: 12px; }
        .wn-crumb-track { min-width: 160px; max-width: 240px; height: 20px; }
        .wn-crumb-route { font-size: .72rem; gap: 5px; }
        .wn-crumb-back { width: 26px; height: 26px; flex-basis: 26px; font-size: .82rem; }
        .wn-crumb-pill { padding: 5px 12px 5px 5px; }
        .wn-crumb-tag { font-size: .6rem; padding: 2px 6px; }

        .wn-forum { inset: 10px; gap: 8px; }
        .wn-forum-dock { padding: 8px; gap: 6px; }
        .wn-forum-dock-item { font-size: .56rem; padding: 6px 2px; }
        .wn-forum-dock-item > i { font-size: .95rem; }
        .wn-forum-tile { font-size: .56rem; }
        .wn-forum-tile > i { font-size: .95rem; }
        .wn-forum-status { font-size: .6rem; gap: 6px; }
        .wn-forum-tag { padding: 3px 7px; }

        .wn-bv2-head { top: 9px; font-size: .65rem; }
        .wn-bv2-logo { width: 19px; height: 19px; font-size: .65rem; }
        .wn-bv2-auto { padding: 3px 7px; font-size: .58rem; }
        .wn-bv2-cols { bottom: 10px; height: 72px; gap: 6px; }
        .wn-bv2-card { top: 36px; left: 22px; width: 76px; height: 28px; padding: 5px 7px; }
        .wn-bv2-note { width: 122px; top: 34px; right: 10px; padding: 6px 7px; }
        .wn-bv2-chip { font-size: .5rem; padding: 1px 4px; }
        .wn-bv2-btn { font-size: .52rem; padding: 2px 3px; }
    }
</style>

<div class="modal fade" id="whatsNewV9Modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-stars me-2"></i>What's New
          <span class="version-pill">v10.2.0</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="wn-viewport">
          <div class="wn-track" id="wnTrack">

            <!-- 1 — Welcome v10.2.0 -->
            <div class="wn-slide">
              <span class="wn-kicker">Polish &amp; Performance</span>
              <div class="wn-stage">
                <div class="wn-ver">
                  <span class="wn-ver-label">version</span>
                  <span class="wn-ver-num">10.2.0</span>
                </div>
              </div>
              <h3>Sharper, faster, more polished</h3>
              <p>v10.2.0 builds on the V10 redesign with a focused round of
                 polish + performance. Cleaner sidebar logo, 12-hour news
                 cache, faster Missed Appointments, a unified glass
                 breadcrumb, and bleed fixes across Payments &amp; Reports.</p>
            </div>

            <!-- 1b — v10.2.0 highlights -->
            <div class="wn-slide">
              <span class="wn-kicker">What's New in 10.2</span>
              <div class="wn-stage">
                <ul style="text-align:start;line-height:1.7;list-style:none;padding:0;margin:0;font-size:.95rem;">
                  <li style="margin-bottom:8px;"><i class="bi bi-lightning-charge-fill" style="color:#10b981;margin-right:8px;"></i><strong>No more logo flicker</strong> — theme + logo + favicon now resolve before first paint</li>
                  <li style="margin-bottom:8px;"><i class="bi bi-broadcast" style="color:#6366f1;margin-right:8px;"></i><strong>Ophthalmology news cached 12h</strong> + instant render from localStorage (no "Loading…" flash on refresh)</li>
                  <li style="margin-bottom:8px;"><i class="bi bi-speedometer2" style="color:#f59e0b;margin-right:8px;"></i><strong>Faster Missed Appointments</strong> — scoped to your doctor, 90-day window, indexed, and lazy-loaded</li>
                  <li style="margin-bottom:8px;"><i class="bi bi-bounding-box-circles" style="color:#0ea5e9;margin-right:8px;"></i><strong>Payments &amp; Reports bleed fixed</strong> — stats cards and tables now stay inside their rounded corners</li>
                  <li style="margin-bottom:8px;"><i class="bi bi-signpost-2-fill" style="color:#a855f7;margin-right:8px;"></i><strong>Unified glass breadcrumb</strong> across Boards, Patient, Appointment, Day Close + others</li>
                  <li style="margin-bottom:8px;"><i class="bi bi-newspaper" style="color:#ec4899;margin-right:8px;"></i><strong>News ticker badges</strong> — BREAKING / FDA / TRIAL / NEW chips with source pills</li>
                  <li style="margin-bottom:8px;"><i class="bi bi-circle-half" style="color:#14b8a6;margin-right:8px;"></i><strong>FAB triplet alignment</strong> — back-to-top, AI agent and dock launcher now share size + slot system</li>
                </ul>
              </div>
              <h3>v10.2.0 — change-list</h3>
              <p>Every fix above is also documented for the ortho fork so it
                 ships with the same polish.</p>
            </div>

            <!-- v10.2.0 #1 — UI/UX Polish -->
            <div class="wn-slide">
              <span class="wn-kicker">Polish</span>
              <div class="wn-stage">
                <div class="wn-polish">
                  <div class="wn-polish-card wn-polish-c1">
                    <div class="wn-polish-label"><i class="bi bi-stars" aria-hidden="true"></i>Logo</div>
                    <div class="wn-polish-sidebar">
                      <div class="wn-polish-logo">
                        <span class="wn-polish-logo-flash" aria-hidden="true"></span>
                        <i class="bi bi-heart-pulse-fill" aria-hidden="true"></i>
                      </div>
                      <span class="wn-polish-row"></span>
                      <span class="wn-polish-row wn-polish-row-b"></span>
                      <span class="wn-polish-row wn-polish-row-c"></span>
                    </div>
                  </div>
                  <div class="wn-polish-card wn-polish-c2">
                    <div class="wn-polish-label"><i class="bi bi-calendar2-event" aria-hidden="true"></i>Corner</div>
                    <div class="wn-polish-appt">
                      <span class="wn-polish-strip" aria-hidden="true"></span>
                      <div class="wn-polish-appt-body">
                        <span class="wn-polish-appt-name"></span>
                        <span class="wn-polish-appt-time"></span>
                      </div>
                    </div>
                  </div>
                  <div class="wn-polish-card wn-polish-c3">
                    <div class="wn-polish-label"><i class="bi bi-image" aria-hidden="true"></i>Upload</div>
                    <div class="wn-polish-field">
                      <div class="wn-polish-input"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><span></span></div>
                      <div class="wn-polish-warn">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        <span class="wn-polish-warn-text"></span>
                      </div>
                      <div class="wn-polish-ok">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                        <span>Clean</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <h3>Polish, end-to-end</h3>
              <p>A focused round of <strong>UI fixes</strong>: the sidebar logo
                 and favicon no longer flicker on refresh, Upcoming Appointments
                 cards now sit flush against the accent strip, and the noisy
                 <code>open_basedir</code> warnings under the Settings logo
                 uploads are gone.</p>
            </div>

            <!-- v10.2.0 #2 — Performance -->
            <div class="wn-slide">
              <span class="wn-kicker">Performance</span>
              <div class="wn-stage">
                <div class="wn-perf">
                  <div class="wn-perf-head">
                    <span class="wn-perf-icon" aria-hidden="true"><i class="bi bi-lightning-charge-fill"></i></span>
                    <span class="wn-perf-title">Page load</span>
                    <span class="wn-perf-delta">2.4s &rarr; 0.3s</span>
                  </div>
                  <div class="wn-perf-races">
                    <div class="wn-perf-race wn-perf-race-before">
                      <span class="wn-perf-label">Before</span>
                      <div class="wn-perf-track">
                        <div class="wn-perf-fill wn-perf-fill-slow"></div>
                      </div>
                      <span class="wn-perf-time wn-perf-time-slow">2.4s</span>
                    </div>
                    <div class="wn-perf-race wn-perf-race-after">
                      <span class="wn-perf-label">After</span>
                      <div class="wn-perf-track">
                        <div class="wn-perf-fill wn-perf-fill-fast"></div>
                        <div class="wn-perf-bolt" aria-hidden="true"><i class="bi bi-lightning-charge-fill"></i></div>
                      </div>
                      <span class="wn-perf-time wn-perf-time-fast">0.3s</span>
                    </div>
                  </div>
                  <div class="wn-perf-chips">
                    <span class="wn-perf-chip wn-perf-chip-1"><i class="bi bi-newspaper" aria-hidden="true"></i>12h news cache</span>
                    <span class="wn-perf-chip wn-perf-chip-2"><i class="bi bi-database-check" aria-hidden="true"></i>DB index</span>
                    <span class="wn-perf-chip wn-perf-chip-3"><i class="bi bi-arrow-down-circle" aria-hidden="true"></i>Lazy-load</span>
                  </div>
                </div>
              </div>
              <h3>Faster than ever</h3>
              <p>We trimmed seconds off your day. <strong>Ophthalmology news</strong>
                 renders instantly from cache, the <strong>missed-appointments</strong>
                 query uses a new doctor+date index, and below-the-fold cards
                 lazy-load as you scroll.</p>
            </div>

            <!-- v10.2.0 #3 — Unified Glass Breadcrumb -->
            <div class="wn-slide">
              <span class="wn-kicker">Navigation</span>
              <div class="wn-stage">
                <div class="wn-crumb-scene">
                  <div class="wn-crumb-pill">
                    <span class="wn-crumb-back" aria-hidden="true">
                      <i class="bi bi-arrow-left"></i>
                    </span>
                    <div class="wn-crumb-track">
                      <div class="wn-crumb-route">
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                        <span class="wn-crumb-root">Patients</span>
                        <i class="bi bi-chevron-right wn-crumb-sep" aria-hidden="true"></i>
                        <span class="wn-crumb-leaf">Ahmed Maher</span>
                      </div>
                      <div class="wn-crumb-route">
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                        <span class="wn-crumb-root">Patients</span>
                        <i class="bi bi-chevron-right wn-crumb-sep" aria-hidden="true"></i>
                        <span class="wn-crumb-mid">Mariam Hassan</span>
                        <i class="bi bi-chevron-right wn-crumb-sep" aria-hidden="true"></i>
                        <span class="wn-crumb-leaf">Appointment #312</span>
                      </div>
                      <div class="wn-crumb-route">
                        <i class="bi bi-cash-coin" aria-hidden="true"></i>
                        <span class="wn-crumb-root">Payments</span>
                        <i class="bi bi-chevron-right wn-crumb-sep" aria-hidden="true"></i>
                        <span class="wn-crumb-leaf">Daily Closure</span>
                      </div>
                    </div>
                  </div>
                  <div class="wn-crumb-dots" aria-hidden="true">
                    <span class="wn-crumb-dot"></span>
                    <span class="wn-crumb-dot"></span>
                    <span class="wn-crumb-dot"></span>
                  </div>
                  <div class="wn-crumb-pages" aria-hidden="true">
                    <span class="wn-crumb-tag"><i class="bi bi-grid-3x3-gap-fill"></i>Board</span>
                    <span class="wn-crumb-tag"><i class="bi bi-person-vcard"></i>Profile</span>
                    <span class="wn-crumb-tag"><i class="bi bi-calendar2-event"></i>Appointment</span>
                    <span class="wn-crumb-tag"><i class="bi bi-clipboard2-pulse"></i>Consult</span>
                    <span class="wn-crumb-tag"><i class="bi bi-cash-stack"></i>Day Close</span>
                  </div>
                </div>
              </div>
              <h3>A unified glass breadcrumb</h3>
              <p>One <strong>.app-breadcrumb</strong> component now powers
                 navigation across the Patients Board, Patient profile,
                 Appointment, Edit Consultation and the new Day Close page —
                 with a glass pill surface and a circular back arrow that
                 slides on hover.</p>
            </div>

            <!-- v10.2.0 #4 — Forum Removed -->
            <div class="wn-slide">
              <span class="wn-kicker">Cleanup</span>
              <div class="wn-stage">
                <div class="wn-forum">
                  <div class="wn-forum-dock" aria-hidden="true">
                    <div class="wn-forum-dock-item"><i class="bi bi-house-door-fill"></i><span>Home</span></div>
                    <div class="wn-forum-dock-item wn-forum-dock-slot">
                      <div class="wn-forum-tile wn-forum-tile-old">
                        <i class="bi bi-chat-square-dots"></i>
                        <span>Forum</span>
                        <span class="wn-forum-strike"></span>
                      </div>
                      <div class="wn-forum-tile wn-forum-tile-new">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        <span>Patients Board</span>
                        <span class="wn-forum-spark"></span>
                      </div>
                    </div>
                    <div class="wn-forum-dock-item"><i class="bi bi-calendar-week-fill"></i><span>Calendar</span></div>
                    <div class="wn-forum-dock-item"><i class="bi bi-people-fill"></i><span>Patients</span></div>
                  </div>
                  <div class="wn-forum-status" aria-hidden="true">
                    <span class="wn-forum-tag wn-forum-tag-removed"><i class="bi bi-trash"></i>Removed</span>
                    <span class="wn-forum-arrow"><i class="bi bi-arrow-right"></i></span>
                    <span class="wn-forum-tag wn-forum-tag-added"><i class="bi bi-stars"></i>Promoted</span>
                  </div>
                </div>
              </div>
              <h3>Out with the old Forum</h3>
              <p>The legacy Discussion module is gone. The <strong>Patients
                 Board</strong> now takes its slot in the navigation dock —
                 less clutter, more clinical signal where you actually need it.</p>
            </div>

            <!-- v10.2.0 #5 — Smarter Patients Board -->
            <div class="wn-slide">
              <span class="wn-kicker">Board Updates</span>
              <div class="wn-stage">
                <div class="wn-bv2">
                  <div class="wn-bv2-bg" aria-hidden="true"></div>
                  <div class="wn-bv2-head">
                    <span class="wn-bv2-logo" aria-hidden="true"><i class="bi bi-columns-gap"></i></span>
                    <span class="wn-bv2-title">Patients Board</span>
                    <span class="wn-bv2-auto">
                      <i class="bi bi-magic" aria-hidden="true"></i>
                      <span>Auto place</span>
                    </span>
                  </div>
                  <div class="wn-bv2-cols" aria-hidden="true">
                    <div class="wn-bv2-col wn-bv2-col-a">
                      <span class="wn-bv2-coltag wn-bv2-tag-a"></span>
                    </div>
                    <div class="wn-bv2-col wn-bv2-col-b">
                      <span class="wn-bv2-coltag wn-bv2-tag-b"></span>
                      <span class="wn-bv2-slot"></span>
                    </div>
                    <div class="wn-bv2-col wn-bv2-col-c">
                      <span class="wn-bv2-coltag wn-bv2-tag-c"></span>
                    </div>
                  </div>
                  <div class="wn-bv2-card" aria-hidden="true">
                    <span class="wn-bv2-dot"></span>
                    <span class="wn-bv2-bar wn-bv2-bar-1"></span>
                    <span class="wn-bv2-bar wn-bv2-bar-2"></span>
                  </div>
                  <div class="wn-bv2-trail" aria-hidden="true"></div>
                  <div class="wn-bv2-note" aria-hidden="true">
                    <div class="wn-bv2-note-head">
                      <i class="bi bi-sticky-fill" aria-hidden="true"></i>
                      <span>Note</span>
                    </div>
                    <div class="wn-bv2-chips">
                      <span class="wn-bv2-chip wn-bv2-chip-pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>PDF</span>
                      <span class="wn-bv2-chip wn-bv2-chip-xls"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i>XLS</span>
                      <span class="wn-bv2-chip wn-bv2-chip-doc"><i class="bi bi-file-earmark-word" aria-hidden="true"></i>DOC</span>
                    </div>
                    <div class="wn-bv2-acts">
                      <span class="wn-bv2-btn wn-bv2-btn-add"><i class="bi bi-plus-lg" aria-hidden="true"></i>Add</span>
                      <span class="wn-bv2-btn wn-bv2-btn-del"><i class="bi bi-trash" aria-hidden="true"></i>Delete</span>
                    </div>
                  </div>
                </div>
              </div>
              <h3>Smarter Patients Board</h3>
              <p>The <strong>Patients Board</strong> gets a one-click Auto-place
                 that drops every card in the right column, plus a refreshed
                 gradient look, notes with PDF, Word, Excel, PowerPoint and TXT
                 attachments, and a unified Add/Delete flow with a confirm modal.</p>
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
    const VERSION       = 'v10_2_0';
    const OPT_OUT_KEY   = 'whatsNew_' + VERSION + '_optOut';     // permanent
    const FIRST_SEEN_KEY= 'whatsNew_' + VERSION + '_firstSeen';  // ms epoch
    const SESSION_KEY   = 'whatsNew_' + VERSION + '_shownSession';
    const WINDOW_MS     = 2 * 24 * 60 * 60 * 1000;               // 2 days

    function shouldShow() {
        // v10.2.0+ — auto-show DISABLED so the wizard doesn't pop on
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

        // Reset to slide 1 whenever the modal opens (from any trigger).
        el.addEventListener('show.bs.modal', () => { idx = 0; render(); });

        // Use getOrCreateInstance so the hide buttons work regardless of
        // whether the modal was opened by us or by a data-bs-toggle trigger
        // (e.g. the dashboard's What's-New notice bar).
        function modal() { return bootstrap.Modal.getOrCreateInstance(el); }

        el.querySelector('#wnDontShow').addEventListener('click', function () {
            try { localStorage.setItem(OPT_OUT_KEY, '1'); } catch (e) {}
            modal().hide();
        });
        el.querySelector('#wnClose').addEventListener('click', function () {
            modal().hide();
        });

        render();

        // Auto-show is gated by shouldShow(); currently disabled so this branch
        // is dormant. The wizard still opens via the dashboard notice bar's
        // data-bs-toggle trigger, and now the navigation is wired regardless.
        if (shouldShow()) {
            try { sessionStorage.setItem(SESSION_KEY, '1'); } catch (e) {}
            setTimeout(() => { try { modal().show(); } catch (e) {} }, 800);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
