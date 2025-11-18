<style>
/* Dark Mode Support for Alerts Page */
.card {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.card-header {
    background-color: var(--bg-alt) !important;
    border-bottom-color: var(--border) !important;
    color: var(--text) !important;
}

.card-body {
    background-color: var(--card) !important;
}

/* Table Dark Mode Styles */
.table {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.table thead th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.table tbody tr {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
}

.table tbody tr:hover {
    background-color: var(--bg-alt) !important;
}

.table td {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .table {
    background-color: var(--card) !important;
    color: var(--text) !important;
}

.dark .table thead th {
    background-color: var(--bg-dark) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .table tbody tr {
    background-color: var(--card) !important;
}

.dark .table tbody tr:hover {
    background-color: var(--bg-alt) !important;
}

.dark .table td {
    background-color: var(--card) !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

.dark .text-muted {
    color: var(--muted) !important;
}

.dark .badge {
    color: var(--text) !important;
}

/* Form Controls Dark Mode */
.form-control, .form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.form-control:focus, .form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
}

.dark .form-control, .dark .form-select {
    background-color: var(--card) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
}

.dark .form-control:focus, .dark .form-select:focus {
    background-color: var(--card) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
}

/* Delete Modal Dark Mode */
#deleteAlertModal .modal-content {
    /* Glass effect - similar to sidebar */
    background: rgba(248, 250, 252, 0.35) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
    color: var(--text) !important;
}

.dark #deleteAlertModal .modal-content {
    background: rgba(11, 18, 32, 0.40) !important;
    border: 1px solid rgba(51, 65, 85, 0.3) !important;
    box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
}

#deleteAlertModal .modal-header {
    background: transparent !important;
    border-bottom-color: rgba(226, 232, 240, 0.3) !important;
    color: var(--text) !important;
}

.dark #deleteAlertModal .modal-header {
    background: transparent !important;
    border-bottom-color: rgba(51, 65, 85, 0.3) !important;
}

/* Close button white in dark mode */
.dark #deleteAlertModal .modal-header .btn-close {
    filter: invert(1) brightness(2);
    opacity: 0.9;
}

.dark #deleteAlertModal .modal-header .btn-close:hover {
    opacity: 1;
    filter: invert(1) brightness(2.5);
}

/* Enable dragging */
#deleteAlertModal .modal-content {
    cursor: move;
}

#deleteAlertModal .modal-dialog {
    cursor: default;
    transition: transform 0.2s ease;
    margin: 1.75rem auto;
}

#deleteAlertModal .modal-header {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

#deleteAlertModal .modal-body {
    background: transparent !important;
    color: var(--text) !important;
}

#deleteAlertModal .modal-footer {
    background: transparent !important;
    border-top-color: rgba(226, 232, 240, 0.3) !important;
}

.dark #deleteAlertModal .modal-footer {
    border-top-color: rgba(51, 65, 85, 0.3) !important;
}

#deleteAlertModal .text-muted {
    color: var(--muted) !important;
}

/* Alert Status Styling in Table */
.table tbody tr.alert-active {
    border-left: 4px solid var(--success) !important;
    background-color: rgba(16, 185, 129, 0.05) !important;
}

.dark .table tbody tr.alert-active {
    border-left: 4px solid var(--success) !important;
    background-color: rgba(74, 222, 128, 0.1) !important;
}

.table tbody tr.alert-dismissed,
.table tbody tr.alert-inactive {
    border-left: 4px solid var(--muted) !important;
    opacity: 0.6;
    background-color: rgba(0, 0, 0, 0.02) !important;
}

.dark .table tbody tr.alert-dismissed,
.dark .table tbody tr.alert-inactive {
    background-color: rgba(0, 0, 0, 0.1) !important;
    opacity: 0.5;
}

.table tbody tr.alert-active:hover {
    background-color: rgba(16, 185, 129, 0.1) !important;
}

.dark .table tbody tr.alert-active:hover {
    background-color: rgba(74, 222, 128, 0.15) !important;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-bell me-2"></i>Alerts Management
            </h2>
            <p class="text-muted mb-0">Manage your notifications and reminders</p>
        </div>
        <button class="btn btn-primary" onclick="openAlertModal(null, null)">
            <i class="bi bi-plus-circle me-2"></i>Create New Alert
        </button>
    </div>

    <!-- Alerts List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>All Alerts
            </h5>
        </div>
        <div class="card-body">
            <div id="alertsListContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../alert_modal.php'; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAlertModal" tabindex="-1" aria-labelledby="deleteAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAlertModalLabel">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this alert?</p>
                <p class="text-muted mb-0"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDeleteAlert()">
                    <i class="bi bi-trash me-1"></i>Delete Alert
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAlertIdToDelete = null;
// Note: currentAlertIdToEdit is declared in alert_modal.php

document.addEventListener('DOMContentLoaded', function() {
    loadAlerts();
    
    // Reset currentAlertIdToDelete when modal is closed without deleting
    const deleteModal = document.getElementById('deleteAlertModal');
    if (deleteModal) {
        deleteModal.addEventListener('hidden.bs.modal', function() {
            // Only reset if deletion wasn't successful (check if alerts were reloaded)
            // We'll keep the ID until deletion is confirmed
            setTimeout(() => {
                // Reset after a short delay to ensure deletion process completed
                if (deleteModal.classList.contains('show') === false) {
                    // Only reset if modal is still closed after delay
                    // This prevents resetting during the deletion process
                }
            }, 100);
        });
    }
});

function loadAlerts() {
    fetch('/api/alerts')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('alertsListContainer');
            if (data.success && data.alerts && data.alerts.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
                html += '<th>Message</th><th>Date & Time</th><th>Patient</th><th>Repeat</th><th>Status</th><th>Actions</th>';
                html += '</tr></thead><tbody>';
                
                data.alerts.forEach(alert => {
                    const patientName = alert.patient_first_name && alert.patient_last_name 
                        ? `${alert.patient_first_name} ${alert.patient_last_name}` 
                        : 'N/A';
                    const alertDateTime = new Date(`${alert.alert_date}T${alert.alert_time}`);
                    const dateStr = alertDateTime.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                    const timeStr = alertDateTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    
                    // Determine alert status (same logic as patient.php)
                    const isActive = alert.is_active == 1;
                    const isDismissed = alert.is_dismissed == 1;
                    const isPast = alertDateTime && alertDateTime < new Date();
                    
                    // Determine alert status class for styling
                    let alertStatusClass = '';
                    if (isDismissed) {
                        alertStatusClass = 'alert-dismissed';
                    } else if (isActive) {
                        alertStatusClass = 'alert-active';
                    } else {
                        alertStatusClass = 'alert-inactive';
                    }
                    
                    const statusBadge = isDismissed 
                        ? '<span class="badge bg-secondary">Dismissed</span>' 
                        : (isActive 
                            ? (isPast 
                                ? '<span class="badge bg-warning">Past Due</span>' 
                                : '<span class="badge bg-success">Active</span>')
                            : '<span class="badge bg-secondary">Inactive</span>');
                    
                    const repeatInfo = alert.repeat_count > 0 
                        ? `${alert.current_repeat}/${alert.repeat_count}` 
                        : 'Infinite';
                    
                    html += `
                        <tr class="${alertStatusClass}">
                            <td>${escapeHtml(alert.message)}</td>
                            <td>
                                <i class="bi bi-calendar me-1"></i>${dateStr}<br>
                                <i class="bi bi-clock me-1"></i>${timeStr}
                            </td>
                            <td>
                                ${alert.patient_id ? `
                                    <a href="/doctor/patients/${alert.patient_id}" class="text-decoration-none">
                                        <i class="bi bi-person me-1"></i>${escapeHtml(patientName)}
                                    </a>
                                ` : '<span class="text-muted">-</span>'}
                            </td>
                            <td>${repeatInfo}${alert.repeat_interval > 0 ? ` (every ${alert.repeat_interval} days)` : ''}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    ${alert.patient_id ? `
                                        <a href="/doctor/patients/${alert.patient_id}" class="btn btn-outline-primary" title="View Patient">
                                            <i class="bi bi-person"></i>
                                        </a>
                                    ` : ''}
                                    <button class="btn btn-outline-info" onclick="editAlert(${alert.id})" title="Edit Alert">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="showDeleteConfirmation(${alert.id})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">No alerts found</h5>
                        <p class="text-muted">Create your first alert to get started</p>
                        <button class="btn btn-primary mt-3" onclick="openAlertModal(null, null)">
                            <i class="bi bi-plus-circle me-2"></i>Create Alert
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('alertsListContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error loading alerts
                </div>
            `;
        });
}

function showDeleteConfirmation(alertId) {
    if (!alertId) {
        showToast('error', 'Error', 'Alert ID is required');
        return;
    }
    
    // Store alert ID in both variable and data attribute for safety
    currentAlertIdToDelete = alertId;
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-alert-id', alertId);
    }
    
    const modal = new bootstrap.Modal(document.getElementById('deleteAlertModal'));
    modal.show();
}

function confirmDeleteAlert() {
    // Try to get alert ID from multiple sources
    let alertId = currentAlertIdToDelete;
    
    // Fallback: try to get from button data attribute
    if (!alertId) {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn && confirmBtn.getAttribute('data-alert-id')) {
            alertId = confirmBtn.getAttribute('data-alert-id');
        }
    }
    
    if (!alertId) {
        showToast('error', 'Error', 'Alert ID is required');
        return;
    }
    
    // Disable button during deletion
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
    }
    
    fetch(`/api/alerts/${alertId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAlertModal'));
        
        // Re-enable button
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Alert';
        }
        
        if (data.success) {
            modal.hide();
            showToast('success', 'Alert Deleted', 'The alert has been deleted successfully.');
            currentAlertIdToDelete = null;
            if (confirmBtn) {
                confirmBtn.removeAttribute('data-alert-id');
            }
            loadAlerts(); // Reload without page refresh
        } else {
            showToast('error', 'Error', data.message || 'Failed to delete alert');
            // Don't hide modal on error so user can try again
        }
    })
    .catch(error => {
        // Re-enable button
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete Alert';
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAlertModal'));
        modal.hide();
        showToast('error', 'Error', 'Failed to delete alert. Please try again.');
        currentAlertIdToDelete = null;
        if (confirmBtn) {
            confirmBtn.removeAttribute('data-alert-id');
        }
    });
}

function editAlert(alertId) {
    // Use the openAlertModal function from alert_modal.php
    // Fetch alert details first, then open modal with alert data
    fetch(`/api/alerts/${alertId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.alert) {
                const alert = data.alert;
                // Use openAlertModal with alert data for edit mode
                openAlertModal(alert.patient_id || null, alert.appointment_id || null, alert);
            } else {
                showToast('error', 'Error', data.message || 'Failed to load alert details');
            }
        })
        .catch(error => {
            showToast('error', 'Error', 'Failed to load alert details. Please try again.');
        });
}

function showToast(type, title, message) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const bgColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    const icon = type === 'success' ? 'bi-check-circle' : type === 'error' ? 'bi-exclamation-circle' : 'bi-info-circle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icon} me-2"></i>
                    <strong>${escapeHtml(title)}:</strong> ${escapeHtml(message)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.getElementById('toastContainer');
    if (container) return container;
    
    const newContainer = document.createElement('div');
    newContainer.id = 'toastContainer';
    newContainer.className = 'toast-container position-fixed bottom-0 start-50 translate-middle-x p-3';
    newContainer.style.zIndex = '9999';
    document.body.appendChild(newContainer);
    return newContainer;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
