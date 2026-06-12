/**
 * WhatsApp Integration JS Module
 */
(function() {
    let templatesCache = null;
    let selectedTemplate = null;
    let currentPatient = null;
    let currentAppointmentId = null;

    function formatEgyptPhone(phone) {
        if (!phone) return '';
        let clean = phone.toString().trim().replace(/[\s\-\(\)]/g, '');
        let formatted = clean;
        if (clean.startsWith('+')) {
            formatted = clean;
        } else if (clean.startsWith('00')) {
            formatted = '+' + clean.substring(2);
        } else if (clean.startsWith('01')) {
            formatted = '+2' + clean;
        } else if (/^[1][0125]\d{8}$/.test(clean)) {
            formatted = '+20' + clean;
        } else {
            formatted = '+2' + clean;
        }
        return formatted;
    }

    const WhatsAppIntegration = {
        init: function() {
            // Bind trigger buttons when modal is shown
            const modalEl = document.getElementById('whatsappModal');
            if (!modalEl) return;

            modalEl.addEventListener('hidden.bs.modal', function() {
                selectedTemplate = null;
                currentPatient = null;
                currentAppointmentId = null;
                document.getElementById('whatsappMessageForm').reset();
                document.getElementById('waTemplateGroup').innerHTML = '';
                document.getElementById('waConsentAlert').classList.add('d-none');
                document.getElementById('waEmergencyDisclaimer').classList.add('d-none');
                document.getElementById('waSendBtn').disabled = false;
            });

            // Bind Send Button
            const sendBtn = document.getElementById('waSendBtn');
            if (sendBtn) {
                sendBtn.addEventListener('click', this.handleSend.bind(this));
            }

            // Bind Override Consent Button
            const overrideBtn = document.getElementById('waOverrideConsentBtn');
            if (overrideBtn) {
                overrideBtn.addEventListener('click', this.handleOverrideConsent.bind(this));
            }

            // Bind Message text area char counter
            const textarea = document.getElementById('waMessageBody');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    document.getElementById('waCharCounter').textContent = `${this.value.length} chars`;
                });
            }
        },

        _isRtl: function() {
            const m = document.getElementById('whatsappModal');
            return (m && m.getAttribute('dir') === 'rtl') || document.documentElement.getAttribute('dir') === 'rtl';
        },

        openModal: async function(patientId, appointmentId = null, defaultCategory = null, fallbackData = null) {
            // First check if WhatsApp integration is enabled
            if (window.WHATSAPP_CONFIG && !window.WHATSAPP_CONFIG.enabled) {
                alert(document.documentElement.getAttribute('dir') === 'rtl' ? 'ميزة واتساب غير مفعلة في إعدادات النظام.' : 'WhatsApp feature is disabled in system settings.');
                return;
            }

            const modalEl = document.getElementById('whatsappModal');
            if (!modalEl) return;

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            currentAppointmentId = appointmentId;

            // Show loading indicators
            const loadingEl = document.getElementById('waTemplatesLoading');
            if (loadingEl) {
                loadingEl.style.display = 'block';
            }

            try {
                // Fetch templates
                await this.loadTemplates();

                // Fetch resolved values
                const response = await fetch('/api/whatsapp/resolve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(Object.assign({ patient_id: patientId, appointment_id: appointmentId }, fallbackData || {}))
                });
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Failed to resolve patient details');
                }

                currentPatient = data;
                document.getElementById('waPatientId').value = patientId;
                document.getElementById('waAppointmentId').value = appointmentId || '';
                const formattedPhone = formatEgyptPhone(data.phone);
                document.getElementById('waPhoneInput').value = formattedPhone;
                document.getElementById('waSummaryName').textContent = data.resolved_body || 'Patient';
                
                // Fetch resolved summary name / phone labels
                const patientName = data.resolved_body.split("\n")[0].replace("مرحبًا ", "").replace("،", "").trim();
                document.getElementById('waSummaryName').textContent = patientName;
                document.getElementById('waSummaryPhone').textContent = formattedPhone;

                // Handle Consent Badge
                const consentBadge = document.getElementById('waConsentBadge');
                const isRtl = modalEl.getAttribute('dir') === 'rtl';
                if (data.consent === 1) {
                    consentBadge.className = 'badge badge-consent-yes';
                    consentBadge.textContent = isRtl ? 'موافق' : 'Consented';
                    document.getElementById('waConsentAlert').classList.add('d-none');
                } else {
                    consentBadge.className = 'badge badge-consent-no';
                    consentBadge.textContent = isRtl ? 'غير موافق' : 'No Consent';
                    document.getElementById('waConsentAlert').classList.remove('d-none');
                }

                // Render templates list
                this.renderTemplates(defaultCategory);

            } catch (error) {
                console.error(error);
                alert(this._isRtl() ? 'حدث خطأ أثناء تحميل بيانات الواتساب.' : 'Error loading WhatsApp modal data: ' + error.message);
                modal.hide();
            }
        },

        loadTemplates: async function() {
            if (templatesCache) return;
            const res = await fetch('/api/whatsapp/templates');
            const data = await res.json();
            if (data.success) {
                templatesCache = data.templates;
            }
        },

        renderTemplates: function(defaultCategory = null) {
            const listContainer = document.getElementById('waTemplateGroup');
            listContainer.innerHTML = '';
            const loadingEl = document.getElementById('waTemplatesLoading');
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }

            if (!templatesCache || templatesCache.length === 0) {
                listContainer.innerHTML = '<div class="text-muted text-center py-3">' + (this._isRtl() ? 'لا توجد قوالب متاحة' : 'No templates found') + '</div>';
                return;
            }

            templatesCache.forEach(template => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                
                // Localized category/title translations
                const isRtl = document.getElementById('whatsappModal').getAttribute('dir') === 'rtl';
                let displayTitle = template.title;
                if (isRtl) {
                    const arabicTitles = {
                        'Appointment Confirmation': 'تأكيد الحجز',
                        'Appointment Reminder': 'تذكير بالموعد',
                        'Appointment Cancellation': 'إلغاء الموعد',
                        'Pupil Dilation Instructions': 'تعليمات قطرة توسيع الحدقة',
                        'Eye Drops Schedule': 'جدول قطرات العين',
                        'Post-Cataract Surgery': 'تعليمات المياه البيضاء',
                        'Post-LASIK / PRK': 'تعليمات تصحيح الإبصار',
                        'Post-Injection Instructions': 'تعليمات حقن العين',
                        'Investigation Request': 'طلب فحوصات عيون',
                        'Follow-up Reminder': 'تذكير بالمتابعة',
                        'Emergency Warning': 'تحذيرات الطوارئ الطبية',
                        'Medication Prescription Link': 'إرسال الوصفة الطبية (رابط)',
                        'Glasses Prescription Link': 'إرسال مقاس النظارة (رابط)',
                        'Comprehensive Visit Report': 'تقرير الزيارة الشامل'
                    };
                    displayTitle = arabicTitles[template.title] || template.title;
                }

                btn.textContent = displayTitle;
                btn.addEventListener('click', () => this.selectTemplate(template));
                listContainer.appendChild(btn);
            });

            // Auto select first template or matching category
            if (templatesCache.length > 0) {
                let initial = templatesCache[0];
                if (defaultCategory) {
                    const match = templatesCache.find(t => t.category === defaultCategory);
                    if (match) initial = match;
                }
                this.selectTemplate(initial);
            }
        },

        selectTemplate: async function(template) {
            selectedTemplate = template;
            document.getElementById('waTemplateId').value = template.id;

            // Highlight in templates list
            const buttons = document.querySelectorAll('#waTemplateGroup .list-group-item');
            buttons.forEach(btn => {
                if (btn.textContent === template.title || btn.textContent === (document.getElementById('whatsappModal').getAttribute('dir') === 'rtl' ? this.translateTitleToArabic(template.title) : template.title)) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Set loading text in body
            const textarea = document.getElementById('waMessageBody');
            textarea.value = this._isRtl() ? 'جاري تجهيز الرسالة...' : 'Resolving placeholders...';

            try {
                // Call resolve endpoint to parse placeholders for this template
                const patientId = document.getElementById('waPatientId').value;
                const appointmentId = document.getElementById('waAppointmentId').value;
                
                const response = await fetch('/api/whatsapp/resolve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ patient_id: patientId, appointment_id: appointmentId, template_id: template.id })
                });
                const data = await response.json();

                if (data.success) {
                    textarea.value = data.resolved_body;
                    document.getElementById('waCharCounter').textContent = `${data.resolved_body.length} chars`;

                    // Emergency disclaimer toggle
                    const emergencyAlert = document.getElementById('waEmergencyDisclaimer');
                    if (template.category === 'emergency' || template.title.includes('Emergency')) {
                        emergencyAlert.classList.remove('d-none');
                    } else {
                        emergencyAlert.classList.add('d-none');
                    }
                }
            } catch (e) {
                console.error(e);
                textarea.value = template.body;
            }
        },

        translateTitleToArabic: function(title) {
            const arabicTitles = {
                'Appointment Confirmation': 'تأكيد الحجز',
                'Appointment Reminder': 'تذكير بالموعد',
                'Appointment Cancellation': 'إلغاء الموعد',
                'Pupil Dilation Instructions': 'تعليمات قطرة توسيع الحدقة',
                'Eye Drops Schedule': 'جدول قطرات العين',
                'Post-Cataract Surgery': 'تعليمات المياه البيضاء',
                'Post-LASIK / PRK': 'تعليمات تصحيح الإبصار',
                'Post-Injection Instructions': 'تعليمات حقن العين',
                'Investigation Request': 'طلب فحوصات عيون',
                'Follow-up Reminder': 'تذكير بالمتابعة',
                'Emergency Warning': 'تحذيرات الطوارئ الطبية',
                'Medication Prescription Link': 'إرسال الوصفة الطبية (رابط)',
                'Glasses Prescription Link': 'إرسال مقاس النظارة (رابط)',
                'Comprehensive Visit Report': 'تقرير الزيارة الشامل'
            };
            return arabicTitles[title] || title;
        },

        handleOverrideConsent: async function() {
            const patientId = document.getElementById('waPatientId').value;
            const consentBadge = document.getElementById('waConsentBadge');
            const isRtl = document.getElementById('whatsappModal').getAttribute('dir') === 'rtl';

            try {
                const res = await fetch('/api/whatsapp/consent', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ patient_id: patientId, consent: 1 })
                });
                const data = await res.json();
                if (data.success) {
                    consentBadge.className = 'badge badge-consent-yes';
                    consentBadge.textContent = isRtl ? 'موافق' : 'Consented';
                    document.getElementById('waConsentAlert').classList.add('d-none');
                    if (currentPatient) currentPatient.consent = 1;
                }
            } catch (err) {
                alert(this._isRtl() ? 'تعذّر تحديث حالة الموافقة.' : 'Failed to update consent status: ' + err.message);
            }
        },

        handleSend: async function() {
            const form = document.getElementById('whatsappMessageForm');
            const patientId = document.getElementById('waPatientId').value;
            const appointmentId = document.getElementById('waAppointmentId').value;
            const templateId = document.getElementById('waTemplateId').value;
            const messageBody = document.getElementById('waMessageBody').value;
            const phone = document.getElementById('waPhoneInput').value;
            
            const relatedEye = document.getElementById('waRelatedEye').value;
            const relatedService = document.getElementById('waRelatedService').value;
            const relatedTestType = document.getElementById('waRelatedTestType').value;

            if (!phone || !messageBody) {
                alert(this._isRtl() ? 'رقم الهاتف ونص الرسالة لا يمكن أن يكونا فارغين.' : 'Phone number and message body cannot be empty.');
                return;
            }

            // Show spinner
            document.getElementById('waStatusSpinner').classList.remove('d-none');
            const waSendBtn = document.getElementById('waSendBtn');
            waSendBtn.disabled = true;

            try {
                // Log communication record in database
                await fetch('/api/whatsapp/log', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({
                        patient_id: patientId,
                        appointment_id: appointmentId ? parseInt(appointmentId) : null,
                        template_id: templateId ? parseInt(templateId) : null,
                        message_body: messageBody,
                        phone_number: phone,
                        status: 'opened',
                        related_eye: relatedEye,
                        related_service: relatedService || null,
                        related_test_type: relatedTestType || null
                    })
                });

                // Generate deep link wa.me
                const formattedPhone = formatEgyptPhone(phone);
                const cleanPhone = formattedPhone.replace(/[^0-9]/g, '');
                const encodedMsg = encodeURIComponent(messageBody);
                const waUrl = `https://wa.me/${cleanPhone}?text=${encodedMsg}`;

                // Open WhatsApp in new tab
                window.open(waUrl, '_blank');

                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('whatsappModal'));
                modal.hide();

            } catch (err) {
                console.error(err);
                alert(this._isRtl() ? 'تعذّر تسجيل الرسالة، لكن يمكنك المتابعة وفتح الواتساب يدويًا.' : 'Logging failed but you can still proceed. Open WhatsApp manually.');
            } finally {
                document.getElementById('waStatusSpinner').classList.add('d-none');
                waSendBtn.disabled = false;
            }
        },

        showConfirm: function(message, onConfirm) {
            const confirmModalEl = document.getElementById('whatsappConfirmModal');
            if (!confirmModalEl) {
                if (confirm(message)) {
                    onConfirm();
                }
                return;
            }

            const messageEl = document.getElementById('whatsappConfirmModalMessage');
            if (messageEl) {
                messageEl.textContent = message;
            }

            const modal = new bootstrap.Modal(confirmModalEl);

            const okBtn = document.getElementById('waConfirmOkBtn');
            if (okBtn) {
                okBtn.onclick = function() {
                    modal.hide();
                    onConfirm();
                };
            }

            modal.show();
        },

        // Triggered after Booking creation
        triggerConfirmationModal: function(patientId, appointmentId, patientName, date, time) {
            if (window.WHATSAPP_CONFIG && !window.WHATSAPP_CONFIG.advanced) return;

            const isRtl = document.documentElement.getAttribute('dir') === 'rtl';
            const confirmMsg = isRtl
                ? `تم حجز موعد المريض (${patientName}) بنجاح يوم ${date} في الساعة ${time}.\nهل ترغب في إرسال رسالة تأكيد عبر الواتساب؟`
                : `Booking successfully created for (${patientName}) on ${date} at ${time}.\nWould you like to send a confirmation WhatsApp message?`;

            this.showConfirm(confirmMsg, () => {
                this.openModal(patientId, appointmentId, 'confirmation');
            });
        },

        // Triggered on Appointment Cancellation
        triggerCancellationModal: function(patientId, appointmentId, patientName, date, time) {
            if (window.WHATSAPP_CONFIG && !window.WHATSAPP_CONFIG.advanced) return;

            const isRtl = document.documentElement.getAttribute('dir') === 'rtl';
            const confirmMsg = isRtl
                ? `تم إلغاء موعد المريض (${patientName}) بنجاح.\nهل ترغب في إرسال رسالة إلغاء الموعد للمريض عبر الواتساب؟`
                : `Appointment for (${patientName}) has been successfully cancelled.\nWould you like to send a cancellation WhatsApp message?`;

            this.showConfirm(confirmMsg, () => {
                this.openModal(patientId, appointmentId, 'cancellation');
            });
        },

        // Triggered on Visit completion
        triggerCompletionModal: function(patientId, appointmentId, patientName) {
            if (window.WHATSAPP_CONFIG && !window.WHATSAPP_CONFIG.advanced) return;

            const isRtl = document.documentElement.getAttribute('dir') === 'rtl';
            const confirmMsg = isRtl
                ? `تم إنهاء زيارة المريض (${patientName}) بنجاح.\nهل ترغب في إرسال تقرير الزيارة الشامل (بما في ذلك روابط الوصفات والتعليمات) للمريض عبر الواتساب؟`
                : `Visit for (${patientName}) successfully marked as completed.\nWould you like to send the comprehensive visit report (including prescription links and instructions) to the patient via WhatsApp?`;

            this.showConfirm(confirmMsg, () => {
                this.openModal(patientId, appointmentId, 'report');
            });
        }
    };

    // Expose globally
    window.WhatsAppIntegration = WhatsAppIntegration;

    // Initialize when DOM loaded
    document.addEventListener('DOMContentLoaded', function() {
        WhatsAppIntegration.init();
        
        // Auto trigger completion modal if flagged in sessionStorage
        if (window.APPOINTMENT_CONFIG && window.WhatsAppIntegration) {
            const justCompletedApptId = sessionStorage.getItem('wa_just_completed_appt');
            if (justCompletedApptId && parseInt(justCompletedApptId) === window.APPOINTMENT_CONFIG.appointmentId) {
                sessionStorage.removeItem('wa_just_completed_appt');
                // Allow a small delay for UI to settle
                setTimeout(() => {
                    window.WhatsAppIntegration.triggerCompletionModal(
                        window.APPOINTMENT_CONFIG.patientId,
                        window.APPOINTMENT_CONFIG.appointmentId,
                        window.APPOINTMENT_CONFIG.patientName
                    );
                }, 500);
            }
        }
    });
})();
