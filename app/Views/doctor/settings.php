<?php
/**
 * Doctor Settings Template
 * صفحة إعدادات الأطباء
 */
?>

<style>
:root {
    --bg: #f8fafc;
    --text: #0f172a;
    --card: #ffffff;
    --muted: #475569;
    --accent: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
    --border: #e2e8f0;
    --sidebar-width: 280px;
}

.dark {
    --bg: #0b1220;
    --text: #f8fafc;
    --card: #1e293b;
    --muted: #cbd5e1;
    --accent: #38bdf8;
    --success: #4ade80;
    --danger: #fb7185;
    --border: #334155;
}

.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    animation: fadeUp 0.35s ease both;
    color: var(--text);
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.card-header {
    background: transparent !important;
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.5rem;
    color: var(--text);
}

.form-control, .form-select {
    background: var(--card);
    border: 2px solid var(--border);
    color: var(--text);
    font-weight: 500;
    border-radius: 8px;
}

.form-control:focus, .form-select:focus {
    background: var(--card);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    font-weight: 600;
}

.form-control::placeholder {
    color: var(--muted);
    font-weight: 400;
}

.form-label {
    color: var(--text);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-text {
    color: var(--muted);
}

/* Custom Toggle Switch Styles */
.toggle-switch-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.toggle-switch {
    height: 32px;
    width: 64px;
    background: var(--card);
    appearance: none;
    border-radius: 32px;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.1),
                inset 0 2px 2px rgba(0,0,0,0.1),
                inset 0 -1px 1px rgba(0,0,0,0.1);
    position: relative;
    outline: none;
    cursor: pointer;
    transition: 0.5s;
    border: 2px solid var(--border);
}

.toggle-switch::before {
    height: 26px;
    width: 26px;
    position: absolute;
    top: 1px;
    left: 1px;
    content: "";
    background: linear-gradient(to bottom, var(--card), var(--bg));
    border-radius: 50%;
    transform: scale(0.9);
    transition: 0.5s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3),
                inset 1px 1px rgba(255,255,255,0.2),
                inset -1px 1px rgba(255,255,255,0.2);
}

.toggle-switch:checked {
    background: var(--accent);
    box-shadow: inset 0 1px 10px rgba(0,0,0,0.1),
                inset 0 1px 2px rgba(0,0,0,0.1),
                inset 0 -1px 1px rgba(0,0,0,0.05);
    border-color: var(--accent);
}

.toggle-switch:checked::before {
    left: 33px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2),
                inset 1px 1px rgba(255,255,255,1),
                inset -1px 1px rgba(255,255,255,1);
    background: linear-gradient(to bottom, #ffffff, #f0f0f0);
}

.toggle-switch-label {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    font-size: 12px;
    font-weight: 700;
    pointer-events: none;
    transition: 0.5s;
    color: var(--text) !important;
    opacity: 0.6;
}

.toggle-switch:checked .toggle-switch-label {
    opacity: 1;
}

.toggle-switch::after {
    content: "OFF";
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 8px;
    font-weight: 700;
    color: var(--text) !important;
    opacity: 0.7;
    transition: 0.5s;
    pointer-events: none;
}

.toggle-switch:checked::after {
    content: "ON";
    left: 40px;
    color: black !important;
    opacity: 1;
}

/* Dark mode adjustments */
.dark .toggle-switch {
    background: var(--card);
    box-shadow: inset 0 4px 20px rgba(0,0,0,0.3),
                inset 0 4px 4px rgba(0,0,0,0.2),
                inset 0 -2px 2px rgba(0,0,0,0.2);
}

.dark .toggle-switch::before {
    background: linear-gradient(to bottom, #334155, #1e293b);
    box-shadow: 0 4px 15px rgba(0,0,0,0.5),
                inset 2px 2px rgba(255,255,255,0.1),
                inset -2px 2px rgba(255,255,255,0.1);
}

.dark .toggle-switch:checked {
    background: var(--accent);
    box-shadow: inset 0 2px 20px rgba(0,0,0,0.2),
                inset 0 2px 4px rgba(0,0,0,0.1),
                inset 0 -2px 2px rgba(0,0,0,0.05);
}

.dark .toggle-switch:checked::before {
    background: linear-gradient(to bottom, #e2e8f0, #cbd5e1);
    box-shadow: 0 4px 10px rgba(0,0,0,0.3),
                inset 2px 2px rgba(255,255,255,1),
                inset -2px 2px rgba(255,255,255,1);
}

.dark .toggle-switch::after {
    color: var(--text);
}

.form-check-label {
    color: var(--text);
}

.form-control:disabled {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--muted);
    cursor: not-allowed;
    opacity: 0.6;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background-color: var(--accent);
    border-color: var(--accent);
}

.btn-primary:hover {
    background-color: var(--accent);
    border-color: var(--accent);
    opacity: 0.9;
}

.btn-outline-secondary {
    color: var(--text);
    border-color: var(--border);
}

.btn-outline-secondary:hover {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--accent);
}

.text-muted {
    color: var(--muted) !important;
}

.settings-section {
    margin-bottom: 2rem;
}

.settings-section h5 {
    color: var(--text);
    border-bottom: 2px solid var(--accent);
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.setting-item {
    margin-bottom: 1.5rem;
}

.setting-description {
    font-size: 0.875rem;
    color: var(--muted);
    margin-top: 0.25rem;
}

.input-group-text {
    background-color: var(--bg);
    border: 2px solid var(--border);
    color: var(--text);
}

.input-group .form-control {
    border-right: 0;
}

.input-group .form-control:focus + .input-group-text {
    border-color: var(--accent);
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Dark mode adjustments */
.dark .card:hover {
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.05);
}

/* Additional Dark Mode Support for Modal Alerts and Progress */
.modal-body .alert-info {
    background-color: rgba(13, 202, 240, 0.1);
    border-color: rgba(13, 202, 240, 0.3);
    color: var(--text);
}

.modal-body .alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.3);
    color: var(--text);
}

.modal-body .alert-warning {
    background-color: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
    color: var(--text);
}

.modal-body .alert-danger {
    background-color: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: var(--text);
}

.modal-body .progress {
    background-color: var(--bg);
}

.modal-body .progress-bar {
    background-color: var(--success);
}

.modal-body .card.bg-primary,
.modal-body .card.bg-success,
.modal-body .card.bg-info {
    color: white !important;
}

.modal-header.bg-success {
    background-color: var(--success) !important;
    color: white !important;
}

/* Update Database Modal Dark Mode Support */
#settingsUpdateDatabaseModal .modal-content {
    /* Glass effect - similar to sidebar */
    background: rgba(248, 250, 252, 0.35) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
    color: var(--text) !important;
}

.dark #settingsUpdateDatabaseModal .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
}

#settingsUpdateDatabaseModal .modal-header {
    background: transparent !important;
    border-bottom-color: rgba(226, 232, 240, 0.3) !important;
    color: var(--text) !important;
}

.dark #settingsUpdateDatabaseModal .modal-header {
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
}

/* Close button white in dark mode */
.dark #settingsUpdateDatabaseModal .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

.dark #settingsUpdateDatabaseModal .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

/* Enable dragging */
#settingsUpdateDatabaseModal .modal-content {
    cursor: move;
}

#settingsUpdateDatabaseModal .modal-dialog {
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
}

#settingsUpdateDatabaseModal .modal-header {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

#settingsUpdateDatabaseModal .modal-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .modal-footer {
    background-color: var(--bg) !important;
    border-top-color: var(--border) !important;
}

#settingsUpdateDatabaseModal .text-muted {
    color: var(--muted) !important;
}

#settingsUpdateDatabaseModal .form-label {
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .alert {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .alert-info {
    background-color: rgba(13, 202, 240, 0.1) !important;
    border-color: rgba(13, 202, 240, 0.3) !important;
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .alert-success {
    background-color: rgba(16, 185, 129, 0.1) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .alert-warning {
    background-color: rgba(245, 158, 11, 0.1) !important;
    border-color: rgba(245, 158, 11, 0.3) !important;
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .alert-danger {
    background-color: rgba(239, 68, 68, 0.1) !important;
    border-color: rgba(239, 68, 68, 0.3) !important;
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#settingsUpdateDatabaseModal .card.bg-primary,
#settingsUpdateDatabaseModal .card.bg-success,
#settingsUpdateDatabaseModal .card.bg-info {
    color: white !important;
}

#settingsUpdateDatabaseModal .progress {
    background-color: var(--bg) !important;
}

#settingsUpdateDatabaseModal .progress-bar {
    background-color: var(--success) !important;
}

/* Already handled above with .dark selector */

.dark #settingsUpdateDatabaseModal .modal-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark #settingsUpdateDatabaseModal .modal-footer {
    background-color: var(--bg) !important;
    border-top-color: var(--border) !important;
}

.dark #settingsUpdateDatabaseModal .btn-secondary {
    background-color: var(--bg) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark #settingsUpdateDatabaseModal .btn-secondary:hover {
    background-color: var(--border) !important;
    color: var(--text) !important;
}

.dark #settingsUpdateDatabaseModal .btn-success {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: white !important;
}

.dark #settingsUpdateDatabaseModal .btn-success:hover {
    opacity: 0.9;
}

.dark #settingsUpdateDatabaseModal .alert strong {
    color: var(--text) !important;
}

.dark #settingsUpdateDatabaseModal .alert p {
    color: var(--text) !important;
}

/* Theme Toggle Switch for Settings Page */
.settings-page .switch {
    position: relative;
    display: inline-block;
    width: calc(var(--scale, 1) * 60px);
    height: calc(var(--scale, 1) * 34px);
}

.settings-page .switch #currentModeInput {
    opacity: 0;
    width: 0;
    height: 0;
}

.settings-page .slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #2196f3;
    transition: 0.4s;
    z-index: 0;
    overflow: hidden;
}

.settings-page .sun-moon {
    position: absolute;
    content: "";
    height: calc(var(--scale, 1) * 26px);
    width: calc(var(--scale, 1) * 26px);
    left: calc(var(--scale, 1) * 4px);
    bottom: calc(var(--scale, 1) * 4px);
    background-color: yellow;
    transition: 0.4s;
}

#currentModeInput:checked + .slider {
    background-color: black;
}

#currentModeInput:focus + .slider {
    box-shadow: 0 0 calc(var(--scale, 1) * 1px) #2196f3;
}

#currentModeInput:checked + .slider .sun-moon {
    transform: translateX(calc(var(--scale, 1) * 26px));
    background-color: white;
    animation: rotate-center-settings 0.6s ease-in-out both;
}

.settings-page .moon-dot {
    opacity: 0;
    transition: 0.4s;
    fill: gray;
}

#currentModeInput:checked + .slider .sun-moon .moon-dot {
    opacity: 1;
}

.settings-page .slider.round {
    border-radius: calc(var(--scale, 1) * 34px);
}

.settings-page .slider.round .sun-moon {
    border-radius: 50%;
}

#settings-moon-dot-1 {
    left: calc(var(--scale, 1) * 10px);
    top: calc(var(--scale, 1) * 3px);
    position: absolute;
    width: calc(var(--scale, 1) * 6px);
    height: calc(var(--scale, 1) * 6px);
    z-index: 4;
}

#settings-moon-dot-2 {
    left: calc(var(--scale, 1) * 2px);
    top: calc(var(--scale, 1) * 10px);
    position: absolute;
    width: calc(var(--scale, 1) * 10px);
    height: calc(var(--scale, 1) * 10px);
    z-index: 4;
}

#settings-moon-dot-3 {
    left: calc(var(--scale, 1) * 16px);
    top: calc(var(--scale, 1) * 18px);
    position: absolute;
    width: calc(var(--scale, 1) * 3px);
    height: calc(var(--scale, 1) * 3px);
    z-index: 4;
}

#settings-light-ray-1,
#settings-light-ray-3,
#settings-light-ray-2 {
    position: absolute;
    z-index: -1;
    fill: white;
    opacity: 10%;
}

#settings-light-ray-1 {
    left: calc(var(--scale, 1) * -8px);
    top: calc(var(--scale, 1) * -8px);
    width: calc(var(--scale, 1) * 43px);
    height: calc(var(--scale, 1) * 43px);
}

#settings-light-ray-2 {
    left: -50%;
    top: -50%;
    width: calc(var(--scale, 1) * 55px);
    height: calc(var(--scale, 1) * 55px);
}

#settings-light-ray-3 {
    left: calc(var(--scale, 1) * -18px);
    top: calc(var(--scale, 1) * -18px);
    width: calc(var(--scale, 1) * 60px);
    height: calc(var(--scale, 1) * 60px);
}

.settings-page .cloud-light,
.settings-page .cloud-dark {
    position: absolute;
    animation-name: cloud-move-settings;
    animation-duration: 6s;
    animation-iteration-count: infinite;
}

.settings-page .cloud-light {
    fill: #eee;
}

.settings-page .cloud-dark {
    fill: #ccc;
    animation-delay: 1s;
}

#settings-cloud-1 {
    left: calc(var(--scale, 1) * 30px);
    top: calc(var(--scale, 1) * 15px);
    width: calc(var(--scale, 1) * 40px);
}

#settings-cloud-2 {
    left: calc(var(--scale, 1) * 44px);
    top: calc(var(--scale, 1) * 10px);
    width: calc(var(--scale, 1) * 20px);
}

#settings-cloud-3 {
    left: calc(var(--scale, 1) * 18px);
    top: calc(var(--scale, 1) * 24px);
    width: calc(var(--scale, 1) * 30px);
}

#settings-cloud-4 {
    left: calc(var(--scale, 1) * 36px);
    top: calc(var(--scale, 1) * 18px);
    width: calc(var(--scale, 1) * 40px);
}

#settings-cloud-5 {
    left: calc(var(--scale, 1) * 48px);
    top: calc(var(--scale, 1) * 14px);
    width: calc(var(--scale, 1) * 20px);
}

#settings-cloud-6 {
    left: calc(var(--scale, 1) * 22px);
    top: calc(var(--scale, 1) * 26px);
    width: calc(var(--scale, 1) * 30px);
}

@keyframes cloud-move-settings {
    0% {
        transform: translateX(0px);
    }
    40% {
        transform: translateX(calc(var(--scale, 1) * 4px));
    }
    80% {
        transform: translateX(calc(var(--scale, 1) * -4px));
    }
    100% {
        transform: translateX(0px);
    }
}

@keyframes rotate-center-settings {
    0% {
        transform: translateX(calc(var(--scale, 1) * 26px)) rotate(0);
    }
    100% {
        transform: translateX(calc(var(--scale, 1) * 26px)) rotate(360deg);
    }
}

.settings-page .stars {
    transform: translateY(calc(var(--scale, 1) * -32px));
    opacity: 0;
    transition: 0.4s;
}

.settings-page .star {
    fill: white;
    position: absolute;
    transition: 0.4s;
    animation-name: star-twinkle-settings;
    animation-duration: 2s;
    animation-iteration-count: infinite;
}

#currentModeInput:checked + .slider .stars {
    transform: translateY(0);
    opacity: 1;
}

#settings-star-1 {
    width: calc(var(--scale, 1) * 20px);
    top: calc(var(--scale, 1) * 2px);
    left: calc(var(--scale, 1) * 3px);
    animation-delay: 0.3s;
}

#settings-star-2 {
    width: calc(var(--scale, 1) * 6px);
    top: calc(var(--scale, 1) * 16px);
    left: calc(var(--scale, 1) * 3px);
}

#settings-star-3 {
    width: calc(var(--scale, 1) * 12px);
    top: calc(var(--scale, 1) * 20px);
    left: calc(var(--scale, 1) * 10px);
    animation-delay: 0.6s;
}

#settings-star-4 {
    width: calc(var(--scale, 1) * 18px);
    top: calc(var(--scale, 1) * 0px);
    left: calc(var(--scale, 1) * 18px);
    animation-delay: 1.3s;
}

@keyframes star-twinkle-settings {
    0% {
        transform: scale(1);
    }
    40% {
        transform: scale(1.2);
    }
    80% {
        transform: scale(0.8);
    }
    100% {
        transform: scale(1);
    }
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>
                        System Settings
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['success_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <form method="POST" action="/doctor/settings" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <!-- Personal Preferences -->
                        <div class="settings-section">
                            <h5><i class="fas fa-user-cog me-2"></i>Personal Preferences</h5>
                            
                            <!-- 1. Don't create alert for new created appointments -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Don't create alert for new created appointments</label>
                                        <div class="form-text">When enabled, new appointments will not automatically create alerts</div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="dontCreateAlertForAppointments" 
                                               onchange="updatePersonalPreference('dont_create_alert_for_appointments', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Back to top Display -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Back to Top Display</label>
                                        <div class="form-text">Show or hide the scroll to top button</div>
                                        <div class="mt-2">
                                            <div class="demo-preview" style="position: relative; height: 70px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                                <div style="position: absolute; bottom: 10px; right: 10px; width: 50px; height: 50px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                                    <i class="bi bi-arrow-up"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="backToTopDisplay" 
                                               onchange="updatePersonalPreference('back_to_top_display', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Desktop Dock -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Desktop Dock</label>
                                        <div class="form-text">Show or hide the quick access dock on desktop</div>
                                        <div class="mt-2">
                                            <div class="demo-preview" style="position: relative; height: 80px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                                <center>
                                                <div style="position: absolute; bottom: 10px; right: 10px; display: flex; gap: 8px; background: rgba(255,255,255,0.9); padding: 8px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-calendar3" style="font-size: 18px;"></i>
                                                    </div>
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-people" style="font-size: 18px;"></i>
                                                    </div>
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-capsule" style="font-size: 18px;"></i>
                                                    </div>
                                                </div>
                                                </center>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="desktopDock" 
                                               onchange="updatePersonalPreference('desktop_dock_enabled', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Dock -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Mobile Dock</label>
                                        <div class="form-text">Show or hide the quick access dock on mobile devices</div>
                                        <div class="mt-2">
                                            <div class="demo-preview" style="position: relative; height: 70px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                                <div style="position: absolute; bottom: 10px; right: 10px; width: 50px; height: 50px; background: var(--accent); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                                    <i class="bi bi-grid-3x3-gap" style="font-size: 24px;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="mobileDock" 
                                               onchange="updatePersonalPreference('mobile_dock_enabled', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Current Mode (Dark/Light) -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Current Mode</label>
                                        <div class="form-text">Switch between dark and light theme</div>
                                    </div>
                                    <label class="switch" for="currentModeInput">
                                        <input id="currentModeInput" type="checkbox" 
                                               onchange="updatePersonalPreference('theme', this.checked ? 'dark' : 'light')" />
                                        <div class="slider round">
                                            <div class="sun-moon">
                                                <svg id="settings-moon-dot-1" class="moon-dot" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-moon-dot-2" class="moon-dot" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-moon-dot-3" class="moon-dot" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-light-ray-1" class="light-ray" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-light-ray-2" class="light-ray" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-light-ray-3" class="light-ray" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-1" class="cloud-dark" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-2" class="cloud-dark" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-3" class="cloud-dark" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-4" class="cloud-light" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-5" class="cloud-light" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-6" class="cloud-light" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                            </div>
                                            <div class="stars">
                                                <svg id="settings-star-1" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                                <svg id="settings-star-2" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                                <svg id="settings-star-3" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                                <svg id="settings-star-4" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- 5. Push Notification -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Push Notification</label>
                                        <div class="form-text">Enable or disable push notifications</div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="pushNotificationsEnabled" 
                                               onchange="updatePersonalPreference('push_notifications_enabled', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Subscribed push notification browsers -->
                            <div class="setting-item">
                                <label class="form-label">Subscribed Push Notification Browsers</label>
                                <div id="pushSubscriptionsList" class="list-group mb-2" style="max-height: 200px; overflow-y: auto;">
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-hourglass-split me-2"></i>Loading...
                                    </div>
                                </div>
                                <div class="form-text">List of browsers subscribed to push notifications. Click delete to remove a subscription.</div>
                            </div>

                            <!-- 7. Display Dashboard items rearrange buttons on mobile -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Display Dashboard Items Rearrange Buttons on Mobile</label>
                                        <div class="form-text">Show or hide rearrange buttons for dashboard items on mobile devices</div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="dashboardRearrangeMobile" 
                                               onchange="updatePersonalPreference('dashboard_rearrange_mobile', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Sidebar Items -->
                            <div class="setting-item">
                                <label class="form-label">Sidebar Items</label>
                                <div class="form-text mb-2">Select which items to display in the sidebar. Some items are always enabled.</div>
                                <div id="sidebarItemsList" class="list-group sidebar-items-list" style="max-height: 300px; overflow-y: auto;">
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-hourglass-split me-2"></i>Loading...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Settings -->
                        <div class="settings-section">
                            <h5><i class="fas fa-info-circle me-2"></i>General Settings</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_name" class="form-label">Clinic Name</label>
                                        <input type="text" class="form-control" id="clinic_name" name="clinic_name" 
                                               value="<?= htmlspecialchars($settings['clinic_name']) ?>" required>
                                        <div class="form-text">The name of your clinic</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_email" class="form-label">Clinic Email</label>
                                        <input type="email" class="form-control" id="clinic_email" name="clinic_email" 
                                               value="<?= htmlspecialchars($settings['clinic_email']) ?>" required>
                                        <div class="form-text">Primary contact email</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_phone" class="form-label">Clinic Phone</label>
                                        <input type="text" class="form-control" id="clinic_phone" name="clinic_phone" 
                                               value="<?= htmlspecialchars($settings['clinic_phone']) ?>">
                                        <div class="form-text">Primary contact phone number</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_address" class="form-label">Clinic Address</label>
                                        <input type="text" class="form-control" id="clinic_address" name="clinic_address" 
                                               value="<?= htmlspecialchars($settings['clinic_address']) ?>">
                                        <div class="form-text">Physical address of the clinic</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_name_arabic" class="form-label">اسم العيادة بالعربية</label>
                                        <input type="text" class="form-control" id="clinic_name_arabic" name="clinic_name_arabic" 
                                               value="<?= htmlspecialchars($settings['clinic_name_arabic']) ?>" dir="rtl">
                                        <div class="form-text">اسم العيادة باللغة العربية</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_website" class="form-label">Website</label>
                                        <input type="text" class="form-control" id="clinic_website" name="clinic_website" 
                                               value="<?= htmlspecialchars($settings['clinic_website'] ?? '') ?>">
                                        <div class="form-text">Clinic website URL</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="clinic_logo" class="form-label">شعار العيادة</label>
                                        <input type="file" class="form-control" id="clinic_logo" name="clinic_logo" 
                                               accept="image/*" onchange="previewImage(this, 'clinic_logo_preview')" disabled>
                                        <div class="form-text">شعار العيادة العام (معطل - يمكن تحديثه من إعدادات أخرى)</div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control" id="clinic_logo_path" name="clinic_logo_path" 
                                                   value="<?= htmlspecialchars($settings['clinic_logo']) ?>" placeholder="مسار الشعار الحالي" readonly>
                                        </div>
                                        <div class="mt-2" id="clinic_logo_preview">
                                            <?php if ($settings['clinic_logo'] && file_exists('/var/www/html/clinic/public' . $settings['clinic_logo'])): ?>
                                                <img src="<?= htmlspecialchars($settings['clinic_logo']) ?>" alt="Current Logo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="clinic_logo_print" class="form-label">شعار الطباعة</label>
                                        <input type="file" class="form-control" id="clinic_logo_print" name="clinic_logo_print" 
                                               accept="image/*" onchange="previewImage(this, 'clinic_logo_print_preview')">
                                        <div class="form-text">رفع شعار العيادة للطباعة (JPEG, PNG, GIF, SVG - حد أقصى 5MB)</div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control" id="clinic_logo_print_path" name="clinic_logo_print_path" 
                                                   value="<?= htmlspecialchars($settings['clinic_logo_print']) ?>" placeholder="أو أدخل مسار الشعار">
                                        </div>
                                        <div class="mt-2" id="clinic_logo_print_preview">
                                            <?php if ($settings['clinic_logo_print'] && file_exists('/var/www/html/clinic/public' . $settings['clinic_logo_print'])): ?>
                                                <img src="<?= htmlspecialchars($settings['clinic_logo_print']) ?>" alt="Current Print Logo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="clinic_logo_watermark" class="form-label">شعار العلامة المائية</label>
                                        <input type="file" class="form-control" id="clinic_logo_watermark" name="clinic_logo_watermark" 
                                               accept="image/*" onchange="previewImage(this, 'clinic_logo_watermark_preview')">
                                        <div class="form-text">رفع شعار العيادة للعلامة المائية (JPEG, PNG, GIF, SVG - حد أقصى 5MB)</div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control" id="clinic_logo_watermark_path" name="clinic_logo_watermark_path" 
                                                   value="<?= htmlspecialchars($settings['clinic_logo_watermark']) ?>" placeholder="أو أدخل مسار الشعار">
                                        </div>
                                        <div class="mt-2" id="clinic_logo_watermark_preview">
                                            <?php if ($settings['clinic_logo_watermark'] && file_exists('/var/www/html/clinic/public' . $settings['clinic_logo_watermark'])): ?>
                                                <img src="<?= htmlspecialchars($settings['clinic_logo_watermark']) ?>" alt="Current Watermark Logo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visit Costs Settings -->
                        <div class="settings-section">
                            <h5><i class="fas fa-dollar-sign me-2"></i>Visit Costs</h5>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="new_visit_cost" class="form-label">New Visit Cost</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="new_visit_cost" name="new_visit_cost" 
                                                   value="<?= htmlspecialchars($settings['new_visit_cost']) ?>" min="0" step="0.01">
                                            <span class="input-group-text">EGP</span>
                                        </div>
                                        <div class="form-text">The cost required for the first visit to the patient</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="repeated_visit_cost" class="form-label">Follow-Up Visit Cost</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="repeated_visit_cost" name="repeated_visit_cost" 
                                                   value="<?= htmlspecialchars($settings['repeated_visit_cost']) ?>" min="0" step="0.01">
                                            <span class="input-group-text">EGP</span>
                                        </div>
                                        <div class="form-text">The cost required for the repeated visits to the patient</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="consultation_cost" class="form-label">Consultation/Medical Procedure Cost</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="consultation_cost" name="consultation_cost" 
                                                   value="<?= htmlspecialchars($settings['consultation_cost'] ?? '50.00') ?>" min="0" step="0.01">
                                            <span class="input-group-text">EGP</span>
                                        </div>
                                        <div class="form-text">The cost required for consultation or medical procedure</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drugs Database Update -->
                        <div class="settings-section">
                            <h5><i class="fas fa-database me-2"></i>Drugs Database Update</h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Information:</strong> Update the drugs database from the official source. 
                                This process downloads the latest drug information and updates your local database.
                            </div>
                            
                            <div class="setting-item">
                                <label class="form-label">Database Update</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" id="settingsUpdateDatabaseBtn" onclick="showSettingsUpdateDatabaseModal()">
                                        <i class="fas fa-sync-alt me-2"></i>
                                        Update Database
                                    </button>
                                </div>
                                <div class="form-text">Click to update the drugs database from the official source</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    if (confirm('Are you sure you want to reset all settings to their default values?')) {
        document.querySelector('form').reset();
        // Clear all previews
        document.querySelectorAll('[id$="_preview"]').forEach(preview => {
            preview.innerHTML = '';
        });
    }
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('يرجى اختيار ملف صورة صالح');
            input.value = '';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('حجم الملف كبير جداً. الحد الأقصى 5MB');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">';
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}

// Auto-save functionality (optional)
let autoSaveTimeout;
document.querySelectorAll('input, select').forEach(element => {
    element.addEventListener('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // You could implement auto-save here
        }, 2000);
    });
});

// Drugs Database Update Functions
function showSettingsUpdateDatabaseModal() {
    const modal = new bootstrap.Modal(document.getElementById('settingsUpdateDatabaseModal'));
    modal.show();
    resetSettingsUpdateModal();
}

function resetSettingsUpdateModal() {
    document.getElementById('settingsUpdateProgressBar').style.width = '0%';
    document.getElementById('settingsUpdateProgressBar').setAttribute('aria-valuenow', '0');
    document.getElementById('settingsProgressText').textContent = '0%';
    document.getElementById('settingsProgressLabel').textContent = 'Preparing...';
    document.getElementById('settingsUpdateStatusMessages').innerHTML = '';
    document.getElementById('settingsUpdateStatistics').style.display = 'none';
    document.getElementById('settingsTotalRecords').textContent = '0';
    document.getElementById('settingsInsertedRecords').textContent = '0';
    document.getElementById('settingsUpdatedRecords').textContent = '0';
    document.getElementById('settingsStartUpdateBtn').disabled = false;
    document.getElementById('settingsStartUpdateBtn').innerHTML = '<i class="fas fa-play-circle me-2"></i>Start Update';
    document.getElementById('settingsCloseUpdateModalBtn').disabled = false;
    document.getElementById('settingsUpdateSpinner').style.display = 'none';
}

function startSettingsDatabaseUpdate() {
    const startBtn = document.getElementById('settingsStartUpdateBtn');
    const closeBtn = document.getElementById('settingsCloseUpdateModalBtn');
    
    startBtn.disabled = true;
    closeBtn.disabled = true;
    startBtn.innerHTML = '<i class="fas fa-hourglass-half me-2"></i>Updating...';
    
    document.getElementById('settingsUpdateSpinner').style.display = 'block';
    
    resetSettingsUpdateModal();
    document.getElementById('settingsUpdateStatistics').style.display = 'flex';
    addSettingsStatusMessage('info', 'Starting update process...');
    updateSettingsDatabase();
}

function updateSettingsDatabase() {
    // Step 1: Downloading
    document.getElementById('settingsProgressLabel').textContent = 'Downloading database file...';
    updateSettingsProgress(10);
    addSettingsStatusMessage('info', 'Downloading database file from server...');
    
    setTimeout(() => {
        updateSettingsProgress(30);
        addSettingsStatusMessage('success', 'Database downloaded successfully');
        
        // Step 2: Extracting data
        document.getElementById('settingsProgressLabel').textContent = 'Extracting data from source database...';
        updateSettingsProgress(50);
        addSettingsStatusMessage('info', 'Extracting data from source database...');
        
        setTimeout(() => {
            updateSettingsProgress(70);
            addSettingsStatusMessage('success', 'Data extracted successfully');
            
            // Step 3: Updating database
            document.getElementById('settingsProgressLabel').textContent = 'Updating data in current database...';
            updateSettingsProgress(80);
            addSettingsStatusMessage('info', 'Updating data in current database...');
            
            // Make API call
            fetch('/api/drugs/update-database', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateSettingsProgress(100);
                    document.getElementById('settingsProgressLabel').textContent = 'Update completed successfully!';
                    addSettingsStatusMessage('success', 'Database updated successfully!');
                    
                    document.getElementById('settingsUpdateSpinner').style.display = 'none';
                    
                    if (data.statistics) {
                        document.getElementById('settingsTotalRecords').textContent = data.statistics.total || 0;
                        document.getElementById('settingsInsertedRecords').textContent = data.statistics.inserted || 0;
                        document.getElementById('settingsUpdatedRecords').textContent = data.statistics.updated || 0;
                    }
                    
                    document.getElementById('settingsCloseUpdateModalBtn').disabled = false;
                    document.getElementById('settingsCloseUpdateModalBtn').textContent = 'Close';
                    
                    document.getElementById('settingsStartUpdateBtn').disabled = false;
                    document.getElementById('settingsStartUpdateBtn').innerHTML = '<i class="fas fa-play-circle me-2"></i>Start Update';
                    
                    setTimeout(() => {
                        const updateModal = bootstrap.Modal.getInstance(document.getElementById('settingsUpdateDatabaseModal'));
                        if (updateModal) {
                            updateModal.hide();
                        }
                        alert('Database updated successfully!');
                        location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.error || data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Update error:', error);
                updateSettingsProgress(0);
                document.getElementById('settingsProgressLabel').textContent = 'An error occurred during update';
                addSettingsStatusMessage('danger', 'Error: ' + error.message);
                
                document.getElementById('settingsUpdateSpinner').style.display = 'none';
                
                document.getElementById('settingsStartUpdateBtn').disabled = false;
                document.getElementById('settingsStartUpdateBtn').innerHTML = '<i class="fas fa-redo me-2"></i>Retry';
                document.getElementById('settingsCloseUpdateModalBtn').disabled = false;
            });
        }, 1000);
    }, 1500);
}

function updateSettingsProgress(percent) {
    const progressBar = document.getElementById('settingsUpdateProgressBar');
    const progressText = document.getElementById('settingsProgressText');
    
    progressBar.style.width = percent + '%';
    progressBar.setAttribute('aria-valuenow', percent);
    progressText.textContent = percent + '%';
}

function addSettingsStatusMessage(type, message) {
    const container = document.getElementById('settingsUpdateStatusMessages');
    const alertClass = {
        'info': 'alert-info',
        'success': 'alert-success',
        'warning': 'alert-warning',
        'danger': 'alert-danger'
    }[type] || 'alert-info';
    
    const icon = {
        'info': 'fas fa-info-circle',
        'success': 'fas fa-check-circle',
        'warning': 'fas fa-exclamation-triangle',
        'danger': 'fas fa-times-circle'
    }[type] || 'fas fa-info-circle';
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    messageDiv.innerHTML = `
        <i class="${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}

// Personal Preferences Management
let personalPreferences = {};

// Load personal preferences on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPersonalPreferences();
});

async function loadPersonalPreferences() {
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.settings) {
            personalPreferences = data.settings;
            
            // Update toggle switches
            updateToggleSwitch('dontCreateAlertForAppointments', personalPreferences.dont_create_alert_for_appointments || false, 'dontCreateAlertForAppointmentsStatus');
            updateToggleSwitch('backToTopDisplay', personalPreferences.back_to_top_display !== false, 'backToTopDisplayStatus'); // Default true
            updateToggleSwitch('desktopDock', personalPreferences.desktop_dock_enabled !== false, 'desktopDockStatus'); // Default true
            updateToggleSwitch('mobileDock', personalPreferences.mobile_dock_enabled === true || personalPreferences.mobile_dock_enabled === '1' || personalPreferences.mobile_dock_enabled === 1, 'mobileDockStatus'); // Check explicitly
            // Update theme switch (special handling)
            const currentModeInput = document.getElementById('currentModeInput');
            if (currentModeInput) {
                currentModeInput.checked = personalPreferences.theme === 'dark';
            }
            updateToggleSwitch('pushNotificationsEnabled', personalPreferences.push_notifications_enabled || false, 'pushNotificationsEnabledStatus');
            updateToggleSwitch('dashboardRearrangeMobile', personalPreferences.dashboard_rearrange_mobile || false, 'dashboardRearrangeMobileStatus');
            
            // Load push subscriptions
            loadPushSubscriptions();
            
            // Load sidebar items
            loadSidebarItems();
        }
    } catch (error) {
        console.error('Error loading personal preferences:', error);
    }
}

function updateToggleSwitch(switchId, checked, statusId, isTheme = false) {
    const switchElement = document.getElementById(switchId);
    
    if (switchElement) {
        switchElement.checked = checked;
    }
}

async function updatePersonalPreference(key, value) {
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                [key]: value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            personalPreferences[key] = value;
            
            // If push notifications enabled, automatically subscribe current browser (same as enablePushBtn)
            if (key === 'push_notifications_enabled' && value === true) {
                // Request notification permission
                if (!('Notification' in window)) {
                    alert('Notifications are not supported in this browser.');
                    return;
                }
                
                // Request permission
                const permission = await Notification.requestPermission();
                
                if (permission === 'granted') {
                    try {
                        // Get service worker registration
                        const registration = await navigator.serviceWorker.ready;
                        
                        // Get VAPID public key
                        function getVapidPublicKey() {
                            return 'BM81HP8k4re4ObeiBgk2BSdC3FDx5Ke8-XbtPF_RbsEF5M6SC0OyHcygclxzQbPeiY8re_q6Hco16kLvol-4ozg';
                        }
                        
                        // Convert VAPID key
                        function urlBase64ToUint8Array(base64String) {
                            const padding = '='.repeat((4 - base64String.length % 4) % 4);
                            const base64 = (base64String + padding)
                                .replace(/\-/g, '+')
                                .replace(/_/g, '/');
                            
                            const rawData = window.atob(base64);
                            const outputArray = new Uint8Array(rawData.length);
                            
                            for (let i = 0; i < rawData.length; ++i) {
                                outputArray[i] = rawData.charCodeAt(i);
                            }
                            return outputArray;
                        }
                        
                        // Subscribe to push
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(getVapidPublicKey())
                        });
                        
                        // Get browser info
                        const browserInfo = navigator.userAgent;
                        const subscriptionObj = {
                            endpoint: subscription.endpoint,
                            keys: {
                                p256dh: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('p256dh')))),
                                auth: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('auth'))))
                            },
                            browser: browserInfo
                        };
                        
                        // Save subscription
                        const saveResponse = await fetch('/api/doctor/settings', {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        const saveData = await saveResponse.json();
                        
                        if (saveData.success && saveData.settings) {
                            let subscriptions = [];
                            
                            if (saveData.settings.push_subscription) {
                                if (typeof saveData.settings.push_subscription === 'string') {
                                    subscriptions = JSON.parse(saveData.settings.push_subscription);
                                } else if (Array.isArray(saveData.settings.push_subscription)) {
                                    subscriptions = saveData.settings.push_subscription;
                                }
                            }
                            
                            // Check if this subscription already exists
                            const existingIndex = subscriptions.findIndex(sub => {
                                const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                                return subEndpoint === subscriptionObj.endpoint;
                            });
                            
                            if (existingIndex >= 0) {
                                subscriptions[existingIndex] = subscriptionObj;
                            } else {
                                subscriptions.push(subscriptionObj);
                            }
                            
                            // Update settings with subscription
                            await fetch('/api/doctor/settings', {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    push_subscription: JSON.stringify(subscriptions)
                                })
                            });
                        }
                        
                        // Reload page to apply changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                        
                    } catch (error) {
                        console.error('Error subscribing to push:', error);
                        alert('Failed to enable push notifications. Please try again.');
                    }
                } else {
                    alert('Please allow notifications in your browser settings to enable push notifications.');
                }
            }
            
            // Reload push subscriptions list if push notification setting changed
            if (key === 'push_notifications_enabled') {
                setTimeout(() => {
                    loadPushSubscriptions();
                }, 2000);
            }
            
            // If dashboard rearrange setting changed, reload page to apply
            if (key === 'dashboard_rearrange_mobile') {
                // Reload page after a short delay to apply changes
                setTimeout(() => {
                    if (window.location.pathname.includes('/doctor/dashboard')) {
                        window.location.reload();
                    }
                }, 500);
            }
            
            // Apply changes immediately
            applyPersonalPreferences();
        } else {
            console.error('Error updating preference:', data.message);
            alert('Failed to update preference: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating personal preference:', error);
        alert('Failed to update preference. Please try again.');
    }
}

function applyPersonalPreferences() {
    // This will be called from main.php to apply settings
    if (window.applyPersonalPreferencesCallback) {
        window.applyPersonalPreferencesCallback(personalPreferences);
    }
}

async function loadPushSubscriptions() {
    const container = document.getElementById('pushSubscriptionsList');
    if (!container) return;
    
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.settings && data.settings.push_subscription) {
            let subscriptions = [];
            
            // Parse push_subscription
            if (typeof data.settings.push_subscription === 'string') {
                subscriptions = JSON.parse(data.settings.push_subscription);
            } else if (Array.isArray(data.settings.push_subscription)) {
                subscriptions = data.settings.push_subscription;
            }
            
            if (subscriptions.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-bell-slash me-2"></i>No subscribed browsers</div>';
                return;
            }
            
            let html = '';
            subscriptions.forEach((sub, index) => {
                const browserInfo = sub.browser || 'Unknown Browser';
                const endpoint = sub.endpoint || '';
                const endpointShort = endpoint.length > 50 ? endpoint.substring(0, 50) + '...' : endpoint;
                const endpointEncoded = encodeURIComponent(endpoint);
                
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${escapeHtml(browserInfo)}</strong>
                            <br>
                            <small class="text-muted">${escapeHtml(endpointShort)}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); event.stopPropagation(); showDeletePushSubscriptionModal('${endpointEncoded}', '${escapeHtml(browserInfo)}'); return false;" title="Delete Subscription">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-bell-slash me-2"></i>No subscribed browsers</div>';
        }
    } catch (error) {
        console.error('Error loading push subscriptions:', error);
        container.innerHTML = '<div class="text-center py-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error loading subscriptions</div>';
    }
}

let deletePushSubscriptionEndpoint = null;

function showDeletePushSubscriptionModal(endpoint, browserInfo) {
    deletePushSubscriptionEndpoint = decodeURIComponent(endpoint);
    document.getElementById('deletePushSubscriptionBrowserInfo').textContent = browserInfo;
    const modal = new bootstrap.Modal(document.getElementById('deletePushSubscriptionModal'));
    modal.show();
}

async function confirmDeletePushSubscription() {
    if (!deletePushSubscriptionEndpoint) {
        return;
    }
    
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.settings && data.settings.push_subscription) {
            let subscriptions = [];
            
            if (typeof data.settings.push_subscription === 'string') {
                subscriptions = JSON.parse(data.settings.push_subscription);
            } else if (Array.isArray(data.settings.push_subscription)) {
                subscriptions = data.settings.push_subscription;
            }
            
            // Find and remove subscription by endpoint
            const index = subscriptions.findIndex(sub => {
                const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                return subEndpoint === deletePushSubscriptionEndpoint;
            });
            
            if (index >= 0) {
                subscriptions.splice(index, 1);
                
                // Update settings
                const updateResponse = await fetch('/api/doctor/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        push_subscription: JSON.stringify(subscriptions)
                    })
                });
                
                const updateData = await updateResponse.json();
                
                if (updateData.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deletePushSubscriptionModal'));
                    if (modal) {
                        modal.hide();
                    }
                    loadPushSubscriptions();
                } else {
                    alert('Failed to delete subscription: ' + (updateData.message || 'Unknown error'));
                }
            } else {
                alert('Subscription not found.');
            }
        }
    } catch (error) {
        console.error('Error deleting push subscription:', error);
        alert('Failed to delete subscription. Please try again.');
    } finally {
        deletePushSubscriptionEndpoint = null;
    }
}

async function loadSidebarItems() {
    const container = document.getElementById('sidebarItemsList');
    if (!container) return;
    
    // Define all sidebar items
    const allSidebarItems = [
        { key: 'dashboard', label: 'Dashboard', icon: 'bi-speedometer2', fixed: true },
        { key: 'calendar', label: 'Calendar', icon: 'bi-calendar3', fixed: true },
        { key: 'patients', label: 'Patients', icon: 'bi-people', fixed: true },
        { key: 'organizer', label: 'Organizer', icon: 'bi-calendar-month', fixed: false },
        { key: 'drugs', label: 'Drugs Database', icon: 'bi-capsule', fixed: false },
        { key: 'payments', label: 'Financial Management', icon: 'bi-credit-card', fixed: false },
        { key: 'reports', label: 'Reports', icon: 'bi-graph-up', fixed: false },
        { key: 'media', label: 'Media', icon: 'bi-images', fixed: false },
        { key: 'glasses', label: 'Glasses Prescriptions', icon: 'bi-eyeglasses', fixed: false },
        { key: 'medications', label: 'Prescriptions', icon: 'bi-capsule', fixed: false },
        { key: 'alerts', label: 'Alerts', icon: 'bi-bell', fixed: false },
        { key: 'notes', label: 'Notes', icon: 'bi-sticky', fixed: false },
        { key: 'settings', label: 'Settings', icon: 'bi-gear', fixed: true },
        { key: 'profile', label: 'Profile', icon: 'bi-person-circle', fixed: false },
        { key: 'about', label: 'About', icon: 'bi-info-circle', fixed: true },
        { key: 'logout', label: 'Logout', icon: 'bi-box-arrow-right', fixed: true }
    ];
    
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        // Get enabled sidebar items from settings (default: all enabled)
        let enabledItems = [];
        if (data.success && data.settings && data.settings.sidebar_items_enabled) {
            if (typeof data.settings.sidebar_items_enabled === 'string') {
                enabledItems = JSON.parse(data.settings.sidebar_items_enabled);
            } else if (Array.isArray(data.settings.sidebar_items_enabled)) {
                enabledItems = data.settings.sidebar_items_enabled;
            }
        } else {
            // Default: all items enabled
            enabledItems = allSidebarItems.map(item => item.key);
        }
        
        let html = '';
        allSidebarItems.forEach(item => {
            const isEnabled = enabledItems.includes(item.key);
            const isFixed = item.fixed;
            
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi ${item.icon} me-2"></i>
                        <span>${escapeHtml(item.label)}</span>
                        ${isFixed ? '<span class="badge bg-secondary ms-2">Always Enabled</span>' : ''}
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" 
                               id="sidebarItem_${item.key}" 
                               ${isEnabled ? 'checked' : ''}
                               ${isFixed ? 'disabled' : ''}
                               onchange="updateSidebarItem('${item.key}', this.checked)">
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading sidebar items:', error);
        container.innerHTML = '<div class="text-center py-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error loading sidebar items</div>';
    }
}

async function updateSidebarItem(itemKey, enabled) {
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        // Get current enabled items
        let enabledItems = [];
        if (data.success && data.settings && data.settings.sidebar_items_enabled) {
            if (typeof data.settings.sidebar_items_enabled === 'string') {
                enabledItems = JSON.parse(data.settings.sidebar_items_enabled);
            } else if (Array.isArray(data.settings.sidebar_items_enabled)) {
                enabledItems = data.settings.sidebar_items_enabled;
            }
        }
        
        // Update the item
        if (enabled) {
            if (!enabledItems.includes(itemKey)) {
                enabledItems.push(itemKey);
            }
        } else {
            enabledItems = enabledItems.filter(key => key !== itemKey);
        }
        
        // Always include fixed items
        const fixedItems = ['dashboard', 'calendar', 'patients', 'about', 'logout'];
        fixedItems.forEach(fixedKey => {
            if (!enabledItems.includes(fixedKey)) {
                enabledItems.push(fixedKey);
            }
        });
        
        // Update settings
        const updateResponse = await fetch('/api/doctor/settings', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                sidebar_items_enabled: JSON.stringify(enabledItems)
            })
        });
        
        const updateData = await updateResponse.json();
        
        if (updateData.success) {
            personalPreferences.sidebar_items_enabled = enabledItems;
            applyPersonalPreferences();
        } else {
            alert('Failed to update sidebar item: ' + (updateData.message || 'Unknown error'));
            // Revert checkbox
            const checkbox = document.getElementById(`sidebarItem_${itemKey}`);
            if (checkbox) {
                checkbox.checked = !enabled;
            }
        }
    } catch (error) {
        console.error('Error updating sidebar item:', error);
        alert('Failed to update sidebar item. Please try again.');
        // Revert checkbox
        const checkbox = document.getElementById(`sidebarItem_${itemKey}`);
        if (checkbox) {
            checkbox.checked = !enabled;
        }
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<!-- Delete Push Subscription Modal -->
<div class="modal fade alerts-modal-glass" id="deletePushSubscriptionModal" tabindex="-1" aria-labelledby="deletePushSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePushSubscriptionModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Delete Push Subscription
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the push notification subscription for:</p>
                <p class="fw-bold" id="deletePushSubscriptionBrowserInfo"></p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeletePushSubscription()">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Settings Update Database Modal -->
<div class="modal fade" id="settingsUpdateDatabaseModal" tabindex="-1" aria-labelledby="settingsUpdateDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="settingsUpdateDatabaseModalLabel">
                    <i class="fas fa-sync-alt me-2"></i>
                    Update Drugs Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="settingsUpdateProgressContainer">
                    <div class="mb-3">
                        <p class="text-muted">The drugs database will be downloaded and updated from the official source.</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This process may take a few minutes depending on the data size.
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0" id="settingsProgressLabel">Preparing...</label>
                            <div id="settingsUpdateSpinner" style="display: none;">
                                <div class="spinner-border" role="status" style="width: 1.5rem; height: 1.5rem; border-width: 3px; border-color: #0dcaf0; border-right-color: transparent;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 id="settingsUpdateProgressBar" 
                                 style="width: 0%"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="settingsProgressText">0%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Messages -->
                    <div id="settingsUpdateStatusMessages" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                        <!-- Status messages will be added here -->
                    </div>
                    
                    <!-- Statistics -->
                    <div id="settingsUpdateStatistics" class="row mt-3" style="display: none;">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6>Total Records</h6>
                                    <h3 id="settingsTotalRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6>Inserted</h6>
                                    <h3 id="settingsInsertedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6>Updated</h6>
                                    <h3 id="settingsUpdatedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="settingsCloseUpdateModalBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="settingsStartUpdateBtn" onclick="startSettingsDatabaseUpdate()">
                    <i class="fas fa-play-circle me-2"></i>
                    Start Update
                </button>
            </div>
        </div>
    </div>
</div>

