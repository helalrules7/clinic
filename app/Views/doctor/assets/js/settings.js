

function resetForm() {
    if (confirm('Are you sure you want to reset all settings to their default values?')) {
        document.querySelector('form').reset();
        // Clear all previews
        document.querySelectorAll('[id$="_preview"]').forEach(preview => {
            preview.innerHTML = '';
        });
    }
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('يرجى اختيار ملف صورة صالح');
            input.value = '';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('حجم الملف كبير جداً. الحد الأقصى 5MB');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">';
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}

// Auto-save functionality (optional)
let autoSaveTimeout;
document.querySelectorAll('input, select').forEach(element => {
    element.addEventListener('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // You could implement auto-save here
        }, 2000);
    });
});

// Drugs Database Update Functions
function showSettingsUpdateDatabaseModal() {
    const modal = new bootstrap.Modal(document.getElementById('settingsUpdateDatabaseModal'));
    modal.show();
    resetSettingsUpdateModal();
}

function resetSettingsUpdateModal() {
    document.getElementById('settingsUpdateProgressBar').style.width = '0%';
    document.getElementById('settingsUpdateProgressBar').setAttribute('aria-valuenow', '0');
    document.getElementById('settingsProgressText').textContent = '0%';
    document.getElementById('settingsProgressLabel').textContent = 'Preparing...';
    document.getElementById('settingsUpdateStatusMessages').innerHTML = '';
    document.getElementById('settingsUpdateStatistics').style.display = 'none';
    document.getElementById('settingsTotalRecords').textContent = '0';
    document.getElementById('settingsInsertedRecords').textContent = '0';
    document.getElementById('settingsUpdatedRecords').textContent = '0';
    document.getElementById('settingsStartUpdateBtn').disabled = false;
    document.getElementById('settingsStartUpdateBtn').innerHTML = '<i class="fas fa-play-circle me-2"></i>Start Update';
    document.getElementById('settingsCloseUpdateModalBtn').disabled = false;
    document.getElementById('settingsUpdateSpinner').style.display = 'none';
}

function startSettingsDatabaseUpdate() {
    const startBtn = document.getElementById('settingsStartUpdateBtn');
    const closeBtn = document.getElementById('settingsCloseUpdateModalBtn');
    
    startBtn.disabled = true;
    closeBtn.disabled = true;
    startBtn.innerHTML = '<i class="fas fa-hourglass-half me-2"></i>Updating...';
    
    document.getElementById('settingsUpdateSpinner').style.display = 'block';
    
    resetSettingsUpdateModal();
    document.getElementById('settingsUpdateStatistics').style.display = 'flex';
    addSettingsStatusMessage('info', 'Starting update process...');
    updateSettingsDatabase();
}

function updateSettingsDatabase() {
    // Step 1: Downloading
    document.getElementById('settingsProgressLabel').textContent = 'Downloading database file...';
    updateSettingsProgress(10);
    addSettingsStatusMessage('info', 'Downloading database file from server...');
    
    setTimeout(() => {
        updateSettingsProgress(30);
        addSettingsStatusMessage('success', 'Database downloaded successfully');
        
        // Step 2: Extracting data
        document.getElementById('settingsProgressLabel').textContent = 'Extracting data from source database...';
        updateSettingsProgress(50);
        addSettingsStatusMessage('info', 'Extracting data from source database...');
        
        setTimeout(() => {
            updateSettingsProgress(70);
            addSettingsStatusMessage('success', 'Data extracted successfully');
            
            // Step 3: Updating database
            document.getElementById('settingsProgressLabel').textContent = 'Updating data in current database...';
            updateSettingsProgress(80);
            addSettingsStatusMessage('info', 'Updating data in current database...');
            
            // Make API call
            fetch('/api/drugs/update-database', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateSettingsProgress(100);
                    document.getElementById('settingsProgressLabel').textContent = 'Update completed successfully!';
                    addSettingsStatusMessage('success', 'Database updated successfully!');
                    
                    document.getElementById('settingsUpdateSpinner').style.display = 'none';
                    
                    if (data.statistics) {
                        document.getElementById('settingsTotalRecords').textContent = data.statistics.total || 0;
                        document.getElementById('settingsInsertedRecords').textContent = data.statistics.inserted || 0;
                        document.getElementById('settingsUpdatedRecords').textContent = data.statistics.updated || 0;
                    }
                    
                    document.getElementById('settingsCloseUpdateModalBtn').disabled = false;
                    document.getElementById('settingsCloseUpdateModalBtn').textContent = 'Close';
                    
                    document.getElementById('settingsStartUpdateBtn').disabled = false;
                    document.getElementById('settingsStartUpdateBtn').innerHTML = '<i class="fas fa-play-circle me-2"></i>Start Update';
                    
                    setTimeout(() => {
                        const updateModal = bootstrap.Modal.getInstance(document.getElementById('settingsUpdateDatabaseModal'));
                        if (updateModal) {
                            updateModal.hide();
                        }
                        alert('Database updated successfully!');
                        location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.error || data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Update error:', error);
                updateSettingsProgress(0);
                document.getElementById('settingsProgressLabel').textContent = 'An error occurred during update';
                addSettingsStatusMessage('danger', 'Error: ' + error.message);
                
                document.getElementById('settingsUpdateSpinner').style.display = 'none';
                
                document.getElementById('settingsStartUpdateBtn').disabled = false;
                document.getElementById('settingsStartUpdateBtn').innerHTML = '<i class="fas fa-redo me-2"></i>Retry';
                document.getElementById('settingsCloseUpdateModalBtn').disabled = false;
            });
        }, 1000);
    }, 1500);
}

function updateSettingsProgress(percent) {
    const progressBar = document.getElementById('settingsUpdateProgressBar');
    const progressText = document.getElementById('settingsProgressText');
    
    progressBar.style.width = percent + '%';
    progressBar.setAttribute('aria-valuenow', percent);
    progressText.textContent = percent + '%';
}

function addSettingsStatusMessage(type, message) {
    const container = document.getElementById('settingsUpdateStatusMessages');
    const alertClass = {
        'info': 'alert-info',
        'success': 'alert-success',
        'warning': 'alert-warning',
        'danger': 'alert-danger'
    }[type] || 'alert-info';
    
    const icon = {
        'info': 'fas fa-info-circle',
        'success': 'fas fa-check-circle',
        'warning': 'fas fa-exclamation-triangle',
        'danger': 'fas fa-times-circle'
    }[type] || 'fas fa-info-circle';
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    messageDiv.innerHTML = `
        <i class="${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}

// Personal Preferences Management
let personalPreferences = {};

// Load personal preferences on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPersonalPreferences();
});

async function loadPersonalPreferences() {
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.settings) {
            personalPreferences = data.settings;
            
            // Update toggle switches
            updateToggleSwitch('dontCreateAlertForAppointments', personalPreferences.dont_create_alert_for_appointments || false, 'dontCreateAlertForAppointmentsStatus');
            updateToggleSwitch('dontCreateNotificationForAppointments', personalPreferences.dont_create_notification_for_appointments || false, 'dontCreateNotificationForAppointmentsStatus');
            updateToggleSwitch('backToTopDisplay', personalPreferences.back_to_top_display !== false, 'backToTopDisplayStatus'); // Default true
            updateToggleSwitch('desktopDock', personalPreferences.desktop_dock_enabled !== false, 'desktopDockStatus'); // Default true
            updateToggleSwitch('mobileDock', personalPreferences.mobile_dock_enabled === true || personalPreferences.mobile_dock_enabled === '1' || personalPreferences.mobile_dock_enabled === 1, 'mobileDockStatus'); // Check explicitly
            // Update theme switch (special handling)
            const currentModeInput = document.getElementById('currentModeInput');
            if (currentModeInput) {
                currentModeInput.checked = personalPreferences.theme === 'dark';
            }
            updateToggleSwitch('pushNotificationsEnabled', personalPreferences.push_notifications_enabled || false, 'pushNotificationsEnabledStatus');
            updateToggleSwitch('dashboardRearrangeMobile', personalPreferences.dashboard_rearrange_mobile || false, 'dashboardRearrangeMobileStatus');
            
            // Load push subscriptions
            loadPushSubscriptions();
            
            // Load sidebar items
            loadSidebarItems();
        }
    } catch (error) {
        console.error('Error loading personal preferences:', error);
    }
}

function updateToggleSwitch(switchId, checked, statusId, isTheme = false) {
    const switchElement = document.getElementById(switchId);
    
    if (switchElement) {
        switchElement.checked = checked;
    }
}

async function updatePersonalPreference(key, value) {
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                [key]: value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            personalPreferences[key] = value;
            
            // If push notifications enabled, automatically subscribe current browser (same as enablePushBtn)
            if (key === 'push_notifications_enabled' && value === true) {
                // Request notification permission
                if (!('Notification' in window)) {
                    alert('Notifications are not supported in this browser.');
                    return;
                }
                
                // Request permission
                const permission = await Notification.requestPermission();
                
                if (permission === 'granted') {
                    try {
                        // Get service worker registration
                        const registration = await navigator.serviceWorker.ready;
                        
                        // Get VAPID public key
                        function getVapidPublicKey() {
                            return 'BM81HP8k4re4ObeiBgk2BSdC3FDx5Ke8-XbtPF_RbsEF5M6SC0OyHcygclxzQbPeiY8re_q6Hco16kLvol-4ozg';
                        }
                        
                        // Convert VAPID key
                        function urlBase64ToUint8Array(base64String) {
                            const padding = '='.repeat((4 - base64String.length % 4) % 4);
                            const base64 = (base64String + padding)
                                .replace(/\-/g, '+')
                                .replace(/_/g, '/');
                            
                            const rawData = window.atob(base64);
                            const outputArray = new Uint8Array(rawData.length);
                            
                            for (let i = 0; i < rawData.length; ++i) {
                                outputArray[i] = rawData.charCodeAt(i);
                            }
                            return outputArray;
                        }
                        
                        // Subscribe to push
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(getVapidPublicKey())
                        });
                        
                        // Get browser info
                        const browserInfo = navigator.userAgent;
                        const subscriptionObj = {
                            endpoint: subscription.endpoint,
                            keys: {
                                p256dh: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('p256dh')))),
                                auth: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('auth'))))
                            },
                            browser: browserInfo
                        };
                        
                        // Save subscription
                        const saveResponse = await fetch('/api/doctor/settings', {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        const saveData = await saveResponse.json();
                        
                        if (saveData.success && saveData.settings) {
                            let subscriptions = [];
                            
                            if (saveData.settings.push_subscription) {
                                if (typeof saveData.settings.push_subscription === 'string') {
                                    subscriptions = JSON.parse(saveData.settings.push_subscription);
                                } else if (Array.isArray(saveData.settings.push_subscription)) {
                                    subscriptions = saveData.settings.push_subscription;
                                }
                            }
                            
                            // Check if this subscription already exists
                            const existingIndex = subscriptions.findIndex(sub => {
                                const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                                return subEndpoint === subscriptionObj.endpoint;
                            });
                            
                            if (existingIndex >= 0) {
                                subscriptions[existingIndex] = subscriptionObj;
                            } else {
                                subscriptions.push(subscriptionObj);
                            }
                            
                            // Update settings with subscription
                            await fetch('/api/doctor/settings', {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    push_subscription: JSON.stringify(subscriptions)
                                })
                            });
                        }
                        
                        // Reload page to apply changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                        
                    } catch (error) {
                        console.error('Error subscribing to push:', error);
                        alert('Failed to enable push notifications. Please try again.');
                    }
                } else {
                    alert('Please allow notifications in your browser settings to enable push notifications.');
                }
            }
            
            // Reload push subscriptions list if push notification setting changed
            if (key === 'push_notifications_enabled') {
                setTimeout(() => {
                    loadPushSubscriptions();
                }, 2000);
            }
            
            // If dashboard rearrange setting changed, reload page to apply
            if (key === 'dashboard_rearrange_mobile') {
                // Reload page after a short delay to apply changes
                setTimeout(() => {
                    if (window.location.pathname.includes('/doctor/dashboard')) {
                        window.location.reload();
                    }
                }, 500);
            }
            
            // Apply changes immediately
            applyPersonalPreferences();
        } else {
            console.error('Error updating preference:', data.message);
            alert('Failed to update preference: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating personal preference:', error);
        alert('Failed to update preference. Please try again.');
    }
}

function applyPersonalPreferences() {
    // This will be called from main.php to apply settings
    if (window.applyPersonalPreferencesCallback) {
        window.applyPersonalPreferencesCallback(personalPreferences);
    }
}

async function loadPushSubscriptions() {
    const container = document.getElementById('pushSubscriptionsList');
    if (!container) return;
    
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.settings && data.settings.push_subscription) {
            let subscriptions = [];
            
            // Parse push_subscription
            if (typeof data.settings.push_subscription === 'string') {
                subscriptions = JSON.parse(data.settings.push_subscription);
            } else if (Array.isArray(data.settings.push_subscription)) {
                subscriptions = data.settings.push_subscription;
            }
            
            if (subscriptions.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-bell-slash me-2"></i>No subscribed browsers</div>';
                return;
            }
            
            let html = '';
            subscriptions.forEach((sub, index) => {
                const browserInfo = sub.browser || 'Unknown Browser';
                const endpoint = sub.endpoint || '';
                const endpointShort = endpoint.length > 50 ? endpoint.substring(0, 50) + '...' : endpoint;
                const endpointEncoded = encodeURIComponent(endpoint);
                
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${escapeHtml(browserInfo)}</strong>
                            <br>
                            <small class="text-muted">${escapeHtml(endpointShort)}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.preventDefault(); event.stopPropagation(); showDeletePushSubscriptionModal('${endpointEncoded}', '${escapeHtml(browserInfo)}'); return false;" title="Delete Subscription">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-bell-slash me-2"></i>No subscribed browsers</div>';
        }
    } catch (error) {
        console.error('Error loading push subscriptions:', error);
        container.innerHTML = '<div class="text-center py-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error loading subscriptions</div>';
    }
}

let deletePushSubscriptionEndpoint = null;

function showDeletePushSubscriptionModal(endpoint, browserInfo) {
    deletePushSubscriptionEndpoint = decodeURIComponent(endpoint);
    document.getElementById('deletePushSubscriptionBrowserInfo').textContent = browserInfo;
    const modal = new bootstrap.Modal(document.getElementById('deletePushSubscriptionModal'));
    modal.show();
}

async function confirmDeletePushSubscription() {
    if (!deletePushSubscriptionEndpoint) {
        return;
    }
    
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.settings && data.settings.push_subscription) {
            let subscriptions = [];
            
            if (typeof data.settings.push_subscription === 'string') {
                subscriptions = JSON.parse(data.settings.push_subscription);
            } else if (Array.isArray(data.settings.push_subscription)) {
                subscriptions = data.settings.push_subscription;
            }
            
            // Find and remove subscription by endpoint
            const index = subscriptions.findIndex(sub => {
                const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                return subEndpoint === deletePushSubscriptionEndpoint;
            });
            
            if (index >= 0) {
                subscriptions.splice(index, 1);
                
                // Update settings
                const updateResponse = await fetch('/api/doctor/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        push_subscription: JSON.stringify(subscriptions)
                    })
                });
                
                const updateData = await updateResponse.json();
                
                if (updateData.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deletePushSubscriptionModal'));
                    if (modal) {
                        modal.hide();
                    }
                    loadPushSubscriptions();
                } else {
                    alert('Failed to delete subscription: ' + (updateData.message || 'Unknown error'));
                }
            } else {
                alert('Subscription not found.');
            }
        }
    } catch (error) {
        console.error('Error deleting push subscription:', error);
        alert('Failed to delete subscription. Please try again.');
    } finally {
        deletePushSubscriptionEndpoint = null;
    }
}

async function loadSidebarItems() {
    const container = document.getElementById('sidebarItemsList');
    if (!container) return;
    
    // Define all sidebar items
    const allSidebarItems = [
        { key: 'dashboard', label: 'Dashboard', icon: 'bi-speedometer2', fixed: true },
        { key: 'calendar', label: 'Calendar', icon: 'bi-calendar3', fixed: true },
        { key: 'patients', label: 'Patients', icon: 'bi-people', fixed: true },
        { key: 'organizer', label: 'Organizer', icon: 'bi-calendar-month', fixed: false },
        { key: 'forum', label: 'Discussion', icon: 'bi-chat-dots', fixed: false },
        { key: 'drugs', label: 'Drugs Database', icon: 'bi-capsule', fixed: false },
        { key: 'payments', label: 'Financial Management', icon: 'bi-credit-card', fixed: false },
        { key: 'reports', label: 'Reports', icon: 'bi-graph-up', fixed: false },
        { key: 'media', label: 'Media', icon: 'bi-images', fixed: false },
        { key: 'glasses', label: 'Glasses Prescriptions', icon: 'bi-eyeglasses', fixed: false },
        { key: 'medications', label: 'Prescriptions', icon: 'bi-capsule', fixed: false },
        { key: 'alerts', label: 'Alerts', icon: 'bi-bell', fixed: false },
        { key: 'notes', label: 'Notes', icon: 'bi-sticky', fixed: false },
        { key: 'settings', label: 'Settings', icon: 'bi-gear', fixed: true },
        { key: 'profile', label: 'Profile', icon: 'bi-person-circle', fixed: false },
        { key: 'about', label: 'About', icon: 'bi-info-circle', fixed: true },
        { key: 'logout', label: 'Logout', icon: 'bi-box-arrow-right', fixed: true }
    ];
    
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        // Get enabled sidebar items from settings (default: all enabled)
        let enabledItems = [];
        if (data.success && data.settings && data.settings.sidebar_items_enabled) {
            if (typeof data.settings.sidebar_items_enabled === 'string') {
                enabledItems = JSON.parse(data.settings.sidebar_items_enabled);
            } else if (Array.isArray(data.settings.sidebar_items_enabled)) {
                enabledItems = data.settings.sidebar_items_enabled;
            }
        } else {
            // Default: all items enabled
            enabledItems = allSidebarItems.map(item => item.key);
        }
        
        let html = '';
        allSidebarItems.forEach(item => {
            const isEnabled = enabledItems.includes(item.key);
            const isFixed = item.fixed;
            
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi ${item.icon} me-2"></i>
                        <span>${escapeHtml(item.label)}</span>
                        ${isFixed ? '<span class="badge bg-secondary ms-2">Always Enabled</span>' : ''}
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" 
                               id="sidebarItem_${item.key}" 
                               ${isEnabled ? 'checked' : ''}
                               ${isFixed ? 'disabled' : ''}
                               onchange="updateSidebarItem('${item.key}', this.checked)">
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading sidebar items:', error);
        container.innerHTML = '<div class="text-center py-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error loading sidebar items</div>';
    }
}

async function updateSidebarItem(itemKey, enabled) {
    try {
        const response = await fetch('/api/doctor/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        // Get current enabled items
        let enabledItems = [];
        if (data.success && data.settings && data.settings.sidebar_items_enabled) {
            if (typeof data.settings.sidebar_items_enabled === 'string') {
                enabledItems = JSON.parse(data.settings.sidebar_items_enabled);
            } else if (Array.isArray(data.settings.sidebar_items_enabled)) {
                enabledItems = data.settings.sidebar_items_enabled;
            }
        }
        
        // Update the item
        if (enabled) {
            if (!enabledItems.includes(itemKey)) {
                enabledItems.push(itemKey);
            }
        } else {
            enabledItems = enabledItems.filter(key => key !== itemKey);
        }
        
        // Always include fixed items
        const fixedItems = ['dashboard', 'calendar', 'patients', 'about', 'logout'];
        fixedItems.forEach(fixedKey => {
            if (!enabledItems.includes(fixedKey)) {
                enabledItems.push(fixedKey);
            }
        });
        
        // Update settings
        const updateResponse = await fetch('/api/doctor/settings', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                sidebar_items_enabled: JSON.stringify(enabledItems)
            })
        });
        
        const updateData = await updateResponse.json();
        
        if (updateData.success) {
            personalPreferences.sidebar_items_enabled = enabledItems;
            applyPersonalPreferences();
        } else {
            alert('Failed to update sidebar item: ' + (updateData.message || 'Unknown error'));
            // Revert checkbox
            const checkbox = document.getElementById(`sidebarItem_${itemKey}`);
            if (checkbox) {
                checkbox.checked = !enabled;
            }
        }
    } catch (error) {
        console.error('Error updating sidebar item:', error);
        alert('Failed to update sidebar item. Please try again.');
        // Revert checkbox
        const checkbox = document.getElementById(`sidebarItem_${itemKey}`);
        if (checkbox) {
            checkbox.checked = !enabled;
        }
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}