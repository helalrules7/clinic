<?php
// What's New v9.0.0 modal — shown once per browser via localStorage.
// Included from layouts/main.php (doctor/admin) and secretary_main.php.
// Bump WHATS_NEW_STORAGE_KEY in a future release to surface a new modal again.
?>
<style>
    /* ---- modal shell ----------------------------------------------------- */
    #whatsNewV9Modal .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .dark #whatsNewV9Modal .modal-content {
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    }
    #whatsNewV9Modal .modal-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #0ea5e9 100%);
        color: #fff;
        border-bottom: none;
        padding: 1.1rem 1.5rem;
    }
    #whatsNewV9Modal .modal-header .modal-title {
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    #whatsNewV9Modal .modal-header .version-pill {
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(6px);
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }
    #whatsNewV9Modal .modal-body { padding: 1.5rem; }
    #whatsNewV9Modal .feature-card {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 14px;
        border-radius: 12px;
        background: rgba(99, 102, 241, 0.05);
        border: 1px solid rgba(99, 102, 241, 0.12);
        margin-bottom: 12px;
    }
    .dark #whatsNewV9Modal .feature-card {
        background: rgba(99, 102, 241, 0.10);
        border-color: rgba(99, 102, 241, 0.25);
    }
    #whatsNewV9Modal .feature-card.is-highlight {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, rgba(14, 165, 233, 0.08) 100%);
        border-color: rgba(139, 92, 246, 0.25);
    }
    .dark #whatsNewV9Modal .feature-card.is-highlight {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.18) 0%, rgba(14, 165, 233, 0.18) 100%);
    }
    #whatsNewV9Modal .feature-icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.3rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    #whatsNewV9Modal .feature-icon.bg-grad-violet { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    #whatsNewV9Modal .feature-icon.bg-grad-emerald { background: linear-gradient(135deg, #10b981, #059669); }
    #whatsNewV9Modal .feature-icon.bg-grad-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
    #whatsNewV9Modal .feature-icon.bg-grad-sky { background: linear-gradient(135deg, #0ea5e9, #6366f1); }
    #whatsNewV9Modal .feature-body { flex: 1; min-width: 0; }
    #whatsNewV9Modal .feature-title {
        margin: 0 0 4px 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .dark #whatsNewV9Modal .feature-title { color: #f1f5f9; }
    #whatsNewV9Modal .feature-text {
        margin: 0;
        font-size: 0.88rem;
        line-height: 1.45;
        color: #475569;
    }
    .dark #whatsNewV9Modal .feature-text { color: #cbd5e1; }
    #whatsNewV9Modal .feature-text strong { color: #0f172a; }
    .dark #whatsNewV9Modal .feature-text strong { color: #f8fafc; }

    /* ---- animated drawing mockup ---------------------------------------- */
    #whatsNewV9Modal .draw-mockup {
        margin-top: 12px;
        position: relative;
        height: 180px;
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .dark #whatsNewV9Modal .draw-mockup {
        background: #0b1220;
        border-color: #1e293b;
    }

    /* Mini toolbar that cycles through tool icons */
    #whatsNewV9Modal .draw-mockup-toolbar {
        position: absolute;
        top: 6px;
        left: 50%;
        transform: translateX(-50%);
        display: inline-flex;
        gap: 4px;
        background: rgba(248, 250, 252, 0.92);
        backdrop-filter: blur(6px);
        padding: 4px 6px;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        z-index: 3;
    }
    .dark #whatsNewV9Modal .draw-mockup-toolbar {
        background: rgba(15, 23, 42, 0.85);
        border-color: rgba(255, 255, 255, 0.1);
    }
    #whatsNewV9Modal .draw-mockup-tool {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        color: #64748b;
        background: transparent;
        transition: background 0.3s, color 0.3s, transform 0.3s;
    }
    #whatsNewV9Modal .draw-mockup-tool.is-active {
        background: #0ea5e9;
        color: #fff;
        transform: scale(1.12);
        box-shadow: 0 2px 6px rgba(14, 165, 233, 0.4);
    }

    /* SVG holds the eye-shape "sketch" being drawn */
    #whatsNewV9Modal .draw-mockup-svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    #whatsNewV9Modal .draw-path {
        fill: none;
        stroke: #0f172a;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 720;
        stroke-dashoffset: 720;
        animation: drawEye 4.5s ease-in-out infinite;
    }
    .dark #whatsNewV9Modal .draw-path { stroke: #e2e8f0; }
    #whatsNewV9Modal .draw-path-arrow {
        fill: none;
        stroke: #ef4444;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 120;
        stroke-dashoffset: 120;
        animation: drawArrow 4.5s ease-in-out infinite;
        animation-delay: 2s;
    }
    #whatsNewV9Modal .draw-cursor {
        position: absolute;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.25);
        animation: moveCursor 4.5s ease-in-out infinite;
        z-index: 2;
        pointer-events: none;
    }

    @keyframes drawEye {
        0%   { stroke-dashoffset: 720; }
        45%  { stroke-dashoffset: 0;   }
        85%  { stroke-dashoffset: 0;   opacity: 1; }
        95%  { opacity: 1; }
        100% { stroke-dashoffset: 720; opacity: 0; }
    }
    @keyframes drawArrow {
        0%, 30% { stroke-dashoffset: 120; }
        60%     { stroke-dashoffset: 0;   }
        85%     { stroke-dashoffset: 0;   opacity: 1; }
        100%    { stroke-dashoffset: 120; opacity: 0; }
    }
    @keyframes moveCursor {
        0%   { left: 20%;  top: 60%; }
        15%  { left: 35%;  top: 35%; }
        30%  { left: 50%;  top: 50%; }
        45%  { left: 70%;  top: 55%; }
        55%  { left: 55%;  top: 70%; opacity: 1; }
        62%  { left: 60%;  top: 100px; }
        72%  { left: 75%;  top: 90px; }
        90%  { left: 75%;  top: 90px; opacity: 1; }
        100% { left: 20%;  top: 60%; opacity: 0; }
    }

    /* Tool cycling — each tool is active in its own quarter of the loop */
    #whatsNewV9Modal .draw-mockup-tool[data-cycle="0"] { animation: toolCycle0 4.5s steps(1) infinite; }
    #whatsNewV9Modal .draw-mockup-tool[data-cycle="1"] { animation: toolCycle1 4.5s steps(1) infinite; }
    #whatsNewV9Modal .draw-mockup-tool[data-cycle="2"] { animation: toolCycle2 4.5s steps(1) infinite; }
    #whatsNewV9Modal .draw-mockup-tool[data-cycle="3"] { animation: toolCycle3 4.5s steps(1) infinite; }
    @keyframes toolCycle0 { 0%, 25%   { background: #0ea5e9; color: #fff; transform: scale(1.12); } 25.01%, 100% { } }
    @keyframes toolCycle1 { 25%, 50%  { background: #0ea5e9; color: #fff; transform: scale(1.12); } }
    @keyframes toolCycle2 { 50%, 75%  { background: #0ea5e9; color: #fff; transform: scale(1.12); } }
    @keyframes toolCycle3 { 75%, 100% { background: #0ea5e9; color: #fff; transform: scale(1.12); } }

    #whatsNewV9Modal .modal-footer {
        background: transparent;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
    }
    .dark #whatsNewV9Modal .modal-footer { border-top-color: rgba(255, 255, 255, 0.1); }
    #whatsNewV9Modal .btn-got-it {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border: none;
        padding: 0.55rem 1.5rem;
        font-weight: 600;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.35);
    }
    #whatsNewV9Modal .btn-got-it:hover {
        filter: brightness(1.05);
        color: #fff;
    }

    @media (max-width: 576px) {
        #whatsNewV9Modal .feature-card { padding: 10px; gap: 10px; }
        #whatsNewV9Modal .feature-icon { width: 38px; height: 38px; font-size: 1.05rem; }
        #whatsNewV9Modal .draw-mockup { height: 150px; }
    }
</style>

<div class="modal fade" id="whatsNewV9Modal" tabindex="-1" aria-labelledby="whatsNewV9Title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="whatsNewV9Title">
                    <i class="bi bi-stars me-2"></i>
                    What's new
                    <span class="version-pill">v9.0.0</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Two-clinic architecture -->
                <div class="feature-card">
                    <div class="feature-icon bg-grad-violet"><i class="bi bi-buildings-fill"></i></div>
                    <div class="feature-body">
                        <h6 class="feature-title">عيادتان منفصلتان: الرياض و كفر الشيخ</h6>
                        <p class="feature-text">
                            كل سكرتارية مربوطة بعيادتها — ترى مواعيدها وحساباتها فقط.
                            الأطباء يرون <strong>الكل</strong> ويستطيعون فلترة العرض حسب العيادة.
                            قوائم المرضى تظل مشتركة لأن المريض يمكن أن يحجز فى أى عيادة.
                        </p>
                    </div>
                </div>

                <!-- Draw Consultation tool with animated mockup -->
                <div class="feature-card is-highlight">
                    <div class="feature-icon bg-grad-sky"><i class="bi bi-pencil-square"></i></div>
                    <div class="feature-body">
                        <h6 class="feature-title">Draw Consultation — لوحة رسم على الجهاز اللوحى</h6>
                        <p class="feature-text">
                            رسم مرفقات الكشف مباشرة من صفحة الموعد وصفحة المريض:
                            رصاص، جاف، ماركر، ممحاة، أشكال (مستطيل/دائرة/مثلث/خط)،
                            ضبط لون التعبئة والإطار، Undo / Redo، وحفظ تلقائى كل 30 ثانية
                            <strong>فى نفس الملف</strong>. تظهر الرسمة فورًا فى قسم Images &amp; Attachments.
                        </p>

                        <div class="draw-mockup" aria-hidden="true">
                            <div class="draw-mockup-toolbar">
                                <span class="draw-mockup-tool" data-cycle="0" title="Pencil"><i class="bi bi-pencil"></i></span>
                                <span class="draw-mockup-tool" data-cycle="1" title="Pen"><i class="bi bi-pen"></i></span>
                                <span class="draw-mockup-tool" data-cycle="2" title="Marker"><i class="bi bi-brush-fill"></i></span>
                                <span class="draw-mockup-tool" data-cycle="3" title="Eraser"><i class="bi bi-eraser"></i></span>
                            </div>
                            <svg class="draw-mockup-svg" viewBox="0 0 500 180" preserveAspectRatio="xMidYMid meet">
                                <!-- Eye outline (almond shape) being "drawn" -->
                                <path class="draw-path" d="M 100 90 Q 250 30 400 90 Q 250 150 100 90 Z M 250 70 a 20 20 0 1 0 0 40 a 20 20 0 1 0 0 -40 Z" />
                                <!-- Annotation arrow with label position -->
                                <path class="draw-path-arrow" d="M 310 30 L 360 60 M 360 60 L 348 52 M 360 60 L 358 46" />
                            </svg>
                            <span class="draw-cursor" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>

                <!-- Financial overhaul -->
                <div class="feature-card">
                    <div class="feature-icon bg-grad-emerald"><i class="bi bi-coin"></i></div>
                    <div class="feature-body">
                        <h6 class="feature-title">حسابات مالية أنظف وأدقّ</h6>
                        <p class="feature-text">
                            • كل عيادة لها حسابها (دفعات، مصروفات، رصيد يومى، إقفال يومى) — لا تداخل بين الفرعين.<br>
                            • &laquo;المبلغ المُستلم&raquo; صار <strong>NET</strong>: يطرح الخصومات ويعفى السجلات المُعفاة.<br>
                            • فلتر &laquo;العيادة&raquo; جديد على صفحة Financial Management للأطباء.<br>
                            • للطبيب الحق فى إلغاء/حذف أى دفعة، وعند ذلك يُخصم المبلغ تلقائيًا من رصيد السكرتارية.<br>
                            • تثبيت توقيت قاعدة البيانات لـ Africa/Cairo فلا تختفى دفعات الليل من إحصائيات اليوم.
                        </p>
                    </div>
                </div>

                <!-- Bulk delete + pagination + filename fixes -->
                <div class="feature-card">
                    <div class="feature-icon bg-grad-amber"><i class="bi bi-images"></i></div>
                    <div class="feature-body">
                        <h6 class="feature-title">إدارة المرفقات والصور — أسرع وأذكى</h6>
                        <p class="feature-text">
                            • Select All + Delete All أيقونيتان مع تأكيد حذف موحَّد.<br>
                            • Pagination تظهر تلقائيًا بعد 4 صور، مع Loading overlay عند التنقّل.<br>
                            • أسماء الملفات تظهر كاملة (لا قطع مبكر)، مع ellipsis ذكى عند الفيض الحقيقى.
                        </p>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-got-it" data-bs-dismiss="modal">
                    <i class="bi bi-check2-circle me-2"></i>تمام، فهمت!
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Bump this key when a future release wants to surface a new "what's new"
    // popup — every browser will see the new one once again. Tied to the
    // running APP_VERSION constant so it's grep-friendly across the codebase.
    const STORAGE_KEY = 'whatsNew_v9_0_0_dismissed';

    function showOnce() {
        if (localStorage.getItem(STORAGE_KEY) === '1') return;
        const el = document.getElementById('whatsNewV9Modal');
        if (!el || typeof bootstrap === 'undefined') return;

        // Delay a beat so the rest of the page settles first (avoids stacking
        // on top of any auto-shown session-warning / notification toasts).
        setTimeout(() => {
            try {
                const modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: true });
                modal.show();
                el.addEventListener('hidden.bs.modal', () => {
                    try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) { /* ignore */ }
                }, { once: true });
            } catch (e) { /* ignore — if Bootstrap fails we just skip the popup */ }
        }, 800);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showOnce);
    } else {
        showOnce();
    }
})();
</script>
