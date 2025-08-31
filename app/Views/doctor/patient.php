<!-- Patient Profile Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <div class="avatar-circle-large me-3">
                <?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?>
            </div>
            <div>
                <h2 class="text-primary mb-1"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
                <p class="text-muted mb-0">Patient ID: #<?= $patient['id'] ?></p>
                <?php if ($patient['dob']): ?>
                    <small class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('M j, Y', strtotime($patient['dob'])) ?> 
                        (<?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> years old)
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-primary me-2" 
                onclick="bookNewAppointment(<?= $patient['id'] ?>)"
                data-bs-toggle="tooltip" 
                data-bs-placement="bottom" 
                data-bs-title="Schedule a new appointment for this patient">
            <i class="bi bi-calendar-plus me-2"></i>
            Book Appointment
        </button>
        <div class="dropdown d-inline">
            <button class="btn btn-outline-secondary dropdown-toggle" 
                    type="button" 
                    data-bs-toggle="dropdown"
                    title="More patient actions and options">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" 
                       href="#" 
                       onclick="printPatientSummary()"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="left" 
                       data-bs-title="Print patient summary report">
                    <i class="bi bi-printer me-2"></i>Print Summary
                </a></li>
                <li><a class="dropdown-item" 
                       href="#" 
                       onclick="exportPatientData()"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="left" 
                       data-bs-title="Export patient data to file">
                    <i class="bi bi-download me-2"></i>Export Data
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" 
                       href="#" 
                       onclick="editPatient(<?= $patient['id'] ?>)"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="left" 
                       data-bs-title="Edit patient information and details">
                    <i class="bi bi-pencil me-2"></i>Edit Patient
                </a></li>
            </ul>
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

    <!-- Emergency Contact -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-exclamation me-2"></i>
                    Emergency Contact
                </h5>
            </div>
            <div class="card-body">
                <?php if ($patient['emergency_contact'] || $patient['emergency_phone']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Name:</strong></div>
                        <div class="col-sm-8" id="emergencyContactName"><?= htmlspecialchars($patient['emergency_contact'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-4"><strong>Phone:</strong></div>
                        <div class="col-sm-8" id="emergencyContactPhone"><?= htmlspecialchars($patient['emergency_phone'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-sm btn-outline-secondary" 
                                    onclick="editEmergencyContact()"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="Edit emergency contact information">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="noEmergencyContact">
                        <p class="text-muted mb-0">No emergency contact information available</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" 
                                onclick="addEmergencyContact()"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                data-bs-title="Add emergency contact information for this patient">
                            <i class="bi bi-plus me-1"></i>Add Emergency Contact
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Medical History -->
<?php if (!empty($medicalHistory)): ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-heart me-2"></i>
            Medical History
        </h5>
        <button class="btn btn-primary btn-sm" 
                onclick="addMedicalHistory()"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                data-bs-title="Add a new medical history entry for this patient">
            <i class="bi bi-plus me-1"></i>Add Entry
        </button>
    </div>
    <div class="card-body">
        <?php foreach ($medicalHistory as $history): ?>
            <div class="row">
                <?php if (!empty($history['allergies'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Allergies
                    </h6>
                    <p><?= htmlspecialchars($history['allergies']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['medications'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary">
                        <i class="bi bi-capsule me-1"></i>
                        Current Medications
                    </h6>
                    <p><?= htmlspecialchars($history['medications']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['systemic_history'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-info">
                        <i class="bi bi-heart-pulse me-1"></i>
                        Systemic History
                    </h6>
                    <p><?= htmlspecialchars($history['systemic_history']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['ocular_history'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-success">
                        <i class="bi bi-eye me-1"></i>
                        Ocular History
                    </h6>
                    <p><?= htmlspecialchars($history['ocular_history']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['prior_surgeries'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-warning">
                        <i class="bi bi-scissors me-1"></i>
                        Prior Surgeries
                    </h6>
                    <p><?= htmlspecialchars($history['prior_surgeries']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['family_history'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-secondary">
                        <i class="bi bi-people me-1"></i>
                        Family History
                    </h6>
                    <p><?= htmlspecialchars($history['family_history']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <hr class="my-3">
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-heart me-2"></i>
            Medical History
        </h5>
    </div>
    <div class="card-body text-center">
        <i class="bi bi-clipboard-heart text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2 mb-0">No medical history recorded</p>
        <button class="btn btn-outline-primary mt-3" 
                onclick="addMedicalHistory()"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                data-bs-title="Add medical history entry for this patient">
            <i class="bi bi-plus me-2"></i>Add Medical History
        </button>
    </div>
</div>
<?php endif; ?>

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
        <?php if (!empty($patientAttachments)): ?>
            <div class="row">
                <?php foreach ($patientAttachments as $attachment): ?>
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
                            <i class="bi <?= $iconClass ?> text-primary me-2" style="font-size: 1.2rem; flex-shrink: 0;"></i>
                            <div class="flex-grow-1">
                                <?php 
                                $originalName = $attachment['original_filename'];
                                $displayName = strlen($originalName) > 20 ? substr($originalName, 0, 10) . '...' : $originalName;
                                ?>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0" style="font-size: 0.8rem; line-height: 1.1;" 
                                        title="<?= htmlspecialchars($originalName) ?>">
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
                               title="<?= htmlspecialchars($description) ?>">
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
                                    data-bs-title="View this file or image">
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
            <div class="text-center py-4">
                <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No files or documents found for this patient</p>
            </div>
        <?php endif; ?>
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

<!-- Emergency Contact Modal -->
<div class="modal fade" id="emergencyContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emergencyContactModalTitle">Add Emergency Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="emergencyContactForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="emergencyContactNameInput" class="form-label">Contact Name *</label>
                        <input type="text" class="form-control" id="emergencyContactNameInput" 
                               name="emergency_contact" required maxlength="100" 
                               placeholder="Enter contact name">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="emergencyContactPhoneInput" class="form-label">Contact Phone *</label>
                        <input type="tel" class="form-control" id="emergencyContactPhoneInput" 
                               name="emergency_phone" required 
                               placeholder="Enter phone number (e.g., 01234567890)">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Please enter a valid Egyptian phone number</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveEmergencyContactBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="saveSpinner"></span>
                        <i class="bi bi-check-circle me-2"></i>
                        Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Dark Mode Support */
.card {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.card-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.table {
    background-color: var(--bg-dark);
    color: var(--text);
}

.table thead th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table-dark th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table tbody tr {
    background-color: var(--bg-dark);
    border-color: var(--border);
}

.table tbody tr:hover {
    background-color: var(--bg-alt);
}

.table td {
    background-color: var(--bg-dark);
    border-color: var(--border);
    color: var(--text);
}

.text-muted {
    color: var(--muted) !important;
}

.avatar-circle-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--accent);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
}

.timeline-content {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
}

.timeline-title {
    margin-bottom: 0.5rem;
    color: var(--text);
}

.timeline-description {
    margin-bottom: 0.5rem;
    color: var(--text);
}

.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Modal and Form Styles */
.modal-content {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.modal-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
}

.form-control {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.form-control:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.form-select {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.form-select:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
}

.form-label {
    color: var(--text);
}

/* Button Styles */
.btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.btn-outline-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    color: white;
}

.btn-outline-success {
    color: #28a745;
    border-color: #28a745;
}

.btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-outline-secondary {
    color: var(--muted);
    border-color: var(--border);
}

.btn-outline-secondary:hover {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.btn-secondary {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.btn-secondary:hover {
    background-color: var(--border);
    border-color: var(--border);
    color: var(--text);
}

/* Badge Styles */
.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

/* File Attachment Styles */
.attachment-card {
    background: var(--bg);
    border: 2px solid var(--border) !important;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.attachment-card:hover {
    border-color: var(--accent) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

/* Notes Section Styles */
.note-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.note-card:hover {
    border-color: var(--accent);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.note-header {
    background-color: var(--bg-alt);
    border-bottom: 1px solid var(--border);
    padding: 0.75rem;
    border-radius: 8px 8px 0 0;
}

.note-content {
    padding: 1rem;
}

.note-meta {
    font-size: 0.875rem;
    color: var(--muted);
}

.note-text {
    color: var(--text);
    line-height: 1.6;
}

/* Form Text and Help Text Styles */
.form-text {
    color: var(--muted) !important;
    font-size: 0.875rem;
    line-height: 1.4;
}

.text-muted {
    color: var(--muted) !important;
}

small.text-muted {
    color: var(--muted) !important;
}

/* Progress and Upload Text */
.progress {
    background-color: var(--bg-alt);
}

.progress-bar {
    background-color: var(--accent);
}

/* Camera Placeholder Text */
.d-flex.flex-column.align-items-center.justify-content-center p.text-muted {
    color: var(--muted) !important;
}

/* File Upload Help Text */
div.form-text {
    color: var(--muted) !important;
    background-color: transparent;
}

/* Modal Body Text */
.modal-body p {
    color: var(--text);
}

.modal-body p.text-muted {
    color: var(--muted) !important;
}
</style>

<script>
function bookNewAppointment(patientId) {
    // Redirect to calendar with patient pre-selected
    window.location.href = `/doctor/calendar?patient_id=${patientId}`;
}

function printPatientSummary() {
    // Open print dialog for patient summary
    window.print();
}

function exportPatientData() {
    // Export patient data functionality
    showInfoModal('Export Patient Data', 'Export functionality will be implemented soon. This feature will allow you to export all patient data including medical history, notes, and files.');
}

function editPatient(patientId) {
    // Redirect to edit patient page
    window.location.href = `/doctor/patients/${patientId}/edit`;
}

function addEmergencyContact() {
    // Clear form and set title for adding
    document.getElementById('emergencyContactModalTitle').textContent = 'Add Emergency Contact';
    document.getElementById('emergencyContactForm').reset();
    clearValidationErrors();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('emergencyContactModal'));
    modal.show();
}

function editEmergencyContact() {
    // Set title for editing
    document.getElementById('emergencyContactModalTitle').textContent = 'Edit Emergency Contact';
    
    // Fill form with current values
    document.getElementById('emergencyContactNameInput').value = document.getElementById('emergencyContactName').textContent || '';
    document.getElementById('emergencyContactPhoneInput').value = document.getElementById('emergencyContactPhone').textContent || '';
    
    clearValidationErrors();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('emergencyContactModal'));
    modal.show();
}

function addMedicalHistory() {
    const patientId = <?= $patient['id'] ?>;
    showAddMedicalHistoryModal(patientId);
}

// Emergency contact form handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('emergencyContactForm');
    if (form) {
        form.addEventListener('submit', handleEmergencyContactSubmit);
    }
    
    // Handle edit note buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-note-btn')) {
            const button = e.target.closest('.edit-note-btn');
            const noteId = button.getAttribute('data-note-id');
            const noteTitle = button.getAttribute('data-note-title');
            const noteContent = button.getAttribute('data-note-content');
            
            editPatientNote(noteId, noteTitle, noteContent);
        }
    });
});

function handleEmergencyContactSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {
        emergency_contact: formData.get('emergency_contact').trim(),
        emergency_phone: formData.get('emergency_phone').trim()
    };
    
    // Clear previous validation errors
    clearValidationErrors();
    
    // Basic validation
    if (!data.emergency_contact) {
        showFieldError('emergencyContactNameInput', 'Contact name is required');
        return;
    }
    
    if (!data.emergency_phone) {
        showFieldError('emergencyContactPhoneInput', 'Phone number is required');
        return;
    }
    
    // Validate Egyptian phone number format
    const phoneRegex = /^(\+20|0)?1[0-9]{9}$/;
    if (!phoneRegex.test(data.emergency_phone)) {
        showFieldError('emergencyContactPhoneInput', 'Please enter a valid Egyptian phone number');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('saveEmergencyContactBtn');
    const spinner = document.getElementById('saveSpinner');
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Get patient ID from URL
    const patientId = window.location.pathname.split('/').pop();
    
    console.log('DEBUG: Sending emergency contact update', {
        patientId: patientId,
        data: data,
        url: `/api/patients/${patientId}/emergency-contact`
    });
    
    // Send API request
    fetch(`/api/patients/${patientId}/emergency-contact`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('DEBUG: Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('DEBUG: Response data:', result);
        if (result.ok) {
            // Success - update the UI
            updateEmergencyContactDisplay(data.emergency_contact, data.emergency_phone);
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('emergencyContactModal'));
            modal.hide();
            
            // Show success message
            showNotification('Emergency contact updated successfully!', 'success');
        } else {
            // Handle validation errors
            if (result.details) {
                Object.keys(result.details).forEach(field => {
                    const fieldName = field === 'emergency_contact' ? 'emergencyContactNameInput' : 'emergencyContactPhoneInput';
                    showFieldError(fieldName, result.details[field][0]);
                });
            } else {
                showNotification(result.error || 'Failed to update emergency contact', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Hide loading state
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
}

function updateEmergencyContactDisplay(name, phone) {
    const nameElement = document.getElementById('emergencyContactName');
    const phoneElement = document.getElementById('emergencyContactPhone');
    const noContactDiv = document.getElementById('noEmergencyContact');
    
    if (nameElement && phoneElement) {
        // Update existing display
        nameElement.textContent = name;
        phoneElement.textContent = phone;
    } else if (noContactDiv) {
        // Replace "no contact" message with contact info
        noContactDiv.innerHTML = `
            <div class="row">
                <div class="col-sm-4"><strong>Name:</strong></div>
                <div class="col-sm-8" id="emergencyContactName">${escapeHtml(name)}</div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4"><strong>Phone:</strong></div>
                <div class="col-sm-8" id="emergencyContactPhone">${escapeHtml(phone)}</div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button class="btn btn-sm btn-outline-secondary" onclick="editEmergencyContact()">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                </div>
            </div>
        `;
    }
}

function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const feedback = field.nextElementSibling;
    
    field.classList.add('is-invalid');
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function escapeHtml(text) {
    if (typeof text !== 'string') {
        return text;
    }
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Patient Files Functions
function showPatientUploadModal(patientId) {
    const modalHtml = `
        <div class="modal fade" id="patientUploadModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Patient File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="patientUploadForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="patient_id" value="${patientId}">
                            
                            <div class="mb-3">
                                <label class="form-label">File Type</label>
                                <select class="form-select" name="file_type" required>
                                    <option value="">Select File Type</option>
                                    <option value="medical_record">Medical Record</option>
                                    <option value="xray">X-ray</option>
                                    <option value="ct_scan">CT Scan</option>
                                    <option value="mri">MRI</option>
                                    <option value="ultrasound">Ultrasound</option>
                                    <option value="lab_report">Lab Report</option>
                                    <option value="blood_test">Blood Test</option>
                                    <option value="prescription">Prescription</option>
                                    <option value="insurance">Insurance Document</option>
                                    <option value="photo">Photo</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">File</label>
                                <input type="file" class="form-control" name="patient_file" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt" required>
                                <div class="form-text">
                                    Supported Files: Images (JPG, PNG, GIF), PDF, Word Documents, Text Files
                                    <br>Maximum File Size: 5 MB
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" 
                                          placeholder="Add a description for the file (optional)"></textarea>
                            </div>
                            
                            <div id="patientUploadProgress" class="mb-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Uploading...</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="patientUploadBtn">
                                <i class="bi bi-cloud-upload me-2"></i>Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('patientUploadModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('patientUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = document.getElementById('patientUploadBtn');
        const progressDiv = document.getElementById('patientUploadProgress');
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
                        showNotification('File uploaded successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error: ' + (response.message || 'Upload failed'), 'error');
                    }
                } catch (parseError) {
                    showNotification('Server response error', 'error');
                }
            } else {
                showNotification('HTTP Error ' + xhr.status, 'error');
            }
        });
        
        xhr.addEventListener('error', function() {
            showNotification('Upload error', 'error');
            uploadBtn.disabled = false;
            progressDiv.style.display = 'none';
        });
        
        xhr.open('POST', '/api/patients/files/upload');
        xhr.send(formData);
    });
    
    // Clean up modal on hide
    document.getElementById('patientUploadModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function openPatientCameraModal(patientId) {
    const modalHtml = `
        <div class="modal fade" id="patientCameraModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-camera me-2"></i>Take Photo for Patient
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="patientCameraId" value="${patientId}">
                        
                        <div class="mb-3">
                            <label class="form-label">Photo Type</label>
                            <select class="form-select" id="patientPhotoType" required>
                                <option value="">Select Photo Type</option>
                                <option value="medical_photo">Medical Photo</option>
                                <option value="xray">X-ray</option>
                                <option value="scan">Scan</option>
                                <option value="lab_result">Lab Result</option>
                                <option value="prescription">Prescription</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Photo Description</label>
                            <textarea class="form-control" id="patientPhotoDescription" rows="2" 
                                      placeholder="Add a description for the photo (optional)"></textarea>
                        </div>
                        
                        <!-- Camera View -->
                        <div class="text-center mb-3">
                            <div id="patientCameraContainer" class="border rounded p-3" style="background: #f8f9fa; min-height: 300px;">
                                <video id="patientCameraVideo" width="100%" height="300" style="max-width: 100%; border-radius: 8px; display: none;" autoplay playsinline></video>
                                <canvas id="patientCameraCanvas" width="640" height="480" style="max-width: 100%; border-radius: 8px; display: none;"></canvas>
                                <div id="patientCameraPlaceholder" class="d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 300px;">
                                    <i class="bi bi-camera text-muted" style="font-size: 4rem;"></i>
                                    <p class="text-muted mt-2">Click "Start Camera" to begin</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Camera Controls -->
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-primary" id="startPatientCameraBtn" onclick="startPatientCamera()">
                                <i class="bi bi-camera-video me-2"></i>Start Camera
                            </button>
                            <button type="button" class="btn btn-success" id="capturePatientPhotoBtn" onclick="capturePatientPhoto()" style="display: none;">
                                <i class="bi bi-camera me-2"></i>Take Photo
                            </button>
                            <button type="button" class="btn btn-warning" id="retakePatientPhotoBtn" onclick="retakePatientPhoto()" style="display: none;">
                                <i class="bi bi-arrow-clockwise me-2"></i>Retake
                            </button>
                            <button type="button" class="btn btn-danger" id="stopPatientCameraBtn" onclick="stopPatientCamera()" style="display: none;">
                                <i class="bi bi-stop-circle me-2"></i>Stop Camera
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="savePatientPhotoBtn" onclick="savePatientPhoto()" style="display: none;">
                            <i class="bi bi-check-lg me-2"></i>Save Photo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('patientCameraModal'));
    modal.show();
    
    // Clean up modal and stop camera on hide
    document.getElementById('patientCameraModal').addEventListener('hidden.bs.modal', function() {
        stopPatientCamera();
        this.remove();
    });
}

let patientCameraStream = null;
let capturedPatientImageData = null;

function startPatientCamera() {
    const video = document.getElementById('patientCameraVideo');
    const placeholder = document.getElementById('patientCameraPlaceholder');
    const startBtn = document.getElementById('startPatientCameraBtn');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const stopBtn = document.getElementById('stopPatientCameraBtn');
    
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'environment'
        } 
    })
    .then(function(stream) {
        patientCameraStream = stream;
        video.srcObject = stream;
        
        placeholder.style.display = 'none';
        video.style.display = 'block';
        
        startBtn.style.display = 'none';
        captureBtn.style.display = 'inline-block';
        stopBtn.style.display = 'inline-block';
        
        showNotification('Camera started successfully', 'success');
    })
    .catch(function(error) {
        showNotification('Error accessing camera: ' + error.message, 'error');
    });
}

function capturePatientPhoto() {
    const video = document.getElementById('patientCameraVideo');
    const canvas = document.getElementById('patientCameraCanvas');
    const context = canvas.getContext('2d');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const retakeBtn = document.getElementById('retakePatientPhotoBtn');
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    canvas.toBlob(function(blob) {
        capturedPatientImageData = blob;
        
        video.style.display = 'none';
        canvas.style.display = 'block';
        
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';
        saveBtn.style.display = 'inline-block';
        
        showNotification('Photo captured! You can now save it or retake.', 'success');
    }, 'image/jpeg', 0.8);
}

function retakePatientPhoto() {
    const video = document.getElementById('patientCameraVideo');
    const canvas = document.getElementById('patientCameraCanvas');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const retakeBtn = document.getElementById('retakePatientPhotoBtn');
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    
    capturedPatientImageData = null;
    
    canvas.style.display = 'none';
    video.style.display = 'block';
    
    retakeBtn.style.display = 'none';
    saveBtn.style.display = 'none';
    captureBtn.style.display = 'inline-block';
}

function stopPatientCamera() {
    if (patientCameraStream) {
        patientCameraStream.getTracks().forEach(track => track.stop());
        patientCameraStream = null;
    }
    
    const video = document.getElementById('patientCameraVideo');
    const canvas = document.getElementById('patientCameraCanvas');
    const placeholder = document.getElementById('patientCameraPlaceholder');
    const startBtn = document.getElementById('startPatientCameraBtn');
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const retakeBtn = document.getElementById('retakePatientPhotoBtn');
    const stopBtn = document.getElementById('stopPatientCameraBtn');
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    
    if (video) {
        video.style.display = 'none';
        video.srcObject = null;
    }
    
    if (canvas) canvas.style.display = 'none';
    if (placeholder) placeholder.style.display = 'flex';
    
    if (startBtn) startBtn.style.display = 'inline-block';
    if (captureBtn) captureBtn.style.display = 'none';
    if (retakeBtn) retakeBtn.style.display = 'none';
    if (stopBtn) stopBtn.style.display = 'none';
    if (saveBtn) saveBtn.style.display = 'none';
    
    capturedPatientImageData = null;
}

function savePatientPhoto() {
    if (!capturedPatientImageData) {
        showNotification('No photo captured', 'error');
        return;
    }
    
    const patientId = document.getElementById('patientCameraId').value;
    const photoType = document.getElementById('patientPhotoType').value;
    const description = document.getElementById('patientPhotoDescription').value;
    
    if (!photoType) {
        showNotification('Please select a photo type', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('patient_id', patientId);
    formData.append('file_type', photoType);
    formData.append('description', description);
    formData.append('patient_file', capturedPatientImageData, 'patient_photo_' + Date.now() + '.jpg');
    
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    saveBtn.disabled = true;
    
    fetch('/api/patients/files/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('patientCameraModal'));
            modal.hide();
            showNotification('Photo saved successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (data.message || 'Save failed'), 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
    });
}

function viewPatientAttachment(attachmentId, filePath, fileExt) {
    const viewUrl = `/api/patients/files/view/${attachmentId}`;
    
    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt.toLowerCase())) {
        showImageModal(viewUrl, attachmentId);
    } else if (fileExt.toLowerCase() === 'pdf') {
        window.open(viewUrl, '_blank');
    } else {
        downloadPatientAttachment(attachmentId);
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
                        <img src="${imageUrl}" class="img-fluid" style="max-height: 80vh;" alt="Patient Image">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="downloadPatientAttachment(${attachmentId})">
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

function downloadPatientAttachment(attachmentId, filename) {
    const downloadUrl = `/api/patients/files/download/${attachmentId}`;
    
    const link = document.createElement('a');
    link.href = downloadUrl;
    if (filename) {
        link.download = filename;
    }
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function deletePatientAttachment(attachmentId) {
    showDeleteConfirmationModal(
        'Delete File',
        'Are you sure you want to delete this file? This action cannot be undone.',
        'Delete File',
        () => {
            fetch(`/api/patients/files/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('File deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }
    );
}

// Patient Notes Functions
function showAddPatientNoteModal(patientId) {
    const modalHtml = `
        <div class="modal fade" id="patientNoteModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Medical Note</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="patientNoteForm">
                        <div class="modal-body">
                            <input type="hidden" name="patient_id" value="${patientId}">
                            
                            <div class="mb-3">
                                <label class="form-label">Note Title</label>
                                <input type="text" class="form-control" name="title" required 
                                       placeholder="Enter note title (e.g., General Examination, Follow-up)">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Note Content</label>
                                <textarea class="form-control" name="content" rows="6" required
                                          placeholder="Enter detailed medical note..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Save Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('patientNoteModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('patientNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/patients/notes', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showNotification('Note added successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + (data.message || 'Failed to add note'), 'error');
            }
        })
        .catch(error => {
            showNotification('Error: ' + error.message, 'error');
        });
    });
    
    // Clean up modal on hide
    document.getElementById('patientNoteModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function editPatientNote(noteId, title, content) {
    // Data is already escaped from HTML attributes, no need to escape again
    // Just ensure we have valid strings
    const safeTitle = title || '';
    const safeContent = content || '';
    
    const modalHtml = `
        <div class="modal fade" id="editPatientNoteModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Medical Note</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editPatientNoteForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Note Title</label>
                                <input type="text" class="form-control" name="title" required 
                                       value="${safeTitle}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Note Content</label>
                                <textarea class="form-control" name="content" rows="6" required>${safeContent}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="updateNoteBtn">
                                <span class="spinner-border spinner-border-sm d-none" id="updateNoteSpinner"></span>
                                <i class="bi bi-check-lg me-2"></i>Update Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editPatientNoteModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('editPatientNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const updateBtn = document.getElementById('updateNoteBtn');
        const spinner = document.getElementById('updateNoteSpinner');
        
        // Show loading state
        updateBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        console.log('DEBUG: Updating note', {
            noteId: noteId,
            title: formData.get('title'),
            content: formData.get('content')
        });
        
        // Convert FormData to URLSearchParams for PUT request
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }
        
        fetch(`/api/patients/notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString()
        })
        .then(response => {
            console.log('DEBUG: Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('DEBUG: Response data:', data);
            if (data.success) {
                modal.hide();
                showNotification('Note updated successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + (data.error || data.message || 'Failed to update note'), 'error');
            }
        })
        .catch(error => {
            console.error('Error updating note:', error);
            showNotification('Error: ' + error.message, 'error');
        })
        .finally(() => {
            // Hide loading state
            updateBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });
    
    // Clean up modal on hide
    document.getElementById('editPatientNoteModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function deletePatientNote(noteId) {
    showDeleteConfirmationModal(
        'Delete Note',
        'Are you sure you want to delete this medical note? This action cannot be undone.',
        'Delete Note',
        () => {
            fetch(`/api/patients/notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Note deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }
    );
}

// Confirmation Modal Functions
function showDeleteConfirmationModal(title, message, buttonText, onConfirm) {
    const modalId = 'deleteConfirmationModal';
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="bi bi-trash me-2"></i>${buttonText}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Handle confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        modal.hide();
        onConfirm();
    });
    
    // Clean up modal on hide
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function showInfoModal(title, message) {
    const modalId = 'infoModal';
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-primary">
                            <i class="bi bi-info-circle me-2"></i>
                            ${title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                            <i class="bi bi-check-circle me-2"></i>OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Clean up modal on hide
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
// Initialize Bootstrap Tooltips
function initializeTooltips() {
    // Initialize tooltips for elements with data-bs-toggle="tooltip"
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    // Initialize tooltips for elements with title attribute (including dropdown triggers)
    const titleElements = document.querySelectorAll('[title]:not([data-bs-toggle="tooltip"]):not([data-bs-toggle="dropdown"])');
    const titleTooltipList = [...titleElements].map(titleEl => new bootstrap.Tooltip(titleEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    // Initialize tooltips for dropdown buttons with title
    const dropdownTitleElements = document.querySelectorAll('[data-bs-toggle="dropdown"][title]');
    const dropdownTooltipList = [...dropdownTitleElements].map(dropdownEl => new bootstrap.Tooltip(dropdownEl, {
        boundary: 'viewport',
        fallbackPlacements: ['top', 'bottom', 'left', 'right'],
        sanitize: false,
        html: false,
        delay: { show: 500, hide: 100 },
        trigger: 'hover focus'
    }));
    
    return [...tooltipList, ...titleTooltipList, ...dropdownTooltipList];
}

// Function to refresh tooltips for dynamically added content
function refreshTooltips() {
    // Dispose of existing tooltips
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"], [title]');
    existingTooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.dispose();
        }
    });
    
    // Reinitialize all tooltips
    initializeTooltips();
}

// Initialize tooltips when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
});

// Refresh tooltips when modals are shown
document.addEventListener('shown.bs.modal', function() {
    setTimeout(() => {
        refreshTooltips();
    }, 100);
});

// Medical History Functions
function showAddMedicalHistoryModal(patientId) {
    const modalHtml = `
        <div class="modal fade" id="medicalHistoryModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-clipboard-heart me-2"></i>
                            Add Medical History
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="medicalHistoryForm">
                        <div class="modal-body">
                            <div id="medicalHistoryMessage" class="alert d-none" role="alert"></div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="medicalCondition" class="form-label">Medical Condition <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="medicalCondition" name="condition" required maxlength="255">
                                        <div class="form-text">Enter the medical condition or diagnosis</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="diagnosisDate" class="form-label">Diagnosis Date</label>
                                        <input type="date" class="form-control" id="diagnosisDate" name="diagnosis_date">
                                        <div class="form-text">When was this condition diagnosed (optional)</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="medicalStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="medicalStatus" name="status" required>
                                            <option value="active">Active</option>
                                            <option value="chronic">Chronic</option>
                                            <option value="resolved">Resolved</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <div class="form-text">Current status of this condition</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="medicalCategory" class="form-label">Category</label>
                                        <select class="form-select" id="medicalCategory" name="category">
                                            <option value="general">General</option>
                                            <option value="allergy">Allergy</option>
                                            <option value="medication">Medication</option>
                                            <option value="surgery">Surgery</option>
                                            <option value="family_history">Family History</option>
                                            <option value="social_history">Social History</option>
                                        </select>
                                        <div class="form-text">Category of medical history</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="medicalNotes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="medicalNotes" name="notes" rows="5" maxlength="1000"></textarea>
                                        <div class="form-text">Additional notes or details (optional)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="saveMedicalHistoryBtn">
                                <i class="bi bi-check-lg me-2"></i>Save Medical History
                                <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('medicalHistoryModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('medicalHistoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveMedicalHistory(patientId);
    });
    
    // Clean up modal on hide
    document.getElementById('medicalHistoryModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function saveMedicalHistory(patientId) {
    const form = document.getElementById('medicalHistoryForm');
    const formData = new FormData(form);
    const saveBtn = document.getElementById('saveMedicalHistoryBtn');
    const spinner = saveBtn.querySelector('.spinner-border');
    const messageDiv = document.getElementById('medicalHistoryMessage');
    
    // Show loading state
    saveBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Prepare data
    const data = {
        condition: formData.get('condition'),
        diagnosis_date: formData.get('diagnosis_date'),
        status: formData.get('status'),
        notes: formData.get('notes'),
        category: formData.get('category')
    };
    
    // Send request
    fetch(`/api/patients/${patientId}/medical-history`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        
        if (result.success) {
            showMessage(messageDiv, 'Medical history added successfully!', 'success');
            
            // Reset form
            form.reset();
            
            // Close modal after delay and refresh page
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('medicalHistoryModal'));
                modal.hide();
                window.location.reload();
            }, 1500);
        } else {
            showMessage(messageDiv, result.error || 'Failed to add medical history', 'danger');
        }
    })
    .catch(error => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        console.error('Error saving medical history:', error);
        showMessage(messageDiv, 'An error occurred. Please try again.', 'danger');
    });
}

function showMessage(element, message, type) {
    element.className = `alert alert-${type}`;
    element.textContent = message;
    element.classList.remove('d-none');
}

</script>
