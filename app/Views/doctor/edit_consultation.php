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
                            <span class="badge bg-<?= $appointment['status'] === 'completed' ? 'success' : ($appointment['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                <?= htmlspecialchars($appointment['status'] === 'completed' ? 'Completed' : ($appointment['status'] === 'cancelled' ? 'Cancelled' : 'Confirmed')) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

                <form method="POST" action="/doctor/appointments/<?= $appointment['id'] ?? '' ?>/consultation" id="consultationForm">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <!-- Chief Complaint -->
                    <div class="mb-3">
                        <label for="chief_complaint" class="form-label">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            Chief Complaint
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



                    <!-- Visual Acuity -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="visual_acuity_right" class="form-label">
                                <i class="bi bi-eye text-primary"></i>
                                Visual Acuity - Right Eye
                            </label>
                            <input type="text" class="form-control" id="visual_acuity_right" name="visual_acuity_right"
                                placeholder="e.g., 20/20" value="<?= htmlspecialchars($consultation['visual_acuity_right'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="visual_acuity_left" class="form-label">
                                <i class="bi bi-eye text-primary"></i>
                                Visual Acuity - Left Eye
                            </label>
                            <input type="text" class="form-control" id="visual_acuity_left" name="visual_acuity_left"
                                placeholder="e.g., 20/20" value="<?= htmlspecialchars($consultation['visual_acuity_left'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Refraction -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="refraction_right" class="form-label">
                                <i class="bi bi-eyeglasses text-info"></i>
                                Refraction - Right Eye
                            </label>
                            <input type="text" class="form-control" id="refraction_right" name="refraction_right"
                                placeholder="e.g., -2.00 -0.50 x 90" value="<?= htmlspecialchars($consultation['refraction_right'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="refraction_left" class="form-label">
                                <i class="bi bi-eyeglasses text-info"></i>
                                Refraction - Left Eye
                            </label>
                            <input type="text" class="form-control" id="refraction_left" name="refraction_left"
                                placeholder="e.g., -2.00 -0.50 x 90" value="<?= htmlspecialchars($consultation['refraction_left'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- IOP (Intraocular Pressure) -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="IOP_right" class="form-label">
                                <i class="bi bi-speedometer text-warning"></i>
                                IOP - Right Eye (mmHg)
                            </label>
                            <input type="number" step="0.1" class="form-control" id="IOP_right" name="IOP_right"
                                placeholder="e.g., 15.0" value="<?= htmlspecialchars($consultation['IOP_right'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="IOP_left" class="form-label">
                                <i class="bi bi-speedometer text-warning"></i>
                                IOP - Left Eye (mmHg)
                            </label>
                            <input type="number" step="0.1" class="form-control" id="IOP_left" name="IOP_left"
                                placeholder="e.g., 15.0" value="<?= htmlspecialchars($consultation['IOP_left'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Slit Lamp Examination -->
                    <div class="mb-3">
                        <label for="slit_lamp" class="form-label">
                            <i class="bi bi-search text-success"></i>
                            Slit Lamp Examination
                        </label>
                        <textarea class="form-control" id="slit_lamp" name="slit_lamp" rows="3"
                            placeholder="Enter slit lamp examination findings..."><?= htmlspecialchars($consultation['slit_lamp'] ?? '') ?></textarea>
                    </div>

                    <!-- Fundus Examination -->
                    <div class="mb-3">
                        <label for="fundus" class="form-label">
                            <i class="bi bi-circle text-danger"></i>
                            Fundus Examination
                        </label>
                        <textarea class="form-control" id="fundus" name="fundus" rows="3"
                            placeholder="Enter fundus examination findings..."><?= htmlspecialchars($consultation['fundus'] ?? '') ?></textarea>
                    </div>

                    <!-- Diagnosis -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="diagnosis" class="form-label">
                                <i class="bi bi-clipboard2-check text-danger"></i>
                                Diagnosis
                            </label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"
                                placeholder="Enter diagnosis..."><?= htmlspecialchars($consultation['diagnosis'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="diagnosis_code" class="form-label">
                                <i class="bi bi-upc text-secondary"></i>
                                Diagnosis Code (ICD-10)
                            </label>
                            <input type="text" class="form-control" id="diagnosis_code" name="diagnosis_code"
                                placeholder="e.g., H25.9" value="<?= htmlspecialchars($consultation['diagnosis_code'] ?? '') ?>">
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

    // Auto-detect correct form action based on available routes
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('consultationForm');
        const appointmentId = '<?= $appointment['id'] ?? '' ?>';
        
        // Test if /edit endpoint exists, otherwise use /consultation
        fetch(`/doctor/appointments/${appointmentId}/edit`, {
            method: 'HEAD'
        }).then(response => {
            if (response.ok || response.status === 405) { // 405 means method not allowed but route exists
                form.action = `/doctor/appointments/${appointmentId}/edit`;
            }
            // If 404, keep the default /consultation action
        }).catch(() => {
            // Keep default /consultation action on error
        });
    });
</script>