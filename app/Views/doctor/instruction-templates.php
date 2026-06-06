<link href="/app/Views/doctor/assets/css/mi-modals.css?v=<?= file_exists(__DIR__ . '/assets/css/mi-modals.css') ? filemtime(__DIR__ . '/assets/css/mi-modals.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/instruction-templates.css?v=<?= file_exists(__DIR__ . '/assets/css/instruction-templates.css') ? filemtime(__DIR__ . '/assets/css/instruction-templates.css') : time() ?>" rel="stylesheet">

<div class="inst-tpl-header mb-4">
    <div class="inst-tpl-header-main">
        <span class="inst-tpl-header-icon"><i class="bi bi-journal-medical"></i></span>
        <div>
            <h4 class="inst-tpl-header-title">Instruction Templates</h4>
            <p class="inst-tpl-header-sub mb-0">قوالب التعليمات العامة للعيادة — مشتركة بين الأطباء، تُربط بالتشخيص وتُستخدم في صفحة الموعد</p>
        </div>
    </div>
    <button type="button" class="btn btn-primary btn-sm" id="instTplAddBtn">
        <i class="bi bi-plus-lg me-1"></i>New Template
    </button>
</div>

<div id="instructionTemplatesManager" class="inst-tpl-manager"></div>

<script src="/app/Views/doctor/assets/js/instruction-templates.js?v=<?= file_exists(__DIR__ . '/assets/js/instruction-templates.js') ? filemtime(__DIR__ . '/assets/js/instruction-templates.js') : time() ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.instructionTemplates && window.instructionTemplates.mount) {
        window.instructionTemplates.mount('#instructionTemplatesManager', '#instTplAddBtn');
    }
});
</script>
