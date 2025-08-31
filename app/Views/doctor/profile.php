<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Profile Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-person-circle me-2"></i>
                    Profile Information
                </h6>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-1"></i>
                    Edit Profile
                </button>
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
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8" required>
                                <div class="password-strength-container mt-2">
                                    <div class="password-strength-bar">
                                        <div class="password-strength-fill" id="password_strength_fill"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="form-text">Password Strength: <span id="password_strength" class="badge bg-secondary">Not entered</span></small>
                                        <small class="form-text text-muted">8+ chars, uppercase, lowercase, numbers</small>
                                    </div>
                                </div>
                                <div class="password-requirements mt-2" id="password_requirements">
                                    <small class="form-text">
                                        <div class="requirement" id="req_length">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            At least 8 characters
                                        </div>
                                        <div class="requirement" id="req_uppercase">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            One uppercase letter
                                        </div>
                                        <div class="requirement" id="req_lowercase">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            One lowercase letter
                                        </div>
                                        <div class="requirement" id="req_number">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            One number
                                        </div>
                                    </small>
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

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="bi bi-person-gear me-2"></i>
                    Edit Profile Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/doctor/profile/update" id="editProfileForm">
                <div class="modal-body">
                    <?= $this->csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                       placeholder="Enter phone number">
                            </div>
                        </div>
                        <?php if (isset($user['doctor_name'])): ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_doctor_name" class="form-label">Display Name</label>
                                <input type="text" class="form-control" id="edit_doctor_name" name="doctor_name" 
                                       value="<?= htmlspecialchars($user['doctor_name']) ?>" 
                                       placeholder="Professional display name">
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($user['specialty'])): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_specialty" class="form-label">Specialty</label>
                                <select class="form-control" id="edit_specialty" name="specialty">
                                    <option value="Ophthalmology" <?= ($user['specialty'] ?? 'Ophthalmology') === 'Ophthalmology' ? 'selected' : '' ?>>Ophthalmology</option>
                                    <option value="Optometry" <?= ($user['specialty'] ?? '') === 'Optometry' ? 'selected' : '' ?>>Optometry</option>
                                    <option value="Retinal Specialist" <?= ($user['specialty'] ?? '') === 'Retinal Specialist' ? 'selected' : '' ?>>Retinal Specialist</option>
                                    <option value="Corneal Specialist" <?= ($user['specialty'] ?? '') === 'Corneal Specialist' ? 'selected' : '' ?>>Corneal Specialist</option>
                                    <option value="Glaucoma Specialist" <?= ($user['specialty'] ?? '') === 'Glaucoma Specialist' ? 'selected' : '' ?>>Glaucoma Specialist</option>
                                    <option value="Pediatric Ophthalmology" <?= ($user['specialty'] ?? '') === 'Pediatric Ophthalmology' ? 'selected' : '' ?>>Pediatric Ophthalmology</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Changes to your profile information will be reflected immediately after saving.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Check if all requirements are met using the live validation
    const requirements = {
        length: newPassword.length >= 8,
        uppercase: /[A-Z]/.test(newPassword),
        lowercase: /[a-z]/.test(newPassword),
        number: /\d/.test(newPassword)
    };
    
    const unmetRequirements = Object.keys(requirements).filter(req => !requirements[req]);
    
    if (unmetRequirements.length > 0) {
        e.preventDefault();
        const reqNames = {
            length: 'at least 8 characters',
            uppercase: 'one uppercase letter',
            lowercase: 'one lowercase letter',
            number: 'one number'
        };
        
        const missingReqs = unmetRequirements.map(req => reqNames[req]).join(', ');
        alert(`Password must contain: ${missingReqs}`);
        
        // Focus on password field and highlight requirements
        document.getElementById('new_password').focus();
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match');
        document.getElementById('confirm_password').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Changing Password...';
    submitBtn.disabled = true;
    
    // Confirm action
    if (!confirm('Are you sure you want to change your password? This will log you out of all devices.')) {
        e.preventDefault();
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return false;
    }
});

// Edit Profile Form Validation
document.getElementById('editProfileForm').addEventListener('submit', function(e) {
    const name = document.getElementById('edit_name').value.trim();
    const email = document.getElementById('edit_email').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Full name is required');
        document.getElementById('edit_name').focus();
        return false;
    }
    
    if (!email) {
        e.preventDefault();
        alert('Email is required');
        document.getElementById('edit_email').focus();
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        document.getElementById('edit_email').focus();
        return false;
    }
    
    // Store the new name for immediate sidebar update
    window.pendingProfileUpdate = {
        name: name,
        email: email,
        phone: document.getElementById('edit_phone').value.trim(),
        doctorName: document.getElementById('edit_doctor_name')?.value.trim() || '',
        specialty: document.getElementById('edit_specialty')?.value || ''
    };
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving Changes...';
    submitBtn.disabled = true;
    
    // Also disable cancel button to prevent accidental clicks
    const cancelBtn = this.querySelector('button[data-bs-dismiss="modal"]');
    cancelBtn.disabled = true;
});

// Password strength indicator - Live validation
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    updatePasswordRequirements(password);
    updatePasswordStrengthIndicator(password);
});

// Also validate confirm password in real-time
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    validatePasswordMatch(newPassword, confirmPassword);
});

function updatePasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password)
    };
    
    // Update each requirement indicator
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById(`req_${req}`);
        const icon = element.querySelector('i');
        
        if (requirements[req]) {
            icon.className = 'bi bi-check-circle text-success me-1';
            element.classList.add('text-success');
            element.classList.remove('text-danger');
        } else {
            icon.className = 'bi bi-x-circle text-danger me-1';
            element.classList.add('text-danger');
            element.classList.remove('text-success');
        }
    });
    
    return requirements;
}

function updatePasswordStrengthIndicator(password) {
    const strengthText = document.getElementById('password_strength');
    const strengthFill = document.getElementById('password_strength_fill');
    
    if (!strengthText || !strengthFill) return;
    
    if (password.length === 0) {
        strengthText.textContent = 'Not entered';
        strengthText.className = 'badge bg-secondary';
        strengthFill.style.width = '0%';
        strengthFill.className = 'password-strength-fill';
        return;
    }
    
    const requirements = updatePasswordRequirements(password);
    const score = Object.values(requirements).filter(Boolean).length;
    
    const strengthData = [
        { label: 'Very Weak', color: 'danger', width: '20%' },
        { label: 'Weak', color: 'warning', width: '40%' },
        { label: 'Fair', color: 'info', width: '60%' },
        { label: 'Good', color: 'success', width: '80%' },
        { label: 'Strong', color: 'success', width: '100%' }
    ];
    
    const currentStrength = strengthData[score - 1] || strengthData[0];
    
    strengthText.textContent = currentStrength.label;
    strengthText.className = `badge bg-${currentStrength.color}`;
    strengthFill.style.width = currentStrength.width;
    strengthFill.className = `password-strength-fill bg-${currentStrength.color}`;
}

function validatePasswordMatch(newPassword, confirmPassword) {
    const confirmInput = document.getElementById('confirm_password');
    
    if (confirmPassword.length === 0) {
        confirmInput.classList.remove('is-valid', 'is-invalid');
        return;
    }
    
    if (newPassword === confirmPassword) {
        confirmInput.classList.add('is-valid');
        confirmInput.classList.remove('is-invalid');
    } else {
        confirmInput.classList.add('is-invalid');
        confirmInput.classList.remove('is-valid');
    }
}

// Check if profile was updated and update sidebar
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('updated') === '1') {
        updateSidebarUserInfo();
        
        // Remove the updated parameter from URL after a short delay
        setTimeout(() => {
            const newUrl = window.location.pathname + '?success=' + encodeURIComponent(urlParams.get('success') || 'Profile updated successfully');
            window.history.replaceState({}, '', newUrl);
        }, 1000);
    }
});

// Function to update sidebar user information
function updateSidebarUserInfo() {
    let updatedName = '';
    
    // Try to get name from pending update first, then from profile display
    if (window.pendingProfileUpdate && window.pendingProfileUpdate.name) {
        updatedName = window.pendingProfileUpdate.name;
    } else {
        // Get updated user name from the profile display
        const nameElements = document.querySelectorAll('.form-control-plaintext');
        if (nameElements.length > 0) {
            updatedName = nameElements[0].textContent.trim();
        }
    }
    
    if (updatedName) {
        // Update sidebar user avatar (first letter)
        const userAvatar = document.querySelector('.user-avatar');
        if (userAvatar) {
            userAvatar.textContent = updatedName.charAt(0).toUpperCase();
            
            // Add animation to avatar
            userAvatar.style.transition = 'all 0.3s ease';
            userAvatar.style.transform = 'scale(1.1)';
            userAvatar.style.boxShadow = '0 0 15px var(--accent)';
            
            setTimeout(() => {
                userAvatar.style.transform = 'scale(1)';
                userAvatar.style.boxShadow = 'none';
            }, 600);
        }
        
        // Update sidebar user name
        const userNameElement = document.querySelector('.user-details h6');
        if (userNameElement) {
            userNameElement.textContent = updatedName;
            
            // Add a subtle animation to indicate update
            userNameElement.style.transition = 'all 0.3s ease';
            userNameElement.style.transform = 'scale(1.05)';
            userNameElement.style.color = 'var(--accent)';
            
            setTimeout(() => {
                userNameElement.style.transform = 'scale(1)';
                userNameElement.style.color = 'var(--text)';
            }, 600);
        }
        
        // Update profile display elements if we have pending data
        if (window.pendingProfileUpdate) {
            updateProfileDisplayElements();
        }
        
        // Show a subtle notification
        showUpdateNotification();
        
        // Clear pending update
        window.pendingProfileUpdate = null;
    }
}

// Function to update profile display elements
function updateProfileDisplayElements() {
    const data = window.pendingProfileUpdate;
    if (!data) return;
    
    // Update name display
    const nameElement = document.querySelector('.form-control-plaintext');
    if (nameElement && data.name) {
        nameElement.textContent = data.name;
    }
    
    // Update email display
    const emailElements = document.querySelectorAll('.form-control-plaintext');
    if (emailElements.length > 1 && data.email) {
        emailElements[1].textContent = data.email;
    }
    
    // Update phone display
    if (emailElements.length > 2 && data.phone) {
        emailElements[2].textContent = data.phone || 'Not provided';
    }
    
    // Update doctor name display if exists
    if (data.doctorName) {
        const doctorNameElements = document.querySelectorAll('.form-control-plaintext');
        if (doctorNameElements.length > 3) {
            doctorNameElements[3].textContent = data.doctorName;
        }
    }
    
    // Update specialty display if exists
    if (data.specialty) {
        const specialtyElements = document.querySelectorAll('.form-control-plaintext');
        if (specialtyElements.length > 4) {
            specialtyElements[4].textContent = data.specialty;
        }
    }
}

// Function to show update notification
function showUpdateNotification() {
    // Create notification element
    const notification = document.createElement('div');
    notification.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            Sidebar updated with new information!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const alertElement = notification.querySelector('.alert');
        if (alertElement) {
            alertElement.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 3000);
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

/* Password Strength Indicator Styles */
.password-strength-container {
    margin-top: 0.5rem;
}

.password-strength-bar {
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    position: relative;
}

.password-strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 3px;
}

.password-requirements {
    background: rgba(108, 117, 125, 0.05);
    border-radius: 6px;
    padding: 0.75rem;
    border-left: 3px solid #6c757d;
}

.requirement {
    display: flex;
    align-items: center;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.requirement:last-child {
    margin-bottom: 0;
}

.requirement.text-success {
    color: #198754 !important;
}

.requirement.text-danger {
    color: #dc3545 !important;
}

.form-control.is-valid {
    border-color: #198754;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.44 1.44L7.4 4.5l.94.94L4.66 9.18z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4M8.2 4.6l-2.4 2.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
    border-color: var(--accent);
}

/* Animate password strength changes */
@keyframes strengthChange {
    0% { transform: scale(0.95); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.password-strength-fill {
    animation: strengthChange 0.3s ease when width changes;
}

/* Modal Styles */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    background: var(--bg);
    color: var(--text);
}

.modal-header {
    background: linear-gradient(135deg, var(--accent), #0ea5e9);
    color: white;
    border-radius: 12px 12px 0 0;
    border-bottom: none;
}

.modal-header .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.modal-header .btn-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 2rem;
    background: var(--bg);
}

.modal-footer {
    border-top: 1px solid var(--border);
    background: var(--bg);
    border-radius: 0 0 12px 12px;
}

/* Dark mode modal styles */
[data-theme="dark"] .modal-content {
    background: var(--bg);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    border: 1px solid var(--border);
}

[data-theme="dark"] .modal-body {
    background: var(--bg);
    color: var(--text);
}

[data-theme="dark"] .modal-footer {
    background: var(--bg);
    border-top: 1px solid var(--border);
}

[data-theme="dark"] .form-control {
    background: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

[data-theme="dark"] .form-control:focus {
    background: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
}

[data-theme="dark"] .form-control::placeholder {
    color: var(--muted);
}

[data-theme="dark"] .form-label {
    color: var(--text);
}

[data-theme="dark"] .alert-info {
    background: rgba(14, 165, 233, 0.15);
    color: var(--accent);
    border-left: 4px solid var(--accent);
}

[data-theme="dark"] .text-danger {
    color: #ff6b6b !important;
}

[data-theme="dark"] .btn-secondary {
    background: var(--muted);
    border-color: var(--border);
    color: var(--text);
}

[data-theme="dark"] .btn-secondary:hover {
    background: var(--border);
    border-color: var(--border);
    color: var(--text);
}

[data-theme="dark"] .btn-primary {
    background: var(--accent);
    border-color: var(--accent);
}

[data-theme="dark"] .btn-primary:hover {
    background: #0284c7;
    border-color: #0284c7;
}

/* Edit button styles */
.btn-outline-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(14, 165, 233, 0.3);
}

[data-theme="dark"] .btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

[data-theme="dark"] .btn-outline-primary:hover {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
    box-shadow: 0 4px 8px rgba(14, 165, 233, 0.4);
}

/* Form enhancements */
.form-label .text-danger {
    font-size: 0.875rem;
}

.form-control:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
}

/* Dark mode select dropdown */
[data-theme="dark"] select.form-control {
    background: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

[data-theme="dark"] select.form-control option {
    background: var(--bg);
    color: var(--text);
}

/* Dark mode modal backdrop */
[data-theme="dark"] .modal-backdrop {
    background-color: rgba(0, 0, 0, 0.7);
}

/* Dark mode password requirements in modal context */
[data-theme="dark"] .password-requirements {
    background: rgba(108, 117, 125, 0.1);
    border-left: 3px solid var(--muted);
}

[data-theme="dark"] .password-strength-bar {
    background-color: var(--border);
}

/* Dark mode form validation states */
[data-theme="dark"] .form-control.is-valid {
    border-color: #20c997;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2320c997' d='m2.3 6.73.94-.94 1.44 1.44L7.4 4.5l.94.94L4.66 9.18z'/%3e%3c/svg%3e");
}

[data-theme="dark"] .form-control.is-invalid {
    border-color: #ff6b6b;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ff6b6b'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4M8.2 4.6l-2.4 2.4'/%3e%3c/svg%3e");
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .password-requirements {
        font-size: 0.8rem;
    }
    
    .requirement {
        margin-bottom: 0.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .card-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .card-header .btn {
        align-self: center;
    }
    
    /* Dark mode responsive adjustments */
    [data-theme="dark"] .modal-dialog {
        margin: 1rem;
    }
    
    [data-theme="dark"] .modal-content {
        border: 1px solid var(--border);
    }
}
</style>
