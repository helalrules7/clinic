<style>
.appointment-header {
    background: linear-gradient(135deg, var(--accent), var(--success));
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.consultation-section {
    border-left: 4px solid var(--accent);
    padding-left: 1rem;
    margin-bottom: 2rem;
}

.prescription-card {
    border: 2px solid var(--border);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.prescription-card:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.vital-sign {
    text-align: center;
    padding: 1rem;
    background: var(--card);
    border-radius: 8px;
    border: 1px solid var(--border);
}

.vital-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--accent);
}

.timeline-item {
    display: flex;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
    background: var(--card);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid var(--border);
}

.attachment-card {
    background: var(--card);
    border: 2px solid var(--border) !important;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.attachment-card:hover {
    border-color: var(--accent) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.attachment-preview {
    max-width: 100%;
    max-height: 200px;
    object-fit: cover;
    border-radius: 4px;
}

.file-type-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: var(--accent);
    color: white;
    font-size: 1.5rem;
}

.consultation-note-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 1rem;
}

.note-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.consultation-section.border-top {
    border-top: 2px solid #dee2e6 !important;
}

.collapse {
    transition: all 0.3s ease;
}

.collapse.show {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Appointment Header -->
<div class="appointment-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-calendar-event me-2"></i>
                Appointment #<?= $appointment['id'] ?>
            </h2>
            <p class="mb-2">
                <i class="bi bi-person me-2"></i>
                <strong><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></strong>
                (ID: #<?= $patient['id'] ?>)
            </p>
            <p class="mb-0">
                <i class="bi bi-clock me-2"></i>
                <?= date('l, M j, Y \a\t g:i A', strtotime($appointment['date'] . ' ' . $appointment['start_time'])) ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex flex-column align-items-end gap-2">
                <span class="status-badge bg-<?= $this->getStatusColor($appointment['status']) ?>">
                    <?= ucfirst($appointment['status']) ?>
                </span>
                <?php if ($appointment['status'] !== 'Completed'): ?>
                <button class="btn btn-light btn-sm" onclick="markCompleted(<?= $appointment['id'] ?>)">
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
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary" onclick="editConsultation(<?= $appointment['id'] ?>)">
                <i class="bi bi-pencil me-1"></i>Edit Consultation
            </button>
            <button type="button" class="btn btn-success" onclick="addPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-prescription2 me-1"></i>Add Prescription
            </button>
            <button type="button" class="btn btn-danger" onclick="addGlassesPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-eyeglasses me-1"></i>Add Glasses
            </button>
            <button type="button" class="btn btn-info" onclick="printReport(<?= $appointment['id'] ?>)">
                <i class="bi bi-printer me-1"></i>Print Report
            </button>
            <button type="button" class="btn btn-warning" onclick="rescheduleAppointment(<?= $appointment['id'] ?>)">
                <i class="bi bi-calendar-plus me-1"></i>Reschedule
            </button>
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
                        <p><strong>National ID:</strong> <?= htmlspecialchars($patient['national_id'] ?? 'N/A') ?></p>
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
                                </div>
                            </div>
                        </div>
                        
                        <!-- Note Content (Collapsible for older notes) -->
                        <div id="note-<?= $note['id'] ?>" class="<?= $index > 0 ? 'collapse' : '' ?>">
                        
                        <!-- Chief Complaint -->
                        <?php if (!empty($note['chief_complaint'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">Chief Complaint</h6>
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

                        <!-- Visual Acuity -->
                        <?php if (!empty($note['visual_acuity_right']) || !empty($note['visual_acuity_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">Visual Acuity</h6>
                            <div class="row">
                                <?php if (!empty($note['visual_acuity_right'])): ?>
                                <div class="col-md-6">
                                    <strong>Right Eye:</strong> <?= htmlspecialchars($note['visual_acuity_right']) ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['visual_acuity_left'])): ?>
                                <div class="col-md-6">
                                    <strong>Left Eye:</strong> <?= htmlspecialchars($note['visual_acuity_left']) ?>
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
                                    <strong>Right Eye:</strong> <?= htmlspecialchars($note['refraction_right']) ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['refraction_left'])): ?>
                                <div class="col-md-6">
                                    <strong>Left Eye:</strong> <?= htmlspecialchars($note['refraction_left']) ?>
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
                                    <strong>Right Eye:</strong> <?= htmlspecialchars($note['IOP_right']) ?> mmHg
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['IOP_left'])): ?>
                                <div class="col-md-6">
                                    <strong>Left Eye:</strong> <?= htmlspecialchars($note['IOP_left']) ?> mmHg
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Slit Lamp Examination -->
                        <?php if (!empty($note['slit_lamp'])): ?>
                        <div class="mb-3">
                            <h6 class="text-success">Slit Lamp Examination</h6>
                            <p><?= nl2br(htmlspecialchars($note['slit_lamp'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Fundus Examination -->
                        <?php if (!empty($note['fundus'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Fundus Examination</h6>
                            <p><?= nl2br(htmlspecialchars($note['fundus'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Diagnosis -->
                        <?php if (!empty($note['diagnosis'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Diagnosis</h6>
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
                    <button class="btn btn-sm btn-primary" onclick="addPrescription(<?= $appointment['id'] ?>)">
                        <i class="bi bi-plus me-1"></i>Add Medication
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($medications)): ?>
                    <?php foreach ($medications as $med): ?>
                    <div class="prescription-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-primary mb-0"><?= htmlspecialchars($med['drug_name']) ?></h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="editMedication(<?= $med['id'] ?>, '<?= addslashes($med['drug_name']) ?>', '<?= addslashes($med['dose']) ?>', '<?= addslashes($med['frequency']) ?>', '<?= addslashes($med['duration']) ?>', '<?= addslashes($med['route']) ?>', '<?= addslashes($med['notes'] ?? '') ?>')" title="Edit Medication">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteMedication(<?= $med['id'] ?>)" title="Delete Medication">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <p class="mb-1">
                            <strong>Dosage:</strong> <?= htmlspecialchars($med['dose']) ?><br>
                            <strong>Frequency:</strong> <?= htmlspecialchars($med['frequency']) ?><br>
                            <strong>Duration:</strong> <?= htmlspecialchars($med['duration']) ?>
                        </p>
                        <?php if (!empty($med['notes'])): ?>
                            <p class="text-muted mb-0">
                                <small><?= htmlspecialchars($med['notes']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center">
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
                    <button class="btn btn-sm btn-primary" onclick="addLabTest(<?= $appointment['id'] ?>)">
                        <i class="bi bi-plus me-1"></i>Add Lab/Radiology
                    </button>
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
                    <button class="btn btn-sm btn-primary" onclick="addGlassesPrescription(<?= $appointment['id'] ?>)">
                        <i class="bi bi-plus me-1"></i>Add Glasses
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($glasses)): ?>
                    <?php foreach ($glasses as $glass): ?>
                    <div class="prescription-card p-3 mb-3">
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
                    <div class="text-center">
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
                <?php if (!empty($attachments)): ?>
                    <div class="row">
                        <?php foreach ($attachments as $attachment): ?>
                        <div class="col-md-6 mb-3">
                            <div class="attachment-card p-2 border rounded" style="min-height: 140px; display: flex; flex-direction: column;">
                                <div class="d-flex align-items-center mb-2 flex-grow-1">
                                    <?php
                                    $fileExt = strtolower(pathinfo($attachment['original_filename'], PATHINFO_EXTENSION));
                                    $fileName = strtolower($attachment['original_filename']);
                                    $description = strtolower($attachment['description'] ?? '');
                                    
                                    // تحديد نوع الملف والأيقونة والـ badge
                                    $iconClass = 'bi-file-earmark';
                                    $fileType = 'Document';
                                    $badgeClass = 'bg-secondary';
                                    
                                    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
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
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')" 
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="downloadAttachment(<?= $attachment['id'] ?>, '<?= $attachment['original_filename'] ?>')"
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                        <i class="bi bi-download me-1"></i>Download
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteAttachment(<?= $attachment['id'] ?>)"
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No images or attachments found</p>
                    </div>
                <?php endif; ?>
                
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

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">

                    <button class="btn btn-outline-success" onclick="scheduleFollowUp(<?= $appointment['id'] ?>)">
                        <i class="bi bi-calendar-plus me-2"></i>Schedule Follow-up
                    </button>
                    <button class="btn btn-outline-info" onclick="viewPatient(<?= $patient['id'] ?>)">
                        <i class="bi bi-person me-2"></i>View Patient Profile
                    </button>
                    <button class="btn btn-outline-warning" onclick="printPrescription(<?= $appointment['id'] ?>)">
                        <i class="bi bi-printer me-2"></i>Print Prescription
                    </button>
                    <button class="btn btn-outline-info" onclick="printGlassesPrescription(<?= $appointment['id'] ?>)">
                        <i class="bi bi-eyeglasses me-2"></i>Print Glasses
                    </button>
                    <button class="btn btn-outline-secondary" onclick="printLabTests(<?= $appointment['id'] ?>)">
                        <i class="bi bi-clipboard-data me-2"></i>Print Lab Tests
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function editConsultation(appointmentId) {
    // Redirect to edit consultation page
    window.location.href = `/doctor/appointments/${appointmentId}/edit`;
}

function addPrescription(appointmentId) {
    // Show prescription modal
    showPrescriptionModal(appointmentId);
}

function printReport(appointmentId) {
    // Open print view
    window.open(`/print/appointment/${appointmentId}`, '_blank');
}

function rescheduleAppointment(appointmentId) {
    // Show reschedule modal
    showRescheduleModal(appointmentId);
}

function addConsultationNotes(appointmentId) {
    // Redirect to edit consultation page (where notes can be added/edited)
    window.location.href = `/doctor/appointments/${appointmentId}/edit`;
}

function markCompleted(appointmentId) {
    // Show confirmation modal instead of simple confirm
    showCompletionConfirmModal(appointmentId);
}

function confirmMarkCompleted(appointmentId) {
    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('completionConfirmModal'));
    modal.hide();
    
    // Show loading state
    const button = document.querySelector(`button[onclick="markCompleted(${appointmentId})"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>جاري التحديث...';
    button.disabled = true;
    
    // API call to update status
    fetch(`/api/appointments/${appointmentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            status: 'Completed'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.ok) {
            // Update UI to show completed status
            const statusBadge = document.querySelector('.status-badge');
            statusBadge.className = 'status-badge bg-success';
            statusBadge.textContent = 'Completed';
            
            // Hide the complete button
            button.style.display = 'none';
            
            // Show success message
            showNotification('Appointment marked as completed successfully!', 'success');
        } else {
            throw new Error(data.error || data.message || 'Error updating appointment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating appointment: ' + error.message, 'error');
        
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function scheduleFollowUp(appointmentId) {
    // Show follow-up scheduling modal
    alert('Schedule follow-up functionality will be implemented soon');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function viewPatient(patientId) {
    // Redirect to patient profile
    window.location.href = `/doctor/patients/${patientId}`;
}

function printPrescription(appointmentId) {
    // Open prescription print view
    window.open(`/print/prescription/${appointmentId}`, '_blank');
}

function printGlassesPrescription(appointmentId) {
    // Open glasses prescription print view
    window.open(`/print/glasses/${appointmentId}`, '_blank');
}

function showPrescriptionModal(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="prescriptionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Prescription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="prescriptionForm" action="/api/prescriptions/meds" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Drug Name</label>
                                    <input type="text" class="form-control" name="drug_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dose</label>
                                    <input type="text" class="form-control" name="dose" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Frequency</label>
                                    <input type="text" class="form-control" name="frequency" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" name="duration" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Route</label>
                                    <select class="form-control" name="route">
                                        <option value="Topical">Topical</option>
                                        <option value="Oral">Oral</option>
                                        <option value="Injection">Injection</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('prescriptionModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/prescriptions/meds', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Prescription added successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('prescriptionModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function showRescheduleModal(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="rescheduleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reschedule Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="rescheduleForm">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            <div class="mb-3">
                                <label class="form-label">New Date</label>
                                <input type="date" class="form-control" name="new_date" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Time</label>
                                <input type="time" class="form-control" name="new_time" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reschedule Reason</label>
                                <textarea class="form-control" name="reason" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">Reschedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/appointments/' + appointmentId, {
            method: 'PUT',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('rescheduleModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Camera Functions
function openCameraModal(appointmentId, patientId) {
    const modalHtml = `
        <div class="modal fade" id="cameraModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-camera me-2"></i>Take Photo
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="cameraAppointmentId" value="${appointmentId}">
                        <input type="hidden" id="cameraPatientId" value="${patientId}">
                        
                        <div class="mb-3">
                            <label class="form-label">Photo Type</label>
                            <select class="form-select" id="cameraAttachmentType" required>
                                <option value="">Select Photo Type</option>
                                <option value="xray">X-ray</option>
                                <option value="ct_scan">CT Scan</option>
                                <option value="mri">MRI</option>
                                <option value="ultrasound">Ultrasound</option>
                                <option value="photo">Photo</option>
                                <option value="eye_photo">Eye Photo</option>
                                <option value="retina_photo">Retina Photo</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Photo Description</label>
                            <textarea class="form-control" id="cameraDescription" rows="2" 
                                      placeholder="Add a description for the photo (optional)"></textarea>
                        </div>
                        
                        <!-- Camera View -->
                        <div class="text-center mb-3">
                            <div id="cameraContainer" class="border rounded p-3" style="background: #f8f9fa; min-height: 300px;">
                                <video id="cameraVideo" width="100%" height="300" style="max-width: 100%; border-radius: 8px; display: none;" autoplay playsinline></video>
                                <canvas id="cameraCanvas" width="640" height="480" style="max-width: 100%; border-radius: 8px; display: none;"></canvas>
                                <div id="cameraPlaceholder" class="d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 300px;">
                                    <i class="bi bi-camera text-muted" style="font-size: 4rem;"></i>
                                    <p class="text-muted mt-2">Click "Start Camera" to begin</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Camera Controls -->
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-primary" id="startCameraBtn" onclick="startCamera()">
                                <i class="bi bi-camera-video me-2"></i>Start Camera
                            </button>
                            <button type="button" class="btn btn-success" id="capturePhotoBtn" onclick="capturePhoto()" style="display: none;">
                                <i class="bi bi-camera me-2"></i>Take Photo
                            </button>
                            <button type="button" class="btn btn-warning" id="retakePhotoBtn" onclick="retakePhoto()" style="display: none;">
                                <i class="bi bi-arrow-clockwise me-2"></i>Retake
                            </button>
                            <button type="button" class="btn btn-danger" id="stopCameraBtn" onclick="stopCamera()" style="display: none;">
                                <i class="bi bi-stop-circle me-2"></i>Stop Camera
                            </button>
                        </div>
                        
                        <div id="cameraProgress" class="mb-3" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">Uploading photo...</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="savePhotoBtn" onclick="savePhoto()" style="display: none;">
                            <i class="bi bi-check-lg me-2"></i>Save Photo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
    modal.show();
    
    // Clean up modal and stop camera on hide
    document.getElementById('cameraModal').addEventListener('hidden.bs.modal', function() {
        stopCamera();
        this.remove();
    });
}

let cameraStream = null;
let capturedImageData = null;

function startCamera() {
    const video = document.getElementById('cameraVideo');
    const placeholder = document.getElementById('cameraPlaceholder');
    const startBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    
    // Check if camera is supported
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showErrorMessage('Camera is not supported in this browser');
        return;
    }
    
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'environment' // Use back camera on mobile
        } 
    })
    .then(function(stream) {
        cameraStream = stream;
        video.srcObject = stream;
        
        // Show video, hide placeholder
        placeholder.style.display = 'none';
        video.style.display = 'block';
        
        // Update buttons
        startBtn.style.display = 'none';
        captureBtn.style.display = 'inline-block';
        stopBtn.style.display = 'inline-block';
        
        showSuccessMessage('Camera started successfully');
    })
    .catch(function(error) {
        console.error('Error accessing camera:', error);
        showErrorMessage('Error accessing camera: ' + error.message);
    });
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const retakeBtn = document.getElementById('retakePhotoBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    
    // Set canvas size to match video
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    // Draw the video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert canvas to blob
    canvas.toBlob(function(blob) {
        capturedImageData = blob;
        
        // Hide video, show canvas
        video.style.display = 'none';
        canvas.style.display = 'block';
        
        // Update buttons
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
        saveBtn.style.display = 'inline-block';
        
        showSuccessMessage('Photo captured! You can now save it or retake.');
    }, 'image/jpeg', 0.8);
}

function retakePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const retakeBtn = document.getElementById('retakePhotoBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    
    // Clear captured image
    capturedImageData = null;
    
    // Show video, hide canvas
    canvas.style.display = 'none';
    video.style.display = 'block';
    
    // Update buttons
    retakeBtn.style.display = 'none';
    saveBtn.style.display = 'none';
    captureBtn.style.display = 'inline-block';
}

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const placeholder = document.getElementById('cameraPlaceholder');
    const startBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const retakeBtn = document.getElementById('retakePhotoBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    
    if (video) {
        video.style.display = 'none';
        video.srcObject = null;
    }
    
    if (canvas) {
        canvas.style.display = 'none';
    }
    
    if (placeholder) {
        placeholder.style.display = 'flex';
    }
    
    // Reset buttons
    if (startBtn) startBtn.style.display = 'inline-block';
    if (captureBtn) captureBtn.style.display = 'none';
    if (retakeBtn) retakeBtn.style.display = 'none';
    if (stopBtn) stopBtn.style.display = 'none';
    if (saveBtn) saveBtn.style.display = 'none';
    
    // Clear captured image
    capturedImageData = null;
}

function savePhoto() {
    if (!capturedImageData) {
        showErrorMessage('No photo captured');
        return;
    }
    
    const appointmentId = document.getElementById('cameraAppointmentId').value;
    const patientId = document.getElementById('cameraPatientId').value;
    const attachmentType = document.getElementById('cameraAttachmentType').value;
    const description = document.getElementById('cameraDescription').value;
    
    if (!attachmentType) {
        showErrorMessage('Please select a photo type');
        return;
    }
    
    const formData = new FormData();
    formData.append('appointment_id', appointmentId);
    formData.append('patient_id', patientId);
    formData.append('attachment_type', attachmentType);
    formData.append('description', description);
    formData.append('attachment_file', capturedImageData, 'camera_photo_' + Date.now() + '.jpg');
    
    const saveBtn = document.getElementById('savePhotoBtn');
    const progressDiv = document.getElementById('cameraProgress');
    const progressBar = progressDiv.querySelector('.progress-bar');
    
    // Show progress
    saveBtn.disabled = true;
    progressDiv.style.display = 'block';
    
    // Create XMLHttpRequest for progress tracking
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        saveBtn.disabled = false;
        progressDiv.style.display = 'none';
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('cameraModal'));
                    modal.hide();
                    showSuccessMessage('Photo saved successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorMessage('Error: ' + (response.message || 'Save failed'));
                }
            } catch (parseError) {
                console.error('Response parsing error:', parseError);
                showErrorMessage('Server response error');
            }
        } else {
            showErrorMessage('HTTP Error ' + xhr.status + ': ' + xhr.statusText);
        }
    });
    
    xhr.addEventListener('error', function() {
        showErrorMessage('Error: ' + xhr.statusText);
        saveBtn.disabled = false;
        progressDiv.style.display = 'none';
    });
    
    xhr.open('POST', '/api/attachments/upload');
    xhr.send(formData);
}

// Medical Attachments Functions
function showUploadModal(appointmentId, patientId) {
    const modalHtml = `
        <div class="modal fade" id="uploadModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload New Attachment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            <input type="hidden" name="patient_id" value="${patientId}">
                            
                            <div class="mb-3">
                                <label class="form-label">Attachment Type</label>
                                <select class="form-select" name="attachment_type" required>
                                    <option value="">Select Attachment Type</option>
                                    <option value="xray">X-ray</option>
                                    <option value="ct_scan">CT Scan</option>
                                    <option value="mri">MRI</option>
                                    <option value="ultrasound">Ultrasound</option>
                                    <option value="lab_report">Lab Report</option>
                                    <option value="blood_test">Blood Test</option>
                                    <option value="photo">Photo</option>
                                    <option value="report">Report</option>
                                    <option value="prescription">Prescription</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">File</label>
                                <input type="file" class="form-control" name="attachment_file" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt" required>
                                <div class="form-text">
                                    Supported Files: Images (JPG, PNG, GIF), PDF, Word Documents, Text Files
                                    <br>Maximum File Size: 2 MB
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Attachment Description</label>
                                <textarea class="form-control" name="description" rows="3" 
                                          placeholder="Add a description for the attachment (optional)"></textarea>
                            </div>
                            
                            <div id="uploadProgress" class="mb-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Uploading...</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="uploadBtn">
                                <i class="bi bi-cloud-upload me-2"></i>Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
    modal.show();
    
    // Handle file selection
    const fileInput = document.querySelector('#uploadModal input[type="file"]');
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size is too large. Maximum 2 MB.');
                this.value = '';
                return;
            }
            
            // Show file info
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            console.log(`Selected file: ${fileName} (${fileSize} MB)`);
        }
    });
    
    // Handle form submission
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = document.getElementById('uploadBtn');
        const progressDiv = document.getElementById('uploadProgress');
        const progressBar = progressDiv.querySelector('.progress-bar');
        
        // Show progress
        uploadBtn.disabled = true;
        progressDiv.style.display = 'block';
        
        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
                progressBar.textContent = Math.round(percentComplete) + '%';
            }
        });
        
        xhr.addEventListener('load', function() {
            uploadBtn.disabled = false;
            progressDiv.style.display = 'none';
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        modal.hide();
                        showSuccessMessage('Attachment uploaded successfully');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showErrorMessage('Error: ' + (response.message || 'Upload failed'));
                    }
                } catch (parseError) {
                    console.error('Response parsing error:', parseError);
                    console.error('Raw response:', xhr.responseText);
                    showErrorMessage('Server response error. Please check if the API endpoint exists.');
                }
            } else {
                showErrorMessage('HTTP Error ' + xhr.status + ': ' + xhr.statusText);
            }
        });
        
        xhr.addEventListener('error', function() {
            showErrorMessage('Error: ' + xhr.statusText);
            uploadBtn.disabled = false;
            progressDiv.style.display = 'none';
        });
        
        xhr.open('POST', '/api/attachments/upload');
        xhr.send(formData);
    });
    
    // Clean up modal on hide
    document.getElementById('uploadModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function viewAttachment(attachmentId, filePath, fileExt) {
    const viewUrl = `/api/attachments/view/${attachmentId}`;
    
    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt.toLowerCase())) {
        // Show image in modal
        showImageModal(viewUrl, attachmentId);
    } else if (fileExt.toLowerCase() === 'pdf') {
        // Open PDF in new tab
        window.open(viewUrl, '_blank');
    } else {
        // Download other file types
        downloadAttachment(attachmentId);
    }
}

function showImageModal(imageUrl, attachmentId) {
    const modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">View Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageUrl}" class="img-fluid" style="max-height: 80vh;" alt="Medical Image">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="downloadAttachment(${attachmentId})">
                            <i class="bi bi-download me-2"></i>Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
    
    // Clean up modal on hide
    document.getElementById('imageModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function downloadAttachment(attachmentId, filename) {
    const downloadUrl = `/api/attachments/download/${attachmentId}`;
    
    // Create temporary link and click it
    const link = document.createElement('a');
    link.href = downloadUrl;
    if (filename) {
        link.download = filename;
    }
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function deleteAttachment(attachmentId) {
    showDeleteConfirmModal(
        'Delete Attachment',
        'Are you sure you want to delete this attachment?',
        'This action cannot be undone.',
        () => {
            fetch(`/api/attachments/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Attachment deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorMessage('Error: ' + data.message);
                }
            })
            .catch(error => {
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

// Utility functions for notifications
function showSuccessMessage(message) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="bi bi-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-success');
        if (alert) {
            alert.remove();
        }
    }, 3000);
}

function showErrorMessage(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-danger');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Delete Confirmation Modal
function showDeleteConfirmModal(title, message, warning, onConfirm) {
    const modalHtml = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>${title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">${message}</p>
                        <p class="text-muted mb-0"><small><i class="bi bi-info-circle me-1"></i>${warning}</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="bi bi-trash me-2"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
    
    // Handle confirm button
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        modal.hide();
        onConfirm();
    });
    
    // Clean up modal on hide
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Delete Medication Function
function deleteMedication(medicationId) {
    showDeleteConfirmModal(
        'Delete Medication',
        'Are you sure you want to delete this medication?',
        'This action cannot be undone.',
        () => {
            fetch(`/api/prescriptions/meds/${medicationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Medication deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorMessage('Error: ' + data.message);
                }
            })
            .catch(error => {
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

// Edit Medication Function
function editMedication(medicationId, drugName, dose, frequency, duration, route, notes) {
    const modalHtml = `
        <div class="modal fade" id="editMedicationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Medication</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editMedicationForm">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Drug Name</label>
                                    <input type="text" class="form-control" name="drug_name" value="${drugName}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dose</label>
                                    <input type="text" class="form-control" name="dose" value="${dose}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Frequency</label>
                                    <input type="text" class="form-control" name="frequency" value="${frequency}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" name="duration" value="${duration}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Route</label>
                                    <select class="form-control" name="route">
                                        <option value="Topical" ${route === 'Topical' ? 'selected' : ''}>Topical</option>
                                        <option value="Oral" ${route === 'Oral' ? 'selected' : ''}>Oral</option>
                                        <option value="Injection" ${route === 'Injection' ? 'selected' : ''}>Injection</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" rows="3">${notes}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Medication</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editMedicationModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('editMedicationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Convert FormData to URLSearchParams for PUT request
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            params.append(key, value);
        }
        
        fetch(`/api/prescriptions/meds/${medicationId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString(),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Medication updated successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editMedicationModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Glasses Prescription Functions
function addGlassesPrescription(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="glassesModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Glasses Prescription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="glassesForm">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            
                            <!-- PD and Lens Type Section -->
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Distance (PD)</label>
                                    <input type="number" step="0.5" class="form-control" name="PD_DISTANCE" placeholder="62.0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Near (NPD)</label>
                                    <input type="number" step="0.5" class="form-control" name="PD_NEAR" placeholder="58.0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Lens Type</label>
                                    <select class="form-control" name="lens_type">
                                        <option value="Single Vision">Single Vision</option>
                                        <option value="Bifocal">Bifocal</option>
                                        <option value="Progressive">Progressive</option>
                                        <option value="Reading">Reading</option>
                                    </select>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Distance Vision Section -->
                            <h6 class="text-success mb-3"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_sphere_r" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_cylinder_r" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="distance_axis_r" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_sphere_l" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_cylinder_l" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="distance_axis_l" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Near Vision Section -->
                            <h6 class="text-info mb-3"><i class="bi bi-book me-2"></i>Near Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="near_sphere_r" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="near_cylinder_r" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="near_axis_r" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="near_sphere_l" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="near_cylinder_l" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="near_axis_l" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comments</label>
                                <textarea class="form-control" name="comments" rows="3" placeholder="Additional notes or instructions"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Glasses Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('glassesModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('glassesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/prescriptions/glasses', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Glasses prescription added successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('Error: ' + (data.error || data.message || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Glasses prescription error:', error);
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('glassesModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function editGlassesPrescription(glassesId, glassesData) {
    const modalHtml = `
        <div class="modal fade" id="editGlassesModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Glasses Prescription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editGlassesForm">
                        <div class="modal-body">
                            <!-- PD and Lens Type Section -->
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Distance (PD)</label>
                                    <input type="number" step="0.5" class="form-control" name="PD_DISTANCE" value="${glassesData.PD_DISTANCE || ''}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Near (NPD)</label>
                                    <input type="number" step="0.5" class="form-control" name="PD_NEAR" value="${glassesData.PD_NEAR || ''}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Lens Type</label>
                                    <select class="form-control" name="lens_type">
                                        <option value="Single Vision" ${glassesData.lens_type === 'Single Vision' ? 'selected' : ''}>Single Vision</option>
                                        <option value="Bifocal" ${glassesData.lens_type === 'Bifocal' ? 'selected' : ''}>Bifocal</option>
                                        <option value="Progressive" ${glassesData.lens_type === 'Progressive' ? 'selected' : ''}>Progressive</option>
                                        <option value="Reading" ${glassesData.lens_type === 'Reading' ? 'selected' : ''}>Reading</option>
                                    </select>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Distance Vision Section -->
                            <h6 class="text-success mb-3"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_sphere_r" value="${glassesData.distance_sphere_r || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_cylinder_r" value="${glassesData.distance_cylinder_r || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="distance_axis_r" value="${glassesData.distance_axis_r || ''}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_sphere_l" value="${glassesData.distance_sphere_l || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="distance_cylinder_l" value="${glassesData.distance_cylinder_l || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="distance_axis_l" value="${glassesData.distance_axis_l || ''}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Near Vision Section -->
                            <h6 class="text-info mb-3"><i class="bi bi-book me-2"></i>Near Vision</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Right Eye (OD)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="near_sphere_r" value="${glassesData.near_sphere_r || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="near_cylinder_r" value="${glassesData.near_cylinder_r || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="near_axis_r" value="${glassesData.near_axis_r || ''}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="number" step="0.25" class="form-control" name="near_sphere_l" value="${glassesData.near_sphere_l || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="number" step="0.25" class="form-control" name="near_cylinder_l" value="${glassesData.near_cylinder_l || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="number" min="0" max="180" class="form-control" name="near_axis_l" value="${glassesData.near_axis_l || ''}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comments</label>
                                <textarea class="form-control" name="comments" rows="3">${glassesData.comments || ''}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Update Glasses Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editGlassesModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('editGlassesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Convert FormData to URLSearchParams for PUT request
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            params.append(key, value);
        }
        
        fetch(`/api/prescriptions/glasses/${glassesId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString(),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Glasses prescription updated successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editGlassesModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function deleteGlassesPrescription(glassesId) {
    showDeleteConfirmModal(
        'Delete Glasses Prescription',
        'Are you sure you want to delete this glasses prescription?',
        'This action cannot be undone.',
        () => {
            fetch(`/api/prescriptions/glasses/${glassesId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Glasses prescription deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorMessage('Error: ' + data.message);
                }
            })
            .catch(error => {
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

// Lab Tests & Radiology Functions
function addLabTest(appointmentId) {
    const modalHtml = '<div class="modal fade" id="labTestModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h5 class="modal-title">' +
        '<i class="bi bi-clipboard-data me-2"></i>Add Lab Test / Radiology' +
        '</h5>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '</div>' +
        '<form id="labTestForm">' +
        '<div class="modal-body">' +
        '<input type="hidden" name="appointment_id" value="' + appointmentId + '">' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Type</label>' +
        '<select class="form-select" name="test_type" required onchange="updateTestCategories(this.value)">' +
        '<option value="">Select Test Type</option>' +
        '<option value="laboratory">Laboratory Test</option>' +
        '<option value="radiology">Radiology</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Category</label>' +
        '<select class="form-select" name="test_category" required id="testCategorySelect">' +
        '<option value="">Select Category First</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Test Name</label>' +
        '<input type="text" class="form-control" name="test_name" required placeholder="Enter specific test name">' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Priority</label>' +
        '<select class="form-select" name="priority">' +
        '<option value="normal">Normal</option>' +
        '<option value="high">High</option>' +
        '<option value="urgent">Urgent</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Status</label>' +
        '<select class="form-select" name="status">' +
        '<option value="ordered">Ordered</option>' +
        '<option value="pending">Pending</option>' +
        '<option value="completed">Completed</option>' +
        '<option value="cancelled">Cancelled</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Ordered Date</label>' +
        '<input type="date" class="form-control" name="ordered_date" value="' + new Date().toISOString().split('T')[0] + '">' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Expected Date</label>' +
        '<input type="date" class="form-control" name="expected_date">' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Clinical Notes</label>' +
        '<textarea class="form-control" name="notes" rows="3" placeholder="Clinical indication, special instructions, etc."></textarea>' +
        '</div>' +
        '<div class="mb-3" id="resultsSection" style="display: none;">' +
        '<label class="form-label">Results</label>' +
        '<textarea class="form-control" name="results" rows="4" placeholder="Test results (if completed)"></textarea>' +
        '</div>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
        '<button type="submit" class="btn btn-primary">' +
        '<i class="bi bi-check-lg me-2"></i>Add Test' +
        '</button>' +
        '</div>' +
        '</form>' +
        '</div>' +
        '</div>' +
        '</div>';
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('labTestModal'));
    modal.show();
    
    // Handle status change to show/hide results section
    document.querySelector('#labTestModal select[name="status"]').addEventListener('change', function() {
        const resultsSection = document.getElementById('resultsSection');
        if (this.value === 'completed') {
            resultsSection.style.display = 'block';
        } else {
            resultsSection.style.display = 'none';
        }
    });
    
    // Handle form submission
    document.getElementById('labTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/lab-tests', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Lab test added successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('Error: ' + (data.message || 'Failed to add lab test'));
            }
        })
        .catch(error => {
            console.error('Lab test error:', error);
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('labTestModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function updateTestCategories(testType) {
    const categorySelect = document.getElementById('testCategorySelect');
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    if (testType === 'laboratory') {
        const labCategories = [
            'Hematology',
            'Biochemistry', 
            'Immunology',
            'Microbiology',
            'Hormones',
            'Tumor Markers',
            'Cardiac Markers',
            'Coagulation',
            'Urine Analysis',
            'Stool Analysis'
        ];
        
        labCategories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.toLowerCase().replace(/\s+/g, '_');
            option.textContent = category;
            categorySelect.appendChild(option);
        });
    } else if (testType === 'radiology') {
        const radiologyCategories = [
            'X-Ray',
            'CT Scan',
            'MRI',
            'Ultrasound',
            'Mammography',
            'Fluoroscopy',
            'Nuclear Medicine',
            'PET Scan',
            'Angiography'
        ];
        
        radiologyCategories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.toLowerCase().replace(/\s+/g, '_');
            option.textContent = category;
            categorySelect.appendChild(option);
        });
    }
}

function editLabTest(testId, testData) {
    const modalHtml = '<div class="modal fade" id="editLabTestModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h5 class="modal-title">' +
        '<i class="bi bi-pencil me-2"></i>Edit Lab Test / Radiology' +
        '</h5>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '</div>' +
        '<form id="editLabTestForm">' +
        '<div class="modal-body">' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Type</label>' +
        '<select class="form-select" name="test_type" required onchange="updateEditTestCategories(this.value)">' +
        '<option value="laboratory"' + (testData.test_type === 'laboratory' ? ' selected' : '') + '>Laboratory Test</option>' +
        '<option value="radiology"' + (testData.test_type === 'radiology' ? ' selected' : '') + '>Radiology</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Test Category</label>' +
        '<select class="form-select" name="test_category" required id="editTestCategorySelect">' +
        '<option value="' + (testData.test_category || '') + '">' + (testData.test_category || 'Select Category') + '</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Test Name</label>' +
        '<input type="text" class="form-control" name="test_name" required value="' + (testData.test_name || '') + '" placeholder="Enter specific test name">' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Priority</label>' +
        '<select class="form-select" name="priority">' +
        '<option value="normal"' + (testData.priority === 'normal' ? ' selected' : '') + '>Normal</option>' +
        '<option value="high"' + (testData.priority === 'high' ? ' selected' : '') + '>High</option>' +
        '<option value="urgent"' + (testData.priority === 'urgent' ? ' selected' : '') + '>Urgent</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Status</label>' +
        '<select class="form-select" name="status" onchange="toggleEditResultsSection(this.value)">' +
        '<option value="ordered"' + (testData.status === 'ordered' ? ' selected' : '') + '>Ordered</option>' +
        '<option value="pending"' + (testData.status === 'pending' ? ' selected' : '') + '>Pending</option>' +
        '<option value="completed"' + (testData.status === 'completed' ? ' selected' : '') + '>Completed</option>' +
        '<option value="cancelled"' + (testData.status === 'cancelled' ? ' selected' : '') + '>Cancelled</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Ordered Date</label>' +
        '<input type="date" class="form-control" name="ordered_date" value="' + (testData.ordered_date || '') + '">' +
        '</div>' +
        '<div class="col-md-6 mb-3">' +
        '<label class="form-label">Expected Date</label>' +
        '<input type="date" class="form-control" name="expected_date" value="' + (testData.expected_date || '') + '">' +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        '<label class="form-label">Clinical Notes</label>' +
        '<textarea class="form-control" name="notes" rows="3" placeholder="Clinical indication, special instructions, etc.">' + (testData.notes || '') + '</textarea>' +
        '</div>' +
        '<div class="mb-3" id="editResultsSection" style="display: ' + (testData.status === 'completed' ? 'block' : 'none') + ';">' +
        '<label class="form-label">Results</label>' +
        '<textarea class="form-control" name="results" rows="4" placeholder="Test results">' + (testData.results || '') + '</textarea>' +
        '</div>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
        '<button type="submit" class="btn btn-primary">' +
        '<i class="bi bi-check-lg me-2"></i>Update Test' +
        '</button>' +
        '</div>' +
        '</form>' +
        '</div>' +
        '</div>' +
        '</div>';
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editLabTestModal'));
    modal.show();
    
    // Initialize categories for the current test type
    updateEditTestCategories(testData.test_type);
    
    // Handle form submission
    document.getElementById('editLabTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Convert FormData to JSON object
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        console.log('Updating lab test:', testId, 'with data:', data);
        
        fetch('/api/lab-tests/' + testId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Edit response:', data);
            if (data.success) {
                modal.hide();
                showSuccessMessage('Lab test updated successfully');
                // Add a longer timeout and force reload without cache
                setTimeout(() => {
                    window.location.href = window.location.href + '?t=' + Date.now();
                }, 1500);
            } else {
                showErrorMessage('Error: ' + (data.message || 'Failed to update lab test'));
            }
        })
        .catch(error => {
            console.error('Lab test update error:', error);
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editLabTestModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function updateEditTestCategories(testType) {
    const categorySelect = document.getElementById('editTestCategorySelect');
    const currentValue = categorySelect.querySelector('option').value;
    
    categorySelect.innerHTML = '<option value="' + currentValue + '">' + (currentValue || 'Select Category') + '</option>';
    
    if (testType === 'laboratory') {
        const labCategories = [
            'Hematology', 'Biochemistry', 'Immunology', 'Microbiology',
            'Hormones', 'Tumor Markers', 'Cardiac Markers', 'Coagulation',
            'Urine Analysis', 'Stool Analysis'
        ];
        
        labCategories.forEach(category => {
            if (category.toLowerCase().replace(/\s+/g, '_') !== currentValue) {
                const option = document.createElement('option');
                option.value = category.toLowerCase().replace(/\s+/g, '_');
                option.textContent = category;
                categorySelect.appendChild(option);
            }
        });
    } else if (testType === 'radiology') {
        const radiologyCategories = [
            'X-Ray', 'CT Scan', 'MRI', 'Ultrasound', 'Mammography',
            'Fluoroscopy', 'Nuclear Medicine', 'PET Scan', 'Angiography'
        ];
        
        radiologyCategories.forEach(category => {
            if (category.toLowerCase().replace(/\s+/g, '_') !== currentValue) {
                const option = document.createElement('option');
                option.value = category.toLowerCase().replace(/\s+/g, '_');
                option.textContent = category;
                categorySelect.appendChild(option);
            }
        });
    }
}

function toggleEditResultsSection(status) {
    const resultsSection = document.getElementById('editResultsSection');
    if (status === 'completed') {
        resultsSection.style.display = 'block';
    } else {
        resultsSection.style.display = 'none';
    }
}

function deleteLabTest(testId) {
    showDeleteConfirmModal(
        'Delete Lab Test',
        'Are you sure you want to delete this lab test?',
        'This action cannot be undone.',
        () => {
            fetch('/api/lab-tests/' + testId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Lab test deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorMessage('Error: ' + (data.message || 'Failed to delete lab test'));
                }
            })
            .catch(error => {
                console.error('Lab test delete error:', error);
                showErrorMessage('Error: ' + error.message);
            });
        }
    );
}

function printLabTest(testId) {
    // Open lab test print view
    window.open('/print/lab-test/' + testId, '_blank');
}

function printLabTests(appointmentId) {
    // Open all lab tests print view for this appointment
    window.open('/print/lab-tests/' + appointmentId, '_blank');
}

// Initialize tooltips for file attachments
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add hover effects to attachment cards
    const attachmentCards = document.querySelectorAll('.attachment-card');
    attachmentCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'all 0.2s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
            this.style.transform = '';
        });
    });
});

// Toggle consultation note details
function toggleNoteDetails(noteId) {
    const noteElement = document.getElementById(noteId);
    const button = document.querySelector(`button[onclick="toggleNoteDetails('${noteId}')"]`);
    const icon = button.querySelector('i');
    
    if (noteElement.classList.contains('show')) {
        // Hide details
        noteElement.classList.remove('show');
        icon.className = 'bi bi-eye';
        button.title = 'Show details';
    } else {
        // Show details
        noteElement.classList.add('show');
        icon.className = 'bi bi-eye-slash';
        button.title = 'Hide details';
    }
}

// Show completion confirmation modal
function showCompletionConfirmModal(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="completionConfirmModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-check-circle me-2"></i>Confirm Appointment Completion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="bi bi-question-circle-fill text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h6 class="mb-3">Are you sure you want to mark this appointment as completed?</h6>
                        <p class="text-muted mb-0">
                            This will update the appointment status to "completed" and cannot be undone.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success" onclick="confirmMarkCompleted(${appointmentId})">
                            <i class="bi bi-check-circle me-1"></i>Confirm Completion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('completionConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('completionConfirmModal'));
    modal.show();
    
    // Clean up modal after hide
    document.getElementById('completionConfirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
    </script>
