/**
 * Secretary profile — avatar upload, password strength, sidebar sync (Arabic).
 */
(function () {
    'use strict';

    var DEPT_LABELS = {
        Administration: 'الإدارة',
        Reception: 'الاستقبال',
        Appointments: 'الحجوزات',
        Billing: 'الفواتير',
        Records: 'السجلات',
        Support: 'الدعم الفني'
    };

    var STRENGTH_LABELS = ['ضعيفة جداً', 'ضعيفة', 'متوسطة', 'جيدة', 'قوية'];

    function $(id) { return document.getElementById(id); }

    // —— Profile image ——
    var fileInput = $('profile_image');
    var clickTarget = document.getElementById('profileImageClickTarget');

    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الملف يتجاوز 5 ميجابايت.');
                fileInput.value = '';
                return;
            }
            var allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (allowed.indexOf(file.type) === -1) {
                alert('نوع الملف غير مدعوم. المسموح: JPEG, PNG, GIF, WebP.');
                fileInput.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function (ev) {
                var preview = $('profileImagePreview');
                if (!preview) return;
                if (preview.tagName === 'IMG') {
                    preview.src = ev.target.result;
                } else {
                    var img = document.createElement('img');
                    img.src = ev.target.result;
                    img.className = 'profile-image-preview';
                    img.id = 'profileImagePreview';
                    img.alt = 'معاينة الصورة';
                    preview.parentNode.replaceChild(img, preview);
                }
            };
            reader.readAsDataURL(file);
        });
    }

    if (clickTarget && fileInput) {
        clickTarget.addEventListener('click', function () { fileInput.click(); });
    }

    // —— Password ——
    function updatePasswordRequirements(password) {
        var requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password)
        };
        Object.keys(requirements).forEach(function (req) {
            var el = $('req_' + req);
            if (!el) return;
            var icon = el.querySelector('i');
            if (requirements[req]) {
                icon.className = 'bi bi-check-circle text-success me-1';
                el.classList.add('text-success');
                el.classList.remove('text-danger');
            } else {
                icon.className = 'bi bi-x-circle text-danger me-1';
                el.classList.add('text-danger');
                el.classList.remove('text-success');
            }
        });
        return requirements;
    }

    function updatePasswordStrengthIndicator(password) {
        var strengthText = $('password_strength');
        var strengthFill = $('password_strength_fill');
        if (!strengthText || !strengthFill) return;

        if (!password.length) {
            strengthText.textContent = 'لم تُدخل بعد';
            strengthText.className = 'badge bg-secondary';
            strengthFill.style.width = '0%';
            strengthFill.className = 'password-strength-fill';
            return;
        }

        var reqs = updatePasswordRequirements(password);
        var score = Object.keys(reqs).filter(function (k) { return reqs[k]; }).length;
        var colors = ['danger', 'warning', 'info', 'success', 'success'];
        var widths = ['20%', '40%', '60%', '80%', '100%'];
        var idx = Math.max(0, Math.min(4, score - 1));

        strengthText.textContent = STRENGTH_LABELS[idx];
        strengthText.className = 'badge bg-' + colors[idx];
        strengthFill.style.width = widths[idx];
        strengthFill.className = 'password-strength-fill bg-' + colors[idx];
    }

    function validatePasswordMatch(newPassword, confirmPassword) {
        var confirmInput = $('confirm_password');
        if (!confirmInput) return;
        if (!confirmPassword.length) {
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

    var newPw = $('new_password');
    var confirmPw = $('confirm_password');
    if (newPw) {
        newPw.addEventListener('input', function () {
            updatePasswordStrengthIndicator(this.value);
            if (confirmPw) validatePasswordMatch(this.value, confirmPw.value);
        });
    }
    if (confirmPw && newPw) {
        confirmPw.addEventListener('input', function () {
            validatePasswordMatch(newPw.value, this.value);
        });
    }

    var changeForm = $('changePasswordForm');
    if (changeForm) {
        changeForm.addEventListener('submit', function (e) {
            var password = newPw ? newPw.value : '';
            var confirm = confirmPw ? confirmPw.value : '';
            var reqs = updatePasswordRequirements(password);
            var missing = Object.keys(reqs).filter(function (k) { return !reqs[k]; });
            if (missing.length) {
                e.preventDefault();
                alert('كلمة المرور لا تستوفي المتطلبات المطلوبة.');
                if (newPw) newPw.focus();
                return false;
            }
            if (password !== confirm) {
                e.preventDefault();
                alert('كلمتا المرور غير متطابقتين.');
                if (confirmPw) confirmPw.focus();
                return false;
            }
            if (!confirm('هل أنت متأكد من تغيير كلمة المرور؟ سيتم تسجيل خروجك من الأجهزة الأخرى.')) {
                e.preventDefault();
                return false;
            }
            var btn = changeForm.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري التغيير…';
            }
        });
    }

    // —— Edit profile ——
    var editForm = $('editProfileForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            var name = ($('edit_name') || {}).value.trim();
            var email = ($('edit_email') || {}).value.trim();
            if (!name) {
                e.preventDefault();
                alert('الاسم الكامل مطلوب.');
                $('edit_name').focus();
                return false;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('يرجى إدخال بريد إلكتروني صالح.');
                $('edit_email').focus();
                return false;
            }
            var deptSelect = $('edit_department');
            window.pendingProfileUpdate = {
                name: name,
                email: email,
                phone: ($('edit_phone') || {}).value.trim(),
                secretaryName: ($('edit_secretary_name') || {}).value.trim(),
                department: deptSelect ? deptSelect.value : '',
                departmentLabel: deptSelect ? (DEPT_LABELS[deptSelect.value] || deptSelect.value) : ''
            };
            var submitBtn = editForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري الحفظ…';
            }
        });
    }

    function updateSidebarUserInfo() {
        var name = '';
        if (window.pendingProfileUpdate && window.pendingProfileUpdate.name) {
            name = window.pendingProfileUpdate.name;
        } else {
            var el = document.querySelector('[data-field="name"]');
            if (el) name = el.textContent.trim();
        }
        if (!name) return;

        var avatar = document.getElementById('sidebarUserAvatar');
        if (avatar) {
            var heroImg = $('profileImageDisplay');
            if (heroImg && heroImg.tagName === 'IMG') {
                avatar.innerHTML = '<img src="' + heroImg.src + '" class="user-avatar-img" alt="الصورة الشخصية">';
            } else {
                avatar.textContent = name.charAt(0).toUpperCase();
            }
            avatar.style.transition = 'transform .3s ease';
            avatar.style.transform = 'scale(1.08)';
            setTimeout(function () { avatar.style.transform = ''; }, 500);
        }

        var nameEl = document.querySelector('.user-details h6');
        if (nameEl) nameEl.textContent = name;

        if (window.pendingProfileUpdate) updateProfileDisplayFields();
        showToast('تم تحديث الملف الشخصي في القائمة الجانبية');
        window.pendingProfileUpdate = null;
    }

    function updateProfileDisplayFields() {
        var d = window.pendingProfileUpdate;
        if (!d) return;
        var map = {
            name: d.name,
            email: d.email,
            phone: d.phone || 'غير محدد',
            secretary_name: d.secretaryName || '—',
            department: d.departmentLabel || '—'
        };
        Object.keys(map).forEach(function (key) {
            var el = document.querySelector('[data-field="' + key + '"]');
            if (el) el.textContent = map[key];
        });
        var heroName = document.querySelector('.profile-hero .profile-name');
        if (heroName && d.name) heroName.textContent = d.name;
        var heroEmail = document.querySelector('.profile-hero .profile-email');
        if (heroEmail && d.email) {
            heroEmail.innerHTML = '<i class="bi bi-envelope me-1"></i>' + d.email;
        }
    }

    function showToast(msg) {
        var wrap = document.createElement('div');
        wrap.innerHTML =
            '<div class="alert alert-success alert-dismissible fade show position-fixed arabic-text" ' +
            'style="top:20px;left:20px;z-index:1000010;min-width:280px;" dir="rtl">' +
            '<i class="bi bi-check-circle me-2"></i>' + msg +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        document.body.appendChild(wrap);
        setTimeout(function () {
            var a = wrap.querySelector('.alert');
            if (a) a.remove();
            wrap.remove();
        }, 3500);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var params = new URLSearchParams(window.location.search);
        if (params.get('updated') === '1') {
            updateSidebarUserInfo();
            setTimeout(function () {
                var success = params.get('success') || 'تم تحديث الملف بنجاح';
                window.history.replaceState({}, '', window.location.pathname + '?success=' + encodeURIComponent(success));
            }, 800);
        }
    });
})();
