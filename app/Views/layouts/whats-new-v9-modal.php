<?php
// "What's New" v11.0.0 — step-by-step wizard for the biggest release in
// the app's history. Auto-show stays DISABLED (return false in shouldShow);
// the wizard surfaces only via the dashboard celebration notice bar's
// data-bs-toggle trigger. Bumping VERSION below to v11_0_0 deliberately
// RESETS the timer / opt-out / session-shown for every browser so the
// new wizard surfaces fresh after the v11 deploy.
//
// Slide track: 21 slides covering every v11 feature with pure-CSS animated
// mockups. ALL v10 slides removed in this release.
?>
<style>
    /* ---- theme tokens (mockup stages follow html.dark / light) ---------- */
    #whatsNewV9Modal {
        --wn-bg-deep: #0f172a;
        --wn-bg-card: #1e293b;
        --wn-bg-surface: rgba(30,41,59,.85);
        --wn-bg-row: rgba(30,41,59,.7);
        --wn-bg-inset: rgba(15,23,42,.5);
        --wn-text: #e2e8f0;
        --wn-muted: #94a3b8;
        --wn-border: rgba(148,163,184,.25);
        --wn-stage-shadow: inset 0 0 40px rgba(0,0,0,.35);
        --wn-glass: linear-gradient(160deg,rgba(30,41,59,.92),rgba(15,23,42,.88));
        --wn-line: rgba(226,232,240,.55);
        --wn-line-muted: rgba(148,163,184,.4);
        --wn-accent-soft: #a5b4fc;
        --wn-chip-bg: rgba(30,41,59,.95);
    }
    html:not(.dark) #whatsNewV9Modal {
        --wn-bg-deep: #e8eef5;
        --wn-bg-card: #ffffff;
        --wn-bg-surface: rgba(255,255,255,.94);
        --wn-bg-row: rgba(255,255,255,.92);
        --wn-bg-inset: rgba(241,245,249,.9);
        --wn-text: #1e293b;
        --wn-muted: #64748b;
        --wn-border: rgba(148,163,184,.42);
        --wn-stage-shadow: inset 0 0 28px rgba(148,163,184,.14);
        --wn-glass: linear-gradient(160deg,rgba(255,255,255,.98),rgba(248,250,252,.95));
        --wn-line: rgba(30,41,59,.35);
        --wn-line-muted: rgba(100,116,139,.35);
        --wn-accent-soft: #4f46e5;
        --wn-chip-bg: rgba(255,255,255,.98);
    }

    /* ---- shell ----------------------------------------------------------- */
    #whatsNewV9Modal .modal-dialog { max-width: 640px; }
    #whatsNewV9Modal .modal-content { border: none; border-radius: 18px; overflow: hidden; background: var(--card); }
    .dark #whatsNewV9Modal .modal-content { background: var(--card); color: #e2e8f0; }
    #whatsNewV9Modal .modal-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #4F46E5 100%);
        color: #fff; border-bottom: none; padding: 1rem 1.4rem;
    }
    #whatsNewV9Modal .modal-title { font-weight: 700; letter-spacing: .3px; }
    #whatsNewV9Modal .version-pill {
        background: rgba(255,255,255,.18); padding: 3px 10px; border-radius: 999px;
        font-size: .72rem; font-weight: 600; margin-left: 8px;
    }
    #whatsNewV9Modal .modal-body { padding: 0; }

    .wn-viewport { position: relative; overflow: hidden; }
    .wn-track { display: flex; transition: transform .4s cubic-bezier(.4,0,.2,1); }
    .wn-slide { min-width: 100%; padding: 1.6rem 1.8rem 1.2rem; box-sizing: border-box; text-align: center; }
    .wn-slide h3 { font-size: 1.25rem; font-weight: 800; margin: .9rem 0 .35rem; color: #4338ca; }
    .dark .wn-slide h3 { color: #a5b4fc; }
    .wn-slide p { font-size: .92rem; color: #475569; margin: 0 auto; max-width: 460px; line-height: 1.55; }
    .dark .wn-slide p { color: #94a3b8; }
    .wn-kicker {
        display:inline-block; font-size:.7rem; font-weight:700;
        letter-spacing:.08em; text-transform:uppercase;
        color:#8b5cf6; background:rgba(139,92,246,.12);
        padding:3px 10px; border-radius:999px;
    }
    .wn-stage {
        height: 210px; margin: 1rem auto 0; max-width: 480px;
        border-radius: 14px; position: relative; overflow: hidden;
        background: var(--wn-bg-deep); border: 1px solid var(--wn-border);
        box-shadow: var(--wn-stage-shadow);
        transition: background .25s ease, border-color .25s ease, box-shadow .25s ease;
    }

    /* ---- prefetch slide (Speculation Rules) ---- */
    .wn-prefetch-scene { position: relative; width: 100%; max-width: 340px; margin: 0 auto; }
    .wn-prefetch-nav {
        display: flex; flex-direction: column; gap: .45rem;
        background: rgba(255,255,255,.06); border: 1px solid rgba(99,102,241,.25);
        border-radius: 14px; padding: .75rem; text-align: left;
    }
    .wn-prefetch-link {
        display: flex; align-items: center; gap: .55rem;
        padding: .45rem .6rem; border-radius: 10px; font-size: .78rem; font-weight: 600;
        color: #cbd5e1; background: rgba(15,23,42,.35);
        animation: wn-prefetch-pulse 4s ease-in-out infinite;
    }
    .wn-prefetch-link i { color: #818cf8; }
    .wn-prefetch-link.wn-prefetch-active {
        background: linear-gradient(135deg, rgba(99,102,241,.35), rgba(59,130,246,.2));
        color: #e0e7ff; box-shadow: 0 0 0 1px rgba(129,140,248,.45);
    }
    .wn-prefetch-badge {
        margin-left: auto; font-size: .62rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; padding: .15rem .4rem; border-radius: 999px;
        background: rgba(34,197,94,.2); color: #86efac;
        animation: wn-prefetch-badge 4s ease-in-out infinite;
    }
    .wn-prefetch-orb {
        position: absolute; right: -8px; top: 50%; width: 54px; height: 54px;
        border-radius: 50%; transform: translateY(-50%);
        background: radial-gradient(circle, rgba(99,102,241,.45) 0%, transparent 70%);
        animation: wn-prefetch-orb 4s ease-in-out infinite;
        pointer-events: none;
    }
    @keyframes wn-prefetch-pulse {
        0%, 18% { background: rgba(15,23,42,.35); box-shadow: none; }
        22%, 55% { background: linear-gradient(135deg, rgba(99,102,241,.35), rgba(59,130,246,.2)); box-shadow: 0 0 0 1px rgba(129,140,248,.45); }
        60%, 100% { background: rgba(15,23,42,.35); box-shadow: none; }
    }
    @keyframes wn-prefetch-badge {
        0%, 20% { opacity: 0; transform: scale(.85); }
        28%, 52% { opacity: 1; transform: scale(1); }
        58%, 100% { opacity: 0; transform: scale(.85); }
    }
    @keyframes wn-prefetch-orb {
        0%, 20% { opacity: 0; transform: translateY(-50%) scale(.6); }
        30%, 50% { opacity: 1; transform: translateY(-50%) scale(1); }
        58%, 100% { opacity: 0; transform: translateY(-50%) scale(.6); }
    }
    /* Prefetch: mockup stays in fixed stage; enable steps live BELOW it */
    .wn-slide-prefetch .wn-prefetch-stage {
        height: 128px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0;
    }
    .wn-slide-prefetch .wn-prefetch-scene { max-width: 300px; }
    .wn-slide-prefetch .wn-prefetch-nav { padding: .5rem .6rem; gap: .3rem; }
    .wn-slide-prefetch .wn-prefetch-link {
        padding: .3rem .5rem;
        font-size: .68rem;
    }
    .wn-slide-prefetch .wn-prefetch-tips {
        width: 100%;
        max-width: 100%;
        margin: .55rem auto 0;
        list-style: none;
        padding: .45rem .55rem;
        background: var(--wn-bg-inset);
        border: 1px solid var(--wn-border);
        border-radius: 10px;
        text-align: left;
        font-size: .62rem;
        line-height: 1.35;
        color: var(--wn-muted);
    }
    .wn-slide-prefetch .wn-prefetch-tips li {
        display: flex;
        align-items: flex-start;
        gap: .4rem;
        padding: .15rem 0;
    }
    .wn-slide-prefetch .wn-prefetch-tips li i {
        color: #818cf8;
        font-size: .72rem;
        flex-shrink: 0;
        margin-top: .05rem;
    }
    .wn-slide-prefetch .wn-prefetch-tips strong { color: var(--wn-text); font-weight: 700; }
    .wn-slide-prefetch .wn-prefetch-tips code {
        font-size: .88em;
        color: var(--wn-accent-soft);
        background: rgba(99,102,241,.1);
        padding: .02rem .2rem;
        border-radius: 3px;
        word-break: break-all;
    }
    .wn-slide-prefetch .wn-prefetch-tips em {
        color: var(--wn-accent-soft);
        font-style: normal;
        font-weight: 600;
    }
    .wn-slide-prefetch h3 { margin-top: .65rem; font-size: 1.1rem; }
    .wn-slide-prefetch p { font-size: .82rem; max-width: 100%; }
    @media (max-width: 575.98px) {
        .wn-slide-prefetch .wn-prefetch-stage { height: 112px; }
        .wn-slide-prefetch .wn-prefetch-link { font-size: .6rem; padding: .25rem .4rem; }
        .wn-slide-prefetch .wn-prefetch-tips { font-size: .56rem; line-height: 1.3; padding: .4rem .45rem; }
        .wn-slide-prefetch .wn-prefetch-tips li i { font-size: .65rem; }
        .wn-slide-prefetch h3 { font-size: 1rem; margin-top: .5rem; }
        .wn-slide-prefetch p { font-size: .76rem; }
    }

    /* ---- change-list slide (static, no animation) ---- */
    /* Tight grid sized to fit 12 items (6 rows × 2 cols) inside the 182px
       inner space (210px stage − 28px inset). Each row ~24px tall;
       6 × 24 + 16px padding = 160px — comfortable headroom. */
    .wn-cl-list {
        position: absolute; inset: 14px; background: var(--wn-bg-card);
        border-radius: 10px; border: 1px solid var(--wn-border);
        padding: 8px 12px; text-align: left; font-size: .78rem; color: var(--wn-text);
        list-style: none; margin: 0;
        display: grid; grid-template-columns: 1fr 1fr; gap: 0 12px;
        align-content: center;
        overflow: hidden;
    }
    .wn-cl-list li {
        display: flex; align-items: center; gap: 7px;
        padding: 2px 0; line-height: 1.25;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        min-width: 0;
    }
    .wn-cl-list li i { color: #a5b4fc; font-size: .92rem; flex-shrink: 0; }
    .wn-cl-list li span { overflow: hidden; text-overflow: ellipsis; min-width: 0; }
    @media (max-width: 575.98px) {
        .wn-cl-list { grid-template-columns: 1fr 1fr; gap: 0 8px; font-size: .68rem; padding: 6px 8px; inset: 10px; }
        .wn-cl-list li { gap: 5px; padding: 1px 0; }
        .wn-cl-list li i { font-size: .8rem; }
    }

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
        #whatsNewV9Modal .modal-body {
            max-height: calc(100dvh - 9.5rem);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        #whatsNewV9Modal .wn-slide { padding: 1.2rem 1.1rem 1rem; }
        /* Auto-height slides opt out of the global short stage */
        #whatsNewV9Modal .wn-stage:not(.wn-fixes-stage):not(.wn-prefetch-stage) { height: 178px; }
    }

    /* ====================================================================== */
    /* v11.0.0 — animated change-slides                                        */
    /* ====================================================================== */

    /* ===== wn-v11 — Welcome to v11.0.0 ===== */
    .wn-v11-stage {
      display: flex;
      align-items: center;
      justify-content: center;
      background:
        radial-gradient(circle at 50% 50%, rgba(99,102,241,.22), transparent 60%),
        var(--wn-bg-deep);
    }
    
    /* Rotating radial starburst behind the number */
    .wn-v11-burst {
      position: absolute;
      inset: 50% auto auto 50%;
      width: 360px;
      height: 360px;
      transform: translate(-50%, -50%);
      background:
        conic-gradient(from 0deg,
          rgba(129,140,248,0) 0deg,
          rgba(129,140,248,.35) 18deg,
          rgba(129,140,248,0) 36deg,
          rgba(139,92,246,.30) 54deg,
          rgba(139,92,246,0) 72deg,
          rgba(99,102,241,.32) 90deg,
          rgba(99,102,241,0) 108deg,
          rgba(165,180,252,.28) 126deg,
          rgba(165,180,252,0) 144deg,
          rgba(129,140,248,.30) 162deg,
          rgba(129,140,248,0) 180deg,
          rgba(139,92,246,.32) 198deg,
          rgba(139,92,246,0) 216deg,
          rgba(99,102,241,.28) 234deg,
          rgba(99,102,241,0) 252deg,
          rgba(165,180,252,.32) 270deg,
          rgba(165,180,252,0) 288deg,
          rgba(129,140,248,.30) 306deg,
          rgba(129,140,248,0) 324deg,
          rgba(139,92,246,.32) 342deg,
          rgba(139,92,246,0) 360deg);
      filter: blur(8px);
      -webkit-mask-image: radial-gradient(circle at 50% 50%, #000 35%, transparent 70%);
              mask-image: radial-gradient(circle at 50% 50%, #000 35%, transparent 70%);
      animation: wn-v11-spin 18s linear infinite;
      opacity: .9;
    }
    
    /* Concentric pulsing rings */
    .wn-v11-rings {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none;
    }
    .wn-v11-ring {
      position: absolute;
      border-radius: 50%;
      border: 1px solid rgba(165,180,252,.28);
      box-shadow: 0 0 24px rgba(99,102,241,.18) inset;
      animation: wn-v11-pulse 4.4s ease-in-out infinite;
    }
    .wn-v11-ring-a { width: 150px; height: 150px; animation-delay: 0s; }
    .wn-v11-ring-b { width: 210px; height: 210px; animation-delay: .8s; border-color: rgba(139,92,246,.22); }
    .wn-v11-ring-c { width: 280px; height: 280px; animation-delay: 1.6s; border-color: rgba(99,102,241,.18); }
    
    /* Floating sparkles */
    .wn-v11-sparkles { position: absolute; inset: 0; pointer-events: none; }
    .wn-v11-spark {
      position: absolute;
      font-size: 12px;
      color: #fde68a;
      text-shadow: 0 0 10px rgba(251,191,36,.7);
      animation: wn-v11-twinkle 3.2s ease-in-out infinite;
    }
    .wn-v11-spark-1 { top: 14%; left: 12%; color: #a5b4fc; animation-delay: 0s; }
    .wn-v11-spark-2 { top: 22%; right: 14%; color: #fbbf24; animation-delay: .5s; }
    .wn-v11-spark-3 { bottom: 18%; left: 18%; color: #818cf8; animation-delay: 1s; }
    .wn-v11-spark-4 { bottom: 22%; right: 12%; color: #fde68a; animation-delay: 1.5s; }
    .wn-v11-spark-5 { top: 50%; left: 6%; color: #c4b5fd; animation-delay: 2s; font-size: 10px; }
    .wn-v11-spark-6 { top: 48%; right: 6%; color: #a5b4fc; animation-delay: 2.5s; font-size: 10px; }
    
    /* Eyebrow label */
    .wn-v11-label {
      position: absolute;
      top: 22px;
      left: 50%;
      transform: translateX(-50%);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .22em;
      color: #c7d2fe;
      background: rgba(99,102,241,.14);
      border: 1px solid rgba(165,180,252,.30);
      border-radius: 999px;
      text-transform: uppercase;
      animation: wn-v11-labelin 5s ease-in-out infinite;
    }
    .wn-v11-label i { color: #fbbf24; font-size: 11px; }
    
    /* Big gradient number */
    .wn-v11-number {
      position: relative;
      display: inline-flex;
      align-items: baseline;
      gap: 2px;
      font-weight: 900;
      font-size: 64px;
      line-height: 1;
      letter-spacing: -.02em;
      filter: drop-shadow(0 0 18px rgba(129,140,248,.45));
      animation: wn-v11-glow 3.6s ease-in-out infinite;
    }
    /* The gradient must be applied to each DIGIT span, not the parent — the
       parent wraps inline-block spans, so its background-clip:text has no
       text of its own to clip against and renders invisibly. */
    .wn-v11-digit {
      display: inline-block;
      animation: wn-v11-rise 5s ease-in-out infinite;
      background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 25%, #6366f1 50%, #8b5cf6 75%, #ec4899 100%);
      -webkit-background-clip: text;
              background-clip: text;
      -webkit-text-fill-color: transparent;
              color: transparent;
    }
    .wn-v11-number .wn-v11-digit:nth-child(1) { animation-delay: 0s; }
    .wn-v11-number .wn-v11-digit:nth-child(3) { animation-delay: .25s; }
    .wn-v11-number .wn-v11-digit:nth-child(5) { animation-delay: .5s; }
    .wn-v11-dot {
      display: inline-block;
      width: 8px;
      height: 8px;
      background: #fbbf24;
      border-radius: 50%;
      margin: 0 2px 6px;
      align-self: flex-end;
      color: transparent;
      box-shadow: 0 0 12px rgba(251,191,36,.7);
      animation: wn-v11-dotpulse 2.2s ease-in-out infinite;
    }
    .wn-v11-number .wn-v11-dot:nth-of-type(2) { animation-delay: .4s; }
    
    /* Bottom tag */
    .wn-v11-tag {
      position: absolute;
      bottom: 18px;
      left: 50%;
      transform: translateX(-50%);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      font-size: 10px;
      font-weight: 600;
      color: #cbd5e1;
      background: #1e293b;
      border: 1px solid rgba(148,163,184,.18);
      border-radius: 999px;
      animation: wn-v11-labelin 5s ease-in-out infinite .4s;
    }
    .wn-v11-pip {
      width: 6px; height: 6px; border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 8px #10b981;
      animation: wn-v11-pip 1.8s ease-in-out infinite;
    }
    
    @keyframes wn-v11-spin {
      0%   { transform: translate(-50%, -50%) rotate(0deg); }
      100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    @keyframes wn-v11-pulse {
      0%, 90%, 100% { transform: scale(1); opacity: .55; }
      45%           { transform: scale(1.08); opacity: .95; }
    }
    @keyframes wn-v11-twinkle {
      0%, 85%, 100% { transform: scale(1) rotate(0deg); opacity: .35; }
      40%           { transform: scale(1.4) rotate(18deg); opacity: 1; }
    }
    @keyframes wn-v11-glow {
      0%, 85%, 100% { filter: drop-shadow(0 0 18px rgba(129,140,248,.45)); }
      45%           { filter: drop-shadow(0 0 28px rgba(165,180,252,.85)) drop-shadow(0 0 40px rgba(139,92,246,.35)); }
    }
    @keyframes wn-v11-rise {
      0%, 80%, 100% { transform: translateY(0); }
      40%           { transform: translateY(-4px); }
    }
    @keyframes wn-v11-dotpulse {
      0%, 85%, 100% { transform: scale(1); box-shadow: 0 0 12px rgba(251,191,36,.7); }
      45%           { transform: scale(1.25); box-shadow: 0 0 18px rgba(251,191,36,1); }
    }
    @keyframes wn-v11-labelin {
      0%, 90%, 100% { opacity: .9; transform: translateX(-50%) translateY(0); }
      45%           { opacity: 1;  transform: translateX(-50%) translateY(-1px); }
    }
    @keyframes wn-v11-pip {
      0%, 85%, 100% { opacity: 1; transform: scale(1); }
      45%           { opacity: .55; transform: scale(.85); }
    }
    
    @media (max-width: 575.98px) {
      .wn-v11-burst { width: 280px; height: 280px; }
      .wn-v11-ring-a { width: 120px; height: 120px; }
      .wn-v11-ring-b { width: 170px; height: 170px; }
      .wn-v11-ring-c { width: 220px; height: 220px; }
      .wn-v11-number { font-size: 52px; }
      .wn-v11-label { top: 14px; font-size: 9px; padding: 3px 8px; }
      .wn-v11-tag   { bottom: 12px; font-size: 9px; padding: 3px 8px; }
      .wn-v11-dot   { width: 6px; height: 6px; margin: 0 1px 4px; }
    }

    /* ===== wn-notif — A redesigned notification center ===== */
    .wn-notif-bg{position:absolute;inset:0;background:radial-gradient(120% 80% at 80% 0%,rgba(99,102,241,.22),transparent 60%),radial-gradient(80% 60% at 10% 100%,rgba(139,92,246,.16),transparent 60%);}
    .wn-notif-orb{position:absolute;border-radius:50%;filter:blur(18px);opacity:.55;}
    .wn-notif-orb-1{width:90px;height:90px;top:-20px;left:-20px;background:#6366f1;}
    .wn-notif-orb-2{width:70px;height:70px;bottom:-20px;right:30%;background:#8b5cf6;}
    .wn-notif-bell{position:absolute;top:10px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(30,41,59,.85);border:1px solid rgba(148,163,184,.25);display:flex;align-items:center;justify-content:center;color:#a5b4fc;font-size:13px;z-index:1;animation:wn-notif-ring 6s ease-in-out infinite;}
    .wn-notif-badge{position:absolute;top:-4px;right:-4px;min-width:14px;height:14px;padding:0 3px;border-radius:7px;background:#ef4444;color:#fff;font-size:8px;font-weight:700;display:flex;align-items:center;justify-content:center;border:1.5px solid #0f172a;}
    .wn-notif-panel{position:absolute;top:8px;right:8px;width:300px;border-radius:12px;background:linear-gradient(160deg,rgba(30,41,59,.92),rgba(15,23,42,.88));border:1px solid rgba(148,163,184,.22);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);box-shadow:0 10px 30px rgba(0,0,0,.45),inset 0 1px 0 rgba(255,255,255,.05);padding:8px;transform-origin:top right;animation:wn-notif-pop 6s ease-in-out infinite;z-index:2;}
    .wn-notif-head{display:flex;justify-content:space-between;align-items:center;padding:2px 4px 6px;border-bottom:1px solid rgba(148,163,184,.14);margin-bottom:6px;}
    .wn-notif-title{color:#e2e8f0;font-size:10px;font-weight:600;display:flex;align-items:center;gap:5px;}
    .wn-notif-title i{color:#a5b4fc;font-size:10px;}
    .wn-notif-count{color:#a5b4fc;font-size:8px;font-weight:600;padding:2px 6px;border-radius:8px;background:rgba(99,102,241,.18);border:1px solid rgba(99,102,241,.3);}
    .wn-notif-bucket{color:#94a3b8;font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;padding:3px 4px 2px;}
    .wn-notif-row{position:relative;display:flex;align-items:center;gap:7px;padding:5px 6px;border-radius:8px;background:rgba(15,23,42,.5);border:1px solid rgba(148,163,184,.08);margin-bottom:3px;overflow:hidden;}
    .wn-notif-row-hover{animation:wn-notif-rowhi 6s ease-in-out infinite;}
    .wn-notif-dot{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;color:#fff;flex-shrink:0;}
    .wn-notif-dot-indigo{background:linear-gradient(135deg,#6366f1,#4F46E5);}
    .wn-notif-dot-amber{background:linear-gradient(135deg,#f59e0b,#fbbf24);}
    .wn-notif-dot-green{background:linear-gradient(135deg,#10b981,#6ee7b7);}
    .wn-notif-body{flex:1;display:flex;flex-direction:column;gap:3px;min-width:0;}
    .wn-notif-line{height:4px;border-radius:2px;background:linear-gradient(90deg,rgba(226,232,240,.55),rgba(226,232,240,.15));}
    .wn-notif-line-1{width:70%;}
    .wn-notif-line-2{width:90%;background:linear-gradient(90deg,rgba(148,163,184,.4),rgba(148,163,184,.1));}
    .wn-notif-line-short{width:55%;}
    .wn-notif-time{color:#94a3b8;font-size:7.5px;font-weight:600;flex-shrink:0;}
    .wn-notif-actions{position:absolute;right:4px;top:50%;transform:translateY(-50%) translateX(6px);display:flex;gap:3px;opacity:0;animation:wn-notif-act 6s ease-in-out infinite;}
    .wn-notif-chip{width:18px;height:18px;border-radius:6px;background:rgba(30,41,59,.95);border:1px solid rgba(148,163,184,.25);color:#a5b4fc;display:flex;align-items:center;justify-content:center;font-size:9px;box-shadow:0 2px 6px rgba(0,0,0,.4);}
    .wn-notif-chip-red{color:#fca5a5;border-color:rgba(239,68,68,.35);background:rgba(239,68,68,.12);}
    .wn-notif-dock{display:flex;justify-content:space-between;align-items:center;gap:4px;margin-top:6px;padding:5px 6px;border-radius:8px;background:linear-gradient(180deg,rgba(15,23,42,.7),rgba(15,23,42,.4));border:1px solid rgba(148,163,184,.12);}
    .wn-notif-dbtn{width:22px;height:22px;border-radius:50%;background:rgba(99,102,241,.15);border:1px solid rgba(165,180,252,.25);color:#a5b4fc;display:flex;align-items:center;justify-content:center;font-size:10px;animation:wn-notif-dbtn 6s ease-in-out infinite;}
    .wn-notif-dbtn:nth-child(1){animation-delay:0s;}
    .wn-notif-dbtn:nth-child(2){animation-delay:.15s;}
    .wn-notif-dbtn:nth-child(3){animation-delay:.3s;}
    .wn-notif-dbtn:nth-child(4){animation-delay:.45s;}
    .wn-notif-dbtn:nth-child(5){animation-delay:.6s;}
    .wn-notif-dbtn:nth-child(6){animation-delay:.75s;}
    @keyframes wn-notif-pop{0%{opacity:0;transform:translate(20px,-12px) scale(.9);}10%,90%{opacity:1;transform:translate(0,0) scale(1);}100%{opacity:0;transform:translate(20px,-12px) scale(.9);}}
    @keyframes wn-notif-ring{0%,12%,100%{transform:rotate(0);}15%{transform:rotate(-14deg);}18%{transform:rotate(12deg);}21%{transform:rotate(-8deg);}24%{transform:rotate(0);}}
    @keyframes wn-notif-rowhi{0%,30%{background:rgba(15,23,42,.5);border-color:rgba(148,163,184,.08);}45%,75%{background:rgba(99,102,241,.14);border-color:rgba(99,102,241,.3);}90%,100%{background:rgba(15,23,42,.5);border-color:rgba(148,163,184,.08);}}
    @keyframes wn-notif-act{0%,30%{opacity:0;transform:translateY(-50%) translateX(6px);}45%,75%{opacity:1;transform:translateY(-50%) translateX(0);}90%,100%{opacity:0;transform:translateY(-50%) translateX(6px);}}
    @keyframes wn-notif-dbtn{0%,80%,100%{transform:translateY(0);background:rgba(99,102,241,.15);color:#a5b4fc;}40%{transform:translateY(-2px);background:rgba(99,102,241,.3);color:#e2e8f0;box-shadow:0 4px 10px rgba(99,102,241,.3);}}
    @media (max-width:575.98px){.wn-notif-panel{width:84%;right:6px;top:6px;padding:6px;}.wn-notif-bell{top:8px;right:8px;width:24px;height:24px;font-size:11px;}.wn-notif-dot{width:16px;height:16px;font-size:8px;}.wn-notif-chip{width:16px;height:16px;font-size:8px;}.wn-notif-dbtn{width:20px;height:20px;font-size:9px;}.wn-notif-title{font-size:9px;}.wn-notif-count{font-size:7px;}.wn-notif-bucket{font-size:7px;}.wn-notif-time{font-size:7px;}}

    /* ===== wn-todo — Smart, multi-list to-dos ===== */
    .wn-todo-scene{position:absolute;inset:0;padding:12px 14px;display:flex;flex-direction:column;gap:8px;font-family:inherit}
    
    /* ---- list rail ---- */
    .wn-todo-rail{display:flex;gap:6px;overflow:hidden;flex-wrap:nowrap}
    .wn-todo-chip{flex:0 0 auto;display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:999px;font-size:10.5px;font-weight:600;letter-spacing:.1px;color:#cbd5e1;background:rgba(30,41,59,.85);border:1px solid rgba(148,163,184,.2);white-space:nowrap}
    .wn-todo-chip i{font-size:10px;opacity:.85}
    .wn-todo-chip-indigo{color:#a5b4fc;border-color:rgba(129,140,248,.35)}
    .wn-todo-chip-amber{color:#fbbf24;border-color:rgba(245,158,11,.3)}
    .wn-todo-chip-cyan{color:#38bdf8;border-color:rgba(56,189,248,.3)}
    .wn-todo-chip-active{color:#fff;background:linear-gradient(135deg,#ec4899,#f43f5e);border-color:rgba(236,72,153,.55);box-shadow:0 4px 14px -4px rgba(236,72,153,.55);animation:wn-todo-chip-pulse 4s ease-in-out infinite}
    @keyframes wn-todo-chip-pulse{0%,80%,100%{box-shadow:0 4px 14px -4px rgba(236,72,153,.55)}40%{box-shadow:0 4px 18px -2px rgba(236,72,153,.85)}}
    
    /* ---- progress card ---- */
    .wn-todo-card{background:linear-gradient(135deg,rgba(236,72,153,.18),rgba(244,63,94,.08));border:1px solid rgba(236,72,153,.28);border-radius:11px;padding:8px 10px 9px;backdrop-filter:blur(8px)}
    .wn-todo-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
    .wn-todo-card-title{display:inline-flex;align-items:center;gap:5px;color:#fde68a;font-size:11px;font-weight:700;letter-spacing:.15px}
    .wn-todo-card-title i{color:#fbbf24;font-size:12px;animation:wn-todo-sparkle 3.6s ease-in-out infinite}
    @keyframes wn-todo-sparkle{0%,80%,100%{transform:rotate(0) scale(1);opacity:1}40%{transform:rotate(15deg) scale(1.18);opacity:.9}}
    .wn-todo-badge{font-size:9.5px;font-weight:700;color:#fda4af;background:rgba(15,23,42,.55);border:1px solid rgba(236,72,153,.35);padding:2px 7px;border-radius:999px;letter-spacing:.3px}
    .wn-todo-badge-num{color:#fff}
    .wn-todo-bar{height:6px;background:rgba(15,23,42,.6);border-radius:999px;overflow:hidden;position:relative}
    .wn-todo-bar-fill{position:absolute;inset:0 auto 0 0;width:75%;background:linear-gradient(90deg,#f43f5e,#ec4899,#fbbf24);border-radius:999px;animation:wn-todo-fill 4s ease-in-out infinite;box-shadow:0 0 10px rgba(236,72,153,.55)}
    @keyframes wn-todo-fill{0%,40%{width:75%}55%,80%{width:100%}90%{width:100%}100%{width:75%}}
    
    /* ---- task rows ---- */
    .wn-todo-tasks{display:flex;flex-direction:column;gap:5px;margin-top:1px}
    .wn-todo-row{display:flex;align-items:center;gap:8px;padding:5px 9px;background:rgba(30,41,59,.7);border:1px solid rgba(148,163,184,.14);border-radius:9px;font-size:11px;color:#e2e8f0;min-height:24px}
    .wn-todo-check{width:15px;height:15px;border-radius:50%;border:1.5px solid rgba(148,163,184,.55);flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;color:transparent;font-size:10px;transition:none}
    .wn-todo-check-static{background:linear-gradient(135deg,#10b981,#6ee7b7);border-color:#10b981;color:#0f172a}
    .wn-todo-title{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;position:relative}
    .wn-todo-title-struck{color:#94a3b8}
    .wn-todo-title-struck::after{content:"";position:absolute;left:0;right:0;top:50%;height:1.5px;background:#94a3b8;border-radius:1px}
    
    /* checking-off row */
    .wn-todo-row-anim{position:relative;overflow:hidden}
    .wn-todo-row-anim::before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(236,72,153,.18),transparent 60%);opacity:0;animation:wn-todo-glow 4s ease-in-out infinite;pointer-events:none}
    @keyframes wn-todo-glow{0%,40%{opacity:0}55%,75%{opacity:1}90%,100%{opacity:0}}
    .wn-todo-check-anim{animation:wn-todo-check 4s ease-in-out infinite}
    @keyframes wn-todo-check{0%,40%{background:transparent;border-color:rgba(148,163,184,.55);color:transparent;transform:scale(1)}50%{transform:scale(1.25)}55%,80%{background:linear-gradient(135deg,#10b981,#6ee7b7);border-color:#10b981;color:#0f172a;transform:scale(1)}92%{background:transparent;border-color:rgba(148,163,184,.55);color:transparent;transform:scale(1)}100%{background:transparent;border-color:rgba(148,163,184,.55);color:transparent}}
    .wn-todo-title-anim{position:relative}
    .wn-todo-title-anim::after{content:"";position:absolute;left:0;top:50%;height:1.5px;background:#94a3b8;border-radius:1px;width:0;animation:wn-todo-strike 4s ease-in-out infinite}
    @keyframes wn-todo-strike{0%,50%{width:0;opacity:1}55%{width:0;opacity:1}75%,85%{width:100%;opacity:1}92%{width:100%;opacity:0}100%{width:0;opacity:1}}
    .wn-todo-row-anim .wn-todo-title-anim{animation:wn-todo-fade 4s ease-in-out infinite}
    @keyframes wn-todo-fade{0%,50%{color:#e2e8f0}75%,88%{color:#94a3b8}100%{color:#e2e8f0}}
    
    /* ---- mobile ---- */
    @media (max-width:575.98px){
      .wn-todo-scene{padding:9px 10px;gap:6px}
      .wn-todo-chip{font-size:9.5px;padding:3px 7px;gap:3px}
      .wn-todo-chip i{font-size:9px}
      .wn-todo-card{padding:6px 9px 7px;border-radius:10px}
      .wn-todo-card-title{font-size:10px}
      .wn-todo-badge{font-size:9px;padding:1px 6px}
      .wn-todo-bar{height:5px}
      .wn-todo-row{padding:4px 8px;font-size:10px;min-height:22px;border-radius:8px}
      .wn-todo-check{width:13px;height:13px;font-size:9px}
      .wn-todo-tasks{gap:4px}
    }

    /* ===== wn-pal — A palette for every mood ===== */
    .wn-pal-scene{
        position:absolute; inset:0; padding:14px;
        display:grid; grid-template-columns: 1fr 128px; gap:12px;
        align-items:center;
    }
    .wn-pal-grid{
        display:grid; grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 1fr);
        gap:8px; height:100%;
    }
    .wn-pal-sw{
        position:relative; border-radius:10px;
        background:#1e293b; border:1px solid rgba(148,163,184,.18);
        display:flex; align-items:center; gap:6px;
        padding:6px 8px; overflow:hidden;
        box-shadow: 0 1px 0 rgba(255,255,255,.03) inset;
    }
    .wn-pal-sw::after{
        content:""; position:absolute; inset:-1px; border-radius:11px;
        border:1.5px solid transparent; pointer-events:none;
        transition:none;
    }
    .wn-pal-dot{
        width:14px; height:14px; border-radius:50%;
        flex:0 0 auto;
        box-shadow: 0 0 0 2px rgba(15,23,42,.6), 0 0 8px rgba(0,0,0,.35);
    }
    .wn-pal-lbl{
        font-size:10px; font-weight:600; letter-spacing:.02em;
        color:#cbd5e1; white-space:nowrap;
    }
    .wn-pal-sw-1 .wn-pal-dot{ background:linear-gradient(135deg,#6366f1,#a5b4fc); }
    .wn-pal-sw-2 .wn-pal-dot{ background:linear-gradient(135deg,#10b981,#6ee7b7); }
    .wn-pal-sw-3 .wn-pal-dot{ background:linear-gradient(135deg,#ec4899,#fca5a5); }
    .wn-pal-sw-4 .wn-pal-dot{ background:linear-gradient(135deg,#64748b,#cbd5e1); }
    .wn-pal-sw-5 .wn-pal-dot{ background:linear-gradient(135deg,#f59e0b,#fde68a); }
    .wn-pal-sw-6 .wn-pal-dot{ background:linear-gradient(135deg,#38bdf8,#bfdbfe); }
    
    /* highlight ring per swatch — staggered keyframes */
    .wn-pal-sw-1::after{ animation: wn-pal-ring1 7.2s infinite; }
    .wn-pal-sw-2::after{ animation: wn-pal-ring2 7.2s infinite; }
    .wn-pal-sw-3::after{ animation: wn-pal-ring3 7.2s infinite; }
    .wn-pal-sw-4::after{ animation: wn-pal-ring4 7.2s infinite; }
    .wn-pal-sw-5::after{ animation: wn-pal-ring5 7.2s infinite; }
    .wn-pal-sw-6::after{ animation: wn-pal-ring6 7.2s infinite; }
    
    @keyframes wn-pal-ring1{ 0%,12%{ border-color:#818cf8; box-shadow:0 0 14px rgba(99,102,241,.55);} 18%,100%{ border-color:transparent; box-shadow:none;} }
    @keyframes wn-pal-ring2{ 0%,16.66%{ border-color:transparent; box-shadow:none;} 16.67%,28.66%{ border-color:#6ee7b7; box-shadow:0 0 14px rgba(16,185,129,.55);} 34.66%,100%{ border-color:transparent; box-shadow:none;} }
    @keyframes wn-pal-ring3{ 0%,33.33%{ border-color:transparent; box-shadow:none;} 33.34%,45.33%{ border-color:#fca5a5; box-shadow:0 0 14px rgba(236,72,153,.55);} 51.33%,100%{ border-color:transparent; box-shadow:none;} }
    @keyframes wn-pal-ring4{ 0%,50%{ border-color:transparent; box-shadow:none;} 50.01%,62%{ border-color:#cbd5e1; box-shadow:0 0 14px rgba(148,163,184,.55);} 68%,100%{ border-color:transparent; box-shadow:none;} }
    @keyframes wn-pal-ring5{ 0%,66.66%{ border-color:transparent; box-shadow:none;} 66.67%,78.66%{ border-color:#fde68a; box-shadow:0 0 14px rgba(245,158,11,.55);} 84.66%,100%{ border-color:transparent; box-shadow:none;} }
    @keyframes wn-pal-ring6{ 0%,83.33%{ border-color:transparent; box-shadow:none;} 83.34%,95.33%{ border-color:#bfdbfe; box-shadow:0 0 14px rgba(56,189,248,.55);} 99%,100%{ border-color:transparent; box-shadow:none;} }
    
    /* preview card */
    .wn-pal-preview{
        background:#1e293b; border:1px solid rgba(148,163,184,.22);
        border-radius:12px; padding:10px;
        display:flex; flex-direction:column; gap:8px;
        height:100%; box-sizing:border-box;
        box-shadow: 0 6px 18px rgba(0,0,0,.35);
    }
    .wn-pal-prev-head{ display:flex; align-items:center; gap:8px; }
    .wn-pal-prev-avatar{
        width:22px; height:22px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        color:#0f172a; font-size:13px;
        background:#818cf8;
        animation: wn-pal-accent-bg 7.2s infinite;
    }
    .wn-pal-prev-lines{ display:flex; flex-direction:column; gap:3px; flex:1; }
    .wn-pal-prev-l1{ height:6px; width:70%; border-radius:3px; background:#334155; }
    .wn-pal-prev-l2{ height:5px; width:45%; border-radius:3px; background:#273449; }
    .wn-pal-prev-bar{
        height:6px; border-radius:3px; background:#273449; overflow:hidden;
    }
    .wn-pal-prev-bar-fill{
        display:block; height:100%; width:62%; border-radius:3px;
        background:linear-gradient(90deg,#6366f1,#818cf8);
        animation: wn-pal-accent-bar 7.2s infinite;
    }
    .wn-pal-prev-row{ display:flex; align-items:center; justify-content:space-between; gap:6px; }
    .wn-pal-prev-chip{
        font-size:9px; font-weight:600; padding:3px 6px;
        border-radius:999px;
        background:rgba(99,102,241,.18); color:#a5b4fc;
        border:1px solid rgba(99,102,241,.45);
        display:inline-flex; align-items:center; gap:3px;
        animation: wn-pal-accent-chip 7.2s infinite;
    }
    .wn-pal-prev-btn{
        font-size:10px; font-weight:700; padding:4px 9px;
        border-radius:6px; color:#fff;
        background:#6366f1;
        box-shadow: 0 4px 10px rgba(99,102,241,.45);
        animation: wn-pal-accent-btn 7.2s infinite;
    }
    
    /* accent cycling — avatar + bar + chip + button share the schedule */
    @keyframes wn-pal-accent-bg{
        0%,12%   { background:#818cf8; }
        16.67%,28.66% { background:#6ee7b7; }
        33.34%,45.33% { background:#fca5a5; }
        50.01%,62%    { background:#cbd5e1; }
        66.67%,78.66% { background:#fde68a; }
        83.34%,95.33% { background:#bfdbfe; }
        100% { background:#818cf8; }
    }
    @keyframes wn-pal-accent-bar{
        0%,12%   { background:linear-gradient(90deg,#6366f1,#818cf8); }
        16.67%,28.66% { background:linear-gradient(90deg,#10b981,#6ee7b7); }
        33.34%,45.33% { background:linear-gradient(90deg,#ec4899,#fca5a5); }
        50.01%,62%    { background:linear-gradient(90deg,#64748b,#cbd5e1); }
        66.67%,78.66% { background:linear-gradient(90deg,#f59e0b,#fde68a); }
        83.34%,95.33% { background:linear-gradient(90deg,#38bdf8,#bfdbfe); }
        100% { background:linear-gradient(90deg,#6366f1,#818cf8); }
    }
    @keyframes wn-pal-accent-chip{
        0%,12%   { background:rgba(99,102,241,.18); color:#a5b4fc; border-color:rgba(99,102,241,.45); }
        16.67%,28.66% { background:rgba(16,185,129,.18); color:#6ee7b7; border-color:rgba(16,185,129,.45); }
        33.34%,45.33% { background:rgba(236,72,153,.18); color:#fca5a5; border-color:rgba(236,72,153,.45); }
        50.01%,62%    { background:rgba(148,163,184,.18); color:#cbd5e1; border-color:rgba(148,163,184,.45); }
        66.67%,78.66% { background:rgba(245,158,11,.18); color:#fde68a; border-color:rgba(245,158,11,.45); }
        83.34%,95.33% { background:rgba(56,189,248,.18); color:#bfdbfe; border-color:rgba(56,189,248,.45); }
        100% { background:rgba(99,102,241,.18); color:#a5b4fc; border-color:rgba(99,102,241,.45); }
    }
    @keyframes wn-pal-accent-btn{
        0%,12%   { background:#6366f1; box-shadow:0 4px 10px rgba(99,102,241,.45); }
        16.67%,28.66% { background:#10b981; box-shadow:0 4px 10px rgba(16,185,129,.45); }
        33.34%,45.33% { background:#ec4899; box-shadow:0 4px 10px rgba(236,72,153,.45); }
        50.01%,62%    { background:#64748b; box-shadow:0 4px 10px rgba(100,116,139,.45); color:#0f172a;}
        66.67%,78.66% { background:#f59e0b; box-shadow:0 4px 10px rgba(245,158,11,.45); color:#0f172a;}
        83.34%,95.33% { background:#38bdf8; box-shadow:0 4px 10px rgba(56,189,248,.45); color:#0f172a;}
        100% { background:#6366f1; box-shadow:0 4px 10px rgba(99,102,241,.45); color:#fff; }
    }
    
    @media (max-width: 575.98px){
        .wn-pal-scene{ padding:10px; gap:8px; grid-template-columns: 1fr 108px; }
        .wn-pal-lbl{ font-size:9px; }
        .wn-pal-dot{ width:12px; height:12px; }
        .wn-pal-sw{ padding:5px 6px; gap:5px; }
        .wn-pal-prev-btn{ font-size:9px; padding:3px 7px; }
        .wn-pal-prev-chip{ font-size:8px; }
    }

    /* ===== wn-sched — Dark by night, light by day ===== */
    .wn-sched-stage { isolation: isolate; }
    
    /* ---- Sky half (left) ---- */
    .wn-sched-sky {
      position: absolute; inset: 0;
      background: linear-gradient(180deg, #0b1228 0%, #0f172a 55%, #111a32 100%);
      animation: wn-sched-sky 12s ease-in-out infinite;
    }
    .wn-sched-sky::after {
      content: ""; position: absolute; inset: 0;
      background:
        radial-gradient(120% 60% at 20% 110%, rgba(99,102,241,.25), transparent 60%),
        radial-gradient(80% 50% at 80% -10%, rgba(139,92,246,.18), transparent 70%);
      pointer-events: none;
    }
    .wn-sched-horizon {
      position: absolute; left: 0; right: 0; bottom: 0; height: 38%;
      background: linear-gradient(180deg, rgba(15,23,42,0) 0%, rgba(15,23,42,.55) 70%, rgba(2,6,23,.85) 100%);
    }
    .wn-sched-stars {
      position: absolute; inset: 0;
      background-image:
        radial-gradient(1px 1px at 12% 22%, #e2e8f0 99%, transparent),
        radial-gradient(1px 1px at 28% 14%, #cbd5e1 99%, transparent),
        radial-gradient(1px 1px at 44% 30%, #e2e8f0 99%, transparent),
        radial-gradient(1px 1px at 18% 48%, #a5b4fc 99%, transparent),
        radial-gradient(1px 1px at 36% 56%, #cbd5e1 99%, transparent),
        radial-gradient(1px 1px at 6% 36%, #fde68a 99%, transparent);
      opacity: 0;
      animation: wn-sched-stars 12s ease-in-out infinite;
    }
    .wn-sched-sun, .wn-sched-moon {
      position: absolute; left: 22%; width: 34px; height: 34px;
      border-radius: 50%; transform: translate(-50%, -50%);
      will-change: top, opacity, box-shadow;
    }
    .wn-sched-sun {
      background: radial-gradient(circle at 35% 35%, #fde68a, #fbbf24 55%, #f59e0b 100%);
      box-shadow: 0 0 24px rgba(251,191,36,.55), 0 0 48px rgba(245,158,11,.35);
      animation: wn-sched-sun 12s ease-in-out infinite;
    }
    .wn-sched-moon {
      background: radial-gradient(circle at 35% 35%, #f1f5f9, #cbd5e1 60%, #94a3b8 100%);
      box-shadow: 0 0 18px rgba(165,180,252,.45), inset -6px -2px 0 rgba(15,23,42,.55);
      animation: wn-sched-moon 12s ease-in-out infinite;
    }
    
    /* ---- Clock (right) ---- */
    .wn-sched-clock {
      position: absolute; right: 18px; top: 50%; transform: translateY(-50%);
      width: 104px; height: 104px;
      background: radial-gradient(circle at 50% 45%, #1e293b 0%, #0f172a 80%);
      border: 1px solid rgba(148,163,184,.28);
      border-radius: 50%;
      box-shadow: 0 10px 24px rgba(0,0,0,.45), inset 0 0 22px rgba(99,102,241,.18);
    }
    .wn-sched-dial { position: absolute; inset: 0; }
    .wn-sched-tick {
      position: absolute; left: 50%; top: 50%;
      width: 2px; height: 7px; margin-left: -1px;
      background: rgba(203,213,225,.7); border-radius: 1px;
    }
    .wn-sched-t12 { transform: translate(0, -46px); }
    .wn-sched-t3  { transform: translate(0, -46px) rotate(90deg); transform-origin: 50% 46px; }
    .wn-sched-t6  { transform: translate(0, -46px) rotate(180deg); transform-origin: 50% 46px; }
    .wn-sched-t9  { transform: translate(0, -46px) rotate(270deg); transform-origin: 50% 46px; }
    .wn-sched-hand {
      position: absolute; left: 50%; top: 50%;
      width: 3px; height: 36px; margin-left: -1.5px;
      background: linear-gradient(180deg, #a5b4fc, #6366f1);
      border-radius: 2px;
      transform-origin: 50% 100%;
      transform: translateY(-100%) rotate(0deg);
      box-shadow: 0 0 8px rgba(99,102,241,.55);
      animation: wn-sched-hand 12s linear infinite;
    }
    .wn-sched-pivot {
      position: absolute; left: 50%; top: 50%;
      width: 8px; height: 8px; margin: -4px 0 0 -4px;
      border-radius: 50%;
      background: #fbbf24;
      box-shadow: 0 0 0 2px #0f172a, 0 0 10px rgba(251,191,36,.7);
    }
    
    /* ---- Time chips ---- */
    .wn-sched-chip {
      position: absolute; left: 12px;
      display: inline-flex; align-items: center; gap: 6px;
      padding: 4px 8px; border-radius: 999px;
      font-size: 10px; font-weight: 600; letter-spacing: .02em;
      background: rgba(30,41,59,.85);
      border: 1px solid rgba(148,163,184,.22);
      backdrop-filter: blur(4px);
      color: #e2e8f0;
    }
    .wn-sched-chip i { font-size: 11px; line-height: 1; }
    .wn-sched-chip-day { top: 14px; color: #fde68a; border-color: rgba(251,191,36,.35); }
    .wn-sched-chip-day i { color: #fbbf24; }
    .wn-sched-chip-night { bottom: 14px; color: #bfdbfe; border-color: rgba(99,102,241,.4); }
    .wn-sched-chip-night i { color: #a5b4fc; }
    
    /* ---- Auto toggle ---- */
    .wn-sched-toggle {
      position: absolute; right: 12px; top: 10px;
      display: inline-flex; align-items: center; gap: 6px;
      padding: 3px 8px 3px 8px; border-radius: 999px;
      background: rgba(15,23,42,.75);
      border: 1px solid rgba(99,102,241,.45);
      font-size: 9.5px; font-weight: 700; letter-spacing: .04em;
      color: #c7d2fe; text-transform: uppercase;
      box-shadow: 0 0 0 1px rgba(99,102,241,.15), 0 6px 14px rgba(0,0,0,.35);
    }
    .wn-sched-toggle-track {
      position: relative; width: 22px; height: 12px; border-radius: 999px;
      background: linear-gradient(90deg, #6366f1, #818cf8);
      box-shadow: inset 0 0 4px rgba(15,23,42,.4);
    }
    .wn-sched-toggle-knob {
      position: absolute; top: 1.5px; left: 11px;
      width: 9px; height: 9px; border-radius: 50%;
      background: #f8fafc;
      box-shadow: 0 1px 2px rgba(0,0,0,.4);
      animation: wn-sched-knob 12s ease-in-out infinite;
    }
    .wn-sched-toggle-state { color: #6ee7b7; }
    
    /* ---- Keyframes ---- */
    @keyframes wn-sched-sky {
      0%, 100% { background: linear-gradient(180deg, #0b1228 0%, #0f172a 55%, #111a32 100%); }
      20%      { background: linear-gradient(180deg, #2a1f4a 0%, #6b3a6e 55%, #f59e0b 100%); }
      40%      { background: linear-gradient(180deg, #1e3a8a 0%, #3b82f6 55%, #bfdbfe 100%); }
      60%      { background: linear-gradient(180deg, #60a5fa 0%, #38bdf8 55%, #fde68a 100%); }
      80%      { background: linear-gradient(180deg, #4c1d6b 0%, #8b5cf6 50%, #ef4444 100%); }
    }
    @keyframes wn-sched-stars {
      0%, 100% { opacity: .85; }
      20%      { opacity: .35; }
      40%, 60% { opacity: 0; }
      80%      { opacity: .4; }
    }
    @keyframes wn-sched-sun {
      0%, 100% { top: 115%; opacity: 0; }
      20%      { top: 70%;  opacity: .9; }
      40%, 60% { top: 32%;  opacity: 1; }
      80%      { top: 70%;  opacity: .85; }
    }
    @keyframes wn-sched-moon {
      0%, 100% { top: 30%; opacity: 1; }
      20%      { top: 70%; opacity: .4; }
      40%, 60% { top: 115%; opacity: 0; }
      80%      { top: 70%; opacity: .45; }
    }
    @keyframes wn-sched-hand {
      0%   { transform: translateY(-100%) rotate(0deg); }
      100% { transform: translateY(-100%) rotate(360deg); }
    }
    @keyframes wn-sched-knob {
      0%, 100% { left: 11px; background: #f8fafc; }
      50%      { left: 11px; background: #f0fdf4; }
    }
    
    /* ---- Mobile safety ---- */
    @media (max-width: 575.98px) {
      .wn-sched-clock { width: 86px; height: 86px; right: 12px; }
      .wn-sched-hand { height: 30px; }
      .wn-sched-t12, .wn-sched-t3, .wn-sched-t6, .wn-sched-t9 { transform: translate(0, -38px); }
      .wn-sched-t3 { transform: translate(0, -38px) rotate(90deg); transform-origin: 50% 38px; }
      .wn-sched-t6 { transform: translate(0, -38px) rotate(180deg); transform-origin: 50% 38px; }
      .wn-sched-t9 { transform: translate(0, -38px) rotate(270deg); transform-origin: 50% 38px; }
      .wn-sched-chip { font-size: 9px; padding: 3px 6px; }
      .wn-sched-toggle { font-size: 9px; padding: 2px 6px; }
      .wn-sched-sun, .wn-sched-moon { width: 28px; height: 28px; left: 20%; }
    }

    /* ===== wn-cmdk — Cmd+K, anywhere ===== */
    .wn-cmdk-scene {
      position: absolute;
      inset: 0;
      padding: 12px 14px 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      overflow: hidden;
    }
    
    /* ⌘ K chip at the top */
    .wn-cmdk-kbd {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 10px;
      border-radius: 8px;
      background: linear-gradient(180deg, #1e293b, #0f172a);
      border: 1px solid rgba(165,180,252,.35);
      box-shadow: 0 4px 14px rgba(99,102,241,.35), inset 0 1px 0 rgba(255,255,255,.06);
      color: #e2e8f0;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .3px;
      animation: wn-cmdk-pulse 4.5s ease-in-out infinite;
      flex-shrink: 0;
    }
    .wn-cmdk-key {
      display: inline-block;
      min-width: 16px;
      padding: 1px 5px;
      border-radius: 4px;
      background: rgba(99,102,241,.18);
      border: 1px solid rgba(165,180,252,.4);
      color: #c7d2fe;
      font-size: 11px;
      line-height: 1.1;
      text-align: center;
    }
    .wn-cmdk-plus {
      color: #94a3b8;
      font-size: 10px;
    }
    
    /* Modal */
    .wn-cmdk-modal {
      width: 86%;
      max-width: 340px;
      border-radius: 10px;
      background: linear-gradient(180deg, rgba(30,41,59,.95), rgba(15,23,42,.95));
      border: 1px solid rgba(129,140,248,.45);
      box-shadow: 0 12px 30px rgba(0,0,0,.45), 0 0 0 1px rgba(99,102,241,.15), inset 0 1px 0 rgba(255,255,255,.04);
      overflow: hidden;
      transform-origin: top center;
      animation: wn-cmdk-modal 4.5s ease-in-out infinite;
      min-width: 0;
    }
    
    /* Input row */
    .wn-cmdk-input {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 7px 10px;
      border-bottom: 1px solid rgba(148,163,184,.18);
      background: rgba(15,23,42,.5);
      position: relative;
      font-size: 11px;
      color: #e2e8f0;
    }
    .wn-cmdk-search {
      color: #818cf8;
      font-size: 12px;
      flex-shrink: 0;
    }
    .wn-cmdk-typed {
      position: relative;
      font-weight: 600;
      letter-spacing: .3px;
      color: #e2e8f0;
      white-space: pre;
      display: inline-block;
      min-width: 28px;
    }
    .wn-cmdk-typed::before {
      content: "";
      animation: wn-cmdk-type 4.5s steps(1, end) infinite;
    }
    .wn-cmdk-caret {
      display: inline-block;
      width: 1.5px;
      height: 11px;
      background: #a5b4fc;
      margin-left: -2px;
      animation: wn-cmdk-caret 0.9s steps(1, end) infinite;
      flex-shrink: 0;
    }
    .wn-cmdk-enter {
      margin-left: auto;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 2px 7px;
      border-radius: 6px;
      background: rgba(99,102,241,.18);
      border: 1px solid rgba(165,180,252,.45);
      color: #c7d2fe;
      font-size: 10px;
      font-weight: 600;
      opacity: 0;
      animation: wn-cmdk-enter 4.5s ease-in-out infinite;
      flex-shrink: 0;
      white-space: nowrap;
    }
    .wn-cmdk-enter .bi { font-size: 10px; }
    
    /* Result list */
    .wn-cmdk-list {
      list-style: none;
      margin: 0;
      padding: 5px;
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .wn-cmdk-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 8px;
      border-radius: 6px;
      font-size: 11px;
      color: #cbd5e1;
      background: transparent;
      border: 1px solid transparent;
      opacity: 0;
      transform: translateY(4px);
    }
    .wn-cmdk-rico {
      font-size: 13px;
      color: #94a3b8;
      flex-shrink: 0;
    }
    .wn-cmdk-rtxt {
      flex: 1;
      min-width: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .wn-cmdk-rtxt b {
      color: #fde68a;
      font-weight: 700;
      background: rgba(245,158,11,.12);
      padding: 0 2px;
      border-radius: 2px;
    }
    .wn-cmdk-rtag {
      font-size: 9px;
      font-weight: 600;
      padding: 1px 6px;
      border-radius: 999px;
      letter-spacing: .3px;
      text-transform: uppercase;
      flex-shrink: 0;
    }
    .wn-cmdk-tag-pt { background: rgba(99,102,241,.18); color: #a5b4fc; border: 1px solid rgba(165,180,252,.3); }
    .wn-cmdk-tag-ac { background: rgba(16,185,129,.15); color: #6ee7b7; border: 1px solid rgba(110,231,183,.3); }
    .wn-cmdk-tag-pg { background: rgba(56,189,248,.15); color: #60a5fa; border: 1px solid rgba(96,165,250,.3); }
    
    .wn-cmdk-r1 { animation: wn-cmdk-row1 4.5s ease-in-out infinite; }
    .wn-cmdk-r2 { animation: wn-cmdk-row2 4.5s ease-in-out infinite; }
    .wn-cmdk-r3 { animation: wn-cmdk-row3 4.5s ease-in-out infinite; }
    
    /* Keyframes */
    @keyframes wn-cmdk-pulse {
      0%, 8%   { transform: scale(1);    box-shadow: 0 4px 14px rgba(99,102,241,.35), inset 0 1px 0 rgba(255,255,255,.06); }
      14%      { transform: scale(1.12); box-shadow: 0 6px 22px rgba(129,140,248,.7), inset 0 1px 0 rgba(255,255,255,.1); }
      22%, 90% { transform: scale(1);    box-shadow: 0 4px 14px rgba(99,102,241,.35), inset 0 1px 0 rgba(255,255,255,.06); }
      100%     { transform: scale(1);    box-shadow: 0 4px 14px rgba(99,102,241,.35), inset 0 1px 0 rgba(255,255,255,.06); }
    }
    
    @keyframes wn-cmdk-modal {
      0%       { opacity: 0; transform: translateY(-6px) scale(.94); }
      10%      { opacity: 0; transform: translateY(-6px) scale(.94); }
      18%      { opacity: 1; transform: translateY(0) scale(1); }
      85%      { opacity: 1; transform: translateY(0) scale(1); }
      95%, 100%{ opacity: 0; transform: translateY(-6px) scale(.94); }
    }
    
    @keyframes wn-cmdk-type {
      0%, 20%   { content: ""; }
      28%       { content: "a"; }
      34%       { content: "ah"; }
      40%, 90%  { content: "ahm"; }
      95%, 100% { content: ""; }
    }
    
    @keyframes wn-cmdk-caret {
      0%, 49%   { opacity: 1; }
      50%, 99%  { opacity: 0; }
      100%      { opacity: 1; }
    }
    
    @keyframes wn-cmdk-row1 {
      0%, 40%   { opacity: 0; transform: translateY(4px); background: transparent; border-color: transparent; }
      48%       { opacity: 1; transform: translateY(0);   background: transparent; border-color: transparent; }
      62%       { opacity: 1; transform: translateY(0);   background: rgba(99,102,241,.22); border-color: rgba(165,180,252,.55); box-shadow: 0 0 0 2px rgba(99,102,241,.18); }
      78%       { opacity: 1; transform: translateY(0);   background: rgba(99,102,241,.35); border-color: rgba(165,180,252,.75); box-shadow: 0 0 0 3px rgba(99,102,241,.28); }
      86%       { opacity: 1; transform: translateY(0) scale(.98); background: rgba(129,140,248,.45); border-color: rgba(165,180,252,.9); }
      92%, 100% { opacity: 0; transform: translateY(4px); background: transparent; border-color: transparent; box-shadow: none; }
    }
    
    @keyframes wn-cmdk-row2 {
      0%, 50%   { opacity: 0; transform: translateY(4px); }
      58%, 90%  { opacity: 1; transform: translateY(0); }
      95%, 100% { opacity: 0; transform: translateY(4px); }
    }
    
    @keyframes wn-cmdk-row3 {
      0%, 58%   { opacity: 0; transform: translateY(4px); }
      66%, 90%  { opacity: 1; transform: translateY(0); }
      95%, 100% { opacity: 0; transform: translateY(4px); }
    }
    
    @keyframes wn-cmdk-enter {
      0%, 60%   { opacity: 0; transform: scale(.9); }
      68%       { opacity: 1; transform: scale(1); }
      78%       { opacity: 1; transform: scale(1.12); }
      86%, 90%  { opacity: 1; transform: scale(1); }
      95%, 100% { opacity: 0; transform: scale(.9); }
    }
    
    @media (max-width: 575.98px) {
      .wn-cmdk-scene { padding: 8px 10px 10px; gap: 7px; }
      .wn-cmdk-modal { width: 94%; }
      .wn-cmdk-row { padding: 5px 7px; font-size: 10px; }
      .wn-cmdk-rtag { font-size: 8px; padding: 1px 5px; }
      .wn-cmdk-input { padding: 6px 8px; font-size: 10px; gap: 4px; }
      .wn-cmdk-kbd { font-size: 10px; padding: 3px 8px; }
      .wn-cmdk-typed { min-width: 22px; }
      .wn-cmdk-enter { padding: 2px 5px; font-size: 9px; }
    }

    /* ===== wn-kbd — Every shortcut at your fingertips ===== */
    .wn-kbd-scene {
      position: absolute; inset: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .wn-kbd-grid {
      position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(99,102,241,.08) 1px, transparent 1px),
        linear-gradient(90deg, rgba(99,102,241,.08) 1px, transparent 1px);
      background-size: 22px 22px;
      mask-image: radial-gradient(ellipse at 30% 50%, #000 30%, transparent 75%);
    }
    .wn-kbd-press {
      position: absolute;
      top: 50%; left: 16%;
      transform: translate(-50%, -50%);
      display: flex; flex-direction: column; align-items: center; gap: 8px;
      animation: wn-kbd-press-fade 6s ease-in-out infinite;
    }
    .wn-kbd-presslabel {
      font-size: 10px; letter-spacing: 2px; color: #94a3b8;
      text-transform: uppercase; font-weight: 600;
    }
    .wn-kbd-chip {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 22px; height: 22px; padding: 0 6px;
      font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
      font-size: 11px; font-weight: 600;
      color: #e2e8f0;
      background: linear-gradient(180deg, #1e293b, #0f172a);
      border: 1px solid rgba(148,163,184,.28);
      border-radius: 6px;
      box-shadow: 0 2px 0 rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.05);
    }
    .wn-kbd-chip-lg {
      min-width: 44px; height: 44px; font-size: 22px; border-radius: 10px;
      color: #a5b4fc;
      background: linear-gradient(180deg, #312e81, #1e1b4b);
      border-color: rgba(165,180,252,.4);
      box-shadow: 0 4px 0 #0b0a24, 0 6px 18px rgba(99,102,241,.35),
        inset 0 1px 0 rgba(255,255,255,.1);
      animation: wn-kbd-keypress 6s ease-in-out infinite;
    }
    .wn-kbd-ripple {
      position: absolute; bottom: -6px; left: 50%;
      width: 44px; height: 44px;
      border-radius: 50%;
      border: 2px solid #818cf8;
      transform: translate(-50%, 0) scale(.4);
      opacity: 0;
      animation: wn-kbd-ripple-anim 6s ease-out infinite;
    }
    .wn-kbd-modal {
      position: absolute;
      top: 50%; right: 14px;
      transform: translateY(-50%) translateX(40px);
      width: 248px;
      background: linear-gradient(180deg, #1e293b, #172033);
      border: 1px solid rgba(148,163,184,.22);
      border-radius: 12px;
      box-shadow: 0 18px 40px rgba(0,0,0,.55), 0 0 0 1px rgba(99,102,241,.08);
      opacity: 0;
      animation: wn-kbd-modal-in 6s cubic-bezier(.22,.61,.36,1) infinite;
      overflow: hidden;
    }
    .wn-kbd-modalhead {
      display: flex; align-items: center; gap: 5px;
      padding: 7px 10px;
      border-bottom: 1px solid rgba(148,163,184,.15);
      background: rgba(15,23,42,.5);
    }
    .wn-kbd-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: rgba(148,163,184,.3);
    }
    .wn-kbd-dot:nth-child(1) { background: #ef4444; }
    .wn-kbd-dot:nth-child(2) { background: #f59e0b; }
    .wn-kbd-dot:nth-child(3) { background: #10b981; }
    .wn-kbd-title {
      margin-left: 6px;
      font-size: 10.5px; font-weight: 600; color: #cbd5e1;
      display: inline-flex; align-items: center; gap: 5px;
    }
    .wn-kbd-title .bi { color: #818cf8; font-size: 11px; }
    .wn-kbd-list {
      list-style: none; margin: 0;
      padding: 6px 10px 8px;
      display: flex; flex-direction: column; gap: 3px;
    }
    .wn-kbd-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 4px 6px;
      border-radius: 6px;
      opacity: 0;
      animation: wn-kbd-row-in 6s ease-out infinite;
      animation-delay: calc(1.1s + var(--wn-kbd-i) * 0.12s);
    }
    .wn-kbd-row:nth-child(2) { background: rgba(99,102,241,.1); }
    .wn-kbd-keys {
      display: inline-flex; align-items: center; gap: 4px;
    }
    .wn-kbd-plus {
      font-size: 9px; color: #64748b; font-style: italic;
      padding: 0 1px;
    }
    .wn-kbd-label {
      font-size: 11px; color: #cbd5e1; font-weight: 500;
    }
    
    @keyframes wn-kbd-keypress {
      0%, 6% { transform: translateY(0); box-shadow: 0 4px 0 #0b0a24, 0 6px 18px rgba(99,102,241,.35), inset 0 1px 0 rgba(255,255,255,.1); }
      10%, 18% { transform: translateY(4px); box-shadow: 0 0 0 #0b0a24, 0 2px 12px rgba(99,102,241,.55), inset 0 1px 0 rgba(255,255,255,.1); }
      24%, 100% { transform: translateY(0); box-shadow: 0 4px 0 #0b0a24, 0 6px 18px rgba(99,102,241,.35), inset 0 1px 0 rgba(255,255,255,.1); }
    }
    @keyframes wn-kbd-ripple-anim {
      0%, 8% { opacity: 0; transform: translate(-50%, 0) scale(.4); }
      14% { opacity: .8; transform: translate(-50%, 0) scale(.9); }
      26% { opacity: 0; transform: translate(-50%, 0) scale(1.8); }
      100% { opacity: 0; transform: translate(-50%, 0) scale(.4); }
    }
    @keyframes wn-kbd-press-fade {
      0%, 18% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
      30%, 82% { opacity: .35; transform: translate(-50%, -50%) scale(.92); }
      94%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }
    @keyframes wn-kbd-modal-in {
      0%, 18% { opacity: 0; transform: translateY(-50%) translateX(40px) scale(.96); }
      28%, 82% { opacity: 1; transform: translateY(-50%) translateX(0) scale(1); }
      94%, 100% { opacity: 0; transform: translateY(-50%) translateX(40px) scale(.96); }
    }
    @keyframes wn-kbd-row-in {
      0%, 14% { opacity: 0; transform: translateX(10px); }
      30%, 80% { opacity: 1; transform: translateX(0); }
      92%, 100% { opacity: 0; transform: translateX(10px); }
    }
    
    @media (max-width: 575.98px) {
      .wn-kbd-press { left: 14%; gap: 6px; }
      .wn-kbd-chip-lg { min-width: 32px; height: 32px; font-size: 15px; border-radius: 8px; }
      .wn-kbd-ripple { width: 32px; height: 32px; }
      .wn-kbd-presslabel { font-size: 8px; letter-spacing: 1.2px; }
      .wn-kbd-modal { width: 122px; right: 6px; border-radius: 10px; }
      .wn-kbd-modalhead { padding: 5px 7px; gap: 4px; }
      .wn-kbd-dot { width: 5px; height: 5px; }
      .wn-kbd-title { font-size: 8px; margin-left: 3px; gap: 3px; }
      .wn-kbd-title .bi { font-size: 9px; }
      .wn-kbd-list { padding: 4px 6px 5px; gap: 2px; }
      .wn-kbd-row { padding: 2px 3px; }
      .wn-kbd-keys { gap: 3px; }
      .wn-kbd-label { font-size: 8.5px; }
      .wn-kbd-chip { min-width: 14px; height: 14px; font-size: 8px; padding: 0 3px; border-radius: 4px; }
      .wn-kbd-plus { font-size: 7px; padding: 0; }
    }

    /* ===== wn-pc — A patient summary, just by hovering ===== */
    .wn-pc-scene {
      position: absolute; inset: 0;
      padding: 14px 18px;
      font-family: inherit;
    }
    
    .wn-pc-row {
      display: flex; align-items: center; gap: 8px;
      font-size: 12px; color: #94a3b8;
      position: relative;
      z-index: 2;
    }
    .wn-pc-label { letter-spacing: .02em; }
    .wn-pc-link {
      color: #a5b4fc;
      text-decoration: none;
      border-bottom: 1px dashed rgba(165,180,252,.45);
      padding-bottom: 1px;
      font-weight: 600;
      position: relative;
      transition: color .25s ease;
      animation: wn-pc-link-hl 4.6s ease-in-out infinite;
    }
    @keyframes wn-pc-link-hl {
      0%, 14%   { color: #a5b4fc; background: transparent; }
      20%, 78%  { color: #c7d2fe; background: rgba(99,102,241,.18); box-shadow: 0 0 0 4px rgba(99,102,241,.10); border-radius: 4px; }
      86%, 100% { color: #a5b4fc; background: transparent; }
    }
    
    .wn-pc-cursor {
      color: #e2e8f0;
      font-size: 13px;
      filter: drop-shadow(0 1px 2px rgba(0,0,0,.5));
      position: absolute;
      left: 110px;
      top: 14px;
      transform: rotate(-12deg);
      animation: wn-pc-cursor-move 4.6s ease-in-out infinite;
    }
    @keyframes wn-pc-cursor-move {
      0%      { left: 175px; top: 26px; opacity: 0; }
      8%      { left: 175px; top: 26px; opacity: 1; }
      18%     { left: 112px; top: 14px; opacity: 1; }
      78%     { left: 112px; top: 14px; opacity: 1; }
      92%     { left: 175px; top: 26px; opacity: .6; }
      100%    { left: 175px; top: 26px; opacity: 0; }
    }
    
    .wn-pc-card {
      position: absolute;
      left: 18px;
      top: 44px;
      right: 18px;
      background: linear-gradient(180deg, rgba(30,41,59,.96), rgba(15,23,42,.96));
      border: 1px solid rgba(148,163,184,.22);
      border-radius: 12px;
      padding: 10px 12px 11px;
      box-shadow: 0 14px 30px rgba(0,0,0,.45), 0 0 0 1px rgba(99,102,241,.08);
      backdrop-filter: blur(6px);
      transform-origin: 18% top;
      opacity: 0;
      transform: translateY(-6px) scale(.94);
      animation: wn-pc-card-in 4.6s ease-in-out infinite;
      z-index: 3;
    }
    .wn-pc-card::before {
      content: "";
      position: absolute;
      top: -5px; left: 28px;
      width: 10px; height: 10px;
      background: #1e293b;
      border-left: 1px solid rgba(148,163,184,.22);
      border-top: 1px solid rgba(148,163,184,.22);
      transform: rotate(45deg);
    }
    @keyframes wn-pc-card-in {
      0%, 16%   { opacity: 0; transform: translateY(-6px) scale(.94); }
      24%, 78%  { opacity: 1; transform: translateY(0) scale(1); }
      90%, 100% { opacity: 0; transform: translateY(-6px) scale(.94); }
    }
    
    .wn-pc-card-head {
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 9px;
    }
    .wn-pc-avatar {
      width: 32px; height: 32px;
      border-radius: 50%;
      display: grid; place-items: center;
      font-size: 11px; font-weight: 700; color: #fff;
      letter-spacing: .02em;
      /* HSL-seeded teal-violet for "AM" */
      background: linear-gradient(135deg, hsl(258 70% 58%), hsl(196 80% 52%));
      box-shadow: 0 0 0 2px rgba(99,102,241,.22), inset 0 -4px 8px rgba(0,0,0,.18);
      flex: 0 0 auto;
    }
    .wn-pc-id { flex: 1; min-width: 0; }
    .wn-pc-name {
      font-size: 13px; font-weight: 600; color: #e2e8f0;
      line-height: 1.1;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .wn-pc-meta { font-size: 10.5px; color: #94a3b8; margin-top: 2px; }
    
    .wn-pc-chip {
      display: inline-flex; align-items: center; gap: 4px;
      background: rgba(245,158,11,.16);
      color: #fbbf24;
      border: 1px solid rgba(245,158,11,.35);
      font-size: 10px; font-weight: 600;
      padding: 3px 7px;
      border-radius: 999px;
      white-space: nowrap;
      animation: wn-pc-chip-pulse 1.6s ease-in-out infinite;
    }
    .wn-pc-chip i { font-size: 10px; }
    @keyframes wn-pc-chip-pulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,.45); transform: scale(1); }
      50%      { box-shadow: 0 0 0 6px rgba(245,158,11,0);   transform: scale(1.04); }
    }
    
    .wn-pc-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 6px;
    }
    .wn-pc-stat {
      background: rgba(99,102,241,.10);
      border: 1px solid rgba(99,102,241,.20);
      border-radius: 8px;
      padding: 6px 7px;
      display: flex; flex-direction: column; gap: 2px;
      min-width: 0;
      opacity: 0;
      transform: translateY(4px);
      animation: wn-pc-stat-in 4.6s ease-out infinite;
    }
    .wn-pc-stat:nth-child(1) { animation-delay: .05s; }
    .wn-pc-stat:nth-child(2) { animation-delay: .15s; }
    .wn-pc-stat:nth-child(3) { animation-delay: .25s; }
    .wn-pc-stat i {
      color: #818cf8;
      font-size: 12px;
    }
    .wn-pc-stat-k {
      font-size: 9.5px; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em;
    }
    .wn-pc-stat-v {
      font-size: 11.5px; color: #e2e8f0; font-weight: 600;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    @keyframes wn-pc-stat-in {
      0%, 22%   { opacity: 0; transform: translateY(4px); }
      32%, 78%  { opacity: 1; transform: translateY(0); }
      90%, 100% { opacity: 0; transform: translateY(4px); }
    }
    
    .wn-pc-glow {
      position: absolute;
      left: -30px; top: -40px;
      width: 220px; height: 220px;
      background: radial-gradient(circle, rgba(99,102,241,.22), transparent 60%);
      pointer-events: none;
      z-index: 1;
      animation: wn-pc-glow-pan 6s ease-in-out infinite;
    }
    @keyframes wn-pc-glow-pan {
      0%, 100% { transform: translate(0, 0); opacity: .8; }
      50%      { transform: translate(40px, 20px); opacity: 1; }
    }
    
    @media (max-width: 575.98px) {
      .wn-pc-scene { padding: 11px 12px; }
      .wn-pc-card { left: 12px; right: 12px; top: 38px; padding: 8px 10px 9px; }
      .wn-pc-avatar { width: 28px; height: 28px; font-size: 10px; }
      .wn-pc-name { font-size: 12px; }
      .wn-pc-meta { font-size: 10px; }
      .wn-pc-chip { font-size: 9.5px; padding: 2px 6px; }
      .wn-pc-stat { padding: 5px 6px; }
      .wn-pc-stat-k { font-size: 9px; }
      .wn-pc-stat-v { font-size: 10.5px; }
      @keyframes wn-pc-cursor-move {
        0%      { left: 140px; top: 24px; opacity: 0; }
        8%      { left: 140px; top: 24px; opacity: 1; }
        18%     { left: 92px;  top: 12px; opacity: 1; }
        78%     { left: 92px;  top: 12px; opacity: 1; }
        92%     { left: 140px; top: 24px; opacity: .6; }
        100%    { left: 140px; top: 24px; opacity: 0; }
      }
    }

    /* ===== wn-snz — Snooze, pin, get back to it ===== */
    .wn-snz-scene{position:absolute;inset:0;padding:14px 16px;display:flex;align-items:flex-start;justify-content:center;}
    .wn-snz-list{position:relative;width:100%;max-width:300px;height:100%;}
    .wn-snz-row{position:absolute;left:0;right:0;display:flex;align-items:center;gap:10px;padding:10px 12px;background:linear-gradient(180deg,#1e293b 0%,#172033 100%);border:1px solid rgba(148,163,184,.18);border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.35);}
    .wn-snz-row-a{top:0;z-index:3;animation:wn-snz-rowa 9s ease-in-out infinite;}
    .wn-snz-row-b{top:64px;z-index:2;animation:wn-snz-rowb 9s ease-in-out infinite;}
    .wn-snz-icon{flex:0 0 30px;width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;}
    .wn-snz-icon-blue{background:linear-gradient(135deg,#3b82f6,#6366f1);box-shadow:0 4px 10px rgba(59,130,246,.35);}
    .wn-snz-icon-pink{background:linear-gradient(135deg,#ec4899,#8b5cf6);box-shadow:0 4px 10px rgba(236,72,153,.3);}
    .wn-snz-body{flex:1 1 auto;min-width:0;}
    .wn-snz-title{color:#e2e8f0;font-size:12px;font-weight:600;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .wn-snz-meta{color:#94a3b8;font-size:10px;line-height:1.3;margin-top:2px;}
    .wn-snz-btn{flex:0 0 26px;width:26px;height:26px;border-radius:8px;border:1px solid rgba(148,163,184,.22);background:rgba(99,102,241,.14);color:#a5b4fc;font-size:12px;display:flex;align-items:center;justify-content:center;padding:0;animation:wn-snz-btn 9s ease-in-out infinite;}
    .wn-snz-menu{position:absolute;top:42px;right:6px;width:152px;background:#0b1224;border:1px solid rgba(148,163,184,.22);border-radius:10px;padding:6px;box-shadow:0 14px 28px rgba(0,0,0,.5);transform-origin:top right;transform:scale(.85) translateY(-6px);opacity:0;pointer-events:none;animation:wn-snz-menu 9s ease-in-out infinite;}
    .wn-snz-mhead{color:#94a3b8;font-size:9px;text-transform:uppercase;letter-spacing:.08em;padding:4px 8px 6px;}
    .wn-snz-mitem{display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:6px;color:#cbd5e1;font-size:10.5px;font-weight:500;opacity:0;transform:translateX(-6px);animation:wn-snz-mitem 9s ease-in-out infinite;}
    .wn-snz-mitem i{color:#818cf8;font-size:11px;width:12px;text-align:center;}
    .wn-snz-mitem:nth-child(2){animation-delay:.05s;}
    .wn-snz-mitem:nth-child(3){animation-delay:.12s;}
    .wn-snz-mitem:nth-child(4){animation-delay:.19s;}
    .wn-snz-mitem:nth-child(5){animation-delay:.26s;}
    .wn-snz-mitem:nth-child(6){animation-delay:.33s;}
    .wn-snz-mitem:nth-child(4){background:rgba(99,102,241,.16);color:#e0e7ff;}
    .wn-snz-pin{position:absolute;top:-6px;right:-6px;width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#0f172a;font-size:11px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(245,158,11,.55);opacity:0;transform:scale(.4) rotate(-25deg);animation:wn-snz-pin 9s ease-in-out infinite;}
    .wn-snz-pin::after{content:"";position:absolute;inset:-4px;border-radius:50%;border:2px solid #fbbf24;opacity:0;animation:wn-snz-pulse 9s ease-in-out infinite;}
    @keyframes wn-snz-rowa{0%,8%{top:0;}18%{top:0;}55%{top:0;}62%{top:64px;transform:scale(.98);}80%{top:64px;}100%{top:0;transform:scale(1);}}
    @keyframes wn-snz-rowb{0%,55%{top:64px;}62%{top:0;transform:scale(1.02);}70%{top:0;transform:scale(1);}80%{top:0;}100%{top:64px;}}
    @keyframes wn-snz-btn{0%,8%{background:rgba(99,102,241,.14);color:#a5b4fc;transform:scale(1);}12%{background:#6366f1;color:#fff;transform:scale(.9);}18%{background:#6366f1;color:#fff;transform:scale(1);}45%{background:#6366f1;color:#fff;}52%{background:rgba(99,102,241,.14);color:#a5b4fc;}100%{background:rgba(99,102,241,.14);color:#a5b4fc;}}
    @keyframes wn-snz-menu{0%,10%{opacity:0;transform:scale(.85) translateY(-6px);}16%{opacity:1;transform:scale(1) translateY(0);}45%{opacity:1;transform:scale(1) translateY(0);}50%{opacity:0;transform:scale(.9) translateY(-4px);}100%{opacity:0;transform:scale(.85) translateY(-6px);}}
    @keyframes wn-snz-mitem{0%,12%{opacity:0;transform:translateX(-6px);}22%{opacity:1;transform:translateX(0);}45%{opacity:1;transform:translateX(0);}50%{opacity:0;transform:translateX(-4px);}100%{opacity:0;transform:translateX(-6px);}}
    @keyframes wn-snz-pin{0%,52%{opacity:0;transform:scale(.4) rotate(-25deg);}58%{opacity:1;transform:scale(1.25) rotate(8deg);}64%{opacity:1;transform:scale(1) rotate(0);}82%{opacity:1;transform:scale(1) rotate(0);}92%{opacity:0;transform:scale(.6) rotate(-10deg);}100%{opacity:0;transform:scale(.4) rotate(-25deg);}}
    @keyframes wn-snz-pulse{0%,56%{opacity:0;transform:scale(.8);}60%{opacity:.8;transform:scale(1);}72%{opacity:0;transform:scale(1.7);}82%{opacity:0;transform:scale(1);}100%{opacity:0;transform:scale(.8);}}
    @media (max-width:575.98px){.wn-snz-scene{padding:10px 12px;}.wn-snz-list{max-width:260px;}.wn-snz-row-b{top:58px;}.wn-snz-title{font-size:11px;}.wn-snz-meta{font-size:9px;}.wn-snz-menu{width:138px;top:38px;}.wn-snz-mitem{font-size:10px;padding:4px 7px;}@keyframes wn-snz-rowa{0%,8%{top:0;}18%{top:0;}55%{top:0;}62%{top:58px;transform:scale(.98);}80%{top:58px;}100%{top:0;transform:scale(1);}}@keyframes wn-snz-rowb{0%,55%{top:58px;}62%{top:0;transform:scale(1.02);}70%{top:0;transform:scale(1);}80%{top:0;}100%{top:58px;}}}

    /* ===== wn-mention — @mention, and stay in the loop ===== */
    .wn-mention-grid{
      position:absolute; inset:0; padding:12px 14px;
      display:grid; grid-template-rows: 1fr auto; gap:10px;
    }
    .wn-mention-note{
      background:linear-gradient(180deg,#1e293b,#172033);
      border:1px solid rgba(148,163,184,.18);
      border-radius:10px; overflow:hidden;
      box-shadow:0 4px 14px rgba(0,0,0,.25);
    }
    .wn-mention-note-head{
      display:flex; align-items:center; gap:6px;
      padding:6px 10px;
      background:rgba(15,23,42,.6);
      border-bottom:1px solid rgba(148,163,184,.12);
    }
    .wn-mention-dot{ width:8px; height:8px; border-radius:50%; display:inline-block; }
    .wn-mention-dot-r{ background:#ef4444; }
    .wn-mention-dot-y{ background:#f59e0b; }
    .wn-mention-dot-g{ background:#10b981; }
    .wn-mention-note-label{
      margin-left:6px; font-size:10px; letter-spacing:.08em;
      text-transform:uppercase; color:#94a3b8;
    }
    .wn-mention-note-body{
      padding:10px 12px; min-height:62px;
      font-size:12.5px; color:#cbd5e1; line-height:1.5;
      display:flex; flex-wrap:wrap; align-items:center; gap:2px;
    }
    .wn-mention-text{ white-space:pre; }
    .wn-mention-typed{ position:relative; display:inline-block; min-height:18px; }
    .wn-mention-raw{
      color:#a5b4fc; font-weight:600;
      display:inline-block;
      max-width:0; overflow:hidden; white-space:nowrap;
      vertical-align:bottom;
      animation: wn-mention-type 6s ease-in-out infinite;
    }
    .wn-mention-chip{
      position:absolute; left:0; top:50%;
      transform:translateY(-50%) scale(.8);
      display:inline-flex; align-items:center; gap:3px;
      padding:2px 8px 2px 6px; border-radius:999px;
      background:linear-gradient(135deg, rgba(99,102,241,.28), rgba(139,92,246,.25));
      border:1px solid rgba(129,140,248,.55);
      color:#e0e7ff; font-weight:600; font-size:11.5px;
      white-space:nowrap; opacity:0;
      box-shadow:0 0 0 0 rgba(129,140,248,.4);
      animation: wn-mention-chip 6s ease-in-out infinite;
    }
    .wn-mention-chip i{ font-size:12px; color:#a5b4fc; }
    .wn-mention-caret{
      display:inline-block; width:1.5px; height:14px;
      background:#a5b4fc; vertical-align:middle; margin-left:1px;
      animation: wn-mention-blink 1s steps(2) infinite;
    }
    .wn-mention-bottom{
      display:grid; grid-template-columns: auto 1fr; gap:10px; align-items:center;
    }
    .wn-mention-bell{
      position:relative; width:42px; height:42px; border-radius:10px;
      background:linear-gradient(160deg,#1e293b,#0f172a);
      border:1px solid rgba(148,163,184,.18);
      display:flex; align-items:center; justify-content:center;
      color:#fbbf24; font-size:18px;
      animation: wn-mention-shake 6s ease-in-out infinite;
    }
    .wn-mention-badge{
      position:absolute; top:-5px; right:-5px;
      min-width:16px; height:16px; padding:0 4px;
      border-radius:999px; background:#ef4444; color:#fff;
      font-size:10px; font-weight:700; line-height:16px; text-align:center;
      border:2px solid #0f172a;
      transform:scale(0);
      animation: wn-mention-pop 6s ease-in-out infinite;
    }
    .wn-mention-ring{
      position:absolute; inset:-2px; border-radius:12px;
      border:2px solid rgba(245,158,11,.6);
      opacity:0;
      animation: wn-mention-ring 6s ease-out infinite;
    }
    .wn-mention-activity{
      background:#1e293b;
      border:1px solid rgba(148,163,184,.18);
      border-radius:10px; overflow:hidden;
      transform:translateX(120%); opacity:0;
      animation: wn-mention-slide 6s ease-in-out infinite;
    }
    .wn-mention-tab{
      display:flex; align-items:center; gap:5px;
      padding:4px 10px;
      background:linear-gradient(90deg, rgba(99,102,241,.22), rgba(99,102,241,0));
      border-bottom:1px solid rgba(148,163,184,.12);
      font-size:10px; letter-spacing:.06em; text-transform:uppercase;
      color:#a5b4fc; font-weight:700;
    }
    .wn-mention-tab i{ font-size:11px; }
    .wn-mention-row{
      display:flex; align-items:center; gap:8px;
      padding:7px 10px;
    }
    .wn-mention-avatar{
      width:24px; height:24px; border-radius:50%;
      background:linear-gradient(135deg,#6366f1,#8b5cf6);
      color:#fff; font-size:9.5px; font-weight:700;
      display:flex; align-items:center; justify-content:center;
      flex-shrink:0; letter-spacing:.02em;
    }
    .wn-mention-row-text{
      font-size:11px; color:#cbd5e1; line-height:1.35;
      display:flex; flex-direction:column; min-width:0;
    }
    .wn-mention-row-text strong{ color:#a5b4fc; font-weight:600; }
    .wn-mention-time{ color:#64748b; font-size:10px; margin-top:1px; }
    
    @keyframes wn-mention-type{
      0%, 8%   { max-width:0; }
      28%, 55% { max-width:78px; }
      60%      { max-width:78px; opacity:0; }
      61%, 95% { max-width:0; opacity:0; }
      100%     { max-width:0; opacity:1; }
    }
    @keyframes wn-mention-chip{
      0%, 55%       { opacity:0; transform:translateY(-50%) scale(.7); box-shadow:0 0 0 0 rgba(129,140,248,.45); }
      60%           { opacity:1; transform:translateY(-50%) scale(1.06); box-shadow:0 0 0 6px rgba(129,140,248,0); }
      66%, 92%      { opacity:1; transform:translateY(-50%) scale(1);    box-shadow:0 0 0 0 rgba(129,140,248,0); }
      97%, 100%     { opacity:0; transform:translateY(-50%) scale(.7); }
    }
    @keyframes wn-mention-blink{
      0%, 100% { opacity:1; }
      50%      { opacity:0; }
    }
    @keyframes wn-mention-pop{
      0%, 58%   { transform:scale(0); }
      64%       { transform:scale(1.35); }
      70%, 90%  { transform:scale(1); }
      96%, 100% { transform:scale(0); }
    }
    @keyframes wn-mention-ring{
      0%, 58%   { opacity:0; transform:scale(.9); }
      62%       { opacity:.9; transform:scale(1); }
      78%       { opacity:0; transform:scale(1.5); }
      100%      { opacity:0; transform:scale(.9); }
    }
    @keyframes wn-mention-shake{
      0%, 58%, 90%, 100% { transform:rotate(0); }
      63% { transform:rotate(-14deg); }
      67% { transform:rotate(12deg); }
      71% { transform:rotate(-8deg); }
      75% { transform:rotate(6deg); }
      79% { transform:rotate(0); }
    }
    @keyframes wn-mention-slide{
      0%, 64%   { transform:translateX(120%); opacity:0; }
      72%, 92%  { transform:translateX(0);    opacity:1; }
      98%, 100% { transform:translateX(120%); opacity:0; }
    }
    
    @media (max-width: 575.98px){
      .wn-mention-grid{ padding:10px 12px; gap:8px; }
      .wn-mention-note-body{ min-height:54px; font-size:12px; padding:8px 10px; }
      .wn-mention-bell{ width:38px; height:38px; font-size:16px; }
      .wn-mention-row-text{ font-size:10.5px; }
      .wn-mention-tab{ font-size:9.5px; padding:3px 8px; }
    }

    /* ===== wn-focus — Distraction-free consultations ===== */
    .wn-focus-stage {
      background:
        radial-gradient(120% 80% at 80% 10%, rgba(99,102,241,.18), transparent 60%),
        radial-gradient(120% 80% at 10% 100%, rgba(139,92,246,.14), transparent 55%),
        #0f172a;
    }
    .wn-focus-app {
      position: absolute; inset: 14px;
      border-radius: 10px;
      background: #0b1224;
      border: 1px solid rgba(148,163,184,.14);
      overflow: hidden;
    }
    /* Sidebar */
    .wn-focus-sidebar {
      position: absolute; top: 0; left: 0; bottom: 0;
      width: 64px;
      background: #1e293b;
      border-right: 1px solid rgba(148,163,184,.18);
      padding: 10px 10px;
      display: flex; flex-direction: column; gap: 8px;
      animation: wn-focus-sidebar 6s ease-in-out infinite;
    }
    .wn-focus-logo {
      width: 22px; height: 22px; border-radius: 6px;
      background: linear-gradient(135deg,#6366f1,#8b5cf6);
      margin-bottom: 6px;
      box-shadow: 0 0 10px rgba(129,140,248,.5);
    }
    .wn-focus-navitem {
      height: 10px; border-radius: 4px;
      background: rgba(148,163,184,.22);
    }
    .wn-focus-navitem-active {
      background: linear-gradient(90deg,#6366f1,#818cf8);
      box-shadow: 0 0 8px rgba(99,102,241,.55);
    }
    /* Header */
    .wn-focus-header {
      position: absolute; top: 0; left: 64px; right: 0;
      height: 28px;
      background: #1e293b;
      border-bottom: 1px solid rgba(148,163,184,.18);
      display: flex; align-items: center; gap: 6px;
      padding: 0 10px;
      animation: wn-focus-header 6s ease-in-out infinite;
    }
    .wn-focus-crumb {
      width: 38px; height: 8px; border-radius: 3px;
      background: rgba(165,180,252,.35);
    }
    .wn-focus-crumb-short { width: 22px; background: rgba(148,163,184,.25); }
    .wn-focus-spacer { flex: 1; }
    .wn-focus-avatar {
      width: 16px; height: 16px; border-radius: 50%;
      background: linear-gradient(135deg,#38bdf8,#6366f1);
    }
    /* Main + textarea */
    .wn-focus-main {
      position: absolute; top: 28px; left: 64px; right: 0; bottom: 0;
      padding: 10px;
      animation: wn-focus-main 6s ease-in-out infinite;
    }
    .wn-focus-textarea {
      position: absolute; inset: 10px;
      background: linear-gradient(180deg, rgba(30,41,59,.95), rgba(15,23,42,.95));
      border: 1px solid rgba(129,140,248,.35);
      border-radius: 8px;
      padding: 12px 14px;
      display: flex; flex-direction: column; gap: 8px;
      box-shadow: 0 0 0 0 rgba(99,102,241,0), inset 0 0 20px rgba(99,102,241,.06);
      animation: wn-focus-textglow 6s ease-in-out infinite;
    }
    .wn-focus-line {
      height: 6px; border-radius: 3px;
      background: rgba(203,213,225,.35);
      width: 92%;
    }
    .wn-focus-line-short { width: 55%; }
    .wn-focus-line-med { width: 75%; }
    .wn-focus-caret {
      position: absolute; left: 14px; bottom: 14px;
      width: 2px; height: 12px;
      background: #a5b4fc;
      animation: wn-focus-caret 1s steps(2,end) infinite;
    }
    /* Focus pill */
    .wn-focus-pill {
      position: absolute; top: 8px; right: 8px;
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 9px;
      border-radius: 999px;
      font: 600 9px/1 ui-sans-serif, system-ui, sans-serif;
      letter-spacing: .04em;
      color: #fde68a;
      background: linear-gradient(135deg, rgba(245,158,11,.22), rgba(139,92,246,.22));
      border: 1px solid rgba(251,191,36,.55);
      box-shadow: 0 4px 14px rgba(245,158,11,.25);
      opacity: 0; transform: translateY(-4px) scale(.9);
      animation: wn-focus-pill 6s ease-in-out infinite;
    }
    .wn-focus-pill i { font-size: 10px; color: #fbbf24; }
    /* Tap indicator */
    .wn-focus-tap {
      position: absolute;
      top: 6px; left: 70px;
      width: 22px; height: 22px;
      display: grid; place-items: center;
      color: #a5b4fc;
      font-size: 11px;
      opacity: 0;
      animation: wn-focus-tap 6s ease-in-out infinite;
    }
    .wn-focus-ring {
      position: absolute; inset: 0;
      border-radius: 50%;
      border: 2px solid rgba(165,180,252,.85);
      animation: wn-focus-ring 6s ease-in-out infinite;
    }
    /* Keyframes — every one returns to its 0% state */
    @keyframes wn-focus-sidebar {
      0%, 18%   { transform: translateX(0); opacity: 1; }
      28%, 70%  { transform: translateX(-72px); opacity: 0; }
      82%, 100% { transform: translateX(0); opacity: 1; }
    }
    @keyframes wn-focus-header {
      0%, 18%   { transform: translateY(0); opacity: 1; }
      28%, 70%  { transform: translateY(-30px); opacity: 0; }
      82%, 100% { transform: translateY(0); opacity: 1; }
    }
    @keyframes wn-focus-main {
      0%, 18%   { top: 28px; left: 64px; }
      28%, 70%  { top: 0;    left: 0; }
      82%, 100% { top: 28px; left: 64px; }
    }
    @keyframes wn-focus-textglow {
      0%, 18%   { box-shadow: inset 0 0 20px rgba(99,102,241,.06); border-color: rgba(129,140,248,.35); }
      28%, 70%  { box-shadow: 0 0 0 1px rgba(99,102,241,.45), inset 0 0 36px rgba(99,102,241,.18); border-color: rgba(165,180,252,.7); }
      82%, 100% { box-shadow: inset 0 0 20px rgba(99,102,241,.06); border-color: rgba(129,140,248,.35); }
    }
    @keyframes wn-focus-pill {
      0%, 20%   { opacity: 0; transform: translateY(-4px) scale(.9); }
      30%, 70%  { opacity: 1; transform: translateY(0) scale(1); }
      80%, 100% { opacity: 0; transform: translateY(-4px) scale(.9); }
    }
    @keyframes wn-focus-tap {
      0%, 6%    { opacity: 0; transform: scale(.6); }
      10%, 16%  { opacity: 1; transform: scale(1); }
      22%, 95%  { opacity: 0; transform: scale(1.2); }
      100%      { opacity: 0; transform: scale(.6); }
    }
    @keyframes wn-focus-ring {
      0%, 6%    { transform: scale(.4); opacity: 0; }
      12%       { transform: scale(1);  opacity: 1; }
      22%       { transform: scale(1.8);opacity: 0; }
      100%      { transform: scale(.4); opacity: 0; }
    }
    @keyframes wn-focus-caret {
      0%, 100% { opacity: 1; }
      50%      { opacity: 0; }
    }
    /* Mobile */
    @media (max-width: 575.98px) {
      .wn-focus-app { inset: 10px; }
      .wn-focus-sidebar { width: 54px; padding: 8px; }
      .wn-focus-header { left: 54px; height: 24px; padding: 0 8px; }
      .wn-focus-main { left: 54px; top: 24px; padding: 8px; }
      .wn-focus-tap { left: 60px; }
      @keyframes wn-focus-sidebar {
        0%, 18%   { transform: translateX(0); opacity: 1; }
        28%, 70%  { transform: translateX(-60px); opacity: 0; }
        82%, 100% { transform: translateX(0); opacity: 1; }
      }
      @keyframes wn-focus-main {
        0%, 18%   { top: 24px; left: 54px; }
        28%, 70%  { top: 0;    left: 0; }
        82%, 100% { top: 24px; left: 54px; }
      }
    }
    @media (prefers-reduced-motion: reduce) {
      .wn-focus-sidebar, .wn-focus-header, .wn-focus-main,
      .wn-focus-textarea, .wn-focus-pill, .wn-focus-tap,
      .wn-focus-ring, .wn-focus-caret { animation: none; }
      .wn-focus-pill { opacity: 1; transform: none; }
    }

    /* ===== wn-tpl — Your phrase library, one click away ===== */
    /* ============ wn-tpl: Templates dropdown + typewriter ============ */
    .wn-tpl-scene {
      position: absolute; inset: 14px;
      display: flex; flex-direction: column; gap: 8px;
      font-family: inherit;
    }
    
    /* --- Toolbar row above textarea --- */
    .wn-tpl-toolbar {
      display: flex; align-items: center; justify-content: space-between;
      gap: 8px;
    }
    .wn-tpl-label {
      font-size: 10px; color: #94a3b8; letter-spacing: .04em;
      display: inline-flex; align-items: center; gap: 6px;
    }
    .wn-tpl-label i { color: #a5b4fc; font-size: 12px; }
    
    .wn-tpl-btn {
      position: relative;
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 9px;
      font-size: 10.5px; font-weight: 600;
      color: #e2e8f0;
      background: linear-gradient(180deg, #4F46E5 0%, #4338ca 100%);
      border: 1px solid rgba(165,180,252,.5);
      border-radius: 7px;
      box-shadow: 0 4px 12px -4px rgba(79,70,229,.55), inset 0 1px 0 rgba(255,255,255,.15);
      animation: wn-tpl-btnPulse 7s ease-in-out infinite;
    }
    .wn-tpl-btn i { font-size: 11px; }
    .wn-tpl-caret {
      display: inline-block;
      transform-origin: center;
      animation: wn-tpl-caretFlip 7s ease-in-out infinite;
    }
    
    /* --- Textarea --- */
    .wn-tpl-textarea {
      position: relative;
      flex: 1;
      background: #1e293b;
      border: 1px solid rgba(148,163,184,.22);
      border-radius: 9px;
      padding: 10px 12px;
      overflow: hidden;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
    }
    .wn-tpl-caret-blink {
      position: absolute;
      top: 11px; left: 12px;
      width: 1.5px; height: 11px;
      background: #a5b4fc;
      animation: wn-tpl-blink 1s steps(2) infinite, wn-tpl-caretMove 7s ease-in-out infinite;
    }
    .wn-tpl-typed {
      display: flex; flex-direction: column; gap: 4px;
      font-size: 10.5px; line-height: 1.35;
      color: #cbd5e1;
    }
    .wn-tpl-line {
      display: block;
      opacity: 0;
      transform: translateX(-8px);
      white-space: nowrap; overflow: hidden;
      max-width: 100%; text-overflow: clip;
    }
    .wn-tpl-l1 { animation: wn-tpl-typeLine 7s ease-in-out infinite; animation-delay: 0s; }
    .wn-tpl-l2 { animation: wn-tpl-typeLine 7s ease-in-out infinite; animation-delay: .18s; }
    .wn-tpl-l3 { animation: wn-tpl-typeLine 7s ease-in-out infinite; animation-delay: .36s; }
    
    /* --- Dropdown menu --- */
    .wn-tpl-menu {
      position: absolute;
      top: 32px; right: 14px;
      width: 188px;
      background: linear-gradient(180deg, #1e293b 0%, #172033 100%);
      border: 1px solid rgba(148,163,184,.28);
      border-radius: 10px;
      padding: 6px 4px;
      box-shadow: 0 18px 36px -10px rgba(0,0,0,.6), 0 0 0 1px rgba(99,102,241,.18);
      transform-origin: top right;
      opacity: 0;
      transform: translateY(-6px) scale(.94);
      pointer-events: none;
      z-index: 3;
      animation: wn-tpl-menuOpen 7s ease-in-out infinite;
    }
    .wn-tpl-group {
      font-size: 8.5px; letter-spacing: .12em; text-transform: uppercase;
      color: #64748b; padding: 4px 8px 2px;
    }
    .wn-tpl-row {
      position: relative;
      display: flex; align-items: center; gap: 7px;
      padding: 4px 8px;
      font-size: 10px; color: #cbd5e1;
      border-radius: 6px;
      z-index: 1;
    }
    .wn-tpl-row i { color: #818cf8; font-size: 11px; width: 12px; }
    .wn-tpl-row span { flex: 1; }
    .wn-tpl-row kbd {
      font-size: 8px; padding: 1px 4px;
      background: rgba(99,102,241,.18);
      color: #a5b4fc;
      border: 1px solid rgba(165,180,252,.25);
      border-radius: 3px;
    }
    .wn-tpl-highlight {
      position: absolute;
      left: 4px; right: 4px;
      top: 22px; height: 19px;
      background: linear-gradient(90deg, rgba(99,102,241,.35), rgba(139,92,246,.25));
      border: 1px solid rgba(165,180,252,.55);
      border-radius: 6px;
      box-shadow: 0 0 0 2px rgba(99,102,241,.12);
      z-index: 0;
      opacity: 0;
      animation: wn-tpl-highlight 7s ease-in-out infinite;
    }
    
    /* ============ Keyframes ============ */
    @keyframes wn-tpl-blink {
      0%,49% { opacity: 1; }
      50%,100% { opacity: 0; }
    }
    @keyframes wn-tpl-caretMove {
      /* caret hides while text is typing, returns when textarea empties */
      0%, 8%   { opacity: 1; }
      9%, 78%  { opacity: 0; }
      85%,100% { opacity: 1; }
    }
    @keyframes wn-tpl-btnPulse {
      0%, 8%   { box-shadow: 0 4px 12px -4px rgba(79,70,229,.55), inset 0 1px 0 rgba(255,255,255,.15); }
      10%, 18% { box-shadow: 0 6px 18px -2px rgba(99,102,241,.85), inset 0 1px 0 rgba(255,255,255,.25); }
      30%,100% { box-shadow: 0 4px 12px -4px rgba(79,70,229,.55), inset 0 1px 0 rgba(255,255,255,.15); }
    }
    @keyframes wn-tpl-caretFlip {
      0%, 10%  { transform: rotate(0deg); }
      14%, 38% { transform: rotate(-180deg); }
      42%,100% { transform: rotate(0deg); }
    }
    @keyframes wn-tpl-menuOpen {
      0%, 10%  { opacity: 0; transform: translateY(-6px) scale(.94); }
      14%, 38% { opacity: 1; transform: translateY(0) scale(1); }
      42%,100% { opacity: 0; transform: translateY(-6px) scale(.94); }
    }
    @keyframes wn-tpl-highlight {
      /* slide highlight from row1 -> row2 -> row3 -> snap to row1 (Normal exam) */
      0%, 14%  { opacity: 0; top: 22px; }
      16%, 19% { opacity: 1; top: 22px; }
      22%, 25% { opacity: 1; top: 41px; }
      28%, 31% { opacity: 1; top: 79px; }
      33%, 36% { opacity: 1; top: 22px; transform: scale(1.04); }
      38%, 100%{ opacity: 0; top: 22px; transform: scale(1); }
    }
    @keyframes wn-tpl-typeLine {
      /* lines stay empty during dropdown phase, then slide-in, hold, then clear */
      0%, 42%  { opacity: 0; transform: translateX(-8px); max-width: 0; }
      46%, 50% { opacity: 1; transform: translateX(0);    max-width: 100%; }
      78%      { opacity: 1; transform: translateX(0);    max-width: 100%; }
      86%      { opacity: 0; transform: translateX(-8px); max-width: 0; }
      100%     { opacity: 0; transform: translateX(-8px); max-width: 0; }
    }
    
    /* ============ Mobile (≤ 575.98px) ============ */
    @media (max-width: 575.98px) {
      .wn-tpl-scene { inset: 11px; gap: 6px; }
      .wn-tpl-btn { font-size: 9.5px; padding: 4px 7px; }
      .wn-tpl-label { font-size: 9px; }
      .wn-tpl-menu { width: 156px; top: 28px; right: 11px; }
      .wn-tpl-row { font-size: 9.5px; padding: 3px 7px; }
      .wn-tpl-highlight { height: 17px; top: 21px; }
      .wn-tpl-typed { font-size: 9.5px; }
      @keyframes wn-tpl-highlight {
        0%, 14%  { opacity: 0; top: 21px; }
        16%, 19% { opacity: 1; top: 21px; }
        22%, 25% { opacity: 1; top: 38px; }
        28%, 31% { opacity: 1; top: 73px; }
        33%, 36% { opacity: 1; top: 21px; transform: scale(1.04); }
        38%, 100%{ opacity: 0; top: 21px; transform: scale(1); }
      }
    }

    /* ====================================================================== */
    /* Light-theme mockup overrides (stages follow selected app theme)         */
    /* ====================================================================== */
    html:not(.dark) #whatsNewV9Modal .wn-v11-stage {
        background: radial-gradient(circle at 50% 50%, rgba(99,102,241,.14), transparent 60%), var(--wn-bg-deep);
    }
    html:not(.dark) #whatsNewV9Modal .wn-cl-list li i { color: #6366f1; }

    html:not(.dark) #whatsNewV9Modal .wn-notif-bell { background: var(--wn-bg-surface); color: var(--wn-accent-soft); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-badge { border-color: var(--wn-bg-deep); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-panel { background: var(--wn-glass); box-shadow: 0 10px 30px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.8); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-title { color: var(--wn-text); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-row { background: var(--wn-bg-inset); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-line { background: linear-gradient(90deg, var(--wn-line), rgba(30,41,59,.08)); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-line-2 { background: linear-gradient(90deg, var(--wn-line-muted), rgba(100,116,139,.08)); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-chip { background: var(--wn-chip-bg); color: var(--wn-accent-soft); }
    html:not(.dark) #whatsNewV9Modal .wn-notif-dock { background: linear-gradient(180deg, rgba(241,245,249,.9), rgba(248,250,252,.7)); }

    html:not(.dark) #whatsNewV9Modal .wn-todo-chip { background: var(--wn-bg-surface); color: var(--wn-muted); }
    html:not(.dark) #whatsNewV9Modal .wn-todo-badge { background: rgba(255,255,255,.75); }
    html:not(.dark) #whatsNewV9Modal .wn-todo-bar { background: rgba(148,163,184,.25); }
    html:not(.dark) #whatsNewV9Modal .wn-todo-row { background: var(--wn-bg-row); color: var(--wn-text); border-color: var(--wn-border); }

    html:not(.dark) #whatsNewV9Modal .wn-pal-sw,
    html:not(.dark) #whatsNewV9Modal .wn-pal-preview { background: var(--wn-bg-card); box-shadow: 0 4px 14px rgba(15,23,42,.08); }
    html:not(.dark) #whatsNewV9Modal .wn-pal-lbl { color: var(--wn-muted); }
    html:not(.dark) #whatsNewV9Modal .wn-pal-prev-l1 { background: #cbd5e1; }
    html:not(.dark) #whatsNewV9Modal .wn-pal-prev-l2,
    html:not(.dark) #whatsNewV9Modal .wn-pal-prev-bar { background: #e2e8f0; }

    html:not(.dark) #whatsNewV9Modal .wn-cmdk-modal,
    html:not(.dark) #whatsNewV9Modal .wn-kbd-modal { background: var(--wn-bg-card); border-color: var(--wn-border); box-shadow: 0 12px 32px rgba(15,23,42,.12); }
    html:not(.dark) #whatsNewV9Modal .wn-cmdk-input { background: var(--wn-bg-inset); color: var(--wn-text); }
    html:not(.dark) #whatsNewV9Modal .wn-cmdk-row { color: var(--wn-text); }
    html:not(.dark) #whatsNewV9Modal .wn-kbd-row { color: var(--wn-text); }
    html:not(.dark) #whatsNewV9Modal .wn-kbd-chip { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }

    html:not(.dark) #whatsNewV9Modal .wn-pc-card { background: var(--wn-bg-card); border-color: var(--wn-border); box-shadow: 0 8px 24px rgba(15,23,42,.1); }
    html:not(.dark) #whatsNewV9Modal .wn-pc-name { color: var(--wn-text); }
    html:not(.dark) #whatsNewV9Modal .wn-pc-meta,
    html:not(.dark) #whatsNewV9Modal .wn-pc-stat-k { color: var(--wn-muted); }

    html:not(.dark) #whatsNewV9Modal .wn-snz-row { background: var(--wn-bg-row); border-color: var(--wn-border); }
    html:not(.dark) #whatsNewV9Modal .wn-snz-title { color: var(--wn-text); }
    html:not(.dark) #whatsNewV9Modal .wn-snz-menu { background: var(--wn-bg-card); border-color: var(--wn-border); box-shadow: 0 8px 24px rgba(15,23,42,.12); }

    html:not(.dark) #whatsNewV9Modal .wn-mention-note { background: var(--wn-bg-card); border-color: var(--wn-border); }
    html:not(.dark) #whatsNewV9Modal .wn-mention-activity { background: var(--wn-bg-card); border-color: var(--wn-border); }
    html:not(.dark) #whatsNewV9Modal .wn-mention-text { color: var(--wn-text); }

    html:not(.dark) #whatsNewV9Modal .wn-focus-app { background: var(--wn-bg-card); }
    html:not(.dark) #whatsNewV9Modal .wn-focus-sidebar { background: #f1f5f9; }
    html:not(.dark) #whatsNewV9Modal .wn-focus-header { background: #fff; border-color: var(--wn-border); }
    html:not(.dark) #whatsNewV9Modal .wn-focus-line { background: #cbd5e1; }

    html:not(.dark) #whatsNewV9Modal .wn-tpl-toolbar,
    html:not(.dark) #whatsNewV9Modal .wn-tpl-textarea,
    html:not(.dark) #whatsNewV9Modal .wn-tpl-menu { background: var(--wn-bg-card); border-color: var(--wn-border); }
    html:not(.dark) #whatsNewV9Modal .wn-tpl-label,
    html:not(.dark) #whatsNewV9Modal .wn-tpl-line { color: var(--wn-text); }

    /* ===== wn-qnote — Quick Notes scratchpad ===== */
    .wn-qnote-scene { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:14px; }
    .wn-qnote-modal {
        width:88%; max-width:320px; background:var(--wn-bg-card); border:1px solid var(--wn-border);
        border-radius:12px; padding:10px 12px; box-shadow:0 10px 28px rgba(0,0,0,.28);
        animation:wn-qnote-pop 5s ease-in-out infinite;
    }
    html:not(.dark) #whatsNewV9Modal .wn-qnote-modal { box-shadow:0 10px 28px rgba(15,23,42,.12); }
    .wn-qnote-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
    .wn-qnote-title { font-size:10px; font-weight:700; color:var(--wn-text); display:flex; align-items:center; gap:5px; }
    .wn-qnote-title i { color:var(--wn-accent-soft); }
    .wn-qnote-pin { width:20px; height:20px; border-radius:6px; background:rgba(99,102,241,.15); color:var(--wn-accent-soft); display:flex; align-items:center; justify-content:center; font-size:10px; animation:wn-qnote-pin 5s ease-in-out infinite; }
    .wn-qnote-body { background:var(--wn-bg-inset); border:1px solid var(--wn-border); border-radius:8px; padding:8px; min-height:52px; text-align:left; }
    .wn-qnote-line { display:block; height:5px; border-radius:3px; background:var(--wn-line); margin-bottom:5px; }
    .wn-qnote-line-1 { width:72%; animation:wn-qnote-type1 5s ease-in-out infinite; }
    .wn-qnote-line-2 { width:55%; animation:wn-qnote-type2 5s ease-in-out infinite; }
    .wn-qnote-caret { display:inline-block; width:2px; height:10px; background:var(--wn-accent-soft); vertical-align:middle; animation:wn-qnote-blink 1s step-end infinite; }
    @keyframes wn-qnote-pop{0%,8%{opacity:0;transform:scale(.92) translateY(8px);}12%,88%{opacity:1;transform:scale(1) translateY(0);}100%{opacity:0;transform:scale(.92) translateY(8px);}}
    @keyframes wn-qnote-pin{0%,40%{color:var(--wn-accent-soft);}50%,70%{color:#f59e0b;background:rgba(245,158,11,.2);}80%,100%{color:var(--wn-accent-soft);}}
    @keyframes wn-qnote-type1{0%,20%{width:0;opacity:0;}30%,80%{width:72%;opacity:1;}90%,100%{width:0;opacity:0;}}
    @keyframes wn-qnote-type2{0%,35%{width:0;opacity:0;}45%,80%{width:55%;opacity:1;}90%,100%{width:0;opacity:0;}}
    @keyframes wn-qnote-blink{50%{opacity:0;}}

    /* ===== wn-ndraw — Notes drawer ===== */
    .wn-ndraw-scene { position:absolute; inset:0; overflow:hidden; }
    .wn-ndraw-app { position:absolute; inset:0; background:var(--wn-bg-deep); opacity:.55; }
    .wn-ndraw-panel {
        position:absolute; top:0; right:0; width:72%; height:100%;
        background:var(--wn-glass); border-left:1px solid var(--wn-border);
        padding:10px; display:flex; flex-direction:column; gap:8px;
        animation:wn-ndraw-slide 6s ease-in-out infinite;
        box-shadow:-8px 0 24px rgba(0,0,0,.25);
    }
    html:not(.dark) #whatsNewV9Modal .wn-ndraw-panel { box-shadow:-8px 0 24px rgba(15,23,42,.1); }
    .wn-ndraw-head { display:flex; align-items:center; justify-content:space-between; }
    .wn-ndraw-head span { font-size:10px; font-weight:700; color:var(--wn-text); }
    .wn-ndraw-filters { display:flex; gap:5px; }
    .wn-ndraw-chip { font-size:8px; font-weight:600; padding:3px 7px; border-radius:999px; background:var(--wn-bg-inset); color:var(--wn-muted); border:1px solid var(--wn-border); }
    .wn-ndraw-chip-on { background:rgba(99,102,241,.18); color:var(--wn-accent-soft); border-color:rgba(99,102,241,.35); animation:wn-ndraw-chip 6s ease-in-out infinite; }
    .wn-ndraw-card { background:var(--wn-bg-row); border:1px solid var(--wn-border); border-radius:8px; padding:7px 8px; text-align:left; }
    .wn-ndraw-card-pin { border-left:3px solid #f59e0b; }
    .wn-ndraw-card-title { font-size:9px; font-weight:700; color:var(--wn-text); margin-bottom:4px; }
    .wn-ndraw-card-line { height:4px; border-radius:2px; background:var(--wn-line-muted); width:80%; }
    .wn-ndraw-fab { position:absolute; bottom:12px; right:14px; width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; box-shadow:0 4px 14px rgba(99,102,241,.45); animation:wn-ndraw-fab 6s ease-in-out infinite; }
    @keyframes wn-ndraw-slide{0%,10%{transform:translateX(100%);opacity:0;}18%,85%{transform:translateX(0);opacity:1;}95%,100%{transform:translateX(100%);opacity:0;}}
    @keyframes wn-ndraw-chip{0%,25%{background:var(--wn-bg-inset);color:var(--wn-muted);}35%,75%{background:rgba(99,102,241,.18);color:var(--wn-accent-soft);}85%,100%{background:var(--wn-bg-inset);color:var(--wn-muted);}}
    @keyframes wn-ndraw-fab{0%,80%,100%{transform:scale(1);}45%{transform:scale(1.12);}}

    /* ===== wn-rxtpl — Drug prescription templates ===== */
    .wn-rxtpl-scene { position:absolute; inset:12px; display:flex; flex-direction:column; gap:7px; }
    .wn-rxtpl-head { display:flex; align-items:center; gap:6px; }
    .wn-rxtpl-drug { flex:1; font-size:10px; font-weight:800; color:var(--wn-text); background:var(--wn-bg-inset); border:1px solid var(--wn-border); border-radius:7px; padding:5px 8px; text-align:left; }
    .wn-rxtpl-badge { font-size:8px; font-weight:700; padding:3px 6px; border-radius:999px; background:rgba(16,185,129,.15); color:#10b981; border:1px solid rgba(16,185,129,.35); animation:wn-rxtpl-badge 5.5s ease-in-out infinite; }
    .wn-rxtpl-fields { display:grid; grid-template-columns:1fr 1fr 1fr; gap:5px; }
    .wn-rxtpl-field { background:var(--wn-bg-row); border:1px solid var(--wn-border); border-radius:6px; padding:5px 6px; text-align:left; }
    .wn-rxtpl-field-k { font-size:7px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--wn-muted); display:block; margin-bottom:3px; }
    .wn-rxtpl-field-v { font-size:9px; font-weight:600; color:var(--wn-text); display:block; min-height:10px; }
    .wn-rxtpl-field-v-fill { animation:wn-rxtpl-fill 5.5s ease-in-out infinite; }
    .wn-rxtpl-actions { display:flex; gap:6px; justify-content:flex-end; margin-top:auto; }
    .wn-rxtpl-btn { font-size:8px; font-weight:700; padding:4px 8px; border-radius:6px; border:1px solid var(--wn-border); color:var(--wn-muted); background:var(--wn-bg-inset); }
    .wn-rxtpl-btn-primary { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border-color:transparent; box-shadow:0 4px 12px rgba(99,102,241,.4); animation:wn-rxtpl-save 5.5s ease-in-out infinite; }
    @keyframes wn-rxtpl-badge{0%,30%{opacity:0;transform:scale(.9);}38%,75%{opacity:1;transform:scale(1);}85%,100%{opacity:0;}}
    @keyframes wn-rxtpl-fill{0%,32%{opacity:0;}40%,78%{opacity:1;}88%,100%{opacity:0;}}
    @keyframes wn-rxtpl-save{0%,42%{box-shadow:0 4px 12px rgba(99,102,241,.4);}48%,58%{box-shadow:0 6px 18px rgba(99,102,241,.7);transform:scale(1.04);}65%,100%{box-shadow:0 4px 12px rgba(99,102,241,.4);transform:scale(1);}}

    /* ===== wn-drugs — Drug Reports ===== */
    .wn-drugs-scene { position:absolute; inset:10px 12px; display:flex; flex-direction:column; gap:7px; }
    .wn-drugs-head { display:flex; align-items:center; justify-content:space-between; }
    .wn-drugs-title { font-size:10px; font-weight:800; color:var(--wn-text); display:flex; align-items:center; gap:5px; }
    .wn-drugs-title i { color:var(--wn-accent-soft); }
    .wn-drugs-filter { font-size:8px; font-weight:700; padding:3px 7px; border-radius:999px; background:rgba(99,102,241,.15); color:var(--wn-accent-soft); border:1px solid rgba(99,102,241,.3); animation:wn-drugs-filter 6s ease-in-out infinite; }
    .wn-drugs-kpis { display:flex; gap:5px; }
    .wn-drugs-kpi { flex:1; background:var(--wn-bg-row); border:1px solid var(--wn-border); border-radius:7px; padding:5px 6px; text-align:left; }
    .wn-drugs-kpi-k { font-size:7px; font-weight:700; text-transform:uppercase; color:var(--wn-muted); display:block; }
    .wn-drugs-kpi-v { font-size:11px; font-weight:800; color:var(--wn-text); display:block; animation:wn-drugs-count 6s ease-in-out infinite; }
    .wn-drugs-chart { flex:1; background:var(--wn-bg-inset); border:1px solid var(--wn-border); border-radius:8px; padding:8px 10px 6px; display:flex; align-items:flex-end; gap:6px; }
    .wn-drugs-bar-wrap { flex:1; display:flex; flex-direction:column; align-items:center; gap:3px; height:100%; justify-content:flex-end; }
    .wn-drugs-bar { width:100%; border-radius:4px 4px 2px 2px; background:linear-gradient(180deg,#818cf8,#4f46e5); transform-origin:bottom; animation:wn-drugs-bar 6s ease-in-out infinite; }
    .wn-drugs-bar-1 { height:42%; animation-delay:0s; }
    .wn-drugs-bar-2 { height:68%; animation-delay:.1s; background:linear-gradient(180deg,#6ee7b7,#10b981); }
    .wn-drugs-bar-3 { height:55%; animation-delay:.2s; background:linear-gradient(180deg,#fcd34d,#f59e0b); }
    .wn-drugs-bar-4 { height:35%; animation-delay:.3s; }
    .wn-drugs-bar-lbl { font-size:6.5px; font-weight:700; color:var(--wn-muted); }
    @keyframes wn-drugs-filter{0%,15%{opacity:.6;}25%,70%{opacity:1;box-shadow:0 0 0 2px rgba(99,102,241,.25);}80%,100%{opacity:.6;}}
    @keyframes wn-drugs-count{0%,20%{opacity:.5;}30%,75%{opacity:1;}85%,100%{opacity:.5;}}
    @keyframes wn-drugs-bar{0%,18%{transform:scaleY(.3);opacity:.4;}28%,78%{transform:scaleY(1);opacity:1;}88%,100%{transform:scaleY(.3);opacity:.4;}}

    /* ===== wn-mi — Medical Instructions (templates + 2-page Rx) ===== */
    .wn-mi-scene { position:absolute; inset:10px 12px; display:flex; flex-direction:column; gap:6px; }
    .wn-mi-card { background:var(--wn-bg-card); border:1px solid var(--wn-border); border-radius:8px; padding:6px 7px; text-align:left; position:relative; }
    .wn-mi-card-head { font-size:8px; font-weight:800; color:var(--wn-text); display:flex; align-items:center; gap:4px; margin-bottom:4px; }
    .wn-mi-card-head i { color:var(--wn-accent-soft); font-size:9px; }
    .wn-mi-line { height:5px; border-radius:3px; background:var(--wn-bg-inset); border:1px solid var(--wn-border); width:72%; animation:wn-mi-line 6s ease-in-out infinite; }
    .wn-mi-print { position:absolute; right:6px; top:6px; font-size:8px; color:var(--wn-muted); padding:2px 5px; border-radius:5px; border:1px solid var(--wn-border); background:var(--wn-bg-inset); animation:wn-mi-print 6s ease-in-out infinite; }
    .wn-mi-actions { display:flex; gap:4px; margin-bottom:5px; }
    .wn-mi-actions span { font-size:6.5px; font-weight:700; padding:2px 5px; border-radius:999px; border:1px solid var(--wn-border); color:var(--wn-muted); background:var(--wn-bg-inset); }
    .wn-mi-actions span:nth-child(1) { animation:wn-mi-chip 6s ease-in-out infinite 0s; }
    .wn-mi-actions span:nth-child(2) { animation:wn-mi-chip 6s ease-in-out infinite .15s; }
    .wn-mi-actions span:nth-child(3) { animation:wn-mi-chip 6s ease-in-out infinite .3s; }
    .wn-mi-body-ar { font-size:7px; font-weight:600; color:#0f766e; line-height:1.35; padding:4px 5px; border-radius:5px; background:rgba(16,185,129,.08); border:1px dashed rgba(16,185,129,.35); animation:wn-mi-body 6s ease-in-out infinite; }
    .wn-mi-pages { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:auto; padding-top:2px; }
    .wn-mi-page { width:34px; height:42px; border-radius:4px; border:1px solid var(--wn-border); background:var(--wn-bg-inset); font-size:7px; font-weight:800; display:flex; align-items:center; justify-content:center; color:var(--wn-muted); }
    .wn-mi-page-rx { animation:wn-mi-pagerx 6s ease-in-out infinite; }
    .wn-mi-page-inst { border-color:rgba(16,185,129,.45); color:#10b981; animation:wn-mi-pageinst 6s ease-in-out infinite; }
    .wn-mi-page-break { width:14px; height:1px; background:var(--wn-border); position:relative; }
    .wn-mi-page-break::after { content:''; position:absolute; top:-3px; left:50%; transform:translateX(-50%); width:0; height:0; border-left:4px solid transparent; border-right:4px solid transparent; border-top:5px solid var(--wn-accent-soft); animation:wn-mi-break 6s ease-in-out infinite; }
    @keyframes wn-mi-line{0%,20%{width:40%;opacity:.5;}35%,75%{width:72%;opacity:1;}88%,100%{width:40%;opacity:.5;}}
    @keyframes wn-mi-print{0%,38%{box-shadow:none;color:var(--wn-muted);}45%,62%{box-shadow:0 0 0 2px rgba(99,102,241,.35);color:var(--wn-accent-soft);}70%,100%{box-shadow:none;color:var(--wn-muted);}}
    @keyframes wn-mi-chip{0%,25%{opacity:.55;}35%,70%{opacity:1;border-color:rgba(99,102,241,.4);color:var(--wn-accent-soft);}80%,100%{opacity:.55;}}
    @keyframes wn-mi-body{0%,30%{opacity:0;transform:translateY(4px);}42%,78%{opacity:1;transform:translateY(0);}90%,100%{opacity:0;transform:translateY(4px);}}
    @keyframes wn-mi-pagerx{0%,48%{opacity:1;}55%,100%{opacity:.55;}}
    @keyframes wn-mi-pageinst{0%,48%{opacity:.4;transform:scale(.92);}55%,78%{opacity:1;transform:scale(1.06);box-shadow:0 4px 14px rgba(16,185,129,.35);}88%,100%{opacity:.4;transform:scale(.92);}}
    @keyframes wn-mi-break{0%,50%{opacity:0;}58%,75%{opacity:1;}85%,100%{opacity:0;}}

    /* ===== wn-pmr — Patient Medical Record PDF ===== */
    .wn-pmr-scene { position:absolute; inset:8px 10px; display:flex; gap:8px; align-items:stretch; }
    .wn-pmr-doc { flex:1; background:var(--wn-bg-card); border:1px solid var(--wn-border); border-radius:6px; padding:6px 7px; display:flex; flex-direction:column; gap:4px; text-align:left; box-shadow:0 6px 18px rgba(99,102,241,.12); animation:wn-pmr-doc 7s ease-in-out infinite; }
    .wn-pmr-doc-head { display:flex; align-items:center; gap:5px; font-size:7px; font-weight:800; color:var(--wn-text); }
    .wn-pmr-doc-head i { color:#ef4444; font-size:9px; }
    .wn-pmr-line { height:4px; border-radius:3px; background:var(--wn-bg-inset); border:1px solid var(--wn-border); }
    .wn-pmr-line.w1 { width:88%; animation:wn-pmr-w1 7s ease-in-out infinite; }
    .wn-pmr-line.w2 { width:72%; animation:wn-pmr-w2 7s ease-in-out infinite; }
    .wn-pmr-line.w3 { width:60%; }
    .wn-pmr-chart { height:28px; border-radius:5px; border:1px dashed rgba(99,102,241,.35); background:linear-gradient(180deg,rgba(99,102,241,.08),transparent); position:relative; overflow:hidden; }
    .wn-pmr-chart::before { content:''; position:absolute; left:6px; right:6px; bottom:6px; height:2px; background:linear-gradient(90deg,#6366f1,#ec4899,#22d3ee); border-radius:2px; transform-origin:left; animation:wn-pmr-linechart 7s ease-in-out infinite; }
    .wn-pmr-blocks { display:flex; flex-direction:column; gap:3px; margin-top:2px; }
    .wn-pmr-block { font-size:6px; padding:3px 4px; border-radius:4px; border-left:2px solid var(--wn-accent-soft); background:var(--wn-bg-inset); color:var(--wn-muted); animation:wn-pmr-block 7s ease-in-out infinite; }
    .wn-pmr-block:nth-child(2) { animation-delay:.2s; border-color:#10b981; }
    .wn-pmr-block:nth-child(3) { animation-delay:.4s; border-color:#f59e0b; }
    .wn-pmr-badge { position:absolute; top:8px; right:10px; font-size:6px; font-weight:800; padding:2px 5px; border-radius:4px; background:rgba(239,68,68,.12); color:#ef4444; border:1px solid rgba(239,68,68,.35); animation:wn-pmr-pdf 7s ease-in-out infinite; }
    @keyframes wn-pmr-doc{0%,20%{transform:translateY(4px) scale(.96);opacity:.6;}35%,78%{transform:translateY(0) scale(1);opacity:1;}90%,100%{transform:translateY(4px) scale(.96);opacity:.6;}}
    @keyframes wn-pmr-w1{0%,30%{width:40%;}45%,75%{width:88%;}88%,100%{width:40%;}}
    @keyframes wn-pmr-w2{0%,35%{width:30%;}50%,70%{width:72%;}85%,100%{width:30%;}}
    @keyframes wn-pmr-linechart{0%,40%{transform:scaleX(.2);opacity:.4;}55%,75%{transform:scaleX(1);opacity:1;}88%,100%{transform:scaleX(.2);opacity:.4;}}
    @keyframes wn-pmr-block{0%,25%{opacity:.45;transform:translateX(-4px);}40%,72%{opacity:1;transform:translateX(0);}85%,100%{opacity:.45;transform:translateX(-4px);}}
    @keyframes wn-pmr-pdf{0%,45%{box-shadow:none;}52%,68%{box-shadow:0 0 0 2px rgba(239,68,68,.35);}75%,100%{box-shadow:none;}}

    /* ===== wn-fixes — Bug fixes & polish (static list) ===== */
    .wn-fixes-stage {
        height: auto;
        min-height: 0;
        overflow: visible;
        margin-top: .75rem;
    }
    .wn-slide-fixes h3 { margin-top: .6rem; font-size: 1.1rem; }
    .wn-slide-fixes p { font-size: .82rem; max-width: 100%; }
    .wn-fix-list {
        position: relative;
        inset: auto;
        background: var(--wn-bg-card);
        border-radius: 10px;
        border: 1px solid var(--wn-border);
        padding: 6px 8px;
        text-align: left;
        font-size: .58rem;
        color: var(--wn-text);
        list-style: none;
        margin: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 8px;
        align-content: start;
        overflow: visible;
    }
    .wn-fix-list li {
        display: flex;
        align-items: flex-start;
        gap: 4px;
        padding: 1px 0;
        line-height: 1.12;
        min-width: 0;
    }
    .wn-fix-list li i {
        color: #10b981;
        font-size: .62rem;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .wn-fix-list li span {
        min-width: 0;
        font-size: .54rem;
        white-space: normal;
        overflow: visible;
        text-overflow: unset;
        display: block;
    }
    @media (max-width: 575.98px) {
        .wn-fix-list {
            padding: 5px 6px;
            gap: 0 5px;
            grid-template-columns: 1fr 1fr;
        }
        .wn-fix-list li span { font-size: .48rem; line-height: 1.1; }
        .wn-fix-list li i { font-size: .56rem; }
        .wn-slide-fixes h3 { font-size: 1rem; }
        .wn-slide-fixes p { font-size: .76rem; }
    }
</style>


<div class="modal fade" id="whatsNewV9Modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-stars me-2"></i>What's New
          <span class="version-pill">v11.0.0</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="wn-viewport">
          <div class="wn-track" id="wnTrack">

            <!-- v11.0.0 — wn-v11 (Welcome to v11.0.0) -->
            <div class="wn-slide">
              <span class="wn-kicker">A major release</span>
              <div class="wn-stage wn-v11-stage">
                <div class="wn-v11-burst" aria-hidden="true"></div>
                <div class="wn-v11-rings" aria-hidden="true">
                  <span class="wn-v11-ring wn-v11-ring-a"></span>
                  <span class="wn-v11-ring wn-v11-ring-b"></span>
                  <span class="wn-v11-ring wn-v11-ring-c"></span>
                </div>
                <div class="wn-v11-sparkles" aria-hidden="true">
                  <i class="bi bi-stars wn-v11-spark wn-v11-spark-1" aria-hidden="true"></i>
                  <i class="bi bi-star-fill wn-v11-spark wn-v11-spark-2" aria-hidden="true"></i>
                  <i class="bi bi-stars wn-v11-spark wn-v11-spark-3" aria-hidden="true"></i>
                  <i class="bi bi-star-fill wn-v11-spark wn-v11-spark-4" aria-hidden="true"></i>
                  <i class="bi bi-asterisk wn-v11-spark wn-v11-spark-5" aria-hidden="true"></i>
                  <i class="bi bi-stars wn-v11-spark wn-v11-spark-6" aria-hidden="true"></i>
                </div>
                <div class="wn-v11-label">
                  <i class="bi bi-rocket-takeoff-fill" aria-hidden="true"></i>
                  <span>VERSION</span>
                </div>
                <div class="wn-v11-number" aria-hidden="true">
                  <span class="wn-v11-digit">11</span>
                  <span class="wn-v11-dot">.</span>
                  <span class="wn-v11-digit">0</span>
                  <span class="wn-v11-dot">.</span>
                  <span class="wn-v11-digit">0</span>
                </div>
                <div class="wn-v11-tag">
                  <span class="wn-v11-pip"></span>
                  <span>Major release</span>
                </div>
              </div>
              <h3>Welcome to v11.0.0</h3>
              <p>Our biggest release yet brings a <strong>redesigned notification center</strong>, drug reports, medical instructions, per-doctor Rx templates, notes drawer, theme palettes, Cmd+K, and more.</p>
            </div>

            <!-- v11.0.0 #2 — wn-prefetch (Speculation Rules — safe navigation prefetch) -->
            <div class="wn-slide wn-slide-prefetch">
              <span class="wn-kicker">Performance</span>
              <div class="wn-stage wn-prefetch-stage">
                <div class="wn-prefetch-scene">
                  <span class="wn-prefetch-orb" aria-hidden="true"></span>
                  <div class="wn-prefetch-nav" aria-hidden="true">
                    <div class="wn-prefetch-link wn-prefetch-active">
                      <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                      <span class="wn-prefetch-badge">prefetch</span>
                    </div>
                    <div class="wn-prefetch-link"><i class="bi bi-people"></i><span>Patients</span></div>
                    <div class="wn-prefetch-link"><i class="bi bi-calendar3"></i><span>Calendar</span></div>
                    <div class="wn-prefetch-link"><i class="bi bi-kanban"></i><span>Board</span></div>
                  </div>
                </div>
              </div>
              <ul class="wn-prefetch-tips" aria-label="How to enable prefetch in your browser">
                <li><i class="bi bi-laptop" aria-hidden="true"></i><span><strong>Desktop:</strong> <code>chrome://settings/performance</code> → <em>Preload pages</em> ON → restart browser. Optional: <code>chrome://flags/#prerender2</code></span></li>
                <li><i class="bi bi-phone" aria-hidden="true"></i><span><strong>Android:</strong> Chrome ⋮ → Settings → Privacy → <em>Preload pages</em> ON. Turn off battery/data saver.</span></li>
                <li><i class="bi bi-apple" aria-hidden="true"></i><span><strong>iPhone/iPad:</strong> Chrome 109+; Low Power Mode OFF. Safari support is limited.</span></li>
                <li><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Tip:</strong> uBlock and similar extensions block prefetch — test in a private window first.</span></li>
              </ul>
              <h3>Faster navigation — safely</h3>
              <p>Browser-level <strong>prefetch</strong> warms allowlisted pages when you hover sidebar links — never APIs, exports, prints, or edit forms.</p>
            </div>

            <!-- v11.0.0 #3 — change-list overview (static) -->
            <div class="wn-slide">
              <span class="wn-kicker">What's New</span>
              <div class="wn-stage">
                <ul class="wn-cl-list">
                  <li><i class="bi bi-bell-fill"></i><span>iOS notification center</span></li>
                  <li><i class="bi bi-check2-square"></i><span>Multi-list To-Do</span></li>
                  <li><i class="bi bi-palette-fill"></i><span>Theme palettes (6)</span></li>
                  <li><i class="bi bi-journal-text"></i><span>Quick Notes + drawer</span></li>
                  <li><i class="bi bi-capsule"></i><span>Drug Rx templates</span></li>
                  <li><i class="bi bi-journal-medical"></i><span>Medical Instructions</span></li>
                  <li><i class="bi bi-bar-chart-fill"></i><span>Drug Reports</span></li>
                  <li><i class="bi bi-command"></i><span>Cmd+K command palette</span></li>
                  <li><i class="bi bi-keyboard-fill"></i><span>Keyboard shortcuts (?)</span></li>
                  <li><i class="bi bi-person-vcard"></i><span>Patient hover-cards</span></li>
                  <li><i class="bi bi-pin-angle-fill"></i><span>Snooze &amp; Pin</span></li>
                  <li><i class="bi bi-at"></i><span>@mentions + Activity</span></li>
                  <li><i class="bi bi-wrench-adjustable-circle"></i><span>30+ fixes &amp; polish</span></li>
                </ul>
              </div>
              <h3>v11.0.0 — change-list</h3>
              <p>The biggest update yet. Every fix and feature is documented for the ortho fork so it ships with the same polish.</p>
            </div>


            <!-- v11.0.0 — wn-notif (A redesigned notification center) -->
            <div class="wn-slide">
              <span class="wn-kicker">Notifications</span>
              <div class="wn-stage">
                <div class="wn-notif-bg" aria-hidden="true">
                  <span class="wn-notif-orb wn-notif-orb-1"></span>
                  <span class="wn-notif-orb wn-notif-orb-2"></span>
                </div>
                <div class="wn-notif-bell" aria-hidden="true">
                  <i class="bi bi-bell-fill"></i>
                  <span class="wn-notif-badge">3</span>
                </div>
                <div class="wn-notif-panel" aria-hidden="true">
                  <div class="wn-notif-head">
                    <span class="wn-notif-title"><i class="bi bi-bell-fill"></i> Notifications</span>
                    <span class="wn-notif-count">3 new</span>
                  </div>
                  <div class="wn-notif-bucket">Today</div>
                  <div class="wn-notif-row wn-notif-row-hover">
                    <span class="wn-notif-dot wn-notif-dot-indigo"><i class="bi bi-calendar-check"></i></span>
                    <div class="wn-notif-body">
                      <span class="wn-notif-line wn-notif-line-1"></span>
                      <span class="wn-notif-line wn-notif-line-2"></span>
                    </div>
                    <span class="wn-notif-time">2m</span>
                    <div class="wn-notif-actions">
                      <span class="wn-notif-chip"><i class="bi bi-clock"></i></span>
                      <span class="wn-notif-chip"><i class="bi bi-pin-angle-fill"></i></span>
                      <span class="wn-notif-chip"><i class="bi bi-check2"></i></span>
                      <span class="wn-notif-chip wn-notif-chip-red"><i class="bi bi-trash"></i></span>
                    </div>
                  </div>
                  <div class="wn-notif-row">
                    <span class="wn-notif-dot wn-notif-dot-amber"><i class="bi bi-exclamation"></i></span>
                    <div class="wn-notif-body">
                      <span class="wn-notif-line wn-notif-line-1"></span>
                      <span class="wn-notif-line wn-notif-line-2 wn-notif-line-short"></span>
                    </div>
                    <span class="wn-notif-time">11m</span>
                  </div>
                  <div class="wn-notif-bucket">Yesterday</div>
                  <div class="wn-notif-row">
                    <span class="wn-notif-dot wn-notif-dot-green"><i class="bi bi-chat-dots-fill"></i></span>
                    <div class="wn-notif-body">
                      <span class="wn-notif-line wn-notif-line-1"></span>
                      <span class="wn-notif-line wn-notif-line-2"></span>
                    </div>
                    <span class="wn-notif-time">1d</span>
                  </div>
                  <div class="wn-notif-dock">
                    <span class="wn-notif-dbtn"><i class="bi bi-check2-all"></i></span>
                    <span class="wn-notif-dbtn"><i class="bi bi-funnel"></i></span>
                    <span class="wn-notif-dbtn"><i class="bi bi-bookmark"></i></span>
                    <span class="wn-notif-dbtn"><i class="bi bi-bell-slash"></i></span>
                    <span class="wn-notif-dbtn"><i class="bi bi-gear"></i></span>
                    <span class="wn-notif-dbtn"><i class="bi bi-archive"></i></span>
                  </div>
                </div>
              </div>
              <h3>A redesigned notification center</h3>
              <p>A <strong>glass notification panel</strong> slides in with smart Today/Yesterday grouping, hover-reveal snooze and pin actions, and a footer dock for quick triage.</p>
            </div>

            <!-- v11.0.0 — wn-todo (Smart, multi-list to-dos) -->
            <div class="wn-slide">
              <span class="wn-kicker">To-Do</span>
              <div class="wn-stage">
                <div class="wn-todo-scene">
                  <div class="wn-todo-rail" aria-hidden="true">
                    <span class="wn-todo-chip wn-todo-chip-indigo"><i class="bi bi-person-fill"></i>Personal</span>
                    <span class="wn-todo-chip wn-todo-chip-active"><i class="bi bi-heart-pulse-fill"></i>Follow-ups</span>
                    <span class="wn-todo-chip wn-todo-chip-amber"><i class="bi bi-box-seam-fill"></i>Inventory</span>
                    <span class="wn-todo-chip wn-todo-chip-cyan"><i class="bi bi-mortarboard-fill"></i>Study</span>
                  </div>

                  <div class="wn-todo-card">
                    <div class="wn-todo-card-head">
                      <div class="wn-todo-card-title">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        <span>Keep it up!</span>
                      </div>
                      <div class="wn-todo-badge"><span class="wn-todo-badge-num">3</span>/4</div>
                    </div>
                    <div class="wn-todo-bar"><div class="wn-todo-bar-fill"></div></div>
                  </div>

                  <div class="wn-todo-tasks">
                    <div class="wn-todo-row wn-todo-row-done">
                      <span class="wn-todo-check wn-todo-check-static" aria-hidden="true"><i class="bi bi-check2"></i></span>
                      <span class="wn-todo-title wn-todo-title-struck">Call Mrs. Hassan re: lab results</span>
                    </div>
                    <div class="wn-todo-row wn-todo-row-anim">
                      <span class="wn-todo-check wn-todo-check-anim" aria-hidden="true"><i class="bi bi-check2"></i></span>
                      <span class="wn-todo-title wn-todo-title-anim">Schedule Ahmed's follow-up visit</span>
                    </div>
                    <div class="wn-todo-row">
                      <span class="wn-todo-check" aria-hidden="true"></span>
                      <span class="wn-todo-title">Send post-op care PDF to Lina</span>
                    </div>
                  </div>
                </div>
              </div>
              <h3>Smart, multi-list to-dos</h3>
              <p>Organize your day with <strong>multiple named lists</strong> — Personal, Patient follow-ups, Inventory and more. Each list tracks its own gamified progress, so finishing tasks actually feels rewarding.</p>
            </div>

            <!-- v11.0.0 — wn-pal (A palette for every mood) -->
            <div class="wn-slide">
              <span class="wn-kicker">Themes</span>
              <div class="wn-stage">
                <div class="wn-pal-scene">
                  <div class="wn-pal-grid" aria-hidden="true">
                    <div class="wn-pal-sw wn-pal-sw-1"><span class="wn-pal-dot"></span><span class="wn-pal-lbl">Indigo</span></div>
                    <div class="wn-pal-sw wn-pal-sw-2"><span class="wn-pal-dot"></span><span class="wn-pal-lbl">Emerald</span></div>
                    <div class="wn-pal-sw wn-pal-sw-3"><span class="wn-pal-dot"></span><span class="wn-pal-lbl">Rose</span></div>
                    <div class="wn-pal-sw wn-pal-sw-4"><span class="wn-pal-dot"></span><span class="wn-pal-lbl">Slate</span></div>
                    <div class="wn-pal-sw wn-pal-sw-5"><span class="wn-pal-dot"></span><span class="wn-pal-lbl">Amber</span></div>
                    <div class="wn-pal-sw wn-pal-sw-6"><span class="wn-pal-dot"></span><span class="wn-pal-lbl">Ocean</span></div>
                  </div>
                  <div class="wn-pal-preview" aria-hidden="true">
                    <div class="wn-pal-prev-head">
                      <span class="wn-pal-prev-avatar"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
                      <span class="wn-pal-prev-lines">
                        <span class="wn-pal-prev-l1"></span>
                        <span class="wn-pal-prev-l2"></span>
                      </span>
                    </div>
                    <div class="wn-pal-prev-bar"><span class="wn-pal-prev-bar-fill"></span></div>
                    <div class="wn-pal-prev-row">
                      <span class="wn-pal-prev-chip"><i class="bi bi-check2" aria-hidden="true"></i> Saved</span>
                      <span class="wn-pal-prev-btn">Apply</span>
                    </div>
                  </div>
                </div>
              </div>
              <h3>A palette for every mood</h3>
              <p>Pick from six accent <strong>palettes</strong> in dark or light mode. Your choice is saved to your profile and follows you everywhere.</p>
            </div>

            <!-- v11.0.0 — wn-sched (Dark by night, light by day) -->
            <div class="wn-slide">
              <span class="wn-kicker">Auto theme</span>
              <div class="wn-stage wn-sched-stage">
                <div class="wn-sched-sky" aria-hidden="true">
                  <div class="wn-sched-stars"></div>
                  <div class="wn-sched-sun"></div>
                  <div class="wn-sched-moon"></div>
                  <div class="wn-sched-horizon"></div>
                </div>

                <div class="wn-sched-clock" aria-hidden="true">
                  <div class="wn-sched-dial">
                    <span class="wn-sched-tick wn-sched-t12"></span>
                    <span class="wn-sched-tick wn-sched-t3"></span>
                    <span class="wn-sched-tick wn-sched-t6"></span>
                    <span class="wn-sched-tick wn-sched-t9"></span>
                    <span class="wn-sched-hand"></span>
                    <span class="wn-sched-pivot"></span>
                  </div>
                </div>

                <div class="wn-sched-chip wn-sched-chip-day" aria-hidden="true">
                  <i class="bi bi-sun-fill" aria-hidden="true"></i>
                  <span>Light from 07:00</span>
                </div>
                <div class="wn-sched-chip wn-sched-chip-night" aria-hidden="true">
                  <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
                  <span>Dark from 19:00</span>
                </div>

                <div class="wn-sched-toggle" aria-hidden="true">
                  <span class="wn-sched-toggle-label">Auto</span>
                  <span class="wn-sched-toggle-track"><span class="wn-sched-toggle-knob"></span></span>
                  <span class="wn-sched-toggle-state">ON</span>
                </div>
              </div>
              <h3>Dark by night, light by day</h3>
              <p>Pick a sunrise and sunset, and the app follows the sky. Turn on <strong>Auto theme</strong> to glide from light to dark without lifting a finger.</p>
            </div>

            <!-- v11.0.0 — wn-cmdk (Cmd+K, anywhere) -->
            <div class="wn-slide">
              <span class="wn-kicker">Command palette</span>
              <div class="wn-stage">
                <div class="wn-cmdk-scene">
                  <div class="wn-cmdk-kbd" aria-hidden="true">
                    <span class="wn-cmdk-key">⌘</span>
                    <span class="wn-cmdk-plus">+</span>
                    <span class="wn-cmdk-key">K</span>
                  </div>
                  <div class="wn-cmdk-modal" aria-hidden="true">
                    <div class="wn-cmdk-input">
                      <i class="bi bi-search wn-cmdk-search" aria-hidden="true"></i>
                      <span class="wn-cmdk-typed"></span>
                      <span class="wn-cmdk-caret"></span>
                      <span class="wn-cmdk-enter">
                        <i class="bi bi-arrow-return-left" aria-hidden="true"></i>
                        Enter
                      </span>
                    </div>
                    <ul class="wn-cmdk-list">
                      <li class="wn-cmdk-row wn-cmdk-r1">
                        <i class="bi bi-person-circle wn-cmdk-rico" aria-hidden="true"></i>
                        <span class="wn-cmdk-rtxt"><b>Ahm</b>ed Maher</span>
                        <span class="wn-cmdk-rtag wn-cmdk-tag-pt">Patient</span>
                      </li>
                      <li class="wn-cmdk-row wn-cmdk-r2">
                        <i class="bi bi-check2-square wn-cmdk-rico" aria-hidden="true"></i>
                        <span class="wn-cmdk-rtxt">Add to-do</span>
                        <span class="wn-cmdk-rtag wn-cmdk-tag-ac">Action</span>
                      </li>
                      <li class="wn-cmdk-row wn-cmdk-r3">
                        <i class="bi bi-calendar2-week wn-cmdk-rico" aria-hidden="true"></i>
                        <span class="wn-cmdk-rtxt">Appointments</span>
                        <span class="wn-cmdk-rtag wn-cmdk-tag-pg">Page</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <h3>Cmd+K, anywhere</h3>
              <p>Hit <strong>Cmd+K</strong> from any screen to fuzzy-search patients, pages, actions and to-dos. Type a few letters, then Enter — you're there.</p>
            </div>

            <!-- v11.0.0 — wn-kbd (Every shortcut at your fingertips) -->
            <div class="wn-slide">
              <span class="wn-kicker">Shortcuts</span>
              <div class="wn-stage">
                <div class="wn-kbd-scene">
                  <div class="wn-kbd-grid" aria-hidden="true"></div>

                  <div class="wn-kbd-press">
                    <span class="wn-kbd-presslabel">Press</span>
                    <span class="wn-kbd-chip wn-kbd-chip-lg">?</span>
                    <span class="wn-kbd-ripple"></span>
                  </div>

                  <div class="wn-kbd-modal" aria-hidden="true">
                    <div class="wn-kbd-modalhead">
                      <span class="wn-kbd-dot"></span>
                      <span class="wn-kbd-dot"></span>
                      <span class="wn-kbd-dot"></span>
                      <span class="wn-kbd-title"><i class="bi bi-keyboard" aria-hidden="true"></i> Keyboard shortcuts</span>
                    </div>
                    <ul class="wn-kbd-list">
                      <li class="wn-kbd-row" style="--wn-kbd-i:0">
                        <span class="wn-kbd-keys"><span class="wn-kbd-chip">&#8984;</span><span class="wn-kbd-chip">K</span></span>
                        <span class="wn-kbd-label">Quick search</span>
                      </li>
                      <li class="wn-kbd-row" style="--wn-kbd-i:1">
                        <span class="wn-kbd-keys"><span class="wn-kbd-chip">?</span></span>
                        <span class="wn-kbd-label">Show shortcuts</span>
                      </li>
                      <li class="wn-kbd-row" style="--wn-kbd-i:2">
                        <span class="wn-kbd-keys"><span class="wn-kbd-chip">T</span></span>
                        <span class="wn-kbd-label">Today's bookings</span>
                      </li>
                      <li class="wn-kbd-row" style="--wn-kbd-i:3">
                        <span class="wn-kbd-keys"><span class="wn-kbd-chip">N</span></span>
                        <span class="wn-kbd-label">New patient</span>
                      </li>
                      <li class="wn-kbd-row" style="--wn-kbd-i:4">
                        <span class="wn-kbd-keys"><span class="wn-kbd-chip">G</span><span class="wn-kbd-plus">then</span><span class="wn-kbd-chip">D</span></span>
                        <span class="wn-kbd-label">Go to Dashboard</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <h3>Every shortcut at your fingertips</h3>
              <p>Press <strong>?</strong> anywhere in the app to open the full keyboard shortcut sheet. Jump between bookings, patients and the dashboard without lifting your hands.</p>
            </div>

            <!-- v11.0.0 — wn-pc (A patient summary, just by hovering) -->
            <div class="wn-slide">
              <span class="wn-kicker">Patient cards</span>
              <div class="wn-stage">
                <div class="wn-pc-scene">
                  <div class="wn-pc-row">
                    <span class="wn-pc-label">Patient:</span>
                    <a class="wn-pc-link" href="#">Ahmed Maher</a>
                    <i class="bi bi-cursor-fill wn-pc-cursor" aria-hidden="true"></i>
                  </div>

                  <div class="wn-pc-card" role="presentation">
                    <div class="wn-pc-card-head">
                      <div class="wn-pc-avatar" aria-hidden="true">AM</div>
                      <div class="wn-pc-id">
                        <div class="wn-pc-name">Ahmed Maher</div>
                        <div class="wn-pc-meta">Male &middot; 34 y</div>
                      </div>
                      <span class="wn-pc-chip">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        2 alerts
                      </span>
                    </div>

                    <div class="wn-pc-stats">
                      <div class="wn-pc-stat">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                        <span class="wn-pc-stat-k">Last visit</span>
                        <span class="wn-pc-stat-v">12 May</span>
                      </div>
                      <div class="wn-pc-stat">
                        <i class="bi bi-calendar-event" aria-hidden="true"></i>
                        <span class="wn-pc-stat-k">Next appt</span>
                        <span class="wn-pc-stat-v">Tue 09:30</span>
                      </div>
                      <div class="wn-pc-stat">
                        <i class="bi bi-capsule" aria-hidden="true"></i>
                        <span class="wn-pc-stat-k">Active Rx</span>
                        <span class="wn-pc-stat-v">3 meds</span>
                      </div>
                    </div>
                  </div>

                  <div class="wn-pc-glow" aria-hidden="true"></div>
                </div>
              </div>
              <h3>A patient summary, just by hovering</h3>
              <p>Hover any patient name to peek at a <strong>mini profile card</strong> — avatar, age, last visit, next appointment, and active alerts — without leaving the page.</p>
            </div>

            <!-- v11.0.0 — wn-snz (Snooze, pin, get back to it) -->
            <div class="wn-slide">
              <span class="wn-kicker">Snooze &amp; Pin</span>
              <div class="wn-stage">
                <div class="wn-snz-scene">
                  <div class="wn-snz-list">
                    <div class="wn-snz-row wn-snz-row-a">
                      <div class="wn-snz-icon wn-snz-icon-blue" aria-hidden="true"><i class="bi bi-bell-fill"></i></div>
                      <div class="wn-snz-body">
                        <div class="wn-snz-title">Lab results ready</div>
                        <div class="wn-snz-meta">Ms. Karim &middot; 2m</div>
                      </div>
                      <button class="wn-snz-btn" type="button" aria-hidden="true">
                        <i class="bi bi-moon-stars-fill"></i>
                      </button>
                      <div class="wn-snz-menu" aria-hidden="true">
                        <div class="wn-snz-mhead">Snooze until</div>
                        <div class="wn-snz-mitem"><i class="bi bi-clock"></i><span>1 hour</span></div>
                        <div class="wn-snz-mitem"><i class="bi bi-hourglass-split"></i><span>4 hours</span></div>
                        <div class="wn-snz-mitem"><i class="bi bi-sunrise"></i><span>Tomorrow 9am</span></div>
                        <div class="wn-snz-mitem"><i class="bi bi-calendar-week"></i><span>Next week</span></div>
                        <div class="wn-snz-mitem"><i class="bi bi-sliders"></i><span>Custom&hellip;</span></div>
                      </div>
                    </div>
                    <div class="wn-snz-row wn-snz-row-b">
                      <div class="wn-snz-icon wn-snz-icon-pink" aria-hidden="true"><i class="bi bi-chat-heart-fill"></i></div>
                      <div class="wn-snz-body">
                        <div class="wn-snz-title">New message from Dr. Salem</div>
                        <div class="wn-snz-meta">Inbox &middot; 6m</div>
                      </div>
                      <div class="wn-snz-pin" aria-hidden="true"><i class="bi bi-pin-angle-fill"></i></div>
                    </div>
                  </div>
                </div>
              </div>
              <h3>Snooze, pin, get back to it</h3>
              <p>Tap the moon to <strong>snooze</strong> a notification for an hour, until tomorrow, or a custom time. Pin the ones you can&rsquo;t forget &mdash; they float to the top and stay put.</p>
            </div>

            <!-- v11.0.0 — wn-mention (@mention, and stay in the loop) -->
            <div class="wn-slide">
              <span class="wn-kicker">Mentions &amp; Activity</span>
              <div class="wn-stage">
                <div class="wn-mention-grid">
                  <div class="wn-mention-note">
                    <div class="wn-mention-note-head">
                      <span class="wn-mention-dot wn-mention-dot-r" aria-hidden="true"></span>
                      <span class="wn-mention-dot wn-mention-dot-y" aria-hidden="true"></span>
                      <span class="wn-mention-dot wn-mention-dot-g" aria-hidden="true"></span>
                      <span class="wn-mention-note-label">Consultation note</span>
                    </div>
                    <div class="wn-mention-note-body">
                      <span class="wn-mention-text">Discussed plan with </span>
                      <span class="wn-mention-typed" aria-hidden="true">
                        <span class="wn-mention-raw">@dr_osama</span>
                        <span class="wn-mention-chip">
                          <i class="bi bi-at" aria-hidden="true"></i>dr_osama
                        </span>
                      </span>
                      <span class="wn-mention-caret" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class="wn-mention-bottom">
                    <div class="wn-mention-bell" aria-hidden="true">
                      <i class="bi bi-bell-fill"></i>
                      <span class="wn-mention-badge">1</span>
                      <span class="wn-mention-ring"></span>
                    </div>
                    <div class="wn-mention-activity">
                      <div class="wn-mention-tab">
                        <i class="bi bi-activity" aria-hidden="true"></i>
                        <span>Activity</span>
                      </div>
                      <div class="wn-mention-row">
                        <span class="wn-mention-avatar" aria-hidden="true">DO</span>
                        <div class="wn-mention-row-text">
                          <strong>dr_osama</strong> was mentioned in a note
                          <span class="wn-mention-time">2 min ago</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <h3>@mention, and stay in the loop</h3>
              <p>Type <strong>@username</strong> in any note and that teammate gets a ping. Every mention also lands in the new Activity tab so nothing slips by.</p>
            </div>

            <!-- v11.0.0 — wn-focus (Distraction-free consultations) -->
            <div class="wn-slide">
              <span class="wn-kicker">Focus mode</span>
              <div class="wn-stage wn-focus-stage">
                <div class="wn-focus-app">
                  <aside class="wn-focus-sidebar" aria-hidden="true">
                    <div class="wn-focus-logo"></div>
                    <div class="wn-focus-navitem"></div>
                    <div class="wn-focus-navitem"></div>
                    <div class="wn-focus-navitem wn-focus-navitem-active"></div>
                    <div class="wn-focus-navitem"></div>
                    <div class="wn-focus-navitem"></div>
                  </aside>
                  <header class="wn-focus-header" aria-hidden="true">
                    <div class="wn-focus-crumb"></div>
                    <div class="wn-focus-crumb wn-focus-crumb-short"></div>
                    <div class="wn-focus-spacer"></div>
                    <div class="wn-focus-avatar"></div>
                  </header>
                  <section class="wn-focus-main">
                    <div class="wn-focus-textarea">
                      <span class="wn-focus-line"></span>
                      <span class="wn-focus-line"></span>
                      <span class="wn-focus-line wn-focus-line-short"></span>
                      <span class="wn-focus-line"></span>
                      <span class="wn-focus-line wn-focus-line-med"></span>
                      <span class="wn-focus-caret"></span>
                    </div>
                  </section>
                  <div class="wn-focus-pill" aria-hidden="true">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                    <span>Focus ON</span>
                  </div>
                  <div class="wn-focus-tap" aria-hidden="true">
                    <span class="wn-focus-ring"></span>
                    <i class="bi bi-fullscreen" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
              <h3>Distraction-free consultations</h3>
              <p>One tap collapses the sidebar, header, and dock so only your <strong>consultation textarea</strong> remains. Perfect for long notes when the patient is sitting across from you.</p>
            </div>

            <!-- v11.0.0 — wn-tpl (Your phrase library, one click away) -->
            <div class="wn-slide">
              <span class="wn-kicker">Templates</span>
              <div class="wn-stage">
                <div class="wn-tpl-scene">
                  <div class="wn-tpl-toolbar">
                    <span class="wn-tpl-label"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Consultation note</span>
                    <button class="wn-tpl-btn" aria-hidden="true">
                      <i class="bi bi-bookmark-plus"></i>
                      <span>Insert template</span>
                      <i class="bi bi-caret-down-fill wn-tpl-caret"></i>
                    </button>
                  </div>

                  <div class="wn-tpl-textarea">
                    <span class="wn-tpl-caret-blink" aria-hidden="true"></span>
                    <div class="wn-tpl-typed">
                      <span class="wn-tpl-line wn-tpl-l1">Patient appears well, alert and oriented.</span>
                      <span class="wn-tpl-line wn-tpl-l2">HEENT: unremarkable. Pupils equal, reactive.</span>
                      <span class="wn-tpl-line wn-tpl-l3">Heart: regular rate, no murmurs detected.</span>
                    </div>
                  </div>

                  <div class="wn-tpl-menu" aria-hidden="true">
                    <div class="wn-tpl-group">General</div>
                    <div class="wn-tpl-row wn-tpl-r1">
                      <i class="bi bi-clipboard2-pulse"></i>
                      <span>Normal exam</span>
                      <kbd>1</kbd>
                    </div>
                    <div class="wn-tpl-row wn-tpl-r2">
                      <i class="bi bi-heart-pulse"></i>
                      <span>Follow-up visit</span>
                      <kbd>2</kbd>
                    </div>
                    <div class="wn-tpl-group">Cardiology</div>
                    <div class="wn-tpl-row wn-tpl-r3">
                      <i class="bi bi-activity"></i>
                      <span>Chest pain workup</span>
                      <kbd>3</kbd>
                    </div>
                    <div class="wn-tpl-row wn-tpl-r4">
                      <i class="bi bi-droplet-half"></i>
                      <span>BP management</span>
                      <kbd>4</kbd>
                    </div>
                    <div class="wn-tpl-highlight" aria-hidden="true"></div>
                  </div>
                </div>
              </div>
              <h3>Your phrase library, one click away</h3>
              <p>Save common notes as <strong>templates</strong> and drop a full block into Edit Consultation with a single click — grouped by category and keyboard-friendly.</p>
            </div>

            <!-- v11.0.0 — wn-qnote (Quick Notes scratchpad) -->
            <div class="wn-slide">
              <span class="wn-kicker">Quick Notes</span>
              <div class="wn-stage">
                <div class="wn-qnote-scene">
                  <div class="wn-qnote-modal" aria-hidden="true">
                    <div class="wn-qnote-head">
                      <span class="wn-qnote-title"><i class="bi bi-journal-plus"></i> Quick note</span>
                      <span class="wn-qnote-pin"><i class="bi bi-pin-angle-fill"></i></span>
                    </div>
                    <div class="wn-qnote-body">
                      <span class="wn-qnote-line wn-qnote-line-1"></span>
                      <span class="wn-qnote-line wn-qnote-line-2"></span>
                      <span class="wn-qnote-caret"></span>
                    </div>
                  </div>
                </div>
              </div>
              <h3>Jot it down in seconds</h3>
              <p>A lightweight <strong>Quick Note</strong> modal for one-off reminders — pin important notes and reach them from the notification footer in one tap.</p>
            </div>

            <!-- v11.0.0 — wn-ndraw (Notes drawer) -->
            <div class="wn-slide">
              <span class="wn-kicker">Notes drawer</span>
              <div class="wn-stage">
                <div class="wn-ndraw-scene">
                  <div class="wn-ndraw-app" aria-hidden="true"></div>
                  <div class="wn-ndraw-panel" aria-hidden="true">
                    <div class="wn-ndraw-head">
                      <span><i class="bi bi-journal-text"></i> Notes</span>
                      <div class="wn-ndraw-filters">
                        <span class="wn-ndraw-chip wn-ndraw-chip-on">All</span>
                        <span class="wn-ndraw-chip">Pinned</span>
                        <span class="wn-ndraw-chip">Recent</span>
                      </div>
                    </div>
                    <div class="wn-ndraw-card wn-ndraw-card-pin">
                      <div class="wn-ndraw-card-title">Post-op follow-up call</div>
                      <div class="wn-ndraw-card-line"></div>
                    </div>
                    <div class="wn-ndraw-card">
                      <div class="wn-ndraw-card-title">Lab re-check in 2 weeks</div>
                      <div class="wn-ndraw-card-line"></div>
                    </div>
                  </div>
                  <span class="wn-ndraw-fab" aria-hidden="true"><i class="bi bi-plus-lg"></i></span>
                </div>
              </div>
              <h3>All your notes, one drawer away</h3>
              <p>The <strong>Notes drawer</strong> keeps every quick note in a persistent glass panel — filter by All, Pinned or Recent, search, and add new notes without leaving your workflow.</p>
            </div>

            <!-- v11.0.0 — wn-rxtpl (Per-doctor drug prescription templates) -->
            <div class="wn-slide">
              <span class="wn-kicker">Drug templates</span>
              <div class="wn-stage">
                <div class="wn-rxtpl-scene">
                  <div class="wn-rxtpl-head">
                    <div class="wn-rxtpl-drug">LACRITEARS</div>
                    <span class="wn-rxtpl-badge"><i class="bi bi-magic"></i> Auto-fill</span>
                  </div>
                  <div class="wn-rxtpl-fields">
                    <div class="wn-rxtpl-field">
                      <span class="wn-rxtpl-field-k">Dose</span>
                      <span class="wn-rxtpl-field-v wn-rxtpl-field-v-fill">1 drop</span>
                    </div>
                    <div class="wn-rxtpl-field">
                      <span class="wn-rxtpl-field-k">Frequency</span>
                      <span class="wn-rxtpl-field-v wn-rxtpl-field-v-fill">TDS</span>
                    </div>
                    <div class="wn-rxtpl-field">
                      <span class="wn-rxtpl-field-k">Duration</span>
                      <span class="wn-rxtpl-field-v wn-rxtpl-field-v-fill">2 weeks</span>
                    </div>
                  </div>
                  <div class="wn-rxtpl-actions">
                    <span class="wn-rxtpl-btn">Clear</span>
                    <span class="wn-rxtpl-btn wn-rxtpl-btn-primary"><i class="bi bi-bookmark-plus"></i> Save as template</span>
                  </div>
                </div>
              </div>
              <h3>Your drug defaults, saved per doctor</h3>
              <p>Save dose, frequency, duration and route as a <strong>per-doctor template</strong> — the next time you pick that drug, every field auto-fills instantly. Edit or clear before saving; templates work from Appointments and the Drugs page.</p>
            </div>

            <!-- v11.0.0 — wn-drugs (Drug Reports) -->
            <div class="wn-slide">
              <span class="wn-kicker">Drug Reports</span>
              <div class="wn-stage">
                <div class="wn-drugs-scene">
                  <div class="wn-drugs-head">
                    <span class="wn-drugs-title"><i class="bi bi-capsule"></i> Drug Reports</span>
                    <span class="wn-drugs-filter"><i class="bi bi-funnel"></i> RAMEDA</span>
                  </div>
                  <div class="wn-drugs-kpis">
                    <div class="wn-drugs-kpi">
                      <span class="wn-drugs-kpi-k">Writes</span>
                      <span class="wn-drugs-kpi-v">312</span>
                    </div>
                    <div class="wn-drugs-kpi">
                      <span class="wn-drugs-kpi-k">New</span>
                      <span class="wn-drugs-kpi-v">198</span>
                    </div>
                    <div class="wn-drugs-kpi">
                      <span class="wn-drugs-kpi-k">Cont.</span>
                      <span class="wn-drugs-kpi-v">114</span>
                    </div>
                  </div>
                  <div class="wn-drugs-chart" aria-hidden="true">
                    <div class="wn-drugs-bar-wrap">
                      <div class="wn-drugs-bar wn-drugs-bar-1"></div>
                      <span class="wn-drugs-bar-lbl">RAM</span>
                    </div>
                    <div class="wn-drugs-bar-wrap">
                      <div class="wn-drugs-bar wn-drugs-bar-2"></div>
                      <span class="wn-drugs-bar-lbl">PHR</span>
                    </div>
                    <div class="wn-drugs-bar-wrap">
                      <div class="wn-drugs-bar wn-drugs-bar-3"></div>
                      <span class="wn-drugs-bar-lbl">SIG</span>
                    </div>
                    <div class="wn-drugs-bar-wrap">
                      <div class="wn-drugs-bar wn-drugs-bar-4"></div>
                      <span class="wn-drugs-bar-lbl">NOV</span>
                    </div>
                  </div>
                </div>
              </div>
              <h3>Know what you prescribe — and who supplies it</h3>
              <p>A new <strong>Drug Reports</strong> type shows prescription writes, new starts vs continuations, demand by company, monthly trends, regimen breakdown, and estimated units — with filters and PDF export.</p>
            </div>

            <!-- v11.0.0 — wn-mi (Medical Instructions — clinic templates + 2-page Rx) -->
            <div class="wn-slide">
              <span class="wn-kicker">Patient education</span>
              <div class="wn-stage">
                <div class="wn-mi-scene">
                  <div class="wn-mi-card wn-mi-card-rx">
                    <div class="wn-mi-card-head"><i class="bi bi-capsule"></i> Medications</div>
                    <div class="wn-mi-line"></div>
                    <span class="wn-mi-print"><i class="bi bi-printer"></i></span>
                  </div>
                  <div class="wn-mi-card wn-mi-card-mi">
                    <div class="wn-mi-card-head"><i class="bi bi-journal-medical"></i> Medical Instructions</div>
                    <div class="wn-mi-actions">
                      <span>Suggested</span><span>Templates</span><span>Custom</span>
                    </div>
                    <div class="wn-mi-body-ar" dir="rtl">خشونة المفاصل — تعليمات عامة</div>
                  </div>
                  <div class="wn-mi-pages" aria-hidden="true">
                    <span class="wn-mi-page wn-mi-page-rx">Rx</span>
                    <span class="wn-mi-page-break"></span>
                    <span class="wn-mi-page wn-mi-page-inst">Tips</span>
                  </div>
                </div>
              </div>
              <h3>Medical instructions — on their own page</h3>
              <p><strong>Clinic-wide templates</strong> match diagnosis keywords. Copy suggestions, pick from the library, or add custom text — <strong>Save as template</strong> pulls diagnosis + ICD from the visit and confirms in a modal. Print from Medications: Rx only, or Rx + instructions on a separate A4 page.</p>
            </div>

            <!-- v11.0.0 — wn-pmr (Patient Medical Record PDF) -->
            <div class="wn-slide">
              <span class="wn-kicker">Patient records</span>
              <div class="wn-stage">
                <span class="wn-pmr-badge">PDF</span>
                <div class="wn-pmr-scene">
                  <div class="wn-pmr-doc">
                    <div class="wn-pmr-doc-head"><i class="bi bi-file-earmark-pdf-fill"></i> Complete Medical Record</div>
                    <div class="wn-pmr-line w1"></div>
                    <div class="wn-pmr-line w2"></div>
                    <div class="wn-pmr-chart" aria-hidden="true"></div>
                    <div class="wn-pmr-blocks">
                      <div class="wn-pmr-block">2026-05-12 · Glaucoma · IOP 18/19</div>
                      <div class="wn-pmr-block">2026-04-03 · Cataract review · VA 6/9</div>
                      <div class="wn-pmr-block">Meds · Labs · Images appendix</div>
                    </div>
                  </div>
                </div>
              </div>
              <h3>One PDF — the whole patient story</h3>
              <p>Export a <strong>dated, comprehensive medical dossier</strong>: aggregated history, every visit with diagnosis &amp; plan, prescriptions, labs, instructions, notes, attachments, and <strong>IOP / visit charts</strong> — replacing the old Word export.</p>
            </div>

            <!-- v11.0.0 — wn-fixes (Bug fixes & polish) -->
            <div class="wn-slide wn-slide-fixes">
              <span class="wn-kicker">Fixes</span>
              <div class="wn-stage wn-fixes-stage">
                <ul class="wn-fix-list">
                  <li><i class="bi bi-check-circle-fill"></i><span>Mobile header: two-row cluster + dashboard subtitle removed</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Header chips unified — theme-aware glass on every control</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Patients page search/bell aligned — no .btn style leak</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Search↔notes &amp; bell↔to-do column grid on mobile</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Palette + icon-only ⌘K on mobile row 2</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Global search: command palette below header (mobile + desktop)</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Search overlay: frosted blur — not a solid white sheet</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Search panel: uniform glass corners + z-index above backdrop</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Push notification prompt centered at top</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Reports type select wired + warning gone</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Drug Reports PDF exports real data</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>To-Do delete modal above drawer</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>List popover no longer clipped</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>New / rename list uses modals</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Archived lists restore &amp; delete</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Notification badge counts correctly</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Patient hover-card: last visit from API root + date object</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Patient hover on every name — dashboard, calendar, appointment, board</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Hover card next appointment matches Upcoming list (today Booked)</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Hover card: smaller type + wrapped visit rows</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Hover card dates in 12-hour format (e.g. 2:00 PM)</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>FAB stack visible with drawers</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Notification row text no longer truncated</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Quick Note + To-Do API signatures fixed</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Wizard slides render correctly</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Medical Instructions: clinic-wide templates + edit/delete</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Rx print: instructions forced to page 2 (page-break)</span></li>
                  <li><i class="bi bi-check-circle-fill"></i><span>Instruction modals: dark/light theme-aware (mi-theme-modal)</span></li>
                </ul>
              </div>
              <h3>30+ fixes under the hood</h3>
              <p>v11 isn't just features — we chased down <strong>silent failures, z-index traps, and API mismatches</strong> so the polish matches the headline upgrades.</p>
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
    const VERSION       = 'v11_0_0';
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

        // Keep animated mockup stages in sync with the app's light/dark theme.
        function syncWizardTheme() {
            const dark = document.documentElement.classList.contains('dark');
            el.setAttribute('data-wn-theme', dark ? 'dark' : 'light');
        }
        syncWizardTheme();
        try {
            new MutationObserver(syncWizardTheme).observe(document.documentElement, {
                attributes: true, attributeFilter: ['class']
            });
        } catch (e) {}

        // Reset to slide 1 whenever the modal opens (from any trigger).
        el.addEventListener('show.bs.modal', () => { idx = 0; syncWizardTheme(); render(); });

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
