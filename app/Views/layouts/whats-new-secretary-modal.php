<?php
// مودال «ما الجديد» — واجهة السكرتارية فقط (عربي · RTL).
// يُضمَّن من secretary_main.php — لا يُستخدم على واجهة الطبيب.
?>
<style>
    #secWhatsNewModal {
        --swn-bg-deep: #0f172a;
        --swn-bg-card: #1e293b;
        --swn-bg-surface: rgba(30,41,59,.85);
        --swn-bg-row: rgba(30,41,59,.7);
        --swn-text: #e2e8f0;
        --swn-muted: #94a3b8;
        --swn-border: rgba(148,163,184,.25);
        --swn-accent: #6366f1;
        --swn-accent-soft: #a5b4fc;
    }
    html:not(.dark) #secWhatsNewModal {
        --swn-bg-deep: #e8eef5;
        --swn-bg-card: #fff;
        --swn-bg-surface: rgba(255,255,255,.94);
        --swn-bg-row: rgba(255,255,255,.92);
        --swn-text: #1e293b;
        --swn-muted: #64748b;
        --swn-border: rgba(148,163,184,.42);
        --swn-accent-soft: #4f46e5;
    }

    #secWhatsNewModal .modal-dialog { max-width: 620px; }
    #secWhatsNewModal .modal-content {
        border: none; border-radius: 18px; overflow: hidden;
        background: var(--card); font-family: 'Cairo', sans-serif;
    }
    .dark #secWhatsNewModal .modal-content { color: #e2e8f0; }

    #secWhatsNewModal .modal-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 55%, #4f46e5 100%);
        color: #fff; border-bottom: none; padding: 1rem 1.35rem;
        flex-direction: row-reverse;
    }
    #secWhatsNewModal .modal-title {
        font-weight: 800; font-size: 1.05rem; display: flex; align-items: center; gap: .5rem;
    }
    #secWhatsNewModal .swn-version-pill {
        background: rgba(255,255,255,.2); padding: 3px 10px; border-radius: 999px;
        font-size: .7rem; font-weight: 700;
    }
    #secWhatsNewModal .modal-body { padding: 0; }

    .swn-viewport { position: relative; overflow: hidden; }
    .swn-track {
        display: flex; direction: ltr;
        transition: transform .38s cubic-bezier(.4,0,.2,1);
    }
    .swn-slide {
        min-width: 100%; padding: 1.45rem 1.6rem 1.1rem;
        box-sizing: border-box; text-align: center;
        direction: rtl;
    }
    .swn-kicker {
        display: inline-block; font-size: .68rem; font-weight: 700;
        color: var(--swn-accent-soft); background: rgba(99,102,241,.12);
        padding: 3px 11px; border-radius: 999px;
    }
    .swn-slide h3 {
        font-size: 1.15rem; font-weight: 800; margin: .75rem 0 .35rem;
        color: var(--swn-accent-soft);
    }
    .swn-slide p {
        font-size: .88rem; color: var(--swn-muted); margin: 0 auto;
        max-width: 440px; line-height: 1.6;
    }
    .swn-bullets {
        list-style: none; margin: .7rem auto 0; padding: .55rem .65rem;
        max-width: 460px; text-align: right;
        background: var(--swn-bg-card); border: 1px solid var(--swn-border);
        border-radius: 10px; font-size: .78rem; line-height: 1.5; color: var(--swn-text);
    }
    .swn-bullets li {
        display: flex; align-items: flex-start; gap: .45rem; padding: .2rem 0;
    }
    .swn-bullets li i { color: var(--swn-accent); flex-shrink: 0; margin-top: .15rem; font-size: .82rem; }

    .swn-stage {
        height: 168px; margin: .85rem auto 0; max-width: 460px;
        border-radius: 14px; position: relative; overflow: hidden;
        background: var(--swn-bg-deep); border: 1px solid var(--swn-border);
        box-shadow: inset 0 0 32px rgba(0,0,0,.12);
    }

    /* slide 1 — hero + stats */
    .swn-welcome-stage { padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; height: auto; min-height: 168px; }
    .swn-welcome-hero {
        height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, rgba(99,102,241,.55), rgba(139,92,246,.35));
        position: relative; overflow: hidden;
    }
    .swn-welcome-hero::after {
        content: ''; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        width: 45%; height: 6px; border-radius: 999px; background: rgba(255,255,255,.75);
        animation: swn-shimmer 3s ease-in-out infinite;
    }
    .swn-welcome-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; flex: 1; }
    .swn-welcome-stat {
        border-radius: 8px; background: var(--swn-bg-surface);
        border: 1px solid var(--swn-border); position: relative;
    }
    .swn-welcome-stat::before {
        content: ''; position: absolute; top: 8px; right: 8px; left: 8px; height: 4px;
        border-radius: 999px; background: rgba(148,163,184,.35);
    }
    .swn-welcome-stat::after {
        content: ''; position: absolute; bottom: 6px; right: 6px; left: 6px; height: 3px;
        border-radius: 999px; background: linear-gradient(90deg, var(--swn-accent), #22c55e);
        animation: swn-spark 3.2s ease-in-out infinite;
    }
    .swn-welcome-stat:nth-child(2)::after { animation-delay: .2s; }
    .swn-welcome-stat:nth-child(3)::after { animation-delay: .4s; }
    .swn-welcome-stat:nth-child(4)::after { animation-delay: .6s; }

    /* slide 2 — stats detail */
    .swn-stats-stage { padding: 14px; display: flex; flex-direction: column; gap: 6px; height: auto; min-height: 168px; }
    .swn-stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; flex: 1; }
    .swn-stat-card {
        border-radius: 8px; background: var(--swn-bg-surface); border: 1px solid var(--swn-border);
        padding: 8px; position: relative;
    }
    .swn-stat-card .swn-val {
        position: absolute; top: 8px; right: 8px; width: 28%; height: 8px;
        border-radius: 4px; background: var(--swn-accent); opacity: .7;
    }
    .swn-stat-card .swn-line {
        position: absolute; bottom: 22px; right: 8px; left: 8px; height: 3px;
        border-radius: 999px; background: linear-gradient(90deg, var(--swn-accent), #22c55e);
        animation: swn-spark 2.8s ease-in-out infinite;
    }
    .swn-stat-card .swn-trend {
        position: absolute; bottom: 6px; left: 8px; font-size: .5rem; font-weight: 800;
        color: #22c55e; background: rgba(34,197,94,.15); padding: 1px 5px; border-radius: 4px;
        animation: swn-trend-pop 3s ease-in-out infinite;
    }

    /* slide 3 — dashboard widgets */
    .swn-dash-stage { padding: 12px 14px; display: flex; gap: 10px; align-items: center; height: auto; min-height: 168px; }
    .swn-donut {
        width: 58px; height: 58px; border-radius: 50%; flex-shrink: 0;
        border: 7px solid var(--swn-accent); border-left-color: #22c55e;
        border-top-color: rgba(99,102,241,.3);
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 800; color: var(--swn-text);
        animation: swn-donut-tilt 4s ease-in-out infinite;
    }
    .swn-appts { flex: 1; display: flex; flex-direction: column; gap: 5px; }
    .swn-appt {
        height: 20px; border-radius: 6px; background: var(--swn-bg-row);
        border: 1px solid var(--swn-border); position: relative;
    }
    .swn-appt::before {
        content: ''; position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        width: 35%; height: 4px; border-radius: 999px; background: rgba(99,102,241,.5);
    }
    .swn-appt.is-hover::after {
        content: ''; position: absolute; left: 6px; top: -16px;
        width: 56px; height: 22px; border-radius: 6px;
        background: var(--swn-bg-card); border: 1px solid rgba(99,102,241,.4);
        box-shadow: 0 4px 14px rgba(15,23,42,.18);
        animation: swn-hover-card 4s ease-in-out infinite;
    }
    .swn-pager { display: flex; justify-content: center; gap: 4px; margin-top: 4px; }
    .swn-pager span { width: 16px; height: 4px; border-radius: 999px; background: rgba(148,163,184,.35); }
    .swn-pager span.is-on { background: var(--swn-accent); animation: swn-page 3s ease-in-out infinite; }

    /* slide 4 — calendar */
    .swn-cal-stage { padding: 12px; display: flex; flex-direction: column; gap: 8px; height: auto; min-height: 168px; }
    .swn-cal-card {
        flex: 1; border-radius: 10px; background: var(--swn-bg-surface);
        border: 1px solid var(--swn-border); position: relative; overflow: hidden;
    }
    .swn-cal-card::before {
        content: ''; position: absolute; top: 0; right: 0; left: 0; height: 14px;
        background: rgba(99,102,241,.15);
    }
    .swn-cal-grid {
        position: absolute; inset: 20px 10px 10px;
        display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px;
    }
    .swn-cal-cell { border-radius: 3px; background: var(--swn-bg-row); }
    .swn-cal-cell.is-active {
        background: var(--swn-accent); box-shadow: 0 0 0 2px rgba(99,102,241,.35);
        animation: swn-cal-pulse 3.5s ease-in-out infinite;
    }
    .swn-cal-tip {
        height: 28px; border-radius: 8px; background: var(--swn-bg-card);
        border: 1px solid rgba(99,102,241,.4); position: relative;
        animation: swn-tip-show 3.5s ease-in-out infinite;
    }
    .swn-cal-tip::before {
        content: ''; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        width: 22%; height: 4px; border-radius: 999px; background: var(--swn-muted);
    }
    .swn-cal-tip::after {
        content: ''; position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
        width: 38%; height: 4px; border-radius: 999px; background: var(--swn-accent);
    }

    /* slide 5 — modals */
    .swn-modal-stage { padding: 14px; position: relative; height: auto; min-height: 168px; }
    .swn-modal-bg {
        position: absolute; inset: 10px; border-radius: 12px;
        background: rgba(15,23,42,.25); backdrop-filter: blur(2px);
        animation: swn-backdrop 4s ease-in-out infinite;
    }
    .swn-modal-box {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 72%; height: 68%; border-radius: 12px;
        background: var(--swn-bg-card); border: 1px solid var(--swn-border);
        box-shadow: 0 12px 32px rgba(15,23,42,.2);
        padding: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 6px;
        z-index: 1;
    }
    .swn-modal-field { border-radius: 5px; background: var(--swn-bg-row); border: 1px solid var(--swn-border); }
    .swn-modal-field.is-wide { grid-column: 1 / -1; }
    .swn-modal-field.is-notes { grid-column: 2; grid-row: 2 / 4; animation: swn-notes-glow 4s ease-in-out infinite; }

    /* slide 6 — summary */
    .swn-done-stage {
        padding: 12px; display: flex; align-items: center; justify-content: center;
        height: auto; min-height: 168px;
    }
    .swn-done-badge {
        width: 88px; height: 88px; border-radius: 50%;
        background: linear-gradient(135deg, var(--swn-accent), #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 2rem;
        box-shadow: 0 8px 28px rgba(99,102,241,.45);
        animation: swn-done-pop 2.5s ease-in-out infinite;
    }

    @keyframes swn-shimmer { 0%,100%{opacity:.6;} 50%{opacity:1;} }
    @keyframes swn-spark { 0%,100%{opacity:.4;} 50%{opacity:1;} }
    @keyframes swn-trend-pop { 0%,30%,100%{transform:scale(1);} 40%,55%{transform:scale(1.08);} }
    @keyframes swn-donut-tilt { 0%,100%{transform:rotate(0);} 50%{transform:rotate(6deg);} }
    @keyframes swn-hover-card { 0%,52%,100%{opacity:0;transform:translateY(4px);} 58%,85%{opacity:1;transform:translateY(0);} }
    @keyframes swn-page { 0%,40%,100%{opacity:.35;} 50%,70%{opacity:1;} }
    @keyframes swn-cal-pulse { 0%,100%{opacity:.85;} 50%{opacity:1;box-shadow:0 0 0 3px rgba(99,102,241,.35);} }
    @keyframes swn-tip-show { 0%,25%,100%{opacity:0;transform:translateY(4px);} 32%,70%{opacity:1;transform:translateY(0);} }
    @keyframes swn-backdrop { 0%,100%{opacity:.5;} 50%{opacity:.75;} }
    @keyframes swn-notes-glow { 0%,100%{border-color:var(--swn-border);} 50%{border-color:rgba(99,102,241,.55);} }
    @keyframes swn-done-pop { 0%,100%{transform:scale(1);} 50%{transform:scale(1.05);} }

    .swn-dots { display: flex; justify-content: center; gap: 7px; padding: .35rem 0 0; direction: ltr; }
    .swn-dots button {
        width: 8px; height: 8px; border-radius: 50%; border: 0; padding: 0;
        background: #cbd5e1; cursor: pointer; transition: all .2s;
    }
    .dark .swn-dots button { background: #334155; }
    .swn-dots button.active { background: var(--swn-accent); width: 22px; border-radius: 5px; }

    #secWhatsNewModal .modal-footer {
        border-top: 1px solid rgba(148,163,184,.18);
        padding: .85rem 1.35rem; gap: .5rem;
        flex-direction: row-reverse; justify-content: space-between;
    }
    .swn-foot-nav { display: flex; gap: .5rem; flex-direction: row-reverse; }
    .swn-foot-end { display: none; gap: .5rem; flex-direction: row-reverse; }
    .swn-foot-end.show { display: flex; }

    @media (max-width: 575.98px) {
        #secWhatsNewModal .modal-body {
            max-height: calc(100dvh - 9.5rem);
            overflow-y: auto; -webkit-overflow-scrolling: touch;
        }
        .swn-slide { padding: 1.15rem 1rem .9rem; }
        .swn-stage { height: 150px; min-height: 150px; }
        .swn-bullets { font-size: .72rem; }
        .swn-welcome-stats { grid-template-columns: repeat(2, 1fr); }
    }
    /* v11 feature mockups */
    .swn-feat-stage {
        display: flex; align-items: center; justify-content: center;
        padding: 14px; height: auto; min-height: 168px; gap: 8px;
    }
    .swn-digit-row { display: flex; align-items: center; gap: 10px; width: 100%; justify-content: center; }
    .swn-digit-chip {
        padding: 8px 10px; border-radius: 8px; background: var(--swn-bg-card);
        border: 1px solid var(--swn-border); font-weight: 800; font-size: .78rem;
        color: var(--swn-text); font-family: 'Cairo', monospace;
    }
    .swn-digit-eq { color: var(--swn-accent); font-weight: 900; }
    .swn-todo-mock { width: 100%; max-width: 220px; display: flex; flex-direction: column; gap: 5px; }
    .swn-todo-row {
        height: 22px; border-radius: 6px; background: var(--swn-bg-row);
        border: 1px solid var(--swn-border); position: relative;
    }
    .swn-todo-row.is-done::before {
        content: ''; position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        width: 10px; height: 10px; border-radius: 3px; background: #22c55e;
    }
    .swn-notes-mock {
        width: 88px; height: 120px; border-radius: 10px 0 0 10px;
        background: var(--swn-bg-card); border: 1px solid var(--swn-border);
        box-shadow: -6px 0 18px rgba(15,23,42,.15); padding: 8px;
        display: flex; flex-direction: column; gap: 5px; margin-right: auto;
    }
    .swn-notes-line { height: 5px; border-radius: 999px; background: rgba(148,163,184,.35); }
    .swn-pal-mock { display: flex; gap: 6px; justify-content: center; }
    .swn-pal-swatch {
        width: 28px; height: 28px; border-radius: 50%; border: 2px solid rgba(255,255,255,.5);
        animation: swn-pal-glow 3s ease-in-out infinite;
    }
    .swn-pal-swatch:nth-child(1) { background: #6366f1; }
    .swn-pal-swatch:nth-child(2) { background: #8b5cf6; animation-delay: .2s; }
    .swn-pal-swatch:nth-child(3) { background: #22c55e; animation-delay: .4s; }
    .swn-notif-mock {
        width: 100%; max-width: 200px; border-radius: 12px; background: var(--swn-bg-card);
        border: 1px solid var(--swn-border); padding: 8px; display: flex; flex-direction: column; gap: 5px;
    }
    .swn-notif-row { height: 18px; border-radius: 5px; background: var(--swn-bg-row); }
    .swn-notif-row.is-new { border-right: 3px solid var(--swn-accent); }
    .swn-cmdk-mock {
        width: 100%; max-width: 210px; border-radius: 10px; background: var(--swn-bg-card);
        border: 1px solid var(--swn-border); padding: 8px;
    }
    .swn-cmdk-input {
        height: 20px; border-radius: 6px; background: var(--swn-bg-row); margin-bottom: 6px;
    }
    .swn-profile-mock {
        width: 100%; max-width: 200px; border-radius: 12px; overflow: hidden;
        border: 1px solid var(--swn-border);
    }
    .swn-profile-hero { height: 36px; background: linear-gradient(135deg,#6366f1,#8b5cf6); }
    .swn-profile-body { padding: 8px; background: var(--swn-bg-card); display: flex; flex-direction: column; gap: 4px; }
    .swn-profile-line { height: 5px; border-radius: 999px; background: rgba(148,163,184,.3); }
    @keyframes swn-pal-glow { 0%,100%{transform:scale(1);} 50%{transform:scale(1.1);} }

    @media (prefers-reduced-motion: reduce) {
        .swn-track { transition: none; }
        .swn-welcome-hero::after, .swn-welcome-stat::after, .swn-stat-card .swn-line,
        .swn-stat-card .swn-trend, .swn-donut, .swn-appt.is-hover::after,
        .swn-pager span.is-on, .swn-cal-cell.is-active, .swn-cal-tip,
        .swn-modal-bg, .swn-modal-field.is-notes, .swn-done-badge,
        .swn-pal-swatch { animation: none; }
    }
</style>

<div class="modal fade" id="secWhatsNewModal" tabindex="-1" aria-hidden="true" dir="rtl" lang="ar">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title arabic-text">
                    <i class="bi bi-stars"></i>
                    ما الجديد
                    <span class="swn-version-pill">v11.0.3</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <div class="modal-body">
                <div class="swn-viewport">
                    <div class="swn-track" id="swnTrack">

                        <div class="swn-slide">
                            <span class="swn-kicker">تحديث يونيو ٢٠٢٦</span>
                            <div class="swn-stage swn-welcome-stage" aria-hidden="true">
                                <div class="swn-welcome-hero"></div>
                                <div class="swn-welcome-stats">
                                    <div class="swn-welcome-stat"></div>
                                    <div class="swn-welcome-stat"></div>
                                    <div class="swn-welcome-stat"></div>
                                    <div class="swn-welcome-stat"></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">مرحباً بتحديث واجهة السكرتارية</h3>
                            <p class="arabic-text">لوحة عربية كاملة — إحصائيات حية، تقويم أوضح، ومودالات مُحسَّنة لتسريع عملك اليومي.</p>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">الإحصائيات</span>
                            <div class="swn-stage swn-stats-stage" aria-hidden="true">
                                <div class="swn-stats-row">
                                    <div class="swn-stat-card"><span class="swn-val"></span><span class="swn-line"></span><span class="swn-trend">+٢</span></div>
                                    <div class="swn-stat-card"><span class="swn-val"></span><span class="swn-line"></span><span class="swn-trend">+١</span></div>
                                </div>
                                <div class="swn-stats-row">
                                    <div class="swn-stat-card"><span class="swn-val"></span><span class="swn-line"></span><span class="swn-trend">−١</span></div>
                                    <div class="swn-stat-card"><span class="swn-val"></span><span class="swn-line"></span><span class="swn-trend">+٣</span></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">كروت إحصائية ذكية</h3>
                            <p class="arabic-text">مؤشرات حية في اللوحة والحجوزات والمرضى والمدفوعات.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-graph-up-arrow"></i><span>خطوط sparkline لكل بطاقة</span></li>
                                <li><i class="bi bi-123"></i><span>شارات الاتجاه (+/−) من السيرفر — دقيقة وليست −1 ثابتة</span></li>
                                <li><i class="bi bi-palette"></i><span>بطاقة الترحيب: عنوان أبيض وثيم متوافق مع اللوحة</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">لوحة التحكم</span>
                            <div class="swn-stage swn-dash-stage" aria-hidden="true">
                                <div class="swn-donut">٥<br><small style="font-size:.42rem;">مواعيد</small></div>
                                <div class="swn-appts">
                                    <div class="swn-appt is-hover"></div>
                                    <div class="swn-appt"></div>
                                    <div class="swn-appt"></div>
                                    <div class="swn-pager"><span class="is-on"></span><span></span><span></span></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">مواعيد اليوم وملخص المريض</h3>
                            <p class="arabic-text">ودجات أوضح وتفاعل أسرع مع بيانات المرضى.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-calendar3"></i><span>٥ مواعيد لكل صفحة مع ترقيم AJAX</span></li>
                                <li><i class="bi bi-pie-chart"></i><span>دونات «حالة اليوم» — حلقة أكبر ونص عربي سليم</span></li>
                                <li><i class="bi bi-person-vcard"></i><span>ملخص المريض عند التمرير على الاسم في كل الصفحات</span></li>
                                <li><i class="bi bi-bug"></i><span>إصلاح خطأ «تعذّر تحميل ملخص المريض» على اللوحة</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">التقويم والحجوزات</span>
                            <div class="swn-stage swn-cal-stage" aria-hidden="true">
                                <div class="swn-cal-card">
                                    <div class="swn-cal-grid">
                                        <span class="swn-cal-cell"></span><span class="swn-cal-cell"></span>
                                        <span class="swn-cal-cell is-active"></span><span class="swn-cal-cell"></span>
                                        <span class="swn-cal-cell"></span><span class="swn-cal-cell"></span>
                                        <span class="swn-cal-cell"></span>
                                    </div>
                                </div>
                                <div class="swn-cal-tip"></div>
                            </div>
                            <h3 class="arabic-text">تقويم حجوزات أوضح</h3>
                            <p class="arabic-text">تفاصيل المواعيد والمريض تظهر بشكل صحيح بعد الحفظ.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-chat-square-text"></i><span>تلميحات التقويم RTL — القيم الإنجليزية تبقى يساراً</span></li>
                                <li><i class="bi bi-calendar2-check"></i><span>التحديد التلقائي للمريض: اسم + هاتف (مثل الطبيب)</span></li>
                                <li><i class="bi bi-x-circle"></i><span>إصلاح التلميح العالق بعد حفظ الموعد</span></li>
                                <li><i class="bi bi-layout-text-window"></i><span>بطاقات زجاجية + أزرار أوضح في التقويم</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">المودالات</span>
                            <div class="swn-stage swn-modal-stage" aria-hidden="true">
                                <div class="swn-modal-bg"></div>
                                <div class="swn-modal-box">
                                    <div class="swn-modal-field"></div>
                                    <div class="swn-modal-field"></div>
                                    <div class="swn-modal-field is-wide"></div>
                                    <div class="swn-modal-field is-notes"></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">مودالات مُحسَّنة</h3>
                            <p class="arabic-text">خلفية شفافة فوق الشريط الجانبي وحقول بترتيب أوضح.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-window"></i><span>backdrop شفاف فوق الـ sidebar</span></li>
                                <li><i class="bi bi-journal-text"></i><span>مودال الحجز: الملاحظات تحت الوقت</span></li>
                                <li><i class="bi bi-person-plus"></i><span>مودال المريض: جنس/عيادة تحت تاريخ الميلاد + chip العيادة</span></li>
                                <li><i class="bi bi-geo-alt"></i><span>حقل العنوان يمتد بمحاذاة صف العيادة</span></li>
                                <li><i class="bi bi-list-ul"></i><span>قائمة نوع الزيارة: النص لا يُلفّ في القائمة</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">بحث ذكي</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-digit-row">
                                    <span class="swn-digit-chip">٠١٠٠٣٠٤٤</span>
                                    <span class="swn-digit-eq">=</span>
                                    <span class="swn-digit-chip">01003044</span>
                                </div>
                            </div>
                            <h3 class="arabic-text">أرقام عربية وإنجليزية — نفس النتيجة</h3>
                            <p class="arabic-text">البحث العام، ⌘K، الإكمال التلقائي، ومودال الحجز يفهمون الأرقام الهندية والإنجليزية.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-search"></i><span>خانة البحث العلوية</span></li>
                                <li><i class="bi bi-person-lines-fill"></i><span>بحث المريض في الحجز والمرضى</span></li>
                                <li><i class="bi bi-telephone"></i><span>هاتف + رقم قومي + تواريخ بأرقام عربية</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">المهام</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-todo-mock">
                                    <div class="swn-todo-row is-done"></div>
                                    <div class="swn-todo-row"></div>
                                    <div class="swn-todo-row"></div>
                                    <div class="swn-todo-row is-done"></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">قائمة المهام — عربية كاملة</h3>
                            <p class="arabic-text">درج المهام من الهيدر: قوائم، أرشفة، استعادة، وحذف بمودالات واضحة.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-check2-square"></i><span>واجهة عربية + اختصارات لوحة المفاتيح</span></li>
                                <li><i class="bi bi-archive"></i><span>أرشفة القوائم واستعادتها</span></li>
                                <li><i class="bi bi-bell"></i><span>شارة العدد في الهيدر — دقيقة</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">الملاحظات</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-notes-mock">
                                    <div class="swn-notes-line" style="width:80%"></div>
                                    <div class="swn-notes-line" style="width:65%"></div>
                                    <div class="swn-notes-line" style="width:90%"></div>
                                    <div class="swn-notes-line" style="width:55%"></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">درج الملاحظات السريعة</h3>
                            <p class="arabic-text">ملاحظات فورية من الهيدر — زجاجي، RTL، ومزامنة مع اختصار ⌘K.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-journal-text"></i><span>ملاحظة سريعة + قوالب</span></li>
                                <li><i class="bi bi-layout-sidebar-reverse"></i><span>درج من اليمين — لا يحجب الشريط الجانبي</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">الثيم</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-pal-mock">
                                    <span class="swn-pal-swatch"></span>
                                    <span class="swn-pal-swatch"></span>
                                    <span class="swn-pal-swatch"></span>
                                </div>
                            </div>
                            <h3 class="arabic-text">ثيم وألوان متوافقة</h3>
                            <p class="arabic-text">لوحة ألوان، وضع ليلي/نهاري، وجدولة تلقائية — مثل الطبيب لكن باتجاه RTL.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-palette"></i><span>شبكة ألوان + معاينة حية</span></li>
                                <li><i class="bi bi-moon-stars"></i><span>جدولة تلقائية للوضع الداكن</span></li>
                                <li><i class="bi bi-sliders"></i><span>من صفحة الإعدادات الشخصية</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">الملف الشخصي</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-profile-mock">
                                    <div class="swn-profile-hero"></div>
                                    <div class="swn-profile-body">
                                        <div class="swn-profile-line" style="width:70%"></div>
                                        <div class="swn-profile-line" style="width:90%"></div>
                                        <div class="swn-profile-line" style="width:50%"></div>
                                    </div>
                                </div>
                            </div>
                            <h3 class="arabic-text">صفحة الملف الشخصي</h3>
                            <p class="arabic-text">بطاقة ترحيب، صورة شخصية، ونموذج تعديل عربي كامل.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-person-badge"></i><span>Hero + رفع صورة</span></li>
                                <li><i class="bi bi-pencil"></i><span>تعديل الاسم والهاتف والبريد</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">المرضى</span>
                            <div class="swn-stage swn-stats-stage" aria-hidden="true">
                                <div class="swn-stats-row">
                                    <div class="swn-stat-card"><span class="swn-val"></span><span class="swn-line"></span></div>
                                    <div class="swn-stat-card"><span class="swn-val"></span><span class="swn-line"></span></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">قائمة المرضى وملف المريض</h3>
                            <p class="arabic-text">كروت إحصائية، بحث فوري، مجلدات، وسِمات — بمستوى الطبيب.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-people"></i><span>قائمة + ملف تفصيلي عربي</span></li>
                                <li><i class="bi bi-folder"></i><span>مجلدات + علامات + ملفات إدارية</span></li>
                                <li><i class="bi bi-image"></i><span>عارض صور بزوم للمرفقات</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">الإعدادات</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-profile-mock" style="max-width:180px">
                                    <div class="swn-profile-body" style="padding:12px">
                                        <div class="swn-profile-line" style="width:85%"></div>
                                        <div class="swn-profile-line" style="width:60%"></div>
                                        <div class="swn-profile-line" style="width:75%"></div>
                                        <div class="swn-profile-line" style="width:40%"></div>
                                    </div>
                                </div>
                            </div>
                            <h3 class="arabic-text">الإعدادات الشخصية</h3>
                            <p class="arabic-text">تفضيلات الواجهة، الثيم، والإشعارات — محفوظة عبر API مخصص للسكرتير.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-gear"></i><span>`/secretary/settings` + `/api/secretary/settings`</span></li>
                                <li><i class="bi bi-bell-slash"></i><span>تفضيلات الإشعارات والواجهة</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">الإشعارات</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-notif-mock">
                                    <div class="swn-notif-row is-new"></div>
                                    <div class="swn-notif-row"></div>
                                    <div class="swn-notif-row is-new"></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">مركز الإشعارات</h3>
                            <p class="arabic-text">جرس الهيدر يفتح مركز إشعارات iOS-style — عربي، زجاجي، مع عدّاد دقيق.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-bell"></i><span>تجميع حسب النوع + قراءة/غير مقروء</span></li>
                                <li><i class="bi bi-phone"></i><span>دعم الإشعارات الفورية (push)</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">⌘K والبحث</span>
                            <div class="swn-stage swn-feat-stage" aria-hidden="true">
                                <div class="swn-cmdk-mock">
                                    <div class="swn-cmdk-input"></div>
                                    <div class="swn-todo-row" style="height:14px;margin-bottom:4px"></div>
                                    <div class="swn-todo-row" style="height:14px"></div>
                                </div>
                            </div>
                            <h3 class="arabic-text">لوحة الأوامر والبحث العام</h3>
                            <p class="arabic-text">⌘K للانتقال السريع + خانة البحث العلوية بزجاج ضبابي فوق الهيدر.</p>
                            <ul class="swn-bullets arabic-text">
                                <li><i class="bi bi-command"></i><span>مرضى · صفحات · إجراءات · مهام</span></li>
                                <li><i class="bi bi-search"></i><span>بحث شامل مع أرقام عربية/إنجليزية</span></li>
                                <li><i class="bi bi-keyboard"></i><span>مساعدة اختصارات عربية</span></li>
                            </ul>
                        </div>

                        <div class="swn-slide">
                            <span class="swn-kicker">جاهز للاستخدام</span>
                            <div class="swn-stage swn-done-stage" aria-hidden="true">
                                <div class="swn-done-badge"><i class="bi bi-check-lg"></i></div>
                            </div>
                            <h3 class="arabic-text">كل شيء جاهز — v11.0.3</h3>
                            <p class="arabic-text">١٥ شريحة من التحسينات — من اللوحة إلى المرضى والبحث الذكي. نتمنى لك يوماً سلساً.</p>
                        </div>

                    </div>
                </div>
                <div class="swn-dots" id="swnDots"></div>
            </div>

            <div class="modal-footer">
                <div class="swn-foot-nav">
                    <button type="button" class="btn btn-primary arabic-text" id="swnNext">
                        التالي<i class="bi bi-chevron-left ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary arabic-text" id="swnPrev" disabled>
                        <i class="bi bi-chevron-right me-1"></i>السابق
                    </button>
                </div>
                <div class="swn-foot-end" id="swnEnd">
                    <button type="button" class="btn btn-outline-secondary arabic-text" id="swnDontShow">
                        لا تُظهر مرة أخرى
                    </button>
                    <button type="button" class="btn btn-primary arabic-text" id="swnClose">
                        <i class="bi bi-check-lg ms-1"></i>إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const VERSION = 'v11_0_3';
    const OPT_OUT_KEY = 'secWhatsNew_' + VERSION + '_optOut';

    function init() {
        const el = document.getElementById('secWhatsNewModal');
        if (!el || typeof bootstrap === 'undefined') return;

        const track = el.querySelector('#swnTrack');
        const slides = track ? track.children : [];
        const total = slides.length;
        const dotsWrap = el.querySelector('#swnDots');
        const prevBtn = el.querySelector('#swnPrev');
        const nextBtn = el.querySelector('#swnNext');
        const endWrap = el.querySelector('#swnEnd');
        let idx = 0;

        for (let i = 0; i < total; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('aria-label', 'الانتقال إلى الشريحة ' + (i + 1));
            b.addEventListener('click', function () { go(i); });
            dotsWrap.appendChild(b);
        }

        function render() {
            track.style.transform = 'translateX(-' + (idx * 100) + '%)';
            Array.from(dotsWrap.children).forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
            });
            prevBtn.disabled = idx === 0;
            const last = idx === total - 1;
            nextBtn.style.display = last ? 'none' : '';
            endWrap.classList.toggle('show', last);
        }

        function go(i) {
            idx = Math.max(0, Math.min(total - 1, i));
            render();
        }

        nextBtn.addEventListener('click', function () { go(idx + 1); });
        prevBtn.addEventListener('click', function () { go(idx - 1); });

        el.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') go(idx + 1);
            if (e.key === 'ArrowRight') go(idx - 1);
        });

        function syncTheme() {
            const dark = document.documentElement.classList.contains('dark');
            el.setAttribute('data-swn-theme', dark ? 'dark' : 'light');
        }
        syncTheme();
        try {
            new MutationObserver(syncTheme).observe(document.documentElement, {
                attributes: true, attributeFilter: ['class']
            });
        } catch (_) {}

        el.addEventListener('show.bs.modal', function () {
            idx = 0;
            syncTheme();
            render();
        });

        function modal() {
            return bootstrap.Modal.getOrCreateInstance(el);
        }

        el.querySelector('#swnDontShow').addEventListener('click', function () {
            try { localStorage.setItem(OPT_OUT_KEY, '1'); } catch (_) {}
            modal().hide();
        });
        el.querySelector('#swnClose').addEventListener('click', function () {
            modal().hide();
        });

        render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
