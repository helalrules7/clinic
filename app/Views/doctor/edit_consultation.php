<!-- Breadcrumb -->
<link
    href="/app/Views/doctor/assets/css/edit_consultation.css?v=<?= file_exists(__DIR__ . '/assets/css/edit_consultation.css') ? filemtime(__DIR__ . '/assets/css/edit_consultation.css') : time() ?>"
    rel="stylesheet">
<link
    href="/app/Views/doctor/assets/css/consultation-ai.css?v=<?= file_exists(__DIR__ . '/assets/css/consultation-ai.css') ? filemtime(__DIR__ . '/assets/css/consultation-ai.css') : time() ?>"
    rel="stylesheet">

<div class="d-flex justify-content-between align-items-center mb-4">
    <nav class="app-breadcrumb" aria-label="Breadcrumb">
        <a href="/doctor/appointments/<?= (int)($appointment['id'] ?? 0) ?>" class="app-crumb-back" aria-label="Back to appointment">
            <i class="bi bi-arrow-left"></i>
        </a>
        <a href="/doctor/patients/<?= (int)($appointment['patient_id'] ?? 0) ?>" class="app-crumb-link patient-name-link" data-patient-id="<?= (int)($appointment['patient_id'] ?? 0) ?>"><?= htmlspecialchars($appointment['patient_name'] ?? '') ?></a>
        <i class="bi bi-chevron-right app-crumb-sep" aria-hidden="true"></i>
        <a href="/doctor/appointments/<?= (int)($appointment['id'] ?? 0) ?>" class="app-crumb-link">Appointment #<?= (int)($appointment['id'] ?? 0) ?></a>
        <i class="bi bi-chevron-right app-crumb-sep" aria-hidden="true"></i>
        <span class="app-crumb-current">Edit Consultation</span>
    </nav>
    <a href="/doctor/appointments/<?= (int)($appointment['id'] ?? 0) ?>" class="btn btn-secondary">
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

<!-- AI Assistant (Phase 1). Read-only situational awareness + lazy,
     on-click only. Nothing here writes the chart or submits the form. -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card cai-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-stars text-warning"></i>
                    AI Assistant
                    <span class="cai-badge"><i class="bi bi-shield-check"></i>
                        AI — review before saving</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="cai-section">
                    <div class="cai-section-label">Prior-visit clinical summary</div>
                    <p class="text-muted small mb-2">
                        Grounded recap of this patient's previous records
                        (IOP / VA / refraction trends flagged). Read-only —
                        not added to the chart.
                    </p>
                    <button type="button" id="caiSummarizeBtn"
                        class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-clipboard2-pulse"></i>
                        Summarize prior visits
                    </button>
                    <div class="cai-summary-output" id="caiSummaryOutput"></div>
                </div>

                <div class="cai-section">
                    <div class="cai-section-label">Ask the assistant</div>
                    <div class="cai-chips">
                        <button type="button" class="cai-chip"
                            data-cai-prompt="Summarize this patient's prior ophthalmic visits in 5 bullets"
                            data-cai-context="patient_history">
                            <i class="bi bi-clock-history"></i>
                            Summarize prior ophthalmic visits
                        </button>
                        <button type="button" class="cai-chip"
                            data-cai-prompt="What might I be missing in the current consultation draft?"
                            data-cai-context="consultation_summary">
                            <i class="bi bi-question-circle"></i>
                            What might I be missing?
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Get the latest consultation note for editing, or prepare for new one
$consultation = !empty($consultationNotes) ? $consultationNotes[0] : [];
$isEditing = !empty($consultation);
?>

<!-- Sticky Save/Cancel Bar -->
<div class="action-bar-container" id="actionBarContainer">
    <div class="action-bar" id="actionBar">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center py-2">
                <a href="/doctor/appointments/<?= $appointment['id'] ?? '' ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i>
                    Cancel
                </a>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('consultationForm').requestSubmit();">
                    <i class="bi bi-check-circle"></i>
                    Save Changes
                </button>
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
                    
                    <?php include __DIR__ . '/partials/consultation_form.php'; ?>



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

<!-- Common Complaints Modal -->
<div class="modal fade" id="commonComplaintsModal" tabindex="-1" aria-labelledby="commonComplaintsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commonComplaintsModalLabel">
                    <i class="bi bi-list-check me-2"></i>Most Common Cases
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="commonComplaintsLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading common cases...</p>
                </div>
                <div id="commonComplaintsContent" style="display: none;">
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Last updated: <span id="lastUpdated"></span>
                        </small>
                    </div>
                    <div id="commonComplaintsList" class="list-group"></div>
                </div>
                <div id="commonComplaintsError" style="display: none;" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="errorMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script
    src="/app/Views/doctor/assets/js/edit_consultation.js?v=<?= file_exists(__DIR__ . '/assets/js/edit_consultation.js') ? filemtime(__DIR__ . '/assets/js/edit_consultation.js') : time() ?>"></script>

<!-- Smart Consultation — AI assists (Phase 1). Config is inline so
     consultation-ai.js stays static/cacheable. ai-chat-widget.js MUST
     load before consultation-ai.js (it calls initAIChatWidget). -->
<script>
    window.CONSULTATION_AI = {
        appointmentId: <?= isset($appointment['id']) ? (int) $appointment['id'] : 'null' ?>,
        patientId: <?= isset($appointment['patient_id']) ? (int) $appointment['patient_id'] : 'null' ?>,
        csrfToken: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>',
        // Doctor Auto Complete preferences (Settings → Auto Complete). Default ON.
        autocomplete: {
            consultation: <?= (!isset($autocompletePrefs) || !empty($autocompletePrefs['consultation'])) ? 'true' : 'false' ?>,
            icd10: <?= (!isset($autocompletePrefs) || !empty($autocompletePrefs['icd10'])) ? 'true' : 'false' ?>
        }
    };
</script>
<link rel="stylesheet"
    href="/app/Views/doctor/assets/css/ai-chat-widget.css?v=<?= file_exists(__DIR__ . '/assets/css/ai-chat-widget.css') ? filemtime(__DIR__ . '/assets/css/ai-chat-widget.css') : time() ?>">
<script
    src="/app/Views/doctor/assets/js/ai-chat-widget.js?v=<?= file_exists(__DIR__ . '/assets/js/ai-chat-widget.js') ? filemtime(__DIR__ . '/assets/js/ai-chat-widget.js') : time() ?>"></script>
<script
    src="/app/Views/doctor/assets/js/consultation-ai.js?v=<?= file_exists(__DIR__ . '/assets/js/consultation-ai.js') ? filemtime(__DIR__ . '/assets/js/consultation-ai.js') : time() ?>"></script>
<style>
.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>