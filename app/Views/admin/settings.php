<?php
/**
 * Admin Settings Template
 * صفحة إعدادات الإدارة
 */
?>

<style>
:root {
    --bg: #1a1a1a;
    --bg-alt: #2d2d2d;
    --bg-dark: #1e1e1e;
    --text: #ffffff;
    --text-muted: #b0b0b0;
    --accent: #0d6efd;
    --accent-rgb: 13, 110, 253;
    --border: #404040;
    --muted: #6c757d;
}

[data-bs-theme="light"] {
    --bg: #ffffff;
    --bg-alt: #f8f9fa;
    --bg-dark: #ffffff;
    --text: #212529;
    --text-muted: #6c757d;
    --accent: #0d6efd;
    --accent-rgb: 13, 110, 253;
    --border: #dee2e6;
    --muted: #6c757d;
}

.card {
    background: var(--bg-dark);
    border: 1px solid var(--border);
    color: var(--text);
}

.card-header {
    background: var(--bg-alt);
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

.form-control, .form-select {
    background: var(--bg-dark);
    border: 2px solid var(--border);
    color: var(--text);
}

.form-control:focus, .form-select:focus {
    background: var(--bg-dark);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.form-label {
    color: var(--text);
    font-weight: 600;
}

.form-text {
    color: var(--text-muted);
}

.form-check-input:checked {
    background-color: var(--accent);
    border-color: var(--accent);
}

.form-check-label {
    color: var(--text);
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
    background-color: var(--bg-alt);
    border-color: var(--accent);
    color: var(--accent);
}

.text-muted {
    color: var(--text-muted) !important;
}

.settings-section {
    margin-bottom: 2rem;
}

.settings-section h5 {
    color: var(--text);
    border-bottom: 2px solid var(--accent);
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
}

.setting-item {
    margin-bottom: 1.5rem;
}

.setting-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
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

                    <form method="POST" action="/admin/settings">
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
    }
}

// Auto-save functionality (optional)
let autoSaveTimeout;
document.querySelectorAll('input, select').forEach(element => {
    element.addEventListener('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // You could implement auto-save here
            console.log('Settings changed, auto-save could be implemented');
        }, 2000);
    });
});
</script>
