<link href="/app/Views/doctor/assets/css/alert_modal.css?v=<?= file_exists(__DIR__ . '/assets/css/alert_modal.css') ? filemtime(__DIR__ . '/assets/css/alert_modal.css') : time() ?>" rel="stylesheet">
<!-- Timepicker CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/timepicker@1.13.18/jquery.timepicker.min.css">

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">
                    <i class="bi bi-bell me-2"></i>Create Alert
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="alertForm">
                    <input type="hidden" id="alertPatientId" name="patient_id">
                    <input type="hidden" id="alertAppointmentId" name="appointment_id">
                    
                    <div class="mb-3">
                        <label for="alertPatientSearch" class="form-label">
                            <i class="bi bi-person me-1"></i>Patient (Optional)
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="alertPatientSearch" 
                                   placeholder="Search patient by name or phone...">
                        </div>
                        <div id="alertPatientSearchResults" class="search-results"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alertMessage" class="form-label">
                            <i class="bi bi-chat-text me-1"></i>Alert Message <span class="text-danger">*</span>
                        </label>
                        <div class="alert-editor-wrapper">
                            <div class="btn-group btn-group-sm mb-2" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="setAlertEditorMode('text')" id="alertEditorTextBtn">
                                    <i class="bi bi-type"></i> Text
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setAlertEditorMode('html')" id="alertEditorHtmlBtn">
                                    <i class="bi bi-code-slash"></i> HTML
                                </button>
                            </div>
                            <textarea class="form-control" id="alertMessage" name="message" rows="3" required placeholder="Enter alert message..."></textarea>
                            <div id="alertMessageHtmlEditor" class="form-control" contenteditable="true" style="display: none; min-height: 100px; max-height: 300px; overflow-y: auto;" placeholder="Enter HTML content..."></div>
                            <small class="text-muted">You can use HTML formatting in alerts</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alertDate" class="form-label">
                                <i class="bi bi-calendar me-1"></i>Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="alertDate" name="alert_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="alertTime" class="form-label">
                                <i class="bi bi-clock me-1"></i>Time <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control timepicker-ui-input" id="alertTime" name="alert_time" required 
                                   placeholder="Click to select time" 
                                   value="12:00 AM">
                            <input type="hidden" id="alertTimeValue" name="alert_time" required>
                            <small class="text-muted">Click to select time in 12-hour format</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alertRepeatCount" class="form-label">
                                <i class="bi bi-arrow-repeat me-1"></i>Repeat Count
                            </label>
                            <input type="number" class="form-control" id="alertRepeatCount" name="repeat_count" min="1" max="100" value="1">
                            <small class="text-muted">Number of times to show this alert (0 = infinite)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="alertRepeatInterval" class="form-label">
                                <i class="bi bi-calendar-week me-1"></i>Repeat Interval (Days)
                            </label>
                            <input type="number" class="form-control" id="alertRepeatInterval" name="repeat_interval" min="0" value="0">
                            <small class="text-muted">Days between each repeat (0 = same day only)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAlert()">
                    <i class="bi bi-check-circle me-1"></i>Create Alert
                </button>
            </div>
        </div>
    </div>
</div>

<style>

</style>

<!-- Alert Modal JS -->
<script src="/app/Views/doctor/assets/js/alert_modal.js?v=<?= file_exists(__DIR__ . '/assets/js/alert_modal.js') ? filemtime(__DIR__ . '/assets/js/alert_modal.js') : time() ?>"></script>
<style>
    .modal-backdrop.show{
        display: none !important;
    }
    body > div.modal-backdrop.fade.show{
        display: none !important;
    }
    .dark .modal-content{
    background: rgba(11, 18, 32, 0.8) !important;
    }
    .modal-content{
    background: rgba(248, 250, 252, 0.8) !important;
    }
</style>