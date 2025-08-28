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
            <span class="status-badge bg-<?= $this->getStatusColor($appointment['status']) ?>">
                <?= ucfirst($appointment['status']) ?>
            </span>
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
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-pulse me-2"></i>
                    Consultation Notes
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($consultationNotes as $note): ?>
                    <div class="consultation-section">
                        
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

                        <!-- Visual Acuity -->
                        <?php if (!empty($note['visual_acuity'])): ?>
                        <div class="mb-3">
                            <h6 class="text-info">Visual Acuity</h6>
                            <p><?= htmlspecialchars($note['visual_acuity']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Examination -->
                        <?php if (!empty($note['examination'])): ?>
                        <div class="mb-3">
                            <h6 class="text-warning">Examination</h6>
                            <p><?= nl2br(htmlspecialchars($note['examination'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Diagnosis -->
                        <?php if (!empty($note['diagnosis'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Diagnosis</h6>
                            <p><?= htmlspecialchars($note['diagnosis']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Plan -->
                        <?php if (!empty($note['plan'])): ?>
                        <div class="mb-3">
                            <h6 class="text-secondary">Treatment Plan</h6>
                            <p><?= nl2br(htmlspecialchars($note['plan'])) ?></p>
                        </div>
                        <?php endif; ?>

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
                <h5 class="mb-0">
                    <i class="bi bi-paperclip me-2"></i>
                Images & Attachements 
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($attachments)): ?>
                    <div class="row">
                        <?php foreach ($attachments as $attachment): ?>
                        <div class="col-md-6 mb-3">
                            <div class="attachment-card p-3 border rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <?php
                                    $fileExt = strtolower(pathinfo($attachment['original_filename'], PATHINFO_EXTENSION));
                                    $iconClass = 'bi-file-earmark';
                                    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $iconClass = 'bi-image';
                                    } elseif ($fileExt == 'pdf') {
                                        $iconClass = 'bi-file-earmark-pdf';
                                    } elseif (in_array($fileExt, ['doc', 'docx'])) {
                                        $iconClass = 'bi-file-earmark-word';
                                    }
                                    ?>
                                    <i class="bi <?= $iconClass ?> text-primary me-2" style="font-size: 1.5rem;"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($attachment['original_filename']) ?></h6>
                                        <small class="text-muted">
                                            <?= number_format($attachment['file_size'] / 1024, 1) ?> KB
                                            • <?= date('d/m/Y H:i', strtotime($attachment['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <?php if (!empty($attachment['description'])): ?>
                                <p class="text-muted mb-2 small"><?= htmlspecialchars($attachment['description']) ?></p>
                                <?php endif; ?>
                                
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <button class="btn btn-outline-primary" onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    <button class="btn btn-outline-success" onclick="downloadAttachment(<?= $attachment['id'] ?>, '<?= $attachment['original_filename'] ?>')">
                                        <i class="bi bi-download me-1"></i>Download
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteAttachment(<?= $attachment['id'] ?>)">
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
                        <p class="text-muted mt-2 mb-0">No images or attachments fou</p>
                    </div>
                <?php endif; ?>
                
                <div class="d-grid mt-3">
                    <button class="btn btn-primary" onclick="showUploadModal(<?= $appointment['id'] ?>, <?= $patient['id'] ?>)">
                        <i class="bi bi-cloud-upload me-2"></i>Upload New Attachment
                    </button>
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
                    <button class="btn btn-outline-primary" onclick="markCompleted(<?= $appointment['id'] ?>)">
                        <i class="bi bi-check-circle me-2"></i>Mark as Completed
                    </button>
                    <button class="btn btn-outline-success" onclick="scheduleFollowUp(<?= $appointment['id'] ?>)">
                        <i class="bi bi-calendar-plus me-2"></i>Schedule Follow-up
                    </button>
                    <button class="btn btn-outline-info" onclick="viewPatient(<?= $patient['id'] ?>)">
                        <i class="bi bi-person me-2"></i>View Patient Profile
                    </button>
                    <button class="btn btn-outline-warning" onclick="printPrescription(<?= $appointment['id'] ?>)">
                        <i class="bi bi-printer me-2"></i>Print Prescription
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
    // Redirect to add notes page
    window.location.href = `/doctor/appointments/${appointmentId}/notes`;
}

function markCompleted(appointmentId) {
    if (confirm('Mark this appointment as completed?')) {
        // API call to update status
        alert('Mark completed functionality will be implemented soon');
    }
}

function scheduleFollowUp(appointmentId) {
    // Show follow-up scheduling modal
    alert('Schedule follow-up functionality will be implemented soon');
}

function viewPatient(patientId) {
    // Redirect to patient profile
    window.location.href = `/doctor/patients/${patientId}`;
}

function printPrescription(appointmentId) {
    // Open prescription print view
    window.open(`/print/prescription/${appointmentId}`, '_blank');
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
</script>
