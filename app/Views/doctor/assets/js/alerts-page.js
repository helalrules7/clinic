/* ============================================================================
 * Alerts page logic — EXTRACTED from alerts/index.php on 2026-06-10.
 *
 * Verbatim move of the page's single inline <script> (no IIFE wrap) so its 17
 * inline onclick handlers (openAlertModal/editAlert/dismissAlert/toggleAlertStatus
 * /confirm* …) keep resolving against the global scope as before.
 *
 * Loaded by alerts/index.php (standalone page AND when embedded into the
 * dashboard via $alertsEmbedded). Two embed-aware tweaks vs the original inline:
 *   1) readyState-safe boot (runs even when loaded late inside the dashboard);
 *   2) loadAlerts() detects .alerts-page--embedded → fetches /api/alerts/today,
 *      skips stats + pagination (compact "today" list). All refresh paths reuse
 *      loadAlerts(), so they stay today-scoped in embedded mode automatically.
 * Uses /api/alerts*. Keep STANDALONE. Mirror any change to ortho.
 * ========================================================================== */

let currentAlertIdToDelete = null;
let currentPage = 1;
let perPage = 10;
// Note: currentAlertIdToEdit is declared in alert_modal.php

function __alertsPageBoot() {
    loadAlerts(currentPage, perPage);

    // Handle per page change (full page only; element is hidden in embedded mode)
    var __ppSel = document.getElementById('alertsPerPageSelect');
    if (__ppSel) __ppSel.addEventListener('change', function() {
        const value = this.value;
        if (value === 'all') {
            perPage = 999999; // Large number to show all
        } else {
            perPage = parseInt(value);
        }
        currentPage = 1;
        loadAlerts(currentPage, perPage);
    });
    
    // Initialize draggable modals
    initializeAlertsModalsDraggable();
    
    // Reset currentAlertIdToDelete when modal is closed without deleting
    const deleteModal = document.getElementById('deleteAlertModal');
    if (deleteModal) {
        deleteModal.addEventListener('hidden.bs.modal', function() {
            setTimeout(() => {
                if (deleteModal.classList.contains('show') === false) {
                    // Only reset if modal is still closed after delay
                }
            }, 100);
        });
    }
    
    // Check if we need to open add alert modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openModal') === 'addAlert') {
        setTimeout(() => {
            if (typeof openAlertModal === 'function') {
                openAlertModal(null, null);
            }
            // Clean URL
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=addAlert/, '').replace(/^&/, '?');
            window.history.replaceState({}, '', newUrl);
        }, 500);
    }
}
// readyState-safe boot so the embed runs even when this file loads AFTER
// DOMContentLoaded has already fired (mid/late inside the dashboard).
if (document.readyState !== 'complete') document.addEventListener('DOMContentLoaded', __alertsPageBoot);
else __alertsPageBoot();

function loadAlerts(page = 1, limit = 10) {
    const container = document.getElementById('alertsListContainer');
    const paginationNav = document.getElementById('alertsPaginationNav');
    const __embedded = !!document.querySelector('.alerts-page--embedded');

    // Show loading
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    if (paginationNav) paginationNav.style.display = 'none';

    // Embedded (dashboard) mode: compact "today" list — no stats, no pagination.
    const url = __embedded
        ? `/api/alerts/today?_=${Date.now()}`
        : (limit >= 999999
            ? `/api/alerts?_=${Date.now()}`
            : `/api/alerts?page=${page}&per_page=${limit}&_=${Date.now()}`);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!__embedded) updateAlertStats(data.stats);
            if (data.success && data.alerts && data.alerts.length > 0) {
                // Embedded mode: render the today list only, skip pagination.
                if (__embedded) {
                    renderAlerts(data.alerts);
                } else if (data.pagination) {
                    renderAlerts(data.alerts);
                    renderAlertsPagination(data.pagination);
                } else {
                    // Fallback: if no pagination, show all and paginate client-side
                    const start = (page - 1) * limit;
                    const end = start + limit;
                    const paginatedAlerts = data.alerts.slice(start, end);
                    renderAlerts(paginatedAlerts);
                    
                    if (data.alerts.length > limit) {
                        const totalPages = Math.ceil(data.alerts.length / limit);
                        renderAlertsPagination({
                            current_page: page,
                            total_pages: totalPages,
                            total_items: data.alerts.length,
                            per_page: limit
                        });
                    }
                }
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
                paginationNav.style.display = 'none';
            }
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error loading alerts
                </div>
            `;
            paginationNav.style.display = 'none';
        });
}

function renderAlerts(alerts) {
    const container = document.getElementById('alertsListContainer');
    if (!alerts || alerts.length === 0) {
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
        return;
    }
    
    let html = '<div class="alerts-card-list">';

    alerts.forEach(alert => {
        const patientName = alert.patient_first_name && alert.patient_last_name
            ? `${alert.patient_first_name} ${alert.patient_last_name}`
            : 'N/A';
        const alertDateTime = new Date(`${alert.alert_date}T${alert.alert_time}`);
        const dateStr = alertDateTime.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        const timeStr = alertDateTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        const isActive = alert.is_active == 1;
        const isDismissed = alert.is_dismissed == 1;
        const isPast = alertDateTime && alertDateTime < new Date();

        // Status → key / label / icon (drives the card's accent color via CSS)
        let statusKey, statusLabel, statusIcon;
        if (isDismissed) { statusKey = 'dismissed'; statusLabel = 'Dismissed'; statusIcon = 'bi-check2-circle'; }
        else if (isActive && isPast) { statusKey = 'pastdue'; statusLabel = 'Past Due'; statusIcon = 'bi-alarm-fill'; }
        else if (isActive) { statusKey = 'active'; statusLabel = 'Active'; statusIcon = 'bi-bell-fill'; }
        else { statusKey = 'inactive'; statusLabel = 'Inactive'; statusIcon = 'bi-bell-slash'; }

        // Active alerts: status pill is clickable to dismiss
        const statusPill = (!isDismissed && isActive)
            ? `<span class="alert-status-pill is-${statusKey}" style="cursor:pointer;" onclick="dismissAlert(${alert.id})" title="Click to dismiss">${statusLabel}</span>`
            : `<span class="alert-status-pill is-${statusKey}">${statusLabel}</span>`;

        const repeatInfo = alert.repeat_count > 0 ? `${alert.current_repeat}/${alert.repeat_count}` : 'Infinite';
        const repeatExtra = alert.repeat_interval > 0 ? ` · every ${alert.repeat_interval}d` : '';

        html += `
            <div class="alert-card is-${statusKey}">
                <span class="alert-card-icon"><i class="bi ${statusIcon}"></i></span>
                <div class="alert-card-main">
                    <div class="alert-card-message">${alert.message}</div>
                    <div class="alert-card-meta">
                        <span class="alert-meta-chip"><i class="bi bi-calendar-event"></i>${dateStr}</span>
                        <span class="alert-meta-chip"><i class="bi bi-clock"></i>${timeStr}</span>
                        ${alert.patient_id ? `<a href="/doctor/patients/${alert.patient_id}" class="alert-meta-chip alert-meta-patient"><i class="bi bi-person"></i>${escapeHtml(patientName)}</a>` : ''}
                        <span class="alert-meta-chip"><i class="bi bi-arrow-repeat"></i>${repeatInfo}${repeatExtra}</span>
                    </div>
                </div>
                <div class="alert-card-side">
                    ${statusPill}
                    <div class="alert-card-actions">
                        ${alert.patient_id ? `<a href="/doctor/patients/${alert.patient_id}" class="alert-act" title="View Patient"><i class="bi bi-person"></i></a>` : ''}
                        ${!isDismissed ? `<button class="alert-act ${isActive ? 'act-warn' : 'act-ok'}" onclick="toggleAlertStatus(${alert.id}, ${isActive ? 0 : 1})" title="${isActive ? 'Deactivate' : 'Activate'} Alert"><i class="bi ${isActive ? 'bi-pause-circle' : 'bi-play-circle'}"></i></button>` : ''}
                        <button class="alert-act act-info" onclick="editAlert(${alert.id})" title="Edit Alert"><i class="bi bi-pencil"></i></button>
                        <button class="alert-act act-danger" onclick="showDeleteConfirmation(${alert.id})" title="Delete"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

function updateAlertStats(stats) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = (v ?? 0); };
    stats = stats || { total: 0, active: 0, past_due: 0, dismissed: 0 };
    set('alertsStatTotal', stats.total);
    set('alertsStatActive', stats.active);
    set('alertsStatPastDue', stats.past_due);
    set('alertsStatDismissed', stats.dismissed);
}

function renderAlertsPagination(pagination) {
    const paginationNav = document.getElementById('alertsPaginationNav');
    const paginationList = document.getElementById('alertsPaginationList');
    
    if (!pagination || pagination.total_pages <= 1) {
        paginationNav.style.display = 'none';
        return;
    }
    
    paginationNav.style.display = 'block';
    
    let html = '';
    const currentPageNum = pagination.current_page;
    const totalPages = pagination.total_pages;
    
    // Previous button
    html += `
        <li class="page-item ${currentPageNum === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); loadAlertsPage(${currentPageNum - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadAlertsPage(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPageNum ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadAlertsPage(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadAlertsPage(${totalPages}); return false;">${totalPages}</a></li>`;
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPageNum === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); loadAlertsPage(${currentPageNum + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationList.innerHTML = html;
}

// Global function for pagination
window.loadAlertsPage = function(page) {
    currentPage = page;
    loadAlerts(currentPage, perPage);
    // Scroll to top of container
    document.getElementById('alertsListContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// Show disable all confirmation
function showDisableAllConfirmation() {
    const modal = new bootstrap.Modal(document.getElementById('disableAllAlertsModal'));
    modal.show();
}

// Confirm disable all alerts
function confirmDisableAllAlerts() {
    const confirmBtn = document.getElementById('confirmDisableAllBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Disabling...';
    }
    
    fetch('/api/alerts/disable-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('disableAllAlertsModal'));
        
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-pause-circle me-1"></i>Disable All Alerts';
        }
        
        if (data.success) {
            modal.hide();
            showToast('success', 'Alerts Disabled', 'All alerts have been disabled successfully.');
            loadAlerts(currentPage, perPage);
        } else {
            showToast('error', 'Error', data.message || 'Failed to disable all alerts');
        }
    })
    .catch(error => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-pause-circle me-1"></i>Disable All Alerts';
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('disableAllAlertsModal'));
        modal.hide();
        showToast('error', 'Error', 'Failed to disable all alerts. Please try again.');
    });
}

// Show delete all confirmation
function showDeleteAllConfirmation() {
    const modal = new bootstrap.Modal(document.getElementById('deleteAllAlertsModal'));
    modal.show();
}

// Confirm delete all alerts
function confirmDeleteAllAlerts() {
    const confirmBtn = document.getElementById('confirmDeleteAllBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
    }
    
    fetch('/api/alerts/delete-all', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAllAlertsModal'));
        
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete All Alerts';
        }
        
        if (data.success) {
            modal.hide();
            showToast('success', 'Alerts Deleted', 'All alerts have been deleted successfully.');
            loadAlerts(currentPage, perPage);
        } else {
            showToast('error', 'Error', data.message || 'Failed to delete all alerts');
        }
    })
    .catch(error => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Delete All Alerts';
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAllAlertsModal'));
        modal.hide();
        showToast('error', 'Error', 'Failed to delete all alerts. Please try again.');
    });
}

// Initialize draggable modals
function initializeAlertsModalsDraggable() {
    const modals = document.querySelectorAll('.alerts-modal-glass');
    modals.forEach(modal => {
        makeAlertsModalDraggable(modal);
    });
}

function makeAlertsModalDraggable(modalElement) {
    /* Drag/center/animation unified in layouts/modal-kit.js. No-op. */
    return;
    const modalDialog = modalElement.querySelector('.modal-dialog');
    if (!modalDialog) return;
    
    let isDragging = false;
    let currentX;
    let currentY;
    let initialX;
    let initialY;
    let xOffset = 0;
    let yOffset = 0;
    
    const modalHeader = modalElement.querySelector('.modal-header');
    if (!modalHeader) return;
    
    modalHeader.addEventListener('mousedown', dragStart);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', dragEnd);
    
    function dragStart(e) {
        if (e.target.closest('button')) return; // Don't drag if clicking buttons
        
        initialX = e.clientX - xOffset;
        initialY = e.clientY - yOffset;
        
        if (e.target === modalHeader || modalHeader.contains(e.target)) {
            isDragging = true;
            modalDialog.classList.add('dragging');
        }
    }
    
    function drag(e) {
        if (isDragging) {
            e.preventDefault();
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            
            xOffset = currentX;
            yOffset = currentY;
            
            setTranslate(currentX, currentY, modalDialog);
        }
    }
    
    function dragEnd(e) {
        initialX = currentX;
        initialY = currentY;
        isDragging = false;
        modalDialog.classList.remove('dragging');
    }
    
    function setTranslate(xPos, yPos, el) {
        el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
    }
    
    // Reset position when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
        xOffset = 0;
        yOffset = 0;
        modalDialog.style.transform = '';
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
            loadAlerts(currentPage, perPage); // Reload without page refresh
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

// Toggle alert status (activate/deactivate) via AJAX
function toggleAlertStatus(alertId, newStatus) {
    if (!alertId) {
        showToast('error', 'Error', 'Alert ID is required');
        return;
    }
    
    fetch(`/api/alerts/${alertId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Success', data.message || 'Alert status updated successfully');
            // Reload alerts without page refresh - add small delay to ensure server has updated
            setTimeout(() => {
                loadAlerts(currentPage, perPage);
            }, 300);
        } else {
            showToast('error', 'Error', data.message || 'Failed to update alert status');
        }
    })
    .catch(error => {
        showToast('error', 'Error', 'Failed to update alert status. Please try again.');
    });
}

// Dismiss alert via AJAX
function dismissAlert(alertId) {
    if (!alertId) {
        showToast('error', 'Error', 'Alert ID is required');
        return;
    }
    
    fetch('/api/alerts/dismiss', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            alert_id: alertId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Success', data.message || 'Alert dismissed successfully');
            // Reload alerts without page refresh - add small delay to ensure server has updated
            setTimeout(() => {
                loadAlerts(currentPage, perPage);
            }, 300);
        } else {
            showToast('error', 'Error', data.message || 'Failed to dismiss alert');
        }
    })
    .catch(error => {
        showToast('error', 'Error', 'Failed to dismiss alert. Please try again.');
    });
}
