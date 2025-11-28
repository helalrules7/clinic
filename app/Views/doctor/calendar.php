<link href="/app/Views/doctor/assets/css/calendar.css?v=<?= file_exists(__DIR__ . '/assets/css/calendar.css') ? filemtime(__DIR__ . '/assets/css/calendar.css') : time() ?>" rel="stylesheet">

<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3">Calendar</h4>
            <div class="d-flex align-items-center ms-3">
                <label class="form-label mb-0 me-2" for="calendarAutoRefresh">
                    <small class="text-muted">Auto-refresh</small>
                </label>
                <div class="toggle-switch-wrapper">
                    <input type="checkbox" class="toggle-switch" id="calendarAutoRefresh" 
                           onchange="toggleCalendarAutoRefresh(this.checked)">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex gap-2 justify-content-end flex-wrap">
            <button type="button" class="btn btn-info" id="goToDateBtn" 
                    data-bs-toggle="popover" 
                    data-bs-placement="bottom" 
                    data-bs-html="true"
                    data-bs-content="<div class='date-picker-tooltip'><label class='form-label mb-2'>Select Date:</label><input type='date' id='tooltipDatePicker' class='form-control'><button class='btn btn-sm btn-outline-info w-100 mt-2' onclick='goToSelectedDate()'>Go to Date</button></div>"
                    data-bs-trigger="click">
                <i class="bi bi-calendar-event me-1"></i>
                Go to Date
            </button>
            <button type="button" class="btn btn-success" id="addAppointmentBtn">
                <i class="bi bi-plus-circle me-2"></i>
                Add Appointment
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" id="todayBtn">Today</button>
                <button type="button" class="btn btn-outline-primary" id="prevDayBtn">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="nextDayBtn">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <!-- Desktop Filters -->
        <div class="d-none d-md-flex gap-2 flex-wrap align-items-center">
            <span class="text-muted me-2"><i class="bi bi-funnel me-1"></i>Filter Times:</span>
            <button type="button" class="btn btn-sm btn-outline-info filter-time-btn" data-filter="2pm-6pm" id="filter2pm6pm">
                <i class="bi bi-clock me-1"></i>
                2:00 PM - 6:00 PM
            </button>
            <button type="button" class="btn btn-sm btn-outline-success filter-time-btn" data-filter="6pm-1045pm" id="filter6pm1045pm">
                <i class="bi bi-clock me-1"></i>
                6:00 PM - 10:45 PM
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary filter-time-btn" data-filter="available" id="filterAvailable">
                <i class="bi bi-check-circle me-1"></i>
                Available Only
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning filter-time-btn" data-filter="unavailable" id="filterUnavailable">
                <i class="bi bi-x-circle me-1"></i>
                Appointments Only
            </button>
            <button type="button" class="btn btn-sm btn-secondary filter-time-btn" data-filter="none" id="filterNone">
                <i class="bi bi-x-lg me-1"></i>
                Clear Filters
            </button>
        </div>
        
        <!-- Mobile Filter Button -->
        <div class="d-md-none">
            <button type="button" class="btn btn-sm btn-outline-primary w-100" id="mobileFilterBtn" 
                    data-bs-toggle="popover" 
                    data-bs-placement="bottom" 
                    data-bs-html="true"
                    data-bs-trigger="click"
                    data-bs-content="">
                <i class="bi bi-funnel me-2"></i>
                Filter Times
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="currentDateDisplay">
                    <?= date('l, F j, Y') ?>
                </h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2" id="statusIndicator">
                        <i class="bi bi-circle-fill me-1"></i>
                        Live
                    </span>
                    <small class="text-muted" id="lastUpdate">
                        Last updated: <?= date('H:i:s') ?>
                    </small>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="calendarContainer">
                    <!-- Calendar will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="startVisitBtn" style="display: none;">
                    <i class="bi bi-play-circle me-2"></i>
                    Start Visit
                </button>
                <button type="button" class="btn btn-success" id="completeVisitBtn" style="display: none;">
                    <i class="bi bi-check-circle me-2"></i>
                    Complete
                </button>
                <button type="button" class="btn btn-warning" id="rescheduleBtn" style="display: none;">
                    <i class="bi bi-calendar-event me-2"></i>
                    Reschedule
                </button>
                <button type="button" class="btn btn-danger" id="cancelBtn" style="display: none;">
                    <i class="bi bi-x-circle me-2"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cancelForm">
                    <div class="mb-3">
                        <label for="cancellationReason" class="form-label">Cancellation Reason *</label>
                        <textarea class="form-control" id="cancellationReason" name="cancellation_reason" 
                                  rows="3" required placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                    <i class="bi bi-x-circle me-2"></i>
                    Confirm Cancellation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Appointment Modal -->
<div class="modal fade" id="deleteAppointmentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Delete Appointment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="bi bi-shield-exclamation fs-3 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Warning!</h6>
                        <p class="mb-0">You are about to delete this appointment permanently. This action <strong>cannot be undone</strong>.</p>
                    </div>
                </div>
                
                <div class="appointment-delete-info mb-4">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-calendar-event me-2"></i>
                        Appointment Details:
                    </h6>
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1" id="deleteAppointmentPatientName"></h6>
                                    <small class="text-muted">Time: <span id="deleteAppointmentTime"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="deletion-consequences">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-list-check me-2"></i>
                        The following data will be deleted permanently:
                    </h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-calendar-event text-danger me-2"></i>
                            <span>Appointment details</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-medical text-danger me-2"></i>
                            <span>Consultation notes and prescriptions</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-receipt text-danger me-2"></i>
                            <span>Associated payments</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-earmark text-danger me-2"></i>
                            <span>Lab tests and results</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAppointmentBtn">
                    <i class="bi bi-trash me-1"></i>
                    <span class="btn-text">Delete Appointment</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAppointmentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patientSearch" class="form-label">
                                    Patient * 
                                    <span id="preselectedLabel" class="badge bg-info ms-2" style="display: none;">Pre-selected</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="patientSearch" 
                                           placeholder="Search patient by name or phone..." required>
                                    <button type="button" class="btn btn-outline-primary" id="newPatientBtn">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="selectedPatientId" name="patient_id">
                                <div id="patientSearchResults" class="search-results"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentDate" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="appointmentDate" name="date" 
                                       min="<?= date('Y-m-d') ?>" required>
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Cannot select a date before today (Local timezone: Egypt)
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentTime" class="form-label">Time *</label>
                                <select class="form-select" id="appointmentTime" name="start_time" required>
                                    <option value="">Select time slot...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="visitType" class="form-label">Visit Type *</label>
                                <select class="form-select" id="visitType" name="visit_type" required>
                                    <option value="">Select visit type...</option>
                                    <option value="New">New Patient</option>
                                    <option value="FollowUp">Follow Up</option>
                                    <option value="Procedure">Procedure</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentSource" class="form-label">Source</label>
                                <select class="form-select" id="appointmentSource" name="source">
                                    <option value="Walk-in">Walk-in</option>
                                    <option value="Phone">Phone</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="appointmentNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="appointmentNotes" name="notes" 
                                          rows="3" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveAppointmentBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        Save Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>
                    Add New Patient
                </h5>
                <div class="keyboard-hint">
                    <span>Press</span>
                    <kbd>Esc</kbd>
                    <span>to close</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="addPatientMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-1"></i>
                                Basic Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="age" class="form-label">Age (Years)</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" placeholder="Enter age in years">
                                <div class="form-text">Alternative: Enter age to automatically calculate date of birth</div>
                            </div>

                            <div class="mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob">
                                <div class="form-text">Patient's date of birth (if empty, today's date will be used)</div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-telephone me-1"></i>
                                Contact Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required maxlength="20">
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Primary contact number</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" maxlength="500"></textarea>
                                <div class="form-text">Home address (optional)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="Male" selected>Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="form-text text-danger"><strong>Required:</strong> Change the gender if needed</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="addPatientSubmit" title="Save patient - Press 'Ctrl+S'">
                        <i class="bi bi-person-plus me-1"></i>
                        <span class="btn-text">Add Patient</span>
                        <small class="ms-2 text-white-50">
                            <kbd style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); font-size: 0.7rem;">Ctrl+S</kbd>
                        </small>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize CALENDAR_CONFIG with PHP variables
<?php
date_default_timezone_set('Africa/Cairo');
$serverDate = date('Y-m-d');
$serverDateTime = date('Y-m-d H:i:s');
$serverTimestamp = time();
?>

window.CALENDAR_CONFIG = {
    serverDate: '<?= $serverDate ?>',
    serverDateTime: '<?= $serverDateTime ?>',
    serverTimestamp: <?= $serverTimestamp ?>,
    doctorId: <?= $doctorId ?>,
    preselectedPatient: <?= $preselectedPatient ? json_encode($preselectedPatient) : 'null' ?>
};
</script>
<script src="/app/Views/doctor/assets/js/calendar.js?v=<?= file_exists(__DIR__ . '/assets/js/calendar.js') ? filemtime(__DIR__ . '/assets/js/calendar.js') : time() ?>"></script>