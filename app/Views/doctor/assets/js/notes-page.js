/* ============================================================================
 * Notes board page logic — EXTRACTED from notes/index.php on 2026-06-10.
 *
 * Verbatim move of the page's single inline <script> (no IIFE wrap) so every
 * inline onclick handler in the note widgets (deleteNote/changeNoteColor/
 * createAlertFromNote/bringToFront/toggleColorPicker/showNoteAlertPicker/…)
 * keeps resolving against the global scope exactly as before.
 *
 * Loaded by notes/index.php (standalone page AND when that view is embedded
 * into the dashboard via $notesEmbedded). Listens on window.NotesSync
 * (__notesPageSyncBound guard) so drawer/dashboard stay live-synced; emits with
 * __notesPageSelfSync self-suppression. Uses window.NoteBG + /api/notes.
 * Keep STANDALONE — no new deps on dashboard.js. Mirror any change to ortho.
 * ========================================================================== */

// Color picker for new notes - default is warning yellow
let currentNoteColor = '#fbbf24';
let currentNoteColorClass = 'warning';

// Color options mapping
const colorMap = {
    'white': { bg: '#ffffff', class: 'white', text: 'dark-text' },
    'red': { bg: '#ef4444', class: 'red', text: 'light-text' },
    'black': { bg: '#1e293b', class: 'black', text: 'light-text' },
    'dodgerblue': { bg: '#1e90ff', class: 'dodgerblue', text: 'light-text' },
    'warning': { bg: '#fbbf24', class: 'warning', text: 'dark-text' },
    'success': { bg: '#10b981', class: 'success', text: 'light-text' }
};

// Color option click handlers
document.querySelectorAll('.color-option').forEach(option => {
    option.addEventListener('click', function() {
        // Remove active class from all options
        document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
        
        // Add active class to clicked option
        this.classList.add('active');
        
        // Update current color
        currentNoteColor = this.getAttribute('data-bg');
        currentNoteColorClass = this.getAttribute('data-color');
    });
});

// Get text color based on background brightness
function getTextColor(backgroundColor) {
    // Glass gradient presets always read on light text.
    if (window.NoteBG && window.NoteBG.isPreset(backgroundColor)) return 'light-text';
    // Convert hex to RGB
    const hex = String(backgroundColor || '').replace('#', '');
    if (hex.length < 6) return 'dark-text';
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    
    // Calculate brightness
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    
    // Return light or dark text
    return brightness > 128 ? 'dark-text' : 'light-text';
}

// Get color class from background color
function getColorClass(backgroundColor) {
    // Gradient preset tokens (NoteBG) aren't in the legacy hex map.
    if (window.NoteBG && window.NoteBG.isPreset(backgroundColor)) return 'warning';
    for (const [key, value] of Object.entries(colorMap)) {
        if (value.bg.toLowerCase() === backgroundColor.toLowerCase()) {
            return value.class;
        }
    }
    // Default to warning if color not found
    return 'warning';
}

// Create note widget
function createNoteWidget(note) {
    const bgColor = note.background_color || '#fbbf24';
    const colorClass = getColorClass(bgColor);
    const isGlass = !!(window.NoteBG && window.NoteBG.isPreset(bgColor));
    const textColorClass = isGlass ? 'light-text' : (colorMap[colorClass]?.text || 'dark-text');
    
    const widget = document.createElement('div');
    widget.className = `note-widget color-${colorClass} ${textColorClass}`;
    widget.id = `note-${note.id}`;
    if (isGlass) window.NoteBG.apply(widget, bgColor);
    widget.style.left = `${note.position_x || 0}px`;
    widget.style.top = `${note.position_y || 0}px`;
    widget.style.width = `${note.width || 300}px`;
    widget.style.height = `${note.height || 200}px`;
    widget.style.zIndex = note.z_index || 1;
    widget.setAttribute('data-bg-color', bgColor);
    widget.setAttribute('data-color-class', colorClass);
    widget.setAttribute('data-note-id', note.id);
    
    widget.innerHTML = `
        <div class="note-widget-header" onmousedown="startDrag(event, ${note.id})" ontouchstart="startDrag(event, ${note.id})">
            <input type="text" class="note-widget-title" placeholder="Note title..." value="${note.title || ''}" 
                   data-note-id="${note.id}" onblur="updateNoteTitle(${note.id}, this.value)">
            <div class="note-widget-actions">
                <div class="note-color-picker-wrapper" style="position: relative;">
                    <button class="note-widget-btn" onclick="toggleColorPicker(${note.id}, event)" title="Change color">
                        <i class="bi bi-palette"></i>
                    </button>
                    <div class="note-color-picker-dropdown" id="colorPicker-${note.id}" style="display: none;">
                        <div class="color-option-dropdown white" data-color="white" data-bg="#ffffff" onclick="changeNoteColor(${note.id}, '#ffffff')" title="White"></div>
                        <div class="color-option-dropdown red" data-color="red" data-bg="#ef4444" onclick="changeNoteColor(${note.id}, '#ef4444')" title="Red"></div>
                        <div class="color-option-dropdown black" data-color="black" data-bg="#1e293b" onclick="changeNoteColor(${note.id}, '#1e293b')" title="Black"></div>
                        <div class="color-option-dropdown dodgerblue" data-color="dodgerblue" data-bg="#1e90ff" onclick="changeNoteColor(${note.id}, '#1e90ff')" title="Dodger Blue"></div>
                        <div class="color-option-dropdown warning" data-color="warning" data-bg="#fbbf24" onclick="changeNoteColor(${note.id}, '#fbbf24')" title="Warning Yellow"></div>
                        <div class="color-option-dropdown success" data-color="success" data-bg="#10b981" onclick="changeNoteColor(${note.id}, '#10b981')" title="Success Green"></div>
                        ${(window.NoteBG ? window.NoteBG.PRESETS : []).map(function (p) {
                            return '<div class="color-option-dropdown color-option--grad" title="' + p.label + '" style="background:' + p.css + '" onclick="changeNoteColor(' + note.id + ', \'' + p.id + '\')"></div>';
                        }).join('')}
                    </div>
                </div>
                <div class="note-alert-wrapper" style="position: relative;">
                    <button class="note-widget-btn" onclick="showNoteAlertPicker(${note.id}, event)" title="Create alert from this note">
                        <i class="bi bi-bell"></i>
                    </button>
                    <div class="note-alert-picker-dropdown" id="alertPicker-${note.id}" style="display: none;">
                        <div class="alert-picker-content">
                            <div class="mb-2">
                                <label class="form-label small">Date:</label>
                                <input type="date" class="form-control form-control-sm" id="alertDate-${note.id}" value="${note.alert ? note.alert.alert_date : new Date().toISOString().split('T')[0]}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Time:</label>
                                <div class="d-flex gap-1 align-items-center">
                                    <input type="number" class="form-control form-control-sm" id="alertHour-${note.id}" min="1" max="12" value="${note.alert ? (parseInt(note.alert.alert_time.split(':')[0]) % 12 || 12) : (new Date().getHours() % 12 || 12)}" style="width: 60px;">
                                    <span>:</span>
                                    <input type="number" class="form-control form-control-sm" id="alertMinute-${note.id}" min="0" max="59" value="${note.alert ? note.alert.alert_time.split(':')[1] : new Date().getMinutes().toString().padStart(2, '0')}" style="width: 60px;">
                                    <select class="form-select form-select-sm" id="alertAmPm-${note.id}" style="width: 70px;">
                                        <option value="AM" ${note.alert ? (parseInt(note.alert.alert_time.split(':')[0]) < 12 ? 'selected' : '') : (new Date().getHours() < 12 ? 'selected' : '')}>AM</option>
                                        <option value="PM" ${note.alert ? (parseInt(note.alert.alert_time.split(':')[0]) >= 12 ? 'selected' : '') : (new Date().getHours() >= 12 ? 'selected' : '')}>PM</option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-primary w-100" onclick="createAlertFromNote(${note.id})">
                                <i class="bi bi-check-circle me-1"></i>${note.alert ? 'Update Alert' : 'Create Alert'}
                            </button>
                        </div>
                    </div>
                </div>
                <button class="note-widget-btn" onclick="bringToFront(${note.id})" title="Bring to front">
                    <i class="bi bi-layers"></i>
                </button>
                <button class="note-widget-btn delete" onclick="deleteNote(${note.id})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <div class="note-widget-body">
            <div class="note-widget-content" 
                 contenteditable="true"
                 data-placeholder="Write your note here... (Use @ for patients, # for appointments, $ for drugs)"
                 data-note-id="${note.id}" 
                 onblur="updateNoteContent(${note.id}, this.innerHTML)">${note.content || ''}</div>
        </div>
        <div class="note-widget-footer">
            <span>Created: ${new Date(note.created_at).toLocaleDateString()}</span>
            <span>Updated: ${new Date(note.updated_at).toLocaleDateString()}</span>
            ${note.alert ? `<span class="note-alert-status"><i class="bi bi-bell-fill me-1"></i>Alert: ${new Date(note.alert.alert_date).toLocaleDateString()} ${(function() { const [h, m] = note.alert.alert_time.split(':'); const h12 = parseInt(h) % 12 || 12; const ampm = parseInt(h) < 12 ? 'AM' : 'PM'; return h12 + ':' + m + ' ' + ampm; })()}</span>` : ''}
        </div>
        <div class="note-widget-resize" onmousedown="startResize(event, ${note.id})" ontouchstart="startResize(event, ${note.id})"></div>
    `;
    
    return widget;
}

// Load notes
async function loadNotes() {
    try {
        const response = await fetch('/api/notes', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-cache'
        });
        
        // Check if response is ok
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error loading notes - HTTP:', response.status, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Error loading notes - Invalid content type:', contentType, text);
            throw new Error('Response is not JSON');
        }
        
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('notesContainer');
            const emptyState = document.getElementById('emptyState');

            if (!container) {
                console.error('Notes container not found');
                return;
            }

            // Check if we have notes
            if (!data.notes || data.notes.length === 0) {
                // No notes - clear all widgets but keep emptyState
                const widgets = container.querySelectorAll('.note-widget');
                widgets.forEach(widget => widget.remove());
                
                // Show empty state
                if (emptyState) {
                    emptyState.style.display = 'block';
                }
                return;
            }

            // We have notes - hide empty state first
            if (emptyState) {
                emptyState.style.display = 'none';
            }
            
            // Clear existing widgets (but keep emptyState)
            const widgets = container.querySelectorAll('.note-widget');
            widgets.forEach(widget => widget.remove());
            
            // Add notes
            data.notes.forEach(note => {
                const widget = createNoteWidget(note);
                container.appendChild(widget);
                
                // Add click listener to widget to hide autocomplete portal when clicking anywhere on widget
                widget.addEventListener('mousedown', function(event) {
                    const target = event.target;
                    // Don't hide if clicking on contenteditable (autocomplete should work there)
                    const contentEditable = widget.querySelector('.note-widget-content[contenteditable="true"]');
                    if (contentEditable && (contentEditable.contains(target) || contentEditable === target)) {
                        return; // Allow autocomplete to work in contenteditable
                    }
                    // Hide autocomplete portal when clicking anywhere else on the widget
                    hideAutocomplete();
                });
                
                // Initialize autocomplete for this contenteditable
                const contentEditable = widget.querySelector('.note-widget-content[contenteditable="true"]');
                if (contentEditable) {
                    // Add click listener to contenteditable to hide autocomplete portal when clicking
                    // (but not when clicking on autocomplete items or links)
                    contentEditable.addEventListener('mousedown', function(event) {
                        const target = event.target;
                        // Don't hide if clicking on autocomplete items (links, badges) or autocomplete portal
                        const isAutocompleteItem = target.closest('a[data-type], span[data-type]');
                        const isAutocompletePortal = target.closest('.note-autocomplete-portal');
                        
                        if (!isAutocompleteItem && !isAutocompletePortal) {
                            // Check if cursor is at a position with trigger symbol
                            const selection = window.getSelection();
                            if (selection.rangeCount > 0) {
                                const range = selection.getRangeAt(0);
                                const fullRange = document.createRange();
                                fullRange.selectNodeContents(contentEditable);
                                fullRange.setEnd(range.startContainer, range.startOffset);
                                const textBeforeCursor = fullRange.toString();
                                const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
                                
                                // Only hide if there's no active trigger symbol
                                if (!match) {
                                    hideAutocomplete();
                                }
                            } else {
                                hideAutocomplete();
                            }
                        }
                    });
                    
                    initAutocomplete(contentEditable);
                }
            });
            
            // Add click listener to notes-container to hide autocomplete portal
            if (container) {
                container.addEventListener('mousedown', function(event) {
                    const target = event.target;
                    // Don't hide if clicking on contenteditable or autocomplete portal
                    const contentEditable = target.closest('.note-widget-content[contenteditable="true"]');
                    const isAutocompletePortal = target.closest('.note-autocomplete-portal');
                    
                    if (!contentEditable && !isAutocompletePortal) {
                        hideAutocomplete();
                    }
                });
            }
            
            // Initialize drug badge click handlers after loading notes
            initDrugBadges();
        } else {
            console.error('Error loading notes:', data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading notes:', error);
        // Don't show alert on dashboard load - it might be called from other pages
        if (window.location.pathname.includes('/doctor/notes')) {
            console.error('Failed to load notes:', error.message);
        }
    }
}

// Add new note
document.getElementById('addNoteBtn').addEventListener('click', async function() {
    const container = document.getElementById('notesContainer');
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    // Get max z-index
    const existingNotes = container.querySelectorAll('.note-widget');
    let maxZIndex = 0;
    existingNotes.forEach(note => {
        const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
        if (zIndex > maxZIndex) maxZIndex = zIndex;
    });
    
    // Calculate position (center of visible area)
    const containerRect = container.getBoundingClientRect();
    const isMobile = window.innerWidth <= 768;
    const widgetWidth = isMobile ? 250 : 400;
    const widgetHeight = isMobile ? 180 : 300;
    const x = Math.max(0, (containerRect.width / 2) - (widgetWidth / 2));
    const y = Math.max(0, (containerRect.height / 2) - (widgetHeight / 2));
    
    try {
        const response = await fetch('/api/notes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: '',
                content: '',
                background_color: currentNoteColor,
                position_x: x,
                position_y: y,
                width: widgetWidth,
                height: widgetHeight,
                z_index: maxZIndex + 1
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Reload all notes to get the new one with full data
            loadNotes();
            if (window.NotesSync) {
                window.__notesPageSelfSync = true;
                try { window.NotesSync.emit('board', { action: 'create', id: data.note ? data.note.id : null }); }
                finally { window.__notesPageSelfSync = false; }
            }
        }
    } catch (error) {
        console.error('Error creating note:', error);
        alert('Failed to create note. Please try again.');
    }
});

// Drag functionality
let isDragging = false;
let currentDragNote = null;
let dragOffset = { x: 0, y: 0 };

function startDrag(event, noteId) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'BUTTON') {
        return;
    }
    
    // Support both mouse and touch events
    const isTouch = event.type === 'touchstart';
    const clientX = isTouch ? event.touches[0].clientX : event.clientX;
    const clientY = isTouch ? event.touches[0].clientY : event.clientY;
    
    isDragging = true;
    currentDragNote = noteId;
    const widget = document.getElementById(`note-${noteId}`);
    const rect = widget.getBoundingClientRect();
    const containerRect = document.getElementById('notesContainer').getBoundingClientRect();
    
    dragOffset.x = clientX - rect.left;
    dragOffset.y = clientY - rect.top;
    
    widget.classList.add('dragging');
    bringToFront(noteId);
    
    // Add both mouse and touch event listeners
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', onDrag, { passive: false });
    document.addEventListener('touchend', stopDrag);
    
    event.preventDefault();
}

function onDrag(event) {
    if (!isDragging || !currentDragNote) return;
    
    // Support both mouse and touch events
    const isTouch = event.type === 'touchmove';
    const clientX = isTouch ? event.touches[0].clientX : event.clientX;
    const clientY = isTouch ? event.touches[0].clientY : event.clientY;
    
    const widget = document.getElementById(`note-${currentDragNote}`);
    const container = document.getElementById('notesContainer');
    const containerRect = container.getBoundingClientRect();
    
    let x = clientX - containerRect.left - dragOffset.x;
    let y = clientY - containerRect.top - dragOffset.y;
    
    // Constrain to container bounds (account for mobile)
    const isMobile = window.innerWidth <= 768;
    const maxX = containerRect.width - widget.offsetWidth;
    const maxY = containerRect.height - widget.offsetHeight;
    x = Math.max(0, Math.min(x, maxX));
    y = Math.max(0, Math.min(y, maxY));
    
    widget.style.left = `${x}px`;
    widget.style.top = `${y}px`;
    
    if (isTouch) {
        event.preventDefault();
    }
}

function stopDrag() {
    if (isDragging && currentDragNote) {
        const widget = document.getElementById(`note-${currentDragNote}`);
        widget.classList.remove('dragging');
        
        // Save position
        updateNotePosition(
            currentDragNote,
            parseInt(widget.style.left),
            parseInt(widget.style.top)
        );
        
        isDragging = false;
        currentDragNote = null;
    }
    
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', onDrag);
    document.removeEventListener('touchend', stopDrag);
}

// Resize functionality
let isResizing = false;
let currentResizeNote = null;
let resizeStart = { x: 0, y: 0, width: 0, height: 0 };

function startResize(event, noteId) {
    // Support both mouse and touch events
    const isTouch = event.type === 'touchstart';
    const clientX = isTouch ? event.touches[0].clientX : event.clientX;
    const clientY = isTouch ? event.touches[0].clientY : event.clientY;
    
    isResizing = true;
    currentResizeNote = noteId;
    const widget = document.getElementById(`note-${noteId}`);
    const rect = widget.getBoundingClientRect();
    
    resizeStart.x = clientX;
    resizeStart.y = clientY;
    resizeStart.width = rect.width;
    resizeStart.height = rect.height;
    
    bringToFront(noteId);
    
    // Add both mouse and touch event listeners
    document.addEventListener('mousemove', onResize);
    document.addEventListener('mouseup', stopResize);
    document.addEventListener('touchmove', onResize, { passive: false });
    document.addEventListener('touchend', stopResize);
    
    event.preventDefault();
    event.stopPropagation();
}

function onResize(event) {
    if (!isResizing || !currentResizeNote) return;
    
    // Support both mouse and touch events
    const isTouch = event.type === 'touchmove';
    const clientX = isTouch ? event.touches[0].clientX : event.clientX;
    const clientY = isTouch ? event.touches[0].clientY : event.clientY;
    
    const widget = document.getElementById(`note-${currentResizeNote}`);
    const container = document.getElementById('notesContainer');
    const containerRect = container.getBoundingClientRect();
    const widgetRect = widget.getBoundingClientRect();
    
    const deltaX = clientX - resizeStart.x;
    const deltaY = clientY - resizeStart.y;
    
    if (isTouch) {
        event.preventDefault();
    }
    
    let newWidth = resizeStart.width + deltaX;
    let newHeight = resizeStart.height + deltaY;
    
    // Constrain to container and min size (smaller on mobile)
    const isMobile = window.innerWidth <= 768;
    const minWidth = isMobile ? 250 : 300;
    const minHeight = isMobile ? 180 : 200;
    newWidth = Math.max(minWidth, Math.min(newWidth, containerRect.width - widgetRect.left));
    newHeight = Math.max(minHeight, Math.min(newHeight, containerRect.height - widgetRect.top));
    
    widget.style.width = `${newWidth}px`;
    widget.style.height = `${newHeight}px`;
}

function stopResize() {
    if (isResizing && currentResizeNote) {
        const widget = document.getElementById(`note-${currentResizeNote}`);
        
        // Save size
        updateNoteSize(
            currentResizeNote,
            parseInt(widget.style.width),
            parseInt(widget.style.height)
        );
        
        isResizing = false;
        currentResizeNote = null;
    }
    
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
    document.removeEventListener('touchmove', onResize);
    document.removeEventListener('touchend', stopResize);
}

// Bring to front
function bringToFront(noteId) {
    const widget = document.getElementById(`note-${noteId}`);
    const container = document.getElementById('notesContainer');
    const allNotes = container.querySelectorAll('.note-widget');
    
    let maxZIndex = 0;
    allNotes.forEach(note => {
        const zIndex = parseInt(window.getComputedStyle(note).zIndex) || 0;
        if (zIndex > maxZIndex) maxZIndex = zIndex;
    });
    
    widget.style.zIndex = maxZIndex + 1;
    updateNoteZIndex(noteId, maxZIndex + 1);
}

// Update functions
async function updateNoteTitle(noteId, title) {
    await updateNote(noteId, { title });
}

async function updateNoteContent(noteId, content) {
    // content is already HTML from contenteditable innerHTML
    await updateNote(noteId, { content: content });
}

async function updateNotePosition(noteId, x, y) {
    await updateNote(noteId, { position_x: x, position_y: y });
}

async function updateNoteSize(noteId, width, height) {
    await updateNote(noteId, { width, height });
}

async function updateNoteZIndex(noteId, zIndex) {
    await updateNote(noteId, { z_index: zIndex });
}

async function updateNote(noteId, data) {
    try {
        const response = await fetch(`/api/notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Network error' }));
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to update note');
        }
    } catch (error) {
        console.error('Error updating note:', error);
        // Don't show alert for every update - it's too frequent
        // Only log to console
    }
}

// Delete note
async function deleteNote(noteId) {
    // Show confirmation modal
    showDeleteConfirmModal(noteId);
}

// Show delete confirmation modal
function showDeleteConfirmModal(noteId) {
    const modal = document.getElementById('deleteNoteModal');
    if (!modal) {
        // Create modal if it doesn't exist
        const modalHtml = `
            <div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-labelledby="deleteNoteModalLabel" aria-hidden="true" style="z-index: 99999;">
                <div class="modal-dialog modal-dialog-centered" style="z-index: 100000;">
                    <div class="modal-content" style="z-index: 100001;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteNoteModalLabel" style="color: var(--text);">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                Delete Note
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this note? This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                <i class="bi bi-trash me-2"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    const modalInstance = new bootstrap.Modal(document.getElementById('deleteNoteModal'));
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Remove previous event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add new event listener
    newConfirmBtn.addEventListener('click', async function() {
        await performDelete(noteId);
        modalInstance.hide();
    });
    
    modalInstance.show();
}

// Perform the actual delete
async function performDelete(noteId) {
    try {
        const response = await fetch(`/api/notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        });
        
        // Check if response is ok
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Network error' }));
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            if (window.NotesSync) {
                window.__notesPageSelfSync = true;
                try { window.NotesSync.emit('board', { action: 'delete', id: noteId }); }
                finally { window.__notesPageSelfSync = false; }
            }
            const widget = document.getElementById(`note-${noteId}`);
            if (widget) {
                widget.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                widget.style.opacity = '0';
                widget.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    widget.remove();
                    
                    // Check if empty - verify after removal
                    const container = document.getElementById('notesContainer');
                    const emptyState = document.getElementById('emptyState');
                    
                    if (container && emptyState) {
                        // Double check: count remaining widgets
                        const remainingWidgets = container.querySelectorAll('.note-widget');
                        if (remainingWidgets.length === 0) {
                            emptyState.style.display = 'block';
                        } else {
                            emptyState.style.display = 'none';
                        }
                    }
                }, 300);
            } else {
                // Widget not found in DOM, but delete was successful
                // Reload notes to sync state
                loadNotes();
            }
        } else {
            throw new Error(data.message || 'Failed to delete note');
        }
    } catch (error) {
        console.error('Error deleting note:', error);
        alert('Failed to delete note: ' + error.message);
    }
}

// Toggle color picker dropdown
function toggleColorPicker(noteId, event) {
    event.stopPropagation();
    
    // Close all other color pickers
    document.querySelectorAll('.note-color-picker-dropdown').forEach(picker => {
        if (picker.id !== `colorPicker-${noteId}`) {
            picker.style.display = 'none';
        }
    });
    
    // Toggle current picker
    const picker = document.getElementById(`colorPicker-${noteId}`);
    if (picker) {
        if (picker.style.display === 'none' || !picker.style.display) {
            picker.style.display = 'flex';
            // Close on outside click
            setTimeout(() => {
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && !e.target.closest(`#colorPicker-${noteId}`)) {
                        picker.style.display = 'none';
                        document.removeEventListener('click', closePicker);
                    }
                });
            }, 10);
        } else {
            picker.style.display = 'none';
        }
    }
}

// Change note color
function changeNoteColor(noteId, color) {
    // Close color picker
    const picker = document.getElementById(`colorPicker-${noteId}`);
    if (picker) {
        picker.style.display = 'none';
    }
    
    const widget = document.getElementById(`note-${noteId}`);
    if (!widget) return;
    
    const colorClass = getColorClass(color);
    const isGlass = !!(window.NoteBG && window.NoteBG.isPreset(color));
    const textColorClass = isGlass ? 'light-text' : (colorMap[colorClass]?.text || 'dark-text');
    
    // Remove all color classes
    widget.classList.remove('color-white', 'color-red', 'color-black', 'color-dodgerblue', 'color-warning', 'color-success');
    widget.classList.remove('light-text', 'dark-text');
    
    // Add new color classes
    widget.classList.add(`color-${colorClass}`);
    widget.classList.add(textColorClass);
    // Gradient / glassmorphism presets paint via NoteBG; clear it for plain hex.
    if (window.NoteBG) {
        if (isGlass) window.NoteBG.apply(widget, color);
        else window.NoteBG.clear(widget);
    }
    
    widget.setAttribute('data-bg-color', color);
    widget.setAttribute('data-color-class', colorClass);
    
    // Save to database immediately
    updateNote(noteId, { background_color: color });
    if (window.NotesSync) {
        window.__notesPageSelfSync = true;
        try { window.NotesSync.emit('board', { action: 'update', id: noteId }); }
        finally { window.__notesPageSelfSync = false; }
    }
}

// =====================================================================
// Quick notes on the board (merged view). Quick notes (drawer / Cmd+K store)
// are rendered here as lightweight, default-placed widgets so a note added in
// ANY surface appears here too. Their mutations route to /api/quick-notes via
// NotesBridge — separate from the board-note (/api/notes) handlers above so the
// canvas drag/resize/alert logic stays untouched. Quick notes have no
// position/size columns, so these widgets are statically cascaded.
function qpEsc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function qpEmit(action, id) {
    if (!window.NotesSync) return;
    window.__notesPageSelfSync = true;
    try { window.NotesSync.emit('quick', { action: action, id: id }); }
    finally { window.__notesPageSelfSync = false; }
}

function qpRec(id) { return { origin: 'quick', id: id, raw: {} }; }

async function qpUpdateTitle(id, title) {
    try { await window.NotesBridge.save(qpRec(id), { title: title }); qpEmit('update', id); }
    catch (e) { console.error('quick title update failed', e); }
}
async function qpUpdateContent(id, text) {
    try { await window.NotesBridge.save(qpRec(id), { body: text }); qpEmit('update', id); }
    catch (e) { console.error('quick content update failed', e); }
}
function qpChangeColor(id, color) {
    var picker = document.getElementById('qpColorPicker-' + id);
    if (picker) picker.style.display = 'none';
    var widget = document.getElementById('quicknote-' + id);
    if (widget) {
        var colorClass = getColorClass(color);
        var isGlass = !!(window.NoteBG && window.NoteBG.isPreset(color));
        var textColorClass = isGlass ? 'light-text' : ((colorMap[colorClass] && colorMap[colorClass].text) || 'dark-text');
        widget.classList.remove('color-white', 'color-red', 'color-black', 'color-dodgerblue', 'color-warning', 'color-success', 'light-text', 'dark-text');
        widget.classList.add('color-' + colorClass, textColorClass);
        if (window.NoteBG) { if (isGlass) window.NoteBG.apply(widget, color); else window.NoteBG.clear(widget); }
        widget.setAttribute('data-bg-color', color);
    }
    window.NotesBridge.save(qpRec(id), { background_color: color })
        .then(function () { qpEmit('update', id); })
        .catch(function (e) { console.error('quick color update failed', e); });
}
function qpTogglePin(id, makePinned, btn) {
    window.NotesBridge.setPinned(qpRec(id), makePinned)
        .then(function () {
            if (btn) {
                btn.setAttribute('onclick', 'qpTogglePin(' + id + ', ' + (!makePinned) + ', this)');
                btn.title = makePinned ? 'Unpin' : 'Pin';
                var ic = btn.querySelector('i');
                if (ic) ic.className = 'bi ' + (makePinned ? 'bi-pin-angle-fill' : 'bi-pin-angle');
            }
            qpEmit('pin', id);
        })
        .catch(function (e) { console.error('quick pin failed', e); });
}
function qpDelete(id) {
    var _t = (window.V11I18n && window.V11I18n.t) ? window.V11I18n.t.bind(window.V11I18n) : function (k, fb) { return fb; };
    if (window.mkConfirmModal) {
        window.mkConfirmModal({
            title: _t('note.delete_title', 'Delete note'),
            message: _t('note.delete_msg', 'This quick note will be permanently removed.'),
            confirmText: _t('modal.delete', 'Delete'),
            cancelText: _t('modal.cancel', 'Cancel'),
            confirmClass: 'btn-danger'
        }).then(function (ok) { if (ok) qpPerformDelete(id); });
    } else if (confirm(_t('note.delete_msg', 'Delete this note?'))) {
        qpPerformDelete(id);
    }
}
function qpPerformDelete(id) {
    window.NotesBridge.remove(qpRec(id))
        .then(function () {
            var widget = document.getElementById('quicknote-' + id);
            if (widget) { widget.style.opacity = '0'; widget.style.transform = 'scale(.92)'; setTimeout(function () { widget.remove(); }, 200); }
            qpEmit('delete', id);
        })
        .catch(function (e) { console.error('quick delete failed', e); });
}

function createQuickNoteWidget(rec, index, baseY) {
    var bgColor = rec.background_color || '#fbbf24';
    var colorClass = getColorClass(bgColor);
    var isGlass = !!(window.NoteBG && window.NoteBG.isPreset(bgColor));
    var textColorClass = isGlass ? 'light-text' : ((colorMap[colorClass] && colorMap[colorClass].text) || 'dark-text');

    var widget = document.createElement('div');
    widget.className = 'note-widget note-widget--quick color-' + colorClass + ' ' + textColorClass;
    widget.id = 'quicknote-' + rec.id;
    widget.setAttribute('data-quick-id', rec.id);
    widget.setAttribute('data-note-kind', 'quick');
    widget.setAttribute('data-bg-color', bgColor);
    if (isGlass) window.NoteBG.apply(widget, bgColor);

    // Static cascade layout (quick notes have no stored position/size). Start
    // below any board notes to avoid overlapping the free-positioned canvas.
    var col = index % 4, row = Math.floor(index / 4);
    widget.style.left = (20 + col * 270) + 'px';
    widget.style.top = ((baseY || 20) + row * 230) + 'px';
    widget.style.width = '250px';
    widget.style.height = '210px';
    widget.style.zIndex = 1;

    var pinned = !!rec.pinned;
    var presets = (window.NoteBG ? window.NoteBG.PRESETS : []).map(function (p) {
        return '<div class="color-option-dropdown color-option--grad" title="' + qpEsc(p.label) + '" style="background:' + p.css + '" onclick="qpChangeColor(' + rec.id + ', \'' + p.id + '\')"></div>';
    }).join('');

    widget.innerHTML =
        '<div class="note-widget-header" style="cursor:default;">' +
            '<input type="text" class="note-widget-title" placeholder="Note title..." value="' + qpEsc(rec.title || '') + '" onblur="qpUpdateTitle(' + rec.id + ', this.value)">' +
            '<div class="note-widget-actions">' +
                '<span class="qp-origin-chip" title="Synced quick note"><i class="bi bi-journal-text"></i></span>' +
                '<div class="note-color-picker-wrapper" style="position: relative;">' +
                    '<button class="note-widget-btn" onclick="(function(e){var d=document.getElementById(\'qpColorPicker-' + rec.id + '\');d.style.display=d.style.display===\'none\'?\'flex\':\'none\';e.stopPropagation();})(event)" title="Change color"><i class="bi bi-palette"></i></button>' +
                    '<div class="note-color-picker-dropdown" id="qpColorPicker-' + rec.id + '" style="display:none;">' +
                        '<div class="color-option-dropdown white" onclick="qpChangeColor(' + rec.id + ', \'#ffffff\')" title="White"></div>' +
                        '<div class="color-option-dropdown warning" onclick="qpChangeColor(' + rec.id + ', \'#fbbf24\')" title="Yellow"></div>' +
                        '<div class="color-option-dropdown success" onclick="qpChangeColor(' + rec.id + ', \'#10b981\')" title="Green"></div>' +
                        '<div class="color-option-dropdown dodgerblue" onclick="qpChangeColor(' + rec.id + ', \'#1e90ff\')" title="Blue"></div>' +
                        presets +
                    '</div>' +
                '</div>' +
                '<button class="note-widget-btn" onclick="qpTogglePin(' + rec.id + ', ' + (!pinned) + ', this)" title="' + (pinned ? 'Unpin' : 'Pin') + '"><i class="bi ' + (pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle') + '"></i></button>' +
                '<button class="note-widget-btn delete" onclick="qpDelete(' + rec.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' +
            '</div>' +
        '</div>' +
        '<div class="note-widget-body">' +
            '<div class="note-widget-content qp-content" contenteditable="true" data-placeholder="Write your note here..." style="white-space:pre-wrap;" onblur="qpUpdateContent(' + rec.id + ', this.innerText)">' + qpEsc(rec.body || '') + '</div>' +
        '</div>' +
        '<div class="note-widget-footer">' +
            '<span>Quick note</span>' +
        '</div>';

    return widget;
}

async function renderQuickNotesOnBoard() {
    var container = document.getElementById('notesContainer');
    if (!container || !window.NotesBridge) return;
    // Remove any previously-rendered quick widgets.
    container.querySelectorAll('.note-widget--quick').forEach(function (w) { w.remove(); });
    var all = await window.NotesBridge.list().catch(function () { return []; });
    var quick = all.filter(function (n) { return n.origin === 'quick'; });
    if (!quick.length) return;
    var emptyState = document.getElementById('emptyState');
    if (emptyState) emptyState.style.display = 'none';
    // Place quick notes below the lowest board note so they don't overlap.
    var baseY = 20;
    container.querySelectorAll('.note-widget:not(.note-widget--quick)').forEach(function (w) {
        var bottom = (parseInt(w.style.top, 10) || 0) + (parseInt(w.style.height, 10) || 200);
        if (bottom + 20 > baseY) baseY = bottom + 20;
    });
    quick.forEach(function (rec, i) { container.appendChild(createQuickNoteWidget(rec, i, baseY)); });
}

// Add color picker to each note (optional - can be added to header)
// For now, we'll add it via a context menu or button

// Autocomplete functionality for notes
let autocompletePortal = null;
let currentAutocompleteType = null; // 'patient', 'appointment', 'drug'
let currentAutocompleteQuery = '';
let currentAutocompleteItems = [];
let selectedAutocompleteIndex = -1;
let autocompleteTextarea = null;
let autocompleteCursorPosition = 0;
let autocompleteDebounceTimer = null;

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

// Escape HTML function
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize autocomplete for a contenteditable div
function initAutocomplete(contentEditable) {
    if (!contentEditable) return;
    
    contentEditable.addEventListener('input', handleContentEditableInput);
    contentEditable.addEventListener('keydown', handleContentEditableKeydown);
    contentEditable.addEventListener('blur', function() {
        // Delay hiding to allow click on autocomplete item
        setTimeout(() => {
            hideAutocomplete();
        }, 200);
    });
    
    // Handle clicks on links - allow navigation
    contentEditable.addEventListener('click', function(event) {
        const target = event.target;
        const link = target.closest('a[data-type]');
        
        if (link) {
            // Allow link navigation - open in new tab
            event.stopPropagation();
            event.preventDefault();
            window.open(link.href, '_blank');
            return false;
        }
    }, true); // Use capture phase to catch event early
    
    // Handle mousedown on badges to position cursor
    contentEditable.addEventListener('mousedown', function(event) {
        const target = event.target;
        const link = target.closest('a[data-type]');
        const badge = target.closest('span[data-type]');
        
        if (link) {
            // For links, don't prevent default - let click handler work
            event.stopPropagation();
            return true;
        }
        
        if (badge) {
            // For badges (drugs), position cursor before or after badge
            event.preventDefault();
            event.stopPropagation();
            const range = document.createRange();
            const selection = window.getSelection();
            const badgeRect = badge.getBoundingClientRect();
            const clickX = event.clientX;
            
            if (clickX < badgeRect.left + badgeRect.width / 2) {
                range.setStartBefore(badge);
            } else {
                range.setStartAfter(badge);
            }
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
            contentEditable.focus();
        }
    }, true); // Use capture phase
}

// Handle contenteditable input with debounce
function handleContentEditableInput(event) {
    const contentEditable = event.target;
    
    // Check if user is deleting content from autocomplete elements
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        const startContainer = range.startContainer;
        
        // Check if cursor is inside or at the edge of an autocomplete element
        let autocompleteElement = null;
        if (startContainer.nodeType === Node.TEXT_NODE) {
            autocompleteElement = startContainer.parentElement;
        } else if (startContainer.nodeType === Node.ELEMENT_NODE) {
            autocompleteElement = startContainer;
        }
        
        // Check if it's an autocomplete element (patient, appointment, or drug)
        while (autocompleteElement && autocompleteElement !== contentEditable) {
            const dataType = autocompleteElement.getAttribute('data-type');
            if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                // Check if user is actually deleting (not just clicking)
                const inputType = event.inputType;
                if (inputType === 'deleteContentBackward' || inputType === 'deleteContentForward' || 
                    inputType === 'deleteByDrag' || inputType === 'deleteByCut' ||
                    (!inputType && event.data === null)) {
                    // User is editing/deleting from an autocomplete element
                    // Remove the entire element
                    const parent = autocompleteElement.parentNode;
                    if (parent) {
                        // Create a text node with space to maintain cursor position
                        const space = document.createTextNode(' ');
                        parent.replaceChild(space, autocompleteElement);
                        
                        // Set cursor after space
                        const newRange = document.createRange();
                        newRange.setStartAfter(space);
                        newRange.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(newRange);
                        
                        // Ensure focus
                        contentEditable.focus();
                        
                        // Update note content
                        const noteId = contentEditable.getAttribute('data-note-id');
                        if (noteId) {
                            updateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                        }
                    }
                    return; // Don't process autocomplete after deletion
                }
                // If not deleting, allow normal interaction
                break;
            }
            autocompleteElement = autocompleteElement.parentElement;
        }
    }
    
    // Check immediately if trigger symbol was deleted (for immediate response)
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0).cloneRange();
        const fullRange = document.createRange();
        fullRange.selectNodeContents(contentEditable);
        fullRange.setEnd(range.startContainer, range.startOffset);
        const textBeforeCursor = fullRange.toString();
        const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
        
        // If no trigger symbol found, hide immediately
        if (!match && autocompletePortal) {
            const computedStyle = window.getComputedStyle(autocompletePortal);
            const isVisible = computedStyle.display !== 'none' && computedStyle.visibility !== 'hidden' && computedStyle.opacity !== '0' && !autocompletePortal.classList.contains('hidden');
            if (isVisible) {
                hideAutocomplete();
            }
        }
    }
    
    // Clear previous debounce timer
    if (autocompleteDebounceTimer) {
        clearTimeout(autocompleteDebounceTimer);
    }
    
    // Debounce the actual processing
    autocompleteDebounceTimer = setTimeout(() => {
        processAutocompleteInput(event);
    }, 150);
}

// Process autocomplete input (called after debounce)
function processAutocompleteInput(event) {
    const contentEditable = event.target;
    const selection = window.getSelection();
    
    if (!selection.rangeCount) {
        hideAutocomplete();
        return;
    }
    
    // Get fresh range (may have changed after debounce)
    const range = selection.getRangeAt(0).cloneRange();
    const textNode = range.startContainer;
    
    // Get all text content before cursor position
    let textBeforeCursor = '';
    
    // Create a range from start of contentEditable to cursor
    const fullRange = document.createRange();
    fullRange.selectNodeContents(contentEditable);
    fullRange.setEnd(range.startContainer, range.startOffset);
    textBeforeCursor = fullRange.toString();
    
    // Check for @ (patient), # (appointment), or $ (drug)
    const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
    
    if (match) {
        const trigger = match[1];
        const query = match[2];
        
        // Minimum query length: 2 characters for patients and drugs
        // For appointments: if numeric (ID search), allow 1 char; if date format, allow 8+ chars; if text (patient name), require 2 chars
        let minLength = 2;
        if (trigger === '#') {
            // For appointments: check if it's a date format, numeric ID, or patient name
            if (query.length === 0) {
                minLength = 0; // Allow showing recent appointments when just typing #
            } else {
                // Check if query looks like a date (DD-MM-YYYY, YYYY-MM-DD, DD/MM/YYYY, etc.)
                const datePattern = /^(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}|\d{2,4}[-\/]\d{1,2}[-\/]\d{1,2})$/;
                if (datePattern.test(query)) {
                    minLength = 8; // Minimum date length (DD-MM-YY)
                } else if (/^\d+$/.test(query)) {
                    minLength = 1; // Numeric ID search
                } else {
                    minLength = 2; // Patient name search
                }
            }
        }
        
        if (query.length >= minLength && query !== currentAutocompleteQuery) {
            currentAutocompleteType = trigger === '@' ? 'patient' : (trigger === '#' ? 'appointment' : 'drug');
            currentAutocompleteQuery = query;
            autocompleteTextarea = contentEditable;
            
            // Get fresh cursor position
            const rect = range.getBoundingClientRect();
            autocompleteCursorPosition = {
                range: range,
                textBefore: textBeforeCursor,
                match: match
            };
            
            showAutocomplete(contentEditable, rect, query);
        } else if (query.length < minLength) {
            hideAutocomplete();
        }
        // If query hasn't changed, don't reload
    } else {
        hideAutocomplete();
    }
}

// Handle keyboard navigation in autocomplete
function handleContentEditableKeydown(event) {
    const contentEditable = event.target;
    const selection = window.getSelection();
    
    // Handle Backspace/Delete to immediately check if trigger symbols are deleted
    if (event.key === 'Backspace' || event.key === 'Delete' || event.keyCode === 8 || event.keyCode === 46) {
        // Use setTimeout to check after the deletion happens
        setTimeout(() => {
            if (!selection.rangeCount) {
                hideAutocomplete();
                return;
            }
            
            const range = selection.getRangeAt(0).cloneRange();
            const fullRange = document.createRange();
            fullRange.selectNodeContents(contentEditable);
            fullRange.setEnd(range.startContainer, range.startOffset);
            const textBeforeCursor = fullRange.toString();
            
            // Check if trigger symbol still exists
            const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
            if (!match) {
                // Trigger symbol was deleted - hide autocomplete immediately
                hideAutocomplete();
            }
        }, 0);
    }
    
    // Check if cursor is inside an autocomplete element (badge/link)
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        let node = range.startContainer;
        
        // Check if we're inside a badge or link
        while (node && node !== contentEditable) {
            if (node.nodeType === Node.ELEMENT_NODE) {
                const dataType = node.getAttribute('data-type');
                if (dataType === 'patient' || dataType === 'appointment' || dataType === 'drug') {
                    // User is trying to type inside an autocomplete element
                    // Move cursor outside the element
                    if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar' || event.keyCode === 13 || event.keyCode === 32) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Create a new text node after the element
                        const textContent = (event.key === 'Enter' || event.keyCode === 13) ? '\n' : ' ';
                        const newTextNode = document.createTextNode(textContent);
                        const parent = node.parentNode;
                        
                        if (parent) {
                            parent.insertBefore(newTextNode, node.nextSibling);
                            
                            // Set cursor after the new text node
                            const newRange = document.createRange();
                            newRange.setStartAfter(newTextNode);
                            newRange.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(newRange);
                            contentEditable.focus();
                            
                            // Update note content
                            const noteId = contentEditable.getAttribute('data-note-id');
                            if (noteId) {
                                updateNoteContent(parseInt(noteId), contentEditable.innerHTML);
                            }
                        }
                        return;
                    }
                    break;
                }
            }
            node = node.parentNode;
        }
    }
    
    // Handle autocomplete portal navigation
    if (!autocompletePortal || autocompletePortal.style.display === 'none') {
        return;
    }
    
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        selectedAutocompleteIndex = Math.min(selectedAutocompleteIndex + 1, currentAutocompleteItems.length - 1);
        updateAutocompleteSelection();
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        selectedAutocompleteIndex = Math.max(selectedAutocompleteIndex - 1, -1);
        updateAutocompleteSelection();
    } else if (event.key === 'Enter' || event.key === 'Tab') {
        event.preventDefault();
        if (selectedAutocompleteIndex >= 0 && currentAutocompleteItems[selectedAutocompleteIndex]) {
            selectAutocompleteItem(currentAutocompleteItems[selectedAutocompleteIndex]);
        }
    } else if (event.key === 'Escape') {
        hideAutocomplete();
    }
}

// Show autocomplete portal
async function showAutocomplete(contentEditable, cursorRect, query) {
    if (!autocompletePortal) {
        autocompletePortal = document.createElement('div');
        autocompletePortal.className = 'note-autocomplete-portal';
        autocompletePortal.id = 'noteAutocompletePortal';
        document.body.appendChild(autocompletePortal);
    }
    
    // Remove existing click handler before adding a new one (to avoid duplicates)
    document.removeEventListener('click', handleClickOutside, true);
    // Add click outside handler to close autocomplete (use capture phase for early detection)
    document.addEventListener('click', handleClickOutside, true);
    
    // Also add mousedown handler for better responsiveness
    document.removeEventListener('mousedown', handleClickOutside, true);
    document.addEventListener('mousedown', handleClickOutside, true);
    
    // Position the portal near the cursor - use fixed positioning for highest z-index
    const x = cursorRect.left + window.scrollX;
    const y = cursorRect.bottom + window.scrollY + 5;
    
    // Remove hidden class first
    autocompletePortal.classList.remove('hidden');
    
    autocompletePortal.style.position = 'fixed';
    autocompletePortal.style.left = `${x}px`;
    autocompletePortal.style.top = `${y}px`;
    autocompletePortal.style.display = 'block';
    autocompletePortal.style.visibility = 'visible';
    autocompletePortal.style.opacity = '1';
    autocompletePortal.style.zIndex = '9999999';
    autocompletePortal.style.pointerEvents = 'auto';
    
    // Load autocomplete items
    await loadAutocompleteItems(query);
}

// Handle click outside autocomplete portal
function handleClickOutside(event) {
    if (!autocompletePortal) {
        return;
    }
    
    // Check if portal is visible
    const computedStyle = window.getComputedStyle(autocompletePortal);
    const isHidden = computedStyle.display === 'none' || computedStyle.visibility === 'hidden' || computedStyle.opacity === '0' || autocompletePortal.classList.contains('hidden');
    
    if (isHidden) {
        return;
    }
    
    // Check if click is outside portal and contenteditable
    const target = event.target;
    const clickedOnPortal = autocompletePortal.contains(target);
    
    // Check if clicking on contenteditable
    let clickedOnContentEditable = false;
    if (autocompleteTextarea) {
        clickedOnContentEditable = (
            autocompleteTextarea.contains(target) || 
            autocompleteTextarea === target ||
            autocompleteTextarea.isSameNode(target)
        );
        
        // Also check if target is inside the contenteditable's parent container
        if (!clickedOnContentEditable) {
            const contentEditableParent = autocompleteTextarea.closest('.note-widget-content-container, .note-widget-content, .note-widget');
            if (contentEditableParent && contentEditableParent.contains(target)) {
                clickedOnContentEditable = true;
            }
        }
    }
    
    // Also check if clicking on autocomplete items (they should not close the portal)
    const clickedOnAutocompleteItem = target.closest('.note-autocomplete-item');
    
    // If clicking outside both portal and contenteditable, close the portal
    if (!clickedOnPortal && !clickedOnContentEditable && !clickedOnAutocompleteItem) {
        hideAutocomplete();
    }
}

// Load autocomplete items based on type
async function loadAutocompleteItems(query) {
    try {
        // Verify query hasn't changed (user may have continued typing)
        if (query !== currentAutocompleteQuery) {
            return; // Query changed, ignore this response
        }
        
        let url = '';
        if (currentAutocompleteType === 'patient') {
            url = `/api/patients/search?q=${encodeURIComponent(query)}`;
        } else if (currentAutocompleteType === 'appointment') {
            url = `/api/appointments/search?q=${encodeURIComponent(query)}&limit=10`;
        } else if (currentAutocompleteType === 'drug') {
            url = `/api/searchDrugsAutocomplete?q=${encodeURIComponent(query)}&limit=10`;
        }
        
        if (!url) {
            return;
        }
        
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            return;
        }
        
        const data = await response.json();
        
        // Double-check query hasn't changed
        if (query !== currentAutocompleteQuery) {
            return; // Query changed during fetch, ignore this response
        }
        
        let items = [];
        
        if (currentAutocompleteType === 'patient' && data.ok && data.data) {
            items = data.data.map(patient => ({
                type: 'patient',
                id: patient.id,
                title: `${patient.first_name} ${patient.last_name}`,
                subtitle: patient.phone || '',
                data: patient
            }));
        } else if (currentAutocompleteType === 'appointment' && data.ok && data.data) {
            items = data.data.map(apt => {
                // Handle date properly - apt.date is in 'YYYY-MM-DD' format
                // Convert to DD-MM-YYYY format
                let dateStr = '';
                if (apt.date) {
                    try {
                        // Parse date string (YYYY-MM-DD) and convert to DD-MM-YYYY
                        const dateParts = apt.date.split('-');
                        if (dateParts.length === 3) {
                            // Format: DD-MM-YYYY
                            const day = dateParts[2].padStart(2, '0');
                            const month = dateParts[1].padStart(2, '0');
                            const year = dateParts[0];
                            dateStr = `${day}-${month}-${year}`;
                        } else {
                            dateStr = apt.date;
                        }
                    } catch (e) {
                        dateStr = apt.date; // Fallback to original string
                    }
                }
                const timeStr = apt.start_time ? apt.start_time.substring(0, 5) : '';
                const patientName = escapeHtml(apt.patient_name || 'Unknown');
                const status = escapeHtml(apt.status || '');
                return {
                    type: 'appointment',
                    id: apt.id,
                    title: `#${apt.id} - ${patientName}`,
                    subtitle: `${dateStr} ${timeStr} - ${status}`,
                    data: apt
                };
            });
        } else if (currentAutocompleteType === 'drug' && data.drugs) {
            items = data.drugs.map(drug => ({
                type: 'drug',
                id: drug.ID,
                title: drug.drug_name,
                subtitle: drug.active_ingredient || drug.Company || '',
                data: drug
            }));
        }
        
        // Final check: query still matches
        if (query === currentAutocompleteQuery) {
            currentAutocompleteItems = items;
            selectedAutocompleteIndex = -1;
            renderAutocompleteItems(items);
        }
    } catch (error) {
        // Silent error handling
    }
}

// Render autocomplete items
function renderAutocompleteItems(items) {
    if (!autocompletePortal) return;
    
    if (items.length === 0) {
        autocompletePortal.innerHTML = '<div class="note-autocomplete-item"><div class="item-content">No results found</div></div>';
        return;
    }
    
    let html = '';
    items.forEach((item, index) => {
        const icon = item.type === 'patient' ? 'bi-person' : (item.type === 'appointment' ? 'bi-calendar-event' : 'bi-capsule');
        html += `
            <div class="note-autocomplete-item ${index === selectedAutocompleteIndex ? 'selected' : ''}" 
                 data-index="${index}"
                 onclick="selectAutocompleteItem(${JSON.stringify(item).replace(/"/g, '&quot;')})">
                <i class="bi ${icon} item-icon"></i>
                <div class="item-content">
                    <div class="item-title">${escapeHtml(item.title)}</div>
                    ${item.subtitle ? `<div class="item-subtitle">${escapeHtml(item.subtitle)}</div>` : ''}
                </div>
            </div>
        `;
    });
    
    autocompletePortal.innerHTML = html;
}

// Update autocomplete selection
function updateAutocompleteSelection() {
    if (!autocompletePortal) return;
    
    const items = autocompletePortal.querySelectorAll('.note-autocomplete-item');
    items.forEach((item, index) => {
        if (index === selectedAutocompleteIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('selected');
        }
    });
}

// Select autocomplete item
function selectAutocompleteItem(item) {
    if (!autocompleteTextarea || !item || !autocompleteCursorPosition) {
        hideAutocomplete();
        return;
    }
    
    const contentEditable = autocompleteTextarea;
    const range = autocompleteCursorPosition.range;
    const match = autocompleteCursorPosition.match;
    
    // Hide autocomplete immediately to prevent any delays
    hideAutocomplete();
    
    if (match && range) {
        // Delete the trigger and query text
        range.setStart(range.startContainer, range.startOffset - match[0].length);
        range.deleteContents();
        
        // Create replacement element
        let replacement = null;
        if (item.type === 'patient') {
            replacement = document.createElement('a');
            replacement.href = `/doctor/patients/${item.id}`;
            replacement.className = 'note-content-link';
            replacement.target = '_blank';
            replacement.setAttribute('data-type', 'patient');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-person patient-icon"></i>${escapeHtml(item.title)}`;
        } else if (item.type === 'appointment') {
            replacement = document.createElement('a');
            replacement.href = `/doctor/appointments/${item.id}`;
            replacement.className = 'note-content-appointment-link';
            replacement.target = '_blank';
            replacement.setAttribute('data-type', 'appointment');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-calendar-event appointment-icon"></i>#${item.id}`;
        } else if (item.type === 'drug') {
            replacement = document.createElement('span');
            replacement.className = 'note-content-drug-badge';
            replacement.setAttribute('data-type', 'drug');
            replacement.setAttribute('data-id', item.id);
            replacement.innerHTML = `<i class="bi bi-capsule drug-icon"></i>${escapeHtml(item.title)}`;
            // Add click event to show drug popover
            replacement.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showDrugPopover(item.id, item.title, e);
            });
        }
        
        if (replacement) {
            // Insert replacement
            range.insertNode(replacement);
            
            // Add space after replacement to allow typing
            const spaceAfter = document.createTextNode(' ');
            range.setStartAfter(replacement);
            range.insertNode(spaceAfter);
            
            // Set cursor after space - create fresh range
            const newRange = document.createRange();
            newRange.setStartAfter(spaceAfter);
            newRange.collapse(true);
            
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(newRange);
            
            // Force focus and ensure typing works - especially for drug badges
            setTimeout(() => {
                contentEditable.focus();
                
                // Create a new range after the space node
                const finalRange = document.createRange();
                const finalSelection = window.getSelection();
                
                // Ensure we have a text node after the replacement for typing
                let textNodeAfter = spaceAfter;
                
                // If spaceAfter is not a direct child or not found, create one
                if (!spaceAfter.parentNode || spaceAfter.parentNode !== contentEditable) {
                    // Find the replacement's parent
                    const parent = replacement.parentNode;
                    if (parent) {
                        // Check if there's already a text node after replacement
                        let nextSibling = replacement.nextSibling;
                        if (nextSibling && nextSibling.nodeType === Node.TEXT_NODE) {
                            textNodeAfter = nextSibling;
                        } else {
                            // Create a new text node after replacement
                            textNodeAfter = document.createTextNode(' ');
                            parent.insertBefore(textNodeAfter, replacement.nextSibling);
                        }
                    }
                }
                
                // Set cursor after the text node
                try {
                    finalRange.setStartAfter(textNodeAfter);
                    finalRange.collapse(true);
                } catch (e) {
                    // Fallback: create a text node at the end
                    const endTextNode = document.createTextNode(' ');
                    contentEditable.appendChild(endTextNode);
                    finalRange.setStartAfter(endTextNode);
                    finalRange.collapse(true);
                }
                
                finalSelection.removeAllRanges();
                finalSelection.addRange(finalRange);
                
                // Ensure contenteditable is ready for input
                contentEditable.focus();
                
                // Additional check: ensure cursor is not inside replacement
                setTimeout(() => {
                    const checkRange = finalSelection.getRangeAt(0);
                    let checkNode = checkRange.startContainer;
                    while (checkNode && checkNode !== contentEditable) {
                        if (checkNode === replacement) {
                            // Cursor is inside replacement, move it out
                            const parent = replacement.parentNode;
                            if (parent) {
                                const newTextNode = document.createTextNode(' ');
                                parent.insertBefore(newTextNode, replacement.nextSibling);
                                const newRange = document.createRange();
                                newRange.setStartAfter(newTextNode);
                                newRange.collapse(true);
                                finalSelection.removeAllRanges();
                                finalSelection.addRange(newRange);
                                contentEditable.focus();
                            }
                            break;
                        }
                        checkNode = checkNode.parentNode;
                    }
                }, 50);
            }, 200);
            
            // Update note content
            const noteId = contentEditable.getAttribute('data-note-id');
            if (noteId) {
                updateNoteContent(parseInt(noteId), contentEditable.innerHTML);
            }
        }
    }
    
    hideAutocomplete();
    contentEditable.focus();
}

// Hide autocomplete
function hideAutocomplete() {
    if (autocompletePortal) {
        // Use multiple methods to ensure hiding
        autocompletePortal.style.display = 'none';
        autocompletePortal.style.visibility = 'hidden';
        autocompletePortal.style.opacity = '0';
        autocompletePortal.style.pointerEvents = 'none';
        autocompletePortal.classList.add('hidden');
    }
    
    // Remove click outside handlers
    document.removeEventListener('click', handleClickOutside, true);
    document.removeEventListener('mousedown', handleClickOutside, true);
    
    currentAutocompleteType = null;
    currentAutocompleteQuery = '';
    currentAutocompleteItems = [];
    selectedAutocompleteIndex = -1;
    autocompleteTextarea = null;
}

// Drug Popover Functions
let currentDrugPopover = null;

async function showDrugPopover(drugId, drugName, event) {
    // Close existing popover if any
    if (currentDrugPopover) {
        closeDrugPopover();
    }
    
    // Create popover element
    const popover = document.createElement('div');
    popover.className = 'note-drug-popover';
    popover.id = 'noteDrugPopover';
    
    // Create backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'note-drug-popover-backdrop';
    backdrop.addEventListener('click', closeDrugPopover);
    
    // Position popover in center of viewport
    popover.style.position = 'fixed';
    popover.style.left = '50%';
    popover.style.top = '50%';
    popover.style.transform = 'translate(-50%, -50%)';
    popover.style.zIndex = '10000000';
    popover.style.maxWidth = '600px';
    popover.style.width = '90%';
    popover.style.maxHeight = '80vh';
    popover.style.overflowY = 'auto';
    
    // Show loading state
    popover.innerHTML = `
        <div class="note-drug-popover-header">
            <h5 class="note-drug-popover-title">${escapeHtml(drugName)}</h5>
            <button type="button" class="note-drug-popover-close" onclick="closeDrugPopover()" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="note-drug-popover-body">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(backdrop);
    document.body.appendChild(popover);
    currentDrugPopover = popover;
    
    try {
        // Fetch drug details
        const response = await fetch(`/api/getDrugDetails?id=${drugId}`);
        const data = await response.json();
        
        if (data.drug) {
            const drug = data.drug;
            popover.innerHTML = `
                <div class="note-drug-popover-header">
                    <h5 class="note-drug-popover-title">${escapeHtml(drug.drug_name || drugName)}</h5>
                    <button type="button" class="note-drug-popover-close" onclick="closeDrugPopover()" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="note-drug-popover-body">
                    <div class="mb-3">
                        <h6 class="text-primary mb-2">Active Ingredient</h6>
                        <p class="mb-0">${escapeHtml(drug.active_ingredient || 'N/A')}</p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <h6 class="text-primary mb-1">Company</h6>
                            <p class="mb-0">${escapeHtml(drug.Company || 'N/A')}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <h6 class="text-primary mb-1">Category</h6>
                            <p class="mb-0">${escapeHtml(drug.category || 'N/A')}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <h6 class="text-primary mb-1">Price</h6>
                            <p class="text-success fw-bold mb-0">${drug.price ? 'EGP ' + escapeHtml(drug.price) : 'N/A'}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <h6 class="text-primary mb-1">Route</h6>
                            <p class="mb-0">${escapeHtml(drug.administration_route || 'N/A')}</p>
                        </div>
                    </div>
                    
                    ${drug.GI ? `
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">General Information</h6>
                            <p class="mb-0" style="line-height: 1.6;">${escapeHtml(drug.GI)}</p>
                        </div>
                    ` : ''}
                    
                    ${drug.SRDE ? `
                        <div>
                            <h6 class="text-primary mb-2">Additional Information</h6>
                            <p class="mb-0" style="line-height: 1.6;">${escapeHtml(drug.SRDE)}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        } else {
            popover.innerHTML = `
                <div class="note-drug-popover-header">
                    <h5 class="note-drug-popover-title">${escapeHtml(drugName)}</h5>
                    <button type="button" class="note-drug-popover-close" onclick="closeDrugPopover()" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="note-drug-popover-body">
                    <div class="text-center py-4">
                        <p class="mb-0">Drug information not available</p>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error fetching drug details:', error);
        popover.innerHTML = `
            <div class="note-drug-popover-header">
                <h5 class="note-drug-popover-title">${escapeHtml(drugName)}</h5>
                <button type="button" class="note-drug-popover-close" onclick="closeDrugPopover()" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="note-drug-popover-body">
                <div class="text-center py-4">
                    <p class="text-danger mb-0">Error loading drug information</p>
                </div>
            </div>
        `;
    }
}

function closeDrugPopover() {
    if (currentDrugPopover) {
        currentDrugPopover.remove();
        currentDrugPopover = null;
    }
    const backdrop = document.querySelector('.note-drug-popover-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Make function global
window.showDrugPopover = showDrugPopover;
window.closeDrugPopover = closeDrugPopover;

// Initialize drug badge click handlers
function initDrugBadges() {
    document.querySelectorAll('.note-content-drug-badge').forEach(badge => {
        const drugId = badge.getAttribute('data-id');
        const drugName = badge.textContent.trim();
        
        badge.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (drugId) {
                showDrugPopover(parseInt(drugId), drugName, e);
            }
        });
    });
}

// Initialize autocomplete for all note content editables
function initAllAutocompletes() {
    document.querySelectorAll('.note-widget-content[contenteditable="true"]').forEach(contentEditable => {
        // Add click listener to contenteditable to hide autocomplete portal when clicking
        // (but not when clicking on autocomplete items or links)
        contentEditable.addEventListener('mousedown', function(event) {
            const target = event.target;
            // Don't hide if clicking on autocomplete items (links, badges) or autocomplete portal
            const isAutocompleteItem = target.closest('a[data-type], span[data-type]');
            const isAutocompletePortal = target.closest('.note-autocomplete-portal');
            
            if (!isAutocompleteItem && !isAutocompletePortal) {
                // Check if cursor is at a position with trigger symbol
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const fullRange = document.createRange();
                    fullRange.selectNodeContents(contentEditable);
                    fullRange.setEnd(range.startContainer, range.startOffset);
                    const textBeforeCursor = fullRange.toString();
                    const match = textBeforeCursor.match(/(@|#|\$)([^\s@#$]*)$/);
                    
                    // Only hide if there's no active trigger symbol
                    if (!match) {
                        hideAutocomplete();
                    }
                } else {
                    hideAutocomplete();
                }
            }
        });
        
        initAutocomplete(contentEditable);
    });
    
    // Add click listener to notes-container to hide autocomplete portal
    const notesContainer = document.getElementById('notesContainer');
    if (notesContainer) {
        notesContainer.addEventListener('mousedown', function(event) {
            const target = event.target;
            // Don't hide if clicking on contenteditable or autocomplete portal
            const contentEditable = target.closest('.note-widget-content[contenteditable="true"]');
            const isAutocompletePortal = target.closest('.note-autocomplete-portal');
            
            if (!contentEditable && !isAutocompletePortal) {
                hideAutocomplete();
            }
        });
    }
    
    // Add click listener to all note-widget elements to hide autocomplete portal
    document.querySelectorAll('.note-widget').forEach(widget => {
        widget.addEventListener('mousedown', function(event) {
            const target = event.target;
            // Don't hide if clicking on contenteditable (autocomplete should work there)
            const contentEditable = widget.querySelector('.note-widget-content[contenteditable="true"]');
            if (contentEditable && (contentEditable.contains(target) || contentEditable === target)) {
                return; // Allow autocomplete to work in contenteditable
            }
            // Hide autocomplete portal when clicking anywhere else on the widget
            hideAutocomplete();
        });
    });
}

// Load notes on initial render — wherever the board is present: the standalone
// /doctor/notes page AND the dashboard embed (which lives at /doctor/dashboard).
// Gating on the container (not the path) is what makes the embed populate.
if (document.getElementById('notesContainer')) {
    // Run on DOMContentLoaded (unless the doc is already complete) so the deferred
    // layout buses NoteBG/NotesSync/NotesBridge — which load AFTER this file in
    // document order — are available before the first render + sync binding.
    if (document.readyState !== 'complete') {
        document.addEventListener('DOMContentLoaded', function() {
            loadNotes();
            // Initialize autocomplete after notes are loaded
            setTimeout(initAllAutocompletes, 500);
            
            // Check if we need to focus a newly created note (from dashboard)
            const shouldFocusNewNote = sessionStorage.getItem('focusNewNote') === 'true';
            const newNoteId = sessionStorage.getItem('newNoteId');
            
            if (shouldFocusNewNote && newNoteId) {
                // Wait for notes to load, then focus the new note
                setTimeout(() => {
                    const newNoteWidget = document.getElementById(`note-${newNoteId}`);
                    if (newNoteWidget) {
                        // Bring note to front
                        bringToFront(parseInt(newNoteId));
                        
                        // Focus the title input
                        const titleInput = newNoteWidget.querySelector('.note-widget-title');
                        if (titleInput) {
                            titleInput.focus();
                        }
                    }
                    
                    // Clear sessionStorage
                    sessionStorage.removeItem('focusNewNote');
                    sessionStorage.removeItem('newNoteId');
                }, 1500);
            }
            
            // Check if we need to open add note modal (legacy support)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('openModal') === 'addNote') {
                setTimeout(() => {
                    const addNoteBtn = document.getElementById('addNoteBtn');
                    if (addNoteBtn) {
                        addNoteBtn.click();
                    }
                    // Clean URL
                    const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=addNote/, '').replace(/^&/, '?');
                    window.history.replaceState({}, '', newUrl);
                }, 1000);
            }
        });
    } else {
        loadNotes();
        setTimeout(initAllAutocompletes, 500);
        
        // Check if we need to focus a newly created note (from dashboard)
        const shouldFocusNewNote = sessionStorage.getItem('focusNewNote') === 'true';
        const newNoteId = sessionStorage.getItem('newNoteId');
        
        if (shouldFocusNewNote && newNoteId) {
            // Wait for notes to load, then focus the new note
            setTimeout(() => {
                const newNoteWidget = document.getElementById(`note-${newNoteId}`);
                if (newNoteWidget) {
                    // Bring note to front
                    bringToFront(parseInt(newNoteId));
                    
                    // Focus the title input
                    const titleInput = newNoteWidget.querySelector('.note-widget-title');
                    if (titleInput) {
                        titleInput.focus();
                    }
                }
                
                // Clear sessionStorage
                sessionStorage.removeItem('focusNewNote');
                sessionStorage.removeItem('newNoteId');
            }, 1500);
        }
        
        // Check if we need to open add note modal (legacy support)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('openModal') === 'addNote') {
            setTimeout(() => {
                const addNoteBtn = document.getElementById('addNoteBtn');
                if (addNoteBtn) {
                    addNoteBtn.click();
                }
                // Clean URL
                const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=addNote/, '').replace(/^&/, '?');
                window.history.replaceState({}, '', newUrl);
            }, 1000);
        }
    }
}

// Inject gradient/glassmorphism swatches into the toolbar color picker and
// keep the notes page in live-sync with the dashboard board-notes widget.
(function initNoteBgExtras() {
    function run() {
        // 1) Toolbar gradient swatches (single source of truth = NoteBG.PRESETS).
        var options = document.querySelector('.color-options');
        if (options && window.NoteBG && !options.__gradInjected) {
            options.__gradInjected = true;
            window.NoteBG.PRESETS.forEach(function (p) {
                var el = document.createElement('div');
                el.className = 'color-option color-option--grad';
                el.setAttribute('data-color', p.id);
                el.setAttribute('data-bg', p.id);
                el.setAttribute('title', p.label);
                el.style.background = p.css;
                el.addEventListener('click', function () {
                    document.querySelectorAll('.color-option').forEach(function (o) { o.classList.remove('active'); });
                    el.classList.add('active');
                    currentNoteColor = p.id;
                    currentNoteColorClass = p.id;
                });
                options.appendChild(el);
            });
        }
        // 2) Live-sync: this page now shows a MERGED view, so reload on ANY note
        //    change (board OR quick) from any surface. Ignore our own echo.
        if (window.NotesSync && !window.__notesPageSyncBound) {
            window.__notesPageSyncBound = true;
            window.NotesSync.on(function () {
                if (window.__notesPageSelfSync) return;
                if (document.getElementById('notesContainer')) loadNotes();
            });
        }
    }
    // Defer to DOMContentLoaded (after the layout's deferred NoteBG/NotesSync) unless
    // the document is already complete — see the loadNotes init note above.
    if (document.readyState !== 'complete') document.addEventListener('DOMContentLoaded', run);
    else run();
})();

// Re-initialize autocomplete when new notes are added
const originalLoadNotes = loadNotes;
loadNotes = async function() {
    await originalLoadNotes();
    await renderQuickNotesOnBoard();   // merged view: show quick notes too
    setTimeout(initAllAutocompletes, 100);
    setTimeout(initDrugBadges, 100);
};

// Show note alert picker dropdown
function showNoteAlertPicker(noteId, event) {
    event.stopPropagation();
    
    // Close all other alert pickers
    document.querySelectorAll('.note-alert-picker-dropdown').forEach(picker => {
        if (picker.id !== `alertPicker-${noteId}`) {
            picker.style.display = 'none';
        }
    });
    
    // Close color pickers
    document.querySelectorAll('.note-color-picker-dropdown').forEach(picker => {
        picker.style.display = 'none';
    });
    
    // Toggle current picker
    const picker = document.getElementById(`alertPicker-${noteId}`);
    if (picker) {
        if (picker.style.display === 'none' || !picker.style.display) {
            picker.style.display = 'block';
            // Close on outside click
            setTimeout(() => {
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && !e.target.closest(`#alertPicker-${noteId}`) && !e.target.closest(`button[onclick*="showNoteAlertPicker(${noteId}"]`)) {
                        picker.style.display = 'none';
                        document.removeEventListener('click', closePicker);
                    }
                });
            }, 10);
        } else {
            picker.style.display = 'none';
        }
    }
}

// Format 24-hour time to 12-hour format
function format12HourTime(time24) {
    const [hours, minutes] = time24.split(':');
    const hour12 = parseInt(hours) % 12 || 12;
    const ampm = parseInt(hours) < 12 ? 'AM' : 'PM';
    return `${hour12}:${minutes} ${ampm}`;
}

// Convert 12-hour time to 24-hour format
function convertTo24Hour(hour, minute, ampm) {
    let hour24 = parseInt(hour);
    if (ampm === 'PM' && hour24 !== 12) {
        hour24 += 12;
    } else if (ampm === 'AM' && hour24 === 12) {
        hour24 = 0;
    }
    return `${hour24.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}:00`;
}

// Create alert from note
async function createAlertFromNote(noteId) {
    const widget = document.getElementById(`note-${noteId}`);
    if (!widget) return;
    
    const noteContent = widget.querySelector('.note-widget-content');
    if (!noteContent) return;
    
    const alertDate = document.getElementById(`alertDate-${noteId}`).value;
    const alertHour = document.getElementById(`alertHour-${noteId}`).value;
    const alertMinute = document.getElementById(`alertMinute-${noteId}`).value;
    const alertAmPm = document.getElementById(`alertAmPm-${noteId}`).value;
    
    if (!alertDate || !alertHour || !alertMinute) {
        alert('Please select date and time for the alert');
        return;
    }
    
    // Convert to 24-hour format
    const alertTime = convertTo24Hour(alertHour, alertMinute, alertAmPm);
    
    // Get note content (HTML)
    const noteHtml = noteContent.innerHTML;
    
    // Close picker
    const picker = document.getElementById(`alertPicker-${noteId}`);
    if (picker) {
        picker.style.display = 'none';
    }
    
    try {
        const response = await fetch('/api/alerts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                message: noteHtml, // Send HTML content
                alert_date: alertDate,
                alert_time: alertTime,
                repeat_count: 1,
                repeat_interval: 0
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'alert alert-success alert-dismissible fade show position-fixed';
            successMsg.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px;';
            successMsg.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>Alert ${data.updated ? 'updated' : 'created'} successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(successMsg);
            
            setTimeout(() => {
                if (successMsg.parentNode) {
                    successMsg.remove();
                }
            }, 3000);
            
            // Reload notes to update alert status
            loadNotes();
        } else {
            alert('Failed to create alert: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error creating alert:', error);
        alert('Failed to create alert: ' + error.message);
    }
}

// Show delete all notes confirmation
function showDeleteAllNotesConfirmation() {
    const modal = document.getElementById('deleteAllNotesModal');
    if (!modal) {
        // Create modal if it doesn't exist
        const modalHtml = `
            <div class="modal fade" id="deleteAllNotesModal" tabindex="-1" aria-labelledby="deleteAllNotesModalLabel" aria-hidden="true" style="z-index: 99999;">
                <div class="modal-dialog modal-dialog-centered" style="z-index: 100000;">
                    <div class="modal-content" style="z-index: 100001;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteAllNotesModalLabel" style="color: var(--text);">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                Delete All Notes
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete <strong>ALL</strong> notes?</p>
                            <p class="text-muted mb-0"><small>This action cannot be undone. All notes will be permanently deleted.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteAllNotesBtn">
                                <i class="bi bi-trash me-2"></i>
                                Delete All Notes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Initialize draggable for the modal
        makeNotesModalDraggable(document.getElementById('deleteAllNotesModal'));
    }
    
    const modalInstance = new bootstrap.Modal(document.getElementById('deleteAllNotesModal'));
    const confirmBtn = document.getElementById('confirmDeleteAllNotesBtn');
    
    // Remove previous event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add new event listener
    newConfirmBtn.addEventListener('click', async function() {
        await performDeleteAllNotes();
        modalInstance.hide();
    });
    
    modalInstance.show();
}

// Perform the actual delete all
async function performDeleteAllNotes() {
    const confirmBtn = document.getElementById('confirmDeleteAllNotesBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    }
    
    try {
        const response = await fetch('/api/notes/delete-all', {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Network error' }));
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Delete All Notes';
        }
        
        if (data.success) {
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'alert alert-success alert-dismissible fade show position-fixed';
            successMsg.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px;';
            successMsg.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>All notes have been deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(successMsg);
            
            setTimeout(() => {
                if (successMsg.parentNode) {
                    successMsg.remove();
                }
            }, 3000);
            
            // Clear all widgets and show empty state
            const container = document.getElementById('notesContainer');
            const emptyState = document.getElementById('emptyState');
            const widgets = container.querySelectorAll('.note-widget');
            
            widgets.forEach(widget => {
                widget.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                widget.style.opacity = '0';
                widget.style.transform = 'scale(0.8)';
            });
            
            setTimeout(() => {
                widgets.forEach(widget => widget.remove());
                if (emptyState) {
                    emptyState.style.display = 'block';
                }
            }, 300);
        } else {
            throw new Error(data.message || 'Failed to delete all notes');
        }
    } catch (error) {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Delete All Notes';
        }
        console.error('Error deleting all notes:', error);
        alert('Failed to delete all notes: ' + error.message);
    }
}

// Make modal draggable
function makeNotesModalDraggable(modalElement) {
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
        if (e.target.closest('button')) return;
        
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

// Initialize draggable for existing delete note modal
if (document.getElementById('deleteNoteModal')) {
    makeNotesModalDraggable(document.getElementById('deleteNoteModal'));
}

// Notes Container Resize functionality
let isContainerResizing = false;
let containerResizeStart = { x: 0, y: 0, width: 0, height: 0 };

function startContainerResize(event) {
    // Support both mouse and touch events
    const isTouch = event.type === 'touchstart';
    const clientX = isTouch ? event.touches[0].clientX : event.clientX;
    const clientY = isTouch ? event.touches[0].clientY : event.clientY;
    
    isContainerResizing = true;
    const container = document.getElementById('notesContainer');
    const rect = container.getBoundingClientRect();
    
    containerResizeStart.x = clientX;
    containerResizeStart.y = clientY;
    containerResizeStart.width = rect.width;
    containerResizeStart.height = rect.height;
    
    // Add both mouse and touch event listeners
    document.addEventListener('mousemove', onContainerResize);
    document.addEventListener('mouseup', stopContainerResize);
    document.addEventListener('touchmove', onContainerResize, { passive: false });
    document.addEventListener('touchend', stopContainerResize);
    
    event.preventDefault();
    event.stopPropagation();
}

function onContainerResize(event) {
    if (!isContainerResizing) return;
    
    // Support both mouse and touch events
    const isTouch = event.type === 'touchmove';
    const clientY = isTouch ? event.touches[0].clientY : event.clientY;
    
    const container = document.getElementById('notesContainer');
    const containerRect = container.getBoundingClientRect();
    
    // Calculate deltaY - positive when dragging down, negative when dragging up
    const deltaY = clientY - containerResizeStart.y;
    
    if (isTouch) {
        event.preventDefault();
    }
    
    // Calculate new height only (not width)
    // When dragging down (deltaY positive), increase height
    // When dragging up (deltaY negative), decrease height
    let newHeight = containerResizeStart.height + deltaY;
    
    // Constrain to viewport and min size
    const isMobile = window.innerWidth <= 768;
    const minHeight = isMobile ? 300 : 400;
    const maxHeight = window.innerHeight - 200; // Account for toolbar and padding
    
    // Ensure height stays within bounds
    newHeight = Math.max(minHeight, Math.min(newHeight, maxHeight));
    
    // Apply new height only (keep width unchanged)
    container.style.height = `${newHeight}px`;
    container.style.minHeight = `${newHeight}px`;
    
    // Save to localStorage (only height, width stays the same)
    const currentWidth = containerResizeStart.width;
    localStorage.setItem('notesContainerWidth', currentWidth);
    localStorage.setItem('notesContainerHeight', newHeight);
}

function stopContainerResize() {
    if (isContainerResizing) {
        isContainerResizing = false;
    }
    
    document.removeEventListener('mousemove', onContainerResize);
    document.removeEventListener('mouseup', stopContainerResize);
    document.removeEventListener('touchmove', onContainerResize);
    document.removeEventListener('touchend', stopContainerResize);
}

// Load saved container size on page load
if (window.location.pathname.includes('/doctor/notes')) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            loadContainerSize();
        });
    } else {
        loadContainerSize();
    }
}

function loadContainerSize() {
    const container = document.getElementById('notesContainer');
    if (!container) return;
    
    const savedWidth = localStorage.getItem('notesContainerWidth');
    const savedHeight = localStorage.getItem('notesContainerHeight');
    
    if (savedWidth && savedHeight) {
        const width = parseInt(savedWidth);
        const height = parseInt(savedHeight);
        
        // Validate dimensions
        const isMobile = window.innerWidth <= 768;
        const minWidth = isMobile ? 300 : 400;
        const minHeight = isMobile ? 300 : 400;
        const maxWidth = window.innerWidth - 40;
        const maxHeight = window.innerHeight - 200;
        
        if (width >= minWidth && width <= maxWidth && height >= minHeight && height <= maxHeight) {
            container.style.width = `${width}px`;
            container.style.height = `${height}px`;
            container.style.minHeight = `${height}px`;
        }
    }
}

// Reset container size to original
function resetContainerSize() {
    const container = document.getElementById('notesContainer');
    if (!container) return;
    
    // Reset to original size (100% width, default min-height)
    container.style.width = '100%';
    container.style.height = '';
    container.style.minHeight = 'calc(100vh - 200px)';
    
    // Remove from localStorage
    localStorage.removeItem('notesContainerWidth');
    localStorage.removeItem('notesContainerHeight');
}
