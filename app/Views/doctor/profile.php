<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Profile Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-person-circle me-2"></i>
                    Profile Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone</label>
                            <p class="form-control-plaintext">
                                <?= htmlspecialchars($user['phone'] ?? 'Not provided') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-primary"><?= ucfirst($user['role']) ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($user['doctor_name'])): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Display Name</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['doctor_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Specialty</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['specialty'] ?? 'Ophthalmology') ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Login</label>
                            <p class="form-control-plaintext">
                                <?= $user['last_login_at'] ? $this->formatDate($user['last_login_at'], 'd/m/Y H:i') : 'Never' ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Account Status</label>
                            <p class="form-control-plaintext">
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-shield-lock me-2"></i>
                    Change Password
                </h6>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/doctor/profile/change-password" id="changePasswordForm">
                    <?= $this->csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8" required>
                                <div class="form-text">
                                    <small>Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-2"></i>
                                    Change Password
                                </button>
                                <button type="reset" class="btn btn-secondary ms-2">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Information -->
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-info-circle me-2"></i>
                    Security Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-shield-check me-2"></i>
                                Password Requirements
                            </h6>
                            <ul class="mb-0">
                                <li>Minimum 8 characters</li>
                                <li>At least one uppercase letter</li>
                                <li>At least one lowercase letter</li>
                                <li>At least one number</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Important Note
                            </h6>
                            <p class="mb-0">
                                Changing your password will log you out of all other devices and sessions. 
                                You will need to log in again with your new password.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const currentPassword = document.getElementById('current_password').value;
    
    // Basic validation
    if (newPassword.length < 8) {
        e.preventDefault();
        alert('New password must be at least 8 characters long');
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match');
        return false;
    }
    
    if (newPassword === currentPassword) {
        e.preventDefault();
        alert('New password must be different from current password');
        return false;
    }
    
    // Password complexity validation
    const hasUpperCase = /[A-Z]/.test(newPassword);
    const hasLowerCase = /[a-z]/.test(newPassword);
    const hasNumbers = /\d/.test(newPassword);
    
    if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
        e.preventDefault();
        alert('Password must contain uppercase, lowercase, and numbers');
        return false;
    }
    
    // Confirm action
    if (!confirm('Are you sure you want to change your password? This will log you out of all devices.')) {
        e.preventDefault();
        return false;
    }
});

// Password strength indicator
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strength = calculatePasswordStrength(password);
    updatePasswordStrengthIndicator(strength);
});

function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 8) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/[a-z]/.test(password)) score += 1;
    if (/\d/.test(password)) score += 1;
    if (/[^A-Za-z0-9]/.test(password)) score += 1;
    
    return score;
}

function updatePasswordStrengthIndicator(strength) {
    const strengthText = document.getElementById('password_strength');
    if (!strengthText) return;
    
    const strengthLabels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
    
    strengthText.textContent = strengthLabels[strength - 1] || 'Very Weak';
    strengthText.className = `badge bg-${strengthColors[strength - 1] || 'danger'}`;
}
</script>

<style>
.form-control-plaintext {
    color: var(--text);
    background: transparent;
    border: none;
    padding: 0.375rem 0;
}

.alert {
    border-radius: 8px;
    border: none;
}

.alert-info {
    background: rgba(14, 165, 233, 0.1);
    color: var(--accent);
    border-left: 4px solid var(--accent);
}

.alert-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border-left: 4px solid #f59e0b;
}

.form-text {
    color: var(--muted);
    font-size: 0.875rem;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.card {
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.card-header {
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    border-radius: 12px 12px 0 0;
}
</style>
