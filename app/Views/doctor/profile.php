<!-- Profile Header with Image -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm profile-header-card">
            <div class="card-body text-center py-4">
                <div class="profile-image-container mb-3">
                    <?php if (!empty($user['profile_image'])): 
                        $profileImagePath = strpos($user['profile_image'], '/public/') === 0 ? $user['profile_image'] : '/public' . $user['profile_image'];
                    ?>
                        <img src="<?= htmlspecialchars($profileImagePath) ?>" 
                             alt="Profile Picture" 
                             class="profile-image-large"
                             id="profileImageDisplay">
                    <?php else: ?>
                        <div class="profile-image-placeholder-large" id="profileImageDisplay">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h4 class="mb-1 profile-name"><?= htmlspecialchars($user['name']) ?></h4>
                <p class="text-muted mb-0 profile-email">
                    <?= htmlspecialchars($user['email']) ?>
                    <?php if (isset($user['doctor_name'])): ?>
                        <br><span class="badge bg-primary mt-2"><?= htmlspecialchars($user['doctor_name']) ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Profile Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold card-header-title">
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
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold card-header-title">
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
            <form method="POST" action="/doctor/profile/update" id="editProfileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= $this->csrfField() ?>
                    
                    <!-- Profile Image Upload -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="profile-image-upload-container">
                                <div class="profile-image-preview-wrapper">
                                    <?php if (!empty($user['profile_image'])): 
                                        $profileImagePath = strpos($user['profile_image'], '/public/') === 0 ? $user['profile_image'] : '/public' . $user['profile_image'];
                                    ?>
                                        <img src="<?= htmlspecialchars($profileImagePath) ?>" 
                                             alt="Profile Preview" 
                                             class="profile-image-preview"
                                             id="profileImagePreview">
                                    <?php else: ?>
                                        <div class="profile-image-placeholder" id="profileImagePreview">
                                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="profile-image-overlay">
                                        <i class="bi bi-camera"></i>
                                        <span>Change Photo</span>
                                    </div>
                                </div>
                                <input type="file" 
                                       class="form-control d-none" 
                                       id="profile_image" 
                                       name="profile_image" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <label for="profile_image" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="bi bi-upload me-1"></i>
                                    Upload Photo
                                </label>
                                <small class="d-block text-muted mt-1">Max 5MB - JPEG, PNG, GIF, WebP</small>
                            </div>
                        </div>
                    </div>
                    
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
// Profile Image Preview
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit.');
            this.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('profileImagePreview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // Replace placeholder with image
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'profile-image-preview';
                img.id = 'profileImagePreview';
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(file);
    }
});

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
        // Update sidebar user avatar (first letter or image)
        const userAvatar = document.querySelector('.user-avatar');
        if (userAvatar) {
            // Check if profile image was updated
            const profileImageDisplay = document.getElementById('profileImageDisplay');
            if (profileImageDisplay && profileImageDisplay.tagName === 'IMG') {
                // Update sidebar with image
                const sidebarImg = document.createElement('img');
                sidebarImg.src = profileImageDisplay.src;
                sidebarImg.className = 'user-avatar-img';
                sidebarImg.alt = 'Profile';
                userAvatar.innerHTML = '';
                userAvatar.appendChild(sidebarImg);
            } else {
            userAvatar.textContent = updatedName.charAt(0).toUpperCase();
            }
            
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

// Profile image click to upload
document.querySelector('.profile-image-preview-wrapper').addEventListener('click', function() {
    document.getElementById('profile_image').click();
});
</script>

<style>
/* Profile Header Card */
.profile-header-card {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    border: 1px solid var(--border);
    border-radius: 12px;
}

.dark .profile-header-card {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(74, 222, 128, 0.15) 100%);
}

/* Profile Header Text */
.profile-name {
    color: var(--text) !important;
}

.profile-email {
    color: var(--muted) !important;
}

.dark .profile-email {
    color: #94a3b8 !important;
}

/* Card Header Title */
.card-header-title, .form-text {
    color: var(--text) !important;
}

.dark .card-header-title {
    color: var(--text) !important;
}

/* Profile Image Styles */
.profile-image-container {
    position: relative;
    display: inline-block;
}

.profile-image-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--accent);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.profile-image-placeholder-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), #10b981);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: bold;
    border: 4px solid var(--accent);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

/* Profile Image Upload Container */
.profile-image-upload-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-image-preview-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid var(--accent);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.profile-image-preview-wrapper:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.profile-image-preview-wrapper:hover .profile-image-overlay {
    opacity: 1;
}

.profile-image-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--accent), #10b981);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    font-weight: bold;
}

.profile-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 50%;
}

.profile-image-overlay i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.profile-image-overlay span {
    font-size: 0.875rem;
    font-weight: 600;
}

.user-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* Card Styles */
.card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    border-radius: 12px;
    box-shadow: 0 0.15rem 1.75rem 0 var(--shadow) !important;
}

.card-header {
    background-color: transparent !important;
    border-bottom-color: var(--border) !important;
    border-radius: 12px 12px 0 0;
    color: var(--text) !important;
}

.dark .card-header {
    color: var(--text) !important;
}

.card-body {
    background-color: transparent !important;
}

/* Form Styles */
.form-control-plaintext {
    color: var(--text) !important;
    background: transparent;
    border: none;
    padding: 0.375rem 0;
}

.dark .form-control-plaintext {
    color: var(--text) !important;
}

.form-label {
    color: var(--text) !important;
    font-weight: 600;
}

.dark .form-label {
    color: var(--text) !important;
}

.form-control {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.form-control:focus {
    background-color: var(--bg);
    border-color: var(--accent);
    color: var(--text);
    box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
}

/* Password Strength Indicator */
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

.dark .password-strength-bar {
    background-color: var(--border);
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

.dark .password-requirements {
    background: rgba(108, 117, 125, 0.1);
    border-left-color: var(--muted);
}

.requirement {
    display: flex;
    align-items: center;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    color: var(--text);
}

.requirement:last-child {
    margin-bottom: 0;
}

.requirement.text-success {
    color: #198754 !important;
}

.dark .requirement.text-success {
    color: #4ade80 !important;
}

.requirement.text-danger {
    color: #dc3545 !important;
}

.dark .requirement.text-danger {
    color: #fb7185 !important;
}

/* Button Styles */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
}

.btn-outline-primary:hover {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
}

/* Alert Styles */
.alert {
    border-radius: 8px;
    border: none;
}

.alert-info {
    background: rgba(14, 165, 233, 0.1);
    color: var(--accent);
    border-left: 4px solid var(--accent);
}

.dark .alert-info {
    background: rgba(14, 165, 233, 0.15);
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-left: 4px solid #10b981;
}

.dark .alert-success {
    background: rgba(16, 185, 129, 0.15);
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-left: 4px solid #ef4444;
}

.dark .alert-danger {
    background: rgba(239, 68, 68, 0.15);
}

/* Modal Styles - Glass Effect */
.modal-content {
    background: rgba(248, 250, 252, 0.35) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    color: var(--text) !important;
    cursor: move;
}

[data-theme="dark"] .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(226, 232, 240, 0.3) !important;
    border-radius: 12px 12px 0 0;
    color: var(--text) !important;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

[data-theme="dark"] .modal-header {
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
}

[data-theme="dark"] .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

[data-theme="dark"] .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

.modal-body {
    padding: 2rem;
    background: transparent !important;
    color: var(--text) !important;
}

.modal-footer {
    border-top: 1px solid rgba(226, 232, 240, 0.3) !important;
    background: transparent !important;
    border-radius: 0 0 12px 12px;
}

[data-theme="dark"] .modal-footer {
    border-top-color: rgba(51, 65, 85, 0.3) !important;
}

.modal-dialog {
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
}

/* Dark Mode Form Styles */
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

/* Responsive */
@media (max-width: 768px) {
    .profile-image-large,
    .profile-image-placeholder-large {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
}

    .profile-image-preview-wrapper {
        width: 120px;
        height: 120px;
}

    .profile-image-placeholder {
        font-size: 2.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
}
</style>
