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

                <form method="POST" action="/doctor/appointments/<?= $appointment['id'] ?? '' ?>/edit">
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
                        <label for="history_of_present_illness" class="form-label">
                            <i class="bi bi-clock-history text-info"></i>
                            History of Present Illness
                        </label>
                        <textarea class="form-control" id="history_of_present_illness" name="history_of_present_illness" rows="3"
                            placeholder="Enter the history of present illness..."><?= htmlspecialchars($consultation['history_of_present_illness'] ?? '') ?></textarea>
                    </div>

                    <!-- Past Medical History -->
                    <div class="mb-3">
                        <label for="past_medical_history" class="form-label">
                            <i class="bi bi-file-medical text-secondary"></i>
                            Past Medical History
                        </label>
                        <textarea class="form-control" id="past_medical_history" name="past_medical_history" rows="3"
                            placeholder="Enter past medical history..."><?= htmlspecialchars($consultation['past_medical_history'] ?? '') ?></textarea>
                    </div>

                    <!-- Family History -->
                    <div class="mb-3">
                        <label for="family_history" class="form-label">
                            <i class="bi bi-people text-primary"></i>
                            Family History
                        </label>
                        <textarea class="form-control" id="family_history" name="family_history" rows="2"
                            placeholder="Enter family history..."><?= htmlspecialchars($consultation['family_history'] ?? '') ?></textarea>
                    </div>

                    <!-- Examination Findings -->
                    <div class="mb-3">
                        <label for="examination_findings" class="form-label">
                            <i class="bi bi-search text-success"></i>
                            Examination Findings
                        </label>
                        <textarea class="form-control" id="examination_findings" name="examination_findings" rows="4"
                            placeholder="Enter examination findings..."><?= htmlspecialchars($consultation['examination_findings'] ?? '') ?></textarea>
                    </div>

                    <!-- Diagnosis -->
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">
                            <i class="bi bi-clipboard2-check text-danger"></i>
                            Diagnosis
                        </label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"
                            placeholder="Enter diagnosis..."><?= htmlspecialchars($consultation['diagnosis'] ?? '') ?></textarea>
                    </div>

                    <!-- Treatment Plan -->
                    <div class="mb-3">
                        <label for="treatment_plan" class="form-label">
                            <i class="bi bi-clipboard2-pulse text-primary"></i>
                            Treatment Plan
                        </label>
                        <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="4"
                            placeholder="Enter treatment plan..."><?= htmlspecialchars($consultation['treatment_plan'] ?? '') ?></textarea>
                    </div>

                    <!-- Follow-up Instructions -->
                    <div class="mb-3">
                        <label for="follow_up_instructions" class="form-label">
                            <i class="bi bi-calendar-check text-warning"></i>
                            Follow-up Instructions
                        </label>
                        <textarea class="form-control" id="follow_up_instructions" name="follow_up_instructions" rows="3"
                            placeholder="Enter follow-up instructions..."><?= htmlspecialchars($consultation['follow_up_instructions'] ?? '') ?></textarea>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="notes" class="form-label">
                            <i class="bi bi-sticky text-info"></i>
                            Additional Notes
                        </label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            placeholder="Enter any additional notes..."><?= htmlspecialchars($consultation['notes'] ?? '') ?></textarea>
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
        const requiredFields = ['chief_complaint', 'examination_findings', 'diagnosis'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });
</script>