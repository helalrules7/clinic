<!-- Breadcrumb -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/doctor/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/doctor/patients">Patients</a></li>
            <li class="breadcrumb-item"><a href="/doctor/patients/<?= $appointment['patient_id'] ?? '' ?>"><?= htmlspecialchars($appointment['patient_name'] ?? '') ?></a></li>
            <li class="breadcrumb-item"><a href="/doctor/appointments/<?= $appointment['id'] ?? '' ?>">Appointment</a></li>
            <li class="breadcrumb-item active">Edit Consultation</li>
        </ol>
    </nav>
    <a href="/doctor/appointments/<?= $appointment['id'] ?? '' ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Appointment
    </a>
</div>

<!-- Page Header -->
<div class="d-flex align-items-center mb-4">
    <div class="me-3">
        <div class="icon-circle bg-primary text-white">
            <i class="bi bi-pencil-square"></i>
        </div>
    </div>
    <div>
        <h1 class="h3 mb-0">Edit Consultation</h1>
        <p class="text-muted mb-0">Edit consultation notes for patient: <?= htmlspecialchars($appointment['patient_name'] ?? '') ?></p>
    </div>
</div>

<!-- Patient Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="card-title text-primary">
                            <i class="bi bi-person"></i> Patient Information
                        </h6>
                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($appointment['patient_name'] ?? '') ?></p>
                        <p class="mb-1"><strong>Age:</strong> <?= htmlspecialchars($appointment['patient_age'] ?? '') ?> years</p>
                        <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? '') ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="card-title text-primary">
                            <i class="bi bi-calendar-event"></i> Appointment Information
                        </h6>
                        <p class="mb-1"><strong>Date:</strong> <?= htmlspecialchars($appointment['date'] ?? '') ?></p>
                        <p class="mb-1"><strong>Time:</strong> <?= htmlspecialchars($appointment['start_time'] ?? '') ?> - <?= htmlspecialchars($appointment['end_time'] ?? '') ?></p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-<?= $appointment['status'] === 'Completed' ? 'success' : ($appointment['status'] === 'Cancelled' ? 'danger' : 'warning') ?>">
                                <?= htmlspecialchars($appointment['status'] === 'Completed' ? 'Completed' : ($appointment['status'] === 'Cancelled' ? 'Cancelled' : 'Confirmed')) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Previous Consultation Notes -->
<?php if (!empty($consultationNotes) && count($consultationNotes) > 1): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i>
                    Previous Consultation Notes
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach (array_slice($consultationNotes, 1) as $note): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card border-secondary">
                            <div class="card-header bg-light">
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?= date('M j, Y \a\t g:i A', strtotime($note['created_at'])) ?>
                                </small>
                                <a href="/doctor/appointments/<?= $appointment['id'] ?>/edit?note_id=<?= $note['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary float-end">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($note['chief_complaint'])): ?>
                                <p><strong>Chief Complaint:</strong> <?= htmlspecialchars(substr($note['chief_complaint'], 0, 100)) ?><?= strlen($note['chief_complaint']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                                <?php if (!empty($note['diagnosis'])): ?>
                                <p><strong>Diagnosis:</strong> <?= htmlspecialchars(substr($note['diagnosis'], 0, 100)) ?><?= strlen($note['diagnosis']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Consultation Form -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard2-pulse"></i>
                    Edit Consultation Notes
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php 
                // Get the latest consultation note for editing, or prepare for new one
                $consultation = !empty($consultationNotes) ? $consultationNotes[0] : [];
                $isEditing = !empty($consultation);
                ?>
                
                <form method="POST" action="/doctor/appointments/<?= $appointment['id'] ?? '' ?>/edit" id="consultationForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <?php if ($isEditing): ?>
                    <!-- Note ID for updating existing note -->
                    <input type="hidden" name="note_id" value="<?= $consultation['id'] ?>">
                    
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        You are editing an existing consultation note from <?= date('M j, Y \a\t g:i A', strtotime($consultation['created_at'])) ?>.
                        <a href="/doctor/appointments/<?= $appointment['id'] ?>/edit/new" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-plus me-1"></i>Add New Note Instead
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-plus-circle me-2"></i>
                        Creating a new consultation note for this appointment.
                    </div>
                    <?php endif; ?>
                    
                    <!-- Chief Complaint -->
                    <div class="mb-3">
                        <label for="chief_complaint" class="form-label">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            <span class="text-danger">*</span> Chief Complaint (Required)
                            <small class="text-muted ms-2">(Required for registration and saving)</small>
                        </label>
                        <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3" 
                            placeholder="Enter the patient's main complaint..."><?= htmlspecialchars($consultation['chief_complaint'] ?? '') ?></textarea>
                    </div>

                    <!-- History of Present Illness -->
                    <div class="mb-3">
                        <label for="hx_present_illness" class="form-label">
                            <i class="bi bi-clock-history text-info"></i>
                            History of Present Illness
                        </label>
                        <textarea class="form-control" id="hx_present_illness" name="hx_present_illness" rows="3"
                            placeholder="Enter the history of present illness..."><?= htmlspecialchars($consultation['hx_present_illness'] ?? '') ?></textarea>
                    </div>

                    <!-- Diagnosis -->
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">
                            <i class="bi bi-clipboard2-check text-danger"></i>
                            <span class="text-danger">*</span> Diagnosis (Required)
                            <small class="text-muted ms-2">(Required for registration and saving)</small>
                        </label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"
                            placeholder="Enter diagnosis..."><?= htmlspecialchars($consultation['diagnosis'] ?? '') ?></textarea>
                    </div>

                    <!-- Diagnosis Code -->
                    <div class="mb-3">
                        <label for="diagnosis_code" class="form-label">
                            <i class="bi bi-upc text-secondary"></i>
                            Diagnosis Code (ICD-10)
                        </label>
                        <input type="text" class="form-control" id="diagnosis_code" name="diagnosis_code"
                            placeholder="e.g., H25.9" value="<?= htmlspecialchars($consultation['diagnosis_code'] ?? '') ?>">
                    </div>

                    <!-- Systemic Disease -->
                    <div class="mb-3">
                        <label for="systemic_disease" class="form-label">
                            <i class="bi bi-heart-pulse text-danger"></i>
                            Systemic Disease
                        </label>
                        <textarea class="form-control" id="systemic_disease" name="systemic_disease" rows="2"
                            placeholder="Enter any systemic diseases..."><?= htmlspecialchars($consultation['systemic_disease'] ?? '') ?></textarea>
                    </div>

                    <!-- Medication -->
                    <div class="mb-3">
                        <label for="medication" class="form-label">
                            <i class="bi bi-capsule text-primary"></i>
                            Current Medication
                        </label>
                        <textarea class="form-control" id="medication" name="medication" rows="2"
                            placeholder="Enter current medications..."><?= htmlspecialchars($consultation['medication'] ?? '') ?></textarea>
                    </div>

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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="visual_acuity_left" class="form-label">
                                <i class="bi bi-eye text-primary"></i>
                                <span class="badge bg-info me-2">OS</span> Visual Acuity - Left Eye
                            </label>
                            <input type="text" class="form-control border-info" id="visual_acuity_left" name="visual_acuity_left"
                                placeholder="e.g., 20/20" value="<?= htmlspecialchars($consultation['visual_acuity_left'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="visual_acuity_right" class="form-label">
                                <i class="bi bi-eye text-primary"></i>
                                <span class="badge bg-success me-2">OD</span> Visual Acuity - Right Eye
                            </label>
                            <input type="text" class="form-control border-success" id="visual_acuity_right" name="visual_acuity_right"
                                placeholder="e.g., 20/20" value="<?= htmlspecialchars($consultation['visual_acuity_right'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Refraction -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="refraction_left" class="form-label">
                                <i class="bi bi-eyeglasses text-info"></i>
                                <span class="badge bg-info me-2">OS</span> Refraction - Left Eye
                            </label>
                            <input type="text" class="form-control border-info" id="refraction_left" name="refraction_left"
                                placeholder="e.g., -2.00 -0.50 x 90" value="<?= htmlspecialchars($consultation['refraction_left'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="refraction_right" class="form-label">
                                <i class="bi bi-eyeglasses text-info"></i>
                                <span class="badge bg-success me-2">OD</span> Refraction - Right Eye
                            </label>
                            <input type="text" class="form-control border-success" id="refraction_right" name="refraction_right"
                                placeholder="e.g., -2.00 -0.50 x 90" value="<?= htmlspecialchars($consultation['refraction_right'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- IOP (Intraocular Pressure) -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="IOP_left" class="form-label">
                                <i class="bi bi-speedometer text-warning"></i>
                                <span class="badge bg-info me-2">OS</span> IOP - Left Eye (mmHg)
                            </label>
                            <input type="number" step="0.1" class="form-control border-info" id="IOP_left" name="IOP_left"
                                placeholder="e.g., 15.0" value="<?= htmlspecialchars($consultation['IOP_left'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="IOP_right" class="form-label">
                                <i class="bi bi-speedometer text-warning"></i>
                                <span class="badge bg-success me-2">OD</span> IOP - Right Eye (mmHg)
                            </label>
                            <input type="number" step="0.1" class="form-control border-success" id="IOP_right" name="IOP_right"
                                placeholder="e.g., 15.0" value="<?= htmlspecialchars($consultation['IOP_right'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Slit Lamp Examination -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="slit_lamp_left" class="form-label">
                                <i class="bi bi-search text-success"></i>
                                <span class="badge bg-info me-2">OS</span> Slit Lamp Examination - Left Eye
                            </label>
                            <textarea class="form-control border-info" id="slit_lamp_left" name="slit_lamp_left" rows="3"
                                placeholder="Enter left eye slit lamp findings..."><?= htmlspecialchars($consultation['slit_lamp_left'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="slit_lamp_right" class="form-label">
                                <i class="bi bi-search text-success"></i>
                                <span class="badge bg-success me-2">OD</span> Slit Lamp Examination - Right Eye
                            </label>
                            <textarea class="form-control border-success" id="slit_lamp_right" name="slit_lamp_right" rows="3"
                                placeholder="Enter right eye slit lamp findings..."><?= htmlspecialchars($consultation['slit_lamp_right'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Fundus Examination -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fundus_left" class="form-label">
                                <i class="bi bi-circle text-danger"></i>
                                <span class="badge bg-info me-2">OS</span> Fundus Examination - Left Eye
                            </label>
                            <textarea class="form-control border-info" id="fundus_left" name="fundus_left" rows="3"
                                placeholder="Enter left eye fundus findings..."><?= htmlspecialchars($consultation['fundus_left'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="fundus_right" class="form-label">
                                <i class="bi bi-circle text-danger"></i>
                                <span class="badge bg-success me-2">OD</span> Fundus Examination - Right Eye
                            </label>
                            <textarea class="form-control border-success" id="fundus_right" name="fundus_right" rows="3"
                                placeholder="Enter right eye fundus findings..."><?= htmlspecialchars($consultation['fundus_right'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- External Appearance -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="external_appearance_left" class="form-label">
                                <i class="bi bi-eye-fill text-warning"></i>
                                <span class="badge bg-info me-2">OS</span> External Appearance - Left Eye
                            </label>
                            <textarea class="form-control border-info" id="external_appearance_left" name="external_appearance_left" rows="3"
                                placeholder="Enter left eye external appearance..."><?= htmlspecialchars($consultation['external_appearance_left'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="external_appearance_right" class="form-label">
                                <i class="bi bi-eye-fill text-warning"></i>
                                <span class="badge bg-success me-2">OD</span> External Appearance - Right Eye
                            </label>
                            <textarea class="form-control border-success" id="external_appearance_right" name="external_appearance_right" rows="3"
                                placeholder="Enter right eye external appearance..."><?= htmlspecialchars($consultation['external_appearance_right'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Eyelid -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="eyelid_left" class="form-label">
                                <i class="bi bi-eye-slash text-secondary"></i>
                                <span class="badge bg-info me-2">OS</span> Eyelid - Left Eye
                            </label>
                            <textarea class="form-control border-info" id="eyelid_left" name="eyelid_left" rows="3"
                                placeholder="Enter left eye eyelid findings..."><?= htmlspecialchars($consultation['eyelid_left'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="eyelid_right" class="form-label">
                                <i class="bi bi-eye-slash text-secondary"></i>
                                <span class="badge bg-success me-2">OD</span> Eyelid - Right Eye
                            </label>
                            <textarea class="form-control border-success" id="eyelid_right" name="eyelid_right" rows="3"
                                placeholder="Enter right eye eyelid findings..."><?= htmlspecialchars($consultation['eyelid_right'] ?? '') ?></textarea>
                        </div>
                    </div>

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

                    <!-- Treatment Plan -->
                    <div class="mb-3">
                        <label for="plan" class="form-label">
                            <i class="bi bi-clipboard2-pulse text-primary"></i>
                            Treatment Plan
                        </label>
                        <textarea class="form-control" id="plan" name="plan" rows="4"
                            placeholder="Enter treatment plan..."><?= htmlspecialchars($consultation['plan'] ?? '') ?></textarea>
                    </div>

                    <!-- Follow-up Days -->
                    <div class="mb-3">
                        <label for="followup_days" class="form-label">
                            <i class="bi bi-calendar-check text-warning"></i>
                            Follow-up in Days
                        </label>
                        <input type="number" class="form-control" id="followup_days" name="followup_days"
                            placeholder="e.g., 7, 14, 30" value="<?= htmlspecialchars($consultation['followup_days'] ?? '') ?>">
                        <div class="form-text">Number of days until next follow-up appointment</div>
                    </div>



                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="/doctor/appointments/<?= $appointment['id'] ?? '' ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = ['chief_complaint', 'diagnosis'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in required fields: Chief Complaint and Diagnosis');
        }
    });


</script>

<style>
/* CSS Variables for Dark Mode */
:root {
    --bg: #f8fafc;
    --text: #0f172a;
    --card: #ffffff;
    --muted: #475569;
    --accent: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
    --border: #e2e8f0;
    --shadow: rgba(0, 0, 0, 0.1);
}

.dark {
    --bg: #0b1220;
    --text: #f8fafc;
    --card: #1e293b;
    --muted: #94a3b8;
    --accent: #38bdf8;
    --success: #4ade80;
    --danger: #fb7185;
    --border: #334155;
    --shadow: rgba(0, 0, 0, 0.3);
}

/* Dark Mode Styles */
.dark .card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .card-header {
    background-color: var(--bg) !important;
    border-bottom-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .card-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark .text-muted {
    color: var(--muted) !important;
}

.dark h2, .dark h3, .dark h4, .dark h5, .dark h6 {
    color: var(--text) !important;
}

.dark p {
    color: var(--text) !important;
}

.dark small {
    color: var(--muted) !important;
}

/* Dark Mode Form Styles */
.dark .form-control {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .form-control:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25) !important;
}

.dark .form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
}

.dark .form-label {
    color: var(--text) !important;
}

/* Dark Mode Alert Styles */
.dark .alert {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .alert-success {
    background-color: rgba(74, 222, 128, 0.1) !important;
    border-color: var(--success) !important;
    color: var(--text) !important;
}

.dark .alert-danger {
    background-color: rgba(251, 113, 133, 0.1) !important;
    border-color: var(--danger) !important;
    color: var(--text) !important;
}

.dark .alert-info {
    background-color: rgba(56, 189, 248, 0.1) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
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

/* Icon Circle Dark Mode */
.dark .icon-circle {
    background-color: var(--accent) !important;
    color: white !important;
}

/* Button Dark Mode */
.dark .btn-secondary {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .btn-secondary:hover {
    background-color: var(--bg) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .row.mb-3 {
        margin-bottom: 1rem !important;
    }
}
</style>

