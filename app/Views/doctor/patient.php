<!-- Patient Profile Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <div class="avatar-circle-large me-3">
                <?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?>
            </div>
            <div>
                <h2 class="text-primary mb-1"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
                <p class="text-muted mb-0">Patient ID: #<?= $patient['id'] ?></p>
                <?php if ($patient['dob']): ?>
                    <small class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('M j, Y', strtotime($patient['dob'])) ?> 
                        (<?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> years old)
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-primary me-2" onclick="bookNewAppointment(<?= $patient['id'] ?>)">
            <i class="bi bi-calendar-plus me-2"></i>
            Book Appointment
        </button>
        <div class="dropdown d-inline">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="printPatientSummary()">
                    <i class="bi bi-printer me-2"></i>Print Summary
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportPatientData()">
                    <i class="bi bi-download me-2"></i>Export Data
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="editPatient(<?= $patient['id'] ?>)">
                    <i class="bi bi-pencil me-2"></i>Edit Patient
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Patient Information Cards -->
<div class="row mb-4">
    <!-- Contact Information -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-telephone me-2"></i>
                    Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>Phone:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></div>
                </div>
                <?php if ($patient['alt_phone']): ?>
                <div class="row mt-2">
                    <div class="col-sm-4"><strong>Alt Phone:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['alt_phone']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($patient['address']): ?>
                <div class="row mt-2">
                    <div class="col-sm-4"><strong>Address:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['address']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($patient['national_id']): ?>
                <div class="row mt-2">
                    <div class="col-sm-4"><strong>National ID:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($patient['national_id']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Emergency Contact -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-exclamation me-2"></i>
                    Emergency Contact
                </h5>
            </div>
            <div class="card-body">
                <?php if ($patient['emergency_contact'] || $patient['emergency_phone']): ?>
                    <div class="row">
                        <div class="col-sm-4"><strong>Name:</strong></div>
                        <div class="col-sm-8" id="emergencyContactName"><?= htmlspecialchars($patient['emergency_contact'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-4"><strong>Phone:</strong></div>
                        <div class="col-sm-8" id="emergencyContactPhone"><?= htmlspecialchars($patient['emergency_phone'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-sm btn-outline-secondary" onclick="editEmergencyContact()">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="noEmergencyContact">
                        <p class="text-muted mb-0">No emergency contact information available</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="addEmergencyContact()">
                            <i class="bi bi-plus me-1"></i>Add Emergency Contact
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Medical History -->
<?php if (!empty($medicalHistory)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-heart me-2"></i>
            Medical History
        </h5>
    </div>
    <div class="card-body">
        <?php foreach ($medicalHistory as $history): ?>
            <div class="row">
                <?php if (!empty($history['allergies'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Allergies
                    </h6>
                    <p><?= htmlspecialchars($history['allergies']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['medications'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-primary">
                        <i class="bi bi-capsule me-1"></i>
                        Current Medications
                    </h6>
                    <p><?= htmlspecialchars($history['medications']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['systemic_history'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-info">
                        <i class="bi bi-heart-pulse me-1"></i>
                        Systemic History
                    </h6>
                    <p><?= htmlspecialchars($history['systemic_history']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['ocular_history'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-success">
                        <i class="bi bi-eye me-1"></i>
                        Ocular History
                    </h6>
                    <p><?= htmlspecialchars($history['ocular_history']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['prior_surgeries'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-warning">
                        <i class="bi bi-scissors me-1"></i>
                        Prior Surgeries
                    </h6>
                    <p><?= htmlspecialchars($history['prior_surgeries']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($history['family_history'])): ?>
                <div class="col-md-6 mb-3">
                    <h6 class="text-secondary">
                        <i class="bi bi-people me-1"></i>
                        Family History
                    </h6>
                    <p><?= htmlspecialchars($history['family_history']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <hr class="my-3">
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-heart me-2"></i>
            Medical History
        </h5>
    </div>
    <div class="card-body text-center">
        <i class="bi bi-clipboard-heart text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2 mb-0">No medical history recorded</p>
        <button class="btn btn-outline-primary mt-3" onclick="addMedicalHistory()">
            <i class="bi bi-plus me-2"></i>Add Medical History
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Recent Appointments -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-calendar-check me-2"></i>
            Recent Appointments
        </h5>
        <span class="badge bg-primary"><?= count($recentAppointments) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentAppointments)): ?>
            <div class="p-4 text-center">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No appointments found</p>
                <button class="btn btn-primary mt-3" onclick="bookNewAppointment(<?= $patient['id'] ?>)">
                    <i class="bi bi-calendar-plus me-2"></i>Book First Appointment
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAppointments as $appointment): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= date('M j, Y', strtotime($appointment['date'])) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('g:i A', strtotime($appointment['start_time'])) ?> - 
                                        <?= date('g:i A', strtotime($appointment['end_time'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($appointment['visit_type']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $this->getStatusBadgeClass($appointment['status']) ?>">
                                        <?= htmlspecialchars($appointment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/doctor/appointments/<?= $appointment['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Patient Timeline -->
<?php if (!empty($timeline)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>
            Patient Timeline
        </h5>
    </div>
    <div class="card-body">
        <div class="timeline">
            <?php foreach ($timeline as $event): ?>
                <div class="timeline-item">
                    <div class="timeline-marker bg-<?= $this->getTimelineEventColor($event['event_type']) ?>">
                        <i class="bi bi-<?= $this->getTimelineEventIcon($event['event_type']) ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <h6 class="timeline-title"><?= htmlspecialchars($event['event_summary']) ?></h6>
                        <?php if (!empty($event['actor_name'])): ?>
                            <p class="timeline-description text-muted">
                                <i class="bi bi-person me-1"></i>
                                by <?= htmlspecialchars($event['actor_name']) ?>
                            </p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            <?= date('M j, Y g:i A', strtotime($event['created_at'])) ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Emergency Contact Modal -->
<div class="modal fade" id="emergencyContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emergencyContactModalTitle">Add Emergency Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="emergencyContactForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="emergencyContactNameInput" class="form-label">Contact Name *</label>
                        <input type="text" class="form-control" id="emergencyContactNameInput" 
                               name="emergency_contact" required maxlength="100" 
                               placeholder="Enter contact name">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="emergencyContactPhoneInput" class="form-label">Contact Phone *</label>
                        <input type="tel" class="form-control" id="emergencyContactPhoneInput" 
                               name="emergency_phone" required 
                               placeholder="Enter phone number (e.g., 01234567890)">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Please enter a valid Egyptian phone number</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveEmergencyContactBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="saveSpinner"></span>
                        <i class="bi bi-check-circle me-2"></i>
                        Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--accent);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
}

.timeline-content {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
}

.timeline-title {
    margin-bottom: 0.5rem;
    color: var(--text);
}

.timeline-description {
    margin-bottom: 0.5rem;
    color: var(--text);
}

.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>

<script>
function bookNewAppointment(patientId) {
    // Redirect to calendar with patient pre-selected
    window.location.href = `/doctor/calendar?patient_id=${patientId}`;
}

function printPatientSummary() {
    // Open print dialog for patient summary
    window.print();
}

function exportPatientData() {
    // Export patient data functionality
    alert('Export functionality will be implemented soon');
}

function editPatient(patientId) {
    // Redirect to edit patient page
    window.location.href = `/doctor/patients/${patientId}/edit`;
}

function addEmergencyContact() {
    // Clear form and set title for adding
    document.getElementById('emergencyContactModalTitle').textContent = 'Add Emergency Contact';
    document.getElementById('emergencyContactForm').reset();
    clearValidationErrors();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('emergencyContactModal'));
    modal.show();
}

function editEmergencyContact() {
    // Set title for editing
    document.getElementById('emergencyContactModalTitle').textContent = 'Edit Emergency Contact';
    
    // Fill form with current values
    document.getElementById('emergencyContactNameInput').value = document.getElementById('emergencyContactName').textContent || '';
    document.getElementById('emergencyContactPhoneInput').value = document.getElementById('emergencyContactPhone').textContent || '';
    
    clearValidationErrors();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('emergencyContactModal'));
    modal.show();
}

function addMedicalHistory() {
    // Show modal or redirect to add medical history
    alert('Add medical history functionality will be implemented soon');
}

// Emergency contact form handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('emergencyContactForm');
    if (form) {
        form.addEventListener('submit', handleEmergencyContactSubmit);
    }
});

function handleEmergencyContactSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {
        emergency_contact: formData.get('emergency_contact').trim(),
        emergency_phone: formData.get('emergency_phone').trim()
    };
    
    // Clear previous validation errors
    clearValidationErrors();
    
    // Basic validation
    if (!data.emergency_contact) {
        showFieldError('emergencyContactNameInput', 'Contact name is required');
        return;
    }
    
    if (!data.emergency_phone) {
        showFieldError('emergencyContactPhoneInput', 'Phone number is required');
        return;
    }
    
    // Validate Egyptian phone number format
    const phoneRegex = /^(\+20|0)?1[0-9]{9}$/;
    if (!phoneRegex.test(data.emergency_phone)) {
        showFieldError('emergencyContactPhoneInput', 'Please enter a valid Egyptian phone number');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('saveEmergencyContactBtn');
    const spinner = document.getElementById('saveSpinner');
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Get patient ID from URL
    const patientId = window.location.pathname.split('/').pop();
    
    console.log('DEBUG: Sending emergency contact update', {
        patientId: patientId,
        data: data,
        url: `/api/patients/${patientId}/emergency-contact`
    });
    
    // Send API request
    fetch(`/api/patients/${patientId}/emergency-contact`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('DEBUG: Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('DEBUG: Response data:', result);
        if (result.ok) {
            // Success - update the UI
            updateEmergencyContactDisplay(data.emergency_contact, data.emergency_phone);
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('emergencyContactModal'));
            modal.hide();
            
            // Show success message
            showNotification('Emergency contact updated successfully!', 'success');
        } else {
            // Handle validation errors
            if (result.details) {
                Object.keys(result.details).forEach(field => {
                    const fieldName = field === 'emergency_contact' ? 'emergencyContactNameInput' : 'emergencyContactPhoneInput';
                    showFieldError(fieldName, result.details[field][0]);
                });
            } else {
                showNotification(result.error || 'Failed to update emergency contact', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Hide loading state
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
}

function updateEmergencyContactDisplay(name, phone) {
    const nameElement = document.getElementById('emergencyContactName');
    const phoneElement = document.getElementById('emergencyContactPhone');
    const noContactDiv = document.getElementById('noEmergencyContact');
    
    if (nameElement && phoneElement) {
        // Update existing display
        nameElement.textContent = name;
        phoneElement.textContent = phone;
    } else if (noContactDiv) {
        // Replace "no contact" message with contact info
        noContactDiv.innerHTML = `
            <div class="row">
                <div class="col-sm-4"><strong>Name:</strong></div>
                <div class="col-sm-8" id="emergencyContactName">${escapeHtml(name)}</div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-4"><strong>Phone:</strong></div>
                <div class="col-sm-8" id="emergencyContactPhone">${escapeHtml(phone)}</div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button class="btn btn-sm btn-outline-secondary" onclick="editEmergencyContact()">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                </div>
            </div>
        `;
    }
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

function showNotification(message, type = 'info') {
    // Create notification element
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
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
