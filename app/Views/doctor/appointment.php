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

.appointment-header {
    background: linear-gradient(135deg, var(--accent), var(--success));
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.appointment-header.closed {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.appointment-header.rescheduled {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

/* Appointment Doctor Avatar Styles */
.appointment-doctor-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--accent), #10b981);
    color: white;
    font-weight: bold;
    font-size: 0.75rem;
    flex-shrink: 0;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.appointment-doctor-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.appointment-doctor-avatar-fallback {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--accent), #10b981);
    color: white;
    font-weight: bold;
    font-size: 0.75rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

/* Doctor Info Badge */
.doctor-info-badge {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050;
    max-width: 90%;
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

.doctor-info-content {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.doctor-info-icon {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.status-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    opacity: 0.9;
}

.status-badge[style*="cursor: pointer"] {
    user-select: none;
}

.status-badge i {
    font-size: 1rem;
}

/* Specific status badge colors */
.status-badge.bg-success {
    background-color: #198754 !important;
    color: white;
}

.status-badge.bg-primary {
    background-color: #0d6efd !important;
    color: white;
}

.status-badge.bg-info {
    background-color: #0dcaf0 !important;
    color: #000;
}

.status-badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.status-badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.status-badge.bg-secondary {
    background-color: #6c757d !important;
    color: white;
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
    background: linear-gradient(135deg,var(--bg), var(--border));
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 1rem;
}

.note-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.consultation-section.border-top {
    border-top: 2px solid var(--border) !important;
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

/* Dark Mode Styles */
.dark .card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .card-header {
    background-color: transparent !important;
    border-bottom-color: var(--border) !important;
    color: var(--text) !important;
}

.card-header{
    background-color: transparent !important;
}

.dark .card-body {
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

/* Dark Mode Modal Styles - Glass Effect */
.dark .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
    color: var(--text) !important;
}

.dark .modal-header {
    background: transparent !important;
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
    color: var(--text) !important;
}

/* Close button white in dark mode */
.dark .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

.dark .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

/* Enable dragging */
.modal-content {
    cursor: move;
}

.modal-dialog {
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
}

.modal-header {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.dark .modal-body {
    background: transparent !important;
    color: var(--text) !important;
}

.dark .modal-footer {
    background: transparent !important;
    border-top-color: rgba(51, 65, 85, 0.3) !important;
}

/* Dark Mode Button Styles */
.dark .btn-outline-primary {
    color: var(--accent) !important;
    border-color: var(--accent) !important;
}

.dark .btn-outline-primary:hover {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: #0b1220 !important;
}

.dark .btn-outline-success {
    color: var(--success) !important;
    border-color: var(--success) !important;
}

.dark .btn-outline-success:hover {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: #0b1220 !important;
}

.dark .btn-outline-danger {
    color: var(--danger) !important;
    border-color: var(--danger) !important;
}

.dark .btn-outline-danger:hover {
    background-color: var(--danger) !important;
    border-color: var(--danger) !important;
    color: white !important;
}

.dark .btn-outline-info {
    color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
}

.dark .btn-outline-info:hover {
    background-color: #0ea5e9 !important;
    border-color: #0ea5e9 !important;
    color: #0b1220 !important;
}

.dark .btn-outline-warning {
    color: #f59e0b !important;
    border-color: #f59e0b !important;
}

.dark .btn-outline-warning:hover {
    background-color: #f59e0b !important;
    border-color: #f59e0b !important;
    color: #0b1220 !important;
}

.dark .btn-outline-secondary {
    color: #64748b !important;
    border-color: #64748b !important;
}

.dark .btn-outline-secondary:hover {
    background-color: #64748b !important;
    border-color: #64748b !important;
    color: white !important;
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

/* Dark Mode Badge Styles */
.dark .badge {
    color: white !important;
}

.dark .badge.bg-primary {
    background-color: var(--accent) !important;
}

.dark .badge.bg-success {
    background-color: var(--success) !important;
}

.dark .badge.bg-danger {
    background-color: var(--danger) !important;
}

.dark .badge.bg-warning {
    background-color: #f59e0b !important;
    color: #0b1220 !important;
}

.dark .badge.bg-info {
    background-color: #0ea5e9 !important;
}

.dark .badge.bg-secondary {
    background-color: #64748b !important;
}

/* Drug Suggestion Badges */
.drug-suggestion-badge {
    transition: all 0.2s ease;
    font-size: 0.75rem;
    padding: 0.35rem 0.6rem;
    border-radius: 15px;
    line-height: 1.2;
}

.drug-suggestion-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    background-color: #0d6efd !important;
}

.drug-suggestion-badge:active {
    transform: translateY(0);
}

/* Drug Suggestions Dropdown */
#drugSuggestions {
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    background: white;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.suggestion-item {
    transition: background-color 0.15s ease;
}

.suggestion-item:hover {
    background-color: #f8f9fa !important;
}

.suggestion-item:last-child {
    border-bottom: none !important;
}

/* Dark mode for drug suggestions */
.dark #drugSuggestions {
    background: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .suggestion-item:hover {
    background-color: var(--bg) !important;
}

.dark .drug-suggestion-badge {
    background-color: var(--accent) !important;
}

.dark .drug-suggestion-badge:hover {
    background-color: #0284c7 !important;
}

/* Usage Count Badge - Red Circle with White Text */
.usage-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #dc3545 !important;
    color: white !important;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 0.6rem;
    font-weight: bold;
    margin-left: 0.4rem;
    min-width: 16px;
    line-height: 1;
    text-align: center;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* Dark mode for usage count badge */
.dark .usage-count-badge {
    background-color: #dc3545 !important;
    color: white !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
}

/* Dark Mode Breadcrumb Styles */
.dark .breadcrumb {
    background-color: transparent !important;
    padding: 0.75rem 0 !important;
}

.dark .breadcrumb-item {
    color: var(--muted) !important;
}

.dark .breadcrumb-item a {
    color: var(--accent) !important;
    text-decoration: none !important;
    transition: color 0.3s ease !important;
}

.dark .breadcrumb-item a:hover {
    color: var(--text) !important;
    text-decoration: none !important;
}

.dark .breadcrumb-item.active {
    color: var(--text) !important;
    font-weight: 500 !important;
}

.dark .breadcrumb-item + .breadcrumb-item::before {
    color: var(--muted) !important;
    content: "›" !important;
}

/* Dark Mode Text Color Classes */
.dark .text-primary {
    color: var(--accent) !important;
}

.dark .text-success {
    color: var(--success) !important;
}

.dark .text-info {
    color: #22d3ee !important;
}

.dark .text-danger {
    color: var(--danger) !important;
}

.dark .text-warning {
    color: #fbbf24 !important;
}

.dark .text-secondary {
    color: var(--muted) !important;
}

/* Dark Mode Badge Styles */
.dark .badge.bg-primary {
    background-color: var(--accent) !important;
    color: #0b1220 !important;
}

.dark .badge.bg-success {
    background-color: var(--success) !important;
    color: #0b1220 !important;
}

.dark .badge.bg-info {
    background-color: #22d3ee !important;
    color: #0b1220 !important;
}

.dark .badge.bg-danger {
    background-color: var(--danger) !important;
    color: #0b1220 !important;
}

.dark .badge.bg-warning {
    background-color: #fbbf24 !important;
    color: #0b1220 !important;
}

.dark .badge.bg-secondary {
    background-color: var(--muted) !important;
    color: var(--text) !important;
}

.dark .badge.bg-light {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark .badge.bg-dark {
    background-color: var(--text) !important;
    color: var(--card) !important;
}

/* Disabled dropdown item styles */
.dropdown-item.disabled {
    pointer-events: none;
    opacity: 0.6;
    cursor: not-allowed;
}

.dark .dropdown-item.disabled {
    opacity: 0.5;
}

/* Dark Mode Progress Bar Styles */
.dark .progress {
    background-color: var(--border) !important;
}

.dark .progress-bar {
    background-color: var(--accent) !important;
}

/* Responsive Action Buttons */
.action-buttons-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.action-buttons-group .btn {
    white-space: nowrap;
    flex-shrink: 0;
}

/* Mobile styles for action buttons */
@media (max-width: 768px) {
    .action-buttons-group {
        flex-wrap: nowrap;
        overflow: hidden;
    }
    
    .action-buttons-group .btn:not(.more-actions-btn) {
        flex: 0 0 auto;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .action-buttons-group .more-actions-btn {
        flex-shrink: 0;
        margin-left: auto;
    }
}

/* Extra small screens - hide some buttons, show dropdown */
@media (max-width: 576px) {
    .action-buttons-group .btn.hide-on-mobile {
        display: none !important;
    }
    
    .action-buttons-group .more-actions-btn {
        display: block !important;
        position: relative !important;
    }
    
    .action-buttons-group .more-actions-btn .btn {
        width: 100%;
    justify-content: center;
    }
    
    /* Ensure dropdown menu is positioned correctly and visible when shown */
    .action-buttons-group {
        overflow: visible !important;
        position: relative !important;
    }
    
    .action-buttons-group .more-actions-btn {
        overflow: visible !important;
        position: relative !important;
        width: 100% !important;
    }
    
    .action-buttons-group .more-actions-btn .dropdown-menu {
        position: fixed !important;
        z-index: 1050 !important;
        /* Position will be set by JavaScript */
        margin: 0 !important;
        margin-top: 0.125rem !important;
        opacity: 1 !important;
        max-height: none !important;
        overflow: visible !important;
        /* Width will be set by JavaScript to match button width */
        min-width: auto !important;
        box-sizing: border-box !important;
        /* Glass effect */
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.15) !important;
    }
    
    .dark .action-buttons-group .more-actions-btn .dropdown-menu {
        background: rgba(30, 41, 59, 0.85) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    
    /* Ensure dropdown items are visible */
    .action-buttons-group .more-actions-btn .dropdown-menu .dropdown-item {
        color: var(--text) !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    .dark .action-buttons-group .more-actions-btn .dropdown-menu .dropdown-item {
        color: var(--text) !important;
    }
    
    .action-buttons-group .more-actions-btn .dropdown-menu.show {
        display: block !important;
        visibility: visible !important;
    }
    
    /* Ensure parent containers don't clip the dropdown */
    .row.mb-4 {
        overflow: visible !important;
    }
    
    .col-12 {
        overflow: visible !important;
    }
}

/* Show all buttons on larger screens */
@media (min-width: 577px) {
    .action-buttons-group .more-actions-btn {
        display: none !important;
    }
    
    .action-buttons-group .btn.hide-on-mobile {
        display: inline-flex !important;
    }
}

/* Hide Edit Consultation button next to breadcrumb on mobile */
@media (max-width: 576px) {
    .hide-edit-on-mobile, .hide-pa-on-mobile {
        display: none !important;
    }
}
</style>

<!-- Breadcrumb -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/doctor/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/doctor/patients">Patients</a></li>
            <li class="breadcrumb-item"><a href="/doctor/patients/<?= $appointment['patient_id'] ?? '' ?>"><?= htmlspecialchars($appointment['patient_name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active">Appointment</li>
        </ol>
    </nav>
    <div class="d-flex gap-2">
        <a href="/doctor/patients/<?= $appointment['patient_id'] ?? '' ?>" class="btn btn-outline-secondary hide-pa-on-mobile">
            <i class="bi bi-person"></i> Patient Profile
        </a>
        <a href="/doctor/appointments/<?= $appointment['id'] ?? '' ?>/edit" class="btn btn-primary hide-edit-on-mobile">
            <i class="bi bi-pencil-square"></i> Edit Consultation
        </a>
    </div>
</div>

<!-- Doctor Info Badge (showing appointment doctor info) -->
<?php 
$appointmentDoctorName = $appointment['doctor_name'] ?? 'Unknown Doctor';
?>

<!-- Appointment Header -->
<div class="appointment-header <?= ($appointment['status'] === 'Closed' || $appointment['status'] === 'Rescheduled') ? ($appointment['status'] === 'Closed' ? 'closed' : 'rescheduled') : '' ?>">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-calendar-event me-2"></i>
                Appointment #<?= $appointment['id'] ?>
                <span class="badge bg-light text-dark ms-2 fs-6 shadow-sm d-inline-flex align-items-center">
                    <?php 
                    $appointmentDoctorImage = $appointment['doctor_profile_image'] ?? null;
                    if (!empty($appointmentDoctorImage)): 
                        $doctorImagePath = strpos($appointmentDoctorImage, '/public/') === 0 ? $appointmentDoctorImage : '/public' . $appointmentDoctorImage;
                    ?>
                        <img src="<?= htmlspecialchars($doctorImagePath) ?>" 
                             alt="<?= htmlspecialchars($appointmentDoctorName) ?>" 
                             class="appointment-doctor-avatar me-2"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="appointment-doctor-avatar-fallback me-2" style="display: none;">
                            <?= strtoupper(substr($appointmentDoctorName ?? 'D', 0, 1)) ?>
                        </div>
                    <?php else: ?>
                        <div class="appointment-doctor-avatar me-2">
                            <?= strtoupper(substr($appointmentDoctorName ?? 'D', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <i class="bi bi-person-check me-1"></i>
                    <?= htmlspecialchars($appointmentDoctorName) ?>'s Patient
                </span>
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
                <span class="status-badge d-flex align-items-center gap-2" 
                      id="appointmentStatusBadge" 
                      onclick="showChangeStatusModal(<?= $appointment['id'] ?>)"
                      style="cursor: pointer;" 
                      data-bs-toggle="tooltip" 
                      data-bs-placement="top" 
                      data-bs-title="Click to change status">
                    <i class="bi bi-question-circle" id="statusIcon"></i>
                    <span id="statusText"><?= ucfirst($appointment['status']) ?></span>
                </span>
                <?php if ($appointment['status'] !== 'Completed'): ?>
                <button class="btn btn-light btn-sm" id="markCompletedBtn" onclick="markAsCompleted(<?= $appointment['id'] ?>)">
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
        <div class="action-buttons-group" role="group">
            <button type="button" class="btn btn-primary hide-on-mobile" onclick="editConsultation(<?= $appointment['id'] ?>)">
                <i class="bi bi-pencil me-1"></i>Edit Consultation
            </button>
            <button type="button" class="btn btn-success hide-on-mobile" onclick="addPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-prescription2 me-1"></i>Add Prescription
            </button>
            <button type="button" class="btn btn-danger hide-on-mobile" onclick="addGlassesPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-eyeglasses me-1"></i>Add Glasses
            </button>
            <button type="button" class="btn btn-info hide-on-mobile" onclick="printReport(<?= $appointment['id'] ?>)">
                <i class="bi bi-printer me-1"></i>Print Report
            </button>
            <button type="button" class="btn btn-warning hide-on-mobile" 
                    onclick="rescheduleFollowupAppointment(<?= $appointment['id'] ?>)">
                <i class="bi bi-calendar-check me-1"></i>Reschedule Followup
            </button>
            <button type="button" class="btn btn-warning hide-on-mobile" 
                    onclick="rescheduleAppointment(<?= $appointment['id'] ?>)"
                    <?= $appointment['status'] === 'Completed' ? 'disabled title="Cannot reschedule completed appointments"' : '' ?>>
                <i class="bi bi-calendar-plus me-1"></i>Reschedule
            </button>
            <button type="button" class="btn btn-warning hide-on-mobile" onclick="openAlertModal(<?= $appointment['patient_id'] ?? 'null' ?>, <?= $appointment['id'] ?>)">
                <i class="bi bi-bell me-1"></i>Set Alert
            </button>
            
            <!-- More Actions Dropdown for Mobile -->
            <div class="dropdown more-actions-btn">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="moreActionsDropdown" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical me-1"></i>Appointment Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="moreActionsDropdown">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { addPrescription(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-prescription2 me-2"></i>Add Prescription
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { addGlassesPrescription(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-eyeglasses me-2"></i>Add Glasses
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { printReport(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-printer me-2"></i>Print Report
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" 
                           href="javascript:void(0);" 
                           onclick="closeDropdownAndExecute('moreActionsDropdown', function() { rescheduleFollowupAppointment(<?= $appointment['id'] ?>); });">
                            <i class="bi bi-calendar-check me-2"></i>Reschedule Followup
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item <?= $appointment['status'] === 'Completed' ? 'disabled text-muted' : '' ?>" 
                           href="javascript:void(0);" 
                           <?php if ($appointment['status'] !== 'Completed'): ?>
                           onclick="closeDropdownAndExecute('moreActionsDropdown', function() { rescheduleAppointment(<?= $appointment['id'] ?>); });"
                           <?php else: ?>
                           onclick="return false;" title="Cannot reschedule completed appointments"
                           <?php endif; ?>>
                            <i class="bi bi-calendar-plus me-2"></i>Reschedule
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { openAlertModal(<?= $appointment['patient_id'] ?? 'null' ?>, <?= $appointment['id'] ?>); });">
                            <i class="bi bi-bell me-2"></i>Set Alert
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="closeDropdownAndExecute('moreActionsDropdown', function() { editConsultation(<?= $appointment['id'] ?>); });">
                                <i class="bi bi-pencil me-2"></i>Edit Consultation
                            </a>
                        </li>
                    </ul>
                </div>
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
                        <p style="display: none;"><strong>National ID:</strong> <?= htmlspecialchars($patient['national_id'] ?? 'N/A') ?></p>
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
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="deleteConsultationNote(<?= $note['id'] ?>, '<?= addslashes($note['chief_complaint'] ?? 'Consultation Note') ?>')"
                                            title="Delete this note">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Note Content (Collapsible for older notes) -->
                        <div id="note-<?= $note['id'] ?>" class="<?= $index > 0 ? 'collapse' : '' ?>">
                        
                        <!-- Chief Complaint -->
                        <?php if (!empty($note['chief_complaint'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">*Chief Complaint (Required)</h6>
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

                        <!-- Systemic Disease -->
                        <?php if (!empty($note['systemic_disease'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Systemic Disease</h6>
                            <p class="ms-3"><?= nl2br(htmlspecialchars($note['systemic_disease'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Medication -->
                        <?php if (!empty($note['medication'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">Current Medication</h6>
                            <p class="ms-3"><?= nl2br(htmlspecialchars($note['medication'])) ?></p>
                        </div>
                        <?php endif; ?>

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
                        <?php if (!empty($note['visual_acuity_right']) || !empty($note['visual_acuity_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">Visual Acuity</h6>
                            <div class="row">
                                <?php if (!empty($note['visual_acuity_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-success border-3 ps-3"><?= htmlspecialchars($note['visual_acuity_right']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                <?php if (!empty($note['visual_acuity_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                </div>
                                    <p class="ms-4 border-start border-info border-3 ps-3"><?= htmlspecialchars($note['visual_acuity_left']) ?></p>
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
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-success border-3 ps-3"><?= htmlspecialchars($note['refraction_right']) ?></p>
                                </div>
                            <?php endif; ?>
                                <?php if (!empty($note['refraction_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-info border-3 ps-3"><?= htmlspecialchars($note['refraction_left']) ?></p>
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
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-success border-3 ps-3"><?= htmlspecialchars($note['IOP_right']) ?> mmHg</p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['IOP_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <p class="ms-4 border-start border-info border-3 ps-3"><?= htmlspecialchars($note['IOP_left']) ?> mmHg</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Slit Lamp Examination -->
                        <?php if (!empty($note['slit_lamp_right']) || !empty($note['slit_lamp_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-success">Slit Lamp Examination</h6>
                            <div class="row">
                                <?php if (!empty($note['slit_lamp_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['slit_lamp_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['slit_lamp_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['slit_lamp_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Fundus Examination -->
                        <?php if (!empty($note['fundus_right']) || !empty($note['fundus_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Fundus Examination</h6>
                            <div class="row">
                                <?php if (!empty($note['fundus_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['fundus_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['fundus_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['fundus_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- External Appearance -->
                        <?php if (!empty($note['external_appearance_right']) || !empty($note['external_appearance_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-warning">External Appearance</h6>
                            <div class="row">
                                <?php if (!empty($note['external_appearance_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['external_appearance_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['external_appearance_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['external_appearance_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Eyelid -->
                        <?php if (!empty($note['eyelid_right']) || !empty($note['eyelid_left'])): ?>
                        <div class="mb-3">
                            <h6 class="text-secondary">Eyelid</h6>
                            <div class="row">
                                <?php if (!empty($note['eyelid_right'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2">OD</span>
                                        <strong>Right Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-success border-3 ps-3"><?= nl2br(htmlspecialchars($note['eyelid_right'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($note['eyelid_left'])): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2">OS</span>
                                        <strong>Left Eye:</strong>
                                    </div>
                                    <div class="ms-4 border-start border-info border-3 ps-3"><?= nl2br(htmlspecialchars($note['eyelid_left'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

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
                        <?php if (!empty($note['diagnosis'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Diagnosis (Required)</h6>
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
            <div class="card-body" id="medicationsContainer">
                <?php if (!empty($medications)): ?>
                    <?php foreach ($medications as $med): ?>
                    <div class="prescription-card p-3 mb-3" data-medication-id="<?= $med['id'] ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-primary mb-0"><?= htmlspecialchars($med['drug_name']) ?></h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="editMedication(<?= $med['id'] ?>, '<?= addslashes($med['drug_name']) ?>', '<?= addslashes($med['notes'] ?? '') ?>')" title="Edit Medication">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteMedication(<?= $med['id'] ?>)" title="Delete Medication">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php if (!empty($med['notes'])): ?>
                            <p class="text-muted mb-0">
                                <small><?= htmlspecialchars($med['notes']) ?></small>
                            </p>
                <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" id="emptyMedicationsMessage">
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
            <div class="card-body" id="glassesContainer">
                <?php if (!empty($glasses)): ?>
                    <?php foreach ($glasses as $glass): ?>
                    <div class="prescription-card p-3 mb-3" data-glasses-id="<?= $glass['id'] ?>">
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
                    <div class="text-center" id="emptyGlassesMessage">
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
                <div id="attachmentsContainer">
                <?php if (!empty($attachments)): ?>
                    <div class="row" id="attachmentsRow">
                        <?php foreach ($attachments as $attachment): ?>
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
                        <div class="col-md-6 mb-3">
                            <div class="attachment-card p-2 border rounded" data-attachment-id="<?= $attachment['id'] ?>" style="min-height: <?= $isImage ? '200px' : '140px' ?>; display: flex; flex-direction: column;">
                                    <?php if ($isImage): ?>
                                <!-- Thumbnail for images -->
                                <div class="mb-2 text-center" style="cursor: pointer;" 
                                     onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')"
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
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="viewAttachment(<?= $attachment['id'] ?>, '<?= $attachment['file_path'] ?>', '<?= $fileExt ?>')" 
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="View Attachement/Photo">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" 
                                            onclick="downloadAttachment(<?= $attachment['id'] ?>, '<?= $attachment['original_filename'] ?>')"
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                        <i class="bi bi-download me-1"></i>Download
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="deleteAttachment(<?= $attachment['id'] ?>)"
                                            style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4" id="emptyAttachmentsMessage">
                        <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No images or attachments found</p>
                    </div>
                <?php endif; ?>
                </div>
                
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
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
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
            // Update UI to show completed status using new functions
            updateStatusBadge('Completed');
            
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
                            
                            <!-- Most Used Drugs Suggestions -->
                            <div class="mb-4">
                                <label class="form-label">Most Used Drugs</label>
                                <div id="mostUsedDrugs" class="d-flex flex-wrap gap-2">
                                    <div class="text-muted">
                                        <i class="bi bi-hourglass-split me-1"></i>Loading suggestions...
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Drug Name <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control" name="drug_name" id="drugNameInput" required autocomplete="off">
                                        <div id="drugSuggestions" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto;"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Dose</label>
                                    <input type="text" class="form-control" name="dose" placeholder="e.g., 1 tablet, 2 drops">
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Frequency</label>
                                    <input type="text" class="form-control" name="frequency" placeholder="e.g., Twice daily, Every 6 hours">
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" name="duration" placeholder="e.g., 7 days, 2 weeks">
                                </div>
                                <div class="col-12 mb-3" style="display: none;">
                                    <label class="form-label">Route</label>
                                    <select class="form-control" name="route">
                                        <option value="Topical">Topical</option>
                                        <option value="Oral">Oral</option>
                                        <option value="IV">IV</option>
                                        <option value="IM">IM</option>
                                        <option value="Sublingual">Sublingual</option>
                                        <option value="Inhalation">Inhalation</option>
                                        <option value="Other">Other</option>
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
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Prescription added successfully');
                setTimeout(() => {
                    reloadMedications();
                }, 300);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            showErrorMessage('Error: ' + error.message);
        });
    });
    
    // Load most used drugs suggestions
    loadMostUsedDrugs();
    
    // Setup autocomplete for drug name input
    setupDrugNameAutocomplete();
    
    // Clean up modal on hide
    document.getElementById('prescriptionModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Load most used drugs and display as clickable badges
async function loadMostUsedDrugs() {
    try {
        const response = await fetch('/api/getMostUsedDrugs?limit=10');
        const data = await response.json();
        
        const container = document.getElementById('mostUsedDrugs');
        if (data.drugs && data.drugs.length > 0) {
            container.innerHTML = '';
            data.drugs.forEach(drug => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary me-2 mb-2 drug-suggestion-badge';
                badge.style.cursor = 'pointer';
                badge.innerHTML = `
                    <i class="bi bi-capsule me-1"></i>
                    ${drug.drug_name}
                    <span class="usage-count-badge">${drug.usage_count}</span>
                `;
                badge.title = `Used ${drug.usage_count} times. Common doses: ${drug.common_doses || 'N/A'}. Common frequencies: ${drug.common_frequencies || 'N/A'}`;
                
                badge.addEventListener('click', () => {
                    document.getElementById('drugNameInput').value = drug.drug_name;
                    // Hide suggestions when drug is selected
                    document.getElementById('drugSuggestions').style.display = 'none';
                });
                
                container.appendChild(badge);
            });
        } else {
            container.innerHTML = '<div class="text-muted"><i class="bi bi-info-circle me-1"></i>No drug usage data available</div>';
        }
    } catch (error) {
        console.error('Error loading most used drugs:', error);
        const container = document.getElementById('mostUsedDrugs');
        container.innerHTML = '<div class="text-muted"><i class="bi bi-exclamation-triangle me-1"></i>Failed to load suggestions</div>';
    }
}

// Setup autocomplete functionality for drug name input
function setupDrugNameAutocomplete() {
    const drugNameInput = document.getElementById('drugNameInput');
    const suggestionsContainer = document.getElementById('drugSuggestions');
    let searchTimeout;
    
    drugNameInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (searchTerm.length < 3) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            searchDrugsAutocomplete(searchTerm);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!drugNameInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    // Show suggestions when input is focused and has content
    drugNameInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 3) {
            searchDrugsAutocomplete(this.value.trim());
        }
    });
}
    
// Search drugs for autocomplete
    async function searchDrugsAutocomplete(searchTerm) {
        try {
        const response = await fetch(`/api/searchDrugsAutocomplete?q=${encodeURIComponent(searchTerm)}&limit=6`);
            const data = await response.json();
        
        const suggestionsContainer = document.getElementById('drugSuggestions');
            
            if (data.drugs && data.drugs.length > 0) {
            suggestionsContainer.innerHTML = '';
            
            data.drugs.forEach(drug => {
                const suggestionItem = document.createElement('div');
                suggestionItem.className = 'p-1 border-bottom suggestion-item';
                suggestionItem.style.cursor = 'pointer';
                suggestionItem.innerHTML = `
                    <div class="fw-bold text-primary" style="font-size: 0.8rem;">${drug.drug_name}</div>
                    <small class="text-muted" style="font-size: 0.7rem;">${drug.active_ingredient || ''} ${drug.Company ? '- ' + drug.Company : ''}</small>
                `;
                
                suggestionItem.addEventListener('click', () => {
                    document.getElementById('drugNameInput').value = drug.drug_name;
                    suggestionsContainer.style.display = 'none';
                });
                
                suggestionItem.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#f8f9fa';
                    });
                    
                suggestionItem.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '';
                    });
                    
                suggestionsContainer.appendChild(suggestionItem);
                });
                
            suggestionsContainer.style.display = 'block';
            } else {
            suggestionsContainer.innerHTML = '<div class="p-2 text-muted text-center">No drugs found</div>';
            suggestionsContainer.style.display = 'block';
            }
        } catch (error) {
            console.error('Error searching drugs:', error);
        const suggestionsContainer = document.getElementById('drugSuggestions');
        suggestionsContainer.innerHTML = '<div class="p-2 text-danger text-center">Error loading suggestions</div>';
        suggestionsContainer.style.display = 'block';
    }
}

// Format time for display (HH:mm to 12-hour format) - Global function
function formatTimeForReschedule(timeStr) {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

// Validate date selection (same as calendar.php) - Global function
function validateDateSelection(dateString) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedDate = new Date(dateString + 'T00:00:00');
    
    if (selectedDate < today) {
        return {
            valid: false,
            message: 'Cannot select a date before today. Please select today or a future date.'
        };
    }
    return { valid: true };
}

function showRescheduleModal(appointmentId) {
    // Get current appointment data
    const currentDate = '<?= $appointment['date'] ?>';
    const currentTime = '<?= $appointment['start_time'] ?>';
    const currentStatus = '<?= $appointment['status'] ?>';
    const patientName = '<?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>';
    
    // Check if appointment is completed
    if (currentStatus === 'Completed') {
        showErrorMessage('Cannot reschedule a completed appointment');
        return;
    }
    
    const modalHtml = `
        <div class="modal fade" id="rescheduleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-plus me-2"></i>Reschedule Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="rescheduleForm">
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Patient:</strong> ${patientName}<br>
                                <strong>Current Appointment:</strong> ${currentDate} at ${currentTime.substring(0, 5)}
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="new_date" id="newDateInput" required 
                                       min="${new Date().toISOString().split('T')[0]}">
                                <div class="form-text" style="color: var(--text-muted);">Must be a future date</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Time <span class="text-danger">*</span></label>
                                <select class="form-select" name="new_time" id="newTimeInput" required>
                                    <option value="">Select available time slot...</option>
                                </select>
                                <div class="form-text" style="color: var(--text-muted);">Only available time slots from calendar are shown</div>
                                <div id="timeSlotsLoading" class="text-muted mt-2" style="display: none;">
                                    <i class="bi bi-hourglass-split me-1"></i>Loading available time slots...
                                </div>
                                <div id="timeSlotsError" class="alert alert-warning mt-2" style="display: none;"></div>
                            </div>
                            <div id="rescheduleError" class="alert alert-danger" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning" id="rescheduleSubmitBtn">
                                <i class="bi bi-calendar-check me-1"></i>Reschedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
    modal.show();
    
    const newDateInput = document.getElementById('newDateInput');
    const newTimeInput = document.getElementById('newTimeInput');
    const errorDiv = document.getElementById('rescheduleError');
    const submitBtn = document.getElementById('rescheduleSubmitBtn');
    const timeSlotsLoading = document.getElementById('timeSlotsLoading');
    const timeSlotsError = document.getElementById('timeSlotsError');
    const doctorId = <?= $appointment['doctor_id'] ?? 'null' ?>;
    
    // Set minimum date to tomorrow if current date is today
    const today = new Date();
    const appointmentDate = new Date(currentDate);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (appointmentDate.toDateString() === today.toDateString()) {
        // If appointment is today, new date must be tomorrow or later
        newDateInput.min = tomorrow.toISOString().split('T')[0];
    } else {
        // If appointment is in the future, new date must be after appointment date
        const minDate = new Date(appointmentDate);
        minDate.setDate(minDate.getDate() + 1);
        newDateInput.min = minDate.toISOString().split('T')[0];
    }
    
    // Load available time slots from calendar
    function loadAvailableTimeSlotsForReschedule(selectedDate) {
        if (!selectedDate || !doctorId) {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
            return;
        }
        
        // Validate selected date
        const validation = validateDateSelection(selectedDate);
        if (!validation.valid) {
            timeSlotsError.textContent = validation.message;
            timeSlotsError.style.display = 'block';
            newTimeInput.innerHTML = '<option value="">Invalid date</option>';
            return;
        }
        
        timeSlotsLoading.style.display = 'block';
        timeSlotsError.style.display = 'none';
        newTimeInput.disabled = true;
        newTimeInput.innerHTML = '<option value="">Loading...</option>';
        
        // Fetch available slots from calendar API
        fetch(`/api/calendar?doctor_id=${doctorId}&date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                
                if (data.ok && data.data && data.data.available_slots) {
                    const availableSlots = data.data.available_slots;
                    
                    if (availableSlots.length === 0) {
                        newTimeInput.innerHTML = '<option value="">No available time slots for this date</option>';
                        timeSlotsError.textContent = 'No available time slots found for the selected date. Please choose another date.';
                        timeSlotsError.style.display = 'block';
                        return;
                    }
                    
                    // Filter slots that are later than current appointment if same date
                    const currentDateTime = new Date(currentDate + 'T' + currentTime);
                    let filteredSlots = availableSlots;
                    
                    if (selectedDate === currentDate) {
                        filteredSlots = availableSlots.filter(slot => {
                            const slotDateTime = new Date(selectedDate + 'T' + slot);
                            return slotDateTime > currentDateTime;
                        });
                    }
                    
                    if (filteredSlots.length === 0) {
                        newTimeInput.innerHTML = '<option value="">No later time slots available for this date</option>';
                        timeSlotsError.textContent = 'No later time slots available for the selected date. Please choose another date.';
                        timeSlotsError.style.display = 'block';
                        return;
                    }
                    
                    // Populate time slots dropdown
                    newTimeInput.innerHTML = '<option value="">Select available time slot...</option>';
                    filteredSlots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = formatTimeForReschedule(slot);
                        newTimeInput.appendChild(option);
                    });
                    
                    timeSlotsError.style.display = 'none';
                } else {
                    newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                    timeSlotsError.textContent = 'Failed to load available time slots. Please try again.';
                    timeSlotsError.style.display = 'block';
                }
            })
            .catch(error => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                timeSlotsError.textContent = 'Error loading available time slots: ' + error.message;
                timeSlotsError.style.display = 'block';
                console.error('Error loading time slots:', error);
            });
    }
    
    // Format time for display (HH:mm to 12-hour format)
    function formatTimeForReschedule(timeStr) {
        if (!timeStr) return '';
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }
    
    // Load time slots when date changes
    newDateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            loadAvailableTimeSlotsForReschedule(selectedDate);
            // Clear time selection when date changes
            newTimeInput.value = '';
        } else {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
        }
        validateRescheduleForm();
    });
    
    // Validation function
    function validateRescheduleForm() {
        const newDate = newDateInput.value;
        const newTime = newTimeInput.value;
        
        if (!newDate || !newTime) {
            errorDiv.style.display = 'none';
            submitBtn.disabled = false;
            return;
        }
        
        const currentDateTime = new Date(currentDate + 'T' + currentTime);
        const newDateTime = new Date(newDate + 'T' + newTime);
        const now = new Date();
        
        // Check if new date/time is in the future
        if (newDateTime <= now) {
            errorDiv.textContent = 'New appointment date and time must be in the future';
            errorDiv.style.display = 'block';
            submitBtn.disabled = true;
            return;
        }
        
        // Check if new date/time is later than current appointment
        if (newDateTime <= currentDateTime) {
            errorDiv.textContent = 'New appointment date and time must be later than the current appointment';
            errorDiv.style.display = 'block';
            submitBtn.disabled = true;
            return;
        }
        
        errorDiv.style.display = 'none';
        submitBtn.disabled = false;
    }
    
    // Real-time validation when time changes
    newTimeInput.addEventListener('change', function() {
        validateRescheduleForm();
    });
    
    // Load time slots for initial date (if date is pre-filled)
    if (newDateInput.value) {
        loadAvailableTimeSlotsForReschedule(newDateInput.value);
    }
    
    // Handle form submission
    document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate before submission
        const newDate = newDateInput.value;
        const newTime = newTimeInput.value;
        
        if (!newDate || !newTime) {
            errorDiv.textContent = 'Please fill in all required fields';
            errorDiv.style.display = 'block';
            return;
        }
        
        const currentDateTime = new Date(currentDate + 'T' + currentTime);
        const newDateTime = new Date(newDate + 'T' + newTime);
        const now = new Date();
        
        // Check if new date/time is in the future
        if (newDateTime <= now) {
            errorDiv.textContent = 'New appointment date and time must be in the future';
            errorDiv.style.display = 'block';
            return;
        }
        
        // Check if new date/time is later than current appointment
        if (newDateTime <= currentDateTime) {
            errorDiv.textContent = 'New appointment date and time must be later than the current appointment';
            errorDiv.style.display = 'block';
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Rescheduling...';
        errorDiv.style.display = 'none';
        
        console.log('Reschedule: Starting reschedule process', {
            appointmentId: appointmentId,
            newDate: newDate,
            newTime: newTime
        });
        
        // Simple form data - just new_date and new_time
        const params = new URLSearchParams();
        params.append('new_date', newDate);
        params.append('new_time', newTime);
        
        fetch('/api/appointments/' + appointmentId + '/reschedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString(),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.error || err.message || `HTTP ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Reschedule response:', data);
            if (data.ok || data.success) {
                modal.hide();
                
                // Show toast notification with rescheduled info
                const formattedDate = data.data?.formatted_date || newDate;
                const formattedTime = data.data?.formatted_time || formatTimeForReschedule(newTime);
                const toastMessage = `تم إعادة جدولة الموعد إلى ${formattedDate} الساعة ${formattedTime}`;
                
                showRescheduleToast(toastMessage, formattedDate, formattedTime);
                
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                throw new Error(data.error || data.message || 'Failed to reschedule appointment');
            }
        })
        .catch(error => {
            console.error('Reschedule error:', error);
            errorDiv.textContent = error.message || 'Error rescheduling appointment. Please try again.';
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Clean up modal on hide
    document.getElementById('rescheduleModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function rescheduleFollowupAppointment(appointmentId) {
    // Show reschedule followup modal
    showRescheduleFollowupModal(appointmentId);
}

function showRescheduleFollowupModal(appointmentId) {
    // Get current appointment data
    const currentDate = '<?= $appointment['date'] ?>';
    const currentTime = '<?= $appointment['start_time'] ?>';
    const currentStatus = '<?= $appointment['status'] ?>';
    const patientName = '<?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>';
    
    // Note: rescheduleFollowup can be done even for completed appointments
    
    const modalHtml = `
        <div class="modal fade" id="rescheduleFollowupModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-check me-2"></i>Reschedule Follow-up Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="rescheduleFollowupForm">
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Patient:</strong> ${patientName}<br>
                                <strong>Current Appointment:</strong> ${currentDate} at ${currentTime.substring(0, 5)}
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="new_date" id="newDateInputFollowup" required 
                                       min="${new Date().toISOString().split('T')[0]}">
                                <div class="form-text" style="color: var(--text-muted);">Must be a future date</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Time <span class="text-danger">*</span></label>
                                <select class="form-select" name="new_time" id="newTimeInputFollowup" required>
                                    <option value="">Select available time slot...</option>
                                </select>
                                <div class="form-text" style="color: var(--text-muted);">Only available time slots from calendar are shown</div>
                                <div id="timeSlotsLoadingFollowup" class="text-muted mt-2" style="display: none;">
                                    <i class="bi bi-hourglass-split me-1"></i>Loading available time slots...
                                </div>
                                <div id="timeSlotsErrorFollowup" class="alert alert-warning mt-2" style="display: none;"></div>
                            </div>
                            <div id="rescheduleFollowupError" class="alert alert-danger" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning" id="rescheduleFollowupSubmitBtn">
                                <i class="bi bi-calendar-check me-1"></i>Schedule Follow-up
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('rescheduleFollowupModal'));
    modal.show();
    
    const newDateInput = document.getElementById('newDateInputFollowup');
    const newTimeInput = document.getElementById('newTimeInputFollowup');
    const errorDiv = document.getElementById('rescheduleFollowupError');
    const submitBtn = document.getElementById('rescheduleFollowupSubmitBtn');
    const timeSlotsLoading = document.getElementById('timeSlotsLoadingFollowup');
    const timeSlotsError = document.getElementById('timeSlotsErrorFollowup');
    const doctorId = <?= $appointment['doctor_id'] ?? 'null' ?>;
    
    // Set minimum date to tomorrow
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    newDateInput.min = tomorrow.toISOString().split('T')[0];
    
    // Load available time slots from calendar
    function loadAvailableTimeSlotsForFollowup(selectedDate) {
        if (!selectedDate || !doctorId) {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
            return;
        }
        
        const validation = validateDateSelection(selectedDate);
        if (!validation.valid) {
            timeSlotsError.textContent = validation.message;
            timeSlotsError.style.display = 'block';
            newTimeInput.innerHTML = '<option value="">Invalid date</option>';
            return;
        }
        
        timeSlotsLoading.style.display = 'block';
        timeSlotsError.style.display = 'none';
        newTimeInput.disabled = true;
        newTimeInput.innerHTML = '<option value="">Loading...</option>';
        
        fetch(`/api/calendar?doctor_id=${doctorId}&date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                
                if (data.ok && data.data && data.data.available_slots) {
                    const availableSlots = data.data.available_slots;
                    
                    if (availableSlots.length === 0) {
                        newTimeInput.innerHTML = '<option value="">No available time slots for this date</option>';
                        timeSlotsError.textContent = 'No available time slots found for the selected date. Please choose another date.';
                        timeSlotsError.style.display = 'block';
                        return;
                    }
                    
                    newTimeInput.innerHTML = '<option value="">Select available time slot...</option>';
                    availableSlots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = formatTimeForReschedule(slot);
                        newTimeInput.appendChild(option);
                    });
                    
                    timeSlotsError.style.display = 'none';
                } else {
                    newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                    timeSlotsError.textContent = 'Failed to load available time slots. Please try again.';
                    timeSlotsError.style.display = 'block';
                }
            })
            .catch(error => {
                timeSlotsLoading.style.display = 'none';
                newTimeInput.disabled = false;
                newTimeInput.innerHTML = '<option value="">Error loading time slots</option>';
                timeSlotsError.textContent = 'Error loading available time slots: ' + error.message;
                timeSlotsError.style.display = 'block';
                console.error('Error loading time slots:', error);
            });
    }
    
    // Load time slots when date changes
    newDateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            loadAvailableTimeSlotsForFollowup(selectedDate);
            newTimeInput.value = '';
        } else {
            newTimeInput.innerHTML = '<option value="">Please select a date first</option>';
        }
    });
    
    // Handle form submission
    document.getElementById('rescheduleFollowupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newDate = newDateInput.value;
        const newTime = newTimeInput.value;
        
        if (!newDate || !newTime) {
            errorDiv.textContent = 'Please fill in all required fields';
            errorDiv.style.display = 'block';
            return;
        }
        
        const newDateTime = new Date(newDate + 'T' + newTime);
        const now = new Date();
        
        if (newDateTime <= now) {
            errorDiv.textContent = 'New appointment date and time must be in the future';
            errorDiv.style.display = 'block';
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Scheduling...';
        errorDiv.style.display = 'none';
        
        console.log('RescheduleFollowup: Starting followup scheduling process', {
            appointmentId: appointmentId,
            newDate: newDate,
            newTime: newTime
        });
        
        // Simple form data - just new_date and new_time
        const params = new URLSearchParams();
        params.append('new_date', newDate);
        params.append('new_time', newTime);
        
        fetch('/api/appointments/' + appointmentId + '/reschedule-followup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString(),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.error || err.message || `HTTP ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('RescheduleFollowup response:', data);
            if (data.ok || data.success) {
                modal.hide();
                showSuccessMessage('Follow-up appointment scheduled successfully');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                throw new Error(data.error || data.message || 'Failed to schedule follow-up appointment');
            }
        })
        .catch(error => {
            console.error('RescheduleFollowup error:', error);
            errorDiv.textContent = error.message || 'Error scheduling follow-up appointment. Please try again.';
            errorDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Clean up modal on hide
    document.getElementById('rescheduleFollowupModal').addEventListener('hidden.bs.modal', function() {
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
                        
                        <div class="mb-3" id="cameraAttachmentTypeContainer">
                            <label class="form-label">Photo Type</label>
                            <select class="form-select" id="cameraAttachmentType" required>
                                <option value="photo" selected>Photo</option>
                                <option value="xray">X-ray</option>
                                <option value="ct_scan">CT Scan</option>
                                <option value="mri">MRI</option>
                                <option value="ultrasound">Ultrasound</option>
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
                                    <p class="text-muted mt-2">Loading camera...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Camera Controls -->
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-success" id="capturePhotoBtn" onclick="capturePhoto()">
                                <i class="bi bi-camera me-2"></i>Take Photo
                            </button>
                            <button type="button" class="btn btn-warning" id="retakePhotoBtn" onclick="retakePhoto()" style="display: none;">
                                <i class="bi bi-arrow-clockwise me-2"></i>Retake
                            </button>
                            <button type="button" class="btn btn-danger" id="stopCameraBtn" onclick="stopCamera()">
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
    
    // Start camera automatically when modal is shown
    document.getElementById('cameraModal').addEventListener('shown.bs.modal', function() {
        startCamera();
    });
    
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
    const captureBtn = document.getElementById('capturePhotoBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const attachmentTypeContainer = document.getElementById('cameraAttachmentTypeContainer');
    
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
            
            // Hide photo type field when camera starts
            if (attachmentTypeContainer) {
                attachmentTypeContainer.style.display = 'none';
            }
            
            // Update buttons
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
    const captureBtn = document.getElementById('capturePhotoBtn');
    const retakeBtn = document.getElementById('retakePhotoBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const saveBtn = document.getElementById('savePhotoBtn');
    const attachmentTypeContainer = document.getElementById('cameraAttachmentTypeContainer');
    
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
    
    // Show photo type field when camera stops
    if (attachmentTypeContainer) {
        attachmentTypeContainer.style.display = 'block';
    }
    
    // Reset buttons
    if (captureBtn) captureBtn.style.display = 'inline-block';
    if (retakeBtn) retakeBtn.style.display = 'none';
    if (stopBtn) stopBtn.style.display = 'inline-block';
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
                    // Wait a bit for modal to close, then reload attachments via Ajax
                    setTimeout(() => {
                        reloadAttachments();
                    }, 300);
                } else {
                    showErrorMessage('Error: ' + (response.message || 'Save failed'));
                }
            } catch (parseError) {
                console.error('Response parsing error:', parseError);
                console.error('Raw response:', xhr.responseText);
                showErrorMessage('Server response error');
            }
        } else {
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            showErrorMessage('HTTP Error ' + xhr.status + ': ' + xhr.statusText);
        }
    });
    
    xhr.addEventListener('error', function() {
        showErrorMessage('Error: ' + xhr.statusText);
        saveBtn.disabled = false;
        progressDiv.style.display = 'none';
    });
    
    xhr.open('POST', '/api/attachments/upload');
    xhr.withCredentials = true;
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
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
                                    <option value="photo" selected>Photo</option>
                                    <option value="xray">X-ray</option>
                                    <option value="ct_scan">CT Scan</option>
                                    <option value="mri">MRI</option>
                                    <option value="ultrasound">Ultrasound</option>
                                    <option value="lab_report">Lab Report</option>
                                    <option value="blood_test">Blood Test</option>
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
                        // Wait a bit for modal to close, then reload attachments via Ajax
                        setTimeout(() => {
                            reloadAttachments();
                        }, 300);
                    } else {
                        showErrorMessage('Error: ' + (response.message || 'Upload failed'));
                    }
                } catch (parseError) {
                    console.error('Response parsing error:', parseError);
                    console.error('Raw response:', xhr.responseText);
                    showErrorMessage('Server response error. Please check if the API endpoint exists.');
                }
            } else {
                console.error('HTTP Error:', xhr.status, xhr.statusText);
                showErrorMessage('HTTP Error ' + xhr.status + ': ' + xhr.statusText);
            }
        });
        
        xhr.addEventListener('error', function() {
            showErrorMessage('Error: ' + xhr.statusText);
            uploadBtn.disabled = false;
            progressDiv.style.display = 'none';
        });
        
        xhr.open('POST', '/api/attachments/upload');
        xhr.withCredentials = true;
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
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
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Attachment deleted successfully');
                    // Remove attachment card from DOM
                    const attachmentCard = document.querySelector(`[data-attachment-id="${attachmentId}"]`);
                    if (attachmentCard) {
                        attachmentCard.closest('.col-md-6').remove();
                        // Check if no attachments left
                        const attachmentsRow = document.getElementById('attachmentsRow');
                        if (attachmentsRow && attachmentsRow.children.length === 0) {
                            attachmentsRow.remove();
                            const emptyMsg = document.getElementById('emptyAttachmentsMessage');
                            if (!emptyMsg) {
                                const container = document.getElementById('attachmentsContainer');
                                container.innerHTML = `
                                    <div class="text-center py-4" id="emptyAttachmentsMessage">
                                            <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">No images or attachments found</p>
                                        </div>
                                    `;
                                }
                            }
                    }
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

// Delete Consultation Note Function
function deleteConsultationNote(noteId, noteTitle) {
    const modalHtml = `
        <div class="modal fade" id="deleteConsultationNoteModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Delete Consultation Notes
                            <i class="bi bi-exclamation-triangle me-2"></i>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="bi bi-trash-fill text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h6 class="mb-3">Are you sure you want to delete this consultation note?</h6>
                        <p class="text-muted mb-3">
                            <strong>Consultation Note Title:</strong> ${noteTitle}
                        </p>
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone and all data in this note will be permanently lost.
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteConsultationNote">
                            <i class="bi bi-trash me-1"></i>Delete Consultation Note
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('deleteConsultationNoteModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConsultationNoteModal'));
    modal.show();
    
    // Handle confirm button
    document.getElementById('confirmDeleteConsultationNote').addEventListener('click', function() {
        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';
        
        fetch(`/api/consultation-notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Consultation note deleted successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showErrorMessage('Error: ' + (data.message || 'Failed to delete consultation note'));
                // Restore button state
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Consultation Note';
            }
        })
        .catch(error => {
            console.error('Consultation note delete error:', error);
            showErrorMessage('Error: ' + error.message);
            // Restore button state
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Consultation Note';
        });
    });
    
    // Clean up modal after hide
    document.getElementById('deleteConsultationNoteModal').addEventListener('hidden.bs.modal', function() {
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
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Medication deleted successfully');
                    // Remove medication card from DOM
                    const medCard = document.querySelector(`[data-medication-id="${medicationId}"]`);
                    if (medCard) {
                        medCard.remove();
                        // Check if no medications left
                        const container = document.getElementById('medicationsContainer');
                        if (container && container.querySelectorAll('[data-medication-id]').length === 0) {
                            container.innerHTML = `
                                <div class="text-center" id="emptyMedicationsMessage">
                                    <i class="bi bi-capsule text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No medications prescribed</p>
                                </div>
                            `;
                        }
                    } else {
                        // If card not found, reload all medications
                        reloadMedications();
                    }
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
function editMedication(medicationId, drugName, notes) {
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
                                <div class="col-12 mb-3">
                                    <label class="form-label">Drug Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="drug_name" value="${drugName}" required>
                                    </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Dose</label>
                                    <input type="text" class="form-control" name="dose" placeholder="e.g., 1 tablet, 2 drops">
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Frequency</label>
                                    <input type="text" class="form-control" name="frequency" placeholder="e.g., Twice daily, Every 6 hours">
                            </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control" name="duration" placeholder="e.g., 7 days, 2 weeks">
                                </div>
                                <div class="col-12 mb-3" style="display: none;">
                                    <label class="form-label">Route</label>
                                    <select class="form-control" name="route">
                                        <option value="Topical">Topical</option>
                                        <option value="Oral">Oral</option>
                                        <option value="IV">IV</option>
                                        <option value="IM">IM</option>
                                        <option value="Sublingual">Sublingual</option>
                                        <option value="Inhalation">Inhalation</option>
                                        <option value="Other">Other</option>
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
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Medication updated successfully');
                setTimeout(() => {
                    reloadMedications();
                }, 300);
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
                                    <input type="text" class="form-control" name="PD_DISTANCE" placeholder="62.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 62.0, +2, -1)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Near (NPD)</label>
                                    <input type="text" class="form-control" name="PD_NEAR" placeholder="58.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 58.0, +2, -1)</div>
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
                                            <input type="text" class="form-control" name="distance_sphere_r" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_r" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_r" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="distance_sphere_l" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_l" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_l" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
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
                                            <input type="text" class="form-control" name="near_sphere_r" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_r" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_r" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="near_sphere_l" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_l" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_l" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
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
            credentials: 'same-origin',
                headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
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
                setTimeout(() => {
                    reloadGlasses();
                }, 300);
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
                                    <input type="text" class="form-control" name="PD_DISTANCE" value="${glassesData.PD_DISTANCE || ''}" placeholder="62.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 62.0, +2, -1)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PD Near (NPD)</label>
                                    <input type="text" class="form-control" name="PD_NEAR" value="${glassesData.PD_NEAR || ''}" placeholder="58.0, +2, -1" pattern="[+-]?[0-9]*\.?[0-9]*">
                                    <div class="form-text">Enter PD value (e.g., 58.0, +2, -1)</div>
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
                                            <input type="text" class="form-control" name="distance_sphere_r" value="${glassesData.distance_sphere_r || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_r" value="${glassesData.distance_cylinder_r || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_r" value="${glassesData.distance_axis_r || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                </div>
                                </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="distance_sphere_l" value="${glassesData.distance_sphere_l || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="distance_cylinder_l" value="${glassesData.distance_cylinder_l || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="distance_axis_l" value="${glassesData.distance_axis_l || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
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
                                            <input type="text" class="form-control" name="near_sphere_r" value="${glassesData.near_sphere_r || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_r" value="${glassesData.near_cylinder_r || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_r" value="${glassesData.near_axis_r || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 border-start">
                                    <h6 class="text-primary ps-3">Left Eye (OS)</h6>
                                    <div class="row ps-3">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SPH</label>
                                            <input type="text" class="form-control" name="near_sphere_l" value="${glassesData.near_sphere_l || ''}" placeholder="0.00, +2.50, -1.25" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Sphere power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CYL</label>
                                            <input type="text" class="form-control" name="near_cylinder_l" value="${glassesData.near_cylinder_l || ''}" placeholder="0.00, +1.50, -0.75" pattern="[+-]?[0-9]*\.?[0-9]*">
                                            <div class="form-text">Cylinder power</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">AXIS</label>
                                            <input type="text" class="form-control" name="near_axis_l" value="${glassesData.near_axis_l || ''}" placeholder="0, 90, 180" pattern="[0-9]*">
                                            <div class="form-text">Axis (0-180)</div>
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
            credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                showSuccessMessage('Glasses prescription updated successfully');
                setTimeout(() => {
                    reloadGlasses();
                }, 300);
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
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Glasses prescription deleted successfully');
                    // Remove glasses card from DOM
                    const glassesCard = document.querySelector(`[data-glasses-id="${glassesId}"]`);
                    if (glassesCard) {
                        glassesCard.remove();
                        // Check if no glasses left
                        const container = document.getElementById('glassesContainer');
                        if (container && container.querySelectorAll('[data-glasses-id]').length === 0) {
                            container.innerHTML = `
                                <div class="text-center" id="emptyGlassesMessage">
                                    <i class="bi bi-eyeglasses text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2 mb-0">No glasses prescription</p>
                                </div>
                            `;
                        }
                    } else {
                        // If card not found, reload all glasses
                        reloadGlasses();
                    }
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

// Status badge functions
function getStatusBadgeClass(status) {
    const classes = {
        'Booked': 'bg-primary',
        'CheckedIn': 'bg-success',
        'InProgress': 'bg-warning',
        'Completed': 'bg-info',
        'Cancelled': 'bg-danger',
        'NoShow': 'bg-secondary',
        'Rescheduled': 'bg-info',
        'Closed': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusDisplayText(status) {
    const statusTexts = {
        'Booked': 'Booked',
        'CheckedIn': 'Checked In',
        'InProgress': 'In Progress',
        'Completed': 'Completed',
        'Cancelled': 'Cancelled',
        'NoShow': 'No Show',
        'Rescheduled': 'Rescheduled',
        'Closed': 'Closed'
    };
    return statusTexts[status] || status;
}

function getStatusIcon(status) {
    const icons = {
        'Booked': 'bi-calendar-check',
        'CheckedIn': 'bi-check-circle-fill',
        'InProgress': 'bi-hourglass-split',
        'Completed': 'bi-check2-all',
        'Cancelled': 'bi-x-circle-fill',
        'NoShow': 'bi-clock-fill',
        'Rescheduled': 'bi-arrow-clockwise',
        'Closed': 'bi-lock-fill'
    };
    return icons[status] || 'bi-question-circle';
}

function updateStatusBadge(status) {
    const badge = document.getElementById('appointmentStatusBadge');
    const icon = document.getElementById('statusIcon');
    const text = document.getElementById('statusText');
    const markCompletedBtn = document.getElementById('markCompletedBtn');
    
    if (badge && icon && text) {
        // Update classes
        badge.className = `status-badge d-flex align-items-center gap-2 ${getStatusBadgeClass(status)}`;
        
        // Update icon
        icon.className = `bi ${getStatusIcon(status)}`;
        
        // Update text
        text.textContent = getStatusDisplayText(status);
        
        // Show/hide "Mark as Completed" button based on status
        if (markCompletedBtn) {
            if (status === 'Completed') {
                markCompletedBtn.style.display = 'none';
            } else {
                markCompletedBtn.style.display = 'inline-block';
            }
        }
    }
}

// Helper function to close dropdown and execute action
function closeDropdownAndExecute(dropdownId, action) {
    const dropdownElement = document.getElementById(dropdownId);
    
    if (dropdownElement) {
        const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
        if (dropdown) {
            dropdown.hide();
        } else {
            // Try to create instance if it doesn't exist
            try {
                const newDropdown = new bootstrap.Dropdown(dropdownElement);
                newDropdown.hide();
            } catch (e) {
                // Error creating Dropdown instance
            }
        }
    }
    
    // Execute action after a small delay to ensure dropdown closes
    setTimeout(function() {
        if (typeof action === 'function') {
            action();
        }
    }, 100);
}

// Initialize tooltips for file attachments
document.addEventListener('DOMContentLoaded', function() {
    // Initialize status badge
    updateStatusBadge('<?= $appointment['status'] ?>');
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap dropdowns manually to ensure they work
    const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.forEach(function (dropdownTriggerEl) {
        try {
            const dropdown = new bootstrap.Dropdown(dropdownTriggerEl, {
                popperConfig: null // Disable Popper.js positioning
            });
        } catch (e) {
            console.error('Error initializing dropdown:', e);
        }
    });
    
    // Specifically check for moreActionsDropdown
    const moreActionsBtn = document.getElementById('moreActionsDropdown');
    if (moreActionsBtn) {
        
        // Check dropdown menu
        const dropdownMenu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
        if (dropdownMenu) {
        }
        
        // Function to position dropdown menu below button
        let positionAnimationFrame = null;
        
        function positionDropdownMenu() {
            const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
            if (menu && menu.classList.contains('show')) {
                const buttonRect = moreActionsBtn.getBoundingClientRect();
                
                // Calculate position: menu should be directly below button
                // getBoundingClientRect() gives position relative to viewport, perfect for fixed positioning
                let top = buttonRect.bottom + 4;
                
                // Check if menu would go off screen at bottom
                const estimatedMenuHeight = 220; // Approximate menu height
                if (buttonRect.bottom + estimatedMenuHeight > window.innerHeight) {
                    // Show menu above button instead
                    top = buttonRect.top - estimatedMenuHeight - 4;
                }
                
                // Calculate left position: align with button's left edge + 15px
                // Menu width should match button width
                const buttonWidth = buttonRect.width;
                let leftValue = buttonRect.left + 15;
                
                // Ensure menu doesn't go off screen on the right
                if (leftValue + buttonWidth > window.innerWidth - 16) {
                    leftValue = window.innerWidth - buttonWidth - 16; // 16px margin from right
                }
                
                // Ensure menu doesn't go off screen on the left
                if (leftValue < 16) {
                    leftValue = 16; // 16px margin from left
                }
                
                // Apply fixed positioning below button - same width as button, 15px left offset
                menu.style.position = 'fixed';
                menu.style.left = leftValue + 'px';
                menu.style.transform = 'none';
                menu.style.top = top + 'px';
                menu.style.width = buttonWidth + 'px';
                menu.style.minWidth = buttonWidth + 'px';
                menu.style.maxWidth = buttonWidth + 'px';
                menu.style.right = 'auto';
                
            }
        }
        
        // Continuous positioning update using requestAnimationFrame
        function startPositionTracking() {
            if (positionAnimationFrame) {
                cancelAnimationFrame(positionAnimationFrame);
            }
            
            function updatePosition() {
                const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
                if (menu && menu.classList.contains('show')) {
                    positionDropdownMenu();
                    positionAnimationFrame = requestAnimationFrame(updatePosition);
                } else {
                    positionAnimationFrame = null;
                }
            }
            
            positionAnimationFrame = requestAnimationFrame(updatePosition);
        }
        
        // Add click event listener for positioning
        moreActionsBtn.addEventListener('click', function(e) {
        
            // Start continuous positioning after Bootstrap shows it
        setTimeout(() => {
                positionDropdownMenu();
                startPositionTracking();
            }, 10);
        });
        
        // Hide dropdown on scroll if it's open
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
            if (menu && menu.classList.contains('show')) {
                // Hide dropdown immediately on scroll
                const dropdown = bootstrap.Dropdown.getInstance(moreActionsBtn);
                if (dropdown) {
                    dropdown.hide();
                }
                if (positionAnimationFrame) {
                    cancelAnimationFrame(positionAnimationFrame);
                    positionAnimationFrame = null;
                }
            }
        }, { passive: true });
        
        // Update position on resize
        window.addEventListener('resize', function() {
            const menu = moreActionsBtn.parentElement.querySelector('.dropdown-menu');
            if (menu && menu.classList.contains('show')) {
                positionDropdownMenu();
            }
        }, { passive: true });
        
        // Listen to Bootstrap dropdown events
        moreActionsBtn.addEventListener('show.bs.dropdown', function() {
        });
        moreActionsBtn.addEventListener('shown.bs.dropdown', function() {
            // Position menu after Bootstrap animation completes and start tracking
            positionDropdownMenu();
            startPositionTracking();
        });
        moreActionsBtn.addEventListener('hide.bs.dropdown', function() {
        });
        moreActionsBtn.addEventListener('hidden.bs.dropdown', function() {
        });
    } else {
        console.warn('More Actions button NOT found!');
    }
    
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

// Mark as Completed directly (with confirmation modal)
function markAsCompleted(appointmentId) {
    // Show confirmation modal
    showCompletionConfirmModal(appointmentId);
}

function confirmMarkCompleted(appointmentId) {
    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('completionConfirmModal'));
    modal.hide();
    
    // Show loading state
    const badge = document.getElementById('appointmentStatusBadge');
    if (badge) {
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
        badge.style.pointerEvents = 'none';
    }
    
    // API call to update status to Completed
    fetch(`/api/appointments/${appointmentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            status: 'Completed',
            reason: null
        })
    })
    .then(() => {
        // Always reload page regardless of response
        window.location.reload();
    })
    .catch(() => {
        // Even on error, reload page
        window.location.reload();
    });
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

// Show change status modal
function showChangeStatusModal(appointmentId) {
    const currentStatus = document.getElementById('statusText').textContent.trim();
    const modalHtml = `
        <div class="modal fade" id="changeStatusModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-arrow-repeat me-2"></i>Change Appointment Status
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            <strong>Current Status:</strong> 
                            <span class="badge ${getStatusBadgeClass(currentStatus)}">${currentStatus}</span>
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Select New Status</label>
                            <select class="form-select" id="newStatusSelect" required>
                                <option value="Booked" ${currentStatus === 'Booked' ? 'selected' : ''}>Booked</option>
                                <option value="NoShow" ${currentStatus === 'NoShow' ? 'selected' : ''}>No Show</option>
                                <option value="Completed" ${currentStatus === 'Completed' ? 'selected' : ''}>Completed</option>
                                <option value="Closed" ${currentStatus === 'Closed' ? 'selected' : ''}>Closed</option>
                            </select>
                        </div>
                        <div class="mb-3" id="statusReasonSection" style="display: none;">
                            <label class="form-label">Reason (Optional)</label>
                            <textarea class="form-control" id="statusReason" rows="3" placeholder="Enter reason for status change..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="confirmChangeStatus(${appointmentId})">
                            <i class="bi bi-check-circle me-1"></i>Change Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('changeStatusModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
    modal.show();
    
    // Show/hide reason section based on status
    document.getElementById('newStatusSelect').addEventListener('change', function() {
        const reasonSection = document.getElementById('statusReasonSection');
        if (this.value === 'NoShow' || this.value === 'Cancelled') {
            reasonSection.style.display = 'block';
        } else {
            reasonSection.style.display = 'none';
        }
    });
    
    // Clean up modal after hide
    document.getElementById('changeStatusModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function confirmChangeStatus(appointmentId) {
    const newStatus = document.getElementById('newStatusSelect').value;
    const reason = document.getElementById('statusReason').value.trim() || null;
    
    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('changeStatusModal'));
    modal.hide();
    
    // Show loading state
    const badge = document.getElementById('appointmentStatusBadge');
    if (badge) {
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Updating...';
        badge.style.pointerEvents = 'none';
    }
    
    // API call to update status
    fetch(`/api/appointments/${appointmentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            status: newStatus,
            reason: reason
        })
    })
    .then(() => {
        // Always reload page regardless of response
        window.location.reload();
    })
    .catch(() => {
        // Even on error, reload page
        window.location.reload();
    });
}

// Close doctor info badge
function closeDoctorInfo() {
    const infoBadge = document.getElementById('doctorInfoBadge');
    if (infoBadge) {
        infoBadge.style.animation = 'slideUp 0.3s ease-out forwards';
        setTimeout(() => {
            infoBadge.remove();
        }, 300);
    }
}

// Add slideUp animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    }
`;
document.head.appendChild(style);

// Auto-hide doctor info after 10 seconds
document.addEventListener('DOMContentLoaded', function() {
    const infoBadge = document.getElementById('doctorInfoBadge');
    if (infoBadge) {
        setTimeout(() => {
            closeDoctorInfo();
        }, 10000); // Auto-hide after 10 seconds
    }
});

// Reload attachments via Ajax
function reloadAttachments() {
    const appointmentId = <?= $appointment['id'] ?? 'null' ?>;
    if (!appointmentId) {
        console.error('No appointment ID found');
        return;
    }
    
    fetch(`/api/appointments/${appointmentId}/attachments`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.attachments !== undefined) {
                const container = document.getElementById('attachmentsContainer');
                if (!container) {
                    console.error('attachmentsContainer not found');
                    return;
                }
                
                if (data.attachments.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4" id="emptyAttachmentsMessage">
                            <i class="bi bi-paperclip text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">No images or attachments found</p>
        </div>
    `;
                } else {
                    let html = '<div class="row" id="attachmentsRow">';
                    data.attachments.forEach(attachment => {
                        const fileExt = attachment.original_filename.split('.').pop().toLowerCase();
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExt);
                        const viewUrl = `/api/attachments/view/${attachment.id}`;
                        
                        // Determine file type and badge
                        let iconClass = 'bi-file-earmark';
                        let fileType = 'Document';
                        let badgeClass = 'bg-secondary';
                        
                        if (isImage) {
                            iconClass = 'bi-image';
                            fileType = 'Photo';
                            badgeClass = 'bg-warning text-dark';
                        } else if (fileExt === 'pdf') {
                            iconClass = 'bi-file-earmark-pdf';
                            fileType = 'PDF Document';
                            badgeClass = 'bg-danger';
                        } else if (['doc', 'docx'].includes(fileExt)) {
                            iconClass = 'bi-file-earmark-word';
                            fileType = 'Word Document';
                            badgeClass = 'bg-primary';
                        } else if (['xls', 'xlsx'].includes(fileExt)) {
                            iconClass = 'bi-file-earmark-excel';
                            fileType = 'Excel Sheet';
                            badgeClass = 'bg-success';
                        }
                        
                        const displayName = attachment.original_filename.length > 20 
                            ? attachment.original_filename.substring(0, 10) + '...' 
                            : attachment.original_filename;
                        const fileSize = (attachment.file_size / 1024).toFixed(1);
                        const createdDate = new Date(attachment.created_at).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="attachment-card p-2 border rounded" data-attachment-id="${attachment.id}" style="min-height: ${isImage ? '200px' : '140px'}; display: flex; flex-direction: column;">
                                    ${isImage ? `
                                    <div class="mb-2 text-center" style="cursor: pointer;" 
                                         onclick="viewAttachment(${attachment.id}, '${attachment.file_path}', '${fileExt}')"
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         data-bs-title="View Attachement/Photo">
                                        <img src="${viewUrl}" 
                                             alt="${attachment.original_filename}"
                                             class="img-thumbnail" 
                                             style="max-width: 100%; max-height: 120px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="display: none; width: 100%; height: 120px; background: #f8f9fa; border-radius: 8px; align-items: center; justify-content: center; flex-direction: column;">
                                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                            <small class="text-muted">Image not available</small>
                    </div>
                        </div>
                                    ` : ''}
                                    <div class="d-flex align-items-center mb-2 flex-grow-1">
                                        <i class="bi ${iconClass} text-primary me-2" style="font-size: 1.2rem; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <h6 class="mb-0" style="font-size: 0.8rem; line-height: 1.1; word-wrap: break-word; overflow-wrap: break-word; flex-grow: 1;" 
                                                    title="${attachment.original_filename}"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top">
                                                    ${displayName}
                                                </h6>
                                                <span class="badge ${badgeClass} ms-2" style="font-size: 0.6rem; flex-shrink: 0; font-weight: 500; border-radius: 8px;">
                                                    ${fileType}
                                                </span>
                            </div>
                                            <small class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.1;">
                                                ${fileSize} KB
                                            </small>
                                            <small class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.1;">
                                                ${createdDate}
                                </small>
                            </div>
                    </div>
                                    ${attachment.description ? `
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small" style="font-size: 0.7rem; line-height: 1.2;"
                                           title="${attachment.description}"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="bottom">
                                           ${attachment.description.length > 40 ? attachment.description.substring(0, 37) + '...' : attachment.description}
                                        </p>
                                    </div>
                                    ` : '<div class="flex-grow-1"></div>'}
                                    <div class="btn-group btn-group-sm w-100 mt-auto" role="group">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="viewAttachment(${attachment.id}, '${attachment.file_path}', '${fileExt}')" 
                                                style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="View Attachement/Photo">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="downloadAttachment(${attachment.id}, '${attachment.original_filename}')"
                                                style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                            <i class="bi bi-download me-1"></i>Download
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteAttachment(${attachment.id})"
                                                style="font-size: 0.7rem; padding: 0.3rem 0.4rem; flex: 1;">
                                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                </div>
            </div>
        </div>
    `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                    
                    // Reinitialize tooltips
                    var tooltipTriggerList = [].slice.call(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            } else {
                console.error('Invalid response format:', data);
                if (data.message) {
                    showErrorMessage('Error loading attachments: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error reloading attachments:', error);
            showErrorMessage('Error reloading attachments. Please refresh the page.');
            // Fallback: reload page after 2 seconds
            setTimeout(() => location.reload(), 2000);
        });
}

function reloadMedications() {
    const appointmentId = <?= $appointment['id'] ?? 'null' ?>;
    if (!appointmentId) {
        console.error('No appointment ID found');
        return;
    }
    
    fetch(`/api/appointments/${appointmentId}/medications`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.medications !== undefined) {
                const container = document.getElementById('medicationsContainer');
                if (!container) {
                    console.error('medicationsContainer not found');
        return;
    }
    
                if (data.medications.length === 0) {
                    container.innerHTML = `
                        <div class="text-center" id="emptyMedicationsMessage">
                            <i class="bi bi-capsule text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No medications prescribed</p>
                        </div>
                    `;
                } else {
                    let html = '';
                    data.medications.forEach(med => {
                        html += `
                            <div class="prescription-card p-3 mb-3" data-medication-id="${med.id}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="text-primary mb-0">${escapeHtml(med.drug_name)}</h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" onclick="editMedication(${med.id}, '${escapeHtml(med.drug_name).replace(/'/g, "\\'")}', '${escapeHtml(med.notes || '').replace(/'/g, "\\'")}')" title="Edit Medication">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteMedication(${med.id})" title="Delete Medication">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                ${med.notes ? `
                                    <p class="text-muted mb-0">
                                        <small>${escapeHtml(med.notes)}</small>
                                    </p>
                                ` : ''}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                }
            } else {
                console.error('Invalid response format:', data);
            }
        })
        .catch(error => {
            console.error('Error reloading medications:', error);
            showErrorMessage('Error loading medications');
        });
}

function reloadGlasses() {
    const appointmentId = <?= $appointment['id'] ?? 'null' ?>;
    if (!appointmentId) {
        console.error('No appointment ID found');
        return;
    }
    
    fetch(`/api/appointments/${appointmentId}/glasses`, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.glasses !== undefined) {
                const container = document.getElementById('glassesContainer');
                if (!container) {
                    console.error('glassesContainer not found');
                    return;
                }
                
                if (data.glasses.length === 0) {
                    container.innerHTML = `
                        <div class="text-center" id="emptyGlassesMessage">
                            <i class="bi bi-eyeglasses text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No glasses prescription</p>
                        </div>
                    `;
        } else {
                    let html = '';
                    data.glasses.forEach(glass => {
                        const glassData = JSON.stringify(glass).replace(/"/g, '&quot;');
                        html += `
                            <div class="prescription-card p-3 mb-3" data-glasses-id="${glass.id}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="text-success mb-0">
                                        <i class="bi bi-eyeglasses me-1"></i>
                                        ${escapeHtml((glass.lens_type || 'Single Vision').charAt(0).toUpperCase() + (glass.lens_type || 'Single Vision').slice(1))}
                                    </h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" onclick="editGlassesPrescription(${glass.id}, ${glassData})" title="Edit Glasses">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteGlassesPrescription(${glass.id})" title="Delete Glasses">
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
                                                SPH: ${escapeHtml(glass.distance_sphere_r || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.distance_cylinder_r || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.distance_axis_r || 'N/A')}
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <p class="mb-1">
                                                SPH: ${escapeHtml(glass.distance_sphere_l || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.distance_cylinder_l || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.distance_axis_l || 'N/A')}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                ${(glass.near_sphere_r || glass.near_sphere_l || glass.near_cylinder_r || glass.near_cylinder_l) ? `
                                <!-- Near Vision -->
                                <div class="mb-3">
                                    <h6 class="text-info"><i class="bi bi-book me-1"></i>Near Vision</h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h6 class="text-primary">Right Eye (OD)</h6>
                                            <p class="mb-1">
                                                SPH: ${escapeHtml(glass.near_sphere_r || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.near_cylinder_r || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.near_axis_r || 'N/A')}
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-primary">Left Eye (OS)</h6>
                                            <p class="mb-1">
                                                SPH: ${escapeHtml(glass.near_sphere_l || 'N/A')}<br>
                                                CYL: ${escapeHtml(glass.near_cylinder_l || 'N/A')}<br>
                                                AXIS: ${escapeHtml(glass.near_axis_l || 'N/A')}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                                ${(glass.PD_DISTANCE || glass.PD_NEAR) ? `
                                    <div class="text-center mt-2">
                                        ${glass.PD_DISTANCE ? `<strong>PD Distance:</strong> ${escapeHtml(glass.PD_DISTANCE)}mm` : ''}
                                        ${glass.PD_DISTANCE && glass.PD_NEAR ? ' | ' : ''}
                                        ${glass.PD_NEAR ? `<strong>PD Near:</strong> ${escapeHtml(glass.PD_NEAR)}mm` : ''}
                                    </div>
                                ` : ''}
                                ${glass.comments ? `
                                    <p class="text-muted mt-2 mb-0">
                                        <small>${escapeHtml(glass.comments)}</small>
                                    </p>
                                ` : ''}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                }
            } else {
                console.error('Invalid response format:', data);
        }
    })
    .catch(error => {
            console.error('Error reloading glasses:', error);
            showErrorMessage('Error loading glasses');
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show reschedule toast notification with red styling
function showRescheduleToast(message, date, time) {
    console.log('showRescheduleToast called:', { message, date, time });
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastId = 'toast-reschedule-' + Date.now();
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 350px;">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-x me-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>تم إعادة جدولة الموعد</strong><br>
                            <small>${escapeHtml(message)}</small>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">${escapeHtml(date)} - ${escapeHtml(time)}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: false // Don't auto-hide, let user dismiss manually
    });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.getElementById('toastContainer');
    if (container) return container;
    
    const newContainer = document.createElement('div');
    newContainer.id = 'toastContainer';
    newContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    newContainer.style.zIndex = '9999';
    document.body.appendChild(newContainer);
    return newContainer;
}

// Set current patient info for alert modal
window.currentPatientInfo = {
    id: <?= isset($patient['id']) ? (int)$patient['id'] : 'null' ?>,
    first_name: <?= json_encode($patient['first_name'] ?? '') ?>,
    last_name: <?= json_encode($patient['last_name'] ?? '') ?>,
    phone: <?= json_encode($patient['phone'] ?? '') ?>,
    age: <?= isset($patient['dob']) ? date_diff(date_create($patient['dob']), date_create('now'))->y : 'null' ?>
};

    </script>

<?php include __DIR__ . '/alert_modal.php'; ?>
