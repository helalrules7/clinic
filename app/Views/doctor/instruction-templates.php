<link href="/app/Views/doctor/assets/css/mi-modals.css?v=<?= file_exists(__DIR__ . '/assets/css/mi-modals.css') ? filemtime(__DIR__ . '/assets/css/mi-modals.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/instruction-templates.css?v=<?= file_exists(__DIR__ . '/assets/css/instruction-templates.css') ? filemtime(__DIR__ . '/assets/css/instruction-templates.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/tags-admin.css?v=<?= file_exists(__DIR__ . '/assets/css/tags-admin.css') ? filemtime(__DIR__ . '/assets/css/tags-admin.css') : time() ?>" rel="stylesheet">

<div class="tags-templates-page">
    <div class="inst-tpl-header mb-4">
        <div class="inst-tpl-header-main">
            <span class="inst-tpl-header-icon"><i class="bi bi-tags"></i></span>
            <div>
                <h4 class="inst-tpl-header-title">Tags and Templates</h4>
                <p class="inst-tpl-header-sub mb-0">Manage patient tags, appointment tags, drug links, and instruction templates</p>
            </div>
        </div>
    </div>

    <section class="tags-admin-section" id="patientTagsAdmin">
        <div class="tags-admin-head">
            <h5><i class="bi bi-person-badge me-2"></i>Patient Tags</h5>
            <span class="text-muted small">Global or private · reports with drill-down</span>
        </div>
        <div class="tags-admin-body" id="patientTagsAdminBody"></div>
    </section>

    <section class="tags-admin-section" id="appointmentTagsAdmin">
        <div class="tags-admin-head">
            <h5><i class="bi bi-calendar-event me-2"></i>Appointment Tags</h5>
            <span class="text-muted small">Persistent tags saved on the appointment record</span>
        </div>
        <div class="tags-admin-body" id="appointmentTagsAdminBody"></div>
    </section>

    <section class="tags-admin-section" id="drugTagsAdmin">
        <div class="tags-admin-head">
            <h5><i class="bi bi-capsule me-2"></i>Drug → Patient Tag Links</h5>
            <span class="text-muted small">Suggest patient tags when prescribing linked drugs</span>
        </div>
        <div class="tags-admin-body" id="drugTagsAdminBody"></div>
    </section>

    <hr class="tags-templates-divider">

    <div class="inst-tpl-header mb-4">
        <div class="inst-tpl-header-main">
            <span class="inst-tpl-header-icon"><i class="bi bi-journal-medical"></i></span>
            <div>
                <h4 class="inst-tpl-header-title">Instruction Templates</h4>
                <p class="inst-tpl-header-sub mb-0">General instruction templates — shared, linked to diagnoses, used in appointments</p>
            </div>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="instTplAddBtn">
            <i class="bi bi-plus-lg me-1"></i>New Template
        </button>
    </div>

    <div id="instructionTemplatesManager" class="inst-tpl-manager"></div>
</div>

<script src="/app/Views/doctor/assets/js/tags-admin.js?v=<?= file_exists(__DIR__ . '/assets/js/tags-admin.js') ? filemtime(__DIR__ . '/assets/js/tags-admin.js') : time() ?>"></script>
<script src="/app/Views/doctor/assets/js/instruction-templates.js?v=<?= file_exists(__DIR__ . '/assets/js/instruction-templates.js') ? filemtime(__DIR__ . '/assets/js/instruction-templates.js') : time() ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.tagsAdmin && window.tagsAdmin.mount) window.tagsAdmin.mount();
    if (window.instructionTemplates && window.instructionTemplates.mount) {
        window.instructionTemplates.mount('#instructionTemplatesManager', '#instTplAddBtn');
    }
});
</script>
