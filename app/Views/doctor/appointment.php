<link href="/app/Views/doctor/assets/css/appointment.css?v=<?= file_exists(__DIR__ . '/assets/css/appointment.css') ? filemtime(__DIR__ . '/assets/css/appointment.css') : time() ?>" rel="stylesheet">


<div class="d-flex justify-content-between align-items-center mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/doctor/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/doctor/patients">Patients</a></li>
            <li class="breadcrumb-item"><a href="/doctor/patients/<?= $appointment['patient_id'] ?? '' ?>"><?= htmlspecialchars($appointment['patient_name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active">Appointment #<?= $appointment['id'] ?></li>
        </ol>
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
                <strong><a href="/doctor/patients/<?= $patient['id'] ?? '' ?>"  style="color: white; font-weight: 600; text-decoration: none;"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></a></strong>
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
            <button type="button" class="btn btn-primary hide-on-mobile" onclick="editConsultation(<?= $appointment['id'] ?>)">
                <i class="bi bi-pencil me-1"></i>Edit Consultation
            </button>
            <button type="button" class="btn btn-info hide-on-mobile" onclick="printReport(<?= $appointment['id'] ?>)">
                <i class="bi bi-printer me-1"></i>Print Report
            </button>
            <button type="button" class="btn btn-success hide-on-mobile" 
                    id="rescheduleFollowupBtn"
                    onclick="rescheduleFollowupAppointment(<?= $appointment['id'] ?>)"
                    <?= !empty($followupAppointment) ? 'disabled title="Follow-up appointment already scheduled"' : '' ?>>
                <i class="bi bi-calendar-check me-1"></i>Schedule Followup
            </button>
            <button type="button" class="btn btn-danger hide-on-mobile" 
                    onclick="rescheduleAppointment(<?= $appointment['id'] ?>)"
                    <?= $appointment['status'] === 'Completed' ? 'disabled title="Cannot reschedule completed appointments"' : '' ?>>
                <i class="bi bi-calendar-plus me-1"></i>Reschedule
            </button>
            <button type="button" class="btn btn-warning hide-on-mobile" onclick="openAlertModal(<?= $appointment['patient_id'] ?? 'null' ?>, <?= $appointment['id'] ?>)">
                <i class="bi bi-bell me-1"></i>Set Alert
            </button>
            <button type="button" class="btn btn-outline-info hide-on-mobile" 
                    id="appointmentHistoryBtn" 
                    data-patient-id="<?= $appointment['patient_id'] ?? 'null' ?>"
                    data-appointment-id="<?= $appointment['id'] ?>">
                <i class="bi bi-clock-history me-1"></i>Appointment History
            </button>
            <button type="button" class="btn btn-info hide-on-mobile" 
                    onclick="showUnifiedClinicalDashboardPopover(<?= $patient['id'] ?>)"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="bottom" 
                    data-bs-title="View unified clinical dashboard for this patient">
                <i class="bi bi-clipboard-pulse me-1"></i>Clinical Dashboard
            </button>
            <?php if (!empty($medications)): ?>
            <button type="button" class="btn btn-outline-warning hide-on-mobile" onclick="printPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-printer me-1"></i>Print Prescription
            </button>
            <?php endif; ?>
            <?php if (!empty($glasses)): ?>
            <button type="button" class="btn btn-outline-info hide-on-mobile" onclick="printGlassesPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-eyeglasses me-1"></i>Print Glasses
            </button>
            <?php endif; ?>
            <?php if (!empty($labTests)): ?>
            <button type="button" class="btn btn-outline-secondary hide-on-mobile" onclick="printLabTests(<?= $appointment['id'] ?>)">
                <i class="bi bi-clipboard-data me-1"></i>Print Lab Tests
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
                        <p><strong>Name:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
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
                                            onclick="deleteConsultationNote(<?= $note['id'] ?>, '<?= addslashes($note['chief_complaint'] ?? 'Consultation Note') ?>')"
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
                            <h6 class="text-danger">Diagnosis (Required)</h6>
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
                            <h6 class="text-secondary">Treatment Plan</h6>
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
                <button class="btn btn-outline-primary mt-3" onclick="addConsultationNotes(<?= $appointment['id'] ?>)">
                    <i class="bi bi-plus me-2"></i>Add Consultation Notes
                </button>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right Column - Prescriptions & Actions -->
    <div class="col-lg-4">
        
        <!-- Medication Prescriptions -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-capsule me-2"></i>
                        Medications
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <?php if (!empty($medications)): ?>
                        <button class="btn btn-sm btn-outline-warning" onclick="printPrescription(<?= $appointment['id'] ?>)" title="Print Prescription">
                            <i class="bi bi-printer"></i>
                        </button>
                        <?php endif; ?>
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
                            <h6 class="text-primary mb-0" onclick="showDrugPopoverFromName('<?= addslashes($med['drug_name']) ?>', event)" style="cursor: pointer;"><?= htmlspecialchars($med['drug_name']) ?></h6>
                                <span class="drug-price-badge badge bg-success text-white" data-drug-name="<?= htmlspecialchars($med['drug_name']) ?>" style="display: none;">
                                    <i class="bi bi-currency-exchange me-1"></i>
                                    <span class="drug-price-value">Loading...</span>
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="event.stopPropagation(); editMedication(<?= $med['id'] ?>, '<?= addslashes($med['drug_name']) ?>', '<?= addslashes($med['notes'] ?? '') ?>')" title="Edit Medication">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="event.stopPropagation(); deleteMedication(<?= $med['id'] ?>)" title="Delete Medication">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
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
            <div class="card-body">
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
                        <button class="btn btn-sm btn-outline-info" onclick="printGlassesPrescription(<?= $appointment['id'] ?>)" title="Print Glasses">
                            <i class="bi bi-printer"></i>
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
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-paperclip me-2"></i>
                        Images & Attachments
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-sm btn-primary" onclick="showUploadModal(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)">
                            <i class="bi bi-cloud-upload me-1"> Upload</i>
                        </button>
                        <button class="btn btn-sm btn-success" onclick="openCameraModal(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)">
                            <i class="bi bi-camera me-1"> Take Photo</i>
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
                        <div class="col-md-6 mb-3">
                            <div class="attachment-card p-2 border rounded" data-attachment-id="<?= $attachment['id'] ?>" style="min-height: <?= $isImage ? '200px' : '140px' ?>; display: flex; flex-direction: column;">
                                    <?php if ($isImage): ?>
                                <!-- Thumbnail for images -->
                                <div class="mb-2 text-center" style="cursor: pointer;" 
                                     onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')"
                                     data-bs-toggle="tooltip" 
                                     data-bs-placement="top" 
                                     data-bs-title="View Attachement/Photo">
                                    <img src="<?= htmlspecialchars($viewUrl) ?>" 
                                             alt="<?= htmlspecialchars($attachment['original_filename']) ?>"
                                         class="img-thumbnail" 
                                         style="max-width: 100%; max-height: 120px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 100%; height: 120px; background: #f8f9fa; border-radius: 8px; align-items: center; justify-content: center; flex-direction: column;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                        <small class="text-muted">Image not available</small>
                                    </div>
                                </div>
                                    <?php endif; ?>
                                
                                <div class="d-flex align-items-center mb-2 flex-grow-1">
                                    <i class="bi <?= $iconClass ?> text-primary me-2" style="font-size: 1.2rem; flex-shrink: 0;"></i>
                                    <div class="flex-grow-1">
                                        <?php 
                                        $originalName = $attachment['original_filename'];
                                        $displayName = strlen($originalName) > 20 ? substr($originalName, 0, 10) . '...' : $originalName;
                                        ?>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <h6 class="mb-0" style="font-size: 0.8rem; line-height: 1.1; word-wrap: break-word; overflow-wrap: break-word; flex-grow: 1;" 
                                                title="<?= htmlspecialchars($originalName) ?>"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top">
                                                <?= htmlspecialchars($displayName) ?>
                                            </h6>
                                            <span class="badge <?= $badgeClass ?> ms-2" style="font-size: 0.6rem; flex-shrink: 0; font-weight: 500; border-radius: 8px;">
                                                <?= $fileType ?>
                                            </span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.1;">
                                            <?= number_format($attachment['file_size'] / 1024, 1) ?> KB
                                        </small>
                                        <small class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.1;">
                                            <?= date('d/m/Y H:i', strtotime($attachment['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Description section with flex-grow to push buttons down -->
                                <div class="flex-grow-1">
                                    <?php if (!empty($attachment['description'])): ?>
                                    <?php 
                                    $description = $attachment['description'];
                                    $shortDescription = strlen($description) > 40 ? substr($description, 0, 37) . '...' : $description;
                                    ?>
                                    <p class="text-muted mb-1 small" style="font-size: 0.7rem; line-height: 1.2;"
                                       title="<?= htmlspecialchars($description) ?>"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="bottom">
                                       <?= htmlspecialchars($shortDescription) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Buttons fixed at bottom -->
                                <div class="btn-group btn-group-sm w-100 mt-auto" role="group">
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')" 
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="View Attachement/Photo">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" 
                                            onclick="downloadAttachment(<?= $attachment['id'] ?>, '<?= $attachment['original_filename'] ?>')"
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                        <i class="bi bi-download me-1"></i>Download
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="deleteAttachment(<?= $attachment['id'] ?>)"
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
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
                
                <div class="row g-2 mt-3">
                    <div class="col-6">
                        <button class="btn btn-primary w-100" onclick="showUploadModal(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)">
                            <i class="bi bi-cloud-upload me-2"></i>Upload File
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-success w-100" onclick="openCameraModal(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)">
                            <i class="bi bi-camera me-2"></i>Take Photo
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<!-- Patient Medical History Carousel -->
<?php if (!empty($medicalHistory)): ?>
<div class="medical-history-wrapper mb-4">
    <div class="medical-history-head">
        <h2>
            <i class="bi bi-clipboard-heart me-2"></i>
            Patient Medical History
            <span class="badge bg-primary ms-2"><?= count($medicalHistory) ?></span>
        </h2>
        <div class="controls">
            <button id="medicalHistoryPrev" class="nav-btn" aria-label="Prev">‹</button>
            <button id="medicalHistoryNext" class="nav-btn" aria-label="Next">›</button>
        </div>
    </div>
    <div class="medical-history-slider">
        <div class="medical-history-track" id="medicalHistoryTrack">
            <?php foreach ($medicalHistory as $index => $history): ?>
            <article class="medical-history-card <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                <div class="medical-history-card__bg" style="background: linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%);"></div>
                <div class="medical-history-card__content">
                    <div class="medical-history-card__icon">
                        <i class="bi bi-clipboard-heart"></i>
                    </div>
                    <div class="medical-history-card__details">
                        <h3 class="medical-history-card__title">
                            <?php if (!empty($history['condition_name'])): ?>
                                <?= htmlspecialchars($history['condition_name']) ?>
                            <?php else: ?>
                                Medical Record #<?= $history['id'] ?>
                            <?php endif; ?>
                        </h3>
                        <div class="medical-history-card__meta">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?php if (!empty($history['diagnosis_date'])): ?>
                                    <?= date('M d, Y', strtotime($history['diagnosis_date'])) ?>
                                <?php else: ?>
                                    <?= date('M d, Y', strtotime($history['created_at'])) ?>
                                <?php endif; ?>
                                <?php if (!empty($history['doctor_name'])): ?>
                                    • by <?= htmlspecialchars($history['doctor_name']) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php 
                        // Always show notes if available - prioritize notes field, then fallback to old format fields
                        $notesText = '';
                        if (!empty($history['notes'])) {
                            // Use notes field directly if available (for new_format entries)
                            $notesText = htmlspecialchars(str_replace(["\r\n", "\r", "\n"], ' ', $history['notes']));
                        } elseif ($history['entry_type'] !== 'new_format') {
                            // For old format, combine all fields
                            $oldFormatNotes = [];
                            if (!empty($history['allergies'])) $oldFormatNotes[] = 'Allergies: ' . htmlspecialchars($history['allergies']);
                            if (!empty($history['medications'])) $oldFormatNotes[] = 'Medications: ' . htmlspecialchars($history['medications']);
                            if (!empty($history['systemic_history'])) $oldFormatNotes[] = 'Systemic: ' . htmlspecialchars($history['systemic_history']);
                            if (!empty($history['ocular_history'])) $oldFormatNotes[] = 'Ocular: ' . htmlspecialchars($history['ocular_history']);
                            if (!empty($history['prior_surgeries'])) $oldFormatNotes[] = 'Surgeries: ' . htmlspecialchars($history['prior_surgeries']);
                            if (!empty($history['family_history'])) $oldFormatNotes[] = 'Family: ' . htmlspecialchars($history['family_history']);
                            if (!empty($oldFormatNotes)) {
                                $notesText = htmlspecialchars(implode(' | ', $oldFormatNotes));
                            }
                        }
                        if (!empty($notesText)): 
                            // Highlight all titles before ":" with dodgerblue color and bold
                            // Match common medical history titles followed by colon and space
                            $titles = [
                                'Chief Complaint', 'Plan', 'History of Present Illness', 
                                'Allergies', 'Medications', 'Systemic', 'Ocular', 
                                'Surgeries', 'Family', 'Diagnosis', 'Treatment'
                            ];
                            
                            // Create pattern that matches titles followed by colon (with word boundary)
                            foreach ($titles as $title) {
                                $pattern = '/\b(' . preg_quote($title, '/') . '):\s*/i';
                                $notesText = preg_replace($pattern, '<strong style="color: dodgerblue;">$1:</strong> ', $notesText);
                            }
                        ?>
                            <p class="medical-history-card__desc"><?= $notesText ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="medical-history-dots" id="medicalHistoryDots"></div>
</div>
<?php endif; ?>
<!-- Forum Topics Section -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-chat-dots me-2"></i>Forum Topics
        </h5>
    </div>
    <div class="card-body">
        <div id="appointmentForumTopics">
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
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
    appointmentDate: '<?= isset($appointment['date']) ? date('Y-m-d', strtotime($appointment['date'])) : 'null' ?>',
    appointmentTime: '<?= isset($appointment['start_time']) ? date('H:i:s', strtotime($appointment['start_time'])) : 'null' ?>',
    appointmentStatus: '<?= isset($appointment['status']) ? htmlspecialchars($appointment['status']) : 'null' ?>',
    patientId: <?= isset($patient['id']) ? (int)$patient['id'] : 'null' ?>,
    patientFirstName: '<?= isset($patient['first_name']) ? htmlspecialchars($patient['first_name']) : 'null' ?>',
    patientLastName: '<?= isset($patient['last_name']) ? htmlspecialchars($patient['last_name']) : 'null' ?>',
    patientName: '<?= isset($patient['first_name']) && isset($patient['last_name']) ? htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) : 'null' ?>',
    patientPhone: '<?= isset($patient['phone']) ? htmlspecialchars($patient['phone']) : 'null' ?>',
    patientAge: <?= isset($patient['dob']) ? (int)date_diff(date_create($patient['dob']), date_create('now'))->y : 'null' ?>,
    doctorId: <?= isset($appointment['doctor_id']) ? (int)$appointment['doctor_id'] : 'null' ?>,
    followupAppointment: <?= !empty($followupAppointment) ? 'true' : 'false' ?>,
    followupAppointmentDate: <?= !empty($followupAppointment) && isset($followupAppointment['date']) ? "'" . date('Y-m-d', strtotime($followupAppointment['date'])) . "'" : 'null' ?>,
    followupAppointmentTime: <?= !empty($followupAppointment) && isset($followupAppointment['start_time']) ? "'" . date('H:i:s', strtotime($followupAppointment['start_time'])) . "'" : 'null' ?>
};
</script>
<script src="/app/Views/doctor/assets/js/appointment.js?v=<?= file_exists(__DIR__ . '/assets/js/appointment.js') ? filemtime(__DIR__ . '/assets/js/appointment.js') : time() ?>"></script>
<script>
    // Medications are loaded with prices from API via reloadMedications() when needed
</script>
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
<script>
    // Auto-detect patient ID for IOP Trend Analyzer
    (function() {
        const patientId = <?= json_encode($appointment['patient_id'] ?? $patient['id'] ?? null) ?>;
        if (patientId) {
            window.currentPatientId = patientId;
        }
    })();
</script>