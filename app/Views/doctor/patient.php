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
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <div class="avatar-circle-large <?= $patient['gender'] === 'Female' ? 'avatar-large-female' : 'avatar-large-male' ?> me-3">
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
                
                <!-- Current Doctor Badge -->
                <?php if (isset($currentDoctor) && $currentDoctor): ?>
                <div class="mt-3">
                    <span class="badge doctor-badge fs-6 px-4 py-2">
                        <i class="bi bi-person-badge me-2"></i>
                        <strong>Treating Doctor:</strong> 
                        <?= htmlspecialchars($currentDoctor['display_name'] ?? $currentDoctor['name']) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group-responsive d-flex flex-wrap justify-content-end gap-2">
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
                                            <button class="btn btn-outline-primary" onclick="editMedicalHistory(<?= $history['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteMedicalHistory(<?= $history['id'] ?>)">
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

/* Breadcrumb Styles */
.breadcrumb-item a {
    color: dodgerblue !important;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #1e90ff !important;
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: var(--text) !important;
    font-weight: 600;
}

.breadcrumb-item + .breadcrumb-item::before {
    color: var(--muted) !important;
    content: ">" !important;
}

/* Dark Mode Breadcrumb */
.dark .breadcrumb-item a {
    color: dodgerblue !important;
}

.dark .breadcrumb-item a:hover {
    color: #87ceeb !important;
}

.dark .breadcrumb-item.active {
    color: #ffffff !important;
    font-weight: 600;
}

.dark .breadcrumb-item + .breadcrumb-item::before {
    color: var(--muted) !important;
}

.avatar-circle-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

/* Gender-based avatar colors for large avatar */
.avatar-large-male {
    background: #3498db; /* Sky blue for males */
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.avatar-large-female {
    background: rgb(255, 85, 224); /* Pink for females */
    box-shadow: 0 2px 8px rgba(255, 85, 224, 0.3);
}

/* Hover effects for large avatar */
.avatar-large-male:hover {
    background: #2980b9;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.avatar-large-female:hover {
    background: rgb(255, 85, 224); /* Pink for females */
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(255, 85, 224, 0.4);
}

/* Default fallback for unknown gender */
.avatar-circle-large:not(.avatar-large-male):not(.avatar-large-female) {
    background: var(--accent);
    box-shadow: 0 2px 8px rgba(var(--accent-rgb), 0.3);
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

/* Medical History Timeline Styles */
.timeline {
    position: relative;
    padding: 0;
    margin: 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
    z-index: 1;
}

.timeline-item {
    position: relative;
    padding-left: 60px;
    margin-bottom: 2rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    border: 3px solid var(--bg);
    box-shadow: 0 2px 4px var(--shadow);
}

.timeline-content {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px var(--shadow);
    position: relative;
}

.timeline-content::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 15px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 8px 8px 0;
    border-color: transparent var(--border) transparent transparent;
}

.timeline-content::after {
    content: '';
    position: absolute;
    left: -7px;
    top: 15px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 8px 8px 0;
    border-color: transparent var(--card) transparent transparent;
}

.timeline-header h6 {
    color: var(--text);
    margin-bottom: 0.25rem;
}

.timeline-body {
    color: var(--text);
}

/* Medical History View Toggle */
.medical-history-view {
    transition: all 0.3s ease;
}

/* Badge Styles for Medical History */
.badge.bg-light.text-dark {
    background-color: var(--border) !important;
    color: var(--text) !important;
}

/* Dark Mode Timeline Adjustments */
.dark .timeline::before {
    background: var(--border);
}

.dark .timeline-marker {
    border-color: var(--bg);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.dark .timeline-content {
    background: var(--card);
    border-color: var(--border);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.dark .timeline-content::before {
    border-color: transparent var(--border) transparent transparent;
}

.dark .timeline-content::after {
    border-color: transparent var(--card) transparent transparent;
}

/* Accordion Dark Mode */
.dark .accordion-item {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.dark .accordion-button {
    background-color: var(--card) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

.dark .accordion-button:not(.collapsed) {
    background-color: var(--bg) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
    box-shadow: none !important;
}

.dark .accordion-button::after {
    filter: invert(1);
}

.dark .accordion-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

/* Status Badge Colors */
.badge.bg-success {
    background-color: var(--success) !important;
}

.badge.bg-info {
    background-color: var(--accent) !important;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
}

/* Dropdown Dark Mode */
.dark .dropdown-menu {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.dark .dropdown-item {
    color: var(--text) !important;
}

.dark .dropdown-item:hover,
.dark .dropdown-item:focus {
    background-color: var(--bg) !important;
    color: var(--text) !important;
}

.dark .dropdown-divider {
    border-color: var(--border) !important;
}

/* Button Group Dark Mode */
.dark .btn-group .btn-outline-secondary {
    color: var(--text) !important;
    border-color: var(--border) !important;
}

.dark .btn-group .btn-outline-secondary:hover {
    background-color: var(--bg) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .btn-group .btn-outline-secondary.active {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: white !important;
}

/* Empty State Dark Mode */
.dark .text-center i.text-muted {
    color: var(--muted) !important;
}

.dark .text-center h6.text-muted {
    color: var(--muted) !important;
}

.dark .text-center p.text-muted {
    color: var(--muted) !important;
}

/* Delete Confirmation Modal Dark Mode */
.dark #deleteConfirmationModal .modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.dark #deleteConfirmationModal .modal-header {
    border-bottom-color: var(--border) !important;
}

.dark #deleteConfirmationModal .modal-footer {
    border-top-color: var(--border) !important;
}

.dark #deleteConfirmationModal .modal-title {
    color: var(--danger) !important;
}

.dark #deleteConfirmationModal .modal-body {
    color: var(--text) !important;
}

.dark #deleteConfirmationModal .text-muted {
    color: var(--muted) !important;
}

/* Add Medical History Modal Dark Mode */
.dark #addMedicalHistoryModal .modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.dark #addMedicalHistoryModal .modal-header {
    border-bottom-color: var(--border) !important;
}

.dark #addMedicalHistoryModal .modal-footer {
    border-top-color: var(--border) !important;
}

.dark #addMedicalHistoryModal .modal-title {
    color: var(--text) !important;
}

.dark #addMedicalHistoryModal .modal-body {
    color: var(--text) !important;
}

.dark #addMedicalHistoryModal .form-label {
    color: var(--text) !important;
}

.dark #addMedicalHistoryModal .form-control {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark #addMedicalHistoryModal .form-control:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25) !important;
}

.dark #addMedicalHistoryModal .form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark #addMedicalHistoryModal .form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
}

/* General Delete Confirmation Modal Dark Mode */
.dark .modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.dark .modal-header {
    border-bottom-color: var(--border) !important;
}

.dark .modal-footer {
    border-top-color: var(--border) !important;
}

.dark .modal-title {
    color: var(--text) !important;
}

.dark .modal-body {
    color: var(--text) !important;
}

.dark .modal-body p {
    color: var(--text) !important;
}

/* Glasses Prescriptions Table Styles */
.prescription-values {
    font-size: 0.85rem;
    line-height: 1.3;
}

.prescription-values div {
    margin-bottom: 2px;
}

.prescription-values strong {
    color: var(--text);
    font-weight: 600;
}

/* Glasses Prescriptions Dark Mode */
.dark .prescription-values strong {
    color: var(--text) !important;
}

.dark .prescription-values {
    color: var(--text) !important;
}

/* Table responsive adjustments for glasses prescriptions */
@media (max-width: 768px) {
    .prescription-values {
        font-size: 0.8rem;
    }
    
    .prescription-values div {
        margin-bottom: 1px;
    }
}

/* Button Group Responsive Styles */
.btn-group-responsive {
    max-width: 100%;
}

.btn-group-responsive .btn {
    flex-shrink: 0;
    white-space: nowrap;
}

/* Responsive button sizing */
@media (max-width: 1199px) {
    .btn-group-responsive .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .btn-group-responsive .btn i {
        font-size: 0.875rem;
    }
}

@media (max-width: 991px) {
    .btn-group-responsive {
        justify-content: center !important;
        margin-top: 1rem;
    }
    
    .btn-group-responsive .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
}

@media (max-width: 575px) {
    .btn-group-responsive {
        flex-direction: column;
        width: 100%;
        gap: 0.5rem !important;
    }
    
    .btn-group-responsive .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Responsive Timeline */
@media (max-width: 768px) {
    .timeline::before {
        left: 15px;
    }
    
    .timeline-item {
        padding-left: 50px;
    }
    
    .timeline-marker {
        width: 30px;
        height: 30px;
        left: 0;
    }
    
    .timeline-content::before,
    .timeline-content::after {
        left: -6px;
        border-width: 6px 6px 6px 0;
    }
    
    .timeline-content::after {
        left: -5px;
    }
}

/* Doctor Badge Styles */
.doctor-badge {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border: 2px solid #0056b3;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    transition: all 0.3s ease;
    animation: doctorBadgePulse 3s infinite;
}

.doctor-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
}

@keyframes doctorBadgePulse {
    0% { box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); }
    50% { box-shadow: 0 4px 20px rgba(0, 123, 255, 0.5); }
    100% { box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); }
}

.doctor-badge i {
    animation: doctorIconSpin 2s linear infinite;
}

@keyframes doctorIconSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Dark mode support for doctor badge */
[data-bs-theme="dark"] .doctor-badge {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    border-color: #0a58ca;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
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
    // Get patient ID from URL
    const patientId = window.location.pathname.split('/').pop();
    
    // Show loading notification
    showNotification('Preparing patient data export...', 'info');
    
    // Create a temporary loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'exportLoadingOverlay';
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    const loadingContent = document.createElement('div');
    loadingContent.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    `;
    
    loadingContent.innerHTML = `
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5>Exporting Patient Data</h5>
        <p class="text-muted mb-0">Generating Word document with all patient information...</p>
    `;
    
    loadingOverlay.appendChild(loadingContent);
    document.body.appendChild(loadingOverlay);
    
    // First, test if the user is authenticated by making a fetch request
    fetch(`/api/patients/${patientId}/export`, {
        method: 'HEAD', // Use HEAD to test without downloading
        credentials: 'same-origin' // Include cookies
    })
    .then(response => {
        if (response.status === 401 || response.status === 403) {
            // User not authenticated or not authorized
            document.body.removeChild(loadingOverlay);
            showNotification('You must be logged in to export patient data. Please refresh the page and try again.', 'warning');
            return;
        }
        
        if (!response.ok && response.status !== 200) {
            // Other error
            document.body.removeChild(loadingOverlay);
            showNotification('Error accessing export function. Please try again.', 'error');
            return;
        }
        
        // User is authenticated, proceed with download
        downloadPatientData(patientId, loadingOverlay);
    })
    .catch(error => {
        console.error('Error testing export access:', error);
        document.body.removeChild(loadingOverlay);
        showNotification('Network error. Please check your connection and try again.', 'error');
    });
}

function downloadPatientData(patientId, loadingOverlay) {
    // Create download link and trigger export
    const downloadLink = document.createElement('a');
    downloadLink.href = `/api/patients/${patientId}/export`;
    downloadLink.download = `Patient_${patientId}_${new Date().toISOString().split('T')[0]}.docx`;
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    
    // Handle download completion/error
    let downloadCompleted = false;
    
    // Set a timeout to remove loading overlay in case of issues
    const timeoutId = setTimeout(() => {
        if (!downloadCompleted) {
            if (document.body.contains(loadingOverlay)) {
                document.body.removeChild(loadingOverlay);
            }
            downloadCompleted = true;
            showNotification('Export completed! Check your downloads folder.', 'success');
        }
    }, 8000); // 8 seconds timeout (increased for larger files)
    
    // Listen for window focus to detect download completion
    const handleWindowFocus = () => {
        if (!downloadCompleted) {
            setTimeout(() => {
                if (!downloadCompleted) {
                    if (document.body.contains(loadingOverlay)) {
                        document.body.removeChild(loadingOverlay);
                    }
                    downloadCompleted = true;
                    clearTimeout(timeoutId);
                    showNotification('Export completed! Check your downloads folder.', 'success');
                }
            }, 1500);
        }
        window.removeEventListener('focus', handleWindowFocus);
    };
    
    window.addEventListener('focus', handleWindowFocus);
    
    // Trigger the download
    try {
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        // For immediate feedback
        setTimeout(() => {
            if (!downloadCompleted) {
                showNotification('Download started. Generating your document...', 'info');
            }
        }, 1000);
        
    } catch (error) {
        console.error('Error triggering download:', error);
        if (!downloadCompleted) {
            if (document.body.contains(loadingOverlay)) {
                document.body.removeChild(loadingOverlay);
            }
            downloadCompleted = true;
            clearTimeout(timeoutId);
        }
        showNotification('Error starting export. Please try again.', 'error');
        if (document.body.contains(downloadLink)) {
            document.body.removeChild(downloadLink);
        }
    }
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

function showAddMedicalHistoryModal(patientId) {
    const modalId = 'addMedicalHistoryModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add Medical History Entry
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addMedicalHistoryForm">
                            <input type="hidden" id="addPatientId" value="${patientId}">
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="addConditionName" class="form-label">Condition Name *</label>
                                    <input type="text" class="form-control" id="addConditionName" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="addDiagnosisDate" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="addDiagnosisDate">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="addCategory" class="form-label">Category</label>
                                    <select class="form-select" id="addCategory">
                                        <option value="general">General</option>
                                        <option value="allergy">Allergy</option>
                                        <option value="medication">Medication</option>
                                        <option value="surgery">Surgery</option>
                                        <option value="family_history">Family History</option>
                                        <option value="social_history">Social History</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addStatus" class="form-label">Status</label>
                                    <select class="form-select" id="addStatus">
                                        <option value="active">Active</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="chronic">Chronic</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="addNotes" class="form-label">Notes</label>
                                <textarea class="form-control" id="addNotes" rows="4"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveNewMedicalHistory()">
                            <i class="bi bi-check me-1"></i>Add Entry
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Clean up when modal is hidden
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function saveNewMedicalHistory() {
    const patientId = document.getElementById('addPatientId').value;
    const formData = {
        condition: document.getElementById('addConditionName').value,
        diagnosis_date: document.getElementById('addDiagnosisDate').value,
        category: document.getElementById('addCategory').value,
        status: document.getElementById('addStatus').value,
        notes: document.getElementById('addNotes').value
    };
    
    // Validate required fields
    if (!formData.condition.trim()) {
        showNotification('Condition name is required', 'danger');
        return;
    }
    
    fetch(`/api/patients/${patientId}/medical-history`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Medical history added successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addMedicalHistoryModal'));
            modal.hide();
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error adding medical history: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding medical history: ' + error.message, 'danger');
    });
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
                        
                        <div class="mb-3" id="patientPhotoTypeContainer">
                            <label class="form-label">Photo Type</label>
                            <select class="form-select" id="patientPhotoType" required>
                                <option value="medical_photo" selected>Medical Photo</option>
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
                                    <p class="text-muted mt-2">Loading camera...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Camera Controls -->
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-success" id="capturePatientPhotoBtn" onclick="capturePatientPhoto()">
                                <i class="bi bi-camera me-2"></i>Take Photo
                            </button>
                            <button type="button" class="btn btn-warning" id="retakePatientPhotoBtn" onclick="retakePatientPhoto()" style="display: none;">
                                <i class="bi bi-arrow-clockwise me-2"></i>Retake
                            </button>
                            <button type="button" class="btn btn-danger" id="stopPatientCameraBtn" onclick="stopPatientCamera()">
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
    
    // Start camera automatically when modal is shown
    document.getElementById('patientCameraModal').addEventListener('shown.bs.modal', function() {
        startPatientCamera();
    });
    
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
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const stopBtn = document.getElementById('stopPatientCameraBtn');
    const photoTypeContainer = document.getElementById('patientPhotoTypeContainer');
    
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
        
        // Hide photo type field when camera starts
        if (photoTypeContainer) {
            photoTypeContainer.style.display = 'none';
        }
        
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
    const captureBtn = document.getElementById('capturePatientPhotoBtn');
    const retakeBtn = document.getElementById('retakePatientPhotoBtn');
    const stopBtn = document.getElementById('stopPatientCameraBtn');
    const saveBtn = document.getElementById('savePatientPhotoBtn');
    const photoTypeContainer = document.getElementById('patientPhotoTypeContainer');
    
    if (video) {
        video.style.display = 'none';
        video.srcObject = null;
    }
    
    if (canvas) canvas.style.display = 'none';
    if (placeholder) placeholder.style.display = 'flex';
    
    // Show photo type field when camera stops
    if (photoTypeContainer) {
        photoTypeContainer.style.display = 'block';
    }
    
    if (captureBtn) captureBtn.style.display = 'inline-block';
    if (retakeBtn) retakeBtn.style.display = 'none';
    if (stopBtn) stopBtn.style.display = 'inline-block';
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
    // Hide all tooltips first to prevent conflicts
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.hide();
        }
    });
    
    // Wait a bit for tooltips to hide, then show modal
    setTimeout(() => {
        showGeneralDeleteConfirmationModal(
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
    }, 100);
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
    // Hide all tooltips first to prevent conflicts
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.hide();
        }
    });
    
    // Wait a bit for tooltips to hide, then show modal
    setTimeout(() => {
        showGeneralDeleteConfirmationModal(
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
    }, 100);
}

// Confirmation Modal Functions
function showGeneralDeleteConfirmationModal(title, message, buttonText, onConfirm) {
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
function switchMedicalHistoryView(viewType) {
    const timelineView = document.getElementById('timelineView');
    const detailsView = document.getElementById('detailsView');
    const timelineBtn = document.getElementById('timelineViewBtn');
    const detailsBtn = document.getElementById('detailsViewBtn');
    
    if (viewType === 'timeline') {
        timelineView.style.display = 'block';
        detailsView.style.display = 'none';
        timelineBtn.classList.add('active');
        detailsBtn.classList.remove('active');
    } else {
        timelineView.style.display = 'none';
        detailsView.style.display = 'block';
        timelineBtn.classList.remove('active');
        detailsBtn.classList.add('active');
    }
}

function viewMedicalHistory(historyId) {
    const patientId = <?= $patient['id'] ?>;
    
    // Check if this is an old format entry (from medical_history table)
    // Old format entries don't have individual API endpoints, so we'll handle them differently
    const historyElement = document.querySelector(`[onclick*="viewMedicalHistory(${historyId})"]`);
    if (historyElement) {
        const timelineItem = historyElement.closest('.timeline-item, .accordion-item');
        if (timelineItem && timelineItem.querySelector('[data-entry-type="old_format"]')) {
            showNotification('Viewing old format medical history is not supported yet. Please use the Details view to see all information.', 'info');
            return;
        }
    }
    
    // Fetch and display medical history details in a modal
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMedicalHistoryModal(data.data, 'view');
            } else {
                showNotification('Error loading medical history details: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading medical history details: ' + error.message, 'danger');
        });
}

function editMedicalHistory(historyId) {
    const patientId = <?= $patient['id'] ?>;
    
    // Check if this is an old format entry (from medical_history table)
    // Old format entries don't have individual API endpoints, so we'll handle them differently
    const historyElement = document.querySelector(`[onclick*="editMedicalHistory(${historyId})"]`);
    if (historyElement) {
        const timelineItem = historyElement.closest('.timeline-item, .accordion-item');
        if (timelineItem && timelineItem.querySelector('[data-entry-type="old_format"]')) {
            showNotification('Editing old format medical history is not supported yet. Please create a new entry with the updated information.', 'info');
            return;
        }
    }
    
    // Fetch and display medical history for editing
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMedicalHistoryModal(data.data, 'edit');
            } else {
                showNotification('Error loading medical history details: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading medical history details: ' + error.message, 'danger');
        });
}

function showMedicalHistoryModal(data, mode) {
    // Create and show modal for viewing/editing medical history
    const modalId = 'medicalHistoryModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const isEdit = mode === 'edit';
    const isView = mode === 'view';
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-clipboard-heart me-2"></i>
                            ${isEdit ? 'Edit' : 'View'} Medical History
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="medicalHistoryForm">
                            <input type="hidden" id="historyId" value="${data.id}">
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="conditionName" class="form-label">Condition Name</label>
                                    <input type="text" class="form-control" id="conditionName" 
                                           value="${data.condition_name || ''}" ${isView ? 'readonly' : ''}>
                                </div>
                                <div class="col-md-4">
                                    <label for="diagnosisDate" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="diagnosisDate" 
                                           value="${data.diagnosis_date || ''}" ${isView ? 'readonly' : ''}>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" ${isView ? 'disabled' : ''}>
                                        <option value="general" ${data.category === 'general' ? 'selected' : ''}>General</option>
                                        <option value="allergy" ${data.category === 'allergy' ? 'selected' : ''}>Allergy</option>
                                        <option value="medication" ${data.category === 'medication' ? 'selected' : ''}>Medication</option>
                                        <option value="surgery" ${data.category === 'surgery' ? 'selected' : ''}>Surgery</option>
                                        <option value="family_history" ${data.category === 'family_history' ? 'selected' : ''}>Family History</option>
                                        <option value="social_history" ${data.category === 'social_history' ? 'selected' : ''}>Social History</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" ${isView ? 'disabled' : ''}>
                                        <option value="active" ${data.status === 'active' ? 'selected' : ''}>Active</option>
                                        <option value="resolved" ${data.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                                        <option value="chronic" ${data.status === 'chronic' ? 'selected' : ''}>Chronic</option>
                                        <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" rows="4" ${isView ? 'readonly' : ''}>${data.notes || ''}</textarea>
                            </div>
                            
                            ${data.created_at ? `
                                <div class="text-muted small">
                                    <i class="bi bi-clock me-1"></i>
                                    Created: ${new Date(data.created_at).toLocaleDateString()}
                                    ${data.doctor_name ? ` by ${data.doctor_name}` : ''}
                                </div>
                            ` : ''}
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            ${isView ? 'Close' : 'Cancel'}
                        </button>
                        ${isEdit ? `
                            <button type="button" class="btn btn-primary" onclick="saveEditMedicalHistory()">
                                <i class="bi bi-check me-1"></i>Save Changes
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Clean up when modal is hidden
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function saveEditMedicalHistory() {
    const patientId = <?= $patient['id'] ?>;
    const historyId = document.getElementById('historyId').value;
    const formData = {
        condition: document.getElementById('conditionName').value,
        diagnosis_date: document.getElementById('diagnosisDate').value,
        category: document.getElementById('category').value,
        status: document.getElementById('status').value,
        notes: document.getElementById('notes').value
    };
    
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Medical history updated successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('medicalHistoryModal'));
            modal.hide();
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error updating medical history: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating medical history: ' + error.message, 'danger');
    });
}

function deleteMedicalHistory(historyId) {
    // Check if this is an old format entry (from medical_history table)
    const historyElement = document.querySelector(`[onclick*="deleteMedicalHistory(${historyId})"]`);
    if (historyElement) {
        const timelineItem = historyElement.closest('.timeline-item, .accordion-item');
        if (timelineItem && timelineItem.querySelector('[data-entry-type="old_format"]')) {
            showNotification('Deleting old format medical history is not supported yet. Please contact administrator for assistance.', 'warning');
            return;
        }
    }
    
    showDeleteConfirmationModal(historyId);
}

function showDeleteConfirmationModal(historyId) {
    const modalId = 'deleteConfirmationModal';
    let existingModal = document.getElementById(modalId);
    
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
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 mb-2">Delete Medical History Entry</h6>
                            <p class="text-muted">Are you sure you want to delete this medical history entry? This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteMedicalHistory(${historyId})">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Clean up when modal is hidden
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function confirmDeleteMedicalHistory(historyId) {
    const patientId = <?= $patient['id'] ?>;
    
    fetch(`/api/patients/${patientId}/medical-history/${historyId}`, {
        method: 'DELETE'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Medical history deleted successfully', 'success');
            // Hide the confirmation modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
            modal.hide();
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error deleting medical history: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting medical history: ' + error.message, 'danger');
    });
}

// Glasses Prescriptions Functions
function showAddGlassesPrescriptionModal(patientId) {
    const modalId = 'addGlassesPrescriptionModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-eyeglasses me-2"></i>
                            Add Glasses Prescription
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addGlassesPrescriptionForm">
                            <input type="hidden" id="glassesPatientId" value="${patientId}">
                            
                            <!-- Lens Type and Appointment Selection -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="glassesAppointmentId" class="form-label">Select Appointment *</label>
                                    <select class="form-select" id="glassesAppointmentId" required>
                                        <option value="">Loading appointments...</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="glassesLensType" class="form-label">Lens Type *</label>
                                    <select class="form-select" id="glassesLensType" required>
                                        <option value="Single Vision">Single Vision</option>
                                        <option value="Bifocal">Bifocal</option>
                                        <option value="Progressive">Progressive</option>
                                        <option value="Reading">Reading</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Distance Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="distanceSphereR" 
                                                           step="0.25" min="-30" max="30" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="distanceCylinderR" 
                                                           step="0.25" min="-10" max="10" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="distanceAxisR" 
                                                           min="1" max="180" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="distanceSphereL" 
                                                           step="0.25" min="-30" max="30" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="distanceCylinderL" 
                                                           step="0.25" min="-10" max="10" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="distanceAxisL" 
                                                           min="1" max="180" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Near Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-book me-2"></i>Near Vision</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="nearSphereR" 
                                                           step="0.25" min="-30" max="30" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="nearCylinderR" 
                                                           step="0.25" min="-10" max="10" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="nearAxisR" 
                                                           min="1" max="180" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="nearSphereL" 
                                                           step="0.25" min="-30" max="30" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="nearCylinderL" 
                                                           step="0.25" min="-10" max="10" placeholder="0.00">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="nearAxisL" 
                                                           min="1" max="180" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PD and Comments -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pdDistance" class="form-label">PD Distance (mm)</label>
                                    <input type="number" class="form-control" id="pdDistance" 
                                           step="0.5" min="40" max="80" placeholder="62.0">
                                </div>
                                <div class="col-md-6">
                                    <label for="pdNear" class="form-label">PD Near (mm)</label>
                                    <input type="number" class="form-control" id="pdNear" 
                                           step="0.5" min="40" max="80" placeholder="60.0">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="glassesComments" class="form-label">Comments</label>
                                <textarea class="form-control" id="glassesComments" rows="3" 
                                          placeholder="Additional notes or comments about the prescription..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveGlassesPrescription()">
                            <i class="bi bi-check me-1"></i>Add Prescription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Load patient appointments
    loadPatientAppointments(patientId);
    
    // Clean up when modal is hidden
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function loadPatientAppointments(patientId) {
    const select = document.getElementById('glassesAppointmentId');
    
    fetch(`/api/patients/${patientId}/appointments`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                select.innerHTML = '<option value="">Select an appointment</option>';
                data.data.forEach(appointment => {
                    const option = document.createElement('option');
                    option.value = appointment.id;
                    option.textContent = `${appointment.date} at ${appointment.start_time} - ${appointment.visit_type}`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No appointments available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            select.innerHTML = '<option value="">Error loading appointments</option>';
        });
}

function saveGlassesPrescription() {
    const formData = {
        appointment_id: document.getElementById('glassesAppointmentId').value,
        lens_type: document.getElementById('glassesLensType').value,
        distance_sphere_r: document.getElementById('distanceSphereR').value || null,
        distance_cylinder_r: document.getElementById('distanceCylinderR').value || null,
        distance_axis_r: document.getElementById('distanceAxisR').value || null,
        distance_sphere_l: document.getElementById('distanceSphereL').value || null,
        distance_cylinder_l: document.getElementById('distanceCylinderL').value || null,
        distance_axis_l: document.getElementById('distanceAxisL').value || null,
        near_sphere_r: document.getElementById('nearSphereR').value || null,
        near_cylinder_r: document.getElementById('nearCylinderR').value || null,
        near_axis_r: document.getElementById('nearAxisR').value || null,
        near_sphere_l: document.getElementById('nearSphereL').value || null,
        near_cylinder_l: document.getElementById('nearCylinderL').value || null,
        near_axis_l: document.getElementById('nearAxisL').value || null,
        PD_DISTANCE: document.getElementById('pdDistance').value || null,
        PD_NEAR: document.getElementById('pdNear').value || null,
        comments: document.getElementById('glassesComments').value || null
    };
    
    // Validate required fields
    if (!formData.appointment_id) {
        showNotification('Please select an appointment', 'danger');
        return;
    }
    
    if (!formData.lens_type) {
        showNotification('Please select a lens type', 'danger');
        return;
    }
    
    // Convert FormData to URLSearchParams for POST request
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(formData)) {
        if (value !== null && value !== '') {
            params.append(key, value);
        }
    }
    
    fetch('/api/prescriptions/glasses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Glasses prescription added successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addGlassesPrescriptionModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (data.error || data.message || 'Failed to add prescription'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error: ' + error.message, 'error');
    });
}

function viewGlassesPrescription(prescriptionId) {
    const url = `/api/prescriptions/glasses/${prescriptionId}?t=${Date.now()}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        },
        cache: 'no-cache'
    })
        .then(response => {
            console.log('View Response status:', response.status);
            console.log('View Response URL:', response.url);
            if (!response.ok) {
                // Log the response text for debugging
                return response.text().then(text => {
                    console.log('Error response text:', text);
                    throw new Error(`HTTP error! status: ${response.status} - ${text.substring(0, 100)}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('View Response data:', data);
            if (data.success) {
                showGlassesPrescriptionModal(data.data, 'view');
            } else {
                showNotification('Error loading prescription details: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('View Error:', error);
            showNotification('Error: ' + error.message, 'error');
        });
}

function editGlassesPrescription(prescriptionId) {
    const url = `/api/prescriptions/glasses/${prescriptionId}?t=${Date.now()}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        },
        cache: 'no-cache'
    })
        .then(response => {
            console.log('Edit Response status:', response.status);
            console.log('Edit Response URL:', response.url);
            if (!response.ok) {
                // Log the response text for debugging
                return response.text().then(text => {
                    console.log('Edit Error response text:', text);
                    throw new Error(`HTTP error! status: ${response.status} - ${text.substring(0, 100)}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Edit Response data:', data);
            if (data.success) {
                showGlassesPrescriptionModal(data.data, 'edit');
            } else {
                showNotification('Error loading prescription details: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Edit Error:', error);
            showNotification('Error: ' + error.message, 'error');
        });
}

function showGlassesPrescriptionModal(data, mode) {
    const modalId = 'viewEditGlassesPrescriptionModal';
    let existingModal = document.getElementById(modalId);
    
    if (existingModal) {
        existingModal.remove();
    }
    
    const isEdit = mode === 'edit';
    const isView = mode === 'view';
    const readonly = isView ? 'readonly' : '';
    const disabled = isView ? 'disabled' : '';
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-eyeglasses me-2"></i>
                            ${isEdit ? 'Edit' : 'View'} Glasses Prescription
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editGlassesPrescriptionForm">
                            <input type="hidden" id="editPrescriptionId" value="${data.id}">
                            
                            <!-- Lens Type -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Lens Type</label>
                                    <select class="form-select" id="editLensType" ${disabled}>
                                        <option value="Single Vision" ${data.lens_type === 'Single Vision' ? 'selected' : ''}>Single Vision</option>
                                        <option value="Bifocal" ${data.lens_type === 'Bifocal' ? 'selected' : ''}>Bifocal</option>
                                        <option value="Progressive" ${data.lens_type === 'Progressive' ? 'selected' : ''}>Progressive</option>
                                        <option value="Reading" ${data.lens_type === 'Reading' ? 'selected' : ''}>Reading</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Created Date</label>
                                    <input type="text" class="form-control" value="${new Date(data.created_at).toLocaleString()}" readonly>
                                </div>
                            </div>
                            
                            <!-- Distance Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Distance Vision</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="editDistanceSphereR" 
                                                           step="0.25" min="-30" max="30" value="${data.distance_sphere_r || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="editDistanceCylinderR" 
                                                           step="0.25" min="-10" max="10" value="${data.distance_cylinder_r || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="editDistanceAxisR" 
                                                           min="1" max="180" value="${data.distance_axis_r || ''}" ${readonly}>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="editDistanceSphereL" 
                                                           step="0.25" min="-30" max="30" value="${data.distance_sphere_l || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="editDistanceCylinderL" 
                                                           step="0.25" min="-10" max="10" value="${data.distance_cylinder_l || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="editDistanceAxisL" 
                                                           min="1" max="180" value="${data.distance_axis_l || ''}" ${readonly}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Near Vision Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-book me-2"></i>Near Vision</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success">Right Eye (OD)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="editNearSphereR" 
                                                           step="0.25" min="-30" max="30" value="${data.near_sphere_r || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="editNearCylinderR" 
                                                           step="0.25" min="-10" max="10" value="${data.near_cylinder_r || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="editNearAxisR" 
                                                           min="1" max="180" value="${data.near_axis_r || ''}" ${readonly}>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success">Left Eye (OS)</h6>
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="form-label">Sphere</label>
                                                    <input type="number" class="form-control" id="editNearSphereL" 
                                                           step="0.25" min="-30" max="30" value="${data.near_sphere_l || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Cylinder</label>
                                                    <input type="number" class="form-control" id="editNearCylinderL" 
                                                           step="0.25" min="-10" max="10" value="${data.near_cylinder_l || ''}" ${readonly}>
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label">Axis</label>
                                                    <input type="number" class="form-control" id="editNearAxisL" 
                                                           min="1" max="180" value="${data.near_axis_l || ''}" ${readonly}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PD and Comments -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">PD Distance (mm)</label>
                                    <input type="number" class="form-control" id="editPdDistance" 
                                           step="0.5" min="40" max="80" value="${data.PD_DISTANCE || ''}" ${readonly}>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PD Near (mm)</label>
                                    <input type="number" class="form-control" id="editPdNear" 
                                           step="0.5" min="40" max="80" value="${data.PD_NEAR || ''}" ${readonly}>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comments</label>
                                <textarea class="form-control" id="editGlassesComments" rows="3" ${readonly}>${data.comments || ''}</textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            ${isView ? 'Close' : 'Cancel'}
                        </button>
                        ${isEdit ? `
                            <button type="button" class="btn btn-primary" onclick="updateGlassesPrescription()">
                                <i class="bi bi-check me-1"></i>Save Changes
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
    
    // Clean up when modal is hidden
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function updateGlassesPrescription() {
    const prescriptionId = document.getElementById('editPrescriptionId').value;
    const formData = {
        lens_type: document.getElementById('editLensType').value,
        distance_sphere_r: document.getElementById('editDistanceSphereR').value || null,
        distance_cylinder_r: document.getElementById('editDistanceCylinderR').value || null,
        distance_axis_r: document.getElementById('editDistanceAxisR').value || null,
        distance_sphere_l: document.getElementById('editDistanceSphereL').value || null,
        distance_cylinder_l: document.getElementById('editDistanceCylinderL').value || null,
        distance_axis_l: document.getElementById('editDistanceAxisL').value || null,
        near_sphere_r: document.getElementById('editNearSphereR').value || null,
        near_cylinder_r: document.getElementById('editNearCylinderR').value || null,
        near_axis_r: document.getElementById('editNearAxisR').value || null,
        near_sphere_l: document.getElementById('editNearSphereL').value || null,
        near_cylinder_l: document.getElementById('editNearCylinderL').value || null,
        near_axis_l: document.getElementById('editNearAxisL').value || null,
        PD_DISTANCE: document.getElementById('editPdDistance').value || null,
        PD_NEAR: document.getElementById('editPdNear').value || null,
        comments: document.getElementById('editGlassesComments').value || null
    };
    
    // Convert FormData to URLSearchParams for PUT request
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(formData)) {
        if (value !== null && value !== '') {
            params.append(key, value);
        }
    }
    
    fetch(`/api/prescriptions/glasses/${prescriptionId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Glasses prescription updated successfully', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('viewEditGlassesPrescriptionModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (data.error || data.message || 'Failed to update prescription'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error: ' + error.message, 'error');
    });
}

function deleteGlassesPrescription(prescriptionId) {
    // Hide all tooltips first to prevent conflicts
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(element => {
        const tooltip = bootstrap.Tooltip.getInstance(element);
        if (tooltip) {
            tooltip.hide();
        }
    });
    
    // Wait a bit for tooltips to hide, then show modal
    setTimeout(() => {
        showGeneralDeleteConfirmationModal(
            'Delete Glasses Prescription',
            'Are you sure you want to delete this glasses prescription? This action cannot be undone.',
            'Delete Prescription',
            () => {
                fetch(`/api/prescriptions/glasses/${prescriptionId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Glasses prescription deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error: ' + (data.error || data.message), 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error: ' + error.message, 'error');
                });
            }
        );
    }, 100);
}

function printGlassesPrescription(prescriptionId) {
    const printUrl = `/print/glasses-prescription/${prescriptionId}?t=${Date.now()}`;
    console.log('Opening print URL:', printUrl);
    window.open(printUrl, '_blank');
}

// Helper function to clear browser cache for specific URLs
function clearApiCache() {
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }
}

// Call this when the page loads to ensure fresh data
document.addEventListener('DOMContentLoaded', function() {
    // Clear any cached API responses
    clearApiCache();
    
    // Add a refresh handler for the glasses prescriptions section
    const glassesSection = document.querySelector('.card:has(h5:contains("Glasses Prescriptions"))');
    if (glassesSection) {
        // Add a small refresh button to the glasses prescriptions header
        const header = glassesSection.querySelector('.card-header .d-flex');
        if (header && !header.querySelector('.refresh-glasses-btn')) {
            const refreshBtn = document.createElement('button');
            refreshBtn.className = 'btn btn-outline-secondary btn-sm me-2 refresh-glasses-btn';
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
            refreshBtn.title = 'Refresh glasses prescriptions';
            refreshBtn.onclick = function() {
                clearApiCache();
                location.reload();
            };
            header.appendChild(refreshBtn);
        }
    }
});

</script>
