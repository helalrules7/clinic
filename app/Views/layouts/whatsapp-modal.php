<?php
// =====================================================================
// WhatsApp Integration Modal Markup
// =====================================================================
$__role = $this->getCurrentUser()['role'] ?? 'doctor';
$__isSec = ($__role === 'secretary');
?>
<link rel="stylesheet" href="/app/Views/doctor/assets/css/whatsapp.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/whatsapp.css') ? filemtime(__DIR__ . '/../doctor/assets/css/whatsapp.css') : time() ?>">

<div class="modal fade whatsapp-integration-modal" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalTitle" aria-hidden="true" <?= $__isSec ? 'dir="rtl"' : '' ?>>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content wa-content">
            
            <div class="modal-header wa-header <?= $__isSec ? 'bg-success text-white' : 'bg-primary text-white' ?>">
                <h5 class="modal-title d-flex align-items-center gap-2" id="whatsappModalTitle">
                    <span class="wa-icon"><i class="bi bi-whatsapp"></i></span>
                    <span><?= $__isSec ? 'ارسال رسالة واتساب' : 'Send WhatsApp Message' ?></span>
                </h5>
                <button type="button" class="btn-close <?= $__isSec ? 'btn-close-white' : '' ?>" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body wa-body p-0">
                <div class="container-fluid p-0">
                    <div class="row g-0">
                        <!-- Left Panel: Templates List -->
                        <div class="col-md-4 border-end wa-templates-panel p-3">
                            <h6 class="wa-panel-title mb-3">
                                <i class="bi bi-card-text me-1"></i>
                                <span><?= $__isSec ? 'قوالب الرسائل' : 'Message Templates' ?></span>
                            </h6>
                            <div class="position-relative">
                                <div class="text-center py-4 text-muted" id="waTemplatesLoading">
                                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                                    <span class="ms-2"><?= $__isSec ? 'جاري التحميل...' : 'Loading...' ?></span>
                                </div>
                                <div class="list-group wa-templates-list" id="waTemplateGroup" role="tablist">
                                    <!-- Injected dynamically by JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Message Editor & Settings -->
                        <div class="col-md-8 p-3 wa-editor-panel d-flex flex-column">
                            <form id="whatsappMessageForm" class="d-flex flex-column flex-grow-1" autocomplete="off" novalidate>
                                <input type="hidden" id="waPatientId" name="patient_id" value="">
                                <input type="hidden" id="waAppointmentId" name="appointment_id" value="">
                                <input type="hidden" id="waTemplateId" name="template_id" value="">
                                
                                <!-- Patient Info Summary -->
                                <div class="wa-patient-summary p-2 mb-3 rounded d-flex justify-content-between align-items-center bg-light border">
                                    <div>
                                        <strong class="d-block" id="waSummaryName">-</strong>
                                        <small class="text-muted" id="waSummaryPhone">-</small>
                                    </div>
                                    <span class="badge" id="waConsentBadge">-</span>
                                </div>

                                <!-- Consent Notice Alert (If no consent) -->
                                <div class="alert alert-warning py-2 px-3 mb-3 d-none" id="waConsentAlert" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <span><?= $__isSec ? 'المريض لم يوافق بعد على استقبال الرسائل الطبية.' : 'Patient has not yet consented to medical messaging.' ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-warning ms-2 py-0 float-end" id="waOverrideConsentBtn">
                                        <?= $__isSec ? 'الموافقة الآن' : 'Consent Now' ?>
                                    </button>
                                </div>

                                <!-- Phone Input -->
                                <div class="mb-3">
                                    <label for="waPhoneInput" class="form-label"><?= $__isSec ? 'رقم الهاتف (واتساب)' : 'WhatsApp Phone Number' ?></label>
                                    <input type="text" class="form-control" id="waPhoneInput" name="phone_number" required>
                                </div>

                                <!-- Message Body Textarea -->
                                <div class="mb-3 flex-grow-1 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="waMessageBody" class="form-label mb-0"><?= $__isSec ? 'نص الرسالة' : 'Message Content' ?></label>
                                        <small class="text-muted" id="waCharCounter">0 chars</small>
                                    </div>
                                    <textarea class="form-control wa-textarea flex-grow-1" id="waMessageBody" name="message_body" rows="6" required placeholder="<?= $__isSec ? 'اختر قالب رسالة أو اكتب هنا...' : 'Select a template or type here...' ?>"></textarea>
                                </div>

                                <!-- Emergency Warning Disclaimer (for emergency category templates) -->
                                <div class="alert alert-info py-2 px-3 mb-3 d-none" id="waEmergencyDisclaimer" role="alert">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <small class="text-dark">
                                        <strong><?= $__isSec ? 'تنويه:' : 'Emergency Disclaimer:' ?></strong>
                                        <?= $__isSec ? 'هذه الرسالة لا تحل محل الرعاية الطبية الطارئة.' : 'This message does not replace emergency medical care.' ?>
                                    </small>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer wa-footer border-top bg-light p-2">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div id="waStatusSpinner" class="text-success d-none">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span class="ms-1 small"><?= $__isSec ? 'جاري التجهيز...' : 'Logging activity...' ?></span>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i><?= $__isSec ? 'إلغاء' : 'Cancel' ?>
                        </button>
                        <button type="button" class="btn <?= $__isSec ? 'btn-success' : 'btn-primary' ?> btn-sm" id="waSendBtn">
                            <i class="bi bi-whatsapp me-1"></i><?= $__isSec ? 'فتح واتساب' : 'Open WhatsApp' ?>
                        </button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Reusable WhatsApp Confirmation Modal -->
<div class="modal fade" id="whatsappConfirmModal" tabindex="-1" aria-labelledby="whatsappConfirmModalTitle" aria-hidden="true" <?= $__isSec ? 'dir="rtl"' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header <?= $__isSec ? 'bg-success' : 'bg-primary' ?> text-white py-2 px-3">
                <h6 class="modal-title d-flex align-items-center gap-2" id="whatsappConfirmModalTitle">
                    <i class="bi bi-whatsapp"></i>
                    <span><?= $__isSec ? 'تأكيد إرسال واتساب' : 'WhatsApp Confirmation' ?></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 px-3 text-center" id="whatsappConfirmModalMessage" style="font-size: 1.15rem; color: #333; line-height: 1.5; font-weight: 500;">
                <!-- Message will be injected here -->
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-3 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal" id="waConfirmCancelBtn" style="border-radius: 6px;">
                    <?= $__isSec ? 'إلغاء' : 'Cancel' ?>
                </button>
                <button type="button" class="btn <?= $__isSec ? 'btn-success' : 'btn-primary' ?> btn-sm px-4" id="waConfirmOkBtn" style="border-radius: 6px;">
                    <?= $__isSec ? 'موافق' : 'Confirm' ?>
                </button>
            </div>
        </div>
    </div>
</div>
