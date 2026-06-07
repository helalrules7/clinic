<?php
/**
 * Secretary Profile — parity with doctor/profile.php (hero, avatar, theme-aware cards).
 */
$deptLabels = [
    'Administration' => 'الإدارة',
    'Reception'     => 'الاستقبال',
    'Appointments'  => 'الحجوزات',
    'Billing'       => 'الفواتير',
    'Records'       => 'السجلات',
    'Support'       => 'الدعم الفني',
];
$deptKey = $user['department'] ?? 'Administration';
$deptDisplay = $deptLabels[$deptKey] ?? htmlspecialchars($deptKey);

$profileImagePath = null;
if (!empty($user['profile_image'])) {
    $profileImagePath = strpos($user['profile_image'], '/public/') === 0
        ? $user['profile_image']
        : '/public' . $user['profile_image'];
}
$initial = strtoupper(mb_substr($user['name'] ?? 'س', 0, 1, 'UTF-8'));
?>
<link href="/app/Views/doctor/assets/css/profile.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/profile.css') ? filemtime(__DIR__ . '/../doctor/assets/css/profile.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/profile.css?v=<?= file_exists(__DIR__ . '/assets/css/profile.css') ? filemtime(__DIR__ . '/assets/css/profile.css') : time() ?>" rel="stylesheet">

<div class="sec-profile-page" dir="rtl">
    <!-- Hero -->
    <div class="profile-hero mb-4">
        <div class="profile-hero-banner"></div>
        <div class="profile-hero-content">
            <div class="profile-avatar-ring">
                <?php if ($profileImagePath): ?>
                    <img src="<?= htmlspecialchars($profileImagePath) ?>"
                         alt="الصورة الشخصية"
                         class="profile-image-large"
                         id="profileImageDisplay">
                <?php else: ?>
                    <div class="profile-image-placeholder-large" id="profileImageDisplay"><?= $initial ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-hero-text">
                <h4 class="profile-name mb-1 arabic-text"><?= htmlspecialchars($user['name']) ?></h4>
                <p class="profile-email mb-2 arabic-text">
                    <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']) ?>
                </p>
                <div class="profile-chips">
                    <span class="profile-chip profile-chip--role">
                        <i class="bi bi-person-badge"></i>سكرتير
                    </span>
                    <?php if (!empty($user['secretary_name'])): ?>
                        <span class="profile-chip">
                            <i class="bi bi-card-text"></i><?= htmlspecialchars($user['secretary_name']) ?>
                        </span>
                    <?php endif; ?>
                    <span class="profile-chip">
                        <i class="bi bi-building"></i><?= $deptDisplay ?>
                    </span>
                    <span class="profile-chip <?= !empty($user['is_active']) ? 'is-active' : 'is-inactive' ?>">
                        <i class="bi <?= !empty($user['is_active']) ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
                        <?= !empty($user['is_active']) ? 'نشط' : 'غير نشط' ?>
                    </span>
                </div>
            </div>
            <button type="button" class="btn profile-edit-btn arabic-text" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="bi bi-pencil-square me-1"></i>تعديل الملف
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Profile Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="m-0 font-weight-bold card-header-title arabic-text">
                        <i class="bi bi-person-circle me-2"></i>معلومات الملف الشخصي
                    </h6>
                    <button type="button" class="btn btn-outline-primary btn-sm arabic-text" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square me-1"></i>تعديل البيانات
                    </button>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['success']) && !isset($_GET['error'])): ?>
                        <div class="alert alert-success arabic-text">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger arabic-text">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">الاسم الكامل</label>
                                <p class="form-control-plaintext arabic-text" data-field="name"><?= htmlspecialchars($user['name']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">البريد الإلكتروني</label>
                                <p class="form-control-plaintext" data-field="email"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">رقم الهاتف</label>
                                <p class="form-control-plaintext arabic-text" data-field="phone"><?= htmlspecialchars($user['phone'] ?? 'غير محدد') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">الدور</label>
                                <p class="form-control-plaintext"><span class="badge rounded-pill" style="background:color-mix(in srgb,var(--accent) 18%,transparent);color:var(--accent);">سكرتير</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">الاسم المعروض</label>
                                <p class="form-control-plaintext arabic-text" data-field="secretary_name"><?= htmlspecialchars($user['secretary_name'] ?? '—') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">القسم</label>
                                <p class="form-control-plaintext arabic-text" data-field="department"><?= $deptDisplay ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">آخر تسجيل دخول</label>
                                <p class="form-control-plaintext arabic-text">
                                    <?= !empty($user['last_login_at']) ? $this->formatDate($user['last_login_at'], 'd/m/Y H:i') : 'لم يسجل دخول من قبل' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold arabic-text">حالة الحساب</label>
                                <p class="form-control-plaintext">
                                    <?php if (!empty($user['is_active'])): ?>
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
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold card-header-title arabic-text">
                        <i class="bi bi-shield-lock me-2"></i>تغيير كلمة المرور
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/secretary/profile/change-password" id="changePasswordForm">
                        <?= $this->csrfField() ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label arabic-text">كلمة المرور الجديدة</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label arabic-text">تأكيد كلمة المرور</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3 password-validation-block">
                                    <div class="password-strength-container">
                                        <div class="password-strength-bar">
                                            <div class="password-strength-fill" id="password_strength_fill"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1 flex-wrap gap-1">
                                            <small class="form-text arabic-text">قوة كلمة المرور: <span id="password_strength" class="badge bg-secondary">لم تُدخل بعد</span></small>
                                            <small class="form-text text-muted arabic-text">8+ أحرف، حرف كبير، حرف صغير، أرقام</small>
                                        </div>
                                    </div>
                                    <div class="password-requirements mt-2" id="password_requirements">
                                        <small class="form-text">
                                            <div class="password-requirements-grid">
                                                <div class="requirement" id="req_length"><i class="bi bi-x-circle text-danger me-1"></i>8 أحرف على الأقل</div>
                                                <div class="requirement" id="req_uppercase"><i class="bi bi-x-circle text-danger me-1"></i>حرف كبير واحد</div>
                                                <div class="requirement" id="req_lowercase"><i class="bi bi-x-circle text-danger me-1"></i>حرف صغير واحد</div>
                                                <div class="requirement" id="req_number"><i class="bi bi-x-circle text-danger me-1"></i>رقم واحد على الأقل</div>
                                            </div>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary arabic-text">
                                <i class="bi bi-shield-check me-2"></i>تغيير كلمة المرور
                            </button>
                            <button type="reset" class="btn btn-secondary arabic-text">
                                <i class="bi bi-arrow-clockwise me-2"></i>إعادة تعيين
                            </button>
                        </div>
                        <p class="form-text text-muted mt-3 mb-0 arabic-text">
                            <i class="bi bi-info-circle me-1"></i>تغيير كلمة المرور يسجّل خروجك من الجلسات الأخرى.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title arabic-text" id="editProfileModalLabel">
                    <i class="bi bi-person-gear me-2"></i>تعديل الملف الشخصي
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="/secretary/profile/update" id="editProfileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= $this->csrfField() ?>

                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="profile-image-upload-container">
                                <div class="profile-image-preview-wrapper" id="profileImageClickTarget">
                                    <?php if ($profileImagePath): ?>
                                        <img src="<?= htmlspecialchars($profileImagePath) ?>"
                                             alt="معاينة الصورة"
                                             class="profile-image-preview"
                                             id="profileImagePreview">
                                    <?php else: ?>
                                        <div class="profile-image-placeholder" id="profileImagePreview"><?= $initial ?></div>
                                    <?php endif; ?>
                                    <div class="profile-image-overlay">
                                        <i class="bi bi-camera"></i>
                                        <span class="arabic-text">تغيير الصورة</span>
                                    </div>
                                </div>
                                <input type="file" class="d-none" id="profile_image" name="profile_image"
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <label for="profile_image" class="btn btn-outline-primary btn-sm mt-2 arabic-text">
                                    <i class="bi bi-upload me-1"></i>رفع صورة
                                </label>
                                <small class="d-block text-muted mt-1 arabic-text">الحد الأقصى 5 ميجابايت — JPEG, PNG, GIF, WebP</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label arabic-text">الاسم الكامل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name"
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label arabic-text">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label arabic-text">رقم الهاتف</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       placeholder="أدخل رقم الهاتف">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_secretary_name" class="form-label arabic-text">الاسم المعروض</label>
                                <input type="text" class="form-control" id="edit_secretary_name" name="secretary_name"
                                       value="<?= htmlspecialchars($user['secretary_name'] ?? '') ?>"
                                       placeholder="الاسم المهني المعروض">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_department" class="form-label arabic-text">القسم</label>
                                <select class="form-select" id="edit_department" name="department">
                                    <?php foreach ($deptLabels as $val => $label): ?>
                                        <option value="<?= htmlspecialchars($val) ?>" <?= $deptKey === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info arabic-text mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>ملاحظة:</strong> التغييرات تظهر فور الحفظ في الملف والقائمة الجانبية.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary arabic-text" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary arabic-text">
                        <i class="bi bi-check-circle me-1"></i>حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/app/Views/secretary/assets/js/profile.js?v=<?= file_exists(__DIR__ . '/assets/js/profile.js') ? filemtime(__DIR__ . '/assets/js/profile.js') : time() ?>"></script>
