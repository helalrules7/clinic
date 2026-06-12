<link href="/app/Views/doctor/assets/css/appointment.css?v=<?= file_exists(__DIR__ . '/assets/css/appointment.css') ? filemtime(__DIR__ . '/assets/css/appointment.css') : time() ?>" rel="stylesheet">
<?php
$__db = \App\Config\Database::getInstance()->getConnection();
$__waSettingsStmt = $__db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key = 'whatsapp_enabled'");
$__waSettingsStmt->execute();
$__waEnabled = (bool)($__waSettingsStmt->fetchColumn() ?? false);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <nav class="app-breadcrumb" aria-label="Breadcrumb">
        <a href="/doctor/patients/<?= (int)($appointment['patient_id'] ?? 0) ?>" class="app-crumb-back" aria-label="Back to patient">
            <i class="bi bi-arrow-left"></i>
        </a>
        <a href="/doctor/patients" class="app-crumb-link">Patients</a>
        <i class="bi bi-chevron-right app-crumb-sep" aria-hidden="true"></i>
        <a href="/doctor/patients/<?= (int)($appointment['patient_id'] ?? 0) ?>" class="app-crumb-link patient-name-link" data-patient-id="<?= (int)($appointment['patient_id'] ?? 0) ?>"><?= htmlspecialchars($appointment['patient_name'] ?? '') ?></a>
        <i class="bi bi-chevron-right app-crumb-sep" aria-hidden="true"></i>
        <span class="app-crumb-current">Appointment #<?= (int)$appointment['id'] ?></span>
    </nav>
    <div class="d-flex gap-2">
        <a href="/doctor/patients/<?= $appointment['patient_id'] ?? '' ?>" class="btn btn-outline-secondary hide-pa-on-mobile">
            <i class="bi bi-person"></i> Patient Profile
        </a>
        <a href="/doctor/appointments/<?= $appointment['id'] ?? '' ?>/edit" class="btn btn-primary hide-edit-on-mobile">
            <i class="bi bi-pencil-square"></i> Edit Consultation
        </a>
    </div>
</div>

<!-- Doctor Info Badge (showing appointment doctor info) -->
<?php 
$appointmentDoctorName = $appointment['doctor_name'] ?? 'Unknown Doctor';
?>

<!-- Appointment Header -->
<?php
$headerClass = '';
$status = strtolower($appointment['status'] ?? '');
$appointmentDate = $appointment['date'] ?? '';
$today = date('Y-m-d');

// Check if appointment is missed (Booked status and date is in the past)
$isMissed = ($status === 'booked' && $appointmentDate < $today);

if ($status === 'completed') {
    $headerClass = 'completed';
} elseif ($isMissed || $status === 'cancelled' || $status === 'noshow') {
    $headerClass = 'missed';
} elseif ($appointment['status'] === 'Closed' || $appointment['status'] === 'Rescheduled') {
    $headerClass = ($appointment['status'] === 'Closed' ? 'closed' : 'rescheduled');
}
?>
<div class="appointment-header <?= $headerClass ?>">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-calendar-event me-2"></i>
                Appointment #<?= $appointment['id'] ?>
                <span class="badge bg-light text-dark ms-2 fs-6 shadow-sm d-inline-flex align-items-center">
                    <?php 
                    $appointmentDoctorImage = $appointment['doctor_profile_image'] ?? null;
                    if (!empty($appointmentDoctorImage)): 
                        $doctorImagePath = strpos($appointmentDoctorImage, '/public/') === 0 ? $appointmentDoctorImage : '/public' . $appointmentDoctorImage;
                    ?>
                        <img src="<?= htmlspecialchars($doctorImagePath) ?>" 
                             alt="<?= htmlspecialchars($appointmentDoctorName) ?>" 
                             class="appointment-doctor-avatar me-2"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="appointment-doctor-avatar-fallback me-2" style="display: none;">
                            <?= strtoupper(substr($appointmentDoctorName ?? 'D', 0, 1)) ?>
                        </div>
                    <?php else: ?>
                        <div class="appointment-doctor-avatar me-2">
                            <?= strtoupper(substr($appointmentDoctorName ?? 'D', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <i class="bi bi-person-check me-1"></i>
                    <?= htmlspecialchars($appointmentDoctorName) ?>'s Patient
                </span>
                <?php if (!empty($followupAppointment)): ?>
                <a href="/doctor/appointments/<?= $followupAppointment['id'] ?>" 
                   class="badge bg-success ms-2 fs-6 shadow-sm d-inline-flex align-items-center text-white text-decoration-none" 
                   id="followupBadge"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="top" 
                   data-bs-title="Go to follow-up appointment scheduled for <?= date('M j, Y \a\t g:i A', strtotime($followupAppointment['date'] . ' ' . $followupAppointment['start_time'])) ?>">
                    <i class="bi bi-calendar-check me-1"></i>
                    Follow-up Scheduled
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <?php endif; ?>
                <?php if (!empty($originalAppointment)): ?>
                <a href="/doctor/appointments/<?= $originalAppointment['id'] ?>" 
                   class="badge bg-info ms-2 fs-6 shadow-sm d-inline-flex align-items-center text-white text-decoration-none" 
                   id="originalAppointmentBadge"
                   data-bs-toggle="tooltip" 
                   data-bs-placement="top" 
                   data-bs-title="Go to original appointment from <?= date('M j, Y \a\t g:i A', strtotime($originalAppointment['date'] . ' ' . $originalAppointment['start_time'])) ?>">
                    <i class="bi bi-calendar-event me-1"></i>
                    Original Appointment
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <?php endif; ?>
            </h2>
            <p class="mb-2">
                <i class="bi bi-person me-2"></i>
                <strong><a href="/doctor/patients/<?= $patient['id'] ?? '' ?>" class="patient-name-link patient-hover-name" data-patient-id="<?= (int)($patient['id'] ?? 0) ?>" style="color: white; font-weight: 600; text-decoration: none;"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></a></strong>
                (ID: #<?= $patient['id'] ?>)
            </p>
            <p class="mb-0">
                <a href="/doctor/calendar?date=<?= $appointment['date'] ?>&appointment_id=<?= $appointment['id'] ?>"
                   style="color: white; text-decoration: none; cursor: pointer; font-weight: 600;"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   data-bs-title="Click to view calendar for this date">
                    <i class="bi bi-clock me-2"></i>
                    <?= date('l, M j, Y \a\t g:i A', strtotime($appointment['date'] . ' ' . $appointment['start_time'])) ?>
                </a>
            </p>
            <?php if (!empty($appointment['clinic_name_ar']) || !empty($appointment['clinic_name_en'])): ?>
            <p class="mb-0 mt-1">
                <i class="bi bi-buildings me-2"></i>
                <strong><?= htmlspecialchars($appointment['clinic_name_ar'] ?: $appointment['clinic_name_en']) ?></strong>
            </p>
            <?php endif; ?>
            <div class="appt-tags-bar mt-2 d-flex flex-wrap align-items-center gap-2">
                <span class="small opacity-75"><i class="bi bi-lightning me-1"></i>Session:</span>
                <div id="apptSessionLabels" class="d-flex flex-wrap align-items-center gap-1"></div>
            </div>
            <div class="appt-tags-bar mt-1 d-flex flex-wrap align-items-center gap-2">
                <span class="small opacity-75"><i class="bi bi-bookmark me-1"></i>Appt tags:</span>
                <div id="apptPersistentTags" class="d-flex flex-wrap align-items-center gap-1"></div>
                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="apptManagePatientTagsBtn" style="font-size:.75rem" title="Manage patient tags">
                    <i class="bi bi-person-badge me-1"></i>Patient Tags
                </button>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex flex-column align-items-end gap-2">
                <?php
                // Determine display status and badge class
                $displayStatus = $appointment['status'];
                $badgeClass = 'bg-primary';
                
                if ($isMissed) {
                    $displayStatus = 'Missed';
                    $badgeClass = 'bg-danger text-white';
                } else {
                    // Map status to badge class
                    $statusBadgeMap = [
                        'Booked' => 'bg-primary',
                        'CheckedIn' => 'bg-success',
                        'InProgress' => 'bg-warning',
                        'Completed' => 'bg-info',
                        'Cancelled' => 'bg-danger',
                        'NoShow' => 'bg-secondary',
                        'Rescheduled' => 'bg-info',
                        'Closed' => 'bg-danger'
                    ];
                    $badgeClass = $statusBadgeMap[$appointment['status']] ?? 'bg-secondary';
                }
                
                // Get icon
                $statusIconMap = [
                    'Booked' => 'bi-calendar-check',
                    'CheckedIn' => 'bi-check-circle-fill',
                    'InProgress' => 'bi-hourglass-split',
                    'Completed' => 'bi-check2-all',
                    'Cancelled' => 'bi-x-circle-fill',
                    'NoShow' => 'bi-clock-fill',
                    'Rescheduled' => 'bi-arrow-clockwise',
                    'Closed' => 'bi-lock-fill',
                    'Missed' => 'bi-exclamation-triangle-fill'
                ];
                $statusIcon = $statusIconMap[$displayStatus] ?? 'bi-question-circle';
                ?>
                <span class="status-badge d-flex align-items-center gap-2 <?= $badgeClass ?>" 
                      id="appointmentStatusBadge" 
                      onclick="showChangeStatusModal(<?= $appointment['id'] ?>)"
                      style="cursor: pointer;" 
                      data-bs-toggle="tooltip" 
                      data-bs-placement="top" 
                      data-bs-title="Click to change status">
                    <i class="bi <?= $statusIcon ?>" id="statusIcon"></i>
                    <span id="statusText"><?= $displayStatus ?></span>
                </span>
                <?php if ($appointment['status'] !== 'Completed'): ?>
                <button class="btn btn-light btn-sm" id="markCompletedBtn" onclick="markAsCompleted(<?= $appointment['id'] ?>)">
                    <i class="bi bi-check-circle me-1"></i> Mark as Completed
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="action-buttons-group" role="group">
            <button type="button" class="btn btn-action-edit hide-on-mobile" onclick="editConsultation(<?= $appointment['id'] ?>)">
                <i class="bi bi-pencil me-1"></i>Edit Consultation
            </button>
            <button type="button" class="btn btn-action-report hide-on-mobile" onclick="printReport(<?= $appointment['id'] ?>)">
                <i class="bi bi-printer me-1"></i>Print Report
            </button>
            <button type="button" class="btn btn-action-followup hide-on-mobile" 
                    id="rescheduleFollowupBtn"
                    onclick="rescheduleFollowupAppointment(<?= $appointment['id'] ?>)"
                    <?= !empty($followupAppointment) ? 'disabled title="Follow-up appointment already scheduled"' : '' ?>>
                <i class="bi bi-calendar-check me-1"></i>Schedule Followup
            </button>
            <button type="button" class="btn btn-action-reschedule hide-on-mobile" 
                    onclick="rescheduleAppointment(<?= $appointment['id'] ?>)"
                    <?= $appointment['status'] === 'Completed' ? 'disabled title="Cannot reschedule completed appointments"' : '' ?>>
                <i class="bi bi-calendar-plus me-1"></i>Reschedule
            </button>
            <button type="button" class="btn btn-action-alert hide-on-mobile" onclick="openAlertModal(<?= $appointment['patient_id'] ?? 'null' ?>, <?= $appointment['id'] ?>)">
                <i class="bi bi-bell me-1"></i>Set Alert
            </button>
            <button type="button" class="btn btn-action-history hide-on-mobile" 
                    id="appointmentHistoryBtn" 
                    data-patient-id="<?= $appointment['patient_id'] ?? 'null' ?>"
                    data-appointment-id="<?= $appointment['id'] ?>">
                <i class="bi bi-clock-history me-1"></i>Appointment History
            </button>
            <button type="button" class="btn btn-action-dashboard hide-on-mobile" 
                    onclick="showUnifiedClinicalDashboardPopover(<?= $patient['id'] ?>)"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="bottom" 
                    data-bs-title="View unified clinical dashboard for this patient">
                <i class="bi bi-clipboard-pulse me-1"></i>Clinical Dashboard
            </button>
            <?php if (!empty($medications)): ?>
            <button type="button" class="btn btn-action-prescription hide-on-mobile" onclick="printPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-printer me-1"></i>Print Prescription
            </button>
            <?php endif; ?>
            <?php if (!empty($glasses)): ?>
            <button type="button" class="btn btn-action-glasses hide-on-mobile" onclick="printGlassesPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-eyeglasses me-1"></i>Print Glasses
            </button>
            <?php endif; ?>
            <?php if (!empty($labTests)): ?>
            <button type="button" class="btn btn-action-lab hide-on-mobile" onclick="printLabTests(<?= $appointment['id'] ?>)">
                <i class="bi bi-clipboard-data me-1"></i>Print Lab Tests
            </button>
            <?php endif; ?>
            <?php if ($__waEnabled): ?>
            <button type="button" class="btn btn-success hide-on-mobile d-inline-flex align-items-center gap-1" onclick="WhatsAppIntegration.openModal(<?= $appointment['patient_id'] ?>, <?= $appointment['id'] ?>, 'report')" style="background-color: #25D366; border-color: #25D366; color: #fff;">
                <i class="bi bi-whatsapp"></i>Send WhatsApp
            </button>
            <?php endif; ?>
            
            <!-- More Actions Button (Three Dots) - Bootstrap Popover -->
            <button class="btn btn-secondary hide-on-mobile" 
                    type="button" 
                    id="moreActionsBtn" 
                    data-bs-toggle="popover" 
                    data-bs-placement="bottom"
                    data-bs-trigger="click"
                    data-bs-html="true"
                    data-bs-content="<div class='more-actions-popover'>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); editConsultation(<?= $appointment['id'] ?>);'>
                            <i class='bi bi-pencil me-2'></i>Edit Consultation
                        </div>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); printReport(<?= $appointment['id'] ?>);'>
                            <i class='bi bi-printer me-2'></i>Print Report
                        </div>
                        <div class='more-actions-popover-item <?= $appointment['status'] === 'Completed' ? 'disabled' : '' ?>' 
                             <?php if ($appointment['status'] !== 'Completed'): ?>
                             onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); rescheduleAppointment(<?= $appointment['id'] ?>);'
                             <?php else: ?>
                             onclick='return false;' title='Cannot reschedule completed appointments'
                             <?php endif; ?>>
                            <i class='bi bi-calendar-plus me-2'></i>Reschedule
                        </div>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); openAlertModal(<?= $appointment['patient_id'] ?? 'null' ?>, <?= $appointment['id'] ?>);'>
                            <i class='bi bi-bell me-2'></i>Set Alert
                        </div>
                        <?php if (!empty($medications)): ?>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); printPrescription(<?= $appointment['id'] ?>);'>
                            <i class='bi bi-printer me-2'></i>Print Prescription
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($glasses)): ?>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); printGlassesPrescription(<?= $appointment['id'] ?>);'>
                            <i class='bi bi-eyeglasses me-2'></i>Print Glasses
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($labTests)): ?>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); printLabTests(<?= $appointment['id'] ?>);'>
                            <i class='bi bi-clipboard-data me-2'></i>Print Lab Tests
                        </div>
                        <?php endif; ?>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); viewPatient(<?= $patient['id'] ?>);'>
                            <i class='bi bi-person me-2'></i>View Patient Profile
                        </div>
                        <div class='more-actions-popover-item' onclick='bootstrap.Popover.getInstance(document.getElementById(&quot;moreActionsBtn&quot;)).hide(); showUnifiedClinicalDashboardPopover(<?= $patient['id'] ?>);'>
                            <i class='bi bi-clipboard-pulse me-2'></i>Clinical Dashboard
                        </div>

                    </div>">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            
            <!-- More Actions Dropdown for Mobile -->
            <div class="dropdown more-actions-btn">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="moreActionsDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical me-1"></i>Appointment Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="moreActionsDropdown">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { editConsultation(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-pencil me-2"></i>Edit Consultation
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-success fw-bold <?= !empty($followupAppointment) ? 'disabled text-muted' : '' ?>" 
                           href="javascript:void(0);" 
                           <?php if (empty($followupAppointment)): ?>
                           onclick="closeDropdownAndExecute('moreActionsDropdown', function() { rescheduleFollowupAppointment(<?= $appointment['id'] ?>); });"
                           <?php else: ?>
                           onclick="return false;" title="Follow-up appointment already scheduled"
                           <?php endif; ?>>
                            <i class="bi bi-calendar-check me-2"></i>Schedule Followup
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger fw-bold <?= $appointment['status'] === 'Completed' ? 'disabled text-muted' : '' ?>" 
                           href="javascript:void(0);" 
                           <?php if ($appointment['status'] !== 'Completed'): ?>
                           onclick="closeDropdownAndExecute('moreActionsDropdown', function() { rescheduleAppointment(<?= $appointment['id'] ?>); });"
                           <?php else: ?>
                           onclick="return false;" title="Cannot reschedule completed appointments"
                           <?php endif; ?>>
                            <i class="bi bi-calendar-plus me-2"></i>Reschedule
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { openAlertModal(<?= $appointment['patient_id'] ?? 'null' ?>, <?= $appointment['id'] ?>); });">
                            <i class="bi bi-bell me-2"></i>Set Alert
                        </a>
                    </li>
                    <?php if (!empty($medications)): ?>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { printPrescription(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-printer me-2"></i>Print Prescription
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($glasses)): ?>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { printGlassesPrescription(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-eyeglasses me-2"></i>Print Glasses
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($labTests)): ?>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { printLabTests(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-clipboard-data me-2"></i>Print Lab Tests
                        </a>
                    </li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { viewPatient(<?= $patient['id'] ?>); });">
                            <i class="bi bi-person me-2"></i>View Patient Profile
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column - Patient & Consultation -->
    <div class="col-lg-8">
        
        <!-- Patient Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge me-2"></i>
                    Patient Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <span class="patient-hover-name" data-patient-id="<?= (int)($patient['id'] ?? 0) ?>"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></span></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></p>
                        <p><strong>Gender:</strong> <?= ucfirst($patient['gender'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Age:</strong> 
                            <?php if ($patient['dob']): ?>
                                <?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> years
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($patient['address'] ?? 'N/A') ?></p>
                        <p style="display: none;"><strong>National ID:</strong> <?= htmlspecialchars($patient['national_id'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consultation Notes -->
        <?php if (!empty($consultationNotes)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-pulse me-2"></i>
                        Consultation Notes
                        <span class="badge bg-primary ms-2"><?= count($consultationNotes) ?> Note<?= count($consultationNotes) > 1 ? 's' : '' ?></span>
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-draw-consultation" type="button" onclick="DrawConsultation && DrawConsultation.openForAppointment(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)" title="Open the drawing canvas">
                            <i class="bi bi-pencil-square me-1"></i>Draw Consultation
                        </button>
                        <button class="btn btn-outline-primary" onclick="editConsultation(<?= $appointment['id'] ?>)">
                            <i class="bi bi-pencil me-1"></i>Edit Latest
                        </button>
                        <button class="btn btn-outline-success" onclick="window.location.href='/doctor/appointments/<?= $appointment['id'] ?>/edit/new'">
                            <i class="bi bi-plus me-1"></i>Add New
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php foreach ($consultationNotes as $index => $note): ?>
                    <div class="consultation-section <?= $index > 0 ? 'border-top pt-4 mt-4' : '' ?>">
                        
                        <!-- Note Header -->
                        <div class="consultation-note-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?= $index === 0 ? 'success' : 'secondary' ?> note-badge me-2">
                                        <?= $index === 0 ? 'Latest' : 'Note #' . (count($consultationNotes) - $index) ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= date('M j, Y \a\t g:i A', strtotime($note['created_at'])) ?>
                                    </small>
                                    <?php if (!empty($note['diagnosis'])): ?>
                                    <span class="badge bg-info ms-2" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars(substr($note['diagnosis'], 0, 30)) ?><?= strlen($note['diagnosis']) > 30 ? '...' : '' ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="window.location.href='/doctor/appointments/<?= $appointment['id'] ?>/edit?note_id=<?= $note['id'] ?>'"
                                            title="Edit this note">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($index > 0): ?>
                                    <button class="btn btn-outline-info btn-sm" 
                                            onclick="toggleNoteDetails('note-<?= $note['id'] ?>')"
                                            title="Show details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="deleteConsultationNote(<?= (int) $note['id'] ?>, <?= htmlspecialchars(json_encode($note['chief_complaint'] ?? 'Consultation Note'), ENT_QUOTES) ?>)"
                                            title="Delete this note">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Note Content (Collapsible for older notes) -->
                        <div id="note-<?= $note['id'] ?>" class="<?= $index > 0 ? 'collapse' : '' ?>">
                        
                        <!-- Chief Complaint -->
                        <?php if (!empty($note['chief_complaint'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">*Chief Complaint (Required)</h6>
                            <p><?= htmlspecialchars($note['chief_complaint']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Vital Signs -->
                        <?php if (!empty($note['vital_signs'])): ?>
                        <div class="mb-3">
                            <h6 class="text-success">Vital Signs</h6>
                            <?php 
                            $vitals = json_decode($note['vital_signs'], true);
                            if ($vitals): ?>
                                <div class="row">
                                    <?php foreach ($vitals as $vital => $value): ?>
                                    <div class="col-md-3 mb-2">
                                        <div class="vital-sign">
                                            <div class="vital-value"><?= htmlspecialchars($value) ?></div>
                                            <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $vital)) ?></small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p><?= htmlspecialchars($note['vital_signs']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- History of Present Illness -->
                        <?php if (!empty($note['hx_present_illness'])): ?>
                        <div class="mb-3">
                            <h6 class="text-info">History of Present Illness</h6>
                            <p><?= nl2br(htmlspecialchars($note['hx_present_illness'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Systemic Disease -->
                        <?php if (!empty($note['systemic_disease'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Systemic Disease</h6>
                            <p class="ms-3"><?= nl2br(htmlspecialchars($note['systemic_disease'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Medication -->
                        <?php if (!empty($note['medication'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">Current Medication</h6>
                            <p class="ms-3"><?= nl2br(htmlspecialchars($note['medication'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Separator -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="border-top border-2 border-primary opacity-25 my-3"></div>
                                <div class="text-center">
                                    <span class="badge bg-primary text-white px-3 py-2">
                                        <i class="bi bi-eye me-1"></i>
                                        Eye Examination
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Visual Acuity -->
                        <?php if (!empty($note['visual_acuity_right']) || !empty($note['visual_acuity_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">Visual Acuity</h6>
                            <div class="row">
                                <?php if (!empty($note['visual_acuity_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-success border-3 ps-3"><?= htmlspecialchars($note['visual_acuity_right']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                <?php if (!empty($note['visual_acuity_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                </div>
                                    <p class="ms-4 border-start border-info border-3 ps-3"><?= htmlspecialchars($note['visual_acuity_left']) ?></p>
                            </div>
                                <?php endif; ?>
                        </div>
                        </div>
                        <?php endif; ?>

                        <!-- Refraction -->
                        <?php if (!empty($note['refraction_right']) || !empty($note['refraction_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-info">Refraction</h6>
                            <div class="row">
                                <?php if (!empty($note['refraction_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-success border-3 ps-3"><?= htmlspecialchars($note['refraction_right']) ?></p>
                                </div>
                            <?php endif; ?>
                                <?php if (!empty($note['refraction_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-info border-3 ps-3"><?= htmlspecialchars($note['refraction_left']) ?></p>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- IOP -->
                        <?php if (!empty($note['IOP_right']) || !empty($note['IOP_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-warning">Intraocular Pressure (IOP)</h6>
                            <div class="row">
                                <?php if (!empty($note['IOP_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-success border-3 ps-3"><?= htmlspecialchars($note['IOP_right']) ?> mmHg</p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['IOP_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-info border-3 ps-3"><?= htmlspecialchars($note['IOP_left']) ?> mmHg</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Slit Lamp Examination -->
                        <?php if (!empty($note['slit_lamp_right']) || !empty($note['slit_lamp_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-success">Slit Lamp Examination</h6>
                            <div class="row">
                                <?php if (!empty($note['slit_lamp_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['slit_lamp_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['slit_lamp_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['slit_lamp_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Fundus Examination -->
                        <?php if (!empty($note['fundus_right']) || !empty($note['fundus_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Fundus Examination</h6>
                            <div class="row">
                                <?php if (!empty($note['fundus_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['fundus_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['fundus_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['fundus_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- External Appearance -->
                        <?php if (!empty($note['external_appearance_right']) || !empty($note['external_appearance_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-warning">External Appearance</h6>
                            <div class="row">
                                <?php if (!empty($note['external_appearance_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['external_appearance_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['external_appearance_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['external_appearance_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Eyelid -->
                        <?php if (!empty($note['eyelid_right']) || !empty($note['eyelid_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-secondary">Eyelid</h6>
                            <div class="row">
                                <?php if (!empty($note['eyelid_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['eyelid_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['eyelid_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['eyelid_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Separator -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="border-top border-2 border-success opacity-25 my-3"></div>
                                <div class="text-center">
                                    <span class="badge bg-success text-white px-3 py-2">
                                        <i class="bi bi-clipboard2-pulse me-1"></i>
                                        Treatment Plan
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnosis -->
                        <?php if (!empty($note['diagnosis'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger d-flex align-items-center gap-2">
                                <span>Diagnosis (Required)</span>
                                <?php if ($__waEnabled): ?>
                                <button type="button" class="btn btn-link text-success p-0 m-0 border-0 lh-1" onclick="WhatsAppIntegration.openModal(<?= $patient['id'] ?>, <?= $appointment['id'] ?>, 'report')" title="Send Diagnosis/Visit Info via WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                                <?php endif; ?>
                            </h6>
                            <p><?= htmlspecialchars($note['diagnosis']) ?>
                            <?php if (!empty($note['diagnosis_code'])): ?>
                                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($note['diagnosis_code']) ?></span>
                            <?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Plan -->
                        <?php if (!empty($note['plan'])): ?>
                        <div class="mb-3">
                            <h6 class="text-secondary d-flex align-items-center gap-2">
                                <span>Treatment Plan</span>
                                <?php if ($__waEnabled): ?>
                                <button type="button" class="btn btn-link text-success p-0 m-0 border-0 lh-1" onclick="WhatsAppIntegration.openModal(<?= $patient['id'] ?>, <?= $appointment['id'] ?>, 'report')" title="Send Plan/Instructions via WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                                <?php endif; ?>
                            </h6>
                            <p><?= nl2br(htmlspecialchars($note['plan'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Follow-up -->
                        <?php if (!empty($note['followup_days'])): ?>
                        <div class="mb-3">
                            <h6 class="text-warning">Follow-up</h6>
                            <p><i class="bi bi-calendar-check me-1"></i>Next appointment in <?= htmlspecialchars($note['followup_days']) ?> days</p>
                        </div>
                        <?php endif; ?>

                        </div> <!-- End collapsible content -->
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-pulse me-2"></i>
                    Consultation Notes
                </h5>
            </div>
            <div class="card-body text-center">
                <i class="bi bi-clipboard-pulse text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No consultation notes recorded</p>
                <div class="d-inline-flex gap-2 mt-3 flex-wrap justify-content-center">
                    <button class="btn btn-outline-primary" onclick="addConsultationNotes(<?= $appointment['id'] ?>)">
                        <i class="bi bi-plus me-2"></i>Add Consultation Notes
                    </button>
                    <button class="btn btn-draw-consultation" type="button" onclick="DrawConsultation && DrawConsultation.openForAppointment(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)" title="Open the drawing canvas">
                        <i class="bi bi-pencil-square me-2"></i>Draw Consultation
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right Column - Prescriptions & Actions -->
    <div class="col-lg-4">
        
        <!-- Medication Prescriptions -->
        <div class="card mb-4" id="medicationsCard">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-capsule me-2"></i>
                        Medications
                    </h5>
                    <?php 
                    $latestNote = !empty($consultationNotes) ? $consultationNotes[0] : null;
                    ?>
                    <div class="btn-group btn-group-sm" role="group" id="medicationsBtnGroup">
                        <?php if (!empty($medications)): ?>
                        <button class="btn btn-sm btn-warning" onclick="printPrescription(<?= $appointment['id'] ?>)" title="Print Prescription">
                            <i class="bi bi-printer"></i>
                        </button>
                        <?php endif; ?>
                        <?php if (!empty($medications) && $__waEnabled): ?>
                        <button class="btn btn-sm btn-success" onclick="WhatsAppIntegration.openModal(<?= $appointment['patient_id'] ?>, <?= $appointment['id'] ?>, 'eye_drops')" title="Send Prescription / Eye Drops Schedule via WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-info" id="suggestMedicationsBtn" onclick="showPrescriptionSuggestions(<?= (int) $appointment['id'] ?>, <?= htmlspecialchars(json_encode($latestNote['diagnosis'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($latestNote['chief_complaint'] ?? ''), ENT_QUOTES) ?>)" title="Suggest medications from similar cases or your most-used drugs">
                            <i class="bi bi-lightbulb me-1"></i>Suggest
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="addPrescription(<?= $appointment['id'] ?>)">
                            <i class="bi bi-plus me-1"></i>Add Medication
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body" id="medicationsContainer">
                <?php if (!empty($medications)): ?>
                    <?php foreach ($medications as $med): ?>
                    <div class="prescription-card p-3 mb-3" data-medication-id="<?= $med['id'] ?>" data-drug-name="<?= htmlspecialchars($med['drug_name']) ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="text-primary mb-0" onclick="showDrugPopoverFromName(<?= htmlspecialchars(json_encode($med['drug_name']), ENT_QUOTES) ?>, event)" style="cursor: pointer;"><?= htmlspecialchars($med['drug_name']) ?></h6>
                                <span class="drug-price-badge badge bg-success text-white" data-drug-name="<?= htmlspecialchars($med['drug_name']) ?>" style="display: none;">
                                    <i class="bi bi-currency-exchange me-1"></i>
                                    <span class="drug-price-value">Loading...</span>
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="event.stopPropagation(); editMedication(<?= (int) $med['id'] ?>, <?= htmlspecialchars(json_encode($med['drug_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($med['notes'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($med['dose'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($med['frequency'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($med['duration'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($med['route'] ?? ''), ENT_QUOTES) ?>)" title="Edit Medication">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="event.stopPropagation(); deleteMedication(<?= $med['id'] ?>)" title="Delete Medication">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php
                            $rxMeta = [];
                            if (!empty($med['dose'])) $rxMeta[] = ['bi-capsule', 'Dose', $med['dose']];
                            if (!empty($med['frequency'])) $rxMeta[] = ['bi-arrow-repeat', 'Frequency', $med['frequency']];
                            if (!empty($med['duration'])) $rxMeta[] = ['bi-calendar3', 'Duration', $med['duration']];
                        ?>
                        <?php if (!empty($rxMeta)): ?>
                            <div class="rx-meta d-flex flex-wrap gap-2 mb-2">
                                <?php foreach ($rxMeta as $m): ?>
                                    <span class="rx-meta-chip"><i class="bi <?= $m[0] ?> me-1"></i><span class="rx-meta-k"><?= $m[1] ?>:</span>&nbsp;<?= htmlspecialchars($m[2]) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($med['notes'])): ?>
                            <p class="text-muted mb-0">
                                <small><?= htmlspecialchars($med['notes']) ?></small>
                            </p>
                <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" id="emptyMedicationsMessage">
                        <i class="bi bi-capsule text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No medications prescribed</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>


        <style>
            /* Prescription card meta chips (dose / frequency / duration) — a
               distinct teal tone so they read apart from the blue drug title
               and the muted notes, in both light + dark themes. */
            .rx-meta-chip {
                display: inline-flex;
                align-items: center;
                font-size: .72rem;
                font-weight: 600;
                padding: .2rem .6rem;
                border-radius: 999px;
                line-height: 1.25;
                color: #0d9488;
                background: rgba(13, 148, 136, .10);
                border: 1px solid rgba(13, 148, 136, .22);
            }
            .rx-meta-chip .rx-meta-k { font-weight: 500; opacity: .75; }
            .dark .rx-meta-chip {
                color: #2dd4bf;
                background: rgba(45, 212, 191, .12);
                border-color: rgba(45, 212, 191, .30);
            }
        </style>

        <!-- Medical Instructions -->
        <div class="card mb-4" id="medicalInstructionsCard">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="bi bi-journal-medical me-2"></i>
                        Medical Instructions
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-sm btn-outline-info" id="miCopySuggestedBtn" title="Copy suggested instructions from diagnosis or patient history">
                            <i class="bi bi-lightbulb me-1"></i>Copy Suggested
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="miFromTemplatesBtn" title="Choose from instruction templates">
                            <i class="bi bi-collection me-1"></i>Templates
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="miAddCustomBtn" title="Add custom instructions">
                            <i class="bi bi-plus me-1"></i>Add Custom
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body" id="medicalInstructionsContainer">
                <div id="medicalInstructionsSuggestions" class="mi-suggestions-panel" hidden></div>
                <div id="medicalInstructionsList"></div>
                <div class="text-center" id="emptyMedicalInstructionsMessage"<?= !empty($medicalInstructions) ? ' style="display:none"' : '' ?>>
                    <i class="bi bi-journal-medical text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No medical instructions yet</p>
                    <p class="text-muted small mb-0">Suggestions appear when a diagnosis matches a template.</p>
                </div>
            </div>
        </div>

        <!-- Glasses Prescriptions -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-eyeglasses me-2"></i>
                        Glasses Prescription
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <?php if (!empty($glasses)): ?>
                        <button class="btn btn-sm btn-info" onclick="printGlassesPrescription(<?= $appointment['id'] ?>)" title="Print Glasses">
                            <i class="bi bi-printer"></i>
                        </button>
                        <?php endif; ?>
                        <?php if (!empty($glasses) && $__waEnabled): ?>
                        <button class="btn btn-sm btn-success" onclick="WhatsAppIntegration.openModal(<?= $appointment['patient_id'] ?>, <?= $appointment['id'] ?>, 'prescription')" title="Send Glasses Prescription via WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-primary" onclick="addGlassesPrescription(<?= $appointment['id'] ?>)">
                            <i class="bi bi-plus me-1"></i>Add Glasses
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body" id="glassesContainer">
                <?php if (!empty($glasses)): ?>
                    <?php foreach ($glasses as $glass): ?>
                    <div class="prescription-card p-3 mb-3" data-glasses-id="<?= $glass['id'] ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-success mb-0">
                                <i class="bi bi-eyeglasses me-1"></i>
                                <?= ucfirst($glass['lens_type'] ?? 'Single Vision') ?>
                            </h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="editGlassesPrescription(<?= $glass['id'] ?>, <?= htmlspecialchars(json_encode($glass), ENT_QUOTES) ?>)" title="Edit Glasses">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteGlassesPrescription(<?= $glass['id'] ?>)" title="Delete Glasses">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Distance Vision -->
                        <div class="mb-3">
                            <h6 class="text-success"><i class="bi bi-eye me-1"></i>Distance Vision</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <p class="mb-1">
                                        SPH: <?= htmlspecialchars($glass['distance_sphere_r'] ?? 'N/A') ?><br>
                                        CYL: <?= htmlspecialchars($glass['distance_cylinder_r'] ?? 'N/A') ?><br>
                                        AXIS: <?= htmlspecialchars($glass['distance_axis_r'] ?? 'N/A') ?>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-primary">Left Eye (OS)</h6>
                                    <p class="mb-1">
                                        SPH: <?= htmlspecialchars($glass['distance_sphere_l'] ?? 'N/A') ?><br>
                                        CYL: <?= htmlspecialchars($glass['distance_cylinder_l'] ?? 'N/A') ?><br>
                                        AXIS: <?= htmlspecialchars($glass['distance_axis_l'] ?? 'N/A') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Near Vision -->
                        <?php if (!empty($glass['near_sphere_r']) || !empty($glass['near_sphere_l']) || !empty($glass['near_cylinder_r']) || !empty($glass['near_cylinder_l'])): ?>
                        <div class="mb-3">
                            <h6 class="text-info"><i class="bi bi-book me-1"></i>Near Vision</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <p class="mb-1">
                                        SPH: <?= htmlspecialchars($glass['near_sphere_r'] ?? 'N/A') ?><br>
                                        CYL: <?= htmlspecialchars($glass['near_cylinder_r'] ?? 'N/A') ?><br>
                                        AXIS: <?= htmlspecialchars($glass['near_axis_r'] ?? 'N/A') ?>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-primary">Left Eye (OS)</h6>
                                    <p class="mb-1">
                                        SPH: <?= htmlspecialchars($glass['near_sphere_l'] ?? 'N/A') ?><br>
                                        CYL: <?= htmlspecialchars($glass['near_cylinder_l'] ?? 'N/A') ?><br>
                                        AXIS: <?= htmlspecialchars($glass['near_axis_l'] ?? 'N/A') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($glass['PD_DISTANCE']) || !empty($glass['PD_NEAR'])): ?>
                            <div class="text-center mt-2">
                                <?php if (!empty($glass['PD_DISTANCE'])): ?>
                                    <strong>PD Distance:</strong> <?= htmlspecialchars($glass['PD_DISTANCE']) ?>mm
                                <?php endif; ?>
                                <?php if (!empty($glass['PD_NEAR'])): ?>
                                    <?php if (!empty($glass['PD_DISTANCE'])): ?> | <?php endif; ?>
                                    <strong>PD Near:</strong> <?= htmlspecialchars($glass['PD_NEAR']) ?>mm
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($glass['comments'])): ?>
                            <p class="text-muted mt-2 mb-0">
                                <small><?= htmlspecialchars($glass['comments']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" id="emptyGlassesMessage">
                        <i class="bi bi-eyeglasses text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No glasses prescription</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>


        <!-- Medical Attachments -->
        <div class="card mb-4">
            <div class="card-header">
                <!-- Row 1: icon + title -->
                <h5 class="mb-3">
                    <i class="bi bi-paperclip me-2"></i>
                    Images & Attachments
                </h5>
                <!-- Row 2: select/delete at the far left, add-attachment actions at the far right -->
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Bulk actions">
                        <button class="btn btn-sm btn-outline-secondary" type="button" id="attachmentsSelectAllBtn"
                                onclick="attachmentsToggleSelectAll()"
                                title="Select all on this page">
                            <i class="bi bi-check2-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" type="button" id="attachmentsDeleteSelectedBtn"
                                onclick="attachmentsConfirmDeleteSelected()" disabled
                                title="Delete selected items">
                            <i class="bi bi-trash"></i>
                            <span class="badge bg-danger ms-1 d-none" id="attachmentsSelectedBadge">0</span>
                        </button>
                    </div>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Add attachment">
                        <button class="btn btn-sm btn-primary" onclick="showUploadModal(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)" title="Upload an existing file">
                            <i class="bi bi-cloud-upload me-1"></i>Upload
                        </button>
                        <button class="btn btn-sm btn-draw-consultation" type="button"
                                onclick="DrawConsultation && DrawConsultation.openForAppointment(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)"
                                title="Open the drawing canvas">
                            <i class="bi bi-pencil-square me-1"></i>Draw
                        </button>
                        <button class="btn btn-sm btn-success" onclick="openCameraModal(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)" title="Take a photo using the camera">
                            <i class="bi bi-camera me-1"></i>Capture
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="attachmentsContainer">
                <?php if (!empty($attachments)): ?>
                    <div class="row" id="attachmentsRow">
                        <?php foreach ($attachments as $attachment): ?>
                                    <?php
                                    $fileExt = strtolower(pathinfo($attachment['original_filename'], PATHINFO_EXTENSION));
                                    $fileName = strtolower($attachment['original_filename']);
                                    $description = strtolower($attachment['description'] ?? '');
                        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                        $viewUrl = '/api/attachments/view/' . $attachment['id'];
                                    
                                    // تحديد نوع الملف والأيقونة والـ badge
                                    $iconClass = 'bi-file-earmark';
                                    $fileType = 'Document';
                                    $badgeClass = 'bg-secondary';
                                    
                        if ($isImage) {
                                        $iconClass = 'bi-image';
                                        // تحديد نوع الصورة بناءً على الاسم أو الوصف
                                        if (strpos($fileName, 'xray') !== false || strpos($fileName, 'x-ray') !== false || 
                                            strpos($description, 'xray') !== false || strpos($description, 'x-ray') !== false ||
                                            strpos($description, 'أشعة') !== false || strpos($description, 'راديو') !== false) {
                                            $fileType = 'X-Ray';
                                            $badgeClass = 'bg-info';
                                        } elseif (strpos($fileName, 'scan') !== false || strpos($fileName, 'ct') !== false || 
                                                  strpos($fileName, 'mri') !== false || strpos($description, 'scan') !== false ||
                                                  strpos($description, 'مسح') !== false || strpos($description, 'رنين') !== false) {
                                            $fileType = 'Scan';
                                            $badgeClass = 'bg-primary';
                                        } elseif (strpos($fileName, 'lab') !== false || strpos($fileName, 'test') !== false ||
                                                  strpos($fileName, 'blood') !== false || strpos($fileName, 'urine') !== false ||
                                                  strpos($description, 'lab') !== false || strpos($description, 'تحليل') !== false ||
                                                  strpos($description, 'فحص') !== false || strpos($description, 'دم') !== false ||
                                                  strpos($description, 'بول') !== false || strpos($description, 'معمل') !== false) {
                                            $fileType = 'Lab Result';
                                            $badgeClass = 'bg-success';
                                        } elseif (strpos($fileName, 'echo') !== false || strpos($fileName, 'ultrasound') !== false ||
                                                  strpos($description, 'echo') !== false || strpos($description, 'سونار') !== false ||
                                                  strpos($description, 'موجات') !== false) {
                                            $fileType = 'Ultrasound';
                                            $badgeClass = 'bg-info';
                                        } elseif (strpos($fileName, 'fundus') !== false || strpos($fileName, 'retina') !== false ||
                                                  strpos($description, 'fundus') !== false || strpos($description, 'قاع العين') !== false ||
                                                  strpos($description, 'شبكية') !== false) {
                                            $fileType = 'Fundus Photo';
                                            $badgeClass = 'bg-primary';
                                        } else {
                                            $fileType = 'Photo';
                                            $badgeClass = 'bg-warning text-dark';
                                        }
                                    } elseif ($fileExt == 'pdf') {
                                        $iconClass = 'bi-file-earmark-pdf';
                                        if (strpos($fileName, 'report') !== false || strpos($fileName, 'result') !== false ||
                                            strpos($description, 'report') !== false || strpos($description, 'تقرير') !== false ||
                                            strpos($description, 'نتيجة') !== false) {
                                            $fileType = 'Report';
                                            $badgeClass = 'bg-success';
                                        } elseif (strpos($fileName, 'prescription') !== false || strpos($fileName, 'rx') !== false ||
                                                  strpos($description, 'prescription') !== false || strpos($description, 'روشتة') !== false ||
                                                  strpos($description, 'وصفة') !== false) {
                                            $fileType = 'Prescription';
                                            $badgeClass = 'bg-danger';
                                        } elseif (strpos($fileName, 'invoice') !== false || strpos($fileName, 'bill') !== false ||
                                                  strpos($description, 'invoice') !== false || strpos($description, 'فاتورة') !== false ||
                                                  strpos($description, 'حساب') !== false) {
                                            $fileType = 'Invoice';
                                            $badgeClass = 'bg-warning text-dark';
                                        } else {
                                            $fileType = 'PDF Document';
                                            $badgeClass = 'bg-danger';
                                        }
                                    } elseif (in_array($fileExt, ['doc', 'docx'])) {
                                        $iconClass = 'bi-file-earmark-word';
                                        $fileType = 'Word Document';
                                        $badgeClass = 'bg-primary';
                                    } elseif (in_array($fileExt, ['xls', 'xlsx'])) {
                                        $iconClass = 'bi-file-earmark-excel';
                                        $fileType = 'Excel Sheet';
                                        $badgeClass = 'bg-success';
                                    } elseif (in_array($fileExt, ['txt'])) {
                                        $iconClass = 'bi-file-earmark-text';
                                        $fileType = 'Text File';
                                        $badgeClass = 'bg-secondary';
                                    }
                                    ?>
                        <?php
                            $originalName = $attachment['original_filename'];
                            $fileSizeKb   = number_format($attachment['file_size'] / 1024, 1);
                            $createdAt    = date('d/m/Y H:i', strtotime($attachment['created_at']));
                            $description  = $attachment['description'] ?? '';
                            $shortDescr   = strlen($description) > 40 ? substr($description, 0, 37) . '...' : $description;
                        ?>
                        <div class="col-md-6 mb-3">
                            <div class="attachment-card p-2 rounded" data-attachment-id="<?= $attachment['id'] ?>" style="min-height: <?= $isImage ? '230px' : '88px' ?>; display: flex; flex-direction: column;">
                                <?php if ($isImage): ?>
                                <!-- Image preview with overlay action row pinned to bottom-centre -->
                                <div class="attachment-image-wrap">
                                    <img src="<?= htmlspecialchars($viewUrl) ?>"
                                         alt="<?= htmlspecialchars($originalName) ?>"
                                         onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= htmlspecialchars($attachment['file_path']) ?>', '<?= $fileExt ?>')"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display:none; width:100%; height:160px; background:var(--bg); align-items:center; justify-content:center; flex-direction:column;">
                                        <i class="bi bi-image text-muted" style="font-size:2rem;"></i>
                                        <small class="text-muted">Image not available</small>
                                    </div>
                                    <div class="attachment-overlay-actions" role="group">
                                        <button type="button" class="attachment-action-btn is-edit"
                                                onclick="editAttachmentDrawing(<?= $attachment['id'] ?>, '<?= htmlspecialchars($viewUrl) ?>', '<?= htmlspecialchars($originalName, ENT_QUOTES) ?>')"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit (open in Drawing)" aria-label="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="attachment-action-btn is-view"
                                                onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= htmlspecialchars($attachment['file_path']) ?>', '<?= $fileExt ?>')"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View" aria-label="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="attachment-action-btn is-download"
                                                onclick="downloadAttachment(<?= $attachment['id'] ?>, '<?= htmlspecialchars($originalName, ENT_QUOTES) ?>')"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download" aria-label="Download">
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <button type="button" class="attachment-action-btn is-delete"
                                                onclick="deleteAttachment(<?= $attachment['id'] ?>)"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" aria-label="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="d-flex align-items-center" style="min-width:0;">
                                    <i class="bi <?= $iconClass ?> text-primary me-2" style="font-size:1.1rem; flex-shrink:0;"></i>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="d-flex align-items-center justify-content-between mb-1" style="gap:.4rem;">
                                            <h6 class="mb-0 attachment-filename text-truncate" style="font-size:.8rem; line-height:1.15; min-width:0;"
                                                title="<?= htmlspecialchars($originalName) ?>"
                                                data-bs-toggle="tooltip" data-bs-placement="top">
                                                <?= htmlspecialchars($originalName) ?>
                                            </h6>
                                            <span class="badge <?= $badgeClass ?>" style="font-size:.6rem; flex-shrink:0; font-weight:500; border-radius:8px;">
                                                <?= $fileType ?>
                                            </span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size:.65rem; line-height:1.15;">
                                            <?= $fileSizeKb ?> KB · <?= $createdAt ?>
                                        </small>
                                    </div>
                                </div>

                                <?php if (!$isImage): ?>
                                <!-- For non-image attachments, the bottom action row sits below the
                                     meta because there's no thumbnail to overlay. View / Download /
                                     Delete only — no Edit (nothing to draw on). -->
                                <div class="attachment-actions-row">
                                    <button type="button" class="attachment-action-btn is-view"
                                            onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= htmlspecialchars($attachment['file_path']) ?>', '<?= $fileExt ?>')"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View" aria-label="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="attachment-action-btn is-download"
                                            onclick="downloadAttachment(<?= $attachment['id'] ?>, '<?= htmlspecialchars($originalName, ENT_QUOTES) ?>')"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download" aria-label="Download">
                                        <i class="bi bi-download"></i>
                                    </button>
                                    <button type="button" class="attachment-action-btn is-delete"
                                            onclick="deleteAttachment(<?= $attachment['id'] ?>)"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" aria-label="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($description)): ?>
                                <p class="text-muted mb-0 small mt-1" style="font-size:.7rem; line-height:1.25;"
                                   title="<?= htmlspecialchars($description) ?>"
                                   data-bs-toggle="tooltip" data-bs-placement="bottom">
                                   <?= htmlspecialchars($shortDescr) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4" id="emptyAttachmentsMessage">
                        <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No images or attachments found</p>
                    </div>
                <?php endif; ?>
                </div>
                <!-- Pagination footer (rendered by reloadAttachments when total > perPage) -->
                <div id="attachmentsPagination" class="attachments-pagination mt-3"></div>
            </div>
        </div>


        <!-- Lab Tests & Radiology -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-data me-2"></i>
                        Lab Tests & Radiology
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <?php if (!empty($labTests)): ?>
                        <button class="btn btn-sm btn-outline-secondary" onclick="printLabTests(<?= $appointment['id'] ?>)" title="Print Lab Tests">
                            <i class="bi bi-printer"></i>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-primary" onclick="addLabTest(<?= $appointment['id'] ?>)">
                            <i class="bi bi-plus me-1"></i>Add Lab/Radiology
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body" id="labTestsContainer">
                <?php if (!empty($labTests)): ?>
                    <?php foreach ($labTests as $test): ?>
                    <div class="prescription-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-primary mb-0">
                                <i class="bi bi-<?= $test['test_type'] === 'radiology' ? 'camera-reels' : 'clipboard-data' ?> me-1"></i>
                                <?= htmlspecialchars($test['test_name']) ?>
                            </h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="editLabTest(<?= $test['id'] ?>, <?= htmlspecialchars(json_encode($test), ENT_QUOTES) ?>)" title="Edit Test">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="printLabTest(<?= $test['id'] ?>)" title="Print Test">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteLabTest(<?= $test['id'] ?>)" title="Delete Test">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <p class="mb-1">
                            <strong>Type:</strong> <?= ucfirst($test['test_type']) ?><br>
                            <strong>Status:</strong> 
                            <span class="badge bg-<?= $test['status'] === 'completed' ? 'success' : ($test['status'] === 'pending' ? 'warning' : 'secondary') ?>">
                                <?= ucfirst($test['status']) ?>
                            </span><br>
                            <?php if (!empty($test['priority'])): ?>
                                <strong>Priority:</strong> 
                                <span class="badge bg-<?= $test['priority'] === 'urgent' ? 'danger' : ($test['priority'] === 'high' ? 'warning' : 'primary') ?>">
                                    <?= ucfirst($test['priority']) ?>
                                </span><br>
                            <?php endif; ?>
                            <?php if (!empty($test['ordered_date'])): ?>
                                <strong>Ordered Date:</strong> <?= date('d/m/Y', strtotime($test['ordered_date'])) ?><br>
                            <?php endif; ?>
                            <?php if (!empty($test['expected_date'])): ?>
                                <strong>Expected Date:</strong> <?= date('d/m/Y', strtotime($test['expected_date'])) ?>
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($test['notes'])): ?>
                            <p class="text-muted mb-1">
                                <small><strong>Notes:</strong> <?= htmlspecialchars($test['notes']) ?></small>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($test['results'])): ?>
                            <p class="text-success mb-0">
                                <small><strong>Results:</strong> <?= htmlspecialchars($test['results']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center">
                        <i class="bi bi-clipboard-data text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No lab tests or radiology ordered</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<!-- Add to Patient Board (board-card visit notes) -->
<!-- Visit note → patient board card comments (tagged "Visit #id") -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>Add to Patient Board</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            Notes added here attach to this patient's board card, tagged
            <strong>Visit #<?= (int)($appointment['id'] ?? 0) ?></strong> (clickable from the board).
        </p>
        <div id="visitBoardNotes" class="visit-board-notes mb-2">
            <div class="text-center text-muted small py-2"><span class="spinner-border spinner-border-sm" role="status"></span></div>
        </div>
        <textarea id="visitCommentInput" class="form-control" rows="2" maxlength="3900"
                  placeholder="Write a note for the patient board…"></textarea>
        <button id="visitCommentSend" class="btn btn-primary btn-sm mt-2" type="button" disabled>
            <i class="bi bi-send me-1"></i>Add to board
        </button>
        <div class="text-danger small mt-2 d-none" id="visitCommentError"></div>
    </div>
</div>
<link rel="stylesheet"
      href="/app/Views/doctor/assets/css/comment-media.css?v=<?= file_exists(__DIR__ . '/assets/css/comment-media.css') ? filemtime(__DIR__ . '/assets/css/comment-media.css') : time() ?>">
<style>
    .visit-board-notes { max-height: 340px; overflow-y: auto; }
    .visit-note { display: flex; gap: .6rem; border: 1px solid var(--glass-border, #e2e8f0); border-radius: var(--r-md, 12px); padding: 10px 12px; margin-bottom: 8px; background: var(--card); }
    .visit-note-av { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex: 0 0 auto; }
    .visit-note-av--ini { display: inline-flex; align-items: center; justify-content: center; background: var(--ds-primary); color: #fff; font-weight: 800; font-size: .75rem; }
    .visit-note-main { flex: 1 1 auto; min-width: 0; }
    .visit-note-meta { font-size: .72rem; color: var(--muted, #64748b); margin-bottom: 3px; }
    .visit-note-author { font-weight: 700; color: var(--text); }
    .visit-note-body { font-size: .85rem; line-height: 1.55; white-space: pre-wrap; word-break: break-word; color: var(--text); }
    .visit-note { position: relative; }
    .visit-note-del {
        flex: 0 0 auto;
        align-self: flex-start;
        background: transparent;
        border: 0;
        color: var(--muted, #64748b);
        width: 28px; height: 28px;
        border-radius: 8px;
        cursor: pointer;
        opacity: .7;
        transition: opacity .15s ease, background .15s ease, color .15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
    }
    .visit-note-del:hover { opacity: 1; background: color-mix(in srgb, var(--danger, #ef4444) 12%, transparent); color: var(--danger, #ef4444); }
    .visit-note-del:focus-visible { opacity: 1; outline: none; box-shadow: 0 0 0 2px color-mix(in srgb, var(--danger, #ef4444) 40%, transparent); }
    .visit-note-del:disabled { opacity: .5; cursor: not-allowed; }
</style>
<script src="/app/Views/doctor/assets/js/comment-media.js?v=<?= file_exists(__DIR__ . '/assets/js/comment-media.js') ? filemtime(__DIR__ . '/assets/js/comment-media.js') : time() ?>"></script>
<script>
(function () {
    const PID  = <?= (int)($appointment['patient_id'] ?? $patient['id'] ?? 0) ?>;
    const APPT = <?= (int)($appointment['id'] ?? 0) ?>;
    const CSRF = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>';
    const CM   = window.CommentMedia;
    const listEl  = document.getElementById('visitBoardNotes');
    const input   = document.getElementById('visitCommentInput');
    const sendBtn = document.getElementById('visitCommentSend');
    const errEl   = document.getElementById('visitCommentError');
    if (!PID || !listEl) return;

    const esc = (s) => CM ? CM.escapeHtml(s) : (s == null ? '' : String(s));
    const fmt = (ts) => { if (!ts) return ''; const d = new Date(String(ts).replace(' ', 'T')); return isNaN(d) ? ts : d.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }); };

    const composer = (CM && input) ? CM.attachComposer({
        textarea: input, getCsrf: () => CSRF,
        onError: (m) => { errEl.textContent = m; errEl.classList.remove('d-none'); },
        onChange: () => { sendBtn.disabled = !composer.hasContent() || composer.isUploading(); },
        onSubmit: () => sendBtn.click()
    }) : null;

    async function load() {
        try {
            const r = await fetch('/api/comments/board_card/' + PID, { credentials: 'same-origin' });
            const j = await r.json();
            const rows = (j.data || []).filter(x => !x.deleted_at);
            if (!rows.length) { listEl.innerHTML = '<p class="text-muted small mb-0">No board notes yet.</p>'; return; }
            listEl.innerHTML = rows.slice(-8).map(r => {
                const img = CM ? CM.avatarSrc(r.author_image) : null;
                const av = img
                    ? `<img class="visit-note-av" src="${esc(img)}" alt="">`
                    : `<span class="visit-note-av visit-note-av--ini">${esc(CM ? CM.initials(r.author_name) : '?')}</span>`;
                const body = CM ? CM.renderBody(r.body, r.mentions) : esc(r.body);
                const atts = CM ? CM.renderAttachments(r.attachments) : '';
                const del  = r.can_edit
                    ? `<button type="button" class="visit-note-del" data-cid="${r.id}" aria-label="Delete note" title="Delete note"><i class="bi bi-trash"></i></button>`
                    : '';
                return `<div class="visit-note">${av}<div class="visit-note-main"><div class="visit-note-meta"><span class="visit-note-author">${esc(r.author_name || 'User')}</span> · ${esc(fmt(r.created_at))}</div><div class="visit-note-body">${body}</div>${atts}</div>${del}</div>`;
            }).join('');
            listEl.scrollTop = listEl.scrollHeight;
        } catch (e) { listEl.innerHTML = '<p class="text-danger small mb-0">Failed to load notes.</p>'; }
    }

    // Delegated delete handler — themed confirmation modal then DELETE.
    listEl.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('.visit-note-del');
        if (!btn) return;
        const cid = btn.getAttribute('data-cid');
        if (!cid) return;
        const _t = (k, fb) => (window.V11I18n && window.V11I18n.t(k, fb)) || fb;
        const ok = typeof window.mkConfirmModal === 'function'
            ? await window.mkConfirmModal({
                title: _t('patient.delete_note_title', 'Delete note?'),
                message: _t('patient.delete_note_msg', 'This board note will be removed. This action cannot be undone.'),
                confirmText: _t('modal.delete', 'Delete'),
                cancelText: _t('modal.cancel', 'Cancel'),
                confirmClass: 'btn-danger',
                icon: 'bi-trash',
            })
            : window.confirm(_t('patient.delete_note_msg', 'Delete this note?'));
        if (!ok) return;
        btn.disabled = true;
        try {
            const r = await fetch('/api/comments/' + cid, {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
            });
            const j = await r.json().catch(() => ({}));
            if (!r.ok || !j.ok) throw new Error((j && j.error) || ('HTTP ' + r.status));
            await load();
        } catch (e) {
            btn.disabled = false;
            errEl.textContent = 'Delete failed: ' + (e.message || 'unknown');
            errEl.classList.remove('d-none');
        }
    });

    if (!composer) input.addEventListener('input', () => { sendBtn.disabled = input.value.trim() === ''; });
    sendBtn.addEventListener('click', async () => {
        const note = composer ? composer.getBody() : input.value.trim();
        const ids  = composer ? composer.getAttachmentIds() : [];
        if (!note && !ids.length) return;
        if (composer && composer.isUploading()) { errEl.textContent = 'Wait for the upload to finish'; errEl.classList.remove('d-none'); return; }
        errEl.classList.add('d-none'); sendBtn.disabled = true;
        const tag = `[Visit #${APPT}](/doctor/appointments/${APPT})`;
        const body = note ? `${tag} ${note}` : tag;
        try {
            const r = await fetch('/api/comments/board_card/' + PID, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({ body: body, attachment_ids: ids })
            });
            const j = await r.json();
            if (!j.ok) throw new Error(j.error || 'Failed to add note');
            if (composer) composer.reset(); else input.value = '';
            await load();
        } catch (e) { errEl.textContent = e.message; errEl.classList.remove('d-none'); sendBtn.disabled = false; }
    });
    load();
})();
</script>

<!-- Patient Medical History -->
<?php if (!empty($medicalHistory)): ?>
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-heart me-2"></i>
                Patient Medical History
            </h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#aiToolsModal" title="AI assistant tools">
                    <i class="bi bi-stars me-1"></i>AI Tools
                </button>
                <span class="badge bg-primary"><?= count($medicalHistory) ?></span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="mh-list">
            <?php foreach ($medicalHistory as $history): ?>
            <?php
                // Notes: prefer the `notes` field (new format), else combine legacy fields.
                $notesText = '';
                if (!empty($history['notes'])) {
                    $notesText = htmlspecialchars(str_replace(["\r\n", "\r", "\n"], ' ', $history['notes']));
                } elseif (($history['entry_type'] ?? '') !== 'new_format') {
                    $oldFormatNotes = [];
                    if (!empty($history['allergies']))        $oldFormatNotes[] = 'Allergies: ' . htmlspecialchars($history['allergies']);
                    if (!empty($history['medications']))      $oldFormatNotes[] = 'Medications: ' . htmlspecialchars($history['medications']);
                    if (!empty($history['systemic_history'])) $oldFormatNotes[] = 'Systemic: ' . htmlspecialchars($history['systemic_history']);
                    if (!empty($history['prior_surgeries']))  $oldFormatNotes[] = 'Surgeries: ' . htmlspecialchars($history['prior_surgeries']);
                    if (!empty($history['family_history']))   $oldFormatNotes[] = 'Family: ' . htmlspecialchars($history['family_history']);
                    if (!empty($oldFormatNotes)) $notesText = htmlspecialchars(implode(' | ', $oldFormatNotes));
                }
                if ($notesText !== '') {
                    foreach (['Chief Complaint','Plan','History of Present Illness','Allergies','Medications','Systemic','Surgeries','Family','Diagnosis','Treatment'] as $title) {
                        $notesText = preg_replace('/\b(' . preg_quote($title, '/') . '):\s*/i', '<strong class="mh-key">$1:</strong> ', $notesText);
                    }
                }
            ?>
            <div class="mh-item">
                <div class="mh-item-head">
                    <span class="mh-item-title">
                        <i class="bi bi-clipboard-heart"></i>
                        <?= !empty($history['condition_name']) ? htmlspecialchars($history['condition_name']) : 'Medical Record #' . (int)$history['id'] ?>
                    </span>
                    <small class="mh-item-date">
                        <?= date('M d, Y', strtotime($history['diagnosis_date'] ?? $history['created_at'])) ?>
                        <?php if (!empty($history['doctor_name'])): ?> · <?= htmlspecialchars($history['doctor_name']) ?><?php endif; ?>
                    </small>
                </div>
                <?php if ($notesText !== ''): ?>
                <div class="mh-item-notes"><?= $notesText ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<style>
    .mh-list { max-height: 360px; overflow-y: auto; }
    .mh-item { padding: 12px 16px; border-bottom: 1px solid var(--border, #e2e8f0); }
    .mh-item:last-child { border-bottom: 0; }
    .mh-item-head { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; flex-wrap: wrap; }
    .mh-item-title { font-weight: 600; font-size: .9rem; display: inline-flex; align-items: center; gap: 6px; }
    .mh-item-title i { color: var(--accent, #0ea5e9); }
    .mh-item-date { color: var(--muted, #64748b); font-size: .72rem; white-space: nowrap; }
    .mh-item-notes { font-size: .82rem; color: var(--text, #0f172a); margin-top: 4px; line-height: 1.55; }
    .mh-key { color: var(--accent, #0ea5e9); }
</style>
<?php endif; ?>

<!-- Forum Topics Section -->
</div>
    </div>
</div>

    </div>
</div>

<?php include __DIR__ . '/alert_modal.php'; ?>
<script>
// Initialize APPOINTMENT_CONFIG with PHP variables
<?php
?>

window.APPOINTMENT_CONFIG = {
    appointmentId: <?= isset($appointment['id']) ? (int)$appointment['id'] : 'null' ?>,
    // Doctor Auto Complete preference (Settings → Auto Complete). Default ON.
    autocompleteMedications: <?= (!isset($autocompletePrefs) || !empty($autocompletePrefs['medications'])) ? 'true' : 'false' ?>,
    appointmentDate: '<?= isset($appointment['date']) ? date('Y-m-d', strtotime($appointment['date'])) : 'null' ?>',
    appointmentTime: '<?= isset($appointment['start_time']) ? date('H:i:s', strtotime($appointment['start_time'])) : 'null' ?>',
    appointmentStatus: '<?= isset($appointment['status']) ? htmlspecialchars($appointment['status']) : 'null' ?>',
    /* Authoritative "is this appointment past its date while still Booked"
       flag, computed server-side using PHP's timezone (Cairo). JS used to
       redo this check with `new Date().toISOString()` which returns UTC,
       and on a phone that's a few hours away from UTC at the day
       boundary the two answers disagreed — page would render with the
       red "missed" header, then JS would re-evaluate, decide "not
       missed", and strip the class. */
    appointmentIsMissed: <?= !empty($isMissed) ? 'true' : 'false' ?>,
    serverToday: '<?= $today ?>',
    patientId: <?= isset($patient['id']) ? (int)$patient['id'] : 'null' ?>,
    patientFirstName: '<?= isset($patient['first_name']) ? htmlspecialchars($patient['first_name']) : 'null' ?>',
    patientLastName: '<?= isset($patient['last_name']) ? htmlspecialchars($patient['last_name']) : 'null' ?>',
    patientName: '<?= isset($patient['first_name']) && isset($patient['last_name']) ? htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) : 'null' ?>',
    patientPhone: '<?= isset($patient['phone']) ? htmlspecialchars($patient['phone']) : 'null' ?>',
    patientAge: <?= isset($patient['dob']) ? (int)date_diff(date_create($patient['dob']), date_create('now'))->y : 'null' ?>,
    doctorId: <?= isset($appointment['doctor_id']) ? (int)$appointment['doctor_id'] : 'null' ?>,
    followupAppointment: <?= !empty($followupAppointment) ? 'true' : 'false' ?>,
    followupAppointmentDate: <?= !empty($followupAppointment) && isset($followupAppointment['date']) ? "'" . date('Y-m-d', strtotime($followupAppointment['date'])) . "'" : 'null' ?>,
    followupAppointmentTime: <?= !empty($followupAppointment) && isset($followupAppointment['start_time']) ? "'" . date('H:i:s', strtotime($followupAppointment['start_time'])) . "'" : 'null' ?>,
    latestDiagnosis: <?= json_encode($latestDiagnosis ?? '', JSON_UNESCAPED_UNICODE) ?>,
    latestDiagnosisCode: <?= json_encode($latestDiagnosisCode ?? '', JSON_UNESCAPED_UNICODE) ?>,
    medicalInstructions: <?= json_encode($medicalInstructions ?? [], JSON_UNESCAPED_UNICODE) ?>
};
</script>
<link rel="stylesheet" href="/app/Views/doctor/assets/css/mi-modals.css?v=<?= file_exists(__DIR__ . '/assets/css/mi-modals.css') ? filemtime(__DIR__ . '/assets/css/mi-modals.css') : time() ?>">
<link rel="stylesheet" href="/app/Views/doctor/assets/css/medical-instructions.css?v=<?= file_exists(__DIR__ . '/assets/css/medical-instructions.css') ? filemtime(__DIR__ . '/assets/css/medical-instructions.css') : time() ?>">
<link rel="stylesheet" href="/app/Views/doctor/assets/css/ai-chat-widget.css?v=<?= file_exists(__DIR__ . '/assets/css/ai-chat-widget.css') ? filemtime(__DIR__ . '/assets/css/ai-chat-widget.css') : time() ?>">
<link rel="stylesheet" href="/app/Views/doctor/assets/css/draw-consultation.css?v=<?= file_exists(__DIR__ . '/assets/css/draw-consultation.css') ? filemtime(__DIR__ . '/assets/css/draw-consultation.css') : time() ?>">
<!-- AI Tools modal — roaya's consultation AI Assistant (from edit_consultation), in an ortho-style modal -->
<div class="modal fade" id="aiToolsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cai-card">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-stars text-warning me-1"></i> AI Assistant
                    <span class="cai-badge ms-2"><i class="bi bi-shield-check"></i> AI — review before saving</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="cai-section">
                    <div class="cai-section-label">Prior-visit clinical summary</div>
                    <p class="text-muted small mb-2">
                        Grounded recap of this patient's previous records
                        (IOP / VA / refraction trends flagged). Read-only —
                        not added to the chart.
                    </p>
                    <button type="button" id="caiSummarizeBtn"
                        class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-clipboard2-pulse"></i>
                        Summarize prior visits
                    </button>
                    <div class="cai-summary-output" id="caiSummaryOutput"></div>
                </div>

                <div class="cai-section">
                    <div class="cai-section-label">Ask the assistant</div>
                    <div class="cai-chips">
                        <button type="button" class="cai-chip"
                            data-cai-prompt="Summarize this patient's prior ophthalmic visits in 5 bullets"
                            data-cai-context="patient_history">
                            <i class="bi bi-clock-history"></i>
                            Summarize prior ophthalmic visits
                        </button>
                        <button type="button" class="cai-chip"
                            data-cai-prompt="What might I be missing in the current consultation draft?"
                            data-cai-context="consultation_summary">
                            <i class="bi bi-question-circle"></i>
                            What might I be missing?
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI assistant config + scripts (shared with the consultation page) -->
<script>
    window.CONSULTATION_AI = {
        appointmentId: <?= isset($appointment['id']) ? (int) $appointment['id'] : 'null' ?>,
        patientId: <?= isset($appointment['patient_id']) ? (int) $appointment['patient_id'] : (isset($patient['id']) ? (int) $patient['id'] : 'null') ?>,
        csrfToken: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>'
    };
</script>
<link rel="stylesheet" href="/app/Views/doctor/assets/css/consultation-ai.css?v=<?= file_exists(__DIR__ . '/assets/css/consultation-ai.css') ? filemtime(__DIR__ . '/assets/css/consultation-ai.css') : time() ?>">
<script defer src="/app/Views/doctor/assets/js/consultation-ai.js?v=<?= file_exists(__DIR__ . '/assets/js/consultation-ai.js') ? filemtime(__DIR__ . '/assets/js/consultation-ai.js') : time() ?>"></script>
<script>
    window.__appointmentTagsConfig = {
        appointmentId: <?= (int)($appointment['id'] ?? 0) ?>,
        patientId: <?= (int)($appointment['patient_id'] ?? $patient['id'] ?? 0) ?>
    };
</script>
<script src="/app/Views/doctor/assets/js/appointment-tags.js?v=<?= file_exists(__DIR__ . '/assets/js/appointment-tags.js') ? filemtime(__DIR__ . '/assets/js/appointment-tags.js') : time() ?>"></script>
<script src="/app/Views/doctor/assets/js/appointment.js?v=<?= file_exists(__DIR__ . '/assets/js/appointment.js') ? filemtime(__DIR__ . '/assets/js/appointment.js') : time() ?>"></script>
<script src="/app/Views/doctor/assets/js/medical-instructions.js?v=<?= file_exists(__DIR__ . '/assets/js/medical-instructions.js') ? filemtime(__DIR__ . '/assets/js/medical-instructions.js') : time() ?>"></script>
<script src="/app/Views/doctor/assets/js/ai-chat-widget.js?v=<?= file_exists(__DIR__ . '/assets/js/ai-chat-widget.js') ? filemtime(__DIR__ . '/assets/js/ai-chat-widget.js') : time() ?>"></script>
<script src="/app/Views/layouts/vendor/fabric.min.js?v=<?= file_exists(dirname(__DIR__) . '/layouts/vendor/fabric.min.js') ? filemtime(dirname(__DIR__) . '/layouts/vendor/fabric.min.js') : '5.3.1' ?>"></script>
<script src="/app/Views/doctor/assets/js/draw-consultation.js?v=<?= file_exists(__DIR__ . '/assets/js/draw-consultation.js') ? filemtime(__DIR__ . '/assets/js/draw-consultation.js') : time() ?>"></script>
<script>
    // Medications are loaded with prices from API via reloadMedications() when needed
</script>
<style>
    .dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>
<script>
    // Auto-detect patient ID for IOP Trend Analyzer
    (function() {
        const patientId = <?= json_encode($appointment['patient_id'] ?? $patient['id'] ?? null) ?>;
        if (patientId) {
            window.currentPatientId = patientId;
        }
    })();
</script>