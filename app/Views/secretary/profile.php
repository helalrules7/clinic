<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Profile Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-person-circle me-2"></i>
                    معلومات الملف الشخصي
                </h6>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-1"></i>
                    تعديل الملف الشخصي
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">الاسم الكامل</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">رقم الهاتف</label>
                            <p class="form-control-plaintext">
                                <?= htmlspecialchars($user['phone'] ?? 'غير محدد') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">الدور</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-primary"><?= ucfirst($user['role']) ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($user['secretary_name'])): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">الاسم المعروض</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['secretary_name']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">القسم</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($user['department'] ?? 'الإدارة') ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">آخر تسجيل دخول</label>
                            <p class="form-control-plaintext">
                                <?= $user['last_login_at'] ? $this->formatDate($user['last_login_at'], 'd/m/Y H:i') : 'لم يسجل دخول من قبل' ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">حالة الحساب</label>
                            <p class="form-control-plaintext">
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">نشط</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">غير نشط</span>
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
                    تغيير كلمة المرور
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
                
                <form method="POST" action="/secretary/profile/change-password" id="changePasswordForm">
                    <?= $this->csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8" required>
                                <div class="password-strength-container mt-2">
                                    <div class="password-strength-bar">
                                        <div class="password-strength-fill" id="password_strength_fill"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="form-text">قوة كلمة المرور: <span id="password_strength" class="badge bg-secondary">لم يتم إدخالها</span></small>
                                        <small class="form-text text-muted">8+ أحرف، حرف كبير، حرف صغير، أرقام</small>
                                    </div>
                                </div>
                                <div class="password-requirements mt-2" id="password_requirements">
                                    <small class="form-text">
                                        <div class="requirement" id="req_length">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            على الأقل 8 أحرف
                                        </div>
                                        <div class="requirement" id="req_uppercase">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            حرف كبير واحد على الأقل
                                        </div>
                                        <div class="requirement" id="req_lowercase">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            حرف صغير واحد على الأقل
                                        </div>
                                        <div class="requirement" id="req_number">
                                            <i class="bi bi-x-circle text-danger me-1"></i>
                                            رقم واحد على الأقل
                                        </div>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-2"></i>
                                    تغيير كلمة المرور
                                </button>
                                <button type="reset" class="btn btn-secondary ms-2">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    إعادة تعيين
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
                    معلومات الأمان
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-shield-check me-2"></i>
                                متطلبات كلمة المرور
                            </h6>
                            <ul class="mb-0">
                                <li>8 أحرف على الأقل</li>
                                <li>حرف كبير واحد على الأقل</li>
                                <li>حرف صغير واحد على الأقل</li>
                                <li>رقم واحد على الأقل</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                ملاحظة مهمة
                            </h6>
                            <p class="mb-0">
                                تغيير كلمة المرور سيقوم بتسجيل خروجك من جميع الأجهزة والجلسات الأخرى. 
                                ستحتاج إلى تسجيل الدخول مرة أخرى بكلمة المرور الجديدة.
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
                    تعديل معلومات الملف الشخصي
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/secretary/profile/update" id="editProfileForm">
                <div class="modal-body">
                    <?= $this->csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                       placeholder="أدخل رقم الهاتف">
                            </div>
                        </div>
                        <?php if (isset($user['secretary_name'])): ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_secretary_name" class="form-label">الاسم المعروض</label>
                                <input type="text" class="form-control" id="edit_secretary_name" name="secretary_name" 
                                       value="<?= htmlspecialchars($user['secretary_name']) ?>" 
                                       placeholder="الاسم المهني المعروض">
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($user['department'])): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_department" class="form-label">القسم</label>
                                <select class="form-control" id="edit_department" name="department">
                                    <option value="Administration" <?= ($user['department'] ?? 'Administration') === 'Administration' ? 'selected' : '' ?>>الإدارة</option>
                                    <option value="Reception" <?= ($user['department'] ?? '') === 'Reception' ? 'selected' : '' ?>>الاستقبال</option>
                                    <option value="Appointments" <?= ($user['department'] ?? '') === 'Appointments' ? 'selected' : '' ?>>الحجوزات</option>
                                    <option value="Billing" <?= ($user['department'] ?? '') === 'Billing' ? 'selected' : '' ?>>الفواتير</option>
                                    <option value="Records" <?= ($user['department'] ?? '') === 'Records' ? 'selected' : '' ?>>السجلات</option>
                                    <option value="Support" <?= ($user['department'] ?? '') === 'Support' ? 'selected' : '' ?>>الدعم الفني</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>ملاحظة:</strong> التغييرات في معلومات ملفك الشخصي ستظهر فوراً بعد الحفظ.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        حفظ التغييرات
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
            length: '8 أحرف على الأقل',
            uppercase: 'حرف كبير واحد على الأقل',
            lowercase: 'حرف صغير واحد على الأقل',
            number: 'رقم واحد على الأقل'
        };
        
        const missingReqs = unmetRequirements.map(req => reqNames[req]).join(', ');
        alert(`كلمة المرور يجب أن تحتوي على: ${missingReqs}`);
        
        // Focus on password field and highlight requirements
        document.getElementById('new_password').focus();
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('كلمات المرور الجديدة غير متطابقة');
        document.getElementById('confirm_password').focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري تغيير كلمة المرور...';
    submitBtn.disabled = true;
    
    // Confirm action
    if (!confirm('هل أنت متأكد من تغيير كلمة المرور؟ سيتم تسجيل خروجك من جميع الأجهزة.')) {
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
        alert('الاسم الكامل مطلوب');
        document.getElementById('edit_name').focus();
        return false;
    }
    
    if (!email) {
        e.preventDefault();
        alert('البريد الإلكتروني مطلوب');
        document.getElementById('edit_email').focus();
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('يرجى إدخال عنوان بريد إلكتروني صحيح');
        document.getElementById('edit_email').focus();
        return false;
    }
    
    // Store the new name for immediate sidebar update
    window.pendingProfileUpdate = {
        name: name,
        email: email,
        phone: document.getElementById('edit_phone').value.trim(),
        secretaryName: document.getElementById('edit_secretary_name')?.value.trim() || '',
        department: document.getElementById('edit_department')?.value || ''
    };
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري حفظ التغييرات...';
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
        strengthText.textContent = 'لم يتم إدخالها';
        strengthText.className = 'badge bg-secondary';
        strengthFill.style.width = '0%';
        strengthFill.className = 'password-strength-fill';
        return;
    }
    
    const requirements = updatePasswordRequirements(password);
    const score = Object.values(requirements).filter(Boolean).length;
    
    const strengthData = [
        { label: 'ضعيف جداً', color: 'danger', width: '20%' },
        { label: 'ضعيف', color: 'warning', width: '40%' },
        { label: 'متوسط', color: 'info', width: '60%' },
        { label: 'جيد', color: 'success', width: '80%' },
        { label: 'قوي', color: 'success', width: '100%' }
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
    
    // Update secretary name display if exists
    if (data.secretaryName) {
        const secretaryNameElements = document.querySelectorAll('.form-control-plaintext');
        if (secretaryNameElements.length > 3) {
            secretaryNameElements[3].textContent = data.secretaryName;
        }
    }
    
    // Update department display if exists
    if (data.department) {
        const departmentElements = document.querySelectorAll('.form-control-plaintext');
        if (departmentElements.length > 4) {
            departmentElements[4].textContent = data.department;
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
                تم تحديث الشريط الجانبي بالمعلومات الجديدة!
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

/* RTL specific adjustments */
.me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
.me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
.ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
.ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
.text-start { text-align: right !important; }
.text-end { text-align: left !important; }
.justify-content-start { justify-content: flex-end !important; }
.justify-content-end { justify-content: flex-start !important; }

/* Arabic text styling */
.arabic-text {
    font-family: 'Cairo', Arial, sans-serif;
    direction: rtl;
    text-align: right;
}

/* Secretary specific styles */
.stat-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.stat-label {
    margin: 0;
    color: var(--muted);
    font-size: 0.875rem;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

/* Gender-based avatar colors */
.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.avatar-male:hover {
    background: #2980b9;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.avatar-female:hover {
    background: #c2185b;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: var(--text);
    background: var(--bg);
}

.table td {
    vertical-align: middle;
    border-top: 1px solid var(--border);
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-left: 1px solid var(--border);
}

/* Search Modal Styles */
.modal-content {
    background-color: var(--bg);
    border-color: var(--border);
    color: var(--text);
}

.modal-header {
    background-color: var(--bg-alt);
    border-bottom-color: var(--border);
    color: var(--text);
}

.modal-footer {
    background-color: var(--bg-alt);
    border-top-color: var(--border);
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
    box-shadow: 0 0 0 0.2rem rgba(var(--accent-rgb), 0.25);
}

.input-group-text {
    background-color: var(--bg-alt);
    border-color: var(--border);
    color: var(--text);
}

.search-results-container {
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background: var(--bg);
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-result-item:hover {
    border-color: var(--accent);
    background: var(--bg-alt);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-result-item:last-child {
    margin-bottom: 0;
}

.search-result-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.search-result-avatar.avatar-male {
    background: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.search-result-avatar.avatar-female {
    background: #e91e63;
    box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
}

.search-result-info h6 {
    margin-bottom: 5px;
    color: var(--text);
}

.search-result-info .text-muted {
    font-size: 0.9rem;
    color: var(--muted) !important;
}

.search-result-actions .btn {
    padding: 5px 10px;
    font-size: 0.85rem;
}

.search-highlight {
    background-color: rgba(255, 193, 7, 0.3);
    padding: 1px 3px;
    border-radius: 3px;
    font-weight: 600;
}

/* Keyboard shortcut styling */
kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-family: 'Courier New', 'Cairo', monospace;
    color: var(--text);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    min-width: 20px;
    text-align: center;
    display: inline-block;
}

.btn-primary kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

.btn-success kbd {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.25);
    color: rgba(255, 255, 255, 0.9);
}

/* Arabic keyboard shortcut styling */
kbd[lang="ar"] {
    font-family: 'Cairo', 'Courier New', monospace;
    font-weight: 600;
}

/* Keyboard shortcut hint in modal */
.keyboard-hint {
    position: absolute;
    top: 10px;
    left: 15px;
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.keyboard-hint kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.65rem;
    padding: 1px 4px;
}

/* Badge styling for dark mode */
.badge.bg-primary {
    background-color: var(--accent) !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: var(--muted) !important;
    color: white;
}

/* Text muted styling */
.text-muted {
    color: var(--muted) !important;
}

/* Search help text styling for dark mode */
.search-help-text {
    background: rgba(var(--accent-rgb), 0.05);
    border: 1px solid rgba(var(--accent-rgb), 0.15);
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 8px;
    transition: all 0.2s ease;
}

.search-help-text:hover {
    background: rgba(var(--accent-rgb), 0.08);
    border-color: rgba(var(--accent-rgb), 0.2);
}

.search-help-text .search-instruction {
    color: var(--text);
    font-weight: 500;
    font-size: 0.875rem;
}

.search-help-text .search-instruction i {
    color: var(--accent);
    opacity: 0.8;
    margin-right: 4px;
}

.search-help-text .search-shortcut {
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 400;
}

.search-help-text kbd {
    background-color: var(--bg-alt);
    border: 1px solid var(--border);
    color: var(--text);
    font-size: 0.7rem;
    padding: 2px 6px;
    margin: 0 1px;
    border-radius: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Courier New', 'Cairo', monospace;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .keyboard-hint {
        position: static;
        margin-top: 10px;
    }
}
</style>
