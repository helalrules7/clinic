<div class="row mb-4">
    <div class="col-md-6">
        <h4 class="mb-0">Manage Bookings</h4>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBookingModal">
            <i class="bi bi-calendar-plus me-2"></i>
            New Booking
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control" id="searchInput" placeholder="Search by patient name, phone, or doctor...">
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="statusFilter">
            <option value="">All Statuses</option>
            <option value="Booked">Booked</option>
            <option value="CheckedIn">Checked In</option>
            <option value="InProgress">In Progress</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
            <option value="NoShow">No Show</option>
        </select>
    </div>
</div>

<!-- Today's Bookings -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-calendar-day me-2"></i>
            Today's Bookings
        </h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshBookings()">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportBookings()">
                <i class="bi bi-download"></i>
                Export
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="bookingsTable">
            <!-- Bookings will be loaded here -->
        </div>
    </div>
</div>

<!-- New Booking Modal -->
<div class="modal fade" id="newBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Appointment Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bookingForm">
                    <?= $this->csrfField() ?>
                    
                    <!-- Patient Selection -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="patientSearch" class="form-label">Patient *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="patientSearch" 
                                       placeholder="Search for existing patient...">
                                <button type="button" class="btn btn-outline-secondary" type="button" 
                                        onclick="searchPatients()">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="patientSearchResults" class="mt-2" style="display: none;"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-success w-100" onclick="showNewPatientForm()">
                                    <i class="bi bi-person-plus me-2"></i>
                                    New Patient
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- New Patient Form (Hidden by default) -->
                    <div id="newPatientForm" style="display: none;">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Register new patient details
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointment Details -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">Doctor *</label>
                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['id'] ?>"><?= $doctor['display_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="visit_type" class="form-label">Visit Type *</label>
                            <select class="form-select" id="visit_type" name="visit_type" required>
                                <option value="">Select Type</option>
                                <option value="New">New Patient</option>
                                <option value="FollowUp">Follow Up</option>
                                <option value="Procedure">Procedure</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="appointment_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="appointment_date" name="date" 
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="start_time" class="form-label">Time *</label>
                            <select class="form-select" id="start_time" name="start_time" required>
                                <option value="">Select Time</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="source" class="form-label">Source *</label>
                            <select class="form-select" id="source" name="source" required>
                                <option value="">Select Source</option>
                                <option value="Walk-in">Walk-in</option>
                                <option value="Phone">Phone</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Any special notes or requirements..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createBooking()">
                    <i class="bi bi-calendar-check me-2"></i>
                    Book Appointment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBookingForm">
                    <?= $this->csrfField() ?>
                    <input type="hidden" id="edit_appointment_id" name="appointment_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label for="edit_doctor_id" class="form-label">Doctor</label>
                            <select class="form-select" id="edit_doctor_id" name="doctor_id" required>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['id'] ?>"><?= $doctor['display_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_appointment_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="edit_appointment_date" name="date" required>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="edit_start_time" class="form-label">Time</label>
                            <select class="form-select" id="edit_start_time" name="start_time" required>
                                <option value="">Select Time</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="rescheduleAppointment()">
                    <i class="bi bi-calendar-event me-2"></i>
                    Reschedule
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedPatient = null;
let selectedDoctor = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadBookings();
    setupEventListeners();
    generateTimeSlots();
});

function setupEventListeners() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', debounce(filterBookings, 300));
    document.getElementById('statusFilter').addEventListener('change', filterBookings);
    
    // Date change handler
    document.getElementById('appointment_date').addEventListener('change', function() {
        generateTimeSlots();
    });
    
    // Doctor change handler
    document.getElementById('doctor_id').addEventListener('change', function() {
        selectedDoctor = this.value;
        generateTimeSlots();
    });
}

function loadBookings() {
    fetch('/api/appointments?date=' + new Date().toISOString().split('T')[0])
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                renderBookingsTable(data.data);
            } else {
                console.error('Error loading bookings:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
        });
}

function renderBookingsTable(appointments) {
    const container = document.getElementById('bookingsTable');
    
    if (appointments.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-calendar-x display-4 text-muted"></i>
                <p class="text-muted mt-2">No appointments scheduled for today</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    appointments.forEach(appointment => {
        html += `
            <tr data-appointment-id="${appointment.id}" data-status="${appointment.status}">
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clock me-2 text-primary"></i>
                        ${formatTime(appointment.start_time)}
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">${appointment.patient_name}</div>
                            <small class="text-muted">${appointment.patient_phone}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-info">${appointment.doctor_name}</span>
                </td>
                <td>
                    <span class="badge ${getVisitTypeBadgeClass(appointment.visit_type)}">
                        ${appointment.visit_type}
                    </span>
                </td>
                <td>
                    <span class="badge ${getStatusBadgeClass(appointment.status)}">
                        ${appointment.status}
                    </span>
                </td>
                <td>
                    <small class="text-muted">${appointment.source}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                onclick="viewAppointment(${appointment.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${appointment.status === 'Booked' ? `
                            <button type="button" class="btn btn-outline-success btn-sm"
                                    onclick="checkInPatient(${appointment.id})">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm"
                                    onclick="editAppointment(${appointment.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

function searchPatients() {
    const query = document.getElementById('patientSearch').value.trim();
    if (query.length < 2) return;
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayPatientSearchResults(data.data);
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
        });
}

function displayPatientSearchResults(patients) {
    const container = document.getElementById('patientSearchResults');
    
    if (patients.length === 0) {
        container.innerHTML = '<div class="text-muted">No patients found</div>';
        container.style.display = 'block';
        return;
    }
    
    let html = '<div class="list-group">';
    patients.forEach(patient => {
        html += `
            <button type="button" class="list-group-item list-group-item-action" 
                    onclick="selectPatient(${patient.id}, '${patient.full_name}', '${patient.phone}')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${patient.full_name}</strong>
                        <br><small class="text-muted">${patient.phone}</small>
                    </div>
                    <small class="text-muted">${patient.gender || 'N/A'}</small>
                </div>
            </button>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
    container.style.display = 'block';
}

function selectPatient(patientId, patientName, patientPhone) {
    selectedPatient = { id: patientId, name: patientName, phone: patientPhone };
    
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('patientSearchResults').style.display = 'none';
    
    // Hide new patient form
    document.getElementById('newPatientForm').style.display = 'none';
}

function showNewPatientForm() {
    document.getElementById('newPatientForm').style.display = 'block';
    document.getElementById('patientSearchResults').style.display = 'none';
    selectedPatient = null;
}

function generateTimeSlots() {
    const date = document.getElementById('appointment_date').value;
    const doctorId = document.getElementById('doctor_id').value;
    
    if (!date || !doctorId) return;
    
    // Generate time slots from 2 PM to 11 PM in 15-minute intervals
    const slots = [];
    const start = new Date();
    start.setHours(14, 0, 0, 0); // 2:00 PM
    
    const end = new Date();
    end.setHours(23, 0, 0, 0); // 11:00 PM
    
    const current = new Date(start);
    
    while (current < end) {
        slots.push(current.toTimeString().substring(0, 5));
        current.setMinutes(current.getMinutes() + 15);
    }
    
    // Populate time slots
    const timeSelect = document.getElementById('start_time');
    timeSelect.innerHTML = '<option value="">Select Time</option>';
    
    slots.forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = formatTime(time);
        timeSelect.appendChild(option);
    });
}

function createBooking() {
    const form = document.getElementById('bookingForm');
    const formData = new FormData(form);
    
    // Validate required fields
    if (!selectedPatient && !formData.get('first_name')) {
        alert('Please select a patient or fill in new patient details');
        return;
    }
    
    if (!formData.get('doctor_id') || !formData.get('date') || !formData.get('start_time')) {
        alert('Please fill in all required fields');
        return;
    }
    
    // If new patient, create patient first
    if (!selectedPatient) {
        createPatientAndBooking(formData);
    } else {
        // Add patient ID to form data
        formData.append('patient_id', selectedPatient.id);
        submitBooking(formData);
    }
}

function createPatientAndBooking(formData) {
    const patientData = {
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        phone: formData.get('phone'),
        gender: formData.get('gender')
    };
    
    fetch('/api/patients', {
        method: 'POST',
        body: new FormData(Object.assign(new FormData(), patientData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            formData.append('patient_id', data.data.id);
            submitBooking(formData);
        } else {
            alert('Error creating patient: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating patient');
    });
}

function submitBooking(formData) {
    fetch('/api/appointments', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            alert('Appointment booked successfully!');
            bootstrap.Modal.getInstance(document.getElementById('newBookingModal')).hide();
            form.reset();
            selectedPatient = null;
            loadBookings();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error booking appointment');
    });
}

function editAppointment(appointmentId) {
    // Load appointment details and show edit modal
    fetch(`/api/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                populateEditForm(data.data);
                new bootstrap.Modal(document.getElementById('editBookingModal')).show();
            }
        })
        .catch(error => {
            console.error('Error loading appointment:', error);
        });
}

function populateEditForm(appointment) {
    document.getElementById('edit_appointment_id').value = appointment.id;
    document.getElementById('edit_doctor_id').value = appointment.doctor_id;
    document.getElementById('edit_appointment_date').value = appointment.date;
    document.getElementById('edit_start_time').value = appointment.start_time;
    document.getElementById('edit_notes').value = appointment.notes || '';
}

function rescheduleAppointment() {
    const form = document.getElementById('editBookingForm');
    const formData = new FormData(form);
    
    fetch(`/api/appointments/${formData.get('appointment_id')}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            doctor_id: formData.get('doctor_id'),
            date: formData.get('date'),
            start_time: formData.get('start_time'),
            notes: formData.get('notes')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            alert('Appointment rescheduled successfully!');
            bootstrap.Modal.getInstance(document.getElementById('editBookingModal')).hide();
            loadBookings();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error rescheduling appointment');
    });
}

function checkInPatient(appointmentId) {
    if (confirm('Mark patient as checked in?')) {
        fetch(`/api/appointments/${appointmentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: 'CheckedIn'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                loadBookings();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating appointment status');
        });
    }
}

function viewAppointment(appointmentId) {
    window.location.href = `/secretary/appointments/${appointmentId}`;
}

function filterBookings() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('#bookingsTable tbody tr');
    
    rows.forEach(row => {
        const patientName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const status = row.dataset.status;
        
        const matchesSearch = patientName.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}

function refreshBookings() {
    loadBookings();
}

function exportBookings() {
    // Implement CSV export functionality
    alert('Export functionality will be implemented');
}

// Utility functions
function formatTime(time) {
    if (!time) return '';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function getStatusBadgeClass(status) {
    const classes = {
        'Booked': 'badge bg-primary',
        'CheckedIn': 'badge bg-info',
        'InProgress': 'badge bg-warning',
        'Completed': 'badge bg-success',
        'Cancelled': 'badge bg-danger',
        'NoShow': 'badge bg-secondary'
    };
    return classes[status] || 'badge bg-secondary';
}

function getVisitTypeBadgeClass(type) {
    const classes = {
        'New': 'badge bg-primary',
        'FollowUp': 'badge bg-success',
        'Procedure': 'badge bg-warning'
    };
    return classes[type] || 'badge bg-secondary';
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
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

.list-group-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.list-group-item:hover {
    background-color: var(--bg);
}

.list-group-item-action:active {
    background-color: var(--accent);
    color: white;
}

#patientSearchResults {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--card);
}

#newPatientForm {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    background: var(--bg);
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    border-right: 1px solid var(--border);
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
}
</style>
