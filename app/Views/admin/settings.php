<?php
/**
 * Admin Settings Template
 * صفحة إعدادات الإدارة
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
    background: var(--bg);
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

.form-check-input:checked {
    background-color: var(--accent);
    border-color: var(--accent);
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

/* Update Database Modal Dark Mode Support */
#updateDatabaseModal .modal-content {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .modal-header {
    background-color: var(--success) !important;
    border-bottom-color: var(--border) !important;
    color: white !important;
}

#updateDatabaseModal .modal-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .modal-footer {
    background-color: var(--bg) !important;
    border-top-color: var(--border) !important;
}

#updateDatabaseModal .text-muted {
    color: var(--muted) !important;
}

#updateDatabaseModal .form-label {
    color: var(--text) !important;
}

#updateDatabaseModal .alert {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .alert-info {
    background-color: rgba(13, 202, 240, 0.1) !important;
    border-color: rgba(13, 202, 240, 0.3) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .alert-success {
    background-color: rgba(16, 185, 129, 0.1) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .alert-warning {
    background-color: rgba(245, 158, 11, 0.1) !important;
    border-color: rgba(245, 158, 11, 0.3) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .alert-danger {
    background-color: rgba(239, 68, 68, 0.1) !important;
    border-color: rgba(239, 68, 68, 0.3) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

#updateDatabaseModal .card.bg-primary,
#updateDatabaseModal .card.bg-success,
#updateDatabaseModal .card.bg-info {
    color: white !important;
}

#updateDatabaseModal .progress {
    background-color: var(--bg) !important;
}

#updateDatabaseModal .progress-bar {
    background-color: var(--success) !important;
}

.dark #updateDatabaseModal .modal-content {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark #updateDatabaseModal .modal-header {
    background-color: var(--success) !important;
    color: white !important;
}

.dark #updateDatabaseModal .modal-body {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark #updateDatabaseModal .modal-footer {
    background-color: var(--bg) !important;
    border-top-color: var(--border) !important;
}

.dark #updateDatabaseModal .btn-secondary {
    background-color: var(--bg) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark #updateDatabaseModal .btn-secondary:hover {
    background-color: var(--border) !important;
    color: var(--text) !important;
}

.dark #updateDatabaseModal .btn-success {
    background-color: var(--success) !important;
    border-color: var(--success) !important;
    color: white !important;
}

.dark #updateDatabaseModal .btn-success:hover {
    opacity: 0.9;
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

                    <form method="POST" action="/admin/settings" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
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
                                <div class="col-md-6">
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
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="repeated_visit_cost" class="form-label">Repeated Visit Cost</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="repeated_visit_cost" name="repeated_visit_cost" 
                                                   value="<?= htmlspecialchars($settings['repeated_visit_cost']) ?>" min="0" step="0.01">
                                            <span class="input-group-text">EGP</span>
                                        </div>
                                        <div class="form-text">The cost required for the repeated visits to the patient</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Notifications -->
                        <div class="settings-section mb-4">
                            <h5><i class="bi bi-bell me-2"></i>System Notifications</h5>
                            <p class="text-muted">Send notifications to users</p>
                            <a href="/admin/notifications" class="btn btn-primary">
                                <i class="bi bi-bell me-2"></i>Manage Notifications
                            </a>
                        </div>
                        
                        <!-- System Settings -->
                        <div class="settings-section">
                            <h5><i class="fas fa-cogs me-2"></i>System Settings</h5>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-select" id="timezone" name="timezone">
                                            <option value="Africa/Cairo" <?= $settings['timezone'] === 'Africa/Cairo' ? 'selected' : '' ?>>Africa/Cairo</option>
                                            <option value="UTC" <?= $settings['timezone'] === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                            <option value="America/New_York" <?= $settings['timezone'] === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                                            <option value="Europe/London" <?= $settings['timezone'] === 'Europe/London' ? 'selected' : '' ?>>Europe/London</option>
                                        </select>
                                        <div class="form-text">System timezone</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="date_format" class="form-label">Date Format</label>
                                        <select class="form-select" id="date_format" name="date_format">
                                            <option value="Y-m-d" <?= $settings['date_format'] === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            <option value="d-m-Y" <?= $settings['date_format'] === 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY</option>
                                            <option value="m/d/Y" <?= $settings['date_format'] === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                        </select>
                                        <div class="form-text">How dates are displayed</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="time_format" class="form-label">Time Format</label>
                                        <select class="form-select" id="time_format" name="time_format">
                                            <option value="H:i" <?= $settings['time_format'] === 'H:i' ? 'selected' : '' ?>>24 Hour (HH:MM)</option>
                                            <option value="h:i A" <?= $settings['time_format'] === 'h:i A' ? 'selected' : '' ?>>12 Hour (HH:MM AM/PM)</option>
                                        </select>
                                        <div class="form-text">How times are displayed</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="items_per_page" class="form-label">Items Per Page</label>
                                        <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                                               value="<?= htmlspecialchars($settings['items_per_page']) ?>" min="1" max="100">
                                        <div class="form-text">Number of items to display per page (1-100)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                        <select class="form-select" id="backup_frequency" name="backup_frequency">
                                            <option value="daily" <?= $settings['backup_frequency'] === 'daily' ? 'selected' : '' ?>>Daily</option>
                                            <option value="weekly" <?= $settings['backup_frequency'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                            <option value="monthly" <?= $settings['backup_frequency'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                        </select>
                                        <div class="form-text">How often to create backups</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="settings-section">
                            <h5><i class="fas fa-bell me-2"></i>Notification Settings</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                                   <?= $settings['email_notifications'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_notifications">
                                                Email Notifications
                                            </label>
                                        </div>
                                        <div class="form-text">Send notifications via email</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications" 
                                                   <?= $settings['sms_notifications'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="sms_notifications">
                                                SMS Notifications
                                            </label>
                                        </div>
                                        <div class="form-text">Send notifications via SMS</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Settings -->
                        <div class="settings-section">
                            <h5><i class="fas fa-tools me-2"></i>Maintenance Settings</h5>
                            
                            <div class="setting-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                           <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="maintenance_mode">
                                        Maintenance Mode
                                    </label>
                                </div>
                                <div class="form-text">Enable maintenance mode to restrict access to the system</div>
                            </div>
                        </div>

                        <!-- Database Management -->
                        <div class="settings-section">
                            <h5><i class="fas fa-database me-2"></i>Database Management</h5>
                            
                            <div class="setting-item">
                                <button type="button" class="btn btn-success" onclick="showUpdateDatabaseModal()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Update Drugs Database
                                </button>
                                <div class="form-text">Update the drugs database from the official source</div>
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

<!-- Update Database Modal -->
<div class="modal fade" id="updateDatabaseModal" tabindex="-1" aria-labelledby="updateDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="updateDatabaseModalLabel">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Update Drugs Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="updateProgressContainer">
                    <div class="mb-3">
                        <p class="text-muted">The drugs database will be downloaded and updated from the official source.</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> This process may take a few minutes depending on the data size.
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0" id="progressLabel">Preparing...</label>
                            <div id="updateSpinner" style="display: none;">
                                <div class="spinner-border" role="status" style="width: 1.5rem; height: 1.5rem; border-width: 3px; border-color: #0dcaf0; border-right-color: transparent;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 id="updateProgressBar" 
                                 style="width: 0%"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="progressText">0%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Messages -->
                    <div id="updateStatusMessages" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                        <!-- Status messages will be added here -->
                    </div>
                    
                    <!-- Statistics -->
                    <div id="updateStatistics" class="row mt-3" style="display: none;">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6>Total Records</h6>
                                    <h3 id="totalRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6>Inserted</h6>
                                    <h3 id="insertedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6>Updated</h6>
                                    <h3 id="updatedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeUpdateModalBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="startUpdateBtn" onclick="startDatabaseUpdate()">
                    <i class="bi bi-play-circle me-2"></i>
                    Start Update
                </button>
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

// Database Update Functions
function showUpdateDatabaseModal() {
    const modal = new bootstrap.Modal(document.getElementById('updateDatabaseModal'));
    modal.show();
    
    // Reset modal state
    resetUpdateModal();
}

function resetUpdateModal() {
    document.getElementById('updateProgressBar').style.width = '0%';
    document.getElementById('updateProgressBar').setAttribute('aria-valuenow', '0');
    document.getElementById('progressText').textContent = '0%';
    document.getElementById('progressLabel').textContent = 'Preparing...';
    document.getElementById('updateStatusMessages').innerHTML = '';
    document.getElementById('updateStatistics').style.display = 'none';
    document.getElementById('totalRecords').textContent = '0';
    document.getElementById('insertedRecords').textContent = '0';
    document.getElementById('updatedRecords').textContent = '0';
    document.getElementById('startUpdateBtn').disabled = false;
    document.getElementById('startUpdateBtn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Start Update';
    document.getElementById('closeUpdateModalBtn').disabled = false;
    document.getElementById('updateSpinner').style.display = 'none';
}

function startDatabaseUpdate() {
    const startBtn = document.getElementById('startUpdateBtn');
    const closeBtn = document.getElementById('closeUpdateModalBtn');
    
    // Disable buttons
    startBtn.disabled = true;
    closeBtn.disabled = true;
    startBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
    
    // Show spinner
    document.getElementById('updateSpinner').style.display = 'block';
    
    // Reset progress
    resetUpdateModal();
    
    // Show statistics
    document.getElementById('updateStatistics').style.display = 'flex';
    
    // Add initial status message
    addStatusMessage('info', 'Starting update process...');
    
    // Start update process
    updateDatabase();
}

function updateDatabase() {
    // Step 1: Downloading
    document.getElementById('progressLabel').textContent = 'Downloading database file...';
    updateProgress(10);
    addStatusMessage('info', 'Downloading database file from server...');
    
    // Simulate progress steps
    setTimeout(() => {
        updateProgress(30);
        addStatusMessage('success', 'Database downloaded successfully');
        
        // Step 2: Extracting data
        document.getElementById('progressLabel').textContent = 'Extracting data from source database...';
        updateProgress(50);
        addStatusMessage('info', 'Extracting data from source database...');
        
        setTimeout(() => {
            updateProgress(70);
            addStatusMessage('success', 'Data extracted successfully');
            
            // Step 3: Updating database
            document.getElementById('progressLabel').textContent = 'Updating data in current database...';
            updateProgress(80);
            addStatusMessage('info', 'Updating data in current database...');
            
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
                    updateProgress(100);
                    document.getElementById('progressLabel').textContent = 'Update completed successfully!';
                    addStatusMessage('success', 'Database updated successfully!');
                    
                    // Hide spinner
                    document.getElementById('updateSpinner').style.display = 'none';
                    
                    // Update statistics
                    if (data.statistics) {
                        document.getElementById('totalRecords').textContent = data.statistics.total || 0;
                        document.getElementById('insertedRecords').textContent = data.statistics.inserted || 0;
                        document.getElementById('updatedRecords').textContent = data.statistics.updated || 0;
                    }
                    
                    // Enable close button
                    document.getElementById('closeUpdateModalBtn').disabled = false;
                    document.getElementById('closeUpdateModalBtn').textContent = 'Close';
                    
                    // Enable start button for potential retry
                    document.getElementById('startUpdateBtn').disabled = false;
                    document.getElementById('startUpdateBtn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Start Update';
                    
                    // Show success message
                    setTimeout(() => {
                        alert('Database updated successfully!');
                        location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.error || data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Update error:', error);
                updateProgress(0);
                document.getElementById('progressLabel').textContent = 'An error occurred during update';
                addStatusMessage('danger', 'Error: ' + error.message);
                
                // Hide spinner
                document.getElementById('updateSpinner').style.display = 'none';
                
                // Enable buttons
                document.getElementById('startUpdateBtn').disabled = false;
                document.getElementById('startUpdateBtn').innerHTML = '<i class="bi bi-play-circle me-2"></i>Retry';
                document.getElementById('closeUpdateModalBtn').disabled = false;
            });
        }, 1000);
    }, 1500);
}

function updateProgress(percent) {
    const progressBar = document.getElementById('updateProgressBar');
    const progressText = document.getElementById('progressText');
    
    progressBar.style.width = percent + '%';
    progressBar.setAttribute('aria-valuenow', percent);
    progressText.textContent = percent + '%';
}

function addStatusMessage(type, message) {
    const container = document.getElementById('updateStatusMessages');
    const alertClass = {
        'info': 'alert-info',
        'success': 'alert-success',
        'warning': 'alert-warning',
        'danger': 'alert-danger'
    }[type] || 'alert-info';
    
    const icon = {
        'info': 'bi-info-circle',
        'success': 'bi-check-circle',
        'warning': 'bi-exclamation-triangle',
        'danger': 'bi-x-circle'
    }[type] || 'bi-info-circle';
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    messageDiv.innerHTML = `
        <i class="bi ${icon} me-2"></i>${escapeHtml(message)}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(messageDiv);
    
    // Auto scroll to bottom
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

