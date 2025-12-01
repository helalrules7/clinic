
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

// =========================================
// Custom Select Menu Logic
// =========================================

// Custom Select Menu Logic
function initCustomSelects() {
    const customSelects = document.querySelectorAll('.field.menu:not([data-initialized])');

    customSelects.forEach(field => {
        const select = field.querySelector('select');
        const button = field.querySelector('.custom-select-toggle');
        const menu = field.querySelector('menu');
        const options = menu ? menu.querySelectorAll('li') : [];

        if (!select || !button || !menu || options.length === 0) {
            console.warn('Missing elements for custom select initialization:', field);
            return;
        }
        
        // Mark as initialized to prevent duplicate event listeners
        field.setAttribute('data-initialized', 'true');

        // Set initial button text
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            const correspondingLi = Array.from(options).find(li => li.dataset.option === selectedOption.value);
            if (correspondingLi) {
                button.textContent = correspondingLi.querySelector('h3')?.textContent || selectedOption.textContent;
                correspondingLi.classList.add('selected');
            } else {
                button.textContent = selectedOption.textContent;
            }
        } else {
            button.textContent = 'Select an option';
        }

        function openMenu() {
            // Close any other open menus first
            document.querySelectorAll('.field.menu.open').forEach(openField => {
                if (openField !== field) {
                    const openButton = openField.querySelector('.custom-select-toggle');
                    openField.classList.remove('open');
                    if (openButton) openButton.setAttribute('aria-expanded', 'false');
                    const openParent = openField.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
                    if (openParent && !openParent.classList.contains('modal')) {
                        openParent.style.zIndex = '';
                        openParent.style.position = '';
                    } else {
                        const openModal = openField.closest('.modal');
                        if (openModal) {
                            openModal.style.zIndex = '';
                        }
                    }
                }
            });

            field.classList.add('open');
            button.setAttribute('aria-expanded', 'true');

            // Fix z-index issue by elevating parent containers manually
            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                parent.style.zIndex = '1000002';
                parent.style.position = 'relative';
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    modal.style.zIndex = '1000002';
                }
            }

            const selected = menu.querySelector('.selected') || options[0];
            if (selected) {
                selected.focus();
                
                // Scroll to selected item if menu has many options
                setTimeout(() => {
                    selected.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 150);
            }
        }

        function closeMenu() {
            field.classList.remove('open');
            button.setAttribute('aria-expanded', 'false');
            if (document.activeElement === document.body || document.activeElement === null) {
                button.focus();
            }

            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                setTimeout(() => {
                    if (!field.classList.contains('open')) {
                        parent.style.zIndex = '';
                        parent.style.position = '';
                    }
                }, 300);
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    setTimeout(() => {
                        if (!field.classList.contains('open')) {
                            modal.style.zIndex = '';
                        }
                    }, 300);
                }
            }
        }

        function setOption(optionEl) {
            const value = optionEl.dataset.option;
            const text = optionEl.querySelector('h3')?.textContent || optionEl.textContent;

            select.value = value;
            select.dispatchEvent(new Event('change'));

            button.textContent = text;

            options.forEach(el => el.classList.remove('selected'));
            optionEl.classList.add('selected');

            closeMenu();
        }

        button.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            if (field.classList.contains('open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        button.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openMenu();
            }
        });

        // Prevent clicks on menu from closing modal
        menu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                setOption(option);
            });

            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setOption(option);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = option.nextElementSibling;
                    if (next) next.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = option.previousElementSibling;
                    if (prev) prev.focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeMenu();
                }
            });
        });

        // Close menu when clicking outside, but prevent modal from closing
        const handleOutsideClick = (e) => {
            const target = e.target;
            const isInteractiveElement = target.tagName === 'INPUT' || 
                                        target.tagName === 'TEXTAREA' || 
                                        target.tagName === 'SELECT' ||
                                        target.isContentEditable ||
                                        target.closest('input, textarea, select, [contenteditable]');
            
            if (isInteractiveElement) {
                return;
            }
            
            if (field.classList.contains('open') && !field.contains(target)) {
                const modal = field.closest('.modal');
                if (modal && target === modal) {
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                closeMenu();
            }
        };
        
        // Store handler for cleanup
        field._outsideClickHandler = handleOutsideClick;
        document.addEventListener('click', handleOutsideClick, false);
    });
}

// Initialize on DOM ready (after existing DOMContentLoaded)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});

// Also initialize when modals are shown
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});