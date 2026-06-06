<link href="/app/Views/doctor/assets/css/patient.css?v=<?= file_exists(__DIR__ . '/assets/css/patient.css') ? filemtime(__DIR__ . '/assets/css/patient.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/medical-history-popover.css?v=<?= file_exists(__DIR__ . '/assets/css/medical-history-popover.css') ? filemtime(__DIR__ . '/assets/css/medical-history-popover.css') : time() ?>" rel="stylesheet">

<!-- Breadcrumb (unified .app-breadcrumb component, see design-system.css) -->
<nav class="app-breadcrumb" aria-label="Breadcrumb">
    <a href="/doctor/patients" class="app-crumb-back" aria-label="Back to patients">
        <i class="bi bi-arrow-left"></i>
    </a>
    <a href="/doctor/patients" class="app-crumb-link">Patients</a>
    <i class="bi bi-chevron-right app-crumb-sep" aria-hidden="true"></i>
    <span class="app-crumb-current"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></span>
</nav>

<!-- Patient Profile Header -->
<div class="patient-profile-header-wrapper mb-4" id="patientProfileHeader" data-patient-id="<?= $patient['id'] ?>">
    <div class="patient-profile-header-background"></div>
    <div class="patient-profile-header-overlay"></div>
    <div class="patient-profile-header-content">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                        <!-- Avatar -->
                        <div class="patient-profile-avatar-wrapper">
                            <div class="avatar-circle-large <?= $patient['gender'] === 'Female' ? 'avatar-large-female' : 'avatar-large-male' ?>">
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
                        </div>
                        
                        <!-- Patient Info -->
                        <div class="flex-grow-1">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                <div>
                                    <h2 class="patient-profile-name mb-1"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
                                    <p class="patient-profile-id mb-1">Patient ID: #<?= $patient['id'] ?></p>
                                    <?php if ($patient['dob']): ?>
                                        <small class="patient-profile-dob d-block">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?= date('M j, Y', strtotime($patient['dob'])) ?> 
                                            (<?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> years old)
                                        </small>
                                    <?php endif; ?>
                                    
                                    <!-- Current Doctor Badge -->
                                    <?php if (isset($currentDoctor) && $currentDoctor): ?>
                                    <div class="mt-2">
                                        <span class="badge doctor-badge fs-6 px-3 py-2 d-inline-flex align-items-center">
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
                                    
                                    <!-- Patient Tags -->
                                    <div class="mt-3" id="patientProfileTags">
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <span class="text-muted small">Tags:</span>
                                            <div id="patientTagsList" class="d-flex flex-wrap gap-2">
                                                <!-- Tags will be loaded here via JavaScript -->
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="showTagManagementModal(<?= $patient['id'] ?>)">
                                                <i class="bi bi-plus-lg me-1"></i>Add Tag
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Marker Button -->
                                <div class="patient-profile-actions">
                                    <button class="btn btn-outline-light btn-sm" id="setMarkerBtn" onclick="showColorMarkerModal(<?= $patient['id'] ?>, null)" title="Set Color Marker">
                                        <i class="bi bi-palette me-1"></i>Set Marker
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Information Cards — contact ~35% / actions ~65% on desktop (§3.50) -->
<div class="row mb-4 patient-info-actions-row">
    <!-- Contact Information -->
    <div class="col-12 col-lg-4 patient-contact-col">
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
                <?php if (!empty($patient['clinic_name_ar']) || !empty($patient['clinic_name_en'])): ?>
                <?php
                    $_clinicVisuals = [
                        'riyadh' => ['icon' => 'bi-buildings-fill', 'color' => '#0d6efd'],
                        'kfs'    => ['icon' => 'bi-hospital-fill',  'color' => '#10b981'],
                    ];
                    $_v = $_clinicVisuals[$patient['clinic_code'] ?? ''] ?? ['icon' => 'bi-building', 'color' => '#6c757d'];
                ?>
                <div class="row mt-2">
                    <div class="col-sm-4"><strong>Clinic:</strong></div>
                    <div class="col-sm-8">
                        <span class="clinic-tag" style="--clinic-color: <?= $_v['color'] ?>;" dir="rtl">
                            <i class="bi <?= $_v['icon'] ?>"></i>
                            <?= htmlspecialchars($patient['clinic_name_ar'] ?: $patient['clinic_name_en']) ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="col-12 col-lg-8 patient-actions-col">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="patient-actions-row">
                    <button class="btn btn-primary"
                            onclick="bookNewAppointment(<?= $patient['id'] ?>)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Schedule a new appointment for this patient">
                        <i class="bi bi-calendar-plus me-2"></i>Book Appointment
                    </button>
                    <button class="btn btn-outline-warning"
                            onclick="showMedicalHistoryPopover(<?= (int)$patient['id'] ?>, this)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Medical history and visit chronology">
                        <i class="bi bi-clipboard-heart me-2"></i>Medical History
                    </button>
                    <button class="btn btn-success"
                            onclick="printPatientSummary()"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Print patient summary report">
                        <i class="bi bi-printer me-2"></i>Print Summary
                    </button>
                    <button class="btn btn-info"
                            onclick="exportPatientData()"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Export complete medical record as PDF">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Export History PDF
                    </button>
                    <button class="btn btn-violet"
                            onclick="autoPlacePatientOnBoard(<?= (int)$patient['id'] ?>, this)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Drop this patient into the most-fitting board column based on tags + recent activity">
                        <i class="bi bi-magic me-2"></i>Auto-place to board
                    </button>
                    <button class="btn btn-secondary"
                            onclick="editPatient(<?= $patient['id'] ?>)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Edit patient information and details">
                        <i class="bi bi-pencil me-2"></i>Edit Patient
                    </button>
                    <button class="btn btn-primary"
                            id="patientIOPTrendBtn"
                            data-patient-id="<?= htmlspecialchars($patient['id']) ?>"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Analyze IOP trend for this patient">
                        <i class="bi bi-graph-up me-2"></i>IOP Trend
                    </button>
                    <button class="btn btn-warning"
                            onclick="openAlertModal(<?= $patient['id'] ?>, null)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="Create an alert for this patient">
                        <i class="bi bi-bell me-2"></i>Set Alert
                    </button>
                    <button class="btn btn-info"
                            onclick="showUnifiedClinicalDashboardPopover(<?= $patient['id'] ?>)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-title="View unified clinical dashboard for this patient">
                        <i class="bi bi-clipboard-pulse me-2"></i>Clinical Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patient Board — notes attached to this patient's board card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>Patient Board</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            Notes added here attach to this patient's board card and are visible from the board.
        </p>
        <div id="patientBoardNotes" class="visit-board-notes mb-2">
            <div class="text-center text-muted small py-2"><span class="spinner-border spinner-border-sm" role="status"></span></div>
        </div>
        <textarea id="patientBoardInput" class="form-control" rows="2" maxlength="3900"
                  placeholder="Write a note for the patient board…"></textarea>
        <button id="patientBoardSend" class="btn btn-primary btn-sm mt-2" type="button" disabled>
            <i class="bi bi-send me-1"></i>Add to board
        </button>
        <div class="text-danger small mt-2 d-none" id="patientBoardError"></div>
    </div>
</div>
<link rel="stylesheet"
      href="/app/Views/doctor/assets/css/comment-media.css?v=<?= file_exists(__DIR__ . '/assets/css/comment-media.css') ? filemtime(__DIR__ . '/assets/css/comment-media.css') : time() ?>">
<style>
    .visit-board-notes { max-height: 340px; overflow-y: auto; }
    .visit-note { display: flex; gap: .6rem; border: 1px solid var(--glass-border, #e2e8f0); border-radius: var(--r-md, 12px); padding: 10px 12px; margin-bottom: 8px; background: var(--card); }
    .visit-note-av { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex: 0 0 auto; }
    .visit-note-av--ini { display: inline-flex; align-items: center; justify-content: center; background: var(--ds-primary); color: #fff; font-weight: 800; font-size: .75rem; }
    .visit-note-main { flex: 1 1 auto; min-width: 0; }
    .visit-note-meta { font-size: .72rem; color: var(--muted, #64748b); margin-bottom: 3px; }
    .visit-note-author { font-weight: 700; color: var(--text); }
    .visit-note-body { font-size: .85rem; line-height: 1.55; white-space: pre-wrap; word-break: break-word; color: var(--text); }
    .visit-note { position: relative; }
    .visit-note-del {
        flex: 0 0 auto;
        align-self: flex-start;
        background: transparent;
        border: 0;
        color: var(--muted, #64748b);
        width: 28px; height: 28px;
        border-radius: 8px;
        cursor: pointer;
        opacity: .7;
        transition: opacity .15s ease, background .15s ease, color .15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
    }
    .visit-note-del:hover { opacity: 1; background: color-mix(in srgb, var(--danger, #ef4444) 12%, transparent); color: var(--danger, #ef4444); }
    .visit-note-del:focus-visible { opacity: 1; outline: none; box-shadow: 0 0 0 2px color-mix(in srgb, var(--danger, #ef4444) 40%, transparent); }
    .visit-note-del:disabled { opacity: .5; cursor: not-allowed; }
</style>
<script src="/app/Views/doctor/assets/js/comment-media.js?v=<?= file_exists(__DIR__ . '/assets/js/comment-media.js') ? filemtime(__DIR__ . '/assets/js/comment-media.js') : time() ?>"></script>
<script>
(function () {
    const PID  = <?= (int)($patient['id'] ?? 0) ?>;
    const CSRF = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>';
    const CM   = window.CommentMedia;
    const listEl  = document.getElementById('patientBoardNotes');
    const input   = document.getElementById('patientBoardInput');
    const sendBtn = document.getElementById('patientBoardSend');
    const errEl   = document.getElementById('patientBoardError');
    if (!PID || !listEl) return;

    const esc = (s) => CM ? CM.escapeHtml(s) : (s == null ? '' : String(s));
    const fmt = (ts) => { if (!ts) return ''; const d = new Date(String(ts).replace(' ', 'T')); return isNaN(d) ? ts : d.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }); };

    const composer = (CM && input) ? CM.attachComposer({
        textarea: input, getCsrf: () => CSRF,
        onError: (m) => { errEl.textContent = m; errEl.classList.remove('d-none'); },
        onChange: () => { sendBtn.disabled = !composer.hasContent() || composer.isUploading(); },
        onSubmit: () => sendBtn.click()
    }) : null;

    async function load() {
        try {
            const r = await fetch('/api/comments/board_card/' + PID, { credentials: 'same-origin' });
            const j = await r.json();
            const rows = (j.data || []).filter(x => !x.deleted_at);
            if (!rows.length) { listEl.innerHTML = '<p class="text-muted small mb-0">No board notes yet.</p>'; return; }
            listEl.innerHTML = rows.slice(-12).map(r => {
                const img = CM ? CM.avatarSrc(r.author_image) : null;
                const av = img
                    ? `<img class="visit-note-av" src="${esc(img)}" alt="">`
                    : `<span class="visit-note-av visit-note-av--ini">${esc(CM ? CM.initials(r.author_name) : '?')}</span>`;
                const body = CM ? CM.renderBody(r.body, r.mentions) : esc(r.body);
                const atts = CM ? CM.renderAttachments(r.attachments) : '';
                const del  = r.can_edit
                    ? `<button type="button" class="visit-note-del" data-cid="${r.id}" aria-label="Delete note" title="Delete note"><i class="bi bi-trash"></i></button>`
                    : '';
                return `<div class="visit-note">${av}<div class="visit-note-main"><div class="visit-note-meta"><span class="visit-note-author">${esc(r.author_name || 'User')}</span> · ${esc(fmt(r.created_at))}</div><div class="visit-note-body">${body}</div>${atts}</div>${del}</div>`;
            }).join('');
            listEl.scrollTop = listEl.scrollHeight;
        } catch (e) { listEl.innerHTML = '<p class="text-danger small mb-0">Failed to load notes.</p>'; }
    }

    // Delegated delete handler — confirms via the themed modal-kit dialog,
    // then DELETE /api/comments/{id} and reload. Gated server-side to
    // author/admin; the trash button only renders when r.can_edit.
    listEl.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('.visit-note-del');
        if (!btn) return;
        const cid = btn.getAttribute('data-cid');
        if (!cid) return;
        const ok = typeof window.mkConfirmModal === 'function'
            ? await window.mkConfirmModal({
                title: 'Delete note?',
                message: 'This board note will be removed. This action cannot be undone.',
                confirmText: 'Delete',
                confirmClass: 'btn-danger',
                icon: 'bi-trash',
            })
            : window.confirm('Delete this note?');
        if (!ok) return;
        btn.disabled = true;
        try {
            const r = await fetch('/api/comments/' + cid, {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
            });
            const j = await r.json().catch(() => ({}));
            if (!r.ok || !j.ok) throw new Error((j && j.error) || ('HTTP ' + r.status));
            await load();
        } catch (e) {
            btn.disabled = false;
            errEl.textContent = 'Delete failed: ' + (e.message || 'unknown');
            errEl.classList.remove('d-none');
        }
    });

    if (!composer) input.addEventListener('input', () => { sendBtn.disabled = input.value.trim() === ''; });
    sendBtn.addEventListener('click', async () => {
        const body = composer ? composer.getBody() : input.value.trim();
        const ids  = composer ? composer.getAttachmentIds() : [];
        if (!body && !ids.length) return;
        if (composer && composer.isUploading()) { errEl.textContent = 'Wait for the upload to finish'; errEl.classList.remove('d-none'); return; }
        errEl.classList.add('d-none'); sendBtn.disabled = true;
        try {
            const r = await fetch('/api/comments/board_card/' + PID, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
                body: JSON.stringify({ body: body, attachment_ids: ids })
            });
            const j = await r.json();
            if (!j.ok) throw new Error(j.error || 'Failed to add note');
            if (composer) composer.reset(); else input.value = '';
            await load();
        } catch (e) { errEl.textContent = e.message; errEl.classList.remove('d-none'); sendBtn.disabled = false; }
    });
    load();
})();
</script>

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
                /* Effective status: a Booked appointment whose date is in the
                   past is shown as "Missed" everywhere — the DB enum doesn't
                   carry a 'Missed' value so we compute it here. Matches the
                   logic on the single-appointment detail page. */
                $rawStatus = $appointment['status'] ?? '';
                $apptDate  = $appointment['date']   ?? '';
                $isPastBooked = ($rawStatus === 'Booked' && $apptDate !== '' && $apptDate < date('Y-m-d'));
                $effectiveStatus = $isPastBooked ? 'Missed' : $rawStatus;
                $isMissedAppt = ($isPastBooked || $rawStatus === 'Cancelled' || $rawStatus === 'NoShow');
                $statusColor = $rawStatus === 'Completed' ? 'success'
                             : ($isMissedAppt ? 'danger'
                             : ($rawStatus === 'InProgress' ? 'warning' : 'primary'));
                $headerStateClass = '';
                if ($rawStatus === 'Completed')          $headerStateClass = 'completed';
                elseif ($isMissedAppt)                   $headerStateClass = 'missed';
                elseif ($rawStatus === 'Closed')         $headerStateClass = 'closed';
                elseif ($rawStatus === 'Rescheduled')    $headerStateClass = 'rescheduled';
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
                    <div class="timeline-header appointment-header <?= $headerStateClass ?> <?= $isLatest ? 'expanded' : 'collapsed' ?>"
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
                                    <?= htmlspecialchars($effectiveStatus) ?>
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
                                
                        <!-- Medications Prescriptions (style mirrors Prescriptions History) -->
                        <?php if (!empty($appointment['medications'])): ?>
                            <div class="mb-3">
                                <h6 class="text-success mb-2">
                                    <i class="bi bi-capsule me-2"></i>Medications Prescribed
                                    <span class="badge bg-success ms-2"><?= count($appointment['medications']) ?></span>
                                </h6>
                                <div class="medications-list">
                                    <?php foreach ($appointment['medications'] as $med): ?>
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
                                    <nav id="appointmentTimelinePagination" class="appointments-pagination mt-3 d-flex justify-content-center" aria-label="Appointment history pagination"></nav>
                                    </div>
                                    </div>
                                <?php endif; ?>

<script>
/* Client-side pagination for the Appointment History timeline — keeps the page
   short when a patient has many visits. Items are server-rendered; we page
   through them in the DOM. */
(function () {
    const PER_PAGE = 5;
    const timeline = document.querySelector('.appointment-timeline');
    const navEl = document.getElementById('appointmentTimelinePagination');
    if (!timeline || !navEl) return;
    const items = Array.prototype.slice.call(timeline.children)
        .filter(el => el.classList && el.classList.contains('appointment-timeline-item'));
    const total = items.length;
    if (total <= PER_PAGE) return;
    const totalPages = Math.ceil(total / PER_PAGE);
    let current = 1;

    function pageWindow() {
        const set = new Set([1, totalPages, current, current - 1, current + 1]);
        if (current <= 3) { set.add(2); set.add(3); }
        if (current >= totalPages - 2) { set.add(totalPages - 1); set.add(totalPages - 2); }
        const pages = [...set].filter(p => p >= 1 && p <= totalPages).sort((a, b) => a - b);
        const out = []; let prev = 0;
        pages.forEach(p => { if (p - prev > 1) out.push('...'); out.push(p); prev = p; });
        return out;
    }

    function render() {
        items.forEach((it, i) => {
            if (Math.floor(i / PER_PAGE) + 1 === current) it.style.removeProperty('display');
            else it.style.setProperty('display', 'none', 'important');
        });
        const rtl = document.documentElement.dir === 'rtl';
        const prevIcon = rtl ? 'bi-chevron-right' : 'bi-chevron-left';
        const nextIcon = rtl ? 'bi-chevron-left' : 'bi-chevron-right';
        let h = '<ul class="pagination mb-0">';
        h += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-pg="${current - 1}" aria-label="Previous"><i class="bi ${prevIcon}"></i></a></li>`;
        pageWindow().forEach(p => {
            if (p === '...') { h += '<li class="page-item disabled"><span class="page-link">…</span></li>'; }
            else { h += `<li class="page-item ${p === current ? 'active' : ''}"><a class="page-link" href="#" data-pg="${p}">${p}</a></li>`; }
        });
        h += `<li class="page-item ${current === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-pg="${current + 1}" aria-label="Next"><i class="bi ${nextIcon}"></i></a></li>`;
        h += '</ul>';
        navEl.innerHTML = h;
        navEl.querySelectorAll('a.page-link[data-pg]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const pg = parseInt(a.getAttribute('data-pg'), 10);
                if (isNaN(pg) || pg < 1 || pg > totalPages || pg === current) return;
                current = pg; render();
                (timeline.closest('.card') || timeline).scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }
    render();
})();
</script>

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

<!-- Patient Files & Attachments -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-paperclip me-2"></i>
                Patient Files & Documents
            </h5>
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group btn-group-sm" role="group" aria-label="Bulk actions">
                    <button class="btn btn-outline-secondary" type="button" id="patientFilesSelectAllBtn"
                            onclick="patientFilesToggleSelectAll()"
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-title="Select all on this page">
                        <i class="bi bi-check2-square"></i>
                    </button>
                    <button class="btn btn-outline-danger" type="button" id="patientFilesDeleteSelectedBtn"
                            onclick="patientFilesConfirmDeleteSelected()" disabled
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-title="Delete selected files">
                        <i class="bi bi-trash"></i>
                        <span class="badge bg-danger ms-1 d-none" id="patientFilesSelectedBadge">0</span>
                    </button>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="Add file">
                    <button class="btn btn-primary"
                            onclick="showPatientUploadModal(<?= $patient['id'] ?>)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-title="Upload files and documents for this patient">
                        <i class="bi bi-cloud-upload me-1"></i>Upload
                    </button>
                    <button class="btn btn-draw-consultation" type="button"
                            onclick="DrawConsultation && DrawConsultation.openForPatient(<?= $patient['id'] ?>)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-title="Open the drawing canvas for this patient">
                        <i class="bi bi-pencil-square me-1"></i>Draw
                    </button>
                    <button class="btn btn-success"
                            onclick="openPatientCameraModal(<?= $patient['id'] ?>)"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-title="Take a photo using camera for this patient">
                        <i class="bi bi-camera me-1"></i>Capture
                    </button>
                </div>
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
                                ?>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0 attachment-filename" style="font-size: 0.8rem; line-height: 1.1; flex-grow: 1; min-width: 0;"
                                        title="<?= htmlspecialchars($originalName) ?>"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                        <?= htmlspecialchars($originalName) ?>
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
        <!-- Pagination footer (rendered by reloadPatientFiles when total > perPage) -->
        <div id="patientFilesPagination" class="attachments-pagination mt-3"></div>
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
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>
            Patient Timeline
        </h5>
    </div>
    <div class="card-body">
        <div class="timeline" id="patientTimeline">
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


<script>
/* Client-side pagination for the Patient Timeline — keeps the page short when a
   patient accumulates many events. Items are server-rendered; we page through
   them in the DOM and self-create the pagination nav after the list. */
(function () {
    const PER_PAGE = 5;
    const timeline = document.getElementById('patientTimeline');
    if (!timeline) return;
    const items = Array.prototype.slice.call(timeline.children)
        .filter(el => el.classList && el.classList.contains('timeline-item'));
    const total = items.length;
    if (total <= PER_PAGE) return;
    const totalPages = Math.ceil(total / PER_PAGE);
    let current = 1;

    const navEl = document.createElement('nav');
    navEl.className = 'appointments-pagination mt-3 d-flex justify-content-center';
    navEl.setAttribute('aria-label', 'Patient timeline pagination');
    timeline.insertAdjacentElement('afterend', navEl);

    function pageWindow() {
        const set = new Set([1, totalPages, current, current - 1, current + 1]);
        if (current <= 3) { set.add(2); set.add(3); }
        if (current >= totalPages - 2) { set.add(totalPages - 1); set.add(totalPages - 2); }
        const pages = [...set].filter(p => p >= 1 && p <= totalPages).sort((a, b) => a - b);
        const out = []; let prev = 0;
        pages.forEach(p => { if (p - prev > 1) out.push('...'); out.push(p); prev = p; });
        return out;
    }
    function render() {
        items.forEach((it, i) => {
            if (Math.floor(i / PER_PAGE) + 1 === current) it.style.removeProperty('display');
            else it.style.setProperty('display', 'none', 'important');
        });
        const rtl = document.documentElement.dir === 'rtl';
        const prevIcon = rtl ? 'bi-chevron-right' : 'bi-chevron-left';
        const nextIcon = rtl ? 'bi-chevron-left' : 'bi-chevron-right';
        let h = '<ul class="pagination mb-0">';
        h += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-pg="${current - 1}" aria-label="Previous"><i class="bi ${prevIcon}"></i></a></li>`;
        pageWindow().forEach(p => {
            if (p === '...') { h += '<li class="page-item disabled"><span class="page-link">…</span></li>'; }
            else { h += `<li class="page-item ${p === current ? 'active' : ''}"><a class="page-link" href="#" data-pg="${p}">${p}</a></li>`; }
        });
        h += `<li class="page-item ${current === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-pg="${current + 1}" aria-label="Next"><i class="bi ${nextIcon}"></i></a></li>`;
        h += '</ul>';
        navEl.innerHTML = h;
        navEl.querySelectorAll('a.page-link[data-pg]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const pg = parseInt(a.getAttribute('data-pg'), 10);
                if (isNaN(pg) || pg < 1 || pg > totalPages || pg === current) return;
                current = pg; render();
                (timeline.closest('.card') || timeline).scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }
    render();
})();
</script>

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
    latest_attachment_id: <?= isset($patient['latest_attachment_id']) && $patient['latest_attachment_id'] ? (int)$patient['latest_attachment_id'] : 'null' ?>,
};
</script>
<link rel="preload" href="/assets/fonts/Amiri-Regular.ttf" as="font" type="font/ttf" crossorigin>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="/app/Views/doctor/assets/js/medical-history-popover.js?v=<?= file_exists(__DIR__ . '/assets/js/medical-history-popover.js') ? filemtime(__DIR__ . '/assets/js/medical-history-popover.js') : time() ?>"></script>
<script src="/app/Views/doctor/assets/js/patient-medical-record-pdf.js?v=<?= file_exists(__DIR__ . '/assets/js/patient-medical-record-pdf.js') ? filemtime(__DIR__ . '/assets/js/patient-medical-record-pdf.js') : time() ?>"></script>
<script src="/app/Views/doctor/assets/js/patients.js?v=<?= file_exists(__DIR__ . '/assets/js/patients.js') ? filemtime(__DIR__ . '/assets/js/patients.js') : time() ?>"></script>
<script src="/app/Views/doctor/assets/js/patient.js?v=<?= file_exists(__DIR__ . '/assets/js/patient.js') ? filemtime(__DIR__ . '/assets/js/patient.js') : time() ?>"></script>
<link rel="stylesheet" href="/app/Views/doctor/assets/css/ai-chat-widget.css?v=<?= file_exists(__DIR__ . '/assets/css/ai-chat-widget.css') ? filemtime(__DIR__ . '/assets/css/ai-chat-widget.css') : time() ?>">
<link rel="stylesheet" href="/app/Views/doctor/assets/css/draw-consultation.css?v=<?= file_exists(__DIR__ . '/assets/css/draw-consultation.css') ? filemtime(__DIR__ . '/assets/css/draw-consultation.css') : time() ?>">
<script src="/app/Views/doctor/assets/js/ai-chat-widget.js?v=<?= file_exists(__DIR__ . '/assets/js/ai-chat-widget.js') ? filemtime(__DIR__ . '/assets/js/ai-chat-widget.js') : time() ?>"></script>
<script src="/app/Views/layouts/vendor/fabric.min.js?v=<?= file_exists(dirname(__DIR__) . '/layouts/vendor/fabric.min.js') ? filemtime(dirname(__DIR__) . '/layouts/vendor/fabric.min.js') : '5.3.1' ?>"></script>
<script src="/app/Views/doctor/assets/js/draw-consultation.js?v=<?= file_exists(__DIR__ . '/assets/js/draw-consultation.js') ? filemtime(__DIR__ . '/assets/js/draw-consultation.js') : time() ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initAIChatWidget === 'function') {
            const patientId = (window.PATIENT_CONFIG && window.PATIENT_CONFIG.patientId) || <?= json_encode($patient['id'] ?? null) ?>;
            if (patientId) {
                initAIChatWidget(patientId, null);
            }
        }
    });
</script>
<style>
.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>
<script>
    // Auto-detect patient ID for IOP Trend Analyzer from URL or page data
    (function() {
        // Try to get from PHP variable first
        const patientIdFromPHP = <?= json_encode($patient['id'] ?? null) ?>;
        if (patientIdFromPHP) {
            window.currentPatientId = patientIdFromPHP;
        } else {
            // Fallback: extract from URL path /doctor/patients/{id}
            const pathMatch = window.location.pathname.match(/\/doctor\/patients\/(\d+)/);
            if (pathMatch && pathMatch[1]) {
                window.currentPatientId = parseInt(pathMatch[1]);
            }
        }
    })();
</script>