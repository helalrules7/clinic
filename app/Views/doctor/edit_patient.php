<!-- Edit Patient Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-gear me-2"></i>
                    Edit Patient Information
                </h5>
            </div>
            <div class="card-body">
                <form id="editPatientForm" method="POST" action="/doctor/patients/<?= $patient['id'] ?>">
                    <input type="hidden" name="_method" value="PUT">
                    
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($patient['first_name']) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($patient['last_name']) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($patient['phone']) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="alt_phone" class="form-label">Alternative Phone</label>
                            <input type="tel" class="form-control" id="alt_phone" name="alt_phone" 
                                   value="<?= htmlspecialchars($patient['alt_phone'] ?? '') ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Personal Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob" 
                                   value="<?= $patient['dob'] ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="national_id" class="form-label">National ID</label>
                            <input type="text" class="form-control" id="national_id" name="national_id" 
                                   value="<?= htmlspecialchars($patient['national_id'] ?? '') ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Address</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">Full Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Emergency Contact</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                                   value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emergency_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone" 
                                   value="<?= htmlspecialchars($patient['emergency_phone'] ?? '') ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="/doctor/patients/<?= $patient['id'] ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Patient Profile
                                </a>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="savePatientBtn">
                                        <span class="spinner-border spinner-border-sm d-none" id="saveSpinner"></span>
                                        <i class="bi bi-check-lg me-2"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Form Styling */
.card {
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

h6.border-bottom {
    color: #495057;
    font-weight: 600;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

/* Dark mode support */
.dark .card {
    background-color: var(--card);
    border-color: var(--border);
}

.dark .card-header {
    background: linear-gradient(135deg, #4a5568 0%, #553c8b 100%);
    border-bottom-color: var(--border);
}

.dark .form-control {
    background-color: var(--card);
    border-color: var(--border);
    color: var(--text);
}

.dark .form-control:focus {
    background-color: var(--card);
    border-color: #667eea;
    color: var(--text);
}

.dark .form-label {
    color: var(--text);
}

.dark h6.border-bottom {
    color: var(--text);
    border-bottom-color: var(--border) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editPatientForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous validation errors
        clearValidationErrors();
        
        // Basic validation
        if (!validateForm()) {
            return;
        }
        
        // Show loading state
        const saveBtn = document.getElementById('savePatientBtn');
        const spinner = document.getElementById('saveSpinner');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        // Submit form
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                // Redirect will be handled by server
                showNotification('Patient updated successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '/doctor/patients/<?= $patient['id'] ?>';
                }, 1000);
            } else {
                return response.json().then(data => {
                    throw new Error(data.error || 'Failed to update patient');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating patient: ' + error.message, 'error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });
});

function validateForm() {
    let isValid = true;
    
    // Required fields
    const requiredFields = ['first_name', 'last_name', 'phone'];
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            showFieldError(fieldName, 'This field is required');
            isValid = false;
        }
    });
    
    // Phone validation
    const phone = document.getElementById('phone').value.trim();
    if (phone && !isValidPhone(phone)) {
        showFieldError('phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    const altPhone = document.getElementById('alt_phone').value.trim();
    if (altPhone && !isValidPhone(altPhone)) {
        showFieldError('alt_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    const emergencyPhone = document.getElementById('emergency_phone').value.trim();
    if (emergencyPhone && !isValidPhone(emergencyPhone)) {
        showFieldError('emergency_phone', 'Please enter a valid phone number');
        isValid = false;
    }
    
    return isValid;
}

function isValidPhone(phone) {
    // Egyptian phone number validation
    const phoneRegex = /^(\+20|0)?1[0-9]{9}$/;
    return phoneRegex.test(phone.replace(/\s+/g, ''));
}

function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const feedback = field.nextElementSibling;
    
    field.classList.add('is-invalid');
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = message;
    }
}

function resetForm() {
    location.reload();
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
