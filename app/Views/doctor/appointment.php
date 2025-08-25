<style>
.appointment-header {
    background: linear-gradient(135deg, var(--accent), var(--success));
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.consultation-section {
    border-left: 4px solid var(--accent);
    padding-left: 1rem;
    margin-bottom: 2rem;
}

.prescription-card {
    border: 2px solid var(--border);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.prescription-card:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.vital-sign {
    text-align: center;
    padding: 1rem;
    background: var(--card);
    border-radius: 8px;
    border: 1px solid var(--border);
}

.vital-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--accent);
}

.timeline-item {
    display: flex;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
    background: var(--card);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid var(--border);
}
</style>

<!-- Appointment Header -->
<div class="appointment-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-calendar-event me-2"></i>
                Appointment #<?= $appointment['id'] ?>
            </h2>
            <p class="mb-2">
                <i class="bi bi-person me-2"></i>
                <strong><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></strong>
                (ID: #<?= $patient['id'] ?>)
            </p>
            <p class="mb-0">
                <i class="bi bi-clock me-2"></i>
                <?= date('l, M j, Y \a\t g:i A', strtotime($appointment['date'] . ' ' . $appointment['start_time'])) ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <span class="status-badge bg-<?= $this->getStatusColor($appointment['status']) ?>">
                <?= ucfirst($appointment['status']) ?>
            </span>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary" onclick="editConsultation(<?= $appointment['id'] ?>)">
                <i class="bi bi-pencil me-1"></i>Edit Consultation
            </button>
            <button type="button" class="btn btn-success" onclick="addPrescription(<?= $appointment['id'] ?>)">
                <i class="bi bi-prescription2 me-1"></i>Add Prescription
            </button>
            <button type="button" class="btn btn-info" onclick="printReport(<?= $appointment['id'] ?>)">
                <i class="bi bi-printer me-1"></i>Print Report
            </button>
            <button type="button" class="btn btn-warning" onclick="rescheduleAppointment(<?= $appointment['id'] ?>)">
                <i class="bi bi-calendar-plus me-1"></i>Reschedule
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column - Patient & Consultation -->
    <div class="col-lg-8">
        
        <!-- Patient Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge me-2"></i>
                    Patient Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></p>
                        <p><strong>Gender:</strong> <?= ucfirst($patient['gender'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Age:</strong> 
                            <?php if ($patient['dob']): ?>
                                <?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> years
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($patient['address'] ?? 'N/A') ?></p>
                        <p><strong>National ID:</strong> <?= htmlspecialchars($patient['national_id'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consultation Notes -->
        <?php if (!empty($consultationNotes)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-pulse me-2"></i>
                    Consultation Notes
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($consultationNotes as $note): ?>
                    <div class="consultation-section">
                        
                        <!-- Chief Complaint -->
                        <?php if (!empty($note['chief_complaint'])): ?>
                        <div class="mb-3">
                            <h6 class="text-primary">Chief Complaint</h6>
                            <p><?= htmlspecialchars($note['chief_complaint']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Vital Signs -->
                        <?php if (!empty($note['vital_signs'])): ?>
                        <div class="mb-3">
                            <h6 class="text-success">Vital Signs</h6>
                            <?php 
                            $vitals = json_decode($note['vital_signs'], true);
                            if ($vitals): ?>
                                <div class="row">
                                    <?php foreach ($vitals as $vital => $value): ?>
                                    <div class="col-md-3 mb-2">
                                        <div class="vital-sign">
                                            <div class="vital-value"><?= htmlspecialchars($value) ?></div>
                                            <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $vital)) ?></small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p><?= htmlspecialchars($note['vital_signs']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Visual Acuity -->
                        <?php if (!empty($note['visual_acuity'])): ?>
                        <div class="mb-3">
                            <h6 class="text-info">Visual Acuity</h6>
                            <p><?= htmlspecialchars($note['visual_acuity']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Examination -->
                        <?php if (!empty($note['examination'])): ?>
                        <div class="mb-3">
                            <h6 class="text-warning">Examination</h6>
                            <p><?= nl2br(htmlspecialchars($note['examination'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Diagnosis -->
                        <?php if (!empty($note['diagnosis'])): ?>
                        <div class="mb-3">
                            <h6 class="text-danger">Diagnosis</h6>
                            <p><?= htmlspecialchars($note['diagnosis']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Plan -->
                        <?php if (!empty($note['plan'])): ?>
                        <div class="mb-3">
                            <h6 class="text-secondary">Treatment Plan</h6>
                            <p><?= nl2br(htmlspecialchars($note['plan'])) ?></p>
                        </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-pulse me-2"></i>
                    Consultation Notes
                </h5>
            </div>
            <div class="card-body text-center">
                <i class="bi bi-clipboard-pulse text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No consultation notes recorded</p>
                <button class="btn btn-outline-primary mt-3" onclick="addConsultationNotes(<?= $appointment['id'] ?>)">
                    <i class="bi bi-plus me-2"></i>Add Consultation Notes
                </button>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right Column - Prescriptions & Actions -->
    <div class="col-lg-4">
        
        <!-- Medication Prescriptions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-capsule me-2"></i>
                    Medications
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($medications)): ?>
                    <?php foreach ($medications as $med): ?>
                    <div class="prescription-card p-3 mb-3">
                        <h6 class="text-primary"><?= htmlspecialchars($med['drug_name']) ?></h6>
                        <p class="mb-1">
                            <strong>Dosage:</strong> <?= htmlspecialchars($med['dose']) ?><br>
                            <strong>Frequency:</strong> <?= htmlspecialchars($med['frequency']) ?><br>
                            <strong>Duration:</strong> <?= htmlspecialchars($med['duration']) ?>
                        </p>
                        <?php if (!empty($med['notes'])): ?>
                            <p class="text-muted mb-0">
                                <small><?= htmlspecialchars($med['notes']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center">
                        <i class="bi bi-capsule text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No medications prescribed</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Glasses Prescriptions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-eyeglasses me-2"></i>
                    Glasses Prescription
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($glasses)): ?>
                    <?php foreach ($glasses as $glass): ?>
                    <div class="prescription-card p-3 mb-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <h6 class="text-primary">Right Eye (OD)</h6>
                                <p class="mb-1">
                                    SPH: <?= htmlspecialchars($glass['od_sphere'] ?? 'N/A') ?><br>
                                    CYL: <?= htmlspecialchars($glass['od_cylinder'] ?? 'N/A') ?><br>
                                    AXIS: <?= htmlspecialchars($glass['od_axis'] ?? 'N/A') ?>
                                </p>
                            </div>
                            <div class="col-6">
                                <h6 class="text-primary">Left Eye (OS)</h6>
                                <p class="mb-1">
                                    SPH: <?= htmlspecialchars($glass['os_sphere'] ?? 'N/A') ?><br>
                                    CYL: <?= htmlspecialchars($glass['os_cylinder'] ?? 'N/A') ?><br>
                                    AXIS: <?= htmlspecialchars($glass['os_axis'] ?? 'N/A') ?>
                                </p>
                            </div>
                        </div>
                        <?php if (!empty($glass['pd'])): ?>
                            <div class="text-center mt-2">
                                <strong>PD:</strong> <?= htmlspecialchars($glass['pd']) ?>mm
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($glass['notes'])): ?>
                            <p class="text-muted mt-2 mb-0">
                                <small><?= htmlspecialchars($glass['notes']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center">
                        <i class="bi bi-eyeglasses text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No glasses prescription</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="markCompleted(<?= $appointment['id'] ?>)">
                        <i class="bi bi-check-circle me-2"></i>Mark as Completed
                    </button>
                    <button class="btn btn-outline-success" onclick="scheduleFollowUp(<?= $appointment['id'] ?>)">
                        <i class="bi bi-calendar-plus me-2"></i>Schedule Follow-up
                    </button>
                    <button class="btn btn-outline-info" onclick="viewPatient(<?= $patient['id'] ?>)">
                        <i class="bi bi-person me-2"></i>View Patient Profile
                    </button>
                    <button class="btn btn-outline-warning" onclick="printPrescription(<?= $appointment['id'] ?>)">
                        <i class="bi bi-printer me-2"></i>Print Prescription
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function editConsultation(appointmentId) {
    // Redirect to edit consultation page
    window.location.href = `/doctor/appointments/${appointmentId}/edit`;
}

function addPrescription(appointmentId) {
    // Show prescription modal
    showPrescriptionModal(appointmentId);
}

function printReport(appointmentId) {
    // Open print view
    window.open(`/print/appointment/${appointmentId}`, '_blank');
}

function rescheduleAppointment(appointmentId) {
    // Show reschedule modal
    showRescheduleModal(appointmentId);
}

function addConsultationNotes(appointmentId) {
    // Redirect to add notes page
    window.location.href = `/doctor/appointments/${appointmentId}/notes`;
}

function markCompleted(appointmentId) {
    if (confirm('Mark this appointment as completed?')) {
        // API call to update status
        alert('Mark completed functionality will be implemented soon');
    }
}

function scheduleFollowUp(appointmentId) {
    // Show follow-up scheduling modal
    alert('Schedule follow-up functionality will be implemented soon');
}

function viewPatient(patientId) {
    // Redirect to patient profile
    window.location.href = `/doctor/patients/${patientId}`;
}

function printPrescription(appointmentId) {
    // Open prescription print view
    window.open(`/print/prescription/${appointmentId}`, '_blank');
}

function showPrescriptionModal(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="prescriptionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة وصفة طبية</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="prescriptionForm" action="/api/prescriptions/meds" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">اسم الدواء</label>
                                    <input type="text" class="form-control" name="drug_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الجرعة</label>
                                    <input type="text" class="form-control" name="dose" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">التكرار</label>
                                    <input type="text" class="form-control" name="frequency" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">المدة</label>
                                    <input type="text" class="form-control" name="duration" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">طريقة الإعطاء</label>
                                    <select class="form-control" name="route">
                                        <option value="Topical">موضعي</option>
                                        <option value="Oral">فموي</option>
                                        <option value="Injection">حقن</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea class="form-control" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ الوصفة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('prescriptionModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/prescriptions/meds', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            alert('حدث خطأ في الاتصال');
        });
    });
    
    // Clean up modal on hide
    document.getElementById('prescriptionModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function showRescheduleModal(appointmentId) {
    const modalHtml = `
        <div class="modal fade" id="rescheduleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إعادة جدولة الموعد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="rescheduleForm">
                        <div class="modal-body">
                            <input type="hidden" name="appointment_id" value="${appointmentId}">
                            <div class="mb-3">
                                <label class="form-label">التاريخ الجديد</label>
                                <input type="date" class="form-control" name="new_date" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الوقت الجديد</label>
                                <input type="time" class="form-control" name="new_time" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">سبب إعادة الجدولة</label>
                                <textarea class="form-control" name="reason" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-warning">إعادة جدولة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
    modal.show();
    
    // Handle form submission
    document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('/api/appointments/' + appointmentId, {
            method: 'PUT',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            alert('حدث خطأ في الاتصال');
        });
    });
    
    // Clean up modal on hide
    document.getElementById('rescheduleModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
</script>
