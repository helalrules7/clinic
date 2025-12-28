<link href="/app/Views/doctor/assets/css/profile.css?v=<?= file_exists(__DIR__ . '/assets/css/profile.css') ? filemtime(__DIR__ . '/assets/css/profile.css') : time() ?>" rel="stylesheet">
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
                    Edit Your Info
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
                                <small class="d-block text-muted mt-1" style="color: var(--text) !important;">Max 5MB - JPEG, PNG, GIF, WebP</small>
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
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-control d-none" id="edit_specialty" name="specialty">
                                            <option value="Ophthalmology" <?= ($user['specialty'] ?? 'Ophthalmology') === 'Ophthalmology' ? 'selected' : '' ?>>Ophthalmology</option>
                                            <option value="Optometry" <?= ($user['specialty'] ?? '') === 'Optometry' ? 'selected' : '' ?>>Optometry</option>
                                            <option value="Retinal Specialist" <?= ($user['specialty'] ?? '') === 'Retinal Specialist' ? 'selected' : '' ?>>Retinal Specialist</option>
                                            <option value="Corneal Specialist" <?= ($user['specialty'] ?? '') === 'Corneal Specialist' ? 'selected' : '' ?>>Corneal Specialist</option>
                                            <option value="Glaucoma Specialist" <?= ($user['specialty'] ?? '') === 'Glaucoma Specialist' ? 'selected' : '' ?>>Glaucoma Specialist</option>
                                            <option value="Pediatric Ophthalmology" <?= ($user['specialty'] ?? '') === 'Pediatric Ophthalmology' ? 'selected' : '' ?>>Pediatric Ophthalmology</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false"><?= htmlspecialchars($user['specialty'] ?? 'Ophthalmology') ?></button>
                                        <menu>
                                            <li data-option="Ophthalmology" tabindex="0" role="button" <?= ($user['specialty'] ?? 'Ophthalmology') === 'Ophthalmology' ? 'class="selected"' : '' ?>><i class="bi-person-badge fs-5"></i><h3>Ophthalmology</h3></li>
                                            <li data-option="Optometry" tabindex="0" role="button" <?= ($user['specialty'] ?? '') === 'Optometry' ? 'class="selected"' : '' ?>><i class="bi-person-badge fs-5"></i><h3>Optometry</h3></li>
                                            <li data-option="Retinal Specialist" tabindex="0" role="button" <?= ($user['specialty'] ?? '') === 'Retinal Specialist' ? 'class="selected"' : '' ?>><i class="bi-person-badge fs-5"></i><h3>Retinal Specialist</h3></li>
                                            <li data-option="Corneal Specialist" tabindex="0" role="button" <?= ($user['specialty'] ?? '') === 'Corneal Specialist' ? 'class="selected"' : '' ?>><i class="bi-person-badge fs-5"></i><h3>Corneal Specialist</h3></li>
                                            <li data-option="Glaucoma Specialist" tabindex="0" role="button" <?= ($user['specialty'] ?? '') === 'Glaucoma Specialist' ? 'class="selected"' : '' ?>><i class="bi-person-badge fs-5"></i><h3>Glaucoma Specialist</h3></li>
                                            <li data-option="Pediatric Ophthalmology" tabindex="0" role="button" <?= ($user['specialty'] ?? '') === 'Pediatric Ophthalmology' ? 'class="selected"' : '' ?>><i class="bi-person-badge fs-5"></i><h3>Pediatric Ophthalmology</h3></li>
                                        </menu>
                                    </div>
                                </section>
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

<script src="/app/Views/doctor/assets/js/profile.js?v=<?= file_exists(__DIR__ . '/assets/js/profile.js') ? filemtime(__DIR__ . '/assets/js/profile.js') : time() ?>"></script>
<style>
    .modal-backdrop.show{
        display: none !important;
    }
    body > div.modal-backdrop.fade.show{
        display: none !important;
    }
    .dark .modal-content{
    background: rgba(11, 18, 32, 0.8) !important;
    }
    .modal-content{
    background: rgba(248, 250, 252, 0.8) !important;
    }
</style>