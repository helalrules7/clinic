<?php
/**
 * Consultation form field groups — single source of truth.
 *
 * Used by:
 *   - doctor/edit_consultation.php  (standalone page, inside its <form> + POST)
 *   - doctor/appointment.php        (inline editor — same <form id> wrapper, AJAX save)
 *
 * Expects (all optional): $consultation = the note row to prefill (or [] for a new note).
 * The field `id`s (chief_complaint, diagnosis_code, caiIcd10Btn, …) are what
 * consultation-ai.js binds to — keep them stable. Only ONE instance of this form
 * is ever in the DOM at a time, so the fixed ids stay unique.
 */
$consultation = $consultation ?? [];
?>
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
        <label for="visual_acuity_right" class="form-label">
            <i class="bi bi-eye text-primary"></i>
            <span class="badge bg-success me-2">OD</span> Visual Acuity - Right Eye
        </label>
        <input type="text" class="form-control border-success" id="visual_acuity_right" name="visual_acuity_right"
            placeholder="e.g., 20/20, 6/6, +2" value="<?= htmlspecialchars($consultation['visual_acuity_right'] ?? '') ?>">
        <div class="form-text">Enter visual acuity (e.g., 20/20, 6/6, +2)</div>
    </div>
    <div class="col-md-6">
        <label for="visual_acuity_left" class="form-label">
            <i class="bi bi-eye text-primary"></i>
            <span class="badge bg-info me-2">OS</span> Visual Acuity - Left Eye
        </label>
        <input type="text" class="form-control border-info" id="visual_acuity_left" name="visual_acuity_left"
            placeholder="e.g., 20/20, 6/6, +2" value="<?= htmlspecialchars($consultation['visual_acuity_left'] ?? '') ?>">
        <div class="form-text">Enter visual acuity (e.g., 20/20, 6/6, +2)</div>
    </div>
</div>

<!-- Refraction -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="refraction_right" class="form-label">
            <i class="bi bi-eyeglasses text-info"></i>
            <span class="badge bg-success me-2">OD</span> Refraction - Right Eye
        </label>
        <input type="text" class="form-control border-success" id="refraction_right" name="refraction_right"
            placeholder="e.g., -2.00 -0.50 x 90, +1.50" value="<?= htmlspecialchars($consultation['refraction_right'] ?? '') ?>">
        <div class="form-text">Enter refraction values (e.g., -2.00 -0.50 x 90, +1.50)</div>
    </div>
    <div class="col-md-6">
        <label for="refraction_left" class="form-label">
            <i class="bi bi-eyeglasses text-info"></i>
            <span class="badge bg-info me-2">OS</span> Refraction - Left Eye
        </label>
        <input type="text" class="form-control border-info" id="refraction_left" name="refraction_left"
            placeholder="e.g., -2.00 -0.50 x 90, +1.50" value="<?= htmlspecialchars($consultation['refraction_left'] ?? '') ?>">
        <div class="form-text">Enter refraction values (e.g., -2.00 -0.50 x 90, +1.50)</div>
    </div>
</div>

<!-- IOP (Intraocular Pressure) -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="IOP_right" class="form-label">
            <i class="bi bi-speedometer text-warning"></i>
            <span class="badge bg-success me-2">OD</span> IOP - Right Eye (mmHg)
        </label>
        <input type="text" class="form-control border-success" id="IOP_right" name="IOP_right"
            placeholder="e.g., 15.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*"
            value="<?= htmlspecialchars($consultation['IOP_right'] ?? '') ?>">
        <div class="form-text">Enter pressure value (e.g., 15.0, +2, -1)</div>
    </div>
    <div class="col-md-6">
        <label for="IOP_left" class="form-label">
            <i class="bi bi-speedometer text-warning"></i>
            <span class="badge bg-info me-2">OS</span> IOP - Left Eye (mmHg)
        </label>
        <input type="text" class="form-control border-info" id="IOP_left" name="IOP_left"
            placeholder="e.g., 15.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*"
            value="<?= htmlspecialchars($consultation['IOP_left'] ?? '') ?>">
        <div class="form-text">Enter pressure value (e.g., 15.0, +2, -1)</div>
    </div>
</div>

<!-- Slit Lamp Examination -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="slit_lamp_right" class="form-label">
            <i class="bi bi-search text-success"></i>
            <span class="badge bg-success me-2">OD</span> Slit Lamp Examination - Right Eye
        </label>
        <textarea class="form-control border-success" id="slit_lamp_right" name="slit_lamp_right" rows="3"
            placeholder="Enter right eye slit lamp findings..."><?= htmlspecialchars($consultation['slit_lamp_right'] ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="slit_lamp_left" class="form-label">
            <i class="bi bi-search text-success"></i>
            <span class="badge bg-info me-2">OS</span> Slit Lamp Examination - Left Eye
        </label>
        <textarea class="form-control border-info" id="slit_lamp_left" name="slit_lamp_left" rows="3"
            placeholder="Enter left eye slit lamp findings..."><?= htmlspecialchars($consultation['slit_lamp_left'] ?? '') ?></textarea>
    </div>
</div>

<!-- Fundus Examination -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="fundus_right" class="form-label">
            <i class="bi bi-circle text-danger"></i>
            <span class="badge bg-success me-2">OD</span> Fundus Examination - Right Eye
        </label>
        <textarea class="form-control border-success" id="fundus_right" name="fundus_right" rows="3"
            placeholder="Enter right eye fundus findings..."><?= htmlspecialchars($consultation['fundus_right'] ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="fundus_left" class="form-label">
            <i class="bi bi-circle text-danger"></i>
            <span class="badge bg-info me-2">OS</span> Fundus Examination - Left Eye
        </label>
        <textarea class="form-control border-info" id="fundus_left" name="fundus_left" rows="3"
            placeholder="Enter left eye fundus findings..."><?= htmlspecialchars($consultation['fundus_left'] ?? '') ?></textarea>
    </div>
</div>

<!-- External Appearance -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="external_appearance_right" class="form-label">
            <i class="bi bi-eye-fill text-warning"></i>
            <span class="badge bg-success me-2">OD</span> External Appearance - Right Eye
        </label>
        <textarea class="form-control border-success" id="external_appearance_right" name="external_appearance_right" rows="3"
            placeholder="Enter right eye external appearance..."><?= htmlspecialchars($consultation['external_appearance_right'] ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="external_appearance_left" class="form-label">
            <i class="bi bi-eye-fill text-warning"></i>
            <span class="badge bg-info me-2">OS</span> External Appearance - Left Eye
        </label>
        <textarea class="form-control border-info" id="external_appearance_left" name="external_appearance_left" rows="3"
            placeholder="Enter left eye external appearance..."><?= htmlspecialchars($consultation['external_appearance_left'] ?? '') ?></textarea>
    </div>
</div>

<!-- Eyelid -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="eyelid_right" class="form-label">
            <i class="bi bi-eye-slash text-secondary"></i>
            <span class="badge bg-success me-2">OD</span> Eyelid - Right Eye
        </label>
        <textarea class="form-control border-success" id="eyelid_right" name="eyelid_right" rows="3"
            placeholder="Enter right eye eyelid findings..."><?= htmlspecialchars($consultation['eyelid_right'] ?? '') ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="eyelid_left" class="form-label">
            <i class="bi bi-eye-slash text-secondary"></i>
            <span class="badge bg-info me-2">OS</span> Eyelid - Left Eye
        </label>
        <textarea class="form-control border-info" id="eyelid_left" name="eyelid_left" rows="3"
            placeholder="Enter left eye eyelid findings..."><?= htmlspecialchars($consultation['eyelid_left'] ?? '') ?></textarea>
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
    <div class="d-flex justify-content-between align-items-center">
        <label for="diagnosis_code" class="form-label mb-0">
            <i class="bi bi-upc text-secondary"></i>
            Diagnosis Code (ICD-10)
        </label>
        <button type="button" id="caiIcd10Btn"
            class="btn btn-sm btn-outline-primary">
            <i class="bi bi-stars"></i>
            Suggest ICD-10
        </button>
    </div>
    <div class="cai-anchor mt-1">
        <input type="text" class="form-control" id="diagnosis_code" name="diagnosis_code"
            placeholder="e.g., H25.9"
            value="<?= htmlspecialchars($consultation['diagnosis_code'] ?? '') ?>">
        <div class="cai-icd-popover" id="caiIcd10Popover"></div>
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
