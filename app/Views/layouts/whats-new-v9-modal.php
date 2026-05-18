<?php
// "What's New" v9.1.0 — step-by-step wizard. Same display policy as v9.0
// (per-login session, 2-day window from first sight, opt-out persistent),
// but bumping VERSION below to v9_1_0 deliberately RESETS the timer +
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
        background:linear-gradient(135deg,#818cf8 0%,#a78bfa 45%,#38bdf8 100%);
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
    .wn-ai-chip i { color:#38bdf8; }
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
        color:#38bdf8; font-weight:700;
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
        fill:rgba(14,165,233,.14); stroke:#0ea5e9; stroke-width:2.5;
        stroke-dasharray:270; stroke-dashoffset:270; opacity:0;
        animation: wnEyeIris 6s ease-in-out infinite;
    }
    .wn-eye-rays {
        opacity:0; stroke:#0ea5e9; stroke-width:1;
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
          <span class="version-pill">v9.1.0</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="wn-viewport">
          <div class="wn-track" id="wnTrack">

            <!-- 1 — Welcome 9.1.0 -->
            <div class="wn-slide">
              <span class="wn-kicker">Release Highlights</span>
              <div class="wn-stage">
                <div class="wn-ver">
                  <span class="wn-ver-label">version</span>
                  <span class="wn-ver-num">9.1.0</span>
                </div>
              </div>
              <h3>Smarter Edit Consultation</h3>
              <p>A focused update — AI right inside the consultation page,
                 instant ICD-10 suggestions, a sharper drawing studio, and
                 the three biggest annoyances quietly squashed.</p>
            </div>

            <!-- 2 — AI Assistant on Edit Consultation -->
            <div class="wn-slide">
              <span class="wn-kicker">AI Assistant</span>
              <div class="wn-stage">
                <div class="wn-ai">
                  <div class="wn-ai-head">
                    <span class="wn-ai-icon"><i class="bi bi-stars"></i></span>
                    <span class="wn-ai-title">AI Assistant</span>
                    <span class="wn-ai-badge">
                      <i class="bi bi-shield-check"></i> review before saving
                    </span>
                  </div>
                  <div class="wn-ai-chips">
                    <span class="wn-ai-chip">
                      <i class="bi bi-clock-history"></i>Summarize prior visits
                    </span>
                    <span class="wn-ai-chip">
                      <i class="bi bi-question-circle"></i>What might I be missing?
                    </span>
                  </div>
                </div>
              </div>
              <h3>AI right inside the consultation</h3>
              <p>Ask the assistant about the patient's history or what your
                 current draft is missing — every reply carries an amber
                 "review before saving" badge so it stays a suggestion,
                 never the chart.</p>
            </div>

            <!-- 3 — Prior-Visit Summary -->
            <div class="wn-slide">
              <span class="wn-kicker">Prior Visits</span>
              <div class="wn-stage">
                <div class="wn-sum">
                  <button type="button" class="wn-sum-btn">
                    <i class="bi bi-clipboard2-pulse"></i>Summarize prior visits
                  </button>
                  <ul class="wn-sum-list">
                    <li>• Diagnosis: Astigmatism (2026-05-14)</li>
                    <li>• Plan: Conjyclear forte ED + glasses</li>
                    <li>• Slit lamp: OD/OS mild hyperaemia</li>
                    <li>• IOP / VA / refraction: <em>not recorded</em></li>
                  </ul>
                </div>
              </div>
              <h3>One-click prior-visit recap</h3>
              <p>The assistant reads ONLY the recorded data and bullets it
                 out, explicitly saying "not recorded" when something is
                 missing. Read-only — it never edits the chart.</p>
            </div>

            <!-- 4 — ICD-10 Suggestions -->
            <div class="wn-slide">
              <span class="wn-kicker">ICD-10</span>
              <div class="wn-stage">
                <div class="wn-icd">
                  <div class="wn-icd-row1">
                    <div class="wn-icd-input">Senile cataract, right eye</div>
                    <button type="button" class="wn-icd-btn">
                      <i class="bi bi-stars"></i>Suggest
                    </button>
                  </div>
                  <div class="wn-icd-pop">
                    <div class="wn-icd-pop-label">AI suggestions</div>
                    <div class="wn-icd-r">
                      <b>H25.13</b>
                      <span>Unilateral age-related cataract, right</span>
                      <i>AI 90%</i>
                    </div>
                    <div class="wn-icd-r">
                      <b>H25.10</b>
                      <span>Age-related cataract, unspecified</span>
                      <i>AI 70%</i>
                    </div>
                  </div>
                </div>
              </div>
              <h3>Instant ICD-10 suggestions</h3>
              <p>Type the diagnosis, hit <strong>Suggest</strong>. Codes the
                 clinic has actually used rank first; AI codes are
                 server-validated by regex so a malformed code never reaches
                 you. One click sets the field — never the rest of the form.</p>
            </div>

            <!-- 5 — Drawing studio (improved, eye SVG) -->
            <div class="wn-slide">
              <span class="wn-kicker">Drawing Studio</span>
              <div class="wn-stage">
                <div class="wn-eye-wrap">
                  <svg class="wn-eye" viewBox="0 0 320 170" xmlns="http://www.w3.org/2000/svg">
                    <!-- Almond / eye outline -->
                    <path class="wn-eye-outline"
                          d="M 30 85 Q 160 8 290 85 Q 160 162 30 85 Z"/>
                    <!-- Iris -->
                    <circle class="wn-eye-iris" cx="160" cy="85" r="42"/>
                    <!-- Iris radial pattern -->
                    <g class="wn-eye-rays">
                      <line x1="160" y1="48"  x2="160" y2="58"/>
                      <line x1="160" y1="112" x2="160" y2="122"/>
                      <line x1="123" y1="85"  x2="133" y2="85"/>
                      <line x1="187" y1="85"  x2="197" y2="85"/>
                      <line x1="134" y1="59"  x2="141" y2="66"/>
                      <line x1="179" y1="104" x2="186" y2="111"/>
                      <line x1="134" y1="111" x2="141" y2="104"/>
                      <line x1="179" y1="66"  x2="186" y2="59"/>
                    </g>
                    <!-- Pupil -->
                    <circle class="wn-eye-pupil" cx="160" cy="85" r="16"/>
                    <!-- Catch-light -->
                    <circle class="wn-eye-glint" cx="152" cy="77" r="5"/>
                    <!-- Annotation: OD -->
                    <g class="wn-eye-label">
                      <line x1="248" y1="36" x2="208" y2="68"/>
                      <text x="252" y="34">OD</text>
                    </g>
                  </svg>
                </div>
              </div>
              <h3>Drawing studio, now ophthalmology-aware</h3>
              <p>Sketch findings right on the appointment with pen, shapes,
                 eye templates and medical stamps. Toolbar refined, arrowheads
                 fixed, per-element settings panel — and the contextual quick
                 menu now follows text and templates too.</p>
            </div>

            <!-- 6 — Bug fixes -->
            <div class="wn-slide">
              <span class="wn-kicker">Bug Fixes</span>
              <div class="wn-stage">
                <ul class="wn-bugs">
                  <li>
                    <span class="wn-bug-icon">
                      <i class="bi bi-bug-fill"></i>
                      <i class="bi bi-check-circle-fill"></i>
                    </span>
                    <span class="wn-bug-label">Diagnosis &amp; complaint autocomplete (404 → live)</span>
                  </li>
                  <li>
                    <span class="wn-bug-icon">
                      <i class="bi bi-bug-fill"></i>
                      <i class="bi bi-check-circle-fill"></i>
                    </span>
                    <span class="wn-bug-label">Common Cases modal now loads</span>
                  </li>
                  <li>
                    <span class="wn-bug-icon">
                      <i class="bi bi-bug-fill"></i>
                      <i class="bi bi-check-circle-fill"></i>
                    </span>
                    <span class="wn-bug-label">Medication suggestions (+ complaint match)</span>
                  </li>
                </ul>
              </div>
              <h3>Three quiet bugs, gone</h3>
              <p>The autocomplete and "Common Cases" routes were dead in
                 production; medication suggestions ignored the chief
                 complaint. All three now work — and medications match
                 against complaint too, not just diagnosis.</p>
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
    const VERSION       = 'v9_1_0';
    const OPT_OUT_KEY   = 'whatsNew_' + VERSION + '_optOut';     // permanent
    const FIRST_SEEN_KEY= 'whatsNew_' + VERSION + '_firstSeen';  // ms epoch
    const SESSION_KEY   = 'whatsNew_' + VERSION + '_shownSession';
    const WINDOW_MS     = 2 * 24 * 60 * 60 * 1000;               // 2 days

    function shouldShow() {
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
