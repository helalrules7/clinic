<link href="/app/Views/doctor/assets/css/patient.css?v=<?= file_exists(__DIR__ . '/assets/css/patient.css') ? filemtime(__DIR__ . '/assets/css/patient.css') : time() ?>" rel="stylesheet">

<!-- Breadcrumb -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/doctor/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/doctor/patients">Patients</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></li>
        </ol>
    </nav>
</div>

<!-- Patient Profile Header -->
<div class="row mb-4">
    <div class="col-12 col-md-8">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center">
            <div class="avatar-circle-large <?= $patient['gender'] === 'Female' ? 'avatar-large-female' : 'avatar-large-male' ?> me-3 mb-3 mb-sm-0">
                <?php
                $firstName = $patient['first_name'];
                $lastName = $patient['last_name'];
                
                // Handle Arabic and English names properly
                $firstChar = mb_substr($firstName, 0, 1, 'UTF-8');
                $lastChar = mb_substr($lastName, 0, 1, 'UTF-8');
                
                // Convert to uppercase using mb_strtoupper for proper UTF-8 handling
                echo mb_strtoupper($firstChar . '.' . $lastChar, 'UTF-8');
                ?>
            </div>
            <div class="flex-grow-1">
                <h2 class="text-primary mb-1 h4 h-md-2"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
                <p class="text-muted mb-0 small">Patient ID: #<?= $patient['id'] ?></p>
                <?php if ($patient['dob']): ?>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('M j, Y', strtotime($patient['dob'])) ?> 
                        (<?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> years old)
                    </small>
                <?php endif; ?>
                
                <!-- Current Doctor Badge -->
                <?php if (isset($currentDoctor) && $currentDoctor): ?>
                <div class="mt-2 mt-md-3">
                    <span class="badge doctor-badge fs-6 px-4 py-2 d-inline-flex align-items-center">
                        <?php if (!empty($currentDoctor['profile_image'])): 
                            $doctorImagePath = strpos($currentDoctor['profile_image'], '/public/') === 0 ? $currentDoctor['profile_image'] : '/public' . $currentDoctor['profile_image'];
                        ?>
                            <img src="<?= htmlspecialchars($doctorImagePath) ?>" 
                                 alt="<?= htmlspecialchars($currentDoctor['display_name'] ?? $currentDoctor['name']) ?>" 
                                 class="treating-doctor-avatar me-2"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="treating-doctor-avatar-fallback me-2" style="display: none;">
                                <?= strtoupper(substr($currentDoctor['display_name'] ?? $currentDoctor['name'] ?? 'D', 0, 1)) ?>
                            </div>
                        <?php else: ?>
                            <div class="treating-doctor-avatar me-2">
                                <?= strtoupper(substr($currentDoctor['display_name'] ?? $currentDoctor['name'] ?? 'D', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <strong>Treating Doctor:</strong> 
                        <span class="ms-1"><?= htmlspecialchars($currentDoctor['display_name'] ?? $currentDoctor['name']) ?></span>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Patient Information Cards -->
<div class="row mb-4">
    <!-- Contact Information -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-telephone me-2"></i>
                    Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>Phone:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></div>
                </div>
                <?php if ($patient['alt_phone']): ?>
                <div class="row mt-2">
                    <div class="col-sm-4"><strong>Alt Phone:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['alt_phone']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($patient['address']): ?>
                <div class="row mt-2">
                    <div class="col-sm-4"><strong>Address:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['address']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($patient['national_id']): ?>
                <div class="row mt-2">
                    <div class="col-sm-4"><strong>National ID:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['national_id']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="btn-group-responsive d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" 
                            onclick="bookNewAppointment(<?= $patient['id'] ?>)"
                                    data-bs-toggle="tooltip" 
                            data-bs-placement="bottom" 
                            data-bs-title="Schedule a new appointment for this patient">
                        <i class="bi bi-calendar-plus me-2"></i>
                        <span class="d-none d-lg-inline">Book Appointment</span>
                        <span class="d-lg-none">Book</span>
                            </button>
                    <button class="btn btn-success" 
                            onclick="printPatientSummary()"
                                data-bs-toggle="tooltip" 
                            data-bs-placement="bottom" 
                            data-bs-title="Print patient summary report">
                        <i class="bi bi-printer me-2"></i>
                        <span class="d-none d-lg-inline">Print Summary</span>
                        <span class="d-lg-none">Print</span>
                    </button>
                    <button class="btn btn-info" 
                            onclick="exportPatientData()"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="bottom" 
                            data-bs-title="Export patient data to file">
                        <i class="bi bi-download me-2"></i>
                        <span class="d-none d-lg-inline">Export Data</span>
                        <span class="d-lg-none">Export</span>
                    </button>
                    <button class="btn btn-outline-secondary" 
                            onclick="editPatient(<?= $patient['id'] ?>)"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="bottom" 
                            data-bs-title="Edit patient information and details">
                        <i class="bi bi-pencil me-2"></i>
                        <span class="d-none d-lg-inline">Edit Patient</span>
                        <span class="d-lg-none">Edit</span>
                    </button>
                    <button class="btn btn-warning" 
                            onclick="openAlertModal(<?= $patient['id'] ?>, null)"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="bottom" 
                            data-bs-title="Create an alert for this patient">
                        <i class="bi bi-bell me-2"></i>
                        <span class="d-none d-lg-inline">Set Alert</span>
                        <span class="d-lg-none">Alert</span>
                        </button>
                    </div>
            </div>
        </div>
    </div>
</div>

<!-- Prescriptions History -->
<?php if (!empty($allMedications) || !empty($allGlasses)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-prescription2 me-2"></i>
            Prescriptions History
        </h5>
    </div>
    <div class="card-body">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="prescriptionsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="medications-tab" data-bs-toggle="tab" data-bs-target="#medications" type="button" role="tab">
                    <i class="bi bi-capsule me-2"></i>Medication Prescriptions
                    <?php if (!empty($allMedications)): ?>
                        <span class="badge bg-success ms-2"><?= count($allMedications) ?></span>
            <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="glasses-tab" data-bs-toggle="tab" data-bs-target="#glasses" type="button" role="tab">
                    <i class="bi bi-eyeglasses me-2"></i>Glass Prescriptions
                    <?php if (!empty($allGlasses)): ?>
                        <span class="badge bg-info ms-2"><?= count($allGlasses) ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-prescriptions" type="button" role="tab">
                    <i class="bi bi-list-ul me-2"></i>All Prescriptions
                    <span class="badge bg-primary ms-2"><?= count($allMedications) + count($allGlasses) ?></span>
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="prescriptionsTabsContent">
            <!-- Medications Tab -->
            <div class="tab-pane fade show active" id="medications" role="tabpanel">
                <?php if (!empty($allMedications)): ?>
            <?php 
                    // Group medications by appointment
                    $groupedMedications = [];
                    foreach ($allMedications as $med) {
                        $appointmentId = $med['appointment_id'];
                        if (!isset($groupedMedications[$appointmentId])) {
                            $groupedMedications[$appointmentId] = [
                                'appointment_info' => [
                                    'id' => $med['appointment_id'],
                                    'date' => $med['appointment_date'],
                                    'time' => $med['appointment_time'],
                                    'doctor_name' => $med['doctor_display_name'] ?? $med['doctor_name'] ?? null,
                                    'status' => $med['appointment_status'] ?? null
                                ],
                                'medications' => []
                            ];
                        }
                        $groupedMedications[$appointmentId]['medications'][] = $med;
                    }
                    ?>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-outline-primary btn-sm" id="expandAllMedicationsBtn" onclick="expandCollapseAllMedications()">
                            <i class="bi bi-chevron-double-down me-1"></i><span id="expandAllMedicationsText">Expand All</span>
                </button>
                    </div>
                    <div class="prescription-timeline">
            <?php 
                        $medicationIndex = 0;
                        $totalMedications = count($groupedMedications);
                        foreach ($groupedMedications as $appointmentId => $group): 
                            $isLatest = ($medicationIndex === 0);
                            $medicationIndex++;
                            $collapseId = 'medicationCollapse' . $appointmentId;
                        ?>
                        <div class="timeline-item prescription-timeline-item">
                            <div class="timeline-marker <?= $isLatest ? 'bg-warning' : 'bg-success' ?>" 
                                 onclick="handlePrescriptionHeaderClick(event, '<?= $collapseId ?>')"
                                 style="cursor: pointer; transition: transform 0.2s ease;"
                                 onmouseover="this.style.transform='scale(1.1)'"
                                 onmouseout="this.style.transform='scale(1)'">
                                <i class="bi bi-calendar-event text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header prescription-header <?= $isLatest ? 'expanded' : 'collapsed' ?>" 
                                     data-bs-target="#<?= $collapseId ?>"
                                     aria-expanded="<?= $isLatest ? 'true' : 'false' ?>"
                                     aria-controls="<?= $collapseId ?>"
                                     onclick="handlePrescriptionHeaderClick(event, '<?= $collapseId ?>')"
                                     style="cursor: pointer;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="bi bi-calendar-event me-2 text-success"></i>
                                            Appointment #<?= $group['appointment_info']['id'] ?>
                                            <?php if ($isLatest): ?>
                                            <span class="badge bg-warning text-dark ms-2">Latest Prescription</span>
            <?php endif; ?>
                                            <span class="badge bg-success ms-2"><?= count($group['medications']) ?> Medication<?= count($group['medications']) > 1 ? 's' : '' ?></span>
                                        </h6>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= date('M d, Y', strtotime($group['appointment_info']['date'])) ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('g:i A', strtotime($group['appointment_info']['time'])) ?>
                                            </small>
                                            <?php if (!empty($group['appointment_info']['doctor_name'])): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-person-badge me-1"></i>
                                                <?= htmlspecialchars($group['appointment_info']['doctor_name']) ?>
                                            </small>
                    <?php endif; ?>
    </div>
                        </div>
                                    <div class="btn-group btn-group-sm" role="group" onclick="event.stopPropagation(); event.preventDefault();">
                                        <?php if (count($group['medications']) > 0): ?>
                                        <button class="btn btn-outline-primary btn-sm" 
                                                data-medications-data='<?= htmlspecialchars(json_encode($group['medications']), ENT_QUOTES) ?>'
                                                data-appointment-id="<?= $group['appointment_info']['id'] ?>"
                                                data-appointment-date="<?= $group['appointment_info']['date'] ?>"
                                                data-appointment-time="<?= $group['appointment_info']['time'] ?>"
                                                data-doctor-name="<?= htmlspecialchars($group['appointment_info']['doctor_name'] ?? '') ?>"
                                                onclick="event.stopPropagation(); event.preventDefault(); viewAllMedicationsForAppointment(this)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="View All Prescriptions">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                <?php endif; ?>
                                        <a href="/doctor/appointments/<?= $group['appointment_info']['id'] ?>" 
                                           class="btn btn-outline-info btn-sm"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top"
                                           data-bs-title="View Appointment"
                                           onclick="event.stopPropagation();">
                                            <i class="bi bi-calendar-event"></i>
                                        </a>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="event.stopPropagation(); printMedicationPrescription(<?= $group['appointment_info']['id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="Print Prescription">
                                            <i class="bi bi-printer"></i>
                                </button>
                            </div>
                                    <i class="bi bi-chevron-down collapse-icon ms-2"></i>
                        </div>
                                <div id="<?= $collapseId ?>" 
                                     class="timeline-body collapse <?= $isLatest ? 'show' : '' ?>" 
                                     data-bs-parent=".prescription-timeline">
                                    <?php if (count($group['medications']) > 1): ?>
                                    <div class="medications-list mt-3">
                                        <?php foreach ($group['medications'] as $index => $med): ?>
                                        <div class="medication-item p-3 mb-2 rounded" style="background: var(--bg-alt); border-left: 3px solid var(--success);">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 text-success">
                                                    <i class="bi bi-capsule me-2"></i>
                                                    <?= htmlspecialchars($med['drug_name']) ?>
                                                </h6>
                                                <?php if (!empty($med['notes'])): ?>
                                                <p class="mb-0 text-muted small">
                                                    <i class="bi bi-sticky me-1"></i>
                                                    <?= htmlspecialchars($med['notes']) ?>
                                                </p>
                        <?php endif; ?>
                    </div>
                            </div>
                                        <?php endforeach; ?>
                            </div>
                                    <?php endif; ?>
                            </div>
                            </div>
                            </div>
                        <?php endforeach; ?>
                                </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-capsule text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No medication prescriptions found</p>
                            </div>
                        <?php endif; ?>
            </div>
            
            <!-- Glasses Tab -->
            <div class="tab-pane fade" id="glasses" role="tabpanel">
                <?php if (!empty($allGlasses)): ?>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-outline-primary btn-sm" id="expandAllGlassesBtn" onclick="expandCollapseAllGlasses()">
                            <i class="bi bi-chevron-double-down me-1"></i><span id="expandAllGlassesText">Expand All</span>
                                        </button>
                                    </div>
                    <div class="prescription-timeline">
                                                        <?php
                        $glassesIndex = 0;
                        $totalGlasses = count($allGlasses);
                        foreach ($allGlasses as $glass): 
                            $isLatest = ($glassesIndex === 0);
                            $glassesIndex++;
                            $collapseId = 'glassesCollapse' . $glass['id'];
                        ?>
                        <div class="timeline-item prescription-timeline-item">
                            <div class="timeline-marker <?= $isLatest ? 'bg-warning' : 'bg-info' ?>" 
                                 onclick="handlePrescriptionHeaderClick(event, '<?= $collapseId ?>')"
                                 style="cursor: pointer; transition: transform 0.2s ease;"
                                 onmouseover="this.style.transform='scale(1.1)'"
                                 onmouseout="this.style.transform='scale(1)'">
                                <i class="bi bi-eyeglasses text-white"></i>
                                                        </div>
                            <div class="timeline-content">
                                <div class="timeline-header prescription-header <?= $isLatest ? 'expanded' : 'collapsed' ?>" 
                                     data-bs-target="#<?= $collapseId ?>"
                                     aria-expanded="<?= $isLatest ? 'true' : 'false' ?>"
                                     aria-controls="<?= $collapseId ?>"
                                     onclick="handlePrescriptionHeaderClick(event, '<?= $collapseId ?>')"
                                     style="cursor: pointer;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="bi bi-eyeglasses me-2 text-info"></i>
                                            <?= htmlspecialchars($glass['lens_type'] ?? 'Glasses Prescription') ?>
                                            <?php if ($isLatest): ?>
                                            <span class="badge bg-warning text-dark ms-2">Latest Prescription</span>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= date('M d, Y', strtotime($glass['appointment_date'])) ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('g:i A', strtotime($glass['appointment_time'])) ?>
                                            </small>
                                            <?php if (!empty($glass['doctor_display_name']) || !empty($glass['doctor_name'])): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-person-badge me-1"></i>
                                                <?= htmlspecialchars($glass['doctor_display_name'] ?? $glass['doctor_name']) ?>
                                            </small>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                Appointment #<?= $glass['appointment_id'] ?>
                                            </small>
                                                    </div>
                                                </div>
                                    <div class="btn-group btn-group-sm" role="group" onclick="event.stopPropagation(); event.preventDefault();">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                data-glass-id="<?= $glass['id'] ?>"
                                                data-glass-data='<?= htmlspecialchars(json_encode($glass), ENT_QUOTES) ?>'
                                                onclick="event.stopPropagation(); event.preventDefault(); viewGlassesDetails(this)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="event.stopPropagation(); printGlassesPrescription(<?= $glass['appointment_id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="Print Prescription">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                                        </div>
                                    <i class="bi bi-chevron-down collapse-icon ms-2"></i>
                                                    </div>
                                <div id="<?= $collapseId ?>" 
                                     class="timeline-body collapse <?= $isLatest ? 'show' : '' ?>" 
                                     data-bs-parent=".prescription-timeline">
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-2">Right Eye (OD)</h6>
                                            <p class="mb-1 small">
                                                <strong>SPH:</strong> <?= htmlspecialchars($glass['distance_sphere_r'] ?? 'N/A') ?><br>
                                                <strong>CYL:</strong> <?= htmlspecialchars($glass['distance_cylinder_r'] ?? 'N/A') ?><br>
                                                <strong>AXIS:</strong> <?= htmlspecialchars($glass['distance_axis_r'] ?? 'N/A') ?>
                                            </p>
                                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-2">Left Eye (OS)</h6>
                                            <p class="mb-1 small">
                                                <strong>SPH:</strong> <?= htmlspecialchars($glass['distance_sphere_l'] ?? 'N/A') ?><br>
                                                <strong>CYL:</strong> <?= htmlspecialchars($glass['distance_cylinder_l'] ?? 'N/A') ?><br>
                                                <strong>AXIS:</strong> <?= htmlspecialchars($glass['distance_axis_l'] ?? 'N/A') ?>
                                            </p>
                                                    </div>
                                                        </div>
                                    <?php if (!empty($glass['comments'])): ?>
                                    <p class="mb-0 mt-2 text-muted small">
                                        <i class="bi bi-sticky me-1"></i>
                                        <?= htmlspecialchars($glass['comments']) ?>
                                    </p>
                                    <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                        <?php endforeach; ?>
                                        </div>
                                                                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-eyeglasses text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No glasses prescriptions found</p>
                                                    </div>
                                                <?php endif; ?>
                    </div>
                    
            <!-- All Prescriptions Tab -->
            <div class="tab-pane fade" id="all-prescriptions" role="tabpanel">
                <?php if (!empty($allMedications) || !empty($allGlasses)): ?>
                        <?php 
                    // Group all prescriptions by appointment
                    $groupedAllPrescriptions = [];
                    
                    // Add medications
                    foreach ($allMedications as $med) {
                        $appointmentId = $med['appointment_id'];
                        if (!isset($groupedAllPrescriptions[$appointmentId])) {
                            $groupedAllPrescriptions[$appointmentId] = [
                                'appointment_info' => [
                                    'id' => $med['appointment_id'],
                                    'date' => $med['appointment_date'],
                                    'time' => $med['appointment_time'],
                                    'doctor_name' => $med['doctor_display_name'] ?? $med['doctor_name'] ?? null,
                                    'status' => $med['appointment_status'] ?? null
                                ],
                                'medications' => [],
                                'glasses' => []
                            ];
                        }
                        $groupedAllPrescriptions[$appointmentId]['medications'][] = $med;
                    }
                    
                    // Add glasses
                    foreach ($allGlasses as $glass) {
                        $appointmentId = $glass['appointment_id'];
                        if (!isset($groupedAllPrescriptions[$appointmentId])) {
                            $groupedAllPrescriptions[$appointmentId] = [
                                'appointment_info' => [
                                    'id' => $glass['appointment_id'],
                                    'date' => $glass['appointment_date'],
                                    'time' => $glass['appointment_time'],
                                    'doctor_name' => $glass['doctor_display_name'] ?? $glass['doctor_name'] ?? null,
                                    'status' => $glass['appointment_status'] ?? null
                                ],
                                'medications' => [],
                                'glasses' => []
                            ];
                        }
                        $groupedAllPrescriptions[$appointmentId]['glasses'][] = $glass;
                    }
                    
                    // Sort by appointment date (newest first)
                    uasort($groupedAllPrescriptions, function($a, $b) {
                        return strtotime($b['appointment_info']['date'] . ' ' . $b['appointment_info']['time']) - 
                               strtotime($a['appointment_info']['date'] . ' ' . $a['appointment_info']['time']);
                    });
                    ?>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-outline-primary btn-sm" id="expandAllPrescriptionsBtn" onclick="expandCollapseAllPrescriptions()">
                            <i class="bi bi-chevron-double-down me-1"></i><span id="expandAllPrescriptionsText">Expand All</span>
                        </button>
                    </div>
                    <div class="prescription-timeline">
                        <?php 
                        $allPrescriptionsIndex = 0;
                        $totalAllPrescriptions = count($groupedAllPrescriptions);
                        foreach ($groupedAllPrescriptions as $appointmentId => $group): 
                            $isLatest = ($allPrescriptionsIndex === 0);
                            $allPrescriptionsIndex++;
                            $collapseId = 'allPrescriptionCollapse' . $appointmentId;
                            $totalPrescriptions = count($group['medications']) + count($group['glasses']);
                        ?>
                        <div class="timeline-item prescription-timeline-item">
                            <div class="timeline-marker <?= $isLatest ? 'bg-warning' : 'bg-primary' ?>" 
                                 onclick="handlePrescriptionHeaderClick(event, '<?= $collapseId ?>')"
                                 style="cursor: pointer; transition: transform 0.2s ease;"
                                 onmouseover="this.style.transform='scale(1.1)'"
                                 onmouseout="this.style.transform='scale(1)'">
                                <i class="bi bi-calendar-event text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header prescription-header <?= $isLatest ? 'expanded' : 'collapsed' ?>" 
                                     data-bs-target="#<?= $collapseId ?>"
                                     aria-expanded="<?= $isLatest ? 'true' : 'false' ?>"
                                     aria-controls="<?= $collapseId ?>"
                                     onclick="handlePrescriptionHeaderClick(event, '<?= $collapseId ?>')"
                                     style="cursor: pointer;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                                            Appointment #<?= $group['appointment_info']['id'] ?>
                                            <?php if ($isLatest): ?>
                                            <span class="badge bg-warning text-dark ms-2">Latest Prescription</span>
                        <?php endif; ?>
                                            <?php if ($totalPrescriptions > 0): ?>
                                            <span class="badge bg-primary ms-2">
                                                <?= $totalPrescriptions ?> Prescription<?= $totalPrescriptions > 1 ? 's' : '' ?>
                                            </span>
                            <?php endif; ?>
                                        </h6>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= date('M d, Y', strtotime($group['appointment_info']['date'])) ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('g:i A', strtotime($group['appointment_info']['time'])) ?>
                                            </small>
                                            <?php if (!empty($group['appointment_info']['doctor_name'])): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-person-badge me-1"></i>
                                                <?= htmlspecialchars($group['appointment_info']['doctor_name']) ?>
                                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                                    <div class="btn-group btn-group-sm" role="group" onclick="event.stopPropagation(); event.preventDefault();">
                                        <?php if (!empty($group['medications']) && count($group['medications']) > 0): ?>
                                        <button class="btn btn-outline-primary btn-sm" 
                                                data-medications-data='<?= htmlspecialchars(json_encode($group['medications']), ENT_QUOTES) ?>'
                                                data-appointment-id="<?= $group['appointment_info']['id'] ?>"
                                                data-appointment-date="<?= $group['appointment_info']['date'] ?>"
                                                data-appointment-time="<?= $group['appointment_info']['time'] ?>"
                                                data-doctor-name="<?= htmlspecialchars($group['appointment_info']['doctor_name'] ?? '') ?>"
                                                onclick="event.stopPropagation(); event.preventDefault(); viewAllMedicationsForAppointment(this)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top"
                                                data-bs-title="View All Medication Prescriptions">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php endif; ?>
                                        <a href="/doctor/appointments/<?= $group['appointment_info']['id'] ?>" 
                                           class="btn btn-outline-info btn-sm"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top"
                                           data-bs-title="View Appointment"
                                           onclick="event.stopPropagation();">
                                            <i class="bi bi-calendar-event"></i>
                                        </a>
                                        <?php if (!empty($group['medications'])): ?>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="event.stopPropagation(); printMedicationPrescription(<?= $group['appointment_info']['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top"
                                                data-bs-title="Print Medication Prescription">
                                            <i class="bi bi-printer"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!empty($group['glasses'])): ?>
                                        <button class="btn btn-outline-info btn-sm" 
                                                onclick="event.stopPropagation(); printGlassesPrescription(<?= $group['appointment_info']['id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top"
                                                data-bs-title="Print Glasses Prescription">
                                            <i class="bi bi-printer"></i>
                                        </button>
                    <?php endif; ?>
                </div>
                                    <i class="bi bi-chevron-down collapse-icon ms-2"></i>
            </div>
                                <div id="<?= $collapseId ?>" 
                                     class="timeline-body collapse <?= $isLatest ? 'show' : '' ?>" 
                                     data-bs-parent=".prescription-timeline">
                                    <!-- Medications Section -->
                                    <?php if (!empty($group['medications']) && count($group['medications']) > 1): ?>
                                    <div class="mb-3">
                                        <h6 class="text-success mb-2">
                                            <i class="bi bi-capsule me-2"></i>Medications
                                            <span class="badge bg-success ms-2"><?= count($group['medications']) ?></span>
                            </h6>
                                        <div class="medications-list">
                                            <?php foreach ($group['medications'] as $med): ?>
                                            <div class="medication-item p-3 mb-2 rounded" style="background: var(--bg-alt); border-left: 3px solid var(--success);">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-success">
                                                        <i class="bi bi-capsule me-2"></i>
                                                        <?= htmlspecialchars($med['drug_name']) ?>
                                                    </h6>
                                                    <?php if (!empty($med['notes'])): ?>
                                                    <p class="mb-0 text-muted small">
                                                        <i class="bi bi-sticky me-1"></i>
                                                        <?= htmlspecialchars($med['notes']) ?>
                                                    </p>
                            <?php endif; ?>
                        </div>
                    </div>
                                            <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                                    <!-- Glasses Section -->
                                    <?php if (!empty($group['glasses'])): ?>
                                    <div class="mb-3">
                                        <h6 class="text-info mb-2">
                                            <i class="bi bi-eyeglasses me-2"></i>Glasses Prescriptions
                                            <span class="badge bg-info ms-2"><?= count($group['glasses']) ?></span>
                                        </h6>
                                        <div class="glasses-list">
                                            <?php foreach ($group['glasses'] as $glass): ?>
                                            <div class="glasses-item p-3 mb-2 rounded" style="background: var(--bg-alt); border-left: 3px solid var(--accent);">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-info">
                                                        <i class="bi bi-eyeglasses me-2"></i>
                                                        <?= htmlspecialchars($glass['lens_type'] ?? 'Glasses Prescription') ?>
                                                    </h6>
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <small class="text-muted">OD: SPH <?= htmlspecialchars($glass['distance_sphere_r'] ?? 'N/A') ?>, CYL <?= htmlspecialchars($glass['distance_cylinder_r'] ?? 'N/A') ?>, AXIS <?= htmlspecialchars($glass['distance_axis_r'] ?? 'N/A') ?></small>
                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="text-muted">OS: SPH <?= htmlspecialchars($glass['distance_sphere_l'] ?? 'N/A') ?>, CYL <?= htmlspecialchars($glass['distance_cylinder_l'] ?? 'N/A') ?>, AXIS <?= htmlspecialchars($glass['distance_axis_l'] ?? 'N/A') ?></small>
                                    </div>
                                </div>
                                                    <?php if (!empty($glass['comments'])): ?>
                                                    <p class="mb-0 mt-2 text-muted small">
                                                        <i class="bi bi-sticky me-1"></i>
                                                        <?= htmlspecialchars($glass['comments']) ?>
                                                    </p>
                            <?php endif; ?>
                                        </div>
                                    </div>
                                            <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                            </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-prescription2 text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No prescriptions found</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
<!-- Appointment History -->
<?php if (!empty($allAppointments)): ?>
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-check me-2"></i>
                Appointment History
                <span class="badge bg-primary ms-2"><?= count($allAppointments) ?></span>
            </h5>
            <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-primary btn-sm" id="expandAllAppointmentsBtn" onclick="expandCollapseAllAppointments()">
                    <i class="bi bi-chevron-double-down me-1"></i><span id="expandAllAppointmentsText">Expand All</span>
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="bookNewAppointment(<?= $patient['id'] ?>)">
                    <i class="bi bi-calendar-plus me-1"></i>Book New Appointment
                </button>
                    </div>
                </div>
    </div>
    <div class="card-body">
        <div class="appointment-timeline">
                            <?php
            $appointmentIndex = 0;
            $totalAppointments = count($allAppointments);
            foreach ($allAppointments as $index => $appointment): 
                $isLatest = ($appointmentIndex === 0);
                $appointmentIndex++;
                $collapseId = 'appointmentCollapse' . $appointment['id'];
                $statusColor = $appointment['status'] === 'Completed' ? 'success' : ($appointment['status'] === 'Cancelled' ? 'danger' : ($appointment['status'] === 'InProgress' ? 'warning' : 'primary'));
                $isFollowup = !empty($appointment['is_followup']) && $appointment['is_followup'] === true;
            ?>
            <div class="timeline-item appointment-timeline-item <?= $isFollowup ? 'followup-appointment' : '' ?>">
                <div class="timeline-marker <?= $isLatest ? 'bg-warning' : 'bg-' . $statusColor ?>" 
                     onclick="handleAppointmentHeaderClick(event, '<?= $collapseId ?>')"
                     style="cursor: pointer; transition: transform 0.2s ease;"
                     onmouseover="this.style.transform='scale(1.1)'"
                     onmouseout="this.style.transform='scale(1)'">
                    <i class="bi bi-calendar-event text-white"></i>
                                    </div>
                        <div class="timeline-content">
                    <div class="timeline-header appointment-header <?= $isLatest ? 'expanded' : 'collapsed' ?>" 
                         data-bs-target="#<?= $collapseId ?>"
                         aria-expanded="<?= $isLatest ? 'true' : 'false' ?>"
                         aria-controls="<?= $collapseId ?>"
                         onclick="handleAppointmentHeaderClick(event, '<?= $collapseId ?>')"
                         style="cursor: pointer;">
                                <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="/doctor/appointments/<?= $appointment['id'] ?>" class="text-decoration-none" onclick="event.stopPropagation();">
                                    Appointment #<?= $appointment['id'] ?>
                                </a>
                                <?php if ($isLatest): ?>
                                <span class="badge bg-warning text-dark ms-2">Latest Appointment</span>
                                    <?php endif; ?>
                                <?php if ($isFollowup): ?>
                                <span class="badge bg-info ms-2">
                                    <i class="bi bi-arrow-return-right me-1"></i>Follow-up
                                </span>
                                    <?php endif; ?>
                                <span class="badge bg-<?= $statusColor ?> ms-2">
                                    <?= ucfirst($appointment['status']) ?>
                                    </span>
                            </h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date('M d, Y', strtotime($appointment['date'])) ?>
                                            </small>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= date('g:i A', strtotime($appointment['start_time'])) ?>
                                    <?php if (!empty($appointment['end_time'])): ?>
                                        - <?= date('g:i A', strtotime($appointment['end_time'])) ?>
                                        <?php endif; ?>
                                </small>
                                <?php if (!empty($appointment['doctor_name']) || !empty($appointment['doctor_display_name'])): ?>
                                <small class="text-muted">
                                    <i class="bi bi-person-badge me-1"></i>
                                    <?= htmlspecialchars($appointment['doctor_display_name'] ?? $appointment['doctor_name']) ?>
                                </small>
                                    <?php endif; ?>
                                <?php if (!empty($appointment['visit_type'])): ?>
                                <small class="text-muted">
                                    <i class="bi bi-tag me-1"></i>
                                    <?= htmlspecialchars($appointment['visit_type']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                        </div>
                        <a href="/doctor/appointments/<?= $appointment['id'] ?>" 
                           class="btn btn-sm btn-outline-primary"
                           onclick="event.stopPropagation();">
                            <i class="bi bi-eye me-1"></i>View Details
                        </a>
                        <i class="bi bi-chevron-down collapse-icon ms-2"></i>
                            </div>
                            
                    <div id="<?= $collapseId ?>" 
                         class="timeline-body collapse <?= $isLatest ? 'show' : '' ?>" 
                         data-bs-parent=".appointment-timeline">
                        <!-- Consultation Notes -->
                        <?php if (!empty($appointment['consultation_note'])): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                                        <h6 class="text-primary mb-2">
                                <i class="bi bi-clipboard-pulse me-2"></i>Consultation Notes
                                        </h6>
                            <?php if (!empty($appointment['consultation_note']['chief_complaint'])): ?>
                            <p class="mb-2">
                                <strong>Chief Complaint:</strong> <?= htmlspecialchars($appointment['consultation_note']['chief_complaint']) ?>
                            </p>
                                                            <?php endif; ?>
                            <?php if (!empty($appointment['consultation_note']['diagnosis'])): ?>
                            <p class="mb-2">
                                <strong>Diagnosis:</strong> 
                                <span class="badge bg-danger"><?= htmlspecialchars($appointment['consultation_note']['diagnosis']) ?></span>
                            </p>
                                                            <?php endif; ?>
                            <?php if (!empty($appointment['consultation_note']['plan'])): ?>
                                                            <p class="mb-0">
                                <strong>Plan:</strong> <?= nl2br(htmlspecialchars($appointment['consultation_note']['plan'])) ?>
                                                            </p>
                                                            <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                        <!-- Medications Prescriptions -->
                        <?php if (!empty($appointment['medications'])): ?>
                                    <div class="mb-3">
                            <h6 class="text-success mb-2">
                                <i class="bi bi-capsule me-2"></i>Medications Prescribed
                                <span class="badge bg-success ms-2"><?= count($appointment['medications']) ?></span>
                                        </h6>
                            <div class="row g-2">
                                <?php foreach ($appointment['medications'] as $med): ?>
                                <div class="col-md-6">
                                    <div class="card border-success border-start border-3">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1 text-success"><?= htmlspecialchars($med['drug_name']) ?></h6>
                                            <?php if (!empty($med['notes'])): ?>
                                            <p class="card-text small mb-0 text-muted"><?= htmlspecialchars($med['notes']) ?></p>
                                    <?php endif; ?>
                                        </div>
                                    </div>
                                        </div>
                                <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                        <!-- Glasses Prescriptions -->
                        <?php if (!empty($appointment['glasses'])): ?>
                                    <div class="mb-3">
                            <h6 class="text-info mb-2">
                                <i class="bi bi-eyeglasses me-2"></i>Glasses Prescriptions
                                <span class="badge bg-info ms-2"><?= count($appointment['glasses']) ?></span>
                                        </h6>
                            <?php foreach ($appointment['glasses'] as $glass): ?>
                            <div class="card border-info border-start border-3 mb-2">
                                <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                            <h6 class="text-primary mb-2">Right Eye (OD)</h6>
                                            <p class="mb-1 small">
                                                <strong>SPH:</strong> <?= htmlspecialchars($glass['distance_sphere_r'] ?? 'N/A') ?><br>
                                                <strong>CYL:</strong> <?= htmlspecialchars($glass['distance_cylinder_r'] ?? 'N/A') ?><br>
                                                <strong>AXIS:</strong> <?= htmlspecialchars($glass['distance_axis_r'] ?? 'N/A') ?>
                                            </p>
                                            </div>
                                            <div class="col-md-6">
                                            <h6 class="text-primary mb-2">Left Eye (OS)</h6>
                                            <p class="mb-1 small">
                                                <strong>SPH:</strong> <?= htmlspecialchars($glass['distance_sphere_l'] ?? 'N/A') ?><br>
                                                <strong>CYL:</strong> <?= htmlspecialchars($glass['distance_cylinder_l'] ?? 'N/A') ?><br>
                                                <strong>AXIS:</strong> <?= htmlspecialchars($glass['distance_axis_l'] ?? 'N/A') ?>
                                            </p>
                                            </div>
                                        </div>
                                    <?php if (!empty($glass['lens_type'])): ?>
                                    <p class="mb-0 mt-2">
                                        <strong>Lens Type:</strong> <?= htmlspecialchars($glass['lens_type']) ?>
                                    </p>
                                                    <?php endif; ?>
                                                </div>
                                        </div>
                            <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                        <!-- Attachments -->
                        <?php if (!empty($appointment['attachments'])): ?>
                                    <div class="mb-3">
                            <h6 class="text-info mb-2">
                                <i class="bi bi-paperclip me-2"></i>Attachments
                                <span class="badge bg-info ms-2"><?= count($appointment['attachments']) ?></span>
                            </h6>
                            <div class="row g-2">
                                <?php foreach ($appointment['attachments'] as $attachment): ?>
                                <?php
                                $fileExt = strtolower(pathinfo($attachment['original_filename'], PATHINFO_EXTENSION));
                                $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                $viewUrl = '/api/attachments/view/' . $attachment['id'];
                                ?>
                                <div class="col-md-3 col-sm-4 col-6">
                                    <div class="attachment-thumbnail-card p-2 border rounded" style="background: var(--bg-alt); border-color: var(--border) !important; cursor: pointer;" 
                                         onclick="viewPatientAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>', true)"
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         data-bs-title="View Attachement/Photo">
                                        <?php if ($isImage): ?>
                                        <!-- Thumbnail for images -->
                                        <div class="text-center mb-2">
                                            <img src="<?= htmlspecialchars($viewUrl) ?>" 
                                                 alt="<?= htmlspecialchars($attachment['original_filename']) ?>"
                                                 class="img-thumbnail" 
                                                 style="max-width: 100%; max-height: 80px; object-fit: cover; border-radius: 4px; cursor: pointer; border-color: var(--border);"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div style="display: none; width: 100%; height: 80px; background: var(--bg); border-radius: 4px; align-items: center; justify-content: center; flex-direction: column; border: 1px solid var(--border);">
                                                <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                                <small class="text-muted" style="font-size: 0.65rem;">Image not available</small>
                                            </div>
                                            </div>
                                        <?php else: ?>
                                        <!-- Icon for non-image files -->
                                        <div class="text-center mb-2">
                                            <i class="bi bi-file-earmark text-primary" style="font-size: 2rem;"></i>
                                    </div>
                                    <?php endif; ?>
                                        <div class="text-center">
                                            <small class="text-muted d-block" style="font-size: 0.7rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                                   title="<?= htmlspecialchars($attachment['original_filename']) ?>">
                                                <?= htmlspecialchars(strlen($attachment['original_filename']) > 15 ? substr($attachment['original_filename'], 0, 12) . '...' : $attachment['original_filename']) ?>
                                            </small>
                                            </div>
                                            </div>
                                        </div>
                                <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                        <!-- Notes if available -->
                        <?php if (!empty($appointment['notes'])): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-sticky me-1"></i>
                                <?= htmlspecialchars($appointment['notes']) ?>
                            </small>
                                    </div>
                                    <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
            <?php endforeach; ?>
                                    </div>
                                    </div>
                                    </div>
                                <?php endif; ?>

<!-- Medical History -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-heart me-2"></i>
                Medical History
                <?php if (!empty($medicalHistory)): ?>
                    <span class="badge bg-primary ms-2"><?= count($medicalHistory) ?></span>
                <?php endif; ?>
            </h5>
            <div class="d-flex gap-2">
                <!-- View Toggle Buttons -->
                <?php if (!empty($medicalHistory)): ?>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="timelineViewBtn" onclick="switchMedicalHistoryView('timeline')">
                        <i class="bi bi-clock-history me-1"></i>Timeline
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="detailsViewBtn" onclick="switchMedicalHistoryView('details')">
                        <i class="bi bi-list-ul me-1"></i>Details
                    </button>
                </div>
                <?php endif; ?>
                <button class="btn btn-primary btn-sm" onclick="addMedicalHistory()">
                    <i class="bi bi-plus me-1"></i>Add Entry
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($medicalHistory)): ?>
            <!-- Timeline View -->
            <div id="timelineView" class="medical-history-view">
                <div class="timeline">
                    <?php foreach ($medicalHistory as $index => $history): ?>
                        <div class="timeline-item" data-entry-type="<?= $history['entry_type'] ?>">
                            <div class="timeline-marker bg-primary">
                                <i class="bi bi-clipboard-heart text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php if (!empty($history['condition_name'])): ?>
                                                <?= htmlspecialchars($history['condition_name']) ?>
                                            <?php else: ?>
                                                Medical Record #<?= $history['id'] ?>
                                            <?php endif; ?>
                                            <?php if (!empty($history['status'])): ?>
                                                <span class="badge bg-<?= $history['status'] === 'active' ? 'success' : ($history['status'] === 'resolved' ? 'info' : 'secondary') ?> ms-2">
                                                    <?= ucfirst($history['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </h6>
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
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="viewMedicalHistory(<?= $history['id'] ?>)">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="editMedicalHistory(<?= $history['id'] ?>)">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteMedicalHistory(<?= $history['id'] ?>)">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="timeline-body mt-2">
                                    <?php if (!empty($history['category'])): ?>
                                        <span class="badge bg-light text-dark me-2 mb-2">
                                            <i class="bi bi-tag me-1"></i><?= ucfirst(str_replace('_', ' ', $history['category'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <!-- Display content based on entry type -->
                                    <?php if ($history['entry_type'] === 'new_format'): ?>
                                        <?php if (!empty($history['notes'])): ?>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($history['notes'])) ?></p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Old format display -->
                                        <?php if (!empty($history['allergies'])): ?>
                                            <div class="mb-2">
                                                <strong class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Allergies:</strong>
                                                <span><?= htmlspecialchars($history['allergies']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($history['medications'])): ?>
                                            <div class="mb-2">
                                                <strong class="text-primary"><i class="bi bi-capsule me-1"></i>Medications:</strong>
                                                <span><?= htmlspecialchars($history['medications']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($history['systemic_history'])): ?>
                                            <div class="mb-2">
                                                <strong class="text-info"><i class="bi bi-heart-pulse me-1"></i>Systemic:</strong>
                                                <span><?= htmlspecialchars($history['systemic_history']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($history['ocular_history'])): ?>
                                            <div class="mb-2">
                                                <strong class="text-success"><i class="bi bi-eye me-1"></i>Ocular:</strong>
                                                <span><?= htmlspecialchars($history['ocular_history']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($history['prior_surgeries'])): ?>
                                            <div class="mb-2">
                                                <strong class="text-warning"><i class="bi bi-scissors me-1"></i>Surgeries:</strong>
                                                <span><?= htmlspecialchars($history['prior_surgeries']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($history['family_history'])): ?>
                                            <div class="mb-2">
                                                <strong class="text-secondary"><i class="bi bi-people me-1"></i>Family:</strong>
                                                <span><?= htmlspecialchars($history['family_history']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Details View -->
            <div id="detailsView" class="medical-history-view" style="display: none;">
                <div class="accordion" id="medicalHistoryAccordion">
                    <?php foreach ($medicalHistory as $index => $history): ?>
                        <div class="accordion-item" data-entry-type="<?= $history['entry_type'] ?>">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" 
                                        aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="me-auto">
                                            <strong>
                                                <?php if (!empty($history['condition_name'])): ?>
                                                    <?= htmlspecialchars($history['condition_name']) ?>
                                                <?php else: ?>
                                                    Medical Record #<?= $history['id'] ?>
                                                <?php endif; ?>
                                            </strong>
                                            <small class="text-muted ms-2">
                                                <?php if (!empty($history['diagnosis_date'])): ?>
                                                    <?= date('M d, Y', strtotime($history['diagnosis_date'])) ?>
                                                <?php else: ?>
                                                    <?= date('M d, Y', strtotime($history['created_at'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <?php if (!empty($history['status'])): ?>
                                            <span class="badge bg-<?= $history['status'] === 'active' ? 'success' : ($history['status'] === 'resolved' ? 'info' : 'secondary') ?> me-3">
                                                <?= ucfirst($history['status']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                 aria-labelledby="heading<?= $index ?>" data-bs-parent="#medicalHistoryAccordion">
                                <div class="accordion-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <?php if (!empty($history['category'])): ?>
                                                <span class="badge bg-light text-dark me-2">
                                                    <i class="bi bi-tag me-1"></i><?= ucfirst(str_replace('_', ' ', $history['category'])) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($history['doctor_name'])): ?>
                                                <small class="text-muted">Added by <?= htmlspecialchars($history['doctor_name']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" onclick="viewMedicalHistory(<?= $history['id'] ?>)" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="View full details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-primary" onclick="editMedicalHistory(<?= $history['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="Edit entry">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteMedicalHistory(<?= $history['id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="Delete entry">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Content based on entry type -->
                                    <?php if ($history['entry_type'] === 'new_format'): ?>
                                        <?php if (!empty($history['notes'])): ?>
                                            <div class="mb-3">
                                                <h6><i class="bi bi-file-text me-2"></i>Notes</h6>
                                                <p><?= nl2br(htmlspecialchars($history['notes'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Old format detailed display -->
                                        <div class="row">
                                            <?php if (!empty($history['allergies'])): ?>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-danger">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Allergies
                                                </h6>
                                                <p><?= htmlspecialchars($history['allergies']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($history['medications'])): ?>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-primary">
                                                    <i class="bi bi-capsule me-1"></i>Current Medications
                                                </h6>
                                                <p><?= htmlspecialchars($history['medications']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($history['systemic_history'])): ?>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-info">
                                                    <i class="bi bi-heart-pulse me-1"></i>Systemic History
                                                </h6>
                                                <p><?= htmlspecialchars($history['systemic_history']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($history['ocular_history'])): ?>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-success">
                                                    <i class="bi bi-eye me-1"></i>Ocular History
                                                </h6>
                                                <p><?= htmlspecialchars($history['ocular_history']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($history['prior_surgeries'])): ?>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-warning">
                                                    <i class="bi bi-scissors me-1"></i>Prior Surgeries
                                                </h6>
                                                <p><?= htmlspecialchars($history['prior_surgeries']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($history['family_history'])): ?>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-secondary">
                                                    <i class="bi bi-people me-1"></i>Family History
                                                </h6>
                                                <p><?= htmlspecialchars($history['family_history']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        Last updated: <?= date('M d, Y \a\t g:i A', strtotime($history['updated_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="bi bi-clipboard-heart text-muted" style="font-size: 4rem;"></i>
                <h6 class="text-muted mt-3 mb-2">No Medical History</h6>
                <p class="text-muted mb-4">Start building this patient's medical history by adding their first entry.</p>
                <button class="btn btn-primary" onclick="addMedicalHistory()">
                    <i class="bi bi-plus me-2"></i>Add First Entry
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
                                
<!-- Patient Alerts -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-bell me-2"></i>
                Patient Alerts
                <span class="badge bg-warning ms-2" id="patientAlertsCount">0</span>
            </h5>
            <button class="btn btn-primary btn-sm" onclick="openAlertModal(<?= isset($patient['id']) ? (int)$patient['id'] : 'null' ?>, null)">
                <i class="bi bi-plus me-1"></i>Add Alert
            </button>
                                            </div>
                                        </div>
    <div class="card-body">
        <div id="patientAlertsContainer">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                                    </div>
                <p class="text-muted mt-2 mb-0">Loading alerts...</p>
                                                        </div>
                                                        </div>
                                                    </div>
                </div>
                
<!-- Delete Patient Alert Modal -->
<div class="modal fade" id="deletePatientAlertModal" tabindex="-1" aria-labelledby="deletePatientAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                <h5 class="modal-title" id="deletePatientAlertModalLabel">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm Delete
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this alert?</p>
                <p class="text-muted mb-0"><small>This action cannot be undone.</small></p>
                                            </div>
                                            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePatientAlertBtn" onclick="confirmDeletePatientAlert()">
                    <i class="bi bi-trash me-1"></i>Delete Alert
                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

<style>
/* Patient Alerts Dark Mode Styles */
#patientAlertsContainer .list-group-item {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
    transition: all 0.3s ease;
}

#patientAlertsContainer .list-group-item:hover {
    background-color: var(--bg-alt) !important;
    border-color: var(--accent) !important;
}

/* Active Alert Styling */
#patientAlertsContainer .list-group-item.alert-active {
    border-left: 4px solid var(--success) !important;
    background-color: rgba(16, 185, 129, 0.05) !important;
}

.dark #patientAlertsContainer .list-group-item.alert-active {
    border-left: 4px solid var(--success) !important;
    background-color: rgba(74, 222, 128, 0.1) !important;
}

/* Dismissed/Inactive Alert Styling */
#patientAlertsContainer .list-group-item.alert-dismissed,
#patientAlertsContainer .list-group-item.alert-inactive {
    border-left: 4px solid var(--muted) !important;
    opacity: 0.6;
    background-color: rgba(0, 0, 0, 0.02) !important;
}

.dark #patientAlertsContainer .list-group-item.alert-dismissed,
.dark #patientAlertsContainer .list-group-item.alert-inactive {
    background-color: rgba(0, 0, 0, 0.1) !important;
    opacity: 0.5;
}

/* Alert Text Colors */
#patientAlertsContainer .list-group-item h6 {
    color: var(--text) !important;
}

#patientAlertsContainer .list-group-item .text-muted {
    color: var(--muted) !important;
}

/* Alert Icon Colors */
#patientAlertsContainer .list-group-item.alert-active .bi-bell-fill {
    color: var(--success) !important;
    animation: pulse-bell 2s infinite;
}

#patientAlertsContainer .list-group-item.alert-dismissed .bi-bell-fill,
#patientAlertsContainer .list-group-item.alert-inactive .bi-bell-fill {
    color: var(--muted) !important;
}

@keyframes pulse-bell {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* Delete Patient Alert Modal Dark Mode */
#deletePatientAlertModal .modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#deletePatientAlertModal .modal-header {
    background-color: var(--bg-alt) !important;
    border-bottom-color: var(--border) !important;
    color: var(--text) !important;
}

#deletePatientAlertModal .modal-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

#deletePatientAlertModal .modal-footer {
    background-color: var(--bg-alt) !important;
    border-top-color: var(--border) !important;
}

#deletePatientAlertModal .text-muted {
    color: var(--muted) !important;
}

.dark #deletePatientAlertModal .modal-content {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark #deletePatientAlertModal .modal-header {
    background-color: var(--bg-alt) !important;
    border-bottom-color: var(--border) !important;
    color: var(--text) !important;
}

.dark #deletePatientAlertModal .modal-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark #deletePatientAlertModal .modal-footer {
    background-color: var(--bg-alt) !important;
    border-top-color: var(--border) !important;
}

.dark #deletePatientAlertModal .text-muted {
    color: var(--muted) !important;
}
</style>

<!-- Recent Appointments -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-calendar-check me-2"></i>
            Recent Appointments
        </h5>
        <span class="badge bg-primary"><?= count($recentAppointments) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentAppointments)): ?>
            <div class="p-4 text-center">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No appointments found</p>
                <button class="btn btn-primary mt-3" 
                        onclick="bookNewAppointment(<?= $patient['id'] ?>)"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        data-bs-title="Schedule the first appointment for this patient">
                    <i class="bi bi-calendar-plus me-2"></i>Book First Appointment
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAppointments as $appointment): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= date('M j, Y', strtotime($appointment['date'])) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('g:i A', strtotime($appointment['start_time'])) ?> - 
                                        <?= date('g:i A', strtotime($appointment['end_time'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($appointment['visit_type']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $this->getStatusBadgeClass($appointment['status']) ?>">
                                        <?= htmlspecialchars($appointment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/doctor/appointments/<?= $appointment['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Patient Files & Attachments -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-paperclip me-2"></i>
                Patient Files & Documents
            </h5>
            <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-primary" 
                        onclick="showPatientUploadModal(<?= $patient['id'] ?>)"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        data-bs-title="Upload files and documents for this patient">
                    <i class="bi bi-cloud-upload me-1"></i>Upload File
                </button>
                <button class="btn btn-success" 
                        onclick="openPatientCameraModal(<?= $patient['id'] ?>)"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        data-bs-title="Take a photo using camera for this patient">
                    <i class="bi bi-camera me-1"></i>Take Photo
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="patientFilesContainer">
        <?php if (!empty($patientAttachments)): ?>
            <div class="row" id="patientFilesRow">
                <?php foreach ($patientAttachments as $attachment): ?>
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
                                if (strpos($fileName, 'xray') !== false || strpos($description, 'xray') !== false) {
                                    $fileType = 'X-Ray';
                                    $badgeClass = 'bg-info';
                                } elseif (strpos($fileName, 'scan') !== false || strpos($description, 'scan') !== false) {
                                    $fileType = 'Scan';
                                    $badgeClass = 'bg-primary';
                                } elseif (strpos($fileName, 'lab') !== false || strpos($description, 'lab') !== false) {
                                    $fileType = 'Lab Result';
                                    $badgeClass = 'bg-success';
                                } else {
                                    $fileType = 'Photo';
                                    $badgeClass = 'bg-warning text-dark';
                                }
                            } elseif ($fileExt == 'pdf') {
                                $iconClass = 'bi-file-earmark-pdf';
                                $fileType = 'PDF Document';
                                $badgeClass = 'bg-danger';
                            }
                            ?>
                <div class="col-md-6 mb-3">
                    <div class="attachment-card p-2 border rounded" data-attachment-id="<?= $attachment['id'] ?>" style="min-height: <?= $isImage ? '200px' : '140px' ?>; display: flex; flex-direction: column;">
                            <?php if ($isImage): ?>
                        <!-- Thumbnail for images -->
                        <div class="mb-2 text-center" style="cursor: pointer;" 
                             onclick="viewPatientAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')"
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
                                    <h6 class="mb-0" style="font-size: 0.8rem; line-height: 1.1;" 
                                        title="<?= htmlspecialchars($originalName) ?>"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top">
                                        <?= htmlspecialchars($displayName) ?>
                                    </h6>
                                    <span class="badge <?= $badgeClass ?> ms-2" style="font-size: 0.6rem;">
                                        <?= $fileType ?>
                                    </span>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.65rem;">
                                    <?= number_format($attachment['file_size'] / 1024, 1) ?> KB
                                </small>
                                <small class="text-muted d-block" style="font-size: 0.65rem;">
                                    <?= date('d/m/Y H:i', strtotime($attachment['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="flex-grow-1">
                            <?php if (!empty($attachment['description'])): ?>
                            <?php 
                            $description = $attachment['description'];
                            $shortDescription = strlen($description) > 40 ? substr($description, 0, 37) . '...' : $description;
                            ?>
                            <p class="text-muted mb-1 small" style="font-size: 0.7rem;"
                               title="<?= htmlspecialchars($description) ?>"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="bottom">
                               <?= htmlspecialchars($shortDescription) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="btn-group btn-group-sm w-100 mt-auto" role="group">
                            <button class="btn btn-outline-primary btn-sm" 
                                    onclick="viewPatientAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')" 
                                    style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="View Attachement/Photo">
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                            <button class="btn btn-outline-success btn-sm" 
                                    onclick="downloadPatientAttachment(<?= $attachment['id'] ?>, '<?= $attachment['original_filename'] ?>')"
                                    style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Download this file to your device">
                                <i class="bi bi-download me-1"></i>Download
                            </button>
                            <button class="btn btn-outline-danger btn-sm" 
                                    onclick="deletePatientAttachment(<?= $attachment['id'] ?>)"
                                    style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Delete this file permanently">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4" id="emptyPatientFilesMessage">
                <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No files or documents found for this patient</p>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<!-- Patient Medical Notes -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-journal-medical me-2"></i>
                Medical Notes
            </h5>
            <button class="btn btn-primary btn-sm" 
                    onclick="showAddPatientNoteModal(<?= $patient['id'] ?>)"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-title="Add a new medical note for this patient">
                <i class="bi bi-plus me-1"></i>Add Note
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($patientNotes)): ?>
            <div class="row">
                <?php foreach ($patientNotes as $note): ?>
                <div class="col-12 mb-3">
                    <div class="note-card">
                        <div class="note-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($note['title']) ?></h6>
                                    <div class="note-meta">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= date('M j, Y \a\t g:i A', strtotime($note['created_at'])) ?>
                                        <?php if (!empty($note['doctor_name'])): ?>
                                        <span class="ms-2">
                                            <i class="bi bi-person me-1"></i>
                                            Dr. <?= htmlspecialchars($note['doctor_name']) ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary edit-note-btn" 
                                            data-note-id="<?= $note['id'] ?>" 
                                            data-note-title="<?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-note-content="<?= htmlspecialchars($note['content'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Edit this medical note">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" 
                                            onclick="deletePatientNote(<?= $note['id'] ?>)"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Delete this medical note permanently">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="note-content">
                            <div class="note-text">
                                <?= nl2br(htmlspecialchars($note['content'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-journal-medical text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No medical notes found for this patient</p>
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
                Glasses Prescriptions
                <?php if (!empty($glassesPrescriptions)): ?>
                    <span class="badge bg-primary ms-2"><?= count($glassesPrescriptions) ?></span>
                <?php endif; ?>
            </h5>
            <button class="btn btn-primary btn-sm" 
                    onclick="showAddGlassesPrescriptionModal(<?= $patient['id'] ?>)"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-title="Add a new glasses prescription for this patient">
                <i class="bi bi-plus me-1"></i>Add Prescription
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($glassesPrescriptions)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Visit</th>
                            <th>Lens Type</th>
                            <th>Distance Vision</th>
                            <th>Near Vision</th>
                            <th>PD</th>
                            <th>Doctor</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($glassesPrescriptions as $prescription): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= date('M j, Y', strtotime($prescription['created_at'])) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('g:i A', strtotime($prescription['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= date('M j, Y', strtotime($prescription['appointment_date'])) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        Visit #<?= $prescription['appointment_id'] ?>
                                        <?php if (!empty($prescription['appointment_time'])): ?>
                                            at <?= date('g:i A', strtotime($prescription['appointment_time'])) ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($prescription['lens_type']) ?></span>
                                </td>
                                <td>
                                    <div class="prescription-values">
                                        <?php if ($prescription['distance_sphere_r'] !== null || $prescription['distance_sphere_l'] !== null): ?>
                                            <div><strong>R:</strong> 
                                                <?= $prescription['distance_sphere_r'] ? sprintf('%+.2f', $prescription['distance_sphere_r']) : '0.00' ?>
                                                <?= $prescription['distance_cylinder_r'] ? sprintf(' %+.2f', $prescription['distance_cylinder_r']) : '' ?>
                                                <?= $prescription['distance_axis_r'] ? ' x ' . $prescription['distance_axis_r'] : '' ?>
                                            </div>
                                            <div><strong>L:</strong> 
                                                <?= $prescription['distance_sphere_l'] ? sprintf('%+.2f', $prescription['distance_sphere_l']) : '0.00' ?>
                                                <?= $prescription['distance_cylinder_l'] ? sprintf(' %+.2f', $prescription['distance_cylinder_l']) : '' ?>
                                                <?= $prescription['distance_axis_l'] ? ' x ' . $prescription['distance_axis_l'] : '' ?>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">Not specified</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="prescription-values">
                                        <?php if ($prescription['near_sphere_r'] !== null || $prescription['near_sphere_l'] !== null): ?>
                                            <div><strong>R:</strong> 
                                                <?= $prescription['near_sphere_r'] ? sprintf('%+.2f', $prescription['near_sphere_r']) : '0.00' ?>
                                                <?= $prescription['near_cylinder_r'] ? sprintf(' %+.2f', $prescription['near_cylinder_r']) : '' ?>
                                                <?= $prescription['near_axis_r'] ? ' x ' . $prescription['near_axis_r'] : '' ?>
                                            </div>
                                            <div><strong>L:</strong> 
                                                <?= $prescription['near_sphere_l'] ? sprintf('%+.2f', $prescription['near_sphere_l']) : '0.00' ?>
                                                <?= $prescription['near_cylinder_l'] ? sprintf(' %+.2f', $prescription['near_cylinder_l']) : '' ?>
                                                <?= $prescription['near_axis_l'] ? ' x ' . $prescription['near_axis_l'] : '' ?>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">Not specified</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($prescription['PD_DISTANCE'] || $prescription['PD_NEAR']): ?>
                                        <div>
                                            <?php if ($prescription['PD_DISTANCE']): ?>
                                                <div><strong>Dist:</strong> <?= $prescription['PD_DISTANCE'] ?>mm</div>
                                            <?php endif; ?>
                                            <?php if ($prescription['PD_NEAR']): ?>
                                                <div><strong>Near:</strong> <?= $prescription['PD_NEAR'] ?>mm</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted">Not specified</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($prescription['doctor_name']) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" 
                                                onclick="viewGlassesPrescription(<?= $prescription['id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="View prescription details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" 
                                                onclick="editGlassesPrescription(<?= $prescription['id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="Edit this prescription">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-success" 
                                                onclick="printGlassesPrescription(<?= $prescription['id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="Print this prescription">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteGlassesPrescription(<?= $prescription['id'] ?>)"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="Delete this prescription">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-eyeglasses text-muted" style="font-size: 4rem;"></i>
                <h6 class="text-muted mt-3 mb-2">No Glasses Prescriptions</h6>
                <p class="text-muted mb-4">No glasses prescriptions have been recorded for this patient yet.</p>
                <button class="btn btn-primary" 
                        onclick="showAddGlassesPrescriptionModal(<?= $patient['id'] ?>)"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        data-bs-title="Add the first glasses prescription for this patient">
                    <i class="bi bi-plus me-2"></i>Add First Prescription
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Forum Topics Section -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-chat-dots me-2"></i>Forum Topics
        </h5>
    </div>
    <div class="card-body">
        <div id="patientForumTopics">
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<br>

<!-- Patient Timeline -->
<?php if (!empty($timeline)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>
            Patient Timeline
        </h5>
    </div>
    <div class="card-body">
        <div class="timeline">
            <?php foreach ($timeline as $event): ?>
                <div class="timeline-item">
                    <div class="timeline-marker bg-<?= $this->getTimelineEventColor($event['event_type']) ?>">
                        <i class="bi bi-<?= $this->getTimelineEventIcon($event['event_type']) ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <h6 class="timeline-title"><?= htmlspecialchars($event['event_summary']) ?></h6>
                        <?php if (!empty($event['actor_name'])): ?>
                            <p class="timeline-description text-muted">
                                <i class="bi bi-person me-1"></i>
                                by <?= htmlspecialchars($event['actor_name']) ?>
                            </p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            <?= date('M j, Y g:i A', strtotime($event['created_at'])) ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php endif; ?>


<?php include __DIR__ . '/alert_modal.php'; ?>
<script>
// Initialize PATIENT_CONFIG with PHP variables
<?php
?>

window.PATIENT_CONFIG = {
    patientId: <?= isset($patient['id']) ? (int)$patient['id'] : 'null' ?>,
    patientFirstName: '<?= isset($patient['first_name']) ? htmlspecialchars($patient['first_name']) : 'null' ?>',
    patientLastName: '<?= isset($patient['last_name']) ? htmlspecialchars($patient['last_name']) : 'null' ?>',
    patientName: '<?= isset($patient['first_name']) && isset($patient['last_name']) ? htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) : 'null' ?>',
    patientPhone: '<?= isset($patient['phone']) ? htmlspecialchars($patient['phone']) : 'null' ?>',
    patientAge: <?= isset($patient['dob']) ? (int)date_diff(date_create($patient['dob']), date_create('now'))->y : 'null' ?>,
    doctorId: <?= isset($appointment['doctor_id']) ? (int)$appointment['doctor_id'] : 'null' ?>,
};
</script>
<script src="/app/Views/doctor/assets/js/patient.js?v=<?= file_exists(__DIR__ . '/assets/js/patient.js') ? filemtime(__DIR__ . '/assets/js/patient.js') : time() ?>"></script>