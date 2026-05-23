
// Debounce function
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

// Alert Modal Functions
let currentAlertIdToEdit = null;
let originalAlertData = null; // Store original alert data for comparison

// Timepicker UI helper functions
(function() {
    let timepickerInstance = null;
    
    // Detect current theme (dark/light) - sync with main.js theme system
    function getCurrentTheme() {
        // Check documentElement class (same as main.js)
        const htmlElement = document.documentElement;
        const isDark = htmlElement.classList.contains('dark');
        
        // Return 'dark' theme for dark mode, 'basic' for light mode
        // This matches timepicker-ui theme names
        return isDark ? 'dark' : 'basic';
    }
    
    // Detect if device is mobile/touch device
    // DISABLED: Always use desktop mode regardless of device
    function isMobileDevice() {
        // Always return false to force desktop mode
        return false;
    }
    
    // Initialize timepicker
    function initTimepicker() {
        const timeInput = document.getElementById('alertTime');
        if (!timeInput) return;
        
        // Destroy existing instance if any (check if it's not already destroyed)
        if (timepickerInstance) {
            try {
                // Check if instance is still valid before destroying
                const element = timepickerInstance.getElement ? timepickerInstance.getElement() : null;
                if (element) {
                    timepickerInstance.destroy();
                }
            } catch (e) {
                // Instance might already be destroyed, ignore
            }
            timepickerInstance = null;
        }
        
        // Wait for TimepickerUI to be available
        if (typeof TimepickerUI === 'undefined') {
            setTimeout(initTimepicker, 100);
            return;
        }
        
        try {
            const currentTheme = getCurrentTheme();
            const isMobile = isMobileDevice();
            
            // Create new instance with all options
            timepickerInstance = new TimepickerUI(timeInput, {
                ui: {
                    theme: currentTheme,
                    animation: true,
                    backdrop: true,
                    mobile: isMobile, // Enable mobile mode only on mobile devices
                    enableSwitchIcon: true, // Enable switch icon for mobile/desktop toggle
                    editable: false,
                    enableScrollbar: false
                },
                clock: {
                    type: '12h',
                    incrementHours: 1,
                    incrementMinutes: 1,
                    autoSwitchToMinutes: false
                },
                labels: {
                    am: 'AM',
                    pm: 'PM',
                    ok: 'OK',
                    cancel: 'Cancel',
                    time: 'Select time',
                    mobileTime: 'Enter Time',
                    mobileHour: 'Hour',
                    mobileMinute: 'Minute'
                },
                behavior: {
                    focusInputAfterClose: false,
                    focusTrap: true,
                    delayHandler: 300
                },
                callbacks: {
                    onConfirm: function(data) {
                        // Update hidden input with 24-hour format
                        try {
                            // data can be object with time property or string
                            let timeStr = '';
                            if (typeof data === 'string') {
                                timeStr = data;
                            } else if (data && data.time) {
                                timeStr = data.time;
                            } else if (data && data.hour && data.minutes) {
                                const type = data.type || 'AM';
                                timeStr = `${data.hour}:${data.minutes} ${type}`;
                            }
                            
                            if (timeStr && timeStr !== '12:00 AM') {
                                const time24 = convert12To24(timeStr);
                                const hiddenInput = document.getElementById('alertTimeValue');
                                if (hiddenInput && time24) {
                                    hiddenInput.value = time24;
                                }
                                
                                // Also update the input value to ensure consistency
                                const timeInput = document.getElementById('alertTime');
                                if (timeInput) {
                                    timeInput.value = timeStr;
                                }
                            }
                        } catch (e) {
                            // Silent error handling
                        }
                    },
                    onUpdate: function(data) {
                        // Update hidden input in real-time
                        try {
                            // data can be object with time property or string
                            let timeStr = '';
                            if (typeof data === 'string') {
                                timeStr = data;
                            } else if (data && data.time) {
                                timeStr = data.time;
                            } else if (data && data.hour && data.minutes) {
                                const type = data.type || 'AM';
                                timeStr = `${data.hour}:${data.minutes} ${type}`;
                            }
                            
                            if (timeStr && timeStr !== '12:00 AM') {
                                const time24 = convert12To24(timeStr);
                                const hiddenInput = document.getElementById('alertTimeValue');
                                if (hiddenInput && time24) {
                                    hiddenInput.value = time24;
                                }
                            }
                        } catch (e) {
                            // Silent error handling
                        }
                    }
                }
            });
            
            // IMPORTANT: Create the timepicker after instantiation (required by timepicker-ui)
            if (timepickerInstance && typeof timepickerInstance.create === 'function') {
                timepickerInstance.create();
                
                // Mobile mode disabled - always use desktop mode
                // Touch support setup removed since we're using desktop mode only
            }
        } catch (e) {
            // Silent error handling
            timepickerInstance = null;
        }
    }
    
    // Convert 12-hour format to 24-hour format
    function convert12To24(time12) {
        if (!time12) return '';
        
        // Ensure time12 is a string
        if (typeof time12 !== 'string') {
            if (typeof time12 === 'object' && time12.time) {
                time12 = time12.time;
            } else {
                return '';
            }
        }
        
        // Trim whitespace
        time12 = time12.trim();
        
        // Parse "HH:MM AM/PM" format
        const match = time12.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
        if (!match) {
            // Try to parse as 24-hour format already
            const match24 = time12.match(/(\d{1,2}):(\d{2})/);
            if (match24) {
                const hours = String(parseInt(match24[1], 10)).padStart(2, '0');
                const minutes = match24[2];
                return `${hours}:${minutes}:00`;
            }
            return '';
        }
        
        let hours = parseInt(match[1], 10);
        const minutes = match[2];
        const period = match[3].toUpperCase();
        
        // Validate hours (1-12 for 12-hour format)
        if (hours < 1 || hours > 12) {
            return '';
        }
        
        // Convert to 24-hour format
        if (period === 'PM' && hours !== 12) {
            hours += 12;
        } else if (period === 'AM' && hours === 12) {
            hours = 0;
        }
        
        return `${String(hours).padStart(2, '0')}:${minutes}:00`;
    }
    
    // Convert 24-hour format to 12-hour format
    function convert24To12(time24) {
        if (!time24) return '12:00 AM';
        
        // Ensure time24 is a string
        if (typeof time24 !== 'string') {
            time24 = String(time24);
        }
        
        // Handle HH:mm:ss or HH:mm format
        const timeParts = time24.split(':');
        let hours = parseInt(timeParts[0], 10);
        const minutes = (timeParts[1] || '00').padStart(2, '0');
        
        // Validate hours
        if (isNaN(hours) || hours < 0 || hours > 23) {
            return '12:00 AM';
        }
        
        const period = hours >= 12 ? 'PM' : 'AM';
        if (hours === 0) {
            hours = 12;
        } else if (hours > 12) {
            hours -= 12;
        }
        
        // Format: "H:MM AM/PM" (single digit hour for 1-9, double for 10-12)
        return `${hours}:${minutes} ${period}`;
    }
    
    // Get timepicker value in 24-hour format
    function getTimepickerValue() {
        // Priority 1: Get from input value directly (timepicker updates input.value on confirm)
        const timeInput = document.getElementById('alertTime');
        if (timeInput && timeInput.value && timeInput.value !== '12:00 AM') {
            const converted = convert12To24(timeInput.value);
            if (converted) {
                return converted;
            }
        }
        
        // Priority 2: Get from timepicker instance (check if instance is valid)
        if (!timepickerInstance) {
            return null;
        }
        
        try {
            // Check if instance is still valid (not destroyed)
            if (timepickerInstance.getElement) {
                const element = timepickerInstance.getElement();
                if (!element) {
                    // Instance is destroyed, return null
                    return null;
                }
            }
            
            const value = timepickerInstance.getValue();
            if (value) {
                // timepickerInstance.getValue() returns an object like {hour, minutes, type, time}
                // Use the time property if available, otherwise build from hour/minutes/type
                let time12 = value.time || '';
                if (!time12 && value.hour && value.minutes) {
                    const type = value.type || 'AM';
                    time12 = `${value.hour}:${value.minutes} ${type}`;
                }
                
                if (time12 && time12 !== '12:00 AM') {
                    return convert12To24(time12);
                }
            }
        } catch (e) {
            // Instance might be destroyed, return null
            return null;
        }
        return null;
    }
    
    // Set timepicker value from 24-hour format
    function setTimepickerValue(time24) {
        if (!time24) return;
        
        // If instance doesn't exist, initialize it first
        if (!timepickerInstance) {
            initTimepicker();
            // Wait for initialization, then try again
            setTimeout(function() {
                if (timepickerInstance) {
                    setTimepickerValue(time24);
                } else {
                    // Fallback: set input value directly
                    const timeInput = document.getElementById('alertTime');
                    const hiddenInput = document.getElementById('alertTimeValue');
                    if (timeInput) {
                        const time12 = convert24To12(time24);
                        if (time12) {
                            timeInput.value = time12;
                        }
                    }
                    if (hiddenInput) {
                        hiddenInput.value = time24;
                    }
                }
            }, 200);
            return;
        }
        
        try {
            // Check if instance is still valid (not destroyed)
            if (timepickerInstance.getElement) {
                const element = timepickerInstance.getElement();
                if (!element) {
                    // Instance is destroyed, re-initialize
                    initTimepicker();
                    setTimeout(function() {
                        setTimepickerValue(time24);
                    }, 200);
                    return;
                }
            }
            
            // Ensure time24 is in HH:mm:ss format, extract HH:mm
            let timeStr = time24;
            if (timeStr.includes(':')) {
                const parts = timeStr.split(':');
                timeStr = `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
            }
            
            const time12 = convert24To12(timeStr + ':00'); // Add :00 for conversion
            if (time12) {
                timepickerInstance.setValue(time12);
                
                // Update hidden input
                const hiddenInput = document.getElementById('alertTimeValue');
                if (hiddenInput) {
                    // Ensure time24 is in HH:mm:ss format
                    const timeParts = time24.split(':');
                    if (timeParts.length === 2) {
                        hiddenInput.value = `${timeParts[0].padStart(2, '0')}:${timeParts[1].padStart(2, '0')}:00`;
                    } else {
                        hiddenInput.value = time24;
                    }
                }
            }
        } catch (e) {
            // Fallback: try to set input value directly
            const timeInput = document.getElementById('alertTime');
            if (timeInput) {
                try {
                    const time12 = convert24To12(time24);
                    if (time12) {
                        timeInput.value = time12;
                    }
                } catch (e2) {
                    // Silent error handling
                }
            }
        }
    }
    
    // Update theme when it changes (debounced to avoid multiple calls)
    const debouncedUpdateTheme = debounce(function() {
        if (!timepickerInstance) {
            // If no instance, initialize it
            const timeInput = document.getElementById('alertTime');
            if (timeInput) {
                initTimepicker();
            }
            return;
        }
        
        try {
            // Check if instance is still valid
            const element = timepickerInstance.getElement ? timepickerInstance.getElement() : null;
            if (!element) {
                // Instance is destroyed, re-initialize
                const timeInput = document.getElementById('alertTime');
                if (timeInput) {
                    initTimepicker();
                }
                return;
            }
            
            // Use update() method to change theme and mobile mode
            // According to docs: update({ options: newOptions })
            // Get current theme (syncs with main.js - checks documentElement class)
            const currentTheme = getCurrentTheme();
            const isMobile = isMobileDevice();
            timepickerInstance.update({
                options: {
                    ui: {
                        theme: currentTheme, // 'dark' for dark mode, 'basic' for light mode
                        mobile: isMobile, // Update mobile mode based on device
                        enableSwitchIcon: true
                    }
                }
            });
        } catch (e) {
            // If update fails, re-initialize
            const timeInput = document.getElementById('alertTime');
            if (timeInput) {
                initTimepicker();
            }
        }
    }, 300);
    
    function updateTimepickerTheme() {
        debouncedUpdateTheme();
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initTimepicker, 500);
        });
    } else {
        setTimeout(initTimepicker, 500);
    }
    
    // Re-initialize when modal is shown (desktop mode only)
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target && e.target.id === 'alertModal') {
            // Small delay to ensure DOM is ready and modal is fully visible
            const delay = 400;
            setTimeout(function() {
                const timeInput = document.getElementById('alertTime');
                if (timeInput) {
                    initTimepicker();
                }
            }, delay);
        }
    });
    
    // Listen for theme changes - sync with main.js theme toggle
    // Watch for changes to documentElement class (same as main.js applies theme)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                // Check if 'dark' class was added or removed
                const target = mutation.target;
                if (target === document.documentElement) {
                    // Theme changed, update timepicker
                    updateTimepickerTheme();
                }
            }
        });
    });
    
    // Observe documentElement for class changes (same as main.js theme system)
    if (document.documentElement) {
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
    
    // Also listen to theme toggle events from main.js if available
    // This ensures immediate update when theme is toggled
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggleInput = document.getElementById('themeToggleInput');
        if (themeToggleInput) {
            themeToggleInput.addEventListener('change', function() {
                // Small delay to ensure class is updated first
                setTimeout(function() {
                    updateTimepickerTheme();
                }, 50);
            });
        }
    });
    
    // Expose functions globally
    window.getTimepickerValue = getTimepickerValue;
    window.setTimepickerValue = setTimepickerValue;
    window.timepickerInstance = timepickerInstance;
})();

function openAlertModal(patientId, appointmentId, alertData = null) {
    // Reset to create mode or set edit mode
    currentAlertIdToEdit = alertData ? alertData.id : null;
    originalAlertData = alertData ? {...alertData} : null; // Store copy of original data
    
    // Clear patient search
    document.getElementById('alertPatientSearch').value = '';
    document.getElementById('alertPatientSearchResults').innerHTML = '';
    document.getElementById('alertPatientId').value = patientId || '';
    document.getElementById('alertAppointmentId').value = appointmentId || '';
    
    if (alertData) {
        // Edit mode - populate form with alert data
        // Check if message contains HTML tags
        const hasHtml = /<[a-z][\s\S]*>/i.test(alertData.message || '');
        if (hasHtml) {
            setAlertEditorMode('html');
            document.getElementById('alertMessageHtmlEditor').innerHTML = alertData.message || '';
        } else {
            setAlertEditorMode('text');
            document.getElementById('alertMessage').value = alertData.message || '';
        }
        document.getElementById('alertDate').value = alertData.alert_date || '';
        
        // Set timepicker value from 24-hour format
        if (alertData.alert_time && typeof setTimepickerValue === 'function') {
            setTimepickerValue(alertData.alert_time);
        } else {
            // Clear timepicker - set to default
            const timeInput = document.getElementById('alertTime');
            if (timeInput) {
                timeInput.value = '12:00 AM';
            }
            const hiddenInput = document.getElementById('alertTimeValue');
            if (hiddenInput) {
                hiddenInput.value = '';
            }
        }
        
        document.getElementById('alertRepeatCount').value = alertData.repeat_count || '1';
        document.getElementById('alertRepeatInterval').value = alertData.repeat_interval || '0';
        
        // Set patient info if available
        if (alertData.patient_id) {
            const fullName = alertData.patient_first_name && alertData.patient_last_name 
                ? `${alertData.patient_first_name} ${alertData.patient_last_name}` 
                : '';
            if (fullName) {
                document.getElementById('alertPatientSearch').value = fullName;
                document.getElementById('alertPatientId').value = alertData.patient_id;
                
                document.getElementById('alertPatientSearchResults').innerHTML = `
                    <div class="selected-patient-info alert alert-info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Selected Patient:</strong> ${fullName}<br>
                                <small>Phone: ${alertData.patient_phone || 'N/A'}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                                Change Patient
                            </button>
                        </div>
                    </div>
                `;
            }
        }
        
        // Update modal title and button
        document.getElementById('alertModalLabel').innerHTML = '<i class="bi bi-bell me-2"></i>Edit Alert';
        const saveBtn = document.querySelector('#alertModal .modal-footer .btn-primary');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Update Alert';
            saveBtn.setAttribute('onclick', 'saveAlert()');
        }
    } else {
        // Create mode - clear form
        setAlertEditorMode('text');
        document.getElementById('alertMessage').value = '';
        document.getElementById('alertMessageHtmlEditor').innerHTML = '';
        document.getElementById('alertDate').value = '';
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.timepicker !== 'undefined') {
            const timeInput = jQuery('#alertTime');
            if (timeInput.length && timeInput.data('timepicker')) {
                timeInput.timepicker('setTime', null);
            } else {
                timeInput.val('');
            }
        } else {
        document.getElementById('alertTime').value = '';
        }
        document.getElementById('alertRepeatCount').value = '1';
        document.getElementById('alertRepeatInterval').value = '0';
        
        // Check if we have patient info from the page (for patient.php)
        const currentPatientInfo = window.currentPatientInfo || null;
        
        // If patientId is provided, set patient info immediately if available, otherwise fetch
        if (patientId) {
            // If we have patient info from the page, use it immediately
            if (currentPatientInfo && currentPatientInfo.id == patientId) {
                const fullName = `${currentPatientInfo.first_name} ${currentPatientInfo.last_name}`;
                document.getElementById('alertPatientSearch').value = fullName;
                document.getElementById('alertPatientId').value = patientId;
                
                // Show selected patient info immediately
                document.getElementById('alertPatientSearchResults').innerHTML = `
                    <div class="selected-patient-info alert alert-info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Selected Patient:</strong> ${fullName}<br>
                                <small>Phone: ${currentPatientInfo.phone || 'N/A'} • Age: ${currentPatientInfo.age || 'N/A'}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                                Change Patient
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // Fetch patient info from API
                fetch(`/api/patients/${patientId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok && data.data) {
                            const patient = data.data;
                            const fullName = `${patient.first_name} ${patient.last_name}`;
                            document.getElementById('alertPatientSearch').value = fullName;
                            document.getElementById('alertPatientId').value = patientId;
                            
                            // Show selected patient info
                            document.getElementById('alertPatientSearchResults').innerHTML = `
                                <div class="selected-patient-info alert alert-info">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>Selected Patient:</strong> ${fullName}<br>
                                            <small>Phone: ${patient.phone || 'N/A'} • Age: ${patient.age || 'N/A'}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                                            Change Patient
                                        </button>
                                    </div>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        // Silent error handling
                    });
            }
        }
        
        // Update modal title and button
        document.getElementById('alertModalLabel').innerHTML = '<i class="bi bi-bell me-2"></i>Create Alert';
        const saveBtn = document.querySelector('#alertModal .modal-footer .btn-primary');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Create Alert';
            saveBtn.setAttribute('onclick', 'saveAlert()');
        }
        
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('alertDate').value = today;
        
        // Set default time to current time + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const hour24 = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const defaultTime = `${hour24}:${minutes}:00`;
        
        // Set timepicker value using timepicker-ui API
        if (typeof setTimepickerValue === 'function') {
            setTimepickerValue(defaultTime);
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    modal.show();
}

// Patient search for alert modal
function searchAlertPatients() {
    const query = document.getElementById('alertPatientSearch').value.trim();
    if (query.length < 2) {
        document.getElementById('alertPatientSearchResults').innerHTML = '';
        return;
    }
    
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayAlertPatientSearchResults(data.data);
            }
        })
        .catch(error => {
            // Silent error handling
        });
}

function displayAlertPatientSearchResults(patients) {
    const resultsContainer = document.getElementById('alertPatientSearchResults');
    
    if (patients.length === 0) {
        resultsContainer.innerHTML = '<div class="search-result-item text-muted">No patients found</div>';
        return;
    }
    
    let html = '';
    patients.forEach(patient => {
        html += `
            <div class="search-result-item" onclick="selectAlertPatient(${patient.id}, '${patient.first_name} ${patient.last_name}', '${patient.phone || ''}', ${patient.age || 'null'})">
                <div class="patient-name">${patient.first_name} ${patient.last_name}</div>
                <div class="patient-details">${patient.phone || 'N/A'} • Age: ${patient.age || 'N/A'}</div>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

function selectAlertPatient(patientId, patientName, phone, age) {
    document.getElementById('alertPatientId').value = patientId;
    document.getElementById('alertPatientSearch').value = patientName;
    document.getElementById('alertPatientSearchResults').innerHTML = `
        <div class="selected-patient-info alert alert-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>Selected Patient:</strong> ${patientName}<br>
                    <small>Phone: ${phone || 'N/A'} • Age: ${age || 'N/A'}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearAlertPatientSelection()">
                    Change Patient
                </button>
            </div>
        </div>
    `;
}

function clearAlertPatientSelection() {
    document.getElementById('alertPatientId').value = '';
    document.getElementById('alertPatientSearch').value = '';
    document.getElementById('alertPatientSearchResults').innerHTML = '';
}

// Make alertModal draggable (separate from main.php to avoid conflicts)
function initializeAlertModalDraggable() {
    /* Drag/center/animation unified in layouts/modal-kit.js. No-op. */
    return;
    const modal = document.getElementById('alertModal');
    if (!modal) return;
    
    const modalDialog = modal.querySelector('.modal-dialog');
    if (!modalDialog) return;
    
    const modalHeader = modal.querySelector('.modal-header');
    if (!modalHeader) return;
    
    let isDragging = false;
    let currentX = 0;
    let currentY = 0;
    let initialX = 0;
    let initialY = 0;
    let xOffset = 0;
    let yOffset = 0;
    
    modalHeader.style.cursor = 'move';
    
    function setTranslate(xPos, yPos, el) {
        // Get viewport dimensions
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Get modal dimensions
        const modalRect = el.getBoundingClientRect();
        const modalWidth = modalRect.width;
        const modalHeight = modalRect.height;
        
        // Get the original position (center of viewport)
        // For Bootstrap centered modal, it's typically at 50% from top (not 50px)
        const originalLeft = (viewportWidth - modalWidth) / 2;
        const originalTop = (viewportHeight - modalHeight) / 2;
        
        // Calculate boundaries relative to original position
        // Allow movement within viewport bounds for both X and Y axes
        const minX = -(originalLeft - 20); // Allow 20px from left edge
        const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
        const minY = -(originalTop - 20); // Allow 20px from top
        const maxY = viewportHeight - modalHeight - originalTop + 20; // Allow 20px from bottom
        
        // Constrain movement on both axes
        const constrainedX = Math.max(minX, Math.min(maxX, xPos));
        const constrainedY = Math.max(minY, Math.min(maxY, yPos));
        
        // Apply transform to both X and Y axes
        el.style.transform = 'translate(' + constrainedX + 'px, ' + constrainedY + 'px)';
    }
    
    function drag(e) {
        if (isDragging) {
            e.preventDefault();
            e.stopPropagation();
            
            // Calculate new position based on mouse movement on both axes
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            
            // Update offsets for both axes
            xOffset = currentX;
            yOffset = currentY;
            
            // Apply translation to both X and Y
            setTranslate(currentX, currentY, modalDialog);
        }
    }
    
    function dragEnd(e) {
        if (isDragging) {
            initialX = currentX;
            initialY = currentY;
            isDragging = false;
            modalDialog.style.transition = '';
        }
    }
    
    function dragStart(e) {
        // Don't drag if clicking on buttons or inputs
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
            return;
        }
        
        // Only start dragging if clicking on header (not on title text)
        if (e.target === modalHeader || (modalHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
            // Get current transform values to continue from current position
            const transform = modalDialog.style.transform;
            if (transform) {
                const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                if (match) {
                    xOffset = parseFloat(match[1]) || 0;
                    yOffset = parseFloat(match[2]) || 0;
                }
            }
            
            // Calculate initial position for both axes
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
            
            // Store start position to detect drag vs click
            const startX = e.clientX;
            const startY = e.clientY;
            let hasMoved = false;
            
            function checkMove(moveEvent) {
                // Check movement on both axes (X and Y)
                const deltaX = Math.abs(moveEvent.clientX - startX);
                const deltaY = Math.abs(moveEvent.clientY - startY);
                // Start dragging if moved more than 5px on either axis
                if (deltaX > 5 || deltaY > 5) {
                    hasMoved = true;
                    isDragging = true;
                    modalDialog.style.transition = 'none';
                    // Update initial positions when dragging starts
                    initialX = moveEvent.clientX - xOffset;
                    initialY = moveEvent.clientY - yOffset;
                    moveEvent.preventDefault();
                    moveEvent.stopPropagation();
                }
            }
            
            function handleMove(moveEvent) {
                if (hasMoved) {
                    drag(moveEvent);
                } else {
                    checkMove(moveEvent);
                }
            }
            
            function handleEnd(endEvent) {
                if (!hasMoved) {
                    // It was just a click, allow normal behavior
                    document.removeEventListener('mousemove', handleMove);
                    document.removeEventListener('mouseup', handleEnd);
                    return;
                }
                dragEnd(endEvent);
                document.removeEventListener('mousemove', handleMove);
                document.removeEventListener('mouseup', handleEnd);
            }
            
            document.addEventListener('mousemove', handleMove);
            document.addEventListener('mouseup', handleEnd);
        }
    }
    
    modalHeader.addEventListener('mousedown', dragStart);
    
    modal.addEventListener('hidden.bs.modal', function() {
        xOffset = 0;
        yOffset = 0;
        modalDialog.style.transform = '';
    });
}

// Initialize patient search on modal show
document.addEventListener('DOMContentLoaded', function() {
    const alertModal = document.getElementById('alertModal');
    if (alertModal) {
        const patientSearchField = document.getElementById('alertPatientSearch');
        if (patientSearchField) {
            patientSearchField.addEventListener('input', debounce(searchAlertPatients, 300));
        }
        
        alertModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('alertPatientSearch').value = '';
            document.getElementById('alertPatientSearchResults').innerHTML = '';
        });
    }
    
    initializeAlertModalDraggable();
});

// Set alert editor mode (text or HTML)
function setAlertEditorMode(mode) {
    const textEditor = document.getElementById('alertMessage');
    const htmlEditor = document.getElementById('alertMessageHtmlEditor');
    const textBtn = document.getElementById('alertEditorTextBtn');
    const htmlBtn = document.getElementById('alertEditorHtmlBtn');
    
    if (!textEditor || !htmlEditor || !textBtn || !htmlBtn) return;
    
    if (mode === 'html') {
        // Switch to HTML mode
        textEditor.style.display = 'none';
        textEditor.removeAttribute('required');
        htmlEditor.style.display = 'block';
        htmlEditor.setAttribute('required', 'required');
        
        // Transfer content if text editor has content
        if (textEditor.value && !htmlEditor.innerHTML.trim()) {
            htmlEditor.innerHTML = escapeHtml(textEditor.value).replace(/\n/g, '<br>');
        }
        
        // Update button states
        textBtn.classList.remove('active');
        htmlBtn.classList.add('active');
    } else {
        // Switch to text mode
        htmlEditor.style.display = 'none';
        htmlEditor.removeAttribute('required');
        textEditor.style.display = 'block';
        textEditor.setAttribute('required', 'required');
        
        // Transfer content if HTML editor has content
        if (htmlEditor.innerHTML && !textEditor.value) {
            // Strip HTML tags and convert <br> to newlines
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlEditor.innerHTML;
            textEditor.value = tempDiv.textContent || tempDiv.innerText || '';
        }
        
        // Update button states
        htmlBtn.classList.remove('active');
        textBtn.classList.add('active');
    }
}

// Escape HTML function
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function saveAlert() {
    const form = document.getElementById('alertForm');
    if (!form || !form.checkValidity()) {
        if (form) {
            form.reportValidity();
        }
        return;
    }
    
    const alertDate = document.getElementById('alertDate').value;
    let alertTime = '';
    
    // Priority 1: Get from timepicker instance directly (most reliable)
    if (typeof getTimepickerValue === 'function') {
        alertTime = getTimepickerValue();
    }
    
    // Priority 2: Parse from input value (12-hour format) - timepicker updates this on confirm
    if (!alertTime) {
        const timeInput = document.getElementById('alertTime');
        if (timeInput && timeInput.value && timeInput.value !== '12:00 AM') {
            // Convert 12-hour to 24-hour
            const match = timeInput.value.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
            if (match) {
                let hours = parseInt(match[1], 10);
                const minutes = match[2];
                const period = match[3].toUpperCase();
                
                if (period === 'PM' && hours !== 12) {
                    hours += 12;
                } else if (period === 'AM' && hours === 12) {
                    hours = 0;
                }
                
                alertTime = `${String(hours).padStart(2, '0')}:${minutes}:00`;
            }
        }
    }
    
    // Priority 3: Fallback to hidden input (only if timepicker and input are not available)
    if (!alertTime) {
        const hiddenInput = document.getElementById('alertTimeValue');
        if (hiddenInput && hiddenInput.value) {
            alertTime = hiddenInput.value;
        }
    }
    
    if (!alertDate || !alertTime) {
        if (typeof showToast === 'function') {
            showToast('error', 'Error', 'Please fill in all required fields.');
        }
        return;
    }
    
    // Validate time format (HH:mm:ss)
    const timePattern = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]:00$/;
    if (!timePattern.test(alertTime)) {
        if (typeof showToast === 'function') {
            showToast('error', 'Error', 'Please select a valid time.');
        }
        return;
    }
    
    
    // Create date (alertTime is already in 24-hour format, no timezone conversion needed)
    // Use local time by creating date string without timezone
    const alertDateTime = new Date(alertDate + 'T' + alertTime);
    const isFuture = alertDateTime > new Date();
    const wasInactive = originalAlertData && (originalAlertData.is_active == 0 || originalAlertData.is_dismissed == 1);
    
    // Get message from appropriate editor (text or HTML)
    let alertMessage = '';
    const htmlEditor = document.getElementById('alertMessageHtmlEditor');
    const textEditor = document.getElementById('alertMessage');
    
    if (htmlEditor && htmlEditor.style.display !== 'none') {
        // HTML mode
        alertMessage = htmlEditor.innerHTML;
    } else {
        // Text mode
        alertMessage = textEditor.value;
    }
    
    const formData = {
        patient_id: document.getElementById('alertPatientId').value || null,
        appointment_id: document.getElementById('alertAppointmentId').value || null,
        message: alertMessage,
        alert_date: alertDate,
        alert_time: alertTime,
        repeat_count: parseInt(document.getElementById('alertRepeatCount').value) || 1,
        repeat_interval: parseInt(document.getElementById('alertRepeatInterval').value) || 0
    };
    
    const isEditMode = currentAlertIdToEdit !== null;
    
    if (isEditMode && isFuture && wasInactive) {
        formData.is_active = 1;
        formData.is_dismissed = 0;
    } else if (isEditMode) {
        formData.is_active = originalAlertData ? (originalAlertData.is_active || 0) : 1;
        formData.is_dismissed = originalAlertData ? (originalAlertData.is_dismissed || 0) : 0;
    }
    
    const url = isEditMode ? '/api/alerts/' + currentAlertIdToEdit : '/api/alerts';
    const method = isEditMode ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            const modalElement = document.getElementById('alertModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
            
            currentAlertIdToEdit = null;
            originalAlertData = null;
            
            if (typeof showToast === 'function') {
                const title = isEditMode ? 'Alert Updated' : 'Alert Created';
                const message = isEditMode ? 'The alert has been updated successfully.' : 'The alert has been added to your notifications.';
                showToast('success', title, message);
            }
            
            if (typeof loadAlerts === 'function') {
                loadAlerts();
            }
            
            if (typeof loadPatientAlerts === 'function') {
                loadPatientAlerts();
            }
        } else {
            if (typeof showToast === 'function') {
                const errorMsg = data.message || 'Failed to ' + (isEditMode ? 'update' : 'create') + ' alert';
                showToast('error', 'Error', errorMsg);
            }
        }
    })
    .catch(function(error) {
        if (typeof showToast === 'function') {
            const errorMsg = 'Failed to ' + (isEditMode ? 'update' : 'create') + ' alert. Please try again.';
            showToast('error', 'Error', errorMsg);
        }
    });
}
